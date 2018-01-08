<?php
//App::setConfig('Config','debug',false);

class ClientCommon extends Common{

    protected $member = [];
    protected $memberid = 0;
    protected $memberkey = null;
    protected $majorid = null;

    //执行父类构造方法
    public function __construct(){

        if( get_parent_class()!='' && method_exists ( get_parent_class(), '__construct' ) ){
            parent::__construct();
        }

        $this->majorid = intval(xInput::request('majorid'));
        if(!$this->majorid){
            $this->majorid = intval(xCookie::get('majorid'));
        }
        xCookie::set('majorid',$this->majorid);

        $this->memberkey = App::getConfig('Config','memberkey','member');
        $member = xCookie::get($this->memberkey);
        $this->member = $member;
        $this->memberid = intval($member['memberid']);


        $saltKey = App::getConfig('Config','saltkey');
        $loginkey = App::getConfig('Config','loginkey');
        $loginData = xCookie::get($loginkey);
        $loginDataArray = explode('|',$loginData);

        if($this->memberid<=0 && count($loginDataArray)==3){

            $memberid = $loginDataArray[0];
            $expires = $loginDataArray[1];
            $salt = $loginDataArray[2];

            $where = array('memberid'=>intval($memberid));
            $memberModel = M('member');
            $member = $memberModel->where($where)->getOne();

            $member = init_login($member);

            $password = $member['password'];

            $loginHash = md5($memberid.$expires.$password.$saltKey);

            if($loginHash==$salt){
                //登录成功,设置临时cookie
                xCookie::set($this->memberkey,$member);
            }
        }
    }

    protected function loginCommon($memberidLogin = null){

        if( intval($memberidLogin)<=0 ){
            $user = xInput::request('user');

            if(!isset($user['mobile']) || !preg_match('/^[1](3|4|5|7|8)\d{9}$/',trim($user['mobile']))){
                return(array('status'=>'error','message'=>'电话号码错误','code'=>-1));
            }

            if(!isset($user['password']) || strlen($user['password'])<6 || strlen($user['password'])>16 ){
                return(array('status'=>'error','message'=>'密码格式错误，6-16个字符','code'=>-1));
            }
            $where = array('mobile'=>$user['mobile']);
            $member = M('member')->where($where)->getOne();
            if(count($member)<=0){
                return(array('status'=>'error','message'=>'您还没有注册，赶快注册吧~','code'=>-1));
            }

            if($member['password']!=createMemberPassword($user['password'])){
                return(array('status'=>'error','message'=>'账号或密码错误','code'=>-1));
            }
        }else{
            $where = array('memberid'=>intval($memberidLogin));
            $memberModel = M('member');
            $member = $memberModel->where($where)->getOne();
        }

        $member = init_login($member);

        $this->member = $member;
        $this->memberid = $member['memberid'];
        $expires = time()+30*24*60*60;
        $salt = App::getConfig('Config','saltkey');
        $hash = md5($this->memberid.$expires.$member['password'].$salt);
        $loginkey_val = $member['memberid'].'|'.$expires.'|'.$hash;
        xCookie::set(App::getConfig('Config','loginkey'),$loginkey_val,array('time'=>$expires));
        return(array('status'=>'success','message'=>'登录成功','code'=>1));
    }

    protected function loginoutCommon(){
        $member = xCookie::get($this->memberkey);
        xCookie::set($this->memberkey,null);
        xCookie::set(App::getConfig('Config','loginkey'),null);
        if(is_array($member) && isset($member['memberid']) && intval($member['memberid'])>0){
            return(array('status'=>'logout','message'=>'登录成功','code'=>1));
        }else{
            return(array('status'=>'login','message'=>'请先登录','code'=>1));
        }
    }


    protected function registerCommon($delete=true){
        $user = xInput::request('user');

        if(!isset($user['mobile']) || !preg_match('/^[1](3|4|5|7|8)\d{9}$/',trim($user['mobile']))){
            return (array('status'=>'error','message'=>'电话号码错误','code'=>-1));
        }
        $user['mobile'] = trim($user['mobile']);

        $sms = new Sendsms();
        $result = $sms->msmVerify(trim($user['mobile']),trim($user['code']),1,$delete);
        if($result!==true){
            return ( array('status'=>'error','message'=>$result,'code'=>-1));
        }
        $memberModel = M('member');
        $member = $memberModel->where(array('mobile'=>$user['mobile']))->getOne();

        $memberid = isset($member['memberid'])?$member['memberid']:0;

        if(count($member)>0 && $member['password']!=''){
            return( array('status'=>'error','message'=>'该手机已注册','code'=>-1));
        }
        if(count($member)<=0){
            $insertData = array(
                'mobile'=>$user['mobile'],
                'registertime'=>time(),
                'groupid'=>1
            );
            $memberid = $memberModel->insert($insertData);
        }
        if($memberid){
            $this->loginCommon($memberid);
            xCookie::set($this->memberkey,null);
            return( array('status'=>'success','message'=>'注册成功','code'=>1));
        }else{
            return( array('status'=>'error','message'=>'系统错误','code'=>-1));
        }
    }

