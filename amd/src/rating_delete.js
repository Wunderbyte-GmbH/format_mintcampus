define(['jquery', 'core/ajax', 'core/notification', 'core/str', 'core/modal_factory', 'core/modal_events', 'core/pending'],
    function ($, Ajax, Notification, Str, ModalFactory, ModalEvents, Pending) {

        /**
         * Initializes the delete rating functionality.
         * @method init
         * @private
         */
        const init = function () {
            var pendingPromise = new Pending('format_mintcampus/rating_delete:init');

            // Event listener for delete button click
            $('.delete-rating').on('click', function () {
                const ratingid = $(this).data('ratingid');

                // Confirmation modal
                ModalFactory.create({
                    title: Str.get_string('confirmdeletion', 'format_mintcampus'),
                    body: Str.get_string('confirmdeletemsg', 'format_mintcampus'),
                    type: ModalFactory.types.SAVE_CANCEL
                }).then(function (modal) {
                    modal.setSaveButtonText(Str.get_string('delete', 'core'));

                    // Handle save (delete confirmation)
                    modal.getRoot().on(ModalEvents.save, function (e) {
                        e.preventDefault();

                        // Perform AJAX request to delete rating
                        Ajax.call([{
                            methodname: 'format_mintcampus_delete_rating',
                            args: { ratingid: ratingid }
                        }])[0].then(function (response) {
                            if (response.status === 'success') {
                                Notification.alert(
                                    Str.get_string('deletionsuccess', 'format_mintcampus'),
                                    Str.get_string('ratingsuccessfullydeleted', 'format_mintcampus'),
                                    Str.get_string('close', 'format_mintcampus')
                                );
                                $(`.rating-item[data-ratingid="${ratingid}"]`).fadeOut();
                            } else {
                                Notification.alert(
                                    Str.get_string('deletionerror', 'format_mintcampus'),
                                    response.message,
                                    Str.get_string('close', 'format_mintcampus')
                                );
                            }
                        }).fail(Notification.exception)
                          .always(pendingPromise.resolve);
                    });

                    // Handle hidden event (cleanup)
                    modal.getRoot().on(ModalEvents.hidden, function () {
                        modal.destroy();
                    });

                    modal.show();
                }).catch(Notification.exception);
            });
        };

        return {
            init: init
        };
    }
);
