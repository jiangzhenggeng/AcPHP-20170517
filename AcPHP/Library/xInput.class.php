<?php 
// +--------------------------------------------------------------------------------------
// + AcPHP
// +--------------------------------------------------------------------------------------
// + 版权所有 2015年11月8日 贵州天岛在线科技有限公司，并保留所有权利。
// + 网站地址: http://www.acphp.com
// +--------------------------------------------------------------------------------------
// + 这不是一个自由软件！您只能在遵守授权协议前提下对程序代码进行修改和使用；不允许对程序代码以任何形式任何目的的再发布。
// + 授权协议：http://www.acphp.com/license.html
// +--------------------------------------------------------------------------------------
// + Author: AcPHP  http://www.jiangzg.com/ <2992265870@qq.com/jiangzg>
// + Release Date: 2015年11月8日 上午11:54:32
// +--------------------------------------------------------------------------------------

// +--------------------------------------------------------------------------------------
// + 数据输入类
// +--------------------------------------------------------------------------------------
class xInput {
	
	// +--------------------------------------------------------------------------------------
	// + 获取POST方式提交的数据
	// +--------------------------------------------------------------------------------------
	public static function post($key_name,$default=''){
		return self::request($key_name,$default,'POST');
	}
	
	// +--------------------------------------------------------------------------------------
	// + 获取GET方式提交的数据
	// +--------------------------------------------------------------------------------------
	public static function get($key_name,$default=''){
		return self::request($key_name,$default,'GET');
	}
	
	// +--------------------------------------------------------------------------------------
	// + 获取FILES方式提交的数据
	// +--------------------------------------------------------------------------------------
	public static function files($key_name,$default=''){
		return self::request($key_name,$default,'FILES');
	}
	
	// +--------------------------------------------------------------------------------------
	// + 获取COOKIE方式提交的数据
	// +--------------------------------------------------------------------------------------
	public static function cookie($key_name,$default='',$gettype='xCookie'){
		if(strtolower($gettype)=='xcookie')
			return self::request($key_name,$default,'COOKIE');
		else
			return isset($_COOKIE[$key_name])?$_COOKIE[$key_name]:$default;
	}
	// +--------------------------------------------------------------------------------------
	// + 获取请求参数的方法
	// + _GET传输方式的参数优先级高与_POST
	// +--------------------------------------------------------------------------------------
	public static function request($key_name,$default='',$method=''){
		$method = strtoupper(trim($method) );
		if ($method===''){
			return isset( $_GET [$key_name] ) ? $_GET [$key_name] : (isset ( $_POST [$key_name] ) ? $_POST [$key_name] : $default);
		}elseif($method=='GET'){
			return isset($_GET [$key_name]) ? $_GET [$key_name] : $default;
		}elseif($method=='POST'){
			return isset($_POST [$key_name]) ? $_POST [$key_name] : $default;
		}elseif($method=='FILES'){
			return isset($_FILES [$key_name]) ? $_FILES [$key_name] : $default;
		}elseif($method=='COOKIE'){
			ac_require_once(C_SYS_LIBRARY_PATH.'Tool/xCookie'.C_EXT);
			return xCookie::get($key_name,$default);
		}else {
			return $default;
		}
	}
	
}