<?php

// +------------------------------------------------------------------------------------------ 
// | Author: longDD <longdd_love@163.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil, no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 仓库模型 Dates: 2016-09-27
// +------------------------------------------------------------------------------------------

namespace Admin\Model;

use Think\Model;

class WarehouseModel extends Model
{
	/**
	 * 入库批次号
	 *
	 * @return void
	 */
	public function createBatch()
	{
		/** 初始化redis */
        $redis = new \Redis();
        $redis->connect(C('REDIS_HOST'), C('REDIS_PORT'));
        $expireTime = strtotime('tomorrow') - 1;
        $batchSerialNumber = $redis->get('batchSerialNumber');

        /** 23:59:59 过期 获取不到流水设置为 0, 设置过期时间 获取流水号并且递增 */
        if (!$batchSerialNumber) {
            $batchSerialNumber = 0;
        }

        $redis->set('batchSerialNumber', $batchSerialNumber + 1);
        $redis->expireat('batchSerialNumber', $expireTime);

        /** 生成规则 年月日 + 流水号 */
        $number = date('ymd') . str_pad($batchSerialNumber, 4, "0", STR_PAD_LEFT);
        return $number;
	}

    /**
     * 调拨批次号
     *
     * @return void
     */
    public function allotBatch()
    {
        /** 初始化redis */
        $redis = new \Redis();
        $redis->connect(C('REDIS_HOST'), C('REDIS_PORT'));
        $expireTime = strtotime('tomorrow') - 1;
        $batchSerialNumber = $redis->get('batchSerialNumber');

        /** 23:59:59 过期 获取不到流水设置为 0, 设置过期时间 获取流水号并且递增 */
        if (!$batchSerialNumber) {
            $batchSerialNumber = 0;
        }

        $redis->set('batchSerialNumber', $batchSerialNumber + 1);
        $redis->expireat('batchSerialNumber', $expireTime);

        /** 生成规则 年月日 + 流水号 */
        $number = date('ymd') . str_pad($batchSerialNumber, 4, "0", STR_PAD_LEFT);
        return $number;
    }

	/**
	 * 物料编号
	 *
	 * @return void
	 */
	public function createNumber()
	{
		/** 初始化redis */
        $redis = new \Redis();
        $redis->connect(C('REDIS_HOST'), C('REDIS_PORT'));
        $expireTime = strtotime('tomorrow') - 1;
        $fittingSerialNumber = $redis->get('fittingSerialNumber');

        /** 23:59:59 过期 获取不到流水设置为 0, 设置过期时间 获取流水号并且递增 */
        if (!$fittingSerialNumber) {
            $fittingSerialNumber = 0;
        }

        $redis->set('fittingSerialNumber', $fittingSerialNumber + 1);
        $redis->expireat('fittingSerialNumber', $expireTime);

        /** 生成规则 类型 + 地区 + 年月日 + 流水号 */
        $number = date('ymd') . str_pad($fittingSerialNumber, 4, "0", STR_PAD_LEFT);
        return $number;
	}

