<?php

// +------------------------------------------------------------------------------------------ 
// | Author: longDD <longdd_love@163.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 配件 Dates: 2016-09-06
// +------------------------------------------------------------------------------------------ 

namespace Admin\Controller;

use Admin\Controller;
use Vendor;

class FittingController extends BaseController
{
	/**
	 * 首页
	 *
	 * @return void
	 */
    public function index()
    {
        $this->display();
    }

    /**
     * 列表
     *
     * @return void
     */
    public function rows()
    {
        $map = array();
        $rst = array();
        $post = I('post.');

        if (!empty($post['phone_id'])) {
            $fittings = M('phone_fitting')->where(array('phone_id' => $post['phone_id']))->getField('fitting_id, phone_id');
            
            if ($fittings) {
                $map['f.id'] = array('in', array_keys($fittings));
            }
        }
        
        if (!empty($post['category_id'])) {
            $map['f.category_id'] = intval($post['category_id']);
        }

        if (!empty($post['keyword'])) {
            $where = array();
            $where['f.number']  = array('like', '%' . $post['keyword'] . '%');
            $where['f.title']  = array('like', '%' . $post['keyword'] . '%');
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }

        $count = M('fitting')->join('f left join phone_fitting pf on f.id=pf.fitting_id')
                ->join('left join fitting_category fc on fc.id = f.category_id')
                ->where($map)->count('DISTINCT(f.id)');
        $rst['total'] = $count;

        $list = M('fitting')->field('f.*, fc.name as category_name, group_concat(p.id) as phone_id,group_concat(p.alias) as phone')
                ->join('f left join phone_fitting pf on f.id=pf.fitting_id left join phone p on pf.phone_id=p.id')
                ->join('left join fitting_category fc on fc.id = f.category_id')
                ->where($map)->limit($this->page())
                ->group('f.id')
                ->order('f.id desc')
                ->select();
        $rst['rows'] = $list;

        $this->ajaxReturn($rst);
    }

    /**
     * 机型
     *
     * @return void
     */
    public function phones()
    {
        $list = M('phone')->where()->field('id, alias')->order('alias asc')->select();
        $this->ajaxReturn($list);
    }

    /**
     * 增加
     *
     * @return void
     */
    public function add()
    {
        $rst = array();
        $data = array();
        $data = I('post.');
        $data['category_id'] = intval($data['category_id']);
        
        if (M('fitting')->where(array('number' => $data['number']))->find()) {
            $rst['success'] = false;
            $rst['errorMsg'] = '物料编号已经存在！';
            $this->ajaxReturn($rst);
        }
        
        M()->startTrans();
        
        $fitting_id = M('fitting')->add($data);
        
        if ($fitting_id === false) {
            M()->rollback();
            $rst['success'] = false;
            $rst['errorMsg'] = '添加失败！';
            $this->ajaxReturn($rst);
        }
        
        $params = array();
        $params = array();
        $phone_ids = explode(',', $data['phone_ids']);
        
        foreach ($phone_ids as $phone_id) {
            $params[] = array('phone_id' => $phone_id, 'fitting_id' => $fitting_id);
        }
        
        if ($params && M('phone_fitting')->addAll($params) === false) {
            M()->rollback();
            $rst['success'] = false;
            $rst['errorMsg'] = '添加失败';
            $this->ajaxReturn($rst);
        }
        
        M()->commit();
        
        $rst['success'] = true;
        
        $this->ajaxReturn($rst);
    }

