//zangyou.php

$(function(){

	$("#datepicker").datepicker();
	$("#datepicker").datepicker("option", "dateFormat", 'yy-mm-dd');
	
	$("#datepicker").change(function() {
	
		let s_no_name = $("#s_no_name").text();
		let s_no = s_no_name.substr(5, 5);
	
		let day = $("#datepicker").val();
		
		$.ajax ({
			type:"get",
			data:{	s_no : s_no,
					day : day},
			dataType:"html",
			url:"zangyou_list.php"
		}).done(function(d) {
			$("#kintai_list").html(d)
			
			$("form").submit(function() {
				if (!$('input[name=k_time]').is(':checked')) {
					alert("勤務日時が未選択です。該当する勤務日時を選択してください");
					return (false);
				}
				if ($("#z_time").val() == "" ) {
					alert("残業時間が未入力です。入力してください");
					return (false);
				}
				if ($("#biko").val() == "" ) {
					alert("内容が未入力です。入力してください");
					return (false);
				}
			});
		});
	});
});

