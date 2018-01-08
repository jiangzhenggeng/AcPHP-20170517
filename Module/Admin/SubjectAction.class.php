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
// + 科目管理模块
// +--------------------------------------------------------------------------------------
class SubjectAction extends AdminCommon{

	public function __construct(){
		if( get_parent_class()!='' && method_exists ( get_parent_class(), '__construct' ) ){
			parent::__construct();
		}
		
	}
	
	//科目列表
	public function lists(){
		$m = M('subject s');
        $search = xInput::request('search');

		$majorid = intval($search['majorid']);

		if($majorid>0) $m->where(array('s.majorid'=>$majorid));
		$count = $m->count();
		$page = $this->page($count);
		$m->limit($page->getLimit()); 
		if($majorid>0) $m->where(array('s.majorid'=>$majorid));
		$subject = $m->field('s.*,m.majorname')->leftJoin('major m','s.majorid=m.majorid')
			->order('s.listorder desc,s.subjectid desc')->getAll();
		if($majorid>0) $this->assign('majorid', $majorid);;
		$this->assign('subject', $subject);
		$this->assign('page', $page->show());

        $major = M('major')->getAll();
        $this->assign('major', $major);
        $this->assign('search', $search);

		$this->display('subject/subject_list.html');
	}
	
	//添加科目
	public function add(){
		if ( xInput::request('query')!='insert' ){
			//生成系统令牌
	        $this->assign('token', $this->token() );
			//获取管理员组
			$major = M('major')->getAll();
	        $this->assign('major', $major );
	        $this->display('subject/subject_add.html');
	    }else{
			//校验系统令牌
	        $this->verifyToken(xInput::request('token')) or $this->showMessage('校验系统令牌失败');
			$subject = xInput::request('subject');
			$subjectid = M('subject')->getNextId();
			$result = M('subject')->insert($subject);
			if($result){
				$subject_tree = array(
					'subjectid'=>$subjectid,
					'parentid'=>0,
					'arrparentid'=>0,
					'arrchildid'=>M('subject_tree')->getNextId(),
					'chaptername'=>$subject['subjectname'],
					'keywords'=>'',
					'description'=>''
				);
				$treeid = M('subject_tree')->insert($subject_tree);
				if($treeid) $this->showMessage('添加成功',U('lists'));
			}
			$this->error();
		}
	}
	//修改科目
	public function edit(){
		$subjectid = intval(xInput::request('subjectid'));
		$subjectid >0 OR $this->showMessage('非法操作');
		
		if ( xInput::request('query')!='insert' ){
			//生成系统令牌
	        $this->assign('token', $this->token() );
			$subject = M('subject')->where(array('subjectid'=>$subjectid))->getOne();
	        $this->assign('subject', $subject );
			$major = M('major')->getAll();
	        $this->assign('major', $major );
	        $this->display('subject/subject_edit.html');
	    }else{
			//校验系统令牌
	        $this->verifyToken(xInput::request('token')) or $this->showMessage('校验系统令牌失败');
			$subject = xInput::request('subject');
			
			$result = M('subject')->where(array('subjectid'=>$subjectid))->update($subject);
			if($result){
				$this->showMessage('修改成功',U('lists'));
			}
			$this->error();
		}
	}
	//删除科目
	public function delete(){
		$subjectid = intval(xInput::get('subjectid'));
		$subjectid >0 OR $this->error();
		$count = M('question')->where(array('subjectid'=>$subjectid))->count();
		if($count>0) $this->showMessage('该科目下有试题，不允许删除',U('lists'));
		
		if(M('subject')->where(array('subjectid'=>$subjectid))->delete()){
			$this->success();
		}
		$this->error();
	}
	
	//删除题型
	public function questiondelete(){
			$subjectid 	= intval(xInput::get('subjectid'));
			$typeid 		= intval(xInput::get('typeid'));
			($subjectid >0 && $typeid) >0 OR $this->error();
			
			$count = M('question')->where(array('subjectid'=>$subjectid,'typeid'=>$typeid))->count();
			if($count>0) $this->showMessage('该题型下有试题，不允许删除',U('lists'));
			
			if(M('subject_question_type')->where(array('subjectid'=>$subjectid,'typeid'=>$typeid))->delete()){
					$this->success();
			}
			$this->error();
	}
	
	//排序
	public function listorder($model){
		if(parent::listorder(M('subject'))){
			$this->success();
		}
		$this->error();
	}

	//配置题型
	public function settingquestiontype(){

		$subjectid = intval(xInput::get('subjectid'));
		$subjectid >0 OR $this->error();

		$m = M('subject_question_type qt');
		$m->where(array('qt.subjectid'=>$subjectid));
		$count = $m->count();
		$page = $this->page($count);
		$m->limit($page->getLimit());

		$questionType = $m->field('qt.*,t.typename,t.description')->leftJoin('question_type t','qt.typeid=t.typeid')
			->where(array('subjectid'=>$subjectid))->order('qt.typeid')->getAll();

		$subject = M('subject')->where(array('subjectid'=>$subjectid))->getOne();

		$this->assign('page', $page->show());
		$this->assign('questionType',$questionType);
		$this->assign('subject',$subject);
		$this->display('subject/setting_question_type.html');

	}
	
	// 添加题型
	public function addquestiontype(){
		
			$subjectid = intval(xInput::get('subjectid'));
			$subjectid >0 OR $this->error();
			
			if ( xInput::request('query')!='insert' ){
					//生成系统令牌
	        $this->assign('token', $this->token() );
					$m = M('question_type');
					$question_type = $m->getAll();
					$subject = M('subject')->where(array('subjectid'=>$subjectid))->getOne();
					$subjectTypes = M('subject_question_type')->where(array('subjectid'=>$subjectid))->getAll();
					foreach($subjectTypes as $v){
							$subjectType[] = $v['typeid'];		
					}
					$this->assign('token', $this->token() );
					$this->assign('subject',$subject);
					$this->assign('subjectType',$subjectType);
					$this->assign('question_type', $question_type);
					$this->display('subject/add_question_type.html');
	    }else{
					$this->verifyToken(xInput::request('token')) or $this->showMessage('校验系统令牌失败');
					$question = xInput::request('question');
					$m = M('subject_question_type');
					
					foreach($question as $k=>$v){
							$subject = M('subject_question_type')->where(array('subjectid'=>$subjectid,'typeid'=>$v))->getOne();
							if($subject) continue;
							$questions['typeid'] = $v;
							$questions['subjectid']	 = $subjectid;
							$result = M('subject_question_type')->insert($questions);
					}
					if($result){
							$this->showMessage('添加成功',U('settingquestiontype',array('subjectid'=>$subjectid)));
					}
					$this->error();
					
			}
			
			
		
	}

	//统计试题
	public function statisticalQuestion(){
        $subjectid = intval(xInput::get('subjectid'));
        $subjectid >0 OR $this->error();
        $subjectTreeModel = M('subject_tree');
        $subject_tree = $subjectTreeModel->where(array('subjectid'=>$subjectid))->getAll();
        $questionMOdel = M('question');

        foreach ($subject_tree as $k => $v ){
            $count = $questionMOdel->clearWhere()
                ->where('treeid in('.$v['arrchildid'].')')
                ->count();
            $subjectTreeModel->clearWhere()->where(array('treeid'=>$v['treeid']))->update(array('number'=>$count));
        }
        $this->success();
    }
}