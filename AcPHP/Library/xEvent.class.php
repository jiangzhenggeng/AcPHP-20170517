<?php
// +--------------------------------------------------------------------------------------
// + AcPHP
// +--------------------------------------------------------------------------------------
// + ��Ȩ���� 2015��11��4�� �����쵺���߿Ƽ����޹�˾������������Ȩ����
// + ��վ��ַ: http://www.acphp.com
// +--------------------------------------------------------------------------------------
// + �ⲻ��һ�������������ֻ����������ȨЭ��ǰ���¶Գ����������޸ĺ�ʹ�ã�������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
// + ��ȨЭ�飺http://www.acphp.com/license.html
// +--------------------------------------------------------------------------------------
// + Author: AcPHP  http://www.jiangzg.com/ <2992265870@qq.com/jiangzg>
// + Release Date: 2015-11-13 ����5:58:31
// +--------------------------------------------------------------------------------------
defined('C_CA') or exit('Server error does not pass validation test.');

class xEvent{

    protected static $listens = array();

    public static function listen($event, $callback, $once = false){
        if( !in_array(gettype($callback),array('string','object'))){
            E('�¼��󶨳���!');
            return false;
        }elseif (!is_callable($callback)){
            return false;
        }
        self::$listens[$event][] = array('callback' => $callback, 'once' => $once);
        return true;
    }

    public static function one($event, $callback){
        return self::listen($event, $callback, true);
    }

    public static function remove($event, $index = null){
        if(!$event){
            self::$listens = array();
        }elseif (is_null($index)){
            unset(self::$listens[$event]);
        }else{
            unset(self::$listens[$event][$index]);
        }
    }

    public static function trigger(){
        if (!func_num_args()) return;
        $args = func_get_args();
        $event = isset($args[0])?$args[0]:array_shift($args);
        if (!isset(self::$listens[$event])) return false;
        foreach ((array)self::$listens[$event] as $index => $listen) {
            $callback = $listen['callback'];
            $listen['once'] && self::remove($event, $index);
            call_user_func_array($callback, $args);
        }
    }
}