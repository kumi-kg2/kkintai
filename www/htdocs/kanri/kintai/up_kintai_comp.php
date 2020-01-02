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

	if ($_GET['k_id']) {
		$up_k_id = h($_GET['k_id']);
	}

	$up_in_time = h($_POST['up_in_time']);
	$up_out_time = h($_POST['up_out_time']);
	if (($_POST['up_z_time']) == 0) {
		$up_z_time = NULL;
		$up_permission = NULL;
	} else {
		$up_z_time = h($_POST['up_z_time']);
		$up_permission = "1";
	}
	$up_biko = h($_POST['up_biko']);
	
	//エラーチェック
	if ($up_k_id == "" ) {
    	echo "出勤日時の未選択エラーです<br>";
	}
	if ($up_in_time == "" ) {
    	echo "出勤時間の未入力エラーです<br>";
	}
	if ($up_out_time == "" ) {
    	echo "退勤時間の未入力エラーです<br>";
	}
	if (!($up_in_time === date('Y-m-d\TH:i', strtotime($up_in_time)))) {
   		echo "出勤時間の入力エラーです、正しく入力して下さい<br>";
    }
   	if (!($up_out_time === date('Y-m-d\TH:i', strtotime($up_out_time)))) {
   		echo "退勤時間の入力エラーです、正しく入力して下さい<br>";
    }
    
    //修正した出勤時間・退勤時間・残業時間・備考をアップする
   	$sql = "UPDATE kintai_list SET in_time = ?, out_time = ?, z_time = ?, permission = ?, biko = ?  WHERE k_id = ?";
	$rs = $db->prepare($sql);
	$up_data = array(
					$up_in_time,
					$up_out_time,
					$up_z_time,
					$up_permission,
					$up_biko,
					$up_k_id
					);
	$rs->execute($up_data);
	$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
	
	$up_kintai_ms ="勤怠時間を修正しました";
    
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css" href="/kanri/kanri.css">
<title>勤怠時間修正完了ページ</title>
</head>
<?php include 'header.inc'; ?>
<body>
	<div class="breadcrumbs">
		<ul>
			<li><a href="/">猫カフェ 勤怠TOP</a></li>
			<li><a href="/kanri/">管理者メニュー</a></li>
			<li><a href="/kanri/kintai/">勤怠時間管理</a></li>
			<li><a href="/kanri/kintai/kintai_kanri.php">勤怠時間修正</a></li>
			<li>勤怠時間修正完了</li>
		</ul>
	</div>
	<div>
		<h2>勤怠時間修正完了</h2>
		<p><?php echo $up_kintai_ms; ?></p>
	</div>
</body>

