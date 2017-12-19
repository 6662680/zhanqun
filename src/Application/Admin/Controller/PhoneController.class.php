<?php

// +------------------------------------------------------------------------------------------ 
// | Author: longDD <longdd_love@163.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 机型 Dates: 2016-08-25
// +------------------------------------------------------------------------------------------ 

namespace Admin\Controller;

use Admin\Controller;

class PhoneController extends BaseController
{
    /**
     * 机型
     *
     * @return void
     */
    public function index()
    {
        $this->display();
    }

    /**
     * 品牌
     *
     * @return void
     */
    public function brand()
    {
        $list = M('goods_brand')->select();
        $this->ajaxReturn($list);
    }

    /**
     * 品类
     *
     * @return void
     */
    public function category()
    {
        $list = M('goods_category')->select();
        $this->ajaxReturn($list);
    }

    /**
     * 类型
     *
     * @return void
     */
    public function type()
    {
        $list = M('goods_type')->select();
        $this->ajaxReturn($list);
    }

    /**
     * 颜色
     *
     * @return void
     */
    public function color()
    {
        $list = M('goods_color')->select();
        $this->ajaxReturn($list);
    }

    /**
     * 配置
     *
     * @return void
     */
    public function conf()
    {
        $list = M('goods_conf')->select();
        $this->ajaxReturn($list);
    }

    /**
     * 列表
     *
     * @return void
     */
    public function phoneRows()
    {
        $map = array();
        $rst = array();
        $post = I('post.');

        if (!empty($post['brand_id'])) {
            $map['brand_id'] = $post['brand_id'];
        }

        if (!empty($post['category_id'])) {
            $map['category_id'] = $post['category_id'];
        }

        if (!empty($post['keyword'])) {
            $map['name'] = array('like', '%' . $post['keyword'] . '%');
        }

        $count = M('phone')->where($map)->count();
        $rst['total'] = $count;

        $list = M('phone')
                ->join('left join `phone_type` on phone.phone_type_id = phone_type.id')
                ->where($map)
                ->limit($this->page())
                ->field('phone.*, phone_type.name as phone_type')
                ->select();

        $rst['rows'] = $list;

        $this->ajaxReturn($rst);
    }

    /**
     * 增加
     *
     * @return void
     */
    public function phoneAdd()
    {
        $rst = array();
        $data = array();
        $post = I('post.');
        $data = $post;
        
        if (M('phone')->add($data) !== false) {
            $rst['success'] = true;
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '添加失败！';
        }
        $this->ajaxReturn($rst);
    }


    /**
     * 更新
     *
     * @return void
     */
    public function phoneSave()
    {
        $rst = array();
        $map = array();
        $map['id'] = I('get.id/d');
        $post = I('post.');
        $data = $post;

        $item = D('phone')->where($map)->find();
        $imgInfo = $this->upload();

        if (!$imgInfo['success']) {
            $rst['success'] = false;
            $rst['errorMsg'] =  $imgInfo['errorMsg'];
        } else {

            if ($imgInfo['info']['img']['savepath']) {
                $data['img'] = '/upload/' .  $imgInfo['info']['img']['savepath']  . $imgInfo['info']['img']['savename'];
            }
        }

        if ($item) {

            if (D('phone')->where($map)->save($data) !== false) {
                $rst['success'] = true;
            } else {
                $rst['success'] = false;
                $rst['errorMsg'] = '更新失败！';
            }
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '记录不存在！';
        }

        $this->ajaxReturn($rst);
    }

   	/**
   	 * 删除
   	 *
   	 * @return void
   	 */
   	public function phoneDelete()
   	{
        $id = I('post.id');
        $rst = array();
        $map = array();
        $map['id'] = $id;
        $item = D('phone')->where($map)->find();

        if ($item) {

            if (D('phone')->where($map)->limit(1)->delete() !== false) {
                $rst['success'] = true;
            } else {
                $rst['success'] = false;
                $rst['errorMsg'] = '删除失败！';
            }
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '记录不存在！';
        }

        $this->ajaxReturn($rst);
    }

