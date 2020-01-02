<?php
	//給与区分修正・新規給与登録ページ
	
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

	$s_no = $_GET['s_no'];
	$syain = new SYAIN();
	$syain->select_sno_syaindata($s_no, $db);
	$name = $syain->name;
	
	//現在の給与内容の変更欄
	$sql = "SELECT * FROM kyuyo_list WHERE syain_no = ? ORDER BY kyuyo_id DESC LIMIT 1";
	$rs = $db->prepare($sql);
	$k_data = array($s_no);
	$rs->execute($k_data);
	$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
	$up_kyuyo_data = "";
	foreach ($rd as $row){
		$up_kyuyo_data .= "<div id='up_kyuyodata'>";
		$up_kyuyo_data .= "<form method ='post' action='up_kyuyo_comp.php?s_no=".$s_no."'>";
		$up_kyuyo_data .= "<p>給与内容を新規で追加する場合は『給与内容を新規追加』<br>をチェックして新規追加内容を入力してください</p>";
		$up_kyuyo_data .= "<p>現在の給与内容を変更する場合はこのまま下記の欄に入力してください</p>";
		$up_kyuyo_data .= "<p>社員番号：".$s_no."　名前：".$name."</p>";
		$up_kyuyo_data .= "<p>給与ID：<input type='text' id='up_kid' name='up_kid' size='30' maxlength='20' value='".h($row['kyuyo_id'])."'></p>";
		$up_kyuyo_data .= "<p>給与区分：<input type='text' id='up_kubun' name='up_kubun' size='30' maxlength='20' value='".h($row['kubun'])."'></p>";
		$up_kyuyo_data .= "<p>基本給：<input type='text' id='up_ktanka' name='up_ktanka' size='30' maxlength='20' value='".h($row['kihon_tanka'])."'></p>";
		$up_kyuyo_data .= "<p>残業代：<input type='text' id='up_ztanka' name='up_ztanka' size='30' maxlength='20' value='".h($row['zangyou_tanka'])."'></p>";
//		$up_kyuyo_data .= "<input type='submit' name='ktourokub' id='ktourokub' value='給与　変更'></form></div>";
		$up_kyuyo_data .= "<button class='kbt' name='ktourokub' id='ktourokub'>給与　変更</button></form></div>";
	}
	//新規給与内容の追加欄
	$new_kyuyo_data = "";
	$new_kyuyo_data .= "<div id='new_kyuyodata'><p>新規追加する給与内容を入力してください</p>";
	$new_kyuyo_data .= "<form method ='post' action='new_kyuyo_comp.php?s_no=".$s_no."'>";
	$new_kyuyo_data .= "<p>社員番号：".$s_no."　名前：".$name."</p>";
	$new_kyuyo_data .= "<p>新規　給与区分：<input type='text' id='new_kubun' name='new_kubun' size='30' maxlength='20'></p>";
	$new_kyuyo_data .= "<p>新規　基本給：<input type='text' id='new_ktanka' name='new_ktanka' size='30' maxlength='20'></p>";
	$new_kyuyo_data .= "<p>新規　残業代：<input type='text' id='new_ztanka' name='new_ztanka' size='30' maxlength='20'></p>";
	$new_kyuyo_data .= "<p>新規　開始年月：<input type='month' id='new_startday' name='new_startday' size='30' maxlength='20'>";
	$new_kyuyo_data .= "<br>(〇〇年〇月21日からになります)</p>";
//	$new_kyuyo_data .= "<input type='submit' name='ntourokub' id='ntourokub' value='給与　新規登録'></form></div>";
	$new_kyuyo_data .= "<button class='kbt' name='ntourokub' id='ntourokub'>給与　新規登録</button></form></div>";

?>

<h3>登録内容変更</h3>
<?php echo $up_kyuyo_data?>
	<p><input type="checkbox" name="kyuyob" value="1">給与内容を新規追加</p>
<?php echo $new_kyuyo_data?>

