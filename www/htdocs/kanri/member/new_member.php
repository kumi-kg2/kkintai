<?php
	//従業員新規登録ページ
	include_once ('db/db.inc');	
	include_once ('common.inc');
	
	$db = new DbConnect();
	$a_check = authcheck();
	
	if (isset($_COOKIE['AUTH'])) {
		$cookie = ($_COOKIE['AUTH']);
		$cf_check = checkCookie_checkFlg($cookie, $db);
	} 
	
	// Cookieあり(DB内と一致)・IPアドレスOK・認証許可FLG1・管理者FLG1→アクセスOK
	if (($a_check == 99) && ($cf_check == 1)) {
		//Cookieから社員Noを取得
		$syain_no = (checkCookie($cookie, $db))->syain_no;
		
		if (isset($syain_no)) {
			$syainno = ($syain_no);
			$ckf_check = checkKanriFlg($syainno, $db);
			
			if ($ckf_check == 1) {
				//	ok
			} else if ($ckf_check == 0) {
				header ("Location: /");
				exit;
			}
		}
		
	} else {
		header ("Location: /");
		exit;
	}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css" href="/kanri/kanri.css">
<script src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.js"></script>
<script type="text/javascript" src="js/member.js"></script>
<title>従業員新規登録</title>
</head>
<?php include 'header.inc'; ?>
<body>
	<div class="breadcrumbs">
		<ul>
			<li><a href="/">猫カフェ 勤怠TOP</a></li>
			<li><a href="/kanri/">管理者メニュー</a></li>
			<li><a href="/kanri/member/">従業員管理</a></li>
			<li>従業員新規登録</li>
		</ul>
	</div>
	<div>
		<h2>新規登録</h2>
			<form method ="post" action="new_member_comp.php">
				<p>社員番号：<input type="text" id="syainno" name="syainno" size="30" maxlength="20">
				<input type="checkbox" name="k_flg" value="1">管理者FLG</p>
				<p>　部　署：<input type="text" id="busyo" name="busyo" size="30" maxlength="20"></p>
				<p>　名　前：<input type="text" id="name" name="name" size="30" maxlength="20"></p>
				<p>フリガナ：<input type="text" id="furi" name="furi" size="30" maxlength="20"><br>
				（カタカナで入力、スペースは使用しないでください）</p>
				<p>生年月日：<input type="date" id="birth" name="birth" size="30" maxlength="20"></p>
				<p>携帯番号：<input type="text" id="phone" name="phone" size="30" maxlength="20"></p>
				<p>給与区分：<input type="text" id="kkubun" name="kkubun" size="30" maxlength="20"></p>
				<p>基本給：<input type="text" id="kktanka" name="kktanka" size="30" maxlength="20"></p>
				<p>残業代：<input type="text" id="kztanka" name="kztanka" size="30" maxlength="20"></p>
				<p>開始年月：<input type="month" id="kstartday" name="kstartday" size="30" maxlength="20"><br>
				(〇〇年〇月21日からになります)</p>
		<!--		<input type="submit" name="tourokub" value="新規登録">-->
				<button class='kbt' name="tourokub">新規登録</button>
			</form>
	</div>
</body>
