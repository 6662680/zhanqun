<?php

// +------------------------------------------------------------------------------------------ 
// | Author: longDD <longdd_love@163.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 仓库 Dates: 2016-10-09
// +------------------------------------------------------------------------------------------ 

namespace Admin\Controller;

use Admin\Controller;

class WarehouseController extends BaseController
{
    /**
     * 库存页面
     *
     * @return void
     */
    public function index()
    {
        $this->display();
    }

    /**
     * 库存数据
     *
     * @return void
     */
    public function rows()
    {
        $map = array();
        $rst = array();
        $post = I('post.');

        /*
        $orgs = session('organizations');

        if (!empty($orgs)) {
            $map['w.organization_id'] = array('in', array_keys($orgs));
        } else {
            $this->ajaxReturn($rst);
        }
        */
        
        $map['o.id'] = array('gt', 0);
        $map['p.id'] = array('gt', 0);
        $map['f.id'] = array('gt', 0);

        if (!empty($post['phone_id']) && $post['phone_id'] != 'all') {
            $map['pf.phone_id'] = $post['phone_id'];
        }

        if (!empty($post['organization_id']) && $post['organization_id'] != 'all') {
            $map['w.organization_id '] = $post['organization_id'];
        }
        
        if (!empty($post['fitting_id']) && $post['fitting_id'] != 'all') {
            $map['w.fitting_id '] = $post['fitting_id'];
        }
        
        if (!empty($post['category_id']) && $post['category_id'] != 'all') {
            $map['f.category_id '] = $post['category_id'];
        }

        if (!empty($post['keyword'])) {
            $where = array();
            $where['f.number'] = array('like', '%' . $post['keyword'] . '%');
            $where['f.title'] = array('like', '%' . $post['keyword'] . '%');
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }

        $join = 'w left join fitting f on w.fitting_id=f.id 
            left join phone_fitting pf on f.id=pf.fitting_id 
            left join phone p on pf.phone_id=p.id
            left join organization o on w.organization_id=o.id
            left join warehouse_safe_stock wss on wss.organization_id = w.organization_id and wss.fitting_id = w.fitting_id
            left join stock s on s.organization_id = w.organization_id and s.status = 1 and s.fitting_id = w.fitting_id';

        $count = M('warehouse')->where($map)->join($join)->count('distinct(w.id)');
        $rst['total'] = $count;

        $list = M('warehouse')->join($join)->where($map)->limit($this->page())
            ->field("w.id, w.organization_id, o.alias as organization, w.fitting_id, 
                    concat(f.title, '(', f.number, ')') as fitting, group_concat(p.alias) as phone, w.amount,
                    sum(s.price) as fitting_price, wss.amount as wss_amount")
            ->group('w.id')
            ->order('o.id asc, phone asc, f.number asc')->select();

        $orgs = array();
        $fitting_ids = array();
        
        foreach ($list as $k => $item) {
            unset($list[$k]);
            
            $item['phone'] = array_unique(explode(',', $item['phone']));
            $phone_count = count($item['phone']);
            $item['phone'] = implode(',', $item['phone']);
            
            if ($phone_count > 1) {
                $item['fitting_price'] = $item['fitting_price'] / $phone_count;
            }
            
            $item['total_price'] = $item['fitting_price'];
            $list[$item['organization_id'].'_'.$item['fitting_id']] = $item;
            $orgs[$item['organization_id']] = $item['organization_id'];
            $fitting_ids[$item['fitting_id']] = $item['fitting_id'];
        }
        
        $fitting_price = array();
        
        if ($orgs && $fitting_ids) {
            $where = array(
                'e.organization_id' => array('in', $orgs),
                'ew.fittings_id' => array('in', $fitting_ids),
                'ew.amount' => array('gt', 0),
                'e.status' => 1,
                's.status' => 3,
            );
            
            $engineer_fitting = M('engineer_warehouse')->join('ew left join engineer e on ew.engineer_id = e.id')
                            ->join('left join stock s on s.engineer_id = ew.engineer_id and s.fitting_id = ew.fittings_id')
                            ->field('e.organization_id, ew.fittings_id, ew.amount, sum(s.price) as engineer_price')
                            ->group('ew.id')
                            ->where($where)
                            ->select();
            
            if ($engineer_fitting) {
                
                foreach ($engineer_fitting as $v) {
                    $k = $v['organization_id'].'_'.$v['fittings_id'];
                    
                    if (isset($list[$k])) {
                        $list[$k]['engineer_amount'] += $v['amount'];
                        $list[$k]['engineer_price'] += $v['engineer_price'];
                        $list[$k]['total_price'] += $v['engineer_price'];
                    }
                }
            }
        }
        
        $rst['rows'] = array_values($list);

        $this->ajaxReturn($rst);
    }

    /**
     * 组织地区
     *
     * @return void
     */
    public function organization()
    {
        $list = M('organization')->select();
        array_unshift($list,array('alias'=>'全部','id'=>''));
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
     * 库存详情
     *
     * @return void
     */
    public function detail()
    {
        $map = array();
        $rst = array();
        $post = I('post.');

        $orgs = session('organizations');

        if (!empty($orgs)) {
            $map['s.organization_id'] = array('in', array_keys($orgs));
        } else {
            $this->ajaxReturn($rst);
        }

        if (!empty($post['organization_id'])) {
            $map['s.organization_id'] = $post['organization_id'];
        } else {
            $this->ajaxReturn($rst);
        }

        if (!empty($post['fitting_id'])) {
            $map['s.fitting_id'] = $post['fitting_id'];
        } else {
            $this->ajaxReturn($rst);
        }

        $map['s.status'] = 1;

        $join = 's left join fitting f on s.fitting_id=f.id 
            left join organization as o on s.organization_id=o.id 
            left join provider as p on s.provider_id=p.id';

        $count = M('stock')->where($map)->join($join)->count();
        $rst['total'] = $count;

        $list = M('stock')->join($join)->where($map)->limit($this->page())
            ->field("s.id, s.organization_id, o.alias as organization, s.fitting_id, concat(f.title, '(', f.number, ')') as fitting, s.number, s.batch, s.price, p.title as provider, s.create_time")
            ->order('s.id desc')->select();
        $rst['rows'] = $list;

        $this->ajaxReturn($rst);
    }

    /**
     * 出入库记录
     *
     * @return void
     */
    public function inout()
    {
        $map = array();
        $rst = array();
        $post = I('post.');

        $orgs = session('organizations');

        if (!empty($orgs)) {
            $map['i.organization_id'] = array('in', array_keys($orgs));
        } else {
            $this->ajaxReturn($rst);
        }

        if (!empty($post['organization_id'])) {
            $map['i.organization_id'] = $post['organization_id'];
        } else {
            $this->ajaxReturn($rst);
        }

        if (!empty($post['fitting_id'])) {
            $map['i.fitting_id'] = $post['fitting_id'];
        } else {
            $this->ajaxReturn($rst);
        }

        $join = 'i left join fitting f on i.fitting_id=f.id 
            left join organization as o on i.organization_id=o.id 
            left join provider as p on i.provider_id=p.id 
            left join user u on i.user_id=u.id 
            left join engineer e on i.engineer_id=e.id';

        $count = M('inout')->where($map)->join($join)->count();
        $rst['total'] = $count;

        $list = M('inout')->join($join)->where($map)->limit($this->page())
            ->field("i.id, i.type, i.inout, o.alias as organization, concat(f.title, '(', f.number, ')') as fitting, i.batch, i.amount, i.price,
                p.title as provider, u.username as audit, e.name as engineer, i.time")
            ->order('i.id desc')->select();
        $rst['rows'] = $list;

        $this->ajaxReturn($rst);
    }

    /**
     * 工程师库存
     *
     * @return void
     */
    public function engineer()
    {
        $map = array();
        $rst = array();
        $post = I('post.');

        $orgs = session('organizations');

        if (!empty($orgs)) {
            $map['e.organization_id'] = array('in', array_keys($orgs));
        } else {
            $this->ajaxReturn($rst);
        }

        if (!empty($post['organization_id'])) {
            $map['e.organization_id'] = $post['organization_id'];
        } else {
            $this->ajaxReturn($rst);
        }

        if (!empty($post['fitting_id'])) {
            $map['ew.fittings_id'] = $post['fitting_id'];
        } else {
            $this->ajaxReturn($rst);
        }

        $map['e.status'] = 1;

        $join = 'ew left join engineer e on ew.engineer_id=e.id 
            left join organization o on e.organization_id=o.id';

        $count = M('engineer_warehouse')->where($map)->join($join)->count();
        $rst['total'] = $count;

        $list = M('engineer_warehouse')->join($join)->where($map)->limit($this->page())
            ->field("ew.id, o.alias as organization, ew.fittings_name as fitting, e.name as engineer, ew.amount")
            ->order('ew.id desc')->select();
        $rst['rows'] = $list;

        $this->ajaxReturn($rst);
    }

    /**
     * 工程师列表 (属于本仓库)
     *
     * @return void
     */
    public function engineers()
    {
        $this->display();
    }

    /**
     * 工程师数据
     *
     * @return void
     */
    public function engineerRows()
    {
        $map = array();
        $rst = array();
        $post = I('post.');


        $orgs = session('organizations');

        if (!empty($orgs)) {
            $map['organization_id'] = array('in', array_keys($orgs));
        }
        
        if (!empty($post['organization_id'])) {
            $map['engineer.organization_id'] = $post['organization_id'];
        }
        
        $map['engineer.status'] = 1;
        
        if (!empty($post['keyword'])) {
            $like['engineer.id'] = array('eq', trim($post['keyword']));
            $like['engineer.work_number'] = array('like', trim($post['keyword']));
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
                ->field('engineer.*, sex, avatar, brith, ei.address, id_card, id_card, email, weixin, alipay, el.title, org.alias, el.quota')->select();
        
        foreach ($list as &$item) {
            
            $worth = M('engineer_warehouse')->join('ew left join fitting f on f.id = ew.fittings_id')
                        ->where(array('engineer_id' => $item['id'], 'amount' => array('gt', 0)))
                        ->sum('ew.amount * f.price');
            
            $apply_worth = M('apply')->where(array('engineer_id' => $item['id'], 'status' => 1, 'type' => 1))->sum('worth');

            /** 未退回废料订单物料价值 */
            $wasteLockWorth = M('waste_refund')->join('wr left join `engineer_inout` ei on wr.order_id=ei.order_id left join `fitting` f on ei.fittings_id=f.id')
                ->where(array('wr.engineer_id' => $item['id'], 'wr.status' => 0))->sum("ei.amount * f.price");
            $wasteLockWorth = round($wasteLockWorth);
            
            $item['quota'] -= ($worth + $apply_worth + $wasteLockWorth);
            $item['quota'] = round($item['quota'], 2);
        }
        
        $rst['rows'] = $list;
    
        $this->ajaxReturn($rst);
    }

    /**
     * 工程师物料详情
     *
     * @return void
     */
    public function engineerDetail()
    {
        $map = array();
        $rst = array();
        $post = I('post.');

        if (!empty($post['engineer_id'])) {
            $map['engineer_id'] = $post['engineer_id'];
        } else {
            $this->ajaxReturn($rst);
        }
        
        if (!empty($post['phone_id'])) {
            $map['phone_id'] = $post['phone_id'];
        }
        
        if (!empty($post['keyword'])) {
            $where = array();
            $where['f.number'] = array('like', '%' . $post['keyword'] . '%');
            $where['f.title'] = array('like', '%' . $post['keyword'] . '%');
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }
        
        $join = 'ew left join`phone_fitting` as pf on pf.fitting_id = ew.fittings_id
                 left join `phone` on phone.id = pf.phone_id 
                 left join `fitting` f on ew.fittings_id=f.id';

        $count = M('engineer_warehouse')->join($join)->where($map)->field('count(1) as count')->group('ew.fittings_id')->select();
        $rst['total'] = count($count);

        $list = M('engineer_warehouse')
                ->join($join)
                ->where($map)
                ->limit($this->page())
                ->field("ew.id, concat(f.title, '(', f.number, ')') as fitting, ew.amount, group_concat(distinct(phone.alias)) as alias, f.price")
                ->group('ew.fittings_id')
                ->order('id desc')
                ->select();
        $rst['rows'] = $list;

        $this->ajaxReturn($rst);
    }

    /**
     * 工程师出入库
     *
     * @return void
     */
    public function engineerInout()
    {
        $map = array();
        $rst = array();
        $post = I('post.');

        if (!empty($post['engineer_id'])) {
            $map['engineer_id'] = $post['engineer_id'];
        } else {
            $this->ajaxReturn($rst);
        }

        $join = 'ei left join fitting f on ei.fittings_id=f.id 
                left join user u on ei.user_id=u.id
                left join`phone_fitting` as pf on pf.fitting_id = ei.fittings_id
                left join phone on phone.id = pf.phone_id';

        $count = M('engineer_inout')->where($map)->join($join)->field('count(1) as count')->group('ei.id')->select();
        $rst['total'] = count($count);

        $list = M('engineer_inout')->join($join)->where($map)->limit($this->page())
            ->field("ei.id, ei.type, ei.inout, concat(f.title, '(', f.number, ')') as fitting, ei.amount, ei.order_id, ei.time, u.username as auditor, group_concat(distinct(phone.alias)) as alias")
            ->group('ei.id')
            ->order('ei.id desc')->select();
        $rst['rows'] = $list;

        $this->ajaxReturn($rst);
    }
    
    /**
     * 导出
     *
     * @return void
     */
    public function export()
    {
        $post = I('post.');

        $orgs = session('organizations');
        
        $map['o.id'] = array('gt', 0);
        $map['f.id'] = array('gt', 0);

        /*
        if (!empty($orgs)) {
            $map['w.organization_id'] = array('in', array_keys($orgs));
        }
        */

        if (!empty($post['phone_id']) && $post['phone_id'] != 'all') {
            $map['pf.phone_id'] = $post['phone_id'];
        }

        if (!empty($post['organization_id']) && $post['organization_id'] != 'all') {
            $map['w.organization_id '] = $post['organization_id'];
        }

        if (!empty($post['keyword'])) {                                                                                                                                                                                                                                         
            $where = array();
            $where['f.number'] = array('like', '%' . $post['keyword'] . '%');
            $where['f.title'] = array('like', '%' . $post['keyword'] . '%');
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }

        $join = 'w left join fitting f on w.fitting_id=f.id 
                left join phone_fitting pf on f.id=pf.fitting_id 
                left join phone p on pf.phone_id=p.id
                left join organization as o on w.organization_id=o.id
                left join warehouse_safe_stock wss on wss.organization_id = w.organization_id and wss.fitting_id = w.fitting_id
                left join stock s on s.organization_id = w.organization_id and s.status = 1 and s.fitting_id = w.fitting_id';

        $count = M('warehouse')->where($map)->join($join)->count();
        $rst['total'] = $count;

        $list = M('warehouse')->join($join)->where($map)->limit()
            ->field("w.organization_id, o.alias as organization, w.fitting_id,
                    concat(f.title, '(', f.number, ')') as fitting, 
                    group_concat(distinct(p.alias)) as phone, 
                    w.amount, sum(s.price) as fitting_price, wss.amount as wss_amount")
            ->group('w.id')->order('o.id asc, phone asc, f.number asc')->select();
        
        if ($list) {
            $orgs = array();
            $fitting_ids = array();
            
            foreach ($list as $k => $item) {
                unset($list[$k]);
                $list[$item['organization_id'].'_'.$item['fitting_id']] = $item;
                $orgs[$item['organization_id']] = $item['organization_id'];
                $fitting_ids[$item['fitting_id']] = $item['fitting_id'];
            }
            
            if ($orgs && $fitting_ids) {
                $where = array(
                    'e.organization_id' => array('in', $orgs),
                    'ew.fittings_id' => array('in', $fitting_ids),
                    'ew.amount' => array('gt', 0),
                    'e.status' => 1,
                    's.status' => 3,
                );
                
                $engineer_fitting = M('engineer_warehouse')->join('ew left join engineer e on ew.engineer_id = e.id')
                            ->join('left join stock s on s.engineer_id = ew.engineer_id and s.fitting_id = ew.fittings_id')
                            ->field('e.organization_id, ew.fittings_id, ew.amount, sum(s.price) as engineer_price')
                            ->group('ew.id')
                            ->where($where)
                            ->select();
            
                if ($engineer_fitting) {
            
                    foreach ($engineer_fitting as $v) {
                        $k = $v['organization_id'].'_'.$v['fittings_id'];
                        
                        if (isset($list[$k])) {
                            $list[$k]['engineer_amount'] += $v['amount'];
                            $list[$k]['engineer_price'] += $v['engineer_price'];
                        }
                    }
                }
            }
            
            $list = array_values($list);
        }
        
        $params = array();
        $path = '/admin/warehouse/index';
        $button = session('button');
        $priv = $button[$path];
        
        if (isset($priv['显示价格'])) {
            $params[] = array('组织(地区)', '配件', '机型', '仓库数量', '仓库总价', '工程师物料数量', '工程师物料总价', '总价', '安全库存');
            
            foreach ($list as $item) {
                $params[] = array($item['organization'], $item['fitting'], $item['phone'], $item['amount'],
                                $item['fitting_price'], $item['engineer_amount'], $item['engineer_price'],
                                $item['fitting_price'] + $item['engineer_price'], $item['wss_amount']);
            }
        } else {
            $params[] = array('组织(地区)', '配件', '机型', '仓库数量', '工程师物料数量');
            
            foreach ($list as $item) {
                $params[] = array($item['organization'], $item['fitting'], $item['phone'], $item['amount'], $item['engineer_amount']);
            }
        }

        
       $this->exportData('配件库存列表', $params);
    }

    /**
     * 出入库（增加或减少库存）
     */
    public function inoutWarehouse()
    {
        $rst = array();
        $data = I('post.');
        
        if (empty($data['batch'])) {
            $rst['success'] = false;
            $rst['errorMsg'] = '请输入出库批次号';
            $this->ajaxReturn($rst);
        }
        
        if (empty($data['organization_id'])) {
            $rst['success'] = false;
            $rst['errorMsg'] = '请选择仓库';
            $this->ajaxReturn($rst);
        }
        
        if (empty($data['phone_id'])) {
            $rst['success'] = false;
            $rst['errorMsg'] = '请选择机型';
            $this->ajaxReturn($rst);
        }
        
        if (empty($data['fitting_id'])) {
            $rst['success'] = false;
            $rst['errorMsg'] = '请选择配件';
            $this->ajaxReturn($rst);
        }
        
        if (empty($data['amount']) || $data['amount'] < 1) {
            $rst['success'] = false;
            $rst['errorMsg'] = '请输入出库数量';
            $this->ajaxReturn($rst);
        }
        
        $map = array();
        $map['fitting_id'] = $data['fitting_id'];
        $map['organization_id'] = $data['organization_id'];
        $current = M('warehouse')->where($map)->find();
        
        if (!$current) {
            $rst['success'] = false;
            $rst['errorMsg'] = '仓库中不存在该配件';
            $this->ajaxReturn($rst);
        }
        
        if ($current['amount'] < $data['amount']) {
            $rst['success'] = false;
            $rst['errorMsg'] = '配件库存数量不足';
            $this->ajaxReturn($rst);
        }
        
        /** 检测批次 */
        $map2 = array();
        $map2['fitting_id'] = $data['fitting_id'];
        $map2['organization_id'] = $data['organization_id'];
        $map2['batch'] = trim($data['batch']);
        $map2['status'] = 1;
        $stocks = M('stock')->where($map2)->order('id asc')->limit($data['amount'])->getField('id, provider_id, price');
        
        if (count($stocks) < $data['amount']) {
            $rst['success'] = false;
            $rst['errorMsg'] = '此批次配件库存数量不足';
            $this->ajaxReturn($rst);
        }
        
        $flag = true;
        M()->startTrans();
        
        if (M('warehouse')->where($map)->setDec('amount', $data['amount']) === false) {
            $flag = false;
        }
        
        $where = array('id' => array('in', array_keys($stocks)));
        $param = array('status' => 5, 'organization_id' => 0);
        
        if (M('stock')->where($where)->save($param) === false) {
            $flag = false;
        }
        
        $param = array(
            'type' => 1,
            'batch' => trim($data['batch']),
            'organization_id' => $data['organization_id'],
            'fitting_id' => $data['fitting_id'],
            'user_id' => session('userId'),
            'provider_id' => current($stocks)['provider_id'],
            'engineer_id' => 0,
            'inout' => 2,
            'amount' => $data['amount'],
            'price' => current($stocks)['price'],
            'fittings' => json_encode($stocks),
            'time' => time()
        );
        
        if (M('inout')->add($param) === false) {
            $flag = false;
        }
        
        if ($flag) {
            M()->commit();
            $rst['success'] = true;
        } else {
            M()->rollback();
            $rst['success'] = false;
            $rst['errorMsg']= '操作失败';
        }
        $this->ajaxReturn($rst);
    }
    
    /**
     * 配件
     *
     * @return void
     */
    public function fittings()
    {
        $phoneId = I('get.phone_id/d', 0);
        
        if (!$phoneId) {
            $this->ajaxReturn(array());
        }
        
        $sql = "select f.id, concat(f.title, '(', f.number, ')') as name from phone_fitting pf 
                left join fitting f on pf.fitting_id=f.id 
                where pf.phone_id={$phoneId} AND f.id > 0";
        $fittings = M()->query($sql);
        $this->ajaxReturn($fittings);
    }
    
    /**
     * 更新工程师库存
     */
    public function resetEngineerWarehouse()
    {
        $id = I('get.id/d');
        $data = I('post.');
        $map = array('ew.id' => $id);
        $rst = array();
        
        $warehouse = M('engineer_warehouse')->join('ew left join fitting f on f.id = ew.fittings_id')
                    ->field('ew.*, f.price')->where($map)->find();
        
        if (!$warehouse) {
            $rst['success'] = false;
            $rst['errorMsg'] = '库存不存在！';
            $this->ajaxReturn($rst);
        }
        
        if ($data['type'] == 1) { //库存转实体
            
            $stock = array();
            
            for ($i = 1; $i <= $warehouse['amount']; $i++) {
                $stock[] = array(
                    'number' => D('warehouse')->createNumber(),
                    'status' => 3,
                    'engineer_id' => $warehouse['engineer_id'],
                    'fitting_id' => $warehouse['fittings_id'],
                    'price' => $warehouse['price'],
                    'create_time' => time()
                );
            }
            
            $map = array(
                'engineer_id' => $warehouse['engineer_id'],
                'fitting_id' => $warehouse['fittings_id'],
                'status' => 3,
            );
            
            $flag = true;
            M()->startTrans();
            
            if (M('stock')->where($map)->delete() === false) {
                $flag = false;
            }
            
            if (M('stock')->addAll($stock) === false) {
                $flag = false;
            }
            
            if ($flag) {
                M()->commit();
                $rst['success'] = true;
            } else {
                $rst['success'] = false;
                $rst['errorMsg'] = '库存更新失败！';
            }
        } else if ($data['type'] == 2) { //实体转库存 
            
            $map = array(
                'status' => 3,
                'engineer_id' => $warehouse['engineer_id'],
                'fitting_id' => $warehouse['fittings_id'],
            );
            
            $count = M('stock')->where($map)->count();
            
            if (M('engineer_warehouse')->where(array('id' => $id))->setField('amount', $count) === false) {
                $rst['success'] = false;
                $rst['errorMsg'] = '库存更新失败！';
            } else {
                $rst['success'] = true;
            }
        } else if ($data['type'] == 3) {//增减库存
            
            if (empty($data['number'])) {
                $rst['success'] = false;
                $rst['errorMsg'] = '请输入增减库存数量！';
                $this->ajaxReturn($rst);
            }
            
            if ($warehouse['amount'] + $data['number'] < 0) {
                $rst['success'] = false;
                $rst['errorMsg'] = '库存更新后不能为负值，请设置合理的参数！';
                $this->ajaxReturn($rst);
            }
            
            $flag = true;
            M()->startTrans();

            if ($data['number'] > 0) {//增加库存
                
                $stock = array();
                
                for ($i = 1; $i <= $data['number']; $i++) {
                    $stock[] = array(
                        'number' => D('warehouse')->createNumber(),
                        'status' => 3,
                        'engineer_id' => $warehouse['engineer_id'],
                        'fitting_id' => $warehouse['fittings_id'],
                        'price' => $warehouse['price'],
                        'create_time' => time()
                    );
                }
                
                if (M('engineer_warehouse')->where(array('id' => $id))->setInc('amount', $data['number']) === false) {
                    $flag = false;
                }
                
                if (M('stock')->addAll($stock) === false) {
                    $flag = false;
                }
            } else {//减少库存
                $data['number'] = abs($data['number']);
                
                if (M('engineer_warehouse')->where(array('id' => $id))->setDec('amount', $data['number']) === false) {
                    $flag = false;
                }
                
                $map = array(
                    'status' => 3,
                    'engineer_id' => $warehouse['engineer_id'],
                    'fitting_id' => $warehouse['fittings_id'],
                );
                
                if (M('stock')->where($map)->limit($data['number'])->save(array('engineer_id' => 0, 'status' => 5)) === false) {
                    $flag = false;
                }
            }
            
            if ($flag) {
                M()->commit();
                $rst['success'] = true;
            } else {
                $rst['success'] = false;
                $rst['errorMsg'] = '库存更新失败！';
            }
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '更新类型错误！';
        }
        $this->ajaxReturn($rst);
    }
    
    /**
     * 工程师库存详情数据导出
     *
     * @return void
     */
    public function exportEngineerWarehouse()
    {
        $map = array();
        $rst = array();
        $post = I('post.');

        if (!empty($post['organization_id']) && $post['organization_id'] != 'all') {
            $map['engineer.organization_id'] = $post['organization_id'];
        }
        
        $map['engineer.status'] = 1;
        $map['ew.amount'] = array('gt', 0);
        $map['f.id'] = array('gt', 0);
        
        if (!empty($post['keyword'])) {
            $like['engineer.id'] = array('eq', trim($post['keyword']));
            $like['engineer.work_number'] = array('like', trim($post['keyword']));
            $like['engineer.name'] = array('like', '%' . trim($post['keyword']) . '%');
            $like['engineer.cellphone'] = array('like', '%' . trim($post['keyword']) . '%');
            $like['_logic'] = 'or';
            $map['_complex'] = $like;
        }
        
        $join = 'ew left join`phone_fitting` as pf on pf.fitting_id = ew.fittings_id
                 left join `engineer` on engineer.id = ew.engineer_id
                 left join `phone` on phone.id = pf.phone_id 
                 left join `fitting` f on ew.fittings_id=f.id';

        $list = M('engineer_warehouse')
                ->join($join)
                ->where($map)
                ->field("engineer.name, concat(f.title, '(', f.number, ')') as fitting, ew.amount, group_concat(distinct(phone.alias)) as alias, f.price")
                ->group('engineer.id, ew.fittings_id')
                ->order('engineer.id asc, ew.fittings_id desc')
                ->select();
        
        $params = array();
        $params[] = array('工程师', '配件', '价格', '数量', '机型');
        
        foreach ($list as $val) {
            $params[] = array($val['name'], $val['fitting'], $val['price'], $val['amount'], $val['alias']);
        }
        
        $this->exportData('工程师物料库存详情'.date('Y-m-d-H-i-s'), $params);
    }
    
    /**
     * 工程师出入库数据导出
     *
     * @return void
     */
    public function exportEngineerInout()
    {
        $map = array();
        $rst = array();
        $post = I('post.');
    
        if (!empty($post['organization_id']) && $post['organization_id'] != 'all') {
            $map['engineer.organization_id'] = $post['organization_id'];
        }
    
        $map['engineer.status'] = 1;
        $map['f.id'] = array('gt', 0);
    
        if (!empty($post['keyword'])) {
            $like['engineer.id'] = array('eq', trim($post['keyword']));
            $like['engineer.work_number'] = array('like', trim($post['keyword']));
            $like['engineer.name'] = array('like', '%' . trim($post['keyword']) . '%');
            $like['engineer.cellphone'] = array('like', '%' . trim($post['keyword']) . '%');
            $like['_logic'] = 'or';
            $map['_complex'] = $like;
        }
    
        $join = 'ei left join fitting f on ei.fittings_id=f.id 
                left join `engineer` on engineer.id = ei.engineer_id
                left join user u on ei.user_id=u.id
                left join`phone_fitting` as pf on pf.fitting_id = ei.fittings_id
                left join phone on phone.id = pf.phone_id';

        $count = M('engineer_inout')->where($map)->join($join)->field('count(1) as count')->group('ei.id')->select();
        $rst['total'] = count($count);

        $list = M('engineer_inout')->join($join)->where($map)
                ->field("engineer.name, ei.type, ei.inout, concat(f.title, '(', f.number, ')') as fitting, ei.amount, ei.order_id, ei.time, 
                    u.username as auditor, group_concat(distinct(phone.alias)) as alias, f.price")
                ->group('ei.id')
                ->order('engineer.id asc,ei.id desc')->select();
        
        $type = array(
            1 => '订单消耗',
            2 => '申请物料',
            3 => '报损',
        );
        $inout = array(1 => '入库', 2 => '出库');
    
        $params = array();
        $params[] = array('工程师', '类型', '出入库', '机型', '配件', '数量', '价格', '订单ID', '经手人', '时间');
    
        foreach ($list as $val) {
            $params[] = array($val['name'], $type[$val['type']], $inout[$val['inout']], $val['alias'], $val['fitting'], $val['amount'], $val['price'], $val['order_id'], $val['auditor'], date('Y-m-d H:i:s', $val['time']));
        }
    
        $this->exportData('工程师物料出入库'.date('Y-m-d-H-i-s'), $params);
    }
    
    /**
     * 更新库存实体
     */
    public function resetStock()
    {
        $id = I('get.id/d');
        $rst = array();
        
        if ($id <= 0) {
            $rst['success'] = false;
            $rst['errorMsg']= '记录不存在';
            $this->ajaxReturn($rst);
        }
        
        $warehouse = M('warehouse')->where(array('id' => $id))->find();
        
        if (!$warehouse) {
            $rst['success'] = false;
            $rst['errorMsg']= '记录不存在';
            $this->ajaxReturn($rst);
        }
        
        if ($warehouse['amount'] < 1) {
            $rst['success'] = false;
            $rst['errorMsg']= '当前库存数量下不需要更新库存实体！';
            $this->ajaxReturn($rst);
        }
        
        $map = array('organization_id' => $warehouse['organization_id'], 'status' => 1, 'fitting_id' => $warehouse['fitting_id']);
        $stocks = M('stock')->where($map)->getField('id, number, price');
        
        $amount = $warehouse['amount'] - count($stocks);
        
        if ($amount == 0) {
            $rst['success'] = true;
        } else {
            
            if ($amount > 0) {
                
                $param = array();
                
                for ($i = 1; $i <= $amount; $i++) {
                    $param[] = array(
                        'number' => D('warehouse')->createNumber(),
                        'status' => 1,
                        'organization_id' => $warehouse['organization_id'],
                        'fitting_id' => $warehouse['fitting_id'],
                        'price' => $stocks ? current($stocks)['price'] : '',
                        'create_time' => time()
                    );
                }
                
                $flag = M('stock')->addAll($param);
                
            } else {
                $stock_ids = array_slice(array_keys($stocks), 0, abs($amount));
                
                $flag = M('stock')->where(array('id' => array('in', $stock_ids)))->delete();
            }
            
            if ($flag) {
                $rst['success'] = true;
            } else {
                $rst['success'] = false;
                $rst['errorMsg']= '库存更新失败，请重试！';
            }
        }
        
        $this->ajaxReturn($rst);
    }
    
    /**
     * 配件大类列表
     */
    public function fittingCategorys()
    {
        $rst = M('fitting_category')->select();
        array_unshift($rst,array('name'=>'全部','id'=>false));
        $this->ajaxReturn($rst);
    }
    
    /**
     * 安全库存
     */
    public function safeStock()
    {
        $this->display();
    }
    
    /**
     * 安全库存列表
     */
    public function safeStockRows()
    {
        $map = array();
        $rst = array();
        $post = I('post.');
        
        $map['o.id'] = array('gt', 0);
        $map['f.id'] = array('gt', 0);
        
        if (!empty($post['phone_id']) && $post['phone_id'] != 'all') {
            $map['pf.phone_id'] = $post['phone_id'];
        }
        
        if (!empty($post['organization_id']) && $post['organization_id'] != 'all') {
            $map['wss.organization_id '] = $post['organization_id'];
        }
        
        if (!empty($post['fitting_id']) && $post['fitting_id'] != 'all') {
            $map['wss.fitting_id '] = $post['fitting_id'];
        }
        
        if (!empty($post['category_id']) && $post['category_id'] != 'all') {
            $map['f.category_id '] = $post['category_id'];
        }
        
        if (!empty($post['keyword'])) {
            $where = array();
            $where['f.number'] = array('like', '%' . $post['keyword'] . '%');
            $where['f.title'] = array('like', '%' . $post['keyword'] . '%');
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }
        
        $join = 'wss left join fitting f on wss.fitting_id=f.id
            left join phone_fitting pf on f.id=pf.fitting_id
            left join phone p on pf.phone_id=p.id
            left join organization o on wss.organization_id=o.id';
        
        $count = M('warehouse_safe_stock')->where($map)->join($join)->count('distinct(wss.id)');
        $rst['total'] = $count;
        
        $list = M('warehouse_safe_stock')->join($join)->where($map)->limit($this->page())
                ->field("wss.*, o.alias as organization, concat(f.title, '(', f.number, ')') as fitting, group_concat(distinct(p.alias)) as phone")
                ->group('wss.id')
                ->order('o.id asc, phone asc, f.number asc')->select();
        
        $rst['rows'] = $list;
        $this->ajaxReturn($rst);
    }
    
    /**
     * 机型配件
     */
    public function phoneFittings()
    {
        $organization_id = I('get.organization_id/d', 0);
        $fiting_id = I('get.fitting_id/d');
        
        if ($organization_id <= 0) {
            $this->ajaxReturn(array());
        }
        
        $map = array('p.id' => array('gt', 0));
        
        $org_fittings = M('warehouse_safe_stock')->where(array('organization_id'=>$organization_id))->getField('id, fitting_id');
        $org_fittings = array_diff($org_fittings, array($fiting_id));
        
        if ($org_fittings) {
            $map['f.id'] = array('not in', $org_fittings);
        }
        
        $list = M('fitting')->join('f left join phone_fitting pf on f.id = pf.fitting_id')
                ->join('left join phone p on p.id = pf.phone_id')
                ->where($map)
                ->field('f.id, concat(group_concat(p.alias), " ", f.title) as name')
                ->group('f.id')
                ->order('name')
                ->select();
        $this->ajaxReturn($list);
    }
    
    /**
     * 新增安全库存
     */
    public function addSafeStock()
    {
        $rst = array();
        $data = array();
        $data = I('post.');
        
        if (empty($data['organization_id'])) {
            $rst['success'] = false;
            $rst['errorMsg'] = '请选择仓库（组织）';
            $this->ajaxReturn($rst);
        }
        
        $map = array('organization_id'=>$data['organization_id'], 'fitting_id' => $data['fitting_id']);
    
        if (M('warehouse_safe_stock')->where($map)->count()) {
            $rst['success'] = false;
            $rst['errorMsg'] = '添加失败（仓库已设置过配件的安全库存）';
            $this->ajaxReturn($rst);
        }
        
        if (M('warehouse_safe_stock')->add($data) !== false) {
            $rst['success'] = true;
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '添加失败！';
        }
        $this->ajaxReturn($rst);
    }
    
    /**
     * 编辑安全库存
     */
    public function editSafeStock()
    {
        $rst = array();
        $data = array();
        $id = I('get.id/d');
        $data = I('post.');
        
        $item = M('warehouse_safe_stock')->where(array('id' => $id))->find();
        
        if (!$item) {
            $rst['success'] = false;
            $rst['errorMsg'] = '记录不存在';
            $this->ajaxReturn($rst);
        }
    
        if (empty($data['organization_id'])) {
            $rst['success'] = false;
            $rst['errorMsg'] = '请选择仓库（组织）';
            $this->ajaxReturn($rst);
        }
        
        $map = array('organization_id'=>$data['organization_id'], 'fitting_id' => $data['fitting_id'], 'id' => array('neq', $id));
    
        if (M('warehouse_safe_stock')->where($map)->count()) {
            $rst['success'] = false;
            $rst['errorMsg'] = '编辑失败（仓库已设置过配件的安全库存）';
            $this->ajaxReturn($rst);
        }
    
        if (M('warehouse_safe_stock')->where(array('id' => $id))->save($data) !== false) {
            $rst['success'] = true;
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '编辑失败！';
        }
        $this->ajaxReturn($rst);
    }
    
    /**
     * 删除安全库存
     */
    public function removeSafeStock()
    {
        $rst = array();
        $id = I('post.id/d');
    
        if (M('warehouse_safe_stock')->where(array('id' => $id))->delete() !== false) {
            $rst['success'] = true;
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '删除失败！';
        }
        $this->ajaxReturn($rst);
    }
    
    /**
     * 导入安全库存
     */
    public function importSafeStock()
    {
        $rst = array();
        
        Vendor('PHPExcel.Classes.PHPExcel.IOFactory');
        
        $inputFileType = \PHPExcel_IOFactory::identify($_FILES['data_file']['tmp_name']);
        $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel = $objReader->load($_FILES['data_file']['tmp_name']);
        $data = $objPHPExcel->getSheet(0)->toArray();
        
        $total_rows = 0; //总行数
        $fail_rows = array(); //导入失败行
        $param = array();
        $orgs = array();
        $fittings = array();
        
        foreach ($data as $k => $value) {
        
            if ($k < 1) {
                continue;
            }
        
            $total_rows++;
        
            if (empty($value[0]) || empty($value[1]) || empty($value[3]) || !is_numeric($value[3])) {
                $fail_rows[] = "第{$k}行仓库（地区）、配件编号、安全库存不能为空！";
                continue;
            }
            
            $param[$k] = array(
                'organization_id' => trim($value[0]),
                'fitting_id' => trim($value[1]),
                'amount' => trim($value[3]),
                'remark' => trim($value[4]),
            );
            
            $orgs[] = trim($value[0]);
            $fittings[] = trim($value[1]);
        }
        
        $fittings = M('fitting')->where(array('number'=>array('in', array_unique($fittings))))->getField('number, id');
        $orgs = M('organization')->where(array('number'=>array('in', array_unique($orgs))))->getField('alias, id');
        
        $data = array();
        $flag = false;
        M()->startTrans();
        
        foreach ($param as $k => $v) {
            
            if (isset($fittings[$v['fitting_id']]) && isset($orgs[$v['organization_id']])) {
                $v['fitting_id'] = $fittings[$v['fitting_id']];
                $v['organization_id'] = $orgs[$v['organization_id']];
                
                $id = M('warehouse_safe_stock')->where(array('fitting_id'=>$v['fitting_id'],'organization_id'=>$v['organization_id']))->getField('id');
                
                if ($id) {
                    $flag = true;
                    M('warehouse_safe_stock')->where(array('id' => $id))->save(array('amount' => $v['amount'], 'remark' => $v['remark']));
                } else {
                    $data[$v['organization_id'].'_'.$v['fitting_id']] = $v;
                }
            } else {
                $fail_rows[] = "第{$k}行仓库（地区）或配件编号不存在！";
            }
        }
        
        if ($flag || ($data && M('warehouse_safe_stock')->addAll(array_values($data)) !== false)) {
            M()->commit();
            $rst['success'] = true;
            $success_rows = $total_rows - count($fail_rows);
            $rst['errorMsg'] = "导入{$total_rows}行数据；成功导入{$success_rows}行！";
        
            if ($fail_rows) {
                $rst['errorMsg'] .= "导入失败：" . implode(' ', $fail_rows) . '。';
            }
        } else {
            M()->rollback();
            $rst['success'] = false;
            $rst['errorMsg'] = '仓库安全库存导入失败！';
            
            if ($fail_rows) {
                $rst['errorMsg'] .= "失败原因：" . implode(' ', $fail_rows) . '。';
            }
        }
        
        $this->ajaxReturn($rst);
    }
}