<?php 
class MemberAction extends MobileCommon{

	public function __construct(){
		if( get_parent_class()!='' && method_exists ( get_parent_class(), '__construct' ) ){
			parent::__construct();
		}

		$member = M('member')->where(array('memberid'=>$this->memberid))->getOne();
        $member = init_login($member);
		$this->assign('member',$member);

	}

	public function baseinfo(){
        $member = M('member')->where(array('memberid'=>$this->memberid))->getOne();
        $member = init_login($member);
        $this->assign('member',$member);
		$this->display('member/base_info.html');
	}

	public function major(){
		$query = xInput::request('query');
		$this->assign('url',base64_decode(xInput::request('url')));
		if($query!='insert'){
			$major = M('major')->getAll();
			$majorid= M('member_major')->field('majorid')
				->where(array('memberid'=>$this->memberid))->getAll();
			$this->assign('major',$major);
			$majorid_array = array();
			foreach ($majorid as $k => $v){
				$majorid_array[] = $v['majorid'];
			}
			$this->assign('majorid_array',$majorid_array);

			$this->display('member/block/major.html');
		}else{
			$majorid_array = xInput::request('majorid_array');
			if(!is_array($majorid_array) || count($majorid_array)<=0){
				xOut::json(array('status'=>'error','message'=>'请选择专业','code'=>-1));
			}

			$major = M('major')->where(array(
				'majorid'=>array(implode(',',$majorid_array),'in')))
				->getAll();

			$subject = M('subject')->where(array(
				'majorid'=>array(implode(',',$majorid_array),'in')))
				->getAll();

			$majorInsertData = $majorInsertDataTemp = array();
			foreach ($major as $k => $v ){
				$majorInsertData[] = array(
					'memberid'=>$this->memberid,
					'majorid'=>$v['majorid'],
					'majorname'=>$v['majorname']
				);
				$majorInsertDataTemp[$v['majorid']] = $v['majorname'];
			}

			M('member_major')->where(array(
				'memberid'=>$this->memberid,
				'majorid'=>array(implode(',',$majorid_array),'in')))->delete();

			M('major')->insert($majorInsertData);

			foreach ($subject as $k => $v ){
				$majorInsertData[] = array(
					'memberid'=>$this->memberid,
					'majorid'=>$v['majorid'],
					'majorname'=>$majorInsertDataTemp[$v['majorid']],
					'subjectid'=>$v['subjectid'],
					'subjectname'=>$v['subjectname']
				);
			}

			M('member_subject')->where(array(
				'memberid'=>$this->memberid,
				'majorid'=>array(implode(',',$majorid_array),'in')))->delete();

			M('member_subject')->insert($majorInsertData);

			xOut::json(array('status'=>'success','message'=>'设置成功','code'=>1));

		}
	}

	public function center(){
		$question_error_count = M('question_error')->where(array('memberid'=>$this->member['memberid']))->count();
		$this->assign('question_error_count',$question_error_count);

		$question_favorite_count = M('favorite')->where(array('memberid'=>$this->member['memberid']))->count();
		$this->assign('question_favorite_count',$question_favorite_count);

		$comment_count = M('question_comment')->where(array('memberid'=>$this->member['memberid']))->count();
		$this->assign('comment_count',$comment_count);

		$this->display('member/center.html');
    }

	public function mobile(){
		if(xInput::request('query')!='insert'){
			if(xInput::request('url')!=''){
				$this->assign('location_url',base64_decode(xInput::request('url')));
			}
			$this->display('member/block/mobile.html');
		}else{
		    $user = xInput::request('user');
            $code = trim($user['code']);
			$mobile = trim($user['mobile']);
            if(!preg_match('/^1(3|4|5|7|8)\d{9}$/',$mobile)){
				xOut::json(array('status'=>'error','message'=>'请正确填写手机号码','code'=>-1));
			}
            $sms = new Sendsms();
            $result = $sms->msmVerify($mobile,$code,1);
            if($result!==true){
                xOut::json ( array('status'=>'error','message'=>$result,'code'=>-1));
            }

            $member = M('member')->where(array('mobile'=>$mobile))->getOne();

            if(count($member)>0){
                if($this->member['curr_openid']){
                    M('member_oauth')->where(array('openid'=>$this->member['curr_openid']))
                        ->update(array('memberid'=>$member['memberid']));
                }else{
                    xOut::json(array('status'=>'error','message'=>'openid丢失,绑定失败','code'=>-1));
                }
            }else{
                $memberModel = M('member')->where(array('memberid'=>$this->memberid));
                $memberModel->update(array('mobile'=>$mobile));
            }

			$this->member['mobile'] = $mobile;
			xCookie::set($this->memberkey,$this->member);
			xOut::json(array('status'=>'success','message'=>'操作成功','code'=>1));
		}
	}

	public function sex(){
		if(xInput::request('query')!='insert'){
			if(xInput::request('url')!=''){
				$this->assign('location_url',base64_decode(xInput::request('url')));
			}
			$this->display('member/block/sex.html');
		}else{
			$sex = intval(xInput::request('sex'));
			if( !in_array($sex,array(1,2)) ){
				xOut::json(array('status'=>'error','message'=>'请正确选择心别','code'=>-1));
			}
			$memberModel = M('member')->where(array('memberid'=>$this->member['memberid']));
			$memberModel->update(array('sex'=>$sex));
			$this->member['sex'] = $sex;
			$this->member['sex_name'] = $sex==1?'男':($sex==2?'女':'未设置');
			xCookie::set($this->memberkey,$this->member);
			xOut::json(array('status'=>'success','message'=>'修改成功','code'=>1));
		}
	}

