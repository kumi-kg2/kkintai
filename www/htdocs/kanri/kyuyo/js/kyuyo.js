//kyuyo_data.php

$(function(){
//	$(document).on("click",".s_no", function() {
	$(".s_no").on('click', function(){
	
		$(".s_no").removeClass("clicked");
		$(this).addClass("clicked");
		
		let s_no = $(this).text();
  	
		let s_no_db = "s_no="+$(this).text();
		$.ajax ({
			type:"get",
			data: s_no_db,
			dataType:"html",
			url:"up_kyuyo.php"
		}).done(function(d) {
			$("#up_kyuyo_dataarea").html(d)
			//給与IDは変更不可
			$("#up_kid").prop('disabled', true);
			//給与内容新規追加にチェックしたら給与内容新規追加の欄を見せる
			$("#new_kyuyodata").hide();
			
			$('input[name="kyuyob"]').change(function() {
				if ($(this).prop('checked')) {
					$("#up_kyuyodata").hide();
					$("#new_kyuyodata").show();
				} else {
					$("#up_kyuyodata").show();
					$("#new_kyuyodata").hide();
				}
			});
			//給与内容変更エラーチェック
			$("#ktourokub").click( function() {
			
				$("#up_kid").prop('disabled', false);
				if ($("#up_kubun").val() == "" ) {
					alert("区分が入力されていません。入力してください");
					return false;	
				}
				let up_ktanka = $("#up_ktanka").val();
				if (up_ktanka == "" ) {
					alert("基本給が入力されていません。入力してください");
					return false;	
				} else if (!(up_ktanka.match(/^[0-9]+$/))) {
					alert("基本給が正しく入力されていません。半角数字で入力してください");
					return false;
				}
				let up_ztanka = $("#up_ztanka").val();
				if (up_ztanka == "" ) {
					alert("残業代が入力されていません。入力してください");
					return false;	
				} else if (!(up_ztanka.match(/^[0-9]+$/))) {
					alert("残業代が正しく入力されていません。半角数字で入力してください");
					return false;
				}
				//給与内容を変更する際に確認を表示する
				if (!confirm('給与内容を変更しますか？')){
        			return false;
    			}else{
    				//
    			}
			});
			
			//新規給与内容追加エラーチェック
			$("#ntourokub").click( function() {
			
				if ($("#new_kubun").val() == "" ) {
					alert("新規　給与区分が入力されていません。入力してください");
					return false;	
				}
				let new_ktanka = $("#new_ktanka").val();
				if (new_ktanka == "" ) {
					alert("新規　基本給が入力されていません。入力してください");
					return false;	
				} else if (!(new_ktanka.match(/^[0-9]+$/))) {
					alert("新規　基本給が正しく入力されていません。半角数字で入力してください");
					return false;
				}
				let new_ztanka = $("#new_ztanka").val();
				if (up_ztanka == "" ) {
					alert("新規　残業代が入力されていません。入力してください");
					return false;	
				} else if (!(new_ztanka.match(/^[0-9]+$/))) {
					alert("新規　残業代が正しく入力されていません。半角数字で入力してください");
					return false;
				}
				if ($("#new_startday").val() == "" ) {
					alert("新規給与開始日が入力されていません。入力してください");
					return false;
				}
			});
		});
	});
});

