/*
 * Change comments
 *
 * @package    videotimetab_chat
 * @module     videotimetab_chat/refresh
 * @copyright  2022 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from "core/ajax";
import Notification from "core/notification";
import Log from "core/log";

export default {

    /**
     * Listen for comment actions
     *
     * @param {int} contextid Context id of module
     * @param {string} selector Content location to replace
     * @param {int} instanceid Context instance id
     */
    init: function(contextid, selector, instanceid) {
        const observer = new ResizeObserver((entries) => {
            for (const entry of entries) {
                const comments = entry.target.querySelector('.videotimetab_chat');
                if (comments && comments.scrollHeight && comments.scrollTop == 0) {
                    Log.debug(comments.scrollHeight);
                    comments.scrollTop = comments.scrollHeight;
                }
            }
        });
        observer.observe(document.querySelector(selector));
        document.querySelector(selector).addEventListener('click', (e) => {
            const button = e.target.closest('[data-type="comments"] [data-action]');
            if (button) {
                const textarea = button.closest('[data-type="comments"]').querySelector('textarea');
                e.preventDefault();
                e.stopPropagation();
                switch (button.getAttribute('data-action')) {
                    case 'addcomment':
                        if (textarea.textLength) {
                            Ajax.call([{
                                methodname: 'videotimetab_chat_add_comments',
                                args: {
                                    comments: [{
                                        content: textarea.value,
                                        contextlevel: 'module',
                                        instanceid: instanceid,
                                        itemid: 0,
                                        component: 'videotimetab_chat',
                                        area: 'chat'
                                    }],
                                },
                                fail: Notification.exception,
                                done: () => {
                                    textarea.value = '';
                                }
                            }]);
                        }
                        break;
                    case 'delete':
                        Ajax.call([{
                            contextid: contextid,
                            methodname: 'videotimetab_chat_delete_comments',
                            args: {
                                comments: [
                                    button.closest('[data-comment]').getAttribute('data-comment')
                                ],
                            },
                            fail: Notification.exception,
                        }]);
                        break;
                }
            }
        });
    }
};
