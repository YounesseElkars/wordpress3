<?php

defined( 'ABSPATH' ) or die();

use BookneticAddon\Rooms\Backend\DTOs\AddNewDto;
use BookneticAddon\Rooms\RoomAddon;
use BookneticApp\Providers\Helpers\Helper;
use function BookneticAddon\Rooms\bkntc__;

/**
 * @var AddNewDto $parameters
 */

?>

<link rel="stylesheet" type="text/css"
      href="<?php echo Helper::assets( 'css/bootstrap-colorpicker.min.css', 'Services' ) ?>"/>
<script type="application/javascript"
        src="<?php echo Helper::assets( 'js/bootstrap-colorpicker.min.js', 'Services' ) ?>"></script>

<script type="text/javascript" src="<?php echo RoomAddon::loadAsset( 'assets/backend/js/add_new_details.js' ) ?>"
        id="add_new_details_tab_JS" data-id="<?php echo $parameters->roomId ?>"></script>

<div class="form-row">
    <div class="form-group col-md-6">
        <label for="title"><?php echo bkntc__( 'Title' ) ?></label>
        <input type="text" class="form-control" id="title" placeholder="<?php echo bkntc__( 'Title' ) ?>"
               value="<?php echo $parameters->isEdit() ? $parameters->room[ 'title' ] : '' ?>">
    </div>
    <div class="form-group col-md-6 position-relative">
        <label for="input_color"><?php echo bkntc__( 'Color' ) ?></label>
        <input type="text" class="form-control" placeholder="#FFFFFF" id="input_color"
               value="<?php echo htmlspecialchars( $parameters->color ) ?>">
        <span class="room_color"></span>
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-12">
        <label for="capacity"><?php echo bkntc__( 'Capacity' ) ?></label>
        <input type="number" class="form-control" id="capacity" placeholder="<?php echo bkntc__( 'Capacity' ) ?>"
               value="<?php echo $parameters->isEdit() ? $parameters->room[ 'capacity' ] : '' ?>">
    </div>
</div>
<div class="form-row">
    <div class="form-group col-md-12">
        <label for="services">
            <?php echo bkntc__( 'Select services you want to associate with your room' ) ?>:
        </label>
        <select class="form-control" id="services" multiple>
            <?php foreach ( $parameters->services as $service ): ?>
                <option value="<?php echo $service->id ?>" <?php echo in_array( $service->id, $parameters->serviceRooms ) ? 'selected' : '' ?>>
                    <?php echo $service->name; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>
