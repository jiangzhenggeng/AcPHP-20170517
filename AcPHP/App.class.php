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
// + Author: AcPHP http://www.jiangzg.com/ <2992265870@qq.com/jiangzg>
// + Release Date: 2015年11月4日 下午11:09:52
// +--------------------------------------------------------------------------------------
defined ( 'C_CA' ) or exit ( 'Server error does not pass validation test.' );

// +--------------------------------------------------------------------------------------
// + 程序基础引导类
// + 自动加载，导入配置文件，载入函数库，异常处理，错误处理，运行日志管理
// +--------------------------------------------------------------------------------------
class App {
	
	static private $runTimeConfig = array();

    static private $core_path = array();
	// +----------------------------------------------------------------------------------
	// + 创建应用
	// +----------------------------------------------------------------------------------
	static public function createApp() {

		// +------------------------------------------------------------------------------
		// + 信息提示模式设置
		// +------------------------------------------------------------------------------
		if (App::getConfig ( 'Config', 'debug' )) {
			ini_set ( "display_errors", 1 );
			error_reporting ( E_ALL );
		} else {
			ini_set ( "display_errors", 0 );
			error_reporting ( 0 );
		}
		// +------------------------------------------------------------------------------
		// + 设置系统编码
		// +------------------------------------------------------------------------------
		header ( 'Content-type: text/html;charset=' . App::getConfig ( 'Config', 'charset' ) );
		defined('__CHARSET__') OR define('__CHARSET__',App::getConfig ( 'Config', 'charset' ));
		// +------------------------------------------------------------------------------
		// + 设置时间戳
		// +------------------------------------------------------------------------------
		date_default_timezone_set ( App::getConfig ( 'Config', 'timezone' ) );
		
		// +------------------------------------------------------------------------------
		// + 开启url重写，隐藏index.php文件
		// +------------------------------------------------------------------------------
		if (App::getConfig ( 'Route', 'url_rewrite' ) && ! file_exists ( C_ROOT_PATH . '.htaccess' )) {
			file_put_contents ( C_ROOT_PATH . '.htaccess', file_get_contents ( C_SYS_TPL_PATH . 'htaccess.html' ) );
		}

        // +------------------------------------------------------------------------------
        // + 初始化系统核心事件绑定
        // +------------------------------------------------------------------------------
        xLibEvent::listenEvents();

        // +------------------------------------------------------------------------------
		// + 是否默认开启session
		// +------------------------------------------------------------------------------
		
		if (App::getConfig ( 'Session', 'session_auto_start' ))
			xSession::start ();
		// +------------------------------------------------------------------------------
		// + 初始化应用
		// +------------------------------------------------------------------------------
		$aplication = new Application ();
		// +------------------------------------------------------------------------------
		// + 自动载入用户函数库
		// +------------------------------------------------------------------------------
		self::_loadAutoFunc();
		// +------------------------------------------------------------------------------
		// + 运行应用
		// +------------------------------------------------------------------------------
		$aplication->init ();
	}

    static public function hasRunTime2($e) {
        echo $e.'=';
    }
	
	
	// +----------------------------------------------------------------------------------
	// + 判断是否有运行环境存在
	// +----------------------------------------------------------------------------------
	static public function hasRunTime() {
		if( is_file(C_CACHE_PATH.'Run_time/~runtime.php') ){
			return true;
		}
		return false;
	}
	
