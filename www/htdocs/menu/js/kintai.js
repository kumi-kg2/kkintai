
$(function(){

	//出勤・退勤時間表示
	$('#kintaibut').click(function() {
		let timenow = new Date();
		let time = timenow.toLocaleString();
		
		let url = "kintai_comp.php?k_time=" + time;

		if (confirm( time + "登録しますか？")) {
    		window.location.href = url;
  		} else {
  			return false;
  		}
	});
	
});

