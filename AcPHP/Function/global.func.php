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
// + Release Date: 2015年11月8日 下午10:29:33
// +--------------------------------------------------------------------------------------
defined('C_CA') or exit('Server error does not pass validation test.');

// +--------------------------------------------------------------------------------------
// + 定向到某个页面
// +--------------------------------------------------------------------------------------
function redirect($page=''){
	
}

function ac_is_mobile(){
	$regex_match="/(nokia|iphone|android|motorola|^mot\-|softbank|foma|docomo|kddi|up\.browser|up\.link|";
	$regex_match.="htc|dopod|blazer|netfront|helio|hosin|huawei|novarra|CoolPad|webos|techfaith|palmsource|";
	$regex_match.="blackberry|alcatel|amoi|ktouch|nexian|samsung|^sam\-|s[cg]h|^lge|ericsson|philips|sagem|wellcom|bunjalloo|maui|";
	$regex_match.="symbian|smartphone|midp|wap|phone|windows ce|iemobile|^spice|^bird|^zte\-|longcos|pantech|gionee|^sie\-|portalmmm|";
	$regex_match.="jig\s browser|hiptop|^ucweb|^benq|haier|^lct|opera\s*mobi|opera\*mini|320x320|240x320|176x220";
	$regex_match.=")/i";
	return isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE']) or preg_match($regex_match, strtolower($_SERVER['HTTP_USER_AGENT']));
}

/**
* 将字符串转换为数组
*
* @param	string	$data	字符串
* @return	array	返回数组格式，如果，data为空，则返回空数组
*/
function string2array($data) {
	$data = trim($data);
	if($data == '') return array();
	@eval("\$array = $data;");
	return $array;
}
/**
* 将数组转换为字符串
*/
function array2string($data) {
	if($data == '' || empty($data)) return '';
	return var_export($data,true);
}

// +--------------------------------------------------------------------------------------
// + 编译文件
// + @param string $filename 文件名
// + @return string
// +--------------------------------------------------------------------------------------
function ac_compile($filename,$config_file=false) {
    $content    =   php_strip_whitespace($filename);
    //$content    =   file_get_contents($filename);
    $content    =   trim(substr($content, 5));
    if ('?>' == substr($content, -2)){
        $content = substr($content, 0, -2);
    }
    if($config_file){
        $function_name = '___config_function___'.md5($filename);
        $content = $function_name.'();function '.$function_name.'(){'.$content.'};';
    }
    return $content;
}
// +--------------------------------------------------------------------------------------
// + 自定义调试模式函数
// +--------------------------------------------------------------------------------------
function ac_debug($error_reporting=E_ALL){
	ini_set("display_errors", 1);
	error_reporting( $error_reporting );
}
// +--------------------------------------------------------------------------------------
// + 字符串截取函数
// +--------------------------------------------------------------------------------------
function cutStr($str, $len = 20, $c = '...', $char = 'utf8') {
	$str = strip_tags ( $str );
	$_len = mb_strlen ( $str, $char );
	if ($_len > $len) {
		return mb_substr ( $str, 0, $len, $char ) . $c;
	} else {
		return $str;
	}
}
// +--------------------------------------------------------------------------------------
// + 将数组的键值转换为小写
// +--------------------------------------------------------------------------------------
function &ac_array_change_key_case(array $array){
	if (!is_array($array)){
		return $array;
	}
	foreach ($array as $k => $v ){
		if (is_array($v)){
			$array[$k] = ac_array_change_key_case($v);
		}
	}
	$array = array_change_key_case($array);
	return $array;
}

