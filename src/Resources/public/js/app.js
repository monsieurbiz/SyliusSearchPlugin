/** global: monsieurbizSearchPlugin */
(function ($) {
    'use strict';
    $.fn.extend({
        instantSearch: function () {
            // No instant if disabled
            if (!monsieurbizSearchPlugin.instantEnabled) {
                return;
            }
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

            // Hide results when user leave the autocomplete form
            const searchForm = document.querySelector(monsieurbizSearchPlugin.searchInputSelector).closest(monsieurbizSearchPlugin.resultClosestSelector);
            searchForm.addEventListener('focusout', function (e) {
                // hide autocomplete results, only if click outside
                if (e.relatedTarget === null || !searchForm.contains(e.relatedTarget)) {
                    const resultElement = searchForm.querySelector(monsieurbizSearchPlugin.resultFindSelector);
                    resultElement.style.display = 'none';
                }
            });

            // Reopen the autocomplete result, if the query is not empty
            document.querySelector(monsieurbizSearchPlugin.searchInputSelector).addEventListener('focus', function (e) {
                var query = e.currentTarget.value;
                if (query !== '') {
                    const resultElement = searchForm.querySelector(monsieurbizSearchPlugin.resultFindSelector);
                    resultElement.style.display = 'block';
                }
            });
        },
        filterSearch: function () {
            $(monsieurbizSearchPlugin.priceFilterSelector).prop('autocomplete', 'off');

            // If only a button can submit filters
            if (monsieurbizSearchPlugin.refreshWithButton) {
                $(monsieurbizSearchPlugin.filterForm).submit(function(event) {
                    $(monsieurbizSearchPlugin.loaderSelector).dimmer('show');
                });
                return;
            }

            // Init a timeout variable when typing a price
            var priceFilterTimeout = null;
            $(monsieurbizSearchPlugin.priceFilterSelector).keyup(function() {
                clearTimeout(priceFilterTimeout);
                var input = $(this);
                priceFilterTimeout = setTimeout(function () {
                    $(this).applyFilter(input.attr('name'),  input.val());
                }, monsieurbizSearchPlugin.keyUpTimeOut);
            });

            $(monsieurbizSearchPlugin.attributeFilterSelector).change(function() {
                $(this).applyFilter($(this).attr('name'),  $(this).val());
            });
        },
        applyFilter: function (field, value) {
            // Changed field and value are available in case we need it
            $(monsieurbizSearchPlugin.loaderSelector).dimmer('show');
            $(monsieurbizSearchPlugin.filterForm).submit();
        }
    });
})(jQuery);

(function($) {
    $(document).ready(function () {
        $(this).instantSearch();
        $(this).filterSearch();
    });
})(jQuery);
