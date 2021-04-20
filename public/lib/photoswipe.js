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
/******/ 	return __webpack_require__(__webpack_require__.s = 534);
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

/***/ 534:
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

// Photoswipe lib
__webpack_require__(535);
__webpack_require__(537);

__webpack_require__(539);
__webpack_require__(540);


/***/ }),

/***/ 535:
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(4)(__webpack_require__(536))

/***/ }),

/***/ 536:
/***/ (function(module, exports) {

module.exports = "/*! PhotoSwipe - v4.1.3 - 2019-01-08\n* http://photoswipe.com\n* Copyright (c) 2019 Dmitry Semenov; */\n(function (root, factory) { \n\tif (typeof define === 'function' && define.amd) {\n\t\tdefine(factory);\n\t} else if (typeof exports === 'object') {\n\t\tmodule.exports = factory();\n\t} else {\n\t\troot.PhotoSwipe = factory();\n\t}\n})(this, function () {\n\n\t'use strict';\n\tvar PhotoSwipe = function(template, UiClass, items, options){\n\n/*>>framework-bridge*/\n/**\n *\n * Set of generic functions used by gallery.\n * \n * You're free to modify anything here as long as functionality is kept.\n * \n */\nvar framework = {\n\tfeatures: null,\n\tbind: function(target, type, listener, unbind) {\n\t\tvar methodName = (unbind ? 'remove' : 'add') + 'EventListener';\n\t\ttype = type.split(' ');\n\t\tfor(var i = 0; i < type.length; i++) {\n\t\t\tif(type[i]) {\n\t\t\t\ttarget[methodName]( type[i], listener, false);\n\t\t\t}\n\t\t}\n\t},\n\tisArray: function(obj) {\n\t\treturn (obj instanceof Array);\n\t},\n\tcreateEl: function(classes, tag) {\n\t\tvar el = document.createElement(tag || 'div');\n\t\tif(classes) {\n\t\t\tel.className = classes;\n\t\t}\n\t\treturn el;\n\t},\n\tgetScrollY: function() {\n\t\tvar yOffset = window.pageYOffset;\n\t\treturn yOffset !== undefined ? yOffset : document.documentElement.scrollTop;\n\t},\n\tunbind: function(target, type, listener) {\n\t\tframework.bind(target,type,listener,true);\n\t},\n\tremoveClass: function(el, className) {\n\t\tvar reg = new RegExp('(\\\\s|^)' + className + '(\\\\s|$)');\n\t\tel.className = el.className.replace(reg, ' ').replace(/^\\s\\s*/, '').replace(/\\s\\s*$/, ''); \n\t},\n\taddClass: function(el, className) {\n\t\tif( !framework.hasClass(el,className) ) {\n\t\t\tel.className += (el.className ? ' ' : '') + className;\n\t\t}\n\t},\n\thasClass: function(el, className) {\n\t\treturn el.className && new RegExp('(^|\\\\s)' + className + '(\\\\s|$)').test(el.className);\n\t},\n\tgetChildByClass: function(parentEl, childClassName) {\n\t\tvar node = parentEl.firstChild;\n\t\twhile(node) {\n\t\t\tif( framework.hasClass(node, childClassName) ) {\n\t\t\t\treturn node;\n\t\t\t}\n\t\t\tnode = node.nextSibling;\n\t\t}\n\t},\n\tarraySearch: function(array, value, key) {\n\t\tvar i = array.length;\n\t\twhile(i--) {\n\t\t\tif(array[i][key] === value) {\n\t\t\t\treturn i;\n\t\t\t} \n\t\t}\n\t\treturn -1;\n\t},\n\textend: function(o1, o2, preventOverwrite) {\n\t\tfor (var prop in o2) {\n\t\t\tif (o2.hasOwnProperty(prop)) {\n\t\t\t\tif(preventOverwrite && o1.hasOwnProperty(prop)) {\n\t\t\t\t\tcontinue;\n\t\t\t\t}\n\t\t\t\to1[prop] = o2[prop];\n\t\t\t}\n\t\t}\n\t},\n\teasing: {\n\t\tsine: {\n\t\t\tout: function(k) {\n\t\t\t\treturn Math.sin(k * (Math.PI / 2));\n\t\t\t},\n\t\t\tinOut: function(k) {\n\t\t\t\treturn - (Math.cos(Math.PI * k) - 1) / 2;\n\t\t\t}\n\t\t},\n\t\tcubic: {\n\t\t\tout: function(k) {\n\t\t\t\treturn --k * k * k + 1;\n\t\t\t}\n\t\t}\n\t\t/*\n\t\t\telastic: {\n\t\t\t\tout: function ( k ) {\n\n\t\t\t\t\tvar s, a = 0.1, p = 0.4;\n\t\t\t\t\tif ( k === 0 ) return 0;\n\t\t\t\t\tif ( k === 1 ) return 1;\n\t\t\t\t\tif ( !a || a < 1 ) { a = 1; s = p / 4; }\n\t\t\t\t\telse s = p * Math.asin( 1 / a ) / ( 2 * Math.PI );\n\t\t\t\t\treturn ( a * Math.pow( 2, - 10 * k) * Math.sin( ( k - s ) * ( 2 * Math.PI ) / p ) + 1 );\n\n\t\t\t\t},\n\t\t\t},\n\t\t\tback: {\n\t\t\t\tout: function ( k ) {\n\t\t\t\t\tvar s = 1.70158;\n\t\t\t\t\treturn --k * k * ( ( s + 1 ) * k + s ) + 1;\n\t\t\t\t}\n\t\t\t}\n\t\t*/\n\t},\n\n\t/**\n\t * \n\t * @return {object}\n\t * \n\t * {\n\t *  raf : request animation frame function\n\t *  caf : cancel animation frame function\n\t *  transfrom : transform property key (with vendor), or null if not supported\n\t *  oldIE : IE8 or below\n\t * }\n\t * \n\t */\n\tdetectFeatures: function() {\n\t\tif(framework.features) {\n\t\t\treturn framework.features;\n\t\t}\n\t\tvar helperEl = framework.createEl(),\n\t\t\thelperStyle = helperEl.style,\n\t\t\tvendor = '',\n\t\t\tfeatures = {};\n\n\t\t// IE8 and below\n\t\tfeatures.oldIE = document.all && !document.addEventListener;\n\n\t\tfeatures.touch = 'ontouchstart' in window;\n\n\t\tif(window.requestAnimationFrame) {\n\t\t\tfeatures.raf = window.requestAnimationFrame;\n\t\t\tfeatures.caf = window.cancelAnimationFrame;\n\t\t}\n\n\t\tfeatures.pointerEvent = !!(window.PointerEvent) || navigator.msPointerEnabled;\n\n\t\t// fix false-positive detection of old Android in new IE\n\t\t// (IE11 ua string contains \"Android 4.0\")\n\t\t\n\t\tif(!features.pointerEvent) { \n\n\t\t\tvar ua = navigator.userAgent;\n\n\t\t\t// Detect if device is iPhone or iPod and if it's older than iOS 8\n\t\t\t// http://stackoverflow.com/a/14223920\n\t\t\t// \n\t\t\t// This detection is made because of buggy top/bottom toolbars\n\t\t\t// that don't trigger window.resize event.\n\t\t\t// For more info refer to _isFixedPosition variable in core.js\n\n\t\t\tif (/iP(hone|od)/.test(navigator.platform)) {\n\t\t\t\tvar v = (navigator.appVersion).match(/OS (\\d+)_(\\d+)_?(\\d+)?/);\n\t\t\t\tif(v && v.length > 0) {\n\t\t\t\t\tv = parseInt(v[1], 10);\n\t\t\t\t\tif(v >= 1 && v < 8 ) {\n\t\t\t\t\t\tfeatures.isOldIOSPhone = true;\n\t\t\t\t\t}\n\t\t\t\t}\n\t\t\t}\n\n\t\t\t// Detect old Android (before KitKat)\n\t\t\t// due to bugs related to position:fixed\n\t\t\t// http://stackoverflow.com/questions/7184573/pick-up-the-android-version-in-the-browser-by-javascript\n\t\t\t\n\t\t\tvar match = ua.match(/Android\\s([0-9\\.]*)/);\n\t\t\tvar androidversion =  match ? match[1] : 0;\n\t\t\tandroidversion = parseFloat(androidversion);\n\t\t\tif(androidversion >= 1 ) {\n\t\t\t\tif(androidversion < 4.4) {\n\t\t\t\t\tfeatures.isOldAndroid = true; // for fixed position bug & performance\n\t\t\t\t}\n\t\t\t\tfeatures.androidVersion = androidversion; // for touchend bug\n\t\t\t}\t\n\t\t\tfeatures.isMobileOpera = /opera mini|opera mobi/i.test(ua);\n\n\t\t\t// p.s. yes, yes, UA sniffing is bad, propose your solution for above bugs.\n\t\t}\n\t\t\n\t\tvar styleChecks = ['transform', 'perspective', 'animationName'],\n\t\t\tvendors = ['', 'webkit','Moz','ms','O'],\n\t\t\tstyleCheckItem,\n\t\t\tstyleName;\n\n\t\tfor(var i = 0; i < 4; i++) {\n\t\t\tvendor = vendors[i];\n\n\t\t\tfor(var a = 0; a < 3; a++) {\n\t\t\t\tstyleCheckItem = styleChecks[a];\n\n\t\t\t\t// uppercase first letter of property name, if vendor is present\n\t\t\t\tstyleName = vendor + (vendor ? \n\t\t\t\t\t\t\t\t\t\tstyleCheckItem.charAt(0).toUpperCase() + styleCheckItem.slice(1) : \n\t\t\t\t\t\t\t\t\t\tstyleCheckItem);\n\t\t\t\n\t\t\t\tif(!features[styleCheckItem] && styleName in helperStyle ) {\n\t\t\t\t\tfeatures[styleCheckItem] = styleName;\n\t\t\t\t}\n\t\t\t}\n\n\t\t\tif(vendor && !features.raf) {\n\t\t\t\tvendor = vendor.toLowerCase();\n\t\t\t\tfeatures.raf = window[vendor+'RequestAnimationFrame'];\n\t\t\t\tif(features.raf) {\n\t\t\t\t\tfeatures.caf = window[vendor+'CancelAnimationFrame'] || \n\t\t\t\t\t\t\t\t\twindow[vendor+'CancelRequestAnimationFrame'];\n\t\t\t\t}\n\t\t\t}\n\t\t}\n\t\t\t\n\t\tif(!features.raf) {\n\t\t\tvar lastTime = 0;\n\t\t\tfeatures.raf = function(fn) {\n\t\t\t\tvar currTime = new Date().getTime();\n\t\t\t\tvar timeToCall = Math.max(0, 16 - (currTime - lastTime));\n\t\t\t\tvar id = window.setTimeout(function() { fn(currTime + timeToCall); }, timeToCall);\n\t\t\t\tlastTime = currTime + timeToCall;\n\t\t\t\treturn id;\n\t\t\t};\n\t\t\tfeatures.caf = function(id) { clearTimeout(id); };\n\t\t}\n\n\t\t// Detect SVG support\n\t\tfeatures.svg = !!document.createElementNS && \n\t\t\t\t\t\t!!document.createElementNS('http://www.w3.org/2000/svg', 'svg').createSVGRect;\n\n\t\tframework.features = features;\n\n\t\treturn features;\n\t}\n};\n\nframework.detectFeatures();\n\n// Override addEventListener for old versions of IE\nif(framework.features.oldIE) {\n\n\tframework.bind = function(target, type, listener, unbind) {\n\t\t\n\t\ttype = type.split(' ');\n\n\t\tvar methodName = (unbind ? 'detach' : 'attach') + 'Event',\n\t\t\tevName,\n\t\t\t_handleEv = function() {\n\t\t\t\tlistener.handleEvent.call(listener);\n\t\t\t};\n\n\t\tfor(var i = 0; i < type.length; i++) {\n\t\t\tevName = type[i];\n\t\t\tif(evName) {\n\n\t\t\t\tif(typeof listener === 'object' && listener.handleEvent) {\n\t\t\t\t\tif(!unbind) {\n\t\t\t\t\t\tlistener['oldIE' + evName] = _handleEv;\n\t\t\t\t\t} else {\n\t\t\t\t\t\tif(!listener['oldIE' + evName]) {\n\t\t\t\t\t\t\treturn false;\n\t\t\t\t\t\t}\n\t\t\t\t\t}\n\n\t\t\t\t\ttarget[methodName]( 'on' + evName, listener['oldIE' + evName]);\n\t\t\t\t} else {\n\t\t\t\t\ttarget[methodName]( 'on' + evName, listener);\n\t\t\t\t}\n\n\t\t\t}\n\t\t}\n\t};\n\t\n}\n\n/*>>framework-bridge*/\n\n/*>>core*/\n//function(template, UiClass, items, options)\n\nvar self = this;\n\n/**\n * Static vars, don't change unless you know what you're doing.\n */\nvar DOUBLE_TAP_RADIUS = 25, \n\tNUM_HOLDERS = 3;\n\n/**\n * Options\n */\nvar _options = {\n\tallowPanToNext:true,\n\tspacing: 0.12,\n\tbgOpacity: 1,\n\tmouseUsed: false,\n\tloop: true,\n\tpinchToClose: true,\n\tcloseOnScroll: true,\n\tcloseOnVerticalDrag: true,\n\tverticalDragRange: 0.75,\n\thideAnimationDuration: 333,\n\tshowAnimationDuration: 333,\n\tshowHideOpacity: false,\n\tfocus: true,\n\tescKey: true,\n\tarrowKeys: true,\n\tmainScrollEndFriction: 0.35,\n\tpanEndFriction: 0.35,\n\tisClickableElement: function(el) {\n        return el.tagName === 'A';\n    },\n    getDoubleTapZoom: function(isMouseClick, item) {\n    \tif(isMouseClick) {\n    \t\treturn 1;\n    \t} else {\n    \t\treturn item.initialZoomLevel < 0.7 ? 1 : 1.33;\n    \t}\n    },\n    maxSpreadZoom: 1.33,\n\tmodal: true,\n\n\t// not fully implemented yet\n\tscaleMode: 'fit' // TODO\n};\nframework.extend(_options, options);\n\n\n/**\n * Private helper variables & functions\n */\n\nvar _getEmptyPoint = function() { \n\t\treturn {x:0,y:0}; \n\t};\n\nvar _isOpen,\n\t_isDestroying,\n\t_closedByScroll,\n\t_currentItemIndex,\n\t_containerStyle,\n\t_containerShiftIndex,\n\t_currPanDist = _getEmptyPoint(),\n\t_startPanOffset = _getEmptyPoint(),\n\t_panOffset = _getEmptyPoint(),\n\t_upMoveEvents, // drag move, drag end & drag cancel events array\n\t_downEvents, // drag start events array\n\t_globalEventHandlers,\n\t_viewportSize = {},\n\t_currZoomLevel,\n\t_startZoomLevel,\n\t_translatePrefix,\n\t_translateSufix,\n\t_updateSizeInterval,\n\t_itemsNeedUpdate,\n\t_currPositionIndex = 0,\n\t_offset = {},\n\t_slideSize = _getEmptyPoint(), // size of slide area, including spacing\n\t_itemHolders,\n\t_prevItemIndex,\n\t_indexDiff = 0, // difference of indexes since last content update\n\t_dragStartEvent,\n\t_dragMoveEvent,\n\t_dragEndEvent,\n\t_dragCancelEvent,\n\t_transformKey,\n\t_pointerEventEnabled,\n\t_isFixedPosition = true,\n\t_likelyTouchDevice,\n\t_modules = [],\n\t_requestAF,\n\t_cancelAF,\n\t_initalClassName,\n\t_initalWindowScrollY,\n\t_oldIE,\n\t_currentWindowScrollY,\n\t_features,\n\t_windowVisibleSize = {},\n\t_renderMaxResolution = false,\n\t_orientationChangeTimeout,\n\n\n\t// Registers PhotoSWipe module (History, Controller ...)\n\t_registerModule = function(name, module) {\n\t\tframework.extend(self, module.publicMethods);\n\t\t_modules.push(name);\n\t},\n\n\t_getLoopedId = function(index) {\n\t\tvar numSlides = _getNumItems();\n\t\tif(index > numSlides - 1) {\n\t\t\treturn index - numSlides;\n\t\t} else  if(index < 0) {\n\t\t\treturn numSlides + index;\n\t\t}\n\t\treturn index;\n\t},\n\t\n\t// Micro bind/trigger\n\t_listeners = {},\n\t_listen = function(name, fn) {\n\t\tif(!_listeners[name]) {\n\t\t\t_listeners[name] = [];\n\t\t}\n\t\treturn _listeners[name].push(fn);\n\t},\n\t_shout = function(name) {\n\t\tvar listeners = _listeners[name];\n\n\t\tif(listeners) {\n\t\t\tvar args = Array.prototype.slice.call(arguments);\n\t\t\targs.shift();\n\n\t\t\tfor(var i = 0; i < listeners.length; i++) {\n\t\t\t\tlisteners[i].apply(self, args);\n\t\t\t}\n\t\t}\n\t},\n\n\t_getCurrentTime = function() {\n\t\treturn new Date().getTime();\n\t},\n\t_applyBgOpacity = function(opacity) {\n\t\t_bgOpacity = opacity;\n\t\tself.bg.style.opacity = opacity * _options.bgOpacity;\n\t},\n\n\t_applyZoomTransform = function(styleObj,x,y,zoom,item) {\n\t\tif(!_renderMaxResolution || (item && item !== self.currItem) ) {\n\t\t\tzoom = zoom / (item ? item.fitRatio : self.currItem.fitRatio);\t\n\t\t}\n\t\t\t\n\t\tstyleObj[_transformKey] = _translatePrefix + x + 'px, ' + y + 'px' + _translateSufix + ' scale(' + zoom + ')';\n\t},\n\t_applyCurrentZoomPan = function( allowRenderResolution ) {\n\t\tif(_currZoomElementStyle) {\n\n\t\t\tif(allowRenderResolution) {\n\t\t\t\tif(_currZoomLevel > self.currItem.fitRatio) {\n\t\t\t\t\tif(!_renderMaxResolution) {\n\t\t\t\t\t\t_setImageSize(self.currItem, false, true);\n\t\t\t\t\t\t_renderMaxResolution = true;\n\t\t\t\t\t}\n\t\t\t\t} else {\n\t\t\t\t\tif(_renderMaxResolution) {\n\t\t\t\t\t\t_setImageSize(self.currItem);\n\t\t\t\t\t\t_renderMaxResolution = false;\n\t\t\t\t\t}\n\t\t\t\t}\n\t\t\t}\n\t\t\t\n\n\t\t\t_applyZoomTransform(_currZoomElementStyle, _panOffset.x, _panOffset.y, _currZoomLevel);\n\t\t}\n\t},\n\t_applyZoomPanToItem = function(item) {\n\t\tif(item.container) {\n\n\t\t\t_applyZoomTransform(item.container.style, \n\t\t\t\t\t\t\t\titem.initialPosition.x, \n\t\t\t\t\t\t\t\titem.initialPosition.y, \n\t\t\t\t\t\t\t\titem.initialZoomLevel,\n\t\t\t\t\t\t\t\titem);\n\t\t}\n\t},\n\t_setTranslateX = function(x, elStyle) {\n\t\telStyle[_transformKey] = _translatePrefix + x + 'px, 0px' + _translateSufix;\n\t},\n\t_moveMainScroll = function(x, dragging) {\n\n\t\tif(!_options.loop && dragging) {\n\t\t\tvar newSlideIndexOffset = _currentItemIndex + (_slideSize.x * _currPositionIndex - x) / _slideSize.x,\n\t\t\t\tdelta = Math.round(x - _mainScrollPos.x);\n\n\t\t\tif( (newSlideIndexOffset < 0 && delta > 0) || \n\t\t\t\t(newSlideIndexOffset >= _getNumItems() - 1 && delta < 0) ) {\n\t\t\t\tx = _mainScrollPos.x + delta * _options.mainScrollEndFriction;\n\t\t\t} \n\t\t}\n\t\t\n\t\t_mainScrollPos.x = x;\n\t\t_setTranslateX(x, _containerStyle);\n\t},\n\t_calculatePanOffset = function(axis, zoomLevel) {\n\t\tvar m = _midZoomPoint[axis] - _offset[axis];\n\t\treturn _startPanOffset[axis] + _currPanDist[axis] + m - m * ( zoomLevel / _startZoomLevel );\n\t},\n\t\n\t_equalizePoints = function(p1, p2) {\n\t\tp1.x = p2.x;\n\t\tp1.y = p2.y;\n\t\tif(p2.id) {\n\t\t\tp1.id = p2.id;\n\t\t}\n\t},\n\t_roundPoint = function(p) {\n\t\tp.x = Math.round(p.x);\n\t\tp.y = Math.round(p.y);\n\t},\n\n\t_mouseMoveTimeout = null,\n\t_onFirstMouseMove = function() {\n\t\t// Wait until mouse move event is fired at least twice during 100ms\n\t\t// We do this, because some mobile browsers trigger it on touchstart\n\t\tif(_mouseMoveTimeout ) { \n\t\t\tframework.unbind(document, 'mousemove', _onFirstMouseMove);\n\t\t\tframework.addClass(template, 'pswp--has_mouse');\n\t\t\t_options.mouseUsed = true;\n\t\t\t_shout('mouseUsed');\n\t\t}\n\t\t_mouseMoveTimeout = setTimeout(function() {\n\t\t\t_mouseMoveTimeout = null;\n\t\t}, 100);\n\t},\n\n\t_bindEvents = function() {\n\t\tframework.bind(document, 'keydown', self);\n\n\t\tif(_features.transform) {\n\t\t\t// don't bind click event in browsers that don't support transform (mostly IE8)\n\t\t\tframework.bind(self.scrollWrap, 'click', self);\n\t\t}\n\t\t\n\n\t\tif(!_options.mouseUsed) {\n\t\t\tframework.bind(document, 'mousemove', _onFirstMouseMove);\n\t\t}\n\n\t\tframework.bind(window, 'resize scroll orientationchange', self);\n\n\t\t_shout('bindEvents');\n\t},\n\n\t_unbindEvents = function() {\n\t\tframework.unbind(window, 'resize scroll orientationchange', self);\n\t\tframework.unbind(window, 'scroll', _globalEventHandlers.scroll);\n\t\tframework.unbind(document, 'keydown', self);\n\t\tframework.unbind(document, 'mousemove', _onFirstMouseMove);\n\n\t\tif(_features.transform) {\n\t\t\tframework.unbind(self.scrollWrap, 'click', self);\n\t\t}\n\n\t\tif(_isDragging) {\n\t\t\tframework.unbind(window, _upMoveEvents, self);\n\t\t}\n\n\t\tclearTimeout(_orientationChangeTimeout);\n\n\t\t_shout('unbindEvents');\n\t},\n\t\n\t_calculatePanBounds = function(zoomLevel, update) {\n\t\tvar bounds = _calculateItemSize( self.currItem, _viewportSize, zoomLevel );\n\t\tif(update) {\n\t\t\t_currPanBounds = bounds;\n\t\t}\n\t\treturn bounds;\n\t},\n\t\n\t_getMinZoomLevel = function(item) {\n\t\tif(!item) {\n\t\t\titem = self.currItem;\n\t\t}\n\t\treturn item.initialZoomLevel;\n\t},\n\t_getMaxZoomLevel = function(item) {\n\t\tif(!item) {\n\t\t\titem = self.currItem;\n\t\t}\n\t\treturn item.w > 0 ? _options.maxSpreadZoom : 1;\n\t},\n\n\t// Return true if offset is out of the bounds\n\t_modifyDestPanOffset = function(axis, destPanBounds, destPanOffset, destZoomLevel) {\n\t\tif(destZoomLevel === self.currItem.initialZoomLevel) {\n\t\t\tdestPanOffset[axis] = self.currItem.initialPosition[axis];\n\t\t\treturn true;\n\t\t} else {\n\t\t\tdestPanOffset[axis] = _calculatePanOffset(axis, destZoomLevel); \n\n\t\t\tif(destPanOffset[axis] > destPanBounds.min[axis]) {\n\t\t\t\tdestPanOffset[axis] = destPanBounds.min[axis];\n\t\t\t\treturn true;\n\t\t\t} else if(destPanOffset[axis] < destPanBounds.max[axis] ) {\n\t\t\t\tdestPanOffset[axis] = destPanBounds.max[axis];\n\t\t\t\treturn true;\n\t\t\t}\n\t\t}\n\t\treturn false;\n\t},\n\n\t_setupTransforms = function() {\n\n\t\tif(_transformKey) {\n\t\t\t// setup 3d transforms\n\t\t\tvar allow3dTransform = _features.perspective && !_likelyTouchDevice;\n\t\t\t_translatePrefix = 'translate' + (allow3dTransform ? '3d(' : '(');\n\t\t\t_translateSufix = _features.perspective ? ', 0px)' : ')';\t\n\t\t\treturn;\n\t\t}\n\n\t\t// Override zoom/pan/move functions in case old browser is used (most likely IE)\n\t\t// (so they use left/top/width/height, instead of CSS transform)\n\t\n\t\t_transformKey = 'left';\n\t\tframework.addClass(template, 'pswp--ie');\n\n\t\t_setTranslateX = function(x, elStyle) {\n\t\t\telStyle.left = x + 'px';\n\t\t};\n\t\t_applyZoomPanToItem = function(item) {\n\n\t\t\tvar zoomRatio = item.fitRatio > 1 ? 1 : item.fitRatio,\n\t\t\t\ts = item.container.style,\n\t\t\t\tw = zoomRatio * item.w,\n\t\t\t\th = zoomRatio * item.h;\n\n\t\t\ts.width = w + 'px';\n\t\t\ts.height = h + 'px';\n\t\t\ts.left = item.initialPosition.x + 'px';\n\t\t\ts.top = item.initialPosition.y + 'px';\n\n\t\t};\n\t\t_applyCurrentZoomPan = function() {\n\t\t\tif(_currZoomElementStyle) {\n\n\t\t\t\tvar s = _currZoomElementStyle,\n\t\t\t\t\titem = self.currItem,\n\t\t\t\t\tzoomRatio = item.fitRatio > 1 ? 1 : item.fitRatio,\n\t\t\t\t\tw = zoomRatio * item.w,\n\t\t\t\t\th = zoomRatio * item.h;\n\n\t\t\t\ts.width = w + 'px';\n\t\t\t\ts.height = h + 'px';\n\n\n\t\t\t\ts.left = _panOffset.x + 'px';\n\t\t\t\ts.top = _panOffset.y + 'px';\n\t\t\t}\n\t\t\t\n\t\t};\n\t},\n\n\t_onKeyDown = function(e) {\n\t\tvar keydownAction = '';\n\t\tif(_options.escKey && e.keyCode === 27) { \n\t\t\tkeydownAction = 'close';\n\t\t} else if(_options.arrowKeys) {\n\t\t\tif(e.keyCode === 37) {\n\t\t\t\tkeydownAction = 'prev';\n\t\t\t} else if(e.keyCode === 39) { \n\t\t\t\tkeydownAction = 'next';\n\t\t\t}\n\t\t}\n\n\t\tif(keydownAction) {\n\t\t\t// don't do anything if special key pressed to prevent from overriding default browser actions\n\t\t\t// e.g. in Chrome on Mac cmd+arrow-left returns to previous page\n\t\t\tif( !e.ctrlKey && !e.altKey && !e.shiftKey && !e.metaKey ) {\n\t\t\t\tif(e.preventDefault) {\n\t\t\t\t\te.preventDefault();\n\t\t\t\t} else {\n\t\t\t\t\te.returnValue = false;\n\t\t\t\t} \n\t\t\t\tself[keydownAction]();\n\t\t\t}\n\t\t}\n\t},\n\n\t_onGlobalClick = function(e) {\n\t\tif(!e) {\n\t\t\treturn;\n\t\t}\n\n\t\t// don't allow click event to pass through when triggering after drag or some other gesture\n\t\tif(_moved || _zoomStarted || _mainScrollAnimating || _verticalDragInitiated) {\n\t\t\te.preventDefault();\n\t\t\te.stopPropagation();\n\t\t}\n\t},\n\n\t_updatePageScrollOffset = function() {\n\t\tself.setScrollOffset(0, framework.getScrollY());\t\t\n\t};\n\t\n\n\n\t\n\n\n\n// Micro animation engine\nvar _animations = {},\n\t_numAnimations = 0,\n\t_stopAnimation = function(name) {\n\t\tif(_animations[name]) {\n\t\t\tif(_animations[name].raf) {\n\t\t\t\t_cancelAF( _animations[name].raf );\n\t\t\t}\n\t\t\t_numAnimations--;\n\t\t\tdelete _animations[name];\n\t\t}\n\t},\n\t_registerStartAnimation = function(name) {\n\t\tif(_animations[name]) {\n\t\t\t_stopAnimation(name);\n\t\t}\n\t\tif(!_animations[name]) {\n\t\t\t_numAnimations++;\n\t\t\t_animations[name] = {};\n\t\t}\n\t},\n\t_stopAllAnimations = function() {\n\t\tfor (var prop in _animations) {\n\n\t\t\tif( _animations.hasOwnProperty( prop ) ) {\n\t\t\t\t_stopAnimation(prop);\n\t\t\t} \n\t\t\t\n\t\t}\n\t},\n\t_animateProp = function(name, b, endProp, d, easingFn, onUpdate, onComplete) {\n\t\tvar startAnimTime = _getCurrentTime(), t;\n\t\t_registerStartAnimation(name);\n\n\t\tvar animloop = function(){\n\t\t\tif ( _animations[name] ) {\n\t\t\t\t\n\t\t\t\tt = _getCurrentTime() - startAnimTime; // time diff\n\t\t\t\t//b - beginning (start prop)\n\t\t\t\t//d - anim duration\n\n\t\t\t\tif ( t >= d ) {\n\t\t\t\t\t_stopAnimation(name);\n\t\t\t\t\tonUpdate(endProp);\n\t\t\t\t\tif(onComplete) {\n\t\t\t\t\t\tonComplete();\n\t\t\t\t\t}\n\t\t\t\t\treturn;\n\t\t\t\t}\n\t\t\t\tonUpdate( (endProp - b) * easingFn(t/d) + b );\n\n\t\t\t\t_animations[name].raf = _requestAF(animloop);\n\t\t\t}\n\t\t};\n\t\tanimloop();\n\t};\n\t\n\n\nvar publicMethods = {\n\n\t// make a few local variables and functions public\n\tshout: _shout,\n\tlisten: _listen,\n\tviewportSize: _viewportSize,\n\toptions: _options,\n\n\tisMainScrollAnimating: function() {\n\t\treturn _mainScrollAnimating;\n\t},\n\tgetZoomLevel: function() {\n\t\treturn _currZoomLevel;\n\t},\n\tgetCurrentIndex: function() {\n\t\treturn _currentItemIndex;\n\t},\n\tisDragging: function() {\n\t\treturn _isDragging;\n\t},\t\n\tisZooming: function() {\n\t\treturn _isZooming;\n\t},\n\tsetScrollOffset: function(x,y) {\n\t\t_offset.x = x;\n\t\t_currentWindowScrollY = _offset.y = y;\n\t\t_shout('updateScrollOffset', _offset);\n\t},\n\tapplyZoomPan: function(zoomLevel,panX,panY,allowRenderResolution) {\n\t\t_panOffset.x = panX;\n\t\t_panOffset.y = panY;\n\t\t_currZoomLevel = zoomLevel;\n\t\t_applyCurrentZoomPan( allowRenderResolution );\n\t},\n\n\tinit: function() {\n\n\t\tif(_isOpen || _isDestroying) {\n\t\t\treturn;\n\t\t}\n\n\t\tvar i;\n\n\t\tself.framework = framework; // basic functionality\n\t\tself.template = template; // root DOM element of PhotoSwipe\n\t\tself.bg = framework.getChildByClass(template, 'pswp__bg');\n\n\t\t_initalClassName = template.className;\n\t\t_isOpen = true;\n\t\t\t\t\n\t\t_features = framework.detectFeatures();\n\t\t_requestAF = _features.raf;\n\t\t_cancelAF = _features.caf;\n\t\t_transformKey = _features.transform;\n\t\t_oldIE = _features.oldIE;\n\t\t\n\t\tself.scrollWrap = framework.getChildByClass(template, 'pswp__scroll-wrap');\n\t\tself.container = framework.getChildByClass(self.scrollWrap, 'pswp__container');\n\n\t\t_containerStyle = self.container.style; // for fast access\n\n\t\t// Objects that hold slides (there are only 3 in DOM)\n\t\tself.itemHolders = _itemHolders = [\n\t\t\t{el:self.container.children[0] , wrap:0, index: -1},\n\t\t\t{el:self.container.children[1] , wrap:0, index: -1},\n\t\t\t{el:self.container.children[2] , wrap:0, index: -1}\n\t\t];\n\n\t\t// hide nearby item holders until initial zoom animation finishes (to avoid extra Paints)\n\t\t_itemHolders[0].el.style.display = _itemHolders[2].el.style.display = 'none';\n\n\t\t_setupTransforms();\n\n\t\t// Setup global events\n\t\t_globalEventHandlers = {\n\t\t\tresize: self.updateSize,\n\n\t\t\t// Fixes: iOS 10.3 resize event\n\t\t\t// does not update scrollWrap.clientWidth instantly after resize\n\t\t\t// https://github.com/dimsemenov/PhotoSwipe/issues/1315\n\t\t\torientationchange: function() {\n\t\t\t\tclearTimeout(_orientationChangeTimeout);\n\t\t\t\t_orientationChangeTimeout = setTimeout(function() {\n\t\t\t\t\tif(_viewportSize.x !== self.scrollWrap.clientWidth) {\n\t\t\t\t\t\tself.updateSize();\n\t\t\t\t\t}\n\t\t\t\t}, 500);\n\t\t\t},\n\t\t\tscroll: _updatePageScrollOffset,\n\t\t\tkeydown: _onKeyDown,\n\t\t\tclick: _onGlobalClick\n\t\t};\n\n\t\t// disable show/hide effects on old browsers that don't support CSS animations or transforms, \n\t\t// old IOS, Android and Opera mobile. Blackberry seems to work fine, even older models.\n\t\tvar oldPhone = _features.isOldIOSPhone || _features.isOldAndroid || _features.isMobileOpera;\n\t\tif(!_features.animationName || !_features.transform || oldPhone) {\n\t\t\t_options.showAnimationDuration = _options.hideAnimationDuration = 0;\n\t\t}\n\n\t\t// init modules\n\t\tfor(i = 0; i < _modules.length; i++) {\n\t\t\tself['init' + _modules[i]]();\n\t\t}\n\t\t\n\t\t// init\n\t\tif(UiClass) {\n\t\t\tvar ui = self.ui = new UiClass(self, framework);\n\t\t\tui.init();\n\t\t}\n\n\t\t_shout('firstUpdate');\n\t\t_currentItemIndex = _currentItemIndex || _options.index || 0;\n\t\t// validate index\n\t\tif( isNaN(_currentItemIndex) || _currentItemIndex < 0 || _currentItemIndex >= _getNumItems() ) {\n\t\t\t_currentItemIndex = 0;\n\t\t}\n\t\tself.currItem = _getItemAt( _currentItemIndex );\n\n\t\t\n\t\tif(_features.isOldIOSPhone || _features.isOldAndroid) {\n\t\t\t_isFixedPosition = false;\n\t\t}\n\t\t\n\t\ttemplate.setAttribute('aria-hidden', 'false');\n\t\tif(_options.modal) {\n\t\t\tif(!_isFixedPosition) {\n\t\t\t\ttemplate.style.position = 'absolute';\n\t\t\t\ttemplate.style.top = framework.getScrollY() + 'px';\n\t\t\t} else {\n\t\t\t\ttemplate.style.position = 'fixed';\n\t\t\t}\n\t\t}\n\n\t\tif(_currentWindowScrollY === undefined) {\n\t\t\t_shout('initialLayout');\n\t\t\t_currentWindowScrollY = _initalWindowScrollY = framework.getScrollY();\n\t\t}\n\t\t\n\t\t// add classes to root element of PhotoSwipe\n\t\tvar rootClasses = 'pswp--open ';\n\t\tif(_options.mainClass) {\n\t\t\trootClasses += _options.mainClass + ' ';\n\t\t}\n\t\tif(_options.showHideOpacity) {\n\t\t\trootClasses += 'pswp--animate_opacity ';\n\t\t}\n\t\trootClasses += _likelyTouchDevice ? 'pswp--touch' : 'pswp--notouch';\n\t\trootClasses += _features.animationName ? ' pswp--css_animation' : '';\n\t\trootClasses += _features.svg ? ' pswp--svg' : '';\n\t\tframework.addClass(template, rootClasses);\n\n\t\tself.updateSize();\n\n\t\t// initial update\n\t\t_containerShiftIndex = -1;\n\t\t_indexDiff = null;\n\t\tfor(i = 0; i < NUM_HOLDERS; i++) {\n\t\t\t_setTranslateX( (i+_containerShiftIndex) * _slideSize.x, _itemHolders[i].el.style);\n\t\t}\n\n\t\tif(!_oldIE) {\n\t\t\tframework.bind(self.scrollWrap, _downEvents, self); // no dragging for old IE\n\t\t}\t\n\n\t\t_listen('initialZoomInEnd', function() {\n\t\t\tself.setContent(_itemHolders[0], _currentItemIndex-1);\n\t\t\tself.setContent(_itemHolders[2], _currentItemIndex+1);\n\n\t\t\t_itemHolders[0].el.style.display = _itemHolders[2].el.style.display = 'block';\n\n\t\t\tif(_options.focus) {\n\t\t\t\t// focus causes layout, \n\t\t\t\t// which causes lag during the animation, \n\t\t\t\t// that's why we delay it untill the initial zoom transition ends\n\t\t\t\ttemplate.focus();\n\t\t\t}\n\t\t\t \n\n\t\t\t_bindEvents();\n\t\t});\n\n\t\t// set content for center slide (first time)\n\t\tself.setContent(_itemHolders[1], _currentItemIndex);\n\t\t\n\t\tself.updateCurrItem();\n\n\t\t_shout('afterInit');\n\n\t\tif(!_isFixedPosition) {\n\n\t\t\t// On all versions of iOS lower than 8.0, we check size of viewport every second.\n\t\t\t// \n\t\t\t// This is done to detect when Safari top & bottom bars appear, \n\t\t\t// as this action doesn't trigger any events (like resize). \n\t\t\t// \n\t\t\t// On iOS8 they fixed this.\n\t\t\t// \n\t\t\t// 10 Nov 2014: iOS 7 usage ~40%. iOS 8 usage 56%.\n\t\t\t\n\t\t\t_updateSizeInterval = setInterval(function() {\n\t\t\t\tif(!_numAnimations && !_isDragging && !_isZooming && (_currZoomLevel === self.currItem.initialZoomLevel)  ) {\n\t\t\t\t\tself.updateSize();\n\t\t\t\t}\n\t\t\t}, 1000);\n\t\t}\n\n\t\tframework.addClass(template, 'pswp--visible');\n\t},\n\n\t// Close the gallery, then destroy it\n\tclose: function() {\n\t\tif(!_isOpen) {\n\t\t\treturn;\n\t\t}\n\n\t\t_isOpen = false;\n\t\t_isDestroying = true;\n\t\t_shout('close');\n\t\t_unbindEvents();\n\n\t\t_showOrHide(self.currItem, null, true, self.destroy);\n\t},\n\n\t// destroys the gallery (unbinds events, cleans up intervals and timeouts to avoid memory leaks)\n\tdestroy: function() {\n\t\t_shout('destroy');\n\n\t\tif(_showOrHideTimeout) {\n\t\t\tclearTimeout(_showOrHideTimeout);\n\t\t}\n\t\t\n\t\ttemplate.setAttribute('aria-hidden', 'true');\n\t\ttemplate.className = _initalClassName;\n\n\t\tif(_updateSizeInterval) {\n\t\t\tclearInterval(_updateSizeInterval);\n\t\t}\n\n\t\tframework.unbind(self.scrollWrap, _downEvents, self);\n\n\t\t// we unbind scroll event at the end, as closing animation may depend on it\n\t\tframework.unbind(window, 'scroll', self);\n\n\t\t_stopDragUpdateLoop();\n\n\t\t_stopAllAnimations();\n\n\t\t_listeners = null;\n\t},\n\n\t/**\n\t * Pan image to position\n\t * @param {Number} x     \n\t * @param {Number} y     \n\t * @param {Boolean} force Will ignore bounds if set to true.\n\t */\n\tpanTo: function(x,y,force) {\n\t\tif(!force) {\n\t\t\tif(x > _currPanBounds.min.x) {\n\t\t\t\tx = _currPanBounds.min.x;\n\t\t\t} else if(x < _currPanBounds.max.x) {\n\t\t\t\tx = _currPanBounds.max.x;\n\t\t\t}\n\n\t\t\tif(y > _currPanBounds.min.y) {\n\t\t\t\ty = _currPanBounds.min.y;\n\t\t\t} else if(y < _currPanBounds.max.y) {\n\t\t\t\ty = _currPanBounds.max.y;\n\t\t\t}\n\t\t}\n\t\t\n\t\t_panOffset.x = x;\n\t\t_panOffset.y = y;\n\t\t_applyCurrentZoomPan();\n\t},\n\t\n\thandleEvent: function (e) {\n\t\te = e || window.event;\n\t\tif(_globalEventHandlers[e.type]) {\n\t\t\t_globalEventHandlers[e.type](e);\n\t\t}\n\t},\n\n\n\tgoTo: function(index) {\n\n\t\tindex = _getLoopedId(index);\n\n\t\tvar diff = index - _currentItemIndex;\n\t\t_indexDiff = diff;\n\n\t\t_currentItemIndex = index;\n\t\tself.currItem = _getItemAt( _currentItemIndex );\n\t\t_currPositionIndex -= diff;\n\t\t\n\t\t_moveMainScroll(_slideSize.x * _currPositionIndex);\n\t\t\n\n\t\t_stopAllAnimations();\n\t\t_mainScrollAnimating = false;\n\n\t\tself.updateCurrItem();\n\t},\n\tnext: function() {\n\t\tself.goTo( _currentItemIndex + 1);\n\t},\n\tprev: function() {\n\t\tself.goTo( _currentItemIndex - 1);\n\t},\n\n\t// update current zoom/pan objects\n\tupdateCurrZoomItem: function(emulateSetContent) {\n\t\tif(emulateSetContent) {\n\t\t\t_shout('beforeChange', 0);\n\t\t}\n\n\t\t// itemHolder[1] is middle (current) item\n\t\tif(_itemHolders[1].el.children.length) {\n\t\t\tvar zoomElement = _itemHolders[1].el.children[0];\n\t\t\tif( framework.hasClass(zoomElement, 'pswp__zoom-wrap') ) {\n\t\t\t\t_currZoomElementStyle = zoomElement.style;\n\t\t\t} else {\n\t\t\t\t_currZoomElementStyle = null;\n\t\t\t}\n\t\t} else {\n\t\t\t_currZoomElementStyle = null;\n\t\t}\n\t\t\n\t\t_currPanBounds = self.currItem.bounds;\t\n\t\t_startZoomLevel = _currZoomLevel = self.currItem.initialZoomLevel;\n\n\t\t_panOffset.x = _currPanBounds.center.x;\n\t\t_panOffset.y = _currPanBounds.center.y;\n\n\t\tif(emulateSetContent) {\n\t\t\t_shout('afterChange');\n\t\t}\n\t},\n\n\n\tinvalidateCurrItems: function() {\n\t\t_itemsNeedUpdate = true;\n\t\tfor(var i = 0; i < NUM_HOLDERS; i++) {\n\t\t\tif( _itemHolders[i].item ) {\n\t\t\t\t_itemHolders[i].item.needsUpdate = true;\n\t\t\t}\n\t\t}\n\t},\n\n\tupdateCurrItem: function(beforeAnimation) {\n\n\t\tif(_indexDiff === 0) {\n\t\t\treturn;\n\t\t}\n\n\t\tvar diffAbs = Math.abs(_indexDiff),\n\t\t\ttempHolder;\n\n\t\tif(beforeAnimation && diffAbs < 2) {\n\t\t\treturn;\n\t\t}\n\n\n\t\tself.currItem = _getItemAt( _currentItemIndex );\n\t\t_renderMaxResolution = false;\n\t\t\n\t\t_shout('beforeChange', _indexDiff);\n\n\t\tif(diffAbs >= NUM_HOLDERS) {\n\t\t\t_containerShiftIndex += _indexDiff + (_indexDiff > 0 ? -NUM_HOLDERS : NUM_HOLDERS);\n\t\t\tdiffAbs = NUM_HOLDERS;\n\t\t}\n\t\tfor(var i = 0; i < diffAbs; i++) {\n\t\t\tif(_indexDiff > 0) {\n\t\t\t\ttempHolder = _itemHolders.shift();\n\t\t\t\t_itemHolders[NUM_HOLDERS-1] = tempHolder; // move first to last\n\n\t\t\t\t_containerShiftIndex++;\n\t\t\t\t_setTranslateX( (_containerShiftIndex+2) * _slideSize.x, tempHolder.el.style);\n\t\t\t\tself.setContent(tempHolder, _currentItemIndex - diffAbs + i + 1 + 1);\n\t\t\t} else {\n\t\t\t\ttempHolder = _itemHolders.pop();\n\t\t\t\t_itemHolders.unshift( tempHolder ); // move last to first\n\n\t\t\t\t_containerShiftIndex--;\n\t\t\t\t_setTranslateX( _containerShiftIndex * _slideSize.x, tempHolder.el.style);\n\t\t\t\tself.setContent(tempHolder, _currentItemIndex + diffAbs - i - 1 - 1);\n\t\t\t}\n\t\t\t\n\t\t}\n\n\t\t// reset zoom/pan on previous item\n\t\tif(_currZoomElementStyle && Math.abs(_indexDiff) === 1) {\n\n\t\t\tvar prevItem = _getItemAt(_prevItemIndex);\n\t\t\tif(prevItem.initialZoomLevel !== _currZoomLevel) {\n\t\t\t\t_calculateItemSize(prevItem , _viewportSize );\n\t\t\t\t_setImageSize(prevItem);\n\t\t\t\t_applyZoomPanToItem( prevItem ); \t\t\t\t\n\t\t\t}\n\n\t\t}\n\n\t\t// reset diff after update\n\t\t_indexDiff = 0;\n\n\t\tself.updateCurrZoomItem();\n\n\t\t_prevItemIndex = _currentItemIndex;\n\n\t\t_shout('afterChange');\n\t\t\n\t},\n\n\n\n\tupdateSize: function(force) {\n\t\t\n\t\tif(!_isFixedPosition && _options.modal) {\n\t\t\tvar windowScrollY = framework.getScrollY();\n\t\t\tif(_currentWindowScrollY !== windowScrollY) {\n\t\t\t\ttemplate.style.top = windowScrollY + 'px';\n\t\t\t\t_currentWindowScrollY = windowScrollY;\n\t\t\t}\n\t\t\tif(!force && _windowVisibleSize.x === window.innerWidth && _windowVisibleSize.y === window.innerHeight) {\n\t\t\t\treturn;\n\t\t\t}\n\t\t\t_windowVisibleSize.x = window.innerWidth;\n\t\t\t_windowVisibleSize.y = window.innerHeight;\n\n\t\t\t//template.style.width = _windowVisibleSize.x + 'px';\n\t\t\ttemplate.style.height = _windowVisibleSize.y + 'px';\n\t\t}\n\n\n\n\t\t_viewportSize.x = self.scrollWrap.clientWidth;\n\t\t_viewportSize.y = self.scrollWrap.clientHeight;\n\n\t\t_updatePageScrollOffset();\n\n\t\t_slideSize.x = _viewportSize.x + Math.round(_viewportSize.x * _options.spacing);\n\t\t_slideSize.y = _viewportSize.y;\n\n\t\t_moveMainScroll(_slideSize.x * _currPositionIndex);\n\n\t\t_shout('beforeResize'); // even may be used for example to switch image sources\n\n\n\t\t// don't re-calculate size on inital size update\n\t\tif(_containerShiftIndex !== undefined) {\n\n\t\t\tvar holder,\n\t\t\t\titem,\n\t\t\t\thIndex;\n\n\t\t\tfor(var i = 0; i < NUM_HOLDERS; i++) {\n\t\t\t\tholder = _itemHolders[i];\n\t\t\t\t_setTranslateX( (i+_containerShiftIndex) * _slideSize.x, holder.el.style);\n\n\t\t\t\thIndex = _currentItemIndex+i-1;\n\n\t\t\t\tif(_options.loop && _getNumItems() > 2) {\n\t\t\t\t\thIndex = _getLoopedId(hIndex);\n\t\t\t\t}\n\n\t\t\t\t// update zoom level on items and refresh source (if needsUpdate)\n\t\t\t\titem = _getItemAt( hIndex );\n\n\t\t\t\t// re-render gallery item if `needsUpdate`,\n\t\t\t\t// or doesn't have `bounds` (entirely new slide object)\n\t\t\t\tif( item && (_itemsNeedUpdate || item.needsUpdate || !item.bounds) ) {\n\n\t\t\t\t\tself.cleanSlide( item );\n\t\t\t\t\t\n\t\t\t\t\tself.setContent( holder, hIndex );\n\n\t\t\t\t\t// if \"center\" slide\n\t\t\t\t\tif(i === 1) {\n\t\t\t\t\t\tself.currItem = item;\n\t\t\t\t\t\tself.updateCurrZoomItem(true);\n\t\t\t\t\t}\n\n\t\t\t\t\titem.needsUpdate = false;\n\n\t\t\t\t} else if(holder.index === -1 && hIndex >= 0) {\n\t\t\t\t\t// add content first time\n\t\t\t\t\tself.setContent( holder, hIndex );\n\t\t\t\t}\n\t\t\t\tif(item && item.container) {\n\t\t\t\t\t_calculateItemSize(item, _viewportSize);\n\t\t\t\t\t_setImageSize(item);\n\t\t\t\t\t_applyZoomPanToItem( item );\n\t\t\t\t}\n\t\t\t\t\n\t\t\t}\n\t\t\t_itemsNeedUpdate = false;\n\t\t}\t\n\n\t\t_startZoomLevel = _currZoomLevel = self.currItem.initialZoomLevel;\n\t\t_currPanBounds = self.currItem.bounds;\n\n\t\tif(_currPanBounds) {\n\t\t\t_panOffset.x = _currPanBounds.center.x;\n\t\t\t_panOffset.y = _currPanBounds.center.y;\n\t\t\t_applyCurrentZoomPan( true );\n\t\t}\n\t\t\n\t\t_shout('resize');\n\t},\n\t\n\t// Zoom current item to\n\tzoomTo: function(destZoomLevel, centerPoint, speed, easingFn, updateFn) {\n\t\t/*\n\t\t\tif(destZoomLevel === 'fit') {\n\t\t\t\tdestZoomLevel = self.currItem.fitRatio;\n\t\t\t} else if(destZoomLevel === 'fill') {\n\t\t\t\tdestZoomLevel = self.currItem.fillRatio;\n\t\t\t}\n\t\t*/\n\n\t\tif(centerPoint) {\n\t\t\t_startZoomLevel = _currZoomLevel;\n\t\t\t_midZoomPoint.x = Math.abs(centerPoint.x) - _panOffset.x ;\n\t\t\t_midZoomPoint.y = Math.abs(centerPoint.y) - _panOffset.y ;\n\t\t\t_equalizePoints(_startPanOffset, _panOffset);\n\t\t}\n\n\t\tvar destPanBounds = _calculatePanBounds(destZoomLevel, false),\n\t\t\tdestPanOffset = {};\n\n\t\t_modifyDestPanOffset('x', destPanBounds, destPanOffset, destZoomLevel);\n\t\t_modifyDestPanOffset('y', destPanBounds, destPanOffset, destZoomLevel);\n\n\t\tvar initialZoomLevel = _currZoomLevel;\n\t\tvar initialPanOffset = {\n\t\t\tx: _panOffset.x,\n\t\t\ty: _panOffset.y\n\t\t};\n\n\t\t_roundPoint(destPanOffset);\n\n\t\tvar onUpdate = function(now) {\n\t\t\tif(now === 1) {\n\t\t\t\t_currZoomLevel = destZoomLevel;\n\t\t\t\t_panOffset.x = destPanOffset.x;\n\t\t\t\t_panOffset.y = destPanOffset.y;\n\t\t\t} else {\n\t\t\t\t_currZoomLevel = (destZoomLevel - initialZoomLevel) * now + initialZoomLevel;\n\t\t\t\t_panOffset.x = (destPanOffset.x - initialPanOffset.x) * now + initialPanOffset.x;\n\t\t\t\t_panOffset.y = (destPanOffset.y - initialPanOffset.y) * now + initialPanOffset.y;\n\t\t\t}\n\n\t\t\tif(updateFn) {\n\t\t\t\tupdateFn(now);\n\t\t\t}\n\n\t\t\t_applyCurrentZoomPan( now === 1 );\n\t\t};\n\n\t\tif(speed) {\n\t\t\t_animateProp('customZoomTo', 0, 1, speed, easingFn || framework.easing.sine.inOut, onUpdate);\n\t\t} else {\n\t\t\tonUpdate(1);\n\t\t}\n\t}\n\n\n};\n\n\n/*>>core*/\n\n/*>>gestures*/\n/**\n * Mouse/touch/pointer event handlers.\n * \n * separated from @core.js for readability\n */\n\nvar MIN_SWIPE_DISTANCE = 30,\n\tDIRECTION_CHECK_OFFSET = 10; // amount of pixels to drag to determine direction of swipe\n\nvar _gestureStartTime,\n\t_gestureCheckSpeedTime,\n\n\t// pool of objects that are used during dragging of zooming\n\tp = {}, // first point\n\tp2 = {}, // second point (for zoom gesture)\n\tdelta = {},\n\t_currPoint = {},\n\t_startPoint = {},\n\t_currPointers = [],\n\t_startMainScrollPos = {},\n\t_releaseAnimData,\n\t_posPoints = [], // array of points during dragging, used to determine type of gesture\n\t_tempPoint = {},\n\n\t_isZoomingIn,\n\t_verticalDragInitiated,\n\t_oldAndroidTouchEndTimeout,\n\t_currZoomedItemIndex = 0,\n\t_centerPoint = _getEmptyPoint(),\n\t_lastReleaseTime = 0,\n\t_isDragging, // at least one pointer is down\n\t_isMultitouch, // at least two _pointers are down\n\t_zoomStarted, // zoom level changed during zoom gesture\n\t_moved,\n\t_dragAnimFrame,\n\t_mainScrollShifted,\n\t_currentPoints, // array of current touch points\n\t_isZooming,\n\t_currPointsDistance,\n\t_startPointsDistance,\n\t_currPanBounds,\n\t_mainScrollPos = _getEmptyPoint(),\n\t_currZoomElementStyle,\n\t_mainScrollAnimating, // true, if animation after swipe gesture is running\n\t_midZoomPoint = _getEmptyPoint(),\n\t_currCenterPoint = _getEmptyPoint(),\n\t_direction,\n\t_isFirstMove,\n\t_opacityChanged,\n\t_bgOpacity,\n\t_wasOverInitialZoom,\n\n\t_isEqualPoints = function(p1, p2) {\n\t\treturn p1.x === p2.x && p1.y === p2.y;\n\t},\n\t_isNearbyPoints = function(touch0, touch1) {\n\t\treturn Math.abs(touch0.x - touch1.x) < DOUBLE_TAP_RADIUS && Math.abs(touch0.y - touch1.y) < DOUBLE_TAP_RADIUS;\n\t},\n\t_calculatePointsDistance = function(p1, p2) {\n\t\t_tempPoint.x = Math.abs( p1.x - p2.x );\n\t\t_tempPoint.y = Math.abs( p1.y - p2.y );\n\t\treturn Math.sqrt(_tempPoint.x * _tempPoint.x + _tempPoint.y * _tempPoint.y);\n\t},\n\t_stopDragUpdateLoop = function() {\n\t\tif(_dragAnimFrame) {\n\t\t\t_cancelAF(_dragAnimFrame);\n\t\t\t_dragAnimFrame = null;\n\t\t}\n\t},\n\t_dragUpdateLoop = function() {\n\t\tif(_isDragging) {\n\t\t\t_dragAnimFrame = _requestAF(_dragUpdateLoop);\n\t\t\t_renderMovement();\n\t\t}\n\t},\n\t_canPan = function() {\n\t\treturn !(_options.scaleMode === 'fit' && _currZoomLevel ===  self.currItem.initialZoomLevel);\n\t},\n\t\n\t// find the closest parent DOM element\n\t_closestElement = function(el, fn) {\n\t  \tif(!el || el === document) {\n\t  \t\treturn false;\n\t  \t}\n\n\t  \t// don't search elements above pswp__scroll-wrap\n\t  \tif(el.getAttribute('class') && el.getAttribute('class').indexOf('pswp__scroll-wrap') > -1 ) {\n\t  \t\treturn false;\n\t  \t}\n\n\t  \tif( fn(el) ) {\n\t  \t\treturn el;\n\t  \t}\n\n\t  \treturn _closestElement(el.parentNode, fn);\n\t},\n\n\t_preventObj = {},\n\t_preventDefaultEventBehaviour = function(e, isDown) {\n\t    _preventObj.prevent = !_closestElement(e.target, _options.isClickableElement);\n\n\t\t_shout('preventDragEvent', e, isDown, _preventObj);\n\t\treturn _preventObj.prevent;\n\n\t},\n\t_convertTouchToPoint = function(touch, p) {\n\t\tp.x = touch.pageX;\n\t\tp.y = touch.pageY;\n\t\tp.id = touch.identifier;\n\t\treturn p;\n\t},\n\t_findCenterOfPoints = function(p1, p2, pCenter) {\n\t\tpCenter.x = (p1.x + p2.x) * 0.5;\n\t\tpCenter.y = (p1.y + p2.y) * 0.5;\n\t},\n\t_pushPosPoint = function(time, x, y) {\n\t\tif(time - _gestureCheckSpeedTime > 50) {\n\t\t\tvar o = _posPoints.length > 2 ? _posPoints.shift() : {};\n\t\t\to.x = x;\n\t\t\to.y = y; \n\t\t\t_posPoints.push(o);\n\t\t\t_gestureCheckSpeedTime = time;\n\t\t}\n\t},\n\n\t_calculateVerticalDragOpacityRatio = function() {\n\t\tvar yOffset = _panOffset.y - self.currItem.initialPosition.y; // difference between initial and current position\n\t\treturn 1 -  Math.abs( yOffset / (_viewportSize.y / 2)  );\n\t},\n\n\t\n\t// points pool, reused during touch events\n\t_ePoint1 = {},\n\t_ePoint2 = {},\n\t_tempPointsArr = [],\n\t_tempCounter,\n\t_getTouchPoints = function(e) {\n\t\t// clean up previous points, without recreating array\n\t\twhile(_tempPointsArr.length > 0) {\n\t\t\t_tempPointsArr.pop();\n\t\t}\n\n\t\tif(!_pointerEventEnabled) {\n\t\t\tif(e.type.indexOf('touch') > -1) {\n\n\t\t\t\tif(e.touches && e.touches.length > 0) {\n\t\t\t\t\t_tempPointsArr[0] = _convertTouchToPoint(e.touches[0], _ePoint1);\n\t\t\t\t\tif(e.touches.length > 1) {\n\t\t\t\t\t\t_tempPointsArr[1] = _convertTouchToPoint(e.touches[1], _ePoint2);\n\t\t\t\t\t}\n\t\t\t\t}\n\t\t\t\t\n\t\t\t} else {\n\t\t\t\t_ePoint1.x = e.pageX;\n\t\t\t\t_ePoint1.y = e.pageY;\n\t\t\t\t_ePoint1.id = '';\n\t\t\t\t_tempPointsArr[0] = _ePoint1;//_ePoint1;\n\t\t\t}\n\t\t} else {\n\t\t\t_tempCounter = 0;\n\t\t\t// we can use forEach, as pointer events are supported only in modern browsers\n\t\t\t_currPointers.forEach(function(p) {\n\t\t\t\tif(_tempCounter === 0) {\n\t\t\t\t\t_tempPointsArr[0] = p;\n\t\t\t\t} else if(_tempCounter === 1) {\n\t\t\t\t\t_tempPointsArr[1] = p;\n\t\t\t\t}\n\t\t\t\t_tempCounter++;\n\n\t\t\t});\n\t\t}\n\t\treturn _tempPointsArr;\n\t},\n\n\t_panOrMoveMainScroll = function(axis, delta) {\n\n\t\tvar panFriction,\n\t\t\toverDiff = 0,\n\t\t\tnewOffset = _panOffset[axis] + delta[axis],\n\t\t\tstartOverDiff,\n\t\t\tdir = delta[axis] > 0,\n\t\t\tnewMainScrollPosition = _mainScrollPos.x + delta.x,\n\t\t\tmainScrollDiff = _mainScrollPos.x - _startMainScrollPos.x,\n\t\t\tnewPanPos,\n\t\t\tnewMainScrollPos;\n\n\t\t// calculate fdistance over the bounds and friction\n\t\tif(newOffset > _currPanBounds.min[axis] || newOffset < _currPanBounds.max[axis]) {\n\t\t\tpanFriction = _options.panEndFriction;\n\t\t\t// Linear increasing of friction, so at 1/4 of viewport it's at max value. \n\t\t\t// Looks not as nice as was expected. Left for history.\n\t\t\t// panFriction = (1 - (_panOffset[axis] + delta[axis] + panBounds.min[axis]) / (_viewportSize[axis] / 4) );\n\t\t} else {\n\t\t\tpanFriction = 1;\n\t\t}\n\t\t\n\t\tnewOffset = _panOffset[axis] + delta[axis] * panFriction;\n\n\t\t// move main scroll or start panning\n\t\tif(_options.allowPanToNext || _currZoomLevel === self.currItem.initialZoomLevel) {\n\n\n\t\t\tif(!_currZoomElementStyle) {\n\t\t\t\t\n\t\t\t\tnewMainScrollPos = newMainScrollPosition;\n\n\t\t\t} else if(_direction === 'h' && axis === 'x' && !_zoomStarted ) {\n\t\t\t\t\n\t\t\t\tif(dir) {\n\t\t\t\t\tif(newOffset > _currPanBounds.min[axis]) {\n\t\t\t\t\t\tpanFriction = _options.panEndFriction;\n\t\t\t\t\t\toverDiff = _currPanBounds.min[axis] - newOffset;\n\t\t\t\t\t\tstartOverDiff = _currPanBounds.min[axis] - _startPanOffset[axis];\n\t\t\t\t\t}\n\t\t\t\t\t\n\t\t\t\t\t// drag right\n\t\t\t\t\tif( (startOverDiff <= 0 || mainScrollDiff < 0) && _getNumItems() > 1 ) {\n\t\t\t\t\t\tnewMainScrollPos = newMainScrollPosition;\n\t\t\t\t\t\tif(mainScrollDiff < 0 && newMainScrollPosition > _startMainScrollPos.x) {\n\t\t\t\t\t\t\tnewMainScrollPos = _startMainScrollPos.x;\n\t\t\t\t\t\t}\n\t\t\t\t\t} else {\n\t\t\t\t\t\tif(_currPanBounds.min.x !== _currPanBounds.max.x) {\n\t\t\t\t\t\t\tnewPanPos = newOffset;\n\t\t\t\t\t\t}\n\t\t\t\t\t\t\n\t\t\t\t\t}\n\n\t\t\t\t} else {\n\n\t\t\t\t\tif(newOffset < _currPanBounds.max[axis] ) {\n\t\t\t\t\t\tpanFriction =_options.panEndFriction;\n\t\t\t\t\t\toverDiff = newOffset - _currPanBounds.max[axis];\n\t\t\t\t\t\tstartOverDiff = _startPanOffset[axis] - _currPanBounds.max[axis];\n\t\t\t\t\t}\n\n\t\t\t\t\tif( (startOverDiff <= 0 || mainScrollDiff > 0) && _getNumItems() > 1 ) {\n\t\t\t\t\t\tnewMainScrollPos = newMainScrollPosition;\n\n\t\t\t\t\t\tif(mainScrollDiff > 0 && newMainScrollPosition < _startMainScrollPos.x) {\n\t\t\t\t\t\t\tnewMainScrollPos = _startMainScrollPos.x;\n\t\t\t\t\t\t}\n\n\t\t\t\t\t} else {\n\t\t\t\t\t\tif(_currPanBounds.min.x !== _currPanBounds.max.x) {\n\t\t\t\t\t\t\tnewPanPos = newOffset;\n\t\t\t\t\t\t}\n\t\t\t\t\t}\n\n\t\t\t\t}\n\n\n\t\t\t\t//\n\t\t\t}\n\n\t\t\tif(axis === 'x') {\n\n\t\t\t\tif(newMainScrollPos !== undefined) {\n\t\t\t\t\t_moveMainScroll(newMainScrollPos, true);\n\t\t\t\t\tif(newMainScrollPos === _startMainScrollPos.x) {\n\t\t\t\t\t\t_mainScrollShifted = false;\n\t\t\t\t\t} else {\n\t\t\t\t\t\t_mainScrollShifted = true;\n\t\t\t\t\t}\n\t\t\t\t}\n\n\t\t\t\tif(_currPanBounds.min.x !== _currPanBounds.max.x) {\n\t\t\t\t\tif(newPanPos !== undefined) {\n\t\t\t\t\t\t_panOffset.x = newPanPos;\n\t\t\t\t\t} else if(!_mainScrollShifted) {\n\t\t\t\t\t\t_panOffset.x += delta.x * panFriction;\n\t\t\t\t\t}\n\t\t\t\t}\n\n\t\t\t\treturn newMainScrollPos !== undefined;\n\t\t\t}\n\n\t\t}\n\n\t\tif(!_mainScrollAnimating) {\n\t\t\t\n\t\t\tif(!_mainScrollShifted) {\n\t\t\t\tif(_currZoomLevel > self.currItem.fitRatio) {\n\t\t\t\t\t_panOffset[axis] += delta[axis] * panFriction;\n\t\t\t\t\n\t\t\t\t}\n\t\t\t}\n\n\t\t\t\n\t\t}\n\t\t\n\t},\n\n\t// Pointerdown/touchstart/mousedown handler\n\t_onDragStart = function(e) {\n\n\t\t// Allow dragging only via left mouse button.\n\t\t// As this handler is not added in IE8 - we ignore e.which\n\t\t// \n\t\t// http://www.quirksmode.org/js/events_properties.html\n\t\t// https://developer.mozilla.org/en-US/docs/Web/API/event.button\n\t\tif(e.type === 'mousedown' && e.button > 0  ) {\n\t\t\treturn;\n\t\t}\n\n\t\tif(_initialZoomRunning) {\n\t\t\te.preventDefault();\n\t\t\treturn;\n\t\t}\n\n\t\tif(_oldAndroidTouchEndTimeout && e.type === 'mousedown') {\n\t\t\treturn;\n\t\t}\n\n\t\tif(_preventDefaultEventBehaviour(e, true)) {\n\t\t\te.preventDefault();\n\t\t}\n\n\n\n\t\t_shout('pointerDown');\n\n\t\tif(_pointerEventEnabled) {\n\t\t\tvar pointerIndex = framework.arraySearch(_currPointers, e.pointerId, 'id');\n\t\t\tif(pointerIndex < 0) {\n\t\t\t\tpointerIndex = _currPointers.length;\n\t\t\t}\n\t\t\t_currPointers[pointerIndex] = {x:e.pageX, y:e.pageY, id: e.pointerId};\n\t\t}\n\t\t\n\n\n\t\tvar startPointsList = _getTouchPoints(e),\n\t\t\tnumPoints = startPointsList.length;\n\n\t\t_currentPoints = null;\n\n\t\t_stopAllAnimations();\n\n\t\t// init drag\n\t\tif(!_isDragging || numPoints === 1) {\n\n\t\t\t\n\n\t\t\t_isDragging = _isFirstMove = true;\n\t\t\tframework.bind(window, _upMoveEvents, self);\n\n\t\t\t_isZoomingIn = \n\t\t\t\t_wasOverInitialZoom = \n\t\t\t\t_opacityChanged = \n\t\t\t\t_verticalDragInitiated = \n\t\t\t\t_mainScrollShifted = \n\t\t\t\t_moved = \n\t\t\t\t_isMultitouch = \n\t\t\t\t_zoomStarted = false;\n\n\t\t\t_direction = null;\n\n\t\t\t_shout('firstTouchStart', startPointsList);\n\n\t\t\t_equalizePoints(_startPanOffset, _panOffset);\n\n\t\t\t_currPanDist.x = _currPanDist.y = 0;\n\t\t\t_equalizePoints(_currPoint, startPointsList[0]);\n\t\t\t_equalizePoints(_startPoint, _currPoint);\n\n\t\t\t//_equalizePoints(_startMainScrollPos, _mainScrollPos);\n\t\t\t_startMainScrollPos.x = _slideSize.x * _currPositionIndex;\n\n\t\t\t_posPoints = [{\n\t\t\t\tx: _currPoint.x,\n\t\t\t\ty: _currPoint.y\n\t\t\t}];\n\n\t\t\t_gestureCheckSpeedTime = _gestureStartTime = _getCurrentTime();\n\n\t\t\t//_mainScrollAnimationEnd(true);\n\t\t\t_calculatePanBounds( _currZoomLevel, true );\n\t\t\t\n\t\t\t// Start rendering\n\t\t\t_stopDragUpdateLoop();\n\t\t\t_dragUpdateLoop();\n\t\t\t\n\t\t}\n\n\t\t// init zoom\n\t\tif(!_isZooming && numPoints > 1 && !_mainScrollAnimating && !_mainScrollShifted) {\n\t\t\t_startZoomLevel = _currZoomLevel;\n\t\t\t_zoomStarted = false; // true if zoom changed at least once\n\n\t\t\t_isZooming = _isMultitouch = true;\n\t\t\t_currPanDist.y = _currPanDist.x = 0;\n\n\t\t\t_equalizePoints(_startPanOffset, _panOffset);\n\n\t\t\t_equalizePoints(p, startPointsList[0]);\n\t\t\t_equalizePoints(p2, startPointsList[1]);\n\n\t\t\t_findCenterOfPoints(p, p2, _currCenterPoint);\n\n\t\t\t_midZoomPoint.x = Math.abs(_currCenterPoint.x) - _panOffset.x;\n\t\t\t_midZoomPoint.y = Math.abs(_currCenterPoint.y) - _panOffset.y;\n\t\t\t_currPointsDistance = _startPointsDistance = _calculatePointsDistance(p, p2);\n\t\t}\n\n\n\t},\n\n\t// Pointermove/touchmove/mousemove handler\n\t_onDragMove = function(e) {\n\n\t\te.preventDefault();\n\n\t\tif(_pointerEventEnabled) {\n\t\t\tvar pointerIndex = framework.arraySearch(_currPointers, e.pointerId, 'id');\n\t\t\tif(pointerIndex > -1) {\n\t\t\t\tvar p = _currPointers[pointerIndex];\n\t\t\t\tp.x = e.pageX;\n\t\t\t\tp.y = e.pageY; \n\t\t\t}\n\t\t}\n\n\t\tif(_isDragging) {\n\t\t\tvar touchesList = _getTouchPoints(e);\n\t\t\tif(!_direction && !_moved && !_isZooming) {\n\n\t\t\t\tif(_mainScrollPos.x !== _slideSize.x * _currPositionIndex) {\n\t\t\t\t\t// if main scroll position is shifted – direction is always horizontal\n\t\t\t\t\t_direction = 'h';\n\t\t\t\t} else {\n\t\t\t\t\tvar diff = Math.abs(touchesList[0].x - _currPoint.x) - Math.abs(touchesList[0].y - _currPoint.y);\n\t\t\t\t\t// check the direction of movement\n\t\t\t\t\tif(Math.abs(diff) >= DIRECTION_CHECK_OFFSET) {\n\t\t\t\t\t\t_direction = diff > 0 ? 'h' : 'v';\n\t\t\t\t\t\t_currentPoints = touchesList;\n\t\t\t\t\t}\n\t\t\t\t}\n\t\t\t\t\n\t\t\t} else {\n\t\t\t\t_currentPoints = touchesList;\n\t\t\t}\n\t\t}\t\n\t},\n\t// \n\t_renderMovement =  function() {\n\n\t\tif(!_currentPoints) {\n\t\t\treturn;\n\t\t}\n\n\t\tvar numPoints = _currentPoints.length;\n\n\t\tif(numPoints === 0) {\n\t\t\treturn;\n\t\t}\n\n\t\t_equalizePoints(p, _currentPoints[0]);\n\n\t\tdelta.x = p.x - _currPoint.x;\n\t\tdelta.y = p.y - _currPoint.y;\n\n\t\tif(_isZooming && numPoints > 1) {\n\t\t\t// Handle behaviour for more than 1 point\n\n\t\t\t_currPoint.x = p.x;\n\t\t\t_currPoint.y = p.y;\n\t\t\n\t\t\t// check if one of two points changed\n\t\t\tif( !delta.x && !delta.y && _isEqualPoints(_currentPoints[1], p2) ) {\n\t\t\t\treturn;\n\t\t\t}\n\n\t\t\t_equalizePoints(p2, _currentPoints[1]);\n\n\n\t\t\tif(!_zoomStarted) {\n\t\t\t\t_zoomStarted = true;\n\t\t\t\t_shout('zoomGestureStarted');\n\t\t\t}\n\t\t\t\n\t\t\t// Distance between two points\n\t\t\tvar pointsDistance = _calculatePointsDistance(p,p2);\n\n\t\t\tvar zoomLevel = _calculateZoomLevel(pointsDistance);\n\n\t\t\t// slightly over the of initial zoom level\n\t\t\tif(zoomLevel > self.currItem.initialZoomLevel + self.currItem.initialZoomLevel / 15) {\n\t\t\t\t_wasOverInitialZoom = true;\n\t\t\t}\n\n\t\t\t// Apply the friction if zoom level is out of the bounds\n\t\t\tvar zoomFriction = 1,\n\t\t\t\tminZoomLevel = _getMinZoomLevel(),\n\t\t\t\tmaxZoomLevel = _getMaxZoomLevel();\n\n\t\t\tif ( zoomLevel < minZoomLevel ) {\n\t\t\t\t\n\t\t\t\tif(_options.pinchToClose && !_wasOverInitialZoom && _startZoomLevel <= self.currItem.initialZoomLevel) {\n\t\t\t\t\t// fade out background if zooming out\n\t\t\t\t\tvar minusDiff = minZoomLevel - zoomLevel;\n\t\t\t\t\tvar percent = 1 - minusDiff / (minZoomLevel / 1.2);\n\n\t\t\t\t\t_applyBgOpacity(percent);\n\t\t\t\t\t_shout('onPinchClose', percent);\n\t\t\t\t\t_opacityChanged = true;\n\t\t\t\t} else {\n\t\t\t\t\tzoomFriction = (minZoomLevel - zoomLevel) / minZoomLevel;\n\t\t\t\t\tif(zoomFriction > 1) {\n\t\t\t\t\t\tzoomFriction = 1;\n\t\t\t\t\t}\n\t\t\t\t\tzoomLevel = minZoomLevel - zoomFriction * (minZoomLevel / 3);\n\t\t\t\t}\n\t\t\t\t\n\t\t\t} else if ( zoomLevel > maxZoomLevel ) {\n\t\t\t\t// 1.5 - extra zoom level above the max. E.g. if max is x6, real max 6 + 1.5 = 7.5\n\t\t\t\tzoomFriction = (zoomLevel - maxZoomLevel) / ( minZoomLevel * 6 );\n\t\t\t\tif(zoomFriction > 1) {\n\t\t\t\t\tzoomFriction = 1;\n\t\t\t\t}\n\t\t\t\tzoomLevel = maxZoomLevel + zoomFriction * minZoomLevel;\n\t\t\t}\n\n\t\t\tif(zoomFriction < 0) {\n\t\t\t\tzoomFriction = 0;\n\t\t\t}\n\n\t\t\t// distance between touch points after friction is applied\n\t\t\t_currPointsDistance = pointsDistance;\n\n\t\t\t// _centerPoint - The point in the middle of two pointers\n\t\t\t_findCenterOfPoints(p, p2, _centerPoint);\n\t\t\n\t\t\t// paning with two pointers pressed\n\t\t\t_currPanDist.x += _centerPoint.x - _currCenterPoint.x;\n\t\t\t_currPanDist.y += _centerPoint.y - _currCenterPoint.y;\n\t\t\t_equalizePoints(_currCenterPoint, _centerPoint);\n\n\t\t\t_panOffset.x = _calculatePanOffset('x', zoomLevel);\n\t\t\t_panOffset.y = _calculatePanOffset('y', zoomLevel);\n\n\t\t\t_isZoomingIn = zoomLevel > _currZoomLevel;\n\t\t\t_currZoomLevel = zoomLevel;\n\t\t\t_applyCurrentZoomPan();\n\n\t\t} else {\n\n\t\t\t// handle behaviour for one point (dragging or panning)\n\n\t\t\tif(!_direction) {\n\t\t\t\treturn;\n\t\t\t}\n\n\t\t\tif(_isFirstMove) {\n\t\t\t\t_isFirstMove = false;\n\n\t\t\t\t// subtract drag distance that was used during the detection direction  \n\n\t\t\t\tif( Math.abs(delta.x) >= DIRECTION_CHECK_OFFSET) {\n\t\t\t\t\tdelta.x -= _currentPoints[0].x - _startPoint.x;\n\t\t\t\t}\n\t\t\t\t\n\t\t\t\tif( Math.abs(delta.y) >= DIRECTION_CHECK_OFFSET) {\n\t\t\t\t\tdelta.y -= _currentPoints[0].y - _startPoint.y;\n\t\t\t\t}\n\t\t\t}\n\n\t\t\t_currPoint.x = p.x;\n\t\t\t_currPoint.y = p.y;\n\n\t\t\t// do nothing if pointers position hasn't changed\n\t\t\tif(delta.x === 0 && delta.y === 0) {\n\t\t\t\treturn;\n\t\t\t}\n\n\t\t\tif(_direction === 'v' && _options.closeOnVerticalDrag) {\n\t\t\t\tif(!_canPan()) {\n\t\t\t\t\t_currPanDist.y += delta.y;\n\t\t\t\t\t_panOffset.y += delta.y;\n\n\t\t\t\t\tvar opacityRatio = _calculateVerticalDragOpacityRatio();\n\n\t\t\t\t\t_verticalDragInitiated = true;\n\t\t\t\t\t_shout('onVerticalDrag', opacityRatio);\n\n\t\t\t\t\t_applyBgOpacity(opacityRatio);\n\t\t\t\t\t_applyCurrentZoomPan();\n\t\t\t\t\treturn ;\n\t\t\t\t}\n\t\t\t}\n\n\t\t\t_pushPosPoint(_getCurrentTime(), p.x, p.y);\n\n\t\t\t_moved = true;\n\t\t\t_currPanBounds = self.currItem.bounds;\n\t\t\t\n\t\t\tvar mainScrollChanged = _panOrMoveMainScroll('x', delta);\n\t\t\tif(!mainScrollChanged) {\n\t\t\t\t_panOrMoveMainScroll('y', delta);\n\n\t\t\t\t_roundPoint(_panOffset);\n\t\t\t\t_applyCurrentZoomPan();\n\t\t\t}\n\n\t\t}\n\n\t},\n\t\n\t// Pointerup/pointercancel/touchend/touchcancel/mouseup event handler\n\t_onDragRelease = function(e) {\n\n\t\tif(_features.isOldAndroid ) {\n\n\t\t\tif(_oldAndroidTouchEndTimeout && e.type === 'mouseup') {\n\t\t\t\treturn;\n\t\t\t}\n\n\t\t\t// on Android (v4.1, 4.2, 4.3 & possibly older) \n\t\t\t// ghost mousedown/up event isn't preventable via e.preventDefault,\n\t\t\t// which causes fake mousedown event\n\t\t\t// so we block mousedown/up for 600ms\n\t\t\tif( e.type.indexOf('touch') > -1 ) {\n\t\t\t\tclearTimeout(_oldAndroidTouchEndTimeout);\n\t\t\t\t_oldAndroidTouchEndTimeout = setTimeout(function() {\n\t\t\t\t\t_oldAndroidTouchEndTimeout = 0;\n\t\t\t\t}, 600);\n\t\t\t}\n\t\t\t\n\t\t}\n\n\t\t_shout('pointerUp');\n\n\t\tif(_preventDefaultEventBehaviour(e, false)) {\n\t\t\te.preventDefault();\n\t\t}\n\n\t\tvar releasePoint;\n\n\t\tif(_pointerEventEnabled) {\n\t\t\tvar pointerIndex = framework.arraySearch(_currPointers, e.pointerId, 'id');\n\t\t\t\n\t\t\tif(pointerIndex > -1) {\n\t\t\t\treleasePoint = _currPointers.splice(pointerIndex, 1)[0];\n\n\t\t\t\tif(navigator.msPointerEnabled) {\n\t\t\t\t\tvar MSPOINTER_TYPES = {\n\t\t\t\t\t\t4: 'mouse', // event.MSPOINTER_TYPE_MOUSE\n\t\t\t\t\t\t2: 'touch', // event.MSPOINTER_TYPE_TOUCH \n\t\t\t\t\t\t3: 'pen' // event.MSPOINTER_TYPE_PEN\n\t\t\t\t\t};\n\t\t\t\t\treleasePoint.type = MSPOINTER_TYPES[e.pointerType];\n\n\t\t\t\t\tif(!releasePoint.type) {\n\t\t\t\t\t\treleasePoint.type = e.pointerType || 'mouse';\n\t\t\t\t\t}\n\t\t\t\t} else {\n\t\t\t\t\treleasePoint.type = e.pointerType || 'mouse';\n\t\t\t\t}\n\n\t\t\t}\n\t\t}\n\n\t\tvar touchList = _getTouchPoints(e),\n\t\t\tgestureType,\n\t\t\tnumPoints = touchList.length;\n\n\t\tif(e.type === 'mouseup') {\n\t\t\tnumPoints = 0;\n\t\t}\n\n\t\t// Do nothing if there were 3 touch points or more\n\t\tif(numPoints === 2) {\n\t\t\t_currentPoints = null;\n\t\t\treturn true;\n\t\t}\n\n\t\t// if second pointer released\n\t\tif(numPoints === 1) {\n\t\t\t_equalizePoints(_startPoint, touchList[0]);\n\t\t}\t\t\t\t\n\n\n\t\t// pointer hasn't moved, send \"tap release\" point\n\t\tif(numPoints === 0 && !_direction && !_mainScrollAnimating) {\n\t\t\tif(!releasePoint) {\n\t\t\t\tif(e.type === 'mouseup') {\n\t\t\t\t\treleasePoint = {x: e.pageX, y: e.pageY, type:'mouse'};\n\t\t\t\t} else if(e.changedTouches && e.changedTouches[0]) {\n\t\t\t\t\treleasePoint = {x: e.changedTouches[0].pageX, y: e.changedTouches[0].pageY, type:'touch'};\n\t\t\t\t}\t\t\n\t\t\t}\n\n\t\t\t_shout('touchRelease', e, releasePoint);\n\t\t}\n\n\t\t// Difference in time between releasing of two last touch points (zoom gesture)\n\t\tvar releaseTimeDiff = -1;\n\n\t\t// Gesture completed, no pointers left\n\t\tif(numPoints === 0) {\n\t\t\t_isDragging = false;\n\t\t\tframework.unbind(window, _upMoveEvents, self);\n\n\t\t\t_stopDragUpdateLoop();\n\n\t\t\tif(_isZooming) {\n\t\t\t\t// Two points released at the same time\n\t\t\t\treleaseTimeDiff = 0;\n\t\t\t} else if(_lastReleaseTime !== -1) {\n\t\t\t\treleaseTimeDiff = _getCurrentTime() - _lastReleaseTime;\n\t\t\t}\n\t\t}\n\t\t_lastReleaseTime = numPoints === 1 ? _getCurrentTime() : -1;\n\t\t\n\t\tif(releaseTimeDiff !== -1 && releaseTimeDiff < 150) {\n\t\t\tgestureType = 'zoom';\n\t\t} else {\n\t\t\tgestureType = 'swipe';\n\t\t}\n\n\t\tif(_isZooming && numPoints < 2) {\n\t\t\t_isZooming = false;\n\n\t\t\t// Only second point released\n\t\t\tif(numPoints === 1) {\n\t\t\t\tgestureType = 'zoomPointerUp';\n\t\t\t}\n\t\t\t_shout('zoomGestureEnded');\n\t\t}\n\n\t\t_currentPoints = null;\n\t\tif(!_moved && !_zoomStarted && !_mainScrollAnimating && !_verticalDragInitiated) {\n\t\t\t// nothing to animate\n\t\t\treturn;\n\t\t}\n\t\n\t\t_stopAllAnimations();\n\n\t\t\n\t\tif(!_releaseAnimData) {\n\t\t\t_releaseAnimData = _initDragReleaseAnimationData();\n\t\t}\n\t\t\n\t\t_releaseAnimData.calculateSwipeSpeed('x');\n\n\n\t\tif(_verticalDragInitiated) {\n\n\t\t\tvar opacityRatio = _calculateVerticalDragOpacityRatio();\n\n\t\t\tif(opacityRatio < _options.verticalDragRange) {\n\t\t\t\tself.close();\n\t\t\t} else {\n\t\t\t\tvar initalPanY = _panOffset.y,\n\t\t\t\t\tinitialBgOpacity = _bgOpacity;\n\n\t\t\t\t_animateProp('verticalDrag', 0, 1, 300, framework.easing.cubic.out, function(now) {\n\t\t\t\t\t\n\t\t\t\t\t_panOffset.y = (self.currItem.initialPosition.y - initalPanY) * now + initalPanY;\n\n\t\t\t\t\t_applyBgOpacity(  (1 - initialBgOpacity) * now + initialBgOpacity );\n\t\t\t\t\t_applyCurrentZoomPan();\n\t\t\t\t});\n\n\t\t\t\t_shout('onVerticalDrag', 1);\n\t\t\t}\n\n\t\t\treturn;\n\t\t}\n\n\n\t\t// main scroll \n\t\tif(  (_mainScrollShifted || _mainScrollAnimating) && numPoints === 0) {\n\t\t\tvar itemChanged = _finishSwipeMainScrollGesture(gestureType, _releaseAnimData);\n\t\t\tif(itemChanged) {\n\t\t\t\treturn;\n\t\t\t}\n\t\t\tgestureType = 'zoomPointerUp';\n\t\t}\n\n\t\t// prevent zoom/pan animation when main scroll animation runs\n\t\tif(_mainScrollAnimating) {\n\t\t\treturn;\n\t\t}\n\t\t\n\t\t// Complete simple zoom gesture (reset zoom level if it's out of the bounds)  \n\t\tif(gestureType !== 'swipe') {\n\t\t\t_completeZoomGesture();\n\t\t\treturn;\n\t\t}\n\t\n\t\t// Complete pan gesture if main scroll is not shifted, and it's possible to pan current image\n\t\tif(!_mainScrollShifted && _currZoomLevel > self.currItem.fitRatio) {\n\t\t\t_completePanGesture(_releaseAnimData);\n\t\t}\n\t},\n\n\n\t// Returns object with data about gesture\n\t// It's created only once and then reused\n\t_initDragReleaseAnimationData  = function() {\n\t\t// temp local vars\n\t\tvar lastFlickDuration,\n\t\t\ttempReleasePos;\n\n\t\t// s = this\n\t\tvar s = {\n\t\t\tlastFlickOffset: {},\n\t\t\tlastFlickDist: {},\n\t\t\tlastFlickSpeed: {},\n\t\t\tslowDownRatio:  {},\n\t\t\tslowDownRatioReverse:  {},\n\t\t\tspeedDecelerationRatio:  {},\n\t\t\tspeedDecelerationRatioAbs:  {},\n\t\t\tdistanceOffset:  {},\n\t\t\tbackAnimDestination: {},\n\t\t\tbackAnimStarted: {},\n\t\t\tcalculateSwipeSpeed: function(axis) {\n\t\t\t\t\n\n\t\t\t\tif( _posPoints.length > 1) {\n\t\t\t\t\tlastFlickDuration = _getCurrentTime() - _gestureCheckSpeedTime + 50;\n\t\t\t\t\ttempReleasePos = _posPoints[_posPoints.length-2][axis];\n\t\t\t\t} else {\n\t\t\t\t\tlastFlickDuration = _getCurrentTime() - _gestureStartTime; // total gesture duration\n\t\t\t\t\ttempReleasePos = _startPoint[axis];\n\t\t\t\t}\n\t\t\t\ts.lastFlickOffset[axis] = _currPoint[axis] - tempReleasePos;\n\t\t\t\ts.lastFlickDist[axis] = Math.abs(s.lastFlickOffset[axis]);\n\t\t\t\tif(s.lastFlickDist[axis] > 20) {\n\t\t\t\t\ts.lastFlickSpeed[axis] = s.lastFlickOffset[axis] / lastFlickDuration;\n\t\t\t\t} else {\n\t\t\t\t\ts.lastFlickSpeed[axis] = 0;\n\t\t\t\t}\n\t\t\t\tif( Math.abs(s.lastFlickSpeed[axis]) < 0.1 ) {\n\t\t\t\t\ts.lastFlickSpeed[axis] = 0;\n\t\t\t\t}\n\t\t\t\t\n\t\t\t\ts.slowDownRatio[axis] = 0.95;\n\t\t\t\ts.slowDownRatioReverse[axis] = 1 - s.slowDownRatio[axis];\n\t\t\t\ts.speedDecelerationRatio[axis] = 1;\n\t\t\t},\n\n\t\t\tcalculateOverBoundsAnimOffset: function(axis, speed) {\n\t\t\t\tif(!s.backAnimStarted[axis]) {\n\n\t\t\t\t\tif(_panOffset[axis] > _currPanBounds.min[axis]) {\n\t\t\t\t\t\ts.backAnimDestination[axis] = _currPanBounds.min[axis];\n\t\t\t\t\t\t\n\t\t\t\t\t} else if(_panOffset[axis] < _currPanBounds.max[axis]) {\n\t\t\t\t\t\ts.backAnimDestination[axis] = _currPanBounds.max[axis];\n\t\t\t\t\t}\n\n\t\t\t\t\tif(s.backAnimDestination[axis] !== undefined) {\n\t\t\t\t\t\ts.slowDownRatio[axis] = 0.7;\n\t\t\t\t\t\ts.slowDownRatioReverse[axis] = 1 - s.slowDownRatio[axis];\n\t\t\t\t\t\tif(s.speedDecelerationRatioAbs[axis] < 0.05) {\n\n\t\t\t\t\t\t\ts.lastFlickSpeed[axis] = 0;\n\t\t\t\t\t\t\ts.backAnimStarted[axis] = true;\n\n\t\t\t\t\t\t\t_animateProp('bounceZoomPan'+axis,_panOffset[axis], \n\t\t\t\t\t\t\t\ts.backAnimDestination[axis], \n\t\t\t\t\t\t\t\tspeed || 300, \n\t\t\t\t\t\t\t\tframework.easing.sine.out, \n\t\t\t\t\t\t\t\tfunction(pos) {\n\t\t\t\t\t\t\t\t\t_panOffset[axis] = pos;\n\t\t\t\t\t\t\t\t\t_applyCurrentZoomPan();\n\t\t\t\t\t\t\t\t}\n\t\t\t\t\t\t\t);\n\n\t\t\t\t\t\t}\n\t\t\t\t\t}\n\t\t\t\t}\n\t\t\t},\n\n\t\t\t// Reduces the speed by slowDownRatio (per 10ms)\n\t\t\tcalculateAnimOffset: function(axis) {\n\t\t\t\tif(!s.backAnimStarted[axis]) {\n\t\t\t\t\ts.speedDecelerationRatio[axis] = s.speedDecelerationRatio[axis] * (s.slowDownRatio[axis] + \n\t\t\t\t\t\t\t\t\t\t\t\ts.slowDownRatioReverse[axis] - \n\t\t\t\t\t\t\t\t\t\t\t\ts.slowDownRatioReverse[axis] * s.timeDiff / 10);\n\n\t\t\t\t\ts.speedDecelerationRatioAbs[axis] = Math.abs(s.lastFlickSpeed[axis] * s.speedDecelerationRatio[axis]);\n\t\t\t\t\ts.distanceOffset[axis] = s.lastFlickSpeed[axis] * s.speedDecelerationRatio[axis] * s.timeDiff;\n\t\t\t\t\t_panOffset[axis] += s.distanceOffset[axis];\n\n\t\t\t\t}\n\t\t\t},\n\n\t\t\tpanAnimLoop: function() {\n\t\t\t\tif ( _animations.zoomPan ) {\n\t\t\t\t\t_animations.zoomPan.raf = _requestAF(s.panAnimLoop);\n\n\t\t\t\t\ts.now = _getCurrentTime();\n\t\t\t\t\ts.timeDiff = s.now - s.lastNow;\n\t\t\t\t\ts.lastNow = s.now;\n\t\t\t\t\t\n\t\t\t\t\ts.calculateAnimOffset('x');\n\t\t\t\t\ts.calculateAnimOffset('y');\n\n\t\t\t\t\t_applyCurrentZoomPan();\n\t\t\t\t\t\n\t\t\t\t\ts.calculateOverBoundsAnimOffset('x');\n\t\t\t\t\ts.calculateOverBoundsAnimOffset('y');\n\n\n\t\t\t\t\tif (s.speedDecelerationRatioAbs.x < 0.05 && s.speedDecelerationRatioAbs.y < 0.05) {\n\n\t\t\t\t\t\t// round pan position\n\t\t\t\t\t\t_panOffset.x = Math.round(_panOffset.x);\n\t\t\t\t\t\t_panOffset.y = Math.round(_panOffset.y);\n\t\t\t\t\t\t_applyCurrentZoomPan();\n\t\t\t\t\t\t\n\t\t\t\t\t\t_stopAnimation('zoomPan');\n\t\t\t\t\t\treturn;\n\t\t\t\t\t}\n\t\t\t\t}\n\n\t\t\t}\n\t\t};\n\t\treturn s;\n\t},\n\n\t_completePanGesture = function(animData) {\n\t\t// calculate swipe speed for Y axis (paanning)\n\t\tanimData.calculateSwipeSpeed('y');\n\n\t\t_currPanBounds = self.currItem.bounds;\n\t\t\n\t\tanimData.backAnimDestination = {};\n\t\tanimData.backAnimStarted = {};\n\n\t\t// Avoid acceleration animation if speed is too low\n\t\tif(Math.abs(animData.lastFlickSpeed.x) <= 0.05 && Math.abs(animData.lastFlickSpeed.y) <= 0.05 ) {\n\t\t\tanimData.speedDecelerationRatioAbs.x = animData.speedDecelerationRatioAbs.y = 0;\n\n\t\t\t// Run pan drag release animation. E.g. if you drag image and release finger without momentum.\n\t\t\tanimData.calculateOverBoundsAnimOffset('x');\n\t\t\tanimData.calculateOverBoundsAnimOffset('y');\n\t\t\treturn true;\n\t\t}\n\n\t\t// Animation loop that controls the acceleration after pan gesture ends\n\t\t_registerStartAnimation('zoomPan');\n\t\tanimData.lastNow = _getCurrentTime();\n\t\tanimData.panAnimLoop();\n\t},\n\n\n\t_finishSwipeMainScrollGesture = function(gestureType, _releaseAnimData) {\n\t\tvar itemChanged;\n\t\tif(!_mainScrollAnimating) {\n\t\t\t_currZoomedItemIndex = _currentItemIndex;\n\t\t}\n\n\n\t\t\n\t\tvar itemsDiff;\n\n\t\tif(gestureType === 'swipe') {\n\t\t\tvar totalShiftDist = _currPoint.x - _startPoint.x,\n\t\t\t\tisFastLastFlick = _releaseAnimData.lastFlickDist.x < 10;\n\n\t\t\t// if container is shifted for more than MIN_SWIPE_DISTANCE, \n\t\t\t// and last flick gesture was in right direction\n\t\t\tif(totalShiftDist > MIN_SWIPE_DISTANCE && \n\t\t\t\t(isFastLastFlick || _releaseAnimData.lastFlickOffset.x > 20) ) {\n\t\t\t\t// go to prev item\n\t\t\t\titemsDiff = -1;\n\t\t\t} else if(totalShiftDist < -MIN_SWIPE_DISTANCE && \n\t\t\t\t(isFastLastFlick || _releaseAnimData.lastFlickOffset.x < -20) ) {\n\t\t\t\t// go to next item\n\t\t\t\titemsDiff = 1;\n\t\t\t}\n\t\t}\n\n\t\tvar nextCircle;\n\n\t\tif(itemsDiff) {\n\t\t\t\n\t\t\t_currentItemIndex += itemsDiff;\n\n\t\t\tif(_currentItemIndex < 0) {\n\t\t\t\t_currentItemIndex = _options.loop ? _getNumItems()-1 : 0;\n\t\t\t\tnextCircle = true;\n\t\t\t} else if(_currentItemIndex >= _getNumItems()) {\n\t\t\t\t_currentItemIndex = _options.loop ? 0 : _getNumItems()-1;\n\t\t\t\tnextCircle = true;\n\t\t\t}\n\n\t\t\tif(!nextCircle || _options.loop) {\n\t\t\t\t_indexDiff += itemsDiff;\n\t\t\t\t_currPositionIndex -= itemsDiff;\n\t\t\t\titemChanged = true;\n\t\t\t}\n\t\t\t\n\n\t\t\t\n\t\t}\n\n\t\tvar animateToX = _slideSize.x * _currPositionIndex;\n\t\tvar animateToDist = Math.abs( animateToX - _mainScrollPos.x );\n\t\tvar finishAnimDuration;\n\n\n\t\tif(!itemChanged && animateToX > _mainScrollPos.x !== _releaseAnimData.lastFlickSpeed.x > 0) {\n\t\t\t// \"return to current\" duration, e.g. when dragging from slide 0 to -1\n\t\t\tfinishAnimDuration = 333; \n\t\t} else {\n\t\t\tfinishAnimDuration = Math.abs(_releaseAnimData.lastFlickSpeed.x) > 0 ? \n\t\t\t\t\t\t\t\t\tanimateToDist / Math.abs(_releaseAnimData.lastFlickSpeed.x) : \n\t\t\t\t\t\t\t\t\t333;\n\n\t\t\tfinishAnimDuration = Math.min(finishAnimDuration, 400);\n\t\t\tfinishAnimDuration = Math.max(finishAnimDuration, 250);\n\t\t}\n\n\t\tif(_currZoomedItemIndex === _currentItemIndex) {\n\t\t\titemChanged = false;\n\t\t}\n\t\t\n\t\t_mainScrollAnimating = true;\n\t\t\n\t\t_shout('mainScrollAnimStart');\n\n\t\t_animateProp('mainScroll', _mainScrollPos.x, animateToX, finishAnimDuration, framework.easing.cubic.out, \n\t\t\t_moveMainScroll,\n\t\t\tfunction() {\n\t\t\t\t_stopAllAnimations();\n\t\t\t\t_mainScrollAnimating = false;\n\t\t\t\t_currZoomedItemIndex = -1;\n\t\t\t\t\n\t\t\t\tif(itemChanged || _currZoomedItemIndex !== _currentItemIndex) {\n\t\t\t\t\tself.updateCurrItem();\n\t\t\t\t}\n\t\t\t\t\n\t\t\t\t_shout('mainScrollAnimComplete');\n\t\t\t}\n\t\t);\n\n\t\tif(itemChanged) {\n\t\t\tself.updateCurrItem(true);\n\t\t}\n\n\t\treturn itemChanged;\n\t},\n\n\t_calculateZoomLevel = function(touchesDistance) {\n\t\treturn  1 / _startPointsDistance * touchesDistance * _startZoomLevel;\n\t},\n\n\t// Resets zoom if it's out of bounds\n\t_completeZoomGesture = function() {\n\t\tvar destZoomLevel = _currZoomLevel,\n\t\t\tminZoomLevel = _getMinZoomLevel(),\n\t\t\tmaxZoomLevel = _getMaxZoomLevel();\n\n\t\tif ( _currZoomLevel < minZoomLevel ) {\n\t\t\tdestZoomLevel = minZoomLevel;\n\t\t} else if ( _currZoomLevel > maxZoomLevel ) {\n\t\t\tdestZoomLevel = maxZoomLevel;\n\t\t}\n\n\t\tvar destOpacity = 1,\n\t\t\tonUpdate,\n\t\t\tinitialOpacity = _bgOpacity;\n\n\t\tif(_opacityChanged && !_isZoomingIn && !_wasOverInitialZoom && _currZoomLevel < minZoomLevel) {\n\t\t\t//_closedByScroll = true;\n\t\t\tself.close();\n\t\t\treturn true;\n\t\t}\n\n\t\tif(_opacityChanged) {\n\t\t\tonUpdate = function(now) {\n\t\t\t\t_applyBgOpacity(  (destOpacity - initialOpacity) * now + initialOpacity );\n\t\t\t};\n\t\t}\n\n\t\tself.zoomTo(destZoomLevel, 0, 200,  framework.easing.cubic.out, onUpdate);\n\t\treturn true;\n\t};\n\n\n_registerModule('Gestures', {\n\tpublicMethods: {\n\n\t\tinitGestures: function() {\n\n\t\t\t// helper function that builds touch/pointer/mouse events\n\t\t\tvar addEventNames = function(pref, down, move, up, cancel) {\n\t\t\t\t_dragStartEvent = pref + down;\n\t\t\t\t_dragMoveEvent = pref + move;\n\t\t\t\t_dragEndEvent = pref + up;\n\t\t\t\tif(cancel) {\n\t\t\t\t\t_dragCancelEvent = pref + cancel;\n\t\t\t\t} else {\n\t\t\t\t\t_dragCancelEvent = '';\n\t\t\t\t}\n\t\t\t};\n\n\t\t\t_pointerEventEnabled = _features.pointerEvent;\n\t\t\tif(_pointerEventEnabled && _features.touch) {\n\t\t\t\t// we don't need touch events, if browser supports pointer events\n\t\t\t\t_features.touch = false;\n\t\t\t}\n\n\t\t\tif(_pointerEventEnabled) {\n\t\t\t\tif(navigator.msPointerEnabled) {\n\t\t\t\t\t// IE10 pointer events are case-sensitive\n\t\t\t\t\taddEventNames('MSPointer', 'Down', 'Move', 'Up', 'Cancel');\n\t\t\t\t} else {\n\t\t\t\t\taddEventNames('pointer', 'down', 'move', 'up', 'cancel');\n\t\t\t\t}\n\t\t\t} else if(_features.touch) {\n\t\t\t\taddEventNames('touch', 'start', 'move', 'end', 'cancel');\n\t\t\t\t_likelyTouchDevice = true;\n\t\t\t} else {\n\t\t\t\taddEventNames('mouse', 'down', 'move', 'up');\t\n\t\t\t}\n\n\t\t\t_upMoveEvents = _dragMoveEvent + ' ' + _dragEndEvent  + ' ' +  _dragCancelEvent;\n\t\t\t_downEvents = _dragStartEvent;\n\n\t\t\tif(_pointerEventEnabled && !_likelyTouchDevice) {\n\t\t\t\t_likelyTouchDevice = (navigator.maxTouchPoints > 1) || (navigator.msMaxTouchPoints > 1);\n\t\t\t}\n\t\t\t// make variable public\n\t\t\tself.likelyTouchDevice = _likelyTouchDevice; \n\t\t\t\n\t\t\t_globalEventHandlers[_dragStartEvent] = _onDragStart;\n\t\t\t_globalEventHandlers[_dragMoveEvent] = _onDragMove;\n\t\t\t_globalEventHandlers[_dragEndEvent] = _onDragRelease; // the Kraken\n\n\t\t\tif(_dragCancelEvent) {\n\t\t\t\t_globalEventHandlers[_dragCancelEvent] = _globalEventHandlers[_dragEndEvent];\n\t\t\t}\n\n\t\t\t// Bind mouse events on device with detected hardware touch support, in case it supports multiple types of input.\n\t\t\tif(_features.touch) {\n\t\t\t\t_downEvents += ' mousedown';\n\t\t\t\t_upMoveEvents += ' mousemove mouseup';\n\t\t\t\t_globalEventHandlers.mousedown = _globalEventHandlers[_dragStartEvent];\n\t\t\t\t_globalEventHandlers.mousemove = _globalEventHandlers[_dragMoveEvent];\n\t\t\t\t_globalEventHandlers.mouseup = _globalEventHandlers[_dragEndEvent];\n\t\t\t}\n\n\t\t\tif(!_likelyTouchDevice) {\n\t\t\t\t// don't allow pan to next slide from zoomed state on Desktop\n\t\t\t\t_options.allowPanToNext = false;\n\t\t\t}\n\t\t}\n\n\t}\n});\n\n\n/*>>gestures*/\n\n/*>>show-hide-transition*/\n/**\n * show-hide-transition.js:\n *\n * Manages initial opening or closing transition.\n *\n * If you're not planning to use transition for gallery at all,\n * you may set options hideAnimationDuration and showAnimationDuration to 0,\n * and just delete startAnimation function.\n * \n */\n\n\nvar _showOrHideTimeout,\n\t_showOrHide = function(item, img, out, completeFn) {\n\n\t\tif(_showOrHideTimeout) {\n\t\t\tclearTimeout(_showOrHideTimeout);\n\t\t}\n\n\t\t_initialZoomRunning = true;\n\t\t_initialContentSet = true;\n\t\t\n\t\t// dimensions of small thumbnail {x:,y:,w:}.\n\t\t// Height is optional, as calculated based on large image.\n\t\tvar thumbBounds; \n\t\tif(item.initialLayout) {\n\t\t\tthumbBounds = item.initialLayout;\n\t\t\titem.initialLayout = null;\n\t\t} else {\n\t\t\tthumbBounds = _options.getThumbBoundsFn && _options.getThumbBoundsFn(_currentItemIndex);\n\t\t}\n\n\t\tvar duration = out ? _options.hideAnimationDuration : _options.showAnimationDuration;\n\n\t\tvar onComplete = function() {\n\t\t\t_stopAnimation('initialZoom');\n\t\t\tif(!out) {\n\t\t\t\t_applyBgOpacity(1);\n\t\t\t\tif(img) {\n\t\t\t\t\timg.style.display = 'block';\n\t\t\t\t}\n\t\t\t\tframework.addClass(template, 'pswp--animated-in');\n\t\t\t\t_shout('initialZoom' + (out ? 'OutEnd' : 'InEnd'));\n\t\t\t} else {\n\t\t\t\tself.template.removeAttribute('style');\n\t\t\t\tself.bg.removeAttribute('style');\n\t\t\t}\n\n\t\t\tif(completeFn) {\n\t\t\t\tcompleteFn();\n\t\t\t}\n\t\t\t_initialZoomRunning = false;\n\t\t};\n\n\t\t// if bounds aren't provided, just open gallery without animation\n\t\tif(!duration || !thumbBounds || thumbBounds.x === undefined) {\n\n\t\t\t_shout('initialZoom' + (out ? 'Out' : 'In') );\n\n\t\t\t_currZoomLevel = item.initialZoomLevel;\n\t\t\t_equalizePoints(_panOffset,  item.initialPosition );\n\t\t\t_applyCurrentZoomPan();\n\n\t\t\ttemplate.style.opacity = out ? 0 : 1;\n\t\t\t_applyBgOpacity(1);\n\n\t\t\tif(duration) {\n\t\t\t\tsetTimeout(function() {\n\t\t\t\t\tonComplete();\n\t\t\t\t}, duration);\n\t\t\t} else {\n\t\t\t\tonComplete();\n\t\t\t}\n\n\t\t\treturn;\n\t\t}\n\n\t\tvar startAnimation = function() {\n\t\t\tvar closeWithRaf = _closedByScroll,\n\t\t\t\tfadeEverything = !self.currItem.src || self.currItem.loadError || _options.showHideOpacity;\n\t\t\t\n\t\t\t// apply hw-acceleration to image\n\t\t\tif(item.miniImg) {\n\t\t\t\titem.miniImg.style.webkitBackfaceVisibility = 'hidden';\n\t\t\t}\n\n\t\t\tif(!out) {\n\t\t\t\t_currZoomLevel = thumbBounds.w / item.w;\n\t\t\t\t_panOffset.x = thumbBounds.x;\n\t\t\t\t_panOffset.y = thumbBounds.y - _initalWindowScrollY;\n\n\t\t\t\tself[fadeEverything ? 'template' : 'bg'].style.opacity = 0.001;\n\t\t\t\t_applyCurrentZoomPan();\n\t\t\t}\n\n\t\t\t_registerStartAnimation('initialZoom');\n\t\t\t\n\t\t\tif(out && !closeWithRaf) {\n\t\t\t\tframework.removeClass(template, 'pswp--animated-in');\n\t\t\t}\n\n\t\t\tif(fadeEverything) {\n\t\t\t\tif(out) {\n\t\t\t\t\tframework[ (closeWithRaf ? 'remove' : 'add') + 'Class' ](template, 'pswp--animate_opacity');\n\t\t\t\t} else {\n\t\t\t\t\tsetTimeout(function() {\n\t\t\t\t\t\tframework.addClass(template, 'pswp--animate_opacity');\n\t\t\t\t\t}, 30);\n\t\t\t\t}\n\t\t\t}\n\n\t\t\t_showOrHideTimeout = setTimeout(function() {\n\n\t\t\t\t_shout('initialZoom' + (out ? 'Out' : 'In') );\n\t\t\t\t\n\n\t\t\t\tif(!out) {\n\n\t\t\t\t\t// \"in\" animation always uses CSS transitions (instead of rAF).\n\t\t\t\t\t// CSS transition work faster here, \n\t\t\t\t\t// as developer may also want to animate other things, \n\t\t\t\t\t// like ui on top of sliding area, which can be animated just via CSS\n\t\t\t\t\t\n\t\t\t\t\t_currZoomLevel = item.initialZoomLevel;\n\t\t\t\t\t_equalizePoints(_panOffset,  item.initialPosition );\n\t\t\t\t\t_applyCurrentZoomPan();\n\t\t\t\t\t_applyBgOpacity(1);\n\n\t\t\t\t\tif(fadeEverything) {\n\t\t\t\t\t\ttemplate.style.opacity = 1;\n\t\t\t\t\t} else {\n\t\t\t\t\t\t_applyBgOpacity(1);\n\t\t\t\t\t}\n\n\t\t\t\t\t_showOrHideTimeout = setTimeout(onComplete, duration + 20);\n\t\t\t\t} else {\n\n\t\t\t\t\t// \"out\" animation uses rAF only when PhotoSwipe is closed by browser scroll, to recalculate position\n\t\t\t\t\tvar destZoomLevel = thumbBounds.w / item.w,\n\t\t\t\t\t\tinitialPanOffset = {\n\t\t\t\t\t\t\tx: _panOffset.x,\n\t\t\t\t\t\t\ty: _panOffset.y\n\t\t\t\t\t\t},\n\t\t\t\t\t\tinitialZoomLevel = _currZoomLevel,\n\t\t\t\t\t\tinitalBgOpacity = _bgOpacity,\n\t\t\t\t\t\tonUpdate = function(now) {\n\t\t\t\t\t\t\t\n\t\t\t\t\t\t\tif(now === 1) {\n\t\t\t\t\t\t\t\t_currZoomLevel = destZoomLevel;\n\t\t\t\t\t\t\t\t_panOffset.x = thumbBounds.x;\n\t\t\t\t\t\t\t\t_panOffset.y = thumbBounds.y  - _currentWindowScrollY;\n\t\t\t\t\t\t\t} else {\n\t\t\t\t\t\t\t\t_currZoomLevel = (destZoomLevel - initialZoomLevel) * now + initialZoomLevel;\n\t\t\t\t\t\t\t\t_panOffset.x = (thumbBounds.x - initialPanOffset.x) * now + initialPanOffset.x;\n\t\t\t\t\t\t\t\t_panOffset.y = (thumbBounds.y - _currentWindowScrollY - initialPanOffset.y) * now + initialPanOffset.y;\n\t\t\t\t\t\t\t}\n\t\t\t\t\t\t\t\n\t\t\t\t\t\t\t_applyCurrentZoomPan();\n\t\t\t\t\t\t\tif(fadeEverything) {\n\t\t\t\t\t\t\t\ttemplate.style.opacity = 1 - now;\n\t\t\t\t\t\t\t} else {\n\t\t\t\t\t\t\t\t_applyBgOpacity( initalBgOpacity - now * initalBgOpacity );\n\t\t\t\t\t\t\t}\n\t\t\t\t\t\t};\n\n\t\t\t\t\tif(closeWithRaf) {\n\t\t\t\t\t\t_animateProp('initialZoom', 0, 1, duration, framework.easing.cubic.out, onUpdate, onComplete);\n\t\t\t\t\t} else {\n\t\t\t\t\t\tonUpdate(1);\n\t\t\t\t\t\t_showOrHideTimeout = setTimeout(onComplete, duration + 20);\n\t\t\t\t\t}\n\t\t\t\t}\n\t\t\t\n\t\t\t}, out ? 25 : 90); // Main purpose of this delay is to give browser time to paint and\n\t\t\t\t\t// create composite layers of PhotoSwipe UI parts (background, controls, caption, arrows).\n\t\t\t\t\t// Which avoids lag at the beginning of scale transition.\n\t\t};\n\t\tstartAnimation();\n\n\t\t\n\t};\n\n/*>>show-hide-transition*/\n\n/*>>items-controller*/\n/**\n*\n* Controller manages gallery items, their dimensions, and their content.\n* \n*/\n\nvar _items,\n\t_tempPanAreaSize = {},\n\t_imagesToAppendPool = [],\n\t_initialContentSet,\n\t_initialZoomRunning,\n\t_controllerDefaultOptions = {\n\t\tindex: 0,\n\t\terrorMsg: '<div class=\"pswp__error-msg\"><a href=\"%url%\" target=\"_blank\">The image</a> could not be loaded.</div>',\n\t\tforceProgressiveLoading: false, // TODO\n\t\tpreload: [1,1],\n\t\tgetNumItemsFn: function() {\n\t\t\treturn _items.length;\n\t\t}\n\t};\n\n\nvar _getItemAt,\n\t_getNumItems,\n\t_initialIsLoop,\n\t_getZeroBounds = function() {\n\t\treturn {\n\t\t\tcenter:{x:0,y:0}, \n\t\t\tmax:{x:0,y:0}, \n\t\t\tmin:{x:0,y:0}\n\t\t};\n\t},\n\t_calculateSingleItemPanBounds = function(item, realPanElementW, realPanElementH ) {\n\t\tvar bounds = item.bounds;\n\n\t\t// position of element when it's centered\n\t\tbounds.center.x = Math.round((_tempPanAreaSize.x - realPanElementW) / 2);\n\t\tbounds.center.y = Math.round((_tempPanAreaSize.y - realPanElementH) / 2) + item.vGap.top;\n\n\t\t// maximum pan position\n\t\tbounds.max.x = (realPanElementW > _tempPanAreaSize.x) ? \n\t\t\t\t\t\t\tMath.round(_tempPanAreaSize.x - realPanElementW) : \n\t\t\t\t\t\t\tbounds.center.x;\n\t\t\n\t\tbounds.max.y = (realPanElementH > _tempPanAreaSize.y) ? \n\t\t\t\t\t\t\tMath.round(_tempPanAreaSize.y - realPanElementH) + item.vGap.top : \n\t\t\t\t\t\t\tbounds.center.y;\n\t\t\n\t\t// minimum pan position\n\t\tbounds.min.x = (realPanElementW > _tempPanAreaSize.x) ? 0 : bounds.center.x;\n\t\tbounds.min.y = (realPanElementH > _tempPanAreaSize.y) ? item.vGap.top : bounds.center.y;\n\t},\n\t_calculateItemSize = function(item, viewportSize, zoomLevel) {\n\n\t\tif (item.src && !item.loadError) {\n\t\t\tvar isInitial = !zoomLevel;\n\t\t\t\n\t\t\tif(isInitial) {\n\t\t\t\tif(!item.vGap) {\n\t\t\t\t\titem.vGap = {top:0,bottom:0};\n\t\t\t\t}\n\t\t\t\t// allows overriding vertical margin for individual items\n\t\t\t\t_shout('parseVerticalMargin', item);\n\t\t\t}\n\n\n\t\t\t_tempPanAreaSize.x = viewportSize.x;\n\t\t\t_tempPanAreaSize.y = viewportSize.y - item.vGap.top - item.vGap.bottom;\n\n\t\t\tif (isInitial) {\n\t\t\t\tvar hRatio = _tempPanAreaSize.x / item.w;\n\t\t\t\tvar vRatio = _tempPanAreaSize.y / item.h;\n\n\t\t\t\titem.fitRatio = hRatio < vRatio ? hRatio : vRatio;\n\t\t\t\t//item.fillRatio = hRatio > vRatio ? hRatio : vRatio;\n\n\t\t\t\tvar scaleMode = _options.scaleMode;\n\n\t\t\t\tif (scaleMode === 'orig') {\n\t\t\t\t\tzoomLevel = 1;\n\t\t\t\t} else if (scaleMode === 'fit') {\n\t\t\t\t\tzoomLevel = item.fitRatio;\n\t\t\t\t}\n\n\t\t\t\tif (zoomLevel > 1) {\n\t\t\t\t\tzoomLevel = 1;\n\t\t\t\t}\n\n\t\t\t\titem.initialZoomLevel = zoomLevel;\n\t\t\t\t\n\t\t\t\tif(!item.bounds) {\n\t\t\t\t\t// reuse bounds object\n\t\t\t\t\titem.bounds = _getZeroBounds(); \n\t\t\t\t}\n\t\t\t}\n\n\t\t\tif(!zoomLevel) {\n\t\t\t\treturn;\n\t\t\t}\n\n\t\t\t_calculateSingleItemPanBounds(item, item.w * zoomLevel, item.h * zoomLevel);\n\n\t\t\tif (isInitial && zoomLevel === item.initialZoomLevel) {\n\t\t\t\titem.initialPosition = item.bounds.center;\n\t\t\t}\n\n\t\t\treturn item.bounds;\n\t\t} else {\n\t\t\titem.w = item.h = 0;\n\t\t\titem.initialZoomLevel = item.fitRatio = 1;\n\t\t\titem.bounds = _getZeroBounds();\n\t\t\titem.initialPosition = item.bounds.center;\n\n\t\t\t// if it's not image, we return zero bounds (content is not zoomable)\n\t\t\treturn item.bounds;\n\t\t}\n\t\t\n\t},\n\n\t\n\n\n\t_appendImage = function(index, item, baseDiv, img, preventAnimation, keepPlaceholder) {\n\t\t\n\n\t\tif(item.loadError) {\n\t\t\treturn;\n\t\t}\n\n\t\tif(img) {\n\n\t\t\titem.imageAppended = true;\n\t\t\t_setImageSize(item, img, (item === self.currItem && _renderMaxResolution) );\n\t\t\t\n\t\t\tbaseDiv.appendChild(img);\n\n\t\t\tif(keepPlaceholder) {\n\t\t\t\tsetTimeout(function() {\n\t\t\t\t\tif(item && item.loaded && item.placeholder) {\n\t\t\t\t\t\titem.placeholder.style.display = 'none';\n\t\t\t\t\t\titem.placeholder = null;\n\t\t\t\t\t}\n\t\t\t\t}, 500);\n\t\t\t}\n\t\t}\n\t},\n\t\n\n\n\t_preloadImage = function(item) {\n\t\titem.loading = true;\n\t\titem.loaded = false;\n\t\tvar img = item.img = framework.createEl('pswp__img', 'img');\n\t\tvar onComplete = function() {\n\t\t\titem.loading = false;\n\t\t\titem.loaded = true;\n\n\t\t\tif(item.loadComplete) {\n\t\t\t\titem.loadComplete(item);\n\t\t\t} else {\n\t\t\t\titem.img = null; // no need to store image object\n\t\t\t}\n\t\t\timg.onload = img.onerror = null;\n\t\t\timg = null;\n\t\t};\n\t\timg.onload = onComplete;\n\t\timg.onerror = function() {\n\t\t\titem.loadError = true;\n\t\t\tonComplete();\n\t\t};\t\t\n\n\t\timg.src = item.src;// + '?a=' + Math.random();\n\n\t\treturn img;\n\t},\n\t_checkForError = function(item, cleanUp) {\n\t\tif(item.src && item.loadError && item.container) {\n\n\t\t\tif(cleanUp) {\n\t\t\t\titem.container.innerHTML = '';\n\t\t\t}\n\n\t\t\titem.container.innerHTML = _options.errorMsg.replace('%url%',  item.src );\n\t\t\treturn true;\n\t\t\t\n\t\t}\n\t},\n\t_setImageSize = function(item, img, maxRes) {\n\t\tif(!item.src) {\n\t\t\treturn;\n\t\t}\n\n\t\tif(!img) {\n\t\t\timg = item.container.lastChild;\n\t\t}\n\n\t\tvar w = maxRes ? item.w : Math.round(item.w * item.fitRatio),\n\t\t\th = maxRes ? item.h : Math.round(item.h * item.fitRatio);\n\t\t\n\t\tif(item.placeholder && !item.loaded) {\n\t\t\titem.placeholder.style.width = w + 'px';\n\t\t\titem.placeholder.style.height = h + 'px';\n\t\t}\n\n\t\timg.style.width = w + 'px';\n\t\timg.style.height = h + 'px';\n\t},\n\t_appendImagesPool = function() {\n\n\t\tif(_imagesToAppendPool.length) {\n\t\t\tvar poolItem;\n\n\t\t\tfor(var i = 0; i < _imagesToAppendPool.length; i++) {\n\t\t\t\tpoolItem = _imagesToAppendPool[i];\n\t\t\t\tif( poolItem.holder.index === poolItem.index ) {\n\t\t\t\t\t_appendImage(poolItem.index, poolItem.item, poolItem.baseDiv, poolItem.img, false, poolItem.clearPlaceholder);\n\t\t\t\t}\n\t\t\t}\n\t\t\t_imagesToAppendPool = [];\n\t\t}\n\t};\n\t\n\n\n_registerModule('Controller', {\n\n\tpublicMethods: {\n\n\t\tlazyLoadItem: function(index) {\n\t\t\tindex = _getLoopedId(index);\n\t\t\tvar item = _getItemAt(index);\n\n\t\t\tif(!item || ((item.loaded || item.loading) && !_itemsNeedUpdate)) {\n\t\t\t\treturn;\n\t\t\t}\n\n\t\t\t_shout('gettingData', index, item);\n\n\t\t\tif (!item.src) {\n\t\t\t\treturn;\n\t\t\t}\n\n\t\t\t_preloadImage(item);\n\t\t},\n\t\tinitController: function() {\n\t\t\tframework.extend(_options, _controllerDefaultOptions, true);\n\t\t\tself.items = _items = items;\n\t\t\t_getItemAt = self.getItemAt;\n\t\t\t_getNumItems = _options.getNumItemsFn; //self.getNumItems;\n\n\n\n\t\t\t_initialIsLoop = _options.loop;\n\t\t\tif(_getNumItems() < 3) {\n\t\t\t\t_options.loop = false; // disable loop if less then 3 items\n\t\t\t}\n\n\t\t\t_listen('beforeChange', function(diff) {\n\n\t\t\t\tvar p = _options.preload,\n\t\t\t\t\tisNext = diff === null ? true : (diff >= 0),\n\t\t\t\t\tpreloadBefore = Math.min(p[0], _getNumItems() ),\n\t\t\t\t\tpreloadAfter = Math.min(p[1], _getNumItems() ),\n\t\t\t\t\ti;\n\n\n\t\t\t\tfor(i = 1; i <= (isNext ? preloadAfter : preloadBefore); i++) {\n\t\t\t\t\tself.lazyLoadItem(_currentItemIndex+i);\n\t\t\t\t}\n\t\t\t\tfor(i = 1; i <= (isNext ? preloadBefore : preloadAfter); i++) {\n\t\t\t\t\tself.lazyLoadItem(_currentItemIndex-i);\n\t\t\t\t}\n\t\t\t});\n\n\t\t\t_listen('initialLayout', function() {\n\t\t\t\tself.currItem.initialLayout = _options.getThumbBoundsFn && _options.getThumbBoundsFn(_currentItemIndex);\n\t\t\t});\n\n\t\t\t_listen('mainScrollAnimComplete', _appendImagesPool);\n\t\t\t_listen('initialZoomInEnd', _appendImagesPool);\n\n\n\n\t\t\t_listen('destroy', function() {\n\t\t\t\tvar item;\n\t\t\t\tfor(var i = 0; i < _items.length; i++) {\n\t\t\t\t\titem = _items[i];\n\t\t\t\t\t// remove reference to DOM elements, for GC\n\t\t\t\t\tif(item.container) {\n\t\t\t\t\t\titem.container = null; \n\t\t\t\t\t}\n\t\t\t\t\tif(item.placeholder) {\n\t\t\t\t\t\titem.placeholder = null;\n\t\t\t\t\t}\n\t\t\t\t\tif(item.img) {\n\t\t\t\t\t\titem.img = null;\n\t\t\t\t\t}\n\t\t\t\t\tif(item.preloader) {\n\t\t\t\t\t\titem.preloader = null;\n\t\t\t\t\t}\n\t\t\t\t\tif(item.loadError) {\n\t\t\t\t\t\titem.loaded = item.loadError = false;\n\t\t\t\t\t}\n\t\t\t\t}\n\t\t\t\t_imagesToAppendPool = null;\n\t\t\t});\n\t\t},\n\n\n\t\tgetItemAt: function(index) {\n\t\t\tif (index >= 0) {\n\t\t\t\treturn _items[index] !== undefined ? _items[index] : false;\n\t\t\t}\n\t\t\treturn false;\n\t\t},\n\n\t\tallowProgressiveImg: function() {\n\t\t\t// 1. Progressive image loading isn't working on webkit/blink \n\t\t\t//    when hw-acceleration (e.g. translateZ) is applied to IMG element.\n\t\t\t//    That's why in PhotoSwipe parent element gets zoom transform, not image itself.\n\t\t\t//    \n\t\t\t// 2. Progressive image loading sometimes blinks in webkit/blink when applying animation to parent element.\n\t\t\t//    That's why it's disabled on touch devices (mainly because of swipe transition)\n\t\t\t//    \n\t\t\t// 3. Progressive image loading sometimes doesn't work in IE (up to 11).\n\n\t\t\t// Don't allow progressive loading on non-large touch devices\n\t\t\treturn _options.forceProgressiveLoading || !_likelyTouchDevice || _options.mouseUsed || screen.width > 1200; \n\t\t\t// 1200 - to eliminate touch devices with large screen (like Chromebook Pixel)\n\t\t},\n\n\t\tsetContent: function(holder, index) {\n\n\t\t\tif(_options.loop) {\n\t\t\t\tindex = _getLoopedId(index);\n\t\t\t}\n\n\t\t\tvar prevItem = self.getItemAt(holder.index);\n\t\t\tif(prevItem) {\n\t\t\t\tprevItem.container = null;\n\t\t\t}\n\t\n\t\t\tvar item = self.getItemAt(index),\n\t\t\t\timg;\n\t\t\t\n\t\t\tif(!item) {\n\t\t\t\tholder.el.innerHTML = '';\n\t\t\t\treturn;\n\t\t\t}\n\n\t\t\t// allow to override data\n\t\t\t_shout('gettingData', index, item);\n\n\t\t\tholder.index = index;\n\t\t\tholder.item = item;\n\n\t\t\t// base container DIV is created only once for each of 3 holders\n\t\t\tvar baseDiv = item.container = framework.createEl('pswp__zoom-wrap'); \n\n\t\t\t\n\n\t\t\tif(!item.src && item.html) {\n\t\t\t\tif(item.html.tagName) {\n\t\t\t\t\tbaseDiv.appendChild(item.html);\n\t\t\t\t} else {\n\t\t\t\t\tbaseDiv.innerHTML = item.html;\n\t\t\t\t}\n\t\t\t}\n\n\t\t\t_checkForError(item);\n\n\t\t\t_calculateItemSize(item, _viewportSize);\n\t\t\t\n\t\t\tif(item.src && !item.loadError && !item.loaded) {\n\n\t\t\t\titem.loadComplete = function(item) {\n\n\t\t\t\t\t// gallery closed before image finished loading\n\t\t\t\t\tif(!_isOpen) {\n\t\t\t\t\t\treturn;\n\t\t\t\t\t}\n\n\t\t\t\t\t// check if holder hasn't changed while image was loading\n\t\t\t\t\tif(holder && holder.index === index ) {\n\t\t\t\t\t\tif( _checkForError(item, true) ) {\n\t\t\t\t\t\t\titem.loadComplete = item.img = null;\n\t\t\t\t\t\t\t_calculateItemSize(item, _viewportSize);\n\t\t\t\t\t\t\t_applyZoomPanToItem(item);\n\n\t\t\t\t\t\t\tif(holder.index === _currentItemIndex) {\n\t\t\t\t\t\t\t\t// recalculate dimensions\n\t\t\t\t\t\t\t\tself.updateCurrZoomItem();\n\t\t\t\t\t\t\t}\n\t\t\t\t\t\t\treturn;\n\t\t\t\t\t\t}\n\t\t\t\t\t\tif( !item.imageAppended ) {\n\t\t\t\t\t\t\tif(_features.transform && (_mainScrollAnimating || _initialZoomRunning) ) {\n\t\t\t\t\t\t\t\t_imagesToAppendPool.push({\n\t\t\t\t\t\t\t\t\titem:item,\n\t\t\t\t\t\t\t\t\tbaseDiv:baseDiv,\n\t\t\t\t\t\t\t\t\timg:item.img,\n\t\t\t\t\t\t\t\t\tindex:index,\n\t\t\t\t\t\t\t\t\tholder:holder,\n\t\t\t\t\t\t\t\t\tclearPlaceholder:true\n\t\t\t\t\t\t\t\t});\n\t\t\t\t\t\t\t} else {\n\t\t\t\t\t\t\t\t_appendImage(index, item, baseDiv, item.img, _mainScrollAnimating || _initialZoomRunning, true);\n\t\t\t\t\t\t\t}\n\t\t\t\t\t\t} else {\n\t\t\t\t\t\t\t// remove preloader & mini-img\n\t\t\t\t\t\t\tif(!_initialZoomRunning && item.placeholder) {\n\t\t\t\t\t\t\t\titem.placeholder.style.display = 'none';\n\t\t\t\t\t\t\t\titem.placeholder = null;\n\t\t\t\t\t\t\t}\n\t\t\t\t\t\t}\n\t\t\t\t\t}\n\n\t\t\t\t\titem.loadComplete = null;\n\t\t\t\t\titem.img = null; // no need to store image element after it's added\n\n\t\t\t\t\t_shout('imageLoadComplete', index, item);\n\t\t\t\t};\n\n\t\t\t\tif(framework.features.transform) {\n\t\t\t\t\t\n\t\t\t\t\tvar placeholderClassName = 'pswp__img pswp__img--placeholder'; \n\t\t\t\t\tplaceholderClassName += (item.msrc ? '' : ' pswp__img--placeholder--blank');\n\n\t\t\t\t\tvar placeholder = framework.createEl(placeholderClassName, item.msrc ? 'img' : '');\n\t\t\t\t\tif(item.msrc) {\n\t\t\t\t\t\tplaceholder.src = item.msrc;\n\t\t\t\t\t}\n\t\t\t\t\t\n\t\t\t\t\t_setImageSize(item, placeholder);\n\n\t\t\t\t\tbaseDiv.appendChild(placeholder);\n\t\t\t\t\titem.placeholder = placeholder;\n\n\t\t\t\t}\n\t\t\t\t\n\n\t\t\t\t\n\n\t\t\t\tif(!item.loading) {\n\t\t\t\t\t_preloadImage(item);\n\t\t\t\t}\n\n\n\t\t\t\tif( self.allowProgressiveImg() ) {\n\t\t\t\t\t// just append image\n\t\t\t\t\tif(!_initialContentSet && _features.transform) {\n\t\t\t\t\t\t_imagesToAppendPool.push({\n\t\t\t\t\t\t\titem:item, \n\t\t\t\t\t\t\tbaseDiv:baseDiv, \n\t\t\t\t\t\t\timg:item.img, \n\t\t\t\t\t\t\tindex:index, \n\t\t\t\t\t\t\tholder:holder\n\t\t\t\t\t\t});\n\t\t\t\t\t} else {\n\t\t\t\t\t\t_appendImage(index, item, baseDiv, item.img, true, true);\n\t\t\t\t\t}\n\t\t\t\t}\n\t\t\t\t\n\t\t\t} else if(item.src && !item.loadError) {\n\t\t\t\t// image object is created every time, due to bugs of image loading & delay when switching images\n\t\t\t\timg = framework.createEl('pswp__img', 'img');\n\t\t\t\timg.style.opacity = 1;\n\t\t\t\timg.src = item.src;\n\t\t\t\t_setImageSize(item, img);\n\t\t\t\t_appendImage(index, item, baseDiv, img, true);\n\t\t\t}\n\t\t\t\n\n\t\t\tif(!_initialContentSet && index === _currentItemIndex) {\n\t\t\t\t_currZoomElementStyle = baseDiv.style;\n\t\t\t\t_showOrHide(item, (img ||item.img) );\n\t\t\t} else {\n\t\t\t\t_applyZoomPanToItem(item);\n\t\t\t}\n\n\t\t\tholder.el.innerHTML = '';\n\t\t\tholder.el.appendChild(baseDiv);\n\t\t},\n\n\t\tcleanSlide: function( item ) {\n\t\t\tif(item.img ) {\n\t\t\t\titem.img.onload = item.img.onerror = null;\n\t\t\t}\n\t\t\titem.loaded = item.loading = item.img = item.imageAppended = false;\n\t\t}\n\n\t}\n});\n\n/*>>items-controller*/\n\n/*>>tap*/\n/**\n * tap.js:\n *\n * Displatches tap and double-tap events.\n * \n */\n\nvar tapTimer,\n\ttapReleasePoint = {},\n\t_dispatchTapEvent = function(origEvent, releasePoint, pointerType) {\t\t\n\t\tvar e = document.createEvent( 'CustomEvent' ),\n\t\t\teDetail = {\n\t\t\t\torigEvent:origEvent, \n\t\t\t\ttarget:origEvent.target, \n\t\t\t\treleasePoint: releasePoint, \n\t\t\t\tpointerType:pointerType || 'touch'\n\t\t\t};\n\n\t\te.initCustomEvent( 'pswpTap', true, true, eDetail );\n\t\torigEvent.target.dispatchEvent(e);\n\t};\n\n_registerModule('Tap', {\n\tpublicMethods: {\n\t\tinitTap: function() {\n\t\t\t_listen('firstTouchStart', self.onTapStart);\n\t\t\t_listen('touchRelease', self.onTapRelease);\n\t\t\t_listen('destroy', function() {\n\t\t\t\ttapReleasePoint = {};\n\t\t\t\ttapTimer = null;\n\t\t\t});\n\t\t},\n\t\tonTapStart: function(touchList) {\n\t\t\tif(touchList.length > 1) {\n\t\t\t\tclearTimeout(tapTimer);\n\t\t\t\ttapTimer = null;\n\t\t\t}\n\t\t},\n\t\tonTapRelease: function(e, releasePoint) {\n\t\t\tif(!releasePoint) {\n\t\t\t\treturn;\n\t\t\t}\n\n\t\t\tif(!_moved && !_isMultitouch && !_numAnimations) {\n\t\t\t\tvar p0 = releasePoint;\n\t\t\t\tif(tapTimer) {\n\t\t\t\t\tclearTimeout(tapTimer);\n\t\t\t\t\ttapTimer = null;\n\n\t\t\t\t\t// Check if taped on the same place\n\t\t\t\t\tif ( _isNearbyPoints(p0, tapReleasePoint) ) {\n\t\t\t\t\t\t_shout('doubleTap', p0);\n\t\t\t\t\t\treturn;\n\t\t\t\t\t}\n\t\t\t\t}\n\n\t\t\t\tif(releasePoint.type === 'mouse') {\n\t\t\t\t\t_dispatchTapEvent(e, releasePoint, 'mouse');\n\t\t\t\t\treturn;\n\t\t\t\t}\n\n\t\t\t\tvar clickedTagName = e.target.tagName.toUpperCase();\n\t\t\t\t// avoid double tap delay on buttons and elements that have class pswp__single-tap\n\t\t\t\tif(clickedTagName === 'BUTTON' || framework.hasClass(e.target, 'pswp__single-tap') ) {\n\t\t\t\t\t_dispatchTapEvent(e, releasePoint);\n\t\t\t\t\treturn;\n\t\t\t\t}\n\n\t\t\t\t_equalizePoints(tapReleasePoint, p0);\n\n\t\t\t\ttapTimer = setTimeout(function() {\n\t\t\t\t\t_dispatchTapEvent(e, releasePoint);\n\t\t\t\t\ttapTimer = null;\n\t\t\t\t}, 300);\n\t\t\t}\n\t\t}\n\t}\n});\n\n/*>>tap*/\n\n/*>>desktop-zoom*/\n/**\n *\n * desktop-zoom.js:\n *\n * - Binds mousewheel event for paning zoomed image.\n * - Manages \"dragging\", \"zoomed-in\", \"zoom-out\" classes.\n *   (which are used for cursors and zoom icon)\n * - Adds toggleDesktopZoom function.\n * \n */\n\nvar _wheelDelta;\n\t\n_registerModule('DesktopZoom', {\n\n\tpublicMethods: {\n\n\t\tinitDesktopZoom: function() {\n\n\t\t\tif(_oldIE) {\n\t\t\t\t// no zoom for old IE (<=8)\n\t\t\t\treturn;\n\t\t\t}\n\n\t\t\tif(_likelyTouchDevice) {\n\t\t\t\t// if detected hardware touch support, we wait until mouse is used,\n\t\t\t\t// and only then apply desktop-zoom features\n\t\t\t\t_listen('mouseUsed', function() {\n\t\t\t\t\tself.setupDesktopZoom();\n\t\t\t\t});\n\t\t\t} else {\n\t\t\t\tself.setupDesktopZoom(true);\n\t\t\t}\n\n\t\t},\n\n\t\tsetupDesktopZoom: function(onInit) {\n\n\t\t\t_wheelDelta = {};\n\n\t\t\tvar events = 'wheel mousewheel DOMMouseScroll';\n\t\t\t\n\t\t\t_listen('bindEvents', function() {\n\t\t\t\tframework.bind(template, events,  self.handleMouseWheel);\n\t\t\t});\n\n\t\t\t_listen('unbindEvents', function() {\n\t\t\t\tif(_wheelDelta) {\n\t\t\t\t\tframework.unbind(template, events, self.handleMouseWheel);\n\t\t\t\t}\n\t\t\t});\n\n\t\t\tself.mouseZoomedIn = false;\n\n\t\t\tvar hasDraggingClass,\n\t\t\t\tupdateZoomable = function() {\n\t\t\t\t\tif(self.mouseZoomedIn) {\n\t\t\t\t\t\tframework.removeClass(template, 'pswp--zoomed-in');\n\t\t\t\t\t\tself.mouseZoomedIn = false;\n\t\t\t\t\t}\n\t\t\t\t\tif(_currZoomLevel < 1) {\n\t\t\t\t\t\tframework.addClass(template, 'pswp--zoom-allowed');\n\t\t\t\t\t} else {\n\t\t\t\t\t\tframework.removeClass(template, 'pswp--zoom-allowed');\n\t\t\t\t\t}\n\t\t\t\t\tremoveDraggingClass();\n\t\t\t\t},\n\t\t\t\tremoveDraggingClass = function() {\n\t\t\t\t\tif(hasDraggingClass) {\n\t\t\t\t\t\tframework.removeClass(template, 'pswp--dragging');\n\t\t\t\t\t\thasDraggingClass = false;\n\t\t\t\t\t}\n\t\t\t\t};\n\n\t\t\t_listen('resize' , updateZoomable);\n\t\t\t_listen('afterChange' , updateZoomable);\n\t\t\t_listen('pointerDown', function() {\n\t\t\t\tif(self.mouseZoomedIn) {\n\t\t\t\t\thasDraggingClass = true;\n\t\t\t\t\tframework.addClass(template, 'pswp--dragging');\n\t\t\t\t}\n\t\t\t});\n\t\t\t_listen('pointerUp', removeDraggingClass);\n\n\t\t\tif(!onInit) {\n\t\t\t\tupdateZoomable();\n\t\t\t}\n\t\t\t\n\t\t},\n\n\t\thandleMouseWheel: function(e) {\n\n\t\t\tif(_currZoomLevel <= self.currItem.fitRatio) {\n\t\t\t\tif( _options.modal ) {\n\n\t\t\t\t\tif (!_options.closeOnScroll || _numAnimations || _isDragging) {\n\t\t\t\t\t\te.preventDefault();\n\t\t\t\t\t} else if(_transformKey && Math.abs(e.deltaY) > 2) {\n\t\t\t\t\t\t// close PhotoSwipe\n\t\t\t\t\t\t// if browser supports transforms & scroll changed enough\n\t\t\t\t\t\t_closedByScroll = true;\n\t\t\t\t\t\tself.close();\n\t\t\t\t\t}\n\n\t\t\t\t}\n\t\t\t\treturn true;\n\t\t\t}\n\n\t\t\t// allow just one event to fire\n\t\t\te.stopPropagation();\n\n\t\t\t// https://developer.mozilla.org/en-US/docs/Web/Events/wheel\n\t\t\t_wheelDelta.x = 0;\n\n\t\t\tif('deltaX' in e) {\n\t\t\t\tif(e.deltaMode === 1 /* DOM_DELTA_LINE */) {\n\t\t\t\t\t// 18 - average line height\n\t\t\t\t\t_wheelDelta.x = e.deltaX * 18;\n\t\t\t\t\t_wheelDelta.y = e.deltaY * 18;\n\t\t\t\t} else {\n\t\t\t\t\t_wheelDelta.x = e.deltaX;\n\t\t\t\t\t_wheelDelta.y = e.deltaY;\n\t\t\t\t}\n\t\t\t} else if('wheelDelta' in e) {\n\t\t\t\tif(e.wheelDeltaX) {\n\t\t\t\t\t_wheelDelta.x = -0.16 * e.wheelDeltaX;\n\t\t\t\t}\n\t\t\t\tif(e.wheelDeltaY) {\n\t\t\t\t\t_wheelDelta.y = -0.16 * e.wheelDeltaY;\n\t\t\t\t} else {\n\t\t\t\t\t_wheelDelta.y = -0.16 * e.wheelDelta;\n\t\t\t\t}\n\t\t\t} else if('detail' in e) {\n\t\t\t\t_wheelDelta.y = e.detail;\n\t\t\t} else {\n\t\t\t\treturn;\n\t\t\t}\n\n\t\t\t_calculatePanBounds(_currZoomLevel, true);\n\n\t\t\tvar newPanX = _panOffset.x - _wheelDelta.x,\n\t\t\t\tnewPanY = _panOffset.y - _wheelDelta.y;\n\n\t\t\t// only prevent scrolling in nonmodal mode when not at edges\n\t\t\tif (_options.modal ||\n\t\t\t\t(\n\t\t\t\tnewPanX <= _currPanBounds.min.x && newPanX >= _currPanBounds.max.x &&\n\t\t\t\tnewPanY <= _currPanBounds.min.y && newPanY >= _currPanBounds.max.y\n\t\t\t\t) ) {\n\t\t\t\te.preventDefault();\n\t\t\t}\n\n\t\t\t// TODO: use rAF instead of mousewheel?\n\t\t\tself.panTo(newPanX, newPanY);\n\t\t},\n\n\t\ttoggleDesktopZoom: function(centerPoint) {\n\t\t\tcenterPoint = centerPoint || {x:_viewportSize.x/2 + _offset.x, y:_viewportSize.y/2 + _offset.y };\n\n\t\t\tvar doubleTapZoomLevel = _options.getDoubleTapZoom(true, self.currItem);\n\t\t\tvar zoomOut = _currZoomLevel === doubleTapZoomLevel;\n\t\t\t\n\t\t\tself.mouseZoomedIn = !zoomOut;\n\n\t\t\tself.zoomTo(zoomOut ? self.currItem.initialZoomLevel : doubleTapZoomLevel, centerPoint, 333);\n\t\t\tframework[ (!zoomOut ? 'add' : 'remove') + 'Class'](template, 'pswp--zoomed-in');\n\t\t}\n\n\t}\n});\n\n\n/*>>desktop-zoom*/\n\n/*>>history*/\n/**\n *\n * history.js:\n *\n * - Back button to close gallery.\n * \n * - Unique URL for each slide: example.com/&pid=1&gid=3\n *   (where PID is picture index, and GID and gallery index)\n *   \n * - Switch URL when slides change.\n * \n */\n\n\nvar _historyDefaultOptions = {\n\thistory: true,\n\tgalleryUID: 1\n};\n\nvar _historyUpdateTimeout,\n\t_hashChangeTimeout,\n\t_hashAnimCheckTimeout,\n\t_hashChangedByScript,\n\t_hashChangedByHistory,\n\t_hashReseted,\n\t_initialHash,\n\t_historyChanged,\n\t_closedFromURL,\n\t_urlChangedOnce,\n\t_windowLoc,\n\n\t_supportsPushState,\n\n\t_getHash = function() {\n\t\treturn _windowLoc.hash.substring(1);\n\t},\n\t_cleanHistoryTimeouts = function() {\n\n\t\tif(_historyUpdateTimeout) {\n\t\t\tclearTimeout(_historyUpdateTimeout);\n\t\t}\n\n\t\tif(_hashAnimCheckTimeout) {\n\t\t\tclearTimeout(_hashAnimCheckTimeout);\n\t\t}\n\t},\n\n\t// pid - Picture index\n\t// gid - Gallery index\n\t_parseItemIndexFromURL = function() {\n\t\tvar hash = _getHash(),\n\t\t\tparams = {};\n\n\t\tif(hash.length < 5) { // pid=1\n\t\t\treturn params;\n\t\t}\n\n\t\tvar i, vars = hash.split('&');\n\t\tfor (i = 0; i < vars.length; i++) {\n\t\t\tif(!vars[i]) {\n\t\t\t\tcontinue;\n\t\t\t}\n\t\t\tvar pair = vars[i].split('=');\t\n\t\t\tif(pair.length < 2) {\n\t\t\t\tcontinue;\n\t\t\t}\n\t\t\tparams[pair[0]] = pair[1];\n\t\t}\n\t\tif(_options.galleryPIDs) {\n\t\t\t// detect custom pid in hash and search for it among the items collection\n\t\t\tvar searchfor = params.pid;\n\t\t\tparams.pid = 0; // if custom pid cannot be found, fallback to the first item\n\t\t\tfor(i = 0; i < _items.length; i++) {\n\t\t\t\tif(_items[i].pid === searchfor) {\n\t\t\t\t\tparams.pid = i;\n\t\t\t\t\tbreak;\n\t\t\t\t}\n\t\t\t}\n\t\t} else {\n\t\t\tparams.pid = parseInt(params.pid,10)-1;\n\t\t}\n\t\tif( params.pid < 0 ) {\n\t\t\tparams.pid = 0;\n\t\t}\n\t\treturn params;\n\t},\n\t_updateHash = function() {\n\n\t\tif(_hashAnimCheckTimeout) {\n\t\t\tclearTimeout(_hashAnimCheckTimeout);\n\t\t}\n\n\n\t\tif(_numAnimations || _isDragging) {\n\t\t\t// changing browser URL forces layout/paint in some browsers, which causes noticable lag during animation\n\t\t\t// that's why we update hash only when no animations running\n\t\t\t_hashAnimCheckTimeout = setTimeout(_updateHash, 500);\n\t\t\treturn;\n\t\t}\n\t\t\n\t\tif(_hashChangedByScript) {\n\t\t\tclearTimeout(_hashChangeTimeout);\n\t\t} else {\n\t\t\t_hashChangedByScript = true;\n\t\t}\n\n\n\t\tvar pid = (_currentItemIndex + 1);\n\t\tvar item = _getItemAt( _currentItemIndex );\n\t\tif(item.hasOwnProperty('pid')) {\n\t\t\t// carry forward any custom pid assigned to the item\n\t\t\tpid = item.pid;\n\t\t}\n\t\tvar newHash = _initialHash + '&'  +  'gid=' + _options.galleryUID + '&' + 'pid=' + pid;\n\n\t\tif(!_historyChanged) {\n\t\t\tif(_windowLoc.hash.indexOf(newHash) === -1) {\n\t\t\t\t_urlChangedOnce = true;\n\t\t\t}\n\t\t\t// first time - add new hisory record, then just replace\n\t\t}\n\n\t\tvar newURL = _windowLoc.href.split('#')[0] + '#' +  newHash;\n\n\t\tif( _supportsPushState ) {\n\n\t\t\tif('#' + newHash !== window.location.hash) {\n\t\t\t\thistory[_historyChanged ? 'replaceState' : 'pushState']('', document.title, newURL);\n\t\t\t}\n\n\t\t} else {\n\t\t\tif(_historyChanged) {\n\t\t\t\t_windowLoc.replace( newURL );\n\t\t\t} else {\n\t\t\t\t_windowLoc.hash = newHash;\n\t\t\t}\n\t\t}\n\t\t\n\t\t\n\n\t\t_historyChanged = true;\n\t\t_hashChangeTimeout = setTimeout(function() {\n\t\t\t_hashChangedByScript = false;\n\t\t}, 60);\n\t};\n\n\n\n\t\n\n_registerModule('History', {\n\n\t\n\n\tpublicMethods: {\n\t\tinitHistory: function() {\n\n\t\t\tframework.extend(_options, _historyDefaultOptions, true);\n\n\t\t\tif( !_options.history ) {\n\t\t\t\treturn;\n\t\t\t}\n\n\n\t\t\t_windowLoc = window.location;\n\t\t\t_urlChangedOnce = false;\n\t\t\t_closedFromURL = false;\n\t\t\t_historyChanged = false;\n\t\t\t_initialHash = _getHash();\n\t\t\t_supportsPushState = ('pushState' in history);\n\n\n\t\t\tif(_initialHash.indexOf('gid=') > -1) {\n\t\t\t\t_initialHash = _initialHash.split('&gid=')[0];\n\t\t\t\t_initialHash = _initialHash.split('?gid=')[0];\n\t\t\t}\n\t\t\t\n\n\t\t\t_listen('afterChange', self.updateURL);\n\t\t\t_listen('unbindEvents', function() {\n\t\t\t\tframework.unbind(window, 'hashchange', self.onHashChange);\n\t\t\t});\n\n\n\t\t\tvar returnToOriginal = function() {\n\t\t\t\t_hashReseted = true;\n\t\t\t\tif(!_closedFromURL) {\n\n\t\t\t\t\tif(_urlChangedOnce) {\n\t\t\t\t\t\thistory.back();\n\t\t\t\t\t} else {\n\n\t\t\t\t\t\tif(_initialHash) {\n\t\t\t\t\t\t\t_windowLoc.hash = _initialHash;\n\t\t\t\t\t\t} else {\n\t\t\t\t\t\t\tif (_supportsPushState) {\n\n\t\t\t\t\t\t\t\t// remove hash from url without refreshing it or scrolling to top\n\t\t\t\t\t\t\t\thistory.pushState('', document.title,  _windowLoc.pathname + _windowLoc.search );\n\t\t\t\t\t\t\t} else {\n\t\t\t\t\t\t\t\t_windowLoc.hash = '';\n\t\t\t\t\t\t\t}\n\t\t\t\t\t\t}\n\t\t\t\t\t}\n\t\t\t\t\t\n\t\t\t\t}\n\n\t\t\t\t_cleanHistoryTimeouts();\n\t\t\t};\n\n\n\t\t\t_listen('unbindEvents', function() {\n\t\t\t\tif(_closedByScroll) {\n\t\t\t\t\t// if PhotoSwipe is closed by scroll, we go \"back\" before the closing animation starts\n\t\t\t\t\t// this is done to keep the scroll position\n\t\t\t\t\treturnToOriginal();\n\t\t\t\t}\n\t\t\t});\n\t\t\t_listen('destroy', function() {\n\t\t\t\tif(!_hashReseted) {\n\t\t\t\t\treturnToOriginal();\n\t\t\t\t}\n\t\t\t});\n\t\t\t_listen('firstUpdate', function() {\n\t\t\t\t_currentItemIndex = _parseItemIndexFromURL().pid;\n\t\t\t});\n\n\t\t\t\n\n\t\t\t\n\t\t\tvar index = _initialHash.indexOf('pid=');\n\t\t\tif(index > -1) {\n\t\t\t\t_initialHash = _initialHash.substring(0, index);\n\t\t\t\tif(_initialHash.slice(-1) === '&') {\n\t\t\t\t\t_initialHash = _initialHash.slice(0, -1);\n\t\t\t\t}\n\t\t\t}\n\t\t\t\n\n\t\t\tsetTimeout(function() {\n\t\t\t\tif(_isOpen) { // hasn't destroyed yet\n\t\t\t\t\tframework.bind(window, 'hashchange', self.onHashChange);\n\t\t\t\t}\n\t\t\t}, 40);\n\t\t\t\n\t\t},\n\t\tonHashChange: function() {\n\n\t\t\tif(_getHash() === _initialHash) {\n\n\t\t\t\t_closedFromURL = true;\n\t\t\t\tself.close();\n\t\t\t\treturn;\n\t\t\t}\n\t\t\tif(!_hashChangedByScript) {\n\n\t\t\t\t_hashChangedByHistory = true;\n\t\t\t\tself.goTo( _parseItemIndexFromURL().pid );\n\t\t\t\t_hashChangedByHistory = false;\n\t\t\t}\n\t\t\t\n\t\t},\n\t\tupdateURL: function() {\n\n\t\t\t// Delay the update of URL, to avoid lag during transition, \n\t\t\t// and to not to trigger actions like \"refresh page sound\" or \"blinking favicon\" to often\n\t\t\t\n\t\t\t_cleanHistoryTimeouts();\n\t\t\t\n\n\t\t\tif(_hashChangedByHistory) {\n\t\t\t\treturn;\n\t\t\t}\n\n\t\t\tif(!_historyChanged) {\n\t\t\t\t_updateHash(); // first time\n\t\t\t} else {\n\t\t\t\t_historyUpdateTimeout = setTimeout(_updateHash, 800);\n\t\t\t}\n\t\t}\n\t\n\t}\n});\n\n\n/*>>history*/\n\tframework.extend(self, publicMethods); };\n\treturn PhotoSwipe;\n});"

/***/ }),