    protected function getregistercodeCommon(){
        $mobile = trim(xInput::request('mobile'));
        if(!preg_match('/^[1](3|4|5|7|8)\d{9}$/',$mobile)){
            return (array('status'=>'error','message'=>'电话号码错误','code'=>-1));
        }

        $member = M('member')->where(array('mobile'=>$mobile))->getOne();

        if( count($member)>0 && $member['password']!='' ){
            if(xInput::request('bind')!='yes'){
                return (array('status'=>'error','message'=>'您已注册','code'=>-2));
            }
        }

        //正式开始发送短信
        $sms = new Sendsms();
        $code = $sms->randomCode();
        $content = '#code#='.$code.'&#company#=新财会';
        $result = $sms->sendMSM($mobile,$content,$code,1);
        if($result!==true || $result===false){
            return( array('status'=>'error','message'=>$result,'code'=>-1));
        }
        return( array('status'=>'success','message'=>'短信验证码发送成功','code'=>1));
    }

    //切换科目
    protected function changesubject(){
        $subjectid = intval(xInput::get('subjectid'));
        if($subjectid){
            xCookie::set('subjectid',$subjectid);
            $subject = M('subject')->field('subjectname')->where(array('subjectid'=>$subjectid))->getOne();
            xCookie::set('subjectname',$subject['subjectname']);

            $majorid = intval(xInput::get('majorid'));
            if($majorid){
                xCookie::set('majorid',$majorid);
                $major = M('major')->field('majorname')->where(array('majorid'=>$majorid))->getOne();
                xCookie::set('majorname',$major['majorname']);
            }
            header('location:'.C_HTTP_REFERER);
        }
        $this->showMessage('切换失败');
    }

    //切换专业
    protected function changemajor(){
        $majorid = intval(xInput::get('majorid'));
        if($majorid){
            xCookie::set('majorid',$majorid);

            $major = M('major')->field('majorname')->where(array('majorid'=>$majorid))->getOne();
            xCookie::set('majorname',$major['majorname']);

            header('location:'.C_HTTP_REFERER);
        }
        $this->showMessage('切换失败');
    }

    //使用qq快捷登录
    protected function qqlogin(){

        require_once(C_COMMON_PATH.'LogExt/QQAPI/qqConnectAPI.php');
        $qc = new QC();
        if(xInput::request('query')!='callback') {
            $qc->qq_login();
        }else{
            $access_token = $qc->qq_callback();
            $openid = $qc->get_openid();
            $MemberOauthModel = M('member_oauth');
            $oauthData = $MemberOauthModel->where(array('openid'=>$openid,'type'=>1))->getOne();
            //不存在该第三方账号 执行绑定操作
            if(count($oauthData)<=0){
                $qc = new QC($access_token,$openid);
                $uinfo = $qc->get_user_info();
                //创建一个空账号
                $insertData = array(
                    'registertime'=>time(),
                    'groupid'=>1,
                    'curr_openid'=>$openid,
                    'nickname'=>$uinfo['nickname'],
                    'facepicture'=>$uinfo['figureurl_qq_2'],
                    'sex'=>$uinfo['gender']=='男'?1:($uinfo['gender']=='女'?2:0)
                );
                $memberid = M('member')->insert($insertData);

                $oauthData = array(
                    'openid'=>$openid,
                    'type'=>1,
                    'memberid'=>$memberid,
                    'oauthname'=>$uinfo['nickname'],
                    'figureurl'=>$uinfo['figureurl_qq_2']
                );
                $MemberOauthModel->insert($oauthData);
                $result = $this->loginCommon($memberid);
                if($result['status']=='success' ) {
                    $this->display('member/block/mobile.html');
                }else{
                    $this->showMessage('系统错误');
                }
            }else{
                M('member')->where(array('memberid'=>$oauthData['memberid']))->update(array('curr_openid'=>$openid));
            }
        }
    }

