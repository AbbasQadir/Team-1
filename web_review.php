<?php
session_start();
include 'navbar.php';


if (!isset($_SESSION['uid'])) {
    echo "<script>window.location.href = 'login.php';</script>";
    exit();
}

$user_id = $_SESSION['uid'];
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;


$orderVerified = false;
$orderDetails = null;
$existingReview = null;
$existingSurvey = null;
$submissionSuccess = false;
$errorMessage = "";

try {
    require_once(__DIR__ . '/PHPHost.php');
    
    
    if ($order_id > 0) {
        $verifyQuery = "SELECT * FROM orders WHERE orders_id = :order_id AND user_id = :user_id";
        $verifyStmt = $db->prepare($verifyQuery);
        $verifyStmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        $verifyStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $verifyStmt->execute();
        $orderDetails = $verifyStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($orderDetails) {
            $orderVerified = true;
            
            
            $reviewQuery = "SELECT * FROM web_review WHERE user_id = :user_id AND orders_id = :order_id";
            $reviewStmt = $db->prepare($reviewQuery);
            $reviewStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $reviewStmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
            $reviewStmt->execute();
            $existingReview = $reviewStmt->fetch(PDO::FETCH_ASSOC);
            
            
            if ($existingReview) {
                $surveyQuery = "SELECT * FROM web_review_survey WHERE web_review_id = :review_id";
                $surveyStmt = $db->prepare($surveyQuery);
                $surveyStmt->bindParam(':review_id', $existingReview['web_review_id'], PDO::PARAM_INT);
                $surveyStmt->execute();
                $existingSurvey = $surveyStmt->fetchAll(PDO::FETCH_ASSOC);
                
                
                if ($existingSurvey) {
                    $surveyData = [];
                    foreach ($existingSurvey as $response) {
                        $surveyData[$response['question_id']] = $response['response'];
                    }
                    $existingSurvey = $surveyData;
                }
            }
        }
    }
    
    
    if ($_SERVER["REQUEST_METHOD"] === "POST" && $orderVerified) {
        $reviewText = trim($_POST['review_text']);
        $rating = (int)$_POST['rating'];
        $improvements = trim($_POST['improvement_suggestions']);
        
        
        if ($rating < 1 || $rating > 5) {
            throw new Exception("Please select a rating between 1 and 5.");
        }
        
        if (empty($reviewText)) {
            throw new Exception("Please provide your feedback in the review.");
        }
        
        
        $db->beginTransaction();
        
        $reviewId = 0;
        
        if ($existingReview) {
            
            $updateQuery = "UPDATE web_review 
                           SET review_text = :review_text, rating = :rating, 
                           improvement_suggestions = :suggestions, created_at = CURRENT_TIMESTAMP 
                           WHERE web_review_id = :review_id";
            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->bindParam(':review_text', $reviewText, PDO::PARAM_STR);
            $updateStmt->bindParam(':rating', $rating, PDO::PARAM_INT);
            $updateStmt->bindParam(':suggestions', $improvements, PDO::PARAM_STR);
            $updateStmt->bindParam(':review_id', $existingReview['web_review_id'], PDO::PARAM_INT);
            $updateStmt->execute();
            
            $reviewId = $existingReview['web_review_id'];
            
            
            $deleteSurveyQuery = "DELETE FROM web_review_survey WHERE web_review_id = :review_id";
            $deleteSurveyStmt = $db->prepare($deleteSurveyQuery);
            $deleteSurveyStmt->bindParam(':review_id', $reviewId, PDO::PARAM_INT);
            $deleteSurveyStmt->execute();
            
        } else {
            
            $insertQuery = "INSERT INTO web_review (user_id, orders_id, review_text, rating, improvement_suggestions)
                           VALUES (:user_id, :order_id, :review_text, :rating, :suggestions)";
            $insertStmt = $db->prepare($insertQuery);
            $insertStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $insertStmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
            $insertStmt->bindParam(':review_text', $reviewText, PDO::PARAM_STR);
            $insertStmt->bindParam(':rating', $rating, PDO::PARAM_INT);
            $insertStmt->bindParam(':suggestions', $improvements, PDO::PARAM_STR);
            $insertStmt->execute();
            
            $reviewId = $db->lastInsertId();
        }
        
        
        $surveyQuestions = [
            1 => 'ease_of_use',
            2 => 'checkout_process',
            3 => 'website_navigation',
            4 => 'product_information',
            5 => 'customer_support'
        ];
        
        foreach ($surveyQuestions as $questionId => $fieldName) {
            if (isset($_POST[$fieldName])) {
                $response = (int)$_POST[$fieldName];
                
                if ($response >= 1 && $response <= 5) {
                    $insertSurveyQuery = "INSERT INTO web_review_survey (web_review_id, question_id, response)
                                         VALUES (:review_id, :question_id, :response)";
                    $insertSurveyStmt = $db->prepare($insertSurveyQuery);
                    $insertSurveyStmt->bindParam(':review_id', $reviewId, PDO::PARAM_INT);
                    $insertSurveyStmt->bindParam(':question_id', $questionId, PDO::PARAM_INT);
                    $insertSurveyStmt->bindParam(':response', $response, PDO::PARAM_INT);
                    $insertSurveyStmt->execute();
                }
            }
        }
        
        
        $db->commit();
        $submissionSuccess = true;
    }
    
} catch (Exception $ex) {
    
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    $errorMessage = $ex->getMessage();
} catch (PDOException $ex) {
    
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    $errorMessage = "Database error: " . $ex->getMessage();
}

