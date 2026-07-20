<!DOCTYPE html>
<html>
<head>
    <title>Historique</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
    <h3>Historique des transactions</h3>
    <a href="/client/dashboard" class="btn btn-secondary mb-3">Retour</a>
    <table class="table table-bordered bg-white">
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Montant</th>
                <th>Frais</th>
                <th>Détail</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($historique)): ?>
                <?php foreach ($historique as $t): ?>
                    <?php
                        $badgeClass = match ($t['type_libelle']) {
                            'depot' => 'success',
                            'retrait' => 'warning',
                            'transfert' => 'info',
                            default => 'secondary',
                        };
                    ?>
                    <tr>
                        <td><?= esc($t['date_transaction']) ?></td>
                        <td><span class="badge bg-<?= $badgeClass ?>"><?= esc(ucfirst($t['type_libelle'])) ?></span></td>
                        <td><?= number_format($t['montant'], 0, ',', ' ') ?> Ar</td>
                        <td><?= number_format($t['frais'] ?? 0, 0, ',', ' ') ?> Ar</td>
                        <td>
                            <?php if ($t['type_libelle'] === 'transfert' && !empty($t['destinataire_numero'])): ?>
                                Vers <?= esc($t['destinataire_numero']) ?>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" class="text-center">Aucune transaction trouvée.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>