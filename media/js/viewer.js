if (typeof jQuery != 'undefined') {
    jQuery(document).ready(function ($) {
        $('.mod_teamspeak3 .join').click(function (e) {
            e.preventDefault();
            var nickname = $(this).closest('.viewer').find('.nickname').html();
            var name = prompt('Nickname', nickname);
            if (name) {
                var uri = $(this).attr('href');
                window.location.href = updateQueryStringParameter(uri, 'nickname', name);
            }
        });
    });
}

/* http://stackoverflow.com/a/6021027/1471846 */
function updateQueryStringParameter(uri, key, value) {
    var re = new RegExp('([?&])' + key + '=.*?(&|$)', 'i');
    var separator = uri.indexOf('?') !== -1 ? '&' : '?';
    if (uri.match(re)) {
        return uri.replace(re, '$1' + key + '=' + value + '$2');
    } else {
        return uri + separator + key + '=' + value;
    }
}