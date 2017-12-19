<?php
 
// +------------------------------------------------------------------------------------------ 
// | Author: longDD <longdd_love@163.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description:  Dates: 2016-08-05
// +------------------------------------------------------------------------------------------ 

namespace Api\Controller;


use Api\Controller;
use Api\Model\CheckModel;

class ContentController extends BaseController
{
    public function _initialize()
    {
        S(array('type'=>'redis'));
    }
    /**
     * 文章列表
     *
     * @return void
     */
    public function rows()
    {
        $page = I('post.page', 1);
        $rows = I('post.rows', 5);
        $data = array();

        if (is_null($page) || $page <= 0) {
            $this->_error(403, '页码错误');
        }

        $startRow = ($page - 1) * $rows;
        $limitStr = (string)$startRow . ',' . (string)$rows;

        $map = array('type' => array('eq', 1), 'status' => array('eq', 1));
        $rst['rows'] = M('post')->where($map)->limit($limitStr)->order('sorting asc')->select();
        $rst['total'] = M('post')->where($map)->count();

        foreach ($rst['rows'] as &$value) {
            $value['create_time'] =  date("Y年m月d日",$value['create_time']);
            $value['update_time'] =  $value['update_time'] ? date("Y年m月d日",$value['update_time']) : '';
            $value['cover_img'] = 'http://'.$_SERVER["SERVER_NAME"].'/upload/'.$value['cover_img'];
        }

        if (empty($rst['rows'])) {
            $this->_error(403, '没有数据了');
        } else {
            $this->_callBack($rst);
        }
    }

    /**
     * 文章内容
     *
     * @return void
     */
    public function content()
    {
        $post = I('post.');

        $map = array('id' => array('eq', $post['id']));
        $rst['rows'] = M('post')->where($map)->find();

        foreach ($rst as &$value) {
            $value['create_time'] =  date("Y年m月d日",$value['create_time']);
            $value['update_time'] =  $value['update_time'] ? date("Y年m月d日",$value['update_time']) : '';
            $value['content'] = html_entity_decode($value['content']);
        }

        if (empty($rst['rows'])) {
            $this->_error(403, '没有数据了');
        } else {
            $this->_callBack($rst);
        }
    }

    /**
     * 新闻列表
     *
     * @return void
     */
    public function news()
    {
        $page = I('post.page', 1);
        $rows = I('post.rows', 5);
        $data = array();

        if (is_null($page) || $page <= 0) {
            $this->_error(403, '页码错误');
        }

        $startRow = ($page - 1) * $rows;
        $limitStr = (string)$startRow . ',' . (string)$rows;

        $map = array('type' => array('eq', 0), 'status' => array('eq', 1));
        $rst['rows'] = M('post')->where($map)->limit($limitStr)->order('sorting asc')->select();
        $rst['total'] = M('post')->where($map)->count();

        if (empty($rst['rows'])) {
            $this->_error(403, '没有数据了');
        } else {
            $this->_callBack($rst);
        }
    }

    /**
     * Banner
     *
     * @return void
     */

    public function banner()
    {
        $rst = array();
        $type = I('get.type');

        if (!$type) {
            $this->_error(403, '非法访问');
        }

        if (empty(S('banner'.$type))) {

            $banner = M('banner')
                ->where(array('type' => $type, 'status' => 1))
                ->order('id DESC,sort ASC')
                ->limit(6)
                ->select();

            foreach ($banner as &$value) {
                $value['image_url'] = 'http://'.$_SERVER["SERVER_NAME"].$value['image_url'];
            }

            S('banner'.$type, $banner);

            $rst['rows'] = $banner;
        } else {
            $rst['rows'] = S('banner'.$type);
        }

        $this->_callBack($rst);

    }

    /**
     * 更新缓存
     *
     * @return void
     */

    public function updateCache()
    {
        S('banner1', null);
        S('banner2', null);
        $this->ajaxReturn(['success'=>true]);

    }
}