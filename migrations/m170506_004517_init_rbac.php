<?php

use yii\db\Migration;

class m170506_004517_init_rbac extends Migration
{
    public $adminId = 1;
    public $hospitalId = 2;
    public $doctorId = 3;
    public $patientId = 4;
    protected function getAuthManager()
    {
        $authManager = Yii::$app->getAuthManager();
        if (!$authManager instanceof \yii\rbac\DbManager) {
            throw new \yii\base\InvalidConfigException('You should configure "authManager" component to use database before executing this migration.');
        }

        return $authManager;
    }
    public function up()
    {
        $auth = Yii::$app->authManager;

        //Roles

        $doctor = $auth->createRole('doctor');
        $doctor->description = 'Doctor';
        $auth->add($doctor);

        $hospital = $auth->createRole('hospital');
        $hospital->description = 'Hospital';
        $auth->add($hospital);

        $patient = $auth->createRole('patient');
        $patient->description = 'Patient';
        $auth->add($patient);

        $admin = $auth->createRole('admin');
        $admin->description = 'Administrator';
        $auth->add($admin);

        //$admin = $auth->getRole('admin');
        //$doctor = $auth->getRole('doctor');
        //$hospital = $auth->getRole('hospital');
        //$patient = $auth->getRole('patient');
/*
        // Assign administrator role to admin (1)
        $auth->assign($admin, $this->adminId);

        // Assign hospital role to hospital (2)
        $auth->assign($hospital, $this->hospitalId);

        // Assign doctor role to doctor (3)
        $auth->assign($doctor, $this->doctorId);

        // Assign patient role to patient (4)
        $auth->assign($patient, $this->patientId);
*/
    }

    public function down()
    {
        Yii::$app->authManager->removeAll();
        $authManager = $this->getAuthManager();
        $this->db = $authManager->db;

        $this->dropTable($authManager->assignmentTable);
        $this->dropTable($authManager->itemChildTable);
        $this->dropTable($authManager->itemTable);
        $this->dropTable($authManager->ruleTable);
    }
    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
