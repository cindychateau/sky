var accion = 'contexto';
$(document).ready(function(){
	
	//Acción de cambiar el nombre en el botón
	$(document).on('click','.dropdown-item',function(e) {
		e.preventDefault();
		accion = $(this).attr('data-action');
		var busqueda = $(this).html();
		$('#btn-accion').html('Búsqueda '+busqueda);
	});

	$(document).on('change','#order, #n',function(e) {
		e.preventDefault();
		$('#pag').val(1);
		search();
	});

	$(document).on('submit','#frm-busqueda',function(e) {
		e.preventDefault();
		$('#pag').val(1);
		search();
	});

	$(document).on('click','.pagination li a',function(e) {
		e.preventDefault();
		if(!$(this).hasClass('active')) {
			var page = $(this).attr('data-pag');
			$('#pag').val(page);
			search();
			$('html,body').animate({
	            scrollTop: 0
	        }, 700);
		}
		
	});

	$(document).on('dblclick','.lnk-doc',function(e) {
		e.preventDefault();
		var lnk = $(this).attr('data-ruta');
		window.open(lnk, '_blank');
	});

});

function search() {

	$.ajax({
		type:'post',
		url:'include/Libs.php?accion=search',
		dataType:'json',
		data:$('#frm-busqueda').serialize()+'&action='+accion,
		beforeSend:function(){ 
			$('.resultados').html('<i class="fa fa-spinner fa-2x fa-spin"></i>');
		},
		error:function(){
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
		success:function(result){
			
			if(!result.error) {

				$(".resultados").html(result.table);
				$(".pags").html(result.pag);
				
			} else {
				bootbox.dialog({
					message: result.msg,
					buttons: {
						cerrar: {
							label: "Cerrar",
							callback: function() {
								bootbox.hideAll();
							}
						}
					}
				});
			}

		}
	});
}