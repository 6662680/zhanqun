<?php

// +------------------------------------------------------------------------------------------ 
// | Author: longDD <longdd_love@163.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 商品 Dates: 2016-08-25
// +------------------------------------------------------------------------------------------ 

namespace Admin\Controller;

use Admin\Controller;

class GoodsController extends BaseController
{
    /**
     * 品牌
     *
     * @return void
     */
    public function brand()
    {
        $this->display();
    }

    /**
     * 列表
     *
     * @return void
     */
    public function brandRows()
    {
        $map = array();
        $rst = array();

        $count = M('goods_brand')->where($map)->count();
        $rst['total'] = $count;

        $list = M('goods_brand')->where($map)->limit($this->page())->select();
        $rst['rows'] = $list;

        $this->ajaxReturn($rst);
    }

    /**
     * 增加
     *
     * @return void
     */
    public function brandAdd()
    {
        $rst = array();
        $data = array();
        $data = I('post.');
        $info = $this->upload();

        if (!$info['success']) {
            $rst['success'] = false;
            $rst['errorMsg'] = $info['errorMsg'];
        } else {

            $data['url'] = '/upload/' . $info['info']['url']['savepath'] . $info['info']['url']['savename'];
            $data['wap_url'] = '/upload/' . $info['info']['wap_url']['savepath'] . $info['info']['wap_url']['savename'];

            if (M('goods_brand')->add($data) !== false) {
                $rst['success'] = true;
            } else {
                $rst['success'] = false;
                $rst['errorMsg'] = '添加失败！';
            }
        }
        
        $this->ajaxReturn($rst);
    }