	// +----------------------------------------------------------------------------------
	// + 创建运行环境存
	// +----------------------------------------------------------------------------------
	static public function createRunTime() {
		self::loadClass('AcDir',C_SYC_XEMS_PATH);
		self::loadClass('Action',C_SYC_XEMS_PATH);
		self::loadClass('Application',C_SYC_XEMS_PATH);
		self::loadClass('Param',C_SYC_XEMS_PATH);
		
		// +--------------------------------------------------------------------------------------
		// + 载入全局函数库
		// +--------------------------------------------------------------------------------------
		self::loadFunc('global','sys');
		self::loadFunc('dir','sys');
		self::loadFunc('date','sys');
		self::loadFunc('password','sys');
		
		// +--------------------------------------------------------------------------------------
		// + 载入SESSION
		// +--------------------------------------------------------------------------------------
		self::loadClass('xSession', C_SYS_LIBRARY_PATH);
		self::loadClass('xCache', C_SYS_LIBRARY_PATH);
		self::loadClass('xModel', C_SYS_LIBRARY_PATH);
		self::loadClass('xDB', C_SYS_LIBRARY_PATH);
		self::loadClass('xTool', C_SYS_LIBRARY_PATH);
		self::loadClass('xInput', C_SYS_LIBRARY_PATH);
		self::loadClass('xLog', C_SYS_LIBRARY_PATH);
		self::loadClass('xOut', C_SYS_LIBRARY_PATH);
		self::loadClass('xPage', C_SYS_LIBRARY_PATH);
		self::loadClass('xTemplate', C_SYS_LIBRARY_PATH);
		self::loadClass('xValidateCode', C_SYS_LIBRARY_PATH);
		
		// +------------------------------------------------------------------------------
		// + 参数处理
		// +------------------------------------------------------------------------------
		Param::init ();
		
		// +------------------------------------------------------------------------------
		// + 检测目录并创建相应的目录和文件
		// +------------------------------------------------------------------------------
		AcDir::init ();
		
		self::loadClass('Common', C_COMMON_PATH);
		
		if(!C_RUN_TIME) return TRUE;
		
		$runTimeString = 
		$runTimeConfigAll = 
		$runTimeConfigRoute = 
		$runTimeConfigLog = 
		$runTimeConfigTemplate = '';
		
		foreach(self::$runTimeConfig as $file){
			$runTimeString .= ac_compile($file);
			foreach($GLOBALS [C]['_SYA_LOAD_FILE_'] as $k => $v){
				if($file==$v['filename']){
					unset($GLOBALS [C]['_SYA_LOAD_FILE_'][$k]);
				}
			}
		}
        
		// +--------------------------------------------------------------------------------------
		// + 载入配置项
		// +--------------------------------------------------------------------------------------
		$list_config_dir = array(C_SYS_CONFIG_PATH,C_CONFIG_PATH);

		foreach($list_config_dir as $config_dir ){
			$list_config_file = dir_list($config_dir,'php');
			foreach($list_config_file as $file_name ){
				$file_name = explode('/',$file_name);
				$file_name = explode('.',$file_name[count($file_name)-1]);
				$config_name = $file_name[0];
				$file_name = $config_dir.ucfirst ( trim ( $file_name[0] ) ).C_INI_EXT;
				$key = md5 ($file_name);
				if(ucfirst ( trim ($config_name) )=='Route' || ucfirst ( trim ($config_name) )=='Log'){
					$runTimeConfigRoute .= '$GLOBALS[C][\''.$key.'_runtime\'] = '.ac_compile($file_name,true);
				}elseif(ucfirst ( trim ($config_name) )=='Template'){
					$runTimeConfigTemplate .= '$GLOBALS[C][\''.$key.'_runtime\'] = '.ac_compile($file_name,true);
				}else{
					$runTimeConfigAll .= '$GLOBALS[C][\''.$key.'_runtime\'] = '.ac_compile($file_name,true);
				}
				foreach($GLOBALS [C]['_SYA_LOAD_FILE_'] as $k => $v){
					if($file_name==$v['filename']){
						unset($GLOBALS [C]['_SYA_LOAD_FILE_'][$k]);
					}
				}
			}
		}
		
		$runTimeString .= $runTimeConfigRoute.'Param::init ();'.$runTimeConfigAll.$runTimeConfigTemplate;
		dir_create(C_CACHE_PATH.'Run_time',0777);
		file_put_contents(C_CACHE_PATH.'Run_time/~runtime.php','<?php '.$runTimeString.'?>');
	}
	
