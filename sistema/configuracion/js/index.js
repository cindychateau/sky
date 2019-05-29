$(document).ready(function(){

	getRecord();
	
	$(document).on('click','.guardar',function(e) {
		e.preventDefault();
		$('#frm-contacto').submit();
	});

	$(document).on('submit','#frm-contacto',function(e) {
		e.preventDefault();
		var formdata = new FormData($('#frm-contacto')[0]);
		$.ajax({
			type: 'POST',
			url: 'include/Libs.php?accion=saveRecord',
			data: formdata,
			dataType:'json',
			cache: false,
			contentType: false,
			processData: false,
			beforeSend: function(){
				$('input, file, textarea, button, select').each(function(){
					$(this).attr('disabled','disabled');
				});
			},
			error: function(){
				$('input, file, textarea, button, select').each(function(){
					$(this).removeAttr('disabled');
				});
				bootbox.dialog({
					message: "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.",
					buttons: {
						cerrar: {
							label: "Cerrar",
							callback: function() {
								bootbox.hideAll();
							}
						}
					}
				});
			},
			success: function(result){
				$('input, file, textarea, button, select').each(function(){
					$(this).removeAttr('disabled');
				});
				bootbox.dialog({
					message: result.msg,
					buttons: {
						cerrar: {
							label: "Cerrar",
							callback: function() {
								if(result.error) {
									bootbox.hideAll();
									$('#'+result.focus).focus();
								} else {
									getRecord();
								}
							}
						}
					}
				});
			}
		});
	});

});

function getRecord() {
	$.ajax({
		type: 'POST',
		url: 'include/Libs.php?accion=showRecord',
		dataType:'json',
		error: function(){
			bootbox.dialog({
				message: "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.",
				buttons: {
					cerrar: {
						label: "Cerrar",
						callback: function() {
							bootbox.hideAll();
							window.location = "index.php";
						}
					}
				}
			});
		},
		success: function(result){
			if(!result.error){
				$('#summernote').html(result.contacto);
				$('#index').html(result.index);
				$('#hex').val(result.hex);
				$('#summernote').summernote({
					fontNames: ['Arial', 'Arial Black', 'Comic Sans MS', 'Courier New'],
					tabsize: 2,
					height: 300
				});

				$('#index').summernote({
					fontNames: ['Arial', 'Arial Black', 'Comic Sans MS', 'Courier New'],
					tabsize: 2,
					height: 300
				});
			}
		}
	});	
}