<?php
namespace Admin\Controller;

use Admin\Controller;

class phonetypeController extends BaseController
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
     * 列表
     * @author liyang
     * @return void
     */
    public function rows()
    {
        $post = I('post.');

        if ($post['keyword']) {
            $like['name'] =   array('LIKE', '%' . trim($post['keyword']) . '%');
            $like['remark'] =   array('LIKE', '%' . trim($post['keyword']) . '%');
            $like['_logic'] = 'or';
            $map['_complex'] = $like;
        }

        $rst['total'] = M('phone_type')->where($map)->count();
        $rst['rows'] = M('phone_type')->where($map)->limit($this->page())->select();

        $this->ajaxReturn($rst);
    }

    /**
     * 故障列表
     * @author liyang
     * @return void
     */
    public function malRows()
    {
        $rst['rows'] = M('phone')->where(array('phone_type_id' => array('eq', 0)))->field('id, name')->select();

        $this->ajaxReturn($rst);
    }

    /**
     * 分类中的故障列表
     * @author liyang
     * @return void
     */
    public function malTypeRows()
    {
        $get = I('get.');

        $model = M('phone_type');
        $model->join('mt left join `phone` m on mt.id = m.phone_type_id');
        $model->where(array('mt.id' => array('eq', $get['id'])));
        $rst['rows'] = $model->select();

        $this->ajaxReturn($rst);
    }



    /**
     * 添加
     * @author liyang
     * @return void
     */
    public function add()
    {
        $post = I('post.');
        $rst['success'] = M('phone_type')->add($post);

        $this->ajaxReturn($rst);
    }

    /**
     * 管理类型
     * @author liyang
     * @return void
     */
    public function manage()
    {
        $post = I('post.');

        $model = M('phone');
        $model->find($post['maltype']);

        if ($post['type']) {
            $model->phone_type_id =  $post['malfunction'];
        } else {
            $model->phone_type_id = 0;
        }

        $rst['success'] = $model->save();

        $this->ajaxReturn($rst);
    }

    /**
     * 删除
     * @author liyang
     * @return void
     */
    public function Delete()
    {
        $post = I('post.');

        $malRst = M('phone')->where(array('phone_type_id' => array('eq', $post['id'])))->find();

        if (!empty($malRst)) {
            $rst['errorMsg'] = '清空请类目下的所有故障才可删除';
            $this->ajaxReturn($rst);
        }

        $model = M('phone_type');
        $model->find($post['id']);
        $rst['success'] = $model->delete();

        $this->ajaxReturn($rst);
    }

    /**
     * 编辑
     * @author liyang
     * @return void
     */
    public function edit()
    {
        $post = I('post.');

        $model = M('phone_type');
        $model->find(I('get.id'));
        $model->name = $post['name'] ? $post['name'] : $model->name;
        $model->brand_id = $post['brand_id'] ? $post['brand_id'] : $model->brand_id;
        $model->remark = $post['remark'] ? $post['remark'] : $model->remark;
        $rst['success'] = $model->save();

        $this->ajaxReturn($rst);
    }



}