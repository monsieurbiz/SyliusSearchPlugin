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
        document.querySelector(searchInputSelector).addEventListener('keyup', function (e) {
            clearTimeout(instantSearchTimeout);
            var query = e.currentTarget.value;
            var resultElement = e.currentTarget.closest(resultClosestSelector).querySelector(resultFindSelector);
            instantSearchTimeout = setTimeout(function () {
                if (query.length >= minQueryLength) {
                    var httpRequest = new XMLHttpRequest();
                    httpRequest.onload = function() {
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

        // Hide results when user leave the search field
        document.querySelector(searchInputSelector).addEventListener('focusout', function (e) {
            var resultElement = e.currentTarget.closest(resultClosestSelector).querySelector(resultFindSelector);
            setTimeout(function () {
                resultElement.style.display = 'none';
            }, 100); // Add timeout to keep the click on the result
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
