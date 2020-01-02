<?php
	//当日出勤者一覧
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
	
	//現在の日付けでのシフト予定の一覧
	date_default_timezone_set('Asia/Tokyo');
	$workday = date("Y-m-d");
	
	$s_work_d = ltrim(date('d', strtotime($workday)), '0');
	$s_work_ym = date('Ym', strtotime($workday));
	
	if (($s_work_d >= 21) && ($s_work_d <= 31)) {
		$s_ym = $s_work_ym;
	} else if (($s_work_d >= 1) && ($s_work_d <=20)) {
		$s_ym = date('Ym', strtotime($s_work_ym.'-1 month'));
	}
	
	$work_member = "";
	$shift_member = "";
	
	$sql = "SELECT * FROM shift_list WHERE s_year_month = ?";
	$rs = $db->prepare($sql);
	$data = array($s_ym);
	$rs->execute($data);
	$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
	
	$s_coad_day = "s_coad_".$s_work_d;
	$s_intime_day_1 = "in_time_".$s_work_d."_1";
	$s_outtime_day_1 = "out_time_".$s_work_d."_1";
	//仮出退勤1のみ表示
	//$s_intime_day_2 = "in_time_".$s_work_d."_2";
	//$s_outtime_day_2 = "out_time_".$s_work_d."_2";
		
	foreach ($rd as $row){

		$s_coad = h($row[$s_coad_day]);
		$s_syain_no = (h($row['syain_no']));
		$s_intime_1 = date('H:i',  strtotime (h($row[$s_intime_day_1])));
		$s_outtime_1 = date('H:i',  strtotime (h($row[$s_outtime_day_1])));
		//仮出退勤1のみ表示
		//$s_intime_2 = date('H:i',  strtotime (h($row[$s_intime_day_2])));
		//$s_outtime_2 = date('H:i',  strtotime (h($row[$s_outtime_day_2])));
		$s_syain = new SYAIN();
		$s_syain->select_sno_syaindata($s_syain_no, $db);
		$s_name = $s_syain->name;
		
		if (isset($s_syain_no)) {
		
			$sql = "SELECT * FROM kintai_list WHERE work_day = ? ";
			$rs = $db->prepare($sql);
			$data = array($workday);
			$rs->execute($data);
			$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
			foreach ($rd as $row){
				$syain_no = h($row['syain_no']);
				//社員Noから名前を取得
				$syain = new SYAIN();
				$syain->select_sno_syaindata($syain_no, $db);
				$name = $syain->name;
				
				if (is_null($row['in_time'])) {
					$intime = "";
				} else {
					$intime = date('H:i',  strtotime(h($row['in_time'])));
				}
				if (is_null($row['f_in_time'])) {
					$f_intime = "";
				} else {
					$f_intime = date('H:i',  strtotime(h($row['f_in_time'])));
				}
				
				if (is_null($row['out_time'])) {
					$outtime = "";
				} else {
					$outtime = date('H:i',  strtotime(h($row['out_time'])));
				}
				if (is_null($row['f_out_time'])) {
					$f_outtime = "";
				} else {
					$f_outtime = date('H:i',  strtotime(h($row['f_out_time'])));
				}
				
				if ($syain_no == $s_syain_no) {
				
					$work_member .= "<tr>";
					$work_member .= "<td>".$syain_no."</td>";
					$work_member .= "<td>".$name."</td>";
					$work_member .= "<td>".$s_coad."</td>";
					$work_member .= "<td>".$intime."</td>";
				//	$work_member .= "<td>".$f_intime."</td>";
					$work_member .= "<td>".$outtime."</td>";
				//	$work_member .= "<td>".$f_outtime."</td>";
					$work_member .= "</tr>";
					
				}
			}
			
			if (isset($s_syain_no)) {
				$shift_member .= "<tr>";
				$shift_member .= "<td>".$s_syain_no."</td>";
				$shift_member .= "<td>".$s_name."</td>";
				$shift_member .= "<td>".$s_coad."</td>";
				$shift_member .= "<td>".$s_intime_1."</td>";
				$shift_member .= "<td>".$s_outtime_1."</td>";
			//	$shift_member .= "<td>".$s_intime_2."</td>";
			//	$shift_member .= "<td>".$s_outtime_2."</td>";
				$shift_member .= "</tr>";
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
<title>当日出勤者一覧</title>
</head>
<?php include 'header.inc'; ?>
<body>
	<div class="breadcrumbs">
		<ul>
			<li><a href="/">猫カフェ 勤怠TOP</a></li>
			<li><a href="/menu/">勤怠メニュー</a></li>
			<li>当日出勤者一覧</li>
		</ul>
	</div>
	<h2>当日出勤者一覧</h2>
	<div>
		<p><?php echo $workday; ?>　シフト一覧</p>
		<table id="shift_member">
			<tr>
				<th>社員番号</th>
				<th>名前</th>
				<th>シフト</th>
				<th>出勤時間</th>
				<th>退勤時間</th>
		<!--	<th>出勤時間2</th> -->
		<!--	<th>退勤時間2</th> -->
			</tr>
<?php echo $shift_member; ?>
	</table>
		<p>現在の出勤者一覧</p>
		<table id="work_member">
			<tr>
				<th>社員番号</th>
				<th>名前</th>
				<th>シフト</th>
				<th>出勤時間</th>
		<!--	<th>仮 出勤時間</th> -->
				<th>退勤時間</th> 
		<!--	<th>仮退勤時間</th> -->
			</tr>
<?php 	echo ($work_member); ?>
		</table>
	</div>
</body>
