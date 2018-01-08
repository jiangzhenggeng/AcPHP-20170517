<?php


class QuestionAction extends ApiCommon  {

    public function getQuestionDetail(){
        $questionid = intval(xInput::request('questionid'));
        $questionid>0 || xOut::json(outError('参数错误'));
        $QuestionCommon = new QuestionCommon();
        $questionData = $QuestionCommon->get($questionid);
        if(!$questionData){
            xOut::json(outError('试题不存在'));
        }else{
            xOut::json(outSuccess($questionData));
        }
    }

    public function getQuestionList(){
        $treeid = intval(xInput::request('treeid'));
        $treeid>0 || xOut::json(outError('参数错误'));

        $arrchildidData = M('subject_tree')->field('arrchildid')->where(['treeid'=>$treeid])->getOne();
        !empty($arrchildidData) || xOut::json(array('status'=>'error','message'=>'treeid错误','code'=>-1));
        $where = array('treeid'=>[$arrchildidData['arrchildid'],'in'],'ischeck'=>1);
        $dataArrayCopy = M('question')->field('typeid,questionid')->where($where)->order('typeid asc')->getAll();
        $dataArrayCopy = ac_hase_question4($dataArrayCopy);
        xOut::json(outSuccess($dataArrayCopy));
    }

    public function saveRecord(){
        $answer = xInput::request('answer');
        $subjectid = intval(xInput::request('subjectid'));
        if( !is_array($answer) || count($answer)<=0 || $subjectid<=0){
            xOut::json(outError('请求错误'));
        }
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
        xOut::json(outSuccess($outerResult));
    }
}



















