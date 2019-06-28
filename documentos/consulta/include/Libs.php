<?php

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
$module = 3;

class Libs extends Common {
	
	
	/*
	 * @author: Cynthia Castillo
	 * 
	 * @param '$id'		int. 	ID de perfil de usuario
	 * 
	 * Metodo que imprime la tabla de permisos de un perfil de usuario en base a su id
	 */
	function showRecord() {
		$json = array();
		$json['error'] = false;
		$json['msg'] = "Experimentamos fallas técnicas.";
		try{
			$sql = "SELECT *
					FROM contacto
					LIMIT 0, 1";

			$consulta = $this->_conexion->prepare($sql);
			$consulta->execute();
			$contacto = $consulta->fetch(PDO::FETCH_ASSOC);
			$json['contacto'] = $contacto['contenido'];
		
		}catch(PDOException $e){
			die($e->getMessage());
		}
		echo json_encode($json);
	}

	function saveRecord() {
		global $ruta;
		$json = array();
		$json["msg"] = "Todos los campos son obligatorios.";
		$json["error"] = false;
		$json["focus"] = "";

		$obligatorios = array("contacto");

		//VALIDACIÓN
		foreach($_POST as $clave=>$valor){
			if(!$json["error"]){
				if($this->is_empty(trim($valor)) && in_array($clave, $obligatorios)) {
					$json["error"] = true;
					$json["focus"] = $clave;
					$json['msg'] = "El campo ". lcfirst($clave)." es obligatorio.";	
				}
			}
		}

		if(!$json["error"]) {
			$db = $this->_conexion;
			$db->beginTransaction();

			$values = array($_POST['contacto'], 1);
				$sql = "UPDATE contacto SET contenido = ?
						WHERE con_id = ?";

			$consulta = $db->prepare($sql);

			try {
				$consulta->execute($values);

			} catch(PDOException $e) {
				$db->rollBack();
				die($e->getMessage());
			}

			$db->commit();
			$json['msg'] = 'Contacto se guardó con éxito.';
		}

		echo json_encode($json);
	}

