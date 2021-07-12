<!doctype html>
<html lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>{!! __("emails.traffic-reminder-subject") !!}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <style type="text/css">
        body {
            font-family      : 'Poppins', sans-serif;
            background-color : #F4F4F5;
        }

        table {
            margin : auto;
            width  : 550px;
        }

        .main {
            background-color : #FFFFFF;
        }

        .main td {
            padding       : 20px;
            border        : 1px solid #BBBBBB;
            border-radius : 5px;
        }

        h1 {
            margin      : 25px 25px 50px;
            font-size   : 1.8em;
            font-weight : bold;
            color       : #2B2C43;
            text-align  : center;
        }

        p {
            margin : 1em;
        }

        h3 {
            margin-top : 40px;
            text-align : center;
        }

        a {
            padding          : 10px 15px;

            font-weight      : bold;
            text-decoration  : none;
            color            : #FFFFFF;

            border-radius    : 5px;
            background-color : #2B2C43;

        }

        .spacer td {
            padding : 20px;
        }

        .logos img {
            margin : 50px;
            height : 75px;
        }

        .legals {
            color     : #888888;
            font-size : .8em;
        }

        .legals td {
            padding-bottom : 15px;
        }
    </style>
</head>
<body>
<table border="0" cellpadding="0" cellspacing="0">
    @if(App::currentLocale() === 'fr')
    <tr class="main">
        <td colspan="2">
            <p>
                Bonjour {{ $actor->name }},<br/><br/>

                <b>L’achalandage mensuel des établissements de nos différents réseaux est une donnée essentielle pour nos clients.</b> Depuis plusieurs années, l’industrie publicitaire et les acheteurs média en particulier exigent en effet de leurs partenaires médias des données précises sur leur clientèle.<br/><br />

                <b>Dans ce contexte, votre collaboration est essentielle.</b> Afin de faciliter l’accès à cette information dont vous disposez, Neo a développé, sur son application « Connect », un module appelé « Propriétés » qui permettra à chacun de ses 400+ partenaires de compléter l’information propre à son établissement.<br/><br/>
            </p>
            <div style="text-align: center;">UNE NOUVELLE MANIÈRE POUR NOUS COMMUNIQUER CETTE INFORMATION ESSENTIELLE</div>
            <p>
                Chaque mois, un rappel vous sera envoyé pour vous demander de nous fournir l’achalandage mensuel de votre établissement pour le mois qui vient de se compléter (nombre de visites)<br/><br />
                Ces données seront comparées à celles de 2019 que nous considérerons comme l’année de référence compte tenu de la pandémie récente que vous venons de traverser. Vous n’aurez donc qu’à compléter l’information à propos de 2019 une seule fois, et à partir des données saisies, des pourcentages de comparaison seront alors calculés de manière automatisée.<br/><br />
                <b>Si vous ne disposez pas de cette information ou si vous considérez ne pas être la personne identifiée pour nous fournir ce type d’information, merci de nous fournir les coordonnées de la personne à qui nous devons nous adresser. Nous lui ferons alors parvenir un accès personnalisé sur cette section de l’application Connect.</b><br/><br/><br />

                Nous vous remercions pour votre collaboration et demeurons disponibles pour toute question ou précision.<br/><br/>

                L’équipe de Neo-ooh.
            </p>
            <h3>
                <a href="https://connect.neo-ooh.com/">
                    {!! __("emails.access-connect") !!}
                </a>
            </h3>
        </td>
    </tr>
    @else
    <tr class="main">
        <td colspan="2">
            <p>
                Hello {{ $actor->name }},<br/><br/>
                <b>The monthly traffic of the establishments in our networks is essential data for our clients.</b> For several years now, the advertising industry and media buyers have been demanding precise data on their clientele from their media partners.<br/><br />

                <b>In this context, your collaboration is essential.</b> To facilitate access to this information, Neo has developed a module called "Properties" on its "Connect" application that will allow each of its 400+ partners to complete the information specific to their establishment.<br/><br/>
            </p>
            <div style="text-align: center;">A NEW WAY TO COMMUNICATE THIS ESSENTIAL INFORMATION TO US</div>
            <p>
                Each month, a reminder will be sent to you asking you to provide us with your establishment's monthly traffic for the month just ended (number of visits)<br/><br />

                This data will be compared to that of 2019, which we will consider as the reference year given the recent pandemic that we have all experienced. You will only have to complete the information for 2019 one time, and from the data entered a comparison will be calculated automatically.<br/><br />

                <b>If you do not have this data, please provide us with the contact information of the person who is responsible for this task. We will then send them a personalized access to this section of the Connect application.</b><br/><br/><br />

                We thank you for your cooperation and remain available for any questions or clarifications.<br/><br/>

                The Neo-ooh team.
            </p>
            <h3>
                <a href="https://connect.neo-ooh.com/">
                    {!! __("emails.access-connect") !!}
                </a>
            </h3>
        </td>
    </tr>
    @endif
    <tr class="logos">
        <td align="center">
            <img src="{{ secure_asset("images/main.dark.$actor->locale.png") }}" alt="Neo-OOH Logo">
        </td>
    </tr>
    <tr class="legals" align="center">
        <td colspan="2">{!! __("email-legals", ["date" => date("Y")]) !!}© {{ date("Y") }}</td>
    </tr>
</table>
</body>
</html>