<?php
 
// +------------------------------------------------------------------------------------------ 
// | Author: longDD <longdd_love@163.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description:  Dates: 2016-08-05
// +------------------------------------------------------------------------------------------ 

namespace Admin\Controller;

use Admin\Controller;

class CustomerController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index(){
        $this->display();
       
    }

    /**
     * 会员列表
     *
     * @return void
     */
    public function rows()
    {

        $post = D('customer');

        $where = " where 1=1";
        if ( $keyword = I('post.keyword')) {
            $type = I('post.type');
            $where .= ' AND '. $type .' like \'%'.$keyword.'%\'';
        }
        if (I('post.status') == 1) {
            $where .= ' AND flag = 1';
        }
        if (I('post.status') == 2) {
            $where .= ' AND flag = 0';
        }


        $rst['total'] = $post->query("SELECT count(*) as total FROM `customer` $where");
        $rst['total'] = $rst['total']['0']['total'];

        $page = $this->page();
        $sql = "SELECT * FROM `customer`  $where  ORDER BY id DESC limit $page";
        $rst['rows'] = $post->query($sql);

        $this->ajaxReturn($rst);
    }

    /**
     * 会员订单
     *
     * @return void
     */
    public function getOrder(){
        $status = array(
            1 => '下单',
            2 => '派单',
            3 => '接单',
            4 => '处理中',
            5 => '结单',
            6 => '入库',
        );

//        $selectSql = 'SELECT e.name as engineer_name,o.engineer_id,e.work_number,e.cellphone,o.number as order_number,
//                      phone_name,o.color,o.malfunction_description,actual_price,p.phone_img,o.create_time
//                      FROM `order` as o
//                      LEFT JOIN `customer` as c ON o.customer_id = c.id
//                      LEFT JOIN `phone` as p ON o.phone_id = p.id
//                      LEFT JOIN `engineer` as e ON o.engineer_id = e.id WHERE c.id = '.I('post.id').'
//                      ORDER BY o.id DESC';
//        $list = M()->query($selectSql);

        $list = array(
               0 => array(
                   'order_number' => '1223333',
                   'phone_name'   => 'iPhone 6s',
                   'create_time'  => '1474425540',
                   'malfunction_description' => '摄像头 HOME键',
                   'engineer_name'=> '工程师名称',
                   'actual_price' => '295',
                   'status'       => 1,

               ),
                1 => array(
                    'order_number' => '1223333',
                    'phone_name'   => 'iPhone 6s',
                    'create_time'  => '1474425540',
                    'malfunction_description' => '摄像头 HOME键',
                    'engineer_name'=> '工程师名称',
                    'actual_price' => '295',
                    'status'       => 2,
                )
        );
        foreach($list as &$value){
            $value['status'] = $status[$value['status']];
        }

        $this->ajaxReturn($list);
    }

    /**
     * 会员评价
     *
     * @return void
     */
    public function getEvaluate(){
//
//        $sql = 'SELECT ev.id,ev.reamrk,ev.service,ev.time,o.number,en.`name` FROM `evaluate` as ev
//                    LEFT JOIN engineer as en ON ev.engineer_id = en.id
//                    LEFT JOIN `order` as o ON ev.order_id = o.id
//                    WHERE ev.customer_id = 1';

        $list = array(
            0 => array(
                'id' => '1223333',
                'reamrk'   => '我是一个留言啊SSSSSSSSSSSSSSSSSSSSSSSSSSS',
                'time'  => '1474425540',
                'service' => '1',
                'number'=> '2222',
                'name' => '张二狗',

            ),
            1 => array(
                'id' => '1223333',
                'reamrk'   => '我是一个留言啊SSSSSSSSSSSSSSSSSSSSSSSSSSS',
                'time'  => '1474425540',
                'service' => '1',
                'number'=> '2222',
                'name' => '张二狗',

            )
        );
        $this->ajaxReturn($list);

    }

    /**
     * 禁用会员
     *
     * @return void
     */

    public function disable(){
        $post = M('customer');
        if (I('post.flag') == 1){
            $flag = 0;
        } else {
            $flag = 1;
        }

        $post->find(I('post.id', 0));
        $post->flag = $flag;

        if ($post->save()){
            $this->ajaxReturn(['success'=>true]);
        } else {
            $this->ajaxReturn( ['success'=>false,'errorMsg'=>'禁用失败']);
        }
    }

}