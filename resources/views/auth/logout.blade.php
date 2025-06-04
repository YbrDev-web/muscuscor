<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Déconnexion - MuscuScore</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
</head>
<body>

    <!-- En-tête -->
    <header>
        <div class="container">
            <h1>MuscuScore</h1>
        </div>
    </header>

    <!-- Contenu principal -->
    <main>
        <div class="container">
            <section class="news-section">
                <h2 class="news-title">👋 Vous êtes déconnecté</h2>
                <p>Merci d'avoir utilisé <strong>MuscuScore</strong>. À bientôt pour repousser vos limites !</p>

                <a href="{{ route('login') }}" class="logout-link">🔁 Se reconnecter</a>
            </section>
        </div>
    </main>

</body>
</html>
