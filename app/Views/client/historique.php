<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Historique' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="/client/dashboard">
                <i class="fas fa-mobile-alt"></i> Mobile Money
            </a>
            <a href="/client/dashboard" class="btn btn-outline-light">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </nav>

    <div class="container mt-4">
        <h1>Historique des opérations</h1>
        <p class="text-muted">Toutes vos transactions</p>

        <div class="card">
            <div class="card-body">
                <?php if (!empty($historique)): ?>
                    <div class="list-group">
                        <?php foreach($historique as $op): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <?php 
                                    $type = strtolower($op['type_libelle'] ?? '');
                                    $icon = 'fa-exchange-alt';
                                    $color = 'text-primary';
                                    if ($type == 'depot') {
                                        $icon = 'fa-arrow-down';
                                        $color = 'text-success';
                                    } elseif ($type == 'retrait') {
                                        $icon = 'fa-arrow-up';
                                        $color = 'text-danger';
                                    } elseif ($type == 'transfert') {
                                        $icon = 'fa-exchange-alt';
                                        $color = 'text-warning';
                                    }
                                    ?>
                                    <i class="fas <?= $icon ?> <?= $color ?>"></i>
                                    <strong><?= ucfirst($op['type_libelle'] ?? 'Opération') ?></strong>
                                    <small class="text-muted d-block">
                                        <i class="far fa-clock"></i> <?= $op['date_transaction'] ?? '' ?>
                                        <?php if ($op['frais'] > 0): ?>
                                            <br><i class="fas fa-coins"></i> Frais: <?= number_format($op['frais'], 2, ',', ' ') ?> Ar
                                        <?php endif; ?>
                                    </small>
                                </div>
                                <span class="badge <?= $type == 'depot' ? 'bg-success' : ($type == 'retrait' ? 'bg-danger' : 'bg-primary') ?>">
                                    <?= number_format($op['montant'] ?? 0, 2, ',', ' ') ?> Ar
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center">Aucune opération effectuée</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>