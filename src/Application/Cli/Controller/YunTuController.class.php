<?php
/**
 * Created by PhpStorm.
 * User: zhujianping
 * Date: 2017/5/27 0027
 * Time: 下午 14:36
 */

namespace Cli\Controller;


use Think\Controller;

class YunTuController extends Controller
{


    protected $host='http://yuntuapi.amap.com';
    protected $key='0aa490086ce73cde1615486ca05fa3d4';
    protected $tableid='55fb8341e4b0cc124e14fe35';

    /**
     * 取得重复的ID并删除
     */
    public function dataUnique()
    {
        $url = $this->host.'/datamanage/data/list?key='.$this->key.'&tableid='.$this->tableid.'&keywords=&city=&limit=100&sortrule=_id:0';
        $data = json_decode(self::quickGet($url),true);
        $ids = $res = array();
        foreach ($data['datas'] as $k=>$v)
        {
            //删除地址为空的
            if(is_null($v['_province'])){
                $this->delByIds($v['_id']);
                continue;
            }
            if (!isset($res[$v['_name']])){
                $res[$v['_name']] = $v;
            }else{
                $ids[]=$v['_id'];
            }
        }
        $this->delByIds($ids);
    }

    /**
     * 根据id批量删除
     * @param $ids
     */
    public function delByIds($ids)
    {
        if(empty($ids)) return;
        $ids = is_string($ids)?$ids:implode(',',$ids);
        $url = $this->host.'/datamanage/data/delete?key='.$this->key.'&tableid='.$this->tableid.'&ids='.$ids;
        self::quickGet($url);
    }


    /**
     * 发送请求
     * @param string $url
     * @param array $headers
     * @return string
     */
    private function quickGet($url)
    {
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_TIMEOUT,15);
        if(!curl_exec($ch)) {
            $data = curl_error($ch);
        }else{
            $data = curl_multi_getcontent($ch);
        }
        curl_close($ch);
        return $data;
    }


}