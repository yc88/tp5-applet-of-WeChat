<?php

/**
 * 权限检测
 * @param $rule
 */
function authCheck($rule)
{
    $control = explode('/', $rule)['0'];
    if(in_array($control, ['login', 'index'])){
        return true;
    }
    if(in_array($rule, cache(session('role_id')))){
        return true;
    }
    return false;
}

/**菜单栏目整合
 * @param $data
 * @return array
 */
function trimMenu($data){
    //数组里面是对象
    $data = objToArray($data);
    $top = [];
    $son = [];
    foreach($data as $k => $v){
        if(0 == $v['type_id']){
            $v['href'] = '#';
            $top[] = $v;
        }else{
            $v['href'] = url($v['control_name'] .'/'. $v['action_name']); //跳转地址
            $son[] = $v;
        }
    }
    foreach($top  as $k => $v){
       foreach($son as $k1 => $v1){
           if($v1['type_id'] == $v['id']){
               $top[$k]['son'][] = $v1;
           }
       }
    }
    unset($child);
    return $top;
}
/**
 * 对象转换成数组
 * @param $obj
 */
function objToArray($obj)
{
    return json_decode(json_encode($obj), true);
}

/**
 * 生成操作按钮
 * @param array $operate 操作按钮数组
 */
function showOperate($operate = [])
{
    if(empty($operate)){
        return '';
    }
    $option = '';
    foreach($operate as $key=>$vo){
        if(authCheck($vo['auth'])){
            $option .= ' <a href="' . $vo['href'] . '"><button type="button" class="btn btn-' . $vo['btnStyle'] . ' btn-sm">'.
                '<i class="' . $vo['icon'] . '"></i> ' . $key . '</button></a>';
        }
    }

    return $option;
}

/**
 * 拼装操作按钮
 * @param $id
 * @return array
 */
function makeButtonAll($id,$editUrl,$delUrl)
{
    return [
        '编辑' => [
            'auth' => $editUrl,
            'href' => url($editUrl, ['id' => $id]),
            'btnStyle' => 'primary',
            'icon' => 'fa fa-paste'
        ],
        '删除' => [
            'auth' => $delUrl,
            'href' => "javascript:roleDel(" .$id .")",
            'btnStyle' => 'danger',
            'icon' => 'fa fa-trash-o'
        ]
    ];
}
/**
 * 整理出tree数据 ---  layui tree
 * @param $pInfo
 * @param $spread
 */
function getTree($pInfo, $spread = true)
{
    $res = [];
    $tree = [];
    //整理数组
    foreach($pInfo as $key=>$vo){
        if($spread){
            $vo['spread'] = true;  //默认展开
        }
        $res[$vo['id']] = $vo;
        $res[$vo['id']]['children'] = [];
    }
    unset($pInfo);
    //查找子孙
    foreach($res as $key=>$vo){
        if(0 != $vo['pid']){
            $res[$vo['pid']]['children'][] = &$res[$key];
        }
    }

    //过滤杂质
    foreach( $res as $key=>$vo ){
        if(0 == $vo['pid']){
            $tree[] = $vo;
        }
    }
    unset( $res );
    return $tree;
}

/**上传图片
* @param $file
* @param $size
* @return array|bool
*/
function uploadImg($name="img",$size = null)
{
    $file  = request()->file($name);
    $img_url = ROOT_PATH . 'public' . DS . 'static'.DS; //创建目录
    $tmp_dir = $img_url."tmp/"; //创建临时目录
    if(!is_dir($tmp_dir)){
        mkdir($tmp_dir,0777,true);
    }
    if($file){
        $info = $file->move($tmp_dir);
        $fileName = $filePath = '';
        if($info){
            $fileName = $info->getSaveName();
            $fileName = str_replace("\\",'/',$fileName);
            $filePath ='tmp/'.$fileName;
        }else{
            // 上传失败获取错误信息
            putMsg(0,$file->getError());
        }
        if ($filePath) {
            if ($size) {
                $view = extendView($fileName, $size);
            } else {
                $view =config('img_url').$filePath;
            }
            echo json_encode(array(
                'view' => $view, //预览的地址图片
                'value' =>$filePath, //提供数据保存图片
            ));
            exit;
        }
    }
    putMsg(0,'系统错误1');
}
/**生成view预览图
 * @param $fileName
 * @param $resize
 * @return string
 */
