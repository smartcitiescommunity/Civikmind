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
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 493);
/******/ })
/************************************************************************/
/******/ ({

/***/ 10:
/***/ (function(module, exports, __webpack_require__) {

try {
  var util = __webpack_require__(7);
  if (typeof util.inherits !== 'function') throw '';
  module.exports = util.inherits;
} catch (e) {
  module.exports = __webpack_require__(11);
}


/***/ }),

/***/ 11:
/***/ (function(module, exports) {

if (typeof Object.create === 'function') {
  // implementation from standard node.js 'util' module
  module.exports = function inherits(ctor, superCtor) {
    ctor.super_ = superCtor
    ctor.prototype = Object.create(superCtor.prototype, {
      constructor: {
        value: ctor,
        enumerable: false,
        writable: true,
        configurable: true
      }
    });
  };
} else {
  // old school shim for old browsers
  module.exports = function inherits(ctor, superCtor) {
    ctor.super_ = superCtor
    var TempCtor = function () {}
    TempCtor.prototype = superCtor.prototype
    ctor.prototype = new TempCtor()
    ctor.prototype.constructor = ctor
  }
}


/***/ }),

/***/ 12:
/***/ (function(module, exports, __webpack_require__) {

"use strict";
/* WEBPACK VAR INJECTION */(function(global) {

var objectAssign = __webpack_require__(13);

// compare and isBuffer taken from https://github.com/feross/buffer/blob/680e9e5e488f22aac27599a57dc844a6315928dd/index.js
// original notice:

/*!
 * The buffer module from node.js, for the browser.
 *
 * @author   Feross Aboukhadijeh <feross@feross.org> <http://feross.org>
 * @license  MIT
 */
function compare(a, b) {
  if (a === b) {
    return 0;
  }

  var x = a.length;
  var y = b.length;

  for (var i = 0, len = Math.min(x, y); i < len; ++i) {
    if (a[i] !== b[i]) {
      x = a[i];
      y = b[i];
      break;
    }
  }

  if (x < y) {
    return -1;
  }
  if (y < x) {
    return 1;
  }
  return 0;
}
function isBuffer(b) {
  if (global.Buffer && typeof global.Buffer.isBuffer === 'function') {
    return global.Buffer.isBuffer(b);
  }
  return !!(b != null && b._isBuffer);
}

// based on node assert, original notice:
// NB: The URL to the CommonJS spec is kept just for tradition.
//     node-assert has evolved a lot since then, both in API and behavior.

// http://wiki.commonjs.org/wiki/Unit_Testing/1.0
//
// THIS IS NOT TESTED NOR LIKELY TO WORK OUTSIDE V8!
//
// Originally from narwhal.js (http://narwhaljs.org)
// Copyright (c) 2009 Thomas Robinson <280north.com>
//
// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the 'Software'), to
// deal in the Software without restriction, including without limitation the
// rights to use, copy, modify, merge, publish, distribute, sublicense, and/or
// sell copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:
//
// The above copyright notice and this permission notice shall be included in
// all copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED 'AS IS', WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN
// ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
// WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

var util = __webpack_require__(7);
var hasOwn = Object.prototype.hasOwnProperty;
var pSlice = Array.prototype.slice;
var functionsHaveNames = (function () {
  return function foo() {}.name === 'foo';
}());
function pToString (obj) {
  return Object.prototype.toString.call(obj);
}
function isView(arrbuf) {
  if (isBuffer(arrbuf)) {
    return false;
  }
  if (typeof global.ArrayBuffer !== 'function') {
    return false;
  }
  if (typeof ArrayBuffer.isView === 'function') {
    return ArrayBuffer.isView(arrbuf);
  }
  if (!arrbuf) {
    return false;
  }
  if (arrbuf instanceof DataView) {
    return true;
  }
  if (arrbuf.buffer && arrbuf.buffer instanceof ArrayBuffer) {
    return true;
  }
  return false;
}
// 1. The assert module provides functions that throw
// AssertionError's when particular conditions are not met. The
// assert module must conform to the following interface.

var assert = module.exports = ok;

// 2. The AssertionError is defined in assert.
// new assert.AssertionError({ message: message,
//                             actual: actual,
//                             expected: expected })

var regex = /\s*function\s+([^\(\s]*)\s*/;
// based on https://github.com/ljharb/function.prototype.name/blob/adeeeec8bfcc6068b187d7d9fb3d5bb1d3a30899/implementation.js
function getName(func) {
  if (!util.isFunction(func)) {
    return;
  }
  if (functionsHaveNames) {
    return func.name;
  }
  var str = func.toString();
  var match = str.match(regex);
  return match && match[1];
}
assert.AssertionError = function AssertionError(options) {
  this.name = 'AssertionError';
  this.actual = options.actual;
  this.expected = options.expected;
  this.operator = options.operator;
  if (options.message) {
    this.message = options.message;
    this.generatedMessage = false;
  } else {
    this.message = getMessage(this);
    this.generatedMessage = true;
  }
  var stackStartFunction = options.stackStartFunction || fail;
  if (Error.captureStackTrace) {
    Error.captureStackTrace(this, stackStartFunction);
  } else {
    // non v8 browsers so we can have a stacktrace
    var err = new Error();
    if (err.stack) {
      var out = err.stack;

      // try to strip useless frames
      var fn_name = getName(stackStartFunction);
      var idx = out.indexOf('\n' + fn_name);
      if (idx >= 0) {
        // once we have located the function frame
        // we need to strip out everything before it (and its line)
        var next_line = out.indexOf('\n', idx + 1);
        out = out.substring(next_line + 1);
      }

      this.stack = out;
    }
  }
};

// assert.AssertionError instanceof Error
util.inherits(assert.AssertionError, Error);

function truncate(s, n) {
  if (typeof s === 'string') {
    return s.length < n ? s : s.slice(0, n);
  } else {
    return s;
  }
}
function inspect(something) {
  if (functionsHaveNames || !util.isFunction(something)) {
    return util.inspect(something);
  }
  var rawname = getName(something);
  var name = rawname ? ': ' + rawname : '';
  return '[Function' +  name + ']';
}
function getMessage(self) {
  return truncate(inspect(self.actual), 128) + ' ' +
         self.operator + ' ' +
         truncate(inspect(self.expected), 128);
}

// At present only the three keys mentioned above are used and
// understood by the spec. Implementations or sub modules can pass
// other keys to the AssertionError's constructor - they will be
// ignored.

// 3. All of the following functions must throw an AssertionError
// when a corresponding condition is not met, with a message that
// may be undefined if not provided.  All assertion methods provide
// both the actual and expected values to the assertion error for
// display purposes.

function fail(actual, expected, message, operator, stackStartFunction) {
  throw new assert.AssertionError({
    message: message,
    actual: actual,
    expected: expected,
    operator: operator,
    stackStartFunction: stackStartFunction
  });
}

// EXTENSION! allows for well behaved errors defined elsewhere.
assert.fail = fail;

// 4. Pure assertion tests whether a value is truthy, as determined
// by !!guard.
// assert.ok(guard, message_opt);
// This statement is equivalent to assert.equal(true, !!guard,
// message_opt);. To test strictly for the value true, use
// assert.strictEqual(true, guard, message_opt);.

function ok(value, message) {
  if (!value) fail(value, true, message, '==', assert.ok);
}
assert.ok = ok;

// 5. The equality assertion tests shallow, coercive equality with
// ==.
// assert.equal(actual, expected, message_opt);

assert.equal = function equal(actual, expected, message) {
  if (actual != expected) fail(actual, expected, message, '==', assert.equal);
};

// 6. The non-equality assertion tests for whether two objects are not equal
// with != assert.notEqual(actual, expected, message_opt);

assert.notEqual = function notEqual(actual, expected, message) {
  if (actual == expected) {
    fail(actual, expected, message, '!=', assert.notEqual);
  }
};

// 7. The equivalence assertion tests a deep equality relation.
// assert.deepEqual(actual, expected, message_opt);

assert.deepEqual = function deepEqual(actual, expected, message) {
  if (!_deepEqual(actual, expected, false)) {
    fail(actual, expected, message, 'deepEqual', assert.deepEqual);
  }
};

assert.deepStrictEqual = function deepStrictEqual(actual, expected, message) {
  if (!_deepEqual(actual, expected, true)) {
    fail(actual, expected, message, 'deepStrictEqual', assert.deepStrictEqual);
  }
};

function _deepEqual(actual, expected, strict, memos) {
  // 7.1. All identical values are equivalent, as determined by ===.
  if (actual === expected) {
    return true;
  } else if (isBuffer(actual) && isBuffer(expected)) {
    return compare(actual, expected) === 0;

  // 7.2. If the expected value is a Date object, the actual value is
  // equivalent if it is also a Date object that refers to the same time.
  } else if (util.isDate(actual) && util.isDate(expected)) {
    return actual.getTime() === expected.getTime();

  // 7.3 If the expected value is a RegExp object, the actual value is
  // equivalent if it is also a RegExp object with the same source and
  // properties (`global`, `multiline`, `lastIndex`, `ignoreCase`).
  } else if (util.isRegExp(actual) && util.isRegExp(expected)) {
    return actual.source === expected.source &&
           actual.global === expected.global &&
           actual.multiline === expected.multiline &&
           actual.lastIndex === expected.lastIndex &&
           actual.ignoreCase === expected.ignoreCase;

  // 7.4. Other pairs that do not both pass typeof value == 'object',
  // equivalence is determined by ==.
  } else if ((actual === null || typeof actual !== 'object') &&
             (expected === null || typeof expected !== 'object')) {
    return strict ? actual === expected : actual == expected;

  // If both values are instances of typed arrays, wrap their underlying
  // ArrayBuffers in a Buffer each to increase performance
  // This optimization requires the arrays to have the same type as checked by
  // Object.prototype.toString (aka pToString). Never perform binary
  // comparisons for Float*Arrays, though, since e.g. +0 === -0 but their
  // bit patterns are not identical.
  } else if (isView(actual) && isView(expected) &&
             pToString(actual) === pToString(expected) &&
             !(actual instanceof Float32Array ||
               actual instanceof Float64Array)) {
    return compare(new Uint8Array(actual.buffer),
                   new Uint8Array(expected.buffer)) === 0;

  // 7.5 For all other Object pairs, including Array objects, equivalence is
  // determined by having the same number of owned properties (as verified
  // with Object.prototype.hasOwnProperty.call), the same set of keys
  // (although not necessarily the same order), equivalent values for every
  // corresponding key, and an identical 'prototype' property. Note: this
  // accounts for both named and indexed properties on Arrays.
  } else if (isBuffer(actual) !== isBuffer(expected)) {
    return false;
  } else {
    memos = memos || {actual: [], expected: []};

    var actualIndex = memos.actual.indexOf(actual);
    if (actualIndex !== -1) {
      if (actualIndex === memos.expected.indexOf(expected)) {
        return true;
      }
    }

    memos.actual.push(actual);
    memos.expected.push(expected);

    return objEquiv(actual, expected, strict, memos);
  }
}

function isArguments(object) {
  return Object.prototype.toString.call(object) == '[object Arguments]';
}

function objEquiv(a, b, strict, actualVisitedObjects) {
  if (a === null || a === undefined || b === null || b === undefined)
    return false;
  // if one is a primitive, the other must be same
  if (util.isPrimitive(a) || util.isPrimitive(b))
    return a === b;
  if (strict && Object.getPrototypeOf(a) !== Object.getPrototypeOf(b))
    return false;
  var aIsArgs = isArguments(a);
  var bIsArgs = isArguments(b);
  if ((aIsArgs && !bIsArgs) || (!aIsArgs && bIsArgs))
    return false;
  if (aIsArgs) {
    a = pSlice.call(a);
    b = pSlice.call(b);
    return _deepEqual(a, b, strict);
  }
  var ka = objectKeys(a);
  var kb = objectKeys(b);
  var key, i;
  // having the same number of owned properties (keys incorporates
  // hasOwnProperty)
  if (ka.length !== kb.length)
    return false;
  //the same set of keys (although not necessarily the same order),
  ka.sort();
  kb.sort();
  //~~~cheap key test
  for (i = ka.length - 1; i >= 0; i--) {
    if (ka[i] !== kb[i])
      return false;
  }
  //equivalent values for every corresponding key, and
  //~~~possibly expensive deep test
  for (i = ka.length - 1; i >= 0; i--) {
    key = ka[i];
    if (!_deepEqual(a[key], b[key], strict, actualVisitedObjects))
      return false;
  }
  return true;
}

// 8. The non-equivalence assertion tests for any deep inequality.
// assert.notDeepEqual(actual, expected, message_opt);

assert.notDeepEqual = function notDeepEqual(actual, expected, message) {
  if (_deepEqual(actual, expected, false)) {
    fail(actual, expected, message, 'notDeepEqual', assert.notDeepEqual);
  }
};

assert.notDeepStrictEqual = notDeepStrictEqual;
function notDeepStrictEqual(actual, expected, message) {
  if (_deepEqual(actual, expected, true)) {
    fail(actual, expected, message, 'notDeepStrictEqual', notDeepStrictEqual);
  }
}


// 9. The strict equality assertion tests strict equality, as determined by ===.
// assert.strictEqual(actual, expected, message_opt);

assert.strictEqual = function strictEqual(actual, expected, message) {
  if (actual !== expected) {
    fail(actual, expected, message, '===', assert.strictEqual);
  }
};

// 10. The strict non-equality assertion tests for strict inequality, as
// determined by !==.  assert.notStrictEqual(actual, expected, message_opt);

assert.notStrictEqual = function notStrictEqual(actual, expected, message) {
  if (actual === expected) {
    fail(actual, expected, message, '!==', assert.notStrictEqual);
  }
};

function expectedException(actual, expected) {
  if (!actual || !expected) {
    return false;
  }

  if (Object.prototype.toString.call(expected) == '[object RegExp]') {
    return expected.test(actual);
  }

  try {
    if (actual instanceof expected) {
      return true;
    }
  } catch (e) {
    // Ignore.  The instanceof check doesn't work for arrow functions.
  }

  if (Error.isPrototypeOf(expected)) {
    return false;
  }

  return expected.call({}, actual) === true;
}

function _tryBlock(block) {
  var error;
  try {
    block();
  } catch (e) {
    error = e;
  }
  return error;
}

function _throws(shouldThrow, block, expected, message) {
  var actual;

  if (typeof block !== 'function') {
    throw new TypeError('"block" argument must be a function');
  }

  if (typeof expected === 'string') {
    message = expected;
    expected = null;
  }

  actual = _tryBlock(block);

  message = (expected && expected.name ? ' (' + expected.name + ').' : '.') +
            (message ? ' ' + message : '.');

  if (shouldThrow && !actual) {
    fail(actual, expected, 'Missing expected exception' + message);
  }

  var userProvidedMessage = typeof message === 'string';
  var isUnwantedException = !shouldThrow && util.isError(actual);
  var isUnexpectedException = !shouldThrow && actual && !expected;

  if ((isUnwantedException &&
      userProvidedMessage &&
      expectedException(actual, expected)) ||
      isUnexpectedException) {
    fail(actual, expected, 'Got unwanted exception' + message);
  }

  if ((shouldThrow && actual && expected &&
      !expectedException(actual, expected)) || (!shouldThrow && actual)) {
    throw actual;
  }
}

// 11. Expected to throw an error:
// assert.throws(block, Error_opt, message_opt);

assert.throws = function(block, /*optional*/error, /*optional*/message) {
  _throws(true, block, error, message);
};

// EXTENSION! This is annoying to write outside this module.
assert.doesNotThrow = function(block, /*optional*/error, /*optional*/message) {
  _throws(false, block, error, message);
};

assert.ifError = function(err) { if (err) throw err; };

// Expose a strict only variant of assert
function strict(value, message) {
  if (!value) fail(value, true, message, '==', strict);
}
assert.strict = objectAssign(strict, assert, {
  equal: assert.strictEqual,
  deepEqual: assert.deepStrictEqual,
  notEqual: assert.notStrictEqual,
  notDeepEqual: assert.notDeepStrictEqual
});
assert.strict.strict = assert.strict;

var objectKeys = Object.keys || function (obj) {
  var keys = [];
  for (var key in obj) {
    if (hasOwn.call(obj, key)) keys.push(key);
  }
  return keys;
};

/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(6)))

/***/ }),

/***/ 13:
/***/ (function(module, exports, __webpack_require__) {

"use strict";
/*
object-assign
(c) Sindre Sorhus
@license MIT
*/


/* eslint-disable no-unused-vars */
var getOwnPropertySymbols = Object.getOwnPropertySymbols;
var hasOwnProperty = Object.prototype.hasOwnProperty;
var propIsEnumerable = Object.prototype.propertyIsEnumerable;

function toObject(val) {
	if (val === null || val === undefined) {
		throw new TypeError('Object.assign cannot be called with null or undefined');
	}

	return Object(val);
}

function shouldUseNative() {
	try {
		if (!Object.assign) {
			return false;
		}

		// Detect buggy property enumeration order in older V8 versions.

		// https://bugs.chromium.org/p/v8/issues/detail?id=4118
		var test1 = new String('abc');  // eslint-disable-line no-new-wrappers
		test1[5] = 'de';
		if (Object.getOwnPropertyNames(test1)[0] === '5') {
			return false;
		}

		// https://bugs.chromium.org/p/v8/issues/detail?id=3056
		var test2 = {};
		for (var i = 0; i < 10; i++) {
			test2['_' + String.fromCharCode(i)] = i;
		}
		var order2 = Object.getOwnPropertyNames(test2).map(function (n) {
			return test2[n];
		});
		if (order2.join('') !== '0123456789') {
			return false;
		}

		// https://bugs.chromium.org/p/v8/issues/detail?id=3056
		var test3 = {};
		'abcdefghijklmnopqrst'.split('').forEach(function (letter) {
			test3[letter] = letter;
		});
		if (Object.keys(Object.assign({}, test3)).join('') !==
				'abcdefghijklmnopqrst') {
			return false;
		}

		return true;
	} catch (err) {
		// We don't expect any of the above to throw, but better to be safe.
		return false;
	}
}

module.exports = shouldUseNative() ? Object.assign : function (target, source) {
	var from;
	var to = toObject(target);
	var symbols;

	for (var s = 1; s < arguments.length; s++) {
		from = Object(arguments[s]);

		for (var key in from) {
			if (hasOwnProperty.call(from, key)) {
				to[key] = from[key];
			}
		}

		if (getOwnPropertySymbols) {
			symbols = getOwnPropertySymbols(from);
			for (var i = 0; i < symbols.length; i++) {
				if (propIsEnumerable.call(from, symbols[i])) {
					to[symbols[i]] = from[symbols[i]];
				}
			}
		}
	}

	return to;
};


/***/ }),

/***/ 4:
/***/ (function(module, exports, __webpack_require__) {

/* WEBPACK VAR INJECTION */(function(console) {/*
	MIT License http://www.opensource.org/licenses/mit-license.php
	Author Tobias Koppers @sokra
*/
module.exports = function(src) {
	function log(error) {
		(typeof console !== "undefined")
		&& (console.error || console.log)("[Script Loader]", error);
	}

	// Check for IE =< 8
	function isIE() {
		return typeof attachEvent !== "undefined" && typeof addEventListener === "undefined";
	}

	try {
		if (typeof execScript !== "undefined" && isIE()) {
			execScript(src);
		} else if (typeof eval !== "undefined") {
			eval.call(null, src);
		} else {
			log("EvalError: No eval function available");
		}
	} catch (error) {
		log(error);
	}
}

/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(5)))

/***/ }),

/***/ 493:
/***/ (function(module, exports, __webpack_require__) {

/**
 * ---------------------------------------------------------------------
 * GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2015-2021 Teclib' and contributors.
 *
 * http://glpi-project.org
 *
 * based on GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2003-2014 by the INDEPNET Development Team.
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GLPI.
 *
 * GLPI is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GLPI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 */
// Gridstack jQuery plugin
__webpack_require__(494);
__webpack_require__(496);
__webpack_require__(498);
__webpack_require__(499);


/***/ }),

/***/ 494:
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(4)(__webpack_require__(495))

/***/ }),

/***/ 495:
/***/ (function(module, exports) {

module.exports = "/**\n * gridstack.js 0.6.3\n * https://gridstackjs.com/\n * (c) 2014-2020 Alain Dumesny, Dylan Weiss, Pavel Reznikov\n * gridstack.js may be freely distributed under the MIT license.\n * @preserve\n*/\n(function(factory) {\n  if (typeof define === 'function' && define.amd) {\n    define(['jquery', 'exports'], factory);\n  } else if (typeof exports !== 'undefined') {\n    var jQueryModule;\n\n    try { jQueryModule = require('jquery'); } catch (e) {}\n\n    factory(jQueryModule || window.jQuery, exports);\n  } else {\n    factory(window.jQuery, window);\n  }\n})(function($, scope) {\n\n  // checks for obsolete method names\n  var obsolete = function(f, oldName, newName, rev) {\n    var wrapper = function() {\n      console.warn('gridstack.js: Function `' + oldName + '` is deprecated in ' + rev + ' and has been replaced ' +\n      'with `' + newName + '`. It will be **completely** removed in v1.0');\n      return f.apply(this, arguments);\n    };\n    wrapper.prototype = f.prototype;\n\n    return wrapper;\n  };\n\n  // checks for obsolete grid options (can be used for any fields, but msg is about options)\n  var obsoleteOpts = function(opts, oldName, newName, rev) {\n    if (opts[oldName] !== undefined) {\n      opts[newName] = opts[oldName];\n      console.warn('gridstack.js: Option `' + oldName + '` is deprecated in ' + rev + ' and has been replaced with `' +\n        newName + '`. It will be **completely** removed in v1.0');\n    }\n  };\n\n  // checks for obsolete grid options which are gone\n  var obsoleteOptsDel = function(opts, oldName, rev, info) {\n    if (opts[oldName] !== undefined) {\n      console.warn('gridstack.js: Option `' + oldName + '` is deprecated in ' + rev + info);\n    }\n  };\n\n  // checks for obsolete Jquery element attributes\n  var obsoleteAttr = function(el, oldName, newName, rev) {\n    var oldAttr = el.attr(oldName);\n    if (oldAttr !== undefined) {\n      el.attr(newName, oldAttr);\n      console.warn('gridstack.js: attribute `' + oldName + '`=' + oldAttr + ' is deprecated on this object in ' + rev + ' and has been replaced with `' +\n        newName + '`. It will be **completely** removed in v1.0');\n    }\n  };\n\n  var Utils = {\n\n    isIntercepted: function(a, b) {\n      return !(a.x + a.width <= b.x || b.x + b.width <= a.x || a.y + a.height <= b.y || b.y + b.height <= a.y);\n    },\n\n    sort: function(nodes, dir, column) {\n      if (!column) {\n        var widths = nodes.map(function(node) { return node.x + node.width; });\n        column = Math.max.apply(Math, widths);\n      }\n\n      if (dir === -1)\n        return Utils.sortBy(nodes, function(n) { return -(n.x + n.y * column); });\n      else\n        return Utils.sortBy(nodes, function(n) { return (n.x + n.y * column); });\n    },\n\n    createStylesheet: function(id) {\n      var style = document.createElement('style');\n      style.setAttribute('type', 'text/css');\n      style.setAttribute('data-gs-style-id', id);\n      if (style.styleSheet) {\n        style.styleSheet.cssText = '';\n      } else {\n        style.appendChild(document.createTextNode(''));\n      }\n      document.getElementsByTagName('head')[0].appendChild(style);\n      return style.sheet;\n    },\n\n    removeStylesheet: function(id) {\n      $('STYLE[data-gs-style-id=' + id + ']').remove();\n    },\n\n    insertCSSRule: function(sheet, selector, rules, index) {\n      if (typeof sheet.insertRule === 'function') {\n        sheet.insertRule(selector + '{' + rules + '}', index);\n      } else if (typeof sheet.addRule === 'function') {\n        sheet.addRule(selector, rules, index);\n      }\n    },\n\n    toBool: function(v) {\n      if (typeof v === 'boolean') {\n        return v;\n      }\n      if (typeof v === 'string') {\n        v = v.toLowerCase();\n        return !(v === '' || v === 'no' || v === 'false' || v === '0');\n      }\n      return Boolean(v);\n    },\n\n    _collisionNodeCheck: function(n) {\n      return n !== this.node && Utils.isIntercepted(n, this.nn);\n    },\n\n    _didCollide: function(bn) {\n      return Utils.isIntercepted({x: this.n.x, y: this.newY, width: this.n.width, height: this.n.height}, bn);\n    },\n\n    _isAddNodeIntercepted: function(n) {\n      return Utils.isIntercepted({x: this.x, y: this.y, width: this.node.width, height: this.node.height}, n);\n    },\n\n    parseHeight: function(val) {\n      var height = val;\n      var heightUnit = 'px';\n      if (height && typeof height === 'string') {\n        var match = height.match(/^(-[0-9]+\\.[0-9]+|[0-9]*\\.[0-9]+|-[0-9]+|[0-9]+)(px|em|rem|vh|vw|%)?$/);\n        if (!match) {\n          throw new Error('Invalid height');\n        }\n        heightUnit = match[2] || 'px';\n        height = parseFloat(match[1]);\n      }\n      return {height: height, unit: heightUnit};\n    },\n\n    without:  function(array, item) {\n      var index = array.indexOf(item);\n\n      if (index !== -1) {\n        array = array.slice(0);\n        array.splice(index, 1);\n      }\n\n      return array;\n    },\n\n    sortBy: function(array, getter) {\n      return array.slice(0).sort(function(left, right) {\n        var valueLeft = getter(left);\n        var valueRight = getter(right);\n\n        if (valueRight === valueLeft) {\n          return 0;\n        }\n\n        return valueLeft > valueRight ? 1 : -1;\n      });\n    },\n\n    defaults: function(target) {\n      var sources = Array.prototype.slice.call(arguments, 1);\n\n      sources.forEach(function(source) {\n        for (var prop in source) {\n          if (source.hasOwnProperty(prop) && (!target.hasOwnProperty(prop) || target[prop] === undefined)) {\n            target[prop] = source[prop];\n          }\n        }\n      });\n\n      return target;\n    },\n\n    clone: function(target) {\n      return $.extend({}, target);\n    },\n\n    throttle: function(callback, delay) {\n      var isWaiting = false;\n\n      return function() {\n        if (!isWaiting) {\n          callback.apply(this, arguments);\n          isWaiting = true;\n          setTimeout(function() { isWaiting = false; }, delay);\n        }\n      };\n    },\n\n    removePositioningStyles: function(el) {\n      var style = el[0].style;\n      if (style.position) {\n        style.removeProperty('position');\n      }\n      if (style.left) {\n        style.removeProperty('left');\n      }\n      if (style.top) {\n        style.removeProperty('top');\n      }\n      if (style.width) {\n        style.removeProperty('width');\n      }\n      if (style.height) {\n        style.removeProperty('height');\n      }\n    },\n    getScrollParent: function(el) {\n      var returnEl;\n      if (el === null) {\n        returnEl = null;\n      } else if (el.scrollHeight > el.clientHeight) {\n        returnEl = el;\n      } else {\n        returnEl = Utils.getScrollParent(el.parentNode);\n      }\n      return returnEl;\n    },\n    updateScrollPosition: function(el, ui, distance) {\n      // is widget in view?\n      var rect = el.getBoundingClientRect();\n      var innerHeightOrClientHeight = (window.innerHeight || document.documentElement.clientHeight);\n      if (rect.top < 0 ||\n        rect.bottom > innerHeightOrClientHeight\n      ) {\n        // set scrollTop of first parent that scrolls\n        // if parent is larger than el, set as low as possible\n        // to get entire widget on screen\n        var offsetDiffDown = rect.bottom - innerHeightOrClientHeight;\n        var offsetDiffUp = rect.top;\n        var scrollEl = Utils.getScrollParent(el);\n        if (scrollEl !== null) {\n          var prevScroll = scrollEl.scrollTop;\n          if (rect.top < 0 && distance < 0) {\n            // moving up\n            if (el.offsetHeight > innerHeightOrClientHeight) {\n              scrollEl.scrollTop += distance;\n            } else {\n              scrollEl.scrollTop += Math.abs(offsetDiffUp) > Math.abs(distance) ? distance : offsetDiffUp;\n            }\n          } else if (distance > 0) {\n            // moving down\n            if (el.offsetHeight > innerHeightOrClientHeight) {\n              scrollEl.scrollTop += distance;\n            } else {\n              scrollEl.scrollTop += offsetDiffDown > distance ? distance : offsetDiffDown;\n            }\n          }\n          // move widget y by amount scrolled\n          ui.position.top += scrollEl.scrollTop - prevScroll;\n        }\n      }\n    }\n  };\n\n  /**\n  * @class GridStackDragDropPlugin\n  * Base class for drag'n'drop plugin.\n  */\n  function GridStackDragDropPlugin(grid) {\n    this.grid = grid;\n  }\n\n  GridStackDragDropPlugin.registeredPlugins = [];\n\n  GridStackDragDropPlugin.registerPlugin = function(pluginClass) {\n    GridStackDragDropPlugin.registeredPlugins.push(pluginClass);\n  };\n\n  GridStackDragDropPlugin.prototype.resizable = function(el, opts) {\n    return this;\n  };\n\n  GridStackDragDropPlugin.prototype.draggable = function(el, opts) {\n    return this;\n  };\n\n  GridStackDragDropPlugin.prototype.droppable = function(el, opts) {\n    return this;\n  };\n\n  GridStackDragDropPlugin.prototype.isDroppable = function(el) {\n    return false;\n  };\n\n  GridStackDragDropPlugin.prototype.on = function(el, eventName, callback) {\n    return this;\n  };\n\n\n  var idSeq = 0;\n\n  var GridStackEngine = function(column, onchange, float, maxRow, items) {\n    this.column = column || 12;\n    this.float = float || false;\n    this.maxRow = maxRow || 0;\n\n    this.nodes = items || [];\n    this.onchange = onchange || function() {};\n\n    this._addedNodes = [];\n    this._removedNodes = [];\n    this._batchMode = false;\n  };\n\n  GridStackEngine.prototype.batchUpdate = function() {\n    if (this._batchMode) return;\n    this._batchMode = true;\n    this._prevFloat = this.float;\n    this.float = true; // let things go anywhere for now... commit() will restore and possibly reposition\n  };\n\n  GridStackEngine.prototype.commit = function() {\n    if (!this._batchMode) return;\n    this._batchMode = false;\n    this.float = this._prevFloat;\n    delete this._prevFloat;\n    this._packNodes();\n    this._notify();\n  };\n\n  // For Meteor support: https://github.com/gridstack/gridstack.js/pull/272\n  GridStackEngine.prototype.getNodeDataByDOMEl = function(el) {\n    return this.nodes.find(function(n) { return el.get(0) === n.el.get(0); });\n  };\n\n  GridStackEngine.prototype._fixCollisions = function(node) {\n    var self = this;\n    this._sortNodes(-1);\n\n    var nn = node;\n    var hasLocked = Boolean(this.nodes.find(function(n) { return n.locked; }));\n    if (!this.float && !hasLocked) {\n      nn = {x: 0, y: node.y, width: this.column, height: node.height};\n    }\n    while (true) {\n      var collisionNode = this.nodes.find(Utils._collisionNodeCheck, {node: node, nn: nn});\n      if (!collisionNode) { return; }\n      this.moveNode(collisionNode, collisionNode.x, node.y + node.height,\n        collisionNode.width, collisionNode.height, true);\n    }\n  };\n\n  GridStackEngine.prototype.isAreaEmpty = function(x, y, width, height) {\n    var nn = {x: x || 0, y: y || 0, width: width || 1, height: height || 1};\n    var collisionNode = this.nodes.find(function(n) {\n      return Utils.isIntercepted(n, nn);\n    });\n    return !collisionNode;\n  };\n\n  GridStackEngine.prototype._sortNodes = function(dir) {\n    this.nodes = Utils.sort(this.nodes, dir, this.column);\n  };\n\n  GridStackEngine.prototype._packNodes = function() {\n    this._sortNodes();\n\n    if (this.float) {\n      this.nodes.forEach(function(n, i) {\n        if (n._updating || n._packY === undefined || n.y === n._packY) {\n          return;\n        }\n\n        var newY = n.y;\n        while (newY >= n._packY) {\n          var collisionNode = this.nodes\n            .slice(0, i)\n            .find(Utils._didCollide, {n: n, newY: newY});\n\n          if (!collisionNode) {\n            n._dirty = true;\n            n.y = newY;\n          }\n          --newY;\n        }\n      }, this);\n    } else {\n      this.nodes.forEach(function(n, i) {\n        if (n.locked) { return; }\n        while (n.y > 0) {\n          var newY = n.y - 1;\n          var canBeMoved = i === 0;\n\n          if (i > 0) {\n            var collisionNode = this.nodes\n              .slice(0, i)\n              .find(Utils._didCollide, {n: n, newY: newY});\n            canBeMoved = collisionNode === undefined;\n          }\n\n          if (!canBeMoved) { break; }\n          // Note: must be dirty (from last position) for GridStack::OnChange CB to update positions\n          // and move items back. The user 'change' CB should detect changes from the original\n          // starting position instead.\n          n._dirty = (n.y !== newY);\n          n.y = newY;\n        }\n      }, this);\n    }\n  };\n\n  GridStackEngine.prototype._prepareNode = function(node, resizing) {\n    node = node || {};\n    // if we're missing position, have the grid position us automatically (before we set them to 0,0)\n    if (node.x === undefined || node.y === undefined || node.x === null || node.y === null) {\n      node.autoPosition = true;\n    }\n\n    // assign defaults for missing required fields\n    var defaults = {width: 1, height: 1, x: 0, y: 0};\n    node = Utils.defaults(node, defaults);\n\n    // convert any strings over\n    node.x = parseInt(node.x);\n    node.y = parseInt(node.y);\n    node.width = parseInt(node.width);\n    node.height = parseInt(node.height);\n    node.autoPosition = node.autoPosition || false;\n    node.noResize = node.noResize || false;\n    node.noMove = node.noMove || false;\n\n    // check for NaN (in case messed up strings were passed. can't do parseInt() || defaults.x above as 0 is valid #)\n    if (Number.isNaN(node.x))      { node.x = defaults.x; node.autoPosition = true; }\n    if (Number.isNaN(node.y))      { node.y = defaults.y; node.autoPosition = true; }\n    if (Number.isNaN(node.width))  { node.width = defaults.width; }\n    if (Number.isNaN(node.height)) { node.height = defaults.height; }\n\n    if (node.width > this.column) {\n      node.width = this.column;\n    } else if (node.width < 1) {\n      node.width = 1;\n    }\n\n    if (node.height < 1) {\n      node.height = 1;\n    }\n\n    if (node.x < 0) {\n      node.x = 0;\n    }\n\n    if (node.x + node.width > this.column) {\n      if (resizing) {\n        node.width = this.column - node.x;\n      } else {\n        node.x = this.column - node.width;\n      }\n    }\n\n    if (node.y < 0) {\n      node.y = 0;\n    }\n\n    return node;\n  };\n\n  GridStackEngine.prototype._notify = function() {\n    if (this._batchMode) { return; }\n    var args = Array.prototype.slice.call(arguments, 0);\n    args[0] = (args[0] === undefined ? [] : (Array.isArray(args[0]) ? args[0] : [args[0]]) );\n    args[1] = (args[1] === undefined ? true : args[1]);\n    var dirtyNodes = args[0].concat(this.getDirtyNodes());\n    this.onchange(dirtyNodes, args[1]);\n  };\n\n  GridStackEngine.prototype.cleanNodes = function() {\n    if (this._batchMode) { return; }\n    this.nodes.forEach(function(n) { delete n._dirty; });\n  };\n\n  GridStackEngine.prototype.getDirtyNodes = function() {\n    return this.nodes.filter(function(n) { return n._dirty; });\n  };\n\n  GridStackEngine.prototype.addNode = function(node, triggerAddEvent) {\n    var prev = {x: node.x, y: node.y, width: node.width, height: node.height};\n\n    node = this._prepareNode(node);\n\n    if (node.maxWidth !== undefined) { node.width = Math.min(node.width, node.maxWidth); }\n    if (node.maxHeight !== undefined) { node.height = Math.min(node.height, node.maxHeight); }\n    if (node.minWidth !== undefined) { node.width = Math.max(node.width, node.minWidth); }\n    if (node.minHeight !== undefined) { node.height = Math.max(node.height, node.minHeight); }\n\n    node._id = node._id || ++idSeq;\n\n    if (node.autoPosition) {\n      this._sortNodes();\n\n      for (var i = 0;; ++i) {\n        var x = i % this.column;\n        var y = Math.floor(i / this.column);\n        if (x + node.width > this.column) {\n          continue;\n        }\n        if (!this.nodes.find(Utils._isAddNodeIntercepted, {x: x, y: y, node: node})) {\n          node.x = x;\n          node.y = y;\n          delete node.autoPosition; // found our slot\n          break;\n        }\n      }\n    }\n\n    this.nodes.push(node);\n    if (triggerAddEvent) {\n      this._addedNodes.push(node);\n    }\n    // use single equal as they come as string/undefined but end as number....\n    if (!node._dirty && (prev.x != node.x || prev.y != node.y || prev.width != node.width || prev.height != node.height)) {\n      node._dirty = true;\n    }\n\n    this._fixCollisions(node);\n    this._packNodes();\n    this._notify();\n    return node;\n  };\n\n  GridStackEngine.prototype.removeNode = function(node, detachNode) {\n    detachNode = (detachNode === undefined ? true : detachNode);\n    this._removedNodes.push(node);\n    node._id = null; // hint that node is being removed\n    this.nodes = Utils.without(this.nodes, node);\n    this._packNodes();\n    this._notify(node, detachNode);\n  };\n\n  GridStackEngine.prototype.removeAll = function(detachNode) {\n    delete this._layouts;\n    if (this.nodes.length === 0) { return; }\n    detachNode = (detachNode === undefined ? true : detachNode);\n    this.nodes.forEach(function(n) { n._id = null; }); // hint that node is being removed\n    this._removedNodes = this.nodes;\n    this.nodes = [];\n    this._notify(this._removedNodes, detachNode);\n  };\n\n  GridStackEngine.prototype.canMoveNode = function(node, x, y, width, height) {\n    if (!this.isNodeChangedPosition(node, x, y, width, height)) {\n      return false;\n    }\n    var hasLocked = Boolean(this.nodes.find(function(n) { return n.locked; }));\n\n    if (!this.maxRow && !hasLocked) {\n      return true;\n    }\n\n    var clonedNode;\n    var clone = new GridStackEngine(\n      this.column,\n      null,\n      this.float,\n      0,\n      this.nodes.map(function(n) {\n        if (n === node) {\n          clonedNode = $.extend({}, n);\n          return clonedNode;\n        }\n        return $.extend({}, n);\n      }));\n\n    if (!clonedNode) {  return true;}\n\n    clone.moveNode(clonedNode, x, y, width, height);\n\n    var res = true;\n\n    if (hasLocked) {\n      res &= !Boolean(clone.nodes.find(function(n) {\n        return n !== clonedNode && Boolean(n.locked) && Boolean(n._dirty);\n      }));\n    }\n    if (this.maxRow) {\n      res &= clone.getGridHeight() <= this.maxRow;\n    }\n\n    return res;\n  };\n\n  GridStackEngine.prototype.canBePlacedWithRespectToHeight = function(node) {\n    if (!this.maxRow) {\n      return true;\n    }\n\n    var clone = new GridStackEngine(\n      this.column,\n      null,\n      this.float,\n      0,\n      this.nodes.map(function(n) { return $.extend({}, n); }));\n    clone.addNode(node);\n    return clone.getGridHeight() <= this.maxRow;\n  };\n\n  GridStackEngine.prototype.isNodeChangedPosition = function(node, x, y, width, height) {\n    if (typeof x !== 'number') { x = node.x; }\n    if (typeof y !== 'number') { y = node.y; }\n    if (typeof width !== 'number') { width = node.width; }\n    if (typeof height !== 'number') { height = node.height; }\n\n    if (node.maxWidth !== undefined) { width = Math.min(width, node.maxWidth); }\n    if (node.maxHeight !== undefined) { height = Math.min(height, node.maxHeight); }\n    if (node.minWidth !== undefined) { width = Math.max(width, node.minWidth); }\n    if (node.minHeight !== undefined) { height = Math.max(height, node.minHeight); }\n\n    if (node.x === x && node.y === y && node.width === width && node.height === height) {\n      return false;\n    }\n    return true;\n  };\n\n  GridStackEngine.prototype.moveNode = function(node, x, y, width, height, noPack) {\n    if (typeof x !== 'number') { x = node.x; }\n    if (typeof y !== 'number') { y = node.y; }\n    if (typeof width !== 'number') { width = node.width; }\n    if (typeof height !== 'number') { height = node.height; }\n\n    if (node.maxWidth !== undefined) { width = Math.min(width, node.maxWidth); }\n    if (node.maxHeight !== undefined) { height = Math.min(height, node.maxHeight); }\n    if (node.minWidth !== undefined) { width = Math.max(width, node.minWidth); }\n    if (node.minHeight !== undefined) { height = Math.max(height, node.minHeight); }\n\n    if (node.x === x && node.y === y && node.width === width && node.height === height) {\n      return node;\n    }\n\n    var resizing = node.width !== width;\n    node._dirty = true;\n\n    node.x = x;\n    node.y = y;\n    node.width = width;\n    node.height = height;\n\n    node.lastTriedX = x;\n    node.lastTriedY = y;\n    node.lastTriedWidth = width;\n    node.lastTriedHeight = height;\n\n    node = this._prepareNode(node, resizing);\n\n    this._fixCollisions(node);\n    if (!noPack) {\n      this._packNodes();\n      this._notify();\n    }\n    return node;\n  };\n\n  GridStackEngine.prototype.getGridHeight = function() {\n    return this.nodes.reduce(function(memo, n) { return Math.max(memo, n.y + n.height); }, 0);\n  };\n\n  GridStackEngine.prototype.beginUpdate = function(node) {\n    if (node._updating) return;\n    node._updating = true;\n    this.nodes.forEach(function(n) { n._packY = n.y; });\n  };\n\n  GridStackEngine.prototype.endUpdate = function() {\n    var n = this.nodes.find(function(n) { return n._updating; });\n    if (n) {\n      n._updating = false;\n      this.nodes.forEach(function(n) { delete n._packY; });\n    }\n  };\n\n  var GridStack = function(el, opts) {\n    var self = this;\n    var oneColumnMode, _prevColumn, isAutoCellHeight;\n\n    opts = opts || {};\n\n    this.container = $(el);\n\n    obsoleteOpts(opts, 'width', 'column', 'v0.5.3');\n    obsoleteOpts(opts, 'height', 'maxRow', 'v0.5.3');\n    obsoleteOptsDel(opts, 'oneColumnModeClass', 'v0.6.3', '. Use class `.grid-stack-1` instead');\n\n    // container attributes\n    obsoleteAttr(this.container, 'data-gs-width', 'data-gs-column', 'v0.5.3');\n    obsoleteAttr(this.container, 'data-gs-height', 'data-gs-max-row', 'v0.5.3');\n\n    opts.itemClass = opts.itemClass || 'grid-stack-item';\n    var isNested = this.container.closest('.' + opts.itemClass).length > 0;\n\n    this.opts = Utils.defaults(opts, {\n      column: parseInt(this.container.attr('data-gs-column')) || 12,\n      maxRow: parseInt(this.container.attr('data-gs-max-row')) || 0,\n      itemClass: 'grid-stack-item',\n      placeholderClass: 'grid-stack-placeholder',\n      placeholderText: '',\n      handle: '.grid-stack-item-content',\n      handleClass: null,\n      cellHeight: 60,\n      verticalMargin: 20,\n      auto: true,\n      minWidth: 768,\n      float: false,\n      staticGrid: false,\n      _class: 'grid-stack-instance-' + (Math.random() * 10000).toFixed(0),\n      animate: Boolean(this.container.attr('data-gs-animate')) || false,\n      alwaysShowResizeHandle: opts.alwaysShowResizeHandle || false,\n      resizable: Utils.defaults(opts.resizable || {}, {\n        autoHide: !(opts.alwaysShowResizeHandle || false),\n        handles: 'se'\n      }),\n      draggable: Utils.defaults(opts.draggable || {}, {\n        handle: (opts.handleClass ? '.' + opts.handleClass : (opts.handle ? opts.handle : '')) ||\n          '.grid-stack-item-content',\n        scroll: false,\n        appendTo: 'body'\n      }),\n      disableDrag: opts.disableDrag || false,\n      disableResize: opts.disableResize || false,\n      rtl: 'auto',\n      removable: false,\n      removableOptions: Utils.defaults(opts.removableOptions || {}, {\n        accept: '.' + opts.itemClass\n      }),\n      removeTimeout: 2000,\n      verticalMarginUnit: 'px',\n      cellHeightUnit: 'px',\n      disableOneColumnMode: opts.disableOneColumnMode || false,\n      oneColumnModeDomSort: opts.oneColumnModeDomSort,\n      ddPlugin: null\n    });\n\n    if (this.opts.ddPlugin === false) {\n      this.opts.ddPlugin = GridStackDragDropPlugin;\n    } else if (this.opts.ddPlugin === null) {\n      this.opts.ddPlugin = GridStackDragDropPlugin.registeredPlugins[0] || GridStackDragDropPlugin;\n    }\n\n    this.dd = new this.opts.ddPlugin(this);\n\n    if (this.opts.rtl === 'auto') {\n      this.opts.rtl = this.container.css('direction') === 'rtl';\n    }\n\n    if (this.opts.rtl) {\n      this.container.addClass('grid-stack-rtl');\n    }\n\n    this.opts.isNested = isNested;\n\n    isAutoCellHeight = (this.opts.cellHeight === 'auto');\n    if (isAutoCellHeight) {\n      // make the cell square initially\n      self.cellHeight(self.cellWidth(), true);\n    } else {\n      this.cellHeight(this.opts.cellHeight, true);\n    }\n    this.verticalMargin(this.opts.verticalMargin, true);\n\n    this.container.addClass(this.opts._class);\n\n    this._setStaticClass();\n\n    if (isNested) {\n      this.container.addClass('grid-stack-nested');\n    }\n\n    this._initStyles();\n\n    this.grid = new GridStackEngine(this.opts.column, function(nodes, detachNode) {\n      detachNode = (detachNode === undefined ? true : detachNode);\n      var maxHeight = 0;\n      this.nodes.forEach(function(n) {\n        maxHeight = Math.max(maxHeight, n.y + n.height);\n      });\n      nodes.forEach(function(n) {\n        if (detachNode && n._id === null) {\n          if (n.el) {\n            n.el.remove();\n          }\n        } else {\n          n.el\n            .attr('data-gs-x', n.x)\n            .attr('data-gs-y', n.y)\n            .attr('data-gs-width', n.width)\n            .attr('data-gs-height', n.height);\n        }\n      });\n      self._updateStyles(maxHeight + 10);\n    }, this.opts.float, this.opts.maxRow);\n\n    if (this.opts.auto) {\n      var elements = [];\n      var _this = this;\n      this.container.children('.' + this.opts.itemClass + ':not(.' + this.opts.placeholderClass + ')')\n        .each(function(index, el) {\n          el = $(el);\n          var x = parseInt(el.attr('data-gs-x'));\n          var y = parseInt(el.attr('data-gs-y'));\n          elements.push({\n            el: el,\n            // if x,y are missing (autoPosition) add them to end of list - but keep their respective DOM order\n            i: (Number.isNaN(x) ? 1000 : x) + (Number.isNaN(y) ? 1000 : y) * _this.opts.column\n          });\n        });\n      Utils.sortBy(elements, function(x) { return x.i; }).forEach(function(item) {\n        this._prepareElement(item.el);\n      }, this);\n    }\n\n    this.setAnimation(this.opts.animate);\n\n    this.placeholder = $(\n      '<div class=\"' + this.opts.placeholderClass + ' ' + this.opts.itemClass + '\">' +\n      '<div class=\"placeholder-content\">' + this.opts.placeholderText + '</div></div>').hide();\n\n    this._updateContainerHeight();\n\n    this._updateHeightsOnResize = Utils.throttle(function() {\n      self.cellHeight(self.cellWidth(), false);\n    }, 100);\n\n    /**\n     * called when we are being resized - check if the one Column Mode needs to be turned on/off\n     * and remember the prev columns we used.\n     */\n    this.onResizeHandler = function() {\n      if (isAutoCellHeight) {\n        self._updateHeightsOnResize();\n      }\n\n      if (self.opts.staticGrid) { return; }\n\n      if (!self.opts.disableOneColumnMode && (window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth) <= self.opts.minWidth) {\n        if (self.oneColumnMode) {  return; }\n        self.oneColumnMode = true;\n        self.setColumn(1);\n      } else {\n        if (!self.oneColumnMode) { return; }\n        self.oneColumnMode = false;\n        self.setColumn(self._prevColumn);\n      }\n    };\n\n    $(window).resize(this.onResizeHandler);\n    this.onResizeHandler();\n\n    if (!self.opts.staticGrid && typeof self.opts.removable === 'string') {\n      var trashZone = $(self.opts.removable);\n      if (!this.dd.isDroppable(trashZone)) {\n        this.dd.droppable(trashZone, self.opts.removableOptions);\n      }\n      this.dd\n        .on(trashZone, 'dropover', function(event, ui) {\n          var el = $(ui.draggable);\n          var node = el.data('_gridstack_node');\n          if (!node || node._grid !== self) {\n            return;\n          }\n          el.data('inTrashZone', true);\n          self._setupRemovingTimeout(el);\n        })\n        .on(trashZone, 'dropout', function(event, ui) {\n          var el = $(ui.draggable);\n          var node = el.data('_gridstack_node');\n          if (!node || node._grid !== self) {\n            return;\n          }\n          el.data('inTrashZone', false);\n          self._clearRemovingTimeout(el);\n        });\n    }\n\n    if (!self.opts.staticGrid && self.opts.acceptWidgets) {\n      var draggingElement = null;\n\n      var onDrag = function(event, ui) {\n        var el = draggingElement;\n        var node = el.data('_gridstack_node');\n        var pos = self.getCellFromPixel({left: event.pageX, top: event.pageY}, true);\n        var x = Math.max(0, pos.x);\n        var y = Math.max(0, pos.y);\n        if (!node._added) {\n          node._added = true;\n\n          node.el = el;\n          node.autoPosition = true;\n          node.x = x;\n          node.y = y;\n          self.grid.cleanNodes();\n          self.grid.beginUpdate(node);\n          self.grid.addNode(node);\n\n          self.container.append(self.placeholder);\n          self.placeholder\n            .attr('data-gs-x', node.x)\n            .attr('data-gs-y', node.y)\n            .attr('data-gs-width', node.width)\n            .attr('data-gs-height', node.height)\n            .show();\n          node.el = self.placeholder;\n          node._beforeDragX = node.x;\n          node._beforeDragY = node.y;\n\n          self._updateContainerHeight();\n        }\n        if (!self.grid.canMoveNode(node, x, y)) {\n          return;\n        }\n        self.grid.moveNode(node, x, y);\n        self._updateContainerHeight();\n      };\n\n      this.dd\n        .droppable(self.container, {\n          accept: function(el) {\n            el = $(el);\n            var node = el.data('_gridstack_node');\n            if (node && node._grid === self) {\n              return false;\n            }\n            return el.is(self.opts.acceptWidgets === true ? '.grid-stack-item' : self.opts.acceptWidgets);\n          }\n        })\n        .on(self.container, 'dropover', function(event, ui) {\n          var el = $(ui.draggable);\n          var width, height;\n\n          // see if we already have a node with widget/height and check for attributes\n          var origNode = el.data('_gridstack_node');\n          if (!origNode || !origNode.width || !origNode.height) {\n            var w = parseInt(el.attr('data-gs-width'));\n            if (w > 0) { origNode = origNode || {}; origNode.width = w; }\n            var h = parseInt(el.attr('data-gs-height'));\n            if (h > 0) { origNode = origNode || {}; origNode.height = h; }\n          }\n\n          // if not calculate the grid size based on element outer size\n          // height: Each row is cellHeight + verticalMargin, until last one which has no margin below\n          var cellWidth = self.cellWidth();\n          var cellHeight = self.cellHeight();\n          var verticalMargin = self.opts.verticalMargin;\n          width = origNode && origNode.width ? origNode.width : Math.ceil(el.outerWidth() / cellWidth);\n          height = origNode && origNode.height ? origNode.height : Math.round((el.outerHeight() + verticalMargin) / (cellHeight + verticalMargin));\n\n          draggingElement = el;\n\n          var node = self.grid._prepareNode({width: width, height: height, _added: false, _temporary: true});\n          node.isOutOfGrid = true;\n          el.data('_gridstack_node', node);\n          el.data('_gridstack_node_orig', origNode);\n\n          el.on('drag', onDrag);\n        })\n        .on(self.container, 'dropout', function(event, ui) {\n          // jquery-ui bug. Must verify widget is being dropped out\n          // check node variable that gets set when widget is out of grid\n          var el = $(ui.draggable);\n          if (!el.data('_gridstack_node')) {\n            return;\n          }\n          var node = el.data('_gridstack_node');\n          if (!node.isOutOfGrid) {\n            return;\n          }\n          el.unbind('drag', onDrag);\n          node.el = null;\n          self.grid.removeNode(node);\n          self.placeholder.detach();\n          self._updateContainerHeight();\n          el.data('_gridstack_node', el.data('_gridstack_node_orig'));\n        })\n        .on(self.container, 'drop', function(event, ui) {\n          self.placeholder.detach();\n\n          var node = $(ui.draggable).data('_gridstack_node');\n          node.isOutOfGrid = false;\n          node._grid = self;\n          var el = $(ui.draggable).clone(false);\n          el.data('_gridstack_node', node);\n          var originalNode = $(ui.draggable).data('_gridstack_node_orig');\n          if (originalNode !== undefined && originalNode._grid !== undefined) {\n            originalNode._grid._triggerRemoveEvent();\n          }\n          $(ui.helper).remove();\n          node.el = el;\n          self.placeholder.hide();\n          Utils.removePositioningStyles(el);\n          el.find('div.ui-resizable-handle').remove();\n\n          el\n            .attr('data-gs-x', node.x)\n            .attr('data-gs-y', node.y)\n            .attr('data-gs-width', node.width)\n            .attr('data-gs-height', node.height)\n            .addClass(self.opts.itemClass)\n            .enableSelection()\n            .removeData('draggable')\n            .removeClass('ui-draggable ui-draggable-dragging ui-draggable-disabled')\n            .unbind('drag', onDrag);\n          self.container.append(el);\n          self._prepareElementsByNode(el, node);\n          self._updateContainerHeight();\n          self.grid._addedNodes.push(node);\n          self._triggerAddEvent();\n          self._triggerChangeEvent();\n\n          self.grid.endUpdate();\n          $(ui.draggable).unbind('drag', onDrag);\n          $(ui.draggable).removeData('_gridstack_node');\n          $(ui.draggable).removeData('_gridstack_node_orig');\n          self.container.trigger('dropped', [originalNode, node]);\n        });\n    }\n  };\n\n  GridStack.prototype._triggerChangeEvent = function(/*forceTrigger*/) {\n    if (this.grid._batchMode) { return; }\n    // TODO: compare original X,Y,W,H (or entire node?) instead as _dirty can be a temporary state\n    var elements = this.grid.getDirtyNodes();\n    if (elements && elements.length) {\n      this.grid._layoutsNodesChange(elements);\n      this.container.trigger('change', [elements]);\n      this.grid.cleanNodes(); // clear dirty flags now that we called\n    }\n  };\n\n  GridStack.prototype._triggerAddEvent = function() {\n    if (this.grid._batchMode) { return; }\n    if (this.grid._addedNodes && this.grid._addedNodes.length > 0) {\n      this.grid._layoutsNodesChange(this.grid._addedNodes);\n      this.container.trigger('added', [this.grid._addedNodes]);\n      this.grid._addedNodes = [];\n    }\n  };\n\n  GridStack.prototype._triggerRemoveEvent = function() {\n    if (this.grid._batchMode) { return; }\n    if (this.grid._removedNodes && this.grid._removedNodes.length > 0) {\n      this.container.trigger('removed', [this.grid._removedNodes]);\n      this.grid._removedNodes = [];\n    }\n  };\n\n  GridStack.prototype._initStyles = function() {\n    if (this._stylesId) {\n      Utils.removeStylesheet(this._stylesId);\n    }\n    this._stylesId = 'gridstack-style-' + (Math.random() * 100000).toFixed();\n    this._styles = Utils.createStylesheet(this._stylesId);\n    if (this._styles !== null) {\n      this._styles._max = 0;\n    }\n  };\n\n  GridStack.prototype._updateStyles = function(maxHeight) {\n    if (this._styles === null || this._styles === undefined) {\n      return;\n    }\n\n    var prefix = '.' + this.opts._class + ' .' + this.opts.itemClass;\n    var self = this;\n    var getHeight;\n\n    if (maxHeight === undefined) {\n      maxHeight = this._styles._max;\n    }\n\n    this._initStyles();\n    this._updateContainerHeight();\n    if (!this.opts.cellHeight) { // The rest will be handled by CSS\n      return ;\n    }\n    if (this._styles._max !== 0 && maxHeight <= this._styles._max) { // Keep this._styles._max increasing\n      return ;\n    }\n\n    if (!this.opts.verticalMargin || this.opts.cellHeightUnit === this.opts.verticalMarginUnit) {\n      getHeight = function(nbRows, nbMargins) {\n        return (self.opts.cellHeight * nbRows + self.opts.verticalMargin * nbMargins) +\n          self.opts.cellHeightUnit;\n      };\n    } else {\n      getHeight = function(nbRows, nbMargins) {\n        if (!nbRows || !nbMargins) {\n          return (self.opts.cellHeight * nbRows + self.opts.verticalMargin * nbMargins) +\n            self.opts.cellHeightUnit;\n        }\n        return 'calc(' + ((self.opts.cellHeight * nbRows) + self.opts.cellHeightUnit) + ' + ' +\n          ((self.opts.verticalMargin * nbMargins) + self.opts.verticalMarginUnit) + ')';\n      };\n    }\n\n    if (this._styles._max === 0) {\n      Utils.insertCSSRule(this._styles, prefix, 'min-height: ' + getHeight(1, 0) + ';', 0);\n    }\n\n    if (maxHeight > this._styles._max) {\n      for (var i = this._styles._max; i < maxHeight; ++i) {\n        Utils.insertCSSRule(this._styles,\n          prefix + '[data-gs-height=\"' + (i + 1) + '\"]',\n          'height: ' + getHeight(i + 1, i) + ';',\n          i\n        );\n        Utils.insertCSSRule(this._styles,\n          prefix + '[data-gs-min-height=\"' + (i + 1) + '\"]',\n          'min-height: ' + getHeight(i + 1, i) + ';',\n          i\n        );\n        Utils.insertCSSRule(this._styles,\n          prefix + '[data-gs-max-height=\"' + (i + 1) + '\"]',\n          'max-height: ' + getHeight(i + 1, i) + ';',\n          i\n        );\n        Utils.insertCSSRule(this._styles,\n          prefix + '[data-gs-y=\"' + i + '\"]',\n          'top: ' + getHeight(i, i) + ';',\n          i\n        );\n      }\n      this._styles._max = maxHeight;\n    }\n  };\n\n  GridStack.prototype._updateContainerHeight = function() {\n    if (this.grid._batchMode) { return; }\n    var height = this.grid.getGridHeight();\n    // check for css min height. Each row is cellHeight + verticalMargin, until last one which has no margin below\n    var cssMinHeight = parseInt(this.container.css('min-height'));\n    if (cssMinHeight > 0) {\n      var verticalMargin = this.opts.verticalMargin;\n      var minHeight =  Math.round((cssMinHeight + verticalMargin) / (this.cellHeight() + verticalMargin));\n      if (height < minHeight) {\n        height = minHeight;\n      }\n    }\n    this.container.attr('data-gs-current-height', height);\n    if (!this.opts.cellHeight) {\n      return ;\n    }\n    if (!this.opts.verticalMargin) {\n      this.container.css('height', (height * (this.opts.cellHeight)) + this.opts.cellHeightUnit);\n    } else if (this.opts.cellHeightUnit === this.opts.verticalMarginUnit) {\n      this.container.css('height', (height * (this.opts.cellHeight + this.opts.verticalMargin) -\n        this.opts.verticalMargin) + this.opts.cellHeightUnit);\n    } else {\n      this.container.css('height', 'calc(' + ((height * (this.opts.cellHeight)) + this.opts.cellHeightUnit) +\n        ' + ' + ((height * (this.opts.verticalMargin - 1)) + this.opts.verticalMarginUnit) + ')');\n    }\n  };\n\n  GridStack.prototype._setupRemovingTimeout = function(el) {\n    var self = this;\n    var node = $(el).data('_gridstack_node');\n\n    if (node._removeTimeout || !self.opts.removable) {\n      return;\n    }\n    node._removeTimeout = setTimeout(function() {\n      el.addClass('grid-stack-item-removing');\n      node._isAboutToRemove = true;\n    }, self.opts.removeTimeout);\n  };\n\n  GridStack.prototype._clearRemovingTimeout = function(el) {\n    var node = $(el).data('_gridstack_node');\n\n    if (!node._removeTimeout) {\n      return;\n    }\n    clearTimeout(node._removeTimeout);\n    node._removeTimeout = null;\n    el.removeClass('grid-stack-item-removing');\n    node._isAboutToRemove = false;\n  };\n\n  GridStack.prototype._prepareElementsByNode = function(el, node) {\n    var self = this;\n\n    var cellWidth;\n    var cellHeight;\n\n    var dragOrResize = function(event, ui) {\n      var x = Math.round(ui.position.left / cellWidth);\n      var y = Math.floor((ui.position.top + cellHeight / 2) / cellHeight);\n      var width;\n      var height;\n\n      if (event.type !== 'drag') {\n        width = Math.round(ui.size.width / cellWidth);\n        height = Math.round(ui.size.height / cellHeight);\n      }\n\n      if (event.type === 'drag') {\n        var distance = ui.position.top - node._prevYPix;\n        node._prevYPix = ui.position.top;\n        Utils.updateScrollPosition(el[0], ui, distance);\n        if (el.data('inTrashZone') || x < 0 || x >= self.grid.column || y < 0 ||\n          (!self.grid.float && y > self.grid.getGridHeight())) {\n          if (!node._temporaryRemoved) {\n            if (self.opts.removable === true) {\n              self._setupRemovingTimeout(el);\n            }\n\n            x = node._beforeDragX;\n            y = node._beforeDragY;\n\n            self.placeholder.detach();\n            self.placeholder.hide();\n            self.grid.removeNode(node);\n            self._updateContainerHeight();\n\n            node._temporaryRemoved = true;\n          } else {\n            return;\n          }\n        } else {\n          self._clearRemovingTimeout(el);\n\n          if (node._temporaryRemoved) {\n            self.grid.addNode(node);\n            self.placeholder\n              .attr('data-gs-x', x)\n              .attr('data-gs-y', y)\n              .attr('data-gs-width', width)\n              .attr('data-gs-height', height)\n              .show();\n            self.container.append(self.placeholder);\n            node.el = self.placeholder;\n            node._temporaryRemoved = false;\n          }\n        }\n      } else if (event.type === 'resize')  {\n        if (x < 0) {\n          return;\n        }\n      }\n      // width and height are undefined if not resizing\n      var lastTriedWidth = width !== undefined ? width : node.lastTriedWidth;\n      var lastTriedHeight = height !== undefined ? height : node.lastTriedHeight;\n      if (!self.grid.canMoveNode(node, x, y, width, height) ||\n        (node.lastTriedX === x && node.lastTriedY === y &&\n        node.lastTriedWidth === lastTriedWidth && node.lastTriedHeight === lastTriedHeight)) {\n        return;\n      }\n      node.lastTriedX = x;\n      node.lastTriedY = y;\n      node.lastTriedWidth = width;\n      node.lastTriedHeight = height;\n      self.grid.moveNode(node, x, y, width, height);\n      self._updateContainerHeight();\n\n      if (event.type === 'resize')  {\n        $(event.target).trigger('gsresize', node);\n      }\n    };\n\n    var onStartMoving = function(event, ui) {\n      self.container.append(self.placeholder);\n      var o = $(this);\n      self.grid.cleanNodes();\n      self.grid.beginUpdate(node);\n      cellWidth = self.cellWidth();\n      var strictCellHeight = self.cellHeight();\n      // TODO: cellHeight = cellHeight() causes issue (i.e. remove strictCellHeight above) otherwise\n      // when sizing up we jump almost right away to next size instead of half way there. Not sure\n      // why as we don't use ceil() in many places but round() instead.\n      cellHeight = self.container.height() / parseInt(self.container.attr('data-gs-current-height'));\n      self.placeholder\n        .attr('data-gs-x', o.attr('data-gs-x'))\n        .attr('data-gs-y', o.attr('data-gs-y'))\n        .attr('data-gs-width', o.attr('data-gs-width'))\n        .attr('data-gs-height', o.attr('data-gs-height'))\n        .show();\n      node.el = self.placeholder;\n      node._beforeDragX = node.x;\n      node._beforeDragY = node.y;\n      node._prevYPix = ui.position.top;\n      var minHeight = (node.minHeight || 1);\n      var verticalMargin = self.opts.verticalMargin;\n\n      // mineHeight - Each row is cellHeight + verticalMargin, until last one which has no margin below\n      self.dd.resizable(el, 'option', 'minWidth', cellWidth * (node.minWidth || 1));\n      self.dd.resizable(el, 'option', 'minHeight', (strictCellHeight * minHeight) + (minHeight - 1) * verticalMargin);\n\n      if (event.type === 'resizestart') {\n        o.find('.grid-stack-item').trigger('resizestart');\n      }\n    };\n\n    var onEndMoving = function(event, ui) {\n      var o = $(this);\n      if (!o.data('_gridstack_node')) {\n        return;\n      }\n\n      // var forceNotify = false; what is the point of calling 'change' event with no data, when the 'removed' event is already called ?\n      self.placeholder.detach();\n      node.el = o;\n      self.placeholder.hide();\n\n      if (node._isAboutToRemove) {\n        // forceNotify = true;\n        var gridToNotify = el.data('_gridstack_node')._grid;\n        gridToNotify._triggerRemoveEvent();\n        el.removeData('_gridstack_node');\n        el.remove();\n      } else {\n        self._clearRemovingTimeout(el);\n        if (!node._temporaryRemoved) {\n          Utils.removePositioningStyles(o);\n          o\n            .attr('data-gs-x', node.x)\n            .attr('data-gs-y', node.y)\n            .attr('data-gs-width', node.width)\n            .attr('data-gs-height', node.height);\n        } else {\n          Utils.removePositioningStyles(o);\n          o\n            .attr('data-gs-x', node._beforeDragX)\n            .attr('data-gs-y', node._beforeDragY)\n            .attr('data-gs-width', node.width)\n            .attr('data-gs-height', node.height);\n          node.x = node._beforeDragX;\n          node.y = node._beforeDragY;\n          node._temporaryRemoved = false;\n          self.grid.addNode(node);\n        }\n      }\n      self._updateContainerHeight();\n      self._triggerChangeEvent(/*forceNotify*/);\n\n      self.grid.endUpdate();\n\n      var nestedGrids = o.find('.grid-stack');\n      if (nestedGrids.length && event.type === 'resizestop') {\n        nestedGrids.each(function(index, el) {\n          $(el).data('gridstack').onResizeHandler();\n        });\n        o.find('.grid-stack-item').trigger('resizestop');\n        o.find('.grid-stack-item').trigger('gsresizestop');\n      }\n      if (event.type === 'resizestop') {\n        self.container.trigger('gsresizestop', o);\n      }\n    };\n\n    this.dd\n      .draggable(el, {\n        start: onStartMoving,\n        stop: onEndMoving,\n        drag: dragOrResize\n      })\n      .resizable(el, {\n        start: onStartMoving,\n        stop: onEndMoving,\n        resize: dragOrResize\n      });\n\n    if (node.noMove || this.opts.disableDrag || this.opts.staticGrid) {\n      this.dd.draggable(el, 'disable');\n    }\n\n    if (node.noResize || this.opts.disableResize || this.opts.staticGrid) {\n      this.dd.resizable(el, 'disable');\n    }\n\n    this._writeAttr(el, node);\n  };\n\n  GridStack.prototype._prepareElement = function(el, triggerAddEvent) {\n    triggerAddEvent = triggerAddEvent !== undefined ? triggerAddEvent : false;\n    var self = this;\n    el = $(el);\n\n    el.addClass(this.opts.itemClass);\n    var node = this._readAttr(el, {el: el, _grid: self});\n    node = self.grid.addNode(node, triggerAddEvent);\n    el.data('_gridstack_node', node);\n\n    this._prepareElementsByNode(el, node);\n  };\n\n  /** call to write any default attributes back to element */\n  GridStack.prototype._writeAttr = function(el, node) {\n    el = $(el);\n    node = node || {};\n    // Note: passing null removes the attr in jquery\n    if (node.x !== undefined) { el.attr('data-gs-x', node.x); }\n    if (node.y !== undefined) { el.attr('data-gs-y', node.y); }\n    if (node.width !== undefined) { el.attr('data-gs-width', node.width); }\n    if (node.height !== undefined) { el.attr('data-gs-height', node.height); }\n    if (node.autoPosition !== undefined) { el.attr('data-gs-auto-position', node.autoPosition ? true : null); }\n    if (node.minWidth !== undefined) { el.attr('data-gs-min-width', node.minWidth); }\n    if (node.maxWidth !== undefined) { el.attr('data-gs-max-width', node.maxWidth); }\n    if (node.minHeight !== undefined) { el.attr('data-gs-min-height', node.minHeight); }\n    if (node.maxHeight !== undefined) { el.attr('data-gs-max-height', node.maxHeight); }\n    if (node.noResize !== undefined) { el.attr('data-gs-no-resize', node.noResize ? true : null); }\n    if (node.noMove !== undefined) { el.attr('data-gs-no-move', node.noMove ? true : null); }\n    if (node.locked !== undefined) { el.attr('data-gs-locked', node.locked ? true : null); }\n    if (node.resizeHandles !== undefined) { el.attr('data-gs-resize-handles', node.resizeHandles); }\n    if (node.id !== undefined) { el.attr('data-gs-id', node.id); }\n  };\n\n  /** call to write any default attributes back to element */\n  GridStack.prototype._readAttr = function(el, node) {\n    el = $(el);\n    node = node || {};\n    node.x = el.attr('data-gs-x');\n    node.y = el.attr('data-gs-y');\n    node.width = el.attr('data-gs-width');\n    node.height = el.attr('data-gs-height');\n    node.autoPosition = Utils.toBool(el.attr('data-gs-auto-position'));\n    node.maxWidth = el.attr('data-gs-max-width');\n    node.minWidth = el.attr('data-gs-min-width');\n    node.maxHeight = el.attr('data-gs-max-height');\n    node.minHeight = el.attr('data-gs-min-height');\n    node.noResize = Utils.toBool(el.attr('data-gs-no-resize'));\n    node.noMove = Utils.toBool(el.attr('data-gs-no-move'));\n    node.locked = Utils.toBool(el.attr('data-gs-locked'));\n    node.resizeHandles = el.attr('data-gs-resize-handles');\n    node.id = el.attr('data-gs-id');\n    return node;\n  };\n\n  GridStack.prototype.setAnimation = function(enable) {\n    if (enable) {\n      this.container.addClass('grid-stack-animate');\n    } else {\n      this.container.removeClass('grid-stack-animate');\n    }\n  };\n\n  GridStack.prototype.addWidget = function(el, node, y, width, height, autoPosition, minWidth, maxWidth, minHeight, maxHeight, id) {\n\n    // new way of calling with an object - make sure all items have been properly initialized\n    if (node === undefined || typeof node === 'object') {\n      // Tempting to initialize the passed in node with default and valid values, but this break knockout demos\n      // as the actual value are filled in when _prepareElement() calls el.attr('data-gs-xyz) before adding the node.\n      // node = this.grid._prepareNode(node);\n      node = node || {};\n    } else {\n      // old legacy way of calling with items spelled out - call us back with single object instead (so we can properly initialized values)\n      return this.addWidget(el, {x: node, y: y, width: width, height: height, autoPosition: autoPosition,\n        minWidth: minWidth, maxWidth: maxWidth, minHeight: minHeight, maxHeight: maxHeight, id: id});\n    }\n\n    el = $(el);\n    this._writeAttr(el, node);\n    this.container.append(el);\n    this._prepareElement(el, true);\n    this._updateContainerHeight();\n    this._triggerAddEvent();\n    // this._triggerChangeEvent(true); already have AddEvent\n\n    return el;\n  };\n\n  GridStack.prototype.makeWidget = function(el) {\n    el = $(el);\n    this._prepareElement(el, true);\n    this._updateContainerHeight();\n    this._triggerAddEvent();\n    // this._triggerChangeEvent(true); already have AddEvent\n\n    return el;\n  };\n\n  GridStack.prototype.willItFit = function(x, y, width, height, autoPosition) {\n    var node = {x: x, y: y, width: width, height: height, autoPosition: autoPosition};\n    return this.grid.canBePlacedWithRespectToHeight(node);\n  };\n\n  GridStack.prototype.removeWidget = function(el, detachNode) {\n    detachNode = (detachNode === undefined ? true : detachNode);\n    el = $(el);\n    var node = el.data('_gridstack_node');\n    // For Meteor support: https://github.com/gridstack/gridstack.js/pull/272\n    if (!node) {\n      node = this.grid.getNodeDataByDOMEl(el);\n    }\n\n    el.removeData('_gridstack_node');\n    this.grid.removeNode(node, detachNode);\n    this._triggerRemoveEvent();\n    // this._triggerChangeEvent(true); already have removeEvent\n  };\n\n  GridStack.prototype.removeAll = function(detachNode) {\n    if (detachNode !== false) {\n      // remove our data structure before list gets emptied and DOM elements stay behind\n      this.grid.nodes.forEach(function(node) { node.el.removeData('_gridstack_node') });\n    }\n    this.grid.removeAll(detachNode);\n    this._triggerRemoveEvent();\n  };\n\n  GridStack.prototype.destroy = function(detachGrid) {\n    $(window).off('resize', this.onResizeHandler);\n    this.disable();\n    if (detachGrid !== undefined && !detachGrid) {\n      this.removeAll(false);\n      this.container.removeData('gridstack');\n    } else {\n      this.container.remove();\n    }\n    Utils.removeStylesheet(this._stylesId);\n    if (this.grid) {\n      this.grid = null;\n    }\n  };\n\n  GridStack.prototype.resizable = function(el, val) {\n    var self = this;\n    el = $(el);\n    el.each(function(index, el) {\n      el = $(el);\n      var node = el.data('_gridstack_node');\n      if (!node) { return; }\n      node.noResize = !(val || false);\n      if (node.noResize) {\n        self.dd.resizable(el, 'disable');\n      } else {\n        self.dd.resizable(el, 'enable');\n      }\n    });\n    return this;\n  };\n\n  GridStack.prototype.movable = function(el, val) {\n    var self = this;\n    el = $(el);\n    el.each(function(index, el) {\n      el = $(el);\n      var node = el.data('_gridstack_node');\n      if (!node) { return; }\n      node.noMove = !(val || false);\n      if (node.noMove) {\n        self.dd.draggable(el, 'disable');\n        el.removeClass('ui-draggable-handle');\n      } else {\n        self.dd.draggable(el, 'enable');\n        el.addClass('ui-draggable-handle');\n      }\n    });\n    return this;\n  };\n\n  GridStack.prototype.enableMove = function(doEnable, includeNewWidgets) {\n    this.movable(this.container.children('.' + this.opts.itemClass), doEnable);\n    if (includeNewWidgets) {\n      this.opts.disableDrag = !doEnable;\n    }\n  };\n\n  GridStack.prototype.enableResize = function(doEnable, includeNewWidgets) {\n    this.resizable(this.container.children('.' + this.opts.itemClass), doEnable);\n    if (includeNewWidgets) {\n      this.opts.disableResize = !doEnable;\n    }\n  };\n\n  GridStack.prototype.disable = function() {\n    this.movable(this.container.children('.' + this.opts.itemClass), false);\n    this.resizable(this.container.children('.' + this.opts.itemClass), false);\n    this.container.trigger('disable');\n  };\n\n  GridStack.prototype.enable = function() {\n    this.movable(this.container.children('.' + this.opts.itemClass), true);\n    this.resizable(this.container.children('.' + this.opts.itemClass), true);\n    this.container.trigger('enable');\n  };\n\n  GridStack.prototype.locked = function(el, val) {\n    el = $(el);\n    el.each(function(index, el) {\n      el = $(el);\n      var node = el.data('_gridstack_node');\n      if (!node) { return; }\n      node.locked = (val || false);\n      el.attr('data-gs-locked', node.locked ? 'yes' : null);\n    });\n    return this;\n  };\n\n  GridStack.prototype.maxHeight = function(el, val) {\n    el = $(el);\n    el.each(function(index, el) {\n      el = $(el);\n      var node = el.data('_gridstack_node');\n      if (!node) { return; }\n      if (!isNaN(val)) {\n        node.maxHeight = (val || false);\n        el.attr('data-gs-max-height', val);\n      }\n    });\n    return this;\n  };\n\n  GridStack.prototype.minHeight = function(el, val) {\n    el = $(el);\n    el.each(function(index, el) {\n      el = $(el);\n      var node = el.data('_gridstack_node');\n      if (!node) { return; }\n      if (!isNaN(val)) {\n        node.minHeight = (val || false);\n        el.attr('data-gs-min-height', val);\n      }\n    });\n    return this;\n  };\n\n  GridStack.prototype.maxWidth = function(el, val) {\n    el = $(el);\n    el.each(function(index, el) {\n      el = $(el);\n      var node = el.data('_gridstack_node');\n      if (!node) { return; }\n      if (!isNaN(val)) {\n        node.maxWidth = (val || false);\n        el.attr('data-gs-max-width', val);\n      }\n    });\n    return this;\n  };\n\n  GridStack.prototype.minWidth = function(el, val) {\n    el = $(el);\n    el.each(function(index, el) {\n      el = $(el);\n      var node = el.data('_gridstack_node');\n      if (!node) { return; }\n      if (!isNaN(val)) {\n        node.minWidth = (val || false);\n        el.attr('data-gs-min-width', val);\n      }\n    });\n    return this;\n  };\n\n  GridStack.prototype._updateElement = function(el, callback) {\n    el = $(el).first();\n    var node = el.data('_gridstack_node');\n    if (!node) { return; }\n    var self = this;\n\n    self.grid.cleanNodes();\n    self.grid.beginUpdate(node);\n\n    callback.call(this, el, node);\n\n    self._updateContainerHeight();\n    self._triggerChangeEvent();\n\n    self.grid.endUpdate();\n  };\n\n  GridStack.prototype.resize = function(el, width, height) {\n    this._updateElement(el, function(el, node) {\n      width = (width !== null && width !== undefined) ? width : node.width;\n      height = (height !== null && height !== undefined) ? height : node.height;\n\n      this.grid.moveNode(node, node.x, node.y, width, height);\n    });\n  };\n\n  GridStack.prototype.move = function(el, x, y) {\n    this._updateElement(el, function(el, node) {\n      x = (x !== null && x !== undefined) ? x : node.x;\n      y = (y !== null && y !== undefined) ? y : node.y;\n\n      this.grid.moveNode(node, x, y, node.width, node.height);\n    });\n  };\n\n  GridStack.prototype.update = function(el, x, y, width, height) {\n    this._updateElement(el, function(el, node) {\n      x = (x !== null && x !== undefined) ? x : node.x;\n      y = (y !== null && y !== undefined) ? y : node.y;\n      width = (width !== null && width !== undefined) ? width : node.width;\n      height = (height !== null && height !== undefined) ? height : node.height;\n\n      this.grid.moveNode(node, x, y, width, height);\n    });\n  };\n\n  /**\n   * relayout grid items to reclaim any empty space\n   */\n  GridStack.prototype.compact = function() {\n    if (this.grid.nodes.length === 0) { return; }\n    this.batchUpdate();\n    this.grid._sortNodes();\n    var nodes = this.grid.nodes;\n    this.grid.nodes = []; // pretend we have no nodes to conflict layout to start with...\n    nodes.forEach(function(n) {\n      if (!n.noMove && !n.locked) {\n        n.autoPosition = true;\n      }\n      this.grid.addNode(n, false); // 'false' for add event trigger\n    }, this);\n    this.commit();\n  };\n\n  GridStack.prototype.verticalMargin = function(val, noUpdate) {\n    if (val === undefined) {\n      return this.opts.verticalMargin;\n    }\n\n    var heightData = Utils.parseHeight(val);\n\n    if (this.opts.verticalMarginUnit === heightData.unit && this.opts.maxRow === heightData.height) {\n      return ;\n    }\n    this.opts.verticalMarginUnit = heightData.unit;\n    this.opts.verticalMargin = heightData.height;\n\n    if (!noUpdate) {\n      this._updateStyles();\n    }\n  };\n\n  /** set/get the current cell height value */\n  GridStack.prototype.cellHeight = function(val, noUpdate) {\n    // getter - returns the opts stored height else compute it...\n    if (val === undefined) {\n      if (this.opts.cellHeight && this.opts.cellHeight !== 'auto') {\n        return this.opts.cellHeight;\n      }\n      // compute the height taking margin into account (each row has margin other than last one)\n      var o = this.container.children('.' + this.opts.itemClass).first();\n      var height = o.attr('data-gs-height');\n      var verticalMargin = this.opts.verticalMargin;\n      return Math.round((o.outerHeight() - (height - 1) * verticalMargin) / height);\n    }\n\n    // setter - updates the cellHeight value if they changed\n    var heightData = Utils.parseHeight(val);\n    if (this.opts.cellHeightUnit === heightData.unit && this.opts.cellHeight === heightData.height) {\n      return ;\n    }\n    this.opts.cellHeightUnit = heightData.unit;\n    this.opts.cellHeight = heightData.height;\n\n    if (!noUpdate) {\n      this._updateStyles();\n    }\n  };\n\n  GridStack.prototype.cellWidth = function() {\n    // TODO: take margin into account ($horizontal_padding in .scss) to make cellHeight='auto' square ? (see 810-many-columns.html)\n    return Math.round(this.container.outerWidth() / this.opts.column);\n  };\n\n  GridStack.prototype.getCellFromPixel = function(position, useOffset) {\n    var containerPos = (useOffset !== undefined && useOffset) ?\n      this.container.offset() : this.container.position();\n    var relativeLeft = position.left - containerPos.left;\n    var relativeTop = position.top - containerPos.top;\n\n    var columnWidth = Math.floor(this.container.width() / this.opts.column);\n    var rowHeight = Math.floor(this.container.height() / parseInt(this.container.attr('data-gs-current-height')));\n\n    return {x: Math.floor(relativeLeft / columnWidth), y: Math.floor(relativeTop / rowHeight)};\n  };\n\n  GridStack.prototype.batchUpdate = function() {\n    this.grid.batchUpdate();\n  };\n\n  GridStack.prototype.commit = function() {\n    this.grid.commit();\n    this._triggerRemoveEvent();\n    this._triggerAddEvent();\n    this._triggerChangeEvent();\n  };\n\n  GridStack.prototype.isAreaEmpty = function(x, y, width, height) {\n    return this.grid.isAreaEmpty(x, y, width, height);\n  };\n\n  GridStack.prototype.setStatic = function(staticValue) {\n    this.opts.staticGrid = (staticValue === true);\n    this.enableMove(!staticValue);\n    this.enableResize(!staticValue);\n    this._setStaticClass();\n  };\n\n  GridStack.prototype._setStaticClass = function() {\n    var staticClassName = 'grid-stack-static';\n\n    if (this.opts.staticGrid === true) {\n      this.container.addClass(staticClassName);\n    } else {\n      this.container.removeClass(staticClassName);\n    }\n  };\n\n  /** called whenever a node is added or moved - updates the cached layouts */\n  GridStackEngine.prototype._layoutsNodesChange = function(nodes) {\n    if (!this._layouts || this._ignoreLayoutsNodeChange) return;\n    // remove smaller layouts - we will re-generate those on the fly... larger ones need to update\n    this._layouts.forEach(function(layout, column) {\n      if (!layout || column === this.column) return;\n      if (column < this.column) {\n        this._layouts[column] = undefined;\n      }\n      else {\n        // we save the original x,y,w (h isn't cached) to see what actually changed to propagate better.\n        // Note: we don't need to check against out of bound scaling/moving as that will be done when using those cache values.\n        nodes.forEach(function(node) {\n          var n = layout.find(function(l) { return l._id === node._id });\n          if (!n) return; // no cache for new nodes. Will use those values.\n          var ratio = column / this.column;\n          // Y changed, push down same amount\n          // TODO: detect doing item 'swaps' will help instead of move (especially in 1 column mode)\n          if (node.y !== node._origY) {\n            n.y += (node.y - node._origY);\n          }\n          // X changed, scale from new position\n          if (node.x !== node._origX) {\n            n.x = Math.round(node.x * ratio);\n          }\n          // width changed, scale from new width\n          if (node.width !== node._origW) {\n            n.width = Math.round(node.width * ratio);\n          }\n          // ...height always carries over from cache\n        }, this);\n      }\n    }, this);\n\n    this._saveInitial(); // reset current value now that we diffed.\n  }\n\n  /**\n   * Called to scale the widget width & position up/down based on the column change.\n   * Note we store previous layouts (especially original ones) to make it possible to go\n   * from say 12 -> 1 -> 12 and get back to where we were.\n   *\n   * oldColumn: previous number of columns\n   * column:    new column number\n   * nodes?:    different sorted list (ex: DOM order) instead of current list\n   */\n  GridStackEngine.prototype._updateNodeWidths = function(oldColumn, column, nodes) {\n    if (!this.nodes.length || oldColumn === column) { return; }\n\n    // cache the current layout in case they want to go back (like 12 -> 1 -> 12) as it requires original data\n    var copy = [this.nodes.length];\n    this.nodes.forEach(function(n, i) {copy[i] = {x: n.x, y: n.y, width: n.width, _id: n._id}}); // only thing we change is x,y,w and id to find it back\n    this._layouts = this._layouts || []; // use array to find larger quick\n    this._layouts[oldColumn] = copy;\n\n    // if we're going to 1 column and using DOM order rather than default sorting, then generate that layout\n    if (column === 1 && nodes && nodes.length) {\n      var top = 0;\n      nodes.forEach(function(n) {\n        n.x = 0;\n        n.width = 1;\n        n.y = Math.max(n.y, top);\n        top = n.y + n.height;\n      });\n    } else {\n      nodes = Utils.sort(this.nodes, -1, oldColumn); // current column reverse sorting so we can insert last to front (limit collision)\n    }\n\n    // see if we have cached previous layout.\n    var cacheNodes = this._layouts[column] || [];\n    // if not AND we are going up in size start with the largest layout as down-scaling is more accurate\n    var lastIndex = this._layouts.length - 1;\n    if (cacheNodes.length === 0 && column > oldColumn && column < lastIndex) {\n      cacheNodes = this._layouts[lastIndex] || [];\n      if (cacheNodes.length) {\n        // pretend we came from that larger column by assigning those values as starting point\n        oldColumn = lastIndex;\n        cacheNodes.forEach(function(cacheNode) {\n          var j = nodes.findIndex(function(n) {return n && n._id === cacheNode._id});\n          if (j !== -1) {\n            // still current, use cache info positions\n            nodes[j].x = cacheNode.x;\n            nodes[j].y = cacheNode.y;\n            nodes[j].width = cacheNode.width;\n          }\n        });\n        cacheNodes = []; // we still don't have new column cached data... will generate from larger one.\n      }\n    }\n\n    // if we found cache re-use those nodes that are still current\n    var newNodes = [];\n    cacheNodes.forEach(function(cacheNode) {\n      var j = nodes.findIndex(function(n) {return n && n._id === cacheNode._id});\n      if (j !== -1) {\n        // still current, use cache info positions\n        nodes[j].x = cacheNode.x;\n        nodes[j].y = cacheNode.y;\n        nodes[j].width = cacheNode.width;\n        newNodes.push(nodes[j]);\n        nodes[j] = null; // erase it so we know what's left\n      }\n    });\n    // ...and add any extra non-cached ones\n    var ratio = column / oldColumn;\n    nodes.forEach(function(node) {\n      if (!node) return;\n      node.x = (column === 1 ? 0 : Math.round(node.x * ratio));\n      node.width = ((column === 1 || oldColumn === 1) ? 1 : (Math.round(node.width * ratio) || 1));\n      newNodes.push(node);\n    });\n\n    // finally relayout them in reverse order (to get correct placement)\n    newNodes = Utils.sort(newNodes, -1, column);\n    this._ignoreLayoutsNodeChange = true;\n    this.batchUpdate();\n    this.nodes = []; // pretend we have no nodes to start with (we use same structures) to simplify layout\n    newNodes.forEach(function(node) {\n      this.addNode(node, false); // 'false' for add event trigger\n      node._dirty = true; // force attr update\n    }, this);\n    this.commit();\n    delete this._ignoreLayoutsNodeChange;\n\n    // save this initial layout so we can see what changed and apply changes to other layouts better (diff)\n    this._saveInitial();\n  }\n\n  /** called to save initial position/size */\n  GridStackEngine.prototype._saveInitial = function() {\n    this.nodes.forEach(function(n) {\n      n._origX = n.x;\n      n._origY = n.y;\n      n._origW = n.width;\n      n._origH = n.height;\n    });\n  }\n\n  /**\n   * Modify number of columns in the grid. Will attempt to update existing widgets\n   * to conform to new number of columns. Requires `gridstack-extra.css` or `gridstack-extra.min.css` for [1-11],\n   * else you will need to generate correct CSS (see https://github.com/gridstack/gridstack.js#change-grid-columns)\n   * @param column - Integer > 0 (default 12).\n   * @param doNotPropagate if true existing widgets will not be updated (optional)\n   */\n  GridStack.prototype.setColumn = function(column, doNotPropagate) {\n    if (this.opts.column === column) { return; }\n    var oldColumn = this.opts.column;\n\n    // if we go into 1 column mode (which happens if we're sized less than minWidth unless disableOneColumnMode is on)\n    // then remember the original columns so we can restore.\n    if (column === 1) {\n      this._prevColumn = oldColumn;\n    } else {\n      delete this._prevColumn;\n    }\n\n    this.container.removeClass('grid-stack-' + oldColumn);\n    this.container.addClass('grid-stack-' + column);\n    this.opts.column = this.grid.column = column;\n\n    if (doNotPropagate === true) { return; }\n\n    // update the items now - see if the dom order nodes should be passed instead (else default to current list)\n    var domNodes;\n    if (this.opts.oneColumnModeDomSort && column === 1) {\n      domNodes = [];\n      this.container.children('.' + this.opts.itemClass).each(function(index, el) {\n        var node = $(el).data('_gridstack_node');\n        if (node) { domNodes.push(node); }\n      });\n      if (!domNodes.length) { domNodes = undefined; }\n    }\n    this.grid._updateNodeWidths(oldColumn, column, domNodes);\n\n    // and trigger our event last...\n    this.grid._ignoreLayoutsNodeChange = true;\n    this._triggerChangeEvent();\n    delete this.grid._ignoreLayoutsNodeChange;\n  };\n\n  GridStack.prototype.float = function(val) {\n    // getter - returns the opts stored mode\n    if (val === undefined) {\n      return this.opts.float || false;\n    }\n    // setter - updates the mode and relayout if gravity is back on\n    if (this.opts.float === val) { return; }\n    this.opts.float = this.grid.float = val || false;\n    if (!val) {\n      this.grid._packNodes();\n      this.grid._notify();\n    }\n  };\n\n  // legacy method renames\n  GridStack.prototype.setGridWidth = obsolete(GridStack.prototype.setColumn,\n    'setGridWidth', 'setColumn', 'v0.5.3');\n\n  scope.GridStackUI = GridStack;\n\n  scope.GridStackUI.Utils = Utils;\n  scope.GridStackUI.Engine = GridStackEngine;\n  scope.GridStackUI.GridStackDragDropPlugin = GridStackDragDropPlugin;\n\n  $.fn.gridstack = function(opts) {\n    return this.each(function() {\n      var o = $(this);\n      if (!o.data('gridstack')) {\n        o\n          .data('gridstack', new GridStack(this, opts));\n      }\n    });\n  };\n\n  return scope.GridStackUI;\n});\n"

/***/ }),

/***/ 496:
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(4)(__webpack_require__(497))

/***/ }),

