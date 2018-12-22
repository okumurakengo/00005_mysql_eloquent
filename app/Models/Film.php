<?php

/**
 * Created by Reliese Model.
 * Date: Sun, 16 Dec 2018 11:17:54 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Film
 * 
 * @property int $film_id
 * @property string $title
 * @property string $description
 * @property \Carbon\Carbon $release_year
 * @property int $language_id
 * @property int $original_language_id
 * @property int $rental_duration
 * @property float $rental_rate
 * @property int $length
 * @property float $replacement_cost
 * @property string $rating
 * @property set $special_features
 * @property \Carbon\Carbon $last_update
 * 
 * @property \App\Models\Language $language
 * @property \Illuminate\Database\Eloquent\Collection $actors
 * @property \Illuminate\Database\Eloquent\Collection $categories
 * @property \Illuminate\Database\Eloquent\Collection $inventories
 *
 * @package App\Models
 */
class Film extends Eloquent
{
	protected $table = 'film';
	protected $primaryKey = 'film_id';
	public $timestamps = false;

	protected $casts = [
		'language_id' => 'int',
		'original_language_id' => 'int',
		'rental_duration' => 'int',
		'rental_rate' => 'float',
		'length' => 'int',
		'replacement_cost' => 'float',
		'special_features' => 'set'
	];

	protected $dates = [
		'release_year',
		'last_update'
	];

	protected $fillable = [
		'title',
		'description',
		'release_year',
		'language_id',
		'original_language_id',
		'rental_duration',
		'rental_rate',
		'length',
		'replacement_cost',
		'rating',
		'special_features',
		'last_update'
	];

	public function language()
	{
		return $this->belongsTo(\App\Models\Language::class, 'original_language_id');
	}

	public function actors()
	{
		return $this->belongsToMany(\App\Models\Actor::class, 'film_actor')
					->withPivot('last_update');
	}

	public function categories()
	{
		return $this->belongsToMany(\App\Models\Category::class, 'film_category')
					->withPivot('last_update');
	}

	public function inventories()
	{
		return $this->hasMany(\App\Models\Inventory::class);
	}
}
