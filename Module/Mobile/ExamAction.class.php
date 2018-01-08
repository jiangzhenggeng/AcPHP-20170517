<?php 


class ExamAction extends MobileCommon{


    public function __construct(){

        if( get_parent_class()!='' && method_exists ( get_parent_class(), '__construct' ) ){
            parent::__construct();
        }

        $majorid = $this->majorid;
        $member_major = $this->getMemberMajor($majorid);
        $buy_url = U('pay/buyMajor',array('majorid'=>$majorid));
        $this->assign('member_major',$member_major);

        if( count($member_major)>0 ){
            if($member_major['buyed']!=1){
                if( $member_major['test']==-1 ){
                    $this->showMessage('试用已结束，请购买后继续做题',$buy_url);
                }elseif ( $member_major['test']==0 ){
                    $this->showMessage('请先进行购买',$buy_url);
                }
            }
        }else{
            $this->showMessage('请先进行购买',$buy_url);
        }
        $this->assign('member_major',$member_major);

    }

    //考试提示
    public function examTips(){

        $synid = intval(xInput::request('synid'));
        $exam_synthesis_paper = M('exam_synthesis_paper')
            ->where(['synid'=>$synid])->getOne();
        $this->assign('synthesis_paper',$exam_synthesis_paper);

        $exam_synthesis_subject = M('exam_synthesis_subject')
            ->where(['synid'=>$synid])->getAll();

        foreach ($exam_synthesis_subject as $k => $v ){
            $subject = M('subject')->where(array('subjectid'=>$v['subjectid']))->getOne();
            $exam_synthesis_subject[$k]['subjectname'] = $subject['subjectname'];
        }
        $majorid = intval(xInput::request('majorid'));
        $this->assign('majorid',$majorid);

        $endtime = $exam_synthesis_paper['starttime'] + ($exam_synthesis_paper['lasttime'] + $exam_synthesis_paper['totaltime']) * 60;
        //考试已经结束啦
        if(C_TIME > $endtime ){
            //自动进入查看考试试卷
            header('location:'.U('view',['synid'=>$synid,'majorid'=>$majorid]));
        }
        $this->assign('synthesis_subject',$exam_synthesis_subject);
        $this->display('exam/exam_tips_list.html');
    }


