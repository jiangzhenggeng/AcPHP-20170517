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
// + 专业管理
// +--------------------------------------------------------------------------------------
class MajorAction extends AdminCommon{

	public function __construct(){
		if( get_parent_class()!='' && method_exists ( get_parent_class(), '__construct' ) ){
			parent::__construct();
		}
		
	}
	
	//管理员列表
	public function lists(){
		$m = M('major');
		$count = $m->count();
		$page = $this->page($count);
		$m->limit($page->getLimit()); 
		$major_temp = $m->order('listorder desc,majorid desc')->getAll();

		$majoridArray = $major = [];
		foreach ($major_temp as $k => $v ){
			$majoridArray[] = $v['majorid'];
			$major[$v['majorid']] = $v;
		}
		if( count($majoridArray) && !empty($majoridArray)){
			$subject = M('subject')->field('majorid,price')->where(['majorid'=>[implode(',',$majoridArray),'IN']])->getAll();
			foreach ($subject as $k => $v ){
				if(!isset($major[$v['majorid']]['price'])) $major[$v['majorid']]['price'] = 0;
				$major[$v['majorid']]['price'] += $v['price'];
				$major[$v['majorid']]['price'] = sc_retain_decimal($major[$v['majorid']]['price']);
			}
		}
		$this->assign('major', $major);
		$this->assign('page', $page->show());
		$this->display('major/major_list.html');
	}
	
	//添加专业
	public function add(){
		if ( xInput::request('query')!='insert' ){
			//生成系统令牌
	        $this->assign('token', $this->token() );
	        $this->display('major/major_add.html');
	    }else{
			$this->verifyToken(xInput::request('token')) or $this->showMessage('校验系统令牌失败');
			$major = xInput::request('major');
			$result = M('major')->insert($major);
			if($result){
				$this->showMessage('添加成功',U('lists'));
			}
			$this->error();
		}
	}
	//修改专业
	public function edit(){
		$majorid = intval(xInput::request('majorid'));
		$majorid >0 OR $this->showMessage('非法操作');
		
		if ( xInput::request('query')!='insert' ){
			//生成系统令牌
	        $this->assign('token', $this->token() );
			$major = M('major')->where(array('majorid'=>$majorid))->getOne();
	        $this->assign('major', $major );
	        $this->display('major/major_edit.html');
	    }else{
	        $this->verifyToken(xInput::request('token')) or $this->showMessage('校验系统令牌失败');
			$major = xInput::request('major');
			
			$result = M('major')->where(array('majorid'=>$majorid))->update($major);
			if($result){
				$this->showMessage('编辑成功',U('lists'));
			}
			$this->error();
		}
	}
	//删除管理员
	public function delete(){
		$majorid = intval(xInput::get('majorid'));
		$majorid >0 OR $this->error();
		$HasSubject = M('subject')->where(array('majorid'=>$majorid))->count();
		if($HasSubject>0){
			$this->showMessage('该专业下有科目，不允许删除');
		}
		if(M('major')->where(array('majorid'=>$majorid))->delete()){
			$this->success();
		}
		$this->error();
	}
	
	//排序
	public function listorder($model){
		if(parent::listorder(M('major'))){
			$this->success();
		}
		$this->error();
	}
}