	// +----------------------------------------------------------------------------------
	// + 启动运行环境存
	// +----------------------------------------------------------------------------------
	static public function getRunTime() {
		include C_CACHE_PATH.'Run_time/~runtime.php';
	}
	
	// +--------------------------------------------------------------------------------------
	// + 载入配置文件全局静态方法函数
	// + 所有配置项存放到$GLOBALS[C]下
	// +--------------------------------------------------------------------------------------
	static public function &getConfig($con_file, $con_key='', $default = NULL, $host=NULL) {
		$con_file = ucfirst( trim ( $con_file ));
		
		$sys_key = md5 ( C_SYS_CONFIG_PATH . $con_file . C_INI_EXT.$host );
		if(!isset($GLOBALS [C] [$sys_key])){
			
			$funcGetKey = function($array, $host){
				$array = ac_array_change_key_case ( $array );
				if(is_null($host)){
					$array = isset ( $array [C_HTTP_HOST] ) ? $array [C_HTTP_HOST] : (isset ( $array ['default'] ) ? $array ['default'] : array ());
				}else{
					$array = isset($array[strtolower($host)])?$array[strtolower($host)]:(isset ( $array ['default'] ) ? $array ['default'] : array ());
				}
				return $array;
			};
			
			$array = $config_path = array();
			$key = md5 ( C_SYS_CONFIG_PATH . $con_file . C_INI_EXT.$host );
			if ( !isset($GLOBALS [C] [$key.'_runtime']) ) {
				// 首先载入系统默认配置
				$config_path [] = C_SYS_CONFIG_PATH . $con_file . C_INI_EXT;
			}else{
				$array = ac_array_merge($array,$funcGetKey($GLOBALS [C] [$key.'_runtime'],$host));
				unset($GLOBALS [C] [$key.'_runtime']);
			}

			$key = md5 ( C_CONFIG_PATH . $con_file . C_INI_EXT.$host );
			if ( !isset($GLOBALS [C] [$key.'_runtime']) ) {
				// 载入用户全局配置覆盖系统默认配置
				$config_path [] = C_CONFIG_PATH . $con_file . C_INI_EXT;
			}else{
				$array = ac_array_merge($array,$funcGetKey($GLOBALS [C] [$key.'_runtime'],$host));
				unset($GLOBALS [C] [$key.'_runtime']);
			}
			// +------------------------------------------------------------------------------
			// + 载入用户单模块(当前访问木块)配置覆盖系统默认配置
			// +------------------------------------------------------------------------------
			if(defined ( 'C_CURR_CONFIG_PATH' )){
				$key = md5 ( C_CURR_CONFIG_PATH . $con_file . C_INI_EXT.$host );
				if (!isset($GLOBALS [C] [$key.'_runtime'])) {
					$config_path [] = C_CURR_CONFIG_PATH . $con_file . C_INI_EXT;
				}else{
					$array = ac_array_merge($array,$funcGetKey($GLOBALS [C] [$key.'_runtime'],$host));
					unset($GLOBALS [C] [$key.'_runtime']);
				}
			}
			
			
			if (count($config_path)>0) {
				$GLOBALS [C] [$sys_key] = is_array ($array)?$array:array();
				foreach ( $config_path as $k => $v ) {
					if (file_exists ( $v )) {
						$array = include $v;
						// +-------------------------------------------------------------------
						// + 系统载入文件个数记录
						// +-------------------------------------------------------------------
						$GLOBALS[C]['_SYA_LOAD_FILE_'][] = array('filename'=>$v,'desc'=>'配置文件');;
						is_array ( $array ) or $array = array ();
						// 将数组键值全部转换为小写
						$GLOBALS [C] [$sys_key] = ac_array_merge($GLOBALS [C] [$sys_key],$funcGetKey($array,$host));
					}
				}
			}else{
				$GLOBALS [C] [$sys_key] = $array;
			}
		}
		
		if(!is_null($con_key)){
			$con_key = strtolower ( trim ( $con_key ) );
		}
		if (isset ( $GLOBALS [C] [$sys_key] [$con_key] )) {
			return $GLOBALS [C] [$sys_key] [$con_key];
		} elseif ($con_key === 'all' || ''===$con_key || is_null($con_key)) {
			if(empty($GLOBALS [C] [$sys_key]))return $default;
			return $GLOBALS [C] [$sys_key];
		} else {
			return $default;
		}
	}
	// +--------------------------------------------------------------------------------------
	// + 设置$GLOBALS[C]下的全局变量
	// +--------------------------------------------------------------------------------------
	static public function setConfig($con_file, $con_key, $default,$host=NULL ) {
		$key = md5 ( C_SYS_CONFIG_PATH . ucfirst ( trim ( $con_file ) ) . C_INI_EXT.$host);
		$con_key = strtolower ( trim ( $con_key ) );
		$GLOBALS [C] [$key] [$con_key] = $default;
	}
	
