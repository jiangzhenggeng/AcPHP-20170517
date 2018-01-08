<?php


class CollectionAction extends ApiCommon   {
    
    //收藏试题
    public function collection(){
        
        $questionid = intval(xInput::request('questionid'));
        $memberid = intval(xInput::request('memberid'));

        $questionid>0 || xOut::json(outError('参数错误'));
        $memberid>0 || xOut::json(outError('请先登录'));
        
        $where = ['memberid'=>$memberid,'questionid'=>$questionid];
        $favoriteModel = M('favorite')->where($where);
        $count = $favoriteModel->count();
        if($count<=0){
            $questionData = M('question')->where(['questionid'=>$questionid])->getOne();
            $result = $favoriteModel->insert([
                'memberid'=>$memberid,
                'questionid'=>$questionid,
                'typeid'=>$questionData['typeid'],
                'subjectid'=>$questionData['subjectid'],
                'addtime'=>time(),
            ]);
            if($result){
                xOut::json(outSuccess(['collection'=>1],'收藏成功'));
            }else{
                xOut::json(outError('收藏失败'));
            }
        }else{
            $result = M('favorite')->where($where)->delete();
            if($result){
                xOut::json(outSuccess(['collection'=>0],'取消成功'));
            }else{
                xOut::json(outError('取消失败'));
            }
        }
    }

    public function collectionQuestionList(){
        $memberid = intval(xInput::request('memberid'));
        $memberid>0 || xOut::json(outError('请先登录'));
        $where = array('memberid'=>$memberid);
        $favoriteModel = M('favorite')->where($where);
        $count = $favoriteModel->count(false);
        $dataArrayCopy = $favoriteModel->order('favoriteid desc')->getAll();
        $dataArrayCopy = ac_hase_question4($dataArrayCopy);
        xOut::json(outSuccess(array(
            'count'=>$count,
            'question'=>$dataArrayCopy
        )));
    }

    public function collectionQuestionDetail(){
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



















