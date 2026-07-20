<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Transfert' ?></title>
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

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <h4 class="text-center mb-4">
                            <i class="fas fa-exchange-alt text-primary"></i> Transfert
                        </h4>

                        <div id="result"></div>

                        <form id="transfertForm">
                            <div class="mb-3">
                                <label>Numéro du destinataire</label>
                                <input type="tel" name="destinataire" class="form-control" 
                                       placeholder="Ex: 0331234567" required>
                            </div>
                            <div class="mb-3">
                                <label>Montant à transférer (Ar)</label>
                                <input type="number" name="montant" class="form-control" 
                                       placeholder="Ex: 10000" min="100" required>
                                <small class="text-muted">Des frais seront appliqués selon le montant</small>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-check"></i> Effectuer le transfert
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('transfertForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
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
                            <strong>Frais : ${data.frais}</strong><br>
                            <strong>Nouveau solde : ${data.nouveau_solde}</strong>
                        </div>
                    `;
                    document.getElementById('transfertForm').reset();
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
                        Une erreur est survenue
                    </div>
                `;
            });
        });
    </script>
</body>
</html>