	// +----------------------------------------------------------------------------------
	// + 手动加载类文件
	// + @param string $classname 类名
	// + @param string or array $file_path 加载路径标志
	// + @param bool $file_path 是否初始化
	// +----------------------------------------------------------------------------------
	static public function &loadClass($classname, $file_path = '', $initialize = false) {
		if(function_exists($classname)) return TRUE;
		// +------------------------------------------------------------------------------
		// + 如果传入的不是数组就包装成数组统一处理
		// +------------------------------------------------------------------------------
		is_array ( $file_path ) or $file_path = array (
				$file_path 
		);
		
		// +------------------------------------------------------------------------------
		// + 循环查找类文件
		// +------------------------------------------------------------------------------
		foreach ( $file_path as $v ) {
			$class_file = $v . $classname . C_EXT;
			$class_file_md5 = md5 ( $class_file );

            if ( isset ( $GLOBALS [C] [$class_file_md5] )){
                return $GLOBALS [C] [$class_file_md5];
            }

            if ( empty(self::$core_path) ){
                $class_file_all = dirname(__FILE__).'/Const/core.classes.php';
                self::$core_path = include $class_file_all;
                if(empty(self::$core_path)) self::$core_path = array('file'=>'no file!');
                $GLOBALS[C]['_SYA_LOAD_FILE_'][] = array('filename'=>$class_file_all,'desc'=>'核心类文件');
            }

            $has_file = false;
            if(in_array($class_file,self::$core_path) || file_exists ( $class_file ) ){
                $has_file = true;
            }
			
			if (! isset ( $GLOBALS [C] [$class_file_md5] ) && $has_file) {
				$GLOBALS [C] [$class_file_md5] = true;
				include $class_file;
				self::$runTimeConfig[] = $class_file;
				// +---------------------------------------------------------------------
				// + 系统载入文件个数记录
				// +---------------------------------------------------------------------
				$GLOBALS[C]['_SYA_LOAD_FILE_'][] = array('filename'=>$class_file,'desc'=>'类文件');
				if ($initialize != false) {
					$GLOBALS [C] [$class_file_md5] = new $classname ();
					return $GLOBALS [C] [$class_file_md5];
				}
				return $GLOBALS [C] [$class_file_md5];
			}
		}
		
		if (! isset ( $GLOBALS [C] [$class_file_md5] )) {
			E ( 'Class File Load Error.Class is ' . $classname . '.' );
		}
		return $GLOBALS [C] [$class_file_md5];
	}
	
