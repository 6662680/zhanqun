<?php
/**
 * Created by PhpStorm.
 * User: zhujianping
 * Date: 2017/5/12 0012
 * Time: 上午 11:51
 */

namespace V2\Transformer;
use V2\Transformer\Transformer;

class PhoneTransformer extends Transformer
{
    public function transform($item)
    {
        return;
    }

    public function makeEasyImg($data)
    {
        foreach ($data as $j => &$s) {

            if ($s['easy_function_img']) {
                $s['easy_function_img'] = 'http://' . $_SERVER["SERVER_NAME"] . $s['easy_function_img'];
            }

            if ($s['easy_function_img_click']) {
                $s['easy_function_img_click'] = 'http://' . $_SERVER["SERVER_NAME"] . $s['easy_function_img_click'];
            }

            if ($s['easy_function_img_highlighted']) {
                $s['easy_function_img_highlighted'] = 'http://' . $_SERVER["SERVER_NAME"] . $s['easy_function_img_highlighted'];
            }

        }

        return $data;
    }


}