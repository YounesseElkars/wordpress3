<?php

namespace BookneticAddon\Rooms;

use BookneticAddon\Rooms\Backend\Ajax;
use BookneticAddon\Rooms\Backend\Controller;
use BookneticApp\Providers\Core\AddonLoader;
use BookneticApp\Providers\Core\Capabilities;
use BookneticApp\Providers\Core\Route;
use BookneticApp\Providers\UI\MenuUI;
use BookneticApp\Providers\UI\TabUI;

function bkntc__( $text, $params = [], $esc = true )
{
    return \bkntc__( $text, $params, $esc, RoomAddon::getAddonSlug() );
}

class RoomAddon extends AddonLoader
{
    public function init(): void
    {
        Capabilities::register( 'rooms', bkntc__( 'Room Management' ) );

        Capabilities::register('rooms_add', bkntc__('Add New') , 'rooms');
        Capabilities::register('rooms_edit', bkntc__('Edit') , 'rooms');
        Capabilities::register('rooms_delete', bkntc__('Delete') , 'rooms');
        Capabilities::register('appointments_rooms_tab', bkntc__('Rooms tab') , 'appointments');

        if ( ! Capabilities::tenantCan( 'rooms' ) ) {
            return;
        }

        add_action( 'bkntc_appointment_request_before_data_validate', [ Listener::class, 'validateFrontend' ] );
        add_action( 'bkntc_appointment_requests_validate', [ Listener::class, 'validate' ] );
        add_action( 'bkntc_appointment_created', [ Listener::class, 'updateAppointmentRooms' ] );
    }

    public function initFrontend(): void
    {
        if ( ! Capabilities::tenantCan( 'rooms' ) ) {
            return;
        }

        add_filter( 'bkntc_calendar_service_disable_slot', [ RoomService::class, 'validate' ], 10, 4 );
    }

    public function initBackend(): void
    {
        if ( ! Capabilities::tenantCan( 'rooms' ) ) {
            return;
        }
		
		// Custom CSS for electron
		if (isset($_GET['electron']) && $_GET['electron'] == 'true') {
			// Check directly if this is an AJAX request
			if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
				echo '<link rel="stylesheet" href="https://milo.ma/wp-content/plugins/booknetic-rooms/App/wordpress.css?ver=2.4" type="text/css">';
			}
		}


        Route::post( 'rooms', Ajax::class );

        if ( ! Capabilities::userCan( 'rooms' ) ) {
            return;
        }

        Route::get( 'rooms', Controller::class );

        MenuUI::get( 'rooms' )
            ->setTitle( bkntc__( 'Rooms' ) )
            ->setIcon( 'fa fa-hospital' )
            ->setPriority( 810 );

        TabUI::get( 'settings_booking_steps' )
            ->item( 'confirm' )
            ->addView( __DIR__ . '/Backend/view/tabs/coupon_section.php' );

        TabUI::get( 'appointments_edit' )
            ->item( 'room_management' )
            ->setTitle( bkntc__( 'Rooms' ) )
            ->addView( __DIR__ . '/Backend/view/tabs/appointment_tab.php', [ Listener::class, 'getAppointmentInfo' ] );

        TabUI::get( 'appointments_add_new' )
            ->item( 'room_management' )
            ->setTitle( bkntc__( 'Rooms' ) )
            ->addView( __DIR__ . '/Backend/view/tabs/appointment_tab.php' );

        add_action( 'bkntc_appointment_deleted', [ Listener::class, 'appointmentDeleted' ] );
        add_action( 'bkntc_appointment_after_edit', [ Listener::class, 'updateAppointmentRooms' ] );

        add_filter( 'bkntc_filter_calendar_event_object', [ CalendarEvents::class, 'filter' ], 10, 3 );
		

    }
}
