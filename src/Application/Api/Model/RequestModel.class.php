<?php
namespace Api\Model;
use Think\Controller;

class RequestModel extends Controller {

    public static function _get($key){
        $get='get.'.$key;

        return I($get,'','htmlspecialchars');
    }

    public static function _post($key){
        $post='post.'.$key;
        return I($post,'','htmlspecialchars');
    }

    public static function _all(){
        return I('param.','','htmlspecialchars');
    }

    public static function _allGet(){
        return I('get.','','htmlspecialchars');
    }

    public static function _allPost(){
        return I('post.','','htmlspecialchars');
    }

}