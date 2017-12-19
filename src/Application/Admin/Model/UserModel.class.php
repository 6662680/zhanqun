<?php

// +------------------------------------------------------------------------------------------ 
// | Author: TCG <tianchunguang@weadoc.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 订单模型 Dates: 2016-09-25
// +------------------------------------------------------------------------------------------

namespace Admin\Model;

use Think\Model\RelationModel;

class UserModel extends RelationModel
{

    /**
     * 获取角色组织
     *
     * @param int $orderId 订单ID
     * @return array
     */
    public function organization()
    {
        $model = M('user');
        $model->join('left join `user_role` ur on user.id = ur.user_id');
        $model->join('left join `role` on ur.role_id = role.id');
        $model->join('left join `user_organization` uo on user.id = uo.user_id');
        $model->join('left join `organization` on uo.organization_id = organization.id');
        $model->where(array('user.id' => $_SESSION['userInfo']['id']));
        $model->field('organization.name as organization_name');
        $role = $model->find();
        return $role['organization_name'];
    }
}
