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
// + 试题管理
// +--------------------------------------------------------------------------------------
class QuestionAction extends AdminCommon{

	public function __construct(){
		if( get_parent_class()!='' && method_exists ( get_parent_class(), '__construct' ) ){
			parent::__construct();
		}
		$this->tree_obj = xTree::getInstance ();
		$this->tree_obj->tree_id = 'treeid';
	}

	//试题管理
	//@parme 是否管理回收站
	public function lists($recycle=false){

        $search = xInput::request('search');
        $question = M('question q');
        if ($recycle) {
            $this->assign('recycle', true);
            $question->where(array('q.isdelete' => 1));
        } else {
            $this->assign('recycle', false);
            $question->where(array('q.isdelete' => 0));
        }

        if (intval($search['questionid']) > 0) {
            $question->where(array('q.questionid' => intval($search['questionid'])));
        }else{
            if (intval($search['typeid']) > 0) $question->where(array('q.typeid' => intval($search['typeid'])));
            if (intval($search['subjectid']) > 0){
                $_POST['subjectid'] = $search['subjectid'];
                $question->where(array('q.subjectid' => intval($search['subjectid'])));
            }
            if (intval($search['treeid']) > 0){
                $_POST['parentid'] = $search['treeid'];
                $question->where(array('q.treeid' => intval($search['treeid'])));
            }
            if (intval($search['difficulty']) > 0) $question->where(array('q.difficulty' => intval($search['difficulty'])));

            if ( $search['question'] ){
                $question->where(array('q.question' => array($search['question'],'%LIKE%') ));
            }

            if (trim($search['admin_name']) != '') $question->where(array('q.admin_name' => trim($search['admin_name'])));
            if (strtotime($search['start_time']) != '') $question->where(array('q.addtime' => array(strtotime($search['addtime']), '>=')));
            if (strtotime($search['end_time']) != '') $question->where(array('q.addtime' => array(strtotime($search['end_time']), '<=')));

            if (in_array($search['order'], array('time', 'id'))) {
                if ($search['order'] == 'time') $question->order('q.addtime desc');
                if ($search['order'] == 'id') $question->order('q.questionid desc');
            }else{
                $question->order('q.questionid desc');
            }
            
            if (isset($search['ischeck']) && in_array($search['ischeck'],[-1,1])){
                $question->where(array('q.ischeck' => intval($search['ischeck'])));
            }
        }
        
		$count = $question->count(false);
		$page = $this->page($count);
		$question->limit($page->getLimit());
        $questionData = $question->getAll();

		$subject = M('subject')->getAll();
		$subject_tree = M('subject_tree')->field('treeid,parentid,subjectid,chaptername')->getAll();
		$create_obj = $this->tree_obj->createTree ( $subject_tree );
		$subject_tree = $this->tree_obj->createTreeMenu ($create_obj,'TreeCommon::createTreeChapterOptionToQuestionList');
		$question_type = M('question_type')->getAll();

		$this->assign('question_type', $question_type);
		$this->assign('subject_tree', $subject_tree);
		$this->assign('subject', $subject);
		$this->assign('question', $questionData);
		$this->assign('search', $search);
		$this->assign('page', $page->show());
		$this->display('question/question_list.html');
	}


