<?php

 //勤怠データ追加ページ
 //ひと月前のまでOKにする
 //社員NOからデータを取得する
 
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
	
	$s_no = h($_GET['s_no']);
	
	//社員Noから名前を取得
	$syain = new SYAIN();
	$syain->select_sno_syaindata($s_no, $db);
	$name = $syain->name;
	
	//現在日時
	date_default_timezone_set('Asia/Tokyo');
	$now_day = date("Y-m-d");
	$last_month_day = date("Y-m-d",strtotime("-1 month"));
	
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css" href="/kanri/kanri.css">
<script src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.js"></script>
<script type="text/javascript" src="js/add_kintai.js"></script>
<title>勤怠新規登録</title>
</head>
<?php include 'header.inc'; ?>
<body>
	<div class="breadcrumbs">
		<ul>
			<li><a href="/">猫カフェ 勤怠TOP</a></li>
			<li><a href="/kanri/">管理者メニュー</a></li>
			<li><a href="/kanri/kintai/">勤怠時間管理</a></li>
			<li><a href="/kanri/kintai/kintai_kanri.php">勤怠時間修正</a></li>
			<li>勤怠リスト</li>
		</ul>
	</div>
	<div id="add_kitaidata">
		<h2>勤怠新規登録</h2>
			<form action = "add_kintai_data_comp.php?s_no=<?php echo $s_no; ?>" method = "post">
				<p>社員番号：<?php echo $s_no; ?>　名前：<?php echo $name; ?></p>
				<p>勤怠登録する勤務日・勤務時間等を入力してください<br>
				出勤日　　<input type='date' id='work_day' name='work_day' min="<?php echo $last_month_day; ?>"><br>
				出勤時間　<input type='time' id='in_time' name='in_time'>　　退勤時間　<input type='time' id='out_time' name='out_time'>
				<br>退勤時間が日付を跨ぐ場合はチェック→<input type="checkbox" name="out_time_day" value="1" ></p>
				<p>残業時間　<input type='number' id='z_time' name='z_time'   min='0' max='500' step='10'></p>
				<p>備考　<textarea id='biko' name='biko'  cols='40' rows='8'></textarea></p>
				<p><input type ="submit" id="tourokub" value = "新規登録"></p>
			</form>
	</div>
</body>