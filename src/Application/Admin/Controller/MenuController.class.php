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

class MenuController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index(){
        $this->display();
    }
    /**
     * @Desc: 栏目显示
     * @User: liyang
     */
    public function rows()
    {

        $map = array();
        $map['status'] = array('neq', -1);
        $menu = D('menu');

        $list = $menu->order('create_time')->where($map)
            ->field('id, title, seo_title, seo_keywords, seo_description, type, pid, create_time, update_time, status')
            ->select();
        $this->ajaxReturn($list);

    }

    /**
     * @Desc: 添加栏目
     * @User: liyang
     */
    public function add(){

        $meun = M("menu");
        $meun->title = I('post.title');
        $meun->seo_title = I('post.seo_title');
        $meun->seo_keywords = I('post.seo_keywords');
        $meun->seo_description = I('post.seo_description');
        $meun->type = 3;
        $meun->pid = 0;
        $meun->status = I('post.status');
        if ($meun->add()){
            $this->ajaxReturn(['success'=>true]);
        } else {
            $this->ajaxReturn(['success'=>false,'errorMsg'=>'添加失败']);
        }
    }

    /**
     * @Desc: 禁用栏目
     * @User: liyang
     */
    public function disable(){

        if (I('post.status') == 1){
            $status = 0;
        }
        if (I('post.status') == 0){
            $status = 1;
        }
        $meun = M("menu");
        $meun->find(I('post.id', 0));
        $meun->status = $status;
        if ($meun->save()){
            $this->ajaxReturn(['success'=>true]);
        } else {
            $this->ajaxReturn(['success'=>false,'errorMsg'=>'修改失败']);
        }
    }

    /**
     * @Desc: 编辑栏目
     * @User: liyang
     */
    public function edit(){

        $id = I('get.id', 0);
        $meun = M("menu");
        $meun->find($id);
        $meun->title = I('post.title');
        $meun->seo_title = I('post.seo_title');
        $meun->seo_keywords = I('post.seo_keywords');
        $meun->seo_description = I('post.seo_description');
        $meun->type = 3;
        $meun->pid = 0;
        $meun->status = I('post.status');

        if ($meun->save()){
            $this->ajaxReturn(['success'=>true]);
        } else {
            $this->ajaxReturn(['success'=>false,'errorMsg'=>'修改失败']);
        }
    }
  }