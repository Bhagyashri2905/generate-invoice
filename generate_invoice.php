<?php
$working_key = '5359E7A74922E31E22D5EF4DC0545518';
$access_code = 'ATJ5ESBC4GUHISZMC7';
$URL = "https://apitest.ccavenue.com/apis/servlet/DoWebTrans";

header("Content-Type: application/json");

// ✅ Get JSON body from Zoho/Postman
$input = json_decode(file_get_contents("php://input"), true);

$merchant_data = json_encode($input);
$enc_request = encrypt($merchant_data, $working_key);

// ✅ Prepare request
$final_data = "request_type=JSON&access_code=" . $access_code .
              "&command=generateQuickInvoice&version=1.2&response_type=JSON&enc_request=" . $enc_request;

// ✅ Send to CCAvenue
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_VERBOSE, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $final_data);
$result = curl_exec($ch);
curl_close($ch);

// ✅ Parse CCAvenue Response
parse_str($result, $info);
$enc_response = $info['enc_response'] ?? '';
$status = $info['status'] ?? '0';

$decrypted = decrypt($enc_response, $working_key);
$responseArray = json_decode($decrypted, true);

// ✅ Return tiny_url to Zoho
echo json_encode([
    "status" => $status,
    "tiny_url" => $responseArray['tiny_url'] ?? '',
    "full_response" => $responseArray
]);

// --- Helpers ---
function encrypt($plainText, $key) {
    $key = hextobin(md5($key));
    $initVector = pack("C*", 0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15);
    return bin2hex(openssl_encrypt($plainText, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $initVector));
}

function decrypt($encryptedText, $key) {
    $key = hextobin(md5($key));
    $initVector = pack("C*", 0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15);
    $encryptedText = hextobin($encryptedText);
    return openssl_decrypt($encryptedText, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $initVector);
}

function hextobin($hexString) {
    $binString = "";
    for ($i = 0; $i < strlen($hexString); $i += 2) {
        $binString .= pack("H*", substr($hexString, $i, 2));
    }
    return $binString;
}
?>
