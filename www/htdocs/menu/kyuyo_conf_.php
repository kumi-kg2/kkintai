<?php
	//個人給与確認ページ
	// 認証されたcookieのみアクセス可(IPアドレスは指定なし)

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
	//社員Noから名前を取得
	$syain = new SYAIN();
	$syain->select_sno_syaindata($syain_no, $db);
	$name = $syain->name;

	//現在の日付を取得
	$now_work_day = date("Y-m-d");
	$now_day = date("d");
	if (($now_day >= 21) && ($now_day <= 31)) {
		$now_ym = date("Ym");
		$shift_ym  = date("Y-m");
		$work_day21 = date("Y-m-21");
	} else if (($now_day >= 1) && ($now_day <=20)) {
		$now_ym = date("Ym", strtotime('-1 month'));
		$shift_ym = date("Y-m", strtotime('-1 month'));
		$work_day21 = date("Y-m-21", strtotime('-1 month'));
	}
	
	$kyuyodata ="";

	//給与区分などを取得
	$sql = "SELECT * FROM kyuyo_list WHERE syain_no = ? ORDER BY kyuyo_id DESC LIMIT 1";
	$rs = $db->prepare($sql);
	$data = array($syain_no);
	$rs->execute($data);
	$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
	foreach ($rd as $row) {
		$kubun = h($row['kubun']);
		$k_tanka = h($row['kihon_tanka']);
		$z_tanka = h($row['zangyou_tanka']);
	}
	
	//
	$sql2 = "SELECT * FROM shift_list WHERE syain_no = ? AND s_year_month = ?";
	$rs2 = $db->prepare($sql2);
	$data2 = array($syain_no, $now_ym);
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
				$kyuyo = $k_tanka * $work_time;
			} else if ($kubun == "日給") {
				$kyuyo = $k_tanka * $cunt;
			}
			$kyuyodata .= "<tr>";
			$kyuyodata .= "<td>".$syain_no."</td>";
			$kyuyodata .= "<td>".$name."</td>";
			$kyuyodata .= "<td>".$kubun."</td>";
			$kyuyodata .= "<td>".number_format($k_tanka)."</td>";
			$kyuyodata .= "<td>".$cunt."</td>";
			$kyuyodata .= "<td>".$work_time."</td>";
			$kyuyodata .= "<td>".number_format($kyuyo)."</td>";
			$kyuyodata .= "</tr>";
		}

	}
	
	$now_kyuyodata ="";

	//現在までの給与目安の表示
	$sql3 = "SELECT * FROM kintai_list WHERE (work_day BETWEEN ? AND ?) AND syain_no = ?";
	$rs3 = $db->prepare($sql3);
	$data3 = array(
				$work_day21,
				$now_work_day,
				$syain_no
			);
	$rs3->execute($data3);
	$rd3 = $rs3->fetchAll(PDO::FETCH_ASSOC);
	
	$work_time = 0;
	$zangyou_time = 0;
	$k_kyuyo = 0;
	$z_kyuyo = 0;
	$g_kyuyo = 0;
	
	//勤務日数
	$k_day = 0;
	
	$w_array ="";
	foreach ($rd3 as $row3) {
		$work_day = h($row3['work_day']);
		$in_time = date('Y-m-d H:i',  strtotime(h($row3['in_time'])));
		$out_time = date('Y-m-d H:i',  strtotime(h($row3['out_time'])));
		$z_time = h($row3['z_time']);
		$permission = h($row3['permission']);
		
		//勤務日数
		$k_day += 1;
		//同じ勤務日があるかを確認する
		$sql = "SELECT COUNT(*) AS cnt FROM kintai_list WHERE (work_day BETWEEN ? AND ?) AND syain_no = ? AND work_day = ?";
		$rs = $db->prepare($sql);
		$data = array(
				$work_day21,
				$now_work_day,
				$syain_no,
				$work_day
				);
		$rs->execute($data);
		$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
		foreach ($rd as $row){
			$cnt = $row["cnt"];
			//同じ勤務日があるときに勤務日が重複しないように0.5でカウント
			if (($cnt == 2)) {
				$k_day -= 0.5;
			}
		}
		
		//勤務時間
		$hour = (strtotime($out_time) - strtotime($in_time)) / 3600;
		$w_hour = round($hour,2);
		$work_time += $w_hour;
		
		//残業時間・残業代　認証されていたら反映させる
		if ((!($z_time == "")) && ($permission == "1")) {
			$z_min = $z_time / 60;
			$z_hour = round($z_min,2);
			$zangyou_time += $z_hour;
			//仮で小数点以下四捨五入
			$z_kyuyo += round($z_tanka * $z_hour);
		} else {
			$zangyou_time += 0;
			$z_kyuyo += 0; 
		}
		
		// 基本給
		//仮 給与区分【月給・時給・日給】
		if ($kubun == "月給") {
			$k_kyuyo += $k_tanka;
		} else if ($kubun == "時給") {
			$k_kyuyo += $k_tanka * $work_time;
		} else if ($kubun == "日給") {
			$k_kyuyo += $k_tanka * $k_day;
		}
		
		//合計支給額
		$g_kyuyo += $k_kyuyo + $z_kyuyo;
	}
		$now_kyuyodata .="<tr>";		
		$now_kyuyodata .="<td>".$k_day."</td>";
		$now_kyuyodata .="<td>".$k_tanka."</td>";
		$now_kyuyodata .="<td>".$work_time."</td>";
		$now_kyuyodata .="<td>".$k_kyuyo."</td>";
		$now_kyuyodata .="<td>".$z_tanka."</td>";
		$now_kyuyodata .="<td>".$zangyou_time."</td>";
		$now_kyuyodata .="<td>".$z_kyuyo."</td>";
		//合計金額は仮で小数点以下四捨五入
		$now_kyuyodata .="<td>".round($g_kyuyo)."</td>";
		$now_kyuyodata .="</tr>";		
		
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css" href="menu.css">
<title>給与確認</title>
</head>
<?php include 'header.inc'; ?>
<body>
	<div class="breadcrumbs">
		<ul>
			<li><a href="/">猫カフェ 勤怠TOP</a></li>
			<li><a href="/menu/">勤怠メニュー</a></li>
			<li><a href="/menu/shift.php">シフト確認</a></li>
			<li>今月度の給与 確認</li>
		</ul>
	</div>
	<div>
	<p>今月度の給与　予定</p>
		<table id="add_kyuyo">
			<tr>
				<th>社員番号</th>
				<th>名前</th>
				<th>区分</th>
				<th>基本単価</th>
				<th>勤務日数</th>
				<th>勤務時間</th>
				<th>給与</th>
			</tr>
	<?php  echo $kyuyodata; ?>
		</table>
<!--		<p>現時点での支給予定</p>
		<table border="1" id="now_kyuyo">
			<tr>
				<th>勤務日数</th>
				<th>基本単価</th>
				<th>勤務時間</th>
				<th>基本給</th>
				<th>残業単価</th>
				<th>残業時間</th>
				<th>残業代</th>
				<th>合計</th>
			</tr>
	<?php  //echo $now_kyuyodata; ?>
		</table>-->
	</div>
</body>
