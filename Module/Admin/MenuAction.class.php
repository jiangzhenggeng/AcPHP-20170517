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
// + 系统菜单管理模块
// +--------------------------------------------------------------------------------------
class MenuAction extends AdminCommon{
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
		if(true/*!xCache::get('menu')*/){
			$this->tree_obj->fix_array = array('','　','│','├','└','<span class="ac-query-click" flag="none">','</span>');
			$menu = M('admin_group_priv_data')->order('`group` asc,listorder asc,priv_id desc')->getAll();
			$create_obj = $this->tree_obj->createTree ( $menu );
			$menu = $this->tree_obj->createTreeMenu ($create_obj,'TreeCommon::createTreeMenuTable');
			xCache::set('menu',$menu);
		}else{
			$menu = xCache::get('menu');
		}
		$this->assign('menu', $menu );
		$this->display('menu/menu_list.html');
	}
	
	//修复数据
	public function repair(){
		$result = M('admin_group_priv_data')->repair();
		$this->success();
	}
	
	
	//系统菜单添加
	public function add(){
		if ( !isset($_POST['query']) || $_POST['query']!='insert' ){
			$menu = M('admin_group_priv_data')->order('listorder asc,priv_id desc')->getAll();
			$tree_obj = xTree::getInstance ();
			$create_obj = $tree_obj->createTree ( $menu );
			$menu = $tree_obj->createTreeMenu ($create_obj,'TreeCommon::createTreeMenuOption');
	        $this->assign('menu', $menu );
			//生成系统令牌
	        $this->assign('token', $this->token() );
			$this->display('menu/menu_add.html');
	    }else{
			//校验系统令牌
	        $this->verifyToken(xInput::request('token')) or $this->error();
			$menu = xInput::request('menu');
			$menu['module'] = strtolower($menu['module']);
			$menu['controller'] = strtolower($menu['controller']);
			$menu['action'] = strtolower($menu['action']);
			$menu['data'] = strtolower($menu['data']);
			
			$result = M('admin_group_priv_data')->insert($menu);
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
			$cur_menu = M('admin_group_priv_data')->where(['priv_id'=>$priv_id])->getOne();
			$_GET['parentid'] = $cur_menu['parentid'];
			$menu = M('admin_group_priv_data')->order('listorder asc,priv_id desc')->getAll();
			$tree_obj = xTree::getInstance ();
			$create_obj = $tree_obj->createTree ( $menu );
			$menu = $tree_obj->createTreeMenu ($create_obj,'TreeCommon::createTreeMenuOption');
	        $this->assign('menu', $menu );
			$this->assign('cur_menu', $cur_menu );
			//生成系统令牌
	        $this->assign('token', $this->token() );
			$this->display('menu/menu_edit.html');
	    }else{
			//校验系统令牌
	        $this->verifyToken(xInput::request('token')) or $this->error();
			$menu = xInput::request('menu');
			$menu['module'] = strtolower($menu['module']);
			$menu['controller'] = strtolower($menu['controller']);
			$menu['action'] = strtolower($menu['action']);
			$menu['data'] = strtolower($menu['data']);
			
			$result = M('admin_group_priv_data')->where(['priv_id'=>$priv_id])->update($menu);
			if($result){
				$this->success(U('lists'));
			}
			$this->error();
		}
	}
	
	//排序
	public function listorder($model=NULL){
		if(parent::listorder(M('admin_group_priv_data'))){
			$this->success();
		}
		$this->error();
	}
	
	
	//分组
	public function group(){
		$group = xInput::post('group');
		if (is_array ( $group ) && count ( $group ) > 0) {
			$AdminGroupPrivData = M('admin_group_priv_data');
			$model_key = $AdminGroupPrivData->getKey();
			foreach ( $group as $key => $val ) {
				$AdminGroupPrivData->clearWhere()->where(array($model_key=>$key))->update(array('`group`'=>$val));
			}
			$this->success(U('lists'));
		}
		$this->error();
	}
	
	//删除管理员组
	public function delete(){
		$priv_id = intval(xInput::get('priv_id'));
		$priv_id >0 OR $this->showMessage('非法操作');
		if(M('admin_group_priv_data')->where(array('parentid'=>$priv_id))->count()){
			$this->showMessage('该菜单下有子菜单，不允许删除');
		}else{
			if(M('admin_group_priv_data')->where(array('priv_id'=>$priv_id))->delete()){
				//删除组权限
				M('admin_group_priv')->where(array('priv_id'=>$priv_id))->delete();
				$this->showMessage();
			}
			$this->showMessage('操作失败');
		}
	}
	
	//获取菜单列表
	public function listsmenu(){
		$priv_id = intval(xInput::request('priv_id',0));
		if($priv_id <=0 ){
			xOut::json(array('message'=>'非法操作','status'=>'error'));
			exit;
		}
		$menu = M('admin_group_priv_data')->order('`group` asc,listorder asc,priv_id desc')->where(['parentid'=>$priv_id])->getAll();
		if(empty($menu)){
			xOut::json(array('message'=>'没有子级菜单','status'=>'error'));
			exit;
		}
		//超级管理员
		if(intval(xSession::get('admin.group_id'))==1){
			foreach($menu as $k => $v ){
				$menu[$k]['url'] = U($v['module'].'/'.$v['controller'].'/'.$v['action'],array('data'=>$v['data']));
				$menu[$k]['menuname'] = $v['priv_name'];
				$menu[$k]['group'] = $v['group'];
			}
			xOut::json($menu);
			exit;
		}
		$mymenu = M('admin_group_priv')->where(['group_id'=>xSession::get('admin.group_id')])->getAll();
		$mymenu_add = array();
		foreach($mymenu as $k => $v ){
			$mymenu_add[] = $v['priv_id'];
		}
		$outMenu = array();
		foreach($menu as $k => $v ){
			if(in_array($v['priv_id'],$mymenu_add) || $v['type']==1){
				$outMenu[$k]['url'] = U($v['module'].'/'.$v['controller'].'/'.$v['action'],array('data'=>$v['data']));
				$outMenu[$k]['menuname'] = $v['priv_name'];
				$outMenu[$k]['group'] = $v['group'];
			}
		}
		if(empty($outMenu)){
			xOut::json(array('message'=>'没有子级菜单','status'=>'error'));
			exit;
		}
		xOut::json($outMenu);
		exit;
	}
}

