<?php

class FavoriteAction extends ApiCommon {
	
	public function favorite(){
		
		$questionid = intval(xInput::request('questionid'));
		$subjectid = intval(xInput::request('subjectid'));
		$memberid = intval(get_member_id());

        if($questionid<=0 || $subjectid<=0 || $memberid<=0){
            xOut::json(outError('参数错误'));
        }

		$favoriteMOdel = M('favorite');
		
		if($favoriteMOdel->where(['memberid'=>$memberid,'questionid'=>$questionid])->delete()){
			xOut::json(outSuccess('取消收藏'));
		}
		$typeid = M('question')->field('typeid')->where(array('questionid'=>$questionid))->getOne();
		$Data = array(
			'memberid'=>$memberid,
			'questionid'=>$questionid,
			'typeid'=>$typeid['typeid'],
			'subjectid'=>$subjectid,
			'addtime'=>time()
		);
		$result = $favoriteMOdel->insert($Data);
		if($result){
		    xOut::json(outSuccess('收藏成功'));
        }else{
            xOut::json(outError('收藏失败'));
        }
	}
	
	
	public function isFavorite(){
		
		$questionid = intval(xInput::request('questionid'));
		$memberid = intval(get_member_id());
        if($questionid<=0 || $memberid<=0){
            xOut::json(outError('参数错误'));
        }
		$count = M('favorite')->where(['memberid'=>$memberid,'questionid'=>$questionid])->count();
		if($count){
		    xOut::json(outStatus(1,'已经收藏'));
        }else{
            xOut::json(outStatus(2,'未收藏'));
        }
	}

	//查看已经收藏的试题
	public function lookFavoriteQuestion(){
        $memberid = intval(get_member_id());
        if( $memberid<=0){
            xOut::json(outError('参数错误'));
        }
		$m = M('favorite');
		$question_favorite = $m->where(['memberid'=>$memberid])->getAll();

		if(count($question_favorite)<=0){
            xOut::json(outStatus(-2,'你还没有收藏任何试题~'));
		}
		$question_favorite = ac_hase_question4($question_favorite);
        xOut::json(outSuccess($question_favorite));
	}


	public function getFavoriteQuestionDetail(){

		$questionid = intval(xInput::request('questionid'));
		//获取试题详细信息
        $questionid>0 || xOut::json(outError('参数错误'));
        $QuestionCommon = new QuestionCommon();
        $questionData = $QuestionCommon->get($questionid);

        if(count($questionData)<=0){
            xOut::json(outError('试题不存在'));
        }else{
            xOut::json(outSuccess($questionData));
        }
	}

	//自动评分
	public function saveRecord(){

		$answer = xInput::request('answer');
		if( !is_array($answer) || count($answer)<=0 ){
            xOut::json(outError('参数错误'));
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
		xOut::json(outSuccess($outerResult,'提交成功'));
	}

}