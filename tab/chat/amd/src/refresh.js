/*
 * Refresh content when changed on the system
 *
 * @package    block_deft
 * @module     block_deft/refresh
 * @copyright  2022 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Templates from "core/templates";
import Refresh from "block_deft/refresh";

export default class extends Refresh {
    /**
     * Replace content
     *
     * @param {DOMNode} content
     * @param {string} html New content
     * @param {string} js Scripts to run after replacement
     */
    replace(content, html, js) {
        const comments = content.querySelector('.videotimetab_chat'),
            height = comments.scrollHeight,
            position = comments.scrollTop;

        Templates.replaceNodeContents(content, html, js);
        content.querySelector('.videotimetab_chat').scrollTop = position +
            content.querySelector('.videotimetab_chat').scrollHeight - height;
    }
}
