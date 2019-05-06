$(document).ready(function(){

	showModules();
	showFolders();
	getClients();
	getTiposUsuarios();

	$(document).on('click','.all-mod',function(e) {
		var id = $(this).attr("data-id");
		var check = $(".module-"+id).find( ".view" ).is(':checked');
		check = !check;
		$(".module-"+id).find( ".view" ).prop('checked',check);
		$(".module-"+id).find( ".view").removeAttr("disabled");
		if(!check){
			$(".module-"+id).find( ".view").each(function () {
				var data = $(this).attr("data-id");
				$("#a-"+data).prop('checked',false);
				$("#b-"+data).prop('checked',false);
				$("#c-"+data).prop('checked',false);
			});
		}

	});

	$(document).on('click','.select-all',function(e){
		var id = $(this).attr("data-id");
		var clase = $(this).attr("data-class");
		var check = $(".module-"+id).find( "."+clase ).is(':checked');
		check = !check;
		$(".module-"+id).find( "."+clase ).prop('checked',check);
		if(clase != "view") {
			var check1 = $(".module-"+id).find( ".reg").is(':checked');
			var check2 = $(".module-"+id).find( ".del").is(':checked');
			var check3 = $(".module-"+id).find( ".ch").is(':checked');
			if(check1 || check2 || check3){
				$(".module-"+id).find( ".view").attr("disabled", "");
			} else {
				$(".module-"+id).find( ".view").removeAttr("disabled");
			}
			$(".module-"+id).find( ".view").prop('checked',true);
		} else {
			if(!check){
				$(".module-"+id).find( "."+clase ).each(function () {
					var data = $(this).attr("data-id");
					$("#a-"+data).prop('checked',false);
					$("#b-"+data).prop('checked',false);
					$("#c-"+data).prop('checked',false);
				});
			}
			$(".module-"+id).find( "."+clase ).removeAttr("disabled");
		}
	});

	$(document).on('click','.sel-all',function(e){
		var clase = $(this).attr("data-class");

		var check = $("#carpetas").find( "."+clase+"-cl" ).is(':checked');
		check = !check;

		$("#carpetas").find( "."+clase+"-cl" ).prop('checked',check);
		$("#carpetas").find( "."+clase+"-car" ).prop('checked',check);

		if(clase != "view") {
			if(check) {
				$("#carpetas").find( ".view-cl" ).prop('checked',check);
				$("#carpetas").find( ".view-car" ).prop('checked',check);
				$("#carpetas").find( ".view-cl").attr("disabled", "");
				$("#carpetas").find( ".view-car").attr("disabled", "");
			} else {
				$("#carpetas").find( ".view-cl").removeAttr("disabled");
				$("#carpetas").find( ".view-car").removeAttr("disabled");
			}
		}

	});

	$(document).on('click','.chk-v',function(e){
		var id = $(this).attr("data-id");
		var check1 = $("#a-"+id).is(':checked');
		var check2 = $("#b-"+id).is(':checked');
		var check3 = $("#c-"+id).is(':checked');
		if(check1 || check2 || check3)
			$("#v-"+id).attr("disabled", "");
		else 
			$("#v-"+id).removeAttr("disabled");
		$("#v-"+id).prop('checked', true);
	});

	$(document).on('click','.title-module',function(e){
		var id = $(this).attr("data-id");
		var check = $('#v-'+id).is(':checked');
		
		$("#v-"+id).prop('checked',!check);
		$("#a-"+id).prop('checked',!check);
		$("#b-"+id).prop('checked',!check);
		$("#c-"+id).prop('checked',!check);

		var check1 = $("#a-"+id).is(':checked');
		var check2 = $("#b-"+id).is(':checked');
		var check3 = $("#c-"+id).is(':checked');
		if(check1 || check2 || check3)
			$("#v-"+id).attr("disabled", "");
		else 
			$("#v-"+id).removeAttr("disabled");
		
	});

	$(document).on('change','#tipo',function(e) {
		var tipo = $('#tipo').val();
		if(tipo == 1) {
			$('#cliente').val(0);
			$('#cliente').select2();
			$("#cliente").prop("disabled", true);
		} else {
			$("#cliente").prop("disabled", false);
		}
	});

	$(document).on('change','#cliente',function(e) {
		e.preventDefault();
		var cliente = $('#cliente').val();
		if(cliente != 0) {
			showFolders2(cliente);
		}
	});

	$(document).on('click','.title-car',function(e){
		var id = $(this).attr("id");
		var check = $('#'+id+'-v').is(':checked');
		
		$('#'+id+'-v').prop('checked',!check);
		$('#'+id+'-a').prop('checked',!check);
		$('#'+id+'-b').prop('checked',!check);
		$('#'+id+'-c').prop('checked',!check);

		var check1 = $("#"+id+"-a").is(':checked');
		var check2 = $("#"+id+"-b").is(':checked');
		var check3 = $("#"+id+"-c").is(':checked');
		if(check1 || check2 || check3)
			$("#"+id+"-v").attr("disabled", "");
		else 
			$("#"+id+"-v").removeAttr("disabled");
		
	});

	$(document).on('click','.chk-v-cl',function(e) {
		var id = $(this).attr("data-id");
		var check1 = $("#cl-"+id+"-a").is(':checked');
		var check2 = $("#cl-"+id+"-b").is(':checked');
		var check3 = $("#cl-"+id+"-c").is(':checked');
		if(check1 || check2 || check3)
			$("#cl-"+id+"-v").attr("disabled", "");
		else 
			$("#cl-"+id+"-v").removeAttr("disabled");
		$("#cl-"+id+"-v").prop('checked', true);
	});

	$(document).on('click','.chk-v-car',function(e) {
		var id = $(this).attr("data-id");
		var check1 = $("#car-"+id+"-a").is(':checked');
		var check2 = $("#car-"+id+"-b").is(':checked');
		var check3 = $("#car-"+id+"-c").is(':checked');
		if(check1 || check2 || check3)
			$("#car-"+id+"-v").attr("disabled", "");
		else 
			$("#car-"+id+"-v").removeAttr("disabled");
		$("#car-"+id+"-v").prop('checked', true);
	});

	$(document).on('click','.has-cl',function(e) {
		var cli_id = $(this).attr('data-par');
		var is_checked = $(this).is(':checked');

		if(!is_checked) {
			$("#cl-"+cli_id+"-v").removeAttr('disabled');
			$("#cl-"+cli_id+"-a").removeAttr('disabled');
			$("#cl-"+cli_id+"-b").removeAttr('disabled');
			$("#cl-"+cli_id+"-c").removeAttr('disabled');
		} else {
			//Mismos permisos que este
			if($(this).hasClass('view-car')) {
				$('#cl-'+cli_id+'-v').prop('checked','true');
				$("#cl-"+cli_id+"-v").attr('disabled', 'disabled');
			}

			if($(this).hasClass('reg-car')) {
				$('#cl-'+cli_id+'-v').prop('checked','true');
				$("#cl-"+cli_id+"-v").attr('disabled', 'disabled');

				$('#cl-'+cli_id+'-a').prop('checked','true');
				$("#cl-"+cli_id+"-a").attr('disabled', 'disabled');
			}

			if($(this).hasClass('del-car')) {
				$('#cl-'+cli_id+'-v').prop('checked','true');
				$("#cl-"+cli_id+"-v").attr('disabled', 'disabled');

				$('#cl-'+cli_id+'-b').prop('checked','true');
				$("#cl-"+cli_id+"-b").attr('disabled', 'disabled');
			}

			if($(this).hasClass('ch-car')) {
				$('#cl-'+cli_id+'-v').prop('checked','true');
				$("#cl-"+cli_id+"-v").attr('disabled', 'disabled');

				$('#cl-'+cli_id+'-c').prop('checked','true');
				$("#cl-"+cli_id+"-c").attr('disabled', 'disabled');
			}

		}

	});

	$(document).on('click','.has-par',function(e) {
		var car_id = $(this).attr('data-par');
		var is_checked = $(this).is(':checked');

		if(!is_checked) {
			$("#car-"+car_id+"-v").removeAttr('disabled');
			$("#car-"+car_id+"-a").removeAttr('disabled');
			$("#car-"+car_id+"-b").removeAttr('disabled');
			$("#car-"+car_id+"-c").removeAttr('disabled');
		} else {
			//Mismos permisos que este
			if($(this).hasClass('view-car')) {
				$('#car_id-'+car_id+'-v').prop('checked','true');
				$("#car_id-"+car_id+"-v").attr('disabled', 'disabled');
			}

			if($(this).hasClass('reg-car')) {
				$('#car_id-'+car_id+'-v').prop('checked','true');
				$("#car_id-"+car_id+"-v").attr('disabled', 'disabled');

				$('#car_id-'+car_id+'-a').prop('checked','true');
				$("#car_id-"+car_id+"-a").attr('disabled', 'disabled');
			}

			if($(this).hasClass('del-car')) {
				$('#car_id-'+car_id+'-v').prop('checked','true');
				$("#car_id-"+car_id+"-v").attr('disabled', 'disabled');

				$('#car_id-'+car_id+'-b').prop('checked','true');
				$("#car_id-"+car_id+"-b").attr('disabled', 'disabled');
			}

			if($(this).hasClass('ch-car')) {
				$('#car_id-'+car_id+'-v').prop('checked','true');
				$("#car_id-"+car_id+"-v").attr('disabled', 'disabled');

				$('#car_id-'+car_id+'-c').prop('checked','true');
				$("#car_id-"+car_id+"-c").attr('disabled', 'disabled');
			}

		}

	});

	/*
	 * @author: Cynthia Castillo
	 * 
	 * Guardar
	 */
  	$(document).on('click','.guardar',function(e) {
		e.preventDefault();
		$('#frm-cliente').submit();
	});

	$(document).on('submit','#frm-cliente',function(e) {
		e.preventDefault();
		$('input').each(function(){
			$(this).removeAttr('disabled');
		});
		$.ajax({
			type: 'POST',
			url: 'include/Libs.php?accion=saveRecord',
			data: $('#frm-cliente').serialize(),
			dataType:'json',
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
									window.location = "index.php";
								}
							}
						}
					}
				});
			}
		});
	});

});

