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
return array (
		// +----------------------------------------------------------------------------------
		// + 写数据库默认配置
		// +----------------------------------------------------------------------------------
		'DEFAULT' => array (
				// 表前缀
				'TABLE_PREFIX' => 'ac_',
				// 数据连接类型
				'DB_TYPE' => 'PDO',
				// 主机地址
				//'DB_HOST' => '',
				// 账号
				//'DB_USER' => '',
				// 密码
				//'DB_PASSWORD' => '',
				// 目标数据库
				//'DB_SELECT' => '',
				// 数据库应用编码
				'CHARSET' => 'utf8' ,
				'db_port' => 3306,
				//读服务器
				'readserver'=>'readserver',
		),
		// +----------------------------------------------------------------------------------
		// + 读数据库默认配置
		// +----------------------------------------------------------------------------------
		'READSERVER' => array (
				// 数据连接类型
				'DB_TYPE' => 'MySql',
				// 主机地址
				//'DB_HOST' => '',
				// 账号
				//'DB_USER' => '',
				// 密码
				//'DB_PASSWORD' => '',
				// 目标数据库
				//'DB_SELECT' => '',
				// 数据库应用编码
				'CHARSET' => 'utf8' ,
				'db_port' => 3306
		) 
);























