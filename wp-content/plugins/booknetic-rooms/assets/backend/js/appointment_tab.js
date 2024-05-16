(($) => {
    'use strict'

    const doc = $(document);

    doc.ready(() => {
        const _rooms = $('#rooms');
        const onChange = () => {
            const id = $('#input_service').val();
            const time = $('#input_time').val();

            booknetic.ajax('rooms.get_service_rooms', {id, time}, (data) => {
                _rooms.empty();

                if (!data?.rooms?.length) {
                    _rooms.trigger('change');
                    return;
                }

                data?.rooms?.forEach((o) => _rooms.append(new Option(o.title, o.id)).trigger('change'));
            })
        }

        _rooms.select2({
            theme: 'bootstrap',
            placeholder: booknetic.__('select'),
            allowClear: true,
            allowMultiple: true,
            multiple: true
        });

        doc.on('change', '#input_service', onChange);
        // .on('change', '#input_time', onChange); //todo://add this functionality later

        booknetic.addFilter('appointments.validation', () => {
            if (_rooms.children('option').length === 0) {
                return false;
            }

            if (_rooms.select2('val').length > 0) {
                return false;
            }

            return 'No Room\'s Selected';
        });
        booknetic.addFilter('appointments.create_appointment.cart', (obj, data) => ({rooms: _rooms.select2('val'), ...obj}))
        booknetic.addFilter('appointments.save_edited_appointment.cart', (obj, data) => ({rooms: _rooms.select2('val'), ...obj}))
    });
})(jQuery)