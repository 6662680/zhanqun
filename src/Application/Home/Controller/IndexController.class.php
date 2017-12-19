<?php
namespace Home\Controller;

use Think\Controller;

class IndexController extends BaseController 
{

    public function index(){
        $this->display();
    }

    public function user_ajax()
    {
        $map = array();
        $map['user'] = I('post.username');
        $map['password'] = md5(I('post.password'));

        if (($user = D('shareUser')->where($map)->find()) !== null ) {

            $_SESSION['share_username'] = $user['user'];
            $_SESSION['share_password'] = $user['password'];
            $_SESSION['share_userid'] = $user['id'];
            $_SESSION['share_type'] = $user['type'];

            $this->user();
        } else {
            $this->error('密码错误');
        }
    }

    /**
     * 退出
     *
     * @return void
     */
    public function logout()
    {
        session_start();
        session_destroy();
        $this->redirect('index/index');
    }


    /**
    * 个人中心
    */
    public function user()
    {

        $this->is_user();
        $user['id'] = $_SESSION['share_userid'];
        $y = date("Y");
        $m = date("m");
        $d = date("d");
        $day_start = mktime(0,0,0,$m,$d,$y);
        $day_end= mktime(23,59,59,$m,$d,$y);
        $sql = 'SELECT s.*,o.actual_price , o.status as order_status FROM share s LEFT JOIN  `order` o ON s.order_id = o.id WHERE (s.start_time<' . $day_end . ' AND s.start_time>' . $day_start .' OR s.end_time<' . $day_end . ' AND s.end_time>' . $day_start .') AND user_id = '. $user['id'];
        $list=M()->query($sql);

        $dayPrice = 0;
        $finish = 0;

        foreach ($list as $key => $value) {
            if($value['order_status'] == '6') {
               $finish++;
               $dayPrice += $value['actual_price'] * $value['ratio'] / 100;
            }
        }

        $this->assign('dayOrderNum',count($list));
        $this->assign('dayFinishOrderNum',$finish);

        $month_start=strtotime(date('Y-m'));
        $sql = 'SELECT s.*,o.actual_price , o.status as order_status FROM share s LEFT JOIN  `order` o ON s.order_id = o.id WHERE (s.start_time>' . $month_start .' OR s.end_time>'. $month_start.') AND user_id = '. $user['id'];

        $list=M()->query($sql);
        $monthPrice = 0;
        $finish = 0;
        foreach ( $list as $key => $value) {

           if($value['order_status'] == '6') {
               $finish++;
               $monthPrice += $value['actual_price'] * $value['ratio'] / 100;
           }
        }

        $this->assign('monthOrderNum',count($list));
        $this->assign('monthFinishOrderNum',$finish);

        $sql = 'SELECT s.*,o.actual_price , o.status as order_status FROM share s LEFT JOIN  `order` o ON s.order_id = o.id WHERE user_id = '. $user['id'];
        $list=M()->query($sql);
        $allPrice=0;
        $finish = 0;

        $chart = array();
        for ($i = 0 ;$i < 30;$i ++) {
            $chart[$i]['theDayStart'] = mktime(0,0,0,$m, $d - $i ,$y);
            $chart[$i]['theDayEnd'] = mktime(23,59,59,$m, $d - $i ,$y);
            $chart[$i]['money'] = 0;
            $chart[$i]['orderNum'] = 0;
            $chart[$i]['finishOrderNum'] = 0;
        }

        foreach ( $list as $key => $value) {
            foreach ($chart as $Ckey => $Cvalue) {

                if ($value['end_time'] < $Cvalue['theDayEnd'] && $value['end_time'] > $Cvalue['theDayStart']) {
                    $chart[$Ckey]['orderNum']++;

                    if ($value['order_status'] == '6') {
                        $chart[$Ckey]['finishOrderNum']++;
                        $chart[$Ckey]['money'] += $value['actual_price'] * $value['ratio'] / 10000;
                    }
                }
            }

            if ($value['order_status'] == '6') {
               $finish++;
               $allPrice += $value['actual_price'] * $value['ratio'] / 100 ;
            }
        }

        $this->assign('chart',$chart);
        $this->assign('allOrderNum',count($list));
        $this->assign('allFinishOrderNum',$finish);
        $this->assign('allPrice',$allPrice);
        $this->assign('monthPrice',$monthPrice);
        $this->assign('dayPrice',$dayPrice);
        $this->display('user');
    }

