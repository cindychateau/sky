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
require_once($ruta."include/PHPExcel/PHPExcel.php");
$module = 8;

class Libs extends Common {

	function saveRecord() {
		global $ruta;
		if(!isset($_SESSION)){
			@session_start();
		}

		$json = array();
		$json["msg"] = "Todos los campos son obligatorios.";
		$json["error"] = false;
		$json["focus"] = "";

		$obligatorios = array("password", "confirmacion");

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

		if(!$json['error']) {
			$foundNumber = false;
			$foundChars = false;
			$numbers = "1234567890";
			$chars = "ABCDEFGHIJKLMNÑOPQRSTUVWXYZ";
			$password = $_POST['password'];
		
			for($i = 0; $i < strlen($password); $i++){
				
				if(!$foundNumber && strpos($numbers, $password[$i]) !== false){
					$foundNumber = true;
				}
			}
			
			for($i = 0; $i < strlen($password); $i++){
				
				if(!$foundChars && strpos($chars, $password[$i]) !== false){
					$foundChars = true;
				}
			}
			
			if(!$foundNumber){
				$json["error"] = true;
				$json["msg"] = "La contraseña debe contener al menos un número (0-9)";
				$json["focus"] = "password";
			}
			elseif(!$foundChars){
				$json["error"] = true;
				$json["msg"] = "La contraseña debe contener al menos una letra mayúscula (A-Z)";
				$json["focus"] = "password";
			}
			elseif($password != $_POST['confirmacion']){
				$json["msg"] = "Las contraseñas no coinciden";
				$json["error"] = true;
				$json["focus"] = "password";
			}
		}

		if(!$json["error"]) {
			$db = $this->_conexion;
			$db->beginTransaction();

			$sql = "UPDATE SISTEMA_USUARIO SET SIU_PASSWORD = ?
						 WHERE SIU_ID = ?";

			$pass_encr = $this->encrypt($_POST['password']);

			$values = array($pass_encr,
							$_SESSION["sky"]["userid"]);	

			$consulta = $db->prepare($sql);

			try {
				$consulta->execute($values);

			} catch(PDOException $e) {
				$db->rollBack();
				die($e->getMessage());
			}

			$db->commit();
			$json['msg'] = 'Contraseña cambiada con éxito.';
		}

		echo json_encode($json);
	}

	function getReport() {
		$json = array();
		$json['error'] = false;
		$json['msg'] = '';
		$json['reporte'] = '';

		if(!isset($_SESSION)){
			@session_start();
		}

		$sup_id = $_SESSION["sky"]["userprofile"];
		$siu_id = $_SESSION["sky"]["userid"];
		$cli_id = $_SESSION["sky"]["cli_id"];

		$db = $this->_conexion;

		//Si es daemon imprimimos todas las carpetas de los clientes
		if($sup_id == 1) {
			
			//Revisamos los clientes
			$sql_cl = "SELECT cli_id, prefijo FROM clientes ORDER BY prefijo ASC";
			$consulta = $db->prepare($sql_cl);
			$consulta->execute();
			$clientes = $consulta->fetchAll(PDO::FETCH_ASSOC);

			foreach ($clientes as $cliente) {

				//if($this->hasPermission(0, $cliente['cli_id'], 1)) {

					//Revisamos si tiene Carpetas hijo
					$sql_car = "SELECT COUNT(car_id) AS num_car FROM carpetas WHERE nivel = 0 AND cli_id = ?";
					$values_car = array($cliente['cli_id']);
					$consulta_car = $db->prepare($sql_car);
					$consulta_car->execute($values_car);
					$num_car = $consulta_car->fetch(PDO::FETCH_ASSOC);

					//Revisamos si tiene archivos hijo
					$sql_ar = "SELECT COUNT(doc_id) as num_ar, SUM(size) as peso FROM documentos WHERE cli_id = ?";
					$values_ar = array($cliente['cli_id']);
					$consulta_ar = $db->prepare($sql_ar);
					$consulta_ar->execute($values_ar);
					$num_ar = $consulta_ar->fetch(PDO::FETCH_ASSOC);

					//Revisamos los documentos (Páginas)
					$sql_doc = "SELECT SUM(num_docs) as paginas FROM documentos WHERE cli_id = ?";
					$values_doc = array($cliente['cli_id']);
					$consulta_doc = $db->prepare($sql_doc);
					$consulta_doc->execute($values_doc);
					$numero = $consulta_doc->fetch(PDO::FETCH_ASSOC);
					/*foreach ($archivos as $archivo) {
						$sql_doc = 'SELECT COUNT(dde_id) as num_pag FROM documentos_detalles WHERE doc_id = ?';
						$values_doc = array($archivo['doc_id']);
						$consulta_doc = $db->prepare($sql_doc);
						$consulta_doc->execute($values_doc);
						$numero = $consulta_doc->fetch(PDO::FETCH_ASSOC);
						$num_paginas += $numero['num_pag'];
					}*/

					$num_ar['peso'] = (is_null($num_ar['peso']) ? 0 : $num_ar['peso']);

					$json['reporte'] .= '<tr>
											<td>'.$cliente['prefijo'].'</td>
											<!--td>'.$num_car['num_car'].'</td-->
											<td>'.$num_ar['num_ar'].'</td>
											<td>'.$numero['paginas'].'</td>
											<td>'.$this->formatBytesToOther($num_ar['peso']).'</td>
										 </tr>';

				//}					
			}


		} else {

			$sql_cl = "SELECT cli_id, prefijo FROM clientes WHERE cli_id = ? ORDER BY prefijo ASC";
			$consulta = $db->prepare($sql_cl);
			$consulta->execute(array($cli_id));
			$cliente = $consulta->fetch(PDO::FETCH_ASSOC);

			//Revisamos si tiene Carpetas hijo
			$sql_car = "SELECT COUNT(car_id) AS num_car FROM carpetas WHERE nivel = 0 AND cli_id = ?";
			$values_car = array($cli_id);
			$consulta_car = $db->prepare($sql_car);
			$consulta_car->execute($values_car);
			$num_car = $consulta_car->fetch(PDO::FETCH_ASSOC);

			//Revisamos si tiene archivos hijo
			$sql_ar = "SELECT COUNT(doc_id) as num_ar, SUM(size) as peso FROM documentos WHERE cli_id = ?";
			$values_ar = array($cli_id);
			$consulta_ar = $db->prepare($sql_ar);
			$consulta_ar->execute($values_ar);
			$num_ar = $consulta_ar->fetch(PDO::FETCH_ASSOC);

			//Revisamos los documentos (Páginas)
			/*$sql_ar = "SELECT doc_id FROM documentos WHERE cli_id = ?";
			$values_ar = array($cli_id);
			$consulta_ar = $db->prepare($sql_ar);
			$consulta_ar->execute($values_ar);
			$archivos = $consulta_ar->fetchAll(PDO::FETCH_ASSOC);*/
			$num_paginas = 0;
			/*foreach ($archivos as $archivo) {
				$sql_doc = 'SELECT COUNT(dde_id) as num_pag FROM documentos_detalles WHERE doc_id = ?';
				$values_doc = array($archivo['doc_id']);
				$consulta_doc = $db->prepare($sql_doc);
				$consulta_doc->execute($values_doc);
				$numero = $consulta_doc->fetch(PDO::FETCH_ASSOC);
				$num_paginas += $numero['num_pag'];
			}*/

			$num_ar['peso'] = (is_null($num_ar['peso']) ? 0 : $num_ar['peso']);

			$json['reporte'] .= '<tr>
									<td>'.($num_car['num_car'] > 0 ? '<a href="carpeta.php?c=0">'.$cliente['prefijo'].'</a>' : $cliente['prefijo']).'</td>
									<!--td>'.$num_car['num_car'].'</td-->
									<td>'.$num_ar['num_ar'].'</td>
									<td>'.$num_paginas.'</td>
									<td>'.$this->formatBytesToOther($num_ar['peso']).'</td>
								 </tr>';
		}


		echo json_encode($json);
	}

