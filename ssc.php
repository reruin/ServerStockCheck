<?php
    /****************************************
     *  VERSION : 0.1.2
     *  DATE    : 2018-08-20
     *
     *  Copyright (C) 201x (reruin#gmail.com) 
     *
     *  This is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the 
     *  Free Software Foundation, either version 2 of the License, or(at your option) any later version.
     *  
     *  This is distributed in the hope that it will be useful,but WITHOUT ANY WARRANTY;  without even the implied warranty of MERCHANTABILITY      
     *  or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details. 
     *
     *  You should have received a copy of the GNU General Public License along with Foobar. If not, see <http://www.gnu.org/licenses/>.
     *
     * changelog
     * 2018-08-20
     * + 添加消息通知
     * * 修改ks的监测逻辑
     * * 新的样式
     *
     * 2017-03-12
     * + 添加对wsi的支持
     *
     *
     *****************************************/

    // $options = array(
    //     'http' => array(
    //     'method' => 'GET',
    //     'header' => 'Content-type:application/x-www-form-urlencoded',
    //     'content' => $postdata,
    //     'timeout' => 15 * 60
    //     )
    // );

    function request($url){
        $context = array(   
            'http' => array (   
                'header'=> 'User-Agent: ' . $_SERVER['HTTP_USER_AGENT']   
            )   
        );
        $xcontext = stream_context_create($context);
        $resp = file_get_contents($url);

        return $resp;
    }

    function _request($url, $data = null){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $info = curl_exec($curl);
        curl_close($curl);
        return $info;
    }

    function checkOnline($id){
        $resp = file_get_contents("https://console.online.net/en/order/server");
        $start = strpos($resp , $id);
        $end = strpos($resp , '</form>',$start);
        $m = substr($resp , $start , $end - $start);
        header('Content-type: application/json');

        if($m && strpos($m , 'disabled') === false ){
            echo('{"status":true}');
        }
        else{
            echo('{"status":false}');
        }
        exit();
    }

    function _checkKimsufi($id){
        $resp = file_get_contents( "https://www.kimsufi.com/en/order/kimsufi.cgi?hard=" . $id);

        $status = array(
            'status'=> strpos($resp , 'icon-availability') !== false
        );
        header('Content-type: application/json');
        echo( json_encode($status));
        exit();
    }

    function checkKimsufi($id){
        $resp = request( "https://www.ovh.com/engine/api/dedicated/server/availabilities?country=ie&hardware=".$id);
        
        $status = array(
            'status'=> preg_match('/availability":"(?!unavailable)/' , $resp) === 1
        );
        header('Content-type: application/json');
        echo( json_encode($status));
        exit();
    }

    function checkSys($id){
        $resp = request( "https://ca.ovh.com/engine/api/dedicated/server/availabilities?country=we&hardware=1801armada01".$id);
        
        $status = array(
            'status'=> preg_match('/availability":"(?!unavailable)/' , $resp) === 1
        );
        header('Content-type: application/json');
        echo( json_encode($status));
        exit();
    }

    function checkWSI($id){
        $resp = file_get_contents( "https://www.wholesaleinternet.net/out-of-stock/?id=" . $id);
        $status = array(
            'status'=> strpos($resp , 'out of stock') === false
        );
        header('Content-type: application/json');
        echo( json_encode($status));
        exit();
    }

    function checkCommon($url , $f){
        $resp = file_get_contents( $url );
        $status = array(
            'status'=> strpos($resp , $f) === false && strpos($resp , 'Cloudflare') === false
        );
        header('Content-type: application/json');
        echo( json_encode($status));
        exit();
    }

    if(!empty($_GET['id']) && !empty($_GET['type'])){
        $id = $_GET['id'];
        $type = $_GET['type'];

        if( $type == 'online'){
            checkOnline($id);
        }
        else if($type == 'wsi'){
            checkWSI($id);
        }
        else if($type == 'ks'){
            checkKimsufi($id);
        }
        else if($type == 'sys'){
            checkSys($id);
        }
        else if($type == 'common'){
            $url = urldecode($_GET['u']);
            $f = $_GET['f'];
            checkCommon($url , $f);
        }
    }

    if($_GET['a']=='proxy' && !empty($_GET['url'])){
        $url = urldecode($_GET['url']);
        echo( request($url) );
        exit();
    }
