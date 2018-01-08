<?php



/**
 * @param null $memberid
 * @return mixed|string
 * 获取用户基本信息
 */
function get_member($memberid=null){
    $memberid = is_null($memberid)?get_memberid():$memberid;
    $key = 'member_info'.$memberid;
    $member_data = xTempCache::get($key);
    if(!$member_data){
        $member_data = M('member')->where(['memberid'=>$memberid])->getOne();
    }
    xTempCache::set($key,$member_data);
    return $member_data;
}

/**
 * @return int
 * 获取用户id
 */
function get_memberid(){
    $memberkey = App::getConfig('Config','memberkey','member');
    $member = xCookie::get($memberkey);
    $member = $member;
    return intval($member['memberid']);
}

/**
 * @param null $memberid
 * @return mixed
 * 获取用户昵称
 */
function get_username($memberid=null){
    $member = get_member($memberid);
    return $member['nickname'];
}

/**
 * @param $sex
 * @return string
 * 转换性别
 */
function get_sex($sex=null,$memberid=null){
    if(is_null($sex)){
        $member = get_member($memberid);
        $sex = $member['sex'];
    }
    if(intval($sex)==1) return '男';
    if(intval($sex)==2)return '女';
    return '未填写';
}

/**
 * @param null $facepicture
 * @param null $memberid
 * @return null|string
 * 获取用户头像
 */
function get_face($facepicture=null,$memberid=null){
    if(is_null($facepicture)){
        $member = get_member($memberid);
        $facepicture = $member['facepicture'];
    }
    if(!$facepicture){
        return __IMG__.'member/default_face.gif';
    }
    return $facepicture;
}


/**
 * @param null $areaid
 * @param null $memberid
 * @return string
 * 获取考区名称
 */
function get_position($areaid=null,$memberid=null){
    if(is_null($areaid)){
        $member = get_member($memberid);
        $areaid = $member['areaid'];
    }
    $examAreaData = M('exam_area')->where(array('areaid'=>$areaid))->getOne();
    if(count($examAreaData)>=0){
        return $examAreaData['areaname'];
    }
    return '未填写';
}

/**
 * @param null $memberid
 * @return mixed
 * 获取手机号码
 */
function get_mobile($memberid=null){
    $member = get_member($memberid);
    return $member['mobile'];
}

/**
 * @param $password
 * @return string
 * 根据明文密码生成用户加密密码
 */
function createMemberPassword($password){
    return md5($password);
}

