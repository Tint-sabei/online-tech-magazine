<?php
include_once 'includes/header.php'; 
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
    
    /* Fixed spacing for the captcha and button */
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
        margin-top: 20px; /* Extra space above the button */
    }
    
    .btn-submit:hover { 
        background: #311B92; 
        transform: translateY(-2px);
    }
</style>

<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<div class="container">
    <div class="contact-card">
        <h1 class="brand-text">Contact Us</h1>
        
        <form action="verify_recaptcha.php" method="post">
            
            <label class="form-label">Full Name</label>
            <input type="text" name="name" class="form-control" placeholder="Enter your name" required>

            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" placeholder="name@example.com" required>

            <label class="form-label">Phone Number</label>
            <input type="text" name="phone" class="form-control" placeholder="+40..." required>

            <label class="form-label">Your Message</label>
            <textarea name="content" class="form-control" rows="5" placeholder="How can we help you today?" required></textarea> 
            
            <div class="captcha-wrapper">
                <div class="g-recaptcha" data-sitekey="6LdQJCYsAAAAAGP0iFoCWWWI_GEVgxHBSEgt60-s"></div>
                
                <button type="submit" name="submit" class="btn-submit shadow-sm">SEND MESSAGE</button>
            </div>
        </form>
    </div>
</div>

<?php 
include_once 'includes/footer.php'; 
?>