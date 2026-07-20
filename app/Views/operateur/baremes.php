<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Barèmes de Frais' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?= view('operateur/navbar') ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1>Barèmes de Frais</h1>
            <a href="/index.php/operateur/bareme/create" class="btn btn-success">
                <i class="fas fa-plus"></i> Nouveau barème
            </a>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <?php if (!empty($baremes)): ?>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Type</th>
                                <th>Montant min</th>
                                <th>Montant max</th>
                                <th>Frais</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($baremes as $bareme): ?>
                                <tr>
                                    <td><?= $bareme['id'] ?></td>
                                    <td>
                                        <?php 
                                        $type = array_filter($types, function($t) use ($bareme) {
                                            return $t['id'] == $bareme['type_operation_id'];
                                        });
                                        $type = reset($type);
                                        echo ucfirst($type['libelle'] ?? 'Inconnu');
                                        ?>
                                    </td>
                                    <td><?= number_format($bareme['montant_min'], 0, ',', ' ') ?> Ar</td>
                                    <td><?= number_format($bareme['montant_max'], 0, ',', ' ') ?> Ar</td>
                                    <td><?= number_format($bareme['frais'], 0, ',', ' ') ?> Ar</td>
                                    <td>
                                        <a href="/index.php/operateur/bareme/edit/<?= $bareme['id'] ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="/index.php/operateur/bareme/delete/<?= $bareme['id'] ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Supprimer ce barème ?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-muted">Aucun barème</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>