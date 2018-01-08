<?php
class Common extends Action{

    public function __construct(){

        if( get_parent_class()!='' && method_exists ( get_parent_class(), '__construct' ) ){
            parent::__construct();
        }

        define ( '__ADMIN_RES_PATH__', '/AcPHP/AcExam/Public/Static/Admin/' );
        define ( '__PC_RES_PATH__', '/AcPHP/AcExam/Public/Static/Pc/develope/' );
        define ( '__MB_RES_PATH__', '/AcPHP/AcExam/Public/Static/Mb/develope/' );
    }

    protected function vOrder($orderno){
        $orderno = preg_replace('/[^\d]/i','',$orderno);
        if(strlen($orderno)!=22){
            return false;
        }
        return true;
    }

    protected function radomOrderNo(){
        $rand = rand(0,99999999);
        $rand = str_pad($rand,8,0,STR_PAD_LEFT);
        return date('YmdHis',time()).$rand;
    }
}
