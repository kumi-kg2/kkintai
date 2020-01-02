<?php
	//勤怠打刻漏れ登録完了ページ
	//仮登録後→管理者へメール→OKしたら登録

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
	
	
	$f_id = h($_GET['f_id']);

	//勤怠ID
	if ($_GET['k_id'] == "" ){
		$k_id = "";
	} else {
		$k_id = h($_GET['k_id']);
	}	
	//勤務日
	if ($_GET['wd']) {
		$work_day = h($_GET['wd']);
		//日付を跨いでの退勤の場合の退勤時間の日付
		$out_time_day = date("Y-m-d" , strtotime($work_day." +1 day"));
	} else if ($_POST['work_day']) {
		$work_day = h($_POST['work_day']);
		$out_time_day = date("Y-m-d" , strtotime($work_day." +1 day"));
	} 
	
	

	//出勤時間
	if (isset($_POST['in_time'])) {
		$in_time = $work_day." ".h($_POST['in_time']);
	} 
	
	//退勤時間
	if ((isset($_GET['out_time'])) && (isset($_GET['td'])) && ($_GET['td'] == "1")) {
		//打刻忘れリストから登録→日付を跨いでの退勤の場合
		$out_time = $out_time_day." ".h($_GET['out_time']);
	} else if ((isset($_POST['out_time'])) && (isset($_POST['out_time_day']))) {
		//勤怠登録ページから日付を跨いでの退勤の場合
		//出退勤両方打刻忘れ 且、日付を跨いでの退勤の場合
		$out_time = $out_time_day." ".h($_POST['out_time']);
	} else if ((isset($_GET['out_time'])) && (!(isset($_POST['out_time_day'])))){
		//打刻忘れリストから退勤時間を登録
		$out_time = $work_day." ".h($_GET['out_time']);
	} else if ((isset($_POST['out_time']))  && (!(isset($_POST['out_time_day'])))) {
		//その他の退勤時間登録の場合
		$out_time = $work_day." ".h($_POST['out_time']);
	}
	$kf_html = "";
	$kf_ml = "";

	//出勤時間1の打刻忘れの場合
	if (($f_id == 0) && ($k_id == "")) {
		//出勤時間1を仮登録・退勤時間1を登録
		$sql = "INSERT INTO kintai_list(syain_no, work_day, out_time, f_in_time) ";
		$sql .= "VALUES (?, ?, ?, ?)";
		$rs = $db->prepare($sql);
		$in_data = array(
						$syain_no,
						$work_day,
						$out_time,
						$in_time
						);
		$rs->execute($in_data);
		$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
		
		$k_id =  $db->lastInsertId();
		
		$kf_html .= "打刻忘れの出勤時間1を仮登録<br>";
		$kf_html .= "退勤時間1を現時刻で打刻しました";
		
		$kf_ml .= "出勤時間1　".$in_time;
		
	//退勤時間1の打刻忘れの場合
	} else if (($f_id == 1) && (isset($k_id))) {
		//退勤時間1仮登録を登録する
		$sql = "UPDATE kintai_list SET f_out_time = ? WHERE k_id = ?";
		$rs = $db->prepare($sql);
		$out_data = array($out_time,$k_id);
		$rs->execute($out_data);
		$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
		//出勤時間2を登録する
		$sql2 = "INSERT INTO kintai_list(syain_no, work_day, in_time) ";
		$sql2 .= "VALUES (?, ?, ?)";
		$rs2 = $db->prepare($sql2);
		$in_data = array(
						$syain_no,
						$work_day,
						$in_time,
						);
		$rs2->execute($in_data);
		$rd2 = $rs2->fetchAll(PDO::FETCH_ASSOC);
		
		$kf_html .= "打刻忘れの退勤時間1を仮登録<br>";
		$kf_html .= "出勤時間2を現時刻で打刻しました";
		
		$kf_ml .= "退勤時間1　".$out_time;
		
	//出勤時間2の打刻忘れの場合
	} else if (($f_id == 2) && (isset($k_id))) {
		//出勤時間2を仮登録・退勤時間2を登録する
		$sql = "INSERT INTO kintai_list(syain_no, work_day, f_in_time, out_time) ";
		$sql .= "VALUES (?, ?, ?, ?)";
		$rs = $db->prepare($sql);
		$in_data = array(
						$syain_no,
						$work_day,
						$in_time,
						$out_time
						);
		$rs->execute($in_data);
		$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
		
		$k_id =  $db->lastInsertId();

		$kf_html .= "打刻忘れの出勤時間2を仮登録<br>";
		$kf_html .= "退勤時間2を現時刻で打刻しました";
		
		$kf_ml .= "出勤時間2　".$in_time;
		
	//退勤時間2の打刻忘れの場合
	} else if (($f_id == 3) && (isset($k_id))) {
		//退勤時間2を仮登録を登録する
		$sql = "UPDATE kintai_list SET f_out_time = ? WHERE k_id = ?";
		$rs = $db->prepare($sql);
		$out_data = array($out_time,$k_id);
		$rs->execute($out_data);
		$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
		
		$kf_html .= "打刻忘れの退勤時間2を仮登録しました";
		
		$kf_ml .= "退勤時間2　".$out_time;
	//日付を超えた場合の退勤時間登録
	} else if (($f_id == 4) && ($k_id == ""))  {
		//退勤時間を登録する
		$sql = "UPDATE kintai_list SET out_time = ? WHERE syain_no = ? ORDER BY k_id DESC LIMIT 1";
		$rs = $db->prepare($sql);
		$out_data = array($out_time,$syain_no);
		$rs->execute($out_data);
		$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
		
		$kf_html .= "退勤時間を登録しました";
	//前回出勤時の退勤打刻忘れ・出勤時間の登録の場合
	} else if (($f_id == 5) && (isset($k_id)))  {
		//退勤時間を登録する
		$sql = "UPDATE kintai_list SET f_out_time = ? WHERE k_id = ?";
		$rs = $db->prepare($sql);
		$out_data = array($out_time,$k_id);
		$rs->execute($out_data);
		$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
				
		//出勤時間を登録する
		//出勤日を現在の日時で取得する
		date_default_timezone_set('Asia/Tokyo');
		$n_work_day = date("Y-m-d");
		$n_in_time = $n_work_day." ".h($_POST['in_time']);
		$sql2 = "INSERT INTO kintai_list(syain_no, work_day, in_time) ";
		$sql2 .= "VALUES (?, ?, ?)";
		$rs2 = $db->prepare($sql2);
		$in_data = array(
						$syain_no,
						$n_work_day,
						$n_in_time
						);
		$rs2->execute($in_data);
		$rd2 = $rs2->fetchAll(PDO::FETCH_ASSOC);
		
		$kf_html .= "打刻忘れの退勤時間を仮登録<br>";
		$kf_html .= "出勤時間を現時刻で打刻しました";

		$kf_ml .= "退勤時間　".$out_time;
		
	//打刻忘れ(退勤)リストからの登録
	} else if (($f_id == 6) && (isset($k_id)))  {
		//退勤時間を登録する
		$sql = "UPDATE kintai_list SET f_out_time = ? WHERE k_id = ?";
		$rs = $db->prepare($sql);
		$out_data = array($out_time,$k_id);
		$rs->execute($out_data);
		$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
		
		$kf_html .= "打刻忘れの前回出勤時の退勤時間を仮登録しました";

		$kf_ml .= "退勤時間　".$out_time;
		
		//前回出勤時の出勤・退勤の両方打刻忘れ
	} else if (($f_id == 7) && (isset($k_id))) {
		//退勤時間1仮登録を登録する
		$sql = "UPDATE kintai_list SET f_out_time = ? WHERE k_id = ?";
		$rs = $db->prepare($sql);
		$out_data = array($out_time,$k_id);
		$rs->execute($out_data);
		$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
		
		$kf_html .= "打刻忘れの退勤時間1を仮登録しました<br>";

		$kf_ml .= "退勤時間1　".$out_time;

	} else if (($f_id == 99) && ($k_id == ""))  {
		//出勤・退勤時間を登録する
		$sql = "INSERT INTO kintai_list(syain_no, work_day, f_in_time, f_out_time) ";
		$sql .= "VALUES (?, ?, ?, ?)";
		$rs = $db->prepare($sql);
		$in_out_data = array(
						$syain_no,
						$work_day,
						$in_time,
						$out_time
						);
		$rs->execute($in_out_data);
		$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
		
		$k_id =  $db->lastInsertId();
		
		$kf_html .= "出勤・退勤時間を登録しました";
		$kf_ml .= "出勤時間　".$in_time;
		$kf_ml .= "\n退勤時間　".$out_time;
	}
	
	if (!($f_id == 4)) {
		$s_coad = "";
		$s_work_d = ltrim(date('d', strtotime($work_day)), "0");
		$s_work_ym = date('Ym', strtotime($work_day));

		if (($s_work_d >= 21) && ($s_work_d <= 31)) {
			$s_ym = $s_work_ym;
		} else if (($s_work_d >= 1) && ($s_work_d <=20)) {
			$s_ym = date('Ym', strtotime($s_work_ym.'-1 month'));
		}
		
		//打刻忘れ申請した日のシフト表示する
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
		$date = date("Y-m-d H:i:s");
		$m_accesIP = $_SERVER['REMOTE_ADDR'];
		$subject = "【".$kintai_name."】打刻漏れの申請登録がありました";
	
		$body = <<< __BODY__
{$kintai_name}にて、打刻漏れの申請登録がありました。
下記内容をご確認の上、勤怠登録の修正をよろしくお願いします。

【打刻漏れ申請内容】
社員番号：{$syain_no}
　名　前：{$name}

シフト予定：
{$work_day}『{$s_coad}』

勤怠情報：
{$kf_ml}

勤怠時間修正URL
http://s.ibg.jp/kanri/kintai/forget_kintai_comp.php?id={$k_id}
		
__BODY__;
		$header = "From:".$to;
		mb_send_mail($to, $subject, $body, $header);
	}
	
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css" href="menu.css">
<title>打刻忘れ入力完了</title>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.js"></script>
<script>
$(function(){
	$(window).load(function() {
		setTimeout(function(){
		window.location.href = '/';
		}, 3000);
	});
});
</script>
</head>
<?php include 'header.inc'; ?>
<body>
	<div class="breadcrumbs">
		<ul>
			<li><a href="/">猫カフェ 勤怠TOP</a></li>
			<li><a href="/menu/">勤怠メニュー</a></li>
			<li><a href="/menu/kintai.php">勤怠入力</a></li>
			<li>打刻忘れ入力完了</li>
		</ul>
	</div>
	<div>
		<h2>打刻忘れ入力完了</h2>
		<p><?php echo $kf_html; ?><br><br>
		自動的にTOPページへ移動します<br>
		移動しない場合は <a href="http://s.ibg.jp/">こちら</a>
		</p>
	</div>
</body>
