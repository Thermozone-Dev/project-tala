<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Class Committee
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Collection|CommitteeHasTrustee[] $committee_has_trustees
 *
 * @package App\Models
 */
class Committee extends Model
{
    use LogsActivity;
	protected $table = 'committees';

	protected $fillable = [
		'name',
		'description'
	];

	public function meetings(): HasMany
	{
		return $this->hasMany(Meeting::class);
	}

	public function committee_has_trustees()
	{
        if(!get_executive_role(auth()->user()->getRoleNames()->first())){
            return $this->hasMany(CommitteeHasTrustee::class)->where('user_id',auth()->user()->id);
        }
		return $this->hasMany(CommitteeHasTrustee::class);
	}

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'description']);
    }

    public function getFormByRole()
	{
		return $this->hasMany(CommitteeHasTrustee::class);
	}


}