    /**
     * 故障
     *
     * @return void
     */
    public function malfunction()
    {
        $this->display();
    }

    /**
     * 列表
     *
     * @return void
     */
    public function malRows()
    {
        $map = array();
        $rst = array();
        $post = I('post.');

        if (!empty($post['keyword'])) {
            $map['name'] = array('like', '%' . $post['keyword'] . '%');
        }

        $count = M('malfunction')->where($map)->count();
        $rst['total'] = $count;

        $list = M('malfunction')->where($map)->limit($this->page())->select();
        $rst['rows'] = $list;

        $this->ajaxReturn($rst);
    }

    /**
     * 增加
     *
     * @return void
     */
    public function malAdd()
    {
        $rst = array();
        $data = array();
        $data = I('post.');
        
        if (M('malfunction')->add($data) !== false) {
            $rst['success'] = true;
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '添加失败！';
        }
        $this->ajaxReturn($rst);
    }


    /**
     * 更新
     *
     * @return void
     */
    public function malSave()
    {
        $rst = array();
        $map = array();
        $map['id'] = I('get.id/d');
        $data = I('post.');
        $item = D('malfunction')->where($map)->find();
        $imgInfo = $this->upload();

        if (!$imgInfo['success']) {
            $rst['success'] = false;
            $rst['errorMsg'] =  $imgInfo['errorMsg'];
        } else {

            if ($imgInfo['info']['easy_img']['savepath']) {
                $data['img'] = '/upload/' .  $imgInfo['info']['easy_img']['savepath']  . $imgInfo['info']['easy_img']['savename'];
            }

            if ($imgInfo['info']['easy_img_click']['savepath']) {
                $data['img_click'] = '/upload/' .  $imgInfo['info']['easy_img_click']['savepath']  . $imgInfo['info']['easy_img_click']['savename'];
            }
        }

        if ($item) {

            if (D('malfunction')->where($map)->save($data) !== false) {
                $rst['success'] = true;
            } else {
                $rst['success'] = false;
                $rst['errorMsg'] = '更新失败！';
            }
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '记录不存在！';
        }

        $this->ajaxReturn($rst);
    }

    /**
     * 删除
     *
     * @return void
     */
    public function malDelete()
    {
        $id = I('post.id');
        $rst = array();
        $map = array();
        $map['id'] = $id;
        $item = D('malfunction')->where($map)->find();

        if ($item) {

            if (D('malfunction')->where($map)->limit(1)->delete() !== false) {
                $rst['success'] = true;
            } else {
                $rst['success'] = false;
                $rst['errorMsg'] = '删除失败！';
            }
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '记录不存在！';
        }

        $this->ajaxReturn($rst);
    }

    /**
     * 机型故障
     *
     * @return void
     */
    public function phomal()
    {
        $this->display();
    }

    /**
     * 列表
     *
     * @return void
     */
    public function phomalRows()
    {
        $map = array();
        $rst = array();
        $post = I('post.');

        if (!empty($post['phone_id'])) {
            $map['phone_id'] = $post['phone_id'];
        }

        if (!empty($post['malfunction_id'])) {
            $map['malfunction_id'] = $post['malfunction_id'];
        }

        if (!empty($post['keyword'])) {
            $where = array();
            $where['p.name'] = array('like', '%' . $post['keyword'] . '%');
            $where['p.alias'] = array('like', '%' . $post['keyword'] . '%');
            $where['malfunction'] = array('like', '%' . $post['keyword'] . '%');
            $where['fitting'] = array('like', '%' . $post['keyword'] . '%');
            $where['waste'] = array('like', '%' . $post['keyword'] . '%');
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }

        $count = M('phone_malfunction')->join('pm left join phone p on p.id = pm.phone_id')->where($map)->count();
        $rst['total'] = $count;

        $list = M('phone_malfunction')->join('pm left join phone p on p.id = pm.phone_id')
                ->field('pm.*, p.alias as phone')->where($map)->limit($this->page())->select();
        $rst['rows'] = $list;

        $this->ajaxReturn($rst);
    }
    
