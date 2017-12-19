<?php
namespace Api\Controller;

use Admin\Model\EngineerModel;
use Think\Controller;
/**
 * 获取品牌手机故障等数据
 * author :liyang
 * time : 2016-8-8
 */

class PhoneTypeController extends BaseController {

//    public function _initialize()
//    {
//        S(array('type'=>'redis'));
//    }

    /**
     * 获取品牌
     */
    public function brand()
    {
        if (!empty(S('brand'))) {
            $brand = S('brand') ;
        } else {
            $brand = D('PhoneType')->brand();
        }

        if (empty($brand)){
            $this->_error(403,'非法访问');
        } else {
            $this->_callBack($brand);
        }
    }

    /**
     * 获取品牌mobile
     */
    public function brand_mobile()
    {
        if (!empty(S('brand_mobile'))) {

            $brand = S('brand_mobile') ;
        } else {

            $brand = D('PhoneType')->brand_mobile();
        }

        if (empty($brand)){
            $this->_error(403,'非法访问');
        } else {
            $this->_callBack($brand);
        }
    }

    /**
     * 获取机型，颜色，常用故障
     */
    public function pattern()
    {
        $model = D('PhoneType');
        $post = I('post.');

        /*判断缓存*/
        if (!empty(S($post['id'].'PCpattern'))) {

            $pattern =  S($post['id'].'PCpattern');
        } else {

            $pattern = $model->pattern($post['id']);
        }

        $model = M('goods_color');

        foreach ($pattern as &$value) {
             $value['phone_img'] = $value['img'];

            foreach ($value['malfunction'] as $j => &$s) {

                if ($s['easy_function_img']) {
                    $s['easy_function_img'] = 'http://'.$_SERVER["SERVER_NAME"].$s['easy_function_img'];
                }

                if ($s['easy_function_img_click']) {
                    $s['easy_function_img_click'] = 'http://'.$_SERVER["SERVER_NAME"].$s['easy_function_img_click'];
                }

                if ($s['easy_function_img_highlighted']) {
                    $s['easy_function_img_highlighted'] = 'http://'.$_SERVER["SERVER_NAME"].$s['easy_function_img_highlighted'];
                }

            }

            foreach (json_decode($value['color']) as $k => $v) {
                $value['color_info'][$k] = $model->find($v);
            }
        }

        if (empty($pattern)){
            $this->_error(403,'非法访问');
        } else {
            $this->_callBack($pattern);
        }
    }

    /**
     * 获取手机机型
    */
    public function pattern_mobile()
    {
        $post = I('post.');

        if (!$post['id']) {
            $this->_error(503,'无参数');
        }

        $model = D('phoneType');

        //判断是否获取全部
        if ($post['more']) {

            if (empty(S($post['id'].'patternMobileMore'))) {
                $pattern = $model->pattern_mobile(array('brand_id' => $post['id']));
            } else {
                $pattern =  S($post['id'].'patternMobileMore');
            }
        } else {
            $pattern = $model->pattern_mobile(array('brand_id' => $post['id']));

            if (empty(S($post['id'].'patternMobile'))) {
                $pattern = $model->pattern_mobile(array('brand_id' => $post['id'], 'category_id' => 1 ), 8);
            } else {
                $pattern =  S($post['id'].'patternMobile');
            }
        }

        if (empty($pattern)){
            $this->_error(503,'无数据');
        }

        $this->_callBack($pattern);
    }

    /**
     * 获取冷门故障
     */
    public function notEasyFunction()
    {
        $post = I('post.');
        if (empty($post['id'])) {
            $this->_error(403,'缺少参数');
        }

        $model = D('PhoneType');
        $pattern = $model->getFunction($post['id'],0);

        foreach($pattern as &$value) {

            if (!empty($value['easy_function_img'])) {
                $value['easy_function_img'] =  'http://'.$_SERVER["SERVER_NAME"].$value['easy_function_img'];
            }

            if (!empty($value['easy_function_img_highlighted'])) {
                $value['easy_function_img_highlighted'] =  'http://'.$_SERVER["SERVER_NAME"].$value['easy_function_img_highlighted'];
            }

            if (!empty($value['easy_function_img_click'])) {
                $value['easy_function_img_click'] =  'http://'.$_SERVER["SERVER_NAME"].$value['easy_function_img_click'];
            }
        }

        if (empty($pattern)){
            $this->_error(403,'非法访问');
        } else {
            $this->_callBack($pattern);
        }
    }

