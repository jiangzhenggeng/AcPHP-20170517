<?php 
class EmptyAction extends Common{
	
	 public function _empty(){
		 //强制关闭调试模式
		App::setConfig('Config','debug',false);
		$this->showMessage('你访问的页面不存在！');
    }
} 