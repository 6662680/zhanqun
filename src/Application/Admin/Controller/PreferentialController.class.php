<?php
// +------------------------------------------------------------------------------------------
// | Author: TCG <tianchunguang@weadoc.com>
// +------------------------------------------------------------------------------------------
// | There is no true,no evil,no light,there is only power.
// +------------------------------------------------------------------------------------------
// | Description: 优惠 Dates: 2017-01-11
// +------------------------------------------------------------------------------------------

namespace Admin\Controller;

use Admin\Controller;

class PreferentialController extends BaseController
{
    /**
     * 优惠
     *
     * @return void
     */
    public function index()
    {
        $this->display();
    }
    
    /**
     * 列表
     */
    public function rows()
    {
        $map = array();
        $rst = array();
        $post = I('post.');
        
        if (!empty($post['type']) && $post['type'] != 'all') {
            $map['type'] = $post['type'];
        }
        
        if (!empty($post['category']) && $post['category'] != 'all') {
            $map['category'] = $post['category'];
        }
        
        if (!empty($post['address_id']) && $post['address_id'] != 'all') {
            $ids = M('preferential_address')->where(array('address_id' => $post['address_id']))->getField('preferential_id, address_id');
            
            if ($ids) {
                $map['id'] = array('in', array_keys($ids));
            } else {
                $map['id'] = 0;
            }
        }
        
        if (!empty($post['phone_id']) && $post['phone_id'] != 'all') {
            $ids = M('preferential_phone')->where(array('phone_id' => $post['phone_id']))->getField('preferential_id, phone_id');
            
            if ($ids) {
                $map['id'] = array('in', array_keys($ids));
            } else {
                $map['id'] = 0;
            }
        }
        
        if (!empty($post['malfunction_id']) && $post['malfunction_id'] != 'all') {
            
            $ids = M('preferential_phomal')->join('pp left join phone_malfunction pm on pm.id = pp.phomal_id')
                    ->where(array('pm.malfunction_id' => $post['malfunction_id']))->getField('pp.preferential_id, pm.malfunction_id');
            
            if ($ids) {
                $map['id'] = array('in', array_keys($ids));
            } else {
                $map['id'] = 0;
            }
        }
        
        if (!empty($post['keyword'])) {
            $where = array();
            $where['id']  = array('eq',  $post['keyword']);
            $where['title']  = array('like', '%' . $post['keyword'] . '%');
            $where['remark']  = array('like', '%' . $post['keyword'] . '%');
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }
        
        $count = M('preferential')->where($map)->count();
        $rst['total'] = $count;
        
        $list = M('preferential')->where($map)->order('id desc')->limit($this->page())->select();
        $rst['rows'] = $list;
        
        $this->ajaxReturn($rst);
    }
    
    /**
     * 新增
     */
    public function add()
    {
        set_time_limit(0);
        $rst = array();
        $data = I('post.');
        
        try {
            
            if (D('preferential')->addPreferential($data) !== false) {
                $rst['success'] = true;
                $rst['errorMsg']= '添加优惠信息成功！';
            } else {
                $rst['success'] = false;
                $rst['errorMsg']= '添加优惠信息失败！';
            }
        } catch (\Exception $e) {
            $rst['success'] = false;
            $rst['errorMsg']= $e->getMessage();
        }
        $this->ajaxReturn($rst);
    }
    
    /**
     * 编辑
     */
    public function edit()
    {
        set_time_limit(0);
        $rst = array();
        $id = I('get.id/d');
        $data = I('post.');
    
        try {
    
            if (D('preferential')->editPreferential($id, $data) !== false) {
                $rst['success'] = true;
                $rst['errorMsg']= '编辑优惠信息成功！';
            } else {
                $rst['success'] = false;
                $rst['errorMsg']= '编辑优惠信息失败！';
            }
        } catch (\Exception $e) {
            $rst['success'] = false;
            $rst['errorMsg']= $e->getMessage();
        }
        $this->ajaxReturn($rst);
    }
    
