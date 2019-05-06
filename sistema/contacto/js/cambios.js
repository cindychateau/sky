var dir_fact = 0;
var dir_ent = 0;
var contacto = 0;
$(document).ready(function(){

	getRecord();

	//Verificamos si está checkearon el campo de OTRO
	$('#dias_cred_4').on('ifChecked', function(e){
	  $('.cont-otro').removeClass("display-none");
	});

	$('#dias_cred_4').on('ifUnchecked', function(e){
	  $('.cont-otro').addClass("display-none");
	});

	//Si hacen click en agregar dirección de fact
	$(document).on('click','.agregar-dir-fact',function(e) {
		e.preventDefault();
		dir_fact++;
		nueva_direccion = '<div id="dir_fact_'+dir_fact+'">'+
                           '   <div class="row">'+
                           '     <div class="col-sm-12">'+
                           '       <div class="form-group row">'+
                           '         <label class="col-sm-2 label-control" for="dir_fact_nombre_'+dir_fact+'">Nombre Comercial de Lugar de Facturación*</label>'+
                           '         <div class="col-sm-4">'+
                           '           <input type="text" id="dir_fact_nombre_'+dir_fact+'" class="form-control border-primary" name="dir_fact_nombre['+dir_fact+']">'+
                           '         </div>'+
                           '         <label class="col-sm-2 label-control" for="dir_fact_razon_'+dir_fact+'">Razón Social*</label>'+
                           '         <div class="col-sm-4">'+
                           '           <input type="text" id="dir_fact_razon_'+dir_fact+'" class="form-control border-primary" name="dir_fact_razon['+dir_fact+']">'+
                           '         </div>'+
                           '       </div>'+
                           '     </div>'+
                           '   </div>'+
                           '   <div class="row">'+
                           '     <div class="col-md-12">'+
                           '       <div class="form-group row">'+
                           '         <label class="col-sm-2 label-control" for="dir_fact_direccion_'+dir_fact+'">Dirección*</label>'+
                           '         <div class="col-sm-10">'+
                           '           <textarea id="dir_fact_direccion_'+dir_fact+'" rows="6" class="form-control border-primary" name="dir_fact_direccion['+dir_fact+']"></textarea>'+
                           '         </div>'+
                           '       </div>'+
                           '     </div>'+
                           '   </div>'+
                           '   <div class="row">'+
                           '     <div class="col-sm-12">'+
                           '       <div class="form-group row">'+
                           '         <label class="col-sm-2 label-control" for="dir_fact_metodo_'+dir_fact+'">Método de Pago</label>'+
                           '         <div class="col-sm-4">'+
                           '           <input type="text" id="dir_fact_metodo_'+dir_fact+'" class="form-control border-primary" name="dir_fact_metodo['+dir_fact+']">'+
                           '         </div>'+
                           '         <label class="col-sm-2 label-control" for="dir_fact_cfdi_'+dir_fact+'">CFDI</label>'+
                           '         <div class="col-sm-4">'+
                           '           <input type="text" id="dir_fact_cfdi_'+dir_fact+'" class="form-control border-primary" name="dir_fact_cfdi['+dir_fact+']">'+
                           '         </div>'+
                           '       </div>'+
                           '     </div>'+
                           '   </div> '+
                           '   <div class="row">'+
                           '     <div class="col-md-12">'+
                           '       <div class="form-group row">'+
                           '         <label class="col-sm-2 label-control" for="dir_fact_comentarios_'+dir_fact+'">Información Adicional</label>'+
                           '         <div class="col-sm-8">'+
                           '           <textarea id="dir_fact_comentarios_'+dir_fact+'" rows="6" class="form-control border-primary" name="dir_fact_comentarios['+dir_fact+']"></textarea>'+
                           '         </div>'+
                           '		<div class="form-group col-sm-12 col-md-2 text-center mt-2">'+
                           '             <button type="button" class="btn btn-danger eliminar-dir-fact" data-id="'+dir_fact+'">'+
                           '             	<i class="ft-x"></i> Eliminar <br>Dirección'+
                           '             </button>'+
                           '         </div>'+
                           '       </div>'+
                           '     </div>'+
                           '   </div>'+
                           '   <hr>'+
                           ' </div>';

        $('#cont-dir-fact').append(nueva_direccion);
	});

	$(document).on('click','.eliminar-dir-fact',function(e) {
		e.preventDefault();
		direccion = $(this).attr("data-id");
		$('#dir_fact_'+direccion).remove();
	});

	$(document).on('click','.eliminar-dir-fact-old',function(e) {
		e.preventDefault();
		direccion = $(this).attr("data-id");
		cdf_id = $(this).attr("data-cdf-id");
		var dir_elim = '<input type="hidden" id="dir_fact_elim_'+cdf_id+'" name="dir_fact_elim[]" value="'+cdf_id+'">';
		$('#cont-dir-fact').append(dir_elim);
		$('#dir_fact_'+direccion).remove();
	});

	$(document).on('click','.agregar-dir-ent',function(e) {
		e.preventDefault();
		dir_ent++;
		nueva_direccion = '<div id="dir_ent_'+dir_ent+'">'+
                          '    <div class="row">'+
                          '      <div class="col-sm-12">'+
                          '        <div class="form-group row">'+
                          '          <label class="col-sm-2 label-control" for="dir_ent_nombre_'+dir_ent+'">Nombre de Localidad de Entrega*</label>'+
                          '          <div class="col-sm-10">'+
                          '            <input type="text" id="dir_ent_nombre_'+dir_ent+'" class="form-control border-primary" name="dir_ent_nombre['+dir_ent+']">'+
                          '          </div>'+
                          '        </div>'+
                          '      </div>'+
                          '    </div>'+
                          '    <div class="row">'+
                          '      <div class="col-md-12">'+
                          '        <div class="form-group row">'+
                          '          <label class="col-sm-2 label-control" for="dir_fact_direccion_'+dir_ent+'">Dirección*</label>'+
                          '          <div class="col-sm-10">'+
                          '            <textarea id="dir_ent_direccion_'+dir_ent+'" rows="6" class="form-control border-primary" name="dir_ent_direccion['+dir_ent+']"></textarea>'+
                          '          </div>'+
                          '        </div>'+
                          '      </div>'+
                          '    </div>'+
                          '    <div class="row">'+
                          '      <div class="col-md-12">'+
                          '        <div class="form-group row">'+
                          '          <label class="col-sm-2 label-control" for="dir_ent_comentarios_'+dir_ent+'">Información Adicional</label>'+
                          '          <div class="col-sm-8">'+
                          '            <textarea id="dir_ent_comentarios_'+dir_ent+'" rows="6" class="form-control border-primary" name="dir_ent_comentarios['+dir_ent+']"></textarea>'+
                           '         </div>'+
                           '		<div class="form-group col-sm-12 col-md-2 text-center mt-2">'+
                           '             <button type="button" class="btn btn-danger eliminar-dir-ent" data-id="'+dir_ent+'">'+
                           '             	<i class="ft-x"></i> Eliminar <br>Dirección'+
                           '             </button>'+
                           '         </div>'+
                           '       </div>'+
                           '     </div>'+
                           '   </div>'+
                           '   <hr>'+
                           ' </div>';

        $('#cont-dir-ent').append(nueva_direccion);
	});

	$(document).on('click','.eliminar-dir-ent',function(e) {
		e.preventDefault();
		direccion = $(this).attr("data-id");
		$('#dir_ent_'+direccion).remove();
	});

	$(document).on('click','.eliminar-dir-ent-old',function(e) {
		e.preventDefault();
		direccion = $(this).attr("data-id");
		cde_id = $(this).attr("data-cde-id");
		var dir_elim = '<input type="hidden" id="dir_ent_elim_'+cde_id+'" name="dir_ent_elim[]" value="'+cde_id+'">';
		$('#cont-dir-ent').append(dir_elim);
		$('#dir_ent_'+direccion).remove();
	});

	$(document).on('click','.agregar-contacto',function(e) {
		e.preventDefault();
		contacto++;
		nuevo_contacto = '<div id="contacto_'+contacto+'">'+
                         '     <div class="row">'+
                         '       <div class="col-sm-12">'+
                         '         <div class="form-group row">'+
                         '           <label class="col-sm-2 label-control" for="contacto_nombre_'+contacto+'">Nombre Completo*</label>'+
                         '           <div class="col-sm-10">'+
                         '             <input type="text" id="contacto_nombre_'+contacto+'" class="form-control border-primary" name="contacto_nombre['+contacto+']">'+
                         '           </div>'+
                         '         </div>'+
                         '       </div>'+
                         '     </div>'+
                         '     <div class="row">'+
                         '       <div class="col-md-12">'+
                         '         <div class="form-group row">'+
                         '           <label class="col-sm-2 label-control" for="contacto_area_'+contacto+'">Área</label>'+
                         '           <div class="col-sm-4">'+
                         '             <input type="text" id="contacto_area_'+contacto+'" class="form-control border-primary" name="contacto_area['+contacto+']">'+
                         '           </div>'+
                         '           <label class="col-sm-2 label-control" for="contacto_puesto_'+contacto+'">Puesto</label>'+
                         '           <div class="col-sm-4">'+
                         '             <input type="text" id="contacto_puesto_'+contacto+'" class="form-control border-primary" name="contacto_puesto['+contacto+']">'+
                         '           </div>'+
                         '         </div>'+
                         '       </div>'+
                         '     </div>'+
                         '     <div class="row">'+
                         '       <div class="col-md-12">'+
                         '         <div class="form-group row">'+
                         '           <label class="col-sm-2 label-control" for="contacto_email_'+contacto+'">E-mail*</label>'+
                         '           <div class="col-sm-4">'+
                         '             <input type="text" id="contacto_email_'+contacto+'" class="form-control border-primary" name="contacto_email['+contacto+']">'+
                         '           </div>'+
                         '           <label class="col-sm-2 label-control" for="contacto_telefono_'+contacto+'">Teléfono*</label>'+
                         '           <div class="col-sm-4">'+
                         '             <input type="text" id="contacto_telefono_'+contacto+'" class="form-control border-primary" name="contacto_telefono['+contacto+']">'+
                         '           </div>'+
                         '         </div>'+
                         '       </div>'+
                         '     </div>'+
                         '     <div class="row">'+
                         '       <div class="col-md-12">'+
                         '         <div class="form-group row">'+
                         '           <label class="col-sm-2 label-control" for="contacto_celular_'+contacto+'">Celular</label>'+
                         '           <div class="col-sm-4">'+
                         '             <input type="text" id="contacto_celular_'+contacto+'" class="form-control border-primary" name="contacto_celular['+contacto+']">'+
                         '           </div>'+
                           '		<div class="form-group col-sm-2 offset-4 text-center mt-2">'+
                           '             <button type="button" class="btn btn-danger eliminar-contacto" data-id="'+contacto+'">'+
                           '             	<i class="ft-x"></i> Eliminar <br>Contacto'+
                           '             </button>'+
                           '         </div>'+
                           '       </div>'+
                           '     </div>'+
                           '   </div>'+
                           '   <hr>'+
                           ' </div>';

        $('#cont-contacto').append(nuevo_contacto);
	});

	$(document).on('click','.eliminar-contacto',function(e) {
		e.preventDefault();
		cont = $(this).attr("data-id");
		$('#contacto_'+cont).remove();
	});

	$(document).on('click','.eliminar-contacto-old',function(e) {
		e.preventDefault();
		cont = $(this).attr("data-id");
		clc_id = $(this).attr("data-clc-id");
		var contacto_elim = '<input type="hidden" id="contacto_elim_'+clc_id+'" name="contacto_elim[]" value="'+clc_id+'">';
		$('#cont-contacto').append(contacto_elim);
		$('#contacto_'+cont).remove();
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

function getRecord() {
	params = {};
	params.id = $("#id").val();
	$.ajax({
		type: 'POST',
		url: 'include/Libs.php?accion=showRecord',
		dataType:'json',
		data: params,
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
			if(!result.error){
				$("#nombre_completo").val(result.CLI_NOMBRE_COMERCIAL);

				//Dias de Cred
				if(result.dias_cred_0 == 1) {
					$('#dias_cred_0').iCheck('check');
				}

				if(result.dias_cred_1 == 1) {
					$('#dias_cred_1').iCheck('check');
				}

				if(result.dias_cred_2 == 1) {
					$('#dias_cred_2').iCheck('check');
				}

				if(result.dias_cred_3 == 1) {
					$('#dias_cred_3').iCheck('check');
				}

				if(result.dias_cred_4 == 1) {
					$('#dias_cred_4').iCheck('check');
					$('.cont-otro').removeClass("display-none");
					$('#dias_cred_otro').val(result.CLI_DIAS_CRED_OTRO);
				}

				//Direcciones de Facturación
				$("#cont-dir-fact").html(result.cont_dir_fact);
				dir_fact = result.dir_fact_num;

				//Direcciones de Entrega
				$("#cont-dir-ent").html(result.cont_dir_ent);
				dir_ent = result.dir_ent_num;

				//Contacto
				$("#cont-contacto").html(result.cont_contacto);
				contacto = result.contacto_num;

				getUsers(result.SIU_ID);
			}
			else {
				window.location = "index.php";
			}
		}
	});	
}


function getUsers(id) {
	params = {};
	params.id = id;
	$.ajax({
		type: 'POST',
		url: 'include/Libs.php?accion=showUsers',
		dataType:'json',
		data: params,
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
			$('.cont-users').html(result.select);
		}
	});
}