    /**
     * 更新
     *
     * @return void
     */
    public function brandSave()
    {
        $rst = array();
        $map = array();
        $map['id'] = I('get.id/d');
        $data = I('post.');
        $item = D('goods_brand')->where($map)->find();

        if ($item) {
            $info = $this->upload();

            if ($info['success']) {

                if (!empty($info['info']['url']['name'])) {
                    $data['url'] = '/upload/' . $info['info']['url']['savepath'] . $info['info']['url']['savename'];
                }

                if (!empty($info['info']['url_click']['name'])) {
                    $data['url_click'] = '/upload/' . $info['info']['url_click']['savepath'] . $info['info']['url_click']['savename'];
                }

                if (!empty($info['info']['wap_url']['name'])) {
                    $data['wap_url'] = '/upload/' . $info['info']['wap_url']['savepath'] . $info['info']['wap_url']['savename'];
                }
            }

            if (D('goods_brand')->where($map)->save($data) !== false) {

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
   	public function brandDelete()
   	{
        $id = I('post.id');
        $rst = array();
        $map = array();
        $map['id'] = $id;
        $item = D('goods_brand')->where($map)->find();

        if ($item) {

            if (D('goods_brand')->where($map)->limit(1)->delete() !== false) {
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
     * 品类
     *
     * @return void
     */
    public function category()
    {
        $this->display();
    }

        /**
     * 列表
     *
     * @return void
     */
    public function categoryRows()
    {
        $map = array();
        $rst = array();

        $count = M('goods_category')->where($map)->count();
        $rst['total'] = $count;

        $list = M('goods_category')->where($map)->limit($this->page())->select();
        $rst['rows'] = $list;

        $this->ajaxReturn($rst);
    }

    /**
     * 增加
     *
     * @return void
     */
    public function categoryAdd()
    {
        $rst = array();
        $data = array();
        $data = I('post.');
        
        if (M('goods_category')->add($data) !== false) {
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
    public function categorySave()
    {
        $rst = array();
        $map = array();
        $map['id'] = I('get.id/d');
        $data = I('post.');
        $item = D('goods_category')->where($map)->find();

        if ($item) {

            if (D('goods_category')->where($map)->save($data) !== false) {
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
    public function categoryDelete()
    {
        $id = I('post.id');
        $rst = array();
        $map = array();
        $map['id'] = $id;
        $item = D('goods_category')->where($map)->find();

        if ($item) {

            if (D('goods_category')->where($map)->limit(1)->delete() !== false) {
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
     * 类型
     *
     * @return void
     */
    public function type()
    {
        $this->display();
    }

        /**
     * 列表
     *
     * @return void
     */
    public function typeRows()
    {
        $map = array();
        $rst = array();

        $count = M('goods_type')->where($map)->count();
        $rst['total'] = $count;

        $list = M('goods_type')->where($map)->limit($this->page())->select();
        $rst['rows'] = $list;

        $this->ajaxReturn($rst);
    }

    /**
     * 增加
     *
     * @return void
     */
    public function typeAdd()
    {
        $rst = array();
        $data = array();
        $data = I('post.');
        
        if (M('goods_type')->add($data) !== false) {
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
    public function typeSave()
    {
        $rst = array();
        $map = array();
        $map['id'] = I('get.id/d');
        $data = I('post.');
        $item = D('goods_type')->where($map)->find();

        if ($item) {

            if (D('goods_type')->where($map)->save($data) !== false) {
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
    public function typeDelete()
    {
        $id = I('post.id');
        $rst = array();
        $map = array();
        $map['id'] = $id;
        $item = D('goods_type')->where($map)->find();

        if ($item) {

            if (D('goods_type')->where($map)->limit(1)->delete() !== false) {
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
     * 颜色
     *
     * @return void
     */
    public function color()
    {
        $this->display();
    }

        /**
     * 列表
     *
     * @return void
     */
    public function colorRows()
    {
        $map = array();
        $rst = array();

        $count = M('goods_color')->where($map)->count();
        $rst['total'] = $count;

        $list = M('goods_color')->where($map)->limit($this->page())->select();
        $rst['rows'] = $list;

        $this->ajaxReturn($rst);
    }

    /**
     * 增加
     *
     * @return void
     */
    public function colorAdd()
    {
        $rst = array();
        $data = array();
        $data = I('post.');
        
        if (M('goods_color')->add($data) !== false) {
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
    public function colorSave()
    {
        $rst = array();
        $map = array();
        $map['id'] = I('get.id/d');
        $data = I('post.');
        $item = D('goods_color')->where($map)->find();

        if ($item) {

            if (D('goods_color')->where($map)->save($data) !== false) {
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
    public function colorDelete()
    {
        $id = I('post.id');
        $rst = array();
        $map = array();
        $map['id'] = $id;
        $item = D('goods_color')->where($map)->find();

        if ($item) {

            if (D('goods_color')->where($map)->limit(1)->delete() !== false) {
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
     * 颜色
     *
     * @return void
     */
    public function colors()
    {
        $list = M('goods_color')->field('id, name')->select();
        $this->ajaxReturn($list);
    }

    /**
     * 配置
     *
     * @return void
     */
    public function conf()
    {
        $this->display();
    }

        /**
     * 列表
     *
     * @return void
     */
    public function confRows()
    {
        $map = array();
        $rst = array();

        $count = M('goods_conf')->where($map)->count();
        $rst['total'] = $count;

        $list = M('goods_conf')->where($map)->limit($this->page())->select();
        $rst['rows'] = $list;

        $this->ajaxReturn($rst);
    }

    /**
     * 增加
     *
     * @return void
     */
    public function confAdd()
    {
        $rst = array();
        $data = array();
        $data = I('post.');
        
        if (M('goods_conf')->add($data) !== false) {
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
    public function confSave()
    {
        $rst = array();
        $map = array();
        $map['id'] = I('get.id/d');
        $data = I('post.');
        $item = D('goods_conf')->where($map)->find();

        if ($item) {

            if (D('goods_conf')->where($map)->save($data) !== false) {
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
    public function confDelete()
    {
        $id = I('post.id');
        $rst = array();
        $map = array();
        $map['id'] = $id;
        $item = D('goods_conf')->where($map)->find();

        if ($item) {

            if (D('goods_conf')->where($map)->limit(1)->delete() !== false) {
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