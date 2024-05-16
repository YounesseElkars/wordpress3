<?php

namespace BookneticAddon\Rooms\Backend;

use BookneticAddon\Rooms\Model\Room;
use BookneticApp\Providers\Core\Capabilities;
use BookneticApp\Providers\UI\DataTableUI;

class Controller extends \BookneticApp\Providers\Core\Controller
{
    public function index(): void
    {
        $dataTable = new DataTableUI( Room::select('*') );

        $dataTable->setTitle( bkntc__( 'Rooms' ) );
        $dataTable->addNewBtn( bkntc__( 'ADD ROOM' ) );

        $dataTable->addAction( 'edit', bkntc__( 'Edit' ) );

        if ( Capabilities::userCan( 'rooms_delete' ) ) {
            $dataTable->addAction( 'delete', bkntc__( 'Delete' ), fn ( $ids ) => Room::whereId( $ids )->delete(), DataTableUI::ACTION_FLAG_BULK_SINGLE );
        }

        $dataTable->searchBy( [ 'title' ] );

        $dataTable->addColumns( bkntc__( '№' ), DataTableUI::ROW_INDEX );
        $dataTable->addColumns( bkntc__( 'TITLE' ), 'title' );
        $dataTable->addColumns( bkntc__( 'CAPACITY' ), 'capacity' );
        $dataTable->addColumns( bkntc__( 'Created At' ), 'created_at' );

        $table = $dataTable->renderHTML();

        $this->view( 'index', [ 'table' => $table ] );
    }

}
