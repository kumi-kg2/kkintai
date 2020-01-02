<?php
	//勤怠打刻忘れ修正ページ

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
	
	//打刻忘れ申請したデータ表示
	$sql = "SELECT * FROM kintai_list";
	$rs = $db->prepare($sql);
	$rs->execute();
	$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
	
	$f_kintaidata ="";
	$f_kintai_html = "";
	
	foreach ($rd as $row){
	
		$k_id = h($row['k_id']);
		$s_no = h($row['syain_no']);
		$work_day = h($row['work_day']);
		$intime = h($row['in_time']);
		$f_intime = h($row['f_in_time']);
		$outtime = h($row['out_time']);
		$f_outtime = h($row['f_out_time']);
		
		if (!($f_intime == "" )) {
			$f_intime_data = date('H:i',  strtotime($f_intime));
		} else if ($f_intime == "" ) {
			$f_intime_data = "";
		}
		if (!($f_outtime == "" )) {
			$f_outtime_data = date('H:i',  strtotime($f_outtime));
		} else if ($f_outtime == "" ) {
			$f_outtime_data = "";
		}

		if ((($intime == "" ) && (!($f_intime == "" ))) or (($outtime == "" ) && (!($f_outtime == "" )))) {

			$s_work_d = ltrim(date('d', strtotime($work_day)), "0");
			$s_work_ym = date('Ym', strtotime($work_day));

			if (($s_work_d >= 21) && ($s_work_d <= 31)) {
				$s_ym = $s_work_ym;
			} else if (($s_work_d >= 1) && ($s_work_d <=20)) {
				$s_ym = date('Ym', strtotime($s_work_ym.'-1 month'));
			}
			
			//打刻忘れ申請した日のシフト表示する
			$sql = "SELECT * FROM shift_list WHERE syain_no = ? AND s_year_month = ?";
			$rs = $db->prepare($sql);
			$data = array($s_no, $s_ym);
			$rs->execute($data);
			$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
			
			$s_coad_day = "s_coad_".$s_work_d;
			foreach ($rd as $row){
				if (($row[$s_coad_day]) == "H") {
					$s_coad = "H";
				} else {
					$s_coad = h($row[$s_coad_day]);
				}
			}
			
			$f_kintaidata .= "<tr><td id='k_id".$k_id."'><a>".$s_no."</a></td>";
			$f_kintaidata .= "<td>".$work_day."</td>";
			$f_kintaidata .="<td>".$s_coad."</td>";
			$f_kintaidata .="<td>".$f_intime_data."</td>";
			$f_kintaidata .="<td>".$f_outtime_data."</td></tr>";
		}
		
		

	}
	
	$f_kintai_html .= "<p>打刻修正が登録された勤怠一覧です<br>";
	$f_kintai_html .= "仮登録された時刻で修正する場合は該当するデータの<br>社員番号をクリックしてください</p>";
	$f_kintai_html .= "<table id='memberTable'>";
	$f_kintai_html .= "<tr><th>社員番号</th><th>出勤日</th><th>シフト</th><th>修正 出勤</th><th>修正 退勤</th></tr>";
	$f_kintai_html .= $f_kintaidata;
	$f_kintai_html .= "</table>";


?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css" href="/kanri/kanri.css"> 
<script src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.js"></script>
<script type="text/javascript" src="js/forget_kintai.js"></script>
<title>打刻忘れ修正</title>
</head>
<?php include 'header.inc'; ?>
<body>
	<div class="breadcrumbs">
		<ul>
			<li><a href="/">猫カフェ 勤怠TOP</a></li>
			<li><a href="/kanri/">管理者メニュー</a></li>
			<li><a href="/kanri/kintai/">勤怠時間管理</a></li>
			<li>打刻忘れ修正</li>
		</ul>
	</div>
	<h2>打刻忘れ修正</h2>
	<div id="f_kintaidata">
<?php
	if (!($f_kintaidata == "")) {
		echo $f_kintai_html;
	} else {
		echo "<p>現在、打刻忘れ修正データはありません</p>";
	}
?>
	</div>
	<div id="f_kintai_dataarea"></div>
</body>
