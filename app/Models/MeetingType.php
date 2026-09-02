<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MeetingType extends Model
{
    protected $fillable = ['name', 'url'];

    protected $table = 'meeting_types';

    public function meetings(): HasMany
    {
        return $this->hasMany(Meeting::class);
    }
}
