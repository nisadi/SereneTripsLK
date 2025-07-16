<?php
require_once __DIR__ . '/admin/config.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize variables
$errors = [];
$fieldErrors = [];
$formData = [];

// Process form when submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Collect and sanitize all form data
        $formData = [
            'package_id' => filter_input(INPUT_POST, 'package_id', FILTER_VALIDATE_INT),
            'name' => filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING),
            'email' => filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL),
            'phone' => filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING),
            'address' => filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING),
            'guests' => filter_input(INPUT_POST, 'guests', FILTER_VALIDATE_INT, ['min_range' => 1]),
            'arrival_date' => filter_input(INPUT_POST, 'arrival_date', FILTER_SANITIZE_STRING),
            'leaving_date' => filter_input(INPUT_POST, 'leaving_date', FILTER_SANITIZE_STRING)
        ];

        // Validate package selection
        if (!$formData['package_id']) {
            $errors[] = "Please select a valid package";
            $fieldErrors['package_id'] = true;
        }

        // Validate name
        if (empty($formData['name'])) {
            $errors[] = "Name is required";
            $fieldErrors['name'] = true;
        }

        // Validate email
        if (!$formData['email']) {
            $errors[] = "Valid email is required";
            $fieldErrors['email'] = true;
        }

        // Validate phone
        if (empty($formData['phone'])) {
            $errors[] = "Phone number is required";
            $fieldErrors['phone'] = true;
        }

        // Validate address
        if (empty($formData['address'])) {
            $errors[] = "Address is required";
            $fieldErrors['address'] = true;
        }

        // Validate guests
        if (!$formData['guests']) {
            $errors[] = "Number of guests is required (minimum 1)";
            $fieldErrors['guests'] = true;
        }

        // Validate dates
        if (empty($formData['arrival_date']) || !strtotime($formData['arrival_date'])) {
            $errors[] = "Valid arrival date is required";
            $fieldErrors['arrival_date'] = true;
        }

        if (empty($formData['leaving_date']) || !strtotime($formData['leaving_date'])) {
            $errors[] = "Valid leaving date is required";
            $fieldErrors['leaving_date'] = true;
        }

        // Validate date range if both dates exist
        if (strtotime($formData['arrival_date']) && strtotime($formData['leaving_date'])) {
            if (strtotime($formData['arrival_date']) > strtotime($formData['leaving_date'])) {
                $errors[] = "Leaving date must be after arrival date";
                $fieldErrors['arrival_date'] = true;
                $fieldErrors['leaving_date'] = true;
            }

            // Check if arrival date is in the past
            if (strtotime($formData['arrival_date']) < strtotime(date('Y-m-d'))) {
                $errors[] = "Arrival date cannot be in the past";
                $fieldErrors['arrival_date'] = true;
            }
        }

        // If no errors, proceed with booking
         if (empty($errors)) {
            $pdo->beginTransaction();

            try {
                // Get package details
                $stmt = $pdo->prepare("SELECT id, title, price FROM packages WHERE id = ?");
                $stmt->execute([$formData['package_id']]);
                $package = $stmt->fetch();

                if (!$package) {
                    throw new Exception("Selected package not found");
                }

                // Calculate total
                $total = $package['price'] * $formData['guests'];

                // Insert booking with created_at
                $stmt = $pdo->prepare("
                    INSERT INTO bookings 
                    (package_id, name, email, phone, address, guests, arrival_date, leaving_date, total_price, status, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
                ");
                
                $success = $stmt->execute([
                    $formData['package_id'],
                    $formData['name'],
                    $formData['email'],
                    $formData['phone'],
                    $formData['address'],
                    $formData['guests'],
                    $formData['arrival_date'],
                    $formData['leaving_date'],
                    $total
                ]);

                if (!$success) {
                    throw new Exception("Failed to create booking");
                }

                $booking_id = $pdo->lastInsertId();
                $pdo->commit();

                // Clear any previous form data
                if (isset($_SESSION['form_data'])) {
                    unset($_SESSION['form_data']);
                }
                if (isset($_SESSION['field_errors'])) {
                    unset($_SESSION['field_errors']);
                }

                // Prepare data for confirmation page
                $_SESSION['booking_data'] = [
                    'booking_id' => $booking_id,
                    'package_title' => $package['title'],
                    'name' => $formData['name'],
                    'email' => $formData['email'],
                    'phone' => $formData['phone'],
                    'address' => $formData['address'],
                    'guests' => $formData['guests'],
                    'arrival_date' => $formData['arrival_date'],
                    'leaving_date' => $formData['leaving_date'],
                    'price' => $package['price'],
                    'total' => $total
                ];

                header("Location: booking_confirmation.php");
                exit;

            } catch (Exception $e) {
                $pdo->rollBack();
                $errors[] = $e->getMessage();
            }
        }

    } catch (PDOException $e) {
        $errors[] = "Database error: " . $e->getMessage();
    }
}

// If there are errors, store them in session and redirect back
if (!empty($errors)) {
    $_SESSION['booking_errors'] = $errors;
    $_SESSION['field_errors'] = $fieldErrors;
    $_SESSION['form_data'] = $formData;
    header("Location: Book.php");
    exit;
}
?>