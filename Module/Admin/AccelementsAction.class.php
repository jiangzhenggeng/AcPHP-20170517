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
// + 会计分录元素管理模块
// +--------------------------------------------------------------------------------------
class AccelementsAction extends AdminCommon{

	public function __construct(){
		if( get_parent_class()!='' && method_exists ( get_parent_class(), '__construct' ) ){
			parent::__construct();
		}
		$this->tree_obj = xTree::getInstance ();
		$this->tree_obj->tree_id = 'accelementsid';
	}
	
	//元素列表
	public function lists(){
		$m = M('accelements');
		$accelements = $m->getAll();
		$this->tree_obj->fix_array = array('','　','│','├','└','<span class="ac-query-click" flag="none">','</span>');
		$create_obj = $this->tree_obj->createTree ( $accelements );
		//添加试题点击刷新会计元素获取
		if(xInput::get('query')=='add_get_acc_option_list'){
			$accelements = $this->tree_obj->createTreeMenu ($create_obj,'AccTreeCommon::createTreeAccOption');
			echo $accelements;
			exit;
		}
		$accelements = $this->tree_obj->createTreeMenu ($create_obj,'AccTreeCommon::createTreeAccTable');
		$this->assign('accelements', $accelements);
		$this->display('question/accelements_list.html');
	}
	
	public function add(){
		if ( !isset($_POST['query']) || $_POST['query']!='insert' ){
			//生成系统令牌
	        $this->assign('token', $this->token() );
			$curr_accelementsid = intval(xInput::request('accelementsid'));
			if($curr_accelementsid>0){
				//设置默认选择父类
				$_GET['parentid'] = $curr_accelementsid;
			}
			$accelements_tree = M('accelements')->getAll();
	        $create_obj = $this->tree_obj->createTree ( $accelements_tree );
			$accelements_tree = $this->tree_obj->createTreeMenu ($create_obj,'AccTreeCommon::createTreeAccOption');
			$this->assign('accelements_tree', $accelements_tree);
	        $this->display('question/accelements_add.html');
	    }else{
			//校验系统令牌
	        if(!$this->verifyToken(xInput::request('token'))){
				$this->error('令牌校验错误');
			}
			$accelements = xInput::request('accelements');
			$accelements['arrparentid'] = 0;
			$accelements['arrchildid'] = 0;
			
			$result = M('accelements')->insert($accelements);
			if($result){
				$this->success();
			}
			$this->error();
		}
	}

	public function edit(){
		$accelementsid = $accelementsid?$accelementsid:intval(xInput::request('accelementsid'));
		$accelementsid >0 OR $this->showMessage('非法操作');
		
		if ( !isset($_POST['query']) || $_POST['query']!='insert' ){
			//生成系统令牌
	        $this->assign('token', $this->token() );
			
			$accelements = M('accelements')->where(array('accelementsid'=>$accelementsid))->getOne();
	        
			//设置默认选择父类
			$_GET['parentid'] = $accelements['parentid'];
			
			$accelements_tree = M('accelements')->getAll();
	        $create_obj = $this->tree_obj->createTree ( $accelements_tree );
			$accelements_tree = $this->tree_obj->createTreeMenu ($create_obj,'AccTreeCommon::createTreeAccOption');
			$this->assign('accelements_tree', $accelements_tree);
			
			$this->assign('accelements', $accelements );
			$this->assign('accelementsid', $accelementsid );
	       $this->display('question/accelements_edit.html');
	    }else{
			//校验系统令牌
	        $this->verifyToken(xInput::request('token')) or $this->showMessage('校验系统令牌失败');
			$accelements = xInput::request('accelements');
			$accelementsObj = M('accelements');
			$result = $accelementsObj->where(array('accelementsid'=>$accelementsid))->update($accelements);
			if($result){
				$this->success();
			}
			$this->error();
		}
	}
	
	public function delete(){
		$accelementsid = intval(xInput::get('accelementsid'));
		$accelementsid >0 OR $this->error();
		if(M('accelements')->where(array('accelementsid'=>$accelementsid))->delete()){
			$this->success();
		}
		$this->error();
	}
	//排序
	public function listorder($model){
		if(parent::listorder(M('accelements'))){
			$this->success();
		}
		$this->error();
	}
	
	//修复数据
	public function repair(){
		$result = M('accelements')->repair();
		$this->success();
	}
}

