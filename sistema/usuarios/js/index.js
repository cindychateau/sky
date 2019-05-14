$(document).ready(function(){

	getRecords();
	
	$(document).on('click','.borrar',function(e){
		e.preventDefault();
		var id = $(this).attr("data-id");
		var params = {};
		params.id = id;
		params.accion = 'deleteRecord';
		bootbox.dialog({
			message: "¿Desea eliminar el Cliente seleccionado?",
			buttons: {
				aceptar: {
					label: "Aceptar",
					className: "btn-primary",
					callback: function() {
						$.ajax({
							type:'post',
							data:params,
							url:'include/Libs.php',
							dataType:'json',
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
								bootbox.dialog({
									message: result.msg,
									buttons: {
										cerrar: {
											label: "Cerrar",
											callback: function() {
												bootbox.hideAll();
												$("#clientes").DataTable().destroy();
												getRecords();
											}
										}
									}
								});
							}
						});
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