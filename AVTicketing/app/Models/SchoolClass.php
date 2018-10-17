<?php

namespace App{
/**
 * App\SchoolClass
 *
 * @property integer $id
 * @property string $ClassName
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\SchoolClass whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\SchoolClass whereClassName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\SchoolClass whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\SchoolClass whereUpdatedAt($value)
 */

	class SchoolClass extends \Eloquent
	{
		protected $table = "schoolClasses";
		protected $fillable = ['ClassName'];
	}

}

