<?php

namespace BookneticAddon\Rooms;

use BookneticAddon\Rooms\Model\AppointmentRoom;
use BookneticAddon\Rooms\Model\Room;
use BookneticApp\Models\Appointment;
use BookneticApp\Providers\DB\Model;

class CalendarEvents
{
    public static bool $initialized = false;

    //[ 'appointment_id' => 'room_color', ...]
    public static array $appointmentRooms = [];

    public static function filter( array $event, $appointment, $query ): array
    {
        if ( ! self::$initialized ) {
            self::init( $query );
        }

        if ( empty( self::$appointmentRooms ) ) {
            return $event;
        }

        if ( empty( self::$appointmentRooms[ $appointment[ 'id' ] ] ) ) {
            return $event;
        }

        $event[ 'color' ] = self::$appointmentRooms[ $appointment[ 'id' ] ];

        return $event;
    }

    private static function init( $query ): void
    {
        /**
         * @var $query Model
         */

        self::$initialized = true;

        $appointments = $query->select( 'id' )->fetchAll();

        $fields = [
            AppointmentRoom::getField( 'appointment_id' ),
            Room::getField( 'color' )
        ];

        $appointmentRooms = AppointmentRoom::select( $fields )
            ->leftJoin( Room::getTableName(), [], Room::getField( 'id' ), AppointmentRoom::getField( 'room_id' ) )
            ->where( AppointmentRoom::getField( 'appointment_id' ), array_column( $appointments, 'id' ) )
            ->fetchAll();

        foreach ( $appointmentRooms as $appointment ) {
            self::$appointmentRooms[ $appointment[ 'appointment_id' ] ] = $appointment[ 'color' ];
        }
    }
}