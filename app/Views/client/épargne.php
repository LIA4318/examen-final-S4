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
                        <h4 class="mb-0"><i class="fas fa-exchange-alt text-info"></i> Epargne</h4>
                        <small class="text-muted">Epargne de l'agent</small>
                    </div>
                    <div class="card-body">
                        <div id="result"></div>
                        <form id="transfertForm">
                            <div class="mb-4">
                                <label class="form-label">Numéros des destinataires</label>
                                <textarea name="destinataires" class="form-control form-control-lg" rows="4" placeholder="Ex : 0331234567, 0339876543" required></textarea>
                            </div>
        
                            <div class="mb-4">
                                <label class="form-label">Montant total à épargner</label>
                                <input type="number" name="montant" id="transfertMontant" class="form-control form-control-lg" placeholder="Ex : 10000" min="100" required>
                                <div class="form-text">Les frais sont calculés automatiquement selon le barème. Le montant indiqué sera partagé entre tous les destinataires.</div>
                            </div>
                            <div class="mb-4">
                                <div id="preview" class="alert alert-secondary d-none"></div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-check me-2"></i> effectuer l'épargne
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>