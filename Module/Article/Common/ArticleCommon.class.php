<?php
class ArticleCommon extends Common {
	
	//执行父类构造方法
	public function __construct(){
		if( get_parent_class()!='' && method_exists ( get_parent_class(), '__construct' ) ){
			parent::__construct();
		}
		//判断是否登录
		parent::adminIsLogin();
		//判断是否具有权限
		parent::adminHasPrivilege();
		
	}
	
	
	//返回资源消耗情况
	public function __destruct(){
		//除ajax获取数据外，所有访问都有返回
		if(C_IS_AJAX){
			return true;
		}else{
			$system_run_time = microtime(TRUE)-C_BEGIN_TIME;
			$string = '<script>$(".ac-nav").append("<span style=\"float:right;\">';
			$string .= '耗时：<font color=\"green\">'.round($system_run_time*1000,2).'MS</font>　';
			$string .= '模板解析时间：<font color=\"green\">'.$this->getTplObj()->getRunTime().'MS</font>　';
			$string .= '内存占用：'.round(memory_get_usage()/1024/1024,2).'MB　占用峰值：'.round(memory_get_peak_usage()/1024/1024,2).'MB';
			$string .= '</span>")</script>';
			echo $string;
		}
	}
}
