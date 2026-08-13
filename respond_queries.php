<?php
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require 'PHPMailer/src/Exception.php';
    require 'PHPMailer/src/PHPMailer.php';
    require 'PHPMailer/src/SMTP.php';

    session_start();
    include "db_helper.php";

    if(!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Staff', 'Admin'])) {
        header("Location: login.php");
        exit();
    }

    $id = $_GET['id'] ?? null;
    if(!$id) {
        header("Location: dashboard.php");
        exit();
    }

    $errors  = [];
    $success = "";

    // fetch query info
    $query = fetchOne($conn, "
        SELECT * FROM customer_query
        WHERE query_id = ? AND status = 'Open'
    ", "i", [$id]);

    if(!$query) {
        header("Location: customer_queries.php");
        exit();
    }

    if(isset($_POST['respond'])) {
        $subject = trim($_POST['subject']        ?? '');
        $message = trim($_POST['query_response'] ?? '');

        if(empty($subject)) $errors[] = "Subject is required.";
        if(empty($message)) $errors[] = "Response is required.";

        if(count($errors) === 0) {
        
        // new phpmailer instance
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP(); // enable smtp since we're using external server (gmail)
            $mail->Host       = 'smtp.gmail.com'; // set stmp as gmail
            $mail->SMTPAuth   = true;
            $mail->Username   = 'globetrekadventures2026@gmail.com'; // account used to send the email
            $mail->Password   = 'dkgzsftbufeeeifg';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // send email (globetrek)
            $mail->setFrom('globetrekadventures2026@gmail.com', 'GlobeTrek Adventures');
            $mail->addAddress($query['email']); // receiver email
            $mail->Subject = $subject; // set email subject
            $mail->Body = $message; // set email message

        } catch (Exception $e) { // handle errors
            $errors[] = "Mail configuration failed: " . $mail->ErrorInfo;
        }

            if(count($errors) === 0) {
                if($mail->send()) {
                    updateRow($conn, "
                        UPDATE customer_query
                        SET status = 'Closed',
                            staff_id = ?,
                            response = ?,
                            responded_at = NOW()
                        WHERE query_id = ?
                    ", "isi", [$_SESSION['user_id'], $message, $query['query_id']]);

                    $success = "Response sent successfully!";
                } else {
                    $errors[] = "Email could not be sent: " . $mail->ErrorInfo;
                }
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Responding Customer Query</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
    <script defer src="https://kit.fontawesome.com/e5a3f33c9d.js" crossorigin="anonymous"></script>
</head>
<body class="bg-light">
    <?php include "staff_navbar.php";?>
    <?php if(!empty($success)): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if(!empty($errors)): ?>
        <?php foreach($errors as $error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endforeach; ?>
    <?php endif; ?>
    <section class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card shadow-sm rounded">
                    <div class="card-body">
                        <h4 class="card-title text-center mb-4"><i class="fa-solid fa-reply"></i> Responding Query ID # <?=$query['query_id']?></h4>
                        <div class="text-center mb-4">
                            <p class="mb-1"><?=$query['name']?> | <a href="mailto:<?=$query['email']?>"><?=$query['email']?></a></p>
                            <p class="mb-4 fw-bold">"<?=$query['message']?>"</p>
                        </div>
                        <form action="respond_queries.php?id=<?= $query['query_id'] ?>" method="post">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Subject</label>
                                <input name="subject" class="form-control" placeholder="Describe the context of this email...">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Response</label>
                                <textarea name="query_response" class="form-control" rows="5">Greetings from GlobeTrek Adventures! We have received your query...</textarea>
                            </div>
                            <button type="submit" name="respond" class="btn btn-booking w-100">Send</button>
                            <p class="text-center text-muted small mt-3 mb-0">Email will be sent to <?=$query['email']?></p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>
</html>