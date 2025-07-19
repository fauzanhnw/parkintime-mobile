<?php
// Mengambil data dari URL yang dikirim oleh Midtrans
$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : 'N/A';
$status_code = isset($_GET['status_code']) ? $_GET['status_code'] : 'N/A';
$transaction_status = isset($_GET['transaction_status']) ? $_GET['transaction_status'] : 'N/A';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Berhasil - Status Pembayaran</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }
        .payment-container {
            max-width: 600px;
            margin-top: 5rem;
            margin-bottom: 5rem;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            animation: fadeIn 0.5s ease-in-out;
        }
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .icon-wrapper {
            font-size: 5rem;
            line-height: 1;
            margin-bottom: 1.5rem;
        }
        .btn-primary-custom {
            background-color: #007bff;
            border-color: #007bff;
            padding: 12px 30px;
            font-weight: 500;
            border-radius: 50px;
            transition: all 0.3s ease;
        }
        .btn-primary-custom:hover {
            background-color: #0056b3;
            border-color: #0056b3;
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100">

<div class="container payment-container">
    <div class="card text-center">
        <div class="card-body p-5">
            <div class="icon-wrapper text-success">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1 class="card-title fw-bold">Payment Successful!</h1>
            <p class="card-text text-muted fs-5 mt-3">
                Thank you! We have received your payment.
            </p>
            <hr class="my-4">
            <div class="text-start">
                <p><strong>Transaction Details:</strong></p>
                <ul class="list-unstyled">
                    <li><strong>Order ID:</strong> <?php echo htmlspecialchars($order_id); ?></li>
                    <li><strong>Transaction Status:</strong> <span class="badge bg-success"><?php echo ucfirst(htmlspecialchars($transaction_status)); ?></span></li>
                    <li><strong>Status Code:</strong> <?php echo htmlspecialchars($status_code); ?></li>
                </ul>
            </div>
            <a href="/halaman-utama-anda.php" class="btn btn-primary-custom mt-4">
                <i class="fas fa-arrow-left me-2"></i>Return to Home
            </a>
        </div>
        <div class="card-footer text-muted">
            Our team will process your order immediately.
        </div>
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>