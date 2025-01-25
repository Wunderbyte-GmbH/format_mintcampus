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
        const init = function (courseid) {

            var pendingPromise = new Pending('format_mintcampus/activitysetting:init');

            //get data from database and set toggle for activities
            Ajax.call([{
                methodname: 'format_mintcampus_get_activity_setting',
                args: {courseid: courseid}
            }])[0].then(function (toggledata) {

                if(toggledata.length > 0 && toggledata[0]['cmid'] != 0){

                    toggledata.forEach(data=>
                        $("#courseindexsetting"+data['cmid']).prop("checked", true)
                    );
                }

            }).always(pendingPromise.resolve)
                .catch(Notification.exception);

            //change settings in database for activity toggle
            $('[id^="courseindexsetting"]').change(function() {
                var cmid = $(this).attr("data-id");
                if(this.checked) {
                    // Toggle is on
                    Ajax.call([{
                        methodname: 'format_mintcampus_set_activity_setting',
                        args: {courseid:courseid, cmid:cmid, state:true}
                    }])[0].then(function (toggle) {

                    }).always(pendingPromise.resolve)
                        .catch(Notification.exception);

                } else {
                    // Toggle is off
                    Ajax.call([{
                        methodname: 'format_mintcampus_set_activity_setting',
                        args: {courseid:courseid, cmid:cmid, state:false}
                    }])[0].then(function (toggle) {

                    }).always(pendingPromise.resolve)
                        .catch(Notification.exception);
                }
            });

        };
        return {
            init: init
        };
    });
