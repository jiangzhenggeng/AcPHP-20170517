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
		
class Multiple extends Action{
	
	public function add($parme_question){
		$insertQuestionID = $parme_question['questionid'];
		
		$question = xInput::request('question','','post');
		if(!is_array($question['sub_question']) OR count($question['sub_question'])<1 ){
			M('question')->where(array('questionid'=>$insertQuestionID))->delete();
			$this->showMessage('请设置问题（至少一项）');
		}
		
		$questionOption = M('question_option_multiple');
		$question_answer_multiple_model = M('question_answer_multiple');
		$i = 1;
		foreach($question['sub_question'] as $k => $v){
			if(trim($v['sub_question'])=='') continue;
			$questionOptionData = array(
				'questionid'=>$insertQuestionID,
				'optiontext'=>$v['sub_question'],
				'optionname'=>'试题'.$i,
				'parentid'=>0,
				'listorder'=>$i++
			);
			$optionid = $questionOption->insert($questionOptionData);
			//如果写入子题目失败
			if(!$optionid) continue;
			$l = 0 ;
			foreach($v['option'] as $k2 => $v2){
				
				if(trim($v2)=='' || trim($k2)=='') continue;
				$questionOptionData = array(
					'questionid'=>$insertQuestionID,
					'optiontext'=>strtoupper($v2),
					'optionname'=>$k2,
					'parentid'=>$optionid,
					'listorder'=>$l++
				);
				$result = $questionOption->insert($questionOptionData);
			}
				
			//插入答案
			$uniqueid = str_pad($insertQuestionID,8,'A',STR_PAD_LEFT).$optionid;
			$uniqueid = str_pad($uniqueid,16,'A',STR_PAD_RIGHT);
			if(!empty($v['answer'])){
				$answe = '';
				foreach($v['answer'] as $k3 => $v3){
					$answe .= strtoupper($v3);
				}
				//写入答案
				$questionAnswerData = array(
					'questionid'=>$insertQuestionID,
					'uniqueid'=>$uniqueid,
					'answer'=>$answe,
					'analysis'=>$v['analysis']
				);
				$result2 = $question_answer_multiple_model->insert($questionAnswerData);
			}
		}
		
		if($optionid){
			return true;
		}else{
			M('question')->where(array('questionid'=>$insertQuestionID))->delete();
			M('question_answer_multiple')->where(array('questionid'=>$insertQuestionID))->delete();
			$questionOption->where(array('questionid'=>$insertQuestionID))->delete();
			return false;
		}
	}
	
	
	public function edit($question){
		
		if ( xInput::request('query')!='insert' ){
			
			$insertQuestionID = $question['questionid'];
			//获取子选项
			$multiple_option = M('question_option_multiple')->order('listorder asc')
				->where(array('questionid'=>$question['questionid']))->getAll();
			//获取答案
			$multiple_answer = M('question_answer_multiple')
				->where(array('questionid'=>$question['questionid']))->getAll();
				
			foreach($multiple_option as $k => $v){
				if($v['parentid']!=0) continue;
				$uniqueid = str_pad($insertQuestionID,8,'A',STR_PAD_LEFT).$v['optionid'];
				$uniqueid = str_pad($uniqueid,16,'A',STR_PAD_RIGHT);
				$hasAnswer = false;
				foreach($multiple_answer as $k2 => $v2){
					if($v2['uniqueid']==$uniqueid){
						$multiple_option[$k]['answer'] = $v2['answer'];
						$multiple_option[$k]['analysis'] = $v2['analysis'];
						$multiple_option[$k]['uniqueid'] = $v2['uniqueid'];
						$hasAnswer = true;
					}
				}
				if($hasAnswer==false){
					$multiple_option[$k]['answer'] = '';
					$multiple_option[$k]['analysis'] = '';
					$multiple_option[$k]['uniqueid'] = '';
				}
			}
			
			$tree_obj = xTree::getInstance ();
			$tree_obj->tree_id = 'optionid';
			$multiple_option = $tree_obj->createTree ( $multiple_option );
			$this->assign('multiple_option', json_encode($multiple_option));			
			return true;
		}
		
		//修改数据
		else{
			
			$insertQuestionID = $question['questionid'];
			//删除原始数据
			M('question_answer_multiple')->where(array('questionid'=>$question['questionid']))->delete();
			M('question_option_multiple')->where(array('questionid'=>$question['questionid']))->delete();
			
			$question = xInput::request('question','','post');

			if(!is_array($question['sub_question']) OR count($question['sub_question'])<1 ){
				$this->showMessage('请设置问题（至少一项）');
			}
			
			$questionOption = M('question_option_multiple');
			$question_answer_multiple_model = M('question_answer_multiple');
			$i = 1;
			foreach($question['sub_question'] as $k => $v){
				if(trim($v['sub_question'])=='') continue;
				$questionOptionData = array(
					'questionid'=>$insertQuestionID,
					'optiontext'=>$v['sub_question'],
					'optionname'=>'试题'.$i,
					'parentid'=>0,
					'listorder'=>$i++
				);
				$optionid = $questionOption->insert($questionOptionData);
				//如果写入子题目失败
				if(!$optionid) continue;
				$l = 0 ;
				foreach($v['option'] as $k2 => $v2){
					
					if(trim($v2)=='' || trim($k2)=='') continue;
					$questionOptionData = array(
						'questionid'=>$insertQuestionID,
						'optiontext'=>strtoupper($v2),
						'optionname'=>$k2,
						'parentid'=>$optionid,
						'listorder'=>$l++
					);
					$result = $questionOption->insert($questionOptionData);
				}
					
				//插入答案
				$uniqueid = str_pad($insertQuestionID,8,'A',STR_PAD_LEFT).$optionid;
				$uniqueid = str_pad($uniqueid,16,'A',STR_PAD_RIGHT);
				if(!empty($v['answer'])){
					$answe = '';
					foreach($v['answer'] as $k3 => $v3){
						$answe .= strtoupper($v3);
					}
					//写入答案
					$questionAnswerData = array(
						'questionid'=>$insertQuestionID,
						'uniqueid'=>$uniqueid,
						'answer'=>$answe,
						'analysis'=>$v['analysis']
					);
					$result2 = $question_answer_multiple_model->insert($questionAnswerData);
				}
			}
			
			if($optionid){
				return true;
			}else{
				return false;
			}
		}
	}
	
