<?php

/*
 *  Se identifica la ruta 
 */

$url = explode("/sky", $_SERVER["REQUEST_URI"]);
$url = explode("/", $url[1]);

//$url = explode("/", $_SERVER["REQUEST_URI"]);

$ruta = "";
$file=$url[count($url)-1];
for ($i=1; $i < (count($url) - 1); $i++){
  $ruta .= "../";
}

//Se incluye la clase Common
include_once($ruta."include/Common.php");


/*
 *  Se definen los parámetros de la página
 */
define("PAGE_TITLE", "Configuración");
define("PAGE_DESCRIPTION", "Módulo encargado de establecer y editar las configuraciones del sistema");

$module = 11;
$parent = 1;

$common->sentinel($module);

//Se definen los js y css - sólo poner los nombres de los archivos no la terminación
$css = array();
$js = array('index');

?>
<!DOCTYPE html>
<html lang="es">
	<head>
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />
		<title><?php echo(TITLE_MAIN); ?></title>
		<meta content='width=device-width, initial-scale=1.0, shrink-to-fit=no' name='viewport' />
		<link rel="icon" href="<?php echo $ruta?>images/favicon.jpeg" type="image/x-icon"/>

		<!-- Fonts and icons -->
		<script src="<?php echo $ruta?>assets/js/plugin/webfont/webfont.min.js"></script>
		<script>
			WebFont.load({
				google: {"families":["Lato:300,400,700,900"]},
				custom: {"families":["Flaticon", "Font Awesome 5 Solid", "Font Awesome 5 Regular", "Font Awesome 5 Brands", "simple-line-icons"], urls: ['<?php echo $ruta?>assets/css/fonts.min.css']},
				active: function() {
					sessionStorage.fonts = true;
				}
			});
		</script>

		<!-- CSS Files -->
		<link rel="stylesheet" href="<?php echo $ruta?>assets/css/bootstrap.min.css">
		<link rel="stylesheet" href="<?php echo $ruta?>assets/css/millenium.css">

		<!-- SKY CONSULTING -->
		<link rel="stylesheet" href="<?php echo $ruta?>assets/css/styles.css">
		<link rel="stylesheet" type="text/css" href="<?php echo $ruta?>assets/css/style.php">
		<?php 
	      if (count($css) > 0) {
	        foreach ($css as $clave => $valor) {
	          echo '<link rel="stylesheet" href="'.$ruta.'assets/css/'.$valor.'.css" />';
	        }
	      }
	    ?>
	</head>
	<body>
		<div class="wrapper">
			<div class="main-header">
				<!-- Logo Header -->
				<div class="logo-header">

					<a href="<?php echo $ruta;?>" class="logo">
						<img src="<?php echo $ruta?>images/logo.png" alt="navbar brand" class="navbar-brand">
					</a>
					<button class="navbar-toggler sidenav-toggler ml-auto" type="button" data-toggle="collapse" data-target="collapse" aria-expanded="false" aria-label="Toggle navigation">
						<span class="navbar-toggler-icon">
							<i class="icon-menu"></i>
						</span>
					</button>
					<button class="topbar-toggler more"><i class="icon-options-vertical"></i></button>
					<div class="nav-toggle">
						<button class="btn btn-toggle toggle-sidebar">
							<i class="icon-menu"></i>
						</button>
					</div>
				</div>
				<!-- End Logo Header -->

				<!-- Navbar Header -->
				<nav class="navbar navbar-header navbar-expand-lg">
					<?php echo $common->printHeader(); ?>
				</nav>
				<!-- End Navbar -->
			</div>

			<!-- Sidebar -->
			<div class="sidebar">
				<div class="sidebar-wrapper scrollbar scrollbar-inner">
					<div class="sidebar-content">
						<?php echo $common->printUserInfo(); ?>
						<?php echo $common->printMenu($module, $parent); ?>
						
					</div>
				</div>
			</div>
			<!-- End Sidebar -->

			<div class="main-panel">
				<div class="content">
					<div class="page-inner">
						<?php echo $common->printBreadcrumbs($module);?>
						<div class="page-header">
							<h4 class="page-title"><?php echo(PAGE_TITLE); ?></h4>
						</div>
						<div class="page-category"><?php echo(PAGE_DESCRIPTION); ?></div>
						<!-- BG: CONTENIDO DE LA PÁGINA -->
						<div class="card">
							<form id="frm-contacto">
								<div class="card-body">
									<label>HOME (al iniciar sesión)</label>
									<textarea id="summernote" name="contacto"></textarea>
									<label>INDEX (antes de iniciar sesión)</label>
									<textarea id="index" name="index"></textarea>
									<div class="row">
									  	<small>
											*Las imágenes deben de ser en formato .png .<br>
											**Al hacer click en la imagen se mostrará el tamaño original de esta.
										</small>	
									</div>
									<div class="row">
										<div class="form-group">
											<a href="../../images/logo.png" target="_blank" style="display: inline-block; text-align: left;">
												<img src="../../images/logo.png" style="width: 30%;">
											</a>
											<label class="col-sm-2 control-label">Logo</label>
											<div class="col-sm-4">
												<input id="logo" name="logo" type="file">
											</div>
										</div>
									</div>
									<br><br>
									<div class="row">
										<div class="form-group">
											<a href="../../images/watermark.png" target="_blank" style="display: inline-block; text-align: left;">
												<img src="../../images/watermark.png" style="width: 50%;">
											</a>
											<label class="col-sm-2 control-label">Watermark</label>
											<div class="col-sm-4">
												<input id="watermark" name="watermark" type="file">
											</div>
										</div>
									</div>
									<br><br>
									<div class="row">
										<div class="form-group">
											<label class="col-sm-2 control-label">HEX</label>
											<div class="col-sm-10">
												<input id="hex" name="hex" type="text" class="form-control">
											</div>
										</div>
									</div>
								</div>
								<div class="card-action">
									<?php   
										$params = array("link"    =>  "#",
						                                "title"   =>  " Guardar",
						                                "classes" =>  "guardar");
				                      echo $common->printButton($module, "guardar", $params);
				                    ?>
								</div>
							</form>
						</div>
						<!-- END: CONTENIDO DE LA PÁGINA -->
					</div>
				</div>
				<?php echo $common->printFooter();?>
			</div>
		</div>
		<!--   Core JS Files   -->
		<script src="<?php echo $ruta?>assets/js/core/jquery.3.2.1.min.js"></script>
		<script src="<?php echo $ruta?>assets/js/core/popper.min.js"></script>
		<script src="<?php echo $ruta?>assets/js/core/bootstrap.min.js"></script>

		<!-- jQuery UI -->
		<script src="<?php echo $ruta?>assets/js/plugin/jquery-ui-1.12.1.custom/jquery-ui.min.js"></script>
		<script src="<?php echo $ruta?>assets/js/plugin/jquery-ui-touch-punch/jquery.ui.touch-punch.min.js"></script>

		<!-- jQuery Scrollbar -->
		<script src="<?php echo $ruta?>assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>

		<!-- Moment JS -->
		<script src="<?php echo $ruta?>assets/js/plugin/moment/moment.min.js"></script>

		<!-- Chart JS -->
		<script src="<?php echo $ruta?>assets/js/plugin/chart.js/chart.min.js"></script>

		<!-- jQuery Sparkline -->
		<script src="<?php echo $ruta?>assets/js/plugin/jquery.sparkline/jquery.sparkline.min.js"></script>

		<!-- Chart Circle -->
		<script src="<?php echo $ruta?>assets/js/plugin/chart-circle/circles.min.js"></script>

		<!-- Datatables -->
		<script src="<?php echo $ruta?>assets/js/plugin/datatables/datatables.min.js"></script>

		<!-- Bootstrap Notify -->
		<script src="<?php echo $ruta?>assets/js/plugin/bootstrap-notify/bootstrap-notify.min.js"></script>

		<!-- Bootstrap Toggle -->
		<script src="<?php echo $ruta?>assets/js/plugin/bootstrap-toggle/bootstrap-toggle.min.js"></script>

		<!-- jQuery Vector Maps -->
		<script src="<?php echo $ruta?>assets/js/plugin/jqvmap/jquery.vmap.min.js"></script>
		<script src="<?php echo $ruta?>assets/js/plugin/jqvmap/maps/jquery.vmap.world.js"></script>

		<!-- Google Maps Plugin -->
		<script src="<?php echo $ruta?>assets/js/plugin/gmaps/gmaps.js"></script>

		<!-- Dropzone -->
		<script src="<?php echo $ruta?>assets/js/plugin/dropzone/dropzone.min.js"></script>

		<!-- Fullcalendar -->
		<script src="<?php echo $ruta?>assets/js/plugin/fullcalendar/fullcalendar.min.js"></script>

		<!-- DateTimePicker -->
		<script src="<?php echo $ruta?>assets/js/plugin/datepicker/bootstrap-datetimepicker.min.js"></script>

		<!-- Bootstrap Tagsinput -->
		<script src="<?php echo $ruta?>assets/js/plugin/bootstrap-tagsinput/bootstrap-tagsinput.min.js"></script>

		<!-- Bootstrap Wizard -->
		<script src="<?php echo $ruta?>assets/js/plugin/bootstrap-wizard/bootstrapwizard.js"></script>

		<!-- jQuery Validation -->
		<script src="<?php echo $ruta?>assets/js/plugin/jquery.validate/jquery.validate.min.js"></script>

		<!-- Summernote -->
		<script src="<?php echo $ruta?>assets/js/plugin/summernote/summernote-bs4.min.js"></script>

		<!-- Select2 -->
		<script src="<?php echo $ruta?>assets/js/plugin/select2/select2.full.min.js"></script>

		<!-- Sweet Alert -->
		<script src="<?php echo $ruta?>assets/js/plugin/sweetalert/sweetalert.min.js"></script>

		<!-- Millenium JS -->
		<script src="<?php echo $ruta?>assets/js/millenium.min.js"></script>

		<!-- Bootbox -->
    	<script src="<?php echo $ruta;?>assets/js/bootbox.min.js" type="text/javascript"></script>

		<!-- Common -->
		<script src="<?php echo $ruta?>assets/js/common.js"></script>

		<!-- JS -->
	    <?php 
	      if (count($js) > 0) {
	        foreach ($js as $clave => $valor) {
	          echo '<script type="text/javascript" src="js/'.$valor.'.js"></script>';
	        }
	      }
	    ?>

	</body>
</html>