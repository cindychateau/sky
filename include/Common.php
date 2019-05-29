<?php

include_once($ruta."include/Core.php");

$baseUrl = 'http://'.$_SERVER["SERVER_NAME"]."/sky/";
//$baseUrl = $_SERVER["SERVER_NAME"]."/";
date_default_timezone_set('America/Mexico_City');
class Common extends Core {

	/*
	 * @version: 0.1 2013-04-01
	 */
	public function printUserName() {
		//Se revisa el tipo de usuario.
		if(!isset($_SESSION)){
			@session_start();
		}
		
		//Se prepara la consulta
		return $_SESSION["sky"]["username"];
		//return "Cynthia Castillo";
	}

	/*
	 * @author: Cynthia Castillo
	 * @version: 0.1 2013-12-23
	 */
	public function printNotifications() {
		global $ruta;
		$numtotal = 0;

		try {
			//Seleccionamos todos los tipos distintos de Notificaciones
			$query = "SELECT DISTINCT(SIN_LIGA),
							 SIN_MENSAJE,
							 SIN_COLOR,
							 SIN_ICONO
					  FROM SISTEMA_NOTIFICACIONES
					  WHERE SIN_ESTADO = 0";
			$consulta = $this->_conexion->prepare($query);
			$consulta->execute();
			$puntero = $consulta->fetchAll(PDO::FETCH_ASSOC);
			$notificaciones = "";
			foreach ($puntero as $row) {
				$num_row = 0;
				$sql_us = "SELECT SIN_USUARIOS,
								  SIN_DATE
						   FROM SISTEMA_NOTIFICACIONES 
						   WHERE SIN_LIGA = ? 
						   AND SIN_ESTADO = 0
						   ORDER BY SIN_DATE ASC";
				$val_us = array($row['SIN_LIGA']);
				$consulta_us = $this->_conexion->prepare($sql_us);
				$consulta_us->execute($val_us);
				$puntero_us = $consulta_us->fetchAll(PDO::FETCH_ASSOC);
				foreach ($puntero_us as $row_us) {
					$usuarios = $row_us['SIN_USUARIOS'];
					$usuarios = explode("|", $usuarios);
					if (in_array($_SESSION["sky"]["userid"], $usuarios, true)) {
						$num_row++;
					}
				}

				$fecha = date("d/m/Y H:i",strtotime($row_us["SIN_DATE"]));
				if($num_row > 0) {

					//Verifica si es para desplegar un pago vencido
					if(strpos($row['SIN_LIGA'],'?id=') !== false) {
						$num_row = "";
					}

					$notificaciones.='<a class="visto" href="'.$ruta.$row['SIN_LIGA'].'" data-liga="'.$row['SIN_LIGA'].'" data-ruta="'.$ruta.'">
											<div class="notif-icon notif-'.$row['SIN_COLOR'].'"> <i class="fa '.$row['SIN_ICONO'].'"></i> </div>
											<div class="notif-content">
												<span class="block">'.$num_row." ".$row['SIN_MENSAJE'].'</div>
												<span class="time">
													<i class="fa fa-clock-o"></i> '.$fecha.'
												</span>
											</div>
										</a>';	
					$numtotal++;				
				}
				
			}
			
		} catch(PDOException $e) {
			die($e->getMessage());
		}
			
		$notif = ($numtotal == 1 ? "Notificación" : "Notificaciones");

		return '<!--li class="nav-item dropdown hidden-caret submenu">
						<a class="nav-link dropdown-toggle" href="#" id="notifDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<i class="fa fa-bell"></i>
							'.($numtotal > 0 ? '<span class="notification">'.$numtotal.'</span>' : '').'
						</a>
						<ul class="dropdown-menu notif-box animated fadeIn" aria-labelledby="notifDropdown">
							'.($numtotal > 0 ? '<li>
													<div class="dropdown-title">'.$numtotal.' '.$notif.'</div>
												</li>
												<!-- BG: NOTIFICACIONES -->
												<li>
													<div class="notif-center">
														'.$notificaciones.'
													</div>
												</li>
												<!-- END: NOTIFICACIONES -->' : 
												'<li class="text-center">
													<a class="see-all" href="javascript:void(0);">No hay notificaciones </a>
												</li>').'
							
						</ul>
					</li-->';
		
	}
	
