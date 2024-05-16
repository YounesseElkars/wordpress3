<?php

namespace BookneticAddon\Rooms;

use BookneticAddon\Rooms\Model\AppointmentRoom;
use BookneticAddon\Rooms\Model\Room;
use BookneticAddon\Rooms\Model\ServiceRoom;
use BookneticApp\Backend\Appointments\Helpers\AppointmentRequestData;
use BookneticApp\Backend\Appointments\Helpers\AppointmentRequests;
use BookneticApp\Backend\Appointments\Helpers\ExtrasService;
use BookneticApp\Backend\Appointments\Helpers\TimeSlotService;
use BookneticApp\Models\Appointment;
use BookneticApp\Providers\Helpers\Helper;
use Exception;

/**
 * Class Listener
 *
 * This class provides methods for handling appointment information and validation.
 */
class Listener
{
    /**
     * Retrieves appointment information based on appointment ID
     *
     * @param int $appointmentId The ID of the appointment
     * @return array Returns an array containing the following keys:
     *   - 'rooms': An array of rooms associated with the appointment
     *   - 'selectedRooms': An array of room IDs that are selected for the appointment
     */
    public static function getAppointmentInfo( $appointmentId ): array
    {
        if ( ! $appointmentId ) {
            return [];
        }

        $appointment = Appointment::select( 'service_id' )->whereId( $appointmentId )->fetch();

        $rooms = Room::where( 'id', ServiceRoom::whereServiceId( $appointment->service_id )->select( 'room_id' ) )->fetchAll();
        $selectedRooms = AppointmentRoom::where( 'appointment_id', $appointmentId )->fetchAll();

        return [
            'rooms' => $rooms,
            'selectedRooms' => array_column( $selectedRooms, 'room_id' )
        ];
    }

    /**
     * Validates appointment requests.
     *
     * @param AppointmentRequests $ar The appointment requests to validate.
     * @throws Exception if a room for the selected timeslot is already at capacity.
     */
    public static function validate( AppointmentRequests $ar ): void
    {
        if ( ! $ar->calledFromBackend ) {
            return;
        }

        foreach ( $ar::appointments() as $appointment ) {
            /**
             * @var $timeslots TimeSlotService[]
             */
            $timeslots = $appointment->timeslots;
            $roomIds = $appointment->getData( 'rooms' );

            if ( empty( $timeslots ) ) {
                continue;
            }

            if ( empty( $roomIds ) ) {
                continue;
            }

            foreach ( $timeslots as $timeslot ) {
                if ( empty( $timeslot ) ) {
                    continue;
                }

                $busyStatuses = Helper::getBusyAppointmentStatuses();
                $start = $timeslot->getTimestamp();
                $end = $start + $timeslot->getServiceInf()->duration * 60 + ExtrasService::calcExtrasDuration( $appointment->getServiceExtras() );

                $overlappingAppointments = Appointment::select( 'id' )
                    ->where( 'status', 'in', $busyStatuses )
                    ->where( fn( $query ) => $query
                        ->where( fn( $query ) => $query->where( 'starts_at', '>=', $start )->where( 'starts_at', '<', $end ) ) // new starts in prev
                        ->orWhere( fn( $query ) => $query->where( 'ends_at', '>', $start )->where( 'ends_at', '<=', $end ) ) // new ends in prev
                        ->orWhere( fn( $query ) => $query->where( 'starts_at', '<=', $start )->where( 'ends_at', '>=', $end ) ) // new contains prev
                    );

                $bookedRooms = AppointmentRoom::select( [ 'room_id', 'count(*) as count' ] )
                    ->where( 'appointment_id', $overlappingAppointments )
                    ->where( 'room_id', $roomIds );

                if ( ! ! $appointment->appointmentId ) {
                    $bookedRooms = $bookedRooms->where( 'appointment_id', '<>', $appointment->appointmentId );
                }

                $bookedRooms = $bookedRooms->groupBy( [ 'room_id' ] )
                    ->fetchAll();
                $rooms = Room::select( [ 'id', 'capacity' ] )
                    ->where( 'id', $roomIds )
                    ->fetchAll();

                foreach ( $bookedRooms as $bookedRoom ) {
                    $match = array_filter( $rooms, fn( $r ) => $r[ 'id' ] === $bookedRoom[ 'room_id' ] );

                    if ( $match[ 0 ][ 'capacity' ] <= $bookedRoom[ 'count' ] ) {
                        throw new Exception( bkntc__( 'This room for the selected timeslot is already at capacity.' ) );
                    }
                }
            }
        }
    }

    /**
     * Validates the frontend appointment request data.
     *
     * @param AppointmentRequestData $appointment The appointment request data object.
     * @return void
     * @throws Exception If no rooms are available for the given time.
     */
    public static function validateFrontend( AppointmentRequestData $appointment ): void
    {
        foreach ( $appointment->timeslots as $timeslot ) {
            /**
             * @var $timeslot TimeSlotService
             */
            if ( empty( $timeslot ) ) {
                continue;
            }

            $start = $timeslot->getTimestamp();
            $end = $start + $timeslot->getServiceInf()->duration * 60 + ExtrasService::calcExtrasDuration( $appointment->getServiceExtras() );
            $rooms = ServiceRoom::whereServiceId( $timeslot->getServiceId() )->count();
            $availableRooms = self::getAvailableRooms( $start, $end, $timeslot->getServiceId() );

            if ( $rooms > 0 && count( $availableRooms ) === 0 ) {
                throw new Exception( bkntc__( 'All rooms for the selected time is at capacity. Please choose another time.' ) );
            }
        }
    }

