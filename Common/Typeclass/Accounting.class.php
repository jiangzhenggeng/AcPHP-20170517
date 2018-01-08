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
		
class Accounting extends Action{
	
	public function add($question){
		$insertQuestionID = $question['questionid'];
		
		$question = xInput::request('question','','post');
		if(!is_array($question['answer']) OR count($question['answer'])<1 ){
			M('question')->where(array('questionid'=>$insertQuestionID))->delete();
				$this->showMessage('请设置一个分录（至少一个分录）');
		}
		
		//写入答案
		$questionAnswerAccounting = M('question_answer_accounting');
		$i = 0;
		foreach($question['answer'] as $k => $v){
			//答案
			$answer = array();
			//评分标志
			$f_answer = '=';
			//格式化数据，统一处理答案格式
			foreach($v as $k2 => $v2){
				$f_answer .= intval($v2['a']).'|';
				$f_answer .= intval($v2['b']).'|';
				//金额需要特殊处理
				$f_answer .= sc_retain_decimal($v2['c']);
				//格式化答案
				$answer[] = array('a'=>intval($v2['a']),'b'=>intval($v2['b']),'c'=>sc_retain_decimal($v2['c']));
			}
			
			$uniqueid = str_pad($insertQuestionID,8,'A',STR_PAD_LEFT).$i;
			$uniqueid = str_pad($uniqueid,16,'A',STR_PAD_RIGHT);
			$questionAnswerData = array(
				'questionid'=>$insertQuestionID,
				'uniqueid'=>$uniqueid,
				'answer'=>var_export($answer,true),
				'f_answer'=>substr($f_answer,1),
				'listorder'=>$i++
			);
			$result = $questionAnswerAccounting->insert($questionAnswerData);
		}
		if($result){
			//写入解析
			M('question_answer_accounting_analysis')->insert(array('questionid'=>$insertQuestionID,'analysis'=>$question['analysis']));
			return true;
		}else{
			M('question')->where(array('questionid'=>$insertQuestionID))->delete();
			$questionAnswerAccounting->where(array('questionid'=>$insertQuestionID))->delete();
			return false;
		}
	}
	
	public function edit($question){
		
		if ( xInput::request('query')!='insert' ){
			//获取答案
			$accounting_answer = M('question_answer_accounting')
				->where(array('questionid'=>$question['questionid']))->order('listorder asc')->getAll();
			foreach($accounting_answer as $k => $v)
				$accounting_answer[$k]['answer'] = string2array($accounting_answer[$k]['answer']);
			$this->assign('accounting_answer', json_encode($accounting_answer));
			//获取解析
			$accounting_analysis = M('question_answer_accounting_analysis')
				->where(array('questionid'=>$question['questionid']))->getOne();
			$this->assign('accounting_analysis', $accounting_analysis);
			return true;
		}
		
		//修改数据
		else{
			
			$insertQuestionID = $question['questionid'];
			//删除原始数据
			M('question_answer_accounting')->where(array('questionid'=>$question['questionid']))->delete();
			M('question_answer_accounting_analysis')->where(array('questionid'=>$question['questionid']))->delete();
			
			$question = xInput::request('question','','post');
			
			if(!is_array($question['answer']) OR count($question['answer'])<1 ){
				M('question')->where(array('questionid'=>$insertQuestionID))->delete();
					$this->showMessage('请设置一个分录（至少一个分录）');
			}
			
			//写入答案
			$questionAnswerAccounting = M('question_answer_accounting');
			$i = 0;
			foreach($question['answer'] as $k => $v){
				//答案
				$answer = array();
				//评分标志
				$f_answer = '=';
				//格式化数据，统一处理答案格式
				foreach($v as $k2 => $v2){
					$f_answer .= intval($v2['a']).'|';
					$f_answer .= intval($v2['b']).'|';
					//金额需要特殊处理
					$f_answer .= sc_retain_decimal($v2['c']);
					//格式化答案
					$answer[] = array('a'=>intval($v2['a']),'b'=>intval($v2['b']),'c'=>sc_retain_decimal($v2['c']));
				}
				
				$uniqueid = str_pad($insertQuestionID,8,'A',STR_PAD_LEFT).$i;
				$uniqueid = str_pad($uniqueid,16,'A',STR_PAD_RIGHT);
				$questionAnswerData = array(
					'questionid'=>$insertQuestionID,
					'uniqueid'=>$uniqueid,
					'answer'=>var_export($answer,true),
					'f_answer'=>substr($f_answer,1),
					'listorder'=>$i++
				);
				$result = $questionAnswerAccounting->insert($questionAnswerData);
			}
			if($result){
				//写入解析
				M('question_answer_accounting_analysis')->insert(array('questionid'=>$insertQuestionID,'analysis'=>$question['analysis']));
				return true;
			}else{
				return false;
			}
		}
	}
	