	public function nickname(){
		if(xInput::request('query')!='insert'){
			if(xInput::request('url')!=''){
				$this->assign('location_url',base64_decode(xInput::request('url')));
			}
			$this->display('member/block/nickname.html');
		}else{
			$nickname = trim(xInput::request('nickname'));
			if( strlen(preg_replace('/\s/','',$nickname))==0 ){
				xOut::json(array('status'=>'error','message'=>'请正确填写昵称','code'=>-1));
			}
			$memberModel = M('member')->where(array('memberid'=>$this->member['memberid']));
			$memberModel->update(array('nickname'=>$nickname));
			$this->member['nickname'] = $nickname;
			xCookie::set($this->memberkey,$this->member);
			xOut::json(array('status'=>'success','message'=>'修改成功','code'=>1));
		}
	}

	public function email(){
		if(xInput::request('query')!='insert'){
			if(xInput::request('url')!=''){
				$this->assign('location_url',base64_decode(xInput::request('url')));
			}
			$this->display('member/block/email.html');
		}else{
			$email = trim(xInput::request('email'));
			if(!preg_match('/^[\d\w\-]{3,20}@[\d\w\.]{2,15}$/',$email)){
				xOut::json(array('status'=>'error','message'=>'请正确填写邮箱','code'=>-1));
			}
			$memberModel = M('member')->where(array('memberid'=>$this->member['memberid']));
			$memberModel->update(array('email'=>$email));
			$this->member['email'] = $email;
			xCookie::set($this->memberkey,$this->member);
			xOut::json(array('status'=>'success','message'=>'修改成功','code'=>1));
		}
	}

	public function introduction(){
		if(xInput::request('query')!='insert'){
			if(xInput::request('url')!=''){
				$this->assign('location_url',base64_decode(xInput::request('url')));
			}
			$this->display('member/block/introduction.html');
		}else{
			$introduction = trim(xInput::request('introduction'));
			$memberDetailModel = M('member')->where(array('memberid'=>$this->memberid))
                ->update(array('introduction'=>$introduction));
			xOut::json(array('status'=>'success','message'=>'修改成功','code'=>1));
		}
	}

	public function changefacepicture(){
		$config = array(
			"pathFormat" => __UPLOAD__.'file/{yyyy}{mm}{dd}/{time}{rand:6}'.$this->memberid,
			"maxSize" => 51200000,
			"allowFiles" => array (
				0 => '.png',
				1 => '.jpg',
				2 => '.jpeg',
				3 => '.gif',
				4 => '.bmp'
			)
		);
		$up = new Uploader('facepicture', $config);
		$file_info = $up->getFileInfo();
		$this->assign('formid',xInput::request('formid'));
		$this->assign('file_info',$file_info);
		$result = M('member')->where(array('memberid'=>$this->memberid))->update(array('facepicture'=>$file_info['url']));
		$this->assign('result',$result);
		if($result){
			$member = xCookie::get('member');
			$member['facepicture'] = $file_info['url'];
			xCookie::set($this->memberkey,$member);
		}
		$this->display('member/block/upload_face.html');
	}

	public function area(){
		if(xInput::request('query')!='insert'){
			//选择考试区域
			$examAreaModel = M('exam_area');
			$exam_area_data = $examAreaModel->order('letter asc')->getAll();
			$this->assign('exam_area',$exam_area_data);
			$this->assign('submint_url',U('changearea'));
			$this->assign('areaid',$this->member['areaid']);
			if(xInput::request('url')!='')$this->assign('location_url',base64_decode(xInput::request('url')));
			$this->display('member/block/area.html');
		}else{
			$areaid = intval(xInput::request('areaid',0));
			$memberModel = M('member')->where(array('memberid'=>$this->memberid));
			if($memberModel->update(array('areaid'=>$areaid))){
				$this->member['areaid'] = $areaid;
				$this->member['position'] = '未知';
				$examAreaData = M('exam_area')->where(array('areaid'=>$this->member['areaid']))
					->getOne();
				if(count($examAreaData)>=0){
					$this->member['position'] = $examAreaData['areaname'];
				}
				xCookie::set($this->memberkey,$this->member);
				xOut::json(array('status'=>'success','message'=>'设置成功','code'=>1));
			}else{
				xOut::json(array('status'=>'error','message'=>'系统错误','code'=>-1));
			}
		}
	}

    public function password(){
        if(xInput::request('query')!='insert'){
            $this->display('member/block/password.html');
        }else{
            $password = xInput::request('password','');
            if(strlen($password)<6 || strlen($password)>20 ){
                xOut::json(array('status'=>'error','message'=>'密码长度为6-20','code'=>-1));
            }else{
                $memberModel= M('member');
                $result = $memberModel->where(array('memberid'=>$this->memberid))
                    ->update(array('password'=>createMemberPassword($password)));
                $this->member['password'] = 'yes';
                xCookie::set($this->memberkey,$this->member);
                xOut::json(array('status'=>'success','message'=>'设置成功','code'=>1));
            }
        }
    }


    //账号绑定
    public function bind(){
        $query = xInput::request('query');
        if($query=='qq'){
            $this->display('member/block/password.html');
        }elseif($query=='weibo') {
            $this->display('member/block/password.html');
        }else{
            $MemberOauthModel = M('member_oauth')->where(array('memberid'=>$this->memberid));
            $bind_data = $MemberOauthModel->getAll();
            $bind_type = [];
            foreach ($bind_data as $k => $v ){
                $this->assign('oauthname'.$v['type'],$v['oauthname']);
                $this->assign('figureurl'.$v['type'],$v['figureurl']);
                $this->assign('type'.$v['type'],$v['type']);
                $this->assign('openid'.$v['type'],$v['openid']);
            }
            $this->display('member/bind.html');
        }
    }

} 