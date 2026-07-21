<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Modifier Opérateur' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include_once 'navbar.php'; ?>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-edit"></i> Modifier Opérateur</h4>
                    </div>
                    <div class="card-body">
                        <?php if (session()->getFlashdata('errors')): ?>
                            <div class="alert alert-danger">
                                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                    <div><?= $error ?></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <form action="/operateur/update/<?= $operateur['id'] ?>" method="POST">
                            <div class="mb-3">
                                <label class="form-label">Nom</label>
                                <input type="text" name="nom" class="form-control" value="<?= $operateur['nom'] ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Code</label>
                                <input type="text" name="code" class="form-control" value="<?= $operateur['code'] ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Préfixe</label>
                                <input type="text" name="prefixe" class="form-control" value="<?= $operateur['prefixe'] ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Commission (%)</label>
                                <input type="number" name="commission_pourcentage" class="form-control" step="0.01" value="<?= $operateur['commission_pourcentage'] ?>">
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" name="actif" class="form-check-input" <?= $operateur['actif'] ? 'checked' : '' ?>>
                                <label class="form-check-label">Actif</label>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Mettre à jour
                            </button>
                            <a href="/operateur/operateurs" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Retour
                            </a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>