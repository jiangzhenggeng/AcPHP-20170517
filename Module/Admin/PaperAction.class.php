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
class PaperAction extends AdminCommon{

	public function __construct(){
		if( get_parent_class()!='' && method_exists ( get_parent_class(), '__construct' ) ){
			parent::__construct();
		}
		
	}
	
	public function lists($recycle=false){
		$search = xInput::request('search');
		
		if($recycle){
			$this->assign('recycle', true);
			$delete = 1;
		}else{
			$this->assign('recycle', false);
			$delete = 0;
		}
		$paperModel = M('paper ep');
		if(count($search['areaid']) && is_array($search['areaid'])){
			$paperModel->leftJoin('paper_area epa','epa.paperid=ep.paperid');
			$paperModel->where(['epa.areaid'=>[$search['areaid'],'in']]);
		}
		if( $search['subjectid'] ){
			$paperModel->where(['ep.subjectid'=>$search['subjectid'] ] );
		}
		if( $search['start_time'] ){
			$paperModel->where('ep.addtime>='.strtotime($search['start_time']));
		}
		if( $search['end_time'] ){
			$paperModel->where('ep.addtime<='.strtotime($search['end_time']));
		}
		
		$count = $paperModel->where(array('ep.`delete`'=>$delete))->count(false);
		$page = $this->page($count);
		$paper = $paperModel->order('listorder desc,starttime desc')
			->where(array('ep.`delete`'=>$delete))
			->limit($page->getLimit())->getAll();
		foreach($paper as $k => $v){
			$paper[$k]['question_total'] = M('paper_data')->where(array('paperid'=>$v['paperid']))->count();
		}
		
		
		$subject =  M('subject')->getAll();
		$this->assign('subject', $subject);
		
		$exam_area = M('exam_area')->getAll();
		$this->assign('exam_area', $exam_area);
		$this->assign('paper', $paper);
		$this->assign('page', $page->show());
		$this->assign('search', $search);
		$this->display('paper/paper_list.html');
	}
	
	//添加管理员
	public function add(){
		if ( !isset($_POST['query']) || $_POST['query']!='insert' ){
			$subject = M('subject')->getAll();
	        $this->assign('subject', $subject );
			
			$rule = M('rule')->getAll();
	        $this->assign('rule', $rule );
			
			$exam_area = M('exam_area')->getAll();
			$this->assign('exam_area', $exam_area);
			//生成系统令牌
	        $this->assign('token', $this->token() );
	        $this->display('paper/paper_add.html');
	    }else{
			//校验系统令牌
	        if(!$this->verifyToken(xInput::request('token'))){
				//$this->error('令牌校验错误');
			}
			$paper = xInput::request('paper');
			
			$paper['starttime'] = strtotime($paper['starttime']);
			$paper['adminid'] = xSession::get('admin.admin_id');
			$paper['addtime'] = time();
			
			$paperid = M('paper')->insert($paper);
			
			$paper_area = xInput::request('paper_area');
			$insertData = [];
			foreach($paper_area as $k => $v ){
				$insertData[] = array(
					'areaid'=>$v,
					'paperid'=>$paperid,
				);
			}
			M('paper_area')->insert($insertData);
			
			if($paperid){
				$this->getquestion($paperid);
				$this->success();
			}
			$this->error();
		}
	}
	//修改管理员
	public function edit(){
		$paperid = intval(xInput::request('paperid'));
		$paperid >0 OR $this->showMessage('非法操作');
		
		if ( !isset($_POST['query']) || $_POST['query']!='insert' ){
			$paper = M('paper')->where(array('paperid'=>$paperid))->getOne();
	        $this->assign('paper', $paper );
			
			$subject = M('subject')->getAll();
	        $this->assign('subject', $subject );
			
			$rule = M('rule')->getAll();
	        $this->assign('rule', $rule );
			$paper_area_temp = M('paper_area')->where(array('paperid'=>$paperid))->getAll();
			$paper_area = [];
			foreach($paper_area_temp as $k => $v ){
				$paper_area[] = $v['areaid'];
			}
			$this->assign('paper_area', $paper_area);
			
			$exam_area = M('exam_area')->getAll();
			$this->assign('exam_area', $exam_area);
			//生成系统令牌
	        $this->assign('token', $this->token() );
	        $this->display('paper/paper_edit.html');
	    }else{
			//校验系统令牌
	        if(!$this->verifyToken(xInput::request('token'))){
				//$this->error('令牌校验错误');
			}
			$paper = xInput::request('paper');
			
			$paper['starttime'] = strtotime($paper['starttime']);
			
			$result = M('paper')->where(array('paperid'=>$paperid))->update($paper);
			
			M('paper_area')->where(array('paperid'=>$paperid))->delete();
			
			$paper_area = xInput::request('paper_area');
			$insertData = [];
			foreach($paper_area['areaid'] as $k => $v ){
				$insertData[] = array(
					'areaid'=>$v,
					'paperid'=>$paperid,
				);
			}
			$result2 = M('paper_area')->insert($insertData);
			
			if($result || $result2){
				$this->success();
			}
			$this->error();
		}
	}
	
