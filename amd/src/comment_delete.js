/**
 * Mintcampus Course Format - Comment Management
 *
 * This module manages the deletion of course comments within the Mintcampus format.
 * It provides functionalities such as confirmation modals, AJAX requests, and 
 * DOM updates to remove deleted comments dynamically.
 *
 * @package    format_mintcampus
 * @version    Refer to '$plugin->version' in the version.php file.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 */

define(['jquery', 'core/ajax', 'core/notification', 'core/str', 'core/modal_factory', 'core/modal_events', 'core/pending'],
    function ($, Ajax, Notification, Str, ModalFactory, ModalEvents, Pending) {

        /**
         * Initializes the delete comment functionality by attaching event listeners
         * to dynamically loaded elements and handling comment deletions via AJAX.
         * 
         * @method init
         * @private
         */
        const init = function () {
            console.log("Delete comment module initialized");

            var pendingPromise = new Pending('format_mintcampus/comment_delete:init');

            // Attach event listener for dynamically added elements with data-commentid
            $('body').on('click', '[data-commentid]', function () {
                const commentid = $(this).data('commentid');
                console.log("Delete button clicked for comment ID:", commentid);

                // Trigger custom event with the comment ID
                $(this).trigger('deleteComment', { commentid: commentid });

                // Display confirmation modal before proceeding with deletion
                ModalFactory.create({
                    title: Str.get_string('confirmdeletion', 'format_mintcampus'),
                    body: Str.get_string('confirmdeletecommentmsg', 'format_mintcampus'),
                    type: ModalFactory.types.SAVE_CANCEL
                }).then(function (modal) {
                    modal.setSaveButtonText(Str.get_string('delete', 'core'));
                
                    // Handle delete confirmation event
                    modal.getRoot().on(ModalEvents.save, function (e) {
                        e.preventDefault();
                
                        // Perform AJAX request to delete the comment
                        Ajax.call([{
                            methodname: 'format_mintcampus_delete_comment',
                            args: { commentid: commentid }
                        }])[0].then(function (response) {
                            if (response.status === 'success') {
                                Notification.alert(
                                    Str.get_string('deletionsuccess', 'format_mintcampus'),
                                    Str.get_string('commentsuccessfullydeleted', 'format_mintcampus'),
                                    Str.get_string('close', 'format_mintcampus')
                                );

                                // Remove the closest .comment-item element from the DOM
                                $(`[data-commentid="${commentid}"]`).parents('.comment').fadeOut(300, function () {
                                    setTimeout(() => $(this).remove(), 300);
                                });

                                // Close the modal upon successful deletion
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
                
                    // Cleanup modal instance when closed
                    modal.getRoot().on(ModalEvents.hidden, function () {
                        modal.destroy();
                    });
                
                    modal.show();
                }).catch(Notification.exception);                
            });

            // Custom event listener for comment deletion trigger
            $('body').on('deleteComment', function (event, data) {
                console.log('Custom deleteComment event triggered for comment ID:', data.commentid);
            });
        };

        return {
            init: init
        };
    }
);
