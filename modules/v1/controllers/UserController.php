<?php

namespace app\modules\v1\controllers;

use app\filters\auth\HttpBearerAuth;
use app\models\LoginForm;
use app\models\PasswordResetForm;
use app\models\PasswordResetRequestForm;
use app\models\PasswordResetTokenVerificationForm;
use app\models\SignupConfirmForm;
use app\models\SignupForm;
use app\models\User;
use app\models\UserEditForm;
use app\models\UserSearch;
use Yii;
use yii\filters\AccessControl;
use yii\filters\auth\CompositeAuth;
use yii\helpers\Url;
use yii\rest\ActiveController;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

class UserController extends ActiveController
{
    public $modelClass = 'app\models\User';

    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);
    }

    public function actions()
    {
        return [];
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            'authMethods' => [
                HttpBearerAuth::className(),
            ],

        ];

        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::className(),
            'actions' => [
                'login' => ['post'],
            ],
        ];

        // remove authentication filter
        $auth = $behaviors['authenticator'];
        unset($behaviors['authenticator']);

        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::className(),
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
            ],
        ];

        // re-add authentication filter
        $behaviors['authenticator'] = $auth;
        // avoid authentication on CORS-pre-flight requests (HTTP OPTIONS method)
        $behaviors['authenticator']['except'] = [
            'options',
            'login',
            'signup',
            'confirm',
            'password-reset-request',
            'password-reset-token-verification',
            'password-reset'
        ];

        return $behaviors;
    }

    /**
     * Search users
     *
     * @return array
     * @throws BadRequestHttpException
     */
    public function actionIndex()
    {
        $search = new UserSearch();
        $search->load(\Yii::$app->request->get());
        $search->in_roles = [User::ROLE_PATIENT,User::ROLE_DOCTOR,User::ROLE_HOSPITAL];
        $search->not_in_status = [User::STATUS_DELETED];
        if (!$search->validate()) {
            throw new BadRequestHttpException(
                'Invalid parameters: ' . json_encode($search->getErrors())
            );
        }

        return $search->getDataProvider();
    }

    /**
     * Process login
     *
     * @return array
     * @throws HttpException
     */
    public function actionLogin()
    {
        $model = new LoginForm();
        $data = [];

        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            $user = $model->getUser();
            $user->generateAccessTokenAfterUpdatingClientInfo(true);

            $response = \Yii::$app->getResponse();
            $response->setStatusCode(200);
            $id = implode(',', array_values($user->getPrimaryKey(true)));

            $data[] = [
                'id' => (int)$id,
                'access_token' => $user->access_token,
            ];

        } else {
            $data = $model->errors;

        }
        return $data;
    }

    /**
     * Process user sign-up
     *
     * @return array
     * @throws HttpException
     */
    public function actionSignup()
    {
        $model = new SignupForm();

        $model->load(Yii::$app->request->post());

        if ($model->validate() && $model->signup()) {
            // Send confirmation email
            $model->sendConfirmationEmail();

            $response = \Yii::$app->getResponse();
            $response->setStatusCode(201);

            $data = $model;

        } else {
            // Validation error
            $data = $model->errors;

            //throw new HttpException(422, json_encode($model->errors));
        }
        return $data;
    }

    /**
     * Process user sign-up confirmation
     *
     * @return array
     * @throws HttpException
     */
    public function actionConfirm()
    {
        $model = new SignupConfirmForm();
        $data = [];

        $model->load(Yii::$app->request->post());
        if ($model->validate() && $model->confirm()) {
            $response = \Yii::$app->getResponse();
            $response->setStatusCode(200);

            $user = $model->getUser();

            $data[] = [
                'id' => (int)$user->id,
                'access_token' => $user->access_token,
            ];
        } else {
            $data = $model->errors;
        }
        return $data;
    }

    /**
     * Process password reset request
     *
     * @return array
     * @throws HttpException
     */
    public function actionPasswordResetRequest()
    {
        $model = new PasswordResetRequestForm();

        $model->load(Yii::$app->request->post());
        if ($model->validate() && $model->sendPasswordResetEmail()) {
            $response = \Yii::$app->getResponse();
            $response->setStatusCode(200);

            $data = $model;
        } else {
            $data = $model->errors;
        }

        return $data;
    }

    /**
     * Verify password reset token
     *
     * @return array
     * @throws HttpException
     */
    public function actionPasswordResetTokenVerification()
    {
        $model = new PasswordResetTokenVerificationForm();

        $model->load(Yii::$app->request->post());
        if ($model->validate() && $model->validate()) {
            $response = \Yii::$app->getResponse();
            $response->setStatusCode(200);

            $data = $model;
        } else {
            $data = $model->errors;
        }
        return $data;
    }

    /**
     * Process password reset
     *
     * @return array
     * @throws HttpException
     */
    public function actionPasswordReset()
    {
        $model = new PasswordResetForm();
        $model->load(Yii::$app->request->post());

        if ($model->validate() && $model->resetPassword()) {
            $response = \Yii::$app->getResponse();
            $response->setStatusCode(200);

            $data = $model;
        } else {
            $data = $model->errors;
        }

        return $data;
    }

    /**
     * Return logged in user information
     *
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionMe()
    {
        $user = User::findIdentity(\Yii::$app->user->getId());

        if ($user) {
            $response = \Yii::$app->getResponse();
            $response->setStatusCode(200);

            return [
                'username' => $user->username,
                'email' => $user->email,
                'last_login_at' => $user->last_login_at,
                'last_login_ip' => $user->last_login_ip,
            ];
        } else {
            // Validation error
            throw new NotFoundHttpException('Object not found');
        }
    }

    /**
     * Update logged in user information
     *
     * @return string
     * @throws HttpException
     * @throws NotFoundHttpException
     */
    public function actionMeUpdate()
    {
        $user = User::findIdentity(\Yii::$app->user->getId());

        if ($user) {
            $model = new UserEditForm();
            $model->load(Yii::$app->request->post());
            $model->id = $user->id;

            if ($model->validate() && $model->save()) {
                $response = \Yii::$app->getResponse();
                $response->setStatusCode(200);

                $responseData = 'true';

                return $responseData;
            } else {
                // Validation error
                throw new HttpException(422, json_encode($model->errors));
            }
        } else {
            // Validation error
            throw new NotFoundHttpException('Object not found');
        }
    }

    /**
     * Handle OPTIONS
     *
     * @param null $id
     * @return string
     */
    public function actionOptions($id = null)
    {
        return 'ok';
    }
}
