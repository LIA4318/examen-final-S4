<!DOCTYPE html>
<html>
<head>
    <title>Tableau de bord</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
    <div class="d-flex justify-content-between">
        <h3>Bonjour, <?= esc($client['numero_telephone']) ?></h3>
        <a href="/client/logout" class="btn btn-outline-danger">Déconnexion</a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <div class="card my-3 p-3">
        <h4>Solde : <?= number_format($client['solde'], 0, ',', ' ') ?> Ar</h4>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card p-3">
                <h5>Dépôt</h5>
                <form action="/client/depot" method="post">
                    <input type="number" name="montant" class="form-control mb-2" placeholder="Montant" required>
                    <button class="btn btn-success w-100">Déposer</button>
                </form>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3">
                <h5>Retrait</h5>
                <form action="/client/retrait" method="post">
                    <input type="number" name="montant" class="form-control mb-2" placeholder="Montant" required>
                    <button class="btn btn-warning w-100">Retirer</button>
                </form>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3">
                <h5>Transfert</h5>
                <form action="/client/transfert" method="post">
                    <input type="text" name="numero_destinataire" class="form-control mb-2" placeholder="Numéro destinataire" required>
                    <input type="number" name="montant" class="form-control mb-2" placeholder="Montant" required>
                    <button class="btn btn-info w-100">Transférer</button>
                </form>
            </div>
        </div>
    </div>

    <a href="/client/historique" class="btn btn-secondary mt-3">Voir l'historique</a>
</div>
</body>
</html>