// +--------------------------------------------------------------------------------------
// + 将两个数组循环进行合并
// + 将数组2合并到数组1，数组2会覆盖数组1的重复值
// +--------------------------------------------------------------------------------------
function &ac_array_merge(array $array1,array $array2){
	if(count($array2)<=0){
		return $array1;
	}
	foreach ($array2 as $k => $v ){
		if(!isset($array1[$k])){
			$array1[$k] = $array2[$k];
			continue;
		}
		if(is_array($array2[$k])){
			$array1[$k] = ac_array_merge($array1[$k],$array2[$k]);
		}else{
			$array1[$k] = $array2[$k];
		}
	}
	return $array1;
}
// +--------------------------------------------------------------------------------------
// + 优化require_once方法
// + @return string 载入文件路径
// +--------------------------------------------------------------------------------------
function ac_require_once($file){
	static $_include_file_array = array();
	$file_md5 = md5($file);
	// +----------------------------------------------------------------------------------
	// + 如果已经载入过就直接返回
	// +----------------------------------------------------------------------------------
	if (!empty($_include_file_array[$file_md5])) {
		return $_include_file_array[$file_md5];
	}
	// +----------------------------------------------------------------------------------
	// + 如果没有载入过，并且文件存在
	// +----------------------------------------------------------------------------------
	if (file_exists($file)) {
		$_include_file_array[$file_md5] = include $file;
		return $_include_file_array[$file_md5];
	}
	return false;
}

// +--------------------------------------------------------------------------------------
// + 实例化Model类
// + @param string $model
// + @param array $_database 自定义数据库配置项
// + @return Object Model
// +--------------------------------------------------------------------------------------
function M( $table, $database=NULL) {
	$table = trim($table);
	if($table==''){
		trigger_error('使用 M 方法必须传入表名！',E_USER_ERROR);
		exit;
	}
	$explode_table = explode(' ',$table);
	$xModel = NULL;
	if( $table!='' ){
		$explode_table = explode('_',str_replace('`','',$explode_table[0]));
		$implode_table = '';
		foreach($explode_table as $v){
			$implode_table .= ucfirst($v);
		}
		$model = ucfirst($implode_table).'Model';
		if(class_exists($model)){
			$xModel= new $model($database);
		}else {
			$xModel = new xModel($database);
		}
	}else{
		$xModel = new xModel($database);
	}
	return $xModel->clearWhere()->table($table);
}

// +--------------------------------------------------------------------------------------
// + 错误处理通用方法
// + @param string $错误信息
// + @param array $level 错误级别
// +--------------------------------------------------------------------------------------
function E( $error_message='系统错误', $level=E_USER_ERROR) {
	App::outError($error_message,$level);
	exit;
}
// +--------------------------------------------------------------------------------------
// + 返回经addslashes处理过的字符串或数组
// + @param $string 需要处理的字符串或数组
// + @return string
// +--------------------------------------------------------------------------------------
function ac_addslashes($string){
	if ( is_string($string) ) {
		$string = addslashes($string);
	}elseif ( is_array($string) ){
		foreach($string as $key => $val){
			$string[$key] = ac_addslashes($val);
		}
	}elseif( is_object($string) ) {
		foreach($string as $key => $val){
			$string->$key = ac_addslashes($val);
		}
	}
	return $string;
}

// +--------------------------------------------------------------------------------------
// + 返回经stripslashes处理过的字符串或数组
// + @param $string 需要处理的字符串或数组
// + @return string
// +--------------------------------------------------------------------------------------
function ac_stripslashes($string) {	
	if ( is_string($string) ) {
		$string = stripslashes($string);
	}elseif ( is_array($string) ){
		foreach($string as $key => $val){
			$string[$key] = ac_stripslashes($val);
		}
	}elseif( is_object($string) ) {
		foreach($string as $key => $val){
			$string->$key = ac_stripslashes($val);
		}
	}
	return $string;
}

