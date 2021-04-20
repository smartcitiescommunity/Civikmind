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
/******/ 	return __webpack_require__(__webpack_require__.s = 500);
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

/***/ 500:
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

// jQuery File Upload plugin
__webpack_require__(501);
__webpack_require__(503);


/***/ }),

/***/ 501:
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(4)(__webpack_require__(502))

/***/ }),

/***/ 502:
/***/ (function(module, exports) {

module.exports = "/*\n * jQuery File Upload Plugin\n * https://github.com/blueimp/jQuery-File-Upload\n *\n * Copyright 2010, Sebastian Tschan\n * https://blueimp.net\n *\n * Licensed under the MIT license:\n * https://opensource.org/licenses/MIT\n */\n\n/* global define, require */\n/* eslint-disable new-cap */\n\n(function (factory) {\n  'use strict';\n  if (typeof define === 'function' && define.amd) {\n    // Register as an anonymous AMD module:\n    define(['jquery', 'jquery-ui/ui/widget'], factory);\n  } else if (typeof exports === 'object') {\n    // Node/CommonJS:\n    factory(require('jquery'), require('./vendor/jquery.ui.widget'));\n  } else {\n    // Browser globals:\n    factory(window.jQuery);\n  }\n})(function ($) {\n  'use strict';\n\n  // Detect file input support, based on\n  // https://viljamis.com/2012/file-upload-support-on-mobile/\n  $.support.fileInput = !(\n    new RegExp(\n      // Handle devices which give false positives for the feature detection:\n      '(Android (1\\\\.[0156]|2\\\\.[01]))' +\n        '|(Windows Phone (OS 7|8\\\\.0))|(XBLWP)|(ZuneWP)|(WPDesktop)' +\n        '|(w(eb)?OSBrowser)|(webOS)' +\n        '|(Kindle/(1\\\\.0|2\\\\.[05]|3\\\\.0))'\n    ).test(window.navigator.userAgent) ||\n    // Feature detection for all other devices:\n    $('<input type=\"file\"/>').prop('disabled')\n  );\n\n  // The FileReader API is not actually used, but works as feature detection,\n  // as some Safari versions (5?) support XHR file uploads via the FormData API,\n  // but not non-multipart XHR file uploads.\n  // window.XMLHttpRequestUpload is not available on IE10, so we check for\n  // window.ProgressEvent instead to detect XHR2 file upload capability:\n  $.support.xhrFileUpload = !!(window.ProgressEvent && window.FileReader);\n  $.support.xhrFormDataFileUpload = !!window.FormData;\n\n  // Detect support for Blob slicing (required for chunked uploads):\n  $.support.blobSlice =\n    window.Blob &&\n    (Blob.prototype.slice ||\n      Blob.prototype.webkitSlice ||\n      Blob.prototype.mozSlice);\n\n  /**\n   * Helper function to create drag handlers for dragover/dragenter/dragleave\n   *\n   * @param {string} type Event type\n   * @returns {Function} Drag handler\n   */\n  function getDragHandler(type) {\n    var isDragOver = type === 'dragover';\n    return function (e) {\n      e.dataTransfer = e.originalEvent && e.originalEvent.dataTransfer;\n      var dataTransfer = e.dataTransfer;\n      if (\n        dataTransfer &&\n        $.inArray('Files', dataTransfer.types) !== -1 &&\n        this._trigger(type, $.Event(type, { delegatedEvent: e })) !== false\n      ) {\n        e.preventDefault();\n        if (isDragOver) {\n          dataTransfer.dropEffect = 'copy';\n        }\n      }\n    };\n  }\n\n  // The fileupload widget listens for change events on file input fields defined\n  // via fileInput setting and paste or drop events of the given dropZone.\n  // In addition to the default jQuery Widget methods, the fileupload widget\n  // exposes the \"add\" and \"send\" methods, to add or directly send files using\n  // the fileupload API.\n  // By default, files added via file input selection, paste, drag & drop or\n  // \"add\" method are uploaded immediately, but it is possible to override\n  // the \"add\" callback option to queue file uploads.\n  $.widget('blueimp.fileupload', {\n    options: {\n      // The drop target element(s), by the default the complete document.\n      // Set to null to disable drag & drop support:\n      dropZone: $(document),\n      // The paste target element(s), by the default undefined.\n      // Set to a DOM node or jQuery object to enable file pasting:\n      pasteZone: undefined,\n      // The file input field(s), that are listened to for change events.\n      // If undefined, it is set to the file input fields inside\n      // of the widget element on plugin initialization.\n      // Set to null to disable the change listener.\n      fileInput: undefined,\n      // By default, the file input field is replaced with a clone after\n      // each input field change event. This is required for iframe transport\n      // queues and allows change events to be fired for the same file\n      // selection, but can be disabled by setting the following option to false:\n      replaceFileInput: true,\n      // The parameter name for the file form data (the request argument name).\n      // If undefined or empty, the name property of the file input field is\n      // used, or \"files[]\" if the file input name property is also empty,\n      // can be a string or an array of strings:\n      paramName: undefined,\n      // By default, each file of a selection is uploaded using an individual\n      // request for XHR type uploads. Set to false to upload file\n      // selections in one request each:\n      singleFileUploads: true,\n      // To limit the number of files uploaded with one XHR request,\n      // set the following option to an integer greater than 0:\n      limitMultiFileUploads: undefined,\n      // The following option limits the number of files uploaded with one\n      // XHR request to keep the request size under or equal to the defined\n      // limit in bytes:\n      limitMultiFileUploadSize: undefined,\n      // Multipart file uploads add a number of bytes to each uploaded file,\n      // therefore the following option adds an overhead for each file used\n      // in the limitMultiFileUploadSize configuration:\n      limitMultiFileUploadSizeOverhead: 512,\n      // Set the following option to true to issue all file upload requests\n      // in a sequential order:\n      sequentialUploads: false,\n      // To limit the number of concurrent uploads,\n      // set the following option to an integer greater than 0:\n      limitConcurrentUploads: undefined,\n      // Set the following option to true to force iframe transport uploads:\n      forceIframeTransport: false,\n      // Set the following option to the location of a redirect url on the\n      // origin server, for cross-domain iframe transport uploads:\n      redirect: undefined,\n      // The parameter name for the redirect url, sent as part of the form\n      // data and set to 'redirect' if this option is empty:\n      redirectParamName: undefined,\n      // Set the following option to the location of a postMessage window,\n      // to enable postMessage transport uploads:\n      postMessage: undefined,\n      // By default, XHR file uploads are sent as multipart/form-data.\n      // The iframe transport is always using multipart/form-data.\n      // Set to false to enable non-multipart XHR uploads:\n      multipart: true,\n      // To upload large files in smaller chunks, set the following option\n      // to a preferred maximum chunk size. If set to 0, null or undefined,\n      // or the browser does not support the required Blob API, files will\n      // be uploaded as a whole.\n      maxChunkSize: undefined,\n      // When a non-multipart upload or a chunked multipart upload has been\n      // aborted, this option can be used to resume the upload by setting\n      // it to the size of the already uploaded bytes. This option is most\n      // useful when modifying the options object inside of the \"add\" or\n      // \"send\" callbacks, as the options are cloned for each file upload.\n      uploadedBytes: undefined,\n      // By default, failed (abort or error) file uploads are removed from the\n      // global progress calculation. Set the following option to false to\n      // prevent recalculating the global progress data:\n      recalculateProgress: true,\n      // Interval in milliseconds to calculate and trigger progress events:\n      progressInterval: 100,\n      // Interval in milliseconds to calculate progress bitrate:\n      bitrateInterval: 500,\n      // By default, uploads are started automatically when adding files:\n      autoUpload: true,\n      // By default, duplicate file names are expected to be handled on\n      // the server-side. If this is not possible (e.g. when uploading\n      // files directly to Amazon S3), the following option can be set to\n      // an empty object or an object mapping existing filenames, e.g.:\n      // { \"image.jpg\": true, \"image (1).jpg\": true }\n      // If it is set, all files will be uploaded with unique filenames,\n      // adding increasing number suffixes if necessary, e.g.:\n      // \"image (2).jpg\"\n      uniqueFilenames: undefined,\n\n      // Error and info messages:\n      messages: {\n        uploadedBytes: 'Uploaded bytes exceed file size'\n      },\n\n      // Translation function, gets the message key to be translated\n      // and an object with context specific data as arguments:\n      i18n: function (message, context) {\n        // eslint-disable-next-line no-param-reassign\n        message = this.messages[message] || message.toString();\n        if (context) {\n          $.each(context, function (key, value) {\n            // eslint-disable-next-line no-param-reassign\n            message = message.replace('{' + key + '}', value);\n          });\n        }\n        return message;\n      },\n\n      // Additional form data to be sent along with the file uploads can be set\n      // using this option, which accepts an array of objects with name and\n      // value properties, a function returning such an array, a FormData\n      // object (for XHR file uploads), or a simple object.\n      // The form of the first fileInput is given as parameter to the function:\n      formData: function (form) {\n        return form.serializeArray();\n      },\n\n      // The add callback is invoked as soon as files are added to the fileupload\n      // widget (via file input selection, drag & drop, paste or add API call).\n      // If the singleFileUploads option is enabled, this callback will be\n      // called once for each file in the selection for XHR file uploads, else\n      // once for each file selection.\n      //\n      // The upload starts when the submit method is invoked on the data parameter.\n      // The data object contains a files property holding the added files\n      // and allows you to override plugin options as well as define ajax settings.\n      //\n      // Listeners for this callback can also be bound the following way:\n      // .on('fileuploadadd', func);\n      //\n      // data.submit() returns a Promise object and allows to attach additional\n      // handlers using jQuery's Deferred callbacks:\n      // data.submit().done(func).fail(func).always(func);\n      add: function (e, data) {\n        if (e.isDefaultPrevented()) {\n          return false;\n        }\n        if (\n          data.autoUpload ||\n          (data.autoUpload !== false &&\n            $(this).fileupload('option', 'autoUpload'))\n        ) {\n          data.process().done(function () {\n            data.submit();\n          });\n        }\n      },\n\n      // Other callbacks:\n\n      // Callback for the submit event of each file upload:\n      // submit: function (e, data) {}, // .on('fileuploadsubmit', func);\n\n      // Callback for the start of each file upload request:\n      // send: function (e, data) {}, // .on('fileuploadsend', func);\n\n      // Callback for successful uploads:\n      // done: function (e, data) {}, // .on('fileuploaddone', func);\n\n      // Callback for failed (abort or error) uploads:\n      // fail: function (e, data) {}, // .on('fileuploadfail', func);\n\n      // Callback for completed (success, abort or error) requests:\n      // always: function (e, data) {}, // .on('fileuploadalways', func);\n\n      // Callback for upload progress events:\n      // progress: function (e, data) {}, // .on('fileuploadprogress', func);\n\n      // Callback for global upload progress events:\n      // progressall: function (e, data) {}, // .on('fileuploadprogressall', func);\n\n      // Callback for uploads start, equivalent to the global ajaxStart event:\n      // start: function (e) {}, // .on('fileuploadstart', func);\n\n      // Callback for uploads stop, equivalent to the global ajaxStop event:\n      // stop: function (e) {}, // .on('fileuploadstop', func);\n\n      // Callback for change events of the fileInput(s):\n      // change: function (e, data) {}, // .on('fileuploadchange', func);\n\n      // Callback for paste events to the pasteZone(s):\n      // paste: function (e, data) {}, // .on('fileuploadpaste', func);\n\n      // Callback for drop events of the dropZone(s):\n      // drop: function (e, data) {}, // .on('fileuploaddrop', func);\n\n      // Callback for dragover events of the dropZone(s):\n      // dragover: function (e) {}, // .on('fileuploaddragover', func);\n\n      // Callback before the start of each chunk upload request (before form data initialization):\n      // chunkbeforesend: function (e, data) {}, // .on('fileuploadchunkbeforesend', func);\n\n      // Callback for the start of each chunk upload request:\n      // chunksend: function (e, data) {}, // .on('fileuploadchunksend', func);\n\n      // Callback for successful chunk uploads:\n      // chunkdone: function (e, data) {}, // .on('fileuploadchunkdone', func);\n\n      // Callback for failed (abort or error) chunk uploads:\n      // chunkfail: function (e, data) {}, // .on('fileuploadchunkfail', func);\n\n      // Callback for completed (success, abort or error) chunk upload requests:\n      // chunkalways: function (e, data) {}, // .on('fileuploadchunkalways', func);\n\n      // The plugin options are used as settings object for the ajax calls.\n      // The following are jQuery ajax settings required for the file uploads:\n      processData: false,\n      contentType: false,\n      cache: false,\n      timeout: 0\n    },\n\n    // jQuery versions before 1.8 require promise.pipe if the return value is\n    // used, as promise.then in older versions has a different behavior, see:\n    // https://blog.jquery.com/2012/08/09/jquery-1-8-released/\n    // https://bugs.jquery.com/ticket/11010\n    // https://github.com/blueimp/jQuery-File-Upload/pull/3435\n    _promisePipe: (function () {\n      var parts = $.fn.jquery.split('.');\n      return Number(parts[0]) > 1 || Number(parts[1]) > 7 ? 'then' : 'pipe';\n    })(),\n\n    // A list of options that require reinitializing event listeners and/or\n    // special initialization code:\n    _specialOptions: [\n      'fileInput',\n      'dropZone',\n      'pasteZone',\n      'multipart',\n      'forceIframeTransport'\n    ],\n\n    _blobSlice:\n      $.support.blobSlice &&\n      function () {\n        var slice = this.slice || this.webkitSlice || this.mozSlice;\n        return slice.apply(this, arguments);\n      },\n\n    _BitrateTimer: function () {\n      this.timestamp = Date.now ? Date.now() : new Date().getTime();\n      this.loaded = 0;\n      this.bitrate = 0;\n      this.getBitrate = function (now, loaded, interval) {\n        var timeDiff = now - this.timestamp;\n        if (!this.bitrate || !interval || timeDiff > interval) {\n          this.bitrate = (loaded - this.loaded) * (1000 / timeDiff) * 8;\n          this.loaded = loaded;\n          this.timestamp = now;\n        }\n        return this.bitrate;\n      };\n    },\n\n    _isXHRUpload: function (options) {\n      return (\n        !options.forceIframeTransport &&\n        ((!options.multipart && $.support.xhrFileUpload) ||\n          $.support.xhrFormDataFileUpload)\n      );\n    },\n\n    _getFormData: function (options) {\n      var formData;\n      if ($.type(options.formData) === 'function') {\n        return options.formData(options.form);\n      }\n      if ($.isArray(options.formData)) {\n        return options.formData;\n      }\n      if ($.type(options.formData) === 'object') {\n        formData = [];\n        $.each(options.formData, function (name, value) {\n          formData.push({ name: name, value: value });\n        });\n        return formData;\n      }\n      return [];\n    },\n\n    _getTotal: function (files) {\n      var total = 0;\n      $.each(files, function (index, file) {\n        total += file.size || 1;\n      });\n      return total;\n    },\n\n    _initProgressObject: function (obj) {\n      var progress = {\n        loaded: 0,\n        total: 0,\n        bitrate: 0\n      };\n      if (obj._progress) {\n        $.extend(obj._progress, progress);\n      } else {\n        obj._progress = progress;\n      }\n    },\n\n    _initResponseObject: function (obj) {\n      var prop;\n      if (obj._response) {\n        for (prop in obj._response) {\n          if (Object.prototype.hasOwnProperty.call(obj._response, prop)) {\n            delete obj._response[prop];\n          }\n        }\n      } else {\n        obj._response = {};\n      }\n    },\n\n    _onProgress: function (e, data) {\n      if (e.lengthComputable) {\n        var now = Date.now ? Date.now() : new Date().getTime(),\n          loaded;\n        if (\n          data._time &&\n          data.progressInterval &&\n          now - data._time < data.progressInterval &&\n          e.loaded !== e.total\n        ) {\n          return;\n        }\n        data._time = now;\n        loaded =\n          Math.floor(\n            (e.loaded / e.total) * (data.chunkSize || data._progress.total)\n          ) + (data.uploadedBytes || 0);\n        // Add the difference from the previously loaded state\n        // to the global loaded counter:\n        this._progress.loaded += loaded - data._progress.loaded;\n        this._progress.bitrate = this._bitrateTimer.getBitrate(\n          now,\n          this._progress.loaded,\n          data.bitrateInterval\n        );\n        data._progress.loaded = data.loaded = loaded;\n        data._progress.bitrate = data.bitrate = data._bitrateTimer.getBitrate(\n          now,\n          loaded,\n          data.bitrateInterval\n        );\n        // Trigger a custom progress event with a total data property set\n        // to the file size(s) of the current upload and a loaded data\n        // property calculated accordingly:\n        this._trigger(\n          'progress',\n          $.Event('progress', { delegatedEvent: e }),\n          data\n        );\n        // Trigger a global progress event for all current file uploads,\n        // including ajax calls queued for sequential file uploads:\n        this._trigger(\n          'progressall',\n          $.Event('progressall', { delegatedEvent: e }),\n          this._progress\n        );\n      }\n    },\n\n    _initProgressListener: function (options) {\n      var that = this,\n        xhr = options.xhr ? options.xhr() : $.ajaxSettings.xhr();\n      // Accesss to the native XHR object is required to add event listeners\n      // for the upload progress event:\n      if (xhr.upload) {\n        $(xhr.upload).on('progress', function (e) {\n          var oe = e.originalEvent;\n          // Make sure the progress event properties get copied over:\n          e.lengthComputable = oe.lengthComputable;\n          e.loaded = oe.loaded;\n          e.total = oe.total;\n          that._onProgress(e, options);\n        });\n        options.xhr = function () {\n          return xhr;\n        };\n      }\n    },\n\n    _deinitProgressListener: function (options) {\n      var xhr = options.xhr ? options.xhr() : $.ajaxSettings.xhr();\n      if (xhr.upload) {\n        $(xhr.upload).off('progress');\n      }\n    },\n\n    _isInstanceOf: function (type, obj) {\n      // Cross-frame instanceof check\n      return Object.prototype.toString.call(obj) === '[object ' + type + ']';\n    },\n\n    _getUniqueFilename: function (name, map) {\n      // eslint-disable-next-line no-param-reassign\n      name = String(name);\n      if (map[name]) {\n        // eslint-disable-next-line no-param-reassign\n        name = name.replace(/(?: \\(([\\d]+)\\))?(\\.[^.]+)?$/, function (\n          _,\n          p1,\n          p2\n        ) {\n          var index = p1 ? Number(p1) + 1 : 1;\n          var ext = p2 || '';\n          return ' (' + index + ')' + ext;\n        });\n        return this._getUniqueFilename(name, map);\n      }\n      map[name] = true;\n      return name;\n    },\n\n    _initXHRData: function (options) {\n      var that = this,\n        formData,\n        file = options.files[0],\n        // Ignore non-multipart setting if not supported:\n        multipart = options.multipart || !$.support.xhrFileUpload,\n        paramName =\n          $.type(options.paramName) === 'array'\n            ? options.paramName[0]\n            : options.paramName;\n      options.headers = $.extend({}, options.headers);\n      if (options.contentRange) {\n        options.headers['Content-Range'] = options.contentRange;\n      }\n      if (!multipart || options.blob || !this._isInstanceOf('File', file)) {\n        options.headers['Content-Disposition'] =\n          'attachment; filename=\"' +\n          encodeURI(file.uploadName || file.name) +\n          '\"';\n      }\n      if (!multipart) {\n        options.contentType = file.type || 'application/octet-stream';\n        options.data = options.blob || file;\n      } else if ($.support.xhrFormDataFileUpload) {\n        if (options.postMessage) {\n          // window.postMessage does not allow sending FormData\n          // objects, so we just add the File/Blob objects to\n          // the formData array and let the postMessage window\n          // create the FormData object out of this array:\n          formData = this._getFormData(options);\n          if (options.blob) {\n            formData.push({\n              name: paramName,\n              value: options.blob\n            });\n          } else {\n            $.each(options.files, function (index, file) {\n              formData.push({\n                name:\n                  ($.type(options.paramName) === 'array' &&\n                    options.paramName[index]) ||\n                  paramName,\n                value: file\n              });\n            });\n          }\n        } else {\n          if (that._isInstanceOf('FormData', options.formData)) {\n            formData = options.formData;\n          } else {\n            formData = new FormData();\n            $.each(this._getFormData(options), function (index, field) {\n              formData.append(field.name, field.value);\n            });\n          }\n          if (options.blob) {\n            formData.append(\n              paramName,\n              options.blob,\n              file.uploadName || file.name\n            );\n          } else {\n            $.each(options.files, function (index, file) {\n              // This check allows the tests to run with\n              // dummy objects:\n              if (\n                that._isInstanceOf('File', file) ||\n                that._isInstanceOf('Blob', file)\n              ) {\n                var fileName = file.uploadName || file.name;\n                if (options.uniqueFilenames) {\n                  fileName = that._getUniqueFilename(\n                    fileName,\n                    options.uniqueFilenames\n                  );\n                }\n                formData.append(\n                  ($.type(options.paramName) === 'array' &&\n                    options.paramName[index]) ||\n                    paramName,\n                  file,\n                  fileName\n                );\n              }\n            });\n          }\n        }\n        options.data = formData;\n      }\n      // Blob reference is not needed anymore, free memory:\n      options.blob = null;\n    },\n\n    _initIframeSettings: function (options) {\n      var targetHost = $('<a></a>').prop('href', options.url).prop('host');\n      // Setting the dataType to iframe enables the iframe transport:\n      options.dataType = 'iframe ' + (options.dataType || '');\n      // The iframe transport accepts a serialized array as form data:\n      options.formData = this._getFormData(options);\n      // Add redirect url to form data on cross-domain uploads:\n      if (options.redirect && targetHost && targetHost !== location.host) {\n        options.formData.push({\n          name: options.redirectParamName || 'redirect',\n          value: options.redirect\n        });\n      }\n    },\n\n    _initDataSettings: function (options) {\n      if (this._isXHRUpload(options)) {\n        if (!this._chunkedUpload(options, true)) {\n          if (!options.data) {\n            this._initXHRData(options);\n          }\n          this._initProgressListener(options);\n        }\n        if (options.postMessage) {\n          // Setting the dataType to postmessage enables the\n          // postMessage transport:\n          options.dataType = 'postmessage ' + (options.dataType || '');\n        }\n      } else {\n        this._initIframeSettings(options);\n      }\n    },\n\n    _getParamName: function (options) {\n      var fileInput = $(options.fileInput),\n        paramName = options.paramName;\n      if (!paramName) {\n        paramName = [];\n        fileInput.each(function () {\n          var input = $(this),\n            name = input.prop('name') || 'files[]',\n            i = (input.prop('files') || [1]).length;\n          while (i) {\n            paramName.push(name);\n            i -= 1;\n          }\n        });\n        if (!paramName.length) {\n          paramName = [fileInput.prop('name') || 'files[]'];\n        }\n      } else if (!$.isArray(paramName)) {\n        paramName = [paramName];\n      }\n      return paramName;\n    },\n\n    _initFormSettings: function (options) {\n      // Retrieve missing options from the input field and the\n      // associated form, if available:\n      if (!options.form || !options.form.length) {\n        options.form = $(options.fileInput.prop('form'));\n        // If the given file input doesn't have an associated form,\n        // use the default widget file input's form:\n        if (!options.form.length) {\n          options.form = $(this.options.fileInput.prop('form'));\n        }\n      }\n      options.paramName = this._getParamName(options);\n      if (!options.url) {\n        options.url = options.form.prop('action') || location.href;\n      }\n      // The HTTP request method must be \"POST\" or \"PUT\":\n      options.type = (\n        options.type ||\n        ($.type(options.form.prop('method')) === 'string' &&\n          options.form.prop('method')) ||\n        ''\n      ).toUpperCase();\n      if (\n        options.type !== 'POST' &&\n        options.type !== 'PUT' &&\n        options.type !== 'PATCH'\n      ) {\n        options.type = 'POST';\n      }\n      if (!options.formAcceptCharset) {\n        options.formAcceptCharset = options.form.attr('accept-charset');\n      }\n    },\n\n    _getAJAXSettings: function (data) {\n      var options = $.extend({}, this.options, data);\n      this._initFormSettings(options);\n      this._initDataSettings(options);\n      return options;\n    },\n\n    // jQuery 1.6 doesn't provide .state(),\n    // while jQuery 1.8+ removed .isRejected() and .isResolved():\n    _getDeferredState: function (deferred) {\n      if (deferred.state) {\n        return deferred.state();\n      }\n      if (deferred.isResolved()) {\n        return 'resolved';\n      }\n      if (deferred.isRejected()) {\n        return 'rejected';\n      }\n      return 'pending';\n    },\n\n    // Maps jqXHR callbacks to the equivalent\n    // methods of the given Promise object:\n    _enhancePromise: function (promise) {\n      promise.success = promise.done;\n      promise.error = promise.fail;\n      promise.complete = promise.always;\n      return promise;\n    },\n\n    // Creates and returns a Promise object enhanced with\n    // the jqXHR methods abort, success, error and complete:\n    _getXHRPromise: function (resolveOrReject, context, args) {\n      var dfd = $.Deferred(),\n        promise = dfd.promise();\n      // eslint-disable-next-line no-param-reassign\n      context = context || this.options.context || promise;\n      if (resolveOrReject === true) {\n        dfd.resolveWith(context, args);\n      } else if (resolveOrReject === false) {\n        dfd.rejectWith(context, args);\n      }\n      promise.abort = dfd.promise;\n      return this._enhancePromise(promise);\n    },\n\n    // Adds convenience methods to the data callback argument:\n    _addConvenienceMethods: function (e, data) {\n      var that = this,\n        getPromise = function (args) {\n          return $.Deferred().resolveWith(that, args).promise();\n        };\n      data.process = function (resolveFunc, rejectFunc) {\n        if (resolveFunc || rejectFunc) {\n          data._processQueue = this._processQueue = (this._processQueue ||\n            getPromise([this]))\n            [that._promisePipe](function () {\n              if (data.errorThrown) {\n                return $.Deferred().rejectWith(that, [data]).promise();\n              }\n              return getPromise(arguments);\n            })\n            [that._promisePipe](resolveFunc, rejectFunc);\n        }\n        return this._processQueue || getPromise([this]);\n      };\n      data.submit = function () {\n        if (this.state() !== 'pending') {\n          data.jqXHR = this.jqXHR =\n            that._trigger(\n              'submit',\n              $.Event('submit', { delegatedEvent: e }),\n              this\n            ) !== false && that._onSend(e, this);\n        }\n        return this.jqXHR || that._getXHRPromise();\n      };\n      data.abort = function () {\n        if (this.jqXHR) {\n          return this.jqXHR.abort();\n        }\n        this.errorThrown = 'abort';\n        that._trigger('fail', null, this);\n        return that._getXHRPromise(false);\n      };\n      data.state = function () {\n        if (this.jqXHR) {\n          return that._getDeferredState(this.jqXHR);\n        }\n        if (this._processQueue) {\n          return that._getDeferredState(this._processQueue);\n        }\n      };\n      data.processing = function () {\n        return (\n          !this.jqXHR &&\n          this._processQueue &&\n          that._getDeferredState(this._processQueue) === 'pending'\n        );\n      };\n      data.progress = function () {\n        return this._progress;\n      };\n      data.response = function () {\n        return this._response;\n      };\n    },\n\n    // Parses the Range header from the server response\n    // and returns the uploaded bytes:\n    _getUploadedBytes: function (jqXHR) {\n      var range = jqXHR.getResponseHeader('Range'),\n        parts = range && range.split('-'),\n        upperBytesPos = parts && parts.length > 1 && parseInt(parts[1], 10);\n      return upperBytesPos && upperBytesPos + 1;\n    },\n\n    // Uploads a file in multiple, sequential requests\n    // by splitting the file up in multiple blob chunks.\n    // If the second parameter is true, only tests if the file\n    // should be uploaded in chunks, but does not invoke any\n    // upload requests:\n    _chunkedUpload: function (options, testOnly) {\n      options.uploadedBytes = options.uploadedBytes || 0;\n      var that = this,\n        file = options.files[0],\n        fs = file.size,\n        ub = options.uploadedBytes,\n        mcs = options.maxChunkSize || fs,\n        slice = this._blobSlice,\n        dfd = $.Deferred(),\n        promise = dfd.promise(),\n        jqXHR,\n        upload;\n      if (\n        !(\n          this._isXHRUpload(options) &&\n          slice &&\n          (ub || ($.type(mcs) === 'function' ? mcs(options) : mcs) < fs)\n        ) ||\n        options.data\n      ) {\n        return false;\n      }\n      if (testOnly) {\n        return true;\n      }\n      if (ub >= fs) {\n        file.error = options.i18n('uploadedBytes');\n        return this._getXHRPromise(false, options.context, [\n          null,\n          'error',\n          file.error\n        ]);\n      }\n      // The chunk upload method:\n      upload = function () {\n        // Clone the options object for each chunk upload:\n        var o = $.extend({}, options),\n          currentLoaded = o._progress.loaded;\n        o.blob = slice.call(\n          file,\n          ub,\n          ub + ($.type(mcs) === 'function' ? mcs(o) : mcs),\n          file.type\n        );\n        // Store the current chunk size, as the blob itself\n        // will be dereferenced after data processing:\n        o.chunkSize = o.blob.size;\n        // Expose the chunk bytes position range:\n        o.contentRange =\n          'bytes ' + ub + '-' + (ub + o.chunkSize - 1) + '/' + fs;\n        // Trigger chunkbeforesend to allow form data to be updated for this chunk\n        that._trigger('chunkbeforesend', null, o);\n        // Process the upload data (the blob and potential form data):\n        that._initXHRData(o);\n        // Add progress listeners for this chunk upload:\n        that._initProgressListener(o);\n        jqXHR = (\n          (that._trigger('chunksend', null, o) !== false && $.ajax(o)) ||\n          that._getXHRPromise(false, o.context)\n        )\n          .done(function (result, textStatus, jqXHR) {\n            ub = that._getUploadedBytes(jqXHR) || ub + o.chunkSize;\n            // Create a progress event if no final progress event\n            // with loaded equaling total has been triggered\n            // for this chunk:\n            if (currentLoaded + o.chunkSize - o._progress.loaded) {\n              that._onProgress(\n                $.Event('progress', {\n                  lengthComputable: true,\n                  loaded: ub - o.uploadedBytes,\n                  total: ub - o.uploadedBytes\n                }),\n                o\n              );\n            }\n            options.uploadedBytes = o.uploadedBytes = ub;\n            o.result = result;\n            o.textStatus = textStatus;\n            o.jqXHR = jqXHR;\n            that._trigger('chunkdone', null, o);\n            that._trigger('chunkalways', null, o);\n            if (ub < fs) {\n              // File upload not yet complete,\n              // continue with the next chunk:\n              upload();\n            } else {\n              dfd.resolveWith(o.context, [result, textStatus, jqXHR]);\n            }\n          })\n          .fail(function (jqXHR, textStatus, errorThrown) {\n            o.jqXHR = jqXHR;\n            o.textStatus = textStatus;\n            o.errorThrown = errorThrown;\n            that._trigger('chunkfail', null, o);\n            that._trigger('chunkalways', null, o);\n            dfd.rejectWith(o.context, [jqXHR, textStatus, errorThrown]);\n          })\n          .always(function () {\n            that._deinitProgressListener(o);\n          });\n      };\n      this._enhancePromise(promise);\n      promise.abort = function () {\n        return jqXHR.abort();\n      };\n      upload();\n      return promise;\n    },\n\n    _beforeSend: function (e, data) {\n      if (this._active === 0) {\n        // the start callback is triggered when an upload starts\n        // and no other uploads are currently running,\n        // equivalent to the global ajaxStart event:\n        this._trigger('start');\n        // Set timer for global bitrate progress calculation:\n        this._bitrateTimer = new this._BitrateTimer();\n        // Reset the global progress values:\n        this._progress.loaded = this._progress.total = 0;\n        this._progress.bitrate = 0;\n      }\n      // Make sure the container objects for the .response() and\n      // .progress() methods on the data object are available\n      // and reset to their initial state:\n      this._initResponseObject(data);\n      this._initProgressObject(data);\n      data._progress.loaded = data.loaded = data.uploadedBytes || 0;\n      data._progress.total = data.total = this._getTotal(data.files) || 1;\n      data._progress.bitrate = data.bitrate = 0;\n      this._active += 1;\n      // Initialize the global progress values:\n      this._progress.loaded += data.loaded;\n      this._progress.total += data.total;\n    },\n\n    _onDone: function (result, textStatus, jqXHR, options) {\n      var total = options._progress.total,\n        response = options._response;\n      if (options._progress.loaded < total) {\n        // Create a progress event if no final progress event\n        // with loaded equaling total has been triggered:\n        this._onProgress(\n          $.Event('progress', {\n            lengthComputable: true,\n            loaded: total,\n            total: total\n          }),\n          options\n        );\n      }\n      response.result = options.result = result;\n      response.textStatus = options.textStatus = textStatus;\n      response.jqXHR = options.jqXHR = jqXHR;\n      this._trigger('done', null, options);\n    },\n\n    _onFail: function (jqXHR, textStatus, errorThrown, options) {\n      var response = options._response;\n      if (options.recalculateProgress) {\n        // Remove the failed (error or abort) file upload from\n        // the global progress calculation:\n        this._progress.loaded -= options._progress.loaded;\n        this._progress.total -= options._progress.total;\n      }\n      response.jqXHR = options.jqXHR = jqXHR;\n      response.textStatus = options.textStatus = textStatus;\n      response.errorThrown = options.errorThrown = errorThrown;\n      this._trigger('fail', null, options);\n    },\n\n    _onAlways: function (jqXHRorResult, textStatus, jqXHRorError, options) {\n      // jqXHRorResult, textStatus and jqXHRorError are added to the\n      // options object via done and fail callbacks\n      this._trigger('always', null, options);\n    },\n\n    _onSend: function (e, data) {\n      if (!data.submit) {\n        this._addConvenienceMethods(e, data);\n      }\n      var that = this,\n        jqXHR,\n        aborted,\n        slot,\n        pipe,\n        options = that._getAJAXSettings(data),\n        send = function () {\n          that._sending += 1;\n          // Set timer for bitrate progress calculation:\n          options._bitrateTimer = new that._BitrateTimer();\n          jqXHR =\n            jqXHR ||\n            (\n              ((aborted ||\n                that._trigger(\n                  'send',\n                  $.Event('send', { delegatedEvent: e }),\n                  options\n                ) === false) &&\n                that._getXHRPromise(false, options.context, aborted)) ||\n              that._chunkedUpload(options) ||\n              $.ajax(options)\n            )\n              .done(function (result, textStatus, jqXHR) {\n                that._onDone(result, textStatus, jqXHR, options);\n              })\n              .fail(function (jqXHR, textStatus, errorThrown) {\n                that._onFail(jqXHR, textStatus, errorThrown, options);\n              })\n              .always(function (jqXHRorResult, textStatus, jqXHRorError) {\n                that._deinitProgressListener(options);\n                that._onAlways(\n                  jqXHRorResult,\n                  textStatus,\n                  jqXHRorError,\n                  options\n                );\n                that._sending -= 1;\n                that._active -= 1;\n                if (\n                  options.limitConcurrentUploads &&\n                  options.limitConcurrentUploads > that._sending\n                ) {\n                  // Start the next queued upload,\n                  // that has not been aborted:\n                  var nextSlot = that._slots.shift();\n                  while (nextSlot) {\n                    if (that._getDeferredState(nextSlot) === 'pending') {\n                      nextSlot.resolve();\n                      break;\n                    }\n                    nextSlot = that._slots.shift();\n                  }\n                }\n                if (that._active === 0) {\n                  // The stop callback is triggered when all uploads have\n                  // been completed, equivalent to the global ajaxStop event:\n                  that._trigger('stop');\n                }\n              });\n          return jqXHR;\n        };\n      this._beforeSend(e, options);\n      if (\n        this.options.sequentialUploads ||\n        (this.options.limitConcurrentUploads &&\n          this.options.limitConcurrentUploads <= this._sending)\n      ) {\n        if (this.options.limitConcurrentUploads > 1) {\n          slot = $.Deferred();\n          this._slots.push(slot);\n          pipe = slot[that._promisePipe](send);\n        } else {\n          this._sequence = this._sequence[that._promisePipe](send, send);\n          pipe = this._sequence;\n        }\n        // Return the piped Promise object, enhanced with an abort method,\n        // which is delegated to the jqXHR object of the current upload,\n        // and jqXHR callbacks mapped to the equivalent Promise methods:\n        pipe.abort = function () {\n          aborted = [undefined, 'abort', 'abort'];\n          if (!jqXHR) {\n            if (slot) {\n              slot.rejectWith(options.context, aborted);\n            }\n            return send();\n          }\n          return jqXHR.abort();\n        };\n        return this._enhancePromise(pipe);\n      }\n      return send();\n    },\n\n    _onAdd: function (e, data) {\n      var that = this,\n        result = true,\n        options = $.extend({}, this.options, data),\n        files = data.files,\n        filesLength = files.length,\n        limit = options.limitMultiFileUploads,\n        limitSize = options.limitMultiFileUploadSize,\n        overhead = options.limitMultiFileUploadSizeOverhead,\n        batchSize = 0,\n        paramName = this._getParamName(options),\n        paramNameSet,\n        paramNameSlice,\n        fileSet,\n        i,\n        j = 0;\n      if (!filesLength) {\n        return false;\n      }\n      if (limitSize && files[0].size === undefined) {\n        limitSize = undefined;\n      }\n      if (\n        !(options.singleFileUploads || limit || limitSize) ||\n        !this._isXHRUpload(options)\n      ) {\n        fileSet = [files];\n        paramNameSet = [paramName];\n      } else if (!(options.singleFileUploads || limitSize) && limit) {\n        fileSet = [];\n        paramNameSet = [];\n        for (i = 0; i < filesLength; i += limit) {\n          fileSet.push(files.slice(i, i + limit));\n          paramNameSlice = paramName.slice(i, i + limit);\n          if (!paramNameSlice.length) {\n            paramNameSlice = paramName;\n          }\n          paramNameSet.push(paramNameSlice);\n        }\n      } else if (!options.singleFileUploads && limitSize) {\n        fileSet = [];\n        paramNameSet = [];\n        for (i = 0; i < filesLength; i = i + 1) {\n          batchSize += files[i].size + overhead;\n          if (\n            i + 1 === filesLength ||\n            batchSize + files[i + 1].size + overhead > limitSize ||\n            (limit && i + 1 - j >= limit)\n          ) {\n            fileSet.push(files.slice(j, i + 1));\n            paramNameSlice = paramName.slice(j, i + 1);\n            if (!paramNameSlice.length) {\n              paramNameSlice = paramName;\n            }\n            paramNameSet.push(paramNameSlice);\n            j = i + 1;\n            batchSize = 0;\n          }\n        }\n      } else {\n        paramNameSet = paramName;\n      }\n      data.originalFiles = files;\n      $.each(fileSet || files, function (index, element) {\n        var newData = $.extend({}, data);\n        newData.files = fileSet ? element : [element];\n        newData.paramName = paramNameSet[index];\n        that._initResponseObject(newData);\n        that._initProgressObject(newData);\n        that._addConvenienceMethods(e, newData);\n        result = that._trigger(\n          'add',\n          $.Event('add', { delegatedEvent: e }),\n          newData\n        );\n        return result;\n      });\n      return result;\n    },\n\n    _replaceFileInput: function (data) {\n      var input = data.fileInput,\n        inputClone = input.clone(true),\n        restoreFocus = input.is(document.activeElement);\n      // Add a reference for the new cloned file input to the data argument:\n      data.fileInputClone = inputClone;\n      $('<form></form>').append(inputClone)[0].reset();\n      // Detaching allows to insert the fileInput on another form\n      // without loosing the file input value:\n      input.after(inputClone).detach();\n      // If the fileInput had focus before it was detached,\n      // restore focus to the inputClone.\n      if (restoreFocus) {\n        inputClone.trigger('focus');\n      }\n      // Avoid memory leaks with the detached file input:\n      $.cleanData(input.off('remove'));\n      // Replace the original file input element in the fileInput\n      // elements set with the clone, which has been copied including\n      // event handlers:\n      this.options.fileInput = this.options.fileInput.map(function (i, el) {\n        if (el === input[0]) {\n          return inputClone[0];\n        }\n        return el;\n      });\n      // If the widget has been initialized on the file input itself,\n      // override this.element with the file input clone:\n      if (input[0] === this.element[0]) {\n        this.element = inputClone;\n      }\n    },\n\n    _handleFileTreeEntry: function (entry, path) {\n      var that = this,\n        dfd = $.Deferred(),\n        entries = [],\n        dirReader,\n        errorHandler = function (e) {\n          if (e && !e.entry) {\n            e.entry = entry;\n          }\n          // Since $.when returns immediately if one\n          // Deferred is rejected, we use resolve instead.\n          // This allows valid files and invalid items\n          // to be returned together in one set:\n          dfd.resolve([e]);\n        },\n        successHandler = function (entries) {\n          that\n            ._handleFileTreeEntries(entries, path + entry.name + '/')\n            .done(function (files) {\n              dfd.resolve(files);\n            })\n            .fail(errorHandler);\n        },\n        readEntries = function () {\n          dirReader.readEntries(function (results) {\n            if (!results.length) {\n              successHandler(entries);\n            } else {\n              entries = entries.concat(results);\n              readEntries();\n            }\n          }, errorHandler);\n        };\n      // eslint-disable-next-line no-param-reassign\n      path = path || '';\n      if (entry.isFile) {\n        if (entry._file) {\n          // Workaround for Chrome bug #149735\n          entry._file.relativePath = path;\n          dfd.resolve(entry._file);\n        } else {\n          entry.file(function (file) {\n            file.relativePath = path;\n            dfd.resolve(file);\n          }, errorHandler);\n        }\n      } else if (entry.isDirectory) {\n        dirReader = entry.createReader();\n        readEntries();\n      } else {\n        // Return an empty list for file system items\n        // other than files or directories:\n        dfd.resolve([]);\n      }\n      return dfd.promise();\n    },\n\n    _handleFileTreeEntries: function (entries, path) {\n      var that = this;\n      return $.when\n        .apply(\n          $,\n          $.map(entries, function (entry) {\n            return that._handleFileTreeEntry(entry, path);\n          })\n        )\n        [this._promisePipe](function () {\n          return Array.prototype.concat.apply([], arguments);\n        });\n    },\n\n    _getDroppedFiles: function (dataTransfer) {\n      // eslint-disable-next-line no-param-reassign\n      dataTransfer = dataTransfer || {};\n      var items = dataTransfer.items;\n      if (\n        items &&\n        items.length &&\n        (items[0].webkitGetAsEntry || items[0].getAsEntry)\n      ) {\n        return this._handleFileTreeEntries(\n          $.map(items, function (item) {\n            var entry;\n            if (item.webkitGetAsEntry) {\n              entry = item.webkitGetAsEntry();\n              if (entry) {\n                // Workaround for Chrome bug #149735:\n                entry._file = item.getAsFile();\n              }\n              return entry;\n            }\n            return item.getAsEntry();\n          })\n        );\n      }\n      return $.Deferred().resolve($.makeArray(dataTransfer.files)).promise();\n    },\n\n    _getSingleFileInputFiles: function (fileInput) {\n      // eslint-disable-next-line no-param-reassign\n      fileInput = $(fileInput);\n      var entries =\n          fileInput.prop('webkitEntries') || fileInput.prop('entries'),\n        files,\n        value;\n      if (entries && entries.length) {\n        return this._handleFileTreeEntries(entries);\n      }\n      files = $.makeArray(fileInput.prop('files'));\n      if (!files.length) {\n        value = fileInput.prop('value');\n        if (!value) {\n          return $.Deferred().resolve([]).promise();\n        }\n        // If the files property is not available, the browser does not\n        // support the File API and we add a pseudo File object with\n        // the input value as name with path information removed:\n        files = [{ name: value.replace(/^.*\\\\/, '') }];\n      } else if (files[0].name === undefined && files[0].fileName) {\n        // File normalization for Safari 4 and Firefox 3:\n        $.each(files, function (index, file) {\n          file.name = file.fileName;\n          file.size = file.fileSize;\n        });\n      }\n      return $.Deferred().resolve(files).promise();\n    },\n\n    _getFileInputFiles: function (fileInput) {\n      if (!(fileInput instanceof $) || fileInput.length === 1) {\n        return this._getSingleFileInputFiles(fileInput);\n      }\n      return $.when\n        .apply($, $.map(fileInput, this._getSingleFileInputFiles))\n        [this._promisePipe](function () {\n          return Array.prototype.concat.apply([], arguments);\n        });\n    },\n\n    _onChange: function (e) {\n      var that = this,\n        data = {\n          fileInput: $(e.target),\n          form: $(e.target.form)\n        };\n      this._getFileInputFiles(data.fileInput).always(function (files) {\n        data.files = files;\n        if (that.options.replaceFileInput) {\n          that._replaceFileInput(data);\n        }\n        if (\n          that._trigger(\n            'change',\n            $.Event('change', { delegatedEvent: e }),\n            data\n          ) !== false\n        ) {\n          that._onAdd(e, data);\n        }\n      });\n    },\n\n    _onPaste: function (e) {\n      var items =\n          e.originalEvent &&\n          e.originalEvent.clipboardData &&\n          e.originalEvent.clipboardData.items,\n        data = { files: [] };\n      if (items && items.length) {\n        $.each(items, function (index, item) {\n          var file = item.getAsFile && item.getAsFile();\n          if (file) {\n            data.files.push(file);\n          }\n        });\n        if (\n          this._trigger(\n            'paste',\n            $.Event('paste', { delegatedEvent: e }),\n            data\n          ) !== false\n        ) {\n          this._onAdd(e, data);\n        }\n      }\n    },\n\n    _onDrop: function (e) {\n      e.dataTransfer = e.originalEvent && e.originalEvent.dataTransfer;\n      var that = this,\n        dataTransfer = e.dataTransfer,\n        data = {};\n      if (dataTransfer && dataTransfer.files && dataTransfer.files.length) {\n        e.preventDefault();\n        this._getDroppedFiles(dataTransfer).always(function (files) {\n          data.files = files;\n          if (\n            that._trigger(\n              'drop',\n              $.Event('drop', { delegatedEvent: e }),\n              data\n            ) !== false\n          ) {\n            that._onAdd(e, data);\n          }\n        });\n      }\n    },\n\n    _onDragOver: getDragHandler('dragover'),\n\n    _onDragEnter: getDragHandler('dragenter'),\n\n    _onDragLeave: getDragHandler('dragleave'),\n\n    _initEventHandlers: function () {\n      if (this._isXHRUpload(this.options)) {\n        this._on(this.options.dropZone, {\n          dragover: this._onDragOver,\n          drop: this._onDrop,\n          // event.preventDefault() on dragenter is required for IE10+:\n          dragenter: this._onDragEnter,\n          // dragleave is not required, but added for completeness:\n          dragleave: this._onDragLeave\n        });\n        this._on(this.options.pasteZone, {\n          paste: this._onPaste\n        });\n      }\n      if ($.support.fileInput) {\n        this._on(this.options.fileInput, {\n          change: this._onChange\n        });\n      }\n    },\n\n    _destroyEventHandlers: function () {\n      this._off(this.options.dropZone, 'dragenter dragleave dragover drop');\n      this._off(this.options.pasteZone, 'paste');\n      this._off(this.options.fileInput, 'change');\n    },\n\n    _destroy: function () {\n      this._destroyEventHandlers();\n    },\n\n    _setOption: function (key, value) {\n      var reinit = $.inArray(key, this._specialOptions) !== -1;\n      if (reinit) {\n        this._destroyEventHandlers();\n      }\n      this._super(key, value);\n      if (reinit) {\n        this._initSpecialOptions();\n        this._initEventHandlers();\n      }\n    },\n\n    _initSpecialOptions: function () {\n      var options = this.options;\n      if (options.fileInput === undefined) {\n        options.fileInput = this.element.is('input[type=\"file\"]')\n          ? this.element\n          : this.element.find('input[type=\"file\"]');\n      } else if (!(options.fileInput instanceof $)) {\n        options.fileInput = $(options.fileInput);\n      }\n      if (!(options.dropZone instanceof $)) {\n        options.dropZone = $(options.dropZone);\n      }\n      if (!(options.pasteZone instanceof $)) {\n        options.pasteZone = $(options.pasteZone);\n      }\n    },\n\n    _getRegExp: function (str) {\n      var parts = str.split('/'),\n        modifiers = parts.pop();\n      parts.shift();\n      return new RegExp(parts.join('/'), modifiers);\n    },\n\n    _isRegExpOption: function (key, value) {\n      return (\n        key !== 'url' &&\n        $.type(value) === 'string' &&\n        /^\\/.*\\/[igm]{0,3}$/.test(value)\n      );\n    },\n\n    _initDataAttributes: function () {\n      var that = this,\n        options = this.options,\n        data = this.element.data();\n      // Initialize options set via HTML5 data-attributes:\n      $.each(this.element[0].attributes, function (index, attr) {\n        var key = attr.name.toLowerCase(),\n          value;\n        if (/^data-/.test(key)) {\n          // Convert hyphen-ated key to camelCase:\n          key = key.slice(5).replace(/-[a-z]/g, function (str) {\n            return str.charAt(1).toUpperCase();\n          });\n          value = data[key];\n          if (that._isRegExpOption(key, value)) {\n            value = that._getRegExp(value);\n          }\n          options[key] = value;\n        }\n      });\n    },\n\n    _create: function () {\n      this._initDataAttributes();\n      this._initSpecialOptions();\n      this._slots = [];\n      this._sequence = this._getXHRPromise(true);\n      this._sending = this._active = 0;\n      this._initProgressObject(this);\n      this._initEventHandlers();\n    },\n\n    // This method is exposed to the widget API and allows to query\n    // the number of active uploads:\n    active: function () {\n      return this._active;\n    },\n\n    // This method is exposed to the widget API and allows to query\n    // the widget upload progress.\n    // It returns an object with loaded, total and bitrate properties\n    // for the running uploads:\n    progress: function () {\n      return this._progress;\n    },\n\n    // This method is exposed to the widget API and allows adding files\n    // using the fileupload API. The data parameter accepts an object which\n    // must have a files property and can contain additional options:\n    // .fileupload('add', {files: filesList});\n    add: function (data) {\n      var that = this;\n      if (!data || this.options.disabled) {\n        return;\n      }\n      if (data.fileInput && !data.files) {\n        this._getFileInputFiles(data.fileInput).always(function (files) {\n          data.files = files;\n          that._onAdd(null, data);\n        });\n      } else {\n        data.files = $.makeArray(data.files);\n        this._onAdd(null, data);\n      }\n    },\n\n    // This method is exposed to the widget API and allows sending files\n    // using the fileupload API. The data parameter accepts an object which\n    // must have a files or fileInput property and can contain additional options:\n    // .fileupload('send', {files: filesList});\n    // The method returns a Promise object for the file upload call.\n    send: function (data) {\n      if (data && !this.options.disabled) {\n        if (data.fileInput && !data.files) {\n          var that = this,\n            dfd = $.Deferred(),\n            promise = dfd.promise(),\n            jqXHR,\n            aborted;\n          promise.abort = function () {\n            aborted = true;\n            if (jqXHR) {\n              return jqXHR.abort();\n            }\n            dfd.reject(null, 'abort', 'abort');\n            return promise;\n          };\n          this._getFileInputFiles(data.fileInput).always(function (files) {\n            if (aborted) {\n              return;\n            }\n            if (!files.length) {\n              dfd.reject();\n              return;\n            }\n            data.files = files;\n            jqXHR = that._onSend(null, data);\n            jqXHR.then(\n              function (result, textStatus, jqXHR) {\n                dfd.resolve(result, textStatus, jqXHR);\n              },\n              function (jqXHR, textStatus, errorThrown) {\n                dfd.reject(jqXHR, textStatus, errorThrown);\n              }\n            );\n          });\n          return this._enhancePromise(promise);\n        }\n        data.files = $.makeArray(data.files);\n        if (data.files.length) {\n          return this._onSend(null, data);\n        }\n      }\n      return this._getXHRPromise(false, data && data.context);\n    }\n  });\n});\n"

/***/ }),