	function search() {
		global $ruta;
		$json = array();
		$json['error'] = false;
		$json['msg'] = '';
		$json['table'] = '';
		$json['num_res'] = 0;

		$ruta = substr($ruta, 0, -3);
		$ruta_global = $ruta;


		if(isset($_POST['term'])) {

			if(!isset($_SESSION)){
				@session_start();
			}

			$sup_id = $_SESSION["sky"]["userprofile"];
			$siu_id = $_SESSION["sky"]["userid"];
			$cli_id = $_SESSION["sky"]["cli_id"];

			//Verifica página a mostrar
			if(isset($_POST['pag']) && $_POST['pag'] > 0 ) {
				$pagina = $_POST['pag'];
			} else {
				$pagina = 1;
			}

			//Verifica la cantidad de resultados para mostrar
			if(isset($_POST['n']) && ($_POST['n'] == 20 || $_POST['n'] == 50 || $_POST['n'] == 100 || $_POST['n'] == "10000")) {
				$cantidad = $_POST['n'];
			} else {
				$cantidad = 20;
			}

			$order = ' ORDER BY nombre ASC ';
			if(isset($_POST['order'])) {
				switch ($_POST['order']) {
					case 'az':
						$order = ' ORDER BY nombre ASC ';
						break;
					case 'za':
						$order = ' ORDER BY nombre DESC ';
						break;
					case 'fd':
						$order = ' ORDER BY date DESC ';
						break;
					case 'fa':
						$order = ' ORDER BY date ASC ';
						break;			
				}
			}

			$where = '';
			$values = array();

			//Revisamos si es de algun cliente en específico
			if($sup_id != 1) {
				$where .= ' AND cli_id = '.$cli_id.' ';
			}

			//Buscamos por categoría
			if(isset($_POST['car_id']) && $_POST['car_id'] != -1 && !empty($_POST['car_id'])) {
				$where.= " AND car_id = ".$_POST['car_id'];
			}

			//Buscamos el término
			if($_POST['action'] == 'contexto') {
				$where .= ' AND contenido LIKE "%'.$_POST['term'].'%" ';
			} else {
				$where .= ' AND nombre LIKE "%'.$_POST['term'].'%" ';
			}

			$db = $this->_conexion;
			$sql_num = 'SELECT COUNT(documentos.doc_id) as Total_Registros
						FROM documentos
						LEFT JOIN documentos_detalles ON documentos_detalles.doc_id = documentos.doc_id
						WHERE 1 = 1 '.$where;
			$consulta_num = $db->prepare($sql_num);

			//echo $sql_num;
			
			try {

				$consulta_num->execute($values);
				$row_num = $consulta_num->fetch(PDO::FETCH_ASSOC);

				$json['num_res'] = $row_num['Total_Registros'];

				//Se calcula cuántas páginas son
				$cant_pags = ceil($row_num['Total_Registros'] / $cantidad);

				$json['table'] = '<table class="table">
									<thead>
										<tr>
											<th>Nombre de Documento</th>
											<th>Contexto</th>
											<th>Ruta</th>
											<th>Fecha de Alta</th>
										</tr>
									</thead>';

				if($row_num['Total_Registros'] > 0 && $pagina <= $cant_pags) {
					//Calcula el límite
					$fin = $pagina*$cantidad;
					$inicio = $fin - $cantidad;
					$limit = " LIMIT ".$inicio.",".$cantidad;
					$json['limit'] = $limit;

					$n = 0;

					$sql = "SELECT *
							FROM documentos
							LEFT JOIN documentos_detalles ON documentos_detalles.doc_id = documentos.doc_id
							WHERE 1 = 1 ".
							$where.
							' GROUP BY documentos.doc_id '.
							$order.
							$limit;				

					$json['sql'] = $sql;

					$consulta = $db->prepare($sql);

					try {
						
						$consulta->execute($values);
						$documentos = $consulta->fetchAll(PDO::FETCH_ASSOC);


						//revisamos prefijo de cliente
						//if($sup_id != 1) {
							$sql_cl = "SELECT cli_id, prefijo FROM clientes ORDER BY prefijo ASC";
							$value_cl = array($cli_id);
							$consulta_cl = $db->prepare($sql_cl);
							$consulta_cl->execute($value_cl);
							$cliente = $consulta_cl->fetch(PDO::FETCH_ASSOC);
						//}

						foreach ($documentos as $documento) {

							//Revisamos si tiene permisos para visualizar este documento
							if($this->hasPermission($documento['car_id'], $documento['cli_id'], 1)) {
								$n++;

								$contexto = '-';
								if($_POST['action'] == 'contexto') {
									$pos = strpos($documento['contenido'], $_POST['term']);

									if($pos <= 50) {
										$contexto = substr($documento['contenido'], 0, 100);
									} else {
										$contexto = substr($documento['contenido'], ($pos - 50), 100);
									}

								}

								$rn = 1;
								$ruta_copy = $documento['ruta'];
								$ruta = str_replace('archivos/', '', $ruta_copy, $rn);
								$documento_name = substr($documento['nombre'], 0, -4);

								$json['table'] .= '<tr class="lnk-doc" data-ruta="'.$ruta_global.$documento['ruta'].$documento['nombre'].'">
													<td>'.$documento_name.'</td>
													<td>'.$contexto.'</td>
													<td>'.$ruta.$documento_name.'</td>
													<td>'.$documento['date'].'</td>
												   </tr>';
							}
							
						}

						$inicio_pags = (ceil($pagina/20) * 20) - 19;
						$max_pags = $inicio_pags + 19;
						$fin_pags = ($max_pags > $cant_pags ? $cant_pags : $max_pags);


						/*PAGINACIÓN*/
						$pag = '<ul class="pagination pg-primary">';

						if($pagina != 1) {
							$pag .= '<li class="page-item"><a href="#" class="page-link" data-pag="'.($pagina-1).'">&laquo;</a></li>';
						}

						for ($i=$inicio_pags; $i <= $fin_pags; $i++) { 
							$pag .= '<li class="page-item '.($i == $pagina ? ' active disabled' : '').'">
										<a  class="page-link '.($i == $pagina ? ' active disabled' : '').'" href="#" data-pag="'.$i.'">'.$i.'</a>
									</li>';
						}

						if($pagina != $cant_pags) {
							$pag .= '<li class="page-item">
										<a  class="page-link" href="#" data-pag="'.($pagina+1).'">
											<span aria-hidden="true">&raquo;</span>
										</a>
									</li>';
						}				

						$pag.=	'</ul>';

						$resultados = '<span class="show-resuilt">Total de Resultados: '.$json['num_res'].'</span>';

						$json['pag'] = $pag.$resultados;

					} catch (PDOException $e) {
						die($e->getMessage());	
					}

				} else {
					$json['table'] = '<h1 class="text-center">No se encontraron resultados</h1>';
				}

							
			} catch (PDOException $e) {
				die($e->getMessage());	
			}			


		} else {
			$json['error'] = false;
			$json['msg'] = 'Favor de ingresar un término a buscar.';
		}

		echo json_encode($json);
	}
	
	
}

if(isset($_REQUEST['accion'])){
	//Se inicializa la clase
	$libs = new Libs;
	switch($_REQUEST['accion']){
		case "showRecord":
			$libs->showRecord();
			break;	
		case "saveRecord":
			$libs->saveRecord();
			break;	
		case "search":
			$libs->search();
			break;		
	}
}

?>