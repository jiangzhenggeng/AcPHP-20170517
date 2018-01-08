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
// + Release Date: 2015年11月4日 下午11:09:52
// +--------------------------------------------------------------------------------------
defined('C_CA') or exit('Server error does not pass validation test.');

// +--------------------------------------------------------------------------------------
// + 参数处理类，负责参数过滤，然后将参数绑定到_GLOBAL['_sys_cfg']全局变量上
// +--------------------------------------------------------------------------------------
class Param {
	
	// +----------------------------------------------------------------------------------
	// + 参数处理
	// +----------------------------------------------------------------------------------
	public static function init(){
		// +------------------------------------------------------------------------------
		// + 参数特殊字符进行转义
		// +------------------------------------------------------------------------------
		if ( !C_MAGIC_QUOTES_GPC ) {
			$_POST 		= ac_addslashes ( $_POST );
			$_GET 		= ac_addslashes ( $_GET );
			$_REQUEST 	= ac_addslashes ( $_REQUEST );
			$_COOKIE 	= ac_addslashes ( $_COOKIE );
		}
		
		// +------------------------------------------------------------------------------
		// + 载入默认路由路径
		// +------------------------------------------------------------------------------
		$route_config = & App::getConfig( 'Route','all',null);
		if($route_config===null){
			E('没有默认路由！',E_USER_ERROR);
		}
		// +------------------------------------------------------------------------------
		// + 注入默认路由全局变量
		// +------------------------------------------------------------------------------
		foreach (array('post','get') as $k => $v ){
			if (isset ( $route_config ['data'] [$v] ) && is_array ( $route_config ['data'] [$v] )) {
				foreach ( $route_config ['data'] [$v] as $_key => $_value ) {
					$_key = strtolower($_key);
					$v = strtolower($v);
					if($v=='post' && ! isset ( $_POST [$_key] )){
						$_POST[$_key] = $_value;
					}elseif($v=='get' && ! isset ( $_GET [$_key] )){
						$_GET[$_key] = $_value;
					}
				}
			}
		}

		$type = $route_config['url_model'];
		// +------------------------------------------------------------------------------
		// + 截取路由信息
		// + 过滤掉Url地址的后缀
		// +------------------------------------------------------------------------------
		if( $type==2 ){
			$extend_fix = '(\.php$)|(\.jsp$)|(\.html$)|(\.htm$)|(\.asp$)|(\.aspx$)';
			$pattern = '/(\.'.$route_config ['url_suffix'].'$)|('.$route_config ['url_suffix'].'$)|'.$extend_fix.'/i';
			$url = preg_replace($pattern,'',substr(C_PATH_INFO,1));
			
			if( substr($url, -1,1) == $route_config ['url_fix'] ){
				$url = substr($url,0,strlen($url)-1);
			}
			$url_array = explode($route_config ['url_fix'], $url);
			
			if( isset($url_array[0]) && ''!==$url_array[0] ){
				$_route_m = $url_array[0];
			}else{
				$_route_m = $route_config ['m'];
			}
			if( isset($url_array[1]) && ''!==$url_array[1] ){
				$_route_c = $url_array[1];
			}else{
				$_route_c = $route_config ['c'];
			}
			if( isset($url_array[2]) && ''!==$url_array[2] ){
				$_route_a = $url_array[2];
			}else{
				$_route_a = $route_config ['a'];
			}
			// +-------------------------------------------------------------------------
			// + 单个controller的优先级高
			// +-------------------------------------------------------------------------
			$_curr_controller = C_CONTROLLER_PATH.ucfirst($_route_m).'Action'.C_EXT;
		}elseif( $type==1 ){
			// +---------------------------------------------------------------------
			// + 兼容模式
			// + 可以自定义模块，控制器，方法别名
			// +---------------------------------------------------------------------	
			if(xInput::request($route_config['m_alias'],'')!=''){
				$_route_m = xInput::request($route_config['m_alias'],'');
			}else{
				$_route_m = $route_config ['m'];
			}		
			if(xInput::request($route_config['c_alias'],'')!=''){
				$_route_c = xInput::request($route_config['c_alias'],'');
			}else{
				$_route_c = $route_config ['c'];
			}
			if(xInput::request($route_config['a_alias'],'')!=''){
				$_route_a = xInput::request($route_config['a_alias'],'');
			}else{
				$_route_a = $route_config ['a'];
			}
			$_curr_controller = C_CONTROLLER_PATH.ucfirst($_route_c).'Action'.C_EXT;
		}
		
		$add_key = 2;
		if(!file_exists($_curr_controller)){
			$add_key += 1;
		}
		// +-------------------------------------------------------------------------
		// + 注入GET参数
		// +-------------------------------------------------------------------------
		if( $type==2 /*|| C_PATH_INFO!=''*/ ){
			for ($i=$add_key;$i<count($url_array);$i +=2 ){
				if(isset($url_array[$i])){
					$_GET[$url_array[$i]] = isset($url_array[$i+1])?$url_array[$i+1]:'';
				}
			}
		}
		
		// +------------------------------------------------------------------------------
		// + 路由解析
		// +------------------------------------------------------------------------------
		if($route_config['url_route']){
			Route::routeAnalysis($route_config);
		}
		// +------------------------------------------------------------------------------
		// + 定义当前访问模块
		// + 单模块的访问权限优先
		// +------------------------------------------------------------------------------
		if(!file_exists($_curr_controller)){
			define ( 'C_CURR_MODULE_PATH', C_MODULE_PATH.ucfirst($_route_m).C_DIR_FIX );
			// 当前模块公共类路径
			define ( 'C_CURR_COMMON_PATH', C_CURR_MODULE_PATH.'Common'.C_DIR_FIX );
			// 当前模块配置文件路径
			define ( 'C_CURR_CONFIG_PATH', C_CURR_COMMON_PATH.'Config'.C_DIR_FIX );
			// 一个模块包
			define ( 'C_ROUTE_M', ucfirst ($_route_m ) );
			define ( 'C_ROUTE_C', ucfirst ($_route_c ) );
			define ( 'C_ROUTE_A', strtolower ($_route_a ) );
			App::setConfig('Route', 'm', C_ROUTE_M );
			App::setConfig('Route', 'c', C_ROUTE_C );
			App::setConfig('Route', 'a', C_ROUTE_A );
			
		}else{
			// 只有控制器
			if( $type==2 ){
				define ( 'C_ROUTE_C', ucfirst ($_route_m) );
				define ( 'C_ROUTE_A', strtolower ($_route_c) );
			}else{
				define ( 'C_ROUTE_C', ucfirst ($_route_c) );
				define ( 'C_ROUTE_A', strtolower ($_route_a) );
			}
			App::setConfig('Route', 'c', C_ROUTE_C );
			App::setConfig('Route', 'a', C_ROUTE_A );
		}
		return true;
	}
}









