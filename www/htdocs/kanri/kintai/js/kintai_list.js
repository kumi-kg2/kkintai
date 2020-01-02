//kintai_lis.php

$(function(){

	$("[id^='k_id']").on('click', function() {
	
		$("[id^='k_id']").removeClass("clicked");
		$(this).addClass("clicked");
	
		let k_id = $(this).attr("id"); 
		let id = k_id.slice(4);
		let  k_id_db = "k_id=" + id;
		
	
		$.ajax ({
			type:"get",
			data: k_id_db,
			dataType:"html",
			url:"up_kintai.php"
		}).done(function(d) {
			$("#up_kintai_dataarea").html(d)
					
			$("#tourokub").click( function() {
						
				if ($("#up_in_time").val() == "" ) {
					alert("出勤時間が入力されていません。入力してください");
					return false;	
				}
				if ($("#up_out_time").val() == "" ) {
					alert("退勤時間が入力されていません。入力してください");
					return false;
				}
				
				//勤怠時間を修正する際に確認を表示する
				if (!confirm('勤怠時間を修正しますか？')){
        			return false;
    			}else{
    				//
    			}
			});
			
			$("#deleteb").click(function(){
				let url = "delete_kintai_data.php?k_id=" + id;
 				if(!confirm('本当に削除しますか？')){
        			return false;
    			}else{
    				window.location.href = url;
    			}
			 });
			 
		});
		
	});
});
