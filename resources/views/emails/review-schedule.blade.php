<!doctype html>
<html lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Programmation en attente de validation — Schedule awaiting approval</title>
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

        a {
            padding: 10px 15px;

            font-weight: bold;
            text-decoration: none;
            color: #FFFFFF;

            border-radius: 5px;
            background-color: #2B2C43;

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
                <h1>Une programmation est en attente de validation</h1>
                <p>
                    Une programmation de <strong>{{ $schedule->owner->name }}</strong> pour la campagne <strong>{{ $schedule->campaign->name  }}</strong> est en attente de validation.<br /><br />
                    Vous pouvez utiliser le lien ci-dessous pour y accèder directement.
                </p>
                <h3>
                    <a href="https://connect.neo-ooh.com/campaigns/{{ $schedule->campaign_id }}#{{ $schedule->id }}">
                        Accéder à la campagne
                    </a>
                </h3>
            </td>
        </tr>
        <tr class="spacer"><td></td></tr>
        <tr class="main">
            <td colspan="2">
                <h1>A scheduled ad is awaiting review</h1>
                <p>
                    A scheduled ad from <strong>{{ $schedule->owner->name }}</strong> for the campaign <strong>{{ $schedule->campaign->name  }}</strong> is pending review.<br /><br />
                    You can use the link below to access it directly.
                </p>
                <h3>
                    <a href="https://connect.neo-ooh.com/campaigns/{{ $schedule->campaign_id }}#{{ $schedule->id }}">
                        Access the campaign
                    </a>
                </h3>
            </td>
        </tr>
        <tr class="logos">
            <td align="center">
                <img src="{{ secure_asset('images/main.dark.fr.png') }}" alt="Neo-OOH Logo">
            </td>
            <td align="center">
                <img src="{{ secure_asset('images/main.dark.en.png') }}" alt="Neo-OOH Logo">
            </td>
        </tr>
        <tr class="legals" align="center">
            <td colspan="2">© {{ date("Y") }} Neo-OOH. Tous droits réservés. — All rights reserved.</td>
        </tr>
    </table>
</body>
</html>
