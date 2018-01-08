<?php

class xLog{	

	static $_xlog_array = array();
	//static $_write_number = 0;
	/**
	 * 日志写入接口
	 * @access public
	 * @param string $log 日志信息
	 * @param string $destination  写入目标
	 * @return void
	 */
	static public function save() {
		$config = App::getConfig('Log', 'all');
		$now = date($config['log_time_format']);
		//self::$_write_number++;
		$destination = $config['log_path'].date('y_m_d').'.log';
		// 自动创建日志目录
		dir_create($config['log_path']);
		//检测日志文件大小，超过配置大小则备份日志文件重新生成
		if(is_file($destination) && floor($config['log_file_size']) <= filesize($destination) ){
			$file_log_name = dirname($destination).'/'.time().'-'.basename($destination);
			rename($destination,$file_log_name);
			@chmod($file_log_name, 0777);
		}
		$string = "[{$now}] [REMOTE_ADDR]".$_SERVER['REMOTE_ADDR'].' [URL] '.$_SERVER['REQUEST_URI']."\r\n";
		foreach (self::$_xlog_array as $val){
			$string .= $val['message'].PHP_EOL;
		}
		error_log($string.PHP_EOL, 3, $destination);
	}
	
	//记录日志
	static public function record($log,$level=''){
		self::$_xlog_array[count(self::$_xlog_array)]['message'] = $log;
	}
}