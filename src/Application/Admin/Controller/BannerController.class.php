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

class BannerController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index(){
        $this->display();
    }

    /**
     * banner列表
     *
     * @return void
     */
    public function rows()
    {
        $sql = 'SELECT * FROM `banner` WHERE  status <> -1 ORDER BY id DESC';
        $list = M()->query($sql);
        $this->ajaxReturn($list);
    }

    /**
     * @Desc: 添加内容
     * @User: liyang
     */
    public function add(){

        $post = M("banner");
        $info = $this->upload();

        if (!$info['success']) {
            $rst['success'] = false;
            $rst['errorMsg'] = $info['errorMsg'];
        } else {
            $post->image_url = '/upload/' . $info['info']['image']['savepath']  .$info['info']['image']['savename'];
        }
        $post->type = I('post.type');
        $post->title = I('post.title');
        $post->sort = I('post.sort');
        $post->url = I('post.url');
        $post->create_time = strtotime(I('post.create_time'));
        $post->online_time = strtotime(I('post.online_time'));
        $post->status = I('post.status');

        if ($post->add()){
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
    public function edit(){
        $post = M('banner');
        $post->find(I('get.id', 0));
        $info = $this->upload();

        if (!$info['success']) {
            $rst['success'] = false;
            $rst['errorMsg'] = $info['errorMsg'];
        } else {
            $post->image_url = '/upload/' . $info['info']['image']['savepath']  .$info['info']['image']['savename'];
        }
        $post->type = I('post.type');
        $post->title = I('post.title');
        $post->sort = I('post.sort');
        $post->url = I('post.url');
        $post->create_time = strtotime(I('post.create_time'));
        $post->online_time = strtotime(I('post.online_time'));
        $post->status = I('post.status');

        if ($post->save()){
            $this->ajaxReturn(['success'=>true]);
        } else {
            $this->ajaxReturn( ['success'=>false,'errorMsg'=>'修改失败']);
        }
    }


    /**
     * 禁用文章
     *
     * @return void
     */

    public function disable(){
        $post = M('banner');
        if (I('post.status') == 1){
            $status = 0;
        } else {
            $status = 1;
        }

        $post->find(I('post.id', 0));
        $post->status = $status;

        if ($post->save()){
            $this->ajaxReturn(['success'=>true]);
        } else {
            $this->ajaxReturn( ['success'=>false,'errorMsg'=>'禁用失败']);
        }
    }

}