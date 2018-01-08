<?php 
//强制关闭调试模式
//App::setConfig('Config','debug',false);

class CommentAction extends ApiCommon {
	
	public function getComment(){
		$questionid = intval(xInput::request('questionid'));
		$pagesize = intval(xInput::request('pagesize'),10);

		if($questionid<=0) xOut::json(outError('参数错误'));
		$questionCommentModel = M('question_comment')->where(['questionid'=>$questionid]);

		$page = $this->page($questionCommentModel->count(false),$pagesize);
		$questionCommentModel->limit($page->getLimit()); 
		$comment_data = $questionCommentModel->order('commentid desc')->getAll();

		/*
		$memberid_str = '';$comment_data_temp = [];
		foreach($comment_data as $k => $v ){
			$memberid_str = ','.$v['memberid'];
			$comment_data_temp[$v['memberid'].'-'.$v['commentid']] = $v;
		}
		$memberid_str = substr($memberid_str,1);
		if($memberid_str=='')
			xOut::json(array('status'=>'success','message'=>'加载成功','code'=>1,'data'=>[]));
		//获取用户信息
		$member_data = M('member')->where(['memberid'=>[$memberid_str,'in']])->getAll();
		$member_data_temp = [];
		foreach($member_data as $k => $v ) $member_data_temp[$v['memberid']] = $v;
		
		foreach($comment_data_temp as $k => $v ){
			$memberid = explode('-',$k);
			$comment_data_temp[$k]['nickname'] = $member_data_temp[$memberid[0]]['nickname'];
			$comment_data_temp[$k]['facepicture'] = $member_data_temp[$memberid[0]]['facepicture'];
		}*/
		foreach($comment_data as $k => $v ){
			$comment_data[$k]['addtime'] = ac_friendly_date($v['addtime']);
		}
		xOut::json(array('status'=>'success','message'=>'加载成功','code'=>1,'data'=>$comment_data));
    }
	
	public function sendcomment(){
		$comment = xInput::request('comment');
		$questionid = intval(xInput::request('questionid'));
		$parentid = intval(xInput::request('parentid',0));
		$questionCommentModel = M('question_comment');
		$question = M('question')->field('subjectid')->where(array('questionid'=>$questionid))->getOne();
		$member = xCookie::get('member');
		$insertData = array(
			'memberid'=>$member['memberid'],
			'nickname'=>$member['nickname'],
			'facepicture'=>$member['facepicture'],
			'questionid'=>$questionid,
			'subjectid'=>$question['subjectid'],
			'parentid'=>$parentid,
			'addtime'=>time(),
			'description'=>$comment,
			'likezan'=>0,
			'replay'=>0
		);

		$result = $questionCommentModel->insert($insertData);
		if($result){
			xOut::json(array('status'=>'success','message'=>'发表成功','code'=>1));
		}
		xOut::json(array('status'=>'error','message'=>'发表失败','code'=>-1));
	}

	public function commentlikezan(){
		$commentid = intval(xInput::request('commentid'));
		if($commentid<=0) xOut::json(array('status'=>'error','message'=>'参数错误','code'=>-1));
		$result = M('question_comment')->where(['commentid'=>$commentid])
			->update(['likezan+'=>1]);
			
		xOut::json(array('status'=>'success','message'=>'点赞成功','code'=>1));
	}
	
} 


















