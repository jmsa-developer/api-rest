<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "consultation".
 *
 * @property integer $id
 * @property integer $doctor_id
 * @property integer $patient_id
 * @property string $observations
 * @property string $specialty
 * @property string $health_condition
 */
class Consultation extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'consultation';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [

            [['doctor_id', 'patient_id', 'observations', 'specialty','health_condition'], 'required'],

        ];
    }


}
