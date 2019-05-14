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
$module = 2;

class Libs extends Common {

	function printTable() {
		global $module;

		if(!isset($_SESSION)){
			@session_start();
		}

		$sup_id = $_SESSION["sky"]["userprofile"];
		$siu_id = $_SESSION["sky"]["userid"];
		$cli_id = $_SESSION["sky"]["cli_id"];

		$where = '';
		if($sup_id != 1) {
			$where = ' WHERE SISTEMA_USUARIO.CLI_ID = '.$cli_id;
		}

		/*
		 * Query principal
		 */
		$sqlQuery = "SELECT SISTEMA_USUARIO.*,
							clientes.nombre
					 FROM SISTEMA_USUARIO
					 LEFT JOIN clientes ON SISTEMA_USUARIO.CLI_ID = clientes.cli_id"
					 .$where;
		
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

				//Botones
				$params_editar = array(	"link"		=>	"cambios.php?id=".$row['SIU_ID'],
										"title"		=>	"Ver/Editar");
				$btn_editar = $this->printButton($module, "cambios", $params_editar);
				$params_borrar = array(	"title"		=>	"Borrar",
										"classes"	=>	"borrar",
										"data_id"	=>	$row['SIU_ID'],
										"extras"	=>	"data-name='".$row["SIU_NOMBRE"]."'");
				$btn_borrar = $this->printButton($module, "baja", $params_borrar);

				//Activo o Inactivo
				$activo = '<a class="tip" data-toggle="tooltip" title="'.($row['SIU_ACTIVO'] == 0 ? 'Inactivo' : 'Activo').'" data-original-title="'.($row['SIU_ACTIVO'] == 0 ? 'Inactivo' : 'Activo').'" href="#" ><button type="button" class="btn btn-link btn-'.($row['SIU_ACTIVO'] == 0 ? 'danger' : 'success').' btn-lg"><i class="fa fa-'.($row['SIU_ACTIVO'] == 0 ? 'times-circle' : 'check').'"></i></button></a>';

				$aRow = array($row["SIU_NOMBRE"], $row['SIU_EMAIL'], $row['nombre'], $activo,'<div class="form-button-action">'.$btn_editar.$btn_borrar.'</div>');
				
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
						FROM SISTEMA_USUARIO
						WHERE SIU_ID = :valor";

				$consulta = $this->_conexion->prepare($sql);
				$consulta->bindParam(':valor', $_POST['id']);
				$consulta->execute();
				$row = $consulta->fetch(PDO::FETCH_ASSOC);

				if ($consulta->rowCount() > 0) {

					$json = array_merge($json, $row);
						
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
		$error_msg = NULL;

		$excepciones = array('module', 'cl', 'carpeta');

		//VALIDACIÓN
		foreach($_POST as $clave=>$valor){
			if(!$json["error"] && !in_array($clave, $excepciones)){
				if( ($clave == 'password' | $clave == 'confirmacion') && isset($_POST['id'])) {
					//NO HAY ERROR
				} else if($this->is_empty(trim($valor))) {
					$json["error"] = true;
					$json["focus"] = $clave;	
				} else if($clave == "email"  && !$this->isEmail($valor)) {
					$json["error"] = true;
					$json["focus"] = $clave;
					$json["msg"] = "E-mail inválido. Favor de ingresarlo nuevamente.";
				}
			}
		}

		/*Valida que el correo no esté ingresado*/
		//Checks email
		$sql = "SELECT SIU_ID FROM SISTEMA_USUARIO WHERE SIU_EMAIL = ? ";
		$params = array($_POST['email']);
		$consulta = $this->_conexion->prepare($sql);
		$consulta->execute($params);
		$resultMail = $consulta->fetchAll(PDO::FETCH_ASSOC);

		if(!$json["error"] && $consulta->rowCount() > 0 && !isset($_POST['id'])){
			$json["error"] = true;
			$json["focus"] = "mail";
			$error_msg = 1;
		}

		/*Valida contraseña*/
		//Se valida la estructura de la contraseña
		if (!$json["error"] && strlen($_POST['password']) > 6) {
			if(!$json["error"] && isset($_POST['password']) && (!$this->is_empty(trim($_POST['password'])) | !$this->is_empty(trim($_POST['confirmacion'])) ) ){
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
		} else {
				//$json["error"] = true;

				if($error_msg == 1){
					$json["error"] = true;
					$json["msg"] = "El correo electrónico ingresado ya ha sido registrado anteriormente.";
					$json["focus"] = "email";
				}else if (strlen($_POST['password'])<=6 && !isset($_POST['id'])){
					$json["error"] = true;
					$json["msg"] = "La contraseña debe contener al menos siete caracteres.";
					$json["focus"] = "password";
				}
		
		}

		if(!$json['error']) {
			//Si es otro que no sea daemon debe de tener un cliente asignado
			if($_POST['tipo'] != 1 && $_POST['cliente'] == 0) {
				$json['error'] = true;
				$json['msg'] = 'Debe de seleccionarse un cliente válido.';
			}
		}

		if(!$json["error"]) {

			$cliente = ($_POST['tipo'] != 1 ? $_POST['cliente'] : 0);

			$db = $this->_conexion;
			$db->beginTransaction();
			//Revisamos permisos
			//Guarda todos los módulos en un arreglo y lo acomoda por id y su permiso -> $modulo[$id][permiso] = 0/1
			foreach ($_POST['module'] as $id => $module) {
				$modulo = array(0, 0, 0, 0);
				foreach ($module as $key => $permiso) {
					switch ($permiso) {
						case 'view':
							$modulo[0] = 1;
							break;

						case 'reg':
							$modulo[1] = 1;
							break;

						case 'del':
							$modulo[2] = 1;
							break;

						case 'ch':
							$modulo[3] = 1;
							break;		
					}
				}
				$modulos[$id] = $modulo;

				//Verifica los padres y les pone permisos completos de manera recursiva
				$modulos = $this->findParent($id, $modulos);
			}

			ksort($modulos);

			//Lo pone todo en un string en el formato que corresponde
			$permisos = "";
			foreach ($modulos as $key => $modulo) {
				$permisos .= $key ."-".$modulo[0].$modulo[1].$modulo[2].$modulo[3]."|";
			}

			$permisos = rtrim($permisos, "|");

			if(isset($_POST['id'])) { //UPDATE
				//Si tiene password
				if(!$this->is_empty(trim($_POST['password']))) {
					$sql_user = "UPDATE SISTEMA_USUARIO SET SIU_NOMBRE = ?,
															SIU_EMAIL = ?,
															SIU_PASSWORD = ?,
															SUP_ID = ?,
															CLI_ID = ?,
															SIU_ACTIVO = ?,
															SIU_MODULOS = ?
								 WHERE SIU_ID = ?";

					$pass_encr = $this->encrypt($_POST['password']);

					$values = array($_POST["nombre"],
									$_POST["email"],
									$pass_encr,
									$_POST["tipo"],
									$cliente,
									$_POST['estatus'],
									$permisos,
									$_POST['id']);							
				} else {
					$sql_user = "UPDATE SISTEMA_USUARIO SET SIU_NOMBRE = ?,
															SIU_EMAIL = ?,
															SUP_ID = ?,
															CLI_ID = ?,
															SIU_ACTIVO = ?,
															SIU_MODULOS = ?
								 WHERE SIU_ID = ?";

					$values = array($_POST["nombre"],
									$_POST["email"],
									$_POST["tipo"],
									$cliente,
									$_POST['estatus'],
									$permisos,
									$_POST['id']);	
				}


				//Eliminamos los permisos de las carpetas anteriores
				$consulta_del = $db->prepare("DELETE FROM permisos_carpetas WHERE siu_id = :valor");
				$consulta_del->bindParam(':valor', $_POST['id']);
				try {
					$consulta_del->execute();
				} catch(PDOException $e) {
					$db->rollBack();
					die($e->getMessage());
				}


			} else { //INSERCION
				$sql_user = "INSERT INTO SISTEMA_USUARIO (SIU_NOMBRE,
														  SIU_EMAIL,
														  SIU_PASSWORD,
														  SUP_ID,
														  CLI_ID,
														  SIU_ACTIVO,
														  SIU_MODULOS,
														  SIU_CARPETAS)
							 VALUES( ?, ?, ?, ?, ?, ?, ?, ? )";

				$pass_encr = $this->encrypt($_POST['password']);

				$values = array($_POST["nombre"],
								$_POST["email"],
								$pass_encr,
								$_POST["tipo"],
								$cliente,
								$_POST['estatus'],
								$permisos,
								'');
			}

			$consulta = $db->prepare($sql_user);

			try {
				$consulta->execute($values);


				if(!isset($_POST['id'])) {
					$siu_id = $this->last_id();
				} else {
					$siu_id = $_POST['id'];
				}

				$json["valid"] = true;

				//Revisamos los permisos de las carpetas
				$carpetas = array();
				if(isset($_POST['cl'])) {
					foreach ($_POST['cl'] as $id => $cl) {

						if($cliente == 0 || ($cliente != 0 && $cliente == $id)) {
							$carpeta_cl = array();
							$carpeta_cl[0] = 0;
							$carpeta_cl[1] = 0;
							$carpeta_cl[2] = 0;
							$carpeta_cl[3] = 0;
							foreach ($cl as $key => $permiso) {
								switch ($permiso) {
									case 'view':
										$carpeta_cl[0] = 1;
										break;

									case 'reg':
										$carpeta_cl[0] = 1;
										$carpeta_cl[1] = 1;
										break;

									case 'del':
										$carpeta_cl[0] = 1;
										$carpeta_cl[2] = 1;
										break;

									case 'ch':
										$carpeta_cl[0] = 1;
										$carpeta_cl[3] = 1;
										break;		
								}
							}

							$carpeta_cl['cli_id'] = $id;
							$carpetas['cl-'.$id] = $carpeta_cl;

							//Verifica los padres y les pone permisos completos de manera recursiva
							//$carpetas = $this->findFolderParent($id, $modulos);
						}
					}
				}

				if(isset($_POST['carpeta'])) {
					foreach ($_POST['carpeta'] as $id => $car) {
						$carpeta = array();
						$carpeta[0] = 0;
						$carpeta[1] = 0;
						$carpeta[2] = 0;
						$carpeta[3] = 0;
						foreach ($car as $key => $permiso) {
							switch ($permiso) {
								case 'view':
									$carpeta[0] = 1;
									break;

								case 'reg':
									$carpeta[0] = 1;
									$carpeta[1] = 1;
									break;

								case 'del':
									$carpeta[0] = 1;
									$carpeta[2] = 1;
									break;

								case 'ch':
									$carpeta[0] = 1;
									$carpeta[3] = 1;
									break;		
							}
						}

						$query = "SELECT nivel, cli_id FROM carpetas WHERE car_id = ?";
						$consulta = $db->prepare($query);

						try {
							$consulta->execute(array($id));
							$padre = $consulta->fetch();

						} catch(PDOException $e) {
							die($e->getMessage());
						}

						$carpeta['cli_id'] = $padre['cli_id'];
						$carpetas[$id] = $carpeta;

						//Verifica los padres y les pone permisos completos de manera recursiva
						$carpetas = $this->findFolderParent($id, $carpetas, $padre['cli_id']);
					}
				}

				foreach ($carpetas as $id => $carpeta) {
					$sql_perm = "INSERT INTO permisos_carpetas (siu_id,
																car_id,
																cli_id,
																permisos)
								  VALUES (?, ?, ?, ?)";


					if(is_numeric($id)) {
						//Carpeta
						$car_id = $id;
					} else {
						//Root de un usuario
						$car_id = 0;
					}

					$values_perm = array($siu_id,
										 $car_id,
										 $carpeta['cli_id'],
										 $carpeta[0].$carpeta[1].$carpeta[2].$carpeta[3]);

					$consulta_perm = $db->prepare($sql_perm);

					try {
						$consulta_perm->execute($values_perm);

					} catch(PDOException $e) {
						$db->rollback();
						die($e->getMessage());
					}

				}


				//Manda mail si se registra
				if(!isset($_POST['id'])) {
					$json["msg"] = "El Usuario registrado con éxito. En breve recibirá un correo electrónico de confirmación";

					//Correo para el usuario
					require_once($ruta."include/Mail.php");
					$Mail = new Mail('¡Bienvenido al Sistema de Archivo de Documentos!');
					$Mail->addMail($_POST['email'], $_POST['nombre']);
					$cuerpo_mensaje = "<tr>
										<td>
									   		<br><br>
											¡Bienvenido al Sistema de Archivo de Documentos!

											<br><br>

											Para comenzar a utilizar el sistema ingresa a  : <a href='https://URL'>https://URL</a> con tu usuario ".$_POST['email'].". Contacta al administrador del sistema para recibir tu contraseña.
										</td>
									</tr>";
					$Mail->content($cuerpo_mensaje);
					
					//Envía el correo
					$Mail->send();	
				} else {
					$json["msg"] = "Los cambios del Usuario han sido realizados exitosamente.";
				}

				$db->commit();

			} catch(PDOException $e) {
				$db->rollback;
				$json["error"] = true;
				$json["msg"] = $e->getMessage();
			}	
		}

		echo json_encode($json);
	}

