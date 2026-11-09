<?php
// config/correo.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../lib/PHPMailer/Exception.php';
require_once __DIR__ . '/../lib/PHPMailer/SMTP.php';
require_once __DIR__ . '/../lib/PHPMailer/PHPMailer.php';

/*
 Envía un mail en formato HTML
 devuelve true si funciono, false si fallo. (No corta el programa)
  */

function enviarCorreo($destinatario, $nombreDestinatario, $asunto, $cuerpoHTML) {
    $config = require __DIR__ . '/correo_config.php';
    $mail = new PHPMailer(true); // true = lanza excepciones si algo falla

    try {
        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->Port = $config['puerto'];
        $mail->SMTPSecure = $config['cifrado'];
        $mail->SMTPAuth = ($config['usuario'] !== '');
        $mail->Username = $config['usuario'];
        $mail->Password = $config['clave'];
        $mail->Timeout = 10; // Si el servidor no responde en 10s, abandona
        $mail->CharSet = 'UTF-8';
        $mail->SetFrom($config['remitente'], $config['nombre_remitente']);
        $mail->addAddress($destinatario, $nombreDestinatario);

        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body = $cuerpoHTML;
        $mail->AltBody = strip_tags($cuerpoHTML); // Versión en texto plano
        $mail->send();
        return true;
    } catch(Exception $e) {
        // No mostramos el error al usuario, lo dejamos en el registro de errores de PHP
        error_log('No se pudo enviar el correo' . $mail->ErrorInfo);
        return false;
    } 
}
