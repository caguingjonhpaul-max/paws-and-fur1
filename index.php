<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PAWS AND FUR CLINIC</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="home-page">
    <header class="site-header">
        <a class="brand" href="index.php"><span class="brand-mark" aria-hidden="true">🐾</span><span>PAWS &amp; FUR <small>VETERINARY CLINIC</small></span></a>
        <nav class="site-nav" aria-label="Main navigation">
            <a href="auth/login.php">Log in</a>
            <a class="button button-small" href="auth/signup.php">Create account</a>
        </nav>
    </header>

    <main>
        <section class="hero">
            <div class="hero-copy">
                <span class="eyebrow">Welcome to Paws &amp; Fur</span>
                <h1>Care for your pets, all in one place.</h1>
                <p>Keep your pet's information organized, request appointments, and stay connected with your clinic.</p>
                <div class="hero-actions">
                    <a class="button" href="auth/signup.php">Get started</a>
                    <a class="button button-outline" href="auth/login.php">I have an account</a>
                </div>
            </div>
            <div class="hero-art" aria-hidden="true">
                <div class="hero-art-inner"><span>🐾</span><strong>Happy pets.<br>Happy people.</strong></div>
            </div>
        </section>

        <section class="feature-section" aria-labelledby="feature-title">
            <div class="section-heading"><span class="eyebrow">Your clinic portal</span><h2 id="feature-title">Simple tools for everyday pet care</h2></div>
            <div class="feature-grid">
                <article class="feature-card"><span class="feature-icon" aria-hidden="true">01</span><h3>Pet records</h3><p>Save and update important details about every pet in your family.</p></article>
                <article class="feature-card"><span class="feature-icon" aria-hidden="true">02</span><h3>Owner information</h3><p>Keep your contact information together with your pets' records.</p></article>
                <article class="feature-card"><span class="feature-icon" aria-hidden="true">03</span><h3>Appointments</h3><p>Request visits and keep track of your upcoming care.</p></article>
            </div>
        </section>
    </main>

    <footer class="site-footer">© <?= date('Y') ?> Paws &amp; Fur Clinic</footer>
</body>
</html>