    /**
     * 入库
     *
     * @return void
     */
    public function putin($data)
    {
        $fittings = json_decode($data['fittings'], true);

        if (!empty($fittings)) {
            foreach ($fittings as $fitting) {

                /** 更新库存 */
                $map = array();
                $map['fitting_id'] = $fitting['fitting_id'];
                $map['organization_id'] = $data['proposer_org'];
                $current = M('warehouse')->where($map)->find();

                /** 判断是否存在该配件 */
                if (!$current) {
                    /** 写入该配件信息 */
                    $item = array();
                    $item['organization_id'] = $data['proposer_org'];
                    $item['fitting_id'] = $fitting['fitting_id'];
                    $item['amount'] = $fitting['amount'];

                    if (M('warehouse')->add($item) === false) {
                        lg('[入库]增加库存错误(插入数据) [' . json_encode($data) . ']', 'ERR');
                        throw new \Exception('[入库]增加 '.$fitting['phone.'].$fitting['fitting'].' 库存错误(插入数据)');
                    }
                } else {
                    /** 更新库存数量 */
                    $item = array();
                    $item['amount'] = $fitting['amount'] + $current['amount'];

                    if (M('warehouse')->where($map)->save($item) === false) {
                        lg('[入库]增加库存错误(更新数据) ['. json_encode($data) . ']', 'ERR');
                        throw new \Exception('[入库]增加 '.$fitting['phone.'].$fitting['fitting'].' 库存错误(更新数据)');
                    }
                }

                /** 更新实体 生成唯一编码 */
                $numbers = array();

                for ($i = 0; $i < $fitting['amount']; $i++) { 
                    $item = array();
                    $item['number'] = $this->createNumber();
                    /** 1 入库 2 调拨 3 工程师 4 消耗 -1 损坏 */
                    $item['status'] = 1;
                    $item['organization_id'] = $data['proposer_org'];
                    $item['fitting_id'] = $fitting['fitting_id'];
                    $item['price'] = $fitting['price'];
                    $item['batch'] = $data['batch'];
                    $item['provider_id'] = $data['provider_id'];
                    $item['create_time'] = time();

                    $id = M('stock')->add($item);

                    if ($id === false) {
                        lg('[入库]更新实体库存错误 [ '. json_encode($data) . ']', 'ERR');
                        throw new \Exception('[入库]插入实体 '.$fitting['phone.'].$fitting['fitting'].' 库存错误(插入数据)');
                    }

                    $numbers[$id] = $item['number'];
                }

                /** 更新出入库  变更日志 */
                $log = array();
                /** 类型 1 出入库 2 调拨 3 工程师申请 4 报损 */
                $log['type'] = 1;
                $log['batch'] = $data['batch'];
                $log['fitting_id'] = $fitting['fitting_id'];
                $log['user_id'] = $data['auditor'];
                $log['organization_id'] = $data['proposer_org'];
                $log['provider_id'] = $data['provider_id'];
                /** 操作类型 1 入库 2 出库 */
                $log['inout'] = 1;
                $log['amount'] = $fitting['amount'];
                $log['fittings'] = json_encode($numbers);
                $log['price'] = $fitting['price'];
                $log['time'] = time();

                if (M('inout')->add($log) === false) {
                    lg('[入库]写入仓库日志错误['. json_encode($data) . ']', 'ERR');
                    throw new \Exception('[入库]写入仓库日志错误');
                }
            }
        }

        return true;
    }

