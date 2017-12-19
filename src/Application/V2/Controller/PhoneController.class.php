<?php

namespace V2\Controller;

use Admin\Model\EngineerModel;
use Think\Controller;
use V2\Transformer\PhoneTransformer;

/**
 * 获取品牌手机故障等数据
 * author :liyang
 * time : 2016-8-8
 */
class PhoneController extends ApiController
{

    public function _initialize()
    {
        S(array('type' => 'redis'));
    }

    /**
     * 判断是从缓存还是model中获取数据
     * @param $cacheName
     * @param $modelName
     * @param $methodName
     */
    private function getCacheDb($cacheName, $modelName, $methodName, $args1 = '', $args2 = '')
    {
        $cacheData = S($cacheName);

        $model=is_string($modelName)?D($modelName):$modelName;

        if (!empty($cacheData)) {
            return $cacheData;
        } else {

            if (!empty($args2)) {
                $data = $model->$methodName($args1, $args2);
            } else {
                $data = $model->$methodName($args1);
            }

            S($cacheName, $data);
            return $data;
        }
    }

    /**
     * 获取品牌
     */
    public function getBrand()
    {
        $brand = $this->getCacheDb('brand', 'Phone', 'brand');

        if (empty($brand)) {
            $this->responseError('获取品牌数据失败', 1022);
        } else {
            $this->responseSuccess($brand);
        }
    }

    /**
     * 获取品牌mobile
     */
    public function getBrandMobile()
    {
        $brand = $this->getCacheDb('brand_mobile', 'Phone', 'brand_mobile');

        if (empty($brand)) {
            $this->responseError('获取品牌数据失败', 1022);
        } else {
            $this->responseSuccess($brand);
        }
    }

    /**
     * 获取机型，颜色，常用故障
     */
    public function postPattern()
    {
        $model = D('Phone');
        $post = I('post.');
        $phoneTransformer=new PhoneTransformer();
        if (!$post['id']) {
            $this->responseError('缺少id参数', 1001);
        }
        /*判断缓存*/
        $pattern = $this->getCacheDb($post['id'] . 'PCpattern', $model, 'pattern', $post['id']);

        $model = M('goods_color');

        foreach ($pattern as &$value) {

            $value['malfunction']=$phoneTransformer->makeEasyImg($value['malfunction']);

            foreach (json_decode($value['color']) as $k => $v) {
                $value['color_info'][$k] = $model->find($v);
            }
        }

        if (empty($pattern)) {
            $this->responseError('获取机型，颜色，常用故障数据失败', 1022);
        } else {
            $this->responseSuccess($pattern);
        }
    }

    /**
     * 获取手机机型
     */
    public function getPatternMobile()
    {
        $post = I('post.');

        if (!$post['id']) {
            $this->responseError('无参数', 1001);
        }

        $model = D('phone');

        //判断是否获取全部
        if ($post['more']) {
            $pattern = $this->getCacheDb($post['id'] . 'patternMobileMore', $model, 'pattern_mobile', array('brand_id' => $post['id']));
        } else {
            #$pattern = $model->pattern_mobile(array('brand_id' => $post['id']));

            $pattern = $this->getCacheDb($post['id'] . 'patternMobile', $model, 'pattern_mobile',
                array('brand_id' => $post['id'], 'category_id' => 1), 8);

        }

        if (empty($pattern)) {
            $this->responseError('获取手机机型数据失败', 1022);
        } else {
            $this->responseSuccess($pattern);
        }
    }

    /**
     * 获取冷门故障
     */
    public function postNotEasyFunction()
    {
        $post = I('post.');
        if (empty($post['id'])) {
            $this->responseError('缺少id参数', 1001);
        }
        $phoneTransformer=new PhoneTransformer();
        $model = D('Phone');
        $pattern = $model->getFunction($post['id'], 0);

        $pattern=$phoneTransformer->makeEasyImg($pattern);

        if (empty($pattern)) {
            $this->responseError('获取冷门故障数据失败', 1022);
        } else {
            $this->responseSuccess($pattern);
        }
    }

