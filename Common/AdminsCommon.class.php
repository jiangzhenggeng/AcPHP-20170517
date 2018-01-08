<?php
/**
 * Created by PhpStorm.
 * User: jiangzg
 * Date: 16/7/21
 * Time: 21:47
 */

class AdminsCommon extends Common{
    protected $noLoginUseAction = array('admin.login.*');
    protected $margeArray = array();
    //系统变量缓存键值
    protected $system_variable= 'system_variable';
    //系统设置键名
    protected $system_setting= 'SEO';

    //执行父类构造方法
    public function __construct(){

        if( get_parent_class()!='' && method_exists ( get_parent_class(), '__construct' ) ){
            parent::__construct();
        }
        if(defined('C_ROUTE_M')){
            $this->margeArray = array(
                strtolower(C_ROUTE_M.'.'.C_ROUTE_C.'.'.C_ROUTE_A),
                strtolower(C_ROUTE_M.'.'.C_ROUTE_C.'.*'),
                strtolower(C_ROUTE_M.'.'.C_ROUTE_C),
                strtolower(C_ROUTE_M.'.*'),
                strtolower(C_ROUTE_M),
                '*'
            );
        }else{
            $this->margeArray = array(
                strtolower(C_ROUTE_C.'.'.C_ROUTE_A),
                strtolower(C_ROUTE_C.'.*'),
                strtolower(C_ROUTE_C),
                '*'
            );
        }
        //设置系统变量
        $this->setSystemVar();
    }

    //判断管理员是否登录
    protected function adminIsLogin(){
        foreach($this->noLoginUseAction as $v )
            if(in_array($v,$this->margeArray)) return true;

        if ( xSession::get('admin')=='' ) {
            if(C_IS_AJAX){
                xOut::json(['message'=>'登录超时，请刷新页面重新登录','status'=>'reload']);
            }else{
                header ( 'location:' . U ( 'admin/login/init') );
            }
            exit;
        }
    }


    //判断管理员是否有权限
    protected function adminHasPrivilege(){
        //如果是超级管理员也不需要检测权限
        if(intval(xSession::get('admin.group_id'))==1){
            return true;
        }
        //不需要判断登录的权限也不需要判断权限分配状态
        foreach($this->noLoginUseAction as $k => $v )
            if(in_array($v,$this->margeArray))return true;

        defined('C_DATA') OR define('C_DATA',strtolower(xInput::get('data','')));
        //检测权限分配状态
        if(defined('C_ROUTE_M')){
            $where = ['module'=>strtolower(C_ROUTE_M),'controller'=>strtolower(C_ROUTE_C),'action'=>strtolower(C_ROUTE_A),'data'=>C_DATA];
        }else{
            $where = ['module'=>'','controller'=>strtolower(C_ROUTE_C),'action'=>strtolower(C_ROUTE_A),'data'=>C_DATA];
        }

        $AdminGroupPrivData = M('admin_group_priv_data');
        $AdminPrivilege = $AdminGroupPrivData->where($where)->getOne ();
        if (count($AdminPrivilege)<=0){
            if(C_IS_AJAX){
                xOut::json(['message'=>'系统没有该权限','status'=>'error']);
            }else{
                $this->showMessage ('系统没有该权限');
            }
            exit;
        }
        //自由权限
        if ($AdminPrivilege['type']==1) {
            return true;
        }
        $where = ['group_id'=>xSession::get('admin.group_id'),'priv_id'=>$AdminPrivilege['priv_id']];
        $AdminGroupPriv = M('admin_group_priv');
        $count = $AdminGroupPriv->where ($where)->count();
        if ($count<=0) {
            if(C_IS_AJAX){
                xOut::json(['message'=>'你没有权限','status'=>'error']);
            }else{
                $this->showMessage ('你没有权限');
            }
            exit;
        }
        return true;
    }

    //通用排序方法
    protected function listorder($model) {
        $listorder = xInput::post('listorder');
        if (is_array ( $listorder ) && count ( $listorder ) > 0) {
            $model_key = $model->getKey();
            foreach ( $listorder as $key => $val ) {
                $model->clearWhere()->where(array($model_key=>$key))->update(array('listorder'=>$val));
            }
            return true;
        }
        return false;
    }

    //设置系统变量
    protected function setSystemVar() {
        if(xCache::get($this->system_variable)){
            $this->assign($this->system_variable, xCache::get($this->system_variable));
        }else{
            $systemVar = M('system_var')->where('isshow=1')->getAll();
            $systemVarTemp = [];
            foreach($systemVar as $k => $v ){
                $systemVarTemp[$v['cfg_key']] = $v['cfg_value'];
            }
            xCache::set($this->system_variable, $systemVarTemp);
            $this->assign($this->system_variable, $systemVarTemp);
        }
        //系统全局设置配置
        $system = M('system_cfg')->field('value')->where('cfgkey="system"')->getOne();
        if($system['value']!=''){
            $system['value'] = string2array($system['value']);
        }else{
            $system['value'] = array();
        }
        $this->assign($this->system_setting, $system['value']);
    }
}