	public function printLateralMenu() {
		//NOTA: Se pone clase "active" a la sección en la que estamos		
		echo '<ul>
				<li>
					<a href="index.html">
						<i class="fa fa-tachometer fa-fw"></i> <span class="menu-text">Dashboard</span>
					</a>					
				</li>
				<li class="has-sub">
					<a href="javascript:;" class="">
					<i class="fa fa-bookmark-o fa-fw"></i> <span class="menu-text">UI Features</span>
					<span class="arrow"></span>
					</a>
					<ul class="sub">
						<li><a class="" href="elements.html"><span class="sub-menu-text">Elements</span></a></li><li><a class="" href="notifications.html"><span class="sub-menu-text">Hubspot Notifications</span></a></li>
						<li><a class="" href="buttons_icons.html"><span class="sub-menu-text">Buttons & Icons</span></a></li>
						<li><a class="" href="sliders_progress.html"><span class="sub-menu-text">Sliders & Progress</span></a></li>
						<li><a class="" href="typography.html"><span class="sub-menu-text">Typography</span></a></li>
						<li><a class="" href="tabs_accordions.html"><span class="sub-menu-text">Tabs & Accordions</span></a></li>
						<li><a class="" href="treeview.html"><span class="sub-menu-text">Treeview</span></a></li>
						<li><a class="" href="nestable_lisInDbts.html"><span class="sub-menu-text">Nestable Lists</span></a></li>
						<li class="has-sub-sub">
							<a href="javascript:;" class=""><span class="sub-menu-text">Third Level Menu</span>
							<span class="arrow"></span>
							</a>
							<ul class="sub-sub">
								<li><a class="" href="#"><span class="sub-sub-menu-text">Item 1</span></a></li>
								<li><a class="" href="#"><span class="sub-sub-menu-text">Item 2</span></a></li>
							</ul>
						</li>
					</ul>
				</li>
				<li><a class="" href="frontend_theme/index.html" target="_blank"><i class="fa fa-desktop fa-fw"></i> <span class="menu-text">Frontend Theme</span></a></li><li><a class="" href="inbox.html"><i class="fa fa-envelope-o fa-fw"></i> <span class="menu-text">Inbox</span></a></li>
				<li class="has-sub">
					<a href="javascript:;" class="">
					<i class="fa fa-table fa-fw"></i> <span class="menu-text">Tables</span>
					<span class="arrow"></span>
					</a>
					<ul class="sub">
						<li><a class="" href="simple_table.html"><span class="sub-menu-text">Simple Tables</span></a></li>
						<li><a class="" href="dynamic_tables.html"><span class="sub-menu-text">Dynamic Tables</span></a></li>
						<li><a class="" href="jqgrid_plugin.html"><span class="sub-menu-text">jqGrid Plugin</span></a></li>
					</ul>
				</li>
				<li class="has-sub">
					<a href="javascript:;" class="">
					<i class="fa fa-pencil-square-o fa-fw"></i> <span class="menu-text">Form Elements</span>
					<span class="arrow"></span>
					</a>
					<ul class="sub">
						<li><a class="" href="forms.html"><span class="sub-menu-text">Forms</span></a></li>
						<li><a class="" href="wizards_validations.html"><span class="sub-menu-text">Wizards & Validations</span></a></li>
						<li><a class="" href="rich_text_editors.html"><span class="sub-menu-text">Rich Text Editors</span></a></li>
						
						<li><a class="" href="dropzone_file_upload.html"><span class="sub-menu-text">Dropzone File Upload</span></a></li>
					</ul>
				</li>
				<li><a class="" href="widgets_box.html"><i class="fa fa-th-large fa-fw"></i> <span class="menu-text">Widgets & Box</span></a></li>
				<li class="has-sub">
					<a href="javascript:;" class="">
					<i class="fa fa-bar-chart-o fa-fw"></i> <span class="menu-text">Visual Charts</span>
					<span class="arrow"></span>
					</a>
					<ul class="sub">
						<li><a class="" href="flot_charts.html"><span class="sub-menu-text">Flot Charts</span></a></li>
						<li><a class="" href="xcharts.html"><span class="sub-menu-text">xCharts</span></a></li>
						
						<li><a class="" href="others.html"><span class="sub-menu-text">Others</span></a></li>
					</ul>
				</li>
				<li class="has-sub">
					<a href="javascript:;" class="">
					<i class="fa fa-columns fa-fw"></i> <span class="menu-text">Layouts</span>
					<span class="arrow"></span>
					</a>
					<ul class="sub">
						<li><a class="" href="mini_sidebar.html"><span class="sub-menu-text">Mini Sidebar</span></a></li>
						<li><a class="" href="fixed_header.html"><span class="sub-menu-text">Fixed Header</span></a></li>
						
						<li><a class="" href="fixed_header_sidebar.html"><span class="sub-menu-text">Fixed Header & Sidebar</span></a></li>
					</ul>
				</li>
				<li><a class="" href="calendar.html"><i class="fa fa-calendar fa-fw"></i> 
					<span class="menu-text">Calendar 
						<span class="tooltip-error pull-right" title="" data-original-title="3 New Events">
							<span class="label label-success">New</span>
						</span>
					</span>
					</a>
				</li>
				<li class="has-sub">
					<a href="javascript:;" class="">
					<i class="fa fa-map-marker fa-fw"></i> <span class="menu-text">Maps</span>
					<span class="arrow"></span>
					</a>
					<ul class="sub">
						<li><a class="" href="google_maps.html"><span class="sub-menu-text">Google Maps</span></a></li>
						<li><a class="" href="vector_maps.html"><span class="sub-menu-text">Vector Maps</span></a></li>
					</ul>
				</li>
				<li><a class="" href="gallery.html"><i class="fa fa-picture-o fa-fw"></i> <span class="menu-text">Gallery</span></a></li>
				<li class="has-sub">
					<a href="javascript:;" class="">
					<i class="fa fa-file-text fa-fw"></i> <span class="menu-text">More Pages</span>
					<span class="arrow"></span>
					</a>
					<ul class="sub">
						<li><a class="" href="login.html"><span class="sub-menu-text">Login & Register Option 1</span></a></li><li><a class="" href="login_bg.html"><span class="sub-menu-text">Login & Register Option 2</span></a></li>
						<li><a class="" href="user_profile.html"><span class="sub-menu-text">User profile</span></a></li>
						
						<li><a class="" href="chats.html"><span class="sub-menu-text">Chats</span></a></li>
						<li><a class="" href="todo_timeline.html"><span class="sub-menu-text">Todo & Timeline</span></a></li>
						<li><a class="" href="address_book.html"><span class="sub-menu-text">Address Book</span></a></li>
						
						<li><a class="" href="pricing.html"><span class="sub-menu-text">Pricing</span></a></li>
						<li><a class="" href="invoice.html"><span class="sub-menu-text">Invoice</span></a></li>
						<li><a class="" href="orders.html"><span class="sub-menu-text">Orders</span></a></li>
					</ul>
				</li>
				<li class="has-sub active">
					<a href="javascript:;" class="">
					<i class="fa fa-briefcase fa-fw"></i> <span class="menu-text">Other Pages <span class="badge pull-right">9</span></span>
					<span class="arrow open"></span>
					<span class="selected"></span>
					</a>
					<ul class="sub">
						<li><a class="" href="search_results.html"><span class="sub-menu-text">Search Results</span></a></li>
						<li><a class="" href="email_templates.html"><span class="sub-menu-text">Email Templates</span></a></li>
						
						<li><a class="" href="error_404.html"><span class="sub-menu-text">Error 404 Option 1</span></a></li><li><a class="" href="error_404_2.html"><span class="sub-menu-text">Error 404 Option 2</span></a></li><li><a class="" href="error_404_3.html"><span class="sub-menu-text">Error 404 Option 3</span></a></li>
						<li><a class="" href="error_500.html"><span class="sub-menu-text">Error 500 Option 1</span></a></li><li><a class="" href="error_500_2.html"><span class="sub-menu-text">Error 500 Option 2</span></a></li>
						<li><a class="" href="faq.html"><span class="sub-menu-text">FAQ</span></a></li>
						<li class="current"><a class="" href="blank_page.html"><span class="sub-menu-text">Blank Page</span></a></li>
					</ul>
				</li>
			</ul>';
	}