    /**
     * 获取所有故障以及颜色
     */
    public function malfunction()
    {
        $post = I('post.');

        if (empty($post['id'])) {
            $this->_error(403,'缺少参数');
        }

        /*判断缓存*/
        if (empty(S($post['id'].'malfunction'))) {
            $model = D('PhoneType');
            $mafun = $model->getFunction($post['id'],2);
            S($post['id'].'malfunction',$mafun);
        } else {
            $mafun = S($post['id'].'malfunction');
        }

        /*组装图片路径*/
        foreach($mafun as &$value) {

            if (!empty($value['easy_function_img'])) {
                $value['easy_function_img'] =  'http://'.$_SERVER["SERVER_NAME"].$value['easy_function_img'];
            }

            if (!empty($value['easy_function_img_highlighted'])) {
                $value['easy_function_img_highlighted'] =  'http://'.$_SERVER["SERVER_NAME"].$value['easy_function_img_highlighted'];
            }

            if (!empty($value['easy_function_img_click'])) {
                $value['easy_function_img_click'] =  'http://'.$_SERVER["SERVER_NAME"].$value['easy_function_img_click'];
            }
        }

        /*获取颜色*/
        $phoneRst = M('phone')->where(array('id' => $post['id']))->field('color_id, img, alias')->find();

        $model = M('goods_color');

        foreach (explode(',',$phoneRst['color_id']) as $k => $v) {
            $model->find($v);
            $color[$k]['name'] = $model->name;
            $color[$k]['code'] = $model->color;
            $color[$k]['color_id'] = $model->id;
        }
        $rst['img'] = 'http://'.$_SERVER["SERVER_NAME"].$phoneRst['img'];
        $rst['malfunction'] = $mafun;
        $rst['color'] = $color;
        $rst['alias'] = $phoneRst['alias'];

        if (empty($rst)){
            $this->_error(403,'无数据');
        } else {
            $this->_callBack($rst);
        }
    }

    /**
     * 获取机型所有数据
     */
    public function getPhoneInfo()
    {
        $post = I('post.');

        if (empty($post['id'])) {
            $this->_error(403,'缺少参数');
        }

        $phoneInfo = M('phone')->find($post['id']);

        /*判断缓存*/
        //if (empty(S($post['id'].'malfunction'))) {
            $model = D('PhoneType');
            $mafun = $model->getFunction($post['id'],2);
            S($post['id'].'malfunction',$mafun);
        //} else {
        //    $mafun = S($post['id'].'malfunction');
        //}

        /*组装图片路径*/
        foreach($mafun as &$value) {

            if (!empty($value['easy_function_img'])) {
                $value['easy_function_img'] =  'http://'.$_SERVER["SERVER_NAME"].$value['easy_function_img'];
            }

            if (!empty($value['easy_function_img_highlighted'])) {
                $value['easy_function_img_highlighted'] =  'http://'.$_SERVER["SERVER_NAME"].$value['easy_function_img_highlighted'];
            }

            if (!empty($value['easy_function_img_click'])) {
                $value['easy_function_img_click'] =  'http://'.$_SERVER["SERVER_NAME"].$value['easy_function_img_click'];
            }
        }
        unset($value);
        /*获取颜色*/
        $colorRst = M('phone')->where(array('id' => $post['id']))->field('color_id')->find();

        $model = M('goods_color');

        foreach (explode(',',$colorRst['color_id']) as $key => $value) {
            $model->find($value);
            $color[$key]['name'] = $model->name;
            $color[$key]['code'] = $model->color;
            $color[$key]['color_id'] = $model->id;

        }

        $rst['malfunction'] = $mafun;
        $rst['brand'] = $phoneInfo['brand'];;
        $rst['color'] = $color;
        $rst['name'] = $phoneInfo['alias'];
        $rst['img'] = 'http://'.$_SERVER["SERVER_NAME"].$phoneInfo['img'];

        if (empty($rst)){
            $this->_error(403,'无数据');
        } else {
            $this->_callBack($rst);
        }
    }

