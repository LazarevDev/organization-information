<?php
header("Content-Type: application/json");

require_once('api_token.php');

$data = json_decode(file_get_contents("php://input"), true);
$query = $data["query"] ?? "";

if (empty($query)) {
    echo json_encode([]);
    exit;
}

$apiUrl = "https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/party";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Token $apiToken"
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["query" => $query]));

$response = curl_exec($ch);

if (curl_errno($ch)) {
    http_response_code(500);
    echo json_encode(["error" => curl_error($ch)]);
    exit;
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $responseData = json_decode($response, true);
    
    if (isset($responseData['suggestions']) && is_array($responseData['suggestions'])) {
        foreach ($responseData['suggestions'] as &$suggestion) {
            $suggestion['id'] = $suggestion['data']['hid'] ?? ''; 
            $suggestion['full_name'] = $suggestion['value'];
            $suggestion['inn'] = $suggestion['data']['inn'] ?? 'Не указан';
            $suggestion['kpp'] = $suggestion['data']['kpp'] ?? 'Не указан';
            $suggestion['value'] = $suggestion['value'] . " (ИНН: " . ($suggestion['data']['inn'] ?? 'Не указан') . ", КПП: " . ($suggestion['data']['kpp'] ?? 'Не указан') . ")";
        }
        echo json_encode($responseData);
    } else {
        echo json_encode(["suggestions" => []]);
    }
} else {
    http_response_code($httpCode);
    echo json_encode(["error" => "Ошибка API DaData"]);
}
