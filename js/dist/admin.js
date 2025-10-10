/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./node_modules/@babel/runtime/helpers/OverloadYield.js":
/*!**************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/OverloadYield.js ***!
  \**************************************************************/
/***/ ((module) => {

function _OverloadYield(e, d) {
  this.v = e, this.k = d;
}
module.exports = _OverloadYield, module.exports.__esModule = true, module.exports["default"] = module.exports;

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js":
/*!*********************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js ***!
  \*********************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ _asyncToGenerator)
/* harmony export */ });
function asyncGeneratorStep(n, t, e, r, o, a, c) {
  try {
    var i = n[a](c),
      u = i.value;
  } catch (n) {
    return void e(n);
  }
  i.done ? t(u) : Promise.resolve(u).then(r, o);
}
function _asyncToGenerator(n) {
  return function () {
    var t = this,
      e = arguments;
    return new Promise(function (r, o) {
      var a = n.apply(t, e);
      function _next(n) {
        asyncGeneratorStep(a, r, o, _next, _throw, "next", n);
      }
      function _throw(n) {
        asyncGeneratorStep(a, r, o, _next, _throw, "throw", n);
      }
      _next(void 0);
    });
  };
}


/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/inheritsLoose.js":
/*!******************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/inheritsLoose.js ***!
  \******************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ _inheritsLoose)
/* harmony export */ });
/* harmony import */ var _setPrototypeOf_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./setPrototypeOf.js */ "./node_modules/@babel/runtime/helpers/esm/setPrototypeOf.js");

function _inheritsLoose(t, o) {
  t.prototype = Object.create(o.prototype), t.prototype.constructor = t, (0,_setPrototypeOf_js__WEBPACK_IMPORTED_MODULE_0__["default"])(t, o);
}


/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/setPrototypeOf.js":
/*!*******************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/setPrototypeOf.js ***!
  \*******************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ _setPrototypeOf)
/* harmony export */ });
function _setPrototypeOf(t, e) {
  return _setPrototypeOf = Object.setPrototypeOf ? Object.setPrototypeOf.bind() : function (t, e) {
    return t.__proto__ = e, t;
  }, _setPrototypeOf(t, e);
}