	// +----------------------------------------------------------------------------------
	// + 手动加载函数库
	// + @param string $file_name 函数库文件名
	// + @param string $file_path 加载路径
	// +----------------------------------------------------------------------------------
	static public function loadFunc($file_name, $file_path='' ) {
		// +------------------------------------------------------------------------------
		// + 加载系统函数库
		// +------------------------------------------------------------------------------
		if (strtolower ( $file_path ) == 'sys') {
			$file_path = C_SYS_FUNC_PATH;
		} elseif (trim ( $file_path ) === 'common') {
			$file_path = C_COMMON_PATH . 'Function' . C_DIR_FIX;
		} elseif (trim ( $file_path ) === 'curr_common' || trim ( $file_path )==='' ) {
			$file_path = C_CURR_COMMON_PATH . 'Function' . C_DIR_FIX;
		} else {
			$file_path = $file_path;
		}
		$func_file = $file_path . $file_name . C_FUNC_EXT;
		$func_file_md5 = md5 ( $func_file );
		if (! isset ( $GLOBALS [C] [$func_file_md5] ) && file_exists ( $func_file )) {
			include $func_file;
			self::$runTimeConfig[] = $func_file;
			// +-------------------------------------------------------------------------
			// + 系统载入文件个数记录
			// +-------------------------------------------------------------------------
			$GLOBALS[C]['_SYA_LOAD_FILE_'][] = array('filename'=>$func_file,'desc'=>'函数库文件');
			$GLOBALS [C] [$func_file_md5] = true;
		}
		if (! isset ( $GLOBALS [C] [$func_file_md5] )) {
			E ( 'File Load Error.' );
		}
		return $GLOBALS [C] [$func_file_md5];
	}
	
	// +--------------------------------------------------------------------------------------
	// + 设置$GLOBALS[C]下的全局变量
	// +--------------------------------------------------------------------------------------
	static private function _loadAutoFunc() {
		$dir_array[] = C_COMMON_PATH . 'Function'. C_DIR_FIX;
		if(defined('C_CURR_COMMON_PATH'))
			$dir_array[] = C_CURR_COMMON_PATH . 'Function'. C_DIR_FIX;
		foreach($dir_array as $k => $dir ){
			$list = dir_list($dir,'php');
			foreach($list as $k2 => $file_name ){
				$file_name = explode('/',$file_name);
				$file_name = $file_name[count($file_name)-1];
				$file_name = explode('.',$file_name);
				self::loadFunc($file_name[0],$dir);
			}
		}
	}
	
	// +----------------------------------------------------------------------------------
	// + 类自动加载
	// +----------------------------------------------------------------------------------
	static public function _autoload($classname) {
		$_class_array_path = array ();
		// +------------------------------------------------------------------------------
		// + 查找模型
		// +------------------------------------------------------------------------------
		! defined ( 'C_MODEL_PATH' ) or $_class_array_path [] = C_MODEL_PATH;
		// +------------------------------------------------------------------------------
		// + 查找当前模块下的公告类
		// +------------------------------------------------------------------------------
		! defined ( 'C_CURR_COMMON_PATH' ) or $_class_array_path [] = C_CURR_COMMON_PATH;
		// +------------------------------------------------------------------------------
		// + 查找所有公告类
		// +------------------------------------------------------------------------------
		! defined ( 'C_COMMON_PATH' ) or $_class_array_path [] = C_COMMON_PATH;
		// +------------------------------------------------------------------------------
		// + 框架自身调用类库Typeclass
		// +------------------------------------------------------------------------------
		! defined ( 'C_COMMON_PATH' ) or $_class_array_path [] = C_COMMON_PATH .'/Classes/';
		! defined ( 'C_COMMON_PATH' ) or $_class_array_path [] = C_COMMON_PATH .'/Typeclass/';
		! defined ( 'C_SYS_LIBRARY_PATH' ) or $_class_array_path [] = C_SYS_LIBRARY_PATH;
		//自定义类自动加载目录
        $auto_config_path = self::getConfig('Autoload','path',array());
        foreach ($auto_config_path as $k => $v ){
            $_class_array_path[] = $v;
        }
        // +------------------------------------------------------------------------------
		// + 使用手动函数载入
		// +------------------------------------------------------------------------------
		return self::loadClass ( $classname, $_class_array_path );
	}
	
