<?php

/**
 * @return bool
 * 检查是否已经登录
 */
function ac_login(){
    $memberkey = App::getConfig('Config','memberkey','member');
    $member = xCookie::get($memberkey);
    if(intval($member['memberid'])>0){
        return true;
    }
    return false;
}

/**
 * @param $member
 * @return mixed
 * 初始化登录信息
 */
function init_login($member){
    $member['facepicture'] = get_face($member['facepicture']);
    $member['sex_name'] = get_sex($member['sex']);
    $member['position'] = get_position($member['areaid']);
    return $member;
}

/**
 * 错误页面跳转函数
 */
function ac_error_page(){
    exit('跳转到错误页面');
}

/**
 * 生成缩略图函数
 * @param  $imgurl 图片路径
 * @param  $width  缩略图宽度
 * @param  $height 缩略图高度
 * @param  $autocut 是否自动裁剪 默认裁剪，当高度或宽度有一个数值为0是，自动关闭
 * @param  $smallpic 无图片是默认图片路径
 */
function ac_thumb($imgurl, $width = 100, $height = 100 ,$autocut = 1, $smallpic = 'nopic.gif') {
	return $imgurl?$imgurl:'http://www.dcloud.io/hellomui/images/yuantiao.jpg';
}


function get_bind_logo($type){
    switch ($type){
        case 1:
            return __PUBLIC__.'Mobile/images/svg/qq.svg';
            break;
        case 2:
            return __PUBLIC__.'Mobile/images/svg/weibo.svg';
            break;
        case 3:
            return __PUBLIC__.'Mobile/images/svg/weixin.svg';
            break;
    }
}

function get_unbind_logo($type){
    switch ($type){
        case 1:
            return __PUBLIC__.'Mobile/images/svg/qq_gray.svg';
            break;
        case 2:
            return __PUBLIC__.'Mobile/images/svg/weibo_gray.svg';
            break;
        case 3:
            return __PUBLIC__.'Mobile/images/svg/weixin_gray.svg';
            break;
    }
}

function outStatus($status_code=0,$message='操作成功'){
    return array('status'=>'success','message'=>$message,'code'=>$status_code);
}

function outError($message='操作错误'){
    return array('status'=>'error','message'=>$message,'code'=>-1);
}
function outSuccess($data=array(),$message='操作成功'){
    if( is_string($data) && $data!='' ){
        $message = $data;
        $data = array();
    }
    return array('status'=>'success','message'=>$message,'code'=>0,'data'=>$data);
}



