/**
 * Created by Administrator on 14-10-10.
 */

/**
 * 根据定位结果计算区域的id，并显示
 */
function locate() {
    var addr = $("#curAdr").html();
    if (addr != "") {
        var ary = addr.split(' ');
        // 修改默认值，设置默认选中的省
        $("#province").find("option:contains('" + ary[0] + "')").attr('selected', true);
        $("#province").trigger('change');
        // 设置默认选中的市
        $("#city").find("option:contains('" + ary[1] + "')").attr('selected', true);
        $("#city").trigger('change');
        $("#area").find("option:contains('" + ary[2] + "')").attr('selected', true);
        $("#area").trigger('change');
        // 隐藏区域的Id
        $("#hidAreaId").val($("#city").val());
    }
}

/**
 * 省动，市跟着动
 */
function proChange() {
    var provinceId = $("#province").val();
    $.ajax({
        url: "./index.php?m=Wap&c=User&a=CityInfo",    // 请求的url地址
        dataType: "json",   // 返回格式为json,后台返回的东西不需要是json格式的，直接返回
        async: false,		// 请求是否异步，默认为异步，这也是ajax重要特性
        data: {'proId': provinceId},    //参数值
        type: "get",   //请求方式
        beforeSend: function () {
            //请求前的处理
        },
        success: function (req) {
            successInitSelect('city', '请选择', req, 'city', 'city_id');
            successInitSelect('area', '请选择', '', 'area', 'area_id');
            successInitSelect('community', '选择小区', '', 'community_name', 'community_id');
            successInitSelect('building', '选择楼栋', '', 'building_name', 'building_id');
            successInitSelect('room', '选择房间', '', 'room_name', 'room_id');
        },
        complete: function () {
            // 请求完成的处理
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            // 请求出错处理
            // alert(XMLHttpRequest.statusText + "status:" + textStatus + "errorThrown:" + errorThrown);
        }
    });
}

/**
 * 市动，区也要动
 */
function cityChange() {
    var cityid = $("#city").val();
    $.ajax({
        url: "./index.php?m=Wap&c=User&a=AreaInfo",    // 请求的url地址
        dataType: "json",   // 返回格式为json,后台返回的东西不需要是json格式的，直接返回
        async: false,		// 请求是否异步，默认为异步，这也是ajax重要特性
        data: {'cityId': cityid},    //参数值
        type: "get",   //请求方式
        beforeSend: function () {
            //请求前的处理
        },
        success: function (req) {
            successInitSelect('area', '请选择', req, 'area', 'area_id');
            successInitSelect('community', '请选择', '', 'community_name', 'community_id');
            successInitSelect('building', '选择楼栋', '', 'building_name', 'building_id');
            successInitSelect('room', '选择房间', '', 'room_name', 'room_id');
        },
        complete: function () {
            // 请求完成的处理
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            // 请求出错处理
            // alert(XMLHttpRequest.statusText + "status:" + textStatus + "errorThrown:" + errorThrown);
        }
    });
}

/**
 * 区域变换，加载支持的小区信息
 */
function areaChange() {
    var areaId = $("#area").val();
    $.ajax({
        url: "./index.php?m=Wap&c=User&a=CommInfo",    // 请求的url地址
        dataType: "json",   // 返回格式为json,后台返回的东西不需要是json格式的，直接返回
        async: false,		// 请求是否异步，默认为异步，这也是ajax重要特性
        data: {'areaId': areaId},    //参数值
        type: "get",   //请求方式
        beforeSend: function () {
            //请求前的处理
        },
        success: function (req) {
            successInitSelect('community', '选择小区', req, 'community_name', 'community_id');
            successInitSelect('building', '选择楼栋', '', 'building_name', 'building_id');
            successInitSelect('room', '选择房间', '', 'room_name', 'room_id');
        },
        complete: function () {
            // 请求完成的处理
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            // 请求出错处理
            // alert(XMLHttpRequest.statusText + "status:" + textStatus + "errorThrown:" + errorThrown);
        }
    });
}

/**
 * 小区变换，加载小区下的楼栋信息
 */
function commChange() {
    var commId = $("#community").find('option:selected').val();
    $.ajax({
        url: "./index.php?m=Wap&c=User&a=BuildingInfo",    // 请求的url地址
        dataType: "json",   // 返回格式为json,后台返回的东西不需要是json格式的，直接返回
        async: false,		// 请求是否异步，默认为异步，这也是ajax重要特性
        data: {'commId': commId},    //参数值
        type: "get",   //请求方式
        beforeSend: function () {
            //请求前的处理
        },
        success: function (req) {
            successInitSelect('building', '选择楼栋', req, 'building_name', 'building_id');
            successInitSelect('room', '选择房间', '', 'room_name', 'room_id');
        },
        complete: function () {
            // 请求完成的处理
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            // 请求出错处理
            // alert(XMLHttpRequest.statusText + "status:" + textStatus + "errorThrown:" + errorThrown);
        }
    });
}

/**
 * 楼栋变化，加载房号信息列表
 */
function buildChange() {
    var buildId = $("#building").find('option:selected').val();
    $.ajax({
        url: "./index.php?m=Wap&c=User&a=RoomInfo",    // 请求的url地址
        dataType: "json",   // 返回格式为json,后台返回的东西不需要是json格式的，直接返回
        async: false,		// 请求是否异步，默认为异步，这也是ajax重要特性
        data: {'buildId': buildId},    //参数值
        type: "get",   //请求方式
        beforeSend: function () {
            //请求前的处理
        },
        success: function (req) {
            successInitSelect('room', '选择房间', req, 'room_name', 'room_id');
        },
        complete: function () {
            // 请求完成的处理
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            // 请求出错处理
            // alert(XMLHttpRequest.statusText + "status:" + textStatus + "errorThrown:" + errorThrown);
        }
    });
}

/**
 * 保存用户的省市区信息
 */
function saveAddr() {
    var detail = $("#province").find('option:selected').text()
        + " " + $("#city").find('option:selected').text()
        + " " + $("#area").find('option:selected').text();
    $("#curAdr").html(detail);

}

/**
 * 存储用户的详细地址
 */
function saveDetail() {
    var detail = "";
    var identity = $('#identity').val();
    if (identity == "owner") {
        detail = $("#community").find('option:selected').text()
            + " " + $("#building").find('option:selected').text()
            + " " + $("#room").find('option:selected').text();
    } else {
        detail = $("#community_a").val()
            + " " + $("#building_a").val()
            + " " + $("#room_a").val()
    }

    $("#detailAdr").val($("#curAdr").html() + " " + detail);
    return true;
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
    obj.easyDropDown({cutOff: 6});
}
