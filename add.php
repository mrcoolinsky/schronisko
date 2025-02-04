<?php
session_start();


$api_base_url = "https://x8ki-letl-twmt.n7.xano.io/api:Kk03S2aS/animal_data"; 


function fetchOptionsCached($endpoint, $sessionKey) {
    global $api_base_url;
    
    if (isset($_SESSION[$sessionKey]) && (time() - $_SESSION[$sessionKey]['time'] < 300)) {
        return $_SESSION[$sessionKey]['data'];
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, str_replace("animal_data", $endpoint, $api_base_url));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    $_SESSION[$sessionKey] = ['data' => $data, 'time' => time()];

    return $data;
}


$animal_types = fetchOptionsCached("animal_types", "animal_types")['animals'];
$adoption_statuses = fetchOptionsCached("adoption_status", "adoption_statuses");
$sizes = fetchOptionsCached("animal_size", "sizes");
$caretakers = fetchOptionsCached("caretakers", "caretakers");

$errors = [];
$response_message = "";


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $age = (int)$_POST['age'];

 
    if (strlen($name) < 2) {
        $errors[] = "Imię musi mieć co najmniej 2 znaki.";
    }
    if ($age < 0 || $age > 25) {
        $errors[] = "Wiek musi być liczbą między 0 a 25.";
    }

    if (empty($errors)) {

        $new_animal = json_encode([
            "name" => $name,
            "age" => $age,
            "animal_type" => (int)$_POST['animal_type'],
            "adoption_status" => (int)$_POST['adoption_status'],
            "animal_size" => (int)$_POST['animal_size'],
            "caretaker" => (int)$_POST['caretaker'],
            "is_vaccinated" => isset($_POST['is_vaccinated']) ? true : false,
            "is_neutered" => isset($_POST['is_neutered']) ? true : false
        ]);


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_base_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $new_animal);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($new_animal)
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);


        if ($http_code == 200 || $http_code == 201) {
            header("Location: index.php?added=1");
            exit;
        } else {
            $response_message = "❌ Błąd API: HTTP CODE $http_code | Odpowiedź API: " . htmlspecialchars($response);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dodaj Zwierzę</title>
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
            <h1>Dodaj Zwierzę</h1>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!empty($response_message)): ?>
                <div class="alert alert-warning"><?= $response_message ?></div>
            <?php endif; ?>

            <form method="POST">
                <label>Imię:</label>
                <input type="text" name="name" class="form-control mb-2" required value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>">

                <label>Wiek:</label>
                <input type="number" name="age" class="form-control mb-2" required min="0" max="25" value="<?= isset($_POST['age']) ? htmlspecialchars($_POST['age']) : '' ?>">

                <label>Gatunek:</label>
                <select name="animal_type" class="form-control mb-2">
                    <?php foreach ($animal_types as $type): ?>
                        <option value="<?= $type['id'] ?>" <?= isset($_POST['animal_type']) && $_POST['animal_type'] == $type['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($type['animal']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label>Status Adopcji:</label>
                <select name="adoption_status" class="form-control mb-2">
                    <?php foreach ($adoption_statuses as $status): ?>
                        <option value="<?= $status['id'] ?>" <?= isset($_POST['adoption_status']) && $_POST['adoption_status'] == $status['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($status['status']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label>Rozmiar:</label>
                <select name="animal_size" class="form-control mb-2">
                    <?php foreach ($sizes as $size): ?>
                        <option value="<?= $size['id'] ?>" <?= isset($_POST['animal_size']) && $_POST['animal_size'] == $size['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($size['size']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label>Opiekun:</label>
                <select name="caretaker" class="form-control mb-2">
                    <?php foreach ($caretakers as $caretaker): ?>
                        <option value="<?= $caretaker['id'] ?>" <?= isset($_POST['caretaker']) && $_POST['caretaker'] == $caretaker['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($caretaker['name'] . " " . $caretaker['surname']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label><input type="checkbox" name="is_vaccinated"> Szczepiony</label><br>
                <label><input type="checkbox" name="is_neutered"> Kastracja</label><br>

                <button type="submit" class="btn btn-success mt-3">Dodaj Zwierzę</button>
                <a href="index.php" class="btn btn-secondary mt-3">Anuluj</a>
            </form>
        </div>
    </div>

    <script src="sidebars.js"></script> <!-- Obsługa sidebaru -->
</body>
</html>
