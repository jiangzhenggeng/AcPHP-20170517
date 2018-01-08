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
// + Release Date: 2015年11月8日 上午11:07:00
// +--------------------------------------------------------------------------------------

class xTemplate{
	// +----------------------------------------------------------------------------------
	// + 模板路径
	// +----------------------------------------------------------------------------------
	public $template_dir 	= null;
	// +----------------------------------------------------------------------------------
	// + 编译路径
	// +----------------------------------------------------------------------------------
	public $complies_dir 	= null;
	// +----------------------------------------------------------------------------------
	// + 是否开启缓存
	// +----------------------------------------------------------------------------------
	public $cacheing 		= false;
	//缓存时间
	public $cache_time 		= 3600;
	// +----------------------------------------------------------------------------------
	// + 缓存的路径
	// +----------------------------------------------------------------------------------
	public $cache_dir 		= null;
	// +----------------------------------------------------------------------------------
	// + 是否开启调试模式，开启调试模式，每次运行都进行模板编译，无论模板是否修改过
	// +----------------------------------------------------------------------------------
	public $debug 			= false;
	// +----------------------------------------------------------------------------------
	// + 标签左边界定界符
	// +----------------------------------------------------------------------------------
	public $left_fix 		= '{';
	// +----------------------------------------------------------------------------------
	// + 标签右边界定界符
	// +----------------------------------------------------------------------------------
	public $right_fix 	 	= '}';
	// +----------------------------------------------------------------------------------
	// + 在模板头部部分加上额外的内容
	// +----------------------------------------------------------------------------------
	public $tpl_header_data	= '';
	// +----------------------------------------------------------------------------------
	// + 插件路径默认 ./plus
	// +----------------------------------------------------------------------------------
	public $_plus_dir 	 	= null;
	// +----------------------------------------------------------------------------------
	// + 自定义解析标签插件如：array('plusname')
	// + 或者array('plusname'=>array('plusmethod1','plusmethod2',...))
	// +----------------------------------------------------------------------------------
	public $_plus 			= array();
	
	private $curr_html 	 	= null;
	private $vars 		 	= array();
	private $_tpl_start_time= 0;
	private $_tpl_end_time 	= 0;
	private $_all_tag 	 	= array();
	private $_tpl_sub 		= array();
	private $complie 		= false;
	
	// +----------------------------------------------------------------------------------
	// + 保存模板解析唯一对象
	// +----------------------------------------------------------------------------------
	static private $instance = null;
	// +----------------------------------------------------------------------------------
	// + 模板相关配置
	// +----------------------------------------------------------------------------------
	private $_config = null;
	// +----------------------------------------------------------------------------------
	// + 强制单文件缓存
	// +----------------------------------------------------------------------------------
	private $_is_cache = false;
	private $_is_cache_time = 3600;
	// +----------------------------------------------------------------------------------
	// + 私有化克隆，不允许复制对象
	// +----------------------------------------------------------------------------------
	private function __clone(){}
	
	// +----------------------------------------------------------------------------------
	// + 私有化构造函数，不允许外部实例化对象
	// +----------------------------------------------------------------------------------
	private function __construct(){
		// +------------------------------------------------------------------------------
		// + 记录开始模板的时间
		// +------------------------------------------------------------------------------
		$this->_tpl_start_time 	= microtime(TRUE);
		$this->_config 			= App::getConfig('Template','all');
		$this->template_dir 	= $this->_config['template_dir'];
		$this->complies_dir 	= $this->_config['complies_dir'].md5($this->template_dir).'/';
		$this->cacheing 		= $this->_config['cacheing'];
		$this->cache_time 		= $this->_config['cache_time'];
		$this->cache_dir 		= $this->_config['cache_dir'].md5($this->template_dir).'/';
		$this->debug 			= $this->_config['debug'];
		$this->left_fix 		= $this->_config['left_fix'];
		$this->right_fix 	 	= $this->_config['right_fix'];
		$this->_plus_dir 	 	= $this->_config['_plus_dir'];
		$this->_plus 			= $this->_config['_plus'];
		$this->tpl_header_data 	= $this->_config['tpl_header_data'];

		if( $this->debug ){
			ini_set("display_errors", 1);
			error_reporting( E_ALL );
		}else{
			ini_set("display_errors", 0);
			error_reporting( 0 );
		}
		
	}
	
