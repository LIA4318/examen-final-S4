<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Dashboard Opérateur' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/index.php/operateur">
                <i class="fas fa-cogs"></i> Opérateur Mobile Money
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="/index.php/operateur/dashboard">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/index.php/operateur/prefixes">
                            <i class="fas fa-tags"></i> Préfixes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/index.php/operateur/types-operations">
                            <i class="fas fa-list"></i> Types
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/index.php/operateur/baremes">
                            <i class="fas fa-calculator"></i> Barèmes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/index.php/operateur/clients">
                            <i class="fas fa-users"></i> Clients
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/index.php/operateur/statistiques">
                            <i class="fas fa-chart-bar"></i> Stats
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1>Dashboard Opérateur</h1>
        <p class="text-muted">Vue d'ensemble du système Mobile Money</p>

        <!-- Statistiques -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-users"></i> Clients
                        </h5>
                        <h2><?= $stats_clients['total'] ?? 0 ?></h2>
                        <small>Total clients</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-coins"></i> Solde total
                        </h5>
                        <h2><?= number_format($stats_clients['total_solde'] ?? 0, 0, ',', ' ') ?> Ar</h2>
                        <small>Sur tous les comptes</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-exchange-alt"></i> Opérations
                        </h5>
                        <h2><?= array_sum(array_column($stats_operations ?? [], 'nb_operations')) ?? 0 ?></h2>
                        <small>Total transactions</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-hand-holding-usd"></i> Gains
                        </h5>
                        <h2><?= number_format($total_gains ?? 0, 0, ',', ' ') ?> Ar</h2>
                        <small>Frais perçus</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Opérations par type -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-chart-pie"></i> Opérations par type</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($stats_operations)): ?>
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Nb</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stats_operations as $stat): ?>
                                        <tr>
                                            <td><?= ucfirst($stat['type_operation']) ?></td>
                                            <td><?= $stat['nb_operations'] ?></td>
                                            <td><?= number_format($stat['total_montant'], 0, ',', ' ') ?> Ar</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p class="text-muted">Aucune opération</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-chart-line"></i> Gains par type</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($gains_frais)): ?>
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Nb opérations</th>
                                        <th>Gains totaux</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($gains_frais as $gain): ?>
                                        <tr>
                                            <td><?= ucfirst($gain['type_operation']) ?></td>
                                            <td><?= $gain['nb_operations'] ?></td>
                                            <td><?= number_format($gain['total_gains'], 0, ',', ' ') ?> Ar</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p class="text-muted">Aucun gain</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>