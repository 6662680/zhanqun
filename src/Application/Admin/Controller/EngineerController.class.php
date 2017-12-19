<?php

// +------------------------------------------------------------------------------------------
// | Author: TCG <tianchunguang@weadoc.com>
// +------------------------------------------------------------------------------------------
// | There is no true,no evil,no light,there is only power.
// +------------------------------------------------------------------------------------------
// | Description: 工程师控制器 Dates: 2016-09-21
// +------------------------------------------------------------------------------------------

namespace Admin\Controller;

use Admin\Controller;

class EngineerController extends BaseController 
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
     *
     * @return void
     */
    public function rows()
    {
        $map = array();
        $rst = array();
        $post = I('post.');
        
        if (!empty($post['organization_id'])) {
            $map['engineer.organization_id'] = $post['organization_id'];
        }
        
        if (is_numeric($post['status'])) {
            $map['engineer.status'] = $post['status'];
        }
        
        if (!empty($post['keyword'])) {
            $like['engineer.id'] = array('eq', trim($post['keyword']));
            $like['engineer.work_number'] = array('like', '%' . trim($post['keyword']) . '%');
            $like['engineer.name'] = array('like', '%' . trim($post['keyword']) . '%');
            $like['engineer.cellphone'] = array('like', '%' . trim($post['keyword']) . '%');
            $like['_logic'] = 'or';
            $map['_complex'] = $like;
        }
    
        $count = M('engineer')->where($map)->count();
        $rst['total'] = $count;
    
        $list = M('engineer')->join('left join engineer_level el on engineer.level = el.id')
                ->join('left join organization org on engineer.organization_id = org.id')
                ->join('left join engineer_info ei on engineer.id = ei.engineer_id')
                ->where($map)->limit($this->page())
                ->field('engineer.*, sex, avatar, brith, ei.address, id_card, id_card, email, weixin, alipay, el.title, org.alias, quota')
                ->order('id asc')
                ->select();
        $rst['rows'] = $list;
    
        $this->ajaxReturn($rst);
    }
    
    /**
     * 增加
     *
     * @return void
     */
    public function add()
    {
        $rst = array();
        $data = array();
        $data = I('post.');
        
        $map = array();
        $map['work_number'] = $data['work_number'];
        
        if (M('engineer')->where($map)->count())
        {
            $rst['success'] = false;
            $rst['errorMsg'] = '工号已经存在';
            $this->ajaxReturn($rst);
        }
        
        $map = array();
        $map['cellphone'] = $data['cellphone'];
        
        if (M('engineer')->where($map)->count())
        {
            $rst['success'] = false;
            $rst['errorMsg'] = '手机号已经存在';
            $this->ajaxReturn($rst);
        }
        
        $info = $this->upload();
        
        if ($info['success']) {
            $data['avatar'] = '/upload/' . $info['info']['avatar']['savepath'] . $info['info']['avatar']['savename'];
        } 
        
        $data['password'] = createPassword($data['password']);
        $data['brith'] = strtotime($data['brith']);
        $data['create_time'] = time();
        $data['update_time'] = time();
        
        M()->startTrans();
        $id = M('engineer')->add($data);
        $data['engineer_id'] = $id;
        M('engineer_info')->add($data);
        
        if (M()->commit() !== false) {
            $rst['success'] = true;
        } else {
            M()->rollback();
            $rst['success'] = false;
            $rst['errorMsg'] = '添加失败！';
        }
        
        $this->ajaxReturn($rst);
    }
    
    /**
     * 更新
     *
     * @return void
     */
    public function edit()
    {
        $rst = array();
        $map = array();
        $map['id'] = I('get.id/d');
        $map2['engineer_id'] = $map['id'];
        $data = I('post.');
        $item = M('engineer')->where($map)->find();
        
        $map3 = array();
        $map3['work_number'] = $data['work_number'];
        $map3['id'] = array('neq', $map['id']);
        
        if (M('engineer')->where($map3)->count())
        {
            $rst['success'] = false;
            $rst['errorMsg'] = '工号已经存在';
            $this->ajaxReturn($rst);
        }
        
        $map3 = array();
        $map3['cellphone'] = $data['cellphone'];
        $map3['id'] = array('neq', $map['id']);
        
        if (M('engineer')->where($map3)->count())
        {
            $rst['success'] = false;
            $rst['errorMsg'] = '手机号已经存在';
            $this->ajaxReturn($rst);
        }
        
        if ($item) {
            
            //工程师更换城市判断是否还有剩余物料，若有必须先退还物料才能更换城市
            if ($data['organization_id'] != $item['organization_id']) {
                
                $where = array(
                    'engineer_id' => $item['id'],
                    'organization_id' => $item['organization_id'],
                );
                
                if (M('engineer_warehouse')->where($where)->sum('amount') > 0) {
                    $rst['success'] = false;
                    $rst['errorMsg'] = '该工程师还有剩余物料，必须先退还剩余物料才能更换地区(组织)！';
                    $this->ajaxReturn($rst);
                }
            }
            
            $info = $this->upload();
        
            if ($info['success']) {
                $data['avatar'] = '/upload/' . $info['info']['avatar']['savepath'] . $info['info']['avatar']['savename'];
            } 
                
            $data['brith'] = strtotime($data['brith']);
            $data['update_time'] = time();
            
            M()->startTrans();
            M('engineer')->where($map)->save($data);
            M('engineer_info')->where($map2)->save($data);
            
            if (M()->commit() !== false) {
                $rst['success'] = true;
            } else {
                M()->rollback();
                $rst['success'] = false;
                $rst['errorMsg'] = '编辑失败！';
            }
            
            $this->ajaxReturn($rst);
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '记录不存在！';
        }
    
        $this->ajaxReturn($rst);
    }
    
    /**
     * 删除
     *
     * @return void
     */
    public function delete()
    {
        $id = I('post.id');
        $rst = array();
        $map = array();
        $map['id'] = $id;
        $item = M('engineer')->where($map)->find();
    
        if ($item) {
            
            $data = array();
            $data['status'] = '-1';
            
            if (M('engineer')->where($map)->save($data) !== false) {
                $rst['success'] = true;
            } else {
                $rst['success'] = false;
                $rst['errorMsg'] = '删除失败！';
            }
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '记录不存在！';
        }
    
        $this->ajaxReturn($rst);
    }
    
    /**
     * 重置密码
     *
     * @return void
     */
    public function reset()
    {
        $id = I('post.id');
        $rst = array();
        $map = array();
        $map['id'] = $id;
        $user = M('engineer')->where($map)->find();
    
        if ($user) {
            
            $data = array();
            $data['password'] = createPassword('12345678');
    
            if (M('engineer')->where($map)->limit(1)->save($data) !== false) {
                $rst['success'] = true;
            } else {
                $rst['success'] = false;
                $rst['errorMsg'] = '重置失败！';
            }
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '记录不存在！';
        }
    
        $this->ajaxReturn($rst);
    }

    /**
     * 重置设备
     *
     * @return void
     */
    public function emptys()
    {
        $id = I('post.id');
        $rst = array();
        $map = array();
        $map['id'] = $id;
        $user = M('engineer')->where($map)->find();
    
        if ($user) {
            
            $data = array();
            $data['registration_id'] = '';
    
            if (M('engineer')->where($map)->limit(1)->save($data) !== false) {
                $rst['success'] = true;
            } else {
                $rst['success'] = false;
                $rst['errorMsg'] = '重置失败！';
            }
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '记录不存在！';
        }
    
        $this->ajaxReturn($rst);
    }
    
    /**
     * 工程师
     */
    public function engineers()
    {
        $list = M('engineer')->where(array('status' => array('gt', -1)))->field('id, name')->select();
        array_unshift($list,array('name'=>'全部','id'=>false));
        $this->ajaxReturn($list);
    }

    /**
     * 组织
     *
     * @return void
     */
    public function organization()
    {
        $list = M('organization')->select();
        array_unshift($list,array('alias'=>'全部','id'=>false));
        $this->ajaxReturn($list);
    }

    /**
     * 工程师等级
     * 
     * @return void
     */
    public function level()
    {
        $list = M('engineer_level')->select();
        $this->ajaxReturn($list);
    }

    /**
     * 导出
     *
     * @return void
     */
    public function export() {
        $map = array();
        $rst = array();
        $post = I('post.');

        if (!empty($post['organization_id'])) {
            $map['engineer.organization_id'] = $post['organization_id'];
        }

        if (is_numeric($post['status'])) {
            $map['engineer.status'] = $post['status'];
        }

        if (!empty($post['keyword'])) {
            $like['engineer.id'] = array('eq', trim($post['keyword']));
            $like['engineer.work_number'] = array('like', '%' . trim($post['keyword']) . '%');
            $like['engineer.name'] = array('like', '%' . trim($post['keyword']) . '%');
            $like['engineer.cellphone'] = array('like', '%' . trim($post['keyword']) . '%');
            $like['_logic'] = 'or';
            $map['_complex'] = $like;
        }

        $list = M('engineer')->join('left join engineer_level el on engineer.level = el.id')
            ->join('left join organization org on engineer.organization_id = org.id')
            ->join('left join engineer_info ei on engineer.id = ei.engineer_id')
            ->where($map)
            ->field('engineer.*, sex, avatar, brith, ei.address, id_card, id_card, email, weixin, alipay, el.title, org.alias, quota')
            ->order('id asc')
            ->select();
        $rst['rows'] = $list;

        $params[] = array('id', '姓名', '工号', '手机号', '地区', '等级', '额度', '类型', '是否接单', '状态');

        for ($i = 1; $i <= count($rst['rows']) ; $i++ ) {
            $params[$i]['id'] =$rst['rows'][$i]['id'];
            $params[$i]['name'] = $rst['rows'][$i]['name'];
            $params[$i]['work_number'] = $rst['rows'][$i]['work_number'];
            $params[$i]['cellphone'] = $rst['rows'][$i]['cellphone'];
            $params[$i]['alias'] = $rst['rows'][$i]['alias'];
            $params[$i]['level'] = $rst['rows'][$i]['level'];
            $params[$i]['quota'] = $rst['rows'][$i]['quota'];
            $params[$i]['type'] = $rst['rows'][$i]['type'] == 2 ? '平台工程师' : '本部工程师';
            $params[$i]['is_work'] = $rst['rows'][$i]['is_work'] == 1? '接单' : '不接单';
            $params[$i]['status'] = $rst['rows'][$i]['status'] == 1 ? '启用' : '删除或禁用';    ;
        }

        $this->exportData('工程师管理-'.date('Y-m-h-H-i-s'), $params);
    }
}