/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/regenerator.js":
/*!************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/regenerator.js ***!
  \************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

var regeneratorDefine = __webpack_require__(/*! ./regeneratorDefine.js */ "./node_modules/@babel/runtime/helpers/regeneratorDefine.js");
function _regenerator() {
  /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */
  var e,
    t,
    r = "function" == typeof Symbol ? Symbol : {},
    n = r.iterator || "@@iterator",
    o = r.toStringTag || "@@toStringTag";
  function i(r, n, o, i) {
    var c = n && n.prototype instanceof Generator ? n : Generator,
      u = Object.create(c.prototype);
    return regeneratorDefine(u, "_invoke", function (r, n, o) {
      var i,
        c,
        u,
        f = 0,
        p = o || [],
        y = !1,
        G = {
          p: 0,
          n: 0,
          v: e,
          a: d,
          f: d.bind(e, 4),
          d: function d(t, r) {
            return i = t, c = 0, u = e, G.n = r, a;
          }
        };
      function d(r, n) {
        for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) {
          var o,
            i = p[t],
            d = G.p,
            l = i[2];
          r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0));
        }
        if (o || r > 1) return a;
        throw y = !0, n;
      }
      return function (o, p, l) {
        if (f > 1) throw TypeError("Generator is already running");
        for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) {
          i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u);
          try {
            if (f = 2, i) {
              if (c || (o = "next"), t = i[o]) {
                if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object");
                if (!t.done) return t;
                u = t.value, c < 2 && (c = 0);
              } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1);
              i = e;
            } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break;
          } catch (t) {
            i = e, c = 1, u = t;
          } finally {
            f = 1;
          }
        }
        return {
          value: t,
          done: y
        };
      };
    }(r, o, i), !0), u;
  }
  var a = {};
  function Generator() {}
  function GeneratorFunction() {}
  function GeneratorFunctionPrototype() {}
  t = Object.getPrototypeOf;
  var c = [][n] ? t(t([][n]())) : (regeneratorDefine(t = {}, n, function () {
      return this;
    }), t),
    u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c);
  function f(e) {
    return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, regeneratorDefine(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e;
  }
  return GeneratorFunction.prototype = GeneratorFunctionPrototype, regeneratorDefine(u, "constructor", GeneratorFunctionPrototype), regeneratorDefine(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", regeneratorDefine(GeneratorFunctionPrototype, o, "GeneratorFunction"), regeneratorDefine(u), regeneratorDefine(u, o, "Generator"), regeneratorDefine(u, n, function () {
    return this;
  }), regeneratorDefine(u, "toString", function () {
    return "[object Generator]";
  }), (module.exports = _regenerator = function _regenerator() {
    return {
      w: i,
      m: f
    };
  }, module.exports.__esModule = true, module.exports["default"] = module.exports)();
}
module.exports = _regenerator, module.exports.__esModule = true, module.exports["default"] = module.exports;

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/regeneratorAsync.js":
/*!*****************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/regeneratorAsync.js ***!
  \*****************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

var regeneratorAsyncGen = __webpack_require__(/*! ./regeneratorAsyncGen.js */ "./node_modules/@babel/runtime/helpers/regeneratorAsyncGen.js");
function _regeneratorAsync(n, e, r, t, o) {
  var a = regeneratorAsyncGen(n, e, r, t, o);
  return a.next().then(function (n) {
    return n.done ? n.value : a.next();
  });
}
module.exports = _regeneratorAsync, module.exports.__esModule = true, module.exports["default"] = module.exports;

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/regeneratorAsyncGen.js":
/*!********************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/regeneratorAsyncGen.js ***!
  \********************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

var regenerator = __webpack_require__(/*! ./regenerator.js */ "./node_modules/@babel/runtime/helpers/regenerator.js");
var regeneratorAsyncIterator = __webpack_require__(/*! ./regeneratorAsyncIterator.js */ "./node_modules/@babel/runtime/helpers/regeneratorAsyncIterator.js");
function _regeneratorAsyncGen(r, e, t, o, n) {
  return new regeneratorAsyncIterator(regenerator().w(r, e, t, o), n || Promise);
}
module.exports = _regeneratorAsyncGen, module.exports.__esModule = true, module.exports["default"] = module.exports;

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/regeneratorAsyncIterator.js":
/*!*************************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/regeneratorAsyncIterator.js ***!
  \*************************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

var OverloadYield = __webpack_require__(/*! ./OverloadYield.js */ "./node_modules/@babel/runtime/helpers/OverloadYield.js");
var regeneratorDefine = __webpack_require__(/*! ./regeneratorDefine.js */ "./node_modules/@babel/runtime/helpers/regeneratorDefine.js");
function AsyncIterator(t, e) {
  function n(r, o, i, f) {
    try {
      var c = t[r](o),
        u = c.value;
      return u instanceof OverloadYield ? e.resolve(u.v).then(function (t) {
        n("next", t, i, f);
      }, function (t) {
        n("throw", t, i, f);
      }) : e.resolve(u).then(function (t) {
        c.value = t, i(c);
      }, function (t) {
        return n("throw", t, i, f);
      });
    } catch (t) {
      f(t);
    }
  }
  var r;
  this.next || (regeneratorDefine(AsyncIterator.prototype), regeneratorDefine(AsyncIterator.prototype, "function" == typeof Symbol && Symbol.asyncIterator || "@asyncIterator", function () {
    return this;
  })), regeneratorDefine(this, "_invoke", function (t, o, i) {
    function f() {
      return new e(function (e, r) {
        n(t, i, e, r);
      });
    }
    return r = r ? r.then(f, f) : f();
  }, !0);
}
module.exports = AsyncIterator, module.exports.__esModule = true, module.exports["default"] = module.exports;

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/regeneratorDefine.js":
/*!******************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/regeneratorDefine.js ***!
  \******************************************************************/