    /**
     * 删除
     */
    public function delete()
    {
        $rst = array();
        $id = I('post.id/d');
        
        if (D('preferential')->delete($id) !== false) {
            $rst['success'] = true;
        } else {
            $rst['success'] = false;
            $rst['errorMsg']= '删除失败！';
        }
        $this->ajaxReturn($rst);
    }
    
    /**
     * 优惠地区
     */
    public function preferentialAddress()
    {
        $data = array();
        $id = I('get.id/d');
        
        if ($id) {
            $data = M('preferential_address')->join('pa left join address adr on adr.id = pa.address_id')
                    ->where(array('preferential_id' => $id))->field('pa.address_id as city, adr.name')->select();
        }
        
        $this->ajaxReturn($data);
    }
    
    /**
     * 开通地区
     */
    public function address()
    {
        $data = array();
        $id = I('get.id/d');
        
        $map = array('adr.id' => array('gt', 0));
    
        if ($id) {
            $addresses = M('preferential_address')->where(array('preferential_id' => $id))->getField('address_id, preferential_id');
            
            if ($addresses) {
                $map['org.city'] = array('not in', array_keys($addresses));
            }
        }
        
        $data = M('organization')->join('org left join address adr on adr.id = org.city')
                ->where($map)->field('distinct org.city, adr.name')->select();
        
        if (!$id) {
            array_unshift($data,array('name'=>'全部', 'city'=>'all'));
        }
    
        $this->ajaxReturn($data);
    }
    
    /**
     * 添加优惠地区
     */
    public function joinedAddress()
    {
        $data = I('post.');
        $data['address_id'] = array_unique(explode(',', $data['address_id']));
        $rst = array();
        
        if (empty($data['address_id'])) {
            $rst['success'] = false;
            $rst['errorMsg'] = '请设置优惠地区！';
            $this->ajaxReturn($rst);
        }
        
        if (M('preferential')->where(array('id' => intval($data['preferential_id'])))->count() < 1) {
            $rst['success'] = false;
            $rst['errorMsg'] = '优惠信息不存在！';
            $this->ajaxReturn($rst);
        }
        
        $param = array();
        
        foreach ($data['address_id'] as $address_id) {
            $param[] = array('preferential_id' => $data['preferential_id'], 'address_id' => $address_id);
        }
        
        if (M('preferential_address')->addAll($param) !== false) {
            $rst['success'] = true;
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '更新优惠地区失败！';
        }
        $this->ajaxReturn($rst);
    }
    
    /**
     * 删除优惠地区
     */
    public function cancelAddress()
    {
        $data = I('post.');
        $data['address_id'] = array_unique(explode(',', $data['address_id']));
        $rst = array();
    
        if (empty($data['address_id'])) {
            $rst['success'] = false;
            $rst['errorMsg'] = '请选择优惠地区！';
            $this->ajaxReturn($rst);
        }
        
        if (M('preferential')->where(array('id' => intval($data['preferential_id'])))->count() < 1) {
            $rst['success'] = false;
            $rst['errorMsg'] = '优惠信息不存在！';
            $this->ajaxReturn($rst);
        }
    
        $map = array(
            'preferential_id' => $data['preferential_id'],
            'address_id' => array('in', $data['address_id'])
        );
    
        if (M('preferential_address')->where($map)->delete() !== false) {
            $rst['success'] = true;
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '更新优惠地区失败！';
        }
        $this->ajaxReturn($rst);
    }
    
    /**
     * 优惠机型
     */
    public function preferentialPhones()
    {
        $data = array();
        $id = I('get.id/d');
    
        if ($id) {
            $data = M('preferential_phone')->join('pp left join phone p on p.id = pp.phone_id')
                    ->where(array('preferential_id' => $id))->field('pp.phone_id, p.alias as phone_name')->select();
        }
    
        $this->ajaxReturn($data);
    }
    
    /**
     * 机型
     */
    public function phones()
    {
        $map = array();
        $id = I('get.id/d');
        
        if ($id) {
            $phone_ids = M('preferential_phone')->where(array('preferential_id' => $id))->getField('phone_id, preferential_id');
            
            if ($phone_ids) {
                $map['id'] = array('not in', array_keys($phone_ids));
            }
        }
        
        $list = M('phone')->where($map)->field('id as phone_id, alias as phone_name')->order('alias asc')->select();
        
        if (!$id) {
            array_unshift($list, array('phone_name'=>'全部', 'phone_id'=>'all'));
        }
        
        $this->ajaxReturn($list);
    }
    
