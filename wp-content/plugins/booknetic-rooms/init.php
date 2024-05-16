<?php
/*
 * Plugin Name: Room Management for Booknetic
 * Description: Manage Your Rooms And Equipment For Required Services
 * Version: 1.0.0
 * Author: Asker Ali
 * Author URI: https://www.booknetic.com
 * License: Commercial
 * Text Domain: booknetic-rooms
 */

defined( 'ABSPATH' ) or exit;

require_once __DIR__ . '/vendor/autoload.php';

add_filter( 'bkntc_addons_load', function ( $addons ) {
	$addons[ \BookneticAddon\Rooms\RoomAddon::getAddonSlug() ] = new \BookneticAddon\Rooms\RoomAddon();

	return $addons;
} );
