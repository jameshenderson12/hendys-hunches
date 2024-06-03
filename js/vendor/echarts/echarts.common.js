
/*
* Licensed to the Apache Software Foundation (ASF) under one
* or more contributor license agreements.  See the NOTICE file
* distributed with this work for additional information
* regarding copyright ownership.  The ASF licenses this file
* to you under the Apache License, Version 2.0 (the
* "License"); you may not use this file except in compliance
* with the License.  You may obtain a copy of the License at
*
*   http://www.apache.org/licenses/LICENSE-2.0
*
* Unless required by applicable law or agreed to in writing,
* software distributed under the License is distributed on an
* "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
* KIND, either express or implied.  See the License for the
* specific language governing permissions and limitations
* under the License.
*/

(function (global, factory) {
    typeof exports === 'object' && typeof module !== 'undefined' ? factory(exports) :
    typeof define === 'function' && define.amd ? define(['exports'], factory) :
    (global = typeof globalThis !== 'undefined' ? globalThis : global || self, factory(global.echarts = {}));
}(this, (function (exports) { 'use strict';

    /*! *****************************************************************************
    Copyright (c) Microsoft Corporation.

    Permission to use, copy, modify, and/or distribute this software for any
    purpose with or without fee is hereby granted.

    THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH
    REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY
    AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT,
    INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM
    LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR
    OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR
    PERFORMANCE OF THIS SOFTWARE.
    ***************************************************************************** */
    /* global Reflect, Promise */

    var extendStatics = function(d, b) {
        extendStatics = Object.setPrototypeOf ||
            ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
            function (d, b) { for (var p in b) if (Object.prototype.hasOwnProperty.call(b, p)) d[p] = b[p]; };
        return extendStatics(d, b);
    };

    function __extends(d, b) {
        if (typeof b !== "function" && b !== null)
            throw new TypeError("Class extends value " + String(b) + " is not a constructor or null");
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    }

    var Browser = (function () {
        function Browser() {
            this.firefox = false;
            this.ie = false;
            this.edge = false;
            this.newEdge = false;
            this.weChat = false;
        }
        return Browser;
    }());
    var Env = (function () {
        function Env() {
            this.browser = new Browser();
            this.node = false;
            this.wxa = false;
            this.worker = false;
            this.svgSupported = false;
            this.touchEventsSupported = false;
            this.pointerEventsSupported = false;
            this.domSupported = false;
            this.transformSupported = false;
            this.transform3dSupported = false;
            this.hasGlobalWindow = typeof window !== 'undefined';
        }
        return Env;
    }());
    var env = new Env();
    if (typeof wx === 'object' && typeof wx.getSystemInfoSync === 'function') {
        env.wxa = true;
        env.touchEventsSupported = true;
    }
    else if (typeof document === 'undefined' && typeof self !== 'undefined') {
        env.worker = true;
    }
    else if (typeof navigator === 'undefined') {
        env.node = true;
        env.svgSupported = true;
    }
    else {
        detect(navigator.userAgent, env);
    }
    function detect(ua, env) {
        var browser = env.browser;
        var firefox = ua.match(/Firefox\/([\d.]+)/);
        var ie = ua.match(/MSIE\s([\d.]+)/)
            || ua.match(/Trident\/.+?rv:(([\d.]+))/);
        var edge = ua.match(/Edge?\/([\d.]+)/);
        var weChat = (/micromessenger/i).test(ua);
        if (firefox) {
            browser.firefox = true;
            browser.version = firefox[1];
        }
        if (ie) {
            browser.ie = true;
            browser.version = ie[1];
        }
        if (edge) {
            browser.edge = true;
            browser.version = edge[1];
            browser.newEdge = +edge[1].split('.')[0] > 18;
        }
        if (weChat) {
            browser.weChat = true;
        }
        env.svgSupported = typeof SVGRect !== 'undefined';
        env.touchEventsSupported = 'ontouchstart' in window && !browser.ie && !browser.edge;
        env.pointerEventsSupported = 'onpointerdown' in window
            && (browser.edge || (browser.ie && +browser.version >= 11));
        env.domSupported = typeof document !== 'undefined';
        var style = document.documentElement.style;
        env.transform3dSupported = ((browser.ie && 'transition' in style)
            || browser.edge
            || (('WebKitCSSMatrix' in window) && ('m11' in new WebKitCSSMatrix()))
            || 'MozPerspective' in style)
            && !('OTransition' in style);
        env.transformSupported = env.transform3dSupported
            || (browser.ie && +browser.version >= 9);
    }

    var DEFAULT_FONT_SIZE = 12;
    var DEFAULT_FONT_FAMILY = 'sans-serif';
    var DEFAULT_FONT = DEFAULT_FONT_SIZE + "px " + DEFAULT_FONT_FAMILY;
    var OFFSET = 20;
    var SCALE = 100;
    var defaultWidthMapStr = "007LLmW'55;N0500LLLLLLLLLL00NNNLzWW\\\\WQb\\0FWLg\\bWb\\WQ\\WrWWQ000CL5LLFLL0LL**F*gLLLL5F0LF\\FFF5.5N";
    function getTextWidthMap(mapStr) {
        var map = {};
        if (typeof JSON === 'undefined') {
            return map;
        }
        for (var i = 0; i < mapStr.length; i++) {
            var char = String.fromCharCode(i + 32);
            var size = (mapStr.charCodeAt(i) - OFFSET) / SCALE;
            map[char] = size;
        }
        return map;
    }
    var DEFAULT_TEXT_WIDTH_MAP = getTextWidthMap(defaultWidthMapStr);
    var platformApi = {
        createCanvas: function () {
            return typeof document !== 'undefined'
                && document.createElement('canvas');
        },
        measureText: (function () {
            var _ctx;
            var _cachedFont;
            return function (text, font) {
                if (!_ctx) {
                    var canvas = platformApi.createCanvas();
                    _ctx = canvas && canvas.getContext('2d');
                }
                if (_ctx) {
                    if (_cachedFont !== font) {
                        _cachedFont = _ctx.font = font || DEFAULT_FONT;
                    }
                    return _ctx.measureText(text);
                }
                else {
                    text = text || '';
                    font = font || DEFAULT_FONT;
                    var res = /(\d+)px/.exec(font);
                    var fontSize = res && +res[1] || DEFAULT_FONT_SIZE;
                    var width = 0;
                    if (font.indexOf('mono') >= 0) {
                        width = fontSize * text.length;
                    }
                    else {
                        for (var i = 0; i < text.length; i++) {
                            var preCalcWidth = DEFAULT_TEXT_WIDTH_MAP[text[i]];
                            width += preCalcWidth == null ? fontSize : (preCalcWidth * fontSize);
                        }
                    }
                    return { width: width };
                }
            };
        })(),
        loadImage: function (src, onload, onerror) {
            var image = new Image();
            image.onload = onload;
            image.onerror = onerror;
            image.src = src;
            return image;
        }
    };
    function setPlatformAPI(newPlatformApis) {
        for (var key in platformApi) {
            if (newPlatformApis[key]) {
                platformApi[key] = newPlatformApis[key];
            }
        }
    }

    var BUILTIN_OBJECT = reduce([
        'Function',
        'RegExp',
        'Date',
        'Error',
        'CanvasGradient',
        'CanvasPattern',
        'Image',
        'Canvas'
    ], function (obj, val) {
        obj['[object ' + val + ']'] = true;
        return obj;
    }, {});
    var TYPED_ARRAY = reduce([
        'Int8',
        'Uint8',
        'Uint8Clamped',
        'Int16',
        'Uint16',
        'Int32',
        'Uint32',
        'Float32',
        'Float64'
    ], function (obj, val) {
        obj['[object ' + val + 'Array]'] = true;
        return obj;
    }, {});
    var objToString = Object.prototype.toString;
    var arrayProto = Array.prototype;
    var nativeForEach = arrayProto.forEach;
    var nativeFilter = arrayProto.filter;
    var nativeSlice = arrayProto.slice;
    var nativeMap = arrayProto.map;
    var ctorFunction = function () { }.constructor;
    var protoFunction = ctorFunction ? ctorFunction.prototype : null;
    var protoKey = '__proto__';
    var idStart = 0x0907;
    function guid() {
        return idStart++;
    }
    function logError() {
        var args = [];
        for (var _i = 0; _i < arguments.length; _i++) {
            args[_i] = arguments[_i];
        }
        if (typeof console !== 'undefined') {
            console.error.apply(console, args);
        }
    }
    function clone(source) {
        if (source == null || typeof source !== 'object') {
            return source;
        }
        var result = source;
        var typeStr = objToString.call(source);
        if (typeStr === '[object Array]') {
            if (!isPrimitive(source)) {
                result = [];
                for (var i = 0, len = source.length; i < len; i++) {
                    result[i] = clone(source[i]);
                }
            }
        }
        else if (TYPED_ARRAY[typeStr]) {
            if (!isPrimitive(source)) {
                var Ctor = source.constructor;
                if (Ctor.from) {
                    result = Ctor.from(source);
                }
                else {
                    result = new Ctor(source.length);
                    for (var i = 0, len = source.length; i < len; i++) {
                        result[i] = source[i];
                    }
                }
            }
        }
        else if (!BUILTIN_OBJECT[typeStr] && !isPrimitive(source) && !isDom(source)) {
            result = {};
            for (var key in source) {
                if (source.hasOwnProperty(key) && key !== protoKey) {
                    result[key] = clone(source[key]);
                }
            }
        }
        return result;
    }
    function merge(target, source, overwrite) {
        if (!isObject(source) || !isObject(target)) {
            return overwrite ? clone(source) : target;
        }
        for (var key in source) {
            if (source.hasOwnProperty(key) && key !== protoKey) {
                var targetProp = target[key];
                var sourceProp = source[key];
                if (isObject(sourceProp)
                    && isObject(targetProp)
                    && !isArray(sourceProp)
                    && !isArray(targetProp)
                    && !isDom(sourceProp)
                    && !isDom(targetProp)
                    && !isBuiltInObject(sourceProp)
                    && !isBuiltInObject(targetProp)
                    && !isPrimitive(sourceProp)
                    && !isPrimitive(targetProp)) {
                    merge(targetProp, sourceProp, overwrite);
                }
                else if (overwrite || !(key in target)) {
                    target[key] = clone(source[key]);
                }
            }
        }
        return target;
    }
    function mergeAll(targetAndSources, overwrite) {
        var result = targetAndSources[0];
        for (var i = 1, len = targetAndSources.length; i < len; i++) {
            result = merge(result, targetAndSources[i], overwrite);
        }
        return result;
    }
    function extend(target, source) {
        if (Object.assign) {
            Object.assign(target, source);
        }
        else {
            for (var key in source) {
                if (source.hasOwnProperty(key) && key !== protoKey) {
                    target[key] = source[key];
                }
            }
        }
        return target;
    }
    function defaults(target, source, overlay) {
        var keysArr = keys(source);
        for (var i = 0; i < keysArr.length; i++) {
            var key = keysArr[i];
            if ((overlay ? source[key] != null : target[key] == null)) {
                target[key] = source[key];
            }
        }
        return target;
    }
    var createCanvas = platformApi.createCanvas;
    function indexOf(array, value) {
        if (array) {
            if (array.indexOf) {
                return array.indexOf(value);
            }
            for (var i = 0, len = array.length; i < len; i++) {
                if (array[i] === value) {
                    return i;
                }
            }
        }
        return -1;
    }
    function inherits(clazz, baseClazz) {
        var clazzPrototype = clazz.prototype;
        function F() { }
        F.prototype = baseClazz.prototype;
        clazz.prototype = new F();
        for (var prop in clazzPrototype) {
            if (clazzPrototype.hasOwnProperty(prop)) {
                clazz.prototype[prop] = clazzPrototype[prop];
            }
        }
        clazz.prototype.constructor = clazz;
        clazz.superClass = baseClazz;
    }
    function mixin(target, source, override) {
        target = 'prototype' in target ? target.prototype : target;
        source = 'prototype' in source ? source.prototype : source;
        if (Object.getOwnPropertyNames) {
            var keyList = Object.getOwnPropertyNames(source);
            for (var i = 0; i < keyList.length; i++) {
                var key = keyList[i];
                if (key !== 'constructor') {
                    if ((override ? source[key] != null : target[key] == null)) {
                        target[key] = source[key];
                    }
                }
            }
        }
        else {
            defaults(target, source, override);
        }
    }
    function isArrayLike(data) {
        if (!data) {
            return false;
        }
        if (typeof data === 'string') {
            return false;
        }
        return typeof data.length === 'number';
    }
    function each(arr, cb, context) {
        if (!(arr && cb)) {
            return;
        }
        if (arr.forEach && arr.forEach === nativeForEach) {
            arr.forEach(cb, context);
        }
        else if (arr.length === +arr.length) {
            for (var i = 0, len = arr.length; i < len; i++) {
                cb.call(context, arr[i], i, arr);
            }
        }
        else {
            for (var key in arr) {
                if (arr.hasOwnProperty(key)) {
                    cb.call(context, arr[key], key, arr);
                }
            }
        }
    }
    function map(arr, cb, context) {
        if (!arr) {
            return [];
        }
        if (!cb) {
            return slice(arr);
        }
        if (arr.map && arr.map === nativeMap) {
            return arr.map(cb, context);
        }
        else {
            var result = [];
            for (var i = 0, len = arr.length; i < len; i++) {
                result.push(cb.call(context, arr[i], i, arr));
            }
            return result;
        }
    }
    function reduce(arr, cb, memo, context) {
        if (!(arr && cb)) {
            return;
        }
        for (var i = 0, len = arr.length; i < len; i++) {
            memo = cb.call(context, memo, arr[i], i, arr);
        }
        return memo;
    }
    function filter(arr, cb, context) {
        if (!arr) {
            return [];
        }
        if (!cb) {
            return slice(arr);
        }
        if (arr.filter && arr.filter === nativeFilter) {
            return arr.filter(cb, context);
        }
        else {
            var result = [];
            for (var i = 0, len = arr.length; i < len; i++) {
                if (cb.call(context, arr[i], i, arr)) {
                    result.push(arr[i]);
                }
            }
            return result;
        }
    }
    function find(arr, cb, context) {
        if (!(arr && cb)) {
            return;
        }
        for (var i = 0, len = arr.length; i < len; i++) {
            if (cb.call(context, arr[i], i, arr)) {
                return arr[i];
            }
        }
    }
    function keys(obj) {
        if (!obj) {
            return [];
        }
        if (Object.keys) {
            return Object.keys(obj);
        }
        var keyList = [];
        for (var key in obj) {
            if (obj.hasOwnProperty(key)) {
                keyList.push(key);
            }
        }
        return keyList;
    }
    function bindPolyfill(func, context) {
        var args = [];
        for (var _i = 2; _i < arguments.length; _i++) {
            args[_i - 2] = arguments[_i];
        }
        return function () {
            return func.apply(context, args.concat(nativeSlice.call(arguments)));
        };
    }
    var bind = (protoFunction && isFunction(protoFunction.bind))
        ? protoFunction.call.bind(protoFunction.bind)
        : bindPolyfill;
    function curry(func) {
        var args = [];
        for (var _i = 1; _i < arguments.length; _i++) {
            args[_i - 1] = arguments[_i];
        }
        return function () {
            return func.apply(this, args.concat(nativeSlice.call(arguments)));
        };
    }
    function isArray(value) {
        if (Array.isArray) {
            return Array.isArray(value);
        }
        return objToString.call(value) === '[object Array]';
    }
    function isFunction(value) {
        return typeof value === 'function';
    }
    function isString(value) {
        return typeof value === 'string';
    }
    function isStringSafe(value) {
        return objToString.call(value) === '[object String]';
    }
    function isNumber(value) {
        return typeof value === 'number';
    }
    function isObject(value) {
        var type = typeof value;
        return type === 'function' || (!!value && type === 'object');
    }
    function isBuiltInObject(value) {
        return !!BUILTIN_OBJECT[objToString.call(value)];
    }
    function isTypedArray(value) {
        return !!TYPED_ARRAY[objToString.call(value)];
    }
    function isDom(value) {
        return typeof value === 'object'
            && typeof value.nodeType === 'number'
            && typeof value.ownerDocument === 'object';
    }
    function isGradientObject(value) {
        return value.colorStops != null;
    }
    function isImagePatternObject(value) {
        return value.image != null;
    }
    function isRegExp(value) {
        return objToString.call(value) === '[object RegExp]';
    }
    function eqNaN(value) {
        return value !== value;
    }
    function retrieve() {
        var args = [];
        for (var _i = 0; _i < arguments.length; _i++) {
            args[_i] = arguments[_i];
        }
        for (var i = 0, len = args.length; i < len; i++) {
            if (args[i] != null) {
                return args[i];
            }
        }
    }
    function retrieve2(value0, value1) {
        return value0 != null
            ? value0
            : value1;
    }
    function retrieve3(value0, value1, value2) {
        return value0 != null
            ? value0
            : value1 != null
                ? value1
                : value2;
    }
    function slice(arr) {
        var args = [];
        for (var _i = 1; _i < arguments.length; _i++) {
            args[_i - 1] = arguments[_i];
        }
        return nativeSlice.apply(arr, args);
    }
    function normalizeCssArray(val) {
        if (typeof (val) === 'number') {
            return [val, val, val, val];
        }
        var len = val.length;
        if (len === 2) {
            return [val[0], val[1], val[0], val[1]];
        }
        else if (len === 3) {
            return [val[0], val[1], val[2], val[1]];
        }
        return val;
    }
    function assert(condition, message) {
        if (!condition) {
            throw new Error(message);
        }
    }
    function trim(str) {
        if (str == null) {
            return null;
        }
        else if (typeof str.trim === 'function') {
            return str.trim();
        }
        else {
            return str.replace(/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g, '');
        }
    }
    var primitiveKey = '__ec_primitive__';
    function setAsPrimitive(obj) {
        obj[primitiveKey] = true;
    }
    function isPrimitive(obj) {
        return obj[primitiveKey];
    }
    var MapPolyfill = (function () {
        function MapPolyfill() {
            this.data = {};
        }
        MapPolyfill.prototype["delete"] = function (key) {
            var existed = this.has(key);
            if (existed) {
                delete this.data[key];
            }
            return existed;
        };
        MapPolyfill.prototype.has = function (key) {
            return this.data.hasOwnProperty(key);
        };
        MapPolyfill.prototype.get = function (key) {
            return this.data[key];
        };
        MapPolyfill.prototype.set = function (key, value) {
            this.data[key] = value;
            return this;
        };
        MapPolyfill.prototype.keys = function () {
            return keys(this.data);
        };
        MapPolyfill.prototype.forEach = function (callback) {
            var data = this.data;
            for (var key in data) {
                if (data.hasOwnProperty(key)) {
                    callback(data[key], key);
                }
            }
        };
        return MapPolyfill;
    }());
    var isNativeMapSupported = typeof Map === 'function';
    function maybeNativeMap() {
        return (isNativeMapSupported ? new Map() : new MapPolyfill());
    }
    var HashMap = (function () {
        function HashMap(obj) {
            var isArr = isArray(obj);
            this.data = maybeNativeMap();
            var thisMap = this;
            (obj instanceof HashMap)
                ? obj.each(visit)
                : (obj && each(obj, visit));
            function visit(value, key) {
                isArr ? thisMap.set(value, key) : thisMap.set(key, value);
            }
        }
        HashMap.prototype.hasKey = function (key) {
            return this.data.has(key);
        };
        HashMap.prototype.get = function (key) {
            return this.data.get(key);
        };
        HashMap.prototype.set = function (key, value) {
            this.data.set(key, value);
            return value;
        };
        HashMap.prototype.each = function (cb, context) {
            this.data.forEach(function (value, key) {
                cb.call(context, value, key);
            });
        };
        HashMap.prototype.keys = function () {
            var keys = this.data.keys();
            return isNativeMapSupported
                ? Array.from(keys)
                : keys;
        };
        HashMap.prototype.removeKey = function (key) {
            this.data["delete"](key);
        };
        return HashMap;
    }());
    function createHashMap(obj) {
        return new HashMap(obj);
    }
    function concatArray(a, b) {
        var newArray = new a.constructor(a.length + b.length);
        for (var i = 0; i < a.length; i++) {
            newArray[i] = a[i];
        }
        var offset = a.length;
        for (var i = 0; i < b.length; i++) {
            newArray[i + offset] = b[i];
        }
        return newArray;
    }
    function createObject(proto, properties) {
        var obj;
        if (Object.create) {
            obj = Object.create(proto);
        }
        else {
            var StyleCtor = function () { };
            StyleCtor.prototype = proto;
            obj = new StyleCtor();
        }
        if (properties) {
            extend(obj, properties);
        }
        return obj;
    }
    function disableUserSelect(dom) {
        var domStyle = dom.style;
        domStyle.webkitUserSelect = 'none';
        domStyle.userSelect = 'none';
        domStyle.webkitTapHighlightColor = 'rgba(0,0,0,0)';
        domStyle['-webkit-touch-callout'] = 'none';
    }
    function hasOwn(own, prop) {
        return own.hasOwnProperty(prop);
    }
    function noop() { }
    var RADIAN_TO_DEGREE = 180 / Math.PI;

    var util = /*#__PURE__*/Object.freeze({
        __proto__: null,
        guid: guid,
        logError: logError,
        clone: clone,
        merge: merge,
        mergeAll: mergeAll,
        extend: extend,
        defaults: defaults,
        createCanvas: createCanvas,
        indexOf: indexOf,
        inherits: inherits,
        mixin: mixin,
        isArrayLike: isArrayLike,
        each: each,
        map: map,
        reduce: reduce,
        filter: filter,
        find: find,
        keys: keys,
        bind: bind,
        curry: curry,
        isArray: isArray,
        isFunction: isFunction,
        isString: isString,
        isStringSafe: isStringSafe,
        isNumber: isNumber,
        isObject: isObject,
        isBuiltInObject: isBuiltInObject,
        isTypedArray: isTypedArray,
        isDom: isDom,
        isGradientObject: isGradientObject,
        isImagePatternObject: isImagePatternObject,
        isRegExp: isRegExp,
        eqNaN: eqNaN,
        retrieve: retrieve,
        retrieve2: retrieve2,
        retrieve3: retrieve3,
        slice: slice,
        normalizeCssArray: normalizeCssArray,
        assert: assert,
        trim: trim,
        setAsPrimitive: setAsPrimitive,
        isPrimitive: isPrimitive,
        HashMap: HashMap,
        createHashMap: createHashMap,
        concatArray: concatArray,
        createObject: createObject,
        disableUserSelect: disableUserSelect,
        hasOwn: hasOwn,
        noop: noop,
        RADIAN_TO_DEGREE: RADIAN_TO_DEGREE
    });

    function create(x, y) {
        if (x == null) {
            x = 0;
        }
        if (y == null) {
            y = 0;
        }
        return [x, y];
    }
    function copy(out, v) {
        out[0] = v[0];
        out[1] = v[1];
        return out;
    }
    function clone$1(v) {
        return [v[0], v[1]];
    }
    function set(out, a, b) {
        out[0] = a;
        out[1] = b;
        return out;
    }
    function add(out, v1, v2) {
        out[0] = v1[0] + v2[0];
        out[1] = v1[1] + v2[1];
        return out;
    }
    function scaleAndAdd(out, v1, v2, a) {
        out[0] = v1[0] + v2[0] * a;
        out[1] = v1[1] + v2[1] * a;
        return out;
    }
    function sub(out, v1, v2) {
        out[0] = v1[0] - v2[0];
        out[1] = v1[1] - v2[1];
        return out;
    }
    function len(v) {
        return Math.sqrt(lenSquare(v));
    }
    var length = len;
    function lenSquare(v) {
        return v[0] * v[0] + v[1] * v[1];
    }
    var lengthSquare = lenSquare;
    function mul(out, v1, v2) {
        out[0] = v1[0] * v2[0];
        out[1] = v1[1] * v2[1];
        return out;
    }
    function div(out, v1, v2) {
        out[0] = v1[0] / v2[0];
        out[1] = v1[1] / v2[1];
        return out;
    }
    function dot(v1, v2) {
        return v1[0] * v2[0] + v1[1] * v2[1];
    }
    function scale(out, v, s) {
        out[0] = v[0] * s;
        out[1] = v[1] * s;
        return out;
    }
    function normalize(out, v) {
        var d = len(v);
        if (d === 0) {
            out[0] = 0;
            out[1] = 0;
        }
        else {
            out[0] = v[0] / d;
            out[1] = v[1] / d;
        }
        return out;
    }
    function distance(v1, v2) {
        return Math.sqrt((v1[0] - v2[0]) * (v1[0] - v2[0])
            + (v1[1] - v2[1]) * (v1[1] - v2[1]));
    }
    var dist = distance;
    function distanceSquare(v1, v2) {
        return (v1[0] - v2[0]) * (v1[0] - v2[0])
            + (v1[1] - v2[1]) * (v1[1] - v2[1]);
    }
    var distSquare = distanceSquare;
    function negate(out, v) {
        out[0] = -v[0];
        out[1] = -v[1];
        return out;
    }
    function lerp(out, v1, v2, t) {
        out[0] = v1[0] + t * (v2[0] - v1[0]);
        out[1] = v1[1] + t * (v2[1] - v1[1]);
        return out;
    }
    function applyTransform(out, v, m) {
        var x = v[0];
        var y = v[1];
        out[0] = m[0] * x + m[2] * y + m[4];
        out[1] = m[1] * x + m[3] * y + m[5];
        return out;
    }
    function min(out, v1, v2) {
        out[0] = Math.min(v1[0], v2[0]);
        out[1] = Math.min(v1[1], v2[1]);
        return out;
    }
    function max(out, v1, v2) {
        out[0] = Math.max(v1[0], v2[0]);
        out[1] = Math.max(v1[1], v2[1]);
        return out;
    }

    var vector = /*#__PURE__*/Object.freeze({
        __proto__: null,
        create: create,
        copy: copy,
        clone: clone$1,
        set: set,
        add: add,
        scaleAndAdd: scaleAndAdd,
        sub: sub,
        len: len,
        length: length,
        lenSquare: lenSquare,
        lengthSquare: lengthSquare,
        mul: mul,
        div: div,
        dot: dot,
        scale: scale,
        normalize: normalize,
        distance: distance,
        dist: dist,
        distanceSquare: distanceSquare,
        distSquare: distSquare,
        negate: negate,
        lerp: lerp,
        applyTransform: applyTransform,
        min: min,
        max: max
    });

    var Param = (function () {
        function Param(target, e) {
            this.target = target;
            this.topTarget = e && e.topTarget;
        }
        return Param;
    }());
    var Draggable = (function () {
        function Draggable(handler) {
            this.handler = handler;
            handler.on('mousedown', this._dragStart, this);
            handler.on('mousemove', this._drag, this);
            handler.on('mouseup', this._dragEnd, this);
        }
        Draggable.prototype._dragStart = function (e) {
            var draggingTarget = e.target;
            while (draggingTarget && !draggingTarget.draggable) {
                draggingTarget = draggingTarget.parent || draggingTarget.__hostTarget;
            }
            if (draggingTarget) {
                this._draggingTarget = draggingTarget;
                draggingTarget.dragging = true;
                this._x = e.offsetX;
                this._y = e.offsetY;
                this.handler.dispatchToElement(new Param(draggingTarget, e), 'dragstart', e.event);
            }
        };
        Draggable.prototype._drag = function (e) {
            var draggingTarget = this._draggingTarget;
            if (draggingTarget) {
                var x = e.offsetX;
                var y = e.offsetY;
                var dx = x - this._x;
                var dy = y - this._y;
                this._x = x;
                this._y = y;
                draggingTarget.drift(dx, dy, e);
                this.handler.dispatchToElement(new Param(draggingTarget, e), 'drag', e.event);
                var dropTarget = this.handler.findHover(x, y, draggingTarget).target;
                var lastDropTarget = this._dropTarget;
                this._dropTarget = dropTarget;
                if (draggingTarget !== dropTarget) {
                    if (lastDropTarget && dropTarget !== lastDropTarget) {
                        this.handler.dispatchToElement(new Param(lastDropTarget, e), 'dragleave', e.event);
                    }
                    if (dropTarget && dropTarget !== lastDropTarget) {
                        this.handler.dispatchToElement(new Param(dropTarget, e), 'dragenter', e.event);
                    }
                }
            }
        };
        Draggable.prototype._dragEnd = function (e) {
            var draggingTarget = this._draggingTarget;
            if (draggingTarget) {
                draggingTarget.dragging = false;
            }
            this.handler.dispatchToElement(new Param(draggingTarget, e), 'dragend', e.event);
            if (this._dropTarget) {
                this.handler.dispatchToElement(new Param(this._dropTarget, e), 'drop', e.event);
            }
            this._draggingTarget = null;
            this._dropTarget = null;
        };
        return Draggable;
    }());

    var Eventful = (function () {
        function Eventful(eventProcessors) {
            if (eventProcessors) {
                this._$eventProcessor = eventProcessors;
            }
        }
        Eventful.prototype.on = function (event, query, handler, context) {
            if (!this._$handlers) {
                this._$handlers = {};
            }
            var _h = this._$handlers;
            if (typeof query === 'function') {
                context = handler;
                handler = query;
                query = null;
            }
            if (!handler || !event) {
                return this;
            }
            var eventProcessor = this._$eventProcessor;
            if (query != null && eventProcessor && eventProcessor.normalizeQuery) {
                query = eventProcessor.normalizeQuery(query);
            }
            if (!_h[event]) {
                _h[event] = [];
            }
            for (var i = 0; i < _h[event].length; i++) {
                if (_h[event][i].h === handler) {
                    return this;
                }
            }
            var wrap = {
                h: handler,
                query: query,
                ctx: (context || this),
                callAtLast: handler.zrEventfulCallAtLast
            };
            var lastIndex = _h[event].length - 1;
            var lastWrap = _h[event][lastIndex];
            (lastWrap && lastWrap.callAtLast)
                ? _h[event].splice(lastIndex, 0, wrap)
                : _h[event].push(wrap);
            return this;
        };
        Eventful.prototype.isSilent = function (eventName) {
            var _h = this._$handlers;
            return !_h || !_h[eventName] || !_h[eventName].length;
        };
        Eventful.prototype.off = function (eventType, handler) {
            var _h = this._$handlers;
            if (!_h) {
                return this;
            }
            if (!eventType) {
                this._$handlers = {};
                return this;
            }
            if (handler) {
                if (_h[eventType]) {
                    var newList = [];
                    for (var i = 0, l = _h[eventType].length; i < l; i++) {
                        if (_h[eventType][i].h !== handler) {
                            newList.push(_h[eventType][i]);
                        }
                    }
                    _h[eventType] = newList;
                }
                if (_h[eventType] && _h[eventType].length === 0) {
                    delete _h[eventType];
                }
            }
            else {
                delete _h[eventType];
            }
            return this;
        };
        Eventful.prototype.trigger = function (eventType) {
            var args = [];
            for (var _i = 1; _i < arguments.length; _i++) {
                args[_i - 1] = arguments[_i];
            }
            if (!this._$handlers) {
                return this;
            }
            var _h = this._$handlers[eventType];
            var eventProcessor = this._$eventProcessor;
            if (_h) {
                var argLen = args.length;
                var len = _h.length;
                for (var i = 0; i < len; i++) {
                    var hItem = _h[i];
                    if (eventProcessor
                        && eventProcessor.filter
                        && hItem.query != null
                        && !eventProcessor.filter(eventType, hItem.query)) {
                        continue;
                    }
                    switch (argLen) {
                        case 0:
                            hItem.h.call(hItem.ctx);
                            break;
                        case 1:
                            hItem.h.call(hItem.ctx, args[0]);
                            break;
                        case 2:
                            hItem.h.call(hItem.ctx, args[0], args[1]);
                            break;
                        default:
                            hItem.h.apply(hItem.ctx, args);
                            break;
                    }
                }
            }
            eventProcessor && eventProcessor.afterTrigger
                && eventProcessor.afterTrigger(eventType);
            return this;
        };
        Eventful.prototype.triggerWithContext = function (type) {
            var args = [];
            for (var _i = 1; _i < arguments.length; _i++) {
                args[_i - 1] = arguments[_i];
            }
            if (!this._$handlers) {
                return this;
            }
            var _h = this._$handlers[type];
            var eventProcessor = this._$eventProcessor;
            if (_h) {
                var argLen = args.length;
                var ctx = args[argLen - 1];
                var len = _h.length;
                for (var i = 0; i < len; i++) {
                    var hItem = _h[i];
                    if (eventProcessor
                        && eventProcessor.filter
                        && hItem.query != null
                        && !eventProcessor.filter(type, hItem.query)) {
                        continue;
                    }
                    switch (argLen) {
                        case 0:
                            hItem.h.call(ctx);
                            break;
                        case 1:
                            hItem.h.call(ctx, args[0]);
                            break;
                        case 2:
                            hItem.h.call(ctx, args[0], args[1]);
                            break;
                        default:
                            hItem.h.apply(ctx, args.slice(1, argLen - 1));
                            break;
                    }
                }
            }
            eventProcessor && eventProcessor.afterTrigger
                && eventProcessor.afterTrigger(type);
            return this;
        };
        return Eventful;
    }());

    var LN2 = Math.log(2);
    function determinant(rows, rank, rowStart, rowMask, colMask, detCache) {
        var cacheKey = rowMask + '-' + colMask;
        var fullRank = rows.length;
        if (detCache.hasOwnProperty(cacheKey)) {
            return detCache[cacheKey];
        }
        if (rank === 1) {
            var colStart = Math.round(Math.log(((1 << fullRank) - 1) & ~colMask) / LN2);
            return rows[rowStart][colStart];
        }
        var subRowMask = rowMask | (1 << rowStart);
        var subRowStart = rowStart + 1;
        while (rowMask & (1 << subRowStart)) {
            subRowStart++;
        }
        var sum = 0;
        for (var j = 0, colLocalIdx = 0; j < fullRank; j++) {
            var colTag = 1 << j;
            if (!(colTag & colMask)) {
                sum += (colLocalIdx % 2 ? -1 : 1) * rows[rowStart][j]
                    * determinant(rows, rank - 1, subRowStart, subRowMask, colMask | colTag, detCache);
                colLocalIdx++;
            }
        }
        detCache[cacheKey] = sum;
        return sum;
    }
    function buildTransformer(src, dest) {
        var mA = [
            [src[0], src[1], 1, 0, 0, 0, -dest[0] * src[0], -dest[0] * src[1]],
            [0, 0, 0, src[0], src[1], 1, -dest[1] * src[0], -dest[1] * src[1]],
            [src[2], src[3], 1, 0, 0, 0, -dest[2] * src[2], -dest[2] * src[3]],
            [0, 0, 0, src[2], src[3], 1, -dest[3] * src[2], -dest[3] * src[3]],
            [src[4], src[5], 1, 0, 0, 0, -dest[4] * src[4], -dest[4] * src[5]],
            [0, 0, 0, src[4], src[5], 1, -dest[5] * src[4], -dest[5] * src[5]],
            [src[6], src[7], 1, 0, 0, 0, -dest[6] * src[6], -dest[6] * src[7]],
            [0, 0, 0, src[6], src[7], 1, -dest[7] * src[6], -dest[7] * src[7]]
        ];
        var detCache = {};
        var det = determinant(mA, 8, 0, 0, 0, detCache);
        if (det === 0) {
            return;
        }
        var vh = [];
        for (var i = 0; i < 8; i++) {
            for (var j = 0; j < 8; j++) {
                vh[j] == null && (vh[j] = 0);
                vh[j] += ((i + j) % 2 ? -1 : 1)
                    * determinant(mA, 7, i === 0 ? 1 : 0, 1 << i, 1 << j, detCache)
                    / det * dest[i];
            }
        }
        return function (out, srcPointX, srcPointY) {
            var pk = srcPointX * vh[6] + srcPointY * vh[7] + 1;
            out[0] = (srcPointX * vh[0] + srcPointY * vh[1] + vh[2]) / pk;
            out[1] = (srcPointX * vh[3] + srcPointY * vh[4] + vh[5]) / pk;
        };
    }

    var EVENT_SAVED_PROP = '___zrEVENTSAVED';
    var _calcOut = [];
    function transformLocalCoord(out, elFrom, elTarget, inX, inY) {
        return transformCoordWithViewport(_calcOut, elFrom, inX, inY, true)
            && transformCoordWithViewport(out, elTarget, _calcOut[0], _calcOut[1]);
    }
    function transformCoordWithViewport(out, el, inX, inY, inverse) {
        if (el.getBoundingClientRect && env.domSupported && !isCanvasEl(el)) {
            var saved = el[EVENT_SAVED_PROP] || (el[EVENT_SAVED_PROP] = {});
            var markers = prepareCoordMarkers(el, saved);
            var transformer = preparePointerTransformer(markers, saved, inverse);
            if (transformer) {
                transformer(out, inX, inY);
                return true;
            }
        }
        return false;
    }
    function prepareCoordMarkers(el, saved) {
        var markers = saved.markers;
        if (markers) {
            return markers;
        }
        markers = saved.markers = [];
        var propLR = ['left', 'right'];
        var propTB = ['top', 'bottom'];
        for (var i = 0; i < 4; i++) {
            var marker = document.createElement('div');
            var stl = marker.style;
            var idxLR = i % 2;
            var idxTB = (i >> 1) % 2;
            stl.cssText = [
                'position: absolute',
                'visibility: hidden',
                'padding: 0',
                'margin: 0',
                'border-width: 0',
                'user-select: none',
                'width:0',
                'height:0',
                propLR[idxLR] + ':0',
                propTB[idxTB] + ':0',
                propLR[1 - idxLR] + ':auto',
                propTB[1 - idxTB] + ':auto',
                ''
            ].join('!important;');
            el.appendChild(marker);
            markers.push(marker);
        }
        return markers;
    }
    function preparePointerTransformer(markers, saved, inverse) {
        var transformerName = inverse ? 'invTrans' : 'trans';
        var transformer = saved[transformerName];
        var oldSrcCoords = saved.srcCoords;
        var srcCoords = [];
        var destCoords = [];
        var oldCoordTheSame = true;
        for (var i = 0; i < 4; i++) {
            var rect = markers[i].getBoundingClientRect();
            var ii = 2 * i;
            var x = rect.left;
            var y = rect.top;
            srcCoords.push(x, y);
            oldCoordTheSame = oldCoordTheSame && oldSrcCoords && x === oldSrcCoords[ii] && y === oldSrcCoords[ii + 1];
            destCoords.push(markers[i].offsetLeft, markers[i].offsetTop);
        }
        return (oldCoordTheSame && transformer)
            ? transformer
            : (saved.srcCoords = srcCoords,
                saved[transformerName] = inverse
                    ? buildTransformer(destCoords, srcCoords)
                    : buildTransformer(srcCoords, destCoords));
    }
    function isCanvasEl(el) {
        return el.nodeName.toUpperCase() === 'CANVAS';
    }
    var replaceReg = /([&<>"'])/g;
    var replaceMap = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        '\'': '&#39;'
    };
    function encodeHTML(source) {
        return source == null
            ? ''
            : (source + '').replace(replaceReg, function (str, c) {
                return replaceMap[c];
            });
    }

    var MOUSE_EVENT_REG = /^(?:mouse|pointer|contextmenu|drag|drop)|click/;
    var _calcOut$1 = [];
    var firefoxNotSupportOffsetXY = env.browser.firefox
        && +env.browser.version.split('.')[0] < 39;
    function clientToLocal(el, e, out, calculate) {
        out = out || {};
        if (calculate) {
            calculateZrXY(el, e, out);
        }
        else if (firefoxNotSupportOffsetXY
            && e.layerX != null
            && e.layerX !== e.offsetX) {
            out.zrX = e.layerX;
            out.zrY = e.layerY;
        }
        else if (e.offsetX != null) {
            out.zrX = e.offsetX;
            out.zrY = e.offsetY;
        }
        else {
            calculateZrXY(el, e, out);
        }
        return out;
    }
    function calculateZrXY(el, e, out) {
        if (env.domSupported && el.getBoundingClientRect) {
            var ex = e.clientX;
            var ey = e.clientY;
            if (isCanvasEl(el)) {
                var box = el.getBoundingClientRect();
                out.zrX = ex - box.left;
                out.zrY = ey - box.top;
                return;
            }
            else {
                if (transformCoordWithViewport(_calcOut$1, el, ex, ey)) {
                    out.zrX = _calcOut$1[0];
                    out.zrY = _calcOut$1[1];
                    return;
                }
            }
        }
        out.zrX = out.zrY = 0;
    }
    function getNativeEvent(e) {
        return e
            || window.event;
    }
    function normalizeEvent(el, e, calculate) {
        e = getNativeEvent(e);
        if (e.zrX != null) {
            return e;
        }
        var eventType = e.type;
        var isTouch = eventType && eventType.indexOf('touch') >= 0;
        if (!isTouch) {
            clientToLocal(el, e, e, calculate);
            var wheelDelta = getWheelDeltaMayPolyfill(e);
            e.zrDelta = wheelDelta ? wheelDelta / 120 : -(e.detail || 0) / 3;
        }
        else {
            var touch = eventType !== 'touchend'
                ? e.targetTouches[0]
                : e.changedTouches[0];
            touch && clientToLocal(el, touch, e, calculate);
        }
        var button = e.button;
        if (e.which == null && button !== undefined && MOUSE_EVENT_REG.test(e.type)) {
            e.which = (button & 1 ? 1 : (button & 2 ? 3 : (button & 4 ? 2 : 0)));
        }
        return e;
    }
    function getWheelDeltaMayPolyfill(e) {
        var rawWheelDelta = e.wheelDelta;
        if (rawWheelDelta) {
            return rawWheelDelta;
        }
        var deltaX = e.deltaX;
        var deltaY = e.deltaY;
        if (deltaX == null || deltaY == null) {
            return rawWheelDelta;
        }
        var delta = deltaY !== 0 ? Math.abs(deltaY) : Math.abs(deltaX);
        var sign = deltaY > 0 ? -1
            : deltaY < 0 ? 1
                : deltaX > 0 ? -1
                    : 1;
        return 3 * delta * sign;
    }
    function addEventListener(el, name, handler, opt) {
        el.addEventListener(name, handler, opt);
    }
    function removeEventListener(el, name, handler, opt) {
        el.removeEventListener(name, handler, opt);
    }
    var stop = function (e) {
        e.preventDefault();
        e.stopPropagation();
        e.cancelBubble = true;
    };
    function isMiddleOrRightButtonOnMouseUpDown(e) {
        return e.which === 2 || e.which === 3;
    }

    var GestureMgr = (function () {
        function GestureMgr() {
            this._track = [];
        }
        GestureMgr.prototype.recognize = function (event, target, root) {
            this._doTrack(event, target, root);
            return this._recognize(event);
        };
        GestureMgr.prototype.clear = function () {
            this._track.length = 0;
            return this;
        };
        GestureMgr.prototype._doTrack = function (event, target, root) {
            var touches = event.touches;
            if (!touches) {
                return;
            }
            var trackItem = {
                points: [],
                touches: [],
                target: target,
                event: event
            };
            for (var i = 0, len = touches.length; i < len; i++) {
                var touch = touches[i];
                var pos = clientToLocal(root, touch, {});
                trackItem.points.push([pos.zrX, pos.zrY]);
                trackItem.touches.push(touch);
            }
            this._track.push(trackItem);
        };
        GestureMgr.prototype._recognize = function (event) {
            for (var eventName in recognizers) {
                if (recognizers.hasOwnProperty(eventName)) {
                    var gestureInfo = recognizers[eventName](this._track, event);
                    if (gestureInfo) {
                        return gestureInfo;
                    }
                }
            }
        };
        return GestureMgr;
    }());
    function dist$1(pointPair) {
        var dx = pointPair[1][0] - pointPair[0][0];
        var dy = pointPair[1][1] - pointPair[0][1];
        return Math.sqrt(dx * dx + dy * dy);
    }
    function center(pointPair) {
        return [
            (pointPair[0][0] + pointPair[1][0]) / 2,
            (pointPair[0][1] + pointPair[1][1]) / 2
        ];
    }
    var recognizers = {
        pinch: function (tracks, event) {
            var trackLen = tracks.length;
            if (!trackLen) {
                return;
            }
            var pinchEnd = (tracks[trackLen - 1] || {}).points;
            var pinchPre = (tracks[trackLen - 2] || {}).points || pinchEnd;
            if (pinchPre
                && pinchPre.length > 1
                && pinchEnd
                && pinchEnd.length > 1) {
                var pinchScale = dist$1(pinchEnd) / dist$1(pinchPre);
                !isFinite(pinchScale) && (pinchScale = 1);
                event.pinchScale = pinchScale;
                var pinchCenter = center(pinchEnd);
                event.pinchX = pinchCenter[0];
                event.pinchY = pinchCenter[1];
                return {
                    type: 'pinch',
                    target: tracks[0].target,
                    event: event
                };
            }
        }
    };

    function create$1() {
        return [1, 0, 0, 1, 0, 0];
    }
    function identity(out) {
        out[0] = 1;
        out[1] = 0;
        out[2] = 0;
        out[3] = 1;
        out[4] = 0;
        out[5] = 0;
        return out;
    }
    function copy$1(out, m) {
        out[0] = m[0];
        out[1] = m[1];
        out[2] = m[2];
        out[3] = m[3];
        out[4] = m[4];
        out[5] = m[5];
        return out;
    }
    function mul$1(out, m1, m2) {
        var out0 = m1[0] * m2[0] + m1[2] * m2[1];
        var out1 = m1[1] * m2[0] + m1[3] * m2[1];
        var out2 = m1[0] * m2[2] + m1[2] * m2[3];
        var out3 = m1[1] * m2[2] + m1[3] * m2[3];
        var out4 = m1[0] * m2[4] + m1[2] * m2[5] + m1[4];
        var out5 = m1[1] * m2[4] + m1[3] * m2[5] + m1[5];
        out[0] = out0;
        out[1] = out1;
        out[2] = out2;
        out[3] = out3;
        out[4] = out4;
        out[5] = out5;
        return out;
    }
    function translate(out, a, v) {
        out[0] = a[0];
        out[1] = a[1];
        out[2] = a[2];
        out[3] = a[3];
        out[4] = a[4] + v[0];
        out[5] = a[5] + v[1];
        return out;
    }
    function rotate(out, a, rad) {
        var aa = a[0];
        var ac = a[2];
        var atx = a[4];
        var ab = a[1];
        var ad = a[3];
        var aty = a[5];
        var st = Math.sin(rad);
        var ct = Math.cos(rad);
        out[0] = aa * ct + ab * st;
        out[1] = -aa * st + ab * ct;
        out[2] = ac * ct + ad * st;
        out[3] = -ac * st + ct * ad;
        out[4] = ct * atx + st * aty;
        out[5] = ct * aty - st * atx;
        return out;
    }
    function scale$1(out, a, v) {
        var vx = v[0];
        var vy = v[1];
        out[0] = a[0] * vx;
        out[1] = a[1] * vy;
        out[2] = a[2] * vx;
        out[3] = a[3] * vy;
        out[4] = a[4] * vx;
        out[5] = a[5] * vy;
        return out;
    }
    function invert(out, a) {
        var aa = a[0];
        var ac = a[2];
        var atx = a[4];
        var ab = a[1];
        var ad = a[3];
        var aty = a[5];
        var det = aa * ad - ab * ac;
        if (!det) {
            return null;
        }
        det = 1.0 / det;
        out[0] = ad * det;
        out[1] = -ab * det;
        out[2] = -ac * det;
        out[3] = aa * det;
        out[4] = (ac * aty - ad * atx) * det;
        out[5] = (ab * atx - aa * aty) * det;
        return out;
    }
    function clone$2(a) {
        var b = create$1();
        copy$1(b, a);
        return b;
    }

    var matrix = /*#__PURE__*/Object.freeze({
        __proto__: null,
        create: create$1,
        identity: identity,
        copy: copy$1,
        mul: mul$1,
        translate: translate,
        rotate: rotate,
        scale: scale$1,
        invert: invert,
        clone: clone$2
    });

    var Point = (function () {
        function Point(x, y) {
            this.x = x || 0;
            this.y = y || 0;
        }
        Point.prototype.copy = function (other) {
            this.x = other.x;
            this.y = other.y;
            return this;
        };
        Point.prototype.clone = function () {
            return new Point(this.x, this.y);
        };
        Point.prototype.set = function (x, y) {
            this.x = x;
            this.y = y;
            return this;
        };
        Point.prototype.equal = function (other) {
            return other.x === this.x && other.y === this.y;
        };
        Point.prototype.add = function (other) {
            this.x += other.x;
            this.y += other.y;
            return this;
        };
        Point.prototype.scale = function (scalar) {
            this.x *= scalar;
            this.y *= scalar;
        };
        Point.prototype.scaleAndAdd = function (other, scalar) {
            this.x += other.x * scalar;
            this.y += other.y * scalar;
        };
        Point.prototype.sub = function (other) {
            this.x -= other.x;
            this.y -= other.y;
            return this;
        };
        Point.prototype.dot = function (other) {
            return this.x * other.x + this.y * other.y;
        };
        Point.prototype.len = function () {
            return Math.sqrt(this.x * this.x + this.y * this.y);
        };
        Point.prototype.lenSquare = function () {
            return this.x * this.x + this.y * this.y;
        };
        Point.prototype.normalize = function () {
            var len = this.len();
            this.x /= len;
            this.y /= len;
            return this;
        };
        Point.prototype.distance = function (other) {
            var dx = this.x - other.x;
            var dy = this.y - other.y;
            return Math.sqrt(dx * dx + dy * dy);
        };
        Point.prototype.distanceSquare = function (other) {
            var dx = this.x - other.x;
            var dy = this.y - other.y;
            return dx * dx + dy * dy;
        };
        Point.prototype.negate = function () {
            this.x = -this.x;
            this.y = -this.y;
            return this;
        };
        Point.prototype.transform = function (m) {
            if (!m) {
                return;
            }
            var x = this.x;
            var y = this.y;
            this.x = m[0] * x + m[2] * y + m[4];
            this.y = m[1] * x + m[3] * y + m[5];
            return this;
        };
        Point.prototype.toArray = function (out) {
            out[0] = this.x;
            out[1] = this.y;
            return out;
        };
        Point.prototype.fromArray = function (input) {
            this.x = input[0];
            this.y = input[1];
        };
        Point.set = function (p, x, y) {
            p.x = x;
            p.y = y;
        };
        Point.copy = function (p, p2) {
            p.x = p2.x;
            p.y = p2.y;
        };
        Point.len = function (p) {
            return Math.sqrt(p.x * p.x + p.y * p.y);
        };
        Point.lenSquare = function (p) {
            return p.x * p.x + p.y * p.y;
        };
        Point.dot = function (p0, p1) {
            return p0.x * p1.x + p0.y * p1.y;
        };
        Point.add = function (out, p0, p1) {
            out.x = p0.x + p1.x;
            out.y = p0.y + p1.y;
        };
        Point.sub = function (out, p0, p1) {
            out.x = p0.x - p1.x;
            out.y = p0.y - p1.y;
        };
        Point.scale = function (out, p0, scalar) {
            out.x = p0.x * scalar;
            out.y = p0.y * scalar;
        };
        Point.scaleAndAdd = function (out, p0, p1, scalar) {
            out.x = p0.x + p1.x * scalar;
            out.y = p0.y + p1.y * scalar;
        };
        Point.lerp = function (out, p0, p1, t) {
            var onet = 1 - t;
            out.x = onet * p0.x + t * p1.x;
            out.y = onet * p0.y + t * p1.y;
        };
        return Point;
    }());

    var mathMin = Math.min;
    var mathMax = Math.max;
    var lt = new Point();
    var rb = new Point();
    var lb = new Point();
    var rt = new Point();
    var minTv = new Point();
    var maxTv = new Point();
    var BoundingRect = (function () {
        function BoundingRect(x, y, width, height) {
            if (width < 0) {
                x = x + width;
                width = -width;
            }
            if (height < 0) {
                y = y + height;
                height = -height;
            }
            this.x = x;
            this.y = y;
            this.width = width;
            this.height = height;
        }
        BoundingRect.prototype.union = function (other) {
            var x = mathMin(other.x, this.x);
            var y = mathMin(other.y, this.y);
            if (isFinite(this.x) && isFinite(this.width)) {
                this.width = mathMax(other.x + other.width, this.x + this.width) - x;
            }
            else {
                this.width = other.width;
            }
            if (isFinite(this.y) && isFinite(this.height)) {
                this.height = mathMax(other.y + other.height, this.y + this.height) - y;
            }
            else {
                this.height = other.height;
            }
            this.x = x;
            this.y = y;
        };
        BoundingRect.prototype.applyTransform = function (m) {
            BoundingRect.applyTransform(this, this, m);
        };
        BoundingRect.prototype.calculateTransform = function (b) {
            var a = this;
            var sx = b.width / a.width;
            var sy = b.height / a.height;
            var m = create$1();
            translate(m, m, [-a.x, -a.y]);
            scale$1(m, m, [sx, sy]);
            translate(m, m, [b.x, b.y]);
            return m;
        };
        BoundingRect.prototype.intersect = function (b, mtv) {
            if (!b) {
                return false;
            }
            if (!(b instanceof BoundingRect)) {
                b = BoundingRect.create(b);
            }
            var a = this;
            var ax0 = a.x;
            var ax1 = a.x + a.width;
            var ay0 = a.y;
            var ay1 = a.y + a.height;
            var bx0 = b.x;
            var bx1 = b.x + b.width;
            var by0 = b.y;
            var by1 = b.y + b.height;
            var overlap = !(ax1 < bx0 || bx1 < ax0 || ay1 < by0 || by1 < ay0);
            if (mtv) {
                var dMin = Infinity;
                var dMax = 0;
                var d0 = Math.abs(ax1 - bx0);
                var d1 = Math.abs(bx1 - ax0);
                var d2 = Math.abs(ay1 - by0);
                var d3 = Math.abs(by1 - ay0);
                var dx = Math.min(d0, d1);
                var dy = Math.min(d2, d3);
                if (ax1 < bx0 || bx1 < ax0) {
                    if (dx > dMax) {
                        dMax = dx;
                        if (d0 < d1) {
                            Point.set(maxTv, -d0, 0);
                        }
                        else {
                            Point.set(maxTv, d1, 0);
                        }
                    }
                }
                else {
                    if (dx < dMin) {
                        dMin = dx;
                        if (d0 < d1) {
                            Point.set(minTv, d0, 0);
                        }
                        else {
                            Point.set(minTv, -d1, 0);
                        }
                    }
                }
                if (ay1 < by0 || by1 < ay0) {
                    if (dy > dMax) {
                        dMax = dy;
                        if (d2 < d3) {
                            Point.set(maxTv, 0, -d2);
                        }
                        else {
                            Point.set(maxTv, 0, d3);
                        }
                    }
                }
                else {
                    if (dx < dMin) {
                        dMin = dx;
                        if (d2 < d3) {
                            Point.set(minTv, 0, d2);
                        }
                        else {
                            Point.set(minTv, 0, -d3);
                        }
                    }
                }
            }
            if (mtv) {
                Point.copy(mtv, overlap ? minTv : maxTv);
            }
            return overlap;
        };
        BoundingRect.prototype.contain = function (x, y) {
            var rect = this;
            return x >= rect.x
                && x <= (rect.x + rect.width)
                && y >= rect.y
                && y <= (rect.y + rect.height);
        };
        BoundingRect.prototype.clone = function () {
            return new BoundingRect(this.x, this.y, this.width, this.height);
        };
        BoundingRect.prototype.copy = function (other) {
            BoundingRect.copy(this, other);
        };
        BoundingRect.prototype.plain = function () {
            return {
                x: this.x,
                y: this.y,
                width: this.width,
                height: this.height
            };
        };
        BoundingRect.prototype.isFinite = function () {
            return isFinite(this.x)
                && isFinite(this.y)
                && isFinite(this.width)
                && isFinite(this.height);
        };
        BoundingRect.prototype.isZero = function () {
            return this.width === 0 || this.height === 0;
        };
        BoundingRect.create = function (rect) {
            return new BoundingRect(rect.x, rect.y, rect.width, rect.height);
        };
        BoundingRect.copy = function (target, source) {
            target.x = source.x;
            target.y = source.y;
            target.width = source.width;
            target.height = source.height;
        };
        BoundingRect.applyTransform = function (target, source, m) {
            if (!m) {
                if (target !== source) {
                    BoundingRect.copy(target, source);
                }
                return;
            }
            if (m[1] < 1e-5 && m[1] > -1e-5 && m[2] < 1e-5 && m[2] > -1e-5) {
                var sx = m[0];
                var sy = m[3];
                var tx = m[4];
                var ty = m[5];
                target.x = source.x * sx + tx;
                target.y = source.y * sy + ty;
                target.width = source.width * sx;
                target.height = source.height * sy;
                if (target.width < 0) {
                    target.x += target.width;
                    target.width = -target.width;
                }
                if (target.height < 0) {
                    target.y += target.height;
                    target.height = -target.height;
                }
                return;
            }
            lt.x = lb.x = source.x;
            lt.y = rt.y = source.y;
            rb.x = rt.x = source.x + source.width;
            rb.y = lb.y = source.y + source.height;
            lt.transform(m);
            rt.transform(m);
            rb.transform(m);
            lb.transform(m);
            target.x = mathMin(lt.x, rb.x, lb.x, rt.x);
            target.y = mathMin(lt.y, rb.y, lb.y, rt.y);
            var maxX = mathMax(lt.x, rb.x, lb.x, rt.x);
            var maxY = mathMax(lt.y, rb.y, lb.y, rt.y);
            target.width = maxX - target.x;
            target.height = maxY - target.y;
        };
        return BoundingRect;
    }());

    var SILENT = 'silent';
    function makeEventPacket(eveType, targetInfo, event) {
        return {
            type: eveType,
            event: event,
            target: targetInfo.target,
            topTarget: targetInfo.topTarget,
            cancelBubble: false,
            offsetX: event.zrX,
            offsetY: event.zrY,
            gestureEvent: event.gestureEvent,
            pinchX: event.pinchX,
            pinchY: event.pinchY,
            pinchScale: event.pinchScale,
            wheelDelta: event.zrDelta,
            zrByTouch: event.zrByTouch,
            which: event.which,
            stop: stopEvent
        };
    }
    function stopEvent() {
        stop(this.event);
    }
    var EmptyProxy = (function (_super) {
        __extends(EmptyProxy, _super);
        function EmptyProxy() {
            var _this = _super !== null && _super.apply(this, arguments) || this;
            _this.handler = null;
            return _this;
        }
        EmptyProxy.prototype.dispose = function () { };
        EmptyProxy.prototype.setCursor = function () { };
        return EmptyProxy;
    }(Eventful));
    var HoveredResult = (function () {
        function HoveredResult(x, y) {
            this.x = x;
            this.y = y;
        }
        return HoveredResult;
    }());
    var handlerNames = [
        'click', 'dblclick', 'mousewheel', 'mouseout',
        'mouseup', 'mousedown', 'mousemove', 'contextmenu'
    ];
    var tmpRect = new BoundingRect(0, 0, 0, 0);
    var Handler = (function (_super) {
        __extends(Handler, _super);
        function Handler(storage, painter, proxy, painterRoot, pointerSize) {
            var _this = _super.call(this) || this;
            _this._hovered = new HoveredResult(0, 0);
            _this.storage = storage;
            _this.painter = painter;
            _this.painterRoot = painterRoot;
            _this._pointerSize = pointerSize;
            proxy = proxy || new EmptyProxy();
            _this.proxy = null;
            _this.setHandlerProxy(proxy);
            _this._draggingMgr = new Draggable(_this);
            return _this;
        }
        Handler.prototype.setHandlerProxy = function (proxy) {
            if (this.proxy) {
                this.proxy.dispose();
            }
            if (proxy) {
                each(handlerNames, function (name) {
                    proxy.on && proxy.on(name, this[name], this);
                }, this);
                proxy.handler = this;
            }
            this.proxy = proxy;
        };
        Handler.prototype.mousemove = function (event) {
            var x = event.zrX;
            var y = event.zrY;
            var isOutside = isOutsideBoundary(this, x, y);
            var lastHovered = this._hovered;
            var lastHoveredTarget = lastHovered.target;
            if (lastHoveredTarget && !lastHoveredTarget.__zr) {
                lastHovered = this.findHover(lastHovered.x, lastHovered.y);
                lastHoveredTarget = lastHovered.target;
            }
            var hovered = this._hovered = isOutside ? new HoveredResult(x, y) : this.findHover(x, y);
            var hoveredTarget = hovered.target;
            var proxy = this.proxy;
            proxy.setCursor && proxy.setCursor(hoveredTarget ? hoveredTarget.cursor : 'default');
            if (lastHoveredTarget && hoveredTarget !== lastHoveredTarget) {
                this.dispatchToElement(lastHovered, 'mouseout', event);
            }
            this.dispatchToElement(hovered, 'mousemove', event);
            if (hoveredTarget && hoveredTarget !== lastHoveredTarget) {
                this.dispatchToElement(hovered, 'mouseover', event);
            }
        };
        Handler.prototype.mouseout = function (event) {
            var eventControl = event.zrEventControl;
            if (eventControl !== 'only_globalout') {
                this.dispatchToElement(this._hovered, 'mouseout', event);
            }
            if (eventControl !== 'no_globalout') {
                this.trigger('globalout', { type: 'globalout', event: event });
            }
        };
        Handler.prototype.resize = function () {
            this._hovered = new HoveredResult(0, 0);
        };
        Handler.prototype.dispatch = function (eventName, eventArgs) {
            var handler = this[eventName];
            handler && handler.call(this, eventArgs);
        };
        Handler.prototype.dispose = function () {
            this.proxy.dispose();
            this.storage = null;
            this.proxy = null;
            this.painter = null;
        };
        Handler.prototype.setCursorStyle = function (cursorStyle) {
            var proxy = this.proxy;
            proxy.setCursor && proxy.setCursor(cursorStyle);
        };
        Handler.prototype.dispatchToElement = function (targetInfo, eventName, event) {
            targetInfo = targetInfo || {};
            var el = targetInfo.target;
            if (el && el.silent) {
                return;
            }
            var eventKey = ('on' + eventName);
            var eventPacket = makeEventPacket(eventName, targetInfo, event);
            while (el) {
                el[eventKey]
                    && (eventPacket.cancelBubble = !!el[eventKey].call(el, eventPacket));
                el.trigger(eventName, eventPacket);
                el = el.__hostTarget ? el.__hostTarget : el.parent;
                if (eventPacket.cancelBubble) {
                    break;
                }
            }
            if (!eventPacket.cancelBubble) {
                this.trigger(eventName, eventPacket);
                if (this.painter && this.painter.eachOtherLayer) {
                    this.painter.eachOtherLayer(function (layer) {
                        if (typeof (layer[eventKey]) === 'function') {
                            layer[eventKey].call(layer, eventPacket);
                        }
                        if (layer.trigger) {
                            layer.trigger(eventName, eventPacket);
                        }
                    });
                }
            }
        };
        Handler.prototype.findHover = function (x, y, exclude) {
            var list = this.storage.getDisplayList();
            var out = new HoveredResult(x, y);
            setHoverTarget(list, out, x, y, exclude);
            if (this._pointerSize && !out.target) {
                var candidates = [];
                var pointerSize = this._pointerSize;
                var targetSizeHalf = pointerSize / 2;
                var pointerRect = new BoundingRect(x - targetSizeHalf, y - targetSizeHalf, pointerSize, pointerSize);
                for (var i = list.length - 1; i >= 0; i--) {
                    var el = list[i];
                    if (el !== exclude
                        && !el.ignore
                        && !el.ignoreCoarsePointer
                        && (!el.parent || !el.parent.ignoreCoarsePointer)) {
                        tmpRect.copy(el.getBoundingRect());
                        if (el.transform) {
                            tmpRect.applyTransform(el.transform);
                        }
                        if (tmpRect.intersect(pointerRect)) {
                            candidates.push(el);
                        }
                    }
                }
                if (candidates.length) {
                    var rStep = 4;
                    var thetaStep = Math.PI / 12;
                    var PI2 = Math.PI * 2;
                    for (var r = 0; r < targetSizeHalf; r += rStep) {
                        for (var theta = 0; theta < PI2; theta += thetaStep) {
                            var x1 = x + r * Math.cos(theta);
                            var y1 = y + r * Math.sin(theta);
                            setHoverTarget(candidates, out, x1, y1, exclude);
                            if (out.target) {
                                return out;
                            }
                        }
                    }
                }
            }
            return out;
        };
        Handler.prototype.processGesture = function (event, stage) {
            if (!this._gestureMgr) {
                this._gestureMgr = new GestureMgr();
            }
            var gestureMgr = this._gestureMgr;
            stage === 'start' && gestureMgr.clear();
            var gestureInfo = gestureMgr.recognize(event, this.findHover(event.zrX, event.zrY, null).target, this.proxy.dom);
            stage === 'end' && gestureMgr.clear();
            if (gestureInfo) {
                var type = gestureInfo.type;
                event.gestureEvent = type;
                var res = new HoveredResult();
                res.target = gestureInfo.target;
                this.dispatchToElement(res, type, gestureInfo.event);
            }
        };
        return Handler;
    }(Eventful));
    each(['click', 'mousedown', 'mouseup', 'mousewheel', 'dblclick', 'contextmenu'], function (name) {
        Handler.prototype[name] = function (event) {
            var x = event.zrX;
            var y = event.zrY;
            var isOutside = isOutsideBoundary(this, x, y);
            var hovered;
            var hoveredTarget;
            if (name !== 'mouseup' || !isOutside) {
                hovered = this.findHover(x, y);
                hoveredTarget = hovered.target;
            }
            if (name === 'mousedown') {
                this._downEl = hoveredTarget;
                this._downPoint = [event.zrX, event.zrY];
                this._upEl = hoveredTarget;
            }
            else if (name === 'mouseup') {
                this._upEl = hoveredTarget;
            }
            else if (name === 'click') {
                if (this._downEl !== this._upEl
                    || !this._downPoint
                    || dist(this._downPoint, [event.zrX, event.zrY]) > 4) {
                    return;
                }
                this._downPoint = null;
            }
            this.dispatchToElement(hovered, name, event);
        };
    });
    function isHover(displayable, x, y) {
        if (displayable[displayable.rectHover ? 'rectContain' : 'contain'](x, y)) {
            var el = displayable;
            var isSilent = void 0;
            var ignoreClip = false;
            while (el) {
                if (el.ignoreClip) {
                    ignoreClip = true;
                }
                if (!ignoreClip) {
                    var clipPath = el.getClipPath();
                    if (clipPath && !clipPath.contain(x, y)) {
                        return false;
                    }
                    if (el.silent) {
                        isSilent = true;
                    }
                }
                var hostEl = el.__hostTarget;
                el = hostEl ? hostEl : el.parent;
            }
            return isSilent ? SILENT : true;
        }
        return false;
    }
    function setHoverTarget(list, out, x, y, exclude) {
        for (var i = list.length - 1; i >= 0; i--) {
            var el = list[i];
            var hoverCheckResult = void 0;
            if (el !== exclude
                && !el.ignore
                && (hoverCheckResult = isHover(el, x, y))) {
                !out.topTarget && (out.topTarget = el);
                if (hoverCheckResult !== SILENT) {
                    out.target = el;
                    break;
                }
            }
        }
    }
    function isOutsideBoundary(handlerInstance, x, y) {
        var painter = handlerInstance.painter;
        return x < 0 || x > painter.getWidth() || y < 0 || y > painter.getHeight();
    }

    var DEFAULT_MIN_MERGE = 32;
    var DEFAULT_MIN_GALLOPING = 7;
    function minRunLength(n) {
        var r = 0;
        while (n >= DEFAULT_MIN_MERGE) {
            r |= n & 1;
            n >>= 1;
        }
        return n + r;
    }
    function makeAscendingRun(array, lo, hi, compare) {
        var runHi = lo + 1;
        if (runHi === hi) {
            return 1;
        }
        if (compare(array[runHi++], array[lo]) < 0) {
            while (runHi < hi && compare(array[runHi], array[runHi - 1]) < 0) {
                runHi++;
            }
            reverseRun(array, lo, runHi);
        }
        else {
            while (runHi < hi && compare(array[runHi], array[runHi - 1]) >= 0) {
                runHi++;
            }
        }
        return runHi - lo;
    }
    function reverseRun(array, lo, hi) {
        hi--;
        while (lo < hi) {
            var t = array[lo];
            array[lo++] = array[hi];
            array[hi--] = t;
        }
    }
    function binaryInsertionSort(array, lo, hi, start, compare) {
        if (start === lo) {
            start++;
        }
        for (; start < hi; start++) {
            var pivot = array[start];
            var left = lo;
            var right = start;
            var mid;
            while (left < right) {
                mid = left + right >>> 1;
                if (compare(pivot, array[mid]) < 0) {
                    right = mid;
                }
                else {
                    left = mid + 1;
                }
            }
            var n = start - left;
            switch (n) {
                case 3:
                    array[left + 3] = array[left + 2];
                case 2:
                    array[left + 2] = array[left + 1];
                case 1:
                    array[left + 1] = array[left];
                    break;
                default:
                    while (n > 0) {
                        array[left + n] = array[left + n - 1];
                        n--;
                    }
            }
            array[left] = pivot;
        }
    }
    function gallopLeft(value, array, start, length, hint, compare) {
        var lastOffset = 0;
        var maxOffset = 0;
        var offset = 1;
        if (compare(value, array[start + hint]) > 0) {
            maxOffset = length - hint;
            while (offset < maxOffset && compare(value, array[start + hint + offset]) > 0) {
                lastOffset = offset;
                offset = (offset << 1) + 1;
                if (offset <= 0) {
                    offset = maxOffset;
                }
            }
            if (offset > maxOffset) {
                offset = maxOffset;
            }
            lastOffset += hint;
            offset += hint;
        }
        else {
            maxOffset = hint + 1;
            while (offset < maxOffset && compare(value, array[start + hint - offset]) <= 0) {
                lastOffset = offset;
                offset = (offset << 1) + 1;
                if (offset <= 0) {
                    offset = maxOffset;
                }
            }
            if (offset > maxOffset) {
                offset = maxOffset;
            }
            var tmp = lastOffset;
            lastOffset = hint - offset;
            offset = hint - tmp;
        }
        lastOffset++;
        while (lastOffset < offset) {
            var m = lastOffset + (offset - lastOffset >>> 1);
            if (compare(value, array[start + m]) > 0) {
                lastOffset = m + 1;
            }
            else {
                offset = m;
            }
        }
        return offset;
    }
    function gallopRight(value, array, start, length, hint, compare) {
        var lastOffset = 0;
        var maxOffset = 0;
        var offset = 1;
        if (compare(value, array[start + hint]) < 0) {
            maxOffset = hint + 1;
            while (offset < maxOffset && compare(value, array[start + hint - offset]) < 0) {
                lastOffset = offset;
                offset = (offset << 1) + 1;
                if (offset <= 0) {
                    offset = maxOffset;
                }
            }
            if (offset > maxOffset) {
                offset = maxOffset;
            }
            var tmp = lastOffset;
            lastOffset = hint - offset;
            offset = hint - tmp;
        }
        else {
            maxOffset = length - hint;
            while (offset < maxOffset && compare(value, array[start + hint + offset]) >= 0) {
                lastOffset = offset;
                offset = (offset << 1) + 1;
                if (offset <= 0) {
                    offset = maxOffset;
                }
            }
            if (offset > maxOffset) {
                offset = maxOffset;
            }
            lastOffset += hint;
            offset += hint;
        }
        lastOffset++;
        while (lastOffset < offset) {
            var m = lastOffset + (offset - lastOffset >>> 1);
            if (compare(value, array[start + m]) < 0) {
                offset = m;
            }
            else {
                lastOffset = m + 1;
            }
        }
        return offset;
    }
    function TimSort(array, compare) {
        var minGallop = DEFAULT_MIN_GALLOPING;
        var length = 0;
        var runStart;
        var runLength;
        var stackSize = 0;
        length = array.length;
        var tmp = [];
        runStart = [];
        runLength = [];
        function pushRun(_runStart, _runLength) {
            runStart[stackSize] = _runStart;
            runLength[stackSize] = _runLength;
            stackSize += 1;
        }
        function mergeRuns() {
            while (stackSize > 1) {
                var n = stackSize - 2;
                if ((n >= 1 && runLength[n - 1] <= runLength[n] + runLength[n + 1])
                    || (n >= 2 && runLength[n - 2] <= runLength[n] + runLength[n - 1])) {
                    if (runLength[n - 1] < runLength[n + 1]) {
                        n--;
                    }
                }
                else if (runLength[n] > runLength[n + 1]) {
                    break;
                }
                mergeAt(n);
            }
        }
        function forceMergeRuns() {
            while (stackSize > 1) {
                var n = stackSize - 2;
                if (n > 0 && runLength[n - 1] < runLength[n + 1]) {
                    n--;
                }
                mergeAt(n);
            }
        }
        function mergeAt(i) {
            var start1 = runStart[i];
            var length1 = runLength[i];
            var start2 = runStart[i + 1];
            var length2 = runLength[i + 1];
            runLength[i] = length1 + length2;
            if (i === stackSize - 3) {
                runStart[i + 1] = runStart[i + 2];
                runLength[i + 1] = runLength[i + 2];
            }
            stackSize--;
            var k = gallopRight(array[start2], array, start1, length1, 0, compare);
            start1 += k;
            length1 -= k;
            if (length1 === 0) {
                return;
            }
            length2 = gallopLeft(array[start1 + length1 - 1], array, start2, length2, length2 - 1, compare);
            if (length2 === 0) {
                return;
            }
            if (length1 <= length2) {
                mergeLow(start1, length1, start2, length2);
            }
            else {
                mergeHigh(start1, length1, start2, length2);
            }
        }
        function mergeLow(start1, length1, start2, length2) {
            var i = 0;
            for (i = 0; i < length1; i++) {
                tmp[i] = array[start1 + i];
            }
            var cursor1 = 0;
            var cursor2 = start2;
            var dest = start1;
            array[dest++] = array[cursor2++];
            if (--length2 === 0) {
                for (i = 0; i < length1; i++) {
                    array[dest + i] = tmp[cursor1 + i];
                }
                return;
            }
            if (length1 === 1) {
                for (i = 0; i < length2; i++) {
                    array[dest + i] = array[cursor2 + i];
                }
                array[dest + length2] = tmp[cursor1];
                return;
            }
            var _minGallop = minGallop;
            var count1;
            var count2;
            var exit;
            while (1) {
                count1 = 0;
                count2 = 0;
                exit = false;
                do {
                    if (compare(array[cursor2], tmp[cursor1]) < 0) {
                        array[dest++] = array[cursor2++];
                        count2++;
                        count1 = 0;
                        if (--length2 === 0) {
                            exit = true;
                            break;
                        }
                    }
                    else {
                        array[dest++] = tmp[cursor1++];
                        count1++;
                        count2 = 0;
                        if (--length1 === 1) {
                            exit = true;
                            break;
                        }
                    }
                } while ((count1 | count2) < _minGallop);
                if (exit) {
                    break;
                }
                do {
                    count1 = gallopRight(array[cursor2], tmp, cursor1, length1, 0, compare);
                    if (count1 !== 0) {
                        for (i = 0; i < count1; i++) {
                            array[dest + i] = tmp[cursor1 + i];
                        }
                        dest += count1;
                        cursor1 += count1;
                        length1 -= count1;
                        if (length1 <= 1) {
                            exit = true;
                            break;
                        }
                    }
                    array[dest++] = array[cursor2++];
                    if (--length2 === 0) {
                        exit = true;
                        break;
                    }
                    count2 = gallopLeft(tmp[cursor1], array, cursor2, length2, 0, compare);
                    if (count2 !== 0) {
                        for (i = 0; i < count2; i++) {
                            array[dest + i] = array[cursor2 + i];
                        }
                        dest += count2;
                        cursor2 += count2;
                        length2 -= count2;
                        if (length2 === 0) {
                            exit = true;
                            break;
                        }
                    }
                    array[dest++] = tmp[cursor1++];
                    if (--length1 === 1) {
                        exit = true;
                        break;
                    }
                    _minGallop--;
                } while (count1 >= DEFAULT_MIN_GALLOPING || count2 >= DEFAULT_MIN_GALLOPING);
                if (exit) {
                    break;
                }
                if (_minGallop < 0) {
                    _minGallop = 0;
                }
                _minGallop += 2;
            }
            minGallop = _minGallop;
            minGallop < 1 && (minGallop = 1);
            if (length1 === 1) {
                for (i = 0; i < length2; i++) {
                    array[dest + i] = array[cursor2 + i];
                }
                array[dest + length2] = tmp[cursor1];
            }
            else if (length1 === 0) {
                throw new Error();
            }
            else {
                for (i = 0; i < length1; i++) {
                    array[dest + i] = tmp[cursor1 + i];
                }
            }
        }
        function mergeHigh(start1, length1, start2, length2) {
            var i = 0;
            for (i = 0; i < length2; i++) {
                tmp[i] = array[start2 + i];
            }
            var cursor1 = start1 + length1 - 1;
            var cursor2 = length2 - 1;
            var dest = start2 + length2 - 1;
            var customCursor = 0;
            var customDest = 0;
            array[dest--] = array[cursor1--];
            if (--length1 === 0) {
                customCursor = dest - (length2 - 1);
                for (i = 0; i < length2; i++) {
                    array[customCursor + i] = tmp[i];
                }
                return;
            }
            if (length2 === 1) {
                dest -= length1;
                cursor1 -= length1;
                customDest = dest + 1;
                customCursor = cursor1 + 1;
                for (i = length1 - 1; i >= 0; i--) {
                    array[customDest + i] = array[customCursor + i];
                }
                array[dest] = tmp[cursor2];
                return;
            }
            var _minGallop = minGallop;
            while (true) {
                var count1 = 0;
                var count2 = 0;
                var exit = false;
                do {
                    if (compare(tmp[cursor2], array[cursor1]) < 0) {
                        array[dest--] = array[cursor1--];
                        count1++;
                        count2 = 0;
                        if (--length1 === 0) {
                            exit = true;
                            break;
                        }
                    }
                    else {
                        array[dest--] = tmp[cursor2--];
                        count2++;
                        count1 = 0;
                        if (--length2 === 1) {
                            exit = true;
                            break;
                        }
                    }
                } while ((count1 | count2) < _minGallop);
                if (exit) {
                    break;
                }
                do {
                    count1 = length1 - gallopRight(tmp[cursor2], array, start1, length1, length1 - 1, compare);
                    if (count1 !== 0) {
                        dest -= count1;
                        cursor1 -= count1;
                        length1 -= count1;
                        customDest = dest + 1;
                        customCursor = cursor1 + 1;
                        for (i = count1 - 1; i >= 0; i--) {
                            array[customDest + i] = array[customCursor + i];
                        }
                        if (length1 === 0) {
                            exit = true;
                            break;
                        }
                    }
                    array[dest--] = tmp[cursor2--];
                    if (--length2 === 1) {
                        exit = true;
                        break;
                    }
                    count2 = length2 - gallopLeft(array[cursor1], tmp, 0, length2, length2 - 1, compare);
                    if (count2 !== 0) {
                        dest -= count2;
                        cursor2 -= count2;
                        length2 -= count2;
                        customDest = dest + 1;
                        customCursor = cursor2 + 1;
                        for (i = 0; i < count2; i++) {
                            array[customDest + i] = tmp[customCursor + i];
                        }
                        if (length2 <= 1) {
                            exit = true;
                            break;
                        }
                    }
                    array[dest--] = array[cursor1--];
                    if (--length1 === 0) {
                        exit = true;
                        break;
                    }
                    _minGallop--;
                } while (count1 >= DEFAULT_MIN_GALLOPING || count2 >= DEFAULT_MIN_GALLOPING);
                if (exit) {
                    break;
                }
                if (_minGallop < 0) {
                    _minGallop = 0;
                }
                _minGallop += 2;
            }
            minGallop = _minGallop;
            if (minGallop < 1) {
                minGallop = 1;
            }
            if (length2 === 1) {
                dest -= length1;
                cursor1 -= length1;
                customDest = dest + 1;
                customCursor = cursor1 + 1;
                for (i = length1 - 1; i >= 0; i--) {
                    array[customDest + i] = array[customCursor + i];
                }
                array[dest] = tmp[cursor2];
            }
            else if (length2 === 0) {
                throw new Error();
            }
            else {
                customCursor = dest - (length2 - 1);
                for (i = 0; i < length2; i++) {
                    array[customCursor + i] = tmp[i];
                }
            }
        }
        return {
            mergeRuns: mergeRuns,
            forceMergeRuns: forceMergeRuns,
            pushRun: pushRun
        };
    }
    function sort(array, compare, lo, hi) {
        if (!lo) {
            lo = 0;
        }
        if (!hi) {
            hi = array.length;
        }
        var remaining = hi - lo;
        if (remaining < 2) {
            return;
        }
        var runLength = 0;
        if (remaining < DEFAULT_MIN_MERGE) {
            runLength = makeAscendingRun(array, lo, hi, compare);
            binaryInsertionSort(array, lo, hi, lo + runLength, compare);
            return;
        }
        var ts = TimSort(array, compare);
        var minRun = minRunLength(remaining);
        do {
            runLength = makeAscendingRun(array, lo, hi, compare);
            if (runLength < minRun) {
                var force = remaining;
                if (force > minRun) {
                    force = minRun;
                }
                binaryInsertionSort(array, lo, lo + force, lo + runLength, compare);
                runLength = force;
            }
            ts.pushRun(lo, runLength);
            ts.mergeRuns();
            remaining -= runLength;
            lo += runLength;
        } while (remaining !== 0);
        ts.forceMergeRuns();
    }

    var REDRAW_BIT = 1;
    var STYLE_CHANGED_BIT = 2;
    var SHAPE_CHANGED_BIT = 4;

    var invalidZErrorLogged = false;
    function logInvalidZError() {
        if (invalidZErrorLogged) {
            return;
        }
        invalidZErrorLogged = true;
        console.warn('z / z2 / zlevel of displayable is invalid, which may cause unexpected errors');
    }
    function shapeCompareFunc(a, b) {
        if (a.zlevel === b.zlevel) {
            if (a.z === b.z) {
                return a.z2 - b.z2;
            }
            return a.z - b.z;
        }
        return a.zlevel - b.zlevel;
    }
    var Storage = (function () {
        function Storage() {
            this._roots = [];
            this._displayList = [];
            this._displayListLen = 0;
            this.displayableSortFunc = shapeCompareFunc;
        }
        Storage.prototype.traverse = function (cb, context) {
            for (var i = 0; i < this._roots.length; i++) {
                this._roots[i].traverse(cb, context);
            }
        };
        Storage.prototype.getDisplayList = function (update, includeIgnore) {
            includeIgnore = includeIgnore || false;
            var displayList = this._displayList;
            if (update || !displayList.length) {
                this.updateDisplayList(includeIgnore);
            }
            return displayList;
        };
        Storage.prototype.updateDisplayList = function (includeIgnore) {
            this._displayListLen = 0;
            var roots = this._roots;
            var displayList = this._displayList;
            for (var i = 0, len = roots.length; i < len; i++) {
                this._updateAndAddDisplayable(roots[i], null, includeIgnore);
            }
            displayList.length = this._displayListLen;
            sort(displayList, shapeCompareFunc);
        };
        Storage.prototype._updateAndAddDisplayable = function (el, clipPaths, includeIgnore) {
            if (el.ignore && !includeIgnore) {
                return;
            }
            el.beforeUpdate();
            el.update();
            el.afterUpdate();
            var userSetClipPath = el.getClipPath();
            if (el.ignoreClip) {
                clipPaths = null;
            }
            else if (userSetClipPath) {
                if (clipPaths) {
                    clipPaths = clipPaths.slice();
                }
                else {
                    clipPaths = [];
                }
                var currentClipPath = userSetClipPath;
                var parentClipPath = el;
                while (currentClipPath) {
                    currentClipPath.parent = parentClipPath;
                    currentClipPath.updateTransform();
                    clipPaths.push(currentClipPath);
                    parentClipPath = currentClipPath;
                    currentClipPath = currentClipPath.getClipPath();
                }
            }
            if (el.childrenRef) {
                var children = el.childrenRef();
                for (var i = 0; i < children.length; i++) {
                    var child = children[i];
                    if (el.__dirty) {
                        child.__dirty |= REDRAW_BIT;
                    }
                    this._updateAndAddDisplayable(child, clipPaths, includeIgnore);
                }
                el.__dirty = 0;
            }
            else {
                var disp = el;
                if (clipPaths && clipPaths.length) {
                    disp.__clipPaths = clipPaths;
                }
                else if (disp.__clipPaths && disp.__clipPaths.length > 0) {
                    disp.__clipPaths = [];
                }
                if (isNaN(disp.z)) {
                    logInvalidZError();
                    disp.z = 0;
                }
                if (isNaN(disp.z2)) {
                    logInvalidZError();
                    disp.z2 = 0;
                }
                if (isNaN(disp.zlevel)) {
                    logInvalidZError();
                    disp.zlevel = 0;
                }
                this._displayList[this._displayListLen++] = disp;
            }
            var decalEl = el.getDecalElement && el.getDecalElement();
            if (decalEl) {
                this._updateAndAddDisplayable(decalEl, clipPaths, includeIgnore);
            }
            var textGuide = el.getTextGuideLine();
            if (textGuide) {
                this._updateAndAddDisplayable(textGuide, clipPaths, includeIgnore);
            }
            var textEl = el.getTextContent();
            if (textEl) {
                this._updateAndAddDisplayable(textEl, clipPaths, includeIgnore);
            }
        };
        Storage.prototype.addRoot = function (el) {
            if (el.__zr && el.__zr.storage === this) {
                return;
            }
            this._roots.push(el);
        };
        Storage.prototype.delRoot = function (el) {
            if (el instanceof Array) {
                for (var i = 0, l = el.length; i < l; i++) {
                    this.delRoot(el[i]);
                }
                return;
            }
            var idx = indexOf(this._roots, el);
            if (idx >= 0) {
                this._roots.splice(idx, 1);
            }
        };
        Storage.prototype.delAllRoots = function () {
            this._roots = [];
            this._displayList = [];
            this._displayListLen = 0;
            return;
        };
        Storage.prototype.getRoots = function () {
            return this._roots;
        };
        Storage.prototype.dispose = function () {
            this._displayList = null;
            this._roots = null;
        };
        return Storage;
    }());

    var requestAnimationFrame;
    requestAnimationFrame = (env.hasGlobalWindow
        && ((window.requestAnimationFrame && window.requestAnimationFrame.bind(window))
            || (window.msRequestAnimationFrame && window.msRequestAnimationFrame.bind(window))
            || window.mozRequestAnimationFrame
            || window.webkitRequestAnimationFrame)) || function (func) {
        return setTimeout(func, 16);
    };
    var requestAnimationFrame$1 = requestAnimationFrame;

    var easingFuncs = {
        linear: function (k) {
            return k;
        },
        quadraticIn: function (k) {
            return k * k;
        },
        quadraticOut: function (k) {
            return k * (2 - k);
        },
        quadraticInOut: function (k) {
            if ((k *= 2) < 1) {
                return 0.5 * k * k;
            }
            return -0.5 * (--k * (k - 2) - 1);
        },
        cubicIn: function (k) {
            return k * k * k;
        },
        cubicOut: function (k) {
            return --k * k * k + 1;
        },
        cubicInOut: function (k) {
            if ((k *= 2) < 1) {
                return 0.5 * k * k * k;
            }
            return 0.5 * ((k -= 2) * k * k + 2);
        },
        quarticIn: function (k) {
            return k * k * k * k;
        },
        quarticOut: function (k) {
            return 1 - (--k * k * k * k);
        },
        quarticInOut: function (k) {
            if ((k *= 2) < 1) {
                return 0.5 * k * k * k * k;
            }
            return -0.5 * ((k -= 2) * k * k * k - 2);
        },
        quinticIn: function (k) {
            return k * k * k * k * k;
        },
        quinticOut: function (k) {
            return --k * k * k * k * k + 1;
        },
        quinticInOut: function (k) {
            if ((k *= 2) < 1) {
                return 0.5 * k * k * k * k * k;
            }
            return 0.5 * ((k -= 2) * k * k * k * k + 2);
        },
        sinusoidalIn: function (k) {
            return 1 - Math.cos(k * Math.PI / 2);
        },
        sinusoidalOut: function (k) {
            return Math.sin(k * Math.PI / 2);
        },
        sinusoidalInOut: function (k) {
            return 0.5 * (1 - Math.cos(Math.PI * k));
        },
        exponentialIn: function (k) {
            return k === 0 ? 0 : Math.pow(1024, k - 1);
        },
        exponentialOut: function (k) {
            return k === 1 ? 1 : 1 - Math.pow(2, -10 * k);
        },
        exponentialInOut: function (k) {
            if (k === 0) {
                return 0;
            }
            if (k === 1) {
                return 1;
            }
            if ((k *= 2) < 1) {
                return 0.5 * Math.pow(1024, k - 1);
            }
            return 0.5 * (-Math.pow(2, -10 * (k - 1)) + 2);
        },
        circularIn: function (k) {
            return 1 - Math.sqrt(1 - k * k);
        },
        circularOut: function (k) {
            return Math.sqrt(1 - (--k * k));
        },
        circularInOut: function (k) {
            if ((k *= 2) < 1) {
                return -0.5 * (Math.sqrt(1 - k * k) - 1);
            }
            return 0.5 * (Math.sqrt(1 - (k -= 2) * k) + 1);
        },
        elasticIn: function (k) {
            var s;
            var a = 0.1;
            var p = 0.4;
            if (k === 0) {
                return 0;
            }
            if (k === 1) {
                return 1;
            }
            if (!a || a < 1) {
                a = 1;
                s = p / 4;
            }
            else {
                s = p * Math.asin(1 / a) / (2 * Math.PI);
            }
            return -(a * Math.pow(2, 10 * (k -= 1))
                * Math.sin((k - s) * (2 * Math.PI) / p));
        },
        elasticOut: function (k) {
            var s;
            var a = 0.1;
            var p = 0.4;
            if (k === 0) {
                return 0;
            }
            if (k === 1) {
                return 1;
            }
            if (!a || a < 1) {
                a = 1;
                s = p / 4;
            }
            else {
                s = p * Math.asin(1 / a) / (2 * Math.PI);
            }
            return (a * Math.pow(2, -10 * k)
                * Math.sin((k - s) * (2 * Math.PI) / p) + 1);
        },
        elasticInOut: function (k) {
            var s;
            var a = 0.1;
            var p = 0.4;
            if (k === 0) {
                return 0;
            }
            if (k === 1) {
                return 1;
            }
            if (!a || a < 1) {
                a = 1;
                s = p / 4;
            }
            else {
                s = p * Math.asin(1 / a) / (2 * Math.PI);
            }
            if ((k *= 2) < 1) {
                return -0.5 * (a * Math.pow(2, 10 * (k -= 1))
                    * Math.sin((k - s) * (2 * Math.PI) / p));
            }
            return a * Math.pow(2, -10 * (k -= 1))
                * Math.sin((k - s) * (2 * Math.PI) / p) * 0.5 + 1;
        },
        backIn: function (k) {
            var s = 1.70158;
            return k * k * ((s + 1) * k - s);
        },
        backOut: function (k) {
            var s = 1.70158;
            return --k * k * ((s + 1) * k + s) + 1;
        },
        backInOut: function (k) {
            var s = 1.70158 * 1.525;
            if ((k *= 2) < 1) {
                return 0.5 * (k * k * ((s + 1) * k - s));
            }
            return 0.5 * ((k -= 2) * k * ((s + 1) * k + s) + 2);
        },
        bounceIn: function (k) {
            return 1 - easingFuncs.bounceOut(1 - k);
        },
        bounceOut: function (k) {
            if (k < (1 / 2.75)) {
                return 7.5625 * k * k;
            }
            else if (k < (2 / 2.75)) {
                return 7.5625 * (k -= (1.5 / 2.75)) * k + 0.75;
            }
            else if (k < (2.5 / 2.75)) {
                return 7.5625 * (k -= (2.25 / 2.75)) * k + 0.9375;
            }
            else {
                return 7.5625 * (k -= (2.625 / 2.75)) * k + 0.984375;
            }
        },
        bounceInOut: function (k) {
            if (k < 0.5) {
                return easingFuncs.bounceIn(k * 2) * 0.5;
            }
            return easingFuncs.bounceOut(k * 2 - 1) * 0.5 + 0.5;
        }
    };

    var mathPow = Math.pow;
    var mathSqrt = Math.sqrt;
    var EPSILON = 1e-8;
    var EPSILON_NUMERIC = 1e-4;
    var THREE_SQRT = mathSqrt(3);
    var ONE_THIRD = 1 / 3;
    var _v0 = create();
    var _v1 = create();
    var _v2 = create();
    function isAroundZero(val) {
        return val > -EPSILON && val < EPSILON;
    }
    function isNotAroundZero(val) {
        return val > EPSILON || val < -EPSILON;
    }
    function cubicAt(p0, p1, p2, p3, t) {
        var onet = 1 - t;
        return onet * onet * (onet * p0 + 3 * t * p1)
            + t * t * (t * p3 + 3 * onet * p2);
    }
    function cubicDerivativeAt(p0, p1, p2, p3, t) {
        var onet = 1 - t;
        return 3 * (((p1 - p0) * onet + 2 * (p2 - p1) * t) * onet
            + (p3 - p2) * t * t);
    }
    function cubicRootAt(p0, p1, p2, p3, val, roots) {
        var a = p3 + 3 * (p1 - p2) - p0;
        var b = 3 * (p2 - p1 * 2 + p0);
        var c = 3 * (p1 - p0);
        var d = p0 - val;
        var A = b * b - 3 * a * c;
        var B = b * c - 9 * a * d;
        var C = c * c - 3 * b * d;
        var n = 0;
        if (isAroundZero(A) && isAroundZero(B)) {
            if (isAroundZero(b)) {
                roots[0] = 0;
            }
            else {
                var t1 = -c / b;
                if (t1 >= 0 && t1 <= 1) {
                    roots[n++] = t1;
                }
            }
        }
        else {
            var disc = B * B - 4 * A * C;
            if (isAroundZero(disc)) {
                var K = B / A;
                var t1 = -b / a + K;
                var t2 = -K / 2;
                if (t1 >= 0 && t1 <= 1) {
                    roots[n++] = t1;
                }
                if (t2 >= 0 && t2 <= 1) {
                    roots[n++] = t2;
                }
            }
            else if (disc > 0) {
                var discSqrt = mathSqrt(disc);
                var Y1 = A * b + 1.5 * a * (-B + discSqrt);
                var Y2 = A * b + 1.5 * a * (-B - discSqrt);
                if (Y1 < 0) {
                    Y1 = -mathPow(-Y1, ONE_THIRD);
                }
                else {
                    Y1 = mathPow(Y1, ONE_THIRD);
                }
                if (Y2 < 0) {
                    Y2 = -mathPow(-Y2, ONE_THIRD);
                }
                else {
                    Y2 = mathPow(Y2, ONE_THIRD);
                }
                var t1 = (-b - (Y1 + Y2)) / (3 * a);
                if (t1 >= 0 && t1 <= 1) {
                    roots[n++] = t1;
                }
            }
            else {
                var T = (2 * A * b - 3 * a * B) / (2 * mathSqrt(A * A * A));
                var theta = Math.acos(T) / 3;
                var ASqrt = mathSqrt(A);
                var tmp = Math.cos(theta);
                var t1 = (-b - 2 * ASqrt * tmp) / (3 * a);
                var t2 = (-b + ASqrt * (tmp + THREE_SQRT * Math.sin(theta))) / (3 * a);
                var t3 = (-b + ASqrt * (tmp - THREE_SQRT * Math.sin(theta))) / (3 * a);
                if (t1 >= 0 && t1 <= 1) {
                    roots[n++] = t1;
                }
                if (t2 >= 0 && t2 <= 1) {
                    roots[n++] = t2;
                }
                if (t3 >= 0 && t3 <= 1) {
                    roots[n++] = t3;
                }
            }
        }
        return n;
    }
    function cubicExtrema(p0, p1, p2, p3, extrema) {
        var b = 6 * p2 - 12 * p1 + 6 * p0;
        var a = 9 * p1 + 3 * p3 - 3 * p0 - 9 * p2;
        var c = 3 * p1 - 3 * p0;
        var n = 0;
        if (isAroundZero(a)) {
            if (isNotAroundZero(b)) {
                var t1 = -c / b;
                if (t1 >= 0 && t1 <= 1) {
                    extrema[n++] = t1;
                }
            }
        }
        else {
            var disc = b * b - 4 * a * c;
            if (isAroundZero(disc)) {
                extrema[0] = -b / (2 * a);
            }
            else if (disc > 0) {
                var discSqrt = mathSqrt(disc);
                var t1 = (-b + discSqrt) / (2 * a);
                var t2 = (-b - discSqrt) / (2 * a);
                if (t1 >= 0 && t1 <= 1) {
                    extrema[n++] = t1;
                }
                if (t2 >= 0 && t2 <= 1) {
                    extrema[n++] = t2;
                }
            }
        }
        return n;
    }
    function cubicSubdivide(p0, p1, p2, p3, t, out) {
        var p01 = (p1 - p0) * t + p0;
        var p12 = (p2 - p1) * t + p1;
        var p23 = (p3 - p2) * t + p2;
        var p012 = (p12 - p01) * t + p01;
        var p123 = (p23 - p12) * t + p12;
        var p0123 = (p123 - p012) * t + p012;
        out[0] = p0;
        out[1] = p01;
        out[2] = p012;
        out[3] = p0123;
        out[4] = p0123;
        out[5] = p123;
        out[6] = p23;
        out[7] = p3;
    }
    function cubicProjectPoint(x0, y0, x1, y1, x2, y2, x3, y3, x, y, out) {
        var t;
        var interval = 0.005;
        var d = Infinity;
        var prev;
        var next;
        var d1;
        var d2;
        _v0[0] = x;
        _v0[1] = y;
        for (var _t = 0; _t < 1; _t += 0.05) {
            _v1[0] = cubicAt(x0, x1, x2, x3, _t);
            _v1[1] = cubicAt(y0, y1, y2, y3, _t);
            d1 = distSquare(_v0, _v1);
            if (d1 < d) {
                t = _t;
                d = d1;
            }
        }
        d = Infinity;
        for (var i = 0; i < 32; i++) {
            if (interval < EPSILON_NUMERIC) {
                break;
            }
            prev = t - interval;
            next = t + interval;
            _v1[0] = cubicAt(x0, x1, x2, x3, prev);
            _v1[1] = cubicAt(y0, y1, y2, y3, prev);
            d1 = distSquare(_v1, _v0);
            if (prev >= 0 && d1 < d) {
                t = prev;
                d = d1;
            }
            else {
                _v2[0] = cubicAt(x0, x1, x2, x3, next);
                _v2[1] = cubicAt(y0, y1, y2, y3, next);
                d2 = distSquare(_v2, _v0);
                if (next <= 1 && d2 < d) {
                    t = next;
                    d = d2;
                }
                else {
                    interval *= 0.5;
                }
            }
        }
        if (out) {
            out[0] = cubicAt(x0, x1, x2, x3, t);
            out[1] = cubicAt(y0, y1, y2, y3, t);
        }
        return mathSqrt(d);
    }
    function cubicLength(x0, y0, x1, y1, x2, y2, x3, y3, iteration) {
        var px = x0;
        var py = y0;
        var d = 0;
        var step = 1 / iteration;
        for (var i = 1; i <= iteration; i++) {
            var t = i * step;
            var x = cubicAt(x0, x1, x2, x3, t);
            var y = cubicAt(y0, y1, y2, y3, t);
            var dx = x - px;
            var dy = y - py;
            d += Math.sqrt(dx * dx + dy * dy);
            px = x;
            py = y;
        }
        return d;
    }
    function quadraticAt(p0, p1, p2, t) {
        var onet = 1 - t;
        return onet * (onet * p0 + 2 * t * p1) + t * t * p2;
    }
    function quadraticDerivativeAt(p0, p1, p2, t) {
        return 2 * ((1 - t) * (p1 - p0) + t * (p2 - p1));
    }
    function quadraticRootAt(p0, p1, p2, val, roots) {
        var a = p0 - 2 * p1 + p2;
        var b = 2 * (p1 - p0);
        var c = p0 - val;
        var n = 0;
        if (isAroundZero(a)) {
            if (isNotAroundZero(b)) {
                var t1 = -c / b;
                if (t1 >= 0 && t1 <= 1) {
                    roots[n++] = t1;
                }
            }
        }
        else {
            var disc = b * b - 4 * a * c;
            if (isAroundZero(disc)) {
                var t1 = -b / (2 * a);
                if (t1 >= 0 && t1 <= 1) {
                    roots[n++] = t1;
                }
            }
            else if (disc > 0) {
                var discSqrt = mathSqrt(disc);
                var t1 = (-b + discSqrt) / (2 * a);
                var t2 = (-b - discSqrt) / (2 * a);
                if (t1 >= 0 && t1 <= 1) {
                    roots[n++] = t1;
                }
                if (t2 >= 0 && t2 <= 1) {
                    roots[n++] = t2;
                }
            }
        }
        return n;
    }
    function quadraticExtremum(p0, p1, p2) {
        var divider = p0 + p2 - 2 * p1;
        if (divider === 0) {
            return 0.5;
        }
        else {
            return (p0 - p1) / divider;
        }
    }
    function quadraticSubdivide(p0, p1, p2, t, out) {
        var p01 = (p1 - p0) * t + p0;
        var p12 = (p2 - p1) * t + p1;
        var p012 = (p12 - p01) * t + p01;
        out[0] = p0;
        out[1] = p01;
        out[2] = p012;
        out[3] = p012;
        out[4] = p12;
        out[5] = p2;
    }
    function quadraticProjectPoint(x0, y0, x1, y1, x2, y2, x, y, out) {
        var t;
        var interval = 0.005;
        var d = Infinity;
        _v0[0] = x;
        _v0[1] = y;
        for (var _t = 0; _t < 1; _t += 0.05) {
            _v1[0] = quadraticAt(x0, x1, x2, _t);
            _v1[1] = quadraticAt(y0, y1, y2, _t);
            var d1 = distSquare(_v0, _v1);
            if (d1 < d) {
                t = _t;
                d = d1;
            }
        }
        d = Infinity;
        for (var i = 0; i < 32; i++) {
            if (interval < EPSILON_NUMERIC) {
                break;
            }
            var prev = t - interval;
            var next = t + interval;
            _v1[0] = quadraticAt(x0, x1, x2, prev);
            _v1[1] = quadraticAt(y0, y1, y2, prev);
            var d1 = distSquare(_v1, _v0);
            if (prev >= 0 && d1 < d) {
                t = prev;
                d = d1;
            }
            else {
                _v2[0] = quadraticAt(x0, x1, x2, next);
                _v2[1] = quadraticAt(y0, y1, y2, next);
                var d2 = distSquare(_v2, _v0);
                if (next <= 1 && d2 < d) {
                    t = next;
                    d = d2;
                }
                else {
                    interval *= 0.5;
                }
            }
        }
        if (out) {
            out[0] = quadraticAt(x0, x1, x2, t);
            out[1] = quadraticAt(y0, y1, y2, t);
        }
        return mathSqrt(d);
    }
    function quadraticLength(x0, y0, x1, y1, x2, y2, iteration) {
        var px = x0;
        var py = y0;
        var d = 0;
        var step = 1 / iteration;
        for (var i = 1; i <= iteration; i++) {
            var t = i * step;
            var x = quadraticAt(x0, x1, x2, t);
            var y = quadraticAt(y0, y1, y2, t);
            var dx = x - px;
            var dy = y - py;
            d += Math.sqrt(dx * dx + dy * dy);
            px = x;
            py = y;
        }
        return d;
    }

    var regexp = /cubic-bezier\(([0-9,\.e ]+)\)/;
    function createCubicEasingFunc(cubicEasingStr) {
        var cubic = cubicEasingStr && regexp.exec(cubicEasingStr);
        if (cubic) {
            var points = cubic[1].split(',');
            var a_1 = +trim(points[0]);
            var b_1 = +trim(points[1]);
            var c_1 = +trim(points[2]);
            var d_1 = +trim(points[3]);
            if (isNaN(a_1 + b_1 + c_1 + d_1)) {
                return;
            }
            var roots_1 = [];
            return function (p) {
                return p <= 0
                    ? 0 : p >= 1
                    ? 1
                    : cubicRootAt(0, a_1, c_1, 1, p, roots_1) && cubicAt(0, b_1, d_1, 1, roots_1[0]);
            };
        }
    }

    var Clip = (function () {
        function Clip(opts) {
            this._inited = false;
            this._startTime = 0;
            this._pausedTime = 0;
            this._paused = false;
            this._life = opts.life || 1000;
            this._delay = opts.delay || 0;
            this.loop = opts.loop || false;
            this.onframe = opts.onframe || noop;
            this.ondestroy = opts.ondestroy || noop;
            this.onrestart = opts.onrestart || noop;
            opts.easing && this.setEasing(opts.easing);
        }
        Clip.prototype.step = function (globalTime, deltaTime) {
            if (!this._inited) {
                this._startTime = globalTime + this._delay;
                this._inited = true;
            }
            if (this._paused) {
                this._pausedTime += deltaTime;
                return;
            }
            var life = this._life;
            var elapsedTime = globalTime - this._startTime - this._pausedTime;
            var percent = elapsedTime / life;
            if (percent < 0) {
                percent = 0;
            }
            percent = Math.min(percent, 1);
            var easingFunc = this.easingFunc;
            var schedule = easingFunc ? easingFunc(percent) : percent;
            this.onframe(schedule);
            if (percent === 1) {
                if (this.loop) {
                    var remainder = elapsedTime % life;
                    this._startTime = globalTime - remainder;
                    this._pausedTime = 0;
                    this.onrestart();
                }
                else {
                    return true;
                }
            }
            return false;
        };
        Clip.prototype.pause = function () {
            this._paused = true;
        };
        Clip.prototype.resume = function () {
            this._paused = false;
        };
        Clip.prototype.setEasing = function (easing) {
            this.easing = easing;
            this.easingFunc = isFunction(easing)
                ? easing
                : easingFuncs[easing] || createCubicEasingFunc(easing);
        };
        return Clip;
    }());

    var Entry = (function () {
        function Entry(val) {
            this.value = val;
        }
        return Entry;
    }());
    var LinkedList = (function () {
        function LinkedList() {
            this._len = 0;
        }
        LinkedList.prototype.insert = function (val) {
            var entry = new Entry(val);
            this.insertEntry(entry);
            return entry;
        };
        LinkedList.prototype.insertEntry = function (entry) {
            if (!this.head) {
                this.head = this.tail = entry;
            }
            else {
                this.tail.next = entry;
                entry.prev = this.tail;
                entry.next = null;
                this.tail = entry;
            }
            this._len++;
        };
        LinkedList.prototype.remove = function (entry) {
            var prev = entry.prev;
            var next = entry.next;
            if (prev) {
                prev.next = next;
            }
            else {
                this.head = next;
            }
            if (next) {
                next.prev = prev;
            }
            else {
                this.tail = prev;
            }
            entry.next = entry.prev = null;
            this._len--;
        };
        LinkedList.prototype.len = function () {
            return this._len;
        };
        LinkedList.prototype.clear = function () {
            this.head = this.tail = null;
            this._len = 0;
        };
        return LinkedList;
    }());
    var LRU = (function () {
        function LRU(maxSize) {
            this._list = new LinkedList();
            this._maxSize = 10;
            this._map = {};
            this._maxSize = maxSize;
        }
        LRU.prototype.put = function (key, value) {
            var list = this._list;
            var map = this._map;
            var removed = null;
            if (map[key] == null) {
                var len = list.len();
                var entry = this._lastRemovedEntry;
                if (len >= this._maxSize && len > 0) {
                    var leastUsedEntry = list.head;
                    list.remove(leastUsedEntry);
                    delete map[leastUsedEntry.key];
                    removed = leastUsedEntry.value;
                    this._lastRemovedEntry = leastUsedEntry;
                }
                if (entry) {
                    entry.value = value;
                }
                else {
                    entry = new Entry(value);
                }
                entry.key = key;
                list.insertEntry(entry);
                map[key] = entry;
            }
            return removed;
        };
        LRU.prototype.get = function (key) {
            var entry = this._map[key];
            var list = this._list;
            if (entry != null) {
                if (entry !== list.tail) {
                    list.remove(entry);
                    list.insertEntry(entry);
                }
                return entry.value;
            }
        };
        LRU.prototype.clear = function () {
            this._list.clear();
            this._map = {};
        };
        LRU.prototype.len = function () {
            return this._list.len();
        };
        return LRU;
    }());

    var kCSSColorTable = {
        'transparent': [0, 0, 0, 0], 'aliceblue': [240, 248, 255, 1],
        'antiquewhite': [250, 235, 215, 1], 'aqua': [0, 255, 255, 1],
        'aquamarine': [127, 255, 212, 1], 'azure': [240, 255, 255, 1],
        'beige': [245, 245, 220, 1], 'bisque': [255, 228, 196, 1],
        'black': [0, 0, 0, 1], 'blanchedalmond': [255, 235, 205, 1],
        'blue': [0, 0, 255, 1], 'blueviolet': [138, 43, 226, 1],
        'brown': [165, 42, 42, 1], 'burlywood': [222, 184, 135, 1],
        'cadetblue': [95, 158, 160, 1], 'chartreuse': [127, 255, 0, 1],
        'chocolate': [210, 105, 30, 1], 'coral': [255, 127, 80, 1],
        'cornflowerblue': [100, 149, 237, 1], 'cornsilk': [255, 248, 220, 1],
        'crimson': [220, 20, 60, 1], 'cyan': [0, 255, 255, 1],
        'darkblue': [0, 0, 139, 1], 'darkcyan': [0, 139, 139, 1],
        'darkgoldenrod': [184, 134, 11, 1], 'darkgray': [169, 169, 169, 1],
        'darkgreen': [0, 100, 0, 1], 'darkgrey': [169, 169, 169, 1],
        'darkkhaki': [189, 183, 107, 1], 'darkmagenta': [139, 0, 139, 1],
        'darkolivegreen': [85, 107, 47, 1], 'darkorange': [255, 140, 0, 1],
        'darkorchid': [153, 50, 204, 1], 'darkred': [139, 0, 0, 1],
        'darksalmon': [233, 150, 122, 1], 'darkseagreen': [143, 188, 143, 1],
        'darkslateblue': [72, 61, 139, 1], 'darkslategray': [47, 79, 79, 1],
        'darkslategrey': [47, 79, 79, 1], 'darkturquoise': [0, 206, 209, 1],
        'darkviolet': [148, 0, 211, 1], 'deeppink': [255, 20, 147, 1],
        'deepskyblue': [0, 191, 255, 1], 'dimgray': [105, 105, 105, 1],
        'dimgrey': [105, 105, 105, 1], 'dodgerblue': [30, 144, 255, 1],
        'firebrick': [178, 34, 34, 1], 'floralwhite': [255, 250, 240, 1],
        'forestgreen': [34, 139, 34, 1], 'fuchsia': [255, 0, 255, 1],
        'gainsboro': [220, 220, 220, 1], 'ghostwhite': [248, 248, 255, 1],
        'gold': [255, 215, 0, 1], 'goldenrod': [218, 165, 32, 1],
        'gray': [128, 128, 128, 1], 'green': [0, 128, 0, 1],
        'greenyellow': [173, 255, 47, 1], 'grey': [128, 128, 128, 1],
        'honeydew': [240, 255, 240, 1], 'hotpink': [255, 105, 180, 1],
        'indianred': [205, 92, 92, 1], 'indigo': [75, 0, 130, 1],
        'ivory': [255, 255, 240, 1], 'khaki': [240, 230, 140, 1],
        'lavender': [230, 230, 250, 1], 'lavenderblush': [255, 240, 245, 1],
        'lawngreen': [124, 252, 0, 1], 'lemonchiffon': [255, 250, 205, 1],
        'lightblue': [173, 216, 230, 1], 'lightcoral': [240, 128, 128, 1],
        'lightcyan': [224, 255, 255, 1], 'lightgoldenrodyellow': [250, 250, 210, 1],
        'lightgray': [211, 211, 211, 1], 'lightgreen': [144, 238, 144, 1],
        'lightgrey': [211, 211, 211, 1], 'lightpink': [255, 182, 193, 1],
        'lightsalmon': [255, 160, 122, 1], 'lightseagreen': [32, 178, 170, 1],
        'lightskyblue': [135, 206, 250, 1], 'lightslategray': [119, 136, 153, 1],
        'lightslategrey': [119, 136, 153, 1], 'lightsteelblue': [176, 196, 222, 1],
        'lightyellow': [255, 255, 224, 1], 'lime': [0, 255, 0, 1],
        'limegreen': [50, 205, 50, 1], 'linen': [250, 240, 230, 1],
        'magenta': [255, 0, 255, 1], 'maroon': [128, 0, 0, 1],
        'mediumaquamarine': [102, 205, 170, 1], 'mediumblue': [0, 0, 205, 1],
        'mediumorchid': [186, 85, 211, 1], 'mediumpurple': [147, 112, 219, 1],
        'mediumseagreen': [60, 179, 113, 1], 'mediumslateblue': [123, 104, 238, 1],
        'mediumspringgreen': [0, 250, 154, 1], 'mediumturquoise': [72, 209, 204, 1],
        'mediumvioletred': [199, 21, 133, 1], 'midnightblue': [25, 25, 112, 1],
        'mintcream': [245, 255, 250, 1], 'mistyrose': [255, 228, 225, 1],
        'moccasin': [255, 228, 181, 1], 'navajowhite': [255, 222, 173, 1],
        'navy': [0, 0, 128, 1], 'oldlace': [253, 245, 230, 1],
        'olive': [128, 128, 0, 1], 'olivedrab': [107, 142, 35, 1],
        'orange': [255, 165, 0, 1], 'orangered': [255, 69, 0, 1],
        'orchid': [218, 112, 214, 1], 'palegoldenrod': [238, 232, 170, 1],
        'palegreen': [152, 251, 152, 1], 'paleturquoise': [175, 238, 238, 1],
        'palevioletred': [219, 112, 147, 1], 'papayawhip': [255, 239, 213, 1],
        'peachpuff': [255, 218, 185, 1], 'peru': [205, 133, 63, 1],
        'pink': [255, 192, 203, 1], 'plum': [221, 160, 221, 1],
        'powderblue': [176, 224, 230, 1], 'purple': [128, 0, 128, 1],
        'red': [255, 0, 0, 1], 'rosybrown': [188, 143, 143, 1],
        'royalblue': [65, 105, 225, 1], 'saddlebrown': [139, 69, 19, 1],
        'salmon': [250, 128, 114, 1], 'sandybrown': [244, 164, 96, 1],
        'seagreen': [46, 139, 87, 1], 'seashell': [255, 245, 238, 1],
        'sienna': [160, 82, 45, 1], 'silver': [192, 192, 192, 1],
        'skyblue': [135, 206, 235, 1], 'slateblue': [106, 90, 205, 1],
        'slategray': [112, 128, 144, 1], 'slategrey': [112, 128, 144, 1],
        'snow': [255, 250, 250, 1], 'springgreen': [0, 255, 127, 1],
        'steelblue': [70, 130, 180, 1], 'tan': [210, 180, 140, 1],
        'teal': [0, 128, 128, 1], 'thistle': [216, 191, 216, 1],
        'tomato': [255, 99, 71, 1], 'turquoise': [64, 224, 208, 1],
        'violet': [238, 130, 238, 1], 'wheat': [245, 222, 179, 1],
        'white': [255, 255, 255, 1], 'whitesmoke': [245, 245, 245, 1],
        'yellow': [255, 255, 0, 1], 'yellowgreen': [154, 205, 50, 1]
    };
    function clampCssByte(i) {
        i = Math.round(i);
        return i < 0 ? 0 : i > 255 ? 255 : i;
    }
    function clampCssAngle(i) {
        i = Math.round(i);
        return i < 0 ? 0 : i > 360 ? 360 : i;
    }
    function clampCssFloat(f) {
        return f < 0 ? 0 : f > 1 ? 1 : f;
    }
    function parseCssInt(val) {
        var str = val;
        if (str.length && str.charAt(str.length - 1) === '%') {
            return clampCssByte(parseFloat(str) / 100 * 255);
        }
        return clampCssByte(parseInt(str, 10));
    }
    function parseCssFloat(val) {
        var str = val;
        if (str.length && str.charAt(str.length - 1) === '%') {
            return clampCssFloat(parseFloat(str) / 100);
        }
        return clampCssFloat(parseFloat(str));
    }
    function cssHueToRgb(m1, m2, h) {
        if (h < 0) {
            h += 1;
        }
        else if (h > 1) {
            h -= 1;
        }
        if (h * 6 < 1) {
            return m1 + (m2 - m1) * h * 6;
        }
        if (h * 2 < 1) {
            return m2;
        }
        if (h * 3 < 2) {
            return m1 + (m2 - m1) * (2 / 3 - h) * 6;
        }
        return m1;
    }
    function lerpNumber(a, b, p) {
        return a + (b - a) * p;
    }
    function setRgba(out, r, g, b, a) {
        out[0] = r;
        out[1] = g;
        out[2] = b;
        out[3] = a;
        return out;
    }
    function copyRgba(out, a) {
        out[0] = a[0];
        out[1] = a[1];
        out[2] = a[2];
        out[3] = a[3];
        return out;
    }
    var colorCache = new LRU(20);
    var lastRemovedArr = null;
    function putToCache(colorStr, rgbaArr) {
        if (lastRemovedArr) {
            copyRgba(lastRemovedArr, rgbaArr);
        }
        lastRemovedArr = colorCache.put(colorStr, lastRemovedArr || (rgbaArr.slice()));
    }
    function parse(colorStr, rgbaArr) {
        if (!colorStr) {
            return;
        }
        rgbaArr = rgbaArr || [];
        var cached = colorCache.get(colorStr);
        if (cached) {
            return copyRgba(rgbaArr, cached);
        }
        colorStr = colorStr + '';
        var str = colorStr.replace(/ /g, '').toLowerCase();
        if (str in kCSSColorTable) {
            copyRgba(rgbaArr, kCSSColorTable[str]);
            putToCache(colorStr, rgbaArr);
            return rgbaArr;
        }
        var strLen = str.length;
        if (str.charAt(0) === '#') {
            if (strLen === 4 || strLen === 5) {
                var iv = parseInt(str.slice(1, 4), 16);
                if (!(iv >= 0 && iv <= 0xfff)) {
                    setRgba(rgbaArr, 0, 0, 0, 1);
                    return;
                }
                setRgba(rgbaArr, ((iv & 0xf00) >> 4) | ((iv & 0xf00) >> 8), (iv & 0xf0) | ((iv & 0xf0) >> 4), (iv & 0xf) | ((iv & 0xf) << 4), strLen === 5 ? parseInt(str.slice(4), 16) / 0xf : 1);
                putToCache(colorStr, rgbaArr);
                return rgbaArr;
            }
            else if (strLen === 7 || strLen === 9) {
                var iv = parseInt(str.slice(1, 7), 16);
                if (!(iv >= 0 && iv <= 0xffffff)) {
                    setRgba(rgbaArr, 0, 0, 0, 1);
                    return;
                }
                setRgba(rgbaArr, (iv & 0xff0000) >> 16, (iv & 0xff00) >> 8, iv & 0xff, strLen === 9 ? parseInt(str.slice(7), 16) / 0xff : 1);
                putToCache(colorStr, rgbaArr);
                return rgbaArr;
            }
            return;
        }
        var op = str.indexOf('(');
        var ep = str.indexOf(')');
        if (op !== -1 && ep + 1 === strLen) {
            var fname = str.substr(0, op);
            var params = str.substr(op + 1, ep - (op + 1)).split(',');
            var alpha = 1;
            switch (fname) {
                case 'rgba':
                    if (params.length !== 4) {
                        return params.length === 3
                            ? setRgba(rgbaArr, +params[0], +params[1], +params[2], 1)
                            : setRgba(rgbaArr, 0, 0, 0, 1);
                    }
                    alpha = parseCssFloat(params.pop());
                case 'rgb':
                    if (params.length >= 3) {
                        setRgba(rgbaArr, parseCssInt(params[0]), parseCssInt(params[1]), parseCssInt(params[2]), params.length === 3 ? alpha : parseCssFloat(params[3]));
                        putToCache(colorStr, rgbaArr);
                        return rgbaArr;
                    }
                    else {
                        setRgba(rgbaArr, 0, 0, 0, 1);
                        return;
                    }
                case 'hsla':
                    if (params.length !== 4) {
                        setRgba(rgbaArr, 0, 0, 0, 1);
                        return;
                    }
                    params[3] = parseCssFloat(params[3]);
                    hsla2rgba(params, rgbaArr);
                    putToCache(colorStr, rgbaArr);
                    return rgbaArr;
                case 'hsl':
                    if (params.length !== 3) {
                        setRgba(rgbaArr, 0, 0, 0, 1);
                        return;
                    }
                    hsla2rgba(params, rgbaArr);
                    putToCache(colorStr, rgbaArr);
                    return rgbaArr;
                default:
                    return;
            }
        }
        setRgba(rgbaArr, 0, 0, 0, 1);
        return;
    }
    function hsla2rgba(hsla, rgba) {
        var h = (((parseFloat(hsla[0]) % 360) + 360) % 360) / 360;
        var s = parseCssFloat(hsla[1]);
        var l = parseCssFloat(hsla[2]);
        var m2 = l <= 0.5 ? l * (s + 1) : l + s - l * s;
        var m1 = l * 2 - m2;
        rgba = rgba || [];
        setRgba(rgba, clampCssByte(cssHueToRgb(m1, m2, h + 1 / 3) * 255), clampCssByte(cssHueToRgb(m1, m2, h) * 255), clampCssByte(cssHueToRgb(m1, m2, h - 1 / 3) * 255), 1);
        if (hsla.length === 4) {
            rgba[3] = hsla[3];
        }
        return rgba;
    }
    function rgba2hsla(rgba) {
        if (!rgba) {
            return;
        }
        var R = rgba[0] / 255;
        var G = rgba[1] / 255;
        var B = rgba[2] / 255;
        var vMin = Math.min(R, G, B);
        var vMax = Math.max(R, G, B);
        var delta = vMax - vMin;
        var L = (vMax + vMin) / 2;
        var H;
        var S;
        if (delta === 0) {
            H = 0;
            S = 0;
        }
        else {
            if (L < 0.5) {
                S = delta / (vMax + vMin);
            }
            else {
                S = delta / (2 - vMax - vMin);
            }
            var deltaR = (((vMax - R) / 6) + (delta / 2)) / delta;
            var deltaG = (((vMax - G) / 6) + (delta / 2)) / delta;
            var deltaB = (((vMax - B) / 6) + (delta / 2)) / delta;
            if (R === vMax) {
                H = deltaB - deltaG;
            }
            else if (G === vMax) {
                H = (1 / 3) + deltaR - deltaB;
            }
            else if (B === vMax) {
                H = (2 / 3) + deltaG - deltaR;
            }
            if (H < 0) {
                H += 1;
            }
            if (H > 1) {
                H -= 1;
            }
        }
        var hsla = [H * 360, S, L];
        if (rgba[3] != null) {
            hsla.push(rgba[3]);
        }
        return hsla;
    }
    function lift(color, level) {
        var colorArr = parse(color);
        if (colorArr) {
            for (var i = 0; i < 3; i++) {
                if (level < 0) {
                    colorArr[i] = colorArr[i] * (1 - level) | 0;
                }
                else {
                    colorArr[i] = ((255 - colorArr[i]) * level + colorArr[i]) | 0;
                }
                if (colorArr[i] > 255) {
                    colorArr[i] = 255;
                }
                else if (colorArr[i] < 0) {
                    colorArr[i] = 0;
                }
            }
            return stringify(colorArr, colorArr.length === 4 ? 'rgba' : 'rgb');
        }
    }
    function toHex(color) {
        var colorArr = parse(color);
        if (colorArr) {
            return ((1 << 24) + (colorArr[0] << 16) + (colorArr[1] << 8) + (+colorArr[2])).toString(16).slice(1);
        }
    }
    function fastLerp(normalizedValue, colors, out) {
        if (!(colors && colors.length)
            || !(normalizedValue >= 0 && normalizedValue <= 1)) {
            return;
        }
        out = out || [];
        var value = normalizedValue * (colors.length - 1);
        var leftIndex = Math.floor(value);
        var rightIndex = Math.ceil(value);
        var leftColor = colors[leftIndex];
        var rightColor = colors[rightIndex];
        var dv = value - leftIndex;
        out[0] = clampCssByte(lerpNumber(leftColor[0], rightColor[0], dv));
        out[1] = clampCssByte(lerpNumber(leftColor[1], rightColor[1], dv));
        out[2] = clampCssByte(lerpNumber(leftColor[2], rightColor[2], dv));
        out[3] = clampCssFloat(lerpNumber(leftColor[3], rightColor[3], dv));
        return out;
    }
    var fastMapToColor = fastLerp;
    function lerp$1(normalizedValue, colors, fullOutput) {
        if (!(colors && colors.length)
            || !(normalizedValue >= 0 && normalizedValue <= 1)) {
            return;
        }
        var value = normalizedValue * (colors.length - 1);
        var leftIndex = Math.floor(value);
        var rightIndex = Math.ceil(value);
        var leftColor = parse(colors[leftIndex]);
        var rightColor = parse(colors[rightIndex]);
        var dv = value - leftIndex;
        var color = stringify([
            clampCssByte(lerpNumber(leftColor[0], rightColor[0], dv)),
            clampCssByte(lerpNumber(leftColor[1], rightColor[1], dv)),
            clampCssByte(lerpNumber(leftColor[2], rightColor[2], dv)),
            clampCssFloat(lerpNumber(leftColor[3], rightColor[3], dv))
        ], 'rgba');
        return fullOutput
            ? {
                color: color,
                leftIndex: leftIndex,
                rightIndex: rightIndex,
                value: value
            }
            : color;
    }
    var mapToColor = lerp$1;
    function modifyHSL(color, h, s, l) {
        var colorArr = parse(color);
        if (color) {
            colorArr = rgba2hsla(colorArr);
            h != null && (colorArr[0] = clampCssAngle(h));
            s != null && (colorArr[1] = parseCssFloat(s));
            l != null && (colorArr[2] = parseCssFloat(l));
            return stringify(hsla2rgba(colorArr), 'rgba');
        }
    }
    function modifyAlpha(color, alpha) {
        var colorArr = parse(color);
        if (colorArr && alpha != null) {
            colorArr[3] = clampCssFloat(alpha);
            return stringify(colorArr, 'rgba');
        }
    }
    function stringify(arrColor, type) {
        if (!arrColor || !arrColor.length) {
            return;
        }
        var colorStr = arrColor[0] + ',' + arrColor[1] + ',' + arrColor[2];
        if (type === 'rgba' || type === 'hsva' || type === 'hsla') {
            colorStr += ',' + arrColor[3];
        }
        return type + '(' + colorStr + ')';
    }
    function lum(color, backgroundLum) {
        var arr = parse(color);
        return arr
            ? (0.299 * arr[0] + 0.587 * arr[1] + 0.114 * arr[2]) * arr[3] / 255
                + (1 - arr[3]) * backgroundLum
            : 0;
    }
    function random() {
        return stringify([
            Math.round(Math.random() * 255),
            Math.round(Math.random() * 255),
            Math.round(Math.random() * 255)
        ], 'rgb');
    }

    var color = /*#__PURE__*/Object.freeze({
        __proto__: null,
        parse: parse,
        lift: lift,
        toHex: toHex,
        fastLerp: fastLerp,
        fastMapToColor: fastMapToColor,
        lerp: lerp$1,
        mapToColor: mapToColor,
        modifyHSL: modifyHSL,
        modifyAlpha: modifyAlpha,
        stringify: stringify,
        lum: lum,
        random: random
    });

    var mathRound = Math.round;
    function normalizeColor(color) {
        var opacity;
        if (!color || color === 'transparent') {
            color = 'none';
        }
        else if (typeof color === 'string' && color.indexOf('rgba') > -1) {
            var arr = parse(color);
            if (arr) {
                color = 'rgb(' + arr[0] + ',' + arr[1] + ',' + arr[2] + ')';
                opacity = arr[3];
            }
        }
        return {
            color: color,
            opacity: opacity == null ? 1 : opacity
        };
    }
    var EPSILON$1 = 1e-4;
    function isAroundZero$1(transform) {
        return transform < EPSILON$1 && transform > -EPSILON$1;
    }
    function round3(transform) {
        return mathRound(transform * 1e3) / 1e3;
    }
    function round4(transform) {
        return mathRound(transform * 1e4) / 1e4;
    }
    function getMatrixStr(m) {
        return 'matrix('
            + round3(m[0]) + ','
            + round3(m[1]) + ','
            + round3(m[2]) + ','
            + round3(m[3]) + ','
            + round4(m[4]) + ','
            + round4(m[5])
            + ')';
    }
    var TEXT_ALIGN_TO_ANCHOR = {
        left: 'start',
        right: 'end',
        center: 'middle',
        middle: 'middle'
    };
    function adjustTextY(y, lineHeight, textBaseline) {
        if (textBaseline === 'top') {
            y += lineHeight / 2;
        }
        else if (textBaseline === 'bottom') {
            y -= lineHeight / 2;
        }
        return y;
    }
    function hasShadow(style) {
        return style
            && (style.shadowBlur || style.shadowOffsetX || style.shadowOffsetY);
    }
    function getShadowKey(displayable) {
        var style = displayable.style;
        var globalScale = displayable.getGlobalScale();
        return [
            style.shadowColor,
            (style.shadowBlur || 0).toFixed(2),
            (style.shadowOffsetX || 0).toFixed(2),
            (style.shadowOffsetY || 0).toFixed(2),
            globalScale[0],
            globalScale[1]
        ].join(',');
    }
    function isImagePattern(val) {
        return val && (!!val.image);
    }
    function isSVGPattern(val) {
        return val && (!!val.svgElement);
    }
    function isPattern(val) {
        return isImagePattern(val) || isSVGPattern(val);
    }
    function isLinearGradient(val) {
        return val.type === 'linear';
    }
    function isRadialGradient(val) {
        return val.type === 'radial';
    }
    function isGradient(val) {
        return val && (val.type === 'linear'
            || val.type === 'radial');
    }
    function getIdURL(id) {
        return "url(#" + id + ")";
    }
    function getPathPrecision(el) {
        var scale = el.getGlobalScale();
        var size = Math.max(scale[0], scale[1]);
        return Math.max(Math.ceil(Math.log(size) / Math.log(10)), 1);
    }
    function getSRTTransformString(transform) {
        var x = transform.x || 0;
        var y = transform.y || 0;
        var rotation = (transform.rotation || 0) * RADIAN_TO_DEGREE;
        var scaleX = retrieve2(transform.scaleX, 1);
        var scaleY = retrieve2(transform.scaleY, 1);
        var skewX = transform.skewX || 0;
        var skewY = transform.skewY || 0;
        var res = [];
        if (x || y) {
            res.push("translate(" + x + "px," + y + "px)");
        }
        if (rotation) {
            res.push("rotate(" + rotation + ")");
        }
        if (scaleX !== 1 || scaleY !== 1) {
            res.push("scale(" + scaleX + "," + scaleY + ")");
        }
        if (skewX || skewY) {
            res.push("skew(" + mathRound(skewX * RADIAN_TO_DEGREE) + "deg, " + mathRound(skewY * RADIAN_TO_DEGREE) + "deg)");
        }
        return res.join(' ');
    }
    var encodeBase64 = (function () {
        if (env.hasGlobalWindow && isFunction(window.btoa)) {
            return function (str) {
                return window.btoa(unescape(encodeURIComponent(str)));
            };
        }
        if (typeof Buffer !== 'undefined') {
            return function (str) {
                return Buffer.from(str).toString('base64');
            };
        }
        return function (str) {
            if ("development" !== 'production') {
                logError('Base64 isn\'t natively supported in the current environment.');
            }
            return null;
        };
    })();

    var arraySlice = Array.prototype.slice;
    function interpolateNumber(p0, p1, percent) {
        return (p1 - p0) * percent + p0;
    }
    function interpolate1DArray(out, p0, p1, percent) {
        var len = p0.length;
        for (var i = 0; i < len; i++) {
            out[i] = interpolateNumber(p0[i], p1[i], percent);
        }
        return out;
    }
    function interpolate2DArray(out, p0, p1, percent) {
        var len = p0.length;
        var len2 = len && p0[0].length;
        for (var i = 0; i < len; i++) {
            if (!out[i]) {
                out[i] = [];
            }
            for (var j = 0; j < len2; j++) {
                out[i][j] = interpolateNumber(p0[i][j], p1[i][j], percent);
            }
        }
        return out;
    }
    function add1DArray(out, p0, p1, sign) {
        var len = p0.length;
        for (var i = 0; i < len; i++) {
            out[i] = p0[i] + p1[i] * sign;
        }
        return out;
    }
    function add2DArray(out, p0, p1, sign) {
        var len = p0.length;
        var len2 = len && p0[0].length;
        for (var i = 0; i < len; i++) {
            if (!out[i]) {
                out[i] = [];
            }
            for (var j = 0; j < len2; j++) {
                out[i][j] = p0[i][j] + p1[i][j] * sign;
            }
        }
        return out;
    }
    function fillColorStops(val0, val1) {
        var len0 = val0.length;
        var len1 = val1.length;
        var shorterArr = len0 > len1 ? val1 : val0;
        var shorterLen = Math.min(len0, len1);
        var last = shorterArr[shorterLen - 1] || { color: [0, 0, 0, 0], offset: 0 };
        for (var i = shorterLen; i < Math.max(len0, len1); i++) {
            shorterArr.push({
                offset: last.offset,
                color: last.color.slice()
            });
        }
    }
    function fillArray(val0, val1, arrDim) {
        var arr0 = val0;
        var arr1 = val1;
        if (!arr0.push || !arr1.push) {
            return;
        }
        var arr0Len = arr0.length;
        var arr1Len = arr1.length;
        if (arr0Len !== arr1Len) {
            var isPreviousLarger = arr0Len > arr1Len;
            if (isPreviousLarger) {
                arr0.length = arr1Len;
            }
            else {
                for (var i = arr0Len; i < arr1Len; i++) {
                    arr0.push(arrDim === 1 ? arr1[i] : arraySlice.call(arr1[i]));
                }
            }
        }
        var len2 = arr0[0] && arr0[0].length;
        for (var i = 0; i < arr0.length; i++) {
            if (arrDim === 1) {
                if (isNaN(arr0[i])) {
                    arr0[i] = arr1[i];
                }
            }
            else {
                for (var j = 0; j < len2; j++) {
                    if (isNaN(arr0[i][j])) {
                        arr0[i][j] = arr1[i][j];
                    }
                }
            }
        }
    }
    function cloneValue(value) {
        if (isArrayLike(value)) {
            var len = value.length;
            if (isArrayLike(value[0])) {
                var ret = [];
                for (var i = 0; i < len; i++) {
                    ret.push(arraySlice.call(value[i]));
                }
                return ret;
            }
            return arraySlice.call(value);
        }
        return value;
    }
    function rgba2String(rgba) {
        rgba[0] = Math.floor(rgba[0]) || 0;
        rgba[1] = Math.floor(rgba[1]) || 0;
        rgba[2] = Math.floor(rgba[2]) || 0;
        rgba[3] = rgba[3] == null ? 1 : rgba[3];
        return 'rgba(' + rgba.join(',') + ')';
    }
    function guessArrayDim(value) {
        return isArrayLike(value && value[0]) ? 2 : 1;
    }
    var VALUE_TYPE_NUMBER = 0;
    var VALUE_TYPE_1D_ARRAY = 1;
    var VALUE_TYPE_2D_ARRAY = 2;
    var VALUE_TYPE_COLOR = 3;
    var VALUE_TYPE_LINEAR_GRADIENT = 4;
    var VALUE_TYPE_RADIAL_GRADIENT = 5;
    var VALUE_TYPE_UNKOWN = 6;
    function isGradientValueType(valType) {
        return valType === VALUE_TYPE_LINEAR_GRADIENT || valType === VALUE_TYPE_RADIAL_GRADIENT;
    }
    function isArrayValueType(valType) {
        return valType === VALUE_TYPE_1D_ARRAY || valType === VALUE_TYPE_2D_ARRAY;
    }
    var tmpRgba = [0, 0, 0, 0];
    var Track = (function () {
        function Track(propName) {
            this.keyframes = [];
            this.discrete = false;
            this._invalid = false;
            this._needsSort = false;
            this._lastFr = 0;
            this._lastFrP = 0;
            this.propName = propName;
        }
        Track.prototype.isFinished = function () {
            return this._finished;
        };
        Track.prototype.setFinished = function () {
            this._finished = true;
            if (this._additiveTrack) {
                this._additiveTrack.setFinished();
            }
        };
        Track.prototype.needsAnimate = function () {
            return this.keyframes.length >= 1;
        };
        Track.prototype.getAdditiveTrack = function () {
            return this._additiveTrack;
        };
        Track.prototype.addKeyframe = function (time, rawValue, easing) {
            this._needsSort = true;
            var keyframes = this.keyframes;
            var len = keyframes.length;
            var discrete = false;
            var valType = VALUE_TYPE_UNKOWN;
            var value = rawValue;
            if (isArrayLike(rawValue)) {
                var arrayDim = guessArrayDim(rawValue);
                valType = arrayDim;
                if (arrayDim === 1 && !isNumber(rawValue[0])
                    || arrayDim === 2 && !isNumber(rawValue[0][0])) {
                    discrete = true;
                }
            }
            else {
                if (isNumber(rawValue) && !eqNaN(rawValue)) {
                    valType = VALUE_TYPE_NUMBER;
                }
                else if (isString(rawValue)) {
                    if (!isNaN(+rawValue)) {
                        valType = VALUE_TYPE_NUMBER;
                    }
                    else {
                        var colorArray = parse(rawValue);
                        if (colorArray) {
                            value = colorArray;
                            valType = VALUE_TYPE_COLOR;
                        }
                    }
                }
                else if (isGradientObject(rawValue)) {
                    var parsedGradient = extend({}, value);
                    parsedGradient.colorStops = map(rawValue.colorStops, function (colorStop) { return ({
                        offset: colorStop.offset,
                        color: parse(colorStop.color)
                    }); });
                    if (isLinearGradient(rawValue)) {
                        valType = VALUE_TYPE_LINEAR_GRADIENT;
                    }
                    else if (isRadialGradient(rawValue)) {
                        valType = VALUE_TYPE_RADIAL_GRADIENT;
                    }
                    value = parsedGradient;
                }
            }
            if (len === 0) {
                this.valType = valType;
            }
            else if (valType !== this.valType || valType === VALUE_TYPE_UNKOWN) {
                discrete = true;
            }
            this.discrete = this.discrete || discrete;
            var kf = {
                time: time,
                value: value,
                rawValue: rawValue,
                percent: 0
            };
            if (easing) {
                kf.easing = easing;
                kf.easingFunc = isFunction(easing)
                    ? easing
                    : easingFuncs[easing] || createCubicEasingFunc(easing);
            }
            keyframes.push(kf);
            return kf;
        };
        Track.prototype.prepare = function (maxTime, additiveTrack) {
            var kfs = this.keyframes;
            if (this._needsSort) {
                kfs.sort(function (a, b) {
                    return a.time - b.time;
                });
            }
            var valType = this.valType;
            var kfsLen = kfs.length;
            var lastKf = kfs[kfsLen - 1];
            var isDiscrete = this.discrete;
            var isArr = isArrayValueType(valType);
            var isGradient = isGradientValueType(valType);
            for (var i = 0; i < kfsLen; i++) {
                var kf = kfs[i];
                var value = kf.value;
                var lastValue = lastKf.value;
                kf.percent = kf.time / maxTime;
                if (!isDiscrete) {
                    if (isArr && i !== kfsLen - 1) {
                        fillArray(value, lastValue, valType);
                    }
                    else if (isGradient) {
                        fillColorStops(value.colorStops, lastValue.colorStops);
                    }
                }
            }
            if (!isDiscrete
                && valType !== VALUE_TYPE_RADIAL_GRADIENT
                && additiveTrack
                && this.needsAnimate()
                && additiveTrack.needsAnimate()
                && valType === additiveTrack.valType
                && !additiveTrack._finished) {
                this._additiveTrack = additiveTrack;
                var startValue = kfs[0].value;
                for (var i = 0; i < kfsLen; i++) {
                    if (valType === VALUE_TYPE_NUMBER) {
                        kfs[i].additiveValue = kfs[i].value - startValue;
                    }
                    else if (valType === VALUE_TYPE_COLOR) {
                        kfs[i].additiveValue =
                            add1DArray([], kfs[i].value, startValue, -1);
                    }
                    else if (isArrayValueType(valType)) {
                        kfs[i].additiveValue = valType === VALUE_TYPE_1D_ARRAY
                            ? add1DArray([], kfs[i].value, startValue, -1)
                            : add2DArray([], kfs[i].value, startValue, -1);
                    }
                }
            }
        };
        Track.prototype.step = function (target, percent) {
            if (this._finished) {
                return;
            }
            if (this._additiveTrack && this._additiveTrack._finished) {
                this._additiveTrack = null;
            }
            var isAdditive = this._additiveTrack != null;
            var valueKey = isAdditive ? 'additiveValue' : 'value';
            var valType = this.valType;
            var keyframes = this.keyframes;
            var kfsNum = keyframes.length;
            var propName = this.propName;
            var isValueColor = valType === VALUE_TYPE_COLOR;
            var frameIdx;
            var lastFrame = this._lastFr;
            var mathMin = Math.min;
            var frame;
            var nextFrame;
            if (kfsNum === 1) {
                frame = nextFrame = keyframes[0];
            }
            else {
                if (percent < 0) {
                    frameIdx = 0;
                }
                else if (percent < this._lastFrP) {
                    var start = mathMin(lastFrame + 1, kfsNum - 1);
                    for (frameIdx = start; frameIdx >= 0; frameIdx--) {
                        if (keyframes[frameIdx].percent <= percent) {
                            break;
                        }
                    }
                    frameIdx = mathMin(frameIdx, kfsNum - 2);
                }
                else {
                    for (frameIdx = lastFrame; frameIdx < kfsNum; frameIdx++) {
                        if (keyframes[frameIdx].percent > percent) {
                            break;
                        }
                    }
                    frameIdx = mathMin(frameIdx - 1, kfsNum - 2);
                }
                nextFrame = keyframes[frameIdx + 1];
                frame = keyframes[frameIdx];
            }
            if (!(frame && nextFrame)) {
                return;
            }
            this._lastFr = frameIdx;
            this._lastFrP = percent;
            var interval = (nextFrame.percent - frame.percent);
            var w = interval === 0 ? 1 : mathMin((percent - frame.percent) / interval, 1);
            if (nextFrame.easingFunc) {
                w = nextFrame.easingFunc(w);
            }
            var targetArr = isAdditive ? this._additiveValue
                : (isValueColor ? tmpRgba : target[propName]);
            if ((isArrayValueType(valType) || isValueColor) && !targetArr) {
                targetArr = this._additiveValue = [];
            }
            if (this.discrete) {
                target[propName] = w < 1 ? frame.rawValue : nextFrame.rawValue;
            }
            else if (isArrayValueType(valType)) {
                valType === VALUE_TYPE_1D_ARRAY
                    ? interpolate1DArray(targetArr, frame[valueKey], nextFrame[valueKey], w)
                    : interpolate2DArray(targetArr, frame[valueKey], nextFrame[valueKey], w);
            }
            else if (isGradientValueType(valType)) {
                var val = frame[valueKey];
                var nextVal_1 = nextFrame[valueKey];
                var isLinearGradient_1 = valType === VALUE_TYPE_LINEAR_GRADIENT;
                target[propName] = {
                    type: isLinearGradient_1 ? 'linear' : 'radial',
                    x: interpolateNumber(val.x, nextVal_1.x, w),
                    y: interpolateNumber(val.y, nextVal_1.y, w),
                    colorStops: map(val.colorStops, function (colorStop, idx) {
                        var nextColorStop = nextVal_1.colorStops[idx];
                        return {
                            offset: interpolateNumber(colorStop.offset, nextColorStop.offset, w),
                            color: rgba2String(interpolate1DArray([], colorStop.color, nextColorStop.color, w))
                        };
                    }),
                    global: nextVal_1.global
                };
                if (isLinearGradient_1) {
                    target[propName].x2 = interpolateNumber(val.x2, nextVal_1.x2, w);
                    target[propName].y2 = interpolateNumber(val.y2, nextVal_1.y2, w);
                }
                else {
                    target[propName].r = interpolateNumber(val.r, nextVal_1.r, w);
                }
            }
            else if (isValueColor) {
                interpolate1DArray(targetArr, frame[valueKey], nextFrame[valueKey], w);
                if (!isAdditive) {
                    target[propName] = rgba2String(targetArr);
                }
            }
            else {
                var value = interpolateNumber(frame[valueKey], nextFrame[valueKey], w);
                if (isAdditive) {
                    this._additiveValue = value;
                }
                else {
                    target[propName] = value;
                }
            }
            if (isAdditive) {
                this._addToTarget(target);
            }
        };
        Track.prototype._addToTarget = function (target) {
            var valType = this.valType;
            var propName = this.propName;
            var additiveValue = this._additiveValue;
            if (valType === VALUE_TYPE_NUMBER) {
                target[propName] = target[propName] + additiveValue;
            }
            else if (valType === VALUE_TYPE_COLOR) {
                parse(target[propName], tmpRgba);
                add1DArray(tmpRgba, tmpRgba, additiveValue, 1);
                target[propName] = rgba2String(tmpRgba);
            }
            else if (valType === VALUE_TYPE_1D_ARRAY) {
                add1DArray(target[propName], target[propName], additiveValue, 1);
            }
            else if (valType === VALUE_TYPE_2D_ARRAY) {
                add2DArray(target[propName], target[propName], additiveValue, 1);
            }
        };
        return Track;
    }());
    var Animator = (function () {
        function Animator(target, loop, allowDiscreteAnimation, additiveTo) {
            this._tracks = {};
            this._trackKeys = [];
            this._maxTime = 0;
            this._started = 0;
            this._clip = null;
            this._target = target;
            this._loop = loop;
            if (loop && additiveTo) {
                logError('Can\' use additive animation on looped animation.');
                return;
            }
            this._additiveAnimators = additiveTo;
            this._allowDiscrete = allowDiscreteAnimation;
        }
        Animator.prototype.getMaxTime = function () {
            return this._maxTime;
        };
        Animator.prototype.getDelay = function () {
            return this._delay;
        };
        Animator.prototype.getLoop = function () {
            return this._loop;
        };
        Animator.prototype.getTarget = function () {
            return this._target;
        };
        Animator.prototype.changeTarget = function (target) {
            this._target = target;
        };
        Animator.prototype.when = function (time, props, easing) {
            return this.whenWithKeys(time, props, keys(props), easing);
        };
        Animator.prototype.whenWithKeys = function (time, props, propNames, easing) {
            var tracks = this._tracks;
            for (var i = 0; i < propNames.length; i++) {
                var propName = propNames[i];
                var track = tracks[propName];
                if (!track) {
                    track = tracks[propName] = new Track(propName);
                    var initialValue = void 0;
                    var additiveTrack = this._getAdditiveTrack(propName);
                    if (additiveTrack) {
                        var addtiveTrackKfs = additiveTrack.keyframes;
                        var lastFinalKf = addtiveTrackKfs[addtiveTrackKfs.length - 1];
                        initialValue = lastFinalKf && lastFinalKf.value;
                        if (additiveTrack.valType === VALUE_TYPE_COLOR && initialValue) {
                            initialValue = rgba2String(initialValue);
                        }
                    }
                    else {
                        initialValue = this._target[propName];
                    }
                    if (initialValue == null) {
                        continue;
                    }
                    if (time > 0) {
                        track.addKeyframe(0, cloneValue(initialValue), easing);
                    }
                    this._trackKeys.push(propName);
                }
                track.addKeyframe(time, cloneValue(props[propName]), easing);
            }
            this._maxTime = Math.max(this._maxTime, time);
            return this;
        };
        Animator.prototype.pause = function () {
            this._clip.pause();
            this._paused = true;
        };
        Animator.prototype.resume = function () {
            this._clip.resume();
            this._paused = false;
        };
        Animator.prototype.isPaused = function () {
            return !!this._paused;
        };
        Animator.prototype.duration = function (duration) {
            this._maxTime = duration;
            this._force = true;
            return this;
        };
        Animator.prototype._doneCallback = function () {
            this._setTracksFinished();
            this._clip = null;
            var doneList = this._doneCbs;
            if (doneList) {
                var len = doneList.length;
                for (var i = 0; i < len; i++) {
                    doneList[i].call(this);
                }
            }
        };
        Animator.prototype._abortedCallback = function () {
            this._setTracksFinished();
            var animation = this.animation;
            var abortedList = this._abortedCbs;
            if (animation) {
                animation.removeClip(this._clip);
            }
            this._clip = null;
            if (abortedList) {
                for (var i = 0; i < abortedList.length; i++) {
                    abortedList[i].call(this);
                }
            }
        };
        Animator.prototype._setTracksFinished = function () {
            var tracks = this._tracks;
            var tracksKeys = this._trackKeys;
            for (var i = 0; i < tracksKeys.length; i++) {
                tracks[tracksKeys[i]].setFinished();
            }
        };
        Animator.prototype._getAdditiveTrack = function (trackName) {
            var additiveTrack;
            var additiveAnimators = this._additiveAnimators;
            if (additiveAnimators) {
                for (var i = 0; i < additiveAnimators.length; i++) {
                    var track = additiveAnimators[i].getTrack(trackName);
                    if (track) {
                        additiveTrack = track;
                    }
                }
            }
            return additiveTrack;
        };
        Animator.prototype.start = function (easing) {
            if (this._started > 0) {
                return;
            }
            this._started = 1;
            var self = this;
            var tracks = [];
            var maxTime = this._maxTime || 0;
            for (var i = 0; i < this._trackKeys.length; i++) {
                var propName = this._trackKeys[i];
                var track = this._tracks[propName];
                var additiveTrack = this._getAdditiveTrack(propName);
                var kfs = track.keyframes;
                var kfsNum = kfs.length;
                track.prepare(maxTime, additiveTrack);
                if (track.needsAnimate()) {
                    if (!this._allowDiscrete && track.discrete) {
                        var lastKf = kfs[kfsNum - 1];
                        if (lastKf) {
                            self._target[track.propName] = lastKf.rawValue;
                        }
                        track.setFinished();
                    }
                    else {
                        tracks.push(track);
                    }
                }
            }
            if (tracks.length || this._force) {
                var clip = new Clip({
                    life: maxTime,
                    loop: this._loop,
                    delay: this._delay || 0,
                    onframe: function (percent) {
                        self._started = 2;
                        var additiveAnimators = self._additiveAnimators;
                        if (additiveAnimators) {
                            var stillHasAdditiveAnimator = false;
                            for (var i = 0; i < additiveAnimators.length; i++) {
                                if (additiveAnimators[i]._clip) {
                                    stillHasAdditiveAnimator = true;
                                    break;
                                }
                            }
                            if (!stillHasAdditiveAnimator) {
                                self._additiveAnimators = null;
                            }
                        }
                        for (var i = 0; i < tracks.length; i++) {
                            tracks[i].step(self._target, percent);
                        }
                        var onframeList = self._onframeCbs;
                        if (onframeList) {
                            for (var i = 0; i < onframeList.length; i++) {
                                onframeList[i](self._target, percent);
                            }
                        }
                    },
                    ondestroy: function () {
                        self._doneCallback();
                    }
                });
                this._clip = clip;
                if (this.animation) {
                    this.animation.addClip(clip);
                }
                if (easing) {
                    clip.setEasing(easing);
                }
            }
            else {
                this._doneCallback();
            }
            return this;
        };
        Animator.prototype.stop = function (forwardToLast) {
            if (!this._clip) {
                return;
            }
            var clip = this._clip;
            if (forwardToLast) {
                clip.onframe(1);
            }
            this._abortedCallback();
        };
        Animator.prototype.delay = function (time) {
            this._delay = time;
            return this;
        };
        Animator.prototype.during = function (cb) {
            if (cb) {
                if (!this._onframeCbs) {
                    this._onframeCbs = [];
                }
                this._onframeCbs.push(cb);
            }
            return this;
        };
        Animator.prototype.done = function (cb) {
            if (cb) {
                if (!this._doneCbs) {
                    this._doneCbs = [];
                }
                this._doneCbs.push(cb);
            }
            return this;
        };
        Animator.prototype.aborted = function (cb) {
            if (cb) {
                if (!this._abortedCbs) {
                    this._abortedCbs = [];
                }
                this._abortedCbs.push(cb);
            }
            return this;
        };
        Animator.prototype.getClip = function () {
            return this._clip;
        };
        Animator.prototype.getTrack = function (propName) {
            return this._tracks[propName];
        };
        Animator.prototype.getTracks = function () {
            var _this = this;
            return map(this._trackKeys, function (key) { return _this._tracks[key]; });
        };
        Animator.prototype.stopTracks = function (propNames, forwardToLast) {
            if (!propNames.length || !this._clip) {
                return true;
            }
            var tracks = this._tracks;
            var tracksKeys = this._trackKeys;
            for (var i = 0; i < propNames.length; i++) {
                var track = tracks[propNames[i]];
                if (track && !track.isFinished()) {
                    if (forwardToLast) {
                        track.step(this._target, 1);
                    }
                    else if (this._started === 1) {
                        track.step(this._target, 0);
                    }
                    track.setFinished();
                }
            }
            var allAborted = true;
            for (var i = 0; i < tracksKeys.length; i++) {
                if (!tracks[tracksKeys[i]].isFinished()) {
                    allAborted = false;
                    break;
                }
            }
            if (allAborted) {
                this._abortedCallback();
            }
            return allAborted;
        };
        Animator.prototype.saveTo = function (target, trackKeys, firstOrLast) {
            if (!target) {
                return;
            }
            trackKeys = trackKeys || this._trackKeys;
            for (var i = 0; i < trackKeys.length; i++) {
                var propName = trackKeys[i];
                var track = this._tracks[propName];
                if (!track || track.isFinished()) {
                    continue;
                }
                var kfs = track.keyframes;
                var kf = kfs[firstOrLast ? 0 : kfs.length - 1];
                if (kf) {
                    target[propName] = cloneValue(kf.rawValue);
                }
            }
        };
        Animator.prototype.__changeFinalValue = function (finalProps, trackKeys) {
            trackKeys = trackKeys || keys(finalProps);
            for (var i = 0; i < trackKeys.length; i++) {
                var propName = trackKeys[i];
                var track = this._tracks[propName];
                if (!track) {
                    continue;
                }
                var kfs = track.keyframes;
                if (kfs.length > 1) {
                    var lastKf = kfs.pop();
                    track.addKeyframe(lastKf.time, finalProps[propName]);
                    track.prepare(this._maxTime, track.getAdditiveTrack());
                }
            }
        };
        return Animator;
    }());

    function getTime() {
        return new Date().getTime();
    }
    var Animation = (function (_super) {
        __extends(Animation, _super);
        function Animation(opts) {
            var _this = _super.call(this) || this;
            _this._running = false;
            _this._time = 0;
            _this._pausedTime = 0;
            _this._pauseStart = 0;
            _this._paused = false;
            opts = opts || {};
            _this.stage = opts.stage || {};
            return _this;
        }
        Animation.prototype.addClip = function (clip) {
            if (clip.animation) {
                this.removeClip(clip);
            }
            if (!this._head) {
                this._head = this._tail = clip;
            }
            else {
                this._tail.next = clip;
                clip.prev = this._tail;
                clip.next = null;
                this._tail = clip;
            }
            clip.animation = this;
        };
        Animation.prototype.addAnimator = function (animator) {
            animator.animation = this;
            var clip = animator.getClip();
            if (clip) {
                this.addClip(clip);
            }
        };
        Animation.prototype.removeClip = function (clip) {
            if (!clip.animation) {
                return;
            }
            var prev = clip.prev;
            var next = clip.next;
            if (prev) {
                prev.next = next;
            }
            else {
                this._head = next;
            }
            if (next) {
                next.prev = prev;
            }
            else {
                this._tail = prev;
            }
            clip.next = clip.prev = clip.animation = null;
        };
        Animation.prototype.removeAnimator = function (animator) {
            var clip = animator.getClip();
            if (clip) {
                this.removeClip(clip);
            }
            animator.animation = null;
        };
        Animation.prototype.update = function (notTriggerFrameAndStageUpdate) {
            var time = getTime() - this._pausedTime;
            var delta = time - this._time;
            var clip = this._head;
            while (clip) {
                var nextClip = clip.next;
                var finished = clip.step(time, delta);
                if (finished) {
                    clip.ondestroy();
                    this.removeClip(clip);
                    clip = nextClip;
                }
                else {
                    clip = nextClip;
                }
            }
            this._time = time;
            if (!notTriggerFrameAndStageUpdate) {
                this.trigger('frame', delta);
                this.stage.update && this.stage.update();
            }
        };
        Animation.prototype._startLoop = function () {
            var self = this;
            this._running = true;
            function step() {
                if (self._running) {
                    requestAnimationFrame$1(step);
                    !self._paused && self.update();
                }
            }
            requestAnimationFrame$1(step);
        };
        Animation.prototype.start = function () {
            if (this._running) {
                return;
            }
            this._time = getTime();
            this._pausedTime = 0;
            this._startLoop();
        };
        Animation.prototype.stop = function () {
            this._running = false;
        };
        Animation.prototype.pause = function () {
            if (!this._paused) {
                this._pauseStart = getTime();
                this._paused = true;
            }
        };
        Animation.prototype.resume = function () {
            if (this._paused) {
                this._pausedTime += getTime() - this._pauseStart;
                this._paused = false;
            }
        };
        Animation.prototype.clear = function () {
            var clip = this._head;
            while (clip) {
                var nextClip = clip.next;
                clip.prev = clip.next = clip.animation = null;
                clip = nextClip;
            }
            this._head = this._tail = null;
        };
        Animation.prototype.isFinished = function () {
            return this._head == null;
        };
        Animation.prototype.animate = function (target, options) {
            options = options || {};
            this.start();
            var animator = new Animator(target, options.loop);
            this.addAnimator(animator);
            return animator;
        };
        return Animation;
    }(Eventful));

    var TOUCH_CLICK_DELAY = 300;
    var globalEventSupported = env.domSupported;
    var localNativeListenerNames = (function () {
        var mouseHandlerNames = [
            'click', 'dblclick', 'mousewheel', 'wheel', 'mouseout',
            'mouseup', 'mousedown', 'mousemove', 'contextmenu'
        ];
        var touchHandlerNames = [
            'touchstart', 'touchend', 'touchmove'
        ];
        var pointerEventNameMap = {
            pointerdown: 1, pointerup: 1, pointermove: 1, pointerout: 1
        };
        var pointerHandlerNames = map(mouseHandlerNames, function (name) {
            var nm = name.replace('mouse', 'pointer');
            return pointerEventNameMap.hasOwnProperty(nm) ? nm : name;
        });
        return {
            mouse: mouseHandlerNames,
            touch: touchHandlerNames,
            pointer: pointerHandlerNames
        };
    })();
    var globalNativeListenerNames = {
        mouse: ['mousemove', 'mouseup'],
        pointer: ['pointermove', 'pointerup']
    };
    var wheelEventSupported = false;
    function isPointerFromTouch(event) {
        var pointerType = event.pointerType;
        return pointerType === 'pen' || pointerType === 'touch';
    }
    function setTouchTimer(scope) {
        scope.touching = true;
        if (scope.touchTimer != null) {
            clearTimeout(scope.touchTimer);
            scope.touchTimer = null;
        }
        scope.touchTimer = setTimeout(function () {
            scope.touching = false;
            scope.touchTimer = null;
        }, 700);
    }
    function markTouch(event) {
        event && (event.zrByTouch = true);
    }
    function normalizeGlobalEvent(instance, event) {
        return normalizeEvent(instance.dom, new FakeGlobalEvent(instance, event), true);
    }
    function isLocalEl(instance, el) {
        var elTmp = el;
        var isLocal = false;
        while (elTmp && elTmp.nodeType !== 9
            && !(isLocal = elTmp.domBelongToZr
                || (elTmp !== el && elTmp === instance.painterRoot))) {
            elTmp = elTmp.parentNode;
        }
        return isLocal;
    }
    var FakeGlobalEvent = (function () {
        function FakeGlobalEvent(instance, event) {
            this.stopPropagation = noop;
            this.stopImmediatePropagation = noop;
            this.preventDefault = noop;
            this.type = event.type;
            this.target = this.currentTarget = instance.dom;
            this.pointerType = event.pointerType;
            this.clientX = event.clientX;
            this.clientY = event.clientY;
        }
        return FakeGlobalEvent;
    }());
    var localDOMHandlers = {
        mousedown: function (event) {
            event = normalizeEvent(this.dom, event);
            this.__mayPointerCapture = [event.zrX, event.zrY];
            this.trigger('mousedown', event);
        },
        mousemove: function (event) {
            event = normalizeEvent(this.dom, event);
            var downPoint = this.__mayPointerCapture;
            if (downPoint && (event.zrX !== downPoint[0] || event.zrY !== downPoint[1])) {
                this.__togglePointerCapture(true);
            }
            this.trigger('mousemove', event);
        },
        mouseup: function (event) {
            event = normalizeEvent(this.dom, event);
            this.__togglePointerCapture(false);
            this.trigger('mouseup', event);
        },
        mouseout: function (event) {
            event = normalizeEvent(this.dom, event);
            var element = event.toElement || event.relatedTarget;
            if (!isLocalEl(this, element)) {
                if (this.__pointerCapturing) {
                    event.zrEventControl = 'no_globalout';
                }
                this.trigger('mouseout', event);
            }
        },
        wheel: function (event) {
            wheelEventSupported = true;
            event = normalizeEvent(this.dom, event);
            this.trigger('mousewheel', event);
        },
        mousewheel: function (event) {
            if (wheelEventSupported) {
                return;
            }
            event = normalizeEvent(this.dom, event);
            this.trigger('mousewheel', event);
        },
        touchstart: function (event) {
            event = normalizeEvent(this.dom, event);
            markTouch(event);
            this.__lastTouchMoment = new Date();
            this.handler.processGesture(event, 'start');
            localDOMHandlers.mousemove.call(this, event);
            localDOMHandlers.mousedown.call(this, event);
        },
        touchmove: function (event) {
            event = normalizeEvent(this.dom, event);
            markTouch(event);
            this.handler.processGesture(event, 'change');
            localDOMHandlers.mousemove.call(this, event);
        },
        touchend: function (event) {
            event = normalizeEvent(this.dom, event);
            markTouch(event);
            this.handler.processGesture(event, 'end');
            localDOMHandlers.mouseup.call(this, event);
            if (+new Date() - (+this.__lastTouchMoment) < TOUCH_CLICK_DELAY) {
                localDOMHandlers.click.call(this, event);
            }
        },
        pointerdown: function (event) {
            localDOMHandlers.mousedown.call(this, event);
        },
        pointermove: function (event) {
            if (!isPointerFromTouch(event)) {
                localDOMHandlers.mousemove.call(this, event);
            }
        },
        pointerup: function (event) {
            localDOMHandlers.mouseup.call(this, event);
        },
        pointerout: function (event) {
            if (!isPointerFromTouch(event)) {
                localDOMHandlers.mouseout.call(this, event);
            }
        }
    };
    each(['click', 'dblclick', 'contextmenu'], function (name) {
        localDOMHandlers[name] = function (event) {
            event = normalizeEvent(this.dom, event);
            this.trigger(name, event);
        };
    });
    var globalDOMHandlers = {
        pointermove: function (event) {
            if (!isPointerFromTouch(event)) {
                globalDOMHandlers.mousemove.call(this, event);
            }
        },
        pointerup: function (event) {
            globalDOMHandlers.mouseup.call(this, event);
        },
        mousemove: function (event) {
            this.trigger('mousemove', event);
        },
        mouseup: function (event) {
            var pointerCaptureReleasing = this.__pointerCapturing;
            this.__togglePointerCapture(false);
            this.trigger('mouseup', event);
            if (pointerCaptureReleasing) {
                event.zrEventControl = 'only_globalout';
                this.trigger('mouseout', event);
            }
        }
    };
    function mountLocalDOMEventListeners(instance, scope) {
        var domHandlers = scope.domHandlers;
        if (env.pointerEventsSupported) {
            each(localNativeListenerNames.pointer, function (nativeEventName) {
                mountSingleDOMEventListener(scope, nativeEventName, function (event) {
                    domHandlers[nativeEventName].call(instance, event);
                });
            });
        }
        else {
            if (env.touchEventsSupported) {
                each(localNativeListenerNames.touch, function (nativeEventName) {
                    mountSingleDOMEventListener(scope, nativeEventName, function (event) {
                        domHandlers[nativeEventName].call(instance, event);
                        setTouchTimer(scope);
                    });
                });
            }
            each(localNativeListenerNames.mouse, function (nativeEventName) {
                mountSingleDOMEventListener(scope, nativeEventName, function (event) {
                    event = getNativeEvent(event);
                    if (!scope.touching) {
                        domHandlers[nativeEventName].call(instance, event);
                    }
                });
            });
        }
    }
    function mountGlobalDOMEventListeners(instance, scope) {
        if (env.pointerEventsSupported) {
            each(globalNativeListenerNames.pointer, mount);
        }
        else if (!env.touchEventsSupported) {
            each(globalNativeListenerNames.mouse, mount);
        }
        function mount(nativeEventName) {
            function nativeEventListener(event) {
                event = getNativeEvent(event);
                if (!isLocalEl(instance, event.target)) {
                    event = normalizeGlobalEvent(instance, event);
                    scope.domHandlers[nativeEventName].call(instance, event);
                }
            }
            mountSingleDOMEventListener(scope, nativeEventName, nativeEventListener, { capture: true });
        }
    }
    function mountSingleDOMEventListener(scope, nativeEventName, listener, opt) {
        scope.mounted[nativeEventName] = listener;
        scope.listenerOpts[nativeEventName] = opt;
        addEventListener(scope.domTarget, nativeEventName, listener, opt);
    }
    function unmountDOMEventListeners(scope) {
        var mounted = scope.mounted;
        for (var nativeEventName in mounted) {
            if (mounted.hasOwnProperty(nativeEventName)) {
                removeEventListener(scope.domTarget, nativeEventName, mounted[nativeEventName], scope.listenerOpts[nativeEventName]);
            }
        }
        scope.mounted = {};
    }
    var DOMHandlerScope = (function () {
        function DOMHandlerScope(domTarget, domHandlers) {
            this.mounted = {};
            this.listenerOpts = {};
            this.touching = false;
            this.domTarget = domTarget;
            this.domHandlers = domHandlers;
        }
        return DOMHandlerScope;
    }());
    var HandlerDomProxy = (function (_super) {
        __extends(HandlerDomProxy, _super);
        function HandlerDomProxy(dom, painterRoot) {
            var _this = _super.call(this) || this;
            _this.__pointerCapturing = false;
            _this.dom = dom;
            _this.painterRoot = painterRoot;
            _this._localHandlerScope = new DOMHandlerScope(dom, localDOMHandlers);
            if (globalEventSupported) {
                _this._globalHandlerScope = new DOMHandlerScope(document, globalDOMHandlers);
            }
            mountLocalDOMEventListeners(_this, _this._localHandlerScope);
            return _this;
        }
        HandlerDomProxy.prototype.dispose = function () {
            unmountDOMEventListeners(this._localHandlerScope);
            if (globalEventSupported) {
                unmountDOMEventListeners(this._globalHandlerScope);
            }
        };
        HandlerDomProxy.prototype.setCursor = function (cursorStyle) {
            this.dom.style && (this.dom.style.cursor = cursorStyle || 'default');
        };
        HandlerDomProxy.prototype.__togglePointerCapture = function (isPointerCapturing) {
            this.__mayPointerCapture = null;
            if (globalEventSupported
                && ((+this.__pointerCapturing) ^ (+isPointerCapturing))) {
                this.__pointerCapturing = isPointerCapturing;
                var globalHandlerScope = this._globalHandlerScope;
                isPointerCapturing
                    ? mountGlobalDOMEventListeners(this, globalHandlerScope)
                    : unmountDOMEventListeners(globalHandlerScope);
            }
        };
        return HandlerDomProxy;
    }(Eventful));

    var dpr = 1;
    if (env.hasGlobalWindow) {
        dpr = Math.max(window.devicePixelRatio
            || (window.screen && window.screen.deviceXDPI / window.screen.logicalXDPI)
            || 1, 1);
    }
    var devicePixelRatio = dpr;
    var DARK_MODE_THRESHOLD = 0.4;
    var DARK_LABEL_COLOR = '#333';
    var LIGHT_LABEL_COLOR = '#ccc';
    var LIGHTER_LABEL_COLOR = '#eee';

    var mIdentity = identity;
    var EPSILON$2 = 5e-5;
    function isNotAroundZero$1(val) {
        return val > EPSILON$2 || val < -EPSILON$2;
    }
    var scaleTmp = [];
    var tmpTransform = [];
    var originTransform = create$1();
    var abs = Math.abs;
    var Transformable = (function () {
        function Transformable() {
        }
        Transformable.prototype.getLocalTransform = function (m) {
            return Transformable.getLocalTransform(this, m);
        };
        Transformable.prototype.setPosition = function (arr) {
            this.x = arr[0];
            this.y = arr[1];
        };
        Transformable.prototype.setScale = function (arr) {
            this.scaleX = arr[0];
            this.scaleY = arr[1];
        };
        Transformable.prototype.setSkew = function (arr) {
            this.skewX = arr[0];
            this.skewY = arr[1];
        };
        Transformable.prototype.setOrigin = function (arr) {
            this.originX = arr[0];
            this.originY = arr[1];
        };
        Transformable.prototype.needLocalTransform = function () {
            return isNotAroundZero$1(this.rotation)
                || isNotAroundZero$1(this.x)
                || isNotAroundZero$1(this.y)
                || isNotAroundZero$1(this.scaleX - 1)
                || isNotAroundZero$1(this.scaleY - 1)
                || isNotAroundZero$1(this.skewX)
                || isNotAroundZero$1(this.skewY);
        };
        Transformable.prototype.updateTransform = function () {
            var parentTransform = this.parent && this.parent.transform;
            var needLocalTransform = this.needLocalTransform();
            var m = this.transform;
            if (!(needLocalTransform || parentTransform)) {
                if (m) {
                    mIdentity(m);
                    this.invTransform = null;
                }
                return;
            }
            m = m || create$1();
            if (needLocalTransform) {
                this.getLocalTransform(m);
            }
            else {
                mIdentity(m);
            }
            if (parentTransform) {
                if (needLocalTransform) {
                    mul$1(m, parentTransform, m);
                }
                else {
                    copy$1(m, parentTransform);
                }
            }
            this.transform = m;
            this._resolveGlobalScaleRatio(m);
        };
        Transformable.prototype._resolveGlobalScaleRatio = function (m) {
            var globalScaleRatio = this.globalScaleRatio;
            if (globalScaleRatio != null && globalScaleRatio !== 1) {
                this.getGlobalScale(scaleTmp);
                var relX = scaleTmp[0] < 0 ? -1 : 1;
                var relY = scaleTmp[1] < 0 ? -1 : 1;
                var sx = ((scaleTmp[0] - relX) * globalScaleRatio + relX) / scaleTmp[0] || 0;
                var sy = ((scaleTmp[1] - relY) * globalScaleRatio + relY) / scaleTmp[1] || 0;
                m[0] *= sx;
                m[1] *= sx;
                m[2] *= sy;
                m[3] *= sy;
            }
            this.invTransform = this.invTransform || create$1();
            invert(this.invTransform, m);
        };
        Transformable.prototype.getComputedTransform = function () {
            var transformNode = this;
            var ancestors = [];
            while (transformNode) {
                ancestors.push(transformNode);
                transformNode = transformNode.parent;
            }
            while (transformNode = ancestors.pop()) {
                transformNode.updateTransform();
            }
            return this.transform;
        };
        Transformable.prototype.setLocalTransform = function (m) {
            if (!m) {
                return;
            }
            var sx = m[0] * m[0] + m[1] * m[1];
            var sy = m[2] * m[2] + m[3] * m[3];
            var rotation = Math.atan2(m[1], m[0]);
            var shearX = Math.PI / 2 + rotation - Math.atan2(m[3], m[2]);
            sy = Math.sqrt(sy) * Math.cos(shearX);
            sx = Math.sqrt(sx);
            this.skewX = shearX;
            this.skewY = 0;
            this.rotation = -rotation;
            this.x = +m[4];
            this.y = +m[5];
            this.scaleX = sx;
            this.scaleY = sy;
            this.originX = 0;
            this.originY = 0;
        };
        Transformable.prototype.decomposeTransform = function () {
            if (!this.transform) {
                return;
            }
            var parent = this.parent;
            var m = this.transform;
            if (parent && parent.transform) {
                mul$1(tmpTransform, parent.invTransform, m);
                m = tmpTransform;
            }
            var ox = this.originX;
            var oy = this.originY;
            if (ox || oy) {
                originTransform[4] = ox;
                originTransform[5] = oy;
                mul$1(tmpTransform, m, originTransform);
                tmpTransform[4] -= ox;
                tmpTransform[5] -= oy;
                m = tmpTransform;
            }
            this.setLocalTransform(m);
        };
        Transformable.prototype.getGlobalScale = function (out) {
            var m = this.transform;
            out = out || [];
            if (!m) {
                out[0] = 1;
                out[1] = 1;
                return out;
            }
            out[0] = Math.sqrt(m[0] * m[0] + m[1] * m[1]);
            out[1] = Math.sqrt(m[2] * m[2] + m[3] * m[3]);
            if (m[0] < 0) {
                out[0] = -out[0];
            }
            if (m[3] < 0) {
                out[1] = -out[1];
            }
            return out;
        };
        Transformable.prototype.transformCoordToLocal = function (x, y) {
            var v2 = [x, y];
            var invTransform = this.invTransform;
            if (invTransform) {
                applyTransform(v2, v2, invTransform);
            }
            return v2;
        };
        Transformable.prototype.transformCoordToGlobal = function (x, y) {
            var v2 = [x, y];
            var transform = this.transform;
            if (transform) {
                applyTransform(v2, v2, transform);
            }
            return v2;
        };
        Transformable.prototype.getLineScale = function () {
            var m = this.transform;
            return m && abs(m[0] - 1) > 1e-10 && abs(m[3] - 1) > 1e-10
                ? Math.sqrt(abs(m[0] * m[3] - m[2] * m[1]))
                : 1;
        };
        Transformable.prototype.copyTransform = function (source) {
            copyTransform(this, source);
        };
        Transformable.getLocalTransform = function (target, m) {
            m = m || [];
            var ox = target.originX || 0;
            var oy = target.originY || 0;
            var sx = target.scaleX;
            var sy = target.scaleY;
            var ax = target.anchorX;
            var ay = target.anchorY;
            var rotation = target.rotation || 0;
            var x = target.x;
            var y = target.y;
            var skewX = target.skewX ? Math.tan(target.skewX) : 0;
            var skewY = target.skewY ? Math.tan(-target.skewY) : 0;
            if (ox || oy || ax || ay) {
                var dx = ox + ax;
                var dy = oy + ay;
                m[4] = -dx * sx - skewX * dy * sy;
                m[5] = -dy * sy - skewY * dx * sx;
            }
            else {
                m[4] = m[5] = 0;
            }
            m[0] = sx;
            m[3] = sy;
            m[1] = skewY * sx;
            m[2] = skewX * sy;
            rotation && rotate(m, m, rotation);
            m[4] += ox + x;
            m[5] += oy + y;
            return m;
        };
        Transformable.initDefaultProps = (function () {
            var proto = Transformable.prototype;
            proto.scaleX =
                proto.scaleY =
                    proto.globalScaleRatio = 1;
            proto.x =
                proto.y =
                    proto.originX =
                        proto.originY =
                            proto.skewX =
                                proto.skewY =
                                    proto.rotation =
                                        proto.anchorX =
                                            proto.anchorY = 0;
        })();
        return Transformable;
    }());
    var TRANSFORMABLE_PROPS = [
        'x', 'y', 'originX', 'originY', 'anchorX', 'anchorY', 'rotation', 'scaleX', 'scaleY', 'skewX', 'skewY'
    ];
    function copyTransform(target, source) {
        for (var i = 0; i < TRANSFORMABLE_PROPS.length; i++) {
            var propName = TRANSFORMABLE_PROPS[i];
            target[propName] = source[propName];
        }
    }

    var textWidthCache = {};
    function getWidth(text, font) {
        font = font || DEFAULT_FONT;
        var cacheOfFont = textWidthCache[font];
        if (!cacheOfFont) {
            cacheOfFont = textWidthCache[font] = new LRU(500);
        }
        var width = cacheOfFont.get(text);
        if (width == null) {
            width = platformApi.measureText(text, font).width;
            cacheOfFont.put(text, width);
        }
        return width;
    }
    function innerGetBoundingRect(text, font, textAlign, textBaseline) {
        var width = getWidth(text, font);
        var height = getLineHeight(font);
        var x = adjustTextX(0, width, textAlign);
        var y = adjustTextY$1(0, height, textBaseline);
        var rect = new BoundingRect(x, y, width, height);
        return rect;
    }
    function getBoundingRect(text, font, textAlign, textBaseline) {
        var textLines = ((text || '') + '').split('\n');
        var len = textLines.length;
        if (len === 1) {
            return innerGetBoundingRect(textLines[0], font, textAlign, textBaseline);
        }
        else {
            var uniondRect = new BoundingRect(0, 0, 0, 0);
            for (var i = 0; i < textLines.length; i++) {
                var rect = innerGetBoundingRect(textLines[i], font, textAlign, textBaseline);
                i === 0 ? uniondRect.copy(rect) : uniondRect.union(rect);
            }
            return uniondRect;
        }
    }
    function adjustTextX(x, width, textAlign) {
        if (textAlign === 'right') {
            x -= width;
        }
        else if (textAlign === 'center') {
            x -= width / 2;
        }
        return x;
    }
    function adjustTextY$1(y, height, verticalAlign) {
        if (verticalAlign === 'middle') {
            y -= height / 2;
        }
        else if (verticalAlign === 'bottom') {
            y -= height;
        }
        return y;
    }
    function getLineHeight(font) {
        return getWidth('国', font);
    }
    function parsePercent(value, maxValue) {
        if (typeof value === 'string') {
            if (value.lastIndexOf('%') >= 0) {
                return parseFloat(value) / 100 * maxValue;
            }
            return parseFloat(value);
        }
        return value;
    }
    function calculateTextPosition(out, opts, rect) {
        var textPosition = opts.position || 'inside';
        var distance = opts.distance != null ? opts.distance : 5;
        var height = rect.height;
        var width = rect.width;
        var halfHeight = height / 2;
        var x = rect.x;
        var y = rect.y;
        var textAlign = 'left';
        var textVerticalAlign = 'top';
        if (textPosition instanceof Array) {
            x += parsePercent(textPosition[0], rect.width);
            y += parsePercent(textPosition[1], rect.height);
            textAlign = null;
            textVerticalAlign = null;
        }
        else {
            switch (textPosition) {
                case 'left':
                    x -= distance;
                    y += halfHeight;
                    textAlign = 'right';
                    textVerticalAlign = 'middle';
                    break;
                case 'right':
                    x += distance + width;
                    y += halfHeight;
                    textVerticalAlign = 'middle';
                    break;
                case 'top':
                    x += width / 2;
                    y -= distance;
                    textAlign = 'center';
                    textVerticalAlign = 'bottom';
                    break;
                case 'bottom':
                    x += width / 2;
                    y += height + distance;
                    textAlign = 'center';
                    break;
                case 'inside':
                    x += width / 2;
                    y += halfHeight;
                    textAlign = 'center';
                    textVerticalAlign = 'middle';
                    break;
                case 'insideLeft':
                    x += distance;
                    y += halfHeight;
                    textVerticalAlign = 'middle';
                    break;
                case 'insideRight':
                    x += width - distance;
                    y += halfHeight;
                    textAlign = 'right';
                    textVerticalAlign = 'middle';
                    break;
                case 'insideTop':
                    x += width / 2;
                    y += distance;
                    textAlign = 'center';
                    break;
                case 'insideBottom':
                    x += width / 2;
                    y += height - distance;
                    textAlign = 'center';
                    textVerticalAlign = 'bottom';
                    break;
                case 'insideTopLeft':
                    x += distance;
                    y += distance;
                    break;
                case 'insideTopRight':
                    x += width - distance;
                    y += distance;
                    textAlign = 'right';
                    break;
                case 'insideBottomLeft':
                    x += distance;
                    y += height - distance;
                    textVerticalAlign = 'bottom';
                    break;
                case 'insideBottomRight':
                    x += width - distance;
                    y += height - distance;
                    textAlign = 'right';
                    textVerticalAlign = 'bottom';
                    break;
            }
        }
        out = out || {};
        out.x = x;
        out.y = y;
        out.align = textAlign;
        out.verticalAlign = textVerticalAlign;
        return out;
    }

    var PRESERVED_NORMAL_STATE = '__zr_normal__';
    var PRIMARY_STATES_KEYS = TRANSFORMABLE_PROPS.concat(['ignore']);
    var DEFAULT_ANIMATABLE_MAP = reduce(TRANSFORMABLE_PROPS, function (obj, key) {
        obj[key] = true;
        return obj;
    }, { ignore: false });
    var tmpTextPosCalcRes = {};
    var tmpBoundingRect = new BoundingRect(0, 0, 0, 0);
    var Element = (function () {
        function Element(props) {
            this.id = guid();
            this.animators = [];
            this.currentStates = [];
            this.states = {};
            this._init(props);
        }
        Element.prototype._init = function (props) {
            this.attr(props);
        };
        Element.prototype.drift = function (dx, dy, e) {
            switch (this.draggable) {
                case 'horizontal':
                    dy = 0;
                    break;
                case 'vertical':
                    dx = 0;
                    break;
            }
            var m = this.transform;
            if (!m) {
                m = this.transform = [1, 0, 0, 1, 0, 0];
            }
            m[4] += dx;
            m[5] += dy;
            this.decomposeTransform();
            this.markRedraw();
        };
        Element.prototype.beforeUpdate = function () { };
        Element.prototype.afterUpdate = function () { };
        Element.prototype.update = function () {
            this.updateTransform();
            if (this.__dirty) {
                this.updateInnerText();
            }
        };
        Element.prototype.updateInnerText = function (forceUpdate) {
            var textEl = this._textContent;
            if (textEl && (!textEl.ignore || forceUpdate)) {
                if (!this.textConfig) {
                    this.textConfig = {};
                }
                var textConfig = this.textConfig;
                var isLocal = textConfig.local;
                var innerTransformable = textEl.innerTransformable;
                var textAlign = void 0;
                var textVerticalAlign = void 0;
                var textStyleChanged = false;
                innerTransformable.parent = isLocal ? this : null;
                var innerOrigin = false;
                innerTransformable.copyTransform(textEl);
                if (textConfig.position != null) {
                    var layoutRect = tmpBoundingRect;
                    if (textConfig.layoutRect) {
                        layoutRect.copy(textConfig.layoutRect);
                    }
                    else {
                        layoutRect.copy(this.getBoundingRect());
                    }
                    if (!isLocal) {
                        layoutRect.applyTransform(this.transform);
                    }
                    if (this.calculateTextPosition) {
                        this.calculateTextPosition(tmpTextPosCalcRes, textConfig, layoutRect);
                    }
                    else {
                        calculateTextPosition(tmpTextPosCalcRes, textConfig, layoutRect);
                    }
                    innerTransformable.x = tmpTextPosCalcRes.x;
                    innerTransformable.y = tmpTextPosCalcRes.y;
                    textAlign = tmpTextPosCalcRes.align;
                    textVerticalAlign = tmpTextPosCalcRes.verticalAlign;
                    var textOrigin = textConfig.origin;
                    if (textOrigin && textConfig.rotation != null) {
                        var relOriginX = void 0;
                        var relOriginY = void 0;
                        if (textOrigin === 'center') {
                            relOriginX = layoutRect.width * 0.5;
                            relOriginY = layoutRect.height * 0.5;
                        }
                        else {
                            relOriginX = parsePercent(textOrigin[0], layoutRect.width);
                            relOriginY = parsePercent(textOrigin[1], layoutRect.height);
                        }
                        innerOrigin = true;
                        innerTransformable.originX = -innerTransformable.x + relOriginX + (isLocal ? 0 : layoutRect.x);
                        innerTransformable.originY = -innerTransformable.y + relOriginY + (isLocal ? 0 : layoutRect.y);
                    }
                }
                if (textConfig.rotation != null) {
                    innerTransformable.rotation = textConfig.rotation;
                }
                var textOffset = textConfig.offset;
                if (textOffset) {
                    innerTransformable.x += textOffset[0];
                    innerTransformable.y += textOffset[1];
                    if (!innerOrigin) {
                        innerTransformable.originX = -textOffset[0];
                        innerTransformable.originY = -textOffset[1];
                    }
                }
                var isInside = textConfig.inside == null
                    ? (typeof textConfig.position === 'string' && textConfig.position.indexOf('inside') >= 0)
                    : textConfig.inside;
                var innerTextDefaultStyle = this._innerTextDefaultStyle || (this._innerTextDefaultStyle = {});
                var textFill = void 0;
                var textStroke = void 0;
                var autoStroke = void 0;
                if (isInside && this.canBeInsideText()) {
                    textFill = textConfig.insideFill;
                    textStroke = textConfig.insideStroke;
                    if (textFill == null || textFill === 'auto') {
                        textFill = this.getInsideTextFill();
                    }
                    if (textStroke == null || textStroke === 'auto') {
                        textStroke = this.getInsideTextStroke(textFill);
                        autoStroke = true;
                    }
                }
                else {
                    textFill = textConfig.outsideFill;
                    textStroke = textConfig.outsideStroke;
                    if (textFill == null || textFill === 'auto') {
                        textFill = this.getOutsideFill();
                    }
                    if (textStroke == null || textStroke === 'auto') {
                        textStroke = this.getOutsideStroke(textFill);
                        autoStroke = true;
                    }
                }
                textFill = textFill || '#000';
                if (textFill !== innerTextDefaultStyle.fill
                    || textStroke !== innerTextDefaultStyle.stroke
                    || autoStroke !== innerTextDefaultStyle.autoStroke
                    || textAlign !== innerTextDefaultStyle.align
                    || textVerticalAlign !== innerTextDefaultStyle.verticalAlign) {
                    textStyleChanged = true;
                    innerTextDefaultStyle.fill = textFill;
                    innerTextDefaultStyle.stroke = textStroke;
                    innerTextDefaultStyle.autoStroke = autoStroke;
                    innerTextDefaultStyle.align = textAlign;
                    innerTextDefaultStyle.verticalAlign = textVerticalAlign;
                    textEl.setDefaultTextStyle(innerTextDefaultStyle);
                }
                textEl.__dirty |= REDRAW_BIT;
                if (textStyleChanged) {
                    textEl.dirtyStyle(true);
                }
            }
        };
        Element.prototype.canBeInsideText = function () {
            return true;
        };
        Element.prototype.getInsideTextFill = function () {
            return '#fff';
        };
        Element.prototype.getInsideTextStroke = function (textFill) {
            return '#000';
        };
        Element.prototype.getOutsideFill = function () {
            return this.__zr && this.__zr.isDarkMode() ? LIGHT_LABEL_COLOR : DARK_LABEL_COLOR;
        };
        Element.prototype.getOutsideStroke = function (textFill) {
            var backgroundColor = this.__zr && this.__zr.getBackgroundColor();
            var colorArr = typeof backgroundColor === 'string' && parse(backgroundColor);
            if (!colorArr) {
                colorArr = [255, 255, 255, 1];
            }
            var alpha = colorArr[3];
            var isDark = this.__zr.isDarkMode();
            for (var i = 0; i < 3; i++) {
                colorArr[i] = colorArr[i] * alpha + (isDark ? 0 : 255) * (1 - alpha);
            }
            colorArr[3] = 1;
            return stringify(colorArr, 'rgba');
        };
        Element.prototype.traverse = function (cb, context) { };
        Element.prototype.attrKV = function (key, value) {
            if (key === 'textConfig') {
                this.setTextConfig(value);
            }
            else if (key === 'textContent') {
                this.setTextContent(value);
            }
            else if (key === 'clipPath') {
                this.setClipPath(value);
            }
            else if (key === 'extra') {
                this.extra = this.extra || {};
                extend(this.extra, value);
            }
            else {
                this[key] = value;
            }
        };
        Element.prototype.hide = function () {
            this.ignore = true;
            this.markRedraw();
        };
        Element.prototype.show = function () {
            this.ignore = false;
            this.markRedraw();
        };
        Element.prototype.attr = function (keyOrObj, value) {
            if (typeof keyOrObj === 'string') {
                this.attrKV(keyOrObj, value);
            }
            else if (isObject(keyOrObj)) {
                var obj = keyOrObj;
                var keysArr = keys(obj);
                for (var i = 0; i < keysArr.length; i++) {
                    var key = keysArr[i];
                    this.attrKV(key, keyOrObj[key]);
                }
            }
            this.markRedraw();
            return this;
        };
        Element.prototype.saveCurrentToNormalState = function (toState) {
            this._innerSaveToNormal(toState);
            var normalState = this._normalState;
            for (var i = 0; i < this.animators.length; i++) {
                var animator = this.animators[i];
                var fromStateTransition = animator.__fromStateTransition;
                if (animator.getLoop() || fromStateTransition && fromStateTransition !== PRESERVED_NORMAL_STATE) {
                    continue;
                }
                var targetName = animator.targetName;
                var target = targetName
                    ? normalState[targetName] : normalState;
                animator.saveTo(target);
            }
        };
        Element.prototype._innerSaveToNormal = function (toState) {
            var normalState = this._normalState;
            if (!normalState) {
                normalState = this._normalState = {};
            }
            if (toState.textConfig && !normalState.textConfig) {
                normalState.textConfig = this.textConfig;
            }
            this._savePrimaryToNormal(toState, normalState, PRIMARY_STATES_KEYS);
        };
        Element.prototype._savePrimaryToNormal = function (toState, normalState, primaryKeys) {
            for (var i = 0; i < primaryKeys.length; i++) {
                var key = primaryKeys[i];
                if (toState[key] != null && !(key in normalState)) {
                    normalState[key] = this[key];
                }
            }
        };
        Element.prototype.hasState = function () {
            return this.currentStates.length > 0;
        };
        Element.prototype.getState = function (name) {
            return this.states[name];
        };
        Element.prototype.ensureState = function (name) {
            var states = this.states;
            if (!states[name]) {
                states[name] = {};
            }
            return states[name];
        };
        Element.prototype.clearStates = function (noAnimation) {
            this.useState(PRESERVED_NORMAL_STATE, false, noAnimation);
        };
        Element.prototype.useState = function (stateName, keepCurrentStates, noAnimation, forceUseHoverLayer) {
            var toNormalState = stateName === PRESERVED_NORMAL_STATE;
            var hasStates = this.hasState();
            if (!hasStates && toNormalState) {
                return;
            }
            var currentStates = this.currentStates;
            var animationCfg = this.stateTransition;
            if (indexOf(currentStates, stateName) >= 0 && (keepCurrentStates || currentStates.length === 1)) {
                return;
            }
            var state;
            if (this.stateProxy && !toNormalState) {
                state = this.stateProxy(stateName);
            }
            if (!state) {
                state = (this.states && this.states[stateName]);
            }
            if (!state && !toNormalState) {
                logError("State " + stateName + " not exists.");
                return;
            }
            if (!toNormalState) {
                this.saveCurrentToNormalState(state);
            }
            var useHoverLayer = !!((state && state.hoverLayer) || forceUseHoverLayer);
            if (useHoverLayer) {
                this._toggleHoverLayerFlag(true);
            }
            this._applyStateObj(stateName, state, this._normalState, keepCurrentStates, !noAnimation && !this.__inHover && animationCfg && animationCfg.duration > 0, animationCfg);
            var textContent = this._textContent;
            var textGuide = this._textGuide;
            if (textContent) {
                textContent.useState(stateName, keepCurrentStates, noAnimation, useHoverLayer);
            }
            if (textGuide) {
                textGuide.useState(stateName, keepCurrentStates, noAnimation, useHoverLayer);
            }
            if (toNormalState) {
                this.currentStates = [];
                this._normalState = {};
            }
            else {
                if (!keepCurrentStates) {
                    this.currentStates = [stateName];
                }
                else {
                    this.currentStates.push(stateName);
                }
            }
            this._updateAnimationTargets();
            this.markRedraw();
            if (!useHoverLayer && this.__inHover) {
                this._toggleHoverLayerFlag(false);
                this.__dirty &= ~REDRAW_BIT;
            }
            return state;
        };
        Element.prototype.useStates = function (states, noAnimation, forceUseHoverLayer) {
            if (!states.length) {
                this.clearStates();
            }
            else {
                var stateObjects = [];
                var currentStates = this.currentStates;
                var len = states.length;
                var notChange = len === currentStates.length;
                if (notChange) {
                    for (var i = 0; i < len; i++) {
                        if (states[i] !== currentStates[i]) {
                            notChange = false;
                            break;
                        }
                    }
                }
                if (notChange) {
                    return;
                }
                for (var i = 0; i < len; i++) {
                    var stateName = states[i];
                    var stateObj = void 0;
                    if (this.stateProxy) {
                        stateObj = this.stateProxy(stateName, states);
                    }
                    if (!stateObj) {
                        stateObj = this.states[stateName];
                    }
                    if (stateObj) {
                        stateObjects.push(stateObj);
                    }
                }
                var lastStateObj = stateObjects[len - 1];
                var useHoverLayer = !!((lastStateObj && lastStateObj.hoverLayer) || forceUseHoverLayer);
                if (useHoverLayer) {
                    this._toggleHoverLayerFlag(true);
                }
                var mergedState = this._mergeStates(stateObjects);
                var animationCfg = this.stateTransition;
                this.saveCurrentToNormalState(mergedState);
                this._applyStateObj(states.join(','), mergedState, this._normalState, false, !noAnimation && !this.__inHover && animationCfg && animationCfg.duration > 0, animationCfg);
                var textContent = this._textContent;
                var textGuide = this._textGuide;
                if (textContent) {
                    textContent.useStates(states, noAnimation, useHoverLayer);
                }
                if (textGuide) {
                    textGuide.useStates(states, noAnimation, useHoverLayer);
                }
                this._updateAnimationTargets();
                this.currentStates = states.slice();
                this.markRedraw();
                if (!useHoverLayer && this.__inHover) {
                    this._toggleHoverLayerFlag(false);
                    this.__dirty &= ~REDRAW_BIT;
                }
            }
        };
        Element.prototype._updateAnimationTargets = function () {
            for (var i = 0; i < this.animators.length; i++) {
                var animator = this.animators[i];
                if (animator.targetName) {
                    animator.changeTarget(this[animator.targetName]);
                }
            }
        };
        Element.prototype.removeState = function (state) {
            var idx = indexOf(this.currentStates, state);
            if (idx >= 0) {
                var currentStates = this.currentStates.slice();
                currentStates.splice(idx, 1);
                this.useStates(currentStates);
            }
        };
        Element.prototype.replaceState = function (oldState, newState, forceAdd) {
            var currentStates = this.currentStates.slice();
            var idx = indexOf(currentStates, oldState);
            var newStateExists = indexOf(currentStates, newState) >= 0;
            if (idx >= 0) {
                if (!newStateExists) {
                    currentStates[idx] = newState;
                }
                else {
                    currentStates.splice(idx, 1);
                }
            }
            else if (forceAdd && !newStateExists) {
                currentStates.push(newState);
            }
            this.useStates(currentStates);
        };
        Element.prototype.toggleState = function (state, enable) {
            if (enable) {
                this.useState(state, true);
            }
            else {
                this.removeState(state);
            }
        };
        Element.prototype._mergeStates = function (states) {
            var mergedState = {};
            var mergedTextConfig;
            for (var i = 0; i < states.length; i++) {
                var state = states[i];
                extend(mergedState, state);
                if (state.textConfig) {
                    mergedTextConfig = mergedTextConfig || {};
                    extend(mergedTextConfig, state.textConfig);
                }
            }
            if (mergedTextConfig) {
                mergedState.textConfig = mergedTextConfig;
            }
            return mergedState;
        };
        Element.prototype._applyStateObj = function (stateName, state, normalState, keepCurrentStates, transition, animationCfg) {
            var needsRestoreToNormal = !(state && keepCurrentStates);
            if (state && state.textConfig) {
                this.textConfig = extend({}, keepCurrentStates ? this.textConfig : normalState.textConfig);
                extend(this.textConfig, state.textConfig);
            }
            else if (needsRestoreToNormal) {
                if (normalState.textConfig) {
                    this.textConfig = normalState.textConfig;
                }
            }
            var transitionTarget = {};
            var hasTransition = false;
            for (var i = 0; i < PRIMARY_STATES_KEYS.length; i++) {
                var key = PRIMARY_STATES_KEYS[i];
                var propNeedsTransition = transition && DEFAULT_ANIMATABLE_MAP[key];
                if (state && state[key] != null) {
                    if (propNeedsTransition) {
                        hasTransition = true;
                        transitionTarget[key] = state[key];
                    }
                    else {
                        this[key] = state[key];
                    }
                }
                else if (needsRestoreToNormal) {
                    if (normalState[key] != null) {
                        if (propNeedsTransition) {
                            hasTransition = true;
                            transitionTarget[key] = normalState[key];
                        }
                        else {
                            this[key] = normalState[key];
                        }
                    }
                }
            }
            if (!transition) {
                for (var i = 0; i < this.animators.length; i++) {
                    var animator = this.animators[i];
                    var targetName = animator.targetName;
                    if (!animator.getLoop()) {
                        animator.__changeFinalValue(targetName
                            ? (state || normalState)[targetName]
                            : (state || normalState));
                    }
                }
            }
            if (hasTransition) {
                this._transitionState(stateName, transitionTarget, animationCfg);
            }
        };
        Element.prototype._attachComponent = function (componentEl) {
            if (componentEl.__zr && !componentEl.__hostTarget) {
                if ("development" !== 'production') {
                    throw new Error('Text element has been added to zrender.');
                }
                return;
            }
            if (componentEl === this) {
                if ("development" !== 'production') {
                    throw new Error('Recursive component attachment.');
                }
                return;
            }
            var zr = this.__zr;
            if (zr) {
                componentEl.addSelfToZr(zr);
            }
            componentEl.__zr = zr;
            componentEl.__hostTarget = this;
        };
        Element.prototype._detachComponent = function (componentEl) {
            if (componentEl.__zr) {
                componentEl.removeSelfFromZr(componentEl.__zr);
            }
            componentEl.__zr = null;
            componentEl.__hostTarget = null;
        };
        Element.prototype.getClipPath = function () {
            return this._clipPath;
        };
        Element.prototype.setClipPath = function (clipPath) {
            if (this._clipPath && this._clipPath !== clipPath) {
                this.removeClipPath();
            }
            this._attachComponent(clipPath);
            this._clipPath = clipPath;
            this.markRedraw();
        };
        Element.prototype.removeClipPath = function () {
            var clipPath = this._clipPath;
            if (clipPath) {
                this._detachComponent(clipPath);
                this._clipPath = null;
                this.markRedraw();
            }
        };
        Element.prototype.getTextContent = function () {
            return this._textContent;
        };
        Element.prototype.setTextContent = function (textEl) {
            var previousTextContent = this._textContent;
            if (previousTextContent === textEl) {
                return;
            }
            if (previousTextContent && previousTextContent !== textEl) {
                this.removeTextContent();
            }
            if ("development" !== 'production') {
                if (textEl.__zr && !textEl.__hostTarget) {
                    throw new Error('Text element has been added to zrender.');
                }
            }
            textEl.innerTransformable = new Transformable();
            this._attachComponent(textEl);
            this._textContent = textEl;
            this.markRedraw();
        };
        Element.prototype.setTextConfig = function (cfg) {
            if (!this.textConfig) {
                this.textConfig = {};
            }
            extend(this.textConfig, cfg);
            this.markRedraw();
        };
        Element.prototype.removeTextConfig = function () {
            this.textConfig = null;
            this.markRedraw();
        };
        Element.prototype.removeTextContent = function () {
            var textEl = this._textContent;
            if (textEl) {
                textEl.innerTransformable = null;
                this._detachComponent(textEl);
                this._textContent = null;
                this._innerTextDefaultStyle = null;
                this.markRedraw();
            }
        };
        Element.prototype.getTextGuideLine = function () {
            return this._textGuide;
        };
        Element.prototype.setTextGuideLine = function (guideLine) {
            if (this._textGuide && this._textGuide !== guideLine) {
                this.removeTextGuideLine();
            }
            this._attachComponent(guideLine);
            this._textGuide = guideLine;
            this.markRedraw();
        };
        Element.prototype.removeTextGuideLine = function () {
            var textGuide = this._textGuide;
            if (textGuide) {
                this._detachComponent(textGuide);
                this._textGuide = null;
                this.markRedraw();
            }
        };
        Element.prototype.markRedraw = function () {
            this.__dirty |= REDRAW_BIT;
            var zr = this.__zr;
            if (zr) {
                if (this.__inHover) {
                    zr.refreshHover();
                }
                else {
                    zr.refresh();
                }
            }
            if (this.__hostTarget) {
                this.__hostTarget.markRedraw();
            }
        };
        Element.prototype.dirty = function () {
            this.markRedraw();
        };
        Element.prototype._toggleHoverLayerFlag = function (inHover) {
            this.__inHover = inHover;
            var textContent = this._textContent;
            var textGuide = this._textGuide;
            if (textContent) {
                textContent.__inHover = inHover;
            }
            if (textGuide) {
                textGuide.__inHover = inHover;
            }
        };
        Element.prototype.addSelfToZr = function (zr) {
            if (this.__zr === zr) {
                return;
            }
            this.__zr = zr;
            var animators = this.animators;
            if (animators) {
                for (var i = 0; i < animators.length; i++) {
                    zr.animation.addAnimator(animators[i]);
                }
            }
            if (this._clipPath) {
                this._clipPath.addSelfToZr(zr);
            }
            if (this._textContent) {
                this._textContent.addSelfToZr(zr);
            }
            if (this._textGuide) {
                this._textGuide.addSelfToZr(zr);
            }
        };
        Element.prototype.removeSelfFromZr = function (zr) {
            if (!this.__zr) {
                return;
            }
            this.__zr = null;
            var animators = this.animators;
            if (animators) {
                for (var i = 0; i < animators.length; i++) {
                    zr.animation.removeAnimator(animators[i]);
                }
            }
            if (this._clipPath) {
                this._clipPath.removeSelfFromZr(zr);
            }
            if (this._textContent) {
                this._textContent.removeSelfFromZr(zr);
            }
            if (this._textGuide) {
                this._textGuide.removeSelfFromZr(zr);
            }
        };
        Element.prototype.animate = function (key, loop, allowDiscreteAnimation) {
            var target = key ? this[key] : this;
            if ("development" !== 'production') {
                if (!target) {
                    logError('Property "'
                        + key
                        + '" is not existed in element '
                        + this.id);
                    return;
                }
            }
            var animator = new Animator(target, loop, allowDiscreteAnimation);
            key && (animator.targetName = key);
            this.addAnimator(animator, key);
            return animator;
        };
        Element.prototype.addAnimator = function (animator, key) {
            var zr = this.__zr;
            var el = this;
            animator.during(function () {
                el.updateDuringAnimation(key);
            }).done(function () {
                var animators = el.animators;
                var idx = indexOf(animators, animator);
                if (idx >= 0) {
                    animators.splice(idx, 1);
                }
            });
            this.animators.push(animator);
            if (zr) {
                zr.animation.addAnimator(animator);
            }
            zr && zr.wakeUp();
        };
        Element.prototype.updateDuringAnimation = function (key) {
            this.markRedraw();
        };
        Element.prototype.stopAnimation = function (scope, forwardToLast) {
            var animators = this.animators;
            var len = animators.length;
            var leftAnimators = [];
            for (var i = 0; i < len; i++) {
                var animator = animators[i];
                if (!scope || scope === animator.scope) {
                    animator.stop(forwardToLast);
                }
                else {
                    leftAnimators.push(animator);
                }
            }
            this.animators = leftAnimators;
            return this;
        };
        Element.prototype.animateTo = function (target, cfg, animationProps) {
            animateTo(this, target, cfg, animationProps);
        };
        Element.prototype.animateFrom = function (target, cfg, animationProps) {
            animateTo(this, target, cfg, animationProps, true);
        };
        Element.prototype._transitionState = function (stateName, target, cfg, animationProps) {
            var animators = animateTo(this, target, cfg, animationProps);
            for (var i = 0; i < animators.length; i++) {
                animators[i].__fromStateTransition = stateName;
            }
        };
        Element.prototype.getBoundingRect = function () {
            return null;
        };
        Element.prototype.getPaintRect = function () {
            return null;
        };
        Element.initDefaultProps = (function () {
            var elProto = Element.prototype;
            elProto.type = 'element';
            elProto.name = '';
            elProto.ignore =
                elProto.silent =
                    elProto.isGroup =
                        elProto.draggable =
                            elProto.dragging =
                                elProto.ignoreClip =
                                    elProto.__inHover = false;
            elProto.__dirty = REDRAW_BIT;
            var logs = {};
            function logDeprecatedError(key, xKey, yKey) {
                if (!logs[key + xKey + yKey]) {
                    console.warn("DEPRECATED: '" + key + "' has been deprecated. use '" + xKey + "', '" + yKey + "' instead");
                    logs[key + xKey + yKey] = true;
                }
            }
            function createLegacyProperty(key, privateKey, xKey, yKey) {
                Object.defineProperty(elProto, key, {
                    get: function () {
                        if ("development" !== 'production') {
                            logDeprecatedError(key, xKey, yKey);
                        }
                        if (!this[privateKey]) {
                            var pos = this[privateKey] = [];
                            enhanceArray(this, pos);
                        }
                        return this[privateKey];
                    },
                    set: function (pos) {
                        if ("development" !== 'production') {
                            logDeprecatedError(key, xKey, yKey);
                        }
                        this[xKey] = pos[0];
                        this[yKey] = pos[1];
                        this[privateKey] = pos;
                        enhanceArray(this, pos);
                    }
                });
                function enhanceArray(self, pos) {
                    Object.defineProperty(pos, 0, {
                        get: function () {
                            return self[xKey];
                        },
                        set: function (val) {
                            self[xKey] = val;
                        }
                    });
                    Object.defineProperty(pos, 1, {
                        get: function () {
                            return self[yKey];
                        },
                        set: function (val) {
                            self[yKey] = val;
                        }
                    });
                }
            }
            if (Object.defineProperty) {
                createLegacyProperty('position', '_legacyPos', 'x', 'y');
                createLegacyProperty('scale', '_legacyScale', 'scaleX', 'scaleY');
                createLegacyProperty('origin', '_legacyOrigin', 'originX', 'originY');
            }
        })();
        return Element;
    }());
    mixin(Element, Eventful);
    mixin(Element, Transformable);
    function animateTo(animatable, target, cfg, animationProps, reverse) {
        cfg = cfg || {};
        var animators = [];
        animateToShallow(animatable, '', animatable, target, cfg, animationProps, animators, reverse);
        var finishCount = animators.length;
        var doneHappened = false;
        var cfgDone = cfg.done;
        var cfgAborted = cfg.aborted;
        var doneCb = function () {
            doneHappened = true;
            finishCount--;
            if (finishCount <= 0) {
                doneHappened
                    ? (cfgDone && cfgDone())
                    : (cfgAborted && cfgAborted());
            }
        };
        var abortedCb = function () {
            finishCount--;
            if (finishCount <= 0) {
                doneHappened
                    ? (cfgDone && cfgDone())
                    : (cfgAborted && cfgAborted());
            }
        };
        if (!finishCount) {
            cfgDone && cfgDone();
        }
        if (animators.length > 0 && cfg.during) {
            animators[0].during(function (target, percent) {
                cfg.during(percent);
            });
        }
        for (var i = 0; i < animators.length; i++) {
            var animator = animators[i];
            if (doneCb) {
                animator.done(doneCb);
            }
            if (abortedCb) {
                animator.aborted(abortedCb);
            }
            if (cfg.force) {
                animator.duration(cfg.duration);
            }
            animator.start(cfg.easing);
        }
        return animators;
    }
    function copyArrShallow(source, target, len) {
        for (var i = 0; i < len; i++) {
            source[i] = target[i];
        }
    }
    function is2DArray(value) {
        return isArrayLike(value[0]);
    }
    function copyValue(target, source, key) {
        if (isArrayLike(source[key])) {
            if (!isArrayLike(target[key])) {
                target[key] = [];
            }
            if (isTypedArray(source[key])) {
                var len = source[key].length;
                if (target[key].length !== len) {
                    target[key] = new (source[key].constructor)(len);
                    copyArrShallow(target[key], source[key], len);
                }
            }
            else {
                var sourceArr = source[key];
                var targetArr = target[key];
                var len0 = sourceArr.length;
                if (is2DArray(sourceArr)) {
                    var len1 = sourceArr[0].length;
                    for (var i = 0; i < len0; i++) {
                        if (!targetArr[i]) {
                            targetArr[i] = Array.prototype.slice.call(sourceArr[i]);
                        }
                        else {
                            copyArrShallow(targetArr[i], sourceArr[i], len1);
                        }
                    }
                }
                else {
                    copyArrShallow(targetArr, sourceArr, len0);
                }
                targetArr.length = sourceArr.length;
            }
        }
        else {
            target[key] = source[key];
        }
    }
    function isValueSame(val1, val2) {
        return val1 === val2
            || isArrayLike(val1) && isArrayLike(val2) && is1DArraySame(val1, val2);
    }
    function is1DArraySame(arr0, arr1) {
        var len = arr0.length;
        if (len !== arr1.length) {
            return false;
        }
        for (var i = 0; i < len; i++) {
            if (arr0[i] !== arr1[i]) {
                return false;
            }
        }
        return true;
    }
    function animateToShallow(animatable, topKey, animateObj, target, cfg, animationProps, animators, reverse) {
        var targetKeys = keys(target);
        var duration = cfg.duration;
        var delay = cfg.delay;
        var additive = cfg.additive;
        var setToFinal = cfg.setToFinal;
        var animateAll = !isObject(animationProps);
        var existsAnimators = animatable.animators;
        var animationKeys = [];
        for (var k = 0; k < targetKeys.length; k++) {
            var innerKey = targetKeys[k];
            var targetVal = target[innerKey];
            if (targetVal != null && animateObj[innerKey] != null
                && (animateAll || animationProps[innerKey])) {
                if (isObject(targetVal)
                    && !isArrayLike(targetVal)
                    && !isGradientObject(targetVal)) {
                    if (topKey) {
                        if (!reverse) {
                            animateObj[innerKey] = targetVal;
                            animatable.updateDuringAnimation(topKey);
                        }
                        continue;
                    }
                    animateToShallow(animatable, innerKey, animateObj[innerKey], targetVal, cfg, animationProps && animationProps[innerKey], animators, reverse);
                }
                else {
                    animationKeys.push(innerKey);
                }
            }
            else if (!reverse) {
                animateObj[innerKey] = targetVal;
                animatable.updateDuringAnimation(topKey);
                animationKeys.push(innerKey);
            }
        }
        var keyLen = animationKeys.length;
        if (!additive && keyLen) {
            for (var i = 0; i < existsAnimators.length; i++) {
                var animator = existsAnimators[i];
                if (animator.targetName === topKey) {
                    var allAborted = animator.stopTracks(animationKeys);
                    if (allAborted) {
                        var idx = indexOf(existsAnimators, animator);
                        existsAnimators.splice(idx, 1);
                    }
                }
            }
        }
        if (!cfg.force) {
            animationKeys = filter(animationKeys, function (key) { return !isValueSame(target[key], animateObj[key]); });
            keyLen = animationKeys.length;
        }
        if (keyLen > 0
            || (cfg.force && !animators.length)) {
            var revertedSource = void 0;
            var reversedTarget = void 0;
            var sourceClone = void 0;
            if (reverse) {
                reversedTarget = {};
                if (setToFinal) {
                    revertedSource = {};
                }
                for (var i = 0; i < keyLen; i++) {
                    var innerKey = animationKeys[i];
                    reversedTarget[innerKey] = animateObj[innerKey];
                    if (setToFinal) {
                        revertedSource[innerKey] = target[innerKey];
                    }
                    else {
                        animateObj[innerKey] = target[innerKey];
                    }
                }
            }
            else if (setToFinal) {
                sourceClone = {};
                for (var i = 0; i < keyLen; i++) {
                    var innerKey = animationKeys[i];
                    sourceClone[innerKey] = cloneValue(animateObj[innerKey]);
                    copyValue(animateObj, target, innerKey);
                }
            }
            var animator = new Animator(animateObj, false, false, additive ? filter(existsAnimators, function (animator) { return animator.targetName === topKey; }) : null);
            animator.targetName = topKey;
            if (cfg.scope) {
                animator.scope = cfg.scope;
            }
            if (setToFinal && revertedSource) {
                animator.whenWithKeys(0, revertedSource, animationKeys);
            }
            if (sourceClone) {
                animator.whenWithKeys(0, sourceClone, animationKeys);
            }
            animator.whenWithKeys(duration == null ? 500 : duration, reverse ? reversedTarget : target, animationKeys).delay(delay || 0);
            animatable.addAnimator(animator, topKey);
            animators.push(animator);
        }
    }

    var Group = (function (_super) {
        __extends(Group, _super);
        function Group(opts) {
            var _this = _super.call(this) || this;
            _this.isGroup = true;
            _this._children = [];
            _this.attr(opts);
            return _this;
        }
        Group.prototype.childrenRef = function () {
            return this._children;
        };
        Group.prototype.children = function () {
            return this._children.slice();
        };
        Group.prototype.childAt = function (idx) {
            return this._children[idx];
        };
        Group.prototype.childOfName = function (name) {
            var children = this._children;
            for (var i = 0; i < children.length; i++) {
                if (children[i].name === name) {
                    return children[i];
                }
            }
        };
        Group.prototype.childCount = function () {
            return this._children.length;
        };
        Group.prototype.add = function (child) {
            if (child) {
                if (child !== this && child.parent !== this) {
                    this._children.push(child);
                    this._doAdd(child);
                }
                if ("development" !== 'production') {
                    if (child.__hostTarget) {
                        throw 'This elemenet has been used as an attachment';
                    }
                }
            }
            return this;
        };
        Group.prototype.addBefore = function (child, nextSibling) {
            if (child && child !== this && child.parent !== this
                && nextSibling && nextSibling.parent === this) {
                var children = this._children;
                var idx = children.indexOf(nextSibling);
                if (idx >= 0) {
                    children.splice(idx, 0, child);
                    this._doAdd(child);
                }
            }
            return this;
        };
        Group.prototype.replace = function (oldChild, newChild) {
            var idx = indexOf(this._children, oldChild);
            if (idx >= 0) {
                this.replaceAt(newChild, idx);
            }
            return this;
        };
        Group.prototype.replaceAt = function (child, index) {
            var children = this._children;
            var old = children[index];
            if (child && child !== this && child.parent !== this && child !== old) {
                children[index] = child;
                old.parent = null;
                var zr = this.__zr;
                if (zr) {
                    old.removeSelfFromZr(zr);
                }
                this._doAdd(child);
            }
            return this;
        };
        Group.prototype._doAdd = function (child) {
            if (child.parent) {
                child.parent.remove(child);
            }
            child.parent = this;
            var zr = this.__zr;
            if (zr && zr !== child.__zr) {
                child.addSelfToZr(zr);
            }
            zr && zr.refresh();
        };
        Group.prototype.remove = function (child) {
            var zr = this.__zr;
            var children = this._children;
            var idx = indexOf(children, child);
            if (idx < 0) {
                return this;
            }
            children.splice(idx, 1);
            child.parent = null;
            if (zr) {
                child.removeSelfFromZr(zr);
            }
            zr && zr.refresh();
            return this;
        };
        Group.prototype.removeAll = function () {
            var children = this._children;
            var zr = this.__zr;
            for (var i = 0; i < children.length; i++) {
                var child = children[i];
                if (zr) {
                    child.removeSelfFromZr(zr);
                }
                child.parent = null;
            }
            children.length = 0;
            return this;
        };
        Group.prototype.eachChild = function (cb, context) {
            var children = this._children;
            for (var i = 0; i < children.length; i++) {
                var child = children[i];
                cb.call(context, child, i);
            }
            return this;
        };
        Group.prototype.traverse = function (cb, context) {
            for (var i = 0; i < this._children.length; i++) {
                var child = this._children[i];
                var stopped = cb.call(context, child);
                if (child.isGroup && !stopped) {
                    child.traverse(cb, context);
                }
            }
            return this;
        };
        Group.prototype.addSelfToZr = function (zr) {
            _super.prototype.addSelfToZr.call(this, zr);
            for (var i = 0; i < this._children.length; i++) {
                var child = this._children[i];
                child.addSelfToZr(zr);
            }
        };
        Group.prototype.removeSelfFromZr = function (zr) {
            _super.prototype.removeSelfFromZr.call(this, zr);
            for (var i = 0; i < this._children.length; i++) {
                var child = this._children[i];
                child.removeSelfFromZr(zr);
            }
        };
        Group.prototype.getBoundingRect = function (includeChildren) {
            var tmpRect = new BoundingRect(0, 0, 0, 0);
            var children = includeChildren || this._children;
            var tmpMat = [];
            var rect = null;
            for (var i = 0; i < children.length; i++) {
                var child = children[i];
                if (child.ignore || child.invisible) {
                    continue;
                }
                var childRect = child.getBoundingRect();
                var transform = child.getLocalTransform(tmpMat);
                if (transform) {
                    BoundingRect.applyTransform(tmpRect, childRect, transform);
                    rect = rect || tmpRect.clone();
                    rect.union(tmpRect);
                }
                else {
                    rect = rect || childRect.clone();
                    rect.union(childRect);
                }
            }
            return rect || tmpRect;
        };
        return Group;
    }(Element));
    Group.prototype.type = 'group';

    /*!
    * ZRender, a high performance 2d drawing library.
    *
    * Copyright (c) 2013, Baidu Inc.
    * All rights reserved.
    *
    * LICENSE
    * https://github.com/ecomfe/zrender/blob/master/LICENSE.txt
    */
    var painterCtors = {};
    var instances = {};
    function delInstance(id) {
        delete instances[id];
    }
    function isDarkMode(backgroundColor) {
        if (!backgroundColor) {
            return false;
        }
        if (typeof backgroundColor === 'string') {
            return lum(backgroundColor, 1) < DARK_MODE_THRESHOLD;
        }
        else if (backgroundColor.colorStops) {
            var colorStops = backgroundColor.colorStops;
            var totalLum = 0;
            var len = colorStops.length;
            for (var i = 0; i < len; i++) {
                totalLum += lum(colorStops[i].color, 1);
            }
            totalLum /= len;
            return totalLum < DARK_MODE_THRESHOLD;
        }
        return false;
    }
    var ZRender = (function () {
        function ZRender(id, dom, opts) {
            var _this = this;
            this._sleepAfterStill = 10;
            this._stillFrameAccum = 0;
            this._needsRefresh = true;
            this._needsRefreshHover = true;
            this._darkMode = false;
            opts = opts || {};
            this.dom = dom;
            this.id = id;
            var storage = new Storage();
            var rendererType = opts.renderer || 'canvas';
            if (!painterCtors[rendererType]) {
                rendererType = keys(painterCtors)[0];
            }
            if ("development" !== 'production') {
                if (!painterCtors[rendererType]) {
                    throw new Error("Renderer '" + rendererType + "' is not imported. Please import it first.");
                }
            }
            opts.useDirtyRect = opts.useDirtyRect == null
                ? false
                : opts.useDirtyRect;
            var painter = new painterCtors[rendererType](dom, storage, opts, id);
            var ssrMode = opts.ssr || painter.ssrOnly;
            this.storage = storage;
            this.painter = painter;
            var handerProxy = (!env.node && !env.worker && !ssrMode)
                ? new HandlerDomProxy(painter.getViewportRoot(), painter.root)
                : null;
            var useCoarsePointer = opts.useCoarsePointer;
            var usePointerSize = (useCoarsePointer == null || useCoarsePointer === 'auto')
                ? env.touchEventsSupported
                : !!useCoarsePointer;
            var defaultPointerSize = 44;
            var pointerSize;
            if (usePointerSize) {
                pointerSize = retrieve2(opts.pointerSize, defaultPointerSize);
            }
            this.handler = new Handler(storage, painter, handerProxy, painter.root, pointerSize);
            this.animation = new Animation({
                stage: {
                    update: ssrMode ? null : function () { return _this._flush(true); }
                }
            });
            if (!ssrMode) {
                this.animation.start();
            }
        }
        ZRender.prototype.add = function (el) {
            if (!el) {
                return;
            }
            this.storage.addRoot(el);
            el.addSelfToZr(this);
            this.refresh();
        };
        ZRender.prototype.remove = function (el) {
            if (!el) {
                return;
            }
            this.storage.delRoot(el);
            el.removeSelfFromZr(this);
            this.refresh();
        };
        ZRender.prototype.configLayer = function (zLevel, config) {
            if (this.painter.configLayer) {
                this.painter.configLayer(zLevel, config);
            }
            this.refresh();
        };
        ZRender.prototype.setBackgroundColor = function (backgroundColor) {
            if (this.painter.setBackgroundColor) {
                this.painter.setBackgroundColor(backgroundColor);
            }
            this.refresh();
            this._backgroundColor = backgroundColor;
            this._darkMode = isDarkMode(backgroundColor);
        };
        ZRender.prototype.getBackgroundColor = function () {
            return this._backgroundColor;
        };
        ZRender.prototype.setDarkMode = function (darkMode) {
            this._darkMode = darkMode;
        };
        ZRender.prototype.isDarkMode = function () {
            return this._darkMode;
        };
        ZRender.prototype.refreshImmediately = function (fromInside) {
            if (!fromInside) {
                this.animation.update(true);
            }
            this._needsRefresh = false;
            this.painter.refresh();
            this._needsRefresh = false;
        };
        ZRender.prototype.refresh = function () {
            this._needsRefresh = true;
            this.animation.start();
        };
        ZRender.prototype.flush = function () {
            this._flush(false);
        };
        ZRender.prototype._flush = function (fromInside) {
            var triggerRendered;
            var start = getTime();
            if (this._needsRefresh) {
                triggerRendered = true;
                this.refreshImmediately(fromInside);
            }
            if (this._needsRefreshHover) {
                triggerRendered = true;
                this.refreshHoverImmediately();
            }
            var end = getTime();
            if (triggerRendered) {
                this._stillFrameAccum = 0;
                this.trigger('rendered', {
                    elapsedTime: end - start
                });
            }
            else if (this._sleepAfterStill > 0) {
                this._stillFrameAccum++;
                if (this._stillFrameAccum > this._sleepAfterStill) {
                    this.animation.stop();
                }
            }
        };
        ZRender.prototype.setSleepAfterStill = function (stillFramesCount) {
            this._sleepAfterStill = stillFramesCount;
        };
        ZRender.prototype.wakeUp = function () {
            this.animation.start();
            this._stillFrameAccum = 0;
        };
        ZRender.prototype.refreshHover = function () {
            this._needsRefreshHover = true;
        };
        ZRender.prototype.refreshHoverImmediately = function () {
            this._needsRefreshHover = false;
            if (this.painter.refreshHover && this.painter.getType() === 'canvas') {
                this.painter.refreshHover();
            }
        };
        ZRender.prototype.resize = function (opts) {
            opts = opts || {};
            this.painter.resize(opts.width, opts.height);
            this.handler.resize();
        };
        ZRender.prototype.clearAnimation = function () {
            this.animation.clear();
        };
        ZRender.prototype.getWidth = function () {
            return this.painter.getWidth();
        };
        ZRender.prototype.getHeight = function () {
            return this.painter.getHeight();
        };
        ZRender.prototype.setCursorStyle = function (cursorStyle) {
            this.handler.setCursorStyle(cursorStyle);
        };
        ZRender.prototype.findHover = function (x, y) {
            return this.handler.findHover(x, y);
        };
        ZRender.prototype.on = function (eventName, eventHandler, context) {
            this.handler.on(eventName, eventHandler, context);
            return this;
        };
        ZRender.prototype.off = function (eventName, eventHandler) {
            this.handler.off(eventName, eventHandler);
        };
        ZRender.prototype.trigger = function (eventName, event) {
            this.handler.trigger(eventName, event);
        };
        ZRender.prototype.clear = function () {
            var roots = this.storage.getRoots();
            for (var i = 0; i < roots.length; i++) {
                if (roots[i] instanceof Group) {
                    roots[i].removeSelfFromZr(this);
                }
            }
            this.storage.delAllRoots();
            this.painter.clear();
        };
        ZRender.prototype.dispose = function () {
            this.animation.stop();
            this.clear();
            this.storage.dispose();
            this.painter.dispose();
            this.handler.dispose();
            this.animation =
                this.storage =
                    this.painter =
                        this.handler = null;
            delInstance(this.id);
        };
        return ZRender;
    }());
    function init(dom, opts) {
        var zr = new ZRender(guid(), dom, opts);
        instances[zr.id] = zr;
        return zr;
    }
    function dispose(zr) {
        zr.dispose();
    }
    function disposeAll() {
        for (var key in instances) {
            if (instances.hasOwnProperty(key)) {
                instances[key].dispose();
            }
        }
        instances = {};
    }
    function getInstance(id) {
        return instances[id];
    }
    function registerPainter(name, Ctor) {
        painterCtors[name] = Ctor;
    }
    var version = '5.4.4';

    var zrender = /*#__PURE__*/Object.freeze({
        __proto__: null,
        init: init,
        dispose: dispose,
        disposeAll: disposeAll,
        getInstance: getInstance,
        registerPainter: registerPainter,
        version: version
    });

    var RADIAN_EPSILON = 1e-4; // Although chrome already enlarge this number to 100 for `toFixed`, but
    // we sill follow the spec for compatibility.

    var ROUND_SUPPORTED_PRECISION_MAX = 20;

    function _trim(str) {
      return str.replace(/^\s+|\s+$/g, '');
    }
    /**
     * Linear mapping a value from domain to range
     * @param  val
     * @param  domain Domain extent domain[0] can be bigger than domain[1]
     * @param  range  Range extent range[0] can be bigger than range[1]
     * @param  clamp Default to be false
     */


    function linearMap(val, domain, range, clamp) {
      var d0 = domain[0];
      var d1 = domain[1];
      var r0 = range[0];
      var r1 = range[1];
      var subDomain = d1 - d0;
      var subRange = r1 - r0;

      if (subDomain === 0) {
        return subRange === 0 ? r0 : (r0 + r1) / 2;
      } // Avoid accuracy problem in edge, such as
      // 146.39 - 62.83 === 83.55999999999999.
      // See echarts/test/ut/spec/util/number.js#linearMap#accuracyError
      // It is a little verbose for efficiency considering this method
      // is a hotspot.


      if (clamp) {
        if (subDomain > 0) {
          if (val <= d0) {
            return r0;
          } else if (val >= d1) {
            return r1;
          }
        } else {
          if (val >= d0) {
            return r0;
          } else if (val <= d1) {
            return r1;
          }
        }
      } else {
        if (val === d0) {
          return r0;
        }

        if (val === d1) {
          return r1;
        }
      }

      return (val - d0) / subDomain * subRange + r0;
    }
    /**
     * Convert a percent string to absolute number.
     * Returns NaN if percent is not a valid string or number
     */

    function parsePercent$1(percent, all) {
      switch (percent) {
        case 'center':
        case 'middle':
          percent = '50%';
          break;

        case 'left':
        case 'top':
          percent = '0%';
          break;

        case 'right':
        case 'bottom':
          percent = '100%';
          break;
      }

      if (isString(percent)) {
        if (_trim(percent).match(/%$/)) {
          return parseFloat(percent) / 100 * all;
        }

        return parseFloat(percent);
      }

      return percent == null ? NaN : +percent;
    }
    function round(x, precision, returnStr) {
      if (precision == null) {
        precision = 10;
      } // Avoid range error


      precision = Math.min(Math.max(0, precision), ROUND_SUPPORTED_PRECISION_MAX); // PENDING: 1.005.toFixed(2) is '1.00' rather than '1.01'

      x = (+x).toFixed(precision);
      return returnStr ? x : +x;
    }
    /**
     * Inplacd asc sort arr.
     * The input arr will be modified.
     */

    function asc(arr) {
      arr.sort(function (a, b) {
        return a - b;
      });
      return arr;
    }
    /**
     * Get precision.
     */

    function getPrecision(val) {
      val = +val;

      if (isNaN(val)) {
        return 0;
      } // It is much faster than methods converting number to string as follows
      //      let tmp = val.toString();
      //      return tmp.length - 1 - tmp.indexOf('.');
      // especially when precision is low
      // Notice:
      // (1) If the loop count is over about 20, it is slower than `getPrecisionSafe`.
      //     (see https://jsbench.me/2vkpcekkvw/1)
      // (2) If the val is less than for example 1e-15, the result may be incorrect.
      //     (see test/ut/spec/util/number.test.ts `getPrecision_equal_random`)


      if (val > 1e-14) {
        var e = 1;

        for (var i = 0; i < 15; i++, e *= 10) {
          if (Math.round(val * e) / e === val) {
            return i;
          }
        }
      }

      return getPrecisionSafe(val);
    }
    /**
     * Get precision with slow but safe method
     */

    function getPrecisionSafe(val) {
      // toLowerCase for: '3.4E-12'
      var str = val.toString().toLowerCase(); // Consider scientific notation: '3.4e-12' '3.4e+12'

      var eIndex = str.indexOf('e');
      var exp = eIndex > 0 ? +str.slice(eIndex + 1) : 0;
      var significandPartLen = eIndex > 0 ? eIndex : str.length;
      var dotIndex = str.indexOf('.');
      var decimalPartLen = dotIndex < 0 ? 0 : significandPartLen - 1 - dotIndex;
      return Math.max(0, decimalPartLen - exp);
    }
    /**
     * Minimal dicernible data precisioin according to a single pixel.
     */

    function getPixelPrecision(dataExtent, pixelExtent) {
      var log = Math.log;
      var LN10 = Math.LN10;
      var dataQuantity = Math.floor(log(dataExtent[1] - dataExtent[0]) / LN10);
      var sizeQuantity = Math.round(log(Math.abs(pixelExtent[1] - pixelExtent[0])) / LN10); // toFixed() digits argument must be between 0 and 20.

      var precision = Math.min(Math.max(-dataQuantity + sizeQuantity, 0), 20);
      return !isFinite(precision) ? 20 : precision;
    }
    /**
     * Get a data of given precision, assuring the sum of percentages
     * in valueList is 1.
     * The largest remainder method is used.
     * https://en.wikipedia.org/wiki/Largest_remainder_method
     *
     * @param valueList a list of all data
     * @param idx index of the data to be processed in valueList
     * @param precision integer number showing digits of precision
     * @return percent ranging from 0 to 100
     */

    function getPercentWithPrecision(valueList, idx, precision) {
      if (!valueList[idx]) {
        return 0;
      }

      var seats = getPercentSeats(valueList, precision);
      return seats[idx] || 0;
    }
    /**
     * Get a data of given precision, assuring the sum of percentages
     * in valueList is 1.
     * The largest remainder method is used.
     * https://en.wikipedia.org/wiki/Largest_remainder_method
     *
     * @param valueList a list of all data
     * @param precision integer number showing digits of precision
     * @return {Array<number>}
     */

    function getPercentSeats(valueList, precision) {
      var sum = reduce(valueList, function (acc, val) {
        return acc + (isNaN(val) ? 0 : val);
      }, 0);

      if (sum === 0) {
        return [];
      }

      var digits = Math.pow(10, precision);
      var votesPerQuota = map(valueList, function (val) {
        return (isNaN(val) ? 0 : val) / sum * digits * 100;
      });
      var targetSeats = digits * 100;
      var seats = map(votesPerQuota, function (votes) {
        // Assign automatic seats.
        return Math.floor(votes);
      });
      var currentSum = reduce(seats, function (acc, val) {
        return acc + val;
      }, 0);
      var remainder = map(votesPerQuota, function (votes, idx) {
        return votes - seats[idx];
      }); // Has remainding votes.

      while (currentSum < targetSeats) {
        // Find next largest remainder.
        var max = Number.NEGATIVE_INFINITY;
        var maxId = null;

        for (var i = 0, len = remainder.length; i < len; ++i) {
          if (remainder[i] > max) {
            max = remainder[i];
            maxId = i;
          }
        } // Add a vote to max remainder.


        ++seats[maxId];
        remainder[maxId] = 0;
        ++currentSum;
      }

      return map(seats, function (seat) {
        return seat / digits;
      });
    }
    /**
     * Solve the floating point adding problem like 0.1 + 0.2 === 0.30000000000000004
     * See <http://0.30000000000000004.com/>
     */

    function addSafe(val0, val1) {
      var maxPrecision = Math.max(getPrecision(val0), getPrecision(val1)); // const multiplier = Math.pow(10, maxPrecision);
      // return (Math.round(val0 * multiplier) + Math.round(val1 * multiplier)) / multiplier;

      var sum = val0 + val1; // // PENDING: support more?

      return maxPrecision > ROUND_SUPPORTED_PRECISION_MAX ? sum : round(sum, maxPrecision);
    } // Number.MAX_SAFE_INTEGER, ie do not support.

    var MAX_SAFE_INTEGER = 9007199254740991;
    /**
     * To 0 - 2 * PI, considering negative radian.
     */

    function remRadian(radian) {
      var pi2 = Math.PI * 2;
      return (radian % pi2 + pi2) % pi2;
    }
    /**
     * @param {type} radian
     * @return {boolean}
     */

    function isRadianAroundZero(val) {
      return val > -RADIAN_EPSILON && val < RADIAN_EPSILON;
    } // eslint-disable-next-line

    var TIME_REG = /^(?:(\d{4})(?:[-\/](\d{1,2})(?:[-\/](\d{1,2})(?:[T ](\d{1,2})(?::(\d{1,2})(?::(\d{1,2})(?:[.,](\d+))?)?)?(Z|[\+\-]\d\d:?\d\d)?)?)?)?)?$/; // jshint ignore:line

    /**
     * @param value valid type: number | string | Date, otherwise return `new Date(NaN)`
     *   These values can be accepted:
     *   + An instance of Date, represent a time in its own time zone.
     *   + Or string in a subset of ISO 8601, only including:
     *     + only year, month, date: '2012-03', '2012-03-01', '2012-03-01 05', '2012-03-01 05:06',
     *     + separated with T or space: '2012-03-01T12:22:33.123', '2012-03-01 12:22:33.123',
     *     + time zone: '2012-03-01T12:22:33Z', '2012-03-01T12:22:33+8000', '2012-03-01T12:22:33-05:00',
     *     all of which will be treated as local time if time zone is not specified
     *     (see <https://momentjs.com/>).
     *   + Or other string format, including (all of which will be treated as local time):
     *     '2012', '2012-3-1', '2012/3/1', '2012/03/01',
     *     '2009/6/12 2:00', '2009/6/12 2:05:08', '2009/6/12 2:05:08.123'
     *   + a timestamp, which represent a time in UTC.
     * @return date Never be null/undefined. If invalid, return `new Date(NaN)`.
     */

    function parseDate(value) {
      if (value instanceof Date) {
        return value;
      } else if (isString(value)) {
        // Different browsers parse date in different way, so we parse it manually.
        // Some other issues:
        // new Date('1970-01-01') is UTC,
        // new Date('1970/01/01') and new Date('1970-1-01') is local.
        // See issue #3623
        var match = TIME_REG.exec(value);

        if (!match) {
          // return Invalid Date.
          return new Date(NaN);
        } // Use local time when no timezone offset is specified.


        if (!match[8]) {
          // match[n] can only be string or undefined.
          // But take care of '12' + 1 => '121'.
          return new Date(+match[1], +(match[2] || 1) - 1, +match[3] || 1, +match[4] || 0, +(match[5] || 0), +match[6] || 0, match[7] ? +match[7].substring(0, 3) : 0);
        } // Timezoneoffset of Javascript Date has considered DST (Daylight Saving Time,
        // https://tc39.github.io/ecma262/#sec-daylight-saving-time-adjustment).
        // For example, system timezone is set as "Time Zone: America/Toronto",
        // then these code will get different result:
        // `new Date(1478411999999).getTimezoneOffset();  // get 240`
        // `new Date(1478412000000).getTimezoneOffset();  // get 300`
        // So we should not use `new Date`, but use `Date.UTC`.
        else {
            var hour = +match[4] || 0;

            if (match[8].toUpperCase() !== 'Z') {
              hour -= +match[8].slice(0, 3);
            }

            return new Date(Date.UTC(+match[1], +(match[2] || 1) - 1, +match[3] || 1, hour, +(match[5] || 0), +match[6] || 0, match[7] ? +match[7].substring(0, 3) : 0));
          }
      } else if (value == null) {
        return new Date(NaN);
      }

      return new Date(Math.round(value));
    }
    /**
     * Quantity of a number. e.g. 0.1, 1, 10, 100
     *
     * @param val
     * @return
     */

    function quantity(val) {
      return Math.pow(10, quantityExponent(val));
    }
    /**
     * Exponent of the quantity of a number
     * e.g., 1234 equals to 1.234*10^3, so quantityExponent(1234) is 3
     *
     * @param val non-negative value
     * @return
     */

    function quantityExponent(val) {
      if (val === 0) {
        return 0;
      }

      var exp = Math.floor(Math.log(val) / Math.LN10);
      /**
       * exp is expected to be the rounded-down result of the base-10 log of val.
       * But due to the precision loss with Math.log(val), we need to restore it
       * using 10^exp to make sure we can get val back from exp. #11249
       */

      if (val / Math.pow(10, exp) >= 10) {
        exp++;
      }

      return exp;
    }
    /**
     * find a “nice” number approximately equal to x. Round the number if round = true,
     * take ceiling if round = false. The primary observation is that the “nicest”
     * numbers in decimal are 1, 2, and 5, and all power-of-ten multiples of these numbers.
     *
     * See "Nice Numbers for Graph Labels" of Graphic Gems.
     *
     * @param  val Non-negative value.
     * @param  round
     * @return Niced number
     */

    function nice(val, round) {
      var exponent = quantityExponent(val);
      var exp10 = Math.pow(10, exponent);
      var f = val / exp10; // 1 <= f < 10

      var nf;

      if (round) {
        if (f < 1.5) {
          nf = 1;
        } else if (f < 2.5) {
          nf = 2;
        } else if (f < 4) {
          nf = 3;
        } else if (f < 7) {
          nf = 5;
        } else {
          nf = 10;
        }
      } else {
        if (f < 1) {
          nf = 1;
        } else if (f < 2) {
          nf = 2;
        } else if (f < 3) {
          nf = 3;
        } else if (f < 5) {
          nf = 5;
        } else {
          nf = 10;
        }
      }

      val = nf * exp10; // Fix 3 * 0.1 === 0.30000000000000004 issue (see IEEE 754).
      // 20 is the uppper bound of toFixed.

      return exponent >= -20 ? +val.toFixed(exponent < 0 ? -exponent : 0) : val;
    }
    /**
     * This code was copied from "d3.js"
     * <https://github.com/d3/d3/blob/9cc9a875e636a1dcf36cc1e07bdf77e1ad6e2c74/src/arrays/quantile.js>.
     * See the license statement at the head of this file.
     * @param ascArr
     */

    function quantile(ascArr, p) {
      var H = (ascArr.length - 1) * p + 1;
      var h = Math.floor(H);
      var v = +ascArr[h - 1];
      var e = H - h;
      return e ? v + e * (ascArr[h] - v) : v;
    }
    /**
     * Order intervals asc, and split them when overlap.
     * expect(numberUtil.reformIntervals([
     *     {interval: [18, 62], close: [1, 1]},
     *     {interval: [-Infinity, -70], close: [0, 0]},
     *     {interval: [-70, -26], close: [1, 1]},
     *     {interval: [-26, 18], close: [1, 1]},
     *     {interval: [62, 150], close: [1, 1]},
     *     {interval: [106, 150], close: [1, 1]},
     *     {interval: [150, Infinity], close: [0, 0]}
     * ])).toEqual([
     *     {interval: [-Infinity, -70], close: [0, 0]},
     *     {interval: [-70, -26], close: [1, 1]},
     *     {interval: [-26, 18], close: [0, 1]},
     *     {interval: [18, 62], close: [0, 1]},
     *     {interval: [62, 150], close: [0, 1]},
     *     {interval: [150, Infinity], close: [0, 0]}
     * ]);
     * @param list, where `close` mean open or close
     *        of the interval, and Infinity can be used.
     * @return The origin list, which has been reformed.
     */

    function reformIntervals(list) {
      list.sort(function (a, b) {
        return littleThan(a, b, 0) ? -1 : 1;
      });
      var curr = -Infinity;
      var currClose = 1;

      for (var i = 0; i < list.length;) {
        var interval = list[i].interval;
        var close_1 = list[i].close;

        for (var lg = 0; lg < 2; lg++) {
          if (interval[lg] <= curr) {
            interval[lg] = curr;
            close_1[lg] = !lg ? 1 - currClose : 1;
          }

          curr = interval[lg];
          currClose = close_1[lg];
        }

        if (interval[0] === interval[1] && close_1[0] * close_1[1] !== 1) {
          list.splice(i, 1);
        } else {
          i++;
        }
      }

      return list;

      function littleThan(a, b, lg) {
        return a.interval[lg] < b.interval[lg] || a.interval[lg] === b.interval[lg] && (a.close[lg] - b.close[lg] === (!lg ? 1 : -1) || !lg && littleThan(a, b, 1));
      }
    }
    /**
     * [Numeric is defined as]:
     *     `parseFloat(val) == val`
     * For example:
     * numeric:
     *     typeof number except NaN, '-123', '123', '2e3', '-2e3', '011', 'Infinity', Infinity,
     *     and they rounded by white-spaces or line-terminal like ' -123 \n ' (see es spec)
     * not-numeric:
     *     null, undefined, [], {}, true, false, 'NaN', NaN, '123ab',
     *     empty string, string with only white-spaces or line-terminal (see es spec),
     *     0x12, '0x12', '-0x12', 012, '012', '-012',
     *     non-string, ...
     *
     * @test See full test cases in `test/ut/spec/util/number.js`.
     * @return Must be a typeof number. If not numeric, return NaN.
     */

    function numericToNumber(val) {
      var valFloat = parseFloat(val);
      return valFloat == val // eslint-disable-line eqeqeq
      && (valFloat !== 0 || !isString(val) || val.indexOf('x') <= 0) // For case ' 0x0 '.
      ? valFloat : NaN;
    }
    /**
     * Definition of "numeric": see `numericToNumber`.
     */

    function isNumeric(val) {
      return !isNaN(numericToNumber(val));
    }
    /**
     * Use random base to prevent users hard code depending on
     * this auto generated marker id.
     * @return An positive integer.
     */

    function getRandomIdBase() {
      return Math.round(Math.random() * 9);
    }
    /**
     * Get the greatest common divisor.
     *
     * @param {number} a one number
     * @param {number} b the other number
     */

    function getGreatestCommonDividor(a, b) {
      if (b === 0) {
        return a;
      }

      return getGreatestCommonDividor(b, a % b);
    }
    /**
     * Get the least common multiple.
     *
     * @param {number} a one number
     * @param {number} b the other number
     */

    function getLeastCommonMultiple(a, b) {
      if (a == null) {
        return b;
      }

      if (b == null) {
        return a;
      }

      return a * b / getGreatestCommonDividor(a, b);
    }

    var ECHARTS_PREFIX = '[ECharts] ';
    var storedLogs = {};
    var hasConsole = typeof console !== 'undefined' // eslint-disable-next-line
    && console.warn && console.log;

    function outputLog(type, str, onlyOnce) {
      if (hasConsole) {
        if (onlyOnce) {
          if (storedLogs[str]) {
            return;
          }

          storedLogs[str] = true;
        } // eslint-disable-next-line


        console[type](ECHARTS_PREFIX + str);
      }
    }

    function log(str, onlyOnce) {
      outputLog('log', str, onlyOnce);
    }
    function warn(str, onlyOnce) {
      outputLog('warn', str, onlyOnce);
    }
    function error(str, onlyOnce) {
      outputLog('error', str, onlyOnce);
    }
    function deprecateLog(str) {
      if ("development" !== 'production') {
        // Not display duplicate message.
        outputLog('warn', 'DEPRECATED: ' + str, true);
      }
    }
    function deprecateReplaceLog(oldOpt, newOpt, scope) {
      if ("development" !== 'production') {
        deprecateLog((scope ? "[" + scope + "]" : '') + (oldOpt + " is deprecated, use " + newOpt + " instead."));
      }
    }
    /**
     * If in __DEV__ environment, get console printable message for users hint.
     * Parameters are separated by ' '.
     * @usage
     * makePrintable('This is an error on', someVar, someObj);
     *
     * @param hintInfo anything about the current execution context to hint users.
     * @throws Error
     */

    function makePrintable() {
      var hintInfo = [];

      for (var _i = 0; _i < arguments.length; _i++) {
        hintInfo[_i] = arguments[_i];
      }

      var msg = '';

      if ("development" !== 'production') {
        // Fuzzy stringify for print.
        // This code only exist in dev environment.
        var makePrintableStringIfPossible_1 = function (val) {
          return val === void 0 ? 'undefined' : val === Infinity ? 'Infinity' : val === -Infinity ? '-Infinity' : eqNaN(val) ? 'NaN' : val instanceof Date ? 'Date(' + val.toISOString() + ')' : isFunction(val) ? 'function () { ... }' : isRegExp(val) ? val + '' : null;
        };

        msg = map(hintInfo, function (arg) {
          if (isString(arg)) {
            // Print without quotation mark for some statement.
            return arg;
          } else {
            var printableStr = makePrintableStringIfPossible_1(arg);

            if (printableStr != null) {
              return printableStr;
            } else if (typeof JSON !== 'undefined' && JSON.stringify) {
              try {
                return JSON.stringify(arg, function (n, val) {
                  var printableStr = makePrintableStringIfPossible_1(val);
                  return printableStr == null ? val : printableStr;
                }); // In most cases the info object is small, so do not line break.
              } catch (err) {
                return '?';
              }
            } else {
              return '?';
            }
          }
        }).join(' ');
      }

      return msg;
    }
    /**
     * @throws Error
     */

    function throwError(msg) {
      throw new Error(msg);
    }

    function interpolateNumber$1(p0, p1, percent) {
      return (p1 - p0) * percent + p0;
    }
    /**
     * Make the name displayable. But we should
     * make sure it is not duplicated with user
     * specified name, so use '\0';
     */


    var DUMMY_COMPONENT_NAME_PREFIX = 'series\0';
    var INTERNAL_COMPONENT_ID_PREFIX = '\0_ec_\0';
    /**
     * If value is not array, then translate it to array.
     * @param  {*} value
     * @return {Array} [value] or value
     */

    function normalizeToArray(value) {
      return value instanceof Array ? value : value == null ? [] : [value];
    }
    /**
     * Sync default option between normal and emphasis like `position` and `show`
     * In case some one will write code like
     *     label: {
     *          show: false,
     *          position: 'outside',
     *          fontSize: 18
     *     },
     *     emphasis: {
     *          label: { show: true }
     *     }
     */

    function defaultEmphasis(opt, key, subOpts) {
      // Caution: performance sensitive.
      if (opt) {
        opt[key] = opt[key] || {};
        opt.emphasis = opt.emphasis || {};
        opt.emphasis[key] = opt.emphasis[key] || {}; // Default emphasis option from normal

        for (var i = 0, len = subOpts.length; i < len; i++) {
          var subOptName = subOpts[i];

          if (!opt.emphasis[key].hasOwnProperty(subOptName) && opt[key].hasOwnProperty(subOptName)) {
            opt.emphasis[key][subOptName] = opt[key][subOptName];
          }
        }
      }
    }
    var TEXT_STYLE_OPTIONS = ['fontStyle', 'fontWeight', 'fontSize', 'fontFamily', 'rich', 'tag', 'color', 'textBorderColor', 'textBorderWidth', 'width', 'height', 'lineHeight', 'align', 'verticalAlign', 'baseline', 'shadowColor', 'shadowBlur', 'shadowOffsetX', 'shadowOffsetY', 'textShadowColor', 'textShadowBlur', 'textShadowOffsetX', 'textShadowOffsetY', 'backgroundColor', 'borderColor', 'borderWidth', 'borderRadius', 'padding']; // modelUtil.LABEL_OPTIONS = modelUtil.TEXT_STYLE_OPTIONS.concat([
    //     'position', 'offset', 'rotate', 'origin', 'show', 'distance', 'formatter',
    //     'fontStyle', 'fontWeight', 'fontSize', 'fontFamily',
    //     // FIXME: deprecated, check and remove it.
    //     'textStyle'
    // ]);

    /**
     * The method does not ensure performance.
     * data could be [12, 2323, {value: 223}, [1221, 23], {value: [2, 23]}]
     * This helper method retrieves value from data.
     */

    function getDataItemValue(dataItem) {
      return isObject(dataItem) && !isArray(dataItem) && !(dataItem instanceof Date) ? dataItem.value : dataItem;
    }
    /**
     * data could be [12, 2323, {value: 223}, [1221, 23], {value: [2, 23]}]
     * This helper method determine if dataItem has extra option besides value
     */

    function isDataItemOption(dataItem) {
      return isObject(dataItem) && !(dataItem instanceof Array); // // markLine data can be array
      // && !(dataItem[0] && isObject(dataItem[0]) && !(dataItem[0] instanceof Array));
    }
    /**
     * Mapping to existings for merge.
     *
     * Mode "normalMege":
     *     The mapping result (merge result) will keep the order of the existing
     *     component, rather than the order of new option. Because we should ensure
     *     some specified index reference (like xAxisIndex) keep work.
     *     And in most cases, "merge option" is used to update partial option but not
     *     be expected to change the order.
     *
     * Mode "replaceMege":
     *     (1) Only the id mapped components will be merged.
     *     (2) Other existing components (except internal components) will be removed.
     *     (3) Other new options will be used to create new component.
     *     (4) The index of the existing components will not be modified.
     *     That means their might be "hole" after the removal.
     *     The new components are created first at those available index.
     *
     * Mode "replaceAll":
     *     This mode try to support that reproduce an echarts instance from another
     *     echarts instance (via `getOption`) in some simple cases.
     *     In this scenario, the `result` index are exactly the consistent with the `newCmptOptions`,
     *     which ensures the component index referring (like `xAxisIndex: ?`) corrent. That is,
     *     the "hole" in `newCmptOptions` will also be kept.
     *     On the contrary, other modes try best to eliminate holes.
     *     PENDING: This is an experimental mode yet.
     *
     * @return See the comment of <MappingResult>.
     */

    function mappingToExists(existings, newCmptOptions, mode) {
      var isNormalMergeMode = mode === 'normalMerge';
      var isReplaceMergeMode = mode === 'replaceMerge';
      var isReplaceAllMode = mode === 'replaceAll';
      existings = existings || [];
      newCmptOptions = (newCmptOptions || []).slice();
      var existingIdIdxMap = createHashMap(); // Validate id and name on user input option.

      each(newCmptOptions, function (cmptOption, index) {
        if (!isObject(cmptOption)) {
          newCmptOptions[index] = null;
          return;
        }

        if ("development" !== 'production') {
          // There is some legacy case that name is set as `false`.
          // But should work normally rather than throw error.
          if (cmptOption.id != null && !isValidIdOrName(cmptOption.id)) {
            warnInvalidateIdOrName(cmptOption.id);
          }

          if (cmptOption.name != null && !isValidIdOrName(cmptOption.name)) {
            warnInvalidateIdOrName(cmptOption.name);
          }
        }
      });
      var result = prepareResult(existings, existingIdIdxMap, mode);

      if (isNormalMergeMode || isReplaceMergeMode) {
        mappingById(result, existings, existingIdIdxMap, newCmptOptions);
      }

      if (isNormalMergeMode) {
        mappingByName(result, newCmptOptions);
      }

      if (isNormalMergeMode || isReplaceMergeMode) {
        mappingByIndex(result, newCmptOptions, isReplaceMergeMode);
      } else if (isReplaceAllMode) {
        mappingInReplaceAllMode(result, newCmptOptions);
      }

      makeIdAndName(result); // The array `result` MUST NOT contain elided items, otherwise the
      // forEach will omit those items and result in incorrect result.

      return result;
    }

    function prepareResult(existings, existingIdIdxMap, mode) {
      var result = [];

      if (mode === 'replaceAll') {
        return result;
      } // Do not use native `map` to in case that the array `existings`
      // contains elided items, which will be omitted.


      for (var index = 0; index < existings.length; index++) {
        var existing = existings[index]; // Because of replaceMerge, `existing` may be null/undefined.

        if (existing && existing.id != null) {
          existingIdIdxMap.set(existing.id, index);
        } // For non-internal-componnets:
        //     Mode "normalMerge": all existings kept.
        //     Mode "replaceMerge": all existing removed unless mapped by id.
        // For internal-components:
        //     go with "replaceMerge" approach in both mode.


        result.push({
          existing: mode === 'replaceMerge' || isComponentIdInternal(existing) ? null : existing,
          newOption: null,
          keyInfo: null,
          brandNew: null
        });
      }

      return result;
    }

    function mappingById(result, existings, existingIdIdxMap, newCmptOptions) {
      // Mapping by id if specified.
      each(newCmptOptions, function (cmptOption, index) {
        if (!cmptOption || cmptOption.id == null) {
          return;
        }

        var optionId = makeComparableKey(cmptOption.id);
        var existingIdx = existingIdIdxMap.get(optionId);

        if (existingIdx != null) {
          var resultItem = result[existingIdx];
          assert(!resultItem.newOption, 'Duplicated option on id "' + optionId + '".');
          resultItem.newOption = cmptOption; // In both mode, if id matched, new option will be merged to
          // the existings rather than creating new component model.

          resultItem.existing = existings[existingIdx];
          newCmptOptions[index] = null;
        }
      });
    }

    function mappingByName(result, newCmptOptions) {
      // Mapping by name if specified.
      each(newCmptOptions, function (cmptOption, index) {
        if (!cmptOption || cmptOption.name == null) {
          return;
        }

        for (var i = 0; i < result.length; i++) {
          var existing = result[i].existing;

          if (!result[i].newOption // Consider name: two map to one.
          // Can not match when both ids existing but different.
          && existing && (existing.id == null || cmptOption.id == null) && !isComponentIdInternal(cmptOption) && !isComponentIdInternal(existing) && keyExistAndEqual('name', existing, cmptOption)) {
            result[i].newOption = cmptOption;
            newCmptOptions[index] = null;
            return;
          }
        }
      });
    }

    function mappingByIndex(result, newCmptOptions, brandNew) {
      each(newCmptOptions, function (cmptOption) {
        if (!cmptOption) {
          return;
        } // Find the first place that not mapped by id and not internal component (consider the "hole").


        var resultItem;
        var nextIdx = 0;

        while ( // Be `!resultItem` only when `nextIdx >= result.length`.
        (resultItem = result[nextIdx]) && ( // (1) Existing models that already have id should be able to mapped to. Because
        // after mapping performed, model will always be assigned with an id if user not given.
        // After that all models have id.
        // (2) If new option has id, it can only set to a hole or append to the last. It should
        // not be merged to the existings with different id. Because id should not be overwritten.
        // (3) Name can be overwritten, because axis use name as 'show label text'.
        resultItem.newOption || isComponentIdInternal(resultItem.existing) || // In mode "replaceMerge", here no not-mapped-non-internal-existing.
        resultItem.existing && cmptOption.id != null && !keyExistAndEqual('id', cmptOption, resultItem.existing))) {
          nextIdx++;
        }

        if (resultItem) {
          resultItem.newOption = cmptOption;
          resultItem.brandNew = brandNew;
        } else {
          result.push({
            newOption: cmptOption,
            brandNew: brandNew,
            existing: null,
            keyInfo: null
          });
        }

        nextIdx++;
      });
    }

    function mappingInReplaceAllMode(result, newCmptOptions) {
      each(newCmptOptions, function (cmptOption) {
        // The feature "reproduce" requires "hole" will also reproduced
        // in case that component index referring are broken.
        result.push({
          newOption: cmptOption,
          brandNew: true,
          existing: null,
          keyInfo: null
        });
      });
    }
    /**
     * Make id and name for mapping result (result of mappingToExists)
     * into `keyInfo` field.
     */


    function makeIdAndName(mapResult) {
      // We use this id to hash component models and view instances
      // in echarts. id can be specified by user, or auto generated.
      // The id generation rule ensures new view instance are able
      // to mapped to old instance when setOption are called in
      // no-merge mode. So we generate model id by name and plus
      // type in view id.
      // name can be duplicated among components, which is convenient
      // to specify multi components (like series) by one name.
      // Ensure that each id is distinct.
      var idMap = createHashMap();
      each(mapResult, function (item) {
        var existing = item.existing;
        existing && idMap.set(existing.id, item);
      });
      each(mapResult, function (item) {
        var opt = item.newOption; // Force ensure id not duplicated.

        assert(!opt || opt.id == null || !idMap.get(opt.id) || idMap.get(opt.id) === item, 'id duplicates: ' + (opt && opt.id));
        opt && opt.id != null && idMap.set(opt.id, item);
        !item.keyInfo && (item.keyInfo = {});
      }); // Make name and id.

      each(mapResult, function (item, index) {
        var existing = item.existing;
        var opt = item.newOption;
        var keyInfo = item.keyInfo;

        if (!isObject(opt)) {
          return;
        } // Name can be overwritten. Consider case: axis.name = '20km'.
        // But id generated by name will not be changed, which affect
        // only in that case: setOption with 'not merge mode' and view
        // instance will be recreated, which can be accepted.


        keyInfo.name = opt.name != null ? makeComparableKey(opt.name) : existing ? existing.name // Avoid that different series has the same name,
        // because name may be used like in color pallet.
        : DUMMY_COMPONENT_NAME_PREFIX + index;

        if (existing) {
          keyInfo.id = makeComparableKey(existing.id);
        } else if (opt.id != null) {
          keyInfo.id = makeComparableKey(opt.id);
        } else {
          // Consider this situatoin:
          //  optionA: [{name: 'a'}, {name: 'a'}, {..}]
          //  optionB [{..}, {name: 'a'}, {name: 'a'}]
          // Series with the same name between optionA and optionB
          // should be mapped.
          var idNum = 0;

          do {
            keyInfo.id = '\0' + keyInfo.name + '\0' + idNum++;
          } while (idMap.get(keyInfo.id));
        }

        idMap.set(keyInfo.id, item);
      });
    }

    function keyExistAndEqual(attr, obj1, obj2) {
      var key1 = convertOptionIdName(obj1[attr], null);
      var key2 = convertOptionIdName(obj2[attr], null); // See `MappingExistingItem`. `id` and `name` trade string equals to number.

      return key1 != null && key2 != null && key1 === key2;
    }
    /**
     * @return return null if not exist.
     */


    function makeComparableKey(val) {
      if ("development" !== 'production') {
        if (val == null) {
          throw new Error();
        }
      }

      return convertOptionIdName(val, '');
    }

    function convertOptionIdName(idOrName, defaultValue) {
      if (idOrName == null) {
        return defaultValue;
      }

      return isString(idOrName) ? idOrName : isNumber(idOrName) || isStringSafe(idOrName) ? idOrName + '' : defaultValue;
    }

    function warnInvalidateIdOrName(idOrName) {
      if ("development" !== 'production') {
        warn('`' + idOrName + '` is invalid id or name. Must be a string or number.');
      }
    }

    function isValidIdOrName(idOrName) {
      return isStringSafe(idOrName) || isNumeric(idOrName);
    }

    function isNameSpecified(componentModel) {
      var name = componentModel.name; // Is specified when `indexOf` get -1 or > 0.

      return !!(name && name.indexOf(DUMMY_COMPONENT_NAME_PREFIX));
    }
    /**
     * @public
     * @param {Object} cmptOption
     * @return {boolean}
     */

    function isComponentIdInternal(cmptOption) {
      return cmptOption && cmptOption.id != null && makeComparableKey(cmptOption.id).indexOf(INTERNAL_COMPONENT_ID_PREFIX) === 0;
    }
    function makeInternalComponentId(idSuffix) {
      return INTERNAL_COMPONENT_ID_PREFIX + idSuffix;
    }
    function setComponentTypeToKeyInfo(mappingResult, mainType, componentModelCtor) {
      // Set mainType and complete subType.
      each(mappingResult, function (item) {
        var newOption = item.newOption;

        if (isObject(newOption)) {
          item.keyInfo.mainType = mainType;
          item.keyInfo.subType = determineSubType(mainType, newOption, item.existing, componentModelCtor);
        }
      });
    }

    function determineSubType(mainType, newCmptOption, existComponent, componentModelCtor) {
      var subType = newCmptOption.type ? newCmptOption.type : existComponent ? existComponent.subType // Use determineSubType only when there is no existComponent.
      : componentModelCtor.determineSubType(mainType, newCmptOption); // tooltip, markline, markpoint may always has no subType

      return subType;
    }
    /**
     * @param payload Contains dataIndex (means rawIndex) / dataIndexInside / name
     *                         each of which can be Array or primary type.
     * @return dataIndex If not found, return undefined/null.
     */

    function queryDataIndex(data, payload) {
      if (payload.dataIndexInside != null) {
        return payload.dataIndexInside;
      } else if (payload.dataIndex != null) {
        return isArray(payload.dataIndex) ? map(payload.dataIndex, function (value) {
          return data.indexOfRawIndex(value);
        }) : data.indexOfRawIndex(payload.dataIndex);
      } else if (payload.name != null) {
        return isArray(payload.name) ? map(payload.name, function (value) {
          return data.indexOfName(value);
        }) : data.indexOfName(payload.name);
      }
    }
    /**
     * Enable property storage to any host object.
     * Notice: Serialization is not supported.
     *
     * For example:
     * let inner = zrUitl.makeInner();
     *
     * function some1(hostObj) {
     *      inner(hostObj).someProperty = 1212;
     *      ...
     * }
     * function some2() {
     *      let fields = inner(this);
     *      fields.someProperty1 = 1212;
     *      fields.someProperty2 = 'xx';
     *      ...
     * }
     *
     * @return {Function}
     */

    function makeInner() {
      var key = '__ec_inner_' + innerUniqueIndex++;
      return function (hostObj) {
        return hostObj[key] || (hostObj[key] = {});
      };
    }
    var innerUniqueIndex = getRandomIdBase();
    /**
     * The same behavior as `component.getReferringComponents`.
     */

    function parseFinder(ecModel, finderInput, opt) {
      var _a = preParseFinder(finderInput, opt),
          mainTypeSpecified = _a.mainTypeSpecified,
          queryOptionMap = _a.queryOptionMap,
          others = _a.others;

      var result = others;
      var defaultMainType = opt ? opt.defaultMainType : null;

      if (!mainTypeSpecified && defaultMainType) {
        queryOptionMap.set(defaultMainType, {});
      }

      queryOptionMap.each(function (queryOption, mainType) {
        var queryResult = queryReferringComponents(ecModel, mainType, queryOption, {
          useDefault: defaultMainType === mainType,
          enableAll: opt && opt.enableAll != null ? opt.enableAll : true,
          enableNone: opt && opt.enableNone != null ? opt.enableNone : true
        });
        result[mainType + 'Models'] = queryResult.models;
        result[mainType + 'Model'] = queryResult.models[0];
      });
      return result;
    }
    function preParseFinder(finderInput, opt) {
      var finder;

      if (isString(finderInput)) {
        var obj = {};
        obj[finderInput + 'Index'] = 0;
        finder = obj;
      } else {
        finder = finderInput;
      }

      var queryOptionMap = createHashMap();
      var others = {};
      var mainTypeSpecified = false;
      each(finder, function (value, key) {
        // Exclude 'dataIndex' and other illgal keys.
        if (key === 'dataIndex' || key === 'dataIndexInside') {
          others[key] = value;
          return;
        }

        var parsedKey = key.match(/^(\w+)(Index|Id|Name)$/) || [];
        var mainType = parsedKey[1];
        var queryType = (parsedKey[2] || '').toLowerCase();

        if (!mainType || !queryType || opt && opt.includeMainTypes && indexOf(opt.includeMainTypes, mainType) < 0) {
          return;
        }

        mainTypeSpecified = mainTypeSpecified || !!mainType;
        var queryOption = queryOptionMap.get(mainType) || queryOptionMap.set(mainType, {});
        queryOption[queryType] = value;
      });
      return {
        mainTypeSpecified: mainTypeSpecified,
        queryOptionMap: queryOptionMap,
        others: others
      };
    }
    var SINGLE_REFERRING = {
      useDefault: true,
      enableAll: false,
      enableNone: false
    };
    var MULTIPLE_REFERRING = {
      useDefault: false,
      enableAll: true,
      enableNone: true
    };
    function queryReferringComponents(ecModel, mainType, userOption, opt) {
      opt = opt || SINGLE_REFERRING;
      var indexOption = userOption.index;
      var idOption = userOption.id;
      var nameOption = userOption.name;
      var result = {
        models: null,
        specified: indexOption != null || idOption != null || nameOption != null
      };

      if (!result.specified) {
        // Use the first as default if `useDefault`.
        var firstCmpt = void 0;
        result.models = opt.useDefault && (firstCmpt = ecModel.getComponent(mainType)) ? [firstCmpt] : [];
        return result;
      }

      if (indexOption === 'none' || indexOption === false) {
        assert(opt.enableNone, '`"none"` or `false` is not a valid value on index option.');
        result.models = [];
        return result;
      } // `queryComponents` will return all components if
      // both all of index/id/name are null/undefined.


      if (indexOption === 'all') {
        assert(opt.enableAll, '`"all"` is not a valid value on index option.');
        indexOption = idOption = nameOption = null;
      }

      result.models = ecModel.queryComponents({
        mainType: mainType,
        index: indexOption,
        id: idOption,
        name: nameOption
      });
      return result;
    }
    function setAttribute(dom, key, value) {
      dom.setAttribute ? dom.setAttribute(key, value) : dom[key] = value;
    }
    function getAttribute(dom, key) {
      return dom.getAttribute ? dom.getAttribute(key) : dom[key];
    }
    function getTooltipRenderMode(renderModeOption) {
      if (renderModeOption === 'auto') {
        // Using html when `document` exists, use richText otherwise
        return env.domSupported ? 'html' : 'richText';
      } else {
        return renderModeOption || 'html';
      }
    }
    /**
     * Interpolate raw values of a series with percent
     *
     * @param data         data
     * @param labelModel   label model of the text element
     * @param sourceValue  start value. May be null/undefined when init.
     * @param targetValue  end value
     * @param percent      0~1 percentage; 0 uses start value while 1 uses end value
     * @return             interpolated values
     *                     If `sourceValue` and `targetValue` are `number`, return `number`.
     *                     If `sourceValue` and `targetValue` are `string`, return `string`.
     *                     If `sourceValue` and `targetValue` are `(string | number)[]`, return `(string | number)[]`.
     *                     Other cases do not supported.
     */

    function interpolateRawValues(data, precision, sourceValue, targetValue, percent) {
      var isAutoPrecision = precision == null || precision === 'auto';

      if (targetValue == null) {
        return targetValue;
      }

      if (isNumber(targetValue)) {
        var value = interpolateNumber$1(sourceValue || 0, targetValue, percent);
        return round(value, isAutoPrecision ? Math.max(getPrecision(sourceValue || 0), getPrecision(targetValue)) : precision);
      } else if (isString(targetValue)) {
        return percent < 1 ? sourceValue : targetValue;
      } else {
        var interpolated = [];
        var leftArr = sourceValue;
        var rightArr = targetValue;
        var length_1 = Math.max(leftArr ? leftArr.length : 0, rightArr.length);

        for (var i = 0; i < length_1; ++i) {
          var info = data.getDimensionInfo(i); // Don't interpolate ordinal dims

          if (info && info.type === 'ordinal') {
            // In init, there is no `sourceValue`, but should better not to get undefined result.
            interpolated[i] = (percent < 1 && leftArr ? leftArr : rightArr)[i];
          } else {
            var leftVal = leftArr && leftArr[i] ? leftArr[i] : 0;
            var rightVal = rightArr[i];
            var value = interpolateNumber$1(leftVal, rightVal, percent);
            interpolated[i] = round(value, isAutoPrecision ? Math.max(getPrecision(leftVal), getPrecision(rightVal)) : precision);
          }
        }

        return interpolated;
      }
    }

    var TYPE_DELIMITER = '.';
    var IS_CONTAINER = '___EC__COMPONENT__CONTAINER___';
    var IS_EXTENDED_CLASS = '___EC__EXTENDED_CLASS___';
    /**
     * Notice, parseClassType('') should returns {main: '', sub: ''}
     * @public
     */

    function parseClassType(componentType) {
      var ret = {
        main: '',
        sub: ''
      };

      if (componentType) {
        var typeArr = componentType.split(TYPE_DELIMITER);
        ret.main = typeArr[0] || '';
        ret.sub = typeArr[1] || '';
      }

      return ret;
    }
    /**
     * @public
     */

    function checkClassType(componentType) {
      assert(/^[a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)?$/.test(componentType), 'componentType "' + componentType + '" illegal');
    }

    function isExtendedClass(clz) {
      return !!(clz && clz[IS_EXTENDED_CLASS]);
    }
    /**
     * Implements `ExtendableConstructor` for `rootClz`.
     *
     * @usage
     * ```ts
     * class Xxx {}
     * type XxxConstructor = typeof Xxx & ExtendableConstructor
     * enableClassExtend(Xxx as XxxConstructor);
     * ```
     */

    function enableClassExtend(rootClz, mandatoryMethods) {
      rootClz.$constructor = rootClz; // FIXME: not necessary?

      rootClz.extend = function (proto) {
        if ("development" !== 'production') {
          each(mandatoryMethods, function (method) {
            if (!proto[method]) {
              console.warn('Method `' + method + '` should be implemented' + (proto.type ? ' in ' + proto.type : '') + '.');
            }
          });
        }

        var superClass = this;
        var ExtendedClass;

        if (isESClass(superClass)) {
          ExtendedClass =
          /** @class */
          function (_super) {
            __extends(class_1, _super);

            function class_1() {
              return _super.apply(this, arguments) || this;
            }

            return class_1;
          }(superClass);
        } else {
          // For backward compat, we both support ts class inheritance and this
          // "extend" approach.
          // The constructor should keep the same behavior as ts class inheritance:
          // If this constructor/$constructor is not declared, auto invoke the super
          // constructor.
          // If this constructor/$constructor is declared, it is responsible for
          // calling the super constructor.
          ExtendedClass = function () {
            (proto.$constructor || superClass).apply(this, arguments);
          };

          inherits(ExtendedClass, this);
        }

        extend(ExtendedClass.prototype, proto);
        ExtendedClass[IS_EXTENDED_CLASS] = true;
        ExtendedClass.extend = this.extend;
        ExtendedClass.superCall = superCall;
        ExtendedClass.superApply = superApply;
        ExtendedClass.superClass = superClass;
        return ExtendedClass;
      };
    }

    function isESClass(fn) {
      return isFunction(fn) && /^class\s/.test(Function.prototype.toString.call(fn));
    }
    /**
     * A work around to both support ts extend and this extend mechanism.
     * on sub-class.
     * @usage
     * ```ts
     * class Component { ... }
     * classUtil.enableClassExtend(Component);
     * classUtil.enableClassManagement(Component, {registerWhenExtend: true});
     *
     * class Series extends Component { ... }
     * // Without calling `markExtend`, `registerWhenExtend` will not work.
     * Component.markExtend(Series);
     * ```
     */


    function mountExtend(SubClz, SupperClz) {
      SubClz.extend = SupperClz.extend;
    } // A random offset.

    var classBase = Math.round(Math.random() * 10);
    /**
     * Implements `CheckableConstructor` for `target`.
     * Can not use instanceof, consider different scope by
     * cross domain or es module import in ec extensions.
     * Mount a method "isInstance()" to Clz.
     *
     * @usage
     * ```ts
     * class Xxx {}
     * type XxxConstructor = typeof Xxx & CheckableConstructor;
     * enableClassCheck(Xxx as XxxConstructor)
     * ```
     */

    function enableClassCheck(target) {
      var classAttr = ['__\0is_clz', classBase++].join('_');
      target.prototype[classAttr] = true;

      if ("development" !== 'production') {
        assert(!target.isInstance, 'The method "is" can not be defined.');
      }

      target.isInstance = function (obj) {
        return !!(obj && obj[classAttr]);
      };
    } // superCall should have class info, which can not be fetched from 'this'.
    // Consider this case:
    // class A has method f,
    // class B inherits class A, overrides method f, f call superApply('f'),
    // class C inherits class B, does not override method f,
    // then when method of class C is called, dead loop occurred.

    function superCall(context, methodName) {
      var args = [];

      for (var _i = 2; _i < arguments.length; _i++) {
        args[_i - 2] = arguments[_i];
      }

      return this.superClass.prototype[methodName].apply(context, args);
    }

    function superApply(context, methodName, args) {
      return this.superClass.prototype[methodName].apply(context, args);
    }
    /**
     * Implements `ClassManager` for `target`
     *
     * @usage
     * ```ts
     * class Xxx {}
     * type XxxConstructor = typeof Xxx & ClassManager
     * enableClassManagement(Xxx as XxxConstructor);
     * ```
     */


    function enableClassManagement(target) {
      /**
       * Component model classes
       * key: componentType,
       * value:
       *     componentClass, when componentType is 'a'
       *     or Object.<subKey, componentClass>, when componentType is 'a.b'
       */
      var storage = {};

      target.registerClass = function (clz) {
        // `type` should not be a "instance member".
        // If using TS class, should better declared as `static type = 'series.pie'`.
        // otherwise users have to mount `type` on prototype manually.
        // For backward compat and enable instance visit type via `this.type`,
        // we still support fetch `type` from prototype.
        var componentFullType = clz.type || clz.prototype.type;

        if (componentFullType) {
          checkClassType(componentFullType); // If only static type declared, we assign it to prototype mandatorily.

          clz.prototype.type = componentFullType;
          var componentTypeInfo = parseClassType(componentFullType);

          if (!componentTypeInfo.sub) {
            if ("development" !== 'production') {
              if (storage[componentTypeInfo.main]) {
                console.warn(componentTypeInfo.main + ' exists.');
              }
            }

            storage[componentTypeInfo.main] = clz;
          } else if (componentTypeInfo.sub !== IS_CONTAINER) {
            var container = makeContainer(componentTypeInfo);
            container[componentTypeInfo.sub] = clz;
          }
        }

        return clz;
      };

      target.getClass = function (mainType, subType, throwWhenNotFound) {
        var clz = storage[mainType];

        if (clz && clz[IS_CONTAINER]) {
          clz = subType ? clz[subType] : null;
        }

        if (throwWhenNotFound && !clz) {
          throw new Error(!subType ? mainType + '.' + 'type should be specified.' : 'Component ' + mainType + '.' + (subType || '') + ' is used but not imported.');
        }

        return clz;
      };

      target.getClassesByMainType = function (componentType) {
        var componentTypeInfo = parseClassType(componentType);
        var result = [];
        var obj = storage[componentTypeInfo.main];

        if (obj && obj[IS_CONTAINER]) {
          each(obj, function (o, type) {
            type !== IS_CONTAINER && result.push(o);
          });
        } else {
          result.push(obj);
        }

        return result;
      };

      target.hasClass = function (componentType) {
        // Just consider componentType.main.
        var componentTypeInfo = parseClassType(componentType);
        return !!storage[componentTypeInfo.main];
      };
      /**
       * @return Like ['aa', 'bb'], but can not be ['aa.xx']
       */


      target.getAllClassMainTypes = function () {
        var types = [];
        each(storage, function (obj, type) {
          types.push(type);
        });
        return types;
      };
      /**
       * If a main type is container and has sub types
       */


      target.hasSubTypes = function (componentType) {
        var componentTypeInfo = parseClassType(componentType);
        var obj = storage[componentTypeInfo.main];
        return obj && obj[IS_CONTAINER];
      };

      function makeContainer(componentTypeInfo) {
        var container = storage[componentTypeInfo.main];

        if (!container || !container[IS_CONTAINER]) {
          container = storage[componentTypeInfo.main] = {};
          container[IS_CONTAINER] = true;
        }

        return container;
      }
    } // /**
    //  * @param {string|Array.<string>} properties
    //  */
    // export function setReadOnly(obj, properties) {
    // FIXME It seems broken in IE8 simulation of IE11
    // if (!zrUtil.isArray(properties)) {
    //     properties = properties != null ? [properties] : [];
    // }
    // zrUtil.each(properties, function (prop) {
    //     let value = obj[prop];
    //     Object.defineProperty
    //         && Object.defineProperty(obj, prop, {
    //             value: value, writable: false
    //         });
    //     zrUtil.isArray(obj[prop])
    //         && Object.freeze
    //         && Object.freeze(obj[prop]);
    // });
    // }

    function makeStyleMapper(properties, ignoreParent) {
      // Normalize
      for (var i = 0; i < properties.length; i++) {
        if (!properties[i][1]) {
          properties[i][1] = properties[i][0];
        }
      }

      ignoreParent = ignoreParent || false;
      return function (model, excludes, includes) {
        var style = {};

        for (var i = 0; i < properties.length; i++) {
          var propName = properties[i][1];

          if (excludes && indexOf(excludes, propName) >= 0 || includes && indexOf(includes, propName) < 0) {
            continue;
          }

          var val = model.getShallow(propName, ignoreParent);

          if (val != null) {
            style[properties[i][0]] = val;
          }
        } // TODO Text or image?


        return style;
      };
    }

    var AREA_STYLE_KEY_MAP = [['fill', 'color'], ['shadowBlur'], ['shadowOffsetX'], ['shadowOffsetY'], ['opacity'], ['shadowColor'] // Option decal is in `DecalObject` but style.decal is in `PatternObject`.
    // So do not transfer decal directly.
    ];
    var getAreaStyle = makeStyleMapper(AREA_STYLE_KEY_MAP);

    var AreaStyleMixin =
    /** @class */
    function () {
      function AreaStyleMixin() {}

      AreaStyleMixin.prototype.getAreaStyle = function (excludes, includes) {
        return getAreaStyle(this, excludes, includes);
      };

      return AreaStyleMixin;
    }();

    var globalImageCache = new LRU(50);
    function findExistImage(newImageOrSrc) {
        if (typeof newImageOrSrc === 'string') {
            var cachedImgObj = globalImageCache.get(newImageOrSrc);
            return cachedImgObj && cachedImgObj.image;
        }
        else {
            return newImageOrSrc;
        }
    }
    function createOrUpdateImage(newImageOrSrc, image, hostEl, onload, cbPayload) {
        if (!newImageOrSrc) {
            return image;
        }
        else if (typeof newImageOrSrc === 'string') {
            if ((image && image.__zrImageSrc === newImageOrSrc) || !hostEl) {
                return image;
            }
            var cachedImgObj = globalImageCache.get(newImageOrSrc);
            var pendingWrap = { hostEl: hostEl, cb: onload, cbPayload: cbPayload };
            if (cachedImgObj) {
                image = cachedImgObj.image;
                !isImageReady(image) && cachedImgObj.pending.push(pendingWrap);
            }
            else {
                image = platformApi.loadImage(newImageOrSrc, imageOnLoad, imageOnLoad);
                image.__zrImageSrc = newImageOrSrc;
                globalImageCache.put(newImageOrSrc, image.__cachedImgObj = {
                    image: image,
                    pending: [pendingWrap]
                });
            }
            return image;
        }
        else {
            return newImageOrSrc;
        }
    }
    function imageOnLoad() {
        var cachedImgObj = this.__cachedImgObj;
        this.onload = this.onerror = this.__cachedImgObj = null;
        for (var i = 0; i < cachedImgObj.pending.length; i++) {
            var pendingWrap = cachedImgObj.pending[i];
            var cb = pendingWrap.cb;
            cb && cb(this, pendingWrap.cbPayload);
            pendingWrap.hostEl.dirty();
        }
        cachedImgObj.pending.length = 0;
    }
    function isImageReady(image) {
        return image && image.width && image.height;
    }

    var STYLE_REG = /\{([a-zA-Z0-9_]+)\|([^}]*)\}/g;
    function truncateText(text, containerWidth, font, ellipsis, options) {
        if (!containerWidth) {
            return '';
        }
        var textLines = (text + '').split('\n');
        options = prepareTruncateOptions(containerWidth, font, ellipsis, options);
        for (var i = 0, len = textLines.length; i < len; i++) {
            textLines[i] = truncateSingleLine(textLines[i], options);
        }
        return textLines.join('\n');
    }
    function prepareTruncateOptions(containerWidth, font, ellipsis, options) {
        options = options || {};
        var preparedOpts = extend({}, options);
        preparedOpts.font = font;
        ellipsis = retrieve2(ellipsis, '...');
        preparedOpts.maxIterations = retrieve2(options.maxIterations, 2);
        var minChar = preparedOpts.minChar = retrieve2(options.minChar, 0);
        preparedOpts.cnCharWidth = getWidth('国', font);
        var ascCharWidth = preparedOpts.ascCharWidth = getWidth('a', font);
        preparedOpts.placeholder = retrieve2(options.placeholder, '');
        var contentWidth = containerWidth = Math.max(0, containerWidth - 1);
        for (var i = 0; i < minChar && contentWidth >= ascCharWidth; i++) {
            contentWidth -= ascCharWidth;
        }
        var ellipsisWidth = getWidth(ellipsis, font);
        if (ellipsisWidth > contentWidth) {
            ellipsis = '';
            ellipsisWidth = 0;
        }
        contentWidth = containerWidth - ellipsisWidth;
        preparedOpts.ellipsis = ellipsis;
        preparedOpts.ellipsisWidth = ellipsisWidth;
        preparedOpts.contentWidth = contentWidth;
        preparedOpts.containerWidth = containerWidth;
        return preparedOpts;
    }
    function truncateSingleLine(textLine, options) {
        var containerWidth = options.containerWidth;
        var font = options.font;
        var contentWidth = options.contentWidth;
        if (!containerWidth) {
            return '';
        }
        var lineWidth = getWidth(textLine, font);
        if (lineWidth <= containerWidth) {
            return textLine;
        }
        for (var j = 0;; j++) {
            if (lineWidth <= contentWidth || j >= options.maxIterations) {
                textLine += options.ellipsis;
                break;
            }
            var subLength = j === 0
                ? estimateLength(textLine, contentWidth, options.ascCharWidth, options.cnCharWidth)
                : lineWidth > 0
                    ? Math.floor(textLine.length * contentWidth / lineWidth)
                    : 0;
            textLine = textLine.substr(0, subLength);
            lineWidth = getWidth(textLine, font);
        }
        if (textLine === '') {
            textLine = options.placeholder;
        }
        return textLine;
    }
    function estimateLength(text, contentWidth, ascCharWidth, cnCharWidth) {
        var width = 0;
        var i = 0;
        for (var len = text.length; i < len && width < contentWidth; i++) {
            var charCode = text.charCodeAt(i);
            width += (0 <= charCode && charCode <= 127) ? ascCharWidth : cnCharWidth;
        }
        return i;
    }
    function parsePlainText(text, style) {
        text != null && (text += '');
        var overflow = style.overflow;
        var padding = style.padding;
        var font = style.font;
        var truncate = overflow === 'truncate';
        var calculatedLineHeight = getLineHeight(font);
        var lineHeight = retrieve2(style.lineHeight, calculatedLineHeight);
        var bgColorDrawn = !!(style.backgroundColor);
        var truncateLineOverflow = style.lineOverflow === 'truncate';
        var width = style.width;
        var lines;
        if (width != null && (overflow === 'break' || overflow === 'breakAll')) {
            lines = text ? wrapText(text, style.font, width, overflow === 'breakAll', 0).lines : [];
        }
        else {
            lines = text ? text.split('\n') : [];
        }
        var contentHeight = lines.length * lineHeight;
        var height = retrieve2(style.height, contentHeight);
        if (contentHeight > height && truncateLineOverflow) {
            var lineCount = Math.floor(height / lineHeight);
            lines = lines.slice(0, lineCount);
        }
        if (text && truncate && width != null) {
            var options = prepareTruncateOptions(width, font, style.ellipsis, {
                minChar: style.truncateMinChar,
                placeholder: style.placeholder
            });
            for (var i = 0; i < lines.length; i++) {
                lines[i] = truncateSingleLine(lines[i], options);
            }
        }
        var outerHeight = height;
        var contentWidth = 0;
        for (var i = 0; i < lines.length; i++) {
            contentWidth = Math.max(getWidth(lines[i], font), contentWidth);
        }
        if (width == null) {
            width = contentWidth;
        }
        var outerWidth = contentWidth;
        if (padding) {
            outerHeight += padding[0] + padding[2];
            outerWidth += padding[1] + padding[3];
            width += padding[1] + padding[3];
        }
        if (bgColorDrawn) {
            outerWidth = width;
        }
        return {
            lines: lines,
            height: height,
            outerWidth: outerWidth,
            outerHeight: outerHeight,
            lineHeight: lineHeight,
            calculatedLineHeight: calculatedLineHeight,
            contentWidth: contentWidth,
            contentHeight: contentHeight,
            width: width
        };
    }
    var RichTextToken = (function () {
        function RichTextToken() {
        }
        return RichTextToken;
    }());
    var RichTextLine = (function () {
        function RichTextLine(tokens) {
            this.tokens = [];
            if (tokens) {
                this.tokens = tokens;
            }
        }
        return RichTextLine;
    }());
    var RichTextContentBlock = (function () {
        function RichTextContentBlock() {
            this.width = 0;
            this.height = 0;
            this.contentWidth = 0;
            this.contentHeight = 0;
            this.outerWidth = 0;
            this.outerHeight = 0;
            this.lines = [];
        }
        return RichTextContentBlock;
    }());
    function parseRichText(text, style) {
        var contentBlock = new RichTextContentBlock();
        text != null && (text += '');
        if (!text) {
            return contentBlock;
        }
        var topWidth = style.width;
        var topHeight = style.height;
        var overflow = style.overflow;
        var wrapInfo = (overflow === 'break' || overflow === 'breakAll') && topWidth != null
            ? { width: topWidth, accumWidth: 0, breakAll: overflow === 'breakAll' }
            : null;
        var lastIndex = STYLE_REG.lastIndex = 0;
        var result;
        while ((result = STYLE_REG.exec(text)) != null) {
            var matchedIndex = result.index;
            if (matchedIndex > lastIndex) {
                pushTokens(contentBlock, text.substring(lastIndex, matchedIndex), style, wrapInfo);
            }
            pushTokens(contentBlock, result[2], style, wrapInfo, result[1]);
            lastIndex = STYLE_REG.lastIndex;
        }
        if (lastIndex < text.length) {
            pushTokens(contentBlock, text.substring(lastIndex, text.length), style, wrapInfo);
        }
        var pendingList = [];
        var calculatedHeight = 0;
        var calculatedWidth = 0;
        var stlPadding = style.padding;
        var truncate = overflow === 'truncate';
        var truncateLine = style.lineOverflow === 'truncate';
        function finishLine(line, lineWidth, lineHeight) {
            line.width = lineWidth;
            line.lineHeight = lineHeight;
            calculatedHeight += lineHeight;
            calculatedWidth = Math.max(calculatedWidth, lineWidth);
        }
        outer: for (var i = 0; i < contentBlock.lines.length; i++) {
            var line = contentBlock.lines[i];
            var lineHeight = 0;
            var lineWidth = 0;
            for (var j = 0; j < line.tokens.length; j++) {
                var token = line.tokens[j];
                var tokenStyle = token.styleName && style.rich[token.styleName] || {};
                var textPadding = token.textPadding = tokenStyle.padding;
                var paddingH = textPadding ? textPadding[1] + textPadding[3] : 0;
                var font = token.font = tokenStyle.font || style.font;
                token.contentHeight = getLineHeight(font);
                var tokenHeight = retrieve2(tokenStyle.height, token.contentHeight);
                token.innerHeight = tokenHeight;
                textPadding && (tokenHeight += textPadding[0] + textPadding[2]);
                token.height = tokenHeight;
                token.lineHeight = retrieve3(tokenStyle.lineHeight, style.lineHeight, tokenHeight);
                token.align = tokenStyle && tokenStyle.align || style.align;
                token.verticalAlign = tokenStyle && tokenStyle.verticalAlign || 'middle';
                if (truncateLine && topHeight != null && calculatedHeight + token.lineHeight > topHeight) {
                    if (j > 0) {
                        line.tokens = line.tokens.slice(0, j);
                        finishLine(line, lineWidth, lineHeight);
                        contentBlock.lines = contentBlock.lines.slice(0, i + 1);
                    }
                    else {
                        contentBlock.lines = contentBlock.lines.slice(0, i);
                    }
                    break outer;
                }
                var styleTokenWidth = tokenStyle.width;
                var tokenWidthNotSpecified = styleTokenWidth == null || styleTokenWidth === 'auto';
                if (typeof styleTokenWidth === 'string' && styleTokenWidth.charAt(styleTokenWidth.length - 1) === '%') {
                    token.percentWidth = styleTokenWidth;
                    pendingList.push(token);
                    token.contentWidth = getWidth(token.text, font);
                }
                else {
                    if (tokenWidthNotSpecified) {
                        var textBackgroundColor = tokenStyle.backgroundColor;
                        var bgImg = textBackgroundColor && textBackgroundColor.image;
                        if (bgImg) {
                            bgImg = findExistImage(bgImg);
                            if (isImageReady(bgImg)) {
                                token.width = Math.max(token.width, bgImg.width * tokenHeight / bgImg.height);
                            }
                        }
                    }
                    var remainTruncWidth = truncate && topWidth != null
                        ? topWidth - lineWidth : null;
                    if (remainTruncWidth != null && remainTruncWidth < token.width) {
                        if (!tokenWidthNotSpecified || remainTruncWidth < paddingH) {
                            token.text = '';
                            token.width = token.contentWidth = 0;
                        }
                        else {
                            token.text = truncateText(token.text, remainTruncWidth - paddingH, font, style.ellipsis, { minChar: style.truncateMinChar });
                            token.width = token.contentWidth = getWidth(token.text, font);
                        }
                    }
                    else {
                        token.contentWidth = getWidth(token.text, font);
                    }
                }
                token.width += paddingH;
                lineWidth += token.width;
                tokenStyle && (lineHeight = Math.max(lineHeight, token.lineHeight));
            }
            finishLine(line, lineWidth, lineHeight);
        }
        contentBlock.outerWidth = contentBlock.width = retrieve2(topWidth, calculatedWidth);
        contentBlock.outerHeight = contentBlock.height = retrieve2(topHeight, calculatedHeight);
        contentBlock.contentHeight = calculatedHeight;
        contentBlock.contentWidth = calculatedWidth;
        if (stlPadding) {
            contentBlock.outerWidth += stlPadding[1] + stlPadding[3];
            contentBlock.outerHeight += stlPadding[0] + stlPadding[2];
        }
        for (var i = 0; i < pendingList.length; i++) {
            var token = pendingList[i];
            var percentWidth = token.percentWidth;
            token.width = parseInt(percentWidth, 10) / 100 * contentBlock.width;
        }
        return contentBlock;
    }
    function pushTokens(block, str, style, wrapInfo, styleName) {
        var isEmptyStr = str === '';
        var tokenStyle = styleName && style.rich[styleName] || {};
        var lines = block.lines;
        var font = tokenStyle.font || style.font;
        var newLine = false;
        var strLines;
        var linesWidths;
        if (wrapInfo) {
            var tokenPadding = tokenStyle.padding;
            var tokenPaddingH = tokenPadding ? tokenPadding[1] + tokenPadding[3] : 0;
            if (tokenStyle.width != null && tokenStyle.width !== 'auto') {
                var outerWidth_1 = parsePercent(tokenStyle.width, wrapInfo.width) + tokenPaddingH;
                if (lines.length > 0) {
                    if (outerWidth_1 + wrapInfo.accumWidth > wrapInfo.width) {
                        strLines = str.split('\n');
                        newLine = true;
                    }
                }
                wrapInfo.accumWidth = outerWidth_1;
            }
            else {
                var res = wrapText(str, font, wrapInfo.width, wrapInfo.breakAll, wrapInfo.accumWidth);
                wrapInfo.accumWidth = res.accumWidth + tokenPaddingH;
                linesWidths = res.linesWidths;
                strLines = res.lines;
            }
        }
        else {
            strLines = str.split('\n');
        }
        for (var i = 0; i < strLines.length; i++) {
            var text = strLines[i];
            var token = new RichTextToken();
            token.styleName = styleName;
            token.text = text;
            token.isLineHolder = !text && !isEmptyStr;
            if (typeof tokenStyle.width === 'number') {
                token.width = tokenStyle.width;
            }
            else {
                token.width = linesWidths
                    ? linesWidths[i]
                    : getWidth(text, font);
            }
            if (!i && !newLine) {
                var tokens = (lines[lines.length - 1] || (lines[0] = new RichTextLine())).tokens;
                var tokensLen = tokens.length;
                (tokensLen === 1 && tokens[0].isLineHolder)
                    ? (tokens[0] = token)
                    : ((text || !tokensLen || isEmptyStr) && tokens.push(token));
            }
            else {
                lines.push(new RichTextLine([token]));
            }
        }
    }
    function isAlphabeticLetter(ch) {
        var code = ch.charCodeAt(0);
        return code >= 0x20 && code <= 0x24F
            || code >= 0x370 && code <= 0x10FF
            || code >= 0x1200 && code <= 0x13FF
            || code >= 0x1E00 && code <= 0x206F;
    }
    var breakCharMap = reduce(',&?/;] '.split(''), function (obj, ch) {
        obj[ch] = true;
        return obj;
    }, {});
    function isWordBreakChar(ch) {
        if (isAlphabeticLetter(ch)) {
            if (breakCharMap[ch]) {
                return true;
            }
            return false;
        }
        return true;
    }
    function wrapText(text, font, lineWidth, isBreakAll, lastAccumWidth) {
        var lines = [];
        var linesWidths = [];
        var line = '';
        var currentWord = '';
        var currentWordWidth = 0;
        var accumWidth = 0;
        for (var i = 0; i < text.length; i++) {
            var ch = text.charAt(i);
            if (ch === '\n') {
                if (currentWord) {
                    line += currentWord;
                    accumWidth += currentWordWidth;
                }
                lines.push(line);
                linesWidths.push(accumWidth);
                line = '';
                currentWord = '';
                currentWordWidth = 0;
                accumWidth = 0;
                continue;
            }
            var chWidth = getWidth(ch, font);
            var inWord = isBreakAll ? false : !isWordBreakChar(ch);
            if (!lines.length
                ? lastAccumWidth + accumWidth + chWidth > lineWidth
                : accumWidth + chWidth > lineWidth) {
                if (!accumWidth) {
                    if (inWord) {
                        lines.push(currentWord);
                        linesWidths.push(currentWordWidth);
                        currentWord = ch;
                        currentWordWidth = chWidth;
                    }
                    else {
                        lines.push(ch);
                        linesWidths.push(chWidth);
                    }
                }
                else if (line || currentWord) {
                    if (inWord) {
                        if (!line) {
                            line = currentWord;
                            currentWord = '';
                            currentWordWidth = 0;
                            accumWidth = currentWordWidth;
                        }
                        lines.push(line);
                        linesWidths.push(accumWidth - currentWordWidth);
                        currentWord += ch;
                        currentWordWidth += chWidth;
                        line = '';
                        accumWidth = currentWordWidth;
                    }
                    else {
                        if (currentWord) {
                            line += currentWord;
                            currentWord = '';
                            currentWordWidth = 0;
                        }
                        lines.push(line);
                        linesWidths.push(accumWidth);
                        line = ch;
                        accumWidth = chWidth;
                    }
                }
                continue;
            }
            accumWidth += chWidth;
            if (inWord) {
                currentWord += ch;
                currentWordWidth += chWidth;
            }
            else {
                if (currentWord) {
                    line += currentWord;
                    currentWord = '';
                    currentWordWidth = 0;
                }
                line += ch;
            }
        }
        if (!lines.length && !line) {
            line = text;
            currentWord = '';
            currentWordWidth = 0;
        }
        if (currentWord) {
            line += currentWord;
        }
        if (line) {
            lines.push(line);
            linesWidths.push(accumWidth);
        }
        if (lines.length === 1) {
            accumWidth += lastAccumWidth;
        }
        return {
            accumWidth: accumWidth,
            lines: lines,
            linesWidths: linesWidths
        };
    }

    var STYLE_MAGIC_KEY = '__zr_style_' + Math.round((Math.random() * 10));
    var DEFAULT_COMMON_STYLE = {
        shadowBlur: 0,
        shadowOffsetX: 0,
        shadowOffsetY: 0,
        shadowColor: '#000',
        opacity: 1,
        blend: 'source-over'
    };
    var DEFAULT_COMMON_ANIMATION_PROPS = {
        style: {
            shadowBlur: true,
            shadowOffsetX: true,
            shadowOffsetY: true,
            shadowColor: true,
            opacity: true
        }
    };
    DEFAULT_COMMON_STYLE[STYLE_MAGIC_KEY] = true;
    var PRIMARY_STATES_KEYS$1 = ['z', 'z2', 'invisible'];
    var PRIMARY_STATES_KEYS_IN_HOVER_LAYER = ['invisible'];
    var Displayable = (function (_super) {
        __extends(Displayable, _super);
        function Displayable(props) {
            return _super.call(this, props) || this;
        }
        Displayable.prototype._init = function (props) {
            var keysArr = keys(props);
            for (var i = 0; i < keysArr.length; i++) {
                var key = keysArr[i];
                if (key === 'style') {
                    this.useStyle(props[key]);
                }
                else {
                    _super.prototype.attrKV.call(this, key, props[key]);
                }
            }
            if (!this.style) {
                this.useStyle({});
            }
        };
        Displayable.prototype.beforeBrush = function () { };
        Displayable.prototype.afterBrush = function () { };
        Displayable.prototype.innerBeforeBrush = function () { };
        Displayable.prototype.innerAfterBrush = function () { };
        Displayable.prototype.shouldBePainted = function (viewWidth, viewHeight, considerClipPath, considerAncestors) {
            var m = this.transform;
            if (this.ignore
                || this.invisible
                || this.style.opacity === 0
                || (this.culling
                    && isDisplayableCulled(this, viewWidth, viewHeight))
                || (m && !m[0] && !m[3])) {
                return false;
            }
            if (considerClipPath && this.__clipPaths) {
                for (var i = 0; i < this.__clipPaths.length; ++i) {
                    if (this.__clipPaths[i].isZeroArea()) {
                        return false;
                    }
                }
            }
            if (considerAncestors && this.parent) {
                var parent_1 = this.parent;
                while (parent_1) {
                    if (parent_1.ignore) {
                        return false;
                    }
                    parent_1 = parent_1.parent;
                }
            }
            return true;
        };
        Displayable.prototype.contain = function (x, y) {
            return this.rectContain(x, y);
        };
        Displayable.prototype.traverse = function (cb, context) {
            cb.call(context, this);
        };
        Displayable.prototype.rectContain = function (x, y) {
            var coord = this.transformCoordToLocal(x, y);
            var rect = this.getBoundingRect();
            return rect.contain(coord[0], coord[1]);
        };
        Displayable.prototype.getPaintRect = function () {
            var rect = this._paintRect;
            if (!this._paintRect || this.__dirty) {
                var transform = this.transform;
                var elRect = this.getBoundingRect();
                var style = this.style;
                var shadowSize = style.shadowBlur || 0;
                var shadowOffsetX = style.shadowOffsetX || 0;
                var shadowOffsetY = style.shadowOffsetY || 0;
                rect = this._paintRect || (this._paintRect = new BoundingRect(0, 0, 0, 0));
                if (transform) {
                    BoundingRect.applyTransform(rect, elRect, transform);
                }
                else {
                    rect.copy(elRect);
                }
                if (shadowSize || shadowOffsetX || shadowOffsetY) {
                    rect.width += shadowSize * 2 + Math.abs(shadowOffsetX);
                    rect.height += shadowSize * 2 + Math.abs(shadowOffsetY);
                    rect.x = Math.min(rect.x, rect.x + shadowOffsetX - shadowSize);
                    rect.y = Math.min(rect.y, rect.y + shadowOffsetY - shadowSize);
                }
                var tolerance = this.dirtyRectTolerance;
                if (!rect.isZero()) {
                    rect.x = Math.floor(rect.x - tolerance);
                    rect.y = Math.floor(rect.y - tolerance);
                    rect.width = Math.ceil(rect.width + 1 + tolerance * 2);
                    rect.height = Math.ceil(rect.height + 1 + tolerance * 2);
                }
            }
            return rect;
        };
        Displayable.prototype.setPrevPaintRect = function (paintRect) {
            if (paintRect) {
                this._prevPaintRect = this._prevPaintRect || new BoundingRect(0, 0, 0, 0);
                this._prevPaintRect.copy(paintRect);
            }
            else {
                this._prevPaintRect = null;
            }
        };
        Displayable.prototype.getPrevPaintRect = function () {
            return this._prevPaintRect;
        };
        Displayable.prototype.animateStyle = function (loop) {
            return this.animate('style', loop);
        };
        Displayable.prototype.updateDuringAnimation = function (targetKey) {
            if (targetKey === 'style') {
                this.dirtyStyle();
            }
            else {
                this.markRedraw();
            }
        };
        Displayable.prototype.attrKV = function (key, value) {
            if (key !== 'style') {
                _super.prototype.attrKV.call(this, key, value);
            }
            else {
                if (!this.style) {
                    this.useStyle(value);
                }
                else {
                    this.setStyle(value);
                }
            }
        };
        Displayable.prototype.setStyle = function (keyOrObj, value) {
            if (typeof keyOrObj === 'string') {
                this.style[keyOrObj] = value;
            }
            else {
                extend(this.style, keyOrObj);
            }
            this.dirtyStyle();
            return this;
        };
        Displayable.prototype.dirtyStyle = function (notRedraw) {
            if (!notRedraw) {
                this.markRedraw();
            }
            this.__dirty |= STYLE_CHANGED_BIT;
            if (this._rect) {
                this._rect = null;
            }
        };
        Displayable.prototype.dirty = function () {
            this.dirtyStyle();
        };
        Displayable.prototype.styleChanged = function () {
            return !!(this.__dirty & STYLE_CHANGED_BIT);
        };
        Displayable.prototype.styleUpdated = function () {
            this.__dirty &= ~STYLE_CHANGED_BIT;
        };
        Displayable.prototype.createStyle = function (obj) {
            return createObject(DEFAULT_COMMON_STYLE, obj);
        };
        Displayable.prototype.useStyle = function (obj) {
            if (!obj[STYLE_MAGIC_KEY]) {
                obj = this.createStyle(obj);
            }
            if (this.__inHover) {
                this.__hoverStyle = obj;
            }
            else {
                this.style = obj;
            }
            this.dirtyStyle();
        };
        Displayable.prototype.isStyleObject = function (obj) {
            return obj[STYLE_MAGIC_KEY];
        };
        Displayable.prototype._innerSaveToNormal = function (toState) {
            _super.prototype._innerSaveToNormal.call(this, toState);
            var normalState = this._normalState;
            if (toState.style && !normalState.style) {
                normalState.style = this._mergeStyle(this.createStyle(), this.style);
            }
            this._savePrimaryToNormal(toState, normalState, PRIMARY_STATES_KEYS$1);
        };
        Displayable.prototype._applyStateObj = function (stateName, state, normalState, keepCurrentStates, transition, animationCfg) {
            _super.prototype._applyStateObj.call(this, stateName, state, normalState, keepCurrentStates, transition, animationCfg);
            var needsRestoreToNormal = !(state && keepCurrentStates);
            var targetStyle;
            if (state && state.style) {
                if (transition) {
                    if (keepCurrentStates) {
                        targetStyle = state.style;
                    }
                    else {
                        targetStyle = this._mergeStyle(this.createStyle(), normalState.style);
                        this._mergeStyle(targetStyle, state.style);
                    }
                }
                else {
                    targetStyle = this._mergeStyle(this.createStyle(), keepCurrentStates ? this.style : normalState.style);
                    this._mergeStyle(targetStyle, state.style);
                }
            }
            else if (needsRestoreToNormal) {
                targetStyle = normalState.style;
            }
            if (targetStyle) {
                if (transition) {
                    var sourceStyle = this.style;
                    this.style = this.createStyle(needsRestoreToNormal ? {} : sourceStyle);
                    if (needsRestoreToNormal) {
                        var changedKeys = keys(sourceStyle);
                        for (var i = 0; i < changedKeys.length; i++) {
                            var key = changedKeys[i];
                            if (key in targetStyle) {
                                targetStyle[key] = targetStyle[key];
                                this.style[key] = sourceStyle[key];
                            }
                        }
                    }
                    var targetKeys = keys(targetStyle);
                    for (var i = 0; i < targetKeys.length; i++) {
                        var key = targetKeys[i];
                        this.style[key] = this.style[key];
                    }
                    this._transitionState(stateName, {
                        style: targetStyle
                    }, animationCfg, this.getAnimationStyleProps());
                }
                else {
                    this.useStyle(targetStyle);
                }
            }
            var statesKeys = this.__inHover ? PRIMARY_STATES_KEYS_IN_HOVER_LAYER : PRIMARY_STATES_KEYS$1;
            for (var i = 0; i < statesKeys.length; i++) {
                var key = statesKeys[i];
                if (state && state[key] != null) {
                    this[key] = state[key];
                }
                else if (needsRestoreToNormal) {
                    if (normalState[key] != null) {
                        this[key] = normalState[key];
                    }
                }
            }
        };
        Displayable.prototype._mergeStates = function (states) {
            var mergedState = _super.prototype._mergeStates.call(this, states);
            var mergedStyle;
            for (var i = 0; i < states.length; i++) {
                var state = states[i];
                if (state.style) {
                    mergedStyle = mergedStyle || {};
                    this._mergeStyle(mergedStyle, state.style);
                }
            }
            if (mergedStyle) {
                mergedState.style = mergedStyle;
            }
            return mergedState;
        };
        Displayable.prototype._mergeStyle = function (targetStyle, sourceStyle) {
            extend(targetStyle, sourceStyle);
            return targetStyle;
        };
        Displayable.prototype.getAnimationStyleProps = function () {
            return DEFAULT_COMMON_ANIMATION_PROPS;
        };
        Displayable.initDefaultProps = (function () {
            var dispProto = Displayable.prototype;
            dispProto.type = 'displayable';
            dispProto.invisible = false;
            dispProto.z = 0;
            dispProto.z2 = 0;
            dispProto.zlevel = 0;
            dispProto.culling = false;
            dispProto.cursor = 'pointer';
            dispProto.rectHover = false;
            dispProto.incremental = false;
            dispProto._rect = null;
            dispProto.dirtyRectTolerance = 0;
            dispProto.__dirty = REDRAW_BIT | STYLE_CHANGED_BIT;
        })();
        return Displayable;
    }(Element));
    var tmpRect$1 = new BoundingRect(0, 0, 0, 0);
    var viewRect = new BoundingRect(0, 0, 0, 0);
    function isDisplayableCulled(el, width, height) {
        tmpRect$1.copy(el.getBoundingRect());
        if (el.transform) {
            tmpRect$1.applyTransform(el.transform);
        }
        viewRect.width = width;
        viewRect.height = height;
        return !tmpRect$1.intersect(viewRect);
    }

    var mathMin$1 = Math.min;
    var mathMax$1 = Math.max;
    var mathSin = Math.sin;
    var mathCos = Math.cos;
    var PI2 = Math.PI * 2;
    var start = create();
    var end = create();
    var extremity = create();
    function fromLine(x0, y0, x1, y1, min, max) {
        min[0] = mathMin$1(x0, x1);
        min[1] = mathMin$1(y0, y1);
        max[0] = mathMax$1(x0, x1);
        max[1] = mathMax$1(y0, y1);
    }
    var xDim = [];
    var yDim = [];
    function fromCubic(x0, y0, x1, y1, x2, y2, x3, y3, min, max) {
        var cubicExtrema$1 = cubicExtrema;
        var cubicAt$1 = cubicAt;
        var n = cubicExtrema$1(x0, x1, x2, x3, xDim);
        min[0] = Infinity;
        min[1] = Infinity;
        max[0] = -Infinity;
        max[1] = -Infinity;
        for (var i = 0; i < n; i++) {
            var x = cubicAt$1(x0, x1, x2, x3, xDim[i]);
            min[0] = mathMin$1(x, min[0]);
            max[0] = mathMax$1(x, max[0]);
        }
        n = cubicExtrema$1(y0, y1, y2, y3, yDim);
        for (var i = 0; i < n; i++) {
            var y = cubicAt$1(y0, y1, y2, y3, yDim[i]);
            min[1] = mathMin$1(y, min[1]);
            max[1] = mathMax$1(y, max[1]);
        }
        min[0] = mathMin$1(x0, min[0]);
        max[0] = mathMax$1(x0, max[0]);
        min[0] = mathMin$1(x3, min[0]);
        max[0] = mathMax$1(x3, max[0]);
        min[1] = mathMin$1(y0, min[1]);
        max[1] = mathMax$1(y0, max[1]);
        min[1] = mathMin$1(y3, min[1]);
        max[1] = mathMax$1(y3, max[1]);
    }
    function fromQuadratic(x0, y0, x1, y1, x2, y2, min, max) {
        var quadraticExtremum$1 = quadraticExtremum;
        var quadraticAt$1 = quadraticAt;
        var tx = mathMax$1(mathMin$1(quadraticExtremum$1(x0, x1, x2), 1), 0);
        var ty = mathMax$1(mathMin$1(quadraticExtremum$1(y0, y1, y2), 1), 0);
        var x = quadraticAt$1(x0, x1, x2, tx);
        var y = quadraticAt$1(y0, y1, y2, ty);
        min[0] = mathMin$1(x0, x2, x);
        min[1] = mathMin$1(y0, y2, y);
        max[0] = mathMax$1(x0, x2, x);
        max[1] = mathMax$1(y0, y2, y);
    }
    function fromArc(x, y, rx, ry, startAngle, endAngle, anticlockwise, min$1, max$1) {
        var vec2Min = min;
        var vec2Max = max;
        var diff = Math.abs(startAngle - endAngle);
        if (diff % PI2 < 1e-4 && diff > 1e-4) {
            min$1[0] = x - rx;
            min$1[1] = y - ry;
            max$1[0] = x + rx;
            max$1[1] = y + ry;
            return;
        }
        start[0] = mathCos(startAngle) * rx + x;
        start[1] = mathSin(startAngle) * ry + y;
        end[0] = mathCos(endAngle) * rx + x;
        end[1] = mathSin(endAngle) * ry + y;
        vec2Min(min$1, start, end);
        vec2Max(max$1, start, end);
        startAngle = startAngle % (PI2);
        if (startAngle < 0) {
            startAngle = startAngle + PI2;
        }
        endAngle = endAngle % (PI2);
        if (endAngle < 0) {
            endAngle = endAngle + PI2;
        }
        if (startAngle > endAngle && !anticlockwise) {
            endAngle += PI2;
        }
        else if (startAngle < endAngle && anticlockwise) {
            startAngle += PI2;
        }
        if (anticlockwise) {
            var tmp = endAngle;
            endAngle = startAngle;
            startAngle = tmp;
        }
        for (var angle = 0; angle < endAngle; angle += Math.PI / 2) {
            if (angle > startAngle) {
                extremity[0] = mathCos(angle) * rx + x;
                extremity[1] = mathSin(angle) * ry + y;
                vec2Min(min$1, extremity, min$1);
                vec2Max(max$1, extremity, max$1);
            }
        }
    }

    var CMD = {
        M: 1,
        L: 2,
        C: 3,
        Q: 4,
        A: 5,
        Z: 6,
        R: 7
    };
    var tmpOutX = [];
    var tmpOutY = [];
    var min$1 = [];
    var max$1 = [];
    var min2 = [];
    var max2 = [];
    var mathMin$2 = Math.min;
    var mathMax$2 = Math.max;
    var mathCos$1 = Math.cos;
    var mathSin$1 = Math.sin;
    var mathAbs = Math.abs;
    var PI = Math.PI;
    var PI2$1 = PI * 2;
    var hasTypedArray = typeof Float32Array !== 'undefined';
    var tmpAngles = [];
    function modPI2(radian) {
        var n = Math.round(radian / PI * 1e8) / 1e8;
        return (n % 2) * PI;
    }
    function normalizeArcAngles(angles, anticlockwise) {
        var newStartAngle = modPI2(angles[0]);
        if (newStartAngle < 0) {
            newStartAngle += PI2$1;
        }
        var delta = newStartAngle - angles[0];
        var newEndAngle = angles[1];
        newEndAngle += delta;
        if (!anticlockwise && newEndAngle - newStartAngle >= PI2$1) {
            newEndAngle = newStartAngle + PI2$1;
        }
        else if (anticlockwise && newStartAngle - newEndAngle >= PI2$1) {
            newEndAngle = newStartAngle - PI2$1;
        }
        else if (!anticlockwise && newStartAngle > newEndAngle) {
            newEndAngle = newStartAngle + (PI2$1 - modPI2(newStartAngle - newEndAngle));
        }
        else if (anticlockwise && newStartAngle < newEndAngle) {
            newEndAngle = newStartAngle - (PI2$1 - modPI2(newEndAngle - newStartAngle));
        }
        angles[0] = newStartAngle;
        angles[1] = newEndAngle;
    }
    var PathProxy = (function () {
        function PathProxy(notSaveData) {
            this.dpr = 1;
            this._xi = 0;
            this._yi = 0;
            this._x0 = 0;
            this._y0 = 0;
            this._len = 0;
            if (notSaveData) {
                this._saveData = false;
            }
            if (this._saveData) {
                this.data = [];
            }
        }
        PathProxy.prototype.increaseVersion = function () {
            this._version++;
        };
        PathProxy.prototype.getVersion = function () {
            return this._version;
        };
        PathProxy.prototype.setScale = function (sx, sy, segmentIgnoreThreshold) {
            segmentIgnoreThreshold = segmentIgnoreThreshold || 0;
            if (segmentIgnoreThreshold > 0) {
                this._ux = mathAbs(segmentIgnoreThreshold / devicePixelRatio / sx) || 0;
                this._uy = mathAbs(segmentIgnoreThreshold / devicePixelRatio / sy) || 0;
            }
        };
        PathProxy.prototype.setDPR = function (dpr) {
            this.dpr = dpr;
        };
        PathProxy.prototype.setContext = function (ctx) {
            this._ctx = ctx;
        };
        PathProxy.prototype.getContext = function () {
            return this._ctx;
        };
        PathProxy.prototype.beginPath = function () {
            this._ctx && this._ctx.beginPath();
            this.reset();
            return this;
        };
        PathProxy.prototype.reset = function () {
            if (this._saveData) {
                this._len = 0;
            }
            if (this._pathSegLen) {
                this._pathSegLen = null;
                this._pathLen = 0;
            }
            this._version++;
        };
        PathProxy.prototype.moveTo = function (x, y) {
            this._drawPendingPt();
            this.addData(CMD.M, x, y);
            this._ctx && this._ctx.moveTo(x, y);
            this._x0 = x;
            this._y0 = y;
            this._xi = x;
            this._yi = y;
            return this;
        };
        PathProxy.prototype.lineTo = function (x, y) {
            var dx = mathAbs(x - this._xi);
            var dy = mathAbs(y - this._yi);
            var exceedUnit = dx > this._ux || dy > this._uy;
            this.addData(CMD.L, x, y);
            if (this._ctx && exceedUnit) {
                this._ctx.lineTo(x, y);
            }
            if (exceedUnit) {
                this._xi = x;
                this._yi = y;
                this._pendingPtDist = 0;
            }
            else {
                var d2 = dx * dx + dy * dy;
                if (d2 > this._pendingPtDist) {
                    this._pendingPtX = x;
                    this._pendingPtY = y;
                    this._pendingPtDist = d2;
                }
            }
            return this;
        };
        PathProxy.prototype.bezierCurveTo = function (x1, y1, x2, y2, x3, y3) {
            this._drawPendingPt();
            this.addData(CMD.C, x1, y1, x2, y2, x3, y3);
            if (this._ctx) {
                this._ctx.bezierCurveTo(x1, y1, x2, y2, x3, y3);
            }
            this._xi = x3;
            this._yi = y3;
            return this;
        };
        PathProxy.prototype.quadraticCurveTo = function (x1, y1, x2, y2) {
            this._drawPendingPt();
            this.addData(CMD.Q, x1, y1, x2, y2);
            if (this._ctx) {
                this._ctx.quadraticCurveTo(x1, y1, x2, y2);
            }
            this._xi = x2;
            this._yi = y2;
            return this;
        };
        PathProxy.prototype.arc = function (cx, cy, r, startAngle, endAngle, anticlockwise) {
            this._drawPendingPt();
            tmpAngles[0] = startAngle;
            tmpAngles[1] = endAngle;
            normalizeArcAngles(tmpAngles, anticlockwise);
            startAngle = tmpAngles[0];
            endAngle = tmpAngles[1];
            var delta = endAngle - startAngle;
            this.addData(CMD.A, cx, cy, r, r, startAngle, delta, 0, anticlockwise ? 0 : 1);
            this._ctx && this._ctx.arc(cx, cy, r, startAngle, endAngle, anticlockwise);
            this._xi = mathCos$1(endAngle) * r + cx;
            this._yi = mathSin$1(endAngle) * r + cy;
            return this;
        };
        PathProxy.prototype.arcTo = function (x1, y1, x2, y2, radius) {
            this._drawPendingPt();
            if (this._ctx) {
                this._ctx.arcTo(x1, y1, x2, y2, radius);
            }
            return this;
        };
        PathProxy.prototype.rect = function (x, y, w, h) {
            this._drawPendingPt();
            this._ctx && this._ctx.rect(x, y, w, h);
            this.addData(CMD.R, x, y, w, h);
            return this;
        };
        PathProxy.prototype.closePath = function () {
            this._drawPendingPt();
            this.addData(CMD.Z);
            var ctx = this._ctx;
            var x0 = this._x0;
            var y0 = this._y0;
            if (ctx) {
                ctx.closePath();
            }
            this._xi = x0;
            this._yi = y0;
            return this;
        };
        PathProxy.prototype.fill = function (ctx) {
            ctx && ctx.fill();
            this.toStatic();
        };
        PathProxy.prototype.stroke = function (ctx) {
            ctx && ctx.stroke();
            this.toStatic();
        };
        PathProxy.prototype.len = function () {
            return this._len;
        };
        PathProxy.prototype.setData = function (data) {
            var len = data.length;
            if (!(this.data && this.data.length === len) && hasTypedArray) {
                this.data = new Float32Array(len);
            }
            for (var i = 0; i < len; i++) {
                this.data[i] = data[i];
            }
            this._len = len;
        };
        PathProxy.prototype.appendPath = function (path) {
            if (!(path instanceof Array)) {
                path = [path];
            }
            var len = path.length;
            var appendSize = 0;
            var offset = this._len;
            for (var i = 0; i < len; i++) {
                appendSize += path[i].len();
            }
            if (hasTypedArray && (this.data instanceof Float32Array)) {
                this.data = new Float32Array(offset + appendSize);
            }
            for (var i = 0; i < len; i++) {
                var appendPathData = path[i].data;
                for (var k = 0; k < appendPathData.length; k++) {
                    this.data[offset++] = appendPathData[k];
                }
            }
            this._len = offset;
        };
        PathProxy.prototype.addData = function (cmd, a, b, c, d, e, f, g, h) {
            if (!this._saveData) {
                return;
            }
            var data = this.data;
            if (this._len + arguments.length > data.length) {
                this._expandData();
                data = this.data;
            }
            for (var i = 0; i < arguments.length; i++) {
                data[this._len++] = arguments[i];
            }
        };
        PathProxy.prototype._drawPendingPt = function () {
            if (this._pendingPtDist > 0) {
                this._ctx && this._ctx.lineTo(this._pendingPtX, this._pendingPtY);
                this._pendingPtDist = 0;
            }
        };
        PathProxy.prototype._expandData = function () {
            if (!(this.data instanceof Array)) {
                var newData = [];
                for (var i = 0; i < this._len; i++) {
                    newData[i] = this.data[i];
                }
                this.data = newData;
            }
        };
        PathProxy.prototype.toStatic = function () {
            if (!this._saveData) {
                return;
            }
            this._drawPendingPt();
            var data = this.data;
            if (data instanceof Array) {
                data.length = this._len;
                if (hasTypedArray && this._len > 11) {
                    this.data = new Float32Array(data);
                }
            }
        };
        PathProxy.prototype.getBoundingRect = function () {
            min$1[0] = min$1[1] = min2[0] = min2[1] = Number.MAX_VALUE;
            max$1[0] = max$1[1] = max2[0] = max2[1] = -Number.MAX_VALUE;
            var data = this.data;
            var xi = 0;
            var yi = 0;
            var x0 = 0;
            var y0 = 0;
            var i;
            for (i = 0; i < this._len;) {
                var cmd = data[i++];
                var isFirst = i === 1;
                if (isFirst) {
                    xi = data[i];
                    yi = data[i + 1];
                    x0 = xi;
                    y0 = yi;
                }
                switch (cmd) {
                    case CMD.M:
                        xi = x0 = data[i++];
                        yi = y0 = data[i++];
                        min2[0] = x0;
                        min2[1] = y0;
                        max2[0] = x0;
                        max2[1] = y0;
                        break;
                    case CMD.L:
                        fromLine(xi, yi, data[i], data[i + 1], min2, max2);
                        xi = data[i++];
                        yi = data[i++];
                        break;
                    case CMD.C:
                        fromCubic(xi, yi, data[i++], data[i++], data[i++], data[i++], data[i], data[i + 1], min2, max2);
                        xi = data[i++];
                        yi = data[i++];
                        break;
                    case CMD.Q:
                        fromQuadratic(xi, yi, data[i++], data[i++], data[i], data[i + 1], min2, max2);
                        xi = data[i++];
                        yi = data[i++];
                        break;
                    case CMD.A:
                        var cx = data[i++];
                        var cy = data[i++];
                        var rx = data[i++];
                        var ry = data[i++];
                        var startAngle = data[i++];
                        var endAngle = data[i++] + startAngle;
                        i += 1;
                        var anticlockwise = !data[i++];
                        if (isFirst) {
                            x0 = mathCos$1(startAngle) * rx + cx;
                            y0 = mathSin$1(startAngle) * ry + cy;
                        }
                        fromArc(cx, cy, rx, ry, startAngle, endAngle, anticlockwise, min2, max2);
                        xi = mathCos$1(endAngle) * rx + cx;
                        yi = mathSin$1(endAngle) * ry + cy;
                        break;
                    case CMD.R:
                        x0 = xi = data[i++];
                        y0 = yi = data[i++];
                        var width = data[i++];
                        var height = data[i++];
                        fromLine(x0, y0, x0 + width, y0 + height, min2, max2);
                        break;
                    case CMD.Z:
                        xi = x0;
                        yi = y0;
                        break;
                }
                min(min$1, min$1, min2);
                max(max$1, max$1, max2);
            }
            if (i === 0) {
                min$1[0] = min$1[1] = max$1[0] = max$1[1] = 0;
            }
            return new BoundingRect(min$1[0], min$1[1], max$1[0] - min$1[0], max$1[1] - min$1[1]);
        };
        PathProxy.prototype._calculateLength = function () {
            var data = this.data;
            var len = this._len;
            var ux = this._ux;
            var uy = this._uy;
            var xi = 0;
            var yi = 0;
            var x0 = 0;
            var y0 = 0;
            if (!this._pathSegLen) {
                this._pathSegLen = [];
            }
            var pathSegLen = this._pathSegLen;
            var pathTotalLen = 0;
            var segCount = 0;
            for (var i = 0; i < len;) {
                var cmd = data[i++];
                var isFirst = i === 1;
                if (isFirst) {
                    xi = data[i];
                    yi = data[i + 1];
                    x0 = xi;
                    y0 = yi;
                }
                var l = -1;
                switch (cmd) {
                    case CMD.M:
                        xi = x0 = data[i++];
                        yi = y0 = data[i++];
                        break;
                    case CMD.L: {
                        var x2 = data[i++];
                        var y2 = data[i++];
                        var dx = x2 - xi;
                        var dy = y2 - yi;
                        if (mathAbs(dx) > ux || mathAbs(dy) > uy || i === len - 1) {
                            l = Math.sqrt(dx * dx + dy * dy);
                            xi = x2;
                            yi = y2;
                        }
                        break;
                    }
                    case CMD.C: {
                        var x1 = data[i++];
                        var y1 = data[i++];
                        var x2 = data[i++];
                        var y2 = data[i++];
                        var x3 = data[i++];
                        var y3 = data[i++];
                        l = cubicLength(xi, yi, x1, y1, x2, y2, x3, y3, 10);
                        xi = x3;
                        yi = y3;
                        break;
                    }
                    case CMD.Q: {
                        var x1 = data[i++];
                        var y1 = data[i++];
                        var x2 = data[i++];
                        var y2 = data[i++];
                        l = quadraticLength(xi, yi, x1, y1, x2, y2, 10);
                        xi = x2;
                        yi = y2;
                        break;
                    }
                    case CMD.A:
                        var cx = data[i++];
                        var cy = data[i++];
                        var rx = data[i++];
                        var ry = data[i++];
                        var startAngle = data[i++];
                        var delta = data[i++];
                        var endAngle = delta + startAngle;
                        i += 1;
                        var anticlockwise = !data[i++];
                        if (isFirst) {
                            x0 = mathCos$1(startAngle) * rx + cx;
                            y0 = mathSin$1(startAngle) * ry + cy;
                        }
                        l = mathMax$2(rx, ry) * mathMin$2(PI2$1, Math.abs(delta));
                        xi = mathCos$1(endAngle) * rx + cx;
                        yi = mathSin$1(endAngle) * ry + cy;
                        break;
                    case CMD.R: {
                        x0 = xi = data[i++];
                        y0 = yi = data[i++];
                        var width = data[i++];
                        var height = data[i++];
                        l = width * 2 + height * 2;
                        break;
                    }
                    case CMD.Z: {
                        var dx = x0 - xi;
                        var dy = y0 - yi;
                        l = Math.sqrt(dx * dx + dy * dy);
                        xi = x0;
                        yi = y0;
                        break;
                    }
                }
                if (l >= 0) {
                    pathSegLen[segCount++] = l;
                    pathTotalLen += l;
                }
            }
            this._pathLen = pathTotalLen;
            return pathTotalLen;
        };
        PathProxy.prototype.rebuildPath = function (ctx, percent) {
            var d = this.data;
            var ux = this._ux;
            var uy = this._uy;
            var len = this._len;
            var x0;
            var y0;
            var xi;
            var yi;
            var x;
            var y;
            var drawPart = percent < 1;
            var pathSegLen;
            var pathTotalLen;
            var accumLength = 0;
            var segCount = 0;
            var displayedLength;
            var pendingPtDist = 0;
            var pendingPtX;
            var pendingPtY;
            if (drawPart) {
                if (!this._pathSegLen) {
                    this._calculateLength();
                }
                pathSegLen = this._pathSegLen;
                pathTotalLen = this._pathLen;
                displayedLength = percent * pathTotalLen;
                if (!displayedLength) {
                    return;
                }
            }
            lo: for (var i = 0; i < len;) {
                var cmd = d[i++];
                var isFirst = i === 1;
                if (isFirst) {
                    xi = d[i];
                    yi = d[i + 1];
                    x0 = xi;
                    y0 = yi;
                }
                if (cmd !== CMD.L && pendingPtDist > 0) {
                    ctx.lineTo(pendingPtX, pendingPtY);
                    pendingPtDist = 0;
                }
                switch (cmd) {
                    case CMD.M:
                        x0 = xi = d[i++];
                        y0 = yi = d[i++];
                        ctx.moveTo(xi, yi);
                        break;
                    case CMD.L: {
                        x = d[i++];
                        y = d[i++];
                        var dx = mathAbs(x - xi);
                        var dy = mathAbs(y - yi);
                        if (dx > ux || dy > uy) {
                            if (drawPart) {
                                var l = pathSegLen[segCount++];
                                if (accumLength + l > displayedLength) {
                                    var t = (displayedLength - accumLength) / l;
                                    ctx.lineTo(xi * (1 - t) + x * t, yi * (1 - t) + y * t);
                                    break lo;
                                }
                                accumLength += l;
                            }
                            ctx.lineTo(x, y);
                            xi = x;
                            yi = y;
                            pendingPtDist = 0;
                        }
                        else {
                            var d2 = dx * dx + dy * dy;
                            if (d2 > pendingPtDist) {
                                pendingPtX = x;
                                pendingPtY = y;
                                pendingPtDist = d2;
                            }
                        }
                        break;
                    }
                    case CMD.C: {
                        var x1 = d[i++];
                        var y1 = d[i++];
                        var x2 = d[i++];
                        var y2 = d[i++];
                        var x3 = d[i++];
                        var y3 = d[i++];
                        if (drawPart) {
                            var l = pathSegLen[segCount++];
                            if (accumLength + l > displayedLength) {
                                var t = (displayedLength - accumLength) / l;
                                cubicSubdivide(xi, x1, x2, x3, t, tmpOutX);
                                cubicSubdivide(yi, y1, y2, y3, t, tmpOutY);
                                ctx.bezierCurveTo(tmpOutX[1], tmpOutY[1], tmpOutX[2], tmpOutY[2], tmpOutX[3], tmpOutY[3]);
                                break lo;
                            }
                            accumLength += l;
                        }
                        ctx.bezierCurveTo(x1, y1, x2, y2, x3, y3);
                        xi = x3;
                        yi = y3;
                        break;
                    }
                    case CMD.Q: {
                        var x1 = d[i++];
                        var y1 = d[i++];
                        var x2 = d[i++];
                        var y2 = d[i++];
                        if (drawPart) {
                            var l = pathSegLen[segCount++];
                            if (accumLength + l > displayedLength) {
                                var t = (displayedLength - accumLength) / l;
                                quadraticSubdivide(xi, x1, x2, t, tmpOutX);
                                quadraticSubdivide(yi, y1, y2, t, tmpOutY);
                                ctx.quadraticCurveTo(tmpOutX[1], tmpOutY[1], tmpOutX[2], tmpOutY[2]);
                                break lo;
                            }
                            accumLength += l;
                        }
                        ctx.quadraticCurveTo(x1, y1, x2, y2);
                        xi = x2;
                        yi = y2;
                        break;
                    }
                    case CMD.A:
                        var cx = d[i++];
                        var cy = d[i++];
                        var rx = d[i++];
                        var ry = d[i++];
                        var startAngle = d[i++];
                        var delta = d[i++];
                        var psi = d[i++];
                        var anticlockwise = !d[i++];
                        var r = (rx > ry) ? rx : ry;
                        var isEllipse = mathAbs(rx - ry) > 1e-3;
                        var endAngle = startAngle + delta;
                        var breakBuild = false;
                        if (drawPart) {
                            var l = pathSegLen[segCount++];
                            if (accumLength + l > displayedLength) {
                                endAngle = startAngle + delta * (displayedLength - accumLength) / l;
                                breakBuild = true;
                            }
                            accumLength += l;
                        }
                        if (isEllipse && ctx.ellipse) {
                            ctx.ellipse(cx, cy, rx, ry, psi, startAngle, endAngle, anticlockwise);
                        }
                        else {
                            ctx.arc(cx, cy, r, startAngle, endAngle, anticlockwise);
                        }
                        if (breakBuild) {
                            break lo;
                        }
                        if (isFirst) {
                            x0 = mathCos$1(startAngle) * rx + cx;
                            y0 = mathSin$1(startAngle) * ry + cy;
                        }
                        xi = mathCos$1(endAngle) * rx + cx;
                        yi = mathSin$1(endAngle) * ry + cy;
                        break;
                    case CMD.R:
                        x0 = xi = d[i];
                        y0 = yi = d[i + 1];
                        x = d[i++];
                        y = d[i++];
                        var width = d[i++];
                        var height = d[i++];
                        if (drawPart) {
                            var l = pathSegLen[segCount++];
                            if (accumLength + l > displayedLength) {
                                var d_1 = displayedLength - accumLength;
                                ctx.moveTo(x, y);
                                ctx.lineTo(x + mathMin$2(d_1, width), y);
                                d_1 -= width;
                                if (d_1 > 0) {
                                    ctx.lineTo(x + width, y + mathMin$2(d_1, height));
                                }
                                d_1 -= height;
                                if (d_1 > 0) {
                                    ctx.lineTo(x + mathMax$2(width - d_1, 0), y + height);
                                }
                                d_1 -= width;
                                if (d_1 > 0) {
                                    ctx.lineTo(x, y + mathMax$2(height - d_1, 0));
                                }
                                break lo;
                            }
                            accumLength += l;
                        }
                        ctx.rect(x, y, width, height);
                        break;
                    case CMD.Z:
                        if (drawPart) {
                            var l = pathSegLen[segCount++];
                            if (accumLength + l > displayedLength) {
                                var t = (displayedLength - accumLength) / l;
                                ctx.lineTo(xi * (1 - t) + x0 * t, yi * (1 - t) + y0 * t);
                                break lo;
                            }
                            accumLength += l;
                        }
                        ctx.closePath();
                        xi = x0;
                        yi = y0;
                }
            }
        };
        PathProxy.prototype.clone = function () {
            var newProxy = new PathProxy();
            var data = this.data;
            newProxy.data = data.slice ? data.slice()
                : Array.prototype.slice.call(data);
            newProxy._len = this._len;
            return newProxy;
        };
        PathProxy.CMD = CMD;
        PathProxy.initDefaultProps = (function () {
            var proto = PathProxy.prototype;
            proto._saveData = true;
            proto._ux = 0;
            proto._uy = 0;
            proto._pendingPtDist = 0;
            proto._version = 0;
        })();
        return PathProxy;
    }());

    function containStroke(x0, y0, x1, y1, lineWidth, x, y) {
        if (lineWidth === 0) {
            return false;
        }
        var _l = lineWidth;
        var _a = 0;
        var _b = x0;
        if ((y > y0 + _l && y > y1 + _l)
            || (y < y0 - _l && y < y1 - _l)
            || (x > x0 + _l && x > x1 + _l)
            || (x < x0 - _l && x < x1 - _l)) {
            return false;
        }
        if (x0 !== x1) {
            _a = (y0 - y1) / (x0 - x1);
            _b = (x0 * y1 - x1 * y0) / (x0 - x1);
        }
        else {
            return Math.abs(x - x0) <= _l / 2;
        }
        var tmp = _a * x - y + _b;
        var _s = tmp * tmp / (_a * _a + 1);
        return _s <= _l / 2 * _l / 2;
    }

    function containStroke$1(x0, y0, x1, y1, x2, y2, x3, y3, lineWidth, x, y) {
        if (lineWidth === 0) {
            return false;
        }
        var _l = lineWidth;
        if ((y > y0 + _l && y > y1 + _l && y > y2 + _l && y > y3 + _l)
            || (y < y0 - _l && y < y1 - _l && y < y2 - _l && y < y3 - _l)
            || (x > x0 + _l && x > x1 + _l && x > x2 + _l && x > x3 + _l)
            || (x < x0 - _l && x < x1 - _l && x < x2 - _l && x < x3 - _l)) {
            return false;
        }
        var d = cubicProjectPoint(x0, y0, x1, y1, x2, y2, x3, y3, x, y, null);
        return d <= _l / 2;
    }

    function containStroke$2(x0, y0, x1, y1, x2, y2, lineWidth, x, y) {
        if (lineWidth === 0) {
            return false;
        }
        var _l = lineWidth;
        if ((y > y0 + _l && y > y1 + _l && y > y2 + _l)
            || (y < y0 - _l && y < y1 - _l && y < y2 - _l)
            || (x > x0 + _l && x > x1 + _l && x > x2 + _l)
            || (x < x0 - _l && x < x1 - _l && x < x2 - _l)) {
            return false;
        }
        var d = quadraticProjectPoint(x0, y0, x1, y1, x2, y2, x, y, null);
        return d <= _l / 2;
    }

    var PI2$2 = Math.PI * 2;
    function normalizeRadian(angle) {
        angle %= PI2$2;
        if (angle < 0) {
            angle += PI2$2;
        }
        return angle;
    }

    var PI2$3 = Math.PI * 2;
    function containStroke$3(cx, cy, r, startAngle, endAngle, anticlockwise, lineWidth, x, y) {
        if (lineWidth === 0) {
            return false;
        }
        var _l = lineWidth;
        x -= cx;
        y -= cy;
        var d = Math.sqrt(x * x + y * y);
        if ((d - _l > r) || (d + _l < r)) {
            return false;
        }
        if (Math.abs(startAngle - endAngle) % PI2$3 < 1e-4) {
            return true;
        }
        if (anticlockwise) {
            var tmp = startAngle;
            startAngle = normalizeRadian(endAngle);
            endAngle = normalizeRadian(tmp);
        }
        else {
            startAngle = normalizeRadian(startAngle);
            endAngle = normalizeRadian(endAngle);
        }
        if (startAngle > endAngle) {
            endAngle += PI2$3;
        }
        var angle = Math.atan2(y, x);
        if (angle < 0) {
            angle += PI2$3;
        }
        return (angle >= startAngle && angle <= endAngle)
            || (angle + PI2$3 >= startAngle && angle + PI2$3 <= endAngle);
    }

    function windingLine(x0, y0, x1, y1, x, y) {
        if ((y > y0 && y > y1) || (y < y0 && y < y1)) {
            return 0;
        }
        if (y1 === y0) {
            return 0;
        }
        var t = (y - y0) / (y1 - y0);
        var dir = y1 < y0 ? 1 : -1;
        if (t === 1 || t === 0) {
            dir = y1 < y0 ? 0.5 : -0.5;
        }
        var x_ = t * (x1 - x0) + x0;
        return x_ === x ? Infinity : x_ > x ? dir : 0;
    }

    var CMD$1 = PathProxy.CMD;
    var PI2$4 = Math.PI * 2;
    var EPSILON$3 = 1e-4;
    function isAroundEqual(a, b) {
        return Math.abs(a - b) < EPSILON$3;
    }
    var roots = [-1, -1, -1];
    var extrema = [-1, -1];
    function swapExtrema() {
        var tmp = extrema[0];
        extrema[0] = extrema[1];
        extrema[1] = tmp;
    }
    function windingCubic(x0, y0, x1, y1, x2, y2, x3, y3, x, y) {
        if ((y > y0 && y > y1 && y > y2 && y > y3)
            || (y < y0 && y < y1 && y < y2 && y < y3)) {
            return 0;
        }
        var nRoots = cubicRootAt(y0, y1, y2, y3, y, roots);
        if (nRoots === 0) {
            return 0;
        }
        else {
            var w = 0;
            var nExtrema = -1;
            var y0_ = void 0;
            var y1_ = void 0;
            for (var i = 0; i < nRoots; i++) {
                var t = roots[i];
                var unit = (t === 0 || t === 1) ? 0.5 : 1;
                var x_ = cubicAt(x0, x1, x2, x3, t);
                if (x_ < x) {
                    continue;
                }
                if (nExtrema < 0) {
                    nExtrema = cubicExtrema(y0, y1, y2, y3, extrema);
                    if (extrema[1] < extrema[0] && nExtrema > 1) {
                        swapExtrema();
                    }
                    y0_ = cubicAt(y0, y1, y2, y3, extrema[0]);
                    if (nExtrema > 1) {
                        y1_ = cubicAt(y0, y1, y2, y3, extrema[1]);
                    }
                }
                if (nExtrema === 2) {
                    if (t < extrema[0]) {
                        w += y0_ < y0 ? unit : -unit;
                    }
                    else if (t < extrema[1]) {
                        w += y1_ < y0_ ? unit : -unit;
                    }
                    else {
                        w += y3 < y1_ ? unit : -unit;
                    }
                }
                else {
                    if (t < extrema[0]) {
                        w += y0_ < y0 ? unit : -unit;
                    }
                    else {
                        w += y3 < y0_ ? unit : -unit;
                    }
                }
            }
            return w;
        }
    }
    function windingQuadratic(x0, y0, x1, y1, x2, y2, x, y) {
        if ((y > y0 && y > y1 && y > y2)
            || (y < y0 && y < y1 && y < y2)) {
            return 0;
        }
        var nRoots = quadraticRootAt(y0, y1, y2, y, roots);
        if (nRoots === 0) {
            return 0;
        }
        else {
            var t = quadraticExtremum(y0, y1, y2);
            if (t >= 0 && t <= 1) {
                var w = 0;
                var y_ = quadraticAt(y0, y1, y2, t);
                for (var i = 0; i < nRoots; i++) {
                    var unit = (roots[i] === 0 || roots[i] === 1) ? 0.5 : 1;
                    var x_ = quadraticAt(x0, x1, x2, roots[i]);
                    if (x_ < x) {
                        continue;
                    }
                    if (roots[i] < t) {
                        w += y_ < y0 ? unit : -unit;
                    }
                    else {
                        w += y2 < y_ ? unit : -unit;
                    }
                }
                return w;
            }
            else {
                var unit = (roots[0] === 0 || roots[0] === 1) ? 0.5 : 1;
                var x_ = quadraticAt(x0, x1, x2, roots[0]);
                if (x_ < x) {
                    return 0;
                }
                return y2 < y0 ? unit : -unit;
            }
        }
    }
    function windingArc(cx, cy, r, startAngle, endAngle, anticlockwise, x, y) {
        y -= cy;
        if (y > r || y < -r) {
            return 0;
        }
        var tmp = Math.sqrt(r * r - y * y);
        roots[0] = -tmp;
        roots[1] = tmp;
        var dTheta = Math.abs(startAngle - endAngle);
        if (dTheta < 1e-4) {
            return 0;
        }
        if (dTheta >= PI2$4 - 1e-4) {
            startAngle = 0;
            endAngle = PI2$4;
            var dir = anticlockwise ? 1 : -1;
            if (x >= roots[0] + cx && x <= roots[1] + cx) {
                return dir;
            }
            else {
                return 0;
            }
        }
        if (startAngle > endAngle) {
            var tmp_1 = startAngle;
            startAngle = endAngle;
            endAngle = tmp_1;
        }
        if (startAngle < 0) {
            startAngle += PI2$4;
            endAngle += PI2$4;
        }
        var w = 0;
        for (var i = 0; i < 2; i++) {
            var x_ = roots[i];
            if (x_ + cx > x) {
                var angle = Math.atan2(y, x_);
                var dir = anticlockwise ? 1 : -1;
                if (angle < 0) {
                    angle = PI2$4 + angle;
                }
                if ((angle >= startAngle && angle <= endAngle)
                    || (angle + PI2$4 >= startAngle && angle + PI2$4 <= endAngle)) {
                    if (angle > Math.PI / 2 && angle < Math.PI * 1.5) {
                        dir = -dir;
                    }
                    w += dir;
                }
            }
        }
        return w;
    }
    function containPath(path, lineWidth, isStroke, x, y) {
        var data = path.data;
        var len = path.len();
        var w = 0;
        var xi = 0;
        var yi = 0;
        var x0 = 0;
        var y0 = 0;
        var x1;
        var y1;
        for (var i = 0; i < len;) {
            var cmd = data[i++];
            var isFirst = i === 1;
            if (cmd === CMD$1.M && i > 1) {
                if (!isStroke) {
                    w += windingLine(xi, yi, x0, y0, x, y);
                }
            }
            if (isFirst) {
                xi = data[i];
                yi = data[i + 1];
                x0 = xi;
                y0 = yi;
            }
            switch (cmd) {
                case CMD$1.M:
                    x0 = data[i++];
                    y0 = data[i++];
                    xi = x0;
                    yi = y0;
                    break;
                case CMD$1.L:
                    if (isStroke) {
                        if (containStroke(xi, yi, data[i], data[i + 1], lineWidth, x, y)) {
                            return true;
                        }
                    }
                    else {
                        w += windingLine(xi, yi, data[i], data[i + 1], x, y) || 0;
                    }
                    xi = data[i++];
                    yi = data[i++];
                    break;
                case CMD$1.C:
                    if (isStroke) {
                        if (containStroke$1(xi, yi, data[i++], data[i++], data[i++], data[i++], data[i], data[i + 1], lineWidth, x, y)) {
                            return true;
                        }
                    }
                    else {
                        w += windingCubic(xi, yi, data[i++], data[i++], data[i++], data[i++], data[i], data[i + 1], x, y) || 0;
                    }
                    xi = data[i++];
                    yi = data[i++];
                    break;
                case CMD$1.Q:
                    if (isStroke) {
                        if (containStroke$2(xi, yi, data[i++], data[i++], data[i], data[i + 1], lineWidth, x, y)) {
                            return true;
                        }
                    }
                    else {
                        w += windingQuadratic(xi, yi, data[i++], data[i++], data[i], data[i + 1], x, y) || 0;
                    }
                    xi = data[i++];
                    yi = data[i++];
                    break;
                case CMD$1.A:
                    var cx = data[i++];
                    var cy = data[i++];
                    var rx = data[i++];
                    var ry = data[i++];
                    var theta = data[i++];
                    var dTheta = data[i++];
                    i += 1;
                    var anticlockwise = !!(1 - data[i++]);
                    x1 = Math.cos(theta) * rx + cx;
                    y1 = Math.sin(theta) * ry + cy;
                    if (!isFirst) {
                        w += windingLine(xi, yi, x1, y1, x, y);
                    }
                    else {
                        x0 = x1;
                        y0 = y1;
                    }
                    var _x = (x - cx) * ry / rx + cx;
                    if (isStroke) {
                        if (containStroke$3(cx, cy, ry, theta, theta + dTheta, anticlockwise, lineWidth, _x, y)) {
                            return true;
                        }
                    }
                    else {
                        w += windingArc(cx, cy, ry, theta, theta + dTheta, anticlockwise, _x, y);
                    }
                    xi = Math.cos(theta + dTheta) * rx + cx;
                    yi = Math.sin(theta + dTheta) * ry + cy;
                    break;
                case CMD$1.R:
                    x0 = xi = data[i++];
                    y0 = yi = data[i++];
                    var width = data[i++];
                    var height = data[i++];
                    x1 = x0 + width;
                    y1 = y0 + height;
                    if (isStroke) {
                        if (containStroke(x0, y0, x1, y0, lineWidth, x, y)
                            || containStroke(x1, y0, x1, y1, lineWidth, x, y)
                            || containStroke(x1, y1, x0, y1, lineWidth, x, y)
                            || containStroke(x0, y1, x0, y0, lineWidth, x, y)) {
                            return true;
                        }
                    }
                    else {
                        w += windingLine(x1, y0, x1, y1, x, y);
                        w += windingLine(x0, y1, x0, y0, x, y);
                    }
                    break;
                case CMD$1.Z:
                    if (isStroke) {
                        if (containStroke(xi, yi, x0, y0, lineWidth, x, y)) {
                            return true;
                        }
                    }
                    else {
                        w += windingLine(xi, yi, x0, y0, x, y);
                    }
                    xi = x0;
                    yi = y0;
                    break;
            }
        }
        if (!isStroke && !isAroundEqual(yi, y0)) {
            w += windingLine(xi, yi, x0, y0, x, y) || 0;
        }
        return w !== 0;
    }
    function contain(pathProxy, x, y) {
        return containPath(pathProxy, 0, false, x, y);
    }
    function containStroke$4(pathProxy, lineWidth, x, y) {
        return containPath(pathProxy, lineWidth, true, x, y);
    }

    var DEFAULT_PATH_STYLE = defaults({
        fill: '#000',
        stroke: null,
        strokePercent: 1,
        fillOpacity: 1,
        strokeOpacity: 1,
        lineDashOffset: 0,
        lineWidth: 1,
        lineCap: 'butt',
        miterLimit: 10,
        strokeNoScale: false,
        strokeFirst: false
    }, DEFAULT_COMMON_STYLE);
    var DEFAULT_PATH_ANIMATION_PROPS = {
        style: defaults({
            fill: true,
            stroke: true,
            strokePercent: true,
            fillOpacity: true,
            strokeOpacity: true,
            lineDashOffset: true,
            lineWidth: true,
            miterLimit: true
        }, DEFAULT_COMMON_ANIMATION_PROPS.style)
    };
    var pathCopyParams = TRANSFORMABLE_PROPS.concat(['invisible',
        'culling', 'z', 'z2', 'zlevel', 'parent'
    ]);
    var Path = (function (_super) {
        __extends(Path, _super);
        function Path(opts) {
            return _super.call(this, opts) || this;
        }
        Path.prototype.update = function () {
            var _this = this;
            _super.prototype.update.call(this);
            var style = this.style;
            if (style.decal) {
                var decalEl = this._decalEl = this._decalEl || new Path();
                if (decalEl.buildPath === Path.prototype.buildPath) {
                    decalEl.buildPath = function (ctx) {
                        _this.buildPath(ctx, _this.shape);
                    };
                }
                decalEl.silent = true;
                var decalElStyle = decalEl.style;
                for (var key in style) {
                    if (decalElStyle[key] !== style[key]) {
                        decalElStyle[key] = style[key];
                    }
                }
                decalElStyle.fill = style.fill ? style.decal : null;
                decalElStyle.decal = null;
                decalElStyle.shadowColor = null;
                style.strokeFirst && (decalElStyle.stroke = null);
                for (var i = 0; i < pathCopyParams.length; ++i) {
                    decalEl[pathCopyParams[i]] = this[pathCopyParams[i]];
                }
                decalEl.__dirty |= REDRAW_BIT;
            }
            else if (this._decalEl) {
                this._decalEl = null;
            }
        };
        Path.prototype.getDecalElement = function () {
            return this._decalEl;
        };
        Path.prototype._init = function (props) {
            var keysArr = keys(props);
            this.shape = this.getDefaultShape();
            var defaultStyle = this.getDefaultStyle();
            if (defaultStyle) {
                this.useStyle(defaultStyle);
            }
            for (var i = 0; i < keysArr.length; i++) {
                var key = keysArr[i];
                var value = props[key];
                if (key === 'style') {
                    if (!this.style) {
                        this.useStyle(value);
                    }
                    else {
                        extend(this.style, value);
                    }
                }
                else if (key === 'shape') {
                    extend(this.shape, value);
                }
                else {
                    _super.prototype.attrKV.call(this, key, value);
                }
            }
            if (!this.style) {
                this.useStyle({});
            }
        };
        Path.prototype.getDefaultStyle = function () {
            return null;
        };
        Path.prototype.getDefaultShape = function () {
            return {};
        };
        Path.prototype.canBeInsideText = function () {
            return this.hasFill();
        };
        Path.prototype.getInsideTextFill = function () {
            var pathFill = this.style.fill;
            if (pathFill !== 'none') {
                if (isString(pathFill)) {
                    var fillLum = lum(pathFill, 0);
                    if (fillLum > 0.5) {
                        return DARK_LABEL_COLOR;
                    }
                    else if (fillLum > 0.2) {
                        return LIGHTER_LABEL_COLOR;
                    }
                    return LIGHT_LABEL_COLOR;
                }
                else if (pathFill) {
                    return LIGHT_LABEL_COLOR;
                }
            }
            return DARK_LABEL_COLOR;
        };
        Path.prototype.getInsideTextStroke = function (textFill) {
            var pathFill = this.style.fill;
            if (isString(pathFill)) {
                var zr = this.__zr;
                var isDarkMode = !!(zr && zr.isDarkMode());
                var isDarkLabel = lum(textFill, 0) < DARK_MODE_THRESHOLD;
                if (isDarkMode === isDarkLabel) {
                    return pathFill;
                }
            }
        };
        Path.prototype.buildPath = function (ctx, shapeCfg, inBatch) { };
        Path.prototype.pathUpdated = function () {
            this.__dirty &= ~SHAPE_CHANGED_BIT;
        };
        Path.prototype.getUpdatedPathProxy = function (inBatch) {
            !this.path && this.createPathProxy();
            this.path.beginPath();
            this.buildPath(this.path, this.shape, inBatch);
            return this.path;
        };
        Path.prototype.createPathProxy = function () {
            this.path = new PathProxy(false);
        };
        Path.prototype.hasStroke = function () {
            var style = this.style;
            var stroke = style.stroke;
            return !(stroke == null || stroke === 'none' || !(style.lineWidth > 0));
        };
        Path.prototype.hasFill = function () {
            var style = this.style;
            var fill = style.fill;
            return fill != null && fill !== 'none';
        };
        Path.prototype.getBoundingRect = function () {
            var rect = this._rect;
            var style = this.style;
            var needsUpdateRect = !rect;
            if (needsUpdateRect) {
                var firstInvoke = false;
                if (!this.path) {
                    firstInvoke = true;
                    this.createPathProxy();
                }
                var path = this.path;
                if (firstInvoke || (this.__dirty & SHAPE_CHANGED_BIT)) {
                    path.beginPath();
                    this.buildPath(path, this.shape, false);
                    this.pathUpdated();
                }
                rect = path.getBoundingRect();
            }
            this._rect = rect;
            if (this.hasStroke() && this.path && this.path.len() > 0) {
                var rectStroke = this._rectStroke || (this._rectStroke = rect.clone());
                if (this.__dirty || needsUpdateRect) {
                    rectStroke.copy(rect);
                    var lineScale = style.strokeNoScale ? this.getLineScale() : 1;
                    var w = style.lineWidth;
                    if (!this.hasFill()) {
                        var strokeContainThreshold = this.strokeContainThreshold;
                        w = Math.max(w, strokeContainThreshold == null ? 4 : strokeContainThreshold);
                    }
                    if (lineScale > 1e-10) {
                        rectStroke.width += w / lineScale;
                        rectStroke.height += w / lineScale;
                        rectStroke.x -= w / lineScale / 2;
                        rectStroke.y -= w / lineScale / 2;
                    }
                }
                return rectStroke;
            }
            return rect;
        };
        Path.prototype.contain = function (x, y) {
            var localPos = this.transformCoordToLocal(x, y);
            var rect = this.getBoundingRect();
            var style = this.style;
            x = localPos[0];
            y = localPos[1];
            if (rect.contain(x, y)) {
                var pathProxy = this.path;
                if (this.hasStroke()) {
                    var lineWidth = style.lineWidth;
                    var lineScale = style.strokeNoScale ? this.getLineScale() : 1;
                    if (lineScale > 1e-10) {
                        if (!this.hasFill()) {
                            lineWidth = Math.max(lineWidth, this.strokeContainThreshold);
                        }
                        if (containStroke$4(pathProxy, lineWidth / lineScale, x, y)) {
                            return true;
                        }
                    }
                }
                if (this.hasFill()) {
                    return contain(pathProxy, x, y);
                }
            }
            return false;
        };
        Path.prototype.dirtyShape = function () {
            this.__dirty |= SHAPE_CHANGED_BIT;
            if (this._rect) {
                this._rect = null;
            }
            if (this._decalEl) {
                this._decalEl.dirtyShape();
            }
            this.markRedraw();
        };
        Path.prototype.dirty = function () {
            this.dirtyStyle();
            this.dirtyShape();
        };
        Path.prototype.animateShape = function (loop) {
            return this.animate('shape', loop);
        };
        Path.prototype.updateDuringAnimation = function (targetKey) {
            if (targetKey === 'style') {
                this.dirtyStyle();
            }
            else if (targetKey === 'shape') {
                this.dirtyShape();
            }
            else {
                this.markRedraw();
            }
        };
        Path.prototype.attrKV = function (key, value) {
            if (key === 'shape') {
                this.setShape(value);
            }
            else {
                _super.prototype.attrKV.call(this, key, value);
            }
        };
        Path.prototype.setShape = function (keyOrObj, value) {
            var shape = this.shape;
            if (!shape) {
                shape = this.shape = {};
            }
            if (typeof keyOrObj === 'string') {
                shape[keyOrObj] = value;
            }
            else {
                extend(shape, keyOrObj);
            }
            this.dirtyShape();
            return this;
        };
        Path.prototype.shapeChanged = function () {
            return !!(this.__dirty & SHAPE_CHANGED_BIT);
        };
        Path.prototype.createStyle = function (obj) {
            return createObject(DEFAULT_PATH_STYLE, obj);
        };
        Path.prototype._innerSaveToNormal = function (toState) {
            _super.prototype._innerSaveToNormal.call(this, toState);
            var normalState = this._normalState;
            if (toState.shape && !normalState.shape) {
                normalState.shape = extend({}, this.shape);
            }
        };
        Path.prototype._applyStateObj = function (stateName, state, normalState, keepCurrentStates, transition, animationCfg) {
            _super.prototype._applyStateObj.call(this, stateName, state, normalState, keepCurrentStates, transition, animationCfg);
            var needsRestoreToNormal = !(state && keepCurrentStates);
            var targetShape;
            if (state && state.shape) {
                if (transition) {
                    if (keepCurrentStates) {
                        targetShape = state.shape;
                    }
                    else {
                        targetShape = extend({}, normalState.shape);
                        extend(targetShape, state.shape);
                    }
                }
                else {
                    targetShape = extend({}, keepCurrentStates ? this.shape : normalState.shape);
                    extend(targetShape, state.shape);
                }
            }
            else if (needsRestoreToNormal) {
                targetShape = normalState.shape;
            }
            if (targetShape) {
                if (transition) {
                    this.shape = extend({}, this.shape);
                    var targetShapePrimaryProps = {};
                    var shapeKeys = keys(targetShape);
                    for (var i = 0; i < shapeKeys.length; i++) {
                        var key = shapeKeys[i];
                        if (typeof targetShape[key] === 'object') {
                            this.shape[key] = targetShape[key];
                        }
                        else {
                            targetShapePrimaryProps[key] = targetShape[key];
                        }
                    }
                    this._transitionState(stateName, {
                        shape: targetShapePrimaryProps
                    }, animationCfg);
                }
                else {
                    this.shape = targetShape;
                    this.dirtyShape();
                }
            }
        };
        Path.prototype._mergeStates = function (states) {
            var mergedState = _super.prototype._mergeStates.call(this, states);
            var mergedShape;
            for (var i = 0; i < states.length; i++) {
                var state = states[i];
                if (state.shape) {
                    mergedShape = mergedShape || {};
                    this._mergeStyle(mergedShape, state.shape);
                }
            }
            if (mergedShape) {
                mergedState.shape = mergedShape;
            }
            return mergedState;
        };
        Path.prototype.getAnimationStyleProps = function () {
            return DEFAULT_PATH_ANIMATION_PROPS;
        };
        Path.prototype.isZeroArea = function () {
            return false;
        };
        Path.extend = function (defaultProps) {
            var Sub = (function (_super) {
                __extends(Sub, _super);
                function Sub(opts) {
                    var _this = _super.call(this, opts) || this;
                    defaultProps.init && defaultProps.init.call(_this, opts);
                    return _this;
                }
                Sub.prototype.getDefaultStyle = function () {
                    return clone(defaultProps.style);
                };
                Sub.prototype.getDefaultShape = function () {
                    return clone(defaultProps.shape);
                };
                return Sub;
            }(Path));
            for (var key in defaultProps) {
                if (typeof defaultProps[key] === 'function') {
                    Sub.prototype[key] = defaultProps[key];
                }
            }
            return Sub;
        };
        Path.initDefaultProps = (function () {
            var pathProto = Path.prototype;
            pathProto.type = 'path';
            pathProto.strokeContainThreshold = 5;
            pathProto.segmentIgnoreThreshold = 0;
            pathProto.subPixelOptimize = false;
            pathProto.autoBatch = false;
            pathProto.__dirty = REDRAW_BIT | STYLE_CHANGED_BIT | SHAPE_CHANGED_BIT;
        })();
        return Path;
    }(Displayable));

    var DEFAULT_TSPAN_STYLE = defaults({
        strokeFirst: true,
        font: DEFAULT_FONT,
        x: 0,
        y: 0,
        textAlign: 'left',
        textBaseline: 'top',
        miterLimit: 2
    }, DEFAULT_PATH_STYLE);
    var TSpan = (function (_super) {
        __extends(TSpan, _super);
        function TSpan() {
            return _super !== null && _super.apply(this, arguments) || this;
        }
        TSpan.prototype.hasStroke = function () {
            var style = this.style;
            var stroke = style.stroke;
            return stroke != null && stroke !== 'none' && style.lineWidth > 0;
        };
        TSpan.prototype.hasFill = function () {
            var style = this.style;
            var fill = style.fill;
            return fill != null && fill !== 'none';
        };
        TSpan.prototype.createStyle = function (obj) {
            return createObject(DEFAULT_TSPAN_STYLE, obj);
        };
        TSpan.prototype.setBoundingRect = function (rect) {
            this._rect = rect;
        };
        TSpan.prototype.getBoundingRect = function () {
            var style = this.style;
            if (!this._rect) {
                var text = style.text;
                text != null ? (text += '') : (text = '');
                var rect = getBoundingRect(text, style.font, style.textAlign, style.textBaseline);
                rect.x += style.x || 0;
                rect.y += style.y || 0;
                if (this.hasStroke()) {
                    var w = style.lineWidth;
                    rect.x -= w / 2;
                    rect.y -= w / 2;
                    rect.width += w;
                    rect.height += w;
                }
                this._rect = rect;
            }
            return this._rect;
        };
        TSpan.initDefaultProps = (function () {
            var tspanProto = TSpan.prototype;
            tspanProto.dirtyRectTolerance = 10;
        })();
        return TSpan;
    }(Displayable));
    TSpan.prototype.type = 'tspan';

    var DEFAULT_IMAGE_STYLE = defaults({
        x: 0,
        y: 0
    }, DEFAULT_COMMON_STYLE);
    var DEFAULT_IMAGE_ANIMATION_PROPS = {
        style: defaults({
            x: true,
            y: true,
            width: true,
            height: true,
            sx: true,
            sy: true,
            sWidth: true,
            sHeight: true
        }, DEFAULT_COMMON_ANIMATION_PROPS.style)
    };
    function isImageLike(source) {
        return !!(source
            && typeof source !== 'string'
            && source.width && source.height);
    }
    var ZRImage = (function (_super) {
        __extends(ZRImage, _super);
        function ZRImage() {
            return _super !== null && _super.apply(this, arguments) || this;
        }
        ZRImage.prototype.createStyle = function (obj) {
            return createObject(DEFAULT_IMAGE_STYLE, obj);
        };
        ZRImage.prototype._getSize = function (dim) {
            var style = this.style;
            var size = style[dim];
            if (size != null) {
                return size;
            }
            var imageSource = isImageLike(style.image)
                ? style.image : this.__image;
            if (!imageSource) {
                return 0;
            }
            var otherDim = dim === 'width' ? 'height' : 'width';
            var otherDimSize = style[otherDim];
            if (otherDimSize == null) {
                return imageSource[dim];
            }
            else {
                return imageSource[dim] / imageSource[otherDim] * otherDimSize;
            }
        };
        ZRImage.prototype.getWidth = function () {
            return this._getSize('width');
        };
        ZRImage.prototype.getHeight = function () {
            return this._getSize('height');
        };
        ZRImage.prototype.getAnimationStyleProps = function () {
            return DEFAULT_IMAGE_ANIMATION_PROPS;
        };
        ZRImage.prototype.getBoundingRect = function () {
            var style = this.style;
            if (!this._rect) {
                this._rect = new BoundingRect(style.x || 0, style.y || 0, this.getWidth(), this.getHeight());
            }
            return this._rect;
        };
        return ZRImage;
    }(Displayable));
    ZRImage.prototype.type = 'image';

    function buildPath(ctx, shape) {
        var x = shape.x;
        var y = shape.y;
        var width = shape.width;
        var height = shape.height;
        var r = shape.r;
        var r1;
        var r2;
        var r3;
        var r4;
        if (width < 0) {
            x = x + width;
            width = -width;
        }
        if (height < 0) {
            y = y + height;
            height = -height;
        }
        if (typeof r === 'number') {
            r1 = r2 = r3 = r4 = r;
        }
        else if (r instanceof Array) {
            if (r.length === 1) {
                r1 = r2 = r3 = r4 = r[0];
            }
            else if (r.length === 2) {
                r1 = r3 = r[0];
                r2 = r4 = r[1];
            }
            else if (r.length === 3) {
                r1 = r[0];
                r2 = r4 = r[1];
                r3 = r[2];
            }
            else {
                r1 = r[0];
                r2 = r[1];
                r3 = r[2];
                r4 = r[3];
            }
        }
        else {
            r1 = r2 = r3 = r4 = 0;
        }
        var total;
        if (r1 + r2 > width) {
            total = r1 + r2;
            r1 *= width / total;
            r2 *= width / total;
        }
        if (r3 + r4 > width) {
            total = r3 + r4;
            r3 *= width / total;
            r4 *= width / total;
        }
        if (r2 + r3 > height) {
            total = r2 + r3;
            r2 *= height / total;
            r3 *= height / total;
        }
        if (r1 + r4 > height) {
            total = r1 + r4;
            r1 *= height / total;
            r4 *= height / total;
        }
        ctx.moveTo(x + r1, y);
        ctx.lineTo(x + width - r2, y);
        r2 !== 0 && ctx.arc(x + width - r2, y + r2, r2, -Math.PI / 2, 0);
        ctx.lineTo(x + width, y + height - r3);
        r3 !== 0 && ctx.arc(x + width - r3, y + height - r3, r3, 0, Math.PI / 2);
        ctx.lineTo(x + r4, y + height);
        r4 !== 0 && ctx.arc(x + r4, y + height - r4, r4, Math.PI / 2, Math.PI);
        ctx.lineTo(x, y + r1);
        r1 !== 0 && ctx.arc(x + r1, y + r1, r1, Math.PI, Math.PI * 1.5);
    }

    var round$1 = Math.round;
    function subPixelOptimizeLine(outputShape, inputShape, style) {
        if (!inputShape) {
            return;
        }
        var x1 = inputShape.x1;
        var x2 = inputShape.x2;
        var y1 = inputShape.y1;
        var y2 = inputShape.y2;
        outputShape.x1 = x1;
        outputShape.x2 = x2;
        outputShape.y1 = y1;
        outputShape.y2 = y2;
        var lineWidth = style && style.lineWidth;
        if (!lineWidth) {
            return outputShape;
        }
        if (round$1(x1 * 2) === round$1(x2 * 2)) {
            outputShape.x1 = outputShape.x2 = subPixelOptimize(x1, lineWidth, true);
        }
        if (round$1(y1 * 2) === round$1(y2 * 2)) {
            outputShape.y1 = outputShape.y2 = subPixelOptimize(y1, lineWidth, true);
        }
        return outputShape;
    }
    function subPixelOptimizeRect(outputShape, inputShape, style) {
        if (!inputShape) {
            return;
        }
        var originX = inputShape.x;
        var originY = inputShape.y;
        var originWidth = inputShape.width;
        var originHeight = inputShape.height;
        outputShape.x = originX;
        outputShape.y = originY;
        outputShape.width = originWidth;
        outputShape.height = originHeight;
        var lineWidth = style && style.lineWidth;
        if (!lineWidth) {
            return outputShape;
        }
        outputShape.x = subPixelOptimize(originX, lineWidth, true);
        outputShape.y = subPixelOptimize(originY, lineWidth, true);
        outputShape.width = Math.max(subPixelOptimize(originX + originWidth, lineWidth, false) - outputShape.x, originWidth === 0 ? 0 : 1);
        outputShape.height = Math.max(subPixelOptimize(originY + originHeight, lineWidth, false) - outputShape.y, originHeight === 0 ? 0 : 1);
        return outputShape;
    }
    function subPixelOptimize(position, lineWidth, positiveOrNegative) {
        if (!lineWidth) {
            return position;
        }
        var doubledPosition = round$1(position * 2);
        return (doubledPosition + round$1(lineWidth)) % 2 === 0
            ? doubledPosition / 2
            : (doubledPosition + (positiveOrNegative ? 1 : -1)) / 2;
    }

    var RectShape = (function () {
        function RectShape() {
            this.x = 0;
            this.y = 0;
            this.width = 0;
            this.height = 0;
        }
        return RectShape;
    }());
    var subPixelOptimizeOutputShape = {};
    var Rect = (function (_super) {
        __extends(Rect, _super);
        function Rect(opts) {
            return _super.call(this, opts) || this;
        }
        Rect.prototype.getDefaultShape = function () {
            return new RectShape();
        };
        Rect.prototype.buildPath = function (ctx, shape) {
            var x;
            var y;
            var width;
            var height;
            if (this.subPixelOptimize) {
                var optimizedShape = subPixelOptimizeRect(subPixelOptimizeOutputShape, shape, this.style);
                x = optimizedShape.x;
                y = optimizedShape.y;
                width = optimizedShape.width;
                height = optimizedShape.height;
                optimizedShape.r = shape.r;
                shape = optimizedShape;
            }
            else {
                x = shape.x;
                y = shape.y;
                width = shape.width;
                height = shape.height;
            }
            if (!shape.r) {
                ctx.rect(x, y, width, height);
            }
            else {
                buildPath(ctx, shape);
            }
        };
        Rect.prototype.isZeroArea = function () {
            return !this.shape.width || !this.shape.height;
        };
        return Rect;
    }(Path));
    Rect.prototype.type = 'rect';

    var DEFAULT_RICH_TEXT_COLOR = {
        fill: '#000'
    };
    var DEFAULT_STROKE_LINE_WIDTH = 2;
    var DEFAULT_TEXT_ANIMATION_PROPS = {
        style: defaults({
            fill: true,
            stroke: true,
            fillOpacity: true,
            strokeOpacity: true,
            lineWidth: true,
            fontSize: true,
            lineHeight: true,
            width: true,
            height: true,
            textShadowColor: true,
            textShadowBlur: true,
            textShadowOffsetX: true,
            textShadowOffsetY: true,
            backgroundColor: true,
            padding: true,
            borderColor: true,
            borderWidth: true,
            borderRadius: true
        }, DEFAULT_COMMON_ANIMATION_PROPS.style)
    };
    var ZRText = (function (_super) {
        __extends(ZRText, _super);
        function ZRText(opts) {
            var _this = _super.call(this) || this;
            _this.type = 'text';
            _this._children = [];
            _this._defaultStyle = DEFAULT_RICH_TEXT_COLOR;
            _this.attr(opts);
            return _this;
        }
        ZRText.prototype.childrenRef = function () {
            return this._children;
        };
        ZRText.prototype.update = function () {
            _super.prototype.update.call(this);
            if (this.styleChanged()) {
                this._updateSubTexts();
            }
            for (var i = 0; i < this._children.length; i++) {
                var child = this._children[i];
                child.zlevel = this.zlevel;
                child.z = this.z;
                child.z2 = this.z2;
                child.culling = this.culling;
                child.cursor = this.cursor;
                child.invisible = this.invisible;
            }
        };
        ZRText.prototype.updateTransform = function () {
            var innerTransformable = this.innerTransformable;
            if (innerTransformable) {
                innerTransformable.updateTransform();
                if (innerTransformable.transform) {
                    this.transform = innerTransformable.transform;
                }
            }
            else {
                _super.prototype.updateTransform.call(this);
            }
        };
        ZRText.prototype.getLocalTransform = function (m) {
            var innerTransformable = this.innerTransformable;
            return innerTransformable
                ? innerTransformable.getLocalTransform(m)
                : _super.prototype.getLocalTransform.call(this, m);
        };
        ZRText.prototype.getComputedTransform = function () {
            if (this.__hostTarget) {
                this.__hostTarget.getComputedTransform();
                this.__hostTarget.updateInnerText(true);
            }
            return _super.prototype.getComputedTransform.call(this);
        };
        ZRText.prototype._updateSubTexts = function () {
            this._childCursor = 0;
            normalizeTextStyle(this.style);
            this.style.rich
                ? this._updateRichTexts()
                : this._updatePlainTexts();
            this._children.length = this._childCursor;
            this.styleUpdated();
        };
        ZRText.prototype.addSelfToZr = function (zr) {
            _super.prototype.addSelfToZr.call(this, zr);
            for (var i = 0; i < this._children.length; i++) {
                this._children[i].__zr = zr;
            }
        };
        ZRText.prototype.removeSelfFromZr = function (zr) {
            _super.prototype.removeSelfFromZr.call(this, zr);
            for (var i = 0; i < this._children.length; i++) {
                this._children[i].__zr = null;
            }
        };
        ZRText.prototype.getBoundingRect = function () {
            if (this.styleChanged()) {
                this._updateSubTexts();
            }
            if (!this._rect) {
                var tmpRect = new BoundingRect(0, 0, 0, 0);
                var children = this._children;
                var tmpMat = [];
                var rect = null;
                for (var i = 0; i < children.length; i++) {
                    var child = children[i];
                    var childRect = child.getBoundingRect();
                    var transform = child.getLocalTransform(tmpMat);
                    if (transform) {
                        tmpRect.copy(childRect);
                        tmpRect.applyTransform(transform);
                        rect = rect || tmpRect.clone();
                        rect.union(tmpRect);
                    }
                    else {
                        rect = rect || childRect.clone();
                        rect.union(childRect);
                    }
                }
                this._rect = rect || tmpRect;
            }
            return this._rect;
        };
        ZRText.prototype.setDefaultTextStyle = function (defaultTextStyle) {
            this._defaultStyle = defaultTextStyle || DEFAULT_RICH_TEXT_COLOR;
        };
        ZRText.prototype.setTextContent = function (textContent) {
            if ("development" !== 'production') {
                throw new Error('Can\'t attach text on another text');
            }
        };
        ZRText.prototype._mergeStyle = function (targetStyle, sourceStyle) {
            if (!sourceStyle) {
                return targetStyle;
            }
            var sourceRich = sourceStyle.rich;
            var targetRich = targetStyle.rich || (sourceRich && {});
            extend(targetStyle, sourceStyle);
            if (sourceRich && targetRich) {
                this._mergeRich(targetRich, sourceRich);
                targetStyle.rich = targetRich;
            }
            else if (targetRich) {
                targetStyle.rich = targetRich;
            }
            return targetStyle;
        };
        ZRText.prototype._mergeRich = function (targetRich, sourceRich) {
            var richNames = keys(sourceRich);
            for (var i = 0; i < richNames.length; i++) {
                var richName = richNames[i];
                targetRich[richName] = targetRich[richName] || {};
                extend(targetRich[richName], sourceRich[richName]);
            }
        };
        ZRText.prototype.getAnimationStyleProps = function () {
            return DEFAULT_TEXT_ANIMATION_PROPS;
        };
        ZRText.prototype._getOrCreateChild = function (Ctor) {
            var child = this._children[this._childCursor];
            if (!child || !(child instanceof Ctor)) {
                child = new Ctor();
            }
            this._children[this._childCursor++] = child;
            child.__zr = this.__zr;
            child.parent = this;
            return child;
        };
        ZRText.prototype._updatePlainTexts = function () {
            var style = this.style;
            var textFont = style.font || DEFAULT_FONT;
            var textPadding = style.padding;
            var text = getStyleText(style);
            var contentBlock = parsePlainText(text, style);
            var needDrawBg = needDrawBackground(style);
            var bgColorDrawn = !!(style.backgroundColor);
            var outerHeight = contentBlock.outerHeight;
            var outerWidth = contentBlock.outerWidth;
            var contentWidth = contentBlock.contentWidth;
            var textLines = contentBlock.lines;
            var lineHeight = contentBlock.lineHeight;
            var defaultStyle = this._defaultStyle;
            var baseX = style.x || 0;
            var baseY = style.y || 0;
            var textAlign = style.align || defaultStyle.align || 'left';
            var verticalAlign = style.verticalAlign || defaultStyle.verticalAlign || 'top';
            var textX = baseX;
            var textY = adjustTextY$1(baseY, contentBlock.contentHeight, verticalAlign);
            if (needDrawBg || textPadding) {
                var boxX = adjustTextX(baseX, outerWidth, textAlign);
                var boxY = adjustTextY$1(baseY, outerHeight, verticalAlign);
                needDrawBg && this._renderBackground(style, style, boxX, boxY, outerWidth, outerHeight);
            }
            textY += lineHeight / 2;
            if (textPadding) {
                textX = getTextXForPadding(baseX, textAlign, textPadding);
                if (verticalAlign === 'top') {
                    textY += textPadding[0];
                }
                else if (verticalAlign === 'bottom') {
                    textY -= textPadding[2];
                }
            }
            var defaultLineWidth = 0;
            var useDefaultFill = false;
            var textFill = getFill('fill' in style
                ? style.fill
                : (useDefaultFill = true, defaultStyle.fill));
            var textStroke = getStroke('stroke' in style
                ? style.stroke
                : (!bgColorDrawn
                    && (!defaultStyle.autoStroke || useDefaultFill))
                    ? (defaultLineWidth = DEFAULT_STROKE_LINE_WIDTH, defaultStyle.stroke)
                    : null);
            var hasShadow = style.textShadowBlur > 0;
            var fixedBoundingRect = style.width != null
                && (style.overflow === 'truncate' || style.overflow === 'break' || style.overflow === 'breakAll');
            var calculatedLineHeight = contentBlock.calculatedLineHeight;
            for (var i = 0; i < textLines.length; i++) {
                var el = this._getOrCreateChild(TSpan);
                var subElStyle = el.createStyle();
                el.useStyle(subElStyle);
                subElStyle.text = textLines[i];
                subElStyle.x = textX;
                subElStyle.y = textY;
                if (textAlign) {
                    subElStyle.textAlign = textAlign;
                }
                subElStyle.textBaseline = 'middle';
                subElStyle.opacity = style.opacity;
                subElStyle.strokeFirst = true;
                if (hasShadow) {
                    subElStyle.shadowBlur = style.textShadowBlur || 0;
                    subElStyle.shadowColor = style.textShadowColor || 'transparent';
                    subElStyle.shadowOffsetX = style.textShadowOffsetX || 0;
                    subElStyle.shadowOffsetY = style.textShadowOffsetY || 0;
                }
                subElStyle.stroke = textStroke;
                subElStyle.fill = textFill;
                if (textStroke) {
                    subElStyle.lineWidth = style.lineWidth || defaultLineWidth;
                    subElStyle.lineDash = style.lineDash;
                    subElStyle.lineDashOffset = style.lineDashOffset || 0;
                }
                subElStyle.font = textFont;
                setSeparateFont(subElStyle, style);
                textY += lineHeight;
                if (fixedBoundingRect) {
                    el.setBoundingRect(new BoundingRect(adjustTextX(subElStyle.x, style.width, subElStyle.textAlign), adjustTextY$1(subElStyle.y, calculatedLineHeight, subElStyle.textBaseline), contentWidth, calculatedLineHeight));
                }
            }
        };
        ZRText.prototype._updateRichTexts = function () {
            var style = this.style;
            var text = getStyleText(style);
            var contentBlock = parseRichText(text, style);
            var contentWidth = contentBlock.width;
            var outerWidth = contentBlock.outerWidth;
            var outerHeight = contentBlock.outerHeight;
            var textPadding = style.padding;
            var baseX = style.x || 0;
            var baseY = style.y || 0;
            var defaultStyle = this._defaultStyle;
            var textAlign = style.align || defaultStyle.align;
            var verticalAlign = style.verticalAlign || defaultStyle.verticalAlign;
            var boxX = adjustTextX(baseX, outerWidth, textAlign);
            var boxY = adjustTextY$1(baseY, outerHeight, verticalAlign);
            var xLeft = boxX;
            var lineTop = boxY;
            if (textPadding) {
                xLeft += textPadding[3];
                lineTop += textPadding[0];
            }
            var xRight = xLeft + contentWidth;
            if (needDrawBackground(style)) {
                this._renderBackground(style, style, boxX, boxY, outerWidth, outerHeight);
            }
            var bgColorDrawn = !!(style.backgroundColor);
            for (var i = 0; i < contentBlock.lines.length; i++) {
                var line = contentBlock.lines[i];
                var tokens = line.tokens;
                var tokenCount = tokens.length;
                var lineHeight = line.lineHeight;
                var remainedWidth = line.width;
                var leftIndex = 0;
                var lineXLeft = xLeft;
                var lineXRight = xRight;
                var rightIndex = tokenCount - 1;
                var token = void 0;
                while (leftIndex < tokenCount
                    && (token = tokens[leftIndex], !token.align || token.align === 'left')) {
                    this._placeToken(token, style, lineHeight, lineTop, lineXLeft, 'left', bgColorDrawn);
                    remainedWidth -= token.width;
                    lineXLeft += token.width;
                    leftIndex++;
                }
                while (rightIndex >= 0
                    && (token = tokens[rightIndex], token.align === 'right')) {
                    this._placeToken(token, style, lineHeight, lineTop, lineXRight, 'right', bgColorDrawn);
                    remainedWidth -= token.width;
                    lineXRight -= token.width;
                    rightIndex--;
                }
                lineXLeft += (contentWidth - (lineXLeft - xLeft) - (xRight - lineXRight) - remainedWidth) / 2;
                while (leftIndex <= rightIndex) {
                    token = tokens[leftIndex];
                    this._placeToken(token, style, lineHeight, lineTop, lineXLeft + token.width / 2, 'center', bgColorDrawn);
                    lineXLeft += token.width;
                    leftIndex++;
                }
                lineTop += lineHeight;
            }
        };
        ZRText.prototype._placeToken = function (token, style, lineHeight, lineTop, x, textAlign, parentBgColorDrawn) {
            var tokenStyle = style.rich[token.styleName] || {};
            tokenStyle.text = token.text;
            var verticalAlign = token.verticalAlign;
            var y = lineTop + lineHeight / 2;
            if (verticalAlign === 'top') {
                y = lineTop + token.height / 2;
            }
            else if (verticalAlign === 'bottom') {
                y = lineTop + lineHeight - token.height / 2;
            }
            var needDrawBg = !token.isLineHolder && needDrawBackground(tokenStyle);
            needDrawBg && this._renderBackground(tokenStyle, style, textAlign === 'right'
                ? x - token.width
                : textAlign === 'center'
                    ? x - token.width / 2
                    : x, y - token.height / 2, token.width, token.height);
            var bgColorDrawn = !!tokenStyle.backgroundColor;
            var textPadding = token.textPadding;
            if (textPadding) {
                x = getTextXForPadding(x, textAlign, textPadding);
                y -= token.height / 2 - textPadding[0] - token.innerHeight / 2;
            }
            var el = this._getOrCreateChild(TSpan);
            var subElStyle = el.createStyle();
            el.useStyle(subElStyle);
            var defaultStyle = this._defaultStyle;
            var useDefaultFill = false;
            var defaultLineWidth = 0;
            var textFill = getFill('fill' in tokenStyle ? tokenStyle.fill
                : 'fill' in style ? style.fill
                    : (useDefaultFill = true, defaultStyle.fill));
            var textStroke = getStroke('stroke' in tokenStyle ? tokenStyle.stroke
                : 'stroke' in style ? style.stroke
                    : (!bgColorDrawn
                        && !parentBgColorDrawn
                        && (!defaultStyle.autoStroke || useDefaultFill)) ? (defaultLineWidth = DEFAULT_STROKE_LINE_WIDTH, defaultStyle.stroke)
                        : null);
            var hasShadow = tokenStyle.textShadowBlur > 0
                || style.textShadowBlur > 0;
            subElStyle.text = token.text;
            subElStyle.x = x;
            subElStyle.y = y;
            if (hasShadow) {
                subElStyle.shadowBlur = tokenStyle.textShadowBlur || style.textShadowBlur || 0;
                subElStyle.shadowColor = tokenStyle.textShadowColor || style.textShadowColor || 'transparent';
                subElStyle.shadowOffsetX = tokenStyle.textShadowOffsetX || style.textShadowOffsetX || 0;
                subElStyle.shadowOffsetY = tokenStyle.textShadowOffsetY || style.textShadowOffsetY || 0;
            }
            subElStyle.textAlign = textAlign;
            subElStyle.textBaseline = 'middle';
            subElStyle.font = token.font || DEFAULT_FONT;
            subElStyle.opacity = retrieve3(tokenStyle.opacity, style.opacity, 1);
            setSeparateFont(subElStyle, tokenStyle);
            if (textStroke) {
                subElStyle.lineWidth = retrieve3(tokenStyle.lineWidth, style.lineWidth, defaultLineWidth);
                subElStyle.lineDash = retrieve2(tokenStyle.lineDash, style.lineDash);
                subElStyle.lineDashOffset = style.lineDashOffset || 0;
                subElStyle.stroke = textStroke;
            }
            if (textFill) {
                subElStyle.fill = textFill;
            }
            var textWidth = token.contentWidth;
            var textHeight = token.contentHeight;
            el.setBoundingRect(new BoundingRect(adjustTextX(subElStyle.x, textWidth, subElStyle.textAlign), adjustTextY$1(subElStyle.y, textHeight, subElStyle.textBaseline), textWidth, textHeight));
        };
        ZRText.prototype._renderBackground = function (style, topStyle, x, y, width, height) {
            var textBackgroundColor = style.backgroundColor;
            var textBorderWidth = style.borderWidth;
            var textBorderColor = style.borderColor;
            var isImageBg = textBackgroundColor && textBackgroundColor.image;
            var isPlainOrGradientBg = textBackgroundColor && !isImageBg;
            var textBorderRadius = style.borderRadius;
            var self = this;
            var rectEl;
            var imgEl;
            if (isPlainOrGradientBg || style.lineHeight || (textBorderWidth && textBorderColor)) {
                rectEl = this._getOrCreateChild(Rect);
                rectEl.useStyle(rectEl.createStyle());
                rectEl.style.fill = null;
                var rectShape = rectEl.shape;
                rectShape.x = x;
                rectShape.y = y;
                rectShape.width = width;
                rectShape.height = height;
                rectShape.r = textBorderRadius;
                rectEl.dirtyShape();
            }
            if (isPlainOrGradientBg) {
                var rectStyle = rectEl.style;
                rectStyle.fill = textBackgroundColor || null;
                rectStyle.fillOpacity = retrieve2(style.fillOpacity, 1);
            }
            else if (isImageBg) {
                imgEl = this._getOrCreateChild(ZRImage);
                imgEl.onload = function () {
                    self.dirtyStyle();
                };
                var imgStyle = imgEl.style;
                imgStyle.image = textBackgroundColor.image;
                imgStyle.x = x;
                imgStyle.y = y;
                imgStyle.width = width;
                imgStyle.height = height;
            }
            if (textBorderWidth && textBorderColor) {
                var rectStyle = rectEl.style;
                rectStyle.lineWidth = textBorderWidth;
                rectStyle.stroke = textBorderColor;
                rectStyle.strokeOpacity = retrieve2(style.strokeOpacity, 1);
                rectStyle.lineDash = style.borderDash;
                rectStyle.lineDashOffset = style.borderDashOffset || 0;
                rectEl.strokeContainThreshold = 0;
                if (rectEl.hasFill() && rectEl.hasStroke()) {
                    rectStyle.strokeFirst = true;
                    rectStyle.lineWidth *= 2;
                }
            }
            var commonStyle = (rectEl || imgEl).style;
            commonStyle.shadowBlur = style.shadowBlur || 0;
            commonStyle.shadowColor = style.shadowColor || 'transparent';
            commonStyle.shadowOffsetX = style.shadowOffsetX || 0;
            commonStyle.shadowOffsetY = style.shadowOffsetY || 0;
            commonStyle.opacity = retrieve3(style.opacity, topStyle.opacity, 1);
        };
        ZRText.makeFont = function (style) {
            var font = '';
            if (hasSeparateFont(style)) {
                font = [
                    style.fontStyle,
                    style.fontWeight,
                    parseFontSize(style.fontSize),
                    style.fontFamily || 'sans-serif'
                ].join(' ');
            }
            return font && trim(font) || style.textFont || style.font;
        };
        return ZRText;
    }(Displayable));
    var VALID_TEXT_ALIGN = { left: true, right: 1, center: 1 };
    var VALID_TEXT_VERTICAL_ALIGN = { top: 1, bottom: 1, middle: 1 };
    var FONT_PARTS = ['fontStyle', 'fontWeight', 'fontSize', 'fontFamily'];
    function parseFontSize(fontSize) {
        if (typeof fontSize === 'string'
            && (fontSize.indexOf('px') !== -1
                || fontSize.indexOf('rem') !== -1
                || fontSize.indexOf('em') !== -1)) {
            return fontSize;
        }
        else if (!isNaN(+fontSize)) {
            return fontSize + 'px';
        }
        else {
            return DEFAULT_FONT_SIZE + 'px';
        }
    }
    function setSeparateFont(targetStyle, sourceStyle) {
        for (var i = 0; i < FONT_PARTS.length; i++) {
            var fontProp = FONT_PARTS[i];
            var val = sourceStyle[fontProp];
            if (val != null) {
                targetStyle[fontProp] = val;
            }
        }
    }
    function hasSeparateFont(style) {
        return style.fontSize != null || style.fontFamily || style.fontWeight;
    }
    function normalizeTextStyle(style) {
        normalizeStyle(style);
        each(style.rich, normalizeStyle);
        return style;
    }
    function normalizeStyle(style) {
        if (style) {
            style.font = ZRText.makeFont(style);
            var textAlign = style.align;
            textAlign === 'middle' && (textAlign = 'center');
            style.align = (textAlign == null || VALID_TEXT_ALIGN[textAlign]) ? textAlign : 'left';
            var verticalAlign = style.verticalAlign;
            verticalAlign === 'center' && (verticalAlign = 'middle');
            style.verticalAlign = (verticalAlign == null || VALID_TEXT_VERTICAL_ALIGN[verticalAlign]) ? verticalAlign : 'top';
            var textPadding = style.padding;
            if (textPadding) {
                style.padding = normalizeCssArray(style.padding);
            }
        }
    }
    function getStroke(stroke, lineWidth) {
        return (stroke == null || lineWidth <= 0 || stroke === 'transparent' || stroke === 'none')
            ? null
            : (stroke.image || stroke.colorStops)
                ? '#000'
                : stroke;
    }
    function getFill(fill) {
        return (fill == null || fill === 'none')
            ? null
            : (fill.image || fill.colorStops)
                ? '#000'
                : fill;
    }
    function getTextXForPadding(x, textAlign, textPadding) {
        return textAlign === 'right'
            ? (x - textPadding[1])
            : textAlign === 'center'
                ? (x + textPadding[3] / 2 - textPadding[1] / 2)
                : (x + textPadding[3]);
    }
    function getStyleText(style) {
        var text = style.text;
        text != null && (text += '');
        return text;
    }
    function needDrawBackground(style) {
        return !!(style.backgroundColor
            || style.lineHeight
            || (style.borderWidth && style.borderColor));
    }

    var getECData = makeInner();
    var setCommonECData = function (seriesIndex, dataType, dataIdx, el) {
      if (el) {
        var ecData = getECData(el); // Add data index and series index for indexing the data by element
        // Useful in tooltip

        ecData.dataIndex = dataIdx;
        ecData.dataType = dataType;
        ecData.seriesIndex = seriesIndex; // TODO: not store dataIndex on children.

        if (el.type === 'group') {
          el.traverse(function (child) {
            var childECData = getECData(child);
            childECData.seriesIndex = seriesIndex;
            childECData.dataIndex = dataIdx;
            childECData.dataType = dataType;
          });
        }
      }
    };

    var _highlightNextDigit = 1;
    var _highlightKeyMap = {};
    var getSavedStates = makeInner();
    var getComponentStates = makeInner();
    var HOVER_STATE_NORMAL = 0;
    var HOVER_STATE_BLUR = 1;
    var HOVER_STATE_EMPHASIS = 2;
    var SPECIAL_STATES = ['emphasis', 'blur', 'select'];
    var DISPLAY_STATES = ['normal', 'emphasis', 'blur', 'select'];
    var Z2_EMPHASIS_LIFT = 10;
    var Z2_SELECT_LIFT = 9;
    var HIGHLIGHT_ACTION_TYPE = 'highlight';
    var DOWNPLAY_ACTION_TYPE = 'downplay';
    var SELECT_ACTION_TYPE = 'select';
    var UNSELECT_ACTION_TYPE = 'unselect';
    var TOGGLE_SELECT_ACTION_TYPE = 'toggleSelect';

    function hasFillOrStroke(fillOrStroke) {
      return fillOrStroke != null && fillOrStroke !== 'none';
    } // Most lifted color are duplicated.


    var liftedColorCache = new LRU(100);

    function liftColor(color$1) {
      if (isString(color$1)) {
        var liftedColor = liftedColorCache.get(color$1);

        if (!liftedColor) {
          liftedColor = lift(color$1, -0.1);
          liftedColorCache.put(color$1, liftedColor);
        }

        return liftedColor;
      } else if (isGradientObject(color$1)) {
        var ret = extend({}, color$1);
        ret.colorStops = map(color$1.colorStops, function (stop) {
          return {
            offset: stop.offset,
            color: lift(stop.color, -0.1)
          };
        });
        return ret;
      } // Change nothing.


      return color$1;
    }

    function doChangeHoverState(el, stateName, hoverStateEnum) {
      if (el.onHoverStateChange && (el.hoverState || 0) !== hoverStateEnum) {
        el.onHoverStateChange(stateName);
      }

      el.hoverState = hoverStateEnum;
    }

    function singleEnterEmphasis(el) {
      // Only mark the flag.
      // States will be applied in the echarts.ts in next frame.
      doChangeHoverState(el, 'emphasis', HOVER_STATE_EMPHASIS);
    }

    function singleLeaveEmphasis(el) {
      // Only mark the flag.
      // States will be applied in the echarts.ts in next frame.
      if (el.hoverState === HOVER_STATE_EMPHASIS) {
        doChangeHoverState(el, 'normal', HOVER_STATE_NORMAL);
      }
    }

    function singleEnterBlur(el) {
      doChangeHoverState(el, 'blur', HOVER_STATE_BLUR);
    }

    function singleLeaveBlur(el) {
      if (el.hoverState === HOVER_STATE_BLUR) {
        doChangeHoverState(el, 'normal', HOVER_STATE_NORMAL);
      }
    }

    function singleEnterSelect(el) {
      el.selected = true;
    }

    function singleLeaveSelect(el) {
      el.selected = false;
    }

    function updateElementState(el, updater, commonParam) {
      updater(el, commonParam);
    }

    function traverseUpdateState(el, updater, commonParam) {
      updateElementState(el, updater, commonParam);
      el.isGroup && el.traverse(function (child) {
        updateElementState(child, updater, commonParam);
      });
    }

    function setStatesFlag(el, stateName) {
      switch (stateName) {
        case 'emphasis':
          el.hoverState = HOVER_STATE_EMPHASIS;
          break;

        case 'normal':
          el.hoverState = HOVER_STATE_NORMAL;
          break;

        case 'blur':
          el.hoverState = HOVER_STATE_BLUR;
          break;

        case 'select':
          el.selected = true;
      }
    }

    function getFromStateStyle(el, props, toStateName, defaultValue) {
      var style = el.style;
      var fromState = {};

      for (var i = 0; i < props.length; i++) {
        var propName = props[i];
        var val = style[propName];
        fromState[propName] = val == null ? defaultValue && defaultValue[propName] : val;
      }

      for (var i = 0; i < el.animators.length; i++) {
        var animator = el.animators[i];

        if (animator.__fromStateTransition // Don't consider the animation to emphasis state.
        && animator.__fromStateTransition.indexOf(toStateName) < 0 && animator.targetName === 'style') {
          animator.saveTo(fromState, props);
        }
      }

      return fromState;
    }

    function createEmphasisDefaultState(el, stateName, targetStates, state) {
      var hasSelect = targetStates && indexOf(targetStates, 'select') >= 0;
      var cloned = false;

      if (el instanceof Path) {
        var store = getSavedStates(el);
        var fromFill = hasSelect ? store.selectFill || store.normalFill : store.normalFill;
        var fromStroke = hasSelect ? store.selectStroke || store.normalStroke : store.normalStroke;

        if (hasFillOrStroke(fromFill) || hasFillOrStroke(fromStroke)) {
          state = state || {};
          var emphasisStyle = state.style || {}; // inherit case

          if (emphasisStyle.fill === 'inherit') {
            cloned = true;
            state = extend({}, state);
            emphasisStyle = extend({}, emphasisStyle);
            emphasisStyle.fill = fromFill;
          } // Apply default color lift
          else if (!hasFillOrStroke(emphasisStyle.fill) && hasFillOrStroke(fromFill)) {
              cloned = true; // Not modify the original value.

              state = extend({}, state);
              emphasisStyle = extend({}, emphasisStyle); // Already being applied 'emphasis'. DON'T lift color multiple times.

              emphasisStyle.fill = liftColor(fromFill);
            } // Not highlight stroke if fill has been highlighted.
            else if (!hasFillOrStroke(emphasisStyle.stroke) && hasFillOrStroke(fromStroke)) {
                if (!cloned) {
                  state = extend({}, state);
                  emphasisStyle = extend({}, emphasisStyle);
                }

                emphasisStyle.stroke = liftColor(fromStroke);
              }

          state.style = emphasisStyle;
        }
      }

      if (state) {
        // TODO Share with textContent?
        if (state.z2 == null) {
          if (!cloned) {
            state = extend({}, state);
          }

          var z2EmphasisLift = el.z2EmphasisLift;
          state.z2 = el.z2 + (z2EmphasisLift != null ? z2EmphasisLift : Z2_EMPHASIS_LIFT);
        }
      }

      return state;
    }

    function createSelectDefaultState(el, stateName, state) {
      // const hasSelect = indexOf(el.currentStates, stateName) >= 0;
      if (state) {
        // TODO Share with textContent?
        if (state.z2 == null) {
          state = extend({}, state);
          var z2SelectLift = el.z2SelectLift;
          state.z2 = el.z2 + (z2SelectLift != null ? z2SelectLift : Z2_SELECT_LIFT);
        }
      }

      return state;
    }

    function createBlurDefaultState(el, stateName, state) {
      var hasBlur = indexOf(el.currentStates, stateName) >= 0;
      var currentOpacity = el.style.opacity;
      var fromState = !hasBlur ? getFromStateStyle(el, ['opacity'], stateName, {
        opacity: 1
      }) : null;
      state = state || {};
      var blurStyle = state.style || {};

      if (blurStyle.opacity == null) {
        // clone state
        state = extend({}, state);
        blurStyle = extend({
          // Already being applied 'emphasis'. DON'T mul opacity multiple times.
          opacity: hasBlur ? currentOpacity : fromState.opacity * 0.1
        }, blurStyle);
        state.style = blurStyle;
      }

      return state;
    }

    function elementStateProxy(stateName, targetStates) {
      var state = this.states[stateName];

      if (this.style) {
        if (stateName === 'emphasis') {
          return createEmphasisDefaultState(this, stateName, targetStates, state);
        } else if (stateName === 'blur') {
          return createBlurDefaultState(this, stateName, state);
        } else if (stateName === 'select') {
          return createSelectDefaultState(this, stateName, state);
        }
      }

      return state;
    }
    /**
     * Set hover style (namely "emphasis style") of element.
     * @param el Should not be `zrender/graphic/Group`.
     * @param focus 'self' | 'selfInSeries' | 'series'
     */


    function setDefaultStateProxy(el) {
      el.stateProxy = elementStateProxy;
      var textContent = el.getTextContent();
      var textGuide = el.getTextGuideLine();

      if (textContent) {
        textContent.stateProxy = elementStateProxy;
      }

      if (textGuide) {
        textGuide.stateProxy = elementStateProxy;
      }
    }
    function enterEmphasisWhenMouseOver(el, e) {
      !shouldSilent(el, e) // "emphasis" event highlight has higher priority than mouse highlight.
      && !el.__highByOuter && traverseUpdateState(el, singleEnterEmphasis);
    }
    function leaveEmphasisWhenMouseOut(el, e) {
      !shouldSilent(el, e) // "emphasis" event highlight has higher priority than mouse highlight.
      && !el.__highByOuter && traverseUpdateState(el, singleLeaveEmphasis);
    }
    function enterEmphasis(el, highlightDigit) {
      el.__highByOuter |= 1 << (highlightDigit || 0);
      traverseUpdateState(el, singleEnterEmphasis);
    }
    function leaveEmphasis(el, highlightDigit) {
      !(el.__highByOuter &= ~(1 << (highlightDigit || 0))) && traverseUpdateState(el, singleLeaveEmphasis);
    }
    function enterBlur(el) {
      traverseUpdateState(el, singleEnterBlur);
    }
    function leaveBlur(el) {
      traverseUpdateState(el, singleLeaveBlur);
    }
    function enterSelect(el) {
      traverseUpdateState(el, singleEnterSelect);
    }
    function leaveSelect(el) {
      traverseUpdateState(el, singleLeaveSelect);
    }

    function shouldSilent(el, e) {
      return el.__highDownSilentOnTouch && e.zrByTouch;
    }

    function allLeaveBlur(api) {
      var model = api.getModel();
      var leaveBlurredSeries = [];
      var allComponentViews = [];
      model.eachComponent(function (componentType, componentModel) {
        var componentStates = getComponentStates(componentModel);
        var isSeries = componentType === 'series';
        var view = isSeries ? api.getViewOfSeriesModel(componentModel) : api.getViewOfComponentModel(componentModel);
        !isSeries && allComponentViews.push(view);

        if (componentStates.isBlured) {
          // Leave blur anyway
          view.group.traverse(function (child) {
            singleLeaveBlur(child);
          });
          isSeries && leaveBlurredSeries.push(componentModel);
        }

        componentStates.isBlured = false;
      });
      each(allComponentViews, function (view) {
        if (view && view.toggleBlurSeries) {
          view.toggleBlurSeries(leaveBlurredSeries, false, model);
        }
      });
    }
    function blurSeries(targetSeriesIndex, focus, blurScope, api) {
      var ecModel = api.getModel();
      blurScope = blurScope || 'coordinateSystem';

      function leaveBlurOfIndices(data, dataIndices) {
        for (var i = 0; i < dataIndices.length; i++) {
          var itemEl = data.getItemGraphicEl(dataIndices[i]);
          itemEl && leaveBlur(itemEl);
        }
      }

      if (targetSeriesIndex == null) {
        return;
      }

      if (!focus || focus === 'none') {
        return;
      }

      var targetSeriesModel = ecModel.getSeriesByIndex(targetSeriesIndex);
      var targetCoordSys = targetSeriesModel.coordinateSystem;

      if (targetCoordSys && targetCoordSys.master) {
        targetCoordSys = targetCoordSys.master;
      }

      var blurredSeries = [];
      ecModel.eachSeries(function (seriesModel) {
        var sameSeries = targetSeriesModel === seriesModel;
        var coordSys = seriesModel.coordinateSystem;

        if (coordSys && coordSys.master) {
          coordSys = coordSys.master;
        }

        var sameCoordSys = coordSys && targetCoordSys ? coordSys === targetCoordSys : sameSeries; // If there is no coordinate system. use sameSeries instead.

        if (!( // Not blur other series if blurScope series
        blurScope === 'series' && !sameSeries // Not blur other coordinate system if blurScope is coordinateSystem
        || blurScope === 'coordinateSystem' && !sameCoordSys // Not blur self series if focus is series.
        || focus === 'series' && sameSeries // TODO blurScope: coordinate system
        )) {
          var view = api.getViewOfSeriesModel(seriesModel);
          view.group.traverse(function (child) {
            // For the elements that have been triggered by other components,
            // and are still required to be highlighted,
            // because the current is directly forced to blur the element,
            // it will cause the focus self to be unable to highlight, so skip the blur of this element.
            if (child.__highByOuter && sameSeries && focus === 'self') {
              return;
            }

            singleEnterBlur(child);
          });

          if (isArrayLike(focus)) {
            leaveBlurOfIndices(seriesModel.getData(), focus);
          } else if (isObject(focus)) {
            var dataTypes = keys(focus);

            for (var d = 0; d < dataTypes.length; d++) {
              leaveBlurOfIndices(seriesModel.getData(dataTypes[d]), focus[dataTypes[d]]);
            }
          }

          blurredSeries.push(seriesModel);
          getComponentStates(seriesModel).isBlured = true;
        }
      });
      ecModel.eachComponent(function (componentType, componentModel) {
        if (componentType === 'series') {
          return;
        }

        var view = api.getViewOfComponentModel(componentModel);

        if (view && view.toggleBlurSeries) {
          view.toggleBlurSeries(blurredSeries, true, ecModel);
        }
      });
    }
    function blurComponent(componentMainType, componentIndex, api) {
      if (componentMainType == null || componentIndex == null) {
        return;
      }

      var componentModel = api.getModel().getComponent(componentMainType, componentIndex);

      if (!componentModel) {
        return;
      }

      getComponentStates(componentModel).isBlured = true;
      var view = api.getViewOfComponentModel(componentModel);

      if (!view || !view.focusBlurEnabled) {
        return;
      }

      view.group.traverse(function (child) {
        singleEnterBlur(child);
      });
    }
    function blurSeriesFromHighlightPayload(seriesModel, payload, api) {
      var seriesIndex = seriesModel.seriesIndex;
      var data = seriesModel.getData(payload.dataType);

      if (!data) {
        if ("development" !== 'production') {
          error("Unknown dataType " + payload.dataType);
        }

        return;
      }

      var dataIndex = queryDataIndex(data, payload); // Pick the first one if there is multiple/none exists.

      dataIndex = (isArray(dataIndex) ? dataIndex[0] : dataIndex) || 0;
      var el = data.getItemGraphicEl(dataIndex);

      if (!el) {
        var count = data.count();
        var current = 0; // If data on dataIndex is NaN.

        while (!el && current < count) {
          el = data.getItemGraphicEl(current++);
        }
      }

      if (el) {
        var ecData = getECData(el);
        blurSeries(seriesIndex, ecData.focus, ecData.blurScope, api);
      } else {
        // If there is no element put on the data. Try getting it from raw option
        // TODO Should put it on seriesModel?
        var focus_1 = seriesModel.get(['emphasis', 'focus']);
        var blurScope = seriesModel.get(['emphasis', 'blurScope']);

        if (focus_1 != null) {
          blurSeries(seriesIndex, focus_1, blurScope, api);
        }
      }
    }
    function findComponentHighDownDispatchers(componentMainType, componentIndex, name, api) {
      var ret = {
        focusSelf: false,
        dispatchers: null
      };

      if (componentMainType == null || componentMainType === 'series' || componentIndex == null || name == null) {
        return ret;
      }

      var componentModel = api.getModel().getComponent(componentMainType, componentIndex);

      if (!componentModel) {
        return ret;
      }

      var view = api.getViewOfComponentModel(componentModel);

      if (!view || !view.findHighDownDispatchers) {
        return ret;
      }

      var dispatchers = view.findHighDownDispatchers(name); // At presnet, the component (like Geo) only blur inside itself.
      // So we do not use `blurScope` in component.

      var focusSelf;

      for (var i = 0; i < dispatchers.length; i++) {
        if ("development" !== 'production' && !isHighDownDispatcher(dispatchers[i])) {
          error('param should be highDownDispatcher');
        }

        if (getECData(dispatchers[i]).focus === 'self') {
          focusSelf = true;
          break;
        }
      }

      return {
        focusSelf: focusSelf,
        dispatchers: dispatchers
      };
    }
    function handleGlobalMouseOverForHighDown(dispatcher, e, api) {
      if ("development" !== 'production' && !isHighDownDispatcher(dispatcher)) {
        error('param should be highDownDispatcher');
      }

      var ecData = getECData(dispatcher);

      var _a = findComponentHighDownDispatchers(ecData.componentMainType, ecData.componentIndex, ecData.componentHighDownName, api),
          dispatchers = _a.dispatchers,
          focusSelf = _a.focusSelf; // If `findHighDownDispatchers` is supported on the component,
      // highlight/downplay elements with the same name.


      if (dispatchers) {
        if (focusSelf) {
          blurComponent(ecData.componentMainType, ecData.componentIndex, api);
        }

        each(dispatchers, function (dispatcher) {
          return enterEmphasisWhenMouseOver(dispatcher, e);
        });
      } else {
        // Try blur all in the related series. Then emphasis the hoverred.
        // TODO. progressive mode.
        blurSeries(ecData.seriesIndex, ecData.focus, ecData.blurScope, api);

        if (ecData.focus === 'self') {
          blurComponent(ecData.componentMainType, ecData.componentIndex, api);
        } // Other than series, component that not support `findHighDownDispatcher` will
        // also use it. But in this case, highlight/downplay are only supported in
        // mouse hover but not in dispatchAction.


        enterEmphasisWhenMouseOver(dispatcher, e);
      }
    }
    function handleGlobalMouseOutForHighDown(dispatcher, e, api) {
      if ("development" !== 'production' && !isHighDownDispatcher(dispatcher)) {
        error('param should be highDownDispatcher');
      }

      allLeaveBlur(api);
      var ecData = getECData(dispatcher);
      var dispatchers = findComponentHighDownDispatchers(ecData.componentMainType, ecData.componentIndex, ecData.componentHighDownName, api).dispatchers;

      if (dispatchers) {
        each(dispatchers, function (dispatcher) {
          return leaveEmphasisWhenMouseOut(dispatcher, e);
        });
      } else {
        leaveEmphasisWhenMouseOut(dispatcher, e);
      }
    }
    function toggleSelectionFromPayload(seriesModel, payload, api) {
      if (!isSelectChangePayload(payload)) {
        return;
      }

      var dataType = payload.dataType;
      var data = seriesModel.getData(dataType);
      var dataIndex = queryDataIndex(data, payload);

      if (!isArray(dataIndex)) {
        dataIndex = [dataIndex];
      }

      seriesModel[payload.type === TOGGLE_SELECT_ACTION_TYPE ? 'toggleSelect' : payload.type === SELECT_ACTION_TYPE ? 'select' : 'unselect'](dataIndex, dataType);
    }
    function updateSeriesElementSelection(seriesModel) {
      var allData = seriesModel.getAllData();
      each(allData, function (_a) {
        var data = _a.data,
            type = _a.type;
        data.eachItemGraphicEl(function (el, idx) {
          seriesModel.isSelected(idx, type) ? enterSelect(el) : leaveSelect(el);
        });
      });
    }
    function getAllSelectedIndices(ecModel) {
      var ret = [];
      ecModel.eachSeries(function (seriesModel) {
        var allData = seriesModel.getAllData();
        each(allData, function (_a) {
          var data = _a.data,
              type = _a.type;
          var dataIndices = seriesModel.getSelectedDataIndices();

          if (dataIndices.length > 0) {
            var item = {
              dataIndex: dataIndices,
              seriesIndex: seriesModel.seriesIndex
            };

            if (type != null) {
              item.dataType = type;
            }

            ret.push(item);
          }
        });
      });
      return ret;
    }
    /**
     * Enable the function that mouseover will trigger the emphasis state.
     *
     * NOTE:
     * This function should be used on the element with dataIndex, seriesIndex.
     *
     */

    function enableHoverEmphasis(el, focus, blurScope) {
      setAsHighDownDispatcher(el, true);
      traverseUpdateState(el, setDefaultStateProxy);
      enableHoverFocus(el, focus, blurScope);
    }
    function disableHoverEmphasis(el) {
      setAsHighDownDispatcher(el, false);
    }
    function toggleHoverEmphasis(el, focus, blurScope, isDisabled) {
      isDisabled ? disableHoverEmphasis(el) : enableHoverEmphasis(el, focus, blurScope);
    }
    function enableHoverFocus(el, focus, blurScope) {
      var ecData = getECData(el);

      if (focus != null) {
        // TODO dataIndex may be set after this function. This check is not useful.
        // if (ecData.dataIndex == null) {
        //     if (__DEV__) {
        //         console.warn('focus can only been set on element with dataIndex');
        //     }
        // }
        // else {
        ecData.focus = focus;
        ecData.blurScope = blurScope; // }
      } else if (ecData.focus) {
        ecData.focus = null;
      }
    }
    var OTHER_STATES = ['emphasis', 'blur', 'select'];
    var defaultStyleGetterMap = {
      itemStyle: 'getItemStyle',
      lineStyle: 'getLineStyle',
      areaStyle: 'getAreaStyle'
    };
    /**
     * Set emphasis/blur/selected states of element.
     */

    function setStatesStylesFromModel(el, itemModel, styleType, // default itemStyle
    getter) {
      styleType = styleType || 'itemStyle';

      for (var i = 0; i < OTHER_STATES.length; i++) {
        var stateName = OTHER_STATES[i];
        var model = itemModel.getModel([stateName, styleType]);
        var state = el.ensureState(stateName); // Let it throw error if getterType is not found.

        state.style = getter ? getter(model) : model[defaultStyleGetterMap[styleType]]();
      }
    }
    /**
     *
     * Set element as highlight / downplay dispatcher.
     * It will be checked when element received mouseover event or from highlight action.
     * It's in change of all highlight/downplay behavior of it's children.
     *
     * @param el
     * @param el.highDownSilentOnTouch
     *        In touch device, mouseover event will be trigger on touchstart event
     *        (see module:zrender/dom/HandlerProxy). By this mechanism, we can
     *        conveniently use hoverStyle when tap on touch screen without additional
     *        code for compatibility.
     *        But if the chart/component has select feature, which usually also use
     *        hoverStyle, there might be conflict between 'select-highlight' and
     *        'hover-highlight' especially when roam is enabled (see geo for example).
     *        In this case, `highDownSilentOnTouch` should be used to disable
     *        hover-highlight on touch device.
     * @param asDispatcher If `false`, do not set as "highDownDispatcher".
     */

    function setAsHighDownDispatcher(el, asDispatcher) {
      var disable = asDispatcher === false;
      var extendedEl = el; // Make `highDownSilentOnTouch` and `onStateChange` only work after
      // `setAsHighDownDispatcher` called. Avoid it is modified by user unexpectedly.

      if (el.highDownSilentOnTouch) {
        extendedEl.__highDownSilentOnTouch = el.highDownSilentOnTouch;
      } // Simple optimize, since this method might be
      // called for each elements of a group in some cases.


      if (!disable || extendedEl.__highDownDispatcher) {
        // Emphasis, normal can be triggered manually by API or other components like hover link.
        // el[method]('emphasis', onElementEmphasisEvent)[method]('normal', onElementNormalEvent);
        // Also keep previous record.
        extendedEl.__highByOuter = extendedEl.__highByOuter || 0;
        extendedEl.__highDownDispatcher = !disable;
      }
    }
    function isHighDownDispatcher(el) {
      return !!(el && el.__highDownDispatcher);
    }
    /**
     * Support highlight/downplay record on each elements.
     * For the case: hover highlight/downplay (legend, visualMap, ...) and
     * user triggered highlight/downplay should not conflict.
     * Only all of the highlightDigit cleared, return to normal.
     * @param {string} highlightKey
     * @return {number} highlightDigit
     */

    function getHighlightDigit(highlightKey) {
      var highlightDigit = _highlightKeyMap[highlightKey];

      if (highlightDigit == null && _highlightNextDigit <= 32) {
        highlightDigit = _highlightKeyMap[highlightKey] = _highlightNextDigit++;
      }

      return highlightDigit;
    }
    function isSelectChangePayload(payload) {
      var payloadType = payload.type;
      return payloadType === SELECT_ACTION_TYPE || payloadType === UNSELECT_ACTION_TYPE || payloadType === TOGGLE_SELECT_ACTION_TYPE;
    }
    function isHighDownPayload(payload) {
      var payloadType = payload.type;
      return payloadType === HIGHLIGHT_ACTION_TYPE || payloadType === DOWNPLAY_ACTION_TYPE;
    }
    function savePathStates(el) {
      var store = getSavedStates(el);
      store.normalFill = el.style.fill;
      store.normalStroke = el.style.stroke;
      var selectState = el.states.select || {};
      store.selectFill = selectState.style && selectState.style.fill || null;
      store.selectStroke = selectState.style && selectState.style.stroke || null;
    }

    var CMD$2 = PathProxy.CMD;
    var points = [[], [], []];
    var mathSqrt$1 = Math.sqrt;
    var mathAtan2 = Math.atan2;
    function transformPath(path, m) {
        if (!m) {
            return;
        }
        var data = path.data;
        var len = path.len();
        var cmd;
        var nPoint;
        var i;
        var j;
        var k;
        var p;
        var M = CMD$2.M;
        var C = CMD$2.C;
        var L = CMD$2.L;
        var R = CMD$2.R;
        var A = CMD$2.A;
        var Q = CMD$2.Q;
        for (i = 0, j = 0; i < len;) {
            cmd = data[i++];
            j = i;
            nPoint = 0;
            switch (cmd) {
                case M:
                    nPoint = 1;
                    break;
                case L:
                    nPoint = 1;
                    break;
                case C:
                    nPoint = 3;
                    break;
                case Q:
                    nPoint = 2;
                    break;
                case A:
                    var x = m[4];
                    var y = m[5];
                    var sx = mathSqrt$1(m[0] * m[0] + m[1] * m[1]);
                    var sy = mathSqrt$1(m[2] * m[2] + m[3] * m[3]);
                    var angle = mathAtan2(-m[1] / sy, m[0] / sx);
                    data[i] *= sx;
                    data[i++] += x;
                    data[i] *= sy;
                    data[i++] += y;
                    data[i++] *= sx;
                    data[i++] *= sy;
                    data[i++] += angle;
                    data[i++] += angle;
                    i += 2;
                    j = i;
                    break;
                case R:
                    p[0] = data[i++];
                    p[1] = data[i++];
                    applyTransform(p, p, m);
                    data[j++] = p[0];
                    data[j++] = p[1];
                    p[0] += data[i++];
                    p[1] += data[i++];
                    applyTransform(p, p, m);
                    data[j++] = p[0];
                    data[j++] = p[1];
            }
            for (k = 0; k < nPoint; k++) {
                var p_1 = points[k];
                p_1[0] = data[i++];
                p_1[1] = data[i++];
                applyTransform(p_1, p_1, m);
                data[j++] = p_1[0];
                data[j++] = p_1[1];
            }
        }
        path.increaseVersion();
    }

    var mathSqrt$2 = Math.sqrt;
    var mathSin$2 = Math.sin;
    var mathCos$2 = Math.cos;
    var PI$1 = Math.PI;
    function vMag(v) {
        return Math.sqrt(v[0] * v[0] + v[1] * v[1]);
    }
    function vRatio(u, v) {
        return (u[0] * v[0] + u[1] * v[1]) / (vMag(u) * vMag(v));
    }
    function vAngle(u, v) {
        return (u[0] * v[1] < u[1] * v[0] ? -1 : 1)
            * Math.acos(vRatio(u, v));
    }
    function processArc(x1, y1, x2, y2, fa, fs, rx, ry, psiDeg, cmd, path) {
        var psi = psiDeg * (PI$1 / 180.0);
        var xp = mathCos$2(psi) * (x1 - x2) / 2.0
            + mathSin$2(psi) * (y1 - y2) / 2.0;
        var yp = -1 * mathSin$2(psi) * (x1 - x2) / 2.0
            + mathCos$2(psi) * (y1 - y2) / 2.0;
        var lambda = (xp * xp) / (rx * rx) + (yp * yp) / (ry * ry);
        if (lambda > 1) {
            rx *= mathSqrt$2(lambda);
            ry *= mathSqrt$2(lambda);
        }
        var f = (fa === fs ? -1 : 1)
            * mathSqrt$2((((rx * rx) * (ry * ry))
                - ((rx * rx) * (yp * yp))
                - ((ry * ry) * (xp * xp))) / ((rx * rx) * (yp * yp)
                + (ry * ry) * (xp * xp))) || 0;
        var cxp = f * rx * yp / ry;
        var cyp = f * -ry * xp / rx;
        var cx = (x1 + x2) / 2.0
            + mathCos$2(psi) * cxp
            - mathSin$2(psi) * cyp;
        var cy = (y1 + y2) / 2.0
            + mathSin$2(psi) * cxp
            + mathCos$2(psi) * cyp;
        var theta = vAngle([1, 0], [(xp - cxp) / rx, (yp - cyp) / ry]);
        var u = [(xp - cxp) / rx, (yp - cyp) / ry];
        var v = [(-1 * xp - cxp) / rx, (-1 * yp - cyp) / ry];
        var dTheta = vAngle(u, v);
        if (vRatio(u, v) <= -1) {
            dTheta = PI$1;
        }
        if (vRatio(u, v) >= 1) {
            dTheta = 0;
        }
        if (dTheta < 0) {
            var n = Math.round(dTheta / PI$1 * 1e6) / 1e6;
            dTheta = PI$1 * 2 + (n % 2) * PI$1;
        }
        path.addData(cmd, cx, cy, rx, ry, theta, dTheta, psi, fs);
    }
    var commandReg = /([mlvhzcqtsa])([^mlvhzcqtsa]*)/ig;
    var numberReg = /-?([0-9]*\.)?[0-9]+([eE]-?[0-9]+)?/g;
    function createPathProxyFromString(data) {
        var path = new PathProxy();
        if (!data) {
            return path;
        }
        var cpx = 0;
        var cpy = 0;
        var subpathX = cpx;
        var subpathY = cpy;
        var prevCmd;
        var CMD = PathProxy.CMD;
        var cmdList = data.match(commandReg);
        if (!cmdList) {
            return path;
        }
        for (var l = 0; l < cmdList.length; l++) {
            var cmdText = cmdList[l];
            var cmdStr = cmdText.charAt(0);
            var cmd = void 0;
            var p = cmdText.match(numberReg) || [];
            var pLen = p.length;
            for (var i = 0; i < pLen; i++) {
                p[i] = parseFloat(p[i]);
            }
            var off = 0;
            while (off < pLen) {
                var ctlPtx = void 0;
                var ctlPty = void 0;
                var rx = void 0;
                var ry = void 0;
                var psi = void 0;
                var fa = void 0;
                var fs = void 0;
                var x1 = cpx;
                var y1 = cpy;
                var len = void 0;
                var pathData = void 0;
                switch (cmdStr) {
                    case 'l':
                        cpx += p[off++];
                        cpy += p[off++];
                        cmd = CMD.L;
                        path.addData(cmd, cpx, cpy);
                        break;
                    case 'L':
                        cpx = p[off++];
                        cpy = p[off++];
                        cmd = CMD.L;
                        path.addData(cmd, cpx, cpy);
                        break;
                    case 'm':
                        cpx += p[off++];
                        cpy += p[off++];
                        cmd = CMD.M;
                        path.addData(cmd, cpx, cpy);
                        subpathX = cpx;
                        subpathY = cpy;
                        cmdStr = 'l';
                        break;
                    case 'M':
                        cpx = p[off++];
                        cpy = p[off++];
                        cmd = CMD.M;
                        path.addData(cmd, cpx, cpy);
                        subpathX = cpx;
                        subpathY = cpy;
                        cmdStr = 'L';
                        break;
                    case 'h':
                        cpx += p[off++];
                        cmd = CMD.L;
                        path.addData(cmd, cpx, cpy);
                        break;
                    case 'H':
                        cpx = p[off++];
                        cmd = CMD.L;
                        path.addData(cmd, cpx, cpy);
                        break;
                    case 'v':
                        cpy += p[off++];
                        cmd = CMD.L;
                        path.addData(cmd, cpx, cpy);
                        break;
                    case 'V':
                        cpy = p[off++];
                        cmd = CMD.L;
                        path.addData(cmd, cpx, cpy);
                        break;
                    case 'C':
                        cmd = CMD.C;
                        path.addData(cmd, p[off++], p[off++], p[off++], p[off++], p[off++], p[off++]);
                        cpx = p[off - 2];
                        cpy = p[off - 1];
                        break;
                    case 'c':
                        cmd = CMD.C;
                        path.addData(cmd, p[off++] + cpx, p[off++] + cpy, p[off++] + cpx, p[off++] + cpy, p[off++] + cpx, p[off++] + cpy);
                        cpx += p[off - 2];
                        cpy += p[off - 1];
                        break;
                    case 'S':
                        ctlPtx = cpx;
                        ctlPty = cpy;
                        len = path.len();
                        pathData = path.data;
                        if (prevCmd === CMD.C) {
                            ctlPtx += cpx - pathData[len - 4];
                            ctlPty += cpy - pathData[len - 3];
                        }
                        cmd = CMD.C;
                        x1 = p[off++];
                        y1 = p[off++];
                        cpx = p[off++];
                        cpy = p[off++];
                        path.addData(cmd, ctlPtx, ctlPty, x1, y1, cpx, cpy);
                        break;
                    case 's':
                        ctlPtx = cpx;
                        ctlPty = cpy;
                        len = path.len();
                        pathData = path.data;
                        if (prevCmd === CMD.C) {
                            ctlPtx += cpx - pathData[len - 4];
                            ctlPty += cpy - pathData[len - 3];
                        }
                        cmd = CMD.C;
                        x1 = cpx + p[off++];
                        y1 = cpy + p[off++];
                        cpx += p[off++];
                        cpy += p[off++];
                        path.addData(cmd, ctlPtx, ctlPty, x1, y1, cpx, cpy);
                        break;
                    case 'Q':
                        x1 = p[off++];
                        y1 = p[off++];
                        cpx = p[off++];
                        cpy = p[off++];
                        cmd = CMD.Q;
                        path.addData(cmd, x1, y1, cpx, cpy);
                        break;
                    case 'q':
                        x1 = p[off++] + cpx;
                        y1 = p[off++] + cpy;
                        cpx += p[off++];
                        cpy += p[off++];
                        cmd = CMD.Q;
                        path.addData(cmd, x1, y1, cpx, cpy);
                        break;
                    case 'T':
                        ctlPtx = cpx;
                        ctlPty = cpy;
                        len = path.len();
                        pathData = path.data;
                        if (prevCmd === CMD.Q) {
                            ctlPtx += cpx - pathData[len - 4];
                            ctlPty += cpy - pathData[len - 3];
                        }
                        cpx = p[off++];
                        cpy = p[off++];
                        cmd = CMD.Q;
                        path.addData(cmd, ctlPtx, ctlPty, cpx, cpy);
                        break;
                    case 't':
                        ctlPtx = cpx;
                        ctlPty = cpy;
                        len = path.len();
                        pathData = path.data;
                        if (prevCmd === CMD.Q) {
                            ctlPtx += cpx - pathData[len - 4];
                            ctlPty += cpy - pathData[len - 3];
                        }
                        cpx += p[off++];
                        cpy += p[off++];
                        cmd = CMD.Q;
                        path.addData(cmd, ctlPtx, ctlPty, cpx, cpy);
                        break;
                    case 'A':
                        rx = p[off++];
                        ry = p[off++];
                        psi = p[off++];
                        fa = p[off++];
                        fs = p[off++];
                        x1 = cpx, y1 = cpy;
                        cpx = p[off++];
                        cpy = p[off++];
                        cmd = CMD.A;
                        processArc(x1, y1, cpx, cpy, fa, fs, rx, ry, psi, cmd, path);
                        break;
                    case 'a':
                        rx = p[off++];
                        ry = p[off++];
                        psi = p[off++];
                        fa = p[off++];
                        fs = p[off++];
                        x1 = cpx, y1 = cpy;
                        cpx += p[off++];
                        cpy += p[off++];
                        cmd = CMD.A;
                        processArc(x1, y1, cpx, cpy, fa, fs, rx, ry, psi, cmd, path);
                        break;
                }
            }
            if (cmdStr === 'z' || cmdStr === 'Z') {
                cmd = CMD.Z;
                path.addData(cmd);
                cpx = subpathX;
                cpy = subpathY;
            }
            prevCmd = cmd;
        }
        path.toStatic();
        return path;
    }
    var SVGPath = (function (_super) {
        __extends(SVGPath, _super);
        function SVGPath() {
            return _super !== null && _super.apply(this, arguments) || this;
        }
        SVGPath.prototype.applyTransform = function (m) { };
        return SVGPath;
    }(Path));
    function isPathProxy(path) {
        return path.setData != null;
    }
    function createPathOptions(str, opts) {
        var pathProxy = createPathProxyFromString(str);
        var innerOpts = extend({}, opts);
        innerOpts.buildPath = function (path) {
            if (isPathProxy(path)) {
                path.setData(pathProxy.data);
                var ctx = path.getContext();
                if (ctx) {
                    path.rebuildPath(ctx, 1);
                }
            }
            else {
                var ctx = path;
                pathProxy.rebuildPath(ctx, 1);
            }
        };
        innerOpts.applyTransform = function (m) {
            transformPath(pathProxy, m);
            this.dirtyShape();
        };
        return innerOpts;
    }
    function createFromString(str, opts) {
        return new SVGPath(createPathOptions(str, opts));
    }
    function extendFromString(str, defaultOpts) {
        var innerOpts = createPathOptions(str, defaultOpts);
        var Sub = (function (_super) {
            __extends(Sub, _super);
            function Sub(opts) {
                var _this = _super.call(this, opts) || this;
                _this.applyTransform = innerOpts.applyTransform;
                _this.buildPath = innerOpts.buildPath;
                return _this;
            }
            return Sub;
        }(SVGPath));
        return Sub;
    }
    function mergePath(pathEls, opts) {
        var pathList = [];
        var len = pathEls.length;
        for (var i = 0; i < len; i++) {
            var pathEl = pathEls[i];
            pathList.push(pathEl.getUpdatedPathProxy(true));
        }
        var pathBundle = new Path(opts);
        pathBundle.createPathProxy();
        pathBundle.buildPath = function (path) {
            if (isPathProxy(path)) {
                path.appendPath(pathList);
                var ctx = path.getContext();
                if (ctx) {
                    path.rebuildPath(ctx, 1);
                }
            }
        };
        return pathBundle;
    }

    var CircleShape = (function () {
        function CircleShape() {
            this.cx = 0;
            this.cy = 0;
            this.r = 0;
        }
        return CircleShape;
    }());
    var Circle = (function (_super) {
        __extends(Circle, _super);
        function Circle(opts) {
            return _super.call(this, opts) || this;
        }
        Circle.prototype.getDefaultShape = function () {
            return new CircleShape();
        };
        Circle.prototype.buildPath = function (ctx, shape) {
            ctx.moveTo(shape.cx + shape.r, shape.cy);
            ctx.arc(shape.cx, shape.cy, shape.r, 0, Math.PI * 2);
        };
        return Circle;
    }(Path));
    Circle.prototype.type = 'circle';

    var EllipseShape = (function () {
        function EllipseShape() {
            this.cx = 0;
            this.cy = 0;
            this.rx = 0;
            this.ry = 0;
        }
        return EllipseShape;
    }());
    var Ellipse = (function (_super) {
        __extends(Ellipse, _super);
        function Ellipse(opts) {
            return _super.call(this, opts) || this;
        }
        Ellipse.prototype.getDefaultShape = function () {
            return new EllipseShape();
        };
        Ellipse.prototype.buildPath = function (ctx, shape) {
            var k = 0.5522848;
            var x = shape.cx;
            var y = shape.cy;
            var a = shape.rx;
            var b = shape.ry;
            var ox = a * k;
            var oy = b * k;
            ctx.moveTo(x - a, y);
            ctx.bezierCurveTo(x - a, y - oy, x - ox, y - b, x, y - b);
            ctx.bezierCurveTo(x + ox, y - b, x + a, y - oy, x + a, y);
            ctx.bezierCurveTo(x + a, y + oy, x + ox, y + b, x, y + b);
            ctx.bezierCurveTo(x - ox, y + b, x - a, y + oy, x - a, y);
            ctx.closePath();
        };
        return Ellipse;
    }(Path));
    Ellipse.prototype.type = 'ellipse';

    var PI$2 = Math.PI;
    var PI2$5 = PI$2 * 2;
    var mathSin$3 = Math.sin;
    var mathCos$3 = Math.cos;
    var mathACos = Math.acos;
    var mathATan2 = Math.atan2;
    var mathAbs$1 = Math.abs;
    var mathSqrt$3 = Math.sqrt;
    var mathMax$3 = Math.max;
    var mathMin$3 = Math.min;
    var e = 1e-4;
    function intersect(x0, y0, x1, y1, x2, y2, x3, y3) {
        var dx10 = x1 - x0;
        var dy10 = y1 - y0;
        var dx32 = x3 - x2;
        var dy32 = y3 - y2;
        var t = dy32 * dx10 - dx32 * dy10;
        if (t * t < e) {
            return;
        }
        t = (dx32 * (y0 - y2) - dy32 * (x0 - x2)) / t;
        return [x0 + t * dx10, y0 + t * dy10];
    }
    function computeCornerTangents(x0, y0, x1, y1, radius, cr, clockwise) {
        var x01 = x0 - x1;
        var y01 = y0 - y1;
        var lo = (clockwise ? cr : -cr) / mathSqrt$3(x01 * x01 + y01 * y01);
        var ox = lo * y01;
        var oy = -lo * x01;
        var x11 = x0 + ox;
        var y11 = y0 + oy;
        var x10 = x1 + ox;
        var y10 = y1 + oy;
        var x00 = (x11 + x10) / 2;
        var y00 = (y11 + y10) / 2;
        var dx = x10 - x11;
        var dy = y10 - y11;
        var d2 = dx * dx + dy * dy;
        var r = radius - cr;
        var s = x11 * y10 - x10 * y11;
        var d = (dy < 0 ? -1 : 1) * mathSqrt$3(mathMax$3(0, r * r * d2 - s * s));
        var cx0 = (s * dy - dx * d) / d2;
        var cy0 = (-s * dx - dy * d) / d2;
        var cx1 = (s * dy + dx * d) / d2;
        var cy1 = (-s * dx + dy * d) / d2;
        var dx0 = cx0 - x00;
        var dy0 = cy0 - y00;
        var dx1 = cx1 - x00;
        var dy1 = cy1 - y00;
        if (dx0 * dx0 + dy0 * dy0 > dx1 * dx1 + dy1 * dy1) {
            cx0 = cx1;
            cy0 = cy1;
        }
        return {
            cx: cx0,
            cy: cy0,
            x0: -ox,
            y0: -oy,
            x1: cx0 * (radius / r - 1),
            y1: cy0 * (radius / r - 1)
        };
    }
    function normalizeCornerRadius(cr) {
        var arr;
        if (isArray(cr)) {
            var len = cr.length;
            if (!len) {
                return cr;
            }
            if (len === 1) {
                arr = [cr[0], cr[0], 0, 0];
            }
            else if (len === 2) {
                arr = [cr[0], cr[0], cr[1], cr[1]];
            }
            else if (len === 3) {
                arr = cr.concat(cr[2]);
            }
            else {
                arr = cr;
            }
        }
        else {
            arr = [cr, cr, cr, cr];
        }
        return arr;
    }
    function buildPath$1(ctx, shape) {
        var _a;
        var radius = mathMax$3(shape.r, 0);
        var innerRadius = mathMax$3(shape.r0 || 0, 0);
        var hasRadius = radius > 0;
        var hasInnerRadius = innerRadius > 0;
        if (!hasRadius && !hasInnerRadius) {
            return;
        }
        if (!hasRadius) {
            radius = innerRadius;
            innerRadius = 0;
        }
        if (innerRadius > radius) {
            var tmp = radius;
            radius = innerRadius;
            innerRadius = tmp;
        }
        var startAngle = shape.startAngle, endAngle = shape.endAngle;
        if (isNaN(startAngle) || isNaN(endAngle)) {
            return;
        }
        var cx = shape.cx, cy = shape.cy;
        var clockwise = !!shape.clockwise;
        var arc = mathAbs$1(endAngle - startAngle);
        var mod = arc > PI2$5 && arc % PI2$5;
        mod > e && (arc = mod);
        if (!(radius > e)) {
            ctx.moveTo(cx, cy);
        }
        else if (arc > PI2$5 - e) {
            ctx.moveTo(cx + radius * mathCos$3(startAngle), cy + radius * mathSin$3(startAngle));
            ctx.arc(cx, cy, radius, startAngle, endAngle, !clockwise);
            if (innerRadius > e) {
                ctx.moveTo(cx + innerRadius * mathCos$3(endAngle), cy + innerRadius * mathSin$3(endAngle));
                ctx.arc(cx, cy, innerRadius, endAngle, startAngle, clockwise);
            }
        }
        else {
            var icrStart = void 0;
            var icrEnd = void 0;
            var ocrStart = void 0;
            var ocrEnd = void 0;
            var ocrs = void 0;
            var ocre = void 0;
            var icrs = void 0;
            var icre = void 0;
            var ocrMax = void 0;
            var icrMax = void 0;
            var limitedOcrMax = void 0;
            var limitedIcrMax = void 0;
            var xre = void 0;
            var yre = void 0;
            var xirs = void 0;
            var yirs = void 0;
            var xrs = radius * mathCos$3(startAngle);
            var yrs = radius * mathSin$3(startAngle);
            var xire = innerRadius * mathCos$3(endAngle);
            var yire = innerRadius * mathSin$3(endAngle);
            var hasArc = arc > e;
            if (hasArc) {
                var cornerRadius = shape.cornerRadius;
                if (cornerRadius) {
                    _a = normalizeCornerRadius(cornerRadius), icrStart = _a[0], icrEnd = _a[1], ocrStart = _a[2], ocrEnd = _a[3];
                }
                var halfRd = mathAbs$1(radius - innerRadius) / 2;
                ocrs = mathMin$3(halfRd, ocrStart);
                ocre = mathMin$3(halfRd, ocrEnd);
                icrs = mathMin$3(halfRd, icrStart);
                icre = mathMin$3(halfRd, icrEnd);
                limitedOcrMax = ocrMax = mathMax$3(ocrs, ocre);
                limitedIcrMax = icrMax = mathMax$3(icrs, icre);
                if (ocrMax > e || icrMax > e) {
                    xre = radius * mathCos$3(endAngle);
                    yre = radius * mathSin$3(endAngle);
                    xirs = innerRadius * mathCos$3(startAngle);
                    yirs = innerRadius * mathSin$3(startAngle);
                    if (arc < PI$2) {
                        var it_1 = intersect(xrs, yrs, xirs, yirs, xre, yre, xire, yire);
                        if (it_1) {
                            var x0 = xrs - it_1[0];
                            var y0 = yrs - it_1[1];
                            var x1 = xre - it_1[0];
                            var y1 = yre - it_1[1];
                            var a = 1 / mathSin$3(mathACos((x0 * x1 + y0 * y1) / (mathSqrt$3(x0 * x0 + y0 * y0) * mathSqrt$3(x1 * x1 + y1 * y1))) / 2);
                            var b = mathSqrt$3(it_1[0] * it_1[0] + it_1[1] * it_1[1]);
                            limitedOcrMax = mathMin$3(ocrMax, (radius - b) / (a + 1));
                            limitedIcrMax = mathMin$3(icrMax, (innerRadius - b) / (a - 1));
                        }
                    }
                }
            }
            if (!hasArc) {
                ctx.moveTo(cx + xrs, cy + yrs);
            }
            else if (limitedOcrMax > e) {
                var crStart = mathMin$3(ocrStart, limitedOcrMax);
                var crEnd = mathMin$3(ocrEnd, limitedOcrMax);
                var ct0 = computeCornerTangents(xirs, yirs, xrs, yrs, radius, crStart, clockwise);
                var ct1 = computeCornerTangents(xre, yre, xire, yire, radius, crEnd, clockwise);
                ctx.moveTo(cx + ct0.cx + ct0.x0, cy + ct0.cy + ct0.y0);
                if (limitedOcrMax < ocrMax && crStart === crEnd) {
                    ctx.arc(cx + ct0.cx, cy + ct0.cy, limitedOcrMax, mathATan2(ct0.y0, ct0.x0), mathATan2(ct1.y0, ct1.x0), !clockwise);
                }
                else {
                    crStart > 0 && ctx.arc(cx + ct0.cx, cy + ct0.cy, crStart, mathATan2(ct0.y0, ct0.x0), mathATan2(ct0.y1, ct0.x1), !clockwise);
                    ctx.arc(cx, cy, radius, mathATan2(ct0.cy + ct0.y1, ct0.cx + ct0.x1), mathATan2(ct1.cy + ct1.y1, ct1.cx + ct1.x1), !clockwise);
                    crEnd > 0 && ctx.arc(cx + ct1.cx, cy + ct1.cy, crEnd, mathATan2(ct1.y1, ct1.x1), mathATan2(ct1.y0, ct1.x0), !clockwise);
                }
            }
            else {
                ctx.moveTo(cx + xrs, cy + yrs);
                ctx.arc(cx, cy, radius, startAngle, endAngle, !clockwise);
            }
            if (!(innerRadius > e) || !hasArc) {
                ctx.lineTo(cx + xire, cy + yire);
            }
            else if (limitedIcrMax > e) {
                var crStart = mathMin$3(icrStart, limitedIcrMax);
                var crEnd = mathMin$3(icrEnd, limitedIcrMax);
                var ct0 = computeCornerTangents(xire, yire, xre, yre, innerRadius, -crEnd, clockwise);
                var ct1 = computeCornerTangents(xrs, yrs, xirs, yirs, innerRadius, -crStart, clockwise);
                ctx.lineTo(cx + ct0.cx + ct0.x0, cy + ct0.cy + ct0.y0);
                if (limitedIcrMax < icrMax && crStart === crEnd) {
                    ctx.arc(cx + ct0.cx, cy + ct0.cy, limitedIcrMax, mathATan2(ct0.y0, ct0.x0), mathATan2(ct1.y0, ct1.x0), !clockwise);
                }
                else {
                    crEnd > 0 && ctx.arc(cx + ct0.cx, cy + ct0.cy, crEnd, mathATan2(ct0.y0, ct0.x0), mathATan2(ct0.y1, ct0.x1), !clockwise);
                    ctx.arc(cx, cy, innerRadius, mathATan2(ct0.cy + ct0.y1, ct0.cx + ct0.x1), mathATan2(ct1.cy + ct1.y1, ct1.cx + ct1.x1), clockwise);
                    crStart > 0 && ctx.arc(cx + ct1.cx, cy + ct1.cy, crStart, mathATan2(ct1.y1, ct1.x1), mathATan2(ct1.y0, ct1.x0), !clockwise);
                }
            }
            else {
                ctx.lineTo(cx + xire, cy + yire);
                ctx.arc(cx, cy, innerRadius, endAngle, startAngle, clockwise);
            }
        }
        ctx.closePath();
    }

    var SectorShape = (function () {
        function SectorShape() {
            this.cx = 0;
            this.cy = 0;
            this.r0 = 0;
            this.r = 0;
            this.startAngle = 0;
            this.endAngle = Math.PI * 2;
            this.clockwise = true;
            this.cornerRadius = 0;
        }
        return SectorShape;
    }());
    var Sector = (function (_super) {
        __extends(Sector, _super);
        function Sector(opts) {
            return _super.call(this, opts) || this;
        }
        Sector.prototype.getDefaultShape = function () {
            return new SectorShape();
        };
        Sector.prototype.buildPath = function (ctx, shape) {
            buildPath$1(ctx, shape);
        };
        Sector.prototype.isZeroArea = function () {
            return this.shape.startAngle === this.shape.endAngle
                || this.shape.r === this.shape.r0;
        };
        return Sector;
    }(Path));
    Sector.prototype.type = 'sector';

    var RingShape = (function () {
        function RingShape() {
            this.cx = 0;
            this.cy = 0;
            this.r = 0;
            this.r0 = 0;
        }
        return RingShape;
    }());
    var Ring = (function (_super) {
        __extends(Ring, _super);
        function Ring(opts) {
            return _super.call(this, opts) || this;
        }
        Ring.prototype.getDefaultShape = function () {
            return new RingShape();
        };
        Ring.prototype.buildPath = function (ctx, shape) {
            var x = shape.cx;
            var y = shape.cy;
            var PI2 = Math.PI * 2;
            ctx.moveTo(x + shape.r, y);
            ctx.arc(x, y, shape.r, 0, PI2, false);
            ctx.moveTo(x + shape.r0, y);
            ctx.arc(x, y, shape.r0, 0, PI2, true);
        };
        return Ring;
    }(Path));
    Ring.prototype.type = 'ring';

    function smoothBezier(points, smooth, isLoop, constraint) {
        var cps = [];
        var v = [];
        var v1 = [];
        var v2 = [];
        var prevPoint;
        var nextPoint;
        var min$1;
        var max$1;
        if (constraint) {
            min$1 = [Infinity, Infinity];
            max$1 = [-Infinity, -Infinity];
            for (var i = 0, len = points.length; i < len; i++) {
                min(min$1, min$1, points[i]);
                max(max$1, max$1, points[i]);
            }
            min(min$1, min$1, constraint[0]);
            max(max$1, max$1, constraint[1]);
        }
        for (var i = 0, len = points.length; i < len; i++) {
            var point = points[i];
            if (isLoop) {
                prevPoint = points[i ? i - 1 : len - 1];
                nextPoint = points[(i + 1) % len];
            }
            else {
                if (i === 0 || i === len - 1) {
                    cps.push(clone$1(points[i]));
                    continue;
                }
                else {
                    prevPoint = points[i - 1];
                    nextPoint = points[i + 1];
                }
            }
            sub(v, nextPoint, prevPoint);
            scale(v, v, smooth);
            var d0 = distance(point, prevPoint);
            var d1 = distance(point, nextPoint);
            var sum = d0 + d1;
            if (sum !== 0) {
                d0 /= sum;
                d1 /= sum;
            }
            scale(v1, v, -d0);
            scale(v2, v, d1);
            var cp0 = add([], point, v1);
            var cp1 = add([], point, v2);
            if (constraint) {
                max(cp0, cp0, min$1);
                min(cp0, cp0, max$1);
                max(cp1, cp1, min$1);
                min(cp1, cp1, max$1);
            }
            cps.push(cp0);
            cps.push(cp1);
        }
        if (isLoop) {
            cps.push(cps.shift());
        }
        return cps;
    }

    function buildPath$2(ctx, shape, closePath) {
        var smooth = shape.smooth;
        var points = shape.points;
        if (points && points.length >= 2) {
            if (smooth) {
                var controlPoints = smoothBezier(points, smooth, closePath, shape.smoothConstraint);
                ctx.moveTo(points[0][0], points[0][1]);
                var len = points.length;
                for (var i = 0; i < (closePath ? len : len - 1); i++) {
                    var cp1 = controlPoints[i * 2];
                    var cp2 = controlPoints[i * 2 + 1];
                    var p = points[(i + 1) % len];
                    ctx.bezierCurveTo(cp1[0], cp1[1], cp2[0], cp2[1], p[0], p[1]);
                }
            }
            else {
                ctx.moveTo(points[0][0], points[0][1]);
                for (var i = 1, l = points.length; i < l; i++) {
                    ctx.lineTo(points[i][0], points[i][1]);
                }
            }
            closePath && ctx.closePath();
        }
    }

    var PolygonShape = (function () {
        function PolygonShape() {
            this.points = null;
            this.smooth = 0;
            this.smoothConstraint = null;
        }
        return PolygonShape;
    }());
    var Polygon = (function (_super) {
        __extends(Polygon, _super);
        function Polygon(opts) {
            return _super.call(this, opts) || this;
        }
        Polygon.prototype.getDefaultShape = function () {
            return new PolygonShape();
        };
        Polygon.prototype.buildPath = function (ctx, shape) {
            buildPath$2(ctx, shape, true);
        };
        return Polygon;
    }(Path));
    Polygon.prototype.type = 'polygon';

    var PolylineShape = (function () {
        function PolylineShape() {
            this.points = null;
            this.percent = 1;
            this.smooth = 0;
            this.smoothConstraint = null;
        }
        return PolylineShape;
    }());
    var Polyline = (function (_super) {
        __extends(Polyline, _super);
        function Polyline(opts) {
            return _super.call(this, opts) || this;
        }
        Polyline.prototype.getDefaultStyle = function () {
            return {
                stroke: '#000',
                fill: null
            };
        };
        Polyline.prototype.getDefaultShape = function () {
            return new PolylineShape();
        };
        Polyline.prototype.buildPath = function (ctx, shape) {
            buildPath$2(ctx, shape, false);
        };
        return Polyline;
    }(Path));
    Polyline.prototype.type = 'polyline';

    var subPixelOptimizeOutputShape$1 = {};
    var LineShape = (function () {
        function LineShape() {
            this.x1 = 0;
            this.y1 = 0;
            this.x2 = 0;
            this.y2 = 0;
            this.percent = 1;
        }
        return LineShape;
    }());
    var Line = (function (_super) {
        __extends(Line, _super);
        function Line(opts) {
            return _super.call(this, opts) || this;
        }
        Line.prototype.getDefaultStyle = function () {
            return {
                stroke: '#000',
                fill: null
            };
        };
        Line.prototype.getDefaultShape = function () {
            return new LineShape();
        };
        Line.prototype.buildPath = function (ctx, shape) {
            var x1;
            var y1;
            var x2;
            var y2;
            if (this.subPixelOptimize) {
                var optimizedShape = subPixelOptimizeLine(subPixelOptimizeOutputShape$1, shape, this.style);
                x1 = optimizedShape.x1;
                y1 = optimizedShape.y1;
                x2 = optimizedShape.x2;
                y2 = optimizedShape.y2;
            }
            else {
                x1 = shape.x1;
                y1 = shape.y1;
                x2 = shape.x2;
                y2 = shape.y2;
            }
            var percent = shape.percent;
            if (percent === 0) {
                return;
            }
            ctx.moveTo(x1, y1);
            if (percent < 1) {
                x2 = x1 * (1 - percent) + x2 * percent;
                y2 = y1 * (1 - percent) + y2 * percent;
            }
            ctx.lineTo(x2, y2);
        };
        Line.prototype.pointAt = function (p) {
            var shape = this.shape;
            return [
                shape.x1 * (1 - p) + shape.x2 * p,
                shape.y1 * (1 - p) + shape.y2 * p
            ];
        };
        return Line;
    }(Path));
    Line.prototype.type = 'line';

    var out = [];
    var BezierCurveShape = (function () {
        function BezierCurveShape() {
            this.x1 = 0;
            this.y1 = 0;
            this.x2 = 0;
            this.y2 = 0;
            this.cpx1 = 0;
            this.cpy1 = 0;
            this.percent = 1;
        }
        return BezierCurveShape;
    }());
    function someVectorAt(shape, t, isTangent) {
        var cpx2 = shape.cpx2;
        var cpy2 = shape.cpy2;
        if (cpx2 != null || cpy2 != null) {
            return [
                (isTangent ? cubicDerivativeAt : cubicAt)(shape.x1, shape.cpx1, shape.cpx2, shape.x2, t),
                (isTangent ? cubicDerivativeAt : cubicAt)(shape.y1, shape.cpy1, shape.cpy2, shape.y2, t)
            ];
        }
        else {
            return [
                (isTangent ? quadraticDerivativeAt : quadraticAt)(shape.x1, shape.cpx1, shape.x2, t),
                (isTangent ? quadraticDerivativeAt : quadraticAt)(shape.y1, shape.cpy1, shape.y2, t)
            ];
        }
    }
    var BezierCurve = (function (_super) {
        __extends(BezierCurve, _super);
        function BezierCurve(opts) {
            return _super.call(this, opts) || this;
        }
        BezierCurve.prototype.getDefaultStyle = function () {
            return {
                stroke: '#000',
                fill: null
            };
        };
        BezierCurve.prototype.getDefaultShape = function () {
            return new BezierCurveShape();
        };
        BezierCurve.prototype.buildPath = function (ctx, shape) {
            var x1 = shape.x1;
            var y1 = shape.y1;
            var x2 = shape.x2;
            var y2 = shape.y2;
            var cpx1 = shape.cpx1;
            var cpy1 = shape.cpy1;
            var cpx2 = shape.cpx2;
            var cpy2 = shape.cpy2;
            var percent = shape.percent;
            if (percent === 0) {
                return;
            }
            ctx.moveTo(x1, y1);
            if (cpx2 == null || cpy2 == null) {
                if (percent < 1) {
                    quadraticSubdivide(x1, cpx1, x2, percent, out);
                    cpx1 = out[1];
                    x2 = out[2];
                    quadraticSubdivide(y1, cpy1, y2, percent, out);
                    cpy1 = out[1];
                    y2 = out[2];
                }
                ctx.quadraticCurveTo(cpx1, cpy1, x2, y2);
            }
            else {
                if (percent < 1) {
                    cubicSubdivide(x1, cpx1, cpx2, x2, percent, out);
                    cpx1 = out[1];
                    cpx2 = out[2];
                    x2 = out[3];
                    cubicSubdivide(y1, cpy1, cpy2, y2, percent, out);
                    cpy1 = out[1];
                    cpy2 = out[2];
                    y2 = out[3];
                }
                ctx.bezierCurveTo(cpx1, cpy1, cpx2, cpy2, x2, y2);
            }
        };
        BezierCurve.prototype.pointAt = function (t) {
            return someVectorAt(this.shape, t, false);
        };
        BezierCurve.prototype.tangentAt = function (t) {
            var p = someVectorAt(this.shape, t, true);
            return normalize(p, p);
        };
        return BezierCurve;
    }(Path));
    BezierCurve.prototype.type = 'bezier-curve';

    var ArcShape = (function () {
        function ArcShape() {
            this.cx = 0;
            this.cy = 0;
            this.r = 0;
            this.startAngle = 0;
            this.endAngle = Math.PI * 2;
            this.clockwise = true;
        }
        return ArcShape;
    }());
    var Arc = (function (_super) {
        __extends(Arc, _super);
        function Arc(opts) {
            return _super.call(this, opts) || this;
        }
        Arc.prototype.getDefaultStyle = function () {
            return {
                stroke: '#000',
                fill: null
            };
        };
        Arc.prototype.getDefaultShape = function () {
            return new ArcShape();
        };
        Arc.prototype.buildPath = function (ctx, shape) {
            var x = shape.cx;
            var y = shape.cy;
            var r = Math.max(shape.r, 0);
            var startAngle = shape.startAngle;
            var endAngle = shape.endAngle;
            var clockwise = shape.clockwise;
            var unitX = Math.cos(startAngle);
            var unitY = Math.sin(startAngle);
            ctx.moveTo(unitX * r + x, unitY * r + y);
            ctx.arc(x, y, r, startAngle, endAngle, !clockwise);
        };
        return Arc;
    }(Path));
    Arc.prototype.type = 'arc';

    var CompoundPath = (function (_super) {
        __extends(CompoundPath, _super);
        function CompoundPath() {
            var _this = _super !== null && _super.apply(this, arguments) || this;
            _this.type = 'compound';
            return _this;
        }
        CompoundPath.prototype._updatePathDirty = function () {
            var paths = this.shape.paths;
            var dirtyPath = this.shapeChanged();
            for (var i = 0; i < paths.length; i++) {
                dirtyPath = dirtyPath || paths[i].shapeChanged();
            }
            if (dirtyPath) {
                this.dirtyShape();
            }
        };
        CompoundPath.prototype.beforeBrush = function () {
            this._updatePathDirty();
            var paths = this.shape.paths || [];
            var scale = this.getGlobalScale();
            for (var i = 0; i < paths.length; i++) {
                if (!paths[i].path) {
                    paths[i].createPathProxy();
                }
                paths[i].path.setScale(scale[0], scale[1], paths[i].segmentIgnoreThreshold);
            }
        };
        CompoundPath.prototype.buildPath = function (ctx, shape) {
            var paths = shape.paths || [];
            for (var i = 0; i < paths.length; i++) {
                paths[i].buildPath(ctx, paths[i].shape, true);
            }
        };
        CompoundPath.prototype.afterBrush = function () {
            var paths = this.shape.paths || [];
            for (var i = 0; i < paths.length; i++) {
                paths[i].pathUpdated();
            }
        };
        CompoundPath.prototype.getBoundingRect = function () {
            this._updatePathDirty.call(this);
            return Path.prototype.getBoundingRect.call(this);
        };
        return CompoundPath;
    }(Path));

    var Gradient = (function () {
        function Gradient(colorStops) {
            this.colorStops = colorStops || [];
        }
        Gradient.prototype.addColorStop = function (offset, color) {
            this.colorStops.push({
                offset: offset,
                color: color
            });
        };
        return Gradient;
    }());

    var LinearGradient = (function (_super) {
        __extends(LinearGradient, _super);
        function LinearGradient(x, y, x2, y2, colorStops, globalCoord) {
            var _this = _super.call(this, colorStops) || this;
            _this.x = x == null ? 0 : x;
            _this.y = y == null ? 0 : y;
            _this.x2 = x2 == null ? 1 : x2;
            _this.y2 = y2 == null ? 0 : y2;
            _this.type = 'linear';
            _this.global = globalCoord || false;
            return _this;
        }
        return LinearGradient;
    }(Gradient));

    var RadialGradient = (function (_super) {
        __extends(RadialGradient, _super);
        function RadialGradient(x, y, r, colorStops, globalCoord) {
            var _this = _super.call(this, colorStops) || this;
            _this.x = x == null ? 0.5 : x;
            _this.y = y == null ? 0.5 : y;
            _this.r = r == null ? 0.5 : r;
            _this.type = 'radial';
            _this.global = globalCoord || false;
            return _this;
        }
        return RadialGradient;
    }(Gradient));

    var extent = [0, 0];
    var extent2 = [0, 0];
    var minTv$1 = new Point();
    var maxTv$1 = new Point();
    var OrientedBoundingRect = (function () {
        function OrientedBoundingRect(rect, transform) {
            this._corners = [];
            this._axes = [];
            this._origin = [0, 0];
            for (var i = 0; i < 4; i++) {
                this._corners[i] = new Point();
            }
            for (var i = 0; i < 2; i++) {
                this._axes[i] = new Point();
            }
            if (rect) {
                this.fromBoundingRect(rect, transform);
            }
        }
        OrientedBoundingRect.prototype.fromBoundingRect = function (rect, transform) {
            var corners = this._corners;
            var axes = this._axes;
            var x = rect.x;
            var y = rect.y;
            var x2 = x + rect.width;
            var y2 = y + rect.height;
            corners[0].set(x, y);
            corners[1].set(x2, y);
            corners[2].set(x2, y2);
            corners[3].set(x, y2);
            if (transform) {
                for (var i = 0; i < 4; i++) {
                    corners[i].transform(transform);
                }
            }
            Point.sub(axes[0], corners[1], corners[0]);
            Point.sub(axes[1], corners[3], corners[0]);
            axes[0].normalize();
            axes[1].normalize();
            for (var i = 0; i < 2; i++) {
                this._origin[i] = axes[i].dot(corners[0]);
            }
        };
        OrientedBoundingRect.prototype.intersect = function (other, mtv) {
            var overlapped = true;
            var noMtv = !mtv;
            minTv$1.set(Infinity, Infinity);
            maxTv$1.set(0, 0);
            if (!this._intersectCheckOneSide(this, other, minTv$1, maxTv$1, noMtv, 1)) {
                overlapped = false;
                if (noMtv) {
                    return overlapped;
                }
            }
            if (!this._intersectCheckOneSide(other, this, minTv$1, maxTv$1, noMtv, -1)) {
                overlapped = false;
                if (noMtv) {
                    return overlapped;
                }
            }
            if (!noMtv) {
                Point.copy(mtv, overlapped ? minTv$1 : maxTv$1);
            }
            return overlapped;
        };
        OrientedBoundingRect.prototype._intersectCheckOneSide = function (self, other, minTv, maxTv, noMtv, inverse) {
            var overlapped = true;
            for (var i = 0; i < 2; i++) {
                var axis = this._axes[i];
                this._getProjMinMaxOnAxis(i, self._corners, extent);
                this._getProjMinMaxOnAxis(i, other._corners, extent2);
                if (extent[1] < extent2[0] || extent[0] > extent2[1]) {
                    overlapped = false;
                    if (noMtv) {
                        return overlapped;
                    }
                    var dist0 = Math.abs(extent2[0] - extent[1]);
                    var dist1 = Math.abs(extent[0] - extent2[1]);
                    if (Math.min(dist0, dist1) > maxTv.len()) {
                        if (dist0 < dist1) {
                            Point.scale(maxTv, axis, -dist0 * inverse);
                        }
                        else {
                            Point.scale(maxTv, axis, dist1 * inverse);
                        }
                    }
                }
                else if (minTv) {
                    var dist0 = Math.abs(extent2[0] - extent[1]);
                    var dist1 = Math.abs(extent[0] - extent2[1]);
                    if (Math.min(dist0, dist1) < minTv.len()) {
                        if (dist0 < dist1) {
                            Point.scale(minTv, axis, dist0 * inverse);
                        }
                        else {
                            Point.scale(minTv, axis, -dist1 * inverse);
                        }
                    }
                }
            }
            return overlapped;
        };
        OrientedBoundingRect.prototype._getProjMinMaxOnAxis = function (dim, corners, out) {
            var axis = this._axes[dim];
            var origin = this._origin;
            var proj = corners[0].dot(axis) + origin[dim];
            var min = proj;
            var max = proj;
            for (var i = 1; i < corners.length; i++) {
                var proj_1 = corners[i].dot(axis) + origin[dim];
                min = Math.min(proj_1, min);
                max = Math.max(proj_1, max);
            }
            out[0] = min;
            out[1] = max;
        };
        return OrientedBoundingRect;
    }());

    var m = [];
    var IncrementalDisplayable = (function (_super) {
        __extends(IncrementalDisplayable, _super);
        function IncrementalDisplayable() {
            var _this = _super !== null && _super.apply(this, arguments) || this;
            _this.notClear = true;
            _this.incremental = true;
            _this._displayables = [];
            _this._temporaryDisplayables = [];
            _this._cursor = 0;
            return _this;
        }
        IncrementalDisplayable.prototype.traverse = function (cb, context) {
            cb.call(context, this);
        };
        IncrementalDisplayable.prototype.useStyle = function () {
            this.style = {};
        };
        IncrementalDisplayable.prototype.getCursor = function () {
            return this._cursor;
        };
        IncrementalDisplayable.prototype.innerAfterBrush = function () {
            this._cursor = this._displayables.length;
        };
        IncrementalDisplayable.prototype.clearDisplaybles = function () {
            this._displayables = [];
            this._temporaryDisplayables = [];
            this._cursor = 0;
            this.markRedraw();
            this.notClear = false;
        };
        IncrementalDisplayable.prototype.clearTemporalDisplayables = function () {
            this._temporaryDisplayables = [];
        };
        IncrementalDisplayable.prototype.addDisplayable = function (displayable, notPersistent) {
            if (notPersistent) {
                this._temporaryDisplayables.push(displayable);
            }
            else {
                this._displayables.push(displayable);
            }
            this.markRedraw();
        };
        IncrementalDisplayable.prototype.addDisplayables = function (displayables, notPersistent) {
            notPersistent = notPersistent || false;
            for (var i = 0; i < displayables.length; i++) {
                this.addDisplayable(displayables[i], notPersistent);
            }
        };
        IncrementalDisplayable.prototype.getDisplayables = function () {
            return this._displayables;
        };
        IncrementalDisplayable.prototype.getTemporalDisplayables = function () {
            return this._temporaryDisplayables;
        };
        IncrementalDisplayable.prototype.eachPendingDisplayable = function (cb) {
            for (var i = this._cursor; i < this._displayables.length; i++) {
                cb && cb(this._displayables[i]);
            }
            for (var i = 0; i < this._temporaryDisplayables.length; i++) {
                cb && cb(this._temporaryDisplayables[i]);
            }
        };
        IncrementalDisplayable.prototype.update = function () {
            this.updateTransform();
            for (var i = this._cursor; i < this._displayables.length; i++) {
                var displayable = this._displayables[i];
                displayable.parent = this;
                displayable.update();
                displayable.parent = null;
            }
            for (var i = 0; i < this._temporaryDisplayables.length; i++) {
                var displayable = this._temporaryDisplayables[i];
                displayable.parent = this;
                displayable.update();
                displayable.parent = null;
            }
        };
        IncrementalDisplayable.prototype.getBoundingRect = function () {
            if (!this._rect) {
                var rect = new BoundingRect(Infinity, Infinity, -Infinity, -Infinity);
                for (var i = 0; i < this._displayables.length; i++) {
                    var displayable = this._displayables[i];
                    var childRect = displayable.getBoundingRect().clone();
                    if (displayable.needLocalTransform()) {
                        childRect.applyTransform(displayable.getLocalTransform(m));
                    }
                    rect.union(childRect);
                }
                this._rect = rect;
            }
            return this._rect;
        };
        IncrementalDisplayable.prototype.contain = function (x, y) {
            var localPos = this.transformCoordToLocal(x, y);
            var rect = this.getBoundingRect();
            if (rect.contain(localPos[0], localPos[1])) {
                for (var i = 0; i < this._displayables.length; i++) {
                    var displayable = this._displayables[i];
                    if (displayable.contain(x, y)) {
                        return true;
                    }
                }
            }
            return false;
        };
        return IncrementalDisplayable;
    }(Displayable));

    var transitionStore = makeInner();
    /**
     * Return null if animation is disabled.
     */

    function getAnimationConfig(animationType, animatableModel, dataIndex, // Extra opts can override the option in animatable model.
    extraOpts, // TODO It's only for pictorial bar now.
    extraDelayParams) {
      var animationPayload; // Check if there is global animation configuration from dataZoom/resize can override the config in option.
      // If animation is enabled. Will use this animation config in payload.
      // If animation is disabled. Just ignore it.

      if (animatableModel && animatableModel.ecModel) {
        var updatePayload = animatableModel.ecModel.getUpdatePayload();
        animationPayload = updatePayload && updatePayload.animation;
      }

      var animationEnabled = animatableModel && animatableModel.isAnimationEnabled();
      var isUpdate = animationType === 'update';

      if (animationEnabled) {
        var duration = void 0;
        var easing = void 0;
        var delay = void 0;

        if (extraOpts) {
          duration = retrieve2(extraOpts.duration, 200);
          easing = retrieve2(extraOpts.easing, 'cubicOut');
          delay = 0;
        } else {
          duration = animatableModel.getShallow(isUpdate ? 'animationDurationUpdate' : 'animationDuration');
          easing = animatableModel.getShallow(isUpdate ? 'animationEasingUpdate' : 'animationEasing');
          delay = animatableModel.getShallow(isUpdate ? 'animationDelayUpdate' : 'animationDelay');
        } // animation from payload has highest priority.


        if (animationPayload) {
          animationPayload.duration != null && (duration = animationPayload.duration);
          animationPayload.easing != null && (easing = animationPayload.easing);
          animationPayload.delay != null && (delay = animationPayload.delay);
        }

        if (isFunction(delay)) {
          delay = delay(dataIndex, extraDelayParams);
        }

        if (isFunction(duration)) {
          duration = duration(dataIndex);
        }

        var config = {
          duration: duration || 0,
          delay: delay,
          easing: easing
        };
        return config;
      } else {
        return null;
      }
    }

    function animateOrSetProps(animationType, el, props, animatableModel, dataIndex, cb, during) {
      var isFrom = false;
      var removeOpt;

      if (isFunction(dataIndex)) {
        during = cb;
        cb = dataIndex;
        dataIndex = null;
      } else if (isObject(dataIndex)) {
        cb = dataIndex.cb;
        during = dataIndex.during;
        isFrom = dataIndex.isFrom;
        removeOpt = dataIndex.removeOpt;
        dataIndex = dataIndex.dataIndex;
      }

      var isRemove = animationType === 'leave';

      if (!isRemove) {
        // Must stop the remove animation.
        el.stopAnimation('leave');
      }

      var animationConfig = getAnimationConfig(animationType, animatableModel, dataIndex, isRemove ? removeOpt || {} : null, animatableModel && animatableModel.getAnimationDelayParams ? animatableModel.getAnimationDelayParams(el, dataIndex) : null);

      if (animationConfig && animationConfig.duration > 0) {
        var duration = animationConfig.duration;
        var animationDelay = animationConfig.delay;
        var animationEasing = animationConfig.easing;
        var animateConfig = {
          duration: duration,
          delay: animationDelay || 0,
          easing: animationEasing,
          done: cb,
          force: !!cb || !!during,
          // Set to final state in update/init animation.
          // So the post processing based on the path shape can be done correctly.
          setToFinal: !isRemove,
          scope: animationType,
          during: during
        };
        isFrom ? el.animateFrom(props, animateConfig) : el.animateTo(props, animateConfig);
      } else {
        el.stopAnimation(); // If `isFrom`, the props is the "from" props.

        !isFrom && el.attr(props); // Call during at least once.

        during && during(1);
        cb && cb();
      }
    }
    /**
     * Update graphic element properties with or without animation according to the
     * configuration in series.
     *
     * Caution: this method will stop previous animation.
     * So do not use this method to one element twice before
     * animation starts, unless you know what you are doing.
     * @example
     *     graphic.updateProps(el, {
     *         position: [100, 100]
     *     }, seriesModel, dataIndex, function () { console.log('Animation done!'); });
     *     // Or
     *     graphic.updateProps(el, {
     *         position: [100, 100]
     *     }, seriesModel, function () { console.log('Animation done!'); });
     */


    function updateProps(el, props, // TODO: TYPE AnimatableModel
    animatableModel, dataIndex, cb, during) {
      animateOrSetProps('update', el, props, animatableModel, dataIndex, cb, during);
    }
    /**
     * Init graphic element properties with or without animation according to the
     * configuration in series.
     *
     * Caution: this method will stop previous animation.
     * So do not use this method to one element twice before
     * animation starts, unless you know what you are doing.
     */

    function initProps(el, props, animatableModel, dataIndex, cb, during) {
      animateOrSetProps('enter', el, props, animatableModel, dataIndex, cb, during);
    }
    /**
     * If element is removed.
     * It can determine if element is having remove animation.
     */

    function isElementRemoved(el) {
      if (!el.__zr) {
        return true;
      }

      for (var i = 0; i < el.animators.length; i++) {
        var animator = el.animators[i];

        if (animator.scope === 'leave') {
          return true;
        }
      }

      return false;
    }
    /**
     * Remove graphic element
     */

    function removeElement(el, props, animatableModel, dataIndex, cb, during) {
      // Don't do remove animation twice.
      if (isElementRemoved(el)) {
        return;
      }

      animateOrSetProps('leave', el, props, animatableModel, dataIndex, cb, during);
    }

    function fadeOutDisplayable(el, animatableModel, dataIndex, done) {
      el.removeTextContent();
      el.removeTextGuideLine();
      removeElement(el, {
        style: {
          opacity: 0
        }
      }, animatableModel, dataIndex, done);
    }

    function removeElementWithFadeOut(el, animatableModel, dataIndex) {
      function doRemove() {
        el.parent && el.parent.remove(el);
      } // Hide label and labelLine first
      // TODO Also use fade out animation?


      if (!el.isGroup) {
        fadeOutDisplayable(el, animatableModel, dataIndex, doRemove);
      } else {
        el.traverse(function (disp) {
          if (!disp.isGroup) {
            // Can invoke doRemove multiple times.
            fadeOutDisplayable(disp, animatableModel, dataIndex, doRemove);
          }
        });
      }
    }
    /**
     * Save old style for style transition in universalTransition module.
     * It's used when element will be reused in each render.
     * For chart like map, heatmap, which will always create new element.
     * We don't need to save this because universalTransition can get old style from the old element
     */

    function saveOldStyle(el) {
      transitionStore(el).oldStyle = el.style;
    }

    var mathMax$4 = Math.max;
    var mathMin$4 = Math.min;
    var _customShapeMap = {};
    /**
     * Extend shape with parameters
     */

    function extendShape(opts) {
      return Path.extend(opts);
    }
    var extendPathFromString = extendFromString;
    /**
     * Extend path
     */

    function extendPath(pathData, opts) {
      return extendPathFromString(pathData, opts);
    }
    /**
     * Register a user defined shape.
     * The shape class can be fetched by `getShapeClass`
     * This method will overwrite the registered shapes, including
     * the registered built-in shapes, if using the same `name`.
     * The shape can be used in `custom series` and
     * `graphic component` by declaring `{type: name}`.
     *
     * @param name
     * @param ShapeClass Can be generated by `extendShape`.
     */

    function registerShape(name, ShapeClass) {
      _customShapeMap[name] = ShapeClass;
    }
    /**
     * Find shape class registered by `registerShape`. Usually used in
     * fetching user defined shape.
     *
     * [Caution]:
     * (1) This method **MUST NOT be used inside echarts !!!**, unless it is prepared
     * to use user registered shapes.
     * Because the built-in shape (see `getBuiltInShape`) will be registered by
     * `registerShape` by default. That enables users to get both built-in
     * shapes as well as the shapes belonging to themsleves. But users can overwrite
     * the built-in shapes by using names like 'circle', 'rect' via calling
     * `registerShape`. So the echarts inner featrues should not fetch shapes from here
     * in case that it is overwritten by users, except that some features, like
     * `custom series`, `graphic component`, do it deliberately.
     *
     * (2) In the features like `custom series`, `graphic component`, the user input
     * `{tpye: 'xxx'}` does not only specify shapes but also specify other graphic
     * elements like `'group'`, `'text'`, `'image'` or event `'path'`. Those names
     * are reserved names, that is, if some user registers a shape named `'image'`,
     * the shape will not be used. If we intending to add some more reserved names
     * in feature, that might bring break changes (disable some existing user shape
     * names). But that case probably rarely happens. So we don't make more mechanism
     * to resolve this issue here.
     *
     * @param name
     * @return The shape class. If not found, return nothing.
     */

    function getShapeClass(name) {
      if (_customShapeMap.hasOwnProperty(name)) {
        return _customShapeMap[name];
      }
    }
    /**
     * Create a path element from path data string
     * @param pathData
     * @param opts
     * @param rect
     * @param layout 'center' or 'cover' default to be cover
     */

    function makePath(pathData, opts, rect, layout) {
      var path = createFromString(pathData, opts);

      if (rect) {
        if (layout === 'center') {
          rect = centerGraphic(rect, path.getBoundingRect());
        }

        resizePath(path, rect);
      }

      return path;
    }
    /**
     * Create a image element from image url
     * @param imageUrl image url
     * @param opts options
     * @param rect constrain rect
     * @param layout 'center' or 'cover'. Default to be 'cover'
     */

    function makeImage(imageUrl, rect, layout) {
      var zrImg = new ZRImage({
        style: {
          image: imageUrl,
          x: rect.x,
          y: rect.y,
          width: rect.width,
          height: rect.height
        },
        onload: function (img) {
          if (layout === 'center') {
            var boundingRect = {
              width: img.width,
              height: img.height
            };
            zrImg.setStyle(centerGraphic(rect, boundingRect));
          }
        }
      });
      return zrImg;
    }
    /**
     * Get position of centered element in bounding box.
     *
     * @param  rect         element local bounding box
     * @param  boundingRect constraint bounding box
     * @return element position containing x, y, width, and height
     */

    function centerGraphic(rect, boundingRect) {
      // Set rect to center, keep width / height ratio.
      var aspect = boundingRect.width / boundingRect.height;
      var width = rect.height * aspect;
      var height;

      if (width <= rect.width) {
        height = rect.height;
      } else {
        width = rect.width;
        height = width / aspect;
      }

      var cx = rect.x + rect.width / 2;
      var cy = rect.y + rect.height / 2;
      return {
        x: cx - width / 2,
        y: cy - height / 2,
        width: width,
        height: height
      };
    }

    var mergePath$1 = mergePath;
    /**
     * Resize a path to fit the rect
     * @param path
     * @param rect
     */

    function resizePath(path, rect) {
      if (!path.applyTransform) {
        return;
      }

      var pathRect = path.getBoundingRect();
      var m = pathRect.calculateTransform(rect);
      path.applyTransform(m);
    }
    /**
     * Sub pixel optimize line for canvas
     */

    function subPixelOptimizeLine$1(shape, lineWidth) {
      subPixelOptimizeLine(shape, shape, {
        lineWidth: lineWidth
      });
      return shape;
    }
    /**
     * Sub pixel optimize rect for canvas
     */

    function subPixelOptimizeRect$1(param) {
      subPixelOptimizeRect(param.shape, param.shape, param.style);
      return param;
    }
    /**
     * Sub pixel optimize for canvas
     *
     * @param position Coordinate, such as x, y
     * @param lineWidth Should be nonnegative integer.
     * @param positiveOrNegative Default false (negative).
     * @return Optimized position.
     */

    var subPixelOptimize$1 = subPixelOptimize;
    /**
     * Get transform matrix of target (param target),
     * in coordinate of its ancestor (param ancestor)
     *
     * @param target
     * @param [ancestor]
     */

    function getTransform(target, ancestor) {
      var mat = identity([]);

      while (target && target !== ancestor) {
        mul$1(mat, target.getLocalTransform(), mat);
        target = target.parent;
      }

      return mat;
    }
    /**
     * Apply transform to an vertex.
     * @param target [x, y]
     * @param transform Can be:
     *      + Transform matrix: like [1, 0, 0, 1, 0, 0]
     *      + {position, rotation, scale}, the same as `zrender/Transformable`.
     * @param invert Whether use invert matrix.
     * @return [x, y]
     */

    function applyTransform$1(target, transform, invert$1) {
      if (transform && !isArrayLike(transform)) {
        transform = Transformable.getLocalTransform(transform);
      }

      if (invert$1) {
        transform = invert([], transform);
      }

      return applyTransform([], target, transform);
    }
    /**
     * @param direction 'left' 'right' 'top' 'bottom'
     * @param transform Transform matrix: like [1, 0, 0, 1, 0, 0]
     * @param invert Whether use invert matrix.
     * @return Transformed direction. 'left' 'right' 'top' 'bottom'
     */

    function transformDirection(direction, transform, invert) {
      // Pick a base, ensure that transform result will not be (0, 0).
      var hBase = transform[4] === 0 || transform[5] === 0 || transform[0] === 0 ? 1 : Math.abs(2 * transform[4] / transform[0]);
      var vBase = transform[4] === 0 || transform[5] === 0 || transform[2] === 0 ? 1 : Math.abs(2 * transform[4] / transform[2]);
      var vertex = [direction === 'left' ? -hBase : direction === 'right' ? hBase : 0, direction === 'top' ? -vBase : direction === 'bottom' ? vBase : 0];
      vertex = applyTransform$1(vertex, transform, invert);
      return Math.abs(vertex[0]) > Math.abs(vertex[1]) ? vertex[0] > 0 ? 'right' : 'left' : vertex[1] > 0 ? 'bottom' : 'top';
    }

    function isNotGroup(el) {
      return !el.isGroup;
    }

    function isPath(el) {
      return el.shape != null;
    }
    /**
     * Apply group transition animation from g1 to g2.
     * If no animatableModel, no animation.
     */


    function groupTransition(g1, g2, animatableModel) {
      if (!g1 || !g2) {
        return;
      }

      function getElMap(g) {
        var elMap = {};
        g.traverse(function (el) {
          if (isNotGroup(el) && el.anid) {
            elMap[el.anid] = el;
          }
        });
        return elMap;
      }

      function getAnimatableProps(el) {
        var obj = {
          x: el.x,
          y: el.y,
          rotation: el.rotation
        };

        if (isPath(el)) {
          obj.shape = extend({}, el.shape);
        }

        return obj;
      }

      var elMap1 = getElMap(g1);
      g2.traverse(function (el) {
        if (isNotGroup(el) && el.anid) {
          var oldEl = elMap1[el.anid];

          if (oldEl) {
            var newProp = getAnimatableProps(el);
            el.attr(getAnimatableProps(oldEl));
            updateProps(el, newProp, animatableModel, getECData(el).dataIndex);
          }
        }
      });
    }
    function clipPointsByRect(points, rect) {
      // FIXME: This way might be incorrect when graphic clipped by a corner
      // and when element has a border.
      return map(points, function (point) {
        var x = point[0];
        x = mathMax$4(x, rect.x);
        x = mathMin$4(x, rect.x + rect.width);
        var y = point[1];
        y = mathMax$4(y, rect.y);
        y = mathMin$4(y, rect.y + rect.height);
        return [x, y];
      });
    }
    /**
     * Return a new clipped rect. If rect size are negative, return undefined.
     */

    function clipRectByRect(targetRect, rect) {
      var x = mathMax$4(targetRect.x, rect.x);
      var x2 = mathMin$4(targetRect.x + targetRect.width, rect.x + rect.width);
      var y = mathMax$4(targetRect.y, rect.y);
      var y2 = mathMin$4(targetRect.y + targetRect.height, rect.y + rect.height); // If the total rect is cliped, nothing, including the border,
      // should be painted. So return undefined.

      if (x2 >= x && y2 >= y) {
        return {
          x: x,
          y: y,
          width: x2 - x,
          height: y2 - y
        };
      }
    }
    function createIcon(iconStr, // Support 'image://' or 'path://' or direct svg path.
    opt, rect) {
      var innerOpts = extend({
        rectHover: true
      }, opt);
      var style = innerOpts.style = {
        strokeNoScale: true
      };
      rect = rect || {
        x: -1,
        y: -1,
        width: 2,
        height: 2
      };

      if (iconStr) {
        return iconStr.indexOf('image://') === 0 ? (style.image = iconStr.slice(8), defaults(style, rect), new ZRImage(innerOpts)) : makePath(iconStr.replace('path://', ''), innerOpts, rect, 'center');
      }
    }
    /**
     * Return `true` if the given line (line `a`) and the given polygon
     * are intersect.
     * Note that we do not count colinear as intersect here because no
     * requirement for that. We could do that if required in future.
     */

    function linePolygonIntersect(a1x, a1y, a2x, a2y, points) {
      for (var i = 0, p2 = points[points.length - 1]; i < points.length; i++) {
        var p = points[i];

        if (lineLineIntersect(a1x, a1y, a2x, a2y, p[0], p[1], p2[0], p2[1])) {
          return true;
        }

        p2 = p;
      }
    }
    /**
     * Return `true` if the given two lines (line `a` and line `b`)
     * are intersect.
     * Note that we do not count colinear as intersect here because no
     * requirement for that. We could do that if required in future.
     */

    function lineLineIntersect(a1x, a1y, a2x, a2y, b1x, b1y, b2x, b2y) {
      // let `vec_m` to be `vec_a2 - vec_a1` and `vec_n` to be `vec_b2 - vec_b1`.
      var mx = a2x - a1x;
      var my = a2y - a1y;
      var nx = b2x - b1x;
      var ny = b2y - b1y; // `vec_m` and `vec_n` are parallel iff
      //     existing `k` such that `vec_m = k · vec_n`, equivalent to `vec_m X vec_n = 0`.

      var nmCrossProduct = crossProduct2d(nx, ny, mx, my);

      if (nearZero(nmCrossProduct)) {
        return false;
      } // `vec_m` and `vec_n` are intersect iff
      //     existing `p` and `q` in [0, 1] such that `vec_a1 + p * vec_m = vec_b1 + q * vec_n`,
      //     such that `q = ((vec_a1 - vec_b1) X vec_m) / (vec_n X vec_m)`
      //           and `p = ((vec_a1 - vec_b1) X vec_n) / (vec_n X vec_m)`.


      var b1a1x = a1x - b1x;
      var b1a1y = a1y - b1y;
      var q = crossProduct2d(b1a1x, b1a1y, mx, my) / nmCrossProduct;

      if (q < 0 || q > 1) {
        return false;
      }

      var p = crossProduct2d(b1a1x, b1a1y, nx, ny) / nmCrossProduct;

      if (p < 0 || p > 1) {
        return false;
      }

      return true;
    }
    /**
     * Cross product of 2-dimension vector.
     */

    function crossProduct2d(x1, y1, x2, y2) {
      return x1 * y2 - x2 * y1;
    }

    function nearZero(val) {
      return val <= 1e-6 && val >= -1e-6;
    }

    function setTooltipConfig(opt) {
      var itemTooltipOption = opt.itemTooltipOption;
      var componentModel = opt.componentModel;
      var itemName = opt.itemName;
      var itemTooltipOptionObj = isString(itemTooltipOption) ? {
        formatter: itemTooltipOption
      } : itemTooltipOption;
      var mainType = componentModel.mainType;
      var componentIndex = componentModel.componentIndex;
      var formatterParams = {
        componentType: mainType,
        name: itemName,
        $vars: ['name']
      };
      formatterParams[mainType + 'Index'] = componentIndex;
      var formatterParamsExtra = opt.formatterParamsExtra;

      if (formatterParamsExtra) {
        each(keys(formatterParamsExtra), function (key) {
          if (!hasOwn(formatterParams, key)) {
            formatterParams[key] = formatterParamsExtra[key];
            formatterParams.$vars.push(key);
          }
        });
      }

      var ecData = getECData(opt.el);
      ecData.componentMainType = mainType;
      ecData.componentIndex = componentIndex;
      ecData.tooltipConfig = {
        name: itemName,
        option: defaults({
          content: itemName,
          formatterParams: formatterParams
        }, itemTooltipOptionObj)
      };
    }

    function traverseElement(el, cb) {
      var stopped; // TODO
      // Polyfill for fixing zrender group traverse don't visit it's root issue.

      if (el.isGroup) {
        stopped = cb(el);
      }

      if (!stopped) {
        el.traverse(cb);
      }
    }

    function traverseElements(els, cb) {
      if (els) {
        if (isArray(els)) {
          for (var i = 0; i < els.length; i++) {
            traverseElement(els[i], cb);
          }
        } else {
          traverseElement(els, cb);
        }
      }
    } // Register built-in shapes. These shapes might be overwritten
    // by users, although we do not recommend that.

    registerShape('circle', Circle);
    registerShape('ellipse', Ellipse);
    registerShape('sector', Sector);
    registerShape('ring', Ring);
    registerShape('polygon', Polygon);
    registerShape('polyline', Polyline);
    registerShape('rect', Rect);
    registerShape('line', Line);
    registerShape('bezierCurve', BezierCurve);
    registerShape('arc', Arc);

    var graphic = /*#__PURE__*/Object.freeze({
        __proto__: null,
        updateProps: updateProps,
        initProps: initProps,
        removeElement: removeElement,
        removeElementWithFadeOut: removeElementWithFadeOut,
        isElementRemoved: isElementRemoved,
        extendShape: extendShape,
        extendPath: extendPath,
        registerShape: registerShape,
        getShapeClass: getShapeClass,
        makePath: makePath,
        makeImage: makeImage,
        mergePath: mergePath$1,
        resizePath: resizePath,
        subPixelOptimizeLine: subPixelOptimizeLine$1,
        subPixelOptimizeRect: subPixelOptimizeRect$1,
        subPixelOptimize: subPixelOptimize$1,
        getTransform: getTransform,
        applyTransform: applyTransform$1,
        transformDirection: transformDirection,
        groupTransition: groupTransition,
        clipPointsByRect: clipPointsByRect,
        clipRectByRect: clipRectByRect,
        createIcon: createIcon,
        linePolygonIntersect: linePolygonIntersect,
        lineLineIntersect: lineLineIntersect,
        setTooltipConfig: setTooltipConfig,
        traverseElements: traverseElements,
        Group: Group,
        Image: ZRImage,
        Text: ZRText,
        Circle: Circle,
        Ellipse: Ellipse,
        Sector: Sector,
        Ring: Ring,
        Polygon: Polygon,
        Polyline: Polyline,
        Rect: Rect,
        Line: Line,
        BezierCurve: BezierCurve,
        Arc: Arc,
        IncrementalDisplayable: IncrementalDisplayable,
        CompoundPath: CompoundPath,
        LinearGradient: LinearGradient,
        RadialGradient: RadialGradient,
        BoundingRect: BoundingRect,
        OrientedBoundingRect: OrientedBoundingRect,
        Point: Point,
        Path: Path
    });

    var EMPTY_OBJ = {};
    function setLabelText(label, labelTexts) {
      for (var i = 0; i < SPECIAL_STATES.length; i++) {
        var stateName = SPECIAL_STATES[i];
        var text = labelTexts[stateName];
        var state = label.ensureState(stateName);
        state.style = state.style || {};
        state.style.text = text;
      }

      var oldStates = label.currentStates.slice();
      label.clearStates(true);
      label.setStyle({
        text: labelTexts.normal
      });
      label.useStates(oldStates, true);
    }

    function getLabelText(opt, stateModels, interpolatedValue) {
      var labelFetcher = opt.labelFetcher;
      var labelDataIndex = opt.labelDataIndex;
      var labelDimIndex = opt.labelDimIndex;
      var normalModel = stateModels.normal;
      var baseText;

      if (labelFetcher) {
        baseText = labelFetcher.getFormattedLabel(labelDataIndex, 'normal', null, labelDimIndex, normalModel && normalModel.get('formatter'), interpolatedValue != null ? {
          interpolatedValue: interpolatedValue
        } : null);
      }

      if (baseText == null) {
        baseText = isFunction(opt.defaultText) ? opt.defaultText(labelDataIndex, opt, interpolatedValue) : opt.defaultText;
      }

      var statesText = {
        normal: baseText
      };

      for (var i = 0; i < SPECIAL_STATES.length; i++) {
        var stateName = SPECIAL_STATES[i];
        var stateModel = stateModels[stateName];
        statesText[stateName] = retrieve2(labelFetcher ? labelFetcher.getFormattedLabel(labelDataIndex, stateName, null, labelDimIndex, stateModel && stateModel.get('formatter')) : null, baseText);
      }

      return statesText;
    }

    function setLabelStyle(targetEl, labelStatesModels, opt, stateSpecified // TODO specified position?
    ) {
      opt = opt || EMPTY_OBJ;
      var isSetOnText = targetEl instanceof ZRText;
      var needsCreateText = false;

      for (var i = 0; i < DISPLAY_STATES.length; i++) {
        var stateModel = labelStatesModels[DISPLAY_STATES[i]];

        if (stateModel && stateModel.getShallow('show')) {
          needsCreateText = true;
          break;
        }
      }

      var textContent = isSetOnText ? targetEl : targetEl.getTextContent();

      if (needsCreateText) {
        if (!isSetOnText) {
          // Reuse the previous
          if (!textContent) {
            textContent = new ZRText();
            targetEl.setTextContent(textContent);
          } // Use same state proxy


          if (targetEl.stateProxy) {
            textContent.stateProxy = targetEl.stateProxy;
          }
        }

        var labelStatesTexts = getLabelText(opt, labelStatesModels);
        var normalModel = labelStatesModels.normal;
        var showNormal = !!normalModel.getShallow('show');
        var normalStyle = createTextStyle(normalModel, stateSpecified && stateSpecified.normal, opt, false, !isSetOnText);
        normalStyle.text = labelStatesTexts.normal;

        if (!isSetOnText) {
          // Always create new
          targetEl.setTextConfig(createTextConfig(normalModel, opt, false));
        }

        for (var i = 0; i < SPECIAL_STATES.length; i++) {
          var stateName = SPECIAL_STATES[i];
          var stateModel = labelStatesModels[stateName];

          if (stateModel) {
            var stateObj = textContent.ensureState(stateName);
            var stateShow = !!retrieve2(stateModel.getShallow('show'), showNormal);

            if (stateShow !== showNormal) {
              stateObj.ignore = !stateShow;
            }

            stateObj.style = createTextStyle(stateModel, stateSpecified && stateSpecified[stateName], opt, true, !isSetOnText);
            stateObj.style.text = labelStatesTexts[stateName];

            if (!isSetOnText) {
              var targetElEmphasisState = targetEl.ensureState(stateName);
              targetElEmphasisState.textConfig = createTextConfig(stateModel, opt, true);
            }
          }
        } // PENDING: if there is many requirements that emphasis position
        // need to be different from normal position, we might consider
        // auto silent is those cases.


        textContent.silent = !!normalModel.getShallow('silent'); // Keep x and y

        if (textContent.style.x != null) {
          normalStyle.x = textContent.style.x;
        }

        if (textContent.style.y != null) {
          normalStyle.y = textContent.style.y;
        }

        textContent.ignore = !showNormal; // Always create new style.

        textContent.useStyle(normalStyle);
        textContent.dirty();

        if (opt.enableTextSetter) {
          labelInner(textContent).setLabelText = function (interpolatedValue) {
            var labelStatesTexts = getLabelText(opt, labelStatesModels, interpolatedValue);
            setLabelText(textContent, labelStatesTexts);
          };
        }
      } else if (textContent) {
        // Not display rich text.
        textContent.ignore = true;
      }

      targetEl.dirty();
    }
    function getLabelStatesModels(itemModel, labelName) {
      labelName = labelName || 'label';
      var statesModels = {
        normal: itemModel.getModel(labelName)
      };

      for (var i = 0; i < SPECIAL_STATES.length; i++) {
        var stateName = SPECIAL_STATES[i];
        statesModels[stateName] = itemModel.getModel([stateName, labelName]);
      }

      return statesModels;
    }
    /**
     * Set basic textStyle properties.
     */

    function createTextStyle(textStyleModel, specifiedTextStyle, // Fixed style in the code. Can't be set by model.
    opt, isNotNormal, isAttached // If text is attached on an element. If so, auto color will handling in zrender.
    ) {
      var textStyle = {};
      setTextStyleCommon(textStyle, textStyleModel, opt, isNotNormal, isAttached);
      specifiedTextStyle && extend(textStyle, specifiedTextStyle); // textStyle.host && textStyle.host.dirty && textStyle.host.dirty(false);

      return textStyle;
    }
    function createTextConfig(textStyleModel, opt, isNotNormal) {
      opt = opt || {};
      var textConfig = {};
      var labelPosition;
      var labelRotate = textStyleModel.getShallow('rotate');
      var labelDistance = retrieve2(textStyleModel.getShallow('distance'), isNotNormal ? null : 5);
      var labelOffset = textStyleModel.getShallow('offset');
      labelPosition = textStyleModel.getShallow('position') || (isNotNormal ? null : 'inside'); // 'outside' is not a valid zr textPostion value, but used
      // in bar series, and magric type should be considered.

      labelPosition === 'outside' && (labelPosition = opt.defaultOutsidePosition || 'top');

      if (labelPosition != null) {
        textConfig.position = labelPosition;
      }

      if (labelOffset != null) {
        textConfig.offset = labelOffset;
      }

      if (labelRotate != null) {
        labelRotate *= Math.PI / 180;
        textConfig.rotation = labelRotate;
      }

      if (labelDistance != null) {
        textConfig.distance = labelDistance;
      } // fill and auto is determined by the color of path fill if it's not specified by developers.


      textConfig.outsideFill = textStyleModel.get('color') === 'inherit' ? opt.inheritColor || null : 'auto';
      return textConfig;
    }
    /**
     * The uniform entry of set text style, that is, retrieve style definitions
     * from `model` and set to `textStyle` object.
     *
     * Never in merge mode, but in overwrite mode, that is, all of the text style
     * properties will be set. (Consider the states of normal and emphasis and
     * default value can be adopted, merge would make the logic too complicated
     * to manage.)
     */

    function setTextStyleCommon(textStyle, textStyleModel, opt, isNotNormal, isAttached) {
      // Consider there will be abnormal when merge hover style to normal style if given default value.
      opt = opt || EMPTY_OBJ;
      var ecModel = textStyleModel.ecModel;
      var globalTextStyle = ecModel && ecModel.option.textStyle; // Consider case:
      // {
      //     data: [{
      //         value: 12,
      //         label: {
      //             rich: {
      //                 // no 'a' here but using parent 'a'.
      //             }
      //         }
      //     }],
      //     rich: {
      //         a: { ... }
      //     }
      // }

      var richItemNames = getRichItemNames(textStyleModel);
      var richResult;

      if (richItemNames) {
        richResult = {};

        for (var name_1 in richItemNames) {
          if (richItemNames.hasOwnProperty(name_1)) {
            // Cascade is supported in rich.
            var richTextStyle = textStyleModel.getModel(['rich', name_1]); // In rich, never `disableBox`.
            // FIXME: consider `label: {formatter: '{a|xx}', color: 'blue', rich: {a: {}}}`,
            // the default color `'blue'` will not be adopted if no color declared in `rich`.
            // That might confuses users. So probably we should put `textStyleModel` as the
            // root ancestor of the `richTextStyle`. But that would be a break change.

            setTokenTextStyle(richResult[name_1] = {}, richTextStyle, globalTextStyle, opt, isNotNormal, isAttached, false, true);
          }
        }
      }

      if (richResult) {
        textStyle.rich = richResult;
      }

      var overflow = textStyleModel.get('overflow');

      if (overflow) {
        textStyle.overflow = overflow;
      }

      var margin = textStyleModel.get('minMargin');

      if (margin != null) {
        textStyle.margin = margin;
      }

      setTokenTextStyle(textStyle, textStyleModel, globalTextStyle, opt, isNotNormal, isAttached, true, false);
    } // Consider case:
    // {
    //     data: [{
    //         value: 12,
    //         label: {
    //             rich: {
    //                 // no 'a' here but using parent 'a'.
    //             }
    //         }
    //     }],
    //     rich: {
    //         a: { ... }
    //     }
    // }
    // TODO TextStyleModel


    function getRichItemNames(textStyleModel) {
      // Use object to remove duplicated names.
      var richItemNameMap;

      while (textStyleModel && textStyleModel !== textStyleModel.ecModel) {
        var rich = (textStyleModel.option || EMPTY_OBJ).rich;

        if (rich) {
          richItemNameMap = richItemNameMap || {};
          var richKeys = keys(rich);

          for (var i = 0; i < richKeys.length; i++) {
            var richKey = richKeys[i];
            richItemNameMap[richKey] = 1;
          }
        }

        textStyleModel = textStyleModel.parentModel;
      }

      return richItemNameMap;
    }

    var TEXT_PROPS_WITH_GLOBAL = ['fontStyle', 'fontWeight', 'fontSize', 'fontFamily', 'textShadowColor', 'textShadowBlur', 'textShadowOffsetX', 'textShadowOffsetY'];
    var TEXT_PROPS_SELF = ['align', 'lineHeight', 'width', 'height', 'tag', 'verticalAlign', 'ellipsis'];
    var TEXT_PROPS_BOX = ['padding', 'borderWidth', 'borderRadius', 'borderDashOffset', 'backgroundColor', 'borderColor', 'shadowColor', 'shadowBlur', 'shadowOffsetX', 'shadowOffsetY'];

    function setTokenTextStyle(textStyle, textStyleModel, globalTextStyle, opt, isNotNormal, isAttached, isBlock, inRich) {
      // In merge mode, default value should not be given.
      globalTextStyle = !isNotNormal && globalTextStyle || EMPTY_OBJ;
      var inheritColor = opt && opt.inheritColor;
      var fillColor = textStyleModel.getShallow('color');
      var strokeColor = textStyleModel.getShallow('textBorderColor');
      var opacity = retrieve2(textStyleModel.getShallow('opacity'), globalTextStyle.opacity);

      if (fillColor === 'inherit' || fillColor === 'auto') {
        if ("development" !== 'production') {
          if (fillColor === 'auto') {
            deprecateReplaceLog('color: \'auto\'', 'color: \'inherit\'');
          }
        }

        if (inheritColor) {
          fillColor = inheritColor;
        } else {
          fillColor = null;
        }
      }

      if (strokeColor === 'inherit' || strokeColor === 'auto') {
        if ("development" !== 'production') {
          if (strokeColor === 'auto') {
            deprecateReplaceLog('color: \'auto\'', 'color: \'inherit\'');
          }
        }

        if (inheritColor) {
          strokeColor = inheritColor;
        } else {
          strokeColor = null;
        }
      }

      if (!isAttached) {
        // Only use default global textStyle.color if text is individual.
        // Otherwise it will use the strategy of attached text color because text may be on a path.
        fillColor = fillColor || globalTextStyle.color;
        strokeColor = strokeColor || globalTextStyle.textBorderColor;
      }

      if (fillColor != null) {
        textStyle.fill = fillColor;
      }

      if (strokeColor != null) {
        textStyle.stroke = strokeColor;
      }

      var textBorderWidth = retrieve2(textStyleModel.getShallow('textBorderWidth'), globalTextStyle.textBorderWidth);

      if (textBorderWidth != null) {
        textStyle.lineWidth = textBorderWidth;
      }

      var textBorderType = retrieve2(textStyleModel.getShallow('textBorderType'), globalTextStyle.textBorderType);

      if (textBorderType != null) {
        textStyle.lineDash = textBorderType;
      }

      var textBorderDashOffset = retrieve2(textStyleModel.getShallow('textBorderDashOffset'), globalTextStyle.textBorderDashOffset);

      if (textBorderDashOffset != null) {
        textStyle.lineDashOffset = textBorderDashOffset;
      }

      if (!isNotNormal && opacity == null && !inRich) {
        opacity = opt && opt.defaultOpacity;
      }

      if (opacity != null) {
        textStyle.opacity = opacity;
      } // TODO


      if (!isNotNormal && !isAttached) {
        // Set default finally.
        if (textStyle.fill == null && opt.inheritColor) {
          textStyle.fill = opt.inheritColor;
        }
      } // Do not use `getFont` here, because merge should be supported, where
      // part of these properties may be changed in emphasis style, and the
      // others should remain their original value got from normal style.


      for (var i = 0; i < TEXT_PROPS_WITH_GLOBAL.length; i++) {
        var key = TEXT_PROPS_WITH_GLOBAL[i];
        var val = retrieve2(textStyleModel.getShallow(key), globalTextStyle[key]);

        if (val != null) {
          textStyle[key] = val;
        }
      }

      for (var i = 0; i < TEXT_PROPS_SELF.length; i++) {
        var key = TEXT_PROPS_SELF[i];
        var val = textStyleModel.getShallow(key);

        if (val != null) {
          textStyle[key] = val;
        }
      }

      if (textStyle.verticalAlign == null) {
        var baseline = textStyleModel.getShallow('baseline');

        if (baseline != null) {
          textStyle.verticalAlign = baseline;
        }
      }

      if (!isBlock || !opt.disableBox) {
        for (var i = 0; i < TEXT_PROPS_BOX.length; i++) {
          var key = TEXT_PROPS_BOX[i];
          var val = textStyleModel.getShallow(key);

          if (val != null) {
            textStyle[key] = val;
          }
        }

        var borderType = textStyleModel.getShallow('borderType');

        if (borderType != null) {
          textStyle.borderDash = borderType;
        }

        if ((textStyle.backgroundColor === 'auto' || textStyle.backgroundColor === 'inherit') && inheritColor) {
          if ("development" !== 'production') {
            if (textStyle.backgroundColor === 'auto') {
              deprecateReplaceLog('backgroundColor: \'auto\'', 'backgroundColor: \'inherit\'');
            }
          }

          textStyle.backgroundColor = inheritColor;
        }

        if ((textStyle.borderColor === 'auto' || textStyle.borderColor === 'inherit') && inheritColor) {
          if ("development" !== 'production') {
            if (textStyle.borderColor === 'auto') {
              deprecateReplaceLog('borderColor: \'auto\'', 'borderColor: \'inherit\'');
            }
          }

          textStyle.borderColor = inheritColor;
        }
      }
    }

    function getFont(opt, ecModel) {
      var gTextStyleModel = ecModel && ecModel.getModel('textStyle');
      return trim([// FIXME in node-canvas fontWeight is before fontStyle
      opt.fontStyle || gTextStyleModel && gTextStyleModel.getShallow('fontStyle') || '', opt.fontWeight || gTextStyleModel && gTextStyleModel.getShallow('fontWeight') || '', (opt.fontSize || gTextStyleModel && gTextStyleModel.getShallow('fontSize') || 12) + 'px', opt.fontFamily || gTextStyleModel && gTextStyleModel.getShallow('fontFamily') || 'sans-serif'].join(' '));
    }
    var labelInner = makeInner();
    function setLabelValueAnimation(label, labelStatesModels, value, getDefaultText) {
      if (!label) {
        return;
      }

      var obj = labelInner(label);
      obj.prevValue = obj.value;
      obj.value = value;
      var normalLabelModel = labelStatesModels.normal;
      obj.valueAnimation = normalLabelModel.get('valueAnimation');

      if (obj.valueAnimation) {
        obj.precision = normalLabelModel.get('precision');
        obj.defaultInterpolatedText = getDefaultText;
        obj.statesModels = labelStatesModels;
      }
    }

    var PATH_COLOR = ['textStyle', 'color'];
    var textStyleParams = ['fontStyle', 'fontWeight', 'fontSize', 'fontFamily', 'padding', 'lineHeight', 'rich', 'width', 'height', 'overflow']; // TODO Performance improvement?

    var tmpText = new ZRText();

    var TextStyleMixin =
    /** @class */
    function () {
      function TextStyleMixin() {}
      /**
       * Get color property or get color from option.textStyle.color
       */
      // TODO Callback


      TextStyleMixin.prototype.getTextColor = function (isEmphasis) {
        var ecModel = this.ecModel;
        return this.getShallow('color') || (!isEmphasis && ecModel ? ecModel.get(PATH_COLOR) : null);
      };
      /**
       * Create font string from fontStyle, fontWeight, fontSize, fontFamily
       * @return {string}
       */


      TextStyleMixin.prototype.getFont = function () {
        return getFont({
          fontStyle: this.getShallow('fontStyle'),
          fontWeight: this.getShallow('fontWeight'),
          fontSize: this.getShallow('fontSize'),
          fontFamily: this.getShallow('fontFamily')
        }, this.ecModel);
      };

      TextStyleMixin.prototype.getTextRect = function (text) {
        var style = {
          text: text,
          verticalAlign: this.getShallow('verticalAlign') || this.getShallow('baseline')
        };

        for (var i = 0; i < textStyleParams.length; i++) {
          style[textStyleParams[i]] = this.getShallow(textStyleParams[i]);
        }

        tmpText.useStyle(style);
        tmpText.update();
        return tmpText.getBoundingRect();
      };

      return TextStyleMixin;
    }();

    var LINE_STYLE_KEY_MAP = [['lineWidth', 'width'], ['stroke', 'color'], ['opacity'], ['shadowBlur'], ['shadowOffsetX'], ['shadowOffsetY'], ['shadowColor'], ['lineDash', 'type'], ['lineDashOffset', 'dashOffset'], ['lineCap', 'cap'], ['lineJoin', 'join'], ['miterLimit'] // Option decal is in `DecalObject` but style.decal is in `PatternObject`.
    // So do not transfer decal directly.
    ];
    var getLineStyle = makeStyleMapper(LINE_STYLE_KEY_MAP);

    var LineStyleMixin =
    /** @class */
    function () {
      function LineStyleMixin() {}

      LineStyleMixin.prototype.getLineStyle = function (excludes) {
        return getLineStyle(this, excludes);
      };

      return LineStyleMixin;
    }();

    var ITEM_STYLE_KEY_MAP = [['fill', 'color'], ['stroke', 'borderColor'], ['lineWidth', 'borderWidth'], ['opacity'], ['shadowBlur'], ['shadowOffsetX'], ['shadowOffsetY'], ['shadowColor'], ['lineDash', 'borderType'], ['lineDashOffset', 'borderDashOffset'], ['lineCap', 'borderCap'], ['lineJoin', 'borderJoin'], ['miterLimit', 'borderMiterLimit'] // Option decal is in `DecalObject` but style.decal is in `PatternObject`.
    // So do not transfer decal directly.
    ];
    var getItemStyle = makeStyleMapper(ITEM_STYLE_KEY_MAP);

    var ItemStyleMixin =
    /** @class */
    function () {
      function ItemStyleMixin() {}

      ItemStyleMixin.prototype.getItemStyle = function (excludes, includes) {
        return getItemStyle(this, excludes, includes);
      };

      return ItemStyleMixin;
    }();

    var Model =
    /** @class */
    function () {
      function Model(option, parentModel, ecModel) {
        this.parentModel = parentModel;
        this.ecModel = ecModel;
        this.option = option; // Simple optimization
        // if (this.init) {
        //     if (arguments.length <= 4) {
        //         this.init(option, parentModel, ecModel, extraOpt);
        //     }
        //     else {
        //         this.init.apply(this, arguments);
        //     }
        // }
      }

      Model.prototype.init = function (option, parentModel, ecModel) {
        var rest = [];

        for (var _i = 3; _i < arguments.length; _i++) {
          rest[_i - 3] = arguments[_i];
        }
      };
      /**
       * Merge the input option to me.
       */


      Model.prototype.mergeOption = function (option, ecModel) {
        merge(this.option, option, true);
      }; // `path` can be 'a.b.c', so the return value type have to be `ModelOption`
      // TODO: TYPE strict key check?
      // get(path: string | string[], ignoreParent?: boolean): ModelOption;


      Model.prototype.get = function (path, ignoreParent) {
        if (path == null) {
          return this.option;
        }

        return this._doGet(this.parsePath(path), !ignoreParent && this.parentModel);
      };

      Model.prototype.getShallow = function (key, ignoreParent) {
        var option = this.option;
        var val = option == null ? option : option[key];

        if (val == null && !ignoreParent) {
          var parentModel = this.parentModel;

          if (parentModel) {
            // FIXME:TS do not know how to make it works
            val = parentModel.getShallow(key);
          }
        }

        return val;
      }; // `path` can be 'a.b.c', so the return value type have to be `Model<ModelOption>`
      // getModel(path: string | string[], parentModel?: Model): Model;
      // TODO 'a.b.c' is deprecated


      Model.prototype.getModel = function (path, parentModel) {
        var hasPath = path != null;
        var pathFinal = hasPath ? this.parsePath(path) : null;
        var obj = hasPath ? this._doGet(pathFinal) : this.option;
        parentModel = parentModel || this.parentModel && this.parentModel.getModel(this.resolveParentPath(pathFinal));
        return new Model(obj, parentModel, this.ecModel);
      };
      /**
       * If model has option
       */


      Model.prototype.isEmpty = function () {
        return this.option == null;
      };

      Model.prototype.restoreData = function () {}; // Pending


      Model.prototype.clone = function () {
        var Ctor = this.constructor;
        return new Ctor(clone(this.option));
      }; // setReadOnly(properties): void {
      // clazzUtil.setReadOnly(this, properties);
      // }
      // If path is null/undefined, return null/undefined.


      Model.prototype.parsePath = function (path) {
        if (typeof path === 'string') {
          return path.split('.');
        }

        return path;
      }; // Resolve path for parent. Perhaps useful when parent use a different property.
      // Default to be a identity resolver.
      // Can be modified to a different resolver.


      Model.prototype.resolveParentPath = function (path) {
        return path;
      }; // FIXME:TS check whether put this method here


      Model.prototype.isAnimationEnabled = function () {
        if (!env.node && this.option) {
          if (this.option.animation != null) {
            return !!this.option.animation;
          } else if (this.parentModel) {
            return this.parentModel.isAnimationEnabled();
          }
        }
      };

      Model.prototype._doGet = function (pathArr, parentModel) {
        var obj = this.option;

        if (!pathArr) {
          return obj;
        }

        for (var i = 0; i < pathArr.length; i++) {
          // Ignore empty
          if (!pathArr[i]) {
            continue;
          } // obj could be number/string/... (like 0)


          obj = obj && typeof obj === 'object' ? obj[pathArr[i]] : null;

          if (obj == null) {
            break;
          }
        }

        if (obj == null && parentModel) {
          obj = parentModel._doGet(this.resolveParentPath(pathArr), parentModel.parentModel);
        }

        return obj;
      };

      return Model;
    }();

    enableClassExtend(Model);
    enableClassCheck(Model);
    mixin(Model, LineStyleMixin);
    mixin(Model, ItemStyleMixin);
    mixin(Model, AreaStyleMixin);
    mixin(Model, TextStyleMixin);

    var base = Math.round(Math.random() * 10);
    /**
     * @public
     * @param {string} type
     * @return {string}
     */

    function getUID(type) {
      // Considering the case of crossing js context,
      // use Math.random to make id as unique as possible.
      return [type || '', base++].join('_');
    }
    /**
     * Implements `SubTypeDefaulterManager` for `target`.
     */

    function enableSubTypeDefaulter(target) {
      var subTypeDefaulters = {};

      target.registerSubTypeDefaulter = function (componentType, defaulter) {
        var componentTypeInfo = parseClassType(componentType);
        subTypeDefaulters[componentTypeInfo.main] = defaulter;
      };

      target.determineSubType = function (componentType, option) {
        var type = option.type;

        if (!type) {
          var componentTypeMain = parseClassType(componentType).main;

          if (target.hasSubTypes(componentType) && subTypeDefaulters[componentTypeMain]) {
            type = subTypeDefaulters[componentTypeMain](option);
          }
        }

        return type;
      };
    }
    /**
     * Implements `TopologicalTravelable<any>` for `entity`.
     *
     * Topological travel on Activity Network (Activity On Vertices).
     * Dependencies is defined in Model.prototype.dependencies, like ['xAxis', 'yAxis'].
     * If 'xAxis' or 'yAxis' is absent in componentTypeList, just ignore it in topology.
     * If there is circular dependencey, Error will be thrown.
     */

    function enableTopologicalTravel(entity, dependencyGetter) {
      /**
       * @param targetNameList Target Component type list.
       *                       Can be ['aa', 'bb', 'aa.xx']
       * @param fullNameList By which we can build dependency graph.
       * @param callback Params: componentType, dependencies.
       * @param context Scope of callback.
       */
      entity.topologicalTravel = function (targetNameList, fullNameList, callback, context) {
        if (!targetNameList.length) {
          return;
        }

        var result = makeDepndencyGraph(fullNameList);
        var graph = result.graph;
        var noEntryList = result.noEntryList;
        var targetNameSet = {};
        each(targetNameList, function (name) {
          targetNameSet[name] = true;
        });

        while (noEntryList.length) {
          var currComponentType = noEntryList.pop();
          var currVertex = graph[currComponentType];
          var isInTargetNameSet = !!targetNameSet[currComponentType];

          if (isInTargetNameSet) {
            callback.call(context, currComponentType, currVertex.originalDeps.slice());
            delete targetNameSet[currComponentType];
          }

          each(currVertex.successor, isInTargetNameSet ? removeEdgeAndAdd : removeEdge);
        }

        each(targetNameSet, function () {
          var errMsg = '';

          if ("development" !== 'production') {
            errMsg = makePrintable('Circular dependency may exists: ', targetNameSet, targetNameList, fullNameList);
          }

          throw new Error(errMsg);
        });

        function removeEdge(succComponentType) {
          graph[succComponentType].entryCount--;

          if (graph[succComponentType].entryCount === 0) {
            noEntryList.push(succComponentType);
          }
        } // Consider this case: legend depends on series, and we call
        // chart.setOption({series: [...]}), where only series is in option.
        // If we do not have 'removeEdgeAndAdd', legendModel.mergeOption will
        // not be called, but only sereis.mergeOption is called. Thus legend
        // have no chance to update its local record about series (like which
        // name of series is available in legend).


        function removeEdgeAndAdd(succComponentType) {
          targetNameSet[succComponentType] = true;
          removeEdge(succComponentType);
        }
      };

      function makeDepndencyGraph(fullNameList) {
        var graph = {};
        var noEntryList = [];
        each(fullNameList, function (name) {
          var thisItem = createDependencyGraphItem(graph, name);
          var originalDeps = thisItem.originalDeps = dependencyGetter(name);
          var availableDeps = getAvailableDependencies(originalDeps, fullNameList);
          thisItem.entryCount = availableDeps.length;

          if (thisItem.entryCount === 0) {
            noEntryList.push(name);
          }

          each(availableDeps, function (dependentName) {
            if (indexOf(thisItem.predecessor, dependentName) < 0) {
              thisItem.predecessor.push(dependentName);
            }

            var thatItem = createDependencyGraphItem(graph, dependentName);

            if (indexOf(thatItem.successor, dependentName) < 0) {
              thatItem.successor.push(name);
            }
          });
        });
        return {
          graph: graph,
          noEntryList: noEntryList
        };
      }

      function createDependencyGraphItem(graph, name) {
        if (!graph[name]) {
          graph[name] = {
            predecessor: [],
            successor: []
          };
        }

        return graph[name];
      }

      function getAvailableDependencies(originalDeps, fullNameList) {
        var availableDeps = [];
        each(originalDeps, function (dep) {
          indexOf(fullNameList, dep) >= 0 && availableDeps.push(dep);
        });
        return availableDeps;
      }
    }
    function inheritDefaultOption(superOption, subOption) {
      // See also `model/Component.ts#getDefaultOption`
      return merge(merge({}, superOption, true), subOption, true);
    }

    /*
    * Licensed to the Apache Software Foundation (ASF) under one
    * or more contributor license agreements.  See the NOTICE file
    * distributed with this work for additional information
    * regarding copyright ownership.  The ASF licenses this file
    * to you under the Apache License, Version 2.0 (the
    * "License"); you may not use this file except in compliance
    * with the License.  You may obtain a copy of the License at
    *
    *   http://www.apache.org/licenses/LICENSE-2.0
    *
    * Unless required by applicable law or agreed to in writing,
    * software distributed under the License is distributed on an
    * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
    * KIND, either express or implied.  See the License for the
    * specific language governing permissions and limitations
    * under the License.
    */


    /**
     * AUTO-GENERATED FILE. DO NOT MODIFY.
     */

    /*
     * Licensed to the Apache Software Foundation (ASF) under one
     * or more contributor license agreements.  See the NOTICE file
     * distributed with this work for additional information
     * regarding copyright ownership.  The ASF licenses this file
     * to you under the Apache License, Version 2.0 (the
     * "License"); you may not use this file except in compliance
     * with the License.  You may obtain a copy of the License at
     *
     *   http://www.apache.org/licenses/LICENSE-2.0
     *
     * Unless required by applicable law or agreed to in writing,
     * software distributed under the License is distributed on an
     * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
     * KIND, either express or implied.  See the License for the
     * specific language governing permissions and limitations
     * under the License.
     */

    /**
     * Language: English.
     */
    var langEN = {
      time: {
        month: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
        monthAbbr: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        dayOfWeek: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
        dayOfWeekAbbr: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']
      },
      legend: {
        selector: {
          all: 'All',
          inverse: 'Inv'
        }
      },
      toolbox: {
        brush: {
          title: {
            rect: 'Box Select',
            polygon: 'Lasso Select',
            lineX: 'Horizontally Select',
            lineY: 'Vertically Select',
            keep: 'Keep Selections',
            clear: 'Clear Selections'
          }
        },
        dataView: {
          title: 'Data View',
          lang: ['Data View', 'Close', 'Refresh']
        },
        dataZoom: {
          title: {
            zoom: 'Zoom',
            back: 'Zoom Reset'
          }
        },
        magicType: {
          title: {
            line: 'Switch to Line Chart',
            bar: 'Switch to Bar Chart',
            stack: 'Stack',
            tiled: 'Tile'
          }
        },
        restore: {
          title: 'Restore'
        },
        saveAsImage: {
          title: 'Save as Image',
          lang: ['Right Click to Save Image']
        }
      },
      series: {
        typeNames: {
          pie: 'Pie chart',
          bar: 'Bar chart',
          line: 'Line chart',
          scatter: 'Scatter plot',
          effectScatter: 'Ripple scatter plot',
          radar: 'Radar chart',
          tree: 'Tree',
          treemap: 'Treemap',
          boxplot: 'Boxplot',
          candlestick: 'Candlestick',
          k: 'K line chart',
          heatmap: 'Heat map',
          map: 'Map',
          parallel: 'Parallel coordinate map',
          lines: 'Line graph',
          graph: 'Relationship graph',
          sankey: 'Sankey diagram',
          funnel: 'Funnel chart',
          gauge: 'Gauge',
          pictorialBar: 'Pictorial bar',
          themeRiver: 'Theme River Map',
          sunburst: 'Sunburst'
        }
      },
      aria: {
        general: {
          withTitle: 'This is a chart about "{title}"',
          withoutTitle: 'This is a chart'
        },
        series: {
          single: {
            prefix: '',
            withName: ' with type {seriesType} named {seriesName}.',
            withoutName: ' with type {seriesType}.'
          },
          multiple: {
            prefix: '. It consists of {seriesCount} series count.',
            withName: ' The {seriesId} series is a {seriesType} representing {seriesName}.',
            withoutName: ' The {seriesId} series is a {seriesType}.',
            separator: {
              middle: '',
              end: ''
            }
          }
        },
        data: {
          allData: 'The data is as follows: ',
          partialData: 'The first {displayCnt} items are: ',
          withName: 'the data for {name} is {value}',
          withoutName: '{value}',
          separator: {
            middle: ', ',
            end: '. '
          }
        }
      }
    };

    /*
    * Licensed to the Apache Software Foundation (ASF) under one
    * or more contributor license agreements.  See the NOTICE file
    * distributed with this work for additional information
    * regarding copyright ownership.  The ASF licenses this file
    * to you under the Apache License, Version 2.0 (the
    * "License"); you may not use this file except in compliance
    * with the License.  You may obtain a copy of the License at
    *
    *   http://www.apache.org/licenses/LICENSE-2.0
    *
    * Unless required by applicable law or agreed to in writing,
    * software distributed under the License is distributed on an
    * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
    * KIND, either express or implied.  See the License for the
    * specific language governing permissions and limitations
    * under the License.
    */


    /**
     * AUTO-GENERATED FILE. DO NOT MODIFY.
     */

    /*
     * Licensed to the Apache Software Foundation (ASF) under one
     * or more contributor license agreements.  See the NOTICE file
     * distributed with this work for additional information
     * regarding copyright ownership.  The ASF licenses this file
     * to you under the Apache License, Version 2.0 (the
     * "License"); you may not use this file except in compliance
     * with the License.  You may obtain a copy of the License at
     *
     *   http://www.apache.org/licenses/LICENSE-2.0
     *
     * Unless required by applicable law or agreed to in writing,
     * software distributed under the License is distributed on an
     * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
     * KIND, either express or implied.  See the License for the
     * specific language governing permissions and limitations
     * under the License.
     */
    var langZH = {
      time: {
        month: ['一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月'],
        monthAbbr: ['1月', '2月', '3月', '4月', '5月', '6月', '7月', '8月', '9月', '10月', '11月', '12月'],
        dayOfWeek: ['星期日', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六'],
        dayOfWeekAbbr: ['日', '一', '二', '三', '四', '五', '六']
      },
      legend: {
        selector: {
          all: '全选',
          inverse: '反选'
        }
      },
      toolbox: {
        brush: {
          title: {
            rect: '矩形选择',
            polygon: '圈选',
            lineX: '横向选择',
            lineY: '纵向选择',
            keep: '保持选择',
            clear: '清除选择'
          }
        },
        dataView: {
          title: '数据视图',
          lang: ['数据视图', '关闭', '刷新']
        },
        dataZoom: {
          title: {
            zoom: '区域缩放',
            back: '区域缩放还原'
          }
        },
        magicType: {
          title: {
            line: '切换为折线图',
            bar: '切换为柱状图',
            stack: '切换为堆叠',
            tiled: '切换为平铺'
          }
        },
        restore: {
          title: '还原'
        },
        saveAsImage: {
          title: '保存为图片',
          lang: ['右键另存为图片']
        }
      },
      series: {
        typeNames: {
          pie: '饼图',
          bar: '柱状图',
          line: '折线图',
          scatter: '散点图',
          effectScatter: '涟漪散点图',
          radar: '雷达图',
          tree: '树图',
          treemap: '矩形树图',
          boxplot: '箱型图',
          candlestick: 'K线图',
          k: 'K线图',
          heatmap: '热力图',
          map: '地图',
          parallel: '平行坐标图',
          lines: '线图',
          graph: '关系图',
          sankey: '桑基图',
          funnel: '漏斗图',
          gauge: '仪表盘图',
          pictorialBar: '象形柱图',
          themeRiver: '主题河流图',
          sunburst: '旭日图'
        }
      },
      aria: {
        general: {
          withTitle: '这是一个关于“{title}”的图表。',
          withoutTitle: '这是一个图表，'
        },
        series: {
          single: {
            prefix: '',
            withName: '图表类型是{seriesType}，表示{seriesName}。',
            withoutName: '图表类型是{seriesType}。'
          },
          multiple: {
            prefix: '它由{seriesCount}个图表系列组成。',
            withName: '第{seriesId}个系列是一个表示{seriesName}的{seriesType}，',
            withoutName: '第{seriesId}个系列是一个{seriesType}，',
            separator: {
              middle: '；',
              end: '。'
            }
          }
        },
        data: {
          allData: '其数据是——',
          partialData: '其中，前{displayCnt}项是——',
          withName: '{name}的数据是{value}',
          withoutName: '{value}',
          separator: {
            middle: '，',
            end: ''
          }
        }
      }
    };

    var LOCALE_ZH = 'ZH';
    var LOCALE_EN = 'EN';
    var DEFAULT_LOCALE = LOCALE_EN;
    var localeStorage = {};
    var localeModels = {};
    var SYSTEM_LANG = !env.domSupported ? DEFAULT_LOCALE : function () {
      var langStr = (
      /* eslint-disable-next-line */
      document.documentElement.lang || navigator.language || navigator.browserLanguage).toUpperCase();
      return langStr.indexOf(LOCALE_ZH) > -1 ? LOCALE_ZH : DEFAULT_LOCALE;
    }();
    function registerLocale(locale, localeObj) {
      locale = locale.toUpperCase();
      localeModels[locale] = new Model(localeObj);
      localeStorage[locale] = localeObj;
    } // export function getLocale(locale: string) {
    //     return localeStorage[locale];
    // }

    function createLocaleObject(locale) {
      if (isString(locale)) {
        var localeObj = localeStorage[locale.toUpperCase()] || {};

        if (locale === LOCALE_ZH || locale === LOCALE_EN) {
          return clone(localeObj);
        } else {
          return merge(clone(localeObj), clone(localeStorage[DEFAULT_LOCALE]), false);
        }
      } else {
        return merge(clone(locale), clone(localeStorage[DEFAULT_LOCALE]), false);
      }
    }
    function getLocaleModel(lang) {
      return localeModels[lang];
    }
    function getDefaultLocaleModel() {
      return localeModels[DEFAULT_LOCALE];
    } // Default locale

    registerLocale(LOCALE_EN, langEN);
    registerLocale(LOCALE_ZH, langZH);

    var ONE_SECOND = 1000;
    var ONE_MINUTE = ONE_SECOND * 60;
    var ONE_HOUR = ONE_MINUTE * 60;
    var ONE_DAY = ONE_HOUR * 24;
    var ONE_YEAR = ONE_DAY * 365;
    var defaultLeveledFormatter = {
      year: '{yyyy}',
      month: '{MMM}',
      day: '{d}',
      hour: '{HH}:{mm}',
      minute: '{HH}:{mm}',
      second: '{HH}:{mm}:{ss}',
      millisecond: '{HH}:{mm}:{ss} {SSS}',
      none: '{yyyy}-{MM}-{dd} {HH}:{mm}:{ss} {SSS}'
    };
    var fullDayFormatter = '{yyyy}-{MM}-{dd}';
    var fullLeveledFormatter = {
      year: '{yyyy}',
      month: '{yyyy}-{MM}',
      day: fullDayFormatter,
      hour: fullDayFormatter + ' ' + defaultLeveledFormatter.hour,
      minute: fullDayFormatter + ' ' + defaultLeveledFormatter.minute,
      second: fullDayFormatter + ' ' + defaultLeveledFormatter.second,
      millisecond: defaultLeveledFormatter.none
    };
    var primaryTimeUnits = ['year', 'month', 'day', 'hour', 'minute', 'second', 'millisecond'];
    var timeUnits = ['year', 'half-year', 'quarter', 'month', 'week', 'half-week', 'day', 'half-day', 'quarter-day', 'hour', 'minute', 'second', 'millisecond'];
    function pad(str, len) {
      str += '';
      return '0000'.substr(0, len - str.length) + str;
    }
    function getPrimaryTimeUnit(timeUnit) {
      switch (timeUnit) {
        case 'half-year':
        case 'quarter':
          return 'month';

        case 'week':
        case 'half-week':
          return 'day';

        case 'half-day':
        case 'quarter-day':
          return 'hour';

        default:
          // year, minutes, second, milliseconds
          return timeUnit;
      }
    }
    function isPrimaryTimeUnit(timeUnit) {
      return timeUnit === getPrimaryTimeUnit(timeUnit);
    }
    function getDefaultFormatPrecisionOfInterval(timeUnit) {
      switch (timeUnit) {
        case 'year':
        case 'month':
          return 'day';

        case 'millisecond':
          return 'millisecond';

        default:
          // Also for day, hour, minute, second
          return 'second';
      }
    }
    function format( // Note: The result based on `isUTC` are totally different, which can not be just simply
    // substituted by the result without `isUTC`. So we make the param `isUTC` mandatory.
    time, template, isUTC, lang) {
      var date = parseDate(time);
      var y = date[fullYearGetterName(isUTC)]();
      var M = date[monthGetterName(isUTC)]() + 1;
      var q = Math.floor((M - 1) / 3) + 1;
      var d = date[dateGetterName(isUTC)]();
      var e = date['get' + (isUTC ? 'UTC' : '') + 'Day']();
      var H = date[hoursGetterName(isUTC)]();
      var h = (H - 1) % 12 + 1;
      var m = date[minutesGetterName(isUTC)]();
      var s = date[secondsGetterName(isUTC)]();
      var S = date[millisecondsGetterName(isUTC)]();
      var localeModel = lang instanceof Model ? lang : getLocaleModel(lang || SYSTEM_LANG) || getDefaultLocaleModel();
      var timeModel = localeModel.getModel('time');
      var month = timeModel.get('month');
      var monthAbbr = timeModel.get('monthAbbr');
      var dayOfWeek = timeModel.get('dayOfWeek');
      var dayOfWeekAbbr = timeModel.get('dayOfWeekAbbr');
      return (template || '').replace(/{yyyy}/g, y + '').replace(/{yy}/g, pad(y % 100 + '', 2)).replace(/{Q}/g, q + '').replace(/{MMMM}/g, month[M - 1]).replace(/{MMM}/g, monthAbbr[M - 1]).replace(/{MM}/g, pad(M, 2)).replace(/{M}/g, M + '').replace(/{dd}/g, pad(d, 2)).replace(/{d}/g, d + '').replace(/{eeee}/g, dayOfWeek[e]).replace(/{ee}/g, dayOfWeekAbbr[e]).replace(/{e}/g, e + '').replace(/{HH}/g, pad(H, 2)).replace(/{H}/g, H + '').replace(/{hh}/g, pad(h + '', 2)).replace(/{h}/g, h + '').replace(/{mm}/g, pad(m, 2)).replace(/{m}/g, m + '').replace(/{ss}/g, pad(s, 2)).replace(/{s}/g, s + '').replace(/{SSS}/g, pad(S, 3)).replace(/{S}/g, S + '');
    }
    function leveledFormat(tick, idx, formatter, lang, isUTC) {
      var template = null;

      if (isString(formatter)) {
        // Single formatter for all units at all levels
        template = formatter;
      } else if (isFunction(formatter)) {
        // Callback formatter
        template = formatter(tick.value, idx, {
          level: tick.level
        });
      } else {
        var defaults$1 = extend({}, defaultLeveledFormatter);

        if (tick.level > 0) {
          for (var i = 0; i < primaryTimeUnits.length; ++i) {
            defaults$1[primaryTimeUnits[i]] = "{primary|" + defaults$1[primaryTimeUnits[i]] + "}";
          }
        }

        var mergedFormatter = formatter ? formatter.inherit === false ? formatter // Use formatter with bigger units
        : defaults(formatter, defaults$1) : defaults$1;
        var unit = getUnitFromValue(tick.value, isUTC);

        if (mergedFormatter[unit]) {
          template = mergedFormatter[unit];
        } else if (mergedFormatter.inherit) {
          // Unit formatter is not defined and should inherit from bigger units
          var targetId = timeUnits.indexOf(unit);

          for (var i = targetId - 1; i >= 0; --i) {
            if (mergedFormatter[unit]) {
              template = mergedFormatter[unit];
              break;
            }
          }

          template = template || defaults$1.none;
        }

        if (isArray(template)) {
          var levelId = tick.level == null ? 0 : tick.level >= 0 ? tick.level : template.length + tick.level;
          levelId = Math.min(levelId, template.length - 1);
          template = template[levelId];
        }
      }

      return format(new Date(tick.value), template, isUTC, lang);
    }
    function getUnitFromValue(value, isUTC) {
      var date = parseDate(value);
      var M = date[monthGetterName(isUTC)]() + 1;
      var d = date[dateGetterName(isUTC)]();
      var h = date[hoursGetterName(isUTC)]();
      var m = date[minutesGetterName(isUTC)]();
      var s = date[secondsGetterName(isUTC)]();
      var S = date[millisecondsGetterName(isUTC)]();
      var isSecond = S === 0;
      var isMinute = isSecond && s === 0;
      var isHour = isMinute && m === 0;
      var isDay = isHour && h === 0;
      var isMonth = isDay && d === 1;
      var isYear = isMonth && M === 1;

      if (isYear) {
        return 'year';
      } else if (isMonth) {
        return 'month';
      } else if (isDay) {
        return 'day';
      } else if (isHour) {
        return 'hour';
      } else if (isMinute) {
        return 'minute';
      } else if (isSecond) {
        return 'second';
      } else {
        return 'millisecond';
      }
    }
    function getUnitValue(value, unit, isUTC) {
      var date = isNumber(value) ? parseDate(value) : value;
      unit = unit || getUnitFromValue(value, isUTC);

      switch (unit) {
        case 'year':
          return date[fullYearGetterName(isUTC)]();

        case 'half-year':
          return date[monthGetterName(isUTC)]() >= 6 ? 1 : 0;

        case 'quarter':
          return Math.floor((date[monthGetterName(isUTC)]() + 1) / 4);

        case 'month':
          return date[monthGetterName(isUTC)]();

        case 'day':
          return date[dateGetterName(isUTC)]();

        case 'half-day':
          return date[hoursGetterName(isUTC)]() / 24;

        case 'hour':
          return date[hoursGetterName(isUTC)]();

        case 'minute':
          return date[minutesGetterName(isUTC)]();

        case 'second':
          return date[secondsGetterName(isUTC)]();

        case 'millisecond':
          return date[millisecondsGetterName(isUTC)]();
      }
    }
    function fullYearGetterName(isUTC) {
      return isUTC ? 'getUTCFullYear' : 'getFullYear';
    }
    function monthGetterName(isUTC) {
      return isUTC ? 'getUTCMonth' : 'getMonth';
    }
    function dateGetterName(isUTC) {
      return isUTC ? 'getUTCDate' : 'getDate';
    }
    function hoursGetterName(isUTC) {
      return isUTC ? 'getUTCHours' : 'getHours';
    }
    function minutesGetterName(isUTC) {
      return isUTC ? 'getUTCMinutes' : 'getMinutes';
    }
    function secondsGetterName(isUTC) {
      return isUTC ? 'getUTCSeconds' : 'getSeconds';
    }
    function millisecondsGetterName(isUTC) {
      return isUTC ? 'getUTCMilliseconds' : 'getMilliseconds';
    }
    function fullYearSetterName(isUTC) {
      return isUTC ? 'setUTCFullYear' : 'setFullYear';
    }
    function monthSetterName(isUTC) {
      return isUTC ? 'setUTCMonth' : 'setMonth';
    }
    function dateSetterName(isUTC) {
      return isUTC ? 'setUTCDate' : 'setDate';
    }
    function hoursSetterName(isUTC) {
      return isUTC ? 'setUTCHours' : 'setHours';
    }
    function minutesSetterName(isUTC) {
      return isUTC ? 'setUTCMinutes' : 'setMinutes';
    }
    function secondsSetterName(isUTC) {
      return isUTC ? 'setUTCSeconds' : 'setSeconds';
    }
    function millisecondsSetterName(isUTC) {
      return isUTC ? 'setUTCMilliseconds' : 'setMilliseconds';
    }

    function getTextRect(text, font, align, verticalAlign, padding, rich, truncate, lineHeight) {
      var textEl = new ZRText({
        style: {
          text: text,
          font: font,
          align: align,
          verticalAlign: verticalAlign,
          padding: padding,
          rich: rich,
          overflow: truncate ? 'truncate' : null,
          lineHeight: lineHeight
        }
      });
      return textEl.getBoundingRect();
    }

    /**
     * Add a comma each three digit.
     */

    function addCommas(x) {
      if (!isNumeric(x)) {
        return isString(x) ? x : '-';
      }

      var parts = (x + '').split('.');
      return parts[0].replace(/(\d{1,3})(?=(?:\d{3})+(?!\d))/g, '$1,') + (parts.length > 1 ? '.' + parts[1] : '');
    }
    function toCamelCase(str, upperCaseFirst) {
      str = (str || '').toLowerCase().replace(/-(.)/g, function (match, group1) {
        return group1.toUpperCase();
      });

      if (upperCaseFirst && str) {
        str = str.charAt(0).toUpperCase() + str.slice(1);
      }

      return str;
    }
    var normalizeCssArray$1 = normalizeCssArray;
    /**
     * Make value user readable for tooltip and label.
     * "User readable":
     *     Try to not print programmer-specific text like NaN, Infinity, null, undefined.
     *     Avoid to display an empty string, which users can not recognize there is
     *     a value and it might look like a bug.
     */

    function makeValueReadable(value, valueType, useUTC) {
      var USER_READABLE_DEFUALT_TIME_PATTERN = '{yyyy}-{MM}-{dd} {HH}:{mm}:{ss}';

      function stringToUserReadable(str) {
        return str && trim(str) ? str : '-';
      }

      function isNumberUserReadable(num) {
        return !!(num != null && !isNaN(num) && isFinite(num));
      }

      var isTypeTime = valueType === 'time';
      var isValueDate = value instanceof Date;

      if (isTypeTime || isValueDate) {
        var date = isTypeTime ? parseDate(value) : value;

        if (!isNaN(+date)) {
          return format(date, USER_READABLE_DEFUALT_TIME_PATTERN, useUTC);
        } else if (isValueDate) {
          return '-';
        } // In other cases, continue to try to display the value in the following code.

      }

      if (valueType === 'ordinal') {
        return isStringSafe(value) ? stringToUserReadable(value) : isNumber(value) ? isNumberUserReadable(value) ? value + '' : '-' : '-';
      } // By default.


      var numericResult = numericToNumber(value);
      return isNumberUserReadable(numericResult) ? addCommas(numericResult) : isStringSafe(value) ? stringToUserReadable(value) : typeof value === 'boolean' ? value + '' : '-';
    }
    var TPL_VAR_ALIAS = ['a', 'b', 'c', 'd', 'e', 'f', 'g'];

    var wrapVar = function (varName, seriesIdx) {
      return '{' + varName + (seriesIdx == null ? '' : seriesIdx) + '}';
    };
    /**
     * Template formatter
     * @param {Array.<Object>|Object} paramsList
     */


    function formatTpl(tpl, paramsList, encode) {
      if (!isArray(paramsList)) {
        paramsList = [paramsList];
      }

      var seriesLen = paramsList.length;

      if (!seriesLen) {
        return '';
      }

      var $vars = paramsList[0].$vars || [];

      for (var i = 0; i < $vars.length; i++) {
        var alias = TPL_VAR_ALIAS[i];
        tpl = tpl.replace(wrapVar(alias), wrapVar(alias, 0));
      }

      for (var seriesIdx = 0; seriesIdx < seriesLen; seriesIdx++) {
        for (var k = 0; k < $vars.length; k++) {
          var val = paramsList[seriesIdx][$vars[k]];
          tpl = tpl.replace(wrapVar(TPL_VAR_ALIAS[k], seriesIdx), encode ? encodeHTML(val) : val);
        }
      }

      return tpl;
    }
    function getTooltipMarker(inOpt, extraCssText) {
      var opt = isString(inOpt) ? {
        color: inOpt,
        extraCssText: extraCssText
      } : inOpt || {};
      var color = opt.color;
      var type = opt.type;
      extraCssText = opt.extraCssText;
      var renderMode = opt.renderMode || 'html';

      if (!color) {
        return '';
      }

      if (renderMode === 'html') {
        return type === 'subItem' ? '<span style="display:inline-block;vertical-align:middle;margin-right:8px;margin-left:3px;' + 'border-radius:4px;width:4px;height:4px;background-color:' // Only support string
        + encodeHTML(color) + ';' + (extraCssText || '') + '"></span>' : '<span style="display:inline-block;margin-right:4px;' + 'border-radius:10px;width:10px;height:10px;background-color:' + encodeHTML(color) + ';' + (extraCssText || '') + '"></span>';
      } else {
        // Should better not to auto generate style name by auto-increment number here.
        // Because this util is usually called in tooltip formatter, which is probably
        // called repeatedly when mouse move and the auto-increment number increases fast.
        // Users can make their own style name by theirselves, make it unique and readable.
        var markerId = opt.markerId || 'markerX';
        return {
          renderMode: renderMode,
          content: '{' + markerId + '|}  ',
          style: type === 'subItem' ? {
            width: 4,
            height: 4,
            borderRadius: 2,
            backgroundColor: color
          } : {
            width: 10,
            height: 10,
            borderRadius: 5,
            backgroundColor: color
          }
        };
      }
    }
    /**
     * @deprecated Use `time/format` instead.
     * ISO Date format
     * @param {string} tpl
     * @param {number} value
     * @param {boolean} [isUTC=false] Default in local time.
     *           see `module:echarts/scale/Time`
     *           and `module:echarts/util/number#parseDate`.
     * @inner
     */

    function formatTime(tpl, value, isUTC) {
      if ("development" !== 'production') {
        deprecateReplaceLog('echarts.format.formatTime', 'echarts.time.format');
      }

      if (tpl === 'week' || tpl === 'month' || tpl === 'quarter' || tpl === 'half-year' || tpl === 'year') {
        tpl = 'MM-dd\nyyyy';
      }

      var date = parseDate(value);
      var getUTC = isUTC ? 'getUTC' : 'get';
      var y = date[getUTC + 'FullYear']();
      var M = date[getUTC + 'Month']() + 1;
      var d = date[getUTC + 'Date']();
      var h = date[getUTC + 'Hours']();
      var m = date[getUTC + 'Minutes']();
      var s = date[getUTC + 'Seconds']();
      var S = date[getUTC + 'Milliseconds']();
      tpl = tpl.replace('MM', pad(M, 2)).replace('M', M).replace('yyyy', y).replace('yy', pad(y % 100 + '', 2)).replace('dd', pad(d, 2)).replace('d', d).replace('hh', pad(h, 2)).replace('h', h).replace('mm', pad(m, 2)).replace('m', m).replace('ss', pad(s, 2)).replace('s', s).replace('SSS', pad(S, 3));
      return tpl;
    }
    /**
     * Capital first
     * @param {string} str
     * @return {string}
     */

    function capitalFirst(str) {
      return str ? str.charAt(0).toUpperCase() + str.substr(1) : str;
    }
    /**
     * @return Never be null/undefined.
     */

    function convertToColorString(color, defaultColor) {
      defaultColor = defaultColor || 'transparent';
      return isString(color) ? color : isObject(color) ? color.colorStops && (color.colorStops[0] || {}).color || defaultColor : defaultColor;
    }
    /**
     * open new tab
     * @param link url
     * @param target blank or self
     */

    function windowOpen(link, target) {
      /* global window */
      if (target === '_blank' || target === 'blank') {
        var blank = window.open();
        blank.opener = null;
        blank.location.href = link;
      } else {
        window.open(link, target);
      }
    }

    var each$1 = each;
    /**
     * @public
     */

    var LOCATION_PARAMS = ['left', 'right', 'top', 'bottom', 'width', 'height'];
    /**
     * @public
     */

    var HV_NAMES = [['width', 'left', 'right'], ['height', 'top', 'bottom']];

    function boxLayout(orient, group, gap, maxWidth, maxHeight) {
      var x = 0;
      var y = 0;

      if (maxWidth == null) {
        maxWidth = Infinity;
      }

      if (maxHeight == null) {
        maxHeight = Infinity;
      }

      var currentLineMaxSize = 0;
      group.eachChild(function (child, idx) {
        var rect = child.getBoundingRect();
        var nextChild = group.childAt(idx + 1);
        var nextChildRect = nextChild && nextChild.getBoundingRect();
        var nextX;
        var nextY;

        if (orient === 'horizontal') {
          var moveX = rect.width + (nextChildRect ? -nextChildRect.x + rect.x : 0);
          nextX = x + moveX; // Wrap when width exceeds maxWidth or meet a `newline` group
          // FIXME compare before adding gap?

          if (nextX > maxWidth || child.newline) {
            x = 0;
            nextX = moveX;
            y += currentLineMaxSize + gap;
            currentLineMaxSize = rect.height;
          } else {
            // FIXME: consider rect.y is not `0`?
            currentLineMaxSize = Math.max(currentLineMaxSize, rect.height);
          }
        } else {
          var moveY = rect.height + (nextChildRect ? -nextChildRect.y + rect.y : 0);
          nextY = y + moveY; // Wrap when width exceeds maxHeight or meet a `newline` group

          if (nextY > maxHeight || child.newline) {
            x += currentLineMaxSize + gap;
            y = 0;
            nextY = moveY;
            currentLineMaxSize = rect.width;
          } else {
            currentLineMaxSize = Math.max(currentLineMaxSize, rect.width);
          }
        }

        if (child.newline) {
          return;
        }

        child.x = x;
        child.y = y;
        child.markRedraw();
        orient === 'horizontal' ? x = nextX + gap : y = nextY + gap;
      });
    }
    /**
     * VBox or HBox layouting
     * @param {string} orient
     * @param {module:zrender/graphic/Group} group
     * @param {number} gap
     * @param {number} [width=Infinity]
     * @param {number} [height=Infinity]
     */


    var box = boxLayout;
    /**
     * VBox layouting
     * @param {module:zrender/graphic/Group} group
     * @param {number} gap
     * @param {number} [width=Infinity]
     * @param {number} [height=Infinity]
     */

    var vbox = curry(boxLayout, 'vertical');
    /**
     * HBox layouting
     * @param {module:zrender/graphic/Group} group
     * @param {number} gap
     * @param {number} [width=Infinity]
     * @param {number} [height=Infinity]
     */

    var hbox = curry(boxLayout, 'horizontal');
    /**
     * Parse position info.
     */

    function getLayoutRect(positionInfo, containerRect, margin) {
      margin = normalizeCssArray$1(margin || 0);
      var containerWidth = containerRect.width;
      var containerHeight = containerRect.height;
      var left = parsePercent$1(positionInfo.left, containerWidth);
      var top = parsePercent$1(positionInfo.top, containerHeight);
      var right = parsePercent$1(positionInfo.right, containerWidth);
      var bottom = parsePercent$1(positionInfo.bottom, containerHeight);
      var width = parsePercent$1(positionInfo.width, containerWidth);
      var height = parsePercent$1(positionInfo.height, containerHeight);
      var verticalMargin = margin[2] + margin[0];
      var horizontalMargin = margin[1] + margin[3];
      var aspect = positionInfo.aspect; // If width is not specified, calculate width from left and right

      if (isNaN(width)) {
        width = containerWidth - right - horizontalMargin - left;
      }

      if (isNaN(height)) {
        height = containerHeight - bottom - verticalMargin - top;
      }

      if (aspect != null) {
        // If width and height are not given
        // 1. Graph should not exceeds the container
        // 2. Aspect must be keeped
        // 3. Graph should take the space as more as possible
        // FIXME
        // Margin is not considered, because there is no case that both
        // using margin and aspect so far.
        if (isNaN(width) && isNaN(height)) {
          if (aspect > containerWidth / containerHeight) {
            width = containerWidth * 0.8;
          } else {
            height = containerHeight * 0.8;
          }
        } // Calculate width or height with given aspect


        if (isNaN(width)) {
          width = aspect * height;
        }

        if (isNaN(height)) {
          height = width / aspect;
        }
      } // If left is not specified, calculate left from right and width


      if (isNaN(left)) {
        left = containerWidth - right - width - horizontalMargin;
      }

      if (isNaN(top)) {
        top = containerHeight - bottom - height - verticalMargin;
      } // Align left and top


      switch (positionInfo.left || positionInfo.right) {
        case 'center':
          left = containerWidth / 2 - width / 2 - margin[3];
          break;

        case 'right':
          left = containerWidth - width - horizontalMargin;
          break;
      }

      switch (positionInfo.top || positionInfo.bottom) {
        case 'middle':
        case 'center':
          top = containerHeight / 2 - height / 2 - margin[0];
          break;

        case 'bottom':
          top = containerHeight - height - verticalMargin;
          break;
      } // If something is wrong and left, top, width, height are calculated as NaN


      left = left || 0;
      top = top || 0;

      if (isNaN(width)) {
        // Width may be NaN if only one value is given except width
        width = containerWidth - horizontalMargin - left - (right || 0);
      }

      if (isNaN(height)) {
        // Height may be NaN if only one value is given except height
        height = containerHeight - verticalMargin - top - (bottom || 0);
      }

      var rect = new BoundingRect(left + margin[3], top + margin[0], width, height);
      rect.margin = margin;
      return rect;
    }
    /**
     * Position a zr element in viewport
     *  Group position is specified by either
     *  {left, top}, {right, bottom}
     *  If all properties exists, right and bottom will be igonred.
     *
     * Logic:
     *     1. Scale (against origin point in parent coord)
     *     2. Rotate (against origin point in parent coord)
     *     3. Translate (with el.position by this method)
     * So this method only fixes the last step 'Translate', which does not affect
     * scaling and rotating.
     *
     * If be called repeatedly with the same input el, the same result will be gotten.
     *
     * Return true if the layout happened.
     *
     * @param el Should have `getBoundingRect` method.
     * @param positionInfo
     * @param positionInfo.left
     * @param positionInfo.top
     * @param positionInfo.right
     * @param positionInfo.bottom
     * @param positionInfo.width Only for opt.boundingModel: 'raw'
     * @param positionInfo.height Only for opt.boundingModel: 'raw'
     * @param containerRect
     * @param margin
     * @param opt
     * @param opt.hv Only horizontal or only vertical. Default to be [1, 1]
     * @param opt.boundingMode
     *        Specify how to calculate boundingRect when locating.
     *        'all': Position the boundingRect that is transformed and uioned
     *               both itself and its descendants.
     *               This mode simplies confine the elements in the bounding
     *               of their container (e.g., using 'right: 0').
     *        'raw': Position the boundingRect that is not transformed and only itself.
     *               This mode is useful when you want a element can overflow its
     *               container. (Consider a rotated circle needs to be located in a corner.)
     *               In this mode positionInfo.width/height can only be number.
     */

    function positionElement(el, positionInfo, containerRect, margin, opt, out) {
      var h = !opt || !opt.hv || opt.hv[0];
      var v = !opt || !opt.hv || opt.hv[1];
      var boundingMode = opt && opt.boundingMode || 'all';
      out = out || el;
      out.x = el.x;
      out.y = el.y;

      if (!h && !v) {
        return false;
      }

      var rect;

      if (boundingMode === 'raw') {
        rect = el.type === 'group' ? new BoundingRect(0, 0, +positionInfo.width || 0, +positionInfo.height || 0) : el.getBoundingRect();
      } else {
        rect = el.getBoundingRect();

        if (el.needLocalTransform()) {
          var transform = el.getLocalTransform(); // Notice: raw rect may be inner object of el,
          // which should not be modified.

          rect = rect.clone();
          rect.applyTransform(transform);
        }
      } // The real width and height can not be specified but calculated by the given el.


      var layoutRect = getLayoutRect(defaults({
        width: rect.width,
        height: rect.height
      }, positionInfo), containerRect, margin); // Because 'tranlate' is the last step in transform
      // (see zrender/core/Transformable#getLocalTransform),
      // we can just only modify el.position to get final result.

      var dx = h ? layoutRect.x - rect.x : 0;
      var dy = v ? layoutRect.y - rect.y : 0;

      if (boundingMode === 'raw') {
        out.x = dx;
        out.y = dy;
      } else {
        out.x += dx;
        out.y += dy;
      }

      if (out === el) {
        el.markRedraw();
      }

      return true;
    }
    function fetchLayoutMode(ins) {
      var layoutMode = ins.layoutMode || ins.constructor.layoutMode;
      return isObject(layoutMode) ? layoutMode : layoutMode ? {
        type: layoutMode
      } : null;
    }
    /**
     * Consider Case:
     * When default option has {left: 0, width: 100}, and we set {right: 0}
     * through setOption or media query, using normal zrUtil.merge will cause
     * {right: 0} does not take effect.
     *
     * @example
     * ComponentModel.extend({
     *     init: function () {
     *         ...
     *         let inputPositionParams = layout.getLayoutParams(option);
     *         this.mergeOption(inputPositionParams);
     *     },
     *     mergeOption: function (newOption) {
     *         newOption && zrUtil.merge(thisOption, newOption, true);
     *         layout.mergeLayoutParam(thisOption, newOption);
     *     }
     * });
     *
     * @param targetOption
     * @param newOption
     * @param opt
     */

    function mergeLayoutParam(targetOption, newOption, opt) {
      var ignoreSize = opt && opt.ignoreSize;
      !isArray(ignoreSize) && (ignoreSize = [ignoreSize, ignoreSize]);
      var hResult = merge(HV_NAMES[0], 0);
      var vResult = merge(HV_NAMES[1], 1);
      copy(HV_NAMES[0], targetOption, hResult);
      copy(HV_NAMES[1], targetOption, vResult);

      function merge(names, hvIdx) {
        var newParams = {};
        var newValueCount = 0;
        var merged = {};
        var mergedValueCount = 0;
        var enoughParamNumber = 2;
        each$1(names, function (name) {
          merged[name] = targetOption[name];
        });
        each$1(names, function (name) {
          // Consider case: newOption.width is null, which is
          // set by user for removing width setting.
          hasProp(newOption, name) && (newParams[name] = merged[name] = newOption[name]);
          hasValue(newParams, name) && newValueCount++;
          hasValue(merged, name) && mergedValueCount++;
        });

        if (ignoreSize[hvIdx]) {
          // Only one of left/right is premitted to exist.
          if (hasValue(newOption, names[1])) {
            merged[names[2]] = null;
          } else if (hasValue(newOption, names[2])) {
            merged[names[1]] = null;
          }

          return merged;
        } // Case: newOption: {width: ..., right: ...},
        // or targetOption: {right: ...} and newOption: {width: ...},
        // There is no conflict when merged only has params count
        // little than enoughParamNumber.


        if (mergedValueCount === enoughParamNumber || !newValueCount) {
          return merged;
        } // Case: newOption: {width: ..., right: ...},
        // Than we can make sure user only want those two, and ignore
        // all origin params in targetOption.
        else if (newValueCount >= enoughParamNumber) {
            return newParams;
          } else {
            // Chose another param from targetOption by priority.
            for (var i = 0; i < names.length; i++) {
              var name_1 = names[i];

              if (!hasProp(newParams, name_1) && hasProp(targetOption, name_1)) {
                newParams[name_1] = targetOption[name_1];
                break;
              }
            }

            return newParams;
          }
      }

      function hasProp(obj, name) {
        return obj.hasOwnProperty(name);
      }

      function hasValue(obj, name) {
        return obj[name] != null && obj[name] !== 'auto';
      }

      function copy(names, target, source) {
        each$1(names, function (name) {
          target[name] = source[name];
        });
      }
    }
    /**
     * Retrieve 'left', 'right', 'top', 'bottom', 'width', 'height' from object.
     */

    function getLayoutParams(source) {
      return copyLayoutParams({}, source);
    }
    /**
     * Retrieve 'left', 'right', 'top', 'bottom', 'width', 'height' from object.
     * @param {Object} source
     * @return {Object} Result contains those props.
     */

    function copyLayoutParams(target, source) {
      source && target && each$1(LOCATION_PARAMS, function (name) {
        source.hasOwnProperty(name) && (target[name] = source[name]);
      });
      return target;
    }

    var inner = makeInner();

    var ComponentModel =
    /** @class */
    function (_super) {
      __extends(ComponentModel, _super);

      function ComponentModel(option, parentModel, ecModel) {
        var _this = _super.call(this, option, parentModel, ecModel) || this;

        _this.uid = getUID('ec_cpt_model');
        return _this;
      }

      ComponentModel.prototype.init = function (option, parentModel, ecModel) {
        this.mergeDefaultAndTheme(option, ecModel);
      };

      ComponentModel.prototype.mergeDefaultAndTheme = function (option, ecModel) {
        var layoutMode = fetchLayoutMode(this);
        var inputPositionParams = layoutMode ? getLayoutParams(option) : {};
        var themeModel = ecModel.getTheme();
        merge(option, themeModel.get(this.mainType));
        merge(option, this.getDefaultOption());

        if (layoutMode) {
          mergeLayoutParam(option, inputPositionParams, layoutMode);
        }
      };

      ComponentModel.prototype.mergeOption = function (option, ecModel) {
        merge(this.option, option, true);
        var layoutMode = fetchLayoutMode(this);

        if (layoutMode) {
          mergeLayoutParam(this.option, option, layoutMode);
        }
      };
      /**
       * Called immediately after `init` or `mergeOption` of this instance called.
       */


      ComponentModel.prototype.optionUpdated = function (newCptOption, isInit) {};
      /**
       * [How to declare defaultOption]:
       *
       * (A) If using class declaration in typescript (since echarts 5):
       * ```ts
       * import {ComponentOption} from '../model/option.js';
       * export interface XxxOption extends ComponentOption {
       *     aaa: number
       * }
       * export class XxxModel extends Component {
       *     static type = 'xxx';
       *     static defaultOption: XxxOption = {
       *         aaa: 123
       *     }
       * }
       * Component.registerClass(XxxModel);
       * ```
       * ```ts
       * import {inheritDefaultOption} from '../util/component.js';
       * import {XxxModel, XxxOption} from './XxxModel.js';
       * export interface XxxSubOption extends XxxOption {
       *     bbb: number
       * }
       * class XxxSubModel extends XxxModel {
       *     static defaultOption: XxxSubOption = inheritDefaultOption(XxxModel.defaultOption, {
       *         bbb: 456
       *     })
       *     fn() {
       *         let opt = this.getDefaultOption();
       *         // opt is {aaa: 123, bbb: 456}
       *     }
       * }
       * ```
       *
       * (B) If using class extend (previous approach in echarts 3 & 4):
       * ```js
       * let XxxComponent = Component.extend({
       *     defaultOption: {
       *         xx: 123
       *     }
       * })
       * ```
       * ```js
       * let XxxSubComponent = XxxComponent.extend({
       *     defaultOption: {
       *         yy: 456
       *     },
       *     fn: function () {
       *         let opt = this.getDefaultOption();
       *         // opt is {xx: 123, yy: 456}
       *     }
       * })
       * ```
       */


      ComponentModel.prototype.getDefaultOption = function () {
        var ctor = this.constructor; // If using class declaration, it is different to travel super class
        // in legacy env and auto merge defaultOption. So if using class
        // declaration, defaultOption should be merged manually.

        if (!isExtendedClass(ctor)) {
          // When using ts class, defaultOption must be declared as static.
          return ctor.defaultOption;
        } // FIXME: remove this approach?


        var fields = inner(this);

        if (!fields.defaultOption) {
          var optList = [];
          var clz = ctor;

          while (clz) {
            var opt = clz.prototype.defaultOption;
            opt && optList.push(opt);
            clz = clz.superClass;
          }

          var defaultOption = {};

          for (var i = optList.length - 1; i >= 0; i--) {
            defaultOption = merge(defaultOption, optList[i], true);
          }

          fields.defaultOption = defaultOption;
        }

        return fields.defaultOption;
      };
      /**
       * Notice: always force to input param `useDefault` in case that forget to consider it.
       * The same behavior as `modelUtil.parseFinder`.
       *
       * @param useDefault In many cases like series refer axis and axis refer grid,
       *        If axis index / axis id not specified, use the first target as default.
       *        In other cases like dataZoom refer axis, if not specified, measn no refer.
       */


      ComponentModel.prototype.getReferringComponents = function (mainType, opt) {
        var indexKey = mainType + 'Index';
        var idKey = mainType + 'Id';
        return queryReferringComponents(this.ecModel, mainType, {
          index: this.get(indexKey, true),
          id: this.get(idKey, true)
        }, opt);
      };

      ComponentModel.prototype.getBoxLayoutParams = function () {
        // Consider itself having box layout configs.
        var boxLayoutModel = this;
        return {
          left: boxLayoutModel.get('left'),
          top: boxLayoutModel.get('top'),
          right: boxLayoutModel.get('right'),
          bottom: boxLayoutModel.get('bottom'),
          width: boxLayoutModel.get('width'),
          height: boxLayoutModel.get('height')
        };
      };
      /**
       * Get key for zlevel.
       * If developers don't configure zlevel. We will assign zlevel to series based on the key.
       * For example, lines with trail effect and progressive series will in an individual zlevel.
       */


      ComponentModel.prototype.getZLevelKey = function () {
        return '';
      };

      ComponentModel.prototype.setZLevel = function (zlevel) {
        this.option.zlevel = zlevel;
      };

      ComponentModel.protoInitialize = function () {
        var proto = ComponentModel.prototype;
        proto.type = 'component';
        proto.id = '';
        proto.name = '';
        proto.mainType = '';
        proto.subType = '';
        proto.componentIndex = 0;
      }();

      return ComponentModel;
    }(Model);

    mountExtend(ComponentModel, Model);
    enableClassManagement(ComponentModel);
    enableSubTypeDefaulter(ComponentModel);
    enableTopologicalTravel(ComponentModel, getDependencies);

    function getDependencies(componentType) {
      var deps = [];
      each(ComponentModel.getClassesByMainType(componentType), function (clz) {
        deps = deps.concat(clz.dependencies || clz.prototype.dependencies || []);
      }); // Ensure main type.

      deps = map(deps, function (type) {
        return parseClassType(type).main;
      }); // Hack dataset for convenience.

      if (componentType !== 'dataset' && indexOf(deps, 'dataset') <= 0) {
        deps.unshift('dataset');
      }

      return deps;
    }

    /*
    * Licensed to the Apache Software Foundation (ASF) under one
    * or more contributor license agreements.  See the NOTICE file
    * distributed with this work for additional information
    * regarding copyright ownership.  The ASF licenses this file
    * to you under the Apache License, Version 2.0 (the
    * "License"); you may not use this file except in compliance
    * with the License.  You may obtain a copy of the License at
    *
    *   http://www.apache.org/licenses/LICENSE-2.0
    *
    * Unless required by applicable law or agreed to in writing,
    * software distributed under the License is distributed on an
    * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
    * KIND, either express or implied.  See the License for the
    * specific language governing permissions and limitations
    * under the License.
    */


    /**
     * AUTO-GENERATED FILE. DO NOT MODIFY.
     */

    /*
    * Licensed to the Apache Software Foundation (ASF) under one
    * or more contributor license agreements.  See the NOTICE file
    * distributed with this work for additional information
    * regarding copyright ownership.  The ASF licenses this file
    * to you under the Apache License, Version 2.0 (the
    * "License"); you may not use this file except in compliance
    * with the License.  You may obtain a copy of the License at
    *
    *   http://www.apache.org/licenses/LICENSE-2.0
    *
    * Unless required by applicable law or agreed to in writing,
    * software distributed under the License is distributed on an
    * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
    * KIND, either express or implied.  See the License for the
    * specific language governing permissions and limitations
    * under the License.
    */
    var platform = ''; // Navigator not exists in node

    if (typeof navigator !== 'undefined') {
      /* global navigator */
      platform = navigator.platform || '';
    }

    var decalColor = 'rgba(0, 0, 0, 0.2)';
    var globalDefault = {
      darkMode: 'auto',
      // backgroundColor: 'rgba(0,0,0,0)',
      colorBy: 'series',
      color: ['#5470c6', '#91cc75', '#fac858', '#ee6666', '#73c0de', '#3ba272', '#fc8452', '#9a60b4', '#ea7ccc'],
      gradientColor: ['#f6efa6', '#d88273', '#bf444c'],
      aria: {
        decal: {
          decals: [{
            color: decalColor,
            dashArrayX: [1, 0],
            dashArrayY: [2, 5],
            symbolSize: 1,
            rotation: Math.PI / 6
          }, {
            color: decalColor,
            symbol: 'circle',
            dashArrayX: [[8, 8], [0, 8, 8, 0]],
            dashArrayY: [6, 0],
            symbolSize: 0.8
          }, {
            color: decalColor,
            dashArrayX: [1, 0],
            dashArrayY: [4, 3],
            rotation: -Math.PI / 4
          }, {
            color: decalColor,
            dashArrayX: [[6, 6], [0, 6, 6, 0]],
            dashArrayY: [6, 0]
          }, {
            color: decalColor,
            dashArrayX: [[1, 0], [1, 6]],
            dashArrayY: [1, 0, 6, 0],
            rotation: Math.PI / 4
          }, {
            color: decalColor,
            symbol: 'triangle',
            dashArrayX: [[9, 9], [0, 9, 9, 0]],
            dashArrayY: [7, 2],
            symbolSize: 0.75
          }]
        }
      },
      // If xAxis and yAxis declared, grid is created by default.
      // grid: {},
      textStyle: {
        // color: '#000',
        // decoration: 'none',
        // PENDING
        fontFamily: platform.match(/^Win/) ? 'Microsoft YaHei' : 'sans-serif',
        // fontFamily: 'Arial, Verdana, sans-serif',
        fontSize: 12,
        fontStyle: 'normal',
        fontWeight: 'normal'
      },
      // http://blogs.adobe.com/webplatform/2014/02/24/using-blend-modes-in-html-canvas/
      // https://developer.mozilla.org/en-US/docs/Web/API/CanvasRenderingContext2D/globalCompositeOperation
      // Default is source-over
      blendMode: null,
      stateAnimation: {
        duration: 300,
        easing: 'cubicOut'
      },
      animation: 'auto',
      animationDuration: 1000,
      animationDurationUpdate: 500,
      animationEasing: 'cubicInOut',
      animationEasingUpdate: 'cubicInOut',
      animationThreshold: 2000,
      // Configuration for progressive/incremental rendering
      progressiveThreshold: 3000,
      progressive: 400,
      // Threshold of if use single hover layer to optimize.
      // It is recommended that `hoverLayerThreshold` is equivalent to or less than
      // `progressiveThreshold`, otherwise hover will cause restart of progressive,
      // which is unexpected.
      // see example <echarts/test/heatmap-large.html>.
      hoverLayerThreshold: 3000,
      // See: module:echarts/scale/Time
      useUTC: false
    };

    var VISUAL_DIMENSIONS = createHashMap(['tooltip', 'label', 'itemName', 'itemId', 'itemGroupId', 'seriesName']);
    var SOURCE_FORMAT_ORIGINAL = 'original';
    var SOURCE_FORMAT_ARRAY_ROWS = 'arrayRows';
    var SOURCE_FORMAT_OBJECT_ROWS = 'objectRows';
    var SOURCE_FORMAT_KEYED_COLUMNS = 'keyedColumns';
    var SOURCE_FORMAT_TYPED_ARRAY = 'typedArray';
    var SOURCE_FORMAT_UNKNOWN = 'unknown';
    var SERIES_LAYOUT_BY_COLUMN = 'column';
    var SERIES_LAYOUT_BY_ROW = 'row';

    var BE_ORDINAL = {
      Must: 1,
      Might: 2,
      Not: 3 // Other cases

    };
    var innerGlobalModel = makeInner();
    /**
     * MUST be called before mergeOption of all series.
     */

    function resetSourceDefaulter(ecModel) {
      // `datasetMap` is used to make default encode.
      innerGlobalModel(ecModel).datasetMap = createHashMap();
    }
    /**
     * [The strategy of the arrengment of data dimensions for dataset]:
     * "value way": all axes are non-category axes. So series one by one take
     *     several (the number is coordSysDims.length) dimensions from dataset.
     *     The result of data arrengment of data dimensions like:
     *     | ser0_x | ser0_y | ser1_x | ser1_y | ser2_x | ser2_y |
     * "category way": at least one axis is category axis. So the the first data
     *     dimension is always mapped to the first category axis and shared by
     *     all of the series. The other data dimensions are taken by series like
     *     "value way" does.
     *     The result of data arrengment of data dimensions like:
     *     | ser_shared_x | ser0_y | ser1_y | ser2_y |
     *
     * @return encode Never be `null/undefined`.
     */

    function makeSeriesEncodeForAxisCoordSys(coordDimensions, seriesModel, source) {
      var encode = {};
      var datasetModel = querySeriesUpstreamDatasetModel(seriesModel); // Currently only make default when using dataset, util more reqirements occur.

      if (!datasetModel || !coordDimensions) {
        return encode;
      }

      var encodeItemName = [];
      var encodeSeriesName = [];
      var ecModel = seriesModel.ecModel;
      var datasetMap = innerGlobalModel(ecModel).datasetMap;
      var key = datasetModel.uid + '_' + source.seriesLayoutBy;
      var baseCategoryDimIndex;
      var categoryWayValueDimStart;
      coordDimensions = coordDimensions.slice();
      each(coordDimensions, function (coordDimInfoLoose, coordDimIdx) {
        var coordDimInfo = isObject(coordDimInfoLoose) ? coordDimInfoLoose : coordDimensions[coordDimIdx] = {
          name: coordDimInfoLoose
        };

        if (coordDimInfo.type === 'ordinal' && baseCategoryDimIndex == null) {
          baseCategoryDimIndex = coordDimIdx;
          categoryWayValueDimStart = getDataDimCountOnCoordDim(coordDimInfo);
        }

        encode[coordDimInfo.name] = [];
      });
      var datasetRecord = datasetMap.get(key) || datasetMap.set(key, {
        categoryWayDim: categoryWayValueDimStart,
        valueWayDim: 0
      }); // TODO
      // Auto detect first time axis and do arrangement.

      each(coordDimensions, function (coordDimInfo, coordDimIdx) {
        var coordDimName = coordDimInfo.name;
        var count = getDataDimCountOnCoordDim(coordDimInfo); // In value way.

        if (baseCategoryDimIndex == null) {
          var start = datasetRecord.valueWayDim;
          pushDim(encode[coordDimName], start, count);
          pushDim(encodeSeriesName, start, count);
          datasetRecord.valueWayDim += count; // ??? TODO give a better default series name rule?
          // especially when encode x y specified.
          // consider: when multiple series share one dimension
          // category axis, series name should better use
          // the other dimension name. On the other hand, use
          // both dimensions name.
        } // In category way, the first category axis.
        else if (baseCategoryDimIndex === coordDimIdx) {
            pushDim(encode[coordDimName], 0, count);
            pushDim(encodeItemName, 0, count);
          } // In category way, the other axis.
          else {
              var start = datasetRecord.categoryWayDim;
              pushDim(encode[coordDimName], start, count);
              pushDim(encodeSeriesName, start, count);
              datasetRecord.categoryWayDim += count;
            }
      });

      function pushDim(dimIdxArr, idxFrom, idxCount) {
        for (var i = 0; i < idxCount; i++) {
          dimIdxArr.push(idxFrom + i);
        }
      }

      function getDataDimCountOnCoordDim(coordDimInfo) {
        var dimsDef = coordDimInfo.dimsDef;
        return dimsDef ? dimsDef.length : 1;
      }

      encodeItemName.length && (encode.itemName = encodeItemName);
      encodeSeriesName.length && (encode.seriesName = encodeSeriesName);
      return encode;
    }
    /**
     * Work for data like [{name: ..., value: ...}, ...].
     *
     * @return encode Never be `null/undefined`.
     */

    function makeSeriesEncodeForNameBased(seriesModel, source, dimCount) {
      var encode = {};
      var datasetModel = querySeriesUpstreamDatasetModel(seriesModel); // Currently only make default when using dataset, util more reqirements occur.

      if (!datasetModel) {
        return encode;
      }

      var sourceFormat = source.sourceFormat;
      var dimensionsDefine = source.dimensionsDefine;
      var potentialNameDimIndex;

      if (sourceFormat === SOURCE_FORMAT_OBJECT_ROWS || sourceFormat === SOURCE_FORMAT_KEYED_COLUMNS) {
        each(dimensionsDefine, function (dim, idx) {
          if ((isObject(dim) ? dim.name : dim) === 'name') {
            potentialNameDimIndex = idx;
          }
        });
      }

      var idxResult = function () {
        var idxRes0 = {};
        var idxRes1 = {};
        var guessRecords = []; // 5 is an experience value.

        for (var i = 0, len = Math.min(5, dimCount); i < len; i++) {
          var guessResult = doGuessOrdinal(source.data, sourceFormat, source.seriesLayoutBy, dimensionsDefine, source.startIndex, i);
          guessRecords.push(guessResult);
          var isPureNumber = guessResult === BE_ORDINAL.Not; // [Strategy of idxRes0]: find the first BE_ORDINAL.Not as the value dim,
          // and then find a name dim with the priority:
          // "BE_ORDINAL.Might|BE_ORDINAL.Must" > "other dim" > "the value dim itself".

          if (isPureNumber && idxRes0.v == null && i !== potentialNameDimIndex) {
            idxRes0.v = i;
          }

          if (idxRes0.n == null || idxRes0.n === idxRes0.v || !isPureNumber && guessRecords[idxRes0.n] === BE_ORDINAL.Not) {
            idxRes0.n = i;
          }

          if (fulfilled(idxRes0) && guessRecords[idxRes0.n] !== BE_ORDINAL.Not) {
            return idxRes0;
          } // [Strategy of idxRes1]: if idxRes0 not satisfied (that is, no BE_ORDINAL.Not),
          // find the first BE_ORDINAL.Might as the value dim,
          // and then find a name dim with the priority:
          // "other dim" > "the value dim itself".
          // That is for backward compat: number-like (e.g., `'3'`, `'55'`) can be
          // treated as number.


          if (!isPureNumber) {
            if (guessResult === BE_ORDINAL.Might && idxRes1.v == null && i !== potentialNameDimIndex) {
              idxRes1.v = i;
            }

            if (idxRes1.n == null || idxRes1.n === idxRes1.v) {
              idxRes1.n = i;
            }
          }
        }

        function fulfilled(idxResult) {
          return idxResult.v != null && idxResult.n != null;
        }

        return fulfilled(idxRes0) ? idxRes0 : fulfilled(idxRes1) ? idxRes1 : null;
      }();

      if (idxResult) {
        encode.value = [idxResult.v]; // `potentialNameDimIndex` has highest priority.

        var nameDimIndex = potentialNameDimIndex != null ? potentialNameDimIndex : idxResult.n; // By default, label uses itemName in charts.
        // So we don't set encodeLabel here.

        encode.itemName = [nameDimIndex];
        encode.seriesName = [nameDimIndex];
      }

      return encode;
    }
    /**
     * @return If return null/undefined, indicate that should not use datasetModel.
     */

    function querySeriesUpstreamDatasetModel(seriesModel) {
      // Caution: consider the scenario:
      // A dataset is declared and a series is not expected to use the dataset,
      // and at the beginning `setOption({series: { noData })` (just prepare other
      // option but no data), then `setOption({series: {data: [...]}); In this case,
      // the user should set an empty array to avoid that dataset is used by default.
      var thisData = seriesModel.get('data', true);

      if (!thisData) {
        return queryReferringComponents(seriesModel.ecModel, 'dataset', {
          index: seriesModel.get('datasetIndex', true),
          id: seriesModel.get('datasetId', true)
        }, SINGLE_REFERRING).models[0];
      }
    }
    /**
     * @return Always return an array event empty.
     */

    function queryDatasetUpstreamDatasetModels(datasetModel) {
      // Only these attributes declared, we by defualt reference to `datasetIndex: 0`.
      // Otherwise, no reference.
      if (!datasetModel.get('transform', true) && !datasetModel.get('fromTransformResult', true)) {
        return [];
      }

      return queryReferringComponents(datasetModel.ecModel, 'dataset', {
        index: datasetModel.get('fromDatasetIndex', true),
        id: datasetModel.get('fromDatasetId', true)
      }, SINGLE_REFERRING).models;
    }
    /**
     * The rule should not be complex, otherwise user might not
     * be able to known where the data is wrong.
     * The code is ugly, but how to make it neat?
     */

    function guessOrdinal(source, dimIndex) {
      return doGuessOrdinal(source.data, source.sourceFormat, source.seriesLayoutBy, source.dimensionsDefine, source.startIndex, dimIndex);
    } // dimIndex may be overflow source data.
    // return {BE_ORDINAL}

    function doGuessOrdinal(data, sourceFormat, seriesLayoutBy, dimensionsDefine, startIndex, dimIndex) {
      var result; // Experience value.

      var maxLoop = 5;

      if (isTypedArray(data)) {
        return BE_ORDINAL.Not;
      } // When sourceType is 'objectRows' or 'keyedColumns', dimensionsDefine
      // always exists in source.


      var dimName;
      var dimType;

      if (dimensionsDefine) {
        var dimDefItem = dimensionsDefine[dimIndex];

        if (isObject(dimDefItem)) {
          dimName = dimDefItem.name;
          dimType = dimDefItem.type;
        } else if (isString(dimDefItem)) {
          dimName = dimDefItem;
        }
      }

      if (dimType != null) {
        return dimType === 'ordinal' ? BE_ORDINAL.Must : BE_ORDINAL.Not;
      }

      if (sourceFormat === SOURCE_FORMAT_ARRAY_ROWS) {
        var dataArrayRows = data;

        if (seriesLayoutBy === SERIES_LAYOUT_BY_ROW) {
          var sample = dataArrayRows[dimIndex];

          for (var i = 0; i < (sample || []).length && i < maxLoop; i++) {
            if ((result = detectValue(sample[startIndex + i])) != null) {
              return result;
            }
          }
        } else {
          for (var i = 0; i < dataArrayRows.length && i < maxLoop; i++) {
            var row = dataArrayRows[startIndex + i];

            if (row && (result = detectValue(row[dimIndex])) != null) {
              return result;
            }
          }
        }
      } else if (sourceFormat === SOURCE_FORMAT_OBJECT_ROWS) {
        var dataObjectRows = data;

        if (!dimName) {
          return BE_ORDINAL.Not;
        }

        for (var i = 0; i < dataObjectRows.length && i < maxLoop; i++) {
          var item = dataObjectRows[i];

          if (item && (result = detectValue(item[dimName])) != null) {
            return result;
          }
        }
      } else if (sourceFormat === SOURCE_FORMAT_KEYED_COLUMNS) {
        var dataKeyedColumns = data;

        if (!dimName) {
          return BE_ORDINAL.Not;
        }

        var sample = dataKeyedColumns[dimName];

        if (!sample || isTypedArray(sample)) {
          return BE_ORDINAL.Not;
        }

        for (var i = 0; i < sample.length && i < maxLoop; i++) {
          if ((result = detectValue(sample[i])) != null) {
            return result;
          }
        }
      } else if (sourceFormat === SOURCE_FORMAT_ORIGINAL) {
        var dataOriginal = data;

        for (var i = 0; i < dataOriginal.length && i < maxLoop; i++) {
          var item = dataOriginal[i];
          var val = getDataItemValue(item);

          if (!isArray(val)) {
            return BE_ORDINAL.Not;
          }

          if ((result = detectValue(val[dimIndex])) != null) {
            return result;
          }
        }
      }

      function detectValue(val) {
        var beStr = isString(val); // Consider usage convenience, '1', '2' will be treated as "number".
        // `isFinit('')` get `true`.

        if (val != null && isFinite(val) && val !== '') {
          return beStr ? BE_ORDINAL.Might : BE_ORDINAL.Not;
        } else if (beStr && val !== '-') {
          return BE_ORDINAL.Must;
        }
      }

      return BE_ORDINAL.Not;
    }

    var internalOptionCreatorMap = createHashMap();
    function registerInternalOptionCreator(mainType, creator) {
      assert(internalOptionCreatorMap.get(mainType) == null && creator);
      internalOptionCreatorMap.set(mainType, creator);
    }
    function concatInternalOptions(ecModel, mainType, newCmptOptionList) {
      var internalOptionCreator = internalOptionCreatorMap.get(mainType);

      if (!internalOptionCreator) {
        return newCmptOptionList;
      }

      var internalOptions = internalOptionCreator(ecModel);

      if (!internalOptions) {
        return newCmptOptionList;
      }

      if ("development" !== 'production') {
        for (var i = 0; i < internalOptions.length; i++) {
          assert(isComponentIdInternal(internalOptions[i]));
        }
      }

      return newCmptOptionList.concat(internalOptions);
    }

    var innerColor = makeInner();
    var innerDecal = makeInner();

    var PaletteMixin =
    /** @class */
    function () {
      function PaletteMixin() {}

      PaletteMixin.prototype.getColorFromPalette = function (name, scope, requestNum) {
        var defaultPalette = normalizeToArray(this.get('color', true));
        var layeredPalette = this.get('colorLayer', true);
        return getFromPalette(this, innerColor, defaultPalette, layeredPalette, name, scope, requestNum);
      };

      PaletteMixin.prototype.clearColorPalette = function () {
        clearPalette(this, innerColor);
      };

      return PaletteMixin;
    }();

    function getDecalFromPalette(ecModel, name, scope, requestNum) {
      var defaultDecals = normalizeToArray(ecModel.get(['aria', 'decal', 'decals']));
      return getFromPalette(ecModel, innerDecal, defaultDecals, null, name, scope, requestNum);
    }

    function getNearestPalette(palettes, requestColorNum) {
      var paletteNum = palettes.length; // TODO palettes must be in order

      for (var i = 0; i < paletteNum; i++) {
        if (palettes[i].length > requestColorNum) {
          return palettes[i];
        }
      }

      return palettes[paletteNum - 1];
    }
    /**
     * @param name MUST NOT be null/undefined. Otherwise call this function
     *             twise with the same parameters will get different result.
     * @param scope default this.
     * @return Can be null/undefined
     */


    function getFromPalette(that, inner, defaultPalette, layeredPalette, name, scope, requestNum) {
      scope = scope || that;
      var scopeFields = inner(scope);
      var paletteIdx = scopeFields.paletteIdx || 0;
      var paletteNameMap = scopeFields.paletteNameMap = scopeFields.paletteNameMap || {}; // Use `hasOwnProperty` to avoid conflict with Object.prototype.

      if (paletteNameMap.hasOwnProperty(name)) {
        return paletteNameMap[name];
      }

      var palette = requestNum == null || !layeredPalette ? defaultPalette : getNearestPalette(layeredPalette, requestNum); // In case can't find in layered color palette.

      palette = palette || defaultPalette;

      if (!palette || !palette.length) {
        return;
      }

      var pickedPaletteItem = palette[paletteIdx];

      if (name) {
        paletteNameMap[name] = pickedPaletteItem;
      }

      scopeFields.paletteIdx = (paletteIdx + 1) % palette.length;
      return pickedPaletteItem;
    }

    function clearPalette(that, inner) {
      inner(that).paletteIdx = 0;
      inner(that).paletteNameMap = {};
    }

    // Internal method names:
    // -----------------------

    var reCreateSeriesIndices;
    var assertSeriesInitialized;
    var initBase;
    var OPTION_INNER_KEY = '\0_ec_inner';
    var OPTION_INNER_VALUE = 1;
    var BUITIN_COMPONENTS_MAP = {
      grid: 'GridComponent',
      polar: 'PolarComponent',
      geo: 'GeoComponent',
      singleAxis: 'SingleAxisComponent',
      parallel: 'ParallelComponent',
      calendar: 'CalendarComponent',
      graphic: 'GraphicComponent',
      toolbox: 'ToolboxComponent',
      tooltip: 'TooltipComponent',
      axisPointer: 'AxisPointerComponent',
      brush: 'BrushComponent',
      title: 'TitleComponent',
      timeline: 'TimelineComponent',
      markPoint: 'MarkPointComponent',
      markLine: 'MarkLineComponent',
      markArea: 'MarkAreaComponent',
      legend: 'LegendComponent',
      dataZoom: 'DataZoomComponent',
      visualMap: 'VisualMapComponent',
      // aria: 'AriaComponent',
      // dataset: 'DatasetComponent',
      // Dependencies
      xAxis: 'GridComponent',
      yAxis: 'GridComponent',
      angleAxis: 'PolarComponent',
      radiusAxis: 'PolarComponent'
    };
    var BUILTIN_CHARTS_MAP = {
      line: 'LineChart',
      bar: 'BarChart',
      pie: 'PieChart',
      scatter: 'ScatterChart',
      radar: 'RadarChart',
      map: 'MapChart',
      tree: 'TreeChart',
      treemap: 'TreemapChart',
      graph: 'GraphChart',
      gauge: 'GaugeChart',
      funnel: 'FunnelChart',
      parallel: 'ParallelChart',
      sankey: 'SankeyChart',
      boxplot: 'BoxplotChart',
      candlestick: 'CandlestickChart',
      effectScatter: 'EffectScatterChart',
      lines: 'LinesChart',
      heatmap: 'HeatmapChart',
      pictorialBar: 'PictorialBarChart',
      themeRiver: 'ThemeRiverChart',
      sunburst: 'SunburstChart',
      custom: 'CustomChart'
    };
    var componetsMissingLogPrinted = {};

    function checkMissingComponents(option) {
      each(option, function (componentOption, mainType) {
        if (!ComponentModel.hasClass(mainType)) {
          var componentImportName = BUITIN_COMPONENTS_MAP[mainType];

          if (componentImportName && !componetsMissingLogPrinted[componentImportName]) {
            error("Component " + mainType + " is used but not imported.\nimport { " + componentImportName + " } from 'echarts/components';\necharts.use([" + componentImportName + "]);");
            componetsMissingLogPrinted[componentImportName] = true;
          }
        }
      });
    }

    var GlobalModel =
    /** @class */
    function (_super) {
      __extends(GlobalModel, _super);

      function GlobalModel() {
        return _super !== null && _super.apply(this, arguments) || this;
      }

      GlobalModel.prototype.init = function (option, parentModel, ecModel, theme, locale, optionManager) {
        theme = theme || {};
        this.option = null; // Mark as not initialized.

        this._theme = new Model(theme);
        this._locale = new Model(locale);
        this._optionManager = optionManager;
      };

      GlobalModel.prototype.setOption = function (option, opts, optionPreprocessorFuncs) {
        if ("development" !== 'production') {
          assert(option != null, 'option is null/undefined');
          assert(option[OPTION_INNER_KEY] !== OPTION_INNER_VALUE, 'please use chart.getOption()');
        }

        var innerOpt = normalizeSetOptionInput(opts);

        this._optionManager.setOption(option, optionPreprocessorFuncs, innerOpt);

        this._resetOption(null, innerOpt);
      };
      /**
       * @param type null/undefined: reset all.
       *        'recreate': force recreate all.
       *        'timeline': only reset timeline option
       *        'media': only reset media query option
       * @return Whether option changed.
       */


      GlobalModel.prototype.resetOption = function (type, opt) {
        return this._resetOption(type, normalizeSetOptionInput(opt));
      };

      GlobalModel.prototype._resetOption = function (type, opt) {
        var optionChanged = false;
        var optionManager = this._optionManager;

        if (!type || type === 'recreate') {
          var baseOption = optionManager.mountOption(type === 'recreate');

          if ("development" !== 'production') {
            checkMissingComponents(baseOption);
          }

          if (!this.option || type === 'recreate') {
            initBase(this, baseOption);
          } else {
            this.restoreData();

            this._mergeOption(baseOption, opt);
          }

          optionChanged = true;
        }

        if (type === 'timeline' || type === 'media') {
          this.restoreData();
        } // By design, if `setOption(option2)` at the second time, and `option2` is a `ECUnitOption`,
        // it should better not have the same props with `MediaUnit['option']`.
        // Because either `option2` or `MediaUnit['option']` will be always merged to "current option"
        // rather than original "baseOption". If they both override a prop, the result might be
        // unexpected when media state changed after `setOption` called.
        // If we really need to modify a props in each `MediaUnit['option']`, use the full version
        // (`{baseOption, media}`) in `setOption`.
        // For `timeline`, the case is the same.


        if (!type || type === 'recreate' || type === 'timeline') {
          var timelineOption = optionManager.getTimelineOption(this);

          if (timelineOption) {
            optionChanged = true;

            this._mergeOption(timelineOption, opt);
          }
        }

        if (!type || type === 'recreate' || type === 'media') {
          var mediaOptions = optionManager.getMediaOption(this);

          if (mediaOptions.length) {
            each(mediaOptions, function (mediaOption) {
              optionChanged = true;

              this._mergeOption(mediaOption, opt);
            }, this);
          }
        }

        return optionChanged;
      };

      GlobalModel.prototype.mergeOption = function (option) {
        this._mergeOption(option, null);
      };

      GlobalModel.prototype._mergeOption = function (newOption, opt) {
        var option = this.option;
        var componentsMap = this._componentsMap;
        var componentsCount = this._componentsCount;
        var newCmptTypes = [];
        var newCmptTypeMap = createHashMap();
        var replaceMergeMainTypeMap = opt && opt.replaceMergeMainTypeMap;
        resetSourceDefaulter(this); // If no component class, merge directly.
        // For example: color, animaiton options, etc.

        each(newOption, function (componentOption, mainType) {
          if (componentOption == null) {
            return;
          }

          if (!ComponentModel.hasClass(mainType)) {
            // globalSettingTask.dirty();
            option[mainType] = option[mainType] == null ? clone(componentOption) : merge(option[mainType], componentOption, true);
          } else if (mainType) {
            newCmptTypes.push(mainType);
            newCmptTypeMap.set(mainType, true);
          }
        });

        if (replaceMergeMainTypeMap) {
          // If there is a mainType `xxx` in `replaceMerge` but not declared in option,
          // we trade it as it is declared in option as `{xxx: []}`. Because:
          // (1) for normal merge, `{xxx: null/undefined}` are the same meaning as `{xxx: []}`.
          // (2) some preprocessor may convert some of `{xxx: null/undefined}` to `{xxx: []}`.
          replaceMergeMainTypeMap.each(function (val, mainTypeInReplaceMerge) {
            if (ComponentModel.hasClass(mainTypeInReplaceMerge) && !newCmptTypeMap.get(mainTypeInReplaceMerge)) {
              newCmptTypes.push(mainTypeInReplaceMerge);
              newCmptTypeMap.set(mainTypeInReplaceMerge, true);
            }
          });
        }

        ComponentModel.topologicalTravel(newCmptTypes, ComponentModel.getAllClassMainTypes(), visitComponent, this);

        function visitComponent(mainType) {
          var newCmptOptionList = concatInternalOptions(this, mainType, normalizeToArray(newOption[mainType]));
          var oldCmptList = componentsMap.get(mainType);
          var mergeMode = // `!oldCmptList` means init. See the comment in `mappingToExists`
          !oldCmptList ? 'replaceAll' : replaceMergeMainTypeMap && replaceMergeMainTypeMap.get(mainType) ? 'replaceMerge' : 'normalMerge';
          var mappingResult = mappingToExists(oldCmptList, newCmptOptionList, mergeMode); // Set mainType and complete subType.

          setComponentTypeToKeyInfo(mappingResult, mainType, ComponentModel); // Empty it before the travel, in order to prevent `this._componentsMap`
          // from being used in the `init`/`mergeOption`/`optionUpdated` of some
          // components, which is probably incorrect logic.

          option[mainType] = null;
          componentsMap.set(mainType, null);
          componentsCount.set(mainType, 0);
          var optionsByMainType = [];
          var cmptsByMainType = [];
          var cmptsCountByMainType = 0;
          var tooltipExists;
          var tooltipWarningLogged;
          each(mappingResult, function (resultItem, index) {
            var componentModel = resultItem.existing;
            var newCmptOption = resultItem.newOption;

            if (!newCmptOption) {
              if (componentModel) {
                // Consider where is no new option and should be merged using {},
                // see removeEdgeAndAdd in topologicalTravel and
                // ComponentModel.getAllClassMainTypes.
                componentModel.mergeOption({}, this);
                componentModel.optionUpdated({}, false);
              } // If no both `resultItem.exist` and `resultItem.option`,
              // either it is in `replaceMerge` and not matched by any id,
              // or it has been removed in previous `replaceMerge` and left a "hole" in this component index.

            } else {
              var isSeriesType = mainType === 'series';
              var ComponentModelClass = ComponentModel.getClass(mainType, resultItem.keyInfo.subType, !isSeriesType // Give a more detailed warn later if series don't exists
              );

              if (!ComponentModelClass) {
                if ("development" !== 'production') {
                  var subType = resultItem.keyInfo.subType;
                  var seriesImportName = BUILTIN_CHARTS_MAP[subType];

                  if (!componetsMissingLogPrinted[subType]) {
                    componetsMissingLogPrinted[subType] = true;

                    if (seriesImportName) {
                      error("Series " + subType + " is used but not imported.\nimport { " + seriesImportName + " } from 'echarts/charts';\necharts.use([" + seriesImportName + "]);");
                    } else {
                      error("Unknown series " + subType);
                    }
                  }
                }

                return;
              } // TODO Before multiple tooltips get supported, we do this check to avoid unexpected exception.


              if (mainType === 'tooltip') {
                if (tooltipExists) {
                  if ("development" !== 'production') {
                    if (!tooltipWarningLogged) {
                      warn('Currently only one tooltip component is allowed.');
                      tooltipWarningLogged = true;
                    }
                  }

                  return;
                }

                tooltipExists = true;
              }

              if (componentModel && componentModel.constructor === ComponentModelClass) {
                componentModel.name = resultItem.keyInfo.name; // componentModel.settingTask && componentModel.settingTask.dirty();

                componentModel.mergeOption(newCmptOption, this);
                componentModel.optionUpdated(newCmptOption, false);
              } else {
                // PENDING Global as parent ?
                var extraOpt = extend({
                  componentIndex: index
                }, resultItem.keyInfo);
                componentModel = new ComponentModelClass(newCmptOption, this, this, extraOpt); // Assign `keyInfo`

                extend(componentModel, extraOpt);

                if (resultItem.brandNew) {
                  componentModel.__requireNewView = true;
                }

                componentModel.init(newCmptOption, this, this); // Call optionUpdated after init.
                // newCmptOption has been used as componentModel.option
                // and may be merged with theme and default, so pass null
                // to avoid confusion.

                componentModel.optionUpdated(null, true);
              }
            }

            if (componentModel) {
              optionsByMainType.push(componentModel.option);
              cmptsByMainType.push(componentModel);
              cmptsCountByMainType++;
            } else {
              // Always do assign to avoid elided item in array.
              optionsByMainType.push(void 0);
              cmptsByMainType.push(void 0);
            }
          }, this);
          option[mainType] = optionsByMainType;
          componentsMap.set(mainType, cmptsByMainType);
          componentsCount.set(mainType, cmptsCountByMainType); // Backup series for filtering.

          if (mainType === 'series') {
            reCreateSeriesIndices(this);
          }
        } // If no series declared, ensure `_seriesIndices` initialized.


        if (!this._seriesIndices) {
          reCreateSeriesIndices(this);
        }
      };
      /**
       * Get option for output (cloned option and inner info removed)
       */


      GlobalModel.prototype.getOption = function () {
        var option = clone(this.option);
        each(option, function (optInMainType, mainType) {
          if (ComponentModel.hasClass(mainType)) {
            var opts = normalizeToArray(optInMainType); // Inner cmpts need to be removed.
            // Inner cmpts might not be at last since ec5.0, but still
            // compatible for users: if inner cmpt at last, splice the returned array.

            var realLen = opts.length;
            var metNonInner = false;

            for (var i = realLen - 1; i >= 0; i--) {
              // Remove options with inner id.
              if (opts[i] && !isComponentIdInternal(opts[i])) {
                metNonInner = true;
              } else {
                opts[i] = null;
                !metNonInner && realLen--;
              }
            }

            opts.length = realLen;
            option[mainType] = opts;
          }
        });
        delete option[OPTION_INNER_KEY];
        return option;
      };

      GlobalModel.prototype.getTheme = function () {
        return this._theme;
      };

      GlobalModel.prototype.getLocaleModel = function () {
        return this._locale;
      };

      GlobalModel.prototype.setUpdatePayload = function (payload) {
        this._payload = payload;
      };

      GlobalModel.prototype.getUpdatePayload = function () {
        return this._payload;
      };
      /**
       * @param idx If not specified, return the first one.
       */


      GlobalModel.prototype.getComponent = function (mainType, idx) {
        var list = this._componentsMap.get(mainType);

        if (list) {
          var cmpt = list[idx || 0];

          if (cmpt) {
            return cmpt;
          } else if (idx == null) {
            for (var i = 0; i < list.length; i++) {
              if (list[i]) {
                return list[i];
              }
            }
          }
        }
      };
      /**
       * @return Never be null/undefined.
       */


      GlobalModel.prototype.queryComponents = function (condition) {
        var mainType = condition.mainType;

        if (!mainType) {
          return [];
        }

        var index = condition.index;
        var id = condition.id;
        var name = condition.name;

        var cmpts = this._componentsMap.get(mainType);

        if (!cmpts || !cmpts.length) {
          return [];
        }

        var result;

        if (index != null) {
          result = [];
          each(normalizeToArray(index), function (idx) {
            cmpts[idx] && result.push(cmpts[idx]);
          });
        } else if (id != null) {
          result = queryByIdOrName('id', id, cmpts);
        } else if (name != null) {
          result = queryByIdOrName('name', name, cmpts);
        } else {
          // Return all non-empty components in that mainType
          result = filter(cmpts, function (cmpt) {
            return !!cmpt;
          });
        }

        return filterBySubType(result, condition);
      };
      /**
       * The interface is different from queryComponents,
       * which is convenient for inner usage.
       *
       * @usage
       * let result = findComponents(
       *     {mainType: 'dataZoom', query: {dataZoomId: 'abc'}}
       * );
       * let result = findComponents(
       *     {mainType: 'series', subType: 'pie', query: {seriesName: 'uio'}}
       * );
       * let result = findComponents(
       *     {mainType: 'series',
       *     filter: function (model, index) {...}}
       * );
       * // result like [component0, componnet1, ...]
       */


      GlobalModel.prototype.findComponents = function (condition) {
        var query = condition.query;
        var mainType = condition.mainType;
        var queryCond = getQueryCond(query);
        var result = queryCond ? this.queryComponents(queryCond) // Retrieve all non-empty components.
        : filter(this._componentsMap.get(mainType), function (cmpt) {
          return !!cmpt;
        });
        return doFilter(filterBySubType(result, condition));

        function getQueryCond(q) {
          var indexAttr = mainType + 'Index';
          var idAttr = mainType + 'Id';
          var nameAttr = mainType + 'Name';
          return q && (q[indexAttr] != null || q[idAttr] != null || q[nameAttr] != null) ? {
            mainType: mainType,
            // subType will be filtered finally.
            index: q[indexAttr],
            id: q[idAttr],
            name: q[nameAttr]
          } : null;
        }

        function doFilter(res) {
          return condition.filter ? filter(res, condition.filter) : res;
        }
      };

      GlobalModel.prototype.eachComponent = function (mainType, cb, context) {
        var componentsMap = this._componentsMap;

        if (isFunction(mainType)) {
          var ctxForAll_1 = cb;
          var cbForAll_1 = mainType;
          componentsMap.each(function (cmpts, componentType) {
            for (var i = 0; cmpts && i < cmpts.length; i++) {
              var cmpt = cmpts[i];
              cmpt && cbForAll_1.call(ctxForAll_1, componentType, cmpt, cmpt.componentIndex);
            }
          });
        } else {
          var cmpts = isString(mainType) ? componentsMap.get(mainType) : isObject(mainType) ? this.findComponents(mainType) : null;

          for (var i = 0; cmpts && i < cmpts.length; i++) {
            var cmpt = cmpts[i];
            cmpt && cb.call(context, cmpt, cmpt.componentIndex);
          }
        }
      };
      /**
       * Get series list before filtered by name.
       */


      GlobalModel.prototype.getSeriesByName = function (name) {
        var nameStr = convertOptionIdName(name, null);
        return filter(this._componentsMap.get('series'), function (oneSeries) {
          return !!oneSeries && nameStr != null && oneSeries.name === nameStr;
        });
      };
      /**
       * Get series list before filtered by index.
       */


      GlobalModel.prototype.getSeriesByIndex = function (seriesIndex) {
        return this._componentsMap.get('series')[seriesIndex];
      };
      /**
       * Get series list before filtered by type.
       * FIXME: rename to getRawSeriesByType?
       */


      GlobalModel.prototype.getSeriesByType = function (subType) {
        return filter(this._componentsMap.get('series'), function (oneSeries) {
          return !!oneSeries && oneSeries.subType === subType;
        });
      };
      /**
       * Get all series before filtered.
       */


      GlobalModel.prototype.getSeries = function () {
        return filter(this._componentsMap.get('series'), function (oneSeries) {
          return !!oneSeries;
        });
      };
      /**
       * Count series before filtered.
       */


      GlobalModel.prototype.getSeriesCount = function () {
        return this._componentsCount.get('series');
      };
      /**
       * After filtering, series may be different
       * from raw series.
       */


      GlobalModel.prototype.eachSeries = function (cb, context) {
        assertSeriesInitialized(this);
        each(this._seriesIndices, function (rawSeriesIndex) {
          var series = this._componentsMap.get('series')[rawSeriesIndex];

          cb.call(context, series, rawSeriesIndex);
        }, this);
      };
      /**
       * Iterate raw series before filtered.
       *
       * @param {Function} cb
       * @param {*} context
       */


      GlobalModel.prototype.eachRawSeries = function (cb, context) {
        each(this._componentsMap.get('series'), function (series) {
          series && cb.call(context, series, series.componentIndex);
        });
      };
      /**
       * After filtering, series may be different.
       * from raw series.
       */


      GlobalModel.prototype.eachSeriesByType = function (subType, cb, context) {
        assertSeriesInitialized(this);
        each(this._seriesIndices, function (rawSeriesIndex) {
          var series = this._componentsMap.get('series')[rawSeriesIndex];

          if (series.subType === subType) {
            cb.call(context, series, rawSeriesIndex);
          }
        }, this);
      };
      /**
       * Iterate raw series before filtered of given type.
       */


      GlobalModel.prototype.eachRawSeriesByType = function (subType, cb, context) {
        return each(this.getSeriesByType(subType), cb, context);
      };

      GlobalModel.prototype.isSeriesFiltered = function (seriesModel) {
        assertSeriesInitialized(this);
        return this._seriesIndicesMap.get(seriesModel.componentIndex) == null;
      };

      GlobalModel.prototype.getCurrentSeriesIndices = function () {
        return (this._seriesIndices || []).slice();
      };

      GlobalModel.prototype.filterSeries = function (cb, context) {
        assertSeriesInitialized(this);
        var newSeriesIndices = [];
        each(this._seriesIndices, function (seriesRawIdx) {
          var series = this._componentsMap.get('series')[seriesRawIdx];

          cb.call(context, series, seriesRawIdx) && newSeriesIndices.push(seriesRawIdx);
        }, this);
        this._seriesIndices = newSeriesIndices;
        this._seriesIndicesMap = createHashMap(newSeriesIndices);
      };

      GlobalModel.prototype.restoreData = function (payload) {
        reCreateSeriesIndices(this);
        var componentsMap = this._componentsMap;
        var componentTypes = [];
        componentsMap.each(function (components, componentType) {
          if (ComponentModel.hasClass(componentType)) {
            componentTypes.push(componentType);
          }
        });
        ComponentModel.topologicalTravel(componentTypes, ComponentModel.getAllClassMainTypes(), function (componentType) {
          each(componentsMap.get(componentType), function (component) {
            if (component && (componentType !== 'series' || !isNotTargetSeries(component, payload))) {
              component.restoreData();
            }
          });
        });
      };

      GlobalModel.internalField = function () {
        reCreateSeriesIndices = function (ecModel) {
          var seriesIndices = ecModel._seriesIndices = [];
          each(ecModel._componentsMap.get('series'), function (series) {
            // series may have been removed by `replaceMerge`.
            series && seriesIndices.push(series.componentIndex);
          });
          ecModel._seriesIndicesMap = createHashMap(seriesIndices);
        };

        assertSeriesInitialized = function (ecModel) {
          // Components that use _seriesIndices should depends on series component,
          // which make sure that their initialization is after series.
          if ("development" !== 'production') {
            if (!ecModel._seriesIndices) {
              throw new Error('Option should contains series.');
            }
          }
        };

        initBase = function (ecModel, baseOption) {
          // Using OPTION_INNER_KEY to mark that this option cannot be used outside,
          // i.e. `chart.setOption(chart.getModel().option);` is forbidden.
          ecModel.option = {};
          ecModel.option[OPTION_INNER_KEY] = OPTION_INNER_VALUE; // Init with series: [], in case of calling findSeries method
          // before series initialized.

          ecModel._componentsMap = createHashMap({
            series: []
          });
          ecModel._componentsCount = createHashMap(); // If user spefied `option.aria`, aria will be enable. This detection should be
          // performed before theme and globalDefault merge.

          var airaOption = baseOption.aria;

          if (isObject(airaOption) && airaOption.enabled == null) {
            airaOption.enabled = true;
          }

          mergeTheme(baseOption, ecModel._theme.option); // TODO Needs clone when merging to the unexisted property

          merge(baseOption, globalDefault, false);

          ecModel._mergeOption(baseOption, null);
        };
      }();

      return GlobalModel;
    }(Model);

    function isNotTargetSeries(seriesModel, payload) {
      if (payload) {
        var index = payload.seriesIndex;
        var id = payload.seriesId;
        var name_1 = payload.seriesName;
        return index != null && seriesModel.componentIndex !== index || id != null && seriesModel.id !== id || name_1 != null && seriesModel.name !== name_1;
      }
    }

    function mergeTheme(option, theme) {
      // PENDING
      // NOT use `colorLayer` in theme if option has `color`
      var notMergeColorLayer = option.color && !option.colorLayer;
      each(theme, function (themeItem, name) {
        if (name === 'colorLayer' && notMergeColorLayer) {
          return;
        } // If it is component model mainType, the model handles that merge later.
        // otherwise, merge them here.


        if (!ComponentModel.hasClass(name)) {
          if (typeof themeItem === 'object') {
            option[name] = !option[name] ? clone(themeItem) : merge(option[name], themeItem, false);
          } else {
            if (option[name] == null) {
              option[name] = themeItem;
            }
          }
        }
      });
    }

    function queryByIdOrName(attr, idOrName, cmpts) {
      // Here is a break from echarts4: string and number are
      // treated as equal.
      if (isArray(idOrName)) {
        var keyMap_1 = createHashMap();
        each(idOrName, function (idOrNameItem) {
          if (idOrNameItem != null) {
            var idName = convertOptionIdName(idOrNameItem, null);
            idName != null && keyMap_1.set(idOrNameItem, true);
          }
        });
        return filter(cmpts, function (cmpt) {
          return cmpt && keyMap_1.get(cmpt[attr]);
        });
      } else {
        var idName_1 = convertOptionIdName(idOrName, null);
        return filter(cmpts, function (cmpt) {
          return cmpt && idName_1 != null && cmpt[attr] === idName_1;
        });
      }
    }

    function filterBySubType(components, condition) {
      // Using hasOwnProperty for restrict. Consider
      // subType is undefined in user payload.
      return condition.hasOwnProperty('subType') ? filter(components, function (cmpt) {
        return cmpt && cmpt.subType === condition.subType;
      }) : components;
    }

    function normalizeSetOptionInput(opts) {
      var replaceMergeMainTypeMap = createHashMap();
      opts && each(normalizeToArray(opts.replaceMerge), function (mainType) {
        if ("development" !== 'production') {
          assert(ComponentModel.hasClass(mainType), '"' + mainType + '" is not valid component main type in "replaceMerge"');
        }

        replaceMergeMainTypeMap.set(mainType, true);
      });
      return {
        replaceMergeMainTypeMap: replaceMergeMainTypeMap
      };
    }

    mixin(GlobalModel, PaletteMixin);

    var availableMethods = ['getDom', 'getZr', 'getWidth', 'getHeight', 'getDevicePixelRatio', 'dispatchAction', 'isSSR', 'isDisposed', 'on', 'off', 'getDataURL', 'getConnectedDataURL', // 'getModel',
    'getOption', // 'getViewOfComponentModel',
    // 'getViewOfSeriesModel',
    'getId', 'updateLabelLayout'];

    var ExtensionAPI =
    /** @class */
    function () {
      function ExtensionAPI(ecInstance) {
        each(availableMethods, function (methodName) {
          this[methodName] = bind(ecInstance[methodName], ecInstance);
        }, this);
      }

      return ExtensionAPI;
    }();

    var coordinateSystemCreators = {};

    var CoordinateSystemManager =
    /** @class */
    function () {
      function CoordinateSystemManager() {
        this._coordinateSystems = [];
      }

      CoordinateSystemManager.prototype.create = function (ecModel, api) {
        var coordinateSystems = [];
        each(coordinateSystemCreators, function (creator, type) {
          var list = creator.create(ecModel, api);
          coordinateSystems = coordinateSystems.concat(list || []);
        });
        this._coordinateSystems = coordinateSystems;
      };

      CoordinateSystemManager.prototype.update = function (ecModel, api) {
        each(this._coordinateSystems, function (coordSys) {
          coordSys.update && coordSys.update(ecModel, api);
        });
      };

      CoordinateSystemManager.prototype.getCoordinateSystems = function () {
        return this._coordinateSystems.slice();
      };

      CoordinateSystemManager.register = function (type, creator) {
        coordinateSystemCreators[type] = creator;
      };

      CoordinateSystemManager.get = function (type) {
        return coordinateSystemCreators[type];
      };

      return CoordinateSystemManager;
    }();

    var QUERY_REG = /^(min|max)?(.+)$/; // Key: mainType
    // type FakeComponentsMap = HashMap<(MappingExistingItem & { subType: string })[]>;

    /**
     * TERM EXPLANATIONS:
     * See `ECOption` and `ECUnitOption` in `src/util/types.ts`.
     */

    var OptionManager =
    /** @class */
    function () {
      // timeline.notMerge is not supported in ec3. Firstly there is rearly
      // case that notMerge is needed. Secondly supporting 'notMerge' requires
      // rawOption cloned and backuped when timeline changed, which does no
      // good to performance. What's more, that both timeline and setOption
      // method supply 'notMerge' brings complex and some problems.
      // Consider this case:
      // (step1) chart.setOption({timeline: {notMerge: false}, ...}, false);
      // (step2) chart.setOption({timeline: {notMerge: true}, ...}, false);
      function OptionManager(api) {
        this._timelineOptions = [];
        this._mediaList = [];
        /**
         * -1, means default.
         * empty means no media.
         */

        this._currentMediaIndices = [];
        this._api = api;
      }

      OptionManager.prototype.setOption = function (rawOption, optionPreprocessorFuncs, opt) {
        if (rawOption) {
          // That set dat primitive is dangerous if user reuse the data when setOption again.
          each(normalizeToArray(rawOption.series), function (series) {
            series && series.data && isTypedArray(series.data) && setAsPrimitive(series.data);
          });
          each(normalizeToArray(rawOption.dataset), function (dataset) {
            dataset && dataset.source && isTypedArray(dataset.source) && setAsPrimitive(dataset.source);
          });
        } // Caution: some series modify option data, if do not clone,
        // it should ensure that the repeat modify correctly
        // (create a new object when modify itself).


        rawOption = clone(rawOption); // FIXME
        // If some property is set in timeline options or media option but
        // not set in baseOption, a warning should be given.

        var optionBackup = this._optionBackup;
        var newParsedOption = parseRawOption(rawOption, optionPreprocessorFuncs, !optionBackup);
        this._newBaseOption = newParsedOption.baseOption; // For setOption at second time (using merge mode);

        if (optionBackup) {
          // FIXME
          // the restore merge solution is essentially incorrect.
          // the mapping can not be 100% consistent with ecModel, which probably brings
          // potential bug!
          // The first merge is delayed, because in most cases, users do not call `setOption` twice.
          // let fakeCmptsMap = this._fakeCmptsMap;
          // if (!fakeCmptsMap) {
          //     fakeCmptsMap = this._fakeCmptsMap = createHashMap();
          //     mergeToBackupOption(fakeCmptsMap, null, optionBackup.baseOption, null);
          // }
          // mergeToBackupOption(
          //     fakeCmptsMap, optionBackup.baseOption, newParsedOption.baseOption, opt
          // );
          // For simplicity, timeline options and media options do not support merge,
          // that is, if you `setOption` twice and both has timeline options, the latter
          // timeline options will not be merged to the former, but just substitute them.
          if (newParsedOption.timelineOptions.length) {
            optionBackup.timelineOptions = newParsedOption.timelineOptions;
          }

          if (newParsedOption.mediaList.length) {
            optionBackup.mediaList = newParsedOption.mediaList;
          }

          if (newParsedOption.mediaDefault) {
            optionBackup.mediaDefault = newParsedOption.mediaDefault;
          }
        } else {
          this._optionBackup = newParsedOption;
        }
      };

      OptionManager.prototype.mountOption = function (isRecreate) {
        var optionBackup = this._optionBackup;
        this._timelineOptions = optionBackup.timelineOptions;
        this._mediaList = optionBackup.mediaList;
        this._mediaDefault = optionBackup.mediaDefault;
        this._currentMediaIndices = [];
        return clone(isRecreate // this._optionBackup.baseOption, which is created at the first `setOption`
        // called, and is merged into every new option by inner method `mergeToBackupOption`
        // each time `setOption` called, can be only used in `isRecreate`, because
        // its reliability is under suspicion. In other cases option merge is
        // performed by `model.mergeOption`.
        ? optionBackup.baseOption : this._newBaseOption);
      };

      OptionManager.prototype.getTimelineOption = function (ecModel) {
        var option;
        var timelineOptions = this._timelineOptions;

        if (timelineOptions.length) {
          // getTimelineOption can only be called after ecModel inited,
          // so we can get currentIndex from timelineModel.
          var timelineModel = ecModel.getComponent('timeline');

          if (timelineModel) {
            option = clone( // FIXME:TS as TimelineModel or quivlant interface
            timelineOptions[timelineModel.getCurrentIndex()]);
          }
        }

        return option;
      };

      OptionManager.prototype.getMediaOption = function (ecModel) {
        var ecWidth = this._api.getWidth();

        var ecHeight = this._api.getHeight();

        var mediaList = this._mediaList;
        var mediaDefault = this._mediaDefault;
        var indices = [];
        var result = []; // No media defined.

        if (!mediaList.length && !mediaDefault) {
          return result;
        } // Multi media may be applied, the latter defined media has higher priority.


        for (var i = 0, len = mediaList.length; i < len; i++) {
          if (applyMediaQuery(mediaList[i].query, ecWidth, ecHeight)) {
            indices.push(i);
          }
        } // FIXME
        // Whether mediaDefault should force users to provide? Otherwise
        // the change by media query can not be recorvered.


        if (!indices.length && mediaDefault) {
          indices = [-1];
        }

        if (indices.length && !indicesEquals(indices, this._currentMediaIndices)) {
          result = map(indices, function (index) {
            return clone(index === -1 ? mediaDefault.option : mediaList[index].option);
          });
        } // Otherwise return nothing.


        this._currentMediaIndices = indices;
        return result;
      };

      return OptionManager;
    }();
    /**
     * [RAW_OPTION_PATTERNS]
     * (Note: "series: []" represents all other props in `ECUnitOption`)
     *
     * (1) No prop "baseOption" declared:
     * Root option is used as "baseOption" (except prop "options" and "media").
     * ```js
     * option = {
     *     series: [],
     *     timeline: {},
     *     options: [],
     * };
     * option = {
     *     series: [],
     *     media: {},
     * };
     * option = {
     *     series: [],
     *     timeline: {},
     *     options: [],
     *     media: {},
     * }
     * ```
     *
     * (2) Prop "baseOption" declared:
     * If "baseOption" declared, `ECUnitOption` props can only be declared
     * inside "baseOption" except prop "timeline" (compat ec2).
     * ```js
     * option = {
     *     baseOption: {
     *         timeline: {},
     *         series: [],
     *     },
     *     options: []
     * };
     * option = {
     *     baseOption: {
     *         series: [],
     *     },
     *     media: []
     * };
     * option = {
     *     baseOption: {
     *         timeline: {},
     *         series: [],
     *     },
     *     options: []
     *     media: []
     * };
     * option = {
     *     // ec3 compat ec2: allow (only) `timeline` declared
     *     // outside baseOption. Keep this setting for compat.
     *     timeline: {},
     *     baseOption: {
     *         series: [],
     *     },
     *     options: [],
     *     media: []
     * };
     * ```
     */


    function parseRawOption( // `rawOption` May be modified
    rawOption, optionPreprocessorFuncs, isNew) {
      var mediaList = [];
      var mediaDefault;
      var baseOption;
      var declaredBaseOption = rawOption.baseOption; // Compatible with ec2, [RAW_OPTION_PATTERNS] above.

      var timelineOnRoot = rawOption.timeline;
      var timelineOptionsOnRoot = rawOption.options;
      var mediaOnRoot = rawOption.media;
      var hasMedia = !!rawOption.media;
      var hasTimeline = !!(timelineOptionsOnRoot || timelineOnRoot || declaredBaseOption && declaredBaseOption.timeline);

      if (declaredBaseOption) {
        baseOption = declaredBaseOption; // For merge option.

        if (!baseOption.timeline) {
          baseOption.timeline = timelineOnRoot;
        }
      } // For convenience, enable to use the root option as the `baseOption`:
      // `{ ...normalOptionProps, media: [{ ... }, { ... }] }`
      else {
          if (hasTimeline || hasMedia) {
            rawOption.options = rawOption.media = null;
          }

          baseOption = rawOption;
        }

      if (hasMedia) {
        if (isArray(mediaOnRoot)) {
          each(mediaOnRoot, function (singleMedia) {
            if ("development" !== 'production') {
              // Real case of wrong config.
              if (singleMedia && !singleMedia.option && isObject(singleMedia.query) && isObject(singleMedia.query.option)) {
                error('Illegal media option. Must be like { media: [ { query: {}, option: {} } ] }');
              }
            }

            if (singleMedia && singleMedia.option) {
              if (singleMedia.query) {
                mediaList.push(singleMedia);
              } else if (!mediaDefault) {
                // Use the first media default.
                mediaDefault = singleMedia;
              }
            }
          });
        } else {
          if ("development" !== 'production') {
            // Real case of wrong config.
            error('Illegal media option. Must be an array. Like { media: [ {...}, {...} ] }');
          }
        }
      }

      doPreprocess(baseOption);
      each(timelineOptionsOnRoot, function (option) {
        return doPreprocess(option);
      });
      each(mediaList, function (media) {
        return doPreprocess(media.option);
      });

      function doPreprocess(option) {
        each(optionPreprocessorFuncs, function (preProcess) {
          preProcess(option, isNew);
        });
      }

      return {
        baseOption: baseOption,
        timelineOptions: timelineOptionsOnRoot || [],
        mediaDefault: mediaDefault,
        mediaList: mediaList
      };
    }
    /**
     * @see <http://www.w3.org/TR/css3-mediaqueries/#media1>
     * Support: width, height, aspectRatio
     * Can use max or min as prefix.
     */


    function applyMediaQuery(query, ecWidth, ecHeight) {
      var realMap = {
        width: ecWidth,
        height: ecHeight,
        aspectratio: ecWidth / ecHeight // lower case for convenience.

      };
      var applicable = true;
      each(query, function (value, attr) {
        var matched = attr.match(QUERY_REG);

        if (!matched || !matched[1] || !matched[2]) {
          return;
        }

        var operator = matched[1];
        var realAttr = matched[2].toLowerCase();

        if (!compare(realMap[realAttr], value, operator)) {
          applicable = false;
        }
      });
      return applicable;
    }

    function compare(real, expect, operator) {
      if (operator === 'min') {
        return real >= expect;
      } else if (operator === 'max') {
        return real <= expect;
      } else {
        // Equals
        return real === expect;
      }
    }

    function indicesEquals(indices1, indices2) {
      // indices is always order by asc and has only finite number.
      return indices1.join(',') === indices2.join(',');
    }

    var each$2 = each;
    var isObject$1 = isObject;
    var POSSIBLE_STYLES = ['areaStyle', 'lineStyle', 'nodeStyle', 'linkStyle', 'chordStyle', 'label', 'labelLine'];

    function compatEC2ItemStyle(opt) {
      var itemStyleOpt = opt && opt.itemStyle;

      if (!itemStyleOpt) {
        return;
      }

      for (var i = 0, len = POSSIBLE_STYLES.length; i < len; i++) {
        var styleName = POSSIBLE_STYLES[i];
        var normalItemStyleOpt = itemStyleOpt.normal;
        var emphasisItemStyleOpt = itemStyleOpt.emphasis;

        if (normalItemStyleOpt && normalItemStyleOpt[styleName]) {
          if ("development" !== 'production') {
            deprecateReplaceLog("itemStyle.normal." + styleName, styleName);
          }

          opt[styleName] = opt[styleName] || {};

          if (!opt[styleName].normal) {
            opt[styleName].normal = normalItemStyleOpt[styleName];
          } else {
            merge(opt[styleName].normal, normalItemStyleOpt[styleName]);
          }

          normalItemStyleOpt[styleName] = null;
        }

        if (emphasisItemStyleOpt && emphasisItemStyleOpt[styleName]) {
          if ("development" !== 'production') {
            deprecateReplaceLog("itemStyle.emphasis." + styleName, "emphasis." + styleName);
          }

          opt[styleName] = opt[styleName] || {};

          if (!opt[styleName].emphasis) {
            opt[styleName].emphasis = emphasisItemStyleOpt[styleName];
          } else {
            merge(opt[styleName].emphasis, emphasisItemStyleOpt[styleName]);
          }

          emphasisItemStyleOpt[styleName] = null;
        }
      }
    }

    function convertNormalEmphasis(opt, optType, useExtend) {
      if (opt && opt[optType] && (opt[optType].normal || opt[optType].emphasis)) {
        var normalOpt = opt[optType].normal;
        var emphasisOpt = opt[optType].emphasis;

        if (normalOpt) {
          if ("development" !== 'production') {
            // eslint-disable-next-line max-len
            deprecateLog("'normal' hierarchy in " + optType + " has been removed since 4.0. All style properties are configured in " + optType + " directly now.");
          } // Timeline controlStyle has other properties besides normal and emphasis


          if (useExtend) {
            opt[optType].normal = opt[optType].emphasis = null;
            defaults(opt[optType], normalOpt);
          } else {
            opt[optType] = normalOpt;
          }
        }

        if (emphasisOpt) {
          if ("development" !== 'production') {
            deprecateLog(optType + ".emphasis has been changed to emphasis." + optType + " since 4.0");
          }

          opt.emphasis = opt.emphasis || {};
          opt.emphasis[optType] = emphasisOpt; // Also compat the case user mix the style and focus together in ec3 style
          // for example: { itemStyle: { normal: {}, emphasis: {focus, shadowBlur} } }

          if (emphasisOpt.focus) {
            opt.emphasis.focus = emphasisOpt.focus;
          }

          if (emphasisOpt.blurScope) {
            opt.emphasis.blurScope = emphasisOpt.blurScope;
          }
        }
      }
    }

    function removeEC3NormalStatus(opt) {
      convertNormalEmphasis(opt, 'itemStyle');
      convertNormalEmphasis(opt, 'lineStyle');
      convertNormalEmphasis(opt, 'areaStyle');
      convertNormalEmphasis(opt, 'label');
      convertNormalEmphasis(opt, 'labelLine'); // treemap

      convertNormalEmphasis(opt, 'upperLabel'); // graph

      convertNormalEmphasis(opt, 'edgeLabel');
    }

    function compatTextStyle(opt, propName) {
      // Check whether is not object (string\null\undefined ...)
      var labelOptSingle = isObject$1(opt) && opt[propName];
      var textStyle = isObject$1(labelOptSingle) && labelOptSingle.textStyle;

      if (textStyle) {
        if ("development" !== 'production') {
          // eslint-disable-next-line max-len
          deprecateLog("textStyle hierarchy in " + propName + " has been removed since 4.0. All textStyle properties are configured in " + propName + " directly now.");
        }

        for (var i = 0, len = TEXT_STYLE_OPTIONS.length; i < len; i++) {
          var textPropName = TEXT_STYLE_OPTIONS[i];

          if (textStyle.hasOwnProperty(textPropName)) {
            labelOptSingle[textPropName] = textStyle[textPropName];
          }
        }
      }
    }

    function compatEC3CommonStyles(opt) {
      if (opt) {
        removeEC3NormalStatus(opt);
        compatTextStyle(opt, 'label');
        opt.emphasis && compatTextStyle(opt.emphasis, 'label');
      }
    }

    function processSeries(seriesOpt) {
      if (!isObject$1(seriesOpt)) {
        return;
      }

      compatEC2ItemStyle(seriesOpt);
      removeEC3NormalStatus(seriesOpt);
      compatTextStyle(seriesOpt, 'label'); // treemap

      compatTextStyle(seriesOpt, 'upperLabel'); // graph

      compatTextStyle(seriesOpt, 'edgeLabel');

      if (seriesOpt.emphasis) {
        compatTextStyle(seriesOpt.emphasis, 'label'); // treemap

        compatTextStyle(seriesOpt.emphasis, 'upperLabel'); // graph

        compatTextStyle(seriesOpt.emphasis, 'edgeLabel');
      }

      var markPoint = seriesOpt.markPoint;

      if (markPoint) {
        compatEC2ItemStyle(markPoint);
        compatEC3CommonStyles(markPoint);
      }

      var markLine = seriesOpt.markLine;

      if (markLine) {
        compatEC2ItemStyle(markLine);
        compatEC3CommonStyles(markLine);
      }

      var markArea = seriesOpt.markArea;

      if (markArea) {
        compatEC3CommonStyles(markArea);
      }

      var data = seriesOpt.data; // Break with ec3: if `setOption` again, there may be no `type` in option,
      // then the backward compat based on option type will not be performed.

      if (seriesOpt.type === 'graph') {
        data = data || seriesOpt.nodes;
        var edgeData = seriesOpt.links || seriesOpt.edges;

        if (edgeData && !isTypedArray(edgeData)) {
          for (var i = 0; i < edgeData.length; i++) {
            compatEC3CommonStyles(edgeData[i]);
          }
        }

        each(seriesOpt.categories, function (opt) {
          removeEC3NormalStatus(opt);
        });
      }

      if (data && !isTypedArray(data)) {
        for (var i = 0; i < data.length; i++) {
          compatEC3CommonStyles(data[i]);
        }
      } // mark point data


      markPoint = seriesOpt.markPoint;

      if (markPoint && markPoint.data) {
        var mpData = markPoint.data;

        for (var i = 0; i < mpData.length; i++) {
          compatEC3CommonStyles(mpData[i]);
        }
      } // mark line data


      markLine = seriesOpt.markLine;

      if (markLine && markLine.data) {
        var mlData = markLine.data;

        for (var i = 0; i < mlData.length; i++) {
          if (isArray(mlData[i])) {
            compatEC3CommonStyles(mlData[i][0]);
            compatEC3CommonStyles(mlData[i][1]);
          } else {
            compatEC3CommonStyles(mlData[i]);
          }
        }
      } // Series


      if (seriesOpt.type === 'gauge') {
        compatTextStyle(seriesOpt, 'axisLabel');
        compatTextStyle(seriesOpt, 'title');
        compatTextStyle(seriesOpt, 'detail');
      } else if (seriesOpt.type === 'treemap') {
        convertNormalEmphasis(seriesOpt.breadcrumb, 'itemStyle');
        each(seriesOpt.levels, function (opt) {
          removeEC3NormalStatus(opt);
        });
      } else if (seriesOpt.type === 'tree') {
        removeEC3NormalStatus(seriesOpt.leaves);
      } // sunburst starts from ec4, so it does not need to compat levels.

    }

    function toArr(o) {
      return isArray(o) ? o : o ? [o] : [];
    }

    function toObj(o) {
      return (isArray(o) ? o[0] : o) || {};
    }

    function globalCompatStyle(option, isTheme) {
      each$2(toArr(option.series), function (seriesOpt) {
        isObject$1(seriesOpt) && processSeries(seriesOpt);
      });
      var axes = ['xAxis', 'yAxis', 'radiusAxis', 'angleAxis', 'singleAxis', 'parallelAxis', 'radar'];
      isTheme && axes.push('valueAxis', 'categoryAxis', 'logAxis', 'timeAxis');
      each$2(axes, function (axisName) {
        each$2(toArr(option[axisName]), function (axisOpt) {
          if (axisOpt) {
            compatTextStyle(axisOpt, 'axisLabel');
            compatTextStyle(axisOpt.axisPointer, 'label');
          }
        });
      });
      each$2(toArr(option.parallel), function (parallelOpt) {
        var parallelAxisDefault = parallelOpt && parallelOpt.parallelAxisDefault;
        compatTextStyle(parallelAxisDefault, 'axisLabel');
        compatTextStyle(parallelAxisDefault && parallelAxisDefault.axisPointer, 'label');
      });
      each$2(toArr(option.calendar), function (calendarOpt) {
        convertNormalEmphasis(calendarOpt, 'itemStyle');
        compatTextStyle(calendarOpt, 'dayLabel');
        compatTextStyle(calendarOpt, 'monthLabel');
        compatTextStyle(calendarOpt, 'yearLabel');
      }); // radar.name.textStyle

      each$2(toArr(option.radar), function (radarOpt) {
        compatTextStyle(radarOpt, 'name'); // Use axisName instead of name because component has name property

        if (radarOpt.name && radarOpt.axisName == null) {
          radarOpt.axisName = radarOpt.name;
          delete radarOpt.name;

          if ("development" !== 'production') {
            deprecateLog('name property in radar component has been changed to axisName');
          }
        }

        if (radarOpt.nameGap != null && radarOpt.axisNameGap == null) {
          radarOpt.axisNameGap = radarOpt.nameGap;
          delete radarOpt.nameGap;

          if ("development" !== 'production') {
            deprecateLog('nameGap property in radar component has been changed to axisNameGap');
          }
        }

        if ("development" !== 'production') {
          each$2(radarOpt.indicator, function (indicatorOpt) {
            if (indicatorOpt.text) {
              deprecateReplaceLog('text', 'name', 'radar.indicator');
            }
          });
        }
      });
      each$2(toArr(option.geo), function (geoOpt) {
        if (isObject$1(geoOpt)) {
          compatEC3CommonStyles(geoOpt);
          each$2(toArr(geoOpt.regions), function (regionObj) {
            compatEC3CommonStyles(regionObj);
          });
        }
      });
      each$2(toArr(option.timeline), function (timelineOpt) {
        compatEC3CommonStyles(timelineOpt);
        convertNormalEmphasis(timelineOpt, 'label');
        convertNormalEmphasis(timelineOpt, 'itemStyle');
        convertNormalEmphasis(timelineOpt, 'controlStyle', true);
        var data = timelineOpt.data;
        isArray(data) && each(data, function (item) {
          if (isObject(item)) {
            convertNormalEmphasis(item, 'label');
            convertNormalEmphasis(item, 'itemStyle');
          }
        });
      });
      each$2(toArr(option.toolbox), function (toolboxOpt) {
        convertNormalEmphasis(toolboxOpt, 'iconStyle');
        each$2(toolboxOpt.feature, function (featureOpt) {
          convertNormalEmphasis(featureOpt, 'iconStyle');
        });
      });
      compatTextStyle(toObj(option.axisPointer), 'label');
      compatTextStyle(toObj(option.tooltip).axisPointer, 'label'); // Clean logs
      // storedLogs = {};
    }

    function get(opt, path) {
      var pathArr = path.split(',');
      var obj = opt;

      for (var i = 0; i < pathArr.length; i++) {
        obj = obj && obj[pathArr[i]];

        if (obj == null) {
          break;
        }
      }

      return obj;
    }

    function set$1(opt, path, val, overwrite) {
      var pathArr = path.split(',');
      var obj = opt;
      var key;
      var i = 0;

      for (; i < pathArr.length - 1; i++) {
        key = pathArr[i];

        if (obj[key] == null) {
          obj[key] = {};
        }

        obj = obj[key];
      }

      if (overwrite || obj[pathArr[i]] == null) {
        obj[pathArr[i]] = val;
      }
    }

    function compatLayoutProperties(option) {
      option && each(LAYOUT_PROPERTIES, function (prop) {
        if (prop[0] in option && !(prop[1] in option)) {
          option[prop[1]] = option[prop[0]];
        }
      });
    }

    var LAYOUT_PROPERTIES = [['x', 'left'], ['y', 'top'], ['x2', 'right'], ['y2', 'bottom']];
    var COMPATITABLE_COMPONENTS = ['grid', 'geo', 'parallel', 'legend', 'toolbox', 'title', 'visualMap', 'dataZoom', 'timeline'];
    var BAR_ITEM_STYLE_MAP = [['borderRadius', 'barBorderRadius'], ['borderColor', 'barBorderColor'], ['borderWidth', 'barBorderWidth']];

    function compatBarItemStyle(option) {
      var itemStyle = option && option.itemStyle;

      if (itemStyle) {
        for (var i = 0; i < BAR_ITEM_STYLE_MAP.length; i++) {
          var oldName = BAR_ITEM_STYLE_MAP[i][1];
          var newName = BAR_ITEM_STYLE_MAP[i][0];

          if (itemStyle[oldName] != null) {
            itemStyle[newName] = itemStyle[oldName];

            if ("development" !== 'production') {
              deprecateReplaceLog(oldName, newName);
            }
          }
        }
      }
    }

    function compatPieLabel(option) {
      if (!option) {
        return;
      }

      if (option.alignTo === 'edge' && option.margin != null && option.edgeDistance == null) {
        if ("development" !== 'production') {
          deprecateReplaceLog('label.margin', 'label.edgeDistance', 'pie');
        }

        option.edgeDistance = option.margin;
      }
    }

    function compatSunburstState(option) {
      if (!option) {
        return;
      }

      if (option.downplay && !option.blur) {
        option.blur = option.downplay;

        if ("development" !== 'production') {
          deprecateReplaceLog('downplay', 'blur', 'sunburst');
        }
      }
    }

    function compatGraphFocus(option) {
      if (!option) {
        return;
      }

      if (option.focusNodeAdjacency != null) {
        option.emphasis = option.emphasis || {};

        if (option.emphasis.focus == null) {
          if ("development" !== 'production') {
            deprecateReplaceLog('focusNodeAdjacency', 'emphasis: { focus: \'adjacency\'}', 'graph/sankey');
          }

          option.emphasis.focus = 'adjacency';
        }
      }
    }

    function traverseTree(data, cb) {
      if (data) {
        for (var i = 0; i < data.length; i++) {
          cb(data[i]);
          data[i] && traverseTree(data[i].children, cb);
        }
      }
    }

    function globalBackwardCompat(option, isTheme) {
      globalCompatStyle(option, isTheme); // Make sure series array for model initialization.

      option.series = normalizeToArray(option.series);
      each(option.series, function (seriesOpt) {
        if (!isObject(seriesOpt)) {
          return;
        }

        var seriesType = seriesOpt.type;

        if (seriesType === 'line') {
          if (seriesOpt.clipOverflow != null) {
            seriesOpt.clip = seriesOpt.clipOverflow;

            if ("development" !== 'production') {
              deprecateReplaceLog('clipOverflow', 'clip', 'line');
            }
          }
        } else if (seriesType === 'pie' || seriesType === 'gauge') {
          if (seriesOpt.clockWise != null) {
            seriesOpt.clockwise = seriesOpt.clockWise;

            if ("development" !== 'production') {
              deprecateReplaceLog('clockWise', 'clockwise');
            }
          }

          compatPieLabel(seriesOpt.label);
          var data = seriesOpt.data;

          if (data && !isTypedArray(data)) {
            for (var i = 0; i < data.length; i++) {
              compatPieLabel(data[i]);
            }
          }

          if (seriesOpt.hoverOffset != null) {
            seriesOpt.emphasis = seriesOpt.emphasis || {};

            if (seriesOpt.emphasis.scaleSize = null) {
              if ("development" !== 'production') {
                deprecateReplaceLog('hoverOffset', 'emphasis.scaleSize');
              }

              seriesOpt.emphasis.scaleSize = seriesOpt.hoverOffset;
            }
          }
        } else if (seriesType === 'gauge') {
          var pointerColor = get(seriesOpt, 'pointer.color');
          pointerColor != null && set$1(seriesOpt, 'itemStyle.color', pointerColor);
        } else if (seriesType === 'bar') {
          compatBarItemStyle(seriesOpt);
          compatBarItemStyle(seriesOpt.backgroundStyle);
          compatBarItemStyle(seriesOpt.emphasis);
          var data = seriesOpt.data;

          if (data && !isTypedArray(data)) {
            for (var i = 0; i < data.length; i++) {
              if (typeof data[i] === 'object') {
                compatBarItemStyle(data[i]);
                compatBarItemStyle(data[i] && data[i].emphasis);
              }
            }
          }
        } else if (seriesType === 'sunburst') {
          var highlightPolicy = seriesOpt.highlightPolicy;

          if (highlightPolicy) {
            seriesOpt.emphasis = seriesOpt.emphasis || {};

            if (!seriesOpt.emphasis.focus) {
              seriesOpt.emphasis.focus = highlightPolicy;

              if ("development" !== 'production') {
                deprecateReplaceLog('highlightPolicy', 'emphasis.focus', 'sunburst');
              }
            }
          }

          compatSunburstState(seriesOpt);
          traverseTree(seriesOpt.data, compatSunburstState);
        } else if (seriesType === 'graph' || seriesType === 'sankey') {
          compatGraphFocus(seriesOpt); // TODO nodes, edges?
        } else if (seriesType === 'map') {
          if (seriesOpt.mapType && !seriesOpt.map) {
            if ("development" !== 'production') {
              deprecateReplaceLog('mapType', 'map', 'map');
            }

            seriesOpt.map = seriesOpt.mapType;
          }

          if (seriesOpt.mapLocation) {
            if ("development" !== 'production') {
              deprecateLog('`mapLocation` is not used anymore.');
            }

            defaults(seriesOpt, seriesOpt.mapLocation);
          }
        }

        if (seriesOpt.hoverAnimation != null) {
          seriesOpt.emphasis = seriesOpt.emphasis || {};

          if (seriesOpt.emphasis && seriesOpt.emphasis.scale == null) {
            if ("development" !== 'production') {
              deprecateReplaceLog('hoverAnimation', 'emphasis.scale');
            }

            seriesOpt.emphasis.scale = seriesOpt.hoverAnimation;
          }
        }

        compatLayoutProperties(seriesOpt);
      }); // dataRange has changed to visualMap

      if (option.dataRange) {
        option.visualMap = option.dataRange;
      }

      each(COMPATITABLE_COMPONENTS, function (componentName) {
        var options = option[componentName];

        if (options) {
          if (!isArray(options)) {
            options = [options];
          }

          each(options, function (option) {
            compatLayoutProperties(option);
          });
        }
      });
    }

    //     data processing stage is blocked in stream.
    //     See <module:echarts/stream/Scheduler#performDataProcessorTasks>
    // (2) Only register once when import repeatedly.
    //     Should be executed after series is filtered and before stack calculation.

    function dataStack(ecModel) {
      var stackInfoMap = createHashMap();
      ecModel.eachSeries(function (seriesModel) {
        var stack = seriesModel.get('stack'); // Compatible: when `stack` is set as '', do not stack.

        if (stack) {
          var stackInfoList = stackInfoMap.get(stack) || stackInfoMap.set(stack, []);
          var data = seriesModel.getData();
          var stackInfo = {
            // Used for calculate axis extent automatically.
            // TODO: Type getCalculationInfo return more specific type?
            stackResultDimension: data.getCalculationInfo('stackResultDimension'),
            stackedOverDimension: data.getCalculationInfo('stackedOverDimension'),
            stackedDimension: data.getCalculationInfo('stackedDimension'),
            stackedByDimension: data.getCalculationInfo('stackedByDimension'),
            isStackedByIndex: data.getCalculationInfo('isStackedByIndex'),
            data: data,
            seriesModel: seriesModel
          }; // If stacked on axis that do not support data stack.

          if (!stackInfo.stackedDimension || !(stackInfo.isStackedByIndex || stackInfo.stackedByDimension)) {
            return;
          }

          stackInfoList.length && data.setCalculationInfo('stackedOnSeries', stackInfoList[stackInfoList.length - 1].seriesModel);
          stackInfoList.push(stackInfo);
        }
      });
      stackInfoMap.each(calculateStack);
    }

    function calculateStack(stackInfoList) {
      each(stackInfoList, function (targetStackInfo, idxInStack) {
        var resultVal = [];
        var resultNaN = [NaN, NaN];
        var dims = [targetStackInfo.stackResultDimension, targetStackInfo.stackedOverDimension];
        var targetData = targetStackInfo.data;
        var isStackedByIndex = targetStackInfo.isStackedByIndex;
        var stackStrategy = targetStackInfo.seriesModel.get('stackStrategy') || 'samesign'; // Should not write on raw data, because stack series model list changes
        // depending on legend selection.

        targetData.modify(dims, function (v0, v1, dataIndex) {
          var sum = targetData.get(targetStackInfo.stackedDimension, dataIndex); // Consider `connectNulls` of line area, if value is NaN, stackedOver
          // should also be NaN, to draw a appropriate belt area.

          if (isNaN(sum)) {
            return resultNaN;
          }

          var byValue;
          var stackedDataRawIndex;

          if (isStackedByIndex) {
            stackedDataRawIndex = targetData.getRawIndex(dataIndex);
          } else {
            byValue = targetData.get(targetStackInfo.stackedByDimension, dataIndex);
          } // If stackOver is NaN, chart view will render point on value start.


          var stackedOver = NaN;

          for (var j = idxInStack - 1; j >= 0; j--) {
            var stackInfo = stackInfoList[j]; // Has been optimized by inverted indices on `stackedByDimension`.

            if (!isStackedByIndex) {
              stackedDataRawIndex = stackInfo.data.rawIndexOf(stackInfo.stackedByDimension, byValue);
            }

            if (stackedDataRawIndex >= 0) {
              var val = stackInfo.data.getByRawIndex(stackInfo.stackResultDimension, stackedDataRawIndex); // Considering positive stack, negative stack and empty data

              if (stackStrategy === 'all' // single stack group
              || stackStrategy === 'positive' && val > 0 || stackStrategy === 'negative' && val < 0 || stackStrategy === 'samesign' && sum >= 0 && val > 0 // All positive stack
              || stackStrategy === 'samesign' && sum <= 0 && val < 0 // All negative stack
              ) {
                  // The sum has to be very small to be affected by the
                  // floating arithmetic problem. An incorrect result will probably
                  // cause axis min/max to be filtered incorrectly.
                  sum = addSafe(sum, val);
                  stackedOver = val;
                  break;
                }
            }
          }

          resultVal[0] = sum;
          resultVal[1] = stackedOver;
          return resultVal;
        });
      });
    }

    var SourceImpl =
    /** @class */
    function () {
      function SourceImpl(fields) {
        this.data = fields.data || (fields.sourceFormat === SOURCE_FORMAT_KEYED_COLUMNS ? {} : []);
        this.sourceFormat = fields.sourceFormat || SOURCE_FORMAT_UNKNOWN; // Visit config

        this.seriesLayoutBy = fields.seriesLayoutBy || SERIES_LAYOUT_BY_COLUMN;
        this.startIndex = fields.startIndex || 0;
        this.dimensionsDetectedCount = fields.dimensionsDetectedCount;
        this.metaRawOption = fields.metaRawOption;
        var dimensionsDefine = this.dimensionsDefine = fields.dimensionsDefine;

        if (dimensionsDefine) {
          for (var i = 0; i < dimensionsDefine.length; i++) {
            var dim = dimensionsDefine[i];

            if (dim.type == null) {
              if (guessOrdinal(this, i) === BE_ORDINAL.Must) {
                dim.type = 'ordinal';
              }
            }
          }
        }
      }

      return SourceImpl;
    }();

    function isSourceInstance(val) {
      return val instanceof SourceImpl;
    }
    /**
     * Create a source from option.
     * NOTE: Created source is immutable. Don't change any properties in it.
     */

    function createSource(sourceData, thisMetaRawOption, // can be null. If not provided, auto detect it from `sourceData`.
    sourceFormat) {
      sourceFormat = sourceFormat || detectSourceFormat(sourceData);
      var seriesLayoutBy = thisMetaRawOption.seriesLayoutBy;
      var determined = determineSourceDimensions(sourceData, sourceFormat, seriesLayoutBy, thisMetaRawOption.sourceHeader, thisMetaRawOption.dimensions);
      var source = new SourceImpl({
        data: sourceData,
        sourceFormat: sourceFormat,
        seriesLayoutBy: seriesLayoutBy,
        dimensionsDefine: determined.dimensionsDefine,
        startIndex: determined.startIndex,
        dimensionsDetectedCount: determined.dimensionsDetectedCount,
        metaRawOption: clone(thisMetaRawOption)
      });
      return source;
    }
    /**
     * Wrap original series data for some compatibility cases.
     */

    function createSourceFromSeriesDataOption(data) {
      return new SourceImpl({
        data: data,
        sourceFormat: isTypedArray(data) ? SOURCE_FORMAT_TYPED_ARRAY : SOURCE_FORMAT_ORIGINAL
      });
    }
    /**
     * Clone source but excludes source data.
     */

    function cloneSourceShallow(source) {
      return new SourceImpl({
        data: source.data,
        sourceFormat: source.sourceFormat,
        seriesLayoutBy: source.seriesLayoutBy,
        dimensionsDefine: clone(source.dimensionsDefine),
        startIndex: source.startIndex,
        dimensionsDetectedCount: source.dimensionsDetectedCount
      });
    }
    /**
     * Note: An empty array will be detected as `SOURCE_FORMAT_ARRAY_ROWS`.
     */

    function detectSourceFormat(data) {
      var sourceFormat = SOURCE_FORMAT_UNKNOWN;

      if (isTypedArray(data)) {
        sourceFormat = SOURCE_FORMAT_TYPED_ARRAY;
      } else if (isArray(data)) {
        // FIXME Whether tolerate null in top level array?
        if (data.length === 0) {
          sourceFormat = SOURCE_FORMAT_ARRAY_ROWS;
        }

        for (var i = 0, len = data.length; i < len; i++) {
          var item = data[i];

          if (item == null) {
            continue;
          } else if (isArray(item)) {
            sourceFormat = SOURCE_FORMAT_ARRAY_ROWS;
            break;
          } else if (isObject(item)) {
            sourceFormat = SOURCE_FORMAT_OBJECT_ROWS;
            break;
          }
        }
      } else if (isObject(data)) {
        for (var key in data) {
          if (hasOwn(data, key) && isArrayLike(data[key])) {
            sourceFormat = SOURCE_FORMAT_KEYED_COLUMNS;
            break;
          }
        }
      }

      return sourceFormat;
    }
    /**
     * Determine the source definitions from data standalone dimensions definitions
     * are not specified.
     */

    function determineSourceDimensions(data, sourceFormat, seriesLayoutBy, sourceHeader, // standalone raw dimensions definition, like:
    // {
    //     dimensions: ['aa', 'bb', { name: 'cc', type: 'time' }]
    // }
    // in `dataset` or `series`
    dimensionsDefine) {
      var dimensionsDetectedCount;
      var startIndex; // PENDING: Could data be null/undefined here?
      // currently, if `dataset.source` not specified, error thrown.
      // if `series.data` not specified, nothing rendered without error thrown.
      // Should test these cases.

      if (!data) {
        return {
          dimensionsDefine: normalizeDimensionsOption(dimensionsDefine),
          startIndex: startIndex,
          dimensionsDetectedCount: dimensionsDetectedCount
        };
      }

      if (sourceFormat === SOURCE_FORMAT_ARRAY_ROWS) {
        var dataArrayRows = data; // Rule: Most of the first line are string: it is header.
        // Caution: consider a line with 5 string and 1 number,
        // it still can not be sure it is a head, because the
        // 5 string may be 5 values of category columns.

        if (sourceHeader === 'auto' || sourceHeader == null) {
          arrayRowsTravelFirst(function (val) {
            // '-' is regarded as null/undefined.
            if (val != null && val !== '-') {
              if (isString(val)) {
                startIndex == null && (startIndex = 1);
              } else {
                startIndex = 0;
              }
            } // 10 is an experience number, avoid long loop.

          }, seriesLayoutBy, dataArrayRows, 10);
        } else {
          startIndex = isNumber(sourceHeader) ? sourceHeader : sourceHeader ? 1 : 0;
        }

        if (!dimensionsDefine && startIndex === 1) {
          dimensionsDefine = [];
          arrayRowsTravelFirst(function (val, index) {
            dimensionsDefine[index] = val != null ? val + '' : '';
          }, seriesLayoutBy, dataArrayRows, Infinity);
        }

        dimensionsDetectedCount = dimensionsDefine ? dimensionsDefine.length : seriesLayoutBy === SERIES_LAYOUT_BY_ROW ? dataArrayRows.length : dataArrayRows[0] ? dataArrayRows[0].length : null;
      } else if (sourceFormat === SOURCE_FORMAT_OBJECT_ROWS) {
        if (!dimensionsDefine) {
          dimensionsDefine = objectRowsCollectDimensions(data);
        }
      } else if (sourceFormat === SOURCE_FORMAT_KEYED_COLUMNS) {
        if (!dimensionsDefine) {
          dimensionsDefine = [];
          each(data, function (colArr, key) {
            dimensionsDefine.push(key);
          });
        }
      } else if (sourceFormat === SOURCE_FORMAT_ORIGINAL) {
        var value0 = getDataItemValue(data[0]);
        dimensionsDetectedCount = isArray(value0) && value0.length || 1;
      } else if (sourceFormat === SOURCE_FORMAT_TYPED_ARRAY) {
        if ("development" !== 'production') {
          assert(!!dimensionsDefine, 'dimensions must be given if data is TypedArray.');
        }
      }

      return {
        startIndex: startIndex,
        dimensionsDefine: normalizeDimensionsOption(dimensionsDefine),
        dimensionsDetectedCount: dimensionsDetectedCount
      };
    }

    function objectRowsCollectDimensions(data) {
      var firstIndex = 0;
      var obj;

      while (firstIndex < data.length && !(obj = data[firstIndex++])) {} // jshint ignore: line


      if (obj) {
        return keys(obj);
      }
    } // Consider dimensions defined like ['A', 'price', 'B', 'price', 'C', 'price'],
    // which is reasonable. But dimension name is duplicated.
    // Returns undefined or an array contains only object without null/undefined or string.


    function normalizeDimensionsOption(dimensionsDefine) {
      if (!dimensionsDefine) {
        // The meaning of null/undefined is different from empty array.
        return;
      }

      var nameMap = createHashMap();
      return map(dimensionsDefine, function (rawItem, index) {
        rawItem = isObject(rawItem) ? rawItem : {
          name: rawItem
        }; // Other fields will be discarded.

        var item = {
          name: rawItem.name,
          displayName: rawItem.displayName,
          type: rawItem.type
        }; // User can set null in dimensions.
        // We don't auto specify name, otherwise a given name may
        // cause it to be referred unexpectedly.

        if (item.name == null) {
          return item;
        } // Also consider number form like 2012.


        item.name += ''; // User may also specify displayName.
        // displayName will always exists except user not
        // specified or dim name is not specified or detected.
        // (A auto generated dim name will not be used as
        // displayName).

        if (item.displayName == null) {
          item.displayName = item.name;
        }

        var exist = nameMap.get(item.name);

        if (!exist) {
          nameMap.set(item.name, {
            count: 1
          });
        } else {
          item.name += '-' + exist.count++;
        }

        return item;
      });
    }

    function arrayRowsTravelFirst(cb, seriesLayoutBy, data, maxLoop) {
      if (seriesLayoutBy === SERIES_LAYOUT_BY_ROW) {
        for (var i = 0; i < data.length && i < maxLoop; i++) {
          cb(data[i] ? data[i][0] : null, i);
        }
      } else {
        var value0 = data[0] || [];

        for (var i = 0; i < value0.length && i < maxLoop; i++) {
          cb(value0[i], i);
        }
      }
    }

    function shouldRetrieveDataByName(source) {
      var sourceFormat = source.sourceFormat;
      return sourceFormat === SOURCE_FORMAT_OBJECT_ROWS || sourceFormat === SOURCE_FORMAT_KEYED_COLUMNS;
    }

    /*
    * Licensed to the Apache Software Foundation (ASF) under one
    * or more contributor license agreements.  See the NOTICE file
    * distributed with this work for additional information
    * regarding copyright ownership.  The ASF licenses this file
    * to you under the Apache License, Version 2.0 (the
    * "License"); you may not use this file except in compliance
    * with the License.  You may obtain a copy of the License at
    *
    *   http://www.apache.org/licenses/LICENSE-2.0
    *
    * Unless required by applicable law or agreed to in writing,
    * software distributed under the License is distributed on an
    * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
    * KIND, either express or implied.  See the License for the
    * specific language governing permissions and limitations
    * under the License.
    */


    /**
     * AUTO-GENERATED FILE. DO NOT MODIFY.
     */

    /*
    * Licensed to the Apache Software Foundation (ASF) under one
    * or more contributor license agreements.  See the NOTICE file
    * distributed with this work for additional information
    * regarding copyright ownership.  The ASF licenses this file
    * to you under the Apache License, Version 2.0 (the
    * "License"); you may not use this file except in compliance
    * with the License.  You may obtain a copy of the License at
    *
    *   http://www.apache.org/licenses/LICENSE-2.0
    *
    * Unless required by applicable law or agreed to in writing,
    * software distributed under the License is distributed on an
    * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
    * KIND, either express or implied.  See the License for the
    * specific language governing permissions and limitations
    * under the License.
    */
    var _a, _b, _c; // TODO
    var providerMethods;
    var mountMethods;
    /**
     * If normal array used, mutable chunk size is supported.
     * If typed array used, chunk size must be fixed.
     */

    var DefaultDataProvider =
    /** @class */
    function () {
      function DefaultDataProvider(sourceParam, dimSize) {
        // let source: Source;
        var source = !isSourceInstance(sourceParam) ? createSourceFromSeriesDataOption(sourceParam) : sourceParam; // declare source is Source;

        this._source = source;
        var data = this._data = source.data; // Typed array. TODO IE10+?

        if (source.sourceFormat === SOURCE_FORMAT_TYPED_ARRAY) {
          if ("development" !== 'production') {
            if (dimSize == null) {
              throw new Error('Typed array data must specify dimension size');
            }
          }

          this._offset = 0;
          this._dimSize = dimSize;
          this._data = data;
        }

        mountMethods(this, data, source);
      }

      DefaultDataProvider.prototype.getSource = function () {
        return this._source;
      };

      DefaultDataProvider.prototype.count = function () {
        return 0;
      };

      DefaultDataProvider.prototype.getItem = function (idx, out) {
        return;
      };

      DefaultDataProvider.prototype.appendData = function (newData) {};

      DefaultDataProvider.prototype.clean = function () {};

      DefaultDataProvider.protoInitialize = function () {
        // PENDING: To avoid potential incompat (e.g., prototype
        // is visited somewhere), still init them on prototype.
        var proto = DefaultDataProvider.prototype;
        proto.pure = false;
        proto.persistent = true;
      }();

      DefaultDataProvider.internalField = function () {
        var _a;

        mountMethods = function (provider, data, source) {
          var sourceFormat = source.sourceFormat;
          var seriesLayoutBy = source.seriesLayoutBy;
          var startIndex = source.startIndex;
          var dimsDef = source.dimensionsDefine;
          var methods = providerMethods[getMethodMapKey(sourceFormat, seriesLayoutBy)];

          if ("development" !== 'production') {
            assert(methods, 'Invalide sourceFormat: ' + sourceFormat);
          }

          extend(provider, methods);

          if (sourceFormat === SOURCE_FORMAT_TYPED_ARRAY) {
            provider.getItem = getItemForTypedArray;
            provider.count = countForTypedArray;
            provider.fillStorage = fillStorageForTypedArray;
          } else {
            var rawItemGetter = getRawSourceItemGetter(sourceFormat, seriesLayoutBy);
            provider.getItem = bind(rawItemGetter, null, data, startIndex, dimsDef);
            var rawCounter = getRawSourceDataCounter(sourceFormat, seriesLayoutBy);
            provider.count = bind(rawCounter, null, data, startIndex, dimsDef);
          }
        };

        var getItemForTypedArray = function (idx, out) {
          idx = idx - this._offset;
          out = out || [];
          var data = this._data;
          var dimSize = this._dimSize;
          var offset = dimSize * idx;

          for (var i = 0; i < dimSize; i++) {
            out[i] = data[offset + i];
          }

          return out;
        };

        var fillStorageForTypedArray = function (start, end, storage, extent) {
          var data = this._data;
          var dimSize = this._dimSize;

          for (var dim = 0; dim < dimSize; dim++) {
            var dimExtent = extent[dim];
            var min = dimExtent[0] == null ? Infinity : dimExtent[0];
            var max = dimExtent[1] == null ? -Infinity : dimExtent[1];
            var count = end - start;
            var arr = storage[dim];

            for (var i = 0; i < count; i++) {
              // appendData with TypedArray will always do replace in provider.
              var val = data[i * dimSize + dim];
              arr[start + i] = val;
              val < min && (min = val);
              val > max && (max = val);
            }

            dimExtent[0] = min;
            dimExtent[1] = max;
          }
        };

        var countForTypedArray = function () {
          return this._data ? this._data.length / this._dimSize : 0;
        };

        providerMethods = (_a = {}, _a[SOURCE_FORMAT_ARRAY_ROWS + '_' + SERIES_LAYOUT_BY_COLUMN] = {
          pure: true,
          appendData: appendDataSimply
        }, _a[SOURCE_FORMAT_ARRAY_ROWS + '_' + SERIES_LAYOUT_BY_ROW] = {
          pure: true,
          appendData: function () {
            throw new Error('Do not support appendData when set seriesLayoutBy: "row".');
          }
        }, _a[SOURCE_FORMAT_OBJECT_ROWS] = {
          pure: true,
          appendData: appendDataSimply
        }, _a[SOURCE_FORMAT_KEYED_COLUMNS] = {
          pure: true,
          appendData: function (newData) {
            var data = this._data;
            each(newData, function (newCol, key) {
              var oldCol = data[key] || (data[key] = []);

              for (var i = 0; i < (newCol || []).length; i++) {
                oldCol.push(newCol[i]);
              }
            });
          }
        }, _a[SOURCE_FORMAT_ORIGINAL] = {
          appendData: appendDataSimply
        }, _a[SOURCE_FORMAT_TYPED_ARRAY] = {
          persistent: false,
          pure: true,
          appendData: function (newData) {
            if ("development" !== 'production') {
              assert(isTypedArray(newData), 'Added data must be TypedArray if data in initialization is TypedArray');
            }

            this._data = newData;
          },
          // Clean self if data is already used.
          clean: function () {
            // PENDING
            this._offset += this.count();
            this._data = null;
          }
        }, _a);

        function appendDataSimply(newData) {
          for (var i = 0; i < newData.length; i++) {
            this._data.push(newData[i]);
          }
        }
      }();

      return DefaultDataProvider;
    }();

    var getItemSimply = function (rawData, startIndex, dimsDef, idx) {
      return rawData[idx];
    };

    var rawSourceItemGetterMap = (_a = {}, _a[SOURCE_FORMAT_ARRAY_ROWS + '_' + SERIES_LAYOUT_BY_COLUMN] = function (rawData, startIndex, dimsDef, idx) {
      return rawData[idx + startIndex];
    }, _a[SOURCE_FORMAT_ARRAY_ROWS + '_' + SERIES_LAYOUT_BY_ROW] = function (rawData, startIndex, dimsDef, idx, out) {
      idx += startIndex;
      var item = out || [];
      var data = rawData;

      for (var i = 0; i < data.length; i++) {
        var row = data[i];
        item[i] = row ? row[idx] : null;
      }

      return item;
    }, _a[SOURCE_FORMAT_OBJECT_ROWS] = getItemSimply, _a[SOURCE_FORMAT_KEYED_COLUMNS] = function (rawData, startIndex, dimsDef, idx, out) {
      var item = out || [];

      for (var i = 0; i < dimsDef.length; i++) {
        var dimName = dimsDef[i].name;

        if ("development" !== 'production') {
          if (dimName == null) {
            throw new Error();
          }
        }

        var col = rawData[dimName];
        item[i] = col ? col[idx] : null;
      }

      return item;
    }, _a[SOURCE_FORMAT_ORIGINAL] = getItemSimply, _a);
    function getRawSourceItemGetter(sourceFormat, seriesLayoutBy) {
      var method = rawSourceItemGetterMap[getMethodMapKey(sourceFormat, seriesLayoutBy)];

      if ("development" !== 'production') {
        assert(method, 'Do not support get item on "' + sourceFormat + '", "' + seriesLayoutBy + '".');
      }

      return method;
    }

    var countSimply = function (rawData, startIndex, dimsDef) {
      return rawData.length;
    };

    var rawSourceDataCounterMap = (_b = {}, _b[SOURCE_FORMAT_ARRAY_ROWS + '_' + SERIES_LAYOUT_BY_COLUMN] = function (rawData, startIndex, dimsDef) {
      return Math.max(0, rawData.length - startIndex);
    }, _b[SOURCE_FORMAT_ARRAY_ROWS + '_' + SERIES_LAYOUT_BY_ROW] = function (rawData, startIndex, dimsDef) {
      var row = rawData[0];
      return row ? Math.max(0, row.length - startIndex) : 0;
    }, _b[SOURCE_FORMAT_OBJECT_ROWS] = countSimply, _b[SOURCE_FORMAT_KEYED_COLUMNS] = function (rawData, startIndex, dimsDef) {
      var dimName = dimsDef[0].name;

      if ("development" !== 'production') {
        if (dimName == null) {
          throw new Error();
        }
      }

      var col = rawData[dimName];
      return col ? col.length : 0;
    }, _b[SOURCE_FORMAT_ORIGINAL] = countSimply, _b);
    function getRawSourceDataCounter(sourceFormat, seriesLayoutBy) {
      var method = rawSourceDataCounterMap[getMethodMapKey(sourceFormat, seriesLayoutBy)];

      if ("development" !== 'production') {
        assert(method, 'Do not support count on "' + sourceFormat + '", "' + seriesLayoutBy + '".');
      }

      return method;
    }

    var getRawValueSimply = function (dataItem, dimIndex, property) {
      return dataItem[dimIndex];
    };

    var rawSourceValueGetterMap = (_c = {}, _c[SOURCE_FORMAT_ARRAY_ROWS] = getRawValueSimply, _c[SOURCE_FORMAT_OBJECT_ROWS] = function (dataItem, dimIndex, property) {
      return dataItem[property];
    }, _c[SOURCE_FORMAT_KEYED_COLUMNS] = getRawValueSimply, _c[SOURCE_FORMAT_ORIGINAL] = function (dataItem, dimIndex, property) {
      // FIXME: In some case (markpoint in geo (geo-map.html)),
      // dataItem is {coord: [...]}
      var value = getDataItemValue(dataItem);
      return !(value instanceof Array) ? value : value[dimIndex];
    }, _c[SOURCE_FORMAT_TYPED_ARRAY] = getRawValueSimply, _c);
    function getRawSourceValueGetter(sourceFormat) {
      var method = rawSourceValueGetterMap[sourceFormat];

      if ("development" !== 'production') {
        assert(method, 'Do not support get value on "' + sourceFormat + '".');
      }

      return method;
    }

    function getMethodMapKey(sourceFormat, seriesLayoutBy) {
      return sourceFormat === SOURCE_FORMAT_ARRAY_ROWS ? sourceFormat + '_' + seriesLayoutBy : sourceFormat;
    } // ??? FIXME can these logic be more neat: getRawValue, getRawDataItem,
    // Consider persistent.
    // Caution: why use raw value to display on label or tooltip?
    // A reason is to avoid format. For example time value we do not know
    // how to format is expected. More over, if stack is used, calculated
    // value may be 0.91000000001, which have brings trouble to display.
    // TODO: consider how to treat null/undefined/NaN when display?


    function retrieveRawValue(data, dataIndex, // If dimIndex is null/undefined, return OptionDataItem.
    // Otherwise, return OptionDataValue.
    dim) {
      if (!data) {
        return;
      } // Consider data may be not persistent.


      var dataItem = data.getRawDataItem(dataIndex);

      if (dataItem == null) {
        return;
      }

      var store = data.getStore();
      var sourceFormat = store.getSource().sourceFormat;

      if (dim != null) {
        var dimIndex = data.getDimensionIndex(dim);
        var property = store.getDimensionProperty(dimIndex);
        return getRawSourceValueGetter(sourceFormat)(dataItem, dimIndex, property);
      } else {
        var result = dataItem;

        if (sourceFormat === SOURCE_FORMAT_ORIGINAL) {
          result = getDataItemValue(dataItem);
        }

        return result;
      }
    }

    var DIMENSION_LABEL_REG = /\{@(.+?)\}/g;

    var DataFormatMixin =
    /** @class */
    function () {
      function DataFormatMixin() {}
      /**
       * Get params for formatter
       */


      DataFormatMixin.prototype.getDataParams = function (dataIndex, dataType) {
        var data = this.getData(dataType);
        var rawValue = this.getRawValue(dataIndex, dataType);
        var rawDataIndex = data.getRawIndex(dataIndex);
        var name = data.getName(dataIndex);
        var itemOpt = data.getRawDataItem(dataIndex);
        var style = data.getItemVisual(dataIndex, 'style');
        var color = style && style[data.getItemVisual(dataIndex, 'drawType') || 'fill'];
        var borderColor = style && style.stroke;
        var mainType = this.mainType;
        var isSeries = mainType === 'series';
        var userOutput = data.userOutput && data.userOutput.get();
        return {
          componentType: mainType,
          componentSubType: this.subType,
          componentIndex: this.componentIndex,
          seriesType: isSeries ? this.subType : null,
          seriesIndex: this.seriesIndex,
          seriesId: isSeries ? this.id : null,
          seriesName: isSeries ? this.name : null,
          name: name,
          dataIndex: rawDataIndex,
          data: itemOpt,
          dataType: dataType,
          value: rawValue,
          color: color,
          borderColor: borderColor,
          dimensionNames: userOutput ? userOutput.fullDimensions : null,
          encode: userOutput ? userOutput.encode : null,
          // Param name list for mapping `a`, `b`, `c`, `d`, `e`
          $vars: ['seriesName', 'name', 'value']
        };
      };
      /**
       * Format label
       * @param dataIndex
       * @param status 'normal' by default
       * @param dataType
       * @param labelDimIndex Only used in some chart that
       *        use formatter in different dimensions, like radar.
       * @param formatter Formatter given outside.
       * @return return null/undefined if no formatter
       */


      DataFormatMixin.prototype.getFormattedLabel = function (dataIndex, status, dataType, labelDimIndex, formatter, extendParams) {
        status = status || 'normal';
        var data = this.getData(dataType);
        var params = this.getDataParams(dataIndex, dataType);

        if (extendParams) {
          params.value = extendParams.interpolatedValue;
        }

        if (labelDimIndex != null && isArray(params.value)) {
          params.value = params.value[labelDimIndex];
        }

        if (!formatter) {
          var itemModel = data.getItemModel(dataIndex); // @ts-ignore

          formatter = itemModel.get(status === 'normal' ? ['label', 'formatter'] : [status, 'label', 'formatter']);
        }

        if (isFunction(formatter)) {
          params.status = status;
          params.dimensionIndex = labelDimIndex;
          return formatter(params);
        } else if (isString(formatter)) {
          var str = formatTpl(formatter, params); // Support 'aaa{@[3]}bbb{@product}ccc'.
          // Do not support '}' in dim name util have to.

          return str.replace(DIMENSION_LABEL_REG, function (origin, dimStr) {
            var len = dimStr.length;
            var dimLoose = dimStr;

            if (dimLoose.charAt(0) === '[' && dimLoose.charAt(len - 1) === ']') {
              dimLoose = +dimLoose.slice(1, len - 1); // Also support: '[]' => 0

              if ("development" !== 'production') {
                if (isNaN(dimLoose)) {
                  error("Invalide label formatter: @" + dimStr + ", only support @[0], @[1], @[2], ...");
                }
              }
            }

            var val = retrieveRawValue(data, dataIndex, dimLoose);

            if (extendParams && isArray(extendParams.interpolatedValue)) {
              var dimIndex = data.getDimensionIndex(dimLoose);

              if (dimIndex >= 0) {
                val = extendParams.interpolatedValue[dimIndex];
              }
            }

            return val != null ? val + '' : '';
          });
        }
      };
      /**
       * Get raw value in option
       */


      DataFormatMixin.prototype.getRawValue = function (idx, dataType) {
        return retrieveRawValue(this.getData(dataType), idx);
      };
      /**
       * Should be implemented.
       * @param {number} dataIndex
       * @param {boolean} [multipleSeries=false]
       * @param {string} [dataType]
       */


      DataFormatMixin.prototype.formatTooltip = function (dataIndex, multipleSeries, dataType) {
        // Empty function
        return;
      };

      return DataFormatMixin;
    }();
    // but guess little chance has been used outside. Do we need to backward
    // compat it?
    // type TooltipFormatResultLegacyObject = {
    //     // `html` means the markup language text, either in 'html' or 'richText'.
    //     // The name `html` is not appropriate because in 'richText' it is not a HTML
    //     // string. But still support it for backward compatibility.
    //     html: string;
    //     markers: Dictionary<ColorString>;
    // };

    /**
     * For backward compat, normalize the return from `formatTooltip`.
     */

    function normalizeTooltipFormatResult(result) {
      var markupText; // let markers: Dictionary<ColorString>;

      var markupFragment;

      if (isObject(result)) {
        if (result.type) {
          markupFragment = result;
        } else {
          if ("development" !== 'production') {
            console.warn('The return type of `formatTooltip` is not supported: ' + makePrintable(result));
          }
        } // else {
        //     markupText = (result as TooltipFormatResultLegacyObject).html;
        //     markers = (result as TooltipFormatResultLegacyObject).markers;
        //     if (markersExisting) {
        //         markers = zrUtil.merge(markersExisting, markers);
        //     }
        // }

      } else {
        markupText = result;
      }

      return {
        text: markupText,
        // markers: markers || markersExisting,
        frag: markupFragment
      };
    }

    /**
     * @param {Object} define
     * @return See the return of `createTask`.
     */

    function createTask(define) {
      return new Task(define);
    }

    var Task =
    /** @class */
    function () {
      function Task(define) {
        define = define || {};
        this._reset = define.reset;
        this._plan = define.plan;
        this._count = define.count;
        this._onDirty = define.onDirty;
        this._dirty = true;
      }
      /**
       * @param step Specified step.
       * @param skip Skip customer perform call.
       * @param modBy Sampling window size.
       * @param modDataCount Sampling count.
       * @return whether unfinished.
       */


      Task.prototype.perform = function (performArgs) {
        var upTask = this._upstream;
        var skip = performArgs && performArgs.skip; // TODO some refactor.
        // Pull data. Must pull data each time, because context.data
        // may be updated by Series.setData.

        if (this._dirty && upTask) {
          var context = this.context;
          context.data = context.outputData = upTask.context.outputData;
        }

        if (this.__pipeline) {
          this.__pipeline.currentTask = this;
        }

        var planResult;

        if (this._plan && !skip) {
          planResult = this._plan(this.context);
        } // Support sharding by mod, which changes the render sequence and makes the rendered graphic
        // elements uniformed distributed when progress, especially when moving or zooming.


        var lastModBy = normalizeModBy(this._modBy);
        var lastModDataCount = this._modDataCount || 0;
        var modBy = normalizeModBy(performArgs && performArgs.modBy);
        var modDataCount = performArgs && performArgs.modDataCount || 0;

        if (lastModBy !== modBy || lastModDataCount !== modDataCount) {
          planResult = 'reset';
        }

        function normalizeModBy(val) {
          !(val >= 1) && (val = 1); // jshint ignore:line

          return val;
        }

        var forceFirstProgress;

        if (this._dirty || planResult === 'reset') {
          this._dirty = false;
          forceFirstProgress = this._doReset(skip);
        }

        this._modBy = modBy;
        this._modDataCount = modDataCount;
        var step = performArgs && performArgs.step;

        if (upTask) {
          if ("development" !== 'production') {
            assert(upTask._outputDueEnd != null);
          }

          this._dueEnd = upTask._outputDueEnd;
        } // DataTask or overallTask
        else {
            if ("development" !== 'production') {
              assert(!this._progress || this._count);
            }

            this._dueEnd = this._count ? this._count(this.context) : Infinity;
          } // Note: Stubs, that its host overall task let it has progress, has progress.
        // If no progress, pass index from upstream to downstream each time plan called.


        if (this._progress) {
          var start = this._dueIndex;
          var end = Math.min(step != null ? this._dueIndex + step : Infinity, this._dueEnd);

          if (!skip && (forceFirstProgress || start < end)) {
            var progress = this._progress;

            if (isArray(progress)) {
              for (var i = 0; i < progress.length; i++) {
                this._doProgress(progress[i], start, end, modBy, modDataCount);
              }
            } else {
              this._doProgress(progress, start, end, modBy, modDataCount);
            }
          }

          this._dueIndex = end; // If no `outputDueEnd`, assume that output data and
          // input data is the same, so use `dueIndex` as `outputDueEnd`.

          var outputDueEnd = this._settedOutputEnd != null ? this._settedOutputEnd : end;

          if ("development" !== 'production') {
            // ??? Can not rollback.
            assert(outputDueEnd >= this._outputDueEnd);
          }

          this._outputDueEnd = outputDueEnd;
        } else {
          // (1) Some overall task has no progress.
          // (2) Stubs, that its host overall task do not let it has progress, has no progress.
          // This should always be performed so it can be passed to downstream.
          this._dueIndex = this._outputDueEnd = this._settedOutputEnd != null ? this._settedOutputEnd : this._dueEnd;
        }

        return this.unfinished();
      };

      Task.prototype.dirty = function () {
        this._dirty = true;
        this._onDirty && this._onDirty(this.context);
      };

      Task.prototype._doProgress = function (progress, start, end, modBy, modDataCount) {
        iterator.reset(start, end, modBy, modDataCount);
        this._callingProgress = progress;

        this._callingProgress({
          start: start,
          end: end,
          count: end - start,
          next: iterator.next
        }, this.context);
      };

      Task.prototype._doReset = function (skip) {
        this._dueIndex = this._outputDueEnd = this._dueEnd = 0;
        this._settedOutputEnd = null;
        var progress;
        var forceFirstProgress;

        if (!skip && this._reset) {
          progress = this._reset(this.context);

          if (progress && progress.progress) {
            forceFirstProgress = progress.forceFirstProgress;
            progress = progress.progress;
          } // To simplify no progress checking, array must has item.


          if (isArray(progress) && !progress.length) {
            progress = null;
          }
        }

        this._progress = progress;
        this._modBy = this._modDataCount = null;
        var downstream = this._downstream;
        downstream && downstream.dirty();
        return forceFirstProgress;
      };

      Task.prototype.unfinished = function () {
        return this._progress && this._dueIndex < this._dueEnd;
      };
      /**
       * @param downTask The downstream task.
       * @return The downstream task.
       */


      Task.prototype.pipe = function (downTask) {
        if ("development" !== 'production') {
          assert(downTask && !downTask._disposed && downTask !== this);
        } // If already downstream, do not dirty downTask.


        if (this._downstream !== downTask || this._dirty) {
          this._downstream = downTask;
          downTask._upstream = this;
          downTask.dirty();
        }
      };

      Task.prototype.dispose = function () {
        if (this._disposed) {
          return;
        }

        this._upstream && (this._upstream._downstream = null);
        this._downstream && (this._downstream._upstream = null);
        this._dirty = false;
        this._disposed = true;
      };

      Task.prototype.getUpstream = function () {
        return this._upstream;
      };

      Task.prototype.getDownstream = function () {
        return this._downstream;
      };

      Task.prototype.setOutputEnd = function (end) {
        // This only happens in dataTask, dataZoom, map, currently.
        // where dataZoom do not set end each time, but only set
        // when reset. So we should record the set end, in case
        // that the stub of dataZoom perform again and earse the
        // set end by upstream.
        this._outputDueEnd = this._settedOutputEnd = end;
      };

      return Task;
    }();

    var iterator = function () {
      var end;
      var current;
      var modBy;
      var modDataCount;
      var winCount;
      var it = {
        reset: function (s, e, sStep, sCount) {
          current = s;
          end = e;
          modBy = sStep;
          modDataCount = sCount;
          winCount = Math.ceil(modDataCount / modBy);
          it.next = modBy > 1 && modDataCount > 0 ? modNext : sequentialNext;
        }
      };
      return it;

      function sequentialNext() {
        return current < end ? current++ : null;
      }

      function modNext() {
        var dataIndex = current % winCount * modBy + Math.ceil(current / winCount);
        var result = current >= end ? null : dataIndex < modDataCount ? dataIndex // If modDataCount is smaller than data.count() (consider `appendData` case),
        // Use normal linear rendering mode.
        : current;
        current++;
        return result;
      }
    }(); // -----------------------------------------------------------------------------
    // For stream debug (Should be commented out after used!)
    // @usage: printTask(this, 'begin');
    // @usage: printTask(this, null, {someExtraProp});
    // @usage: Use `__idxInPipeline` as conditional breakpiont.
    //
    // window.printTask = function (task: any, prefix: string, extra: { [key: string]: unknown }): void {
    //     window.ecTaskUID == null && (window.ecTaskUID = 0);
    //     task.uidDebug == null && (task.uidDebug = `task_${window.ecTaskUID++}`);
    //     task.agent && task.agent.uidDebug == null && (task.agent.uidDebug = `task_${window.ecTaskUID++}`);
    //     let props = [];
    //     if (task.__pipeline) {
    //         let val = `${task.__idxInPipeline}/${task.__pipeline.tail.__idxInPipeline} ${task.agent ? '(stub)' : ''}`;
    //         props.push({text: '__idxInPipeline/total', value: val});
    //     } else {
    //         let stubCount = 0;
    //         task.agentStubMap.each(() => stubCount++);
    //         props.push({text: 'idx', value: `overall (stubs: ${stubCount})`});
    //     }
    //     props.push({text: 'uid', value: task.uidDebug});
    //     if (task.__pipeline) {
    //         props.push({text: 'pipelineId', value: task.__pipeline.id});
    //         task.agent && props.push(
    //             {text: 'stubFor', value: task.agent.uidDebug}
    //         );
    //     }
    //     props.push(
    //         {text: 'dirty', value: task._dirty},
    //         {text: 'dueIndex', value: task._dueIndex},
    //         {text: 'dueEnd', value: task._dueEnd},
    //         {text: 'outputDueEnd', value: task._outputDueEnd}
    //     );
    //     if (extra) {
    //         Object.keys(extra).forEach(key => {
    //             props.push({text: key, value: extra[key]});
    //         });
    //     }
    //     let args = ['color: blue'];
    //     let msg = `%c[${prefix || 'T'}] %c` + props.map(item => (
    //         args.push('color: green', 'color: red'),
    //         `${item.text}: %c${item.value}`
    //     )).join('%c, ');
    //     console.log.apply(console, [msg].concat(args));
    //     // console.log(this);
    // };
    // window.printPipeline = function (task: any, prefix: string) {
    //     const pipeline = task.__pipeline;
    //     let currTask = pipeline.head;
    //     while (currTask) {
    //         window.printTask(currTask, prefix);
    //         currTask = currTask._downstream;
    //     }
    // };
    // window.showChain = function (chainHeadTask) {
    //     var chain = [];
    //     var task = chainHeadTask;
    //     while (task) {
    //         chain.push({
    //             task: task,
    //             up: task._upstream,
    //             down: task._downstream,
    //             idxInPipeline: task.__idxInPipeline
    //         });
    //         task = task._downstream;
    //     }
    //     return chain;
    // };
    // window.findTaskInChain = function (task, chainHeadTask) {
    //     let chain = window.showChain(chainHeadTask);
    //     let result = [];
    //     for (let i = 0; i < chain.length; i++) {
    //         let chainItem = chain[i];
    //         if (chainItem.task === task) {
    //             result.push(i);
    //         }
    //     }
    //     return result;
    // };
    // window.printChainAEachInChainB = function (chainHeadTaskA, chainHeadTaskB) {
    //     let chainA = window.showChain(chainHeadTaskA);
    //     for (let i = 0; i < chainA.length; i++) {
    //         console.log('chainAIdx:', i, 'inChainB:', window.findTaskInChain(chainA[i].task, chainHeadTaskB));
    //     }
    // };

    /**
     * Convert raw the value in to inner value in List.
     *
     * [Performance sensitive]
     *
     * [Caution]: this is the key logic of user value parser.
     * For backward compatibility, do not modify it until you have to!
     */

    function parseDataValue(value, // For high performance, do not omit the second param.
    opt) {
      // Performance sensitive.
      var dimType = opt && opt.type;

      if (dimType === 'ordinal') {
        // If given value is a category string
        return value;
      }

      if (dimType === 'time' // spead up when using timestamp
      && !isNumber(value) && value != null && value !== '-') {
        value = +parseDate(value);
      } // dimType defaults 'number'.
      // If dimType is not ordinal and value is null or undefined or NaN or '-',
      // parse to NaN.
      // number-like string (like ' 123 ') can be converted to a number.
      // where null/undefined or other string will be converted to NaN.


      return value == null || value === '' ? NaN // If string (like '-'), using '+' parse to NaN
      // If object, also parse to NaN
      : +value;
    }
    var valueParserMap = createHashMap({
      'number': function (val) {
        // Do not use `numericToNumber` here. We have `numericToNumber` by default.
        // Here the number parser can have loose rule:
        // enable to cut suffix: "120px" => 120, "14%" => 14.
        return parseFloat(val);
      },
      'time': function (val) {
        // return timestamp.
        return +parseDate(val);
      },
      'trim': function (val) {
        return isString(val) ? trim(val) : val;
      }
    });

    var SortOrderComparator =
    /** @class */
    function () {
      /**
       * @param order by default: 'asc'
       * @param incomparable by default: Always on the tail.
       *        That is, if 'asc' => 'max', if 'desc' => 'min'
       *        See the definition of "incomparable" in [SORT_COMPARISON_RULE].
       */
      function SortOrderComparator(order, incomparable) {
        var isDesc = order === 'desc';
        this._resultLT = isDesc ? 1 : -1;

        if (incomparable == null) {
          incomparable = isDesc ? 'min' : 'max';
        }

        this._incomparable = incomparable === 'min' ? -Infinity : Infinity;
      } // See [SORT_COMPARISON_RULE].
      // Performance sensitive.


      SortOrderComparator.prototype.evaluate = function (lval, rval) {
        // Most cases is 'number', and typeof maybe 10 times faseter than parseFloat.
        var lvalFloat = isNumber(lval) ? lval : numericToNumber(lval);
        var rvalFloat = isNumber(rval) ? rval : numericToNumber(rval);
        var lvalNotNumeric = isNaN(lvalFloat);
        var rvalNotNumeric = isNaN(rvalFloat);

        if (lvalNotNumeric) {
          lvalFloat = this._incomparable;
        }

        if (rvalNotNumeric) {
          rvalFloat = this._incomparable;
        }

        if (lvalNotNumeric && rvalNotNumeric) {
          var lvalIsStr = isString(lval);
          var rvalIsStr = isString(rval);

          if (lvalIsStr) {
            lvalFloat = rvalIsStr ? lval : 0;
          }

          if (rvalIsStr) {
            rvalFloat = lvalIsStr ? rval : 0;
          }
        }

        return lvalFloat < rvalFloat ? this._resultLT : lvalFloat > rvalFloat ? -this._resultLT : 0;
      };

      return SortOrderComparator;
    }();

    /**
     * TODO: disable writable.
     * This structure will be exposed to users.
     */

    var ExternalSource =
    /** @class */
    function () {
      function ExternalSource() {}

      ExternalSource.prototype.getRawData = function () {
        // Only built-in transform available.
        throw new Error('not supported');
      };

      ExternalSource.prototype.getRawDataItem = function (dataIndex) {
        // Only built-in transform available.
        throw new Error('not supported');
      };

      ExternalSource.prototype.cloneRawData = function () {
        return;
      };
      /**
       * @return If dimension not found, return null/undefined.
       */


      ExternalSource.prototype.getDimensionInfo = function (dim) {
        return;
      };
      /**
       * dimensions defined if and only if either:
       * (a) dataset.dimensions are declared.
       * (b) dataset data include dimensions definitions in data (detected or via specified `sourceHeader`).
       * If dimensions are defined, `dimensionInfoAll` is corresponding to
       * the defined dimensions.
       * Otherwise, `dimensionInfoAll` is determined by data columns.
       * @return Always return an array (even empty array).
       */


      ExternalSource.prototype.cloneAllDimensionInfo = function () {
        return;
      };

      ExternalSource.prototype.count = function () {
        return;
      };
      /**
       * Only support by dimension index.
       * No need to support by dimension name in transform function,
       * because transform function is not case-specific, no need to use name literally.
       */


      ExternalSource.prototype.retrieveValue = function (dataIndex, dimIndex) {
        return;
      };

      ExternalSource.prototype.retrieveValueFromItem = function (dataItem, dimIndex) {
        return;
      };

      ExternalSource.prototype.convertValue = function (rawVal, dimInfo) {
        return parseDataValue(rawVal, dimInfo);
      };

      return ExternalSource;
    }();

    function createExternalSource(internalSource, externalTransform) {
      var extSource = new ExternalSource();
      var data = internalSource.data;
      var sourceFormat = extSource.sourceFormat = internalSource.sourceFormat;
      var sourceHeaderCount = internalSource.startIndex;
      var errMsg = '';

      if (internalSource.seriesLayoutBy !== SERIES_LAYOUT_BY_COLUMN) {
        // For the logic simplicity in transformer, only 'culumn' is
        // supported in data transform. Otherwise, the `dimensionsDefine`
        // might be detected by 'row', which probably confuses users.
        if ("development" !== 'production') {
          errMsg = '`seriesLayoutBy` of upstream dataset can only be "column" in data transform.';
        }

        throwError(errMsg);
      } // [MEMO]
      // Create a new dimensions structure for exposing.
      // Do not expose all dimension info to users directly.
      // Because the dimension is probably auto detected from data and not might reliable.
      // Should not lead the transformers to think that is reliable and return it.
      // See [DIMENSION_INHERIT_RULE] in `sourceManager.ts`.


      var dimensions = [];
      var dimsByName = {};
      var dimsDef = internalSource.dimensionsDefine;

      if (dimsDef) {
        each(dimsDef, function (dimDef, idx) {
          var name = dimDef.name;
          var dimDefExt = {
            index: idx,
            name: name,
            displayName: dimDef.displayName
          };
          dimensions.push(dimDefExt); // Users probably do not specify dimension name. For simplicity, data transform
          // does not generate dimension name.

          if (name != null) {
            // Dimension name should not be duplicated.
            // For simplicity, data transform forbids name duplication, do not generate
            // new name like module `completeDimensions.ts` did, but just tell users.
            var errMsg_1 = '';

            if (hasOwn(dimsByName, name)) {
              if ("development" !== 'production') {
                errMsg_1 = 'dimension name "' + name + '" duplicated.';
              }

              throwError(errMsg_1);
            }

            dimsByName[name] = dimDefExt;
          }
        });
      } // If dimension definitions are not defined and can not be detected.
      // e.g., pure data `[[11, 22], ...]`.
      else {
          for (var i = 0; i < internalSource.dimensionsDetectedCount || 0; i++) {
            // Do not generete name or anything others. The consequence process in
            // `transform` or `series` probably have there own name generation strategry.
            dimensions.push({
              index: i
            });
          }
        } // Implement public methods:


      var rawItemGetter = getRawSourceItemGetter(sourceFormat, SERIES_LAYOUT_BY_COLUMN);

      if (externalTransform.__isBuiltIn) {
        extSource.getRawDataItem = function (dataIndex) {
          return rawItemGetter(data, sourceHeaderCount, dimensions, dataIndex);
        };

        extSource.getRawData = bind(getRawData, null, internalSource);
      }

      extSource.cloneRawData = bind(cloneRawData, null, internalSource);
      var rawCounter = getRawSourceDataCounter(sourceFormat, SERIES_LAYOUT_BY_COLUMN);
      extSource.count = bind(rawCounter, null, data, sourceHeaderCount, dimensions);
      var rawValueGetter = getRawSourceValueGetter(sourceFormat);

      extSource.retrieveValue = function (dataIndex, dimIndex) {
        var rawItem = rawItemGetter(data, sourceHeaderCount, dimensions, dataIndex);
        return retrieveValueFromItem(rawItem, dimIndex);
      };

      var retrieveValueFromItem = extSource.retrieveValueFromItem = function (dataItem, dimIndex) {
        if (dataItem == null) {
          return;
        }

        var dimDef = dimensions[dimIndex]; // When `dimIndex` is `null`, `rawValueGetter` return the whole item.

        if (dimDef) {
          return rawValueGetter(dataItem, dimIndex, dimDef.name);
        }
      };

      extSource.getDimensionInfo = bind(getDimensionInfo, null, dimensions, dimsByName);
      extSource.cloneAllDimensionInfo = bind(cloneAllDimensionInfo, null, dimensions);
      return extSource;
    }

    function getRawData(upstream) {
      var sourceFormat = upstream.sourceFormat;

      if (!isSupportedSourceFormat(sourceFormat)) {
        var errMsg = '';

        if ("development" !== 'production') {
          errMsg = '`getRawData` is not supported in source format ' + sourceFormat;
        }

        throwError(errMsg);
      }

      return upstream.data;
    }

    function cloneRawData(upstream) {
      var sourceFormat = upstream.sourceFormat;
      var data = upstream.data;

      if (!isSupportedSourceFormat(sourceFormat)) {
        var errMsg = '';

        if ("development" !== 'production') {
          errMsg = '`cloneRawData` is not supported in source format ' + sourceFormat;
        }

        throwError(errMsg);
      }

      if (sourceFormat === SOURCE_FORMAT_ARRAY_ROWS) {
        var result = [];

        for (var i = 0, len = data.length; i < len; i++) {
          // Not strictly clone for performance
          result.push(data[i].slice());
        }

        return result;
      } else if (sourceFormat === SOURCE_FORMAT_OBJECT_ROWS) {
        var result = [];

        for (var i = 0, len = data.length; i < len; i++) {
          // Not strictly clone for performance
          result.push(extend({}, data[i]));
        }

        return result;
      }
    }

    function getDimensionInfo(dimensions, dimsByName, dim) {
      if (dim == null) {
        return;
      } // Keep the same logic as `List::getDimension` did.


      if (isNumber(dim) // If being a number-like string but not being defined a dimension name.
      || !isNaN(dim) && !hasOwn(dimsByName, dim)) {
        return dimensions[dim];
      } else if (hasOwn(dimsByName, dim)) {
        return dimsByName[dim];
      }
    }

    function cloneAllDimensionInfo(dimensions) {
      return clone(dimensions);
    }

    var externalTransformMap = createHashMap();
    function registerExternalTransform(externalTransform) {
      externalTransform = clone(externalTransform);
      var type = externalTransform.type;
      var errMsg = '';

      if (!type) {
        if ("development" !== 'production') {
          errMsg = 'Must have a `type` when `registerTransform`.';
        }

        throwError(errMsg);
      }

      var typeParsed = type.split(':');

      if (typeParsed.length !== 2) {
        if ("development" !== 'production') {
          errMsg = 'Name must include namespace like "ns:regression".';
        }

        throwError(errMsg);
      } // Namespace 'echarts:xxx' is official namespace, where the transforms should
      // be called directly via 'xxx' rather than 'echarts:xxx'.


      var isBuiltIn = false;

      if (typeParsed[0] === 'echarts') {
        type = typeParsed[1];
        isBuiltIn = true;
      }

      externalTransform.__isBuiltIn = isBuiltIn;
      externalTransformMap.set(type, externalTransform);
    }
    function applyDataTransform(rawTransOption, sourceList, infoForPrint) {
      var pipedTransOption = normalizeToArray(rawTransOption);
      var pipeLen = pipedTransOption.length;
      var errMsg = '';

      if (!pipeLen) {
        if ("development" !== 'production') {
          errMsg = 'If `transform` declared, it should at least contain one transform.';
        }

        throwError(errMsg);
      }

      for (var i = 0, len = pipeLen; i < len; i++) {
        var transOption = pipedTransOption[i];
        sourceList = applySingleDataTransform(transOption, sourceList, infoForPrint, pipeLen === 1 ? null : i); // piped transform only support single input, except the fist one.
        // piped transform only support single output, except the last one.

        if (i !== len - 1) {
          sourceList.length = Math.max(sourceList.length, 1);
        }
      }

      return sourceList;
    }

    function applySingleDataTransform(transOption, upSourceList, infoForPrint, // If `pipeIndex` is null/undefined, no piped transform.
    pipeIndex) {
      var errMsg = '';

      if (!upSourceList.length) {
        if ("development" !== 'production') {
          errMsg = 'Must have at least one upstream dataset.';
        }

        throwError(errMsg);
      }

      if (!isObject(transOption)) {
        if ("development" !== 'production') {
          errMsg = 'transform declaration must be an object rather than ' + typeof transOption + '.';
        }

        throwError(errMsg);
      }

      var transType = transOption.type;
      var externalTransform = externalTransformMap.get(transType);

      if (!externalTransform) {
        if ("development" !== 'production') {
          errMsg = 'Can not find transform on type "' + transType + '".';
        }

        throwError(errMsg);
      } // Prepare source


      var extUpSourceList = map(upSourceList, function (upSource) {
        return createExternalSource(upSource, externalTransform);
      });
      var resultList = normalizeToArray(externalTransform.transform({
        upstream: extUpSourceList[0],
        upstreamList: extUpSourceList,
        config: clone(transOption.config)
      }));

      if ("development" !== 'production') {
        if (transOption.print) {
          var printStrArr = map(resultList, function (extSource) {
            var pipeIndexStr = pipeIndex != null ? ' === pipe index: ' + pipeIndex : '';
            return ['=== dataset index: ' + infoForPrint.datasetIndex + pipeIndexStr + ' ===', '- transform result data:', makePrintable(extSource.data), '- transform result dimensions:', makePrintable(extSource.dimensions)].join('\n');
          }).join('\n');
          log(printStrArr);
        }
      }

      return map(resultList, function (result, resultIndex) {
        var errMsg = '';

        if (!isObject(result)) {
          if ("development" !== 'production') {
            errMsg = 'A transform should not return some empty results.';
          }

          throwError(errMsg);
        }

        if (!result.data) {
          if ("development" !== 'production') {
            errMsg = 'Transform result data should be not be null or undefined';
          }

          throwError(errMsg);
        }

        var sourceFormat = detectSourceFormat(result.data);

        if (!isSupportedSourceFormat(sourceFormat)) {
          if ("development" !== 'production') {
            errMsg = 'Transform result data should be array rows or object rows.';
          }

          throwError(errMsg);
        }

        var resultMetaRawOption;
        var firstUpSource = upSourceList[0];
        /**
         * Intuitively, the end users known the content of the original `dataset.source`,
         * calucating the transform result in mind.
         * Suppose the original `dataset.source` is:
         * ```js
         * [
         *     ['product', '2012', '2013', '2014', '2015'],
         *     ['AAA', 41.1, 30.4, 65.1, 53.3],
         *     ['BBB', 86.5, 92.1, 85.7, 83.1],
         *     ['CCC', 24.1, 67.2, 79.5, 86.4]
         * ]
         * ```
         * The dimension info have to be detected from the source data.
         * Some of the transformers (like filter, sort) will follow the dimension info
         * of upstream, while others use new dimensions (like aggregate).
         * Transformer can output a field `dimensions` to define the its own output dimensions.
         * We also allow transformers to ignore the output `dimensions` field, and
         * inherit the upstream dimensions definition. It can reduce the burden of handling
         * dimensions in transformers.
         *
         * See also [DIMENSION_INHERIT_RULE] in `sourceManager.ts`.
         */

        if (firstUpSource && resultIndex === 0 // If transformer returns `dimensions`, it means that the transformer has different
        // dimensions definitions. We do not inherit anything from upstream.
        && !result.dimensions) {
          var startIndex = firstUpSource.startIndex; // We copy the header of upstream to the result, because:
          // (1) The returned data always does not contain header line and can not be used
          // as dimension-detection. In this case we can not use "detected dimensions" of
          // upstream directly, because it might be detected based on different `seriesLayoutBy`.
          // (2) We should support that the series read the upstream source in `seriesLayoutBy: 'row'`.
          // So the original detected header should be add to the result, otherwise they can not be read.

          if (startIndex) {
            result.data = firstUpSource.data.slice(0, startIndex).concat(result.data);
          }

          resultMetaRawOption = {
            seriesLayoutBy: SERIES_LAYOUT_BY_COLUMN,
            sourceHeader: startIndex,
            dimensions: firstUpSource.metaRawOption.dimensions
          };
        } else {
          resultMetaRawOption = {
            seriesLayoutBy: SERIES_LAYOUT_BY_COLUMN,
            sourceHeader: 0,
            dimensions: result.dimensions
          };
        }

        return createSource(result.data, resultMetaRawOption, null);
      });
    }

    function isSupportedSourceFormat(sourceFormat) {
      return sourceFormat === SOURCE_FORMAT_ARRAY_ROWS || sourceFormat === SOURCE_FORMAT_OBJECT_ROWS;
    }

    var UNDEFINED = 'undefined';
    /* global Float64Array, Int32Array, Uint32Array, Uint16Array */
    // Caution: MUST not use `new CtorUint32Array(arr, 0, len)`, because the Ctor of array is
    // different from the Ctor of typed array.

    var CtorUint32Array = typeof Uint32Array === UNDEFINED ? Array : Uint32Array;
    var CtorUint16Array = typeof Uint16Array === UNDEFINED ? Array : Uint16Array;
    var CtorInt32Array = typeof Int32Array === UNDEFINED ? Array : Int32Array;
    var CtorFloat64Array = typeof Float64Array === UNDEFINED ? Array : Float64Array;
    /**
     * Multi dimensional data store
     */

    var dataCtors = {
      'float': CtorFloat64Array,
      'int': CtorInt32Array,
      // Ordinal data type can be string or int
      'ordinal': Array,
      'number': Array,
      'time': CtorFloat64Array
    };
    var defaultDimValueGetters;

    function getIndicesCtor(rawCount) {
      // The possible max value in this._indicies is always this._rawCount despite of filtering.
      return rawCount > 65535 ? CtorUint32Array : CtorUint16Array;
    }

    function getInitialExtent() {
      return [Infinity, -Infinity];
    }

    function cloneChunk(originalChunk) {
      var Ctor = originalChunk.constructor; // Only shallow clone is enough when Array.

      return Ctor === Array ? originalChunk.slice() : new Ctor(originalChunk);
    }

    function prepareStore(store, dimIdx, dimType, end, append) {
      var DataCtor = dataCtors[dimType || 'float'];

      if (append) {
        var oldStore = store[dimIdx];
        var oldLen = oldStore && oldStore.length;

        if (!(oldLen === end)) {
          var newStore = new DataCtor(end); // The cost of the copy is probably inconsiderable
          // within the initial chunkSize.

          for (var j = 0; j < oldLen; j++) {
            newStore[j] = oldStore[j];
          }

          store[dimIdx] = newStore;
        }
      } else {
        store[dimIdx] = new DataCtor(end);
      }
    }
    /**
     * Basically, DataStore API keep immutable.
     */

    var DataStore =
    /** @class */
    function () {
      function DataStore() {
        this._chunks = []; // It will not be calculated until needed.

        this._rawExtent = [];
        this._extent = [];
        this._count = 0;
        this._rawCount = 0;
        this._calcDimNameToIdx = createHashMap();
      }
      /**
       * Initialize from data
       */


      DataStore.prototype.initData = function (provider, inputDimensions, dimValueGetter) {
        if ("development" !== 'production') {
          assert(isFunction(provider.getItem) && isFunction(provider.count), 'Invalid data provider.');
        }

        this._provider = provider; // Clear

        this._chunks = [];
        this._indices = null;
        this.getRawIndex = this._getRawIdxIdentity;
        var source = provider.getSource();
        var defaultGetter = this.defaultDimValueGetter = defaultDimValueGetters[source.sourceFormat]; // Default dim value getter

        this._dimValueGetter = dimValueGetter || defaultGetter; // Reset raw extent.

        this._rawExtent = [];
        var willRetrieveDataByName = shouldRetrieveDataByName(source);
        this._dimensions = map(inputDimensions, function (dim) {
          if ("development" !== 'production') {
            if (willRetrieveDataByName) {
              assert(dim.property != null);
            }
          }

          return {
            // Only pick these two props. Not leak other properties like orderMeta.
            type: dim.type,
            property: dim.property
          };
        });

        this._initDataFromProvider(0, provider.count());
      };

      DataStore.prototype.getProvider = function () {
        return this._provider;
      };
      /**
       * Caution: even when a `source` instance owned by a series, the created data store
       * may still be shared by different sereis (the source hash does not use all `source`
       * props, see `sourceManager`). In this case, the `source` props that are not used in
       * hash (like `source.dimensionDefine`) probably only belongs to a certain series and
       * thus should not be fetch here.
       */


      DataStore.prototype.getSource = function () {
        return this._provider.getSource();
      };
      /**
       * @caution Only used in dataStack.
       */


      DataStore.prototype.ensureCalculationDimension = function (dimName, type) {
        var calcDimNameToIdx = this._calcDimNameToIdx;
        var dimensions = this._dimensions;
        var calcDimIdx = calcDimNameToIdx.get(dimName);

        if (calcDimIdx != null) {
          if (dimensions[calcDimIdx].type === type) {
            return calcDimIdx;
          }
        } else {
          calcDimIdx = dimensions.length;
        }

        dimensions[calcDimIdx] = {
          type: type
        };
        calcDimNameToIdx.set(dimName, calcDimIdx);
        this._chunks[calcDimIdx] = new dataCtors[type || 'float'](this._rawCount);
        this._rawExtent[calcDimIdx] = getInitialExtent();
        return calcDimIdx;
      };

      DataStore.prototype.collectOrdinalMeta = function (dimIdx, ordinalMeta) {
        var chunk = this._chunks[dimIdx];
        var dim = this._dimensions[dimIdx];
        var rawExtents = this._rawExtent;
        var offset = dim.ordinalOffset || 0;
        var len = chunk.length;

        if (offset === 0) {
          // We need to reset the rawExtent if collect is from start.
          // Because this dimension may be guessed as number and calcuating a wrong extent.
          rawExtents[dimIdx] = getInitialExtent();
        }

        var dimRawExtent = rawExtents[dimIdx]; // Parse from previous data offset. len may be changed after appendData

        for (var i = offset; i < len; i++) {
          var val = chunk[i] = ordinalMeta.parseAndCollect(chunk[i]);

          if (!isNaN(val)) {
            dimRawExtent[0] = Math.min(val, dimRawExtent[0]);
            dimRawExtent[1] = Math.max(val, dimRawExtent[1]);
          }
        }

        dim.ordinalMeta = ordinalMeta;
        dim.ordinalOffset = len;
        dim.type = 'ordinal'; // Force to be ordinal
      };

      DataStore.prototype.getOrdinalMeta = function (dimIdx) {
        var dimInfo = this._dimensions[dimIdx];
        var ordinalMeta = dimInfo.ordinalMeta;
        return ordinalMeta;
      };

      DataStore.prototype.getDimensionProperty = function (dimIndex) {
        var item = this._dimensions[dimIndex];
        return item && item.property;
      };
      /**
       * Caution: Can be only called on raw data (before `this._indices` created).
       */


      DataStore.prototype.appendData = function (data) {
        if ("development" !== 'production') {
          assert(!this._indices, 'appendData can only be called on raw data.');
        }

        var provider = this._provider;
        var start = this.count();
        provider.appendData(data);
        var end = provider.count();

        if (!provider.persistent) {
          end += start;
        }

        if (start < end) {
          this._initDataFromProvider(start, end, true);
        }

        return [start, end];
      };

      DataStore.prototype.appendValues = function (values, minFillLen) {
        var chunks = this._chunks;
        var dimensions = this._dimensions;
        var dimLen = dimensions.length;
        var rawExtent = this._rawExtent;
        var start = this.count();
        var end = start + Math.max(values.length, minFillLen || 0);

        for (var i = 0; i < dimLen; i++) {
          var dim = dimensions[i];
          prepareStore(chunks, i, dim.type, end, true);
        }

        var emptyDataItem = [];

        for (var idx = start; id