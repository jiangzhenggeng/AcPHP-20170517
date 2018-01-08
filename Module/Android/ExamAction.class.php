<?php


class ExamAction extends ApiCommon  {

    public function getExamList(){
		
        $page = intval(xInput::request('page'));
		$page = $page?$page:1;
		$size = 20;
        $paper = M('paper')->order('listorder desc,starttime desc')
			->limit( ($page-1)*$size.','.$size)->getAll();
		
		$subject_ids = [];
		foreach($paper as $k => $v ){
			if(!in_array($v['subjectid'],$subject_ids)) $subject_ids[] = $v['subjectid'];
		}
		
		$subject_temp = M('subject')->field('subjectid,subjectname,shortname')->where(['subjectid'=>[$subject_ids,'in']])->getAll();
		$subject = [];
		foreach($subject_temp as $k => $v ){
			$subject[$v['subjectid']] = $v;
		}
		
		foreach($paper as $k => $v ){
			$shortname = $subject[$v['subjectid']]['shortname'];
			$subjectname = $subject[$v['subjectid']]['subjectname'];
			$paper[$k]['subjectname'] = $shortname?:$subjectname?:'';
		}
        xOut::json(outSuccess($paper));
    }
	
	public function ExamQuestionList(){
		$paperid = intval(xInput::request('paperid'));
        $memberid = intval(xInput::request('memberid'));
        $paperid>0 || xOut::json(outError('参数错误'));
        $memberid>0 || xOut::json(outError('请先登录'));
		
        $where = array('paperid'=>$paperid);
		M('paper')->where($where)->update([
			'online_number+'=>1
		]);

        $paper = M('paper')->where($where)->getOne();
        $subjectid = $paper['subjectid'];

		$paperDataModel = M('paper_data')->where($where);
		$count = $paperDataModel->count(false);
        $dataArrayCopy = $paperDataModel->order('typeid asc,questionid asc')->getAll();
        $dataArrayCopy = ac_hase_question4($dataArrayCopy);

		$paper_me = M('paper_me')->where([
			'memberid'=>$memberid,
			'paperid'=>$paperid,
		])->field('paper_meid')->getOne();

		//保存记录练习
		if( !isset($paper_me['paper_meid']) ){
			$paper_data = M('paper')->field('papername')->where(['paperid'=>$paperid])->getOne();
			$paper_meid = M('paper_me')->insert([
			    'totalscore'=>$paper['totalscore'],
                'passscore'=>$paper['passscore'],
				'memberid'=>$memberid,
				'paperid'=>$paperid,
				'subjectid'=>$subjectid,
				'title'=>$paper_data['papername'],
				'addtime'=>time(),
				'totalnumber'=>$count,
			]);

            M('paper_me_data')->insert([
                'paper_meid'=>$paper_meid,
                'paperid'=>$paperid,
                'memberid'=>$memberid,
                'user_answer'=>'',
            ]);

		}else{
            $paper_meid = $paper_me['paper_meid'];
			//继续上一次做题,获取做题记录
            $user_answer = M('paper_me_data')->where(['paper_meid'=>$paper_meid])->getOne();
            $user_answer['user_answer'] = json_decode($user_answer['user_answer'],true);
		}
        xOut::json(outSuccess(array(
			'count'=>$count,
			'question'=>$dataArrayCopy,
			'user_answer'=>$user_answer['user_answer']
		)));
	}

	public function ExamQuestionDetail(){
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

	//提交答案
	public function ExamSubmitUrl(){
        $paperid = intval(xInput::request('paperid'));
        $memberid = intval(xInput::request('memberid'));
        $memberid>0 || xOut::json(outError('请先登录'));
        $paperid >0 || xOut::json(outError('参数错误'));


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


        $rule_score = [];
        $paper = M('paper')->where(['paperid'=>$paperid])->getOne();
        $rule_score_temp = M('rule_score')->where(['ruleid'=>$paper['ruleid']])->getAll();
        foreach ($rule_score_temp as $k => $v ){
            $rule_score[$v['typeid']] = $v;
        }
        unset($rule_score_temp);

        //按照不同的题模型进行对应的模型处理评分方式
        $outerResult = $result = [];
        $truenumber = $falsenumber = $mescore = 0;
        $question_type_temp = [];
        foreach ($question_type as $k => $v ){
            $typeClass = ucfirst($v['modelclass']);
            $typeClassObject = new $typeClass();
            // 1考试2练习3强化4模拟
            $result = $typeClassObject->score($answer_questionid[$v['typeid']],$memberid,1);

            $outerResult[$v['typeid']]['typename'] = $v['typename'];
            $outerResult[$v['typeid']]['truenumber'] = $result['result']['truenumber'];
            $outerResult[$v['typeid']]['falsenumber'] = $result['result']['falsenumber'];
            $outerResult[$v['typeid']]['totalnumber'] = $type['type'.$v['typeid']]['num'];

            $truenumber += $result['result']['truenumber'];
            $falsenumber += $result['result']['falsenumber'];

            $mescore += $result['result']['truenumber'] * $rule_score[$v['typeid']]['score'];
            $question_type_temp[$v['typeid']] = $v;
        }

        foreach ($type as $k => $v ){
            if(!isset($question_type_temp[$v['typeid']])){
                $outerResult[$v['typeid']]['typename'] = getQuestionTypeName( $v['typeid'] );
                $outerResult[$v['typeid']]['truenumber'] = 0;
                $outerResult[$v['typeid']]['falsenumber'] = 0;
                $outerResult[$v['typeid']]['totalnumber'] = $v['num'];
            }
        }

        M('paper_me')->where([
            'paperid'=>$paperid,
            'memberid'=>$memberid
        ])->update([
            'mescore'=> $mescore,
            'metime'=> time(),
            'status'=> 2,
            'truenumber'=> $truenumber,
            'falsenumber'=> $falsenumber,
        ]);

        M('paper_me_data')->where([
            'paperid'=>$paperid,
            'memberid'=>$memberid
        ])->update([
            'user_answer'=> ac_addslashes( json_encode($answer) )
        ]);

        xOut::json(outSuccess( $outerResult ));
    }
}





















