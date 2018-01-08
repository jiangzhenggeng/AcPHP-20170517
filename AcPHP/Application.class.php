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

class Application {
	
	// +----------------------------------------------------------------------------------
	// + 调用件事
	// +----------------------------------------------------------------------------------
	public function init() {
		// +------------------------------------------------------------------------------
		// + 调用目标模块，获取目标对象控制器
		// +------------------------------------------------------------------------------
		$controller = $this->load_controller ();
		// +------------------------------------------------------------------------------
		// + 检测是否有前置方法
		// +------------------------------------------------------------------------------
		if (method_exists ( $controller, 'before' ) && is_callable ( array ($controller,'before') ) ) {
			call_user_func ( array ($controller,'before') );
		}

		// +------------------------------------------------------------------------------
		// + 事件方法检测
		// +------------------------------------------------------------------------------
		if (method_exists ( $controller, C_ROUTE_A )) {
			// +--------------------------------------------------------------------------
			// + 过滤方法名称，检测合法性
			// +--------------------------------------------------------------------------
			if (preg_match ( '/^[_]/i', C_ROUTE_A )) {
				E('You are visiting the action is to protect the private action' );
			} else {
				//获取类的所有可用方法
				$class_method_array = get_class_methods($controller);
				$call_method = '';
				foreach ($class_method_array as $k => $v ){
					if(strtolower($v)===C_ROUTE_A){
						$call_method = $v;
						break;
					}
				}
				if($call_method===''){
					E ( 'You are visiting the action is to not query--002' );
				}
                call_user_func ( array ($controller,$call_method) );
			}
		} else {
			// +--------------------------------------------------------------------------
			// + 如果目标模块不存在使用默认空方法
			// +--------------------------------------------------------------------------
			if (method_exists ( $controller, '_empty' )) {
				call_user_func ( array ($controller,'_empty') );
			} else {
				E ( 'Action does not exist.[001]<b>_empty</b>' );
			}
		}
		// +------------------------------------------------------------------------------
		// + 检测是否有后置方法
		// +------------------------------------------------------------------------------
		if (method_exists ( $controller, 'after' ) && is_callable ( array ($controller,'after') ) ) {
			call_user_func ( array ($controller,'after') );
		}
	}
	
	// +----------------------------------------------------------------------------------
	// + 加载控制器
	// +----------------------------------------------------------------------------------
	private function load_controller() {
		// +------------------------------------------------------------------------------
		// + 获取路由控制
		// +------------------------------------------------------------------------------
		$_r_m = App::getConfig('Route', 'm');
		$_r_c = App::getConfig('Route', 'c');
		$_r_a = App::getConfig('Route', 'a');
		
		$_action_array = array();
		// +------------------------------------------------------------------------------
		// + 如果不存在当前模块，说明调用的是一个单模块包，只有一个控制器
		// + 这种情况的优先级高
		// +------------------------------------------------------------------------------
		if(!defined('C_ROUTE_M')){
			$_action_array['file_path']  = C_CONTROLLER_PATH . C_ROUTE_C. 'Action' . C_EXT;
		}else{
			$_action_array['file_path']  = C_MODULE_PATH . C_ROUTE_M . C_DIR_FIX . C_ROUTE_C . 'Action' . C_EXT;
		}
		$_action_array['class_name'] = C_ROUTE_C . 'Action';
		$_action_array['empty_path'] = C_CONTROLLER_PATH . 'EmptyAction' . C_EXT;
		// +------------------------------------------------------------------------------
		// + 如果控制器存在
		// +------------------------------------------------------------------------------
		if ( file_exists($_action_array['file_path']) ) {
			include ($_action_array['file_path']);
			if (class_exists ( $_action_array['class_name'] )) {
				return new $_action_array['class_name'];
			}else {
				E ( 'Controller does not exist.[001]' );
			}
		}elseif ( file_exists($_action_array['empty_path']) ) {
			include ($_action_array['empty_path']);
			$_action_array['class_name'] = 'EmptyAction';
			if (class_exists ($_action_array['class_name'])) {
				return new $_action_array['class_name'];
			} else {
				E ( 'Controller does not exist.[002]');
			}
		}else{
			E ( 'Class file does not exist.[003]' );
		}
	}
}







