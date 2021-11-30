/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "/public/";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./assets/js/app.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./assets/js/app.js":
/*!**************************!*\
  !*** ./assets/js/app.js ***!
  \**************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

/* WEBPACK VAR INJECTION */(function(global) {function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

global.MonsieurBizInstantSearch = /*#__PURE__*/function () {
  "use strict";

  function _class(instantUrl, searchInputSelector, resultClosestSelector, resultFindSelector, keyUpTimeOut, minQueryLength) {
    _classCallCheck(this, _class);

    // Init a timeout variable to be used below
    var instantSearchTimeout = null;
    document.querySelector(searchInputSelector).addEventListener('keyup', function (e) {
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
          httpRequest.send(new URLSearchParams({
            query: query
          }).toString());
        }
      }, keyUpTimeOut);
    }); // Hide results when user leave the search field

    document.querySelector(searchInputSelector).addEventListener('focusout', function (e) {
      var resultElement = e.currentTarget.closest(resultClosestSelector).querySelector(resultFindSelector);
      setTimeout(function () {
        resultElement.style.display = 'none';
      }, 100); // Add timeout to keep the click on the result
    });
  }

  return _class;
}();

document.addEventListener("DOMContentLoaded", function () {
  new MonsieurBizInstantSearch(monsieurbizSearchPlugin.instantUrl, monsieurbizSearchPlugin.searchInputSelector, monsieurbizSearchPlugin.resultClosestSelector, monsieurbizSearchPlugin.resultFindSelector, monsieurbizSearchPlugin.keyUpTimeOut, monsieurbizSearchPlugin.minQueryLength);
});
/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(/*! ./../../node_modules/webpack/buildin/global.js */ "./node_modules/webpack/buildin/global.js")))

/***/ }),

/***/ "./node_modules/webpack/buildin/global.js":
/*!***********************************!*\
  !*** (webpack)/buildin/global.js ***!
  \***********************************/
/*! no static exports found */
/***/ (function(module, exports) {

var g;

// This works in non-strict mode
g = (function() {
	return this;
})();

try {
	// This works if eval is allowed (see CSP)
	g = g || new Function("return this")();
} catch (e) {
	// This works if the window reference is available
	if (typeof window === "object") g = window;
}

// g can still be undefined, but nothing to do about it...
// We return undefined, instead of nothing here, so it's
// easier to handle this case. if(!global) { ...}

module.exports = g;


/***/ })

