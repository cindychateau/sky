$(document).ready(function(){
	
	getReport();

    $(document).on('click','.generar',function(e) {
        e.preventDefault();
        getExcel();
    });

});

function getReport() {
    $.ajax({
        type: 'POST',
        url: 'include/Libs.php?accion=getReport',
        dataType:'json',
        beforeSend: function() {
            $("#table-peso tbody").html('<i class="fa fa-spinner fa-2x fa-spin"></i>');
            $('.cont-guardar').html('');
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

function getExcel() {
    $.ajax({
        type: 'POST',
        url: 'include/Libs.php?accion=getExcel1',
        dataType:'json',
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
                $(".cont-guardar").html('<a href="include/peso-cantidad.xlsx"><button class="btn btn-success" title=""><i class="far fa-save"></i> Guardar Excel</button></a>');
            }
            
        }
    });
}