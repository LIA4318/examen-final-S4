<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Situation des Gains' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include_once 'navbar.php'; ?>

    <div class="container mt-4">
        <h1>Situation des Gains</h1>
        <p class="text-muted">Gains par opérateur</p>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5><i class="fas fa-user"></i> Opérateur Principal</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($gains_principal)): ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Nb</th>
                                        <th>Frais</th>
                                        <th>Gains</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($gains_principal as $gain): ?>
                                        <tr>
                                            <td><?= ucfirst($gain['type_operation']) ?></td>
                                            <td><?= $gain['nb_operations'] ?></td>
                                            <td><?= number_format($gain['total_frais'], 0, ',', ' ') ?> Ar</td>
                                            <td><?= number_format($gain['total_frais'], 0, ',', ' ') ?> Ar</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p class="text-muted">Aucune donnée</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-warning">
                        <h5><i class="fas fa-exchange-alt"></i> Autres Opérateurs</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($gains_autres)): ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Opérateur</th>
                                        <th>Nb</th>
                                        <th>Frais</th>
                                        <th>Commission</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($gains_autres as $gain): ?>
                                        <tr>
                                            <td><?= $gain['operateur'] ?? 'Inconnu' ?></td>
                                            <td><?= $gain['nb_transactions'] ?></td>
                                            <td><?= number_format($gain['total_frais'] ?? 0, 0, ',', ' ') ?> Ar</td>
                                            <td><?= number_format($gain['total_commission'] ?? 0, 0, ',', ' ') ?> Ar</td>
                                            <td><?= number_format(($gain['total_frais'] ?? 0) + ($gain['total_commission'] ?? 0), 0, ',', ' ') ?> Ar</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p class="text-muted">Aucune transaction</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5><i class="fas fa-money-bill-wave"></i> Montants à envoyer</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($montants_a_envoyer)): ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Opérateur</th>
                                        <th>Préfixe</th>
                                        <th>Nb</th>
                                        <th>Montant</th>
                                        <th>Commission</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($montants_a_envoyer as $montant): ?>
                                        <tr>
                                            <td><?= $montant['operateur'] ?? 'Inconnu' ?></td>
                                            <td><span class="badge bg-info"><?= $montant['prefixe'] ?? '-' ?></span></td>
                                            <td><?= $montant['nb_transactions'] ?></td>
                                            <td><?= number_format($montant['montant_total'], 0, ',', ' ') ?> Ar</td>
                                            <td><?= number_format($montant['commission_totale'] ?? 0, 0, ',', ' ') ?> Ar</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p class="text-muted">Aucun montant</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>