    /*
     * 故障分类+颜色+机型ID+机型图片
     */
    public function malfunctionType()
    {
        $rst = array();
        $post = I('post.');

        /*判断缓存*/
        if (empty(S($post['id'].'malfunctionType'))) {
            $model = M('phone');
            $model->find($post['id']);
            $rst['id'] = $model->id;
            $rst['alias'] = $model->alias;
            $rst['img'] = 'http://'.$_SERVER["SERVER_NAME"].$model->img;
            $rst['malfunction'] = M('malfunction_type')->select();
            $colorModel = M('goods_color');

            foreach (explode(',',$model->color_id) as $key => $value) {
                $colorModel->find($value);
                $color[$key]['name'] = $colorModel->name;
                $color[$key]['code'] = $colorModel->color;
                $color[$key]['color_id'] = $colorModel->id;
            }

            $rst['color'] = $color;

            foreach ($rst['malfunction'] as $key => &$value) {

                $map = array();
                $map['malfunction_type_id'] = array('eq', $value['id']);
                $map['phone_id'] = array('eq', $post['id']);

                $tmp = M('malfunction')
                        ->join('left join phone_malfunction pm on pm.malfunction_id = malfunction.id')
                        ->where($map)
                        ->field('pm.id, pm.malfunction, pm.price_reference') //, malfunction.remark
                        ->select();

                if (count($tmp) > 0) {
                    $value['malfunction'] = $tmp;
                } else {
                    unset($rst['malfunction'][$key]);
                }
            }

            S($post['id'].'malfunctionType', $rst);
        } else {
            $rst = S($post['id'].'malfunctionType');
        }

        $this->_callBack($rst);
    }

    /*
     * 所有机型
     */
    public function getPhone()
    {
        if (empty(S('getPhone'))){

            $phone = M('phone')
                ->where(array('category_id' => array('in','1,2')))
                ->select();
            S('getPhone', $phone);
        }

        $this->_callBack(S('getPhone'));
    }

    /*
     * 所有机型 : 品牌+机型
     */
    public function brandPhone()
    {
        if (empty(S('brandPhone'))){

            $phone = M('phone')
                ->where(array('category_id' => array('in','1,2')))
                ->select();

            foreach ($phone as &$value) {
                //苹果不做过滤
                if (!strstr($value['alias'], $value['brand']) && $value['brand'] != 'iPhone' && $value['brand'] != 'oppo') {
                    $value['alias'] = $value['brand'].$value['alias'];
                } else {
                    $value['ty'] = 1;
                }
            }

            S('brandPhone', $phone);
        }

        $this->_callBack(S('brandPhone'));
    }


    /**
     * 内存升级
     */
    public function MemoryUpgrade()
    {
        $model = D('PhoneType');
        $colorModel = M('goods_color');
        $rst = $model->MemoryUpgrade();

        foreach ($rst as $key => &$value) {
            $colorRst = M('phone')->where(array('id' => $value['phone_id']))->field('color_id')->find();

            foreach (explode(',',$colorRst['color_id']) as $k => $v) {
                $colorModel->find($v);
                $rst[$key]['phone_color'][$k]['name'] = $colorModel->name;
                $rst[$key]['phone_color'][$k]['code'] = $colorModel->color;
                $rst[$key]['phone_color'][$k]['color_id'] = $colorModel->id;

            }

        }

        $this->_callBack($rst);
    }

    /**
     * 彩壳升级
     */
    public function replaceShell()
    {
        $model = D('PhoneType');
        $colorModel = M('goods_color');
        $rst = $model->replaceShell();

        if (empty(S('replaceShell'))) {

            foreach ($rst as $key => &$value) {
                $colorRst = M('phone')->where(array('id' => $value['phone_id']))->field('color_id')->find();

                foreach (explode(',',$colorRst['color_id']) as $k => $v) {
                    $colorModel->find($v);
                    $rst[$key]['phone_color'][$k]['name'] = $colorModel->name;
                    $rst[$key]['phone_color'][$k]['code'] = $colorModel->color;
                    $rst[$key]['phone_color'][$k]['color_id'] = $colorModel->id;
                }

                foreach ($value['color'] as &$s) {
                    $s['price'] = M('phone_malfunction')->where(array('id' => $s['id']))->getField('price_reference');
                }
            }

            S('replaceShell',$rst);
        } else {
           $rst = S('replaceShell');
        }

        $this->_callBack($rst);
    }

