<?php
// send-reset-link.php

// Sertakan file yang dibutuhkan
require 'db_connection.php';
require 'phpmailer/Exception.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMer\Exception;

// Atur zona waktu
date_default_timezone_set('Asia/Jakarta');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);

    if (!$email) {
        header("Location: forgot-password.php");
        exit();
    }
    
    $stmt = $pdo->prepare("SELECT id FROM akun WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $hashed_token = hash('sha256', $token);
        $expires_at = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        $update_stmt = $pdo->prepare("UPDATE akun SET reset_token = ?, reset_token_expires_at = ? WHERE id = ?");
        $update_stmt->execute([$hashed_token, $expires_at, $user['id']]);
        
        $mail = new PHPMailer(true);

        try {
            // -- KONFIGURASI SMTP --
            $mail->isSMTP();
            $mail->Host       = 'app.parkintime.web.id';         // GANTI: SMTP Host Anda
            $mail->SMTPAuth   = true;
            $mail->Username   = 'support@app.parkintime.web.id'; // GANTI: Email Anda
            $mail->Password   = 'H~Nh=?h8)SdhSST?';         // GANTI: Password email Anda
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;      // GANTI: 'ssl' atau 'tls'
            $mail->Port       = 465;                             // GANTI: 465 untuk SSL, 587 untuk TLS

            // -- PENGATURAN EMAIL --
            $mail->setFrom('support@app.parkintime.web.id', 'Parkintime Support');
            $mail->addAddress($email);

            // -- KONTEN EMAIL --
            $reset_link = "https://app.parkintime.web.id/flutter/reset-password/reset-password.php?token=" . $token;
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body    = "
    <p>Thank you for using the ParkInTime app. To complete your password reset process, please follow this link:</p>
    
    <p><a href='{$reset_link}'>{$reset_link}</a></p>
    
    <p>This code is valid only once and will expire in 15 minutes. If you did not request a password reset on ParkInTime, please disregard this email.</p>
    
    <p>Thank you,<br></p>
    <p>Parkintime</p>
";
            
            $mail->send();

        } catch (Exception $e) {
            // Anda bisa mencatat error jika perlu, tapi jangan tampilkan ke user
            // error_log("Mailer Error: " . $mail->ErrorInfo);
        }
    }
    
    $successMessage = "If an account with that email exists, we have sent instructions to reset your password.";
    header("Location: forgot-password.php?message=" . urlencode($successMessage));
    exit();
}
?>