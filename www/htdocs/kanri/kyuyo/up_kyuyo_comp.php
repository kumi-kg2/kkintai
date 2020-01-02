<?php
//給与内容変更完了ページ
	
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
	$up_kid = h($_POST['up_kid']);
	$up_kubun = h($_POST['up_kubun']);
	$up_ktanka = h($_POST['up_ktanka']);
	$up_ztanka = h($_POST['up_ztanka']);
	
	//社員Noから名前を取得
	$syain = new SYAIN();
	$syain->select_sno_syaindata($s_no, $db);
	$s_name = $syain->name;
	
	
	date_default_timezone_set('Asia/Tokyo');
	$up_date = date("YmdHis");
	
	//エラーチェック
	if ($up_kubun == "" ) {
    	echo "区分の未入力エラーです<br>";
	}
	if ($up_ktanka == "" ) {
    	echo "基本単価の未入力エラーです<br>";
	} else if (!(preg_match("/^[0-9]+$/", $up_ktanka))) {
    	echo "基本単価の入力エラーです、基本単価は半角数字で入力してください<br>";
	}
	if ($up_ztanka == "" ) {
    	echo "残業単価の未入力エラーです<br>";
	} else if (!(preg_match("/^[0-9]+$/", $up_ztanka))) {
    	echo "残業単価の入力エラーです、残業単価は半角数字で入力してください<br>";
	}
	
	$sql = "UPDATE kyuyo_list SET kubun= ?, kihon_tanka= ?, zangyou_tanka= ?, updated= ? ";
	$sql .= "WHERE syain_no = ? AND kyuyo_id = ?";
	$rs = $db->prepare($sql);
	$data = array(
				$up_kubun,
				$up_ktanka,
				$up_ztanka,
				$up_date,
				$s_no,
				$up_kid
			);
	$rs->execute($data);
	$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
	
	$up_kyuyo_data = "";
	$up_kyuyo_data .= "<tr>";
	$up_kyuyo_data .= "<td>".$s_no."</td>";
	$up_kyuyo_data .= "<td>".$s_name."</td>";
	$up_kyuyo_data .= "<td>".$up_kid."</td>";
	$up_kyuyo_data .= "<td>".$up_kubun."</td>";
	$up_kyuyo_data .= "<td>".$up_ktanka."</td>";
	$up_kyuyo_data .= "<td>".$up_ztanka."</td>";
	$up_kyuyo_data .= "</tr>";
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="/kanri/kanri.css">
<title>給与区分変更完了</title>
</head>
<?php include 'header.inc'; ?>
<body>
<h2>給与区分変更完了</h2>
	<div class="breadcrumbs">
		<ul>
			<li><a href="/">猫カフェ 勤怠TOP</a></li>
			<li><a href="/kanri/">管理者メニュー</a></li>
			<li><a href="/kanri/kyuyo/">給与管理</a></li>
			<li><a href="/kanri/kyuyo/kyuyo_data.php">給与区分リスト</a></li>
			<li>給与区分変更完了</li>
		</ul>
	</div>
	<div>
		<p>登録内容を変更しました。</p>
		<table>
			<tr>
				<th>社員番号</th>
				<th>名前</th>
				<th>給与ID</th>
				<th>区分</th>
				<th>基本給</th>
				<th>残業代</th>
			</tr>
<?php  echo $up_kyuyo_data; ?>
		</table>
	</div>
</body>
