<?php

namespace Api\Controller;

use Think\Controller;

class IosversionController extends Controller
{
    public function iosVersionCheck()
    {
        $oldVersion = trim(I('request.version'));
        $newVersionInfo = M('Iosversion')->order('`id` DESC')->find();

        $newVersion = $newVersionInfo['version'];
        $update = $newVersionInfo['update'];

        $flag = false;

        $oldArray = explode('.', $oldVersion);
        $newArray = explode('.', $newVersion);
        
        foreach ($newArray as $key => $value) {
            $new = intval(trim($value));
            $old = intval(trim($oldArray[$key]));

            if ($new > $old) {
                $flag = true;
                break;
            } elseif ($new < $old) {
                break;
            }
        }

        $rst = ($flag && $update) ? array('code' => 1, 'msg' => '版本已更新，速去更新') : array('code' => 0, 'msg' => '已是最新版本');

        $this->ajaxReturn($rst);
    }
}