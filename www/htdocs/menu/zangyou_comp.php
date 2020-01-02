<?php
	//残業登録完了ページ
	//IPアドレスOK・cookie認証済のみアクセス可

	include_once ('db/db.inc');
	include_once ('common.inc');
	
	mb_language("Japanese");
	mb_internal_encoding("utf-8");
	
	$to = "kumi@ibg.jp";
	$kintai_name = "猫カフェ勤怠";
	
	$db = new DbConnect();
	$a_check = authcheck();
	
	if (isset($_COOKIE['AUTH'])) {
		$cookie = ($_COOKIE['AUTH']);
		$cf_check = checkCookie_checkFlg($cookie, $db);
	}
	
	//未入力チェック
	if (isset($_POST['k_time'])) {
		$k_id = substr((h($_POST['k_time'])), 1);
	} else {
		echo "勤務日時が未選択です<br>";
		exit;
	}
	if (($_POST['z_time']) == "" ) {
		echo "残業時間が未入力です<br>";
		exit;
	}
	if (($_POST['biko']) == "" ) {
		echo "内容が未入力です<br>";
		exit;
	}
	
	$z_time = (h($_POST['z_time']));
	
	$biko = (h($_POST['biko']));
	
	
	// Cookieあり(DB内と一致)・IPアドレスOK・認証許可FLG1→アクセスOK
	if (($a_check == 99) && ($cf_check == 1)) {
		//Cookieから社員Noを取得
		$syain_no = (checkCookie($cookie, $db))->syain_no;
	} else {
		header ("Location: /");
		exit;
	}
	
	//社員Noから名前を取得
	$syain = new SYAIN();
	$syain->select_sno_syaindata($syain_no, $db);
	$name = $syain->name;
	
	$z_touroku = "";
	
	//勤怠IDから出勤時間取得する
	$sql = "SELECT * FROM kintai_list WHERE k_id = ?"; 
	$rs = $db->prepare($sql);
	$data = array($k_id);
	$rs->execute($data);
	$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
	foreach ($rd as $row){
		$work_day = h($row['work_day']);
		$in_time = h($row['in_time']);
		$out_time = h($row['out_time']);
		$f_in_time = h($row['f_in_time']);
		$f_out_time = h($row['f_out_time']);
		$permission = h($row['permission']);
		
		//出勤時間を打刻忘れした場合
		if ($in_time == "") {
			$k_intime = date('Y-m-d H:i',  strtotime($f_in_time))." ～";
		} else if (!($in_time == "")) {
		//出勤時間を打刻済の場合
			$k_intime = date('Y-m-d H:i',  strtotime($in_time))." ～";
		}
		
		if (($out_time == "") && ($f_out_time == "")) {
		//退勤時間未入力の場合(早出の残業など)
			$k_outtime = "退勤時間未入力";
		} else if ($out_time == "") {
		//退勤時間を打刻忘れした場合
			$k_outtime = date('Y-m-d H:i',  strtotime($f_out_time));
		} else if (!($out_time == "")) {
		//退勤時間を打刻済の場合
			$k_outtime = date('Y-m-d H:i',  strtotime($out_time));
		} 
		
		// 残業登録されているかを確認する
		if ($permission == "") {
			//残業が未登録の場合
			//残業時間・備考・認証許可FLG 0 で登録
			$z_permission = "0";
			$sql = "UPDATE kintai_list SET z_time = ?, permission = ?, biko = ?";
			$sql .= "WHERE k_id = ?";
			$rs = $db->prepare($sql);
			$z_data = array(
							$z_time,
							$z_permission,
							$biko,
							$k_id
						);
			$rs->execute($z_data);
			$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
			
			$z_touroku .="<p>社員No：".$syain_no."　名前：".$name."</p>";
			$z_touroku .="<p>勤務日時：".$k_intime."<br>残業時間：".$z_time."分<br>理由<br>".nl2br($biko)."</p>";
			$z_touroku .="<p>上記の内容で登録しました</p>";
			
			$s_coad = "";
			$s_work_d = ltrim(date('d', strtotime($work_day)), "0");
			$s_work_ym = date('Ym', strtotime($work_day));

			if (($s_work_d >= 21) && ($s_work_d <= 31)) {
				$s_ym = $s_work_ym;
			} else if (($s_work_d >= 1) && ($s_work_d <=20)) {
				$s_ym = date('Ym', strtotime($s_work_ym.'-1 month'));
			}
			
			//残業登録した日のシフト表示する
			$sql = "SELECT * FROM shift_list WHERE syain_no = ? AND s_year_month = ?";
			$rs = $db->prepare($sql);
			$data = array($syain_no, $s_ym);
			$rs->execute($data);
			$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
			
			$s_coad_day = "s_coad_".$s_work_d;
			foreach ($rd as $row){
				if (($row[$s_coad_day]) == "H") {
					$s_coad .= "H";
				} else {
					$s_coad .= h($row[$s_coad_day]);
				}
			}

			//管理者へメール
			$subject = "【".$kintai_name."】残業登録がありました";
	
			$body = <<< __BODY__
{$kintai_name}にて、残業登録がありました。
下記内容をご確認の上、残業登録の認証をよろしくお願いします。

【残業登録申請内容】
社員番号：{$syain_no}
　名　前：{$name}

　シフト：{$work_day}『{$s_coad}』

勤務日時：{$k_intime}{$k_outtime}
残業時間：{$z_time}分
　理　由：{$biko}

残業登録 認証URL
http://s.ibg.jp/kanri/zangyou/zangyou_kanri_comp.php?id={$k_id}
		
__BODY__;

			$header = "From:".$to;
			mb_send_mail($to, $subject, $body, $header);
	
		} else if ($permission == "0") {
			//残業登録済・管理者認証待ちの場合
			$z_touroku .= "既に残業登録済、管理者の認証待ちです";
		} else if ($permission == "1") {
			//残業登録済・管理者認証済の場合
			$z_touroku .= "既に残業登録済、管理者認証済です";
		}
	}

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css" href="menu.css">
<title>残業登録完了</title>
</head>
<?php include 'header.inc'; ?>
<body>
	<div class="breadcrumbs">
		<ul>
			<li><a href="/">猫カフェ 勤怠TOP</a></li>
			<li><a href="/menu/">勤怠メニュー</a></li>
			<li><a href="/menu/zangyou.php">残業登録</a></li>
			<li>残業登録完了</li>
		</ul>
	</div>
	<div>
		<h2>残業登録完了</h2>
<?php echo $z_touroku; ?>
	</div>
</body>