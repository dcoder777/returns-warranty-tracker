<?php

declare(strict_types=1);

require __DIR__ . '/db.php';

$errors = [];
$successMessage = '';
$searchTerm = trim($_GET['search'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $serialNumber = trim($_POST['serial_number'] ?? '');
    $invoiceNumber = trim($_POST['invoice_number'] ?? '');
    $productName = trim($_POST['product_name'] ?? '');
    $returnReason = trim($_POST['return_reason'] ?? '');
    $returnDate = trim($_POST['return_date'] ?? '');
    $warrantyExpiryDate = trim($_POST['warranty_expiry_date'] ?? '');

    if ($serialNumber === '' || mb_strlen($serialNumber) > 100) {
        $errors[] = 'Serial number is required and must be 100 characters or fewer.';
    }

    if ($invoiceNumber === '' || mb_strlen($invoiceNumber) > 100) {
        $errors[] = 'Invoice number is required and must be 100 characters or fewer.';
    }

    if ($productName === '' || mb_strlen($productName) > 150) {
        $errors[] = 'Product name is required and must be 150 characters or fewer.';
    }

    if ($returnReason === '' || mb_strlen($returnReason) > 500) {
        $errors[] = 'Return reason is required and must be 500 characters or fewer.';
    }

    $returnDateObj = DateTime::createFromFormat('Y-m-d', $returnDate);
    $expiryDateObj = DateTime::createFromFormat('Y-m-d', $warrantyExpiryDate);

    if (!$returnDateObj || $returnDateObj->format('Y-m-d') !== $returnDate) {
        $errors[] = 'Return date must be a valid date.';
    }

    if (!$expiryDateObj || $expiryDateObj->format('Y-m-d') !== $warrantyExpiryDate) {
        $errors[] = 'Warranty expiry date must be a valid date.';
    }

    if (empty($errors)) {
        $insertSql = 'INSERT INTO returns (serial_number, invoice_number, product_name, return_reason, return_date, warranty_expiry_date)
                      VALUES (:serial_number, :invoice_number, :product_name, :return_reason, :return_date, :warranty_expiry_date)';

        $statement = $pdo->prepare($insertSql);
        $statement->execute([
            ':serial_number' => $serialNumber,
            ':invoice_number' => $invoiceNumber,
            ':product_name' => $productName,
            ':return_reason' => $returnReason,
            ':return_date' => $returnDate,
            ':warranty_expiry_date' => $warrantyExpiryDate,
        ]);

        header('Location: index.php?success=1');
        exit;
    }
}

if (isset($_GET['success'])) {
    $successMessage = 'Return record saved successfully.';
}

$querySql = 'SELECT id, serial_number, invoice_number, product_name, return_reason, return_date, warranty_expiry_date
             FROM returns';
$params = [];

if ($searchTerm !== '') {
    $querySql .= ' WHERE serial_number LIKE :search OR invoice_number LIKE :search';
    $params[':search'] = '%' . $searchTerm . '%';
}

$querySql .= ' ORDER BY return_date DESC, id DESC';

$listStatement = $pdo->prepare($querySql);
$listStatement->execute($params);
$records = $listStatement->fetchAll();

$today = new DateTimeImmutable('today');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return & Warranty Tracker</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
    <header>
        <h1>Return &amp; Warranty Tracker</h1>
        <p>Track product returns and monitor warranty expiry in one place.</p>
    </header>

    <section class="card">
        <h2>Log a Return</h2>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($successMessage !== ''): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="post" class="form-grid" novalidate>
            <label>
                Serial Number
                <input type="text" name="serial_number" maxlength="100" required value="<?= htmlspecialchars($_POST['serial_number'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </label>

            <label>
                Invoice Number
                <input type="text" name="invoice_number" maxlength="100" required value="<?= htmlspecialchars($_POST['invoice_number'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </label>

            <label class="full-width">
                Product Name
                <input type="text" name="product_name" maxlength="150" required value="<?= htmlspecialchars($_POST['product_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </label>

            <label class="full-width">
                Return Reason
                <textarea name="return_reason" maxlength="500" rows="3" required><?= htmlspecialchars($_POST['return_reason'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </label>

            <label>
                Return Date
                <input type="date" name="return_date" required value="<?= htmlspecialchars($_POST['return_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </label>

            <label>
                Warranty Expiry Date
                <input type="date" name="warranty_expiry_date" required value="<?= htmlspecialchars($_POST['warranty_expiry_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </label>

            <button type="submit" class="full-width">Save Return</button>
        </form>
    </section>

    <section class="card">
        <h2>Search Returns</h2>
        <form method="get" class="search-form">
            <input type="search" name="search" placeholder="Search by serial number or invoice" value="<?= htmlspecialchars($searchTerm, ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit">Search</button>
            <?php if ($searchTerm !== ''): ?>
                <a href="index.php" class="button-link">Clear</a>
            <?php endif; ?>
        </form>
    </section>

    <section class="card">
        <h2>Return Records</h2>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Serial #</th>
                        <th>Invoice #</th>
                        <th>Product</th>
                        <th>Reason</th>
                        <th>Return Date</th>
                        <th>Warranty Expiry</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($records)): ?>
                    <tr>
                        <td colspan="7" class="empty-cell">No records found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($records as $record): ?>
                        <?php
                        $expiryDate = new DateTimeImmutable($record['warranty_expiry_date']);
                        $interval = $today->diff($expiryDate);
                        $daysToExpiry = (int)$interval->format('%r%a');
                        if ($daysToExpiry < 0) {
                            $status = 'Expired';
                            $statusClass = 'status-expired';
                        } elseif ($daysToExpiry <= 30) {
                            $status = 'Expiring Soon';
                            $statusClass = 'status-warning';
                        } else {
                            $status = 'Active';
                            $statusClass = 'status-active';
                        }
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($record['serial_number'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($record['invoice_number'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($record['product_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($record['return_reason'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($record['return_date'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($record['warranty_expiry_date'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><span class="status-pill <?= $statusClass ?>"><?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
</body>
</html>
