<?php
// +--------------------------------------------------------------------------------------
// + AcPHP
// +--------------------------------------------------------------------------------------
// + 版权所有 2015年11月8日 贵州天岛在线科技有限公司，并保留所有权利。
// + 网站地址: http://www.acphp.com
// +--------------------------------------------------------------------------------------
// + 这不是一个自由软件！您只能在遵守授权协议前提下对程序代码进行修改和使用；不允许对程序代码以任何形式任何目的的再发布。
// + 授权协议：http://www.acphp.com/license.html
// +--------------------------------------------------------------------------------------
// + Author: AcPHP http://www.jiangzg.com/ <2992265870@qq.com/jiangzg>
// + Release Date: 2015年11月8日 上午12:08:32
// +--------------------------------------------------------------------------------------

// +--------------------------------------------------------------------------------------
// + 错误信息专属配置项
// +--------------------------------------------------------------------------------------
return array (
		'DEFAULT' => array (
				'log_time_format'   =>  ' c ',
				'log_file_size'     =>  10240000,
				'log_path'          =>  C_CACHE_PATH.'Cache_log'.C_DIR_FIX,
				// 是否开启错误日志
				'LOG_RECORD' => 1,
				//日志处理类型
				'SAVE_TYPE' => 'db',
				
				'TRACE_MAX_RECORD'=>5,
				// 致命错误显示模板
				'ERROR_TPL' => C_SYS_TPL_PATH . 'debug_info.tpl'
		) 
);
