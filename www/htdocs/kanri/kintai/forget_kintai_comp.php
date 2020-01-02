<?php
	//勤怠打刻忘れ修正完了ページ

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

	$k_id = $_GET['id'];

	$sql = "SELECT * FROM kintai_list WHERE k_id = ?";
	$rs = $db->prepare($sql);
	$data = array($k_id);
	$rs->execute($data);
	$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
	
	$f_kintai_ms ="";
	
	foreach ($rd as $row) {
		$s_no = h($row['syain_no']);
		$work_day = h($row['work_day']);
		$intime = h($row['in_time']);
		$f_intime = h($row['f_in_time']);
		$outtime = h($row['out_time']);
		$f_outtime = h($row['f_out_time']);
		
		if ((!($intime == "")) && (!($outtime == "" ))) {
			$f_kintai_ms ="既に修正済です";
			
		//打刻忘れ申請した仮出勤時間を登録する
		} else if ((!($f_intime == "" ))  && ($f_outtime == "" )){
			$sql = "UPDATE kintai_list SET in_time = ? WHERE k_id = ?";
			$rs = $db->prepare($sql);
			$in_data = array($f_intime, $k_id);
			$rs->execute($in_data);
			$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
			$f_kintai_ms ="出勤時間を修正しました";
			
		//打刻忘れ申請した仮退勤時間を登録する
		} else if (($f_intime == "" ) && (!($f_outtime == "" ))) {
			$sql = "UPDATE kintai_list SET out_time = ? WHERE k_id = ?";
			$rs = $db->prepare($sql);
			$out_data = array($f_outtime, $k_id);
			$rs->execute($out_data);
			$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
			$f_kintai_ms ="退勤時間を修正しました";
			
		//出勤時間・退勤時間両方打刻忘れの場合
		} else if ((!($f_intime == "" )) && (!($f_outtime == "" ))) {
			$sql = "UPDATE kintai_list SET in_time = ?, out_time = ? WHERE k_id = ?";
			$rs = $db->prepare($sql);
			$in_out_data = array($f_intime, $f_outtime, $k_id);
			$rs->execute($in_out_data);
			$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
			
			$f_kintai_ms ="出勤時間・退勤時間を修正しました";
		}
	}
	
?>

<h3>打刻修正完了</h3>
<p><?php echo $f_kintai_ms; ?></p>
