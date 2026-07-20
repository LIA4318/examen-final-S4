<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Dépôt' ?></title>
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
                        <h4 class="mb-0"><i class="fas fa-arrow-down text-success"></i> Dépôt</h4>
                        <small class="text-muted">Ajoutez de l'argent à votre solde en un clic.</small>
                    </div>
                    <div class="card-body">
                        <div id="result"></div>
                        <form id="depotForm">
                            <div class="mb-4">
                                <label class="form-label">Montant à déposer (Ar)</label>
                                <input type="number" name="montant" class="form-control form-control-lg" placeholder="Ex : 10000" min="100" required>
                                <div class="form-text">Aucun frais n'est appliqué pour les dépôts.</div>
                            </div>
                            <button type="submit" class="btn btn-success btn-lg w-100">
                                <i class="fas fa-check me-2"></i> Effectuer le dépôt
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('depotForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('/index.php/client/doDepot', {
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
                            <strong>Nouveau solde : ${data.nouveau_solde}</strong>
                        </div>
                    `;
                    document.getElementById('depotForm').reset();
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
