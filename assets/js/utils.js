$(function() {
	$( "#msg-import" ).draggable();
	$( ".login-box" ).draggable();
	$( "#status" ).draggable().resizable();
	$( "#status-notif" ).draggable().resizable();
	$( ".drag-field" ).draggable();
	$( init );
	function init() {
	  $('.drag-field').draggable({ revert: "invalid" });
	  $('.cuadro').droppable( {
	    	drop: handleDropEvent
	  });

	  $('.active-field').droppable( {
	    drop: handleDropEventReset
	  } );
	}

	function handleDropEventReset( event, ui ) {
		var draggable = ui.draggable;
		$( "input[value=\'"+draggable.attr('id')+"\']" ).val( "" );
		$(this).append(draggable.css({ 'top': 0, 'left': 0, 'width':'auto', 'height': '30px' }));
	}
	 
	function handleDropEvent( event, ui ) {
	  var draggable = ui.draggable;
	  var pos = $(this).attr('title');
	  $( "input[value=\'"+draggable.attr('id')+"\']" ).val( "" );
	  $('#'+pos).val(draggable.attr('id'));
	  $('#prov_'+pos+'_id_'+$('#edit_prov_id').val()).val(draggable.attr('id'));
	  $(this).append(draggable.css({ 'top': 0, 'left': 0, 'width':'70%', 'height': '66px' }));
	}
});

function delete_provider(id){
	close_details(id);
	var confirmar = confirm($('#confirmar').val());

	if (confirmar == true) {
		$('#borrar_id').val(id);
	    $('#borrar').submit();
	}
}

function save_other_provider(){
	var id = $('#edit_prov_id').val();
	var reg = $('#total_reg').val();
	$('#edit_name').val($('.prov_name_'+id).val());
	$('#edit_country').val($('.prov_country_'+id).val());
	$('#edit_prov_files').val($('.prov_files_'+id).val());
	$('#edit_activo').val($('#prov_activo_'+id).val());

	for (i=1; i<=reg; i++){
		field = $('#prov_position_'+i+'_id_'+id).val();
		$('#edit_position_'+i).val(field);
	}

	$('#editar').submit();
}

function hide_errors(){
	$('.err-prov').hide();
	$('.panel-other-prov').css({ 'margin-top':'0px' });
}

function details(id){
	$('.details').hide();
	$('#edit_prov_id').val(id);
	$('#detalles_'+id).show();
	hide_errors();
}

function close_details(id){
	$('#detalles_'+id).hide();
}

$( document ).ready(function() {
	$("#add_prov").click(function() {
		$('.details').hide();
		$('#close_prov').show();
		$('#capa-sup').show(); 
		hide_errors();
	});

	$('#close_prov').click(function(){
		$('#capa-sup').hide(); 
		$('#close_prov').hide();
	});

	$('#cancel-prov').click(function(){
		$('#capa-sup').hide(); 
		$('#close_prov').hide();
	});

	$('.row-other-prov').click(function(){
		var id = $(this).attr('rel');
		details(id);
	});
});