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
            </tr>
        </thead>
        <tbody>
            <?php foreach ($transactions as $t): ?>
                <tr>
                    <td><?= $t['date_transaction'] ?></td>
                    <td><?= $t['type_operation_id'] ?></td>
                    <td><?= number_format($t['montant'], 0, ',', ' ') ?> Ar</td>
                    <td><?= number_format($t['frais'], 0, ',', ' ') ?> Ar</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>