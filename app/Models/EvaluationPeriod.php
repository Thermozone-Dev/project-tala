<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EvaluationPeriod extends Model
{
    protected $table = 'evaluation_period';

    use SoftDeletes;


    protected $fillable = [
        'date_from',
        'date_to',
        'status_id',
        'created_by'
    ];

    protected $dates = [
        'date_from',
        'date_to'
    ];

    public function status()
    {
        return $this->belongsTo(EvaluationPeriodStatus::class, 'status_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignments()
    {
        return $this->hasMany(TrusteeHasEvaluation::class, 'evaluation_id');
    }



    public function getFormattedCoverage()
    {
        return Carbon::parse($this->date_from)->format('M d, Y') . ' - ' . Carbon::parse($this->date_to)->format('M d, Y');
    }
}
