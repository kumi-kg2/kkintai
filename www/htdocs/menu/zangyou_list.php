<?php
	//残業登録ページ
	
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
	} else {
		header ("Location: /");
		exit;
	}
	
	$s_no = h($_GET['s_no']);
	$day = h($_GET['day']);

	//社員Noと勤務日から出勤日を取得
	$sql = "SELECT * FROM kintai_list WHERE syain_no = ? AND work_day = ?"; 
	$rs = $db->prepare($sql);
	$data = array($s_no, $day);
	$rs->execute($data);
	$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
	
	$work_day_time = "";
	$work_day_time .= "該当する勤務日時を選択してください<br>";
	foreach ($rd as $row){
		$k_id = h($row['k_id']);
		$work_day = h($row['work_day']);
		$in_time = h($row['in_time']);
		$f_in_time = h($row['f_in_time']);
		$z_time = h($row['z_time']);
		
		if ($in_time == "") {
			$k_time = date('Y-m-d H:i',  strtotime($f_in_time));
		} else if (!($in_time == "")) {
			//出勤時間を打刻済の場合
			$k_time = date('Y-m-d H:i',  strtotime($in_time));
		}
		
		if ($z_time == "") {	
			//	//残業時間未登録の場合
			$work_day_time .= "<p><input type='radio' id ='k_time' name='k_time' value='k".$k_id."'>".$k_time."</p>";
		} else {
			$work_day_time .= "<p><input type='radio' id ='k_time' name='k_time' value='' disabled='disabled'>".$k_time."　残業登録済</p>";
		}
	}
	//勤務データがない場合
	if (!(isset($k_id))) {
		echo $day."の勤務データはありません";
		exit;
	}
	if ($z_time == "") {	
		//残業時間未登録の場合
		$work_day_time .= "<p>残業時間：<input type='number' id='z_time' name='z_time' min='30' max='600' step='30'>分(30分単位で入力)</p>";
		$work_day_time .= "<p>内容<br><textarea name='biko' id='biko' cols='40' rows='8'></textarea></p>";
	//	$work_day_time .="<input type ='submit' value = '残業登録'>";
		$work_day_time .="<button class='mbt'>残業登録</button>";
	} 

?>
<form action = "zangyou_comp.php" method = "post">
<?php  echo $work_day_time; ?>

</form>
