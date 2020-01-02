<?php
	//社員NOから過去から現在の給与リスト一覧表示する
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
	
	$s_no = $_GET['s_no'];
	
	//社員Noから名前を取得
	$syain = new SYAIN();
	$syain->select_sno_syaindata($s_no, $db);
	$s_name = $syain->name;
	
	
	$sql = "SELECT * FROM kyuyo_list WHERE syain_no = ? ORDER BY kyuyo_id DESC";
	$rs = $db->prepare($sql);
	$data = array($s_no);
	$rs->execute($data);
	$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
	$kyuyo_data = "";
	foreach ($rd as $row){
		$k_id = h($row['kyuyo_id']);
		$start_day = date('Y-m-d',  strtotime($k_id));
		$kyuyo_data .="<table id='memberTable'>";
		$kyuyo_data .="<tr><th>開始日</th><th>区分</th><th>基本給</th><th>残業代</th><th>　</th></tr>";
		$kyuyo_data .= "<tr>";
		$kyuyo_data .= "<td>".$start_day."</td>";
		$kyuyo_data .= "<td>".h($row['kubun'])."</td>";
		$kyuyo_data .= "<td>".number_format(h($row['kihon_tanka']))."</td>";
		$kyuyo_data .= "<td>".number_format(h($row['zangyou_tanka']))."</td>";
		$kyuyo_data .= "<td><a href='now_kyuyo.php?s_no=".$s_no."&k_id=".$k_id."'>▼</a></td>";
		$kyuyo_data .= "</tr>";
		$kyuyo_data .="</table>";
	}
	
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="/kanri/kanri.css">
<title>従業員別給与区分</title>
</head>
<?php include 'header.inc'; ?>
<body>
<h2>従業員別給与区分</h2>
	<div class="breadcrumbs">
		<ul>
			<li><a href="/">猫カフェ 勤怠TOP</a></li>
			<li><a href="/kanri/">管理者メニュー</a></li>
			<li><a href="/kanri/kyuyo/">給与管理</a></li>
			<li><a href="/kanri/kyuyo/kyuyo_data.php">給与区分リスト</a></li>
			<li>従業員別給与区分</li>
		</ul>
	</div>
	<div>
		<p>社員番号：<?php echo $s_no; ?>　名前：<?php echo $s_name?>
		<br>実際の勤怠データによる給与を確認する場合は▼をクリックしてください</p>
	<?php 	echo ($kyuyo_data); ?>
	</div>
</body>
		
