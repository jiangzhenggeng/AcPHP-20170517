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
// + 系统临时缓存类
// +--------------------------------------------------------------------------------------
class xTempCache {
	
	public static $storage = array();

	// +----------------------------------------------------------------------------------
	// + 设置缓存
	// +----------------------------------------------------------------------------------
	public static function set($key, $data ){
        self::$storage[md5(__CLASS__.$key)] = $data;
		return true;
	}
	// +----------------------------------------------------------------------------------
	// + 获取缓存
	// +----------------------------------------------------------------------------------
	public static function get($key,$default=''){
        $key = md5(__CLASS__.$key);
		return isset(self::$storage[$key])?self::$storage[$key]:$default;
	}
	// +----------------------------------------------------------------------------------
	// + 删除缓存
	// +----------------------------------------------------------------------------------
	public static function delete($key){
        $key = md5(__CLASS__.$key);
        self::$storage[$key] = array();
	}
	// +----------------------------------------------------------------------------------
	// + 清空缓存
	// +----------------------------------------------------------------------------------
	public static function clear(){
        self::$storage = array();
	}
}




















