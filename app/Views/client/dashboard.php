<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Dashboard' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include_once 'navbar.php'; ?>

    <div class="container mt-4">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3 display-6 text-success"><i class="fas fa-wallet"></i></div>
                            <div>
                                <h6 class="text-muted mb-1">Solde</h6>
                                <h3 class="mb-0" id="solde-value"><?= number_format($client['solde'], 0, ',', ' ') ?> Ar</h3>
                            </div>
                        </div>
                        <p class="text-muted">Votre solde disponible pour les opérations.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3 display-6 text-primary"><i class="fas fa-history"></i></div>
                            <div>
                                <h6 class="text-muted mb-1">Dernières opérations</h6>
                                <h3 class="mb-0"><?= count($historique) ?></h3>
                            </div>
                        </div>
                        <p class="text-muted">Affiché jusqu'à 10 opérations récentes.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3 display-6 text-info"><i class="fas fa-user"></i></div>
                            <div>
                                <h6 class="text-muted mb-1">Compte</h6>
                                <h5 class="mb-0"><?= esc($client['numero_telephone']) ?></h5>
                            </div>
                        </div>
                        <p class="text-muted">Accès sécurisé via votre numéro de téléphone.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-arrow-down text-success"></i> Dépôt</h5>
                        <p class="text-muted">Créez un dépôt automatique et alimentez votre solde.</p>
                        <a href="/index.php/client/depot" class="btn btn-success mt-3 w-100">Déposer</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-arrow-up text-danger"></i> Retrait</h5>
                        <p class="text-muted">Retirez de l'argent avec frais calculés automatiquement.</p>
                        <a href="/index.php/client/retrait" class="btn btn-danger mt-3 w-100">Retirer</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-exchange-alt text-info"></i> Transfert</h5>
                        <p class="text-muted">Envoyez de l'argent vers un autre client.</p>
                        <a href="/index.php/client/transfert" class="btn btn-info mt-3 w-100">Transférer</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-5">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="card-title mb-0">Récentes opérations</h5>
                        <small class="text-muted">Vos dernières transactions</small>
                    </div>
                    <a href="/index.php/client/historique" class="btn btn-outline-primary btn-sm">Voir tout</a>
                </div>

                <?php if (!empty($historique)): ?>
                    <div class="list-group">
                        <?php foreach (array_slice($historique, 0, 5) as $op): ?>
                            <?php
                                $type = strtolower($op['type_libelle'] ?? '');
                                $icon = 'fa-exchange-alt';
                                $color = 'text-primary';
                                $badge = 'bg-primary';
                                if ($type == 'depot') {
                                    $icon = 'fa-arrow-down';
                                    $color = 'text-success';
                                    $badge = 'bg-success';
                                } elseif ($type == 'retrait') {
                                    $icon = 'fa-arrow-up';
                                    $color = 'text-danger';
                                    $badge = 'bg-danger';
                                } elseif ($type == 'transfert') {
                                    $icon = 'fa-exchange-alt';
                                    $color = 'text-info';
                                    $badge = 'bg-info text-dark';
                                }
                            ?>
                            <div class="list-group-item d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="fw-bold"><i class="fas <?= $icon ?> <?= $color ?> me-2"></i><?= ucfirst($op['type_libelle'] ?? 'Opération') ?></div>
                                    <div class="small text-muted mt-1">
                                        <?= $op['date_transaction'] ?? '' ?>
                                        <?php if (!empty($op['frais']) && $op['frais'] > 0): ?>
                                            &middot; Frais <?= number_format($op['frais'], 0, ',', ' ') ?> Ar
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <span class="badge <?= $badge ?> rounded-pill align-self-center">
                                    <?= number_format($op['montant'] ?? 0, 0, ',', ' ') ?> Ar
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">Aucune opération pour le moment.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const showAlert = (type, message) => {
            const container = document.querySelector('.container');
            const existing = document.getElementById('ajax-alert');
            if (existing) existing.remove();
            const div = document.createElement('div');
            div.id = 'ajax-alert';
            div.className = 'alert alert-' + type + ' mt-3';
            div.textContent = message;
            container.insertBefore(div, container.firstChild);
        };

        const updateSolde = (text) => {
            const el = document.getElementById('solde-value');
            if (el) el.textContent = text;
        };

        document.getElementById('form-depot').addEventListener('submit', async (e) => {
            e.preventDefault();
            const montant = document.getElementById('depot-montant').value;
            const res = await fetch('/index.php/client/doDepot', { method: 'POST', body: new URLSearchParams({ montant }) });
            const json = await res.json();
            if (json.success) {
                showAlert('success', json.message);
                updateSolde(json.nouveau_solde);
            } else {
                showAlert('danger', json.message);
            }
        });

        document.getElementById('form-retrait').addEventListener('submit', async (e) => {
            e.preventDefault();
            const montant = document.getElementById('retrait-montant').value;
            const res = await fetch('/index.php/client/doRetrait', { method: 'POST', body: new URLSearchParams({ montant }) });
            const json = await res.json();
            if (json.success) {
                showAlert('success', json.message + ' Frais: ' + (json.frais || '0'));
                updateSolde(json.nouveau_solde);
            } else {
                showAlert('danger', json.message);
            }
        });

        document.getElementById('form-transfert').addEventListener('submit', async (e) => {
            e.preventDefault();
            const montant = document.getElementById('transfert-montant').value;
            const destinataire = document.getElementById('transfert-destinataire').value;
            const res = await fetch('/index.php/client/doTransfert', { method: 'POST', body: new URLSearchParams({ montant, destinataire }) });
            const json = await res.json();
            if (json.success) {
                showAlert('success', json.message + ' Frais: ' + (json.frais || '0'));
                updateSolde(json.nouveau_solde);
            } else {
                showAlert('danger', json.message);
            }
        });
    </script>
</body>
</html>
