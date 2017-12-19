<?php
// +------------------------------------------------------------------------------------------
// | Author: longdd <xujialong@weadoc.com>
// +------------------------------------------------------------------------------------------
// | There is no true,no evil,no light,there is only power.
// +------------------------------------------------------------------------------------------
// | Description: app改版过渡接口(之后迁移到V2中) Dates: 2017-06-28
// +------------------------------------------------------------------------------------------
namespace Api\Controller;

use Api\Controller;

class TransitionController extends BaseController
{
    // 缓存key前缀
    public $prefix = 'transition_';

    /**
     * 构造函数
     *
     * @return void
     */
    public function __construct() 
    {
        parent::__construct();

        // 初始化redis
        S(
            array(
                'type' => 'redis',
                'host' => C('REDIS_HOST'),
                'port' => C('REDIS_PORT'),
                'expire' => 3600,
            )
        );
    }

    /**
     * 获取当前工程师未完成的订单
     *
     * @return void
     */
    public function orderMap()
    {
        $engineerId = I('get.engineerId');

        if (empty($engineerId)) {
            $rst = array();
            $rst['status'] = 0;
            $rst['errorMsg'] = '请输入工程师ID';
            $this->ajaxReturn($rst);
        }

        $map = array();
        $map['engineer_id'] = $engineerId;
        $map['status'] = array('in', array(3, 4));
        $items = M('order')->field('id, number, customer, address')->where($map)->order('create_time desc')->select();

        $rst = array();
        $rst['status'] = 1;
        $rst['data'] = $items;
        $this->ajaxReturn($rst);
    }

    /**
     * 获取当前工程师的订单
     *
     * @return void
     */
    public function orders()
    {
        $engineerId = I('get.engineerId');

        if (empty($engineerId)) {
            $rst = array();
            $rst['status'] = 0;
            $rst['errorMsg'] = '请输入工程师ID';
            $this->ajaxReturn($rst);
        }

        if (I('get.status') == 4) {
            $status = 4;
        } else {
            $status = 3;
        }

        $map = array();
        $map['o.engineer_id'] = $engineerId;
        $map['o.status'] = $status;
        
        $items = M('order')->join('o left join order_phomal opm on opm.order_id = o.id')
            ->join('left join phone_malfunction pm ON opm.phomal_id = pm.id')
            ->field('o.id, o.number, o.type, o.cellphone, o.create_time as time, o.actual_price as price, o.status, o.malfunction_description, o.phone_name, o.customer, o.address, group_concat(pm.malfunction) as malfunctions')
            ->where($map)->group('o.id')->order('o.create_time desc')->select();

        foreach ($items as &$order) {
            $order['time'] = date('Y-m-d H:i:s', $order['time']);
            
            if (!$order['malfunctions']) {
                $order['malfunctions'] = $order['malfunction_description'];
            }
        }

        $rst = array();
        $rst['status'] = 1;
        $rst['data'] = $items;
        $this->ajaxReturn($rst);
    }

    /**
     * 获取品牌信息
     *
     * @return void
     */
    public function brands()
    {
        $brands = S($this->prefix . 'brands');

        if ($brands) {
            $rst = array();
            $rst['status'] = 1;
            $rst['data'] = $brands;
            $this->ajaxReturn($rst);
        } else {
            $items = M('goods_brand')->field('id, name')->select();
            S($this->prefix . 'brands', $items, 3600);
            
            $rst = array();
            $rst['status'] = 1;
            $rst['data'] = $items;
            $this->ajaxReturn($rst);
        }
    }

    /**
     * 获取机型信息
     *
     * @return void
     */
    public function phones()
    {
        $brandId = I('get.brandId');

        if (empty($brandId)) {
            $rst = array();
            $rst['status'] = 0;
            $rst['errorMsg'] = '请输入品牌ID';
            $this->ajaxReturn($rst);
        }

        $phones = S($this->prefix . 'phones_' . $brandId);

        if ($phones) {
            $rst = array();
            $rst['status'] = 1;
            $rst['data'] = $phones;
            $this->ajaxReturn($rst);
        } else {
            $map = array();
            $map['brand_id'] = $brandId;
            $items = M('phone')->where($map)->field('id, alias')->order('alias asc')->select();
            S($this->prefix . 'phones_' . $brandId, $items, 3600);
            
            $rst = array();
            $rst['status'] = 1;
            $rst['data'] = $items;
            $this->ajaxReturn($rst);
        }
    }

