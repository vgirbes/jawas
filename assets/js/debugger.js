
function cerrar_status(){
	$('#status').hide();
}

function windows(type){
	if (type == 'max'){
	 	$('#status').css({
 			'width':'99%',
 			'height':'95%',
 			'top':'30px',
 			'left':0
	 	});

	 	$('#status-text').css({
 			'height':'auto',
 			'max-height':$('#status').height()+'px'
	 	});
	}else{
	 	$('#status').css({
 			'width':'60%',
 			'height':'155px',
 			'top':'85px',
 			'left':'20px'
	 	});

	 	$('#status-text').css({
 			'height':'110px',
 			'max-height':'110px'
	 	});
	}
}

	 
