define([
    'core/str',
], function(
    str
) {
    var init = function() {
        document.querySelectorAll('h2 a[data-toggle="popover"]').forEach(function(container) {
            str.get_string('modulenamepro_help', 'videotime').then(function(helpmessage) {
                container.setAttribute('data-content', helpmessage);
            });
        });
    };

    return {
        init: init
    };
});
