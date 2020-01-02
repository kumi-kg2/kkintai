<?php
//シフトマスタ登録完了ページ
//仮出退勤時間2は非表示
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
	
	date_default_timezone_set('Asia/Tokyo');
	$up_date = date("YmdHis");
	
	if (isset($_POST['coad'])) {
	// 新規のシフトコード
		$coad = h($_POST['coad']);
	} else if (isset($_GET['cd'])) {
	// 既存のシフトコード
		$coad = h($_GET['cd']);
	}
	
	$in_time1 = h($_POST['in_time1']);
	$out_time1 = h($_POST['out_time1']);
//	$in_time2 = h($_POST['in_time2']);
//	$out_time2 = h($_POST['out_time2']);
	
	if ($_POST['work_time']) {
		$work_time = h($_POST['work_time']);
	}
	
	if (isset($_POST['biko'])) {
		$biko = h($_POST['biko']);
	} else {
		$biko = NULL;
	}
	
	//エラーチェック
	if ($coad == "" ) {
		echo "シフトコードの未入力エラーです";
	}
	if (!(preg_match("/^[a-zA-Z0-9]+$/", $coad))){
		echo "シフトコードの入力エラーです、コードは半角英数字で入力してください";
	}
	if ((mb_strlen($coad)) > 3 ) {
		echo "シフトコードの入力エラーです、コードは2～3文字で入力してください";
	} else if ((mb_strlen($coad)) <= 1 ) {
		echo "シフトコードの入力エラーです、コードは2～3文字で入力してください";
	}
	//(仮)出勤時間・退勤時間00:00の際にエラーチェック
	if ($in_time1 == "" ){
		echo "出勤時間1が未入力エラーです";
	}
	if ($out_time1 == "" ){
		echo "退勤時間1が未入力エラーです";
	}

	if ($work_time == "" ){
		echo "勤務時間が未入力エラーです";
	} else if ($work_time == "0" ){
		echo "勤務時間が未入力エラーです";
	}
	
	// 新規登録→既にシフトコードが登録されていなかを確認する
	$sql = "SELECT COUNT(*) AS cnt FROM shift_master WHERE shift_coad = ?";
	$rs = $db->prepare($sql);
	$data = array($coad);
	$rs->execute($data);
	$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
	
	foreach ($rd as $row){
		$cnt = $row["cnt"];
		if ($cnt == 0 ){
		// 新規シフトコードOK→マスタ新規登録
//			$sql = "INSERT INTO shift_master (shift_coad, shift_in_time_1, shift_out_time_1, shift_in_time_2, shift_out_time_2, work_time, biko) ";
//			$sql .= "VALUES(?, ?, ?, ?, ?, ?, ?)";
			$sql = "INSERT INTO shift_master (shift_coad, shift_in_time_1, shift_out_time_1, work_time, biko) ";
			$sql .= "VALUES(?, ?, ?, ?, ?)";
			$rs = $db->prepare($sql);
			$data = array(
						$coad,
						$in_time1,
						$out_time1,
					//	$in_time2,
					//	$out_time2,
						$work_time,
						$biko
					);
			$rs->execute($data);
			$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
		} else if (($cnt > 0 ) && (isset($_GET['cd']))) {
		//既存シフトコード修正→既存コードか確認ok→登録内容を修正
//			$sql = "UPDATE shift_master SET shift_in_time_1=?, shift_out_time_1=?, shift_in_time_2=?, shift_out_time_2=?, work_time=?, biko=?, updated=? ";
			$sql = "UPDATE shift_master SET shift_in_time_1=?, shift_out_time_1=?, work_time=?, biko=?, updated=? ";
			$sql .= "WHERE shift_coad = ?";
			$rs = $db->prepare($sql);
			$data = array(
						$in_time1,
						$out_time1,
					//	$in_time2,
					//	$out_time2,
						$work_time,
						$biko,
						$up_date,
						$coad
					);
			$rs->execute($data);
			$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
		} else {
			echo "既存のシフトコードです";
			exit;
		}
	}
	
	
	$new_shift_master = "";
	$new_shift_master .= "<tr>";
	$new_shift_master .= "<td>".$coad."</td>";
	$new_shift_master .= "<td>".date('H:i',  strtotime($in_time1))."</td>";
	$new_shift_master .= "<td>".date('H:i',  strtotime($out_time1))."</td>";
//	$new_shift_master .= "<td>".str_replace('00:00', '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', date('H:i',  strtotime($in_time2)))."</td>";
//	$new_shift_master .= "<td>".str_replace('00:00', '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', date('H:i',  strtotime($out_time2)))."</td>";
	$new_shift_master .= "<td>".$work_time."</td>";
	$new_shift_master .= "<td>".nl2br($biko)."</td>";
	$new_shift_master .= "</tr>";
	
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css" href="/kanri/kanri.css"> 
<title>シフトマスタ作成・修正完了</title>
</head>
<?php include 'header.inc'; ?>
<body>
	<div class="breadcrumbs">
		<ul>
			<li><a href="/">猫カフェ 勤怠TOP</a></li>
			<li><a href="/kanri/">管理者メニュー</a></li>
			<li><a href="/kanri/shift/">シフト管理</a></li>
			<li><a href="/kanri/shift/">シフトマスタ一覧(作成・修正)</a></li>
			<li>シフトマスタ作成・修正完了</li>
		</ul>
	</div>
	<h2>シフトマスタ作成・修正内容</h2>
	<div>
		<p>下記内容で作成・修正を完了しました。</p>
		<table>
			<tr>
				<th>シフトコード</th>
				<th>出勤時間</th>
				<th>退勤時間</th>
			<!--	<th>出勤時間2</th>
				<th>退勤時間2</th>-->
				<th>勤務時間</th>
				<th>　備　考　</th>
			</tr>
<?php 	echo ($new_shift_master); ?>
		</table>
	</div>
</body>