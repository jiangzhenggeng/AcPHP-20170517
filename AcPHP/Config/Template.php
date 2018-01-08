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
// + Release Date: 2015年11月8日 上午12:06:14
// +--------------------------------------------------------------------------------------

// +--------------------------------------------------------------------------------------
// + 模板相关配置项
// +--------------------------------------------------------------------------------------
return array (
		
		'DEFAULT' => array (
				// 模板路径
				'TEMPLATE_DIR' => C_VIEWS_PATH . App::getConfig ( 'View', 'path' ) . C_DIR_FIX . (defined('C_ROUTE_M')?C_ROUTE_M:C_ROUTE_C) . C_DIR_FIX,
				// 编译路径
				'COMPLIES_DIR' => C_CACHE_PATH . 'Cache_tpl' . C_DIR_FIX,
				// 是否开启缓存
				'CACHEING' => false,
				// 缓存时间
				'CACHE_TIME' => 3600,
				// 缓存的路径
				'CACHE_DIR' => C_CACHE_PATH . 'Cache_tpl' . C_DIR_FIX,
				// 是否开启调试模式，开启调试模式，每次运行都进行模板编译，无论模板是否修改过
				'DEBUG' => true,//App::getConfig ( 'Config', 'debug' ),
				// 标签左边界定界符
				'LEFT_FIX' => '{',
				// 标签右边界定界符
				'RIGHT_FIX' => '}',
				'TPL_HEADER_DATA' => '<?php defined(\'C_APP_START\') AND C_APP_START===TRUE?TRUE:exit(\'Can\\\'t Find The Resources！\');?>' . PHP_EOL,
				// 如果没有配置，插件路径默认 ./plus
				'_PLUS_DIR' => C_SYS_PLUG_PATH.'TemplatePlus',
				// 自定义解析标签插件如：array('plusname')
				// 或者array('plusname'=>array('plusmethod1','plusmethod2',...))
				'_PLUS' => array () 
		) 
);