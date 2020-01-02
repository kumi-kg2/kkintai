<?php
// シフトマスタ新規作成ページ
//仮　出退勤時間2は非表示
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
	
	//既存シフトマスタ一覧
	$sql = "SELECT * FROM shift_master ORDER BY work_time DESC, shift_in_time_1 ASC;";
	$rs = $db->prepare($sql);
	$rs->execute();
	$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
	
	$shiftmaster_data = "";
	
	foreach ($rd as $row) {
		$shift_in_time1 = substr((h($row['shift_in_time_1'])), 0, -3);
		$shift_out_time1 = substr((h($row['shift_out_time_1'])), 0, -3);
		$shift_in_time2 = str_replace('00:00', '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', substr((h($row['shift_in_time_2'])), 0, -3));
		$shift_out_time2 = str_replace('00:00', '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', substr((h($row['shift_out_time_2'])), 0, -3));
		$w_time = (h($row['work_time']));
		
		$shiftcoad = "";
		$shiftcoad .= "<tr>";
		$shiftcoad .= "<td id='s_cd".h($row['shift_coad'])."'><a>".h($row['shift_coad'])."</a></td>";
		$shiftcoad .= "<td>".$shift_in_time1."</td>";
		$shiftcoad .= "<td>".$shift_out_time1."</td>";
	//	$shiftcoad .= "<td>".$shift_in_time2."</td>";
	//	$shiftcoad .= "<td>".$shift_out_time2."</td>";
		$shiftcoad .= "<td>".$w_time."</td>";
		$shiftcoad .= "<td>".nl2br($row['biko'])."</td>";
		$shiftcoad .= "</tr>";
		
		
		$shiftmaster_data .= $shiftcoad;
	}

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css" href="/kanri/kanri.css"> 
<title>シフトマスタ一覧</title>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.js"></script>
<script type="text/javascript" src="js/shift_master.js"></script>
</head>
<?php include 'header.inc'; ?>
<body>
	<div class="breadcrumbs">
		<ul>
			<li><a href="/">猫カフェ 勤怠TOP</a></li>
			<li><a href="/kanri/">管理者メニュー</a></li>
			<li><a href="/kanri/shift/">シフト管理</a></li>
			<li>シフトマスタ一覧(作成・修正)</li>
		</ul>
	</div>
	<div id="comp_shiftmaster">
		<div id="shiftmaster_list">
			<h2>シフトマスタ一覧</h2>
			<table id="shiftmaster">
				<tr>
					<th>コード</th>
					<th>出勤時間</th>
					<th>退勤時間</th>
			<!--		<th>出勤時間2</th>
					<th>退勤時間2</th> -->
					<th>勤務時間</th>
					<th>　備　考　</th>
				</tr>
		<?php 	echo ($shiftmaster_data); ?>
			</table>
		</div>
		<div id="change_shiftmaster"></div>
		<div id="new_shiftmaster">
			<h3>シフトマスタ新規作成</h3>
				<p>シフトマスタを修正する場合は既存シフトマスタ一覧内の<br>修正したいシフトコードをクリックしてください</p>
				<form method ="post" action="shift_master_comp.php">
					<p>シフトコード：<input type="text" id="coad" name="coad" size="3" maxlength="3"></p>
					<p>出勤時間：<input class="time1" type="time" id="in_time1" name="in_time1" step="1800" > / 
					退勤時間：<input class="time1" type="time" id="out_time1" name="out_time1" step="1800" ></p>
			<!--		<p>出勤時間2：<input class="time2" type="time" id="in_time2" name="in_time2" step="1800" > / 
					退勤時間2：<input class="time2" type="time" id="out_time2" name="out_time2" step="1800" ></p> -->
					<p>勤務時間：<input type="text" id="work_time" name="work_time" size="3" maxlength="3"></p>
					<p>備　考　：<textarea name="biko" id="biko" cols="40" rows="8"></textarea></p>
					<p><button class='kbt' name="mtourokub">シフトマスタ新規登録</button> 
				</form>
		</div>
	</div>
</body>
