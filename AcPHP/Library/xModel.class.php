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

class xModel {	
	protected $db = array();

    protected $database_config = array();

    protected $table_prefix = '';
    protected $readserver_method = array('getAll','getOne','count','getTotal');
	
	//当前模型表
	protected $modelTable = NULL;
	protected $clusterMethod = array();
	protected $clusterMethodInit = array(
			'field'=>'*',
			'table'=>NULL,
			'where'=>NULL,
			'clearWhere'=>NULL,
			'order'=>NULL,
			'limit'=>NULL,
			'leftJoin'=>NULL,
			'rightJoin'=>NULL,
			'join'=>NULL,
			'group'=>NULL
		);
	
	/**
	 * 构造函数，获取数据库操作句柄
	 */
	public function __construct($database = NULL ) {
//		$this->database_config['db_1'] = & App::getConfig('database','all',NULL,$database);
//		$this->db['db_1'] = xDB::db($this->database_config['db_1']);
//
//        $this->table_prefix = xTool::tableFix('',$this->database_config['db_1']['table_prefix']);
//
//        if(isset($this->database_config['db_1']['readserver'])){
//            $this->database_config['db_2'] = & App::getConfig('database','all',NULL,$this->database_config['db_1']['readserver']);
//            $this->db['db_2'] = xDB::db($this->database_config['db_2']);
//        }
        $this->clearWhere();

        $this->database_config = & App::getConfig('database','all',NULL,$database);
        $this->db = xDB::db($this->database_config);

        $this->table_prefix = xTool::tableFix('',$this->database_config['table_prefix']);

	}
	
	//__call实现数据获取
	public function __call($method, $parma) {

//        $is_call = method_exists ( $this->db['db_1'], $method ) && is_callable ( array ($this->db['db_1'],$method));
//	    //查询数据库对象
//        if($is_call && isset($this->db['db_2']) && in_array($method,$this->readserver_method)){
//            $select_db = 'db_2';
//        }
//        //写或者没有开启读写分离
//        elseif( $is_call ) {
//            $select_db = 'db_1';
//		} else {
//			E('Controller does not exist.[009]'.$method,E_USER_ERROR);
//		}

        $this->db->clusterWhere = $this->clusterMethod;

        //是否清除以前的条件
        $clear_where = true;
        if(in_array($method,array('count','getTotal','getAll','getOne'))){
            $clear_where = isset($parma[0])?$parma[0]:true;
        }

        if($method!='getSql')$this->clearWhere ($clear_where);

        $config  = & App::getConfig('Config','all');
        if($config['debug']){
            //记录运行sql语句的开始时间
            $sql_stat_time = microtime(TRUE);
        }
        //获取当前将要执行的sql语句
        $sql_data = call_user_func_array(array($this->db,$method), $parma);
        //开启调试模式，记录SQL
        if($method!='getSql' && $config['debug']){
            xDebug::trace( (number_format((microtime(TRUE)-$sql_stat_time)*1000,2)).']　'.$this->db->getSql(),xDebug::LABEL_SQL);
        }
        return $sql_data;
	}
	
	
	
	// +----------------------------------------------------------------------------------
	// + 格式化字段
	// +----------------------------------------------------------------------------------
	public function field($field) {
		$field = trim ( $field );
		if ($field == '' || $field == '*') {
			$this->clusterMethod[__FUNCTION__] = '*';
			return $this;
		}
		$field = explode (',', $field );
        foreach ( $field as $k => $v ) {
            $v = trim ( $v );
            if($v=='')continue;

            $field [$k] = $v;
        }
		$field = implode (',',$field );
		$this->clusterMethod[__FUNCTION__] = $this->clusterMethod[__FUNCTION__] == '*' ? $field : $this->clusterMethod[__FUNCTION__] . ',' . $field;
		return $this;
	}
	
	// +----------------------------------------------------------------------------------
	// + 设置操作目标表
	// +----------------------------------------------------------------------------------
	public function table($table,$table_prefix='') {
		$table = trim ( $table );
		if ($table == '') {
			return $this;
		}
        $table_prefix = trim($table_prefix);
		if($table_prefix==''){
            $table = $this->table_prefix.$table;
        }else{
            $table = $table_prefix.$table;
        }

		if($this->modelTable===NULL){
			$this->modelTable = $table;
		}
        $this->clusterMethod[__FUNCTION__] = $table;
		$tableArray = explode(',',$table);
		foreach($tableArray as $k => $v ){
			$tempTable = explode(' ',trim($v));
			$this->tableArray[] = str_replace('`','',$tempTable[0]);
		}
		return $this;
	}

