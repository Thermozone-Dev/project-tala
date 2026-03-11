<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrusteeEvaluationStatus extends Model
{
    //
    protected $table = 'trustee_evaluation_statuses';

    public $timestamps = false; // remove if you add timestamps later

    protected $fillable = [
        'name',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */


}
