<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - MuscuScore</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>

    <div class="logo">MUSCUSCORE</div>

    <div class="decor">
        <div></div>
        <div></div>
        <div style="width: 20px; margin-left: 20px;"></div>
    </div>

    <div class="login-container">
        <h2>SE CONNECTER</h2>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <input type="email" name="email" placeholder="E-mail" required>
            <input type="password" name="password" placeholder="Mot de passe" required>

            <div class="checkbox">
                <label>
                    <input type="checkbox" name="remember"> Garder la session ouverte
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