// todo
function extendView($fileName, $resize)
{   $img_url = ROOT_PATH . 'public' . DS . 'static'.DS;
    $filePath = $img_url . 'tmp/' . $fileName;
    $newFilePath = $img_url . 'view/';
    if(!is_dir($newFilePath)){
        mkdir($newFilePath,0777,true);
    }
    if (!is_array($resize)) {
        $resize = array($resize);
    }
    $image = \think\Image::open($filePath); //打开一幅图
    foreach ($resize as $resizeItem) {
        $image->thumb($resizeItem['width'], $resizeItem['height'])->save($newFilePath,$fileName);
    }
    return $newFilePath . $fileName;
}

/**扩展图片
 * @param $fileName //图片名称
 * @param $subFile //子文件名
 * @param $resize array('width'=>px, 'height'=>px),
 *      array('width'=>px, 'height'=>px)
 * )
 * @param $saveSource //是否保存源文件
 * @return mixed
 */
function extendImage($fileName, $subFile, $resize = array(), $saveSource = false)
{
    $filePath = '.'.config('img_url'); // ./static/
     $img_url = $filePath.$fileName; //更目录  整个图片存在路径
    $fileArray = explode('/', $fileName);
    $fileName = end($fileArray);
    $file = explode('.', $fileName);
    $name = $file[0]; //文件名称
    $ext = $file[1]; // 文件格式  jpg
    if ($subFile) { //保存目录
        $newFilePath = $filePath.$subFile . '/' . date('Ymd') . '/';
    } else {
        $newFilePath = $filePath . date('Ymd') . '/';
    }
    if (!is_dir($newFilePath)) {
        mkdir($newFilePath, 0777, true);
        chmod($newFilePath, 0777);
    }
    if (is_bool($resize)) {
        $saveSource = $resize;
        $resize = array();
    }
    //保存源文件
    if ($saveSource) {
        $image = \think\Image::open($img_url);
        $image->save($newFilePath . $fileName);
    }
    //扩展图片
    if (!empty($resize)) {
        if (!is_array($resize[0])) {
            $resize = array($resize);
        }
        foreach ($resize as $resizeItem) {
            $image = \think\Image::open($img_url);
            $newImageName = $name . '_' .
                (is_array($resizeItem) ?
                    (
                        (isset($resizeItem['width']) ? $resizeItem['width'] : 'auto') .
                        'x' .
                        (isset($resizeItem['height']) ? $resizeItem['height'] : 'auto')
                    ) : '') . '.' . $ext;
            $image->thumb($resizeItem['width'], $resizeItem['height'])->save($newFilePath . $newImageName);
        }
    }
    removeImageTmp($fileName);
    if ($subFile) {
        return $subFile . '/' . date('Ymd') . '/' . $fileName;
    }
    return date('Ymd') . '/' . $fileName;
}
/**删除tmp文件
 * @param $imagePath
 */
function removeImageTmp($imagePath)
{
    $rootUrl = '.'.config('img_url');
    @unlink($rootUrl . '/tmp/' . $imagePath);
    @unlink($rootUrl . '/view/' . $imagePath);
}

///**获取图片预览地址
// * @param $fileName
// * @param null $width
// * @param null $height
// * @param string '' $subFile
// * @return string
// */
//function getImgName($fileName, $width = null, $height = null, $subFile = '')
//{
//    if(!$fileName){
//        return false;
//    }
//    $file = explode('.', $fileName);
//    $name = $file[0];
//    $ext = $file[1];
//    if (!$width && !$height) {
//        return config('img_url').$subFile . $fileName;
//    } else {
//        return config('img_url').$subFile . '/' . $name . '_' . $width . 'x' . $height . '.' . $ext;
//    }
//}

