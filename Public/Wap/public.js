/**
 *
 * POST 提交异步请求
 *
 * @param url 调用的url地址
 * @param options json参数
 * @param success 成功后的回调函数
 * @param failed 失败后的回调函数
 */
function ajaxCall(url, options, success, failed) {
    $.ajax({
        type: 'POST',
        url: url, // 请求的url地址
        dataType: 'json', // 返回格式为json
        data: options,
        success: success,
        error: failed
    });
}


/*
 *  AJAX 失败公共方法
 */
function failed() {
    alert("数据链接超时！");
}

$(function () {
//订单状态：0(已取消);10(默认)未付款;20已付款;30:已发货;40:已收货;99:货到付款;
    var sql_order_state = new Array();
    sql_order_state[0] = '已取消';
    sql_order_state[10] = '待付款';
    sql_order_state[20] = '待发货';
    sql_order_state[30] = '已发货';
    sql_order_state[40] = '已收货';

    var sql_order_pay_code = new Array();
    sql_order_pay_code['alipay'] = '(支付宝)';
    sql_order_pay_code['weixin'] = '(微信支付)';
    sql_order_pay_code['hdfk'] = '(货到付款)';

    $(".sql_order_state").each(function () {
        $(this).text(sql_order_state[$(this).text()]);
    })
    $(".sql_order_pay_code").each(function () {
        $(this).text(sql_order_pay_code[$(this).text()]);
    })
});


/*
 *confirm
 */
function showYesOrNo(msg, url) {
    if (confirm(msg)) {
        window.location = url;
    }
}

/*
 * 获取终端型号
 */
function getUserAgent() {
    var mobileAgent = new Array("iphone", "ipod", "ipad", "android", "mobile", "blackberry", "webos", "incognito", "webmate", "bada", "nokia", "lg", "ucweb", "skyfire", "mozilla");
    var browser = navigator.userAgent.toLowerCase();
    var isMobile = false;
    for (var i = 0; i < mobileAgent.length; i++) {
        if (browser.indexOf(mobileAgent[i]) != -1) {
            isMobile = true;
            //alert(mobileAgent[i]);
            //location.href = '手机网址';
            return mobileAgent[i];
        }
    }
    return 'PC';
}

/*动态载入JS,CSS文件*/
function loadjscssfile(filename,filetype){

    if(filetype == "js"){
        var fileref = document.createElement('script');
        fileref.setAttribute("type","text/javascript");
        fileref.setAttribute("src",filename);
    }else if(filetype == "css"){

        var fileref = document.createElement('link');
        fileref.setAttribute("rel","stylesheet");
        fileref.setAttribute("type","text/css");
        fileref.setAttribute("href",filename);
    }
    if(typeof fileref != "undefined"){
        document.getElementsByTagName("head")[0].appendChild(fileref);
    }
}

Date.prototype.Format = function(fmt)
{
    var o = {
        "M+" : this.getMonth()+1,                 //月份
        "d+" : this.getDate(),                    //日
        "h+" : this.getHours(),                   //小时
        "m+" : this.getMinutes(),                 //分
        "s+" : this.getSeconds(),                 //秒
        "q+" : Math.floor((this.getMonth()+3)/3), //季度
        "S"  : this.getMilliseconds()             //毫秒
    };
    if(/(y+)/.test(fmt))
        fmt=fmt.replace(RegExp.$1, (this.getFullYear()+"").substr(4 - RegExp.$1.length));
    for(var k in o)
        if(new RegExp("("+ k +")").test(fmt))
            fmt = fmt.replace(RegExp.$1, (RegExp.$1.length==1) ? (o[k]) : (("00"+ o[k]).substr((""+ o[k]).length)));
    return fmt;
}

//图片预览
function viewImgage(obj){
    if($(obj).attr('src').length>10){
        var html='<img src="'+$(obj).attr('src')+'" />';
    }else{
        var html='没有图片';
    }
    art.dialog({title:'图片预览',content:html,lock:true,background: '#000',opacity: 0.45});
}

function successInitSelect(objStr, outStr, req, text, value) {
    obj = $("select#" + objStr);
    obj.empty();
    obj.append($("<option/>").text(outStr).attr("value", "0"));
    $(req).each(function (i, o) {
        opt = $("<option/>").text(o[text]).attr("value", o[value]);
        obj.append(opt);
    });
    obj.easyDropDown('destroy');
    obj.easyDropDown({cutOff: 12});
}