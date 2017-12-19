<?php
 
// +------------------------------------------------------------------------------------------ 
// | Author: longDD <longdd_love@163.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description:  Dates: 2016-08-05
// +------------------------------------------------------------------------------------------ 

namespace Api\Controller;


use Api\Controller;
use Api\Model\CheckModel;

class CommentController extends BaseController
{
    /**
     * 添加留言
     *
     * @return void
     */
    public function add()
    {
        $post = I('post.');
        $data = array();
        $model = M('comment');

        if (!CheckModel::regexp('mobile', trim($post['mobile']))) {
            $this->_error(403, '手机号格式错误');
        }

        $key = 'code' . trim($post['mobile']);

        if (S($key) != $post['code'] || empty($post['code'])) {
            $this->_error(403, '短信验证码错误');
        }

        $data = array(
            "cellphone" => $post['mobile'],
            "content" => $post['content'],
            "time" => time(),
            "status" => 0,
        );

        if (!$model->add($data)) {
            $this->_error(403, '反馈失败');
        } else {
            $this->_callBack('反馈成功');
        }
    }

}