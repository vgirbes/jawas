function save_mail(){
	var url = $('#url').val();
	var serializedData = $('#f_alertas').serialize();
	var ahora = new Date();
	$.ajax({
		 type: 'POST',
		 url: url, 
		 data: serializedData,
		 beforeSend: function () {
                html = 'Processing...';
                $('#status-save').addClass('blink_me');
                $('#status-save').css({ 'background-color':'#ccc' });
                $('#status-save').html(html);
                $('#status-save').fadeIn();
         },
		 success: function(resp) { 
		 	respuesta = eval('(' + resp + ')');
		 	$('#status-save').removeClass('blink_me');
		 	if (respuesta.errores == ''){
		 		if ($('#hay').val() == 'no'){
		 			$('#der-mails').html('');	
		 		} 
		 		id_time = ahora.getTime();
		 		$('#status-save').html('Item saved');
		 		$('#status-save').css({ 'background-color':'#C1F94F' });
		 		$('#status-save').fadeIn().delay(2000).fadeOut();
		 		$('#der-mails').prepend('<div class="mail-contact" onclick="delete_mail($(this));" id="email_'+id_time+'" title="'+respuesta.email+'">'+respuesta.email+'<br/></div>');
		 		$('#email_'+id_time).effect('slide', 1000);
		 	}else{
		 		$('#status-save').html(respuesta.errores);
		 		$('#status-save').css({ 'background-color':'red' });
		 		$('#status-save').fadeIn().delay(2000).fadeOut();
		 	}
		 	$('#email').val('');
		 }
	});
}

function delete_mail(f){
		email = f.attr('title');
		url = $('#url_delete').val();
		id = f.attr('id');
		$('#der-mails').css({ 'border':'5px dashed red' });
		$.ajax({
			 type: 'POST',
			 url: url, 
			 data: 'email='+email,
			 beforeSend: function () {
                html = 'Processing...';
                $('#status-save').addClass('blink_me');
                $('#status-save').css({ 'background-color':'#ccc' });
                $('#status-save').html(html);
                $('#status-save').fadeIn();
         	 },
			 success: function(resp) { 
			 	respuesta = eval('(' + resp + ')');
			 	$('#status-save').removeClass('blink_me');
			 	$('#der-mails').css({ 'border':'5px dashed #ccc' });
			 	if (respuesta.errores == ''){
			 		$('#'+id).remove();
			 		$('#status-save').html('Item deleted');
			 		$('#status-save').css({ 'background-color':'#C1F94F' });
			 		$('#status-save').fadeIn().delay(2000).fadeOut();
			 	}else{
			 		$('#status-save').html(respuesta.errores);
			 		$('#status-save').css({ 'background-color':'red' });
			 		$('#status-save').fadeIn().delay(2000).fadeOut();
			 	}
			 }
		});
}

function save_mail_google(f){
	var url = $('#url').val();
	var type = $('#type').val();
	var email = f.attr('title');
	var ahora = new Date();
	$.ajax({
		 type: 'POST',
		 url: url, 
		 data: 'email='+email+'&type='+type,
		 beforeSend: function () {
                html = 'Processing...';
                $('#status-save').addClass('blink_me');
                $('#status-save').css({ 'background-color':'#ccc' });
                $('#status-save').html(html);
                $('#status-save').fadeIn();
         },
		 success: function(resp) { 
		 	respuesta = eval('(' + resp + ')');
		 	$('#status-save').removeClass('blink_me');
		 	if (respuesta.errores == ''){
		 		if ($('#hay').val() == 'no'){
		 			$('#der-mails').html('');	
		 		} 
		 		id_time = ahora.getTime();
		 		$('#status-save').html('Item saved');
		 		$('#status-save').css({ 'background-color':'#C1F94F' });
		 		$('#status-save').fadeIn().delay(2000).fadeOut();
		 		$('#der-mails').prepend('<div class="mail-contact" onclick="delete_mail($(this));" id="email_'+id_time+'" title="'+respuesta.email+'">'+respuesta.email+'<br/></div>');
		 		$('#email_'+id_time).effect('slide', 1000);
	
		 	}else{
		 		$('#status-save').html(respuesta.errores);
		 		$('#status-save').css({ 'background-color':'red' });
		 		$('#status-save').fadeIn().delay(2000).fadeOut();
		 	}
		 	$('#email').val('');
		 }
	});
}


