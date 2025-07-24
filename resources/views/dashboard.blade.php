<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - MuscuScore</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
</head>
<body>

    <!-- En-tête -->
    <header>
        <div class="container">
            <div class="max-w-7xl mx-auto flex justify-between items-center">
                @include('partials.menu')
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="logout-btn">
                        Se déconnecter
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main>

    <h1 class="text-3xl font-bold text-red-600 dark:text-red-400">Dashboard</h1>
    
        <div class="container">

            <!-- Message de bienvenue -->
            <section class="news-section">
                <p>👋 Bienvenue sur <strong>MuscuScore</strong> ! Vous êtes connecté <strong>{{ Auth::user()->name }}</strong>.</p>
            </section>

            <!-- Actualités sportives -->
            <section class="news-section">
                <h2 class="news-title">📰 Dernières actualités sportives</h2>

                <div class="news-list">
                    <article class="news-item">
                        <h3>🎯 Paris 2024 : les JO approchent</h3>
                        <p>À moins de 100 jours des Jeux Olympiques, les athlètes français intensifient leurs préparations physiques et mentales...</p>
                        <a href="{{ route('posts.index') }}">Lire plus</a>
                    </article>

                    <article class="news-item">
                        <h3>🏋️ Records battus en powerlifting</h3>
                        <p>Deux records du monde ont été battus ce week-end au championnat européen des -93kg hommes...</p>
                        <a href="#">Lire plus</a>
                    </article>

                    <article class="news-item">
                        <h3>🚴 Tour de France 2025 : parcours dévoilé</h3>
                        <p>Le tracé officiel du Tour de France met à l’honneur les Alpes du Sud avec une arrivée inédite à Lyon...</p>
                        <a href="#">Lire plus</a>
                    </article>
                </div>
            </section>

        </div>
    </main>

</body>
</html>