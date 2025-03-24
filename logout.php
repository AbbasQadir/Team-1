<?php

	session_start();
	session_destroy();

	header("Location: /");

?>
 <H2> Logged out now! </H2> 
 <p>Would like to log in again? <a href="login.php">Log in</a>  </p>