	function findParent($id, $modulos) {
		$query = "SELECT SIM_NIVEL FROM SISTEMA_MODULOS WHERE SIM_ID = ?";
		$consulta = $this->_conexion->prepare($query);

		try {
			$consulta->execute(array($id));
			$padre = $consulta->fetchAll();
		} catch(PDOException $e) {
			die($e->getMessage());
		}

		if(isset($padre[0]['SIM_NIVEL'])) {
			if($padre[0]['SIM_NIVEL'] > 0) {
				$modulos[$padre[0]['SIM_NIVEL']] = array(1, 1, 1, 1);
				$modulos = $this->findParent($padre[0]['SIM_NIVEL'], $modulos);
			}
		}

		return $modulos;
	}

	function findFolderParent($id, $modulos, $cliente) {
		$query = "SELECT nivel, cli_id FROM carpetas WHERE car_id = ?";
		$consulta = $this->_conexion->prepare($query);

		try {
			$consulta->execute(array($id));
			$padre = $consulta->fetch();

			if($consulta->rowCount()) {
				if($cliente == 0 || ($cliente != 0 && $cliente == $padre['cli_id'])) {
					if($padre['nivel'] > 0) {
						$modulos[$padre['nivel']] = array();
						$modulos[$padre['nivel']][0] = 1;
						$modulos[$padre['nivel']][1] = 1;
						$modulos[$padre['nivel']][2] = 1;
						$modulos[$padre['nivel']][3] = 1;
						$modulos[$padre['nivel']]['cli_id'] = $padre['cli_id'];
						$modulos = $this->findParent($padre['nivel'], $modulos, $padre['cli_id']);
					} else {
						$modulos['cl-'.$padre['cli_id']] = array();
						$modulos['cl-'.$padre['cli_id']][0] = 1;
						$modulos['cl-'.$padre['cli_id']][1] = 1;
						$modulos['cl-'.$padre['cli_id']][2] = 1;
						$modulos['cl-'.$padre['cli_id']][3] = 1;
						$modulos['cl-'.$padre['cli_id']]['cli_id'] = $cliente;
					}
				}
			}

		} catch(PDOException $e) {
			die($e->getMessage());
		}


		return $modulos;
	}

