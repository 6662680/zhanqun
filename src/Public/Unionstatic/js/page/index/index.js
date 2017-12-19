/**
 * 网站联盟登录JS
 * author : Tom
 * time : 2015-11-30
 */
define(['jquery','pop','hint','checkout'],function($){
    $(function(){
        $('#J_submit').click(function(){
            $.ajax({
                url: weadoc.path + "/index/user_ajax",
                type: "post",
                dataType: "json",
                data: {username:$('#J_username').val(),password:$('#J_password').val()},

                success: function(data){
                    if(data.status == 1){
                        location.href= weadoc.path + '/index/user';
                    }else{
                        $hint.init({ dom:$('#J_username'),
                            direction:'bottom',
                            width:'auto',
                            text:'账号或密码错误',
                            zindex:1001
                        });
                    }
                }
            });
        });


        $('#J_registerBtn').on('click',function(){
            var _html = '<div class="registerWrap"><p class="registerRow"><label>手机：</label><input type="text" id="J_registerPhone" placeholder="请输入手机号码"><input type="button" id="J_sendCode" value="发送验证码"><span class="again">60秒</span></p><p class="registerRow"><label>验证码：</label><input type="text"  style="width: 100px;" id="J_registerCode" placeholder="请输入验证码"></p><p class="registerRow"><label>密码：</label><input type="password" id="J_registerPassword" placeholder="请输入密码"></p><p class="registerRow"><label>确认密码：</label><input type="password" id="J_registerPwdAgain" placeholder="确认密码"></p><p class="registerBtnWrap"><input type="button" class="register" id="J_registerCommitBtn" value="注册"></p></div>';
            $popUpBox.init({
                html:_html,
                close:true,
                callback:function(){
                    //发送验证码
                    $('#J_sendCode').on('click',function(){
                        var _this = $(this);
                        if(_this.hasClass('disabledBtn')){
                            return;
                        }

                        if(!$checkout.phone.init({
                                dom:$('#J_registerPhone'),
                                direction:'bottom',
                                width:'150',
                                zindex:1001
                            })){
                            return;
                        }

                        _this.addClass('disabledBtn');
                        $.ajax({
                            url: weadoc.path + "/Note/sendCode",
                            type: "post",
                            dataType: "json",
                            data: {phoneNumber:$('#J_registerPhone').val()},
                            success: function(data){
                                if(data.status!=1)
                                {
                                    $hint.init({ dom:$('#J_registerPhone'),
                                        direction:'bottom',
                                        width:'auto',
                                        text:data.msg,
                                        zindex:1001
                                    });
                                    _this.val('重新发送').removeClass('disabledBtn');
                                }else{

                                    $('.again').show();
                                    $('#J_sendCode').hide();
                                    var timer,minute=60;
                                    timer=setInterval('timelyFun()',1000); //每隔时间执行
                                    timelyFun=function(){
                                        if(minute==0){
                                            $('.again').hide().text('60秒');
                                            $('#J_sendCode').show().val('重新发送').removeClass('disabledBtn');
                                            clearInterval(timer);
                                        }else{
                                            $('.again').html(minute+'秒');
                                        }

                                        minute--;
                                    };
                                }
                            }
                        });

                    });
                    //注册提交
                    $('#J_registerCommitBtn').on('click',function(){
                        var _this = $(this);
                        if(_this.hasClass('disabledBtn')){
                            return;
                        }
                        if(!$checkout.phone.init({
                                dom:$('#J_registerPhone'),
                                direction:'bottom',
                                width:'auto',
                                zindex:1001
                            })){
                            return;
                        }
                        if(!$('#J_registerPassword').val()){
                            $hint.init({ dom:$('#J_registerPassword'),
                                direction:'bottom',
                                width:'auto',
                                text:'请输入密码',
                                zindex:1001
                            });
                            return;
                        }
                        if($('#J_registerPwdAgain').val()!=$('#J_registerPassword').val()){
                            $hint.init({ dom:$('#J_registerPwdAgain'),
                                direction:'bottom',
                                width:'auto',
                                text:'两次输入的密码不相同',
                                zindex:1001
                            });
                            return;
                        }

                        _this.addClass('disabledBtn');

                        $.ajax({
                            url: weadoc.path + "/Note/verifyCode",
                            type: "post",
                            dataType: "json",
                            data: {
                                phoneNumber: $('#J_registerPhone').val(),
                                code: $('#J_registerCode').val()
                            },
                            success: function (data) {
                                if(data.status == 0) {
                                    $hint.init({ dom:$('#J_registerCode'),
                                        direction:'bottom',
                                        width:'auto',
                                        text:data.msg,
                                        zindex:1001
                                    });
                                    _this.removeClass('disabledBtn');
                                }else{
                                    $.ajax({
                                        url: weadoc.path + "/index/register",
                                        type: "post",
                                        dataType: "json",
                                        data: {user:$('#J_registerPhone').val(),password:$('#J_registerPassword').val()},
                                        success: function(data){
                                            if(data.status==0){
                                                $hint.init({ dom:$('#J_registerPhone'),
                                                    direction:'bottom',
                                                    width:'auto',
                                                    text:data.msg,
                                                    zindex:1001
                                                });
                                                _this.removeClass('disabledBtn');
                                            }else if(data.status==1){
                                                $popUpBox.init({
                                                    html:'<p style="background:#fff;padding:35px 55px;">注册成功</p>',
                                                    close:true,
                                                    callback:function(){
                                                        setInterval(function(){
                                                            location.href= weadoc.path + '/index/user';
                                                        },2000);
                                                    }
                                                });
                                            }
                                        }
                                    });
                                }
                            }
                        });

                    });
                }
            });
        });

    });
});