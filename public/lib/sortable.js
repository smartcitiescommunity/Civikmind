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
/******/ 	return __webpack_require__(__webpack_require__.s = 553);
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

/***/ 553:
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

window.sortable = __webpack_require__(554);

/***/ }),

/***/ 554:
/***/ (function(module, exports, __webpack_require__) {

/* WEBPACK VAR INJECTION */(function(console) {var __WEBPACK_AMD_DEFINE_RESULT__;/*
 * HTML5Sortable package
 * https://github.com/lukasoppermann/html5sortable
 *
 * Maintained by Lukas Oppermann <lukas@vea.re>
 *
 * Released under the MIT license.
 */
!(__WEBPACK_AMD_DEFINE_RESULT__ = (function () { 'use strict';

  /**
   * Get or set data on element
   * @param {HTMLElement} element
   * @param {string} key
   * @param {any} value
   * @return {*}
   */
  function addData(element, key, value) {
      if (value === undefined) {
          return element && element.h5s && element.h5s.data && element.h5s.data[key];
      }
      else {
          element.h5s = element.h5s || {};
          element.h5s.data = element.h5s.data || {};
          element.h5s.data[key] = value;
      }
  }
  /**
   * Remove data from element
   * @param {HTMLElement} element
   */
  function removeData(element) {
      if (element.h5s) {
          delete element.h5s.data;
      }
  }

  /* eslint-env browser */
  /**
   * Filter only wanted nodes
   * @param {NodeList|HTMLCollection|Array} nodes
   * @param {String} selector
   * @returns {Array}
   */
  var _filter = (function (nodes, selector) {
      if (!(nodes instanceof NodeList || nodes instanceof HTMLCollection || nodes instanceof Array)) {
          throw new Error('You must provide a nodeList/HTMLCollection/Array of elements to be filtered.');
      }
      if (typeof selector !== 'string') {
          return Array.from(nodes);
      }
      return Array.from(nodes).filter(function (item) { return item.nodeType === 1 && item.matches(selector); });
  });

  /* eslint-env browser */
  var stores = new Map();
  /**
   * Stores data & configurations per Sortable
   * @param {Object} config
   */
  var Store = /** @class */ (function () {
      function Store() {
          this._config = new Map(); // eslint-disable-line no-undef
          this._placeholder = undefined; // eslint-disable-line no-undef
          this._data = new Map(); // eslint-disable-line no-undef
      }
      Object.defineProperty(Store.prototype, "config", {
          /**
           * get the configuration map of a class instance
           * @method config
           * @return {object}
           */
          get: function () {
              // transform Map to object
              var config = {};
              this._config.forEach(function (value, key) {
                  config[key] = value;
              });
              // return object
              return config;
          },
          /**
           * set the configuration of a class instance
           * @method config
           * @param {object} config object of configurations
           */
          set: function (config) {
              if (typeof config !== 'object') {
                  throw new Error('You must provide a valid configuration object to the config setter.');
              }
              // combine config with default
              var mergedConfig = Object.assign({}, config);
              // add config to map
              this._config = new Map(Object.entries(mergedConfig));
          },
          enumerable: false,
          configurable: true
      });
      /**
       * set individual configuration of a class instance
       * @method setConfig
       * @param  key valid configuration key
       * @param  value any value
       * @return void
       */
      Store.prototype.setConfig = function (key, value) {
          if (!this._config.has(key)) {
              throw new Error("Trying to set invalid configuration item: " + key);
          }
          // set config
          this._config.set(key, value);
      };
      /**
       * get an individual configuration of a class instance
       * @method getConfig
       * @param  key valid configuration key
       * @return any configuration value
       */
      Store.prototype.getConfig = function (key) {
          if (!this._config.has(key)) {
              throw new Error("Invalid configuration item requested: " + key);
          }
          return this._config.get(key);
      };
      Object.defineProperty(Store.prototype, "placeholder", {
          /**
           * get the placeholder for a class instance
           * @method placeholder
           * @return {HTMLElement|null}
           */
          get: function () {
              return this._placeholder;
          },
          /**
           * set the placeholder for a class instance
           * @method placeholder
           * @param {HTMLElement} placeholder
           * @return {void}
           */
          set: function (placeholder) {
              if (!(placeholder instanceof HTMLElement) && placeholder !== null) {
                  throw new Error('A placeholder must be an html element or null.');
              }
              this._placeholder = placeholder;
          },
          enumerable: false,
          configurable: true
      });
      /**
       * set an data entry
       * @method setData
       * @param {string} key
       * @param {any} value
       * @return {void}
       */
      Store.prototype.setData = function (key, value) {
          if (typeof key !== 'string') {
              throw new Error('The key must be a string.');
          }
          this._data.set(key, value);
      };
      /**
       * get an data entry
       * @method getData
       * @param {string} key an existing key
       * @return {any}
       */
      Store.prototype.getData = function (key) {
          if (typeof key !== 'string') {
              throw new Error('The key must be a string.');
          }
          return this._data.get(key);
      };
      /**
       * delete an data entry
       * @method deleteData
       * @param {string} key an existing key
       * @return {boolean}
       */
      Store.prototype.deleteData = function (key) {
          if (typeof key !== 'string') {
              throw new Error('The key must be a string.');
          }
          return this._data.delete(key);
      };
      return Store;
  }());
  /**
   * @param {HTMLElement} sortableElement
   * @returns {Class: Store}
   */
  var store = (function (sortableElement) {
      // if sortableElement is wrong type
      if (!(sortableElement instanceof HTMLElement)) {
          throw new Error('Please provide a sortable to the store function.');
      }
      // create new instance if not avilable
      if (!stores.has(sortableElement)) {
          stores.set(sortableElement, new Store());
      }
      // return instance
      return stores.get(sortableElement);
  });

  /**
   * @param {Array|HTMLElement} element
   * @param {Function} callback
   * @param {string} event
   */
  function addEventListener(element, eventName, callback) {
      if (element instanceof Array) {
          for (var i = 0; i < element.length; ++i) {
              addEventListener(element[i], eventName, callback);
          }
          return;
      }
      element.addEventListener(eventName, callback);
      store(element).setData("event" + eventName, callback);
  }
  /**
   * @param {Array<HTMLElement>|HTMLElement} element
   * @param {string} eventName
   */
  function removeEventListener(element, eventName) {
      if (element instanceof Array) {
          for (var i = 0; i < element.length; ++i) {
              removeEventListener(element[i], eventName);
          }
          return;
      }
      element.removeEventListener(eventName, store(element).getData("event" + eventName));
      store(element).deleteData("event" + eventName);
  }

  /**
   * @param {Array<HTMLElement>|HTMLElement} element
   * @param {string} attribute
   * @param {string} value
   */
  function addAttribute(element, attribute, value) {
      if (element instanceof Array) {
          for (var i = 0; i < element.length; ++i) {
              addAttribute(element[i], attribute, value);
          }
          return;
      }
      element.setAttribute(attribute, value);
  }
  /**
   * @param {Array|HTMLElement} element
   * @param {string} attribute
   */
  function removeAttribute(element, attribute) {
      if (element instanceof Array) {
          for (var i = 0; i < element.length; ++i) {
              removeAttribute(element[i], attribute);
          }
          return;
      }
      element.removeAttribute(attribute);
  }

  /**
   * @param {HTMLElement} element
   * @returns {Object}
   */
  var _offset = (function (element) {
      if (!element.parentElement || element.getClientRects().length === 0) {
          throw new Error('target element must be part of the dom');
      }
      var rect = element.getClientRects()[0];
      return {
          left: rect.left + window.pageXOffset,
          right: rect.right + window.pageXOffset,
          top: rect.top + window.pageYOffset,
          bottom: rect.bottom + window.pageYOffset
      };
  });

  /**
   * Creates and returns a new debounced version of the passed function which will postpone its execution until after wait milliseconds have elapsed
   * @param {Function} func to debounce
   * @param {number} time to wait before calling function with latest arguments, 0 - no debounce
   * @returns {function} - debounced function
   */
  var _debounce = (function (func, wait) {
      if (wait === void 0) { wait = 0; }
      var timeout;
      return function () {
          var args = [];
          for (var _i = 0; _i < arguments.length; _i++) {
              args[_i] = arguments[_i];
          }
          clearTimeout(timeout);
          timeout = setTimeout(function () {
              func.apply(void 0, args);
          }, wait);
      };
  });

  /* eslint-env browser */
  /**
   * Get position of the element relatively to its sibling elements
   * @param {HTMLElement} element
   * @returns {number}
   */
  var _index = (function (element, elementList) {
      if (!(element instanceof HTMLElement) || !(elementList instanceof NodeList || elementList instanceof HTMLCollection || elementList instanceof Array)) {
          throw new Error('You must provide an element and a list of elements.');
      }
      return Array.from(elementList).indexOf(element);
  });

  /* eslint-env browser */
  /**
   * Test whether element is in DOM
   * @param {HTMLElement} element
   * @returns {boolean}
   */
  var isInDom = (function (element) {
      if (!(element instanceof HTMLElement)) {
          throw new Error('Element is not a node element.');
      }
      return element.parentNode !== null;
  });

  /* eslint-env browser */
  /**
   * Insert node before or after target
   * @param {HTMLElement} referenceNode - reference element
   * @param {HTMLElement} newElement - element to be inserted
   * @param {String} position - insert before or after reference element
   */
  var insertNode = function (referenceNode, newElement, position) {
      if (!(referenceNode instanceof HTMLElement) || !(referenceNode.parentElement instanceof HTMLElement)) {
          throw new Error('target and element must be a node');
      }
      referenceNode.parentElement.insertBefore(newElement, (position === 'before' ? referenceNode : referenceNode.nextElementSibling));
  };
  /**
   * Insert before target
   * @param {HTMLElement} target
   * @param {HTMLElement} element
   */
  var insertBefore = function (target, element) { return insertNode(target, element, 'before'); };
  /**
   * Insert after target
   * @param {HTMLElement} target
   * @param {HTMLElement} element
   */
  var insertAfter = function (target, element) { return insertNode(target, element, 'after'); };

  /* eslint-env browser */
  /**
   * Filter only wanted nodes
   * @param {HTMLElement} sortableContainer
   * @param {Function} customSerializer
   * @returns {Array}
   */
  var _serialize = (function (sortableContainer, customItemSerializer, customContainerSerializer) {
      if (customItemSerializer === void 0) { customItemSerializer = function (serializedItem, sortableContainer) { return serializedItem; }; }
      if (customContainerSerializer === void 0) { customContainerSerializer = function (serializedContainer) { return serializedContainer; }; }
      // check for valid sortableContainer
      if (!(sortableContainer instanceof HTMLElement) || !sortableContainer.isSortable === true) {
          throw new Error('You need to provide a sortableContainer to be serialized.');
      }
      // check for valid serializers
      if (typeof customItemSerializer !== 'function' || typeof customContainerSerializer !== 'function') {
          throw new Error('You need to provide a valid serializer for items and the container.');
      }
      // get options
      var options = addData(sortableContainer, 'opts');
      var item = options.items;
      // serialize container
      var items = _filter(sortableContainer.children, item);
      var serializedItems = items.map(function (item) {
          return {
              parent: sortableContainer,
              node: item,
              html: item.outerHTML,
              index: _index(item, items)
          };
      });
      // serialize container
      var container = {
          node: sortableContainer,
          itemCount: serializedItems.length
      };
      return {
          container: customContainerSerializer(container),
          items: serializedItems.map(function (item) { return customItemSerializer(item, sortableContainer); })
      };
  });

  /* eslint-env browser */
  /**
   * create a placeholder element
   * @param {HTMLElement} sortableElement a single sortable
   * @param {string|undefined} placeholder a string representing an html element
   * @param {string} placeholderClasses a string representing the classes that should be added to the placeholder
   */
  var _makePlaceholder = (function (sortableElement, placeholder, placeholderClass) {
      var _a;
      if (placeholderClass === void 0) { placeholderClass = 'sortable-placeholder'; }
      if (!(sortableElement instanceof HTMLElement)) {
          throw new Error('You must provide a valid element as a sortable.');
      }
      // if placeholder is not an element
      if (!(placeholder instanceof HTMLElement) && placeholder !== undefined) {
          throw new Error('You must provide a valid element as a placeholder or set ot to undefined.');
      }
      // if no placeholder element is given
      if (placeholder === undefined) {
          if (['UL', 'OL'].includes(sortableElement.tagName)) {
              placeholder = document.createElement('li');
          }
          else if (['TABLE', 'TBODY'].includes(sortableElement.tagName)) {
              placeholder = document.createElement('tr');
              // set colspan to always all rows, otherwise the item can only be dropped in first column
              placeholder.innerHTML = '<td colspan="100"></td>';
          }
          else {
              placeholder = document.createElement('div');
          }
      }
      // add classes to placeholder
      if (typeof placeholderClass === 'string') {
          (_a = placeholder.classList).add.apply(_a, placeholderClass.split(' '));
      }
      return placeholder;
  });

  /* eslint-env browser */
  /**
   * Get height of an element including padding
   * @param {HTMLElement} element an dom element
   */
  var _getElementHeight = (function (element) {
      if (!(element instanceof HTMLElement)) {
          throw new Error('You must provide a valid dom element');
      }
      // get calculated style of element
      var style = window.getComputedStyle(element);
      // get only height if element has box-sizing: border-box specified
      if (style.getPropertyValue('box-sizing') === 'border-box') {
          return parseInt(style.getPropertyValue('height'), 10);
      }
      // pick applicable properties, convert to int and reduce by adding
      return ['height', 'padding-top', 'padding-bottom']
          .map(function (key) {
          var int = parseInt(style.getPropertyValue(key), 10);
          return isNaN(int) ? 0 : int;
      })
          .reduce(function (sum, value) { return sum + value; });
  });

  /* eslint-env browser */
  /**
   * Get width of an element including padding
   * @param {HTMLElement} element an dom element
   */
  var _getElementWidth = (function (element) {
      if (!(element instanceof HTMLElement)) {
          throw new Error('You must provide a valid dom element');
      }
      // get calculated style of element
      var style = window.getComputedStyle(element);
      // pick applicable properties, convert to int and reduce by adding
      return ['width', 'padding-left', 'padding-right']
          .map(function (key) {
          var int = parseInt(style.getPropertyValue(key), 10);
          return isNaN(int) ? 0 : int;
      })
          .reduce(function (sum, value) { return sum + value; });
  });

  /* eslint-env browser */
  /**
   * get handle or return item
   * @param {Array<HTMLElement>} items
   * @param {string} selector
   */
  var _getHandles = (function (items, selector) {
      if (!(items instanceof Array)) {
          throw new Error('You must provide a Array of HTMLElements to be filtered.');
      }
      if (typeof selector !== 'string') {
          return items;
      }
      return items
          // remove items without handle from array
          .filter(function (item) {
          return item.querySelector(selector) instanceof HTMLElement ||
              (item.shadowRoot && item.shadowRoot.querySelector(selector) instanceof HTMLElement);
      })
          // replace item with handle in array
          .map(function (item) {
          return item.querySelector(selector) || (item.shadowRoot && item.shadowRoot.querySelector(selector));
      });
  });

  /**
   * @param {Event} event
   * @returns {HTMLElement}
   */
  var getEventTarget = (function (event) {
      return (event.composedPath && event.composedPath()[0]) || event.target;
  });

  /* eslint-env browser */
  /**
   * defaultDragImage returns the current item as dragged image
   * @param {HTMLElement} draggedElement - the item that the user drags
   * @param {object} elementOffset - an object with the offsets top, left, right & bottom
   * @param {Event} event - the original drag event object
   * @return {object} with element, posX and posY properties
   */
  var defaultDragImage = function (draggedElement, elementOffset, event) {
      return {
          element: draggedElement,
          posX: event.pageX - elementOffset.left,
          posY: event.pageY - elementOffset.top
      };
  };
  /**
   * attaches an element as the drag image to an event
   * @param {Event} event - the original drag event object
   * @param {HTMLElement} draggedElement - the item that the user drags
   * @param {Function} customDragImage - function to create a custom dragImage
   * @return void
   */
  var setDragImage = (function (event, draggedElement, customDragImage) {
      // check if event is provided
      if (!(event instanceof Event)) {
          throw new Error('setDragImage requires a DragEvent as the first argument.');
      }
      // check if draggedElement is provided
      if (!(draggedElement instanceof HTMLElement)) {
          throw new Error('setDragImage requires the dragged element as the second argument.');
      }
      // set default function of none provided
      if (!customDragImage) {
          customDragImage = defaultDragImage;
      }
      // check if setDragImage method is available
      if (event.dataTransfer && event.dataTransfer.setDragImage) {
          // get the elements offset
          var elementOffset = _offset(draggedElement);
          // get the dragImage
          var dragImage = customDragImage(draggedElement, elementOffset, event);
          // check if custom function returns correct values
          if (!(dragImage.element instanceof HTMLElement) || typeof dragImage.posX !== 'number' || typeof dragImage.posY !== 'number') {
              throw new Error('The customDragImage function you provided must return and object with the properties element[string], posX[integer], posY[integer].');
          }
          // needs to be set for HTML5 drag & drop to work
          event.dataTransfer.effectAllowed = 'copyMove';
          // Firefox requires it to use the event target's id for the data
          event.dataTransfer.setData('text/plain', getEventTarget(event).id);
          // set the drag image on the event
          event.dataTransfer.setDragImage(dragImage.element, dragImage.posX, dragImage.posY);
      }
  });

  /**
   * Check if curList accepts items from destList
   * @param {sortable} destination the container an item is move to
   * @param {sortable} origin the container an item comes from
   */
  var _listsConnected = (function (destination, origin) {
      // check if valid sortable
      if (destination.isSortable === true) {
          var acceptFrom = store(destination).getConfig('acceptFrom');
          // check if acceptFrom is valid
          if (acceptFrom !== null && acceptFrom !== false && typeof acceptFrom !== 'string') {
              throw new Error('HTML5Sortable: Wrong argument, "acceptFrom" must be "null", "false", or a valid selector string.');
          }
          if (acceptFrom !== null) {
              return acceptFrom !== false && acceptFrom.split(',').filter(function (sel) {
                  return sel.length > 0 && origin.matches(sel);
              }).length > 0;
          }
          // drop in same list
          if (destination === origin) {
              return true;
          }
          // check if lists are connected with connectWith
          if (store(destination).getConfig('connectWith') !== undefined && store(destination).getConfig('connectWith') !== null) {
              return store(destination).getConfig('connectWith') === store(origin).getConfig('connectWith');
          }
      }
      return false;
  });

  /**
   * default configurations
   */
  var defaultConfiguration = {
      items: null,
      // deprecated
      connectWith: null,
      // deprecated
      disableIEFix: null,
      acceptFrom: null,
      copy: false,
      placeholder: null,
      placeholderClass: 'sortable-placeholder',
      draggingClass: 'sortable-dragging',
      hoverClass: false,
      dropTargetContainerClass: false,
      debounce: 0,
      throttleTime: 100,
      maxItems: 0,
      itemSerializer: undefined,
      containerSerializer: undefined,
      customDragImage: null,
      orientation: 'vertical'
  };

  /**
   * make sure a function is only called once within the given amount of time
   * @param {Function} fn the function to throttle
   * @param {number} threshold time limit for throttling
   */
  // must use function to keep this context
  function _throttle (fn, threshold) {
      var _this = this;
      if (threshold === void 0) { threshold = 250; }
      // check function
      if (typeof fn !== 'function') {
          throw new Error('You must provide a function as the first argument for throttle.');
      }
      // check threshold
      if (typeof threshold !== 'number') {
          throw new Error('You must provide a number as the second argument for throttle.');
      }
      var lastEventTimestamp = null;
      return function () {
          var args = [];
          for (var _i = 0; _i < arguments.length; _i++) {
              args[_i] = arguments[_i];
          }
          var now = Date.now();
          if (lastEventTimestamp === null || now - lastEventTimestamp >= threshold) {
              lastEventTimestamp = now;
              fn.apply(_this, args);
          }
      };
  }

  /* eslint-env browser */
  /**
   * enable or disable hoverClass on mouseenter/leave if container Items
   * @param {sortable} sortableContainer a valid sortableContainer
   * @param {boolean} enable enable or disable event
   */
  // export default (sortableContainer: sortable, enable: boolean) => {
  var enableHoverClass = (function (sortableContainer, enable) {
      if (typeof store(sortableContainer).getConfig('hoverClass') === 'string') {
          var hoverClasses_1 = store(sortableContainer).getConfig('hoverClass').split(' ');
          // add class on hover
          if (enable === true) {
              addEventListener(sortableContainer, 'mousemove', _throttle(function (event) {
                  // check of no mouse button was pressed when mousemove started == no drag
                  if (event.buttons === 0) {
                      _filter(sortableContainer.children, store(sortableContainer).getConfig('items')).forEach(function (item) {
                          var _a, _b;
                          if (item !== event.target) {
                              (_a = item.classList).remove.apply(_a, hoverClasses_1);
                          }
                          else {
                              (_b = item.classList).add.apply(_b, hoverClasses_1);
                          }
                      });
                  }
              }, store(sortableContainer).getConfig('throttleTime')));
              // remove class on leave
              addEventListener(sortableContainer, 'mouseleave', function () {
                  _filter(sortableContainer.children, store(sortableContainer).getConfig('items')).forEach(function (item) {
                      var _a;
                      (_a = item.classList).remove.apply(_a, hoverClasses_1);
                  });
              });
              // remove events
          }
          else {
              removeEventListener(sortableContainer, 'mousemove');
              removeEventListener(sortableContainer, 'mouseleave');
          }
      }
  });

  /* eslint-env browser */
  /*
   * variables global to the plugin
   */
  var dragging;
  var draggingHeight;
  var draggingWidth;
  /*
   * Keeps track of the initialy selected list, where 'dragstart' event was triggered
   * It allows us to move the data in between individual Sortable List instances
   */
  // Origin List - data from before any item was changed
  var originContainer;
  var originIndex;
  var originElementIndex;
  var originItemsBeforeUpdate;
  // Previous Sortable Container - we dispatch as sortenter event when a
  // dragged item enters a sortableContainer for the first time
  var previousContainer;
  // Destination List - data from before any item was changed
  var destinationItemsBeforeUpdate;
  /**
   * remove event handlers from items
   * @param {Array|NodeList} items
   */
  var _removeItemEvents = function (items) {
      removeEventListener(items, 'dragstart');
      removeEventListener(items, 'dragend');
      removeEventListener(items, 'dragover');
      removeEventListener(items, 'dragenter');
      removeEventListener(items, 'drop');
      removeEventListener(items, 'mouseenter');
      removeEventListener(items, 'mouseleave');
  };
  // Remove container events
  var _removeContainerEvents = function (originContainer, previousContainer) {
      if (originContainer) {
          removeEventListener(originContainer, 'dragleave');
      }
      if (previousContainer && (previousContainer !== originContainer)) {
          removeEventListener(previousContainer, 'dragleave');
      }
  };
  /**
   * _getDragging returns the current element to drag or
   * a copy of the element.
   * Is Copy Active for sortable
   * @param {HTMLElement} draggedItem - the item that the user drags
   * @param {HTMLElement} sortable a single sortable
   */
  var _getDragging = function (draggedItem, sortable) {
      var ditem = draggedItem;
      if (store(sortable).getConfig('copy') === true) {
          ditem = draggedItem.cloneNode(true);
          addAttribute(ditem, 'aria-copied', 'true');
          draggedItem.parentElement.appendChild(ditem);
          ditem.style.display = 'none';
          ditem.oldDisplay = draggedItem.style.display;
      }
      return ditem;
  };
  /**
   * Remove data from sortable
   * @param {HTMLElement} sortable a single sortable
   */
  var _removeSortableData = function (sortable) {
      removeData(sortable);
      removeAttribute(sortable, 'aria-dropeffect');
  };
  /**
   * Remove data from items
   * @param {Array<HTMLElement>|HTMLElement} items
   */
  var _removeItemData = function (items) {
      removeAttribute(items, 'aria-grabbed');
      removeAttribute(items, 'aria-copied');
      removeAttribute(items, 'draggable');
      removeAttribute(items, 'role');
  };
  /**
   * find sortable from element. travels up parent element until found or null.
   * @param {HTMLElement} element a single sortable
   * @param {Event} event - the current event. We need to pass it to be able to
   * find Sortable whith shadowRoot (document fragment has no parent)
   */
  function findSortable(element, event) {
      if (event.composedPath) {
          return event.composedPath().find(function (el) { return el.isSortable; });
      }
      while (element.isSortable !== true) {
          element = element.parentElement;
      }
      return element;
  }
  /**
   * Dragging event is on the sortable element. finds the top child that
   * contains the element.
   * @param {HTMLElement} sortableElement a single sortable
   * @param {HTMLElement} element is that being dragged
   */
  function findDragElement(sortableElement, element) {
      var options = addData(sortableElement, 'opts');
      var items = _filter(sortableElement.children, options.items);
      var itemlist = items.filter(function (ele) {
          return ele.contains(element) || (ele.shadowRoot && ele.shadowRoot.contains(element));
      });
      return itemlist.length > 0 ? itemlist[0] : element;
  }
  /**
   * Destroy the sortable
   * @param {HTMLElement} sortableElement a single sortable
   */
  var _destroySortable = function (sortableElement) {
      var opts = addData(sortableElement, 'opts') || {};
      var items = _filter(sortableElement.children, opts.items);
      var handles = _getHandles(items, opts.handle);
      // remove event handlers & data from sortable
      removeEventListener(sortableElement, 'dragover');
      removeEventListener(sortableElement, 'dragenter');
      removeEventListener(sortableElement, 'dragstart');
      removeEventListener(sortableElement, 'dragend');
      removeEventListener(sortableElement, 'drop');
      // remove event data from sortable
      _removeSortableData(sortableElement);
      // remove event handlers & data from items
      removeEventListener(handles, 'mousedown');
      _removeItemEvents(items);
      _removeItemData(items);
      _removeContainerEvents(originContainer, previousContainer);
      // clear sortable flag
      sortableElement.isSortable = false;
  };
  /**
   * Enable the sortable
   * @param {HTMLElement} sortableElement a single sortable
   */
  var _enableSortable = function (sortableElement) {
      var opts = addData(sortableElement, 'opts');
      var items = _filter(sortableElement.children, opts.items);
      var handles = _getHandles(items, opts.handle);
      addAttribute(sortableElement, 'aria-dropeffect', 'move');
      addData(sortableElement, '_disabled', 'false');
      addAttribute(handles, 'draggable', 'true');
      // @todo: remove this fix
      // IE FIX for ghost
      // can be disabled as it has the side effect that other events
      // (e.g. click) will be ignored
      if (opts.disableIEFix === false) {
          var spanEl = (document || window.document).createElement('span');
          if (typeof spanEl.dragDrop === 'function') {
              addEventListener(handles, 'mousedown', function () {
                  if (items.indexOf(this) !== -1) {
                      this.dragDrop();
                  }
                  else {
                      var parent = this.parentElement;
                      while (items.indexOf(parent) === -1) {
                          parent = parent.parentElement;
                      }
                      parent.dragDrop();
                  }
              });
          }
      }
  };
  /**
   * Disable the sortable
   * @param {HTMLElement} sortableElement a single sortable
   */
  var _disableSortable = function (sortableElement) {
      var opts = addData(sortableElement, 'opts');
      var items = _filter(sortableElement.children, opts.items);
      var handles = _getHandles(items, opts.handle);
      addAttribute(sortableElement, 'aria-dropeffect', 'none');
      addData(sortableElement, '_disabled', 'true');
      addAttribute(handles, 'draggable', 'false');
      removeEventListener(handles, 'mousedown');
  };
  /**
   * Reload the sortable
   * @param {HTMLElement} sortableElement a single sortable
   * @description events need to be removed to not be double bound
   */
  var _reloadSortable = function (sortableElement) {
      var opts = addData(sortableElement, 'opts');
      var items = _filter(sortableElement.children, opts.items);
      var handles = _getHandles(items, opts.handle);
      addData(sortableElement, '_disabled', 'false');
      // remove event handlers from items
      _removeItemEvents(items);
      _removeContainerEvents(originContainer, previousContainer);
      removeEventListener(handles, 'mousedown');
      // remove event handlers from sortable
      removeEventListener(sortableElement, 'dragover');
      removeEventListener(sortableElement, 'dragenter');
      removeEventListener(sortableElement, 'drop');
  };
  /**
   * Public sortable object
   * @param {Array|NodeList} sortableElements
   * @param {object|string} options|method
   */
  function sortable(sortableElements, options) {
      // get method string to see if a method is called
      var method = String(options);
      options = options || {};
      // check if the user provided a selector instead of an element
      if (typeof sortableElements === 'string') {
          sortableElements = document.querySelectorAll(sortableElements);
      }
      // if the user provided an element, return it in an array to keep the return value consistant
      if (sortableElements instanceof HTMLElement) {
          sortableElements = [sortableElements];
      }
      sortableElements = Array.prototype.slice.call(sortableElements);
      if (/serialize/.test(method)) {
          return sortableElements.map(function (sortableContainer) {
              var opts = addData(sortableContainer, 'opts');
              return _serialize(sortableContainer, opts.itemSerializer, opts.containerSerializer);
          });
      }
      sortableElements.forEach(function (sortableElement) {
          if (/enable|disable|destroy/.test(method)) {
              return sortable[method](sortableElement);
          }
          // log deprecation
          ['connectWith', 'disableIEFix'].forEach(function (configKey) {
              if (Object.prototype.hasOwnProperty.call(options, configKey) && options[configKey] !== null) {
                  console.warn("HTML5Sortable: You are using the deprecated configuration \"" + configKey + "\". This will be removed in an upcoming version, make sure to migrate to the new options when updating.");
              }
          });
          // merge options with default options
          options = Object.assign({}, defaultConfiguration, store(sortableElement).config, options);
          // init data store for sortable
          store(sortableElement).config = options;
          // set options on sortable
          addData(sortableElement, 'opts', options);
          // property to define as sortable
          sortableElement.isSortable = true;
          // reset sortable
          _reloadSortable(sortableElement);
          // initialize
          var listItems = _filter(sortableElement.children, options.items);
          // create element if user defined a placeholder element as a string
          var customPlaceholder;
          if (options.placeholder !== null && options.placeholder !== undefined) {
              var tempContainer = document.createElement(sortableElement.tagName);
              if (options.placeholder instanceof HTMLElement) {
                  tempContainer.appendChild(options.placeholder);
              }
              else {
                  tempContainer.innerHTML = options.placeholder;
              }
              customPlaceholder = tempContainer.children[0];
          }
          // add placeholder
          store(sortableElement).placeholder = _makePlaceholder(sortableElement, customPlaceholder, options.placeholderClass);
          addData(sortableElement, 'items', options.items);
          if (options.acceptFrom) {
              addData(sortableElement, 'acceptFrom', options.acceptFrom);
          }
          else if (options.connectWith) {
              addData(sortableElement, 'connectWith', options.connectWith);
          }
          _enableSortable(sortableElement);
          addAttribute(listItems, 'role', 'option');
          addAttribute(listItems, 'aria-grabbed', 'false');
          // enable hover class
          enableHoverClass(sortableElement, true);
          /*
           Handle drag events on draggable items
           Handle is set at the sortableElement level as it will bubble up
           from the item
           */
          addEventListener(sortableElement, 'dragstart', function (e) {
              // ignore dragstart events
              var target = getEventTarget(e);
              if (target.isSortable === true) {
                  return;
              }
              e.stopImmediatePropagation();
              if ((options.handle && !target.matches(options.handle)) || target.getAttribute('draggable') === 'false') {
                  return;
              }
              var sortableContainer = findSortable(target, e);
              var dragItem = findDragElement(sortableContainer, target);
              // grab values
              originItemsBeforeUpdate = _filter(sortableContainer.children, options.items);
              originIndex = originItemsBeforeUpdate.indexOf(dragItem);
              originElementIndex = _index(dragItem, sortableContainer.children);
              originContainer = sortableContainer;
              // add transparent clone or other ghost to cursor
              setDragImage(e, dragItem, options.customDragImage);
              // cache selsection & add attr for dragging
              draggingHeight = _getElementHeight(dragItem);
              draggingWidth = _getElementWidth(dragItem);
              dragItem.classList.add(options.draggingClass);
              dragging = _getDragging(dragItem, sortableContainer);
              addAttribute(dragging, 'aria-grabbed', 'true');
              // dispatch sortstart event on each element in group
              sortableContainer.dispatchEvent(new CustomEvent('sortstart', {
                  detail: {
                      origin: {
                          elementIndex: originElementIndex,
                          index: originIndex,
                          container: originContainer
                      },
                      item: dragging,
                      originalTarget: target
                  }
              }));
          });
          /*
           We are capturing targetSortable before modifications with 'dragenter' event
          */
          addEventListener(sortableElement, 'dragenter', function (e) {
              var target = getEventTarget(e);
              var sortableContainer = findSortable(target, e);
              if (sortableContainer && sortableContainer !== previousContainer) {
                  destinationItemsBeforeUpdate = _filter(sortableContainer.children, addData(sortableContainer, 'items'))
                      .filter(function (item) { return item !== store(sortableElement).placeholder; });
                  if (options.dropTargetContainerClass) {
                      sortableContainer.classList.add(options.dropTargetContainerClass);
                  }
                  sortableContainer.dispatchEvent(new CustomEvent('sortenter', {
                      detail: {
                          origin: {
                              elementIndex: originElementIndex,
                              index: originIndex,
                              container: originContainer
                          },
                          destination: {
                              container: sortableContainer,
                              itemsBeforeUpdate: destinationItemsBeforeUpdate
                          },
                          item: dragging,
                          originalTarget: target
                      }
                  }));
                  addEventListener(sortableContainer, 'dragleave', function (e) {
                      // TODO: rename outTarget to be more self-explanatory
                      // e.fromElement for very old browsers, similar to relatedTarget
                      var outTarget = e.relatedTarget || e.fromElement;
                      if (!e.currentTarget.contains(outTarget)) {
                          if (options.dropTargetContainerClass) {
                              sortableContainer.classList.remove(options.dropTargetContainerClass);
                          }
                          sortableContainer.dispatchEvent(new CustomEvent('sortleave', {
                              detail: {
                                  origin: {
                                      elementIndex: originElementIndex,
                                      index: originIndex,
                                      container: sortableContainer
                                  },
                                  item: dragging,
                                  originalTarget: target
                              }
                          }));
                      }
                  });
              }
              previousContainer = sortableContainer;
          });
          /*
           * Dragend Event - https://developer.mozilla.org/en-US/docs/Web/Events/dragend
           * Fires each time dragEvent end, or ESC pressed
           * We are using it to clean up any draggable elements and placeholders
           */
          addEventListener(sortableElement, 'dragend', function (e) {
              if (!dragging) {
                  return;
              }
              dragging.classList.remove(options.draggingClass);
              addAttribute(dragging, 'aria-grabbed', 'false');
              if (dragging.getAttribute('aria-copied') === 'true' && addData(dragging, 'dropped') !== 'true') {
                  dragging.remove();
              }
              dragging.style.display = dragging.oldDisplay;
              delete dragging.oldDisplay;
              var visiblePlaceholder = Array.from(stores.values()).map(function (data) { return data.placeholder; })
                  .filter(function (placeholder) { return placeholder instanceof HTMLElement; })
                  .filter(isInDom)[0];
              if (visiblePlaceholder) {
                  visiblePlaceholder.remove();
              }
              // dispatch sortstart event on each element in group
              sortableElement.dispatchEvent(new CustomEvent('sortstop', {
                  detail: {
                      origin: {
                          elementIndex: originElementIndex,
                          index: originIndex,
                          container: originContainer
                      },
                      item: dragging
                  }
              }));
              previousContainer = null;
              dragging = null;
              draggingHeight = null;
              draggingWidth = null;
          });
          /*
           * Drop Event - https://developer.mozilla.org/en-US/docs/Web/Events/drop
           * Fires when valid drop target area is hit
           */
          addEventListener(sortableElement, 'drop', function (e) {
              if (!_listsConnected(sortableElement, dragging.parentElement)) {
                  return;
              }
              e.preventDefault();
              e.stopPropagation();
              addData(dragging, 'dropped', 'true');
              // get the one placeholder that is currently visible
              var visiblePlaceholder = Array.from(stores.values()).map(function (data) {
                  return data.placeholder;
              })
                  // filter only HTMLElements
                  .filter(function (placeholder) { return placeholder instanceof HTMLElement; })
                  // filter only elements in DOM
                  .filter(isInDom)[0];
              // attach element after placeholder
              insertAfter(visiblePlaceholder, dragging);
              // remove placeholder from dom
              visiblePlaceholder.remove();
              /*
               * Fires Custom Event - 'sortstop'
               */
              sortableElement.dispatchEvent(new CustomEvent('sortstop', {
                  detail: {
                      origin: {
                          elementIndex: originElementIndex,
                          index: originIndex,
                          container: originContainer
                      },
                      item: dragging
                  }
              }));
              var placeholder = store(sortableElement).placeholder;
              var originItems = _filter(originContainer.children, options.items)
                  .filter(function (item) { return item !== placeholder; });
              var destinationContainer = this.isSortable === true ? this : this.parentElement;
              var destinationItems = _filter(destinationContainer.children, addData(destinationContainer, 'items'))
                  .filter(function (item) { return item !== placeholder; });
              var destinationElementIndex = _index(dragging, Array.from(dragging.parentElement.children)
                  .filter(function (item) { return item !== placeholder; }));
              var destinationIndex = _index(dragging, destinationItems);
              if (options.dropTargetContainerClass) {
                  destinationContainer.classList.remove(options.dropTargetContainerClass);
              }
              /*
               * When a list item changed container lists or index within a list
               * Fires Custom Event - 'sortupdate'
               */
              if (originElementIndex !== destinationElementIndex || originContainer !== destinationContainer) {
                  sortableElement.dispatchEvent(new CustomEvent('sortupdate', {
                      detail: {
                          origin: {
                              elementIndex: originElementIndex,
                              index: originIndex,
                              container: originContainer,
                              itemsBeforeUpdate: originItemsBeforeUpdate,
                              items: originItems
                          },
                          destination: {
                              index: destinationIndex,
                              elementIndex: destinationElementIndex,
                              container: destinationContainer,
                              itemsBeforeUpdate: destinationItemsBeforeUpdate,
                              items: destinationItems
                          },
                          item: dragging
                      }
                  }));
              }
          });
          var debouncedDragOverEnter = _debounce(function (sortableElement, element, pageX, pageY) {
              if (!dragging) {
                  return;
              }
              // set placeholder height if forcePlaceholderSize option is set
              if (options.forcePlaceholderSize) {
                  store(sortableElement).placeholder.style.height = draggingHeight + 'px';
                  store(sortableElement).placeholder.style.width = draggingWidth + 'px';
              }
              // if element the draggedItem is dragged onto is within the array of all elements in list
              // (not only items, but also disabled, etc.)
              if (Array.from(sortableElement.children).indexOf(element) > -1) {
                  var thisHeight = _getElementHeight(element);
                  var thisWidth = _getElementWidth(element);
                  var placeholderIndex = _index(store(sortableElement).placeholder, element.parentElement.children);
                  var thisIndex = _index(element, element.parentElement.children);
                  // Check if `element` is bigger than the draggable. If it is, we have to define a dead zone to prevent flickering
                  if (thisHeight > draggingHeight || thisWidth > draggingWidth) {
                      // Dead zone?
                      var deadZoneVertical = thisHeight - draggingHeight;
                      var deadZoneHorizontal = thisWidth - draggingWidth;
                      var offsetTop = _offset(element).top;
                      var offsetLeft = _offset(element).left;
                      if (placeholderIndex < thisIndex &&
                          ((options.orientation === 'vertical' && pageY < offsetTop) ||
                              (options.orientation === 'horizontal' && pageX < offsetLeft))) {
                          return;
                      }
                      if (placeholderIndex > thisIndex &&
                          ((options.orientation === 'vertical' && pageY > offsetTop + thisHeight - deadZoneVertical) ||
                              (options.orientation === 'horizontal' && pageX > offsetLeft + thisWidth - deadZoneHorizontal))) {
                          return;
                      }
                  }
                  if (dragging.oldDisplay === undefined) {
                      dragging.oldDisplay = dragging.style.display;
                  }
                  if (dragging.style.display !== 'none') {
                      dragging.style.display = 'none';
                  }
                  // To avoid flicker, determine where to position the placeholder
                  // based on where the mouse pointer is relative to the elements
                  // vertical center.
                  var placeAfter = false;
                  try {
                      var elementMiddleVertical = _offset(element).top + element.offsetHeight / 2;
                      var elementMiddleHorizontal = _offset(element).left + element.offsetWidth / 2;
                      placeAfter = (options.orientation === 'vertical' && (pageY >= elementMiddleVertical)) ||
                          (options.orientation === 'horizontal' && (pageX >= elementMiddleHorizontal));
                  }
                  catch (e) {
                      placeAfter = placeholderIndex < thisIndex;
                  }
                  if (placeAfter) {
                      insertAfter(element, store(sortableElement).placeholder);
                  }
                  else {
                      insertBefore(element, store(sortableElement).placeholder);
                  }
                  // get placeholders from all stores & remove all but current one
                  Array.from(stores.values())
                      // remove empty values
                      .filter(function (data) { return data.placeholder !== undefined; })
                      // foreach placeholder in array if outside of current sorableContainer -> remove from DOM
                      .forEach(function (data) {
                      if (data.placeholder !== store(sortableElement).placeholder) {
                          data.placeholder.remove();
                      }
                  });
              }
              else {
                  // get all placeholders from store
                  var placeholders = Array.from(stores.values())
                      .filter(function (data) { return data.placeholder !== undefined; })
                      .map(function (data) {
                      return data.placeholder;
                  });
                  // check if element is not in placeholders
                  if (placeholders.indexOf(element) === -1 && sortableElement === element && !_filter(element.children, options.items).length) {
                      placeholders.forEach(function (element) { return element.remove(); });
                      element.appendChild(store(sortableElement).placeholder);
                  }
              }
          }, options.debounce);
          // Handle dragover and dragenter events on draggable items
          var onDragOverEnter = function (e) {
              var element = e.target;
              var sortableElement = element.isSortable === true ? element : findSortable(element, e);
              element = findDragElement(sortableElement, element);
              if (!dragging || !_listsConnected(sortableElement, dragging.parentElement) || addData(sortableElement, '_disabled') === 'true') {
                  return;
              }
              var options = addData(sortableElement, 'opts');
              if (parseInt(options.maxItems) && _filter(sortableElement.children, addData(sortableElement, 'items')).length >= parseInt(options.maxItems) && dragging.parentElement !== sortableElement) {
                  return;
              }
              e.preventDefault();
              e.stopPropagation();
              e.dataTransfer.dropEffect = store(sortableElement).getConfig('copy') === true ? 'copy' : 'move';
              debouncedDragOverEnter(sortableElement, element, e.pageX, e.pageY);
          };
          addEventListener(listItems.concat(sortableElement), 'dragover', onDragOverEnter);
          addEventListener(listItems.concat(sortableElement), 'dragenter', onDragOverEnter);
      });
      return sortableElements;
  }
  sortable.destroy = function (sortableElement) {
      _destroySortable(sortableElement);
  };
  sortable.enable = function (sortableElement) {
      _enableSortable(sortableElement);
  };
  sortable.disable = function (sortableElement) {
      _disableSortable(sortableElement);
  };
  /* START.TESTS_ONLY */
  sortable.__testing = {
      // add internal methods here for testing purposes
      _data: addData,
      _removeItemEvents: _removeItemEvents,
      _removeItemData: _removeItemData,
      _removeSortableData: _removeSortableData,
      _removeContainerEvents: _removeContainerEvents
  };

  return sortable;

}).call(exports, __webpack_require__, exports, module),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));

/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(5)))

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
//# sourceMappingURL=sortable.js.map