	public function printBreadcrumbs($modulo = 0) {
		global $ruta;
		$breadcrumbs = array();
		
		try {
			$query = "SELECT * FROM SISTEMA_MODULOS WHERE SIM_ID = :valor";
			$consulta = $this->_conexion->prepare($query);
			$url_principal = "";
			$i = 0;
			while ($modulo > 0) {
				$consulta->bindParam(":valor",$modulo);
				$consulta->execute();
				$result = $consulta->fetch();
				$modulo = $result['SIM_NIVEL'];
				if($i == 0) {
					$url_principal = $ruta.$result['SIM_URL'];
				}
				$breadcrumbs[] = array('href'=> $url_principal,
								'titulo'=>$result['SIM_NOMBRE'],
								'icon'=>'fa '.$result['SIM_IMAGEN'],
								'class'=>'nav-item',
								'id'=>$result['SIM_ID']);

				$i++;

			}
			$breadcrumbs[] = array('href'=>$ruta.'home.php',
								'titulo'=>'',
								'icon'=>'flaticon-home',
								'class'=>'nav-home',
								'id'=>0);	
		} catch(PDOException $e) {
			die($e->getMessage());
		}
		//sort($breadcrumbs);
		$breadcrumbs = $this->array_sort($breadcrumbs, 'id', SORT_ASC);
		echo '<ul class="breadcrumbs">';

		$n = 0;
		foreach ($breadcrumbs as $breadcrumb) {
			if($n != 0) {
				echo '<li class="separator">
							<i class="flaticon-right-arrow"></i>
						</li>';
			}

			$n++;
			echo '<li class="'.$breadcrumb['class'].'">
						<i class="'.$breadcrumb['icon'].'"></i>
				  		<a href="'.$breadcrumb['href'].'">'.$breadcrumb['titulo'].'</a>
				  </li>';
		}
		echo '</ul>';
		/*echo '<ul class="breadcrumb">
				<li>
					<i class="fa fa-home"></i>
					<a href="'.$ruta.'home.php">Home</a>
				</li>
				<li>
					<a href="#">Other Pages</a>
				</li>
				<li>Panel de Control</li>
			</ul>';*/
	}

