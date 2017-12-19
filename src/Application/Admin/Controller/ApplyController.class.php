<?php

// +------------------------------------------------------------------------------------------ 
// | Author: longDD <longdd_love@163.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 工程师申请 Dates: 2016-10-09
// +------------------------------------------------------------------------------------------ 

namespace Admin\Controller;

use Admin\Controller;
use Think\Exception;

class ApplyController extends BaseController
{
    /**
     * 申请页面
     *
     * @return void
     */
    public function index()
    {
        $this->display();
    }

    /**
     * 申请数据
     *
     * @return void
     */
    public function rows()
    {
        $map = array();
        $rst = array();
        $post = I('post.');

        $orgs = session('organizations');

        if (!empty($orgs)) {
            $map['a.organization_id'] = array('in', array_keys($orgs));
        } else {
            $this->ajaxReturn($rst);
        }

        if (!empty($post['starttime']) && empty($post['endtime'])) {
            $map['a.time'] = array('egt', strtotime($post['starttime']));
        }

        if (!empty($post['endtime']) && empty($post['starttime'])) {
            $map['a.time '] = array('elt', strtotime($post['endtime']));
        }

        if ($post['starttime'] && $post['endtime']) {
            $map['a.time '] = array(array('gt',strtotime($post['starttime'])),array('lt',strtotime($post['endtime'])+24*60*60-1),'and');
        }

//        if (!empty($param['city'])) {
//
//            if ($param['city'] != 9999 && $param['city'] != 'all') {
//                $map['o.city'] = trim($param['city']);
//            } else {
//                $city = array(0);
//
//                foreach (session('addresses') as $address) {
//                    $city[] = $address['city'];
//                }
//
//                if ($city && !in_array(9999, $city)) {
//                    $map['o.city'] = array('in', $city);
//                }
//            }
//        } else {
//            $city = array(0);
//
//            foreach (session('addresses') as $address) {
//                $city[] = $address['city'];
//            }
//
//            if ($city && !in_array(9999, $city)) {
//                $map['o.city'] = array('in', $city);
//            }
//        }

        if (!empty($post['organization_id']) && $post['organization_id'] != 'all') {
            $map['a.organization_id'] = $post['organization_id'];

            $organizations = array(0);

            foreach (session('organizations') as $organizations) {
                $organizations[] = $organizations['id'];
            }

            if ($organizations && !in_array(9999, $organizations)) {
                $map['a.organization_id'] = array('in', $organizations);
            }
        }

        if (!empty($post['type']) && $post['type'] != 'all') {
            $map['a.type'] = $post['type'];
        }

        if (!empty($post['status']) && $post['status'] != 'all' && $post['status'] != '0') {
            $map['a.status'] = $post['status'];
        } elseif ($post['status'] == '0') {
            $map['a.status'] = 0;
        }
        
        if (!empty($post['engineer_id']) && $post['engineer_id'] != 'all') {
            $map['a.engineer_id'] = $post['engineer_id'];
        }
        
        if (!empty($post['keyword'])) {
            $like['e.cellphone'] = array('like', '%' . trim($post['keyword']). '%');
            $like['e.name'] = array('like', '%' . trim($post['keyword']). '%');
            $like['a.fittings'] = array('like', '%' . str_replace("\\", '_', json_encode(trim($post['keyword']))). '%');
            $like['_logic'] = 'or';
            $map['_complex'] = $like;
        }

        $join = 'a left join organization o on a.organization_id=o.id 
            left join user u on a.user_id=u.id 
            left join engineer e on a.engineer_id=e.id';

        $count = M('apply')->join($join)->where($map)->count();
        
        $rst['total'] = $count;

        $list = M('apply')->join($join)->where($map)->limit($this->page())
            ->field('a.id, e.name as engineer, u.username as user, o.name as organization, a.status, a.type, a.time, a.fittings')->order('id desc')->select();
        $rst['rows'] = $list;

        $this->ajaxReturn($rst);
    }

    /**
     * 组织地区
     *
     * @return void
     */
    public function organization()
    {
        $orgs = session('organizations');
        array_unshift($orgs,array('alias'=>'全部','id'=>''));
        $this->ajaxReturn(array_values($orgs));
    }

    /**
     * 机型
     *
     * @return void
     */
    public function phones()
    {
        $list = M('phone')->where()->field('id, alias')->order('alias asc')->select();
        $this->ajaxReturn($list);
    }

    /**
     * 审核
     *
     * @return void
     */
    public function audit()
    {
        /** 状态：-1 取消 (审核不通过)  0 待审核 1 审核通过 2 已发放 3 已退还 */
        $post = I('post.');
        $map = array();
        $rst = array();
        $map['id'] = $post['id'];
        $item = D('apply')->where($map)->find();

        if (!$item) {
            $rst['success'] = false;
            $rst['errorMsg'] = '记录不存在！';
            $this->ajaxReturn($rst);
        }

        if ($item['status'] != 0) {
            $rst['success'] = false;
            $rst['errorMsg'] = '非待审核状态，请刷新页面！';
            $this->ajaxReturn($rst);
        }

        $orgs = session('organizations');

        /** 权限判断 属于目标仓库方可审核 */
        if (!in_array($item['organization_id'], array_keys($orgs))) {
            $rst['success'] = false;
            $rst['errorMsg'] = '没有权限审核此申请！请联系目标仓库管理员审核！';
            $this->ajaxReturn($rst);
        }

        $flag = $post['flag'];
        $data = array();

        /** 审核通过 or 不通过 */
        if ($flag) {
            $data['status'] = 1;
        } else {
            $data['status'] = -1;
        }

        /** 审核人 */
        $data['user_id'] = session('userId');

        if (D('apply')->where($map)->limit(1)->save($data) !== false) {
            $rst['success'] = true;
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '操作失败！';
        }

        $this->ajaxReturn($rst);
    }

