<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class QuestionaireAnswer extends Model
{
    //
    use HasFactory,LogsActivity;

    protected $table = 'questionnaire_answers';

    public $timestamps = false; // remove if you add timestamps later

    protected $fillable = [
        'questionnaire_id',
        'rating_scale_values_id',
        'trustee_evaluation_id',
        'remarks',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function questionnaire()
    {
        return $this->belongsTo(Questionnaire::class);
    }

    public function ratingScaleValue()
    {
        return $this->belongsTo(RatingScaleValue::class, 'rating_scale_values_id');
    }

    public function trusteeEvaluation()
    {
        return $this->belongsTo(TrusteeHasEvaluation::class, 'trustee_evaluation_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'questionnaire.name',
                'ratingScaleValue.value',
                'ratingScaleValue.qualitative',
                'remarks',
            ]);
    }
}