    /**
     * 发放
     *
     * @return array $batchs 批次信息
     */
    public function give($data)
    {
        $fittings = json_decode($data['fittings'], true);

        if (!empty($fittings)) {
            $batch = array();

            foreach ($fittings as $fitting) {

                $fitting['fitting_id'] = $fitting['fitting_id'] ? $fitting['fitting_id'] : $fitting['id'];
                $fitting['fitting'] = $fitting['fitting'] ? $fitting['fitting'] : $fitting['name'];
                
                /** 更新库存 */
                $map = array();
                $map['fitting_id'] = $fitting['fitting_id'];
                $map['organization_id'] = $data['organization_id'];
                $current = M('warehouse')->where($map)->find();

                /** 减少库存 */
                /** 判断是否存在该配件 */
                if (!$current) {
                    lg('[工程师发放]减少库存错误(配件不存在) ['. json_encode($data) . ']', 'ERR');
                    throw new \Exception('[工程师发放]减少库存错误:配件 ' . $fitting['phone'].$fitting['fitting'] . '不存在');
                }

                /** 判断配件数量是否充足 */
                if (($current['amount'] - $fitting['amount']) < 0) {
                    lg('[工程师发放]减少库存错误(配件数量不足) ['. json_encode($data) . ']', 'ERR');
                    throw new \Exception('[工程师发放]减少库存错误:配件 '.$fitting['phone.'].$fitting['fitting'].'数量不足');
                }

                /** 更新库存数量 */
                $item = array();
                $item['amount'] = $current['amount'] - $fitting['amount'];

                if (M('warehouse')->where($map)->save($item) === false) {
                    lg('[工程师发放]减少库存错误(更新数据) [' . json_encode($data) . ']', 'ERR');
                    throw new \Exception('[工程师发放]减少'.$fitting['phone.'].$fitting['fitting'].'库存错误(更新数据)');
                }

                /** 更新工程师库存 */
                $map = array();
                $map['fittings_id'] = $fitting['fitting_id'];
                $map['engineer_id'] = $data['engineer_id'];
                $current = M('engineer_warehouse')->where($map)->find();

                /** 判断是否存在该配件 */
                if (!$current) {
                    /** 写入该配件信息 */
                    $item = array();
                    $item['engineer_id'] = $data['engineer_id'];
                    $item['fittings_id'] = $fitting['fitting_id'];
                    $item['fittings_name'] = $fitting['fitting'];
                    $item['amount'] = $fitting['amount'];

                    if (M('engineer_warehouse')->add($item) === false) {
                        lg('[工程师发放]增加工程师库存错误(插入数据) [' . json_encode($data) . ']', 'ERR');
                        throw new \Exception('[工程师发放]增加工程师'.$fitting['phone.'].$fitting['fitting'].'库存错误(插入数据)');
                    }
                } else {
                    /** 更新库存数量 */
                    $item = array();
                    $item['amount'] = $fitting['amount'] + $current['amount'];

                    if (M('engineer_warehouse')->where($map)->save($item) === false) {
                        lg('[工程师发放]增加工程师库存错误(更新数据) ['. json_encode($data) . ']', 'ERR');
                        throw new \Exception('[工程师发放]增加工程师'.$fitting['phone.'].$fitting['fitting'].'库存错误(更新数据)');
                    }
                }

                /** 更新实体 先进先出 */
                $map = array();
                /** 1 入库 2 调拨 3 工程师 4 消耗 -1 损坏 */
                $map['status'] = 1;
                $map['fitting_id'] = $fitting['fitting_id'];
                $map['organization_id'] = $data['organization_id'];
                $goods = M('stock')->where($map)->limit($fitting['amount'])->order('id asc')->getField('id, number');
                
                if (!$goods) {
                    lg('[工程师发放]'.$fitting['phone.'].$fitting['fitting'].'库存实体不能存在['. json_encode($data) . ']', 'ERR');
                    throw new \Exception('[工程师发放]'.$fitting['phone.'].$fitting['fitting'].'库存实体不能存在');
                }
                
                if (count($goods) != $fitting['amount']) {
                    lg('[工程师发放]'.$fitting['phone.'].$fitting['fitting'].'库存实体不足['. json_encode($data) . ']', 'ERR');
                    throw new \Exception('[工程师发放]'.$fitting['phone.'].$fitting['fitting'].'库存实体不足');
                }

                /** 更新状态 更新仓库 */
                $map = array();
                $map['id'] = array('in', array_keys($goods));
                $stock = array();
                $stock['status'] = 3;
                $stock['organization_id'] = 0;
                $stock['engineer_id'] = $data['engineer_id'];

                if (M('stock')->where($map)->save($stock) === false) {
                    lg('[工程师发放]更新实体库存错误 [ '. json_encode($data) . ']', 'ERR');
                    throw new \Exception('[工程师发放]更新实体库存'.$fitting['phone.'].$fitting['fitting'].'错误(更新数据)');
                }

                /** 更新出入库  变更日志 */
                $log = array();
                /** 类型 1 出入库 2 调拨 3 工程师申请 4 报损 */
                $log['type'] = 3;
                $log['fitting_id'] = $fitting['fitting_id'];
                $log['user_id'] = session('userId');
                $log['organization_id'] = $data['organization_id'];
                $log['engineer_id'] = $data['engineer_id'];
                /** 操作类型 1 入库 2 出库 */
                $log['inout'] = 2;
                $log['amount'] = $fitting['amount'];
                $log['fittings'] = json_encode($goods);
                $log['time'] = time();

                if (M('inout')->add($log) === false) {
                    lg('[工程师发放]写入仓库日志错误['. json_encode($data) . ']', 'ERR');
                    throw new \Exception('[工程师发放]写入仓库日志错误');
                }

                /** 更新工程师日志 */
                $log = array();
                /** 类型 1 订单消耗 2 申请物料 3 报损 */
                $log['type'] = 2;
                $log['fittings_id'] = $fitting['fitting_id'];
                $log['user_id'] = session('userId');
                $log['engineer_id'] = $data['engineer_id'];
                /** 操作类型 1 入库 2 出库 */
                $log['inout'] = 1;
                $log['amount'] = $fitting['amount'];
                $log['fittings'] = json_encode($goods);
                $log['time'] = time();

                if (M('engineer_inout')->add($log) === false) {
                    lg('[工程师发放]写入工程师日志错误['. json_encode($data) . ']', 'ERR');
                    throw new \Exception('[工程师发放]写入工程师日志错误');
                }
            }
        }

        return true;
    }