	function getReportCarpeta() {
		$json = array();
		$json['error'] = false;
		$json['msg'] = '';
		$json['reporte'] = '';

		if(!isset($_SESSION)){
			@session_start();
		}

		$sup_id = $_SESSION["sky"]["userprofile"];
		$siu_id = $_SESSION["sky"]["userid"];
		$cli_id = $_SESSION["sky"]["cli_id"];

		$db = $this->_conexion;

		if($_POST['cl'] != -1 && $sup_id == 1) {

			//SU RUTA -> es un cliente en específico
			$sql_ruta = "SELECT prefijo FROM clientes WHERE cli_id = ?";
			$values_ruta = array($_POST['cl']);
			$consulta_ruta = $db->prepare($sql_ruta);
			$consulta_ruta->execute($values_ruta);
			$row_ruta = $consulta_ruta->fetch(PDO::FETCH_ASSOC);
			$json['ruta'] = $row_ruta['prefijo'];

			$json['back'] = 'index.php';

			//De un cliente en específico
			$sql_car = "SELECT * FROM carpetas WHERE nivel = 0 AND cli_id = ? ORDER BY nombre ASC";
			$values_car = array($_POST['cl']);
			$consulta_car = $db->prepare($sql_car);
			$consulta_car->execute($values_car);
			if ($consulta_car->rowCount()) {
				$carpetas = $consulta_car->fetchAll(PDO::FETCH_ASSOC);
				foreach ($carpetas as $carpeta) {
					//Revisamos si tiene Carpetas hijo
					$sql_car = "SELECT COUNT(car_id) AS num_car FROM carpetas WHERE nivel = ?";
					$values_car = array($carpeta['car_id']);
					$consulta_car = $db->prepare($sql_car);
					$consulta_car->execute($values_car);
					$num_car = $consulta_car->fetch(PDO::FETCH_ASSOC);

					//Revisamos recursivamente la cantidad de documentos y peso
					$num_ar_empty = array();
					$num_ar_empty['num_ar'] = 0;
					$num_ar_empty['peso'] = 0;
					$num_ar_empty['num_paginas'] = 0;
					$num_ar = $this->getCantidadesRec($carpeta['car_id'], $num_ar_empty);

					$json['reporte'] .= '<tr>
											<td>'.($num_car['num_car'] > 0 ? '<a href="carpeta.php?c='.$carpeta['car_id'].'">'.$carpeta['nombre'].'</a>' : $carpeta['nombre']).'</td>
											<!--td>'.$num_car['num_car'].'</td-->
											<td>'.$num_ar['num_ar'].'</td>
											<td>'.$num_ar['num_paginas'].'</td>
											<td>'.$this->formatBytesToOther($num_ar['peso']).'</td>
										 </tr>';

				}
			}

			//Revisamos archivos solos
			$sql_ar = "SELECT COUNT(doc_id) as num_ar, SUM(size) as peso FROM documentos WHERE car_id = 0 AND cli_id = ?";
			$values_ar = array($_POST['cl']);
			$consulta_ar = $db->prepare($sql_ar);
			$consulta_ar->execute($values_ar);
			$num_ar = $consulta_ar->fetch(PDO::FETCH_ASSOC);

			//Revisamos los documentos (Páginas)
			/*$sql_ar = "SELECT doc_id FROM documentos WHERE car_id = 0 AND cli_id = ?";
			$values_ar = array($_POST['cl']);
			$consulta_ar = $db->prepare($sql_ar);
			$consulta_ar->execute($values_ar);
			$archivos = $consulta_ar->fetchAll(PDO::FETCH_ASSOC);*/
			$num_paginas = 0;
			/*foreach ($archivos as $archivo) {
				$sql_doc = 'SELECT COUNT(dde_id) as num_pag FROM documentos_detalles WHERE doc_id = ?';
				$values_doc = array($archivo['doc_id']);
				$consulta_doc = $db->prepare($sql_doc);
				$consulta_doc->execute($values_doc);
				$numero = $consulta_doc->fetch(PDO::FETCH_ASSOC);
				$num_paginas += $numero['num_pag'];
			}*/

			$num_ar['peso'] = (is_null($num_ar['peso']) ? 0 : $num_ar['peso']);

			$json['reporte'] .= '<tr>
									<td>Archivos*</td>
									<!--td>-</td-->
									<td>'.$num_ar['num_ar'].'</td>
									<td>'.$num_paginas.'</td>
									<td>'.$this->formatBytesToOther($num_ar['peso']).'</td>
								 </tr>';


		} else {

			if($sup_id == 1) {

				//Consultamos ruta
				$sql_ruta = 'SELECT nombre, ruta, nivel, cli_id FROM carpetas WHERE car_id = ?';
				$values_ruta = array($_POST['c']);
				$consulta_ruta = $db->prepare($sql_ruta);
				$consulta_ruta->execute($values_ruta);
				$row_ruta = $consulta_ruta->fetch(PDO::FETCH_ASSOC);
				$rn = 1;
				$json['ruta'] = $row_ruta['ruta'].$row_ruta['nombre'];
				$json['ruta'] = str_replace('archivos/', '', $json['ruta'], $rn);

				$json['back'] = ($row_ruta['nivel'] == 0 ? 'carpeta.php?c='.$row_ruta['nivel'].'&cl='.$row_ruta['cli_id'] : 'carpeta.php?c='.$row_ruta['nivel']);

				$sql_car = "SELECT * FROM carpetas WHERE nivel = ? ORDER BY nombre ASC";
				$values_car = array($_POST['c']);
				$consulta_car = $db->prepare($sql_car);
				$consulta_car->execute($values_car);
				if ($consulta_car->rowCount()) {
					$carpetas = $consulta_car->fetchAll(PDO::FETCH_ASSOC);
					foreach ($carpetas as $carpeta) {
						//Revisamos si tiene Carpetas hijo
						$sql_car = "SELECT COUNT(car_id) AS num_car FROM carpetas WHERE nivel = ?";
						$values_car = array($carpeta['car_id']);
						$consulta_car = $db->prepare($sql_car);
						$consulta_car->execute($values_car);
						$num_car = $consulta_car->fetch(PDO::FETCH_ASSOC);

						//Revisamos recursivamente la cantidad de documentos y peso
						$num_ar_empty = array();
						$num_ar_empty['num_ar'] = 0;
						$num_ar_empty['peso'] = 0;
						$num_ar_empty['num_paginas'] = 0;
						$num_ar = $this->getCantidadesRec($carpeta['car_id'], $num_ar_empty);

						$json['reporte'] .= '<tr>
												<td>'.($num_car['num_car'] > 0 ? '<a href="carpeta.php?c='.$carpeta['car_id'].'">'.$carpeta['nombre'].'</a>' : $carpeta['nombre']).'</td>
												<!--td>'.$num_car['num_car'].'</td-->
												<td>'.$num_ar['num_ar'].'</td>
												<td>'.$num_ar['num_paginas'].'</td>
												<td>'.$this->formatBytesToOther($num_ar['peso']).'</td>
											 </tr>';

					}
				}

				//Revisamos archivos solos
				$sql_ar = "SELECT COUNT(doc_id) as num_ar, SUM(size) as peso FROM documentos WHERE car_id = ?";
				$values_ar = array($_POST['c']);
				$consulta_ar = $db->prepare($sql_ar);
				$consulta_ar->execute($values_ar);
				$num_ar = $consulta_ar->fetch(PDO::FETCH_ASSOC);

				//Revisamos los documentos (Páginas)
				/*$sql_ar = "SELECT doc_id FROM documentos WHERE car_id = ?";
				$values_ar = array($_POST['c']);
				$consulta_ar = $db->prepare($sql_ar);
				$consulta_ar->execute($values_ar);
				$archivos = $consulta_ar->fetchAll(PDO::FETCH_ASSOC);*/
				$num_paginas = 0;
				/*foreach ($archivos as $archivo) {
					$sql_doc = 'SELECT COUNT(dde_id) as num_pag FROM documentos_detalles WHERE doc_id = ?';
					$values_doc = array($archivo['doc_id']);
					$consulta_doc = $db->prepare($sql_doc);
					$consulta_doc->execute($values_doc);
					$numero = $consulta_doc->fetch(PDO::FETCH_ASSOC);
					$num_paginas += $numero['num_pag'];
				}*/

				$num_ar['peso'] = (is_null($num_ar['peso']) ? 0 : $num_ar['peso']);

				$json['reporte'] .= '<tr>
										<td>Archivos*</td>
										<!--td>-</td-->
										<td>'.$num_ar['num_ar'].'</td>
										<td>'.$num_paginas.'</td>
										<td>'.$this->formatBytesToOther($num_ar['peso']).'</td>
									 </tr>';
			} else {

				//Consultamos ruta
				$sql_ruta = 'SELECT nombre, ruta, nivel FROM carpetas WHERE car_id = ? AND cli_id = ?';
				$values_ruta = array($_POST['c'], $cli_id);
				$consulta_ruta = $db->prepare($sql_ruta);
				$consulta_ruta->execute($values_ruta);
				$row_ruta = $consulta_ruta->fetch(PDO::FETCH_ASSOC);
				$rn = 1;
				$json['ruta'] = $row_ruta['ruta'].$row_ruta['nombre'];
				$json['ruta'] = str_replace('archivos/', '', $json['ruta'], $rn);

				$json['back'] = ($row_ruta['nivel'] == 0 ? 'index.php' : 'carpeta.php?c='.$row_ruta['nivel']);

				$sql_car = "SELECT * FROM carpetas WHERE nivel = ? AND cli_id = ? ORDER BY nombre ASC";
				$values_car = array($_POST['c'], $cli_id);
				$consulta_car = $db->prepare($sql_car);
				$consulta_car->execute($values_car);
				if ($consulta_car->rowCount()) {
					$carpetas = $consulta_car->fetchAll(PDO::FETCH_ASSOC);
					foreach ($carpetas as $carpeta) {
						//Revisamos si tiene Carpetas hijo
						$sql_car = "SELECT COUNT(car_id) AS num_car FROM carpetas WHERE nivel = ?";
						$values_car = array($carpeta['car_id']);
						$consulta_car = $db->prepare($sql_car);
						$consulta_car->execute($values_car);
						$num_car = $consulta_car->fetch(PDO::FETCH_ASSOC);

						//Revisamos recursivamente la cantidad de documentos y peso
						$num_ar_empty = array();
						$num_ar_empty['num_ar'] = 0;
						$num_ar_empty['peso'] = 0;
						$num_ar_empty['num_paginas'] = 0;
						$num_ar = $this->getCantidadesRec($carpeta['car_id'], $num_ar_empty);

						$json['reporte'] .= '<tr>
												<td>'.($num_car['num_car'] > 0 ? '<a href="carpeta.php?c='.$carpeta['car_id'].'">'.$carpeta['nombre'].'</a>' : $carpeta['nombre']).'</td>
												<!--td>'.$num_car['num_car'].'</td-->
												<td>'.$num_ar['num_ar'].'</td>
												<td>'.$num_ar['num_paginas'].'</td>
												<td>'.$this->formatBytesToOther($num_ar['peso']).'</td>
											 </tr>';

					}
				}

				//Revisamos archivos solos
				$sql_ar = "SELECT COUNT(doc_id) as num_ar, SUM(size) as peso FROM documentos WHERE car_id = ? AND cli_id = ?";
				$values_ar = array($_POST['c'], $cli_id);
				$consulta_ar = $db->prepare($sql_ar);
				$consulta_ar->execute($values_ar);
				$num_ar = $consulta_ar->fetch(PDO::FETCH_ASSOC);

				//Revisamos los documentos (Páginas)
				/*$sql_ar = "SELECT doc_id FROM documentos WHERE car_id = ? AND cli_id = ?";
				$values_ar = array($_POST['c'], $cli_id);
				$consulta_ar = $db->prepare($sql_ar);
				$consulta_ar->execute($values_ar);
				$archivos = $consulta_ar->fetchAll(PDO::FETCH_ASSOC);*/
				$num_paginas = 0;
				/*foreach ($archivos as $archivo) {
					$sql_doc = 'SELECT COUNT(dde_id) as num_pag FROM documentos_detalles WHERE doc_id = ?';
					$values_doc = array($archivo['doc_id']);
					$consulta_doc = $db->prepare($sql_doc);
					$consulta_doc->execute($values_doc);
					$numero = $consulta_doc->fetch(PDO::FETCH_ASSOC);
					$num_paginas += $numero['num_pag'];
				}*/

				$num_ar['peso'] = (is_null($num_ar['peso']) ? 0 : $num_ar['peso']);

				$json['reporte'] .= '<tr>
										<td>Archivos*</td>
										<!--td>-</td-->
										<td>'.$num_ar['num_ar'].'</td>
										<td>'.$num_paginas.'</td>
										<td>'.$this->formatBytesToOther($num_ar['peso']).'</td>
									 </tr>';
			}

		}

		echo json_encode($json);
	}

