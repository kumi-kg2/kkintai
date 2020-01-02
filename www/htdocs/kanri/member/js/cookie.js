//cookie_list.php

$(function(){
	//該当する登録端末を削除する場合
//	$(document).on("click","#n_id", function() {
	$(".n_id").on('click', function(){

		let n_text = "認証ID『"+$(this).text()+"』の登録端末を削除しますか？";
		let n_id = "n_id="+$(this).text();
	    if(!confirm(n_text)){
        	return false;
    	} else {
    		$.ajax ({
				type:"get",
				data: n_id,
				dataType:"html",
				url:"cookie_delete.php"
				}).done(function(d) {
				$("#delete_cookiedataarea").html(d)
			});
	    }
	});
	
	//退職者の全ての登録端末を削除する場合
	//	$(document).on("click","#s_id", function() {
		$("#s_id").on('click', function(){
		
		let s_text = "社員番号『"+$(this).text()+"』の登録端末を全て削除しますか？";
		let s_id = "s_id="+$(this).text();
		console.log(s_id);
	    if(!confirm(s_text)){
        	return false;
    	} else {
    		$.ajax ({
				type:"get",
				data: s_id,
				dataType:"html",
				url:"all_cookie_delete.php"
				}).done(function(d) {
				$("#delete_allcookiedataarea").html(d)
			});
	    }
	});
	
});
