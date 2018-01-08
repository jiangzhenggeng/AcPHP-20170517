<?php 

//强制关闭调试模式
class LoginAction extends ApiCommon {
	//登录
	public function login($memberid=0,$message='登录成功'){
	    if($memberid<=0){
            $mobile = xInput::request('mobile');
            $password = xInput::request('password');
            if(!preg_match('/^[1](3|4|5|7|8)\d{9}$/',trim($mobile))){
                xOut::json(outError('电话号码错误'));
            }
            if( strlen($password)<6 || strlen($password)>16 ){
                xOut::json(outError('密码格式错误'));
            }
            $where = array('mobile'=>$mobile);
            $member = M('member')->where($where)->getOne();
            if(count($member)<=0){
                xOut::json(outStatus('-2','您还没有注册，赶快注册吧~'));
            }
            if($member['password']!=createMemberPassword($password)){
                xOut::json(outStatus(-3,'账号或密码错误'));
            }
        }else{
            $where = array('memberid'=>intval($memberid));
            $memberModel = M('member');
            $member = $memberModel->where($where)->getOne();
            if(count($member)<=0){
                xOut::json(outStatus(-4,'账号不存在'));
            }
        }

        $member = init_login($member);
        $expires = time()+30*24*60*60;
        $salt = App::getConfig('Config','saltkey');
        $hash = md5($this->memberid.$expires.$member['password'].$salt);
        $loginkey_val = $member['memberid'].'|'.$expires.'|'.$hash;
        $member['_loginkey'] = $loginkey_val;
        $member['_expires'] = $expires;
        xOut::json(outSuccess($member,$message));
    }

    //获取注册验证码
    public function getRegisterCode(){
        $mobile = trim(xInput::request('mobile'));
        if(!preg_match('/^[1](3|4|5|7|8)\d{9}$/',$mobile)){
            xOut::json (outError('电话号码错误'));
        }
        $member = M('member')->where(array('mobile'=>$mobile))->getOne();
        if( count($member)>0 && $member['memberid']>0 ){
            xOut::json( outError('该手机已注册',-2));
        }
        //正式开始发送短信
        $sms = new Sendsms();
        $code = $sms->randomCode();
        $content = '#code#='.$code.'&#company#=新财会';
        $result = $sms->sendMSM($mobile,$content,$code,1);
        if($result!==true || $result===false){
            xOut::json(outError($result));
        }
        xOut::json( outSuccess('短信验证码发送成功'));
    }

    //注册
    public function register(){

        $user = xInput::request('user');
        $user['mobile'] = trim( isset($user['mobile'])?$user['mobile']:'');
        if( !preg_match('/^1(3|4|5|7|8)\d{9}$/',trim($user['mobile']))){
            xOut::json(outError('电话号码错误'.$user['mobile']));
        }
        if( strlen($user['password'])<6 || strlen($user['password'])>16 ){
            xOut::json(outError('密码格式错误'));
        }
        
        $sms = new Sendsms();
        $result = $sms->msmVerify(trim($user['mobile']),trim($user['code']),1,true);
        if($result!==true){
            xOut::json(outError($result));
        }
        $memberModel = M('member');
        $count = $memberModel->where(array('mobile'=>$user['mobile']))->count();
        if( $count>0 ){
            xOut::json( outError('该手机已注册',-2));
        }
        $insertData = array(
            'mobile'=>$user['mobile'],
            'password'=>createMemberPassword($user['password']),
            'registertime'=>time(),
            'groupid'=>1,
        );
        $memberid = $memberModel->insert($insertData);

        if($memberid){
            $this->login($memberid,'注册成功，将自动登录');
        }else{
            xOut::json( outError('系统错误'));
        }
    }


	//获取忘记验证码
    public function getFindPasswordCode(){
        $mobile = trim(xInput::request('mobile'));
        if(!preg_match('/^[1](3|4|5|7|8)\d{9}$/',$mobile)){
            xOut::json (outError('电话号码错误'));
        }
        $member = M('member')->where(array('mobile'=>$mobile))->getOne();
        if( count($member)<=0 && $member['memberid']<=0 ){
            xOut::json( outError('该手机未注册',-2));
        }
        //正式开始发送短信
        $sms = new Sendsms();
        $code = $sms->randomCode();
        $content = '#code#='.$code.'&#company#=新财会';
        $result = $sms->sendMSM($mobile,$content,$code,3);
        if($result!==true || $result===false){
            xOut::json(outError($result));
        }
        xOut::json( outSuccess('短信验证码发送成功'));
    }
    
    //设置密码
	public function setPassword(){
        
        $user = xInput::request('user');
        $user['mobile'] = trim( isset($user['mobile'])?$user['mobile']:'');
        if( !preg_match('/^1(3|4|5|7|8)\d{9}$/',trim($user['mobile']))){
            xOut::json(outError('电话号码错误'.$user['mobile']));
        }
        if( strlen($user['password'])<6 || strlen($user['password'])>16 ){
            xOut::json(outError('密码格式错误'));
        }
        
        $sms = new Sendsms();
        $result = $sms->msmVerify(trim($user['mobile']),trim($user['code']),3,true);
        if($result!==true){
            xOut::json(outError($result));
        }
        
        $mobile = trim($user['mobile']);
        $password = $user['password'];
        
        $memberModel = M('member');
        $memberModel->where(array('mobile'=>$mobile))->update(array(
            'password'=>createMemberPassword($password)
        ));
        xOut::json(outSuccess('设置成功'));
    }

} 