var e, t, s, r, o, a, i, n, l, c, u, h, d, p, f, g, m, v, y, w, x, b, _, C, T, k, j, E, D, S, N, q;

function A(e, i) {
    var n = !1,
        t = e.charAt(0).toUpperCase() + e.slice(1);
    return s.each((e + " " + a.join(t + " ") + t).split(" "), function(e, t) {
        if (o[t] !== r) return n = !i || t, !1
    }), n
}

function P(e) {
    return A(e, !0)
}

function H(e, t) {
    this.settings = null, this.options = E.extend({}, H.Defaults, t), this.$element = E(e), this._handlers = {}, this._plugins = {}, this._supress = {}, this._current = null, this._speed = null, this._coordinates = [], this._breakpoint = null, this._width = null, this._items = [], this._clones = [], this._mergers = [], this._widths = [], this._invalidated = {}, this._pipe = [], this._drag = {
        time: null,
        target: null,
        pointer: null,
        stage: {
            start: null,
            current: null
        },
        direction: null
    }, this._states = {
        current: {},
        tags: {
            initializing: ["busy"],
            animating: ["busy"],
            dragging: ["interacting"]
        }
    }, E.each(["onResize", "onThrottledResize"], E.proxy(function(e, t) {
        this._handlers[t] = E.proxy(this[t], this)
    }, this)), E.each(H.Plugins, E.proxy(function(e, t) {
        this._plugins[e.charAt(0).toLowerCase() + e.slice(1)] = new t(this)
    }, this)), E.each(H.Workers, E.proxy(function(e, t) {
        this._pipe.push({
            filter: t.filter,
            run: E.proxy(t.run, this)
        })
    }, this)), this.setup(), this.initialize()
}

