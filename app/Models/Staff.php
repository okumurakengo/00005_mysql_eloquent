<?php

/**
 * Created by Reliese Model.
 * Date: Sun, 16 Dec 2018 11:17:54 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Staff
 * 
 * @property int $staff_id
 * @property string $first_name
 * @property string $last_name
 * @property int $address_id
 * @property boolean $picture
 * @property string $email
 * @property int $store_id
 * @property bool $active
 * @property string $username
 * @property string $password
 * @property \Carbon\Carbon $last_update
 * 
 * @property \App\Models\Address $address
 * @property \App\Models\Store $store
 * @property \Illuminate\Database\Eloquent\Collection $payments
 * @property \Illuminate\Database\Eloquent\Collection $rentals
 * @property \Illuminate\Database\Eloquent\Collection $stores
 *
 * @package App\Models
 */
class Staff extends Eloquent
{
	protected $primaryKey = 'staff_id';
	public $timestamps = false;

	protected $casts = [
		'address_id' => 'int',
		'picture' => 'boolean',
		'store_id' => 'int',
		'active' => 'bool'
	];

	protected $dates = [
		'last_update'
	];

	protected $hidden = [
		'password'
	];

	protected $fillable = [
		'first_name',
		'last_name',
		'address_id',
		'picture',
		'email',
		'store_id',
		'active',
		'username',
		'password',
		'last_update'
	];

	public function address()
	{
		return $this->belongsTo(\App\Models\Address::class);
	}

	public function store()
	{
		return $this->belongsTo(\App\Models\Store::class);
	}

	public function payments()
	{
		return $this->hasMany(\App\Models\Payment::class);
	}

	public function rentals()
	{
		return $this->hasMany(\App\Models\Rental::class);
	}

	public function stores()
	{
		return $this->hasMany(\App\Models\Store::class, 'manager_staff_id');
	}
}
