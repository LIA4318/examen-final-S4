<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="/client/dashboard">
            <i class="fas fa-mobile-alt"></i> Mobile Money
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/client/dashboard">
                        <i class="fas fa-home"></i> Accueil
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/client/depot">
                        <i class="fas fa-arrow-down text-success"></i> Dépôt
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/client/retrait">
                        <i class="fas fa-arrow-up text-danger"></i> Retrait
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/client/transfert">
                        <i class="fas fa-exchange-alt text-warning"></i> Transfert
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/client/historique">
                        <i class="fas fa-history"></i> Historique
                    </a>
                </li>
            </ul>
            <!-- Bouton vers le côté opérateur -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="btn btn-outline-light btn-sm" href="/operateur/dashboard">
                        <i class="fas fa-cogs"></i> Côté Opérateur
                    </a>
                </li>
                <li class="nav-item ms-2">
                    <span class="navbar-text text-white">
                        <i class="fas fa-user"></i> 
                        <?= session()->get('client_telephone') ?? 'Client' ?>
                    </span>
                </li>
                <li class="nav-item ms-2">
                    <a class="btn btn-outline-danger btn-sm" href="/client/logout">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>