?>

<!doctype html>
<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8">
    <title>SSC 库存状态监控</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, minimal-ui">
    <meta name="screen-orientation" content="portrait"/>
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="format-detection" content="telephone=no">
    <meta name="full-screen" content="yes">
    <meta name="x5-fullscreen" content="true">
    <style type="text/css">
    *{box-sizing: border-box;}
    html{
        background: #ecf0f5;
    }
    body {
        margin: 0;
        font-family: "microsoft yahei", simhei;
        font-size: 12px;
        -webkit-font-smoothing: antialiased;
    }
    
    ul,
    ol,
    li,
    dl,
    dd,
    dt,
    p,
    h1,
    h2,
    h3,
    h4,
    h5,
    h6,
    form,
    input {
        margin: 0;
        padding: 0;
    }
    
    img,
    fieldset {
        border: none;
    }
    
    li {
        list-style: none;
    }
    
    select,
    input,
    img {
        vertical-align: middle;
    }
    
    select,
    input,
    textarea {
        font-size: 12px;
    }
    
    a {
        color: #010101;
        text-decoration: none;
    }
    
    a:hover {
        color: #4c7d08;
        text-decoration: underline;
    }
    
    div,
    ul,
    dl {
        zoom: 1;
    }
    
    div:after,
    ul:after,
    dl:after {
        content: "";
        display: block;
        clear: both;
        height: 0;
        visibility: hidden;
    }
    
    html{
        -webkit-tap-highlight-color: rgba(0,0,0,0);
    }

    header {
        padding: 35px 20px 15px 20px;
        color:#0d1a26;
        display: flex;
        justify-content: space-between;
        align-items:center;
    }
    header h2{
        font-size: 26px;
        font-weight: normal;
        display: inline-block;
    }
    header div{ display:inline-block;}
    header div:nth-child(n+2){
        margin-left: 15px;
    }
    header select{
        padding:7px;
        border:#d2d6de;
        outline: none;
    }

    header span{color:#666; margin-right:5px;}

    
    footer{
        position:fixed;
        width:100%;
        bottom:0;
        left:0;
        padding: 20px;
    }

    footer p{
        color: #aaa;
        font-size: 12px;
        margin-bottom: 0px;
        text-align: right;
        vertical-align: middle;
        height:16px;
        line-height: 16px;
    }

    footer a.github{ vertical-align: middle; margin-right: 5px;}

    section{
        overflow:auto;
        margin:20px 20px 55px 20px;
        position: relative;
        z-index: 2;
        /*box-shadow: 0 1px 1px rgba(0,0,0,0.1);*/
    }

    table{
        width:100%;background: #fff;
    }
    thead th{
        background-color: #fafafa;
        color: rgba(0,0,0,.85);
        font-weight: 500;
        text-align: left;
    }

    table td:first-child,th:first-child{
        padding-left:25px;
    }
    table td:last-child,th:last-child{
        padding-right:25px;
    }
    tbody td{
        color:rgba(0,0,0,.65);
    }
    table td,th{
        border-bottom: 1px solid #e8e8e8;
        padding:16px;font-size:13px;
    }
    
    @media screen and (max-width:768px) {
      table th:nth-child(3),
      table td:nth-child(3),
      table th:nth-child(6),
      table td:nth-child(6)
      {
        display: none;
      }
    }

    @media screen and (max-width:480px) {
      table th:nth-child(3),
      table td:nth-child(3),
      table th:nth-child(4),
      table td:nth-child(4),
      table th:nth-child(5),
      table td:nth-child(5),
      table th:nth-child(6),
      table td:nth-child(6)
      {
        display: none;
      }
    }
    
    .item span {
        vertical-align: middle;
    }
    
    .item span{ 
        width:100px; 
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        padding-right: 10px;
    }


    .w1 {
        width: 60px;
    }

    .w2{
        width: 180px; 
        color: #333;
    }
    .w3{
        min-width: 100px; 
    }

    .uptime{ padding-left:8px; font-size: 9px; }
    .status{ width:42px; }
    .op{
        text-align:center;
    }
    .op a{ padding: 5px;cursor:pointer;}
    
    .availabe {
        background: #d1f1c2;
    }

    audio{
        opacity: 0;width:1;height:1px;position: absolute;z-index:0;
    }
    </style>
</head>

<body>
    <audio preload loop id="clock">
        <source src="http://www.naobiao.com/web_system/naobiao_com_www/img/music/ka_nong_meng_huan/1.ogg" type="audio/ogg">
        <source src="http://www.naobiao.com/web_system/naobiao_com_www/img/music/ka_nong_meng_huan/1.mp3" type="audio/mpeg">
    </audio>
    <header>
        <h2>库存状态监控</h2>
        <div class='menu'>
            <div>
                <a id="j_notify">设置消息推送</a>
            </div>
            <div class="models"></div>
            <div class="tick">
                <span>刷新时间</span>
                <select>
                    <option value='0'>立即</option>
                    <option value='1000'>1秒</option>
                    <option value='5000' selected>5秒</option>
                    <option value='10000'>10秒</option>
                    <option value='30000'>30秒</option>
                </select>
            </div>
        </div>
    </header>

    <section>
        <table cellspacing="0" cellpadding="0" border="0">
            <thead>
                <tr>
                    <th class="w3">类型</th>
                    <th class="w3">状态</th>
                    <th class="w2">CPU</th>
                    <th>内存</th>
                    <th>磁盘</th>
                    <th>网络</th>
                    <th class="w3">价格</th>
                    <th class="op">操作</th>
                </tr>
            </thead>
            <tbody id="all">
                
            </tbody>
        </table>
    </section>
    

    <footer>
        <p><a class="github" target="_blank" href="https://github.com/reruin/ServerStockCheck"><svg aria-hidden="true" height="16" version="1.1" viewBox="0 0 16 16" width="16"><path fill-rule="evenodd" d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0 0 16 8c0-4.42-3.58-8-8-8z"></path></svg></a>reruin#gmail.com</p>
    </footer>
    <script type="text/javascript" src='//cdn.bootcss.com/jquery/3.2.1/jquery.min.js'></script>
    <!-- <script type="text/javascript" src='https://rawgit.com/reruin/ServerStockCheck/master/lib/ssc.js'></script> -->
    <script type="text/javascript" src='lib/ssc.js'></script>
    <script type="text/javascript">  
        $(function() {

            var store = window.localStorage;

            function template(str, data) {
                return str.replace(/\{ *([\w\.\_]+) *\}/g, function(str, key) {
                    var p = key.split('.');
                    var value;
                    for(var i=0; i<p.length;i++){
                        if(value === undefined) value = data;
                        value = value[p[i]];
                    }
                    if (value === undefined) {
                        console.log('No value provided for variable ' + str);
                        value = "{" + key + "}";
                    } else if (typeof value === 'function') {
                        value = value(data);
                    }
                    return value;
                })
            };

            function create_layout(app) {
                var tpl = "<option value ='{type}--{id}'>{title}</option>",
                    s = "";
                var list = ssc.models;
                for (var i = 0; i < list.length; i++) {
                    s += template(tpl, list[i]);
                }

                s = "<select placeholder='请选择服务器'><option value=''>请选择服务器</option>" + s + "</select>";
                $("header .models").append(s)
                .find("select").change(function() {
                    app.add($(this).val());
                });
                /*
$("<span>添加</span><select>" + s + "</select>")
                .appendTo('header .models')
                .on('select','change' , function() {
                    alert()
                    app.add($(this).val());
                });
                */

                $('header .help a').click(function() {
                    alert('本程序只做学习探讨之用。\r\n reruin#gmail.com\r\n https://github.com/reruin')
                });

                $("header .tick select").change(function() {
                    app.setTick($(this).val());
                });

                $("header .filter select").change(function() {
                    app.setFilter($(this).val());
                });

                $('#all').on('click', 'a.remove' , function(){
                    var id = $(this).attr('data-id');
                    if(window.confirm('确定删除？')){
                        app.remove(id);
                    }
                }).on('click', 'a.j_order' , function(){
                    var id = $(this).attr('data-id');
                    var type = $(this).attr('data-type');
                    var url = ssc.getOrderUrl(id , type);
                    if(url) window.open(url);
                })

                $('#j_notify').on('click' , function(){
                    var default_url = store.notify_url || ''
                    var notify_url = window.prompt('消息推送地址，仅限GET方式。\n1、使用ftqq推送通知，不支持占位符，\n格式 https://sc.ftqq.com/xxxx.send\n2、自定义推送地址，可用使用以下占位符 \n{title} : 名称\n{link}: 下单地址（如果有）\n{type}：类型',default_url)
                    if( notify_url !== null ){
                        store.notify_url = notify_url
                    }
                })

            }

            function save(d){
                store.task = JSON.stringify(d);
            }

            function read(){
                return store.task ? JSON.parse( store.task ) : null;
            }

            function emit(d){
                if(store.notify_url){
                    var url = store.notify_url
                    if(url.indexOf('https://sc.ftqq.com/')>=0){
                        url = url + '?text={type}有货_{time}&desp=[下单]({link})'
                    }
                    
                    var obj = {title:d.data.title , id:d.data.id , type:d.data.type , link:ssc.getOrderUrl(d.id , d.type) , time:Date.now()}
                    $.ajax({
                        url:'?a=proxy&url='+encodeURIComponent(template(url,obj)) ,
                        method:'get',
                        success:function(){

                        }
                    })
                }
            }

            function notify(){
                if($('#all').find('.availabe input:checked').length == 0){
                    $('#clock')[0].pause();
                }else{
                    if( $('#clock')[0].paused ){
                        $('#clock')[0].play();
                    }
                }
            }

            function start() {
                var tpl = '<tr id="item_{type}--{id}">' +
                    '<td>{data.title}</td>' +
                    '<td>' +
                    '<span class="status">{status_str}</span>' +
                    '<span class="uptime"></span>' +
                    '</td>' +
                    '<td>{data.cpu}</td><td>{data.ram}</td><td>{data.disk}</td><td>{data.network}</td><td>{data.price}</td><td class="op"><label><input type="checkbox" checked />音乐提醒</label><a class="remove" data-id="{type}--{id}">移除</a><a href="javascript:void(0)" class="j_order" data-id="{id}" data-type="{type}">下单</a></td>' +
                    '</tr>';

                    //<a href="https://www.kimsufi.com/en/order/kimsufi.cgi?hard={id}" target="_blank">下单</a>
                var app = ssc();

                app
                    .setFilter('local')
                    .on('add', function(data) {
                        $('#all').append(template(tpl, data));
                        save( app.getModels() );
                    })
                    .on('remove' , function(data){
                        var id = data.type+"--"+data.id;
                        $('#item_'+id).remove();
                        save( app.getModels() );
                        notify();
                    })
                    .on('update', function(data) {
                        for (var i in data) {
                            var el = $('#item_' +data[i].type+'--'+ data[i].id);
                            if (el) {
                                el.find('.uptime').html(data[i].uptime + '秒前');
                                el.find('.status').html(data[i].status_str);
                                el.toggleClass('availabe', data[i].status === true);
                            }
                        }
                        notify();
                    })
                    .on('hit', function(data) {
                        emit(data)
                    })

                    .add( read() || ['ks--1801sk12', 'ks--1804sk12']);

                create_layout(app);
            }

            start();
        });
</script>
</body>
</html>