    /**
     * 获取配件所属大类
     *
     * @return void
     */
    public function categorys()
    {
        $categorys = S($this->prefix . 'categorys');

        if ($categorys) {
            $rst = array();
            $rst['status'] = 1;
            $rst['data'] = $categorys;
            $this->ajaxReturn($rst);
        } else {
            $items = M('fitting_category')->field('id, name')->select();
            S($this->prefix . 'categorys', $items, 3600);
            
            $rst = array();
            $rst['status'] = 1;
            $rst['data'] = $items;
            $this->ajaxReturn($rst);
        }
    }

    /**
     * 获取机型配件
     *
     * @return void
     */
    public function fittings()
    {
        $phoneId = I('get.phoneId');

        if (empty($phoneId)) {
            $rst = array();
            $rst['status'] = 0;
            $rst['errorMsg'] = '请输入机型ID';
            $this->ajaxReturn($rst);
        }

        $engineerId = I('get.engineerId');

        if (empty($engineerId)) {
            $rst = array();
            $rst['status'] = 0;
            $rst['errorMsg'] = '请输入工程师ID';
            $this->ajaxReturn($rst);
        }

        $map = array();
        $map['id'] = $engineerId;
        $map['status'] = 1;

        $engineer = M('engineer')->where($map)->find();

        if ($engineer === false) {
            $rst = array();
            $rst['status'] = 0;
            $rst['errorMsg'] = '未查询到工程师信息';
            $this->ajaxReturn($rst);
        }

        if (empty($engineer['organization_id'])) {
            $rst = array();
            $rst['status'] = 0;
            $rst['errorMsg'] = '未查询到工程师所属组织';
            $this->ajaxReturn($rst);
        }

        $map = array();
        $map['pf.phone_id'] = $phoneId;
        $map['w.organization_id'] = $engineer['organization_id'];

        $categoryId = I('get.categoryId');

        if (!empty($categoryId)) {
            $map['f.category_id'] = $categoryId;
        }

        $keyword = I('get.keyword');

        if (!empty($keyword)) {
            $where = array();
            $where['f.number']  = array('like', '%' . $keyword . '%');
            $where['f.title']  = array('like', '%' . $keyword . '%');
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }

        // pr($map);exit;

        $items = M('fitting')->join('f left join phone_fitting pf on f.id = pf.fitting_id')
            ->join('left join warehouse w on f.id = w.fitting_id')
            ->where($map)
            ->field('f.id, f.number, f.title, f.price, w.amount')
            ->order('title asc')->select();

        $rst = array();
        $rst['status'] = 1;
        $rst['data'] = $items;
        $this->ajaxReturn($rst);
    }

    /**
     * 申请额度
     *
     * @return void
     */
    public function quota()
    {
        $engineerId = I('get.engineerId');

        if (empty($engineerId)) {
            $rst = array();
            $rst['status'] = 0;
            $rst['errorMsg'] = '请输入工程师ID';
            $this->ajaxReturn($rst);
        }

        $map = array();
        $map['e.id'] = $engineerId;
        $map['e.status'] = 1;
        $engineer = M('engineer')->join('e left join engineer_level el on e.level = el.id')->where($map)->field('e.id, el.quota')->find();

        if ($engineer === false) {
            $rst = array();
            $rst['status'] = 0;
            $rst['errorMsg'] = '工程师不存在';
            $this->ajaxReturn($rst);
        }

        // 物料额度
        $quota = $engineer['quota'];

        //身上持有物料的价值
        $ownedWorth = M('engineer_warehouse')->join('ew left join fitting f on ew.fittings_id = f.id')
            ->where(array('engineer_id' => $engineerId))->sum("ew.amount * f.price");

        if (!$ownedWorth) {
            $ownedWorth = 0;
        }

        //申请，并且审核通过的物料的价值
        $map = array();
        $map['engineer_id'] = $engineerId;
        $map['status'] = array('in', array(0, 1));
        $map['type'] = 1;
        $applyWorth = M('apply')->where($map)->sum('worth');

        if (!$applyWorth) {
            $applyWorth = 0;
        }

        /** 未退回废料订单物料价值 */
        $map = array();
        $map['wr.status'] = 0;
        $map['wr.engineer_id'] = $engineerId;
        $wasteLockWorth = M('waste_refund')->join('wr left join `engineer_inout` ei on wr.order_id=ei.order_id left join `fitting` f on ei.fittings_id=f.id')
            ->where($map)->sum("ei.amount * f.price");
        $wasteLockWorth = round($wasteLockWorth);

        if (!$wasteLockWorth) {
            $wasteLockWorth = 0;
        }
        
        /** 剩余额度 */
        $remainWorth = round($quota - $ownedWorth - $applyWorth - $wasteLockWorth);
        
        if ($remainWorth < 0) {
            $remainWorth = 0;
        }

        $data = array();
        $data['worth'] = $remainWorth;

        $rst = array();
        $rst['status'] = 1;
        $rst['data'] = $data;
        $this->ajaxReturn($rst);
    }

