<?php
	//管理者 今月シフト確認・修正ページ
	//時刻表示なし

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

	$shift_data = "";
	
	$week = array (
				'日',
				'月',
				'火',
				'水',
				'木', 
				'金', 
				'土'
			);

	//今日の日付を取得する
	date_default_timezone_set('Asia/Tokyo');
	$now_date = date("Y-m-d");
	$now_day = date("d");
	
	//〇月度
	if (($now_day >= 21) && ($now_day <= 31)) {
		$now_ymonth = date("Ym");
		$y_month = date("Y-m");
		$n_y_month = date("Y-m", strtotime('+1 month'));
		$l_y_month = date("Y-m", strtotime('-1 month'));
	} else if (($now_day >= 1) && ($now_day <=20)) {
		$now_ymonth = date("Ym", strtotime('-1 month'));
		$y_month = date("Y-m", strtotime('-1 month'));
		$n_y_month = date("Y-m");
		$l_y_month = date("Y-m", strtotime('-2 month'));
	}
	
	//今日の日付があるのシフトを確認する
	$sql = "SELECT * FROM shift_list WHERE s_year_month = ?";
	$rs = $db->prepare($sql);
	$data = array($now_ymonth);
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
					
		$no .= "<td style='font-size: 8pt;'>".$s_name."<br>(".$s_no.")</td>";
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
				$in_time1 = str_replace('00:00', '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', (substr((h($val)), 10, 6)));

				//21～月末までの曜日
				if (($in_date1 >= 21) &&  ($in_date1 <= 31)) {
					$month_day = $now_ymonth.$in_date1;
					$shift_week = new DateTime($month_day);
					$w = (int)$shift_week->format('w');
					$date_week = $week[$w];
				//1～20日までの曜日
				} else if (($in_date1 >= 1) && ($in_date1 <=20)) {
					$firstday = date($now_ymonth."-1");
					//シフト月の翌月(1～20日の月)
					$next_month = date('Ym', strtotime($firstday));
					$month_day = $next_month.$in_date1;
					$shift_week = new DateTime($month_day);
					$w = (int)$shift_week->format('w');
					$date_week = $week[$w];
				}
				$date .= "<th class='thday' id='d".$in_date1."' style='font-size: 7pt;'>".$in_date1."<br>(".$date_week.")</th>";
			//	$shift_time .="<td align='center' class='tdshift' id='sid".$s_id."_d".$in_date1."' style='font-size: 10pt;'>".$shift_coad."<hr>".$in_time1;
				$shift_time .="<td align='center' class='tdshift' id='sid".$s_id."_d".$in_date1."' style='font-size: 10pt;'>".$shift_coad;
			}
			//退勤1の時間
			if ((strpos($key , 'out_time') !== false) && (preg_match("/_1$/",$key))) {
				//退勤1の時間取得
				$out_time1 = str_replace('00:00', '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', (substr((h($val)), 10, 6)));
			//	$shift_time .=$out_time1;
			}
			//出勤2の時間
			if ((strpos($key , 'in_time') !== false) && (preg_match("/_2$/",$key))) {
				$in_date2 = substr((h($val)), 8, 2);
				//退勤1の時間取得
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
	$shift_data .= "<table id='shift_data' border='1'><th class='thname' style='font-size: 10pt;'>名前</th>".$date.$shift_time."</table>";

?>
<!DOCTYPE html>   
<html>
<head>
<meta charset="utf-8">
<title>シフト一覧</title>
<link rel="stylesheet" type="text/css" href="/menu/shift.css">
<script src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.js"></script>
<script type="text/javascript" src="js/k_shift.js"></script>
<body>
	<div>
		<h2>シフト一覧</h2>
			<p><?php echo $l_y_month ?>月度<a href= "k_shift_list.php?m_shift=<?php echo $l_y_month;?>" >≪</a>　<?php echo $y_month; ?>月度シフト
			　<a href= "k_shift_list.php?m_shift=<?php echo $n_y_month;?>">≫</a><?php echo $n_y_month ?>月度<br>
			過去のシフトを確認する場合は<a href= "shift_past.php">こちら</a></p>
	<?php echo $shift_data;  ?>
		<p>修正したいシフトがある場合は該当する<br>シフトコードをクリックしてください</p>
		<div id="change_shiftarea"></div>
		<p><a href="/kanri/shift">≪ シフト管理ページ</a></p>
	</div>
</body>

