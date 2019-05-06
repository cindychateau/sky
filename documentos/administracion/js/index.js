var cli_id = 0;
var car_id = 0;
var doc_id = 0;
var tree_id;
var tree_header;
$(document).ready(function(){

	printRoot();

	//Expandimos carpeta
	$(document).on('dblclick','.has_child.closed',function(e) {
		e.preventDefault();
		tree_header = $(this).attr('id'); //El header de la carpeta 
		console.log(tree_header);
		tree_id = $('#'+tree_header).parent().attr('id'); //El elemento principal
		$('.tree-selected').removeClass('tree-selected'); //Estilo de seleccionado
		$('#'+tree_header).addClass('tree-selected');
		$('#'+tree_header).removeClass('closed');
		$('#'+tree_header).addClass('opened');

		//Revisamos si es la carpeta root de un cliente en específico
		var accion = 'printFolder';
		if($('#'+tree_header).hasClass('tree-cl')) {
			cli_id = $('#'+tree_header).attr('data-id');
			car_id = 0;
			accion = 'printClientRoot';
		} else {
			//Si no entonces vemos qué carpeta específica es
			cli_id = 0;
			car_id = $('#'+tree_header).attr('data-id');
		}

		//Revisa si tiene hijos
		if($('#'+tree_header).hasClass('has_child')) {
			//Revisa si no ha sido cargado anteriormente
			if($('#'+tree_header).hasClass('nc')) {
				printFolder(cli_id, car_id);
			} else {
				//Si el contenido YA fue cargado, solamente lo mostramos de nuevo
				$('#'+tree_id).find('.tree-folder-content').css('display', 'block');
				//Cambiamos a que sea MINUS (para minimizar)
				$('#'+tree_id+'-fa').removeClass('fa-folder-plus');
				$('#'+tree_id+'-fa').addClass('fa-folder-minus');
			}
		}

	});

	//Expandimos carpeta haciendo click en el iconito
	/*$(document).on('click','.fa-folder-plus',function(e) {
		e.preventDefault();
		tree_header = $(this).parent().attr('id'); //El header de la carpeta 
		tree_id = $('#'+tree_header).parent().attr('id'); //El elemento principal
		$('.tree-selected').removeClass('tree-selected'); //Estilo de seleccionado
		$('#'+tree_header).addClass('tree-selected');

		//Revisamos si es la carpeta root de un cliente en específico
		var accion = 'printFolder';
		if($('#'+tree_header).hasClass('tree-cl')) {
			cli_id = $('#'+tree_header).attr('data-id');
			car_id = 0;
			accion = 'printClientRoot';
		} else {
			//Si no entonces vemos qué carpeta específica es
			cli_id = 0;
			car_id = $('#'+tree_header).attr('data-id');
		}

		//Revisa si tiene hijos
		if($('#'+tree_header).hasClass('has_child')) {
			//Revisa si no ha sido cargado anteriormente
			if($('#'+tree_header).hasClass('nc')) {
				printFolder(cli_id, car_id);
			} else {
				//Si el contenido YA fue cargado, solamente lo mostramos de nuevo
				$('#'+tree_id).find('.tree-folder-content').css('display', 'block');
				//Cambiamos a que sea MINUS (para minimizar)
				$('#'+tree_id+'-fa').removeClass('fa-folder-plus');
				$('#'+tree_id+'-fa').addClass('fa-folder-minus');
			}
		}

	});*/

	//Minimizamos carpeta
	$(document).on('dblclick','.has_child.opened',function(e) {
		e.preventDefault();
		tree_header = $(this).attr('id'); //El header de la carpeta 
		tree_id = $('#'+tree_header).parent().attr('id'); //El elemento principal
		$('.tree-selected').removeClass('tree-selected'); //Estilo de seleccionado
		$('#'+tree_header).addClass('tree-selected');
		$('#'+tree_header).addClass('closed');
		$('#'+tree_header).removeClass('opened');
		//Escondemos el contenido de la carpeta
		$('#'+tree_id).find('.tree-folder-content').css('display', 'none');
		$('#'+tree_id+'-fa').removeClass('fa-folder-minus');
		$('#'+tree_id+'-fa').addClass('fa-folder-plus');
	});

	//Minimizamos carpeta con el iconito
	/*$(document).on('click','.fa-folder-minus',function(e) {
		e.preventDefault();
		tree_header = $(this).parent().attr('id'); //El header de la carpeta 
		tree_id = $('#'+tree_header).parent().attr('id'); //El elemento principal
		$('.tree-selected').removeClass('tree-selected'); //Estilo de seleccionado
		$('#'+tree_header).addClass('tree-selected');
		//Escondemos el contenido de la carpeta
		$('#'+tree_id).find('.tree-folder-content').css('display', 'none');
		$('#'+tree_id+'-fa').removeClass('fa-folder-minus');
		$('#'+tree_id+'-fa').addClass('fa-folder-plus');
	});*/

	//Seleccionamos carpeta
	$(document).on('click','.tree-folder-header',function(e) {
		e.preventDefault();
		tree_header = $(this).attr('id'); //El header de la carpeta 
		$('.tree-selected').removeClass('tree-selected'); //Estilo de seleccionado
		$('#'+tree_header).addClass('tree-selected');

		//Revisamos si es la carpeta root de un cliente en específico
		if($('#'+tree_header).hasClass('tree-cl')) {
			cli_id = $('#'+tree_header).attr('data-id');
			car_id = 0;
		} else {
			//Si no entonces vemos qué carpeta específica es
			cli_id = 0;
			car_id = $('#'+tree_header).attr('data-id');
		}

		//Imprimimos Botones dependiendo de los permisos
		var params = {};
		params.car_id = car_id;
		params.cli_id = cli_id;
		$.ajax({
			type: 'POST',
			url: 'include/Libs.php?accion=getCarButtons',
			dataType:'json',
			data: params,
			beforeSend: function(){
				$('.buttons').html();
			},
			error: function(){
				bootbox.dialog({
					message: "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.",
					buttons: {
						cerrar: {
							label: "Cerrar",
							callback: function() {
								bootbox.hideAll();
								//window.location = "../";
							}
						}
					}
				});
			},
			success: function(result){
				$('.buttons').html(result.buttons);
			}
		});


	});

	//Seleccionamos documento
	$(document).on('click','.tree-item',function(e) {
		e.preventDefault();
		$('.tree-selected').removeClass('tree-selected'); //Estilo de seleccionado
		$(this).addClass('tree-selected');
		car_id = $(this).attr('data-car');
		doc_id = $(this).attr('data-id');
		var params = {};
		params.car_id = car_id;
		params.doc_id = doc_id;
		$.ajax({
			type: 'POST',
			url: 'include/Libs.php?accion=getDocButtons',
			dataType:'json',
			data: params,
			beforeSend: function(){
				$('.buttons').html();
			},
			error: function(){
				bootbox.dialog({
					message: "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.",
					buttons: {
						cerrar: {
							label: "Cerrar",
							callback: function() {
								bootbox.hideAll();
								//window.location = "../";
							}
						}
					}
				});
			},
			success: function(result){
				$('.buttons').html(result.buttons);
			}
		});
	});

	//Abrimos doc en tab aparte
	$(document).on('dblclick','.tree-item',function(e) {
		e.preventDefault();
		var href = $(this).find('.tree-item-name a').attr('href');
		window.open(href, '_blank'); 
	});

	//Subimos Documento 
	$(document).on('click','.new-doc',function(e) {
		e.preventDefault();
		var html_msg = "<form id='form-new-doc' name='form-new-doc' role='form' class='form-horizontal'>"+
							"<div class='form-group'>"+
								"<div class='fileUpload btn btn-success'>"+
								    "<span><i class='fa fa-file-pdf'></i> <span class='seleccione_pdf'>Seleccione PDF</span></span>"+
								    "<input type='file' id='doc' name='doc' class='upload' accept='application/pdf' />"+
								"</div>"+
							"</div>"+
						  "</form>";
		bootbox.dialog({
			message: html_msg,
			buttons: {
				guardar: {
				    label: "Guardar",
				    className: "btn-success",
				    callback: function() {

				    	var formdata = new FormData($('form[name="form-new-doc"]')[0]);
				    	formdata.append('car_id', car_id);
				    	formdata.append('cli_id', cli_id);

				    	var accion_extra = 0;

				    	//Revisamos que no haya otro documento con el mismo nombre en la misma carpeta
				    	var params = {};
				    	params.car_id = car_id;
				    	params.cli_id = cli_id;
				    	params.doc = $('#doc').val().split('\\').pop();
				    	$.ajax({
							url: 'include/Libs.php?accion=checkDoc',
							type: 'POST',
							data: params,
							dataType: 'JSON',
							async: false,
							beforeSend: function(){
								$('input, file, textarea, button, select').each(function(){
									$(this).attr('disabled','disabled');
								});
								$('.modal-footer').prepend('<div class="loader"></div>');
							},
							error: function (){
								$('input, file, textarea, button, select').each(function(){
									$(this).removeAttr('disabled');
								});
								$('.loader').remove();
								bootbox.alert("Experimentamos fallas técnicas. Comuníquese con su proveedor.");
							}, success: function (result) {
								$('input, file, textarea, button, select').each(function(){
									$(this).removeAttr('disabled');
								});
								$('.loader').remove();

								if(!result.error) {
									if(result.check) {
										bootbox.dialog({
											message: 'Existe un documento con el mismo nombre. Elija la acción a tomar. (Esta acción no se podrá deshacer)',
											buttons: {
												reemplazar: {
													label: "Reemplazar",
													className: "btn-info",
													callback: function() {
														accion_extra = 1;
														formdata.append('accion_extra', accion_extra);
														saveDoc(formdata);
													}
												},
												conservar: {
													label: "Conservar ambos",
													className: "btn-primary",
													callback: function() {
														accion_extra = 2;
														formdata.append('accion_extra', accion_extra);
														saveDoc(formdata);
													}
												},
												cancelar: {
													label: "Cancelar",
													className: "btn-danger",
													callback: function() {
														accion_extra = 3;
														bootbox.hideAll();
													}
												}
											}
										});
									} else {
										formdata.append('accion_extra', accion_extra);
										saveDoc(formdata);
									}
								} else {
									bootbox.alert(result.msg);	
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
				    	bootbox.hideAll();
				    }
				}
			}
		});

		//Listener del documento
		$(document).on('change','#doc',function(e) {
			e.preventDefault();
			file_name = e.target.files[0].name;
			$('.seleccione_pdf').html(file_name);
		});	
	});

	//Eliminamos documento
	$(document).on('click','.erase-doc',function(e) {
		e.preventDefault();
		var doc_id = $(this).attr('data-id');
		var doc_name = $(this).attr('data-name');
		bootbox.dialog({
			message: '¿Desea eliminar permanentemente el documento: '+doc_name+'?',
			buttons: {
				guardar: {
				    label: "Aceptar",
				    className: "btn-success",
				    callback: function() {
				    	var params = {};
				    	params.doc_id = doc_id;
				    	$.ajax({
							url: 'include/Libs.php?accion=deleteDoc',
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
								bootbox.dialog({
									message: result.msg,
									buttons: {
										cerrar: {
											label: "Cerrar",
											callback: function() {
												if(!result.error) {

													var parent = $('#tree-item-'+doc_id).parents('.tree-folder').attr('id');
													var header = $('#'+parent).find('.tree-folder-header').attr('id');

													if($('#'+header).hasClass('tree-cl')) {
														cli_id = $('#'+header).attr('data-id');
														car_id = 0;
													} else {
														//Si no entonces vemos qué carpeta específica es
														cli_id = 0;
														car_id = $('#'+header).attr('data-id');
													}

													printFolder(cli_id, car_id);

													bootbox.hideAll();
												}
											}
										}
									}
								});
							}
						});

						return false;
				    }
				},
				cancelar: {
				    label: "Cancelar",
				    className: "btn-danger",
				    callback: function() {
				    	bootbox.hideAll();
				    }
				}
			}
		});
	});

	//Nueva Carpeta 
	$(document).on('click','.new-folder',function(e) {
		e.preventDefault();
		var html_msg = "<form id='form-new-folder' name='form-new-folder' role='form' class='form-horizontal'>"+
							"<div class='form-group'>"+
							    "<label for='carpeta' class='col-sm-3 control-label'>Nombre de Carpeta</label>"+
								"<div class='col-sm-7'>" +
									"<input type='input' id='carpeta' name='carpeta' class='form-control'>"+
								"</div>"+ 
							"</div>"+
						  "</form>";
		bootbox.dialog({
			message: html_msg,
			buttons: {
				guardar: {
				    label: "Guardar",
				    className: "btn-success",
				    callback: function() {

				    	//Revisamos que no haya otro documento con el mismo nombre en la misma carpeta
				    	var params = {};
				    	params.car_id = car_id;
				    	params.cli_id = cli_id;
				    	params.carpeta = $('#carpeta').val();
				    	$.ajax({
							url: 'include/Libs.php?accion=newFolder',
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
									$('#form-new-folder').prepend(alert);
									printFolder(cli_id, car_id);
									setTimeout(function () { bootbox.hideAll();}, 3000);
								} else {
									var alert = "<div class='alert alert-danger' role='alert'>"+result.msg+"</div>";
									$('#form-new-folder').prepend(alert);
								}


								/*bootbox.dialog({
									message: result.msg,
									buttons: {
										cerrar: {
											label: "Cerrar",
											callback: function() {
												if(!result.error) {

													printFolder(cli_id, car_id);

													bootbox.hideAll();
												}
											}
										}
									}
								});*/
							}
						});

						return false;
				    }
				},
				cancelar: {
				    label: "Cancelar",
				    className: "btn-danger",
				    callback: function() {
				    	bootbox.hideAll();
				    }
				}
			}
		});
	});

	//Eliminamos Carpeta
	$(document).on('click','.erase-folder',function(e) {
		e.preventDefault();
		var car_id = $(this).attr('data-id');
		var car_name = $(this).attr('data-name');
		bootbox.dialog({
			message: '¿Desea eliminar permanentemente la carpeta: '+car_name+'?<br> Recuerda que al ser eliminada todos sus contenidos serán borrados también.',
			buttons: {
				guardar: {
				    label: "Aceptar",
				    className: "btn-success",
				    callback: function() {
				    	var params = {};
				    	params.car_id = car_id;
				    	$.ajax({
							url: 'include/Libs.php?accion=deleteFolder',
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
								bootbox.dialog({
									message: result.msg,
									buttons: {
										cerrar: {
											label: "Cerrar",
											callback: function() {
												if(!result.error) {

													var parent = $('#car-'+car_id).parents('.tree-folder').attr('id');
													var header = $('#'+parent).find('.tree-folder-header').attr('id');

													if($('#'+header).hasClass('tree-cl')) {
														cli_id = $('#'+header).attr('data-id');
														car_id = 0;
													} else {
														//Si no entonces vemos qué carpeta específica es
														cli_id = 0;
														car_id = $('#'+header).attr('data-id');
													}

													printFolder(cli_id, car_id);

													bootbox.hideAll();
												}
											}
										}
									}
								});
							}
						});

						return false;
				    }
				},
				cancelar: {
				    label: "Cancelar",
				    className: "btn-danger",
				    callback: function() {
				    	bootbox.hideAll();
				    }
				}
			}
		});
	});

	//Renombrar Documento
	$(document).on('click','.rename-doc',function(e) {
		e.preventDefault();
		var doc_name = $(this).attr('data-name');
		var doc_id = $(this).attr('data-id');
		var html_msg = "<form id='form-rename-doc' name='form-rename-doc' role='form' class='form-horizontal'>"+
							"<div class='form-group'>"+
							    "<label for='doc' class='col-sm-3 control-label'>Nombre de Documento</label>"+
								"<div class='col-sm-7'>" +
									"<input type='input' id='doc' name='doc' class='form-control' value='"+doc_name+"'>"+
								"</div>"+ 
							"</div>"+
						  "</form>";
		bootbox.dialog({
			message: html_msg,
			buttons: {
				guardar: {
				    label: "Guardar",
				    className: "btn-success",
				    callback: function() {

				    	//Revisamos que no haya otro documento con el mismo nombre en la misma carpeta
				    	var params = {};
				    	params.doc_id = doc_id;
				    	params.doc = $('#doc').val();
				    	$.ajax({
							url: 'include/Libs.php?accion=renameDoc',
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
									$('#form-rename-doc').prepend(alert);
									var parent = $('#tree-item-'+doc_id).parents('.tree-folder').attr('id');
									var header = $('#'+parent).find('.tree-folder-header').attr('id');

									if($('#'+header).hasClass('tree-cl')) {
										cli_id = $('#'+header).attr('data-id');
										car_id = 0;
									} else {
										//Si no entonces vemos qué carpeta específica es
										cli_id = 0;
										car_id = $('#'+header).attr('data-id');
									}
									printFolder(cli_id, car_id);
									setTimeout(function () { bootbox.hideAll();}, 3000);
								} else {
									var alert = "<div class='alert alert-danger' role='alert'>"+result.msg+"</div>";
									$('#form-rename-doc').prepend(alert);
								}


								/*bootbox.dialog({
									message: result.msg,
									buttons: {
										cerrar: {
											label: "Cerrar",
											callback: function() {
												if(!result.error) {

													var parent = $('#tree-item-'+doc_id).parents('.tree-folder').attr('id');
													var header = $('#'+parent).find('.tree-folder-header').attr('id');

													if($('#'+header).hasClass('tree-cl')) {
														cli_id = $('#'+header).attr('data-id');
														car_id = 0;
													} else {
														//Si no entonces vemos qué carpeta específica es
														cli_id = 0;
														car_id = $('#'+header).attr('data-id');
													}

													printFolder(cli_id, car_id);

													bootbox.hideAll();
												}
											}
										}
									}
								});*/
							}
						});

						return false;
				    }
				},
				cancelar: {
				    label: "Cancelar",
				    className: "btn-danger",
				    callback: function() {
				    	bootbox.hideAll();
				    }
				}
			}
		});
	});

	//Renombrar Carpeta
	$(document).on('click','.rename',function(e) {
		e.preventDefault();
		var car_name = $(this).attr('data-name');
		var car_id = $(this).attr('data-id');
		var html_msg = "<form id='form-rename' name='form-rename' role='form' class='form-horizontal'>"+
							"<div class='form-group'>"+
							    "<label for='car' class='col-sm-3 control-label'>Nombre de Carpeta</label>"+
								"<div class='col-sm-7'>" +
									"<input type='input' id='car' name='car' class='form-control' value='"+car_name+"'>"+
								"</div>"+ 
							"</div>"+
						  "</form>";
		bootbox.dialog({
			message: html_msg,
			buttons: {
				guardar: {
				    label: "Guardar",
				    className: "btn-success",
				    callback: function() {

				    	//Revisamos que no haya otro documento con el mismo nombre en la misma carpeta
				    	var params = {};
				    	params.car_id = car_id;
				    	params.car = $('#car').val();
				    	$.ajax({
							url: 'include/Libs.php?accion=renameFolder',
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
									$('#form-rename').prepend(alert);
									var parent = $('#car-'+car_id).parents('.tree-folder').attr('id');
									var header = $('#'+parent).find('.tree-folder-header').attr('id');

									if($('#'+header).hasClass('tree-cl')) {
										cli_id = $('#'+header).attr('data-id');
										car_id = 0;
									} else {
										//Si no entonces vemos qué carpeta específica es
										cli_id = 0;
										car_id = $('#'+header).attr('data-id');
									}

									printFolder(cli_id, car_id);
									setTimeout(function () { bootbox.hideAll();}, 3000);
								} else {
									var alert = "<div class='alert alert-danger' role='alert'>"+result.msg+"</div>";
									$('#form-rename').prepend(alert);
								}


								/*bootbox.dialog({
									message: result.msg,
									buttons: {
										cerrar: {
											label: "Cerrar",
											callback: function() {
												if(!result.error) {

													var parent = $('#car-'+car_id).parents('.tree-folder').attr('id');
													var header = $('#'+parent).find('.tree-folder-header').attr('id');

													if($('#'+header).hasClass('tree-cl')) {
														cli_id = $('#'+header).attr('data-id');
														car_id = 0;
													} else {
														//Si no entonces vemos qué carpeta específica es
														cli_id = 0;
														car_id = $('#'+header).attr('data-id');
													}

													printFolder(cli_id, car_id);

													bootbox.hideAll();
												}
											}
										}
									}
								});*/
							}
						});

						return false;
				    }
				},
				cancelar: {
				    label: "Cancelar",
				    className: "btn-danger",
				    callback: function() {
				    	bootbox.hideAll();
				    }
				}
			}
		});
	});

	//Nueva Carpeta con Documentos 
	$(document).on('click','.new-folder2',function(e) {
		e.preventDefault();
		var files = '';
		var html_msg = "<form id='form-new-folder' name='form-new-folder' role='form' class='form-horizontal'>"+
							"<div class='form-group'>"+
								"<div class='fileUpload btn btn-info'>"+
								    "<span><i class='fa fa-folder'></i> <span class='seleccione_carpeta'>Seleccione Carpeta</span></span>"+
								    "<input type='file' id='pdfs' name='pdfs[]' class='upload' multiple directory webkitdirectory='' />"+
								"</div>"+
							"</div>"+
						  "</form>";
		bootbox.dialog({
			message: html_msg,
			buttons: {
				guardar: {
				    label: "Guardar",
				    className: "btn-success",
				    callback: function() {

				    	var formdata = new FormData();
				    	formdata.append('car_id', car_id);
				    	formdata.append('cli_id', cli_id);

						var paths = '';
						//Path
						for (var i in files){
							// Append the current file path to the paths variable (delimited by tripple hash signs - ###)
							paths += files[i].webkitRelativePath+"###";
							// Append current file to our FormData with the index of i
							formdata.append(i, files[i]);
						};
						//Append path
						formdata.append('paths', paths);

				    	$.ajax({
							url: 'include/Libs.php?accion=newFolderDocs',
							type: 'POST',
							data: formdata,
							dataType: 'JSON',
							processData: false,
        					contentType: false,
							beforeSend: function(){
								$('input, file, textarea, button, select').each(function(){
									$(this).attr('disabled','disabled');
								});
								$('.modal-footer').prepend('<div class="loader"></div>');
							},
							error: function (){
								$('input, file, textarea, button, select').each(function(){
									$(this).removeAttr('disabled');
								});
								$('.loader').remove();
								bootbox.alert("Experimentamos fallas técnicas. Comuníquese con su proveedor.");
							}, success: function (result) {
								$('input, file, textarea, button, select').each(function(){
									$(this).removeAttr('disabled');
								});
								$('.loader').remove();


								if(!result.error) {
									var alert = "<div class='alert alert-success' role='alert'>"+result.msg+"</div>";
									$('#form-new-folder').prepend(alert);
									printFolder(cli_id, car_id);
									setTimeout(function () { bootbox.hideAll();}, 3000);
								} else {
									var alert = "<div class='alert alert-danger' role='alert'>"+result.msg+"</div>";
									$('#form-new-folder').prepend(alert);
								}


								/*bootbox.dialog({
									message: result.msg,
									buttons: {
										cerrar: {
											label: "Cerrar",
											callback: function() {
												if(!result.error) {

													printFolder(cli_id, car_id);

													bootbox.hideAll();
												}
											}
										}
									}
								});*/
							}
						});

						return false;
				    }
				},
				cancelar: {
				    label: "Cancelar",
				    className: "btn-danger",
				    callback: function() {
				    	bootbox.hideAll();
				    }
				}
			}
		});

		//Revisamos las files a subir
		$(document).on('change','#pdfs',function(e) {
			files = e.target.files;
			var num_files = this.files.length;
			$('.seleccione_carpeta').html(num_files+" files");
		});
	});

	//Revisamos las files a subir
	$(document).on('change','#pdf',function(e) {
		files = e.target.files;
	});

	$(document).on('submit','#frm-prueba',function(e) {
		e.preventDefault();
		var formdata = new FormData();
		var paths = '';

		//Path
		for (var i in files){
			// Append the current file path to the paths variable (delimited by tripple hash signs - ###)
			paths += files[i].webkitRelativePath+"###";
			// Append current file to our FormData with the index of i
			formdata.append(i, files[i]);
		};

		//Append path
		formdata.append('paths', paths);
		$.ajax({
			type: 'POST',
			url: 'include/Libs.php?accion=pruebaFile',
			data: formdata,
			dataType:'json',
			processData: false,
        	contentType: false,
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
								alert(result);
							}
						}
					}
				});
			}
		});
	});

});

