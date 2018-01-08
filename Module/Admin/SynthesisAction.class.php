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
// + 管理员组管理模块
// +--------------------------------------------------------------------------------------
class SynthesisAction extends AdminCommon{

	public function __construct(){
		if( get_parent_class()!='' && method_exists ( get_parent_class(), '__construct' ) ){
			parent::__construct();
		}
		
	}
	
	//管理员列表
	public function lists($recycle=false){
		if($recycle){
			$this->assign('recycle', true);
			$delete = 1;
		}else{
			$this->assign('recycle', false);
			$delete = 0;
		}
		$paperSynthesisModel = M('paper_synthesis');
		
		$count = $paperSynthesisModel->where(array('`delete`'=>$delete))->count(false);
		$page = $this->page($count);
		$paper = $paperSynthesisModel->order('paperid desc')
			->where(array('`delete`'=>$delete))
			->limit($page->getLimit())->getAll();
		foreach($paper as $k => $v){
			$paper[$k]['question_total'] = M('paper_synthesis_data')->where(array('paperid'=>$v['paperid']))->count();
		}
		$this->assign('paper', $paper);
		$this->assign('page', $page->show());
		$this->display('synthesis/paper_list.html');
	}
	
	//添加管理员
	public function add(){
		$query = xInput::request('query');
		$ruleid = $groupid = intval(xInput::request('groupid'));
		if($query=='getsubject' && $ruleid>0  && C_IS_AJAX){
			$rule_group = M('rule_group')->where(array('groupid'=>$ruleid))->getOne();
			$rule_group['ruleconfig'] = string2array($rule_group['ruleconfig']);
			$subjectModel = M('subject');
			$rule_group['ruleconfig'] = $subjectModel->where(array('subjectid'=>array(implode(',',array_keys($rule_group['ruleconfig'])),'in')))->getAll();
			xOut::json($rule_group);
		}
		elseif ( $query!='insert' ){
			$major = M('major')->getAll();
	        $this->assign('major', $major );
			
			$rule_group = M('rule_group')->getAll();
	        $this->assign('rule_group', $rule_group );
			
			//生成系统令牌
	        $this->assign('token', $this->token() );
	        $this->display('synthesis/paper_add.html');
	    }else{

			//校验系统令牌
	        if(!$this->verifyToken(xInput::request('token'))){
				$this->error('令牌校验错误');
			}
			$paper = xInput::request('paper');
			
			$paper['starttime'] = strtotime($paper['starttime']);
			$paper['adminid'] = xSession::get('admin.admin_id');
			$paper['adminname'] = xSession::get('admin.admin_name');
			$paper['addtime'] = time();
			
			$paperid = M('paper_synthesis')->insert($paper);

			if($paperid){
				//写入科目
				$paper_subject = xInput::request('paper_subject');
				if(!is_array($paper_subject) && count($paper_subject)<=0){
					$this->error('请选择科目');
				}

				$groupid = $paper['groupid'];

				$rule_group = M('rule_group')->where(array('groupid'=>$groupid))->getOne();
				$rule_group['ruleconfig'] = string2array($rule_group['ruleconfig']);

				$totaltime = 0;
				foreach ($paper_subject as $k => $v){
					$totaltime += intval($v['totaltime']);
					$paper_subject[$k]['paperid'] = $paperid;
					$paper_subject[$k]['ruleid'] = $rule_group['ruleconfig'][$v['subjectid']];
				}
				$result = M('paper_synthesis_subject')->insert($paper_subject);
				M('paper_synthesis')->where(array('paperid'=>$paperid))
					->update(array('totaltime'=>$totaltime));
				if($result)
					$this->success();
				else
					$this->error();
			}
			$this->error();
		}
	}
	//修改管理员
	public function edit(){

		$paperid = intval(xInput::request('paperid'));
		$paperid >0 OR $this->showMessage('非法操作');
		$query = xInput::request('query');
		$ruleid = $groupid = intval(xInput::request('groupid'));

		if($query=='getsubject' && $ruleid>0 && C_IS_AJAX ){
			$paper_synthesis_subject = M('paper_synthesis_subject')->where(array('paperid'=>$paperid))->getAll();
			$subject_string = '';
			$paper_synthesis_subject_temp = [];
			foreach ($paper_synthesis_subject as $k => $v ){
				$subject_string .= ','.$v['subjectid'];
				$paper_synthesis_subject_temp[$v['subjectid']] = $v;
			}

			unset($paper_synthesis_subject);

			$subject_string = substr($subject_string,1);
			if(!$subject_string) xOut::json([]);
			$subjectData = M('subject')->field('subjectid,subjectname')->where(array('subjectid'=>[$subject_string,'IN']))->getAll();
			foreach ($subjectData as $k => $v){
				$paper_synthesis_subject_temp[$v['subjectid']]['subjectname'] = $v['subjectname'];
			}
			xOut::json($paper_synthesis_subject_temp);
		}
		elseif ( $query!='insert' ){
			$paper = M('paper_synthesis')->where(array('paperid'=>$paperid))->getOne();
	        $this->assign('paper', $paper );

			$major = M('major')->getAll();
			$this->assign('major', $major );

			$rule_group = M('rule_group')->getAll();
			$this->assign('rule_group', $rule_group );
			
			//生成系统令牌
	        $this->assign('token', $this->token() );
	        $this->display('synthesis/paper_edit.html');
	    }else{
			//校验系统令牌
			if(!$this->verifyToken(xInput::request('token'))){
				//$this->error('令牌校验错误');
			}
			$paper = xInput::request('paper');

			$paper['starttime'] = strtotime($paper['starttime']);

			//M('paper_synthesis')->where(['paperid'=>$paperid])->update($paper);

			//写入科目
			$paper_subject = xInput::request('paper_subject');
			if(!is_array($paper_subject) && count($paper_subject)<=0){
				$this->error('请选择科目');
			}
			$groupid = $paper['groupid'];

			$rule_group = M('rule_group')->where(array('groupid'=>$groupid))->getOne();
			$rule_group['ruleconfig'] = string2array($rule_group['ruleconfig']);

			$totaltime = 0;
			foreach ($paper_subject as $k => $v){
				$totaltime += intval($v['totaltime']);
				$paper_subject[$k]['paperid'] = $paperid;
				$paper_subject[$k]['ruleid'] = $rule_group['ruleconfig'][$v['subjectid']];
			}
			$paper_synthesis_subjectModel = M('paper_synthesis_subject');
			$paper_synthesis_subjectModel->where(['paperid'=>$paperid])->delete();

			$result = $paper_synthesis_subjectModel->insert($paper_subject);

			$paper['totaltime'] = $totaltime;
			M('paper_synthesis')->where(array('paperid'=>$paperid))
				->update($paper);
			
			if($result)
				$this->success();
			else
				$this->error();
		}
	}
	//删除试卷（放入回收站）
	public function delete(){
		$paperid = intval(xInput::get('paperid'));
		$paperid >0 OR $this->error();
		if(M('paper_synthesis')->where(array('paperid'=>$paperid))->delete()){
			M('paper_synthesis_data')->where(array('paperid'=>$paperid))->delete();
			M('paper_synthesis_subject')->where(array('paperid'=>$paperid))->delete();
			$this->success();
		}
		$this->error();
	}
	
