<?php

// +------------------------------------------------------------------------------------------ 
// | Author: longDD <longdd_love@163.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: excel操作类 Dates: 2015-10-21
// +------------------------------------------------------------------------------------------

namespace Org\Util;

use Think\Upload;

class Excel
{
    /**
     * 导入操作
     *
     * @return void
     */
    public static function doWarehouseImport()
    {
        $upload = new Upload();
        $upload->maxSize = 1024 * 1024 * 10;
        $upload->exts = array('xls', 'xlsx');
        $upload->rootPath = './upload/';
        $upload->savePath = '';
        $info = $upload->upload();

        $result = array();
        if (!$info) {
            $result['status'] = -1;
            $result['msg'] = $upload->getError();
            return $result;
        } else {
            Vendor('PHPExcel.Classes.PHPExcel.IOFactory');

            $objPHPExcel = \PHPExcel_IOFactory::load('./upload/'.$info['warehouse_import_data']['savepath'].$info['warehouse_import_data']['savename']);
            $currentSheet = $objPHPExcel->getSheet(0);
            $allColumn = $currentSheet->getHighestColumn();
            $allRow = $currentSheet->getHighestRow();

            $r = array();
            for ($i=1;$i<=$allRow;$i++) {
                $d = array();
                for ($j='A';$j<=$allColumn;$j++){
                    $addr = $j.$i;
                    $cell = $currentSheet->getCell($addr)->getValue();
                    $d[] = trim($cell);
                }
                $r[] = $d;
            }
           $result['status'] = 1;
           $result['data'] = $r;
           return $result;
        }
    }

    /**
     * 配件导入
     *
     * @return array
     */
    public static function doFittingImport()
    {
        $upload = new Upload();
        $upload->maxSize = 1024 * 1024 * 10;
        $upload->exts = array('xls', 'xlsx');
        $upload->rootPath = './upload/';
        $upload->savePath = '';
        $info = $upload->upload();

        $result = array();
        if (!$info) {
            $result['status'] = -1;
            $result['msg'] = $upload->getError();
            return $result;
        } else {
            Vendor('PHPExcel.Classes.PHPExcel.IOFactory');

            $objPHPExcel = \PHPExcel_IOFactory::load('./upload/'.$info['fitting_import_data']['savepath'].$info['fitting_import_data']['savename']);
            $currentSheet = $objPHPExcel->getSheet(0);
            $allColumn = $currentSheet->getHighestColumn();
            $allRow = $currentSheet->getHighestRow();

            $r = array();
            for ($i=1;$i<=$allRow;$i++) {
                $d = array();
                for ($j='A';$j<=$allColumn;$j++){
                    $addr = $j.$i;
                    $cell = $currentSheet->getCell($addr)->getValue();
                    $d[] = trim($cell);
                }
                $r[] = $d;
            }
            $result['status'] = 1;
            $result['data'] = $r;
            return $result;
        }
    }
}