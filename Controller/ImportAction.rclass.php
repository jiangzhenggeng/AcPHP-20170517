<?php

function parmeS($string){
    return str_replace('"', '\"', $string);
}

set_time_limit(1800);

class ImportAction extends Action{

    public function type1()
    {

        $ac_question_model = M('question');
        $ac_question_option_radio_model = M('question_option_radio');
        $ac_question_answer_radio_model = M('question_answer_radio');

        $pager = intval(isset($_GET['page']) ? $_GET['page'] : 1);
        $number = 20;
        $count = M('question q', 'exam4test')->where(array('q.type' => 1))->count();
        if (($pager - 1) * $number > $count) exit('结束');
        //获取题目数据
        $ex_data = M('question q', 'exam4test')->where(array('q.type' => 1))
            ->field('q.question,q.questionid,q.catid,q.subjectid,q.options,q.difficulty,q.descriptions,a.answer,a.analysis,c.catname,s.subject')
            ->leftJoin('answer a', 'a.questionid=q.questionid')
            ->leftJoin('subject s', 's.subjectid=q.subjectid')
            ->leftJoin('category c', 'c.catid=q.catid')
            //->limit('0,1')

            ->limit((($pager - 1) * $number) . ',' . $number)
            ->getAll();

        //保存数据

        foreach ($ex_data as $k => $v) {
            if (M('question')->where(array('bq' => $v['questionid']))->count() > 0) continue;
            $questionid = $ac_question_model->insert(array(
                'bq' => $v['questionid'],
                'admin_id' => 48,
                'admin_name' => 'jiangzg',
                'subjectname' => $v['subject'],
                'typename' => '单项选择题',
                'chaptername' => parmeS($v['catname']),
                'subjectid' => $v['subjectid'],
                'typeid' => 1,
                'treeid' => $v['catid'],
                'difficulty' => $v['difficulty'],
                'question' => parmeS($v['question']),
                'description' => parmeS($v['descriptions']),
                'addtime' => time(),
                'updatetime' => time(),
                'ischeck' => 0,
                'isdelete' => 0
            ));

            $uniqueid = str_pad($questionid, 8, 'A', STR_PAD_LEFT);
            $uniqueid = str_pad($uniqueid, 16, 'A', STR_PAD_RIGHT);
            $ac_question_answer_radio_model->insert(array(
                'questionid' => $questionid,
                'uniqueid' => $uniqueid,
                'answer' => parmeS($v['answer']),
                'analysis' => parmeS($v['analysis'])
            ));

            $v['options'] = string2array($v['options']);
            $i = 0;
            foreach ($v['options'] as $k2 => $v2) {
                $ac_question_option_radio_model->insert(array(
                    'questionid' => $questionid,
                    'optiontext' => parmeS($v2),
                    'listorder' => $i++,
                    'optionname' => parmeS($k2)
                ));
            }
        }
        echo '<script>window.location="' . U('/Import/' . __FUNCTION__) . '?page=' . (++$pager) . '"</script>';

    }