	//获取试题列表
	public function getQuestionList(){
		$synid = intval(xInput::request('synid'));
		$synid>0 OR $this->showMessage('参数错误1');

        $majorid = intval(xInput::request('majorid'));
        $this->assign('majorid',$majorid);

		//判断考试的时间
		$exam_synthesis_paper = M('exam_synthesis_paper')
			->where(['synid'=>$synid])->getOne();

        //没有开始考试
		if($exam_synthesis_paper['starttime']>C_TIME){
			$this->showMessage(date('m-d H:i:s',$exam_synthesis_paper['starttime']).'开始考试');
		}

		//获取考试科目信息
		$exam_synthesis_subject = M('exam_synthesis_subject')
			->where(['synid'=>$synid])->order('listorder asc')->getAll();

        //没有科目
		if(count($exam_synthesis_subject)<=0){
			$this->showMessage('系统错误[没有科目]');
		}

        $end_time = 0;
        foreach ($exam_synthesis_subject as $item) {
            $end_time += $item['totaltime'] * 60;
		}

		//检查是否是第一次进入考试
        $exam_me = M('exam_me')->where(['memberid'=>$this->memberid,'synid'=>$synid])->getOne();

        $end_time = $exam_synthesis_paper['starttime'] + $end_time + $exam_synthesis_paper['lasttime'] * 60;
        //考试已经结束啦
		if(C_TIME > $end_time || $exam_me['status']==2){
            //自动进入查看考试试卷
            header('location:'.U('view',['synid'=>$synid,'majorid'=>$majorid]));
            return;
        }

		//第一次进入考试
		if(count($exam_me)<=0 ){
			$totaltime = 0;
			foreach ($exam_synthesis_subject as $k => $v ){
				$totaltime += $v['totaltime'];
			}

			$totalscore = $passscore = 0;
			foreach ($exam_synthesis_subject as $k => $v ){
				$totalscore += $v['totalscore'];
				$passscore += $v['passscore'];
			}
			//记录考试信息
			$meid = M('exam_me')->insert(array(
				'memberid'=>$this->memberid,
				'synid'=>$synid,
				'type'=>1,
				'metitle'=>$exam_synthesis_paper['papername'],
				'mescore'=>0,
				'totalscore'=>$totalscore,
				'passscore'=>$passscore,
				'addtime'=>time(),
				'metime'=>0,
				'status'=>1,//正在考试
				'truenumber'=>0,
				'falsenumber'=>0,
				'totalnumber'=>0
			));

			//记录考试科目
			$n = 0;
			foreach ($exam_synthesis_subject as $k => $v ){
				//表示未开始考试
				$status = -1;
				if($n++==0){
					$status = 1;//表示已经开始考试
					$subjectid = $v['subjectid'];
				}

				M('exam_me_subject')->insert(array(
					'meid'=>$meid,
					'synid'=>$synid,
					'subjectid'=>$v['subjectid'],
					'mescore'=>0,
					'totalscore'=>$v['totalscore'],
					'passscore'=>$v['passscore'],
					'totaltime'=>$v['totaltime'],
					'metime'=>0,
					'status'=>$status,
					'truenumber'=>0,
					'falsenumber'=>0,
					'totalnumber'=>0,
					'listorder'=>$v['listorder'],
				));
			}
            M('exam_me_subject')->where(array(
                'subjectid'=>$subjectid,
                'meid'=>$meid,
                'starttime'=>-1
            ))->update([
                'starttime'=>C_TIME,
                'status'=>1
            ]);
		}else{
			//不是第一考进入考试或者进入下一个科目考试
			$meid = $exam_me['meid'];

			$exam_me_subjectMOdel = M('exam_me_subject');
			$exam_me_subject =$exam_me_subjectMOdel->field('subjectid,totaltime,status')
				->where(['meid'=>$meid])
				->order('listorder asc')->getAll();
			//判断考试的科目是否过期
			foreach ($exam_me_subject as $k => $v ){
				//该科目考试已经结束
				if( in_array($v['status'],[-1,1])){
                    $subjectid = $v['subjectid'];
                    M('exam_me_subject')->where(array(
                        'subjectid'=>$subjectid,
                        'meid'=>$meid,
                        'starttime'=>-1
                    ))->update([
                        'starttime'=>C_TIME,
                        'status'=>1
                    ]);
                    break;
				}
			}
		}

        //进入下一个科目考试
        if( isset($subjectid) && $subjectid>0 ){
            $exam_me_subjectMOdel = M('exam_me_subject');
            //更新当前考试科目状态
            $exam_me_subjectMOdel->where(['meid'=>$meid, 'subjectid'=>$subjectid])
                ->update(array('status'=>1));

            $mesu_data = $exam_me_subjectMOdel->field('mesuid')->where(['meid'=>$meid, 'subjectid'=>$subjectid])->getOne();
            $mesuid = $mesu_data['mesuid'];
            $this->assign('mesuid',$mesuid);

            //获取考试数据
            $dataArrayCopy = M('exam_synthesis_paper_data')
                ->where(['synid'=>$synid,'subjectid'=>$subjectid])
                ->order('typeid asc')->getAll();

            $subject = M('subject')->where(['subjectid'=>$subjectid])->getOne();
            $this->assign('subject',$subject);
            $dataArrayCopy = ac_hase_question4($dataArrayCopy);
            //获取已经做过的试题结果
            $exam_me_data = M('exam_me_data')->where(['meid'=>$meid])->getOne();
            $exam_me_data['answer_all'] = string2array($exam_me_data['answer_all']);

            $this->assign('user_answer',$exam_me_data['answer_all']);

            $this->assign('question',$dataArrayCopy);

            //设置考试模式
            App::setConfig('exam','examModel',true);
            //设置获取试题地址
            App::setConfig('exam','getQuestionUrl',U('getQuestionDetail',['meid'=>$meid]));

            $this->assign('meid',$meid);
            $this->assign('subjectid',$subjectid);
            $this->assign('synid',$synid);

            //获取考试科目信息
            $exam_me_subject = M('exam_me_subject')
                ->where(['meid'=>$meid,'synid'=>$synid,'subjectid'=>$subjectid])->getOne();
            $this->assign('exam_me_subject',$exam_me_subject);

            $this->display('exam/exam_question.html');
        }else{
            //本次考试的状态
            M('exam_me')->where(array(
                'meid'=>$meid
            ))->update([
                'metime'=>ceil(C_TIME - $exam_me['addtime']/60),
                'status'=>2
            ]);
            //转到查看考试成绩页面
            header('location:'.U('view',['synid'=>$synid,'majorid'=>$majorid]));
            return;
        }

	}


