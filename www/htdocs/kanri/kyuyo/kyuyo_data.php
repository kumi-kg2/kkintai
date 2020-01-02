<?php
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
	
	//社員の一覧を表示
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
			$kyuyodata .= "<tr>";
			$kyuyodata .= "<td class='s_no'><a>".$s_no."</a></td>";
			$kyuyodata .= "<td>".$name."</td>";
			$kyuyodata .= "<td>".h($row2['kubun'])."</td>";
			$kyuyodata .= "<td>".h($row2['kihon_tanka'])."</td>";
			$kyuyodata .= "<td>".h($row2['zangyou_tanka'])."</td>";
			$kyuyodata .= "<td>".$start_day."</td>";
			$kyuyodata .= "<td><a href='kyuyo_list.php?s_no=".$s_no."'>▼</a></td>";
			$kyuyodata .= "</tr>";
		}
	}

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="/kanri/kanri.css">
<script src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.js"></script>
<script type="text/javascript" src="js/kyuyo.js"></script>
<title>給与区分リスト</title>
</head>
<?php include 'header.inc'; ?>
<body>
	<div class="breadcrumbs">
		<ul>
			<li><a href="/">猫カフェ 勤怠TOP</a></li>
			<li><a href="/kanri/">管理者メニュー</a></li>
			<li><a href="/kanri/kyuyo/">給与管理</a></li>
			<li>給与区分リスト</li>
		</ul>
	</div>
	<h2>給与区分リスト</h2>
	<div id="syain_dataarea">
		<p>過去の給与区分確認する場合は▼をクリックしてください<br>
		給与区分の修正や新規追加する場合は社員番号をクリックしてください</p>
		<table id="memberTable">
			<tr>
				<th>社員番号</th>
				<th>名前</th>
				<th>区分</th>
				<th>基本給</th>
				<th>残業代</th>
				<th>開始日</th>
				<th>　</th>
			</tr>
	<?php echo ($kyuyodata); ?>
		</table>
	</div>
	<div id="up_kyuyo_dataarea"></div>
</body>
