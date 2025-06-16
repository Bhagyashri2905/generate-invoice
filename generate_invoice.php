<?php
$working_key = '5359E7A74922E31E22D5EF4DC0545518';
$access_code = 'ATJ5ESBC4GUHISZMC7';

header("Content-Type: application/json");

// ✅ Read raw JSON instead of $_POST
$input = json_decode(file_get_contents("php://input"), true);

$merchant_data = json_encode($input);
$enc_request = encrypt($merchant_data, $working_key);

echo json_encode([
    "encRequest" => $enc_request,
    "accessCode" => $access_code
]);

// --- Helpers ---
function encrypt($plainText, $key) {
    $key = hextobin(md5($key));
    $initVector = pack("C*", 0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15);
    $openMode = openssl_encrypt($plainText, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $initVector);
    return bin2hex($openMode);
}

function hextobin($hexString) {
    $binString = "";
    for ($i = 0; $i < strlen($hexString); $i += 2) {
        $binString .= pack("H*", substr($hexString, $i, 2));
    }
    return $binString;
}
?>
