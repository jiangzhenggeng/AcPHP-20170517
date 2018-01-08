<?php
// +--------------------------------------------------------------------------------------
// + AcPHP
// +--------------------------------------------------------------------------------------
// + 版权所有 2015年11月4日 贵州天岛在线科技有限公司，并保留所有权利。
// + 网站地址: http://www.acphp.com
// +--------------------------------------------------------------------------------------
// + 这不是一个自由软件！您只能在遵守授权协议前提下对程序代码进行修改和使用；不允许对程序代码以任何形式任何目的的再发布。
// + 授权协议：http://www.acphp.com/license.html
// +--------------------------------------------------------------------------------------
// + Author: AcPHP  http://www.jiangzg.com/ <2992265870@qq.com/jiangzg>
// + Release Date: 2015年11月4日 下午11:17:40
// +--------------------------------------------------------------------------------------

defined('C_CA') or exit('Server error does not pass validation test.');

defined('C_RUN_TIME')    		or define('C_RUN_TIME',			TRUE);
defined('C_TIME')    	    	or define('C_TIME',		    	time());
// +--------------------------------------------------------------------------------------
// + 记录开始运行时间
// +--------------------------------------------------------------------------------------
defined('C_BEGIN_TIME')    		or define('C_BEGIN_TIME', 		microtime(TRUE));
// +--------------------------------------------------------------------------------------
// + 记录内存初始使用
// +--------------------------------------------------------------------------------------
defined('C_START_USE_MEMORY')	or define('C_START_USE_MEMORY',	memory_get_usage());
// +--------------------------------------------------------------------------------------
// + 定义入口开关
// +--------------------------------------------------------------------------------------
defined('C_APP_START')    		or define('C_APP_START', 		TRUE);
// +--------------------------------------------------------------------------------------
// + 版本信息
// +--------------------------------------------------------------------------------------
defined('C_VERSION')    		or define('C_VERSION',			'1.0.0');
// +--------------------------------------------------------------------------------------
// + 类文件后缀
// +--------------------------------------------------------------------------------------
defined('C_EXT')    			or define('C_EXT',				'.class.php');
defined('C_FUNC_EXT')    		or define('C_FUNC_EXT',				'.func.php');

// +--------------------------------------------------------------------------------------
// + 配置文件后缀
// +--------------------------------------------------------------------------------------
defined('C_INI_EXT')    		or define('C_INI_EXT',			'.php');
// +--------------------------------------------------------------------------------------
// + 目录分割线
// +--------------------------------------------------------------------------------------
defined('C_DIR_FIX')    		or define('C_DIR_FIX',			'/');

// +--------------------------------------------------------------------------------------
// + 系统常量定义
// + 框架目录
// +--------------------------------------------------------------------------------------
defined('C_SYC_XEMS_PATH')    	or define('C_SYC_XEMS_PATH',	dirname(dirname(__FILE__)).C_DIR_FIX);
defined('C_SYC_API_PATH') 		or define('C_SYC_API_PATH',   	C_SYC_XEMS_PATH.'Api'.C_DIR_FIX);
defined('C_SYS_CONFIG_PATH') 	or define('C_SYS_CONFIG_PATH',  C_SYC_XEMS_PATH.'Config'.C_DIR_FIX);
defined('C_SYS_INCLUDE_PATH') 	or define('C_SYS_INCLUDE_PATH', C_SYC_XEMS_PATH.'Include'.C_DIR_FIX);
defined('C_SYS_LIBRARY_PATH') 	or define('C_SYS_LIBRARY_PATH', C_SYC_XEMS_PATH.'Library'.C_DIR_FIX);
defined('C_SYS_FUNC_PATH') 		or define('C_SYS_FUNC_PATH',   	C_SYC_XEMS_PATH.'Function'.C_DIR_FIX);
defined('C_SYS_PLUG_PATH') 		or define('C_SYS_PLUG_PATH',   	C_SYC_XEMS_PATH.'Plugins'.C_DIR_FIX);//插件路径
defined('C_SYS_TPL_PATH') 		or define('C_SYS_TPL_PATH',   	C_SYC_XEMS_PATH.'SystemTpl'.C_DIR_FIX);

