<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="/index.php/client/dashboard">
            <i class="fas fa-mobile-alt"></i> Mobile Money
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#clientNavbar" aria-controls="clientNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="clientNavbar">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= strpos(current_url(), 'dashboard') !== false ? 'active' : '' ?>" href="/index.php/client/dashboard">Tableau de bord</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos(current_url(), 'depot') !== false ? 'active' : '' ?>" href="/index.php/client/depot">Dépôt</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos(current_url(), 'retrait') !== false ? 'active' : '' ?>" href="/index.php/client/retrait">Retrait</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos(current_url(), 'transfert') !== false ? 'active' : '' ?>" href="/index.php/client/transfert">Transfert</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos(current_url(), 'historique') !== false ? 'active' : '' ?>" href="/index.php/client/historique">Historique</a>
                </li>
                <li class="nav-item ms-2">
                    <a class="nav-link btn btn-outline-light btn-sm" href="/index.php/operateur/dashboard">
                        <i class="fas fa-cogs"></i> Espace opérateur
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link btn btn-outline-light btn-sm ms-2" href="/index.php/client/logout">Déconnexion</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
