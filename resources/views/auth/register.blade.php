<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription - MuscuScore</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
</head>
<body>
    <div class="logo">MUSCUSCORE</div>

    <div class="decor">
        <div></div>
        <div></div>
        <div style="width: 20px; margin-left: 20px;"></div>
    </div>

    <div class="register-container">
        <h2>INSCRIPTION</h2>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <input type="text" name="name" placeholder="Nom complet" required>
            <input type="email" name="email" placeholder="E-mail" required>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <input type="password" name="password_confirmation" placeholder="Confirmer le mot de passe" required>

            <button type="submit">S'INSCRIRE</button>
        </form>

        <div class="link-login">
            <span>Déjà un compte ? <a href="{{ route('login') }}">Se connecter</a></span>
        </div>
    </div>

</body>
</html>
