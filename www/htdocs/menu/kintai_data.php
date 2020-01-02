<?php

	//入力済勤怠データ表示(ひと月)
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
	
	foreach ($rd as $row){
		$k_id = h($row['k_id']);
		$syain_no = h($row['syain_no']);
		$work_day = h($row['work_day']);
		$outtime = h($row['out_time']);
		$f_outtime = h($row['f_out_time']);
		
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
		
		//シフトの勤務時間を表示する
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
					$syain_no,
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
		}

		
		if ((!($now_day == $work_day)) && (($intime == "") or ($outtime == ""))) {
			$kintai_data .= "<tr bgcolor='#BAD3FF'>";
			$kintai_data .= "<td id='k_id".$k_id."'>".$work_day."</td>";
			$kintai_data .= "<td>".$s_coad."</td>";
			$kintai_data .= "<td>".$in_time1."～".$out_time1."</td>";
			$kintai_data .= "<td>".$intime."</td>";
			$kintai_data .= "<td>".$f_intime."</td>";
			$kintai_data .= "<td>".$outtime."</td>";
			$kintai_data .= "<td>".$f_outtime."</td>";
			$kintai_data .= "</tr>";
		} else {
			$kintai_data .= "<tr>";
			$kintai_data .= "<td id='k_id".$k_id."'>".$work_day."</td>";
			$kintai_data .= "<td>".$s_coad."</td>";
			$kintai_data .= "<td>".$in_time1."～".$out_time1."</td>";
			$kintai_data .= "<td>".$intime."</td>";
			$kintai_data .= "<td>".$f_intime."</td>";
			$kintai_data .= "<td>".$outtime."</td>";
			$kintai_data .= "<td>".$f_outtime."</td>";
			$kintai_data .= "</tr>";
		}
	}

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css" href="menu.css">
<title>勤怠データ</title>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.js"></script>
<script type="text/javascript" src="js/kintai_forget.js"></script>
</head>
<?php include 'header.inc'; ?>
<body>
	<div class="breadcrumbs">
		<ul>
			<li><a href="/">猫カフェ 勤怠TOP</a></li>
			<li><a href="/menu/">勤怠メニュー</a></li>
			<li><a href="/menu/kintai_forget2.php">打刻修正登録</a></li>
			<li>勤怠データ</li>
		</ul>
	</div>
	<h2>勤怠データ</h2>
	<div id="kitaidata">
		<p><?php  echo $now_day; ?>からひと月前まで入力済み勤怠データ<br>
		※打刻忘れがある場合や打刻修正が未認証の日付は<br>
		色付きで表示されています</p>
		<table border="1">
			<tr>
				<th>出勤日</th>
				<th>シフト</th>
				<th>勤務時間</th>
				<th>出勤打刻時間</th>
				<th>打刻忘れ<br>出勤時間</th>
				<th>退勤打刻時間</th>
				<th>打刻忘れ<br>退勤時間</th>
			</tr>
<?php echo $kintai_data; ?>
		</table>
	</div>
</body>
