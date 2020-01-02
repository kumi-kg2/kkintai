
//kintai_forget.php
//kintai_forget2.php

$(function(){

	//kintai_forget.phpページjs
	
	$("#now_time").prop('disabled', true);
	
	$("#tourokub").click( function() {
		
		if ($("#in_time").val() == "") {
			alert('出勤時間が入力されていません。入力してください');
			return (false);
		}
		if ($("#out_time").val() == "") {
			alert('退勤時間が入力されていません。入力してください');
			return (false);
		}
		$("#now_time").prop('disabled', false);
	});
	
	
	
	//kintai_forget2.phpページjs
	
	//打刻忘れ(出勤・退勤両方の場合)を登録・入力エラーチェック
	$("#n_tourokub").click(function(){
		if ($("#work_day").val() == "") {
			alert('出勤日が入力されていません。入力してください');
			return (false);
		}		
		if ($("#in_time").val() == "") {
			alert('出勤時間が入力されていません。入力してください');
			return (false);
		}
		if ($("#out_time").val() == "") {
			alert('退勤時間が入力されていません。入力してください');
			return (false);
		}
	});

//	$("#f_kitaidata").hide();
	
	//打刻忘れ一覧を表示にチェックした場合
/*	$('input[name="forgetlist_c"]').change(function() {
		if ($(this).prop('checked')) {
			$("#kitaidata").hide();
			$("#new_f_kitaidata").hide();
			$("#f_kitaidata").show();
			$("#f_in_time").prop('disabled', true);
		} else {
			$("#kitaidata").show();
			$("#new_f_kitaidata").show();
			$("#f_kitaidata").hide();
		}
	});*/
	
	$("#f_in_time").prop('disabled', true);
	$("#f_tourokub").click(function(){
		if ($("#f_in_time").val() == "") {
			alert('出勤日が選択されていません。選択してください');
			return (false);
		}
		if ($("#f_out_time").val() == "") {
			alert('退勤時間が入力されていません。入力してください');
			return (false);
		}
		
	});
	
	
	//打刻忘れ一覧を表示・入力エラーチェック
	$("[id^='k_id']").click(function(){
	
		$("[id^='k_id']").removeClass("clicked");
		$(this).addClass("clicked");
		
		let work_day =  $(this).text();
		let k_id = $(this).attr("id");
		let id = k_id.substr(4)
		
		let in_time = $(this).next().text();
		$("#f_in_time").val(in_time);
		$("#f_in_time").prop('disabled', true);
		
		$("#f_tourokub").click(function(){

			let out_time = $("#f_out_time").val();

			if (out_time == "") {
				return (false);
			}
			
			let url = "";
			
			if ($("#out_time_day:checked").val() == "on") {
				//日付を跨いでの退勤の場合
				url = "kintai_forget_comp.php?f_id=6&k_id=" + id + "&out_time=" + out_time + "&wd="+ work_day +"&td=1";
			} else {
				url = "kintai_forget_comp.php?f_id=6&k_id=" + id + "&out_time=" + out_time + "&wd="+ work_day;
			}
			window.location.href = url;
			return false;
		});
	});
});	