    /**
     * 退还
     *
     * @return void
     */
    public function refund($data)
    {
        $fittings = json_decode($data['fittings'], true);

        if (!empty($fittings)) {
            $batch = array();

            foreach ($fittings as $fitting) {

                $fitting['fitting_id'] = $fitting['fitting_id'] ? $fitting['fitting_id'] : $fitting['id'];
                $fitting['fitting'] = $fitting['fitting'] ? $fitting['fitting'] : $fitting['name'];
                
                /** 更新工程师库存 */
                $map = array();
                $map['fittings_id'] = $fitting['fitting_id'];
                $map['engineer_id'] = $data['engineer_id'];
                $current = M('engineer_warehouse')->where($map)->find();

                /** 减少库存 */
                /** 判断是否存在该配件 */
                if (!$current) {
                    lg('[工程师退还]配件不存在 ['. json_encode($data) . ']', 'ERR');
                    throw new \Exception('[工程师退还]配件 '.$fitting['phone.'].$fitting['fitting'].' 不存在');
                }

                /** 判断配件数量是否充足 */
                if (($current['amount'] - $fitting['amount']) < 0) {
                    lg('[工程师退还]配件数量不足 ['. json_encode($data) . ']', 'ERR');
                    throw new \Exception('[工程师退还]配件 '.$fitting['phone.'].$fitting['fitting'].' 数量不足');
                }

                /** 更新库存数量 */
                $item = array();
                $item['amount'] = $current['amount'] - $fitting['amount'];

                if (M('engineer_warehouse')->where($map)->save($item) === false) {
                    lg('[工程师退还]减少库存错误(更新数据) [' . json_encode($data) . ']', 'ERR');
                    throw new \Exception('[工程师退还]减少配件 '.$fitting['phone.'].$fitting['fitting'].' 库存错误(更新数据)');
                }

                /** 更新库存 */
                $map = array();
                $map['fitting_id'] = $fitting['fitting_id'];
                $map['organization_id'] = $data['organization_id'];

                $current = M('warehouse')->where($map)->find();

                /** 判断是否存在该配件 */
                if (!$current) {
                    /** 写入该配件信息 */
                    $item = array();
                    $item['organization_id'] = $data['organization_id'];
                    $item['fitting_id'] = $fitting['fitting_id'];
                    $item['amount'] = $fitting['amount'];

                    if (M('warehouse')->add($item) === false) {
                        lg('[工程师退还]增加工程师库存错误(插入数据) [' . json_encode($data) . ']', 'ERR');
                        throw new \Exception('[工程师退还]增加工程师 '.$fitting['phone.'].$fitting['fitting'].' 库存错误(插入数据)');
                    }
                } else {
                    /** 更新库存数量 */
                    $item = array();
                    $item['amount'] = $fitting['amount'] + $current['amount'];

                    if (M('warehouse')->where($map)->save($item) === false) {
                        lg('[工程师退还]增加工程师库存错误(更新数据) ['. json_encode($data) . ']', 'ERR');
                        throw new \Exception('[工程师退还]增加工程师 '.$fitting['phone.'].$fitting['fitting'].' 库存错误(更新数据)');
                    }
                }

                /** 更新实体 先进先出 */
                $map = array();
                /** 1 入库 2 调拨 3 工程师 4 消耗 -1 损坏 */
                $map['status'] = 3;
                $map['fitting_id'] = $fitting['fitting_id'];
                $map['engineer_id'] = $data['engineer_id'];
                $goods = M('stock')->where($map)->limit($fitting['amount'])->order('id desc')->getField('id, number');
                
                if (!$goods) {
                    lg('[工程师退还]'.$fitting['phone.'].$fitting['fitting'].'库存实体不能存在['. json_encode($data) . ']', 'ERR');
                    throw new \Exception('[工程师退还]'.$fitting['phone.'].$fitting['fitting'].'库存实体不能存在');
                }
                
                if (count($goods) != $fitting['amount']) {
                    lg('[工程师退还]'.$fitting['phone.'].$fitting['fitting'].'库存实体不足['. json_encode($data) . ']', 'ERR');
                    throw new \Exception('[工程师退还]'.$fitting['phone.'].$fitting['fitting'].'库存实体不足');
                }

                /** 更新状态 更新仓库 */
                $map = array();
                $map['id'] = array('in', array_keys($goods));

                $stock = array();
                $stock['status'] = 1;
                $stock['engineer_id'] = 0;
                $stock['organization_id'] = $data['organization_id'];

                if (M('stock')->where($map)->save($stock) === false) {
                    lg('[工程师退还]更新实体库存错误 [ '. json_encode($data) . ']', 'ERR');
                    throw new \Exception('[工程师退还]更新实体 '.$fitting['phone.'].$fitting['fitting'].'库存错误(更新数据)');
                }

                /** 更新出入库  变更日志 */
                $log = array();
                /** 类型 1 出入库 2 调拨 3 工程师申请 4 报损 */
                $log['type'] = 3;
                $log['fitting_id'] = $fitting['fitting_id'];
                $log['user_id'] = session('userId');
                $log['engineer_id'] = $data['engineer_id'];
                $log['organization_id'] = $data['organization_id'];
                /** 操作类型 1 入库 2 出库 */
                $log['inout'] = 1;
                $log['amount'] = $fitting['amount'];
                $log['fittings'] = json_encode($goods);
                $log['time'] = time();

                if (M('inout')->add($log) === false) {
                    lg('[工程师退还]写入仓库日志错误['. json_encode($data) . ']', 'ERR');
                    throw new \Exception('[工程师退还]写入仓库日志错误');
                }

                /** 更新工程师日志 */
                $log = array();
                /** 类型 1 订单消耗 2 申请物料 3 报损 */
                $log['type'] = 2;
                $log['fittings_id'] = $fitting['fitting_id'];
                $log['user_id'] = session('userId');
                $log['engineer_id'] = $data['engineer_id'];
                /** 操作类型 1 入库 2 出库 */
                $log['inout'] = 2;
                $log['amount'] = $fitting['amount'];
                $log['fittings'] = json_encode($goods);
                $log['time'] = time();

                if (M('engineer_inout')->add($log) === false) {
                    lg('[工程师退还]写入工程师日志错误['. json_encode($data) . ']', 'ERR');
                    throw new \Exception('[工程师退还]写入工程师日志错误');
                }
            }
        }

        return true;
    }

