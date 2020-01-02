<?php
	//従業員認証ページ
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
	
	$pm_no = "0"; //認証許可FLGが0

	$sql = "SELECT COUNT(*) AS cnt FROM ninsyou_list WHERE permission = ?";
	$rs = $db->prepare($sql);
	$data = array($pm_no);
	$rs->execute($data);
	$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
	
	$unapproved_data = "";
	$unapproved_html = "";
	
	foreach ($rd as $row){
		$cnt = $row["cnt"];
		
		if ($cnt >= 1 ){
			//認証されていない人を出す(認証許可FLGが0のデータ)
			$sql = "SELECT * FROM ninsyou_list WHERE permission = ?"; 
			$rs = $db->prepare($sql);
			$data = array($pm_no);
			$rs->execute($data);
			$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
			
			foreach ($rd as $row){
				$n_id_db = h($row['n_id']);
				$s_no_db = h($row['syain_no']);
				$u_agent_db = ($row['user_agent']);
				$t_day_db = ($row['touroku_day']);
				
				$unapproved_data .= '<tr>';
				$unapproved_data .= '<td class="idno"><a>'.$n_id_db.'</a></td>';
				$unapproved_data .= '<td>'.$s_no_db.'</td>';
				$unapproved_data .= '<td width="400">'.$u_agent_db.'</td>';
				$unapproved_data .= '<td>'.$t_day_db.'</td>';
				$unapproved_data .= '</tr>';
			}
		}
	}
	
	$unapproved_html .= "<table><tr><th>ID</th><th>社員番号</th><th>登録端末</th><th>登録日時</th></tr>";
	$unapproved_html .= $unapproved_data;
	$unapproved_html .= "</table>";
	
	
	//従業員一覧データ
	//届いたメールの内容と一致するかを分かり易く確認するため
	$sql2 = "SELECT * FROM syain_list";
	$rs2 = $db->prepare($sql2);
	$rs2->execute();
	$rd2 = $rs2->fetchAll(PDO::FETCH_ASSOC);
	
	$member_data = "";
	foreach ($rd2 as $row) {
		$member_data .= "<tr>";
		$member_data .= "<td>".h($row['syain_no'])."</td>";
		$member_data .= "<td>".h($row['busyo'])."</td>";
		$member_data .= "<td>".h($row['name'])."</td>";
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
<script type="text/javascript" src="js/ninsyou.js"></script>
<title>認証待ちリスト</title>
<?php include 'header.inc'; ?>
</head>
<body>
	<div class="breadcrumbs">
		<ul>
			<li><a href="/">猫カフェ 勤怠TOP</a></li>
			<li><a href="/kanri/">管理者メニュー</a></li>
			<li><a href="/kanri/member/">従業員管理</a></li>
			<li>認証待ちリスト</li>
		</ul>
	</div>
	<h2>認証待ちリスト</h2>
	<div>
		<div id="unapproved_dataarea">
<?php
	if (!($unapproved_data == "")) {
		echo $unapproved_html;
	} else {
		echo "<p>現在、認証待ちの従業員データはありません</p>";
	}
?>
		</div>
	<h3>従業員リスト</h3>
		<div id="member_dataarea">
			<table>
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
		<p>メールの新規登録者情報や従業員リストなどを見て<br>
		認証待ちデータと情報が合致するかを確認してください。<br>
		認証する際は認証待ちデータの該当する<font color="#F00">【ID番号】</font>(数字)<br>
		をクリック → OKを選択し、認証してください。</p>
		<div id="authenticated_dataarea"></div>
	</div>
</body>
