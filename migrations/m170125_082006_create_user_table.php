<?php

use yii\db\Migration;

/**
 * Handles the creation of table `user`.
 */
class m170125_082006_create_user_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('user', [
            'id' => $this->primaryKey(),
            'identification' => $this->string(100),
            'auth_key' => $this->string(255),
            'access_token_expired_at' => $this->integer(11) . ' NULL DEFAULT NULL',
            'password_hash' => $this->string(255),
            'password_reset_token' => $this->string(255),
            'email' => $this->string(100),
            'phone' => $this->string(40),
            'address' => $this->string(255),
            'services' => $this->string(255),
            'birthday' => $this->date(),

            'unconfirmed_email' => $this->string(255),
            'confirmed_at' => $this->integer(11) . ' NULL DEFAULT NULL',
            'registration_ip' => $this->string(20),
            'last_login_at' => $this->integer(11) . ' NULL DEFAULT NULL',
            'last_login_ip' => $this->string(20),
            'blocked_at' => $this->integer(11) . ' NULL DEFAULT NULL',
            'status' => $this->integer(2)->defaultValue(10),
            'role' => $this->integer(11)->null(),
            'created_at' => $this->integer(11) . ' NULL DEFAULT NULL',
            'updated_at' => $this->integer(11) . ' NULL DEFAULT NULL',

        ]);

        // creates index for table
        $this->createIndex(
            'idx-user',
            'user',
            ['identification', 'auth_key', 'password_hash', 'status']
        );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropIndex('idx-user', 'user');

        $this->dropTable('user');
    }
}
