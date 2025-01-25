/**
 * mintcampus Course Format settings
 *
 * @package    format_mintcampus
 * @version    See the value of '$plugin->version' in the version.php file.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 */

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
        const init = function (cmid) {

            var pendingPromise = new Pending('format_mintcampus/activitynavigation:init');

            var ispresent = $('.activity-header');
            if(ispresent.length){

               Ajax.call([{
                   methodname: 'format_mintcampus_get_activity_navigation',
                   args: {cmid: cmid}
               }])[0].then(function (activitydata) {
                   return activitydata;
               }).then(async function (activitydata) {

                   //apply activity data
                   $('#mintcampusactivityheader').html(activitydata['activityheader']);
                   $('#mintcampusactivityfooter').html(activitydata['activityfooter']);

                   if(activitydata['previouscm']!=0){
                       $("#courseindex-content").find("li[data-id='"+activitydata['previouscm']+"']").addClass("pageitem");
                   }

                   $("div.float-right a").removeClass("btn-link").addClass("btn-primary").html(await Str.get_string('captionnext', 'format_mintcampus'));
                   $("div.float-left a").removeClass("btn-link").addClass("btn-primary").html(await Str.get_string('captionback', 'format_mintcampus'));

                   const courseid = $('#mintcampuscourserating').attr("data-id");

                   $('#mintcampuscourserating').click(function() {

                       //get data for course rating
                       Ajax.call([{
                           methodname: 'format_mintcampus_get_rating',
                           args: {courseid: courseid}
                       }])[0].then(async function (ratingprompt) {

                           ModalFactory.create({
                               title: await Str.get_string('courserating', 'format_mintcampus'),
                               body: ratingprompt['ratingprompt'],
                               type: ModalFactory.types.SAVE_CANCEL,
                               large: true
                           }).then(async function(modal) {
                               modal.setSaveButtonText(await Str.get_string('save', 'core'));

                               const rangeInputs = document.querySelectorAll('input[type="range"]');

                               function handleInputChange(e) {
                                   let target = e.target;
                                   const min = target.min;
                                   const max = target.max;
                                   const val = target.value;

                                   target.style.backgroundSize = (val - min) * 100 / (max - min) + '% 100%';
                               }

                               rangeInputs.forEach(input => {
                                   input.addEventListener('input', handleInputChange);

                                   let min = input.min;
                                   let max = input.max;
                                   let val = input.value;

                                   input.style.backgroundSize = (val - min) * 100 / (max - min) + '% 100%';
                               });

                               // Register the click, space and enter events as activators for the trigger element.
                               /*$('#mintcampus_radio_box').change(function(){
                               });*/

                               // Handle save event.
                               modal.getRoot().on(ModalEvents.save, async function(e) {

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
                                       await Str.get_string('ratingsavesuccessheader', 'format_mintcampus'),
                                       await Str.get_string('ratingsavesuccess', 'format_mintcampus'),
                                       await Str.get_string('close', 'format_mintcampus')
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

                   //wait for courseindex to load
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
                   //end

               }).always(pendingPromise.resolve).catch(Notification.exception);

            }
        };
        return {
            init: init
        };
    });