    /**
     * 添加优惠地区
     */
    public function joinedPhones()
    {
        $data = I('post.');
        $data['phone_id'] = array_unique(explode(',', $data['phone_id']));
        $rst = array();
    
        if (empty($data['phone_id'])) {
            $rst['success'] = false;
            $rst['errorMsg'] = '请设置优惠机型！';
            $this->ajaxReturn($rst);
        }
    
        if (M('preferential')->where(array('id' => intval($data['preferential_id'])))->count() < 1) {
            $rst['success'] = false;
            $rst['errorMsg'] = '优惠信息不存在！';
            $this->ajaxReturn($rst);
        }
    
        $param = array();
    
        foreach ($data['phone_id'] as $phone_id) {
            $param[] = array('preferential_id' => $data['preferential_id'], 'phone_id' => $phone_id);
        }
    
        if (M('preferential_phone')->addAll($param) !== false) {
            $rst['success'] = true;
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '更新优惠机型失败！';
        }
        $this->ajaxReturn($rst);
    }
    
    /**
     * 删除优惠地区
     */
    public function cancelPhones()
    {
        $data = I('post.');
        $data['phone_id'] = array_unique(explode(',', $data['phone_id']));
        $rst = array();
    
        if (empty($data['phone_id'])) {
            $rst['success'] = false;
            $rst['errorMsg'] = '请选择优惠机型！';
            $this->ajaxReturn($rst);
        }
    
        if (M('preferential')->where(array('id' => intval($data['preferential_id'])))->count() < 1) {
            $rst['success'] = false;
            $rst['errorMsg'] = '优惠信息不存在！';
            $this->ajaxReturn($rst);
        }
    
        $map = array(
            'preferential_id' => $data['preferential_id'],
            'phone_id' => array('in', $data['phone_id'])
        );
    
        if (M('preferential_phone')->where($map)->delete() !== false) {
            $rst['success'] = true;
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '更新优惠机型失败！';
        }
        $this->ajaxReturn($rst);
    }
    
    /**
     * 故障列表
     */
    public function malfunctions()
    {
        $list = M('malfunction')->field('id, name')->select();
        array_unshift($list, array('name'=>'全部', 'id'=>'all'));
        $this->ajaxReturn($list);
    }
    
    /**
     * 机型故障列表
     */
    public function phomals() 
    {
        $map = array('p.id' => array('gt', 0));
        $id = I('get.id/d');
        $phone_id = I('get.phone_id/d');
        
        if ($id) {
            $phomal_ids = M('preferential_phomal')->where(array('preferential_id' => $id))->getField('phomal_id, preferential_id');
        
            if ($phomal_ids) {
                $map['pm.id'] = array('not in', array_keys($phomal_ids));
            }
        }
        
        if ($phone_id > 0) {
            $map['pm.phone_id'] = $phone_id;
        }
        
        $list = M('phone_malfunction')->join('pm left join phone p on p.id = pm.phone_id')
                ->where($map)->field('pm.id as phomal_id, concat(p.alias, " ",pm.malfunction) as phomal_name')->select();
        $this->ajaxReturn($list);
    }
    
    /**
     * 优惠故障
     */
    public function preferentialPhomals()
    {
        $data = array();
        $id = I('get.id/d');
        $phone_id = I('get.phone_id/d');
        
        if ($id) {
            
            $map = array('preferential_id' => $id);
            
            if ($phone_id > 0) {
                $map['pm.phone_id'] = $phone_id;
            }
            
            $data = M('preferential_phomal')->join('pp left join phone_malfunction pm on pp.phomal_id = pm.id')
                    ->join('left join phone p on p.id = pm.phone_id')
                    ->where($map)
                    ->field('pp.phomal_id, concat(p.alias, " ",pm.malfunction) as phomal_name')->select();
        }
        
        $this->ajaxReturn($data);
    }
    
