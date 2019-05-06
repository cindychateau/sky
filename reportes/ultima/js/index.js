$(document).ready(function(){

    $('.fecha').datetimepicker({
        format: 'DD/MM/YYYY'
    });     
    

	getLast();

    $(document).on('click','.ver-reporte',function(e) {
        e.preventDefault();

        var fecha_1 = $("#fecha_1").val();
        var fecha_2 = $("#fecha_2").val();

        if(!isEmpty(fecha_1) && !isEmpty(fecha_2)) {
            getReporteFechas();
        } else {
             bootbox.dialog({
                message: "Ingrese ambas fechas para desplegar el reporte.",
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
    });

    $(document).on('click','.generar',function(e) {
        e.preventDefault();
        getExcel();
    });

});

function getLast() {
    $.ajax({
        type: 'POST',
        url: 'include/Libs.php?accion=getReport',
        dataType:'json',
        beforeSend: function() {
            $("#table-peso tbody").html('<i class="fa fa-spinner fa-2x fa-spin"></i>');
        },
        error: function(){
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
            if(result.error) {
                window.location = "index.php";
            } else {
                $("#table-peso tbody").html(result.reporte);
            }
            
        }
    });
}

function getReporteFechas() {
    var fecha_1 = $("#fecha_1").val();
    var fecha_2 = $("#fecha_2").val();
    var actividad = $("#actividad").val();
    params = {};
    params.fecha_1 = fecha_1;
    params.fecha_2 = fecha_2;
    $.ajax({
        type: 'POST',
        url: 'include/Libs.php?accion=getReporteFechas',
        dataType:'json',
        data: params,
        beforeSend: function() {
            $("#table-peso tbody").html('<i class="fa fa-spinner fa-2x fa-spin"></i>');
        },
        error: function(){
            bootbox.dialog({
                message: "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.",
                buttons: {
                    cerrar: {
                        label: "Cerrar",
                        callback: function() {
                            $(".cont-loader").removeClass("loader");
                            $("input, button").removeAttr("disabled");
                            bootbox.hideAll();
                            //window.location = "../";
                        }
                    }
                }
            });
        },
        success: function(result){
            if(result.error) {
                window.location = "index.php";
            } else {
                $("#table-peso tbody").html(result.reporte);
            }
            
        }
    }); 
}

function getExcel() {
    var fecha_1 = $("#fecha_1").val();
    var fecha_2 = $("#fecha_2").val();
    var actividad = $("#actividad").val();
    params = {};
    params.fecha_1 = fecha_1;
    params.fecha_2 = fecha_2;
    $.ajax({
        type: 'POST',
        url: 'include/Libs.php?accion=getExcel1',
        dataType:'json',
        data: params,
        beforeSend: function() {
            $("input, button").attr("disabled", "disabled");
            $(".cont-loader").html("<div class='loader'></div>");
            $(".cont-guardar").html('');
        },
        error: function(){
            bootbox.dialog({
                message: "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.",
                buttons: {
                    cerrar: {
                        label: "Cerrar",
                        callback: function() {
                            $(".cont-loader").html("");
                            $("input, button").removeAttr("disabled");
                            $(".cont-guardar").html('');
                            bootbox.hideAll();
                        }
                    }
                }
            });
        },
        success: function(result){
            if(result.error) {
                window.location = "index.php";
            } else {
                $(".cont-loader").html("");
                $("input, button").removeAttr("disabled");
                $(".cont-guardar").html('<a href="include/ultima-alta.xlsx"><button class="btn btn-success" title=""><i class="far fa-save"></i> Guardar Excel</button></a>');
            }
            
        }
    });
}

function isEmpty(str) {
    return (!str || 0 === str.length || str == "");
}