/***/ 503:
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(4)(__webpack_require__(504))

/***/ }),

/***/ 504:
/***/ (function(module, exports) {

module.exports = "/*\n * jQuery Iframe Transport Plugin\n * https://github.com/blueimp/jQuery-File-Upload\n *\n * Copyright 2011, Sebastian Tschan\n * https://blueimp.net\n *\n * Licensed under the MIT license:\n * https://opensource.org/licenses/MIT\n */\n\n/* global define, require */\n\n(function (factory) {\n  'use strict';\n  if (typeof define === 'function' && define.amd) {\n    // Register as an anonymous AMD module:\n    define(['jquery'], factory);\n  } else if (typeof exports === 'object') {\n    // Node/CommonJS:\n    factory(require('jquery'));\n  } else {\n    // Browser globals:\n    factory(window.jQuery);\n  }\n})(function ($) {\n  'use strict';\n\n  // Helper variable to create unique names for the transport iframes:\n  var counter = 0,\n    jsonAPI = $,\n    jsonParse = 'parseJSON';\n\n  if ('JSON' in window && 'parse' in JSON) {\n    jsonAPI = JSON;\n    jsonParse = 'parse';\n  }\n\n  // The iframe transport accepts four additional options:\n  // options.fileInput: a jQuery collection of file input fields\n  // options.paramName: the parameter name for the file form data,\n  //  overrides the name property of the file input field(s),\n  //  can be a string or an array of strings.\n  // options.formData: an array of objects with name and value properties,\n  //  equivalent to the return data of .serializeArray(), e.g.:\n  //  [{name: 'a', value: 1}, {name: 'b', value: 2}]\n  // options.initialIframeSrc: the URL of the initial iframe src,\n  //  by default set to \"javascript:false;\"\n  $.ajaxTransport('iframe', function (options) {\n    if (options.async) {\n      // javascript:false as initial iframe src\n      // prevents warning popups on HTTPS in IE6:\n      // eslint-disable-next-line no-script-url\n      var initialIframeSrc = options.initialIframeSrc || 'javascript:false;',\n        form,\n        iframe,\n        addParamChar;\n      return {\n        send: function (_, completeCallback) {\n          form = $('<form style=\"display:none;\"></form>');\n          form.attr('accept-charset', options.formAcceptCharset);\n          addParamChar = /\\?/.test(options.url) ? '&' : '?';\n          // XDomainRequest only supports GET and POST:\n          if (options.type === 'DELETE') {\n            options.url = options.url + addParamChar + '_method=DELETE';\n            options.type = 'POST';\n          } else if (options.type === 'PUT') {\n            options.url = options.url + addParamChar + '_method=PUT';\n            options.type = 'POST';\n          } else if (options.type === 'PATCH') {\n            options.url = options.url + addParamChar + '_method=PATCH';\n            options.type = 'POST';\n          }\n          // IE versions below IE8 cannot set the name property of\n          // elements that have already been added to the DOM,\n          // so we set the name along with the iframe HTML markup:\n          counter += 1;\n          iframe = $(\n            '<iframe src=\"' +\n              initialIframeSrc +\n              '\" name=\"iframe-transport-' +\n              counter +\n              '\"></iframe>'\n          ).on('load', function () {\n            var fileInputClones,\n              paramNames = $.isArray(options.paramName)\n                ? options.paramName\n                : [options.paramName];\n            iframe.off('load').on('load', function () {\n              var response;\n              // Wrap in a try/catch block to catch exceptions thrown\n              // when trying to access cross-domain iframe contents:\n              try {\n                response = iframe.contents();\n                // Google Chrome and Firefox do not throw an\n                // exception when calling iframe.contents() on\n                // cross-domain requests, so we unify the response:\n                if (!response.length || !response[0].firstChild) {\n                  throw new Error();\n                }\n              } catch (e) {\n                response = undefined;\n              }\n              // The complete callback returns the\n              // iframe content document as response object:\n              completeCallback(200, 'success', { iframe: response });\n              // Fix for IE endless progress bar activity bug\n              // (happens on form submits to iframe targets):\n              $('<iframe src=\"' + initialIframeSrc + '\"></iframe>').appendTo(\n                form\n              );\n              window.setTimeout(function () {\n                // Removing the form in a setTimeout call\n                // allows Chrome's developer tools to display\n                // the response result\n                form.remove();\n              }, 0);\n            });\n            form\n              .prop('target', iframe.prop('name'))\n              .prop('action', options.url)\n              .prop('method', options.type);\n            if (options.formData) {\n              $.each(options.formData, function (index, field) {\n                $('<input type=\"hidden\"/>')\n                  .prop('name', field.name)\n                  .val(field.value)\n                  .appendTo(form);\n              });\n            }\n            if (\n              options.fileInput &&\n              options.fileInput.length &&\n              options.type === 'POST'\n            ) {\n              fileInputClones = options.fileInput.clone();\n              // Insert a clone for each file input field:\n              options.fileInput.after(function (index) {\n                return fileInputClones[index];\n              });\n              if (options.paramName) {\n                options.fileInput.each(function (index) {\n                  $(this).prop('name', paramNames[index] || options.paramName);\n                });\n              }\n              // Appending the file input fields to the hidden form\n              // removes them from their original location:\n              form\n                .append(options.fileInput)\n                .prop('enctype', 'multipart/form-data')\n                // enctype must be set as encoding for IE:\n                .prop('encoding', 'multipart/form-data');\n              // Remove the HTML5 form attribute from the input(s):\n              options.fileInput.removeAttr('form');\n            }\n            window.setTimeout(function () {\n              // Submitting the form in a setTimeout call fixes an issue with\n              // Safari 13 not triggering the iframe load event after resetting\n              // the load event handler, see also:\n              // https://github.com/blueimp/jQuery-File-Upload/issues/3633\n              form.submit();\n              // Insert the file input fields at their original location\n              // by replacing the clones with the originals:\n              if (fileInputClones && fileInputClones.length) {\n                options.fileInput.each(function (index, input) {\n                  var clone = $(fileInputClones[index]);\n                  // Restore the original name and form properties:\n                  $(input)\n                    .prop('name', clone.prop('name'))\n                    .attr('form', clone.attr('form'));\n                  clone.replaceWith(input);\n                });\n              }\n            }, 0);\n          });\n          form.append(iframe).appendTo(document.body);\n        },\n        abort: function () {\n          if (iframe) {\n            // javascript:false as iframe src aborts the request\n            // and prevents warning popups on HTTPS in IE6.\n            iframe.off('load').prop('src', initialIframeSrc);\n          }\n          if (form) {\n            form.remove();\n          }\n        }\n      };\n    }\n  });\n\n  // The iframe transport returns the iframe content document as response.\n  // The following adds converters from iframe to text, json, html, xml\n  // and script.\n  // Please note that the Content-Type for JSON responses has to be text/plain\n  // or text/html, if the browser doesn't include application/json in the\n  // Accept header, else IE will show a download dialog.\n  // The Content-Type for XML responses on the other hand has to be always\n  // application/xml or text/xml, so IE properly parses the XML response.\n  // See also\n  // https://github.com/blueimp/jQuery-File-Upload/wiki/Setup#content-type-negotiation\n  $.ajaxSetup({\n    converters: {\n      'iframe text': function (iframe) {\n        return iframe && $(iframe[0].body).text();\n      },\n      'iframe json': function (iframe) {\n        return iframe && jsonAPI[jsonParse]($(iframe[0].body).text());\n      },\n      'iframe html': function (iframe) {\n        return iframe && $(iframe[0].body).html();\n      },\n      'iframe xml': function (iframe) {\n        var xmlDoc = iframe && iframe[0];\n        return xmlDoc && $.isXMLDoc(xmlDoc)\n          ? xmlDoc\n          : $.parseXML(\n              (xmlDoc.XMLDocument && xmlDoc.XMLDocument.xml) ||\n                $(xmlDoc.body).html()\n            );\n      },\n      'iframe script': function (iframe) {\n        return iframe && $.globalEval($(iframe[0].body).text());\n      }\n    }\n  });\n});\n"

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
//# sourceMappingURL=jquery-file-upload.js.map