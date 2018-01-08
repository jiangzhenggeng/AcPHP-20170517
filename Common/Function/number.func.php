<?php

//保留两位小数，
//不四舍五入
function sc_retain_decimal($number){
	return substr(sprintf("%.3f", $number), 0, -1);
}