	function getCantidadesRec($car_id, $num_ar) {
		//Verificamos la cantidad de archivos
		$db = $this->_conexion;
		$sql_ar = "SELECT COUNT(doc_id) as num_ar, SUM(size) as peso FROM documentos WHERE car_id = ?";
		$values_ar = array($car_id);
		$consulta_ar = $db->prepare($sql_ar);
		$consulta_ar->execute($values_ar);
		$row_ar = $consulta_ar->fetch(PDO::FETCH_ASSOC);

		$num_ar['num_ar'] += $row_ar['num_ar'];
		$num_ar['peso'] += $row_ar['peso'];

		//Revisamos los documentos (Páginas)
		/*$sql_ar = "SELECT doc_id FROM documentos WHERE car_id = ?";
		$values_ar = array($car_id);
		$consulta_ar = $db->prepare($sql_ar);
		$consulta_ar->execute($values_ar);
		$archivos = $consulta_ar->fetchAll(PDO::FETCH_ASSOC);
		foreach ($archivos as $archivo) {
			$sql_doc = 'SELECT COUNT(dde_id) as num_pag FROM documentos_detalles WHERE doc_id = ?';
			$values_doc = array($archivo['doc_id']);
			$consulta_doc = $db->prepare($sql_doc);
			$consulta_doc->execute($values_doc);
			$numero = $consulta_doc->fetch(PDO::FETCH_ASSOC);
			$num_ar['num_paginas'] += $numero['num_pag'];
		}*/

		//Verificamos las carpetas hijo
		$sql_car = "SELECT * FROM carpetas WHERE nivel = ?";
		$values_car = array($car_id);
		$consulta_car = $db->prepare($sql_car);
		$consulta_car->execute($values_car);
		if ($consulta_car->rowCount()) {
			$carpetas = $consulta_car->fetchAll(PDO::FETCH_ASSOC);
			foreach ($carpetas as $carpeta) {
				$num_ar = $this->getCantidadesRec($carpeta['car_id'], $num_ar);
			}
		}

		return $num_ar;

	}

