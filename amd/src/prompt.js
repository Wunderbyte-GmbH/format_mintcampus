define(['jquery','core/ajax',
        'core/notification',
        'core/str',
        'core/templates',
        'core/modal_factory',
        'core/modal_events',
        'core/pending'],
    function ($,Ajax,
              Notification,
              Str,
              Templates,
              ModalFactory,
              ModalEvents,
              Pending) {
        /**
         * Init this module which allows activity completion state to be changed via ajax.
         * @method init
         * @param {string} fullName The current user's full name.
         * @private
         */
        const init = function () {

            var pendingPromise = new Pending('format_mintcampus/prompt:init');

            $('#mintcampuscourserating').click(function() {
                const courseid = $(this).attr("data-id");

                //get data for course rating
                Ajax.call([{
                    methodname: 'format_mintcampus_get_rating',
                    args: {courseid: courseid}
                }])[0].then(function (ratingprompt) {

                    ModalFactory.create({
                        title: Str.get_string('courserating', 'format_mintcampus'),
                        body: ratingprompt['ratingprompt'],
                        type: ModalFactory.types.SAVE_CANCEL,
                        large: true
                    }).then(function(modal) {
                        modal.setSaveButtonText(Str.get_string('save','core'));

                        const rangeInputs = document.querySelectorAll('input[type="range"]')

                        function handleInputChange(e) {
                            let target = e.target
                            const min = target.min
                            const max = target.max
                            const val = target.value

                            target.style.backgroundSize = (val - min) * 100 / (max - min) + '% 100%'
                        }

                        rangeInputs.forEach(input => {
                            input.addEventListener('input', handleInputChange)

                            let min = input.min
                            let max = input.max
                            let val = input.value

                            input.style.backgroundSize = (val - min) * 100 / (max - min) + '% 100%'
                        })

                        // Register the click, space and enter events as activators for the trigger element.
                        /*$('#mintcampus_radio_box').change(function(){
                        });*/

                        // Handle save event.
                        modal.getRoot().on(ModalEvents.save, function(e) {

                            var submission = $('#mintcampus_comment').val();

                            Ajax.call([{
                                methodname: 'format_mintcampus_save_comment',
                                args: {courseid: courseid, submission:submission}
                            }])[0].then(function () {

                            }).always(pendingPromise.resolve)
                                .catch(Notification.exception);

                            var rating = $("input[name='stars']:checked").val();

                            Ajax.call([{
                                methodname: 'format_mintcampus_save_rating',
                                args: {courseid: courseid, rating: rating}
                            }])[0].then(function () {

                            }).always(pendingPromise.resolve)
                                .catch(Notification.exception);

                            Notification.alert(
                                Str.get_string('ratingsavesuccessheader', 'format_mintcampus'),
                                Str.get_string('ratingsavesuccess', 'format_mintcampus'),
                                Str.get_string('close', 'format_mintcampus')
                            );

                        });

                        // Handle hidden event.
                        modal.getRoot().on(ModalEvents.hidden, function() {
                            // Destroy when hidden.
                            modal.destroy();
                        });

                        modal.show();

                    }).catch(Notification.exception);

                }).always(pendingPromise.resolve)
                    .catch(Notification.exception);
            });

        };
        return {
            init: init
        };
    });