    /**
     * 订单详情页面
     *
     * @return void
     */
    public function order()
    {
        // 机型
        $map = array();

        $phones = D('phone')->where($map)->field('id,name')->select();

        $this->assign('phones', $phones);

        $get = I('get.');
        $this->assign('get', $get);

        $map = array();
        $map['order.status'] = array('neq', -1);

        if (!empty($get['start_time']) && empty($get['end_time'])) {
            $map['order.create_time'] = array('EGT', strtotime($get['start_time']));
        } else if(!empty($get['end_time']) && empty($get['start_time'])) {
            $map['order.create_time'] = array('ELT', strtotime($get['end_time']) + 24 * 60 * 60 - 1);
        } else if(!empty($get['start_time']) && !empty($get['end_time'])) {
            $map['order.create_time'] = array(array('EGT', strtotime($get['start_time'])), array('ELT', strtotime($get['end_time']) + 24 * 60 * 60 - 1));
        }

        if (!empty($get['status'])) {
            $map['order.status'] = 6;
        }

        if (!empty($get['category'])) {
            $map['order.category'] = $get['category'];
        }

        if (!empty($get['keyword'])) {
            $like['order.id'] = array('eq', trim($get['keyword']));
            $like['order.number'] = array('LIKE', '%' . trim($get['keyword']) . '%');
            $like['customer.cellphone'] = array('LIKE', '%' . trim($get['keyword']) . '%');
            $like['customer.name'] = array('LIKE', '%' . trim($get['keyword']) . '%');
            $like['_logic'] = 'or';
            $map['_complex'] = $like;
        }

        /** 分享 */
        $map['share.user_id'] = $_SESSION['share_userid'];
        $join = 'customer on customer.id = order.customer_id';
        $joinOrder = '`order` on order.id = share.order_id';
        $count = D('share')->join($joinOrder, 'LEFT')->join($join, 'LEFT')->where($map)->count();

        $Page = new \Think\Page($count, 50);
        $page = $Page->show();
        $list = D('share')->join($joinOrder, 'LEFT')
                ->join($join, 'LEFT')
                ->join('left join phone p on order.phone_id = p.id')
                ->where($map)->field('`order`.*, customer.name as cname, customer.cellphone as ccellphone, p.alias')
                ->limit($Page->firstRow.','.$Page->listRows)
                ->order('id desc')
                ->select();

        if (!empty($list)) {
            foreach ($list as $key => &$value) {
                $order_id = $value['id'];
                $sql = "select opm.phomal_id as id, m.name as name
                        from order_phomal as opm
                        left join phone_malfunction as pm on opm.phomal_id = pm.id
                        left join malfunction as m on pm.malfunction_id = m.id
                        where opm.order_id = {$order_id}";

                $list[$key]['malfunctions'] = D('')->query($sql);
            }
        }

        $this->assign('shareType', $_SESSION['share_type']);
        $this->assign('list',$list);
        $this->assign('page',$page);
        $this->display();
    }

    /**
     * 注册
     */
    public function register()
    {
        if($_SESSION['phoneNumber'] == I('post.user')) {

            $map=array();
            $map['user'] = I('post.user');

            $model = D('shareUser');

            if( $model -> where($map) -> find()  === null){
                if (!$model->create()) {
                    $result['status'] = 0;
                    $result['msg'] = $model->getError();
                } else {

                    if ($selfid = $model->add()) {
                        $result['status'] = 1;
                        $result['msg'] = '注册成功';
                        $_SESSION['share_username'] = I('post.user');
                        $_SESSION['share_password'] = md5(I('post.password'));
                        $_SESSION['share_userid'] = $selfid;
                        $_SESSION['share_type'] = 1;


                    } else {
                        $result['status'] = 0;
                        $result['msg'] = '注册失败';
                    }
                }
            }else{
                $result['status'] = 0;
                $result['msg'] = '该手机号码已经注册！';
            }


        } else{
            $result['status'] = 0;
            $result['msg'] = '手机验证码未通过';
        }

        echo json_encode($result);
    }


    /**
     * 个人信息
     */
    public function userinfo()
    {

        $user = M("share_user")->where(array('id' => $_SESSION['share_userid']))->find();

        $this->assign('user', $user);

        $this->display();
    }

    /**
     * 修改密码
     */
    public function editorpwd(){
        $get = I('get.');

        $model = M('share_user');
        $model->find($get['id']);
        $model->password = md5($get['password']);

        if ($model->save()) {
            echo true;
        } else {
            echo false;
        }

    }

}