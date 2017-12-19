<?php

// +------------------------------------------------------------------------------------------ 
// | Author: longDD <longdd_love@163.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 后台基类 Dates: 2016-07-13
// +------------------------------------------------------------------------------------------

namespace Admin\Controller;

use Think\Controller;

class BaseController extends Controller 
{
	/**
	 * 构造函数
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
		$this->checkAccess();
		$this->checkMenu();
	}

	/**
	 * 检查权限
	 *
	 * @return void
	 */
	private function checkAccess()
	{
		if (!session('?userId') || !session('?userInfo.id')) {
			session(null);
			session_start();
        	session_destroy();

			$this->redirect('admin/login/index');
		}

		if (!\Org\Tool\Permission::authenticate() && !APP_DEBUG) {

			if (IS_AJAX) {
				$this->ajaxReturn(array('success' => false, 'errorMsg' => '没有操作权限！'));
			} else {
				$this->error('没有操作权限！', '/admin/index/index', 3);
			}
		}
	}

	/**
	 * 检查目录
	 *
	 * @return void
	 */
	private function checkMenu()
	{
		if (!session('?menu')) {
			session(null);
			session_start();
        	session_destroy();

			$this->redirect('admin/login/index');
		}

		$path = strtolower('/' . MODULE_NAME . '/' . CONTROLLER_NAME . '/' . ACTION_NAME);
		$button = session('button');
		$this->assign('buttons', $button[$path]);
	}

	/**
	 * 分页
	 *
	 * @return void
	 */
	public function page()
	{
		$page = I('post.page', 1);
        $rows = I('post.rows', 10);
        $startRow = ($page - 1) * $rows;
        $limitStr = (string)$startRow . ',' . (string)$rows;
        return $limitStr;
	}

	// 批量操作
	protected function batchOperate($table, $map, $data)
	{
		return M($table)->where($map)->save($data);
	}

	/**
	 * 上传
	 *
	 * @return void
	 */
	public function upload()
	{
		$upload = new \Think\Upload();
	    $upload->maxSize = 10485760;
	    $upload->exts = explode(',', 'jpg,gif,png,jpeg');
	    $upload->rootPath = './upload/';
	    $upload->saveName = array('uniqid','');
	    $upload->autoSub = true;
	    $upload->subName = array('date','Ymd');
	    $info = $upload->upload();

	    $rst = array();

	    if (!$info) {
	    	$rst['success'] = false;
	    	$rst['errorMsg'] = $upload->getError();
	    } else {
	    	$rst['success'] = true;
	    	$rst['info'] = $info;
	    }

	    return $rst;
	}

	/**
	 * 导出
	 * @param  string  $filename   导出文件名
	 * @param  array   $params     导出数据
	 * @param  bool    $isMultiSheet是否分工作表导出
	 * @return void
	 */
	public function exportData($filename, $params, $isMultiSheet = false) 
	{
	    ob_end_clean();
	    
	    Vendor('PHPExcel.Classes.PHPExcel');
	    
	    $objPHPExcel = new \PHPExcel();
	    $objPHPExcel->getProperties()->setCreator("weadoc")
            	    ->setLastModifiedBy("weadoc")
            	    ->setTitle("闪修侠数据列表")
            	    ->setSubject("闪修侠数据列表")
            	    ->setDescription("闪修侠数据列表")
            	    ->setKeywords("闪修侠数据列表")
            	    ->setCategory("闪修侠数据列表");
	    
	    if ($isMultiSheet) {
	        $index = 0;
	        
	        foreach ($params as $key => $value) {
	            $objPHPExcel->setactivesheetindex($index);
	            $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(25);
	            $objPHPExcel->getActiveSheet()->setTitle($key);
	            $objPHPExcel->getActiveSheet()->fromArray($value);
	        
	            $index++;
	        
	            if ($index <= count($params)) {
	                $objPHPExcel->createSheet($index);
	            }
	        }
	        
	        $objPHPExcel->setactivesheetindex(0);
	    } else {
	        $objPHPExcel->setactivesheetindex(0);
	        $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(25);
	        $objPHPExcel->getActiveSheet()->fromArray($params);
	    }
	    
	    // Redirect output to a client’s web browser (Excel5)
	    $filename = $filename ? $filename . '.xls' : 'weadoc_' . date('Y-m-d H:i:s') . '.xls';
	    header('Content-Type: application/vnd.ms-excel');
	    header('Content-Disposition: attachment;filename="'.$filename.'"');
	    header('Cache-Control: max-age=0');
	    // If you're serving to IE 9, then the following may be needed
	    header('Cache-Control: max-age=1');
	    
	    // If you're serving to IE over SSL, then the following may be needed
	    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
	    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
	    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
	    header ('Pragma: public'); // HTTP/1.0
	    
	    $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	    $objWriter->save('php://output');
	    exit;
	}
}