<?php
	//過去の給与予定・実際の勤怠データによる給与予定表示
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
	
	$k_id_ym = "";
	$now_ym = "";
	$work_day21 = "";
	$k_ym = "";
	$y_work_day = "";
	
	if (isset($_GET['ym'])) {
		$k_id_ym .= h($_GET['ym']);
		$now_ym .= substr($k_id_ym, 0, -2);
		$work_day21 .= date('Y-m-d',  strtotime($k_id_ym));
		$k_ym .= date("Y-m", strtotime($k_id_ym));
		$y_work_day .= date("Y-m-20", strtotime($k_id_ym . '+1 month'));
	} else if ($_POST['m_kyuyo']) {
		$k_id_ym .= date('Ym',  strtotime(h($_POST['m_kyuyo'])))."21";
		$now_ym .= date('Ym',  strtotime($k_id_ym));
		$work_day21 .= date('Y-m-21',  strtotime($k_id_ym));
		$k_ym .= date("Y-m", strtotime($k_id_ym));
		$y_work_day .= date("Y-m-20", strtotime($k_id_ym . '-1 month'));
	}
	
	$s_no = h($_GET['s_no']);
	
	//前のページに戻るためのID
	$url_k_id = h($_GET['k_id']);
	
	$syain = new SYAIN();
	$syain->select_sno_syaindata($s_no, $db);
	$name = $syain->name;
	
	
	//年月に当てはまるID
	$sql = "SELECT * FROM kyuyo_list WHERE kyuyo_id <= ? AND syain_no = ? ORDER BY kyuyo_id DESC LIMIT 1";
	$rs = $db->prepare($sql);
	$data = array($k_id_ym, $s_no);
	$rs->execute($data);
	$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
	foreach ($rd as $row) {
		$k_id = h($row['kyuyo_id']);
		$kubun = h($row['kubun']);
		$k_tanka = h($row['kihon_tanka']);
		$z_tanka = h($row['zangyou_tanka']);
	}

	
	$kyuyodata ="";
	$kyuyo_html ="";
	//〇月度のシフト上の出勤日数をしらべる
	$sql2 = "SELECT * FROM shift_list WHERE syain_no = ? AND s_year_month = ? GROUP BY syain_no";
	$rs2 = $db->prepare($sql2);
	$data2 = array($s_no, $now_ym);
	$rs2->execute($data2);
	$rd2 = $rs2->fetchAll(PDO::FETCH_ASSOC);
		foreach ($rd2 as $row2) {
			$s_id = $row2['s_id'];
			$s_coad_db ="";
			$work_time = 0;
			
			foreach ($row2 as $key=>$val) {
				if (strpos($key , 's_coad') !== false) {
				
					if (!($val == "H")) {
						$s_coad = $val;
						$s_coad_db .= $val.",";
						$s_coad_array = substr($s_coad_db, 0, -1);
						$s_array = explode(',',$s_coad_array);
						//勤務日数
						$cunt = count($s_array, COUNT_RECURSIVE);

						// 勤務時間
						$sql = "SELECT * FROM shift_master WHERE shift_coad = ?";
						$rs = $db->prepare($sql);
						$data = array($s_coad);
						$rs->execute($data);
						$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
						foreach ($rd as $row) {
							$work_time += intval(h($row['work_time']));
						}
					}
				}
			}
				
		if (isset($s_id)) {
			//給与
			//仮 給与区分【月給・時給・日給】
			if ($kubun == "月給") {
				$kyuyo = $k_tanka;
			} else if ($kubun== "時給") {
				$kyuyo = $k_tanka * ($work_time / 10);
			} else if ($kubun == "日給") {
				$kyuyo = $k_tanka * $cunt;
			}
		}
	}
	if ((isset($k_id)) && (isset($s_id))) {
		$kyuyodata .= "<div id='kyuyo_data' style='padding: 10px; margin-bottom: 10px; border: 1px solid #333333; border-radius: 10px; width:300px;'>";
		$kyuyodata .= "社員番号：".$s_no."　名前：".$name."<br>".$k_ym."月度　";
		$kyuyodata .= "給与区分【".$kubun."】<br>基本給【".number_format($k_tanka)."】<br>残業代【".number_format($z_tanka)."】</div>";
		$kyuyodata .= "<div id='shift_data' style='padding: 10px; margin-bottom: 10px; border: 1px solid #333333; border-radius: 10px; width:300px;'>";
		$kyuyodata .= "シフト上のデータ<br>勤務日数：".$cunt."　勤務時間：".$work_time."<br>給与予定額：".number_format($kyuyo)."</div>";
	} else {
		$kyuyodata .= "<div id='kyuyo_data' style='padding: 10px; margin-bottom: 10px; border: 1px solid #333333; border-radius: 10px; width:300px;'>";
		$kyuyodata .= "社員番号：".$s_no."　名前：".$name."<br>".$k_ym."月度<br>";
		$kyuyodata .= "給与区分がまだ登録されていません</div>";
	}
	
	$now_kyuyodata = "";
	//勤怠上の出勤日数・時間を調べる
	$sql3 = "SELECT * FROM kintai_list WHERE (work_day BETWEEN ? AND ?) AND syain_no = ?";
	$rs3 = $db->prepare($sql3);
	$data3 = array(
				$work_day21,
				$y_work_day,
				$s_no
			);
	$rs3->execute($data3);
	$rd3 = $rs3->fetchAll(PDO::FETCH_ASSOC);
	
	$work_time = 0;
	$zangyou_time = 0;
	$un_zangyou_time = 0;
	
	$z_ms = "";
	$k_kyuyo = 0;
	$z_kyuyo = 0;
	$g_kyuyo = 0;
	$k_day = 0;
	$w_hour2 = 0;

	foreach ($rd3 as $row3) {
		$work_day = h($row3['work_day']);
		
		if ((isset($row3['in_time'])) && (isset($row3['out_time']))) {
			$in_time = date('Y-m-d H:i',  strtotime(h($row3['in_time'])));
			$out_time = date('Y-m-d H:i',  strtotime(h($row3['out_time'])));
			
			$hour = (strtotime($out_time) - strtotime($in_time)) / 3600;
			$w_hour = round($hour,2);
						
			//勤務時間6時間以上の場合は休憩1時間
			if ($w_hour >= 6) {
				$work_time += $w_hour;
				$work_time -= 1;
			} else if ($w_hour < 6) {
				$work_time += $w_hour;
			}
			
		} else { 
			//出勤・退勤のどちかが打刻されていない場合
			//勤務時間0・出勤カウントしない
			$n_hour = 0;
			$work_time += $n_hour;
			$k_day -= 1;
		}
		$z_time = h($row3['z_time']);
		$permission = h($row3['permission']);
		
		//勤務日数
		$k_day += 1;

		//同じ勤務日があるかを確認する
		$sql = "SELECT COUNT(*) AS cnt FROM kintai_list WHERE (work_day BETWEEN ? AND ?) AND syain_no = ? AND work_day = ?";
		$rs = $db->prepare($sql);
		$data = array(
				$y_work_day,
				$work_day21,
				$s_no,
				$work_day
				);
		$rs->execute($data);
		$rd = $rs->fetchAll(PDO::FETCH_ASSOC);

		foreach ($rd as $row){
			$cnt = $row["cnt"];
			//同じ勤務日があるときに勤務日が重複しないように0.5でカウント
			//勤務合計時間が6時間以上の場合は休憩1時間
			if ($cnt == 2) {
				$k_day -= 0.5;
				$w_hour2 += $w_hour;
				if ($w_hour2 >=6 ) {
					$work_time -= 1;
				}
			}
		}
		
		//残業時間・残業代　認証されていたら反映させる
		if ((!($z_time == "")) && ($permission == "1")) {
			//残業認証済(FLG=1)の場合
			$z_min = $z_time / 60;
			$z_hour = round($z_min,2);
			$zangyou_time += $z_hour;
			//仮で小数点以下四捨五入
			$z_kyuyo += round($z_tanka * $z_hour);
			$z_ms .= "";
		} else if ((!($z_time == "")) && ($permission == "0")) {
			//残業未認証(FLG=0)の場合
			$z_min = $z_time / 60;
			$z_hour = round($z_min,2);
			$un_zangyou_time +=  $z_hour;
			$z_kyuyo += 0;
			
			$z_ms .= "<p>未承認の残業時間が".$un_zangyou_time."時間あります<br>";
			$z_ms .= "承認する場合は<a href='/kanri/zangyou/zangyou_kanri.php'>こちら</a></p>";
		}
		
		$work_time -= $zangyou_time;
		$work_time -= $un_zangyou_time;
	}
	
	// 基本給
	//仮 給与区分【月給・時給・日給】
	//仮で小数点以下四捨五入
	if (isset($kubun)) {

		if ($kubun == "月給") {
			$k_kyuyo = $k_tanka;
		} else if ($kubun == "時給") {
			$k_kyuyo = round($k_tanka * $work_time);
		} else if ($kubun == "日給") {
			$k_kyuyo = round($k_tanka * $k_day);
		}
		//合計支給額
		$g_kyuyo += $k_kyuyo + $z_kyuyo;
	}
	
	$now_kyuyodata .="<tr>";
	$now_kyuyodata .="<td>".$k_day."</td>";
	$now_kyuyodata .="<td>".$work_time."</td>";
	$now_kyuyodata .="<td>".number_format($k_kyuyo)."</td>";
	$now_kyuyodata .="<td>".number_format($zangyou_time)."</td>";
	$now_kyuyodata .="<td>".number_format($z_kyuyo)."</td>";
	//合計金額は仮で小数点以下四捨五入
	$now_kyuyodata .="<td>".number_format(round($g_kyuyo))."</td>";
	$now_kyuyodata .="</tr>";
	
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="/kanri/kanri.css">
<title>過去給与データ</title>
</head>
<?php include 'header.inc'; ?>
<body>
	<div class="breadcrumbs">
		<ul>
			<li><a href="/">猫カフェ 勤怠TOP</a></li>
			<li><a href="/kanri/">管理者メニュー</a></li>
			<li><a href="/kanri/kyuyo/">給与管理</a></li>
			<li><a href="/kanri/kyuyo/add_kyuyo.php">給与リスト</a></li>
			<li><a href="/kanri/kyuyo/now_kyuyo.php?s_no=<?php echo $s_no ?>&k_id=<?php echo $url_k_id; ?>">今月度給与データ</a></li>
			<li>過去給与データ</li>
		</ul>
	</div>
	<div>
	<h2>過去給与データ</h2>
<?php echo $kyuyodata; ?>
<?php echo $kyuyo_html; ?>
		<p>勤怠上のデータ</p>
		<table border="1" id="k_kyuyo">
			<tr>
				<th>勤務日数</th>
				<th>勤務時間</th>
				<th>基本給</th>
				<th>残業時間</th>
				<th>残業代</th>
				<th>合計</th>
			</tr>
<?php echo $now_kyuyodata; ?>
		</table>
<?php echo $z_ms; ?>
	</div>
</body>
