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
// + Release Date: 2015年11月8日 下午9:07:19
// +--------------------------------------------------------------------------------------

defined('C_CA') or exit('Server error does not pass validation test.');

class AcDir{
	
	public static function init(){
		if(is_file(C_CONFIG_PATH.'Lock')){
			return true;
		}
		$default_m = App::getConfig('Route', 'm','Index');
		$default_c = App::getConfig('Route', 'c','Index');
		$default_a = App::getConfig('Route', 'a','init');
		$_create_dir_array = array(
				C_CACHE_PATH,
				C_CACHE_PATH.'Cache_log',
				C_CACHE_PATH.'Cache_tpl',
				C_CACHE_PATH.'Run_time',
				C_CONTROLLER_PATH,
				C_COMMON_PATH,
				C_CONFIG_PATH,
				C_COMMON_PATH.'Function',
				C_MODEL_PATH,
				C_MODULE_PATH,
				C_MODULE_PATH.$default_m,
				C_MODULE_PATH.$default_m.'/Template',
				C_MODULE_PATH.$default_m.'/Common',
				C_MODULE_PATH.$default_m.'/Common/Config',
				C_MODULE_PATH.$default_m.'/Common/Function',
				C_VIEWS_PATH,
				C_VIEWS_PATH.'Default/'.$default_m,
		);
		if( defined('C_RES_PATH') ){
			$_create_dir_array[] = C_RES_PATH;
			$_create_dir_array[] = C_RES_PATH.'Css';
			$_create_dir_array[] = C_RES_PATH.'Js';
			$_create_dir_array[] = C_RES_PATH.'Images';
			$_create_dir_array[] = C_RES_PATH.'Upload';
		}
		foreach ($_create_dir_array as $k => $v ){
			if(!is_dir($v)){
				dir_create($v);
			}
		}
		
		$_create_file_array = array(
				C_CONFIG_PATH.'Config'.C_INI_EXT=>file_get_contents(C_SYS_CONFIG_PATH.'Config'.C_INI_EXT),
				C_COMMON_PATH.'Function/global'.C_FUNC_EXT=>'<?php'.PHP_EOL.'//改文件加下的函数库文件会自动加载到全局'.PHP_EOL.'?>',
				C_MODULE_PATH.$default_m.'/Common/Function/global'.C_FUNC_EXT=>'<?php'.PHP_EOL.'//改文件加下的函数库文件会自动加载到'.$default_m.'模块'.PHP_EOL.'?>',
				C_CONFIG_PATH.'Route'.C_INI_EXT=>file_get_contents(C_SYS_CONFIG_PATH.'Route'.C_INI_EXT),
				C_CONFIG_PATH.'View'.C_INI_EXT=>file_get_contents(C_SYS_CONFIG_PATH.'View'.C_INI_EXT),
				C_CONFIG_PATH.'Database'.C_INI_EXT=>file_get_contents(C_SYS_CONFIG_PATH.'Database'.C_INI_EXT),
				C_COMMON_PATH.'Common'.C_EXT=>'<?php'.PHP_EOL.'class Common extends Action{'.PHP_EOL.'}'.PHP_EOL.'?>',

				C_MODULE_PATH.$default_m.'/Common/Config/Config'.C_INI_EXT=>'<?php'.PHP_EOL.'return array();'.PHP_EOL.'?>',
				C_MODULE_PATH.$default_m.'/Common/'.$default_m.'Common'.C_EXT=>'<?php'.PHP_EOL.'class '.$default_m.'Common extends Common {'.PHP_EOL.'}'.PHP_EOL.'?>',
				
				C_MODULE_PATH.$default_m.'/'.$default_c.'Action'.C_EXT=>str_replace(array('{c}','{a}'),array($default_c,$default_a),file_get_contents(C_SYS_TPL_PATH.'default_class.tpl')),
				C_MODULE_PATH.$default_m.'/Template/index.html'=>file_get_contents(C_SYS_TPL_PATH.'default_views.tpl'),
				C_VIEWS_PATH.'Default/'.$default_m.'/index.html'=>file_get_contents(C_SYS_TPL_PATH.'default_views.tpl'),
				C_CONFIG_PATH.'Lock'=>'',
		);
		foreach ($_create_file_array as $k => $v ){
			if(!file_exists($k)){
				file_put_contents($k, $v);
				chmod($k, 0777);
			}
		}
	}
}














