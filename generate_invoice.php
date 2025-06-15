<?php
// ✅ PHP Webhook for CCAvenue generateQuickInvoice (Deluge Compatible)

// 🔐 Configuration
$working_key = '5359E7A74922E31E22D5EF4DC0545518'; // staging working key
$access_code = 'ATJ5ESBC4GUHISZMC7';              // staging access code
$URL = "https://apitest.ccavenue.com/apis/servlet/DoWebTrans"; // staging API URL

// ✅ Get POST data from Zoho
$customer_name = $_POST['customer_name'] ?? 'Unknown';
$amount = $_POST['amount'] ?? 1.0;
$customer_email = $_POST['customer_email'] ?? 'test@example.com';
$reference_no = $_POST['reference_no'] ?? 'REF123456';

// 📦 Request Body
$merchant_json_data = array(
    "customer_name" => $customer_name,
    "bill_delivery_type" => "email",
    "customer_email_id" => $customer_email,
    "customer_email_subject" => "Payment Invoice",
    "invoice_description" => "Invoice for payment",
    "currency" => "INR",
    "valid_for" => 2,
    "valid_type" => "days",
    "amount" => (float)$amount,
    "merchant_reference_no" => $reference_no
);
$merchant_data = json_encode($merchant_json_data);

// 🔐 Encrypt the request
$encrypted_data = encrypt($merchant_data, $working_key);

// 📤 Prepare API Request
$final_data = "request_type=JSON&access_code=" . $access_code .
              "&command=generateQuickInvoice&version=1.2&response_type=JSON&enc_request=" . $encrypted_data;

// 🌐 Make API Call (cURL)
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_VERBOSE, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $final_data);
$result = curl_exec($ch);
curl_close($ch);

// 📥 Handle Response
$information = explode('&', $result);
$status1 = explode('=', $information[0]);
$status2 = explode('=', $information[1]);

if (trim($status1[1]) == '1') {
    echo "Error: " . urldecode($status2[1]);
    exit;
} else {
    $status = decrypt(trim($status2[1]), $working_key);
    $statusData = json_decode($status, true);
    echo $statusData['tiny_url']; // ✅ Return payment link only for Deluge
    exit;
}

// 🔐 Encrypt Function
function encrypt($plainText, $key) {
    $key = hextobin(md5($key));
    $initVector = pack("C*", 0x00, 0x01, 0x02, 0x03,
        0x04, 0x05, 0x06, 0x07,
        0x08, 0x09, 0x0a, 0x0b,
        0x0c, 0x0d, 0x0e, 0x0f);
    $openMode = openssl_encrypt($plainText, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $initVector);
    return bin2hex($openMode);
}

// 🔓 Decrypt Function
function decrypt($encryptedText, $key) {
    $key = hextobin(md5($key));
    $initVector = pack("C*", 0x00, 0x01, 0x02, 0x03,
        0x04, 0x05, 0x06, 0x07,
        0x08, 0x09, 0x0a, 0x0b,
        0x0c, 0x0d, 0x0e, 0x0f);
    $encryptedText = hextobin($encryptedText);
    $decryptedText = openssl_decrypt($encryptedText, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $initVector);
    return $decryptedText;
}

// 🔄 Hex to Binary Function
function hextobin($hexString) {
    $length = strlen($hexString);
    $binString = "";
    $count = 0;
    while ($count < $length) {
        $subString = substr($hexString, $count, 2);
        $packedString = pack("H*", $subString);
        $binString .= $packedString;
        $count += 2;
    }
    return $binString;
}
?>