	public function get($questionid,$user_answer=[]){
		//第一种获取试题的方式，一道一道的获取
		$optionid = intval(xInput::request('optionid'));
		$gettype = intval(xInput::request('gettype'));
		if($gettype==2 && $optionid>0 ){
			//获取子选项
			$question_option_multiple_model = M('question_option_multiple');
			$multiple_option = $question_option_multiple_model->order('listorder asc')
				->where('(questionid='.$questionid.' AND optionid='.$optionid.')')
				->where(array('parentid'=>array($optionid,'=','OR')))->getAll();
				//echo $question_option_multiple_model->getSql();
				//exit;
				//print_r($multiple_option);
			//获取答案
			$uniqueid = str_pad($questionid,8,'A',STR_PAD_LEFT).$optionid;
			$uniqueid = str_pad($uniqueid,16,'A',STR_PAD_RIGHT);
			$multiple_answer = M('question_answer_multiple')
				->where(array('uniqueid'=>$uniqueid))->getAll();
			
		}else{
			//获取子选项
			$multiple_option = M('question_option_multiple')->order('listorder asc')
				->where(array('questionid'=>$questionid))->getAll();
			//获取答案
			$multiple_answer = M('question_answer_multiple')
				->where(array('questionid'=>$questionid))->getAll();
		}
		
		foreach($multiple_option as $k => $v){
			if($v['parentid']!=0) continue;
			$uniqueid = str_pad($questionid,8,'A',STR_PAD_LEFT).$v['optionid'];
			$uniqueid = str_pad($uniqueid,16,'A',STR_PAD_RIGHT);
			$hasAnswer = false;
			foreach($multiple_answer as $k2 => $v2){
				if($v2['uniqueid']==$uniqueid){
					$multiple_option[$k]['answer'] = $v2['answer'];
					$multiple_option[$k]['analysis'] = $v2['analysis'];
					$multiple_option[$k]['uniqueid'] = $v2['uniqueid'];

					if(isset($user_answer[$v2['uniqueid']])){
						$multiple_option[$k]['user_answer'] = implode('',$user_answer[$v2['uniqueid']]);
					}
					$hasAnswer = true;
				}
			}
			if($hasAnswer==false){
				$multiple_option[$k]['answer'] = '';
				$multiple_option[$k]['analysis'] = '';
				$multiple_option[$k]['uniqueid'] = '';
			}
		}
		$tree_obj = xTree::getInstance ();
		$tree_obj->tree_id = 'optionid';
		$multiple_option = $tree_obj->createTree ( $multiple_option );

		foreach ($multiple_option as $k => $v ) {
			if(isset($v['children']) && count($v['children'])){
				foreach ($v['children'] as $k2 => $v2 ) {
					if(strpos($v['user_answer'],$v2['optionname'])!==false){
						$multiple_option[$k]['children'][$k2]['selected'] = 'selected';
					}else{
						$multiple_option[$k]['children'][$k2]['selected'] = 0;
					}
				}
			}
		}
		return $multiple_option;
	}

	// +----------------------------------------------------------------------------------
	// + 比较答案的对错
	// +----------------------------------------------------------------------------------
	public function score( & $answer_array){
		$uniqueid_array = $uniqueid_key = [];
		
		foreach($answer_array as  $k => $v ){
			$uniqueid_array[ $v['uniqueid'] ] = $v['user_answer'];
			$uniqueid_key[] = $v['uniqueid'];
		}

		$result = M('question_answer_multiple')->field('questionid,uniqueid,answer')
			->where(array('uniqueid'=>array('"'.implode('","',$uniqueid_key).'"','IN')))
			->order('questionid desc')
			->getAll();

		$score_result = [];
		$score_result['result']['truenumber'] = 0;
		$score_result['result']['falsenumber'] = 0;


		foreach ($result as $k => $v ){

			$score_result['data'][$v['questionid']][$k] = array(
				'questionid'=>$v['questionid'],
				'uniqueid'=>$v['uniqueid'],
				'trueanswer'=>strtoupper($v['answer']),
				'user_answer'=>strtoupper($uniqueid_array[$v['uniqueid']]),
			);

			if( strtoupper($uniqueid_array[$v['uniqueid']])==strtoupper($v['answer']) ){
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












