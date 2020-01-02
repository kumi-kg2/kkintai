<?php

	include_once ('db/db.inc');
	include_once ('common.inc');
		
	$db = new DbConnect();
	
//	$syain = new SYAIN();
//	$syain->select_syaindata($db);
	
//	echo $syain->j_id;
//	echo $syain->syain_no;
	
	if (isset($_COOKIE['AUTH'])) {
		$cookie = ($_COOKIE['AUTH']);
	} 
//	$checkCookie = checkCookie($_COOKIE['AUTH'], $db);←本来はこっち使う
//	$checkCookie = checkCookie($cookie, $db);//てすと仮

	//	echo (checkCookie($cookie, $db))->furi;
	//	echo (checkCookie($cookie, $db))->name;
	$s_no = (checkCookie($cookie, $db))->syain_no;

	$c_flag = checkCookie_checkFlg($cookie, $db);
	
//	$syain = new SYAIN();
//	$syain->select_sno_syaindata($s_no, $db);
//	echo $syain->busyo;
//	echo $syain->furi;
//	echo $syain->name;



//	echo $syain->furi;
	
	
	
	if ($c_flag == 0) {
		echo 'NG';
	} else if ($c_flag == 1) {
		echo 'ok';
	}
	
