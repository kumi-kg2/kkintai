<?php
//管理者　シフト確認ページ
//先月・翌月のシフトを表示・修正する
//出退勤時間2は表示しない

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
	
	$m_shift_data = "";
	
	$week = array (
				'日',
				'月',
				'火',
				'水',
				'木', 
				'金', 
				'土'
			);

	//月間シフトの場合
	if (isset($_GET['m_shift'])) {
		
		$m_shift = $_GET['m_shift'];
		$m_shiftday = $m_shift."/21";
		
		$month = str_replace("-", "",$m_shift);
		
		//指定された月のシフトがあるかを確認する
		$sql = "SELECT COUNT(*) AS cnt FROM shift_list WHERE s_year_month = ?";
		$rs = $db->prepare($sql);
		$data = array($month);
		$rs->execute($data);
		$rd = $rs->fetchAll(PDO::FETCH_ASSOC);

		foreach ($rd as $row){
			$cnt = $row["cnt"];
			if ($cnt == 0) {
				echo "指定された".$m_shift."月度のシフトはありません";
				exit;
			} else if ($cnt > 0 ) {
				
				$sql = "SELECT * FROM shift_list WHERE s_year_month = ?";
				$rs = $db->prepare($sql);
				$data = array($month);
				$rs->execute($data);
				$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
				
				$shift_time ="";
						
				foreach ($rd as $row) {
					$date = "";
					$no = "";
					$s_coad = "";
					$s_no = $row['syain_no'];
					$s_id = $row['s_id'];
					
					//社員NOから名前を取得
					$syain = new SYAIN();
					$syain->select_sno_syaindata($s_no, $db);
					$s_name = $syain->name;
					
					$no .= "<td class='tdname' style='font-size: 10pt;'>".$s_no."<hr>".$s_name."</td>";
					$shift_time .="<tr>".$no;
					
					foreach ($row as $key=>$val) {
						//シフトコード
						if (strpos($key , 's_coad') !== false) {
							$shift_coad = "";
							if ($val == NULL) {
								$shift_coad .= "H";
							} else {
								$shift_coad .= h($val);
							}
						}
						//出勤1の時間
						if ((strpos($key , 'in_time') !== false) && (preg_match("/_1$/",$key))) {
							//シフトの日にち取得
							$in_date1 = substr((h($val)), 8, 2);
							//出勤1の時間取得
							$in_time1 = str_replace('00:00', '&nbsp;<br>&nbsp;', (substr((h($val)), 10, 6)));

							//21～月末までの曜日
							if (($in_date1 >= 21) &&  ($in_date1 <= 31)) {
								$month_day = $month.$in_date1;
								$shift_week = new DateTime($month_day);
								$w = (int)$shift_week->format('w');
								$date_week = $week[$w];
							//1～20日までの曜日
							} else if (($in_date1 >= 1) && ($in_date1 <=20)) {
								$firstday = date($m_shift."-1");
								//シフト月の翌月(1～20日の月)
								$next_month = date('Ym', strtotime($firstday.'+1 month'));
								$month_day = $next_month.$in_date1;
								$shift_week = new DateTime($month_day);
								$w = (int)$shift_week->format('w');
								$date_week = $week[$w];
							}
							$date .= "<th class='thday' id='d".$in_date1."'>".$in_date1."<br>(".$date_week.")</th>";
							$shift_time .="<td class='tdshift' id='sid".$s_id."_d".$in_date1."' style='font-size: 10pt;' >".$shift_coad."<hr>".$in_time1;
						}
						//退勤1の時間
						if ((strpos($key , 'out_time') !== false) && (preg_match("/_1$/",$key))) {
							//退勤1の時間取得
							$out_time1 = str_replace('00:00', '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', (substr((h($val)), 10, 6)));
							$shift_time .=$out_time1;
						}
						//出勤2の時間
						if ((strpos($key , 'in_time') !== false) && (preg_match("/_2$/",$key))) {
							$in_date2 = substr((h($val)), 8, 2);
							//出勤2の時間取得
							$in_time2 = str_replace('00:00', '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', (substr((h($val)), 10, 6)));
						//	$shift_time .="<hr>".$in_time2;
						}
						//退勤2の時間
						if ((strpos($key , 'out_time') !== false) && (preg_match("/_2$/",$key))) {
							$out_time2 = str_replace('00:00', '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', (substr((h($val)), 10, 6)));
						//	$shift_time .= $out_time2."</td>";
						}
					}
					$shift_time .= "</tr>";
				}
				$m_shift_data .= "<p>".$m_shift."月度シフト一覧</p>";
				$m_shift_data .= "<table id='shift_data' class='shift_data' ><th class='thname'>名前</th>".$date.$shift_time."</table>";
			}
		}
	} else {
		$m_shift_data .= "<p>".$m_shift."月度のシフトデータはありません</p>";
	}
?>
<!DOCTYPE html>    
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>シフト一覧</title>
<link rel="stylesheet" type="text/css" href="/menu/shift.css">
<link rel="stylesheet" type="text/css" href="/kanri/kanri.css">
<script src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.js"></script>
<script type="text/javascript" src="js/k_shift.js"></script>
<?php include 'header.inc'; ?>
</head>
<body>
	<div class="breadcrumbs">
		<ul>
			<li><a href="/">猫カフェ 勤怠TOP</a></li>
			<li><a href="/kanri/">管理者メニュー</a></li>
			<li><a href="/kanri/shift/">シフト管理</a></li>
			<li><a href="/kanri/shift/k_shift.php">シフト一覧</a></li>
			<li><?php echo $m_shift; ?>月度シフト</li>
		</ul>
	</div>
	<h2>シフト一覧</h2>
<?php echo $m_shift_data;  ?>
	<p>修正したいシフトがある場合は該当する<br>シフトコードをクリックしてください</p>
	<div id="change_shiftarea"></div>
</body>
