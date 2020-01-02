<?php
	//残業登録一覧ページ(認証していない残業登録データ一覧)

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
	
	//残業が未認証(認証FLG→0)
	$z_permission = "0";
	$zangyou_data ="";
	$zangyou_html ="";
	
	// 残業登録申請データがあるかを確認
	$sql = "SELECT COUNT(*) AS cnt FROM kintai_list WHERE permission = ?";
	$rs = $db->prepare($sql);
	$data = array($z_permission);
	$rs->execute($data);
	$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
	foreach ($rd as $row){
		$cnt = $row["cnt"];
		if ($cnt == 0 ){
		//	$zangyou_data .= "<p>現在、残業登録申請データはありません</p>";
		} else {
			//認証していない残業登録データ一覧を表示
			$sql = "SELECT * FROM kintai_list WHERE permission = ?";
			$rs = $db->prepare($sql);
			$data = array($z_permission);
			$rs->execute($data);
			$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
			
			foreach ($rd as $row) {
				$k_id = h($row['k_id']);
				$s_no = h($row['syain_no']);
				$work_day = h($row['work_day']);
				$in_time = h($row['in_time']);
				$f_in_time = h($row['f_in_time']);
				$out_time = h($row['out_time']);
				$f_out_time = h($row['f_out_time']);
				$z_time = h($row['z_time']);
				$biko = h($row['biko']);
				
				//出勤時間を打刻忘れした場合
				if ($in_time == "") {
					$k_intime = date('H:i',  strtotime($f_in_time));
				} else if (!($in_time == "")) {
				//出勤時間を打刻済の場合
					$k_intime = date('H:i',  strtotime($in_time));
				}
				
				if (($out_time == "") && ($f_out_time == "")) {
				//退勤時間未入力の場合(早出の残業など)
					$k_outtime = "未入力";
				} else if ($out_time == "") {
				//退勤時間を打刻忘れした場合
					$k_outtime = date('H:i',  strtotime($f_out_time));
				} else if (!($out_time == "")) {
				//退勤時間を打刻済の場合
					$k_outtime = date('H:i',  strtotime($out_time));
				} 
				
				$zangyou_data .= "<tr>";
				$zangyou_data .= "<td id=k_id".$k_id."><a>".$s_no."</a></td>";
				$zangyou_data .= "<td>".$work_day."</td>";
				$zangyou_data .= "<td>".$k_intime."</td>";
				$zangyou_data .= "<td>".$k_outtime."</td>";
				$zangyou_data .= "<td>".$z_time."</td>";
				$zangyou_data .= "<td>".nl2br($biko)."</td>";
				$zangyou_data .= "</tr>";
			}
		}
	}
	
	$zangyou_html .="<p>残業登録を承認する場合は該当する社員番号をクリックしてください</p>";
	$zangyou_html .="<table id='memberTable'>";
	$zangyou_html .="<tr><th>社員番号</th><th>出勤日</th><th>出勤時間</th><th>退勤時間</th><th>残業時間</th><th>残業理由</th></tr>";
	$zangyou_html .= $zangyou_data;
	$zangyou_html .="</table>";


?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="/kanri/kanri.css">
<script src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.js"></script>
<script type="text/javascript" src="js/zangyou.js"></script>
<title>残業登録申請リスト</title>
</head>
<?php include 'header.inc'; ?>
<body>
	<div class="breadcrumbs">
		<ul>
			<li><a href="/">猫カフェ 勤怠TOP</a></li>
			<li><a href="/kanri/">管理者メニュー</a></li>
			<li><a href="/kanri/zangyou">残業時間管理</a></li>
			<li>残業登録申請リスト</li>
		</ul>
	</div>
	<h2>残業登録申請リスト</h2>
	<div>
		<div id="zangyou_dataarea">
		
<?php
	if (!($zangyou_data == "")) {
		echo $zangyou_html;
	} else {
		echo "<p>現在、残業登録申請データはありません</p>";
	}
?>

		<div id="zangyou_nisyouarea"></div>
	</div>
</body>
