<?php

class MobileCommon extends MbCommon{

	public function __construct(){
		
		if( get_parent_class()!='' && method_exists ( get_parent_class(), '__construct' ) ){
			parent::__construct();
		}
		//如果没有登录
		if($this->memberid<=0 ){
			//不是注册或者登录模块
			if(C_IS_AJAX){
			    xOut::json(array('status'=>'islogin','message'=>'请先登录','code'=>1));
            }
			header('Location:'.U('mobile/login/login'));
			exit;
		}else{
			//检查信息完整度
			if(!C_IS_AJAX){

                //信息完整度检测
                if(!preg_match('/^1(3|4|5|7|8)\d{9}$/',trim($this->member['mobile']))){
                    $this->display('member/block/mobile.html');
                }

                if($this->member['password']=='') {
                    $this->display('member/block/password.html');
                }

                if(intval($this->member['areaid'])<=0) {
                    $examAreaModel = M('exam_area');
                    $exam_area_data = $examAreaModel->order('letter asc')->getAll();
                    $this->assign('exam_area',$exam_area_data);
                    $this->assign('submint_url',U('changearea'));
                    $this->assign('areaid',$this->member['areaid']);
                    if(xInput::request('url')!='')$this->assign('location_url',base64_decode(xInput::request('url')));
                    $this->display('member/block/area.html');
                }

                if(!$this->member['nickname']){
                    $this->display('member/block/nickname.html');
                }
            }
		}
	}
}