    public function type2(){
        $pager = intval(isset($_GET['page']) ? $_GET['page'] : 1);
        $number = 20;
        $count = M('question q', 'exam4test')->where(array('q.type' => 2))->count();
        if (($pager - 1) * $number > $count) exit('结束');
        //获取题目数据


        $ex_data = M('question q', 'exam4test')->where(array('q.type' => 2))
            ->field('q.question,q.questionid,q.catid,q.subjectid,q.options,q.difficulty,q.descriptions,a.answer,a.analysis,c.catname,s.subject')
            ->leftJoin('answer a', 'a.questionid=q.questionid')
            ->leftJoin('subject s', 's.subjectid=q.subjectid')
            ->leftJoin('category c', 'c.catid=q.catid')
            //->limit('0,1')

            ->limit((($pager - 1) * $number) . ',' . $number)
            ->getAll();

        //保存数据

        foreach ($ex_data as $k => $v) {
            if (M('question')->where(array('bq' => $v['questionid']))->count() > 0) continue;
            $questionid = M('question')->insert(array(
                'bq' => $v['questionid'],
                'admin_id' => 48,
                'admin_name' => 'jiangzg',
                'subjectname' => $v['subject'],
                'typename' => '多项选择题',
                'chaptername' => parmeS($v['catname']),
                'subjectid' => $v['subjectid'],
                'typeid' => 2,
                'treeid' => $v['catid'],
                'difficulty' => $v['difficulty'],
                'question' => parmeS($v['question']),
                'description' => parmeS($v['descriptions']),
                'addtime' => time(),
                'updatetime' => time(),
                'ischeck' => 0,
                'isdelete' => 0
            ));
            $uniqueid = str_pad($questionid, 8, 'A', STR_PAD_LEFT);
            $uniqueid = str_pad($uniqueid, 16, 'A', STR_PAD_RIGHT);
            $v['answer'] = string2array($v['answer']);
            M('question_answer_checked')->insert(array(
                'questionid' => $questionid,
                'uniqueid' => $uniqueid,
                'answer' => parmeS(implode('', $v['answer'])),
                'analysis' => parmeS($v['analysis'])

            ));

            $v['options'] = string2array($v['options']);

            $i = 0;

            foreach ($v['options'] as $k2 => $v2) {
                M('question_option_checked')->insert(array(
                    'questionid' => $questionid,
                    'optiontext' => parmeS($v2),
                    'listorder' => $i++,
                    'optionname' => parmeS($k2)
                ));
            }
        }
        echo '<script>window.location="' . U('/Import/' . __FUNCTION__) . '?page=' . (++$pager) . '"</script>';

    }


    public function type3(){
        $pager = intval(isset($_GET['page']) ? $_GET['page'] : 1);
        $number = 20;
        $count = M('question q', 'exam4test')->where(array('q.type' => 3))->count();
        if (($pager - 1) * $number > $count) exit('结束');
        //获取题目数据
        $ex_data = M('question q', 'exam4test')->where(array('q.type' => 3))
            ->field('q.question,q.questionid,q.catid,q.subjectid,q.options,q.difficulty,q.descriptions,a.answer,a.analysis,c.catname,s.subject')
            ->leftJoin('answer a', 'a.questionid=q.questionid')
            ->leftJoin('subject s', 's.subjectid=q.subjectid')
            ->leftJoin('category c', 'c.catid=q.catid')
            //->limit('0,1')
            ->limit((($pager - 1) * $number) . ',' . $number)
            ->getAll();

        //保存数据

        foreach ($ex_data as $k => $v) {
            if (M('question')->where(array('bq' => $v['questionid']))->count() > 0) continue;
            $questionid = M('question')->insert(array(
                'bq' => $v['questionid'],
                'admin_id' => 48,
                'admin_name' => 'jiangzg',
                'subjectname' => $v['subject'],
                'typename' => '判断题',
                'chaptername' => parmeS($v['catname']),
                'subjectid' => $v['subjectid'],
                'typeid' => 3,
                'treeid' => $v['catid'],
                'difficulty' => $v['difficulty'],
                'question' => parmeS($v['question']),
                'description' => parmeS($v['descriptions']),
                'addtime' => time(),
                'updatetime' => time(),
                'ischeck' => 0,
                'isdelete' => 0
            ));
            $uniqueid = str_pad($questionid, 8, 'A', STR_PAD_LEFT);
            $uniqueid = str_pad($uniqueid, 16, 'A', STR_PAD_RIGHT);
            M('question_answer_decide')->insert(array(
                'questionid' => $questionid,
                'uniqueid' => $uniqueid,
                'answer' => $v['answer'] == 0 ? 2 : 1,
                'analysis' => parmeS($v['analysis'])
            ));
        }
        echo '<script>window.location="' . U('/Import/' . __FUNCTION__) . '?page=' . (++$pager) . '"</script>';

    }


