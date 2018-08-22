// ==UserScript==
// @name         ssc
// @namespace    https://github.com/reruin
// @version      0.2
// @license      Apache License 2.0
// @description  ssc
// @author       reruin@gmail.com
// @grant        none
// @include      https://www.kimsufi.com/*
// @include      https://www.soyoustart.com/*
// @connect      *
// @run-at       document-start
// ==/UserScript==


(function(root) {
    var nw = root.nw = {};
    var stack = [];
    var ArrayProto = Array.prototype,
        ObjProto = Object.prototype;
    var hasOwnProperty = ObjProto.hasOwnProperty;

    var page = {
        addStyle: function(css) {
            var el = document.createElement("style");
            el.innerHTML = css;
            document.getElementsByTagName('head')[0].appendChild(el);
        },
        addScript: function(script, pos) {
            var el = document.createElement("script");
            el.textContent = script;
            if (typeof pos == 'object') {
                pos.appendChild(el);
            } else if (pos == 'head') {
                document.getElementsByTagName('head')[0].appendChild(el);
            } else {
                document.getElementsByTagName('body')[0].appendChild(el);
            }
        },
        addScriptLink: function(data, pos) {
            var el = document.createElement("script");
            for (var i in data) {
                el.setAttribute(i, data[i]);
            }

            if (typeof pos == 'object') {
                pos.appendChild(el);
            } else if (pos == 'head') {
                document.getElementsByTagName('head')[0].appendChild(el);
            } else {
                document.getElementsByTagName('body')[0].appendChild(el);
            }
        },
        addHtml: function(dom) {
            var el = document.createElement("dom");
            el.innerHTML = dom;
            document.getElementsByTagName('body')[0].appendChild(el);
        }
    };


    function $(e) {
        return document.querySelector(e);
    }

    function $$(e) {
        return document.querySelectorAll(e);
    }

    function noop() {

    }

    function has(obj, key) {
        return obj != null && hasOwnProperty.call(obj, key);
    }

    function key(obj) {
        var k = [];
        for (var i in obj) {
            if (has(obj, i)) k.push(i);
        }
        return k;
    }

    function isString(v) {
        return typeof v === 'string';
    }

    function is(v, b) {
        return ObjProto.toString.call(v) === "[object " + b + "]";
    }

    function isArray(v) {
        return is(v, 'Array');
    }

    function isRegExp(v) {
        return is(v, 'RegExp');
    }

    function isObject(v) {
        return is(v, 'Object');
    }

    function isFunction(v) {
        return is(v, 'Function');
    }

    function create(expr, handler) {
        if (expr && handler) {
            stack.push({ rule: expr, post: handler });
        } else {
            stack.push(expr);
        }
    }

    function replace(str, obj, format) {
        return str.replace(RegExp('(?:' + key(obj).join('|').replace(/([\:\'\)\(\{\}])/g, '\\$1') + ')', 'g'), function(match) {
            return format ? format(obj[match]) : obj[match];
        });
    }

    function toArray(a) {
        return Array.prototype.slice.call(a);
    }

    function formatLink(newurl, m) {
        return newurl.replace(/\$(\d+)/g, function($0, $1) {
            return m[$1];
        });
    }

    function hit(obj) {
        var ret = [];
        for (var i in stack) {
            var rule = stack[i].rule;
            if (isRegExp(rule)) {
                var m = obj.url.match(rule);
                // console.log(stack[i].post,m)
                if (m) {
                    if (isString(stack[i].post)) {
                        ret.push({
                            redirect: formatLink(stack[i].post, toArray(m))
                        });
                    } else {
                        ret.push({
                            pre: stack[i].pre || noop,
                            post: stack[i].post || noop,
                            args: toArray(m)
                        });

                    }
                }

            } else if (isObject(rule)) {
                var flag = true;
                var m = null,
                    ret_t = {};
                for (var key in rule) {
                    m = obj[key].match(rule[key]);
                    if (!m) {
                        flag = false;
                        break;
                    } else {
                        if (m.length > 1) {
                            ret_t[key] = toArray(m);
                        }
                    }
                }
                if (flag) {
                    ret.push({
                        pre: stack[i].pre || noop,
                        post: stack[i].post || noop,
                        args: ret_t
                    });
                }
            } else if (isFunction(rule)) {
                if (rule()) {
                    ret.push({
                        pre: stack[i].pre || noop,
                        post: stack[i].post || noop,
                        args: {}
                    });
                }
            } else if (isArray(rule)) {
                var flag = false;
                for (var j = rule.length - 1; j >= 0; j--) {
                    if (obj.url.match(rule[j])) {
                        flag = true;
                        break;
                    }
                }
                if (flag) {
                    ret.push({
                        pre: stack[i].pre || noop,
                        post: stack[i].post || noop,
                        args: {}
                    });
                }
            }
        }
        return ret;
    }

    function init() {
        var loc = window.location;

        var obj = {
            url: loc.href,
            scheme: loc.protocol.slice(0, -1),
            host: loc.hostname,
            port: loc.port,
            path: loc.pathname,
            search: loc.search,
            hash: loc.hash
        };

        var handlers = hit(obj);
        if (handlers.length) {
            handlers.forEach(function(handler) {
                if (handler.redirect) {
                    open(handler.redirect);
                } else if (handler.pre) handler.pre(handler.args);
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            if (handlers.length) {
                handlers.forEach(function(handler) {
                    if (handler.post) {
                        console.log(handler.post)
                        handler.post(handler.args);
                    }
                });
            }
        })
    }

    function monitor(tag, expr, callback) {
        var d = tag.split(':');
        var evts = {
            'removed': 'DOMNodeRemoved',
            'inserted': 'DOMNodeInserted',
            'modified': 'DOMSubtreeModified'
        };

        tag = d[0];

        var evt = evts[d[1] || 'modified'];

        var watch = d[2] === undefined ? false : true;

        if (isFunction(expr)) {
            callback = expr;
            expr = null;
        }

        var matchSpan = function(target, t) {
            var k = document.createElement('div');
            k.appendChild(target.cloneNode(false));
            var ret = k.querySelector(t);
            k = null;
            return ret;
        }

        //return new promise(function(resolve, reject){
        var handler = function(event) {
            var target = event.target;
            if (matchSpan(target, tag)) {
                if (expr) {
                    var m = target.textContent.match(expr);
                    if (m) {
                        if (callback) callback(m);
                        if (!watch) document.removeEventListener(evt, handler);
                    }
                } else {
                    if (callback) callback(target);

                    if (!watch) document.removeEventListener(evt, handler);
                }
            }
        };

        document.addEventListener(evt, handler);
        //});
    }

    function open(url) {
        open_direct(url);
    }

    function open_direct(url) {
        var link = document.createElementNS('http://www.w3.org/1999/xhtml', 'a');
        link.href = url;
        link.click();
    }

    nw.c = create;
    nw.m = monitor;
    nw.o = open;

    nw.$ = $;
    nw.$$ = $$;
    nw.r = replace;

    nw.init = init;
    nw.noop = noop;

    nw.addStyle = page.addStyle;
    nw.addScript = page.addScript;
    nw.addScriptLink = page.addScriptLink;
}(this));


/**
 * ssc
 *
 */

//https://www.kimsufi.com/en/order/kimsufi.xml?reference=1801sk12
nw.c({
    rule: /www\.kimsufi\.com\/en\/order\/kimsufi\.xml\?reference=/,
    pre: function() {},
    post: function() {
        var script = function() {
            var checkStock = () => {

            }
            var $ = jQuery

            var sessionId = getCookie('KSOrderSessionID')
            var dedicatedServer = location.search.match(/(?<=reference=)[\da-w]+/)[0]

            function getCookie(name) {
                let strcookie = document.cookie
                let arrcookie = strcookie.split("; ")
                for (var i = 0; i < arrcookie.length; i++) {
                    var arr = arrcookie[i].split("=")
                    if (arr[0] == name) {
                        return arr[1]
                    }
                }
                return ""
            }

            function getSearch(){

            }

            var request = (url, data) => {
                return new Promise((resolve) => {
                    $.ajax({
                        url: url,
                        method:'get',
                        data: {params:JSON.stringify(data)},
                        dataType: "jsonp",
                        success: (resp) => {
                            resolve(resp)
                        }
                    })
                })
            }

            var getSession = () => {

                return request('https://ws.ovh.com/sessionHandler/r4/ws.dispatcher/getAnonymousSession', {
                    params: { "language": "ie" }
                })
            }

            var checkAvailability = () => {
                return request('https://ws.ovh.com/order/dedicated/servers/ws.dispatcher/getPossibleOptionsAndAvailability', {
                    "sessionId": sessionId,
                    "billingCountry": "KSEU",
                    "dedicatedServer": dedicatedServer,
                    "installFeeMode": "directly",
                    "duration": "1m"
                })
            } 


            var dig = (function($) {
                var link = '/cart.php?a=add&pid=%id&billingcycle=annually',
                    retry = 0;

                var el
                var storage = window.localStorage

                function init() {
                  el = $('<button style="position:fixed;left:0;bottom:0;z-index:9999;background:#65c178;padding:15px;color:#fff" id="__ssc__" onclick="window.dig()" class="btn">自动检测</button>')
                  $('body').append(el);
                }

                function process() {
                    el.prop('disabled', true).html('运行中(' + retry++ + ')');

                    checkAvailability().then((resp)=>{
                      console.log(resp)
                    })

                    // $.get(link).then(function(resp) {
                    //     if (resp.indexOf('Out of Stock') >= 0) {
                    //         setTimeout(process, 3000);
                    //     } else {
                    //         location.href = link;
                    //     }
                    // }, function() {
                    //     setTimeout(process, 3000);
                    // });
                }

                function check(id) {
                    if(!sessionId){
                      alert('请先登录')
                    }else{
                      process()
                    }
                }

                init();

                return check;
            }(jQuery));

            window.dig = dig;
        }

        nw.addScript(';(' + script + '());', 'body');

    }
});

//==================================
nw.init();