/***/ ((module) => {

function _regeneratorDefine(e, r, n, t) {
  var i = Object.defineProperty;
  try {
    i({}, "", {});
  } catch (e) {
    i = 0;
  }
  module.exports = _regeneratorDefine = function regeneratorDefine(e, r, n, t) {
    function o(r, n) {
      _regeneratorDefine(e, r, function (e) {
        return this._invoke(r, n, e);
      });
    }
    r ? i ? i(e, r, {
      value: n,
      enumerable: !t,
      configurable: !t,
      writable: !t
    }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2));
  }, module.exports.__esModule = true, module.exports["default"] = module.exports, _regeneratorDefine(e, r, n, t);
}
module.exports = _regeneratorDefine, module.exports.__esModule = true, module.exports["default"] = module.exports;

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/regeneratorKeys.js":
/*!****************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/regeneratorKeys.js ***!
  \****************************************************************/
/***/ ((module) => {

function _regeneratorKeys(e) {
  var n = Object(e),
    r = [];
  for (var t in n) r.unshift(t);
  return function e() {
    for (; r.length;) if ((t = r.pop()) in n) return e.value = t, e.done = !1, e;
    return e.done = !0, e;
  };
}
module.exports = _regeneratorKeys, module.exports.__esModule = true, module.exports["default"] = module.exports;

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/regeneratorRuntime.js":
/*!*******************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/regeneratorRuntime.js ***!
  \*******************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

var OverloadYield = __webpack_require__(/*! ./OverloadYield.js */ "./node_modules/@babel/runtime/helpers/OverloadYield.js");
var regenerator = __webpack_require__(/*! ./regenerator.js */ "./node_modules/@babel/runtime/helpers/regenerator.js");
var regeneratorAsync = __webpack_require__(/*! ./regeneratorAsync.js */ "./node_modules/@babel/runtime/helpers/regeneratorAsync.js");
var regeneratorAsyncGen = __webpack_require__(/*! ./regeneratorAsyncGen.js */ "./node_modules/@babel/runtime/helpers/regeneratorAsyncGen.js");
var regeneratorAsyncIterator = __webpack_require__(/*! ./regeneratorAsyncIterator.js */ "./node_modules/@babel/runtime/helpers/regeneratorAsyncIterator.js");
var regeneratorKeys = __webpack_require__(/*! ./regeneratorKeys.js */ "./node_modules/@babel/runtime/helpers/regeneratorKeys.js");
var regeneratorValues = __webpack_require__(/*! ./regeneratorValues.js */ "./node_modules/@babel/runtime/helpers/regeneratorValues.js");
function _regeneratorRuntime() {
  "use strict";

  var r = regenerator(),
    e = r.m(_regeneratorRuntime),
    t = (Object.getPrototypeOf ? Object.getPrototypeOf(e) : e.__proto__).constructor;
  function n(r) {
    var e = "function" == typeof r && r.constructor;
    return !!e && (e === t || "GeneratorFunction" === (e.displayName || e.name));
  }
  var o = {
    "throw": 1,
    "return": 2,
    "break": 3,
    "continue": 3
  };
  function a(r) {
    var e, t;
    return function (n) {
      e || (e = {
        stop: function stop() {
          return t(n.a, 2);
        },
        "catch": function _catch() {
          return n.v;
        },
        abrupt: function abrupt(r, e) {
          return t(n.a, o[r], e);
        },
        delegateYield: function delegateYield(r, o, a) {
          return e.resultName = o, t(n.d, regeneratorValues(r), a);
        },
        finish: function finish(r) {
          return t(n.f, r);
        }
      }, t = function t(r, _t, o) {
        n.p = e.prev, n.n = e.next;
        try {
          return r(_t, o);
        } finally {
          e.next = n.n;
        }
      }), e.resultName && (e[e.resultName] = n.v, e.resultName = void 0), e.sent = n.v, e.next = n.n;
      try {
        return r.call(this, e);
      } finally {
        n.p = e.prev, n.n = e.next;
      }
    };
  }
  return (module.exports = _regeneratorRuntime = function _regeneratorRuntime() {
    return {
      wrap: function wrap(e, t, n, o) {
        return r.w(a(e), t, n, o && o.reverse());
      },
      isGeneratorFunction: n,
      mark: r.m,
      awrap: function awrap(r, e) {
        return new OverloadYield(r, e);
      },
      AsyncIterator: regeneratorAsyncIterator,
      async: function async(r, e, t, o, u) {
        return (n(e) ? regeneratorAsyncGen : regeneratorAsync)(a(r), e, t, o, u);
      },
      keys: regeneratorKeys,
      values: regeneratorValues
    };
  }, module.exports.__esModule = true, module.exports["default"] = module.exports)();
}
module.exports = _regeneratorRuntime, module.exports.__esModule = true, module.exports["default"] = module.exports;

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/regeneratorValues.js":
/*!******************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/regeneratorValues.js ***!
  \******************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

var _typeof = (__webpack_require__(/*! ./typeof.js */ "./node_modules/@babel/runtime/helpers/typeof.js")["default"]);
function _regeneratorValues(e) {
  if (null != e) {
    var t = e["function" == typeof Symbol && Symbol.iterator || "@@iterator"],
      r = 0;
    if (t) return t.call(e);
    if ("function" == typeof e.next) return e;
    if (!isNaN(e.length)) return {
      next: function next() {
        return e && r >= e.length && (e = void 0), {
          value: e && e[r++],
          done: !e
        };
      }
    };
  }
  throw new TypeError(_typeof(e) + " is not iterable");
}
module.exports = _regeneratorValues, module.exports.__esModule = true, module.exports["default"] = module.exports;

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/typeof.js":
/*!*******************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/typeof.js ***!
  \*******************************************************/
/***/ ((module) => {

function _typeof(o) {
  "@babel/helpers - typeof";

  return module.exports = _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) {
    return typeof o;
  } : function (o) {
    return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o;
  }, module.exports.__esModule = true, module.exports["default"] = module.exports, _typeof(o);
}
module.exports = _typeof, module.exports.__esModule = true, module.exports["default"] = module.exports;

/***/ }),

