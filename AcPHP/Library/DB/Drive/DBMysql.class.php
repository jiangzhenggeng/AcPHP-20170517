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

final class DBMysql extends DBAbstract {
    protected $database_config = array();
    protected $curr_hander = null;
    protected $sql = '';
    protected $lastqueryid = null;
	// +----------------------------------------------------------------------------------
	// + 数据库连接操作
	// + $database_config 数据库配置项
	// + return bool
	// +----------------------------------------------------------------------------------
	public function connect($database_config) {
		$this->database_config = $_config = $database_config;
		try {
			$this->curr_hander = mysql_connect ( $_config ['db_host'], $_config ['db_user'], $_config ['db_password'] );
			if($this->curr_hander===false){
				E('数据库连接错误！[001]',E_USER_ERROR);
			}
			$select_db_result = mysql_select_db ( $_config ['db_select'], $this->curr_hander );
			if($select_db_result===false){
				E('数据库选择错误！[002]'.mysql_error($this->curr_hander).mysql_error($this->curr_hander),E_USER_ERROR);
			}
			mysql_query ( "SET NAMES '" . str_replace ( '-', '', $_config ['charset'] ) . "'", $this->curr_hander );
		} catch (Exception $e) {
			E('数据库连接出错！[003]',E_USER_ERROR);
		}
		return $this->curr_hander;
	}
	
	// +----------------------------------------------------------------------------------
	// + 释放查询资源
	// +----------------------------------------------------------------------------------
	private function _freeRsult() {
		if (is_resource ( $this->lastqueryid )) {
			mysql_free_result ( $this->lastqueryid );
			$this->lastqueryid = null;
		}
	}
	
	// +----------------------------------------------------------------------------------
	// + 执行SQL语句
	// +----------------------------------------------------------------------------------
	public function query($sql = '', $link_type = MYSQL_ASSOC) {
		$sql = trim ( $sql );
		$this->sql = $sql != '' ? $sql : $this->sql;
		if($this->sql == ''){
			E('没有sql语句！',E_USER_ERROR);
		}
		$this->lastqueryid = mysql_query ( $this->sql, $this->curr_hander );
		if ($this->lastqueryid===false) {
			trigger_error(mysql_errno($this->curr_hander ).mysql_error($this->curr_hander ).',SQL:'.$this->sql,E_USER_ERROR);
			exit;
		}
		$fix = strtoupper ( substr ( $this->sql, 0, 6 ) );
		if (in_array ( $fix, array ('UPDATE','INSERT','DELETE','REPLAC') )) {
			return $this->getRows ();
		}if (in_array ( $fix, array ('INSERT') )) {
			if($this->insertId())
				return $this->insertId();
			else
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
		return mysql_insert_id ( $this->curr_hander );
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





