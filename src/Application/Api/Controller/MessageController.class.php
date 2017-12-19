<?php

namespace Api\Controller;

class MessageController extends BaseController
{
    public function noticeList()
    {
        $post = I('request.');
        $e_id = intval(trim($post['engineerId']));
        $page = intval(trim($post['page']));

        $rst = array();

        if ($e_id > 0) {
            $model = D('Admin/MessageEngineer');
            $rst['status'] = 1;
            $rst['data'] = $model->getListByEngineerId($e_id, $page);
        }

        $this->ajaxReturn($rst);
    }

    public function isReadMessage()
    {
        $post = I('request.');
        $e_id = intval(trim($post['engineerId']));
        $m_id = intval(trim($post['msgId']));
        $rst = array();
        if ($e_id > 0 && $m_id > 0) {

            $model = D('Admin/MessageEngineer');
            $res = $model->setMessageRead($e_id, $m_id);
            if ($res) {
                $rst['status'] = 1;
                $rst['info'] = '成功';
            } else {
                $rst['status'] = 0;
                $rst['info'] = '失败';
            }
        } else {
            $rst['status'] = 0;
            $rst['info'] = '参数错误';
        }

        $this->ajaxReturn($rst);
    }

    public function isCheckMessage()
    {
        $post = I('request.');
        $e_id = intval(trim($post['engineerId']));
        $m_id = intval(trim($post['msgId']));
        $rst = array();
        if ($e_id > 0 && $m_id > 0) {

            $model = D('Admin/MessageEngineer');
            $res = $model->setMessageCheck($e_id, $m_id);
            if ($res) {
                $rst['status'] = 1;
                $rst['info'] = '成功';
            } else {
                $rst['status'] = 0;
                $rst['info'] = '失败';
            }
        } else {
            $rst['status'] = 0;
            $rst['info'] = '参数错误';
        }

        $this->ajaxReturn($rst);
    }
}