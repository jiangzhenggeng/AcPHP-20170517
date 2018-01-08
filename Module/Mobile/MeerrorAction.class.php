<?php 


class MeerrorAction extends MobileCommon{

	//获取试题列表
	public function view(){

		$question_error_temp = M('question_error')
			->where(['memberid'=>$this->memberid])->getAll();
		if(count($question_error_temp)<=0){
			$this->showMessage('你还没有错题');
		}
		$questionid = $question_error = [];
		foreach ($question_error_temp as $k => $v ){
			$questionid[] = $v['questionid'];
			$question_error[$v['questionid']] = $v;
		}

		$questionTypeId = M('question')->field('questionid,typeid')->where(['questionid'=>[implode(',',$questionid),'in']])->getAll();

		unset($questionid);
		foreach ($questionTypeId as $k => $v ){
			$question_error[$v['questionid']]['typeid'] = $v['typeid'];
		}
		$dataArrayCopy = ac_hase_question4($question_error);

		foreach ($dataArrayCopy as $k => $v ){
			unset($dataArrayCopy[$k]['user_answer']);
			$user_answer[$v['questionid']] = string2array($v['user_answer']);
		}
		$this->assign('user_answer',$user_answer);
		//设置非考试模式
		App::setConfig('exam','examModel',false);
		//设置获取试题地址
		App::setConfig('exam','getQuestionUrl',U('init'));
		App::setConfig('exam','disabledAnser',true);
		//允许重新做题
		App::setConfig('exam','allowDo',true);
		App::setConfig('exam','disabledAnser',false);
		$this->assign('question',$dataArrayCopy);
		$this->assign('sheetNoSubmit',true);

		$this->display('examline/exam_question_view.html');
	}

	public function init(){

		$questionid = intval(xInput::request('questionid'));
		//获取试题详细信息
		if($questionid>0 || C_IS_AJAX){
			$question_error = M('question_error')->where(
				['questionid'=>$questionid,'memberid'=>$this->memberid])->getOne();

			$user_answer = string2array($question_error['user_answer']);
			$QuestionCommon = new QuestionCommon();
			$questionData = $QuestionCommon->get($questionid,$user_answer);

			if(!$questionData){
				xOut::json(array('status'=>'error','message'=>'试题不存在','code'=>-1));
			}else{
				xOut::json(array('status'=>'success','code'=>-1,'data'=>$questionData));
			}
		}
		xOut::json(array('status'=>'error','message'=>'参数错误','code'=>-1));
    }

	//自动评分
	public function saverecord(){

		$answer = xInput::request('answer');
		if( !C_IS_AJAX || !is_array($answer) || count($answer)<=0 ){
			xOut::json(array('status'=>'error','message'=>'参数错误','code'=>-1));
		}

		//开始进行评分
		$typeid_array = $answer_questionid = $exam_me_data_user_answer = [];
		//按试题的类型进行分类
		foreach ($answer as $k => $v ){
			$key = explode('-',$k);
			$typeid_array[] = $key[1];
			$answer_questionid[$key[1]][] = $v;
			$exam_me_data_user_answer[$key[0]] = $v;
		}
		unset($answer);

		$question_type = M('question_type')->field('typeid,typename,modelclass')
			->where(array('typeid'=>array(implode(',',$typeid_array),'in')))
			->getAll();

		//按照不同的题模型进行对应的模型处理评分方式
		$result = $outerResult = [];
		foreach ($question_type as $k => $v ){
			$typeClass = ucfirst($v['modelclass']);
			$typeClassObject = new $typeClass();
			$result[$v['typeid']] = $typeClassObject->score($answer_questionid[$v['typeid']]);
			$outerResult[$v['typeid']]['typename'] = $v['typename'];
			$outerResult[$v['typeid']]['truenumber'] = $result[$v['typeid']]['result']['truenumber'];
			$outerResult[$v['typeid']]['falsenumber'] = $result[$v['typeid']]['result']['falsenumber'];
		}
		//移除错题
		$QuestionCommon = new QuestionCommon();
		$QuestionCommon->removeErrorQuert($result,$exam_me_data_user_answer);

		xOut::json(array(
			'status'=>'success',
			'message'=>'提交成功',
			'code'=>1,
			'url'=>U('view'),
			'data'=>$outerResult
		));
	}

} 

