    /**
     * 调拨发货
     *
     * @return void
     */
    public function send($data)
    {
        $fittings = json_decode($data['fittings'], true);

        if (!empty($fittings)) {
            $batch = array();

            foreach ($fittings as $fitting) {
                
                if ($fitting['amount'] <= 0) {
                    continue;
                }

                /** 更新库存 */
                $map = array();
                $map['fitting_id'] = $fitting['fitting_id'];

                /** 申请 or 退还 */
                if ($data['type'] == 1) {
                    $map['organization_id'] = $data['auditor_org'];
                } else {
                    $map['organization_id'] = $data['proposer_org'];
                }

                $current = M('warehouse')->where($map)->find();

                /** 减少库存 */
                /** 判断是否存在该配件 */
                if (!$current) {
                    lg('[调拨发货]减少库存错误(配件不存在) ['. json_encode($data) . ']', 'ERR');
                    throw new \Exception('[调拨发货]减少库存错误:配件' .$fitting['phone'].$fitting['fitting'].' 不存在');
                }

                /** 判断配件数量是否充足 */
                if (($current['amount'] - $fitting['amount']) < 0) {
                    lg('[调拨发货]减少库存错误(配件数量不足) ['. json_encode($data) . ']', 'ERR');
                    throw new \Exception('[调拨发货]减少 库存错误:配件' .$fitting['phone'].$fitting['fitting'].' 数量不足)');
                }

                /** 更新库存数量 */
                $item = array();
                $item['amount'] = $current['amount'] - $fitting['amount'];

                if (M('warehouse')->where($map)->save($item) === false) {
                    lg('[调拨发货]减少库存错误(更新数据) [' . json_encode($data) . ']', 'ERR');
                    throw new \Exception('[调拨发货]减少 '.$fitting['phone.'].$fitting['fitting'].' 库存错误(更新数据)');
                }

                /** 更新实体 先进先出 */
                $map = array();
                /** 1 入库 2 调拨 3 工程师 4 消耗 -1 损坏 */
                $map['status'] = 1;
                $map['fitting_id'] = $fitting['fitting_id'];

                /** 申请 or 退还 */
                if ($data['type'] == 1) {
                    $map['organization_id'] = $data['auditor_org'];
                } else {
                    $map['organization_id'] = $data['proposer_org'];
                }
                
                $goods = M('stock')->where($map)->limit($fitting['amount'])->order('id desc')->getField('id, number');
                
                if (!$goods) {
                    lg('[调拨发货]'.$fitting['phone.'].$fitting['fitting'].'库存实体不能存在['. json_encode($data) . ']', 'ERR');
                    throw new \Exception('[调拨发货]'.$fitting['phone.'].$fitting['fitting'].'库存实体不能存在');
                }
                
                if (count($goods) != $fitting['amount']) {
                    lg('[调拨发货]'.$fitting['phone.'].$fitting['fitting'].'库存实体不足['. json_encode($data) . ']', 'ERR');
                    throw new \Exception('[调拨发货]'.$fitting['phone.'].$fitting['fitting'].'库存实体不足');
                }
                
                $batch[$fitting['fitting_id']] = $goods;

                /** 更新状态 更新仓库 */
                $map = array();
                $map['id'] = array('in', array_keys($goods));
                $stock = array();
                $stock['status'] = 2;
                $stock['organization_id'] = 0;

                if (M('stock')->where($map)->save($stock) === false) {
                    lg('[调拨发货]更新实体库存错误 [ '. json_encode($data) . ']', 'ERR');
                    throw new \Exception('[调拨发货]更新实体'.$fitting['phone.'].$fitting['fitting'].'库存错误(更新数据)');
                }

                /** 更新出入库  变更日志 */
                $log = array();
                /** 类型 1 出入库 2 调拨 3 工程师申请 4 报损 */
                $log['type'] = 2;
                $log['fitting_id'] = $fitting['fitting_id'];
                $log['user_id'] = session('userId');

                if ($data['type'] == 1) {
                    $log['organization_id'] = $data['auditor_org'];
                    $log['target_orgid'] = $data['proposer_org'];
                } else {
                    $log['organization_id'] = $data['proposer_org'];
                    $log['target_orgid'] = $data['auditor_org'];
                }
                
                /** 操作类型 1 入库 2 出库 */
                $log['inout'] = 2;
                $log['amount'] = $fitting['amount'];
                $log['fittings'] = json_encode($goods);
                $log['time'] = time();

                if (M('inout')->add($log) === false) {
                    lg('[调拨发货]写入仓库日志错误['. json_encode($data) . ']', 'ERR');
                    throw new \Exception('[调拨发货]写入仓库日志错误');
                }
            }
        }

        return $batch;
    }

