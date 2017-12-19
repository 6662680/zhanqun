<?php

// +------------------------------------------------------------------------------------------ 
// | Author: liyang
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 数据迁移 Dates: 2016-8-22
// | 可直接使用的表为：type,goods_brand,goods_color,malfunction(description改成remark,添加img字段)
// +------------------------------------------------------------------------------------------ 

namespace Cli\Controller;

use \Think\Controller;

class MigrationController extends Controller 
{
    private $secondaryDB ='' ;
    private $org = array(
                        0 => '0',
                        1 => '3',//杭州分仓
                        2 => '11',//总仓
                        3 => '1',//北京分仓
                        4 => '2',//上海分仓
                        5 => '4',//广州分仓
                        6 => '5',//深圳分仓
                        7 => '6',//成都分仓
                        8 => '7',//苏州分仓
                        9 => '9',//武汉分仓
                        10 => '8',//南京分仓
                        11 => '10',//重庆分仓
                        12 => '13',//无锡分仓
                    );

    public function __construct()
    {
        parent::__construct();
        $this->secondaryDB = "mysql://longdd:long01@121.41.24.216:3306/shanxiuxia#utf8";
    }
    
    /**
     * user
     */
    public function user()
    {
        //角色
        $data = M('role', '', $this->secondaryDB)->select();
        
        if ($data) {
            M('role')->addAll($data);
        }
        
        //用户
        $data = M('user', '', $this->secondaryDB)->select();
        
        if ($data) {
            
            $user_roles = array();
            
            foreach ($data as &$item) {
                $item['password'] = createPassword('123456');
                
                if ($item['role_id'] > 0) {
                    $user_roles[] = array(
                        'user_id' => $item['id'],
                        'role_id' => $item['role_id']
                    );
                }
            }
            
            M('user')->addAll($data);
            M('user_role')->addAll($user_roles);
        }
    }
    
    /**
     * goods
     */
    public function goods()
    {
        $data = M('goods_brand','',$this->secondaryDB)
                ->field('id, name, image_url as url, web_image_url as wap_url, sort, image_url_click as url_click')
                ->select();
        
        if ($data) {
            M('goods_brand')->addAll($data);
        }
        
        $data = M('goods_category','',$this->secondaryDB)->select();
        
        if ($data) {
            M('goods_category')->addAll($data);
        }
        
        $data = M('goods_color','',$this->secondaryDB)->select();
        
        if ($data) {
            M('goods_color')->addAll($data);
        }
        
        $data = M('goods_configuration','',$this->secondaryDB)->select();
        
        if ($data) {
            M('goods_conf')->addAll($data);
        }
        
        $data = M('goods_type','',$this->secondaryDB)->select();
        
        if ($data) {
            M('goods_type')->addAll($data);
        }
        
        unset($data);
    }

    /**
     * phone
     *
     */
    public function phone()
    {

        $data = M('phone','',$this->secondaryDB)->select();
        foreach($data as &$value ) {
            $value['brand'] = M('goods_brand')->where(['id'=>$value['brand_id']])->getField(['name']);
            $value['type'] = M('goods_type')->where(['id'=>$value['type_id']])->getField(['name']);
            $value['category'] = M('goods_category')->where(['id'=>$value['category_id']])->getField(['name']);

            $value['color'] = json_decode($value['color']);
            foreach($value['color'] as $k => $v) {
                $value['colors'][$k] = M('goods_color')->where(['id'=>$v])->getField(['name']);
            }
            $value['colorTmp'] = implode(",",$value['colors']);
            $value['color_id'] = $value['color'];
            $value['color'] = $value['colorTmp'];
            $value['color_id'] = implode(',',$value['color_id']);
            $value['img'] = $value['phone_img'];
            unset($value['colors']);
            unset($value['phone_img']);
            unset($value['colorTmp']);
            M('phone')->add($value);
        }
    }