	public function formatBytesToOther($size) {
	    $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
	    $factor = floor((strlen($size) - 1) / 3);
	    return number_format($size / pow(1024, $factor), 2) ." ". $units[$factor];
	}

	function getExcel1() {
		$json = array();
		$json['completado'] = false;

		$columns = array("A",
						 "B",
						 "C",
						 "D");

		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getProperties()->setCreator("Sky Consulting Partners")
					 ->setLastModifiedBy("Sky Consulting Partners")
					 ->setTitle("Peso Cantidad")
					 ->setSubject("Peso Cantidad")
					 ->setDescription("Reporte de Peso Cantidad")
					 ->setKeywords("pesos cantidad");

		$styleArray = array(
				        'font' => array(
				            'bold' => true
				        ),
				        'alignment' => array(
				            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				        )
				    );
		$styleArray2 = array('alignment' => array(
				            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				        )
				    );		    			 

		//Hacemos más grande las columnas, bold la primera y text-center
		foreach ($columns as $column) {
			$objPHPExcel->getActiveSheet()->getColumnDimension($column)->setWidth(40);
			$objPHPExcel->getActiveSheet()->getStyle($column."1")->applyFromArray($styleArray);
			$objPHPExcel->getActiveSheet()->getStyle($column)->applyFromArray($styleArray2);
		}

		//$objPHPExcel->getStyle("M")->getNumberFormat()->setFormatCode('0'); 


		$objPHPExcel->setActiveSheetIndex(0)
		            ->setCellValue('A1', 'CARPETA')
		            ->setCellValue('B1', 'CANTIDAD DE ARCHIVOS')
		            ->setCellValue('C1', 'CANTIDAD DE DOCUMENTOS')
		            ->setCellValue('D1', 'PESO');


		/*DATOS*/
		if(!isset($_SESSION)){
			@session_start();
		}

		$sup_id = $_SESSION["sky"]["userprofile"];
		$siu_id = $_SESSION["sky"]["userid"];
		$cli_id = $_SESSION["sky"]["cli_id"];

		$db = $this->_conexion;
		$n = 2;

		//Si es daemon imprimimos todas las carpetas de los clientes
		if($sup_id == 1) {
			
			//Revisamos los clientes
			$sql_cl = "SELECT cli_id, prefijo FROM clientes ORDER BY prefijo ASC";
			$consulta = $db->prepare($sql_cl);
			$consulta->execute();
			$clientes = $consulta->fetchAll(PDO::FETCH_ASSOC);

			foreach ($clientes as $cliente) {

				//if($this->hasPermission(0, $cliente['cli_id'], 1)) {

					//Revisamos si tiene Carpetas hijo
					$sql_car = "SELECT COUNT(car_id) AS num_car FROM carpetas WHERE nivel = 0 AND cli_id = ?";
					$values_car = array($cliente['cli_id']);
					$consulta_car = $db->prepare($sql_car);
					$consulta_car->execute($values_car);
					$num_car = $consulta_car->fetch(PDO::FETCH_ASSOC);

					//Revisamos si tiene archivos hijo
					$sql_ar = "SELECT COUNT(doc_id) as num_ar, SUM(size) as peso FROM documentos WHERE cli_id = ?";
					$values_ar = array($cliente['cli_id']);
					$consulta_ar = $db->prepare($sql_ar);
					$consulta_ar->execute($values_ar);
					$num_ar = $consulta_ar->fetch(PDO::FETCH_ASSOC);

					//Revisamos los documentos (Páginas)
					$sql_ar = "SELECT doc_id FROM documentos WHERE cli_id = ?";
					$values_ar = array($cliente['cli_id']);
					$consulta_ar = $db->prepare($sql_ar);
					$consulta_ar->execute($values_ar);
					$archivos = $consulta_ar->fetchAll(PDO::FETCH_ASSOC);
					$num_paginas = 0;
					foreach ($archivos as $archivo) {
						$sql_doc = 'SELECT COUNT(dde_id) as num_pag FROM documentos_detalles WHERE doc_id = ?';
						$values_doc = array($archivo['doc_id']);
						$consulta_doc = $db->prepare($sql_doc);
						$consulta_doc->execute($values_doc);
						$numero = $consulta_doc->fetch(PDO::FETCH_ASSOC);
						$num_paginas += $numero['num_pag'];
					}

					$num_ar['peso'] = (is_null($num_ar['peso']) ? 0 : $num_ar['peso']);

					//AGREGAMOS LA ROW
					$objPHPExcel->setActiveSheetIndex(0)
			            ->setCellValue('A'.$n, $cliente['prefijo'])
			            ->setCellValue('B'.$n, $num_ar['num_ar'])
			            ->setCellValue('C'.$n, $num_paginas)
			            ->setCellValue('D'.$n, $this->formatBytesToOther($num_ar['peso']));		

			        $n++;   			 

				//}					
			}


		} else {

			$sql_cl = "SELECT cli_id, prefijo FROM clientes WHERE cli_id = ? ORDER BY prefijo ASC";
			$consulta = $db->prepare($sql_cl);
			$consulta->execute(array($cli_id));
			$cliente = $consulta->fetch(PDO::FETCH_ASSOC);

			//Revisamos si tiene Carpetas hijo
			$sql_car = "SELECT COUNT(car_id) AS num_car FROM carpetas WHERE nivel = 0 AND cli_id = ?";
			$values_car = array($cli_id);
			$consulta_car = $db->prepare($sql_car);
			$consulta_car->execute($values_car);
			$num_car = $consulta_car->fetch(PDO::FETCH_ASSOC);

			//Revisamos si tiene archivos hijo
			$sql_ar = "SELECT COUNT(doc_id) as num_ar, SUM(size) as peso FROM documentos WHERE cli_id = ?";
			$values_ar = array($cli_id);
			$consulta_ar = $db->prepare($sql_ar);
			$consulta_ar->execute($values_ar);
			$num_ar = $consulta_ar->fetch(PDO::FETCH_ASSOC);

			//Revisamos los documentos (Páginas)
			$sql_ar = "SELECT doc_id FROM documentos WHERE cli_id = ?";
			$values_ar = array($cli_id);
			$consulta_ar = $db->prepare($sql_ar);
			$consulta_ar->execute($values_ar);
			$archivos = $consulta_ar->fetchAll(PDO::FETCH_ASSOC);
			$num_paginas = 0;
			foreach ($archivos as $archivo) {
				$sql_doc = 'SELECT COUNT(dde_id) as num_pag FROM documentos_detalles WHERE doc_id = ?';
				$values_doc = array($archivo['doc_id']);
				$consulta_doc = $db->prepare($sql_doc);
				$consulta_doc->execute($values_doc);
				$numero = $consulta_doc->fetch(PDO::FETCH_ASSOC);
				$num_paginas += $numero['num_pag'];
			}

			$num_ar['peso'] = (is_null($num_ar['peso']) ? 0 : $num_ar['peso']);

			//AGREGAMOS LA ROW
			$objPHPExcel->setActiveSheetIndex(0)
	            ->setCellValue('A'.$n, $cliente['prefijo'])
	            ->setCellValue('B'.$n, $num_ar['num_ar'])
	            ->setCellValue('C'.$n, $num_paginas)
	            ->setCellValue('D'.$n, $this->formatBytesToOther($num_ar['peso']));


	    	$n++;					 
		}
		

		$objPHPExcel->getActiveSheet()->setTitle('PesoCantidad');  
		$objPHPExcel->setActiveSheetIndex(0);    
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save(str_replace('Libs.php', 'peso-cantidad.xlsx', __FILE__));      


		$json['completado'] = true;

		echo json_encode($json);
	}

