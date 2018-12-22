<?php

/**
 * Created by Reliese Model.
 * Date: Sun, 16 Dec 2018 11:17:54 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Payment
 * 
 * @property int $payment_id
 * @property int $customer_id
 * @property int $staff_id
 * @property int $rental_id
 * @property float $amount
 * @property \Carbon\Carbon $payment_date
 * @property \Carbon\Carbon $last_update
 * 
 * @property \App\Models\Customer $customer
 * @property \App\Models\Rental $rental
 * @property \App\Models\Staff $staff
 *
 * @package App\Models
 */
class Payment extends Eloquent
{
	protected $table = 'payment';
	protected $primaryKey = 'payment_id';
	public $timestamps = false;

	protected $casts = [
		'customer_id' => 'int',
		'staff_id' => 'int',
		'rental_id' => 'int',
		'amount' => 'float'
	];

	protected $dates = [
		'payment_date',
		'last_update'
	];

	protected $fillable = [
		'customer_id',
		'staff_id',
		'rental_id',
		'amount',
		'payment_date',
		'last_update'
	];

	public function customer()
	{
		return $this->belongsTo(\App\Models\Customer::class);
	}

	public function rental()
	{
		return $this->belongsTo(\App\Models\Rental::class);
	}

	public function staff()
	{
		return $this->belongsTo(\App\Models\Staff::class);
	}
}