    /**
     * 更新
     *
     * @return void
     */
    public function save()
    {
        $rst = array();
        $map = array();
        $map['id'] = I('get.id/d');
        $data = I('post.');
        $data['category_id'] = intval($data['category_id']);
        
        $item = D('fitting')->where($map)->find();

        if ($item) {
            
            if (M('fitting')->where(array('number' => $data['number'], 'id' => array('neq', $map['id'])))->find()) {
                $rst['success'] = false;
                $rst['errorMsg'] = '物料编号已经存在！';
                $this->ajaxReturn($rst);
            }
            
            M()->startTrans();
            
            if (M('fitting')->where($map)->save($data) === false) {
                M()->rollback();
                $rst['success'] = false;
                $rst['errorMsg'] = '更新失败！';
                $this->ajaxReturn($rst);
            }
            
            if (M('phone_fitting')->where(array('fitting_id' => $item['id']))->delete() === false) {
                M()->rollback();
                $rst['success'] = false;
                $rst['errorMsg'] = '更新失败！';
                $this->ajaxReturn($rst);
            }
            
            $params = array();
            $phone_ids = explode(',', $data['phone_ids']);
            
            foreach ($phone_ids as $phone_id) {
                $params[] = array('phone_id' => $phone_id, 'fitting_id' => $item['id']);
            }
            
            if ($params && M('phone_fitting')->addAll($params) === false) {
                M()->rollback();
                $rst['success'] = false;
                $rst['errorMsg'] = '更新失败！';
                $this->ajaxReturn($rst);
            }
            
            M()->commit();
            $rst['success'] = true;
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '记录不存在！';
        }

        $this->ajaxReturn($rst);
    }

   	/**
   	 * 删除
   	 *
   	 * @return void
   	 */
   	public function delete()
   	{
        $id = I('post.id');
        $rst = array();
        $map = array();
        $map['id'] = $id;
        $item = D('fitting')->where($map)->find();

        if ($item) {
            
            M()->startTrans();
            
            if (D('phone_fitting')->where(array('fitting_id' => $item['id']))->delete() === false) {
                M()->rollback();
                $rst['success'] = false;
                $rst['errorMsg'] = '删除失败！';
                $this->ajaxReturn($rst);
            }

            if (D('fitting')->where($map)->limit(1)->delete() === false) {
                M()->rollback();
                $rst['success'] = false;
                $rst['errorMsg'] = '删除失败！';
                $this->ajaxReturn($rst);
            }
            
            M()->commit();
            $rst['success'] = true;
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '记录不存在！';
        }

        $this->ajaxReturn($rst);
    }

    /**
     * 导入
     * 
     * @return void
     */
    public function import()
    {
        $rst = array();
        
        Vendor('PHPExcel.Classes.PHPExcel.IOFactory');
        
        $inputFileType = \PHPExcel_IOFactory::identify($_FILES['fitting_file']['tmp_name']);
        $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel = $objReader->load($_FILES['fitting_file']['tmp_name']);
        $data = $objPHPExcel->getSheet(0)->toArray();
        
        $total_rows = 0; //总行数
        $fail_rows = array(); //导入失败行
        $flag = true;
        
        M()->startTrans();
        
        foreach ($data as $k => $value) {
            
            if ($k < 1) {
                continue;
            }
            
            if (empty($value[0]) && empty($value[1]) && empty($value[2]) && empty($value[3])) {
                continue;
            }
            
            $total_rows++;
            
            if (empty($value[0]) || empty($value[1]) || !is_numeric($value[2]) || $value[2] < 0 || empty($value[3])) {
                $fail_rows[] = "第{$k}行编号、名称、价格或机型不能为空！";
                continue;
            }
            
            if (M('fitting')->where(array('number'=>trim($value[0])))->count()) {
                $fail_rows[] = "第{$k}行配件编号已经存在！";
                continue;
            }
            
            $phones = explode(',', $value[3]);
            $map = array(
                'alias' => array('in', $phones),
                'id' => array('in', $phones),
                 '_logic' => 'OR'
            );
            
            $phone_ids = array_keys(M('phone')->where($map)->getField('id, alias'));
            
            if (!$phone_ids) {
                $fail_rows[] = "第{$k}行未匹配到对应机型！";
                continue;
            }
            
            $param = array(
                'number' => trim($value[0]),
                'title' => trim($value[1]),
                'price' => trim($value[2]),
                'remark' => trim($value[4]),
            );
            
            $fitting_id = M('fitting')->add($param);
            
            if ($fitting_id === false) {
                $flag = false;
                continue;
            }
            
            foreach ($phone_ids as $phone_id) {
                
                if (M('phone_fitting')->add(array('phone_id' => $phone_id, 'fitting_id' => $fitting_id) ) === false) {
                    $flag = false;
                }
            }
        }
        
        if ($flag) {
            M()->commit();
            $rst['success'] = true;
            $success_rows = $total_rows - count($fail_rows);
            $rst['errorMsg'] = "导入{$total_rows}行数据；成功导入{$success_rows}行！";
            
            if ($fail_rows) {
                $rst['errorMsg'] .= "导入失败：" . implode('', $fail_rows) . '。';
            }
        } else {
            M()->rollback();
            $rst['success'] = false;
            $rst['errorMsg'] = '配件导入失败（写入数据失败）！';
        }
        
        $this->ajaxReturn($rst);
    }
    
