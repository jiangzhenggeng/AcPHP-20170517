<?php 


class DoquestionAction extends MobileCommon{
	
	public function init(){
		
		$questionid = intval(xInput::request('questionid'));
		
		//获取试题详细信息
		if($questionid>0 && C_IS_AJAX){
			$QuestionCommon = new QuestionCommon();
			$questionData = $QuestionCommon->get($questionid);
			if(!$questionData){
				xOut::json(array('status'=>'error','message'=>'试题不存在','code'=>-1));
			}else{
				xOut::json(array('status'=>'success','code'=>-1,'data'=>$questionData));
			}
		}
		xOut::json(array('status'=>'error','message'=>'参数错误','code'=>-1));
    }

	public function getQuestionList(){

		$treeid = intval(xInput::request('treeid'));

		$arrchildidData = M('subject_tree')->field('arrchildid')->where(['treeid'=>$treeid])->getOne();

		$where = array('treeid'=>[$arrchildidData['arrchildid'],'in'],'ischeck'=>1);

		$dataArrayCopy = M('question')->field('typeid,questionid')->where($where)->order('typeid asc')->getAll();

		$dataArrayCopy = ac_hase_question4($dataArrayCopy);

		if( C_IS_AJAX ){
			xOut::json(array('status'=>'success','code'=>-1,'data'=>$dataArrayCopy));
		}

		App::setConfig('exam','examModel',false);

		$this->assign('question',$dataArrayCopy);
		$this->display('doquestion/index.html');
	}
	
	public function saverecord(){

		$answer = xInput::request('answer');
		if( !C_IS_AJAX || !is_array($answer) || count($answer)<=0 ){
			xOut::json(array('status'=>'error','message'=>'请求错误','code'=>-1));
		}
		$subjectid = intval(xInput::request('subjectid'));
		$subjectid>0 OR xOut::json(array('status'=>'error','message'=>'参数错误','code'=>-1));


		//开始进行评分
		$answer_questionid = [];
		$typeid_array = [];
		//按试题的类型进行分类
		foreach ($answer as $k => $v ){
			$key = explode('-',$k);
			$typeid_array[] = $key[1];
			$answer_questionid[$key[1]][] = $v;
		}
		unset($answer);
		$question_type = M('question_type')->field('typeid,typename,modelclass')
			->where(array('typeid'=>array(implode(',',$typeid_array),'in')))
			->getAll();

		//按照不同的题模型进行对应的模型处理评分方式
		$outerResult = [];
		foreach ($question_type as $k => $v ){
			$typeClass = ucfirst($v['modelclass']);
			$typeClassObject = new $typeClass();
			$result = $typeClassObject->score($answer_questionid[$v['typeid']]);
			$outerResult[$v['typeid']]['typename'] = $v['typename'];
			$outerResult[$v['typeid']]['truenumber'] = $result['result']['truenumber'];
			$outerResult[$v['typeid']]['falsenumber'] = $result['result']['falsenumber'];
		}
		xOut::json(array('status'=>'success','message'=>'请求错误','code'=>1,'data'=>$outerResult));
    }
} 

