/***/ 497:
/***/ (function(module, exports) {

module.exports = "/** gridstack.js 0.6.3 - JQuery UI Drag&Drop plugin @preserve */\n/**\n * https://gridstackjs.com/\n * (c) 2014-2020 Alain Dumesny, Dylan Weiss, Pavel Reznikov\n * gridstack.js may be freely distributed under the MIT license.\n*/\n(function(factory) {\n  if (typeof define === 'function' && define.amd) {\n    define(['jquery', 'gridstack', 'exports'], factory);\n  } else if (typeof exports !== 'undefined') {\n    try { jQuery = require('jquery'); } catch (e) {}\n    try { gridstack = require('gridstack'); } catch (e) {}\n    factory(jQuery, gridstack.GridStackUI, exports);\n  } else {\n    factory(jQuery, GridStackUI, window);\n  }\n})(function($, GridStackUI, scope) {\n  /**\n  * @class JQueryUIGridStackDragDropPlugin\n  * jQuery UI implementation of drag'n'drop gridstack plugin.\n  */\n  function JQueryUIGridStackDragDropPlugin(grid) {\n    GridStackUI.GridStackDragDropPlugin.call(this, grid);\n  }\n\n  GridStackUI.GridStackDragDropPlugin.registerPlugin(JQueryUIGridStackDragDropPlugin);\n\n  JQueryUIGridStackDragDropPlugin.prototype = Object.create(GridStackUI.GridStackDragDropPlugin.prototype);\n  JQueryUIGridStackDragDropPlugin.prototype.constructor = JQueryUIGridStackDragDropPlugin;\n\n  JQueryUIGridStackDragDropPlugin.prototype.resizable = function(el, opts) {\n    el = $(el);\n    if (opts === 'disable' || opts === 'enable') {\n      el.resizable(opts);\n    } else if (opts === 'option') {\n      var key = arguments[2];\n      var value = arguments[3];\n      el.resizable(opts, key, value);\n    } else {\n      var handles = el.data('gs-resize-handles') ? el.data('gs-resize-handles') :\n        this.grid.opts.resizable.handles;\n      el.resizable($.extend({}, this.grid.opts.resizable, {\n        handles: handles\n      }, {\n        start: opts.start || function() {},\n        stop: opts.stop || function() {},\n        resize: opts.resize || function() {}\n      }));\n    }\n    return this;\n  };\n\n  JQueryUIGridStackDragDropPlugin.prototype.draggable = function(el, opts) {\n    el = $(el);\n    if (opts === 'disable' || opts === 'enable') {\n      el.draggable(opts);\n    } else {\n      el.draggable($.extend({}, this.grid.opts.draggable, {\n        containment: (this.grid.opts.isNested && !this.grid.opts.dragOut) ?\n          this.grid.container.parent() :\n          (this.grid.opts.draggable.containment || null),\n        start: opts.start || function() {},\n        stop: opts.stop || function() {},\n        drag: opts.drag || function() {}\n      }));\n    }\n    return this;\n  };\n\n  JQueryUIGridStackDragDropPlugin.prototype.droppable = function(el, opts) {\n    el = $(el);\n    el.droppable(opts);\n    return this;\n  };\n\n  JQueryUIGridStackDragDropPlugin.prototype.isDroppable = function(el, opts) {\n    el = $(el);\n    return Boolean(el.data('droppable'));\n  };\n\n  JQueryUIGridStackDragDropPlugin.prototype.on = function(el, eventName, callback) {\n    $(el).on(eventName, callback);\n    return this;\n  };\n\n  scope.JQueryUIGridStackDragDropPlugin = JQueryUIGridStackDragDropPlugin;\n\n  return JQueryUIGridStackDragDropPlugin;\n});\n"

/***/ }),

