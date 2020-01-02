<?php
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
	
	$s_no = h($_GET['s_no']);
	
	//前のページに戻るためのID
	$url_k_id = h($_GET['k_id']);
	
	//今日の日付を取得する
	date_default_timezone_set('Asia/Tokyo');
	$now_date = date("Y-m");
	$now_day = date("d");
	
	//〇月度(21～20日)
	//前月度からのみ確認出来るようにするので、ひと月度前の年月度を取得
	if (($now_day >= 21) && ($now_day <= 31)) {
		$y_month = date("Y-m", strtotime('-1 month'));
	} else if (($now_day >= 1) && ($now_day <=20)) {
		$y_month = date("Y-m", strtotime('-2 month'));
	}	
	
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="/kanri/kanri.css">
<title>過去給与検索</title>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.js"></script>
<script>
$(function(){
	$('form').submit(function() {
		
		let shift = $('input[name="kyuyo"]:checked').val();
		//入力エラーチェック
		if (($("#m_shift").val() == "") && (shift == "month") ) {
			alert ("年/月が未選択です");
			return false;
		}
	});
</script>
</head>
<?php include 'header.inc'; ?>
<body>
	<div class="breadcrumbs">
		<ul>
			<li><a href="/">猫カフェ 勤怠TOP</a></li>
			<li><a href="/kanri/">管理者メニュー</a></li>
			<li><a href="/kanri/kyuyo/">給与管理</a></li>
			<li><a href="/kanri/kyuyo/add_kyuyo.php">給与リスト</a></li>
			<li><a href="/kanri/kyuyo/now_kyuyo.php?s_no=<?php echo $s_no ?>&k_id=<?php echo $url_k_id; ?>">今月度給与データ</a></li>
			<li>過去給与検索</li>
		</ul>
	</div>
	<div>
		<h2>過去給与検索</h2>
		<form action = "past_kyuyo_list.php?s_no=<?php echo $s_no; ?>&k_id=<?php echo $url_k_id; ?>" method = "post">
			<p><input type="month" id="m_kyuyo" name="m_kyuyo" max="<?php echo $y_month; ?>" type="text" /><input type ="submit" value = "確認"></p>
		</form>
		<p>例:2019年10月を選択の場合…<br>2019年10月21日～2019年11月20日(2019年10月度)</p>
	</div>
</body>

