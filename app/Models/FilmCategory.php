<?php

/**
 * Created by Reliese Model.
 * Date: Sun, 16 Dec 2018 11:17:54 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class FilmCategory
 * 
 * @property int $film_id
 * @property int $category_id
 * @property \Carbon\Carbon $last_update
 * 
 * @property \App\Models\Category $category
 * @property \App\Models\Film $film
 *
 * @package App\Models
 */
class FilmCategory extends Eloquent
{
	protected $table = 'film_category';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'film_id' => 'int',
		'category_id' => 'int'
	];

	protected $dates = [
		'last_update'
	];

	protected $fillable = [
		'last_update'
	];

	public function category()
	{
		return $this->belongsTo(\App\Models\Category::class);
	}

	public function film()
	{
		return $this->belongsTo(\App\Models\Film::class);
	}
}
