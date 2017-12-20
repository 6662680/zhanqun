<?php

// +------------------------------------------------------------------------------------------ 
// | Author: longDD <longdd_love@163.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 用户 Dates: 2016-08-05
// +------------------------------------------------------------------------------------------ 

namespace Admin\Controller;

use Admin\Controller;

class SiteController extends BaseController
{
	/**
	 * 首页
	 *
	 * @return void
	 */
    public function index()
    {
    	
        $this->display();
    }

    /**
     * 获取用户
     *
     * @return void
     */
    public function rows()
    {
        $map = array();
        $rst = array();
        $post = I('post.');

        if (is_numeric($post['status'])) {
            $map['status'] = $post['status'];
        }

        if (!empty($post['keyword'])) {
            $where = array();
            $where['site_name']  = array('like', '%' . $post['keyword'] . '%');
            $where['site_address']  = array('like', '%' . $post['keyword'] . '%');
    
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }

        $count = D('site')->where($map)->count();
        $rst['total'] = $count;

        $list = D('site')->field('*')
            ->where($map)->order('id')->limit($this->page())->select();
        $rst['rows'] = $list;
        $this->ajaxReturn($rst);
    }
	
	/**
     * 更新网站
     *
     * @return void
     */
    public function save()
    {
        $rst = array();
        $map = array();
        $map['id'] = I('get.id/d');
        $data = I('post.');
        $site = D('site')->where($map)->find();

        if ($site) {

            if ($site['is_root'] == 1) {
                $rst['success'] = false;
                $rst['errorMsg'] = '超级管理员，禁止操作！';
                $this->ajaxReturn($rst);
            }

            if (D('site')->where($map)->save($data) !== false) {
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
   	 * 删除网站
   	 *
   	 * @return void
   	 */
   	public function delete()
   	{
        $id = I('post.id');
        $rst = array();
        $map = array();
        $map['id'] = $id;
        $site = D('site')->where($map)->find();

        if ($site) {

            if ($site['is_root'] == 1) {
                $rst['success'] = false;
                $rst['errorMsg'] = '超级管理员，禁止操作！';
                $this->ajaxReturn($rst);
            }

            if (D('site')->where($map)->limit(1)->delete() !== false) {
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
     * 增加网站
     *
     * @return void
     */
    public function add()
    {
        $rst = array();
        $data = array();
        $data = I('post.');
        
        if (D('site')->add($data) !== false) {
            $rst['success'] = true;
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '添加失败！';
        }
        $this->ajaxReturn($rst);
    }
	
	/**
     * 当前网站下栏目成员
     *
     * @return void
     */
    public function inColumn()
    {
        $id = I('get.id', 0);
        $sql = "select col.id, col.column_name from column_class co 
            left join site s on co.site_id=s.id
            left join `column` col on co.column_id=col.id 
            where co.site_id = {$id} order by co.id";
        $list = M()->query($sql);
        $this->ajaxReturn($list);
    }

    /**
     * 非当前网站下栏目成员
     *
     * @return void
     */
    public function notinColumn()
    {
        $id = I('get.id', 0);
        $sql = "select col.id, col.column_name from `column` col
            where col.id not in (select column_id from column_class where site_id = {$id})";
        $list = M()->query($sql);
        $this->ajaxReturn($list);
    }

    /**
     * 添加栏目成员
     *
     * @return void
     */
    public function addNews()
    {
        $rst = array();
        $data = array();
        $data['site_id'] = I('post.siteId');
        $data['column_id'] = I('post.columnId');

        if (D('column_class')->add($data) !== false) {
            $rst['success'] = true;
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '添加失败！';
        }
        $this->ajaxReturn($rst);
    }

    /**
     * 移除栏目成员
     *
     * @return void
     */
    public function removeNews()
    {
        $rst = array();
        $map = array();
        $map['site_id'] = I('post.siteId');
        $map['column_id'] = I('post.columnId');

        if (D('column_class')->where($map)->limit(1)->delete() !== false) {
            $rst['success'] = true;
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '删除失败！';
        }
        $this->ajaxReturn($rst);
    }

	/**
     * 当前站点下当前栏目的新闻成员
     *
     * @return void
     */
    public function inNews()
    {
        $id = I('get.id', 0);
        $sql = "select n.id, n.title from news n 
            left join news_class nc on nc.news_id=n.id
            where nc.column_class_id = {$id}";
        $list = M()->query($sql);
        $this->ajaxReturn($list);
    }

    /**
     * 非当前站点下当前栏目的新闻成员
     *
     * @return void
     */
    public function notinNews()
    {
        $id = I('get.id', 0);
        $sql = "select n.id, n.title from news n
            where n.id not in (select news_id from news_class where news_class.column_class_id = {$id})";
        $list = M()->query($sql);
        $this->ajaxReturn($list);
    }
	
    /**
     * 添加新闻成员
     *
     * @return void
     */
    public function addColumn()
    {
        $rst = array();
        $data = array();
        $data['site_id'] = I('post.siteId');
        $data['column_id'] = I('post.columnId');

        if (D('column_class')->add($data) !== false) {
            $rst['success'] = true;
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '添加失败！';
        }
        $this->ajaxReturn($rst);
    }

    /**
     * 移除成员
     *
     * @return void
     */
    public function removeColumn()
    {
        $rst = array();
        $map = array();
        $map['site_id'] = I('post.siteId');
        $map['column_id'] = I('post.columnId');

        if (D('column_class')->where($map)->limit(1)->delete() !== false) {
            $rst['success'] = true;
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '删除失败！';
        }
        $this->ajaxReturn($rst);
    }
	

}