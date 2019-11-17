(function ($) {
    'use strict';
    $.fn.extend({
        instantSearch: function () {
            $(monsieurbizSearchPlugin.searchInputSelector).prop('autocomplete', 'off');
            // Init a timeout variable to be used below
            var instantSearchTimeout = null;
            $(monsieurbizSearchPlugin.searchInputSelector).keyup(function() {
                clearTimeout(instantSearchTimeout);
                var query = $(this).val();
                var resultElement = $(this).closest(monsieurbizSearchPlugin.resultClosestSelector).find(monsieurbizSearchPlugin.resultFindSelector);
                instantSearchTimeout = setTimeout(function () {
                    if (query.length >= monsieurbizSearchPlugin.minQueryLength) {
                        $.post(monsieurbizSearchPlugin.instantUrl, { query: query })
                            .done(function( data ) {
                                resultElement.html(data);
                                resultElement.show();
                            });
                    }
                }, monsieurbizSearchPlugin.keyUpTimeOut);
            });
        }
    });
})(jQuery);

(function($) {
    $(document).ready(function () {
        $(this).instantSearch();
    });
})(jQuery);
