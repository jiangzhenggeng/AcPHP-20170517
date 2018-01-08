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
// + 科目章节管理模块
// +--------------------------------------------------------------------------------------
class ChapterAction extends AdminCommon{
	private $tree_obj = null;
	
	public function __construct(){
		if( get_parent_class()!='' && method_exists ( get_parent_class(), '__construct' ) ){
			parent::__construct();
		}
		$this->tree_obj = xTree::getInstance ();
		$this->tree_obj->tree_id = 'treeid';
	}
	
	//
	public function lists(){
		$subjectid = intval(xInput::request('subjectid',0));
		if($subjectid <= 0){
			$subjectOne = M('subject_tree')->field('subjectid')->where(array('parentid'=>0))->getOne();
			$subjectid = $subjectOne['subjectid'];
		}
		$subject_tree = M('subject_tree')
			->where(['subjectid'=>$subjectid])
			->order('listorder asc,treeid asc')->getAll();
		$this->tree_obj->fix_array = array('','　','│','├','└','<span class="ac-query-click" flag="none">','</span>');
		$create_obj = $this->tree_obj->createTree ( $subject_tree );
		$subject_tree = $this->tree_obj->createTreeMenu ($create_obj,'TreeCommon::createTreeChapterTable');
		
		$subject = M('subject')->where(array('subjectid'=>$subjectid))->getOne();
				
		$this->assign('subject', $subject);
		$this->assign('subject_tree', $subject_tree);
		$this->display('subject/subject_tree.html');
	}
	
	//添加管理员
	public function add(){
		
		if ( xInput::request('query','')!='insert' ){
			$subjectid = intval(xInput::request('subjectid',0));
			$subjectid >0 OR $this->showMessage('请选择科目');
			
			$treeid = intval(xInput::request('treeid',0));
			$treeid >0 OR $this->showMessage('请选择章节');
			
			$_GET['parentid'] = $treeid;
			
			$subject_tree = M('subject_tree')->where(array('subjectid'=>$subjectid))
				->order('listorder asc,treeid asc')->getAll();
				
			$create_obj = $this->tree_obj->createTree ( $subject_tree );
			$subject_tree = $this->tree_obj->createTreeMenu ($create_obj,'TreeCommon::createTreeChapterOption');
			
			//获取科目
			$subject = M('subject')->where(array('subjectid'=>$subjectid))->getAll();
			
			$this->assign('subjectid', $subjectid);
			$this->assign('subject', $subject);
			$this->assign('subject_tree', $subject_tree);
	        $this->display('subject/subject_tree_add.html');
	    }else{
			$subject_tree = xInput::request('subject_tree');
			$subject_tree['subjectid'] >0 OR $this->showMessage('请选择科目');
			$subject_tree['parentid'] >0 OR $this->showMessage('请选择父章节');
			trim($subject_tree['chaptername']) != '' OR $this->showMessage('请选填写章节名称');
			
			$subject_tree['arrparentid'] = 0;
			$subject_tree['arrchildid'] = 0;
			
			$subject_tree_model = M('subject_tree');
			$batch = xInput::request('batch');
			if(intval($batch)===1){
				//批量添加
				$chaptername_array = explode('|',$subject_tree['chaptername']);
				foreach($chaptername_array as $k => $v ){
					$v = trim($v);
					if($v=='') continue;
					$subject_tree['chaptername'] = $v;
					$result = $subject_tree_model->insert($subject_tree);
				}
			}else{
				$result = $subject_tree_model->insert($subject_tree);
			}
			if($result){
				$this->success(U('/Admin/Chapter/lists',array('subjectid'=>$subject_tree['subjectid'])));
			}
			$this->error();
		}
	}
	//修改管理员
	public function edit($admin_id='',$query=''){
		$treeid = intval(xInput::request('treeid',0));
		$treeid >0 OR $this->showMessage('非法操作');
		
		if ( !isset($_POST['query']) || $_POST['query']!='insert' ){
			$subjectid = intval(xInput::request('subjectid',0));
			$subjectid >0 OR $this->showMessage('请选择科目');
			
			//获取修改数据
			$curr_subject_tree = M('subject_tree')->where(array('treeid'=>$treeid))->getOne();
			
			$_GET['parentid'] = $curr_subject_tree['parentid'];
			
			$subject_tree = M('subject_tree')->where(array('subjectid'=>$subjectid))
				->order('listorder asc,treeid asc')->getAll();
			$create_obj = $this->tree_obj->createTree ( $subject_tree );
			$subject_tree = $this->tree_obj->createTreeMenu ($create_obj,'TreeCommon::createTreeChapterOption');
			
			//获取科目
			$subject = M('subject')->getAll();
			
			$this->assign('curr_subject_tree', $curr_subject_tree);
			$this->assign('subjectid', $subjectid);
			$this->assign('subject', $subject);
			$this->assign('subject_tree', $subject_tree);
	        $this->display('subject/subject_tree_edit.html');
			
	    }else{
			$subject_tree = xInput::request('subject_tree');
			$subject_tree['subjectid'] >0 OR $this->showMessage('请选择科目');
			//$subject_tree['parentid'] >0 OR $this->showMessage('请选择父章节');
			trim($subject_tree['chaptername']) != '' OR $this->showMessage('请选填写章节名称');
			
			$treeid = intval(xInput::request('treeid'));
			$treeid >0 OR $this->showMessage('非法操作');
			
			$subject_tree_model = M('subject_tree');
			$result = $subject_tree_model->where(array('treeid'=>$treeid))->update($subject_tree);
			if($result){
				$this->success(U('/Admin/Chapter/lists',array('subjectid'=>$subject_tree['subjectid'])));
			}
			$this->error();
		}
	}
	//删除章节
	public function delete(){
		$treeid = intval(xInput::request('treeid',0));
		$treeid >0 OR $this->showMessage('请选择章节');
		
		if(M('question')->where(array('subjectid'=>$subjectid))->count()){
			$this->showMessage('该章节下有试题，不允许删除');
		}else{
			$result = M('subject_tree')->where(array('treeid'=>$treeid))->delete();
			if($result) $this->success();
		}
		$this->error();
	}
	
	
	//修复数据
	public function repair(){
		$subjectid = intval(xInput::request('subjectid',0));
		$subjectid >0 OR $this->showMessage('请选择科目');
		$result = M('subject_tree')->repair($subjectid);
		$this->success();
	}
	
	//排序
	public function listorder($model){
		if(parent::listorder(M('subject_tree'))){
			$this->success();
		}
		$this->error();
	}
}

