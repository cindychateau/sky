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