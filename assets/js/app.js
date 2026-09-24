global.MonsieurBizInstantSearch = class {
    constructor(instantUrl, searchInputSelector, resultClosestSelector, resultFindSelector, keyUpTimeOut, minQueryLength) {
        this.requests = new WeakMap();
        this.inputs = new WeakMap();
        document.querySelectorAll(searchInputSelector).forEach((searchInput) => {
            const searchForm = searchInput.closest(resultClosestSelector);
            const resultElement = searchForm?.querySelector(resultFindSelector);
            if (!resultElement) {
                return;
            }
            this.inputs.set(resultElement, searchInput);
            let timeout;
            const hide = () => {
                clearTimeout(timeout);
                this.requests.get(resultElement)?.abort();
                this.requests.delete(resultElement);
                resultElement.style.display = 'none';
                searchInput.setAttribute('aria-expanded', 'false');
            };
            searchInput.addEventListener('input', () => {
                hide();
                const query = searchInput.value;
                if (query.length < minQueryLength) {
                    resultElement.innerHTML = '';
                    return;
                }
                timeout = setTimeout(() => this.callSearch(query, minQueryLength, instantUrl, resultElement), keyUpTimeOut);
            });
            searchInput.addEventListener('focus', () => {
                this.callSearch(searchInput.value, minQueryLength, instantUrl, resultElement);
            });
            searchForm.addEventListener('focusout', (event) => {
                if (!searchForm.contains(event.relatedTarget)) {
                    hide();
                }
            });
            searchForm.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && resultElement.style.display !== 'none') {
                    // Close suggestions without the search input's native Escape clearing its query.
                    event.preventDefault();
                    hide();
                }
            });
        });
    }

    callSearch(query, minQueryLength, instantUrl, resultElement) {
        this.requests.get(resultElement)?.abort();
        if (query.length < minQueryLength) {
            this.requests.delete(resultElement);
            resultElement.innerHTML = '';
            resultElement.style.display = 'none';
            return;
        }
        const request = new XMLHttpRequest();
        this.requests.set(resultElement, request);
        request.onload = () => {
            if (this.requests.get(resultElement) !== request) {
                return;
            }
            const input = this.inputs.get(resultElement);
            if (request.status === 200) {
                resultElement.innerHTML = request.responseText;
                resultElement.style.display = 'block';
                input?.setAttribute('aria-expanded', 'true');
            } else {
                resultElement.style.display = 'none';
                input?.setAttribute('aria-expanded', 'false');
            }
        };
        request.onerror = () => {
            if (this.requests.get(resultElement) === request) {
                const input = this.inputs.get(resultElement);
                resultElement.style.display = 'none';
                input?.setAttribute('aria-expanded', 'false');
            }
        };
        request.open('POST', instantUrl);
        request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        request.send(new URLSearchParams({query}).toString());
    }
};

document.addEventListener('DOMContentLoaded', () => {
    if (typeof monsieurbizSearchPlugin === 'undefined' || !monsieurbizSearchPlugin.instantEnabled) {
        return;
    }
    new MonsieurBizInstantSearch(
        monsieurbizSearchPlugin.instantUrl,
        monsieurbizSearchPlugin.searchInputSelector,
        monsieurbizSearchPlugin.resultClosestSelector,
        monsieurbizSearchPlugin.resultFindSelector,
        monsieurbizSearchPlugin.keyUpTimeOut,
        monsieurbizSearchPlugin.minQueryLength
    );
});