/******/ });
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vd2VicGFjay9ib290c3RyYXAiLCJ3ZWJwYWNrOi8vLy4vYXNzZXRzL2pzL2FwcC5qcyIsIndlYnBhY2s6Ly8vKHdlYnBhY2spL2J1aWxkaW4vZ2xvYmFsLmpzIl0sIm5hbWVzIjpbImdsb2JhbCIsIk1vbnNpZXVyQml6SW5zdGFudFNlYXJjaCIsImluc3RhbnRVcmwiLCJzZWFyY2hJbnB1dFNlbGVjdG9yIiwicmVzdWx0Q2xvc2VzdFNlbGVjdG9yIiwicmVzdWx0RmluZFNlbGVjdG9yIiwia2V5VXBUaW1lT3V0IiwibWluUXVlcnlMZW5ndGgiLCJpbnN0YW50U2VhcmNoVGltZW91dCIsImRvY3VtZW50IiwicXVlcnlTZWxlY3RvciIsImFkZEV2ZW50TGlzdGVuZXIiLCJlIiwiY2xlYXJUaW1lb3V0IiwicXVlcnkiLCJjdXJyZW50VGFyZ2V0IiwidmFsdWUiLCJyZXN1bHRFbGVtZW50IiwiY2xvc2VzdCIsInNldFRpbWVvdXQiLCJsZW5ndGgiLCJodHRwUmVxdWVzdCIsIlhNTEh0dHBSZXF1ZXN0Iiwib25sb2FkIiwic3RhdHVzIiwiaW5uZXJIVE1MIiwicmVzcG9uc2VUZXh0Iiwic3R5bGUiLCJkaXNwbGF5Iiwib3BlbiIsInNldFJlcXVlc3RIZWFkZXIiLCJzZW5kIiwiVVJMU2VhcmNoUGFyYW1zIiwidG9TdHJpbmciLCJtb25zaWV1cmJpelNlYXJjaFBsdWdpbiJdLCJtYXBwaW5ncyI6IjtRQUFBO1FBQ0E7O1FBRUE7UUFDQTs7UUFFQTtRQUNBO1FBQ0E7UUFDQTtRQUNBO1FBQ0E7UUFDQTtRQUNBO1FBQ0E7UUFDQTs7UUFFQTtRQUNBOztRQUVBO1FBQ0E7O1FBRUE7UUFDQTtRQUNBOzs7UUFHQTtRQUNBOztRQUVBO1FBQ0E7O1FBRUE7UUFDQTtRQUNBO1FBQ0EsMENBQTBDLGdDQUFnQztRQUMxRTtRQUNBOztRQUVBO1FBQ0E7UUFDQTtRQUNBLHdEQUF3RCxrQkFBa0I7UUFDMUU7UUFDQSxpREFBaUQsY0FBYztRQUMvRDs7UUFFQTtR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QzNCLGNBTjVCO0FBUUgsQ0FURCxFOzs7Ozs7Ozs7Ozs7QUMxQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0EsQ0FBQzs7QUFFRDtBQUNBO0FBQ0E7QUFDQSxDQUFDO0FBQ0Q7QUFDQTtBQUNBOztBQUVBO0FBQ0E7QUFDQSw0Q0FBNEM7O0FBRTVDIiwiZmlsZSI6ImpzL21vbnNpZXVyYml6LXNlYXJjaC5qcyIsInNvdXJjZXNDb250ZW50IjpbIiBcdC8vIFRoZSBtb2R1bGUgY2FjaGVcbiBcdHZhciBpbnN0YWxsZWRNb2R1bGVzID0ge307XG5cbiBcdC8vIFRoZSByZXF1aXJlIGZ1bmN0aW9uXG4gXHRmdW5jdGlvbiBfX3dlYnBhY2tfcmVxdWlyZV9fKG1vZHVsZUlkKSB7XG5cbiBcdFx0Ly8gQ2hlY2sgaWYgbW9kdWxlIGlzIGluIGNhY2hlXG4gXHRcdGlmKGluc3RhbGxlZE1vZHVsZXNbbW9kdWxlSWRdKSB7XG4gXHRcdFx0cmV0dXJuIGluc3RhbGxlZE1vZHVsZXNbbW9kdWxlSWRdLmV4cG9ydHM7XG4gXHRcdH1cbiBcdFx0Ly8gQ3JlYXRlIGEgbmV3IG1vZHVsZSAoYW5kIHB1dCBpdCBpbnRvIHRoZSBjYWNoZSlcbiBcdFx0dmFyIG1vZHVsZSA9IGluc3RhbGxlZE1vZHVsZXNbbW9kdWxlSWRdID0ge1xuIFx0XHRcdGk6IG1vZHVsZUlkLFxuIFx0XHRcdGw6IGZhbHNlLFxuIFx0XHRcdGV4cG9ydHM6IHt9XG4gXHRcdH07XG5cbiBcdFx0Ly8gRXhlY3V0ZSB0aGUgbW9kdWxlIGZ1bmN0aW9uXG4gXHRcdG1vZHVsZXNbbW9kdWxlSWRdLmNhbGwobW9kdWxlLmV4cG9ydHMsIG1vZHVsZSwgbW9kdWxlLmV4cG9ydHMsIF9fd2VicGFja19yZXF1aXJlX18pO1xuXG4gXHRcdC8vIEZsYWcgdGhlIG1vZHVsZSBhcyBsb2FkZWRcbiBcdFx0bW9kdWxlLmwgPSB0cnVlO1xuXG4gXHRcdC8vIFJldHVybiB0aGUgZXhwb3J0cyBvZiB0aGUgbW9kdWxlXG4gXHRcdHJldHVybiBtb2R1bGUuZXhwb3J0cztcbiBcdH1cblxuXG4gXHQvLyBleHBvc2UgdGhlIG1vZHVsZXMgb2JqZWN0IChfX3dlYnBhY2tfbW9kdWxlc19fKVxuIFx0X193ZWJwYWNrX3JlcXVpcmVfXy5tID0gbW9kdWxlcztcblxuIFx0Ly8gZXhwb3NlIHRoZSBtb2R1bGUgY2FjaGVcbiBcdF9fd2VicGFja19yZXF1aXJlX18uYyA9IGluc3RhbGxlZE1vZHVsZXM7XG5cbiBcdC8vIGRlZmluZSBnZXR0ZXIgZnVuY3Rpb24gZm9yIGhhcm1vbnkgZXhwb3J0c1xuIFx0X193ZWJwYWNrX3JlcXVpcmVfXy5kID0gZnVuY3Rpb24oZXhwb3J0cywgbmFtZSwgZ2V0dGVyKSB7XG4gXHRcdGlmKCFfX3dlYnBhY2tfcmVxdWlyZV9fLm8oZXhwb3J0cywgbmFtZSkpIHtcbiBcdFx0XHRPYmplY3QuZGVmaW5lUHJvcGVydHkoZXhwb3J0cywgbmFtZSwgeyBlbnVtZXJhYmxlOiB0cnVlLCBnZXQ6IGdldHRlciB9KTtcbiBcdFx0fVxuIFx0fTtcblxuIFx0Ly8gZGVmaW5lIF9fZXNNb2R1bGUgb24gZXhwb3J0c1xuIFx0X193ZWJwYWNrX3JlcXVpcmVfXy5yID0gZnVuY3Rpb24oZXhwb3J0cykge1xuIFx0XHRpZih0eXBlb2YgU3ltYm9sICE9PSAndW5kZWZpbmVkJyAmJiBTeW1ib2wudG9TdHJpbmdUYWcpIHtcbiBcdFx0XHRPYmplY3QuZGVmaW5lUHJvcGVydHkoZXhwb3J0cywgU3ltYm9sLnRvU3RyaW5nVGFnLCB7IHZhbHVlOiAnTW9kdWxlJyB9KTtcbiBcdFx0fVxuIFx0XHRPYmplY3QuZGVmaW5lUHJvcGVydHkoZXhwb3J0cywgJ19fZXNNb2R1bGUnLCB7IHZhbHVlOiB0cnVlIH0pO1xuIFx0fTtcblxuIFx0Ly8gY3JlYXRlIGEgZmFrZSBuYW1lc3BhY2Ugb2JqZWN0XG4gXHQvLyBtb2RlICYgMTogdmFsdWUgaXMgYSBtb2R1bGUgaWQsIHJlcXVpcmUgaXRcbiBcdC8vIG1vZGUgJiAyOiBtZXJnZSBhbGwgcHJvcGVydGllcyBvZiB2YWx1ZSBpbnRvIHRoZSBuc1xuIFx0Ly8gbW9kZSAmIDQ6IHJldHVybiB2YWx1ZSB3aGVuIGFscmVhZHkgbnMgb2JqZWN0XG4gXHQvLyBtb2RlICYgOHwxOiBiZWhhdmUgbGlrZSByZXF1aXJlXG4gXHRfX3dlYnBhY2tfcmVxdWlyZV9fLnQgPSBmdW5jdGlvbih2YWx1ZSwgbW9kZSkge1xuIFx0XHRpZihtb2RlICYgMSkgdmFsdWUgPSBfX3dlYnBhY2tfcmVxdWlyZV9fKHZhbHVlKTtcbiBcdFx0aWYobW9kZSAmIDgpIHJldHVybiB2YWx1ZTtcbiBcdFx0aWYoKG1vZGUgJiA0KSAmJiB0eXBlb2YgdmFsdWUgPT09ICdvYmplY3QnICYmIHZhbHVlICYmIHZhbHVlLl9fZXNNb2R1bGUpIHJldHVybiB2YWx1ZTtcbiBcdFx0dmFyIG5zID0gT2JqZWN0LmNyZWF0ZShudWxsKTtcbiBcdFx0X193ZWJwYWNrX3JlcXVpcmVfXy5yKG5zKTtcbiBcdFx0T2JqZWN0LmRlZmluZVByb3BlcnR5KG5zLCAnZGVmYXVsdCcsIHsgZW51bWVyYWJsZTogdHJ1ZSwgdmFsdWU6IHZhbHVlIH0pO1xuIFx0XHRpZihtb2RlICYgMiAmJiB0eXBlb2YgdmFsdWUgIT0gJ3N0cmluZycpIGZvcih2YXIga2V5IGluIHZhbHVlKSBfX3dlYnBhY2tfcmVxdWlyZV9fLmQobnMsIGtleSwgZnVuY3Rpb24oa2V5KSB7IHJldHVybiB2YWx1ZVtrZXldOyB9LmJpbmQobnVsbCwga2V5KSk7XG4gXHRcdHJldHVybiBucztcbiBcdH07XG5cbiBcdC8vIGdldERlZmF1bHRFeHBvcnQgZnVuY3Rpb24gZm9yIGNvbXBhdGliaWxpdHkgd2l0aCBub24taGFybW9ueSBtb2R1bGVzXG4gXHRfX3dlYnBhY2tfcmVxdWlyZV9fLm4gPSBmdW5jdGlvbihtb2R1bGUpIHtcbiBcdFx0dmFyIGdldHRlciA9IG1vZHVsZSAmJiBtb2R1bGUuX19lc01vZHVsZSA/XG4gXHRcdFx0ZnVuY3Rpb24gZ2V0RGVmYXVsdCgpIHsgcmV0dXJuIG1vZHVsZVsnZGVmYXVsdCddOyB9IDpcbiBcdFx0XHRmdW5jdGlvbiBnZXRNb2R1bGVFeHBvcnRzKCkgeyByZXR1cm4gbW9kdWxlOyB9O1xuIFx0XHRfX3dlYnBhY2tfcmVxdWlyZV9fLmQoZ2V0dGVyLCAnYScsIGdldHRlcik7XG4gXHRcdHJldHVybiBnZXR0ZXI7XG4gXHR9O1xuXG4gXHQvLyBPYmplY3QucHJvdG90eXBlLmhhc093blByb3BlcnR5LmNhbGxcbiBcdF9fd2VicGFja19yZXF1aXJlX18ubyA9IGZ1bmN0aW9uKG9iamVjdCwgcHJvcGVydHkpIHsgcmV0dXJuIE9iamVjdC5wcm90b3R5cGUuaGFzT3duUHJvcGVydHkuY2FsbChvYmplY3QsIHByb3BlcnR5KTsgfTtcblxuIFx0Ly8gX193ZWJwYWNrX3B1YmxpY19wYXRoX19cbiBcdF9fd2VicGFja19yZXF1aXJlX18ucCA9IFwiL3B1YmxpYy9cIjtcblxuXG4gXHQvLyBMb2FkIGVudHJ5IG1vZHVsZSBhbmQgcmV0dXJuIGV4cG9ydHNcbiBcdHJldHVybiBfX3dlYnBhY2tfcmVxdWlyZV9fKF9fd2VicGFja19yZXF1aXJlX18ucyA9IFwiLi9hc3NldHMvanMvYXBwLmpzXCIpO1xuIiwiZ2xvYmFsLk1vbnNpZXVyQml6SW5zdGFudFNlYXJjaCA9IGNsYXNzIHtcbiAgICBjb25zdHJ1Y3RvcihcbiAgICAgICAgaW5zdGFudFVybCxcbiAgICAgICAgc2VhcmNoSW5wdXRTZWxlY3RvcixcbiAgICAgICAgcmVzdWx0Q2xvc2VzdFNlbGVjdG9yLFxuICAgICAgICByZXN1bHRGaW5kU2VsZWN0b3IsXG4gICAgICAgIGtleVVwVGltZU91dCxcbiAgICAgICAgbWluUXVlcnlMZW5ndGhcbiAgICApIHtcbiAgICAgICAgLy8gSW5pdCBhIHRpbWVvdXQgdmFyaWFibGUgdG8gYmUgdXNlZCBiZWxvd1xuICAgICAgICB2YXIgaW5zdGFudFNlYXJjaFRpbWVvdXQgPSBudWxsO1xuICAgICAgICBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKHNlYXJjaElucHV0U2VsZWN0b3IpLmFkZEV2ZW50TGlzdGVuZXIoJ2tleXVwJywgZnVuY3Rpb24gKGUpIHtcbiAgICAgICAgICAgIGNsZWFyVGltZW91dChpbnN0YW50U2VhcmNoVGltZW91dCk7XG4gICAgICAgICAgICB2YXIgcXVlcnkgPSBlLmN1cnJlbnRUYXJnZXQudmFsdWU7XG4gICAgICAgICAgICB2YXIgcmVzdWx0RWxlbWVudCA9IGUuY3VycmVudFRhcmdldC5jbG9zZXN0KHJlc3VsdENsb3Nlc3RTZWxlY3RvcikucXVlcnlTZWxlY3RvcihyZXN1bHRGaW5kU2VsZWN0b3IpO1xuICAgICAgICAgICAgaW5zdGFudFNlYXJjaFRpbWVvdXQgPSBzZXRUaW1lb3V0KGZ1bmN0aW9uICgpIHtcbiAgICAgICAgICAgICAgICBpZiAocXVlcnkubGVuZ3RoID49IG1pblF1ZXJ5TGVuZ3RoKSB7XG4gICAgICAgICAgICAgICAgICAgIHZhciBodHRwUmVxdWVzdCA9IG5ldyBYTUxIdHRwUmVxdWVzdCgpO1xuICAgICAgICAgICAgICAgICAgICBodHRwUmVxdWVzdC5vbmxvYWQgPSBmdW5jdGlvbigpIHtcbiAgICAgICAgICAgICAgICAgICAgICAgIGlmICh0aGlzLnN0YXR1cyA9PT0gMjAwKSB7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgcmVzdWx0RWxlbWVudC5pbm5lckhUTUwgPSB0aGlzLnJlc3BvbnNlVGV4dDtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICByZXN1bHRFbGVtZW50LnN0eWxlLmRpc3BsYXkgPSAnYmxvY2snO1xuICAgICAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgICAgICB9O1xuICAgICAgICAgICAgICAgICAgICBodHRwUmVxdWVzdC5vcGVuKFwiUE9TVFwiLCBpbnN0YW50VXJsKTtcbiAgICAgICAgICAgICAgICAgICAgaHR0cFJlcXVlc3Quc2V0UmVxdWVzdEhlYWRlcihcIlgtUmVxdWVzdGVkLVdpdGhcIiwgXCJYTUxIdHRwUmVxdWVzdFwiKTtcbiAgICAgICAgICAgICAgICAgICAgaHR0cFJlcXVlc3Quc2V0UmVxdWVzdEhlYWRlcihcIkNvbnRlbnQtVHlwZVwiLCBcImFwcGxpY2F0aW9uL3gtd3d3LWZvcm0tdXJsZW5jb2RlZFwiKTtcbiAgICAgICAgICAgICAgICAgICAgaHR0cFJlcXVlc3Quc2VuZChuZXcgVVJMU2VhcmNoUGFyYW1zKHtxdWVyeTogcXVlcnl9KS50b1N0cmluZygpKTtcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICB9LCBrZXlVcFRpbWVPdXQpO1xuICAgICAgICB9KTtcblxuICAgICAgICAvLyBIaWRlIHJlc3VsdHMgd2hlbiB1c2VyIGxlYXZlIHRoZSBzZWFyY2ggZmllbGRcbiAgICAgICAgZG9jdW1lbnQucXVlcnlTZWxlY3RvcihzZWFyY2hJbnB1dFNlbGVjdG9yKS5hZGRFdmVudExpc3RlbmVyKCdmb2N1c291dCcsIGZ1bmN0aW9uIChlKSB7XG4gICAgICAgICAgICB2YXIgcmVzdWx0RWxlbWVudCA9IGUuY3VycmVudFRhcmdldC5jbG9zZXN0KHJlc3VsdENsb3Nlc3RTZWxlY3RvcikucXVlcnlTZWxlY3RvcihyZXN1bHRGaW5kU2VsZWN0b3IpO1xuICAgICAgICAgICAgc2V0VGltZW91dChmdW5jdGlvbiAoKSB7XG4gICAgICAgICAgICAgICAgcmVzdWx0RWxlbWVudC5zdHlsZS5kaXNwbGF5ID0gJ25vbmUnO1xuICAgICAgICAgICAgfSwgMTAwKTsgLy8gQWRkIHRpbWVvdXQgdG8ga2VlcCB0aGUgY2xpY2sgb24gdGhlIHJlc3VsdFxuICAgICAgICB9KTtcbiAgICB9XG59XG5cbmRvY3VtZW50LmFkZEV2ZW50TGlzdGVuZXIoXCJET01Db250ZW50TG9hZGVkXCIsIGZ1bmN0aW9uKCkge1xuICAgIG5ldyBNb25zaWV1ckJpekluc3RhbnRTZWFyY2goXG4gICAgICAgIG1vbnNpZXVyYml6U2VhcmNoUGx1Z2luLmluc3RhbnRVcmwsXG4gICAgICAgIG1vbnNpZXVyYml6U2VhcmNoUGx1Z2luLnNlYXJjaElucHV0U2VsZWN0b3IsXG4gICAgICAgIG1vbnNpZXVyYml6U2VhcmNoUGx1Z2luLnJlc3VsdENsb3Nlc3RTZWxlY3RvcixcbiAgICAgICAgbW9uc2lldXJiaXpTZWFyY2hQbHVnaW4ucmVzdWx0RmluZFNlbGVjdG9yLFxuICAgICAgICBtb25zaWV1cmJpelNlYXJjaFBsdWdpbi5rZXlVcFRpbWVPdXQsXG4gICAgICAgIG1vbnNpZXVyYml6U2VhcmNoUGx1Z2luLm1pblF1ZXJ5TGVuZ3RoXG4gICAgKTtcbn0pO1xuIiwidmFyIGc7XG5cbi8vIFRoaXMgd29ya3MgaW4gbm9uLXN0cmljdCBtb2RlXG5nID0gKGZ1bmN0aW9uKCkge1xuXHRyZXR1cm4gdGhpcztcbn0pKCk7XG5cbnRyeSB7XG5cdC8vIFRoaXMgd29ya3MgaWYgZXZhbCBpcyBhbGxvd2VkIChzZWUgQ1NQKVxuXHRnID0gZyB8fCBuZXcgRnVuY3Rpb24oXCJyZXR1cm4gdGhpc1wiKSgpO1xufSBjYXRjaCAoZSkge1xuXHQvLyBUaGlzIHdvcmtzIGlmIHRoZSB3aW5kb3cgcmVmZXJlbmNlIGlzIGF2YWlsYWJsZVxuXHRpZiAodHlwZW9mIHdpbmRvdyA9PT0gXCJvYmplY3RcIikgZyA9IHdpbmRvdztcbn1cblxuLy8gZyBjYW4gc3RpbGwgYmUgdW5kZWZpbmVkLCBidXQgbm90aGluZyB0byBkbyBhYm91dCBpdC4uLlxuLy8gV2UgcmV0dXJuIHVuZGVmaW5lZCwgaW5zdGVhZCBvZiBub3RoaW5nIGhlcmUsIHNvIGl0J3Ncbi8vIGVhc2llciB0byBoYW5kbGUgdGhpcyBjYXNlLiBpZighZ2xvYmFsKSB7IC4uLn1cblxubW9kdWxlLmV4cG9ydHMgPSBnO1xuIl0sInNvdXJjZVJvb3QiOiIifQ==