/***/ "./node_modules/@babel/runtime/regenerator/index.js":
/*!**********************************************************!*\
  !*** ./node_modules/@babel/runtime/regenerator/index.js ***!
  \**********************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

// TODO(Babel 8): Remove this file.

var runtime = __webpack_require__(/*! ../helpers/regeneratorRuntime */ "./node_modules/@babel/runtime/helpers/regeneratorRuntime.js")();
module.exports = runtime;

// Copied from https://github.com/facebook/regenerator/blob/main/packages/runtime/runtime.js#L736=
try {
  regeneratorRuntime = runtime;
} catch (accidentalStrictMode) {
  if (typeof globalThis === "object") {
    globalThis.regeneratorRuntime = runtime;
  } else {
    Function("r", "regeneratorRuntime = r")(runtime);
  }
}

/***/ }),

/***/ "./src/admin/components/AISettingsPage.tsx":
/*!*************************************************!*\
  !*** ./src/admin/components/AISettingsPage.tsx ***!
  \*************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ AISettingsPage)
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_esm_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/esm/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_helpers_esm_inheritsLoose__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/esm/inheritsLoose */ "./node_modules/@babel/runtime/helpers/esm/inheritsLoose.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var flarum_admin_app__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! flarum/admin/app */ "flarum/admin/app");
/* harmony import */ var flarum_admin_app__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(flarum_admin_app__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var flarum_admin_components_ExtensionPage__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! flarum/admin/components/ExtensionPage */ "flarum/admin/components/ExtensionPage");
/* harmony import */ var flarum_admin_components_ExtensionPage__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(flarum_admin_components_ExtensionPage__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var flarum_common_components_Button__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! flarum/common/components/Button */ "flarum/common/components/Button");
/* harmony import */ var flarum_common_components_Button__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(flarum_common_components_Button__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var flarum_common_components_Select__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! flarum/common/components/Select */ "flarum/common/components/Select");
/* harmony import */ var flarum_common_components_Select__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(flarum_common_components_Select__WEBPACK_IMPORTED_MODULE_6__);
/* harmony import */ var flarum_common_utils_Stream__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! flarum/common/utils/Stream */ "flarum/common/utils/Stream");
/* harmony import */ var flarum_common_utils_Stream__WEBPACK_IMPORTED_MODULE_7___default = /*#__PURE__*/__webpack_require__.n(flarum_common_utils_Stream__WEBPACK_IMPORTED_MODULE_7__);








