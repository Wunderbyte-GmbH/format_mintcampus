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

            function waitForElm(selector) {
                return new Promise(resolve => {
                    if (document.querySelector(selector)) {
                        return resolve(document.querySelector(selector));
                    }

                    const observer = new MutationObserver(mutations => {
                        if (document.querySelector(selector)) {
                            resolve(document.querySelector(selector));
                            observer.disconnect();
                        }
                    });

                    observer.observe(document.body, {
                        childList: true,
                        subtree: true
                    });
                });
            }

            waitForElm('#courseindex').then((elm) => {
                $("div.courseindex-section[data-number='0']").first().hide();

                //get data from database and set toggle for activities
                Ajax.call([{
                    methodname: 'format_mintcampus_get_activity_setting',
                    args: {courseid: courseid}
                }])[0].then(function (toggledata) {

                    if(toggledata.length > 0 && toggledata[0]['cmid'] != 0){
                        toggledata.forEach(data=>
                            $("#courseindex-content").find("li[data-id='"+data['cmid']+"']").removeClass("d-flex").addClass("d-flex-noedit")
                        );
                    }

                }).always(pendingPromise.resolve)
                    .catch(Notification.exception);

            });

        };
        return {
            init: init
        };
    });