// +--------------------------------------------------------------------------------------
// + 过滤危险字符
// + @param $string
// + @return string
// +--------------------------------------------------------------------------------------
function ac_safe_replace($string) {
	if ( is_array($string) ) {
		foreach ($string as $k => $v ){
			$string[$k] = ac_safe_replace($v);
		}
	}elseif ( is_object($string) ){
		foreach ($string as $k => $v ){
			$string->$k = ac_safe_replace($v);
		}
	}else {
		$string = str_replace('%20','',$string);
		$string = str_replace('%27','',$string);
		$string = str_replace('%2527','',$string);
		$string = str_replace('*','',$string);
		$string = str_replace('"','&quot;',$string);
		$string = str_replace("'",'',$string);
		$string = str_replace('"','',$string);
		$string = str_replace(';','',$string);
		$string = str_replace('<','&lt;',$string);
		$string = str_replace('>','&gt;',$string);
		$string = str_replace("{",'',$string);
		$string = str_replace('}','',$string);
		$string = str_replace('\\','',$string);
	}
	return $string;
}

// +--------------------------------------------------------------------------------------
// + 获取请求ip
// + @return string ip地址
// +--------------------------------------------------------------------------------------
function ac_getIp() {
	$ip = $_SERVER['REMOTE_ADDR'];
    if (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
        foreach ($matches[0] AS $xip) {
            if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
                $ip = $xip;
                break;
            }
        }
    }
    return $ip;
}

// +--------------------------------------------------------------------------------------
// + 取得操作系统类型
// + @return string 
// +--------------------------------------------------------------------------------------
function ac_getUseSys() {
	$phpos = explode ( " ", php_uname () );
	$sys = $phpos [0] . "&nbsp;" . $phpos [1];
	if (empty ( $phpos [0] )) {
		$sys = "---";
	}
	return $sys;
}
// +--------------------------------------------------------------------------------------
// + 是否运行于安全模式
// + @return bool
// +--------------------------------------------------------------------------------------
function ac_getPhpSafemod() {
	$phpsafemod = get_cfg_var ( "safe_mode" );
	if ($phpsafemod == 1) {
		$word = true;
	} else {
		$word = false;
	}
	return $word;
}
// +--------------------------------------------------------------------------------------
// + 转换大小
// + @return string
// +--------------------------------------------------------------------------------------
function ac_changeSize($size) {
	if ($size < 1024) {
		$str = $size . " B";
	} elseif ($size < 1024 * 1024) {
		$str = round ( $size / 1024, 2 ) . " KB";
	} elseif ($size < 1024 * 1024 * 1024) {
		$str = round ( $size / (1024 * 1024), 2 ) . " MB";
	} else {
		$str = round ( $size / (1024 * 1024 * 1024), 2 ) . " GB";
	}
	return $str;
}

/**
 * @param $sTime
 * @param string $formate
 * @return mixed|string
 * 序列化时间
 */
function ac_serialize_date($sTime,$formate = '%d天%h小时%i分%m秒') {
	if (!$sTime) return '';
	$hasTime = $sTime -time();
	$d = floor($hasTime / (60 * 60 * 24));
	$h = floor($hasTime / (60 * 60)) - ($d * 24);
	$i = floor($hasTime / 60) - ($d * 24 * 60) - ($h * 60);
	$m = floor($hasTime) - ($d * 24 * 60 * 60) - ($h * 60 * 60) - ($i * 60);
	if($d<=0){
		$formate = preg_replace('/%d(.*?)%h/i','%h',$formate);
	}
	if($h<=0){
		$formate = preg_replace('/%h(.*?)%i/i','%i',$formate);
	}
	$f_time = str_replace(array('%d','%h','%i','%m'),array($d,$h,$i,$m),$formate);
	return $f_time;
}

