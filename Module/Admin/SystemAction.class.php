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

// +--------------------------------------------------------------------------------------
// + 系统设置中心模块
// +--------------------------------------------------------------------------------------
class SystemAction extends AdminCommon{

	public function __construct(){
		if( get_parent_class()!='' && method_exists ( get_parent_class(), '__construct' ) ){
			parent::__construct();
		}
		
	}
	
	//系统变量表
	public function variable(){
		$query_data = xInput::request('data');
		$var_id = intval(xInput::request('var_id',''));
		//列表系统变量
		if($query_data=='' || $query_data=='lists'){
			$systemVar = M('system_var');
			$count = $systemVar->count();
			$page = $this->page($count);
			$systemVar->limit($page->getLimit()); 
			$systemVar = $systemVar->getAll();
			$this->assign('system_var', $systemVar);
			$this->assign('page', $page->show());
			$this->display('system/system_var_list.html');
		}
		//创建系统变量
		elseif($query_data=='create'){
			if(xInput::request('query')!='insert'){
				//生成系统令牌
	        	$this->assign('token', $this->token() );
				$this->display('system/system_var_add.html');
				
			}elseif(xInput::request('query')=='insert'){
				$this->verifyToken(xInput::request('token')) or $this->error();
				$system_var = xInput::post('system_var');
				$result = M('system_var')->where(['var_id'=>$var_id])->insert($system_var);
				$result or $this->error();
				$this->success(U('variable',['data'=>'lists']));
			}			
		}
		//编辑系统变量
		elseif($query_data=='edit'){
			$var_id >0 or $this->error();
			if(xInput::request('query')!='insert'){
				$systemVar = M('system_var')->where(['var_id'=>$var_id])->getOne();
				$this->assign('system_var', $systemVar);
				$this->assign('token', $this->token() );
				$this->display('system/system_var_edit.html');
			}elseif(xInput::request('query')=='insert'){
				$this->verifyToken(xInput::request('token')) or $this->error();
				$system_var = xInput::post('system_var');
				$result = M('system_var')->where(['var_id'=>$var_id])->update($system_var);
				$result or $this->error();
				$this->success(U('variable',['data'=>'lists']));
			}
		}
		//删除系统变量
		elseif($query_data=='delete'){
			$var_id >0 or $this->error();
			$result = M('system_var')->where(['var_id'=>$var_id])->delete();
			$result or $this->error();
			$this->success();
		}
		//排序
		elseif($query_data=='listorder'){
			if(parent::listorder(M('system_var'))){
				$this->success();
			}
			$this->error();
		}
	}
	
	
	//系统配置
	public function setting(){
		if(xInput::request('query')!='insert'){
			$this->__config('system','网站配置');			
		}elseif(xInput::request('query')=='insert'){
			if($this->__configInsert('system','文件上传设置')) {
				$this->success(U('Admin/System/setting'));
			}
			$this->error(U('Admin/System/setting'));
		}
	}
	
	
	//邮件配置
	public function emailconfig(){
		
		if(xInput::request('query')!='insert' && xInput::request('query')!='test'){
			$this->__config('email','邮件配置');			
		}elseif(xInput::request('query')=='insert'){
			if($this->__configInsert('email','邮件配置')) {
				$this->system = array ('DEFAULT' => $this->system );
				file_put_contents(C_CONFIG_PATH.'Mail.php','<?php'.PHP_EOL.'return '.var_export($this->system,TRUE).';');
				$this->success(U('Admin/System/emailconfig'));
			}
			$this->error();
		}
		//测试邮件
		elseif(xInput::request('query')=='test'){
			$m = new MailSend();
			$array = array(
				'subject' => '测试邮件'.date('Y-m-d H:i:s'),
				'content' => '测试邮件'.date('Y-m-d H:i:s'),
				'address' => xInput::request('testmail')
			);
			if($m->send($array)){
				exit(json_encode(array('status'=>1,'message'=>'邮件发送成功')));
			}
			exit(json_encode(array('status'=>-1,'message'=>'邮件发送失败')));
		}
	}
	
	
	public function uploadconfig(){
		if(xInput::request('query')!='insert' && xInput::request('query')!='test'){
			$this->__config('upload','文件上传设置');
		}elseif(xInput::request('query')=='insert'){
			
			if($this->__configInsert('upload','文件上传设置')){
				$this->system = array ('DEFAULT' => $this->system );
				file_put_contents(C_CONFIG_PATH.'Mail.php','<?php'.PHP_EOL.'return '.var_export($this->system,TRUE).';');
				$this->success(U('Admin/System/uploadconfig'));
			}
			$this->error();
		}
	}
	
	private function __config($cfgkey='upload',$cfgname='文件上传设置'){
		$this->system = M('system_cfg')->where(array('cfgkey'=>$cfgkey))->getOne();
		if(count($this->system)<=0){
			$array = array('cfgkey'=>$cfgkey,'cfgname'=>$cfgname,'value'=>'');
			M('system_cfg')->insert($array);
		}
		if($this->system['value']!=''){
			$this->system['value'] = string2array($this->system['value']);
		}else{
			$this->system['value'] = array();
		}
		$this->assign('cfgid', $this->system['cfgid']);
		$this->assign($cfgkey, $this->system['value']);
		//生成系统令牌
		$this->assign('token', $this->token() );
		$this->display('system/'.$cfgkey.'_config.html');
	}
	
	private $system = array();
	
	private function __configInsert($cfgkey='upload',$cfgname='文件上传设置'){
		$this->verifyToken(xInput::request('token')) 
		or $this->showMessage('校验系统令牌失败');
		
		$cfgid = xInput::request('cfgid');
		$cfgid >0 OR $this->error();
		$system_val = array();
		$this->system = xInput::request($cfgkey);
		
		$system_val['`cfgkey`'] = $cfgkey;
		$system_val['`value`'] = str_replace('"','\\"',array2string($this->system));
		$system_val['`cfgname`'] = $cfgname;
		$m = M('system_cfg');
		return $m->where('cfgid='.$cfgid)->update($system_val);		
	}
	
}

