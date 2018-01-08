<?php
// +--------------------------------------------------------------------------------------
// + AcPHP
// +--------------------------------------------------------------------------------------
// + 版权所有 2015年11月8日 贵州天岛在线科技有限公司，并保留所有权利。
// + 网站地址: http://www.acphp.com
// +--------------------------------------------------------------------------------------
// + 这不是一个自由软件！您只能在遵守授权协议前提下对程序代码进行修改和使用；不允许对程序代码以任何形式任何目的的再发布。
// + 授权协议：http://www.acphp.com/license.html
// +--------------------------------------------------------------------------------------
// + Author: AcPHP  http://www.jiangzg.com/ <2992265870@qq.com/jiangzg>
// + Release Date: 2015年11月8日 上午1:09:25
// +--------------------------------------------------------------------------------------

// +--------------------------------------------------------------------------------------
// + 管理员组管理模块
// +--------------------------------------------------------------------------------------
class PaperruleAction extends AdminCommon{

    public function __construct(){
        if( get_parent_class()!='' && method_exists ( get_parent_class(), '__construct' ) ){
            parent::__construct();
        }
        $this->tree_obj = xTree::getInstance ();
        $this->tree_obj->tree_id = 'treeid';
    }

    //组卷规则列表
    public function lists(){

        $search = xInput::request('search');

        $subjectid = intval($search['subjectid']);
        $keyword = trim($search['keyword']);

        $m = M('rule r');

        if($subjectid>0){
            $m->where( array('r.subjectid'=>$subjectid) );
        }
        if($keyword!=''){
            $m->where( array('r.rulename'=>array($keyword,'like')) );
        }

        $count = $m->count(false);
        $page = $this->page($count);
        $m->limit($page->getLimit());

        $rule = $m->field('r.*,a.admin_name,s.subjectname')->where(array('r.delete'=>0))
            ->leftJoin('admin a','a.admin_id=r.admin_id')->leftJoin('subject s','s.subjectid=r.subjectid')
            ->order('r.listorder desc,r.ruleid desc')->getAll();

        $subject = M('subject')->getAll();

        $this->assign('rule', $rule);
        $this->assign('page', $page->show());
        $this->assign('search', $search);
        $this->assign('subject', $subject);

        $this->display('rule/rule_list.html');
    }

    //创建组卷规则
    public function add(){
        if ( xInput::request('query')!='insert' ){
            $subject = M('subject')->getAll();
            $this->assign('subject', $subject );

            $subjectid = intval(xInput::request('subjectid'));
            if($subjectid<=0){
                $subjectid = $subject[0]['subjectid'];
            }
            $this->assign('subjectid', $subjectid );

            $question_type = M('question_type qt')->field('qt.*')
                ->leftJoin('subject_question_type sqt','sqt.typeid=qt.typeid')
                ->where('sqt.subjectid='.$subjectid)->getAll();
            if(count($question_type)<=0){
                $this->showMessage('请先对该科目进行题型配置');
                exit;
            }
            $this->assign('question_type', $question_type );
            $GLOBALS['question_type'] = $question_type;

            $subject_tree = M('subject_tree')
                ->field('treeid,parentid,chaptername,arrparentid,arrchildid,listorder')
                ->where(array('subjectid'=>$subjectid))
                ->order('listorder asc,treeid desc')->getAll();

            $this->tree_obj->fix_array = array('','　','│','├','└','<span class="ac-query-click" flag="none">','</span>');

            $create_obj = $this->tree_obj->createTree ( $subject_tree );
            $subject_tree = $this->tree_obj->createTreeMenu ($create_obj,'TreeCommon::createTreeChapterTableRule');

            $this->assign('subject_tree', $subject_tree );

            $this->display('rule/rule_add.html');
        }else{
            $rule = xInput::request('rule');

            $rule_info = array(
                'admin_id' => xSession::get('admin.admin_id'),
                'subjectid'=> $rule['info']['subjectid'],
                'rulename'=> $rule['info']['rulename'],
                'description'=> $rule['info']['description'],
                '`delete`'=> 0,
                'listorder'=> 0,
                'addtime'=> time()
            );
            $ruleid = M('rule')->insert($rule_info);

            $rulescoreModel = M('rule_score');
            foreach($rule['rule_score'] as $k => $v){
                $rule_score = array(
                    'ruleid' => $ruleid,
                    'typeid'=> $k,
                    'score'=> $v
                );
                $rulescoreModel->insert($rule_score);
            }

            $rulecfgModel = M('rule_cfg');
            foreach($rule['cfg'] as $k2 => $v2){
                foreach($v2 as $k => $v){
                    $v['number'] = intval($v['number']);
                    if($v['number']<=0) continue;
                    $rule_cfg = array(
                        'ruleid' => $ruleid,
                        'typeid'=> intval($v['typeid']),
                        'model'=> intval($v['model']),
                        'number'=> intval($v['number']),
                        'treeid'=> intval($k2)
                    );
                    $rulecfgModel->insert($rule_cfg);
                }
            }
            $this->showMessage('操作成功');
        }
    }

