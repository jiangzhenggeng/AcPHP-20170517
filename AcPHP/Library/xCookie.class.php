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
// + Release Date: 2015年11月8日 下午12:17:23
// +--------------------------------------------------------------------------------------

class xCookie{

	/**
	 * 设置 cookie
	 * 
	 * @param string $var
	 *        	变量名
	 * @param string $value
	 *        	变量值
	 * @param int $time
	 *        	过期时间
	 */
	public static function set($var, $value = '', $option = array('time'=>0,'path'=>'/') ) {
		$config = App::getConfig('Cookie','all');
		$time = intval($option['time']) > 0 ? intval($option['time']) : ($value == '' ? time() - 3600 : null);
		$s = $_SERVER ['servier_port'] == '443' ? 1 : 0;
		$var = $config['cookie_pre'] . $var;
		$_value = serialize($value);
		$_COOKIE [$var] = $value;
		$config[ 'cookie_dome' ] = false===strpos($config[ 'cookie_dome' ],'.')?'':$config[ 'cookie_dome' ];
		setcookie ( $var, $config['cookie_pre'].xEncryption::encrypt( $_value ), $time, $config[ 'cookie_path' ], $config[ 'cookie_dome' ] ,$s);
	}
	
	/**
	 * 获取通过 set_cookie 设置的 cookie 变量
	 * 
	 * @param string $var
	 *        	变量名
	 * @param string $default
	 *        	默认值
	 * @return mixed 成功则返回cookie 值，否则返回 false
	 */
	public static function get($var, $default = '') {
		$config = App::getConfig('Cookie','all');
		$var = $config['cookie_pre'] . $var;
		if( isset($_COOKIE [$var]) && is_string($_COOKIE [$var]) ){
			$l = strlen($config['cookie_pre']);
			$v = substr($_COOKIE [$var],0,$l);
			if( $v==$config['cookie_pre'] ){
				$value = unserialize( isset ( $_COOKIE [$var] ) ? xEncryption::decrypt( substr($_COOKIE [$var],$l) ) : $default );
			}
		}else {
			$value =  isset ( $_COOKIE [$var] ) ? $_COOKIE [$var] : $default;
		}
		$value = ac_safe_replace( $value );
		return $value;
	}
}


