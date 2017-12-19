/**
 * 提示模块
 * author : Tom
 * time : 2015-7-13
 */
define(['jquery'],function($){
    $(function(){
        $hint={
            conf:{
                dom:$('#J_hint'),//选择对象
                direction:'right',//出现方向,top上,bottom 下,left 左,right 右
                width:'100',//提示框宽度
                text:'我是提示框',//提示内容
                bgcolor:'#fbfbfb',//背景颜色
                borcolor:'#666666',//线条颜色
                color:'#e7212f',//文字颜色
                x:0,//x轴位移偏差调整
                y:0,//y轴位移偏差调整
                pause:1000,//出现时间
                zindex:999,//z-index
                callback:function(){}
            },
            init:function(_opt) {
                $('.hintNow').remove();//移除元素
                var _conf = this.conf;
                if (_opt) {//初始化传值
                    $.extend(_conf, _opt);
                }
                var _$=_conf.dom,_style,_html,_xy;
                var _x=_$.offset().top,_y=_$.offset().left;
                switch (_conf.direction){
                    case 'top':
                        _y+=_conf.y;
                        _x+=-50+_conf.x;
                        break;
                    case 'bottom':
                        _y+=_conf.y;
                        _x+=_$.height()+15+_conf.x;
                        break;
                    case 'left':
                        _y+=_conf.y-15+_conf.x;
                        _x+=_$.height()/2-18+_conf.x;
                        break;
                    case 'right':
                        _y+=_$.width()+15+_conf.y;
                        _x+=_$.height()/2-18+_conf.x;
                        break;
                }
                _xy='top:'+_x+'px;left:'+_y+'px;';
                _style='width:'+_conf.width+'px;'+'color:'+_conf.color+';'+'background-color:'+_conf.bgcolor+';'+'border: 1px solid '+_conf.borcolor;
                _html='<div class="hintNow" style="position:absolute;display:none;'+_xy+';z-index:'+_conf.zindex+';"><div class="'+_conf.direction+'Hint" style="'+_style+'">'+_conf.text+'</div></div>';
                $('body').append(_html);
                $('.hintNow').append('<style>.'+_conf.direction+'Hint:after{ border-' +_conf.direction+': 10px solid '+_conf.bgcolor+' } .'+_conf.direction+'Hint:before{ border-'+_conf.direction+': 12px solid '+_conf.borcolor+' }</style>');
                $('.hintNow').fadeIn(500,function(){
                    setTimeout("$('.hintNow').fadeOut(500)",_conf.pause);
                });//展现元素

                _conf.callback();//执行回调函数
            }
        }
    });
});