var AISettingsPage = /*#__PURE__*/function (_ExtensionPage) {
  function AISettingsPage() {
    var _this;
    for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
      args[_key] = arguments[_key];
    }
    _this = _ExtensionPage.call.apply(_ExtensionPage, [this].concat(args)) || this;
    _this.catalog = {};
    _this.currentProvider = flarum_common_utils_Stream__WEBPACK_IMPORTED_MODULE_7___default()('openai');
    _this.textModel = flarum_common_utils_Stream__WEBPACK_IMPORTED_MODULE_7___default()('gpt-4o-mini');
    _this.embeddingsModel = flarum_common_utils_Stream__WEBPACK_IMPORTED_MODULE_7___default()('text-embedding-3-small');
    _this.moderationModel = flarum_common_utils_Stream__WEBPACK_IMPORTED_MODULE_7___default()('omni-moderation-latest');
    _this.customTextModel = flarum_common_utils_Stream__WEBPACK_IMPORTED_MODULE_7___default()('');
    _this.customEmbeddingsModel = flarum_common_utils_Stream__WEBPACK_IMPORTED_MODULE_7___default()('');
    _this.customModerationModel = flarum_common_utils_Stream__WEBPACK_IMPORTED_MODULE_7___default()('');
    _this.isTestingText = flarum_common_utils_Stream__WEBPACK_IMPORTED_MODULE_7___default()(false);
    _this.isTestingEmbeddings = flarum_common_utils_Stream__WEBPACK_IMPORTED_MODULE_7___default()(false);
    _this.isTestingModeration = flarum_common_utils_Stream__WEBPACK_IMPORTED_MODULE_7___default()(false);
    return _this;
  }
  (0,_babel_runtime_helpers_esm_inheritsLoose__WEBPACK_IMPORTED_MODULE_1__["default"])(AISettingsPage, _ExtensionPage);
  var _proto = AISettingsPage.prototype;
  _proto.oninit = function oninit(vnode) {
    _ExtensionPage.prototype.oninit.call(this, vnode);
    this.currentProvider(this.setting('datlechin-ai.provider', 'openai')());
    var textSelected = this.setting('datlechin-ai.models.selected.text', 'gpt-4o-mini')();
    var textCustom = this.setting('datlechin-ai.models.custom.text', '')();
    this.textModel(textSelected);
    this.customTextModel(textCustom);
    var embeddingsSelected = this.setting('datlechin-ai.models.selected.embeddings', 'text-embedding-3-small')();
    var embeddingsCustom = this.setting('datlechin-ai.models.custom.embeddings', '')();
    this.embeddingsModel(embeddingsSelected);
    this.customEmbeddingsModel(embeddingsCustom);
    var moderationSelected = this.setting('datlechin-ai.models.selected.moderation', 'omni-moderation-latest')();
    var moderationCustom = this.setting('datlechin-ai.models.custom.moderation', '')();
    this.moderationModel(moderationSelected);
    this.customModerationModel(moderationCustom);
    this.loadCatalog();
  };
  _proto.loadCatalog = /*#__PURE__*/function () {
    var _loadCatalog = (0,_babel_runtime_helpers_esm_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2___default().mark(function _callee() {
      var response, _t;
      return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2___default().wrap(function (_context) {
        while (1) switch (_context.prev = _context.next) {
          case 0:
            _context.prev = 0;
            _context.next = 1;
            return flarum_admin_app__WEBPACK_IMPORTED_MODULE_3___default().request({
              method: 'GET',
              url: flarum_admin_app__WEBPACK_IMPORTED_MODULE_3___default().forum.attribute('apiUrl') + '/ai/provider-catalog'
            });
          case 1:
            response = _context.sent;
            if (response && response.success) {
              this.catalog = response.data;
              if (this.customTextModel()) {
                this.textModel('__custom__');
              }
              if (this.customEmbeddingsModel()) {
                this.embeddingsModel('__custom__');
              }
              if (this.customModerationModel()) {
                this.moderationModel('__custom__');
              }
              m.redraw();
            }
            _context.next = 3;
            break;
          case 2:
            _context.prev = 2;
            _t = _context["catch"](0);
            console.error('Failed to load provider catalog:', _t);
          case 3:
          case "end":
            return _context.stop();
        }
      }, _callee, this, [[0, 2]]);
    }));
    function loadCatalog() {
      return _loadCatalog.apply(this, arguments);
    }
    return loadCatalog;
  }();
  _proto.content = function content() {
    var _this2 = this;
    return m("div", {
      className: "AISettingsPage"
    }, m("div", {
      className: "container"
    }, m("div", {
      className: "Form"
    }, m("div", {
      className: "Form-group"
    }, m("label", null, flarum_admin_app__WEBPACK_IMPORTED_MODULE_3___default().translator.trans('datlechin-ai.admin.settings.provider_label')), m((flarum_common_components_Select__WEBPACK_IMPORTED_MODULE_6___default()), {
      value: this.currentProvider(),
      options: {
        openai: flarum_admin_app__WEBPACK_IMPORTED_MODULE_3___default().translator.trans('datlechin-ai.admin.settings.provider_openai'),
        gemini: flarum_admin_app__WEBPACK_IMPORTED_MODULE_3___default().translator.trans('datlechin-ai.admin.settings.provider_gemini')
      },
      onchange: function onchange(value) {
        _this2.currentProvider(value);
        _this2.setting('datlechin-ai.provider')(value);
        _this2.updateModelsForProvider(value);
      }
    })), m("div", {
      className: "Form-group"
    }, m("label", null, flarum_admin_app__WEBPACK_IMPORTED_MODULE_3___default().translator.trans('datlechin-ai.admin.settings.api_key_label', {
      provider: this.currentProvider()
    })), this.buildSettingComponent({
      type: 'text',
      setting: "datlechin-ai." + this.currentProvider() + ".api_key",
      placeholder: flarum_admin_app__WEBPACK_IMPORTED_MODULE_3___default().translator.trans('datlechin-ai.admin.settings.api_key_placeholder', {
        provider: this.currentProvider()
      })
    })), m("div", {
      className: "Form-group"
    }, m("label", null, flarum_admin_app__WEBPACK_IMPORTED_MODULE_3___default().translator.trans('datlechin-ai.admin.settings.base_url_label')), this.buildSettingComponent({
      type: 'text',
      setting: "datlechin-ai." + this.currentProvider() + ".base_url",
      placeholder: flarum_admin_app__WEBPACK_IMPORTED_MODULE_3___default().translator.trans('datlechin-ai.admin.settings.base_url_placeholder')
    })), this.renderModelSelect('text', this.textModel, this.customTextModel), this.renderModelSelect('embeddings', this.embeddingsModel, this.customEmbeddingsModel), this.currentProvider() !== 'gemini' && this.renderModelSelect('moderation', this.moderationModel, this.customModerationModel), m("div", {
      className: "Form-group"
    }, m((flarum_common_components_Button__WEBPACK_IMPORTED_MODULE_5___default()), {
      className: "Button",
      onclick: function onclick() {
        return _this2.testTextModel();
      },
      loading: this.isTestingText(),
      disabled: this.isTestingText()
    }, flarum_admin_app__WEBPACK_IMPORTED_MODULE_3___default().translator.trans('datlechin-ai.admin.settings.test_text_button')), ' ', m((flarum_common_components_Button__WEBPACK_IMPORTED_MODULE_5___default()), {
      className: "Button",
      onclick: function onclick() {
        return _this2.testEmbeddings();
      },
      loading: this.isTestingEmbeddings(),
      disabled: this.isTestingEmbeddings()
    }, flarum_admin_app__WEBPACK_IMPORTED_MODULE_3___default().translator.trans('datlechin-ai.admin.settings.test_embeddings_button')), ' ', this.currentProvider() !== 'gemini' && m('[', null, m((flarum_common_components_Button__WEBPACK_IMPORTED_MODULE_5___default()), {
      className: "Button",
      onclick: function onclick() {
        return _this2.testModeration();
      },
      loading: this.isTestingModeration(),
      disabled: this.isTestingModeration()
    }, flarum_admin_app__WEBPACK_IMPORTED_MODULE_3___default().translator.trans('datlechin-ai.admin.settings.test_moderation_button')), ' ')), this.submitButton())));
  };
  _proto.renderModelSelect = function renderModelSelect(capability, stream, customStream) {
    var _this3 = this;
    var provider = this.currentProvider();
    var providerCatalog = this.catalog[provider];
    if (!(providerCatalog != null && providerCatalog[capability])) {
      return null;
    }
    var models = providerCatalog[capability] || [];
    var options = {};
    models.forEach(function (model) {
      options[model.value] = model.label;
    });
    options['__custom__'] = 'Custom Model';
    var isCustom = stream() === '__custom__';
    return m("div", {
      className: "Form-group"
    }, m("label", null, flarum_admin_app__WEBPACK_IMPORTED_MODULE_3___default().translator.trans("datlechin-ai.admin.settings." + capability + "_model_label")), m("span", {
      "class": "Select"
    }, m("select", {
      className: "Select-input FormControl",
      value: stream(),
      onchange: function onchange(e) {
        var value = e.target.value;
        stream(value);
        _this3.setting("datlechin-ai.models.selected." + capability)(value);
        if (value !== '__custom__') {
          customStream('');
          _this3.setting("datlechin-ai.models.custom." + capability)('');
        }
        m.redraw();
      }
    }, Object.keys(options).map(function (key) {
      return m("option", {
        value: key,
        selected: key === stream()
      }, options[key]);
    })), m("i", {
      "aria-hidden": "true",
      "class": "icon fas fa-sort Select-caret"
    })), isCustom && m("div", {
      className: "Form-group",
      style: {
        marginTop: '10px'
      }
    }, m("input", {
      type: "text",
      className: "FormControl",
      placeholder: flarum_admin_app__WEBPACK_IMPORTED_MODULE_3___default().translator.trans('datlechin-ai.admin.settings.custom_model_placeholder'),
      value: customStream(),
      oninput: function oninput(e) {
        customStream(e.target.value);
        _this3.setting("datlechin-ai.models.custom." + capability)(e.target.value);
      }
    })));
  };
  _proto.updateModelsForProvider = function updateModelsForProvider(provider) {
    var _catalog$text, _catalog$text2, _catalog$embeddings, _catalog$embeddings2, _catalog$moderation, _catalog$moderation2;
    var catalog = this.catalog[provider];
    if (!catalog) return;
    var textDefault = ((_catalog$text = catalog.text) == null || (_catalog$text = _catalog$text.find(function (m) {
      return m.is_default;
    })) == null ? void 0 : _catalog$text.value) || ((_catalog$text2 = catalog.text) == null || (_catalog$text2 = _catalog$text2[0]) == null ? void 0 : _catalog$text2.value);
    var embeddingsDefault = ((_catalog$embeddings = catalog.embeddings) == null || (_catalog$embeddings = _catalog$embeddings.find(function (m) {
      return m.is_default;
    })) == null ? void 0 : _catalog$embeddings.value) || ((_catalog$embeddings2 = catalog.embeddings) == null || (_catalog$embeddings2 = _catalog$embeddings2[0]) == null ? void 0 : _catalog$embeddings2.value);
    var moderationDefault = ((_catalog$moderation = catalog.moderation) == null || (_catalog$moderation = _catalog$moderation.find(function (m) {
      return m.is_default;
    })) == null ? void 0 : _catalog$moderation.value) || ((_catalog$moderation2 = catalog.moderation) == null || (_catalog$moderation2 = _catalog$moderation2[0]) == null ? void 0 : _catalog$moderation2.value);
    if (textDefault && !this.customTextModel()) {
      this.textModel(textDefault);
      this.setting('datlechin-ai.models.selected.text')(textDefault);
    }
    if (embeddingsDefault && !this.customEmbeddingsModel()) {
      this.embeddingsModel(embeddingsDefault);
      this.setting('datlechin-ai.models.selected.embeddings')(embeddingsDefault);
    }
    if (moderationDefault && !this.customModerationModel()) {
      this.moderationModel(moderationDefault);
      this.setting('datlechin-ai.models.selected.moderation')(moderationDefault);
    }
    m.redraw();
  };
  _proto.testTextModel = /*#__PURE__*/function () {
    var _testTextModel = (0,_babel_runtime_helpers_esm_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2___default().mark(function _callee2() {
      var response, _t2;
      return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2___default().wrap(function (_context2) {
        while (1) switch (_context2.prev = _context2.next) {
          case 0:
            if (!this.isTestingText()) {
              _context2.next = 1;
              break;
            }
            return _context2.abrupt("return");
          case 1:
            this.isTestingText(true);
            m.redraw();
            _context2.prev = 2;
            _context2.next = 3;
            return flarum_admin_app__WEBPACK_IMPORTED_MODULE_3___default().request({
              method: 'POST',
              url: flarum_admin_app__WEBPACK_IMPORTED_MODULE_3___default().forum.attribute('apiUrl') + '/ai/generate',
              body: {
                messages: [{
                  role: 'user',
                  content: 'Hello! Please respond with a brief greeting.'
                }]
              }
            });
          case 3:
            response = _context2.sent;
            if (response && response.success) {
              flarum_admin_app__WEBPACK_IMPORTED_MODULE_3___default().alerts.show({
                type: 'success'
              }, flarum_admin_app__WEBPACK_IMPORTED_MODULE_3___default().translator.trans('datlechin-ai.admin.settings.test_success'));
            }
            _context2.next = 5;
            break;
          case 4:
            _context2.prev = 4;
            _t2 = _context2["catch"](2);
            flarum_admin_app__WEBPACK_IMPORTED_MODULE_3___default().alerts.show({
              type: 'error'
            }, flarum_admin_app__WEBPACK_IMPORTED_MODULE_3___default().translator.trans('datlechin-ai.admin.settings.test_failed', {
              error: _t2.message
            }));
          case 5:
            _context2.prev = 5;
            this.isTestingText(false);
            m.redraw();
            return _context2.finish(5);
          case 6:
          case "end":
            return _context2.stop();
        }
      }, _callee2, this, [[2, 4, 5, 6]]);
    }));
    function testTextModel() {
      return _testTextModel.apply(this, arguments);
    }
    return testTextModel;
  }();
  _proto.testEmbeddings = /*#__PURE__*/function () {
    var _testEmbeddings = (0,_babel_runtime_helpers_esm_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2___default().mark(function _callee3() {
      var response, _t3;
      return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2___default().wrap(function (_context3) {
        while (1) switch (_context3.prev = _context3.next) {
          case 0:
            if (!this.isTestingEmbeddings()) {
              _context3.next = 1;
              break;
            }
            return _context3.abrupt("return");
          case 1:
            this.isTestingEmbeddings(true);
            m.redraw();
            _context3.prev = 2;
            _context3.next = 3;
            return flarum_admin_app__WEBPACK_IMPORTED_MODULE_3___default().request({
              method: 'POST',
              url: flarum_admin_app__WEBPACK_IMPORTED_MODULE_3___default().forum.attribute('apiUrl') + '/ai/embeddings',
              body: {
                texts: ['This is a test embedding.']
              }
            });
          case 3:
            response = _context3.sent;
            if (response && response.success) {
              flarum_admin_app__WEBPACK_IMPORTED_MODULE_3___default().alerts.show({
                type: 'success'
              }, flarum_admin_app__WEBPACK_IMPORTED_MODULE_3___default().translator.trans('datlechin-ai.admin.settings.test_success'));
            }
            _context3.next = 5;
            break;
          case 4:
            _context3.prev = 4;
            _t3 = _context3["catch"](2);
            flarum_admin_app__WEBPACK_IMPORTED_MODULE_3___default().alerts.show({
              type: 'error'
            }, flarum_admin_app__WEBPACK_IMPORTED_MODULE_3___default().translator.trans('datlechin-ai.admin.settings.test_failed', {
              error: _t3.message
            }));
          case 5:
            _context3.prev = 5;
            this.isTestingEmbeddings(false);
            m.redraw();
            return _context3.finish(5);
          case 6:
          case "end":
            return _context3.stop();
        }
      }, _callee3, this, [[2, 4, 5, 6]]);
    }));
    function testEmbeddings() {
      return _testEmbeddings.apply(this, arguments);
    }
    return testEmbeddings;
  }();
  _proto.testModeration = /*#__PURE__*/function () {
    var _testModeration = (0,_babel_runtime_helpers_esm_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2___default().mark(function _callee4() {
      var response, _t4;
      return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2___default().wrap(function (_context4) {
        while (1) switch (_context4.prev = _context4.next) {
          case 0:
            if (!this.isTestingModeration()) {
              _context4.next = 1;
              break;
            }
            return _context4.abrupt("return");
          case 1:
            this.isTestingModeration(true);
            m.redraw();
            _context4.prev = 2;
            _context4.next = 3;
            return flarum_admin_app__WEBPACK_IMPORTED_MODULE_3___default().request({
              method: 'POST',
              url: flarum_admin_app__WEBPACK_IMPORTED_MODULE_3___default().forum.attribute('apiUrl') + '/ai/moderate',
              body: {
                text: 'This is a test message for moderation.'
              }
            });
          case 3:
            response = _context4.sent;
            if (response && response.success) {
              flarum_admin_app__WEBPACK_IMPORTED_MODULE_3___default().alerts.show({
                type: 'success'
              }, flarum_admin_app__WEBPACK_IMPORTED_MODULE_3___default().translator.trans('datlechin-ai.admin.settings.test_success'));
            }
            _context4.next = 5;
            break;
          case 4:
            _context4.prev = 4;
            _t4 = _context4["catch"](2);
            flarum_admin_app__WEBPACK_IMPORTED_MODULE_3___default().alerts.show({
              type: 'error'
            }, flarum_admin_app__WEBPACK_IMPORTED_MODULE_3___default().translator.trans('datlechin-ai.admin.settings.test_failed', {
              error: _t4.message
            }));
          case 5:
            _context4.prev = 5;
            this.isTestingModeration(false);
            m.redraw();
            return _context4.finish(5);
          case 6:
          case "end":
            return _context4.stop();
        }
      }, _callee4, this, [[2, 4, 5, 6]]);
    }));
    function testModeration() {
      return _testModeration.apply(this, arguments);
    }
    return testModeration;
  }();
  return AISettingsPage;
}((flarum_admin_components_ExtensionPage__WEBPACK_IMPORTED_MODULE_4___default()));


