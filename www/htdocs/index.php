<?php

	include_once ('db/db.inc');
	include_once ('common.inc');
		
	$db = new DbConnect();
		
	$a_check = authcheck();
	
	if (isset($_COOKIE['AUTH'])) {
		$cookie = ($_COOKIE['AUTH']);
		$cf_check = checkCookie_checkFlg($cookie, $db);
	} 
	

	if ($a_check == 0) {
		//Cookieない・IPアドレスOK→登録画面へ
		header('Location: /touroku.php');
		exit;
	} else if ($a_check == 1) {
		//Cookieない・IPアドレスNG→アクセス不可
		echo "アクセス出来ません";
		exit;
	} else if (($a_check == 2) && ($cf_check == 1)) {
		// Cookieあり(DB内と一致)・IPアドレスNG・認証許可FLG1
		//シフトのみ確認OK
		header('Location: /menu/shift.php');
		exit;
	} else if (($a_check == 99) && ($cf_check == 0)) {
		// Cookieあり(DB内と一致)・IPアドレスOK・認証許可FLG0→認証待ち
		echo "管理者からの認証待ちです";
		exit;
	} else if (($a_check == 99) && ($cf_check == 1)) {
		// Cookieあり(DB内と一致)・IPアドレスOK・認証許可FLG1→アクセスOK
		header('Location: /menu/');
		exit;
	} else if (($a_check == 99) && ($cf_check == 99)) {
		// CookieがDB内と一致しない・IPアドレスOK→再登録？
		echo "情報が一致しません(Cookieを削除して再登録してください)";
		exit;
	}