    /**
     * Retrieves the available rooms for a given time frame and service.
     *
     * @param string $start The start time of the time frame.
     * @param string $end The end time of the time frame.
     * @param int $serviceId The ID of the service.
     * @return array An array of available rooms for the given time frame and service.
     */
    public static function getAvailableRooms( $start, $end, $serviceId ): array
    {
        $busyStatuses = Helper::getBusyAppointmentStatuses();

        $overlappingAppointments = Appointment::where( 'status', 'in', $busyStatuses )
            ->where( fn( $query ) => $query
                ->where( fn( $query ) => $query->where( 'starts_at', '>=', $start )->where( 'starts_at', '<', $end ) ) // new starts in prev
                ->orWhere( fn( $query ) => $query->where( 'ends_at', '>', $start )->where( 'ends_at', '<=', $end ) ) // new ends in prev
                ->orWhere( fn( $query ) => $query->where( 'starts_at', '<=', $start )->where( 'ends_at', '>=', $end ) ) // new contains prev
            )->select( 'id' );

        $appointmentRooms = AppointmentRoom::select( 'room_id' )
            ->where( 'appointment_id', $overlappingAppointments );

        $serviceRooms = ServiceRoom::whereServiceId( $serviceId )->select( 'room_id' );

        $rooms = Room::where( 'id', $serviceRooms )->fetchAll();
        $bookedRoomsData = $appointmentRooms->select( [ 'room_id', 'count(*) as count' ] )->groupBy( [ 'room_id' ] )->fetchAll();

        $bookedRooms = [];

        foreach ( $bookedRoomsData as $roomData ) {
            $bookedRooms[ $roomData[ 'room_id' ] ] = $roomData[ 'count' ];
        }

        // Filter out rooms that don't have necessary capacity for the new booking
        return array_filter( $rooms, fn( $r ) => $r[ 'capacity' ] > ( $bookedRooms[ $r[ 'id' ] ] ?? 0 ) );
    }

    /**
     * Updates the appointment rooms.
     *
     * @param AppointmentRequestData $appointment The appointment data.
     * @return void
     */
    public static function updateAppointmentRooms( AppointmentRequestData $appointment ): void
    {
        if ( ! $appointment->calledFromBackend ) {
            self::updateAppointmentRoomsFrontend( $appointment );
            return;
        }

        $roomIds = $appointment->getData( 'rooms' );

        if ( empty( $roomIds ) || empty( $appointment->appointmentId ) ) {
            return;
        }

        $rooms = Room::select( 'id' )->whereId( $roomIds )->fetchAll();

        if ( empty( $rooms ) ) {
            return;
        }

        AppointmentRoom::where( 'appointment_id', $appointment->appointmentId )->delete();

        foreach ( $rooms as $room ) {
            AppointmentRoom::insert( [
                'appointment_id' => $appointment->appointmentId,
                'room_id' => $room->id
            ] );
        }
    }

    /**
     * Update appointment rooms from frontend.
     *
     * @param AppointmentRequestData $appointment The appointment request data.
     * @return void
     */
    public static function updateAppointmentRoomsFrontend( AppointmentRequestData $appointment ): void
    {
        /**
         * @var $timeslot TimeSlotService
         */
        $timeslot = $appointment->timeslots[ 0 ];

        $start = $timeslot->getTimestamp();
        $end = $start + $timeslot->getServiceInf()->duration * 60 + ExtrasService::calcExtrasDuration( $appointment->getServiceExtras() );
        $roomsCount = ServiceRoom::whereServiceId( $timeslot->getServiceId() )->count();
        $availableRooms = self::getAvailableRooms( $start, $end, $timeslot->getServiceId() );

        if ( $roomsCount === 0 || count( $availableRooms ) === 0 ) {
            return;
        }

        $room = reset( $availableRooms );

        AppointmentRoom::insert( [
            'appointment_id' => $appointment->appointmentId,
            'room_id' => $room->id
        ] );
    }

    /**
     * Deletes the appointment room with the given appointment ID.
     *
     * @param int $appointmentId The ID of the appointment to be deleted.
     * @return void
     */
    public static function appointmentDeleted( $appointmentId ): void
    {
        AppointmentRoom::where( 'appointment_id', $appointmentId )->delete();
    }

    //todo://no corresponding filters for this method. Add one when you get the chance
    /**
     * Deletes the service from the ServiceRoom table based on the given service ID.
     *
     * @param int $serviceId The ID of the service to be deleted.
     * @return void
     */
    public static function serviceDeleted( $serviceId ): void
    {
        ServiceRoom::whereServiceId( $serviceId )->delete();
    }
}