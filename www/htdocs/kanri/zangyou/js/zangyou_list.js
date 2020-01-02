//up.zangyou.php

$(function(){

	//$(document).on("click",".s_no", function() {
	$(".s_no").on('click', function(){
	
		$(".s_no").removeClass("clicked");
		$(this).addClass("clicked");
	
		let s_no = $(this).text();
		
		let s_no_db = "s_no="+$(this).text();

		$.ajax ({
			type:"get",
			data: s_no_db,
			dataType:"html",
			url:"zangyou_data.php"
		}).done(function(d) {
		
			$("#zangyou_dataarea").html(d)
				
			$('[name=kintaidata]').change(function() {
				let k_id = $('[name=kintaidata]').val();
				let k = k_id.slice(2);
				let k_id_db = "k_id="+ k;
				$.ajax ({
					type:"get",
					data: k_id_db,
					dataType:"html",
					url:"up_zangyou.php"
				}).done(function(d) {
				
					$("#up_zangyou_dataarea").html(d)
					
					$("#tourokub").click( function() {
					
						if ($("[name=kintaidata]").val() == "" ) {
							alert("出勤日時が選択されていません。選択してください");
							return false;	
						}
						
						//残業時間修正する際に確認を表示する
	 					if(!confirm('残業時間を修正しますか？')){
	        				return false;
	    				}else{
	    					//
	    				}
	
					});
					
				});
			});
		});
	});
});
