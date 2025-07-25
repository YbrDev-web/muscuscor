<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - MuscuScore</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>

    <header>
        <a href="{{ route('home') }}">
            <div class="logo">MUSCUSCORE</div>
        </a>
        <nav class="nav">
            <a href="{{ route('login') }}">Connexion</a>
            <a href="{{ route('register') }}">Inscription</a>
        </nav>
        <div class="decor">
            <div></div>
            <div></div>
            <div style="width: 20px; margin-left: 20px;"></div>
        </div>
    </header>

    <div class="login-container">
        <h2>SE CONNECTER</h2>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="input-group">
                <input type="email" name="email" placeholder="E-mail" required>
                <?php if ($errors->has('email')): ?>
                    <span class="error">{{ $errors->first('email') }}</span>
                <?php endif; ?>
            </div>
            <div class="input-group">
                 <input type="password" name="password" placeholder="Mot de passe" required>
            </div>

            <div class="checkbox">
                <input type="checkbox" id="remember">
                <label for="remember">
                    Garder la session ouverte
                </label>
            </div>

            <div class="forgot-password">
                <a href="{{ route('password.request') }}">Mot de passe oublié ?</a>
            </div>

            <button type="submit">SE CONNECTER</button>
        </form>
    </div>

</body>
</html>