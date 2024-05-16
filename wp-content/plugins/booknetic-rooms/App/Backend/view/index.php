<?php

use BookneticAddon\Rooms\RoomAddon;

defined( 'ABSPATH' ) or die();

echo $parameters[ 'table' ];
?>

<script type="text/javascript" src="<?php echo RoomAddon::loadAsset( 'assets/backend/js/rooms.js' ) ?>"></script>
