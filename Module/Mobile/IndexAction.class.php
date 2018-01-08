<?php 
class IndexAction extends MobileCommon{

    public function __construct(){

        if( get_parent_class()!='' && method_exists ( get_parent_class(), '__construct' ) ){
            parent::__construct();
        }
    }

    //首页
    public function init(){
		$major = M('major')->order('listorder desc')->getAll();
		$this->assign('major',$major);
		$this->display('index/index.html');
    }

    //在线考试
    public function examInit(){
        $majorid = intval(xInput::request('majorid'));

        $member_major = $this->getMemberMajor($majorid);

        $this->assign('member_major',$member_major);

        $exam_synthesis_paper = M('exam_synthesis_paper')->where(array(
            'majorid'=>$majorid,
            'areaid'=>$this->member['areaid']
        ))->getAll();


        $major = M('major')->where(array('majorid'=>$majorid))->getOne();

        $this->assign('majorid',$majorid);
        $this->assign('major',$major);
        $this->assign('synthesis_paper',$exam_synthesis_paper);

        $this->display('exam/exam_list.html');
    }

    //点击章节练习
	public function major(){
        $majorid = intval(xInput::request('majorid'));

        $major = M('major')->where(['majorid'=>$majorid])->getOne();
        $this->assign('major',$major);
        $this->assign('majorid',$majorid);

		$subject = M('subject')->where(['majorid'=>$majorid])
            ->order('listorder desc')->getAll();
		$this->assign('subject',$subject);
		$this->display('index/subject_list.html');
    }
	
	//ajax获取章节列表
	public function chapter(){
		$subjectid = intval(xInput::request('subjectid'));
		$parentid = intval(xInput::request('parentid'));
		if($parentid<=0){
			$subject_tree = M('subject_tree')->where(['subjectid'=>$subjectid,'parentid'=>0])->getOne();
			$parentid = $subject_tree['treeid'];
		}
		$chapter = M('subject_tree')->where(['subjectid'=>$subjectid,'parentid'=>$parentid])->getAll();
		foreach($chapter as $k => $v){
			$chapter[$k]['url'] = U('chapter',['subjectid'=>$v['subjectid'],'parentid'=>$v['treeid']]);
			$chapter[$k]['doquestion_url'] = U('Mobile/Doquestion/getquestionlist',['subjectid'=>$v['subjectid'],'treeid'=>$v['treeid']]);
		}
		xOut::json($chapter);
	}
}




















