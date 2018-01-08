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
// + 系统生成模块
// +--------------------------------------------------------------------------------------
class LoginAction extends Action{

	public function __construct(){
		if( get_parent_class()!='' && method_exists ( get_parent_class(), '__construct' ) ){
			parent::__construct();
		}
	}
	public function init(){
		
		$this->assign('hello','<font color=red size=15>Hello XEXAM!</font>');

		$this->display('index.html');

	}
	
	// +----------------------------------------------------------------------------------
	// + 前置方法
	// + 在执行所有方法之前执行
	// +----------------------------------------------------------------------------------
	public function before() {
		echo 'before';
	}
	// +----------------------------------------------------------------------------------
	// + 后置方法
	// + 在执行所有方法之后执行
	// +----------------------------------------------------------------------------------
	public function after() {
		echo 'after';
	}
	// +----------------------------------------------------------------------------------
	// + 默认空方法
	// + 如果不存在用户指定的方法，则执行该方法
	// +----------------------------------------------------------------------------------
	public function _empty() {
		echo '_empty';
	}
}

?>