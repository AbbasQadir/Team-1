<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    require_once(__DIR__ . '/PHPHost.php');
} catch (Exception $ex) {
    echo "Failed to include PHPHost.php: " . $ex->getMessage();
    exit;
}

if (!isset($_SESSION['uid'])) {
    echo "<script>alert('Please log in to access the quiz.'); window.location.href = 'login.php';</script>";
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'add_to_basket') {
    header('Content-Type: application/json');
    $productId = isset($_GET['productId']) ? (int)$_GET['productId'] : 0;
    if ($productId <= 0) {
        echo json_encode(["error" => "Invalid product ID."]);
        exit;
    }

    $userId = $_SESSION['uid'];

    try {
        $sql = "INSERT INTO asad_basket (user_id, product_id, quantity)
                VALUES (:uid, :pid, 1)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':uid' => $userId,
            ':pid' => $productId
        ]);
        echo json_encode(["success" => true, "message" => "Product added to basket."]);
    } catch (PDOException $e) {
        echo json_encode(["error" => "Basket error: " . $e->getMessage()]);
    }
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'get_recommendation') {
    header('Content-Type: application/json');

    $goal  = $_GET['goal']  ?? '';
    $diet  = $_GET['diet']  ?? '';
    $focus = $_GET['focus'] ?? '';

    $productSearch = '%Multivitamins%';
    if ($goal === 'muscle') {
        if ($focus === 'strength') {
            $productSearch = '%Creatine Monohydrate Powder%';
        } elseif ($focus === 'endurance') {
            $productSearch = '%Optimum Nutrition Whipped Protein Bar%';
        }
    } elseif ($goal === 'weight-gain') {
        $productSearch = '%Serious Mass Protein Powder%';
    } elseif ($goal === 'general-health') {
        if ($focus === 'strength') {
            $productSearch = '%Optimum Nutrition Whipped Protein Bar%';
        } elseif ($focus === 'endurance') {
            if ($diet === 'vegetarian' || $diet === 'vegan') {
                $productSearch = '%Multivitamins and Minerals%';
            } else {
                $productSearch = '%Omega 3 1000mg Capsules%';
            }
        }
    }

    try {
        $sql = "
            SELECT p.product_id,
                   p.product_name,
                   p.product_image,
                   pi.price AS product_price
              FROM product p
              JOIN product_item pi ON p.product_id = pi.product_id
             WHERE p.product_name LIKE :search
             LIMIT 1
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute([':search' => $productSearch]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($product) {
            echo json_encode([
                "productId" => $product['product_id'],
                "name"      => $product['product_name'],
                "image"     => $product['product_image'],
                "price"     => $product['product_price']
            ]);
        } else {
            echo json_encode([
                "productId" => 9999,
                "name"      => "Default Product",
                "image"     => "images/default.jpg",
                "price"     => "9.99"
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode(["error" => "Database error: " . $e->getMessage()]);
    }
    exit;
}

include 'navbar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Multi-Step Supplement Quiz</title>
  <!-- Inline CSS -->
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
    /* Global Reset */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    /* Body */
    body {
      margin: 0;
      padding: 0;
      font-family: 'Merriweather', serif;
      background-color: #f5f5f5;
      color: #000;
      font-size: 18px;
      line-height: 1.6;
    }

    /* Container with dark mode background and white text */
    .container {
      background: linear-gradient(145deg, var(--card-bg), var(--icon-bg));
      border-radius: 15px;
      box-shadow: 0 6px 15px var(--shadow);
      padding: 30px;
      text-align: center;
      color: var(--text-color);
      width: 80%;
      margin: 30px auto;
      display: flex;
      flex-direction: column;
      align-items: center;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .container:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px var(--shadow);
    }

    .progressbar {
      display: flex;
      justify-content: center; 
      align-items: center;      
      margin: 50px auto 30px;
      width: 100%;
      max-width: 800px;
    }

    /* Step Container */
    .step {
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
    }

    /* Circle for each step */
    .circle {
      width: 50px;
      height: 50px;
      background-color: #415A77;
      border-radius: 50%;
      color: #fff;
      font-weight: bold;
      font-size: 1em;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 5px;
      transition: background-color 0.3s ease;
    }

    /* Active step circle */
    .step.active .circle {
      background-color: #0D1B2A;
    }

    /* Label under each step - now white */
    .label {
      font-size: 1em;
      color: var(--text-color);
      margin-bottom: 0;
    }

    /* Connecting lines between steps */
    .line {
      height: 3px;
      width: 80px;
      background-color: #0D1B2A;
      margin: 0 15px;
    }

    /* Quiz Step Content */
    .quiz-step {
      margin: 0 auto;
      max-width: 500px;
    }

    .quiz-step h2 {
      margin-bottom: 25px;
      color: var(--text-color);
      font-size: 24px;
      font-weight: bold;
      text-align: center;
    }

    /* Center each label + radio input; updated text color */
    .quiz-step label {
      display: block;
      margin: 12px auto;
      font-size: 1.05em;
      cursor: pointer;
      text-align: center;
      width: fit-content;
      color: var(--text-color);
    }

    input[type="radio"] {
      margin-right: 8px;
    }

    /* Buttons (base styling with color) */
    .btn {
      background: #415A77;
      color: #fff;
      border-radius: 4px;
      font-size: 1.05em;
      font-weight: bold;
      box-shadow: 2px 2px 8px rgba(0, 0, 0, 0.2);
      cursor: pointer;
      transition: all 0.3s ease;
      text-align: center;
      text-decoration: none;
      width: 200px;
      height: 50px;
      line-height: 50px;
      display: inline-block;
      margin-top: 25px;
    }

    .btn:hover {
      background: #8EA3BB;
      color: #000;
      transform: scale(1.05);
      box-shadow: 3px 3px 12px rgba(0, 0, 0, 0.3);
    }

    /* Grouping multiple buttons side by side */
    .button-group {
      display: flex;
      justify-content: center;
      gap: 20px;
      margin-top: 25px;
    }

    /* Recommendation Section */
    .placeholder-text {
      font-style: italic;
      font-weight: bold;
      color: var(--text-color);
      margin-bottom: 20px;
    }

    /* Recommended Product Container */
    .recommendation-container,
    .single-product {
      display: flex;
      flex-direction: row;
      align-items: center;
      justify-content: center;
      margin: 30px auto 0;
      padding: 20px;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      max-width: 700px;
    }

    /* Product Image - Large, Curved, with Subtle Hover Zoom */
    .recommendation-container img,
    .single-product img {
      width: 260px;
      height: auto;
      object-fit: cover;
      border-radius: 12px;
      margin-right: 20px;
      transition: transform 0.3s ease;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }

    .recommendation-container img:hover,
    .single-product img:hover {
      transform: scale(1.03);
    }

    /* Product Details */
    .recommendation-details {
      display: flex;
      flex-direction: column;
      align-items: center;
      max-width: 350px;
      text-align: center;
    }

    .recommendation-details h3 {
      margin-bottom: 10px;
      font-size: 1.3em;
      font-weight: bold;
      color: #333;
    }

    .recommendation-details p {
      font-size: 1em;
      color: #333;
      margin: 0 0 10px 0;
    }

    #product-price {
      font-weight: bold;
      color: #111;
      margin-bottom: 15px;
    }

    /* Responsive Adjustments */
    @media (max-width: 600px) {
      /* Progress bar adjustments */
      .line {
        width: 40px;
        margin: 0 8px;
      }
      .circle {
        width: 40px;
        height: 40px;
      }

      /* Button adjustments */
      .btn,
      button,
      a.btn,
      .add-to-basket-btn {
        width: 140px !important;
        height: 45px !important;
        line-height: 45px !important;
      }

      /* Recommendation container adjustments */
      .recommendation-container,
      .single-product {
        flex-direction: column;
        max-width: 90%;
      }
      .recommendation-container img,
      .single-product img {
        width: 220px;
        margin: 0 0 15px 0;
      }
      .recommendation-details {
        max-width: 100%;
      }
    }

    /* Error & Success messages */
    .error-message {
      display: none;
      color: red;
      margin-bottom: 15px;
      text-align: center;
      font-weight: bold;
    }
    .success-message {
      display: none;
      color: green;
      margin-bottom: 15px;
      text-align: center;
      font-weight: bold;
    }
  </style>
</head>
<body>

<div class="container">
  <div id="success-message" class="success-message"></div>
  <div id="error-message" class="error-message"></div>

  <h1 class="quiz-title">Multi-Step Supplement Quiz</h1>

  <div class="progressbar">
    <div class="step active" id="step-1">
      <div class="circle">1</div>
      <div class="label">Step 1</div>
    </div>
    <div class="line"></div>
    <div class="step" id="step-2">
      <div class="circle">2</div>
      <div class="label">Step 2</div>
    </div>
    <div class="line"></div>
    <div class="step" id="step-3">
      <div class="circle">3</div>
      <div class="label">Step 3</div>
    </div>
    <div class="line"></div>
    <div class="step" id="step-4">
      <div class="circle">4</div>
      <div class="label">Step 4</div>
    </div>
  </div>

  <div class="quiz-step" id="quiz-step-1">
    <h2>What is your primary fitness goal?</h2>
    <label><input type="radio" name="goal" value="muscle"> Build Muscle</label>
    <label><input type="radio" name="goal" value="weight-gain"> Gain Weight</label>
    <label><input type="radio" name="goal" value="general-health"> General Health</label>
    <button class="btn next-btn" onclick="nextStep()">Next</button>
  </div>

  <div class="quiz-step" id="quiz-step-2" style="display: none;">
    <h2>Do you follow any special diet?</h2>
    <label><input type="radio" name="diet" value="none"> No, I eat everything</label>
    <label><input type="radio" name="diet" value="vegetarian"> Vegetarian</label>
    <label><input type="radio" name="diet" value="vegan"> Vegan</label>
    <div class="button-group">
      <button class="btn prev-btn" onclick="prevStep()">Previous</button>
      <button class="btn next-btn" onclick="nextStep()">Next</button>
    </div>
  </div>

  <div class="quiz-step" id="quiz-step-3" style="display: none;">
    <h2>What do you want to focus on improving?</h2>
    <label><input type="radio" name="focus" value="strength"> Strength</label>
    <label><input type="radio" name="focus" value="endurance"> Endurance</label>
    <label><input type="radio" name="focus" value="immunity"> Immunity</label>
    <div class="button-group">
      <button class="btn prev-btn" onclick="prevStep()">Previous</button>
      <button class="btn next-btn" onclick="calculateRecommendation()">Recommendation</button>
    </div>
  </div>

  <div class="quiz-step" id="quiz-step-4" style="display: none;">
    <h2>Your Personalized Supplement Recommendation</h2>
    <p id="result-text" class="placeholder-text">
      Please complete the quiz steps to see your recommendation.
    </p>
    <div class="single-product recommendation-container" id="recommendation-box" style="display: none;">
      <img id="product-image" src="" alt="Recommended Product">
      <div class="recommendation-details">
        <h3 id="product-name"></h3>
        <p id="product-price"></p>
        <button class="btn add-to-basket-btn" onclick="addToBasket()">Add to Basket</button>
      </div>
    </div>
    <div class="button-group" id="finalButtonGroup" style="display: none; margin-top: 20px;">
      <button class="btn" onclick="cancelQuiz()">Cancel</button>
      <button class="btn" onclick="retakeQuiz()">Retake Quiz</button>
    </div>
  </div>
</div>

<script>
  let currentStep = 1;
  let recommendedProductId = null;

  function showStep(step) {
    document.querySelectorAll('.quiz-step').forEach(div => {
      div.style.display = 'none';
    });
    document.getElementById('quiz-step-' + step).style.display = 'block';
    document.querySelectorAll('.step').forEach((stepElem, index) => {
      if (index < step) {
        stepElem.classList.add('active');
      } else {
        stepElem.classList.remove('active');
      }
    });
  }

  function showError(message) {
    const errorDiv = document.getElementById('error-message');
    errorDiv.innerText = message;
    errorDiv.style.display = 'block';
    errorDiv.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  function hideError() {
    const errorDiv = document.getElementById('error-message');
    errorDiv.innerText = '';
    errorDiv.style.display = 'none';
  }

  function showSuccess(message) {
    const successDiv = document.getElementById('success-message');
    successDiv.innerText = message;
    successDiv.style.display = 'block';
    setTimeout(() => {
      successDiv.style.display = 'none';
    }, 3000);
    successDiv.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  function nextStep() {
    hideError();
    if (currentStep === 1) {
      if (!document.querySelector('input[name="goal"]:checked')) {
        showError("Please select your primary fitness goal.");
        return;
      }
    } else if (currentStep === 2) {
      if (!document.querySelector('input[name="diet"]:checked')) {
        showError("Please select your diet.");
        return;
      }
    }
    if (currentStep < 3) {
      currentStep++;
      showStep(currentStep);
    } else if (currentStep === 3) {
      showError("Use the 'Recommendation' button to proceed.");
    }
  }

  function prevStep() {
    hideError();
    if (currentStep > 1) {
      currentStep--;
      showStep(currentStep);
    }
  }

  function cancelQuiz() {
    window.location.href = "index.php";
  }

  function calculateRecommendation() {
    hideError();
    const goal  = document.querySelector('input[name="goal"]:checked');
    const diet  = document.querySelector('input[name="diet"]:checked');
    const focus = document.querySelector('input[name="focus"]:checked');
    if (!goal || !diet || !focus) {
      showError("Please answer all questions first.");
      return;
    }
    const params = new URLSearchParams({
      action: 'get_recommendation',
      goal: goal.value,
      diet: diet.value,
      focus: focus.value
    });
    fetch('quiz.php?' + params.toString(), { credentials: 'include' })
      .then(res => res.json())
      .then(data => {
        if (data.error) {
          showError("Error: " + data.error);
          return;
        }
        currentStep = 4;
        showStep(4);
        const resultTextElem = document.getElementById("result-text");
        resultTextElem.innerText = `Based on your goal to "${goal.value}" and focus on "${focus.value}", we recommend the following supplement, tailored for your ${diet.value} diet.`;
        resultTextElem.classList.remove("placeholder-text");
        document.getElementById("product-image").src = data.image || "images/default.jpg";
        document.getElementById("product-name").innerText = data.name;
        document.getElementById("product-price").innerText = data.price ? "Price: £" + data.price : "";
        recommendedProductId = data.productId;
        document.getElementById("recommendation-box").style.display = "flex";
        document.getElementById("finalButtonGroup").style.display = "flex";
      })
      .catch(err => {
        console.error(err);
        showError("Error fetching recommendation. Please try again.");
      });
  }

  function retakeQuiz() {
    document.getElementById("recommendation-box").style.display = "none";
    document.getElementById("finalButtonGroup").style.display = "none";
    const resultTextElem = document.getElementById("result-text");
    resultTextElem.innerText = "Please complete the quiz steps to see your recommendation.";
    resultTextElem.classList.add("placeholder-text");
    document.querySelectorAll('input[type="radio"]:checked').forEach(r => r.checked = false);
    currentStep = 1;
    showStep(currentStep);
  }

  function addToBasket() {
    if (!recommendedProductId) {
      showError("No product selected.");
      return;
    }
    const params = new URLSearchParams({
      action: 'add_to_basket',
      productId: recommendedProductId
    });
    fetch('quiz.php?' + params.toString(), { credentials: 'include' })
      .then(res => res.json())
      .then(data => {
        if (data.error) {
          showError("Error: " + data.error);
        } else {
          hideError();
          showSuccess("Item added to basket!");
        }
      })
      .catch(err => {
        console.error(err);
        showError("Error adding product to basket. Please try again.");
      });
  }

  showStep(currentStep);
</script>
</body>
</html>
<?php include 'footer.php'; ?>
