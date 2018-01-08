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

class MailSend extends Action{
	//subject
	//content
	//attachment
	//address
	//bcc
	//cc
	public function send($parme){
		
		$mail = new PHPMailer ( true );
		$mail_config = & App::getConfig('mail','all');
		$mail->CharSet = $mail_config['charset'];
		
		$mail->isSMTP ();
		$mail->Host = $mail_config['host'];
		$mail->Port = $mail_config['port'];
		$mail->SMTPSecure = "none";
		//$mail->SMTPDebug = 2;
		$mail->SMTPAuth = true;
		$mail->Username = $mail_config['username'];
		$mail->Password = $mail_config['parssword'];
		if(isset($mail_config['replayto'])){
			$mail->addReplyTo ( $mail_config['replayto'], $mail_config['replayname'] );
		}
		if(isset($mail_config['from'])){
			$mail->setFrom ( $mail_config['from'], $mail_config['fromname'] );
		}
		
		if(!is_array($parme['address'])){
			$parme['address'] = explode(',',$parme['address']);
		}
		foreach($parme['address'] as $k => $v ){
			if(isset($v['mail']) && isset($v['name'])){
				$mail->addAddress ($v['mail'], $v['name']);
			}else{
				$mail->addAddress (isset($v['mail'])?$v['mail']:$v);
			}
		}
		
		if(isset($parme['bcc']) && $parme['bcc']!='' ){
			if(!is_array($parme['bcc'])){
				$parme['bcc'] = explode(',',$parme['bcc']);
			}
			foreach($parme['bcc'] as $k => $v ){
				if(isset($v['mail']) && isset($v['name'])){
					$mail->addBCC ($v['mail'], $v['name']);
				}else{
					$mail->addBCC (isset($v['mail'])?$v['mail']:$v);
				}
			}
		}
		if(isset($parme['cc']) && $parme['cc']!=''){
			if(!is_array($parme['cc'])){
				$parme['cc'] = explode(',',$parme['cc']);
			}
			foreach($parme['cc'] as $k => $v ){
				if(isset($v['mail']) && isset($v['name'])){
					$mail->addCC ($v['mail'], $v['name']);
				}else{
					$mail->addCC (isset($v['mail'])?$v['mail']:$v);
				}
			}
		}
		$mail->Subject = $parme['subject'];
		$body = $parme['content'];
		$mail->WordWrap = 78;
		$mail->msgHTML ( $body );
		
		if(isset($parme['attachment']) && $parme['attachment']!=''){
			if(!is_array($parme['attachment'])){
				$parme['attachment'] = explode(',',$parme['attachment']);
			}
			foreach($parme['attachment'] as $k => $v ){
				if(isset($v['file']) && isset($v['name'])){
					$mail->addAttachment ($v['file'], $v['name']);
				}else{
					$mail->addAttachment (isset($v['file'])?$v['file']:$v);
				}
			}
		}
		return $mail->Send();
	}
	
}