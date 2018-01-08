<?php
// +--------------------------------------------------------------------------------------
// + AcPHP
// +--------------------------------------------------------------------------------------
// + 版权所有 2015年11月15日 贵州天岛在线科技有限公司，并保留所有权利。
// + 网站地址: http://www.acphp.com
// +--------------------------------------------------------------------------------------
// + 这不是一个自由软件！您只能在遵守授权协议前提下对程序代码进行修改和使用；不允许对程序代码以任何形式任何目的的再发布。
// + 授权协议：http://www.acphp.com/license.html
// +--------------------------------------------------------------------------------------
// + Author: AcPHP  http://www.jiangzg.com/ <2992265870@qq.com/jiangzg>
// + Release Date: 2015年11月15日 下午11:09:27
// +--------------------------------------------------------------------------------------
defined('C_CA') or exit('Server error does not pass validation test.');

class xDownLoad{
	
	// +----------------------------------------------------------------------------------
	// + 下载速度
	// +----------------------------------------------------------------------------------
	private $_speed = 512;

    /** 下载
    * @param String  $file   要下载的文件路径
    * @param String  $name   文件名称,为空则与下载的文件名称一样
    * @param boolean $reload 是否开启断点续传
    */
    public function download($file, $name='', $reload=false){
        if(file_exists($file)){
            if($name==''){
                $name = basename($file);
            }
            $fp = fopen($file, 'rb');
            $file_size = filesize($file);
            $ranges = $this->getRange($file_size);
            
            header('cache-control:public');
            header('content-type:application/octet-stream');
            header('content-disposition:attachment; filename='.$name);

            if($reload && $ranges!=null){
            	// 使用续传
                header('HTTP/1.1 206 Partial Content');
                header('Accept-Ranges:bytes');
                // 剩余长度
                header(sprintf('content-length:%u',$ranges['end']-$ranges['start']));
                // range信息
                header(sprintf('content-range:bytes %s-%s/%s', $ranges['start'], $ranges['end'], $file_size));
                // fp指针跳到断点位置
                fseek($fp, sprintf('%u', $ranges['start']));
            }else{
                header('HTTP/1.1 200 OK');
                header('content-length:'.$file_size);
            }
            if( PHP_VERSION>=5.4 ){
            	if(!isset($_SESSION)){
            		session_start();
            	}
            	$_SESSION['download'][md5($name)]['file_size'] = $file_size;
            }
            while(!feof($fp)){
            	if( PHP_VERSION>=5.4 ){
            		$_SESSION['download'][md5($name)]['file_start'] = $ranges['start'];
            		$_SESSION['download'][md5($name)]['file_end'] = $ranges['end'];
            	}
                echo fread($fp, round($this->_speed*1024,0));
                ob_flush();
                //sleep(1); // 用于测试,减慢下载速度
            }

            ($fp!=null) && fclose($fp);

        }else{
            return '';
        }
    }
    
    public function getDownLoadInfo(){
    	echo json_encode($_SESSION['download']);
    }


    /** 设置下载速度
    * @param int $speed
    */
    public function setSpeed($speed){
        if(is_numeric($speed) && $speed>16 && $speed<4096){
            $this->_speed = $speed;
        }
    }


    /** 获取header range信息
    * @param  int   $file_size 文件大小
    * @return Array
    */
    private function getRange($file_size){
        if(isset($_SERVER['HTTP_RANGE']) && !empty($_SERVER['HTTP_RANGE'])){
            $range = $_SERVER['HTTP_RANGE'];
            $range = preg_replace('/[\s|,].*/', '', $range);
            $range = explode('-', substr($range, 6));
            if(count($range)<2){
                $range[1] = $file_size;
            }
            $range = array_combine(array('start','end'), $range);
            if(empty($range['start'])){
                $range['start'] = 0;
            }
            if(empty($range['end'])){
                $range['end'] = $file_size;
            }
            return $range;
        }
        return null;
    }
}








