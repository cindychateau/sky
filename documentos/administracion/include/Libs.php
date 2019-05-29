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
$module = 5;

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

		if(!$json["error"]) {
			$db = $this->_conexion;
			$db->beginTransaction();

			$values = array($_POST['nombre'],
							$_POST['prefijo']);

			if(isset($_POST['id'])) { //UPDATE
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

			} catch(PDOException $e) {
				$db->rollBack();
				die($e->getMessage());
			}

			$db->commit();
			$json['msg'] = 'El Cliente se guardó con éxito.';
		}

		echo json_encode($json);
	}

	function pruebaPDF() {
		header('Content-type: text/html; charset=utf-8');
		mb_internal_encoding('UTF-8');
		if(isset($_FILES['pdf']['name']) && $_FILES['pdf']['name'] != "") {
			$ruta = "doc/";
			/*$filename = $_FILES['pdf']['name'];
			$ext = pathinfo($filename, PATHINFO_EXTENSION);
			$pdf_name = $ruta.$filename;
			if(!move_uploaded_file($_FILES["pdf"]["tmp_name"], $pdf_name)){
				$json['error'] = true;
				$json['msg'] = "Error al subir archivo. Inténtelo de nuevo más tarde.";
			}

			include 'vendor/autoload.php';
			$parser = new \Smalot\PdfParser\Parser();
			$pdf    = $parser->parseFile($pdf_name);
			 
			// Retrieve all pages from the pdf file.
			$db = $this->_conexion;

			$pages  = $pdf->getPages();
 
			// Loop	 over each page to extract text.
			$num_page = 1;
			 
			// Loop over each page to extract text.
			foreach ($pages as $page) {
			    $sql = "INSERT INTO prueba (pagina,
											texto,
											documento)
						VALUES( ?, ?, ? )";	

				$values = array($num_page,
								$page->getText(),
								$pdf_name);

				$consulta = $db->prepare($sql);

				try {
					$consulta->execute($values);

				} catch(PDOException $e) {
					die($e->getMessage());
				}

				$num_page++;
			}*/
		}

		echo json_encode(array('Listo'));
	}

	function pruebaFile() {
		$json = array();
		$json['error'] = false;
		$json['msg'] = '';
		$uploadDir = 'doc/';

		if(sizeof($_FILES) > 0) {

			// Split the string containing the list of file paths into an array 
			$paths = explode("###",rtrim($_POST['paths'],"###"));

			// Loop through files sent
			foreach($_FILES as $key => $current) {
				// Stores full destination path of file on server
				$uploadFile=$uploadDir.rtrim($paths[$key],"/.");
				// Stores containing folder path to check if dir later
				$folder = substr($uploadFile,0,strrpos($uploadFile,"/"));


				
				// Check whether the current entity is an actual file or a folder (With a . for a name)
				if(strlen($current['name'])!=1) {
					// Upload current file
					$folders = explode('/', $folder);
					$ruta_folder = $uploadDir;
					foreach ($folders as $key_f => $fold) {

						if(!is_dir($folder)){
							echo $folder.'<br>';
							mkdir($folder,0755,true);
						}
					}
				}

				// Moves current file to upload destination
				if(move_uploaded_file($current['tmp_name'],$uploadFile))
					echo "The file ".$paths[$key]." has been uploadedn <br>";
				else 
					echo "Error";
			}

		} else {
			$json['error'] = true;
			$json['msg'] = 'No hay archivos a subir.';
		}
	}

	/*
	 * 
	 * Función encargada de revisar el tipo de usuario que es y 
	 * despliega todas las carpetas o solamente de un Cliente específico
	 *
	 */
	function printRoot() {
		$json = array();
		$json['error'] = false;
		$json['msg'] = '';
		$json['arbol'] = '';

		if(!isset($_SESSION)){
			@session_start();
		}

		$sup_id = $_SESSION["sky"]["userprofile"];
		$siu_id = $_SESSION["sky"]["userid"];
		$cli_id = $_SESSION["sky"]["cli_id"];

		//Si es daemon imprimimos todas las carpetas de los clientes
		if($sup_id == 1) {
			
			//Revisamos los clientes
			$db = $this->_conexion;
			$sql_cl = "SELECT cli_id, prefijo FROM clientes ORDER BY prefijo ASC";
			$consulta = $db->prepare($sql_cl);
			$consulta->execute();
			$clientes = $consulta->fetchAll(PDO::FETCH_ASSOC);

			foreach ($clientes as $cliente) {

				if($this->hasPermission(0, $cliente['cli_id'], 1)) {

					//Revisamos si tiene Carpetas hijo
					$sql_car = "SELECT car_id FROM carpetas WHERE nivel = 0 AND cli_id = ? LIMIT 0, 1";
					$values_car = array($cliente['cli_id']);
					$consulta_car = $db->prepare($sql_car);
					$consulta_car->execute($values_car);
					$icon_carpeta = '<i class="fas fa-folder text-info"></i>';
					$class = '';
					if ($consulta_car->rowCount())  {
						$icon_carpeta = '<i id="cl-'.$cliente['cli_id'].'-fa" class="fas fa-folder-plus text-info"></i>';
						$class = 'has_child nc closed';
					}

					//Revisamos si tiene archivos hijo
					$sql_ar = "SELECT doc_id FROM documentos WHERE car_id = 0 AND cli_id = ? LIMIT 0, 1";
					$values_ar = array($cliente['cli_id']);
					$consulta_ar = $db->prepare($sql_ar);
					$consulta_ar->execute($values_ar);
					if ($consulta_ar->rowCount())  {
						$icon_carpeta = '<i id="cl-'.$cliente['cli_id'].'-fa" class="fas fa-folder-plus text-info"></i>';
						$class = 'has_child nc closed';
					}



					$json['arbol'] .= '<div id="cl-'.$cliente['cli_id'].'" class="tree-folder" style="display: block;">				
											<div id="cl-'.$cliente['cli_id'].'-child" class="tree-folder-header tree-cl '.$class.'" data-id="'.$cliente['cli_id'].'">					
												'.$icon_carpeta.'				
												<div class="tree-folder-name">'.$cliente['prefijo'].'</div>				
											</div>				
											<div class="tree-folder-content"></div>				
											<div class="tree-loader" style="display: none;">
												<div class="tree-loading">
													<i class="fa fa-spinner fa-2x fa-spin"></i>
												</div>
											</div>
										</div>';

				}					
			}


		} else {
			//Revisamos si tiene permisos de vista
			if($this->hasPermission(0, $_SESSION["sky"]["cli_id"], 1))  {
				//Si es cliente imprimos todas las carpeta de ese cliente en específico
				$json['arbol'] = $this->getClientRoot($_SESSION["sky"]["cli_id"], 1);
			}
		}


		echo json_encode($json);
	}

	function getClientRoot($cli_id, $root) {
		global $ruta;
		$ruta = substr($ruta, 0, -3);
		$arbol_innner = '';
		$arbol = '';
		$db = $this->_conexion;

		//Revisamos si tiene Carpetas hijo
		$sql_car = "SELECT * FROM carpetas WHERE nivel = 0 AND cli_id = ? ORDER BY nombre ASC";
		$values_car = array($cli_id);
		$consulta_car = $db->prepare($sql_car);
		$consulta_car->execute($values_car);
		if ($consulta_car->rowCount())  {
			$carpetas = $consulta_car->fetchAll(PDO::FETCH_ASSOC);
			foreach ($carpetas as $carpeta) {
				//Revisamos si tiene permiso de vista para esta carpeta
				if($this->hasPermission($carpeta['car_id'], $carpeta['cli_id'], 1)) {
					//Revisamos si tiene Carpetas hijo
					$sql_car_child = "SELECT car_id FROM carpetas WHERE nivel = ? AND cli_id = ? LIMIT 0, 1";
					$values_car_child = array($carpeta['car_id'], $cli_id);
					$consulta_car_child = $db->prepare($sql_car_child);
					$consulta_car_child->execute($values_car_child);
					$icon_carpeta = '<i class="fas fa-folder text-info"></i>';
					$class = '';
					if ($consulta_car_child->rowCount())  {
						$icon_carpeta = '<i id="car-'.$carpeta['car_id'].'-fa" class="fas fa-folder-plus text-info"></i>';
						$class = 'has_child nc closed';
					}

					//Revisamos si tiene archivos hijo
					$sql_ar = "SELECT doc_id FROM documentos WHERE car_id = ? AND cli_id = ? LIMIT 0, 1";
					$values_ar = array($carpeta['car_id'], $cli_id);
					$consulta_ar = $db->prepare($sql_ar);
					$consulta_ar->execute($values_ar);
					if ($consulta_ar->rowCount())  {
						$icon_carpeta = '<i id="car-'.$carpeta['car_id'].'-fa" class="fas fa-folder-plus text-info"></i>';
						$class = 'has_child nc closed';
					}

					$arbol_innner .= '<div id="car-'.$carpeta['car_id'].'" class="tree-folder" style="display: block;">				
											<div id="car-'.$carpeta['car_id'].'-child" class="tree-folder-header tree-car '.$class.'" data-id="'.$carpeta['car_id'].'">					
												'.$icon_carpeta.'				
												<div class="tree-folder-name">'.$carpeta['nombre'].'</div>				
											</div>				
											<div class="tree-folder-content"></div>				
											<div class="tree-loader" style="display: none;">
												<div class="tree-loading">
													<i class="fa fa-spinner fa-2x fa-spin"></i>
												</div>
											</div>
										</div>';
				}
			}
		}

		//Revisamos si tiene archivos hijo
		if($this->hasPermission(0, $cli_id, 1)) {
			$sql_ar = "SELECT * FROM documentos WHERE car_id = 0 AND cli_id = ? ORDER by nombre ASC";
			$values_ar = array($cli_id);
			$consulta_ar = $db->prepare($sql_ar);
			$consulta_ar->execute($values_ar);
			if ($consulta_ar->rowCount())  {
				$archivos = $consulta_ar->fetchAll(PDO::FETCH_ASSOC);
				foreach ($archivos as $archivo ) {
					$archivo_name = substr($archivo['nombre'], 0, -4);

					$arbol_innner .= '<div id="tree-item-'.$archivo['doc_id'].'" class="tree-item" style="display: block;" data-id="'.$archivo['doc_id'].'" data-car="0" data-cli="'.($root == 1 ? $cli_id : 0).'">								
										<div class="tree-item-name">
											<a href="'.$ruta.$archivo['ruta'].$archivo['nombre'].'" target="_blank"><i class="fa fa-file-pdf text-success"></i> '.$archivo_name.'</a>
										</div>			
									  </div>';
				}
			}
		}


		//Si root es 1 significa que va a mostrar una carpeta root antes
		if($root == 1) {
			//Revisamos nombre de Cliente
			$sql_cl = "SELECT cli_id, prefijo FROM clientes WHERE cli_id = ? ORDER BY prefijo ASC";
			$values_cl = array($cli_id);
			$consulta = $db->prepare($sql_cl);
			$consulta->execute($values_cl);
			$cliente = $consulta->fetch(PDO::FETCH_ASSOC);

			$arbol = '<div id="car-0" class="tree-folder tree-car" style="display: block;" data-id="0">
						<div id="car-0-child" class="tree-folder-header" data-id="0">
							<i class="fas fa-folder text-info"></i>				
							<div class="tree-folder-name">'.$cliente['prefijo'].'</div>				
						</div>				
						<div class="tree-folder-content">
							'.$arbol_innner.'
						</div>
					</div>';
		} else {
			$arbol = $arbol_innner;
		}


		return $arbol;
	}

	function printClientRoot() {
		$json = array();
		$json['msg'] = '';
		$json['error'] = false;
		$json['arbol'] = '';

		if(!isset($_SESSION)){
			@session_start();
		}

		$sup_id = $_SESSION["sky"]["userprofile"];

		if(isset($_POST['cli_id']) && $sup_id == 1) {
			$json['arbol'] = $this->getClientRoot($_POST['cli_id'], 0);
		} else {
			$json['error'] = true;
			$json['msg'] = 'Error al escoger usuario';
		}

		echo json_encode($json);
	}

	function printFolder() {
		global $ruta;
		$ruta = substr($ruta, 0, -3);
		$json = array();
		$json['msg'] = '';
		$json['error'] = false;
		$json['arbol'] = '';

		if(!isset($_SESSION)){
			@session_start();
		}

		if(isset($_POST['car_id'])) {
			//PEND: Permisos

			$db = $this->_conexion;

			//Revisamos cliente de carpeta
			$sql_par = "SELECT cli_id FROM carpetas WHERE car_id = ?";
			$values_par = array($_POST['car_id']);
			$consulta_par = $db->prepare($sql_par);
			$consulta_par->execute($values_par);
			$cliente = $consulta_par->fetch(PDO::FETCH_ASSOC);

			//Revisamos si tiene Carpetas hijo
			$sql_car = "SELECT * FROM carpetas WHERE nivel = ?";
			$values_car = array($_POST['car_id']);
			$consulta_car = $db->prepare($sql_car);
			$consulta_car->execute($values_car);
			if ($consulta_car->rowCount())  {
				$carpetas = $consulta_car->fetchAll(PDO::FETCH_ASSOC);
				foreach ($carpetas as $carpeta) {
					//Revisamos si tiene permiso de vista para esta carpeta
					if($this->hasPermission($carpeta['car_id'], $carpeta['cli_id'], 1)) {
						//Revisamos si tiene Carpetas hijo
						$sql_car_child = "SELECT car_id FROM carpetas WHERE nivel = ? LIMIT 0, 1";
						$values_car_child = array($carpeta['car_id']);
						$consulta_car_child = $db->prepare($sql_car_child);
						$consulta_car_child->execute($values_car_child);
						$icon_carpeta = '<i class="fas fa-folder text-info"></i>';
						$class = '';
						if ($consulta_car_child->rowCount())  {
							$icon_carpeta = '<i id="car-'.$carpeta['car_id'].'-fa" class="fas fa-folder-plus text-info"></i>';
							$class = 'has_child nc closed';
						}

						//Revisamos si tiene archivos hijo
						$sql_ar = "SELECT doc_id FROM documentos WHERE car_id = ? LIMIT 0, 1";
						$values_ar = array($carpeta['car_id']);
						$consulta_ar = $db->prepare($sql_ar);
						$consulta_ar->execute($values_ar);
						if ($consulta_ar->rowCount())  {
							$icon_carpeta = '<i id="car-'.$carpeta['car_id'].'-fa" class="fas fa-folder-plus text-info"></i>';
							$class = 'has_child nc closed';
						}

						$json['arbol'] .= '<div id="car-'.$carpeta['car_id'].'" class="tree-folder" style="display: block;">				
												<div id="car-'.$carpeta['car_id'].'-child" class="tree-folder-header tree-car '.$class.'" data-id="'.$carpeta['car_id'].'">					
													'.$icon_carpeta.'				
													<div class="tree-folder-name">'.$carpeta['nombre'].'</div>				
												</div>				
												<div class="tree-folder-content"></div>				
												<div class="tree-loader" style="display: none;">
													<div class="tree-loading">
														<i class="fa fa-spinner fa-2x fa-spin"></i>
													</div>
												</div>
											</div>';
					}
				}
			}

			//Revisamos si tiene archivos hijo
			//Revisamos si tiene permisos de vista
			if($this->hasPermission($_POST['car_id'], $cliente['cli_id'], 1)) {
				$sql_ar = "SELECT * FROM documentos WHERE car_id = ? ORDER BY nombre ASC";
				$values_ar = array($_POST['car_id']);
				$consulta_ar = $db->prepare($sql_ar);
				$consulta_ar->execute($values_ar);
				if ($consulta_ar->rowCount())  {
					$archivos = $consulta_ar->fetchAll(PDO::FETCH_ASSOC);
					foreach ($archivos as $archivo ) {
						$archivo_name = substr($archivo['nombre'], 0, -4);

						$json['arbol'] .= '<div id="tree-item-'.$archivo['doc_id'].'" class="tree-item" style="display: block;" data-id="'.$archivo['doc_id'].'" data-car="'.$archivo['car_id'].'" data-cli="0">								
											<div class="tree-item-name">
												<a href="'.$ruta.$archivo['ruta'].$archivo['nombre'].'" target="_blank"><i class="fa fa-file-pdf text-success"></i> '.$archivo_name.'</a>
											</div>			
										  </div>';
					}
				}
			}

		} else {
			$json['error'] = true;
			$json['msg'] = 'Error al escoger usuario';
		}

		echo json_encode($json);
	}

	function getCarButtons() {
		$json = array();
		$json['buttons'] = '';

		if(!isset($_SESSION)){
			@session_start();
		}

		//Revisamos permisos de alta
		if($_POST['car_id'] == 0) {

			if($_POST['cli_id'] == 0) {
				$cli_id = $_SESSION["sky"]["cli_id"];
			} else {
				$cli_id = $_POST['cli_id'];
			}

			if($this->hasPermission($_POST['car_id'], $cli_id, 2)) {
				//Alta de Documento
				$json['buttons'] .= '<a class="new-doc" href="#">
										<button class="btn btn-success">
											<i class="fa fa-plus-circle"></i> Nuevo Documento
										</button>
									</a>';

				//Alta de Carpeta
				$json['buttons'] .= '<a class="new-folder" href="#">
										<button class="btn btn-info">
											<i class="fa fa-folder"></i> Nueva Carpeta
										</button>
									</a>';

				//Alta Carpeta con Docs
				$json['buttons'] .= '<a class="new-folder2" href="#">
										<button class="btn btn-primary">
											<i class="fa fa-folder-plus"></i> Nueva Carpeta (c/Docs)
										</button>
									</a>';
			}
		}

		$db = $this->_conexion;
		$sql = "SELECT car_id, nombre, cli_id FROM carpetas WHERE car_id = ?";
		$values = array($_POST['car_id']);
		$consulta = $db->prepare($sql);
		$consulta->execute($values);


		if ($consulta->rowCount()) {

			$carpeta = $consulta->fetch(PDO::FETCH_ASSOC);

			if($this->hasPermission($_POST['car_id'], $carpeta['cli_id'], 2)) {
				//Alta de Documento
				$json['buttons'] .= '<a class="new-doc" href="#">
										<button class="btn btn-success">
											<i class="fa fa-plus-circle"></i> Nuevo Documento
										</button>
									</a>';

				//Alta de Carpeta
				$json['buttons'] .= '<a class="new-folder" href="#">
										<button class="btn btn-info">
											<i class="fa fa-folder"></i> Nueva Carpeta
										</button>
									</a>';

				//Alta Carpeta con Docs
				$json['buttons'] .= '<a class="new-folder2" href="#">
										<button class="btn btn-primary">
											<i class="fa fa-folder-plus"></i> Nueva Carpeta (c/Docs)
										</button>
									</a>';
			}
			

			if($_POST['car_id'] != 0) {

				//Revisamos permisos de cambios
				if($this->hasPermission($_POST['car_id'], $carpeta['cli_id'], 4)) {
					$json['buttons'] .= '<a class="rename" href="#" data-name="'.$carpeta['nombre'].'" data-id="'.$carpeta['car_id'].'">
											<button class="btn btn-secondary">
												<i class="fa fa-edit"></i> Renombrar
											</button>
										</a>';
				}	

				//Revisamos permisos de baja
				if($this->hasPermission($_POST['car_id'], $carpeta['cli_id'], 3)) {
					$json['buttons'] .= '<a class="erase-folder" href="#" data-name="'.$carpeta['nombre'].'" data-id="'.$carpeta['car_id'].'">
											<button class="btn btn-danger">
												<i class="fa fa-times"></i> Eliminar Carpeta
											</button>
										</a>';	
				}			
			}

		}

		echo json_encode($json);

	}

	function getDocButtons() {
		$json = array();
		$json['buttons'] = '';

		/*REVISAMOS PERMISOS*/
		$db = $this->_conexion;
		$sql = "SELECT doc_id, nombre, cli_id FROM documentos WHERE car_id = ? AND doc_id = ?";
		$values = array($_POST['car_id'], $_POST['doc_id']);
		$consulta = $db->prepare($sql);
		$consulta->execute($values);

		if ($consulta->rowCount()) {

			$documento = $consulta->fetch(PDO::FETCH_ASSOC);

			//Revisamos Permisos de cambios
			if($this->hasPermission($_POST['car_id'], $documento['cli_id'], 4)) {
				$document_name = substr($documento['nombre'], 0, -4);
				$json['buttons'] .= '<a class="rename-doc" href="#" data-name="'.$document_name.'" data-id="'.$documento['doc_id'].'">
									<button class="btn btn-secondary">
										<i class="fa fa-edit"></i> Renombrar
									</button>
								</a>';	
			}

			//Revisamos permisos de baja
			if($this->hasPermission($_POST['car_id'], $documento['cli_id'], 3)) {
				$json['buttons'] .= '<a class="erase-doc" href="#" data-name="'.$documento['nombre'].'" data-id="'.$documento['doc_id'].'">
										<button class="btn btn-danger">
											<i class="fa fa-times"></i> Eliminar Documento
										</button>
									</a>';	
			}

		}

		echo json_encode($json);

	}

	function newDoc() {
		global $ruta;
		$json = array();
		$json['error'] = false;
		$json['msg'] = '';

		if(!isset($_SESSION)){
			@session_start();
		}

		$sup_id = $_SESSION["sky"]["userprofile"];
		$siu_id = $_SESSION["sky"]["userid"];
		$cli_id = $_SESSION["sky"]["cli_id"];

		if(isset($_POST['car_id'])) {			

			//Revisamos que se haya elegido un archivo
			if(!isset($_FILES['doc']['name'])) {
				$json['error'] = true;
				$json['msg'] = 'Favor de elegir un documento para subir.';
			}

			//Revisamos que exista la carpeta y vemos su ruta
			$db = $this->_conexion;
			$db->beginTransaction();
			if(!$json['error']) {
				$sql_car = 'SELECT * FROM carpetas WHERE car_id = ?';
				$values_car = array($_POST['car_id']);
				$consulta_car = $db->prepare($sql_car);
				$consulta_car->execute($values_car);
				if ($consulta_car->rowCount()) {
					$carpeta = $consulta_car->fetch(PDO::FETCH_ASSOC);
					$carpeta['ruta'] = $carpeta['ruta'].$carpeta['nombre'].'/';
					$cli_id = $carpeta['cli_id'];
					//revisamos permisos
					if(!$this->hasPermission($_POST['car_id'], $cli_id, 2)) {
						$json['error'] = true;
						$json['msg'] = 'No tiene permisos de alta.';
					}
				} else {
					/*Es carpeta ROOT*/

					//Si es daemon debe de especificar a qué cliente es
					if($sup_id == 1) {
						if(!isset($_POST['cli_id'])) {
							$json['error'] = true;
							$json['msg'] = 'Favor de elegir un cliente válido.';
						} else {
							$cli_id = $_POST['cli_id'];
						}
					} else {
						$cli_id = $_SESSION["sky"]["cli_id"];
					}

					//Revisamos el prefijo del cliente
					if(!$json['error']) {
						$sql_pre = 'SELECT prefijo FROM clientes WHERE cli_id = ?';
						$values_pre = array($cli_id);
						$consulta_pre = $db->prepare($sql_pre);
						$consulta_pre->execute($values_pre);
						$cliente = $consulta_pre->fetch(PDO::FETCH_ASSOC);

						$carpeta['ruta'] = 'archivos/'.$cliente['prefijo'].'/';
					}

					//Revisamos permiso
					if(!$this->hasPermission(0, $cli_id, 2)) {
						$json['error'] = true;
						$json['msg'] = 'No tiene permisos de alta.';
					}
				}
			}

			//Revisamos si hay repetido y qué acción se tomará
			if(!$json['error']) {

				$file_name = $_FILES['doc']['name'];

				$sql_chck = "SELECT * FROM documentos WHERE car_id = ? AND nombre = ?";
				$values_chck = array($_POST['car_id'],
									 $file_name);

				$consulta_chck = $db->prepare($sql_chck);
				$consulta_chck->execute($values_chck);
				if ($consulta_chck->rowCount()) {
					//SI hay un documento con ese nombre
					$documento = $consulta_chck->fetch(PDO::FETCH_ASSOC);
					//Verificamos la acción a tomar
					if($_POST['accion_extra'] == 2) {
						//Esto significa que quiere conservar ambos docs
						//Buscamos un nuevo nombre
						$ruta_doc = $ruta.$carpeta['ruta'];
						$doc_name = $ruta_doc.$file_name;
						$ext = pathinfo($file_name, PATHINFO_EXTENSION);
						$name_original = rtrim($file_name, '.'.$ext);
						$n = 1;
						while (file_exists($doc_name)) {
							$name = $name_original.'('.$n.').'.$ext;
							$file_name = $name;
							$doc_name = $ruta_doc.$file_name;
							$n++;
						}

					} else {
						//Reemplazamos el documento actual
						//Eliminamos los registros que hay de este documento
						$consulta_del = $db->prepare("DELETE FROM documentos WHERE doc_id = :valor");
						$consulta_del->bindParam(':valor', $documento['doc_id']);
						$consulta_del->execute();

						//Eliminamos detalle de documento
						$consulta_del = $db->prepare("DELETE FROM documentos_detalles WHERE doc_id = :valor");
						$consulta_del->bindParam(':valor', $documento['doc_id']);
						$consulta_del->execute();
					}
				}

			}

			if(!$json['error']) {
				//Subimos documento
				$ruta_doc = $ruta.$carpeta['ruta'];
				$doc_name = $ruta_doc.$file_name;

				//CHMOD - Damos permisos al usuario a agregar el
				//chmod($ruta_doc, 0777);

				if(move_uploaded_file($_FILES['doc']['tmp_name'], $doc_name)){

					//Agregamos el documento en la BD
					$size = filesize($doc_name);
					$sql = "INSERT INTO documentos (nombre,
													car_id,
													siu_id,
													cli_id,
													ruta,
													size)
							VALUES( ?, ?, ?, ?, ?, ? )";	

					$values = array($file_name,
									$_POST['car_id'],
									$siu_id,
									$cli_id,
									$carpeta['ruta'],
									$size);

					$consulta = $db->prepare($sql);

					try {
						$consulta->execute($values);

					} catch(PDOException $e) {
						$db->rollBack();
						die($e->getMessage());
					}

					$doc_id = $this->last_id();


					//Comenzamos a verificar el contenido de cada página
					include 'vendor/autoload.php';
					$parser = new \Smalot\PdfParser\Parser();
					$pdf    = $parser->parseFile($doc_name);
					 
					// Retrieve all pages from the pdf file.
					$pages  = $pdf->getPages();
		 
					// Loop	 over each page to extract text.
					$num_page = 1;
					foreach ($pages as $page) {
					    $sql = "INSERT INTO documentos_detalles (contenido,
																 pagina,
																 doc_id)
								VALUES( ?, ?, ? )";	

						$values = array($page->getText(),
										$num_page,
										$doc_id);

						$consulta = $db->prepare($sql);

						try {
							$consulta->execute($values);

						} catch(PDOException $e) {
							$db->rollBack();
							die($e->getMessage());
						}

						$num_page++;
					}

					$json['msg'] = 'Documento guardado con éxito.';
					$db->commit();

					//chmod($ruta_doc, 0755);

				} else {
					$json['error'] = true;
					$json['msg'] = "Error al subir archivo. Inténtelo de nuevo más tarde.";
				}
			}


		} else {
			$json['error'] = true;
			$json['msg'] = 'Favor de elegir una carpeta válida.';
		}


		echo json_encode($json);
	}

	function checkDoc() {
		$json = array();
		$json['error'] = false;
		$json['msg'] = '';
		$json['check'] = false;

		if(!isset($_SESSION)){
			@session_start();
		}

		$sup_id = $_SESSION["sky"]["userprofile"];
		$siu_id = $_SESSION["sky"]["userid"];
		$cli_id = $_SESSION["sky"]["cli_id"];

		if(isset($_POST['car_id'])) {

			//Revisamos que se haya elegido un archivo
			if(!isset($_POST['doc']) || $_POST['doc'] == '') {
				$json['error'] = true;
				$json['msg'] = 'Favor de elegir un documento para subir.';
			}

			//Revisamos que exista la carpeta y vemos su ruta
			$db = $this->_conexion;
			if(!$json['error']) {
				$sql_car = 'SELECT * FROM carpetas WHERE car_id = ?';
				$values_car = array($_POST['car_id']);
				$consulta_car = $db->prepare($sql_car);
				$consulta_car->execute($values_car);
				if ($consulta_car->rowCount()) {
					$carpeta = $consulta_car->fetch(PDO::FETCH_ASSOC);
					$carpeta['ruta'] = $carpeta['ruta'].$carpeta['nombre'].'/';
					$cli_id = $carpeta['cli_id'];
					//PEND revisamos permisos

				} else {
					/*Es carpeta ROOT*/

					//Si es daemon debe de especificar a qué cliente es
					if($sup_id == 1) {
						if(!isset($_POST['cli_id'])) {
							$json['error'] = true;
							$json['msg'] = 'Favor de elegir un cliente válido.';
						} else {
							$cli_id = $_POST['cli_id'];
						}
					} else {
						$cli_id = $_SESSION["sky"]["cli_id"];
					}

					//Revisamos el prefijo del cliente
					if(!$json['error']) {
						$sql_pre = 'SELECT prefijo FROM clientes WHERE cli_id = ?';
						$values_pre = array($cli_id);
						$consulta_pre = $db->prepare($sql_pre);
						$consulta_pre->execute($values_pre);
						$cliente = $consulta_pre->fetch(PDO::FETCH_ASSOC);

						$carpeta['ruta'] = 'archivos/'.$cliente['prefijo'].'/';
					}
				}
			}

			if(!$json['error']) {
				//Subimos documento
				global $ruta;
				$ruta_doc = $ruta.$carpeta['ruta'];

				/*$name = explode('/');
				$name = $name[count($name)-1];*/
				$name = $_POST['doc'];

				$doc_name = $ruta_doc.$name;
				$json['doc_name'] = $doc_name;
				if(file_exists($doc_name)) {
					$json['check'] = true;
				}
			}


		} else {
			$json['error'] = true;
			$json['msg'] = 'Favor de elegir una carpeta válida.';
		}


		echo json_encode($json);
	}

	function deleteDoc() {
		global $ruta;
		$json = array();
		$json['error'] = false;
		$json['msg'] = '';

		if(isset($_POST['doc_id'])) {

			//Detalles del documento
			$db = $this->_conexion;
			$sql_doc = 'SELECT * FROM documentos WHERE doc_id = ?';
			$values_doc = array($_POST['doc_id']);
			$consulta_doc = $db->prepare($sql_doc);
			$consulta_doc->execute($values_doc);
			$documento = $consulta_doc->fetch(PDO::FETCH_ASSOC);

			//Revisamos Permisos
			if($this->hasPermission($documento['car_id'], $documento['cli_id'], 3)) {
				
				$db->beginTransaction();
				try {

					//Eliminamos los registros que hay de este documento
					$consulta_del = $db->prepare("DELETE FROM documentos WHERE doc_id = :valor");
					$consulta_del->bindParam(':valor', $documento['doc_id']);
					$consulta_del->execute();

					//Eliminamos detalle de documento
					$consulta_del = $db->prepare("DELETE FROM documentos_detalles WHERE doc_id = :valor");
					$consulta_del->bindParam(':valor', $documento['doc_id']);
					$consulta_del->execute();

					//Eliminamos el documento
					$doc_name = $ruta.$documento['ruta'].$documento['nombre'];
					if(file_exists($doc_name)) {
						unlink($doc_name);
					}

				} catch(PDOException $e) {
					$db->rollBack();
					die($e->getMessage());
				}

				$json['msg'] = 'Documento eliminado con éxito.';
				$db->commit();
			} else {
				$json['error'] = true;
				$json['msg'] = 'No tiene permisos de baja.';
			}

		} else {
			$json['error'] = true;
			$json['msg'] = 'Favor de elegir un documento válido.';
		}

		echo json_encode($json);
	}

	function checkFolder() {
		$json = array();
		$json['error'] = false;
		$json['msg'] = '';
		$json['check'] = false;

		if(!isset($_SESSION)){
			@session_start();
		}

		$sup_id = $_SESSION["sky"]["userprofile"];
		$siu_id = $_SESSION["sky"]["userid"];
		$cli_id = $_SESSION["sky"]["cli_id"];

		if(isset($_POST['car_id'])) {

			//Revisamos que se haya elegido un archivo
			if(!isset($_POST['carpeta'])) {
				$json['error'] = true;
				$json['msg'] = 'Favor de elegir un documento para subir.';
			}

			//Revisamos que exista la carpeta y vemos su ruta
			$db = $this->_conexion;
			if(!$json['error']) {
				$sql_car = 'SELECT * FROM carpetas WHERE car_id = ?';
				$values_car = array($_POST['car_id']);
				$consulta_car = $db->prepare($sql_car);
				$consulta_car->execute($values_car);
				if ($consulta_car->rowCount()) {
					$carpeta = $consulta_car->fetch(PDO::FETCH_ASSOC);
					$cli_id = $carpeta['cli_id'];
					//PEND revisamos permisos

				} else {
					/*Es carpeta ROOT*/

					//Si es daemon debe de especificar a qué cliente es
					if($sup_id == 1) {
						if(!isset($_POST['cli_id'])) {
							$json['error'] = true;
							$json['msg'] = 'Favor de elegir un cliente válido.';
						} else {
							$cli_id = $_POST['cli_id'];
						}
					} else {
						$cli_id = $_SESSION["sky"]["cli_id"];
					}

					//Revisamos el prefijo del cliente
					if(!$json['error']) {
						$sql_pre = 'SELECT prefijo FROM clientes WHERE cli_id = ?';
						$values_pre = array($cli_id);
						$consulta_pre = $db->prepare($sql_pre);
						$consulta_pre->execute($values_pre);
						$cliente = $consulta_pre->fetch(PDO::FETCH_ASSOC);

						$carpeta['ruta'] = 'archivos/'.$cliente['prefijo'].'/';
					}
				}
			}

			if(!$json['error']) {
				//Subimos documento
				global $ruta;
				$ruta_doc = $ruta.$carpeta['ruta'];

				/*$name = explode('/');
				$name = $name[count($name)-1];*/
				$name = $_POST['carpeta'];

				$doc_name = $ruta_doc.$name;
				if(file_exists($doc_name)) {
					$json['check'] = true;
				}
			}


		} else {
			$json['error'] = true;
			$json['msg'] = 'Favor de elegir una carpeta válida.';
		}


		echo json_encode($json);
	}

	function newFolder() {
		$json = array();
		$json['error'] = false;
		$json['msg'] = '';
		$json['check'] = false;

		if(!isset($_SESSION)){
			@session_start();
		}

		$sup_id = $_SESSION["sky"]["userprofile"];
		$siu_id = $_SESSION["sky"]["userid"];
		$cli_id = $_SESSION["sky"]["cli_id"];

		if(isset($_POST['car_id'])) {

			//Revisamos que se haya elegido un archivo
			if(!isset($_POST['carpeta']) || empty($_POST['carpeta'])) {
				$json['error'] = true;
				$json['msg'] = 'Favor de elegir un documento para subir.';
			}

			//Revisamos que exista la carpeta y vemos su ruta
			$db = $this->_conexion;
			if(!$json['error']) {
				$sql_car = 'SELECT * FROM carpetas WHERE car_id = ?';
				$values_car = array($_POST['car_id']);
				$consulta_car = $db->prepare($sql_car);
				$consulta_car->execute($values_car);
				if ($consulta_car->rowCount()) {
					$carpeta = $consulta_car->fetch(PDO::FETCH_ASSOC);
					$carpeta['ruta'] = $carpeta['ruta'].$carpeta['nombre'].'/';
					$cli_id = $carpeta['cli_id'];
					//Revisamos Permisos
					if(!$this->hasPermission($_POST['car_id'], $cli_id, 2)) {
						$json['error'] = true;
						$json['msg'] = 'No tiene permisos de alta.';
					}

				} else {
					/*Es carpeta ROOT*/

					//Si es daemon debe de especificar a qué cliente es
					if($sup_id == 1) {
						if(!isset($_POST['cli_id'])) {
							$json['error'] = true;
							$json['msg'] = 'Favor de elegir un cliente válido.';
						} else {
							$cli_id = $_POST['cli_id'];
						}
					} else {
						$cli_id = $_SESSION["sky"]["cli_id"];
					}

					//Revisamos el prefijo del cliente
					if(!$json['error']) {
						$sql_pre = 'SELECT prefijo FROM clientes WHERE cli_id = ?';
						$values_pre = array($cli_id);
						$consulta_pre = $db->prepare($sql_pre);
						$consulta_pre->execute($values_pre);
						$cliente = $consulta_pre->fetch(PDO::FETCH_ASSOC);

						$carpeta['ruta'] = 'archivos/'.$cliente['prefijo'].'/';
					}

					if(!$this->hasPermission(0, $cli_id, 2)) {
						$json['error'] = true;
						$json['msg'] = 'No tiene permisos de alta.';
					}
				}
			}

			if(!$json['error']) {
				//Subimos documento
				global $ruta;
				$ruta_doc = $ruta.$carpeta['ruta'];

				/*$name = explode('/');
				$name = $name[count($name)-1];*/
				$name = $_POST['carpeta'];

				$doc_name = $ruta_doc.$name;
				$json['doc_name'] = $doc_name;
				if(file_exists($doc_name)) {
					
					$json['error'] = true;
					$json['msg'] = 'Existe otra carpeta con el mismo nombre, favor de escoger otro nombre.';

				} else {
					mkdir($doc_name,0755,true);

					$sql = "INSERT INTO carpetas (nombre,
												  nivel,
												  ruta,
												  cli_id)
							VALUES( ?, ?, ?, ? )";	

					$values = array($_POST['carpeta'],
									$_POST['car_id'],
									$carpeta['ruta'],
									$cli_id);

					$consulta = $db->prepare($sql);

					try {
						$consulta->execute($values);

					} catch(PDOException $e) {
						die($e->getMessage());
					}

					//Otorgamos los mismos permisos que al padre
					$car_id = $this->last_id();
					$sql_permiso = 'SELECT * FROM permisos_carpetas WHERE car_id = ?';
					$values_permiso = array($_POST['car_id']);
					$consulta_permiso = $db->prepare($sql_permiso);
					$consulta_permiso->execute($values_permiso);
					if ($consulta_permiso->rowCount()) {
						$permisos = $consulta_permiso->fetchAll(PDO::FETCH_ASSOC);
						foreach ($permisos as $permiso) {
							$sql = "INSERT INTO permisos_carpetas (siu_id,
																  car_id,
																  cli_id,
																  permisos)
									VALUES( ?, ?, ?, ? )";	

							$values = array($permiso['siu_id'],
											$car_id,
											$permiso['cli_id'],
											$permiso['permisos']);

							$consulta = $db->prepare($sql);

							try {
								$consulta->execute($values);

							} catch(PDOException $e) {
								die($e->getMessage());
							}
						}
					}

					$json['msg'] = 'Carpeta guardada con éxito.';

				}
			}


		} else {
			$json['error'] = true;
			$json['msg'] = 'Favor de elegir una carpeta válida.';
		}


		echo json_encode($json);
	}

	function deleteFolder() {
		global $ruta;
		$json = array();
		$json['error'] = false;
		$json['msg'] = '';

		if(isset($_POST['car_id'])) {

			$db = $this->_conexion;


			//Datos de carpeta
			$sql_car = 'SELECT * FROM carpetas WHERE car_id = ?';
			$values_car = array($_POST['car_id']);
			$consulta_car = $db->prepare($sql_car);
			$consulta_car->execute($values_car);
			$carpeta = $consulta_car->fetch(PDO::FETCH_ASSOC);

			//Revisamos permisos
			if($this->hasPermission($carpeta['car_id'], $carpeta['cli_id'], 3)) {
				$db->beginTransaction();
				try {

					//Eliminamos los registros que hay de esta carpeta de manera recursiva
					$this->deleteFolderRec($_POST['car_id']);

				} catch(PDOException $e) {
					$db->rollBack();
					die($e->getMessage());
				}

				$json['msg'] = 'Carpeta eliminada con éxito.';
				$db->commit();
			} else {
				$json['error'] = true;
				$json['msg'] = 'No tiene permisos de baja.';
			}

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
			unlink($doc_name);

			//Eliminamos el detalle
			$consulta_del = $db->prepare("DELETE FROM documentos_detalles WHERE doc_id = :valor");
			$consulta_del->bindParam(':valor', $documento['doc_id']);
			$consulta_del->execute();
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

	function checkDocRename() {
		$json = array();
		$json['error'] = false;
		$json['msg'] = '';
		$json['check'] = false;

		if(!isset($_SESSION)){
			@session_start();
		}

		$sup_id = $_SESSION["sky"]["userprofile"];
		$siu_id = $_SESSION["sky"]["userid"];
		$cli_id = $_SESSION["sky"]["cli_id"];

		//Revisamos que se haya elegido un archivo
		if(!isset($_POST['doc']) && !isset($_POST['doc_id']) && !empty($_POST['doc'])) {
			$json['error'] = true;
			$json['msg'] = 'Favor de ingresar un nombre válido.';
		}

		//Revisamos que exista la carpeta y vemos su ruta
		$db = $this->_conexion;
		if(!$json['error']) {

			$sql_doc = 'SELECT * FROM documentos WHERE doc_id = ?';
			$values_doc = array($_POST['doc_id']);
			$consulta_doc = $db->prepare($sql_doc);
			$consulta_doc->execute($values_doc);
			if ($consulta_doc->rowCount()) {
				$documento = $consulta_doc->fetch(PDO::FETCH_ASSOC);

				//Vemos si hay algún doc con el mismo nombre
				$sql_chck = 'SELECT * FROM documentos WHERE car_id = ? AND cli_id = ? AND nombre = ?';
				$values_chck = array($documento['car_id'],
									 $documento['cli_id'],
									 $_POST['doc']);
				$consulta_chck = $db->prepare($sql_chck);
				$consulta_chck->execute($values_chck);
				if ($consulta_chck->rowCount()) {
					$json['check'] = true;
				}


			} else {
				$json['error'] = true;
				$json['msg'] = 'Documento inválido.';
			}

		}


		echo json_encode($json);
	}

	function renameDoc() {
		global $ruta;
		$json = array();
		$json['error'] = false;
		$json['msg'] = '';
		$json['check'] = false;

		if(!isset($_SESSION)){
			@session_start();
		}

		$sup_id = $_SESSION["sky"]["userprofile"];
		$siu_id = $_SESSION["sky"]["userid"];
		$cli_id = $_SESSION["sky"]["cli_id"];

		//Revisamos que se haya elegido un archivo
		if(!isset($_POST['doc']) && !isset($_POST['doc_id']) && !empty($_POST['doc'])) {
			$json['error'] = true;
			$json['msg'] = 'Favor de ingresar un nombre válido.';
		}

		//Revisamos que exista la carpeta y vemos su ruta
		$db = $this->_conexion;
		if(!$json['error']) {

			$sql_doc = 'SELECT * FROM documentos WHERE doc_id = ?';
			$values_doc = array($_POST['doc_id']);
			$consulta_doc = $db->prepare($sql_doc);
			$consulta_doc->execute($values_doc);
			if ($consulta_doc->rowCount()) {
				$documento = $consulta_doc->fetch(PDO::FETCH_ASSOC);

				//Revisamos si tiene permisos de cambio
				if($this->hasPermission($documento['car_id'], $documento['cli_id'], 4)) {
					//Vemos si hay algún doc con el mismo nombre
					$sql_chck = 'SELECT * FROM documentos WHERE car_id = ? AND cli_id = ? AND nombre = ?';
					$values_chck = array($documento['car_id'],
										 $documento['cli_id'],
										 $_POST['doc']);
					$consulta_chck = $db->prepare($sql_chck);
					$consulta_chck->execute($values_chck);
					if ($consulta_chck->rowCount()) {
						$json['error'] = true;
						$json['msg'] = 'El nombre ingresado ya existe en la carpeta, favor de elegir otro.';
					} else {

						//Revisamos la extensión
						$ext = substr($_POST['doc'], -4);
						if($ext != '.pdf') {
							$_POST['doc'] = $_POST['doc'].'.pdf';
						}

						$sql = "UPDATE documentos SET nombre = ?
								WHERE doc_id = ?";

						$values = array($_POST['doc'],
										$_POST['doc_id']);

						$consulta = $db->prepare($sql);

						try {
							$consulta->execute($values);

						} catch(PDOException $e) {
							die($e->getMessage());
						}

						//Cambiamos el nombre físico del doc
						rename($ruta.$documento['ruta'].$documento['nombre'], $ruta.$documento['ruta'].$_POST['doc']);

						$json['msg'] = 'Documento guardado con éxito.';
					}
				} else {
					$json['error'] = true;
					$json['msg'] = 'No tiene permisos de cambios';
				}

			} else {
				$json['error'] = true;
				$json['msg'] = 'Documento inválido.';
			}

		}


		echo json_encode($json);
	}

	function renameFolder() {
		global $ruta;
		$json = array();
		$json['error'] = false;
		$json['msg'] = '';
		$json['check'] = false;

		if(!isset($_SESSION)){
			@session_start();
		}

		$sup_id = $_SESSION["sky"]["userprofile"];
		$siu_id = $_SESSION["sky"]["userid"];
		$cli_id = $_SESSION["sky"]["cli_id"];

		//Revisamos que se haya elegido un archivo
		if(!isset($_POST['car']) && !isset($_POST['car_id']) && !empty($_POST['car'])) {
			$json['error'] = true;
			$json['msg'] = 'Favor de ingresar un nombre válido.';
		}

		//Revisamos que exista la carpeta y vemos su ruta
		$db = $this->_conexion;
		$db->beginTransaction();
		if(!$json['error']) {

			$sql_doc = 'SELECT * FROM carpetas WHERE car_id = ?';
			$values_doc = array($_POST['car_id']);
			$consulta_doc = $db->prepare($sql_doc);
			$consulta_doc->execute($values_doc);
			if ($consulta_doc->rowCount()) {
				$carpeta = $consulta_doc->fetch(PDO::FETCH_ASSOC);

				//Revisamos si tiene permisos de cambio
				if($this->hasPermission($carpeta['car_id'], $carpeta['cli_id'], 4)) {
					//Vemos si hay alguna carpeta con el mismo nombre
					$sql_chck = 'SELECT * FROM carpetas WHERE nivel = ? AND cli_id = ? AND nombre = ?';
					$values_chck = array($carpeta['nivel'],
										 $carpeta['cli_id'],
										 $_POST['car']);
					$consulta_chck = $db->prepare($sql_chck);
					$consulta_chck->execute($values_chck);
					if ($consulta_chck->rowCount()) {
						$json['error'] = true;
						$json['msg'] = 'El nombre ingresado ya existe en la carpeta, favor de elegir otro.';
					} else {

						$sql1 = "UPDATE carpetas SET nombre = ?
								WHERE car_id = ?";

						$values1 = array($_POST['car'],
										$_POST['car_id']);

						$consulta1 = $db->prepare($sql1);

						try {
							$consulta1->execute($values1);

						} catch(PDOException $e) {
							$db->rollBack();
							die($e->getMessage().$sql1);
						}

						//Revisamos todas las carpetas hijo
						$sql2 = "UPDATE carpetas SET ruta = REPLACE (ruta, ?, ?)
								 WHERE ruta LIKE '".$carpeta['ruta'].$carpeta['nombre']."%'";

						$values2 = array($carpeta['ruta'].$carpeta['nombre'],
										$carpeta['ruta'].$_POST['car']);

						$consulta2 = $db->prepare($sql2);

						try {
							$consulta2->execute($values2);

						} catch(PDOException $e) {
							$db->rollBack();
							die($e->getMessage().$sql2);
						}

						//Revisamos todas los documentos hijo
						$sql3 = "UPDATE documentos SET ruta = REPLACE (ruta, ?, ?)
								 WHERE ruta LIKE '".$carpeta['ruta'].$carpeta['nombre']."%'";

						$values3 = array($carpeta['ruta'].$carpeta['nombre'],
										$carpeta['ruta'].$_POST['car']);

						$consulta3 = $db->prepare($sql3);

						try {
							$consulta3->execute($values3);

						} catch(PDOException $e) {
							$db->rollBack();
							die($e->getMessage().$sql3);
						}

						//Cambiamos el nombre físico del carpeta
						if(!rename($ruta.$carpeta['ruta'].$carpeta['nombre'], $ruta.$carpeta['ruta'].$_POST['car'])) {
							$db->rollBack();
						} 

						$db->commit();

						$json['msg'] = 'Carpeta guardada con éxito.';
					}
				}

			} else {
				$json['error'] = true;
				$json['msg'] = 'Carpeta inválida.';
			}

		}


		echo json_encode($json);
	}

	function newFolderDocs() {
		$json = array();
		$json['error'] = false;
		$json['msg'] = '';
		$json['check'] = false;

		if(!isset($_SESSION)){
			@session_start();
		}

		$sup_id = $_SESSION["sky"]["userprofile"];
		$siu_id = $_SESSION["sky"]["userid"];
		$cli_id = $_SESSION["sky"]["cli_id"];

		if(isset($_POST['car_id'])) {

			//Revisamos que se haya elegido un archivo
			/*if(!isset($_POST['pdfs']) || empty($_POST['pdfs'])) {
				$json['error'] = true;
				$json['msg'] = 'Favor de elegir una carpeta para subir.';
			}*/

			//Revisamos que exista la carpeta y vemos su ruta
			$db = $this->_conexion;
			if(!$json['error']) {
				$sql_car = 'SELECT * FROM carpetas WHERE car_id = ?';
				$values_car = array($_POST['car_id']);
				$consulta_car = $db->prepare($sql_car);
				$consulta_car->execute($values_car);
				if ($consulta_car->rowCount()) {
					$carpeta = $consulta_car->fetch(PDO::FETCH_ASSOC);
					$carpeta['ruta'] = $carpeta['ruta'].$carpeta['nombre'].'/';
					$cli_id = $carpeta['cli_id'];
					//Revisamos Permisos
					if(!$this->hasPermission($_POST['car_id'], $cli_id, 2)) {
						$json['error'] = true;
						$json['msg'] = 'No tiene permisos de alta.';
					}

				} else {
					/*Es carpeta ROOT*/

					//Si es daemon debe de especificar a qué cliente es
					if($sup_id == 1) {
						if(!isset($_POST['cli_id'])) {
							$json['error'] = true;
							$json['msg'] = 'Favor de elegir un cliente válido.';
						} else {
							$cli_id = $_POST['cli_id'];
						}
					} else {
						$cli_id = $_SESSION["sky"]["cli_id"];
					}

					//Revisamos el prefijo del cliente
					if(!$json['error']) {
						$sql_pre = 'SELECT prefijo FROM clientes WHERE cli_id = ?';
						$values_pre = array($cli_id);
						$consulta_pre = $db->prepare($sql_pre);
						$consulta_pre->execute($values_pre);
						$cliente = $consulta_pre->fetch(PDO::FETCH_ASSOC);

						$carpeta['ruta'] = 'archivos/'.$cliente['prefijo'].'/';
					}

					if(!$this->hasPermission(0, $cli_id, 2)) {
						$json['error'] = true;
						$json['msg'] = 'No tiene permisos de alta.';
					}
				}
			}

			if(!$json['error']) {
				//Subimos documento
				global $ruta;
				$ruta_doc = $ruta.$carpeta['ruta'];

				// Split the string containing the list of file paths into an array 
				$paths = explode("###",rtrim($_POST['paths'],"###"));

				// Loop through files sent
				foreach($_FILES as $key => $current) {
					//echo $current['name'].'<br>';
					// Stores full destination path of file on server
					$destination = rtrim($paths[$key],"/.");
					$uploadFile = $ruta_doc.$destination;
					// Stores containing folder path to check if dir later
					$folder = substr($uploadFile,0,strrpos($uploadFile,"/"));


					
					// Check whether the current entity is an actual file or a folder (With a . for a name)
					if(strlen($current['name'])!=1) {

						//Recorremos todos los folders del archivo
						$dest = substr($destination,0,strrpos($destination,"/"));
						//echo $dest.'<br>';
						$folders_dest = explode('/', $dest);
						$last_car_id = $_POST['car_id'];
						$last_ruta = $carpeta['ruta'];
						foreach ($folders_dest as $key_fd => $folder_dest) {
							//Buscamos si existe el folder
							$folder_exists = true;
							if(!is_dir($ruta.$last_ruta.$folder_dest)) {
								$folder_exists = false;
							} else {
								//SI sí existe, solo vemos qué id tiene
								$sql_chck_folder = "SELECT * FROM carpetas WHERE nivel = ? AND nombre = ? AND cli_id = ?";
								$values_chck_folder = array($last_car_id,
															$folder_dest,
															$cli_id);
								$consulta_chck_folder = $db->prepare($sql_chck_folder);
								$consulta_chck_folder->execute($values_chck_folder);
								if ($consulta_chck_folder->rowCount()) {
									//echo 'si existe '.$ruta.$last_ruta.$folder_dest.'<br>';
									$folder_actual = $consulta_chck_folder->fetch(PDO::FETCH_ASSOC);
									$last_car_id = $folder_actual['car_id'];
									$last_ruta = $folder_actual['ruta'].$folder_actual['nombre'].'/';
								} else {
									$folder_exists = false;
								}

							}

							//Si no existe, creamos uno y guardamos los datos
							if(!$folder_exists) {
								//Creamos la carpeta
								//echo 'no existe '.$ruta.$last_ruta.$folder_dest.'<br>';
								mkdir($ruta.$last_ruta.$folder_dest,0755,true);

								//Guardamos en DB
								$sql_folder = "INSERT INTO carpetas (nombre,
															  		 nivel,
															  		 ruta,
															  		 cli_id)
											   VALUES( ?, ?, ?, ? )";	

								$values_folder = array($folder_dest,
													   $last_car_id,
													   $last_ruta,
													   $cli_id);

								$consulta_folder = $db->prepare($sql_folder);

								try {
									$consulta_folder->execute($values_folder);

								} catch(PDOException $e) {
									die($e->getMessage());
								}

								//Guardamos datos
								$parent_folder = $last_car_id;
								$last_car_id = $this->last_id();
								$last_ruta .= $folder_dest.'/';

								//Otorgamos los mismos permisos que al padre
								$sql_permiso = 'SELECT * FROM permisos_carpetas WHERE car_id = ?';
								$values_permiso = array($parent_folder);
								$consulta_permiso = $db->prepare($sql_permiso);
								$consulta_permiso->execute($values_permiso);
								if ($consulta_permiso->rowCount()) {
									$permisos = $consulta_permiso->fetchAll(PDO::FETCH_ASSOC);
									foreach ($permisos as $permiso) {
										$sql = "INSERT INTO permisos_carpetas (siu_id,
																			  car_id,
																			  cli_id,
																			  permisos)
												VALUES( ?, ?, ?, ? )";	

										$values = array($permiso['siu_id'],
														$last_car_id,
														$permiso['cli_id'],
														$permiso['permisos']);

										$consulta = $db->prepare($sql);

										try {
											$consulta->execute($values);

										} catch(PDOException $e) {
											die($e->getMessage());
										}
									}
								}


							}

						}

						//Una vez que ya revisamos que estén todas las carpetas
						//Subimos el archivo
						//Revisamos si hay repetido y qué acción se tomará
						if(!$json['error'] && $current['name'] != '.DS_Store') {

							$file_name = $current['name'];

							$sql_chck = "SELECT * FROM documentos WHERE car_id = ? AND nombre = ?";
							$values_chck = array($last_car_id,
												 $file_name);

							$consulta_chck = $db->prepare($sql_chck);
							$consulta_chck->execute($values_chck);
							if ($consulta_chck->rowCount()) {
								//SI hay un documento con ese nombre
								$documento = $consulta_chck->fetch(PDO::FETCH_ASSOC);
								//Verificamos la acción a tomar
								//if($_POST['accion_extra'] == 2) {
									//Esto significa que quiere conservar ambos docs
									//Buscamos un nuevo nombre
									$ruta_doc = $ruta.$last_ruta;
									$doc_name = $ruta_doc.$file_name;
									$ext = pathinfo($file_name, PATHINFO_EXTENSION);
									$name_original = rtrim($file_name, '.'.$ext);
									$n = 1;
									while (file_exists($doc_name)) {
										$name = $name_original.'('.$n.').'.$ext;
										$file_name = $name;
										$doc_name = $ruta_doc.$file_name;
										$n++;
									}

								//}
							}

						}

						if(!$json['error'] && $current['name'] != '.DS_Store') {
							//Subimos documento
							$ruta_doc = $ruta.$last_ruta;
							$doc_name = $ruta_doc.$file_name;
							if(move_uploaded_file($current['tmp_name'], $doc_name)){

								//Agregamos el documento en la BD
								$size = filesize($doc_name);
								$sql = "INSERT INTO documentos (nombre,
																car_id,
																siu_id,
																cli_id,
																ruta,
																size)
										VALUES( ?, ?, ?, ?, ?, ? )";	

								$values = array($file_name,
												$last_car_id,
												$siu_id,
												$cli_id,
												$last_ruta,
												$size);

								$consulta = $db->prepare($sql);

								try {
									$consulta->execute($values);

								} catch(PDOException $e) {
									$db->rollBack();
									die($e->getMessage());
								}

								$doc_id = $this->last_id();


								//Comenzamos a verificar el contenido de cada página
								include 'vendor/autoload.php';
								$parser = new \Smalot\PdfParser\Parser();
								$pdf    = $parser->parseFile($doc_name);
								 
								// Retrieve all pages from the pdf file.
								$pages  = $pdf->getPages();
					 
								// Loop	 over each page to extract text.
								$num_page = 1;
								foreach ($pages as $page) {
								    $sql = "INSERT INTO documentos_detalles (contenido,
																			 pagina,
																			 doc_id)
											VALUES( ?, ?, ? )";	

									$values = array($page->getText(),
													$num_page,
													$doc_id);

									$consulta = $db->prepare($sql);

									try {
										$consulta->execute($values);

									} catch(PDOException $e) {
										$db->rollBack();
										die($e->getMessage());
									}

									$num_page++;
								}

								$json['msg'] = 'Archivos guardado con éxito.';
								//$db->commit();

							} else {
								$json['error'] = true;
								$json['msg'] = "Error al subir archivo. Inténtelo de nuevo más tarde.";
							}
						}

					}

				}

			}


		} else {
			$json['error'] = true;
			$json['msg'] = 'Favor de elegir una carpeta válida.';
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
		case "pruebaPDF":
			$libs->pruebaPDF();
			break;	
		case "pruebaFile":
			$libs->pruebaFile();
			break;	
		case "printRoot":
			$libs->printRoot();
			break;
		case "printClientRoot":
			$libs->printClientRoot();
			break;	
		case "printFolder":
			$libs->printFolder();
			break;
		case "getCarButtons":
			$libs->getCarButtons();
			break;	
		case "getDocButtons":
			$libs->getDocButtons();
			break;
		case "newDoc":
			$libs->newDoc();
			break;
		case "checkDoc":
			$libs->checkDoc();
			break;	
		case "deleteDoc":
			$libs->deleteDoc();
			break;			
		case "newFolder":
			$libs->newFolder();
			break;	
		case "deleteFolder":
			$libs->deleteFolder();
			break;	
		case "checkDocRename":
			$libs->checkDocRename();
			break;
		case "renameDoc":
			$libs->renameDoc();
			break;
		case "renameFolder":
			$libs->renameFolder();
			break;
		case "newFolderDocs":
			$libs->newFolderDocs();
			break;													
	}
}

?>