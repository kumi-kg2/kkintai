<?php
	//認証ページ(member/ninsyou.php)から認証
	//登録ページ(touroku_comp.php)のメールから認証
	//従業員認証完了ページ
	
	include_once ('db/db.inc');
	include_once ('common.inc');
	
	mb_language("Japanese");
	mb_internal_encoding("utf-8");
	$to = "kumi@ibg.jp";
	$kintai_name = "猫カフェ勤怠";
	
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

	$id = $_GET['id'];
	
	$html_ms = "";
	
	$mail_sub = "";
	$mail_ms = "";

	//DB内に認証IDがあるかを確認する
	$sql = "SELECT COUNT(*) AS cnt FROM ninsyou_list WHERE n_id = ?";
	$rs = $db->prepare($sql);
	$data = array($id);
	$rs->execute($data);
	$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
		
	foreach ($rd as $row){
		$cnt = $row["cnt"];
		
		if ($cnt == 1 ){
		//認証IDok→認証許可FLGを確認
			$sql = "SELECT * FROM ninsyou_list WHERE n_id = ?";
			$rs = $db->prepare($sql);
			$data = array($id);
			$rs->execute($data);
			$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
			foreach ($rd as $row){
				$pm_no = $row["permission"];
				$s_no = $row["syain_no"];
				$s_ip = $row["user_agent"];
				// 社員番号から名前・フリガナ取得する
				$syain = new SYAIN();
				$syain->select_sno_syaindata($s_no, $db);
				$syain_no_db = $syain->syain_no;
				$syain_name_db = $syain->name;
				$syain_furi_db = $syain->furi;
				
				if ($pm_no == "0") {
					//認証許可FLGが0→認証許可FLG1にして登録
					$pm_ok = "1"; //認証許可FLG1
					$sql = "UPDATE ninsyou_list SET permission= ? WHERE n_id = ?";
					$rs = $db->prepare($sql);
					$data = array($pm_ok, $id);
					$rs->execute($data);
					$rd = $rs->fetchAll(PDO::FETCH_ASSOC);
					$html_ms .= "認証許可しました。";
					
					$mail_sub .= "認証登録が完了しました";
					$mail_ms .= "下記従業員の認証登録が完了しました。\n";
					$mail_ms .= "登録者情報をご確認ください。\n\n";
					$mail_ms .= "【登録者情報】\n\n社員番号：".$s_no."\n　名　前：".$syain_name_db;
					$mail_ms .="\nフリガナ：".$syain_furi_db."\n登録端末：".$s_ip;
					
				} else if ($pm_no == "1") {
					//認証許可FLGが1→すでに認証済み
					$html_ms .= "そのデータは許可済です";
					
					$mail_sub .= "認証登録エラー(既に認証済です)";
					$mail_ms .= "既に認証済みの従業員です、下記の登録者情報をご確認ください。\n\n";
					$mail_ms .= "【登録者情報】\n\n社員番号：".$s_no."\n　名　前：".$syain_name_db;
					$mail_ms .="\nフリガナ：".$syain_furi_db."\n登録端末：".$s_ip;
					$mail_ms .= "\n\n下記アドレスにて認証待ちのデータを確認することが出来ます。\n";
					$mail_ms .= "http://s.ibg.jp/kanri/member/ninsyou.php";
				}
			}
		} else {
		//認証IDがDB内に存在しない
			$html_ms .= "そのデータは存在しません";
			
			$mail_sub .= "認証登録エラー";
			
			$mail_ms .= "既に登録を削除済みのデータか、登録していないデータです。\n\n";
			$mail_ms .= "下記アドレスにて認証待ちのデータを確認することが出来ます。\n";
			$mail_ms .= "http://s.ibg.jp/kanri/member/ninsyou.php";
		}
	}

	//管理者へメール
	$date = date("Y-m-d H:i:s");
	$m_accesIP = $_SERVER['REMOTE_ADDR'];
	$subject = "【".$kintai_name."】".$mail_sub;

	$body = $mail_ms;

	$header = "From:".$to;
	mb_send_mail($to, $subject, $body, $header);

?>

<h3>認証登録ページ</h3>
	<p><?php echo $html_ms; ?></p>
