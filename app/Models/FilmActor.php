<?php

/**
 * Created by Reliese Model.
 * Date: Sun, 16 Dec 2018 11:17:54 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class FilmActor
 * 
 * @property int $actor_id
 * @property int $film_id
 * @property \Carbon\Carbon $last_update
 * 
 * @property \App\Models\Actor $actor
 * @property \App\Models\Film $film
 *
 * @package App\Models
 */
class FilmActor extends Eloquent
{
	protected $table = 'film_actor';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'actor_id' => 'int',
		'film_id' => 'int'
	];

	protected $dates = [
		'last_update'
	];

	protected $fillable = [
		'last_update'
	];

	public function actor()
	{
		return $this->belongsTo(\App\Models\Actor::class);
	}

	public function film()
	{
		return $this->belongsTo(\App\Models\Film::class);
	}
}
