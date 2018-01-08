<?php

class FeedbackAction extends ApiCommon  {
	
    public function feedBackSubmit(){

		$questionid = intval(xInput::request('questionid'));
        $description = trim(xInput::request('description'));
        $type = intval(xInput::request('type'));
        $memberid = intval(xInput::request('memberid'));

        $questionid >0 OR xOut::json(outError( '非法操作' ));
        $type >0 OR xOut::json(outError( '非法操作' ));
        $memberid >0 OR xOut::json(outError( '非法操作' ));

        $member = M('member')->where('memberid='.$memberid)->getOne();
        if(count($member)<=0){
            xOut::json(outError( '非法操作' ));
        }

        $result = M('feedback')->insert([
            '`memberid`'=>$memberid,
            '`username`'=>$member['nickname'],
            '`type`'=>$type,
            '`description`'=>$description,
            '`questionid`'=>$questionid,
            '`addtime`'=>time()
        ]);

        if($result){
            xOut::json(outSuccess([],'感谢你的支持'));
        }
        xOut::json(outError('系统错误'));
    }


}




















