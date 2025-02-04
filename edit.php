<?php
session_start();


$api_base_url = "https://x8ki-letl-twmt.n7.xano.io/api:Kk03S2aS/";


$animal_id = $_GET['id'] ?? null;
if (!$animal_id) {
    die("Nie znaleziono zwierzęcia.");
}


function fetchOptionsCached($endpoint, $sessionKey) {
    global $api_base_url;
    
    if (isset($_SESSION[$sessionKey]) && (time() - $_SESSION[$sessionKey]['time'] < 300)) {
        return $_SESSION[$sessionKey]['data'];
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_base_url . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    
    $_SESSION[$sessionKey] = ['data' => $data, 'time' => time()];

    return $data;
}


$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_base_url . "animal_data/" . $animal_id);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$response = curl_exec($ch);
curl_close($ch);
$animal = json_decode($response, true);


$animal_types = fetchOptionsCached("animal_types", "animal_types")['animals'];
$adoption_statuses = fetchOptionsCached("adoption_status", "adoption_statuses");
$sizes = fetchOptionsCached("animal_size", "sizes");
$caretakers = fetchOptionsCached("caretakers", "caretakers");


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $updated_data = [
        'animal_data_id' => $animal['id'],
        'name' => $_POST['name'],
        'age' => (int)$_POST['age'],
        'animal_type' => (int)$_POST['animal_type'],
        'adoption_status' => (int)$_POST['adoption_status'],
        'animal_size' => (int)$_POST['animal_size'],
        'caretaker' => (int)$_POST['caretaker'],
        'is_vaccinated' => isset($_POST['is_vaccinated']),
        'is_neutered' => isset($_POST['is_neutered'])
    ];

    $ch = curl_init($api_base_url . "animal_data/" . $animal_id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($updated_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_exec($ch);
    curl_close($ch);

    header("Location: index.php?updated=1");
    exit;
}

?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edytuj Zwierzę</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="sidebars.css"> <!-- Stylizacja sidebaru -->
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand ms-3" href="index.php">🐾 Schronisko</a>
        </div>
    </nav>

    <div class="d-flex">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Główna zawartość -->
        <div class="container mt-5" style="margin-left: 270px; padding-top: 70px;">
            <h1>Edytuj Zwierzę: <?= htmlspecialchars($animal['name']) ?></h1>

            <form method="POST">
                <label>Imię:</label>
                <input type="text" name="name" value="<?= htmlspecialchars($animal['name']) ?>" class="form-control mb-2" required>

                <label>Wiek:</label>
                <input type="number" name="age" value="<?= htmlspecialchars($animal['age']) ?>" class="form-control mb-2" required>

                <label>Gatunek:</label>
                <select name="animal_type" class="form-control mb-2">
                    <?php foreach ($animal_types as $type): ?>
                        <option value="<?= $type['id'] ?>" <?= ($type['id'] == $animal['animal_type']) ? "selected" : "" ?>>
                            <?= htmlspecialchars($type['animal']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label>Status Adopcji:</label>
                <select name="adoption_status" class="form-control mb-2">
                    <?php foreach ($adoption_statuses as $status): ?>
                        <option value="<?= $status['id'] ?>" <?= ($status['id'] == $animal['adoption_status']) ? "selected" : "" ?>>
                            <?= htmlspecialchars($status['status']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label>Rozmiar:</label>
                <select name="animal_size" class="form-control mb-2">
                    <?php foreach ($sizes as $size): ?>
                        <option value="<?= $size['id'] ?>" <?= ($size['id'] == $animal['animal_size']) ? "selected" : "" ?>>
                            <?= htmlspecialchars($size['size']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label>Opiekun:</label>
                <select name="caretaker" class="form-control mb-2">
                    <?php foreach ($caretakers as $caretaker): ?>
                        <option value="<?= $caretaker['id'] ?>" <?= ($caretaker['id'] == $animal['caretaker']) ? "selected" : "" ?>>
                            <?= htmlspecialchars($caretaker['name'] . " " . $caretaker['surname']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label>
                    <input type="checkbox" name="is_vaccinated" <?= $animal['is_vaccinated'] ? "checked" : "" ?>> Szczepiony
                </label><br>

                <label>
                    <input type="checkbox" name="is_neutered" <?= $animal['is_neutered'] ? "checked" : "" ?>> Kastracja
                </label><br>

                <button type="submit" class="btn btn-success mt-3">Zapisz zmiany</button>
                <a href="index.php" class="btn btn-secondary mt-3">Anuluj</a>
            </form>
        </div>
    </div>

    <script src="sidebars.js"></script> <!-- Obsługa sidebaru -->
</body>
</html>
