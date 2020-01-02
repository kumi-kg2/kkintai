<?php
	include_once ('db/db.inc');
	include_once ('common.inc');		
	
	$db = new DbConnect();
	
	$a_check = authcheck();

	if ($a_check == 0) {
		//Cookieない・IPアドレスOK→Cookie登録
		//アクセスok
	} else {
		header ("Location: /");
		exit;
	}
	
	//従業員一覧データ
	$sql = "SELECT * FROM syain_list";
	$rs = $db->prepare($sql);
	$rs->execute();
	$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
	
	$member_data = "";
	foreach ($rd as $row) {
		$member_data .= "<tr>";
		$member_data .= "<td id=s_no".h($row['syain_no'])." >".h($row['syain_no'])."</td>";
		$member_data .= "<td>".h($row['busyo'])."</td>";
		$member_data .= "<td>".h($row['name'])."</td>";
		$member_data .= "<td>".h($row['furi'])."</td>";
	//	$member_data .= "<td>".$row['birthday']."</td>";
	//	$member_data .= "<td>".$row['phone']."</td>";
	//	$member_data .= "<td>".$row['created']."</td>";
		$member_data .= "</tr>";
	}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css" href="menu/menu.css">
<title>登録画面</title>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.js"></script>
<script>
$(function(){
	
	$("[id^='s_no']").click(function(){
		$("[id^='s_no']").removeClass("clicked");
		$(this).addClass("clicked");
		let s_no = $(this).attr("id"); 
		let no = s_no.substr(4);
		$("#syainno").val(no);
	});

	$("#tourokub").click( function() {

//	$('form').submit(function() {
	
  		let syainno = $("#syainno").val();
  		if ( syainno == "" ) {
			alert("社員番号が選択されていません。選択してください");
			return false;
		}
		if (!(syainno.match(/^[A-Za-z0-9]*$/))) {
			alert("社員番号が正しく入力されていません。従業員リスト内の社員番号を選択してください");
			return false;
		}
	});
	
});
</script>
</head>
<?php include 'header.inc'; ?>
<body>
<div>
	<h2>登録画面</h2>
		<div id="member_dataarea">
				<p>該当する社員番号をクリックしてください</p>
				<table border="1">
					<tr>
						<th>社員番号</th>
						<th>部署</th>
						<th>名前</th>
						<th>フリガナ</th>
					<!--	<th>生年月日</th> -->
					<!--	<th>携帯番号</th> -->
					<!--	<th>登録日</th> -->
					</tr>
		<?php 	echo ($member_data); ?>
				</table>
			</div>
		<form method ="post" action="touroku_comp.php">
			<p>社員番号：<input type="text" id="syainno" name="syainno" size="10" maxlength="10">
			<button class='mbt' name="tourokub" id="tourokub">登録申請</button>　</p>
		</form>
</div>

</body>