	// +----------------------------------------------------------------------------------
	// + 获取数据库连接唯一对象句柄
	// +----------------------------------------------------------------------------------
	static public function getInstance(){
		if( !(self::$instance instanceof self) ){
			self::$instance = new self();
		}
		return self::$instance;
	}
	/**
	 * 预处理路径为统一的格式
	 * @param unknown $dir
	 * @return string
	 */
	private function _pDir($dir){
		$_dir = substr($dir, -1);
		if( !in_array($_dir, array('/','\\')) ){
			$dir = $dir.DIRECTORY_SEPARATOR;
		}
		return $dir;
	}
	//获取模板文件名
	public function getTplFilePath($tpl_file){
		return $this->_pDir($this->template_dir).$tpl_file;
	}
	//获取编译文件名
	public function getComplieFilePath($tpl_file){
		$complie_file_path = dirname( $this->_pDir($this->complies_dir).$tpl_file ).C_DIR_FIX;
		return $complie_file_path.md5($tpl_file.'.php').str_replace(array('/','\\','.'), '_', $tpl_file).'.php';
	}
	//获取缓存文件名
	public function getCacheFilePath($tpl_file){
		return $this->_pDir($this->cache_dir).md5($tpl_file.'.html.php').$tpl_file.'.html.php';;
	}
	/**
	 * 注入变量
	 * @param unknown $name
	 * @param unknown $var
	 */
	public function assign($name,$var){
		$this->vars[$name] = $var;
	}
	/**
	 * 显示模板
	 * @param unknown $tpl
	 */
	public function display(){
		//释放变量到环境中
		extract($this->vars);
		$this->_is_cache = func_get_arg(1);
		$this->_is_cache_time = func_get_arg(2);
		include $this->_getIncludeFilePath(func_get_arg(0),true);
	}
	
	//包含模板
	public function includeTpl($tpl_file){
		return $this->_getIncludeFilePath($tpl_file,false);
	}
	
	//获取包含文件路径
	private function _getIncludeFilePath($tpl_file,$create_cache=true){
		//获取模板文件路径
		$tpl_file_path = $this->getTplFilePath($tpl_file);
		if( !file_exists($tpl_file_path) ){
			E('模板文件：'.$tpl_file_path.'，不存在！');
		}

		//编译模板文件,返回编译文件路径
		$include_file = $complie_file_path = $this->_complieTplFile($tpl_file);

		if( $this->cacheing || $this->_is_cache===true ){

			$count = count($this->_tpl_sub);
			if( $create_cache ){
				$this->_tpl_sub[$count]['cache_file'] = $this->getCacheFilePath($tpl_file);
			}else{
				$this->_tpl_sub[$count]['cache_file'] = $complie_file_path;
			}
			$this->_tpl_sub[$count]['tpl_file'] = $tpl_file_path;
		}
		
		if( $this->_is_cache!==false && (($create_cache && $this->cacheing) || ($create_cache && $this->_is_cache===true)) ){
			//生成缓存，返回缓存文件路径
			if( $this->_createCacheFile($tpl_file) ){
				exit;
			}
		}
		return $include_file;
	}
	//编译模板文件
	private function _complieTplFile($tpl_file){
		//clearstatcache();
		
		//获取模板文件路径
		$tpl_file_path = $this->getTplFilePath($tpl_file);
		//获取编译文路径
		$complie_file_path = $this->getComplieFilePath($tpl_file);

		if(
				$this->complie ||
				!file_exists($complie_file_path) ||
				filectime($tpl_file_path)>filectime($complie_file_path)
		 ){
			//编译模板,返回处理后的模板内容
			$complie_file_content = $this->_complie_tpl($tpl_file_path);
			!is_dir(dirname($complie_file_path))?dir_create(dirname($complie_file_path)):true;
			file_put_contents($complie_file_path, $complie_file_content);
		}
		return $complie_file_path;
	}
	
	//创建缓存文件
	private function _createCacheFile($tpl_file){
		//获取模板文件路径
		$tpl_file_path = $this->getTplFilePath($tpl_file);
		//获取编译文路径
		$complie_file_path = $this->getComplieFilePath($tpl_file);
		//获取缓存文路径
		$cache_file_path = $this->getCacheFilePath($tpl_file);
		
		//模板函数名
		$tpl_cache_function = 'tpl_'.md5($cache_file_path);
		//模板变量名
		$tpl_cache_var = '_'.md5($cache_file_path);

		$tpl_file_path_time = filectime($tpl_file_path);
		$cache_file_path_time = filectime($cache_file_path);
		//如果缓存文件不存在，或者缓存过期，或者修改过模板，或者修改过包含模板
		//主文件
		if(
				$this->complie || 
				!file_exists($cache_file_path) ||
				$tpl_file_path_time>$cache_file_path ||
				($cache_file_path+$this->cache_time)<time() ||
				($cache_file_path+$this->cache_time)<$this->_is_cache_time
		){
			//编译模板,返回处理后的模板内容
			$this->_createCacheFileBack($complie_file_path,$cache_file_path,$tpl_cache_function,$tpl_cache_var);
		}else{
			include $cache_file_path;
			$create = false;
			foreach ($$tpl_cache_var as $k => $v ){
				$v_time = filectime($v['cache_file']);
				$v_tpl_file_time = filectime($v['tpl_file']);
				if(
						!file_exists($v['cache_file']) ||
						$v_tpl_file_time>$v_time ||
						($v_tpl_file_time+$this->cache_time)<time()
				){
					$create = true;break;
				}
			}
			if( $create===true ){
				$this->_createCacheFileBack($complie_file_path,$cache_file_path,$tpl_cache_function,$tpl_cache_var);
			}else {
				$tpl_cache_function( $$tpl_cache_var );
			}
		}
		return true;
	}
	
