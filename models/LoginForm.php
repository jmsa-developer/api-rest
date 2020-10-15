<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 *
 * @property User|null $user This property is read-only.
 *
 */
class LoginForm extends Model
{
    public $identification;
    public $password;
    public $roles = [];
    public $rememberMe = true;
    /** @var User */
    private $_user = false;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // identification, email, phone and password required
            [['identification', 'password'], 'required'],

            ['rememberMe', 'boolean'],

            ['password', 'validatePassword'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUserByIdentification();

            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect identification or password.');
            } else{

                switch ($user->status) {
                    case User::STATUS_PENDING:
                        $this->addError($attribute, 'User must validate email');
                        break;
                    case User::STATUS_DELETED:
                        $this->addError($attribute, 'User was deleted');
                        break;
                    case User::STATUS_DISABLED:
                        $this->addError($attribute, 'User is disabled');
                        break;
                }
            }
        }
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUserByIdentification()
    {
        // Roles must be set to get an user

        if ($this->_user === false) {
            $this->_user = User::findByIdentificationWithRoles($this->identification, $this->roles);
        }

        return $this->_user;
    }

    /**
     * Logs in a user using the provided identification and password.
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUserByIdentification(), $this->rememberMe ? 3600 * 24 * 30 : 0);
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
}