// +--------------------------------------------------------------------------------------
// + 使用导入的方式载入插件块
// + @param unknown $class		插件名
// + @param string $initialize	是否实例化
// + @param string $param			实例化需要传递的参数
// +--------------------------------------------------------------------------------------
function ac_import($class, $initialize =false,$param='') {
	$class = trim($class);
	static $_file = array();
	if (substr($class, -2)=='.*') {
		//导入一个完整的插件包
		$class_path = C_SYS_PLUG_PATH.substr($class, 0, strlen($class)-2).C_DIR_FIX;
		if (!is_dir($class_path)) {
			exit('插件<font color=red>包</font>：'.C_SYS_PLUG_PATH.'<font color=red>'.$class.'</font>不存在!');
		}
		App::loadFunc('dir','sys');
		// +------------------------------------------------------------------------------
		// + 列表插件包下的所有文件
		// +------------------------------------------------------------------------------
		$class_files = dir_list($class_path,'php');
		$flsge = true;
		foreach ($class_files as $k => $v ){
			if (file_exists($v)) {
				ac_require_once($v);
			}else{
				$flsge = false;
			}
		}
		if($flsge===false){
			exit('插件载入不完整！');
		}
		return true;
	}
	// +------------------------------------------------------------------------------
	// + 支持.或/的方式导入
	// +------------------------------------------------------------------------------
	$class = str_replace('.', '/', $class);
	$class_strut = explode('/', $class);
	$class_name = $class_strut[0];
	if( count($class_strut)>0 ){
		$class_path_1 = C_SYS_PLUG_PATH.$class_strut[0].'/'.implode('/', $class_strut).C_EXT;
		$class_path_2 = C_SYS_PLUG_PATH.implode('/', $class_strut).C_EXT;
	}
	if( file_exists($class_path_1) ){
		$class_path = $class_path_1;
	}elseif( file_exists($class_path_2) ) {
		$class_path = $class_path_2;
	}else{
		exit('插件：'.$class_path_1.'不存在');
	}
	$class_Md5 = md5($class_path);
	if (isset($_file[$class_Md5])){
		return $_file[$class_Md5];
	}
	$_file[$class_Md5] = true;
	ac_require_once($class_path);
	if( $initialize ){
		if($param){
			$_file[$class_Md5] = new $class_name($param);
		}else {
			$_file[$class_Md5] = new $class_name();
		}
	}
	return $_file[$class_Md5];
}

