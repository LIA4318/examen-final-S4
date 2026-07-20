<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Statistiques' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?= view('operateur/navbar') ?>

    <div class="container mt-4">
        <h1>Statistiques Globales</h1>
        <p class="text-muted">Rapports et analyses du système</p>

        <!-- Résumé -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h6>Total Clients</h6>
                        <h3><?= $stats_clients['total'] ?? 0 ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h6>Solde Total</h6>
                        <h3><?= number_format($stats_clients['total_solde'] ?? 0, 0, ',', ' ') ?> Ar</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body">
                        <h6>Total Opérations</h6>
                        <h3><?= array_sum(array_column($stats_operations ?? [], 'nb_operations')) ?? 0 ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <h6>Total Gains</h6>
                        <h3><?= number_format($total_gains ?? 0, 0, ',', ' ') ?> Ar</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques détaillées -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-chart-bar"></i> Opérations par type</h5>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Nb</th>
                                    <th>Total</th>
                                    <th>Moyenne</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stats_operations ?? [] as $stat): ?>
                                    <tr>
                                        <td><?= ucfirst($stat['type_operation']) ?></td>
                                        <td><?= $stat['nb_operations'] ?></td>
                                        <td><?= number_format($stat['total_montant'], 0, ',', ' ') ?> Ar</td>
                                        <td><?= number_format($stat['montant_moyen'] ?? 0, 0, ',', ' ') ?> Ar</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-coins"></i> Gains par type</h5>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Nb opérations</th>
                                    <th>Gains totaux</th>
                                    <th>Gain moyen</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($gains_frais ?? [] as $gain): ?>
                                    <tr>
                                        <td><?= ucfirst($gain['type_operation']) ?></td>
                                        <td><?= $gain['nb_operations'] ?></td>
                                        <td><?= number_format($gain['total_gains'], 0, ',', ' ') ?> Ar</td>
                                        <td><?= number_format($gain['gain_moyen'] ?? 0, 0, ',', ' ') ?> Ar</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Opérations par jour -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-calendar"></i> Opérations des 30 derniers jours</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($operations_par_jour)): ?>
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Nb opérations</th>
                                        <th>Total</th>
                                        <th>Frais</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($operations_par_jour as $jour): ?>
                                        <tr>
                                            <td><?= $jour['date'] ?></td>
                                            <td><?= $jour['nb_operations'] ?></td>
                                            <td><?= number_format($jour['total_montant'], 0, ',', ' ') ?> Ar</td>
                                            <td><?= number_format($jour['total_frais'], 0, ',', ' ') ?> Ar</td>
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
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>