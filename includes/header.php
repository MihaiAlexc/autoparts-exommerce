<div class="text-light py-1 d-none d-md-block" style="background-color: #111;">
    <div class="container d-flex justify-content-between align-items-center" style="font-size: 0.85rem;">
        <div><i class="fas fa-phone-alt me-1 text-primary"></i> 0723995579 <span class="mx-2 text-secondary">|</span> <i class="fas fa-envelope me-1 text-primary"></i> alexgabimihai4@gmail.com</div>
        <div><i class="fas fa-truck me-1 text-primary"></i> Livrare rapidă în 24/48h <span class="mx-2 text-secondary">|</span> <i class="fas fa-undo-alt me-1 text-primary"></i> Retur în 14 zile</div>
    </div>
</div>

<nav class="navbar navbar-expand-lg navbar-dark py-3 shadow-sm border-bottom border-dark" style="background-color: #222;">
  <div class="container">
    <a class="navbar-brand fw-bold fs-3 text-white m-0" href="index.php"><i class="fas fa-cogs text-primary"></i> AutoParts<span class="text-primary">Pro</span></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainMenu"><span class="navbar-toggler-icon"></span></button>

    <div class="collapse navbar-collapse" id="mainMenu">
        <form class="d-flex mx-auto my-3 my-lg-0" style="width: 100%; max-width: 550px;" action="index.php" method="GET">
            <div class="input-group">
                <input type="text" class="form-control border-0" placeholder="Caută piesa sau codul OE..." name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" style="box-shadow: none;">
                <button class="btn btn-primary px-4 fw-bold text-light" type="submit"><i class="fas fa-search"></i> Caută</button>
            </div>
        </form>

        <ul class="navbar-nav ms-auto align-items-center gap-3">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle btn btn-outline-light d-flex align-items-center gap-2 px-3 border-secondary" href="#" data-bs-toggle="dropdown">
                    <i class="fas fa-user-circle fs-5"></i> 
                    <span class="fw-medium"><?php echo isset($_SESSION['client_nume']) ? htmlspecialchars($_SESSION['client_nume']) : 'Contul meu'; ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark shadow mt-2">
                    <?php if(isset($_SESSION['client_id'])): ?>
                        <li><a class="dropdown-item py-2" href="istoric_comenzi.php"><i class="fas fa-box text-primary me-2"></i> Comenzile mele</a></li>
                        <li><a class="dropdown-item py-2" href="favorite.php"><i class="fas fa-heart text-danger me-2"></i> Favorite</a></li>
                        <li><hr class="dropdown-divider border-secondary"></li>
                        <li><a class="dropdown-item py-2 text-danger" href="logout_client.php"><i class="fas fa-sign-out-alt me-2"></i> Deconectare</a></li>
                    <?php else: ?>
                        <li><a class="dropdown-item py-2" href="login_client.php"><i class="fas fa-sign-in-alt text-primary me-2"></i> Autentificare</a></li>
                        <li><a class="dropdown-item py-2" href="register.php"><i class="fas fa-user-plus text-primary me-2"></i> Înregistrare cont nou</a></li>
                    <?php endif; ?>
                </ul>
            </li>

            <li class="nav-item">
                <a href="cos.php" class="btn btn-primary d-flex align-items-center gap-2 px-3 position-relative fw-bold text-light">
                    <i class="fas fa-shopping-cart fs-5"></i>
                    <span>Coș</span>
                    <span id="cart-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-dark">
                        <?= $total_in_cos ?>
                    </span>
                </a>
            </li>
        </ul>
    </div>
  </div>
</nav>

<div class="d-none d-lg-block shadow-sm" style="background-color: #1a1a1a;">
    <div class="container">
        <ul class="nav nav-pills py-1 justify-content-center gap-4">
            <li class="nav-item"><a class="nav-link text-light px-3 py-2 text-uppercase" style="font-size: 0.85rem;" href="index.php"><i class="fas fa-home me-1 text-primary"></i> Acasă</a></li>
            <li class="nav-item"><a class="nav-link text-light px-3 py-2 text-uppercase" style="font-size: 0.85rem;" href="index.php?categorie=uleiuri"><i class="fas fa-oil-can me-1 text-primary"></i> Uleiuri Motor</a></li>
            <li class="nav-item"><a class="nav-link text-light px-3 py-2 text-uppercase" style="font-size: 0.85rem;" href="anvelope.php"><i class="fas fa-bullseye me-1 text-primary"></i> Anvelope</a></li>
            <li class="nav-item"><a class="nav-link text-light px-3 py-2 text-uppercase" style="font-size: 0.85rem;" href="index.php?categorie=frane"><i class="fas fa-compact-disc me-1 text-primary"></i> Sistem Frânare</a></li>
            <li class="nav-item"><a class="nav-link text-light px-3 py-2 text-uppercase" style="font-size: 0.85rem;" href="index.php?categorie=filtre"><i class="fas fa-filter me-1 text-primary"></i> Filtre auto</a></li>
            <li class="nav-item"><a class="nav-link text-primary fw-bold px-3 py-2 text-uppercase" style="font-size: 0.85rem;" href="#"><i class="fas fa-tags me-1"></i> Oferte Speciale</a></li>
        </ul>
    </div>
</div>

