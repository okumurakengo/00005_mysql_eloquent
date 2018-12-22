<?php

/**
 * Created by Reliese Model.
 * Date: Sun, 16 Dec 2018 11:17:54 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Language
 * 
 * @property int $language_id
 * @property string $name
 * @property \Carbon\Carbon $last_update
 * 
 * @property \Illuminate\Database\Eloquent\Collection $films
 *
 * @package App\Models
 */
class Language extends Eloquent
{
	protected $table = 'language';
	protected $primaryKey = 'language_id';
	public $timestamps = false;

	protected $dates = [
		'last_update'
	];

	protected $fillable = [
		'name',
		'last_update'
	];

	public function films()
	{
		return $this->hasMany(\App\Models\Film::class, 'original_language_id');
	}
}
