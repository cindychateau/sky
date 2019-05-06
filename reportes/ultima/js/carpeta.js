$(document).ready(function(){
	
	getReport();

});

function getReport() {
    var params = {};
    params.cl = $('#cl').val();
    params.c = $('#c').val();
    $.ajax({
        type: 'POST',
        url: 'include/Libs.php?accion=getReportCarpeta',
        dataType:'json',
        data: params,
        beforeSend: function() {
            $("#table-peso tbody").html('<i class="fa fa-spinner fa-2x fa-spin"></i>')
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