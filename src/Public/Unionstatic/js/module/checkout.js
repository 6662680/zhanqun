/**
 * 校验模块
 * author : Tom
 * time : 2015-7-10
 */
define(['jquery','hint'],function($){
    $(function() {
            $checkout={
                name:{//姓名验证
                    conf:{
                        dom:$('#J_name'),//默认选取Dom
                        callback:function(){},//回调函数
                        direction:'top',
                        bgcolor:'#fbfbfb',
                        borcolor:'#ff5555',
                        color:'#ff5555',
                        zindex:999//z-index
                    },
                    init:function(_opt){
                        var _conf = this.conf;
                        if(_opt) {//初始化传值
                            $.extend(_conf, _opt);
                        }
                        var _$=_conf.dom;
                        if($checkout.notnull.init({//空值判断
                                dom:_$,
                                text:'请输入姓名',
                                zindex:_conf.zindex
                            })){
                            if(_$.attr("value").length<2 || _$.attr("value").length>20){
                                $hint.init({
                                    dom:_$,
                                    direction:_conf.direction,
                                    text:'姓名输入不合法',
                                    bgcolor:_conf.bgcolor,
                                    borcolor:_conf.borcolor,
                                    color:_conf.color,
                                    zindex:_conf.zindex
                                });
                                _$.focus();
                                return false;
                            }
                        }else{
                            return false;
                        }

                        _conf.callback();
                        return true
                    }
                },

                email:{//邮箱验证
                    conf:{
                        dom:$('#J_email'),//默认选取Dom
                        zindex:999,//z-index
                        callback:function(){}//回调函数
                    },
                    init:function(_opt){
                        var _conf = this.conf;
                        if(_opt) {//初始化传值
                            $.extend(_conf, _opt);
                        }
                        var _$=_conf.dom;
                        if($checkout.notnull.init({//空值判断
                                dom:_$,
                                text:'请输入邮箱',
                                zindex:_conf.zindex
                            })) {
                            var _myreg = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;//邮件正则表达
                            if (!_myreg.test(_$.attr("value"))) {
                                alert('请输入有效的邮箱！');
                                _$.focus();
                                return false;
                            }
                        }
                        _conf.callback();
                        return true
                    }
                },
                Alipay:{//支付宝验证
                    conf:{
                        dom:$('#J_aliPay'),//默认选取Dom
                        direction:'top',
                        bgcolor:'#fbfbfb',
                        borcolor:'#ff5555',
                        color:'#ff5555',
                        zindex:999,//z-index
                        callback:function(){}//回调函数
                    },
                    init:function(_opt){
                        var _conf = this.conf;
                        if(_opt) {//初始化传值
                            $.extend(_conf, _opt);
                        }
                        var _$=_conf.dom;
                        if($checkout.notnull.init({//空值判断
                                dom:_$,
                                text:'请输入支付宝账号',
                                width:'150',
                                zindex:_conf.zindex
                            })) {
                            var _myreg = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;//邮件正则表达
                            var _myregphone = /^(13|15|17|18|14)[0-9]{9}$/;//手机号正则表达
                                if ((!_myreg.test(_$.attr("value")) && (!_myregphone.test(_$.attr("value")) || _$.attr("value").length != 11))){
                                    $hint.init({
                                        dom:_$,//默认选取Dom
                                        direction:_conf.direction,
                                        text:'请输入有效的支付宝！',
                                        width:'200',
                                        bgcolor:_conf.bgcolor,
                                        borcolor:_conf.borcolor,
                                        color:_conf.color,
                                        zindex:_conf.zindex
                                    });
                                _$.focus();
                                return false;
                            }
                        }else{
                            return false;
                        }
                        _conf.callback();
                        return true
                    }
                },
                phone:{//手机验证
                    conf:{
                        dom:$('#J_phone'),//默认选取Dom
                        callback:function(){},//回调函数
                        direction:'top',
                        bgcolor:'#fbfbfb',
                        borcolor:'#ff5555',
                        color:'#ff5555',
                        width:'250',
                        zindex:999//z-index
                    },
                    init:function(_opt){
                        var _conf = this.conf;
                        if(_opt) {//初始化传值
                            $.extend(_conf, _opt);
                        }

                        var _$=_conf.dom;

                        if($checkout.notnull.init({//空值判断
                                dom:_$,
                                text:'请输入手机号码',
                                zindex:_conf.zindex,
                                width:_conf.width
                            })) {
                            var _myreg = /^(13|15|17|18|14)[0-9]{9}$/;//手机号正则表达
                            if (!_myreg.test(_$.attr("value")) || _$.attr("value").length != 11) {//判断手机正则及手机位数
                                $hint.init({
                                    dom:_$,//默认选取Dom
                                    direction:_conf.direction,
                                    text:'请输入有效的手机号码！',
                                    width:_conf.width,
                                    bgcolor:_conf.bgcolor,
                                    borcolor:_conf.borcolor,
                                    color:_conf.color,
                                    zindex:_conf.zindex
                                });
                                _$.focus();
                                return false;
                            }
                        }else{
                            return false;
                        }
                        _conf.callback();
                        return true
                    }

                },

                verify:{//动态验证码验证
                    conf:{
                        dom:$('#J_verify'),//默认选取Dom
                        zindex:999,//z-index
                        callback:function(){}//回调函数
                    },
                    init:function(_opt){
                        var _conf = this.conf;
                        if(_opt) {//初始化传值
                            $.extend(_conf, _opt);
                        }
                        var _$=_conf.dom;
                        if(_$.attr("value").length!=6){//验证码位数
                            alert('验证码不合法');
                            _$.focus();
                            return false;
                        }
                        _conf.callback();
                        return true
                    }
                },

                address:{//地址验证
                    conf:{
                        dom:$('#J_notnull'),//默认选取Dom
                        zindex:999,//z-index
                        direction:'top',
                        bgcolor:'#f2f2f2',
                        borcolor:'#ff5555',
                        color:'#ff5555',
                        callback:function(){}//回调函数
                    },
                    init:function(_opt) {
                        var _conf = this.conf;
                        if (_opt) {//初始化传值
                            $.extend(_conf, _opt);
                        }
                        var _$ = _conf.dom;
                        if (_$.attr("value").length < 5) {//判断地址长度
                            $hint.init({
                                dom:_$,//默认选取Dom
                                direction:_conf.direction,
                                text:'请把地址再填写的详细点！',
                                width:'200',
                                bgcolor:_conf.bgcolor,
                                borcolor:_conf.borcolor,
                                color:_conf.color,
                                zindex:_conf.zindex
                            });
                            _$.focus();
                            return false;
                        }
                        return true;
                    }
                },

                captcha:{//手机验证码验证
                    conf:{
                        dom:$('#J_captcha'),//默认选取Dom
                        zindex:999,//z-index
                        callback:function(){}//回调函数
                    },
                    init:function(_opt){
                        var _conf = this.conf;
                        if(_opt) {//初始化传值
                            $.extend(_conf, _opt);
                        }
                        var _$=_conf.dom;
                        if(_$.attr("value").length!=6){//手机验证码位数
                            alert('手机验证码不合法');
                            _$.focus();
                            return false;
                        }
                        _conf.callback();
                        return true
                    }
                },
                isChecked:{
                    conf:{
                        dom:$('#J_checkbox'),//默认选取Dom
                        direction:'top',
                        bgcolor:'#fbfbfb',
                        borcolor:'#ff5555',
                        color:'#ff5555',
                        zindex:999,//z-index
                        callback:function(){}//回调函数
                    },
                    init:function(_opt) {
                        var _conf = this.conf;
                        if (_opt) {//初始化传值
                            $.extend(_conf, _opt);
                        }
                        var _$ = _conf.dom;
                        if (!_$.prop("checked")) {//判断是否已选
                            $hint.init({
                                dom:_$,//默认选取Dom
                                direction:_conf.direction,
                                text:'请选择！',
                                width:'200',
                                bgcolor:_conf.bgcolor,
                                borcolor:_conf.borcolor,
                                color:_conf.color,
                                zindex:_conf.zindex
                            });
                            _$.focus();
                            return false;
                        }
                        return true;
                    }
                },
                notnull:{//不为空验证
                    conf:{
                        dom:$('.notnull'),//默认选取Dom
                        text:"不能为空",
                        callback:function(){},//回调函数
                        direction:'top',
                        width:'100',
                        zindex:999,//z-index
                        bgcolor:'#fbfbfb',
                        borcolor:'#ff5555',
                        color:'#ff5555'
                    },
                    init:function(_opt){
                        var _conf = this.conf;
                        if(_opt) {//初始化传值
                            $.extend(_conf, _opt);
                        }
                        var _$=_conf.dom;
                        if(_$.attr("value").length==0){//判断位数
                            $hint.init({
                                dom:_$,
                                direction:_conf.direction,
                                text:_conf.text,
                                bgcolor:_conf.bgcolor,
                                borcolor:_conf.borcolor,
                                color:_conf.color,
                                width:_conf.width,
                                zindex:_conf.zindex
                            });
                            _$.focus();
                            return false;
                        }
                        _conf.callback();
                        return true
                    }
                }
            }
        });
});