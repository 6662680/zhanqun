<?php
 
// +------------------------------------------------------------------------------------------ 
// | Author: longDD <longdd_love@163.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 订单统计  Dates: 2015-08-18
// +------------------------------------------------------------------------------------------ 

namespace Api\Controller;
use Think\Controller;

class StatisticsController extends BaseController
{
    public $targer;
    private $token;

	public function Index()
    {
        $this->token = '!@3456';
        $this->targer = 2000;
        //验证token
        $this->validation($_GET['token']);

        $today = strtotime('today');

        $startTime = strtotime(date('Y-m-01')); //月初
        $lastMonthEnd = strtotime(date('Y-m-d')) + 24 * 60 * 60 - 1;

        $count = array();
        $count_eng = array();
        $temporary = array();
        $area = array('北京', '杭州', '上海', '广州', '深圳', '苏州', '南京', '武汉', '成都', '重庆', '无锡', '郑州', '天津', '厦门', '西安');

       foreach($area as $k=>$v){
            $sql = 'select count(*) as count from `order` as o left join customer as c on o.customer_id=c.id
                    where c.address like \'%'.$v.'%\' and o.create_time >= '.$today;
            $temporary = M()->query($sql);
            $count[$k] = $temporary[0];
            $count[$k]['area'] = $v;


        }
        $sql = 'SELECT e.name, wc.name as area, COUNT(o.id) AS num FROM engineer e
                LEFT JOIN organization wc ON e.organization_id = wc.id
                LEFT JOIN (SELECT id, engineer_id  FROM `order` WHERE status = 6
                AND clearing_time >= '.$startTime.'
                AND clearing_time <= '.$lastMonthEnd.') o ON e.id = o.engineer_id
                WHERE e.name NOT LIKE \'%芯片%\' GROUP BY e.id ORDER BY num DESC LIMIT 0 , 5';

//        $sql = 'select e.name, wc.filiale_name, count(o.id) as num
//                    from engineer e
//                    left join warehouse_class wc on e.class_id = wc.id
//                    left join `order` o on e.id = o.engineer_id and o.status=6 and e.name not like \'%芯片%\' and o.clearing_time >= '.$startTime.' and o.clearing_time <= '.$lastMonthEnd.'
//                    group by e.id order by num desc limit 0,5 ';

        $rst = M()->query($sql);

        foreach($rst as &$value){
            $value['count'] = $value['num'];
            unset($value['filiale_name']);
        }

        //全部
        $sql = "select count(*) as count from `order` as o left join customer as c on o.customer_id=c.id
                where o.create_time >= {$today} and o.phone_name not like '%一元%'";
        $count_all = M()->query($sql);

        header("Access-Control-Allow-Origin:*");
        echo json_encode(array('status' => true, 'data' => $count, 'list'=>$rst,'area'=>$area,'targetToolNum'=>['toolNum'=>$count_all,'target'=>$this->targer]));
	}



    private function rank($data){
        foreach($data as $key => &$value){
            $count[$key] = $value['count'];
            //$value['name']=preg_replace(array("/\（.*\）/","/\(.*\)/"),'', $value['name']);
        }
        array_multisort($count,SORT_DESC,$data);
        return $data;
    }


    private function validation($token){
        if($token!=$this->token){
            die('error');
        }
    }

}