    /**
     * 获取所有故障以及颜色
     */
    public function getMalfunction()
    {
        $post = I('post.');

        if (empty($post['id'])) {
            $this->responseError('缺少参数', 1001);
        }

        /*判断缓存*/
        $mafun = $this->getCacheDb($post['id'] . 'malfunction', 'Phone', 'getFunction', $post['id'], 2);


        /*组装图片路径*/
        foreach ($mafun as &$value) {

            if (!empty($value['easy_function_img'])) {
                $value['easy_function_img'] = 'http://' . $_SERVER["SERVER_NAME"] . $value['easy_function_img'];
            }

            if (!empty($value['easy_function_img_highlighted'])) {
                $value['easy_function_img_highlighted'] = 'http://' . $_SERVER["SERVER_NAME"] . $value['easy_function_img_highlighted'];
            }

            if (!empty($value['easy_function_img_click'])) {
                $value['easy_function_img_click'] = 'http://' . $_SERVER["SERVER_NAME"] . $value['easy_function_img_click'];
            }
        }

        /*获取颜色*/
        $phoneRst = M('phone')->where(array('id' => $post['id']))->field('color_id, img, alias')->find();

        $model = M('goods_color');

        foreach (explode(',', $phoneRst['color_id']) as $k => $v) {
            $model->find($v);
            $color[$k]['name'] = $model->name;
            $color[$k]['code'] = $model->color;
            $color[$k]['color_id'] = $model->id;
        }
        $rst['img'] = 'http://' . $_SERVER["SERVER_NAME"] . $phoneRst['img'];
        $rst['malfunction'] = $mafun;
        $rst['color'] = $color;
        $rst['alias'] = $phoneRst['alias'];

        if (empty($rst)) {
            $this->responseError('获取所有故障以及颜色数据失败', 1022);
        } else {
            $this->responseSuccess($rst);
        }
    }

    /**
     * 获取机型所有数据
     */
    public function getPhoneInfo()
    {
        $post = I('post.');

        if (empty($post['id'])) {
            $this->responseError('缺少参数', 1001);
        }

        $phoneInfo = M('phone')->find($post['id']);

        /*判断缓存*/
        //if (empty(S($post['id'].'malfunction'))) {
        $model = D('Phone');
        $mafun = $model->getFunction($post['id'], 2);
        S($post['id'] . 'malfunction', $mafun);
        //} else {
        //    $mafun = S($post['id'].'malfunction');
        //}

        /*组装图片路径*/
        foreach ($mafun as &$value) {

            if (!empty($value['easy_function_img'])) {
                $value['easy_function_img'] = 'http://' . $_SERVER["SERVER_NAME"] . $value['easy_function_img'];
            }

            if (!empty($value['easy_function_img_highlighted'])) {
                $value['easy_function_img_highlighted'] = 'http://' . $_SERVER["SERVER_NAME"] . $value['easy_function_img_highlighted'];
            }

            if (!empty($value['easy_function_img_click'])) {
                $value['easy_function_img_click'] = 'http://' . $_SERVER["SERVER_NAME"] . $value['easy_function_img_click'];
            }
        }
        unset($value);
        /*获取颜色*/
        $colorRst = M('phone')->where(array('id' => $post['id']))->field('color_id')->find();

        $model = M('goods_color');

        foreach (explode(',', $colorRst['color_id']) as $key => $value) {
            $model->find($value);
            $color[$key]['name'] = $model->name;
            $color[$key]['code'] = $model->color;
            $color[$key]['color_id'] = $model->id;

        }

        $rst['malfunction'] = $mafun;
        $rst['brand'] = $phoneInfo['brand'];;
        $rst['color'] = $color;
        $rst['name'] = $phoneInfo['alias'];
        $rst['img'] = 'http://' . $_SERVER["SERVER_NAME"] . $phoneInfo['img'];

        if (empty($rst)) {
            $this->responseError('获取机型所有数据失败', 1022);
        } else {
            $this->responseSuccess($rst);
        }
    }

    /*
     * 故障分类+颜色+机型ID+机型图片
     */
    public function getMalfunctionType()
    {
        $rst = array();
        $post = I('post.');

        /*判断缓存*/
        if (empty(S($post['id'] . 'malfunctionType'))) {
            $model = M('phone');
            $model->find($post['id']);
            $rst['id'] = $model->id;
            $rst['alias'] = $model->alias;
            $rst['img'] = 'http://' . $_SERVER["SERVER_NAME"] . $model->img;
            $rst['malfunction'] = M('malfunction_type')->select();
            $colorModel = M('goods_color');

            foreach (explode(',', $model->color_id) as $key => $value) {
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
                    ->field('pm.id, pm.malfunction, pm.price_reference')//, malfunction.remark
                    ->select();

                if (count($tmp) > 0) {
                    $value['malfunction'] = $tmp;
                } else {
                    unset($rst['malfunction'][$key]);
                }
            }

            S($post['id'] . 'malfunctionType', $rst);
        } else {
            $rst = S($post['id'] . 'malfunctionType');
        }

        if (empty($rst)) {
            $this->responseError('故障分类+颜色+机型ID+机型图片', 1022);
        } else {
            $this->responseSuccess($rst);
        }
    }

    /*
     * 所有机型
     */
    public function getPhone()
    {
        if (empty(S('getPhone'))) {
            $phone = M('phone')
                ->where(array('category_id' => array('in', '1,2')))
                ->select();
            S('getPhone', $phone);
        } else {
            $phone = S('getPhone');
        }

        if (empty($phone)) {
            $this->responseError('所有机型', 1022);
        } else {
            $this->responseSuccess($phone);
        }
    }