/***/ 537:
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(4)(__webpack_require__(538))

/***/ }),

/***/ 538:
/***/ (function(module, exports) {

module.exports = "/*! PhotoSwipe Default UI - 4.1.3 - 2019-01-08\n* http://photoswipe.com\n* Copyright (c) 2019 Dmitry Semenov; */\n/**\n*\n* UI on top of main sliding area (caption, arrows, close button, etc.).\n* Built just using public methods/properties of PhotoSwipe.\n* \n*/\n(function (root, factory) { \n\tif (typeof define === 'function' && define.amd) {\n\t\tdefine(factory);\n\t} else if (typeof exports === 'object') {\n\t\tmodule.exports = factory();\n\t} else {\n\t\troot.PhotoSwipeUI_Default = factory();\n\t}\n})(this, function () {\n\n\t'use strict';\n\n\n\nvar PhotoSwipeUI_Default =\n function(pswp, framework) {\n\n\tvar ui = this;\n\tvar _overlayUIUpdated = false,\n\t\t_controlsVisible = true,\n\t\t_fullscrenAPI,\n\t\t_controls,\n\t\t_captionContainer,\n\t\t_fakeCaptionContainer,\n\t\t_indexIndicator,\n\t\t_shareButton,\n\t\t_shareModal,\n\t\t_shareModalHidden = true,\n\t\t_initalCloseOnScrollValue,\n\t\t_isIdle,\n\t\t_listen,\n\n\t\t_loadingIndicator,\n\t\t_loadingIndicatorHidden,\n\t\t_loadingIndicatorTimeout,\n\n\t\t_galleryHasOneSlide,\n\n\t\t_options,\n\t\t_defaultUIOptions = {\n\t\t\tbarsSize: {top:44, bottom:'auto'},\n\t\t\tcloseElClasses: ['item', 'caption', 'zoom-wrap', 'ui', 'top-bar'], \n\t\t\ttimeToIdle: 4000, \n\t\t\ttimeToIdleOutside: 1000,\n\t\t\tloadingIndicatorDelay: 1000, // 2s\n\t\t\t\n\t\t\taddCaptionHTMLFn: function(item, captionEl /*, isFake */) {\n\t\t\t\tif(!item.title) {\n\t\t\t\t\tcaptionEl.children[0].innerHTML = '';\n\t\t\t\t\treturn false;\n\t\t\t\t}\n\t\t\t\tcaptionEl.children[0].innerHTML = item.title;\n\t\t\t\treturn true;\n\t\t\t},\n\n\t\t\tcloseEl:true,\n\t\t\tcaptionEl: true,\n\t\t\tfullscreenEl: true,\n\t\t\tzoomEl: true,\n\t\t\tshareEl: true,\n\t\t\tcounterEl: true,\n\t\t\tarrowEl: true,\n\t\t\tpreloaderEl: true,\n\n\t\t\ttapToClose: false,\n\t\t\ttapToToggleControls: true,\n\n\t\t\tclickToCloseNonZoomable: true,\n\n\t\t\tshareButtons: [\n\t\t\t\t{id:'facebook', label:'Share on Facebook', url:'https://www.facebook.com/sharer/sharer.php?u={{url}}'},\n\t\t\t\t{id:'twitter', label:'Tweet', url:'https://twitter.com/intent/tweet?text={{text}}&url={{url}}'},\n\t\t\t\t{id:'pinterest', label:'Pin it', url:'http://www.pinterest.com/pin/create/button/'+\n\t\t\t\t\t\t\t\t\t\t\t\t\t'?url={{url}}&media={{image_url}}&description={{text}}'},\n\t\t\t\t{id:'download', label:'Download image', url:'{{raw_image_url}}', download:true}\n\t\t\t],\n\t\t\tgetImageURLForShare: function( /* shareButtonData */ ) {\n\t\t\t\treturn pswp.currItem.src || '';\n\t\t\t},\n\t\t\tgetPageURLForShare: function( /* shareButtonData */ ) {\n\t\t\t\treturn window.location.href;\n\t\t\t},\n\t\t\tgetTextForShare: function( /* shareButtonData */ ) {\n\t\t\t\treturn pswp.currItem.title || '';\n\t\t\t},\n\t\t\t\t\n\t\t\tindexIndicatorSep: ' / ',\n\t\t\tfitControlsWidth: 1200\n\n\t\t},\n\t\t_blockControlsTap,\n\t\t_blockControlsTapTimeout;\n\n\n\n\tvar _onControlsTap = function(e) {\n\t\t\tif(_blockControlsTap) {\n\t\t\t\treturn true;\n\t\t\t}\n\n\n\t\t\te = e || window.event;\n\n\t\t\tif(_options.timeToIdle && _options.mouseUsed && !_isIdle) {\n\t\t\t\t// reset idle timer\n\t\t\t\t_onIdleMouseMove();\n\t\t\t}\n\n\n\t\t\tvar target = e.target || e.srcElement,\n\t\t\t\tuiElement,\n\t\t\t\tclickedClass = target.getAttribute('class') || '',\n\t\t\t\tfound;\n\n\t\t\tfor(var i = 0; i < _uiElements.length; i++) {\n\t\t\t\tuiElement = _uiElements[i];\n\t\t\t\tif(uiElement.onTap && clickedClass.indexOf('pswp__' + uiElement.name ) > -1 ) {\n\t\t\t\t\tuiElement.onTap();\n\t\t\t\t\tfound = true;\n\n\t\t\t\t}\n\t\t\t}\n\n\t\t\tif(found) {\n\t\t\t\tif(e.stopPropagation) {\n\t\t\t\t\te.stopPropagation();\n\t\t\t\t}\n\t\t\t\t_blockControlsTap = true;\n\n\t\t\t\t// Some versions of Android don't prevent ghost click event \n\t\t\t\t// when preventDefault() was called on touchstart and/or touchend.\n\t\t\t\t// \n\t\t\t\t// This happens on v4.3, 4.2, 4.1, \n\t\t\t\t// older versions strangely work correctly, \n\t\t\t\t// but just in case we add delay on all of them)\t\n\t\t\t\tvar tapDelay = framework.features.isOldAndroid ? 600 : 30;\n\t\t\t\t_blockControlsTapTimeout = setTimeout(function() {\n\t\t\t\t\t_blockControlsTap = false;\n\t\t\t\t}, tapDelay);\n\t\t\t}\n\n\t\t},\n\t\t_fitControlsInViewport = function() {\n\t\t\treturn !pswp.likelyTouchDevice || _options.mouseUsed || screen.width > _options.fitControlsWidth;\n\t\t},\n\t\t_togglePswpClass = function(el, cName, add) {\n\t\t\tframework[ (add ? 'add' : 'remove') + 'Class' ](el, 'pswp__' + cName);\n\t\t},\n\n\t\t// add class when there is just one item in the gallery\n\t\t// (by default it hides left/right arrows and 1ofX counter)\n\t\t_countNumItems = function() {\n\t\t\tvar hasOneSlide = (_options.getNumItemsFn() === 1);\n\n\t\t\tif(hasOneSlide !== _galleryHasOneSlide) {\n\t\t\t\t_togglePswpClass(_controls, 'ui--one-slide', hasOneSlide);\n\t\t\t\t_galleryHasOneSlide = hasOneSlide;\n\t\t\t}\n\t\t},\n\t\t_toggleShareModalClass = function() {\n\t\t\t_togglePswpClass(_shareModal, 'share-modal--hidden', _shareModalHidden);\n\t\t},\n\t\t_toggleShareModal = function() {\n\n\t\t\t_shareModalHidden = !_shareModalHidden;\n\t\t\t\n\t\t\t\n\t\t\tif(!_shareModalHidden) {\n\t\t\t\t_toggleShareModalClass();\n\t\t\t\tsetTimeout(function() {\n\t\t\t\t\tif(!_shareModalHidden) {\n\t\t\t\t\t\tframework.addClass(_shareModal, 'pswp__share-modal--fade-in');\n\t\t\t\t\t}\n\t\t\t\t}, 30);\n\t\t\t} else {\n\t\t\t\tframework.removeClass(_shareModal, 'pswp__share-modal--fade-in');\n\t\t\t\tsetTimeout(function() {\n\t\t\t\t\tif(_shareModalHidden) {\n\t\t\t\t\t\t_toggleShareModalClass();\n\t\t\t\t\t}\n\t\t\t\t}, 300);\n\t\t\t}\n\t\t\t\n\t\t\tif(!_shareModalHidden) {\n\t\t\t\t_updateShareURLs();\n\t\t\t}\n\t\t\treturn false;\n\t\t},\n\n\t\t_openWindowPopup = function(e) {\n\t\t\te = e || window.event;\n\t\t\tvar target = e.target || e.srcElement;\n\n\t\t\tpswp.shout('shareLinkClick', e, target);\n\n\t\t\tif(!target.href) {\n\t\t\t\treturn false;\n\t\t\t}\n\n\t\t\tif( target.hasAttribute('download') ) {\n\t\t\t\treturn true;\n\t\t\t}\n\n\t\t\twindow.open(target.href, 'pswp_share', 'scrollbars=yes,resizable=yes,toolbar=no,'+\n\t\t\t\t\t\t\t\t\t\t'location=yes,width=550,height=420,top=100,left=' + \n\t\t\t\t\t\t\t\t\t\t(window.screen ? Math.round(screen.width / 2 - 275) : 100)  );\n\n\t\t\tif(!_shareModalHidden) {\n\t\t\t\t_toggleShareModal();\n\t\t\t}\n\t\t\t\n\t\t\treturn false;\n\t\t},\n\t\t_updateShareURLs = function() {\n\t\t\tvar shareButtonOut = '',\n\t\t\t\tshareButtonData,\n\t\t\t\tshareURL,\n\t\t\t\timage_url,\n\t\t\t\tpage_url,\n\t\t\t\tshare_text;\n\n\t\t\tfor(var i = 0; i < _options.shareButtons.length; i++) {\n\t\t\t\tshareButtonData = _options.shareButtons[i];\n\n\t\t\t\timage_url = _options.getImageURLForShare(shareButtonData);\n\t\t\t\tpage_url = _options.getPageURLForShare(shareButtonData);\n\t\t\t\tshare_text = _options.getTextForShare(shareButtonData);\n\n\t\t\t\tshareURL = shareButtonData.url.replace('{{url}}', encodeURIComponent(page_url) )\n\t\t\t\t\t\t\t\t\t.replace('{{image_url}}', encodeURIComponent(image_url) )\n\t\t\t\t\t\t\t\t\t.replace('{{raw_image_url}}', image_url )\n\t\t\t\t\t\t\t\t\t.replace('{{text}}', encodeURIComponent(share_text) );\n\n\t\t\t\tshareButtonOut += '<a href=\"' + shareURL + '\" target=\"_blank\" '+\n\t\t\t\t\t\t\t\t\t'class=\"pswp__share--' + shareButtonData.id + '\"' +\n\t\t\t\t\t\t\t\t\t(shareButtonData.download ? 'download' : '') + '>' + \n\t\t\t\t\t\t\t\t\tshareButtonData.label + '</a>';\n\n\t\t\t\tif(_options.parseShareButtonOut) {\n\t\t\t\t\tshareButtonOut = _options.parseShareButtonOut(shareButtonData, shareButtonOut);\n\t\t\t\t}\n\t\t\t}\n\t\t\t_shareModal.children[0].innerHTML = shareButtonOut;\n\t\t\t_shareModal.children[0].onclick = _openWindowPopup;\n\n\t\t},\n\t\t_hasCloseClass = function(target) {\n\t\t\tfor(var  i = 0; i < _options.closeElClasses.length; i++) {\n\t\t\t\tif( framework.hasClass(target, 'pswp__' + _options.closeElClasses[i]) ) {\n\t\t\t\t\treturn true;\n\t\t\t\t}\n\t\t\t}\n\t\t},\n\t\t_idleInterval,\n\t\t_idleTimer,\n\t\t_idleIncrement = 0,\n\t\t_onIdleMouseMove = function() {\n\t\t\tclearTimeout(_idleTimer);\n\t\t\t_idleIncrement = 0;\n\t\t\tif(_isIdle) {\n\t\t\t\tui.setIdle(false);\n\t\t\t}\n\t\t},\n\t\t_onMouseLeaveWindow = function(e) {\n\t\t\te = e ? e : window.event;\n\t\t\tvar from = e.relatedTarget || e.toElement;\n\t\t\tif (!from || from.nodeName === 'HTML') {\n\t\t\t\tclearTimeout(_idleTimer);\n\t\t\t\t_idleTimer = setTimeout(function() {\n\t\t\t\t\tui.setIdle(true);\n\t\t\t\t}, _options.timeToIdleOutside);\n\t\t\t}\n\t\t},\n\t\t_setupFullscreenAPI = function() {\n\t\t\tif(_options.fullscreenEl && !framework.features.isOldAndroid) {\n\t\t\t\tif(!_fullscrenAPI) {\n\t\t\t\t\t_fullscrenAPI = ui.getFullscreenAPI();\n\t\t\t\t}\n\t\t\t\tif(_fullscrenAPI) {\n\t\t\t\t\tframework.bind(document, _fullscrenAPI.eventK, ui.updateFullscreen);\n\t\t\t\t\tui.updateFullscreen();\n\t\t\t\t\tframework.addClass(pswp.template, 'pswp--supports-fs');\n\t\t\t\t} else {\n\t\t\t\t\tframework.removeClass(pswp.template, 'pswp--supports-fs');\n\t\t\t\t}\n\t\t\t}\n\t\t},\n\t\t_setupLoadingIndicator = function() {\n\t\t\t// Setup loading indicator\n\t\t\tif(_options.preloaderEl) {\n\t\t\t\n\t\t\t\t_toggleLoadingIndicator(true);\n\n\t\t\t\t_listen('beforeChange', function() {\n\n\t\t\t\t\tclearTimeout(_loadingIndicatorTimeout);\n\n\t\t\t\t\t// display loading indicator with delay\n\t\t\t\t\t_loadingIndicatorTimeout = setTimeout(function() {\n\n\t\t\t\t\t\tif(pswp.currItem && pswp.currItem.loading) {\n\n\t\t\t\t\t\t\tif( !pswp.allowProgressiveImg() || (pswp.currItem.img && !pswp.currItem.img.naturalWidth)  ) {\n\t\t\t\t\t\t\t\t// show preloader if progressive loading is not enabled, \n\t\t\t\t\t\t\t\t// or image width is not defined yet (because of slow connection)\n\t\t\t\t\t\t\t\t_toggleLoadingIndicator(false); \n\t\t\t\t\t\t\t\t// items-controller.js function allowProgressiveImg\n\t\t\t\t\t\t\t}\n\t\t\t\t\t\t\t\n\t\t\t\t\t\t} else {\n\t\t\t\t\t\t\t_toggleLoadingIndicator(true); // hide preloader\n\t\t\t\t\t\t}\n\n\t\t\t\t\t}, _options.loadingIndicatorDelay);\n\t\t\t\t\t\n\t\t\t\t});\n\t\t\t\t_listen('imageLoadComplete', function(index, item) {\n\t\t\t\t\tif(pswp.currItem === item) {\n\t\t\t\t\t\t_toggleLoadingIndicator(true);\n\t\t\t\t\t}\n\t\t\t\t});\n\n\t\t\t}\n\t\t},\n\t\t_toggleLoadingIndicator = function(hide) {\n\t\t\tif( _loadingIndicatorHidden !== hide ) {\n\t\t\t\t_togglePswpClass(_loadingIndicator, 'preloader--active', !hide);\n\t\t\t\t_loadingIndicatorHidden = hide;\n\t\t\t}\n\t\t},\n\t\t_applyNavBarGaps = function(item) {\n\t\t\tvar gap = item.vGap;\n\n\t\t\tif( _fitControlsInViewport() ) {\n\t\t\t\t\n\t\t\t\tvar bars = _options.barsSize; \n\t\t\t\tif(_options.captionEl && bars.bottom === 'auto') {\n\t\t\t\t\tif(!_fakeCaptionContainer) {\n\t\t\t\t\t\t_fakeCaptionContainer = framework.createEl('pswp__caption pswp__caption--fake');\n\t\t\t\t\t\t_fakeCaptionContainer.appendChild( framework.createEl('pswp__caption__center') );\n\t\t\t\t\t\t_controls.insertBefore(_fakeCaptionContainer, _captionContainer);\n\t\t\t\t\t\tframework.addClass(_controls, 'pswp__ui--fit');\n\t\t\t\t\t}\n\t\t\t\t\tif( _options.addCaptionHTMLFn(item, _fakeCaptionContainer, true) ) {\n\n\t\t\t\t\t\tvar captionSize = _fakeCaptionContainer.clientHeight;\n\t\t\t\t\t\tgap.bottom = parseInt(captionSize,10) || 44;\n\t\t\t\t\t} else {\n\t\t\t\t\t\tgap.bottom = bars.top; // if no caption, set size of bottom gap to size of top\n\t\t\t\t\t}\n\t\t\t\t} else {\n\t\t\t\t\tgap.bottom = bars.bottom === 'auto' ? 0 : bars.bottom;\n\t\t\t\t}\n\t\t\t\t\n\t\t\t\t// height of top bar is static, no need to calculate it\n\t\t\t\tgap.top = bars.top;\n\t\t\t} else {\n\t\t\t\tgap.top = gap.bottom = 0;\n\t\t\t}\n\t\t},\n\t\t_setupIdle = function() {\n\t\t\t// Hide controls when mouse is used\n\t\t\tif(_options.timeToIdle) {\n\t\t\t\t_listen('mouseUsed', function() {\n\t\t\t\t\t\n\t\t\t\t\tframework.bind(document, 'mousemove', _onIdleMouseMove);\n\t\t\t\t\tframework.bind(document, 'mouseout', _onMouseLeaveWindow);\n\n\t\t\t\t\t_idleInterval = setInterval(function() {\n\t\t\t\t\t\t_idleIncrement++;\n\t\t\t\t\t\tif(_idleIncrement === 2) {\n\t\t\t\t\t\t\tui.setIdle(true);\n\t\t\t\t\t\t}\n\t\t\t\t\t}, _options.timeToIdle / 2);\n\t\t\t\t});\n\t\t\t}\n\t\t},\n\t\t_setupHidingControlsDuringGestures = function() {\n\n\t\t\t// Hide controls on vertical drag\n\t\t\t_listen('onVerticalDrag', function(now) {\n\t\t\t\tif(_controlsVisible && now < 0.95) {\n\t\t\t\t\tui.hideControls();\n\t\t\t\t} else if(!_controlsVisible && now >= 0.95) {\n\t\t\t\t\tui.showControls();\n\t\t\t\t}\n\t\t\t});\n\n\t\t\t// Hide controls when pinching to close\n\t\t\tvar pinchControlsHidden;\n\t\t\t_listen('onPinchClose' , function(now) {\n\t\t\t\tif(_controlsVisible && now < 0.9) {\n\t\t\t\t\tui.hideControls();\n\t\t\t\t\tpinchControlsHidden = true;\n\t\t\t\t} else if(pinchControlsHidden && !_controlsVisible && now > 0.9) {\n\t\t\t\t\tui.showControls();\n\t\t\t\t}\n\t\t\t});\n\n\t\t\t_listen('zoomGestureEnded', function() {\n\t\t\t\tpinchControlsHidden = false;\n\t\t\t\tif(pinchControlsHidden && !_controlsVisible) {\n\t\t\t\t\tui.showControls();\n\t\t\t\t}\n\t\t\t});\n\n\t\t};\n\n\n\n\tvar _uiElements = [\n\t\t{ \n\t\t\tname: 'caption', \n\t\t\toption: 'captionEl',\n\t\t\tonInit: function(el) {  \n\t\t\t\t_captionContainer = el; \n\t\t\t} \n\t\t},\n\t\t{ \n\t\t\tname: 'share-modal', \n\t\t\toption: 'shareEl',\n\t\t\tonInit: function(el) {  \n\t\t\t\t_shareModal = el;\n\t\t\t},\n\t\t\tonTap: function() {\n\t\t\t\t_toggleShareModal();\n\t\t\t} \n\t\t},\n\t\t{ \n\t\t\tname: 'button--share', \n\t\t\toption: 'shareEl',\n\t\t\tonInit: function(el) { \n\t\t\t\t_shareButton = el;\n\t\t\t},\n\t\t\tonTap: function() {\n\t\t\t\t_toggleShareModal();\n\t\t\t} \n\t\t},\n\t\t{ \n\t\t\tname: 'button--zoom', \n\t\t\toption: 'zoomEl',\n\t\t\tonTap: pswp.toggleDesktopZoom\n\t\t},\n\t\t{ \n\t\t\tname: 'counter', \n\t\t\toption: 'counterEl',\n\t\t\tonInit: function(el) {  \n\t\t\t\t_indexIndicator = el;\n\t\t\t} \n\t\t},\n\t\t{ \n\t\t\tname: 'button--close', \n\t\t\toption: 'closeEl',\n\t\t\tonTap: pswp.close\n\t\t},\n\t\t{ \n\t\t\tname: 'button--arrow--left', \n\t\t\toption: 'arrowEl',\n\t\t\tonTap: pswp.prev\n\t\t},\n\t\t{ \n\t\t\tname: 'button--arrow--right', \n\t\t\toption: 'arrowEl',\n\t\t\tonTap: pswp.next\n\t\t},\n\t\t{ \n\t\t\tname: 'button--fs', \n\t\t\toption: 'fullscreenEl',\n\t\t\tonTap: function() {  \n\t\t\t\tif(_fullscrenAPI.isFullscreen()) {\n\t\t\t\t\t_fullscrenAPI.exit();\n\t\t\t\t} else {\n\t\t\t\t\t_fullscrenAPI.enter();\n\t\t\t\t}\n\t\t\t} \n\t\t},\n\t\t{ \n\t\t\tname: 'preloader', \n\t\t\toption: 'preloaderEl',\n\t\t\tonInit: function(el) {  \n\t\t\t\t_loadingIndicator = el;\n\t\t\t} \n\t\t}\n\n\t];\n\n\tvar _setupUIElements = function() {\n\t\tvar item,\n\t\t\tclassAttr,\n\t\t\tuiElement;\n\n\t\tvar loopThroughChildElements = function(sChildren) {\n\t\t\tif(!sChildren) {\n\t\t\t\treturn;\n\t\t\t}\n\n\t\t\tvar l = sChildren.length;\n\t\t\tfor(var i = 0; i < l; i++) {\n\t\t\t\titem = sChildren[i];\n\t\t\t\tclassAttr = item.className;\n\n\t\t\t\tfor(var a = 0; a < _uiElements.length; a++) {\n\t\t\t\t\tuiElement = _uiElements[a];\n\n\t\t\t\t\tif(classAttr.indexOf('pswp__' + uiElement.name) > -1  ) {\n\n\t\t\t\t\t\tif( _options[uiElement.option] ) { // if element is not disabled from options\n\t\t\t\t\t\t\t\n\t\t\t\t\t\t\tframework.removeClass(item, 'pswp__element--disabled');\n\t\t\t\t\t\t\tif(uiElement.onInit) {\n\t\t\t\t\t\t\t\tuiElement.onInit(item);\n\t\t\t\t\t\t\t}\n\t\t\t\t\t\t\t\n\t\t\t\t\t\t\t//item.style.display = 'block';\n\t\t\t\t\t\t} else {\n\t\t\t\t\t\t\tframework.addClass(item, 'pswp__element--disabled');\n\t\t\t\t\t\t\t//item.style.display = 'none';\n\t\t\t\t\t\t}\n\t\t\t\t\t}\n\t\t\t\t}\n\t\t\t}\n\t\t};\n\t\tloopThroughChildElements(_controls.children);\n\n\t\tvar topBar =  framework.getChildByClass(_controls, 'pswp__top-bar');\n\t\tif(topBar) {\n\t\t\tloopThroughChildElements( topBar.children );\n\t\t}\n\t};\n\n\n\t\n\n\tui.init = function() {\n\n\t\t// extend options\n\t\tframework.extend(pswp.options, _defaultUIOptions, true);\n\n\t\t// create local link for fast access\n\t\t_options = pswp.options;\n\n\t\t// find pswp__ui element\n\t\t_controls = framework.getChildByClass(pswp.scrollWrap, 'pswp__ui');\n\n\t\t// create local link\n\t\t_listen = pswp.listen;\n\n\n\t\t_setupHidingControlsDuringGestures();\n\n\t\t// update controls when slides change\n\t\t_listen('beforeChange', ui.update);\n\n\t\t// toggle zoom on double-tap\n\t\t_listen('doubleTap', function(point) {\n\t\t\tvar initialZoomLevel = pswp.currItem.initialZoomLevel;\n\t\t\tif(pswp.getZoomLevel() !== initialZoomLevel) {\n\t\t\t\tpswp.zoomTo(initialZoomLevel, point, 333);\n\t\t\t} else {\n\t\t\t\tpswp.zoomTo(_options.getDoubleTapZoom(false, pswp.currItem), point, 333);\n\t\t\t}\n\t\t});\n\n\t\t// Allow text selection in caption\n\t\t_listen('preventDragEvent', function(e, isDown, preventObj) {\n\t\t\tvar t = e.target || e.srcElement;\n\t\t\tif(\n\t\t\t\tt && \n\t\t\t\tt.getAttribute('class') && e.type.indexOf('mouse') > -1 && \n\t\t\t\t( t.getAttribute('class').indexOf('__caption') > 0 || (/(SMALL|STRONG|EM)/i).test(t.tagName) ) \n\t\t\t) {\n\t\t\t\tpreventObj.prevent = false;\n\t\t\t}\n\t\t});\n\n\t\t// bind events for UI\n\t\t_listen('bindEvents', function() {\n\t\t\tframework.bind(_controls, 'pswpTap click', _onControlsTap);\n\t\t\tframework.bind(pswp.scrollWrap, 'pswpTap', ui.onGlobalTap);\n\n\t\t\tif(!pswp.likelyTouchDevice) {\n\t\t\t\tframework.bind(pswp.scrollWrap, 'mouseover', ui.onMouseOver);\n\t\t\t}\n\t\t});\n\n\t\t// unbind events for UI\n\t\t_listen('unbindEvents', function() {\n\t\t\tif(!_shareModalHidden) {\n\t\t\t\t_toggleShareModal();\n\t\t\t}\n\n\t\t\tif(_idleInterval) {\n\t\t\t\tclearInterval(_idleInterval);\n\t\t\t}\n\t\t\tframework.unbind(document, 'mouseout', _onMouseLeaveWindow);\n\t\t\tframework.unbind(document, 'mousemove', _onIdleMouseMove);\n\t\t\tframework.unbind(_controls, 'pswpTap click', _onControlsTap);\n\t\t\tframework.unbind(pswp.scrollWrap, 'pswpTap', ui.onGlobalTap);\n\t\t\tframework.unbind(pswp.scrollWrap, 'mouseover', ui.onMouseOver);\n\n\t\t\tif(_fullscrenAPI) {\n\t\t\t\tframework.unbind(document, _fullscrenAPI.eventK, ui.updateFullscreen);\n\t\t\t\tif(_fullscrenAPI.isFullscreen()) {\n\t\t\t\t\t_options.hideAnimationDuration = 0;\n\t\t\t\t\t_fullscrenAPI.exit();\n\t\t\t\t}\n\t\t\t\t_fullscrenAPI = null;\n\t\t\t}\n\t\t});\n\n\n\t\t// clean up things when gallery is destroyed\n\t\t_listen('destroy', function() {\n\t\t\tif(_options.captionEl) {\n\t\t\t\tif(_fakeCaptionContainer) {\n\t\t\t\t\t_controls.removeChild(_fakeCaptionContainer);\n\t\t\t\t}\n\t\t\t\tframework.removeClass(_captionContainer, 'pswp__caption--empty');\n\t\t\t}\n\n\t\t\tif(_shareModal) {\n\t\t\t\t_shareModal.children[0].onclick = null;\n\t\t\t}\n\t\t\tframework.removeClass(_controls, 'pswp__ui--over-close');\n\t\t\tframework.addClass( _controls, 'pswp__ui--hidden');\n\t\t\tui.setIdle(false);\n\t\t});\n\t\t\n\n\t\tif(!_options.showAnimationDuration) {\n\t\t\tframework.removeClass( _controls, 'pswp__ui--hidden');\n\t\t}\n\t\t_listen('initialZoomIn', function() {\n\t\t\tif(_options.showAnimationDuration) {\n\t\t\t\tframework.removeClass( _controls, 'pswp__ui--hidden');\n\t\t\t}\n\t\t});\n\t\t_listen('initialZoomOut', function() {\n\t\t\tframework.addClass( _controls, 'pswp__ui--hidden');\n\t\t});\n\n\t\t_listen('parseVerticalMargin', _applyNavBarGaps);\n\t\t\n\t\t_setupUIElements();\n\n\t\tif(_options.shareEl && _shareButton && _shareModal) {\n\t\t\t_shareModalHidden = true;\n\t\t}\n\n\t\t_countNumItems();\n\n\t\t_setupIdle();\n\n\t\t_setupFullscreenAPI();\n\n\t\t_setupLoadingIndicator();\n\t};\n\n\tui.setIdle = function(isIdle) {\n\t\t_isIdle = isIdle;\n\t\t_togglePswpClass(_controls, 'ui--idle', isIdle);\n\t};\n\n\tui.update = function() {\n\t\t// Don't update UI if it's hidden\n\t\tif(_controlsVisible && pswp.currItem) {\n\t\t\t\n\t\t\tui.updateIndexIndicator();\n\n\t\t\tif(_options.captionEl) {\n\t\t\t\t_options.addCaptionHTMLFn(pswp.currItem, _captionContainer);\n\n\t\t\t\t_togglePswpClass(_captionContainer, 'caption--empty', !pswp.currItem.title);\n\t\t\t}\n\n\t\t\t_overlayUIUpdated = true;\n\n\t\t} else {\n\t\t\t_overlayUIUpdated = false;\n\t\t}\n\n\t\tif(!_shareModalHidden) {\n\t\t\t_toggleShareModal();\n\t\t}\n\n\t\t_countNumItems();\n\t};\n\n\tui.updateFullscreen = function(e) {\n\n\t\tif(e) {\n\t\t\t// some browsers change window scroll position during the fullscreen\n\t\t\t// so PhotoSwipe updates it just in case\n\t\t\tsetTimeout(function() {\n\t\t\t\tpswp.setScrollOffset( 0, framework.getScrollY() );\n\t\t\t}, 50);\n\t\t}\n\t\t\n\t\t// toogle pswp--fs class on root element\n\t\tframework[ (_fullscrenAPI.isFullscreen() ? 'add' : 'remove') + 'Class' ](pswp.template, 'pswp--fs');\n\t};\n\n\tui.updateIndexIndicator = function() {\n\t\tif(_options.counterEl) {\n\t\t\t_indexIndicator.innerHTML = (pswp.getCurrentIndex()+1) + \n\t\t\t\t\t\t\t\t\t\t_options.indexIndicatorSep + \n\t\t\t\t\t\t\t\t\t\t_options.getNumItemsFn();\n\t\t}\n\t};\n\t\n\tui.onGlobalTap = function(e) {\n\t\te = e || window.event;\n\t\tvar target = e.target || e.srcElement;\n\n\t\tif(_blockControlsTap) {\n\t\t\treturn;\n\t\t}\n\n\t\tif(e.detail && e.detail.pointerType === 'mouse') {\n\n\t\t\t// close gallery if clicked outside of the image\n\t\t\tif(_hasCloseClass(target)) {\n\t\t\t\tpswp.close();\n\t\t\t\treturn;\n\t\t\t}\n\n\t\t\tif(framework.hasClass(target, 'pswp__img')) {\n\t\t\t\tif(pswp.getZoomLevel() === 1 && pswp.getZoomLevel() <= pswp.currItem.fitRatio) {\n\t\t\t\t\tif(_options.clickToCloseNonZoomable) {\n\t\t\t\t\t\tpswp.close();\n\t\t\t\t\t}\n\t\t\t\t} else {\n\t\t\t\t\tpswp.toggleDesktopZoom(e.detail.releasePoint);\n\t\t\t\t}\n\t\t\t}\n\t\t\t\n\t\t} else {\n\n\t\t\t// tap anywhere (except buttons) to toggle visibility of controls\n\t\t\tif(_options.tapToToggleControls) {\n\t\t\t\tif(_controlsVisible) {\n\t\t\t\t\tui.hideControls();\n\t\t\t\t} else {\n\t\t\t\t\tui.showControls();\n\t\t\t\t}\n\t\t\t}\n\n\t\t\t// tap to close gallery\n\t\t\tif(_options.tapToClose && (framework.hasClass(target, 'pswp__img') || _hasCloseClass(target)) ) {\n\t\t\t\tpswp.close();\n\t\t\t\treturn;\n\t\t\t}\n\t\t\t\n\t\t}\n\t};\n\tui.onMouseOver = function(e) {\n\t\te = e || window.event;\n\t\tvar target = e.target || e.srcElement;\n\n\t\t// add class when mouse is over an element that should close the gallery\n\t\t_togglePswpClass(_controls, 'ui--over-close', _hasCloseClass(target));\n\t};\n\n\tui.hideControls = function() {\n\t\tframework.addClass(_controls,'pswp__ui--hidden');\n\t\t_controlsVisible = false;\n\t};\n\n\tui.showControls = function() {\n\t\t_controlsVisible = true;\n\t\tif(!_overlayUIUpdated) {\n\t\t\tui.update();\n\t\t}\n\t\tframework.removeClass(_controls,'pswp__ui--hidden');\n\t};\n\n\tui.supportsFullscreen = function() {\n\t\tvar d = document;\n\t\treturn !!(d.exitFullscreen || d.mozCancelFullScreen || d.webkitExitFullscreen || d.msExitFullscreen);\n\t};\n\n\tui.getFullscreenAPI = function() {\n\t\tvar dE = document.documentElement,\n\t\t\tapi,\n\t\t\ttF = 'fullscreenchange';\n\n\t\tif (dE.requestFullscreen) {\n\t\t\tapi = {\n\t\t\t\tenterK: 'requestFullscreen',\n\t\t\t\texitK: 'exitFullscreen',\n\t\t\t\telementK: 'fullscreenElement',\n\t\t\t\teventK: tF\n\t\t\t};\n\n\t\t} else if(dE.mozRequestFullScreen ) {\n\t\t\tapi = {\n\t\t\t\tenterK: 'mozRequestFullScreen',\n\t\t\t\texitK: 'mozCancelFullScreen',\n\t\t\t\telementK: 'mozFullScreenElement',\n\t\t\t\teventK: 'moz' + tF\n\t\t\t};\n\n\t\t\t\n\n\t\t} else if(dE.webkitRequestFullscreen) {\n\t\t\tapi = {\n\t\t\t\tenterK: 'webkitRequestFullscreen',\n\t\t\t\texitK: 'webkitExitFullscreen',\n\t\t\t\telementK: 'webkitFullscreenElement',\n\t\t\t\teventK: 'webkit' + tF\n\t\t\t};\n\n\t\t} else if(dE.msRequestFullscreen) {\n\t\t\tapi = {\n\t\t\t\tenterK: 'msRequestFullscreen',\n\t\t\t\texitK: 'msExitFullscreen',\n\t\t\t\telementK: 'msFullscreenElement',\n\t\t\t\teventK: 'MSFullscreenChange'\n\t\t\t};\n\t\t}\n\n\t\tif(api) {\n\t\t\tapi.enter = function() { \n\t\t\t\t// disable close-on-scroll in fullscreen\n\t\t\t\t_initalCloseOnScrollValue = _options.closeOnScroll; \n\t\t\t\t_options.closeOnScroll = false; \n\n\t\t\t\tif(this.enterK === 'webkitRequestFullscreen') {\n\t\t\t\t\tpswp.template[this.enterK]( Element.ALLOW_KEYBOARD_INPUT );\n\t\t\t\t} else {\n\t\t\t\t\treturn pswp.template[this.enterK](); \n\t\t\t\t}\n\t\t\t};\n\t\t\tapi.exit = function() { \n\t\t\t\t_options.closeOnScroll = _initalCloseOnScrollValue;\n\n\t\t\t\treturn document[this.exitK](); \n\n\t\t\t};\n\t\t\tapi.isFullscreen = function() { return document[this.elementK]; };\n\t\t}\n\n\t\treturn api;\n\t};\n\n\n\n};\nreturn PhotoSwipeUI_Default;\n\n\n});\n"

/***/ }),

/***/ 539:
/***/ (function(module, exports, __webpack_require__) {

// extracted by mini-css-extract-plugin

/***/ }),

/***/ 540:
/***/ (function(module, exports, __webpack_require__) {

// extracted by mini-css-extract-plugin

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
//# sourceMappingURL=photoswipe.js.map