    //创建组卷规则
    public function edit(){
        $ruleid = intval(xInput::request('ruleid'));
        $ruleid >0 OR $this->showMessage('非法操作');

        if ( xInput::request('query')!='insert' ){
            $subject = M('subject')->getAll();
            $this->assign('subject', $subject );
            $rule = M('rule')->where(array('ruleid'=>$ruleid))->getOne();
            $this->assign('rule', $rule );

            $rule_score = M('rule_score')->where(array('ruleid'=>$ruleid))->getAll();
            foreach($rule_score as $k=>$v) $rule_score[$v['typeid']] = $v['score'];

            $this->assign('rule_score', $rule_score );

            $subjectid = $rule['subjectid'];
            $this->assign('subjectid', $subjectid );

            $question_type = M('question_type qt')->field('qt.*')
                ->leftJoin('subject_question_type sqt','sqt.typeid=qt.typeid')
                ->where('sqt.subjectid='.$subjectid)->getAll();
            if(count($question_type)<=0){
                $this->showMessage('请先对该科目进行题型配置');
                exit;
            }
            $this->assign('question_type', $question_type );
            $GLOBALS['question_type'] = $question_type;

            $rule_cfg = M('rule_cfg')->where(array('ruleid'=>$ruleid))->getAll();
            foreach($rule_cfg as $k=>$v){
                $rule_cfg[$v['typeid'].'-'.$v['treeid']]['number'] = $v['number'];
                $rule_cfg[$v['typeid'].'-'.$v['treeid']]['model'] = $v['model'];
                unset($rule_cfg[$k]);
            }

            $GLOBALS['rule_cfg_injection'] = $rule_cfg;

            $subject_tree = M('subject_tree')
                ->field('treeid,parentid,chaptername,arrparentid,arrchildid,listorder')
                ->where(array('subjectid'=>$subjectid))
                ->order('listorder asc,treeid desc')->getAll();

            $this->tree_obj->fix_array = array('','　','│','├','└','<span class="ac-query-click" flag="none">','</span>');

            $create_obj = $this->tree_obj->createTree ( $subject_tree );
            $subject_tree = $this->tree_obj->createTreeMenu ($create_obj,'TreeCommon::createTreeChapterTableRule');

            $this->assign('subject_tree', $subject_tree );

            $this->display('rule/rule_edit.html');
        }else{
            $rule = xInput::request('rule');

            $rule_info = array(
                'rulename'=> $rule['info']['rulename'],
                'description'=> $rule['info']['description']
            );
            M('rule')->where(array('ruleid'=>$ruleid))->update($rule_info);

            $rulescoreModel = M('rule_score');
            $rulescoreModel->where(array('ruleid'=>$ruleid))->delete();

            foreach($rule['rule_score'] as $k => $v){
                $rule_score = array(
                    'ruleid' => $ruleid,
                    'typeid'=> $k,
                    'score'=> $v
                );
                $rulescoreModel->insert($rule_score);
            }

            $rulecfgModel = M('rule_cfg');
            $rulecfgModel->where(array('ruleid'=>$ruleid))->delete();
            foreach($rule['cfg'] as $k2 => $v2){
                foreach($v2 as $k => $v){
                    $v['number'] = intval($v['number']);
                    if($v['number']<=0) continue;
                    $k2 = intval($k2);
                    $rule_cfg = array(
                        'ruleid' => $ruleid,
                        'typeid'=> intval($v['typeid']),
                        'model'=> intval($v['model']),
                        'number'=> intval($v['number']),
                        'treeid'=> $k2
                    );
                    $rulecfgModel->insert($rule_cfg);
                }
            }
            $this->showMessage('操作成功');
        }
    }

    //删除组卷规则
    public function delete(){
        $ruleid = intval(xInput::get('ruleid'));
        $ruleid >0 OR $this->error();
        if(M('rule')->where(array('ruleid'=>$ruleid))->update(array('`delete`'=>1))){
            $this->success();
        }
        $this->error();
    }

    //清除组卷
    public function cleardata(){
        $ruleid = intval(xInput::get('ruleid'));
        $ruleid >0 OR $this->error();
        if(M('rule')->where(array('ruleid'=>$ruleid))->delete()){
            $this->success();
        }
        $this->error();
    }

    //恢复组卷
    public function restoredata(){
        $ruleid = intval(xInput::get('ruleid'));
        $ruleid >0 OR $this->error();
        if(M('rule')->where(array('ruleid'=>$ruleid))->update(array('`delete`'=>0))){
            $this->success();
        }
        $this->error();
    }

    //组卷回收站
    public function recycle(){
        $m = M('rule r');
        $count = $m->count();
        $page = $this->page($count);
        $m->limit($page->getLimit());

        $rule = $m->field('r.*,a.admin_name,s.subjectname')->where(array('r.delete'=>array('0','!=')))
            ->leftJoin('admin a','a.admin_id=r.admin_id')->leftJoin('subject s','s.subjectid=r.subjectid')
            ->order('r.listorder desc,r.ruleid desc')->getAll();

        $this->assign('rule', $rule);
        $this->assign('page', $page->show());

        $this->display('rule/rule_list.html');
    }

    //排序
    public function listorder($model){
        if(parent::listorder(M('rule'))){
            $this->success();
        }
        $this->error();
    }
}