    /**
     * 回退
     *
     * @return void
     */
    public function rollback()
    {
        /** 状态：-1 取消 (审核不通过)  0 待审核 1 审核通过 2 已发放 3 已退还 */
        $post = I('post.');
        $map = array();
        $rst = array();
        $map['id'] = $post['id'];
        $item = D('apply')->where($map)->find();

        if (!$item) {
            $rst['success'] = false;
            $rst['errorMsg'] = '记录不存在！';
            $this->ajaxReturn($rst);
        }

        if ($item['status'] != 1 && $item['status'] != -1) {
            $rst['success'] = false;
            $rst['errorMsg'] = '非审核状态，请刷新页面！';
            $this->ajaxReturn($rst);
        }

        $orgs = session('organizations');

        /** 权限判断 属于仓库方可审核 */
        if (!in_array($item['organization_id'], array_keys($orgs))) {
            $rst['success'] = false;
            $rst['errorMsg'] = '没有权限审核此申请！请联系目标仓库管理员审核！';
            $this->ajaxReturn($rst);
        }

        $data = array();
        $data['status'] = 0;

        if (D('apply')->where($map)->limit(1)->save($data) !== false) {
            $rst['success'] = true;
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '操作失败！';
        }

        $this->ajaxReturn($rst);
    }

    /**
     * 发放/接收
     *
     * @return void
     */
    public function grant()
    {
        $id = I('request.id');
        $rst = array();
        $map = array();
        $map['id'] = $id;
        $item = M('apply')->where($map)->find();

        if (!$item) {
            $rst['success'] = false;
            $rst['errorMsg'] = '记录不存在！';
            $this->ajaxReturn($rst);
        }

        if ($item['status'] != 1) {
            $rst['success'] = false;
            $rst['errorMsg'] = '此记录不在待发货状态，请刷新页面！';
            $this->ajaxReturn($rst);
        }

        $orgs = session('organizations');

        /** 权限判断 申请属于目标仓库方可发货 */
        if (!in_array($item['organization_id'], array_keys($orgs))) {
            $rst['success'] = false;
            $rst['errorMsg'] = '没有权限！请联系此仓库管理员操作！';
            $this->ajaxReturn($rst);
        }

        if ($item['type'] == 1) {

            // 工程师额度检查
            $engineerModel = D('Engineer');
            // array('quota' => '工程师总额度', 'warehouse' => '库存物料额度', 'apply' => '申请中的物料额度', 'wasteLock' => '废料未退回锁定的额度')
            $quota = $engineerModel->getEngineerQuota($item['engineer_id']);

            // 由于$quota['apply']是所有申请中的额度，包括本次，所以应该加上本次的钱
            if ($quota['quota'] + $item['worth'] - $quota['warehouse'] - $quota['apply'] - $quota['wasteLock'] < 0) {
                $rst['success'] = false;
                $rst['errorMsg'] = '工程师额度已超出，请核查';
                $this->ajaxReturn($rst);
            }
        }

        try {
            M()->startTrans();
            
            if ($item['type'] == 1) {
                $sendRst = D('warehouse')->give($item);
            } else {
                $sendRst = D('warehouse')->refund($item);
            }
            
            if ($sendRst === false) {
                M()->rollback();
                $rst['success'] = false;
                $rst['errorMsg'] = '更新仓库数据错误！';
                $this->ajaxReturn($rst);
            }
            
            /** 更新采购单状态 */
            $data = array();
            /** 状态：-1 取消 (审核不通过)  0 待审核 1 审核通过 2 已发放 3 已退还 */
            if ($item['type'] == 1) {
                $data['status'] = 2;
            } else {
                $data['status'] = 3;
            }
            
            if (M('apply')->where($map)->save($data) === false) {
                M()->rollback();
                $rst['success'] = false;
                $rst['errorMsg'] = '更新工程师申请错误！';
                $this->ajaxReturn($rst);
            }
            
            M()->commit();
            $rst['success'] = true;
        } catch (\Exception $e) {
            M()->rollback();
            $rst['success'] = false;
            $rst['errorMsg'] = $e->getMessage();
        }

        $this->ajaxReturn($rst);
    }

    /**
     * 更新机型
     *
     * @return void
     */
    public function update()
    {
        $post = I('post.');
        $rst = array();

        $map = array();
        $map['id'] = $post['id'];
        $item = D('apply')->where($map)->find();

        if (!$item) {
            $rst['success'] = false;
            $rst['errorMsg'] = '记录不存在！';
            $this->ajaxReturn($rst);
        }

        $fittingStr = $item['fittings'];
        $fittings = json_decode($fittingStr, true);

        if (!empty($fittings)) {
            foreach ($fittings as $key => $value) {
                $phone = M('phone')->join('p left join phone_fitting pf on p.id=pf.phone_id')->where(array('pf.fitting_id' => $value['fitting_id']))->field('id, alias')->find();
                $fittings[$key]['phone_id'] = $phone['id'];
                $fittings[$key]['phone'] = $phone['alias'];
            }

            $data = array();
            $data['fittings'] = json_encode($fittings);

            if (D('apply')->where($map)->save($data) === false) {
                $rst['success'] = false;
                $rst['errorMsg'] = '更新数据库错误，请联系管理员！';
            } else {
                $rst['success'] = true;
            }
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '记录为空！';
            $this->ajaxReturn($rst);
        }

        $this->ajaxReturn($rst);
    }

    /**
     * 工程师
     */
    public function engineers()
    {
        $list = M('engineer')->where(array('status' => array('gt', -1)))->field('id, name')->select();
        array_unshift($list,array('name'=>'全部','id'=>''));
        $this->ajaxReturn($list);
    }
}