function showModules() {
	$.ajax({
		type: 'POST',
		url: 'include/Libs.php?accion=showModules',
		dataType:'json',
		beforeSend: function(){
			$('#table-content').html("<div class='loader'></div>");
		},
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
			$('#modulos').html(result.content);
		}
	});
}

function showFolders() {
	$.ajax({
		type: 'POST',
		url: 'include/Libs.php?accion=showFolders',
		dataType:'json',
		beforeSend: function(){
			$('#table-content').html("<div class='loader'></div>");
		},
		error: function(){
			bootbox.dialog({
				message: "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.",
				buttons: {
					cerrar: {
						label: "Cerrar",
						callback: function() {
							bootbox.hideAll();
							//window.location = "index.php";
						}
					}
				}
			});
		},
		success: function(result){
			$('#carpetas tbody').html(result.carpetas);
		}
	});
}

function showFolders2(cliente) {
	var params = {};
	params.cliente = cliente;
	$.ajax({
		type: 'POST',
		url: 'include/Libs.php?accion=showFolders',
		data: params,
		dataType:'json',
		beforeSend: function(){
			$('#table-content').html("<div class='loader'></div>");
		},
		error: function(){
			bootbox.dialog({
				message: "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.",
				buttons: {
					cerrar: {
						label: "Cerrar",
						callback: function() {
							bootbox.hideAll();
							//window.location = "index.php";
						}
					}
				}
			});
		},
		success: function(result){
			$('#carpetas tbody').html(result.carpetas);
		}
	});
}

function getClients() {
	$.ajax({
		type: 'POST',
		url: 'include/Libs.php?accion=getClients',
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
			$('.cont-clientes').html(result.select);
			$('#cliente').select2();
			$("#cliente").prop("disabled", true);
		}
	});
}

function getTiposUsuarios() {
	$.ajax({
		type: 'POST',
		url: 'include/Libs.php?accion=getTiposUsuarios',
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
			$('.cont-tipo').html(result.select);
		}
	});
}

