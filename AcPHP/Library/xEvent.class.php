<?php
// +--------------------------------------------------------------------------------------
// + AcPHP
// +--------------------------------------------------------------------------------------
// + 版权所有 2015年11月4日 贵州天岛在线科技有限公司，并保留所有权利。
// + 网站地址: http://www.acphp.com
// +--------------------------------------------------------------------------------------
// + 这不是一个自由软件！您只能在遵守授权协议前提下对程序代码进行修改和使用；不允许对程序代码以任何形式任何目的的再发布。
// + 授权协议：http://www.acphp.com/license.html
// +--------------------------------------------------------------------------------------
// + Author: AcPHP  http://www.jiangzg.com/ <2992265870@qq.com/jiangzg>
// + Release Date: 2015-11-13 下午5:58:31
// +--------------------------------------------------------------------------------------
defined('C_CA') or exit('Server error does not pass validation test.');

class xEvent{

    protected static $listens = array();

    public static function listen($event, $callback, $once = false){
        if( !in_array(gettype($callback),array('string','object'))){
            E('事件绑定出错!');
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