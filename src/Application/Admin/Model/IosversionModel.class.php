<?php

namespace Admin\Model;

use Think\Model;

class IosversionModel extends Model
{
    // 发布状态
    static private $updateStatus = array('0' => '未发布', '1' => '已发布');

    /**
     * 得到所有的发布状态
     *
     * @return array
     */
    static public function getUpdateStatuses()
    {
        return self::$updateStatus;
    }

    public function listByPage($page)
    {
        $rst = array('total' => 0, 'rows' => array());
        $rst['total'] = $this->count();
        $list = $this->field(array('id', 'version', 'update'))->limit($page)->order('`id` DESC')->select();
        $rst['rows'] = $list;
        return $rst;
    }

    public function edit($id, $data)
    {
        $rst = array();

        if (intval($id) > 0 && !empty($data)) {
            $where['id'] = intval($id);

            if ($this->where($where)->save($data) !== false) {
                $rst['code'] = 0;
                $rst['msg'] = '保存成功';
            } else {
                $rst['code'] = -2;
                $rst['msg'] = '保存失败';
            }
        } else {
            $rst['code'] = -1;
            $rst['msg'] = '数据出错';
        }

        return $rst;
    }

    public function addData($data)
    {
        $rst = array();

        if (!empty($data)) {

            if ($this->add($data) !== false) {
                $rst['code'] = 0;
                $rst['msg'] = '添加成功';
            } else {
                $rst['code'] = -2;
                $rst['msg'] = '添加失败';
            }
        } else {
            $rst['code'] = -1;
            $rst['msg'] = '数据出错';
        }

        return $rst;
    }

    public function publish($id)
    {
        $rst = array();

        if (intval($id) > 0) {
            $where['id'] = intval($id);
            $data['update'] = 1;

            if ($this->where($where)->save($data) !== false) {
                $rst['code'] = 0;
                $rst['msg'] = '发布成功';
            } else {
                $rst['code'] = -2;
                $rst['msg'] = '发布失败';
            }

        } else {
            $rst['code'] = -1;
            $rst['msg'] = '数据ID出错';
        }

        return $rst;
    }
}