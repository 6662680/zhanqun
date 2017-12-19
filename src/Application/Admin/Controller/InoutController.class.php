<?php

// +------------------------------------------------------------------------------------------ 
// | Author: longDD <longdd_love@163.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description:  出入库 Dates: 2016-09-29
// +------------------------------------------------------------------------------------------ 

namespace Admin\Controller;

use Admin\Controller;

class InoutController extends BaseController
{
    /**
     * 出入库列表页面
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
        
        $orgs = session('organizations');
        
        if (!empty($post['starttime']) && empty($post['endtime'])) {
            $map['i.time'] = array('egt', strtotime($post['starttime']));
        }
        
        if (!empty($post['endtime']) && empty($post['starttime'])) {
            $map['i.time '] = array('elt', strtotime($post['endtime']));
        }

        if ($post['starttime'] && $post['endtime']) {
            $map['i.time '] = array(array('gt',strtotime($post['starttime'])),array('lt',strtotime($post['endtime'])+24*60*60-1),'and');
        }

        if (!empty($post['type']) && $post['type'] != 'all') {
            $map['i.type'] = $post['type'];
        }
        
        if (is_numeric($post['inout']) && $post['inout'] != 'all') {
            $map['i.inout'] = $post['inout'];
        }
        
        if (!empty($orgs)) {
            $map['i.organization_id'] = array('in', array_keys($orgs));
        } else {
            $this->ajaxReturn($rst);
        }

        if (!empty($post['organization_id']) && $post['organization_id'] != 'all') {
            $map['i.organization_id '] = $post['organization_id'];
        }
        
        if (!empty($post['engineer_id']) && $post['engineer_id'] != 'all') {
            $map['i.engineer_id '] = $post['engineer_id'];
        }
        
        if (!empty($post['provider_id']) && $post['provider_id'] != 'all') {
            $map['i.provider_id '] = $post['provider_id'];
        }
        
        if (!empty($post['user_id']) && $post['user_id'] != 'all') {
            $map['i.user_id '] = $post['user_id'];
        }
        
        if (!empty($post['phone_id']) && $post['phone_id'] != 'all') {
            $map['pf.phone_id '] = $post['phone_id'];
        }
        
        if (!empty($post['keyword'])) {
            $where = array();
            $where['i.batch'] = array('like', '%' . $post['keyword'] . '%');
            $where['f.number'] = array('like', '%' . $post['keyword'] . '%');
            $where['f.title'] = array('like', '%' . $post['keyword'] . '%');
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }

        $join = 'i left join fitting f on i.fitting_id=f.id
                left join phone_fitting pf on pf.fitting_id = f.id
                left join phone pho on pho.id = pf.phone_id
                left join organization o on i.organization_id=o.id
                left join organization o2 on i.target_orgid=o2.id
                left join user u on i.user_id=u.id
                left join engineer e on i.engineer_id=e.id
                left join provider p on i.provider_id=p.id';
        
        $count = M('inout')->where($map)->join($join)->count();
        $rst['total'] = $count;

        $list = M('inout')->join($join)->where($map)->limit($this->page())
                ->field("i.id, i.type, i.batch, i.inout, i.amount, i.time, i.price, o.alias as organization, o2.alias as organization2,
                        e.name as engineer, p.title as provider, u.username, group_concat(distinct(pho.alias)) as phone_name,
                        concat(f.title, '(', f.number, ')') as fitting")
                ->group('i.id')->order('i.id desc')->select();
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
        array_unshift($orgs,array('alias'=>'全部','id'=>false));
        $this->ajaxReturn(array_values($orgs));
    }
    
    /**
     * 供应商
     */
    public function provider()
    {
        $list = M('provider')->select();
        array_unshift($list,array('title'=>'全部','id'=>false));
        $this->ajaxReturn($list);
    }
    
    /**
     * 操作人
     */
    public function user()
    {
        $list = M('user')->field('id, username')->select();
        array_unshift($list,array('username'=>'全部','id'=>false));
        $this->ajaxReturn($list);
    }
    
    /**
     * 机型
     *
     * @return void
     */
    public function phones()
    {
        $list = M('phone')->where()->field('id, alias')->order('alias asc')->select();
        array_unshift($list,array('alias'=>'全部','id'=>false));
        $this->ajaxReturn($list);
    }
    
