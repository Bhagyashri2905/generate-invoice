<?php
// ✅ CCAvenue staging working key & access code
$working_key = '5359E7A74922E31E22D5EF4DC0545518';
$access_code = 'ATJ5ESBC4GUHISZMC7';

header("Content-Type: application/json");

// ✅ Get JSON payload from Zoho
$input = json_decode(file_get_contents("php://input"), true);

// 🔐 Encrypt
$merchant_data = json_encode($input);
$enc_request = encrypt($merchant_data, $working_key);

// ✅ Respond back to Zoho
echo json_encode([
    "encRequest" => $enc_request,
    "accessCode" => $access_code
]);
exit;

// --- Encryption Function ---
function encrypt($plainText, $key) {
    $key = hextobin(md5($key));
    $initVector = pack("C*", 0x00,0x01,0x02,0x03,0x04,0x05,0x06,0x07,
                           0x08,0x09,0x0a,0x0b,0x0c,0x0d,0x0e,0x0f);
    $openMode = openssl_encrypt($plainText, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $initVector);
    return bin2hex($openMode);
}

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
