define([
    "core/templates",
    "block_deft/refresh"
], function(Templates, Refresh) {
    return function() {
        return {
            /**
             * Replace content
             *
             * @param {DOMNode} content
             * @param {string} html New content
             * @param {string} js Scripts to run after replacement
             */
            replace: function(content, html, js) {
                const comments = content.querySelector('.videotimetab_chat'),
                    height = comments.scrollHeight,
                    position = comments.scrollTop;

                Templates.replaceNodeContents(content, html, js);
                content.querySelector('.videotimetab_chat').scrollTop = position +
                    content.querySelector('.videotimetab_chat').scrollHeight - height;
            }
        };
    }();
});