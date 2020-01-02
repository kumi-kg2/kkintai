<?php
//シフト一覧ページ
//月間か日別のシフトの一覧を表示する

	ini_set('display_errors', "On");

	include_once ('db/db.inc');
	include_once ('common.inc');
	
	$db = new DbConnect();
	
	$m_shift_data = "";
	$d_shift_data = "";
	
	$week = array (
				'日',
				'月',
				'火',
				'水',
				'木', 
				'金', 
				'土'
			);

	//月間シフトの場合
	if (isset($_POST['m_shift'])) {
		
		$m_shift = $_POST['m_shift'];
		$m_shiftday = $m_shift."/21";
		
		$month = str_replace("-", "",$m_shift);
		
		//指定された月のシフトがあるかを確認する
		$sql = "SELECT COUNT(*) AS cnt FROM shift_list WHERE s_year_month = ?";
		$rs = $db->prepare($sql);
		$data = array($month);
		$rs->execute($data);
		$rd = $rs->fetchAll(PDO::FETCH_ASSOC);

		foreach ($rd as $row){
			$cnt = $row["cnt"];
			if ($cnt == 0) {
				echo "指定された".$m_shift."月度のシフトはありません";
				exit;
			} else if ($cnt > 0 ) {
				
				$sql = "SELECT * FROM shift_list WHERE s_year_month = ?";
				$rs = $db->prepare($sql);
				$data = array($month);
				$rs->execute($data);
				$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
				
				$shift_time ="";
						
				foreach ($rd as $row) {
					$date = "";
					$no = "";
					$s_coad = "";
					$s_no = $row['syain_no'];
					$s_id = $row['s_id'];
					
					//社員NOから名前を取得
					$syain = new SYAIN();
					$syain->select_sno_syaindata($s_no, $db);
					$s_name = $syain->name;
					
					$no .= "<td>".$s_name."(".$s_no.")</td><td align='center' style='font-size: 10pt;'>出退勤</td>";
					$shift_time .="<tr>".$no;
					
					foreach ($row as $key=>$val) {
						//シフトコード
						if (strpos($key , 's_coad') !== false) {
							$shift_coad = "";
							if ($val == NULL) {
								$shift_coad .= "休";
							} else {
								$shift_coad .= h($val);
							}
						}
						//出勤1の時間
						if ((strpos($key , 'in_time') !== false) && (preg_match("/_1$/",$key))) {
							//シフトの日にち取得
							$in_date1 = substr((h($val)), 8, 2);
							//出勤1の時間取得
							$in_time1 = substr((h($val)), 10, 6);

							//21～月末までの曜日
							if (($in_date1 >= 21) &&  ($in_date1 <= 31)) {
								$month_day = $month.$in_date1;
								$shift_week = new DateTime($month_day);
								$w = (int)$shift_week->format('w');
								$date_week = $week[$w];
							//1～20日までの曜日
							} else if (($in_date1 >= 1) && ($in_date1 <=20)) {
								$firstday = date($m_shift."-1");
								//シフト月の翌月(1～20日の月)
								$next_month = date('Ym', strtotime($firstday.'+1 month'));
								$month_day = $next_month.$in_date1;
								$shift_week = new DateTime($month_day);
								$w = (int)$shift_week->format('w');
								$date_week = $week[$w];
							}
							$date .= "<th id='d".$in_date1."'>".$in_date1."<br>(".$date_week.")</th>";
							$shift_time .="<td align='center' class='tdshift' id='sid".$s_id."_d".$in_date1."' style='font-size: 10pt;' >".$shift_coad."<hr>".$in_time1;
						}
						//退勤1の時間
						if ((strpos($key , 'out_time') !== false) && (preg_match("/_1$/",$key))) {
							//退勤1の時間取得
							$out_time1 = substr((h($val)), 10, 6);
							$shift_time .=$out_time1;
						}
						//出勤2の時間
						if ((strpos($key , 'in_time') !== false) && (preg_match("/_2$/",$key))) {
							$in_date2 = substr((h($val)), 8, 2);
							//退勤1の時間取得
							$in_time2 = substr((h($val)), 10, 6);
							$shift_time .="<hr>".$in_time2;
						}
						//退勤2の時間
						if ((strpos($key , 'out_time') !== false) && (preg_match("/_2$/",$key))) {
							$out_time2 = substr((h($val)), 10, 6);
							$shift_time .= $out_time2."</td>";
						}
					}
					$shift_time .= "</tr>";
				}
				$m_shift_data .= "<p>".$m_shift."月度シフト一覧</p>";
				$m_shift_data .= "<table id='shift_data' border='1'><th>名前</th><th>　</th>".$date.$shift_time."</table>";
			}
		}
	}
	
	//日別シフトの場合
	if (isset($_POST['d_shift'])) {
		$d_shift = $_POST['d_shift'];
	
		$day = str_replace("-", "",$d_shift);
		
		$d_year = substr($day, 0, 4);
		$d_month = substr($day, 4, 2);
		//0なし日付
		$d_day = h(ltrim((substr($day, -2)), 0));
		//0あり日付
		$dd_day = (substr($day, -2));
		
		//21～月末の場合のs_year_monthの年月
		if (($d_day >= 21) && ($d_day <= 31)) {
			$s_year_month = substr($day, 0, -2);
		} else if (($d_day >= 1) && ($d_day <=20)) {
		//1～20日の場合のs_year_monthの年月(前月)
			$d_ym = $d_year."/".$d_month;
			//シフト月の1日
			$firstday = date($d_ym."/1");
			//前月
			$last_month = date('Ym', strtotime($firstday.'-1 month'));
			$s_year_month = $last_month;
		}
		
		//曜日の取得
		$shift_week = new DateTime($d_shift);
		$w = (int)$shift_week->format('w');
		$date_week = $week[$w];
		
		$in_day_1 = "in_time_".$d_day."_1";
		$in_day_2 = "in_time_".$d_day."_2";
		$out_day_1 = "out_time_".$d_day."_1";
		$out_day_2 = "out_time_".$d_day."_2";
		$shift_coad_day = "s_coad_".$d_day;

		
		$sql = "SELECT * FROM shift_list WHERE s_year_month = ?";
		$rs = $db->prepare($sql);
		$data = array($s_year_month);
		$rs->execute($data);
		$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
		
		$s_no = "";
		$s_name = "";
		$shift_coad = "";
		$in_time1 = "";
		$in_time2 = "";
		$out_time1 = "";
		$out_time2 = "";
		
		foreach ($rd as $row) {
		
			$s_id = $row['s_id'];
			
			//社員NOから名前を取得
			$syain = new SYAIN();
			$syain->select_sno_syaindata(h($row['syain_no']), $db);
			$s_no .= "<th>".h($row['syain_no'])."</th>";
			$s_name .= "<td>".$syain->name."</td>";
			
			if (($row[$shift_coad_day]) == NULL) {
				$shift_coad .= "<td id='sid".$s_id."_d".$dd_day."'>休</td>";
			} else {
				$shift_coad .= "<td id='sid".$s_id."_d".$dd_day."'>".h($row[$shift_coad_day])."</td>";
			}
			
			$in_time1 .= "<td>".substr((h($row[$in_day_1])), 10, 6)."</td>";
			$out_time1 .= "<td>".substr((h($row[$out_day_1])), 10, 6)."</td>";
			$in_time2 .= "<td>".substr((h($row[$in_day_2])), 10, 6)."</td>";
			$out_time2 .= "<td>".substr((h($row[$out_day_2])), 10, 6)."</td>";
		}
		
		//指定された日にちのシフトがあるかを確認する
		if (empty($in_time1)) {
			echo "指定された".$d_shift."のシフトはありません";
			exit;
		} else {
			$d_shift_data .= "<p>".$d_shift."(".$date_week.")　シフト</p>";
			$d_shift_data .= "<table id='shift_data_day' border='1' >";
			$d_shift_data .= "<tr><th>社員番号</th>".$s_no."</tr><tr><th>名前</th>".$s_name."</tr><tr><td>コード</td>".$shift_coad."</tr>";
			$d_shift_data .= "<td>出勤1</td>".$in_time1."</tr><tr><td>退勤1</td>".$out_time1."</tr>";
			$d_shift_data .= "<tr><td>出勤2</td>".$in_time2."</tr><tr><td>退勤2</td>".$out_time2."</tr></table>";
		}
	}
?>
<!DOCTYPE html>    
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>シフト一覧</title>
<style>
.clicked { background-color: #f00; }
</style>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.js"></script>
<script>
$(function(){
	//日付が00になるものは非表示(月末が31日までない場合00になる)
    $("#shift_data [id $= 'd00']").hide();
});
</script>
<body>
<?php echo $m_shift_data;  ?>
<?php echo $d_shift_data;  ?>
</body>
