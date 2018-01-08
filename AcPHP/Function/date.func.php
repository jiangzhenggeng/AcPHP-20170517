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
// + Release Date: 2015年11月8日 下午10:54:48
// +--------------------------------------------------------------------------------------
defined('C_CA') or exit('Server error does not pass validation test.');

// +--------------------------------------------------------------------------------------
// + 自定义时间函数
// +--------------------------------------------------------------------------------------
function ac_date($date=0,$format='Y-m-d',$def=''){
	$date = intval($date)<=0?time():$date;
	if($date==='')return $def;
	return date($format,$date);;
}


// +--------------------------------------------------------------------------------------
// + 时间间隔格式化
// +--------------------------------------------------------------------------------------
function ac_format_date($time,$next_fix='') {
	$t = time () - $time;
	$f = array (
			'31536000' => '年',
			'2592000' => '个月',
			'604800' => '星期',
			'86400' => '天',
			'3600' => '小时',
			'60' => '分钟',
			'1' => '秒' 
	);
	foreach ( $f as $k => $v ) {
		if (0 != $c = floor ( $t / ( int ) $k )) {
			return $c . $v . $next_fix;
		}
	}
}

function ac_friendly_date($sTime,$type = 'mohu',$alt = 'false') {
	if (!$sTime)
		return '';
	//sTime=源时间，cTime=当前时间，dTime=时间差
	$cTime      =   time();
	$dTime      =   $cTime - $sTime;
	$dDay       =   intval(date("z",$cTime)) - intval(date("z",$sTime));
	//$dDay     =   intval($dTime/3600/24);
	$dYear      =   intval(date("Y",$cTime)) - intval(date("Y",$sTime));
	//normal：n秒前，n分钟前，n小时前，日期

	if($type=='normal'){
		if( $dTime < 60 ){
			if($dTime < 10){
				return '刚刚';    //by yangjs
			}else{
				return intval(floor($dTime / 10) * 10)."秒前";
			}
		}elseif( $dTime < 3600 ){
			return intval($dTime/60)."分钟前";
			//今天的数据.年份相同.日期相同.
		}elseif( $dYear==0 && $dDay == 0  ){
			//return intval($dTime/3600)."小时前";
			return '今天'.date('H:i',$sTime);
		}elseif($dYear==0){
			return date("m月d日 H:i",$sTime);
		}else{
			return date("Y-m-d H:i",$sTime);
		}
	}elseif($type=='mohu'){
		/*if( $dTime < 60 ){
            return $dTime."秒前";
        }elseif( $dTime < 3600 ){
            return intval($dTime/60)."分钟前";
        }elseif( $dTime >= 3600 && $dDay == 0  ){
            return intval($dTime/3600)."小时前";
        }elseif( $dDay > 0 && $dDay<=7 ){
            return intval($dDay)."天前";
        }elseif( $dDay > 7 &&  $dDay <= 30 ){
            return intval($dDay/7) . '周前';
        }elseif( $dDay > 30 ){
            return intval($dDay/30) . '个月前';
        }*/

		if( $dTime < 60 ){
			return $dTime."秒前";
		}elseif( $dTime < 3600 ){
			return intval($dTime/60)."分钟前";
		}elseif( $dTime >= 3600 && $dDay == 0  ){
			return intval($dTime/3600)."小时前";
		}elseif( $dDay > 0 && $dDay<=7 ){
			return intval($dDay)."天前";
		}elseif( $dDay > 7 &&  $dDay <= 30 ){
			return intval($dDay/7) . '周前';
		}elseif( $dDay > 30 && $dYear==0){
			return intval($dDay/30) . '个月前';
		}else{
			return date("Y/m/d",$sTime);
		}

		//full: Y-m-d , H:i:s
	}elseif($type=='full'){
		return date("Y-m-d , H:i:s",$sTime);
	}elseif($type=='ymd'){
		return date("Y-m-d",$sTime);
	}else{
		if( $dTime < 60 ){
			return $dTime."秒前";
		}elseif( $dTime < 3600 ){
			return intval($dTime/60)."分钟前";
		}elseif( $dTime >= 3600 && $dDay == 0  ){
			return intval($dTime/3600)."小时前";
		}elseif($dYear==0){
			return date("Y-m-d H:i:s",$sTime);
		}else{
			return date("Y-m-d H:i:s",$sTime);
		}
	}
}


















