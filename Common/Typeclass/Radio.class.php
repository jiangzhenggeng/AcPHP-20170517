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
		
class Radio extends Action{
	
	public function add($question){
		$insertQuestionID = $question['questionid'];
		
		$question = xInput::request('question','','post');
		if(!is_array($question['option']) OR count($question['option'])<2 ){
			M('question')->where(array('questionid'=>$insertQuestionID))->delete();
			$this->showMessage('请设置选项（至少两项）');
		}
				
		$answeAscii = ord(strtoupper($question['answer']));
		if($answeAscii<65 OR $answeAscii>97 ){
			M('question')->where(array('questionid'=>$insertQuestionID))->delete();
			$this->showMessage('请设置答案');
		}
			
		
		$questionOption = M('question_option_radio');
		$i = 0;
		foreach($question['option'] as $k => $v){
			if(trim($v)=='' || trim($k)=='') continue;
			$questionOptionData = array(
				'questionid'=>$insertQuestionID,
				'optiontext'=>$v,
				'listorder'=>$i++,
				'parentid'=>0,
				'optionname'=>$k
			);
			$result = $questionOption->insert($questionOptionData);
		}
		
		$uniqueid = str_pad($insertQuestionID,8,'A',STR_PAD_LEFT);
		$uniqueid = str_pad($uniqueid,16,'A',STR_PAD_RIGHT);
		//写入答案
		$questionAnswerData = array(
			'questionid'=>$insertQuestionID,
			'uniqueid'=>$uniqueid,
			'answer'=>strtoupper($question['answer']),
			'analysis'=>$question['analysis']
		);
		$result2 = M('question_answer_radio')->insert($questionAnswerData);
			
		if($result && $result2){
			return true;
		}else{
			M('question')->where(array('questionid'=>$insertQuestionID))->delete();
			M('question_answer_radio')->where(array('questionid'=>$insertQuestionID))->delete();
			$questionOption->where(array('questionid'=>$insertQuestionID))->delete();
			return false;
		}
	}
	
	//编辑试题的时候获取试题附加数据
	public function edit($question){
		if ( xInput::request('query')!='insert' ){
			//获取答案
			$radio_answer = M('question_option_radio')
				->where(array('questionid'=>$question['questionid']))->order('listorder asc')->getAll();
			$this->assign('radio_answer', json_encode($radio_answer));
			//获取解析
			$radio_analysis = M('question_answer_radio')
				->where(array('questionid'=>$question['questionid']))->getOne();
			$this->assign('radio_analysis', $radio_analysis);
			return true;
		}
		
		//修改数据
		else{
			
			$insertQuestionID = $question['questionid'];
			//删除原始数据
			M('question_option_radio')->where(array('questionid'=>$question['questionid']))->delete();
			
			M('question_answer_radio')->where(array('questionid'=>$question['questionid']))->delete();
			
			$question = xInput::request('question','','post');
			if(!is_array($question['option']) OR count($question['option'])<2 ){
				$this->showMessage('请设置选项（至少两项）');
			}
					
			$answeAscii = ord(strtoupper($question['answer']));
			if($answeAscii<65 OR $answeAscii>97 ){
				$this->showMessage('请设置答案');
			}
			
			//写入答案
			$questionOption = M('question_option_radio');
			$i = 0 ;
			foreach($question['option'] as $k => $v){
				if(trim($v)=='' || trim($k)=='') continue;
				$questionOptionData = array(
					'questionid'=>$insertQuestionID,
					'optiontext'=>$v,
					'listorder'=>$i++,
					'parentid'=>0,
					'optionname'=>$k
				);
				$result = $questionOption->insert($questionOptionData);
			}
			
			$uniqueid = str_pad($insertQuestionID,8,'A',STR_PAD_LEFT);
			$uniqueid = str_pad($uniqueid,16,'A',STR_PAD_RIGHT);
			//写入答案
			$questionAnswerData = array(
				'questionid'=>$insertQuestionID,
				'uniqueid'=>$uniqueid,
				'answer'=>strtoupper($question['answer']),
				'analysis'=>$question['analysis']
			);
			$result2 = M('question_answer_radio')->insert($questionAnswerData);
				
			if($result && $result2){
				return true;
			}else{
				return false;
			}
		}
	}
	
