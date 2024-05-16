<?php

use BookneticAddon\Rooms\RoomAddon;

defined( 'ABSPATH' ) or die();

/**
 * @var mixed $parameters
 */

?>

<script type="text/javascript" src="<?php echo RoomAddon::loadAsset( 'assets/backend/js/service_add.js' ) ?>"></script>

<div>
    <div class="form-row">
        <div class="form-group col-md-12">
            <label for="rooms"><?php echo bkntc__( 'Select rooms you want to associate with your service' ) ?>:</label>
            <select class="form-control" id="rooms" multiple>
                <?php foreach ( $parameters[ 'rooms' ] as $room ): ?>
                    <option value="<?php echo $room[ 'id' ] ?>" <?php echo in_array( $room[ 'id' ], $parameters[ 'service_rooms' ] ) ? 'selected' : '' ?>>
                        <?php echo $room[ 'title' ]; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</div>