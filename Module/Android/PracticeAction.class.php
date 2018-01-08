<?php


class PracticeAction extends ApiCommon  {

    public function getSubjectList(){

        $subjectModel = M('subject');
        $subjectData = $subjectModel->order('listorder desc,subjectid desc')->getAll();

        $subjectData = subject_class($subjectData);

        xOut::json(outSuccess($subjectData));

    }

    public function getChapterList(){

        $subjectid = intval(xInput::request('subjectid'));
        $subjectid >0 || xOut::json(outError('参数错误'));

        $subjectModel = M('subject_tree')->where(array(
            'subjectid'=>$subjectid,
            'parentid'=>0
        ));
        $topTree = $subjectModel->getOne();
        if(!$topTree['treeid']){
            xOut::json(outError('没有对应的章节'));
        }
        $subjectData = $subjectModel->clearWhere()->where('parentid='.$topTree['treeid'])
            ->order('listorder asc,treeid asc')->getAll();

        $treeid_array = [];
        foreach ($subjectData as $k => $v ){
            $treeid_array[] = $v['treeid'];
        }
        if(empty($treeid_array)){
            xOut::json(outError('没有对应的章节'));
        }

        $subjectDataSub = $subjectModel->clearWhere()->where('parentid in('.implode(',',$treeid_array).')')
            ->order('listorder asc,treeid asc')->getAll();

        $subjectData_temp = [];
        foreach ($subjectData as $k => $v ){
            $subjectData_temp[$v['treeid']] = $v;
        }
        unset($subjectData);

        foreach ($subjectDataSub as $k => $v ){
            $subjectData_temp[$v['parentid']]['child'][] = $v;
        }
        xOut::json(outSuccess(array('count'=>$topTree['number'],'chapterList'=>$subjectData_temp)));
    }


    public function getQuestionList(){
        $treeid = intval(xInput::request('treeid'));
        $memberid = intval(xInput::request('memberid'));
        $treeid>0 || xOut::json(outError('参数错误'));
        $memberid>0 || xOut::json(outError('请先登录'));

        $practiceid = intval(xInput::request('practiceid'));

        $arrchildidData = M('subject_tree')->field('subjectid,chaptername,arrchildid')->where(['treeid'=>$treeid])->getOne();
        $subjectid = $arrchildidData['subjectid'];

        !empty($arrchildidData) || xOut::json(array('status'=>'error','message'=>'treeid错误','code'=>-1));
        $where = array('treeid'=>[$arrchildidData['arrchildid'],'in'],'ischeck'=>1);
        $questionModel = M('question')->field('typeid,questionid')->where($where);
        $count = $questionModel->count(false);
        $dataArrayCopy = $questionModel->order('typeid asc,questionid asc')->getAll();
        $dataArrayCopy = ac_hase_question4($dataArrayCopy);

        $practice_answer = [];
        //保存记录练习
        if($practiceid<=0){
            $Model =  M('member_practice');
            $practiceid = $Model->insert([
                'memberid'=>$memberid,
                'type'=>2,
                'subjectid'=>$subjectid,
                'treeid'=>$treeid,
                'totaltime'=>0,
                'starttime'=>time(),
                'addtime'=>time(),
                'result'=>'',
                'user_answer'=>'',
                'title'=>$arrchildidData['chaptername'],
            ]);
        }else{
            //继续上一次做题,获取做题记录
            $practice_answer = M('member_practice')->where(['practiceid'=>$practiceid])->getOne();
            $practice_answer['user_answer'] = json_decode($practice_answer['user_answer'],true);
            M('member_practice')->where(['practiceid'=>$practiceid])->update(['starttime'=>time()]);
        }
        xOut::json(outSuccess(array(
            'practiceid'=>$practiceid,
            'count'=>$count,
            'question'=>$dataArrayCopy,
            'practice_answer'=>$practice_answer
        )));
    }