	// +--------------------------------------------------------------------------------------
	// + 自定义错误处理
	// + @access public
	// + @param int $e 错误类型
	// + @param string $errstr 错误信息
	// + @param string $errfile 错误文件
	// + @param int $errline 错误行数
	// + @return void
	// +--------------------------------------------------------------------------------------
	static public function appError($e, $errmsg, $filename, $linenum, $vars) {
		$erroe_config = & App::getConfig('Log', 'all');
		switch ($e) {
			case E_ERROR :
			case E_PARSE :
			case E_CORE_ERROR :
			case E_COMPILE_ERROR :
			case E_USER_ERROR :
				@ob_end_clean();
				$errorStr = "[$e] $errmsg " . $filename . " 第 $linenum 行.";
				if ($erroe_config['log_record']){
					//记录日志
					xLog::record($errorStr);
				}
				self::outError ( $errorStr );
				break;
			default :
				$errorStr = "[$e] $errmsg " . $filename . " 第 $linenum 行.";
				if( $erroe_config['log_record'] ) {
					xLog::record($errorStr);
				}
				xDebug::trace ( $errorStr,xDebug::LABEL_NOTIC);
				break;
		}
	}
	
	// +--------------------------------------------------------------------------------------
	// + 致命错误捕获
	// + 保存日志文件
	// + 只执行一次
	// +--------------------------------------------------------------------------------------
	static public function fatalError() {
		$e = error_get_last ();
		if ($e) {
			$erroe_config = & App::getConfig('Log', 'all');
			if ($erroe_config['log_record']){
				$errorStr = '['.$e['type'].']'.$e['message']. $e['file'] . ' 第 '.$e['line'].' 行.';
				//记录日志
				xLog::record($errorStr);
			}
			switch ($e ['type']) {
				case E_ERROR :
				case E_PARSE :
				case E_CORE_ERROR :
				case E_COMPILE_ERROR :
				case E_USER_ERROR :
					ob_end_clean();
					self::outError ( $e );
			}
		}else{
			//保存日志
			xLog::save();
			// +------------------------------------------------------------------------------
			// + 检测是否开启调试模式
			// +------------------------------------------------------------------------------
			if (App::getConfig ( 'Config', 'debug' ) && !C_IS_AJAX ) {
				self::appTrace();
			}
		}
	}
	
	// +--------------------------------------------------------------------------------------
	// + 自定义异常处理
	// +--------------------------------------------------------------------------------------
	static public function appException($e, $errmsg, $filename, $linenum, $vars) {
		$error = array ();
		$error ['message'] = $e->getMessage ();
		$trace = $e->getTrace ();
		if ('E' == $trace [0] ['function']) {
			$error ['file'] = $trace [0] ['file'];
			$error ['line'] = $trace [0] ['line'];
		} else {
			$error ['file'] = $e->getFile ();
			$error ['line'] = $e->getLine ();
		}
		$error ['trace'] = $e->getTraceAsString ();
		$erroe_config = & App::getConfig('Log', 'all');
		if ($erroe_config['log_record']){
			//记录日志
			xLog::record($error ['message']);
		}
		// 发送404信息
		header ( 'HTTP/1.1 404 Not Found' );
		header ( 'Status:404 Not Found' );
		self::outError($error);
	}
	
	// +--------------------------------------------------------------------------------------
	// + 输出错误信息
	// +--------------------------------------------------------------------------------------
	static public function outError($error) {
		//写入日志
		xLog::save ();
		$config = & App::getConfig('Config', 'all');
		$e = array();
        if ($config['debug']) {
            //调试模式下输出错误信息
            if (!is_array($error)) {
                $trace          = debug_backtrace();
                $e['message']   = $error;
                $e['file']      = $trace[0]['file'];
                $e['line']      = $trace[0]['line'];
                ob_start();
                debug_print_backtrace();
                $e['trace']     = ob_get_clean();
            } else {
                $e              = $error;
            }
            // 包含异常页面模板
            include App::getConfig('Log', 'error_tpl');
        } else {
            //否则定向到错误页面
            $error_page         = $config['error_page'];
            if (!empty($error_page)) {
                redirect($error_page);
            }
        }
	}
	
