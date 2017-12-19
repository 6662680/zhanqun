<?php

// +------------------------------------------------------------------------------------------ 
// | Author: longDD <longdd_love@163.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 组织 Dates: 2016-08-15
// +------------------------------------------------------------------------------------------ 

namespace Admin\Controller;

use Admin\Controller;

class ClientdataController extends BaseController
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
     * 分享列表
     * @author liyang
     * @return void
     */
    public function userRow()
    {
        $map = array();
        $model = M('client');

        if (I('post.key')) {
            $like['client.client_name'] = array('like','%'.I('post.key').'%');
            $like['client.client_mobile'] = array('like','%'.I('post.key').'%');
            $like['client.client_organization'] = array('like','%'.I('post.key').'%');
            $like['_logic'] = 'or';
            $map['_complex'] = $like;
        }

        if (I('post.city')) {
            $like['client.client_city'] = array('like','%'.I('post.city').'%');
            $like['_logic'] = 'or';
            $map['_complex'] = $like;
        }

        if (I('post.day') && I('post.day') != 'all') {
            $map['contact_time'] = array(array('ELT', (strtotime(I('post.day')))), array('EGT', time()), 'and');
        }

        if (I('post.activity_type')) {
            $map['client.activity_type'] = I('post.activity_type');
        }

        if (I('post.client_type')) {
            $map['client.client_type'] = I('post.client_type');
        }

        /*下单时间*/
        if (!empty(I('post.contact_time_start')) && empty(I('post.contact_time_end'))) {
            $map['contact_time'] = array(array('EGT',strtotime(I('contact_time_start'))));
        } else if(empty(I('post.contact_time_start')) && !empty(I('post.contact_time_end'))) {
            $map['contact_time'] = array(array('ElT',strtotime(I('post.contact_time_end'))) + 24 * 60 * 60 - 1);
        } else if(!empty(I('post.contact_time_start')) && !empty(I('post.contact_time_end'))) {
            $map['contact_time'] = array(array('ElT',(strtotime(I('post.contact_time_end'))) + 24 * 60 * 60 - 1),array('EGT',strtotime(I('post.contact_time_start'))),'and');
        }

        $rst['total'] = $model->where($map)->count();
        $rst['rows'] = $model->where($map)
                        ->join('left join `client_type` on client.client_type = client_type.id')
                        ->join('left join `activity_type` on client.activity_type = activity_type.id')
                        ->order('client.id desc')
                        ->limit($this->page())
                        ->field('client.id, client_organization, client_name, client_mobile, client_city,client.time,
                                client_type.client_type, is_activity, activity_type.activity_type, contact_time, remark, activity_summarize,
                                client_name2, client_mobile2, write_user, result, is_change, create_time, number, time')
                        ->select();

        $this->ajaxReturn($rst);
    }

    /**
     * 添加
     * @author liyang
     * @return void
     */
    public function add()
    {
        $rst = array();
        $id = I('get.id', 0);

        $post = I('post.');
        $post['time'] = strtotime($post['time']);
        $post['contact_time'] = strtotime($post['contact_time']);
        $post['client_city_code'] = M('address')->where(array('name' => array('eq', $post['client_city']), 'leve' => 2))->getField('id');
        $post['create_time'] = time();

        if ($post['is_change'] == 1 && empty($post['number'])) {
            $rst['success'] = false;
            $rst['errorMsg'] = '订单号不许为空';
            $this->ajaxReturn($rst);
        }

        if ($post['is_activity'] == 1 ) {

            if (empty($post['result']) || empty($post['time'])) {
                $rst['success'] = false;
                $rst['errorMsg'] = '下次和本次联系时间不许为空';
                $this->ajaxReturn($rst);
            }
        }

        if ($post['is_change'] == 1 && strlen($post['number']) != 16) {
            $rst['success'] = false;
            $rst['errorMsg'] = '订单号少于16位';
            $this->ajaxReturn($rst);
        }

        if (!$post['client_city_code']) {
            $rst['success'] = false;
            $rst['errorMsg'] = '城市不正确';
            $this->ajaxReturn($rst);
        }

        if (!M('client')->add($post)) {
            $rst['success'] = false;
            $rst['errorMsg'] = '添加失败！';
        } else {
            $rst['success'] = true;
        }

        $this->ajaxReturn($rst);
    }

    /**
     * 编辑
     * @author liyang
     * @return void
     */
    public function edit()
    {
        $rst = array();
        $id = I('get.id', 0);
        $post = I('post.');

        if ($post['is_change'] == 1 && empty($post['number'])) {
            $rst['success'] = false;
            $rst['errorMsg'] = '订单号不许为空';
            $this->ajaxReturn($rst);
        }

        if ($post['is_change'] == 1 && strlen($post['number']) != 16) {
            $rst['success'] = false;
            $rst['errorMsg'] = '订单号少于16位';
            $this->ajaxReturn($rst);
        }

        if ($post['is_activity'] == 1 && empty($post['result']) || empty($post['time'])) {
            $rst['success'] = false;
            $rst['errorMsg'] = '下次和本次联系时间不许为空';
            $this->ajaxReturn($rst);
        }

        $model = M('client');
        $model->find($id);
        $model->client_name = $post['client_name'] ? $post['client_name'] : '';
        $model->client_organization = $post['client_organization'] ? $post['client_organization'] : '';
        $model->client_mobile = $post['client_mobile'] ? $post['client_mobile'] : '';
        $model->client_city = $post['client_city']? $post['client_city'] : '';
        $model->client_city_code = $post['client_city'] ? M('address')->where(array('name' => array('eq', $post['client_city']), 'leve' => 2))->getField('id') : '';
        $model->client_type = M('client_type')->where(array('client_type' => array('eq', $post['client_type'])))->getField('id');
        $model->activity_type = M('activity_type')->where(array('activity_type' => array('eq', $post['activity_type'])))->getField('id');
        $model->contact_time = strtotime($post['contact_time']);
        $model->remark = $post['remark'];
        $model->activity_summarize = $post['activity_summarize'];
        $model->is_activity = $post['is_activity'];
        $model->time = strtotime($post['time']);
        $model->client_name2 = $post['client_name2'] ? $post['client_name2'] : '';
        $model->client_mobile2 = $post['client_mobile2'] ? $post['client_mobile2'] : '';
        $model->write_user = $post['write_user'] ? $post['write_user'] : '';
        $model->result = $post['result'] ? $post['result'] : '';
        $model->is_change = $post['is_change'] ? $post['is_change'] : '';
        $model->number = $post['number'] ? $post['number'] : '';
        $model->create_time = strtotime($post['create_time']);

        if (!$model->client_city_code) {
            $rst['success'] = false;
            $rst['errorMsg'] = '城市不正确';
            $this->ajaxReturn($rst);
        }

        if (!$model->save()) {
            $rst['success'] = false;
            $rst['errorMsg'] = '编辑失败！';
        } else {
            $rst['success'] = true;
        }
        $this->ajaxReturn($rst);
    }

    /**
     * 删除
     * @author liyang
     * @return void
     */
    public function delete()
    {
        $rst = array();
        $model = M('client');
        $model->find(I('post.id'));
        if (!$model->delete()) {
            $rst['success'] = false;
            $rst['errorMsg'] = '删除失败！';
        } else {
            $rst['success'] = true;
        }
        $this->ajaxReturn($rst);
    }

    /**
     * 客户类型下拉
     * @author liyang
     * @return void
     */
    public function clientType()
    {
        $rst = M('client_type')->select();

        $this->ajaxReturn($rst);
    }

    /**
     * 活动类型下拉
     * @author liyang
     * @return void
     */
    public function activityType()
    {
        $rst = M('activity_type')->select();

        $this->ajaxReturn($rst);
    }

    /**
     * 客户类型
     * @author liyang
     * @return void
     */
    public function clientRow()
    {
        $rst['rows'] = M('client_type')->limit($this->page())->select();
        $rst['total'] = M('client_type')->count();

        $this->ajaxReturn($rst);
    }

    /**
     * 活动类型
     * @author liyang
     * @return void
     */
    public function activityRow()
    {
        $rst['rows'] = M('activity_type')->limit($this->page())->select();
        $rst['total'] = M('activity_type')->count();

        $this->ajaxReturn($rst);
    }


    /**
     * 添加活动类型
     * @author liyang
     * @return void
     */
    public function addActivity()
    {
        $post['activity_type'] = I('post.type_name');

        if (!M('activity_type')->add($post)) {

            $rst['success'] = false;
            $rst['errorMsg'] = '添加失败！';
        } else {
            $rst['success'] = true;
        }

        $this->ajaxReturn($rst);
    }

    /**
     * 添加客户类型
     * @author liyang
     * @return void
     */
    public function addClient()
    {
        $post['client_type'] = I('post.type_name');

        if (!M('client_type')->add($post)) {

            $rst['success'] = false;
            $rst['errorMsg'] = '添加失败！';
        } else {
            $rst['success'] = true;
        }

        $this->ajaxReturn($rst);
    }

    /**
     * 编辑活动类型
     * @author liyang
     * @return void
     */
    public function editActType()
    {
        $post['activity_type'] = I('post.type_name');

        $model = M('activity_type');
        $model->find(I('get.id'));
        $model->activity_type = $post['activity_type'];

        if (!$model->save()) {
            $rst['success'] = false;
            $rst['errorMsg'] = '添加失败！';
        } else {
            $rst['success'] = true;
        }

        $this->ajaxReturn($rst);
    }

    /**
     * 编辑客户类型
     * @author liyang
     * @return void
     */
    public function editCliType()
    {
        $post['client_type'] = I('post.type_name');

        $model = M('client_type');
        $model->find(I('get.id'));
        $model->client_type = $post['client_type'];

        if (!$model->save()) {
            $rst['success'] = false;
            $rst['errorMsg'] = '添加失败！';
        } else {
            $rst['success'] = true;
        }

        $this->ajaxReturn($rst);
    }

    /**
     * 删除客户类型
     * @author liyang
     * @return void
     */
    public function deleteCliType()
    {
        $rst = array();
        $model = M('client_type');
        $model->find(I('get.id'));

        if (!$model->delete()) {
            $rst['success'] = false;
            $rst['errorMsg'] = '删除失败！';
        } else {
            $rst['success'] = true;
        }
        $this->ajaxReturn($rst);
    }

    /**
     * 删除活动类型
     * @author liyang
     * @return void
     */
    public function deleteActType()
    {

        $rst = array();
        $model = M('activity_type');
        $model->find(I('get.id'));

        if (!$model->delete()) {
            $rst['success'] = false;
            $rst['errorMsg'] = '删除失败！';
        } else {
            $rst['success'] = true;
        }
        $this->ajaxReturn($rst);
    }

    public function day()
    {
        $now = time();

        // '今天';
        $Today = date('Y-m-d 23:59:59', $now);

        //'3天';
        $Three = date('Y-m-d 23:59:59', strtotime('+2 day', $now));

        //'5天内';
        $Five = date('Y-m-d 23:59:59', strtotime('+4 day', $now));

        //'7天内';
        $Week = date('Y-m-d 23:59:59', strtotime('+6 day', $now));

        //'30天';
        $Month = date('Y-m-d 23:59:59', strtotime('+29 day', $now));

        $time = array(
            array('key' => '全部', 'value' => 'all'),
            array('key' => '今天', 'value' => $Today),
            array('key' => '3天', 'value' => $Three),
            array('key' => '5天', 'value' => $Five),
            array('key' => '7天', 'value' => $Week),
            array('key' => '30天', 'value'=> $Month),
        );

        $this->ajaxReturn($time);
    }

    /**
     * 导出
     * @author liyang
     * @return void
     */
    public function export()
    {
        $columns[] = array('ID', '公司名称', '联系人', '联系电话', '城市', '客户类型', '活动类型', '下次联系时间', '留言', '活动总结', '
                        第二联系人', '第二联系方式', '填写人', '本次联系结果', '是否转化', '订单号', '创建时间');
        $rst = array();
        $map = array();

        if (I('post.key')) {
            $like['client.client_name'] = array('like','%'.I('post.key').'%');
            $like['client.client_mobile'] = array('like','%'.I('post.key').'%');
            $like['client.client_organization'] = array('like','%'.I('post.key').'%');
            $like['_logic'] = 'or';
            $map['_complex'] = $like;
        }

        if (I('post.city')) {
            $like['client.client_city'] = array('like','%'.I('post.city').'%');
            $like['_logic'] = 'or';
            $map['_complex'] = $like;
        }

        $result = M('client')->where($map)
            ->join('left join `client_type` on client.client_type = client_type.id')
            ->join('left join `activity_type` on client.activity_type = activity_type.id')
            ->order('client.id desc')
            ->field('client.id, client_organization, client_name, client_mobile, client_city, client_type.client_type,
                    activity_type.activity_type, contact_time, remark, activity_summarize, client_name2, client_mobile2,
                    write_user, result, is_change, number, create_time')
            ->select();

        foreach ($result as $value) {
            $columns[] = array($value['id'], $value['client_organization'], $value['client_name'], $value['client_mobile'],
                $value['client_city'], $value['client_type'], $value['activity_type'], date('Y-m-d-H:i:s',$value['contact_time']),
                $value['remark'], $value['activity_summarize'], $value['client_name2'], $value['client_mobile2'], $value[' write_user'],
                $value['result'], $value['is_change'], $value['number'], $value['create_time']
            );
        }

        $this->exportData('大客户数据'.date('Y_m_d_H_i_s'), $columns);
    }

    /**
     * 导入
     * @author liyang
     * @return void
     */
    public function  import()
    {
        $rst = array();
        $num = 0;
        Vendor('PHPExcel.Classes.PHPExcel.IOFactory');

        $inputFileType = \PHPExcel_IOFactory::identify($_FILES['fitting_file']['tmp_name']);
        $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel = $objReader->load($_FILES['fitting_file']['tmp_name']);
        $data = $objPHPExcel->getSheet(0)->toArray();

        $total_rows = 0; //总行数
        $fail_rows = array(); //导入失败行
        $flag = true;

        foreach ($data as $key => $value) {

            if ($key < 1) {
                continue;
            }

            if (empty($value[1]) && empty($value[2]) && empty($value[3]) && empty($value[4]) && empty($value[5]) && empty($value[6])) {
                continue;
            }

            $line = $key ++;

            if (empty($value[1]) || empty($value[3]) || empty($value[4])) {
                $rst['success'] = false;
                $rst['msg'] = '导入失败(客户公司，联系电话，城市是必填项目,错误第('. $line .'))';
            }

            $add[$num] = array(
                'client_city_code' => M('address')->where(array('name' => array('eq', $value[4]), 'leve' => 2))->getField('id'),
                'client_organization' => $value[1],
                'client_name' => $value[2],
                'client_mobile' => $value[3],
                'client_city' => $value[4],
                'client_type' =>  M('client_type')->where(array('client_type' => array('eq', $value[5])))->getField('id'),
                'activity_type' => M('activity_type')->where(array('activity_type' => array('eq', $value[6])))->getField('id'),
                'contact_time' => strtotime($value[7]),
                'remark' => $value[8],
                'activity_summarize' => $value[9],
            );

            if (empty($add[$num]['client_city_code'])) {

                $rst['success'] = false;
                $rst['msg'] = '导入失败(第'. $line .'行城市错误)！';
                $this->ajaxReturn($rst);
            }

            if (empty($add[$num]['client_type'])) {

                $rst['success'] = false;
                $rst['msg'] = '导入失败(第'. $line .'行客户类型错误)！';
                $this->ajaxReturn($rst);
            }

            if (empty($add[$num]['activity_type'])) {

                $rst['success'] = false;
                $rst['msg'] = '导入失败(第'. $line .'行活动类型错误)！';
                $this->ajaxReturn($rst);
            }

            $num++;
        }

        if (!M('client')->addAll($add)) {

            $rst['success'] = false;
            $rst['msg'] = '导入失败！';
        } else {
            $rst['success'] = true;
            $rst['msg'] = '导入成功！';
        };

        $this->ajaxReturn($rst);
    }
}