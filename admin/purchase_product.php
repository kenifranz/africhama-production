<?php
// File: purchase_product.php
session_start();
$base_path = '/africhama-production';
$root_path = $_SERVER['DOCUMENT_ROOT'] . $base_path;
require_once $root_path . '/db_connect.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: " . $base_path . "/products.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = $_GET['id'];

// Fetch product details
$stmt = $conn->prepare("SELECT title, friches_price FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    $_SESSION['error_message'] = "Product not found.";
    header("Location: " . $base_path . "/products.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = $_POST['payment_method'];

    if ($payment_method === 'paypal') {
        // Redirect to PayPal payment page
        $_SESSION['product_id'] = $product_id;
        header("Location: " . $base_path . "/paypal_payment.php?amount=" . $product['friches_price']);
        exit();
    } elseif ($payment_method === 'manual') {
        // Handle manual payment upload
        if (isset($_FILES['bank_statement']) && $_FILES['bank_statement']['error'] == 0) {
            $file_name = $_FILES['bank_statement']['name'];
            $file_tmp = $_FILES['bank_statement']['tmp_name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_ext = array("pdf", "jpg", "jpeg", "png");

            if (in_array($file_ext, $allowed_ext)) {
                $upload_dir = $root_path . "/uploads/product_payments/";
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                $new_file_name = uniqid() . "." . $file_ext;
                if (move_uploaded_file($file_tmp, $upload_dir . $new_file_name)) {
                    // Record the manual payment in the database
                    $stmt = $conn->prepare("INSERT INTO product_payments (user_id, product_id, amount, file_path, status) VALUES (?, ?, ?, ?, 'pending')");
                    $stmt->bind_param("iids", $user_id, $product_id, $product['friches_price'], $new_file_name);
                    if ($stmt->execute()) {
                        $payment_id = $conn->insert_id;
                        $_SESSION['success_message'] = "Your payment request has been submitted. Please wait for admin approval.";
                        
                        // Notify admin
                        $admin_stmt = $conn->prepare("SELECT id FROM users WHERE role = 'admin'");
                        $admin_stmt->execute();
                        $admin_result = $admin_stmt->get_result();
                        while ($admin = $admin_result->fetch_assoc()) {
                            $notification_message = "New product purchase request submitted for " . $product['title'];
                            $notification_link = "/admin/approve_product_purchases.php?id=" . $payment_id;
                            sendNotification($conn, $admin['id'], $notification_message, 'product_purchase', $notification_link);
                        }
                    } else {
                        $_SESSION['error_message'] = "Failed to submit payment request. Please try again.";
                    }
                } else {
                    $_SESSION['error_message'] = "Failed to upload file. Please try again.";
                }
            } else {
                $_SESSION['error_message'] = "Invalid file format. Please upload a PDF, JPG, JPEG, or PNG file.";
            }
        } else {
            $_SESSION['error_message'] = "Please upload a bank statement.";
        }
        header("Location: " . $base_path . "/products.php");
        exit();
    }
}

function sendNotification($conn, $user_id, $message, $type, $link) {
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, type, link) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $message, $type, $link);
    $stmt->execute();

    // Trigger WebSocket notification (if implemented)
    // This is a placeholder for WebSocket implementation
    $ws_message = json_encode([
        'type' => 'notification',
        'user_id' => $user_id,
        'message' => $message,
        'link' => $link
    ]);
    // Send $ws_message to WebSocket server
}

$page_title = "Purchase Product";
include $root_path . '/includes/header.php';
?>

<div class="container mt-4">
    <h1>Purchase Product: <?php echo htmlspecialchars($product['title']); ?></h1>
    <p>Price: $<?php echo number_format($product['friches_price'], 2); ?></p>

    <form action="purchase_product.php?id=<?php echo $product_id; ?>" method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">Payment Method:</label>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="payment_method" id="paypal" value="paypal" required>
                <label class="form-check-label" for="paypal">PayPal</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="payment_method" id="manual" value="manual" required>
                <label class="form-check-label" for="manual">Manual Bank Transfer</label>
            </div>
        </div>

        <div id="manual-payment-form" style="display: none;">
            <div class="mb-3">
                <label for="bank_statement" class="form-label">Upload Bank Statement (PDF, JPG, JPEG, or PNG):</label>
                <input type="file" id="bank_statement" name="bank_statement" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Proceed with Payment</button>
    </form>
</div>

<script>
document.querySelectorAll('input[name="payment_method"]').forEach((elem) => {
    elem.addEventListener("change", function(event) {
        var manualForm = document.getElementById("manual-payment-form");
        manualForm.style.display = event.target.value === "manual" ? "block" : "none";
    });
});
</script>

<?php include $root_path . '/includes/footer.php'; ?>