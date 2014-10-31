!function () {
    function a(a, b) {
        for (var c = 0; c < a.length; c++) {
            var d = a[c];
            b(d)
        }
    }

    function b(a) {
        return "string" == typeof a || "[object String]" == toString.call(a)
    }

    function c() {
        navigator.userAgent.toLowerCase().indexOf("msie") >= 0 ? location.href = location.href : location.reload()
    }

    window.m = window.m || {};
    for (var d, e = function () {
    }, f = ["assert", "clear", "count", "debug", "dir", "dirxml", "error", "exception", "group", "groupCollapsed", "groupEnd", "info", "log", "markTimeline", "profile", "profileEnd", "table", "time", "timeEnd", "timeStamp", "trace", "warn"], g = f.length, h = window.console = window.console || {}; g--;)d = f[g], h[d] || (h[d] = e);
    h.debug = function (a) {
        try {
            var b = "[" + (+new Date - st) / 1e3 + "] ";
            h.info(b + a)
        } catch (c) {
        }
    }, m.restore = function () {
        document.cookie = "m-no-eval=1;path=/;max-age=31536000", c()
    }, m.mobilize = function () {
        document.cookie = "m-no-eval=0;path=/;max-age=31536000", c()
    }, m.selectFull = function () {
        document.cookie = "m-no-eval=0;path=/;max-age=31536000", document.cookie = "m-no=1;path=/;max-age=31536000"
    }, m.beforeHtmlChangeIn = function (b, c) {
        a(["append", "prepend", "html", "text"], function (a) {
            m.beforeJqMethod(a, b, c)
        })
    }, m.logHtmlChanges = function () {
        a(["append", "appendTo", "prepend", "prependTo", "html", "text", "after", "before", "insertAfter", "insertBefore", "wrap", "wrapAll", "wrapInner"], function (a) {
            m.afterJqMethod(a, null, function () {
                this.selector ? h.info('html changed by method: "' + a + '", selector: "' + this.selector + '"') : (h.info('html changed by method: "' + a + '", jquery object:'), h.info(this))
            })
        })
    }, m.beforeJqMethod = function (c, d, e) {
        var f = $.fn[c];
        f && ($.fn[c] = function () {
            if (!d || this.is(d)) {
                var c, g = arguments[0], h = e.apply(this, arguments);
                return b(g) && h.length && h.jquery ? (c = "", a(h, function (a) {
                    c += a.outerHTML || ""
                })) : c = h, f.apply(this, [c])
            }
            return f.apply(this, arguments)
        })
    }, m.afterJqMethod = function (a, b, c) {
        var d = $.fn[a];
        d && ($.fn[a] = function () {
            var a = d.apply(this, arguments);
            if (!b || this.is(b))var e = this, f = setTimeout(function () {
                c.apply(e, arguments), clearTimeout(f)
            }, 1);
            return a
        })
    }
}();