<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Créer Barème' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include_once 'navbar.php'; ?>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-plus"></i> Nouveau barème de frais</h4>
                    </div>
                    <div class="card-body">
                        <?php if (session()->getFlashdata('errors')): ?>
                            <div class="alert alert-danger">
                                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                    <div><?= $error ?></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <form action="/operateur/bareme/store" method="POST">
                            <div class="mb-3">
                                <label class="form-label">Type d'opération</label>
                                <select name="type_operation_id" class="form-control" required>
                                    <option value="">Sélectionner...</option>
                                    <?php foreach ($types as $type): ?>
                                        <option value="<?= $type['id'] ?>"><?= ucfirst($type['libelle']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Montant minimum (Ar)</label>
                                        <input type="number" name="montant_min" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Montant maximum (Ar)</label>
                                        <input type="number" name="montant_max" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Frais (Ar)</label>
                                <input type="number" name="frais" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Créer
                            </button>
                            <a href="/operateur/baremes" class="btn btn-secondary">
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