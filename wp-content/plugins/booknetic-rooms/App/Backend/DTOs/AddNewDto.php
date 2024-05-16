<?php

namespace BookneticAddon\Rooms\Backend\DTOs;

use BookneticAddon\Rooms\Model\Room;
use BookneticAddon\Rooms\Model\ServiceRoom;
use BookneticApp\Models\Service;

class AddNewDto
{
    public int $roomId = 0;

    public string $color = '#53d56c';

    public array $room = [];

    public array $services = [];

    public array $serviceRooms = [];

    public function __construct( $id )
    {
        $this->setServices();
        $this->setRoom( $id );
    }

    private function setServices(): void
    {
        $this->services = Service::select( [ 'id', 'name' ] )->fetchAll();
    }

    private function setRoom( $id ): void
    {
        $room = Room::whereId( $id )->fetch();

        if ( ! $room ) {
            return;
        }

        $this->roomId = (int) $room->id;
        $this->room = $room->toArray();
        $this->color = $room[ 'color' ] ?? '#53d56c';
        $this->serviceRooms = ServiceRoom::getServiceIds( $this->roomId );
    }

    public function isEdit(): bool
    {
        return $this->roomId > 0;
    }

    public function colorIs( $color ): bool
    {
        return $this->color === $color;
    }
}