    /**
     * phone_malfunction
     */
    public function phone_malfunction()
    {
        $data = M('malfunction','',$this->secondaryDB)->field('id, name, description as remark')->select();
        
        if ($data) {
            M('malfunction')->addAll($data);
        }
        
        $data = M('phone_malfunction','',$this->secondaryDB)->select();

        foreach ($data as  &$value) {
            $value['price_market'] = $value['market_price'];
            $value['price_reference'] = $value['reference_price'];
            $value['divide_local'] = $value['local_divide'];
            $value['divide_platform'] = $value['platform_divide'];
            $value['malfunction'] = M('malfunction')->where(['id'=>$value['malfunction_id']])->getField('name');
            
            if (!$value['malfunction']) {
                continue;
            }
            
            $tmp = json_decode($value['required_part'],true);

            if ($tmp) {
                
                foreach ($tmp as $k => &$v) {

                    if ($v['color_id']){
                        $value['fitting'][$v['color_id']]['id'] = $v['color_id'];
                        $value['fitting'][$v['color_id']]['name'] = $v['color_name'];

                        foreach ($v['fittings'] as $j) {
                            $value['fitting'][$v['color_id']]['items'][$j['id']] = $j;
                        }
                    } else {
                        $value['fitting'][$v['id']] = $v;
                    }
                }
                
                $value['fitting'] = json_encode($value['fitting']);
            }

            if ($value['waste_fitting']) {

                foreach (json_decode($value['waste_fitting'],true) as $k => $v) {
                    $value['waste'][$v['id']] = $v;
                }
                $value['waste'] = json_encode($value['waste']);
            }

            $value['is_hot'] = (int)$value['easy_function'];
            unset($value['market_price']);
            unset($value['reference_price']);
            unset($value['local_divide']);
            unset($value['platform_divide']);
            unset($value['required_part']);
            unset($value['waste_fitting']);
            unset($value['easy_function_img']);
            unset($value['easy_function_img_click']);
            unset($value['easy_function_img_highlighted']);
            
            M('phone_malfunction')->add($value);
        }
    }

    /**
     * organization
     *
     */
    public function organization()
    {
        $data = M('company_address','',$this->secondaryDB)->select();
        foreach($data as &$value) {
            $value['name'] = $value['subname'];
            $value['alias'] = mb_substr($value['subname'],0,2);
            $value['cellphone'] = $value['phonecall'];
            $value['status'] = 1;
            M('organization')->add($value);
        }
    }
    
    /**
     * fittings
     */
    public function fitting()
    {
        $data = M('fittings','',$this->secondaryDB)->field('*, price_engineer as price')->select();
        
        if ($data) {
            M('fitting')->addAll($data);
        }
        
        $data = M('phone_fittings','',$this->secondaryDB)->field('phone_id, fittings_id as fitting_id')->select();
        
        if ($data) {
            M('phone_fitting')->addAll($data);
        }

        unset($data);
    }
    
    /**
     * wastes
     */
    public function waste()
    {
        //废料管理
        $data = M('waste','',$this->secondaryDB)->field("*, price_engineer as price")->select();
    
        if ($data) {
            M('waste')->addAll($data);
        }
        
        //机型废料管理
        $data = M('phone_waste','',$this->secondaryDB)->field('phone_id, waste_id')->select();
        
        if ($data) {
            M('phone_waste')->addAll($data);
        }
        
        unset($data);
    }
    
    /**
     * customer
     */
    public function customer()
    {
        //客户
        $model = M('customer', '', $this->secondaryDB);
        
        $count = $model->count();
        $pageCount = ceil($count / 5000);
        
        for ($page = 1; $page <= $pageCount; $page++) {
            $data = $model->limit(($page - 1) * 5000, 5000)->select();
            M('customer')->addAll($data);
        }
    }
    
