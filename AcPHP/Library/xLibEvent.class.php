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

class xLibEvent extends xEvent{

    public static function listenEvents(){
        $libEventList = & App::getConfig('event');
        foreach ($libEventList as $event => $callbackList ){
            foreach ($callbackList as $callback){
                parent::listen($event,$callback);
            }
        }
        return true;
    }
}