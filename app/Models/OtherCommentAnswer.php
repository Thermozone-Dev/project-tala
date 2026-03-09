<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class OtherCommentAnswer extends Model
{
    use HasFactory,LogsActivity;

    protected $table = 'other_comments';

    public $timestamps = false; // remove if you add timestamps later

    protected $fillable = [
        'comment',
        'trustee_evaluation_id',
        'comment_id'
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function trusteeEvaluation()
    {
        return $this->belongsTo(TrusteeHasEvaluation::class, 'trustee_evaluation_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'comment',
            ]);
    }
}