	function showModules() {
		$json = array();
		$json['error'] = false;
		$json["nombre"] = "";

		if(!isset($_SESSION)){
			@session_start();
		}

		$sup_id = $_SESSION["sky"]["userprofile"];
		$siu_id = $_SESSION["sky"]["userid"];
		$cli_id = $_SESSION["sky"]["cli_id"];

		$where = '';
		if($sup_id != 1) {
			$where = ' WHERE SIM_ACCESO = 1';
		}

		//Consulta de Módulos
		$query = "SELECT * FROM SISTEMA_MODULOS ".$where." ORDER BY SIM_ORDEN ASC";
		$consulta = $this->_conexion->prepare($query);
		try {
			$consulta->execute();
			$modulos = $consulta->fetchAll();
		} catch(PDOException $e) {
			die($e->getMessage());
		}

		//Obtenemos todos los permisos de la persona
		$permisos = array();
		$consulta = $this->_conexion->prepare('SELECT SIU_MODULOS
												FROM SISTEMA_USUARIO
												WHERE SIU_ID = ?');
		
		//Se ejecuta la consulta
		try {
			$consulta->execute(array($siu_id));
			$puntero = $consulta->fetch(PDO::FETCH_ASSOC);
			$puntero = $puntero["SIU_MODULOS"];
	
			//Se procesa los permisos
			$permisos = array();
			$puntero = explode("|", $puntero);
			
			foreach ($puntero as $clave => $valor) {
				$temporal = explode("-", $valor);
				$modulo = $temporal[0];
				$permisos[$modulo] = str_split($temporal[1]);
			}
		} catch(PDOException $e) {
			die($e->getMessage());
		}
		
		//Ordena todos los módulos en el árbol con su id como referencia y un arreglo con sus hijos
		foreach ($modulos as $modulo) {
			if(isset($permisos[$modulo['SIM_ID']])) {
				$tree[$modulo['SIM_ID']] = $modulo;
				$tree[$modulo['SIM_ID']]['children'] = array();
			}
		}

		//Asigna los hijos al arreglo principal
		foreach ($tree as $key => &$leaf) {
			$parent = isset($tree[$key]['SIM_NIVEL'])?$tree[$key]['SIM_NIVEL']:array();
			if(isset($leaf['SIM_NIVEL'])) {
				$tree[$parent]['children'][] = &$tree[$key];
			}
		}

		$modulos_permiso = array();

		if(isset($_POST['id'])){
			try{
				$sql = "SELECT SIU_MODULOS FROM SISTEMA_USUARIO WHERE SIU_ID = :valor";

				$consulta = $this->_conexion->prepare($sql);
				$consulta->bindParam(':valor', $_POST['id']);
				$consulta->execute();
				$result = $consulta->fetch(PDO::FETCH_ASSOC);

				if ($consulta->rowCount() > 0) {
					$row = $result;

					//División entre módulos
					$puntero = explode("|", $row["SIU_MODULOS"]);			

					foreach ($puntero as $clave => $valor) {
						$temporal = explode("-", $valor);
						$modulo_id = $temporal[0];

						$sql = "SELECT * FROM SISTEMA_MODULOS WHERE SIM_ID = :valor";

						$consulta = $this->_conexion->prepare($sql);
						$consulta->bindParam(':valor', $modulo_id);
						$consulta->execute();
						$result = $consulta->fetch(PDO::FETCH_ASSOC);
						$modulos_permiso[$result["SIM_ID"]] = $result;
						$modulos_permiso[$result["SIM_ID"]]["permiso"] = str_split($temporal[1]);
					}

				} else {
					$json['error'] = true;
				}

			} catch(PDOException $e) {
			die($e->getMessage());
			}
		}	

		$json['content'] = self::printChild($tree[0]['children'], "", $modulos_permiso);

		echo json_encode($json);
	}

	function printChild($children, $print = "", $permisos) {
		$content = "";
		foreach ($children as $child) {
			if($child['SIM_NIVEL'] == 0) {
				$content .= "
				<div class='col-md-6'>
					<div class='box border primary'>
						<div class='box-title all-mod pointer' data-id='".$child['SIM_ID']."'><h4 class='center col-md-12'>".$child['SIM_NOMBRE']."</h4></div>
						<div class='box-body'>
							<table class='table table-hover module-".$child['SIM_ID']."'>
								<tbody>
									<tr>
										<td></td>
										<td class='select-all pointer' data-id='".$child['SIM_ID']."' data-class='view' align='center'>Vista</td>
										<td class='select-all pointer' data-id='".$child['SIM_ID']."' data-class='reg' align='center'>Alta</td>
										<td class='select-all pointer' data-id='".$child['SIM_ID']."' data-class='del' align='center'>Baja</td>
										<td class='select-all pointer' data-id='".$child['SIM_ID']."' data-class='ch' align='center'>Cambios</td>
									</tr>";

				if(is_array($child['children']) && count($child['children'])) {
					$content .= self::printChild($child['children'], $print, $permisos);
				} else {
					$content .= "			<tr class='white'>
										<td align='center' class='title-module pointer' data-id='".$child['SIM_ID']."'>".$child['SIM_NOMBRE']."</td>
										<td align='center'><input id='v-".$child['SIM_ID']."' type='checkbox' class='view' name='module[".$child['SIM_ID']."][]' value='view' data-id='".$child['SIM_ID']."' ".(isset($permisos[$child['SIM_ID']]["permiso"][0]) ? $permisos[$child['SIM_ID']]["permiso"][0]==1 ? 'checked' : '' : '' )." ></td>
										<td align='center'><input id='a-".$child['SIM_ID']."' type='checkbox' class='reg chk-v' name='module[".$child['SIM_ID']."][]' value='reg' data-id='".$child['SIM_ID']."' ".(isset($permisos[$child['SIM_ID']]["permiso"][1]) ? $permisos[$child['SIM_ID']]["permiso"][1]==1 ? 'checked' : '' : '' )." ></td>
										<td align='center'><input id='b-".$child['SIM_ID']."' type='checkbox' class='del chk-v' name='module[".$child['SIM_ID']."][]' value='del' data-id='".$child['SIM_ID']."' ".(isset($permisos[$child['SIM_ID']]["permiso"][2]) ? $permisos[$child['SIM_ID']]["permiso"][2]==1 ? 'checked' : '' : '' )." ></td>
										<td align='center'><input id='c-".$child['SIM_ID']."' type='checkbox' class='ch chk-v' name='module[".$child['SIM_ID']."][]' value='ch' data-id='".$child['SIM_ID']."' ".(isset($permisos[$child['SIM_ID']]["permiso"][3]) ? $permisos[$child['SIM_ID']]["permiso"][3]==1 ? 'checked' : '' : '' )." ></td>
									</tr>";
				}			

				$content .= "			</tbody>
							</table>
						</div>
					</div>
				</div>";
			} else {
				if(is_array($child['children']) && count($child['children'])) {
					$content .= self::printChild($child['children'], $print.$child['SIM_NOMBRE']." - ", $permisos);
				} else {
					$content .= "			<tr class='white'>
										<td align='center' class='title-module pointer' data-id='".$child['SIM_ID']."'>".$print.$child['SIM_NOMBRE']."</td>
										<td align='center'><input id='v-".$child['SIM_ID']."' type='checkbox' class='view' name='module[".$child['SIM_ID']."][]' value='view' data-id='".$child['SIM_ID']."' ".(isset($permisos[$child['SIM_ID']]["permiso"][0]) ? $permisos[$child['SIM_ID']]["permiso"][0]==1 ? 'checked' : '' : '' )." ></td>
										<td align='center'><input id='a-".$child['SIM_ID']."' type='checkbox' class='reg chk-v' name='module[".$child['SIM_ID']."][]' value='reg' data-id='".$child['SIM_ID']."' ".(isset($permisos[$child['SIM_ID']]["permiso"][1]) ? $permisos[$child['SIM_ID']]["permiso"][1]==1 ? 'checked' : '' : '' )." ></td>
										<td align='center'><input id='b-".$child['SIM_ID']."' type='checkbox' class='del chk-v' name='module[".$child['SIM_ID']."][]' value='del' data-id='".$child['SIM_ID']."' ".(isset($permisos[$child['SIM_ID']]["permiso"][2]) ? $permisos[$child['SIM_ID']]["permiso"][2]==1 ? 'checked' : '' : '' )." ></td>
										<td align='center'><input id='c-".$child['SIM_ID']."' type='checkbox' class='ch chk-v' name='module[".$child['SIM_ID']."][]' value='ch' data-id='".$child['SIM_ID']."' ".(isset($permisos[$child['SIM_ID']]["permiso"][3]) ? $permisos[$child['SIM_ID']]["permiso"][3]==1 ? 'checked' : '' : '' )." ></td>
									</tr>";
				}	
			}
		}

		return $content;
	}

	function getTiposUsuarios() {
		$json = array();
		$json['select'] = '';

		if(!isset($_SESSION)){
			@session_start();
		}

		$sup_id = $_SESSION["sky"]["userprofile"];
		$siu_id = $_SESSION["sky"]["userid"];
		$cli_id = $_SESSION["sky"]["cli_id"];

		$json['select'] = '<select class="form-control" id="tipo" name="tipo">';
		$where = '';
		if($sup_id != 1) {
			$where = ' WHERE SUP_ID != 1';
		}

		$db = $this->_conexion;
		$sql = "SELECT * FROM SISTEMA_USUARIO_PERFIL".$where;
		$consulta = $db->prepare($sql);
		$consulta->execute();
		$result = $consulta->fetchAll(PDO::FETCH_ASSOC);

		foreach ($result as $row){
			$json['select'] .= '<option value="'.$row['SUP_ID'].'" '.(isset($_POST['id']) && $_POST['id'] == $row['SUP_ID'] ? 'selected' : '').'>'.$row['SUP_NOMBRE'].'</option>';
		}

		$json['select'] .= '</select>';

		echo json_encode($json);
	}

	function getClients() {
		$json = array();
		$json['select'] = '';

		if(!isset($_SESSION)){
			@session_start();
		}

		$sup_id = $_SESSION["sky"]["userprofile"];
		$siu_id = $_SESSION["sky"]["userid"];
		$cli_id = $_SESSION["sky"]["cli_id"];

		$json['select'] = '<select class="form-control" id="cliente" name="cliente">';
		$where = '';
		if($sup_id != 1) {
			$where = ' WHERE CLI_ID = '.$cli_id;
		} else {
			$json['select'] .= '<option value="0">N/A</option>';
		}

		$db = $this->_conexion;
		$sql = "SELECT * FROM clientes".$where;
		$consulta = $db->prepare($sql);
		$consulta->execute();
		$result = $consulta->fetchAll(PDO::FETCH_ASSOC);

		foreach ($result as $row){
			$json['select'] .= '<option value="'.$row['cli_id'].'" '.(isset($_POST['id']) && $_POST['id'] == $row['cli_id'] ? 'selected' : '').'>'.$row['nombre'].'</option>';
		}

		$json['select'] .= '</select>';

		echo json_encode($json);
	}

	function showFolders() {
		$json = array();
		$json['error'] = false;
		$json['msg'] = '';
		$json['carpetas'] = '';

		if(!isset($_SESSION)){
			@session_start();
		}

		$sup_id = $_SESSION["sky"]["userprofile"];
		$siu_id = $_SESSION["sky"]["userid"];
		$cli_id = $_SESSION["sky"]["cli_id"];

		//Si es daemon imprimimos todas las carpetas de los clientes
		if($sup_id == 1 && !isset($_POST['cliente'])) {
			
			//Revisamos los clientes
			$db = $this->_conexion;
			$sql_cl = "SELECT cli_id, prefijo FROM clientes ORDER BY prefijo ASC";
			$consulta = $db->prepare($sql_cl);
			$consulta->execute();
			$clientes = $consulta->fetchAll(PDO::FETCH_ASSOC);

			foreach ($clientes as $cliente) {

				$checked = array(0, 0, 0, 0);
				$siu_id = 0;
				if(isset($_POST['id'])) {
					$siu_id = $_POST['id'];
					$sql_ch = "SELECT permisos FROM permisos_carpetas WHERE siu_id = ? AND cli_id = ? AND car_id = 0";
					$values_ch = array($_POST['id'],
									  $cliente['cli_id']);
					$consulta_ch = $db->prepare($sql_ch);
					$consulta_ch->execute($values_ch);
					if($consulta_ch->rowCount()) {
						$permiso = $consulta_ch->fetch(PDO::FETCH_ASSOC);
						$checked = str_split($permiso['permisos']);
					}
				}


				$before = '|--';

				$json['carpetas'] .= '<tr>
										<td align="left"><span id="cl-'.$cliente['cli_id'].'" class="title-car">'.$before.$cliente['prefijo'].'</span></td>
										<td align="center"><input id="cl-'.$cliente['cli_id'].'-v" type="checkbox" class="view-cl" name="cl['.$cliente['cli_id'].'][]" value="view" data-id="'.$cliente['cli_id'].'" '.($checked[0]==1 ? 'checked' : '').'></th>
										<td align="center"><input id="cl-'.$cliente['cli_id'].'-a" type="checkbox" class="reg-cl chk-v-cl" name="cl['.$cliente['cli_id'].'][]" value="reg" data-id="'.$cliente['cli_id'].'" '.($checked[1]==1 ? 'checked' : '').'></td>
										<td align="center"><input id="cl-'.$cliente['cli_id'].'-b" type="checkbox" class="del-cl chk-v-cl" name="cl['.$cliente['cli_id'].'][]" value="del" data-id="'.$cliente['cli_id'].'" '.($checked[2]==1 ? 'checked' : '').'></td>
										<td align="center"><input id="cl-'.$cliente['cli_id'].'-c" type="checkbox" class="ch-cl chk-v-cl" name="cl['.$cliente['cli_id'].'][]" value="ch" data-id="'.$cliente['cli_id'].'" '.($checked[3]==1 ? 'checked' : '').'></td>
									</tr>';


				$json['carpetas'] .= $this->getClientRoot($cliente['cli_id'], 0, $before, $siu_id);


			}


		} else {
			$before = '|--';
			//Si es cliente imprimos todas las carpeta de ese cliente en específico
			if(isset($_POST['cliente'])) {
				$json['carpetas'] = $this->getClientRoot($_POST['cliente'], 1, $before, $siu_id);
			} else {
				$json['carpetas'] = $this->getClientRoot($_SESSION["sky"]["cli_id"], 1, $before, $siu_id);
			}
		}

		echo json_encode($json);					

	}

	function getClientRoot($cli_id, $root, $before, $siu_id) {
		global $ruta;
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

				$checked = array(0, 0, 0, 0);
				if($siu_id > 0) {
					$sql_ch = "SELECT permisos FROM permisos_carpetas WHERE siu_id = ? AND car_id = ?";
					$values_ch = array($siu_id,
									   $carpeta['car_id']);
					$consulta_ch = $db->prepare($sql_ch);
					$consulta_ch->execute($values_ch);
					if($consulta_ch->rowCount()) {
						$permiso = $consulta_ch->fetch(PDO::FETCH_ASSOC);
						$checked = str_split($permiso['permisos']);
					}
				}


				$b_f = '|<span class="space"></span>'.$before;

				$arbol_innner .= '<tr>
										<td align="left"><span id="car-'.$carpeta['car_id'].'" class="title-car">'.$b_f.$carpeta['nombre'].'</span></td>
										<td align="center"><input id="car-'.$carpeta['car_id'].'-v" type="checkbox" class="view-car has-cl" data-par="'.$cli_id.'" name="carpeta['.$carpeta['car_id'].'][]" value="view" data-id="'.$carpeta['car_id'].'" '.($checked[0]==1 ? 'checked' : '').'></th>
										<td align="center"><input id="car-'.$carpeta['car_id'].'-a" type="checkbox" class="reg-car chk-v-car has-cl" data-par="'.$cli_id.'" name="carpeta['.$carpeta['car_id'].'][]" value="reg" data-id="'.$carpeta['car_id'].'" '.($checked[1]==1 ? 'checked' : '').'></td>
										<td align="center"><input id="car-'.$carpeta['car_id'].'-b" type="checkbox" class="del-car chk-v-car has-cl" data-par="'.$cli_id.'" name="carpeta['.$carpeta['car_id'].'][]" value="del" data-id="'.$carpeta['car_id'].'" '.($checked[2]==1 ? 'checked' : '').'></td>
										<td align="center"><input id="car-'.$carpeta['car_id'].'-c" type="checkbox" class="ch-car chk-v-car has-cl" data-par="'.$cli_id.'" name="carpeta['.$carpeta['car_id'].'][]" value="ch" data-id="'.$carpeta['car_id'].'" '.($checked[3]==1 ? 'checked' : '').'></td>
									</tr>';


				$arbol_innner .= $this->getChildFolders($carpeta['car_id'], $b_f, $siu_id);

			}
		}


