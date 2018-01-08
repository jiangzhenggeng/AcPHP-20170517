<?php
// +--------------------------------------------------------------------------------------
// + AcPHP
// +--------------------------------------------------------------------------------------
// + 版权所有 2015年11月15日 贵州天岛在线科技有限公司，并保留所有权利。
// + 网站地址: http://www.acphp.com
// +--------------------------------------------------------------------------------------
// + 这不是一个自由软件！您只能在遵守授权协议前提下对程序代码进行修改和使用；不允许对程序代码以任何形式任何目的的再发布。
// + 授权协议：http://www.acphp.com/license.html
// +--------------------------------------------------------------------------------------
// + Author: AcPHP  http://www.jiangzg.com/ <2992265870@qq.com/jiangzg>
// + Release Date: 2015年11月15日 下午8:37:15
// +--------------------------------------------------------------------------------------
defined('C_CA') or exit('Server error does not pass validation test.');

final class DBPdo extends DBAbstract {
	protected $database_config = array();
    protected $sql = '';
    protected $lastqueryid = null;
	
	// +----------------------------------------------------------------------------------
	// + 数据库连接操作
	// + $database_config 数据库配置项
	// + return bool
	// +----------------------------------------------------------------------------------
	public function connect($database_config) {
		$this->database_config = $_config = $database_config;
		$dsn  = 'mysql:dbname='.$this->database_config["db_select"].';';
		$dsn .= 'host='.$this->database_config["db_host"].';';
		$dsn .= 'port='.$this->database_config["db_port"].';';
		try{
			$this->pdo = new PDO($dsn, $this->database_config["db_user"], $this->database_config["db_password"], array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES ".$this->database_config["charset"]));
			$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			$this->bConnected = true;
		}catch (PDOException $e){
			trigger_error($e->getMessage(),E_USER_ERROR);
			exit;
		}
		return true;
	}
	
	// +----------------------------------------------------------------------------------
	// + 释放查询资源
	// +----------------------------------------------------------------------------------
	private function _freeRsult() {
		if ($this->pdo) {
			$this->pdo = null;
		}
	}
	
	// +----------------------------------------------------------------------------------
	// + 执行SQL语句
	// +----------------------------------------------------------------------------------
	public function query($sql = '', $link_type = MYSQL_ASSOC) {
		$sql = trim ( $sql );
		$this->sql = $sql != '' ? $sql : $this->sql;
		if($this->sql == ''){
			trigger_error('没有sql语句！',E_USER_ERROR);
			exit;
		}
		$this->lastqueryid = mysql_query ( $this->sql, $this->pdo );
		if ($this->lastqueryid===false) {
			trigger_error(mysql_errno($this->pdo ).mysql_error($this->pdo ).',SQL:'.$this->sql,E_USER_ERROR);
			exit;
		}
		$fix = strtoupper ( substr ( $this->sql, 0, 6 ) );
		if (in_array ( $fix, array ('UPDATE','INSERT','DELETE','REPLAC' ) )) {
			return $this->getRows ();
		}
		$result = array ();
		while ( ! ! $row = mysql_fetch_array ( $this->lastqueryid, $link_type ) ) {
			$result [] = $row;
		}
		$this->_freeRsult ();
		return $result;
	}
	
	/**
	 * 获取最后一次添加记录的主键号
	 * @return int
	 */
	public function insertId() {
		return mysql_insert_id ( $this->pdo );
	}
	
	
	/**
	 * 获取执行SQL语句影响的行数
	 * @return number
	 */
	public function getRows() {
		if (is_resource ( $this->lastqueryid )) {
			return mysql_affected_rows ( $this->lastqueryid );
		}
		return mysql_affected_rows ();
	}
	
	/**
	 * 获取执行SQL语句影响的行数
	 * @return number
	 */
	public function close() {
		if (is_resource ( $this->lastqueryid )) {
			return mysql_close ( $this->lastqueryid );
		}
		return mysql_close ();
	}

}