	// +----------------------------------------------------------------------------------
	// + 条件查询预先设置条件
	// + @param string $where ['admin'=>['jiangzg','<>'],'admin1'=>['jiangzg','!=','OR']]
	// + @param string $condition OR或者AND，默认AND
	// +----------------------------------------------------------------------------------
	public function where($where, $condition = 'AND' ) {
		
		if ($where===NULL) { // 清空所有条件
			unset($this->clusterMethod[__FUNCTION__]);
			return $this;
		}
		
		$this->clusterMethod[__FUNCTION__] = 
			trim ( $this->clusterMethod[__FUNCTION__] ) == '' ? 
				('WHERE ' . xTool::toolWhere ( $where ) ) : 
					($this->clusterMethod[__FUNCTION__] . ' ' . xTool::toolWhere ( $where, $condition ) );
		return $this;
	}
	
	// +----------------------------------------------------------------------------------
	// + 排序条件设置
	// +----------------------------------------------------------------------------------
	public function order($order) {
		if ($order == '' || is_null($order) ){
			unset($this->clusterMethod[__FUNCTION__]);
		}elseif($this->clusterMethod[__FUNCTION__]!=''){
			$this->clusterMethod[__FUNCTION__] .= ','.$order;
		}else{
			$this->clusterMethod[__FUNCTION__] = 'ORDER BY '.$order;
		}
		return $this;
	}
	
	// +----------------------------------------------------------------------------------
	// + 分组条件设置
	// +----------------------------------------------------------------------------------
	public function group($group) {
		if ($group == '' || is_null($group) ){
			unset($this->clusterMethod[__FUNCTION__]);
		}elseif($this->clusterMethod[__FUNCTION__]!=''){
			$this->clusterMethod[__FUNCTION__] .= ','.$group;
		}else{
			$this->clusterMethod[__FUNCTION__] = 'GROUP BY '.$group;
		}
		return $this;
	}
	
	// +----------------------------------------------------------------------------------
	// + 设置获取记录条数
	// +----------------------------------------------------------------------------------
	public function limit($limit) {
		if ($limit == '' || is_null($limit) )
			unset($this->clusterMethod[__FUNCTION__]);
		else
			$this->clusterMethod[__FUNCTION__] = 'LIMIT '.$limit;
		return $this;
	}
	
	// +----------------------------------------------------------------------------------
	// + 清空之前留下的条件
	// +----------------------------------------------------------------------------------
	public function clearWhere($clear_where=true) {
		if($clear_where){
			$this->clusterMethod = $this->clusterMethodInit;
			$this->clusterMethod['field'] = '*';
			$this->clusterMethod['table'] = $this->modelTable;
			$this->tableArray = array($this->modelTable);
		}
		return $this;
	}
	
	/**
	 * 左连接查询
	 * @param unknown $table1        	
	 * @param unknown $on_where        	
	 * @param string $table2        	
	 * @param string $type        	
	 * @return Model
	 */
	public function leftJoin($table1, $on_where, $table_prefix='') {
		return $this->join ( $table1, $on_where, $table_prefix,'LEFT' );
	}
	
	/**
	 * 右连接查询
	 * @param unknown $table1        	
	 * @param unknown $on_where        	
	 * @param string $table2        	
	 * @param string $type        	
	 * @return Model
	 */
	public function rightJoin($table1, $on_where, $table_prefix='') {
		return $this->join ( $table1, $on_where, $table_prefix ,'RIGHT');
	}
	
	// +----------------------------------------------------------------------------------
	// + 右连接查询
	// + @param unknown $table1        	
	// + @param unknown $on_where        	
	// + @param string $table2        	
	// + @param string $type        	
	// + @return Model
	// +----------------------------------------------------------------------------------
	public function join($table1, $on_where,$table_prefix='', $type = 'LEFT') {
        $table_prefix = trim($table_prefix);
        if($table_prefix==''){
            $table1 = $this->table_prefix.$table1;
        }else{
            $table1 = $table_prefix.$table1;
        }
		if ($this->clusterMethod[__FUNCTION__] != '') {
			$this->clusterMethod[__FUNCTION__] = $this->clusterMethod[__FUNCTION__] . ' ' . $type . ' JOIN ' . $table1 . ' ON ' . $on_where;
		} else {
			$this->clusterMethod[__FUNCTION__] = $type . ' JOIN ' . $table1 . ' ON ' . $on_where;
		}
		$tableArray = explode(',',$table1);
		foreach($tableArray as $k => $v ){
			$tempTable = explode(' ',trim($v));
			$this->tableArray[] = str_replace('`','',$tempTable[0]);
		}
		return $this;
	}
}
