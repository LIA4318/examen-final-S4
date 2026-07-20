<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Transfert - Mobile Money' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include_once 'navbar.php'; ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <h4 class="text-center mb-4">
                            <i class="fas fa-exchange-alt text-primary"></i> Transfert
                        </h4>
                        <p class="text-muted text-center">Transférez facilement vers un autre numéro valide.</p>

                        <div id="result"></div>

                        <form id="transfertForm">
                            <div class="mb-3">
                                <label class="form-label">Numéro du destinataire</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="tel" name="destinataire" class="form-control" 
                                           placeholder="Ex: 0331234567" required>
                                </div>
                                <small class="text-muted">Entrez le numéro de téléphone du bénéficiaire</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Montant à transférer (Ar)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-money-bill-wave"></i></span>
                                    <input type="number" name="montant" class="form-control" 
                                           placeholder="Ex: 10000" min="100" step="100" required>
                                </div>
                                <small class="text-muted">Des frais seront appliqués selon le montant</small>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-paper-plane"></i> Effectuer le transfert
                            </button>
                        </form>

                        <div class="text-center mt-3">
                            <a href="/client/dashboard" class="text-decoration-none">
                                <i class="fas fa-arrow-left"></i> Retour au dashboard
                            </a>
                        </div>
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
            
            // Afficher un message de chargement
            document.getElementById('result').innerHTML = `
                <div class="alert alert-info">
                    <i class="fas fa-spinner fa-spin"></i> Traitement en cours...
                </div>
            `;
            
            fetch('/client/doTransfert', {
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
                            <strong>Frais :</strong> ${data.frais}<br>
                            <strong>Nouveau solde :</strong> ${data.nouveau_solde}
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
                        <i class="fas fa-exclamation-circle"></i> Une erreur est survenue. Veuillez réessayer.
                    </div>
                `;
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>