/***/ }),

/***/ "./src/admin/index.ts":
/*!****************************!*\
  !*** ./src/admin/index.ts ***!
  \****************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var flarum_admin_app__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! flarum/admin/app */ "flarum/admin/app");
/* harmony import */ var flarum_admin_app__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(flarum_admin_app__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _components_AISettingsPage__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./components/AISettingsPage */ "./src/admin/components/AISettingsPage.tsx");


flarum_admin_app__WEBPACK_IMPORTED_MODULE_0___default().initializers.add('nqd/ai-core', function () {
  flarum_admin_app__WEBPACK_IMPORTED_MODULE_0___default().extensionData["for"]('datlechin-ai').registerPage(_components_AISettingsPage__WEBPACK_IMPORTED_MODULE_1__["default"]);
});

/***/ }),

/***/ "flarum/admin/app":
/*!**************************************************!*\
  !*** external "flarum.core.compat['admin/app']" ***!
  \**************************************************/
/***/ ((module) => {

"use strict";
module.exports = flarum.core.compat['admin/app'];

/***/ }),

/***/ "flarum/admin/components/ExtensionPage":
/*!***********************************************************************!*\
  !*** external "flarum.core.compat['admin/components/ExtensionPage']" ***!
  \***********************************************************************/
/***/ ((module) => {

"use strict";
module.exports = flarum.core.compat['admin/components/ExtensionPage'];

/***/ }),

/***/ "flarum/common/components/Button":
/*!*****************************************************************!*\
  !*** external "flarum.core.compat['common/components/Button']" ***!
  \*****************************************************************/
/***/ ((module) => {

"use strict";
module.exports = flarum.core.compat['common/components/Button'];

/***/ }),

/***/ "flarum/common/components/Select":
/*!*****************************************************************!*\
  !*** external "flarum.core.compat['common/components/Select']" ***!
  \*****************************************************************/
/***/ ((module) => {

"use strict";
module.exports = flarum.core.compat['common/components/Select'];

/***/ }),

/***/ "flarum/common/utils/Stream":
/*!************************************************************!*\
  !*** external "flarum.core.compat['common/utils/Stream']" ***!
  \************************************************************/
/***/ ((module) => {

"use strict";
module.exports = flarum.core.compat['common/utils/Stream'];

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be in strict mode.
(() => {
"use strict";
/*!******************!*\
  !*** ./admin.ts ***!
  \******************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _src_admin__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./src/admin */ "./src/admin/index.ts");

})();

module.exports = __webpack_exports__;
/******/ })()
;
//# sourceMappingURL=admin.js.map