    //使用新浪微博快捷登录
    protected function weibologin(){

        include_once( C_COMMON_PATH.'LogExt/Libweibo/config.php' );
        include_once( C_COMMON_PATH.'LogExt/Libweibo/saetv2.ex.class.php' );

        $o = new SaeTOAuthV2( WB_AKEY , WB_SKEY );
        if(xInput::request('query')!='callback') {
            header('Location:' . $o->getAuthorizeURL(WB_CALLBACK_URL));
        }else{
            if (xInput::request('code','')!='') {
                $keys = array();
                $keys['code'] = xInput::request('code');
                $keys['redirect_uri'] = WB_CALLBACK_URL;
                try {
                    $token = $o->getAccessToken( 'code', $keys ) ;
                    $openid = $token['uid'];
                } catch (OAuthException $e) { }
            }
            if (isset($openid)) {
                $MemberOauthModel = M('member_oauth');
                $oauthData = $MemberOauthModel->where(array('openid'=>$openid,'type'=>2))->getOne();

                //不存在该第三方账号 执行绑定操作
                if(count($oauthData)<=0){
                    $c = new SaeTClientV2( WB_AKEY , WB_SKEY , $token['access_token'] );
                    $uinfo = $c->show_user_by_id( $token['uid'] );
                    //创建一个空账号
                    $insertData = array(
                        'registertime'=>time(),
                        'groupid'=>1,
                        'curr_openid'=>$openid,
                        'nickname'=>$uinfo['screen_name'],
                        'facepicture'=>$uinfo['avatar_hd'],
                        'introduction'=>$uinfo['description'],
                        'sex'=>$uinfo['gender']=='m'?1:($uinfo['gender']=='f'?2:0)
                    );
                    $memberid = M('member')->insert($insertData);
                    $oauthData = array(
                        'openid'=>$openid,
                        'type'=>2,
                        'memberid'=>$memberid,
                        'oauthname'=>$uinfo['screen_name'],
                        'figureurl'=>$uinfo['avatar_large']
                    );
                    $MemberOauthModel->insert($oauthData);
                    $result = $this->loginCommon($memberid);
                    if($result['status']=='success' ) {
                        $this->display('member/block/mobile.html');
                    }else{
                        $this->showMessage('系统错误');
                    }
                }else{
                    M('member')->where(array('memberid'=>$oauthData['memberid']))->update(array('curr_openid'=>$openid));
                }

                $result = $this->loginCommon($oauthData['memberid']);
                if($result['status']=='success' ){
                    $url = (isset($_COOKIE['back_url']) && $_COOKIE['back_url']!='') ?$_COOKIE['back_url']:U('/');
                    header('location: '.$url);
                    exit;
                }else{
                    $this->showMessage('系统错误');
                }
            } else {
                header('location:'.U('/'));
            }
        }
    }


    protected function logout(){
        xSession::delete($this->memberkey);
        xOut::json($this->loginoutCommon());
    }

    //获取专业数据
    protected function getMajor( $majorid = null ){
        $majorid = $majorid?$majorid:intval(xInput::request('majorid'));
        $major_data = xTempCache::get('major'.$majorid);
        if(!$major_data){
            $MajorModel = M('major')->where(array('majorid'=>$majorid));
            $major_data = $MajorModel->getOne();
            xTempCache::set('major'.$majorid,$major_data);
        }
        return $major_data;
    }

    //获取科目数据
    protected function getSubject( $subjectid = null ){
        $subjectid = $subjectid?$subjectid:intval(xInput::request('subjectid'));
        $key = 'subject'.$subjectid;
        $subject_data = xTempCache::get($key);
        if(!$subject_data){
            $subjectModel = M('subject')->where(array('subjectid'=>$subjectid));
            $subject_data = $subjectModel->getOne();
            xTempCache::set($key,$subject_data);
        }
        return $subject_data;
    }

    //获取专业对应的科目数据
    protected function getMajorSubjectList( $majorid = null ){
        $majorid = $majorid?$majorid:intval(xInput::request('majorid'));
        $key = 'subject_list'.$majorid;
        $subject_list_data = xTempCache::get($key);
        if(!$subject_list_data){
            $subjectModel = M('subject')->where(array('majorid'=>$majorid));
            $subject_list_data = $subjectModel->getAll();
            xTempCache::set($key,$subject_list_data);
        }
        return $subject_list_data;
    }

    //获取学员专业数据
    protected function getMemberMajor( $majorid = null ,$memberid = null ){
        $majorid = $majorid?$majorid:intval(xInput::request('majorid'));
        $memberid = intval($memberid?$memberid:$this->memberid);
        $key = 'member_major'.$majorid.$memberid;
        $member_major_data = xTempCache::get( $key );
        if(!$member_major_data){
            $memberMajorModel = M('member_major')->where(array('memberid'=>$this->memberid,'majorid'=>$majorid));
            $member_major_data = $memberMajorModel->getOne();
            xTempCache::set( $key, $member_major_data);
        }
        return $member_major_data;
    }
}

















































