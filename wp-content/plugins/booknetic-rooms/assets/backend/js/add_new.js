(function ($) {
    'use strict';

    $(document).ready(function () {
        $('.fs-modal').on('click', '#save', function () {
            const title = $("#title").val();
            const services = $("#services").val();
            const capacity = $("#capacity").val();
            const color = $('#input_color').val();

            const data = new FormData();

            data.append('id', $("#add_new_JS").data('id'));
            data.append('title', title);
            data.append('services', services);
            data.append('capacity', capacity);
            data.append('color', color)

            booknetic.ajax('save', data, function () {
                booknetic.modalHide($(".fs-modal"));

                booknetic.dataTable.reload($("#fs_data_table_div"));
            });
        });
    });

})(jQuery);