		//Si root es 1 significa que va a mostrar una carpeta root antes
		if($root == 1) {

			$checked = array(0, 0, 0, 0);
			if($siu_id > 0) {
				$sql_ch = "SELECT permisos FROM permisos_carpetas WHERE siu_id = ? AND cli_id = ? AND car_id = 0";
				$values_ch = array($siu_id,
								   $cli_id);
				$consulta_ch = $db->prepare($sql_ch);
				$consulta_ch->execute($values_ch);
				if($consulta_ch->rowCount()) {
					$permiso = $consulta_ch->fetch(PDO::FETCH_ASSOC);
					$checked = str_split($permiso['permisos']);
				}
			}

			//Revisamos nombre de Cliente
			$sql_cl = "SELECT cli_id, prefijo FROM clientes WHERE cli_id = ? ORDER BY prefijo ASC";
			$values_cl = array($cli_id);
			$consulta = $db->prepare($sql_cl);
			$consulta->execute($values_cl);
			$cliente = $consulta->fetch(PDO::FETCH_ASSOC);


			$arbol = '<tr>
						<td align="left"><span id="cl-'.$cli_id.'" class="title-car">'.$before.$cliente['prefijo'].'</span></td>
						<td align="center"><input id="cl-'.$cli_id.'-v" type="checkbox" class="view-cl" name="cl['.$cli_id.'][]" value="view" data-id="'.$cli_id.'"  '.($checked[0]==1 ? 'checked' : '').'></th>
						<td align="center"><input id="cl-'.$cli_id.'-a" type="checkbox" class="reg-cl chk-v-cl" name="cl['.$cli_id.'][]" value="reg" data-id="'.$cli_id.'"  '.($checked[1]==1 ? 'checked' : '').'></td>
						<td align="center"><input id="cl-'.$cli_id.'-b" type="checkbox" class="del-cl chk-v-cl" name="cl['.$cli_id.'][]" value="del" data-id="'.$cli_id.'"  '.($checked[2]==1 ? 'checked' : '').'></td>
						<td align="center"><input id="cl-'.$cli_id.'-c" type="checkbox" class="ch-cl chk-v-cl" name="cl['.$cli_id.'][]" value="ch" data-id="'.$cli_id.'"  '.($checked[3]==1 ? 'checked' : '').'></td>
					</tr>';

			$arbol .= $arbol_innner;		
		} else {
			$arbol = $arbol_innner;
		}

