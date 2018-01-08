<?php
class ApiCommon extends AppCommon  {
	
	//执行父类构造方法
	public function __construct(){
        //App::setConfig('Config','debug',false);
		if( get_parent_class()!='' && method_exists ( get_parent_class(), '__construct' ) ){
			parent::__construct();
		}
		define('C_HTTP','http://ac.jiangzg.com');
	}

    public function before(){
        if( get_parent_class()!='' && method_exists ( get_parent_class(), 'before' ) ){
            parent::before();
        }
        C_IS_APP_IOS || xOut::json(outError('非IOS请求'));
        return true;
    }
}
