<?php

$api_base_url = "https://x8ki-letl-twmt.n7.xano.io/api:Kk03S2aS/animal_data";


$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_base_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$response = curl_exec($ch);
curl_close($ch);
$animals = json_decode($response, true);


header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=zwierzeta.csv');


$output = fopen('php://output', 'w');


fputcsv($output, ['Imię', 'Wiek', 'Gatunek', 'Status Adopcyjny', 'Rozmiar', 'Opiekun', 'Telefon Opiekuna', 'Szczepienia', 'Kastracja']);


foreach ($animals as $animal) {
    fputcsv($output, [
        $animal['name'],
        $animal['age'] . ' lat',
        isset($animal['_animal_types'][0]['animal']) ? $animal['_animal_types'][0]['animal'] : "Nieznany",
        $animal['_adoption_status']['status'],
        $animal['_animal_size']['size'],
        $animal['_caretakers']['name'] . " " . $animal['_caretakers']['surname'],
        $animal['_caretakers']['telephone'],
        $animal['is_vaccinated'] ? "Tak" : "Nie",
        $animal['is_neutered'] ? "Tak" : "Nie"
    ]);
}


fclose($output);
exit;
?>
