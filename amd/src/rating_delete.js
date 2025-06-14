/**
 * mintcampus Course Format settings
 *
 * @package    format_mintcampus
 * @version    See the value of '$plugin->version' in the version.php file.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 */

define(['jquery', 'core/ajax', 'core/notification', 'core/str', 'core/modal_factory', 'core/modal_events', 'core/pending'],
    function ($, Ajax, Notification, Str, ModalFactory, ModalEvents, Pending) {

        /**
         * Initializes the delete rating functionality.
         * @method init
         * @private
         */
        const init = function () {
            console.log("init");
            var pendingPromise = new Pending('format_mintcampus/rating_delete:init');

            // Full-body event listener to handle dynamically added elements
            $('body').on('click', '[data-ratingid]', function () {
                const ratingid = $(this).data('ratingid');
                console.log("Clicked on rating with ID:", ratingid);

                // Trigger custom event with ratingid
                $(this).trigger('deleteRating', { ratingid: ratingid });

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
                                
                                // Remove the rating item from the DOM
                                // Find the closest .ratings container and remove it
                                $(`[data-ratingid="${ratingid}"]`).parents('.rating-item').fadeOut(300, function () {
                                    setTimeout(() => $(this).remove(), 300);
                                }); 
             
                                // Close the modal after success
                                modal.hide();
                            } else {
                                Notification.alert(
                                    Str.get_string('deletionerror', 'format_mintcampus'),
                                    response.message,
                                    Str.get_string('close', 'format_mintcampus')
                                );
                            }
                        }).fail(function(error) {
                            Notification.exception(error);
                        }).always(pendingPromise.resolve);
                    });
                
                    // Handle hidden event (cleanup)
                    modal.getRoot().on(ModalEvents.hidden, function () {
                        modal.destroy();
                    });
                
                    modal.show();
                }).catch(Notification.exception);                
            });

            // Custom event listener for deleteRating event
            $('body').on('deleteRating', function (event, data) {
                console.log('Custom deleteRating event triggered for rating ID:', data.ratingid);
            });
        };

        return {
            init: init
        };
    }
);
