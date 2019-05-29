<?php header("Content-type: text/css"); 

$url = explode("/sky", $_SERVER["REQUEST_URI"]);
$url = explode("/", $url[1]);

//$url = explode("/", $_SERVER["REQUEST_URI"]);

$ruta = "";
$file=$url[count($url)-1];
for ($i=1; $i < (count($url) - 1); $i++){
  $ruta .= "../";
}

include_once("../../include/Common.php");
try{
	$sql = "SELECT *
			FROM configuracion
			WHERE variable = 'hex'
			LIMIT 0, 1";

	$consulta = $common->_conexion->prepare($sql);
	$consulta->execute();
	$contacto = $consulta->fetch(PDO::FETCH_ASSOC);
	$color = $contacto['contenido'];



}catch(PDOException $e){
	die($e->getMessage());
}


?>

.navbar-header {
	background:<?=$color?> !important;
}

.sidebar .nav > .nav-item li.active a p {
	color: <?=$color?> !important;
}

.sidebar .nav.nav-secondary > .nav-item li.active a i {
	color: <?=$color?> !important;	
}

.sidebar .nav.nav-secondary > .nav-item a:hover i, .sidebar .nav.nav-secondary > .nav-item a:focus i, .sidebar .nav.nav-secondary > .nav-item a[data-toggle=collapse][aria-expanded=true] i, .sidebar[data-background-color="white"] .nav.nav-secondary > .nav-item a:hover i, .sidebar[data-background-color="white"] .nav.nav-secondary > .nav-item a:focus i, .sidebar[data-background-color="white"] .nav.nav-secondary > .nav-item a[data-toggle=collapse][aria-expanded=true] i {
	color: <?=$color?> !important;
}

.bg-secondary-gradient {
	background: <?=$color?>!important;
}

.btn-secondary {
	background-color: <?=$color?>!important;
	border-color: <?=$color?>!important;
}

.sidebar .nav.nav-secondary > .nav-item.active a:before, .sidebar[data-background-color="white"] .nav.nav-secondary > .nav-item.active a:before {
	background-color: <?=$color?>!important;
}

.btn-secondary:disabled, .btn-secondary:focus, .btn-secondary:hover {
	background-color: <?=$color?>!important;
	border-color: <?=$color?>!important;
	opacity: .95;
}

