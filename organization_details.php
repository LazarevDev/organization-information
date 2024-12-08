<?php
require_once('api_token.php');
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);
$id = $data["id"] ?? "";

if (empty($id)) {
    echo json_encode(["success" => false, "message" => "ID организации не указан"]);
    exit;
}

$apiUrl = "https://suggestions.dadata.ru/suggestions/api/4_1/rs/findById/party";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Token $apiToken"
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["query" => $id]));

$response = curl_exec($ch);

if (curl_errno($ch)) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => curl_error($ch)]);
    exit;
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $responseData = json_decode($response, true);
    if (!empty($responseData['suggestions'][0]['data'])) {
        $data = $responseData['suggestions'][0]['data'];

        echo json_encode([
            "success" => true,
            "details" => [
                "name" => $data['name']['full_with_opf'] ?? "Не указано", // Полное название с формой
                "inn" => $data['inn'] ?? "Не указан",
                "ogrn" => $data['ogrn'] ?? "Не указан",
                "address" => $data['address']['unrestricted_value'] ?? "Не указан",
                "status" => $data['state']['status'] ?? "Не указан",
                "registration_date" => isset($data['state']['registration_date']) 
                    ? date("d.m.Y", $data['state']['registration_date'] / 1000) 
                    : "Не указана",
            ],
        ]);
    } else {
    echo json_encode(["success" => false, "message" => "Данные об организации не найдены"]);
    }
} else {
    http_response_code($httpCode);
    echo json_encode(["success" => false, "error" => "Ошибка API DaData"]);
}
