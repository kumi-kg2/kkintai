<?php

	//シフトマスタ削除完了ページ

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

	$s_cd = $_GET['cd'];

	$sql = "DELETE FROM shift_master WHERE shift_coad = ?";
	$rs = $db->prepare($sql);
	$data = array($s_cd);
	$rs->execute($data);
	$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>シフトマスタ削除</title>
</head>
<body>
	<div>
		<h3>シフトマスタ削除完了</h3>
		<p>シフトコード【<?php echo $s_cd;?>】のデータを削除しました</p>
		<p><a href="/kanri/shift/shift_master.php">≪ シフトマスタ管理ページ</a></p>
	</div>
</body>
