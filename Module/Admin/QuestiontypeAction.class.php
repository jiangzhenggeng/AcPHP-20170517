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
// + 试题模型管理
// +--------------------------------------------------------------------------------------
class QuestiontypeAction extends AdminCommon{

	public function __construct(){
		if( get_parent_class()!='' && method_exists ( get_parent_class(), '__construct' ) ){
			parent::__construct();
		}
		
	}
	
	//模型管理
	public function lists(){
		$m = M('question_type');
		$count = $m->count();
		$page = $this->page($count);
		$m->limit($page->getLimit()); 
		$question_type = $m->getAll();
		$this->assign('question_type', $question_type);
		$this->assign('page', $page->show());
		$this->display('question/question_type_list.html');
	}
	
	//添加模型
	public function add(){
		if ( xInput::request('query')!='insert' ){
			//生成系统令牌
	        $this->assign('token', $this->token() );
	        $this->display('question/question_type_add.html');
	    }else{
			$this->verifyToken(xInput::request('token')) or $this->showMessage('校验系统令牌失败');
			$question_type = xInput::request('question_type');
			$result = M('question_type')->insert($question_type);
			if($result){
				$this->showMessage('添加成功',U('lists'));
			}
			$this->error();
		}
	}
	//修改模型
	public function edit(){
		$typeid = intval(xInput::request('typeid'));
		$typeid >0 OR $this->showMessage('非法操作');
		
		if ( xInput::request('query')!='insert' ){
			//生成系统令牌
	        $this->assign('token', $this->token() );
			$question_type = M('question_type')->where(array('typeid'=>$typeid))->getOne();
	        $this->assign('question_type', $question_type );
	        $this->display('question/question_type_edit.html');
	    }else{
	        $this->verifyToken(xInput::request('token')) or $this->showMessage('校验系统令牌失败');
			$question_type = xInput::request('question_type');
			
			$result = M('question_type')->where(array('typeid'=>$typeid))->update($question_type);
			if($result){
				$this->showMessage('修改成功',U('lists'));
			}
			$this->error();
		}
	}
	//删除模式
	public function delete(){
		$typeid = intval(xInput::get('typeid'));
		$typeid >0 OR $this->error();
		$HasModelSublect = M('subject_question_type')->where(array('typeid'=>$typeid))->count();
		$HasModel = M('question')->where(array('typeid'=>$typeid))->count();
		if($HasModel>0 OR $HasModelSublect>0){
			$this->showMessage('该模型下有试题，或者科目下包含该模型，不允许删除');
		}
		if(M('question_type')->where(array('typeid'=>$typeid))->delete()){
			$this->success();
		}
		$this->error();
	}
}

