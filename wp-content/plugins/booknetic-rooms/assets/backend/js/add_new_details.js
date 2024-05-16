(($) => {
    const doc = $(document)

    doc.ready(() => {
        $("#services").select2({
            theme: 'bootstrap',
            placeholder: booknetic.__('select'),
            allowClear: true,
            allowMultiple: true,
            multiple: true
        });

        let currentColor = $('#input_color').val();

        if (currentColor !== '') {
            $(".fs-modal .room_color").css('background-color', currentColor);
        }

        $("#input_color_hex").colorpicker({
            format: 'hex'
        });

        doc.on('click', '#input_color, .room_color', function () {
            const panel = $("#roomColorPanel");
            let x = parseInt($(".fs-modal .fs-modal-content").outerWidth()) / 2 - panel.outerWidth() / 2,
                y = parseInt($(this).offset().top) + 60;

            panel.css({top: y + 'px', left: x + 'px'}).fadeIn(200);
        })
            .on('click', '#roomColorPanel .color-rounded', function () {
                $("#roomColorPanel .color-rounded.selected-color").removeClass('selected-color');
                $(this).addClass('selected-color');

                $("#input_color_hex").val($(this).data('color'));
            })
            .on('click', '#roomColorPanel .close-btn1', function () {
                $("#roomColorPanel .close-popover-btn").click();
            })
            .on('click', '#roomColorPanel .save-btn1', function () {
                const color = $("#input_color_hex").val();

                $(".fs-modal .room_color").css('background-color', color);
                $('#input_color').val(color);

                $("#roomColorPanel .close-popover-btn").click();
            });

    })
})(jQuery)