/***/ 498:
/***/ (function(module, exports, __webpack_require__) {

// extracted by mini-css-extract-plugin

/***/ }),

/***/ 499:
/***/ (function(module, exports, __webpack_require__) {

// extracted by mini-css-extract-plugin

/***/ }),

/***/ 5:
/***/ (function(module, exports, __webpack_require__) {

/* WEBPACK VAR INJECTION */(function(global) {/*global window, global*/
var util = __webpack_require__(7)
var assert = __webpack_require__(12)
function now() { return new Date().getTime() }

var slice = Array.prototype.slice
var console
var times = {}

if (typeof global !== "undefined" && global.console) {
    console = global.console
} else if (typeof window !== "undefined" && window.console) {
    console = window.console
} else {
    console = {}
}

var functions = [
    [log, "log"],
    [info, "info"],
    [warn, "warn"],
    [error, "error"],
    [time, "time"],
    [timeEnd, "timeEnd"],
    [trace, "trace"],
    [dir, "dir"],
    [consoleAssert, "assert"]
]

for (var i = 0; i < functions.length; i++) {
    var tuple = functions[i]
    var f = tuple[0]
    var name = tuple[1]

    if (!console[name]) {
        console[name] = f
    }
}

module.exports = console

function log() {}

function info() {
    console.log.apply(console, arguments)
}

function warn() {
    console.log.apply(console, arguments)
}

function error() {
    console.warn.apply(console, arguments)
}

function time(label) {
    times[label] = now()
}

function timeEnd(label) {
    var time = times[label]
    if (!time) {
        throw new Error("No such label: " + label)
    }

    delete times[label]
    var duration = now() - time
    console.log(label + ": " + duration + "ms")
}

function trace() {
    var err = new Error()
    err.name = "Trace"
    err.message = util.format.apply(null, arguments)
    console.error(err.stack)
}

function dir(object) {
    console.log(util.inspect(object) + "\n")
}

function consoleAssert(expression) {
    if (!expression) {
        var arr = slice.call(arguments, 1)
        assert.ok(false, util.format.apply(null, arr))
    }
}

/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(6)))

/***/ }),