    /**
     * 导出
     */
    public function export()
    {
        $post = I('post.');
        
        if (!empty($post['starttime']) && empty($post['endtime'])) {
            $map['i.time'] = array('egt', strtotime($post['starttime']));
        }
        
        if (!empty($post['endtime']) && empty($post['starttime'])) {
            $map['i.time '] = array('elt', strtotime($post['endtime']));
        }
        
        if ($post['starttime'] && $post['endtime']) {
            $map['i.time '] = array(array('gt',strtotime($post['starttime'])),array('lt',strtotime($post['endtime'])+24*60*60-1),'and');
        }
        
        if (!empty($post['type']) && $post['type'] != 'all') {
            $map['i.type'] = $post['type'];
        }
        
        if (is_numeric($post['inout']) && $post['inout'] != 'all') {
            $map['i.inout'] = $post['inout'];
        }
        
        if (!empty($post['organization_id']) && $post['organization_id'] != 'all') {
            $map['i.organization_id '] = $post['organization_id'];
        }
        
        if (!empty($post['engineer_id']) && $post['engineer_id'] != 'all') {
            $map['i.engineer_id '] = $post['engineer_id'];
        }
        
        if (!empty($post['provider_id']) && $post['provider_id'] != 'all') {
            $map['i.provider_id '] = $post['provider_id'];
        }
        
        if (!empty($post['user_id']) && $post['user_id'] != 'all') {
            $map['i.user_id '] = $post['user_id'];
        }
        
        if (!empty($post['phone_id']) && $post['phone_id'] != 'all') {
            $map['pf.phone_id '] = $post['phone_id'];
        }
        
        if (!empty($post['keyword'])) {
            $where = array();
            $where['i.batch'] = array('like', '%' . $post['keyword'] . '%');
            $where['f.number'] = array('like', '%' . $post['keyword'] . '%');
            $where['f.title'] = array('like', '%' . $post['keyword'] . '%');
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }
        
        $join = 'i left join fitting f on i.fitting_id=f.id
                left join phone_fitting pf on pf.fitting_id = f.id
                left join phone pho on pho.id = pf.phone_id
                left join organization o on i.organization_id=o.id
                left join organization o2 on i.target_orgid=o2.id
                left join user u on i.user_id=u.id
                left join engineer e on i.engineer_id=e.id
                left join provider p on i.provider_id=p.id';
        
        $list = M('inout')->join($join)->where($map)
                ->field("i.id, i.type, i.batch, i.inout, i.amount, i.time, i.price, o.alias as organization, o2.alias as organization2,
                        e.name as engineer, p.title as provider, u.username, group_concat(distinct(pho.alias)) as phone_name,
                        concat(f.title, '(', f.number, ')') as fitting")
                ->group('i.id')->order('i.id desc')->select();
        
        /** 导出 */
        $exports = array();
        $title = array(
            'ID',
            '批次',
            '申请仓库',
            '类型',
            '出入库',
            '目标仓库',
            '配件(编码)',
            '机型',
            '经手人',
            '供应商',
            '工程师',
            '类型',
            '出入库',
            '数量',
            '价格',
            '时间'
        );
        $exports[] = $title;
        
        $type = array(
            1 => '出入库',
            2 =>'调拨',
            3 => '工程师申请',
            4 => '报损'
        );
        $inout = array(1 => '入库', 2 => '出库');
        
        foreach ($list as $item) {
            $exports[] = array(
                'ID' => $item['id'],
                '批次' => $item['batch'],
                '申请仓库' => $item['organization'],
                '类型'  => $type[$item['type']],
                '出入库' => $inout[$item['inout']],
                '目标仓库' => $item['organization2'],
                '配件(编码)' => $item['fitting'],
                '机型' => $item['phone_name'],
                '经手人' => $item['username'],
                '供应商' => $item['provider'],
                '工程师' => $item['engineer'],
                '数量' => $item['amount'],
                '价格' => $item['price'],
                '时间' => date('Y-m-d H:i:s', $item['time'])
            );
        }
        
        $this->exportData('物料出入库_' . date('Y_m_d_H_i_s'), $exports);
    }
}