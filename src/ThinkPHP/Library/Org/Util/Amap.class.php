<?php

// +------------------------------------------------------------------------------------------ 
// | Author: longDD <longdd_love@163.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 高德地图 Dates: 2015-09-22
// +------------------------------------------------------------------------------------------

namespace Org\Util;

class Amap
{
    /** 接口秘钥 */
    public $key = '0aa490086ce73cde1615486ca05fa3d4';
    /** 云储存在API post */
    public $yuntuapi = 'http://yuntuapi.amap.com/datamanage/table/create';
    /** 附近API get */
    public $nearbyapi = 'http://yuntuapi.amap.com/nearby/around';
    /** 地理编码API get */
    public $geoapi = 'http://restapi.amap.com/v3/geocode/geo';
    /** 云检索API get **/
    public $searchapi = 'http://yuntuapi.amap.com/datasearch/local';
    /** 云图id **/
    public $tableid = '55fb8341e4b0cc124e14fe35';
    /** 删除数据（单条/批量）Api post **/
    public $removeapi = 'http://yuntuapi.amap.com/datamanage/data/delete';

    /**
     * 地理编码
     *
     * @param string $address 地理位置
     * @return string 地理坐标
     */
    public function geo($address)
    {

        $parameter = array();
        $parameter['key'] = $this->key;
        $parameter['address'] = $address;
        $parameter['output'] = 'json';

        $url = $this->geoapi . '?';

        if (!empty($parameter)) {
            foreach ($parameter as $key => $value) {
                $url .= $key . '=' . urlencode($value) . '&';
            }
        }

        $url = trim($url, '&');
        $rst = $this->curlGet($url);
        $rst = json_decode($rst, true);

        if ($rst['status'] && $rst['count'] > 0) {
            return $rst['geocodes'][0]['location'];
        } else {
            return false;
        }
    }

    /**
     * 范围筛选
     *
     * @return void
     */
    public function around($center, $radius)
    {
        $parameter = array();
        $parameter['key'] = $this->key;
        $parameter['center'] = $center;
        $parameter['radius'] = $radius;
        $parameter['limit'] = 30;
        $parameter['searchtype'] = 1;
        $parameter['radius'] = $radius;

        $url = $this->nearbyapi . '?';

        if (!empty($parameter)) {
            foreach ($parameter as $key => $value) {
                $url .= $key . '=' . urlencode($value) . '&';
            }
        }

        $url = trim($url, '&');
        $rst = $this->curlGet($url);
        $rst = json_decode($rst, true);

        if ($rst['status']) {
            return $rst;
        } else {
            return false;
        }
    }

    /**
     * 生成签名
     *
     * @param array $para 参数
     * @return string 签名
     */
    public function sign($para)
    {

    }
    
    /**
     * 搜索
     */
    public function search($param) 
    {
        $param['key'] = $this->key;
        $param['tableid'] = $this->tableid;
        
        if (!isset($param['keywords'])) {
            $param['keywords'] = '';
        }
        
        if (!isset($param['city'])) {
            $param['city'] = '全国';
        }
        
        return $this->curlGet($this->searchapi . '?' . http_build_query($param));
    }
    
    /**
     * 删除用户上传的云图地址
     */
    public function remove($param)
    {
        if (!$param['ids']) {
            return false;
        }
        
        $param['key'] = $this->key;
        $param['tableid'] = $this->tableid;
        
        return $this->curlPost($this->removeapi, $param);
        
    }

    /**
     * curl get方法
     *
     * @param string $url 请求地址
     * @return unknown
     */
    public function curlGet($url)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, 0 ); // 过滤HTTP头
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
        $responseText = curl_exec($curl);
        curl_close($curl);
        
        return $responseText;
    }

    /**
     * curl post方法
     *
     * @param string $url 请求地址
     * @param string $url 请求地址
     * @return unknown
     */
    public function curlPost($url, $para)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, 0 ); // 过滤HTTP头
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
        curl_setopt($curl, CURLOPT_POST, true); // post传输数据
        curl_setopt($curl, CURLOPT_POSTFIELDS, $para);// post传输数据
        $responseText = curl_exec($curl);
        curl_close($curl);
        
        return $responseText;
    }
}