	private function _createCacheFileBack($complie_file_path,$cache_file_path,$tpl_cache_function,$tpl_cache_var){
		$cache_file_content = $cache_file_content2 = $this->_complie_cache($complie_file_path);
		!is_dir(dirname($cache_file_path))?dir_create(dirname($cache_file_path)):true;
		$cache_file_content = '<?php'.PHP_EOL.'$'.$tpl_cache_var.'='.var_export($this->_tpl_sub,true).';'.PHP_EOL.'function '.$tpl_cache_function.'($'.$tpl_cache_var.'){'.PHP_EOL.'?>'.$cache_file_content.'<?php }?>';
		file_put_contents($cache_file_path, $cache_file_content);
		include $cache_file_path;
		$tpl_cache_function( $$tpl_cache_var );
	}
	
	/**
	 * 缓存静态页面
	 * @param unknown $_curr_php
	 * @param unknown $_curr_html
	 */
	private function _complie_cache(){
		ob_start();
		extract($this->vars);
		include func_get_arg(0);
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
	
	/**
	 * 过滤注释
	 * @param unknown $content
	 */
	private function _filter($content){
		$array1 = array(
				'/<!--.*\\'.$this->left_fix.'(.*)\\'.$this->right_fix.'.*-->/U'
		);
		$array2 = array(
				$this->left_fix . '$1' . $this->right_fix
		);
		$content = preg_replace( $array1, $array2, $content);
		return $content;
	}
	/**
	 * 编译模板，返回处理后的模板内容
	 * @param 模板路径 $tpl_file_path
	 */
	private function _complie_tpl($tpl_file_path){
		$this->_all_tag = array();
		static $_obj_array = array();
		$content_1 = $this->tpl_header_data.file_get_contents($tpl_file_path);
		$content_1 = $this->_filter($content_1);
		if(version_compare(PHP_VERSION,'5.4.0','<')) {
			$content = preg_replace('/\\'.$this->left_fix.'(.[^\\'.$this->right_fix.'\\'.$this->left_fix.PHP_EOL.']*)\\'.$this->right_fix.'/ei','$this->_complie_tag(\'$1\')', $content_1);
		}else{
			$content = preg_replace_callback('/\\'.$this->left_fix.'(.[^\\'.$this->right_fix.'\\'.$this->left_fix.PHP_EOL.']*)\\'.$this->right_fix.'/i',function ($match){
				return $this->_complie_tag($match[1]);
			}, $content_1);
		}
		
		if( count($this->_plus)>0 && count($this->_all_tag)>0 ){
			//插件路径处理
			if( $this->_plus_dir==null ){
				$this->_plus_dir = $this->_pDir(dirname(__FILE__)).'plus';
			}
			$_all_tag = $this->_all_tag;
			foreach ($this->_plus as $k => $v ){
				if( is_array($v) ){
					$class = $k;
				}else {
					$class = $v;
				}
				$curr_plus = $this->_plus_dir.DIRECTORY_SEPARATOR.$class.'.class.php';
				if( !file_exists($curr_plus) ){
					E('plus file not find '.$curr_plus);
				}
				$_md5_obj = md5($curr_plus);
				if( empty($_obj_array[$_md5_obj]) ){
					include $curr_plus;
					$_obj_array[$_md5_obj] = new $class;
				}
				
				if( is_array($v) ){
					foreach ($v as $k2 => $v2 ){
						$this->_all_tag = $_obj_array[$_md5_obj]->$v2($this->_all_tag,$this,$content,$content_1);
					}
				}else{
					$this->_all_tag = $_obj_array[$_md5_obj]->init($this->_all_tag,$this,$content,$content_1);
				}
			}
			$content = str_replace($_all_tag, $this->_all_tag, $content);
		}
		return $content;
	}
	
	/**
	 * 预处理标签并返回
	 * @param unknown $string
	 */
	private function _complie_tag( $string ){
		$string = stripcslashes( $string );
		if (empty($string)){
			$string = '';
		}		
		//结束标签
		elseif (substr($string, 0,1)=='/'){
			$_string = substr($string,1);
			switch ($_string){
				
				case 'if':
					$string = '<?php }?>';
					break;
					
				case 'loop':
					$string = '<?php if($_tpl_first==1){$_tpl_first=0;}}}?>';
					break;
					
				case 'foreach':
					$string = '<?php if($_tpl_first==1){$_tpl_first=0;}}}?>';
					break;

				case 'php':
					$string = ' ?>';
					break;
				case 'for':
					$string = '<?php }?>';
					break;
					
				default:
					//未知结束标签
					$string = $this->_all_tag[] = $this->left_fix.$string.$this->right_fix;
			}
		}
		//注释
		elseif (substr($string, 0,1)=='*'){
			$string = '';
		}
		//函数
		elseif (substr($string, 0,1)=='@'){
			$string = '<?php echo '.substr($string,1).';?>';
		}
		//变量
		elseif (substr($string, 0,1)=='$'){
			//非缓存变量
			$string = '<?php echo isset('.$string.')?('.$string.'):\'\';?>';
		}
		//php单行段标签代码
		elseif ($string=='php'){
			$string = '<?php ';
		}
		//php块级代码
		elseif (substr($string, 0,4)=='php '){
			$string = '<?php '.substr($string,4).';?>';
		}
		//if标签
		elseif (substr($string, 0,2)=='if'){
			$string = '<?php if('.substr($string,2).'){?>';
		}
		
		//elseif标签
		elseif (substr($string, 0,6)=='elseif'){
			$string = '<?php }elseif('.substr($string,6).'){?>';
		}
		
		//else标签
		elseif (substr($string, 0,4)=='else'){
			$string = '<?php }else{?>';
		}
		
		//loop标签
		elseif (substr($string, 0,4)=='loop'){
			$string = $this->_complie_loop(substr($string,4));
		}
		
		//loop标签
		elseif (substr($string, 0,3)=='for'){
			$string = '<?php for('.substr($string, 3).'){ ?>';
		}
		
		//foreach标签
		elseif (substr($string, 0,7)=='foreach'){
			$string = $this->_complie_loop(substr($string,7));
		}
		
		//include标签 
		elseif (substr($string, 0,7)=='include'){
			$string = $this->_complie_include(substr($string,7));
		}
		
		//常量
		elseif (defined($string)){
			$string = '<?php echo '.$string.';?>';
		}
		//函数
		elseif (substr($string, 0,1)=='@'){
			if(substr($string, 1,1)=='=')
				$string = '<?php echo '.substr($string, 2).';?>';
			else 
				$string = '<?php '.substr($string,1).';?>';
		}
		//函数
		//未知标签
		else {
			$position = strpos($string, '(');
			if ($position!=false AND strpos($string, ')')!=false) {
				if(function_exists(trim(substr($string, 0,$position)))){
					$string = '<?php echo '.$string.';?>';
				}else{
					$string = $this->_all_tag[] = $this->left_fix.$string.$this->right_fix;
				}
			}else {
				$string = $this->_all_tag[] = $this->left_fix.$string.$this->right_fix;
			}
		}
		
		return $string;
	}
	/**
	 * 解析 loop 子字符串
	 * @param unknown $string
	 * @return unknown
	 */
	private function _complie_loop($string){
		$string = trim($string);
		$string = preg_replace('/\s+/', ' ', $string);
		$_s = explode(' ', $string);
		$_f = '<?php if(is_array('.$_s[0].')){$_tpl_first=$_tpl_last=$_tpl_index=1;$_tpl_last=count('.$_s[0].');foreach(';
		$_e = '){$_tpl_index++;?>';	
		if( isset($_s[2]) ){
			return $_f.$_s[0].' as '.$_s[1].'=>'.$_s[2].$_e;
		}
		return $_f.$_s[0].' as '.$_s[1].$_e;
	}
	
	/**
	 * 解析  include 包含子模板
	 * file="sub/footer.html"
	 * @param unknown $string
	 * @return unknown
	 */
	private function _complie_include($string){
		$string = trim($string);
		$file_fix = substr($string, -1);
		$sub_file_array = explode($file_fix, $string);
		return '<?php include '.__CLASS__.'::getInstance()->includeTpl("'.$sub_file_array[1].'");?>';
	}
	
	/**
	 * 获取模板解析运行时间
	 * @return string
	 */
	public function getRunTime(){
		$this->_tpl_end_time 	= microtime(TRUE);
		$run_time = number_format(($this->_tpl_end_time-$this->_tpl_start_time)*1000,2);
		return $run_time<0?0.00:$run_time;
	}
}



