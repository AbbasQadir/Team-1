<?php
session_start();
include 'navbar.php';

try {
    require_once(__DIR__ . '/PHPHost.php');

    $questionTexts = [
        1 => 'Website Ease of Use',
        2 => 'Checkout Process',
        3 => 'Website Navigation',
        4 => 'Product Information',
        5 => 'Customer Support'
    ];


    $sql = "
        SELECT
            r.web_review_id,
            r.review_text,
            r.rating,
            r.created_at AS review_created,
            u.first_name,
            u.last_name,
            s.question_id,
            s.response
        FROM web_review AS r
        JOIN users AS u ON r.user_id = u.user_id
        LEFT JOIN web_review_survey AS s ON r.web_review_id = s.web_review_id
        ORDER BY r.web_review_id DESC, s.id
    ";
    
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);


    $reviews = [];
    foreach ($rows as $row) {
        $rid = $row['web_review_id'];
        if (!isset($reviews[$rid])) {
            $reviews[$rid] = [
                'review_text'      => $row['review_text'],
                'rating'           => $row['rating'],
                'review_created'   => $row['review_created'],
                'first_name'       => $row['first_name'],
                'last_name'        => $row['last_name'],
                'survey_responses' => []
            ];
        }
        if (!empty($row['question_id'])) {
            $reviews[$rid]['survey_responses'][] = [
                'question_id' => $row['question_id'],
                'response'    => $row['response']
            ];
        }
    }
    
} catch (PDOException $e) {
    echo 'Database error: ' . htmlspecialchars($e->getMessage());
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Customer Reviews</title>
  <style>
  
    :root {
      --bg-color: #0d1b2a;
      --text-color: #e0e1dd;
      --secondary-text: #778da9;
      --card-bg: #1b263b;
      --icon-bg: #415a77;
      --shadow: rgba(0, 0, 0, 0.3);
      --accent-color: #778da9;
      --accent-hover: #a8b2c8;
    }
    
  
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    }
    
 
    body {
      margin: 0;
      background-color: var(--bg-color);
      color: var(--text-color);
      font-size: 18px;
      line-height: 1.6;
      transition: background 0.3s ease, color 0.3s ease;
    }
    

    .container {
      width: 95%;
      max-width: 1200px;
      margin: 40px auto;
      padding: 20px;
    }
    
    .sectionHeader {
      font-size: 28px;
      font-weight: bold;
      text-align: center;
      margin-bottom: 30px;
      letter-spacing: 0.5px;
    }
    

    .reviewPreview {
      background: linear-gradient(145deg, var(--card-bg), var(--icon-bg));
      border-radius: 15px;
      padding: 30px;
      margin: 20px auto;
      width: 90%;
      max-width: 1000px;
      box-shadow: 0 6px 15px var(--shadow);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      text-align: center;
      color: var(--text-color);
    }
    .reviewPreview:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px var(--shadow);
    }
    
    .reviewHeader {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-weight: bold;
      font-size: 18px;
      margin-bottom: 15px;
    }
    .reviewUser {
      color: var(--text-color);
    }
    .reviewRating {
      color: var(--text-color);
      font-size: 20px;
    }
    

    .reviewDate {
      font-size: 18px;
      color: var(--text-color);
      font-weight: bold;
      margin-bottom: 15px;
    }
    
    .reviewText {
      font-size: 18px;
      color: var(--text-color);
      font-style: italic;
      margin-bottom: 20px;
      padding: 0 10px;
    }
    
    .reviewSeparator {
      border: none;
      height: 1px;
      background: #ddd;
      margin-top: 15px;
      width: 100%;
    }
    
    .survey-list {
      list-style: none;
      padding-left: 0;
      margin-top: 15px;
    }
    .survey-list li {
      margin-bottom: 8px;
      font-size: 16px;
      color: var(--text-color);
    }
    .survey-list li strong {
      color: var(--text-color);
    }
    .answer-stars {
      color: var(--text-color);
      margin-left: 5px;
    }
    

    @media (max-width: 768px) {
      .reviewPreview {
        width: 100%;
        padding: 20px;
      }
    }
  </style>
</head>
<body>
  <!-- Navbar (already included above) -->
  
  <div class="container">
    <div class="sectionHeader">Customer Reviews</div>
    <?php if (!empty($reviews)): ?>
      <?php foreach ($reviews as $review): ?>
        <div class="reviewPreview">
          <div class="reviewHeader">
            <div class="reviewUser">
              <?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?>
            </div>
            <div class="reviewRating">
              <?php
                $rating = (int)$review['rating'];
                for ($i = 1; $i <= 5; $i++) {
                  echo ($i <= $rating) ? '★' : '☆';
                }
              ?>
            </div>
          </div>
          <div class="reviewDate">
            Reviewed on <?php echo date('F j, Y', strtotime($review['review_created'])); ?>
          </div>
          <div class="reviewText">
            &ldquo;<?php echo nl2br(htmlspecialchars($review['review_text'])); ?>&rdquo;
          </div>
          <div class="reviewSeparator"></div>
          <?php if (!empty($review['survey_responses'])): ?>
            <ul class="survey-list">
              <?php foreach ($review['survey_responses'] as $resp): 
                $qid = (int)$resp['question_id'];
                $questionLabel = isset($questionTexts[$qid]) ? $questionTexts[$qid] : "Question #{$qid}";
                $responseStars = '';
                $responseValue = (int)$resp['response'];
                for ($i = 1; $i <= 5; $i++) {
                  $responseStars .= ($i <= $responseValue) ? '★' : '☆';
                }
              ?>
                <li>
                  <strong><?php echo htmlspecialchars($questionLabel); ?>:</strong>
                  <span class="answer-stars"><?php echo $responseStars; ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="reviewPreview">
        <p>No reviews found.</p>
      </div>
    <?php endif; ?>
  </div>
  
  <?php include 'footer.php'; ?>
</body>
</html>
