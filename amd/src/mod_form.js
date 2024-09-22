define([
    'core/str',
], function(
    str
) {
    var init = function() {
        document.querySelectorAll('h2 a[data-toggle="popover"]').forEach(async function(container) {
            const helpmessage = await str.get_string('modulenamepro_help', 'videotime');
            container.setAttribute('data-content', helpmessage);
        });
    };

    return {
        init: init
    };
});
