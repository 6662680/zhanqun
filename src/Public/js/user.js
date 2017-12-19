// 密码验证
$.extend($.fn.validatebox.defaults.rules, {
    confirmPass: {
        validator: function(value, param){
            var pass = $(param[0]).passwordbox('getValue');
            return value == pass;
        },
        message: '两次密码输入不一致！'
    }
})

// 单选验证
$.extend($.fn.validatebox.defaults.rules, {
    requireRadio: {  
        validator: function(value, param){
            return $(param[0] + ':checked').val() != undefined;
        },  
        message: '请选择一个！'  
    }  
});

// 状态显示
function formatStatus(val,row) {  
    if(val == 1) {  
        return '<span class="label label-bg2">启用</span>';  
    } else if (val == 0) {  
        return '<span class="label label-bg3">禁用</span>';  
    } else if (val == -1) {
        return '<span class="label label-bg3">删除</span>';
    }
}

// 时间格式化
function formatDate(val, row) {
	
	val = parseInt(val);
    if (val <= 0) { 
        return '';
    }
    
    var oDate = new Date(val * 1000),  
    oYear = oDate.getFullYear(),  
    oMonth = oDate.getMonth()+1,  
    oDay = oDate.getDate(),
    oHour = oDate.getHours(),
    oMinute = oDate.getMinutes(),
    oSeconds = oDate.getSeconds();
    
    oMonth = parseInt(oMonth) < 10 ? '0'+oMonth : oMonth;
    oDay = parseInt(oDay) < 10 ? '0'+oDay : oDay;
    oHour = parseInt(oHour) < 10 ? '0'+oHour : oHour;
    oMinute = parseInt(oMinute) < 10 ? '0'+oMinute : oMinute;
    oSeconds = parseInt(oSeconds) < 10 ? '0'+oSeconds : oSeconds;
    
    return oYear +'-'+ oMonth +'-'+ oDay +' '+ oHour +':'+ oMinute +':'+ oSeconds;
}


// 自定义时间格式
function formatCustomDate(val) {
	
	val = parseInt(val);
    if (val <= 0) { 
        return '';
    }
	
    var date = new Date(val*1000);
    var fmt = 'MM/dd/yyyy';
    var o = {
        "M+": date.getMonth() + 1, //月份
        "d+": date.getDate(), //日
        "h+": date.getHours(), //小时
        "m+": date.getMinutes(), //分*
        "s+": date.getSeconds(), //秒
    };
    if (/(y+)/.test(fmt))
        fmt = fmt.replace(RegExp.$1, (date.getFullYear() + "").substr(4 - RegExp.$1.length));
    for (var k in o)
        if (new RegExp("(" + k + ")").test(fmt))
            fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
    return fmt;
}

//行操作按钮
function formatRowActionButton() {
    return $('#rowmenu').html();
}

//点击行操作按钮设置当前行选中
function selectedRow(obj, $t_obj) { 
    $t_obj = $t_obj || $('#dg');
    if (obj.parent().parent().parent().attr('datagrid-row-index') == 'undefined') {
        $t_obj.datagrid('selectRow', obj.parent().parent().parent().attr('node-id'));
    } else {
        $t_obj.datagrid('selectRow', obj.parent().parent().parent().attr('datagrid-row-index'));
    }
}

//显示区域伸缩
function toggleBox(index)
{
    var rowObj = $('.datagrid-btable tr');
    
    if (typeof(index) == 'undefined') {
        $('div.cls_toggle').each(function(i) {
            
            if ($(this).height() == 42) {
                $(this).css({'height':'auto', 'overflow': 'auto', 'overflow-x': 'hidden'});
                $(this).parent().parent().parent().css('height', 'auto');
                
                if (rowObj != 'undefined') {
                    rowObj.eq(i).css('height', $(this).parent().parent().parent().height()+'px');
                }
            } else {
                $(this).css({'height':'42px', 'overflow': 'hidden'});
                $(this).parent().parent().parent().css('height', 'auto');
                
                if (rowObj != 'undefined') {
                    rowObj.eq(i).css('height', '43px');
                }
            }
        });
    } else {
        index = index || 0;
        $('#dg').datagrid('selectRow', index);
        var obj = $('.cls_toggle'+index);
        var pobj = obj.parent().parent().parent();
        
        if (obj.height() == 42) {
            obj.css({'height':'auto', 'overflow': 'auto', 'overflow-x': 'hidden'});
            pobj.css('height', 'auto');
            
            if (rowObj != 'undefined') {
                rowObj.eq(index).css('height', pobj.height() +'px');
            }
        } else {
            obj.css({'height':'42px', 'overflow': 'hidden'});
            pobj.css('height', 'auto');
            
            if (rowObj != 'undefined') {
                rowObj.eq(index).css('height', '43px');
            }
        }
    }
}