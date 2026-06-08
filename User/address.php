<?php
session_start();
require_once "../Database/dB.php";

$user_id = $_SESSION["user_id"] ?? null;

if (!$user_id) {
    header("Location: login.php");
    exit;
}

$stmt = $pdo->prepare("
    SELECT *
    FROM addresses
    WHERE user_id = :user_id
    ORDER BY created_at DESC
");

$stmt->execute([
    ":user_id" => $user_id
]);

$addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT a.* 
    FROM addresses a
    JOIN users u ON a.address_id = u.current_address_id
    WHERE a.user_id = ?
");

$stmt->execute([$_SESSION['user_id']]);


$currentAddress = $stmt->fetch() ?? null;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Address | Smart Tech</title>

    <link rel="stylesheet" href="../Assets/CSS/address.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <script src="../Assets/JavaScript/script.js" defer></script>
    <script src="../Assets/JavaScript/address.js" defer></script>
</head>

<body>

    <div class="glow-orb orb-1"></div>
    <div class="glow-orb orb-2"></div>

    <div class="info-card">
        <h2 class="page-title">Delivery Address</h2>

        <div id="viewMode">
            <div class="info-row">
                <span>Full Name</span>
                <p id="nameText">
                    <?= $currentAddress ? htmlspecialchars($currentAddress["full_name"]) : "No address yet"; ?>
                </p>
            </div>

            <div class="info-row">
                <span>Phone Number</span>
                <p id="phoneText">
                    <?= $currentAddress ? htmlspecialchars($currentAddress["phone"]) : "No address yet"; ?>
                </p>
            </div>

            <div class="info-row">
                <span>Address</span>
                <p id="addressText">
                    <?php if ($currentAddress): ?>
                        <?= htmlspecialchars(
                            $currentAddress["address_line"] . ", " .
                            $currentAddress["city"] . ", " .
                            $currentAddress["province"] .
                            (!empty($currentAddress["postal_code"]) ? " " . $currentAddress["postal_code"] : "")
                        ); ?>
                    <?php else: ?>
                        No address yet
                    <?php endif; ?>
                </p>
            </div>

            <div class="btn-group">
                <button type="button" class="btn-secondary" onclick="goBack()">
                    <i class="fa-solid fa-arrow-left"></i> Back
                </button>

                <button type="button" class="btn-primary" onclick="showEditMode()">
                    <i class="fa-solid fa-pen"></i> Edit
                </button>
            </div>
        </div>

        <div id="editMode" style="display: none;">

            <div class="address-select-section" id="addressSelectSection">
                <select id="selectedAddress" name="selected_address_id">
                    <option value="">Select Saved Address</option>

                    <?php foreach ($addresses as $address): ?>
                        <option value="<?= htmlspecialchars($address["address_id"]); ?>"
                            data-address-id="<?= htmlspecialchars($address["address_id"]); ?>"
                            data-full-name="<?= htmlspecialchars($address["full_name"]); ?>"
                            data-phone="<?= htmlspecialchars($address["phone"]); ?>"
                            data-address-line="<?= htmlspecialchars($address["address_line"]); ?>"
                            data-city="<?= htmlspecialchars($address["city"]); ?>"
                            data-province="<?= htmlspecialchars($address["province"]); ?>"
                            data-postal-code="<?= htmlspecialchars($address["postal_code"] ?? ""); ?>">
                            <?= htmlspecialchars(
                                $address["full_name"] . " - " .
                                $address["address_line"] . ", " .
                                $address["city"] . ", " .
                                $address["province"] .
                                (!empty($address["postal_code"]) ? " " . $address["postal_code"] : "")
                            ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <div class="btn-group">
                    <button type="button" class="btn-secondary" onclick="cancelEdit()">
                        Cancel
                    </button>

                    <button type="button" class="btn-primary" onclick="useSelectedAddress()">
                        Use Selected Address
                    </button>
                </div>

                <div class="btn-group">
                    <button type="button" class="btn-primary" onclick="showAddAddressForm()">
                        <i class="fa-solid fa-plus"></i> Add New Address
                    </button>
                </div>
            </div>

            <div class="edit-form" id="editForm" style="display: none;">
                <input type="hidden" id="addressId" name="address_id">

                <input type="text" id="newFullName" name="full_name" placeholder="Enter Full Name" required>

                <input type="text" id="newPhone" name="phone" placeholder="Enter Phone Number" required>

                <input type="text" id="newAddressLine" name="address_line" placeholder="Enter Complete Address"
                    required>

                <select id="newProvince" name="province" required>
                    <option value="">Select Province</option>
                </select>

                <select id="newCity" name="city" required disabled>
                    <option value="">Select City</option>
                </select>

                <input type="text" id="newPostalCode" name="postal_code" placeholder="Enter Postal Code">

                <div class="btn-group">
                    <button type="button" class="btn-secondary" onclick="backToAddressSelect()">
                        Back
                    </button>

                    <button type="button" class="btn-primary" onclick="saveAddress()">
                        <i class="fa-solid fa-check"></i> Save
                    </button>
                </div>
            </div>

        </div>
    </div>

</body>

</html>