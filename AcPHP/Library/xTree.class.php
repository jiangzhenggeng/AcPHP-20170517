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
// + Release Date: 2015年11月8日 上午1:09:25
// +--------------------------------------------------------------------------------------

// +--------------------------------------------------------------------------------------
// + 树形❀菜单
// +--------------------------------------------------------------------------------------

class xTree{
	//单列唯一对象
	static private $instance = null;
	//顶级菜单id的值
	public $tree_top_val	 = 0;
	//主键值名称
	public $tree_id	 		 = 'catid';
	//依附主键值名称
	public $tree_parentid	 = 'parentid';
	public $tree_fix		 = 'fix';
	
	public $child_name		 = 'children';
	//格式化规则
	/**
	 * 所有菜单之前修饰符
	 * 菜单空白修饰符
	 * @var unknown
	 */
	public $fix_array 		 = array('','　','│','├','└','<span>','</span>');
	//一级菜单修饰前缀,顶级菜单与顶级菜单之间的间隔符
	public $first_menu_fix	 = '';
	
	//私有匤E}^�ګ?�N����yղ��]�[;|D���
	private function __clone(){}
	//私有化构造函数，不允许外部实例化对象
	private function __construct(){}
	
	/**
	 * 获取唯一树形对象
	 * @return Tree
	 */
	static public function getInstance(){
		if( !(self::$instance instanceof self) ){
			self::$instance = new self();
		}
		return self::$instance;
	}
	/**
	 * 生成树形结构
	 * @param unknown $array
	 * @param number $pid
	 * @param string $id
	 * @param string $parentid
	 * @return multitype:unknown
	 */
	public function createTree(&$array,$pid=0) {
		
		$_temp_array = array();
		foreach ($array as $val ) 
			$_temp_array[$val[$this->tree_id]] = $val;
		$array = array();
		foreach ($_temp_array as $key => $val ) 
			!isset($_temp_array[$val[$this->tree_parentid]]) ? $array[] = &$_temp_array[$key]:$_temp_array[$val[$this->tree_parentid]][$this->child_name][] = &$_temp_array[$key];
		return $array;
		
		/*
		$_temp_array = array();
		$p = array();
		$pid = $pid!=0?$pid:$this->tree_top_val;
		foreach ($array as $key => $val ){
			if ($val[$this->tree_parentid]==$pid) {
				$i = count($_temp_array);
				$_temp_array[$i] = isset($p[$val[$this->tree_id]])?array_merge($val,$p[$val[$this->tree_id]]):$val;
				$p[$val[$this->tree_id]] = &$_temp_array[$i];
			}else {
				$i = isset($p[$val[$this->tree_parentid]][$this->child_name])?count($p[$val[$this->tree_parentid]][$this->child_name]):0;
				$p[$val[$this->tree_parentid]][$this->child_name][$i] = $val;
				if( isset($p[$val[$this->tree_id]][$this->child_name]) ){
					$p[$val[$this->tree_parentid]][$this->child_name][$i][$this->child_name] = $p[$val[$this->tree_id]][$this->child_name];
				}
				$p[$val[$this->tree_id]] = &$p[$val[$this->tree_parentid]][$this->child_name][$i];
			}
		}
		return $_temp_array;
		*/
	}
	/**
	 * 创建无限分类树形菜单结构并返回
	 * @param unknown $category_tree	创建菜单的二维数组
	 * @param number $n				菜单修饰符之前的统一字符数量
	 * @param unknown $k
	 * @return string
	 */
	public function createTreeMenu(&$category_tree, $style = '', $n = 0, $k = array()) {	
		$kn = array();$temp_str = '';
		$_count = count ( $category_tree );
		for($j = 0; $j < $_count; $j ++) {
			$te = $this->_putTree ( $category_tree [$j], $category_tree, $n, $j, $k, $style );
			if(isset($te ['putTree'])){
				$temp_str .= $te ['putTree'];
				$kn = $te ['kn'];
				if (isset ( $category_tree [$j] [$this->child_name] [0] ) && $category_tree [$j] [$this->child_name] [0] != '') {
					$temp_str = $temp_str . $this->createTreeMenu ( $category_tree [$j] [$this->child_name], $style, $n + 1, $kn );
				}
			}
		}
		return $temp_str;
	}

	private function _putTree(&$category_tree, &$category_tree2 = '', $n = '', $j = '', $k = array(), $style = 'configTree') {
		// 生成树前半部分
		$te = $this->_getCategoryStyle ( $category_tree2, $n, $j, $k );
		$style = str_replace(array('::',':','->'),array('.'),$style);
		$_style = explode('.', $style);
		// 生成树后半一部分
		if (function_exists($style)) {
			$te ['putTree'] = $style ( $category_tree, $te['temp_str'] );
		}elseif ( count($_style)>=2 AND is_callable(array($_style[0],$_style[1]))){
			$te ['putTree'] = @call_user_func_array ( array ($_style[0],$_style[1]),array( $category_tree, $te['temp_str'] ));
		}else{
			//$style为动态字符串
			extract($category_tree);
			$fix = $this->tree_fix;
			$$fix = $te['temp_str'];
			eval("\$te['putTree'] = \"$style\";");
		}
		return $te;
	}
	
	private function _getCategoryStyle($category_tree, $n, $j, $k) {
		$temp_str_temp = $temp_str = '';
		
		$_count = count ( $category_tree );
		$kn = array();
		for($i = - 1; $i < $n; $i ++) {
	
			if ($i + 1 == $n) {
				if ($j + 1 >= $_count) {
					if ($category_tree [$j] [$this->tree_parentid] != $this->tree_top_val) {
						$temp_str_temp = $this->fix_array[4].$this->fix_array[5].$this->fix_array[6];
					}elseif($this->first_menu_fix!=''){
						$temp_str_temp = $this->first_menu_fix;
					}
					$kn [$i + 1] = false;
				} else {
					if ($i != - 1) {
						$temp_str_temp = $this->fix_array[3].$this->fix_array[5].$this->fix_array[6];
					}elseif($this->first_menu_fix!=''){
						$temp_str_temp = $this->first_menu_fix;
					}
					$kn [$i + 1] = true;
				}
			} else {
				if (isset ( $k [$i + 1] ) && $k [$i + 1]) {
					$temp_str_temp = $this->fix_array[2].$this->fix_array[1].$this->fix_array[1];
					$kn [$i + 1] = true;
				} else {
					$temp_str_temp = $this->fix_array[1].$this->fix_array[1].$this->fix_array[1];
				}
			}
	
			if ($i == - 1) {
				$kn [$i + 1] = false;
			}
			$temp_str = $temp_str . $temp_str_temp;
		}
		$tArr = array (
				'temp_str' => $this->fix_array[0].$temp_str,
				'kn' => $kn
		);
		return $tArr;
	}
}




