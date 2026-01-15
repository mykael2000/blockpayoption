<?php
/**
 * Payment Links - Create Page
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_auth();
check_session_timeout();

// Get active payment methods
try {
    $stmt = $pdo->query("SELECT id, name, symbol FROM payment_methods WHERE is_active = 1 ORDER BY display_order ASC");
    $payment_methods = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Payment methods fetch error: " . $e->getMessage());
    $payment_methods = [];
}

// Get active bank payment methods
$bank_payment_methods = getBankPaymentMethods(true);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST[CSRF_TOKEN_NAME] ?? '')) {
        set_flash('error', 'Invalid security token.');
        redirect('/admin/payment-links/create.php');
    }

    $errors = [];
    
    // Validate required fields
    $payment_type = $_POST['payment_type'] ?? 'crypto';
    $payment_method_id = filter_var($_POST['payment_method_id'] ?? '', FILTER_VALIDATE_INT);
    $bank_payment_method_id = filter_var($_POST['bank_payment_method_id'] ?? '', FILTER_VALIDATE_INT);
    $amount = trim($_POST['amount'] ?? '');
    $recipient_email = trim($_POST['recipient_email'] ?? '');
    $expiry_option = $_POST['expiry_option'] ?? 'custom';
    $expires_at = null;

    // Validate based on payment type
    if ($payment_type === 'crypto' && !$payment_method_id) {
        $errors[] = 'Please select a cryptocurrency payment method.';
    } elseif ($payment_type === 'bank' && !$bank_payment_method_id) {
        $errors[] = 'Please select a bank payment method.';
    }

    if (empty($amount) || !is_numeric($amount) || floatval($amount) <= 0) {
        $errors[] = 'Please enter a valid amount greater than 0.';
    }

    if (!empty($recipient_email) && !is_valid_email($recipient_email)) {
        $errors[] = 'Please enter a valid email address.';
    }

    // Calculate expiration date
    if ($expiry_option === 'custom' && !empty($_POST['expires_at'])) {
        $expires_at = $_POST['expires_at'];
    } elseif ($expiry_option !== 'never') {
        $days = intval($expiry_option);
        if ($days > 0) {
            $expires_at = date('Y-m-d H:i:s', strtotime("+{$days} days"));
        }
    }

    if (empty($errors)) {
        // Get currency based on payment type
        try {
            $currency = '';
            
            if ($payment_type === 'crypto') {
                $stmt = $pdo->prepare("SELECT symbol FROM payment_methods WHERE id = ?");
                $stmt->execute([$payment_method_id]);
                $currency = $stmt->fetchColumn();
                
                if (!$currency) {
                    $errors[] = 'Invalid cryptocurrency payment method selected.';
                }
            } else {
                $stmt = $pdo->prepare("SELECT currency FROM bank_payment_methods WHERE id = ?");
                $stmt->execute([$bank_payment_method_id]);
                $currency = $stmt->fetchColumn();
                
                if (!$currency) {
                    $errors[] = 'Invalid bank payment method selected.';
                }
            }

            if (empty($errors)) {
                // Generate unique ID
                $unique_id = generate_unique_id('pay-');

                // Insert payment link
                $stmt = $pdo->prepare("
                    INSERT INTO payment_links 
                    (unique_id, payment_method_id, payment_type, bank_payment_method_id, amount, currency, recipient_email, status, expires_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?)
                ");
                $stmt->execute([
                    $unique_id,
                    $payment_type === 'crypto' ? $payment_method_id : null,
                    $payment_type,
                    $payment_type === 'bank' ? $bank_payment_method_id : null,
                    floatval($amount),
                    $currency,
                    $recipient_email ?: null,
                    $expires_at
                ]);

                $link_id = $pdo->lastInsertId();

                set_flash('success', 'Payment link created successfully!');
                redirect('/admin/payment-links/view.php?id=' . $link_id);
            }
        } catch (PDOException $e) {
            error_log("Payment link creation error: " . $e->getMessage());
            $errors[] = 'Error creating payment link. Please try again.';
        }
    }

    if (!empty($errors)) {
        set_flash('error', implode('<br>', $errors));
    }
}

$page_title = 'Create Payment Link';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - <?= SITE_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <?php require_once __DIR__ . '/../includes/nav.php'; ?>
    
    <div class="flex">
        <!-- Sidebar -->
        <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="flex-1 p-8">
            <!-- Header -->
            <div class="mb-8">
                <a href="/admin/payment-links/index.php" class="text-purple-600 hover:text-purple-700 font-medium mb-4 inline-block">
                    ← Back to Payment Links
                </a>
                <h1 class="text-3xl font-bold text-gray-800">Create Payment Link</h1>
                <p class="text-gray-600 mt-1">Generate a new cryptocurrency or bank transfer payment link</p>
            </div>

            <!-- Flash Messages -->
            <?php if ($flash = get_flash()): ?>
                <div class="mb-6 p-4 rounded-lg <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-red-100 text-red-700 border border-red-200' ?>">
                    <?= $flash['message'] ?>
                </div>
            <?php endif; ?>

            <!-- Form Card -->
            <div class="max-w-3xl">
                <div class="bg-white rounded-xl shadow-md p-8">
                    <form method="POST" id="createLinkForm">
                        <?= csrf_field() ?>

                        <!-- Payment Type Selector -->
                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                Payment Type <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-2 gap-4">
                                <label class="flex items-center p-4 border-2 border-purple-200 rounded-lg cursor-pointer hover:bg-purple-50 transition payment-type-option" data-type="crypto">
                                    <input type="radio" name="payment_type" value="crypto" class="mr-3 text-purple-600 focus:ring-purple-500" <?= (!isset($_POST['payment_type']) || $_POST['payment_type'] === 'crypto') ? 'checked' : '' ?>>
                                    <div>
                                        <div class="font-semibold text-gray-900 flex items-center">
                                            <span class="text-xl mr-2">₿</span>
                                            Cryptocurrency
                                        </div>
                                        <div class="text-sm text-gray-500">Bitcoin, Ethereum, etc.</div>
                                    </div>
                                </label>
                                <label class="flex items-center p-4 border-2 border-green-200 rounded-lg cursor-pointer hover:bg-green-50 transition payment-type-option" data-type="bank">
                                    <input type="radio" name="payment_type" value="bank" class="mr-3 text-green-600 focus:ring-green-500" <?= (isset($_POST['payment_type']) && $_POST['payment_type'] === 'bank') ? 'checked' : '' ?>>
                                    <div>
                                        <div class="font-semibold text-gray-900 flex items-center">
                                            <span class="text-xl mr-2">🏦</span>
                                            Bank Transfer
                                        </div>
                                        <div class="text-sm text-gray-500">Traditional banking</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Crypto Payment Method -->
                        <div class="mb-6 payment-method-container" id="crypto_payment_method" style="<?= (isset($_POST['payment_type']) && $_POST['payment_type'] === 'bank') ? 'display:none;' : '' ?>">
                            <label for="payment_method_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                Cryptocurrency Method <span class="text-red-500">*</span>
                            </label>
                            <select 
                                name="payment_method_id" 
                                id="payment_method_id" 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                            >
                                <option value="">Select a cryptocurrency</option>
                                <?php foreach ($payment_methods as $pm): ?>
                                    <option value="<?= $pm['id'] ?>" data-symbol="<?= e($pm['symbol']) ?>" <?= (isset($_POST['payment_method_id']) && $_POST['payment_method_id'] == $pm['id']) ? 'selected' : '' ?>>
                                        <?= e($pm['name']) ?> (<?= e($pm['symbol']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="mt-2 text-sm text-gray-500">Choose the cryptocurrency for this payment</p>
                        </div>

                        <!-- Bank Payment Method -->
                        <div class="mb-6 payment-method-container" id="bank_payment_method" style="<?= (!isset($_POST['payment_type']) || $_POST['payment_type'] === 'crypto') ? 'display:none;' : '' ?>">
                            <label for="bank_payment_method_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                Bank Account <span class="text-red-500">*</span>
                            </label>
                            <select 
                                name="bank_payment_method_id" 
                                id="bank_payment_method_id" 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition"
                            >
                                <option value="">Select a bank account</option>
                                <?php foreach ($bank_payment_methods as $bpm): ?>
                                    <option value="<?= $bpm['id'] ?>" data-currency="<?= e($bpm['currency']) ?>" <?= (isset($_POST['bank_payment_method_id']) && $_POST['bank_payment_method_id'] == $bpm['id']) ? 'selected' : '' ?>>
                                        <?= e($bpm['bank_name']) ?> - <?= e($bpm['account_holder_name']) ?> (<?= e($bpm['currency']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="mt-2 text-sm text-gray-500">Choose the bank account for this payment</p>
                        </div>

                        <!-- Amount -->
                        <div class="mb-6">
                            <label for="amount" class="block text-sm font-semibold text-gray-700 mb-2">
                                Amount <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input 
                                    type="text" 
                                    name="amount" 
                                    id="amount" 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition pr-20"
                                    placeholder="0.001"
                                    step="0.00000001"
                                    value="<?= e($_POST['amount'] ?? '') ?>"
                                    required
                                >
                                <span id="currency_label" class="absolute right-4 top-3.5 text-gray-500 font-medium"></span>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">Enter the amount to be paid (supports up to 8 decimal places)</p>
                        </div>

                        <!-- Recipient Email (Optional) -->
                        <div class="mb-6">
                            <label for="recipient_email" class="block text-sm font-semibold text-gray-700 mb-2">
                                Recipient Email <span class="text-gray-400 text-xs">(Optional)</span>
                            </label>
                            <input 
                                type="email" 
                                name="recipient_email" 
                                id="recipient_email" 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                                placeholder="customer@example.com"
                                value="<?= e($_POST['recipient_email'] ?? '') ?>"
                            >
                            <p class="mt-2 text-sm text-gray-500">Optionally specify who this payment is for</p>
                        </div>

                        <!-- Expiration -->
                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Expiration
                            </label>
                            <div class="space-y-3">
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="expiry_option" value="7" class="mr-3 text-purple-600 focus:ring-purple-500" <?= (!isset($_POST['expiry_option']) || $_POST['expiry_option'] === '7') ? 'checked' : '' ?>>
                                    <span class="text-gray-700">7 days from now</span>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="expiry_option" value="14" class="mr-3 text-purple-600 focus:ring-purple-500" <?= (isset($_POST['expiry_option']) && $_POST['expiry_option'] === '14') ? 'checked' : '' ?>>
                                    <span class="text-gray-700">14 days from now</span>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="expiry_option" value="30" class="mr-3 text-purple-600 focus:ring-purple-500" <?= (isset($_POST['expiry_option']) && $_POST['expiry_option'] === '30') ? 'checked' : '' ?>>
                                    <span class="text-gray-700">30 days from now</span>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="expiry_option" value="custom" class="mr-3 text-purple-600 focus:ring-purple-500" <?= (isset($_POST['expiry_option']) && $_POST['expiry_option'] === 'custom') ? 'checked' : '' ?>>
                                    <span class="text-gray-700">Custom date and time</span>
                                </label>
                                <div id="custom_expiry_container" class="ml-8 hidden">
                                    <input 
                                        type="datetime-local" 
                                        name="expires_at" 
                                        id="expires_at" 
                                        class="px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                                        value="<?= e($_POST['expires_at'] ?? '') ?>"
                                    >
                                </div>
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="expiry_option" value="never" class="mr-3 text-purple-600 focus:ring-purple-500" <?= (isset($_POST['expiry_option']) && $_POST['expiry_option'] === 'never') ? 'checked' : '' ?>>
                                    <span class="text-gray-700">Never expires</span>
                                </label>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">Set when this payment link should expire</p>
                        </div>

                        <!-- Info Box -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                            <div class="flex">
                                <span class="text-blue-500 text-xl mr-3">ℹ️</span>
                                <div class="text-sm text-blue-800">
                                    <p class="font-semibold mb-1">Payment Link Information</p>
                                    <ul class="list-disc list-inside space-y-1">
                                        <li>A unique payment link will be generated automatically</li>
                                        <li>The link can be shared with customers via email, message, or website</li>
                                        <li>Payment links are immutable once created (except status updates)</li>
                                        <li>You can track the status of each payment link</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center justify-end space-x-4">
                            <a href="/admin/payment-links/index.php" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition font-medium">
                                Cancel
                            </a>
                            <button 
                                type="submit" 
                                class="px-6 py-3 bg-gradient-to-r from-purple-600 to-blue-600 text-white rounded-lg hover:shadow-lg transition font-medium"
                            >
                                Create Payment Link
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Payment type toggle
        const paymentTypeRadios = document.querySelectorAll('input[name="payment_type"]');
        const cryptoContainer = document.getElementById('crypto_payment_method');
        const bankContainer = document.getElementById('bank_payment_method');
        const cryptoSelect = document.getElementById('payment_method_id');
        const bankSelect = document.getElementById('bank_payment_method_id');
        const currencyLabel = document.getElementById('currency_label');

        paymentTypeRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'crypto') {
                    cryptoContainer.style.display = 'block';
                    bankContainer.style.display = 'none';
                    cryptoSelect.required = true;
                    bankSelect.required = false;
                    updateCurrencyLabel();
                } else {
                    cryptoContainer.style.display = 'none';
                    bankContainer.style.display = 'block';
                    cryptoSelect.required = false;
                    bankSelect.required = true;
                    updateCurrencyLabel();
                }
                
                // Update border colors for selected type
                document.querySelectorAll('.payment-type-option').forEach(opt => {
                    opt.classList.remove('border-purple-500', 'border-green-500', 'bg-purple-50', 'bg-green-50');
                    opt.classList.add('border-purple-200', 'border-green-200');
                });
                const selectedOption = this.closest('.payment-type-option');
                if (this.value === 'crypto') {
                    selectedOption.classList.remove('border-purple-200');
                    selectedOption.classList.add('border-purple-500', 'bg-purple-50');
                } else {
                    selectedOption.classList.remove('border-green-200');
                    selectedOption.classList.add('border-green-500', 'bg-green-50');
                }
            });
        });

        // Update currency label based on selected payment method
        function updateCurrencyLabel() {
            const paymentType = document.querySelector('input[name="payment_type"]:checked').value;
            let symbol = '';
            
            if (paymentType === 'crypto') {
                const selectedOption = cryptoSelect.options[cryptoSelect.selectedIndex];
                symbol = selectedOption.getAttribute('data-symbol') || '';
            } else {
                const selectedOption = bankSelect.options[bankSelect.selectedIndex];
                symbol = selectedOption.getAttribute('data-currency') || '';
            }
            
            currencyLabel.textContent = symbol;
        }

        // Update currency label when payment method changes
        cryptoSelect.addEventListener('change', updateCurrencyLabel);
        bankSelect.addEventListener('change', updateCurrencyLabel);

        // Trigger on page load to set initial values
        const checkedRadio = document.querySelector('input[name="payment_type"]:checked');
        if (checkedRadio) {
            checkedRadio.dispatchEvent(new Event('change'));
        }
        updateCurrencyLabel();

        // Show/hide custom expiry date input
        const expiryRadios = document.querySelectorAll('input[name="expiry_option"]');
        const customExpiryContainer = document.getElementById('custom_expiry_container');

        expiryRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'custom') {
                    customExpiryContainer.classList.remove('hidden');
                } else {
                    customExpiryContainer.classList.add('hidden');
                }
            });
        });

        // Trigger on page load
        const checkedRadio = document.querySelector('input[name="expiry_option"]:checked');
        if (checkedRadio && checkedRadio.value === 'custom') {
            customExpiryContainer.classList.remove('hidden');
        }

        // Validate amount input
        document.getElementById('amount').addEventListener('input', function() {
            let value = this.value;
            // Remove non-numeric characters except decimal point
            value = value.replace(/[^0-9.]/g, '');
            // Ensure only one decimal point
            const parts = value.split('.');
            if (parts.length > 2) {
                value = parts[0] + '.' + parts.slice(1).join('');
            }
            // Limit to 8 decimal places
            if (parts.length === 2 && parts[1].length > 8) {
                value = parts[0] + '.' + parts[1].substring(0, 8);
            }
            this.value = value;
        });
    </script>
</body>
</html>
