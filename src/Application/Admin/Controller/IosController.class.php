<?php

namespace Admin\Controller;

use Admin\Controller;

class IosController extends BaseController
{
    public function index()
    {
        $this->display();
    }

    public function rows()
    {
        $model = D('Iosversion');
        $rst = $model->listByPage($this->page());
        $this->ajaxReturn($rst);
    }

    public function add()
    {
        $post = I('request.');
        $data = array(
            'version' => trim($post['version']),
            'update' => intval(trim($post['update']))
        );
        $model = D('Iosversion');
        $res = $model->addData($data);

        $this->ajaxReturn($res);
    }

    public function edit()
    {
        $post = I('request.');
        $data = array(
            'version' => trim($post['version']),
            'update' => intval(trim($post['update']))
        );
        $id = intval(trim($post['id']));
        $model = D('Iosversion');
        $res = $model->edit($id, $data);

        $this->ajaxReturn($res);
    }

    public function publish()
    {
        $id = intval(trim(I('request.id')));

        if ($id > 0) {
            $model = D('Iosversion');
            $res = $model->publish($id);

            $this->ajaxReturn($res);
        }
    }
}