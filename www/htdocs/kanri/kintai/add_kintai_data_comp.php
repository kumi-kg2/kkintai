<?php
	//管理者側　勤怠登録完了ページ

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
	
	$s_no = h($_GET['s_no']);
	
	//勤務日
	if ($_POST['work_day']) {
		$work_day = h($_POST['work_day']);
		$out_time_day = date("Y-m-d" , strtotime($work_day." +1 day"));
	}

	//出勤時間
	if (isset($_POST['in_time'])) {
		$in_time = $work_day." ".h($_POST['in_time']);
	} 
	
	//退勤時間
	if  ((isset($_POST['out_time'])) && (isset($_POST['out_time_day']))) {
		//日付を跨いでの退勤の場合
		$out_time = $out_time_day." ".h($_POST['out_time']);
	} else if ((isset($_POST['out_time']))  && (!(isset($_POST['out_time_day'])))) {
		//その他の退勤時間登録の場合
		$out_time = $work_day." ".h($_POST['out_time']);
	}
	
	//残業時間ある場合、permission=1で追加
	if ((isset($_POST['z_time'])) && (($_POST['z_time']) > 0)) {
		$z_time = h($_POST['z_time']);
		$z_permission = "1";
	} else {
		$z_time = NULL;
		$z_permission = NULL;
	}
	
	if (isset($_POST['biko'])) {
		$biko = h($_POST['biko']);
	}
	
	$add_kintai_html = "";
	//勤怠データがあるかチェックする
	$sql = "SELECT COUNT(*) AS cnt FROM kintai_list WHERE work_day = ? AND syain_no = ? ";
	$rs = $db->prepare($sql);
	$data = array(
				$work_day,
				$s_no
				);
	$rs->execute($data);
	$rd = $rs->fetchAll(PDO::FETCH_ASSOC);

	foreach ($rd as $row){
		$cnt = $row["cnt"];
		if ( $cnt <= 1) {
			//勤怠データに出勤日が未登録or1個登録の場合
			$sql = "INSERT INTO kintai_list(syain_no, work_day, in_time, out_time, z_time, permission, biko) ";
			$sql .= "VALUES (?, ?, ?, ?, ?, ?, ?)";
			$rs = $db->prepare($sql);
			$in_out_data = array(
							$s_no,
							$work_day,
							$in_time,
							$out_time,
							$z_time,
							$z_permission,
							$biko
							);
			$rs->execute($in_out_data);
			$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
			
			$add_kintai_html .= "<p>".$work_day."の勤怠データを追加しました</p>";
		} else {
			$add_kintai_html .= "<p>".$work_day."の勤怠のデータは既に登録済です</p>";
		}
	}

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css" href="/kanri/kanri.css">
<script src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.js"></script>
<script type="text/javascript" src="js/add_kintai.js"></script>
<title>勤怠新規登録完了</title>
</head>
<?php include 'header.inc'; ?>
<body>
	<div class="breadcrumbs">
		<ul>
			<li><a href="/">猫カフェ 勤怠TOP</a></li>
			<li><a href="/kanri/">管理者メニュー</a></li>
			<li><a href="/kanri/kintai/">勤怠時間管理</a></li>
			<li><a href="/kanri/kintai/kintai_kanri.php">勤怠時間修正</a></li>
			<li><a href="/kanri/kintai/add_kintai_data.php?s_no=<?php echo $s_no; ?>">勤怠リスト</a></li>
			<li>勤怠新規登録完了</li>
		</ul>
	</div>
	<div>
		<h2>勤怠新規登録完了</h2>
<?php echo $add_kintai_html; ?>
	</div>
</body>

