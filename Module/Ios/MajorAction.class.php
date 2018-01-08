<?php 
class MajorAction extends ApiCommon  {

    public function __construct(){
        if( get_parent_class()!='' && method_exists ( get_parent_class(), '__construct' ) ){
            parent::__construct();
        }
    }

    public function getMajorList(){
		$major = M('major')->order('listorder desc')->getAll();
		xOut::ajax(outSuccess($major));
    }
	
	public function getSubjectList(){
        $majorid = intval(xInput::request('majorid'));
        $majorid>0 || xOut::json(outError('参数错误'));
		$subject = M('subject')->where(['majorid'=>$majorid])
            ->order('listorder desc')->getAll();
        xOut::ajax(outSuccess($subject));
    }
	
	//ajax获取章节列表
	public function getChapterList(){
		$subjectid = intval(xInput::request('subjectid'));
		$parentid = intval(xInput::request('parentid'));
        $subjectid>0 || xOut::json(outError('参数错误'));
		if($parentid<=0){
			$subject_tree = M('subject_tree')->where(['subjectid'=>$subjectid,'parentid'=>0])->getOne();
			$parentid = $subject_tree['treeid'];
		}
		$chapter = M('subject_tree')->where(['subjectid'=>$subjectid,'parentid'=>$parentid])->getAll();
		xOut::json(outSuccess($chapter));
	}

	public function getInlineList(){
        $majorid = intval(xInput::request('majorid'));
        $majorid>0 || xOut::json(outError('参数错误'));
        $exam_synthesis_paper = M('exam_synthesis_paper')->where(array(
            'majorid'=>$majorid,
            'areaid'=>$majorid['areaid']
        ))->getAll();
        xOut::json(outSuccess($exam_synthesis_paper));
    }
}




















