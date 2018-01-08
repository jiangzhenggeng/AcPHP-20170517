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
		
class Blank extends Action{
	
	public function add($question){
		$insertQuestionID = $question['questionid'];
		
		$question = xInput::request('question','','post');
		if(!is_array($question['answer']) OR count($question['answer'])<1 ){
			M('question')->where(array('questionid'=>$insertQuestionID))->delete();
				$this->showMessage('请设置填空（至少一个填空）');
		}
		
		//写入答案
		$questionAnswerBlank = M('question_answer_blank');
		$i = 0;
		foreach($question['answer'] as $k => $v){
			//格式化数据
			$v = sc_retain_decimal($v);
			$uniqueid = str_pad($insertQuestionID,8,'A',STR_PAD_LEFT).$i;
			$uniqueid = str_pad($uniqueid,16,'A',STR_PAD_RIGHT);
			$questionAnswerData = array(
				'questionid'=>$insertQuestionID,
				'uniqueid'=>$uniqueid,
				'answer'=>$v,
				'listorder'=>$i++
			);
			$result = $questionAnswerBlank->insert($questionAnswerData);
		}
		if($result){
			//写入解析
			M('question_answer_blank_analysis')->insert(array('questionid'=>$insertQuestionID,'analysis'=>$question['analysis']));
			return true;
		}else{
			M('question')->where(array('questionid'=>$insertQuestionID))->delete();
			$questionAnswerBlank->where(array('questionid'=>$insertQuestionID))->delete();
			return false;
		}
	}
	
	//编辑试题的时候获取试题附加数据
	public function edit($question){
		if ( xInput::request('query')!='insert' ){
			//获取答案
			$blank_answer = M('question_answer_blank')
				->where(array('questionid'=>$question['questionid']))
				->order('listorder asc')->field('answer')->getAll();
			$this->assign('blank_answer', json_encode($blank_answer));
			//获取解析
			$blank_analysis = M('question_answer_blank_analysis')
				->where(array('questionid'=>$question['questionid']))->getOne();
			$this->assign('blank_analysis', $blank_analysis);
			return true;
		}
		
		//修改数据
		else{
			
			$insertQuestionID = $question['questionid'];
			//删除原始数据
			M('question_answer_blank')->where(array('questionid'=>$question['questionid']))->delete();
			
			M('question_answer_blank_analysis')->where(array('questionid'=>$question['questionid']))->delete();
			
			$question = xInput::request('question','','post');
			if(!is_array($question['answer']) OR count($question['answer'])<1 ){
				M('question')->where(array('questionid'=>$insertQuestionID))->delete();
					$this->showMessage('请设置填空（至少一个填空）');
			}
			
			//写入答案
			$questionAnswerBlank = M('question_answer_blank');
			$i = 0;
			foreach($question['answer'] as $k => $v){
				//格式化数据
				$v = sc_retain_decimal($v);
				$uniqueid = str_pad($insertQuestionID,8,'A',STR_PAD_LEFT).$i;
				$uniqueid = str_pad($uniqueid,16,'A',STR_PAD_RIGHT);
				$questionAnswerData = array(
					'questionid'=>$insertQuestionID,
					'uniqueid'=>$uniqueid,
					'answer'=>$v,
					'listorder'=>$i++
				);
				$result = $questionAnswerBlank->insert($questionAnswerData);
			}
			if($result){
				//写入解析
				M('question_answer_blank_analysis')->insert(array('questionid'=>$insertQuestionID,'analysis'=>$question['analysis']));
				return true;
			}else{
				return false;
			}
		}
	}
	
	public function get($questionid,$user_answer=[]){
		//获取答案
		$blank_answer['answer'] = M('question_answer_blank')
			->where(array('questionid'=>$questionid))
			->order('listorder asc')->field('*')->getAll();
		//获取解析
		$analysis = M('question_answer_blank_analysis')
			->where(array('questionid'=>$questionid))->getOne();
		$blank_answer['analysis'] = $analysis['analysis'];
		foreach ($blank_answer['answer'] as $k => $v ) {
			if(isset($user_answer[$v['uniqueid']])){
				$blank_answer['answer'][$k]['user_answer'] = sc_retain_decimal($user_answer[$v['uniqueid']]);
				$blank_answer['answer'][$k]['selected'] = 'selected';
			}else{
				$blank_answer['answer'][$k]['user_answer'] = '';
				$blank_answer['answer'][$k]['selected'] = 0;
			}
		}

		return $blank_answer;
	}

	// +----------------------------------------------------------------------------------
	// + 比较答案的对错
	// +----------------------------------------------------------------------------------
	public function score( & $answer_array){
		$uniqueid_array = $uniqueid_key = [];

		foreach($answer_array as $v2 ){
			foreach($v2 as $k => $v ) {
				$uniqueid_array[$k] = $v;
				$uniqueid_key[] = $k;
			}
		}

		$result = M('question_answer_blank')->field('questionid,uniqueid,answer')
			->where(array('uniqueid'=>array('"'.implode('","',$uniqueid_key).'"','IN')))->getAll();
		$score_result = [];
		$score_result['result']['truenumber'] = 0;
		$score_result['result']['falsenumber'] = 0;

		foreach ($result as $k => $v ){

			$score_result['data'][$v['questionid']][$k] = array(
				'questionid'=>$v['questionid'],
				'uniqueid'=>$v['uniqueid'],
				'trueanswer'=>sc_retain_decimal($v['answer']),
				'user_answer'=>sc_retain_decimal($uniqueid_array[$v['uniqueid']]),
			);

			if( sc_retain_decimal($uniqueid_array[$v['uniqueid']])==sc_retain_decimal($v['answer']) ){
				$score_result['data'][$v['questionid']][$k]['istrue'] = true;
				$score_result['result']['truenumber']++;
			}else{
				$score_result['data'][$v['questionid']][$k]['istrue'] = false;
				$score_result['result']['falsenumber']++;
			}
		}
		return $score_result;
	}
	
}