    /**
     * 屏幕换新
     */
    public function replaceScreen()
    {
        $color = array();
        $model = D('PhoneType');
        $colorModel = M('goods_color');
        $rst = $model->replaceScreen();


        foreach ($rst as $key => &$value) {
            $colorRst = M('phone')->where(array('id' => $value['phone_id']))->field('color_id')->find();

            foreach (explode(',',$colorRst['color_id']) as $k => $v) {

                $colorModel->find($v);
                $rst[$key]['color'][$k]['name'] = $colorModel->name;
                $rst[$key]['color'][$k]['code'] = $colorModel->color;
                $rst[$key]['color'][$k]['color_id'] = $colorModel->id;

            }
        }
        $this->_callBack($rst);
    }

    /**
     * (新)首页 机型数据
     */
    public function getHomeData()
    {
        $post = I('post.');

        if (empty(S('getHomeData'))) {
            $phone = M('phone')
                ->where(array('phone_type_id' => array('eq', $post['id'])))
                ->field('id, alias, brand, brand_id, category_id, color_id, color, img')
                ->order('phone.id DESC')
                ->select();

            foreach ($phone as &$value) {
                $value['color'] = M('goods_color')->where(array('id' => array('in', $value['color_id'])))->select();
            }

        } else {
            $phone = S('getHomeData');
        }

        $this->_callBack($phone);
    }

    /**
     * 获取系列
     */
    public function getSeries()
    {
        $post = I('post.');

        $rst = M('phone_type')->where(array('brand_id' => array('eq', $post['id'])))->select();
        $this->_callBack($rst);
    }

    /**
     * 电池换新
     */
    public function replaceBattery()
    {
        $model = D('PhoneType');
        $this->_callBack($model->replaceBattery());
    }

    /**
     * 数据线 and 钢化膜
     */
    public function usbSticker()
    {
        $model = D('PhoneType');
        $this->_callBack($model->usbSticker());
    }

    /**
     * 活动品牌brand(黑,灰)
     */
    public function activity()
    {
        $model = D('PhoneType');
        $this->_callBack($model->activity());
    }

    /**
     * new pc
     * 机型分类
     * @parameter post 机型ID
     */
    public function malclass()
    {
        $id = I('post.id');

        if (empty($id)) {
            $this->_error('403','非法访问');
        }

        $phone = M('phone')->where(array('id' => $id))->field('color_id, alias, img')->find();

        $map = array();
        $map['phone_id'] = $id;
        $map['malfunction.malfunction_type_id'] = array('neq',0);

        $rst['alias'] = $phone['alias'];
        $rst['color'] = M('goods_color')->where(array('id' => array('in', $phone['color_id'])))->select();
        $rst['img'] = $phone['img'];
        $rst['malfunction'] = M('phone_malfunction')
                ->join('left join `malfunction` on phone_malfunction.malfunction_id = malfunction.id')
                ->join('left join `malfunction_type` on malfunction.malfunction_type_id = malfunction_type.id')
                ->where($map)
                ->field('malfunction_type.id, malfunction_type.name, malfunction_type.img')
                ->group('malfunction_type.id')
                ->order('id asc')
                ->select();

        $this->_callBack($rst);
    }

    /**
     * new pc
     * 故障详情
     * @parameter post 机型ID
     * @parameter post 分类ID
     */
    public function maldetails()
    {
        $post = I('post.');

        if (empty($post)) {
            $this->_error('403','非法访问');
        }

        $rst = M('malfunction')
            ->join('left join `phone_malfunction` on malfunction.id = phone_malfunction.malfunction_id')
            ->where(array('malfunction_type_id' => $post['type_id'], 'phone_id' => $post['id']))
            ->field('phone_malfunction.id, phone_malfunction.malfunction, price_reference, malfunction.remark')
            ->select();

        foreach ($rst as &$value) {
            $value['price_reference'] =intval($value['price_reference']);
        }

        $this->_callBack($rst);
    }

}
