<?php

namespace App{
/**
 * App\Room
 *
 * @property integer $id
 * @property string $RoomName
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Room whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Room whereRoomName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Room whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Room whereUpdatedAt($value)
 */

	class Room extends \Eloquent
	{
		protected $fillable = ['RoomName'];
	}

}

