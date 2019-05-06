$(document).ready(function () {

	$(document).on('click','#iniciar-sesion',function(e) {
		e.preventDefault();
		$(".login-form").submit();
	});

	$(".login-form").submit(function (e){
		e.preventDefault();
		$.ajax({
			url: 'include/Login.php?accion=login',
			type:'POST',
			dataType: 'JSON',
			data: $(this).serialize(),
			error: function () {
				$('#login-msg').html("Experimentamos fallas técnicas.");
				$('#login-msg').fadeIn();
			},
			success: function (result){
				if (result.auth){
					document.location.href="./home.php";
				}else {
					$('#login-msg').html(result.msg);
					$('#login-msg').fadeIn();
				}
			}
		});
	});

	$(document).on('click','#recuperar',function(e) {
		e.preventDefault();
		$(".forgot-form").submit();
	});

	$(".forgot-form").submit(function (e) {
		e.preventDefault();
		$.ajax({
			url: 'include/Login.php?accion=forgot',
			type: 'POST',
			dataType: 'JSON',
			data: $(this).serialize(),
			error: function() {
				$('#forgot-msg').html(result.msg);
				$('#forgot-msg').fadeIn();
			}, success: function (result) {
				if (result.reset) {
					$('#forgot-msg').addClass('alert-info');
					$('#forgot-msg').removeClass('alert-danger');
				}else {
					$('#forgot-msg').removeClass('alert-info');
					$('#forgot-msg').addClass('alert-danger');
				}
				$('#forgot-msg').html(result.msg);
				$('#forgot-msg').fadeIn();
			}
		});
	});

	$(document).on('click','.close', function (e) {
		e.preventDefault();
		var tipo = $(this).attr("data-dismiss");
		if (typeof tipo !== 'undefined' && tipo !== false && tipo == 'alerta') {
			$(this).parent().css("display","none");
			console.log("Hidden alert");
		} 
	});
});