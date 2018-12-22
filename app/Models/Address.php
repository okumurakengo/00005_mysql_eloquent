<?php

/**
 * Created by Reliese Model.
 * Date: Sun, 16 Dec 2018 11:17:54 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Address
 * 
 * @property int $address_id
 * @property string $address
 * @property string $address2
 * @property string $district
 * @property int $city_id
 * @property string $postal_code
 * @property string $phone
 * @property geometry $location
 * @property \Carbon\Carbon $last_update
 * 
 * @property \App\Models\City $city
 * @property \Illuminate\Database\Eloquent\Collection $customers
 * @property \Illuminate\Database\Eloquent\Collection $staff
 * @property \Illuminate\Database\Eloquent\Collection $stores
 *
 * @package App\Models
 */
class Address extends Eloquent
{
	protected $table = 'address';
	protected $primaryKey = 'address_id';
	public $timestamps = false;

	protected $casts = [
		'city_id' => 'int',
		'location' => 'geometry'
	];

	protected $dates = [
		'last_update'
	];

	protected $fillable = [
		'address',
		'address2',
		'district',
		'city_id',
		'postal_code',
		'phone',
		'location',
		'last_update'
	];

	public function city()
	{
		return $this->belongsTo(\App\Models\City::class);
	}

	public function customers()
	{
		return $this->hasMany(\App\Models\Customer::class);
	}

	public function staff()
	{
		return $this->hasMany(\App\Models\Staff::class);
	}

	public function stores()
	{
		return $this->hasMany(\App\Models\Store::class);
	}
}
