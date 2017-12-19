<?php

namespace Admin\Model;

use Think\Model;

class MessagePushModel extends Model
{
    /**
     * 消息类型数组
     * @var array
     */
//    private static $types = array('0' => '全部', '1' => '短信', '2' => 'APP推送');
    private static $types = array('2' => 'APP推送', '1' => '短信');

    /**
     * 静态方法得到所有的消息类型
     *
     * @return array
     */
    public static function getTypes() {
        return self::$types;
    }

    /**
     * 自动完成
     * @var array
     */
    protected $_auto = array (
        array('time', 'time', self::MODEL_BOTH, 'function')
    );

    /**
     * 自动验证
     * @var array
     */
    protected $_validate = array(
        array('type', 'checkType', '消息类型错误', self::EXISTS_VALIDATE, 'callback', self::MODEL_BOTH)
    );

    /**
     * 验证消息类型（判断 $type 是不是在 self::$type 的键中）
     *
     * @param $type
     * @return bool
     */
    protected function checkType($type) {
        $types = self::$types;
        return array_key_exists($type, $types);
    }

    /**
     * 添加消息
     *
     * @param $data 消息标题、内容等
     * @return array array('res' => Int, 'msg' => String);
     */
    public function addMessage($data)
    {
        // 要保存的数据
        $saveData = array(
            'title' => $data['title'],
            'content' => $data['content'],
            'type' => $data['type'],
            'eids' => $data['eids'] // 工程师id
        );

        $rst = array();

        if ($this->create($saveData)) {

            if (empty($saveData['eids']) || strtolower($saveData['eids']) == 'null') { // 没有工程师id

                if ($this->add()) {
                    $rst['res'] = 0;
                    $rst['msg'] = '添加成功';
                } else {
                    $rst['res'] = -1;
                    $rst['msg'] = '添加失败';
                }
            } else { // 有工程师id

                $this->startTrans();

                if ($id = $this->add()) { // 消息添加成功

                    $addData = array();
                    $ids = $saveData['eids'];

                    if (is_string($ids)) {
                        $ids = explode(',', $ids);
                    }

                    foreach ($ids as $value) {
                        if (intval($value) == 0) continue;
                        $addData[] = array(
                            'message_id' => $id,
                            'engineer_id' => $value
                        );
                    }

                    if (M('messageEngineer')->addAll($addData)) {
                        $rst['res'] = 0;
                        $rst['msg'] = '添加成功';
                        $this->commit();
                    } else {
                        $rst['res'] = -3;
                        $rst['msg'] = '添加失败';
                        $this->rollback();
                    }
                } else { // 消息添加失败
                    $rst['res'] = -2;
                    $rst['msg'] = '添加失败';
                    $this->rollback();
                }
            }

        } else {
            $rst['res'] = -1;
            $rst['msg'] = $this->getError();
        }

        return $rst;
    }

    function updateMessage($data) {
        // 要保存的数据
        $saveData = array(
            'id' => $data['id'],
            'title' => $data['title'],
            'content' => $data['content'],
            'type' => $data['type'],
            'eids' => $data['eids'] // 工程师id
        );
        $rst = array();

        if ($this->create($saveData)) {
            $this->startTrans();

            if ($this->save()) {
                $meModel = M('messageEngineer');
                $delRes = $meModel->where(array('message_id' => $saveData['id']))->delete();
                $addRes = true;
                $ids = $saveData['eids'];

                if ($ids) {
                    $addData = array();

                    if (is_string($ids)) {
                        $ids = explode(',', $ids);
                    }

                    foreach ($ids as $value) {
                        if (intval($value) == 0) continue;
                        $addData[] = array(
                            'message_id' => $saveData['id'],
                            'engineer_id' => $value
                        );
                    }

                    $addRes = $meModel->addAll($addData);
                }

                if (($delRes !== false) && ($addRes !== false) ) {
                    $this->commit();
                    $rst['res'] = 0;
                    $rst['msg'] = '更新成功';
                } else {
                    $this->rollback();
                    $rst['res'] = -1;
                    $rst['msg'] = '推送工程师更新失败';
                }

            } else {
                $this->rollback();
                $rst['res'] = -3;
                $rst['msg'] = '消息保存失败';
            }
        } else {
            $rst['res'] = -1;
            $rst['msg'] = $this->getError();
        }

        return $rst;
    }
}