	//编辑试题
	public function edit(){
		$questionid = intval(xInput::request('questionid'));
		$questionid>0 or $this->showMessage('参数错误');

		//获取试题数据
		$select_question_data = M('question')->where(array('questionid'=>$questionid))->getOne();
		if(count($select_question_data)<=0){
			$this->showMessage('试题不存在');
		}
		if ( xInput::request('query')!='insert' ){
			$this->assign('question', $select_question_data);
			$j = M('question_type');
			$typeid = $select_question_data['typeid'];
			$subjectid = $select_question_data['subjectid'];
			//获取试题类型数据
			$question_type = $j
				->where(array('typeid'=>$typeid))->getOne();
			$this->assign('question_type', $question_type);
			$this->assign('typeid', $typeid);
			$this->assign('subjectid', $subjectid);
			//获取章节数据
			$subject_tree = M('subject_tree')
				->field('treeid,parentid,chaptername')
				->where(array('subjectid'=>$subjectid))
				->order('listorder asc,treeid desc')->getAll();

			//设置默认选择
			$_GET['parentid'] = $select_question_data['treeid'];
			$create_obj = $this->tree_obj->createTree ( $subject_tree );
			$subject_tree = $this->tree_obj->createTreeMenu ($create_obj,'TreeCommon::createTreeChapterOption');
			$this->assign('subject_tree', $subject_tree);

			//获取题型数据
			$subject_question_type = M('subject_question_type sqt')->field('qt.*')
				->leftJoin('question_type qt', 'qt.typeid=sqt.typeid')
				->where(array('sqt.subjectid'=>$subjectid))->getAll();
			$this->assign('subject_question_type', $subject_question_type);

			//获取当前科目
			$subject = M('subject')->where(array('subjectid'=>$subjectid))->getOne();
			$this->assign('subject', $subject);

			//获取试题附加数据
			$questionTypeClass = ucfirst(strtolower($question_type['modelclass']));

			$RradioObj = new $questionTypeClass();
			$RradioObj->edit($select_question_data);

			//生成系统令牌
			$this->assign('token', $this->token() );
			$this->display('question/question_edit.html');
		}else{
			$this->verifyToken(xInput::request('token'),false)
			or $this->showMessage('校验系统令牌失败');

			$question = xInput::request('question','','post');

			$insertData = array();
			if(intval($question['treeid'])<=0) $this->showMessage('请选择章节');
			if(intval($question['difficulty'])<=0) $this->showMessage('请选择试题难度');
			if(trim(strip_tags($question['question']))=='') $this->showMessage('请填写试题');

			//获取关键关联数据
			$_chapterData = M('subject_tree')->field('chaptername')->where(array('treeid'=>$question['treeid']))->getOne();

			$insertData['`chaptername`'] = $_chapterData['chaptername'];
			$insertData['`treeid`']		 = intval($question['treeid']);
			$insertData['`difficulty`']	 = intval($question['difficulty']);
			$insertData['`question`']	 = $question['question'];
			$insertData['`description`'] = $question['description'];
			$insertData['`classics`'] = intval($question['classics']);
			$insertData['`isreal`'] = intval($question['isreal']);
			$insertData['`updatetime`']	 = time();

			$questionModel = M('question');

			$questionModel->where(array('questionid'=>$questionid))->update($insertData);

			//获取试题类型数据
			$question_type = M('question_type')
				->where(array('typeid'=>$select_question_data['typeid']))->getOne();

			$questionTypeClass = ucfirst(strtolower($question_type['modelclass']));
			$RradioObj = new $questionTypeClass();
			$result = $RradioObj->edit($select_question_data);

			//销毁令牌
			$this->destroyToken();
			$this->showMessage('修改成功',U('',array('questionid'=>$questionid)));
		}
	}


	//添加试题
	public function add(){
		if ( xInput::request('query')=='subjectselect' ){
			$SubjectQuestionType = M('subject_question_type sqt');
			//选择科目
			$subject = M('subject')->order('listorder desc,subjectid desc')->getAll();
			foreach($subject as $k => $v ){
				$subject[$k]['question_type'] = $SubjectQuestionType->field('qt.*')->leftJoin('question_type qt', 'qt.typeid=sqt.typeid')
					->where(array('subjectid'=>$v['subjectid']))->getAll();
				if(count($subject[$k]['question_type'])<=0){
					unset($subject[$k]);
				}
			}
			$this->assign('subject', $subject);
			$this->display('question/question_add_to_subject.html');

		}elseif ( xInput::request('query')=='showaddpage' ){
			$subjectid = intval(xInput::request('subjectid'));
			$subjectid >0 OR $this->showMessage('请选择科目');

			$typeid = intval(xInput::request('typeid'));
			$typeid >0 OR $this->showMessage('请选择题型');

			$this->assign('subjectid', $subjectid);
			$this->assign('typeid', $typeid);

			//获取试题类型数据
			$question_type = M('question_type')
				->where(array('typeid'=>$typeid))->getOne();
			$this->assign('question_type', $question_type);

			//获取章节数据
			$subject_tree = M('subject_tree')
				->field('treeid,parentid,chaptername')
				->where(array('subjectid'=>$subjectid))
				->order('listorder asc,treeid desc')->getAll();
			$parentid = intval(xInput::request('parentid'));
			$_GET['parentid'] = $parentid?$parentid:intval(xInput::request('treeid'));

			$create_obj = $this->tree_obj->createTree ( $subject_tree );
			$subject_tree = $this->tree_obj->createTreeMenu ($create_obj,'TreeCommon::createTreeChapterOption');
			$this->assign('subject_tree', $subject_tree);
			//获取题型数据
			$subject_question_type = M('subject_question_type sqt')->field('qt.*')
				->leftJoin('question_type qt', 'qt.typeid=sqt.typeid')
				->where(array('sqt.subjectid'=>$subjectid))->getAll();
			$this->assign('subject_question_type', $subject_question_type);

			//获取当前科目
			$subject = M('subject')->where(array('subjectid'=>$subjectid))->getOne();
			$this->assign('subject', $subject);
			//生成系统令牌
	        $this->assign('token', $this->token() );
	        $this->display('question/question_add.html');

	    }elseif ( xInput::request('query')=='insert' ){

			$this->verifyToken(xInput::request('token'),false) or $this->showMessage('校验系统令牌失败');

			$question = xInput::request('question','','post');

			$insertData = array();

			if(intval($question['subjectid'])<=0) $this->showMessage('请选择科目');
			if(intval($question['typeid'])<=0) $this->showMessage('请选择题型');
			if(intval($question['treeid'])<=0) $this->showMessage('请选择科目');
			if(intval($question['difficulty'])<=0) $this->showMessage('请选择试题难度');
			if(trim(strip_tags($question['question']))=='') $this->showMessage('请填写试题');

			$admin_id = xSession::get('admin.admin_id');
			//获取关键关联数据
			$_adminData = M('admin')->field('admin_name')->where(array('admin_id'=>$admin_id))->getOne();
			$_subjectData = M('subject')->field('subjectname')->where(array('subjectid'=>$question['subjectid']))->getOne();
			$_typeData = M('question_type')->field('typename')->where(array('typeid'=>$question['typeid']))->getOne();
			$_chapterData = M('subject_tree')->field('chaptername')->where(array('treeid'=>$question['treeid']))->getOne();

			$insertData['`admin_id`'] 	 = xSession::get('admin.admin_id');
			$insertData['`admin_name`']  = $_adminData['admin_name'];
			$insertData['`subjectname`'] = $_subjectData['subjectname'];
			$insertData['`typename`'] 	 = $_typeData['typename'];
			$insertData['`chaptername`'] = $_chapterData['chaptername'];
			$insertData['`subjectid`'] 	 = intval($question['subjectid']);
			$insertData['`typeid`'] 	 = intval($question['typeid']);
			$insertData['`treeid`']		 = intval($question['treeid']);
			$insertData['`difficulty`']	 = intval($question['difficulty']);

			$insertData['`question`']	 = $question['question'];
			$insertData['`description`'] = $question['description'];

			$insertData['`classics`'] = intval($question['classics']);
			$insertData['`isreal`'] = intval($question['isreal']);
			$insertData['`addtime`']	 = time();
			$insertData['`updatetime`']	 = 0;
			$insertData['`ischeck`']	 = -1;
			$insertData['`isdelete`']	 = -1;

			$questionModel = M('question');

			$insertQuestionID = $questionModel->insert($insertData);
			$question['questionid'] = $insertQuestionID;

			if($insertQuestionID){
				$questionTypeClass = ucfirst(strtolower($question['modelclass']));
				$RradioObj = new $questionTypeClass();
				$result = $RradioObj->add($question);
				if($result){
					//销毁令牌
					$this->destroyToken();
					$this->showMessage('添加成功',U('add',array('query'=>'showaddpage','subjectid'=>$question['subjectid'],'typeid'=>$question['typeid'],'treeid'=>$question['treeid'])));
				}else{
					$this->error();
				}
			}
			$this->error();
		}
	}

