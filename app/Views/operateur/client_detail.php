<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Détail Client' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include_once 'navbar.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-user"></i> Informations client</h4>
                    </div>
                    <div class="card-body">
                        <ul class="list-group">
                            <li class="list-group-item">
                                <strong>ID :</strong> <?= $client['id'] ?>
                            </li>
                            <li class="list-group-item">
                                <strong>Téléphone :</strong> <?= $client['numero_telephone'] ?>
                            </li>
                            <li class="list-group-item">
                                <strong>Solde :</strong> <?= number_format($client['solde'], 2, ',', ' ') ?> Ar
                            </li>
                            <li class="list-group-item">
                                <strong>Date création :</strong> <?= $client['date_creation'] ?>
                            </li>
                        </ul>
                        <a href="/operateur/clients" class="btn btn-secondary mt-3">
                            <i class="fas fa-arrow-left"></i> Retour
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-history"></i> Historique des opérations</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($historique)): ?>
                            <div class="list-group">
                                <?php foreach($historique as $op): ?>
                                    <div class="list-group-item">
                                        <strong><?= number_format($op['montant'], 2, ',', ' ') ?> Ar</strong>
                                        <small class="text-muted d-block"><?= $op['date_transaction'] ?></small>
                                    </div>
                                <?php endforeach; ?>
                            </div>
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