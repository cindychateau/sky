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
$module = 9;

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
			$sql_cl = "SELECT cli_id, prefijo, nombre FROM clientes ORDER BY prefijo ASC";
			$consulta = $db->prepare($sql_cl);
			$consulta->execute();
			$clientes = $consulta->fetchAll(PDO::FETCH_ASSOC);

			foreach ($clientes as $cliente) {

				//if($this->hasPermission(0, $cliente['cli_id'], 1)) {

					//Revisamos si tiene archivos hijo
					$sql_ar = "SELECT * 
							   FROM documentos 
							   LEFT JOIN SISTEMA_USUARIO ON SISTEMA_USUARIO.SIU_ID = documentos.siu_id
							   WHERE documentos.cli_id = ? 
							   ORDER BY date DESC LIMIT 0, 1 ";
					$values_ar = array($cliente['cli_id']);
					$consulta_ar = $db->prepare($sql_ar);
					$consulta_ar->execute($values_ar);

					if ($consulta_ar->rowCount()) {
						$num_ar = $consulta_ar->fetch(PDO::FETCH_ASSOC);
					} else {
						$num_ar['nombre'] = '';
						$num_ar['ruta'] = '';
						$num_ar['size'] = 0;
						$num_ar['SIU_NOMBRE'] = '';
						$num_ar['date'] = '';
						$ruta = '';
					}

					$rn = 1;
					/*if($sup_id != 1) {
						$ruta_copy = $num_ar['ruta'];
						$replace = 'archivos/'.$cliente['prefijo'].'/';
						$ruta = str_replace($replace, '', $ruta_copy, $rn);
						$ruta = 'root/'.$ruta;
					} else {*/
						$ruta_copy = $num_ar['ruta'];
						$ruta = str_replace('archivos/', '', $ruta_copy, $rn);
					//}

					$json['reporte'] .= '<tr>
											<td>'.$num_ar['nombre'].'</td>
											<td>'.$ruta.'</td>
											<td>'.$this->formatBytesToOther($num_ar['size']).'</td>
											<td>'.$cliente['nombre'].'</td>
											<td>'.(is_null($num_ar['SIU_NOMBRE']) ? 'Usuario Eliminado' : $num_ar['SIU_NOMBRE']).'</td>
											<td>'.$num_ar['date'].'</td>
										 </tr>';

				//}					
			}


		} else {

			//Prefijo de cliente
			$sql_cl = "SELECT cli_id, prefijo, nombre FROM clientes WHERE cli_id = ? ORDER BY prefijo ASC";
			$values_cl = array($cli_id);
			$consulta = $db->prepare($sql_cl);
			$consulta->execute($values_cl);
			$cliente = $consulta->fetch(PDO::FETCH_ASSOC);

			//Revisamos si tiene archivos hijo
			$sql_ar = "SELECT * 
					   FROM documentos 
					   LEFT JOIN SISTEMA_USUARIO ON SISTEMA_USUARIO.SIU_ID = documentos.siu_id
					   WHERE documentos.cli_id = ? 
					   ORDER BY date DESC LIMIT 0, 1 ";
			$values_ar = array($cli_id);
			$consulta_ar = $db->prepare($sql_ar);
			$consulta_ar->execute($values_ar);

			if ($consulta_ar->rowCount()) {
				$num_ar = $consulta_ar->fetch(PDO::FETCH_ASSOC);
			} else {
				$num_ar['nombre'] = '';
				$num_ar['ruta'] = '';
				$num_ar['size'] = 0;
				$num_ar['SIU_NOMBRE'] = '';
				$num_ar['date'] = '';
				$ruta = '';
			}

			$rn = 1;
			/*if($sup_id != 1) {
				$ruta_copy = $num_ar['ruta'];
				$replace = 'archivos/'.$cliente['prefijo'].'/';
				$ruta = str_replace($replace, '', $ruta_copy, $rn);
				$ruta = 'root/'.$ruta;
			} else {*/
				$ruta_copy = $num_ar['ruta'];
				$ruta = str_replace('archivos/', '', $ruta_copy, $rn);
			//}

			$json['reporte'] .= '<tr>
									<td>'.$num_ar['nombre'].'</td>
									<td>'.$ruta.'</td>
									<td>'.$this->formatBytesToOther($num_ar['size']).'</td>
									<td>'.$cliente['nombre'].'</td>
									<td>'.(is_null($num_ar['SIU_NOMBRE']) ? 'Usuario Eliminado' : $num_ar['SIU_NOMBRE']).'</td>
									<td>'.$num_ar['date'].'</td>
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
					$num_ar = $this->getCantidadesRec($carpeta['car_id'], $num_ar_empty);

					$json['reporte'] .= '<tr>
											<td>'.($num_car['num_car'] > 0 ? '<a href="carpeta.php?c='.$carpeta['car_id'].'">'.$carpeta['nombre'].'</a>' : $carpeta['nombre']).'</td>
											<td>'.$num_car['num_car'].'</td>
											<td>'.$num_ar['num_ar'].'</td>
											<td>'.$num_ar['peso'].'</td>
										 </tr>';

				}
			}

			//Revisamos archivos solos
			$sql_ar = "SELECT COUNT(doc_id) as num_ar, SUM(size) as peso FROM documentos WHERE car_id = 0 AND cli_id = ?";
			$values_ar = array($_POST['cl']);
			$consulta_ar = $db->prepare($sql_ar);
			$consulta_ar->execute($values_ar);
			$num_ar = $consulta_ar->fetch(PDO::FETCH_ASSOC);

			$json['reporte'] .= '<tr>
									<td>Archivos*</td>
									<td>-</td>
									<td>'.$num_ar['num_ar'].'</td>
									<td>'.$num_ar['peso'].'</td>
								 </tr>';


		} else {

			if($sup_id == 1) {
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
						$num_ar = $this->getCantidadesRec($carpeta['car_id'], $num_ar_empty);

						$json['reporte'] .= '<tr>
												<td>'.($num_car['num_car'] > 0 ? '<a href="carpeta.php?c='.$carpeta['car_id'].'">'.$carpeta['nombre'].'</a>' : $carpeta['nombre']).'</td>
												<td>'.$num_car['num_car'].'</td>
												<td>'.$num_ar['num_ar'].'</td>
												<td>'.$num_ar['peso'].'</td>
											 </tr>';

					}
				}

				//Revisamos archivos solos
				$sql_ar = "SELECT COUNT(doc_id) as num_ar, SUM(size) as peso FROM documentos WHERE car_id = ?";
				$values_ar = array($_POST['c']);
				$consulta_ar = $db->prepare($sql_ar);
				$consulta_ar->execute($values_ar);
				$num_ar = $consulta_ar->fetch(PDO::FETCH_ASSOC);

				$json['reporte'] .= '<tr>
										<td>Archivos*</td>
										<td>-</td>
										<td>'.$num_ar['num_ar'].'</td>
										<td>'.$num_ar['peso'].'</td>
									 </tr>';
			} else {
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
						$num_ar = $this->getCantidadesRec($carpeta['car_id'], $num_ar_empty);

						$json['reporte'] .= '<tr>
												<td>'.($num_car['num_car'] > 0 ? '<a href="carpeta.php?c='.$carpeta['car_id'].'">'.$carpeta['nombre'].'</a>' : $carpeta['nombre']).'</td>
												<td>'.$num_car['num_car'].'</td>
												<td>'.$num_ar['num_ar'].'</td>
												<td>'.$num_ar['peso'].'</td>
											 </tr>';

					}
				}

				//Revisamos archivos solos
				$sql_ar = "SELECT COUNT(doc_id) as num_ar, SUM(size) as peso FROM documentos WHERE car_id = ? AND cli_id = ?";
				$values_ar = array($_POST['c'], $cli_id);
				$consulta_ar = $db->prepare($sql_ar);
				$consulta_ar->execute($values_ar);
				$num_ar = $consulta_ar->fetch(PDO::FETCH_ASSOC);

				$json['reporte'] .= '<tr>
										<td>Archivos*</td>
										<td>-</td>
										<td>'.$num_ar['num_ar'].'</td>
										<td>'.$num_ar['peso'].'</td>
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

	function getReporteFechas() {
		$json = array();
		$json['msg'] = '';
		$json['error'] = false;
		$json['reporte'] = '';

		if(!isset($_SESSION)){
			@session_start();
		}

		$sup_id = $_SESSION["sky"]["userprofile"];
		$siu_id = $_SESSION["sky"]["userid"];
		$cli_id = $_SESSION["sky"]["cli_id"];

		$db = $this->_conexion;

		$fecha_1 = date("Y-m-d",strtotime(str_replace("/", "-", $_POST['fecha_1'])));	
		$fecha_2 = date("Y-m-d",strtotime(str_replace("/", "-", $_POST['fecha_2'])));

		$sql = 'SELECT documentos.*,
					   clientes.prefijo,
					   clientes.nombre as cli_nombre,
					   SIU_NOMBRE
				FROM documentos
				JOIN clientes ON documentos.cli_id = clientes.cli_id
				LEFT JOIN SISTEMA_USUARIO ON SISTEMA_USUARIO.SIU_ID = documentos.siu_id
				WHERE DATE(documentos.date) >= ? AND DATE(documentos.date) <= ? ';
		$values = array($fecha_1,
						$fecha_2);
		$order = ' ORDER BY documentos.date DESC ';
		$where = '';
		if($sup_id != 1) {
			$where = ' AND cli_id = ?';
			$values[] = $cli_id;
		}

		$query = $sql.$where.$order;
		$consulta = $db->prepare($query);	
		try {
			$consulta->execute($values);
			$documentos = $consulta->fetchAll(PDO::FETCH_ASSOC);

			foreach ($documentos as $documento) {

				$rn = 1;
				$ruta_copy = $documento['ruta'];
				$ruta = str_replace('archivos/', '', $ruta_copy, $rn);

				//GENERAMOS LA TABLA
				$json['reporte'] .= '<tr>
										<td align="center">'.$documento['nombre'].'</td>
										<td align="center">'.$ruta.'</td>
										<td align="center">'.$this->formatBytesToOther($documento['size']).'</td>
										<td align="center">'.$documento['cli_nombre'].'</td>
										<td align="center">'.(is_null($documento['SIU_NOMBRE']) ? 'Usuario Eliminado' : $documento['SIU_NOMBRE']).'</td>
										<td align="center">'.$documento['date'].'</td>
								   </tr>';




			}

		} catch (PDOException $e) {
			die($e->getMessage().$sql);
		}

		echo json_encode($json);

	}

	function getExcel1() {
		$json = array();
		$json['completado'] = false;

		$columns = array("A",
						 "B",
						 "C",
						 "D",
						 "E",
						 "F");

		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getProperties()->setCreator("Sky Consulting Partners")
					 ->setLastModifiedBy("Sky Consulting Partners")
					 ->setTitle("Ultima Alta")
					 ->setSubject("Ultima Alta")
					 ->setDescription("Reporte de Ultima Alta")
					 ->setKeywords("ultima alta");

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
		            ->setCellValue('A1', 'NOMBRE DEL ARCHIVO')
		            ->setCellValue('B1', 'RUTA')
		            ->setCellValue('C1', 'PESO')
		            ->setCellValue('D1', 'CLIENTE')
		            ->setCellValue('E1', 'USUARIO')
		            ->setCellValue('F1', 'FECHA');


		/*DATOS*/
		if(!isset($_SESSION)){
			@session_start();
		}

		$sup_id = $_SESSION["sky"]["userprofile"];
		$siu_id = $_SESSION["sky"]["userid"];
		$cli_id = $_SESSION["sky"]["cli_id"];

		$db = $this->_conexion;
		$n = 2;

		if(isset($_POST['fecha_1']) && !empty($_POST['fecha_1']) && isset($_POST['fecha_2']) && !empty($_POST['fecha_2'])) {

			$fecha_1 = date("Y-m-d",strtotime(str_replace("/", "-", $_POST['fecha_1'])));	
			$fecha_2 = date("Y-m-d",strtotime(str_replace("/", "-", $_POST['fecha_2'])));

			$sql = 'SELECT documentos.*,
						   clientes.prefijo,
						   clientes.nombre as cli_nombre,
						   SIU_NOMBRE
					FROM documentos
					JOIN clientes ON documentos.cli_id = clientes.cli_id
					LEFT JOIN SISTEMA_USUARIO ON SISTEMA_USUARIO.SIU_ID = documentos.siu_id
					WHERE documentos.date >= ? AND documentos.date <= ? ';
			$values = array($fecha_1,
							$fecha_2);
			$order = ' ORDER BY documentos.date DESC ';
			$where = '';
			if($sup_id != 1) {
				$where = ' AND cli_id = ?';
				$values[] = $cli_id;
			}

			$query = $sql.$where.$order;
			$consulta = $db->prepare($query);	
			try {
				$consulta->execute($values);
				$documentos = $consulta->fetchAll(PDO::FETCH_ASSOC);

				foreach ($documentos as $documento) {

					$rn = 1;
					$ruta_copy = $documento['ruta'];
					$ruta = str_replace('archivos/', '', $ruta_copy, $rn);

					//GENERAMOS LA TABLA
					$objPHPExcel->setActiveSheetIndex(0)
			            ->setCellValue('A'.$n, $documento['nombre'])
			            ->setCellValue('B'.$n, $ruta)
			            ->setCellValue('C'.$n, $this->formatBytesToOther($documento['size']))
			            ->setCellValue('D'.$n, $documento['cli_nombre'])		
			            ->setCellValue('E'.$n, (is_null($documento['SIU_NOMBRE']) ? 'Usuario Eliminado' : $documento['SIU_NOMBRE']))		
			            ->setCellValue('F'.$n, $documento['date']);		

			        $n++; 



				}

			} catch (PDOException $e) {
				die($e->getMessage().$sql);
			}			

		} else if($sup_id == 1) {
			
			//Revisamos los clientes
			$sql_cl = "SELECT cli_id, prefijo, nombre FROM clientes ORDER BY prefijo ASC";
			$consulta = $db->prepare($sql_cl);
			$consulta->execute();
			$clientes = $consulta->fetchAll(PDO::FETCH_ASSOC);

			foreach ($clientes as $cliente) {

				//if($this->hasPermission(0, $cliente['cli_id'], 1)) {

					//Revisamos si tiene archivos hijo
					$sql_ar = "SELECT * 
							   FROM documentos 
							   LEFT JOIN SISTEMA_USUARIO ON SISTEMA_USUARIO.SIU_ID = documentos.siu_id
							   WHERE documentos.cli_id = ? 
							   ORDER BY date DESC LIMIT 0, 1 ";
					$values_ar = array($cliente['cli_id']);
					$consulta_ar = $db->prepare($sql_ar);
					$consulta_ar->execute($values_ar);

					if ($consulta_ar->rowCount()) {
						$num_ar = $consulta_ar->fetch(PDO::FETCH_ASSOC);
					} else {
						$num_ar['nombre'] = '';
						$num_ar['ruta'] = '';
						$num_ar['size'] = 0;
						$num_ar['SIU_NOMBRE'] = '';
						$num_ar['date'] = '';
						$ruta = '';
					}

					$rn = 1;
					/*if($sup_id != 1) {
						$ruta_copy = $num_ar['ruta'];
						$replace = 'archivos/'.$cliente['prefijo'].'/';
						$ruta = str_replace($replace, '', $ruta_copy, $rn);
						$ruta = 'root/'.$ruta;
					} else {*/
						$ruta_copy = $num_ar['ruta'];
						$ruta = str_replace('archivos/', '', $ruta_copy, $rn);
					//}

					$objPHPExcel->setActiveSheetIndex(0)
			            ->setCellValue('A'.$n, $num_ar['nombre'])
			            ->setCellValue('B'.$n, $ruta)
			            ->setCellValue('C'.$n, $this->formatBytesToOther($num_ar['size']))
			            ->setCellValue('D'.$n, $cliente['nombre'])		
			            ->setCellValue('E'.$n, (is_null($num_ar['SIU_NOMBRE']) ? 'Usuario Eliminado' : $num_ar['SIU_NOMBRE']))		
			            ->setCellValue('F'.$n, $num_ar['date']);		

			        $n++; 					 

				//}					
			}


		} else {

			//Prefijo de cliente
			$sql_cl = "SELECT cli_id, prefijo, nombre FROM clientes WHERE cli_id = ? ORDER BY prefijo ASC";
			$values_cl = array($cli_id);
			$consulta = $db->prepare($sql_cl);
			$consulta->execute($values_cl);
			$cliente = $consulta->fetch(PDO::FETCH_ASSOC);

			//Revisamos si tiene archivos hijo
			$sql_ar = "SELECT * 
					   FROM documentos 
					   LEFT JOIN SISTEMA_USUARIO ON SISTEMA_USUARIO.SIU_ID = documentos.siu_id
					   WHERE documentos.cli_id = ? 
					   ORDER BY date DESC LIMIT 0, 1 ";
			$values_ar = array($cli_id);
			$consulta_ar = $db->prepare($sql_ar);
			$consulta_ar->execute($values_ar);

			if ($consulta_ar->rowCount()) {
				$num_ar = $consulta_ar->fetch(PDO::FETCH_ASSOC);
			} else {
				$num_ar['nombre'] = '';
				$num_ar['ruta'] = '';
				$num_ar['size'] = 0;
				$num_ar['SIU_NOMBRE'] = '';
				$num_ar['date'] = '';
				$ruta = '';
			}

			$rn = 1;
			/*if($sup_id != 1) {
				$ruta_copy = $num_ar['ruta'];
				$replace = 'archivos/'.$cliente['prefijo'].'/';
				$ruta = str_replace($replace, '', $ruta_copy, $rn);
				$ruta = 'root/'.$ruta;
			} else {*/
				$ruta_copy = $num_ar['ruta'];
				$ruta = str_replace('archivos/', '', $ruta_copy, $rn);
			//}

			$json['reporte'] .= '<tr>
									<td>'.$num_ar['nombre'].'</td>
									<td>'.$ruta.'</td>
									<td>'.$this->formatBytesToOther($num_ar['size']).'</td>
									<td>'.$cliente['nombre'].'</td>
									<td>'.$num_ar['SIU_NOMBRE'].'</td>
									<td>'.$num_ar['date'].'</td>
								 </tr>';

			$objPHPExcel->setActiveSheetIndex(0)
			            ->setCellValue('A'.$n, $num_ar['nombre'])
			            ->setCellValue('B'.$n, $ruta)
			            ->setCellValue('C'.$n, $this->formatBytesToOther($num_ar['size']))
			            ->setCellValue('D'.$n, $cliente['nombre'])		
			            ->setCellValue('E'.$n, (is_null($num_ar['SIU_NOMBRE']) ? 'Usuario Eliminado' : $num_ar['SIU_NOMBRE']))		
			            ->setCellValue('F'.$n, $num_ar['date']);		

			$n++; 

		}		

		$objPHPExcel->getActiveSheet()->setTitle('UltimaAlta');  
		$objPHPExcel->setActiveSheetIndex(0);    
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save(str_replace('Libs.php', 'ultima-alta.xlsx', __FILE__));      


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
		case "getReporteFechas":
			$libs->getReporteFechas();
			break;	
		case "getExcel1":
			$libs->getExcel1();
			break;				
	}
}

?>