<?php 
class LoginAction extends PcCommon{

    private function _isLogin(){
        if(intval($this->memberid)){
            if(!C_IS_AJAX){
                header('location:'.U('index/init'));
            }else{
                xOut::json(array('status'=>'error','message'=>'你已经登录啦~','code'=>-1));
            }
            exit;
        }
    }

	 public function init(){
	     $this->_isLogin();
         if(xInput::request('query')=='insert'){
             $result = $this->loginCommon();
             xOut::json($result);
         }
		 $this->display('index.html');
    }
    public function login(){
        $this->init();
    }


    public function logout(){
        xCookie::set('member',null);
        header('location:'.U('login'));
    }

    public function forgetpassword(){
        $this->display('login/forget_password.html');
    }
    public function setetpassword(){
        $this->display('login/set_password.html');
    }

    //使用qq快捷登录
    public function qqlogin(){
        $this->_isLogin();
        require_once(C_COMMON_PATH.'QQAPI/qqConnectAPI.php');

        if(xInput::request('query')=='callback'){
            $qc = new QC();
            $access_token = $qc->qq_callback();
            $openid = $qc->get_openid();

            $MemberOauthModel = M('member_oauth');
            $oauthData = $MemberOauthModel->where(array('openid'=>$openid,'type'=>1))->getOne();
            xCookie::set('member_oauth_data',$oauthData);
            //已经绑定过
            if(isset($oauthData['memberid']) && $oauthData['memberid']>0){
                $result = $this->loginCommon($oauthData['memberid']);

                if($result['status']=='success' ){
                    $url = (isset($_COOKIE['back_url']) && $_COOKIE['back_url']!='') ?$_COOKIE['back_url']:U('mobile/check/check');
                    header('location: '.$url);
                    exit;
                }
            }
            //执行绑定操作
            else{
                if(!isset($oauthData['memberid'])){
                    $qc = new QC($access_token,$openid);
                    $uinfo = $qc->get_user_info();
                    $oauthData = array('openid'=>$openid,'type'=>1,'memberid'=>0,'oauthname'=>$uinfo['nickname'],'figureurl'=>$uinfo['figureurl_qq_2']);
                    $MemberOauthModel->insert($oauthData);
                }
                $this->display('login/write_mobile.html');
            }
        }
        $qc = new QC();
        $qc->qq_login();
    }

    //使用新浪微博快捷登录
    public function weibologin(){
        $this->_isLogin();
        include_once( C_COMMON_PATH.'Libweibo/config.php' );
        include_once( C_COMMON_PATH.'Libweibo/saetv2.ex.class.php' );

        if(xInput::request('query')=='callback'){
            $o = new SaeTOAuthV2( WB_AKEY , WB_SKEY );
            if (isset($_REQUEST['code'])) {
                $keys = array();
                $keys['code'] = $_REQUEST['code'];
                $keys['redirect_uri'] = WB_CALLBACK_URL;
                try {
                    $token = $o->getAccessToken( 'code', $keys ) ;
                    $openid = $token['uid'];
                } catch (OAuthException $e) { }
            }
            if ($token) {
                $MemberOauthModel = M('member_oauth');
                $oauthData = $MemberOauthModel->where(array('openid'=>$openid,'type'=>2))->getOne();
                xCookie::set('member_oauth_data',$oauthData);
                //已经绑定过
                if(isset($oauthData['memberid']) && $oauthData['memberid']>0){
                    $result = $this->loginCommon($oauthData['memberid']);
                    if($result['status']=='success' ){
                        $url = (isset($_COOKIE['back_url']) && $_COOKIE['back_url']!='') ?$_COOKIE['back_url']:U('mobile/check/check');
                        header('location: '.$url);
                        exit;
                    }
                }
                //执行绑定操作
                else{
                    if(!isset($oauthData['memberid'])){
                        $c = new SaeTClientV2( WB_AKEY , WB_SKEY , $token['access_token'] );
                        $uinfo = $c->show_user_by_id( $token['uid'] );
                        $oauthData = array('openid'=>$openid,'type'=>2,'memberid'=>0,'oauthname'=>$uinfo['screen_name'],'figureurl'=>$uinfo['avatar_large']);
                        $MemberOauthModel->insert($oauthData);
                    }
                    $this->display('login/write_mobile.html');
                }
            } else {
                header('location:'.U('mobile/index/init'));
            }
        }
        $o = new SaeTOAuthV2( WB_AKEY , WB_SKEY );
        header('Location:'.$o->getAuthorizeURL( WB_CALLBACK_URL ));
    }

} 