//add_kintai_data.php

$(function(){

	$("#tourokub").click( function() {
	
		if ($("#work_day").val() == "" ) {
			alert("出勤日が入力されていません。入力してください");
			return false;	
		}					
		if ($("#in_time").val() == "" ) {
			alert("出勤時間が入力されていません。入力してください");
			return false;	
		}
		if ($("#out_time").val() == "" ) {
			alert("退勤時間が入力されていません。入力してください");
			return false;
		}
		//勤怠時間を登録する際に確認を表示する
		if (!confirm('勤怠時間を登録しますか？')){
       		return false;
    	}else{
    		//
    	}
	});

});