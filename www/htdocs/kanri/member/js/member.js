//従業員新規登録.js
//エラーチェック
$(function(){
	$('form').submit(function() {
	
  		let syainno = $("#syainno").val();
  		if ( syainno == "" ) {
			alert("社員番号が入力されていません。入力してください");
			return false;
		}
		if (!(syainno.match(/^[A-Za-z0-9]*$/))) {
			alert("社員番号が正しく入力されていません。半角英数字で入力してください");
			return false;
		}
		if (syainno.length != 5) {
			alert("社員番号が正しく入力されていません。5文字で入力してください");
			return false;
		}
		
		if ($("#busyo").val() == "" ) {
			alert("部署が入力されていません。入力してください");
			return false;	
		}
		
		if ($("#name").val() == "" ) {
			alert("名前が入力されていません。入力してください");
			return false;	
		}
		
		let furi = $("#furi").val();
		if ( furi == "" ) {
			alert("フリガナが入力されていません。入力してください");
			return false;	
		}
		
		if(!(furi.match(/^[ァ-ヶー]*$/))) { 
			alert("フリガナが正しく入力されていません。全角カタカナで入力して下さい、またはスペースを外してください");
			return false;
  		}
  		
  		if ($("#birth").val() == "" ) {
			alert("生年月日が入力されていません。入力してください");
			return false;	
		}
		
		let phone = $("#phone").val();
		if ( phone == "" ) {
			alert("携帯番号が入力されていません。入力してください");
			return false;	
		}
		if (phone.length != 11) {
			alert("携帯番号が正しく入力されていません。半角数字・ハイフンなしで入力してください");
			return false;
		}
		
		if ($("#kkubun").val() == "" ) {
			alert("給与区分が入力されていません。入力してください");
			return false;	
		}
		
		let k_ktanka = $("#kktanka").val();
		if ( k_ktanka == "" ) {
			alert("基本単価が入力されていません。入力してください");
			return false;	
		}
		if( k_ktanka.match( /[^0-9.,-]+/ )) {
			alert("基本単価が正しく入力されていません。半角数字で入力してください");
			return false;
		}
		
		let k_ztanka = $("#kztanka").val();
		if ( k_ztanka == "" ) {
			alert("残業単価が入力されていません。入力してください");
			return false;	
		}
		if( k_ztanka.match( /[^0-9.,-]+/ )) {
			alert("残業単価が正しく入力されていません。半角数字で入力してください");
			return false;
		}
		
		if ($("#kstartday").val() == "" ) {
			alert("開始日が入力されていません。入力してください");
			return false;	
		}

	});
});
