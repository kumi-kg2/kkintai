<?php

// 管理データの確認

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
	
	$sql = "SELECT * FROM ninsyou_list";
	$rs = $db->prepare($sql);
	$rs->execute();
	$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
	
	$ninsyou_data = "";

	foreach ($rd as $row){
	
		$n_id = h($row['n_id']);
		$s_no = h($row['syain_no']);
		$user_agent = h($row['user_agent']);
		$ninsyou_num = h($row['ninsyou_num']);
		$permission = h($row['permission']);
		$tourokuday = h($row['touroku_day']);
		$lastday = h($row['lastaccess']);
		
		
		$ninsyou_data .= "<tr>";
		$ninsyou_data .= "<td>".$n_id."</td>";
		$ninsyou_data .= "<td>".$s_no."</td>";
		$ninsyou_data .= "<td>".$user_agent."</td>";
		$ninsyou_data .= "<td>".$ninsyou_num."</td>";
		$ninsyou_data .= "<td>".$permission."</td>";
		$ninsyou_data .= "<td>".$tourokuday."</td>";
		$ninsyou_data .= "<td>".$lastday."</td>";
		$ninsyou_data .= "</tr>";
	}
	

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css" href="/kanri/kanri.css"> 
<script src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.js"></script>
<script type="text/javascript" src="js/forget_kintai.js"></script>
<style type="text/css">
* {
font-size: 10px;
}
</style>
<title>認証データ</title>
</head>
<?php include 'header.inc'; ?>
<body>
	<div>
		<table>
			<tr>
				<th>認証ID</th>
				<th>社員NO</th>
				<th>端末</th>
				<th>cookie</th>
				<th>認証</th>
				<th>登録日</th>
				<th>アクセス日</th>
			</tr>
<?php echo $ninsyou_data; ?>
		</table>
	</div>
</body>
