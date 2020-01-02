//forget_kintai.php(勤怠打刻忘れ修正ページ)

$(function(){
	$(document).ready( function(){
	
		$("#f_kintaidata").hide();
		if ($("#un_f_kintaidata").text() == "" ) {
		//打刻修正がある場合
		    $("#un_f_kintaidata").hide();
		    $("#f_kintaidata").show()
		} else {
		//打刻修正がない場合
			$("#f_kintaidata").hide();
			$("#un_f_kintaidata").show();
		}
	});
	
	$("[id^='k_id']").click(function(){
	
		$("[id^='k_id']").removeClass("clicked");
		$(this).addClass("clicked");
		
		let k_id = $(this).attr("id");
		let id = k_id.substr(4)
		let id_db = "id="+id;
		if(!confirm('打刻時間を修正しますか？')){
		        return false;
		} else {
			
			$.ajax ({
				type:"get",
				data: id_db,
				dataType:"html",
				url:"forget_kintai_comp.php"
			}).done(function(d) {
				$("#f_kintai_dataarea").html(d)
				
				setTimeout(function(){
					window.location.href = '/kanri/kintai/forget_kintai.php';
				}, 3000);
				
			});
		}
	});
});