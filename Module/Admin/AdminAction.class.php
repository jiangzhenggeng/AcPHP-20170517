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
// + 管理员组管理模块
// +--------------------------------------------------------------------------------------
class AdminAction extends AdminCommon{

	public function __construct(){
		if( get_parent_class()!='' && method_exists ( get_parent_class(), '__construct' ) ){
			parent::__construct();
		}
		
	}
	
	//管理员列表
	public function lists(){
		$m = M('admin a');
		$count = $m->count();
		$page = $this->page($count);
		$m->limit($page->getLimit()); 
		$admin = $m->field('a.*,ag.group_name')->leftJoin('admin_group ag','ag.group_id=a.group_id')
			->order('a.listorder desc,a.admin_id desc')->getAll();
		$this->assign('admin', $admin);
		$this->assign('page', $page->show());
		$this->display('admin/admin_list.html');
	}
	
	//添加管理员
	public function add(){
		if ( !isset($_POST['query']) || $_POST['query']!='insert' ){
			//生成系统令牌
	        $this->assign('token', $this->token() );
			//获取管理员组
			$admin_group = M('admin_group')->where(array('isshow'=>array(0,'!=')))->getAll();
	        $this->assign('admin_group', $admin_group );
	        $this->display('admin/admin_add.html');
	    }else{
			//校验系统令牌
	        if(!$this->verifyToken(xInput::request('token'))){
				$this->error('令牌校验错误');
			}
			$admin = xInput::request('admin');
			if( !preg_match('/^[\w\d\-@\.]{6,20}/',$admin['admin_name']) ){
				$this->showMessage('用户名不能小于6位和大于20位，不能由特殊字符组成');
			}
			if(strlen($admin['password'])<6 || strlen($admin['password'])>20 ){
				$this->showMessage('密码不能小于6位和大于20位');
			}
			if($admin['password']!=$admin['repassword']){
				$this->showMessage('两次密码输入不同');
			}
			unset($admin['repassword']);
			
			$admin['password'] = createPassword($admin['password']);
			$result = M('admin')->insert($admin);
			if($result){
				$this->success();
			}
			$this->error();
		}
	}
	//修改管理员
	public function edit($admin_id='',$query=''){
		$admin_id = $admin_id?$admin_id:intval(xInput::request('admin_id'));
		$admin_id >0 OR $this->showMessage('非法操作');
		
		if ( !isset($_POST['query']) || $_POST['query']!='insert' ){
			//生成系统令牌
	        $this->assign('token', $this->token() );
			$admin = M('admin')->where(array('admin_id'=>$admin_id))->getOne();
	        $this->assign('admin', $admin );
			//获取管理员组
			$admin_group = M('admin_group')->where(array('isshow'=>array(0,'!=')))->getAll();
	        $this->assign('admin_group', $admin_group );
			$this->assign('query', $query );
	        $this->display('admin/admin_edit.html');
	    }else{
			//校验系统令牌
	        $this->verifyToken(xInput::request('token')) or $this->showMessage('校验系统令牌失败');
			$admin = xInput::request('admin');
			if($admin['password']!=''){
				if(strlen($admin['password'])<6 || strlen($admin['password'])>20 ){
					$this->showMessage('密码不能小于6位和大于20位');
				}
				if($admin['password']!=$admin['repassword']){
					$this->showMessage('两次密码输入不同');
				}
				$admin['password'] = createPassword($admin['password']);
			}else{
				unset($admin['password']);
			}
			unset($admin['repassword']);
			unset($admin['admin_name']);
			if($query=='self'){
				unset($admin['group_id']);
			}
			$result = M('admin')->where(array('admin_id'=>$admin_id))->update($admin);
			if($result){
				$this->success();
			}
			$this->error();
		}
	}
	//删除管理员
	public function delete(){
		$admin_id = intval(xInput::get('admin_id'));
		$admin_id >0 OR $this->error();
		if(M('admin')->where(array('admin_id'=>$admin_id))->delete()){
			$this->success();
		}
		$this->error();
	}
	//排序
	public function listorder($model){
		if(parent::listorder(M('admin'))){
			$this->success();
		}
		$this->error();
	}
	
	//修改自己的信息
	public function selfedit(){
		$this->edit(xSession::get('admin.admin_id'),'self');
	}
}

