<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MeetingStatus extends Model
{
    protected $table = 'meeting_statuses';

    protected $fillable = [
        'name',
        'label',
        'color',
    ];

    public function meetings(): HasMany
    {
        return $this->hasMany(Meeting::class, 'meeting_status_id');
    }
}
