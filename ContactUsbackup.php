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

<!DOCTYPE HTML>
<html lang = "en">
    <head>
        <title> Contact Us Page </title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel = "stylesheet" href = "stylesheet.css" type = "text/css"> <!-- Links to stylesheet -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="homestyle.css">


        <style id="operaUserStyle"></style>
    </head>
    <body class = "bg-secondary">
        <form id="contact-me" action="" method="POST">
            <!-- Aligns all content to center -->
            <div class="vh-200 d-flex justify-content-center align-items-center">
              <div class="container-fluid">
                <div class="row d-flex justify-content-center">
                  <div class="col-12 col-md-8 col-lg-6">
                    <!-- Creates white card -->
                    <div class="card bg-light">
                        <!-- Creates padded card body -->
                      <div class="card-body p-5">
                        <!-- Sets text to bold / uppercase -->
                          <h2 class="fw-bold mb-2 text-uppercase ">Contact us</h2>
                          <p class=" mb-5">Please enter your details below</p>
                          <div class="mb-3">
                            <!-- Selector for contact type -->
                            <label for="contact" class="form-label ">Contact Type</label><br>
                              <select name="contact" name="contact">
                                <option value="Email">Email</option>
                                <option value="Text">Text</option>
                                <option value="Call">Phone Call</option>
                              </select> 
                          </div>
                          <div class="mb-3">
                         
                            <label for="firstname" class="form-label ">First Name</label>
                            <input type="text" maxlength="50" class="form-control" name="firstname" placeholder="Name" required>
                          </div>
                          <div class="mb-3">
                     
                            <label for="description" class="form-label ">Description</label>
                            <textarea class="form-control" name="description" placeholder="Enter a description" rows="3" required></textarea>
                          </div>
                          <div class="mb-3">
                 
                            <label for="phone" class="form-label ">Phone Number</label>
                            <input type="tel" pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}" maxlength="20" class="form-control" name="phone" placeholder="Enter your number. Format: 123-456-7890" required>
                          </div>
                       
                          <div class = "mb-3">
                            <label for="date" class="form-label">Pick a date</label>
                            <input type="date" class="form-control" name="date" id="datetime" required>
                          </div>
                      
                          <div class="mb-3">
                            <label for="email1" class="form-label ">Email</label>
                            <input type="email"  minlength="8" class="form-control" name="email1" id="emailinput" placeholder="name@example.com" required>
                          </div>
                   
                          <div class="mb-3">
                            <label for="email2" class="form-label ">Repeat Email</label>
                            <input type="email" minlength="8" class="form-control" name="email2" id="emailconfirm" placeholder="name@example.com" required>
                          </div>
                           <p id="message" style="color: red;"></p>
                          <!-- Submit button -->
                          <div class="d-grid">
                            <button class="btn btn-outline-dark" type="submit" name ="submit" value="Send">Send email</button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </form>
          <?php include 'footer.php'; ?>

	<script src="Form.js"></script>  
    </body>
</html>