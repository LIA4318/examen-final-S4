<!DOCTYPE html>
<html>
<head>
    <title>Connexion - Mobile Money</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <h3 class="text-center mb-4">Mobile Money</h3>
            <?php if (!empty($prefixes)): ?>
                <div class="alert alert-info">
                    Préfixes autorisés : <strong><?= esc(implode(', ', $prefixes)) ?></strong>
                </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
            <?php endif; ?>
            <form action="/index.php/client/doLogin" method="post">
                <div class="mb-3">
                    <label>Numéro de téléphone</label>
                    <input type="text" name="telephone" class="form-control" 
                           placeholder="Ex: 0331234567" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Se connecter</button>
            </form>
            <div class="text-center mt-3">
                <a href="/index.php/operateur/dashboard" class="btn btn-outline-secondary btn-sm">
                    Aller à l'espace opérateur
                </a>
            </div>
        </div>
    </div>
</div>
</body>
</html>