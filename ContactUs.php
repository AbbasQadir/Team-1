<?php
include 'navbar.php';

try {
    require_once(__DIR__ . '/PHPHost.php');
} catch (Exception $ex) {
    echo "<p style='color:red'>Failed to include PHPHost.php: " . htmlspecialchars($ex->getMessage()) . "</p>";
    exit;
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $contact = htmlspecialchars($_POST['contact'], ENT_QUOTES, 'UTF-8');
    $name = htmlspecialchars($_POST['firstname'], ENT_QUOTES, 'UTF-8');
    $phone = htmlspecialchars($_POST['phone'], ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars($_POST['email1'], ENT_QUOTES, 'UTF-8');
    $date = htmlspecialchars($_POST['date'], ENT_QUOTES, 'UTF-8');
    $message = htmlspecialchars($_POST['description'], ENT_QUOTES, 'UTF-8');

    $sql = "INSERT INTO Email (`name`, `email`, `number`, `message`, `contact`, `date`)
            VALUES (:name, :email, :number, :message, :contact, :date)";
	$stmt = $db->prepare($sql);

	$data = [
    'name' => $name,
    'email' => $email,
    'number' => $phone,
    'message' => $message,
    'contact' => $contact,
    'date' => $date,
	];

    try {
    $stmt->execute($data);
    echo "<script type='text/javascript'>alert('contact sent successfully');</script>";
	} catch (PDOException $e) {
    die("Error inserting record: " . $e->getMessage());
	}
}

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
        	<form action="#" method="POST" id="contact-me">
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
