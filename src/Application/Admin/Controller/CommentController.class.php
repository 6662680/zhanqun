<?php
 
// +------------------------------------------------------------------------------------------ 
// | Author: longDD <longdd_love@163.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description:  Dates: 2016-08-05
// +------------------------------------------------------------------------------------------ 

namespace Admin\Controller;

use Admin\Controller;

class CommentController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index(){
        $this->display();
       
    }

    /**
     * 留言列表
     *
     * @return void
     */
    public function rows()
    {
        $where = " where 1=1";
        $post = I('post.');

        if ( $keyword = $post['keyword']) {
            $type = $post['type'];
            $where .= ' AND '. $type .' like \'%'.$keyword.'%\'';
        }

        if ($post['status'] == 1) {
            $where .= ' AND status = 1';
        }

        if ($post['status'] == 2) {
            $where .= ' AND status = 0';
        }

        if ($post['start_time'] && empty($post['end_time'])) {
            $where .= ' AND '.'time > '.strtotime($post['start_time']);
        }


        if ($post['end_time'] && empty($post['start_time'])) {
            $where .= ' AND '.'time > '.(strtotime($post['end_time']) +24*60*60-1);
        }

        if ($post['end_time'] && $post['start_time']) {
            $where .= ' AND '.'time > '.strtotime($post['start_time']) . ' AND ' .'time < '. (strtotime($post['end_time']) +24*60*60-1) ;
        }


        $rst['total'] = M()->query("SELECT count(*) as total FROM `comment` $where");
        $rst['total'] = $rst['total']['0']['total'];

        $page = $this->page();
        $sql = "SELECT * FROM `comment`  $where  ORDER BY id DESC limit $page";
        $rst['rows'] = M()->query($sql);

        $this->ajaxReturn($rst);
    }

    /**
     * 导出
     *
     * @return void
     */
    public function export()
    {
        $where = " where 1=1";
        $post = I('post.');
        $params = array();
        $params[] = array('手机号', '留言时间', '内容');

        if ( $keyword = $post['keyword']) {
            $type = $post['type'];
            $where .= ' AND '. $type .' like \'%'.$keyword.'%\'';
        }

        if ($post['status'] == 1) {
            $where .= ' AND status = 1';
        }

        if ($post['status'] == 2) {
            $where .= ' AND status = 0';
        }

        if ($post['start_time'] && empty($post['end_time'])) {
            $where .= ' AND '.'time > '.strtotime($post['start_time']);
        }


        if ($post['end_time'] && empty($post['start_time'])) {
            $where .= ' AND '.'time > '.(strtotime($post['end_time']) +24*60*60-1);
        }

        if ($post['end_time'] && $post['start_time']) {
            $where .= ' AND '.'time > '.strtotime($post['start_time']) . ' AND ' .'time < '. (strtotime($post['end_time']) +24*60*60-1) ;
        }

        $sql = "SELECT * FROM `comment`  $where  ORDER BY id DESC";

        $rst['rows'] = M()->query($sql);

        foreach ($rst['rows'] as $item) {
            $params[] = array($item['cellphone'],  date("Y-m-d H:i:s", $item['time']), $item['content']);
        }

        $this->exportData('留言内容列表', $params);
    }

//    /**
//     * 文章内容
//     *
//     * @return void
//     */
//    public function getContent(){
//        $post = M('post');
//        $list = $post->find(I('post.id'));
//
//        $this->ajaxReturn($list);
//    }
//
//    /**
//     * @Desc: 添加内容
//     * @User: liyang
//     */
//    public function add(){
//
//        $post = M("post");
//        $post->title = I('post.title');
//        $post->seo_title = I('post.seo_title');
//        $post->seo_keywords = I('post.seo_keywords');
//        $post->seo_description = I('post.seo_description');
//        $post->create_time = I('post.create_time')?I('post.create_time'):time();
//        $post->content = I('post.content');
//        $post->status = I('post.status');
//        if ($post->add()){
//            $this->ajaxReturn(['success'=>true]);
//        } else {
//            $this->ajaxReturn(['success'=>false,'errorMsg'=>'添加失败']);
//        }
//    }
//
//    /**
//     * 文章编辑
//     *
//     * @return void
//     */
//    public function edit(){
//        $post = M('post');
//        $post->find(I('get.id', 0));
//        $post->title = I('post.title');
//        $post->seo_title = I('post.seo_title');
//        $post->seo_keywords = I('post.seo_keywords');
//        $post->seo_description = I('post.seo_description');
//        $post->status = I('post.status');
//        $post->create_time = strtotime(I('post.create_time'));
//        $post->content = I('post.content');
//        if ($post->save()){
//            $this->ajaxReturn(['success'=>true]);
//        } else {
//
//            $this->ajaxReturn( ['success'=>false,'errorMsg'=>'修改失败']);
//        }
//    }

    /**
     * 删除留言
     *
     * @return void
     */

    public function destroy(){
        $post = M('post');
        $post->find(I('post.id', 0));
        if ($post->delete()){
            $this->ajaxReturn(['success'=>true]);
        } else {

            $this->ajaxReturn( ['success'=>false,'errorMsg'=>'删除失败']);
        }
    }

}