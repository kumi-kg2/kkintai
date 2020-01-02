//ninsyou.php
$(function(){

//	$(document).on("click",".idno", function() {

	$(".idno").on('click', function(){

		let text = "ID『"+$(this).text()+"』の登録を承認しますか？";
		let id = "id="+$(this).text();
		console.log(id);
	    if(!confirm(text)){
        	return false;
    	} else {
    		$.ajax ({
				type:"get",
				data: id,
				dataType:"html",
				url:"ninsyou_comp.php"
				}).done(function(d) {
				$("#authenticated_dataarea").html(d)
			});
	    }
	});
});
