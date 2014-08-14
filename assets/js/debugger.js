
function cerrar_status(){
	$('#status').hide();
}

function show_notif(){
	$('#status-notif').show();
}

function windows(type, ventana){
	if (type == 'max'){
	 	$('#status'+ventana).css({
 			'width':'99%',
 			'height':'95%',
 			'top':'30px',
 			'left':0
	 	});

	 	$('#status-text'+ventana).css({
 			'height':'auto',
 			'max-height':$('#status').height()+'px'
	 	});
	}else{
	 	$('#status'+ventana).css({
 			'width':'60%',
 			'height':'155px',
 			'top':'85px',
 			'left':'20px'
	 	});

	 	$('#status-text'+ventana).css({
 			'height':'110px',
 			'max-height':'110px'
	 	});
	}
}

function close_notify(url){
	var r = confirm('Are you sure?');

	if (r){
		$('#status-notif').hide();
		$.ajax({
			 type: 'POST',
			 url: url, 
			 success: function(resp) { 
			 	respuesta = eval('(' + resp + ')');
			 	
			 	$('#notif-num').html('<a href="#">0</a>').fadeIn();
			 }
		});
	}
}

function show_alert(){
	$('#lista-m').toggle();
	$('#alert-m').toggle();
}

	 
