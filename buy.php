<?php
session_start();
require_once 'Database/runQuery.php';
$sql = "SELECT c.cart_id, c.quantity, p.product_id, p.product_name, p.price, p.image FROM cart c JOIN products p ON c.product_id = p.product_id WHERE c.user_id = ? ORDER BY c.created_at DESC;";
$cartItems = runQuery($pdo, $sql, [$_SESSION['user_id']], true);
if (empty($cartItems)) {
  header('Location: cart.php');
  exit;
}
$totalPrice = 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Checkout - Smart Tech</title>
  <link rel="stylesheet" href="Assets/CSS/navBar.css">
  <link rel="stylesheet" href="Assets/CSS/buy-css.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <script src="Assets/JavaScript/script.js" defer></script>
  <script src="Assets/JavaScript/buy.js" defer></script>
</head>

<body>

  <!-- Navbar -->
  <nav class="navbar">
    <div class="nav-container">
      <h1 class="logo">Smart Tech</h1>

      <div class="nav-links">
        <a href="home.php">Home</a>
        <a href="shop.php">Shop</a>
        <a href="order.php">Order</a>
        <a href="cart.php">Cart</a>
        <a href="User/profile.php">
          <div class="profile-icon">
            <i class="fa-solid fa-user"></i>
          </div>
        </a>
      </div>
    </div>
  </nav>

  <div class="cart-page">
    <h1 class="page-title">Checkout</h1>

    <!-- Address Bar + Place Order -->
    <div class="address-checkout-container">
      <div class="address-bar">
        <div class="address-content">
          <i class="fa-solid fa-location-dot location-icon"></i>
          <div class="address-details">
            <div class="user-info">
              <strong>Mark Francis G. Lampit</strong>
              <span>09329676767</span>
            </div>
            <p class="full-address">
              Block 5 Lot 25, Santa Rosa 1, Saint Rose Village, Noveleta, Cavite 4105
            </p>
          </div>
        </div>
        <a href="User/address.php" class="change-address-btn">Change address</a>
      </div>

      <a class="checkout-btn" onclick="placeOrder()">Place Order</a>
    </div>

    <!-- Order Items -->
    <div class="cart-items">
      <table class="cart-table">
        <thead>
          <tr>
            <th>Product Ordered</th>
            <th class="price-col">Unit Price</th>
            <th class="qty-col">Quantity</th>
            <th class="price-col">Total Price</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($cartItems as $item): ?>
            <?php $totalPrice += $item['price'] * $item['quantity']; ?>
            <tr>
              <td class="product-cell">
                <img src="<?= $item['image'] ?>" alt="<?= $item['product_name'] ?>" class="product-img">
                <strong><?= $item['product_name'] ?></strong>
              </td>
              <td class="price-col">₱<?= number_format($item['price'], 2) ?></td>
              <td class="qty-col"><?= $item['quantity'] ?></td>
              <td class="price-col total-price">₱<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div style="display: flex; justify-content: end; margin-bottom:20px">
      <span class="info-section" style="display: flex; align-items: center;">
        <span style="color:#4e0b99; padding:0; font-weight: bold; font-size: 1.5rem;">Grand Total:</span>
        <span style="font-size:1.2rem; font-weight: bold; color:darkgreen">₱<?= number_format($totalPrice, 2) ?></span>
      </span>
    </div>

    <!-- Payment Method -->
    <div class="info-section">
      <div class="section-header">
        <h3 class="section-title">Payment Method</h3>
      </div>
      <div class="payment-options">
        <button class="payment-btn active" onclick="selectPayment(this)">COD</button>
        <button class="payment-btn active" onclick="selectPayment(this)">GCash</button>
        <button class="payment-btn active" onclick="selectPayment(this)">Maya</button>
        <button class="payment-btn active" onclick="selectPayment(this)">Credit/Debit Card</button>
      </div>
    </div>

    <!-- Item Arrival -->
    <div class="info-section">
      <div class="section-header">
        <h3 class="section-title">Item arrival</h3>
      </div>
      <div class="arrival-content">
        <p class="arrival-info">
          Orders within Luzon are expected to arrive within 3 days. For deliveries to Visayas and Mindanao, estimated
          shipping time is 3–7 days, depending on location and courier processing.
        </p>
      </div>
    </div>

  </div>

  <!-- Order Placed Popup -->
  <div id="orderPlacedPopup" class="added-popup">
    <div class="popup-content">
      <div class="check-circle">
        <i class="fa-solid fa-check"></i>
      </div>
      <h3>ORDER PLACED</h3>
    </div>
  </div>

  <script>
    async function placeOrder() {
      //alerts will be later replaced with sweet alerts
      if (paymentMethod == "") {
        alert('Please select a payment method.');
        return;
      }
      const formData = new FormData();
      formData.append('total_price', <?= $totalPrice ?>);
      formData.append('payment_method', paymentMethod);

      try {
        const response = await fetch('Actions/Buy/place_order.php', {
          method: 'POST',
          body: formData
        });
        const result = await response.json();
        if (result.success) {
          // Order placed successfully, redirect to order page
          showOrderPlacedPopup();
          setTimeout(() => {
            window.location.href = 'order.php';
          }, 2000);
        } else {
          alert('Failed to place order: ' + result.error);
        }
      } catch (error) {
        alert('An error occurred: ' + error.message);
      }
    }
  </script>

</body>

</html>