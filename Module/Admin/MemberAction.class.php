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
class MemberAction extends AdminCommon{
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
		$m = M('member m');
		$count = $m->count();
		$page = $this->page($count);
		$m->limit($page->getLimit()); 
		$member = $m->order('m.registertime desc')->field('m.*,mg.group_name')
			->leftJoin('member_group mg','mg.groupid=m.groupid')->getAll();
		$this->assign('member', $member);
		$this->assign('page', $page->show());
		$this->display('member/member_list.html');
	}
	
	//添加管理员组
	public function add(){
		if ( !isset($_POST['query']) || $_POST['query']!='insert' ){
			//生成系统令牌
	        $this->assign('token', $this->token() );
			$member_group = M('member_group')->where(array('isshow'=>1))->order('groupid desc')->getAll();
			$this->assign('member_group', $member_group);
	        $this->display('member/member_add.html');
	    }else{
			//校验系统令牌
	        if(!$this->verifyToken(xInput::request('token'))){
				$this->showMessage('令牌验证错误');
			}
			$member = xInput::request('member');
			$member['registertime'] = time();
			$member['account'] = 'xck'.$member['mobile'];
			$result = M('member')->insert($member);
			if($result){
				$this->showMessage('添加成功',U('lists'));
			}
			$this->showMessage('添加失败');
		}
	}
	//添加管理员组
	public function edit(){
		$memberid = intval(xInput::request('memberid'));
		$memberid >0 OR $this->showMessage('非法操作');
		
		if ( !isset($_POST['query']) || $_POST['query']!='insert' ){
			//生成系统令牌
	        $this->assign('token', $this->token() );
			$member = M('member')->where(array('memberid'=>$memberid))->getOne();
			$member_group = M('member_group')->where(array('isshow'=>1))->order('groupid desc')->getAll();
			$this->assign('member_group', $member_group);
	        $this->assign('member', $member );
	        $this->display('member/member_edit.html');
	    }else{
			//校验系统令牌
	        if(!$this->verifyToken(xInput::request('token'))){
				$this->showMessage('令牌验证错误');
			}
			$member = xInput::request('member');
			$member['account'] = 'xck'.$member['mobile'];
			$result = M('member')->where(array('memberid'=>$memberid))->update($member);
			if($result){
				$this->showMessage('',U('lists'));
			}
			$this->showMessage('编辑失败');
		}
	}
	//删除管理员组
	public function delete(){
		$memberid = intval(xInput::get('memberid'));
		$memberid >0 OR $this->showMessage('非法操作');
		$member = xInput::request('member');
		if(M('admin')->where(array('memberid'=>$memberid))->count()){
			$this->showMessage('该组下有管理员，不允许删除');
		}else{
			if(M('member')->where(array('memberid'=>$memberid))->delete()){
				//删除组权限
				M('member_priv')->where(array('memberid'=>$memberid))->delete();
				$this->showMessage();
			}
			$this->showMessage('操作失败');
		}
	}
	//排序
	public function listorder($model=NULL){
		if(parent::listorder(M('member'))){
			$this->showMessage();
		}
		$this->showMessage('操作失败');
	}
	
	
	//权限分配
	public function privSetting(){
		$memberid = intval(xInput::request('memberid'));
		$memberid >0 OR $this->showMessage('非法操作');
		
		if ( xInput::request('query')!='insert' ){
			$menu = M('member_priv_data')->order('listorder desc,priv_id desc')->getAll();
			$adminmemberPrivModel = M('member_priv');
			foreach ($menu as $k => $v ){
				$where = array('memberid'=>$memberid,'priv_id'=>$v['priv_id']);
				$menu[$k]['has_priv'] = $adminmemberPrivModel->clearWhere()->where($where)->count();
			}
			$this->tree_obj->fix_array = array('','　','│','├','└','<span class="ac-query-click" flag="none">','</span>');
			$create_obj = $this->tree_obj->createTree ( $menu );
			$menu = $this->tree_obj->createTreeMenu ($create_obj,'TreeCommon::createTreeMenuTable');
			$this->assign('menu', $menu );
			$this->assign('memberid', $memberid );
			$this->assign('token', $this->token() );
			$this->display('member/member_priv_setting.html');
		}else{
			if(!$this->verifyToken(xInput::request('token')))$this->showMessage('令牌验证错误');
			
			$member_priv = xInput::request('member_priv','');
			($member_priv =='' || is_array($member_priv)) OR $this->showMessage('请选择权限');
			$adminmemberPrivModel = M('member_priv');
			$adminmemberPrivDataModel = M('member_priv_data');
			//首先清空所有条件
			$adminmemberPrivModel->where(['memberid'=>$memberid])->delete();
			foreach($member_priv as $k => $v ){
				//获取权限
				$cur_priv = $adminmemberPrivDataModel->clearWhere()->where(['priv_id'=>$v])->getOne();
				//写入权限
				$adminmemberPrivModel->clearWhere()->insert(array('memberid'=>$memberid,'priv_id'=>$v));
			}
			$this->showMessage('操作成功',U('member/member/lists'));
		}
		
	}
	
}

