<?php
// Selalu mulai session di bagian paling atas
session_start();

require 'db_connection.php';

$token_error = '';
$token_valid = false;
$token = $_GET['token'] ?? '';

// Ambil pesan error dari session jika ada
$form_error = '';
if (isset($_SESSION['error_message'])) {
    $form_error = $_SESSION['error_message'];
    // Hapus pesan setelah diambil agar tidak muncul lagi
    unset($_SESSION['error_message']);
}


if ($token) {
    $hashed_token = hash('sha256', $token);
    $stmt = $pdo->prepare("SELECT id FROM akun WHERE reset_token = ? AND reset_token_expires_at > NOW()");
    $stmt->execute([$hashed_token]);
    if ($stmt->fetch()) {
        $token_valid = true;
    } else {
        $token_error = "The password reset link is invalid or has expired.";
    }
} else {
    $token_error = "Token not found.";
}
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-100">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full flex items-center justify-center py-12 px-4">
    <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-xl shadow-lg">
        <?php if ($token_valid): ?>
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">Create New Password</h2>
            </div>
            
            <?php if (!empty($form_error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline"><?= htmlspecialchars($form_error) ?></span>
            </div>
            <?php endif; ?>

            <form class="mt-8 space-y-6" action="update-password.php" method="POST">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <div class="rounded-md shadow-sm -space-y-px">
                    <div>
                        <label for="password" class="sr-only">New Password</label>
                        <input id="password" name="password" type="password" required class="appearance-none rounded-t-md relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="Password Baru">
                    </div>
                    <div>
                        <label for="password_confirm" class="sr-only">Confirm Password</label>
                        <input id="password_confirm" name="password_confirm" type="password" required class="appearance-none rounded-b-md relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="Konfirmasi Password Baru">
                    </div>
                </div>
                <div>
                    <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Set New Password
                    </button>
                </div>
            </form>
        <?php else: ?>
            <div class="text-center">
                <h2 class="text-2xl font-bold text-red-600">Error</h2>
                <p class="mt-2 text-gray-600"><?= htmlspecialchars($token_error) ?></p>
                <a href="forgot-password.php" class="mt-6 inline-block text-indigo-600 hover:text-indigo-500">Request a New Link</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>