$surveyQuestions = [
    [
        'id' => 'ease_of_use',
        'text' => 'Website Ease of Use',
        'hint' => 'How intuitive was our website?'
    ],
    [
        'id' => 'checkout_process',
        'text' => 'Checkout Process',
        'hint' => 'How was your payment experience?'
    ],
    [
        'id' => 'website_navigation',
        'text' => 'Website Navigation',
        'hint' => 'Could you find products easily?'
    ],
    [
        'id' => 'product_information',
        'text' => 'Product Information',
        'hint' => 'Were descriptions helpful and accurate?'
    ],
    [
        'id' => 'customer_support',
        'text' => 'Customer Support',
        'hint' => 'If applicable, rate our support'
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave a Review</title>
    <link rel="stylesheet" href="web_review.css">
</head>
<body>
    <div class="container">
        <h1>Share Your Experience</h1>
        
        <?php if ($submissionSuccess): ?>
            <div class="success-message">
                <h2>Thank You For Your Feedback!</h2>
                <p>We appreciate you taking the time to share your experience with us.</p>
                <a href="index.php" class="return-link">Return to Homepage</a>
            </div>
        <?php elseif (!empty($errorMessage)): ?>
            <div class="error-message">
                <h2>Error</h2>
                <p><?php echo htmlspecialchars($errorMessage); ?></p>
            </div>
        <?php elseif (!$orderVerified): ?>
            <div class="error-message">
                <h2>Invalid Order</h2>
                <p>We couldn't find this order in your account. Please select a valid order to review.</p>
                <a href="order_history.php" class="return-link">View Your Orders</a>
            </div>
        <?php else: ?>
            <div class="review-info">
                <p>Reviewing Order: <span class="order-id">#<?php echo $order_id; ?></span></p>
                <p>Order Date: <?php echo date('F j, Y', strtotime($orderDetails['order_date'])); ?></p>
            </div>
            
            <form method="POST" action="" id="review-form">
                
                <div class="form-group">
                    <label for="rating">How would you rate your overall shopping experience?</label>
                    <div class="rating-container">
                        <span class="star <?php echo ($existingReview && $existingReview['rating'] >= 1) ? 'selected' : ''; ?>" data-value="1">★</span>
                        <span class="star <?php echo ($existingReview && $existingReview['rating'] >= 2) ? 'selected' : ''; ?>" data-value="2">★</span>
                        <span class="star <?php echo ($existingReview && $existingReview['rating'] >= 3) ? 'selected' : ''; ?>" data-value="3">★</span>
                        <span class="star <?php echo ($existingReview && $existingReview['rating'] >= 4) ? 'selected' : ''; ?>" data-value="4">★</span>
                        <span class="star <?php echo ($existingReview && $existingReview['rating'] >= 5) ? 'selected' : ''; ?>" data-value="5">★</span>
                    </div>
                    <input type="hidden" name="rating" id="rating-value" value="<?php echo $existingReview ? $existingReview['rating'] : '0'; ?>">
                </div>
                
                
                <div class="form-group">
                    <label for="review_text">Please share your thoughts about your shopping experience:</label>
                    <textarea name="review_text" id="review_text" required><?php echo $existingReview ? htmlspecialchars($existingReview['review_text']) : ''; ?></textarea>
                </div>
                
                
                <h2>Rate Your Experience</h2>
                <div class="survey-grid">
                    <?php foreach ($surveyQuestions as $index => $question): 
                        $questionId = $index + 1;
                        $currentValue = ($existingSurvey && isset($existingSurvey[$questionId])) ? $existingSurvey[$questionId] : 0;
                    ?>
                    <div class="survey-item">
                        <label for="<?php echo $question['id']; ?>"><?php echo $question['text']; ?></label>
                        <p class="hint-text"><?php echo $question['hint']; ?></p>
                        <div class="survey-options">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                            <label class="survey-option">
                                <input type="radio" name="<?php echo $question['id']; ?>" value="<?php echo $i; ?>" <?php echo ($currentValue == $i) ? 'checked' : ''; ?>>
                                <?php echo $i; ?>
                            </label>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                
                <div class="form-group">
                    <label for="improvement_suggestions">What could we do to improve your shopping experience?</label>
                    <textarea name="improvement_suggestions" id="improvement_suggestions"><?php echo $existingReview && isset($existingReview['improvement_suggestions']) ? htmlspecialchars($existingReview['improvement_suggestions']) : ''; ?></textarea>
                </div>
                
                <button type="submit" class="submit-btn">Submit Review</button>
            </form>
        <?php endif; ?>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            const stars = document.querySelectorAll('.star');
            const ratingInput = document.getElementById('rating-value');
            
            stars.forEach(star => {
                star.addEventListener('click', function() {
                    const value = this.getAttribute('data-value');
                    ratingInput.value = value;
                    
                    
                    stars.forEach(s => {
                        if (s.getAttribute('data-value') <= value) {
                            s.classList.add('selected');
                        } else {
                            s.classList.remove('selected');
                        }
                    });
                });
                
                
                star.addEventListener('mouseover', function() {
                    const value = this.getAttribute('data-value');
                    
                    stars.forEach(s => {
                        if (s.getAttribute('data-value') <= value) {
                            s.style.color = '#FFD700';
                        }
                    });
                });
                
                star.addEventListener('mouseout', function() {
                    stars.forEach(s => {
                        if (!s.classList.contains('selected')) {
                            s.style.color = '#ccc';
                        } else {
                            s.style.color = '#FFD700';
                        }
                    });
                });
            });
            
            
            document.getElementById('review-form').addEventListener('submit', function(e) {
                
                if (ratingInput.value < 1) {
                    e.preventDefault();
                    alert('Please select a rating before submitting.');
                    return false;
                }
                
                
                const reviewText = document.getElementById('review_text').value.trim();
                if (reviewText === '') {
                    e.preventDefault();
                    alert('Please provide your feedback in the review field.');
                    return false;
                }
            });
        });
    </script>
</body>
</html>
<?php include 'footer.php'; ?>