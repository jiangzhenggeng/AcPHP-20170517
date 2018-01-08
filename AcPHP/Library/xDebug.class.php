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
// + Release Date: 2015年11月4日 下午11:09:52
// +--------------------------------------------------------------------------------------
defined('C_CA') or exit('Server error does not pass validation test.');

// +--------------------------------------------------------------------------------------
// + xDebug调试窗口
// +--------------------------------------------------------------------------------------
class xDebug{
	const LABEL_NOTIC	='NOTIC';
	const LABEL_MEMORY	='内存';
	const LABEL_SQL		='SQL';
	const LABEL_FILE	='文件';
	const LABEL_RUNTIME	='环境';
	
	private static $LABEL_NOTIC_KEY		=0;
	private static $LABEL_MEMORY_KEY	=1;
	private static $LABEL_SQL_KEY		=2;
	private static $LABEL_FILE_KEY		=3;
	private static $LABEL_RUNTIME_KEY	=4;
	/**
	 * 添加和获取页面Trace记录
	 * @param string $value 变量
	 * @param string $label 标签
	 * @param string $level 日志级别(或者页面Trace的选项卡)
	 * @param boolean $record 是否记录日志
	 * @return void|array
	 */
	static public function trace($value='[get]',$label=self::LABEL_NOTIC){
		static $_trace =  array();
        if('[get]' === $value){ // 获取trace信息
            return $_trace;
        }else{
			$_trace [$label] [] = $value;
        }
	}
}




























