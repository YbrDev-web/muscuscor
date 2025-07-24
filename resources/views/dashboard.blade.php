<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - MuscuScore</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
</head>
<body>

    <!-- Barre de navigation -->
    <nav>
        <div class="logo">
            <h1>MuscuScore</h1>
        </div>
        <div class="nav-links">
            @include('partials.menu')
        </div>
        <form method="POST" action="{{ route('logout') }}" class="logout">
            @csrf
            <button type="submit" class="button red">
                Se déconnecter
            </button>
        </form>
    </nav>

    <main class="fade-in">
        <div class="dashboard-news">

            <!-- Message de bienvenue -->
            <section class="news-section">
                <p>👋 Bienvenue sur <strong>MuscuScore</strong> ! Vous êtes connecté <strong>{{ Auth::user()->name }}</strong>.</p>
            </section>

            <!-- Actualités sportives -->
            <section class="news-section">
                <h2 class="news-title">📰 Dernières actualités sportives</h2>

                <div class="news-list">
                    <article class="news-item">
                        <h2>🎯 Paris 2024 : les JO approchent</h2>
                        <p>À moins de 100 jours des Jeux Olympiques, les athlètes français intensifient leurs préparations physiques et mentales...</p>
                        <!-- <a href="{{ route('posts.index') }}">Lire plus</a> -->
                    </article>

                    <article class="news-item">
                        <h2>🏋️ Records battus en powerlifting</h2>
                        <p>Deux records du monde ont été battus ce week-end au championnat européen des -93kg hommes...</p>
                        <!-- <a href="{{ route('posts.index') }}">Lire plus</a> -->
                    </article>

                    <article class="news-item">
                        <h2>🚴 Tour de France 2025 : parcours dévoilé</h2>
                        <p>Le tracé officiel du Tour de France met à l’honneur les Alpes du Sud avec une arrivée inédite à Lyon...</p>
                        <!-- <a href="{{ route('posts.index') }}">Lire plus</a> -->
                    </article>
                </div>
                <button><a href="{{ route('posts.index') }}">Plus d'articles</a></button>
            </section>

        </div>
    </main>

</body>
</html>
