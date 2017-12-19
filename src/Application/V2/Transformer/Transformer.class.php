<?php
/**
 * Created by PhpStorm.
 * User: zhujianping
 * Date: 2017/5/12 0012
 * Time: 上午 11:56
 */

namespace V2\Transformer;


abstract class Transformer
{
    public function collection($items)
    {
        return array_map([$this,'transform'],$items);
    }

    public function makeTransform($data)
    {
        if(!empty($data) && is_array(current($data))){
            return $this->collection($data);
        }else{
            return $this->transform($data);
        }
    }

    abstract function transform($item);
}