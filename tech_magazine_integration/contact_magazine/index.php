<?php
// Start session at the top of index.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'includes/header.php'; 

// Check for message from contact form
$contact_msg = '';
$msg_type = '';
if(isset($_SESSION['contact_msg'])) {
    $contact_msg = $_SESSION['contact_msg'];
    $msg_type = $_SESSION['contact_msg_type'] ?? 'info';
    unset($_SESSION['contact_msg']);
    unset($_SESSION['contact_msg_type']);
}
?>

<style>
    body { background-color: #F3E5F5; font-family: 'Segoe UI', sans-serif; }
    
    .contact-card {
        background: white;
        border-radius: 30px;
        padding: 40px;
        margin: 50px auto;
        max-width: 700px;
        box-shadow: 0 10px 30px rgba(81, 45, 168, 0.1);
    }
    
    .brand-text { color: #512DA8; text-align: center; margin-bottom: 30px; font-weight: bold; }
    
    .form-label { font-weight: bold; color: #512DA8; display: block; margin-bottom: 8px; }
    
    .form-control { 
        width: 100%; 
        padding: 12px; 
        margin-bottom: 20px; 
        border: 1px solid #D1C4E9; 
        border-radius: 12px; 
        transition: border-color 0.3s;
    }
    
    .form-control:focus {
        outline: none;
        border-color: #512DA8;
        box-shadow: 0 0 0 3px rgba(81, 45, 168, 0.1);
    }
    
    .captcha-wrapper { 
        display: flex; 
        flex-direction: column; 
        align-items: center; 
        margin: 20px 0 30px 0; 
    }
    
    .btn-submit { 
        background: #512DA8; 
        color: white; 
        border: none; 
        padding: 15px 30px; 
        border-radius: 50px; 
        cursor: pointer; 
        width: 100%; 
        font-weight: bold;
        font-size: 1.1em;
        transition: background 0.3s, transform 0.2s;
        margin-top: 20px;
    }
    
    .btn-submit:hover { 
        background: #311B92; 
        transform: translateY(-2px);
    }
    
    .alert-message {
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 12px;
        text-align: center;
    }
    
    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .alert-error {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    
    .alert-error {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

</style>

<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<div class="container">
    <div class="contact-card">
        <h1 class="brand-text">Contact Us</h1>
        
        <!-- Display message if any -->
        <?php if(!empty($contact_msg)): ?>
            <div class="alert-<?php echo $msg_type; ?>">
                <?php echo htmlspecialchars($contact_msg); ?>
            </div>
        <?php endif; ?>
        
        <form action="verify_recaptcha.php" method="post" id="contactForm">
            
            <label class="form-label">Full Name</label>
            <input type="text" name="name" class="form-control" placeholder="Enter your name" required>

            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" placeholder="name@example.com" required>

            <label class="form-label">Phone Number</label>
            <input type="text" name="phone" class="form-control" placeholder="+40..." required>

            <label class="form-label">Your Message</label>
            <textarea name="content" class="form-control" rows="5" placeholder="How can we help you today?" required></textarea> 
            
            <div class="captcha-wrapper">
                <div class="g-recaptcha" data-sitekey="xxx"></div>
                
                <button type="submit" name="submit" class="btn-submit shadow-sm">SEND MESSAGE</button>
            </div>
        </form>
    </div>
</div>

<script>
// Client-side validation for CAPTCHA
document.getElementById('contactForm').addEventListener('submit', function(e) {
    var response = grecaptcha.getResponse();
    if(response.length === 0) {
        e.preventDefault();
        alert('Please complete the CAPTCHA verification.');
        return false;
    }
});
</script>

<?php 
include_once 'includes/footer.php'; 
?>
