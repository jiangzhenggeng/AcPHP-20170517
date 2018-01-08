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
// + Author: AcPHP  http://www.jiangzg.com/ <2992265870@qq.com/jiangzg>
// + Release Date: 2015年11月8日 上午11:16:37
// +--------------------------------------------------------------------------------------

// +--------------------------------------------------------------------------------------
// + 缓存驱动类
// +--------------------------------------------------------------------------------------
class xCache {
	
	public static $storage = null;
	
	// +----------------------------------------------------------------------------------
	// + 配置缓存
	// +----------------------------------------------------------------------------------
	private static function _getCacheInstance(){
		$cachetype =& App::getConfig('Cache','cachetype','File');
		if(self::$storage!==null) $cachetype['cachetype'] = self::$storage;
		
		if($cachetype===''){
			$cachetype = 'File';
		}
		$class = 'Cache'.ucfirst($cachetype);
		App::loadClass('CacheAbstract',C_SYS_LIBRARY_PATH.'Cache/');
		App::loadClass($class,C_SYS_LIBRARY_PATH.'Cache/Drive/');
		return $class::getInstance();
	}
	
	// +----------------------------------------------------------------------------------
	// + 配置缓存
	// +----------------------------------------------------------------------------------
	private static function _config($key=''){
		$_config =& App::getConfig('Cache',$key);
		return $_config;
	}
	// +----------------------------------------------------------------------------------
	// + 设置缓存
	// +----------------------------------------------------------------------------------
	public static function set($key, $data ,$ttl=NULL){
		if($data=='' || is_null($data)){
			return true;
		}
		if(is_array($data) || is_object($data)){
			$data = 'string2array'.array2string($data);
		}else{
			$data = '-----------'.$data;
		}
		$key = md5(self::_config('Cache','cachefix').$key);
		if($ttl===NULL){
			$cachetime = self::_config('Cache','cachetime');
			//获取缓存时间
			$ttl = $cachetime?$cachetime:3600;
		}
		return self::_getCacheInstance()->set($key, $data ,$ttl);
	}
	// +----------------------------------------------------------------------------------
	// + 获取缓存
	// +----------------------------------------------------------------------------------
	public static function get($key,$default=''){
		$data = self::_getCacheInstance()->get(md5(self::_config('Cache','cachefix').$key),$default);
		$fixFunc = substr($data,0,11);
		$data = substr($data,11);
		if($fixFunc==='unserialize'){
			return unserialize($data);
		}
		return $data;
	}
	// +----------------------------------------------------------------------------------
	// + 删除缓存
	// +----------------------------------------------------------------------------------
	public static function delete($key){
		return self::_getCacheInstance()->delete(md5(self::_config('Cache','cachefix').$key));
	}
	// +----------------------------------------------------------------------------------
	// + 清空缓存
	// +----------------------------------------------------------------------------------
	public static function clear(){
		return self::_getCacheInstance()->clear();
	}
}




















