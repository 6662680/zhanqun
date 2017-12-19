<?php

// +------------------------------------------------------------------------------------------ 
// | Author: liyang <664577655@qq.com>
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 数据验证模型 Dates: 2016-07-29
// +------------------------------------------------------------------------------------------

namespace V2\Model;
//use Think\Model;
use Think\Model;

class PhoneModel extends Model
{

    /*
    * 获取品牌
    * */
    public function brand()
    {
        $brand = M('goods_brand')->order('sort ASC')->select();

        foreach ($brand as &$value) {
            $value['web_image_url'] = 'http://'.$_SERVER["SERVER_NAME"].$value['url'];
            $value['image_url_click'] = 'http://'.$_SERVER["SERVER_NAME"].$value['url_click'];
        }

        return $brand;
    }

    /*
   * 获取品牌mobile
   * */
    public function brand_mobile()
    {
        $brand = M('goods_brand')->order('sort ASC')->select();

        foreach ($brand as &$value) {
            $value['url'] = 'http://'.$_SERVER["SERVER_NAME"].$value['url'];
            $value['url_click'] = 'http://'.$_SERVER["SERVER_NAME"].$value['url_click'];
            $value['wap_url'] = 'http://'.$_SERVER["SERVER_NAME"].$value['wap_url'];

        }

        return $brand;
    }

    /*
    * 获取机型mobile
    * */
    public function pattern_mobile($map,$limit=NULL)
    {
        $model = M('phone');
        $model->where($map);
        $model->field('alias,id,category_id');
        $model->order('id DESC');
        $model->limit($limit);
        $pattern = $model->select();

        /*是首页还是更多*/
        if ($limit) {
            S($map['brand_id'].'mobilePattern',$pattern);
        } else {
            S($map['brand_id'].'mobilePatternMore',$pattern);
        }

        return $pattern;
    }

    /*
     * 获取品牌机型以及常用故障模型
     * */
    public function pattern($id)
    {
        $pattern = M('phone')->where(array('brand_id' => $id))->order('id DESC')->select();

       foreach($pattern as $key => &$value){
            $value['color'] = json_encode(explode(',' , $value['color_id']));
            $value['malfunction'] = $this->getFunction($value['id'],1);

            if (!empty($value['img'])) {
                $value['phone_img'] = $value['img'] = 'http://'.$_SERVER["SERVER_NAME"].$value['img'];
            }
        }

        return $pattern;
    }

    public function color($id)
    {
        return M('goods_color')->find($id);
    }

