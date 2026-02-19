<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CommitteeHasTrustee
 *
 * @property int $id
 * @property int $role_id
 * @property int $committee_id
 * @property int $user_id
 *
 * @property Committee $committee
 * @property Role $role
 * @property User $user
 *
 * @package App\Models
 */
class CommitteeHasTrustee extends Model
{
	protected $table = 'committee_has_trustees';
	public $timestamps = false;

	protected $casts = [
		'role_id' => 'int',
		'committee_id' => 'int',
		'user_id' => 'int'
	];

	protected $fillable = [
		'role_id',
		'committee_id',
		'user_id'
	];

	public function committee()
	{
		return $this->belongsTo(Committee::class);
	}

	public function role()
	{
		return $this->belongsTo(\Spatie\Permission\Models\Role::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
