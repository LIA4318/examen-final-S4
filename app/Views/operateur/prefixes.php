<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Gestion des Préfixes' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar (même que dashboard) -->
    <?php include_once 'navbar.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-tags"></i> Gestion des préfixes téléphoniques</h4>
                    </div>
                    <div class="card-body">
                        <?php if (session()->getFlashdata('success')): ?>
                            <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
                        <?php endif; ?>
                        <?php if (session()->getFlashdata('error')): ?>
                            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
                        <?php endif; ?>

                        <form action="/operateur/update-prefixes" method="POST">
                            <div class="mb-3">
                                <label class="form-label">Préfixes valides</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="text" name="prefixes" class="form-control" 
                                           value="<?= implode(', ', $prefixes ?? ['033', '037']) ?>"
                                           placeholder="Ex: 033, 037, 038">
                                </div>
                                <small class="text-muted">
                                    Séparez les préfixes par des virgules (ex: 033, 037)
                                </small>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Mettre à jour
                            </button>
                        </form>

                        <hr>
                        <h5>Préfixes actuels :</h5>
                        <ul class="list-group">
                            <?php foreach ($prefixes ?? ['033', '037'] as $prefix): ?>
                                <li class="list-group-item">
                                    <i class="fas fa-check-circle text-success"></i>
                                    <?= $prefix ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>