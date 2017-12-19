<?php

// +------------------------------------------------------------------------------------------ 
// | Author: liyang <664577655@qq.com>
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 订单验证模型 Dates: 2016-08-4
// +------------------------------------------------------------------------------------------

namespace V2\Model;
//use Api\Extend\Vendor;
//use Think\Model;
class AddressModel
{

    /*
     * 获取开放的省市区
     *
     * */
    public function getAddress(){
        /** 开放的城市 */
        $openCity = '110100, 310100, 440100, 440300, 330100, 510100, 320500, 420100, 320100, 500100';
        /** 开放的区县 */
        $openCounty = array(
            '110100' => '110114, 110105, 110115, 110101, 110106, 110108, 110102, 110107',
            '310100' => '310101, 310104, 310105, 310106, 310107, 310108, 310109, 310110, 310112, 310113, 310114, 310115, 310116, 310117, 310118, 310120, 310230',
            '440100' => '440104, 440103, 440105, 440106, 440111, 440112, 440113, 440116, 440114, 440115, 440183, 440184',
            '440300' => '440306, 440304, 440307, 440303, 440305, 440308',
            '330100' => '330106, 330105, 330104, 330102, 330103, 330110, 330108, 330109',
            '510100' => '510104, 510105, 510106, 510107, 510108, 510116, 510122, 510112, 510114, 510115, 510124, 510117',
            '320500' => '320503, 320506, 320505, 320507, 320508, 320509, 320584, 320583',
            '420100' => '420103, 420105, 420106, 420111, 420102, 420104',
            '320100' => '320102, 320106, 320105, 320103, 320104, 320107, 320111, 320113, 320114, 320115',
            '500100' => '500103, 500104, 500105, 500106, 500107, 500108, 500112',
            //'320200' => '320205, 320202, 320203, 320204, 320211, 320206, 320207',
        );
        $city = array(); //省
        $county = array(); //市
        $area = array(); //区
        $openCityStr = implode(',', $openCounty);
        $sql = "select id, pid,  name from `address` where id in ({$openCity}) or (pid in ({$openCity}) and id in ({$openCityStr})) or id in (select pid from `address` where id in ({$openCity}))";

        $address = M()->query($sql);

        $openCity = explode(',',$openCity);


        foreach ($address as $value) {
            //判断是否省级
            if ($value['pid'] == 0){
                $city[$value['id']] = $value;
            } else {
                $county[$value['id']]  = $value;
            }

            //市关联省
            foreach($openCity as $v){
                if ($v == $value['pid']){
                    $area[]= $value;
                }

            }
        }


        foreach($city as $key => &$value){
            $num = -1;
            foreach($county as $k =>$v){
                //提取市区

                if($v['pid'] == $value['id']){
                    $num++;
                    $value['city'][$num] = $v;

                    foreach($area as $r=>$s){
                        if ($s['pid']==$v['id']){
                            $value['city'][$num]['area'][] = $s ;
                        }
                    }
                }
            }
        }
        return $city;
    }

    /*
     * 获取所有寄修地址
     * */
    public function mailAddress(){
        $result = M('organization')->where(array('type' => 1))->select();
        return $result;
    }

    /*
     *ID获取寄修地址
     * */
    public function idMailAddress($id){
        $result = M('organization')->find($id);
        return $result;
    }

    /*
     * ID获取寄修的区县
     * */
    public function idAddress($id){
        $result = M('address')->find($id);
        return $result['name'];
    }

    public function getDetailAddress($province,$city,$area){
        $name= $this->idAddress($province);
        $name.= $this->idAddress($city);
        $name.= $this->idAddress($area);
        return $name;
    }
}