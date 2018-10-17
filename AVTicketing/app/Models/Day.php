<?php

namespace App{

/**
 * App\Day
 *
 * @property integer $id
 * @property integer $periodLength
 * @property string $day
 * @property string $startTime
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Day whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Day whereDay($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Day whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Day whereUpdatedAt($value)
 */

	class Day extends \Eloquent
	{
		protected $fillable = ['day', 'periodLength', 'startTime'];
	}

}

