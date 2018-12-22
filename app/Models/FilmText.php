<?php

/**
 * Created by Reliese Model.
 * Date: Sun, 16 Dec 2018 11:17:54 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class FilmText
 * 
 * @property int $film_id
 * @property string $title
 * @property string $description
 *
 * @package App\Models
 */
class FilmText extends Eloquent
{
	protected $table = 'film_text';
	protected $primaryKey = 'film_id';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'film_id' => 'int'
	];

	protected $fillable = [
		'title',
		'description'
	];
}