	// +----------------------------------------------------------------------------------
	// + 获取试题附加数据
	// +----------------------------------------------------------------------------------
	public function get($questionid,$user_answer=[]){
		$radio_answer['option'] = M('question_option_radio')
			->where(array('questionid'=>$questionid))->order('listorder asc')->getAll();
		//获取解析
		$radio_analysis = M('question_answer_radio')->field('uniqueid,analysis,answer')
			->where(array('questionid'=>$questionid))->getOne();
		$radio_answer['analysis'] = $radio_analysis['analysis'];
		$radio_answer['answer'] = $radio_analysis['answer'];
		$radio_answer['uniqueid'] = $radio_analysis['uniqueid'];
		$radio_answer['user_answer'] = implode('',$user_answer);
		foreach ($radio_answer['option'] as $k => $v ) {
			if($v['optionname']==$radio_answer['user_answer']){
				$radio_answer['option'][$k]['selected'] = 'selected';
			}else{
				$radio_answer['option'][$k]['selected'] = 0;
			}
		}
		return $radio_answer;
	}

	// +----------------------------------------------------------------------------------
	// + 比较答案的对错
	// +----------------------------------------------------------------------------------
	public function score( & $answer_array,$memberid,$type=1){
		$uniqueid_array = $uniqueid_key = [];

		foreach($answer_array as $k => $v ){
			$uniqueid_array[ $v['uniqueid'] ] = $v['user_answer'];
			$uniqueid_key[] = $v['uniqueid'];
		}
		$result = M('question_answer_radio')->field('questionid,uniqueid,answer')
			->where(array('uniqueid'=>array('"'.implode('","',$uniqueid_key).'"','IN')))->getAll();
		$score_result = [];
		$score_result['result']['truenumber'] = 0;
		$score_result['result']['falsenumber'] = 0;

        $questionidArray = [];
        foreach ($result as $k => $v ){
            $questionidArray[] = $v['questionid'];
        }
        $questionTemp = M('question')->field('treeid,subjectid')
            ->where(array('questionid'=>array('"'.implode('","',$questionidArray).'"','IN')))->getAll();
        $questionData = [];
        foreach ($questionTemp as $k => $v ){
            $questionData[$v['questionid']] = $v;
        }

        $insertData = [];
        $memberErrorInsertData = [];
		foreach ($result as $k => $v ){

			$score_result['data'][$v['questionid']] = array(
				'questionid'=>$v['questionid'],
				'uniqueid'=>$v['uniqueid'],
				'trueanswer'=>strtoupper($v['answer']),
				'user_answer'=>strtoupper($uniqueid_array[$v['uniqueid']]),
			);

            $insertData_cell = array(
                'memberid'=>$memberid,
                'questionid'=>$v['questionid'],
                'typeid'=>1,
                'type'=>$type,
                'istrue'=>1,
                'addtime'=>time(),
                'answer'=>strtoupper($uniqueid_array[$v['uniqueid']])
            );

			if( strtoupper($uniqueid_array[$v['uniqueid']])==strtoupper($v['answer']) ){
				$score_result['data'][$v['questionid']]['istrue'] = true;
				$score_result['result']['truenumber']++;
                $insertData_cell['istrue'] = 1;
			}else{
				$score_result['data'][$v['questionid']]['istrue'] = false;
				$score_result['result']['falsenumber']++;
                $insertData_cell['istrue'] = 2;

                //保存错题记录
                $memberErrorInsertData[] = array(
                    'memberid'=>$memberid,
                    'questionid'=>$v['questionid'],
                    'typeid'=>2,
                    'type'=>$type,
                    'treeid'=>$questionData[$v['questionid']]['treeid'],
                    'subjectid'=>$questionData[$v['questionid']]['subjectid'],
                    'user_answer'=>strtoupper($uniqueid_array[$v['uniqueid']]),
                    'addtime'=>time()
                );

			}

            $insertData[] = $insertData_cell;
		}

		M('member_record_one')->insert($insertData);
        if(count($memberErrorInsertData)>0){
            M('member_error')->replace($memberErrorInsertData);
        }
		return $score_result;
	}
}