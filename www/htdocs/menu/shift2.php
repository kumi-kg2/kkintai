<?php
	// 個人シフト確認ページ
	// 認証されたcookieのみアクセス可(IPアドレスは指定なし)

	include_once ('db/db.inc');
	include_once ('common.inc');
	
	$db = new DbConnect();
	$a_check = authcheck();

	if (isset($_COOKIE['AUTH'])) {
		$cookie = ($_COOKIE['AUTH']);
		$cf_check = checkCookie_checkFlg($cookie, $db);
	}
	
	// Cookieあり(DB内と一致)・IPアドレスOK・認証許可FLG1→アクセスOK
	if (($a_check == 99) && ($cf_check == 1)) {
		//Cookieから社員Noを取得
		$syain_no = (checkCookie($cookie, $db))->syain_no;
	// Cookieあり(DB内と一致)・IPアドレスNG・認証許可FLG1→アクセスOK
	} else if (($a_check == 2 ) && ($cf_check == 1)) {
		//Cookieから社員Noを取得
		$syain_no = (checkCookie($cookie, $db))->syain_no;
	} else {
		header ("Location: /");
		exit;
	}
	
	//社員Noから名前を取得
	$syain = new SYAIN();
	$syain->select_sno_syaindata($syain_no, $db);
	$name = $syain->name;

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>シフト確認</title>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.js"></script>
<script>
$(function(){
	$('form').submit(function() {
		
		let shift = $('input[name="shift"]:checked').val();
		
		//入力エラーチェック
		if (($("#m_shift").val() == "") && (shift == "month") ) {
			alert ("月間シフトの年/月が未入力です");
			return false;
		}
		if (($("#d_shift").val() == "") && (shift == "day") ) {
			alert ("日別シフトの年/月/日が未入力です");
			return false;
		}
	});
	
	//月間シフトか日別シフトか選択する
	$("#d_shift").prop("disabled", true);
	$('input[value="month"]').change(function() {
		$("#m_shift").prop("disabled", false);
		$("#d_shift").prop("disabled", true);
	});
/*	$('input[value="day"]').change(function() {
		$("#m_shift").prop("disabled", true);
		$("#d_shift").prop("disabled", false);
	});*/

});
</script>
</head>
<body>
	<div>
		<h2>シフト確認ページ</h2>
			<form action = "shift_comp.php" method = "post">
				<p>社員No：<?php echo $syain_no; ?></p>
				<p>名　前 ：<?php echo h($name); ?></p>
				仮ここのページにて給与目安分かるようにする
				
				<p><input type="radio" name="shift" id="shift" value="month" checked>月間シフト　<input type="month" id="m_shift" name="m_shift" type="text" /></p>
				<p><input type="radio" name="shift" id="shift" value="day">日別シフト　<input type="date" id="d_shift" name="d_shift" type="text" /></p>
				<input type ="submit" value = "確認">
				<p>月間シフト・日別シフトどちらかにチェックを入れてください<br>月間シフトは〇〇年〇月度のシフト一覧になります<br>例:2019年10月を選択の場合…<br>2019年10月21日～2019年11月20日のシフト一覧が表示<br></p>
		</form>
	</div>
</body>