	public function get($questionid,$user_answer=[]){
		//获取答案
		$accounting_answer['accounting'] = M('question_answer_accounting')
			->where(array('questionid'=>$questionid))->order('listorder asc')->getAll();
		$accelements = array();
		$accelements_temp = M('accelements')->field('accelementsid,accelementsname,number')->getAll();
		foreach($accelements_temp as $k => $v){
			$accelements[$v['accelementsid']] = $v;
		}

		foreach($accounting_answer['accounting'] as $k => $v){
			$accounting_answer['accounting'][$k]['answer'] = string2array($accounting_answer['accounting'][$k]['answer']);
			$index = $accounting_answer['accounting'][$k]['answer'];

			foreach($index as $k2 => $v2){
				if(isset($accelements[$index[$k2]['b']])){
					$accounting_answer['accounting'][$k]['answer'][$k2]['accelementsname'] = $accelements[$index[$k2]['b']]['accelementsname'];
					$accounting_answer['accounting'][$k]['answer'][$k2]['number'] = $accelements[$index[$k2]['b']]['number'];
				}
			}


			if(isset($user_answer[$v['uniqueid']])){
				$accounting_answer['accounting'][$k]['user_answer'] = $user_answer[$v['uniqueid']];
				$index = $accounting_answer['accounting'][$k]['user_answer'];
				foreach($index as $k2 => $v2){
					if(isset($accelements[$index[$k2]['b']])){
						$accounting_answer['accounting'][$k]['user_answer'][$k2]['c'] = sc_retain_decimal($accounting_answer['accounting'][$k]['user_answer'][$k2]['c']);
						$accounting_answer['accounting'][$k]['user_answer'][$k2]['accelementsname'] = $accelements[$index[$k2]['b']]['accelementsname'];
						$accounting_answer['accounting'][$k]['user_answer'][$k2]['number'] = $accelements[$index[$k2]['b']]['number'];
					}
				}
			}
		}
		//获取解析
		$accounting_analysis = M('question_answer_accounting_analysis')
			->where(array('questionid'=>$questionid))->getOne();
			
		$accounting_answer['analysis'] = $accounting_analysis['analysis'];

		return $accounting_answer;
	}

	// +----------------------------------------------------------------------------------
	// + 比较答案的对错
	// +----------------------------------------------------------------------------------
	public function score( & $answer_array){
		$uniqueid_array = $uniqueid_key = [];

		foreach($answer_array as $v2 ){
			foreach($v2 as $k => $v ) {
				foreach($v as $v3 ) {
					if(!isset($uniqueid_array[$k]))$uniqueid_array[$k] = '';
					$uniqueid_array[$k] .= intval($v3['a']).'|'.intval($v3['b']).'|'.sc_retain_decimal($v3['c']);
				}
				$uniqueid_key[] = $k;
			}
		}

		$result = M('question_answer_accounting')->field('questionid,uniqueid,f_answer')
			->where(array('uniqueid'=>array('"'.implode('","',$uniqueid_key).'"','IN')))->getAll();
		$score_result = [];
		$score_result['result']['truenumber'] = 0;
		$score_result['result']['falsenumber'] = 0;

		foreach ($result as $k => $v ){

			$score_result['data'][$v['questionid']][$k] = array(
				'questionid'=>$v['questionid'],
				'uniqueid'=>$v['uniqueid'],
				'trueanswer'=>$v['f_answer'],
				'user_answer'=>$uniqueid_array[$v['uniqueid']]
			);

			if( $uniqueid_array[$v['uniqueid']]==$v['f_answer'] ){
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