// +--------------------------------------------------------------------------------------
// + 创建URL地址
// + @param array || string $action	定位模块名
// + @param array $action			定位模块名
// + @param unknown $type			附加参数传递方式
// + @return string
// +--------------------------------------------------------------------------------------
function U($action=array(),$param=array()){
	if(is_array($action) && count($action)<=0) return C_REQUEST_URI;
	if($action=='/') return '/';
	
	// +----------------------------------------------------------------------------------
	// + 获取路由配置信息
	// +----------------------------------------------------------------------------------
	$load_config = App::getConfig('Route','all');
	// +----------------------------------------------------------------------------------
	// + url分隔符
	// +----------------------------------------------------------------------------------
	$_url_fix = $load_config['url_fix']?$load_config['url_fix']:'/';
	// +----------------------------------------------------------------------------------
	// + 查看是否存在 C_ROUTE_M
	// +----------------------------------------------------------------------------------
	$_r_m = defined('C_ROUTE_M')?(C_ROUTE_M.$_url_fix):'';
	
	if(is_string($action) || is_null($action)){
		// +------------------------------------------------------------------------------
		// + 提供/或.的方式分隔模块
		// +------------------------------------------------------------------------------
		$action = str_replace( array('.',' '), array('/',''), $action);
		if(substr($action, 0,1)=='/'){
			$is_root_url = true;
		}else{
			$is_root_url = false;
		}
		$action = (substr($action, 0,1)=='/')?substr($action, 1):$action;
		
		
		if($action==''){
			$action = $_r_m.C_ROUTE_C.$_url_fix.C_ROUTE_A;
		}else{
			$action = explode('/', $action);
			foreach($action as $k => $v){
				if($v==='') unset($action[$k]);
			}
			if(count($action)==1){
				$action = $is_root_url?ucfirst($action[0]):($_r_m.C_ROUTE_C.$_url_fix.$action[0]);
			}elseif(count($action)==2){
				$action = $is_root_url?(ucfirst($action[0]).$_url_fix.$action[1]):($_r_m.ucfirst($action[0]).$_url_fix.$action[1]);
			}else {
				$_has_parme_url = ucfirst($action[0]).$_url_fix.ucfirst($action[1]).$_url_fix.$action[2];
				if(count($action)>3){
					unset($action[0]);unset($action[1]);unset($action[2]);
					$_has_parme_url .= $_url_fix.implode($_url_fix,$action);
				}
				$action = $_has_parme_url;
			}
		}
	}elseif (is_array($action)){
		$_temp = array('m'=>'');
		// +------------------------------------------------------------------------------
		// + 如果存在模块包
		// +------------------------------------------------------------------------------
		if(defined(C_ROUTE_M)){
			$_temp['m'] = isset($action['m'])?$action['m']:
					(count($action)>=3?(isset($action['0'])?$action['0']:
							C_ROUTE_M):C_ROUTE_M);
		}
		// +------------------------------------------------------------------------------
		// + 获取模块控制器
		// +------------------------------------------------------------------------------
		$_temp['c'] = isset($action['c'])?$action['c']:(count($action)>=2?(isset($action['1'])?$action['1']:C_ROUTE_C):C_ROUTE_C);
		// +------------------------------------------------------------------------------
		// + 获取方法
		// +------------------------------------------------------------------------------
		$_temp['a'] = isset($action['a'])?$action['a']:(count($action)==1?(isset($action['0'])?$action['0']:C_ROUTE_A):
						(count($action)==2?(isset($action['1'])?$action['1']:C_ROUTE_A):
						(count($action)>=3?(isset($action['2'])?$action['2']:C_ROUTE_A):C_ROUTE_A)));
		$action = ucfirst($_temp['m']).$_url_fix.ucfirst($_temp['c']).$_url_fix.$_temp['a'];
	}
	
	// +----------------------------------------------------------------------------------
	// + 参数绑定
	// +----------------------------------------------------------------------------------
	$_temp_param = '';
	// URL编码模式1为兼容模式，2为PATHINFO模式
	$type = $load_config['url_model'];
	if (is_array($param)) {
		foreach ($param as $k => $v ){
			if( $type==1 || $type!=2 ){
				$_temp_param .= '&'.$k.'='.$v;
			}else{
				if($k!='' && $v!='')
					$_temp_param .= $_url_fix.$k.$_url_fix.$v;
			}
		}
		$_temp_param = substr($_temp_param, 1);
	}
	// +----------------------------------------------------------------------------------
	// + 参数组装成url地址
	// + 开启兼容模式
	// +----------------------------------------------------------------------------------
	if( $type==1 || $type!=2 ){
		$temp_action = explode($_url_fix, $action);
		foreach ($temp_action as $k=>$v ){
			if($k==0){
				$action = $load_config['m_alias'].'='.$v.'&';
			}elseif($k==1){
				$action .= $load_config['c_alias'].'='.$v.'&';
			}elseif($k==2){
				$action .= $load_config['a_alias'].'='.$v;
			}
		}
		$action = $action.($_temp_param!=''?('&'.$_temp_param):'');
	}else{
		$action = $action.($_temp_param!=''?($_url_fix.$_temp_param):'').$load_config['url_suffix'];
	}
	// +----------------------------------------------------------------------------------
	// + 是否开启url重写方式
	// +----------------------------------------------------------------------------------
	$_root = ($load_config['url_rewrite'] && $load_config['url_model']==2)?C__ROOT__:(C__ROOT__.'index.php'.( ( $type==1 || $type!=2 )?'?':'/'));
	return $_root.$action;
}

// +--------------------------------------------------------------------------------------
// + 增强explode函数
// + @param $string		目标分隔符，如果是空则可以将字符串按单字符分隔
// + @param $string2	分隔目标字符串
// +--------------------------------------------------------------------------------------
function my_explode($string,$string2 ) {
	if( !is_string($string2) ){
		return array();
	}
	if( strlen($string)>0 ){
		return explode($string, $string2);
	}else{
		$l = strlen($string2);
		$temp = array();
		for ($i=0;$i<$l;$i++){
			$temp[] = $string2{$i};
		}
		return $temp;
	}
}







