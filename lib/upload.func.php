<?php
/**
 * Created by PhpStorm.
 * User: zjy
 * Date: 2018/7/17
 * Time: 14:47
 */
require_once '../lib/string.func.php';
/**
 * 构建上传文件信息
 * @return array
 */
function buildInfo(){
    $i=0;
    foreach($_FILES as $v){
        //单文件
        if(is_string($v['name'])){
            $files[$i]=$v;
            $i++;
        }else{
            //多文件
            foreach($v['name'] as $key=>$val){
                $files[$i]['name']=$val;
                $files[$i]['size']=$v['size'][$key];
                $files[$i]['tmp_name']=$v['tmp_name'][$key];
                $files[$i]['error']=$v['error'][$key];
                $files[$i]['type']=$v['type'][$key];
                $i++;
            }
        }
    }
    return $files;
}


function uploadFile($path="uploads",$allowExt=array("gif","jpeg","png","jpg","wbmp"),$maxSize=2097152,$imgFlag=true){
    if(!file_exists($path)){
        mkdir($path,0777,true);
        chmod ($path, 0777 );
    }
    $i=0;
    $files=buildInfo();
    foreach($files as $file){
        var_dump($file);
        if($file['error']===UPLOAD_ERR_OK){
            $ext=getExt($file['name']);
            //检测文件的扩展名
            if(!in_array($ext,$allowExt)){
                exit("非法文件类型");
            }
            //校验是否是一个真正的图片类型
            if($imgFlag){
                if(!getimagesize($file['tmp_name'])){
                    exit("不是真正的图片类型");
                }
            }
            //上传文件的大小
            if($file['size']>$maxSize){
                exit("上传文件过大");
            }
            if(!is_uploaded_file($file['tmp_name'])){
                exit("不是通过HTTP POST方式上传上来的");
            }
            $filename=getUniName().".".$ext;
            $destination=$path."/".$filename;
            if(move_uploaded_file($file['tmp_name'],$destination)){
                // 文件上传成功
                echo file_exists($file['tmp_name'])? $file['tmp_name']."存在": $file['tmp_name']."不存在";
                echo "<br />";
                echo file_exists($destination)? $destination."存在": $destination."不存在";
                echo "<br />";
                print_r("move_uploaded_file执行成功");
                $file['name']=$filename;
                unset($file['error'],$file['tmp_name'],$file['size'],$file['type']);
                $uploadedFiles[$i]=$file;
                $i++;
            }
        }else{
            switch($file['error']){
                case 1:
                    $mes="超过了配置文件上传文件的大小";//UPLOAD_ERR_INI_SIZE
                    break;
                case 2:
                    $mes="超过了表单设置上传文件的大小";			//UPLOAD_ERR_FORM_SIZE
                    break;
                case 3:
                    $mes="文件部分被上传";//UPLOAD_ERR_PARTIAL
                    break;
                case 4:
                    $mes="没有文件被上传";//UPLOAD_ERR_NO_FILE
                    break;
                case 6:
                    $mes="没有找到临时目录";//UPLOAD_ERR_NO_TMP_DIR
                    break;
                case 7:
                    $mes="文件不可写";//UPLOAD_ERR_CANT_WRITE;
                    break;
                case 8:
                    $mes="由于PHP的扩展程序中断了文件上传";//UPLOAD_ERR_EXTENSION
                    break;
            }
            echo $mes;
        }
    }
    return $uploadedFiles;
}