    /**
     * 导出
     *
     * @return void
     */
    public function export()
    {
        $data = I('post.');
        
        if (!empty($data['phone_id'])) {
            $map['pf.phone_id'] = $data['phone_id'];
        }
        
        if (!empty($data['keyword'])) {
            $where = array();
            $where['f.number']  = array('like', '%' . $data['keyword'] . '%');
            $where['f.title']  = array('like', '%' . $data['keyword'] . '%');
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }
        
        $params = array();
        $params[] = array('名称', '编号', '价格', '机型', '备注');
        
        $list = M('fitting')->field('f.*, group_concat(distinct(p.alias)) as phone')
                ->join('f left join phone_fitting pf on f.id=pf.fitting_id left join phone p on pf.phone_id=p.id')
                ->where($map)
                ->group('f.id')
                ->select();
        
        foreach ($list as $item) {
            $params[] = array($item['title'], $item['number'], $item['price'], $item['phone'], $item['remark']);
        }
        
        $this->exportData('配件列表', $params);
    }
    
    /**
     * 大类
     *
     * @return void
     */
    public function category()
    {
        $this->display();
    }

    /**
     * 列表
     *
     * @return void
     */
    public function categoryRows()
    {
        $map = array();
        $rst = array();

        $count = M('fitting_category')->where($map)->count();
        $rst['total'] = $count;

        $list = M('fitting_category')->where($map)->limit($this->page())->select();
        $rst['rows'] = $list;

        $this->ajaxReturn($rst);
    }

    /**
     * 增加
     *
     * @return void
     */
    public function categoryAdd()
    {
        $rst = array();
        $data = array();
        $data = I('post.');
        
        if (M('fitting_category')->where(array('name'=>$data['name']))->count()) {
            $rst['success'] = false;
            $rst['errorMsg'] = '添加失败（配件大类已经存在）';
            $this->ajaxReturn($rst);
        }
        
        if (M('fitting_category')->add($data) !== false) {
            $rst['success'] = true;
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '添加失败！';
        }
        $this->ajaxReturn($rst);
    }


    /**
     * 更新
     *
     * @return void
     */
    public function categorySave()
    {
        $rst = array();
        $map = array();
        $map['id'] = I('get.id/d');
        $data = I('post.');
        $item = D('fitting_category')->where($map)->find();

        if ($item) {
            
            if (M('fitting_category')->where(array('name'=>$data['name'], 'id' => array('neq', $item['id'])))->count()) {
                $rst['success'] = false;
                $rst['errorMsg'] = '更新失败（配件大类已经存在）';
                $this->ajaxReturn($rst);
            }

            if (D('fitting_category')->where($map)->save($data) !== false) {
                $rst['success'] = true;
            } else {
                $rst['success'] = false;
                $rst['errorMsg'] = '更新失败！';
            }
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '记录不存在！';
        }

        $this->ajaxReturn($rst);
    }

    /**
     * 删除
     *
     * @return void
     */
    public function categoryDelete()
    {
        $id = I('post.id/d');
        $rst = array();
        $map = array();
        $map['id'] = $id;
        $item = D('fitting_category')->where($map)->find();

        if ($item) {
            
            if (M('fitting')->where(array('category_id' => $id))->count()) {
                $rst['success'] = false;
                $rst['errorMsg'] = '删除失败(配件大类已关联配件)！';
                $this->ajaxReturn($rst);
            }

            if (D('fitting_category')->where($map)->limit(1)->delete() !== false) {
                $rst['success'] = true;
            } else {
                $rst['success'] = false;
                $rst['errorMsg'] = '删除失败！';
            }
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '记录不存在！';
        }

        $this->ajaxReturn($rst);
    }
    
    /**
     * 配件大类列表
     */
    public function fittingCategorys()
    {
        $rst = M('fitting_category')->select();
        $this->ajaxReturn($rst);
    }
}