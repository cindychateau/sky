<?php
/**
 * Clase que extiende a PHPMailer
 * 
 * @author: Cynthia Castillo
 */

require_once("class.phpmailer.php");

class Mail extends PHPMailer
{
	/**
	 * Se Inicializa la clase Mail. Recibe El Asunto del correo
	 */
	function __construct($subject = ""){
		$this->subject = $subject;
		$this->hasAttach = false;
		$this->recipients = array();
	}
	
	/*
	 * Función que genera el encabezado del correo.
	 */
	function Header(){
		$result  = '<table style="line-height:100%; border:none; width:600px; margin-top:0; margin-bottom:0;" cellspacing="0" cellpadding="0">
				<tr style="height:30px; color:black; font-family:arial; font-size:12pt;">
					<td>
						<br/>
						<p align="center" style="line-height:100%;">
							<img src="http://technoweb.mx/sky-logo.png">
						</p>
					</td>
				</tr>';
		return $result;
	}
	
	/*
	 * Función que genera el pie del correo.
	 */
	function Footer(){
		$result  = ('<tr>');
		$result .= 	('<td style="border-top:1px solid #31629f">&nbsp;</td>');
		$result .= ('</tr>');
		$result .= ('<tr>');
		$result .= 	('<td style="color:#31629f;font-family:tahoma;font-size:9pt;">');
		$result .= 	('<p style="text-align: justify;style="line-height:100%;">');
		$result .= ('El contenido de éste mensaje es confidencial y dirigido exclusivamente al destinatario
				del mismo. Por lo tanto no debe ser re-enviado y/o re-transmitido por ningún medio
				sin la autorización del Autor original del mismo. Si usted no es el destinatario,
				no tiene ninguna autorización para usar el mensaje total o parcialmente para
				ningún propósito.
				<br/><br/>');
		$result .= 	('</p>');
		$result .= ('</td>');
		$result .= ('</tr>');
		$result .= ('</table>');
		
		return $result;
	}
	
	/*
	 * Función que agrega un destinario.
	 */
	function addMail($email, $name = ""){
		$this->recipients[$email] = $name;
	}
	
	/*
	 * Función que agrega un destinario con Copia.
	 */
	function addCC($email, $name = ""){
		$this->recipientsCC[$email] = $name;
	}
	
	/*
	 * Función que agrega un destinario con Copia.
	 */
	function addBCC($email, $name = ""){
		$this->recipientsBCC[$email] = $name;
	}
	
	/*
	 * Función que recibe el cuerpo del mensaje. Puede ser en HTML.
	 */
	function content($content = ""){
		$this->Body = $content;
	}
	
	/*
	 * Función que agrega un archivo adjunto
	 * @$path: Ruta al archivo.
	 * @name: Nombre del archivo que aparecerá en el correo.
	 */
	function addAttach($path, $name){
		$this->hasAttach = true;
		$this->files[$name] = $path;
	}
	
	function addAt($att) {
		$this->hasAttach = true;
		$this->file = $att;
	}
	/*
	 * Función que envía el mensaje
	 */
	function send(){
		$mail = new PHPMailer();
		$mail->CharSet = "utf-8";
		$mail->From = "no-reply@sky-cp.com";
		$mail->FromName = "Sky Consulting Partners";
		
		$mail->Host = "mail.technoweb.mx";
		$mail->SMTPAuth = true;
		$mail->Username = "sender@technoweb.mx";
		$mail->Password = "Huo0lpaw";
		$mail->Port = 587;

		$mail->IsSMTP();
		//$mail->isSendMail();
		$mail->IsHTML(true);
		
		$mail->Subject = $this->subject;
		$mail->Body = $this->Header() . $this->Body . $this->Footer();
		
		//Agrega los destinatarios.
		foreach($this->recipients as $email => $name){
			$mail->AddAddress($email, $name);
		}
		
		//Valida si se encuentra la matriz con los destinatarios Con Copia.
		if(isset($this->recipientsCC) && is_array($this->recipientsCC) && count($this->recipientsCC) > 0){
			//Se agregan los destinarios con copia.
			foreach($this->recipientsCC as $email => $name){
				$mail->AddCC($email, $name);
			}
		}
		
		//Valida si se encuentra la matriz con los destinatarios Con Copia Oculta.
		if(isset($this->recipientsBCC) && is_array($this->recipientsBCC) && count($this->recipientsBCC) > 0){
			//Se agregan los destinatarios con copia oculta.
			foreach($this->recipientsBCC as $email => $name){
				$mail->AddBCC($email, $name);
			}
		}
		
		//Valida si se agregaron archivos al correo.
		if($this->hasAttach){
			//Se adjuntan los archivos al correo.
			/*foreach($this->files as $filename => $path){
				$mail->AddAttachment($path, $filename, 'base64');
			}*/
			$mail->AddAttachment($this->file);
		}
		
		if(!$mail->Send()){
			return $mail->ErrorInfo;
		}
		else{
			$mail->ClearAllRecipients();
			$mail->ClearAttachments();
			return "success";
		}
	}
}
?>