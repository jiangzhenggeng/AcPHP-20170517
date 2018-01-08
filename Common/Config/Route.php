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
// + Release Date: 2015年11月8日 上午12:13:58
// +--------------------------------------------------------------------------------------

if(ac_is_mobile()){
    return array (
        'DEFAULT' => array (
            'M' => 'Mobile',
            'C' => 'Index',
            'A' => 'init',
            'm_alias'=>'module',
            'c_alias'=>'controller',
            'a_alias'=>'action',
            'DATA' => array (
                'POST' => array (
                    //'catid' => 1
                ),
                'GET' => array (
                    //'contentid' => 1
                )
            ),

            // 是否开启URL重写，REWRITE
            'URL_REWRITE' => false,
            // URL编码模式1为兼容模式，2为PATHINFO模式
            'URL_MODEL' => 2,
            // 伪静态后缀
            'URL_SUFFIX' => '.jsp',
            // url参数分隔标识符
            'URL_FIX' => '/',
            // 是否启用URL路由配置
            'URL_ROUTE' => FALSE,
            'URL_ROUTE_CONFIG' => array ()
        )
    );
}else{
    return array (
        'DEFAULT' => array (
            'M' => 'Pcs',
            'C' => 'Index',
            'A' => 'init',
            'm_alias'=>'module',
            'c_alias'=>'controller',
            'a_alias'=>'action',
            'DATA' => array (
                'POST' => array (
                    //'catid' => 1
                ),
                'GET' => array (
                    //'contentid' => 1
                )
            ),

            // 是否开启URL重写，REWRITE
            'URL_REWRITE' => false,
            // URL编码模式1为兼容模式，2为PATHINFO模式
            'URL_MODEL' => 2,
            // 伪静态后缀
            'URL_SUFFIX' => '.jsp',
            // url参数分隔标识符
            'URL_FIX' => '/',
            // 是否启用URL路由配置
            'URL_ROUTE' => FALSE,
            'URL_ROUTE_CONFIG' => array ()
        )
    );

}















