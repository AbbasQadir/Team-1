<?php 
include 'contactHandler.php'; 
include 'navbar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Contact Us</title>
    <link rel="stylesheet" href="contactUsStyle.css">
</head>
<body>
    <main>
        <div class="contact-container" id="container">
            <div class="contact-form-container">
                <h2>Contact Us</h2>
                <form action="contactHandler.php" method="POST" id="contact-me">
                    <p class="description">Please select how you would like to be contacted:</p>
                    <select id="contact-type" class="input-field" name="contact" required>
                        <option value="phone">Phone</option>
                        <option value="email">Email</option>
                        <option value="text">Text</option>
                    </select>

                    <input type="email" class="input-field" id="emailinput" placeholder="Your Email" style="display:none;" name="email1">
                    <input type="email" class="input-field" id="emailconfirm" placeholder="Confirm your Email" style="display:none;" name="email2">
                    <input type="tel" id="phone-field" class="input-field" placeholder="Your Phone Number" name="phone">
                    <input type="text" class="input-field" placeholder="Your Name" name="firstname" required>
                    <input type="date" class="input-field" id="datetime" name="date" required>
                    <textarea class="input-field" placeholder="Your Message" rows="4" name="description" required></textarea>
                    <p id="message" style="color: red;"></p>
                    <button type="submit" class="submit-button">Send Message</button>
                </form>
            </div>
        </div>
    </main>
    <?php include 'footer.php'; ?>
    <script src="contactUsJavaScript.js"></script>
</body>
</html>