<?php
namespace Api\Controller;
use Api\Model\CheckModel;
use Api\Model\RequestModel;
use Think\Controller;
/**
 * 地址控制器
 * author :liyang
 * time : 2016-8-22
 */

class AddressController extends BaseController {

    /**
     * 获取上门地址
     */
    public function doorAddress(){
        $model = D('Address');
        $door_address = $model->getAddress();
        $this->_callBack($door_address);
    }

    /**
     * 获取寄修地址
     */
    public function mailAddress(){
      
        $model = D('Address');
        $mail_address = $model->mailAddress();

        $this->_callBack($mail_address);
    }

    /**
     * 地址转code
     */
    public function getAddressCode()
    {
        $rst = array();
        $post = I('post.');

        $str = strstr($post['address'], "省", true);
        $map['level'] = array('eq', 1);
        $map['name'] = array('eq', $str.= '省');
        $rst['province'] = M('address')->where($map)->getField('id');

        preg_match("/省(.*?)市(.*?)区/",$post['address'],$str);
        $map['level'] =  $map['level'] = array('eq', 2);
        $map['pid'] = array('eq', $rst['province']);
        $map['name'] = array('eq', $str[1].= '市');
        $rst['city'] = M('address')->where($map)->getField('id');


        $map['pid'] = array('eq', $rst['city']);
        $map['level'] = array('eq', 3);
        $map['name'] = array('eq', $str[2].= '区');
        $rst['county'] = M('address')->where($map)->getField('id');

        $this->_callBack($rst);


    }
}