<?php

/**
 * Created by Reliese Model.
 * Date: Sun, 16 Dec 2018 11:17:54 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Country
 * 
 * @property int $country_id
 * @property string $country
 * @property \Carbon\Carbon $last_update
 * 
 * @property \Illuminate\Database\Eloquent\Collection $cities
 *
 * @package App\Models
 */
class Country extends Eloquent
{
	protected $table = 'country';
	protected $primaryKey = 'country_id';
	public $timestamps = false;

	protected $dates = [
		'last_update'
	];

	protected $fillable = [
		'country',
		'last_update'
	];

	public function cities()
	{
		return $this->hasMany(\App\Models\City::class);
	}
}
