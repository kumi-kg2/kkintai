<?php
	//従業員リスト&従業員登録内容変更ページ
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
	
	$sql = "SELECT * FROM syain_list";
	$rs = $db->prepare($sql);
	$rs->execute();
	$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
	
	$member_data = "";
	foreach ($rd as $row) {
		$member_data .= "<tr>";
		$member_data .= "<td class='s_no'><a>".h($row['syain_no'])."</a></td>";
		$member_data .= "<td id='busyo'>".h($row['busyo'])."</td>";
		$member_data .= "<td id='name'>".h($row['name'])."</td>";
		$member_data .= "<td>".h($row['furi'])."</td>";
		$member_data .= "<td>".h($row['birthday'])."</td>";
		$member_data .= "<td>".h($row['phone'])."</td>";
		$member_data .= "</tr>";
	}

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css" href="/kanri/kanri.css">
<script src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.js"></script>
<script type="text/javascript" src="js/up_member.js"></script>
<title>従業員リスト</title>
</head>
<?php include 'header.inc'; ?>
<body>
	<div class="breadcrumbs">
		<ul>
			<li><a href="/">猫カフェ 勤怠TOP</a></li>
			<li><a href="/kanri/">管理者メニュー</a></li>
			<li><a href="/kanri/member/">従業員管理</a></li>
			<li>従業員リスト</li>
		</ul>
	</div>
	<h2>従業員リスト</h2>
	<div>
		<div id="member_dataarea">
			<table id="memberTable">
				<tr>
					<th>社員番号</th>
					<th>部署</th>
					<th>名前</th>
					<th>フリガナ</th>
					<th>生年月日</th>
					<th>携帯番号</th>
				</tr>
	<?php 	echo ($member_data); ?>
			</table>
		</div>
		<div id="up_member_dataarea">
			<h3>登録内容変更</h3>
				<p>登録内容を変更する場合は社員番号をクリックしてください</p>
		</div>
	</div>
</body>