	function getExcel2() {
		$json = array();
		$json['completado'] = false;

		$columns = array("A",
						 "B",
						 "C",
						 "D");

		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getProperties()->setCreator("Sky Consulting Partners")
					 ->setLastModifiedBy("Sky Consulting Partners")
					 ->setTitle("Peso Cantidad")
					 ->setSubject("Peso Cantidad")
					 ->setDescription("Reporte de Peso Cantidad")
					 ->setKeywords("pesos cantidad");

		$styleArray = array(
				        'font' => array(
				            'bold' => true
				        ),
				        'alignment' => array(
				            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				        )
				    );
		$styleArray2 = array('alignment' => array(
				            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				        )
				    );		    			 

		//Hacemos más grande las columnas, bold la primera y text-center
		foreach ($columns as $column) {
			$objPHPExcel->getActiveSheet()->getColumnDimension($column)->setWidth(40);
			$objPHPExcel->getActiveSheet()->getStyle($column."1")->applyFromArray($styleArray);
			$objPHPExcel->getActiveSheet()->getStyle($column)->applyFromArray($styleArray2);
		}

		//$objPHPExcel->getStyle("M")->getNumberFormat()->setFormatCode('0'); 


		$objPHPExcel->setActiveSheetIndex(0)
		            ->setCellValue('A1', 'CARPETA')
		            ->setCellValue('B1', 'CANTIDAD DE ARCHIVOS')
		            ->setCellValue('C1', 'CANTIDAD DE DOCUMENTOS')
		            ->setCellValue('D1', 'PESO');


		/*DATOS*/
		if(!isset($_SESSION)){
			@session_start();
		}

		$sup_id = $_SESSION["sky"]["userprofile"];
		$siu_id = $_SESSION["sky"]["userid"];
		$cli_id = $_SESSION["sky"]["cli_id"];

		$db = $this->_conexion;
		$n = 2;

		if($_POST['cl'] != -1 && $sup_id == 1) {

			//SU RUTA -> es un cliente en específico
			$sql_ruta = "SELECT prefijo FROM clientes WHERE cli_id = ?";
			$values_ruta = array($_POST['cl']);
			$consulta_ruta = $db->prepare($sql_ruta);
			$consulta_ruta->execute($values_ruta);
			$row_ruta = $consulta_ruta->fetch(PDO::FETCH_ASSOC);
			$json['ruta'] = $row_ruta['prefijo'];

			$json['back'] = 'index.php';

			//De un cliente en específico
			$sql_car = "SELECT * FROM carpetas WHERE nivel = 0 AND cli_id = ? ORDER BY nombre ASC";
			$values_car = array($_POST['cl']);
			$consulta_car = $db->prepare($sql_car);
			$consulta_car->execute($values_car);
			if ($consulta_car->rowCount()) {
				$carpetas = $consulta_car->fetchAll(PDO::FETCH_ASSOC);
				foreach ($carpetas as $carpeta) {
					//Revisamos si tiene Carpetas hijo
					$sql_car = "SELECT COUNT(car_id) AS num_car FROM carpetas WHERE nivel = ?";
					$values_car = array($carpeta['car_id']);
					$consulta_car = $db->prepare($sql_car);
					$consulta_car->execute($values_car);
					$num_car = $consulta_car->fetch(PDO::FETCH_ASSOC);

					//Revisamos recursivamente la cantidad de documentos y peso
					$num_ar_empty = array();
					$num_ar_empty['num_ar'] = 0;
					$num_ar_empty['peso'] = 0;
					$num_ar_empty['num_paginas'] = 0;
					$num_ar = $this->getCantidadesRec($carpeta['car_id'], $num_ar_empty);

					$objPHPExcel->setActiveSheetIndex(0)
			            ->setCellValue('A'.$n, $carpeta['nombre'])
			            ->setCellValue('B'.$n, $num_ar['num_ar'])
			            ->setCellValue('C'.$n, $num_ar['num_paginas'])
			            ->setCellValue('D'.$n, $this->formatBytesToOther($num_ar['peso']));

			    	$n++;					 

				}
			}

			//Revisamos archivos solos
			$sql_ar = "SELECT COUNT(doc_id) as num_ar, SUM(size) as peso FROM documentos WHERE car_id = 0 AND cli_id = ?";
			$values_ar = array($_POST['cl']);
			$consulta_ar = $db->prepare($sql_ar);
			$consulta_ar->execute($values_ar);
			$num_ar = $consulta_ar->fetch(PDO::FETCH_ASSOC);

			//Revisamos los documentos (Páginas)
			$sql_ar = "SELECT doc_id FROM documentos WHERE car_id = 0 AND cli_id = ?";
			$values_ar = array($_POST['cl']);
			$consulta_ar = $db->prepare($sql_ar);
			$consulta_ar->execute($values_ar);
			$archivos = $consulta_ar->fetchAll(PDO::FETCH_ASSOC);
			$num_paginas = 0;
			foreach ($archivos as $archivo) {
				$sql_doc = 'SELECT COUNT(dde_id) as num_pag FROM documentos_detalles WHERE doc_id = ?';
				$values_doc = array($archivo['doc_id']);
				$consulta_doc = $db->prepare($sql_doc);
				$consulta_doc->execute($values_doc);
				$numero = $consulta_doc->fetch(PDO::FETCH_ASSOC);
				$num_paginas += $numero['num_pag'];
			}

			$num_ar['peso'] = (is_null($num_ar['peso']) ? 0 : $num_ar['peso']);

			$objPHPExcel->setActiveSheetIndex(0)
			            ->setCellValue('A'.$n, 'Archivos*')
			            ->setCellValue('B'.$n, $num_ar['num_ar'])
			            ->setCellValue('C'.$n, $num_paginas)
			            ->setCellValue('D'.$n, $this->formatBytesToOther($num_ar['peso']));

			$n++;					 


		} else {

			if($sup_id == 1) {

				//Consultamos ruta
				$sql_ruta = 'SELECT nombre, ruta, nivel, cli_id FROM carpetas WHERE car_id = ?';
				$values_ruta = array($_POST['c']);
				$consulta_ruta = $db->prepare($sql_ruta);
				$consulta_ruta->execute($values_ruta);
				$row_ruta = $consulta_ruta->fetch(PDO::FETCH_ASSOC);
				$rn = 1;
				$json['ruta'] = $row_ruta['ruta'].$row_ruta['nombre'];
				$json['ruta'] = str_replace('archivos/', '', $json['ruta'], $rn);

				$json['back'] = ($row_ruta['nivel'] == 0 ? 'carpeta.php?c='.$row_ruta['nivel'].'&cl='.$row_ruta['cli_id'] : 'carpeta.php?c='.$row_ruta['nivel']);

				$sql_car = "SELECT * FROM carpetas WHERE nivel = ? ORDER BY nombre ASC";
				$values_car = array($_POST['c']);
				$consulta_car = $db->prepare($sql_car);
				$consulta_car->execute($values_car);
				if ($consulta_car->rowCount()) {
					$carpetas = $consulta_car->fetchAll(PDO::FETCH_ASSOC);
					foreach ($carpetas as $carpeta) {
						//Revisamos si tiene Carpetas hijo
						$sql_car = "SELECT COUNT(car_id) AS num_car FROM carpetas WHERE nivel = ?";
						$values_car = array($carpeta['car_id']);
						$consulta_car = $db->prepare($sql_car);
						$consulta_car->execute($values_car);
						$num_car = $consulta_car->fetch(PDO::FETCH_ASSOC);

						//Revisamos recursivamente la cantidad de documentos y peso
						$num_ar_empty = array();
						$num_ar_empty['num_ar'] = 0;
						$num_ar_empty['peso'] = 0;
						$num_ar_empty['num_paginas'] = 0;
						$num_ar = $this->getCantidadesRec($carpeta['car_id'], $num_ar_empty);

						$objPHPExcel->setActiveSheetIndex(0)
						            ->setCellValue('A'.$n, $carpeta['nombre'])
						            ->setCellValue('B'.$n, $num_ar['num_ar'])
						            ->setCellValue('C'.$n, $num_ar['num_paginas'])
						            ->setCellValue('D'.$n, $this->formatBytesToOther($num_ar['peso']));

						$n++;					 

					}
				}

				//Revisamos archivos solos
				$sql_ar = "SELECT COUNT(doc_id) as num_ar, SUM(size) as peso FROM documentos WHERE car_id = ?";
				$values_ar = array($_POST['c']);
				$consulta_ar = $db->prepare($sql_ar);
				$consulta_ar->execute($values_ar);
				$num_ar = $consulta_ar->fetch(PDO::FETCH_ASSOC);

				//Revisamos los documentos (Páginas)
				$sql_ar = "SELECT doc_id FROM documentos WHERE car_id = ?";
				$values_ar = array($_POST['c']);
				$consulta_ar = $db->prepare($sql_ar);
				$consulta_ar->execute($values_ar);
				$archivos = $consulta_ar->fetchAll(PDO::FETCH_ASSOC);
				$num_paginas = 0;
				foreach ($archivos as $archivo) {
					$sql_doc = 'SELECT COUNT(dde_id) as num_pag FROM documentos_detalles WHERE doc_id = ?';
					$values_doc = array($archivo['doc_id']);
					$consulta_doc = $db->prepare($sql_doc);
					$consulta_doc->execute($values_doc);
					$numero = $consulta_doc->fetch(PDO::FETCH_ASSOC);
					$num_paginas += $numero['num_pag'];
				}

				$num_ar['peso'] = (is_null($num_ar['peso']) ? 0 : $num_ar['peso']);

				$objPHPExcel->setActiveSheetIndex(0)
				            ->setCellValue('A'.$n, 'Archivos*')
				            ->setCellValue('B'.$n, $num_ar['num_ar'])
				            ->setCellValue('C'.$n, $num_paginas)
				            ->setCellValue('D'.$n, $this->formatBytesToOther($num_ar['peso']));

				$n++;

									 
			} else {

				//Consultamos ruta
				$sql_ruta = 'SELECT nombre, ruta, nivel FROM carpetas WHERE car_id = ? AND cli_id = ?';
				$values_ruta = array($_POST['c'], $cli_id);
				$consulta_ruta = $db->prepare($sql_ruta);
				$consulta_ruta->execute($values_ruta);
				$row_ruta = $consulta_ruta->fetch(PDO::FETCH_ASSOC);
				$rn = 1;
				$json['ruta'] = $row_ruta['ruta'].$row_ruta['nombre'];
				$json['ruta'] = str_replace('archivos/', '', $json['ruta'], $rn);

				$json['back'] = ($row_ruta['nivel'] == 0 ? 'index.php' : 'carpeta.php?c='.$row_ruta['nivel']);

				$sql_car = "SELECT * FROM carpetas WHERE nivel = ? AND cli_id = ? ORDER BY nombre ASC";
				$values_car = array($_POST['c'], $cli_id);
				$consulta_car = $db->prepare($sql_car);
				$consulta_car->execute($values_car);
				if ($consulta_car->rowCount()) {
					$carpetas = $consulta_car->fetchAll(PDO::FETCH_ASSOC);
					foreach ($carpetas as $carpeta) {
						//Revisamos si tiene Carpetas hijo
						$sql_car = "SELECT COUNT(car_id) AS num_car FROM carpetas WHERE nivel = ?";
						$values_car = array($carpeta['car_id']);
						$consulta_car = $db->prepare($sql_car);
						$consulta_car->execute($values_car);
						$num_car = $consulta_car->fetch(PDO::FETCH_ASSOC);

						//Revisamos recursivamente la cantidad de documentos y peso
						$num_ar_empty = array();
						$num_ar_empty['num_ar'] = 0;
						$num_ar_empty['peso'] = 0;
						$num_ar_empty['num_paginas'] = 0;
						$num_ar = $this->getCantidadesRec($carpeta['car_id'], $num_ar_empty);


						$objPHPExcel->setActiveSheetIndex(0)
						            ->setCellValue('A'.$n, $carpeta['nombre'])
						            ->setCellValue('B'.$n, $num_ar['num_ar'])
						            ->setCellValue('C'.$n, $num_ar['num_paginas'])
						            ->setCellValue('D'.$n, $this->formatBytesToOther($num_ar['peso']));

						$n++;						 

					}
				}

				//Revisamos archivos solos
				$sql_ar = "SELECT COUNT(doc_id) as num_ar, SUM(size) as peso FROM documentos WHERE car_id = ? AND cli_id = ?";
				$values_ar = array($_POST['c'], $cli_id);
				$consulta_ar = $db->prepare($sql_ar);
				$consulta_ar->execute($values_ar);
				$num_ar = $consulta_ar->fetch(PDO::FETCH_ASSOC);

				//Revisamos los documentos (Páginas)
				$sql_ar = "SELECT doc_id FROM documentos WHERE car_id = ? AND cli_id = ?";
				$values_ar = array($_POST['c'], $cli_id);
				$consulta_ar = $db->prepare($sql_ar);
				$consulta_ar->execute($values_ar);
				$archivos = $consulta_ar->fetchAll(PDO::FETCH_ASSOC);
				$num_paginas = 0;
				foreach ($archivos as $archivo) {
					$sql_doc = 'SELECT COUNT(dde_id) as num_pag FROM documentos_detalles WHERE doc_id = ?';
					$values_doc = array($archivo['doc_id']);
					$consulta_doc = $db->prepare($sql_doc);
					$consulta_doc->execute($values_doc);
					$numero = $consulta_doc->fetch(PDO::FETCH_ASSOC);
					$num_paginas += $numero['num_pag'];
				}

				$num_ar['peso'] = (is_null($num_ar['peso']) ? 0 : $num_ar['peso']);

				$objPHPExcel->setActiveSheetIndex(0)
				            ->setCellValue('A'.$n, 'Archivos*')
				            ->setCellValue('B'.$n, $num_ar['num_ar'])
				            ->setCellValue('C'.$n, $num_paginas)
				            ->setCellValue('D'.$n, $this->formatBytesToOther($num_ar['peso']));

				$n++;


			}

		}
		

		$objPHPExcel->getActiveSheet()->setTitle('PesoCantidad');  
		$objPHPExcel->setActiveSheetIndex(0);    
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save(str_replace('Libs.php', 'peso-cantidad.xlsx', __FILE__));      


		$json['completado'] = true;

		echo json_encode($json);
	}
	
	
}

if(isset($_REQUEST['accion'])){
	//Se inicializa la clase
	$libs = new Libs;
	switch($_REQUEST['accion']){
		case "saveRecord":
			$libs->saveRecord();
			break;	
		case "getReport":
			$libs->getReport();
			break;
		case "getReportCarpeta":
			$libs->getReportCarpeta();
			break;
		case "getExcel1":
			$libs->getExcel1();
			break;
		case "getExcel2":
			$libs->getExcel2();
			break;					
	}
}

?>