	// +--------------------------------------------------------------------------------------
	// + 输出调试窗口
	// +--------------------------------------------------------------------------------------
	static public function appTrace() {
		// +--------------------------------------------------------------------------
		// + 文件载入数量
		// +--------------------------------------------------------------------------
		foreach ($GLOBALS[C]['_SYA_LOAD_FILE_'] as $k => $v ){
			if (is_array($v)) {
				xDebug::trace((isset($v['desc'])?('['.$v['desc'].'] '):'').$v['filename'],xDebug::LABEL_FILE);
			}else{
				xDebug::trace($v,xDebug::LABEL_FILE);
			}
		}
		// +--------------------------------------------------------------------------
		// + 内存使用情况
		// +--------------------------------------------------------------------------
		$memory_get_usage_size = ac_changeSize(memory_get_usage()-C_START_USE_MEMORY);
		// +--------------------------------------------------------------------------
		// + 运行内存使用最大值
		// +--------------------------------------------------------------------------
		$memory_get_peak_usage_size = ac_changeSize(memory_get_peak_usage()-C_START_USE_MEMORY);
		// +--------------------------------------------------------------------------
		// + 运行时间计算
		// +--------------------------------------------------------------------------
		$run_time = round((microtime(TRUE)-C_BEGIN_TIME)*1000,2).' MS';
		xDebug::trace('运行时间：'.$run_time,xDebug::LABEL_MEMORY);
		xDebug::trace('运行内存：'.$memory_get_usage_size,xDebug::LABEL_MEMORY);
		xDebug::trace('内存峰值：'.$memory_get_peak_usage_size,xDebug::LABEL_MEMORY);
		if (App::getConfig ( 'Session', 'session_auto_start' )){
			xDebug::trace('SESSION：开启',xDebug::LABEL_RUNTIME);
			xDebug::trace('SESSION存储类型：'.App::getConfig ( 'Session', 'session_type' ),xDebug::LABEL_RUNTIME);
		}else{
			xDebug::trace('SESSION：未开启',xDebug::LABEL_RUNTIME);
		}
		xDebug::trace('系统编码：'.App::getConfig ( 'Config', 'charset' ),xDebug::LABEL_RUNTIME);
		
		xDebug::trace('PHP服务器：'.PHP_VERSION,xDebug::LABEL_RUNTIME);
		$_trace = xDebug::trace('[get]');
		// +--------------------------------------------------------------------------
		// + 增加默认标签显示完整
		// +--------------------------------------------------------------------------
		isset($_trace[xDebug::LABEL_FILE]) or $_trace[xDebug::LABEL_FILE] = array();
		isset($_trace[xDebug::LABEL_MEMORY]) or $_trace[xDebug::LABEL_MEMORY] = array();
		isset($_trace[xDebug::LABEL_NOTIC]) or $_trace[xDebug::LABEL_NOTIC] = array();
		isset($_trace[xDebug::LABEL_RUNTIME]) or $_trace[xDebug::LABEL_RUNTIME] = array();
		isset($_trace[xDebug::LABEL_SQL]) or $_trace[xDebug::LABEL_SQL] = array();
		// +--------------------------------------------------------------------------
		// + 排序标签显示顺序
		// +--------------------------------------------------------------------------
		$trace[xDebug::LABEL_RUNTIME] = $_trace[xDebug::LABEL_RUNTIME];
		$trace[xDebug::LABEL_MEMORY] = $_trace[xDebug::LABEL_MEMORY];
		$trace[xDebug::LABEL_NOTIC] = $_trace[xDebug::LABEL_NOTIC];
		$trace[xDebug::LABEL_FILE] = $_trace[xDebug::LABEL_FILE];
		$trace[xDebug::LABEL_SQL] = $_trace[xDebug::LABEL_SQL];
		
		include C_SYS_TPL_PATH.'page_trace.tpl';
	}
}










