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
// + 会员组管理模块
// +--------------------------------------------------------------------------------------
class MembergroupAction extends AdminCommon{
	private $tree_obj = null;
	
	public function __construct(){
		if( get_parent_class()!='' && method_exists ( get_parent_class(), '__construct' ) ){
			parent::__construct();
		}
		$this->tree_obj = xTree::getInstance ();
		$this->tree_obj->tree_id = 'priv_id';
	}
	
	//管理员组列表
	public function lists(){
		$m = M('member_group');
		$count = $m->count();
		$page = $this->page($count);
		$m->limit($page->getLimit()); 
		$group = $m->order('listorder desc,groupid desc')->getAll();
		$this->assign('group', $group);
		$this->assign('page', $page->show());
		$this->display('member/group_list.html');
	}
	
	//添加管理员组
	public function add(){
		if ( !isset($_POST['query']) || $_POST['query']!='insert' ){
			//生成系统令牌
	        $this->assign('token', $this->token() );
	        $this->display('member/group_add.html');
	    }else{
			//校验系统令牌
	        if(!$this->verifyToken(xInput::request('token'))){
				$this->showMessage('令牌验证错误');
			}
			$group = xInput::request('group');
			$result = M('member_group')->insert($group);
			if($result){
				$this->showMessage('添加成功',U('lists'));
			}
			$this->showMessage('添加失败');
		}
	}
	//添加管理员组
	public function edit(){
		$groupid = intval(xInput::get('groupid'));
		$groupid >0 OR $this->showMessage('非法操作');
		
		if ( !isset($_POST['query']) || $_POST['query']!='insert' ){
			//生成系统令牌
	        $this->assign('token', $this->token() );
			$group = M('member_group')->where(array('groupid'=>$groupid))->getOne();
			if($group['isedit']!=1){
				$this->showMessage('不允许编辑');
			}
	        $this->assign('group', $group );
	        $this->display('member/group_edit.html');
	    }else{
			//校验系统令牌
	        if(!$this->verifyToken(xInput::request('token'))){
				$this->showMessage('令牌验证错误');
			}
			$group = xInput::request('group');
			$result = M('member_group')->where(array('groupid'=>$groupid))->update($group);
			if($result){
				$this->showMessage('',U('lists'));
			}
			$this->showMessage('编辑失败');
		}
	}
	//删除管理员组
	public function delete(){
		$groupid = intval(xInput::get('groupid'));
		$groupid >0 OR $this->showMessage('非法操作');
		$group = xInput::request('group');
		if(M('admin')->where(array('groupid'=>$groupid))->count()){
			$this->showMessage('该组下有管理员，不允许删除');
		}else{
			if(M('member_group')->where(array('groupid'=>$groupid))->delete()){
				//删除组权限
				M('member_group_priv')->where(array('groupid'=>$groupid))->delete();
				$this->showMessage();
			}
			$this->showMessage('操作失败');
		}
	}
	//排序
	public function listorder($model=NULL){
		if(parent::listorder(M('member_group'))){
			$this->showMessage();
		}
		$this->showMessage('操作失败');
	}
	
	
	//权限分配
	public function privSetting(){
		$groupid = intval(xInput::request('groupid'));
		$groupid >0 OR $this->showMessage('非法操作');
		
		if ( xInput::request('query')!='insert' ){
			$member_group_priv_data = M('member_group_priv_data')->order('listorder desc,priv_id desc')->getAll();
			$adminGroupPrivModel = M('member_group_priv');
			foreach ($member_group_priv_data as $k => $v ){
				$where = array('groupid'=>$groupid,'priv_id'=>$v['priv_id']);
				$member_group_priv_data[$k]['has_priv'] = $adminGroupPrivModel->clearWhere()->where($where)->count();
			}
			$this->tree_obj->fix_array = array('','　','│','├','└','<span class="ac-query-click" flag="none">','</span>');
			$create_obj = $this->tree_obj->createTree ( $member_group_priv_data );
			$member_group_priv_data = $this->tree_obj->createTreeMenu ($create_obj,'MemberTreeCommon::createTreeMemberTableSetting');
			$this->assign('member_group_priv_data', $member_group_priv_data );
			$this->assign('groupid', $groupid );
			$this->assign('token', $this->token() );
			$this->display('member/group_priv_setting.html');
		}else{
			if(!$this->verifyToken(xInput::request('token')))$this->showMessage('令牌验证错误');
			
			$group_priv = xInput::request('group_priv','');
			($group_priv =='' || is_array($group_priv)) OR $this->showMessage('请选择权限');
			$adminGroupPrivModel = M('member_group_priv');
			$adminGroupPrivDataModel = M('member_group_priv_data');
			//首先清空所有条件
			$adminGroupPrivModel->where(['groupid'=>$groupid])->delete();
			foreach($group_priv as $k => $v ){
				//获取权限
				$cur_priv = $adminGroupPrivDataModel->clearWhere()->where(['priv_id'=>$v])->getOne();
				//写入权限
				$adminGroupPrivModel->clearWhere()->insert(array('groupid'=>$groupid,'priv_id'=>$v));
			}
			$this->showMessage('操作成功',U('Admin/Membergroup/lists'));
		}
		
	}
	
}

