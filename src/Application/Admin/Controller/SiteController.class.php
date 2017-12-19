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

        if (!empty($post['organization_id'])) {
            $map['uo.organization_id'] = $post['organization_id'];
        }

        if (is_numeric($post['status'])) {
            $map['u.status'] = $post['status'];
        }

        if (!empty($post['keyword'])) {
            $where = array();
            $where['u.username']  = array('like', '%' . $post['keyword'] . '%');
            $where['u.realname']  = array('like', '%' . $post['keyword'] . '%');
            $where['u.telphone']  = array('like', '%' . $post['keyword'] . '%');
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

}