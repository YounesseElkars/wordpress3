<?php

defined( 'ABSPATH' ) or die();

use BookneticAddon\Rooms\Backend\DTOs\AddNewDto;
use BookneticAddon\Rooms\RoomAddon;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\UI\TabUI;
use function BookneticAddon\Rooms\bkntc__;

/**
 * @var AddNewDto $parameters
 */
?>

<link rel="stylesheet" href="<?php echo RoomAddon::loadAsset( 'assets/backend/css/add_new.css' ) ?>">
<script type="text/javascript" src="<?php echo RoomAddon::loadAsset( 'assets/backend/js/add_new.js' ) ?>"
        id="add_new_JS" data-id="<?php echo $parameters->roomId ?>"></script>

<div class="fs-modal-title">
    <div class="title-icon badge-lg badge-purple"><i class="fa fa-plus"></i></div>
    <div class="title-text"><?php echo $parameters->isEdit() ? bkntc__( 'Edit Room' ) : bkntc__( 'Add Room' ) ?></div>
    <div class="close-btn" data-dismiss="modal"><i class="fa fa-times"></i></div>
</div>

<div class="fs-modal-body">
    <div class="fs-modal-body-inner">
        <form>
            <ul class="nav nav-tabs nav-light" data-tab-group="room_add_new">
                <?php foreach ( TabUI::get( 'room_add_new' )->getSubItems() as $tab ): ?>
                    <li class="nav-item"><a class="nav-link" data-tab="<?php echo $tab->getSlug(); ?>"
                                            href="#"><?php echo $tab->getTitle(); ?></a></li>
                <?php endforeach; ?>
            </ul>

            <div class="tab-content mt-5">
                <?php foreach ( TabUI::get( 'room_add_new' )->getSubItems() as $tab ): ?>
                    <div class="tab-pane" data-tab-content="room_add_new_<?php echo $tab->getSlug(); ?>"
                         id="tab_<?php echo $tab->getSlug(); ?>"><?php echo $tab->getContent( $parameters ); ?></div>
                <?php endforeach; ?>
            </div>

        </form>
    </div>
</div>

<div class="fs-modal-footer">
    <button type="button" class="btn btn-lg btn-outline-secondary"
            data-dismiss="modal"><?php echo bkntc__( 'CANCEL' ) ?></button>
    <button type="button" class="btn btn-lg btn-primary"
            id="save"><?php echo $parameters->isEdit() ? bkntc__( 'Save' ) : bkntc__( 'ADD ROOM' ) ?></button>
</div>
<div class="fs-popover" id="roomColorPanel">
    <div class="fs-popover-title">
        <span><?php echo bkntc__( 'Select colors' ) ?> </span>
        <img src="<?php echo Helper::icon( 'cross.svg' ) ?>" class="close-popover-btn" alt="">
    </div>
    <div class="fs-popover-content">
        <div class="room-colors-line">
            <div class="color-rounded color-r-1<?php echo( $parameters->colorIs( '#53d56c' ) ? ' selected-color' : '' ) ?>"
                 data-color="#53d56c"></div>
            <div class="color-rounded color-r-2<?php echo( $parameters->colorIs( '#26c0d6' ) ? ' selected-color' : '' ) ?>"
                 data-color="#26c0d6"></div>
            <div class="color-rounded color-r-3<?php echo( $parameters->colorIs( '#fd9b78' ) ? ' selected-color' : '' ) ?>"
                 data-color="#fd9b78"></div>
            <div class="color-rounded color-r-4<?php echo( $parameters->colorIs( '#cc65aa' ) ? ' selected-color' : '' ) ?>"
                 data-color="#cc65aa"></div>
            <div class="color-rounded color-r-5<?php echo( $parameters->colorIs( '#2078fa' ) ? ' selected-color' : '' ) ?>"
                 data-color="#2078fa"></div>
        </div>
        <div class="room-colors-line mt-3">
            <div class="color-rounded color-r-6 <?php echo( $parameters->colorIs( '#947bbf' ) ? 'selected-color' : '' ) ?>"
                 data-color="#947bbf"></div>
            <div class="color-rounded color-r-7 <?php echo( $parameters->colorIs( '#c9c2b8' ) ? 'selected-color' : '' ) ?>"
                 data-color="#c9c2b8"></div>
            <div class="color-rounded color-r-8 <?php echo( $parameters->colorIs( '#527dde' ) ? 'selected-color' : '' ) ?>"
                 data-color="#527dde"></div>
            <div class="color-rounded color-r-9 <?php echo( $parameters->colorIs( '#425a64' ) ? 'selected-color' : '' ) ?>"
                 data-color="#425a64"></div>
            <div class="color-rounded color-r-10 <?php echo( $parameters->colorIs( '#ffbb44' ) ? 'selected-color' : '' ) ?>"
                 data-color="#ffbb44"></div>
        </div>

        <div class="form-row mt-3">
            <div class="form-group col-md-12">
                <label for="input_color_hex"><?php echo bkntc__( 'Hex' ) ?></label>
                <input type="text" class="form-control" id="input_color_hex" value="#53d56c">
            </div>
        </div>

        <div class="fs-popover-footer">
            <button type="button" class="btn btn-default btn-lg close-btn1"><?php echo bkntc__( 'CLOSE' ) ?></button>
            <button type="button" class="btn btn-primary btn-lg ml-2 save-btn1"><?php echo bkntc__( 'SAVE' ) ?></button>
        </div>
    </div>
</div>