<?php
/*
 *	Verificación de Sesión
 */
if(!isset($_SESSION)){
	@session_start();

}

if (isset($_SESSION["sky"]["userid"])) {
	//Si ya se encuentra registrado el usuario en la session lo redirecciona al sistema
	header("Location: home.php");
}

$error = false;

if (isset($_SESSION["sky"]["loginError"])) {
	$error = true;
	$errorNo = $_SESSION["sky"]["loginError"];
	unset($_SESSION["sky"]["loginError"]);
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<title>Sistema de Archivo de Documentos - Sky Consulting</title>
	<meta content='width=device-width, initial-scale=1.0, shrink-to-fit=no' name='viewport' />
	<link rel="icon" href="images/favicon.jpeg" type="image/x-icon"/>

	<!-- Fonts and icons -->
	<script src="assets/js/plugin/webfont/webfont.min.js"></script>
	<script>
		WebFont.load({
			google: {"families":["Lato:300,400,700,900"]},
			custom: {"families":["Flaticon", "Font Awesome 5 Solid", "Font Awesome 5 Regular", "Font Awesome 5 Brands", "simple-line-icons"], urls: ['assets/css/fonts.min.css']},
			active: function() {
				sessionStorage.fonts = true;
			}
		});
	</script>
	
	<!-- CSS Files -->
	<link rel="stylesheet" href="assets/css/bootstrap.min.css">
	<link rel="stylesheet" href="assets/css/millenium.min.css">
	<!-- SKY CONSULTING -->
	<link rel="stylesheet" href="assets/css/styles.css">
	<link rel="stylesheet" type="text/css" href="assets/css/style.php">
</head>
<body class="login">
	<div class="wrapper wrapper-login wrapper-login-full p-0">
		<div class="login-aside w-50 d-flex flex-column align-items-center justify-content-center text-center bg-secondary-gradient">
			<h1 class="title fw-bold text-white mb-3">Sky Consulting</h1>
			<p class="subtitle text-white op-7">Bienvenido al Sistema de Archivo de Documentos. Para comenzar a utilizarlo favor de iniciar sesión.</p>
		</div>
		<div class="login-aside w-50 d-flex align-items-center justify-content-center bg-white">
			<div class="container container-login container-transparent animated fadeIn">
				<h3 class="text-center">Iniciar Sesión</h3>
				<form class="login-form">
					<div id="login-msg" class="alert alert-danger" style="display: none;"></div>
					<div class="form-group">
						<label for="email" class="placeholder"><b>E-mail</b></label>
						<input id="email" name="email" type="text" class="form-control" required>
					</div>
					<div class="form-group">
						<label for="password" class="placeholder"><b>Password</b></label>
						<a href="#" id="show-signup" class="link float-right">¿Olvidó su contraseña?</a>
						<div class="position-relative">
							<input id="password" name="password" type="password" class="form-control" required>
							<div class="show-password">
								<i class="icon-eye"></i>
							</div>
						</div>
					</div>
					<div class="form-group form-action-d-flex mb-3 text-center">
						<a href="#" id="iniciar-sesion" class="btn btn-secondary col-md-5 float-right mt-3 mt-sm-0 fw-bold">Iniciar Sesión</a>
					</div>
					<div class="login-account">
						<span class="msg">¿No tiene cuenta? Comuníquese con su administrador para el registro.</span>
					</div>
				</form>
			</div>

			<div class="container container-signup container-transparent animated fadeIn">
				<h3 class="text-center">Recuperar Contraseña</h3>
				<form class="forgot-form">
					<div id="forgot-msg" class="alert alert-danger" style="display: none;"></div>
					<div class="form-group">
						<label for="email" class="placeholder"><b>E-mail</b></label>
						<input  id="email" name="email" type="email" class="form-control" required>
					</div>
					<div class="row form-action">
						<div class="col-md-6">
							<a href="#" id="show-signin" class="btn btn-danger btn-link w-100 fw-bold">Cancel</a>
						</div>
						<div class="col-md-6">
							<a id="recuperar" href="#" class="btn btn-secondary w-100 fw-bold">Enviar</a>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
	<script src="assets/js/core/jquery.3.2.1.min.js"></script>
	<script src="assets/js/plugin/jquery-ui-1.12.1.custom/jquery-ui.min.js"></script>
	<script src="assets/js/core/popper.min.js"></script>
	<script src="assets/js/core/bootstrap.min.js"></script>
	<script src="assets/js/millenium.min.js"></script>
	<!-- Script de Página -->
	<script src="assets/js/index.js"></script>
</body>
</html>