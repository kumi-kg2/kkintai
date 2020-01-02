<?php
	include_once ('db/db.inc');
	include_once ('common.inc');

	$a_check = authcheck();

	if ($a_check == 0) {
		//Cookieない・IPアドレスOK→Cookie登録
		//アクセスok
	} else {
		header ("Location: /");
		exit;
	}

	mb_language("Japanese");
	mb_internal_encoding("utf-8");
	
	$to = "kumi@ibg.jp";
	$kintai_name = "猫カフェ勤怠";
	
	$db = new DbConnect();
	$a_check = authcheck();

	if (isset($_POST['syainno'])) {
		$syainno = h($_POST['syainno']);
	}
	//エラーチェック
    if ($syainno == "" ) {
		echo "社員番号未入力のエラーです<br>";
	}
	if (!(preg_match("/^[a-zA-Z0-9]+$/", $syainno))) {
		echo "社員番号入力のエラーです。半角英数字で入力してください";
	}
    
	//cookieの設定↓
	date_default_timezone_set('Asia/Tokyo');
	$user_date = date("YmdHis");
	$user_agent =  $_SERVER['HTTP_USER_AGENT'];
	$ninsyou_num = $user_agent . '.' . $user_date; //ブラウザ＋日付のデータ
	$ninsyou_num_db = base64_encode($ninsyou_num); //ブラウザ＋日付のデータを64
	
	
	// 社員番号入力有・cookieなし・IPアドレスOK→登録
	if ((isset($syainno)) && ($a_check == 0)) {
		//社員番号がDB内と一致するものをカウントする
		$sql = "SELECT COUNT(*) AS cnt FROM syain_list WHERE syain_no = ?";
		$rs = $db->prepare($sql);
		$data = array($syainno);
		$rs->execute($data);
		$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
		
		foreach ($rd as $row){
			$cnt = $row["cnt"];
			if ($cnt > 0 ){
				//社員番号OK→cookie登録する
				setcookie("AUTH", $ninsyou_num_db, time()+ (5 * 365 * 24 * 60 * 60));
				// 社員番号から名前・フリガナ取得する
				$syain = new SYAIN();
				$syain->select_sno_syaindata($syainno, $db);
				$syain_no_db = $syain->syain_no;
				$syain_name_db = $syain->name;
				$syain_furi_db = $syain->furi;
				
				//認証許可FLGは0でDB内に登録
				$sql = "INSERT INTO ninsyou_list (syain_no, user_agent, ninsyou_num, permission) ";
				$sql .= "VALUES(?, ?, ?, ?)";
				$rs = $db->prepare($sql);
				$data = array (
							$syain_no_db,
							$user_agent,
							$ninsyou_num_db,
							0
						);
						
				$rs->execute($data);
				$n_id =  $db->lastInsertId();
				$rd = $rs->fetchAll(PDO::FETCH_ASSOC);


			} else if ($cnt == 0) {
				echo "社員番号を正しく入力してください";
				exit;
			}
		}
	} else {
		header ("Location: /");
		exit;
	}
	
	//管理者へメール
	$date = date("Y-m-d H:i:s");
	$m_accesIP = $_SERVER['REMOTE_ADDR'];
	$subject = "【".$kintai_name."】新規登録がありました";
	

		$body = <<< __BODY__
		
{$kintai_name}にて、新規登録がありました。
下記内容をご確認の上、認証をよろしくお願いします。

【新規登録者情報】

社員番号：{$syain_no_db}
　名　前：{$syain_name_db}
フリガナ：{$syain_furi_db}

・IPアドレス
{$m_accesIP}
・登録端末
{$user_agent}

認証ページURL
http://s.ibg.jp/kanri/member/ninsyou_comp.php?id={$n_id}

__BODY__;
	$header = "From:".$to;
	mb_send_mail($to, $subject, $body, $header);

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css" href="menu/menu.css">
<title>登録完了画面</title>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.js"></script>
<script>
$(function(){
	$(window).load(function() {
		setTimeout(function(){
		window.location.href = '/';
		}, 3000);
	});
});
</script>
</head>
<?php include 'header.inc'; ?>
<body>
<div>
	登録完了しました<br>
	自動的にTOPページへ移動します<br>
	移動しない場合は <a href="http://s.ibg.jp/">こちら</a>
</div>
</body>