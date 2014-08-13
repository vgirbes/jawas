function save_mail(){
	var url = $('#url').val();
	var serializedData = $('#f_alertas').serialize();
	var ahora = new Date();
	$.ajax({
		 type: 'POST',
		 url: url, 
		 data: serializedData,
		 success: function(resp) { 
		 	respuesta = eval('(' + resp + ')');
		 	if (respuesta.errores == ''){
		 		if ($('#hay').val() == 'no'){
		 			$('#der-mails').html('');	
		 		} 
		 		$('#status-save').html('Item saved');
		 		$('#status-save').css({ 'background-color':'#C1F94F' });
		 		$('#status-save').fadeIn().delay(2000).fadeOut();
		 		$('#der-mails').append('<div class="mail-contact" onclick="delete_mail($(this));" id="email_'+ahora.getTime()+'" title="'+respuesta.email+'">'+respuesta.email+'<br/></div>');
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
		$.ajax({
			 type: 'POST',
			 url: url, 
			 data: 'email='+email,
			 success: function(resp) { 
			 	respuesta = eval('(' + resp + ')');
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
