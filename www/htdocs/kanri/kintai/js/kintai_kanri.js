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
			url:"kintai_data.php"
		}).done(function(d) {
			$("#kintai_dataarea").html(d)
			
			$('[name=kintaidata]').change(function() {
				let k_id = $('[name=kintaidata]').val();
				let k = k_id.slice(2);
				let k_id_db = "k_id="+ k;
				$.ajax ({
					type:"get",
					data: k_id_db,
					dataType:"html",
					url:"up_kintai.php"
				}).done(function(d) {
					$("#up_kintai_dataarea").html(d)
					
					$("#tourokub").click( function() {
					
						if ($("[name=kintaidata]").val() == "" ) {
							alert("出勤日時が選択されていません。選択してください");
							return false;	
						}
						
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
						let url = "delete_kintai_data.php?k_id=" + k;
 							if(!confirm('本当に削除しますか？')){
        						return false;
    						}else{
    							window.location.href = url;
    						}
			 		});
				});
			});
		});
	});
});