    public function type4(){
        $pager = intval(isset($_GET['page']) ? $_GET['page'] : 1);
        $number = 8;
        $count = M('question q', 'exam4test')->where(array('q.type' => 4))->count();
        if (($pager - 1) * $number > $count) exit('结束');
        //获取题目数

        // a.answer,a.analysis
        $ex_data = M('question q', 'exam4test')->where(array('q.type' => 4))
            ->field('q.question,q.questionid,q.catid,q.subjectid,q.options,q.difficulty,q.descriptions,c.catname,s.subject')
            ->leftJoin('subject s', 's.subjectid=q.subjectid')
            ->leftJoin('category c', 'c.catid=q.catid')
            //->limit('0,1')
            ->limit((($pager - 1) * $number) . ',' . $number)
            ->getAll();

        $_ex_data = [];
        $type4questionid = '';
        foreach ($ex_data as $k => $v) {
            $type4questionid .= ',' . $v['questionid'];
            $v['sub'] = [];
            $_ex_data[$v['questionid']] = $v;
        }
        $type4questionid = substr($type4questionid, 1);
        if ($type4questionid == '') exit('结束');
        $caseoptionModel = new xModel('exam4test');
        //获取子题目与答案
        $ex_sub_answer = $caseoptionModel->table('caseoption cp')->where(array('cp.questionid' => array($type4questionid, 'in')))
            ->field('cp.*,ca.answer,ca.analysis')
            ->leftJoin('caseanswer ca', 'ca.caseoptionid=cp.caseoptionid')->getAll();
        //将答案与试题连接组合
        foreach ($ex_sub_answer as $k => $v) {
            $v['options'] = string2array($v['options']);
            $v['answer'] = string2array($v['answer']);
            $_ex_data[$v['questionid']]['sub'][] = $v;
        }
        //print_r($_ex_data);
        //exit;
        //保存数据
        foreach ($_ex_data as $k => $v) {
            if (M('question')->where(array('bq' => $v['questionid']))->count() > 0) continue;
            $questionid = M('question')->insert(array(
                'bq' => $v['questionid'],
                'admin_id' => 48,
                'admin_name' => 'jiangzg',
                'subjectname' => $v['subject'],
                'typename' => '不定项选择题',
                'chaptername' => parmeS($v['catname']),
                'subjectid' => $v['subjectid'],
                'typeid' => 4,
                'treeid' => $v['catid'],
                'difficulty' => $v['difficulty'],
                'question' => parmeS($v['question']),
                'description' => parmeS($v['descriptions']),
                'addtime' => time(),
                'updatetime' => time(),
                'ischeck' => 0,
                'isdelete' => 0
            ));
            $n = 1;
            foreach ($v['sub'] as $k_sub => $v_sub) {
                //插入子题目
                $optionid = M('question_option_multiple')->insert(array(
                    'questionid' => $questionid,
                    'optiontext' => parmeS($v_sub['casequestion']),
                    'optionname' => '试题' . $n,
                    'parentid' => 0,
                    'listorder' => $n
                ));
                $n++;


                //插入选项
                $m_s = 1;
                foreach ($v_sub['options'] as $k_op => $v_op) {
                    M('question_option_multiple')->insert(array(
                        'questionid' => $questionid,
                        'optiontext' => parmeS($v_op),
                        'optionname' => parmeS($k_op),
                        'parentid' => $optionid,
                        'listorder' => $m_s++
                    ));
                }
                $uniqueid = str_pad($questionid, 8, 'A', STR_PAD_LEFT);
                $uniqueid = str_pad($uniqueid . $optionid, 16, 'A', STR_PAD_RIGHT);
                //插入答案
                M('question_answer_multiple')->insert(array(
                    'questionid' => $questionid,
                    'uniqueid' => $uniqueid,
                    'answer' => parmeS(implode('', $v_sub['answer'])),
                    'analysis' => parmeS($v_sub['analysis'])
                ));
            }
        }
        echo '<script>window.location="' . U('/Import/' . __FUNCTION__) . '?page=' . (++$pager) . '"</script>';
    }


    public function type5(){
        $count = M('question q', 'exam4test')->where(array('q.type' => 5))->count();
        if (($pager - 1) * $number > $count) exit('结束');
        exit('程序没有实现');
    }


