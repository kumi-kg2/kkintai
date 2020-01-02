<?php
//従業員各自の勤怠日時一覧ページ

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
	
	//現在日時
	date_default_timezone_set('Asia/Tokyo');
	$now_day = date("Y-m-d");
	// 1日前
	$y_day = date("Y-m-d",strtotime("-1 day"));
	$last_month_day = date("Y-m-d",strtotime("-1 month"));
	
	//社員Noから名前を取得
	$syain = new SYAIN();
	$syain->select_sno_syaindata($s_no, $db);
	$name = $syain->name;

	$sql = "SELECT * FROM kintai_list WHERE (work_day BETWEEN ? AND ?) AND syain_no = ? ORDER BY work_day DESC;";
	$rs = $db->prepare($sql);
	$data = array(
				$last_month_day,
				$y_day,
				$s_no
				);
	$rs->execute($data);
	$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
	$kintai_data = "";
	$kintai_data .= "<option value=''>出勤日時を選択</option>";
	foreach ($rd as $row){
		$k_id = h($row['k_id']);
		$in_time = h($row['in_time']);
		$f_in_time = h($row['f_in_time']);
		
		//出勤時間を打刻忘れした場合
		if ($in_time == "") {
			$k_intime = date('Y-m-d H:i',  strtotime($f_in_time));
		} else if (!($in_time == "")) {
		//出勤時間を打刻済の場合
			$k_intime = date('Y-m-d H:i',  strtotime($in_time));
		}
		$kintai_data .= "<option value='id".$k_id."'>".$k_intime."</option>";
	}
?>
<h3>勤怠時間修正</h3>
	<p>社員番号：<?php echo $s_no; ?>　名前：<?php echo $name; ?>
	　<a href="add_kintai_data.php?s_no=<?php echo $s_no; ?>"><button class='kbt'>勤怠時間 新規追加</button></a></p>
	<p><?php echo $now_day;?>(今日からひと月前の出勤データを表示)<br>※当日のデータは修正不可</p>
	<select name="kintaidata" id="kintaidata">
	<?php echo $kintai_data; ?>
	</select>
