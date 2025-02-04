<?php
session_start(); 


$api_base_url = "https://x8ki-letl-twmt.n7.xano.io/api:Kk03S2aS/animal_data/";


$animal_id = $_GET['id'] ?? null;

if (!$animal_id) {
    die("❌ Błąd: Nie znaleziono zwierzęcia.");
}


$ch = curl_init($api_base_url . $animal_id);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);


if ($http_code == 200 || $http_code == 204) {
    unset($_SESSION['animals']); 


    header("Location: index.php?deleted=1");
    exit;
} else {
    echo "<h3>❌ Błąd: Nie udało się usunąć zwierzęcia.</h3>";
    echo "<pre>Odpowiedź API: ";
    print_r($response);
    echo "</pre>";
    exit;
}
?>
