<?php
session_start(); 


if (isset($_GET['added']) || isset($_GET['deleted']) || isset($_GET['updated'])) {
    unset($_SESSION['animals']); // Reset cache


    echo "<script>
        let url = new URL(window.location.href);
        url.searchParams.delete('added');
        url.searchParams.delete('deleted');
        url.searchParams.delete('updated');
        window.history.replaceState({}, document.title, url);
    </script>";
}


$api_base_url = "https://x8ki-letl-twmt.n7.xano.io/api:Kk03S2aS/";


function fetchAnimalsCached() {
    global $api_base_url;
    
    if (!isset($_SESSION['animals']) || (time() - $_SESSION['animals']['time'] >= 300)) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_base_url . "animal_data");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        $_SESSION['animals'] = ['data' => $data, 'time' => time()];
        return $data;
    }
    return $_SESSION['animals']['data'];
}

// Pobranie zwierząt
$animals = fetchAnimalsCached();


$species = array();
$sizes = array();
$statuses = array();

foreach ($animals as $animal) {
    $species[] = $animal['_animal_types'][0]['animal'];
    $sizes[] = $animal['_animal_size']['size'];
    $statuses[] = $animal['_adoption_status']['status'];
}

$species = array_unique($species);
sort($species);
$sizes = array_unique($sizes);
sort($sizes);
$statuses = array_unique($statuses);
sort($statuses);
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schronisko dla Zwierząt</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="sidebars.css"> <!-- Stylizacja sidebaru -->
</head>
<body>

    <!-- Navbar (pasek na górze) -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand ms-3" href="index.php">🐾 Schronisko</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link text-white" href="#">👤</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="logout.php">🚪</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="d-flex">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Główna zawartość -->
        <div class="container mt-5" style="margin-left: 270px; padding-top: 70px;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="mb-0">Lista Zwierząt</h2>
            </div>

            <!-- Filtry -->
            <div class="mb-4 p-3 bg-light rounded-3 shadow-sm">
                <div class="row g-3">
                    <div class="col-md-2">
                        <select class="form-select" id="filterSpecies">
                            <option value="">Wszystkie gatunki</option>
                            <?php foreach ($species as $s): ?>
                                <option value="<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" id="filterSize">
                            <option value="">Wszystkie rozmiary</option>
                            <?php foreach ($sizes as $s): ?>
                                <option value="<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" id="filterStatus">
                            <option value="">Wszystkie statusy</option>
                            <?php foreach ($statuses as $s): ?>
                                <option value="<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" id="filterVaccinated">
                            <option value="">Szczepienia</option>
                            <option value="Tak">Tak</option>
                            <option value="Nie">Nie</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" id="filterNeutered">
                            <option value="">Kastracja</option>
                            <option value="Tak">Tak</option>
                            <option value="Nie">Nie</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-secondary w-100" onclick="resetFilters()">Resetuj filtry</button>
                    </div>
                </div>
            </div>

            <!-- Tabela zwierząt -->
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Imię</th>
                        <th>Wiek</th>
                        <th>Gatunek</th>
                        <th>Status Adopcyjny</th>
                        <th>Rozmiar</th>
                        <th>Opiekun</th>
                        <th>Telefon Opiekuna</th>
                        <th>Szczepienia</th>
                        <th>Kastracja</th>
                        <th>Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($animals as $animal): ?>
                        <tr data-species="<?= htmlspecialchars($animal['_animal_types'][0]['animal']) ?>"
                            data-size="<?= htmlspecialchars($animal['_animal_size']['size']) ?>"
                            data-status="<?= htmlspecialchars($animal['_adoption_status']['status']) ?>"
                            data-vaccinated="<?= $animal['is_vaccinated'] ? 'Tak' : 'Nie' ?>"
                            data-neutered="<?= $animal['is_neutered'] ? 'Tak' : 'Nie' ?>">
                            <td><?= htmlspecialchars($animal['name']) ?></td>
                            <td><?= htmlspecialchars($animal['age']) ?> lat</td>
                            <td><?= htmlspecialchars($animal['_animal_types'][0]['animal']) ?></td>
                            <td><?= htmlspecialchars($animal['_adoption_status']['status']) ?></td>
                            <td><?= htmlspecialchars($animal['_animal_size']['size']) ?></td>
                            <td><?= htmlspecialchars($animal['_caretakers']['name'] . " " . $animal['_caretakers']['surname']) ?></td>
                            <td><?= htmlspecialchars($animal['_caretakers']['telephone']) ?></td>
                            <td><?= $animal['is_vaccinated'] ? "Tak" : "Nie" ?></td>
                            <td><?= $animal['is_neutered'] ? "Tak" : "Nie" ?></td>
                            <td>
                                <a href="edit.php?id=<?= $animal['id'] ?>" class="btn btn-warning btn-sm">Edytuj</a>
                                <a href="delete.php?id=<?= $animal['id'] ?>" class="btn btn-danger btn-sm">Usuń</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="sidebars.js"></script>
    <script>
        function filterAnimals() {
            const filters = {
                species: document.getElementById('filterSpecies').value,
                size: document.getElementById('filterSize').value,
                status: document.getElementById('filterStatus').value,
                vaccinated: document.getElementById('filterVaccinated').value,
                neutered: document.getElementById('filterNeutered').value
            };

            document.querySelectorAll('tbody tr').forEach(row => {
                const matches = Object.keys(filters).every(key => {
                    if (!filters[key]) return true;
                    return row.getAttribute(`data-${key}`) === filters[key];
                });

                row.style.display = matches ? '' : 'none';
            });
        }

        function resetFilters() {
            document.querySelectorAll('.form-select').forEach(select => {
                select.value = '';
            });
            filterAnimals();
        }


        document.querySelectorAll('.form-select').forEach(select => {
            select.addEventListener('change', filterAnimals);
        });
    </script>
</body>
</html>