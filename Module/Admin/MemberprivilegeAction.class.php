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
// + 会员组权限管理
// +--------------------------------------------------------------------------------------
class MemberprivilegeAction extends AdminCommon{
	private $tree_obj = null;
	
	public function __construct(){
		if( get_parent_class()!='' && method_exists ( get_parent_class(), '__construct' ) ){
			parent::__construct();
		}
		$this->tree_obj = xTree::getInstance ();
		$this->tree_obj->tree_id = 'priv_id';
	}
	//系统菜单列表
	public function lists(){
		if(true/*!xCache::get('member')*/){
			$this->tree_obj->fix_array = array('','　','│','├','└','<span class="ac-query-click" flag="none">','</span>');
			$privilege = M('member_group_priv_data')->order('`group` asc,listorder asc,priv_id desc')->getAll();
			$create_obj = $this->tree_obj->createTree ( $privilege );
			$privilege = $this->tree_obj->createTreeMenu ($create_obj,'MemberTreeCommon::createTreeMemberTable');
			xCache::set('privilege',$privilege);
		}else{
			$privilege = xCache::get('privilege');
		}
		$this->assign('privilege', $privilege );
		$this->display('member/privilege_list.html');
	}
	
	//修复数据
	public function repair(){
		$result = M('member_group_priv_data')->repair();
		$this->success();
	}
	
	
	//系统菜单添加
	public function add(){
		if ( !isset($_POST['query']) || $_POST['query']!='insert' ){
			$member_privilege = M('member_group_priv_data')->order('listorder asc,priv_id desc')->getAll();
			$tree_obj = xTree::getInstance ();
			$create_obj = $tree_obj->createTree ( $member_privilege );
			$member_privilege = $tree_obj->createTreeMenu ($create_obj,'MemberTreeCommon::createTreeMemberOption');
	        $this->assign('member_privilege', $member_privilege );
			//生成系统令牌
	        $this->assign('token', $this->token() );
			$this->display('member/privilege_add.html');
	    }else{
			//校验系统令牌
	        $this->verifyToken(xInput::request('token')) or $this->error();
			$member_privilege = xInput::request('member_privilege');
			$member_privilege['module'] = strtolower($member_privilege['module']);
			$member_privilege['controller'] = strtolower($member_privilege['controller']);
			$member_privilege['action'] = strtolower($member_privilege['action']);
			$member_privilege['data'] = strtolower($member_privilege['data']);
			
			$result = M('member_group_priv_data')->insert($member_privilege);
			if($result){
				$this->success(U('lists'));
			}
			$this->error();
		}
	}
	
	//系统菜单编辑
	public function edit(){
		$priv_id = intval(xInput::request('priv_id',0));
		$priv_id >0 OR $this->showMessage('非法操作');
		
		if ( !isset($_POST['query']) || $_POST['query']!='insert' ){
			$cur_member_privilege = M('member_group_priv_data')->where(['priv_id'=>$priv_id])->getOne();
			$_GET['parentid'] = $cur_member_privilege['parentid'];
			$member_privilege = M('member_group_priv_data')->order('listorder asc,priv_id desc')->getAll();
			$tree_obj = xTree::getInstance ();
			$create_obj = $tree_obj->createTree ( $member_privilege );
			$member_privilege = $tree_obj->createTreeMenu ($create_obj,'TreeCommon::createTreeMenuOption');
	        $this->assign('member_privilege', $member_privilege );
			$this->assign('cur_member_privilege', $cur_member_privilege );
			//生成系统令牌
	        $this->assign('token', $this->token() );
			$this->display('member/privilege_edit.html');
	    }else{
			//校验系统令牌
	        $this->verifyToken(xInput::request('token')) or $this->error();
			$member_privilege = xInput::request('member_privilege');
			$member_privilege['module'] = strtolower($member_privilege['module']);
			$member_privilege['controller'] = strtolower($member_privilege['controller']);
			$member_privilege['action'] = strtolower($member_privilege['action']);
			$member_privilege['data'] = strtolower($member_privilege['data']);
			
			$result = M('member_group_priv_data')->where(['priv_id'=>$priv_id])->update($member_privilege);
			if($result){
				$this->success(U('lists'));
			}
			$this->error();
		}
	}
	
	//排序
	public function listorder($model=NULL){
		if(parent::listorder(M('member_group_priv_data'))){
			$this->success();
		}
		$this->error();
	}
	
	//删除管理员组
	public function delete(){
		$priv_id = intval(xInput::get('priv_id'));
		$priv_id >0 OR $this->showMessage('非法操作');
		if(M('member_group_priv_data')->where(array('parentid'=>$priv_id))->count()){
			$this->showMessage('该菜单下有子菜单，不允许删除');
		}else{
			if(M('member_group_priv_data')->where(array('priv_id'=>$priv_id))->delete()){
				//删除组权限
				M('member_group_priv')->where(array('priv_id'=>$priv_id))->delete();
				$this->showMessage();
			}
			$this->showMessage('操作失败');
		}
	}
}

