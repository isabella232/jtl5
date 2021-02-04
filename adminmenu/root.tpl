<script>
    document.getElementById('content_wrapper').setAttribute('style', 'position:relative');

    const addCss = function (url) {
        var cssId = btoa(url).substr(btoa(url).length - 32, 32);  // you could encode the css path itself to generate id..
        if (!document.getElementById(cssId)) {
            var head = document.getElementsByTagName('head')[0];
            var link = document.createElement('link');
            link.id = cssId;
            link.rel = 'stylesheet';
            link.type = 'text/css';
            link.href = url;
            link.media = 'all';
            head.appendChild(link);
        }
    };
    {*foreach from=$css item=file*}
    addCss('{$root}app/build/static/css/2.14a8aa98.chunk.css');
    addCss('{$root}app/build/static/css/main.e95ef23c.chunk.css');
    {*/foreach*}
</script>

<script type='application/json' id='pluginInfo'>{$infoJSON}</script>
<div id="root"
     style="top:0;left:0;right:0;bottom:0;position:absolute;z-index:1;background:white url('https://cdn.webstollen.com/plugin/img/ws_bg.jpg'); background-size: cover"></div>
<script>{literal}!function (e) {
        function r(r) {
            for (var n, a, i = r[0], c = r[1], l = r[2], p = 0, s = []; p < i.length; p++) a = i[p], Object.prototype.hasOwnProperty.call(o, a) && o[a] && s.push(o[a][0]), o[a] = 0;
            for (n in c) Object.prototype.hasOwnProperty.call(c, n) && (e[n] = c[n]);
            for (f && f(r); s.length;) s.shift()();
            return u.push.apply(u, l || []), t()
        }

        function t() {
            for (var e, r = 0; r < u.length; r++) {
                for (var t = u[r], n = !0, i = 1; i < t.length; i++) {
                    var c = t[i];
                    0 !== o[c] && (n = !1)
                }
                n && (u.splice(r--, 1), e = a(a.s = t[0]))
            }
            return e
        }

        var n = {}, o = {1: 0}, u = [];

        function a(r) {
            if (n[r]) return n[r].exports;
            var t = n[r] = {i: r, l: !1, exports: {}};
            return e[r].call(t.exports, t, t.exports, a), t.l = !0, t.exports
        }

        a.e = function (e) {
            var r = [], t = o[e];
            if (0 !== t) if (t) r.push(t[2]); else {
                var n = new Promise((function (r, n) {
                    t = o[e] = [r, n]
                }));
                r.push(t[2] = n);
                var u, i = document.createElement("script");
                i.charset = "utf-8", i.timeout = 120, a.nc && i.setAttribute("nonce", a.nc), i.src = function (e) {
                    return a.p + "static/js/" + ({}[e] || e) + "." + {3: "e219452f"}[e] + ".chunk.js"
                }(e);
                var c = new Error;
                u = function (r) {
                    i.onerror = i.onload = null, clearTimeout(l);
                    var t = o[e];
                    if (0 !== t) {
                        if (t) {
                            var n = r && ("load" === r.type ? "missing" : r.type), u = r && r.target && r.target.src;
                            c.message = "Loading chunk " + e + " failed.\n(" + n + ": " + u + ")", c.name = "ChunkLoadError", c.type = n, c.request = u, t[1](c)
                        }
                        o[e] = void 0
                    }
                };
                var l = setTimeout((function () {
                    u({type: "timeout", target: i})
                }), 12e4);
                i.onerror = i.onload = u, document.head.appendChild(i)
            }
            return Promise.all(r)
        }, a.m = e, a.c = n, a.d = function (e, r, t) {
            a.o(e, r) || Object.defineProperty(e, r, {enumerable: !0, get: t})
        }, a.r = function (e) {
            "undefined" != typeof Symbol && Symbol.toStringTag && Object.defineProperty(e, Symbol.toStringTag, {value: "Module"}), Object.defineProperty(e, "__esModule", {value: !0})
        }, a.t = function (e, r) {
            if (1 & r && (e = a(e)), 8 & r) return e;
            if (4 & r && "object" == typeof e && e && e.__esModule) return e;
            var t = Object.create(null);
            if (a.r(t), Object.defineProperty(t, "default", {
                enumerable: !0,
                value: e
            }), 2 & r && "string" != typeof e) for (var n in e) a.d(t, n, function (r) {
                return e[r]
            }.bind(null, n));
            return t
        }, a.n = function (e) {
            var r = e && e.__esModule ? function () {
                return e.default
            } : function () {
                return e
            };
            return a.d(r, "a", r), r
        }, a.o = function (e, r) {
            return Object.prototype.hasOwnProperty.call(e, r)
        }, a.p = "/", a.oe = function (e) {
            throw console.error(e), e
        };
        var i = this.webpackJsonpapp = this.webpackJsonpapp || [], c = i.push.bind(i);
        i.push = r, i = i.slice();
        for (var l = 0; l < i.length; l++) r(i[l]);
        var f = c;
        t()
    }([]){/literal}</script>
{*foreach from=$js item=file*}
    <script src="{$root}app/build/static/js/2.035edc5a.chunk.js"></script>
    <script src="{$root}app/build/static/js/ain.41361e11.chunk.js"></script>
{*/foreach*}