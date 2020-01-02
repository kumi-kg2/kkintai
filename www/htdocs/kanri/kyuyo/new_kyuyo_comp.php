<?php
//新規給与内容登録完了ページ

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
	$new_kubun = h($_POST['new_kubun']);
	$new_ktanka = h($_POST['new_ktanka']);
	$new_ztanka = h($_POST['new_ztanka']);
	$new_startday = h($_POST['new_startday']);
	$new_kid = str_replace("-", "",$new_startday);
	$k_id = $new_kid."21";

	//社員Noから名前を取得
	$syain = new SYAIN();
	$syain->select_sno_syaindata($s_no, $db);
	$s_name = $syain->name;
	
	
	if ($new_kubun == "" ) {
    	echo "給与区分の未入力エラーです<br>";
	}
	if ($new_ktanka == "" ) {
    	echo "基本単価の未入力エラーです<br>";
	} else if (!(preg_match("/^[0-9]+$/", $new_ktanka))) {
		echo "基本単価の入力エラーです。半角数字で入力してください<br>";
	}
	if ($new_ztanka == "" ) {
    	echo "残業単価の未入力エラーです<br>";
	} else if (!(preg_match("/^[0-9]+$/", $new_ztanka))) {
		echo "残業単価の入力エラーです。半角数字で正しく入力してください<br>";
	}
	if ($new_startday == "" ) {
    	echo "開始日の未入力エラーです<br>";
	}


	$sql = "SELECT COUNT(*) AS cnt FROM kyuyo_list WHERE syain_no = ? AND kyuyo_id = ?";
	$rs = $db->prepare($sql);
	$data = array($s_no,$new_kid);
	$rs->execute($data);
	$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
	
	$new_kyuyo_data = "";

	foreach ($rd as $row){
		$cnt = $row["cnt"];
		if ($cnt == 0 ){
			$sql = "INSERT INTO kyuyo_list (kyuyo_id, syain_no, kubun, kihon_tanka, zangyou_tanka) ";
			$sql .= "VALUES(?, ?, ?, ?, ?)";
			$rs = $db->prepare($sql);
			$data = array(
						$k_id,
						$s_no,
						$new_kubun,
						$new_ktanka,
						$new_ztanka
					);
			$rs->execute($data);
			$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
			
			$new_kyuyo_data .= "<tr>";
			$new_kyuyo_data .= "<td>".$s_no."</td>";
			$new_kyuyo_data .= "<td>".$s_name."</td>";
			$new_kyuyo_data .= "<td>".$new_kubun."</td>";
			$new_kyuyo_data .= "<td>".$new_ktanka."</td>";
			$new_kyuyo_data .= "<td>".$new_ztanka."</td>";
			$new_kyuyo_data .= "<td>".$new_startday."-21</td>";
			$new_kyuyo_data .= "</tr>";
		} else {
			echo  "同じ開始日(給与ID)で登録している給与データがあります";
			exit;
		}
		
	}


?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="/kanri/kanri.css">
<title>給与区分新規追加完了</title>
</head>
<?php include 'header.inc'; ?>
<body>
	<div class="breadcrumbs">
		<ul>
			<li><a href="/">猫カフェ 勤怠TOP</a></li>
			<li><a href="/kanri/">管理者メニュー</a></li>
			<li><a href="/kanri/kyuyo/">給与管理</a></li>
			<li><a href="/kanri/kyuyo/kyuyo_data.php">給与区分リスト</a></li>
			<li>給与区分新規追加完了</li>
		</ul>
	</div>
	<div>
	<h2>給与区分新規追加完了</h2>
		<table>
			<tr>
				<th>社員番号</th>
				<th>名前</th>
				<th>区分</th>
				<th>基本給</th>
				<th>残業代</th>
				<th>開始年月</th>
			</tr>
<?php  echo $new_kyuyo_data; ?>
		</table>
	</div>
</body>
