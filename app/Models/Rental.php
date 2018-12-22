<?php

/**
 * Created by Reliese Model.
 * Date: Sun, 16 Dec 2018 11:17:54 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Rental
 * 
 * @property int $rental_id
 * @property \Carbon\Carbon $rental_date
 * @property int $inventory_id
 * @property int $customer_id
 * @property \Carbon\Carbon $return_date
 * @property int $staff_id
 * @property \Carbon\Carbon $last_update
 * 
 * @property \App\Models\Customer $customer
 * @property \App\Models\Inventory $inventory
 * @property \App\Models\Staff $staff
 * @property \Illuminate\Database\Eloquent\Collection $payments
 *
 * @package App\Models
 */
class Rental extends Eloquent
{
	protected $table = 'rental';
	protected $primaryKey = 'rental_id';
	public $timestamps = false;

	protected $casts = [
		'inventory_id' => 'int',
		'customer_id' => 'int',
		'staff_id' => 'int'
	];

	protected $dates = [
		'rental_date',
		'return_date',
		'last_update'
	];

	protected $fillable = [
		'rental_date',
		'inventory_id',
		'customer_id',
		'return_date',
		'staff_id',
		'last_update'
	];

	public function customer()
	{
		return $this->belongsTo(\App\Models\Customer::class);
	}

	public function inventory()
	{
		return $this->belongsTo(\App\Models\Inventory::class);
	}

	public function staff()
	{
		return $this->belongsTo(\App\Models\Staff::class);
	}

	public function payments()
	{
		return $this->hasMany(\App\Models\Payment::class);
	}
}
