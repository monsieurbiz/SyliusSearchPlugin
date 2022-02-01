global.MonsieurBizInstantSearch = class {
    constructor(
        instantUrl,
        searchInputSelector,
        resultClosestSelector,
        resultFindSelector,
        keyUpTimeOut,
        minQueryLength
    ) {
        // Init a timeout variable to be used below
        var instantSearchTimeout = null;
        const searchInput = document.querySelector(searchInputSelector);
        if (!searchInput) {
            return;
        }
        searchInput.addEventListener('keyup', function (e) {
            clearTimeout(instantSearchTimeout);
            var query = e.currentTarget.value;
            var resultElement = e.currentTarget.closest(resultClosestSelector).querySelector(resultFindSelector);
            instantSearchTimeout = setTimeout(function () {
                if (query.length >= minQueryLength) {
                    var httpRequest = new XMLHttpRequest();
                    httpRequest.onload = function () {
                        if (this.status === 200) {
                            resultElement.innerHTML = this.responseText;
                            resultElement.style.display = 'block';
                        }
                    };
                    httpRequest.open("POST", instantUrl);
                    httpRequest.setRequestHeader("X-Requested-With", "XMLHttpRequest");
                    httpRequest.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                    httpRequest.send(new URLSearchParams({query: query}).toString());
                }
            }, keyUpTimeOut);
        });

        // Hide results when user leave the autocomplete form
        const searchForm = searchInput.closest(resultClosestSelector);
        searchForm.addEventListener('focusout', function (e) {
            if (e.relatedTarget === null || !searchForm.contains(e.relatedTarget)) {
                const resultElement = searchForm.querySelector(resultFindSelector);
                resultElement.style.display = 'none';
            }
        });

        searchInput.addEventListener('focus', function (e) {
            var query = e.currentTarget.value;
            if (query !== '') {
                const resultElement = searchForm.querySelector(resultFindSelector);
                resultElement.style.display = 'block';
            }
        });
    }
}

document.addEventListener("DOMContentLoaded", function() {
    new MonsieurBizInstantSearch(
        monsieurbizSearchPlugin.instantUrl,
        monsieurbizSearchPlugin.searchInputSelector,
        monsieurbizSearchPlugin.resultClosestSelector,
        monsieurbizSearchPlugin.resultFindSelector,
        monsieurbizSearchPlugin.keyUpTimeOut,
        monsieurbizSearchPlugin.minQueryLength
    );
});
