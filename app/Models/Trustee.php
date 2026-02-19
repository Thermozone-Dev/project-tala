<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Trustee
 * 
 * @property int $id
 * @property int $user_id
 * @property string|null $mobile_number
 * @property Carbon|null $date_of_birth
 * @property string|null $gender
 * @property string $status
 * @property Carbon|null $appointed_at
 * @property Carbon|null $term_start
 * @property Carbon|null $term_end
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property User $user
 *
 * @package App\Models
 */
class Trustee extends Model
{
	protected $table = 'trustees';

	protected $casts = [
		'user_id' => 'int',
		'date_of_birth' => 'datetime',
		'appointed_at' => 'datetime',
		'term_start' => 'datetime',
		'term_end' => 'datetime'
	];

	protected $fillable = [
		'user_id',
		'mobile_number',
		'date_of_birth',
		'gender',
		'status',
		'appointed_at',
		'term_start',
		'term_end'
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
