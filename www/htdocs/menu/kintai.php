<?php
	//出退勤登録ページ
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
	
	//登録する現在の日付け
	date_default_timezone_set('Asia/Tokyo');
	$workday = date("Y-m-d");

	//前日の日付
	$yesterday = date("Y-m-d",strtotime("-1 day"));
	
	//現在の日付からシフトを表示する
	$now_day = ltrim(date("d"), '0');
	if (($now_day >= 21) && ($now_day <= 31)) {
		$now_ym = date("Ym");
	} else if (($now_day >= 1) && ($now_day <=20)) {
		$now_ym = date("Ym", strtotime('-1 month'));
	}
	$s_coad_db = "s_coad_".$now_day;
	$in_time1_db = "in_time_".$now_day."_1";
	$out_time1_db = "out_time_".$now_day."_1";
	$in_time2_db = "in_time_".$now_day."_2";
	$out_time2_db = "out_time_".$now_day."_2";
	
	$sql3 = "SELECT * FROM shift_list WHERE syain_no = ? AND s_year_month = ?";
	$rs3 = $db->prepare($sql3);
	$data3 = array(
				$syain_no,
				$now_ym
				);
	$rs3->execute($data3);
	$rd3 = $rs3->fetchAll(PDO::FETCH_ASSOC);
	foreach ($rd3 as $row3){
		foreach ($row3 as $key=>$val) {
			//シフトコード
			if(strpos($key, $s_coad_db) !== false){
				$s_coad = h($val);
			}
			//出勤時間1
			if(strpos($key, $in_time1_db) !== false){
				$in_time1 = str_replace('00:00', '&nbsp;&nbsp;', (substr((h($val)), 10, 6)));
			}
			//退勤時間1			
			if(strpos($key, $out_time1_db) !== false){
				$out_time1 = str_replace('00:00', '&nbsp;&nbsp;', (substr((h($val)), 10, 6)));
			}
			//出勤時間2
			if(strpos($key, $in_time2_db) !== false){
				$in_time2 = str_replace('00:00', '&nbsp;&nbsp;', (substr((h($val)), 10, 6)));
			}
			//退勤時間2
			if(strpos($key, $out_time2_db) !== false){
				$out_time2 = str_replace('00:00', '&nbsp;&nbsp;', (substr((h($val)), 10, 6)));
			}
		}
	}
	if ((isset($s_coad)) && ($s_coad == "H")) {
		$shift = $workday."&nbsp;&nbsp;休み";
	} else if ((isset($s_coad)) && (!($s_coad == "H"))) {
		$shift = $workday."&nbsp;&nbsp;シフト情報<br>シフトコード&nbsp;".$s_coad."<br>";
	//	$shift .= $in_time1."&nbsp;～&nbsp;".$out_time1."&nbsp;/&nbsp;".$in_time2."&nbsp;～&nbsp;".$out_time2; //出退勤時間1のみ表示
		$shift .= $in_time1."&nbsp;～&nbsp;".$out_time1;
	} else {
		$shift = "シフトは未登録です";
	}
	//最終出勤日・時間(出退勤)表示
	$kintai_last = "";
	$sql = "SELECT * FROM kintai_list WHERE syain_no = ? ORDER BY work_day DESC LIMIT 1";
	$rs = $db->prepare($sql);
	$data = array($syain_no);
	$rs->execute($data);
	$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
	foreach ($rd as $row){
		$last_id = h($row['k_id']);
//		$last_no = h($row['syain_no']);
		$last_day = h($row['work_day']);
		$last_intime= h($row['in_time']);
		$last_f_intime= h($row['f_in_time']);
		$last_outtime= h($row['out_time']);
		$last_f_outtime= h($row['f_out_time']);
	}
	
	//最終の勤怠情報表示
	if ((!(isset($last_day)))) {
		//勤怠情報がまだない場合
		$kintai_last .= "最終勤怠登録情報はまだありません";
	} else {
		$kintai_last .= "最終勤怠登録情報<br>";
		$kintai_last .= "出勤日　".$last_day."<br>";
		
		//前回の出退勤が入力済
		if ((!($last_intime == "")) && (!($last_outtime == ""))) {
			$kintai_last .= "出勤時間　".date('H:i',  strtotime($last_intime));
			$kintai_last .= "　退勤時間　".date('H:i',  strtotime($last_outtime));
		//前回の仮出勤時間・退勤時間が入力済
		} else if ((!($last_f_intime == "")) && (!($last_outtime == ""))) {
			$kintai_last .= "出勤時間　".date('H:i',  strtotime($last_f_intime));
			$kintai_last .= "　退勤時間　".date('H:i',  strtotime($last_outtime));
		//前回の出勤時間・仮退勤時間が入力済
		} else if ((!($last_intime == "")) && (!($last_f_outtime == ""))) {
			$kintai_last .= "出勤時間　".date('H:i',  strtotime($last_intime));
			$kintai_last .= "　退勤時間　".date('H:i',  strtotime($last_f_outtime));
		//前回の仮出退勤時間入力済
		} else if ((!($last_f_intime == "")) && (!($last_f_outtime == ""))) {
			$kintai_last .= "出勤時間　".date('H:i',  strtotime($last_f_intime));
			$kintai_last .= "　退勤時間　".date('H:i',  strtotime($last_f_outtime));
		//前回の出勤時間のみ入力済
		} else if ((!($last_intime == "")) && (($last_outtime == "") or ($last_f_outtime == "")) ){
			$kintai_last .= "出勤時間　".date('H:i',  strtotime($last_intime));
		//前回の仮出勤時間のみ入力済
		} else if ((!($last_f_intime == "")) && (($last_outtime == "") or ($last_f_outtime == "")) ){
			$kintai_last .= "出勤時間　".date('H:i',  strtotime($last_f_intime));
		}
	}
	//出勤日(work_day)が登録済みかをカウントして確認する
	$sql2 = "SELECT COUNT(*) AS cnt FROM kintai_list WHERE syain_no = ? AND work_day=?";
	$rs2 = $db->prepare($sql2);
	$data2 = array($syain_no, $workday);
	$rs2->execute($data2);
	$rd2 = $rs2->fetchAll(PDO::FETCH_ASSOC);
	
	$k_hmtl ="";
	
	foreach ($rd2 as $row2){
		$cnt = $row2["cnt"];
		
		if ((!(isset($last_day)))) {
			//勤怠情報がまだない場合
			$k_hmtl .= "<p>出勤時間を登録 → <button id='kintaibut' class='mbt'>出　勤</button></p>";
		} else if (($cnt == 0 ) && ((!($last_outtime == "")) or (!($last_f_outtime == "")))){
			//出勤時間1登録ボタン表示
			$k_hmtl .= "<p>出勤時間を登録 → <button id='kintaibut' class='mbt'>出　勤</button></p>";
			//出勤時間1未登録・退勤勤時間1登録したい場合
			$k_hmtl .= "<p>出勤の打刻忘れ・退勤時間登録 → <a href='kintai_forget.php?f_id=0&k_id='><button class='mbt'>打刻修正</button></a>";
			
		} else if (($cnt == 0 ) && (($last_outtime == "") or ($last_f_outtime == "")) && ($last_day == $yesterday)) {
			//出勤時間1登録ボタン表示
			$k_hmtl .= "<p>出勤時間を登録 → <button id='kintaibut' class='mbt'>出　勤</button></p>";
			//出勤時間1未登録・退勤勤時間1登録したい場合
			$k_hmtl .= "<p>出勤の打刻忘れ・退勤時間登録 → <a href='kintai_forget.php?f_id=0&k_id='><button class='mbt'>打刻修正</button></a>";
			//前回出勤時の退勤打刻忘れ
			$k_hmtl .="<br>前回の退勤打刻忘れ・出勤時間登録 → <a href='kintai_forget.php?f_id=5&k_id=".$last_id."&wd=".$last_day."'><button class='mbt'>打刻修正</button></a>";
			//日付を跨いでの退勤入力
			$k_hmtl .="<br>日付を跨いでの退勤登録 → <a href='kintai_forget.php?f_id=4&k_id='><button class='mbt'>退　勤</button></a></p>";
			
		} else if (($cnt == 0 ) && (($last_outtime == "") or ($last_f_outtime == ""))) {
			//出勤時間1登録ボタン表示
			$k_hmtl .= "<p>出勤時間を登録 → <button id='kintaibut' class='mbt'>出　勤</button></p>";
			//出勤時間1未登録・退勤勤時間1登録したい場合
			$k_hmtl .= "<p>出勤の打刻忘れ・退勤時間登録 → <a href='kintai_forget.php?f_id=0&k_id='><button class='mbt'>打刻修正</button></a>";
			//前回出勤時の退勤打刻忘れ
			$k_hmtl .="<br>前回の退勤打刻忘れ・出勤時間登録 → <a href='kintai_forget.php?f_id=5&k_id=".$last_id."&wd=".$last_day."'><button class='mbt'>打刻修正</button></a></p>";
			
		} else if (( 0 < $cnt ) && ( $cnt <= 2)) {
		
			$sql = "SELECT * FROM kintai_list WHERE syain_no = ? AND work_day=?";
			$kintaidata = array($syain_no, $workday);
			$rs = $db->prepare($sql);
			$rs->execute($kintaidata);
			$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
			
			foreach ($rd as $row){
				$k_id = $row['k_id'];
				$syain_no = $row['syain_no'];
				$work_day = $row['work_day'];
				$intime= $row['in_time'];
				$f_intime= $row['f_in_time'];
				$outtime= $row['out_time'];
				$f_outtime= $row['f_out_time'];
		}
			
//仮で出勤・退勤時間2ボタン表示の際のみ『出勤時間2・退勤時間2』と表示
//だいたいは出勤・退勤が１回のみの予定

			if (($cnt == 1 ) && ((!($intime == "" )) or (!($f_intime == "" ))) && ((!($outtime =="")) or (!($f_outtime ==""))) ) {
				//出勤時間2登録ボタン表示
				$k_hmtl .= "<p>出勤時間2を登録 → <button id='kintaibut' class='mbt'>出　勤</button></p>";
				//出勤時間2未登録・退勤勤時間2登録したい場合
				$k_hmtl .= "<p>出勤時間2の打刻忘れ・退勤時間2登録 → <a href='kintai_forget.php?f_id=2&k_id=".$k_id."'><button class='mbt'>打刻修正</button></a></p>";
				
			} else if (($cnt == 1 ) && (!($intime == "" ))) {
				//退勤時間1登録ボタン表示
				$k_hmtl .= "<p>退勤時間を登録 → <button id='kintaibut' class='mbt'>退　勤</button></p>";
				//退勤時間1のみ登録したい場合
				$k_hmtl .= "<p>退勤時間1の打刻忘れ登録 → <a href='kintai_forget.php?f_id=7&k_id=".$k_id."'><button class='mbt'>打刻修正</button></a>";
				//退勤時間1未登録・出勤時間2登録したい場合
				$k_hmtl .= "<br>退勤時間1の打刻忘れ・出勤時間2登録 → <a href='kintai_forget.php?f_id=1&k_id=".$k_id."'><button class='mbt'>打刻修正</button></a></p>";
				
				
			} else if (($cnt == 2 ) && ((isset($intime)) or (isset($f_intime))) && ((isset($outtime)) or (isset($f_outtime)) )) {
				$k_hmtl .= "<p>今日の出退勤は全て登録済です</p>";
				
			} else if (($cnt == 2 ) && ((empty($outtime)) or (empty($f_outtime))) ) {
				//退勤時間2登録ボタン表示
				$k_hmtl .= "退勤時間2を登録 → <button id='kintaibut' class='mbt'>退　勤</button></p>";
				$k_hmtl .= "<p>退勤時間2の打刻忘れ登録 → <a href='kintai_forget.php?f_id=3&k_id=".$k_id."'><button class='mbt'>打刻修正</button></a></p>";//いらない？かも
			}
		}
	}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css" href="menu.css">
<title>勤怠入力画面</title>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.js"></script>
<script type="text/javascript" src="js/kintai.js"></script>
</head>
<?php include 'header.inc'; ?>
<body>
	<div class="breadcrumbs">
		<ul>
			<li><a href="/">猫カフェ 勤怠TOP</a></li>
			<li><a href="/menu/">勤怠メニュー</a></li>
			<li>勤怠入力</li>
		</ul>
	</div>
	<h2>勤怠入力</h2>
	<div class="input_kintai">
		<p>社員番号：<?php echo $syain_no; ?>　名前：<?php echo h($name); ?></p>
		<div id="kintailast" style="padding: 10px; margin-bottom: 10px; border: 1px solid #333333; border-radius: 10px; width:280px;">
		<?php echo $kintai_last; ?>
		</div>
		<div id="kintaishift" style="padding: 10px; margin-bottom: 10px; border: 1px solid #333333; border-radius: 10px; width:280px;">
		<?php echo $shift?>
		</div>
		<div id="kintai"></div>
		<!-- 共通メッセージ表示(仮)
		<div id="cmms"></div>
		<!-- 個人メッセージ表示(仮)
		<div id="psms"></div> -->
	<?php echo $k_hmtl; ?>
	</div>
</body>

