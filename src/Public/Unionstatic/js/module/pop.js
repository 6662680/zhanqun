/**
 * 弹出层模块
 * author : Tom
 * time : 2015-7-9
 */
define(['jquery'],function($){
    $(function(){
        $popUpBox={
            conf:{
                id:"",//外框ID设置
                title:'',//默认标题
                html:'',//弹出框Html
                close:true,//是否有关闭框
                bgcolor:"#000",//弹出层背景颜色
                pellucidity:'30',//弹出层背景透明度
                cartoon:'',//出现动画 '':无动画,top:从上出现,bottom:从下出现,shade:渐变出现,left:从左出现,right:从右出现
                speed:'1000',//动画速度
                callback:function(){},//回调事件
                clearcallback:function(){}
            },
            init:function(_opt){
                var _self=this;
                _self.clear();//清除原先节点
                var _conf = this.conf;
                if(_opt) {//初始化传值
                    $.extend(_conf, _opt);
                }
                if(_conf.close){
                    _conf.html+='<div class="icon close hand"></div>';
                }
                //生成弹出层和背景层
                if(_conf.title!=''){
                    $('body').append('<div class="P_popBg"></div><div class="P_popMain" id="'+_conf.id+'"><div class="defalut">'+_conf.title+'</div>'+_conf.html+'</div>');
                }else{
                    $('body').append('<div class="P_popBg"></div><div class="P_popMain" id="'+_conf.id+'">'+_conf.html+'</div>');
                }
                var _bg=$('.P_popBg') , _m=$('.P_popMain') , _s=parseFloat(_conf.speed);

                _bg.css({//背景初始化
                    'height':$(window).height(),
                    'background-color':_conf.bgcolor,
                    'filter':'alpha(opacity='+_conf.pellucidity+')',
                    '-moz-opacity':_conf.pellucidity/100,
                    'opacity':_conf.pellucidity/100
                });
                $('main').css({
                '-webkit-filter': 'blur(3px)',
                '-moz-filter': 'blur(3px)',
                '-o-filter': 'blur(3px)',
                '-ms-filter': 'blur(3px)',
                'filter': 'blur(3px)'
                })

                _m.css({//弹出层初始化
                    'margin-top':'-'+_m.height()/2+'px',
                    'margin-left':'-'+_m.width()/2+'px'
                });


               //生成出现动画效果
                switch (_conf.cartoon)
                {
                    case '':
                        _m.show();
                        _conf.callback();
                        break;
                    case 'top':
                        _m.css('top','-'+_m.height()+'px');
                        _m.show();
                        _m.animate({top:'50%'},_s,function(){
                            _conf.callback();
                        });
                        break;
                    case 'bottom':
                        _m.css('top','120%');
                        _m.show();
                        _m.animate({top:'50%'},_s,function(){
                            _conf.callback();
                        });
                        break;
                    case 'left':
                        _m.css('left','-'+_m.width()+'px');
                        _m.show();
                        _m.animate({left:'50%'},_s,function(){
                            _conf.callback();
                        });
                        break;
                    case 'right':
                        _m.css('left','120%');
                        _m.show();
                        _m.animate({left:'50%'},_s,function(){
                            _conf.callback();
                        });
                        break;
                    case 'shade':
                        _m.show(_s,function(){
                            _conf.callback();
                        });
                        break;
                }

                $('.P_popMain>.close').live('click',function(){
                   _self.clear();
                });
            },
            clear:function(){
                this.conf.clearcallback();
                $('.P_popBg').remove();
                $('.P_popMain').remove();
                this.conf.title='';
                this.conf.html='';
                $('main').css({
                    '-webkit-filter': 'blur(0px)',
                    '-moz-filter': 'blur(0px)',
                    '-o-filter': 'blur(0px)',
                    '-ms-filter': 'blur(0px)',
                    'filter': 'blur(0px)'
                })
            }
        }
    });
});