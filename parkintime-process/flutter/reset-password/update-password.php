<?php
// Selalu mulai session di bagian paling atas
session_start();

require 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    // Fungsi untuk mengalihkan kembali dengan pesan error
    function redirect_with_error($message, $token) {
        $_SESSION['error_message'] = $message;
        header('Location: reset-password.php?token=' . urlencode($token));
        exit();
    }

    // Validasi input
    if (empty($token)) {
        redirect_with_error("Invalid request, token not found.", '');
    }
    if ($password !== $password_confirm) {
        redirect_with_error("Password and password confirmation do not match.", $token);
    }
    if (strlen($password) < 8) {
        redirect_with_error("Password must be at least 8 characters.", $token);
    }

    // Validasi token sekali lagi
    $hashed_token = hash('sha256', $token);
    $stmt = $pdo->prepare("SELECT id FROM akun WHERE reset_token = ? AND reset_token_expires_at > NOW()");
    $stmt->execute([$hashed_token]);
    $user = $stmt->fetch();

    if ($user) {
        // Hash password baru
        $new_hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Update password dan hapus token
        $update_stmt = $pdo->prepare("UPDATE akun SET password = ?, reset_token = NULL, reset_token_expires_at = NULL WHERE id = ?");
        $update_stmt->execute([$new_hashed_password, $user['id']]);

        // Hapus session error jika berhasil
        unset($_SESSION['error_message']);
        
        // Tampilkan halaman sukses
        echo '
        <!DOCTYPE html>
        <html>
        <head>
            <title>Success</title>
            <script src="https://cdn.tailwindcss.com"></script>
        </head>
        <body class="bg-gray-100 flex items-center justify-center h-screen">
            <div class="text-center bg-white p-10 rounded-xl shadow-lg">
                <h1 class="text-2xl font-bold text-green-600">Password Updated!</h1>
                <p class="mt-2 text-gray-700">Your password has been changed successfully.</p>
                <p class="mt-2 text-gray-700">Open the application then log in to your account with the new password.</p>
            </div>
        </body>
        </html>';

    } else {
        redirect_with_error("The password reset link is invalid or has expired.", $token);
    }
}
?>