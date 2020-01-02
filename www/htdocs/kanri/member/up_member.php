<?php
//従業員の登録内容変更ページ
//syain_list　kyuyo_list変更
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
	
	//社員情報の登録内容変更欄
	$sql = "SELECT * FROM syain_list WHERE syain_no = ?";
	$rs = $db->prepare($sql);
	$data = array($s_no);
	$rs->execute($data);
	$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
	$up_member_data = "";
	foreach ($rd as $row){
		//仮:現在は社員NOから登録内容を変更するように設定
		//今後社員NOを変更することがある場合は従業員IDで変更する？
		$up_member_data .= "<p>従業員の登録内容を変更する場合は下記の欄に入力してください<br>（社員番号は変更不可です）</p>";
//		$up_member_data .= "<p>従業員ID：<input type='text' id='up_jid' name='up_jid' size='30' maxlength='20' value='".h($row['j_id'])."'></p>";
		$up_member_data .= "<p>社員番号：<input type='text' id='up_sno' name='up_sno' size='10' maxlength='10' value='".h($row['syain_no'])."'>";
		$up_member_data .= "<input type='checkbox' name='up_kflg' value='1'>管理者FLG</p></p>";
		$up_member_data .= "<p>　部　署：<input type='text' id='up_busyo' name='up_busyo' size='30' maxlength='20' value='".h($row['busyo'])."'></p>";
		$up_member_data .= "<p>　名　前：<input type='text' id='up_name' name='up_name' size='30' maxlength='20' value='".h($row['name'])."'></p>";
		$up_member_data .= "<p>フリガナ：<input type='text' id='up_furi' name='up_furi' size='30' maxlength='20' value='".h($row['furi'])."'><br>";
		$up_member_data .= "（カタカナで入力、スペースは使用しないでください）</p>";
		$up_member_data .= "<p>生年月日：<input type='date' id='up_birth' name='up_birth' size='30' maxlength='20' value='".h($row['birthday'])."'></p>";
		$up_member_data .= "<p>携帯番号：<input type='text' id='up_phone' name='up_phone' size='30' maxlength='20' value='".h($row['phone'])."'></p>";
	}
	
	//現在の給与内容の変更欄
	$sql2 = "SELECT * FROM kyuyo_list WHERE syain_no = ? ORDER BY kyuyo_id DESC LIMIT 1";
	$rs2 = $db->prepare($sql2);
	$k_data = array($s_no);
	$rs2->execute($k_data);
	$rd2 = $rs2->fetchAll(PDO::FETCH_ASSOC);
	$up_kyuyo_data = "";
	foreach ($rd2 as $row2){
		$up_kyuyo_data .= "<div id='up_kyuyodata'>";
		$up_kyuyo_data .= "<form method ='post' action='../kyuyo/up_kyuyo_comp.php?s_no=".$s_no."'>";
		$up_kyuyo_data .= "<p>給与内容を新規で追加する場合は『給与内容を新規追加』<br>をチェックして新規追加内容を入力してください<br>";
		$up_kyuyo_data .= "現在の給与内容を変更する場合はこのまま下記の欄に入力してください<br>（給与IDは変更不可です）</p>";
		$up_kyuyo_data .= "<p>給与ID：<input type='text' id='up_kid' name='up_kid' size='10' maxlength='10' value='".h($row2['kyuyo_id'])."'></p>";
		$up_kyuyo_data .= "<p>給与区分：<input type='text' id='up_kubun' name='up_kubun' size='10' maxlength='10' value='".h($row2['kubun'])."'></p>";
		$up_kyuyo_data .= "<p>基本給：<input type='text' id='up_ktanka' name='up_ktanka' size='10' maxlength='10' value='".h($row2['kihon_tanka'])."'></p>";
		$up_kyuyo_data .= "<p>残業代：<input type='text' id='up_ztanka' name='up_ztanka' size='10' maxlength='10' value='".h($row2['zangyou_tanka'])."'></p>";
		$up_kyuyo_data .= "<button class='kbt' name='ktourokub' id='ktourokub'>給与　変更</button></form></div>";

	}
	
	//新規給与内容の追加欄
	$new_kyuyo_data = "";
	$new_kyuyo_data .= "<div id='new_kyuyodata'><p>新規追加する給与内容を入力してください</p>";
	$new_kyuyo_data .= "<form method ='post' action='../kyuyo/new_kyuyo_comp.php?s_no=".$s_no."'>";
	$new_kyuyo_data .= "<p>新規　給与区分：<input type='text' id='new_kubun' name='new_kubun' size='10' maxlength='10'></p>";
	$new_kyuyo_data .= "<p>新規　基本給：<input type='text' id='new_ktanka' name='new_ktanka' size='10' maxlength='10'></p>";
	$new_kyuyo_data .= "<p>新規　残業代：<input type='text' id='new_ztanka' name='new_ztanka' size='10' maxlength='10'></p>";
	$new_kyuyo_data .= "<p>新規　開始年月：<input type='month' id='new_startday' name='new_startday' size='30' maxlength='20'>";
	$new_kyuyo_data .= "<br>(〇〇年〇月21日からになります)</p>";
	$new_kyuyo_data .= "<button class='kbt' name='ntourokub' id='ntourokub'>給与　新規登録</button></form></div>";
	
?>
<h3>登録内容変更</h3>
	<form method ="post" action="up_member_comp.php">
<?php echo $up_member_data?>
	<button class='kbt' name="tourokub" id="tourokub">登録内容変更</button>
	</form>
	<p><a href= "cookie_list.php?s_no=<?php echo $s_no; ?>"><button class='kbt'>登録端末リスト確認・退職登録</button></a></p>
<?php echo $up_kyuyo_data?>
	<p><input type="checkbox" name="kyuyob" value="1">給与内容を新規追加</p>
<?php echo $new_kyuyo_data?>
	<p><a href= "../kyuyo/kyuyo_list.php?s_no=<?php echo $s_no; ?>" ><button class='kbt'>過去の給与データ確認</button></a></p>

