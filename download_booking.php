<?php
require_once '../vendor/autoload.php';
use Dompdf\Dompdf;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pdf_content'])) {
    $pdfContent = base64_decode($_POST['pdf_content']);
    
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="Booking_Confirmation_'.$_POST['booking_id'].'.pdf"');
    header('Content-Length: ' . strlen($pdfContent));
    
    echo $pdfContent;
    exit;
} else {
    header("Location: home.php");
    exit;
}
?>