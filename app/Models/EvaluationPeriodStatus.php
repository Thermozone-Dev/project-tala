<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EvaluationPeriodStatus extends Model
{
    use SoftDeletes;

    protected $table = 'evaluation_period_status';

    protected $fillable = [
        'name'
    ];

    public function evaluationPeriods()
    {
        return $this->hasMany(EvaluationPeriod::class, 'status_id');
    }
}