		return $arbol;
	}

	function getChildFolders($car_id, $before, $siu_id) {
		global $ruta;
		$arbol_innner = '';
		$arbol = '';
		$db = $this->_conexion;
		$sql_car = "SELECT * FROM carpetas WHERE nivel = ? ORDER BY nombre ASC";
		$values_car = array($car_id);
		$consulta_car = $db->prepare($sql_car);
		$consulta_car->execute($values_car);
		if ($consulta_car->rowCount())  {
			$carpetas = $consulta_car->fetchAll(PDO::FETCH_ASSOC);
			foreach ($carpetas as $carpeta) {
				$b_f = '|<span class="space"></span>'.$before;

				$checked = array(0, 0, 0, 0);
				if($siu_id > 0) {
					$sql_ch = "SELECT permisos FROM permisos_carpetas WHERE siu_id = ? AND car_id = ?";
					$values_ch = array($siu_id,
									   $carpeta['car_id']);
					$consulta_ch = $db->prepare($sql_ch);
					$consulta_ch->execute($values_ch);
					if($consulta_ch->rowCount()) {
						$permiso = $consulta_ch->fetch(PDO::FETCH_ASSOC);
						$checked = str_split($permiso['permisos']);
					}
				}

				$arbol .= '<tr>
										<td align="left"><span id="car-'.$carpeta['car_id'].'" class="title-car">'.$b_f.$carpeta['nombre'].'</span></td>
										<td align="center"><input id="car-'.$carpeta['car_id'].'-v" type="checkbox" class="view-car has-par" data-par="'.$carpeta['nivel'].'" name="carpeta['.$carpeta['car_id'].'][]" value="view" data-id="'.$carpeta['car_id'].'"  '.($checked[0]==1 ? 'checked' : '').'></th>
										<td align="center"><input id="car-'.$carpeta['car_id'].'-a" type="checkbox" class="reg-car chk-v-car has-par" data-par="'.$carpeta['nivel'].'" name="carpeta['.$carpeta['car_id'].'][]" value="reg" data-id="'.$carpeta['car_id'].'" '.($checked[1]==1 ? 'checked' : '').'></td>
										<td align="center"><input id="car-'.$carpeta['car_id'].'-b" type="checkbox" class="del-car chk-v-car has-par" data-par="'.$carpeta['nivel'].'" name="carpeta['.$carpeta['car_id'].'][]" value="del" data-id="'.$carpeta['car_id'].'" '.($checked[2]==1 ? 'checked' : '').'></td>
										<td align="center"><input id="car-'.$carpeta['car_id'].'-c" type="checkbox" class="ch-car chk-v-car has-par" data-par="'.$carpeta['nivel'].'" name="carpeta['.$carpeta['car_id'].'][]" value="ch" data-id="'.$carpeta['car_id'].'" '.($checked[3]==1 ? 'checked' : '').'></td>
									</tr>';


				$arbol .= $this->getChildFolders($carpeta['car_id'], $b_f, $siu_id);
			}

		}

		return $arbol;
	}

	function deleteRecord() {
		$json = array();
		$json['error'] = true;
		$json['msg'] = "Experimentamos fallas técnicas.";
		if(isset($_POST['id'])){
			try{
				$consulta = $this->_conexion->prepare("DELETE FROM SISTEMA_USUARIO WHERE SIU_ID = :valor");
				$consulta->bindParam(':valor', $_POST['id']);
				$consulta->execute();
				if($consulta->rowCount()){
					$json['msg'] = "El Usuario fue eliminado con éxito.";
					$json['error'] = false;

					//Eliminamos los permisos
					$consulta = $this->_conexion->prepare("DELETE FROM permisos_carpetas WHERE siu_id = :valor");
					$consulta->bindParam(':valor', $_POST['id']);
					$consulta->execute();

				} else{
					$json['error'] = true;
					$json['msg'] = "El Usuario elegido no pudo ser eliminado.";
				}
			}catch(PDOException $e){
				die($e->getMessage());
			}	
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
		case "showModules":
			$libs->showModules();
			break;
		case "showFolders":
			$libs->showFolders();
			break;		
		case "getClients":
			$libs->getClients();
			break;
		case "getTiposUsuarios":
			$libs->getTiposUsuarios();
			break;	
		case "deleteRecord":
			$libs->deleteRecord();
			break;				
	}
}

?>