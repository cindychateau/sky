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
					FROM configuracion
					WHERE variable = 'home'
					LIMIT 0, 1";

			$consulta = $this->_conexion->prepare($sql);
			$consulta->execute();
			$contacto = $consulta->fetch(PDO::FETCH_ASSOC);
			$json['contacto'] = $contacto['contenido'];

			$sql = "SELECT *
					FROM configuracion
					WHERE variable = 'hex'
					LIMIT 0, 1";

			$consulta = $this->_conexion->prepare($sql);
			$consulta->execute();
			$contacto = $consulta->fetch(PDO::FETCH_ASSOC);
			$json['hex'] = $contacto['contenido'];
		
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

			$values = array($_POST['contacto']);
			$sql = "UPDATE configuracion SET contenido = ?
						WHERE variable = 'home'";

			$consulta = $db->prepare($sql);

			try {
				$consulta->execute($values);

			} catch(PDOException $e) {
				$db->rollBack();
				die($e->getMessage());
			}

			if(isset($_FILES['logo']['name']) && $_FILES['logo']['name'] != "" && !$json['error']) {
				$allowed =  array('png','PNG');
				$filename = $_FILES['logo']['name'];
				$ext = pathinfo($filename, PATHINFO_EXTENSION);
				if(!in_array($ext,$allowed) ) {
				   	$json["error"] = true;
					$json["msg"] = "Favor de seleccionar una imagen con la extensión correcta para el Logo.";
					$json["focus"] = "logo";
				} else {
					$pathFoto = "../../../images/logo.png";

					if (file_exists($pathFoto)) {
						unlink($pathFoto);
					}

					move_uploaded_file($_FILES["logo"]["tmp_name"], $pathFoto);

					}
			}

			if(isset($_FILES['watermark']['name']) && $_FILES['watermark']['name'] != "" && !$json['error']) {
				$allowed =  array('png','PNG');
				$filename = $_FILES['watermark']['name'];
				$ext = pathinfo($filename, PATHINFO_EXTENSION);
				if(!in_array($ext,$allowed) ) {
				   	$json["error"] = true;
					$json["msg"] = "Favor de seleccionar una imagen con la extensión correcta para el Logo.";
					$json["focus"] = "watermark";
				} else {
					$pathFoto = "../../../images/watermark.png";

					if (file_exists($pathFoto)) {
						unlink($pathFoto);
					}

					move_uploaded_file($_FILES["watermark"]["tmp_name"], $pathFoto);

					}
			}

			if(isset($_POST['hex'])) {
				$values = array($_POST['hex']);
				$sql = "UPDATE configuracion SET contenido = ?
							WHERE variable = 'hex'";

				$consulta = $db->prepare($sql);

				try {
					$consulta->execute($values);

				} catch(PDOException $e) {
					$db->rollBack();
					die($e->getMessage());
				}
			}

			$db->commit();
			$json['msg'] = 'Configuración se guardó con éxito.';
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
	}
}

?>