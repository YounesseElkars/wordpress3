<?php

namespace BookneticAddon\Rooms;

use BookneticAddon\Rooms\Model\AppointmentRoom;
use BookneticAddon\Rooms\Model\Room;
use BookneticAddon\Rooms\Model\ServiceRoom;
use BookneticApp\Backend\Appointments\Helpers\CalendarService;
use BookneticApp\Models\Appointment;
use BookneticApp\Providers\Helpers\Helper;
use DateTimeImmutable;
use DateTimeZone;
use Exception;

class RoomService
{
    public static CalendarService $calendarService;

    public static array $rooms = [];

    public static array $roomAppointments = [];

    public static bool $initialized = false;

    public static bool $hasFreeRoom = false;

    public static function validate( bool $isDisabled, DateTimeImmutable $start, DateTimeImmutable $end, CalendarService $calendarService ): bool
    {
        if ( ! self::$initialized ) {
            self::$calendarService = $calendarService;

            self::init();
        }

        if ( empty( self::$rooms ) || empty( self::$roomAppointments ) || self::$hasFreeRoom ) {
            return $isDisabled;
        }

        $startTs = $start->setTimezone( new DateTimeZone( 'UTC' ) )->getTimestamp();
        $endTs = $end->setTimezone( new DateTimeZone( 'UTC' ) )->getTimestamp();

        $isDisabled = true;

        foreach ( self::$rooms as $room ) {
            $appointments = self::$roomAppointments[ $room->id ] ?? [];

            if ( empty( $appointments ) ) {
                $isDisabled = false;
                break;
            }

            self::$roomAppointments[ $room->id ] = $appointments = array_filter( $appointments, fn( $a ) => $a->ends_at >= $startTs );

            $filter = array_filter( $appointments, fn( $a ) => ( $a->starts_at >= $startTs && $a->starts_at < $endTs ) // new starts in prev
                || ( $a->ends_at > $startTs && $a->ends_at <= $endTs ) // new ends in prev
                || ( $a->starts_at <= $startTs && $a->ends_at >= $endTs ) // new contains prev
            );

            if ( count( $filter ) < $room->capacity ) {
                $isDisabled = false;
                break;
            }
        }

        return $isDisabled;
    }

    public static function init(): void
    {
        self::$initialized = true;
        self::$rooms = Room::select( [ Room::getField( 'id' ), Room::getField( 'capacity' ) ] )->leftJoin( ServiceRoom::getTableName(), [], ServiceRoom::getField( 'room_id' ), Room::getField( 'id' ) )
            ->where( ServiceRoom::getField( 'service_id' ), self::$calendarService->getServiceId() )
            ->fetchAll();

        if ( empty( self::$rooms ) ) {
            return;
        }

        try {
            $busyFrom = ( new DateTimeImmutable( self::$calendarService->dateTo . ' 24:00:00' ) )
                ->setTimezone( self::$calendarService->serverTz )
                ->modify( "+" . self::$calendarService->serviceMarginAfter . " minutes" )
                ->getTimestamp();

            $busyTo = ( new DateTimeImmutable( self::$calendarService->dateFrom . ' 00:00:00' ) )
                ->setTimezone( self::$calendarService->serverTz )
                ->modify( "-" . self::$calendarService->serviceMarginBefore . " minutes" )
                ->getTimestamp();
        } catch ( Exception $e ) {
            return;
        }

        $fields = [
            AppointmentRoom::getField( 'room_id' ),
            AppointmentRoom::getField( 'appointment_id' ),
            Appointment::getField( 'starts_at' ),
            Appointment::getField( 'ends_at' )
        ];

        $appointments = AppointmentRoom::select( $fields )
            ->leftJoin( Appointment::getTableName(), [], Appointment::getField( 'id' ), AppointmentRoom::getField( 'appointment_id' )
            )
            ->where( AppointmentRoom::getField( 'room_id' ), array_column( self::$rooms, 'id' ) )
            ->where( Appointment::getField( 'busy_from' ), '<=', $busyFrom )
            ->where( Appointment::getField( 'busy_to' ), '>=', $busyTo )
            ->where( Appointment::getField( 'status' ), Helper::getBusyAppointmentStatuses() )
            ->fetchAll();

        foreach ( $appointments as $appointment ) {
            self::$roomAppointments[ $appointment[ 'room_id' ] ][] = $appointment;
        }

        self::$hasFreeRoom = count( self::$rooms ) > count( self::$roomAppointments );
    }
}