	public function array_sort($array, $on, $order=SORT_ASC) {
	    $new_array = array();
	    $sortable_array = array();

	    if (count($array) > 0) {
	        foreach ($array as $k => $v) {
	            if (is_array($v)) {
	                foreach ($v as $k2 => $v2) {
	                    if ($k2 == $on) {
	                        $sortable_array[$k] = $v2;
	                    }
	                }
	            } else {
	                $sortable_array[$k] = $v;
	            }
	        }

	        switch ($order) {
	            case SORT_ASC:
	                asort($sortable_array);
	            break;
	            case SORT_DESC:
	                arsort($sortable_array);
	            break;
	        }

	        foreach ($sortable_array as $k => $v) {
	            $new_array[$k] = $array[$k];
	        }
	    }

	    return $new_array;
	}

	public function printHeader() {
		global $ruta;
		if(!isset($_SESSION)){
			@session_start();
		}
		echo '<div class="container-fluid">
						<ul class="navbar-nav topbar-nav ml-md-auto align-items-center">
							'.$this->printNotifications().'
							<li class="nav-item dropdown hidden-caret">
								<a class="dropdown-toggle profile-pic" data-toggle="dropdown" href="#" aria-expanded="false">
									<div class="avatar-sm">
										<img src="'.$ruta.'images/user1.png" alt="Sky Consulting" class="avatar-img rounded-circle">
									</div>
								</a>
								<ul class="dropdown-menu dropdown-user animated fadeIn">
									<div class="dropdown-user-scroll scrollbar-outer">
										<li>
											<div class="user-box">
												<div class="u-text">
													<h4>'.$_SESSION['sky']['username'].'</h4>
													<p class="text-muted">'.$_SESSION['sky']['email'].'</p><a href="'.$ruta.'perfil" class="btn btn-xs btn-secondary btn-sm">Cambiar Contraseña</a>
												</div>
											</div>
										</li>
										<li>
											<div class="dropdown-divider"></div>
											<a class="dropdown-item" href="'.$ruta.'include/Login.php?accion=logout">Cerrar Sesión</a>
										</li>
									</div>
								</ul>
							</li>
						</ul>
					</div>';
	}

