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
$module = 10;

class Libs extends Common {

	function printTable() {
		global $module;

		/*
		 * Query principal
		 */
		$sqlQuery = "SELECT *
					 FROM clientes";
		
		//Se prepara la consulta de extración de datos
		$consulta = $this->_conexion->prepare($sqlQuery);

		//echo $sqlQueryFiltered;

		//Se ejecuta la consulta
		try {
			
			$consulta->execute();
			
			//Se imprime la tabla
			$puntero = $consulta->fetchAll(PDO::FETCH_ASSOC);
			
			/*
			* Salida de Datos
			*/
			$data = array();
			$counter = 0;
			
			foreach ($puntero as $row) {
				$counter++;

				/*<div class="form-button-action">
					<button type="button" data-toggle="tooltip" title="" class="btn btn-link btn-primary btn-lg" data-original-title="Edit Task">
						<i class="fa fa-edit"></i>
					</button>
					<button type="button" data-toggle="tooltip" title="" class="btn btn-link btn-danger" data-original-title="Remove">
						<i class="fa fa-times"></i>
					</button>
				</div>*/

				//Botones
				$params_editar = array(	"link"		=>	"cambios.php?id=".$row['cli_id'],
										"title"		=>	"Ver/Editar");
				$btn_editar = $this->printButton($module, "cambios", $params_editar);
				$params_borrar = array(	"title"		=>	"Borrar",
										"classes"	=>	"borrar",
										"data_id"	=>	$row['cli_id'],
										"extras"	=>	"data-name='".$row["nombre"]."'");
				$btn_borrar = $this->printButton($module, "baja", $params_borrar);

				$aRow = array($row["nombre"], $row['prefijo'], '<div class="form-button-action">'.$btn_editar.$btn_borrar.'</div>');
				
				//Se guarda la fila en la matriz principal
				$data[] = $aRow;
			}

			$json = array();
			$json['data'] = $data;

			echo json_encode($json);
		} catch(PDOException $e) {
			die($e->getMessage());
		}
	}
	
	
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
		if(isset($_POST['id'])){
			try{
				$sql = "SELECT *
						FROM clientes
						WHERE cli_id = :valor";

				$consulta = $this->_conexion->prepare($sql);
				$consulta->bindParam(':valor', $_POST['id']);
				$consulta->execute();
				$row = $consulta->fetch(PDO::FETCH_ASSOC);

				if ($consulta->rowCount() > 0) {

					$json['nombre'] = $row['nombre'];
					$json['prefijo'] = $row['prefijo'];
						
				} else {
					$json['error'] = true;
				}

			}catch(PDOException $e){
				die($e->getMessage());
			}
		}
		echo json_encode($json);
	}

	function saveRecord() {
		global $ruta;
		$json = array();
		$json["msg"] = "Todos los campos son obligatorios.";
		$json["error"] = false;
		$json["focus"] = "";

		//VALIDACIÓN
		foreach($_POST as $clave=>$valor){
			if(!$json["error"]){
				if($this->is_empty(trim($valor))) {
					$json["error"] = true;
					$json["focus"] = $clave;
					$json['msg'] = "El campo ". lcfirst($clave)." es obligatorio.";	
				}
			}
		}

		$db = $this->_conexion;
		//Verificamos que el prefijo no esté tomado
		$sql = "SELECT cli_id, prefijo FROM clientes WHERE prefijo = ? ";
		$params = array($_POST['prefijo']);
		$consulta = $db->prepare($sql);
		$consulta->execute($params);
		if($consulta->rowCount()) {
			$prefijo = $consulta->fetch(PDO::FETCH_ASSOC);
			if(isset($_POST['id'])) {
				if($prefijo['cli_id'] != $_POST['id']) {
					$json['error'] = true;
					$json['msg'] = 'El prefijo ingresado ya ha sido registrado anteriormente. Favor de escoger un prefijo único.';
				}
			} else {
				$json['error'] = true;
				$json['msg'] = 'El prefijo ingresado ya ha sido registrado anteriormente. Favor de escoger un prefijo único.';
			}
		}

		if(!$json["error"]) {
			$db->beginTransaction();

			$values = array($_POST['nombre'],
							$_POST['prefijo']);

			if(isset($_POST['id'])) { //UPDATE

				//Revisamos el nombre anterior de la carpeta
				$sql_chk = "SELECT cli_id, prefijo FROM clientes WHERE cli_id = ? ";
				$params = array($_POST['id']);
				$consulta_chk = $db->prepare($sql_chk);
				$consulta_chk->execute($params);
				if($consulta_chk->rowCount()) {
					$prefijo_ant = $consulta_chk->fetch(PDO::FETCH_ASSOC);
					$prefijo_ant = $prefijo_ant['prefijo'];
				}

				$sql = "UPDATE clientes SET nombre = ?,
										    prefijo = ?
						WHERE cli_id = ?";

				$values[] = $_POST['id'];

			} else { //INSERCION
				$sql = "INSERT INTO clientes (nombre,
											  prefijo)
						VALUES( ?, ? )";
			}

			$consulta = $db->prepare($sql);

			try {
				$consulta->execute($values);

				$folder = $ruta.'archivos/'.$_POST['prefijo'];

				if(isset($_POST['id'])) {

					$old_folder = $ruta.'archivos/'.$prefijo_ant; //Ruta de la vieja carpeta
					//Revisamos si existe esa carpeta
					if(!is_dir($old_folder)) { 
						//En caso de NO existir, solo hacemos una carpeta con el nuevo prefijo
						mkdir($folder,0755,true);
					} else {
						//Si SI existe, revisamos si se cambió el prefijo
						if($prefijo_ant != $_POST['prefijo']) {
							rename($old_folder, $folder); //renombramos la carpeta

							//PEND: Cambiar todas las rutas de archivos/carpetas
						}
					}

				} else {
					//Hacemos carpeta del nuevo cliente
					mkdir($folder,0755,true);
				}

			} catch(PDOException $e) {
				$db->rollBack();
				die($e->getMessage());
			}

			$db->commit();
			$json['msg'] = 'El Cliente se guardó con éxito.';
		}

		echo json_encode($json);
	}

	function deleteRecord() {
		global $ruta;

		if(isset($_POST['id'])) {

			$db = $this->_conexion;

			//Datos de carpeta
			$sql_car = 'SELECT car_id, prefijo 
						FROM carpetas 
						JOIN clientes ON clientes.cli_id = carpetas.cli_id
						WHERE clientes.cli_id = ? 
						AND nivel = 0';
			$values_car = array($_POST['id']);
			$consulta_car = $db->prepare($sql_car);
			$consulta_car->execute($values_car);
			$carpetas = $consulta_car->fetchAll(PDO::FETCH_ASSOC);

			//Recorremos todos las carpetas nivel 0
			$db->beginTransaction();
			foreach ($carpetas as $carpeta) {
				try {

					//Eliminamos los registros que hay de esta carpeta de manera recursiva
					$this->deleteFolderRec($carpeta['car_id']);

				} catch(PDOException $e) {
					$db->rollBack();
					die($e->getMessage());
				}
			}

			//Revisamos todos los documentos hijos
			$sql_doc = 'SELECT * FROM documentos WHERE cli_id = ? AND car_id = 0';
			$values_doc = array($_POST['id']);
			$consulta_doc = $db->prepare($sql_doc);
			$consulta_doc->execute($values_doc);
			$documentos = $consulta_doc->fetchAll(PDO::FETCH_ASSOC);		
			foreach ($documentos as $documento) {
				//Eliminamos todos los documentos de la DB
				$consulta_del = $db->prepare("DELETE FROM documentos WHERE doc_id = :valor");
				$consulta_del->bindParam(':valor', $documento['doc_id']);
				$consulta_del->execute();

				//Eliminamos documento "físico"
				$doc_name = $ruta.$documento['ruta'].$documento['nombre'];
				if(file_exists($doc_name)) {
					unlink($doc_name);
				}
				

				//Eliminamos el detalle
				$consulta_del = $db->prepare("DELETE FROM documentos_detalles WHERE doc_id = :valor");
				$consulta_del->bindParam(':valor', $documento['doc_id']);
				$consulta_del->execute();
			}

			//Eliminamos la carpeta completa
			$sql_car = 'SELECT prefijo 
						FROM clientes 
						WHERE cli_id = ?';
			$values_car = array($_POST['id']);
			$consulta_car = $db->prepare($sql_car);
			$consulta_car->execute($values_car);
			$cliente = $consulta_car->fetch(PDO::FETCH_ASSOC);
			$car_name = $ruta.'archivos/'.$cliente['prefijo'];
			if(file_exists($car_name)) {
				rmdir($car_name);
			}

			$consulta_del = $db->prepare("DELETE FROM clientes WHERE cli_id = :valor");
			$consulta_del->bindParam(':valor', $_POST['id']);
			$consulta_del->execute();

			$json['msg'] = 'Carpeta eliminada con éxito.';
			$db->commit();

		} else {
			$json['error'] = true;
			$json['msg'] = 'Favor de elegir un documento válido.';
		}

		echo json_encode($json);
	}

	function deleteFolderRec($car_id) {
		global $ruta;

		$db = $this->_conexion;
		$sql_car = 'SELECT * FROM carpetas WHERE car_id = ?';
		$values_car = array($car_id);
		$consulta_car = $db->prepare($sql_car);
		$consulta_car->execute($values_car);
		$carpeta = $consulta_car->fetch(PDO::FETCH_ASSOC);

		//Eliminamos la carpeta de la DB
		$consulta_del = $db->prepare("DELETE FROM carpetas WHERE car_id = :valor");
		$consulta_del->bindParam(':valor', $carpeta['car_id']);
		$consulta_del->execute();

		//Revisamos todos los documentos hijos
		$sql_doc = 'SELECT * FROM documentos WHERE car_id = ?';
		$values_doc = array($car_id);
		$consulta_doc = $db->prepare($sql_doc);
		$consulta_doc->execute($values_doc);
		$documentos = $consulta_doc->fetchAll(PDO::FETCH_ASSOC);		
		foreach ($documentos as $documento) {
			//Eliminamos todos los documentos de la DB
			$consulta_del = $db->prepare("DELETE FROM documentos WHERE doc_id = :valor");
			$consulta_del->bindParam(':valor', $documento['doc_id']);
			$consulta_del->execute();

			//Eliminamos documento "físico"
			$doc_name = $ruta.$documento['ruta'].$documento['nombre'];
			//if(file_exists($doc_name)) {
				@unlink($doc_name);
			//}
			

			//Eliminamos el detalle
			$consulta_del = $db->prepare("DELETE FROM documentos_detalles WHERE doc_id = :valor");
			$consulta_del->bindParam(':valor', $documento['doc_id']);
			$consulta_del->execute();
		}

		//Por si acaso hay documentos que no están listados en la db
		foreach (scandir($ruta.$carpeta['ruta'].$carpeta['nombre']) as $item) {
	        if ($item != '.' && $item != '..') {
	        	if (!is_dir($ruta.$carpeta['ruta'].$carpeta['nombre'] . DIRECTORY_SEPARATOR . $item)) {
	        		unlink($ruta.$carpeta['ruta'].$carpeta['nombre'] . DIRECTORY_SEPARATOR . $item);
	        	}
	        }

	    }

		//Revisamos todas las carpetas hijas y las eliminamos
		$sql_car2 = 'SELECT * FROM carpetas WHERE nivel = ?';
		$values_car2 = array($car_id);
		$consulta_car2 = $db->prepare($sql_car2);
		$consulta_car2->execute($values_car2);
		$carpetas_hijas = $consulta_car2->fetchAll(PDO::FETCH_ASSOC);

		foreach ($carpetas_hijas as $carpeta_hija) {
			$this->deleteFolderRec($carpeta_hija['car_id']);
		}

		//Eliminamos la carpeta completa
		$car_name = $ruta.$carpeta['ruta'].$carpeta['nombre'];
		if(file_exists($car_name)) {
			rmdir($car_name);
		}

	}
	
	
}

if(isset($_REQUEST['accion'])){
	//Se inicializa la clase
	$libs = new Libs;
	switch($_REQUEST['accion']){
		case "printTable":
			$libs->printTable();
			break;	
		case "showRecord":
			$libs->showRecord();
			break;	
		case "saveRecord":
			$libs->saveRecord();
			break;	
		case "deleteRecord":
			$libs->deleteRecord();
			break;	
	}
}

?>