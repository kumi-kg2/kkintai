<?php
	//給与確認ページ
	//〇月度　勤怠入力した時点までの給与を表示
	//前日までのを反映させる？
	
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
	
	$kyuyo_id = h($_GET['k_id']);
	$s_no = h($_GET['s_no']);
	$syain = new SYAIN();
	$syain->select_sno_syaindata($s_no, $db);
	$name = $syain->name;
	
	//前日の日付を取得
	$y_work_day = date("Y-m-d", strtotime('-1 day'));
	$y_day = date("d", strtotime('-1 day'));
	if (($y_day >= 21) && ($y_day <= 31)) {
		$now_ym = date("Ym");
		$shift_ym  = date("Y-m");
		$work_day21 = date("Y-m-21");
		$l_y_month = date("Ym21", strtotime('-1 month'));
		$l_y_2month = date("Ym21", strtotime('-2 month'));
	} else if (($y_day >= 1) && ($y_day <=20)) {
		$now_ym = date("Ym", strtotime('-1 month'));
		$shift_ym = date("Y-m", strtotime('-1 month'));
		$work_day21 = date("Y-m-21", strtotime('-1 month'));
		$l_y_month = date("Ym21", strtotime('-2 month'));
		$l_y_2month = date("Ym21", strtotime('-3 month'));
	}
	
	$now_kyuyodata ="";
	
	//従業員別の給与区分などを取得
	$sql = "SELECT * FROM kyuyo_list WHERE kyuyo_id = ? AND syain_no = ?";
	$rs = $db->prepare($sql);
	$data = array(
				$kyuyo_id,
				$s_no
			);
	$rs->execute($data);
	$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
	foreach ($rd as $row) {
		$kubun = h($row['kubun']);
		$k_tanka = h($row['kihon_tanka']);
		$z_tanka = h($row['zangyou_tanka']);
	}
	//従業員別の出勤日数・時間を取得
	$sql2 = "SELECT * FROM kintai_list WHERE (work_day BETWEEN ? AND ?) AND syain_no = ?";
	$rs2 = $db->prepare($sql2);
	$data2 = array(
				$work_day21,
				$y_work_day,
				$s_no
			);
	$rs2->execute($data2);
	$rd2 = $rs2->fetchAll(PDO::FETCH_ASSOC);
	
	
	$work_time = 0;
	$zangyou_time = 0;
	$un_zangyou_time = 0;
	$z_ms = "";
	$g_kyuyo = 0;
	$k_kyuyo = 0;
	$z_kyuyo = 0;
	$k_day = 0;
	$w_hour2 = 0;

	foreach ($rd2 as $row2) {
		$work_day = h($row2['work_day']);
		
		if ((isset($row2['in_time'])) && (isset($row2['out_time']))) {
			$in_time = date('Y-m-d H:i',  strtotime(h($row2['in_time'])));
			$out_time = date('Y-m-d H:i',  strtotime(h($row2['out_time'])));
			
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
		$z_time = h($row2['z_time']);
		$permission = h($row2['permission']);
		
		//勤務日数
		$k_day += 1;

		//同じ勤務日があるかを確認する
		$sql = "SELECT COUNT(*) AS cnt FROM kintai_list WHERE (work_day BETWEEN ? AND ?) AND syain_no = ? AND work_day = ?";
		$rs = $db->prepare($sql);
		$data = array(
				$work_day21,
				$y_work_day,
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
	if ($kubun == "月給") {
		$k_kyuyo += $k_tanka;
	} else if ($kubun == "時給") {
		$k_kyuyo += ($k_tanka * $work_time);
	} else if ($kubun == "日給") {
		$k_kyuyo += round($k_tanka * $k_day);
	}
	
	//合計支給額
	$g_kyuyo += $k_kyuyo + $z_kyuyo;

	$now_kyuyodata .="<tr>";
	$now_kyuyodata .="<td>".$s_no."</td>";
	$now_kyuyodata .="<td>".$name."</td>";
	$now_kyuyodata .="<td>".$kubun."</td>";
	$now_kyuyodata .="<td>".$k_day."</td>";
	$now_kyuyodata .="<td>".number_format($k_tanka)."</td>";
	$now_kyuyodata .="<td>".$work_time."</td>";
	$now_kyuyodata .="<td>".number_format($k_kyuyo)."</td>";
	$now_kyuyodata .="<td>".number_format($z_tanka)."</td>";
	$now_kyuyodata .="<td>".$zangyou_time."</td>";
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
<title>今月度給与データ</title>
</head>
<?php include 'header.inc'; ?>
<body>
	<div class="breadcrumbs">
		<ul>
			<li><a href="/">猫カフェ 勤怠TOP</a></li>
			<li><a href="/kanri/">管理者メニュー</a></li>
			<li><a href="/kanri/kyuyo/">給与管理</a></li>
			<li><a href="/kanri/kyuyo/add_kyuyo.php">給与リスト</a></li>
			<li>今月度給与データ</li>
		</ul>
	</div>
	<h2>今月度給与データ</h2>
	<div>
		<p><a href="past_kyuyo_list.php?ym=<?php echo $l_y_2month;?>&s_no=<?php echo $s_no; ?>&k_id=<?php echo $kyuyo_id?>">
		≪≪<?php echo  date('Y-m' ,strtotime($l_y_2month));?>月度</a>　
		<a href="past_kyuyo_list.php?ym=<?php echo $l_y_month;?>&s_no=<?php echo $s_no; ?>&k_id=<?php echo $kyuyo_id?>">
		≪<?php echo date('Y-m' ,strtotime($l_y_month));?>月度</a>　
		<a href="past_kyuyo.php?s_no=<?php echo $s_no;?>&k_id=<?php echo $kyuyo_id; ?>">過去給与 検索</a></p>
		<p>	<?php echo $shift_ym; ?>月度給与(<?php echo $y_work_day; ?> 昨日時点での給与予定)
		<table id="memberTable">
			<tr>
				<th>社員番号</th>
				<th>名前</th>
				<th>区分</th>
				<th>勤務日数</th>
				<th>基本単価</th>
				<th>勤務時間</th>
				<th>基本給</th>
				<th>残業単価</th>
				<th>残業時間</th>
				<th>残業代</th>
				<th>合計</th>
			</tr>
	<?php echo $now_kyuyodata; ?>
		</table>
	<?php echo $z_ms; ?>
	</div>
</body>

