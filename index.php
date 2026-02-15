<?php include 'includes/header.php'; ?>

<div class="hero">
    <h1>Save Lives Today</h1>
    <p>Your donation can save up to 3 lives. Be a hero, donate blood.</p>
    <div class="hero-buttons">
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="user/donate.php" class="btn btn-primary">Donate Blood</a>
            <a href="user/request.php" class="btn btn-secondary">Request Blood</a>
        <?php
else: ?>
            <a href="login.php?redirect=donate" class="btn btn-primary">Donate Blood</a>
            <a href="login.php?redirect=request" class="btn btn-secondary">Request Blood</a>
        <?php
endif; ?>
    </div>
</div>

<div class="container">
    <div class="card-grid">
        <div class="card">
            <i class="fas fa-hand-holding-heart fa-3x" style="color: var(--primary-color); margin-bottom: 1rem;"></i>
            <h3>Why Donate?</h3>
            <p>Donating blood is a simple, safe way to help save lives. One donation can save up to three lives.</p>
        </div>
        <div class="card">
            <i class="fas fa-hospital-user fa-3x" style="color: var(--primary-color); margin-bottom: 1rem;"></i>
            <h3>Who Can Donate?</h3>
            <p>Most people can give blood if they are in good health. There are some basic requirements you need to fulfill.</p>
        </div>
        <div class="card">
            <i class="fas fa-search-location fa-3x" style="color: var(--primary-color); margin-bottom: 1rem;"></i>
            <h3>Find Blood</h3>
            <p>In emergencies, you can request blood and find compatible donors near you quickly.</p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
