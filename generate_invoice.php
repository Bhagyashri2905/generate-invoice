<?php
$working_key = '5359E7A74922E31E22D5EF4DC0545518';
$access_code = 'ATJ5ESBC4GUHISZMC7';
$api_url = "https://apitest.ccavenue.com/apis/servlet/DoWebTrans";

header("Content-Type: application/json");

// ✅ Get POST input
$input = $_POST;

// ✅ Encrypt
$merchant_data = json_encode($input);
$enc_request = encrypt($merchant_data, $working_key);

// ✅ Send to CCAvenue
$params = http_build_query([
    "command" => "generateQuickInvoice",
    "access_code" => $access_code,
    "request_type" => "JSON",
    "enc_request" => $enc_request,
    "version" => "1.1"
]);

$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
curl_setopt($ch, CURLOPT_POST, 1);
$response = curl_exec($ch);
curl_close($ch);

// ✅ Parse CCAvenue response
parse_str($response, $response_parts);
$enc_response = $response_parts['enc_response'] ?? '';
$status = $response_parts['status'] ?? '0';

// ✅ Decrypt
$decrypted = decrypt($enc_response, $working_key);
$final = json_decode($decrypted, true);

// ✅ Return only tiny_url or full decrypted response
echo json_encode([
    "status" => $status,
    "link" => $final['tiny_url'] ?? '',
    "full" => $final
]);

// --- helper functions ---
function encrypt($plainText, $key) {
    $key = hextobin(md5($key));
    $initVector = pack("C*", 0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15);
    $openMode = openssl_encrypt($plainText, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $initVector);
    return bin2hex($openMode);
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
