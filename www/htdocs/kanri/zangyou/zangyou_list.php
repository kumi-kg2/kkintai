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

	
	//社員の一覧を表示
	$sql = "SELECT * FROM syain_list";
	$rs = $db->prepare($sql);
	$rs->execute();
	$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
	$syaindata ="";
	foreach ($rd as $row) {
		$syaindata .= "<tr>";
		$syaindata .= "<td class='s_no'><a>".h($row['syain_no'])."</a></td>";
		$syaindata .= "<td>".h($row['busyo'])."</td>";
		$syaindata .= "<td>".h($row['name'])."</td>";
		$syaindata .= "<td><a href='http://s.ibg.jp/kanri/kintai/kintai_list.php?s_no=".h($row['syain_no'])."'>▼</a></td>";
		$syaindata .= "</tr>";
	}

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="/kanri/kanri.css">
<script src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.js"></script>
<script type="text/javascript" src="js/zangyou_list.js"></script>
<title>残業登録リスト</title>
</head>
<?php include 'header.inc'; ?>
<body>
<h2>残業登録リスト</h2>
	<div class="breadcrumbs">
		<ul>
			<li><a href="/">猫カフェ 勤怠TOP</a></li>
			<li><a href="/kanri/">管理者メニュー</a></li>
			<li><a href="/kanri/zangyou">残業時間管理</a></li>
			<li>残業登録リスト</li>
		</ul>
	</div>
	<div>
		<div id="syain_dataarea">
			<h3>社員一覧</h3>
			<p>残業時間を修正・追加したい社員番号をクリックしてください</p>
			<p>▼をクリックすると勤怠ページにアクセス→勤怠リストを確認できます<br>
			勤怠リストページからでも残業時間などの修正ができます</p>
			<table id="memberTable">
				<tr>
					<th>社員番号</th>
					<th>部署</th>
					<th>名前</th>
					<th>　</th>
				</tr>
	<?php echo ($syaindata); ?>
			</table>
		</div>
		<form method ="post" action="up_zangyou_comp.php">
		<div id="zangyou_dataarea"></div>
		<div id="up_zangyou_dataarea"></div>
	</div>
</body>
