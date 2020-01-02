<?php
	//給与確認ページ
	//シフト上での給与の目安を表示(〇月度で)
	//勤怠入力で実際にはどうなのかを表示する
	
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

	//現在の日付を取得
	$now_day = date("d");
	if (($now_day >= 21) && ($now_day <= 31)) {
		$now_ym = date("Ym");
		$shift_ym  = date("Y-m");
		$k_id_ym = date("Ym21");
	} else if (($now_day >= 1) && ($now_day <=20)) {
		$now_ym = date("Ym", strtotime('-1 month'));
		$shift_ym = date("Y-m", strtotime('-1 month'));
		$k_id_ym = date("Ym21", strtotime('-1 month'));
	}
	
	//開始日(kyuyo_id)が新しいものと社員NOを取得
	$sql = "SELECT syain_no, MAX(kyuyo_id) FROM kyuyo_list GROUP BY syain_no";
	$rs = $db->prepare($sql);
	$rs->execute();
	$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
	$kyuyodata ="";
	foreach ($rd as $row) {
		$m_kyuyo_id = h($row['MAX(kyuyo_id)']);
		$start_day = date('Y-m-d',  strtotime($m_kyuyo_id));

		$s_no = h($row['syain_no']);
		//社員Noから名前を取得
		$syain = new SYAIN();
		$syain->select_sno_syaindata($s_no, $db);
		$name = $syain->name;
		
		
		//最新の給与IDと社員NOから取得
		$sql2 = "SELECT * FROM kyuyo_list WHERE kyuyo_id = ? AND syain_no = ?";
		$rs2 = $db->prepare($sql2);
		$data2 = array($m_kyuyo_id, $s_no);
		$rs2->execute($data2);
		$rd2 = $rs2->fetchAll(PDO::FETCH_ASSOC);
		foreach ($rd2 as $row2) {
			$k_id = h($row2['kyuyo_id']);
			$kubun = h($row2['kubun']);
			$k_tanka = h($row2['kihon_tanka']);
			$z_tanka = h($row2['zangyou_tanka']);
			
			//最新の給与IDの開始日が〇月度より先の場合
			if ($m_kyuyo_id > $k_id_ym) {
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
			}
		}
		//〇月度の出勤日数をしらべる
		$sql3 = "SELECT * FROM shift_list WHERE syain_no = ? AND s_year_month = ? GROUP BY syain_no";
		$rs3 = $db->prepare($sql3);
		$data3 = array($s_no, $now_ym);
		$rs3->execute($data3);
		$rd3 = $rs3->fetchAll(PDO::FETCH_ASSOC);
			foreach ($rd3 as $row3) {
				$s_id = $row3['s_id'];
				$s_coad_db ="";
				$work_time = 0;
				
				foreach ($row3 as $key=>$val) {
					if (strpos($key , 's_coad') !== false) {
					
						if ((!($val == "H")) && (!($val == ""))){
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
						
					$kyuyodata .= "<tr>";
					$kyuyodata .= "<td class='s_no'>".$s_no."</td>";
					$kyuyodata .= "<td>".$name."</td>";
					$kyuyodata .= "<td>".$kubun."</td>";
					$kyuyodata .= "<td>".number_format($k_tanka)."</td>";
					$kyuyodata .= "<td>".$cunt."</td>";
					$kyuyodata .= "<td>".$work_time."</td>";
					$kyuyodata .= "<td>".number_format($kyuyo)."</td>";
					$kyuyodata .= "<td><a href='now_kyuyo.php?s_no=".$s_no."&k_id=".$k_id."'>▼</a></td>";
					$kyuyodata .= "</tr>";
						
				} 
			}
		}
	
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="/kanri/kanri.css">
<title>給与リスト</title>
</head>
<?php include 'header.inc'; ?>
<body>
	<div class="breadcrumbs">
		<ul>
			<li><a href="/">猫カフェ 勤怠TOP</a></li>
			<li><a href="/kanri/">管理者メニュー</a></li>
			<li><a href="/kanri/kyuyo/">給与管理</a></li>
			<li>給与リスト</li>
		</ul>
	</div>
	<div>
		<h2>給与リスト</h2>
		<p><?php echo $shift_ym; ?>月度給与予定<br>実際の勤怠データによる給与を確認する場合は▼をクリックしてください</p>
		<table border="1" id="memberTable">
			<tr>
				<th>社員番号</th>
				<th>名前</th>
				<th>区分</th>
				<th>基本給</th>
				<th>勤務日数</th>
				<th>勤務時間</th>
				<th>給与</th>
				<th>　</th>
			</tr>
	<?php  echo $kyuyodata; ?>
		</table>
	</div>
</body>