// +--------------------------------------------------------------------------------------
// + 应用名称
// +--------------------------------------------------------------------------------------
defined('C_APP_NAME')    		or define('C_APP_NAME',			'');
// +--------------------------------------------------------------------------------------
// + 应用路径
// +--------------------------------------------------------------------------------------
defined('C_APP_PATH')    		or define('C_APP_PATH',			dirname(C_SYC_XEMS_PATH).C_DIR_FIX );
// +--------------------------------------------------------------------------------------
// + 应用真实路径 C_APP_PATH + C_APP_NAME
// +--------------------------------------------------------------------------------------
defined('C_ROOT_PATH')    		or define('C_ROOT_PATH',		C_APP_PATH.C_APP_NAME.(C_APP_NAME?C_DIR_FIX:''));

defined('C_CACHE_PATH') 		or define('C_CACHE_PATH',   	C_ROOT_PATH.'Cache'.C_DIR_FIX);
defined('C_CONTROLLER_PATH') 	or define('C_CONTROLLER_PATH',  C_ROOT_PATH.'Controller'.C_DIR_FIX);
defined('C_COMMON_PATH') 		or define('C_COMMON_PATH',   	C_ROOT_PATH.'Common'.C_DIR_FIX);
defined('C_CONFIG_PATH') 		or define('C_CONFIG_PATH',   	C_COMMON_PATH.'Config'.C_DIR_FIX);
defined('C_MODEL_PATH') 		or define('C_MODEL_PATH',   	C_ROOT_PATH.'Model'.C_DIR_FIX);
defined('C_MODULE_PATH') 		or define('C_MODULE_PATH',   	C_ROOT_PATH.'Module'.C_DIR_FIX);
defined('C_VIEWS_PATH') 		or define('C_VIEWS_PATH',   	C_ROOT_PATH.'View'.C_DIR_FIX);

// +--------------------------------------------------------------------------------------
// + 定义常用服务器常量
// +--------------------------------------------------------------------------------------
defined('C_REQUEST_URI') 		or define('C_REQUEST_URI',   	isset($_SERVER['REQUEST_URI'])?$_SERVER['REQUEST_URI']:'');
defined('C_HTTP_HOST') 			or define('C_HTTP_HOST',   		isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'');
defined('C_SCRIPT_NAME') 		or define('C_SCRIPT_NAME',   	isset($_SERVER['SCRIPT_NAME'])?$_SERVER['SCRIPT_NAME']:'');

if(isset($_SERVER['PATH_INFO'])){
    $path_info = $_SERVER['PATH_INFO'];
}elseif(isset($_SERVER['ORIG_PATH_INFO'])){
    $path_info = $_SERVER['ORIG_PATH_INFO'];
}elseif (isset($_SERVER['DOCUMENT_URI']) && isset($_SERVER['REQUEST_URI'])){
    $start = strlen($_SERVER['DOCUMENT_URI']);
    $end = strpos($_SERVER['REQUEST_URI'],'?');
    $end = $end?$end:strlen($_SERVER['REQUEST_URI']);
    $path_info = substr($_SERVER['REQUEST_URI'],$start,$end-$start);
}else{
    $path_info = '';
}

defined('C_PATH_INFO') 			or define('C_PATH_INFO',   		$path_info);
defined('C_HTTP_REFERER') 		or define('C_HTTP_REFERER',   	isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'');
defined('C_DOCUMENT_ROOT') 		or define('C_DOCUMENT_ROOT',   	isset($_SERVER['DOCUMENT_ROOT'])?$_SERVER['DOCUMENT_ROOT']:'');

