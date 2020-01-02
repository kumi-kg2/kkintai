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
	
	//２か月前からのシフトのみ検索可
	//今日の日付を取得する
	date_default_timezone_set('Asia/Tokyo');
	$now_date = date("Y-m-d");
	$now_day = date("d");
	
	//〇月度
	if (($now_day >= 21) && ($now_day <= 31)) {
		$y_2month = date("Y-m", strtotime('-2 month'));
	} else if (($now_day >= 1) && ($now_day <=20)) {
		$y_2month = date("Y-m", strtotime('-3 month'));
	}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css" href="/kanri/kanri.css">
<title>過去シフト検索</title>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.js"></script>
<script>
$(function(){
	$("#kensaku_bt").click(function(){
		//入力エラーチェック
		if ($("#m_shift").val() == "") {
			alert ("月間シフトの年/月が未入力です");
			return false;
		}
	});
});
</script>
</head>
<?php include 'header.inc'; ?>
<body>
	<div class="breadcrumbs">
		<ul>
			<li><a href="/">猫カフェ 勤怠TOP</a></li>
			<li><a href="/kanri/">管理者メニュー</a></li>
			<li><a href="/kanri/shift/">シフト管理</a></li>
			<li><a href="/kanri/shift/k_shift.php">シフト一覧</a></li>
			<li>過去シフト検索</li>
		</ul>
	</div>
	<div>
	<h2>過去シフト検索</h2>
		<form action = "shift_past_list.php" method = "post">
			<p><input type="month" id="m_shift" name="m_shift" type="text" max="<?php echo $y_2month; ?>" />
			<button class="kbt" id="kensaku_bt" name="kensaku_bt" >シフト検索</button></p>
		<!--	<input type ="submit" value = "確認"></p> -->
		</form>
		<p>シフトは〇〇年〇月度のシフト一覧になります<br>例:2019年10月を選択の場合…<br>2019年10月21日～2019年11月20日のシフト</p>
		<p>過去シフト2か月前からのみになります<br>先月/当月/翌月シフトは<a href="/kanri/shift/k_shift.php">シフト一覧</a>からご確認ください</p>
	</div>
</body>
