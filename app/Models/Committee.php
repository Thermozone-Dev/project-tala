<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

/**
 * Class Committee
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Committee extends Model
{
	protected $table = 'committees';

	protected $fillable = [
		'name',
		'description'
	];

//    public function assignee()
//    {
//        return $this->belongsToMany(
//            \App\Models\User::class,       // related model
//            'model_has_roles',             // pivot table
//            'committee_id',                // foreign key on pivot table
//            'model_id'                     // related key (user id) on pivot table
//        )->withPivot('role_id');           // include role info
//    }
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'model_has_roles', 'committee_id', 'role_id')
            ->withPivot('model_id', 'model_type');
    }
}
