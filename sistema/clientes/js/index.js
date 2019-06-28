$(document).ready(function(){

	getRecords();
	
	$(document).on('click','.borrar',function(e){
		e.preventDefault();
		var id = $(this).attr("data-id");
		var params = {};
		params.id = id;
		params.accion = 'deleteRecord';
		bootbox.dialog({
			message: '<div id="eliminar-car">¿Desea eliminar el Cliente seleccionado?</div>',
			buttons: {
				aceptar: {
					label: "Aceptar",
					className: "btn-primary",
					callback: function() {
						$.ajax({
							url: 'include/Libs.php',
							type: 'POST',
							data: params,
							dataType: 'JSON',
							beforeSend: function(){
								$('input, file, textarea, button, select').each(function(){
									$(this).attr('disabled','disabled');
								});
							},
							error: function (){
								$('input, file, textarea, button, select').each(function(){
									$(this).removeAttr('disabled');
								});
								bootbox.alert("Experimentamos fallas técnicas. Comuníquese con su proveedor.");
							}, success: function (result) {
								$('input, file, textarea, button, select').each(function(){
									$(this).removeAttr('disabled');
								});

								if(!result.error) {
									var alert = "<div class='alert alert-success' role='alert'>"+result.msg+"</div>";
									$("#clientes").DataTable().destroy();
									getRecords();
									setTimeout(function () { bootbox.hideAll();}, 1750);
								} else {
									var alert = "<div class='alert alert-danger' role='alert'>"+result.msg+"</div>";
									$('#eliminar-car').prepend(alert);
								}
							}
						});

						return false;
					}
				},
				cancelar: {
					label: "Cancelar",
					className: "btn-danger",
					callback: function() {
						$('.modal-dialog').modal('hide');
					}
				}
			}
		});
	});

});

function getRecords() {
	$.ajax({
		type: 'POST',
		url: 'include/Libs.php?accion=printTable',
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
				$('#clientes').DataTable( {
					"ajax": {
					    "url": "include/Libs.php?accion=printTable",
					    "type": "POST"
					},
					"language": {
					    "emptyTable":     "No se encontraron registros",
					    "info":           "Mostrando _START_ a _END_ de _TOTAL_",
					    "infoEmpty":      "Mostrando 0 de 0",
					    "lengthMenu":     "Mostrando _MENU_ registros",
					    "loadingRecords": "Cargando...",
					    "processing":     "Procesando...",
					    "search":         "Buscar:",
					    "zeroRecords":    "No se encontraron registros",
					    "paginate": {
					        "first":      "Primera",
					        "last":       "Última",
					        "next":       "Siguiente",
					        "previous":   "Anterior"
					    }
				    },
				    "initComplete": function(settings, json) {
					    $('.tip').tooltip();
					}
				});
			}
		}
	});	

}