	//根据抽题规则进行抽题
	public function getquestion(){
		$paperid = intval(xInput::get('paperid'));
		$paperid >0 OR xOut::json(array('status'=>'error','code'=>-1,'message'=>'非法操作'));
		
		$paperDataAll = M('paper_synthesis_subject')->where(array('paperid'=>$paperid))->getAll();

		$message = '';
		$insertData = array();
		
		foreach ($paperDataAll as $k => $v ){
			$v['id'] = $v['paperid'];
			$PaperGetDataObj = new PaperGetData();

			$questionData = $PaperGetDataObj->paper_get_question($v);

			if( count($questionData)<=0 ) $message .= '规则ID'.$v['ruleid'].'没有试题，';
			//写入数据
			foreach($questionData as $k2 => $v2){
				$insertData[] = array(
					'paperid'=>$paperid,
					'subjectid'=>$v['subjectid'],
					'questionid'=>$v2['questionid'],
					'typeid'=>$v2['typeid']
				);
			}
		}
		if(count($insertData)<=0){
			xOut::json(array('status'=>'error','code'=>-1,'message'=>$message));
		}
		$paper_data_obj = M('paper_synthesis_data');
		$paper_data_obj->where(array('paperid'=>$paperid))->delete();

		$result = $paper_data_obj->insert($insertData);
		if( $result ){
			$count = count($insertData);
			if($count>0) M('paper_synthesis')->where(array('paperid'=>$paperid))->update(array('hasquestion'=>1));
			xOut::json(array('total'=>'<font color="red">'.$count.'<font>','status'=>'success','code'=>0,'message'=>'抽取成功（'.$count.'）道题'.$message));
		}
	}

	public function view($c=''){
		parent::view('paper_synthesis_data');
	}
}

