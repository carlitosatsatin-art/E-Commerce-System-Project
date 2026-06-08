<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Orders - Smart Tech</title>
  <link rel="stylesheet" href="Assets/CSS/navBar.css">
  <link rel="stylesheet" href="Assets/CSS/order.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <script src="Assets/JavaScript/script.js" defer></script>
  <script src="Assets/JavaScript/order.js" defer></script>
</head>

<body>

  <!-- Receipt Modal Component -->
  <div class="receipt-modal-overlay" id="receiptModal">
    <div class="receipt-box">

      <button class="receipt-close-btn" onclick="closeReceiptModal()">
        &times;
      </button>

      <div class="receipt-header">
        <h2>Smart Tech</h2>
        <p>Official Purchase Receipt</p>
      </div>

      <div class="receipt-order-info">
        <div>
          <span>Order ID</span>
          <strong id="receiptOrderId">#ST-1001</strong>
        </div>

        <div>
          <span>Date</span>
          <strong id="receiptDate">June 7, 2026</strong>
        </div>

        <div>
          <span>Payment</span>
          <strong id="receiptPayment">Cash on Delivery</strong>
        </div>

        <div>
          <span>Status</span>
          <strong class="receipt-status" id="receiptStatus">Pending</strong>
        </div>
      </div>

      <div class="receipt-products">
        <div class="receipt-products-header">
          <span>Product</span>
          <span>Qty</span>
          <span>Price</span>
          <span>Subtotal</span>
        </div>

        <div id="receiptItems">
          <!-- Product rows will be inserted by JavaScript -->
        </div>
      </div>

      <div class="receipt-summary">
        <div class="receipt-total">
          <span>Grand Total</span>
          <strong id="receiptGrandTotal">₱0.00</strong>
        </div>
      </div>

      <div class="receipt-footer">
        <p>Thank you for shopping with <strong>Smart Tech</strong>.</p>

        <div class="receipt-actions">
          <button class="receipt-print-btn" onclick="printReceipt()">
            Print Receipt
          </button>

          <button class="receipt-cancel-btn" onclick="closeReceiptModal()">
            Close
          </button>
        </div>
      </div>

    </div>
  </div>

  <!-- Navbar -->
  <nav class="navbar">
    <div class="nav-container">
      <h1 class="logo">Smart Tech</h1>

      <div class="search-container">
        <i class="fa-solid fa-magnifying-glass search-icon"></i>
        <input type="text" id="searchInput" onkeyup="searchOrders()" placeholder="Search Orders" class="search-input">
      </div>

      <div class="nav-links">
        <a href="home.php">Home</a>
        <a href="shop.php">Shop</a>
        <a href="order.php" class="active">Order</a>
        <a href="cart.php">Cart</a>
        <a href="User/profile.php">
          <div class="profile-icon">
            <i class="fa-solid fa-user"></i>
          </div>
        </a>
      </div>
    </div>
  </nav>

  <div class="orders-page">
    <h1 class="page-title">Orders</h1>

    <div class="filterContainer">
      <select onchange="filterOrders(this.value)" class="status-filter">
        <option value="all" selected>All</option>
        <option value="pending">Pending</option>
        <option value="shipped">Shipped</option>
        <option value="delivered">Delivered</option>
        <option value="cancelled">Cancelled</option>
      </select>
    </div>

    <div class="orders-container">
      <table class="orders-table">
        <thead>
          <tr>
            <th>Product Ordered</th>
            <th>Order ID</th>
            <th>Unit Price</th>
            <th>Quantity</th>
            <th>Total Price</th>
            <th>Status</th>
            <th>Receipt</th>
          </tr>
        </thead>
        <tbody>
          <!-- Order rows will be inserted by JavaScript -->
        </tbody>
      </table>
    </div>
    <div class="pagination" id="pagination">
      <button id="prevBtn" class="page-btn">Prev</button>
      <div id="pageNumbers">1</div>
      <button id="nextBtn" class="page-btn">Next</button>
    </div>
  </div>
  <div class="review-modal-overlay" id="reviewModal">
    <div class="review-modal-card">
      <div class="modal-header">
        <h2>Write a Review</h2>
        <button class="close-modal-btn" onclick="closeReviewModal()">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <div class="modal-body">
        <p class="modal-product-name">GIGABYTE GeForce RTX 4090 D 24GB WINDFORCE [CDM]</p>
        <!--
        <div class="rating-picker">
          <label>Your Rating</label>
          <div class="stars">
            <i class="fa-regular fa-star" onclick="setRating(1)"></i>
            <i class="fa-regular fa-star" onclick="setRating(2)"></i>
            <i class="fa-regular fa-star" onclick="setRating(3)"></i>
            <i class="fa-regular fa-star" onclick="setRating(4)"></i>
            <i class="fa-regular fa-star" onclick="setRating(5)"></i>
          </div>
        </div>
    -->
        <div class="comment-field-container">
          <label for="reviewComment">Your Feedback</label>
          <textarea id="reviewComment"
            placeholder="Share your experience with this product... Describe item variations, performance, or packaging quality."
            rows="5"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn-cancel" onclick="closeReviewModal()">Cancel</button>
        <button class="btn-submit" onclick="submitReviewForm()">Submit Review</button>
      </div>
    </div>
  </div>

  <script>
    let orders = [];
    let currentPage = 1;
    let totalPages = 1;
    let limit = 5;

    async function fetchOrders() {
      try {
        const response = await fetch('Actions/Order/fetch_orders.php');
        if (!response.ok) {
          console.error("Failed to fetch orders");
          return;
        }
        const data = await response.json();
        orders = data;
        totalPages = Math.ceil(orders.length / limit);
        ordersToShow = orders.slice((currentPage - 1) * limit, currentPage * limit);
        renderOrders(ordersToShow);
        renderPageNumbers();

        if (data.error) {
          console.error("Error fetching orders:", data.error);
          return;
        }
      } catch (error) {
        console.error("Error fetching orders:", error);
      }
    }

    fetchOrders();

    function renderOrders(ordersList = orders) {
      const ordersTableBody = document.querySelector(".orders-table tbody");
      ordersTableBody.innerHTML = "";

      ordersList.forEach(function (order) {
        const row = document.createElement("tr");

        row.innerHTML = `
            <td class="product-cell">
              <img src="${order.image}" alt="${order.product_name}" class="product-img">
              <div class="product-info">
                <strong>${order.product_name}</strong>
              </div>
            </td>
            <td>ORD-${order.order_id}</td>
            <td>₱${Number(order.price).toLocaleString("en-PH", { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
            <td class="qty-col">${order.quantity}</td>
            <td class="price-col">₱${Number(order.price * order.quantity).toLocaleString("en-PH", { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
            <td class="status-col">
              <span class="status ${order.order_status.toLowerCase()}">
                ${order.order_status.charAt(0).toUpperCase() + order.order_status.slice(1)}
              </span>
            </td>
            <td class="receipt-col">
                <button class="receipt-btn" onclick='fetchReceiptData(${order.order_id})'>View Receipt</button>
            </td>
          `;
        ordersTableBody.appendChild(row);
      });
    }

    //pagination logic

    const pageNumbers = document.getElementById("pageNumbers");
    const prevBtn = document.getElementById("prevBtn");
    const nextBtn = document.getElementById("nextBtn");

    function renderPageNumbers() {
      pageNumbers.innerHTML = "";

      for (let i = 1; i <= totalPages; i++) {
        const btn = document.createElement("button");
        btn.className = "page-btn";
        btn.textContent = i;

        if (i === currentPage) {
          btn.classList.add("active");
        }

        btn.addEventListener("click", () => {
          currentPage = i;
          update();
        });

        pageNumbers.appendChild(btn);
      }
    }

    prevBtn.addEventListener("click", () => {
      if (currentPage > 1) {
        currentPage--;
        update();
      }
    });

    nextBtn.addEventListener("click", () => {
      if (currentPage < totalPages) {
        currentPage++;
        update();
      }
    });

    function update() {
      ordersToShow = orders.slice((currentPage - 1) * limit, currentPage * limit);
      renderOrders(ordersToShow);
      renderPageNumbers();

      prevBtn.disabled = currentPage === 1;
      nextBtn.disabled = currentPage === totalPages;
    }

    //search orders logic
    function searchOrders() {
      const query = document.getElementById("searchInput").value.toLowerCase();
      const filteredOrders = orders.filter(order =>
        order.product_name.toLowerCase().includes(query) ||
        (`ORD-${order.order_id}`).toLowerCase().includes(query) ||
        order.order_status.toLowerCase().includes(query)
      );
      renderOrders(filteredOrders);
    }

    //filter orders logic
    function filterOrders(status) {
      if (status === "all") {
        ordersToShow = orders.slice((currentPage - 1) * limit, currentPage * limit);
        renderOrders(ordersToShow);
        totalPages = Math.ceil(orders.length / limit);
      } else {
        const filteredOrders = orders.filter(order => order.order_status.toLowerCase() === status);
        ordersToShow = filteredOrders.slice((currentPage - 1) * limit, currentPage * limit);
        renderOrders(ordersToShow);
        totalPages = Math.ceil(ordersToShow.length / limit);
      }
      renderPageNumbers();
    }

    const receiptModal = document.getElementById("receiptModal");
    const receiptItems = document.getElementById("receiptItems");

    function openReceiptModal(order) {
      loadReceiptData(order);
      receiptModal.classList.add("active");
    }

    function closeReceiptModal() {
      receiptModal.classList.remove("active");
    }

    async function fetchReceiptData(orderId) {
      try {
        const response = await fetch(`Actions/Order/fetch_receipt.php?order_id=${orderId}`);
        if (!response.ok) {
          console.error("Failed to fetch receipt data");
          return;
        }
        const data = await response.json();
        console.log(data);
        if (data.error) {
          console.error("Error fetching receipt:", data.error);
          return;
        }
        openReceiptModal(data.data);
      } catch (error) {
        console.error("Error fetching receipt data:", error);
      }
    }

    function loadReceiptData(order) {
      document.getElementById("receiptOrderId").textContent = order.order_id;
      document.getElementById("receiptDate").textContent = new Date(order.order_date.replace(' ', 'T')).toLocaleString('en-US', {
        month: 'long',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
      }); //date formatting can be improved
      document.getElementById("receiptPayment").textContent = order.payment_method;
      document.getElementById("receiptStatus").textContent = (order.status).charAt(0).toUpperCase() + (order.status).slice(1);

      receiptItems.innerHTML = "";

      let subtotal = 0;

      order.products.forEach(function (item) {
        const itemSubtotal = item.quantity * item.price;
        subtotal += itemSubtotal;

        const row = document.createElement("div");
        row.className = "receipt-item";

        row.innerHTML = `
      <div class="receipt-product-name">
        <strong>${item.product_name}</strong>
        <small>${item.category_name}</small>
      </div>

      <span class="receipt-center">${item.quantity}</span>

      <span class="receipt-right">${formatPeso(item.price)}</span>

      <span class="receipt-right">${formatPeso(itemSubtotal)}</span>`;

        receiptItems.appendChild(row);
      });

      grandTotal = subtotal;
      document.getElementById("receiptGrandTotal").textContent = formatPeso(grandTotal);
    }

    function formatPeso(amount) {
      return "₱" + Number(amount).toLocaleString("en-PH", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
    }

    function printReceipt() {
      window.print();
    }

    receiptModal.addEventListener("click", function (event) {
      if (event.target === receiptModal) {
        closeReceiptModal();
      }
    });

    document.addEventListener("keydown", function (event) {
      if (event.key === "Escape") {
        closeReceiptModal();
      }
    });
  </script>
</body>

</html>