    /*
     * 获取故障模型
     * */
    public function getFunction($id,$easy=NULL)
    {
        $map = array();

        //判断是否获取所有故障
        if ($easy != 2) {
            $map['is_hot'] = $easy;
        }

        $map['phone_id'] = $id;
        $map['m.id'] = array(array('neq' , 35 ) , array('neq' , 36));

        $model = M('phone_malfunction');
        $model->join('pm left join `malfunction` as m ON m.id = pm.malfunction_id');
        $model->where($map);
        $model->field('pm.id,m.name,m.remark,m.img as easy_function_img, m.img_click as easy_function_img_click , pm.
                price_market as market_price,pm.price_reference as reference_price ,pm.is_color');
       return $model->select();
    }

    /*
     * 内存升级
     * */
    public function MemoryUpgrade()
    {
        return $data = array(
            '0' => array(
                'phone_id'=>32,
                'name'=>'iPhone6',
                'img' => 'http://'.$_SERVER["SERVER_NAME"].'\upload\memoryUpgrade\iPhone.png',
                'rom32'  => array('id'=>'2313','price'=>'288','size'=>'32GB'),
                'romsmall'  => array('id'=>'1008','price'=>'398','size'=>'64GB'),
                'rombig'    => array('id'=>'1009','price'=>'598','size'=>'128GB'),
            ),
            '1' => array(
                'phone_id'=>33,
                'name' => 'iPhone6 plus',
                'img' => 'http://'.$_SERVER["SERVER_NAME"].'\upload\memoryUpgrade\iPhone.png',
                'rom32'  => array('id'=>'2314','price'=>'288','size'=>'32GB'),
                'romsmall'  => array('id'=>'1010','price'=>'398','size'=>'64GB'),
                'rombig'    => array('id'=>'1011','price'=>'598','size'=>'128GB'),
            ),
            '2' => array(
                'phone_id'=>68,
                'name' => 'iPhone6s plus',
                'img' => 'http://'.$_SERVER["SERVER_NAME"].'\upload\memoryUpgrade\iPhone.png',
                'rom32'  => array('id'=>'2315','price'=>'288','size'=>'32GB'),
                'romsmall'  => array('id'=>'1206','price'=>'448','size'=>'64GB'),
                'rombig'    => array('id'=>'1205','price'=>'598','size'=>'128GB'),
                'rom256'    => array('id'=>'2318','price'=>'698','size'=>'256GB'),
            ),
            '3' => array(
                'phone_id'=>67,
                'name' => 'iPhone6s',
                'img' => 'http://'.$_SERVER["SERVER_NAME"].'\upload\memoryUpgrade\iPhone.png',
                'rom32'  => array('id'=>'2316','price'=>'288','size'=>'32GB'),
                'romsmall'  => array('id'=>'1203','price'=>'448','size'=>'64GB'),
                'rombig'    => array('id'=>'1204','price'=>'598','size'=>'128GB'),
                'rom256'    => array('id'=>'2317','price'=>'698','size'=>'256GB'),
            ),
            '4' => array(
                'phone_id'=>18,
                'name' => 'iPad air',
                'img' => 'http://'.$_SERVER["SERVER_NAME"].'\upload\memoryUpgrade\iPad.png',
                'romsmall'  => array('id'=>'1017','price'=>'398','size'=>'64GB'),
                'rombig'    => array('id'=>'1018','price'=>'598','size'=>'128GB'),
            ) ,
            '5' => array(
                'phone_id'=>15,
                'name' => 'iPad air2',
                'img' => 'http://'.$_SERVER["SERVER_NAME"].'\upload\memoryUpgrade\iPad.png',
                'romsmall'  => array('id'=>'1046','price'=>'398','size'=>'64GB'),
                'rombig'    => array('id'=>'1047','price'=>'598','size'=>'128GB'),
            ),
            '6' => array(
                'phone_id'=>12,
                'name' => 'iPad mini 2',
                'img' => 'http://'.$_SERVER["SERVER_NAME"].'\upload\memoryUpgrade\iPad.png',
                'romsmall'  => array('id'=>'1022','price'=>'398','size'=>'64GB'),
                'rombig'    => array('id'=>'1015','price'=>'598','size'=>'128GB'),
            ),
            '7' => array(
                'phone_id'=>9,
                'name' => 'iPad mini 3',
                'img' => 'http://'.$_SERVER["SERVER_NAME"].'\upload\memoryUpgrade\iPad.png',
                'romsmall'  => array('id'=>'1014','price'=>'398','size'=>'64GB'),
                'rombig'    => array('id'=>'1016','price'=>'598','size'=>'128GB'),
            ),
        );
    }

    /*
     * 活动logo
     * */
    public function activity()
    {
        return $data = array(
            '0' => array(
                'id'=>1,
                'name'=>'苹果',
                'img1' => 'http://'.$_SERVER["SERVER_NAME"].'\upload\brand\tueiguang\pingguo.png',
                'img2' => 'http://'.$_SERVER["SERVER_NAME"].'\upload\brand\tueiguang\pingguoc.png',
            ),
            '1' => array(
                'id'=>3,
                'name'=>'小米',
                'img1' => 'http://'.$_SERVER["SERVER_NAME"].'\upload\brand\tueiguang\xiaomi.png',
                'img2' => 'http://'.$_SERVER["SERVER_NAME"].'\upload\brand\tueiguang\xiaomic.png',
            ),
            '2' => array(
                'id'=>4,
                'name'=>'华为',
                'img1' => 'http://'.$_SERVER["SERVER_NAME"].'\upload\brand\tueiguang\huawei.png',
                'img2' => 'http://'.$_SERVER["SERVER_NAME"].'\upload\brand\tueiguang\huaweic.png',
            ),
            '3' => array(
                'id'=>5,
                'name'=>'三星',
                'img1' => 'http://'.$_SERVER["SERVER_NAME"].'\upload\brand\tueiguang\sanxing.png',
                'img2' => 'http://'.$_SERVER["SERVER_NAME"].'\upload\brand\tueiguang\sanxingc.png',
            ),
            '4' => array(
                'id'=>6,
                'name'=>'魅族',
                'img1' => 'http://'.$_SERVER["SERVER_NAME"].'\upload\brand\tueiguang\meizu.png',
                'img2' => 'http://'.$_SERVER["SERVER_NAME"].'\upload\brand\tueiguang\meizuc.png',
            ),
            '5' => array(
                'id'=>10,
                'name'=>'oppo',
                'img1' => 'http://'.$_SERVER["SERVER_NAME"].'\upload\brand\tueiguang\oppo.png',
                'img2' => 'http://'.$_SERVER["SERVER_NAME"].'\upload\brand\tueiguang\oppoc.png',
            ),
            '6' => array(
                'id'=>11,
                'name'=>'vivo',
                'img1' => 'http://'.$_SERVER["SERVER_NAME"].'\upload\brand\tueiguang\vivo.png',
                'img2' => 'http://'.$_SERVER["SERVER_NAME"].'\upload\brand\tueiguang\vivoc.png',
            ),

        );
    }


    /*
    *  更换外壳
    * */
    public function replaceShell()
    {
        return $data = array(
            '0' => array(
                'phone_id'=>32,
                'name'=>'iPhone6',
                'color' => array(

                        '0' => array('code'=>'#fbb','id'=>'1398','color_id'=>7,'name'=>'紫薇粉','wap_img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\wap_ziweifen.png','img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\ziweifen-.png'),
                        '1' => array('code'=>'#FFC0CB','id'=>'1396','color_id'=>14,'name'=>'玫瑰金','wap_img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\wap_meigueijin.png','img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\meigueijin-.png'),
                        '2' => array('code'=>'#ff5c8f','id'=>'1399','color_id'=>11,'name'=>'牵牛紫','wap_img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\wap_qianniuzi.png','img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\qianniuzi-.png'),
                        '3' => array('code'=>'#333333','id'=>'1397','color_id'=>5,'name'=>'烟墨黑','wap_img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\wap_xunyanhei.png','img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\xunyanhei-.png'),
                        '4' => array('code'=>'#ecccbe','id'=>'1812','color_id'=>1,'name'=>'金色后壳（改iPhone7外观)','wap_img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\wap_meigueijin.png','img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\meigueijin-.png'),
                        '5' => array('code'=>'#333333','id'=>'1813','color_id'=>5,'name'=>'亚黑色后壳（改iPhone7外观)','wap_img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\wap_xunyanhei.png','img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\xunyanhei-.png'),
                        '6' => array('code'=>'#FF3333','id'=>'2256','color_id'=>12,'name'=>'熔岩红彩色后壳','wap_img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\waprongyanhongiphone6.png','img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\rongyanhongiphone7.png'),
                        '7' => array('code'=>'#FF3333','id'=>'2262','color_id'=>12,'name'=>'熔岩红后壳（改iPhone7外观)','wap_img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\waprongyanhongiphone.png','img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\rongyanhongiphone7.png'),
                        '8' => array('code'=>'#000000','id'=>'2266','color_id'=>15,'name'=>'亮黑色后壳（改iPhone7外观)','wap_img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\wap_lianghei.png','img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\lianghei.png'),
                ),

            ),

            '1' => array(
                'phone_id'=>33,
                'name'=>'iPhone6 Plus',
                'color' => array(
                    '0' => array('code'=>'#fbb','id'=>'1402','color_id'=>7,'name'=>'紫薇粉','wap_img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\wap_ziweifen.png','img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\ziweifen-.png'),
                    '1' => array('code'=>'#FFC0CB','id'=>'1400','color_id'=>14,'name'=>'玫瑰金','wap_img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\wap_meigueijin.png','img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\meigueijin-.png'),
                    '2' => array('code'=>'#ff5c8f','id'=>'1403','color_id'=>11,'name'=>'牵牛紫','wap_img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\wap_qianniuzi.png','img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\qianniuzi-.png'),
                    '3' => array('code'=>'#333333','id'=>'1401','color_id'=>5,'name'=>'烟墨黑','wap_img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\wap_xunyanhei.png','img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\xunyanhei-.png'),
                    '4' => array('code'=>'#dfe1e3','id'=>'1816','color_id'=>2,'name'=>'银色后壳（iPhone7Plus）','wap_img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\wap_xunyanhei.png','img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\xunyanhei-.png'),
                    '5' => array('code'=>'#F0E0CF','id'=>'1817','color_id'=>1,'name'=>'金色后壳（iPhone7Plus）','wap_img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\wap_meigueijin.png','img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\meigueijin-.png'),
                    '6' => array('code'=>'#FF3333','id'=>'2257','color_id'=>12,'name'=>'熔岩红彩色后壳','wap_img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\waprongyanhongiphone6.png','img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\rongyanhongiphone7.png'),
                    '7' => array('code'=>'#FF3333','id'=>'2263','color_id'=>12,'name'=>'熔岩红后壳（改iPhone7Plus外观)','wap_img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\waprongyanhongiphone7.png','img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\rongyanhongiphone7.png'),
                    '8' => array('code'=>'#000000','id'=>'2267','color_id'=>15,'name'=>'亮黑色后壳（改iPhone7Plus外观)','wap_img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\wap_lianghei.png','img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\lianghei.png'),
                ),

            ),

            '2' => array(
                'phone_id'=>67,
                'name'=>'iPhone6s',
                'color' => array(
                    '0' => array('code'=>'#fbb','id'=>'2173','color_id'=>7,'name'=>'紫薇粉','wap_img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\wap_ziweifen.png','img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\ziweifen-.png'),
                    '1' => array('code'=>'#FF3333','id'=>'2258','color_id'=>12,'name'=>'熔岩红彩色后壳','wap_img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\waprongyanhongiphone6.png','img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\rongyanhongiphone7.png'),
                    '2' => array('code'=>'#ff5c8f','id'=>'2174','color_id'=>11,'name'=>'牵牛紫','wap_img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\wap_qianniuzi.png','img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\qianniuzi-.png'),
                    '3' => array('code'=>'#333333','id'=>'2172','color_id'=>5,'name'=>'烟墨黑','wap_img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\wap_xunyanhei.png','img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\xunyanhei-.png'),
                    '4' => array('code'=>'#333333','id'=>'1821','color_id'=>5,'name'=>'亚黑色后壳（改iPhone7外观)','wap_img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\wap_xunyanhei.png','img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\xunyanhei-.png'),
                    '5' => array('code'=>'#ecccbe','id'=>'1822','color_id'=>14,'name'=>'玫瑰金后壳（改iPhone7外观)','wap_img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\wap_meigueijin.png','img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\meigueijin-.png'),
                    '6' => array('code'=>'#FFC0CB','id'=>'1820','color_id'=>1,'name'=>'金色后壳（改iPhone7外观)','wap_img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\wap_meigueijin.png','img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\meigueijin-.png'),
                    '7' => array('code'=>'#FF3333','id'=>'2264','color_id'=>12,'name'=>'熔岩红后壳（改iPhone7外观)','wap_img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\waprongyanhongiphone.png','img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\rongyanhongiphone7.png'),
                    //'8' => array('code'=>'#000000','id'=>'2268','color_id'=>15,'name'=>'亮黑色后壳（改iPhone7外观)','wap_img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\wap_lianghei.png','img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\lianghei.png'),
                ),

            ),

            '3' => array(
                'phone_id'=>68,
                'name'=>'iPhone6s Plus',
                'color' => array(
                    '0' => array('code'=>'#000000','id'=>'2042','color_id'=>15,'name'=>'亮黑色后壳（改iPhone7Plus外观)','wap_img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\wap_lianghei.png','img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\lianghei.png'),
                    '1' => array('code'=>'#333333','id'=>'2041','color_id'=>5,'name'=>'亚黑色后壳（改iPhone7Plus外观)','wap_img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\wap_meigueijin.png','img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\meigueijin-.png'),
                    '2' => array('code'=>'#ecccbe','id'=>'2040','color_id'=>14,'name'=>'玫瑰金后壳（改iPhone7Plus外观)','wap_img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\wap_meigueijin.png','img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\meigueijin-.png'),
                    '3' => array('code'=>'#FFC0CB','id'=>'2039','color_id'=>1,'name'=>'金色后壳（改iPhone7Plus外观)','wap_img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\wap_meigueijin.png','img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\meigueijin-.png'),
                    '4' => array('code'=>'#dfe1e3','id'=>'2038','color_id'=>2,'name'=>'银色后壳（改iPhone7Plus外观)','wap_img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\wap_meigueijin.png','img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\meigueijin-.png'),

                    '5' => array('code'=>'#333333','id'=>'2175','color_id'=>5,'name'=>'烟墨黑彩色后壳','wap_img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\wap_xunyanhei.png','img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\xunyanhei-.png'),
                    '6' => array('code'=>'#FF9999','id'=>'2176','color_id'=>7,'name'=>'紫薇粉彩色后壳','wap_img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\wap_ziweifen.png','img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\ziweifen-.png'),
                    '7' => array('code'=>'#990099','id'=>'2177','color_id'=>11,'name'=>'牵牛紫彩色后壳','wap_img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\wap_qianniuzi.png','img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\qianniuzi-.png'),
                    '8' => array('code'=>'#FF3333','id'=>'2265','color_id'=>12,'name'=>'熔岩红后壳（改iPhone7Plus外观)','wap_img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\waprongyanhongiphone7.png','img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\rongyanhongiphone7.png'),
                    '9' => array('code'=>'#FF3333','id'=>'2259','color_id'=>12,'name'=>'熔岩红彩色后壳','wap_img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\waprongyanhongiphone6.png','img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\rongyanhongiphone7.png'),
                    //'9' => array('code'=>'#ecccbe','id'=>'2038','color_id'=>7,'name'=>'熔岩红后壳（iPhone7plus）','wap_img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\wap_meigueijin.png','img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\meigueijin-.png'),
                ),

            ),
            '4' => array(
                'phone_id'=>129,
                'name'=>'iPhone7',
                'color' => array(
                    '0' => array('code'=>'#FF3333','id'=>'2260','color_id'=>12,'name'=>'熔岩红彩色后壳','wap_img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\waprongyanhongiphone7.png','img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\rongyanhongiphone7.png'),
                ),

            ),

            '5' => array(
                'phone_id'=>130,
                'name'=>'iPhone7 Plus',
                'color' => array(
                    '0' => array('code'=>'#FF3333','id'=>'2261','color_id'=>12,'name'=>'熔岩红彩色后壳','wap_img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\waprongyanhongiphone7.png','img'=>'http://'.$_SERVER["SERVER_NAME"].'\upload\replaceShell\rongyanhongiphone7.png'),
                ),

            ),
        );

    }

    /*
     * 更换电池
     * */
    public function replaceBattery()
    {
        return $data = array(
            '0' => array(
                'phone_id'=>67,
                'name'=>'iPhone6s',
                'malfunction'  => array('id'=>'1057','price'=>'189'),
            ),
            '1' => array(
                'phone_id'=>68,
                'name' => 'iPhone6s plus',
                'malfunction'  => array('id'=>'1061','price'=>'210'),
            ),
            '2' => array(
                'phone_id'=>100,
                'name'=>'iPhone SE',
                'malfunction'  => array('id'=>'1541','price'=>'170'),
            ),
            '3' => array(
                'phone_id'=>32,
                'name'=>'iPhone6',
                'malfunction'  => array('id'=>'45','price'=>'150'),
            ),
            '4' => array(
                'phone_id'=>33,
                'name' => 'iPhone6 plus',
                'malfunction'  => array('id'=>'11','price'=>'150'),
            ),
            '5' => array(
                'phone_id'=>31,
                'name' => 'iPhone5s',
                'malfunction'  => array('id'=>'65','price'=>'120'),
            ),
            '6' => array(
                'phone_id'=>30,
                'name' => 'iPhone5c',
                'malfunction'  => array('id'=>'112','price'=>'120'),
            ),
            '7' => array(
                'phone_id'=>29,
                'name' => 'iPhone5',
                'malfunction'  => array('id'=>'86','price'=>'120'),
            ),
            '8' => array(
                'phone_id'=>28,
                'name' => 'iPhone4S',
                'malfunction'  => array('id'=>'134','price'=>'89'),
            ),
            '9' => array(
                'phone_id'=>27,
                'name' => 'iPhone4',
                'malfunction'  => array('id'=>'167','price'=>'89'),
            ),

        );
    }

    /**
     * 数据线 and 钢化膜
     */
    public function usbSticker()
    {
        return $data = array(
            '0' => array(
                'phone_id'=>129,
                'name'=>'iPhone7',
            ),
            '1' => array(
                'phone_id'=>130,
                'name'=>'iPhone7 Plus',
            ),
            '2' => array(
                'phone_id'=>68,
                'name'=>'iPhone6s Plus',
            ),
            '3' => array(
                'phone_id'=>67,
                'name'=>'iPhone6s',
            ),
            '4' => array(
                'phone_id'=>32,
                'name'=>'iPhone6',
            ),
            '5' => array(
                'phone_id'=>33,
                'name' => 'iPhone6 plus',
            ),
            '6' => array(
                'phone_id'=>31,
                'name' => 'iPhone5s',
            ),
            '7' => array(
                'phone_id'=>30,
                'name' => 'iPhone5c',
            ),
            '8' => array(
                'phone_id'=>29,
                'name' => 'iPhone5',
            ),
            '9' => array(
                'phone_id'=>28,
                'name' => 'iPhone4S',
            ),
            '10' => array(
                'phone_id'=>27,
                'name' => 'iPhone4',
            ),
            '11' => array(
                'phone_id'=>39,
                'name' => '华为P7',
            ),
            '12' => array(
                'phone_id'=>38,
                'name' => '华为P8',
            ),
            '13' => array(
                'phone_id'=>37,
                'name' => '华为P8 MAX',
            ),
            '14' => array(
                'phone_id'=>90,
                'name' => '华为P9',
            ),
            '15' => array(
                'phone_id'=>92,
                'name' => '华为P9 plus',
            ),
            '16' => array(
                'phone_id'=>36,
                'name' => '华为荣耀6',
            ),
            '17' => array(
                'phone_id'=>35,
                'name' => '华为荣耀6 plus',
            ),
            '18' => array(
                'phone_id'=>34,
                'name' => '华为荣耀7',
            ),
            '19' => array(
                'phone_id'=>40,
                'name' => '华为mate7',
            ),
            '20' => array(
                'phone_id'=>81,
                'name' => '华为mate8',
            ),
            '21' => array(
                'phone_id'=>49,
                'name' => '小米4',
            ),
            '22' => array(
                'phone_id'=>110,
                'name' => '小米5',
            ),
            '23' => array(
                'phone_id'=>110,
                'name' => '小米5s',
            ),
            '24' => array(
                'phone_id'=>50,
                'name' => '小米NOTE',
            ),
            '25' => array(
                'phone_id'=>44,
                'name' => '红米NOTE',
            ),
            '26' => array(
                'phone_id'=>43,
                'name' => '红米2',
            ),
            '27' => array(
                'phone_id'=>63,
                'name' => 'GALAXY S5',
            ),
            '28' => array(
                'phone_id'=>62,
                'name' => 'GALAXY S6',
            ),
            '29' => array(
                'phone_id'=>108,
                'name' => 'GALAXY S3',
            ),
            '30' => array(
                'phone_id'=>74,
                'name' => '三星手机s6 edge',
            ),
            '31' => array(
                'phone_id'=>91,
                'name' => 'Galaxy S7 edge',
            ),
            '32' => array(
                'phone_id'=>94,
                'name' => 'Galaxy C5',
            ),
            '33' => array(
                'phone_id'=>99,
                'name' => 'GALAXY NOTE5',
            ),
            '34' => array(
                'phone_id'=>126,
                'name' => 'GALAXY NOTE7',
            ),
            '35' => array(
                'phone_id'=>135,
                'name' => 'VIVO X7',
            ),
            '36' => array(
                'phone_id'=>134,
                'name' => 'vivo X7Plus',
            ),
            '37' => array(
                'phone_id'=>127,
                'name' => 'vivo X6',
            ),
            '38' => array(
                'phone_id'=>0,
                'name' => 'OPPO R7',
            ),
            '39' => array(
                'phone_id'=>140,
                'name' => 'OPPO R9',
            ),
            '40' => array(
                'phone_id'=>139,
                'name' => 'OPPO R9Plus',
            ),
            '41' => array(
                'phone_id'=>116,
                'name' => '魅族MX5',
            ),
            '42' => array(
                'phone_id'=>115,
                'name' => '魅族MX6',
            ),
            '43' => array(
                'phone_id'=>119,
                'name' => '魅族PRO 6',
            ),
            '44' => array(
                'phone_id'=>120,
                'name' => '魅族PRO 5',
            ),
            '45' => array(
                'phone_id'=>149,
                'name' => '魅族MAX',
            ),
            '46' => array(
                'phone_id'=>153,
                'name' => '魅蓝3',
            ),
            '47' => array(
                'phone_id'=>122,
                'name' => '魅蓝E',
            )
        );
    }

    /*
     * 更换屏幕
     * */
    public function replaceScreen()
    {
        return $data = array(

            '0' => array(
                'phone_id'=>129,
                'name'=>'iPhone7',
                'outside'  => array('id'=>'1544','price'=>'699','name'=>'外屏碎裂'),
                'inner'  => array('id'=>'1545','price'=>'1500','name'=>'内屏异常'),
            ),
            '1' => array(
                'phone_id'=>130,
                'name' => 'iPhone7 plus',
                'outside'  => array('id'=>'1549','price'=>'786','name'=>'外屏碎裂'),
                'inner'  => array('id'=>'1550','price'=>'1700','name'=>'内屏异常'),
            ),
            '2' => array(
                'phone_id'=>67,
                'name'=>'iPhone6s',
                'outside'  => array('id'=>'1005','price'=>'388','name'=>'外屏碎裂'),
                'inner'  => array('id'=>'943','price'=>'746','name'=>'内屏异常'),
            ),
            '3' => array(
                'phone_id'=>68,
                'name' => 'iPhone6s plus',
                'outside'  => array('id'=>'942','price'=>'468','name'=>'外屏碎裂'),
                'inner'  => array('id'=>'944','price'=>'1100','name'=>'内屏异常'),
            ),
            '4' => array(
                'phone_id'=>32,
                'name'=>'iPhone6',
                'outside'  => array('id'=>'35','price'=>'320','name'=>'外屏碎裂'),
                'inner'  => array('id'=>'36','price'=>'580','name'=>'内屏异常'),
            ),
            '5' => array(
                'phone_id'=>33,
                'name' => 'iPhone6 plus',
                'outside'  => array('id'=>'1002','price'=>'349','name'=>'外屏碎裂'),
                'inner'  => array('id'=>'5','price'=>'650','name'=>'内屏异常'),
            ),
            '6' => array(
                'phone_id'=>100,
                'name' => 'iPhone SE',
                'outside'  => array('id'=>'1321','price'=>'240','name'=>'外屏碎裂'),
                'inner'  => array('id'=>'1322','price'=>'360','name'=>'内屏异常'),
            ),
            '7' => array(
                'phone_id'=>31,
                'name' => 'iPhone5s',
                'outside'  => array('id'=>'58','price'=>'240','name'=>'外屏碎裂'),
                'inner'  => array('id'=>'59','price'=>'360','name'=>'内屏异常'),
            ),
            '8' => array(
                'phone_id'=>30,
                'name' => 'iPhone5c',
                'outside'  => array('id'=>'105','price'=>'240','name'=>'外屏碎裂'),
                'inner'  => array('id'=>'106','price'=>'360','name'=>'内屏异常'),
            ),
            '9' => array(
                'phone_id'=>29,
                'name' => 'iPhone5',
                'outside'  => array('id'=>'79','price'=>'240','name'=>'外屏碎裂'),
                'inner'  => array('id'=>'80','price'=>'360','name'=>'内屏异常'),
            ),
            '10' => array(
                'phone_id'=>28,
                'name' => 'iPhone4S',
                'outside'  => array('id'=>'127','price'=>'260','name'=>'外屏碎裂'),
                'inner'  => array('id'=>'128','price'=>'260','name'=>'内屏异常'),
            ),
            '11' => array(
                'phone_id'=>27,
                'name' => 'iPhone4',
                'outside'  => array('id'=>'148','price'=>'260','name'=>'外屏碎裂'),
                'inner'  => array('id'=>'149','price'=>'260','name'=>'内屏异常'),
            ),

        );
    }
}
