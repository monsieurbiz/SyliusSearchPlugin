const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');

function createSearch(inputs = []) {
    const requests = [];
    class Request {
        constructor() { requests.push(this); }
        open(method, url) { this.method = method; this.url = url; }
        setRequestHeader() {}
        send(body) { this.body = body; }
        abort() { this.aborted = true; }
        complete(text, status = 200) { this.status = status; this.responseText = text; this.onload(); }
        fail() { this.onerror(); }
    }
    const context = {global: {}, document: {querySelectorAll: () => inputs, addEventListener() {}}, XMLHttpRequest: Request, URLSearchParams, WeakMap, setTimeout, clearTimeout};
    vm.runInNewContext(fs.readFileSync(path.join(__dirname, '../../assets/js/app.js'), 'utf8'), context);
    const search = new context.global.MonsieurBizInstantSearch('/instant', 'input', 'form', '.results', 500, 3);
    return {search, requests, result: {style: {}, innerHTML: ''}};
}

test('instant search posts an encoded query and renders the HTML response', () => {
    const {search, requests, result} = createSearch();
    search.callSearch('shirt & cap', 3, '/instant', result);
    assert.equal(requests[0].method, 'POST');
    assert.equal(requests[0].body, 'query=shirt+%26+cap');
    requests[0].complete('<a>Shirt</a>');
    assert.equal(result.innerHTML, '<a>Shirt</a>');
    assert.equal(result.style.display, 'block');
});

test('a late response cannot overwrite a newer query', () => {
    const {search, requests, result} = createSearch();
    search.callSearch('shirt', 3, '/instant', result);
    search.callSearch('jeans', 3, '/instant', result);
    assert.equal(requests[0].aborted, true);
    requests[1].complete('Jeans');
    requests[0].complete('Shirt');
    assert.equal(result.innerHTML, 'Jeans');
});

test('shortening a query clears results and invalidates the pending response', () => {
    const {search, requests, result} = createSearch();
    search.callSearch('shirt', 3, '/instant', result);
    search.callSearch('sh', 3, '/instant', result);
    requests[0].complete('Shirt');
    assert.equal(result.innerHTML, '');
    assert.equal(result.style.display, 'none');
});

test('failed requests do not open the results panel', () => {
    const {search, requests, result} = createSearch();
    search.callSearch('shirt', 3, '/instant', result);
    requests[0].complete('Unavailable', 503);
    assert.equal(result.style.display, 'none');
    assert.equal(result.innerHTML, '');
});

test('network failures close the results panel and reset expanded state', () => {
    const inputListeners = {};
    const attributes = {'aria-expanded': 'true'};
    const result = {style: {display: 'block'}, innerHTML: 'Previous results'};
    const form = {
        querySelector: () => result,
        addEventListener() {},
    };
    const input = {
        value: 'shirt',
        closest: () => form,
        addEventListener: (event, handler) => { inputListeners[event] = handler; },
        setAttribute: (name, value) => { attributes[name] = value; },
    };

    const {requests} = createSearch([input]);
    inputListeners.focus();
    requests[0].fail();

    assert.equal(result.style.display, 'none');
    assert.equal(attributes['aria-expanded'], 'false');
});

test('focus, Escape and leaving the form control the panel and accessibility state', () => {
    const inputListeners = {};
    const formListeners = {};
    const attributes = {};
    const result = {style: {}, innerHTML: ''};
    const resultLink = {};
    const form = {
        querySelector: () => result,
        addEventListener: (event, handler) => { formListeners[event] = handler; },
        contains: (element) => element === input || element === resultLink,
    };
    const input = {
        value: 'shirt',
        closest: () => form,
        addEventListener: (event, handler) => { inputListeners[event] = handler; },
        setAttribute: (name, value) => { attributes[name] = value; },
    };
    const {requests} = createSearch([input]);
    inputListeners.focus();
    requests[0].complete('Shirt');
    assert.equal(result.style.display, 'block');
    assert.equal(attributes['aria-expanded'], 'true');
    formListeners.focusout({relatedTarget: resultLink});
    assert.equal(result.style.display, 'block');
    let defaultPrevented = false;
    formListeners.keydown({key: 'Escape', preventDefault() { defaultPrevented = true; }});
    assert.equal(defaultPrevented, true);
    assert.equal(result.style.display, 'none');
    assert.equal(attributes['aria-expanded'], 'false');
    inputListeners.focus();
    requests[1].complete('Shirt');
    assert.equal(result.style.display, 'block');
    formListeners.focusout({relatedTarget: null});
    assert.equal(result.style.display, 'none');
    input.value = 's';
    inputListeners.input();
    assert.equal(result.innerHTML, '');
});
