<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * ConsultationForm is the model behind the login form.
 *
 * @property User|null $user This property is read-only.
 *
 */
class ConsultationForm extends Model
{
    public $doctor_id;
    public $patient_id;
    public $observations;
    public $specialty;
    public $health_condition;


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['doctor_id', 'patient_id', 'observations', 'specialty','health_condition'], 'required'],

        ];
    }

    public function create()
    {
        $consultation = new Consultation([
            'doctor_id' => $this->doctor_id,
            'patient_id' => $this->patient_id,
            'observations' => $this->observations,
            'health_condition' => $this->health_condition,
            'specialty' => $this->specialty
        ]);

        return $consultation->save(false);
    }
}
