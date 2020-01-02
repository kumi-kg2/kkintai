<?php
	//従業員登録内容変更完了ページ
	
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
	
	date_default_timezone_set('Asia/Tokyo');
	$up_date = date("YmdHis");
	
/*	if (isset($_POST['up_jid'])) {
		$up_jid = h($_POST['up_jid']);
	}*/
	if (isset($_POST['up_sno'])) {
		$up_sno = h($_POST['up_sno']);
	}
	
	if (isset($_POST['up_kflg'])) {
		if ((h($_POST['up_kflg'])) == "1") {
			$up_k_flg = "1";
			$k_flg_html = "管理者登録しました。";
		}
	} else {
		$up_k_flg = "0";
		$k_flg_html = "";
	}
	$up_busyo = h($_POST['up_busyo']);
	$up_name = h($_POST['up_name']);
	$up_furi = h($_POST['up_furi']);
	$up_birth = h($_POST['up_birth']);
	$up_phone = h($_POST['up_phone']);

	//エラーチェック
	if ($up_busyo == "" ) {
    	echo "部署の未入力エラーです<br>";
	}
	if ($up_name == "" ) {
    	echo "名前の未入力エラーです<br>";
	}
	if ($up_furi == "" ) {
    	echo "フリガナの未入力エラーです<br>";
	}
	if (!(preg_match("/^[ァ-ヶー]+$/u", $up_furi))) {
        echo "フリガナの入力エラーです。全角カタカナで入力してください<br>";
    }
    if ($up_birth == "" ) {
    	echo "生年月日の未入力エラーです<br>";
	}
	if ($up_phone == "" ) {
    	echo "携帯番号の未入力エラーです<br>";
	}
	
	//仮：社員NOからデータを変更(今後社員NOを変更するようにすることがある場合はj_idで変更出来るようにする)
	$sql = "UPDATE syain_list SET busyo= ?, name= ?, furi= ?, birthday= ?, phone= ?, k_flg=?, updated= ? ";
	$sql .= "WHERE syain_no = ?";
	$rs = $db->prepare($sql);
	$data = array(
				$up_busyo,
				$up_name,
				$up_furi,
				$up_birth,
				$up_phone,
				$up_k_flg,
				$up_date,
				$up_sno
			);
	$rs->execute($data);
	$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
	
	$up_member_data = "";
	$up_member_data .= "<tr>";
	$up_member_data .= "<td>".$up_sno."</td>";
	$up_member_data .= "<td>".$up_busyo."</td>";
	$up_member_data .= "<td>".$up_name."</td>";
	$up_member_data .= "<td>".$up_furi."</td>";
	$up_member_data .= "<td>".$up_birth."</td>";
	$up_member_data .= "<td>".$up_phone."</td>";
	$up_member_data .= "</tr>";
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css" href="/kanri/kanri.css">
<title>従業員登録内容変更完了</title>
</head>
<?php include 'header.inc'; ?>
<body>
	<div class="breadcrumbs">
		<ul>
			<li><a href="/">猫カフェ 勤怠TOP</a></li>
			<li><a href="/kanri/">管理者メニュー</a></li>
			<li><a href="/kanri/member/">従業員管理</a></li>
			<li><a href="/kanri/member/member_kanri.php">従業員リスト</a></li>
			<li>従業員 登録内容変更完了</li>
		</ul>
	</div>
<h2>登録者情報</h2>
	<div>
		<p>登録内容を変更しました。<br><?php echo $k_flg_html; ?></p>
		<table>
			<tr>
				<th>社員番号</th>
				<th>部署</th>
				<th>名前</th>
				<th>フリガナ</th>
				<th>生年月日</th>
				<th>携帯番号</th>
			</tr>
<?php  echo $up_member_data; ?>
		</table>
	</div>
</body>
