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
        $sql = "select col.id, col.column_name,cc.id as ccid from column_class cc 
            left join site s on cc.site_id=s.id
            left join `column` col on cc.column_id=col.id 
            where cc.site_id = {$id} order by cc.id";
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
     * 移除栏目成员
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
            $rst['errorMsg'] = '移除失败！';
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
            left join column_class cc on nc.column_class_id=cc.id
            where cc.id = {$id}";
        $list = M()->query($sql);
        $this->ajaxReturn($list);
    }

    /**
     * 当前站点下非当前栏目的新闻成员
     *
     * @return void
     */
    public function notinNews()
    {
       
        $post = I('post.');
		
        if (is_numeric($post['column']) && empty($post['newskeyword'])) {
        	$id = I('get.id', 0);
			$column=$post['column'];
        	$sql = "select n.id, n.title, cc.column_id,c.column_name from news n
		        	left join news_class nc on nc.news_id=n.id
		        	left join column_class cc on nc.column_class_id=cc.id
		        	left join `column` c on cc.column_id=c.id
		        	where cc.column_id={$column} and 
		             n.id not in 
		            (select nc.news_id from news_class nc
		            where nc.column_class_id = {$id}) group by n.id";
        	$list = M()->query($sql);
        	
        	$sql2 = " select count(*) as num from (select count(*) from news n
		            left join news_class nc on nc.news_id=n.id
		        	left join column_class cc on nc.column_class_id=cc.id
		        	left join `column` c on cc.column_id=c.id
		        	where cc.column_id={$column} and 
		             n.id not in  
		            (select nc.news_id from news_class nc
		            where nc.column_class_id = 36) group by n.id) as ssss";
       	 	$count =M()->query($sql2);
		
		 	$rst["total"] = $count[0]['num'];
       	 	$rst['rows'] = $list;
       		 $this->ajaxReturn($rst);
        } elseif (!empty($post['newskeyword']) && !is_numeric($post['column'])) {
        	$id = I('get.id', 0);
			$newskeyword=$post['newskeyword'];
        	$sql = "select n.id, n.title, cc.column_id,c.column_name from news n
		        	left join news_class nc on nc.news_id=n.id
		        	left join column_class cc on nc.column_class_id=cc.id
		        	left join `column` c on cc.column_id=c.id
		        	where n.title like '%{$newskeyword}%' and 
		             n.id not in 
		            (select nc.news_id from news_class nc
		            where nc.column_class_id = {$id}) group by n.id";
        	$list = M()->query($sql);
        	
        	$sql2 = " select count(*) as num from (select count(*) from news n
		            left join news_class nc on nc.news_id=n.id
		        	left join column_class cc on nc.column_class_id=cc.id
		        	left join `column` c on cc.column_id=c.id
		        	where n.title like '%{$newskeyword}%' and 
		             n.id not in  
		            (select nc.news_id from news_class nc
		            where nc.column_class_id = 36) group by n.id) as ssss";
       	 	$count =M()->query($sql2);
		
		 	$rst["total"] = $count[0]['num'];
       		 $rst['rows'] = $list;
       		 $this->ajaxReturn($rst);
        } elseif (is_numeric($post['column']) && !empty($post['newskeyword'])) {
        	$id = I('get.id', 0);
        	$column=$post['column'];
			$newskeyword=$post['newskeyword'];
        	$sql = "select n.id, n.title, cc.column_id,c.column_name from news n
		        	left join news_class nc on nc.news_id=n.id
		        	left join column_class cc on nc.column_class_id=cc.id
		        	left join `column` c on cc.column_id=c.id
		        	where n.title like '%{$newskeyword}%' and 
		        	cc.column_id={$column} and
		             n.id not in 
		            (select nc.news_id from news_class nc
		            where nc.column_class_id = {$id}) group by n.id";
        	$list = M()->query($sql);
        	
        	$sql2 = " select count(*) as num from (select count(*) from news n
		            left join news_class nc on nc.news_id=n.id
		        	left join column_class cc on nc.column_class_id=cc.id
		        	left join `column` c on cc.column_id=c.id
		        	where n.title like '%{$newskeyword}%' and 
		        	cc.column_id={$column} and
		             n.id not in 
		            (select nc.news_id from news_class nc
		            where nc.column_class_id = 36) group by n.id) as ssss";
       	 	$count =M()->query($sql2);
		
		 	$rst["total"] = $count[0]['num'];
       	 	$rst['rows'] = $list;
       		 $this->ajaxReturn($rst);
        } else {
        		
        	$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
			$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;	
        	$offset=($page-1)*$rows;
			
        	$id = I('get.id', 0);
        	$sql = "select n.id, n.title, cc.column_id,c.column_name from news n
		        	left join news_class nc on nc.news_id=n.id
		        	left join column_class cc on nc.column_class_id=cc.id
		        	left join `column` c on cc.column_id=c.id
		            where n.id not in 
		            (select nc.news_id from news_class nc
		            where nc.column_class_id = {$id}) group by n.id limit {$offset},{$rows}";
       	 $list = M()->query($sql);
       	 
       	 $sql2 = " select count(*) as num from (select count(*) from news n
		            where n.id not in 
		            (select nc.news_id from news_class nc
		            where nc.column_class_id = 36) group by n.id) as ssss";
       	 $count =M()->query($sql2);
		
		 $rst["total"] = $count[0]['num'];
       	 $rst['rows'] = $list;
        	$this->ajaxReturn($rst);
        }
    }
	
    /**
     * 添加新闻成员
     *
     * @return void
     */
    public function addNews()
    {
        $rst = array();
        $data = array();
        $data['column_class_id'] = I('post.columnClassId');
        $data['news_id'] = I('post.newsId');

        if (D('news_class')->add($data) !== false) {
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
    public function removeNews()
    {
        $rst = array();
        $map = array();
        $map['column_class_id'] = I('post.columnClassId');
        $map['news_id'] = I('post.newsId');

        if (D('news_class')->where($map)->limit(1)->delete() !== false) {
            $rst['success'] = true;
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '移除失败！';
        }
        $this->ajaxReturn($rst);
    }
	

}