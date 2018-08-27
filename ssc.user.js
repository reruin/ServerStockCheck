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
        addStyle : function(css){
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
                } else if (handler.pre) {
                  handler.pre(handler.args);
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            if (handlers.length) {
                handlers.forEach(function(handler) {
                    if (handler.post) {
                        handler.post(handler.args);
                    }
                });
            }
        })
    }

    nw.c = create
    nw.init = init
    nw.addScript = page.addScript
    nw.addStyle = page.addStyle
    setTimeout(()=>{
      init()
    },0)
}(this));


/**
 * ssc
 *
 */

//https://www.kimsufi.com/en/order/kimsufi.xml?reference=1801sk12
nw.c({
  rule: /www\.kimsufi\.com\/(us\/)?en\/servers.xml/,
  pre: function() {},
  post: function() {
    nw.addStyle('.hide-on-ref-unavailable{display:table-cell !important;}.show-on-ref-unavailable{display:none !important;}')
  }
})
//https://www.kimsufi.com/en/order/kimsufi.xml?reference=1801sk12
nw.c({
    rule: /www\.kimsufi\.com\/(us\/)?en\/order\/kimsufi\.xml\?reference=/,
    pre: function() {},
    post: function() {
        var script = function() {
            const request = (function(){

                let __jsnop__ = window.__jsnop__ = {}

                const http = (url , data = {}) => new Promise((resolve) => {
                  jQuery.ajax({
                    url , data,
                    method:'get',
                    dataType:'json',
                    timeout:2000,
                    success:(resp)=>{
                      resolve(resp)
                    },
                    error:()=>{
                      resolve(false)
                    }
                  })
                })

                const jsonp = (url , data = {}) => new Promise((resolve) => {
                  url = url + '?callback=?'
                  for(let i in data){
                    url += '&' + i+'='+JSON.stringify(data[i])
                  }

                  var callbackName = 'jsonp_callback_' + new Date().getTime()

                  var timer = window.setTimeout(() =>{
                    resolve(false)
                    window.clearTimeout(timer)
                    delete __jsnop__[callbackName]
                    if(script) document.head.removeChild(script)

                  }, 2000)

                  __jsnop__[callbackName] = function (data) {
                      if(timer) window.clearTimeout(timer)
                      resolve(data)
                      delete __jsnop__[callbackName]
                      document.head.removeChild(script)
                  };

                  var script = document.createElement('script');
                  script.src = url.replace("=?", "=" + "__jsnop__." + callbackName);
                  document.head.appendChild(script);
                })

                return http
            }())




            const ks = (function(request){


              /**
               * 获取匿名Session
               */
              const getAnonymousSession = (language = 'ie') => {
                return request('https://ws.ovh.com/sessionHandler/r4/ws.dispatcher/getAnonymousSession', {
                    params: JSON.stringify({ language })
                })
              }

              /**
               * 检测库存 ， 接口调用配额
               * 匿名 500 per 3600 seconds
               * 实名 250 per 3600 seconds
               */
              const checkAvailability = (sessionId , dedicatedServer , billingCountry , language) => {
                return request('https://ws.ovh.com/order/dedicated/servers/ws.dispatcher/getPossibleOptionsAndAvailability', {
                  params:JSON.stringify({
                      sessionId,
                      billingCountry,
                      dedicatedServer,
                      "installFeeMode": "directly",
                      "duration": "1m"
                  })
                }).then(resp=>{
                  return new Promise((resolve)=>{
                    if(resp.error){
                      resolve(false)
                    }else{
                      let invalid = (resp.answer[0].zones || []).every( i=>(i.availability == -1))
                      resolve(invalid)
                    }
                  })
                })
              }

              /**
               * 另一种检测库存 ， 显然是有缓存
               */
              const checkAvailabilityFromGlobal = ( sessionId , dedicatedServer , billingCountry , language) => {
                return request('https://www.ovh.com/engine/api/dedicated/server/availabilities', {
                  country:language,
                  hardware:dedicatedServer,
                }).then(resp=>{
                  return new Promise((resolve)=>{
                    if(/availability":"(?!unavailable)/.test(JSON.stringify(resp))){
                      resolve(true)
                    }else{
                      resolve(false)
                    }
                  })
                })
              }

              /**
               * 获取当前session
               * {"answer":{"__class":"sessionType:session","language":"ie","billingCountry":"KSEU","id":"classic/wt33749-ks-28f7ff5d15ffaac0eb0d161e3d8c5a35","startDate":"2018-08-23T11:50:34+02:00","login":"wt33749-ks"},"version":"1.0","error":null,"id":0}
               */
              const sessionHandler = (sessionId)=>{
                return request('https://ws.ovh.com/sessionHandler/r4/ws.dispatcher/fetch', {
                    params: JSON.stringify({ sessionId:sessionId })
                })
              }


              /**
               * 登录，返回
               * {"answer":{"__class":"sessionType:sessionWithToken","session":{"__class":"sessionType:session","language":"ie","billingCountry":"KSEU","id":"order/wt33749-ks-28f7ff5d15ffaac0eb0d161e3d8c5a35","startDate":"2018-08-23T11:50:34+02:00","login":"wt33749-ks"},"token":null},"version":"1.0","error":null,"id":0}
               */
              const signin = (email , password , language = 'ie')=>{
                return request('https://ws.ovh.com/sessionHandler/r4/ws.dispatcher/login', {
                    params: JSON.stringify({
                      "login":email,
                      "password":password,
                      "language":language,
                      "company":"KS",
                      "context":"order"
                    })
                })
              }

              const ping = (url)=>{
                 return request(url)
              }


              // 下单 ，返回结果
              // {"answer":{"__class":"orderCreateType:priceAndPaymentStructInformation","vat":"0.00","totalPriceWithVat":"17.98","orderId":"****","totalPriceWithoutVat":"17.98","vatRate":0,"publicUrl":"https://www.kimsufi.com/en/cgi-bin/order/displayOrder.cgi?orderId=****&orderPassword=****"},"version":"1.0","error":null,"id":0}
              const createOrder = (sessionId , dedicatedServer , quantity , billingCountry) =>{
                // return new Promise((resolve)=>{
                //   resolve({"answer":{"__class":"orderCreateType:priceAndPaymentStructInformation","vat":"0.00","totalPriceWithVat":"17.98","orderId":"****","totalPriceWithoutVat":"17.98","vatRate":0,"publicUrl":"https://www.kimsufi.com/en/cgi-bin/order/displayOrder.cgi?orderId=****&orderPassword=****"},"version":"1.0","error":null,"id":0})
                // })

                return request('https://ws.ovh.com/order/dedicated/servers/ws.dispatcher/createOrder', {
                  params:JSON.stringify({
                    "sessionId": sessionId,
                    "billingCountry": billingCountry,
                    "dedicatedServer": dedicatedServer,
                    "installFeeMode": "directly",
                    "duration": "1m",
                    "zone":"default",
                    "quantity":quantity,
                    "dryRun":false,
                    "acceptContracts":true,
                    "giveUpRetractation":null,
                    "paymentMeanId":null,
                    "promotionCode":null,
                    "throughAgora":false
                  })
                })
              }

              class KsWatch {
                constructor(manager , {dedicatedServer , quantity , sessionId , language , billingCountry }){
                  this.dedicatedServer = dedicatedServer
                  this.quantity = quantity
                  this.sessionId = sessionId
                  this.language = language
                  this.billingCountry = billingCountry

                  this.manager = manager
                  this.retry = 0
                  this.watchHandler = null
                }

                process(){
                  checkAvailabilityFromGlobal(this.sessionId , this.dedicatedServer , this.billingCountry , this.language).then((resp)=>{
                    if(resp === false){
                      this.nextTick()
                    }else{

                      createOrder(this.sessionIdUser , this.dedicatedServer, this.quantity , this.billingCountry).then(resp=>{
                        if(!resp.error && resp.answer && resp.answer.orderId){
                          this.handleSuccess(resp.answer)
                        }else{
                          this.nextTick()
                        }
                      })
                    }
                  })
                }

                setQuantity(v){
                  if(isNaN(v)){
                    this.quantity = v
                  }
                }

                setSessionId(v){
                  if(v != this.sessionId ){
                    this.sessionId = v
                  }
                }

                start(){
                  this.process()
                  return this
                }

                nextTick(){
                  this.manager.emit('update' , { serverId: this.dedicatedServer , data:{ retry: this.retry++ } })
                  this.watchHandler = setTimeout(()=>{
                    this.process()
                  }, 2000)
                }

                handleSuccess(data){
                  this.manager.emit('success' , { serverId: this.dedicatedServer , data })
                }
              }

              class KsManage {
                constructor({ email , password , language = 'ie' , sessionId }){

                  this.language = language
                  this.listeners = {}

                  this.email = email
                  this.password = password

                  this.watchers = {}

                  if(this.email && this.password){
                    this.signin()
                  }else if(sessionId){
                    this.signinBySession(sessionId)
                  }

                  this.ping()

                }

                signin(){
                  signin(this.email , this.password , this.language).then(resp=>{
                    if(resp === false){
                      window.setTimeout(()=>{
                        this.signin(this.email , this.password , this.language)
                      }, 1000)
                    }else{
                      if(resp.error){
                        this.emit('error.siginin' , resp.error.message)
                      }else{
                        let session = resp.answer.session
                        this.billingCountry = session.billingCountry
                        this.sessionId = session.id
                        this.language = session.language
                        this.emit('ready')
                      }
                    }

                  })
                }

                signinBySession(id){
                  sessionHandler(id).then(resp=>{
                    if(resp === false){
                      window.setTimeout(()=>{
                        this.signinBySession(id)
                      }, 1000)
                    }else{
                      if(resp.error){
                        this.emit('error.siginin' , resp.error.message)
                      }else{
                        let session = resp.answer
                        this.billingCountry = session.billingCountry
                        this.sessionId = session.id
                        this.language = session.language
                        this.emit('ready')
                      }
                    }
                  })
                }

                start(){

                }

                // 型号 数量
                watch(dedicatedServer , quantity){
                  if(!this.watchers[dedicatedServer]){
                    this.watchers[dedicatedServer] = new KsWatch(this , {
                      "sessionId": this.sessionId,
                      "billingCountry": this.billingCountry,
                      "language":this.language,
                      dedicatedServer,
                      quantity,
                    }).start()
                  }else{
                    this.watchers[dedicatedServer].setQuantity(quantity)
                  }
                }

                updateWatchers(){
                  for(let i in this.watchers){
                    this.watchers[i].setSessionId( this.sessionId )
                  }
                }

                ping(){
                  if( this.sessionId ){
                    sessionHandler( this.sessionId ).then(resp=>{
                      if(resp === false){
                        window.setTimeout(()=>{
                          this.ping()
                        }, 2000)
                      }else{
                        if(resp.error){
                          window.setTimeout(()=>{
                            this.ping()
                          }, 2000)
                        }else{
                          let session = resp.answer
                          

                          if(session.id != this.sessionId){
                            this.updateWatchers()
                          }
                          this.billingCountry = session.billingCountry
                          this.sessionId = session.id
                          this.language = session.language

                          setTimeout(()=>{
                            this.ping()
                          }, 60 * 1000)
                        }
                      }
                    })
                  }else{
                    setTimeout(()=>{
                      this.ping()
                    }, 60 * 1000)
                  }
                  /*
                  ping(location.href).then((resp)=>{
                    setTimeout(()=>{
                      this.ping()
                    }, 60 * 1000)
                  })
                  */
                }


                emit(evt , data){
                  let listeners = this.listeners
                  if(listeners[evt]){
                    listeners[evt].forEach(i=>{
                      i( data )
                    })
                  }
                }

                on(evt , handler){
                  let listeners = this.listeners
                  if(!listeners[evt]){
                    listeners[evt] = []
                  }
                  listeners[evt].push( handler )

                  return this
                }
              }

              return (opts)=>(new KsManage(opts))

            }(request));


            const getCookie = (name) => {
                let strcookie = document.cookie
                let arrcookie = strcookie.split("; ")

                for (let i = 0; i < arrcookie.length; i++) {
                    var arr = arrcookie[i].split("=")
                    if (arr[0] == name) {
                        return arr[1]
                    }
                }
                return ""
            }


            const dig = (function($){
                let retry = 0 , el

                let sessionId = getCookie('KSOrderSessionID')
                let dedicatedServer = location.search.match(/(?<=reference=)[\da-w]+/)[0]
                let quantity = 1
                let ksManage = null

                function init() {
                  el = $('<button style="border:none;position:fixed;left:0;top:50%;z-index:9999;background:#65c178;padding:15px;color:#fff;opacity:1 !important;" id="__ssc__" onclick="window.dig()">自动检测</button><div id="j_login_box" class="login-box"><div class="header">登录</div><div class="item"><span>Email</span><input type="text" id="j_email" value="" placeholder="邮箱" /></div><div class="item"><span>Password</span><input id="j_passwd" type="password" value="" placeholder="密码" /></div><div class="item center"><button id="j_signin_btn">登录</button></div></div>')
                  $('body').append(el)

                  el.on('click' , '#j_signin_btn' , function(){
                    $(this).prop('disabled', true)
                    login()
                  })


                }

                function login(){
                  let email = $('#j_email').val()
                  let password = $('#j_passwd').val()
                  ksManage = ks({email , password})
                  start()
                }

                function login_auto(sessionId){
                  ksManage = ks({sessionId})
                  start()
                }

                function start(){
                  ksManage.on('ready' , ()=>{
                    $('#j_signin_btn').prop('disabled', false)

                    $('#j_login_box').fadeOut()
                    ksManage.watch( dedicatedServer , quantity )
                  }).on('update' , (resp)=>{
                    el.prop('disabled', true).html('检测中(' + resp.data.retry + ')')
                  }).on('success' , (resp)=>{
                    location.href = resp.data.publicUrl
                  }).on('error.siginin' , (error)=>{
                    alert(error)
                  })
                }


                init()

                return function(){
                  if(!sessionId){
                    setTimeout(()=>{
                      $('#j_login_box').fadeIn()
                    },100)
                  }else{
                    login_auto(sessionId)
                  }
                }

            }(jQuery));

            window.dig = dig
        }

        nw.addScript(';(' + script + '());', 'body')
        nw.addStyle('.login-box{display:none;position:fixed;top:50%;left:50%;width:450px;padding:0 12px;transform: translate(-50%,-50%);background:#fff;box-shadow:0 0 3px rgba(0,0,0,.3);}.login-box .header{padding:12px;font-size:15px;color:#333;border-bottom:1px solid #eee;}.login-box .item{ margin:12px; display:flex;align-items:center;}.login-box .item span{ flex:0 0 80px; font-size:12px;}.login-box .item input{padding:8px;flex:1 1 auto;}.login-box button{background:#65c178;padding:8px 24px;}.login-box .item.center{justify-content:center;}')

    }
});