/**删除图片
 * @param $uploadImagePath
 * array(array('imgurl','width','height')
 */
function removeUploadImage($uploadImagePath)
{
    $basePath = '.'.config('img_url');
    if (is_array($uploadImagePath)) {
        foreach ($uploadImagePath as $item) {
           if (is_string($item)) {
                @unlink($basePath . $item);
            } else {
                @unlink('.'.getImgName($item[0], $item[1], $item[2]));
            }
        }
    } else {
        @unlink($basePath . $uploadImagePath);
    }
}


/**测试中
 * @param $expTitle
 * @param $expCellName
 * @param $expTableData
 * @throws PHPExcel_Exception
 * @throws PHPExcel_Reader_Exception
 */
function exportExcel($expCellName,$expTableData,$save_name = 'static/excel/excel.xls'){
    $cellNum = count($expCellName); //头部标题  28
    $dataNum = count($expTableData); //返回的数据 15
    vendor('PHPExcel.Classes.PHPExcel');
    vendor("PHPExcel.Classes.PHPExcel.Writer.Excel5");
    vendor("PHPExcel.Classes.PHPExcel.Writer.Excel2007");
    vendor("PHPExcel.Classes.PHPExcel.IOFactory");
    $objPHPExcel = new PHPExcel();
    $cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');
     $objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(25);//所有单元格（行）默认高度
     $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(32);//所有单元格（列）默认宽度
     $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);//设置列宽度
     $objPHPExcel->getActiveSheet()->getStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
    for($i=0;$i<$cellNum;$i++){
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'1', $expCellName[$i][1]);
        $objPHPExcel->getActiveSheet()->getStyle($cellName[$i].'1')->getFont()->setSize(15);//设置文字大小
        $objPHPExcel->getActiveSheet()->getStyle($cellName[$i].'1')->getFont()->setBold(true);//设置是否加粗
        $objPHPExcel->getActiveSheet()->getStyle($cellName[$i].'1')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_BLACK);// 设置文字颜色
        $objPHPExcel->getActiveSheet()->getStyle($cellName[$i].'1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//设置文字居左（HORIZONTAL_LEFT，默认值）中（HORIZONTAL_CENTER）右（HORIZONTAL_RIGHT）
        $objPHPExcel->getActiveSheet()->getStyle($cellName[$i].'1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
        for($t=2; $t < 35; $t++ ){
            $objPHPExcel->getActiveSheet()->getStyle($cellName[$i].$t)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
            $objPHPExcel->getActiveSheet()->getStyle($cellName[$i].$t)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//设置文字居左（HORIZONTAL_LEFT，默认值）中（HORIZONTAL_CENTER）右（HORIZONTAL_RIGHT）
            $objPHPExcel->getActiveSheet()->getStyle($cellName[$i].$t)->getFont()->setSize(12);//设置文字大小
        }
    }
    // Miscellaneous glyphs, UTF-8
    for($i=0;$i<$dataNum;$i++){
        for($j=0;$j<$cellNum;$j++){
            $objPHPExcel->getActiveSheet()->setCellValue($cellName[$j].($i+2), $expTableData[$i][$expCellName[$j][0]]);
        }
    }
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save($save_name);
    $fileNameArr = explode('/',$save_name);
    $file_name = $fileNameArr[count($fileNameArr)-1];
    $contents = file_get_contents($save_name);
    $file_size = filesize($save_name);
    header("Content-type: application/octet-stream;charset=utf-8");
    header("Accept-Ranges: bytes");
    header("Accept-Length: $file_size");
    header("Content-Disposition: attachment; filename=".$file_name);
    exit($contents);

}