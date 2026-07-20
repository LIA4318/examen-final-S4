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
        <h4>Solde : <span id="solde-value"><?= number_format($client['solde'], 0, ',', ' ') ?> Ar</span></h4>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card p-3">
                <h5>Dépôt</h5>
                <form id="form-depot">
                    <input type="number" name="montant" id="depot-montant" class="form-control mb-2" placeholder="Montant" required>
                    <button class="btn btn-success w-100" type="submit">Déposer</button>
                </form>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3">
                <h5>Retrait</h5>
                <form id="form-retrait">
                    <input type="number" name="montant" id="retrait-montant" class="form-control mb-2" placeholder="Montant" required>
                    <button class="btn btn-warning w-100" type="submit">Retirer</button>
                </form>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3">
                <h5>Transfert</h5>
                <form id="form-transfert">
                    <input type="text" name="destinataire" id="transfert-destinataire" class="form-control mb-2" placeholder="Numéro destinataire" required>
                    <input type="number" name="montant" id="transfert-montant" class="form-control mb-2" placeholder="Montant" required>
                    <button class="btn btn-info w-100" type="submit">Transférer</button>
                </form>
            </div>
        </div>
    </div>

    <a href="/client/historique" class="btn btn-secondary mt-3">Voir l'historique</a>
</div>
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

    // Dépôt
    document.getElementById('form-depot').addEventListener('submit', async (e) => {
        e.preventDefault();
        const montant = document.getElementById('depot-montant').value;
        const res = await fetch('/client/doDepot', { method: 'POST', body: new URLSearchParams({ montant }) });
        const json = await res.json();
        if (json.success) {
            showAlert('success', json.message);
            updateSolde(json.nouveau_solde);
        } else {
            showAlert('danger', json.message);
        }
    });

    // Retrait
    document.getElementById('form-retrait').addEventListener('submit', async (e) => {
        e.preventDefault();
        const montant = document.getElementById('retrait-montant').value;
        const res = await fetch('/client/doRetrait', { method: 'POST', body: new URLSearchParams({ montant }) });
        const json = await res.json();
        if (json.success) {
            showAlert('success', json.message + ' Frais: ' + (json.frais || '0'));
            updateSolde(json.nouveau_solde);
        } else {
            showAlert('danger', json.message);
        }
    });

    // Transfert
    document.getElementById('form-transfert').addEventListener('submit', async (e) => {
        e.preventDefault();
        const montant = document.getElementById('transfert-montant').value;
        const destinataire = document.getElementById('transfert-destinataire').value;
        const res = await fetch('/client/doTransfert', { method: 'POST', body: new URLSearchParams({ montant, destinataire }) });
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