    /**
     * 工程师收益列表
     *
     * @return void
     */
    public function divide()
    {
        $engineerId = I('get.engineerId');

        if (empty($engineerId)) {
            $rst = array();
            $rst['status'] = 0;
            $rst['errorMsg'] = '请输入工程师ID';
            $this->ajaxReturn($rst);
        }

        $map = array();
        $map['ed.engineer_id'] = $engineerId;
        $map['o.status'] = 6;

        $startTime = I('get.startTime');

        if (!empty($startTime)) {
            $map['o.end_time'] = array('egt', strtotime($startTime));
        }

        $endTime = I('get.endTime');

        if (!empty($endTime)) {
            $map['o.end_time'] = array('elt', strtotime($endTime));
        }

        $page = I('get.page');

        if (!empty($page)) {
            $pageStart = intval($page) * 15;
        } else {
            $pageStart = 0;
        }

        $limit = (string)$pageStart . ', 15';
        
        $items = M('engineer_divide')
            ->join('ed left join `order` o on ed.order_id = o.id')
            ->join('left join order_phomal opm on opm.order_id = o.id')
            ->join('left join phone_malfunction pm on opm.phomal_id = pm.id')
            ->join('left join malfunction m on pm.malfunction_id = m.id')
            ->where($map)->field('ed.id, o.end_time, o.number, o.phone_name, group_concat(m.name) as malfunctions, o.actual_price, ed.earning')
            ->group('o.id')
            ->order('ed.id desc')
            ->limit($limit)
            ->select();

        $rst = array();
        $rst['status'] = 1;
        $rst['data'] = $items;
        $this->ajaxReturn($rst);
    }

    /**
     * 工程师收益详情
     *
     * @return void
     */
    public function divideDetail()
    {
        $engineerId = I('get.engineerId');

        if (empty($engineerId)) {
            $rst = array();
            $rst['status'] = 0;
            $rst['errorMsg'] = '请输入工程师ID';
            $this->ajaxReturn($rst);
        }

        $edId = I('get.edId');

        if (empty($edId)) {
            $rst = array();
            $rst['status'] = 0;
            $rst['errorMsg'] = '请输入收益记录ID';
            $this->ajaxReturn($rst);
        }

        $map = array();
        $map['ed.id'] = $edId;
        $map['ed.engineer_id'] = $engineerId;
        $map['o.status'] = 6;
        
        $item = M('engineer_divide')
            ->join('ed left join `order` o on ed.order_id = o.id')
            ->join('left join order_phomal opm on opm.order_id = o.id')
            ->join('left join phone_malfunction pm on opm.phomal_id = pm.id')
            ->join('left join malfunction m on pm.malfunction_id = m.id')
            ->where($map)->field('ed.id, o.end_time, o.number, o.phone_name, group_concat(m.name) as malfunctions, o.actual_price, ed.earning')->find();

        $rst = array();
        $rst['status'] = 1;
        $rst['data'] = $item;
        $this->ajaxReturn($rst);
    }

    /**
     * 工程师反馈
     *
     * @return void
     */
    public function feedback()
    {
        $engineerId = I('post.engineerId');

        if (empty($engineerId)) {
            $rst = array();
            $rst['status'] = 0;
            $rst['errorMsg'] = '请输入工程师ID';
            $this->ajaxReturn($rst);
        }

        $content = I('post.content');

        if (empty($content)) {
            $rst = array();
            $rst['status'] = 0;
            $rst['errorMsg'] = '请输入反馈内容';
            $this->ajaxReturn($rst);
        }

        $data = array();
        $data['engineerId'] = $engineerId;
        $data['content'] = $content;
        $data['create_time'] = time();

        $rst = array();
            
        if (M('engineer_feedback')->add($data) === false) {
            $rst['status'] = 0;
            $rst['errorMsg'] = '添加失败';
            $this->ajaxReturn($rst);
        } else {
            $rst['status'] = 1;
            $this->ajaxReturn($rst);
        }
    }
}
