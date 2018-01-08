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

/*/ +--------------------------------------------------------------------------------------
// + Api数据返回格式规定
// + 
array(
	'status'=>'error',		==> succes|error
	'message'=>'参数错误',	信息说明
	'code'=>-1,				状态代码==>0|-1
	'data'=>[...]			返回的数据
)
// +------------------------------------------------------------------------------------*/
class QuestionCommon {

	private $member = null;
	private $memberid = null;

	public function __construct(){
		if( get_parent_class()!='' && method_exists ( get_parent_class(), '__construct' ) ){
			parent::__construct();
		}

		$this->member = xInput::cookie('member');
		$this->memberid = $this->member['memberid'];
	}
	
	public function get($questionid=null,$user_answer=null){
		if(is_null($questionid)) $questionid = intval(xInput::request('questionid'));
		
		if($questionid<0 )return false;
		
		$question_data = M('question')->where(array('questionid'=>$questionid))->getOne();
		if(count($question_data)<=0){
			return false;
		}
		$question_type = M('question_type')->field('modelclass')->where(array('typeid'=>$question_data['typeid']))->getOne();
		if(count($question_type)<=0){
			return false;
		}
		//获取试题附加数据
		$questionTypeClass = ucfirst(strtolower($question_type['modelclass']));
		
		$questionTypeClassObj = new $questionTypeClass();
		$question_data['addition'] = $questionTypeClassObj->get($questionid,$user_answer);
		return $question_data;
	}
	
	public function automaticScoring($questionid, $answer ){
		if(is_null($questionid)) $questionid = intval(xInput::request('questionid'));
		
		if($questionid<0 )return false;
		
		$question_data = M('question')->where(array('questionid'=>$questionid))->getOne();
		if(count($question_data)<=0){
			return false;
		}
		$question_type = M('question_type')->field('modelclass')->where(array('typeid'=>$question_data['typeid']))->getOne();
		if(count($question_type)<=0){
			return false;
		}
		//获取试题附加数据
		$questionTypeClass = ucfirst(strtolower($question_type['modelclass']));
		
		$questionTypeClassObj = new $questionTypeClass();
		$question_data['addition'] = $questionTypeClassObj->get($questionid);
		return $question_data;
	}

	public function insertErrorQuert($errorQuestionData,$user_answer_all,$typeid_array){

		foreach ($errorQuestionData as $k => $v ){
			foreach ($v['data'] as $k2 => $v2 ) {
				if(isset($v2['istrue']) && $v2['istrue']){
					continue;
				}else{
					$answer_sub = reset($v2);
					if(is_array($answer_sub) && isset($answer_sub['istrue'])){
						$is_continue = true;
						foreach ($v2 as $k3 => $v3 ){
							if(isset($v3['istrue']) && !$v3['istrue']) $is_continue = false;
						}
						if($is_continue)  continue;
					}
				}

				$insertDataError[] = array(
					'memberid'=>$this->memberid,
					'questionid'=>$k2,
					'addtime'=>time(),
					'typeid'=>$typeid_array[$k2],
					'subjectid'=>xInput::request('subjectid',0),
					'user_answer'=>array2string($user_answer_all[$k2])
				);
			}
		}
		return M('question_error')->replace($insertDataError);
	}

	public function removeErrorQuert($errorQuestionData,$user_answer_all){
		$insertDataError = [];
		foreach ($errorQuestionData as $k => $v ){
			foreach ($v['data'] as $k2 => $v2 ) {
				if(isset($v2['istrue']) && !$v2['istrue']){
					continue;
				}else{
					$answer_sub = reset($v2);
					if(is_array($answer_sub) && isset($answer_sub['istrue'])){
						$is_continue = true;
						foreach ($v2 as $k3 => $v3 ){
							if(isset($v3['istrue']) && $v3['istrue']) $is_continue = false;
						}
						if($is_continue)  continue;
					}
				}

				$insertDataError[] = $k2;
			}
		}
		if(count($insertDataError)){
			return M('question_error')->where(array(
				'questionid'=>array(implode(',',$insertDataError),'in'),
				'memberid'=>$this->memberid
			))->delete();
		}
		return true;
	}
}

