//k_shift.php
//k_shift_list.php

$(function(){
	//日付が00になるものは非表示(月末が31日までない場合00になる)
    $("#shift_data [id $= 'd00']").hide();

	$(window).load(function() {
		//土日の場合背景色変更
		$('th:contains("土")').css("background-color", "#D9E5FF");
		$('th:contains("日")').css("background-color", "#FFDBC9");
	});
	
   $("[id^='sid']").click(function(){
    
		let sid = $(this).attr("id"); 
		let id = sid.slice(3 , -4);
		let day = sid.slice(-2);
		
		$.ajax ({
			type:"get",
			data:{	id : id,
					day : day},
			dataType:"html",
			url:"shift_change.php"
		}).done(function(d) {
			$("#change_shiftarea").html(d)
			
			//シフトコード変更の際に確認を表示する
			$("#changeb").click(function(){
 				if(!confirm('シフトコードを変更しますか？')){
        			return false;
    			}else{
    				//
    			}
 			});
		});
		
		//クリックしたtdの色を変更→リセット
		$("[id^='sid']").removeClass("clicked");
		$(this).addClass("clicked");

	});
});
