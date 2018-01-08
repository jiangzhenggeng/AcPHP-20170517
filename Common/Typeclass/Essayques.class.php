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
		
class Essayques extends Action{
	
	public function add($question){
		$insertQuestionID = $question['questionid'];
		
		$question = xInput::request('question','','post');			
		
		$questionAnswerEssayques = M('question_answer_essayques');
		
		$uniqueid = str_pad($insertQuestionID,8,'A',STR_PAD_LEFT);
		$uniqueid = str_pad($uniqueid,16,'A',STR_PAD_RIGHT);
		//写入答案
		$questionAnswerData = array(
			'questionid'=>$insertQuestionID,
			'uniqueid'=>$uniqueid,
			'answer'=>$question['answer'],
			'pointer'=>array2string($question['pointer']),
			'analysis'=>$question['analysis'],
			'analysis'=>$question['analysis'],
			'f_answer'=>strip_tags($question['answer'])
		);
		$result = $questionAnswerEssayques->insert($questionAnswerData);
			
		if($result){
			return true;
		}else{
			M('question')->where(array('questionid'=>$insertQuestionID))->delete();
			$questionAnswerEssayques->where(array('questionid'=>$insertQuestionID))->delete();
			return false;
		}
	}
	
	
	//编辑试题的时候获取试题附加数据
	public function edit($question){
		if ( xInput::request('query')!='insert' ){
			//获取答案
			//获取解析
			$analysis = M('question_answer_essayques')
				->where(array('questionid'=>$question['questionid']))->getOne();
			$analysis['pointer'] = string2array($analysis['pointer']);
			
			$this->assign('analysis', $analysis);
			return true;
		}
		
		//修改数据
		else{
			
			$insertQuestionID = $question['questionid'];
			//删除原始数据
			M('question_answer_essayques')->where(array('questionid'=>$question['questionid']))->delete();
		
			$question = xInput::request('question','','post');
			
			$uniqueid = str_pad($insertQuestionID,8,'A',STR_PAD_LEFT);
			$uniqueid = str_pad($uniqueid,16,'A',STR_PAD_RIGHT);
			$questionAnswerEssayques = M('question_answer_essayques');
			//写入答案
			$questionAnswerData = array(
				'questionid'=>$insertQuestionID,
				'uniqueid'=>$uniqueid,
				'answer'=>$question['answer'],
				'pointer'=>var_export($question['pointer'],true),
				'analysis'=>$question['analysis'],
				'f_answer'=>strip_tags($question['answer'])
			);
			$result = $questionAnswerEssayques->insert($questionAnswerData);
				
			if($result){
				return true;
			}else{
				return false;
			}
		}
	}
	
	public function get($questionid){
		$analysis = M('question_answer_essayques')
				->where(array('questionid'=>$questionid))->getOne();
		$analysis['pointer'] = string2array($analysis['pointer']);

		return $analysis;
	}

	// +----------------------------------------------------------------------------------
	// + 比较答案的对错
	// +----------------------------------------------------------------------------------
	public function score( & $answer_array){
       
		$uniqueid_array = $uniqueid_key = [];
		foreach($answer_array as $k => $v ){
			$uniqueid_array[ $v['uniqueid'] ] = $v['user_answer'];
			$uniqueid_key[] = $v['uniqueid'];
		}
		$result = M('question_answer_essayques')
			->where(array('uniqueid'=>array('"'.implode('","',$uniqueid_key).'"','IN')))->getAll();
		$score_result = [];
		$score_result['result']['truenumber'] = 0;
		$score_result['result']['falsenumber'] = 0;

		foreach ($result as $k => $v ){
			$pointer = json_decode($v['pointer'],true);
			$score_result['data'][$v['questionid']] = array(
				'questionid'=>$v['questionid'],
				'uniqueid'=>$v['uniqueid'],
				'trueanswer'=>$v['answer'],
				'user_answer'=>$uniqueid_array[$v['uniqueid']],
				'pointer'=>$pointer,
			);
			$score_result['data'][$v['questionid']]['istrue'] = 0;
			foreach($pointer as $pk => $pv ){
				if( strpos($uniqueid_array[$v['uniqueid']],$pv)!==FALSE ){
					$score_result['data'][$v['questionid']]['istrue']++;
				}else{	
					$score_result['data'][$v['questionid']]['isfalse']++;
				}
			}
		}
		return $score_result;
		
	}
	
}



