    /**
     * 调拨收货
     *
     * @return void
     */
    public function receive($data)
    {
        $fittings = json_decode($data['fittings'], true);

        if (!empty($fittings)) {
            foreach ($fittings as $fitting) {
                
                if ($fitting['amount'] <= 0) {
                    continue;
                }

                /** 更新库存 */
                $map = array();
                $map['fitting_id'] = $fitting['fitting_id'];
                
                if ($data['type'] == 1) {
                    $map['organization_id'] = $data['proposer_org'];
                } else {
                    $map['organization_id'] = $data['auditor_org'];
                }

                $current = M('warehouse')->where($map)->find();

                /** 判断是否存在该配件 */
                if (!$current) {
                    /** 写入该配件信息 */
                    $item = array();
                    
                    if ($data['type'] == 1) {
                        $item['organization_id'] = $data['proposer_org'];
                    } else {
                        $item['organization_id'] = $data['auditor_org'];
                    }

                    $item['fitting_id'] = $fitting['fitting_id'];
                    $item['amount'] = $fitting['amount'];

                    if (M('warehouse')->add($item) === false) {
                        lg('[调拨收货]增加库存错误(插入数据) [' . json_encode($data) . ']', 'ERR');
                        throw new \Exception('[调拨收货]增加 '.$fitting['phone.'].$fitting['fitting'].' 库存错误(插入数据)');
                    }
                } else {
                    /** 更新库存数量 */
                    $item = array();
                    $item['amount'] = $fitting['amount'] + $current['amount'];

                    if (M('warehouse')->where($map)->save($item) === false) {
                        lg('[调拨收货]增加库存错误(更新数据) ['. json_encode($data) . ']', 'ERR');
                        throw new \Exception('[调拨收货]增加 '.$fitting['phone.'].$fitting['fitting'].' 库存错误(更新数据)');
                    }
                }

                $batch = json_decode($data['batch'], true);

                if (!empty($batch[$fitting['fitting_id']])) {
                    /** 更新实体 */
                    $map = array();
                    $map['id'] = array('in', array_keys($batch[$fitting['fitting_id']]));
                    $stock = array();
                    $stock['status'] = 1;

                    if ($data['type'] == 1) {
                        $stock['organization_id'] = $data['proposer_org'];
                    } else {
                        $stock['organization_id'] = $data['auditor_org'];
                    }

                    if (M('stock')->where($map)->save($stock) === false) {
                        lg('[调拨收货]更新实体库存错误 [ '. json_encode($data) . ']', 'ERR');
                        throw new \Exception('[调拨收货]更新实体 '.$fitting['phone.'].$fitting['fitting'].' 库存错误(更新数据)');
                    }
                }

                /** 更新出入库  变更日志 */
                $log = array();
                /** 类型 1 出入库 2 调拨 3 工程师申请 4 报损 */
                $log['type'] = 2;
                $log['fitting_id'] = $fitting['fitting_id'];

                if ($data['type'] == 1) {
                    $log['organization_id'] = $data['proposer_org'];
                    $log['target_orgid'] = $data['auditor_org'];
                } else {
                    $log['organization_id'] = $data['auditor_org'];
                    $log['target_orgid'] = $data['proposer_org'];
                }

                /** 操作类型 1 入库 2 出库 */
                $log['inout'] = 1;
                $log['amount'] = $fitting['amount'];
                $log['fittings'] = json_encode($batch[$fitting['fitting_id']]);
                $log['user_id'] = session('userId');
                $log['time'] = time();

                if (M('inout')->add($log) === false) {
                    lg('[调拨收货]写入仓库日志错误['. json_encode($data) . ']', 'ERR');
                    throw new \Exception('[入库]写入仓库日志错误');
                }
            }
        }

        return true;
    }

    /**
     * 报损
     *
     * @return void
     */
    public function breakage()
    {
        #code...
    }

}