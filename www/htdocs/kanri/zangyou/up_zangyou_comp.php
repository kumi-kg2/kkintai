<?php
// 勤怠時間修正完了ページ

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
	
	// 勤怠IDを取得
	$k_id = $_POST['kintaidata'];
	$up_k_id = substr($k_id, 2);

	$up_z_time = h($_POST['up_z_time']);
	
	if (isset($_POST['up_biko'])) {
		$up_biko = h($_POST['up_biko']);
	} else {
		$up_biko = "";
	}
	
	//エラーチェック
	if ($k_id == "" ) {
    	echo "出勤日時の未選択エラーです<br>";
	}
	if ($up_z_time == "" ) {
    	echo "出勤時間の未入力エラーです<br>";
	}
	
	//残業時間0にする場合認証FLGをNULLにする
	if ($up_z_time == "0") {
		$z_permission = NULL;
		$z_time = NULL;
	} else {
	//残業時間ある場合、認証済(認証FLG→1)
		$z_permission = "1";
		$z_time = $up_z_time;
	}
	
	
    //修正した出勤時間・退勤時間をアップする
   	$sql = "UPDATE kintai_list SET z_time = ?, permission = ?, biko = ?  WHERE k_id = ?";
	$rs = $db->prepare($sql);
	$up_data = array(
					$z_time,
					$z_permission,
					$up_biko,
					$up_k_id
				);
	$rs->execute($up_data);
	$rd = $rs->fetchAll(PDO::FETCH_ASSOC);    
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="/kanri/kanri.css">
<title>残業時間修正完了</title>
</head>
<?php include 'header.inc'; ?>
<body>
	<div class="breadcrumbs">
		<ul>
			<li><a href="/">猫カフェ 勤怠TOP</a></li>
			<li><a href="/kanri/">管理者メニュー</a></li>
			<li><a href="/kanri/zangyou">残業時間管理</a></li>
			<li><a href="/kanri/zangyou/zangyou_list.php">残業登録リスト</a></li>
			<li>残業時間修正完了</li>
		</ul>
	</div>
	<div>
		<h2>残業時間修正完了</h2>
		<p>残業時間を修正しました</p>
	</div>
</body>
