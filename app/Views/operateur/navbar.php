<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="/operateur">
            <i class="fas fa-cogs"></i> Opérateur Mobile Money
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link <?= current_url() == base_url('/operateur/dashboard') ? 'active' : '' ?>" 
                       href="/operateur/dashboard">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos(current_url(), 'prefixes') ? 'active' : '' ?>" 
                       href="/operateur/prefixes">
                        <i class="fas fa-tags"></i> Préfixes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos(current_url(), 'types-operations') ? 'active' : '' ?>" 
                       href="/operateur/types-operations">
                        <i class="fas fa-list"></i> Types
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos(current_url(), 'baremes') ? 'active' : '' ?>" 
                       href="/operateur/baremes">
                        <i class="fas fa-calculator"></i> Barèmes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos(current_url(), 'clients') ? 'active' : '' ?>" 
                       href="/operateur/clients">
                        <i class="fas fa-users"></i> Clients
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos(current_url(), 'statistiques') ? 'active' : '' ?>" 
                       href="/operateur/statistiques">
                        <i class="fas fa-chart-bar"></i> Stats
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>