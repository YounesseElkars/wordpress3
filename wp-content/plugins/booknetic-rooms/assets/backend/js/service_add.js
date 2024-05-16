(($) => {
    'use strict'

    const doc = $(document);

    doc.ready(() => {
        $("#rooms").select2({
            theme: 'bootstrap',
            placeholder: booknetic.__('select'),
            allowClear: true,
            allowMultiple: true,
            multiple: true
        });

        booknetic.addAction('ajax_after_save_service_success', (booknetic, _, result) => {
            if (!result['id']) {
                return;
            }

            const params = new FormData();

            params.append('rooms', $('#rooms').select2('val'));
            params.append('service_id', result['id'])


            booknetic.ajax('rooms.save_service_rooms', params)
        })
    });
})(jQuery)