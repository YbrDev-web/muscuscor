<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>MuscuScore | Suivi & Défis Musculation</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>

    <header>
        <div class="logo">MuscuScore</div>
        <nav>
            <a href="{{ route('login') }}">Connexion</a>
            <a href="{{ route('register') }}">Inscription</a>
        </nav>
    </header>

    <section class="hero">
        <h1>Suivi, Défis, Progrès.</h1>
        <p>Le réseau social ultime pour les passionnés de musculation.</p>
        <a href="{{ route('register') }}">Rejoins la communauté</a>
    </section>

    <section class="features">
        <div class="feature">
            <h3>Suivi de performances</h3>
            <p>Enregistre tes exercices, poids, répétitions et visualise ta progression.</p>
        </div>
        <div class="feature">
            <h3>Défis communautaires</h3>
            <p>Participe à des challenges, débloque des badges et dépasse tes limites.</p>
        </div>
        <div class="feature">
            <h3>Réseau social sportif</h3>
            <p>Publie, commente, like, connecte-toi avec d'autres athlètes motivés.</p>
        </div>
    </section>

    <footer>
        &copy; {{ date('Y') }} MuscuScore. Tous droits réservés.
    </footer>

</body>
</html>