	public function statistics(){
		$size = 20;
		$page = intval( xInput::request('page') );
		$page = $page>0?$page:0;
		$start = $page * $size;
		
		$paperModel = M('paper');
		$result = $paperModel->where('starttime<'.time())->where('hasquestion=1')->limit($page.','.$size)->getAll();
		//print_r($result);
		
		if(is_array($result) && count($result)>0 ){
			foreach( $result as $k => $v ){
				$count = M('paper_data')->where('paperid='.$v['paperid'] )->count();
			
				if($count){
					$paperModel->clearWhere()->where('paperid='.$v['paperid'] )->update(['hasquestion'=>1]);
				}else{
					$paperModel->clearWhere()->where('paperid='.$v['paperid'] )->update(['hasquestion'=>0]);
				}
			}
			echo '<div style="">正在计算...</div><script>window.location="'.U('',array('page'=>$page+1)).'";</script>';
		}else{
			$this->success();
		}
	}
	//删除试卷（放入回收站）
	public function delete($recover=1){
		$paperid = intval(xInput::get('paperid'));
		$paperid >0 OR $this->error();
		if(M('paper')->where(array('paperid'=>$paperid))->update(array('`delete`'=>$recover))){
			$this->success();
		}
		$this->error();
	}
	
	//排序
	public function listorder($model){
		if(parent::listorder(M('paper'))){
			$this->success();
		}
		$this->error();
	}
	
	//试题回收站管理
	public function recycle(){
		$this->lists('recycle');
	}
	
	//从回收站恢复试题
	public function recover(){
		$this->delete(0);
	}
	
	//从回收站清除试题
	public function clear(){
		$paperid = intval(xInput::get('paperid'));
		$paperid >0 OR $this->error();
		if(M('paper')->where(array('paperid'=>$paperid))->delete()){
			M('paper_data')->where(array('paperid'=>$paperid))->delete();
			$this->success();
		}
		$this->error();
	}
	
	//根据抽题规则进行抽题
	public function getquestion($_paperid=null){
		if($_paperid){
			$paperid = $_paperid;
		}else{
			$paperid = intval(xInput::get('paperid'));
			$paperid >0 OR xOut::json(array('status'=>'error','code'=>-1,'message'=>'非法操作'));
		}
		
		$paperData = M('paper')->where(array('paperid'=>$paperid))->getOne();
		$paperData['id'] = $paperData['paperid'];
		$PaperGetDataObj = new PaperGetData();
		$questionData = $PaperGetDataObj->paper_get_question($paperData);
		if( count($questionData)<=0 ){ 
			if(!$_paperid){
				xOut::json(array('status'=>'success','code'=>0,'message'=>'没有试题'));
				exit;
			}else{
				return false;
			}
		}
		$insertData = array();
		//写入数据
		foreach($questionData as $k => $v){
			$insertData[] = array(
				'paperid'=>$v['id'],
				'questionid'=>$v['questionid'],
				'typeid'=>$v['typeid']
			);
		}
		$paper_data_obj = M('paper_data');
		$paper_data_obj->where(array('paperid'=>$paperData['paperid']))->delete();
		$result = $paper_data_obj->insert($insertData);
		
		if( $result ){
			
			$count = count($insertData);
			if($count>0) M('paper')->where(array('paperid'=>$paperData['paperid']))->update(array('hasquestion'=>1));
			
			if($_paperid){
				return true;
			}
			
			xOut::json(array('total'=>'<font color="red">'.$count.'<font>','status'=>'success','code'=>0,'message'=>'抽取成功（'.$count.'）道题'));
		}
	}

	public function view($c=''){
		parent::view('paper_data');
	}
	
}

