<?php
	// 個人シフト確認ページ
	// 認証されたcookieのみアクセス可(IPアドレスは指定なし)
	// 出退勤2は表示しない

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
	// Cookieあり(DB内と一致)・IPアドレスNG・認証許可FLG1→アクセスOK
	} else if (($a_check == 2 ) && ($cf_check == 1)) {
		//Cookieから社員Noを取得
		$syain_no = (checkCookie($cookie, $db))->syain_no;
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
				$date .= "<th class='thday' id='d".$in_date1."'>".$in_date1."<br>(".$date_week.")</th>";
				$shift_time .="<td class='tdshift' id='sid".$s_id."_d".$in_date1."' style='font-size: 10pt;'>".$shift_coad."<hr>".$in_time1;
			}
			//退勤1の時間
			if ((strpos($key , 'out_time') !== false) && (preg_match("/_1$/",$key))) {
				//退勤1の時間取得
				$out_time1 = str_replace('00:00', '&nbsp;', (substr((h($val)), 10, 6)));
				$shift_time .=$out_time1;
			}
			//出勤2の時間
			if ((strpos($key , 'in_time') !== false) && (preg_match("/_2$/",$key))) {
				$in_date2 = substr((h($val)), 8, 2);
				//退勤1の時間取得
				$in_time2 = str_replace('00:00', '&nbsp;', (substr((h($val)), 10, 6)));
			//	$shift_time .="<hr>".$in_time2;
			}
			//退勤2の時間
			if ((strpos($key , 'out_time') !== false) && (preg_match("/_2$/",$key))) {
				$out_time2 = str_replace('00:00', '&nbsp;', (substr((h($val)), 10, 6)));
			//	$shift_time .= $out_time2."</td>";
			}
		}
		$shift_time .= "</tr>";
	}
	$shift_data .= "<table class='shift_data' id='shift_data' border='1'><th class='thname'>名前</th>".$date.$shift_time."</table>";


?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>シフト確認</title>
<link rel="stylesheet" type="text/css" href="menu.css">
<link rel="stylesheet" type="text/css" href="shift.css">
<script src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.js"></script>
<script>
$(function(){
	//日付が00になるものは非表示(月末が31日までない場合00になる)
    $("#shift_data [id $= 'd00']").hide();

	$(window).load(function() {
		//土日の場合背景色変更
		$('th:contains("土")').css("background-color", "#D9E5FF");
		$('th:contains("日")').css("background-color", "#FFDBC9");
	});
});
</script>
<?php include 'header.inc'; ?>
</head>
<body>
	<div class="breadcrumbs">
		<ul>
			<li><a href="/">猫カフェ 勤怠TOP</a></li>
			<li><a href="/menu/">勤怠メニュー</a></li>
			<li>シフト確認</li>
		</ul>
	</div>
	<div>
		<h2>今月度シフト</h2>
			<p><a href="kyuyo_conf.php"><button class='mbt'>今月度の給与確認</button></a></p>
			<p><a href= "shift_list.php?m_shift=<?php echo $l_y_month;?>" >≪<?php echo $l_y_month ?>月度</a>
			　<?php echo $y_month; ?>月度シフト　<a href= "shift_list.php?m_shift=<?php echo $n_y_month;?>">≫<?php echo $n_y_month ?>月度</a></p>
<?php echo $shift_data;  ?>
	</div>
</body>
