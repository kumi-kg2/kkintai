$(function(){
	
	
	
	//勤務時間
	$("#work_time").prop('disabled', true);
	
	//出退勤1
	$(".time1").on("keydown keyup keypress change", function() {

		let in_time1 = $("#in_time1").val().split(':');
		let intime1_m = parseInt(in_time1[0]*60) + parseInt(in_time1[1]);

		let out_time1 = $("#out_time1").val().split(':');
		let out_time1_m = parseInt(out_time1[0]*60) + parseInt(out_time1[1]);
		
		let k_time1 = (out_time1_m - intime1_m)/60;
		
		if ( 0 >= k_time1 ) {
			$("#work_time").val("0");
		} else if (6 >= k_time1 > 0) {
			$("#work_time").val(k_time1);
		} else if (k_time1 > 6) {
			$("#work_time").val(k_time1 -1);
		}

	});
	//出退勤2
	$(".time2").on("keydown keyup keypress change", function() {

		let in_time1 = $("#in_time1").val().split(':');
		let intime1_m = parseInt(in_time1[0]*60) + parseInt(in_time1[1]);

		let out_time1 = $("#out_time1").val().split(':');
		let out_time1_m = parseInt(out_time1[0]*60) + parseInt(out_time1[1]);
		
		let k_time1 = (out_time1_m - intime1_m)/60;	
	
	
		let in_time2 = $("#in_time2").val().split(':');
		let intime2_m = parseInt(in_time2[0]*60) + parseInt(in_time2[1]);

		let out_time2 = $("#out_time2").val().split(':');
		let out_time2_m = parseInt(out_time2[0]*60) + parseInt(out_time2[1]);
		let k_time2 = (out_time2_m - intime2_m)/60;
		
		let g_time = k_time1 + k_time2;

		if (( 0 >= g_time ) || ( 0 >= k_time1 ) || ( 0 >= k_time2 ) || (out_time1 > in_time2)) {
			$("#work_time").val("0");
		} else if (6 >= g_time > 0) {
			$("#work_time").val(g_time);
		} else if (g_time > 6) {
			$("#work_time").val(g_time -1);
		}

	});	
	
	//シフトコードをクリック→修正
	$("[id^='s_cd']").click(function(){
	
    	$("[id^='s_cd']").removeClass("clicked");
		$(this).addClass("clicked");
		
		let s_cd = $(this).attr("id"); 
		let coad = s_cd.substr(4);
		let coad_db = "s_cd="+coad;
		$("#new_shiftmaster").remove();
		
		$.ajax ({
			type:"get",
			data: coad_db,
			dataType:"html",
			url:"change_shift_master.php"
		}).done(function(d) {
			$("#change_shiftmaster").html(d)
			
			$("#work_time").prop('disabled', true);

			//既存マスタの修正 出退勤1
			$(".time1").on("keydown keyup keypress change", function() {
			
				let in_time1 = $("#in_time1").val().split(':');
				let intime1_m = parseInt(in_time1[0]*60) + parseInt(in_time1[1]);

				let out_time1 = $("#out_time1").val().split(':');
				let out_time1_m = parseInt(out_time1[0]*60) + parseInt(out_time1[1]);
				
				let k_time1 = (out_time1_m - intime1_m)/60;
				
				if ( 0 >= k_time1 ) {
					$("#work_time").val("0");
				} else if (6 >= k_time1 > 0) {
					$("#work_time").val(k_time1);
				} else if (k_time1 > 6) {
					$("#work_time").val(k_time1 -1);
				}

			});
			
			//既存マスタの修正 出退勤2
			$(".time2").on("keydown keyup keypress change", function() {
			
				let in_time1 = $("#in_time1").val().split(':');
				let intime1_m = parseInt(in_time1[0]*60) + parseInt(in_time1[1]);

				let out_time1 = $("#out_time1").val().split(':');
				let out_time1_m = parseInt(out_time1[0]*60) + parseInt(out_time1[1]);
				
				let k_time1 = (out_time1_m - intime1_m)/60;	
			
			
				let in_time2 = $("#in_time2").val().split(':');
				let intime2_m = parseInt(in_time2[0]*60) + parseInt(in_time2[1]);

				let out_time2 = $("#out_time2").val().split(':');
				let out_time2_m = parseInt(out_time2[0]*60) + parseInt(out_time2[1]);
				let k_time2 = (out_time2_m - intime2_m)/60;
				
				let g_time = k_time1 + k_time2;

				if (( 0 >= g_time ) || ( 0 >= k_time1 ) || ( 0 >= k_time2 ) || (out_time1 > in_time2)) {
					$("#work_time").val("0");
				} else if (6 >= g_time > 0) {
					$("#work_time").val(g_time);
				} else if (g_time > 6) {
					$("#work_time").val(g_time -1);
				}

			});	
			
			$("#mtourokub").click(function(){
				//既存マスタの修正、入力エラーチェック
				
				if (!($("#work_time").val() == 0)) {
					$("#work_time").prop('disabled', false);
				}
				if ($("#in_time1").val() == "" ) {
					alert("出勤時間1が未入力です。正しく入力してください");
					return false;
				}
				if ($("#out_time1").val() == "" ) {
					alert("退勤時間1が未入力です。正しく入力してください");
					return false;
				}
				
				if ($("#work_time").val() == 0) {
					alert("勤務時間が0のままです。出退勤時間を正しく入力してください");
					return false;					
				}
				
				//シフトマスタ修正の際に確認表示する
				if(!confirm('シフトマスタを修正しますか？')){
        			return false;
    			}else{
    				//
    			}
    			
 			});
 			
 			//シフトマスタ削除の際に確認表示する
 			$("#mdelete").click(function(){
 				if(!confirm('本当に削除しますか？')){
        			return false;
    			}else{
    				//
    			}
 			});
		});
	});


	//新規登録マスタの入力エラーチェック
	$('form').submit(function() {
		
		if (!($("#work_time").val() == 0)) {
			$("#work_time").prop('disabled', false);
		}
		
		let coad = $("#coad").val();
		coad_c = coad.length;
		
		if (!(coad.match(/^[0-9a-zA-Z]*$/))) {
			alert("シフトコードは半角英数字で入力してください");
    		return false;
 		}
		if (coad == "" ) {
			alert("シフトコードが入力されていません。入力してください");
			return false;
		}
		if (( coad_c > 3 ) || ( 2 > coad_c )){
			alert("シフトコードは2～3文字で入力してください");
			return false;
		} 
		if ($("#in_time1").val() == "" ) {
			alert("出勤時間1が未入力です。正しく入力してください");
			return false;
		}
		if ($("#out_time1").val() == "" ) {
			alert("退勤時間1が未入力です。正しく入力してください");
			return false;
		}
		
		if ($("#work_time").val() == 0) {
			alert("勤務時間が0のままです。出退勤時間を正しく入力してください");
			return false;
		}
 
	});

});