// +--------------------------------------------------------------------------------------
// + 设置系统访问url
// + 当前访问php脚本文件
// +--------------------------------------------------------------------------------------
defined('DOCUMENT_ROOT') 		or define('DOCUMENT_ROOT',   	(substr($_SERVER['DOCUMENT_ROOT'],-1)=='/')?$_SERVER['DOCUMENT_ROOT']:($_SERVER['DOCUMENT_ROOT'].'/'));
defined('HTTP_HOST') 			or define('HTTP_HOST',   		$_SERVER['HTTP_HOST']);
defined('C_CURR_PHP_FILE') 		or define('C_CURR_PHP_FILE',   	C_SCRIPT_NAME);
defined('C_CURR_PHP_FOLDER') 	or define('C_CURR_PHP_FOLDER', str_replace('\\',C_DIR_FIX, dirname(C_CURR_PHP_FILE)));

defined('C_ABSOLUT_URL') 		or define('C_ABSOLUT_URL',   	FALSE);
$_root_path = (C_CURR_PHP_FOLDER=='/' OR C_CURR_PHP_FOLDER=='\\' OR C_CURR_PHP_FOLDER=='')?'/':(C_CURR_PHP_FOLDER.C_DIR_FIX);
defined('C__ROOT__') 			or define('C__ROOT__', C_ABSOLUT_URL?('http://'.HTTP_HOST.$_root_path):$_root_path);
// +--------------------------------------------------------------------------------------
// + 定义网站根目录
// +--------------------------------------------------------------------------------------
defined('__ROOT__') 			or define('__ROOT__',  			C__ROOT__);

// +--------------------------------------------------------------------------------------
// + 资源路径
// +--------------------------------------------------------------------------------------
if( !defined('C_RES_URL') ){
	define('C_RES_URL', 	__ROOT__.C_APP_NAME.(C_APP_NAME?C_DIR_FIX:''));
	define('C_RES_PATH',	C_ROOT_PATH);
	define('C_RES_ROOT',	C_RES_PATH);
}else{
	define('C_RES_ROOT',	C_RES_URL);
}
// +--------------------------------------------------------------------------------------
// + 定义网站资源路径
// +--------------------------------------------------------------------------------------
defined('__PUBLIC__') 			or define('__PUBLIC__',   		C_RES_ROOT.'Public'.C_DIR_FIX);
defined('__CSS__') 				or define('__CSS__',  			__PUBLIC__.'Css'.C_DIR_FIX);
defined('__JS__') 				or define('__JS__',  			__PUBLIC__.'Js'.C_DIR_FIX);
defined('__IMG__') 				or define('__IMG__',  			__PUBLIC__.'Images'.C_DIR_FIX);
defined('__UPLOAD__') 			or define('__UPLOAD__',  		__PUBLIC__.'Upload'.C_DIR_FIX);
defined('__UPLOAD_PATH__') 		or define('__UPLOAD_PATH__',  	str_replace('http://'.HTTP_HOST.'/','',DOCUMENT_ROOT.substr(__PUBLIC__,1)).'Upload'.C_DIR_FIX);

// +--------------------------------------------------------------------------------------
// + 系统魔法常量转义检测信息
// +--------------------------------------------------------------------------------------
if(version_compare(PHP_VERSION,'5.4.0','<')) {
	ini_set('magic_quotes_runtime',0);
	define('C_MAGIC_QUOTES_GPC',get_magic_quotes_gpc()? true : false);
}else{
	define('C_MAGIC_QUOTES_GPC',false);
}

// +--------------------------------------------------------------------------------------
// + 是否是AJAX请求
// +--------------------------------------------------------------------------------------
define('C_IS_AJAX', ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')) ? true : false);
define('IS_AJAX', C_IS_AJAX);
// +--------------------------------------------------------------------------------------
// + 全局配置键值
// +--------------------------------------------------------------------------------------
defined('C')					or define('C', '__sys_config__');
// +--------------------------------------------------------------------------------------
// + 申明配置静态全局变量
// +--------------------------------------------------------------------------------------
$GLOBALS[C] = array();
// +--------------------------------------------------------------------------------------
// + 系统载入文件个数记录
// +--------------------------------------------------------------------------------------
$GLOBALS[C]['_SYA_LOAD_FILE_'][] = array('filename'=>__FILE__,'desc'=>'应用常量库文件');









