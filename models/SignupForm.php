<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * Signup form
 */
class SignupForm extends Model
{
    public $identification;
    public $email;
    public $phone;
    public $password;
    public $role;

    public $name;
    public $address;
    public $services;
    public $birthday;

    /** @var User */
    private $_user = false;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['identification', 'trim'],
            ['identification', 'required'],
            [
                'identification',
                'unique',
                'targetClass' => '\app\models\User',
                'message' => Yii::t('app', 'This identification has already been taken.')
            ],
            ['identification', 'string', 'length' => [3, 25]],
            [
                'identification',
                'match',
                'pattern' => '/^[A-Za-z0-9_-]{3,25}$/',
                'message' => Yii::t(
                    'app',
                    'Your identification can only contain alphanumeric characters, underscores and dashes.'
                )
            ],
            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            [
                'email',
                'unique',
                'targetClass' => '\app\models\User',
                'message' => Yii::t('app', 'This email address has already been taken.')
            ],

            ['password', 'required'],
            ['password', 'string', 'min' => 6],
            ['phone', 'required'],
            ['phone', 'string', 'length' => [8, 15]],
            ['role', 'required'],
            ['role', 'integer'],
            ['role', 'validateRole'],

            ['name', 'required'],
            ['name', 'string', 'length' => [4, 30]],

            ['address', 'required'],
            ['address', 'string', 'length' => [8, 255]],

            [['services'], 'required', 'when' =>
                function ($model) {
                    return ($model->role == User::ROLE_HOSPITAL);
                },
                ],
            [['birthday'], 'required', 'when' =>
                function ($model) {
                    return ($model->role == User::ROLE_PATIENT);
                },
            ],


        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validateRole($attribute, $params)
    {
        if (!$this->hasErrors()) {

            if ($this->role != User::ROLE_HOSPITAL && $this->role != User::ROLE_PATIENT) {
                $this->addError($attribute, 'Only allow registration for Hospital and Patient');
            }
        }
    }

    /**
     * Signs user up.
     *
     * @return boolean the saved model or null if saving fails
     */
    public function signup()
    {
        if ($this->validate()) {
            $user = new User();
            $user->identification = strtolower($this->identification);
            $user->email = $this->email;
            $user->phone = $this->phone;
            $user->birthday = $this->birthday;
            $user->address = $this->address;
            $user->services = $this->services;

            $user->unconfirmed_email = $this->email;
            $user->role = $this->role;
            $user->status = User::STATUS_PENDING;
            $user->setPassword($this->password);
            $user->generateAuthKey();

            $user->registration_ip = Yii::$app->request->userIP;

            if ($user->save(false)) {
                $this->_user = $user;
                return true;
            }

            return false;
        }
        return false;
    }

    /**
     * Return User object
     *
     * @return User
     */
    public function getUser()
    {
        return $this->_user;
    }

    /**
     * Send confirmation email
     *
     * @return bool
     */
    public function sendConfirmationEmail()
    {
        $confirmURL = \Yii::$app->params['frontendURL'].'v1/user' . '/confirm?id=' . $this->_user->id . '&auth_key=' . $this->_user->auth_key;

        $email = \Yii::$app->mailer
            ->compose(
                ['html' => 'signup-confirmation-html'],
                [
                    'appName' => \Yii::$app->name,
                    'confirmURL' => $confirmURL,
                ]
            )
            ->setTo($this->email)
            ->setFrom([\Yii::$app->params['supportEmail'] => \Yii::$app->name])
            ->setSubject('Signup confirmation')
            ->send();

        return $email;
    }
}
