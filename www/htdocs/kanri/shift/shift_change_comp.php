<?php
//シフト修正ページ

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

	
	date_default_timezone_set('Asia/Tokyo');
	$up_date = date("YmdHis");
	
	$week = array (
				'日',
				'月',
				'火',
				'水',
				'木', 
				'金', 
				'土'
			);
	
	$s_id =	h($_GET['s_id']);
	$s_year_month = h($_GET['ym']);
	$s_year = substr($s_year_month, 0 , 4);
	$s_month = substr($s_year_month, -2);
	//0なし日付
	$s_day = h(ltrim($_GET['day'], '0'));
	//0あり日付
	$ss_day = h($_GET['day']);

	$sql = "SELECT * FROM shift_list WHERE s_id = ?";
	$rs = $db->prepare($sql);
	$data = array($s_id);
	$rs->execute($data);
	$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
	foreach ($rd as $row) {
		//社員NOから名前を取得
		$syain = new SYAIN();
		$syain->select_sno_syaindata(h($row['syain_no']), $db);
		$s_no = h($row['syain_no']);
		$s_name = $syain->name;		
	}
	
	$s_ymd = $s_year."-".$s_month."-".$ss_day;

	//曜日
	$shift_week = new DateTime($s_ymd);
	$w = (int)$shift_week->format('w');
	$date_week = $week[$w];
	
	$change_shift_day ="<p>社員No：".$s_no."　名前：".$s_name."<br>".$s_ymd."(".$date_week.")のシフトを修正しました</p>";
	
	//シフトコードが休以外の場合
	if (!($_POST['s_coad'] == "H")) {
	
		$s_coad = $_POST['s_coad'];
		// シフトマスタから勤怠時間を取得する
		$sql = "SELECT * FROM shift_master WHERE shift_coad = ?";
		$rs = $db->prepare($sql);
		$data = array($s_coad);
		$rs->execute($data);
		$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
		foreach ($rd as $row) {
			$shift_in_time1 = $s_ymd." ".h($row['shift_in_time_1']);
			$shift_out_time1 = $s_ymd." ".h($row['shift_out_time_1']);
			$shift_in_time2 = $s_ymd." ".h($row['shift_in_time_2']);
			$shift_out_time2 = $s_ymd." ".h($row['shift_out_time_2']);
		}
	//シフトコードが休の場合
	} else {
		$s_coad = "H";
		$shift_in_time1 = $s_ymd." 00:00:00";
		$shift_out_time1 = $s_ymd." 00:00:00";
		$shift_in_time2 = $s_ymd." 00:00:00";
		
		$shift_out_time2 = $s_ymd." 00:00:00";
	}
	
	$s_coad_day = "s_coad_".$s_day;
	$in_time1_day = "in_time_".$s_day."_1";
	$out_time1_day = "out_time_".$s_day."_1";
	$in_time2_day = "in_time_".$s_day."_2";
	$out_time2_day = "out_time_".$s_day."_2";
	
	//修正したいシフトコードに変更する
	$sql2 = "UPDATE shift_list SET ".$s_coad_day."= ?, ".$in_time1_day."= ?, ".$out_time1_day."= ?, ".$in_time2_day."= ?, ".$out_time2_day."= ?, ";
	$sql2 .= "updated =? ";
	$sql2 .= "WHERE s_id = ?";
	
	$rs2 = $db->prepare($sql2);
	$data2 = array(
				$s_coad,
				$shift_in_time1,
				$shift_out_time1,
				$shift_in_time2,
				$shift_out_time2,
				$up_date,
				$s_id
			);
	$rs2->execute($data2);
	$rd2 = $rs2->fetchAll(PDO::FETCH_ASSOC);
	
	
?>
<!DOCTYPE html> 
<html>
<head>
<meta charset="utf-8">
<link rel="stylesheet" type="text/css" href="/kanri/kanri.css"> 
<title>シフト修正完了</title>
<?php include 'header.inc'; ?>
</head>
<body>
	<div class="breadcrumbs">
		<ul>
			<li><a href="/">猫カフェ 勤怠TOP</a></li>
			<li><a href="/kanri/">管理者メニュー</a></li>
			<li><a href="/kanri/shift/">シフト管理</a></li>
			<li><a href="/kanri/shift/k_shift.php">シフト一覧</a></li>
			<li>シフト修正完了</li>
		</ul>
	</div>
	<div>
	<h2>シフト修正完了</h2>
<?php echo $change_shift_day;?>
	</div>
</body>