function printRoot() {
	$.ajax({
		type: 'POST',
		url: 'include/Libs.php?accion=printRoot',
		dataType:'json',
		error: function(){
			bootbox.dialog({
				message: "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.",
				buttons: {
					cerrar: {
						label: "Cerrar",
						callback: function() {
							bootbox.hideAll();
							//window.location = "../";
						}
					}
				}
			});
		},
		success: function(result){
			$('.tree').html(result.arbol);
		}
	});
}

function printFolder(cli_id, car_id) {
	var params = {};
	params.cli_id = cli_id;
	params.car_id = car_id;

	accion = 'printFolder';
	if(cli_id != 0) {
		accion = 'printClientRoot';
		tree_id = 'cl-'+cli_id;
	} else {
		tree_id = 'car-'+car_id;
	}

	$.ajax({
		type: 'POST',
		url: 'include/Libs.php?accion='+accion,
		dataType:'json',
		data: params,
		beforeSend: function(){
			$('#'+tree_id).find('.tree-loader').css("display", "block");
		},
		error: function(){
			bootbox.dialog({
				message: "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.",
				buttons: {
					cerrar: {
						label: "Cerrar",
						callback: function() {
							bootbox.hideAll();
							$('#'+tree_id).find('.tree-loader').css("display", "none");
						}
					}
				}
			});
		},
		success: function(result){
			$('#'+tree_id).find('.tree-loader').css("display", "none");
			if(!result.error) {
				//Desplegamos contenido
				$('#'+tree_id).find('.tree-folder-content').css('display', 'block');
				$('#'+tree_id).find('.tree-folder-content').html(result.arbol);

				//Cambiamos a que sea MINUS (para minimizar)
				$('#'+tree_id+'-fa').removeClass('fa-folder-plus');
				$('#'+tree_id+'-fa').addClass('fa-folder-minus');

				//Quitamos que NO se ha cargado (porque ya tenemos el contenido)
				$('#'+tree_id+"-child").removeClass('nc');
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

function saveDoc(formdata) {
	$.ajax({
		url: 'include/Libs.php?accion=newDoc',
		type: 'POST',
		data: formdata,
		dataType: 'JSON',
		processData: false,
		contentType: false,
		beforeSend: function(){
			$('input, file, textarea, button, select').each(function(){
				$(this).attr('disabled','disabled');
			});
			$('.modal-footer').prepend('<div class="loader"></div>');
		},
		error: function (){
			$('input, file, textarea, button, select').each(function(){
				$(this).removeAttr('disabled');
			});
			$('.loader').remove();
			bootbox.alert("Experimentamos fallas técnicas. Comuníquese con su proveedor.");
		}, success: function (result) {
			$('input, file, textarea, button, select').each(function(){
				$(this).removeAttr('disabled');
			});
			$('.loader').remove();

			if(!result.error) {
				var alert = "<div class='alert alert-success' role='alert'>"+result.msg+"</div>";
				$('#form-new-doc').prepend(alert);
				printFolder(cli_id, car_id);
				setTimeout(function () { bootbox.hideAll();}, 3000);
			} else {
				var alert = "<div class='alert alert-danger' role='alert'>"+result.msg+"</div>";
				$('#form-new-doc').prepend(alert);
			}

			/*bootbox.dialog({
				message: result.msg,
				buttons: {
					cerrar: {
						label: "Cerrar",
						callback: function() {
							if(!result.error) {

								printFolder(cli_id, car_id);

								bootbox.hideAll();
							}
						}
					}
				}
			});*/
		}
	});
}
