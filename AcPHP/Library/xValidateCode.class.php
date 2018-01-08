<?php
//验证码类
class xValidateCode {
	private $charset = '1234569QWERTYUIPASDFGHJKLZXCVBNMqwertyupasdfghkzxcvbnm';	//随机因子
	private $code;							//验证码
	private $codelen = 5;					//验证码长度
	private $width = 80;					//宽度
	private $height = 36;					//高度
	private $img;								//图形资源句柄
	private $font;								//指定的字体
	private $fontsize = 14;				//指定字体大小
	private $fontcolor;						//指定字体颜色
	private $color;
	
	//构造方法初始化
	public function __construct($color='255,255,255',$width=80,$height=36) {
		$this->color = $color;
		$this->width = $width;
		$this->height = $height;
		$this->font = C_SYS_INCLUDE_PATH.'font/consolai.ttf';
	}
	
	//生成随机码
	private function createCode() {
		$_len = strlen($this->charset)-1;
		for ($i=0;$i<$this->codelen;$i++) {
			$this->code .= $this->charset[mt_rand(0,$_len)];
		}
	}
	
	//生成背景
	private function createBg() {
		$this->img = imagecreatetruecolor($this->width, $this->height);
		//$color = imagecolorallocate($this->img, mt_rand(157,255), mt_rand(157,255), mt_rand(157,255));
		//189
		if ($this->color!='') {
			$color = explode(',', $this->color);
			$color = imagecolorallocate($this->img, $color[0],$color[1],$color[2]);
		}else {
			$color = imagecolorallocate($this->img, 255,255, 255);
		}
		
		imagefilledrectangle($this->img,0,$this->height,$this->width,0,$color);
	}
	
	//生成文字
	private function createFont() {	
		$_x = $this->width / $this->codelen;
		for ($i=0;$i<$this->codelen;$i++) {
			$this->fontcolor = imagecolorallocate($this->img,mt_rand(0,156),mt_rand(0,156),mt_rand(0,156));
			imagettftext($this->img,$this->fontsize,mt_rand(-30,30),$_x*$i+mt_rand(1,5),$this->height / 1.4,$this->fontcolor,$this->font,$this->code[$i]);
		}
	}
	
	//生成线条、雪花
	private function createLine() {
		for ($i=0;$i<6;$i++) {
			$color = imagecolorallocate($this->img,mt_rand(0,156),mt_rand(0,156),mt_rand(0,156));
			imageline($this->img,mt_rand(0,$this->width),mt_rand(0,$this->height),mt_rand(0,$this->width),mt_rand(0,$this->height),$color);
		}
		for ($i=0;$i<100;$i++) {
			$color = imagecolorallocate($this->img,mt_rand(200,255),mt_rand(200,255),mt_rand(200,255));
			imagestring($this->img,mt_rand(1,5),mt_rand(0,$this->width),mt_rand(0,$this->height),'*',$color);
		}
	}
	
	//输出
	private function outPut() {
		header('Content-type:image/png');		
		imagepng($this->img);
		imagedestroy($this->img);
	}
	
	//对外生成
	public function doimg() {
		$this->createBg();
		$this->createCode();
		//$this->createLine();
		$this->createFont();
		$this->outPut();
	}
	
	//获取验证码
	public function getCode() {
		return strtolower($this->code);
	
	}
	
}

	
	