    /*
     * 所有机型 : 品牌+机型
     */
    public function getBrandPhone()
    {
        if (empty(S('brandPhone'))) {

            $phone = M('phone')
                ->where(array('category_id' => array('in', '1,2')))
                ->select();

            foreach ($phone as &$value) {
                //苹果不做过滤
                if (!strstr($value['alias'], $value['brand']) && $value['brand'] != 'iPhone' && $value['brand'] != 'oppo') {
                    $value['alias'] = $value['brand'] . $value['alias'];
                } else {
                    $value['ty'] = 1;
                }
            }

            S('brandPhone', $phone);
        } else {
            $phone = S('brandPhone');
        }

        if (empty($phone)) {
            $this->responseError('获取所有品牌+机型数据失败', 1022);
        } else {
            $this->responseSuccess($phone);
        }
    }


    /**
     * 内存升级
     */
    public function getMemoryUpgrade()
    {
        $model = D('Phone');
        $colorModel = M('goods_color');
        $rst = $model->MemoryUpgrade();

        foreach ($rst as $key => &$value) {
            $colorRst = M('phone')->where(array('id' => $value['phone_id']))->field('color_id')->find();

            foreach (explode(',', $colorRst['color_id']) as $k => $v) {
                $colorModel->find($v);
                $rst[$key]['phone_color'][$k]['name'] = $colorModel->name;
                $rst[$key]['phone_color'][$k]['code'] = $colorModel->color;
                $rst[$key]['phone_color'][$k]['color_id'] = $colorModel->id;

            }

        }

        if (empty($rst)) {
            $this->responseError('获取内存升级数据失败', 1022);
        } else {
            $this->responseSuccess($rst);
        }
    }

    /**
     * 彩壳升级
     */
    public function getReplaceShell()
    {
        $model = D('Phone');
        $colorModel = M('goods_color');
        $rst = $model->replaceShell();

        if (empty(S('replaceShell'))) {

            foreach ($rst as $key => &$value) {
                $colorRst = M('phone')->where(array('id' => $value['phone_id']))->field('color_id')->find();

                foreach (explode(',', $colorRst['color_id']) as $k => $v) {
                    $colorModel->find($v);
                    $rst[$key]['phone_color'][$k]['name'] = $colorModel->name;
                    $rst[$key]['phone_color'][$k]['code'] = $colorModel->color;
                    $rst[$key]['phone_color'][$k]['color_id'] = $colorModel->id;
                }

                foreach ($value['color'] as &$s) {
                    $s['price'] = M('phone_malfunction')->where(array('id' => $s['id']))->getField('price_reference');
                }
            }

            S('replaceShell', $rst);
        } else {
            $rst = S('replaceShell');
        }

        if (empty($rst)) {
            $this->responseError('获取彩壳升级数据失败', 1022);
        } else {
            $this->responseSuccess($rst);
        }
    }

    /**
     * 屏幕换新
     */
    public function getReplaceScreen()
    {
        $color = array();
        $model = D('Phone');
        $colorModel = M('goods_color');
        $rst = $model->replaceScreen();


        foreach ($rst as $key => &$value) {
            $colorRst = M('phone')->where(array('id' => $value['phone_id']))->field('color_id')->find();

            foreach (explode(',', $colorRst['color_id']) as $k => $v) {

                $colorModel->find($v);
                $rst[$key]['color'][$k]['name'] = $colorModel->name;
                $rst[$key]['color'][$k]['code'] = $colorModel->color;
                $rst[$key]['color'][$k]['color_id'] = $colorModel->id;

            }
        }
        if (empty($phone)) {
            $this->responseError('获取屏幕换新数据失败', 1022);
        } else {
            $this->responseSuccess($rst);
        }
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

        if (empty($phone)) {
            $this->responseError('获取机型数据失败', 1022);
        } else {
            $this->responseSuccess($phone);
        }
    }

    /**
     * 获取系列
     */
    public function getSeries()
    {
        $post = I('post.');

        $rst = M('phone_type')->where(array('brand_id' => array('eq', $post['id'])))->select();
        if (empty($rst)) {
            $this->responseError('获取系列数据失败', 1022);
        } else {
            $this->responseSuccess($rst);
        }
    }

    /**
     * 电池换新
     */
    public function getReplaceBattery()
    {
        $model = D('Phone');
        $data = $model->replaceBattery();
        if (empty($data)) {
            $this->responseError('获取电池换新数据失败', 1022);
        } else {
            $this->responseSuccess($data);
        }
    }

    /**
     * 数据线 and 钢化膜
     */
    public function getUsbSticker()
    {
        $model = D('Phone');
        $data = $model->usbSticker();
        if (empty($data)) {
            $this->responseError('获取线钢化膜数据失败', 1022);
        } else {
            $this->responseSuccess($data);
        }
    }

    /**
     * 活动品牌brand(黑,灰)
     */
    public function getActivity()
    {
        $model = D('Phone');
        $data = $model->activity();
        if (empty($data)) {
            $this->responseError('获取活动品牌膜数据失败', 1022);
        } else {
            $this->responseSuccess($data);
        }
    }
}
