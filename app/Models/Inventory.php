<?php

/**
 * Created by Reliese Model.
 * Date: Sun, 16 Dec 2018 11:17:54 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Inventory
 * 
 * @property int $inventory_id
 * @property int $film_id
 * @property int $store_id
 * @property \Carbon\Carbon $last_update
 * 
 * @property \App\Models\Film $film
 * @property \App\Models\Store $store
 * @property \Illuminate\Database\Eloquent\Collection $rentals
 *
 * @package App\Models
 */
class Inventory extends Eloquent
{
	protected $table = 'inventory';
	protected $primaryKey = 'inventory_id';
	public $timestamps = false;

	protected $casts = [
		'film_id' => 'int',
		'store_id' => 'int'
	];

	protected $dates = [
		'last_update'
	];

	protected $fillable = [
		'film_id',
		'store_id',
		'last_update'
	];

	public function film()
	{
		return $this->belongsTo(\App\Models\Film::class);
	}

	public function store()
	{
		return $this->belongsTo(\App\Models\Store::class);
	}

	public function rentals()
	{
		return $this->hasMany(\App\Models\Rental::class);
	}
}
