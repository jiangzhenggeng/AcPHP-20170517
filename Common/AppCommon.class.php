<?php

class AppCommon extends Common{

    public $member = null;
    public $memberid = null;

    //执行父类构造方法
    public function __construct(){
        //关闭调试模式信息
        //App::setConfig('Config','debug',false);
        $json = file_get_contents('php://input');
        $_POST = json_decode($json,true);

        if( get_parent_class()!='' && method_exists ( get_parent_class(), '__construct' ) ){
            parent::__construct();
        }
        define('C_IS_APP',true);
        define('C_IS_APP_IOS',true);
        define('C_IS_APP_ANDROID',false);

        //C_IS_APP || xOut::json(outError('非法请求'));

        //接口签名验证
        //$this->_signVerification();

        //获取会员信息
        $this->_loginMember();

    }

    private function _signVerification(){
        //接口安全验证
        //密钥验证
        $para   = $_REQUEST;
        unset($para['sign']);
        $secret = App::getConfig('Config','secret_key');
        $str = '';
        if(count($para)>0){
            // 对加密数组进行字典排序
            foreach ($para as $key=>$value){
                $arr[$key] = $key;
            }
            sort($arr); //字典排序的作用就是防止因为参数顺序不一致而导致下面拼接加密不同
            // 将Key和Value拼接
            foreach ($arr as $k => $v) {
                $str .= $arr[$k].$para[$v];
            }
        }
        //将拼接的字符串加密
        $sign = md5($secret.$str);

        if($_REQUEST['sign']!=$sign||!$_REQUEST['sign']){
            xOut::json(outError('签名错误'));
        }
    }

    private function _loginMember(){
        $loginkey = App::getConfig('Config','loginkey');
        $loginData = xInput::request($loginkey);
        $loginDataArray = explode('|',$loginData);

        $memberid = $loginDataArray[0];
        $expires = $loginDataArray[1];
        $salt = $loginDataArray[2];

        $where = array('memberid'=>intval($memberid));
        $memberModel = M('member');
        $member = $memberModel->where($where)->getOne();

        $member = init_login($member);

        $password = $member['password'];

        $saltKey = App::getConfig('Config','saltkey');
        $loginHash = md5($memberid.$expires.$password.$saltKey);

        if($loginHash==$salt){
            //登录成功,设置临时cookie
            $this->member = $member;
            $this->memberid = intval($member['memberid']);
            define('C_IS_LOGIN',false);
        }else{
            define('C_IS_LOGIN',true);
        }
    }
}

















