function L() {
    // $(".dropdown > a").click(function(e) {
    //     e.preventDefault(), $(this).toggleClass("active"), $(this).closest(".dropdown").find(".dropdown-menu").stop().slideToggle()
    // })
}
e = "undefined" != typeof window ? window : this, t = function(f, e) {
        function t(e, t) {
            return t.toUpperCase()
        }
        var i = [],
            u = i.slice,
            g = i.concat,
            a = i.push,
            s = i.indexOf,
            n = {},
            r = n.toString,
            m = n.hasOwnProperty,
            v = {},
            y = f.document,
            o = "2.1.4",
            C = function(e, t) {
                return new C.fn.init(e, t)
            },
            l = /^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g,
            c = /^-ms-/,
            h = /-([\da-z])/gi;

        function d(e) {
            var t = "length" in e && e.length,
                i = C.type(e);
            return "function" !== i && !C.isWindow(e) && (!(1 !== e.nodeType || !t) || ("array" === i || 0 === t || "number" == typeof t && 0 < t && t - 1 in e))
        }
        C.fn = C.prototype = {
            jquery: o,
            constructor: C,
            selector: "",
            length: 0,
            toArray: function() {
                return u.call(this)
            },
            get: function(e) {
                return null != e ? e < 0 ? this[e + this.length] : this[e] : u.call(this)
            },
            pushStack: function(e) {
                var t = C.merge(this.constructor(), e);
                return t.prevObject = this, t.context = this.context, t
            },
            each: function(e, t) {
                return C.each(this, e, t)
            },
            map: function(i) {
                return this.pushStack(C.map(this, function(e, t) {
                    return i.call(e, t, e)
                }))
            },
            slice: function() {
                return this.pushStack(u.apply(this, arguments))
            },
            first: function() {
                return this.eq(0)
            },
            last: function() {
                return this.eq(-1)
            },
            eq: function(e) {
                var t = this.length,
                    i = +e + (e < 0 ? t : 0);
                return this.pushStack(0 <= i && i < t ? [this[i]] : [])
            },
            end: function() {
                return this.prevObject || this.constructor(null)
            },
            push: a,
            sort: i.sort,
            splice: i.splice
        }, C.extend = C.fn.extend = function() {
            var e, t, i, n, s, r, o = arguments[0] || {},
                a = 1,
                l = arguments.length,
                c = !1;
            for ("boolean" == typeof o && (c = o, o = arguments[a] || {}, a++), "object" == typeof o || C.isFunction(o) || (o = {}), a === l && (o = this, a--); a < l; a++)
                if (null != (e = arguments[a]))
                    for (t in e) i = o[t], o !== (n = e[t]) && (c && n && (C.isPlainObject(n) || (s = C.isArray(n))) ? (r = s ? (s = !1, i && C.isArray(i) ? i : []) : i && C.isPlainObject(i) ? i : {}, o[t] = C.extend(c, r, n)) : void 0 !== n && (o[t] = n));
            return o
        }, C.extend({
            expando: "jQuery" + (o + Math.random()).replace(/\D/g, ""),
            isReady: !0,
            error: function(e) {
                throw new Error(e)
            },
            noop: function() {},
            isFunction: function(e) {
                return "function" === C.type(e)
            },
            isArray: Array.isArray,
            isWindow: function(e) {
                return null != e && e === e.window
            },
            isNumeric: function(e) {
                return !C.isArray(e) && 0 <= e - parseFloat(e) + 1
            },
            isPlainObject: function(e) {
                return "object" === C.type(e) && !e.nodeType && !C.isWindow(e) && !(e.constructor && !m.call(e.constructor.prototype, "isPrototypeOf"))
            },
            isEmptyObject: function(e) {
                var t;
                for (t in e) return !1;
                return !0
            },
            type: function(e) {
                return null == e ? e + "" : "object" == typeof e || "function" == typeof e ? n[r.call(e)] || "object" : typeof e
            },
            globalEval: function(e) {
                var t, i = eval;
                (e = C.trim(e)) && (1 === e.indexOf("use strict") ? ((t = y.createElement("script")).text = e, y.head.appendChild(t).parentNode.removeChild(t)) : i(e))
            },
            camelCase: function(e) {
                return e.replace(c, "ms-").replace(h, t)
            },
            nodeName: function(e, t) {
                return e.nodeName && e.nodeName.toLowerCase() === t.toLowerCase()
            },
            each: function(e, t, i) {
                var n = 0,
                    s = e.length,
                    r = d(e);
                if (i) {
                    if (r)
                        for (; n < s && !1 !== t.apply(e[n], i); n++);
                    else
                        for (n in e)
                            if (!1 === t.apply(e[n], i)) break
                } else if (r)
                    for (; n < s && !1 !== t.call(e[n], n, e[n]); n++);
                else
                    for (n in e)
                        if (!1 === t.call(e[n], n, e[n])) break;
                return e
            },
            trim: function(e) {
                return null == e ? "" : (e + "").replace(l, "")
            },
            makeArray: function(e, t) {
                var i = t || [];
                return null != e && (d(Object(e)) ? C.merge(i, "string" == typeof e ? [e] : e) : a.call(i, e)), i
            },
            inArray: function(e, t, i) {
                return null == t ? -1 : s.call(t, e, i)
            },
            merge: function(e, t) {
                for (var i = +t.length, n = 0, s = e.length; n < i; n++) e[s++] = t[n];
                return e.length = s, e
            },
            grep: function(e, t, i) {
                for (var n = [], s = 0, r = e.length, o = !i; s < r; s++) !t(e[s], s) != o && n.push(e[s]);
                return n
            },
            map: function(e, t, i) {
                var n, s = 0,
                    r = e.length,
                    o = [];
                if (d(e))
                    for (; s < r; s++) null != (n = t(e[s], s, i)) && o.push(n);
                else
                    for (s in e) null != (n = t(e[s], s, i)) && o.push(n);
                return g.apply([], o)
            },
            guid: 1,
            proxy: function(e, t) {
                var i, n, s;
                return "string" == typeof t && (i = e[t], t = e, e = i), C.isFunction(e) ? (n = u.call(arguments, 2), (s = function() {
                    return e.apply(t || this, n.concat(u.call(arguments)))
                }).guid = e.guid = e.guid || C.guid++, s) : void 0
            },
            now: Date.now,
            support: v
        }), C.each("Boolean Number String Function Array Date RegExp Object Error".split(" "), function(e, t) {
            n["[object " + t + "]"] = t.toLowerCase()
        });
        var p = function(i) {
            function h(e, t, i) {
                var n = "0x" + t - 65536;
                return n != n || i ? t : n < 0 ? String.fromCharCode(65536 + n) : String.fromCharCode(n >> 10 | 55296, 1023 & n | 56320)
            }

            function n() {
                v()
            }
            var e, f, x, r, s, g, d, m, b, c, u, v, _, o, y, w, a, p, C, T = "sizzle" + 1 * new Date,
                k = i.document,
                j = 0,
                E = 0,
                l = re(),
                $ = re(),
                D = re(),
                S = function(e, t) {
                    return e === t && (u = !0), 0
                },
                N = {}.hasOwnProperty,
                t = [],
                q = t.pop,
                A = t.push,
                P = t.push,
                H = t.slice,
                L = function(e, t) {
                    for (var i = 0, n = e.length; i < n; i++)
                        if (e[i] === t) return i;
                    return -1
                },
                z = "checked|selected|async|autofocus|autoplay|controls|defer|disabled|hidden|ismap|loop|multiple|open|readonly|required|scoped",
                O = "[\\x20\\t\\r\\n\\f]",
                M = "(?:\\\\.|[\\w-]|[^\\x00-\\xa0])+",
                F = M.replace("w", "w#"),
                W = "\\[" + O + "*(" + M + ")(?:" + O + "*([*^$|!~]?=)" + O + "*(?:'((?:\\\\.|[^\\\\'])*)'|\"((?:\\\\.|[^\\\\\"])*)\"|(" + F + "))|)" + O + "*\\]",
                R = ":(" + M + ")(?:\\((('((?:\\\\.|[^\\\\'])*)'|\"((?:\\\\.|[^\\\\\"])*)\")|((?:\\\\.|[^\\\\()[\\]]|" + W + ")*)|.*)\\)|)",
                I = new RegExp(O + "+", "g"),
                B = new RegExp("^" + O + "+|((?:^|[^\\\\])(?:\\\\.)*)" + O + "+$", "g"),
                X = new RegExp("^" + O + "*," + O + "*"),
                U = new RegExp("^" + O + "*([>+~]|" + O + ")" + O + "*"),
                Q = new RegExp("=" + O + "*([^\\]'\"]*?)" + O + "*\\]", "g"),
                V = new RegExp(R),
                Y = new RegExp("^" + F + "$"),
                K = {
                    ID: new RegExp("^#(" + M + ")"),
                    CLASS: new RegExp("^\\.(" + M + ")"),
                    TAG: new RegExp("^(" + M.replace("w", "w*") + ")"),
                    ATTR: new RegExp("^" + W),
                    PSEUDO: new RegExp("^" + R),
                    CHILD: new RegExp("^:(only|first|last|nth|nth-last)-(child|of-type)(?:\\(" + O + "*(even|odd|(([+-]|)(\\d*)n|)" + O + "*(?:([+-]|)" + O + "*(\\d+)|))" + O + "*\\)|)", "i"),
                    bool: new RegExp("^(?:" + z + ")$", "i"),
                    needsContext: new RegExp("^" + O + "*[>+~]|:(even|odd|eq|gt|lt|nth|first|last)(?:\\(" + O + "*((?:-\\d)?\\d*)" + O + "*\\)|)(?=[^-]|$)", "i")
                },
                Z = /^(?:input|select|textarea|button)$/i,
                G = /^h\d$/i,
                J = /^[^{]+\{\s*\[native \w/,
                ee = /^(?:#([\w-]+)|(\w+)|\.([\w-]+))$/,
                te = /[+~]/,
                ie = /'|\\/g,
                ne = new RegExp("\\\\([\\da-f]{1,6}" + O + "?|(" + O + ")|.)", "ig");
            try {
                P.apply(t = H.call(k.childNodes), k.childNodes), t[k.childNodes.length].nodeType
            } catch (e) {
                P = {
                    apply: t.length ? function(e, t) {
                        A.apply(e, H.call(t))
                    } : function(e, t) {
                        for (var i = e.length, n = 0; e[i++] = t[n++];);
                        e.length = i - 1
                    }
                }
            }

            function se(e, t, i, n) {
                var s, r, o, a, l, c, u, h, d, p;
                if ((t ? t.ownerDocument || t : k) !== _ && v(t), i = i || [], a = (t = t || _).nodeType, "string" != typeof e || !e || 1 !== a && 9 !== a && 11 !== a) return i;
                if (!n && y) {
                    if (11 !== a && (s = ee.exec(e)))
                        if (o = s[1]) {
                            if (9 === a) {
                                if (!(r = t.getElementById(o)) || !r.parentNode) return i;
                                if (r.id === o) return i.push(r), i
                            } else if (t.ownerDocument && (r = t.ownerDocument.getElementById(o)) && C(t, r) && r.id === o) return i.push(r), i
                        } else {
                            if (s[2]) return P.apply(i, t.getElementsByTagName(e)), i;
                            if ((o = s[3]) && f.getElementsByClassName) return P.apply(i, t.getElementsByClassName(o)), i
                        } if (f.qsa && (!w || !w.test(e))) {
                        if (h = u = T, d = t, p = 1 !== a && e, 1 === a && "object" !== t.nodeName.toLowerCase()) {
                            for (c = g(e), (u = t.getAttribute("id")) ? h = u.replace(ie, "\\$&") : t.setAttribute("id", h), h = "[id='" + h + "'] ", l = c.length; l--;) c[l] = h + ge(c[l]);
                            d = te.test(e) && pe(t.parentNode) || t, p = c.join(",")
                        }
                        if (p) try {
                            return P.apply(i, d.querySelectorAll(p)), i
                        } catch (e) {} finally {
                            u || t.removeAttribute("id")
                        }
                    }
                }
                return m(e.replace(B, "$1"), t, i, n)
            }

            function re() {
                var n = [];
                return function e(t, i) {
                    return n.push(t + " ") > x.cacheLength && delete e[n.shift()], e[t + " "] = i
                }
            }

            function oe(e) {
                return e[T] = !0, e
            }

            function ae(e) {
                var t = _.createElement("div");
                try {
                    return !!e(t)
                } catch (e) {
                    return !1
                } finally {
                    t.parentNode && t.parentNode.removeChild(t), t = null
                }
            }

            function le(e, t) {
                for (var i = e.split("|"), n = e.length; n--;) x.attrHandle[i[n]] = t
            }

            function ce(e, t) {
                var i = t && e,
                    n = i && 1 === e.nodeType && 1 === t.nodeType && (~t.sourceIndex || 1 << 31) - (~e.sourceIndex || 1 << 31);
                if (n) return n;
                if (i)
                    for (; i = i.nextSibling;)
                        if (i === t) return -1;
                return e ? 1 : -1
            }

            function ue(t) {
                return function(e) {
                    return "input" === e.nodeName.toLowerCase() && e.type === t
                }
            }

            function he(i) {
                return function(e) {
                    var t = e.nodeName.toLowerCase();
                    return ("input" === t || "button" === t) && e.type === i
                }
            }

            function de(o) {
                return oe(function(r) {
                    return r = +r, oe(function(e, t) {
                        for (var i, n = o([], e.length, r), s = n.length; s--;) e[i = n[s]] && (e[i] = !(t[i] = e[i]))
                    })
                })
            }

            function pe(e) {
                return e && void 0 !== e.getElementsByTagName && e
            }
            for (e in f = se.support = {}, s = se.isXML = function(e) {
                    var t = e && (e.ownerDocument || e).documentElement;
                    return !!t && "HTML" !== t.nodeName
                }, v = se.setDocument = function(e) {
                    var t, i, l = e ? e.ownerDocument || e : k;
                    return l !== _ && 9 === l.nodeType && l.documentElement ? (o = (_ = l).documentElement, (i = l.defaultView) && i !== i.top && (i.addEventListener ? i.addEventListener("unload", n, !1) : i.attachEvent && i.attachEvent("onunload", n)), y = !s(l), f.attributes = ae(function(e) {
                        return e.className = "i", !e.getAttribute("className")
                    }), f.getElementsByTagName = ae(function(e) {
                        return e.appendChild(l.createComment("")), !e.getElementsByTagName("*").length
                    }), f.getElementsByClassName = J.test(l.getElementsByClassName), f.getById = ae(function(e) {
                        return o.appendChild(e).id = T, !l.getElementsByName || !l.getElementsByName(T).length
                    }), f.getById ? (x.find.ID = function(e, t) {
                        if (void 0 !== t.getElementById && y) {
                            var i = t.getElementById(e);
                            return i && i.parentNode ? [i] : []
                        }
                    }, x.filter.ID = function(e) {
                        var t = e.replace(ne, h);
                        return function(e) {
                            return e.getAttribute("id") === t
                        }
                    }) : (delete x.find.ID, x.filter.ID = function(e) {
                        var i = e.replace(ne, h);
                        return function(e) {
                            var t = void 0 !== e.getAttributeNode && e.getAttributeNode("id");
                            return t && t.value === i
                        }
                    }), x.find.TAG = f.getElementsByTagName ? function(e, t) {
                        return void 0 !== t.getElementsByTagName ? t.getElementsByTagName(e) : f.qsa ? t.querySelectorAll(e) : void 0
                    } : function(e, t) {
                        var i, n = [],
                            s = 0,
                            r = t.getElementsByTagName(e);
                        if ("*" !== e) return r;
                        for (; i = r[s++];) 1 === i.nodeType && n.push(i);
                        return n
                    }, x.find.CLASS = f.getElementsByClassName && function(e, t) {
                        return y ? t.getElementsByClassName(e) : void 0
                    }, a = [], w = [], (f.qsa = J.test(l.querySelectorAll)) && (ae(function(e) {
                        o.appendChild(e).innerHTML = "<a id='" + T + "'></a><select id='" + T + "-\f]' msallowcapture=''><option selected=''></option></select>", e.querySelectorAll("[msallowcapture^='']").length && w.push("[*^$]=" + O + "*(?:''|\"\")"), e.querySelectorAll("[selected]").length || w.push("\\[" + O + "*(?:value|" + z + ")"), e.querySelectorAll("[id~=" + T + "-]").length || w.push("~="), e.querySelectorAll(":checked").length || w.push(":checked"), e.querySelectorAll("a#" + T + "+*").length || w.push(".#.+[+~]")
                    }), ae(function(e) {
                        var t = l.createElement("input");
                        t.setAttribute("type", "hidden"), e.appendChild(t).setAttribute("name", "D"), e.querySelectorAll("[name=d]").length && w.push("name" + O + "*[*^$|!~]?="), e.querySelectorAll(":enabled").length || w.push(":enabled", ":disabled"), e.querySelectorAll("*,:x"), w.push(",.*:")
                    })), (f.matchesSelector = J.test(p = o.matches || o.webkitMatchesSelector || o.mozMatchesSelector || o.oMatchesSelector || o.msMatchesSelector)) && ae(function(e) {
                        f.disconnectedMatch = p.call(e, "div"), p.call(e, "[s!='']:x"), a.push("!=", R)
                    }), w = w.length && new RegExp(w.join("|")), a = a.length && new RegExp(a.join("|")), t = J.test(o.compareDocumentPosition), C = t || J.test(o.contains) ? function(e, t) {
                        var i = 9 === e.nodeType ? e.documentElement : e,
                            n = t && t.parentNode;
                        return e === n || !(!n || 1 !== n.nodeType || !(i.contains ? i.contains(n) : e.compareDocumentPosition && 16 & e.compareDocumentPosition(n)))
                    } : function(e, t) {
                        if (t)
                            for (; t = t.parentNode;)
                                if (t === e) return !0;
                        return !1
                    }, S = t ? function(e, t) {
                        if (e === t) return u = !0, 0;
                        var i = !e.compareDocumentPosition - !t.compareDocumentPosition;
                        return i || (1 & (i = (e.ownerDocument || e) === (t.ownerDocument || t) ? e.compareDocumentPosition(t) : 1) || !f.sortDetached && t.compareDocumentPosition(e) === i ? e === l || e.ownerDocument === k && C(k, e) ? -1 : t === l || t.ownerDocument === k && C(k, t) ? 1 : c ? L(c, e) - L(c, t) : 0 : 4 & i ? -1 : 1)
                    } : function(e, t) {
                        if (e === t) return u = !0, 0;
                        var i, n = 0,
                            s = e.parentNode,
                            r = t.parentNode,
                            o = [e],
                            a = [t];
                        if (!s || !r) return e === l ? -1 : t === l ? 1 : s ? -1 : r ? 1 : c ? L(c, e) - L(c, t) : 0;
                        if (s === r) return ce(e, t);
                        for (i = e; i = i.parentNode;) o.unshift(i);
                        for (i = t; i = i.parentNode;) a.unshift(i);
                        for (; o[n] === a[n];) n++;
                        return n ? ce(o[n], a[n]) : o[n] === k ? -1 : a[n] === k ? 1 : 0
                    }, l) : _
                }, se.matches = function(e, t) {
                    return se(e, null, null, t)
                }, se.matchesSelector = function(e, t) {
                    if ((e.ownerDocument || e) !== _ && v(e), t = t.replace(Q, "='$1']"), !(!f.matchesSelector || !y || a && a.test(t) || w && w.test(t))) try {
                        var i = p.call(e, t);
                        if (i || f.disconnectedMatch || e.document && 11 !== e.document.nodeType) return i
                    } catch (e) {}
                    return 0 < se(t, _, null, [e]).length
                }, se.contains = function(e, t) {
                    return (e.ownerDocument || e) !== _ && v(e), C(e, t)
                }, se.attr = function(e, t) {
                    (e.ownerDocument || e) !== _ && v(e);
                    var i = x.attrHandle[t.toLowerCase()],
                        n = i && N.call(x.attrHandle, t.toLowerCase()) ? i(e, t, !y) : void 0;
                    return void 0 !== n ? n : f.attributes || !y ? e.getAttribute(t) : (n = e.getAttributeNode(t)) && n.specified ? n.value : null
                }, se.error = function(e) {
                    throw new Error("Syntax error, unrecognized expression: " + e)
                }, se.uniqueSort = function(e) {
                    var t, i = [],
                        n = 0,
                        s = 0;
                    if (u = !f.detectDuplicates, c = !f.sortStable && e.slice(0), e.sort(S), u) {
                        for (; t = e[s++];) t === e[s] && (n = i.push(s));
                        for (; n--;) e.splice(i[n], 1)
                    }
                    return c = null, e
                }, r = se.getText = function(e) {
                    var t, i = "",
                        n = 0,
                        s = e.nodeType;
                    if (s) {
                        if (1 === s || 9 === s || 11 === s) {
                            if ("string" == typeof e.textContent) return e.textContent;
                            for (e = e.firstChild; e; e = e.nextSibling) i += r(e)
                        } else if (3 === s || 4 === s) return e.nodeValue
                    } else
                        for (; t = e[n++];) i += r(t);
                    return i
                }, (x = se.selectors = {
                    cacheLength: 50,
                    createPseudo: oe,
                    match: K,
                    attrHandle: {},
                    find: {},
                    relative: {
                        ">": {
                            dir: "parentNode",
                            first: !0
                        },
                        " ": {
                            dir: "parentNode"
                        },
                        "+": {
                            dir: "previousSibling",
                            first: !0
                        },
                        "~": {
                            dir: "previousSibling"
                        }
                    },
                    preFilter: {
                        ATTR: function(e) {
                            return e[1] = e[1].replace(ne, h), e[3] = (e[3] || e[4] || e[5] || "").replace(ne, h), "~=" === e[2] && (e[3] = " " + e[3] + " "), e.slice(0, 4)
                        },
                        CHILD: function(e) {
                            return e[1] = e[1].toLowerCase(), "nth" === e[1].slice(0, 3) ? (e[3] || se.error(e[0]), e[4] = +(e[4] ? e[5] + (e[6] || 1) : 2 * ("even" === e[3] || "odd" === e[3])), e[5] = +(e[7] + e[8] || "odd" === e[3])) : e[3] && se.error(e[0]), e
                        },
                        PSEUDO: function(e) {
                            var t, i = !e[6] && e[2];
                            return K.CHILD.test(e[0]) ? null : (e[3] ? e[2] = e[4] || e[5] || "" : i && V.test(i) && (t = g(i, !0)) && (t = i.indexOf(")", i.length - t) - i.length) && (e[0] = e[0].slice(0, t), e[2] = i.slice(0, t)), e.slice(0, 3))
                        }
                    },
                    filter: {
                        TAG: function(e) {
                            var t = e.replace(ne, h).toLowerCase();
                            return "*" === e ? function() {
                                return !0
                            } : function(e) {
                                return e.nodeName && e.nodeName.toLowerCase() === t
                            }
                        },
                        CLASS: function(e) {
                            var t = l[e + " "];
                            return t || (t = new RegExp("(^|" + O + ")" + e + "(" + O + "|$)")) && l(e, function(e) {
                                return t.test("string" == typeof e.className && e.className || void 0 !== e.getAttribute && e.getAttribute("class") || "")
                            })
                        },
                        ATTR: function(i, n, s) {
                            return function(e) {
                                var t = se.attr(e, i);
                                return null == t ? "!=" === n : !n || (t += "", "=" === n ? t === s : "!=" === n ? t !== s : "^=" === n ? s && 0 === t.indexOf(s) : "*=" === n ? s && -1 < t.indexOf(s) : "$=" === n ? s && t.slice(-s.length) === s : "~=" === n ? -1 < (" " + t.replace(I, " ") + " ").indexOf(s) : "|=" === n && (t === s || t.slice(0, s.length + 1) === s + "-"))
                            }
                        },
                        CHILD: function(p, e, t, f, g) {
                            var m = "nth" !== p.slice(0, 3),
                                v = "last" !== p.slice(-4),
                                y = "of-type" === e;
                            return 1 === f && 0 === g ? function(e) {
                                return !!e.parentNode
                            } : function(e, t, i) {
                                var n, s, r, o, a, l, c = m != v ? "nextSibling" : "previousSibling",
                                    u = e.parentNode,
                                    h = y && e.nodeName.toLowerCase(),
                                    d = !i && !y;
                                if (u) {
                                    if (m) {
                                        for (; c;) {
                                            for (r = e; r = r[c];)
                                                if (y ? r.nodeName.toLowerCase() === h : 1 === r.nodeType) return !1;
                                            l = c = "only" === p && !l && "nextSibling"
                                        }
                                        return !0
                                    }
                                    if (l = [v ? u.firstChild : u.lastChild], v && d) {
                                        for (a = (n = (s = u[T] || (u[T] = {}))[p] || [])[0] === j && n[1], o = n[0] === j && n[2], r = a && u.childNodes[a]; r = ++a && r && r[c] || (o = a = 0) || l.pop();)
                                            if (1 === r.nodeType && ++o && r === e) {
                                                s[p] = [j, a, o];
                                                break
                                            }
                                    } else if (d && (n = (e[T] || (e[T] = {}))[p]) && n[0] === j) o = n[1];
                                    else
                                        for (;
                                            (r = ++a && r && r[c] || (o = a = 0) || l.pop()) && ((y ? r.nodeName.toLowerCase() !== h : 1 !== r.nodeType) || !++o || (d && ((r[T] || (r[T] = {}))[p] = [j, o]), r !== e)););
                                    return (o -= g) === f || o % f == 0 && 0 <= o / f
                                }
                            }
                        },
                        PSEUDO: function(e, r) {
                            var t, o = x.pseudos[e] || x.setFilters[e.toLowerCase()] || se.error("unsupported pseudo: " + e);
                            return o[T] ? o(r) : 1 < o.length ? (t = [e, e, "", r], x.setFilters.hasOwnProperty(e.toLowerCase()) ? oe(function(e, t) {
                                for (var i, n = o(e, r), s = n.length; s--;) e[i = L(e, n[s])] = !(t[i] = n[s])
                            }) : function(e) {
                                return o(e, 0, t)
                            }) : o
                        }
                    },
                    pseudos: {
                        not: oe(function(e) {
                            var n = [],
                                s = [],
                                a = d(e.replace(B, "$1"));
                            return a[T] ? oe(function(e, t, i, n) {
                                for (var s, r = a(e, null, n, []), o = e.length; o--;)(s = r[o]) && (e[o] = !(t[o] = s))
                            }) : function(e, t, i) {
                                return n[0] = e, a(n, null, i, s), n[0] = null, !s.pop()
                            }
                        }),
                        has: oe(function(t) {
                            return function(e) {
                                return 0 < se(t, e).length
                            }
                        }),
                        contains: oe(function(t) {
                            return t = t.replace(ne, h),
                                function(e) {
                                    return -1 < (e.textContent || e.innerText || r(e)).indexOf(t)
                                }
                        }),
                        lang: oe(function(i) {
                            return Y.test(i || "") || se.error("unsupported lang: " + i), i = i.replace(ne, h).toLowerCase(),
                                function(e) {
                                    var t;
                                    do {
                                        if (t = y ? e.lang : e.getAttribute("xml:lang") || e.getAttribute("lang")) return (t = t.toLowerCase()) === i || 0 === t.indexOf(i + "-")
                                    } while ((e = e.parentNode) && 1 === e.nodeType);
                                    return !1
                                }
                        }),
                        target: function(e) {
                            var t = i.location && i.location.hash;
                            return t && t.slice(1) === e.id
                        },
                        root: function(e) {
                            return e === o
                        },
                        focus: function(e) {
                            return e === _.activeElement && (!_.hasFocus || _.hasFocus()) && !!(e.type || e.href || ~e.tabIndex)
                        },
                        enabled: function(e) {
                            return !1 === e.disabled
                        },
                        disabled: function(e) {
                            return !0 === e.disabled
                        },
                        checked: function(e) {
                            var t = e.nodeName.toLowerCase();
                            return "input" === t && !!e.checked || "option" === t && !!e.selected
                        },
                        selected: function(e) {
                            return e.parentNode && e.parentNode.selectedIndex, !0 === e.selected
                        },
                        empty: function(e) {
                            for (e = e.firstChild; e; e = e.nextSibling)
                                if (e.nodeType < 6) return !1;
                            return !0
                        },
                        parent: function(e) {
                            return !x.pseudos.empty(e)
                        },
                        header: function(e) {
                            return G.test(e.nodeName)
                        },
                        input: function(e) {
                            return Z.test(e.nodeName)
                        },
                        button: function(e) {
                            var t = e.nodeName.toLowerCase();
                            return "input" === t && "button" === e.type || "button" === t
                        },
                        text: function(e) {
                            var t;
                            return "input" === e.nodeName.toLowerCase() && "text" === e.type && (null == (t = e.getAttribute("type")) || "text" === t.toLowerCase())
                        },
                        first: de(function() {
                            return [0]
                        }),
                        last: de(function(e, t) {
                            return [t - 1]
                        }),
                        eq: de(function(e, t, i) {
                            return [i < 0 ? i + t : i]
                        }),
                        even: de(function(e, t) {
                            for (var i = 0; i < t; i += 2) e.push(i);
                            return e
                        }),
                        odd: de(function(e, t) {
                            for (var i = 1; i < t; i += 2) e.push(i);
                            return e
                        }),
                        lt: de(function(e, t, i) {
                            for (var n = i < 0 ? i + t : i; 0 <= --n;) e.push(n);
                            return e
                        }),
                        gt: de(function(e, t, i) {
                            for (var n = i < 0 ? i + t : i; ++n < t;) e.push(n);
                            return e
                        })
                    }
                }).pseudos.nth = x.pseudos.eq, {
                    radio: !0,
                    checkbox: !0,
                    file: !0,
                    password: !0,
                    image: !0
                }) x.pseudos[e] = ue(e);
            for (e in {
                    submit: !0,
                    reset: !0
                }) x.pseudos[e] = he(e);

            function fe() {}

            function ge(e) {
                for (var t = 0, i = e.length, n = ""; t < i; t++) n += e[t].value;
                return n
            }

            function me(o, e, t) {
                var a = e.dir,
                    l = t && "parentNode" === a,
                    c = E++;
                return e.first ? function(e, t, i) {
                    for (; e = e[a];)
                        if (1 === e.nodeType || l) return o(e, t, i)
                } : function(e, t, i) {
                    var n, s, r = [j, c];
                    if (i) {
                        for (; e = e[a];)
                            if ((1 === e.nodeType || l) && o(e, t, i)) return !0
                    } else
                        for (; e = e[a];)
                            if (1 === e.nodeType || l) {
                                if ((n = (s = e[T] || (e[T] = {}))[a]) && n[0] === j && n[1] === c) return r[2] = n[2];
                                if ((s[a] = r)[2] = o(e, t, i)) return !0
                            }
                }
            }

            function ve(s) {
                return 1 < s.length ? function(e, t, i) {
                    for (var n = s.length; n--;)
                        if (!s[n](e, t, i)) return !1;
                    return !0
                } : s[0]
            }

            function ye(e, t, i, n, s) {
                for (var r, o = [], a = 0, l = e.length, c = null != t; a < l; a++) !(r = e[a]) || i && !i(r, n, s) || (o.push(r), c && t.push(a));
                return o
            }

            function we(p, f, g, m, v, e) {
                return m && !m[T] && (m = we(m)), v && !v[T] && (v = we(v, e)), oe(function(e, t, i, n) {
                    var s, r, o, a = [],
                        l = [],
                        c = t.length,
                        u = e || function(e, t, i) {
                            for (var n = 0, s = t.length; n < s; n++) se(e, t[n], i);
                            return i
                        }(f || "*", i.nodeType ? [i] : i, []),
                        h = !p || !e && f ? u : ye(u, a, p, i, n),
                        d = g ? v || (e ? p : c || m) ? [] : t : h;
                    if (g && g(h, d, i, n), m)
                        for (s = ye(d, l), m(s, [], i, n), r = s.length; r--;)(o = s[r]) && (d[l[r]] = !(h[l[r]] = o));
                    if (e) {
                        if (v || p) {
                            if (v) {
                                for (s = [], r = d.length; r--;)(o = d[r]) && s.push(h[r] = o);
                                v(null, d = [], s, n)
                            }
                            for (r = d.length; r--;)(o = d[r]) && -1 < (s = v ? L(e, o) : a[r]) && (e[s] = !(t[s] = o))
                        }
                    } else d = ye(d === t ? d.splice(c, d.length) : d), v ? v(null, t, d, n) : P.apply(t, d)
                })
            }

            function xe(e) {
                for (var s, t, i, n = e.length, r = x.relative[e[0].type], o = r || x.relative[" "], a = r ? 1 : 0, l = me(function(e) {
                        return e === s
                    }, o, !0), c = me(function(e) {
                        return -1 < L(s, e)
                    }, o, !0), u = [function(e, t, i) {
                        var n = !r && (i || t !== b) || ((s = t).nodeType ? l(e, t, i) : c(e, t, i));
                        return s = null, n
                    }]; a < n; a++)
                    if (t = x.relative[e[a].type]) u = [me(ve(u), t)];
                    else {
                        if ((t = x.filter[e[a].type].apply(null, e[a].matches))[T]) {
                            for (i = ++a; i < n && !x.relative[e[i].type]; i++);
                            return we(1 < a && ve(u), 1 < a && ge(e.slice(0, a - 1).concat({
                                value: " " === e[a - 2].type ? "*" : ""
                            })).replace(B, "$1"), t, a < i && xe(e.slice(a, i)), i < n && xe(e = e.slice(i)), i < n && ge(e))
                        }
                        u.push(t)
                    } return ve(u)
            }
            return fe.prototype = x.filters = x.pseudos, x.setFilters = new fe, g = se.tokenize = function(e, t) {
                var i, n, s, r, o, a, l, c = $[e + " "];
                if (c) return t ? 0 : c.slice(0);
                for (o = e, a = [], l = x.preFilter; o;) {
                    for (r in i && !(n = X.exec(o)) || (n && (o = o.slice(n[0].length) || o), a.push(s = [])), i = !1, (n = U.exec(o)) && (i = n.shift(), s.push({
                            value: i,
                            type: n[0].replace(B, " ")
                        }), o = o.slice(i.length)), x.filter) !(n = K[r].exec(o)) || l[r] && !(n = l[r](n)) || (i = n.shift(), s.push({
                        value: i,
                        type: r,
                        matches: n
                    }), o = o.slice(i.length));
                    if (!i) break
                }
                return t ? o.length : o ? se.error(e) : $(e, a).slice(0)
            }, d = se.compile = function(e, t) {
                var i, n = [],
                    s = [],
                    r = D[e + " "];
                if (!r) {
                    for (i = (t = t || g(e)).length; i--;)(r = xe(t[i]))[T] ? n.push(r) : s.push(r);
                    (r = D(e, function(m, v) {
                        function e(e, t, i, n, s) {
                            var r, o, a, l = 0,
                                c = "0",
                                u = e && [],
                                h = [],
                                d = b,
                                p = e || w && x.find.TAG("*", s),
                                f = j += null == d ? 1 : Math.random() || .1,
                                g = p.length;
                            for (s && (b = t !== _ && t); c !== g && null != (r = p[c]); c++) {
                                if (w && r) {
                                    for (o = 0; a = m[o++];)
                                        if (a(r, t, i)) {
                                            n.push(r);
                                            break
                                        } s && (j = f)
                                }
                                y && ((r = !a && r) && l--, e && u.push(r))
                            }
                            if (l += c, y && c !== l) {
                                for (o = 0; a = v[o++];) a(u, h, t, i);
                                if (e) {
                                    if (0 < l)
                                        for (; c--;) u[c] || h[c] || (h[c] = q.call(n));
                                    h = ye(h)
                                }
                                P.apply(n, h), s && !e && 0 < h.length && 1 < l + v.length && se.uniqueSort(n)
                            }
                            return s && (j = f, b = d), u
                        }
                        var y = 0 < v.length,
                            w = 0 < m.length;
                        return y ? oe(e) : e
                    }(s, n))).selector = e
                }
                return r
            }, m = se.select = function(e, t, i, n) {
                var s, r, o, a, l, c = "function" == typeof e && e,
                    u = !n && g(e = c.selector || e);
                if (i = i || [], 1 === u.length) {
                    if (2 < (r = u[0] = u[0].slice(0)).length && "ID" === (o = r[0]).type && f.getById && 9 === t.nodeType && y && x.relative[r[1].type]) {
                        if (!(t = (x.find.ID(o.matches[0].replace(ne, h), t) || [])[0])) return i;
                        c && (t = t.parentNode), e = e.slice(r.shift().value.length)
                    }
                    for (s = K.needsContext.test(e) ? 0 : r.length; s-- && (o = r[s], !x.relative[a = o.type]);)
                        if ((l = x.find[a]) && (n = l(o.matches[0].replace(ne, h), te.test(r[0].type) && pe(t.parentNode) || t))) {
                            if (r.splice(s, 1), !(e = n.length && ge(r))) return P.apply(i, n), i;
                            break
                        }
                }
                return (c || d(e, u))(n, t, !y, i, te.test(e) && pe(t.parentNode) || t), i
            }, f.sortStable = T.split("").sort(S).join("") === T, f.detectDuplicates = !!u, v(), f.sortDetached = ae(function(e) {
                return 1 & e.compareDocumentPosition(_.createElement("div"))
            }), ae(function(e) {
                return e.innerHTML = "<a href='#'></a>", "#" === e.firstChild.getAttribute("href")
            }) || le("type|href|height|width", function(e, t, i) {
                return i ? void 0 : e.getAttribute(t, "type" === t.toLowerCase() ? 1 : 2)
            }), f.attributes && ae(function(e) {
                return e.innerHTML = "<input/>", e.firstChild.setAttribute("value", ""), "" === e.firstChild.getAttribute("value")
            }) || le("value", function(e, t, i) {
                return i || "input" !== e.nodeName.toLowerCase() ? void 0 : e.defaultValue
            }), ae(function(e) {
                return null == e.getAttribute("disabled")
            }) || le(z, function(e, t, i) {
                var n;
                return i ? void 0 : !0 === e[t] ? t.toLowerCase() : (n = e.getAttributeNode(t)) && n.specified ? n.value : null
            }), se
        }(f);
        C.find = p, C.expr = p.selectors, C.expr[":"] = C.expr.pseudos, C.unique = p.uniqueSort, C.text = p.getText, C.isXMLDoc = p.isXML, C.contains = p.contains;
        var w = C.expr.match.needsContext,
            x = /^<(\w+)\s*\/?>(?:<\/\1>|)$/,
            b = /^.[^:#\[\.,]*$/;

        function _(e, i, n) {
            if (C.isFunction(i)) return C.grep(e, function(e, t) {
                return !!i.call(e, t, e) !== n
            });
            if (i.nodeType) return C.grep(e, function(e) {
                return e === i !== n
            });
            if ("string" == typeof i) {
                if (b.test(i)) return C.filter(i, e, n);
                i = C.filter(i, e)
            }
            return C.grep(e, function(e) {
                return 0 <= s.call(i, e) !== n
            })
        }
        C.filter = function(e, t, i) {
            var n = t[0];
            return i && (e = ":not(" + e + ")"), 1 === t.length && 1 === n.nodeType ? C.find.matchesSelector(n, e) ? [n] : [] : C.find.matches(e, C.grep(t, function(e) {
                return 1 === e.nodeType
            }))
        }, C.fn.extend({
            find: function(e) {
                var t, i = this.length,
                    n = [],
                    s = this;
                if ("string" != typeof e) return this.pushStack(C(e).filter(function() {
                    for (t = 0; t < i; t++)
                        if (C.contains(s[t], this)) return !0
                }));
                for (t = 0; t < i; t++) C.find(e, s[t], n);
                return (n = this.pushStack(1 < i ? C.unique(n) : n)).selector = this.selector ? this.selector + " " + e : e, n
            },
            filter: function(e) {
                return this.pushStack(_(this, e || [], !1))
            },
            not: function(e) {
                return this.pushStack(_(this, e || [], !0))
            },
            is: function(e) {
                return !!_(this, "string" == typeof e && w.test(e) ? C(e) : e || [], !1).length
            }
        });
        var T, k = /^(?:\s*(<[\w\W]+>)[^>]*|#([\w-]*))$/;
        (C.fn.init = function(e, t) {
            var i, n;
            if (!e) return this;
            if ("string" != typeof e) return e.nodeType ? (this.context = this[0] = e, this.length = 1, this) : C.isFunction(e) ? void 0 !== T.ready ? T.ready(e) : e(C) : (void 0 !== e.selector && (this.selector = e.selector, this.context = e.context), C.makeArray(e, this));
            if (!(i = "<" === e[0] && ">" === e[e.length - 1] && 3 <= e.length ? [null, e, null] : k.exec(e)) || !i[1] && t) return !t || t.jquery ? (t || T).find(e) : this.constructor(t).find(e);
            if (i[1]) {
                if (t = t instanceof C ? t[0] : t, C.merge(this, C.parseHTML(i[1], t && t.nodeType ? t.ownerDocument || t : y, !0)), x.test(i[1]) && C.isPlainObject(t))
                    for (i in t) C.isFunction(this[i]) ? this[i](t[i]) : this.attr(i, t[i]);
                return this
            }
            return (n = y.getElementById(i[2])) && n.parentNode && (this.length = 1, this[0] = n), this.context = y, this.selector = e, this
        }).prototype = C.fn, T = C(y);
        var j = /^(?:parents|prev(?:Until|All))/,
            E = {
                children: !0,
                contents: !0,
                next: !0,
                prev: !0
            };

        function $(e, t) {
            for (;
                (e = e[t]) && 1 !== e.nodeType;);
            return e
        }
        C.extend({
            dir: function(e, t, i) {
                for (var n = [], s = void 0 !== i;
                    (e = e[t]) && 9 !== e.nodeType;)
                    if (1 === e.nodeType) {
                        if (s && C(e).is(i)) break;
                        n.push(e)
                    } return n
            },
            sibling: function(e, t) {
                for (var i = []; e; e = e.nextSibling) 1 === e.nodeType && e !== t && i.push(e);
                return i
            }
        }), C.fn.extend({
            has: function(e) {
                var t = C(e, this),
                    i = t.length;
                return this.filter(function() {
                    for (var e = 0; e < i; e++)
                        if (C.contains(this, t[e])) return !0
                })
            },
            closest: function(e, t) {
                for (var i, n = 0, s = this.length, r = [], o = w.test(e) || "string" != typeof e ? C(e, t || this.context) : 0; n < s; n++)
                    for (i = this[n]; i && i !== t; i = i.parentNode)
                        if (i.nodeType < 11 && (o ? -1 < o.index(i) : 1 === i.nodeType && C.find.matchesSelector(i, e))) {
                            r.push(i);
                            break
                        } return this.pushStack(1 < r.length ? C.unique(r) : r)
            },
            index: function(e) {
                return e ? "string" == typeof e ? s.call(C(e), this[0]) : s.call(this, e.jquery ? e[0] : e) : this[0] && this[0].parentNode ? this.first().prevAll().length : -1
            },
            add: function(e, t) {
                return this.pushStack(C.unique(C.merge(this.get(), C(e, t))))
            },
            addBack: function(e) {
                return this.add(null == e ? this.prevObject : this.prevObject.filter(e))
            }
        }), C.each({
            parent: function(e) {
                var t = e.parentNode;
                return t && 11 !== t.nodeType ? t : null
            },
            parents: function(e) {
                return C.dir(e, "parentNode")
            },
            parentsUntil: function(e, t, i) {
                return C.dir(e, "parentNode", i)
            },
            next: function(e) {
                return $(e, "nextSibling")
            },
            prev: function(e) {
                return $(e, "previousSibling")
            },
            nextAll: function(e) {
                return C.dir(e, "nextSibling")
            },
            prevAll: function(e) {
                return C.dir(e, "previousSibling")
            },
            nextUntil: function(e, t, i) {
                return C.dir(e, "nextSibling", i)
            },
            prevUntil: function(e, t, i) {
                return C.dir(e, "previousSibling", i)
            },
            siblings: function(e) {
                return C.sibling((e.parentNode || {}).firstChild, e)
            },
            children: function(e) {
                return C.sibling(e.firstChild)
            },
            contents: function(e) {
                return e.contentDocument || C.merge([], e.childNodes)
            }
        }, function(n, s) {
            C.fn[n] = function(e, t) {
                var i = C.map(this, s, e);
                return "Until" !== n.slice(-5) && (t = e), t && "string" == typeof t && (i = C.filter(t, i)), 1 < this.length && (E[n] || C.unique(i), j.test(n) && i.reverse()), this.pushStack(i)
            }
        });
        var D, S = /\S+/g,
            N = {};

        function q() {
            y.removeEventListener("DOMContentLoaded", q, !1), f.removeEventListener("load", q, !1), C.ready()
        }
        C.Callbacks = function(s) {
            s = "string" == typeof s ? N[s] || function(e) {
                var i = N[e] = {};
                return C.each(e.match(S) || [], function(e, t) {
                    i[t] = !0
                }), i
            }(s) : C.extend({}, s);
            var t, i, n, r, o, a, l = [],
                c = !s.once && [],
                u = function(e) {
                    for (t = s.memory && e, i = !0, a = r || 0, r = 0, o = l.length, n = !0; l && a < o; a++)
                        if (!1 === l[a].apply(e[0], e[1]) && s.stopOnFalse) {
                            t = !1;
                            break
                        } n = !1, l && (c ? c.length && u(c.shift()) : t ? l = [] : h.disable())
                },
                h = {
                    add: function() {
                        if (l) {
                            var e = l.length;
                            ! function n(e) {
                                C.each(e, function(e, t) {
                                    var i = C.type(t);
                                    "function" === i ? s.unique && h.has(t) || l.push(t) : t && t.length && "string" !== i && n(t)
                                })
                            }(arguments), n ? o = l.length : t && (r = e, u(t))
                        }
                        return this
                    },
                    remove: function() {
                        return l && C.each(arguments, function(e, t) {
                            for (var i; - 1 < (i = C.inArray(t, l, i));) l.splice(i, 1), n && (i <= o && o--, i <= a && a--)
                        }), this
                    },
                    has: function(e) {
                        return e ? -1 < C.inArray(e, l) : !(!l || !l.length)
                    },
                    empty: function() {
                        return l = [], o = 0, this
                    },
                    disable: function() {
                        return l = c = t = void 0, this
                    },
                    disabled: function() {
                        return !l
                    },
                    lock: function() {
                        return c = void 0, t || h.disable(), this
                    },
                    locked: function() {
                        return !c
                    },
                    fireWith: function(e, t) {
                        return !l || i && !c || (t = [e, (t = t || []).slice ? t.slice() : t], n ? c.push(t) : u(t)), this
                    },
                    fire: function() {
                        return h.fireWith(this, arguments), this
                    },
                    fired: function() {
                        return !!i
                    }
                };
            return h
        }, C.extend({
            Deferred: function(e) {
                var r = [
                        ["resolve", "done", C.Callbacks("once memory"), "resolved"],
                        ["reject", "fail", C.Callbacks("once memory"), "rejected"],
                        ["notify", "progress", C.Callbacks("memory")]
                    ],
                    s = "pending",
                    o = {
                        state: function() {
                            return s
                        },
                        always: function() {
                            return a.done(arguments).fail(arguments), this
                        },
                        then: function() {
                            var s = arguments;
                            return C.Deferred(function(n) {
                                C.each(r, function(e, t) {
                                    var i = C.isFunction(s[e]) && s[e];
                                    a[t[1]](function() {
                                        var e = i && i.apply(this, arguments);
                                        e && C.isFunction(e.promise) ? e.promise().done(n.resolve).fail(n.reject).progress(n.notify) : n[t[0] + "With"](this === o ? n.promise() : this, i ? [e] : arguments)
                                    })
                                }), s = null
                            }).promise()
                        },
                        promise: function(e) {
                            return null != e ? C.extend(e, o) : o
                        }
                    },
                    a = {};
                return o.pipe = o.then, C.each(r, function(e, t) {
                    var i = t[2],
                        n = t[3];
                    o[t[1]] = i.add, n && i.add(function() {
                        s = n
                    }, r[1 ^ e][2].disable, r[2][2].lock), a[t[0]] = function() {
                        return a[t[0] + "With"](this === a ? o : this, arguments), this
                    }, a[t[0] + "With"] = i.fireWith
                }), o.promise(a), e && e.call(a, a), a
            },
            when: function(e) {
                function t(t, i, n) {
                    return function(e) {
                        i[t] = this, n[t] = 1 < arguments.length ? u.call(arguments) : e, n === s ? c.notifyWith(i, n) : --l || c.resolveWith(i, n)
                    }
                }
                var s, i, n, r = 0,
                    o = u.call(arguments),
                    a = o.length,
                    l = 1 !== a || e && C.isFunction(e.promise) ? a : 0,
                    c = 1 === l ? e : C.Deferred();
                if (1 < a)
                    for (s = new Array(a), i = new Array(a), n = new Array(a); r < a; r++) o[r] && C.isFunction(o[r].promise) ? o[r].promise().done(t(r, n, o)).fail(c.reject).progress(t(r, i, s)) : --l;
                return l || c.resolveWith(n, o), c.promise()
            }
        }), C.fn.ready = function(e) {
            return C.ready.promise().done(e), this
        }, C.extend({
            isReady: !1,
            readyWait: 1,
            holdReady: function(e) {
                e ? C.readyWait++ : C.ready(!0)
            },
            ready: function(e) {
                (!0 === e ? --C.readyWait : C.isReady) || ((C.isReady = !0) !== e && 0 < --C.readyWait || (D.resolveWith(y, [C]), C.fn.triggerHandler && (C(y).triggerHandler("ready"), C(y).off("ready"))))
            }
        }), C.ready.promise = function(e) {
            return D || (D = C.Deferred(), "complete" === y.readyState ? setTimeout(C.ready) : (y.addEventListener("DOMContentLoaded", q, !1), f.addEventListener("load", q, !1))), D.promise(e)
        }, C.ready.promise();
        var A = C.access = function(e, t, i, n, s, r, o) {
            var a = 0,
                l = e.length,
                c = null == i;
            if ("object" === C.type(i))
                for (a in s = !0, i) C.access(e, t, a, i[a], !0, r, o);
            else if (void 0 !== n && (s = !0, C.isFunction(n) || (o = !0), c && (t = o ? (t.call(e, n), null) : (c = t, function(e, t, i) {
                    return c.call(C(e), i)
                })), t))
                for (; a < l; a++) t(e[a], i, o ? n : n.call(e[a], a, t(e[a], i)));
            return s ? e : c ? t.call(e) : l ? t(e[0], i) : r
        };

        function P() {
            Object.defineProperty(this.cache = {}, 0, {
                get: function() {
                    return {}
                }
            }), this.expando = C.expando + P.uid++
        }
        C.acceptData = function(e) {
            return 1 === e.nodeType || 9 === e.nodeType || !+e.nodeType
        }, P.uid = 1, P.accepts = C.acceptData, P.prototype = {
            key: function(t) {
                if (!P.accepts(t)) return 0;
                var i = {},
                    n = t[this.expando];
                if (!n) {
                    n = P.uid++;
                    try {
                        i[this.expando] = {
                            value: n
                        }, Object.defineProperties(t, i)
                    } catch (e) {
                        i[this.expando] = n, C.extend(t, i)
                    }
                }
                return this.cache[n] || (this.cache[n] = {}), n
            },
            set: function(e, t, i) {
                var n, s = this.key(e),
                    r = this.cache[s];
                if ("string" == typeof t) r[t] = i;
                else if (C.isEmptyObject(r)) C.extend(this.cache[s], t);
                else
                    for (n in t) r[n] = t[n];
                return r
            },
            get: function(e, t) {
                var i = this.cache[this.key(e)];
                return void 0 === t ? i : i[t]
            },
            access: function(e, t, i) {
                var n;
                return void 0 === t || t && "string" == typeof t && void 0 === i ? void 0 !== (n = this.get(e, t)) ? n : this.get(e, C.camelCase(t)) : (this.set(e, t, i), void 0 !== i ? i : t)
            },
            remove: function(e, t) {
                var i, n, s, r = this.key(e),
                    o = this.cache[r];
                if (void 0 === t) this.cache[r] = {};
                else {
                    i = (n = C.isArray(t) ? t.concat(t.map(C.camelCase)) : (s = C.camelCase(t), t in o ? [t, s] : (n = s) in o ? [n] : n.match(S) || [])).length;
                    for (; i--;) delete o[n[i]]
                }
            },
            hasData: function(e) {
                return !C.isEmptyObject(this.cache[e[this.expando]] || {})
            },
            discard: function(e) {
                e[this.expando] && delete this.cache[e[this.expando]]
            }
        };
        var H = new P,
            L = new P,
            z = /^(?:\{[\w\W]*\}|\[[\w\W]*\])$/,
            O = /([A-Z])/g;

        function M(e, t, i) {
            var n;
            if (void 0 === i && 1 === e.nodeType)
                if (n = "data-" + t.replace(O, "-$1").toLowerCase(), "string" == typeof(i = e.getAttribute(n))) {
                    try {
                        i = "true" === i || "false" !== i && ("null" === i ? null : +i + "" === i ? +i : z.test(i) ? C.parseJSON(i) : i)
                    } catch (e) {}
                    L.set(e, t, i)
                } else i = void 0;
            return i
        }
        C.extend({
            hasData: function(e) {
                return L.hasData(e) || H.hasData(e)
            },
            data: function(e, t, i) {
                return L.access(e, t, i)
            },
            removeData: function(e, t) {
                L.remove(e, t)
            },
            _data: function(e, t, i) {
                return H.access(e, t, i)
            },
            _removeData: function(e, t) {
                H.remove(e, t)
            }
        }), C.fn.extend({
            data: function(n, e) {
                var t, i, s, r = this[0],
                    o = r && r.attributes;
                if (void 0 !== n) return "object" == typeof n ? this.each(function() {
                    L.set(this, n)
                }) : A(this, function(t) {
                    var e, i = C.camelCase(n);
                    if (r && void 0 === t) {
                        if (void 0 !== (e = L.get(r, n))) return e;
                        if (void 0 !== (e = L.get(r, i))) return e;
                        if (void 0 !== (e = M(r, i, void 0))) return e
                    } else this.each(function() {
                        var e = L.get(this, i);
                        L.set(this, i, t), -1 !== n.indexOf("-") && void 0 !== e && L.set(this, n, t)
                    })
                }, null, e, 1 < arguments.length, null, !0);
                if (this.length && (s = L.get(r), 1 === r.nodeType && !H.get(r, "hasDataAttrs"))) {
                    for (t = o.length; t--;) o[t] && (0 === (i = o[t].name).indexOf("data-") && (i = C.camelCase(i.slice(5)), M(r, i, s[i])));
                    H.set(r, "hasDataAttrs", !0)
                }
                return s
            },
            removeData: function(e) {
                return this.each(function() {
                    L.remove(this, e)
                })
            }
        }), C.extend({
            queue: function(e, t, i) {
                var n;
                return e ? (t = (t || "fx") + "queue", n = H.get(e, t), i && (!n || C.isArray(i) ? n = H.access(e, t, C.makeArray(i)) : n.push(i)), n || []) : void 0
            },
            dequeue: function(e, t) {
                t = t || "fx";
                var i = C.queue(e, t),
                    n = i.length,
                    s = i.shift(),
                    r = C._queueHooks(e, t);
                "inprogress" === s && (s = i.shift(), n--), s && ("fx" === t && i.unshift("inprogress"), delete r.stop, s.call(e, function() {
                    C.dequeue(e, t)
                }, r)), !n && r && r.empty.fire()
            },
            _queueHooks: function(e, t) {
                var i = t + "queueHooks";
                return H.get(e, i) || H.access(e, i, {
                    empty: C.Callbacks("once memory").add(function() {
                        H.remove(e, [t + "queue", i])
                    })
                })
            }
        }), C.fn.extend({
            queue: function(t, i) {
                var e = 2;
                return "string" != typeof t && (i = t, t = "fx", e--), arguments.length < e ? C.queue(this[0], t) : void 0 === i ? this : this.each(function() {
                    var e = C.queue(this, t, i);
                    C._queueHooks(this, t), "fx" === t && "inprogress" !== e[0] && C.dequeue(this, t)
                })
            },
            dequeue: function(e) {
                return this.each(function() {
                    C.dequeue(this, e)
                })
            },
            clearQueue: function(e) {
                return this.queue(e || "fx", [])
            },
            promise: function(e, t) {
                function i() {
                    --s || r.resolveWith(o, [o])
                }
                var n, s = 1,
                    r = C.Deferred(),
                    o = this,
                    a = this.length;
                for ("string" != typeof e && (t = e, e = void 0), e = e || "fx"; a--;)(n = H.get(o[a], e + "queueHooks")) && n.empty && (s++, n.empty.add(i));
                return i(), r.promise(t)
            }
        });

        function F(e, t) {
            return e = t || e, "none" === C.css(e, "display") || !C.contains(e.ownerDocument, e)
        }
        var W, R, I = /[+-]?(?:\d*\.|)\d+(?:[eE][+-]?\d+|)/.source,
            B = ["Top", "Right", "Bottom", "Left"],
            X = /^(?:checkbox|radio)$/i;
        W = y.createDocumentFragment().appendChild(y.createElement("div")), (R = y.createElement("input")).setAttribute("type", "radio"), R.setAttribute("checked", "checked"), R.setAttribute("name", "t"), W.appendChild(R), v.checkClone = W.cloneNode(!0).cloneNode(!0).lastChild.checked, W.innerHTML = "<textarea>x</textarea>", v.noCloneChecked = !!W.cloneNode(!0).lastChild.defaultValue;
        var U = "undefined";
        v.focusinBubbles = "onfocusin" in f;
        var Q = /^key/,
            V = /^(?:mouse|pointer|contextmenu)|click/,
            Y = /^(?:focusinfocus|focusoutblur)$/,
            K = /^([^.]*)(?:\.(.+)|)$/;

        function Z() {
            return !0
        }

        function G() {
            return !1
        }

        function J() {
            try {
                return y.activeElement
            } catch (e) {}
        }
        C.event = {
            global: {},
            add: function(t, e, i, n, s) {
                var r, o, a, l, c, u, h, d, p, f, g, m = H.get(t);
                if (m)
                    for (i.handler && (i = (r = i).handler, s = r.selector), i.guid || (i.guid = C.guid++), (l = m.events) || (l = m.events = {}), (o = m.handle) || (o = m.handle = function(e) {
                            return typeof C != U && C.event.triggered !== e.type ? C.event.dispatch.apply(t, arguments) : void 0
                        }), c = (e = (e || "").match(S) || [""]).length; c--;) p = g = (a = K.exec(e[c]) || [])[1], f = (a[2] || "").split(".").sort(), p && (h = C.event.special[p] || {}, p = (s ? h.delegateType : h.bindType) || p, h = C.event.special[p] || {}, u = C.extend({
                        type: p,
                        origType: g,
                        data: n,
                        handler: i,
                        guid: i.guid,
                        selector: s,
                        needsContext: s && C.expr.match.needsContext.test(s),
                        namespace: f.join(".")
                    }, r), (d = l[p]) || ((d = l[p] = []).delegateCount = 0, h.setup && !1 !== h.setup.call(t, n, f, o) || t.addEventListener && t.addEventListener(p, o, !1)), h.add && (h.add.call(t, u), u.handler.guid || (u.handler.guid = i.guid)), s ? d.splice(d.delegateCount++, 0, u) : d.push(u), C.event.global[p] = !0)
            },
            remove: function(e, t, i, n, s) {
                var r, o, a, l, c, u, h, d, p, f, g, m = H.hasData(e) && H.get(e);
                if (m && (l = m.events)) {
                    for (c = (t = (t || "").match(S) || [""]).length; c--;)
                        if (p = g = (a = K.exec(t[c]) || [])[1], f = (a[2] || "").split(".").sort(), p) {
                            for (h = C.event.special[p] || {}, d = l[p = (n ? h.delegateType : h.bindType) || p] || [], a = a[2] && new RegExp("(^|\\.)" + f.join("\\.(?:.*\\.|)") + "(\\.|$)"), o = r = d.length; r--;) u = d[r], !s && g !== u.origType || i && i.guid !== u.guid || a && !a.test(u.namespace) || n && n !== u.selector && ("**" !== n || !u.selector) || (d.splice(r, 1), u.selector && d.delegateCount--, h.remove && h.remove.call(e, u));
                            o && !d.length && (h.teardown && !1 !== h.teardown.call(e, f, m.handle) || C.removeEvent(e, p, m.handle), delete l[p])
                        } else
                            for (p in l) C.event.remove(e, p + t[c], i, n, !0);
                    C.isEmptyObject(l) && (delete m.handle, H.remove(e, "events"))
                }
            },
            trigger: function(e, t, i, n) {
                var s, r, o, a, l, c, u, h = [i || y],
                    d = m.call(e, "type") ? e.type : e,
                    p = m.call(e, "namespace") ? e.namespace.split(".") : [];
                if (r = o = i = i || y, 3 !== i.nodeType && 8 !== i.nodeType && !Y.test(d + C.event.triggered) && (0 <= d.indexOf(".") && (d = (p = d.split(".")).shift(), p.sort()), l = d.indexOf(":") < 0 && "on" + d, (e = e[C.expando] ? e : new C.Event(d, "object" == typeof e && e)).isTrigger = n ? 2 : 3, e.namespace = p.join("."), e.namespace_re = e.namespace ? new RegExp("(^|\\.)" + p.join("\\.(?:.*\\.|)") + "(\\.|$)") : null, e.result = void 0, e.target || (e.target = i), t = null == t ? [e] : C.makeArray(t, [e]), u = C.event.special[d] || {}, n || !u.trigger || !1 !== u.trigger.apply(i, t))) {
                    if (!n && !u.noBubble && !C.isWindow(i)) {
                        for (a = u.delegateType || d, Y.test(a + d) || (r = r.parentNode); r; r = r.parentNode) h.push(r), o = r;
                        o === (i.ownerDocument || y) && h.push(o.defaultView || o.parentWindow || f)
                    }
                    for (s = 0;
                        (r = h[s++]) && !e.isPropagationStopped();) e.type = 1 < s ? a : u.bindType || d, (c = (H.get(r, "events") || {})[e.type] && H.get(r, "handle")) && c.apply(r, t), (c = l && r[l]) && c.apply && C.acceptData(r) && (e.result = c.apply(r, t), !1 === e.result && e.preventDefault());
                    return e.type = d, n || e.isDefaultPrevented() || u._default && !1 !== u._default.apply(h.pop(), t) || !C.acceptData(i) || l && C.isFunction(i[d]) && !C.isWindow(i) && ((o = i[l]) && (i[l] = null), i[C.event.triggered = d](), C.event.triggered = void 0, o && (i[l] = o)), e.result
                }
            },
            dispatch: function(e) {
                e = C.event.fix(e);
                var t, i, n, s, r, o = [],
                    a = u.call(arguments),
                    l = (H.get(this, "events") || {})[e.type] || [],
                    c = C.event.special[e.type] || {};
                if ((a[0] = e).delegateTarget = this, !c.preDispatch || !1 !== c.preDispatch.call(this, e)) {
                    for (o = C.event.handlers.call(this, e, l), t = 0;
                        (s = o[t++]) && !e.isPropagationStopped();)
                        for (e.currentTarget = s.elem, i = 0;
                            (r = s.handlers[i++]) && !e.isImmediatePropagationStopped();) e.namespace_re && !e.namespace_re.test(r.namespace) || (e.handleObj = r, e.data = r.data, void 0 !== (n = ((C.event.special[r.origType] || {}).handle || r.handler).apply(s.elem, a)) && !1 === (e.result = n) && (e.preventDefault(), e.stopPropagation()));
                    return c.postDispatch && c.postDispatch.call(this, e), e.result
                }
            },
            handlers: function(e, t) {
                var i, n, s, r, o = [],
                    a = t.delegateCount,
                    l = e.target;
                if (a && l.nodeType && (!e.button || "click" !== e.type))
                    for (; l !== this; l = l.parentNode || this)
                        if (!0 !== l.disabled || "click" !== e.type) {
                            for (n = [], i = 0; i < a; i++) void 0 === n[s = (r = t[i]).selector + " "] && (n[s] = r.needsContext ? 0 <= C(s, this).index(l) : C.find(s, this, null, [l]).length), n[s] && n.push(r);
                            n.length && o.push({
                                elem: l,
                                handlers: n
                            })
                        } return a < t.length && o.push({
                    elem: this,
                    handlers: t.slice(a)
                }), o
            },
            props: "altKey bubbles cancelable ctrlKey currentTarget eventPhase metaKey relatedTarget shiftKey target timeStamp view which".split(" "),
            fixHooks: {},
            keyHooks: {
                props: "char charCode key keyCode".split(" "),
                filter: function(e, t) {
                    return null == e.which && (e.which = null != t.charCode ? t.charCode : t.keyCode), e
                }
            },
            mouseHooks: {
                props: "button buttons clientX clientY offsetX offsetY pageX pageY screenX screenY toElement".split(" "),
                filter: function(e, t) {
                    var i, n, s, r = t.button;
                    return null == e.pageX && null != t.clientX && (n = (i = e.target.ownerDocument || y).documentElement, s = i.body, e.pageX = t.clientX + (n && n.scrollLeft || s && s.scrollLeft || 0) - (n && n.clientLeft || s && s.clientLeft || 0), e.pageY = t.clientY + (n && n.scrollTop || s && s.scrollTop || 0) - (n && n.clientTop || s && s.clientTop || 0)), e.which || void 0 === r || (e.which = 1 & r ? 1 : 2 & r ? 3 : 4 & r ? 2 : 0), e
                }
            },
            fix: function(e) {
                if (e[C.expando]) return e;
                var t, i, n, s = e.type,
                    r = e,
                    o = this.fixHooks[s];
                for (o || (this.fixHooks[s] = o = V.test(s) ? this.mouseHooks : Q.test(s) ? this.keyHooks : {}), n = o.props ? this.props.concat(o.props) : this.props, e = new C.Event(r), t = n.length; t--;) e[i = n[t]] = r[i];
                return e.target || (e.target = y), 3 === e.target.nodeType && (e.target = e.target.parentNode), o.filter ? o.filter(e, r) : e
            },
            special: {
                load: {
                    noBubble: !0
                },
                focus: {
                    trigger: function() {
                        return this !== J() && this.focus ? (this.focus(), !1) : void 0
                    },
                    delegateType: "focusin"
                },
                blur: {
                    trigger: function() {
                        return this === J() && this.blur ? (this.blur(), !1) : void 0
                    },
                    delegateType: "focusout"
                },
                click: {
                    trigger: function() {
                        return "checkbox" === this.type && this.click && C.nodeName(this, "input") ? (this.click(), !1) : void 0
                    },
                    _default: function(e) {
                        return C.nodeName(e.target, "a")
                    }
                },
                beforeunload: {
                    postDispatch: function(e) {
                        void 0 !== e.result && e.originalEvent && (e.originalEvent.returnValue = e.result)
                    }
                }
            },
            simulate: function(e, t, i, n) {
                var s = C.extend(new C.Event, i, {
                    type: e,
                    isSimulated: !0,
                    originalEvent: {}
                });
                n ? C.event.trigger(s, null, t) : C.event.dispatch.call(t, s), s.isDefaultPrevented() && i.preventDefault()
            }
        }, C.removeEvent = function(e, t, i) {
            e.removeEventListener && e.removeEventListener(t, i, !1)
        }, C.Event = function(e, t) {
            return this instanceof C.Event ? (e && e.type ? (this.originalEvent = e, this.type = e.type, this.isDefaultPrevented = e.defaultPrevented || void 0 === e.defaultPrevented && !1 === e.returnValue ? Z : G) : this.type = e, t && C.extend(this, t), this.timeStamp = e && e.timeStamp || C.now(), void(this[C.expando] = !0)) : new C.Event(e, t)
        }, C.Event.prototype = {
            isDefaultPrevented: G,
            isPropagationStopped: G,
            isImmediatePropagationStopped: G,
            preventDefault: function() {
                var e = this.originalEvent;
                this.isDefaultPrevented = Z, e && e.preventDefault && e.preventDefault()
            },
            stopPropagation: function() {
                var e = this.originalEvent;
                this.isPropagationStopped = Z, e && e.stopPropagation && e.stopPropagation()
            },
            stopImmediatePropagation: function() {
                var e = this.originalEvent;
                this.isImmediatePropagationStopped = Z, e && e.stopImmediatePropagation && e.stopImmediatePropagation(), this.stopPropagation()
            }
        }, C.each({
            mouseenter: "mouseover",
            mouseleave: "mouseout",
            pointerenter: "pointerover",
            pointerleave: "pointerout"
        }, function(e, s) {
            C.event.special[e] = {
                delegateType: s,
                bindType: s,
                handle: function(e) {
                    var t, i = e.relatedTarget,
                        n = e.handleObj;
                    return i && (i === this || C.contains(this, i)) || (e.type = n.origType, t = n.handler.apply(this, arguments), e.type = s), t
                }
            }
        }), v.focusinBubbles || C.each({
            focus: "focusin",
            blur: "focusout"
        }, function(i, n) {
            function s(e) {
                C.event.simulate(n, e.target, C.event.fix(e), !0)
            }
            C.event.special[n] = {
                setup: function() {
                    var e = this.ownerDocument || this,
                        t = H.access(e, n);
                    t || e.addEventListener(i, s, !0), H.access(e, n, (t || 0) + 1)
                },
                teardown: function() {
                    var e = this.ownerDocument || this,
                        t = H.access(e, n) - 1;
                    t ? H.access(e, n, t) : (e.removeEventListener(i, s, !0), H.remove(e, n))
                }
            }
        }), C.fn.extend({
            on: function(e, t, i, n, s) {
                var r, o;
                if ("object" == typeof e) {
                    for (o in "string" != typeof t && (i = i || t, t = void 0), e) this.on(o, t, i, e[o], s);
                    return this
                }
                if (null == i && null == n ? (n = t, i = t = void 0) : null == n && ("string" == typeof t ? (n = i, i = void 0) : (n = i, i = t, t = void 0)), !1 === n) n = G;
                else if (!n) return this;
                return 1 === s && (r = n, (n = function(e) {
                    return C().off(e), r.apply(this, arguments)
                }).guid = r.guid || (r.guid = C.guid++)), this.each(function() {
                    C.event.add(this, e, n, i, t)
                })
            },
            one: function(e, t, i, n) {
                return this.on(e, t, i, n, 1)
            },
            off: function(e, t, i) {
                var n, s;
                if (e && e.preventDefault && e.handleObj) return n = e.handleObj, C(e.delegateTarget).off(n.namespace ? n.origType + "." + n.namespace : n.origType, n.selector, n.handler), this;
                if ("object" != typeof e) return !1 !== t && "function" != typeof t || (i = t, t = void 0), !1 === i && (i = G), this.each(function() {
                    C.event.remove(this, e, i, t)
                });
                for (s in e) this.off(s, t, e[s]);
                return this
            },
            trigger: function(e, t) {
                return this.each(function() {
                    C.event.trigger(e, t, this)
                })
            },
            triggerHandler: function(e, t) {
                var i = this[0];
                return i ? C.event.trigger(e, t, i, !0) : void 0
            }
        });
        var ee = /<(?!area|br|col|embed|hr|img|input|link|meta|param)(([\w:]+)[^>]*)\/>/gi,
            te = /<([\w:]+)/,
            ie = /<|&#?\w+;/,
            ne = /<(?:script|style|link)/i,
            se = /checked\s*(?:[^=]|=\s*.checked.)/i,
            re = /^$|\/(?:java|ecma)script/i,
            oe = /^true\/(.*)/,
            ae = /^\s*<!(?:\[CDATA\[|--)|(?:\]\]|--)>\s*$/g,
            le = {
                option: [1, "<select multiple='multiple'>", "</select>"],
                thead: [1, "<table>", "</table>"],
                col: [2, "<table><colgroup>", "</colgroup></table>"],
                tr: [2, "<table><tbody>", "</tbody></table>"],
                td: [3, "<table><tbody><tr>", "</tr></tbody></table>"],
                _default: [0, "", ""]
            };

        function ce(e, t) {
            return C.nodeName(e, "table") && C.nodeName(11 !== t.nodeType ? t : t.firstChild, "tr") ? e.getElementsByTagName("tbody")[0] || e.appendChild(e.ownerDocument.createElement("tbody")) : e
        }

        function ue(e) {
            return e.type = (null !== e.getAttribute("type")) + "/" + e.type, e
        }

        function he(e) {
            var t = oe.exec(e.type);
            return t ? e.type = t[1] : e.removeAttribute("type"), e
        }

        function de(e, t) {
            for (var i = 0, n = e.length; i < n; i++) H.set(e[i], "globalEval", !t || H.get(t[i], "globalEval"))
        }

        function pe(e, t) {
            var i, n, s, r, o, a, l, c;
            if (1 === t.nodeType) {
                if (H.hasData(e) && (r = H.access(e), o = H.set(t, r), c = r.events))
                    for (s in delete o.handle, o.events = {}, c)
                        for (i = 0, n = c[s].length; i < n; i++) C.event.add(t, s, c[s][i]);
                L.hasData(e) && (a = L.access(e), l = C.extend({}, a), L.set(t, l))
            }
        }

        function fe(e, t) {
            var i = e.getElementsByTagName ? e.getElementsByTagName(t || "*") : e.querySelectorAll ? e.querySelectorAll(t || "*") : [];
            return void 0 === t || t && C.nodeName(e, t) ? C.merge([e], i) : i
        }
        le.optgroup = le.option, le.tbody = le.tfoot = le.colgroup = le.caption = le.thead, le.th = le.td, C.extend({
            clone: function(e, t, i) {
                var n, s, r, o, a, l, c, u = e.cloneNode(!0),
                    h = C.contains(e.ownerDocument, e);
                if (!(v.noCloneChecked || 1 !== e.nodeType && 11 !== e.nodeType || C.isXMLDoc(e)))
                    for (o = fe(u), n = 0, s = (r = fe(e)).length; n < s; n++) a = r[n], l = o[n], void 0, "input" === (c = l.nodeName.toLowerCase()) && X.test(a.type) ? l.checked = a.checked : "input" !== c && "textarea" !== c || (l.defaultValue = a.defaultValue);
                if (t)
                    if (i)
                        for (r = r || fe(e), o = o || fe(u), n = 0, s = r.length; n < s; n++) pe(r[n], o[n]);
                    else pe(e, u);
                return 0 < (o = fe(u, "script")).length && de(o, !h && fe(e, "script")), u
            },
            buildFragment: function(e, t, i, n) {
                for (var s, r, o, a, l, c, u = t.createDocumentFragment(), h = [], d = 0, p = e.length; d < p; d++)
                    if ((s = e[d]) || 0 === s)
                        if ("object" === C.type(s)) C.merge(h, s.nodeType ? [s] : s);
                        else if (ie.test(s)) {
                    for (r = r || u.appendChild(t.createElement("div")), o = (te.exec(s) || ["", ""])[1].toLowerCase(), a = le[o] || le._default, r.innerHTML = a[1] + s.replace(ee, "<$1></$2>") + a[2], c = a[0]; c--;) r = r.lastChild;
                    C.merge(h, r.childNodes), (r = u.firstChild).textContent = ""
                } else h.push(t.createTextNode(s));
                for (u.textContent = "", d = 0; s = h[d++];)
                    if ((!n || -1 === C.inArray(s, n)) && (l = C.contains(s.ownerDocument, s), r = fe(u.appendChild(s), "script"), l && de(r), i))
                        for (c = 0; s = r[c++];) re.test(s.type || "") && i.push(s);
                return u
            },
            cleanData: function(e) {
                for (var t, i, n, s, r = C.event.special, o = 0; void 0 !== (i = e[o]); o++) {
                    if (C.acceptData(i) && ((s = i[H.expando]) && (t = H.cache[s]))) {
                        if (t.events)
                            for (n in t.events) r[n] ? C.event.remove(i, n) : C.removeEvent(i, n, t.handle);
                        H.cache[s] && delete H.cache[s]
                    }
                    delete L.cache[i[L.expando]]
                }
            }
        }), C.fn.extend({
            text: function(e) {
                return A(this, function(e) {
                    return void 0 === e ? C.text(this) : this.empty().each(function() {
                        1 !== this.nodeType && 11 !== this.nodeType && 9 !== this.nodeType || (this.textContent = e)
                    })
                }, null, e, arguments.length)
            },
            append: function() {
                return this.domManip(arguments, function(e) {
                    1 !== this.nodeType && 11 !== this.nodeType && 9 !== this.nodeType || ce(this, e).appendChild(e)
                })
            },
            prepend: function() {
                return this.domManip(arguments, function(e) {
                    if (1 === this.nodeType || 11 === this.nodeType || 9 === this.nodeType) {
                        var t = ce(this, e);
                        t.insertBefore(e, t.firstChild)
                    }
                })
            },
            before: function() {
                return this.domManip(arguments, function(e) {
                    this.parentNode && this.parentNode.insertBefore(e, this)
                })
            },
            after: function() {
                return this.domManip(arguments, function(e) {
                    this.parentNode && this.parentNode.insertBefore(e, this.nextSibling)
                })
            },
            remove: function(e, t) {
                for (var i, n = e ? C.filter(e, this) : this, s = 0; null != (i = n[s]); s++) t || 1 !== i.nodeType || C.cleanData(fe(i)), i.parentNode && (t && C.contains(i.ownerDocument, i) && de(fe(i, "script")), i.parentNode.removeChild(i));
                return this
            },
            empty: function() {
                for (var e, t = 0; null != (e = this[t]); t++) 1 === e.nodeType && (C.cleanData(fe(e, !1)), e.textContent = "");
                return this
            },
            clone: function(e, t) {
                return e = null != e && e, t = null == t ? e : t, this.map(function() {
                    return C.clone(this, e, t)
                })
            },
            html: function(e) {
                return A(this, function(e) {
                    var t = this[0] || {},
                        i = 0,
                        n = this.length;
                    if (void 0 === e && 1 === t.nodeType) return t.innerHTML;
                    if ("string" == typeof e && !ne.test(e) && !le[(te.exec(e) || ["", ""])[1].toLowerCase()]) {
                        e = e.replace(ee, "<$1></$2>");
                        try {
                            for (; i < n; i++) 1 === (t = this[i] || {}).nodeType && (C.cleanData(fe(t, !1)), t.innerHTML = e);
                            t = 0
                        } catch (e) {}
                    }
                    t && this.empty().append(e)
                }, null, e, arguments.length)
            },
            replaceWith: function() {
                var t = arguments[0];
                return this.domManip(arguments, function(e) {
                    t = this.parentNode, C.cleanData(fe(this)), t && t.replaceChild(e, this)
                }), t && (t.length || t.nodeType) ? this : this.remove()
            },
            detach: function(e) {
                return this.remove(e, !0)
            },
            domManip: function(i, n) {
                i = g.apply([], i);
                var e, t, s, r, o, a, l = 0,
                    c = this.length,
                    u = this,
                    h = c - 1,
                    d = i[0],
                    p = C.isFunction(d);
                if (p || 1 < c && "string" == typeof d && !v.checkClone && se.test(d)) return this.each(function(e) {
                    var t = u.eq(e);
                    p && (i[0] = d.call(this, e, t.html())), t.domManip(i, n)
                });
                if (c && (t = (e = C.buildFragment(i, this[0].ownerDocument, !1, this)).firstChild, 1 === e.childNodes.length && (e = t), t)) {
                    for (r = (s = C.map(fe(e, "script"), ue)).length; l < c; l++) o = e, l !== h && (o = C.clone(o, !0, !0), r && C.merge(s, fe(o, "script"))), n.call(this[l], o, l);
                    if (r)
                        for (a = s[s.length - 1].ownerDocument, C.map(s, he), l = 0; l < r; l++) o = s[l], re.test(o.type || "") && !H.access(o, "globalEval") && C.contains(a, o) && (o.src ? C._evalUrl && C._evalUrl(o.src) : C.globalEval(o.textContent.replace(ae, "")))
                }
                return this
            }
        }), C.each({
            appendTo: "append",
            prependTo: "prepend",
            insertBefore: "before",
            insertAfter: "after",
            replaceAll: "replaceWith"
        }, function(e, o) {
            C.fn[e] = function(e) {
                for (var t, i = [], n = C(e), s = n.length - 1, r = 0; r <= s; r++) t = r === s ? this : this.clone(!0), C(n[r])[o](t), a.apply(i, t.get());
                return this.pushStack(i)
            }
        });
        var ge, me = {};

        function ve(e, t) {
            var i, n = C(t.createElement(e)).appendTo(t.body),
                s = f.getDefaultComputedStyle && (i = f.getDefaultComputedStyle(n[0])) ? i.display : C.css(n[0], "display");
            return n.detach(), s
        }

        function ye(e) {
            var t = y,
                i = me[e];
            return i || ("none" !== (i = ve(e, t)) && i || ((t = (ge = (ge || C("<iframe frameborder='0' width='0' height='0'/>")).appendTo(t.documentElement))[0].contentDocument).write(), t.close(), i = ve(e, t), ge.detach()), me[e] = i), i
        }
        var we = /^margin/,
            xe = new RegExp("^(" + I + ")(?!px)[a-z%]+$", "i"),
            be = function(e) {
                return e.ownerDocument.defaultView.opener ? e.ownerDocument.defaultView.getComputedStyle(e, null) : f.getComputedStyle(e, null)
            };

        function _e(e, t, i) {
            var n, s, r, o, a = e.style;
            return (i = i || be(e)) && (o = i.getPropertyValue(t) || i[t]), i && ("" !== o || C.contains(e.ownerDocument, e) || (o = C.style(e, t)), xe.test(o) && we.test(t) && (n = a.width, s = a.minWidth, r = a.maxWidth, a.minWidth = a.maxWidth = a.width = o, o = i.width, a.width = n, a.minWidth = s, a.maxWidth = r)), void 0 !== o ? o + "" : o
        }

        function Ce(e, t) {
            return {
                get: function() {
                    return e() ? void delete this.get : (this.get = t).apply(this, arguments)
                }
            }
        }! function() {
            var t, i, n = y.documentElement,
                s = y.createElement("div"),
                r = y.createElement("div");
            if (r.style) {
                function e() {
                    r.style.cssText = "-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;display:block;margin-top:1%;top:1%;border:1px;padding:1px;width:4px;position:absolute", r.innerHTML = "", n.appendChild(s);
                    var e = f.getComputedStyle(r, null);
                    t = "1%" !== e.top, i = "4px" === e.width, n.removeChild(s)
                }
                r.style.backgroundClip = "content-box", r.cloneNode(!0).style.backgroundClip = "", v.clearCloneStyle = "content-box" === r.style.backgroundClip, s.style.cssText = "border:0;width:0;height:0;top:0;left:-9999px;margin-top:1px;position:absolute", s.appendChild(r), f.getComputedStyle && C.extend(v, {
                    pixelPosition: function() {
                        return e(), t
                    },
                    boxSizingReliable: function() {
                        return null == i && e(), i
                    },
                    reliableMarginRight: function() {
                        var e, t = r.appendChild(y.createElement("div"));
                        return t.style.cssText = r.style.cssText = "-webkit-box-sizing:content-box;-moz-box-sizing:content-box;box-sizing:content-box;display:block;margin:0;border:0;padding:0", t.style.marginRight = t.style.width = "0", r.style.width = "1px", n.appendChild(s), e = !parseFloat(f.getComputedStyle(t, null).marginRight), n.removeChild(s), r.removeChild(t), e
                    }
                })
            }
        }(), C.swap = function(e, t, i, n) {
            var s, r, o = {};
            for (r in t) o[r] = e.style[r], e.style[r] = t[r];
            for (r in s = i.apply(e, n || []), t) e.style[r] = o[r];
            return s
        };
        var Te = /^(none|table(?!-c[ea]).+)/,
            ke = new RegExp("^(" + I + ")(.*)$", "i"),
            je = new RegExp("^([+-])=(" + I + ")", "i"),
            Ee = {
                position: "absolute",
                visibility: "hidden",
                display: "block"
            },
            $e = {
                letterSpacing: "0",
                fontWeight: "400"
            },
            De = ["Webkit", "O", "Moz", "ms"];

        function Se(e, t) {
            if (t in e) return t;
            for (var i = t[0].toUpperCase() + t.slice(1), n = t, s = De.length; s--;)
                if ((t = De[s] + i) in e) return t;
            return n
        }

        function Ne(e, t, i) {
            var n = ke.exec(t);
            return n ? Math.max(0, n[1] - (i || 0)) + (n[2] || "px") : t
        }

        function qe(e, t, i, n, s) {
            for (var r = i === (n ? "border" : "content") ? 4 : "width" === t ? 1 : 0, o = 0; r < 4; r += 2) "margin" === i && (o += C.css(e, i + B[r], !0, s)), n ? ("content" === i && (o -= C.css(e, "padding" + B[r], !0, s)), "margin" !== i && (o -= C.css(e, "border" + B[r] + "Width", !0, s))) : (o += C.css(e, "padding" + B[r], !0, s), "padding" !== i && (o += C.css(e, "border" + B[r] + "Width", !0, s)));
            return o
        }

        function Ae(e, t, i) {
            var n = !0,
                s = "width" === t ? e.offsetWidth : e.offsetHeight,
                r = be(e),
                o = "border-box" === C.css(e, "boxSizing", !1, r);
            if (s <= 0 || null == s) {
                if (((s = _e(e, t, r)) < 0 || null == s) && (s = e.style[t]), xe.test(s)) return s;
                n = o && (v.boxSizingReliable() || s === e.style[t]), s = parseFloat(s) || 0
            }
            return s + qe(e, t, i || (o ? "border" : "content"), n, r) + "px"
        }

        function Pe(e, t) {
            for (var i, n, s, r = [], o = 0, a = e.length; o < a; o++)(n = e[o]).style && (r[o] = H.get(n, "olddisplay"), i = n.style.display, t ? (r[o] || "none" !== i || (n.style.display = ""), "" === n.style.display && F(n) && (r[o] = H.access(n, "olddisplay", ye(n.nodeName)))) : (s = F(n), "none" === i && s || H.set(n, "olddisplay", s ? i : C.css(n, "display"))));
            for (o = 0; o < a; o++)(n = e[o]).style && (t && "none" !== n.style.display && "" !== n.style.display || (n.style.display = t ? r[o] || "" : "none"));
            return e
        }

        function He(e, t, i, n, s) {
            return new He.prototype.init(e, t, i, n, s)
        }
        C.extend({
            cssHooks: {
                opacity: {
                    get: function(e, t) {
                        if (t) {
                            var i = _e(e, "opacity");
                            return "" === i ? "1" : i
                        }
                    }
                }
            },
            cssNumber: {
                columnCount: !0,
                fillOpacity: !0,
                flexGrow: !0,
                flexShrink: !0,
                fontWeight: !0,
                lineHeight: !0,
                opacity: !0,
                order: !0,
                orphans: !0,
                widows: !0,
                zIndex: !0,
                zoom: !0
            },
            cssProps: {
                float: "cssFloat"
            },
            style: function(e, t, i, n) {
                if (e && 3 !== e.nodeType && 8 !== e.nodeType && e.style) {
                    var s, r, o, a = C.camelCase(t),
                        l = e.style;
                    return t = C.cssProps[a] || (C.cssProps[a] = Se(l, a)), o = C.cssHooks[t] || C.cssHooks[a], void 0 === i ? o && "get" in o && void 0 !== (s = o.get(e, !1, n)) ? s : l[t] : ("string" === (r = typeof i) && (s = je.exec(i)) && (i = (s[1] + 1) * s[2] + parseFloat(C.css(e, t)), r = "number"), void(null != i && i == i && ("number" !== r || C.cssNumber[a] || (i += "px"), v.clearCloneStyle || "" !== i || 0 !== t.indexOf("background") || (l[t] = "inherit"), o && "set" in o && void 0 === (i = o.set(e, i, n)) || (l[t] = i))))
                }
            },
            css: function(e, t, i, n) {
                var s, r, o, a = C.camelCase(t);
                return t = C.cssProps[a] || (C.cssProps[a] = Se(e.style, a)), (o = C.cssHooks[t] || C.cssHooks[a]) && "get" in o && (s = o.get(e, !0, i)), void 0 === s && (s = _e(e, t, n)), "normal" === s && t in $e && (s = $e[t]), "" === i || i ? (r = parseFloat(s), !0 === i || C.isNumeric(r) ? r || 0 : s) : s
            }
        }), C.each(["height", "width"], function(e, s) {
            C.cssHooks[s] = {
                get: function(e, t, i) {
                    return t ? Te.test(C.css(e, "display")) && 0 === e.offsetWidth ? C.swap(e, Ee, function() {
                        return Ae(e, s, i)
                    }) : Ae(e, s, i) : void 0
                },
                set: function(e, t, i) {
                    var n = i && be(e);
                    return Ne(0, t, i ? qe(e, s, i, "border-box" === C.css(e, "boxSizing", !1, n), n) : 0)
                }
            }
        }), C.cssHooks.marginRight = Ce(v.reliableMarginRight, function(e, t) {
            return t ? C.swap(e, {
                display: "inline-block"
            }, _e, [e, "marginRight"]) : void 0
        }), C.each({
            margin: "",
            padding: "",
            border: "Width"
        }, function(s, r) {
            C.cssHooks[s + r] = {
                expand: function(e) {
                    for (var t = 0, i = {}, n = "string" == typeof e ? e.split(" ") : [e]; t < 4; t++) i[s + B[t] + r] = n[t] || n[t - 2] || n[0];
                    return i
                }
            }, we.test(s) || (C.cssHooks[s + r].set = Ne)
        }), C.fn.extend({
            css: function(e, t) {
                return A(this, function(e, t, i) {
                    var n, s, r = {},
                        o = 0;
                    if (C.isArray(t)) {
                        for (n = be(e), s = t.length; o < s; o++) r[t[o]] = C.css(e, t[o], !1, n);
                        return r
                    }
                    return void 0 !== i ? C.style(e, t, i) : C.css(e, t)
                }, e, t, 1 < arguments.length)
            },
            show: function() {
                return Pe(this, !0)
            },
            hide: function() {
                return Pe(this)
            },
            toggle: function(e) {
                return "boolean" == typeof e ? e ? this.show() : this.hide() : this.each(function() {
                    F(this) ? C(this).show() : C(this).hide()
                })
            }
        }), ((C.Tween = He).prototype = {
            constructor: He,
            init: function(e, t, i, n, s, r) {
                this.elem = e, this.prop = i, this.easing = s || "swing", this.options = t, this.start = this.now = this.cur(), this.end = n, this.unit = r || (C.cssNumber[i] ? "" : "px")
            },
            cur: function() {
                var e = He.propHooks[this.prop];
                return e && e.get ? e.get(this) : He.propHooks._default.get(this)
            },
            run: function(e) {
                var t, i = He.propHooks[this.prop];
                return this.options.duration ? this.pos = t = C.easing[this.easing](e, this.options.duration * e, 0, 1, this.options.duration) : this.pos = t = e, this.now = (this.end - this.start) * t + this.start, this.options.step && this.options.step.call(this.elem, this.now, this), i && i.set ? i.set(this) : He.propHooks._default.set(this), this
            }
        }).init.prototype = He.prototype, (He.propHooks = {
            _default: {
                get: function(e) {
                    var t;
                    return null == e.elem[e.prop] || e.elem.style && null != e.elem.style[e.prop] ? (t = C.css(e.elem, e.prop, "")) && "auto" !== t ? t : 0 : e.elem[e.prop]
                },
                set: function(e) {
                    C.fx.step[e.prop] ? C.fx.step[e.prop](e) : e.elem.style && (null != e.elem.style[C.cssProps[e.prop]] || C.cssHooks[e.prop]) ? C.style(e.elem, e.prop, e.now + e.unit) : e.elem[e.prop] = e.now
                }
            }
        }).scrollTop = He.propHooks.scrollLeft = {
            set: function(e) {
                e.elem.nodeType && e.elem.parentNode && (e.elem[e.prop] = e.now)
            }
        }, C.easing = {
            linear: function(e) {
                return e
            },
            swing: function(e) {
                return .5 - Math.cos(e * Math.PI) / 2
            }
        }, C.fx = He.prototype.init, C.fx.step = {};
        var Le, ze, Oe, Me, Fe, We = /^(?:toggle|show|hide)$/,
            Re = new RegExp("^(?:([+-])=|)(" + I + ")([a-z%]*)$", "i"),
            Ie = /queueHooks$/,
            Be = [function(t, e, i) {
                var n, s, r, o, a, l, c, u = this,
                    h = {},
                    d = t.style,
                    p = t.nodeType && F(t),
                    f = H.get(t, "fxshow");
                for (n in i.queue || (null == (a = C._queueHooks(t, "fx")).unqueued && (a.unqueued = 0, l = a.empty.fire, a.empty.fire = function() {
                        a.unqueued || l()
                    }), a.unqueued++, u.always(function() {
                        u.always(function() {
                            a.unqueued--, C.queue(t, "fx").length || a.empty.fire()
                        })
                    })), 1 === t.nodeType && ("height" in e || "width" in e) && (i.overflow = [d.overflow, d.overflowX, d.overflowY], c = C.css(t, "display"), "inline" === ("none" === c ? H.get(t, "olddisplay") || ye(t.nodeName) : c) && "none" === C.css(t, "float") && (d.display = "inline-block")), i.overflow && (d.overflow = "hidden", u.always(function() {
                        d.overflow = i.overflow[0], d.overflowX = i.overflow[1], d.overflowY = i.overflow[2]
                    })), e)
                    if (s = e[n], We.exec(s)) {
                        if (delete e[n], r = r || "toggle" === s, s === (p ? "hide" : "show")) {
                            if ("show" !== s || !f || void 0 === f[n]) continue;
                            p = !0
                        }
                        h[n] = f && f[n] || C.style(t, n)
                    } else c = void 0;
                if (C.isEmptyObject(h)) "inline" === ("none" === c ? ye(t.nodeName) : c) && (d.display = c);
                else
                    for (n in f ? "hidden" in f && (p = f.hidden) : f = H.access(t, "fxshow", {}), r && (f.hidden = !p), p ? C(t).show() : u.done(function() {
                            C(t).hide()
                        }), u.done(function() {
                            var e;
                            for (e in H.remove(t, "fxshow"), h) C.style(t, e, h[e])
                        }), h) o = Ve(p ? f[n] : 0, n, u), n in f || (f[n] = o.start, p && (o.end = o.start, o.start = "width" === n || "height" === n ? 1 : 0))
            }],
            Xe = {
                "*": [function(e, t) {
                    var i = this.createTween(e, t),
                        n = i.cur(),
                        s = Re.exec(t),
                        r = s && s[3] || (C.cssNumber[e] ? "" : "px"),
                        o = (C.cssNumber[e] || "px" !== r && +n) && Re.exec(C.css(i.elem, e)),
                        a = 1,
                        l = 20;
                    if (o && o[3] !== r)
                        for (r = r || o[3], s = s || [], o = +n || 1; o /= a = a || ".5", C.style(i.elem, e, o + r), a !== (a = i.cur() / n) && 1 !== a && --l;);
                    return s && (o = i.start = +o || +n || 0, i.unit = r, i.end = s[1] ? o + (s[1] + 1) * s[2] : +s[2]), i
                }]
            };

        function Ue() {
            return setTimeout(function() {
                Le = void 0
            }), Le = C.now()
        }

        function Qe(e, t) {
            var i, n = 0,
                s = {
                    height: e
                };
            for (t = t ? 1 : 0; n < 4; n += 2 - t) s["margin" + (i = B[n])] = s["padding" + i] = e;
            return t && (s.opacity = s.width = e), s
        }

        function Ve(e, t, i) {
            for (var n, s = (Xe[t] || []).concat(Xe["*"]), r = 0, o = s.length; r < o; r++)
                if (n = s[r].call(i, t, e)) return n
        }

        function Ye(r, e, t) {
            var i, o, n = 0,
                s = Be.length,
                a = C.Deferred().always(function() {
                    delete l.elem
                }),
                l = function() {
                    if (o) return !1;
                    for (var e = Le || Ue(), t = Math.max(0, c.startTime + c.duration - e), i = 1 - (t / c.duration || 0), n = 0, s = c.tweens.length; n < s; n++) c.tweens[n].run(i);
                    return a.notifyWith(r, [c, i, t]), i < 1 && s ? t : (a.resolveWith(r, [c]), !1)
                },
                c = a.promise({
                    elem: r,
                    props: C.extend({}, e),
                    opts: C.extend(!0, {
                        specialEasing: {}
                    }, t),
                    originalProperties: e,
                    originalOptions: t,
                    startTime: Le || Ue(),
                    duration: t.duration,
                    tweens: [],
                    createTween: function(e, t) {
                        var i = C.Tween(r, c.opts, e, t, c.opts.specialEasing[e] || c.opts.easing);
                        return c.tweens.push(i), i
                    },
                    stop: function(e) {
                        var t = 0,
                            i = e ? c.tweens.length : 0;
                        if (o) return this;
                        for (o = !0; t < i; t++) c.tweens[t].run(1);
                        return e ? a.resolveWith(r, [c, e]) : a.rejectWith(r, [c, e]), this
                    }
                }),
                u = c.props;
            for (function(e, t) {
                    var i, n, s, r, o;
                    for (i in e)
                        if (s = t[n = C.camelCase(i)], r = e[i], C.isArray(r) && (s = r[1], r = e[i] = r[0]), i !== n && (e[n] = r, delete e[i]), (o = C.cssHooks[n]) && "expand" in o)
                            for (i in r = o.expand(r), delete e[n], r) i in e || (e[i] = r[i], t[i] = s);
                        else t[n] = s
                }(u, c.opts.specialEasing); n < s; n++)
                if (i = Be[n].call(c, r, u, c.opts)) return i;
            return C.map(u, Ve, c), C.isFunction(c.opts.start) && c.opts.start.call(r, c), C.fx.timer(C.extend(l, {
                elem: r,
                anim: c,
                queue: c.opts.queue
            })), c.progress(c.opts.progress).done(c.opts.done, c.opts.complete).fail(c.opts.fail).always(c.opts.always)
        }
        C.Animation = C.extend(Ye, {
            tweener: function(e, t) {
                for (var i, n = 0, s = (e = C.isFunction(e) ? (t = e, ["*"]) : e.split(" ")).length; n < s; n++) i = e[n], Xe[i] = Xe[i] || [], Xe[i].unshift(t)
            },
            prefilter: function(e, t) {
                t ? Be.unshift(e) : Be.push(e)
            }
        }), C.speed = function(e, t, i) {
            var n = e && "object" == typeof e ? C.extend({}, e) : {
                complete: i || !i && t || C.isFunction(e) && e,
                duration: e,
                easing: i && t || t && !C.isFunction(t) && t
            };
            return n.duration = C.fx.off ? 0 : "number" == typeof n.duration ? n.duration : n.duration in C.fx.speeds ? C.fx.speeds[n.duration] : C.fx.speeds._default, null != n.queue && !0 !== n.queue || (n.queue = "fx"), n.old = n.complete, n.complete = function() {
                C.isFunction(n.old) && n.old.call(this), n.queue && C.dequeue(this, n.queue)
            }, n
        }, C.fn.extend({
            fadeTo: function(e, t, i, n) {
                return this.filter(F).css("opacity", 0).show().end().animate({
                    opacity: t
                }, e, i, n)
            },
            animate: function(t, e, i, n) {
                function s() {
                    var e = Ye(this, C.extend({}, t), o);
                    (r || H.get(this, "finish")) && e.stop(!0)
                }
                var r = C.isEmptyObject(t),
                    o = C.speed(e, i, n);
                return s.finish = s, r || !1 === o.queue ? this.each(s) : this.queue(o.queue, s)
            },
            stop: function(s, e, r) {
                function o(e) {
                    var t = e.stop;
                    delete e.stop, t(r)
                }
                return "string" != typeof s && (r = e, e = s, s = void 0), e && !1 !== s && this.queue(s || "fx", []), this.each(function() {
                    var e = !0,
                        t = null != s && s + "queueHooks",
                        i = C.timers,
                        n = H.get(this);
                    if (t) n[t] && n[t].stop && o(n[t]);
                    else
                        for (t in n) n[t] && n[t].stop && Ie.test(t) && o(n[t]);
                    for (t = i.length; t--;) i[t].elem !== this || null != s && i[t].queue !== s || (i[t].anim.stop(r), e = !1, i.splice(t, 1));
                    !e && r || C.dequeue(this, s)
                })
            },
            finish: function(o) {
                return !1 !== o && (o = o || "fx"), this.each(function() {
                    var e, t = H.get(this),
                        i = t[o + "queue"],
                        n = t[o + "queueHooks"],
                        s = C.timers,
                        r = i ? i.length : 0;
                    for (t.finish = !0, C.queue(this, o, []), n && n.stop && n.stop.call(this, !0), e = s.length; e--;) s[e].elem === this && s[e].queue === o && (s[e].anim.stop(!0), s.splice(e, 1));
                    for (e = 0; e < r; e++) i[e] && i[e].finish && i[e].finish.call(this);
                    delete t.finish
                })
            }
        }), C.each(["toggle", "show", "hide"], function(e, n) {
            var s = C.fn[n];
            C.fn[n] = function(e, t, i) {
                return null == e || "boolean" == typeof e ? s.apply(this, arguments) : this.animate(Qe(n, !0), e, t, i)
            }
        }), C.each({
            slideDown: Qe("show"),
            slideUp: Qe("hide"),
            slideToggle: Qe("toggle"),
            fadeIn: {
                opacity: "show"
            },
            fadeOut: {
                opacity: "hide"
            },
            fadeToggle: {
                opacity: "toggle"
            }
        }, function(e, n) {
            C.fn[e] = function(e, t, i) {
                return this.animate(n, e, t, i)
            }
        }), C.timers = [], C.fx.tick = function() {
            var e, t = 0,
                i = C.timers;
            for (Le = C.now(); t < i.length; t++)(e = i[t])() || i[t] !== e || i.splice(t--, 1);
            i.length || C.fx.stop(), Le = void 0
        }, C.fx.timer = function(e) {
            C.timers.push(e), e() ? C.fx.start() : C.timers.pop()
        }, C.fx.interval = 13, C.fx.start = function() {
            ze = ze || setInterval(C.fx.tick, C.fx.interval)
        }, C.fx.stop = function() {
            clearInterval(ze), ze = null
        }, C.fx.speeds = {
            slow: 600,
            fast: 200,
            _default: 400
        }, C.fn.delay = function(n, e) {
            return n = C.fx && C.fx.speeds[n] || n, e = e || "fx", this.queue(e, function(e, t) {
                var i = setTimeout(e, n);
                t.stop = function() {
                    clearTimeout(i)
                }
            })
        }, Oe = y.createElement("input"), Me = y.createElement("select"), Fe = Me.appendChild(y.createElement("option")), Oe.type = "checkbox", v.checkOn = "" !== Oe.value, v.optSelected = Fe.selected, Me.disabled = !0, v.optDisabled = !Fe.disabled, (Oe = y.createElement("input")).value = "t", Oe.type = "radio", v.radioValue = "t" === Oe.value;
        var Ke, Ze = C.expr.attrHandle;
        C.fn.extend({
            attr: function(e, t) {
                return A(this, C.attr, e, t, 1 < arguments.length)
            },
            removeAttr: function(e) {
                return this.each(function() {
                    C.removeAttr(this, e)
                })
            }
        }), C.extend({
            attr: function(e, t, i) {
                var n, s, r = e.nodeType;
                if (e && 3 !== r && 8 !== r && 2 !== r) return typeof e.getAttribute == U ? C.prop(e, t, i) : (1 === r && C.isXMLDoc(e) || (t = t.toLowerCase(), n = C.attrHooks[t] || (C.expr.match.bool.test(t) ? Ke : void 0)), void 0 === i ? n && "get" in n && null !== (s = n.get(e, t)) ? s : null == (s = C.find.attr(e, t)) ? void 0 : s : null !== i ? n && "set" in n && void 0 !== (s = n.set(e, i, t)) ? s : (e.setAttribute(t, i + ""), i) : void C.removeAttr(e, t))
            },
            removeAttr: function(e, t) {
                var i, n, s = 0,
                    r = t && t.match(S);
                if (r && 1 === e.nodeType)
                    for (; i = r[s++];) n = C.propFix[i] || i, C.expr.match.bool.test(i) && (e[n] = !1), e.removeAttribute(i)
            },
            attrHooks: {
                type: {
                    set: function(e, t) {
                        if (!v.radioValue && "radio" === t && C.nodeName(e, "input")) {
                            var i = e.value;
                            return e.setAttribute("type", t), i && (e.value = i), t
                        }
                    }
                }
            }
        }), Ke = {
            set: function(e, t, i) {
                return !1 === t ? C.removeAttr(e, i) : e.setAttribute(i, i), i
            }
        }, C.each(C.expr.match.bool.source.match(/\w+/g), function(e, t) {
            var r = Ze[t] || C.find.attr;
            Ze[t] = function(e, t, i) {
                var n, s;
                return i || (s = Ze[t], Ze[t] = n, n = null != r(e, t, i) ? t.toLowerCase() : null, Ze[t] = s), n
            }
        });
        var Ge = /^(?:input|select|textarea|button)$/i;
        C.fn.extend({
            prop: function(e, t) {
                return A(this, C.prop, e, t, 1 < arguments.length)
            },
            removeProp: function(e) {
                return this.each(function() {
                    delete this[C.propFix[e] || e]
                })
            }
        }), C.extend({
            propFix: {
                for: "htmlFor",
                class: "className"
            },
            prop: function(e, t, i) {
                var n, s, r = e.nodeType;
                if (e && 3 !== r && 8 !== r && 2 !== r) return (1 !== r || !C.isXMLDoc(e)) && (t = C.propFix[t] || t, s = C.propHooks[t]), void 0 !== i ? s && "set" in s && void 0 !== (n = s.set(e, i, t)) ? n : e[t] = i : s && "get" in s && null !== (n = s.get(e, t)) ? n : e[t]
            },
            propHooks: {
                tabIndex: {
                    get: function(e) {
                        return e.hasAttribute("tabindex") || Ge.test(e.nodeName) || e.href ? e.tabIndex : -1
                    }
                }
            }
        }), v.optSelected || (C.propHooks.selected = {
            get: function(e) {
                var t = e.parentNode;
                return t && t.parentNode && t.parentNode.selectedIndex, null
            }
        }), C.each(["tabIndex", "readOnly", "maxLength", "cellSpacing", "cellPadding", "rowSpan", "colSpan", "useMap", "frameBorder", "contentEditable"], function() {
            C.propFix[this.toLowerCase()] = this
        });
        var Je = /[\t\r\n\f]/g;
        C.fn.extend({
            addClass: function(t) {
                var e, i, n, s, r, o, a = "string" == typeof t && t,
                    l = 0,
                    c = this.length;
                if (C.isFunction(t)) return this.each(function(e) {
                    C(this).addClass(t.call(this, e, this.className))
                });
                if (a)
                    for (e = (t || "").match(S) || []; l < c; l++)
                        if (n = 1 === (i = this[l]).nodeType && (i.className ? (" " + i.className + " ").replace(Je, " ") : " ")) {
                            for (r = 0; s = e[r++];) n.indexOf(" " + s + " ") < 0 && (n += s + " ");
                            o = C.trim(n), i.className !== o && (i.className = o)
                        } return this
            },
            removeClass: function(t) {
                var e, i, n, s, r, o, a = 0 === arguments.length || "string" == typeof t && t,
                    l = 0,
                    c = this.length;
                if (C.isFunction(t)) return this.each(function(e) {
                    C(this).removeClass(t.call(this, e, this.className))
                });
                if (a)
                    for (e = (t || "").match(S) || []; l < c; l++)
                        if (n = 1 === (i = this[l]).nodeType && (i.className ? (" " + i.className + " ").replace(Je, " ") : "")) {
                            for (r = 0; s = e[r++];)
                                for (; 0 <= n.indexOf(" " + s + " ");) n = n.replace(" " + s + " ", " ");
                            o = t ? C.trim(n) : "", i.className !== o && (i.className = o)
                        } return this
            },
            toggleClass: function(s, t) {
                var r = typeof s;
                return "boolean" == typeof t && "string" == r ? t ? this.addClass(s) : this.removeClass(s) : this.each(C.isFunction(s) ? function(e) {
                    C(this).toggleClass(s.call(this, e, this.className, t), t)
                } : function() {
                    if ("string" == r)
                        for (var e, t = 0, i = C(this), n = s.match(S) || []; e = n[t++];) i.hasClass(e) ? i.removeClass(e) : i.addClass(e);
                    else r != U && "boolean" != r || (this.className && H.set(this, "__className__", this.className), this.className = this.className || !1 === s ? "" : H.get(this, "__className__") || "")
                })
            },
            hasClass: function(e) {
                for (var t = " " + e + " ", i = 0, n = this.length; i < n; i++)
                    if (1 === this[i].nodeType && 0 <= (" " + this[i].className + " ").replace(Je, " ").indexOf(t)) return !0;
                return !1
            }
        });
        var et = /\r/g;
        C.fn.extend({
            val: function(i) {
                var n, e, s, t = this[0];
                return arguments.length ? (s = C.isFunction(i), this.each(function(e) {
                    var t;
                    1 === this.nodeType && (null == (t = s ? i.call(this, e, C(this).val()) : i) ? t = "" : "number" == typeof t ? t += "" : C.isArray(t) && (t = C.map(t, function(e) {
                        return null == e ? "" : e + ""
                    })), (n = C.valHooks[this.type] || C.valHooks[this.nodeName.toLowerCase()]) && "set" in n && void 0 !== n.set(this, t, "value") || (this.value = t))
                })) : t ? (n = C.valHooks[t.type] || C.valHooks[t.nodeName.toLowerCase()]) && "get" in n && void 0 !== (e = n.get(t, "value")) ? e : "string" == typeof(e = t.value) ? e.replace(et, "") : null == e ? "" : e : void 0
            }
        }), C.extend({
            valHooks: {
                option: {
                    get: function(e) {
                        var t = C.find.attr(e, "value");
                        return null != t ? t : C.trim(C.text(e))
                    }
                },
                select: {
                    get: function(e) {
                        for (var t, i, n = e.options, s = e.selectedIndex, r = "select-one" === e.type || s < 0, o = r ? null : [], a = r ? s + 1 : n.length, l = s < 0 ? a : r ? s : 0; l < a; l++)
                            if (!(!(i = n[l]).selected && l !== s || (v.optDisabled ? i.disabled : null !== i.getAttribute("disabled")) || i.parentNode.disabled && C.nodeName(i.parentNode, "optgroup"))) {
                                if (t = C(i).val(), r) return t;
                                o.push(t)
                            } return o
                    },
                    set: function(e, t) {
                        for (var i, n, s = e.options, r = C.makeArray(t), o = s.length; o--;)((n = s[o]).selected = 0 <= C.inArray(n.value, r)) && (i = !0);
                        return i || (e.selectedIndex = -1), r
                    }
                }
            }
        }), C.each(["radio", "checkbox"], function() {
            C.valHooks[this] = {
                set: function(e, t) {
                    return C.isArray(t) ? e.checked = 0 <= C.inArray(C(e).val(), t) : void 0
                }
            }, v.checkOn || (C.valHooks[this].get = function(e) {
                return null === e.getAttribute("value") ? "on" : e.value
            })
        }), C.each("blur focus focusin focusout load resize scroll unload click dblclick mousedown mouseup mousemove mouseover mouseout mouseenter mouseleave change select submit keydown keypress keyup error contextmenu".split(" "), function(e, i) {
            C.fn[i] = function(e, t) {
                return 0 < arguments.length ? this.on(i, null, e, t) : this.trigger(i)
            }
        }), C.fn.extend({
            hover: function(e, t) {
                return this.mouseenter(e).mouseleave(t || e)
            },
            bind: function(e, t, i) {
                return this.on(e, null, t, i)
            },
            unbind: function(e, t) {
                return this.off(e, null, t)
            },
            delegate: function(e, t, i, n) {
                return this.on(t, e, i, n)
            },
            undelegate: function(e, t, i) {
                return 1 === arguments.length ? this.off(e, "**") : this.off(t, e || "**", i)
            }
        });
        var tt = C.now(),
            it = /\?/;
        C.parseJSON = function(e) {
            return JSON.parse(e + "")
        }, C.parseXML = function(e) {
            var t;
            if (!e || "string" != typeof e) return null;
            try {
                t = (new DOMParser).parseFromString(e, "text/xml")
            } catch (e) {
                t = void 0
            }
            return t && !t.getElementsByTagName("parsererror").length || C.error("Invalid XML: " + e), t
        };
        var nt = /#.*$/,
            st = /([?&])_=[^&]*/,
            rt = /^(.*?):[ \t]*([^\r\n]*)$/gm,
            ot = /^(?:GET|HEAD)$/,
            at = /^\/\//,
            lt = /^([\w.+-]+:)(?:\/\/(?:[^\/?#]*@|)([^\/?#:]*)(?::(\d+)|)|)/,
            ct = {},
            ut = {},
            ht = "*/".concat("*"),
            dt = f.location.href,
            pt = lt.exec(dt.toLowerCase()) || [];

        function ft(r) {
            return function(e, t) {
                "string" != typeof e && (t = e, e = "*");
                var i, n = 0,
                    s = e.toLowerCase().match(S) || [];
                if (C.isFunction(t))
                    for (; i = s[n++];) "+" === i[0] ? (i = i.slice(1) || "*", (r[i] = r[i] || []).unshift(t)) : (r[i] = r[i] || []).push(t)
            }
        }

        function gt(t, s, r, o) {
            var a = {},
                l = t === ut;

            function c(e) {
                var n;
                return a[e] = !0, C.each(t[e] || [], function(e, t) {
                    var i = t(s, r, o);
                    return "string" != typeof i || l || a[i] ? l ? !(n = i) : void 0 : (s.dataTypes.unshift(i), c(i), !1)
                }), n
            }
            return c(s.dataTypes[0]) || !a["*"] && c("*")
        }

        function mt(e, t) {
            var i, n, s = C.ajaxSettings.flatOptions || {};
            for (i in t) void 0 !== t[i] && ((s[i] ? e : n = n || {})[i] = t[i]);
            return n && C.extend(!0, e, n), e
        }
        C.extend({
            active: 0,
            lastModified: {},
            etag: {},
            ajaxSettings: {
                url: dt,
                type: "GET",
                isLocal: /^(?:about|app|app-storage|.+-extension|file|res|widget):$/.test(pt[1]),
                global: !0,
                processData: !0,
                async: !0,
                contentType: "application/x-www-form-urlencoded; charset=UTF-8",
                accepts: {
                    "*": ht,
                    text: "text/plain",
                    html: "text/html",
                    xml: "application/xml, text/xml",
                    json: "application/json, text/javascript"
                },
                contents: {
                    xml: /xml/,
                    html: /html/,
                    json: /json/
                },
                responseFields: {
                    xml: "responseXML",
                    text: "responseText",
                    json: "responseJSON"
                },
                converters: {
                    "* text": String,
                    "text html": !0,
                    "text json": C.parseJSON,
                    "text xml": C.parseXML
                },
                flatOptions: {
                    url: !0,
                    context: !0
                }
            },
            ajaxSetup: function(e, t) {
                return t ? mt(mt(e, C.ajaxSettings), t) : mt(C.ajaxSettings, e)
            },
            ajaxPrefilter: ft(ct),
            ajaxTransport: ft(ut),
            ajax: function(e, t) {
                "object" == typeof e && (t = e, e = void 0), t = t || {};
                var u, h, d, i, p, n, f, s, g = C.ajaxSetup({}, t),
                    m = g.context || g,
                    v = g.context && (m.nodeType || m.jquery) ? C(m) : C.event,
                    y = C.Deferred(),
                    w = C.Callbacks("once memory"),
                    x = g.statusCode || {},
                    r = {},
                    o = {},
                    b = 0,
                    a = "canceled",
                    _ = {
                        readyState: 0,
                        getResponseHeader: function(e) {
                            var t;
                            if (2 === b) {
                                if (!i)
                                    for (i = {}; t = rt.exec(d);) i[t[1].toLowerCase()] = t[2];
                                t = i[e.toLowerCase()]
                            }
                            return null == t ? null : t
                        },
                        getAllResponseHeaders: function() {
                            return 2 === b ? d : null
                        },
                        setRequestHeader: function(e, t) {
                            var i = e.toLowerCase();
                            return b || (e = o[i] = o[i] || e, r[e] = t), this
                        },
                        overrideMimeType: function(e) {
                            return b || (g.mimeType = e), this
                        },
                        statusCode: function(e) {
                            var t;
                            if (e)
                                if (b < 2)
                                    for (t in e) x[t] = [x[t], e[t]];
                                else _.always(e[_.status]);
                            return this
                        },
                        abort: function(e) {
                            var t = e || a;
                            return u && u.abort(t), l(0, t), this
                        }
                    };
                if (y.promise(_).complete = w.add, _.success = _.done, _.error = _.fail, g.url = ((e || g.url || dt) + "").replace(nt, "").replace(at, pt[1] + "//"), g.type = t.method || t.type || g.method || g.type, g.dataTypes = C.trim(g.dataType || "*").toLowerCase().match(S) || [""], null == g.crossDomain && (n = lt.exec(g.url.toLowerCase()), g.crossDomain = !(!n || n[1] === pt[1] && n[2] === pt[2] && (n[3] || ("http:" === n[1] ? "80" : "443")) === (pt[3] || ("http:" === pt[1] ? "80" : "443")))), g.data && g.processData && "string" != typeof g.data && (g.data = C.param(g.data, g.traditional)), gt(ct, g, t, _), 2 === b) return _;
                for (s in (f = C.event && g.global) && 0 == C.active++ && C.event.trigger("ajaxStart"), g.type = g.type.toUpperCase(), g.hasContent = !ot.test(g.type), h = g.url, g.hasContent || (g.data && (h = g.url += (it.test(h) ? "&" : "?") + g.data, delete g.data), !1 === g.cache && (g.url = st.test(h) ? h.replace(st, "$1_=" + tt++) : h + (it.test(h) ? "&" : "?") + "_=" + tt++)), g.ifModified && (C.lastModified[h] && _.setRequestHeader("If-Modified-Since", C.lastModified[h]), C.etag[h] && _.setRequestHeader("If-None-Match", C.etag[h])), (g.data && g.hasContent && !1 !== g.contentType || t.contentType) && _.setRequestHeader("Content-Type", g.contentType), _.setRequestHeader("Accept", g.dataTypes[0] && g.accepts[g.dataTypes[0]] ? g.accepts[g.dataTypes[0]] + ("*" !== g.dataTypes[0] ? ", " + ht + "; q=0.01" : "") : g.accepts["*"]), g.headers) _.setRequestHeader(s, g.headers[s]);
                if (g.beforeSend && (!1 === g.beforeSend.call(m, _, g) || 2 === b)) return _.abort();
                for (s in a = "abort", {
                        success: 1,
                        error: 1,
                        complete: 1
                    }) _[s](g[s]);
                if (u = gt(ut, g, t, _)) {
                    _.readyState = 1, f && v.trigger("ajaxSend", [_, g]), g.async && 0 < g.timeout && (p = setTimeout(function() {
                        _.abort("timeout")
                    }, g.timeout));
                    try {
                        b = 1, u.send(r, l)
                    } catch (e) {
                        if (!(b < 2)) throw e;
                        l(-1, e)
                    }
                } else l(-1, "No Transport");

                function l(e, t, i, n) {
                    var s, r, o, a, l, c = t;
                    2 !== b && (b = 2, p && clearTimeout(p), u = void 0, d = n || "", _.readyState = 0 < e ? 4 : 0, s = 200 <= e && e < 300 || 304 === e, i && (a = function(e, t, i) {
                        for (var n, s, r, o, a = e.contents, l = e.dataTypes;
                            "*" === l[0];) l.shift(), void 0 === n && (n = e.mimeType || t.getResponseHeader("Content-Type"));
                        if (n)
                            for (s in a)
                                if (a[s] && a[s].test(n)) {
                                    l.unshift(s);
                                    break
                                } if (l[0] in i) r = l[0];
                        else {
                            for (s in i) {
                                if (!l[0] || e.converters[s + " " + l[0]]) {
                                    r = s;
                                    break
                                }
                                o = o || s
                            }
                            r = r || o
                        }
                        return r ? (r !== l[0] && l.unshift(r), i[r]) : void 0
                    }(g, _, i)), a = function(e, t, i, n) {
                        var s, r, o, a, l, c = {},
                            u = e.dataTypes.slice();
                        if (u[1])
                            for (o in e.converters) c[o.toLowerCase()] = e.converters[o];
                        for (r = u.shift(); r;)
                            if (e.responseFields[r] && (i[e.responseFields[r]] = t), !l && n && e.dataFilter && (t = e.dataFilter(t, e.dataType)), l = r, r = u.shift())
                                if ("*" === r) r = l;
                                else if ("*" !== l && l !== r) {
                            if (!(o = c[l + " " + r] || c["* " + r]))
                                for (s in c)
                                    if ((a = s.split(" "))[1] === r && (o = c[l + " " + a[0]] || c["* " + a[0]])) {
                                        !0 === o ? o = c[s] : !0 !== c[s] && (r = a[0], u.unshift(a[1]));
                                        break
                                    } if (!0 !== o)
                                if (o && e.throws) t = o(t);
                                else try {
                                    t = o(t)
                                } catch (e) {
                                    return {
                                        state: "parsererror",
                                        error: o ? e : "No conversion from " + l + " to " + r
                                    }
                                }
                        }
                        return {
                            state: "success",
                            data: t
                        }
                    }(g, a, _, s), s ? (g.ifModified && ((l = _.getResponseHeader("Last-Modified")) && (C.lastModified[h] = l), (l = _.getResponseHeader("etag")) && (C.etag[h] = l)), 204 === e || "HEAD" === g.type ? c = "nocontent" : 304 === e ? c = "notmodified" : (c = a.state, r = a.data, s = !(o = a.error))) : (o = c, !e && c || (c = "error", e < 0 && (e = 0))), _.status = e, _.statusText = (t || c) + "", s ? y.resolveWith(m, [r, c, _]) : y.rejectWith(m, [_, c, o]), _.statusCode(x), x = void 0, f && v.trigger(s ? "ajaxSuccess" : "ajaxError", [_, g, s ? r : o]), w.fireWith(m, [_, c]), f && (v.trigger("ajaxComplete", [_, g]), --C.active || C.event.trigger("ajaxStop")))
                }
                return _
            },
            getJSON: function(e, t, i) {
                return C.get(e, t, i, "json")
            },
            getScript: function(e, t) {
                return C.get(e, void 0, t, "script")
            }
        }), C.each(["get", "post"], function(e, s) {
            C[s] = function(e, t, i, n) {
                return C.isFunction(t) && (n = n || i, i = t, t = void 0), C.ajax({
                    url: e,
                    type: s,
                    dataType: n,
                    data: t,
                    success: i
                })
            }
        }), C._evalUrl = function(e) {
            return C.ajax({
                url: e,
                type: "GET",
                dataType: "script",
                async: !1,
                global: !1,
                throws: !0
            })
        }, C.fn.extend({
            wrapAll: function(t) {
                var e;
                return C.isFunction(t) ? this.each(function(e) {
                    C(this).wrapAll(t.call(this, e))
                }) : (this[0] && (e = C(t, this[0].ownerDocument).eq(0).clone(!0), this[0].parentNode && e.insertBefore(this[0]), e.map(function() {
                    for (var e = this; e.firstElementChild;) e = e.firstElementChild;
                    return e
                }).append(this)), this)
            },
            wrapInner: function(i) {
                return this.each(C.isFunction(i) ? function(e) {
                    C(this).wrapInner(i.call(this, e))
                } : function() {
                    var e = C(this),
                        t = e.contents();
                    t.length ? t.wrapAll(i) : e.append(i)
                })
            },
            wrap: function(t) {
                var i = C.isFunction(t);
                return this.each(function(e) {
                    C(this).wrapAll(i ? t.call(this, e) : t)
                })
            },
            unwrap: function() {
                return this.parent().each(function() {
                    C.nodeName(this, "body") || C(this).replaceWith(this.childNodes)
                }).end()
            }
        }), C.expr.filters.hidden = function(e) {
            return e.offsetWidth <= 0 && e.offsetHeight <= 0
        }, C.expr.filters.visible = function(e) {
            return !C.expr.filters.hidden(e)
        };
        var vt = /%20/g,
            yt = /\[\]$/,
            wt = /\r?\n/g,
            xt = /^(?:submit|button|image|reset|file)$/i,
            bt = /^(?:input|select|textarea|keygen)/i;

        function _t(i, e, n, s) {
            var t;
            if (C.isArray(e)) C.each(e, function(e, t) {
                n || yt.test(i) ? s(i, t) : _t(i + "[" + ("object" == typeof t ? e : "") + "]", t, n, s)
            });
            else if (n || "object" !== C.type(e)) s(i, e);
            else
                for (t in e) _t(i + "[" + t + "]", e[t], n, s)
        }
        C.param = function(e, t) {
            function i(e, t) {
                t = C.isFunction(t) ? t() : null == t ? "" : t, s[s.length] = encodeURIComponent(e) + "=" + encodeURIComponent(t)
            }
            var n, s = [];
            if (void 0 === t && (t = C.ajaxSettings && C.ajaxSettings.traditional), C.isArray(e) || e.jquery && !C.isPlainObject(e)) C.each(e, function() {
                i(this.name, this.value)
            });
            else
                for (n in e) _t(n, e[n], t, i);
            return s.join("&").replace(vt, "+")
        }, C.fn.extend({
            serialize: function() {
                return C.param(this.serializeArray())
            },
            serializeArray: function() {
                return this.map(function() {
                    var e = C.prop(this, "elements");
                    return e ? C.makeArray(e) : this
                }).filter(function() {
                    var e = this.type;
                    return this.name && !C(this).is(":disabled") && bt.test(this.nodeName) && !xt.test(e) && (this.checked || !X.test(e))
                }).map(function(e, t) {
                    var i = C(this).val();
                    return null == i ? null : C.isArray(i) ? C.map(i, function(e) {
                        return {
                            name: t.name,
                            value: e.replace(wt, "\r\n")
                        }
                    }) : {
                        name: t.name,
                        value: i.replace(wt, "\r\n")
                    }
                }).get()
            }
        }), C.ajaxSettings.xhr = function() {
            try {
                return new XMLHttpRequest
            } catch (e) {}
        };
        var Ct = 0,
            Tt = {},
            kt = {
                0: 200,
                1223: 204
            },
            jt = C.ajaxSettings.xhr();
        f.attachEvent && f.attachEvent("onunload", function() {
            for (var e in Tt) Tt[e]()
        }), v.cors = !!jt && "withCredentials" in jt, v.ajax = jt = !!jt, C.ajaxTransport(function(r) {
            var o;
            return v.cors || jt && !r.crossDomain ? {
                send: function(e, t) {
                    var i, n = r.xhr(),
                        s = ++Ct;
                    if (n.open(r.type, r.url, r.async, r.username, r.password), r.xhrFields)
                        for (i in r.xhrFields) n[i] = r.xhrFields[i];
                    for (i in r.mimeType && n.overrideMimeType && n.overrideMimeType(r.mimeType), r.crossDomain || e["X-Requested-With"] || (e["X-Requested-With"] = "XMLHttpRequest"), e) n.setRequestHeader(i, e[i]);
                    o = function(e) {
                        return function() {
                            o && (delete Tt[s], o = n.onload = n.onerror = null, "abort" === e ? n.abort() : "error" === e ? t(n.status, n.statusText) : t(kt[n.status] || n.status, n.statusText, "string" == typeof n.responseText ? {
                                text: n.responseText
                            } : void 0, n.getAllResponseHeaders()))
                        }
                    }, n.onload = o(), n.onerror = o("error"), o = Tt[s] = o("abort");
                    try {
                        n.send(r.hasContent && r.data || null)
                    } catch (e) {
                        if (o) throw e
                    }
                },
                abort: function() {
                    o && o()
                }
            } : void 0
        }), C.ajaxSetup({
            accepts: {
                script: "text/javascript, application/javascript, application/ecmascript, application/x-ecmascript"
            },
            contents: {
                script: /(?:java|ecma)script/
            },
            converters: {
                "text script": function(e) {
                    return C.globalEval(e), e
                }
            }
        }), C.ajaxPrefilter("script", function(e) {
            void 0 === e.cache && (e.cache = !1), e.crossDomain && (e.type = "GET")
        }), C.ajaxTransport("script", function(i) {
            var n, s;
            if (i.crossDomain) return {
                send: function(e, t) {
                    n = C("<script>").prop({
                        async: !0,
                        charset: i.scriptCharset,
                        src: i.url
                    }).on("load error", s = function(e) {
                        n.remove(), s = null, e && t("error" === e.type ? 404 : 200, e.type)
                    }), y.head.appendChild(n[0])
                },
                abort: function() {
                    s && s()
                }
            }
        });
        var Et = [],
            $t = /(=)\?(?=&|$)|\?\?/;
        C.ajaxSetup({
            jsonp: "callback",
            jsonpCallback: function() {
                var e = Et.pop() || C.expando + "_" + tt++;
                return this[e] = !0, e
            }
        }), C.ajaxPrefilter("json jsonp", function(e, t, i) {
            var n, s, r, o = !1 !== e.jsonp && ($t.test(e.url) ? "url" : "string" == typeof e.data && !(e.contentType || "").indexOf("application/x-www-form-urlencoded") && $t.test(e.data) && "data");
            return o || "jsonp" === e.dataTypes[0] ? (n = e.jsonpCallback = C.isFunction(e.jsonpCallback) ? e.jsonpCallback() : e.jsonpCallback, o ? e[o] = e[o].replace($t, "$1" + n) : !1 !== e.jsonp && (e.url += (it.test(e.url) ? "&" : "?") + e.jsonp + "=" + n), e.converters["script json"] = function() {
                return r || C.error(n + " was not called"), r[0]
            }, e.dataTypes[0] = "json", s = f[n], f[n] = function() {
                r = arguments
            }, i.always(function() {
                f[n] = s, e[n] && (e.jsonpCallback = t.jsonpCallback, Et.push(n)), r && C.isFunction(s) && s(r[0]), r = s = void 0
            }), "script") : void 0
        }), C.parseHTML = function(e, t, i) {
            if (!e || "string" != typeof e) return null;
            "boolean" == typeof t && (i = t, t = !1), t = t || y;
            var n = x.exec(e),
                s = !i && [];
            return n ? [t.createElement(n[1])] : (n = C.buildFragment([e], t, s), s && s.length && C(s).remove(), C.merge([], n.childNodes))
        };
        var Dt = C.fn.load;
        C.fn.load = function(e, t, i) {
            if ("string" != typeof e && Dt) return Dt.apply(this, arguments);
            var n, s, r, o = this,
                a = e.indexOf(" ");
            return 0 <= a && (n = C.trim(e.slice(a)), e = e.slice(0, a)), C.isFunction(t) ? (i = t, t = void 0) : t && "object" == typeof t && (s = "POST"), 0 < o.length && C.ajax({
                url: e,
                type: s,
                dataType: "html",
                data: t
            }).done(function(e) {
                r = arguments, o.html(n ? C("<div>").append(C.parseHTML(e)).find(n) : e)
            }).complete(i && function(e, t) {
                o.each(i, r || [e.responseText, t, e])
            }), this
        }, C.each(["ajaxStart", "ajaxStop", "ajaxComplete", "ajaxError", "ajaxSuccess", "ajaxSend"], function(e, t) {
            C.fn[t] = function(e) {
                return this.on(t, e)
            }
        }), C.expr.filters.animated = function(t) {
            return C.grep(C.timers, function(e) {
                return t === e.elem
            }).length
        };
        var St = f.document.documentElement;

        function Nt(e) {
            return C.isWindow(e) ? e : 9 === e.nodeType && e.defaultView
        }
        C.offset = {
            setOffset: function(e, t, i) {
                var n, s, r, o, a, l, c = C.css(e, "position"),
                    u = C(e),
                    h = {};
                "static" === c && (e.style.position = "relative"), a = u.offset(), r = C.css(e, "top"), l = C.css(e, "left"), s = ("absolute" === c || "fixed" === c) && -1 < (r + l).indexOf("auto") ? (o = (n = u.position()).top, n.left) : (o = parseFloat(r) || 0, parseFloat(l) || 0), C.isFunction(t) && (t = t.call(e, i, a)), null != t.top && (h.top = t.top - a.top + o), null != t.left && (h.left = t.left - a.left + s), "using" in t ? t.using.call(e, h) : u.css(h)
            }
        }, C.fn.extend({
            offset: function(t) {
                if (arguments.length) return void 0 === t ? this : this.each(function(e) {
                    C.offset.setOffset(this, t, e)
                });
                var e, i, n = this[0],
                    s = {
                        top: 0,
                        left: 0
                    },
                    r = n && n.ownerDocument;
                return r ? (e = r.documentElement, C.contains(e, n) ? (typeof n.getBoundingClientRect != U && (s = n.getBoundingClientRect()), i = Nt(r), {
                    top: s.top + i.pageYOffset - e.clientTop,
                    left: s.left + i.pageXOffset - e.clientLeft
                }) : s) : void 0
            },
            position: function() {
                if (this[0]) {
                    var e, t, i = this[0],
                        n = {
                            top: 0,
                            left: 0
                        };
                    return "fixed" === C.css(i, "position") ? t = i.getBoundingClientRect() : (e = this.offsetParent(), t = this.offset(), C.nodeName(e[0], "html") || (n = e.offset()), n.top += C.css(e[0], "borderTopWidth", !0), n.left += C.css(e[0], "borderLeftWidth", !0)), {
                        top: t.top - n.top - C.css(i, "marginTop", !0),
                        left: t.left - n.left - C.css(i, "marginLeft", !0)
                    }
                }
            },
            offsetParent: function() {
                return this.map(function() {
                    for (var e = this.offsetParent || St; e && !C.nodeName(e, "html") && "static" === C.css(e, "position");) e = e.offsetParent;
                    return e || St
                })
            }
        }), C.each({
            scrollLeft: "pageXOffset",
            scrollTop: "pageYOffset"
        }, function(t, s) {
            var r = "pageYOffset" === s;
            C.fn[t] = function(e) {
                return A(this, function(e, t, i) {
                    var n = Nt(e);
                    return void 0 === i ? n ? n[s] : e[t] : void(n ? n.scrollTo(r ? f.pageXOffset : i, r ? i : f.pageYOffset) : e[t] = i)
                }, t, e, arguments.length, null)
            }
        }), C.each(["top", "left"], function(e, i) {
            C.cssHooks[i] = Ce(v.pixelPosition, function(e, t) {
                return t ? (t = _e(e, i), xe.test(t) ? C(e).position()[i] + "px" : t) : void 0
            })
        }), C.each({
            Height: "height",
            Width: "width"
        }, function(r, o) {
            C.each({
                padding: "inner" + r,
                content: o,
                "": "outer" + r
            }, function(n, e) {
                C.fn[e] = function(e, t) {
                    var i = arguments.length && (n || "boolean" != typeof e),
                        s = n || (!0 === e || !0 === t ? "margin" : "border");
                    return A(this, function(e, t, i) {
                        var n;
                        return C.isWindow(e) ? e.document.documentElement["client" + r] : 9 === e.nodeType ? (n = e.documentElement, Math.max(e.body["scroll" + r], n["scroll" + r], e.body["offset" + r], n["offset" + r], n["client" + r])) : void 0 === i ? C.css(e, t, s) : C.style(e, t, i, s)
                    }, o, i ? e : void 0, i, null)
                }
            })
        }), C.fn.size = function() {
            return this.length
        }, C.fn.andSelf = C.fn.addBack, "function" == typeof define && define.amd && define("jquery", [], function() {
            return C
        });
        var qt = f.jQuery,
            At = f.$;
        return C.noConflict = function(e) {
            return f.$ === C && (f.$ = At), e && f.jQuery === C && (f.jQuery = qt), C
        }, typeof e == U && (f.jQuery = f.$ = C), C
    }, "object" == typeof module && "object" == typeof module.exports ? module.exports = e.document ? t(e, !0) : function(e) {
        if (!e.document) throw new Error("jQuery requires a window with a document");
        return t(e)
    } : t(e), E = window.Zepto || window.jQuery, D = window, S = document, H.Defaults = {
        items: 3,
        loop: !1,
        center: !1,
        rewind: !1,
        mouseDrag: !0,
        touchDrag: !0,
        pullDrag: !0,
        freeDrag: !1,
        margin: 0,
        stagePadding: 0,
        merge: !1,
        mergeFit: !0,
        autoWidth: !1,
        startPosition: 0,
        rtl: !1,
        smartSpeed: 250,
        fluidSpeed: !1,
        dragEndSpeed: !1,
        responsive: {},
        responsiveRefreshRate: 200,
        responsiveBaseElement: D,
        fallbackEasing: "swing",
        info: !1,
        nestedItemSelector: !1,
        itemElement: "div",
        stageElement: "div",
        refreshClass: "owl-refresh",
        loadedClass: "owl-loaded",
        loadingClass: "owl-loading",
        rtlClass: "owl-rtl",
        responsiveClass: "owl-responsive",
        dragClass: "owl-drag",
        itemClass: "owl-item",
        stageClass: "owl-stage",
        stageOuterClass: "owl-stage-outer",
        grabClass: "owl-grab"
    }, H.Width = {
        Default: "default",
        Inner: "inner",
        Outer: "outer"
    }, H.Type = {
        Event: "event",
        State: "state"
    }, H.Plugins = {}, H.Workers = [{
        filter: ["width", "settings"],
        run: function() {
            this._width = this.$element.width()
        }
    }, {
        filter: ["width", "items", "settings"],
        run: function(e) {
            e.current = this._items && this._items[this.relative(this._current)]
        }
    }, {
        filter: ["items", "settings"],
        run: function() {
            this.$stage.children(".cloned").remove()
        }
    }, {
        filter: ["width", "items", "settings"],
        run: function(e) {
            var t = this.settings.margin || "",
                i = !this.settings.autoWidth,
                n = this.settings.rtl,
                s = {
                    width: "auto",
                    "margin-left": n ? t : "",
                    "margin-right": n ? "" : t
                };
            i || this.$stage.children().css(s), e.css = s
        }
    }, {
        filter: ["width", "items", "settings"],
        run: function(e) {
            var t = (this.width() / this.settings.items).toFixed(3) - this.settings.margin,
                i = null,
                n = this._items.length,
                s = !this.settings.autoWidth,
                r = [];
            for (e.items = {
                    merge: !1,
                    width: t
                }; n--;) i = this._mergers[n], i = this.settings.mergeFit && Math.min(i, this.settings.items) || i, e.items.merge = 1 < i || e.items.merge, r[n] = s ? t * i : this._items[n].width();
            this._widths = r
        }
    }, {
        filter: ["items", "settings"],
        run: function() {
            var e = [],
                t = this._items,
                i = this.settings,
                n = Math.max(2 * i.items, 4),
                s = 2 * Math.ceil(t.length / 2),
                r = i.loop && t.length ? i.rewind ? n : Math.max(n, s) : 0,
                o = "",
                a = "";
            for (r /= 2; r--;) e.push(this.normalize(e.length / 2, !0)), o += t[e[e.length - 1]][0].outerHTML, e.push(this.normalize(t.length - 1 - (e.length - 1) / 2, !0)), a = t[e[e.length - 1]][0].outerHTML + a;
            this._clones = e, E(o).addClass("cloned").appendTo(this.$stage), E(a).addClass("cloned").prependTo(this.$stage)
        }
    }, {
        filter: ["width", "items", "settings"],
        run: function() {
            for (var e = this.settings.rtl ? 1 : -1, t = this._clones.length + this._items.length, i = -1, n = 0, s = 0, r = []; ++i < t;) n = r[i - 1] || 0, s = this._widths[this.relative(i)] + this.settings.margin, r.push(n + s * e);
            this._coordinates = r
        }
    }, {
        filter: ["width", "items", "settings"],
        run: function() {
            var e = this.settings.stagePadding,
                t = this._coordinates,
                i = {
                    width: Math.ceil(Math.abs(t[t.length - 1])) + 2 * e,
                    "padding-left": e || "",
                    "padding-right": e || ""
                };
            this.$stage.css(i)
        }
    }, {
        filter: ["width", "items", "settings"],
        run: function(e) {
            var t = this._coordinates.length,
                i = !this.settings.autoWidth,
                n = this.$stage.children();
            if (i && e.items.merge)
                for (; t--;) e.css.width = this._widths[this.relative(t)], n.eq(t).css(e.css);
            else i && (e.css.width = e.items.width, n.css(e.css))
        }
    }, {
        filter: ["items"],
        run: function() {
            this._coordinates.length < 1 && this.$stage.removeAttr("style")
        }
    }, {
        filter: ["width", "items", "settings"],
        run: function(e) {
            e.current = e.current ? this.$stage.children().index(e.current) : 0, e.current = Math.max(this.minimum(), Math.min(this.maximum(), e.current)), this.reset(e.current)
        }
    }, {
        filter: ["position"],
        run: function() {
            this.animate(this.coordinates(this._current))
        }
    }, {
        filter: ["width", "position", "items", "settings"],
        run: function() {
            var e, t, i, n, s = this.settings.rtl ? 1 : -1,
                r = 2 * this.settings.stagePadding,
                o = this.coordinates(this.current()) + r,
                a = o + this.width() * s,
                l = [];
            for (i = 0, n = this._coordinates.length; i < n; i++) e = this._coordinates[i - 1] || 0, t = Math.abs(this._coordinates[i]) + r * s, (this.op(e, "<=", o) && this.op(e, ">", a) || this.op(t, "<", o) && this.op(t, ">", a)) && l.push(i);
            this.$stage.children(".active").removeClass("active"), this.$stage.children(":eq(" + l.join("), :eq(") + ")").addClass("active"), this.settings.center && (this.$stage.children(".center").removeClass("center"), this.$stage.children().eq(this.current()).addClass("center"))
        }
    }], H.prototype.initialize = function() {
        var e, t, i;
        this.enter("initializing"), this.trigger("initialize"), this.$element.toggleClass(this.settings.rtlClass, this.settings.rtl), this.settings.autoWidth && !this.is("pre-loading") && (e = this.$element.find("img"), t = this.settings.nestedItemSelector ? "." + this.settings.nestedItemSelector : N, i = this.$element.children(t).width(), e.length && i <= 0 && this.preloadAutoWidthImages(e)), this.$element.addClass(this.options.loadingClass), this.$stage = E("<" + this.settings.stageElement + ' class="' + this.settings.stageClass + '"/>').wrap('<div class="' + this.settings.stageOuterClass + '"/>'), this.$element.append(this.$stage.parent()), this.replace(this.$element.children().not(this.$stage.parent())), this.$element.is(":visible") ? this.refresh() : this.invalidate("width"), this.$element.removeClass(this.options.loadingClass).addClass(this.options.loadedClass), this.registerEventHandlers(), this.leave("initializing"), this.trigger("initialized")
    }, H.prototype.setup = function() {
        var t = this.viewport(),
            e = this.options.responsive,
            i = -1,
            n = null;
        e ? (E.each(e, function(e) {
            e <= t && i < e && (i = Number(e))
        }), "function" == typeof(n = E.extend({}, this.options, e[i])).stagePadding && (n.stagePadding = n.stagePadding()), delete n.responsive, n.responsiveClass && this.$element.attr("class", this.$element.attr("class").replace(new RegExp("(" + this.options.responsiveClass + "-)\\S+\\s", "g"), "$1" + i))) : n = E.extend({}, this.options), this.trigger("change", {
            property: {
                name: "settings",
                value: n
            }
        }), this._breakpoint = i, this.settings = n, this.invalidate("settings"), this.trigger("changed", {
            property: {
                name: "settings",
                value: this.settings
            }
        })
    }, H.prototype.optionsLogic = function() {
        this.settings.autoWidth && (this.settings.stagePadding = !1, this.settings.merge = !1)
    }, H.prototype.prepare = function(e) {
        var t = this.trigger("prepare", {
            content: e
        });
        return t.data || (t.data = E("<" + this.settings.itemElement + "/>").addClass(this.options.itemClass).append(e)), this.trigger("prepared", {
            content: t.data
        }), t.data
    }, H.prototype.update = function() {
        for (var e = 0, t = this._pipe.length, i = E.proxy(function(e) {
                return this[e]
            }, this._invalidated), n = {}; e < t;)(this._invalidated.all || 0 < E.grep(this._pipe[e].filter, i).length) && this._pipe[e].run(n), e++;
        this._invalidated = {}, this.is("valid") || this.enter("valid")
    }, H.prototype.width = function(e) {
        switch (e = e || H.Width.Default) {
            case H.Width.Inner:
            case H.Width.Outer:
                return this._width;
            default:
                return this._width - 2 * this.settings.stagePadding + this.settings.margin
        }
    }, H.prototype.refresh = function() {
        this.enter("refreshing"), this.trigger("refresh"), this.setup(), this.optionsLogic(), this.$element.addClass(this.options.refreshClass), this.update(), this.$element.removeClass(this.options.refreshClass), this.leave("refreshing"), this.trigger("refreshed")
    }, H.prototype.onThrottledResize = function() {
        D.clearTimeout(this.resizeTimer), this.resizeTimer = D.setTimeout(this._handlers.onResize, this.settings.responsiveRefreshRate)
    }, H.prototype.onResize = function() {
        return !!this._items.length && this._width !== this.$element.width() && !!this.$element.is(":visible") && (this.enter("resizing"), this.trigger("resize").isDefaultPrevented() ? (this.leave("resizing"), !1) : (this.invalidate("width"), this.refresh(), this.leave("resizing"), void this.trigger("resized")))
    }, H.prototype.registerEventHandlers = function() {
        E.support.transition && this.$stage.on(E.support.transition.end + ".owl.core", E.proxy(this.onTransitionEnd, this)), !1 !== this.settings.responsive && this.on(D, "resize", this._handlers.onThrottledResize), this.settings.mouseDrag && (this.$element.addClass(this.options.dragClass), this.$stage.on("mousedown.owl.core", E.proxy(this.onDragStart, this)), this.$stage.on("dragstart.owl.core selectstart.owl.core", function() {
            return !1
        })), this.settings.touchDrag && (this.$stage.on("touchstart.owl.core", E.proxy(this.onDragStart, this)), this.$stage.on("touchcancel.owl.core", E.proxy(this.onDragEnd, this)))
    }, H.prototype.onDragStart = function(e) {
        var t = null;
        3 !== e.which && (t = E.support.transform ? {
            x: (t = this.$stage.css("transform").replace(/.*\(|\)| /g, "").split(","))[16 === t.length ? 12 : 4],
            y: t[16 === t.length ? 13 : 5]
        } : (t = this.$stage.position(), {
            x: this.settings.rtl ? t.left + this.$stage.width() - this.width() + this.settings.margin : t.left,
            y: t.top
        }), this.is("animating") && (E.support.transform ? this.animate(t.x) : this.$stage.stop(), this.invalidate("position")), this.$element.toggleClass(this.options.grabClass, "mousedown" === e.type), this.speed(0), this._drag.time = (new Date).getTime(), this._drag.target = E(e.target), this._drag.stage.start = t, this._drag.stage.current = t, this._drag.pointer = this.pointer(e), E(S).on("mouseup.owl.core touchend.owl.core", E.proxy(this.onDragEnd, this)), E(S).one("mousemove.owl.core touchmove.owl.core", E.proxy(function(e) {
            var t = this.difference(this._drag.pointer, this.pointer(e));
            E(S).on("mousemove.owl.core touchmove.owl.core", E.proxy(this.onDragMove, this)), Math.abs(t.x) < Math.abs(t.y) && this.is("valid") || (e.preventDefault(), this.enter("dragging"), this.trigger("drag"))
        }, this)))
    }, H.prototype.onDragMove = function(e) {
        var t = null,
            i = null,
            n = null,
            s = this.difference(this._drag.pointer, this.pointer(e)),
            r = this.difference(this._drag.stage.start, s);
        this.is("dragging") && (e.preventDefault(), this.settings.loop ? (t = this.coordinates(this.minimum()), i = this.coordinates(this.maximum() + 1) - t, r.x = ((r.x - t) % i + i) % i + t) : (t = this.settings.rtl ? this.coordinates(this.maximum()) : this.coordinates(this.minimum()), i = this.settings.rtl ? this.coordinates(this.minimum()) : this.coordinates(this.maximum()), n = this.settings.pullDrag ? -1 * s.x / 5 : 0, r.x = Math.max(Math.min(r.x, t + n), i + n)), this._drag.stage.current = r, this.animate(r.x))
    }, H.prototype.onDragEnd = function(e) {
        var t = this.difference(this._drag.pointer, this.pointer(e)),
            i = this._drag.stage.current,
            n = 0 < t.x ^ this.settings.rtl ? "left" : "right";
        E(S).off(".owl.core"), this.$element.removeClass(this.options.grabClass), (0 !== t.x && this.is("dragging") || !this.is("valid")) && (this.speed(this.settings.dragEndSpeed || this.settings.smartSpeed), this.current(this.closest(i.x, 0 !== t.x ? n : this._drag.direction)), this.invalidate("position"), this.update(), this._drag.direction = n, (3 < Math.abs(t.x) || 300 < (new Date).getTime() - this._drag.time) && this._drag.target.one("click.owl.core", function() {
            return !1
        })), this.is("dragging") && (this.leave("dragging"), this.trigger("dragged"))
    }, H.prototype.closest = function(i, n) {
        var s = -1,
            r = this.width(),
            o = this.coordinates();
        return this.settings.freeDrag || E.each(o, E.proxy(function(e, t) {
            return "left" === n && t - 30 < i && i < t + 30 ? s = e : "right" === n && t - r - 30 < i && i < t - r + 30 ? s = e + 1 : this.op(i, "<", t) && this.op(i, ">", o[e + 1] || t - r) && (s = "left" === n ? e + 1 : e), -1 === s
        }, this)), this.settings.loop || (this.op(i, ">", o[this.minimum()]) ? s = i = this.minimum() : this.op(i, "<", o[this.maximum()]) && (s = i = this.maximum())), s
    }, H.prototype.animate = function(e) {
        var t = 0 < this.speed();
        this.is("animating") && this.onTransitionEnd(), t && (this.enter("animating"), this.trigger("translate")), E.support.transform3d && E.support.transition ? this.$stage.css({
            transform: "translate3d(" + e + "px,0px,0px)",
            transition: this.speed() / 1e3 + "s"
        }) : t ? this.$stage.animate({
            left: e + "px"
        }, this.speed(), this.settings.fallbackEasing, E.proxy(this.onTransitionEnd, this)) : this.$stage.css({
            left: e + "px"
        })
    }, H.prototype.is = function(e) {
        return this._states.current[e] && 0 < this._states.current[e]
    }, H.prototype.current = function(e) {
        if (e === N) return this._current;
        if (0 === this._items.length) return N;
        if (e = this.normalize(e), this._current !== e) {
            var t = this.trigger("change", {
                property: {
                    name: "position",
                    value: e
                }
            });
            t.data !== N && (e = this.normalize(t.data)), this._current = e, this.invalidate("position"), this.trigger("changed", {
                property: {
                    name: "position",
                    value: this._current
                }
            })
        }
        return this._current
    }, H.prototype.invalidate = function(e) {
        return "string" === E.type(e) && (this._invalidated[e] = !0, this.is("valid") && this.leave("valid")), E.map(this._invalidated, function(e, t) {
            return t
        })
    }, H.prototype.reset = function(e) {
        (e = this.normalize(e)) !== N && (this._speed = 0, this._current = e, this.suppress(["translate", "translated"]), this.animate(this.coordinates(e)), this.release(["translate", "translated"]))
    }, H.prototype.normalize = function(e, t) {
        var i = this._items.length,
            n = t ? 0 : this._clones.length;
        return !this.isNumeric(e) || i < 1 ? e = N : (e < 0 || i + n <= e) && (e = ((e - n / 2) % i + i) % i + n / 2), e
    }, H.prototype.relative = function(e) {
        return e -= this._clones.length / 2, this.normalize(e, !0)
    }, H.prototype.maximum = function(e) {
        var t, i, n, s = this.settings,
            r = this._coordinates.length;
        if (s.loop) r = this._clones.length / 2 + this._items.length - 1;
        else if (s.autoWidth || s.merge) {
            for (t = this._items.length, i = this._items[--t].width(), n = this.$element.width(); t-- && !(n < (i += this._items[t].width() + this.settings.margin)););
            r = t + 1
        } else r = s.center ? this._items.length - 1 : this._items.length - s.items;
        return e && (r -= this._clones.length / 2), Math.max(r, 0)
    }, H.prototype.minimum = function(e) {
        return e ? 0 : this._clones.length / 2
    }, H.prototype.items = function(e) {
        return e === N ? this._items.slice() : (e = this.normalize(e, !0), this._items[e])
    }, H.prototype.mergers = function(e) {
        return e === N ? this._mergers.slice() : (e = this.normalize(e, !0), this._mergers[e])
    }, H.prototype.clones = function(i) {
        function n(e) {
            return e % 2 == 0 ? s + e / 2 : t - (e + 1) / 2
        }
        var t = this._clones.length / 2,
            s = t + this._items.length;
        return i === N ? E.map(this._clones, function(e, t) {
            return n(t)
        }) : E.map(this._clones, function(e, t) {
            return e === i ? n(t) : null
        })
    }, H.prototype.speed = function(e) {
        return e !== N && (this._speed = e), this._speed
    }, H.prototype.coordinates = function(e) {
        var t, i = 1,
            n = e - 1;
        return e === N ? E.map(this._coordinates, E.proxy(function(e, t) {
            return this.coordinates(t)
        }, this)) : (this.settings.center ? (this.settings.rtl && (i = -1, n = e + 1), t = this._coordinates[e], t += (this.width() - t + (this._coordinates[n] || 0)) / 2 * i) : t = this._coordinates[n] || 0, t = Math.ceil(t))
    }, H.prototype.duration = function(e, t, i) {
        return 0 === i ? 0 : Math.min(Math.max(Math.abs(t - e), 1), 6) * Math.abs(i || this.settings.smartSpeed)
    }, H.prototype.to = function(e, t) {
        var i = this.current(),
            n = null,
            s = e - this.relative(i),
            r = (0 < s) - (s < 0),
            o = this._items.length,
            a = this.minimum(),
            l = this.maximum();
        this.settings.loop ? (!this.settings.rewind && Math.abs(s) > o / 2 && (s += -1 * r * o), (n = (((e = i + s) - a) % o + o) % o + a) !== e && n - s <= l && 0 < n - s && (i = n - s, e = n, this.reset(i))) : e = this.settings.rewind ? (e % (l += 1) + l) % l : Math.max(a, Math.min(l, e)), this.speed(this.duration(i, e, t)), this.current(e), this.$element.is(":visible") && this.update()
    }, H.prototype.next = function(e) {
        e = e || !1, this.to(this.relative(this.current()) + 1, e)
    }, H.prototype.prev = function(e) {
        e = e || !1, this.to(this.relative(this.current()) - 1, e)
    }, H.prototype.onTransitionEnd = function(e) {
        if (e !== N && (e.stopPropagation(), (e.target || e.srcElement || e.originalTarget) !== this.$stage.get(0))) return !1;
        this.leave("animating"), this.trigger("translated")
    }, H.prototype.viewport = function() {
        var e;
        return this.options.responsiveBaseElement !== D ? e = E(this.options.responsiveBaseElement).width() : D.innerWidth ? e = D.innerWidth : S.documentElement && S.documentElement.clientWidth ? e = S.documentElement.clientWidth : console.warn("Can not detect viewport width."), e
    }, H.prototype.replace = function(e) {
        this.$stage.empty(), this._items = [], e = e && (e instanceof jQuery ? e : E(e)), this.settings.nestedItemSelector && (e = e.find("." + this.settings.nestedItemSelector)), e.filter(function() {
            return 1 === this.nodeType
        }).each(E.proxy(function(e, t) {
            t = this.prepare(t), this.$stage.append(t), this._items.push(t), this._mergers.push(1 * t.find("[data-merge]").addBack("[data-merge]").attr("data-merge") || 1)
        }, this)), this.reset(this.isNumeric(this.settings.startPosition) ? this.settings.startPosition : 0), this.invalidate("items")
    }, H.prototype.add = function(e, t) {
        var i = this.relative(this._current);
        t = t === N ? this._items.length : this.normalize(t, !0), e = e instanceof jQuery ? e : E(e), this.trigger("add", {
            content: e,
            position: t
        }), e = this.prepare(e), 0 === this._items.length || t === this._items.length ? (0 === this._items.length && this.$stage.append(e), 0 !== this._items.length && this._items[t - 1].after(e), this._items.push(e), this._mergers.push(1 * e.find("[data-merge]").addBack("[data-merge]").attr("data-merge") || 1)) : (this._items[t].before(e), this._items.splice(t, 0, e), this._mergers.splice(t, 0, 1 * e.find("[data-merge]").addBack("[data-merge]").attr("data-merge") || 1)), this._items[i] && this.reset(this._items[i].index()), this.invalidate("items"), this.trigger("added", {
            content: e,
            position: t
        })
    }, H.prototype.remove = function(e) {
        (e = this.normalize(e, !0)) !== N && (this.trigger("remove", {
            content: this._items[e],
            position: e
        }), this._items[e].remove(), this._items.splice(e, 1), this._mergers.splice(e, 1), this.invalidate("items"), this.trigger("removed", {
            content: null,
            position: e
        }))
    }, H.prototype.preloadAutoWidthImages = function(e) {
        e.each(E.proxy(function(e, t) {
            this.enter("pre-loading"), t = E(t), E(new Image).one("load", E.proxy(function(e) {
                t.attr("src", e.target.src), t.css("opacity", 1), this.leave("pre-loading"), this.is("pre-loading") || this.is("initializing") || this.refresh()
            }, this)).attr("src", t.attr("src") || t.attr("data-src") || t.attr("data-src-retina"))
        }, this))
    }, H.prototype.destroy = function() {
        for (var e in this.$element.off(".owl.core"), this.$stage.off(".owl.core"), E(S).off(".owl.core"), !1 !== this.settings.responsive && (D.clearTimeout(this.resizeTimer), this.off(D, "resize", this._handlers.onThrottledResize)), this._plugins) this._plugins[e].destroy();
        this.$stage.children(".cloned").remove(), this.$stage.unwrap(), this.$stage.children().contents().unwrap(), this.$stage.children().unwrap(), this.$element.removeClass(this.options.refreshClass).removeClass(this.options.loadingClass).removeClass(this.options.loadedClass).removeClass(this.options.rtlClass).removeClass(this.options.dragClass).removeClass(this.options.grabClass).attr("class", this.$element.attr("class").replace(new RegExp(this.options.responsiveClass + "-\\S+\\s", "g"), "")).removeData("owl.carousel")
    }, H.prototype.op = function(e, t, i) {
        var n = this.settings.rtl;
        switch (t) {
            case "<":
                return n ? i < e : e < i;
            case ">":
                return n ? e < i : i < e;
            case ">=":
                return n ? e <= i : i <= e;
            case "<=":
                return n ? i <= e : e <= i
        }
    }, H.prototype.on = function(e, t, i, n) {
        e.addEventListener ? e.addEventListener(t, i, n) : e.attachEvent && e.attachEvent("on" + t, i)
    }, H.prototype.off = function(e, t, i, n) {
        e.removeEventListener ? e.removeEventListener(t, i, n) : e.detachEvent && e.detachEvent("on" + t, i)
    }, H.prototype.trigger = function(e, t, i, n, s) {
        var r = {
                item: {
                    count: this._items.length,
                    index: this.current()
                }
            },
            o = E.camelCase(E.grep(["on", e, i], function(e) {
                return e
            }).join("-").toLowerCase()),
            a = E.Event([e, "owl", i || "carousel"].join(".").toLowerCase(), E.extend({
                relatedTarget: this
            }, r, t));
        return this._supress[e] || (E.each(this._plugins, function(e, t) {
            t.onTrigger && t.onTrigger(a)
        }), this.register({
            type: H.Type.Event,
            name: e
        }), this.$element.trigger(a), this.settings && "function" == typeof this.settings[o] && this.settings[o].call(this, a)), a
    }, H.prototype.enter = function(e) {
        E.each([e].concat(this._states.tags[e] || []), E.proxy(function(e, t) {
            this._states.current[t] === N && (this._states.current[t] = 0), this._states.current[t]++
        }, this))
    }, H.prototype.leave = function(e) {
        E.each([e].concat(this._states.tags[e] || []), E.proxy(function(e, t) {
            this._states.current[t]--
        }, this))
    }, H.prototype.register = function(i) {
        if (i.type === H.Type.Event) {
            if (E.event.special[i.name] || (E.event.special[i.name] = {}), !E.event.special[i.name].owl) {
                var t = E.event.special[i.name]._default;
                E.event.special[i.name]._default = function(e) {
                    return !t || !t.apply || e.namespace && -1 !== e.namespace.indexOf("owl") ? e.namespace && -1 < e.namespace.indexOf("owl") : t.apply(this, arguments)
                }, E.event.special[i.name].owl = !0
            }
        } else i.type === H.Type.State && (this._states.tags[i.name] ? this._states.tags[i.name] = this._states.tags[i.name].concat(i.tags) : this._states.tags[i.name] = i.tags, this._states.tags[i.name] = E.grep(this._states.tags[i.name], E.proxy(function(e, t) {
            return E.inArray(e, this._states.tags[i.name]) === t
        }, this)))
    }, H.prototype.suppress = function(e) {
        E.each(e, E.proxy(function(e, t) {
            this._supress[t] = !0
        }, this))
    }, H.prototype.release = function(e) {
        E.each(e, E.proxy(function(e, t) {
            delete this._supress[t]
        }, this))
    }, H.prototype.pointer = function(e) {
        var t = {
            x: null,
            y: null
        };
        return (e = (e = e.originalEvent || e || D.event).touches && e.touches.length ? e.touches[0] : e.changedTouches && e.changedTouches.length ? e.changedTouches[0] : e).pageX ? (t.x = e.pageX, t.y = e.pageY) : (t.x = e.clientX, t.y = e.clientY), t
    }, H.prototype.isNumeric = function(e) {
        return !isNaN(parseFloat(e))
    }, H.prototype.difference = function(e, t) {
        return {
            x: e.x - t.x,
            y: e.y - t.y
        }
    }, E.fn.owlCarousel = function(t) {
        var n = Array.prototype.slice.call(arguments, 1);
        return this.each(function() {
            var e = E(this),
                i = e.data("owl.carousel");
            i || (i = new H(this, "object" == typeof t && t), e.data("owl.carousel", i), E.each(["next", "prev", "to", "destroy", "refresh", "replace", "add", "remove"], function(e, t) {
                i.register({
                    type: H.Type.Event,
                    name: t
                }), i.$element.on(t + ".owl.carousel.core", E.proxy(function(e) {
                    e.namespace && e.relatedTarget !== this && (this.suppress([t]), i[t].apply(this, [].slice.call(arguments, 1)), this.release([t]))
                }, i))
            })), "string" == typeof t && "_" !== t.charAt(0) && i[t].apply(i, n)
        })
    }, E.fn.owlCarousel.Constructor = H, T = window.Zepto || window.jQuery, k = window, document, (j = function(e) {
        this._core = e, this._interval = null, this._visible = null, this._handlers = {
            "initialized.owl.carousel": T.proxy(function(e) {
                e.namespace && this._core.settings.autoRefresh && this.watch()
            }, this)
        }, this._core.options = T.extend({}, j.Defaults, this._core.options), this._core.$element.on(this._handlers)
    }).Defaults = {
        autoRefresh: !0,
        autoRefreshInterval: 500
    }, j.prototype.watch = function() {
        this._interval || (this._visible = this._core.$element.is(":visible"), this._interval = k.setInterval(T.proxy(this.refresh, this), this._core.settings.autoRefreshInterval))
    }, j.prototype.refresh = function() {
        this._core.$element.is(":visible") !== this._visible && (this._visible = !this._visible, this._core.$element.toggleClass("owl-hidden", !this._visible), this._visible && this._core.invalidate("width") && this._core.refresh())
    }, j.prototype.destroy = function() {
        var e, t;
        for (e in k.clearInterval(this._interval), this._handlers) this._core.$element.off(e, this._handlers[e]);
        for (t in Object.getOwnPropertyNames(this)) "function" != typeof this[t] && (this[t] = null)
    }, T.fn.owlCarousel.Constructor.Plugins.AutoRefresh = j, b = window.Zepto || window.jQuery, _ = window, document, (C = function(e) {
        this._core = e, this._loaded = [], this._handlers = {
            "initialized.owl.carousel change.owl.carousel resized.owl.carousel": b.proxy(function(e) {
                if (e.namespace && this._core.settings && this._core.settings.lazyLoad && (e.property && "position" == e.property.name || "initialized" == e.type))
                    for (var t = this._core.settings, i = t.center && Math.ceil(t.items / 2) || t.items, n = t.center && -1 * i || 0, s = (e.property && void 0 !== e.property.value ? e.property.value : this._core.current()) + n, r = this._core.clones().length, o = b.proxy(function(e, t) {
                            this.load(t)
                        }, this); n++ < i;) this.load(r / 2 + this._core.relative(s)), r && b.each(this._core.clones(this._core.relative(s)), o), s++
            }, this)
        }, this._core.options = b.extend({}, C.Defaults, this._core.options), this._core.$element.on(this._handlers)
    }).Defaults = {
        lazyLoad: !1
    }, C.prototype.load = function(e) {
        var t = this._core.$stage.children().eq(e),
            i = t && t.find(".owl-lazy");
        !i || -1 < b.inArray(t.get(0), this._loaded) || (i.each(b.proxy(function(e, t) {
            var i, n = b(t),
                s = 1 < _.devicePixelRatio && n.attr("data-src-retina") || n.attr("data-src");
            this._core.trigger("load", {
                element: n,
                url: s
            }, "lazy"), n.is("img") ? n.one("load.owl.lazy", b.proxy(function() {
                n.css("opacity", 1), this._core.trigger("loaded", {
                    element: n,
                    url: s
                }, "lazy")
            }, this)).attr("src", s) : ((i = new Image).onload = b.proxy(function() {
                n.css({
                    "background-image": 'url("' + s + '")',
                    opacity: "1"
                }), this._core.trigger("loaded", {
                    element: n,
                    url: s
                }, "lazy")
            }, this), i.src = s)
        }, this)), this._loaded.push(t.get(0)))
    }, C.prototype.destroy = function() {
        var e, t;
        for (e in this.handlers) this._core.$element.off(e, this.handlers[e]);
        for (t in Object.getOwnPropertyNames(this)) "function" != typeof this[t] && (this[t] = null)
    }, b.fn.owlCarousel.Constructor.Plugins.Lazy = C, w = window.Zepto || window.jQuery, window, document, (x = function(e) {
        this._core = e, this._handlers = {
            "initialized.owl.carousel refreshed.owl.carousel": w.proxy(function(e) {
                e.namespace && this._core.settings.autoHeight && this.update()
            }, this),
            "changed.owl.carousel": w.proxy(function(e) {
                e.namespace && this._core.settings.autoHeight && "position" == e.property.name && this.update()
            }, this),
            "loaded.owl.lazy": w.proxy(function(e) {
                e.namespace && this._core.settings.autoHeight && e.element.closest("." + this._core.settings.itemClass).index() === this._core.current() && this.update()
            }, this)
        }, this._core.options = w.extend({}, x.Defaults, this._core.options), this._core.$element.on(this._handlers)
    }).Defaults = {
        autoHeight: !1,
        autoHeightClass: "owl-height"
    }, x.prototype.update = function() {
        var e, t = this._core._current,
            i = t + this._core.settings.items,
            n = this._core.$stage.children().toArray().slice(t, i),
            s = [];
        w.each(n, function(e, t) {
            s.push(w(t).height())
        }), e = Math.max.apply(null, s), this._core.$stage.parent().height(e).addClass(this._core.settings.autoHeightClass)
    }, x.prototype.destroy = function() {
        var e, t;
        for (e in this._handlers) this._core.$element.off(e, this._handlers[e]);
        for (t in Object.getOwnPropertyNames(this)) "function" != typeof this[t] && (this[t] = null)
    }, w.fn.owlCarousel.Constructor.Plugins.AutoHeight = x, m = window.Zepto || window.jQuery, window, v = document, (y = function(e) {
        this._core = e, this._videos = {}, this._playing = null, this._handlers = {
            "initialized.owl.carousel": m.proxy(function(e) {
                e.namespace && this._core.register({
                    type: "state",
                    name: "playing",
                    tags: ["interacting"]
                })
            }, this),
            "resize.owl.carousel": m.proxy(function(e) {
                e.namespace && this._core.settings.video && this.isInFullScreen() && e.preventDefault()
            }, this),
            "refreshed.owl.carousel": m.proxy(function(e) {
                e.namespace && this._core.is("resizing") && this._core.$stage.find(".cloned .owl-video-frame").remove()
            }, this),
            "changed.owl.carousel": m.proxy(function(e) {
                e.namespace && "position" === e.property.name && this._playing && this.stop()
            }, this),
            "prepared.owl.carousel": m.proxy(function(e) {
                if (e.namespace) {
                    var t = m(e.content).find(".owl-video");
                    t.length && (t.css("display", "none"), this.fetch(t, m(e.content)))
                }
            }, this)
        }, this._core.options = m.extend({}, y.Defaults, this._core.options), this._core.$element.on(this._handlers), this._core.$element.on("click.owl.video", ".owl-video-play-icon", m.proxy(function(e) {
            this.play(e)
        }, this))
    }).Defaults = {
        video: !1,
        videoHeight: !1,
        videoWidth: !1
    }, y.prototype.fetch = function(e, t) {
        var i = e.attr("data-vimeo-id") ? "vimeo" : e.attr("data-vzaar-id") ? "vzaar" : "youtube",
            n = e.attr("data-vimeo-id") || e.attr("data-youtube-id") || e.attr("data-vzaar-id"),
            s = e.attr("data-width") || this._core.settings.videoWidth,
            r = e.attr("data-height") || this._core.settings.videoHeight,
            o = e.attr("href");
        if (!o) throw new Error("Missing video URL.");
        if (-1 < (n = o.match(/(http:|https:|)\/\/(player.|www.|app.)?(vimeo\.com|youtu(be\.com|\.be|be\.googleapis\.com)|vzaar\.com)\/(video\/|videos\/|embed\/|channels\/.+\/|groups\/.+\/|watch\?v=|v\/)?([A-Za-z0-9._%-]*)(\&\S+)?/))[3].indexOf("youtu")) i = "youtube";
        else if (-1 < n[3].indexOf("vimeo")) i = "vimeo";
        else {
            if (!(-1 < n[3].indexOf("vzaar"))) throw new Error("Video URL not supported.");
            i = "vzaar"
        }
        n = n[6], this._videos[o] = {
            type: i,
            id: n,
            width: s,
            height: r
        }, t.attr("data-video", o), this.thumbnail(e, this._videos[o])
    }, y.prototype.thumbnail = function(t, e) {
        function i(e) {
            n = c.lazyLoad ? '<div class="owl-video-tn ' + l + '" ' + a + '="' + e + '"></div>' : '<div class="owl-video-tn" style="opacity:1;background-image:url(' + e + ')"></div>', t.after(n), t.after('<div class="owl-video-play-icon"></div>')
        }
        var n, s, r = e.width && e.height ? 'style="width:' + e.width + "px;height:" + e.height + 'px;"' : "",
            o = t.find("img"),
            a = "src",
            l = "",
            c = this._core.settings;
        if (t.wrap('<div class="owl-video-wrapper"' + r + "></div>"), this._core.settings.lazyLoad && (a = "data-src", l = "owl-lazy"), o.length) return i(o.attr(a)), o.remove(), !1;
        "youtube" === e.type ? (s = "//img.youtube.com/vi/" + e.id + "/hqdefault.jpg", i(s)) : "vimeo" === e.type ? m.ajax({
            type: "GET",
            url: "//vimeo.com/api/v2/video/" + e.id + ".json",
            jsonp: "callback",
            dataType: "jsonp",
            success: function(e) {
                s = e[0].thumbnail_large, i(s)
            }
        }) : "vzaar" === e.type && m.ajax({
            type: "GET",
            url: "//vzaar.com/api/videos/" + e.id + ".json",
            jsonp: "callback",
            dataType: "jsonp",
            success: function(e) {
                s = e.framegrab_url, i(s)
            }
        })
    }, y.prototype.stop = function() {
        this._core.trigger("stop", null, "video"), this._playing.find(".owl-video-frame").remove(), this._playing.removeClass("owl-video-playing"), this._playing = null, this._core.leave("playing"), this._core.trigger("stopped", null, "video")
    }, y.prototype.play = function(e) {
        var t, i = m(e.target).closest("." + this._core.settings.itemClass),
            n = this._videos[i.attr("data-video")],
            s = n.width || "100%",
            r = n.height || this._core.$stage.height();
        this._playing || (this._core.enter("playing"), this._core.trigger("play", null, "video"), i = this._core.items(this._core.relative(i.index())), this._core.reset(i.index()), "youtube" === n.type ? t = '<iframe width="' + s + '" height="' + r + '" src="//www.youtube.com/embed/' + n.id + "?autoplay=1&rel=0&v=" + n.id + '" frameborder="0" allowfullscreen></iframe>' : "vimeo" === n.type ? t = '<iframe src="//player.vimeo.com/video/' + n.id + '?autoplay=1" width="' + s + '" height="' + r + '" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>' : "vzaar" === n.type && (t = '<iframe frameborder="0"height="' + r + '"width="' + s + '" allowfullscreen mozallowfullscreen webkitAllowFullScreen src="//view.vzaar.com/' + n.id + '/player?autoplay=true"></iframe>'), m('<div class="owl-video-frame">' + t + "</div>").insertAfter(i.find(".owl-video")), this._playing = i.addClass("owl-video-playing"))
    }, y.prototype.isInFullScreen = function() {
        var e = v.fullscreenElement || v.mozFullScreenElement || v.webkitFullscreenElement;
        return e && m(e).parent().hasClass("owl-video-frame")
    }, y.prototype.destroy = function() {
        var e, t;
        for (e in this._core.$element.off("click.owl.video"), this._handlers) this._core.$element.off(e, this._handlers[e]);
        for (t in Object.getOwnPropertyNames(this)) "function" != typeof this[t] && (this[t] = null)
    }, m.fn.owlCarousel.Constructor.Plugins.Video = y, f = window.Zepto || window.jQuery, window, document, (g = function(e) {
        this.core = e, this.core.options = f.extend({}, g.Defaults, this.core.options), this.swapping = !0, this.previous = void 0, this.next = void 0, this.handlers = {
            "change.owl.carousel": f.proxy(function(e) {
                e.namespace && "position" == e.property.name && (this.previous = this.core.current(), this.next = e.property.value)
            }, this),
            "drag.owl.carousel dragged.owl.carousel translated.owl.carousel": f.proxy(function(e) {
                e.namespace && (this.swapping = "translated" == e.type)
            }, this),
            "translate.owl.carousel": f.proxy(function(e) {
                e.namespace && this.swapping && (this.core.options.animateOut || this.core.options.animateIn) && this.swap()
            }, this)
        }, this.core.$element.on(this.handlers)
    }).Defaults = {
        animateOut: !1,
        animateIn: !1
    }, g.prototype.swap = function() {
        if (1 === this.core.settings.items && f.support.animation && f.support.transition) {
            this.core.speed(0);
            var e, t = f.proxy(this.clear, this),
                i = this.core.$stage.children().eq(this.previous),
                n = this.core.$stage.children().eq(this.next),
                s = this.core.settings.animateIn,
                r = this.core.settings.animateOut;
            this.core.current() !== this.previous && (r && (e = this.core.coordinates(this.previous) - this.core.coordinates(this.next), i.one(f.support.animation.end, t).css({
                left: e + "px"
            }).addClass("animated owl-animated-out").addClass(r)), s && n.one(f.support.animation.end, t).addClass("animated owl-animated-in").addClass(s))
        }
    }, g.prototype.clear = function(e) {
        f(e.target).css({
            left: ""
        }).removeClass("animated owl-animated-out owl-animated-in").removeClass(this.core.settings.animateIn).removeClass(this.core.settings.animateOut), this.core.onTransitionEnd()
    }, g.prototype.destroy = function() {
        var e, t;
        for (e in this.handlers) this.core.$element.off(e, this.handlers[e]);
        for (t in Object.getOwnPropertyNames(this)) "function" != typeof this[t] && (this[t] = null)
    }, f.fn.owlCarousel.Constructor.Plugins.Animate = g, u = window.Zepto || window.jQuery, h = window, d = document, (p = function(e) {
        this._core = e, this._timeout = null, this._paused = !1, this._handlers = {
            "changed.owl.carousel": u.proxy(function(e) {
                e.namespace && "settings" === e.property.name ? this._core.settings.autoplay ? this.play() : this.stop() : e.namespace && "position" === e.property.name && this._core.settings.autoplay && this._setAutoPlayInterval()
            }, this),
            "initialized.owl.carousel": u.proxy(function(e) {
                e.namespace && this._core.settings.autoplay && this.play()
            }, this),
            "play.owl.autoplay": u.proxy(function(e, t, i) {
                e.namespace && this.play(t, i)
            }, this),
            "stop.owl.autoplay": u.proxy(function(e) {
                e.namespace && this.stop()
            }, this),
            "mouseover.owl.autoplay": u.proxy(function() {
                this._core.settings.autoplayHoverPause && this._core.is("rotating") && this.pause()
            }, this),
            "mouseleave.owl.autoplay": u.proxy(function() {
                this._core.settings.autoplayHoverPause && this._core.is("rotating") && this.play()
            }, this),
            "touchstart.owl.core": u.proxy(function() {
                this._core.settings.autoplayHoverPause && this._core.is("rotating") && this.pause()
            }, this),
            "touchend.owl.core": u.proxy(function() {
                this._core.settings.autoplayHoverPause && this.play()
            }, this)
        }, this._core.$element.on(this._handlers), this._core.options = u.extend({}, p.Defaults, this._core.options)
    }).Defaults = {
        autoplay: !1,
        autoplayTimeout: 5e3,
        autoplayHoverPause: !1,
        autoplaySpeed: !1
    }, p.prototype.play = function(e, t) {
        this._paused = !1, this._core.is("rotating") || (this._core.enter("rotating"), this._setAutoPlayInterval())
    }, p.prototype._getNextTimeout = function(e, t) {
        return this._timeout && h.clearTimeout(this._timeout), h.setTimeout(u.proxy(function() {
            this._paused || this._core.is("busy") || this._core.is("interacting") || d.hidden || this._core.next(t || this._core.settings.autoplaySpeed)
        }, this), e || this._core.settings.autoplayTimeout)
    }, p.prototype._setAutoPlayInterval = function() {
        this._timeout = this._getNextTimeout()
    }, p.prototype.stop = function() {
        this._core.is("rotating") && (h.clearTimeout(this._timeout), this._core.leave("rotating"))
    }, p.prototype.pause = function() {
        this._core.is("rotating") && (this._paused = !0)
    }, p.prototype.destroy = function() {
        var e, t;
        for (e in this.stop(), this._handlers) this._core.$element.off(e, this._handlers[e]);
        for (t in Object.getOwnPropertyNames(this)) "function" != typeof this[t] && (this[t] = null)
    }, u.fn.owlCarousel.Constructor.Plugins.autoplay = p,
    function(r) {
        "use strict";
        var t = function(e) {
            this._core = e, this._initialized = !1, this._pages = [], this._controls = {}, this._templates = [], this.$element = this._core.$element, this._overrides = {
                next: this._core.next,
                prev: this._core.prev,
                to: this._core.to
            }, this._handlers = {
                "prepared.owl.carousel": r.proxy(function(e) {
                    e.namespace && this._core.settings.dotsData && this._templates.push('<div class="' + this._core.settings.dotClass + '">' + r(e.content).find("[data-dot]").addBack("[data-dot]").attr("data-dot") + "</div>")
                }, this),
                "added.owl.carousel": r.proxy(function(e) {
                    e.namespace && this._core.settings.dotsData && this._templates.splice(e.position, 0, this._templates.pop())
                }, this),
                "remove.owl.carousel": r.proxy(function(e) {
                    e.namespace && this._core.settings.dotsData && this._templates.splice(e.position, 1)
                }, this),
                "changed.owl.carousel": r.proxy(function(e) {
                    e.namespace && "position" == e.property.name && this.draw()
                }, this),
                "initialized.owl.carousel": r.proxy(function(e) {
                    e.namespace && !this._initialized && (this._core.trigger("initialize", null, "navigation"), this.initialize(), this.update(), this.draw(), this._initialized = !0, this._core.trigger("initialized", null, "navigation"))
                }, this),
                "refreshed.owl.carousel": r.proxy(function(e) {
                    e.namespace && this._initialized && (this._core.trigger("refresh", null, "navigation"), this.update(), this.draw(), this._core.trigger("refreshed", null, "navigation"))
                }, this)
            }, this._core.options = r.extend({}, t.Defaults, this._core.options), this.$element.on(this._handlers)
        };
        t.Defaults = {
            nav: !1,
            navText: ["Предыдущий", "Следующий"],
            navSpeed: !1,
            navElement: "div",
            navContainer: !1,
            navContainerClass: "owl-nav",
            navClass: ["owl-prev", "owl-next"],
            slideBy: 1,
            dotClass: "owl-dot",
            dotsClass: "owl-dots",
            dots: !0,
            dotsEach: !1,
            dotsData: !1,
            dotsSpeed: !1,
            dotsContainer: !1
        }, t.prototype.initialize = function() {
            var e, i = this._core.settings;
            for (e in this._controls.$relative = (i.navContainer ? r(i.navContainer) : r("<div>").addClass(i.navContainerClass).appendTo(this.$element)).addClass("disabled"), this._controls.$previous = r("<" + i.navElement + ">").addClass(i.navClass[0]).html(i.navText[0]).prependTo(this._controls.$relative).on("click", r.proxy(function(e) {
                    this.prev(i.navSpeed)
                }, this)), this._controls.$next = r("<" + i.navElement + ">").addClass(i.navClass[1]).html(i.navText[1]).appendTo(this._controls.$relative).on("click", r.proxy(function(e) {
                    this.next(i.navSpeed)
                }, this)), i.dotsData || (this._templates = [r("<div>").addClass(i.dotClass).append(r("<span>")).prop("outerHTML")]), this._controls.$absolute = (i.dotsContainer ? r(i.dotsContainer) : r("<div>").addClass(i.dotsClass).appendTo(this.$element)).addClass("disabled"), this._controls.$absolute.on("click", "div", r.proxy(function(e) {
                    var t = r(e.target).parent().is(this._controls.$absolute) ? r(e.target).index() : r(e.target).parent().index();
                    e.preventDefault(), this.to(t, i.dotsSpeed)
                }, this)), this._overrides) this._core[e] = r.proxy(this[e], this)
        }, t.prototype.destroy = function() {
            var e, t, i, n;
            for (e in this._handlers) this.$element.off(e, this._handlers[e]);
            for (t in this._controls) this._controls[t].remove();
            for (n in this.overides) this._core[n] = this._overrides[n];
            for (i in Object.getOwnPropertyNames(this)) "function" != typeof this[i] && (this[i] = null)
        }, t.prototype.update = function() {
            var e, t, i = this._core.clones().length / 2,
                n = i + this._core.items().length,
                s = this._core.maximum(!0),
                r = this._core.settings,
                o = r.center || r.autoWidth || r.dotsData ? 1 : r.dotsEach || r.items;
            if ("page" !== r.slideBy && (r.slideBy = Math.min(r.slideBy, r.items)), r.dots || "page" == r.slideBy)
                for (this._pages = [], e = i, t = 0; e < n; e++) {
                    if (o <= t || 0 === t) {
                        if (this._pages.push({
                                start: Math.min(s, e - i),
                                end: e - i + o - 1
                            }), Math.min(s, e - i) === s) break;
                        t = 0, 0
                    }
                    t += this._core.mergers(this._core.relative(e))
                }
        }, t.prototype.draw = function() {
            var e, t = this._core.settings,
                i = this._core.items().length <= t.items,
                n = this._core.relative(this._core.current()),
                s = t.loop || t.rewind;
            this._controls.$relative.toggleClass("disabled", !t.nav || i), t.nav && (this._controls.$previous.toggleClass("disabled", !s && n <= this._core.minimum(!0)), this._controls.$next.toggleClass("disabled", !s && n >= this._core.maximum(!0))), this._controls.$absolute.toggleClass("disabled", !t.dots || i), t.dots && (e = this._pages.length - this._controls.$absolute.children().length, t.dotsData && 0 != e ? this._controls.$absolute.html(this._templates.join("")) : 0 < e ? this._controls.$absolute.append(new Array(1 + e).join(this._templates[0])) : e < 0 && this._controls.$absolute.children().slice(e).remove(), this._controls.$absolute.find(".active").removeClass("active"), this._controls.$absolute.children().eq(r.inArray(this.current(), this._pages)).addClass("active"))
        }, t.prototype.onTrigger = function(e) {
            var t = this._core.settings;
            e.page = {
                index: r.inArray(this.current(), this._pages),
                count: this._pages.length,
                size: t && (t.center || t.autoWidth || t.dotsData ? 1 : t.dotsEach || t.items)
            }
        }, t.prototype.current = function() {
            var i = this._core.relative(this._core.current());
            return r.grep(this._pages, r.proxy(function(e, t) {
                return e.start <= i && e.end >= i
            }, this)).pop()
        }, t.prototype.getPosition = function(e) {
            var t, i, n = this._core.settings;
            return "page" == n.slideBy ? (t = r.inArray(this.current(), this._pages), i = this._pages.length, e ? ++t : --t, t = this._pages[(t % i + i) % i].start) : (t = this._core.relative(this._core.current()), i = this._core.items().length, e ? t += n.slideBy : t -= n.slideBy), t
        }, t.prototype.next = function(e) {
            r.proxy(this._overrides.to, this._core)(this.getPosition(!0), e)
        }, t.prototype.prev = function(e) {
            r.proxy(this._overrides.to, this._core)(this.getPosition(!1), e)
        }, t.prototype.to = function(e, t, i) {
            var n;
            !i && this._pages.length ? (n = this._pages.length, r.proxy(this._overrides.to, this._core)(this._pages[(e % n + n) % n].start, t)) : r.proxy(this._overrides.to, this._core)(e, t)
        }, r.fn.owlCarousel.Constructor.Plugins.Navigation = t
    }(window.Zepto || window.jQuery, window, document),
    function(n, s) {
        "use strict";
        var t = function(e) {
            this._core = e, this._hashes = {}, this.$element = this._core.$element, this._handlers = {
                "initialized.owl.carousel": n.proxy(function(e) {
                    e.namespace && "URLHash" === this._core.settings.startPosition && n(s).trigger("hashchange.owl.navigation")
                }, this),
                "prepared.owl.carousel": n.proxy(function(e) {
                    if (e.namespace) {
                        var t = n(e.content).find("[data-hash]").addBack("[data-hash]").attr("data-hash");
                        if (!t) return;
                        this._hashes[t] = e.content
                    }
                }, this),
                "changed.owl.carousel": n.proxy(function(e) {
                    if (e.namespace && "position" === e.property.name) {
                        var i = this._core.items(this._core.relative(this._core.current())),
                            t = n.map(this._hashes, function(e, t) {
                                return e === i ? t : null
                            }).join();
                        if (!t || s.location.hash.slice(1) === t) return;
                        s.location.hash = t
                    }
                }, this)
            }, this._core.options = n.extend({}, t.Defaults, this._core.options), this.$element.on(this._handlers), n(s).on("hashchange.owl.navigation", n.proxy(function(e) {
                var t = s.location.hash.substring(1),
                    i = this._core.$stage.children(),
                    n = this._hashes[t] && i.index(this._hashes[t]);
                void 0 !== n && n !== this._core.current() && this._core.to(this._core.relative(n), !1, !0)
            }, this))
        };
        t.Defaults = {
            URLhashListener: !1
        }, t.prototype.destroy = function() {
            var e, t;
            for (e in n(s).off("hashchange.owl.navigation"), this._handlers) this._core.$element.off(e, this._handlers[e]);
            for (t in Object.getOwnPropertyNames(this)) "function" != typeof this[t] && (this[t] = null)
        }, n.fn.owlCarousel.Constructor.Plugins.Hash = t
    }(window.Zepto || window.jQuery, window, document), s = window.Zepto || window.jQuery, window, document, o = s("<support>").get(0).style, a = "Webkit Moz O ms".split(" "), i = {
        transition: {
            end: {
                WebkitTransition: "webkitTransitionEnd",
                MozTransition: "transitionend",
                OTransition: "oTransitionEnd",
                transition: "transitionend"
            }
        },
        animation: {
            end: {
                WebkitAnimation: "webkitAnimationEnd",
                MozAnimation: "animationend",
                OAnimation: "oAnimationEnd",
                animation: "animationend"
            }
        }
    }, n = function() {
        return !!A("transform")
    }, l = function() {
        return !!A("perspective")
    }, c = function() {
        return !!A("animation")
    }, A("transition") && (s.support.transition = new String(P("transition")), s.support.transition.end = i.transition.end[s.support.transition]), c() && (s.support.animation = new String(P("animation")), s.support.animation.end = i.animation.end[s.support.animation]), n() && (s.support.transform = new String(P("transform")), s.support.transform3d = l()), q = function(L) {
        "use strict";
        var s = "styler",
            n = {
                idSuffix: "-styler",
                filePlaceholder: "Файл не выбран",
                fileBrowse: "Обзор...",
                fileNumber: "Выбрано файлов: %s",
                selectPlaceholder: "Выберите...",
                selectSearch: !1,
                selectSearchLimit: 10,
                selectSearchNotFound: "Совпадений не найдено",
                selectSearchPlaceholder: "Поиск...",
                selectVisibleOptions: 0,
                selectSmartPositioning: !0,
                locale: "ru",
                locales: {
                    en: {
                        filePlaceholder: "No file selected",
                        fileBrowse: "Browse...",
                        fileNumber: "Selected files: %s",
                        selectPlaceholder: "Select...",
                        selectSearchNotFound: "No matches found",
                        selectSearchPlaceholder: "Search..."
                    }
                },
                onSelectOpened: function() {},
                onSelectClosed: function() {},
                onFormStyled: function() {}
            };

        function r(e, t) {
            this.element = e, this.options = L.extend({}, n, t);
            var i = this.options.locale;
            void 0 !== this.options.locales[i] && L.extend(this.options, this.options.locales[i]), this.init()
        }

        function z(e) {
            if (!L(e.target).parents().hasClass("jq-selectbox") && "OPTION" != e.target.nodeName && L("div.jq-selectbox.opened").length) {
                var t = L("div.jq-selectbox.opened"),
                    i = L("div.jq-selectbox__search input", t),
                    n = L("div.jq-selectbox__dropdown", t);
                t.find("select").data("_" + s).options.onSelectClosed.call(t), i.length && i.val("").keyup(), n.hide().find("li.sel").addClass("selected"), t.removeClass("focused opened dropup dropdown")
            }
        }
        r.prototype = {
            init: function() {
                var q = L(this.element),
                    A = this.options,
                    P = !(!navigator.userAgent.match(/(iPad|iPhone|iPod)/i) || navigator.userAgent.match(/(Windows\sPhone)/i)),
                    e = !(!navigator.userAgent.match(/Android/i) || navigator.userAgent.match(/(Windows\sPhone)/i));

                function H() {
                    void 0 !== q.attr("id") && "" !== q.attr("id") && (this.id = q.attr("id") + A.idSuffix), this.title = q.attr("title"), this.classes = q.attr("class"), this.data = q.data()
                }
                if (q.is(":checkbox")) {
                    var t = function() {
                        var e = new H,
                            t = L('<div class="jq-checkbox"><div class="jq-checkbox__div"></div></div>').attr({
                                id: e.id,
                                title: e.title
                            }).addClass(e.classes).data(e.data);
                        q.after(t).prependTo(t), q.is(":checked") && t.addClass("checked"), q.is(":disabled") && t.addClass("disabled"), t.click(function(e) {
                            e.preventDefault(), q.triggerHandler("click"), t.is(".disabled") || (q.is(":checked") ? (q.prop("checked", !1), t.removeClass("checked")) : (q.prop("checked", !0), t.addClass("checked")), q.focus().change())
                        }), q.closest("label").add('label[for="' + q.attr("id") + '"]').on("click.styler", function(e) {
                            L(e.target).is("a") || L(e.target).closest(t).length || (t.triggerHandler("click"), e.preventDefault())
                        }), q.on("change.styler", function() {
                            q.is(":checked") ? t.addClass("checked") : t.removeClass("checked")
                        }).on("keydown.styler", function(e) {
                            32 == e.which && t.click()
                        }).on("focus.styler", function() {
                            t.is(".disabled") || t.addClass("focused")
                        }).on("blur.styler", function() {
                            t.removeClass("focused")
                        })
                    };
                    t(), q.on("refresh", function() {
                        q.closest("label").add('label[for="' + q.attr("id") + '"]').off(".styler"), q.off(".styler").parent().before(q).remove(), t()
                    })
                } else if (q.is(":radio")) {
                    var i = function() {
                        var e = new H,
                            i = L('<div class="jq-radio"><div class="jq-radio__div"></div></div>').attr({
                                id: e.id,
                                title: e.title
                            }).addClass(e.classes).data(e.data);
                        q.after(i).prependTo(i), q.is(":checked") && i.addClass("checked"), q.is(":disabled") && i.addClass("disabled"), L.fn.commonParents = function() {
                            var e = this;
                            return e.first().parents().filter(function() {
                                return L(this).find(e).length === e.length
                            })
                        }, L.fn.commonParent = function() {
                            return L(this).commonParents().first()
                        }, i.click(function(e) {
                            if (e.preventDefault(), q.triggerHandler("click"), !i.is(".disabled")) {
                                var t = L('input[name="' + q.attr("name") + '"]');
                                t.commonParent().find(t).prop("checked", !1).parent().removeClass("checked"), q.prop("checked", !0).parent().addClass("checked"), q.focus().change()
                            }
                        }), q.closest("label").add('label[for="' + q.attr("id") + '"]').on("click.styler", function(e) {
                            L(e.target).is("a") || L(e.target).closest(i).length || (i.triggerHandler("click"), e.preventDefault())
                        }), q.on("change.styler", function() {
                            q.parent().addClass("checked")
                        }).on("focus.styler", function() {
                            i.is(".disabled") || i.addClass("focused")
                        }).on("blur.styler", function() {
                            i.removeClass("focused")
                        })
                    };
                    i(), q.on("refresh", function() {
                        q.closest("label").add('label[for="' + q.attr("id") + '"]').off(".styler"), q.off(".styler").parent().before(q).remove(), i()
                    })
                } else if (q.is(":file")) {
                    var n = function() {
                        var e = new H,
                            n = q.data("placeholder");
                        void 0 === n && (n = A.filePlaceholder);
                        var t = q.data("browse");
                        void 0 !== t && "" !== t || (t = A.fileBrowse);
                        var s = L('<div class="jq-file"><div class="jq-file__name">' + n + '</div><div class="jq-file__browse">' + t + "</div></div>").attr({
                            id: e.id,
                            title: e.title
                        }).addClass(e.classes).data(e.data);
                        q.after(s).appendTo(s), q.is(":disabled") && s.addClass("disabled");
                        var i = q.val(),
                            r = L("div.jq-file__name", s);
                        i && r.text(i.replace(/.+[\\\/]/, "")), q.on("change.styler", function() {
                            var e = q.val();
                            if (q.is("[multiple]")) {
                                e = "";
                                var t = q[0].files.length;
                                if (0 < t) {
                                    var i = q.data("number");
                                    void 0 === i && (i = A.fileNumber), e = i = i.replace("%s", t)
                                }
                            }
                            r.text(e.replace(/.+[\\\/]/, "")), "" === e ? (r.text(n), s.removeClass("changed")) : s.addClass("changed")
                        }).on("focus.styler", function() {
                            s.addClass("focused")
                        }).on("blur.styler", function() {
                            s.removeClass("focused")
                        }).on("click.styler", function() {
                            s.removeClass("focused")
                        })
                    };
                    n(), q.on("refresh", function() {
                        q.off(".styler").parent().before(q).remove(), n()
                    })
                } else if (q.is('input[type="number"]')) {
                    var s = function() {
                        var e = new H,
                            t = L('<div class="jq-number"><div class="jq-number__spin minus"></div><div class="jq-number__spin plus"></div></div>').attr({
                                id: e.id,
                                title: e.title
                            }).addClass(e.classes).data(e.data);
                        q.after(t).prependTo(t).wrap('<div class="jq-number__field"></div>'), q.is(":disabled") && t.addClass("disabled");
                        var r, o, a, i = null,
                            n = null;
                        void 0 !== q.attr("min") && (r = q.attr("min")), void 0 !== q.attr("max") && (o = q.attr("max")), a = void 0 !== q.attr("step") && L.isNumeric(q.attr("step")) ? Number(q.attr("step")) : Number(1);

                        function s(e) {
                            var t, i = q.val();
                            L.isNumeric(i) || (i = 0, q.val("0")), e.is(".minus") ? t = Number(i) - a : e.is(".plus") && (t = Number(i) + a);
                            var n = (a.toString().split(".")[1] || []).length;
                            if (0 < n) {
                                for (var s = "1"; s.length <= n;) s += "0";
                                t = Math.round(t * s) / s
                            }
                            L.isNumeric(r) && L.isNumeric(o) ? r <= t && t <= o && q.val(t) : L.isNumeric(r) && !L.isNumeric(o) ? r <= t && q.val(t) : !L.isNumeric(r) && L.isNumeric(o) ? t <= o && q.val(t) : q.val(t)
                        }
                        t.is(".disabled") || (t.on("mousedown", "div.jq-number__spin", function() {
                            var e = L(this);
                            s(e), i = setTimeout(function() {
                                n = setInterval(function() {
                                    s(e)
                                }, 40)
                            }, 350)
                        }).on("mouseup mouseout", "div.jq-number__spin", function() {
                            clearTimeout(i), clearInterval(n)
                        }).on("mouseup", "div.jq-number__spin", function() {
                            q.change().trigger("input")
                        }), q.on("focus.styler", function() {
                            t.addClass("focused")
                        }).on("blur.styler", function() {
                            t.removeClass("focused")
                        }))
                    };
                    s(), q.on("refresh", function() {
                        q.off(".styler").closest(".jq-number").before(q).remove(), s()
                    })
                } else if (q.is("select")) {
                    var r = function() {
                        function $(t) {
                            var i = t.prop("scrollHeight") - t.outerHeight(),
                                n = null,
                                s = null;
                            t.off("mousewheel DOMMouseScroll").on("mousewheel DOMMouseScroll", function(e) {
                                n = e.originalEvent.detail < 0 || 0 < e.originalEvent.wheelDelta ? 1 : -1, s = t.scrollTop(), (i <= s && n < 0 || s <= 0 && 0 < n) && (e.stopPropagation(), e.preventDefault())
                            })
                        }
                        var D = L("option", q),
                            S = "";

                        function N() {
                            for (var e = 0; e < D.length; e++) {
                                var t = D.eq(e),
                                    i = "",
                                    n = "",
                                    s = "",
                                    r = "",
                                    o = "",
                                    a = "",
                                    l = "",
                                    c = "",
                                    u = "";
                                t.prop("selected") && (n = "selected sel"), t.is(":disabled") && (n = "disabled"), t.is(":selected:disabled") && (n = "selected sel disabled"), void 0 !== t.attr("id") && "" !== t.attr("id") && (r = ' id="' + t.attr("id") + A.idSuffix + '"'), void 0 !== t.attr("title") && "" !== D.attr("title") && (o = ' title="' + t.attr("title") + '"'), void 0 !== t.attr("class") && (l = " " + t.attr("class"), u = ' data-jqfs-class="' + t.attr("class") + '"');
                                var h = t.data();
                                for (var d in h) "" !== h[d] && (a += " data-" + d + '="' + h[d] + '"');
                                n + l !== "" && (s = ' class="' + n + l + '"'), i = "<li" + u + a + s + o + r + ">" + t.html() + "</li>", t.parent().is("optgroup") && (void 0 !== t.parent().attr("class") && (c = " " + t.parent().attr("class")), i = "<li" + u + a + ' class="' + n + l + " option" + c + '"' + o + r + ">" + t.html() + "</li>", t.is(":first-child") && (i = '<li class="optgroup' + c + '">' + t.parent().attr("label") + "</li>" + i)), S += i
                            }
                        }
                        if (q.is("[multiple]")) {
                            if (e || P) return;
                            ! function() {
                                var e = new H,
                                    t = L('<div class="jq-select-multiple jqselect"></div>').attr({
                                        id: e.id,
                                        title: e.title
                                    }).addClass(e.classes).data(e.data);
                                q.after(t), N(), t.append("<ul>" + S + "</ul>");
                                var i = L("ul", t),
                                    s = L("li", t),
                                    n = q.attr("size"),
                                    r = i.outerHeight(),
                                    o = s.outerHeight();
                                void 0 !== n && 0 < n ? i.css({
                                    height: o * n
                                }) : i.css({
                                    height: 4 * o
                                }), r > t.height() && (i.css("overflowY", "scroll"), $(i), s.filter(".selected").length && i.scrollTop(i.scrollTop() + s.filter(".selected").position().top)), q.prependTo(t), q.is(":disabled") ? (t.addClass("disabled"), D.each(function() {
                                    L(this).is(":selected") && s.eq(L(this).index()).addClass("selected")
                                })) : (s.filter(":not(.disabled):not(.optgroup)").click(function(e) {
                                    q.focus();
                                    var t = L(this);
                                    if (e.ctrlKey || e.metaKey || t.addClass("selected"), e.shiftKey || t.addClass("first"), e.ctrlKey || e.metaKey || e.shiftKey || t.siblings().removeClass("selected first"), (e.ctrlKey || e.metaKey) && (t.is(".selected") ? t.removeClass("selected first") : t.addClass("selected first"), t.siblings().removeClass("first")), e.shiftKey) {
                                        var i = !1,
                                            n = !1;
                                        t.siblings().removeClass("selected").siblings(".first").addClass("selected"), t.prevAll().each(function() {
                                            L(this).is(".first") && (i = !0)
                                        }), t.nextAll().each(function() {
                                            L(this).is(".first") && (n = !0)
                                        }), i && t.prevAll().each(function() {
                                            if (L(this).is(".selected")) return !1;
                                            L(this).not(".disabled, .optgroup").addClass("selected")
                                        }), n && t.nextAll().each(function() {
                                            if (L(this).is(".selected")) return !1;
                                            L(this).not(".disabled, .optgroup").addClass("selected")
                                        }), 1 == s.filter(".selected").length && t.addClass("first")
                                    }
                                    D.prop("selected", !1), s.filter(".selected").each(function() {
                                        var e = L(this),
                                            t = e.index();
                                        e.is(".option") && (t -= e.prevAll(".optgroup").length), D.eq(t).prop("selected", !0)
                                    }), q.change()
                                }), D.each(function(e) {
                                    L(this).data("optionIndex", e)
                                }), q.on("change.styler", function() {
                                    s.removeClass("selected");
                                    var t = [];
                                    D.filter(":selected").each(function() {
                                        t.push(L(this).data("optionIndex"))
                                    }), s.not(".optgroup").filter(function(e) {
                                        return -1 < L.inArray(e, t)
                                    }).addClass("selected")
                                }).on("focus.styler", function() {
                                    t.addClass("focused")
                                }).on("blur.styler", function() {
                                    t.removeClass("focused")
                                }), r > t.height() && q.on("keydown.styler", function(e) {
                                    38 != e.which && 37 != e.which && 33 != e.which || i.scrollTop(i.scrollTop() + s.filter(".selected").position().top - o), 40 != e.which && 39 != e.which && 34 != e.which || i.scrollTop(i.scrollTop() + s.filter(".selected:last").position().top - i.innerHeight() + 2 * o)
                                }))
                            }()
                        } else ! function() {
                            var e = new H,
                                t = "",
                                i = q.data("placeholder"),
                                n = q.data("search"),
                                s = q.data("search-limit"),
                                r = q.data("search-not-found"),
                                o = q.data("search-placeholder"),
                                l = q.data("smart-positioning");
                            void 0 === i && (i = A.selectPlaceholder), void 0 !== n && "" !== n || (n = A.selectSearch), void 0 !== s && "" !== s || (s = A.selectSearchLimit), void 0 !== r && "" !== r || (r = A.selectSearchNotFound), void 0 === o && (o = A.selectSearchPlaceholder), void 0 !== l && "" !== l || (l = A.selectSmartPositioning);
                            var c = L('<div class="jq-selectbox jqselect"><div class="jq-selectbox__select"><div class="jq-selectbox__select-text"></div><div class="jq-selectbox__trigger"><div class="jq-selectbox__trigger-arrow"></div></div></div></div>').attr({
                                id: e.id,
                                title: e.title
                            }).addClass(e.classes).data(e.data);
                            q.after(c).prependTo(c);
                            var u = c.css("z-index");
                            u = 0 < u ? u : 1;
                            var a = L("div.jq-selectbox__select", c),
                                h = L("div.jq-selectbox__select-text", c),
                                d = D.filter(":selected");
                            N(), n && (t = '<div class="jq-selectbox__search"><input type="search" autocomplete="off" placeholder="' + o + '"></div><div class="jq-selectbox__not-found">' + r + "</div>");
                            var p = L('<div class="jq-selectbox__dropdown" data-simplebar>' + t + "<ul>" + S + "</ul></div>");
                            c.append(p);
                            var f = L("ul", p),
                                g = L("li", p),
                                m = L("input", p),
                                v = L("div.jq-selectbox__not-found", p).hide();
                            g.length < s && m.parent().hide(), "" === D.first().text() && D.first().is(":selected") && !1 !== i ? h.text(i).addClass("placeholder") : h.text(d.text());
                            var y = 0,
                                w = 0;
                            if (g.css({
                                    display: "inline-block"
                                }), g.each(function() {
                                    var e = L(this);
                                    e.innerWidth() > y && (y = e.innerWidth(), w = e.width())
                                }), g.css({
                                    display: ""
                                }), h.is(".placeholder") && h.width() > y) h.width(h.width());
                            else {
                                var x = c.clone().appendTo("body").width("auto"),
                                    b = x.outerWidth();
                                x.remove(), b == c.outerWidth() && h.width(w)
                            }
                            y > c.width() && p.width(y), "" === D.first().text() && "" !== q.data("placeholder") && g.first().hide();
                            var _ = c.outerHeight(!0),
                                C = m.parent().outerHeight(!0) || 0,
                                T = f.css("max-height"),
                                k = g.filter(".selected");
                            if (k.length < 1 && g.first().addClass("selected sel"), void 0 === g.data("li-height")) {
                                var j = g.outerHeight();
                                !1 !== i && (j = g.eq(1).outerHeight()), g.data("li-height", j)
                            }
                            var E = p.css("top");
                            if ("auto" == p.css("left") && p.css({
                                    left: 0
                                }), "auto" == p.css("top") && (p.css({
                                    top: _
                                }), E = _), p.hide(), k.length && (D.first().text() != d.text() && c.addClass("changed"), c.data("jqfs-class", k.data("jqfs-class")), c.addClass(k.data("jqfs-class"))), q.is(":disabled")) return c.addClass("disabled");
                            a.click(function() {
                                if (L("div.jq-selectbox").filter(".opened").length && A.onSelectClosed.call(L("div.jq-selectbox").filter(".opened")), q.focus(), !P) {
                                    var t = L(window),
                                        i = g.data("li-height"),
                                        n = c.offset().top,
                                        s = t.height() - _ - (n - t.scrollTop()),
                                        e = q.data("visible-options");
                                    void 0 !== e && "" !== e || (e = A.selectVisibleOptions);
                                    var r = 5 * i,
                                        o = i * e;
                                    0 < e && e < 6 && (r = o), 0 === e && (o = "auto");
                                    var a = function() {
                                        p.height("auto").css({
                                            bottom: "auto",
                                            top: E
                                        });

                                        function e() {
                                            f.css("max-height", Math.floor((s - 20 - C) / i) * i)
                                        }
                                        e(), f.css("max-height", o), "none" != T && f.css("max-height", T), s < p.outerHeight() + 20 && e()
                                    };
                                    !0 === l || 1 === l ? r + C + 20 < s ? (a(), c.removeClass("dropup").addClass("dropdown")) : (function() {
                                        p.height("auto").css({
                                            top: "auto",
                                            bottom: E
                                        });

                                        function e() {
                                            f.css("max-height", Math.floor((n - t.scrollTop() - 20 - C) / i) * i)
                                        }
                                        e(), f.css("max-height", o), "none" != T && f.css("max-height", T), n - t.scrollTop() - 20 < p.outerHeight() + 20 && e()
                                    }(), c.removeClass("dropdown").addClass("dropup")) : !1 === l || 0 === l ? r + C + 20 < s && (a(), c.removeClass("dropup").addClass("dropdown")) : (p.height("auto").css({
                                        bottom: "auto",
                                        top: E
                                    }), f.css("max-height", o), "none" != T && f.css("max-height", T)), c.offset().left + p.outerWidth() > t.width() && p.css({
                                        left: "auto",
                                        right: 0
                                    }), L("div.jqselect").css({
                                        zIndex: u - 1
                                    }).removeClass("opened"), c.css({
                                        zIndex: u
                                    }), p.is(":hidden") ? (L("div.jq-selectbox__dropdown:visible").hide(), p.show(), c.addClass("opened focused"), A.onSelectOpened.call(c)) : (p.hide(), c.removeClass("opened dropup dropdown"), L("div.jq-selectbox").filter(".opened").length && A.onSelectClosed.call(c)), m.length && (m.val("").keyup(), v.hide(), m.keyup(function() {
                                        var e = L(this).val();
                                        g.each(function() {
                                            L(this).html().match(new RegExp(".*?" + e + ".*?", "i")) ? L(this).show() : L(this).hide()
                                        }), "" === D.first().text() && "" !== q.data("placeholder") && g.first().hide(), g.filter(":visible").length < 1 ? v.show() : v.hide()
                                    })), g.filter(".selected").length && ("" === q.val() ? f.scrollTop(0) : (f.innerHeight() / i % 2 != 0 && (i /= 2), f.scrollTop(f.scrollTop() + g.filter(".selected").position().top - f.innerHeight() / 2 + i))), $(f)
                                }
                            }), g.hover(function() {
                                L(this).siblings().removeClass("selected")
                            }), g.filter(".selected").text(), g.filter(":not(.disabled):not(.optgroup)").click(function() {
                                q.focus();
                                var e = L(this),
                                    t = e.text();
                                if (!e.is(".selected")) {
                                    var i = e.index();
                                    i -= e.prevAll(".optgroup").length, e.addClass("selected sel").siblings().removeClass("selected sel"), D.prop("selected", !1).eq(i).prop("selected", !0), t, h.text(t), c.data("jqfs-class") && c.removeClass(c.data("jqfs-class")), c.data("jqfs-class", e.data("jqfs-class")), c.addClass(e.data("jqfs-class")), q.change()
                                }
                                p.hide(), c.removeClass("opened dropup dropdown"), A.onSelectClosed.call(c)
                            }), p.mouseout(function() {
                                L("li.sel", p).addClass("selected")
                            }), q.on("change.styler", function() {
                                h.text(D.filter(":selected").text()).removeClass("placeholder"), g.removeClass("selected sel").not(".optgroup").eq(q[0].selectedIndex).addClass("selected sel"), D.first().text() != g.filter(".selected").text() ? c.addClass("changed") : c.removeClass("changed")
                            }).on("focus.styler", function() {
                                c.addClass("focused"), L("div.jqselect").not(".focused").removeClass("opened dropup dropdown").find("div.jq-selectbox__dropdown").hide()
                            }).on("blur.styler", function() {
                                c.removeClass("focused")
                            }).on("keydown.styler keyup.styler", function(e) {
                                var t = g.data("li-height");
                                "" === q.val() ? h.text(i).addClass("placeholder") : h.text(D.filter(":selected").text()), g.removeClass("selected sel").not(".optgroup").eq(q[0].selectedIndex).addClass("selected sel"), 38 != e.which && 37 != e.which && 33 != e.which && 36 != e.which || ("" === q.val() ? f.scrollTop(0) : f.scrollTop(f.scrollTop() + g.filter(".selected").position().top)), 40 != e.which && 39 != e.which && 34 != e.which && 35 != e.which || f.scrollTop(f.scrollTop() + g.filter(".selected").position().top - f.innerHeight() + t), 13 == e.which && (e.preventDefault(), p.hide(), c.removeClass("opened dropup dropdown"), A.onSelectClosed.call(c))
                            }).on("keydown.styler", function(e) {
                                32 == e.which && (e.preventDefault(), a.click())
                            }), z.registered || (L(document).on("click", z), z.registered = !0)
                        }()
                    };
                    r(), q.on("refresh", function() {
                        q.off(".styler").parent().before(q).remove(), r()
                    })
                } else q.is(":reset") && q.on("click", function() {
                    setTimeout(function() {
                        q.closest("form").find("input, select").trigger("refresh")
                    }, 1)
                })
            },
            destroy: function() {
                var e = L(this.element);
                e.is(":checkbox") || e.is(":radio") ? (e.removeData("_" + s).off(".styler refresh").removeAttr("style").parent().before(e).remove(), e.closest("label").add('label[for="' + e.attr("id") + '"]').off(".styler")) : e.is('input[type="number"]') ? e.removeData("_" + s).off(".styler refresh").closest(".jq-number").before(e).remove() : (e.is(":file") || e.is("select")) && e.removeData("_" + s).off(".styler refresh").removeAttr("style").parent().before(e).remove()
            }
        }, L.fn[s] = function(t) {
            var i, n = arguments;
            return void 0 === t || "object" == typeof t ? (this.each(function() {
                L.data(this, "_" + s) || L.data(this, "_" + s, new r(this, t))
            }).promise().done(function() {
                var e = L(this[0]).data("_" + s);
                e && e.options.onFormStyled.call()
            }), this) : "string" == typeof t && "_" !== t[0] && "init" !== t ? (this.each(function() {
                var e = L.data(this, "_" + s);
                e instanceof r && "function" == typeof e[t] && (i = e[t].apply(e, Array.prototype.slice.call(n, 1)))
            }), void 0 !== i ? i : this) : void 0
        }, z.registered = !1
    }, "function" == typeof define && define.amd ? define(["jquery"], q) : "object" == typeof exports ? module.exports = q($ || require("jquery")) : q(jQuery), $(document).ready(function() {
        $("[data-target]").click(function(e) {
            e.preventDefault();
            var t = $(this).attr("data-target");
            $("#" + t).toggleClass("active")
        }), $(".modal-close, .overlay-close").click(function(e) {
            e.preventDefault(), $(".modal-overlay").removeClass("active")
        }), $(".main-slider").owlCarousel({
            items: 1,
            loop: !0,
            nav: !0,
            dots: !0,
            margin: 0,
            autoplay: !0,
            autoplayTimeout: 5e3,
            autoplaySpeed: 1e3
        }), $(".review-slider").owlCarousel({
            items: 1,
            loop: !1,
            nav: !0,
            margin: 10
        }), $(".tearm-slider").owlCarousel({
            items: 1,
            loop: !1,
            nav: !0,
            margin: 10,
            responsive: {
                0: {
                    items: 1
                },
                480: {
                    items: 2
                },
                768: {
                    items: 3
                }
            }
        }), $(".category-btn").click(function(e) {
            e.preventDefault(), $(".submenu").toggleClass("active")
        }), $(document).click(function(e) {
            var t = $(".submenu");
            0 === $(e.target).closest(".header-nav__category").length && t.removeClass("active")
        }), $(".sidebar-title").click(function(e) {
            if ($(e.target).closest(".sidebar-item__tooltip").length) return;
            e.preventDefault(), $(this).closest(".sidebar-item").toggleClass("open")
        }), $(".btn-filter").click(function(e) {
            e.preventDefault(), $(".sidebar").toggleClass("open")
        }), $("[data-tab]").click(function(e) {
            e.preventDefault();
            var t = $(this).attr("data-tab");
            $(".tab").removeClass("active"), $("[data-tab]").removeClass("active"), $("." + t).addClass("active"), $(this).addClass("active")
        }), $(".js-select").styler(), $(window).width() <= 992 && L(), $(".js-selectize").selectize({
            create: !0,
            sortField: {
                field: "text",
                direction: "asc"
            }
        });
    })