    public function getQuestionDetail(){

        $questionid = intval(xInput::request('questionid'));
        //获取试题详细信息
        if($questionid>0 && C_IS_AJAX){

            $meid = intval(xInput::request('meid'));
            $exam_me_data = M('exam_me_data')->where(['meid'=>$meid])->getOne();
            $answer_all = string2array($exam_me_data['answer_all']);
            $user_answer = isset($answer_all[$questionid])?$answer_all[$questionid]:null;
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

        $mesuid       = intval(xInput::request('mesuid'));
        $answer     = xInput::request('answer');

		if( !C_IS_AJAX || $mesuid<=0 ){
			xOut::json(array('status'=>'error','message'=>'参数错误','code'=>-1));
		}

		if( !C_IS_AJAX || !is_array($answer) || count($answer)<=0 ){
			xOut::json(array('status'=>'success','message'=>'请填先做题','code'=>-1));
		}

        $exam_me_subject = M('exam_me_subject')->where(array('mesuid'=>$mesuid))->getOne();

        $meid = $exam_me_subject['meid'];
        $subjectid = $exam_me_subject['subjectid'];
        $synid = $exam_me_subject['synid'];

		$exam_me_data = M('exam_me_data')->where(array('meid'=>$meid))->getOne();
		$exam_me_data_user_answer = isset($exam_me_data['answer_all'])
					?string2array($exam_me_data['answer_all']):[];
		//开始进行评分
		$answer_questionid = [];
		$typeid_array = [];
		//按试题的类型进行分类
		foreach ($answer as $k => $v ){
			$key = explode('-',$k);
			$typeid_array[$key[0]] = $key[1];
			$answer_questionid[$key[1]][] = $v;
			if(!isset($exam_me_data_user_answer[$key[0]])){
				$exam_me_data_user_answer[$key[0]] = $v;
			}
		}
		unset($answer);
		$insertData = array(
			'subjectid'=>$subjectid,
			'answer_all'=>array2string($exam_me_data_user_answer)
		);
		//保存答案
		if(count($exam_me_data)){
			M('exam_me_data')->where(array('meid'=>$meid))->update($insertData);
		}else{
			$insertData['meid'] = $meid;
			M('exam_me_data')->insert($insertData);
		}
		unset($insertData);

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
		//保存错题
		$QuestionCommon = new QuestionCommon();
		$QuestionCommon->insertErrorQuert($result,$exam_me_data_user_answer,$typeid_array);

		M('exam_me_subject')->where(array('mesuid'=>$mesuid))->update(array('status'=>2));


        $exam_me_subject_num = M('exam_me_subject')->where(array('meid'=>$meid,'status'=>-1))->count();

		xOut::json(array(
			'status'=>'success',
			'message'=>$exam_me_subject_num?'下一个科目':'查看考试成绩',
			'code'=>1,
			'data'=>$outerResult
		));
	}

	//获取试题列表
	public function view(){
		$synid = intval(xInput::request('synid'));
        $majorid = intval(xInput::request('majorid'));

		if( $synid<=0 || $majorid<=0){
		    $this->showMessage('参数错误');
        }

		$subjectid = intval(xInput::request('subjectid'));

		if($subjectid<=0){
			$exam_synthesis_subject =M('exam_synthesis_subject')->field('subjectid')
				->where(['synid'=>$synid])
				->order('listorder asc')->getOne();
			$subjectid = $exam_synthesis_subject['subjectid'];
		}

		//判断考试的时间
		$exam_synthesis_paper = M('exam_synthesis_paper')
			->where(['synid'=>$synid])->getOne();

		if($exam_synthesis_paper['starttime']>C_TIME){
			$this->showMessage(date('m-d H:i:s',$exam_synthesis_paper['starttime']).'开始考试');
		}

		//获取考试科目信息
		$exam_synthesis_subject = M('exam_synthesis_subject')
			->where(['synid'=>$synid])->order('listorder asc')->getAll();

		$dataArrayCopy = M('exam_synthesis_paper_data')
			->where(['synid'=>$synid,'subjectid'=>$subjectid])
			->order('typeid asc')->getAll();

		$subject = M('subject')->where(['subjectid'=>$subjectid])->getOne();
		$this->assign('subject',$subject);
		$dataArrayCopy = ac_hase_question4($dataArrayCopy);

		//获取已经做过的试题结果
		$meid = intval(xInput::request('meid'));
		if($meid>0){
			$exam_me_data = M('exam_me_data')->where(['meid'=>$meid])->getOne();
			$exam_me_data['answer_all'] = string2array($exam_me_data['answer_all']);
			$this->assign('exam_me_data',$exam_me_data);
			$this->assign('meid',$meid);
		}
		//设置非考试模式
		App::setConfig('exam','examModel',false);
		//设置获取试题地址
		App::setConfig('exam','getQuestionUrl',U('getQuestionDetail'));
		App::setConfig('exam','disabledAnser',true);
		$this->assign('question',$dataArrayCopy);
		foreach ($exam_synthesis_subject as $k => $v ){
			$subjectTemp = M('subject')->field('subjectname')->where(['subjectid'=>$v['subjectid']])->getOne();
			$exam_synthesis_subject[$k]['subjectname'] = $subjectTemp['subjectname'];
			$exam_synthesis_subject[$k]['url'] = U('view',['synid'=>$synid,'subjectid'=>$v['subjectid'],'majorid'=>$majorid]);
		}

		$this->assign('exam_synthesis_subject',$exam_synthesis_subject);
		$this->assign('synid',$synid);
        $this->assign('majorid',$majorid);

		$this->display('exam/exam_question_view.html');
	}

} 

























