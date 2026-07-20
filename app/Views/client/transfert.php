<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Transfert' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?= view('client/navbar') ?>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-0">
                        <h4 class="mb-0"><i class="fas fa-exchange-alt text-info"></i> Transfert</h4>
                        <small class="text-muted">Transférez facilement vers un ou plusieurs numéros du même opérateur.</small>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($prefixes)): ?>
                            <div class="alert alert-info">
                                Numéros autorisés : <strong><?= esc(implode(', ', $prefixes)) ?></strong>. Envoi possible uniquement vers le même opérateur.
                            </div>
                        <?php endif; ?>
                        <div id="result"></div>
                        <form id="transfertForm">
                            <div class="mb-4">
                                <label class="form-label">Numéros des destinataires</label>
                                <textarea name="destinataires" class="form-control form-control-lg" rows="4" placeholder="Ex : 0331234567, 0339876543" required></textarea>
                                <div class="form-text">Séparez plusieurs numéros par des virgules, des points-virgules ou des retours à la ligne. Le montant sera divisé automatiquement entre tous les destinataires.</div>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="inclureFrais" name="inclure_frais" checked>
                                <label class="form-check-label" for="inclureFrais">Je prends en charge les frais de transfert. Sinon, les frais seront déduits du montant envoyé.</label>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Montant total à transférer (Ar)</label>
                                <input type="number" name="montant" id="transfertMontant" class="form-control form-control-lg" placeholder="Ex : 10000" min="100" required>
                                <div class="form-text">Les frais sont calculés automatiquement selon le barème. Le montant indiqué sera partagé entre tous les destinataires.</div>
                            </div>
                            <div class="mb-4">
                                <div id="preview" class="alert alert-secondary d-none"></div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-check me-2"></i> Effectuer le transfert
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const preview = document.getElementById('preview');
        const transfertForm = document.getElementById('transfertForm');
        const destinatairesField = transfertForm.querySelector('[name="destinataires"]');
        const montantField = document.getElementById('transfertMontant');

        const updatePreview = () => {
            const raw = destinatairesField.value.trim();
            const montant = parseFloat(montantField.value);
            if (!raw || !montant || montant <= 0) {
                preview.classList.add('d-none');
                preview.innerHTML = '';
                return;
            }

            const parts = raw
                .replace(/\r/g, '\n')
                .replace(/;/g, ',')
                .split(/[\s,]+/)
                .filter(Boolean)
                .map(item => item.replace(/[^0-9]/g, ''))
                .filter(Boolean);

            const unique = [...new Set(parts)];
            if (unique.length === 0) {
                preview.classList.add('d-none');
                preview.innerHTML = '';
                return;
            }

            const count = unique.length;
            const share = Math.floor((montant * 100) / count) / 100;
            const remainder = (montant - share * count).toFixed(2);
            const shareText = count > 1
                ? `${share.toLocaleString('fr-FR', {minimumFractionDigits: 2, maximumFractionDigits: 2})} Ar par destinataire, avec un reste de ${remainder} Ar ajouté au dernier destinataire.`
                : `${montant.toLocaleString('fr-FR', {minimumFractionDigits: 2, maximumFractionDigits: 2})} Ar pour le destinataire.`;

            preview.classList.remove('d-none');
            preview.innerHTML = `
                <strong>${count}</strong> destinataire(s) détecté(s).<br>
                Montant estimé par destinataire : ${shareText}
                <br><small class="text-muted">Le montant total sera divisé entre tous les destinataires valides.</small>
            `;
        };

        destinatairesField.addEventListener('input', updatePreview);
        montantField.addEventListener('input', updatePreview);

        transfertForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('/index.php/client/doTransfert', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const resultDiv = document.getElementById('result');
                if (data.success) {
                    resultDiv.innerHTML = `
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> ${data.message}<br>
                            <strong>Frais totaux : ${data.frais}</strong><br>
                            <strong>Total débité : ${data.total_debite}</strong><br>
                            <strong>Nouveau solde : ${data.nouveau_solde}</strong>
                        </div>
                    `;
                    transfertForm.reset();
                    preview.classList.add('d-none');
                } else {
                    resultDiv.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i> ${data.message}
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                document.getElementById('result').innerHTML = `
                    <div class="alert alert-danger">
                        Une erreur est survenue. Veuillez réessayer.
                    </div>
                `;
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
