<?php
// 	勤怠IDを取得して出退勤時間表示させる→修正

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

	$k_id = $_GET['k_id'];
	$sql = "SELECT * FROM kintai_list WHERE k_id = ?";
	$rs = $db->prepare($sql);
	$data = array($k_id);
	$rs->execute($data);
	$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
	$up_kintai_data = "";
	foreach ($rd as $row){
		$in_time = h($row['in_time']);
		$f_in_time = h($row['f_in_time']);
		$out_time = h($row['out_time']);
		$f_out_time = h($row['f_out_time']);
		$z_time = h($row['z_time']);
		$biko = h($row['biko']);

		//出勤時間を打刻忘れした場合
		if ($in_time == "") {
			$k_intime = date('Y-m-d\TH:i',  strtotime($f_in_time));
		} else if (!($in_time == "")) {
		//出勤時間を打刻済の場合
			$k_intime = date('Y-m-d\TH:i',  strtotime($in_time));
		}
				
		if (($out_time == "") && ($f_out_time == "")) {
		//退勤時間未入力の場合
			$k_outtime = "";
		} else if ($out_time == "") {
		//退勤時間を打刻忘れした場合
			$k_outtime = date('Y-m-d\TH:i',  strtotime($f_out_time));
		} else if (!($out_time == "")) {
		//退勤時間を打刻済の場合
			$k_outtime = date('Y-m-d\TH:i',  strtotime($out_time));
		} 
	//	$up_kintai_data .= "<p>勤怠　ID：<input type='text' id='up_kid' name='up_kid' size='30' maxlength='20' value='".h($row['k_id'])."'></p>";
	//	$up_kintai_data .= "<p>社員番号：<input type='text' id='up_sno' name='up_sno' size='30' maxlength='20' value='".h($row['syain_no'])."'></p>";
	//	$up_kintai_data .= "<p>　日　付：<input type='text' id='up_day' name='up_day' size='30' maxlength='20' value='".h($row['work_day'])."'></p>";
		$up_kintai_data .= "<p>出勤時間：<input type='datetime-local' id='up_in_time' name='up_in_time' value='".$k_intime."'></p>";
		$up_kintai_data .= "<p>退勤時間：<input type='datetime-local' id='up_out_time' name='up_out_time'  value='".$k_outtime."'><br>";
		$up_kintai_data .= "<p>残業時間：<input type='number' id='up_z_time' name='up_z_time'  value='".$z_time."' min='0' max='600' step='30'><br>";
		$up_kintai_data .= "<p>備考：<textarea id='up_biko' name='up_biko'  cols='40' rows='8'>".$biko."</textarea></p>";
	}
?>
	<form method ="post" action="up_kintai_comp.php?k_id=<?php echo $k_id; ?>">
<?php echo $up_kintai_data; ?>
	<p><button class='kbt' name="tourokub" id="tourokub">勤怠時間修正</button></p>
	</form>
	<p><button class='kbt' name="deleteb" id="deleteb">勤怠データ削除</button></p>
