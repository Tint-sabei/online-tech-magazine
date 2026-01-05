<?php 

/**
 * EMAIL TRANSMISSION
 * This script fulfills the requirement to implement email functionality for contact/messages.
 * 1. TECHNOLOGY: Uses PHPMailer with SMTP authentication for reliable delivery.
 * 2. SECURITY: Integrates Google reCAPTCHA v2 to prevent automated spam.
 * 3. PERSISTENCE: Messages are both emailed to the admin and archived in the 'messages' database table.
 */

// verify_recaptcha.php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../classes/Database.php';
require_once 'mail/class.phpmailer.php';
require_once 'mail/class.smtp.php';
require_once 'mail/mail_config.php';

$returnMsg = ''; 

if(isset($_POST['submit'])){ 
    
    if(!empty($_POST['name']) && !empty($_POST['email']) && !empty($_POST['content'])){ 
         
        if(isset($_POST['g-recaptcha-response']) && !empty($_POST['g-recaptcha-response'])){ 
            
            $secret_key = '6LdQJCYsAAAAADr5TXDzVskWV-1nm4LSmKvTaBdU'; 
            $verify_captcha = @file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$secret_key.'&response='.$_POST['g-recaptcha-response']); 
            
            if($verify_captcha !== FALSE) {
                $verify_response = json_decode($verify_captcha); 
                
                if($verify_response->success){ 
                    
                    $name = trim($_POST['name']); 
                    $email = trim($_POST['email']); 
                    $phone = trim($_POST['phone'] ?? '');
                    $message_content = trim($_POST['content']);

                    // --- 1. SAVE TO DATABASE ---
                    $db_saved = false;
                    try {
                        $db_conn = Database::getInstance()->getConnection();
                        $sql = "INSERT INTO messages (name, email, phone, message_text, sent_date) VALUES (?, ?, ?, ?, NOW())";
                        $stmt = $db_conn->prepare($sql);
                        $db_saved = $stmt->execute([$name, $email, $phone, $message_content]);
                    } catch (Exception $e) {
                        error_log("Database error: " . $e->getMessage());
                    }

                    // --- 2. SEND EMAIL ---
                    $email_sent = false;
                    try {
                        $mail = new PHPMailer(true);
                        
                        // SMTP Configuration 
                        $mail->isSMTP();
                        $mail->SMTPAuth = true;
                        $mail->SMTPSecure = 'ssl';
                        $mail->Host = 'mail.tsoewin.daw.ssmr.ro';
                        $mail->Port = 465;
                        $mail->Username = $username;
                        $mail->Password = $password;
                        
                        // Sender/Recipient
                        $mail->setFrom('tsoewins@tsoewin.daw.ssmr.ro', 'Tech Magazine Contact');
                        $mail->addAddress('tsoewins@tsoewin.daw.ssmr.ro', 'Tech Magazine Admin');
                        $mail->addReplyTo($email, $name);
                        
                        // Email content
                        $mail->Subject = 'New Contact Form: ' . $name;
                        $mail->isHTML(true);
                        $mail->Body = "
                        <h3>New Contact Form Submission</h3>
                        <p><strong>Name:</strong> " . htmlspecialchars($name) . "</p>
                        <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
                        <p><strong>Phone:</strong> " . htmlspecialchars($phone) . "</p>
                        <p><strong>Message:</strong><br>" . nl2br(htmlspecialchars($message_content)) . "</p>
                        <p><strong>Submitted:</strong> " . date('Y-m-d H:i:s') . "</p>
                        <p><strong>IP Address:</strong> " . $_SERVER['REMOTE_ADDR'] . "</p>
                        <hr>
                        <p><small>This message was submitted via the Tech Magazine contact form.</small></p>";
                        
                        // Plain text version
                        $mail->AltBody = "New Contact Form Submission\n\n" .
                                        "Name: " . $name . "\n" .
                                        "Email: " . $email . "\n" .
                                        "Phone: " . $phone . "\n" .
                                        "Message:\n" . $message_content . "\n\n" .
                                        "Submitted: " . date('Y-m-d H:i:s') . "\n" .
                                        "IP Address: " . $_SERVER['REMOTE_ADDR'];
                        
                        $email_sent = $mail->send();
                        
                    } catch (Exception $e) {
                        error_log("Email error: " . $e->getMessage());
                    }
                    
                    // --- 3. SET SUCCESS MESSAGE ---
                    if($db_saved && $email_sent) {
                        $returnMsg = 'Thank you! Your message has been sent successfully. We will respond soon.';
                    } elseif($db_saved && !$email_sent) {
                        $returnMsg = 'Message received! We saved your message and will contact you shortly.';
                    } elseif(!$db_saved && $email_sent) {
                        $returnMsg = 'Email sent! There was an issue saving to database, but we received your message.';
                    } else {
                        $returnMsg = 'Sorry, there was an error processing your message. Please try again.';
                    }
                     
                } else { 
                    $returnMsg = 'CAPTCHA verification failed. Please try again.'; 
                } 
            } else {
                $returnMsg = 'Unable to verify CAPTCHA. Please try again.';
            }
        } else { 
            $returnMsg = 'Please complete the CAPTCHA verification.'; 
        } 
    } else { 
        $returnMsg = 'Please fill all required fields (Name, Email, Message).'; 
    } 
} 

// Store message in session and redirect
$_SESSION['contact_msg'] = $returnMsg;
$_SESSION['contact_msg_type'] = ($returnMsg === 'Thank you! Your message has been sent successfully. We will respond soon.') ? 'success' : 'error';

header("Location: index.php");
exit;
?>