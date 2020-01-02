<?php
	//従業員の新規登録完了ページ
	
	include_once ('db/db.inc');
	include_once ('common.inc');

	$allowIP = '122.215.65.219';
	$accesIP = $_SERVER['REMOTE_ADDR'];
	
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
	
	$s_no = h($_POST['syainno']);
	
	if (isset($_POST['k_flg'])) {
		if ((h($_POST['k_flg'])) == "1") {
			$k_flg = "1";
			$k_flg_html = "管理者登録しました。";
		}
	} else {
		$k_flg = "0";
		$k_flg_html = "";
	}
	$busyo = h($_POST['busyo']);
	$name = h($_POST['name']);
	$furi = h($_POST['furi']);
	$birth = h($_POST['birth']);
	$phone = h($_POST['phone']);
	$kubun = h($_POST['kkubun']);
	$k_tanka = h($_POST['kktanka']);
	$z_tanka = h($_POST['kztanka']);
	$startday = h($_POST['kstartday']);

	//エラーチェック
	if ($s_no == "" ) {
    	echo "社員番号の未入力エラーです<br>";
	}
	if ($busyo == "" ) {
    	echo "部署の未入力エラーです<br>";
	}
	if ($name == "" ) {
    	echo "名前の未入力エラーです<br>";
	}
	if ($furi == "" ) {
    	echo "フリガナの未入力エラーです<br>";
	}
	if (!(preg_match("/^[ァ-ヶー]+$/u", $furi))) {
        echo "フリガナの入力エラーです。全角カタカナで入力してください<br>";
    }
    if ($birth == "" ) {
    	echo "生年月日の未入力エラーです<br>";
	}
	if ($phone == "" ) {
    	echo "携帯番号の未入力エラーです<br>";
	} else if (!(mb_strlen($phone) == 11)) {
		echo "携帯番号の入力エラーです。正しく入力してください<br>";
	}
	if ($kubun == "" ) {
    	echo "給与区分の未入力エラーです<br>";
	}
	if ($k_tanka == "" ) {
    	echo "基本単価の未入力エラーです<br>";
	} else if (!(preg_match("/^[0-9]+$/", $k_tanka))) {
		echo "基本単価の入力エラーです。半角数字で入力してください<br>";
	}
	if ($z_tanka == "" ) {
    	echo "残業単価の未入力エラーです<br>";
	} else if (!(preg_match("/^[0-9]+$/", $z_tanka))) {
		echo "残業単価の入力エラーです。半角数字で正しく入力してください<br>";
	}
	if ($startday == "" ) {
    	echo "開始日の未入力エラーです<br>";
	}
	
	$kyuyo_id = str_replace("-", "",$startday);
	$k_id = $kyuyo_id."21";
		
	//社員番号が重複していないかチェックする
	$sql = "SELECT COUNT(*) AS cnt FROM syain_list WHERE syain_no = ?";
	$rs = $db->prepare($sql);
	$data = array($s_no);
	$rs->execute($data);
	$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
	
	foreach ($rd as $row){
		$cnt = $row["cnt"];
		if ($cnt == 0 ){
		//社員番号ok→新規登録
			$sql = "INSERT INTO syain_list (syain_no, busyo, name, furi, birthday, phone, k_flg) ";
			$sql .= "VALUES(?, ?, ?, ?, ?, ?, ?)";
			$rs = $db->prepare($sql);
			$data = array(
						$s_no,
						$busyo,
						$name,
						$furi,
						$birth,
						$phone,
						$k_flg
					);
			$rs->execute($data);
			$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
			
			$sql2 = "INSERT INTO kyuyo_list (kyuyo_id, syain_no, kubun, kihon_tanka, zangyou_tanka) ";
			$sql2 .= "VALUES(?, ?, ?, ?, ?)";
			$rs2 = $db->prepare($sql2);
			$kdata = array(
						$k_id,
						$s_no,
						$kubun,
						$k_tanka,
						$z_tanka
					);
			$rs2->execute($kdata);
			$rd2 = $rs2->fetchAll(PDO::FETCH_ASSOC);
			
			
		} else if ($cnt > 0 ) {
			echo "既に登録済の社員番号です。";
			exit;
		}
	}
	
	$new_member_data = "";
	$new_member_data .= "<tr>";
	$new_member_data .= "<td>".$s_no."</td>";
	$new_member_data .= "<td>".$busyo."</td>";
	$new_member_data .= "<td>".$name."</td>";
	$new_member_data .= "<td>".$furi."</td>";
	$new_member_data .= "<td>".$birth."</td>";
	$new_member_data .= "<td>".$phone."</td>";
	$new_member_data .= "</tr>";
	
	$new_kyuyo_data = "";
	$new_kyuyo_data .= "<tr>";
	$new_kyuyo_data .= "<td>".$kubun."</td>";
	$new_kyuyo_data .= "<td>".$k_tanka."</td>";
	$new_kyuyo_data .= "<td>".$z_tanka."</td>";
	$new_kyuyo_data .= "<td>".$startday."-21</td>";
	$new_kyuyo_data .= "<tr>";

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css" href="/kanri/kanri.css">
<title>新規登録完了</title>
<?php include 'header.inc'; ?>
</head>
<body>
	<div class="breadcrumbs">
		<ul>
			<li><a href="/">猫カフェ 勤怠TOP</a></li>
			<li><a href="/kanri/">管理者メニュー</a></li>
			<li><a href="/kanri/member/">従業員管理</a></li>
			<li><a href="/kanri/member/new_member.php">従業員新規登録</a></li>
			<li>従業員新規登録完了</li>
		</ul>
	</div>
<h2>新規登録者情報</h2>
	<div id="newmember_dataarea">
		<p>下記内容で新規登録完了しました。</p>
		<p>従業員登録内容<br><?php echo $k_flg_html; ?></p>
		<table id="j_data">
			<tr>
				<th>社員番号</th>
				<th>部署</th>
				<th>名前</th>
				<th>フリガナ</th>
				<th>生年月日</th>
				<th>携帯番号</th>
			</tr>
<?php 	echo ($new_member_data); ?>
		</table>
		<p>給与登録内容</p>
		<table id="k_data">
			<tr>
				<th>給与区分</th>
				<th>基本給</th>
				<th>残業代</th>
				<th>開始日</th>
			</tr>
<?php 	echo ($new_kyuyo_data); ?>
		</table>
	</div>
</body>

