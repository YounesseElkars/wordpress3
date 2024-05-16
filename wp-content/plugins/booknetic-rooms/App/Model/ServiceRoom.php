<?php

namespace BookneticAddon\Rooms\Model;

use BookneticApp\Providers\DB\Model;

/**
 * @property-read int $id
 * @property int $room_id
 * @property int $service_id
 */
class ServiceRoom extends Model
{
    public static $relations = [
        'rooms' => [ Room::class, 'id', 'room_id' ]
    ];

    public static function whereServiceId( $id )
    {
        return self::where( 'service_id', $id );
    }

    public static function whereRoomId( $id )
    {
        return self::where( 'room_id', $id );
    }

    public static function insertMultipleRooms( $serviceId, $roomIds ): void
    {
        foreach ( $roomIds as $roomId ) {
            self::insert( [
                'service_id' => $serviceId,
                'room_id' => $roomId
            ] );
        }
    }

    public static function insertMultipleServices( $roomId, $serviceIds ): void
    {
        foreach ( $serviceIds as $serviceId ) {
            self::insert( [
                'service_id' => $serviceId,
                'room_id' => $roomId
            ] );
        }
    }

    public static function getServiceIds( $id ): array
    {
        $rows = self::whereRoomId( $id )
            ->select( 'service_id' )
            ->fetchAll();

        return array_column( $rows, 'service_id' );
    }

    public static function getRoomIds( $id ): array
    {
        $rows = self::whereServiceId( $id )
            ->select( 'room_id' )
            ->fetchAll();

        return array_column( $rows, 'room_id' );
    }
}
