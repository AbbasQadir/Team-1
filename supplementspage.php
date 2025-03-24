<?php
include 'navbar.php';
require_once "PHPHost.php";
?>
<link rel="stylesheet" href="homestyle.css">

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Mind & Motion</title>
        <link rel="stylesheet" href="styless.css">
    </head>
    <body>
    
    <?php 
        $category = "Supplements";
        require "itemList.php";
    ?>


 <div class="quiz-cta">
            <h2>Find Your Perfect Supplement!</h2>
            <p>Not sure which supplement suits you best? Take our quick quiz and get personalised recommendations.</p>
            <a href="quiz.php" class="quiz-btn">Take the Quiz</a>
        </div>
    </div>
    
    <div class="disclaimer">
		<p>"A dietary supplement is not a substitute for diverse and balanced nutrition."</p>
	</div>
    
    </body>
</html>
        
<?php include 'footer.php'; ?>
