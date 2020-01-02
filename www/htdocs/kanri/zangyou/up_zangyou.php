<?php
	// 	勤怠IDを取得して残業時間表示させる→修正

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
	
	$k_id = $_GET['k_id'];
	$sql = "SELECT * FROM kintai_list WHERE k_id = ?";
	$rs = $db->prepare($sql);
	$data = array($k_id);
	$rs->execute($data);
	$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
	$zangyou_data = "";
	foreach ($rd as $row){
		$zangyou_data .= "<p>残業：<input type='number' id='up_z_time' name='up_z_time' value='".h($row['z_time'])."' min='0' max='500' step='10'>";
		$zangyou_data .="　10分単位で入力してください</p>";
		$zangyou_data .= "<p>備考：<textarea id='up_biko' name='up_biko'  cols='40' rows='8'>".h($row['biko'])."</textarea></p>";
	}
?>
<?php echo $zangyou_data; ?>
	<button class='kbt' name="tourokub" id="tourokub">残業時間修正</button>
	</form>
