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
// + Release Date: 2015年11月8日 上午1:09:25
// +--------------------------------------------------------------------------------------

class UeditorConfig{
	static public $config = null;
	
	static public function getConfig(){
		self::$config = array (
		  'imageActionName' => 'uploadimage',
		  'imageFieldName' => 'upfile',
		  'imageMaxSize' => 2048000000,
		  'imageAllowFiles' => 
		  array (
			0 => '.png',
			1 => '.jpg',
			2 => '.jpeg',
			3 => '.gif',
			4 => '.bmp',
		  ),
		  'imageCompressEnable' => false,
		  'imageCompressBorder' => 1600,
		  'imageInsertAlign' => 'none',
		  'imageUrlPrefix' => '',
		  'imagePathFormat' => __UPLOAD__.'image/{yyyy}{mm}{dd}/{time}{rand:6}',
		  'scrawlActionName' => 'uploadscrawl',
		  'scrawlFieldName' => 'upfile',
		  'scrawlPathFormat' => __UPLOAD__.'image/{yyyy}{mm}{dd}/{time}{rand:6}',
		  'scrawlMaxSize' => 2048000000,
		  'scrawlUrlPrefix' => '',
		  'scrawlInsertAlign' => 'none',
		  'snapscreenActionName' => 'uploadimage',
		  'snapscreenPathFormat' => __UPLOAD__.'image/{yyyy}{mm}{dd}/{time}{rand:6}',
		  'snapscreenUrlPrefix' => '',
		  'snapscreenInsertAlign' => 'none',
		  'catcherLocalDomain' => 
		  array (
			0 => '127.0.0.1',
			1 => 'localhost',
			2 => 'img.baidu.com',
		  ),
		  'catcherActionName' => 'catchimage',
		  'catcherFieldName' => 'source',
		  'catcherPathFormat' => __UPLOAD__.'image/{yyyy}{mm}{dd}/{time}{rand:6}',
		  'catcherUrlPrefix' => '',
		  'catcherMaxSize' => 2048000000,
		  'catcherAllowFiles' => 
		  array (
			0 => '.png',
			1 => '.jpg',
			2 => '.jpeg',
			3 => '.gif',
			4 => '.bmp',
		  ),
		  'videoActionName' => 'uploadvideo',
		  'videoFieldName' => 'upfile',
		  'videoPathFormat' => __UPLOAD__.'video/{yyyy}{mm}{dd}/{time}{rand:6}',
		  'videoUrlPrefix' => '',
		  'videoMaxSize' => 2048000000,
		  'videoAllowFiles' => 
		  array (
			0 => '.flv',
			1 => '.swf',
			2 => '.mkv',
			3 => '.avi',
			4 => '.rm',
			5 => '.rmvb',
			6 => '.mpeg',
			7 => '.mpg',
			8 => '.ogg',
			9 => '.ogv',
			10 => '.mov',
			11 => '.wmv',
			12 => '.mp4',
			13 => '.webm',
			14 => '.mp3',
			15 => '.wav',
			16 => '.mid',
		  ),
		  'fileActionName' => 'uploadfile',
		  'fileFieldName' => 'upfile',
		  'filePathFormat' => __UPLOAD__.'file/{yyyy}{mm}{dd}/{time}{rand:6}',
		  'fileUrlPrefix' => '',
		  'fileMaxSize' => 2048000000,
		  'fileAllowFiles' => 
		  array (
			0 => '.png',
			1 => '.jpg',
			2 => '.jpeg',
			3 => '.gif',
			4 => '.bmp',
			5 => '.flv',
			6 => '.swf',
			7 => '.mkv',
			8 => '.avi',
			9 => '.rm',
			10 => '.rmvb',
			11 => '.mpeg',
			12 => '.mpg',
			13 => '.ogg',
			14 => '.ogv',
			15 => '.mov',
			16 => '.wmv',
			17 => '.mp4',
			18 => '.webm',
			19 => '.mp3',
			20 => '.wav',
			21 => '.mid',
			22 => '.rar',
			23 => '.zip',
			24 => '.tar',
			25 => '.gz',
			26 => '.7z',
			27 => '.bz2',
			28 => '.cab',
			29 => '.iso',
			30 => '.doc',
			31 => '.docx',
			32 => '.xls',
			33 => '.xlsx',
			34 => '.ppt',
			35 => '.pptx',
			36 => '.pdf',
			37 => '.txt',
			38 => '.md',
			39 => '.xml',
		  ),
		  'imageManagerActionName' => 'listimage',
		  'imageManagerListPath' => __UPLOAD_PATH__.'image/',
		  'imageManagerListSize' => 20,
		  'imageManagerUrlPrefix' => '',
		  'imageManagerInsertAlign' => 'none',
		  'imageManagerAllowFiles' => 
		  array (
			0 => '.png',
			1 => '.jpg',
			2 => '.jpeg',
			3 => '.gif',
			4 => '.bmp',
		  ),
		  'fileManagerActionName' => 'listfile',
		  'fileManagerListPath' => __UPLOAD_PATH__.'file/',
		  'fileManagerUrlPrefix' => '',
		  'fileManagerListSize' => 20,
		  'fileManagerAllowFiles' => 
		  array (
			0 => '.png',
			1 => '.jpg',
			2 => '.jpeg',
			3 => '.gif',
			4 => '.bmp',
			5 => '.flv',
			6 => '.swf',
			7 => '.mkv',
			8 => '.avi',
			9 => '.rm',
			10 => '.rmvb',
			11 => '.mpeg',
			12 => '.mpg',
			13 => '.ogg',
			14 => '.ogv',
			15 => '.mov',
			16 => '.wmv',
			17 => '.mp4',
			18 => '.webm',
			19 => '.mp3',
			20 => '.wav',
			21 => '.mid',
			22 => '.rar',
			23 => '.zip',
			24 => '.tar',
			25 => '.gz',
			26 => '.7z',
			27 => '.bz2',
			28 => '.cab',
			29 => '.iso',
			30 => '.doc',
			31 => '.docx',
			32 => '.xls',
			33 => '.xlsx',
			34 => '.ppt',
			35 => '.pptx',
			36 => '.pdf',
			37 => '.txt',
			38 => '.md',
			39 => '.xml',
		  ),
		);
		return self::$config;
	}
	
}