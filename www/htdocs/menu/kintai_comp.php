<?php
	//出退勤登録完了ページ
	//IPアドレスOK・cookie認証済のみアクセス可→勤怠時間を登録

	include_once ('db/db.inc');	
	include_once ('common.inc');
	
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
	//jsから勤務時間を取得
	$time = urldecode($_GET['k_time']);
//	$syain_no = urldecode($_GET['s_no']);

	//社員Noから名前を取得
	$syain = new SYAIN();
	$syain->select_sno_syaindata($syain_no, $db);
	$name = $syain->name;
	
	date_default_timezone_set('Asia/Tokyo');
	$workday = date("Y-m-d");
	
	//出勤日(work_day)が登録済みかをカウントして確認する
	$sql = "SELECT COUNT(*) AS cnt FROM kintai_list WHERE syain_no = ? AND work_day=?";
	$rs = $db->prepare($sql);
	$data = array($syain_no, $workday);
	$rs->execute($data);
	$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
	
	$kintai_ms ="";
	
	foreach ($rd as $row){
		$cnt = $row["cnt"];
		//出勤時間1を新規登録する
		if ($cnt == 0 ) {
			$sql = "INSERT INTO kintai_list(syain_no, work_day, in_time, out_time, biko) ";
			$sql .= "VALUES (?, ?, ?, ?, ?)";
			$rs = $db->prepare($sql);
			$in_data = array(
							$syain_no,
							$workday,
							$time,
							NULL,
							NULL
							);
			$rs->execute($in_data);
			$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
			$kintai_ms .= '出勤時間1を登録しました';
			
		} else if (( 0 < $cnt ) && ( $cnt <= 2)) {
			$sql = "SELECT * FROM kintai_list WHERE syain_no = ? AND work_day=?";
			$kintaidata = array($syain_no, $workday);
			$rs = $db->prepare($sql);
			$rs->execute($kintaidata);
			$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
			
			foreach ($rd as $row){
				$k_id = $row['k_id'];
				$syain_no = $row['syain_no'];
				$work_day = $row['work_day'];
				$intime= $row['in_time'];
				$f_intime= $row['f_in_time'];
				$outtime= $row['out_time'];
				$f_outtime= $row['f_out_time'];
			}
			
			if (($cnt == 1 ) && ((!($intime == "" )) or (!($f_intime == "" ))) && ((!($outtime =="")) or (!($f_outtime =="")))) {
				//出勤時間2を新規登録する
				$sql = "INSERT INTO kintai_list(syain_no, work_day, in_time, out_time, biko) ";
				$sql .= "VALUES (?, ?, ?, ?, ?)";
				$rs = $db->prepare($sql);
				$in_data = array(
								$syain_no,
								$workday,
								$time,
								NULL,
								NULL
								);
				$rs->execute($in_data);
				$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
				$kintai_ms .= '出勤時間2を登録しました';
				
			} else if (($cnt == 1 ) && (!($intime == "" ))) {
				//退勤時間1を登録する
				$sql = "UPDATE kintai_list SET out_time= ? ";
				$sql .= "WHERE k_id = ?";
				$rs = $db->prepare($sql);
				$out_data = array($time, $k_id);
				$rs->execute($out_data);
				$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
				$kintai_ms .= '退勤時間1を登録しました';
				
			} else if (($cnt == 2 ) && ((isset($intime)) or (isset($f_intime))) && ((isset($outtime)) or (isset($f_outtime)))) {
				$kintai_ms .= '登録済です';
				
			} else if (($cnt == 2 ) && ((empty($outtime)) or (empty($f_outtime)))) {
				//退勤時間2を登録する
				$sql = "UPDATE kintai_list SET out_time= ? ";
				$sql .= "WHERE k_id = ?";
				$rs = $db->prepare($sql);
				$out_data = array($time, $k_id);
				$rs->execute($out_data);
				$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
				$kintai_ms .= '退勤時間2を登録しました';
			} 
		}
	}
?>
<!DOCTYPE html>
<html>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css" href="menu.css">
<title>勤怠入力完了</title>
<head>
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
			<li>勤怠入力</li>
		</ul>
	</div>
<div>
	<h2>勤怠入力完了</h2>
	<p>社員No：<?php echo $syain_no; ?></p>
	<p>名　前 ：<?php echo h($name); ?></p>
	<!-- 共通メッセージ表示(仮)
	<div id="cmms"></div>
	-->
	<!-- 個人メッセージ表示(仮)
	<div id="psms"></div> 
		<input type="button" id="kintaibut" value="出勤・退勤">-->
	<p><?php echo $kintai_ms; ?><br><br>
	自動的にTOPページへ移動します<br>
	移動しない場合は <a href="http://s.ibg.jp/">こちら</a>
	</p>
</div>
</body>


