<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Clients' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?= view('operateur/navbar') ?>

    <div class="container mt-4">
        <h1>Clients</h1>
        <p class="text-muted">Liste des clients et leurs soldes</p>

        <div class="card">
            <div class="card-body">
                <?php if (!empty($clients)): ?>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Téléphone</th>
                                <th>Solde</th>
                                <th>Date création</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($clients as $client): ?>
                                <tr>
                                    <td><?= $client['id'] ?></td>
                                    <td><?= $client['numero_telephone'] ?></td>
                                    <td><?= number_format($client['solde'], 2, ',', ' ') ?> Ar</td>
                                    <td><?= $client['date_creation'] ?></td>
                                    <td>
                                        <a href="/index.php/operateur/client/<?= $client['id'] ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-muted">Aucun client</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>