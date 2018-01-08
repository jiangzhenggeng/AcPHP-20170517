<?php
// +--------------------------------------------------------------------------------------
// + AcPHP
// +--------------------------------------------------------------------------------------
// + 版权所有 2015年11月8日 贵州天岛在线科技有限公司，并保留所有权利。
// + 网站地址: http://www.acphp.com
// +--------------------------------------------------------------------------------------
// + 这不是一个自由软件！您只能在遵守授权协议前提下对程序代码进行修改和使用；不允许对程序代码以任何形式任何目的的再发布。
// + 授权协议：http://www.acphp.com/license.html
// +--------------------------------------------------------------------------------------
// + Author: AcPHP  http://www.jiangzg.com/ <2992265870@qq.com/jiangzg>
// + Release Date: 2015年11月8日 上午1:09:25
// +--------------------------------------------------------------------------------------
		
class Sms extends Action{
	
	/**
     * url 为服务的url地址
     * query 为请求串
     */
    protected function sock_post($url,$query){
        $data = "";
        $info=parse_url($url);
        $fp=fsockopen($info["host"],80,$errno,$errstr,30);
        if(!$fp){
            return $data;
        }
        $head="POST ".$info['path']." HTTP/1.0\r\n";
        $head.="Host: ".$info['host']."\r\n";
        $head.="Referer: http://".$info['host'].$info['path']."\r\n";
        $head.="Content-type: application/x-www-form-urlencoded\r\n";
        $head.="Content-Length: ".strlen(trim($query))."\r\n";
        $head.="\r\n";
        $head.=trim($query);
        $write=fputs($fp,$head);
        $header = "";
        while ($str = trim(fgets($fp,4096))) {
            $header.=$str;
        }
        while (!feof($fp)) {
            $data .= fgets($fp,4096);
        }
        return $data;
    }

    /**
     * 模板接口发短信
     * apikey 为云片分配的apikey
     * tpl_id 为模板id
     * tpl_value 为模板值
     * mobile 为接受短信的手机号
     */
    protected function tpl_send_sms($apikey, $tpl_id, $tpl_value, $mobile){
        $url="https://sms.yunpian.com/v2/sms/tpl_single_send.json";
        $encoded_tpl_value = urlencode("$tpl_value");
        $post_string="apikey=$apikey&tpl_id=$tpl_id&tpl_value=$encoded_tpl_value&mobile=$mobile";
        return $this->sock_post($url, $post_string);
    }

    /**
     * 普通接口发短信
     * apikey 为云片分配的apikey
     * text 为短信内容
     * mobile 为接受短信的手机号
     */
    protected function send_sms($apikey, $text, $mobile){
        $url="https://sms.yunpian.com/v2/sms/single_send.json";
        $encoded_text = urlencode("$text");
        $post_string="apikey=$apikey&text=$encoded_text&mobile=$mobile";
        return $this->sock_post($url, $post_string);
    }
	
}




class Sendsms extends Sms{
	
	public function sendMSM($mobile,$parme,$code,$type){
		
		$token = App::getConfig('msm','token','');
		if($token=='') return false;
			
		$result = $this->roomSignIp();
		if($result!==true)return $result;
		
		$result = $this->roomSignNumber($mobile);
		if($result!==true)return $result;
		
		$msmVerifyModel = M('msm_verify');
		$data = array(
			'mobile'=>$mobile,
			'type'=>$type,
			'code'=>$code,
			'addtime'=>time(),
			'ip'=>ac_getIp(),
			'`delete`'=>0,
		);
		$result =  $msmVerifyModel->insert($data);
		
		if($result){
			$this->send_sms($token,'【新财会】您的验证码是'.$code.'。如非本人操作，请忽略本短信',$mobile);
			return true;
		}
		return false;
	}
	
	
	/************************************
	校验短信验证码
	************************************/
	public function msmVerify($mobile,$code,$type=1,$delete=true){
		$msmVerifyModel = M('msm_verify');
		$where = array('mobile'=>$mobile,'type'=>$type);
		$data = $msmVerifyModel->where($where)->order('id DESC')->getOne();

		if(count($data)<=0 || $data['delete'] == 1){
            return '请先获取短信验证码';
		}
        if($code!=$data['code']){
            return '短信验证码错误';
        }
		
		if($data['addtime'] +  App::getConfig('msm','expire_time',1800) < time() ){
			return '请重新获取验证码';
		}
		if($delete){
            $msmVerifyModel->where(array('mobile'=>$mobile,'type'=>$type))->update(array('`delete`'=>1));
        }
		return true;
	}
	
	/************************************
	产生一个验证码
	************************************/
	public function randomCode(){
		$code = '';
		for($i = 0 ; $i < App::getConfig('msm','msm_len',6) ; $i++ ){
			$code .= rand(0,9);
		}
		return $code;
	}
	
	
	/************************************
	防IP刷短信机制
	************************************/
	private function roomSignIp(){
		$msmVerifyModel = M('msm_verify');
		$count = $msmVerifyModel->where(array('ip'=>ac_getIp(),'addtime'=>array( (time()-(24*60*60)),'>')))->count();
		$ip_max_num = App::getConfig('msm','ip_max_num',5);
		if($count>=$ip_max_num){
			return '你在同一个IP下发送'.$ip_max_num.'条短信，已达到系统限定值';
		}
		return true;
	}
	
	/************************************
	防单个手机号码刷短信机制
	************************************/
	private function roomSignNumber($mobile,$type=1){
		$msmVerifyModel = M('msm_verify');
		$count = $msmVerifyModel->where(array('mobile'=>$mobile,'addtime'=>(time()-(24*60*60))))->count();
		$mobile_max_num = App::getConfig('msm','mobile_max_num',5);
		if($count>=$mobile_max_num){
			return '你在24发送'.$mobile_max_num.'条短信，已达到系统限定值';
		}
		return true;
	}
	
}


























