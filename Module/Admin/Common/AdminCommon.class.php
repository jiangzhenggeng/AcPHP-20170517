<?php
class AdminCommon extends AdminsCommon {
	
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

	protected function getArea(){
        $key = '$area_data';
        $area = xTempCache::get($key);
        if(!$area){
            $area = M('exam_area')->field('areaid,areaname')->getAll();
            xTempCache::set($key,$area);
        }
        return $area;
    }
	
	
	//返回资源消耗情况
	public function __destruct(){
		//除ajax获取数据外，所有访问都有返回
		if(C_IS_AJAX){
			return true;
		}elseif(strtolower(xInput::request('memory'))!='hide'){
			$system_run_time = microtime(TRUE)-C_BEGIN_TIME;
			$string = '<script>if(typeof $==\'undefined\')document.write(\'<script src="'.__JS__.'jquery-1.11.3.min.js"><\/script>\');</script>';
			$string .= '<script>$(".ac-nav").append("<span style=\"float:right;\">';
			$string .= '耗时：<font color=\"green\">'.round($system_run_time*1000,2).'MS</font>　';
			$string .= '模板解析时间：<font color=\"green\">'.$this->getTplObj()->getRunTime().'MS</font>　';
			$string .= '内存占用：'.round(memory_get_usage()/1024/1024,2).'MB　占用峰值：'.round(memory_get_peak_usage()/1024/1024,2).'MB';
			$string .= '</span>")</script>';
			echo $string;
		}
	}

	public function view($thble='paper_data'){
		App::setConfig('Config','debug',false);
		$paperid = intval(xInput::get('paperid'));
		$paperid >0 OR xOut::json(array('status'=>'error','code'=>-1,'message'=>'非法操作'));

		$paperData = M($thble)->where(array('paperid'=>$paperid))->getAll();
		$this->assign('questionid_array', $paperData );

		$this->display('common/paper_view.html');
	}
}
