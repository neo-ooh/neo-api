<!doctype html>
<html lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Connexion aux services web Neo-ooh — Neo-ooh web services connection</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <style type="text/css">
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #F4F4F5;
        }

        table {
            margin: auto;
            width: 550px;
        }

        .main {
            background-color: #FFFFFF;
        }

        .main td {
            padding: 20px;
            border: 1px solid #BBBBBB;
            border-radius: 5px;
        }

        h1 {
            margin: 25px 25px 50px;
            font-size: 1.8em;
            font-weight: bold;
            color: #2B2C43;
            text-align: center;
        }

        p {
            margin: 1em;
        }

        h3 {
            margin-top: 40px;
            text-align: center;
        }

        h3 span {
            display: inline-block;
            width: 20px;
            padding: 5px 2px;
            margin: 5px;

            font-size: 1.5em;
        }

        span.letter-spacer {
            border: none;
            width: 0;
        }

        .spacer td {
            padding: 20px;
        }

        .logos img {
            margin: 50px;
            height: 75px;
        }

        .legals {
            color: #888888;
            font-size: .8em;
        }

        .legals td {
            padding-bottom: 15px;
        }
    </style>
</head>
<body>
<table border="0" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center" colspan="2">
            <h6>ENGLISH VERSION BELOW</h6>
        </td>
    </tr>
    <tr class="main">
        <td colspan="2">
            <h1>Jeton d'authentification Neo-OOH</h1>
            <p>
                Entrez le code ci-dessous pour confirmer votre connexion à votre compte Neo-ooh.
            </p>
            <h3>
                <span>{{ substr($token, 0, 1) }}</span>
                <span>{{ substr($token, 1, 1) }}</span>
                <span>{{ substr($token, 2, 1) }}</span>
                <span class="letter-spacer"></span>
                <span>{{ substr($token, 3, 1) }}</span>
                <span>{{ substr($token, 4, 1) }}</span>
                <span>{{ substr($token, 5, 1) }}</span>
            </h3>
        </td>
    </tr>
    <tr class="spacer">
        <td></td>
    </tr>
    <tr class="main">
        <td colspan="2">
            <h1>Neo-OOH authentication token</h1>
            <p>
                Enter the code below to confirm your connection to your Neo-ooh account.
            </p>
            <h3>
                <span>{{ substr($token, 0, 1) }}</span>
                <span>{{ substr($token, 1, 1) }}</span>
                <span>{{ substr($token, 2, 1) }}</span>
                <span class="letter-spacer"></span>
                <span>{{ substr($token, 3, 1) }}</span>
                <span>{{ substr($token, 4, 1) }}</span>
                <span>{{ substr($token, 5, 1) }}</span>
            </h3>
        </td>
    </tr>
    <tr class="logos">
        <td align="center">
            <img src="{{ asset('images/main.dark.fr.png') }}" alt="Neo-OOH Logo">
        </td>
        <td align="center">
            <img src="{{ asset('images/main.dark.en.png') }}" alt="Neo-OOH Logo">
        </td>
    </tr>
    <tr class="legals" align="center">
        <td colspan="2">© 2020 Neo-OOH. Tous droit réservés. — All rights reserved.</td>
    </tr>
</table>
</body>
</html>

