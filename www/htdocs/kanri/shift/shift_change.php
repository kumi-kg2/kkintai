<?php
//	シフト修正ページ(ajaxでshift_list_comp.phpにて表示)
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
	
	$week = array (
				'日',
				'月',
				'火',
				'水',
				'木', 
				'金', 
				'土'
			);
	
	$s_id = h($_GET['id']);
	//0なし日付
	$s_day = h(ltrim($_GET['day'], '0'));
	//0あり日付
	$ss_day =h($_GET['day']);
	
	$s_coad_day = "s_coad_".$s_day;
	$in_day1 = "in_time_".$s_day."_1";
	$out_day1 = "out_time_".$s_day."_1";
	$in_day2 = "in_time_".$s_day."_2";
	$out_day2 = "out_time_".$s_day."_2";
	
	$change_shift_data="";

	$sql = "SELECT * FROM shift_list WHERE s_id = ?";
	$rs = $db->prepare($sql);
	$data = array($s_id);
	$rs->execute($data);
	$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
	foreach ($rd as $row) {
		//社員NOから名前を取得
		$syain = new SYAIN();
		$syain->select_sno_syaindata(h($row['syain_no']), $db);
		$s_no = h($row['syain_no']);
		$s_name = $syain->name;
		
		$s_year = substr((h($row['s_year_month'])), 0 , 4);
		$s_month = substr((h($row['s_year_month'])), -2);
		$s_ym = $s_year."-".$s_month;
	
		if (($s_day >= 21) && ($s_day <= 31)) {
			$year_month = $s_ym;
		} else if (($s_day >= 1) && ($s_day <=20)) {
			//シフト月の1日
			$firstday = date($s_ym."-1");
			//シフト月の翌月(1～20日の月)
			$next_year_month = date('Y-m', strtotime($firstday.'+1 month'));
			$year_month = $next_year_month;
		}
		
		$change_s_ymd = $year_month."-".$ss_day;
		
		//曜日
		$shift_week = new DateTime($change_s_ymd);
		$w = (int)$shift_week->format('w');
		$date_week = $week[$w];
		
		$change_shift_data .="<p>".$change_s_ymd."(".$date_week.")";
		$change_shift_data .="<br>".$s_name."(".$s_no.")</p>";

	}

	$sql2 = "SELECT * FROM shift_master";
	$rs2 = $db->prepare($sql2);
	$rs2->execute();
	$rd2 = $rs2->fetchAll(PDO::FETCH_ASSOC);
	
	$select_shift_coad = "";
	$select_shift_coad .="<p>シフトコード：<select name='s_coad'>";
	$select_shift_coad .="<option value='H'>H</option>";
	foreach ($rd2 as $row) {
		$select_shift_coad .="<option value='".h($row['shift_coad'])."'>".h($row['shift_coad'])."</option>";
	}
	$select_shift_coad .="</select></p>";


?>
<h3>シフト修正</h3>
	<form method ="post" action="shift_change_comp.php?s_id=<?php echo $s_id; ?>&ym=<?php echo $year_month; ?>&day=<?php echo $ss_day; ?>">
<?php echo $change_shift_data; ?>
<?php echo $select_shift_coad; ?>
	<button class='kbt' name="changeb" id="changeb">シフト修正</button>
	</form>

