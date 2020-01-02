<?php

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
	
	//従業員登録の認証待ち・残業登録の認証待ち・打刻修正依頼あれば表示する
	$un_data_ms = "";
	
	//従業員登録の認証待ちのデータを表示する
	$pm_no = "0"; //認証許可FLGが0
	$sql = "SELECT COUNT(*) AS cnt FROM ninsyou_list WHERE permission = ?";
	$rs = $db->prepare($sql);
	$j_data = array($pm_no);
	$rs->execute($j_data);
	$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
	
	foreach ($rd as $row){
		$cnt = $row["cnt"];
		
		$unapproved_data = "";
		$non_unapproved_data = "";
		if ($cnt >= 1 ){
			$un_data_ms .= "<p>未認証の<a href='/kanri/member/ninsyou.php'>従業員登録</a>があります</p>";
		}
	}
	
	//勤怠時間修正(打刻忘れ)のデータを表示する
	$sql2 = "SELECT * FROM kintai_list";
	$rs2 = $db->prepare($sql2);
	$rs2->execute();
	$rd2 = $rs2->fetchAll(PDO::FETCH_ASSOC);
	
	foreach ($rd2 as $row2) {
		$intime = h($row2['in_time']);
		$f_intime = h($row2['f_in_time']);
		$outtime = h($row2['out_time']);
		$f_outtime = h($row2['f_out_time']);
	}
	if ((($intime == "" ) && (!($f_intime == "" ))) or (($outtime == "" ) && (!($f_outtime == "" )))) {
		$un_data_ms .= "<p>未認証の<a href='/kanri/kintai/forget_kintai.php'>打刻修正登録</a>があります</p>";
	}
	
	//認証待ちの残業登録のデータを表示する
	//残業が未認証(認証FLG→0)
	$z_permission = "0";
	// 残業登録申請データがあるかを確認
	$sql3 = "SELECT COUNT(*) AS cnt FROM kintai_list WHERE permission = ?";
	$rs3 = $db->prepare($sql3);
	$z_data = array($z_permission);
	$rs3->execute($z_data);
	$rd3 = $rs3->fetchAll(PDO::FETCH_ASSOC);
	foreach ($rd3 as $row3){
		$z_cnt = $row3["cnt"];
		if ($z_cnt >= 1  ){
			$un_data_ms .= "<p>未認証の<a href='/kanri/zangyou/zangyou_kanri.php'>残業登録</a>があります</p>";
		}
	}
	if ($un_data_ms == "" ) {
		$kanri_ms = "<p>現在、お知らせはございません</p>";
	} else {
		$kanri_ms = $un_data_ms;
	}

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css" href="kanri.css">
<title>管理者ページ</title>
</head>
<?php include 'header.inc'; ?>
<body>
	<div class="breadcrumbs">
		<ul>
			<li><a href="/">猫カフェ 勤怠TOP</a></li>
			<li>管理者メニュー</li>
		</ul>
	</div>
	<div class="kanri_menu">
		<h2>管理者メニュー</h2>
		<ul>
			<li><a href="/kanri/member/">従業員管理</a></li>
			<li><a href="/kanri/shift/">シフト管理</a></li>
			<li><a href="/kanri/kintai/">勤怠時間管理</a></li>
			<li><a href="/kanri/zangyou/">残業時間管理</a></li>
			<li><a href="/kanri/kyuyo">給与管理</a></li>
		<ul>
	</div>
	<div class="kanri_ms">
		<h2>お知らせ</h2>
		<?php echo $kanri_ms; ?>
	</div>
</body>