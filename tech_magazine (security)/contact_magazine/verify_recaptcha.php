<?php 

require_once('../classes/Database.php');

// --- INITIALIZE PDO CONNECTION ---
try {
    // Get the single instance of my Database class
    $db = Database::getInstance();
    // Get the underlying PDO object
    $pdo = $db->getConnection(); 
} catch (PDOException $e) {
    // Critical error: Database connection failed.
    die('Database connection error. Contact administrator.');
}

$returnMsg = ''; 

if(isset($_POST['submit'])){ 
    
    // --- Input Validation and Sanitization ---
    // We require name, email, and content (message)
    if(!empty($_POST['name']) && !empty($_POST['email']) && !empty($_POST['content'])){ 
        
        // --- reCAPTCHA Validation Check ---
        if(isset($_POST['g-recaptcha-response']) && !empty($_POST['g-recaptcha-response'])){ 
            
            // Google reCAPTCHA API secret key 
            $secret_key = 'xxx'; 
            
            // ReCAPTCHA verification API call
            $verify_captcha = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$secret_key.'&response='.$_POST['g-recaptcha-response']); 
            $verify_response = json_decode($verify_captcha); 
            
            // Check if reCAPTCHA response returns success 
            if($verify_response->success){ 
                
                // Get and sanitize inputs
                $name = trim($_POST['name']); 
                $email = trim($_POST['email']); 
                $phone = trim($_POST['phone'] ?? ''); // Handle phone field being optional/empty
                $message_content = trim($_POST['content']);

                // Define default/placeholder values to match MESSAGE table structure
                $subject = 'Public Contact Form Submission'; 
                $user_id = NULL; // NULL because the message is from a non-logged-in visitor

                $sql = "INSERT INTO messages (
                            name, 
                            email, 
                            subject, 
                            message_text, 
                            sent_date, 
                            user_id
                        ) 
                        VALUES (
                            :name, 
                            :email, 
                            :subject, 
                            :message_text, 
                            NOW(), 
                            :user_id
                        )";

                try {
                    $stmt = $pdo->prepare($sql);
                    
                    // Bind values using named parameters (PDO handles security automatically)
                    $params = [
                        ':name' => $name,
                        ':email' => $email,
                        ':subject' => $subject,
                        ':message_text' => $message_content,
                        ':user_id' => $user_id
                    ];
                    
                    if ($stmt->execute($params)) {
                        $returnMsg = 'Your message has been submitted and saved successfully.';
                        // You can redirect here: header('Location: success.php'); exit;
                        
                    } else {
                        $returnMsg = 'Database Error: Could not save message.';
                    }
                    
                } catch (PDOException $e) {
                    // SECURITY: Do NOT show $e->getMessage() to the public. 
                    // It can reveal database structure to hackers.
                    $returnMsg = "Database Error. Please try again later.";
                }
                // --------------------------------------------------------
                
            } else { 
                // CAPTCHA verification failed
                $returnMsg = 'CAPTCHA Verification Failed. Please try again.'; 
            } 
        } else { 
            // reCAPTCHA not checked
            $returnMsg = 'Please check the CAPTCHA box.'; 
        } 
    } else { 
        // Form fields missing
        $returnMsg = 'Please fill the required fields (Name, Email, Message).'; 
    } 
} 
echo $returnMsg;
?>
