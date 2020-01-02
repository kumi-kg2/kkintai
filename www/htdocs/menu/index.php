<?php
//menu画面ページ

	include_once ('db/db.inc');
	include_once ('common.inc');
	
	$db = new DbConnect();
	$a_check = authcheck();
	
	if (isset($_COOKIE['AUTH'])) {
		$cookie = ($_COOKIE['AUTH']);
		$cf_check = checkCookie_checkFlg($cookie, $db);
	}
	
	if (($a_check == 99) && ($cf_check == 1)) {
		// Cookieあり(DB内と一致)・IPアドレスOK・認証許可FLG1→アクセスOK
		// Cookieの期限を延ばしてあげる
		setcookie("AUTH", $cookie, time()+ (5 * 365 * 24 * 60 * 60));
		// 最終アクセス日の更新
		date_default_timezone_set('Asia/Tokyo');
		$last_date = date("YmdHis");
		$sql = "UPDATE ninsyou_list SET lastaccess= ? ";
		$sql .= "WHERE ninsyou_num = ?";
		$rs = $db->prepare($sql);
		$data = array($last_date, $cookie);
		$rs->execute($data);
		$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
		
	} else if (($a_check == 2) && ($cf_check == 1)) {
		// Cookieあり(DB内と一致)・IPアドレスNG・認証許可FLG1→シフトのみOK
		header ("Location: http://s.ibg.jp/menu/shift.php");
		exit;
	} else {
		header ("Location: /");
		exit;
	}
	
		if (isset($_COOKIE['AUTH'])) {
		$cookie = ($_COOKIE['AUTH']);
		$cf_check = checkCookie_checkFlg($cookie, $db);
	} 
	
	// Cookieあり(DB内と一致)・IPアドレスOK・認証許可FLG1・管理者FLG1
	// →管理者ページURL表示
	if (($a_check == 99) && ($cf_check == 1)) {
		//Cookieから社員Noを取得
		$syain_no = (checkCookie($cookie, $db))->syain_no;
		
		if (isset($syain_no)) {
			$syainno = ($syain_no);
			$ckf_check = checkKanriFlg($syainno, $db);
			
			if ($ckf_check == 1) {
				$kanri_html = "<li class='kanri_menu'><a href='/kanri/'>管理者メニュー</a></li>";
			} else if ($ckf_check == 0) {
				$kanri_html = "";
			}
		}
		
	} else {
		header ("Location: /");
		exit;
	}


?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css" href="menu.css">
<title>猫カフェ勤怠</title>
</head>
<?php include 'header.inc'; ?>
<body>
	<div class="kintai">
		<h2>勤怠メニュー</h2>
		<ul>
			<li class="kintai_menu"><a href="kintai.php">勤怠入力</a></li>
			<li class="kintai_menu"><a href="shift.php">シフト確認</a></li>
			<li class="kintai_menu"><a href="zangyou.php">残業登録</a></li>
			<li class="kintai_menu"><a href="kintai_forget2.php">打刻(勤怠)修正登録</a></li>
			<li class="kintai_menu"><a href="list.php">当日出勤者一覧</a></li>
			<?php echo $kanri_html; ?>
		</ul>
	</div>
</body>