	//删除试题
	public function delete($recover=1){
		$questionid = intval(xInput::request('questionid','','get'));
		$questionid or $this->showMessage('非法操作');
		$questionModel = M('question');

		$result = $questionModel->where(array('questionid'=>$questionid))->update(['isdelete'=>$recover]);
		if( $result ) $this->success();
		$this->error();
	}

	//试题回收站管理
	public function recycle(){
		$this->lists('recycle');
	}

	//从回收站恢复试题
	public function recover(){
		$this->delete(0);
	}

	//从回收站恢复试题
	public function ischeck(){
		$questionid = intval(xInput::request('questionid'));
		$ischeck = intval(xInput::request('ischeck'));
		$questionid or xOut::json(array('status'=>'error','code'=>-1,'message'=>'非法操作'));
		in_array($ischeck,array(-1,1)) or xOut::json(array('status'=>'error','code'=>-1,'message'=>'参数错误'));

		$questionModel = M('question');
		$result = $questionModel->where(array('questionid'=>$questionid))->update(array('ischeck'=>$ischeck));
		$innerText = '审核';
		if($ischeck==1) $innerText = '取消审核';
		if( $result ){
			xOut::json(array('innerText'=>'<font color="green">'.$innerText.'</font>','status'=>'success','code'=>0,'message'=>'操作成功'));
		}
		xOut::json(array('status'=>'error','code'=>-1,'message'=>'操作失败'));
	}


	public function view(){

		App::setConfig('Config','debug',false);

		$questionid = intval(xInput::request('questionid'));
		$question_data = M('question')->where(array('questionid'=>$questionid))->getOne();
		if(count($question_data)<=0){
			$this->error();
		}
		//获取试题类型数据
		$question_type = M('question_type')
			->where(array('typeid'=>$question_data['typeid']))->getOne();
		$this->assign('question_type', $question_type);

		$this->assign('questionid', $questionid);
		$this->display('question/question_view.html');
	}

	public function get(){
		$QuestionCommon = new QuestionCommon();
		$result = $QuestionCommon->get();
		if($result===false){
			xOut::json(array('status'=>'error','message'=>'参数错误','code'=>-1));
		}
		xOut::json(array('status'=>'success','message'=>'加载成功','code'=>1,'data'=>$result));
	}

}