    /**
     * order
     */
    public function order()
    {
        //订单
        $model = M('order', '', $this->secondaryDB);
        $model->query("ALTER TABLE `order`
                       MODIFY COLUMN `phone_imei`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '手机imei号' AFTER `phone_id`,
                       MODIFY COLUMN `third_party_number`  varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `clearing_time`;");
        
        $count = $model->count();
        $pageCount = ceil($count / 5000);
        
        for ($page = 1; $page <= $pageCount; $page++) {
            $data = $model->join('o left join customer c on c.id = o.customer_id')
                    ->field('o.*, c.name as customer, c.cellphone, c.address')->limit(($page - 1) * 5000, 5000)->select();
            M('order')->addAll($data);
        }
        
        //第三方订单
        $data = M('order_partner', '', $this->secondaryDB)->field('order_id, order_number, partner, is_paid, is_confirm')->select();
        
        if ($data) {
            M('order_partner')->addAll($data);
        }
        
        //订单故障
        $data = M('order_phone_malfunction', '', $this->secondaryDB)->field('order_id, phone_malfunction_id as phomal_id')->select();
        
        if ($data) {
            M('order_phomal')->addAll($data);
        }
        
        //订单取消原因
        $data = M('order_close_reason', '', $this->secondaryDB)->select();
        
        if ($data) {
            M('order_close_reason')->addAll($data);
        }
        
        //订单日志
        $model = M('order_log', '', $this->secondaryDB);
        $count = $model->count();
        $pageCount = ceil($count / 5000);
        
        for ($page = 1; $page <= $pageCount; $page++) {
            $data = $model->limit(($page - 1) * 5000, 5000)->field('order_id, time, action')->select();
            M('order_log')->addAll($data);
        }
        
        unset($data);
    }
    
    /**
     * engineer
     */
    public function engineerInfo()
    {
        //工程师等级
        $data = M('engineer_level', '', $this->secondaryDB)->select();
        
        if ($data) {
            M('engineer_level')->addAll($data);
        }
        
        //工程师
        $data = M('engineer', '', $this->secondaryDB)->where(array('status' => 1))->select();
        
        if ($data) {
        
            foreach ($data as &$item) {
                unset($item['registration_id']);
                $item['organization_id'] = $this->org[$item['class_id']];
                $item['password'] = createPassword('123456');
            }
        
            M('engineer')->addAll($data);
        }
        
        //工程师info
        $data = M('engineer_info', '', $this->secondaryDB)
                ->join('ei left join engineer e on e.id = ei.engineer_id')
                ->where(array('e.status' => 1))->select();
        
        if ($data) {
            M('engineer_info')->addAll($data);
        }
    }
    
    /**
     * engineer
     */
    public function engineerRelated()
    {
        //工程师收益
        $model = M('engineer_divide', '', $this->secondaryDB);
        $count = $model->count();
        $pageCount = ceil($count / 5000);
        
        for ($page = 1; $page <= $pageCount; $page++) {
            $data = $model->join('ed left join engineer e on e.id = ed.engineer_id')
                    ->where(array('e.status' => 1))
                    ->field('engineer_id, order_id, order_number, order_name, 
                    divide, actual_earnings as earning, is_clear')
                    ->limit(($page - 1) * 5000, 5000)->select();
            M('engineer_divide')->addAll($data);
        }
        
        //工程师操作日志
        /*
        $model = M('engineer_action_log', '', $this->secondaryDB);
        $count = $model->count();
        $pageCount = ceil($count / 5000);
        
        for ($page = 1; $page <= $pageCount; $page++) {
            $data = $model->limit(($page - 1) * 5000, 5000)->select();
            M('engineer_action_log')->addAll($data);
        }
        */
        
        //工程师登录日志
        /*
        $model = M('engineer_login_log', '', $this->secondaryDB);
        $count = $model->count();
        $pageCount = ceil($count / 5000);
        
        for ($page = 1; $page <= $pageCount; $page++) {
            $data = $model->limit(($page - 1) * 5000, 5000)->select();
            M('engineer_login_log')->addAll($data);
        }
        */
        
        //工程师物料
        $model = M('engineer_warehouse', '', $this->secondaryDB);
        $count = $model->count();
        $pageCount = ceil($count / 5000);
        $MWarehouse = D('Admin/warehouse');
        
        for ($page = 1; $page <= $pageCount; $page++) {
            $data = $model->join('ew left join fittings f on f.id = ew.fittings_id')
                    ->join('left join engineer e on e.id = ew.engineer_id')
                    ->group('engineer_id, fittings_id')
                    ->where(array('e.status' => 1))
                    ->field('ew.engineer_id, ew.fittings_id, ew.fittings_name, sum(amount) as amount, price_engineer')
                    ->limit(($page - 1) * 5000, 5000)->select();
            
            $stocks = array();
            
            foreach ($data as $item) {
                
                for ($i = 1; $i <= $item['amount']; $i++) {
                    $stocks[] = array(
                        'number' => $MWarehouse->createNumber(),
                        'status' => 3,
                        'organization_id' => 0,
                        'engineer_id' => $item['engineer_id'],
                        'order_id' => 0,
                        'fitting_id' => $item['fittings_id'],
                        'price' => $item['price_engineer'],
                        'provider_id' => 0,
                        'create_time' => time(),
                        'consume_time' => 0,
                    );
                }
            }
            
            M('engineer_warehouse')->addAll($data);
            M('stock')->addAll($stocks);
        }
        
        //工程师物料日志
        /*
        $model = M('engineer_warehouse_log', '', $this->secondaryDB);
        $count = $model->count();
        $pageCount = ceil($count / 5000);
        
        for ($page = 1; $page <= $pageCount; $page++) {
            $data = $model->limit(($page - 1) * 5000, 5000)->select();
            
            $param = array();
        
            foreach($data as $value) {
                $param[] = array(
                    'id' => $value['id'],
                    'type' => $value['order_id'] ? 1 : 2,
                    'inout' => $value['out_put'],
                    'engineer_id' => $value['engineer_id'],
                    'order_id' => $value['order_id'],
                    'user_id' => $value['admin_id'],
                    'fittings_id' => $value['fittings_id'],
                    'amount' => $value['number'],
                    'time' => $value['time']
                );
            }
            
            M('engineer_inout')->addAll($param);
        }
        */
    }
    
    /**
     * 工程师物料库存
     */
    public function resetEngineerWarehouse()
    {
        //清除新版本上线之前的库存实体表数据
        //M('stock')->where(array('create_time' => array('lt', strtotime(date('2016-11-17 23:00:00')))))->delete();
        $map = array(
            'engineer_id' => array('eq', 270),
        );
        
        M('engineer_warehouse')->where($map)->delete();
        
        $map = array(
            'status' => 3,
            'order_id' => array('lt' , 1),
            'engineer_id' => array('eq', 270),
        );
            
        $data = M('stock')->join('left join fitting f on f.id = stock.fitting_id')
                ->field('engineer_id, fitting_id as fittings_id, f.title as fittings_name, count(*) as amount')
                ->where($map)->group('engineer_id, fitting_id')->select();
        
        M('engineer_warehouse')->addAll($data);
    }
    
    /**
     * warehouse
     */
    public function warehouse()
    {
        //物料库存(warehouse/stock)
        $model = M('warehouse', '', $this->secondaryDB);
        $count = $model->count();
        $pageCount = ceil($count / 5000);
        $MWarehouse = D('Admin/warehouse');
        
        for ($page = 1; $page <= $pageCount; $page++) {
            $data = $model->limit(($page - 1) * 5000, 5000)->join('w left join fittings f on f.id = w.fittings_id')
                    ->field('w.class_id, f.price_engineer as price, w.fittings_id as fitting_id, w.amount')
                    ->where(array('w.amount' => array('gt', 0), 'class_id' => array('neq', 2)))->select();
            
            $stocks = array();
            
            foreach ($data as &$item) {
                
                $organization_id = $this->org[$item['class_id']];
                $item['organization_id'] = $organization_id;
                
                for ($i = 1; $i <= $item['amount']; $i++) {
                    $stocks[] = array(
                        'number' => $MWarehouse->createNumber(),
                        'status' => 1,
                        'organization_id' => $organization_id,
                        'engineer_id' => 0,
                        'order_id' => 0,
                        'fitting_id' => $item['fitting_id'],
                        'price' => $item['price'],
                        'provider_id' => 0,
                        'create_time' => time(),
                        'consume_time' => 0,
                    );
                }
            }

            M('warehouse')->addAll($data);
            M('stock')->addAll($stocks);
        }
        
        //废料
        $model = M('waste_warehouse', '', $this->secondaryDB);
        $count = $model->count();
        $pageCount = ceil($count / 5000);
        
        for ($page = 1; $page <= $pageCount; $page++) {
            $data = $model->limit(($page - 1) * 5000, 5000)
                    ->where(array('amount' => array('gt', 0), 'class_id' => array('neq', 2)))
                    ->field('class_id, waste_id, amount')->select();
            
            $waste_stocks = array();
            
            foreach ($data as &$item) {
                $organization_id = $this->org[$item['class_id']];
                $item['organization_id'] = $organization_id;
                
                for ($i = 1; $i <= $item['amount']; $i++) {
                    $waste_stocks[] = array(
                        'number' => $MWarehouse->createNumber(),
                        'status' => 1,
                        'organization_id' => $organization_id,
                        'engineer_id' => 0,
                        'order_id' => 0,
                        'waste_id' => $item['waste_id'],
                        'create_time' => time(),
                    );
                }
            }
            
            M('waste_warehouse')->addAll($data);
            M('waste_stock')->addAll($waste_stocks);
        }
    }
    
    /**
     * 废料退还
     */
    public function wasteRefund()
    {
        //废料退还
        /*
        $model = M('waste_apply_log', '', $this->secondaryDB);
        $count = $model->count();
        $pageCount = ceil($count / 5000);
        
        for ($page = 1; $page <= $pageCount; $page++) {
            $data = $model->limit(($page - 1) * 5000, 5000)
                    ->field('class_id, order_id, user_id, engineer_id, wastes, status, time')->select();
        
            foreach ($data as &$item) {
                $organization_id = $this->org[$item['class_id']];
                $item['organization_id'] = $organization_id;
            }
        
            M('waste_refund')->addAll($data);
        }
        */
        
        //废料调拨
        $model = M('waste_allot_record', '', $this->secondaryDB);
        $count = $model->count();
        $pageCount = ceil($count / 5000);
        
        for ($page = 1; $page <= $pageCount; $page++) {
            $data = $model->limit(($page - 1) * 5000, 5000)
                    ->field('proposer, proposer_warehouse, type, wastes, auditor, auditor_warehouse, time, status, remark')->select();
        
            foreach ($data as &$item) {
                $wastes = json_decode($item[wastes], true);
                
                foreach ($wastes as &$v) {
                    $v['name'] = str_replace(' ', '', $v['name']);
                }
                $item['wastes'] = json_encode($wastes);
                $organization_id = intval($this->org[$item['proposer_warehouse']]);
                $item['proposer_org'] = $organization_id;
                $organization_id = intval($this->org[$item['auditor_warehouse']]);
                $item['auditor_org'] = $organization_id;
            }
        
            M('waste_allot')->addAll($data);
        }
    }

    /*
     * banner
     */
    public function banner()
    {
        $model = M('banner', '', $this->secondaryDB);
        $result = $model->select();
        foreach ($result as $key => &$value) {
            $value['sort'] = $value['order'];
            unset($value['order']);
        }

        M('banner')->addAll($result);
    }

    /*
     * post
     */
    public function post()
    {
        $model = M('post', '', $this->secondaryDB);
        $result = $model->select();
        M('post')->addAll($result);
    }

    /*
    * menu
    */
    public function menu()
    {
        $model = M('menu', '', $this->secondaryDB);
        $result = $model->select();
        M('menu')->addAll($result);
    }

    /*
    * share
    */
    public function share()
    {
        $model = M('share', '', $this->secondaryDB);
        $result = $model->select();
        M('share')->addAll($result);
    }

    /*
    * share_user
    */
    public function share_user()
    {
        $model = M('share_user', '', $this->secondaryDB);
        $result = $model->select();
        M('share_user')->addAll($result);
    }

    /*
     * 废料退还结构修改脚本
     */
    public function waste_refund()
    {
        $rst = M('waste_refund')->select();

        foreach ($rst as $k => &$value) {

            if ($value['id'] > 95834) {
                continue;
            }

            $wastes  = json_decode($value['wastes'], true);

            if (!$wastes) {
                continue;
            }

            $phone = M('order')->where(array('id' => $value['order_id']))->field('phone_id,phone_name')->find();

            foreach ($wastes as &$v) {
                $v['waste_id'] = $v['id'];
                $v['phone_id'] = (int)$phone['phone_id'];
                $v['phone'] = $phone['phone_name'] ? $phone['phone_name'] : '';
                unset($v['id']);
            }

            $rst[$k]['wastes'] = json_encode($wastes);

            M('waste_refund')->where(array('id' => $value['id']))->save($rst[$k]);

        }

    }

    /*
     * 废料申请结构修改
     */
    public function waste_allot()
    {
        $rst = M('waste_allot')->select();

        foreach ($rst as $key => &$value) {
            $wastes  = json_decode($value['wastes'], true);

                foreach ($wastes as &$v) {
                    $v['phone_id'] = $v['id'];
                    $v['phone'] = M('phone')->where(array('id' => $v['id']))->getField('alias');
                    unset($v['id']);
                }

                $rst[$key]['wastes'] = json_encode($wastes);
                //pr($rst[$key]['wastes']);
                M('waste_allot')->where(array('id' => $value['id']))->save($rst[$key]);

        }

    }
}