    /**
     * 机型故障
     *
     * @return void
     */
    public function phomals()
    {
        $map = array();
        $map['phone_id'] = I('get.phone_id/d');
        $list = M('phone_malfunction')->join('pm left join malfunction m on m.id = pm.malfunction_id')
                ->field('pm.id, m.name')
                ->where($map)->select();
        $this->ajaxReturn($list);
    }

    /**
     * 配件
     *
     * @return void
     */
    public function fittings()
    {
        $phoneId = I('get.phone_id/d', 0);
        $sql = "select f.id, f.number, f.title from phone_fitting pf left join fitting f on pf.fitting_id=f.id where pf.phone_id={$phoneId}";
        $fittings = M()->query($sql);
        $this->ajaxReturn($fittings);
    }

    /**
     * 废件
     *
     * @return void
     */
    public function wastes()
    {
        $phoneId = I('get.phone_id/d', 0);
        $sql = "select w.id, w.number, w.title from phone_waste pf left join waste w on pf.waste_id=w.id where pf.phone_id={$phoneId}";
        $fittings = M()->query($sql);
        $this->ajaxReturn($fittings);
    }

    /**
     * 机型
     *
     * @return void
     */
    public function phones()
    {
        $map = array();
        $list = M('phone')->where($map)->field('id, alias')->order('alias asc')->select();
        $this->ajaxReturn($list);
    }

    /**
     * 颜色
     *
     * @return void
     */
    public function colors()
    {
        $map = array();
        $map['id'] = I('get.id/d', 0);
        $info = M('phone')->where($map)->find();

        $colorIds = explode(',', $info['color_id']);
        $colors = explode(',', $info['color']);
        $rst = array();

        foreach ($colorIds as $key => $value) {
            $item = array();
            $item['id'] = $value;
            $item['name'] = $colors[$key];
            $rst[] = $item; 
        }

        $this->ajaxReturn($rst);
    }

    /**
     * 故障
     *
     * @return void
     */
    public function malfunctions()
    {
        $map = array();
        $list = M('malfunction')->where($map)->field('id, name')->select();
        $this->ajaxReturn($list);
    }

    /**
     * 添加
     *
     * @return void
     */
    public function phomalAdd()
    {
        $rst = array();
        $data = array();
        $post = I('post.');
        $data['phone_id'] = $post['phone_id'];
        $data['phone'] = $post['phone'];
        $data['malfunction_id'] = $post['malfunction_id'];
        $data['malfunction'] = $post['malfunction'];
        $data['price_market'] = $post['price_market'];
        $data['price_reference'] = $post['price_reference'];
        $data['divide_local'] = $post['divide_local'];
        $data['divide_platform'] = $post['divide_platform'];
        $data['is_color'] = $post['is_color'];
        $data['remark'] = $post['remark'];

        $fittings = array();

        if ($post['is_color'] && !empty($post['color'])) {
            foreach ($post['color'] as $key => $value) {
                $fitting = array();
                $colorInfo = explode('_', $key);
                $colorId = $colorInfo[0];
                $colorName = $colorInfo[1];

                foreach ($value as $k => $v) {
                    $info = explode('_', $v);
                    $item = array();
                    $item['id'] = $info[0];
                    $item['name'] = $info[1];
                    $item['amount'] = $post['amount'][$key][$k];
                    $fitting[$info[0]] = $item;
                }
                $fittings[$colorId]['name'] = $colorName;
                $fittings[$colorId]['items'] = $fitting;
            }
        } else if(!empty($post['fittings'])){
            foreach ($post['fittings'] as $k => $v) {
                $info = explode('_', $v);
                $item = array();
                $item['id'] = $info[0];
                $item['name'] = $info[1];
                $item['amount'] = $post['fittings_amount'][$k];
                $fittings[$info[0]] = $item;
            }
        }

        if(!empty($post['wastes'])){
            $wastes = array();

            foreach ($post['wastes'] as $k => $v) {
                $info = explode('_', $v);
                $item = array();
                $item['id'] = $info[0];
                $item['name'] = $info[1];
                $item['amount'] = $post['wastes_amount'][$k];
                $wastes[$info[0]] = $item;
            }
        }

        $data['fitting'] = json_encode($fittings);
        $data['waste'] = json_encode($wastes);
        
        if (M('phone_malfunction')->add($data) !== false) {
            $rst['success'] = true;
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '添加失败！';
        }
        $this->ajaxReturn($rst);
    }

