<?php
//App::setConfig('Config','debug',false);

class PcsCommon extends PcCommon{

    //执行父类构造方法
    public function __construct(){

        if (get_parent_class() != '' && method_exists(get_parent_class(), '__construct')) {
            parent::__construct();
        }
        //如果没有登录
        if (intval($this->memberid) <= 0) {
            //不是注册或者登录模块
            if (C_IS_AJAX) {
                xOut::json(array('status' => 'islogin', 'message' => '请先登录', 'code' => 1));
            } else {
                header('Location:' . U('login/init'));
                exit;
            }
        } else {
            //如果信息不完整
            $this->_checkmobile();
            $this->_checkarea();
            $this->_checknickname();
        }
    }

    private function _checkmobile(){
        //信息完整度检测
        if(!preg_match('/^1(3|4|5|7|8)\d{9}$/',trim($this->member['mobile']))){
            //填写手机号码
            $this->display('login/write_mobile.html');
        }
        return true;
    }

    private function _checkarea(){
        if(intval($this->member['areaid'])<=0){
            //选择考试区域
            $examAreaModel = M('exam_area');
            $exam_area_data = $examAreaModel->order('letter asc')->getAll();
            $this->assign('exam_area',$exam_area_data);
            $this->display('login/select_area.html');
        }
        return true;
    }

    private function _checknickname(){
        if(!$this->member['nickname']){
            $this->display('login/write_nickname.html');
        }
        return true;
    }

}