	public function printUserInfo() {
		global $ruta;
		if(!isset($_SESSION)){
			@session_start();
		}

		//Verificamos tipo de perfil
		$db = $this->_conexion;
		$sql = "SELECT SUP_NOMBRE FROM SISTEMA_USUARIO_PERFIL WHERE SUP_ID = :valor";
		$consulta = $db->prepare($sql);
		$consulta->bindParam(':valor', $_SESSION['sky']['userprofile']);
		$consulta->execute();
		if ($consulta->rowCount() > 0){
			$perfil = $consulta->fetch(PDO::FETCH_ASSOC);
			$perfil = $perfil['SUP_NOMBRE'];
		}

		echo '<div class="user">
				<div class="avatar-sm float-left mr-2">
					<img src="'.$ruta.'images/user.png" alt="..." class="avatar-img rounded-circle">
				</div>
				<div class="info">
					<a data-toggle="collapse" href="#collapseExample" aria-expanded="true">
						<span>
							'.$_SESSION['sky']['username'].'
							<span class="user-level">'.$perfil.'</span>
							<span class="caret"></span>
						</span>
					</a>
					<div class="clearfix"></div>

					<div class="collapse in" id="collapseExample">
						<ul class="nav">
							<li>
								<a href="'.$ruta.'perfil">
									<span class="link-collapse">Cambiar Contraseña</span>
								</a>
							</li>
							<li>
								<a href="'.$ruta.'include/Login.php?accion=logout">
									<span class="link-collapse">Cerrar Sesión</span>
								</a>
							</li>
						</ul>
					</div>
				</div>
			</div>';



	}
	/*
	 * Print Menu
	 * @version: 0.1 2013-12-27
	 */
	public function printMenu($actual = "", $padre = ""){
		global $ruta;
		//Se revisa el tipo de usuario.
		if(!isset($_SESSION)){
			@session_start();
		}
		$query = "SELECT * FROM SISTEMA_MODULOS ORDER BY SIM_ORDEN ASC";
		$consulta = $this->_conexion->prepare($query);
		try {
			$consulta->execute();
			$modulos = $consulta->fetchAll();
		} catch(PDOException $e) {
			die($e->getMessage());
		}
		
		foreach ($modulos as $modulo) {
			$tree[$modulo['SIM_ID']] = $modulo;
			$tree[$modulo['SIM_ID']]['children'] = array();
		}

		foreach ($tree as $key => &$leaf) {
			$parent = isset($tree[$key]['SIM_NIVEL'])?$tree[$key]['SIM_NIVEL']:array();
			if(isset($leaf['SIM_NIVEL'])) {
				$tree[$parent]['children'][] = &$tree[$key];
			}
		}

		$permisos = self::getPermissions();
		
		self::printChildren($tree[0]['children'],$permisos,$actual, $padre);

	}

	function printChildren($children, $permisos, $actual = "", $padre = "" ,$nivel = 0) {
		global $baseUrl;
		//die(print_r($children));
		echo '<ul class="nav nav-'.($nivel > 0? "collapse" :"secondary").'">';
		foreach ($children as $child) {
			//print_r($child);
			if(array_key_exists($child['SIM_ID'], $permisos) && is_array($child['children']) && count($child['children'])) {
				echo '<li class="nav-item '.($padre == $child['SIM_ID'] ? ' submenu' : '').'">
						<a data-toggle="collapse" href="#module-'.$child['SIM_ID'].'">
							<i class="fas '.(empty($child['SIM_IMAGEN'])?"":$child['SIM_IMAGEN']).'"></i>
							<p>'.$child['SIM_NOMBRE'].'</p>
							<span class="caret"></span>
						</a>
						<div class="collapse '.($padre == $child['SIM_ID'] ? 'show' : '').'" id="module-'.$child['SIM_ID'].'">';
				self::printChildren($child['children'],$permisos,$actual,($nivel+1));
				echo('</div></li>');
			}else if(array_key_exists($child['SIM_ID'], $permisos)) {
				echo '<li class="'.($nivel == 0? "nav-item" : "").' '.($actual == $child['SIM_ID'] ? ' active' : '').'">
							<a href="'.$baseUrl.$child['SIM_URL'].'">
								<i class="fas '.(empty($child['SIM_IMAGEN'])?"":$child['SIM_IMAGEN']).'"></i>
								<p>'.$child['SIM_NOMBRE'].'</p>
							</a>
					  </li>';
			}
		}
		echo '</ul>';
	}

	//Se crea el metodo que indica si una cadena esta vacia o no
	public function is_empty($string){
		//Se limpian los espacios en blanco de la cadena
		$string=trim($string);
		//Se declara la variable de control
		$is_empty = true;
		//Se verifica si la cadena trae contenido
		if(strlen($string)!=0) $is_empty=false;
		//Se devuelve el contenido de la variable de control
		return $is_empty;
	}

	function printFooter() {
		global $ruta;
		echo '<footer class="footer">
					<div class="container-fluid">
						<nav class="pull-left">
							<ul class="nav">
								<li class="nav-item">
									<a class="nav-link" href="'.$ruta.'contacto.php">
										Contacto
									</a>
								</li>
							</ul>
						</nav>
						<div class="copyright ml-auto">
							<a target="_blank" href="http://www.sky-cp.mx/">Sky Consulting Partners</a>
						</div>
					</div>
				</footer>';
	}

}

$common = new Common();

try{
	$sql = "SELECT *
			FROM configuracion
			WHERE variable = 'title'
			LIMIT 0, 1";

	$consulta = $common->_conexion->prepare($sql);
	$consulta->execute();
	$title = $consulta->fetch(PDO::FETCH_ASSOC);
	$title = $title['contenido'];

}catch(PDOException $e){
	die($e->getMessage());
}

//Se definen las constante del administrador
define("TITLE_MAIN", $title);


?>