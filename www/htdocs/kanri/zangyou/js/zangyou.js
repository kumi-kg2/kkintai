//zangyou_kanri.php

$(function(){
	
	$("[id^='k_id']").click(function(){
	
		$("[id^='k_id']").removeClass("clicked");
		$(this).addClass("clicked");
		
		let k_id = $(this).attr("id");
		let id = k_id.substr(4)
		let id_db = "id="+id;
		if(!confirm('残業登録を認証しますか？')){
		        return false;
		} else {
			$.ajax ({
				type:"get",
				data: id_db,
				dataType:"html",
				url:"zangyou_kanri_comp.php"
			}).done(function(d) {
				$("#zangyou_nisyouarea").html(d)
				
				setTimeout(function(){
					window.location.href = '/kanri/zangyou/zangyou_kanri.php';
				}, 3000);
			});
		}
	});
})
