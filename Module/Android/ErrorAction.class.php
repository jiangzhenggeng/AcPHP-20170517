<?php


class ErrorAction extends ApiCommon   {

    public function errorQuestionList(){
        $memberid = intval(xInput::request('memberid'));
        $memberid>0 || xOut::json(outError('请先登录'));
        $where = array('memberid'=>$memberid);
        $Model = M('member_error')->where($where);
        $count = $Model->count(false);
        $dataArrayCopy = $Model->order('id desc')->getAll();
        $dataArrayCopy = ac_hase_question4($dataArrayCopy);
        xOut::json(outSuccess(array(
            'count'=>$count,
            'question'=>$dataArrayCopy
        )));
    }

    public function errorQuestionDetail(){
        $questionid = intval(xInput::request('questionid'));
        $questionid>0 || xOut::json(outError('参数错误'));
        $QuestionCommon = new QuestionCommon();
        $questionData = $QuestionCommon->get($questionid);

        //查询收藏情况
        $questionData['collection'] = 0;
        $memberid = intval(xInput::request('memberid'));
        if($memberid>0){
            $where = ['memberid'=>$memberid,'questionid'=>$questionid];
            $questionData['collection'] = M('favorite')->where($where)->count();
        }

        if(!$questionData){
            xOut::json(outError('试题不存在'));
        }else{
            xOut::json(outSuccess($questionData));
        }
    }

}



















