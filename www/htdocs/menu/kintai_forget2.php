<?php
	//打刻忘れ入力ページ(前回出勤時の出勤・退勤の両方打刻忘れ)
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
	
		
	//現在日時
	date_default_timezone_set('Asia/Tokyo');
	$now_day = date("Y-m-d");
	$last_month_day = date("Y-m-d",strtotime("-1 month"));
	
	
	//社員Noから名前を取得
	$syain = new SYAIN();
	$syain->select_sno_syaindata($syain_no, $db);
	$name = $syain->name;
	
	
	//出勤日・時間(出退勤)表示
	//現在の日時からひと月前までのデータを表示
	$kintai_last = "";
	$sql = "SELECT * FROM kintai_list WHERE (work_day BETWEEN ? AND ?) AND syain_no = ? ORDER BY work_day, in_time, f_in_time ASC";
	$rs = $db->prepare($sql);
	$data = array(
				$last_month_day,
				$now_day,
				$syain_no
				);
	$rs->execute($data);
	$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
	
	$kintai_data ="";
	$f_kintai_data ="";
		$f_data = "";

	foreach ($rd as $row){
		$k_id = h($row['k_id']);
		$syain_no = h($row['syain_no']);
		$work_day = h($row['work_day']);
		$intime = date('H:i',  strtotime(h($row['in_time'])));
		$f_intime = date('H:i',  strtotime(h($row['f_in_time'])));
//		$outtime = h($row['out_time']);
//		$f_outtime = h($row['f_out_time']);
		
/*		if (is_null($row['out_time'])) {
			$outtime = "";
		} else {
			$outtime = date('H:i',  strtotime(h($row['out_time'])));
		}
		if (is_null($row['f_out_time'])) {
			$f_outtime = "";
		} else {
			$f_outtime = date('H:i',  strtotime(h($row['f_out_time'])));
		}*/
		
		//打刻漏れしている勤怠リストを表示
		if (is_null($row['out_time']) && (is_null($row['f_out_time']))) {
			$f_kintai_data .= "<tr>";
			$f_kintai_data .= "<td id='k_id".$k_id."'>".$work_day."</td>";
			$f_kintai_data .= "<td>".$intime."</td>";
		//	$f_kintai_data .= "<td>".$outtime."</td>";
			$f_kintai_data .= "</tr>";
		}
	}
	$f_data .= "<p>退勤打刻忘れデータ</p>";
	$f_data .= "<table id='f_kitaidataT'>";
	$f_data .= "<tr><th>出勤日</th><th>出勤時間</th></tr>";
	$f_data .= $f_kintai_data;
	$f_data .= "</table>";
	$f_data .= "<p>登録したいデータの出勤日をクリックすると<br>該当するデータの出勤時間が自動的に入力されます<br>";
	$f_data .= "登録したい打刻忘れの退勤時間を入力してください</p>";
	$f_data .= "<p>出勤時間　<input type='time' name='f_in_time' id='f_in_time'>";
	$f_data .= "　退勤時間　<input type='time' name='f_out_time' id='f_out_time'>";
	$f_data .= "<br>退勤時間が日付を跨ぐ場合はチェック→<input type='checkbox' name='out_time_day' id='out_time_day'>";
//	$f_data .= "<br><input type ='submit' id='f_tourokub' value = '退勤 打刻登録'></p>";
	$f_data .= "<br><button class='mbt' id='f_tourokub'>退勤 打刻登録</button>";
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css" href="menu.css">
<title>打刻修正登録</title>
<style type="text/css">
   	.clicked {
		background-color: #D9E5FF;
	}
</style>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.js"></script>
<script type="text/javascript" src="js/kintai_forget.js"></script>
</head>
<?php include 'header.inc'; ?>
<body>
	<div class="breadcrumbs">
		<ul>
			<li><a href="/">猫カフェ 勤怠TOP</a></li>
			<li><a href="/menu/">勤怠メニュー</a></li>
			<li>打刻修正登録</li>
		</ul>
	</div>
	<h2>打刻修正登録</h2>
	<p><a href="/menu//kintai_data.php"><button class='mbt'>勤怠入力済データ</button></a></p>
	<div class='k_border' id="f_kitaidata" style="padding: 10px; margin-bottom: 15px; border: 1px solid #333333; border-radius: 10px; width:380px;">
<?php 	if (!($f_kintai_data == "")) {
			echo ($f_data);
		} else {
			echo "<p>現在、退勤打刻忘れのデータはありません</p>";
		}
?>
	</div>
	<div class='k_border' id="new_f_kitaidata" style="padding: 10px; border: 1px solid #333333; border-radius: 10px; width:380px;">
		<form action = "kintai_forget_comp.php?f_id=99&k_id=&wd=" method = "post">
			<p>退勤打刻忘れのデータ以外に、登録する場合は<br>
			登録したい勤務日・勤務時間を入力してください</p>
			<p>　出勤日　<input type='date' id='work_day' name='work_day'><br>
			出勤時間　<input type='time' id='in_time' name='in_time'>　　退勤時間　<input type='time' id='out_time' name='out_time'>
			<br>退勤時間が日付を跨ぐ場合はチェック→<input type="checkbox" name="out_time_day">
			<br><button class='mbt' id='n_tourokub'>新規 打刻登録</button></p>
	<!--		<br><input type ="submit" id="n_tourokub" value = "新規 勤怠登録"></p>-->
		</form>
	</div>
</body>
