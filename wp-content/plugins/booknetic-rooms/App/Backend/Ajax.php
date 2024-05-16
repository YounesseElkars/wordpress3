<?php

namespace BookneticAddon\Rooms\Backend;

use BookneticAddon\Rooms\Backend\DTOs\AddNewDto;
use BookneticAddon\Rooms\Model\Room;
use BookneticAddon\Rooms\Model\ServiceRoom;
use BookneticApp\Providers\Core\Capabilities;
use BookneticApp\Providers\Core\CapabilitiesException;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\UI\TabUI;

class Ajax extends \BookneticApp\Providers\Core\Controller
{
    /**
     * @throws CapabilitiesException
     */
    public function add_new()
    {
        $id = Helper::_post( 'id', '0', 'integer' );

        if ( $id > 0 ) {
            Capabilities::must( 'rooms_edit' );
        } else {
            Capabilities::must( 'rooms_add' );
        }

        TabUI::get( 'room_add_new' )
            ->item( 'details' )
            ->setTitle( bkntc__( 'Room Details' ) )
            ->addView( __DIR__ . '/view/tabs/room_add_new_details.php' )
            ->setPriority( 1 );

        return $this->modalView( 'add_new', new AddNewDto( $id ) );
    }

    public function save()
    {
        $id = Helper::_post( 'id', '', 'int' );
        $title = Helper::_post( 'title', '', 'string' );
        $services = Helper::_post( 'services', '', 'string' );
        $capacity = Helper::_post( 'capacity', 1, 'int' );
        $color = Helper::_post( 'color', '#53d56c', 'string' );

        if ( empty( $id ) ) {
            Room::insert( [
                'title' => $title,
                'capacity' => $capacity,
                'color' => $color
            ] );

            $id = Room::lastId();
        } else {
            $room = Room::whereId( $id )->fetch();

            if ( empty( $room ) ) {
                return $this->response( false, bkntc__( 'Room Not Found!' ) );
            }

            Room::whereId( $id )->update( [
                'title' => $title,
                'capacity' => $capacity,
                'color' => $color
            ] );

            ServiceRoom::whereRoomId( $id )->delete();
        }

        ServiceRoom::insertMultipleServices( $id, explode( ',', $services ) );

        return $this->response( true );
    }

    public function save_service_rooms()
    {
        $serviceId = Helper::_post( 'service_id', '', 'int' );
        $roomsStr = Helper::_post( 'rooms', '', 'string' );

        if ( empty( $serviceId ) || empty( $roomsStr ) ) {
            return $this->response( true );
        }

        $ids = Room::getFieldArray( explode( ',', $roomsStr ), 'id' );

        ServiceRoom::whereServiceId( $serviceId )->delete();
        ServiceRoom::insertMultipleRooms( $serviceId, $ids );

        return $this->response( true );
    }

    public function get_service_rooms()
    {
        $id = Helper::_post( 'id', '', 'int' );

        if ( empty( $id ) ) {
            return $this->response( true );
        }

        $rooms = ServiceRoom::select( [ Room::getField( 'id' ), Room::getField( 'title' ) ] )
            ->leftJoin( 'rooms', [] )
            ->where( 'service_id', $id )
            ->fetchAll();

        return $this->response( true, [ 'rooms' => $rooms ] );
    }
}