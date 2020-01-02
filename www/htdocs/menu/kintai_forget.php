<?php
	//勤怠入力画面から打刻忘れを登録する
	//IPアドレスOK・cookie認証済のみアクセス可

	include_once ('db/db.inc');	
	include_once ('common.inc');
	
	$db = new DbConnect();
	$a_check = authcheck();
	
	if (isset($_COOKIE['AUTH'])) {
		$cookie = ($_COOKIE['AUTH']);
		$cf_check = checkCookie_checkFlg($cookie, $db);
	} 
	
	// Cookieあり(DB内と一致)・IPアドレスOK・認証許可FLG1→アクセスOK
	if (($a_check == 99) && ($cf_check == 1)) {
		//Cookieから社員Noを取得
		$syain_no = (checkCookie($cookie, $db))->syain_no;
	} else {
		header ("Location: /");
		exit;
	}
	
	//社員Noから名前を取得
	$syain = new SYAIN();
	$syain->select_sno_syaindata($syain_no, $db);
	$name = $syain->name;
	
	//登録する時刻
	date_default_timezone_set('Asia/Tokyo');
	$now_time = date("Y-m-d H:i:s");
	

	//登録する勤務日
	if (isset($_GET['wd'])) {
		$work_day = h($_GET['wd']);
	} else {
		$work_day = date("Y-m-d");
	}
	
	$f_id = h($_GET['f_id']);
	
	if (h($_GET['k_id']) == "" ){
		$k_id = "";
	} else {
		$k_id = h($_GET['k_id']);
	}
	$kf_html = "";
	
	if ($f_id == 0) {
		//出勤時間1の打刻忘れの場合
		$kf_html .= "<p>出勤時間の打刻忘れ・退勤時間登録</p>";
		$kf_html .= "<p><b>".$work_day."</b><br>";
		$kf_html .= "出勤時間　<input type='time' id='in_time' name='in_time'>　";
		$kf_html .= "退勤時間　<input type='time' name='out_time' id='now_time' value='".(date('H:i',  strtotime($now_time)))."'></p>";
	} else if ($f_id == 1) {
		//退勤時間1の打刻忘れの場合
		$kf_html .= "<p>退勤時間1の打刻忘れ・出勤時間2登録</p>";
		$kf_html .= "<p><b>".$work_day."</b><br>";
		$kf_html .= "退勤時間1　<input type='time' id='out_time' name='out_time'>　";
		$kf_html .= "出勤時間2　<input type='time' name='in_time' id='now_time' value='".(date('H:i',  strtotime($now_time)))."'></p>";
	} else if ($f_id == 2) {
		//出勤時間2の打刻忘れの場合
		$kf_html .= "<p>出勤時間2の打刻忘れ・退勤時間2登録</p>";
		$kf_html .= "<p><b>".$work_day."</b><br>";
		$kf_html .= "出勤時間2　<input type='time' id='in_time' name='in_time'>　";
		$kf_html .= "退勤時間2　<input type='time' name='out_time' id='now_time' value='".(date('H:i',  strtotime($now_time)))."'></p>";
	} else if ($f_id == 3) {
		//退勤時間2の打刻忘れの場合
		$kf_html .= "<p>退勤時間2の打刻忘れ</p>";
		$kf_html .= "<p><b>".$work_day."</b><br>";
		$kf_html .= "退勤時間2　<input type='time' id='out_time' name='out_time'><br>";
	} else if ($f_id == 4) {
		//退勤時間が日付を超えた場合
		$kf_html .= "<p>退勤時間(日付変更後)の打刻</p>";
		$kf_html .= "退勤時間　<input type='time' name='out_time' id='now_time' value='".(date('H:i',  strtotime($now_time)))."'></p>";
	} else if ($f_id == 5) {
		//前回出勤時の退勤打刻忘れの場合
		$kf_html .= "<p>前回出勤時の退勤時間の打刻・出勤時間登録</p>";
		$kf_html .= "退勤時間　<input type='time' id='out_time' name='out_time'><br>";
		$kf_html .= "出勤時間　<input type='time' name='in_time' id='now_time' value='".(date('H:i',  strtotime($now_time)))."'></p>";
	} else if ($f_id == 7) {
		//退勤時間1の打刻忘れの場合
		$kf_html .= "<p>退勤時間の打刻忘れ登録</p>";
		$kf_html .= "<p>退勤時間　<input type='time' id='out_time' name='out_time'></p>";
	}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css" href="menu.css">
<title>打刻忘れ入力</title>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.js"></script>
<script type="text/javascript" src="js/kintai_forget.js"></script>
</head>
<?php include 'header.inc'; ?>
<body>
	<div>
	<div class="breadcrumbs">
		<ul>
			<li><a href="/">猫カフェ 勤怠TOP</a></li>
			<li><a href="/menu/">勤怠メニュー</a></li>
			<li><a href="/menu/kintai.php">勤怠入力</a></li>
			<li>打刻忘れ入力</li>
		</ul>
	</div>
	<h2>打刻忘れ入力</h2>
		<form action = "kintai_forget_comp.php?f_id=<?php echo $f_id; ?>&k_id=<?php echo $k_id; ?>&wd=<?php echo $work_day;?>" method = "post">
<?php echo $kf_html; ?>
		<p><button class='mbt' name="tourokub" id="tourokub">　登録　</button></p>
		</form>
	</div>
</body>
