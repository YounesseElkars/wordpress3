<?php

namespace BookneticAddon\Rooms\Model;

use BookneticApp\Providers\DB\Model;

/**
 * @property-read int $id
 * @property int $room_id
 * @property int $appointment_id
 */
class AppointmentRoom extends Model
{
    public static $relations = [
        'room' => [ Room::class, 'id', 'room_id' ]
    ];
}