    /**
     * 编辑
     *
     * @return void
     */
    public function phomalSave()
    {
        $rst = array();
        $map = array();
        $map['id'] = I('get.id/d');
        $data = I('post.');
        $item = D('phone_malfunction')->where($map)->find();

        if ($data['easy_function'] == 1) {
            $result = M('malfunction')->find($data['malfunction_id']);
            if (empty($result['img'])) {
                $rst['success'] = false;
                $rst['errorMsg'] = '没有热门图标的故障不许添加成热门，请联系UI上传图标！';
                $this->ajaxReturn($rst);
            }
        }

        if ($item) {
            
            $fittings = array();
            
            if ($data['is_color'] && !empty($data['color'])) {
                foreach ($data['color'] as $key => $value) {
                    $fitting = array();
                    $colorInfo = explode('_', $key);
                    $colorId = $colorInfo[0];
                    $colorName = $colorInfo[1];
            
                    foreach ($value as $k => $v) {
                        $info = explode('_', $v);
                        $item = array();
                        $item['id'] = $info[0];
                        $item['name'] = $info[1];
                        $item['amount'] = $data['amount'][$key][$k];
                        $fitting[$info[0]] = $item;
                    }
                    $fittings[$colorId]['name'] = $colorName;
                    $fittings[$colorId]['items'] = $fitting;
                }
            } else if(!empty($data['fittings'])){
                foreach ($data['fittings'] as $k => $v) {
                    $info = explode('_', $v);
                    $item = array();
                    $item['id'] = $info[0];
                    $item['name'] = $info[1];
                    $item['amount'] = $data['fittings_amount'][$k];
                    $fittings[$info[0]] = $item;
                }
            }
            
            if(!empty($data['wastes'])){
                $wastes = array();
            
                foreach ($data['wastes'] as $k => $v) {
                    $info = explode('_', $v);
                    $item = array();
                    $item['id'] = $info[0];
                    $item['name'] = $info[1];
                    $item['amount'] = $data['wastes_amount'][$k];
                    $wastes[$info[0]] = $item;
                }
            }
            
            $data['fitting'] = json_encode($fittings);
            $data['waste'] = json_encode($wastes);

            if (D('phone_malfunction')->where($map)->save($data) !== false) {
                $rst['success'] = true;
            } else {
                $rst['success'] = false;
                $rst['errorMsg'] = '更新失败！';
            }
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '记录不存在！';
        }

        $this->ajaxReturn($rst);
    }

    /**
     * 删除
     *
     * @return void
     */
    public function phomalDelete()
    {
        $id = I('post.id');
        $rst = array();
        $map = array();
        $map['id'] = $id;
        $item = D('phone_malfunction')->where($map)->find();

        if ($item) {

            if (D('phone_malfunction')->where($map)->limit(1)->delete() !== false) {
                $rst['success'] = true;
            } else {
                $rst['success'] = false;
                $rst['errorMsg'] = '删除失败！';
            }
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '记录不存在！';
        }

        $this->ajaxReturn($rst);
    }

}