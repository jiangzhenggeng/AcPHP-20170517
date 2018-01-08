<?php 
//强制关闭调试模式

class LoginAction extends MbCommon{

    //如果已经登录跳转
	private function _isLogin(){
		if(intval($this->memberid)){
			if(!C_IS_AJAX){
                header('location:'.U('mobile/index/init'));
			}else{
				xOut::json(array('status'=>'error','message'=>'你已经登录啦~','code'=>-1));
			}
			exit;
		}
	}

	//登录
	public function login(){
		$this->_isLogin();
		if(xInput::request('query')=='insert'){
			$result = $this->loginCommon();
			xOut::json($result);
		}
		$this->display('login/login.html');
    }

    public function loginout(){
        xOut::json($this->loginoutCommon());
    }

    //注册
    public function register(){
        $this->_isLogin();
        //开始注册
        if(xInput::request('query')=='insert'){
            xOut::json( $this->registerCommon() );
        }
        //显示注册页面
        else{
            $this->display('login/register.html');
        }
    }
    //qq登录
    public function qqlogin(){
        $this->_isLogin();
        parent::qqlogin();
    }

    //微博登录
    public function weibologin(){
        $this->_isLogin();
        parent::weibologin();
    }

    //获取注册验证码
    public function getregistercode(){
        xOut::json($this->getregistercodeCommon());
    }

    //忘记密码
	public function forgetpassword(){
        $this->_isLogin();
	    if(xInput::request('query')=='insert'){
	        $user = xInput::request('user');
            $mobile = trim($user['mobile']);
            $code = trim($user['code']);
            if(!preg_match('/^[1](3|4|5|7|8)\d{9}$/',$mobile)){
                xOut::json (array('status'=>'error','message'=>'电话号码错误','code'=>-1));
            }
            $sms = new Sendsms();
            $result = $sms->msmVerify($mobile,$code,1);
            if($result!==true){
                xOut::json ( array('status'=>'error','message'=>$result,'code'=>-1));
            }
            $memberModel = M('member');
            $member = $memberModel->where(array('mobile'=>$mobile))->getOne();

            if(count($member)<=0){
                xCookie::set('mobile',$mobile);
                xOut::json( array('status'=>'error','message'=>'该手机未注册','code'=>-1));
            }else{
                xCookie::set('setetpassword_mobile',$mobile);
                xOut::json( array('status'=>'success','message'=>'验证成功,可以设置密码','code'=>1));
            }
        }else{
            $this->display('login/forget_password.html');
        }
    }

    //设置密码
	public function setetpassword(){
        $this->_isLogin();
        if(xInput::request('query')=='insert'){
            $mobile = trim(xCookie::get('setetpassword_mobile'));
            $password = xInput::request('password');
            if(strlen($password)<6 || strlen($password)>20 ){
                xOut::json (array('status'=>'error','message'=>'密码为6-20位','code'=>-1));
            }
            if(!preg_match('/^[1](3|4|5|7|8)\d{9}$/',$mobile)){
                xOut::json (array('status'=>'error','message'=>'电话号码错误','code'=>-1));
            }
            $memberModel = M('member');
            $memberModel->where(array('mobile'=>$mobile))->update(array(
                'password'=>createMemberPassword($password)
            ));
            xCookie::set('setetpassword_mobile',null);
            xOut::json(array('status'=>'success','message'=>'设置成功','code'=>1));
        }else{
            $mobile = trim(xCookie::get('setetpassword_mobile'));
            $this->assign('mobile',$mobile);
            $this->display('login/set_password.html');
        }
    }
} 