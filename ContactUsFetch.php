<?php
ob_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_log.php'); 
    exit();
}

include 'sidebar.php';

try {
    require_once(__DIR__ . '/PHPHost.php');
} catch (Exception $ex) {
    echo "<p style='color:red'>Failed to include PHPHost.php: " . htmlspecialchars($ex->getMessage()) . "</p>";
    exit;
}


// Fetches all data from DB and is then displayed on page.
$sql = "SELECT name, contact, number, email, date, message, id FROM Email";
$stmt = $db->prepare($sql);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// If method is delete then it will delete that record from DB
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    $id = $_POST['delete_id'];

    try {
        $sql = "DELETE FROM Email WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        // Redirect to avoid form resubmission
        header("Location: ContactUsFetch.php");
        exit();
    } catch (PDOException $e) {
        die("Deletion failed: " . $e->getMessage());
    }
}

// Allow buffering and allow changes
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Records</title>
	<link rel="stylesheet" href="admin-dashboard.css">

    <style>
		h2 { 
    		text-align: center; 
    		color: var(--text-color); 
		}

        body { 
            font-family: Arial, sans-serif; 
            max-width: 80%;  /* Limits the width of the page */
            margin: auto;    /* Centers the page */
            padding: 20px;   /* Adds space around content */
            background-color: var(--bg-color);
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px; 
            background: var(--card-bg); 
            border: 2px solid #415A77;
        }
        th, td { 
            border: 1px solid #415A77;
            padding: 10px; /* More padding for spacing */
            text-align: left; 
        }
        th { 
            background-color: #0D1B2A; 
            color: white; 
        }
		td { 
    		border: 1px solid var(--border-color); 
    		padding: 6px 8px; /* Reduce padding for a smaller text box area */
    		text-align: left; 
   		 	max-width: 150px; /* Limit the width of table cells */
    		white-space: nowrap; /* Prevent text from wrapping */
   	 		overflow: hidden; /* Hide overflowing text */
    		text-overflow: ellipsis; /* Add "..." when text is too long */
		}

        tr:nth-child(even) { background-color: var(--nth-color); }
        .delete-btn { 
            background-color: #1B263B; 
            color: white; 
            padding: 8px 12px; 
            border: none; 
            cursor: pointer; 
            border-radius: 5px; 
        }
        .delete-btn:hover { background-color: #778DA9; }
        .read-btn { background-color: #28a745; }
        .read-btn:hover { background-color: #218838; }
		
         .read-btn { 
    		background-color: #28a745; 
    		padding: 10px 16px; /* Increased padding */
    		font-size: 14px; /* Slightly bigger text */
    		border-radius: 5px; 
    		cursor: pointer; 
    		border: none;
    		color: white; 
   		 	transition: 0.3s ease-in-out;
		}
        /* Modal Styles */
        .modal {
            display: none; 
            position: fixed; 
            z-index: 1000; 
            left: 0; 
            top: 0; 
            width: 100%; 
            height: 100%; 
            background-color: rgba(0,0,0,0.5); 
        }
        .modal-content {
            background-color: var(--card-bg);
            margin: 10% auto;
            padding: 20px;
            border-radius: 10px;
            width: 50%;
            text-align: center;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3);
        }
        .close-btn {
            color: red;
            font-size: 20px;
            float: right;
            cursor: pointer;
        }
        .close-btn:hover {
            color: darkred;
        }
    </style>
</head>
<body>

    <h2>Email Records</h2>
    <!-- Create a table -->
    <table>
    <!-- Create the header row and fill with categories -->
        <thead>
            <tr>
                <th>Name</th>
                <th>Contact</th>
                <th>Number</th>
                <th>Email</th>
                <th>Date</th>
                <th>Message</th>
                <th>ID</th>
                <th>Action</th>
            </tr>
        </thead>
    	<!-- Create the body row and fill with data with a foreach if there are any records available to pull from DB -->
        <tbody>
            <?php
            if ($results && count($results) > 0) {
                foreach ($results as $row) {
                    echo "<tr>
                            <td>{$row['name']}</td>
                            <td>{$row['contact']}</td>
                            <td>{$row['number']}</td>
                            <td>{$row['email']}</td>
                            <td>{$row['date']}</td>
                            <td>
                                <button class='btn read-btn' onclick='openModal(\"".htmlspecialchars($row['message'], ENT_QUOTES)."\")'>Read</button>
                            </td>
                            <td>{$row['id']}</td>
                            <td>
                                <form method='POST' onsubmit='return confirm(\"Are you sure you want to delete this record?\");'>
                                    <input type='hidden' name='delete_id' value='{$row['id']}'>
                                    <button type='submit' class='delete-btn'>Delete</button>
                                </form>
                            </td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='8'>No records found</td></tr>";
            }
            ?>
        </tbody>
    </table>
	<!-- Create a modal to display user messages on screen -->
    <div id="messageModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal()">&times;</span>
            <h3>Full Message</h3>
            <p id="fullMessage"></p>
        </div>
    </div>
	<!-- On click of read, open the modal -->
    <script>
        function openModal(message) {
            document.getElementById('fullMessage').innerText = message;
            document.getElementById('messageModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('messageModal').style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target == document.getElementById('messageModal')) {
                closeModal();
            }
        }
    </script>
            
</body>
</html>