    /**
     * 添加优惠故障
     */
    public function joinedPhomals()
    {
        $data = I('post.');
        $data['phomal_id'] = array_unique(explode(',', $data['phomal_id']));
        
        $rst = array();
    
        if (empty($data['phomal_id'])) {
            $rst['success'] = false;
            $rst['errorMsg'] = '请设置优惠故障！';
            $this->ajaxReturn($rst);
        }
    
        if (M('preferential')->where(array('id' => intval($data['preferential_id'])))->count() < 1) {
            $rst['success'] = false;
            $rst['errorMsg'] = '优惠信息不存在！';
            $this->ajaxReturn($rst);
        }
    
        $param = array();
    
        foreach ($data['phomal_id'] as $phomal_id) {
            $param[] = array('preferential_id' => $data['preferential_id'], 'phomal_id' => $phomal_id);
        }
    
        if (M('preferential_phomal')->addAll($param) !== false) {
            $rst['success'] = true;
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '更新优惠故障失败！';
        }
        $this->ajaxReturn($rst);
    }
    
    /**
     * 删除优惠故障
     */
    public function cancelPhomals()
    {
        $data = I('post.');
        $data['phomal_id'] = array_unique(explode(',', $data['phomal_id']));
        $rst = array();
    
        if (empty($data['phomal_id'])) {
            $rst['success'] = false;
            $rst['errorMsg'] = '请选择优惠故障！';
            $this->ajaxReturn($rst);
        }
    
        if (M('preferential')->where(array('id' => intval($data['preferential_id'])))->count() < 1) {
            $rst['success'] = false;
            $rst['errorMsg'] = '优惠信息不存在！';
            $this->ajaxReturn($rst);
        }
    
        $map = array(
            'preferential_id' => $data['preferential_id'],
            'phomal_id' => array('in', $data['phomal_id'])
        );
    
        if (M('preferential_phomal')->where($map)->delete() !== false) {
            $rst['success'] = true;
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '更新优惠故障失败！';
        }
        $this->ajaxReturn($rst);
    }
    
    /**
     * 优惠券列表
     */
    public function coupon()
    {
        $map = array();
        $data = array_merge(I('post.'), I('get.'));
        $rst = array();
        
        if (!$data['preferential_id']) {
            $this->ajaxReturn($rst);
        }
        
        $map['preferential_id'] = $data['preferential_id'];
        
        if (is_numeric($data['status'])) {
            $map['coupon_status'] = $data['status'];
        }
        
        if (!empty($data['keyword'])) {
            $where = array();
            $where['coupon_number']  = array('like', '%' . $data['keyword'] . '%');
            $where['coupon_orderid']  = array('like', '%' . $data['keyword'] . '%');
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }
        
        $count = M('preferential_coupon')->where($map)->count();
        $rst['total'] = $count;
        
        $list = M('preferential_coupon')->where($map)->limit($this->page())->select();
        $rst['rows'] = $list;
        
        $this->ajaxReturn($rst);
    }
    
    /**
     * 优惠券导出
     */
    public function export()
    {
        set_time_limit(0);
        $preferential_id = I('get.preferential_id/d');
        
        if (!$preferential_id) {
            return;
        }
        
        $filename = './cache/Temp/preferential_' . $preferential_id . '.txt';
        
        if (file_exists($filename)) {
            header("Content-Type: application/force-download");
            header("Content-Disposition: attachment; filename=".basename($filename));
            readfile($filename);
            exit;
        }
        
        $preferential = M('preferential')->where(array('id' => $preferential_id))->find();
        
        if (!$preferential || $preferential['status'] != 1) {
            return;
        }
        
        $param = array();
        
        $map = array('preferential_id' => $preferential_id, 'conpon_status' => 1);
        $list = array_keys(M('preferential_coupon')->where($map)->getField('coupon_number, coupon_id'));
        $data = implode("\r\n", $list);
        file_put_contents($filename, $data);
        
        header("Content-Type: application/force-download");
        header("Content-Disposition: attachment; filename=".basename($filename));
        readfile($filename);
        exit;
    }
}