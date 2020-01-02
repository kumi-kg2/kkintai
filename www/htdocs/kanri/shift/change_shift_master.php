<?php
//シフトマスタ修正入力ページ(ajaxでshift_master.phpにて表示)
//シフトコードから該当するデータをテキストボックスにて表示
//シフトコードは変更不可 仮出退勤時間2非表示

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
	
	$s_cd = h($_GET['s_cd']);
	
	$sql = "SELECT * FROM shift_master WHERE shift_coad = ?";
	$rs = $db->prepare($sql);
	$data = array($s_cd);
	$rs->execute($data);
	$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
	
	$shift_master_data = "";
	$shift_master_data .= "<p>シフトコード：<input type='text' id='coad' name='coad' size='3' maxlength='3' value='".($s_cd)."' disabled='disabled' >";
	$shift_master_data .= "　<a id='mdelete' href= 'shift_master_delete.php?cd=".$s_cd."' ><button class='kbt'>シフトマスタ削除</button></a></p>";

	foreach ($rd as $row) {
		$w_time = (h($row['work_time']));
		$shift_master_data .= "<p>出勤時間：<input class='time1' type='time' id='in_time1' name='in_time1' step='1800' value='".h($row['shift_in_time_1'])."'> / ";
		$shift_master_data .= "退勤時間：<input class='time1' type='time' id='out_time1' name='out_time1' step='1800' value='".h($row['shift_out_time_1'])."'></p>";
//		$shift_master_data .= "<p>出勤時間2：<input type='time' class='time2' id='in_time2' name='in_time2' step='1800' value='".h($row['shift_in_time_2'])."'> / ";
//		$shift_master_data .= "退勤時間2：<input type='time' class='time2' id='out_time2' name='out_time2' step='1800' value='".h($row['shift_out_time_2'])."'></p>";
		$shift_master_data .= "<p>勤務時間：<input type='text' id='work_time' name='work_time' value='".$w_time."' size='3' maxlength='3'></p>";
		$shift_master_data .= "<p>備　考　：<textarea name='biko' id='biko' cols='40' rows='8' >".h($row['biko'])."</textarea></p>";
		
	}
?>
<h3>シフトマスタ修正</h3>
	<form method ="post" action="shift_master_comp.php?cd=<?php echo h($s_cd); ?>">
		<?php echo $shift_master_data; ?>
		<p><button class='kbt' name="mtourokub" id="mtourokub">シフトマスタ修正</button></p>
	</form>
	<a href= "shift_master.php" ><button class='kbt'>新規マスタ作成画面に戻る</button></a>
