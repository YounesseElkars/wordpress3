<?php

use BookneticAddon\Rooms\RoomAddon;

defined( 'ABSPATH' ) or die();

/**
 * @var mixed $parameters
 */

$rooms = $parameters[ 'rooms' ] ?? [];
$selectedRooms = $parameters[ 'selectedRooms' ] ?? [];

?>

<script type="text/javascript"
        src="<?php echo RoomAddon::loadAsset( 'assets/backend/js/appointment_tab.js' ) ?>"></script>

<div>
    <div class="form-row">
        <div class="form-group col-md-12">
            <label for="rooms"><?php echo bkntc__( 'Select rooms' ) ?>:</label>
            <select class="form-control" id="rooms" multiple>
                <?php foreach ( $rooms as $room ): ?>
                    <option value="<?php echo $room->id ?>" <?php echo in_array( $room->id, $selectedRooms ) ? 'selected' : '' ?>><?php echo $room->title ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</div>