    public function type6(){
        $pager = intval(isset($_GET['page']) ? $_GET['page'] : 1);
        $number = 20;
        $count = M('question q', 'exam4test')->where(array('q.type' => 6))->count();
        if (($pager - 1) * $number > $count) exit('结束');
        //获取题目数据
        $ex_data = M('question q', 'exam4test')->where(array('q.type' => 6))
            ->field('q.question,q.questionid,q.catid,q.subjectid,q.options,q.difficulty,q.descriptions,a.answer,a.analysis,c.catname,s.subject')
            ->leftJoin('answer a', 'a.questionid=q.questionid')
            ->leftJoin('subject s', 's.subjectid=q.subjectid')
            ->leftJoin('category c', 'c.catid=q.catid')
            //->limit('0,1')
            ->limit((($pager - 1) * $number) . ',' . $number)
            ->getAll();

        foreach ($ex_data as $k => $v) {
            $ex_data[$k]['answer'] = string2array($v['answer']);
        }
        //print_r($ex_data);
        //exit;
        $ac_question_model = M('question');
        //保存数据
        foreach ($ex_data as $k => $v) {
            if (M('question')->where(array('bq' => $v['questionid']))->count() > 0) continue;
            $questionid = $ac_question_model->insert(array(
                'bq' => $v['questionid'],
                'admin_id' => 48,
                'admin_name' => 'jiangzg',
                'subjectname' => $v['subject'],
                'typename' => '计算题（会计分录）',
                'chaptername' => parmeS($v['catname']),
                'subjectid' => $v['subjectid'],
                'typeid' => 6,
                'treeid' => $v['catid'],
                'difficulty' => $v['difficulty'],
                'question' => parmeS($v['question']),
                'description' => parmeS($v['descriptions']),
                'addtime' => time(),
                'updatetime' => time(),
                'ischeck' => 0,
                'isdelete' => 0
            ));
            $analysis = '';
            $uniqueid = str_pad($questionid, 8, 'A', STR_PAD_LEFT);
            $i = 0;
            foreach ($v['answer'] as $k2 => $v2) {
                $answer = [];
                $f_answer = '';
                $a_index = 0;
                foreach ($v2['answer'] as $k3 => $v3) {
                    $answer[$a_index]['a'] = intval($v3['jie']);
                    $answer[$a_index]['b'] = intval($v3['mingxi']);
                    $answer[$a_index]['c'] = sc_retain_decimal($v3['jine']);
                    $a_index++;
                    $f_answer .= intval($v3['jie']) . '|';
                    $f_answer .= intval($v3['mingxi']) . '|';
                    //金额需要特殊处理
                    $f_answer .= sc_retain_decimal($v3['jine']);
                }

                M('question_answer_accounting')->insert(array(
                    'questionid' => $questionid,
                    'uniqueid' => str_pad($uniqueid . $i, 16, 'A', STR_PAD_RIGHT),
                    'answer' => array2string($answer),
                    'f_answer' => $f_answer,
                    'listorder' => $i++
                ));
                if ($analysis != '') $analysis .= '<br>';
                if (trim($v2['analysis']) != '') $analysis .= $i . '）' . trim($v2['analysis']);
            }
            M('question_answer_accounting_analysis')->insert(array(
                'questionid' => $questionid,
                'analysis' => parmeS($analysis)
            ));
        }
        echo '<script>window.location="' . U('/Import/' . __FUNCTION__) . '?page=' . (++$pager) . '"</script>';
    }


