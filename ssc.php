<?php
    /****************************************
     *  VERSION : v.20170312
     *  DATE    : 2016-10-06
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
     *****************************************/

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

    function checkKimsufi($id){
        $resp = file_get_contents( "https://www.kimsufi.com/en/order/kimsufi.cgi?hard=" . $id);
        $status = array(
            'status'=> strpos($resp , 'icon-availability') !== false
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
    }
?>

<!doctype html>
<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8">
    <title>SSC 库存状态监控</title>
    <style type="text/css">
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
        padding: 15px 20px;
        color:#6c6c6c;
    }
    header h2{
        font-size: 20px;
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

    header .menu{
        float:right;
    }
    footer{
        position:fixed;
        width:100%;
        bottom:0;
        left:0;
        margin:10px 0;
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
        margin:20px;
        box-shadow: 0 1px 1px rgba(0,0,0,0.1);
    }

    .item {
        position:relative;
        height: 54px;
        line-height: 54px;
        font-size: 12px;
        margin-bottom: 0px;
        border-bottom: #ececec 1px solid;
        cursor: default;
        background-color: #fff;
        padding:0 15px;
        font-size: 12px;
        color:#6c6c6c;
    }
    .item.head{
        background-color: #f9f9f9;
        height:48px;
        line-height: 48px;
        font-size: 12px;
        color:#000;
        font-weight: 500;
    }

    .item span {
        width: 75px;
        display: inline-block;
    }
    
    .item span{ width:100px; }
    .item span.w1 {
        width: 60px;
    }

    .item span.w2{
        width: 150px; 
        color:#333;
    }
    .item span.w3{
        width: 120px; 
    }

    .item span .uptime{ padding-left:8px; font-size: 9px; }
    .item span .status{ width:42px; }
    .item span.op{
        position:absolute;right:0;
        text-align:right;width:150px !important;
    }
    .item span.op a{ padding: 5px;}
    .item.head{
        border-bottom: #9ed8ec 2px solid;
    }

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

    <section id = 'all'>
        <div class="head item">
            <span>类型</span>
            <span class="w2">状态</span>
            <span class="w3">CPU</span>
            <span>内存</span>
            <span>磁盘</span>
            <span>网络</span>
            <span class="w3">价格</span>
        </div>
    </section>

    <footer>
        <p><a class="github" href="https://github.com/reruin"><svg aria-hidden="true" height="16" version="1.1" viewBox="0 0 16 16" width="16"><path fill-rule="evenodd" d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0 0 16 8c0-4.42-3.58-8-8-8z"></path></svg></a>reruin#gmail.com</p>
    </footer>
    <script type="text/javascript" src='//cdn.bootcss.com/jquery/3.2.1/jquery.min.js'></script>
    <script type="text/javascript" src='https://rawgit.com/reruin/ServerStockCheck/master/lib/ssc.js'></script>
    <!-- <script type="text/javascript" src='lib/ssc.js'></script> -->
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
                var tpl = "<option value ='{type}_{id}'>{title}</option>",
                    s = "";
                var list = ssc.models;
                for (var i = 0; i < list.length; i++) {
                    s += template(tpl, list[i]);
                }

                s = "<span>添加</span><select>" + s + "</select>";
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
                    alert('本程序只做学习探讨之用。\r\n reruin@gmail.com\r\n https://github.com/reruin')
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
                })

            }

            function save(d){
                store.task = JSON.stringify(d);
            }

            function read(){
                return store.task ? JSON.parse( store.task ) : null;
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
                var tpl = '<div class="item" id="item_{id}">' +
                    '<span>{data.title}</span>' +
                    '<span class="w2">' +
                    '<span class="status">{status_str}</span>' +
                    '<span class="uptime"></span>' +
                    '</span>' +
                    '<span class="w3">{data.cpu}</span><span>{data.ram}</span><span>{data.disk}</span><span>{data.network}</span><span class="w3">{data.price}</span><span class="op"><label><input type="checkbox" checked />音乐提醒</label><a href="#" class="remove" data-id="{id}">移除</a></span>' +
                    '</div>';

                    //<a href="https://www.kimsufi.com/en/order/kimsufi.cgi?hard={id}" target="_blank">下单</a>
                var app = ssc();

                app
                    .setFilter('local')
                    .on('add', function(data) {
                        $('#all').append(template(tpl, data));
                        save( app.getModels() );
                    })
                    .on('remove' , function(data){
                        var id = data.id;
                        $('#item_'+id).remove();
                        save( app.getModels() );
                        notify();
                    })
                    .on('update', function(data) {
                        for (var i in data) {
                            var el = $('#item_' + data[i].id);
                            if (el) {
                                el.find('.uptime').html(data[i].uptime + '秒前');
                                el.find('.status').html(data[i].status_str);
                                el.toggleClass('availabe', data[i].status === true);
                            }
                        }
                        notify();
                    })

                    .add( read() || ['ks_162sk32', 'ks_162sk42']);

                create_layout(app);
            }

            start();
        });
</script>
</body>
</html>
