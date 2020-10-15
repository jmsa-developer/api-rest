<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%consultation}}`.
 */
class m201007_232340_create_consultation_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%consultation}}', [
            'id' => $this->primaryKey(),
            'doctor_id' => $this->integer(),
            'patient_id' => $this->integer(),
            'observations' => $this->string(),
            'specialty' => $this->string(),
            'health_condition' => $this->string(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%consultation}}');
    }
}
