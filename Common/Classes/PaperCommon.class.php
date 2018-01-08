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
// + Api试卷接口
// +------------------------------------------------------------------------------------*/
class PaperCommon{
	
	public $error_status_code = array('status'=>'error','message'=>'获取失败','code'=>-1);
	public $success_status_code = array('status'=>'success','message'=>'获取成功','code'=>0);
	
	public function __construct(){
		if( get_parent_class()!='' && method_exists ( get_parent_class(), '__construct' ) ){
			parent::__construct();
		}
		
	}
	
	public function get(){
		$questionid = intval(xInput::request('questionid'));
		$questionid>0 or xOut::json(array('status'=>'error','message'=>'参数错误','code'=>-1));
		
		$question_data = M('question')->where(array('questionid'=>$questionid))->getOne();
		if(count($question_data)<=0){
			xOut::json(array('status'=>'error','message'=>'试题不存在','code'=>-1));
		}
		$question_type = M('question_type')->field('modelclass')->where(array('typeid'=>$question_data['typeid']))->getOne();
		if(count($question_type)<=0){
			xOut::json(array('status'=>'error','message'=>'模型不存在','code'=>-1));
		}
		//获取试题附加数据
		$questionTypeClass = ucfirst(strtolower($question_type['modelclass']));
		
		$questionTypeClassObj = new $questionTypeClass();
		$question_data['addition'] = $questionTypeClassObj->get($questionid);
		$this->success_status_code['data'] = $question_data;
		xOut::json($this->success_status_code);
	}
}

