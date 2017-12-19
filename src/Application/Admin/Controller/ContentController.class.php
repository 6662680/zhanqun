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

class ContentController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index(){
        $this->display();
       
    }

    /**
     * 文章列表
     *
     * @return void
     */
    public function rows()
    {

        $post = D('post');

        $where = " where 1=1";
        if ( $keyword = I('post.keyword')) {
            $type = 'post.'.I('post.type');
            $where .= ' AND '. $type .' like \'%'.$keyword.'%\'';
        }
        if (I('post.status') == 1) {
            $where .= ' AND post.status = 1';
        }
        if (I('post.status') == 2) {
            $where .= ' AND post.status = 0';
        }

        $rst['total'] = $post->query("SELECT count(*) as total FROM `post` $where");
        $rst['total'] = $rst['total']['0']['total'];

        $page = $this->page();
        $sql = "SELECT post.update_time,post.url,post.content,post.create_time,post.id,post.cover_img,post.type,post.title,post.source,post.introduction,post.sorting,post.media_img,post.hit,post.status,menu.title as menu_title
                FROM `post` left join menu ON post.menu_id = menu.id  $where  ORDER BY post.id DESC limit $page";
        $rst['rows'] = $post->query($sql);

        foreach ($rst['rows'] as &$value) {
            $value['content'] = html_entity_decode($value['content']);
        }

        $this->ajaxReturn($rst);
    }

    /**
     * 文章内容
     *
     * @return void
     */
    public function getContent(){
        $post = M('post');
        $list = $post->find(I('post.id'));

        $this->ajaxReturn($list);
    }

    /**
     * @Desc: 添加内容
     * @User: liyang
     */
    public function add()
    {
        $post = I('post.','','');
        $model = M("post");

        $imgInfo = $this->upload();

        if (!$imgInfo['success']) {
            $rst['success'] = false;
            $rst['errorMsg'] = $imgInfo['errorMsg'];
        } else {

            if ($imgInfo['info']['cover_img']) {
                $model->cover_img =  $imgInfo['info']['cover_img']['savepath'] . $imgInfo['info']['cover_img']['savename'] ;
            }

            if ($imgInfo['info']['media_img']['savepath']) {
                $model->media_img = $imgInfo['info']['media_img']['savepath'] . $imgInfo['info']['media_img']['savename'];
            }
        }

        if ($post['type'] == 1) {


            $model->content = $post['content'];

        } else {
            $model->url = $post['url'];
            $model->source = $post['source'];
        }

        $model->type = $post['type'];
        $model->title = $post['title'];
        $model->status = $post['status'];
        $model->create_time = time();
        $model->sorting = $post['sorting'];
        $model->menu_title = $post['menu_title'];
        $model->introduction = $post['introduction'];

        if ($model->add()){
            $this->ajaxReturn(['success'=>true]);
        } else {
            $this->ajaxReturn(['success'=>false,'errorMsg'=>'添加失败']);
        }
    }

    /**
     * 文章编辑
     *
     * @return void
     */
    public function edit()
    {
        $post = I('post.');
        $model = M('post');
        $model->find(I('get.id', 0));

        $imgInfo = $this->upload();

        if (!$imgInfo['success']) {
            $rst['success'] = false;
            $rst['errorMsg'] = $imgInfo['errorMsg'];
        } else {

            if ($imgInfo['info']['cover_img']) {
                $model->cover_img =  $imgInfo['info']['cover_img']['savepath'] . $imgInfo['info']['cover_img']['savename'] ;
            }

            if ($imgInfo['info']['media_img']['savepath']) {
                $model->media_img = $imgInfo['info']['media_img']['savepath'] . $imgInfo['info']['media_img']['savename'];
            }
        }

        if ($post['type'] == 1) {

            $model->content = $post['content'];

        } else {
            $model->url = $post['url'];
            $model->source = $post['source'];
        }

        $model->type = $post['type'];
        $model->title = $post['title'];
        $model->status = $post['status'];
        $model->create_time = time();
        $model->sorting = $post['sorting'];
        $model->menu_title = $post['menu_title'];
        $model->introduction = $post['introduction'];

        if ($model->save()){
            $this->ajaxReturn(['success'=>true]);
        } else {

            $this->ajaxReturn( ['success'=>false,'errorMsg'=>'修改失败']);
        }
    }

    /**
     * 删除文章
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