    public function getQuestionDetail(){
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

    public function saveRecord(){

        $practiceid = intval(xInput::request('practiceid'));
        $practiceid >0 || xOut::json(outError('参数错误'));

        $memberid = intval(xInput::request('memberid'));
        $memberid>0 || xOut::json(outError('请先登录'));

        $answer = xInput::request('answer');

        if( !is_array($answer) || count($answer)<=0 ){
            xOut::json(outError('请求错误'));
        }

        $type = xInput::request('type');

        if( !is_array($type) || count($type)<=0 ){
            xOut::json(outError('请求错误2'));
        }

        //开始进行评分
        $answer_questionid = [];
        $typeid_array = [];
        //按试题的类型进行分类
        foreach ($answer as $k => $v ){
            $typeid_array[] = $v['typeid'];
            $answer_questionid[ $v['typeid'] ][] = $v;
        }
        $question_type = M('question_type')->field('typeid,typename,modelclass')
            ->where(array('typeid'=>array(implode(',',$typeid_array),'in')))
            ->getAll();

        //按照不同的题模型进行对应的模型处理评分方式
        $outerResult = $result = [];
        foreach ($question_type as $k => $v ){
            $typeClass = ucfirst($v['modelclass']);
            $typeClassObject = new $typeClass();
            // 1考试2练习3强化4模拟
            $result = $typeClassObject->score($answer_questionid[$v['typeid']],$memberid,2);

            $outerResult[$v['typeid']]['typename'] = $v['typename'];
            $outerResult[$v['typeid']]['truenumber'] = $result['result']['truenumber'];
            $outerResult[$v['typeid']]['falsenumber'] = $result['result']['falsenumber'];
            $outerResult[$v['typeid']]['totalnumber'] = $type['type'.$v['typeid']]['num'];
        }

        foreach ($type as $k => $v ){
            if(!isset($question_type_temp[$v['typeid']])){
                $outerResult[$v['typeid']]['typename'] = getQuestionTypeName( $v['typeid'] );
                $outerResult[$v['typeid']]['truenumber'] = 0;
                $outerResult[$v['typeid']]['falsenumber'] = 0;
                $outerResult[$v['typeid']]['totalnumber'] = $v['num'];
            }
        }

        //更新做题记录
        $practice = M('member_practice')->where(['practiceid'=>$practiceid])->field('starttime,totaltime')->getOne();
        M('member_practice')->where([
            'practiceid'=>$practiceid,
            'memberid'=>$memberid,
        ])->update([
            'totaltime'=>time() - $practice['starttime'] + $practice['totaltime'],
            'result'=>ac_addslashes(json_encode($result)),
            'user_answer'=>ac_addslashes(json_encode($answer)),
        ]);

        xOut::json(outSuccess( $outerResult ));
    }


    public function practiceList(){
        $memberid = intval(xInput::request('memberid'));
        $memberid >0 || xOut::json(outError('未登录'));

        $Model = M('member_practice')->where(array(
            'memberid'=>$memberid,
            'type'=>2
        ))->order('practiceid desc');

        $practice = $Model->getAll();
        $subjectid = [];
        foreach ($practice as $key => $item) {
            $practice[$key]['addtime'] = date('y年m月d日H:m',$practice[$key]['addtime']);
            if($item['donumber']>$item['totalnumber']){
                $item['donumber'] = $item['totalnumber'];
            }
            if(!in_array($item['subjectid'],$subjectid)){
                $subjectid[] = $item['subjectid'];
            }
        }
        $subject_temp = M('subject')->where(array('subjectid'=>[implode(',',$subjectid)],'in'))->field('subjectid,subjectname')->getAll();
        $subject = [];
        foreach ($subject_temp as $key => $item) {
            $subject[$item['subjectid']] = $item;
        }
        foreach ($practice as $key => $item) {
            $practice[$key]['subjectname'] = $subject[$item['subjectid']]['subjectname'];
        }
        xOut::json(outSuccess($practice));

    }


}





















