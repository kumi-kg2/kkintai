<?php
//年月を指定してそのデーターを印刷する
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
	$startday = h($_POST['startday']);
	$lastday = h($_POST['lastday']);	
	
	//社員Noから名前を取得
	$syain = new SYAIN();
	$syain->select_sno_syaindata($s_no, $db);
	$name = $syain->name;
	
	$kintai_data ="";
	$kintai_data_html ="";

	//指定された年月からの勤怠のデータを表示する
	$sql = "SELECT * FROM kintai_list WHERE (work_day BETWEEN ? AND ?) AND syain_no = ? ORDER BY work_day, in_time, f_in_time ASC";
	$rs = $db->prepare($sql);
	$data = array(
				$startday,
				$lastday,
				$s_no
				);
	$rs->execute($data);
	$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
	foreach ($rd as $row){
		$k_id = h($row['k_id']);
		$work_day = h($row['work_day']);
		$z_time = h($row['z_time']);
		$biko = h($row['biko']);
		
		if (is_null($row['in_time'])) {
			$intime = "";
		} else {
		//	$intime = date('H:i',  strtotime(h($row['in_time'])));
			$intime = (h($row['in_time']));
		}
		if (is_null($row['f_in_time'])) {
			$f_intime = "";
		} else {
		//	$f_intime = date('H:i',  strtotime(h($row['f_in_time'])));
			$f_intime = (h($row['f_in_time']));
		}
				
		if (is_null($row['out_time'])) {
			$outtime = "";
		} else {
		//	$outtime = date('H:i',  strtotime(h($row['out_time'])));
			$outtime = (h($row['out_time']));
		}
		if (is_null($row['f_out_time'])) {
			$f_outtime = "";
		} else {
		//	$f_outtime = date('H:i',  strtotime(h($row['f_out_time'])));
			$f_outtime = (h($row['f_out_time']));
		}
		
/*		//シフトの勤務時間を表示する
		$w_day = ltrim((substr($work_day, -2)) ,'0');
				
		$s_coad_db = "s_coad_".$w_day;
		$in_time1_db = "in_time_".$w_day."_1";
		$out_time1_db = "out_time_".$w_day."_1";
		$in_time2_db = "in_time_".$w_day."_2";
		$out_time2_db = "out_time_".$w_day."_2";
				
		if (($w_day >= 21) && ($w_day <= 31)) {
			$s_ym = date("Ym",strtotime($work_day));
		} else if (($w_day >= 1) && ($w_day <=20)) {
			$s_ym = date("Ym", strtotime($work_day.'-1 month'));
		}
		$sql2 = "SELECT * FROM shift_list WHERE syain_no = ? AND s_year_month = ? ";
		$rs2 = $db->prepare($sql2);
		$data2 = array(
					$s_no,
					$s_ym
				);
		$rs2->execute($data2);
		$rd2 = $rs2->fetchAll(PDO::FETCH_ASSOC);
		foreach ($rd2 as $row2){
			foreach ($row2 as $key=>$val) {
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
		}*/
		
		$kintai_data .= "<tr>";
		$kintai_data .= "<td>".$work_day."</td>";
//		$kintai_data .= "<td>".$s_coad."</td>";
//		$kintai_data .= "<td>".$in_time1."～".$out_time1."</td>";
		$kintai_data .= "<td>".$intime."</td>";
		$kintai_data .= "<td>".$f_intime."</td>";
		$kintai_data .= "<td>".$outtime."</td>";
		$kintai_data .= "<td>".$f_outtime."</td>";
		$kintai_data .= "<td>".$z_time."</td>";
//		$kintai_data .= "<td>".$biko."</td>";
		$kintai_data .= "</tr>";
	}
//	$kintai_data_html .= "<table><tr><th>出勤日</th><th>シフト</th><th>勤務時間</th><th>出勤時間</th><th>打刻忘れ<br>出勤時間</th>";
	$kintai_data_html .= "<table><tr><th>出勤日</th><th>出勤打刻時間</th><th>打刻忘れ<br>出勤時間</th>";
//	$kintai_data_html .= "<th>退勤時間</th><th>打刻忘れ<br>退勤時間</th><th>残業時間</th><th>備考</th></tr>";
	$kintai_data_html .= "<th>退勤打刻時間</th><th>打刻忘れ<br>退勤時間</th><th>残業時間</th></tr>";
	$kintai_data_html .= $kintai_data;
	$kintai_data_html .= "</table>";
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css" href="/kanri/kanri.css">
<script src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.js"></script>
<title>勤怠データ</title>
</head>
<?php include 'header.inc'; ?>
<body>
	<div class="breadcrumbs">
		<ul>
			<li><a href="/">猫カフェ 勤怠TOP</a></li>
			<li><a href="/kanri/">管理者メニュー</a></li>
			<li><a href="/kanri/kintai/">勤怠時間管理</a></li>
			<li><a href="/kanri/kintai/kintai_kanri.php">勤怠時間修正</a></li>
			<li><a href="/kanri/kintai/kintai_list.php?s_no=<?php echo $s_no ?>">勤怠リスト</a></li>
			<li>勤怠データ</li>
		</ul>
	</div>
	<h2>勤怠リスト</h2>
	<div>
		<p>社員番号：<?php echo $s_no; ?>　名前：<?php echo $name; ?></p>
		<?php echo $kintai_data_html; ?>
	</div>
</body>
