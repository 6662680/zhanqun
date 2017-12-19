<?php
/**
 * Created by PhpStorm.
 * User: zhujianping
 * Date: 2017/5/12 0012
 * Time: 上午 11:51
 */

namespace V2\Transformer;
use V2\Transformer\Transformer;

class EngineerTransformer extends Transformer
{
    public function transform($item)
    {
        return ['orderId'=>$item['id']];
    }



}