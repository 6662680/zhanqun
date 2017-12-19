require.config({
    baseUrl:'http://www.weadoc.com/Unionstatic/js',
    paths: {
        jquery: 'jquery.min',
        custom:'module/custom',
        tooltip:'module/easyTooltip',
        pop:'module/pop',
        hint:'module/hint',
        checkout:'module/checkout',
        hoverintent:'module/hoverIntent',
        wysiwyg:'module/jquery.wysiwyg',
        jqui:'module/jquery-ui-1.7.2.custom.min',
        superfish:'module/superfish',
        highcharts:'module/highcharts',
        login:'page/index/index',
        user:'page/index/user'
    },
    shim:{
        'wysiwyg': ['jquery'],
        'jqui': ['jquery'],
        'custom':['jquery','superfish'],
        'tooltip':['jquery'],
        'hoverintent':['jquery'],
        'wysiwyg':['jquery'],
        'jqui':['jquery'],
        'superfish':['jquery'],
        'highcharts':['jquery']
    }
});

switch (weadoc.banner)
{
    case 'Index':
        if(weadoc.action=='index'){
            require(['login']);
        }else if(weadoc.action=='user'){
            require(['user']);
        }
        break;
}