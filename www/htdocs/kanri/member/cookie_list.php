<?php

//従業員登録端末(cookie)リスト
//該当する端末のみ削除・退職者の登録端末全て削除　選択ページ
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
	
	//社員Noから名前を取得
	$syain = new SYAIN();
	$syain->select_sno_syaindata($s_no, $db);
	$s_name = $syain->name;

	$sql = "SELECT * FROM ninsyou_list WHERE syain_no = ? ORDER BY n_id DESC";
	$rs = $db->prepare($sql);
	$data = array($s_no);
	$rs->execute($data);
	$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
	$kyuyo_data = "";
	foreach ($rd as $row){
		$kyuyo_data .= "<tr>";
		$kyuyo_data .= "<td align='center'  class='n_id' ><a>".h($row['n_id'])."</a></td>";
		$kyuyo_data .= "<td align='center' width='400'>".h($row['user_agent'])."</td>";
		$kyuyo_data .= "<td align='center'>".substr((h($row['touroku_day'])), 0, 10)."</td>";
		$kyuyo_data .= "<td align='center'>".substr((h($row['lastaccess'])), 0, 10)."</td>";
		$kyuyo_data .= "</tr>";
	}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>登録端末リスト</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css" href="/kanri/kanri.css">
<script src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.js"></script>
<script type="text/javascript" src="js/cookie.js"></script>
</head>
<?php include 'header.inc'; ?>
<body>
	<div class="breadcrumbs">
		<ul>
			<li><a href="/">猫カフェ 勤怠TOP</a></li>
			<li><a href="/kanri/">管理者メニュー</a></li>
			<li><a href="/kanri/member/">従業員管理</a></li>
			<li><a href="/kanri/member/member_kanri.php">従業員リスト</a></li>
			<li>登録端末リスト</li>
		</ul>
	</div>
	<h2>登録端末リスト</h2>
	<p>最終アクセス日を確認し、削除する端末の認証IDをクリックしてください<br>
	退職者の登録端末をすべて削除する場合は社員NOをクリックしてください</p>
	<p><b><?php echo $s_name; ?>【</b><b id ='s_id'><a><?php echo $s_no; ?></a></b><b>】　登録端末リスト</b></p>
		<table border="1" id="memberTable">
			<tr>
				<th>認証ID</th>
				<th>登録端末</th>
				<th>登録日</th>
				<th>最終アクセス日</th>
			</tr>
<?php 	echo ($kyuyo_data); ?>
		</table>
		<div id="delete_cookiedataarea"></div>
		<div id="delete_allcookiedataarea"></div>
</body>