    public function type7(){
        $pager = intval(isset($_GET['page']) ? $_GET['page'] : 1);
        $number = 20;
        $count = M('question q', 'exam4test')->where(array('q.type' => 7))->count();
        if (($pager - 1) * $number > $count) exit('结束');
        //获取题目数据
        $ex_data = M('question q', 'exam4test')->where(array('q.type' => 7))
            ->field('q.question,q.questionid,q.catid,q.subjectid,q.options,q.difficulty,q.descriptions,a.answer,a.analysis,c.catname,s.subject')
            ->leftJoin('answer a', 'a.questionid=q.questionid')
            ->leftJoin('subject s', 's.subjectid=q.subjectid')
            ->leftJoin('category c', 'c.catid=q.catid')
            //->limit('0,1')
            ->limit((($pager - 1) * $number) . ',' . $number)
            ->getAll();
        foreach ($ex_data as $k => $v) {
            $ex_data[$k]['answer'] = string2array($v['answer']);
        }
        //print_r($ex_data);
        //exit;
        //保存数据
        foreach ($ex_data as $k => $v) {
            if (M('question')->where(array('bq' => $v['questionid']))->count() > 0) continue;
            $questionid = M('question')->insert(array(
                'bq' => $v['questionid'],
                'admin_id' => 48,
                'admin_name' => 'jiangzg',
                'subjectname' => $v['subject'],
                'typename' => '计算题（填空）',
                'chaptername' => parmeS($v['catname']),
                'subjectid' => $v['subjectid'],
                'typeid' => 7,
                'treeid' => $v['catid'],
                'difficulty' => $v['difficulty'],
                'question' => parmeS($v['question']),
                'description' => parmeS($v['descriptions']),
                'addtime' => time(),
                'updatetime' => time(),
                'ischeck' => 0,
                'isdelete' => 0
            ));

            $analysis = '';
            $i = 0;
            foreach ($v['answer'] as $k2 => $v2) {
                $uniqueid = str_pad($questionid, 8, 'A', STR_PAD_LEFT);
                M('question_answer_blank')->insert(array(
                    'questionid' => $questionid,
                    'uniqueid' => str_pad($uniqueid . $i, 16, 'A', STR_PAD_RIGHT),
                    'answer' => sc_retain_decimal($v2['answer']),
                    'listorder' => $i++
                ));
                if ($analysis != '') $analysis .= '<br>';
                if (trim($v2['analysis']) != '') $analysis .= $i . '）' . trim($v2['analysis']);
            }
            M('question_answer_blank_analysis')->insert(array(
                'questionid' => $questionid,
                'analysis' => parmeS($analysis . trim($v['analysis']))
            ));
        }
        echo '<script>window.location="' . U('/Import/' . __FUNCTION__) . '?page=' . (++$pager) . '"</script>';
    }

    //专业导入转换

    public function major(){
        $exModel = new xModel('exam4test');
        (new xModel())->query('TRUNCATE `ac_major`');
        $professional = $exModel->table('professional')->getAll();
        foreach ($professional as $k => $v) {
            M('major')->insert(array(
                'majorid' => $v['professionalid'],
                'majorname' => parmeS($v['professionalname']),
                'description' => parmeS($v['descriptions']),
                'listorder' => $v['listorder']
            ));
        }
    }

    //科目导入转换
    public function subject(){
        $exModel = new xModel('exam4test');
        (new xModel())->query('TRUNCATE `ac_subject`');

        $subject = $exModel->table('subject')->getAll();
        foreach ($subject as $k => $v) {
            M('subject')->insert(array(
                'subjectid' => $v['subjectid'],
                'subjectname' => parmeS($v['subject']),
                'majorid' => $v['professionalid'],
                'description' => parmeS($v['descriptions']),
                'listorder' => $v['listorder']
            ));
        }
    }


    //章节导入转换

    public function subject_tree(){

        $exModel = new xModel('exam4test');
        (new xModel())->query('TRUNCATE `ac_subject_tree`');
        $category = $exModel->table('category')->getAll();
        foreach ($category as $k => $v) {
            M('subject_tree')->insert(array(
                'treeid' => $v['catid'],
                'subjectid' => $v['subjectid'],
                'parentid' => $v['parentid'],
                'arrparentid' => $v['arrparentid'],
                'arrchildid' => $v['arrchildid'],
                'chaptername' => $v['catname'],
                'keywords' => $v['keywords'],
                'description' => $v['descriptions'],
                'number' => $v['number'],
                'subnumber' => 0,
                'listorder' => $v['listorder'],
                'hits' => $v['hits']
            ));
        }
    }

}













































