/***/ 6:
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


/***/ }),

/***/ 7:
/***/ (function(module, exports, __webpack_require__) {

/* WEBPACK VAR INJECTION */(function(process, console) {// Copyright Joyent, Inc. and other Node contributors.
//
// Permission is hereby granted, free of charge, to any person obtaining a
// copy of this software and associated documentation files (the
// "Software"), to deal in the Software without restriction, including
// without limitation the rights to use, copy, modify, merge, publish,
// distribute, sublicense, and/or sell copies of the Software, and to permit
// persons to whom the Software is furnished to do so, subject to the
// following conditions:
//
// The above copyright notice and this permission notice shall be included
// in all copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
// OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
// MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN
// NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM,
// DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
// OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE
// USE OR OTHER DEALINGS IN THE SOFTWARE.

var getOwnPropertyDescriptors = Object.getOwnPropertyDescriptors ||
  function getOwnPropertyDescriptors(obj) {
    var keys = Object.keys(obj);
    var descriptors = {};
    for (var i = 0; i < keys.length; i++) {
      descriptors[keys[i]] = Object.getOwnPropertyDescriptor(obj, keys[i]);
    }
    return descriptors;
  };

var formatRegExp = /%[sdj%]/g;
exports.format = function(f) {
  if (!isString(f)) {
    var objects = [];
    for (var i = 0; i < arguments.length; i++) {
      objects.push(inspect(arguments[i]));
    }
    return objects.join(' ');
  }

  var i = 1;
  var args = arguments;
  var len = args.length;
  var str = String(f).replace(formatRegExp, function(x) {
    if (x === '%%') return '%';
    if (i >= len) return x;
    switch (x) {
      case '%s': return String(args[i++]);
      case '%d': return Number(args[i++]);
      case '%j':
        try {
          return JSON.stringify(args[i++]);
        } catch (_) {
          return '[Circular]';
        }
      default:
        return x;
    }
  });
  for (var x = args[i]; i < len; x = args[++i]) {
    if (isNull(x) || !isObject(x)) {
      str += ' ' + x;
    } else {
      str += ' ' + inspect(x);
    }
  }
  return str;
};


// Mark that a method should not be used.
// Returns a modified function which warns once by default.
// If --no-deprecation is set, then it is a no-op.
exports.deprecate = function(fn, msg) {
  if (typeof process !== 'undefined' && process.noDeprecation === true) {
    return fn;
  }

  // Allow for deprecating things in the process of starting up.
  if (typeof process === 'undefined') {
    return function() {
      return exports.deprecate(fn, msg).apply(this, arguments);
    };
  }

  var warned = false;
  function deprecated() {
    if (!warned) {
      if (process.throwDeprecation) {
        throw new Error(msg);
      } else if (process.traceDeprecation) {
        console.trace(msg);
      } else {
        console.error(msg);
      }
      warned = true;
    }
    return fn.apply(this, arguments);
  }

  return deprecated;
};


var debugs = {};
var debugEnviron;
exports.debuglog = function(set) {
  if (isUndefined(debugEnviron))
    debugEnviron = process.env.NODE_DEBUG || '';
  set = set.toUpperCase();
  if (!debugs[set]) {
    if (new RegExp('\\b' + set + '\\b', 'i').test(debugEnviron)) {
      var pid = process.pid;
      debugs[set] = function() {
        var msg = exports.format.apply(exports, arguments);
        console.error('%s %d: %s', set, pid, msg);
      };
    } else {
      debugs[set] = function() {};
    }
  }
  return debugs[set];
};


/**
 * Echos the value of a value. Trys to print the value out
 * in the best way possible given the different types.
 *
 * @param {Object} obj The object to print out.
 * @param {Object} opts Optional options object that alters the output.
 */
/* legacy: obj, showHidden, depth, colors*/
function inspect(obj, opts) {
  // default options
  var ctx = {
    seen: [],
    stylize: stylizeNoColor
  };
  // legacy...
  if (arguments.length >= 3) ctx.depth = arguments[2];
  if (arguments.length >= 4) ctx.colors = arguments[3];
  if (isBoolean(opts)) {
    // legacy...
    ctx.showHidden = opts;
  } else if (opts) {
    // got an "options" object
    exports._extend(ctx, opts);
  }
  // set default options
  if (isUndefined(ctx.showHidden)) ctx.showHidden = false;
  if (isUndefined(ctx.depth)) ctx.depth = 2;
  if (isUndefined(ctx.colors)) ctx.colors = false;
  if (isUndefined(ctx.customInspect)) ctx.customInspect = true;
  if (ctx.colors) ctx.stylize = stylizeWithColor;
  return formatValue(ctx, obj, ctx.depth);
}
exports.inspect = inspect;


// http://en.wikipedia.org/wiki/ANSI_escape_code#graphics
inspect.colors = {
  'bold' : [1, 22],
  'italic' : [3, 23],
  'underline' : [4, 24],
  'inverse' : [7, 27],
  'white' : [37, 39],
  'grey' : [90, 39],
  'black' : [30, 39],
  'blue' : [34, 39],
  'cyan' : [36, 39],
  'green' : [32, 39],
  'magenta' : [35, 39],
  'red' : [31, 39],
  'yellow' : [33, 39]
};

// Don't use 'blue' not visible on cmd.exe
inspect.styles = {
  'special': 'cyan',
  'number': 'yellow',
  'boolean': 'yellow',
  'undefined': 'grey',
  'null': 'bold',
  'string': 'green',
  'date': 'magenta',
  // "name": intentionally not styling
  'regexp': 'red'
};


function stylizeWithColor(str, styleType) {
  var style = inspect.styles[styleType];

  if (style) {
    return '\u001b[' + inspect.colors[style][0] + 'm' + str +
           '\u001b[' + inspect.colors[style][1] + 'm';
  } else {
    return str;
  }
}


function stylizeNoColor(str, styleType) {
  return str;
}


function arrayToHash(array) {
  var hash = {};

  array.forEach(function(val, idx) {
    hash[val] = true;
  });

  return hash;
}


function formatValue(ctx, value, recurseTimes) {
  // Provide a hook for user-specified inspect functions.
  // Check that value is an object with an inspect function on it
  if (ctx.customInspect &&
      value &&
      isFunction(value.inspect) &&
      // Filter out the util module, it's inspect function is special
      value.inspect !== exports.inspect &&
      // Also filter out any prototype objects using the circular check.
      !(value.constructor && value.constructor.prototype === value)) {
    var ret = value.inspect(recurseTimes, ctx);
    if (!isString(ret)) {
      ret = formatValue(ctx, ret, recurseTimes);
    }
    return ret;
  }

  // Primitive types cannot have properties
  var primitive = formatPrimitive(ctx, value);
  if (primitive) {
    return primitive;
  }

  // Look up the keys of the object.
  var keys = Object.keys(value);
  var visibleKeys = arrayToHash(keys);

  if (ctx.showHidden) {
    keys = Object.getOwnPropertyNames(value);
  }

  // IE doesn't make error fields non-enumerable
  // http://msdn.microsoft.com/en-us/library/ie/dww52sbt(v=vs.94).aspx
  if (isError(value)
      && (keys.indexOf('message') >= 0 || keys.indexOf('description') >= 0)) {
    return formatError(value);
  }

  // Some type of object without properties can be shortcutted.
  if (keys.length === 0) {
    if (isFunction(value)) {
      var name = value.name ? ': ' + value.name : '';
      return ctx.stylize('[Function' + name + ']', 'special');
    }
    if (isRegExp(value)) {
      return ctx.stylize(RegExp.prototype.toString.call(value), 'regexp');
    }
    if (isDate(value)) {
      return ctx.stylize(Date.prototype.toString.call(value), 'date');
    }
    if (isError(value)) {
      return formatError(value);
    }
  }

  var base = '', array = false, braces = ['{', '}'];

  // Make Array say that they are Array
  if (isArray(value)) {
    array = true;
    braces = ['[', ']'];
  }

  // Make functions say that they are functions
  if (isFunction(value)) {
    var n = value.name ? ': ' + value.name : '';
    base = ' [Function' + n + ']';
  }

  // Make RegExps say that they are RegExps
  if (isRegExp(value)) {
    base = ' ' + RegExp.prototype.toString.call(value);
  }

  // Make dates with properties first say the date
  if (isDate(value)) {
    base = ' ' + Date.prototype.toUTCString.call(value);
  }

  // Make error with message first say the error
  if (isError(value)) {
    base = ' ' + formatError(value);
  }

  if (keys.length === 0 && (!array || value.length == 0)) {
    return braces[0] + base + braces[1];
  }

  if (recurseTimes < 0) {
    if (isRegExp(value)) {
      return ctx.stylize(RegExp.prototype.toString.call(value), 'regexp');
    } else {
      return ctx.stylize('[Object]', 'special');
    }
  }

  ctx.seen.push(value);

  var output;
  if (array) {
    output = formatArray(ctx, value, recurseTimes, visibleKeys, keys);
  } else {
    output = keys.map(function(key) {
      return formatProperty(ctx, value, recurseTimes, visibleKeys, key, array);
    });
  }

  ctx.seen.pop();

  return reduceToSingleString(output, base, braces);
}


function formatPrimitive(ctx, value) {
  if (isUndefined(value))
    return ctx.stylize('undefined', 'undefined');
  if (isString(value)) {
    var simple = '\'' + JSON.stringify(value).replace(/^"|"$/g, '')
                                             .replace(/'/g, "\\'")
                                             .replace(/\\"/g, '"') + '\'';
    return ctx.stylize(simple, 'string');
  }
  if (isNumber(value))
    return ctx.stylize('' + value, 'number');
  if (isBoolean(value))
    return ctx.stylize('' + value, 'boolean');
  // For some reason typeof null is "object", so special case here.
  if (isNull(value))
    return ctx.stylize('null', 'null');
}


function formatError(value) {
  return '[' + Error.prototype.toString.call(value) + ']';
}


function formatArray(ctx, value, recurseTimes, visibleKeys, keys) {
  var output = [];
  for (var i = 0, l = value.length; i < l; ++i) {
    if (hasOwnProperty(value, String(i))) {
      output.push(formatProperty(ctx, value, recurseTimes, visibleKeys,
          String(i), true));
    } else {
      output.push('');
    }
  }
  keys.forEach(function(key) {
    if (!key.match(/^\d+$/)) {
      output.push(formatProperty(ctx, value, recurseTimes, visibleKeys,
          key, true));
    }
  });
  return output;
}


function formatProperty(ctx, value, recurseTimes, visibleKeys, key, array) {
  var name, str, desc;
  desc = Object.getOwnPropertyDescriptor(value, key) || { value: value[key] };
  if (desc.get) {
    if (desc.set) {
      str = ctx.stylize('[Getter/Setter]', 'special');
    } else {
      str = ctx.stylize('[Getter]', 'special');
    }
  } else {
    if (desc.set) {
      str = ctx.stylize('[Setter]', 'special');
    }
  }
  if (!hasOwnProperty(visibleKeys, key)) {
    name = '[' + key + ']';
  }
  if (!str) {
    if (ctx.seen.indexOf(desc.value) < 0) {
      if (isNull(recurseTimes)) {
        str = formatValue(ctx, desc.value, null);
      } else {
        str = formatValue(ctx, desc.value, recurseTimes - 1);
      }
      if (str.indexOf('\n') > -1) {
        if (array) {
          str = str.split('\n').map(function(line) {
            return '  ' + line;
          }).join('\n').substr(2);
        } else {
          str = '\n' + str.split('\n').map(function(line) {
            return '   ' + line;
          }).join('\n');
        }
      }
    } else {
      str = ctx.stylize('[Circular]', 'special');
    }
  }
  if (isUndefined(name)) {
    if (array && key.match(/^\d+$/)) {
      return str;
    }
    name = JSON.stringify('' + key);
    if (name.match(/^"([a-zA-Z_][a-zA-Z_0-9]*)"$/)) {
      name = name.substr(1, name.length - 2);
      name = ctx.stylize(name, 'name');
    } else {
      name = name.replace(/'/g, "\\'")
                 .replace(/\\"/g, '"')
                 .replace(/(^"|"$)/g, "'");
      name = ctx.stylize(name, 'string');
    }
  }

  return name + ': ' + str;
}


function reduceToSingleString(output, base, braces) {
  var numLinesEst = 0;
  var length = output.reduce(function(prev, cur) {
    numLinesEst++;
    if (cur.indexOf('\n') >= 0) numLinesEst++;
    return prev + cur.replace(/\u001b\[\d\d?m/g, '').length + 1;
  }, 0);

  if (length > 60) {
    return braces[0] +
           (base === '' ? '' : base + '\n ') +
           ' ' +
           output.join(',\n  ') +
           ' ' +
           braces[1];
  }

  return braces[0] + base + ' ' + output.join(', ') + ' ' + braces[1];
}


// NOTE: These type checking functions intentionally don't use `instanceof`
// because it is fragile and can be easily faked with `Object.create()`.
function isArray(ar) {
  return Array.isArray(ar);
}
exports.isArray = isArray;

function isBoolean(arg) {
  return typeof arg === 'boolean';
}
exports.isBoolean = isBoolean;

function isNull(arg) {
  return arg === null;
}
exports.isNull = isNull;

function isNullOrUndefined(arg) {
  return arg == null;
}
exports.isNullOrUndefined = isNullOrUndefined;

function isNumber(arg) {
  return typeof arg === 'number';
}
exports.isNumber = isNumber;

function isString(arg) {
  return typeof arg === 'string';
}
exports.isString = isString;

function isSymbol(arg) {
  return typeof arg === 'symbol';
}
exports.isSymbol = isSymbol;

function isUndefined(arg) {
  return arg === void 0;
}
exports.isUndefined = isUndefined;

function isRegExp(re) {
  return isObject(re) && objectToString(re) === '[object RegExp]';
}
exports.isRegExp = isRegExp;

function isObject(arg) {
  return typeof arg === 'object' && arg !== null;
}
exports.isObject = isObject;

function isDate(d) {
  return isObject(d) && objectToString(d) === '[object Date]';
}
exports.isDate = isDate;

function isError(e) {
  return isObject(e) &&
      (objectToString(e) === '[object Error]' || e instanceof Error);
}
exports.isError = isError;

function isFunction(arg) {
  return typeof arg === 'function';
}
exports.isFunction = isFunction;

function isPrimitive(arg) {
  return arg === null ||
         typeof arg === 'boolean' ||
         typeof arg === 'number' ||
         typeof arg === 'string' ||
         typeof arg === 'symbol' ||  // ES6 symbol
         typeof arg === 'undefined';
}
exports.isPrimitive = isPrimitive;

exports.isBuffer = __webpack_require__(9);

function objectToString(o) {
  return Object.prototype.toString.call(o);
}


function pad(n) {
  return n < 10 ? '0' + n.toString(10) : n.toString(10);
}


var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep',
              'Oct', 'Nov', 'Dec'];

// 26 Feb 16:19:34
function timestamp() {
  var d = new Date();
  var time = [pad(d.getHours()),
              pad(d.getMinutes()),
              pad(d.getSeconds())].join(':');
  return [d.getDate(), months[d.getMonth()], time].join(' ');
}


// log is just a thin wrapper to console.log that prepends a timestamp
exports.log = function() {
  console.log('%s - %s', timestamp(), exports.format.apply(exports, arguments));
};


/**
 * Inherit the prototype methods from one constructor into another.
 *
 * The Function.prototype.inherits from lang.js rewritten as a standalone
 * function (not on Function.prototype). NOTE: If this file is to be loaded
 * during bootstrapping this function needs to be rewritten using some native
 * functions as prototype setup using normal JavaScript does not work as
 * expected during bootstrapping (see mirror.js in r114903).
 *
 * @param {function} ctor Constructor function which needs to inherit the
 *     prototype.
 * @param {function} superCtor Constructor function to inherit prototype from.
 */
exports.inherits = __webpack_require__(10);

exports._extend = function(origin, add) {
  // Don't do anything if add isn't an object
  if (!add || !isObject(add)) return origin;

  var keys = Object.keys(add);
  var i = keys.length;
  while (i--) {
    origin[keys[i]] = add[keys[i]];
  }
  return origin;
};

function hasOwnProperty(obj, prop) {
  return Object.prototype.hasOwnProperty.call(obj, prop);
}

var kCustomPromisifiedSymbol = typeof Symbol !== 'undefined' ? Symbol('util.promisify.custom') : undefined;

exports.promisify = function promisify(original) {
  if (typeof original !== 'function')
    throw new TypeError('The "original" argument must be of type Function');

  if (kCustomPromisifiedSymbol && original[kCustomPromisifiedSymbol]) {
    var fn = original[kCustomPromisifiedSymbol];
    if (typeof fn !== 'function') {
      throw new TypeError('The "util.promisify.custom" argument must be of type Function');
    }
    Object.defineProperty(fn, kCustomPromisifiedSymbol, {
      value: fn, enumerable: false, writable: false, configurable: true
    });
    return fn;
  }

  function fn() {
    var promiseResolve, promiseReject;
    var promise = new Promise(function (resolve, reject) {
      promiseResolve = resolve;
      promiseReject = reject;
    });

    var args = [];
    for (var i = 0; i < arguments.length; i++) {
      args.push(arguments[i]);
    }
    args.push(function (err, value) {
      if (err) {
        promiseReject(err);
      } else {
        promiseResolve(value);
      }
    });

    try {
      original.apply(this, args);
    } catch (err) {
      promiseReject(err);
    }

    return promise;
  }

  Object.setPrototypeOf(fn, Object.getPrototypeOf(original));

  if (kCustomPromisifiedSymbol) Object.defineProperty(fn, kCustomPromisifiedSymbol, {
    value: fn, enumerable: false, writable: false, configurable: true
  });
  return Object.defineProperties(
    fn,
    getOwnPropertyDescriptors(original)
  );
}

exports.promisify.custom = kCustomPromisifiedSymbol

function callbackifyOnRejected(reason, cb) {
  // `!reason` guard inspired by bluebird (Ref: https://goo.gl/t5IS6M).
  // Because `null` is a special error value in callbacks which means "no error
  // occurred", we error-wrap so the callback consumer can distinguish between
  // "the promise rejected with null" or "the promise fulfilled with undefined".
  if (!reason) {
    var newReason = new Error('Promise was rejected with a falsy value');
    newReason.reason = reason;
    reason = newReason;
  }
  return cb(reason);
}

function callbackify(original) {
  if (typeof original !== 'function') {
    throw new TypeError('The "original" argument must be of type Function');
  }

  // We DO NOT return the promise as it gives the user a false sense that
  // the promise is actually somehow related to the callback's execution
  // and that the callback throwing will reject the promise.
  function callbackified() {
    var args = [];
    for (var i = 0; i < arguments.length; i++) {
      args.push(arguments[i]);
    }

    var maybeCb = args.pop();
    if (typeof maybeCb !== 'function') {
      throw new TypeError('The last argument must be of type Function');
    }
    var self = this;
    var cb = function() {
      return maybeCb.apply(self, arguments);
    };
    // In true node style we process the callback on `nextTick` with all the
    // implications (stack, `uncaughtException`, `async_hooks`)
    original.apply(this, args)
      .then(function(ret) { process.nextTick(cb, null, ret) },
            function(rej) { process.nextTick(callbackifyOnRejected, rej, cb) });
  }

  Object.setPrototypeOf(callbackified, Object.getPrototypeOf(original));
  Object.defineProperties(callbackified,
                          getOwnPropertyDescriptors(original));
  return callbackified;
}
exports.callbackify = callbackify;

/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(8), __webpack_require__(5)))

/***/ }),

/***/ 8:
/***/ (function(module, exports) {

// shim for using process in browser
var process = module.exports = {};

// cached from whatever global is present so that test runners that stub it
// don't break things.  But we need to wrap it in a try catch in case it is
// wrapped in strict mode code which doesn't define any globals.  It's inside a
// function because try/catches deoptimize in certain engines.

var cachedSetTimeout;
var cachedClearTimeout;

function defaultSetTimout() {
    throw new Error('setTimeout has not been defined');
}
function defaultClearTimeout () {
    throw new Error('clearTimeout has not been defined');
}
(function () {
    try {
        if (typeof setTimeout === 'function') {
            cachedSetTimeout = setTimeout;
        } else {
            cachedSetTimeout = defaultSetTimout;
        }
    } catch (e) {
        cachedSetTimeout = defaultSetTimout;
    }
    try {
        if (typeof clearTimeout === 'function') {
            cachedClearTimeout = clearTimeout;
        } else {
            cachedClearTimeout = defaultClearTimeout;
        }
    } catch (e) {
        cachedClearTimeout = defaultClearTimeout;
    }
} ())
function runTimeout(fun) {
    if (cachedSetTimeout === setTimeout) {
        //normal enviroments in sane situations
        return setTimeout(fun, 0);
    }
    // if setTimeout wasn't available but was latter defined
    if ((cachedSetTimeout === defaultSetTimout || !cachedSetTimeout) && setTimeout) {
        cachedSetTimeout = setTimeout;
        return setTimeout(fun, 0);
    }
    try {
        // when when somebody has screwed with setTimeout but no I.E. maddness
        return cachedSetTimeout(fun, 0);
    } catch(e){
        try {
            // When we are in I.E. but the script has been evaled so I.E. doesn't trust the global object when called normally
            return cachedSetTimeout.call(null, fun, 0);
        } catch(e){
            // same as above but when it's a version of I.E. that must have the global object for 'this', hopfully our context correct otherwise it will throw a global error
            return cachedSetTimeout.call(this, fun, 0);
        }
    }


}
function runClearTimeout(marker) {
    if (cachedClearTimeout === clearTimeout) {
        //normal enviroments in sane situations
        return clearTimeout(marker);
    }
    // if clearTimeout wasn't available but was latter defined
    if ((cachedClearTimeout === defaultClearTimeout || !cachedClearTimeout) && clearTimeout) {
        cachedClearTimeout = clearTimeout;
        return clearTimeout(marker);
    }
    try {
        // when when somebody has screwed with setTimeout but no I.E. maddness
        return cachedClearTimeout(marker);
    } catch (e){
        try {
            // When we are in I.E. but the script has been evaled so I.E. doesn't  trust the global object when called normally
            return cachedClearTimeout.call(null, marker);
        } catch (e){
            // same as above but when it's a version of I.E. that must have the global object for 'this', hopfully our context correct otherwise it will throw a global error.
            // Some versions of I.E. have different rules for clearTimeout vs setTimeout
            return cachedClearTimeout.call(this, marker);
        }
    }



}
var queue = [];
var draining = false;
var currentQueue;
var queueIndex = -1;

function cleanUpNextTick() {
    if (!draining || !currentQueue) {
        return;
    }
    draining = false;
    if (currentQueue.length) {
        queue = currentQueue.concat(queue);
    } else {
        queueIndex = -1;
    }
    if (queue.length) {
        drainQueue();
    }
}

function drainQueue() {
    if (draining) {
        return;
    }
    var timeout = runTimeout(cleanUpNextTick);
    draining = true;

    var len = queue.length;
    while(len) {
        currentQueue = queue;
        queue = [];
        while (++queueIndex < len) {
            if (currentQueue) {
                currentQueue[queueIndex].run();
            }
        }
        queueIndex = -1;
        len = queue.length;
    }
    currentQueue = null;
    draining = false;
    runClearTimeout(timeout);
}

process.nextTick = function (fun) {
    var args = new Array(arguments.length - 1);
    if (arguments.length > 1) {
        for (var i = 1; i < arguments.length; i++) {
            args[i - 1] = arguments[i];
        }
    }
    queue.push(new Item(fun, args));
    if (queue.length === 1 && !draining) {
        runTimeout(drainQueue);
    }
};

// v8 likes predictible objects
function Item(fun, array) {
    this.fun = fun;
    this.array = array;
}
Item.prototype.run = function () {
    this.fun.apply(null, this.array);
};
process.title = 'browser';
process.browser = true;
process.env = {};
process.argv = [];
process.version = ''; // empty string to avoid regexp issues
process.versions = {};

function noop() {}

process.on = noop;
process.addListener = noop;
process.once = noop;
process.off = noop;
process.removeListener = noop;
process.removeAllListeners = noop;
process.emit = noop;
process.prependListener = noop;
process.prependOnceListener = noop;

process.listeners = function (name) { return [] }

process.binding = function (name) {
    throw new Error('process.binding is not supported');
};

process.cwd = function () { return '/' };
process.chdir = function (dir) {
    throw new Error('process.chdir is not supported');
};
process.umask = function() { return 0; };


/***/ }),

/***/ 9:
/***/ (function(module, exports) {

module.exports = function isBuffer(arg) {
  return arg && typeof arg === 'object'
    && typeof arg.copy === 'function'
    && typeof arg.fill === 'function'
    && typeof arg.readUInt8 === 'function';
}

/***/ })

/******/ });
//# sourceMappingURL=gridstack.js.map