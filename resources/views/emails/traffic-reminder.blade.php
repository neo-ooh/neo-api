<!doctype html>
<html lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>{!! __("emails.traffic-reminder-subject") !!}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <style type="text/css">
        body
        {
            font-family      : 'Poppins', sans-serif;
            background-color : #F4F4F5;
        }

        table
        {
            margin : auto;
            width  : 550px;
        }

        .main
        {
            background-color : #FFFFFF;
        }

        .main td
        {
            padding       : 20px;
            border        : 1px solid #BBBBBB;
            border-radius : 5px;
        }

        h1
        {
            margin      : 25px 25px 50px;
            font-size   : 1.8em;
            font-weight : bold;
            color       : #2B2C43;
            text-align  : center;
        }

        p
        {
            margin : 1em;
        }

        h3
        {
            margin-top : 40px;
            text-align : center;
        }

        a
        {
            padding          : 10px 15px;

            font-weight      : bold;
            text-decoration  : none;
            color            : #FFFFFF;

            border-radius    : 5px;
            background-color : #2B2C43;

        }

        .spacer td
        {
            padding : 20px;
        }

        .logos img
        {
            margin : 50px;
            height : 75px;
        }

        .legals
        {
            color     : #888888;
            font-size : .8em;
        }

        .legals td
        {
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

                    <b>Nous vous écrivons aujourd’hui afin de vous rappeler l’importance de compléter les données d’achalandage
                        pour le mois de {{ $date->locale('fr')->format('F') }}.</b><br/><br/>

                    Comme vous le comprenez, l’achalandage mensuel des établissements de nos différents réseaux est une donnée
                    essentielle pour nos clients.<b> Dans ce contexte, votre collaboration est essentielle.</b><br/>
                </p>
                <p>
                    Si par contre, vous n’avez pas encore complété les données d’achalandage de 2019 et des mois de 2021 jusqu’à
                    aujourd’hui, sachez que Neo a développé un module appelé « Propriétés » sur son application « Connect » qui
                    vous permettra de compléter l’information propre à votre établissement en trois étapes simples&nbsp;:
                <ol>
                    <li>Compléter les informations d’achalandage concernant 2019 une seule fois</li>
                    <li>Compléter les informations d’achalandage concernant 2021</li>
                    <li>Chaque mois, compléter l’information d’achalandage du mois précédent</li>
                </ol>
                <br/>
                <b>Si vous ne disposez pas de cette information ou vous ne considérez pas être la personne identifiée pour nous
                    fournir ce type d’information, merci de nous fournir les coordonnées de la personne à qui nous devons nous
                    adresser. Nous lui ferons alors parvenir un accès personnalisé sur cette section de l’application Connect.</b><br/><br/><br/>

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

                    <b>We are writing to you today to remind you of the importance of completing traffic data for the month
                        of {{ $date->format('F') }}.</b><br/><br/>

                    As you will understand, the monthly traffic of the establishments in our various networks is essential data
                    for our clients.<b> In this context, your collaboration is essential.</b><br/>
                </p>
                <p>
                    If you haven’t yet completed the traffic data for 2019 and for the months of 2021 to today, be aware that Neo
                    has developed a module called "Properties" on its "Connect" application that will allow you to complete the
                    information specific to your establishment in three easy steps:
                <ol>
                    <li>Complete the required traffic information for the reference year, 2019</li>
                    <li>Complete the traffic information for 2021</li>
                    <li>Each month, complete the previous month's information</li>
                </ol>
                <br/>
                <b>If you do not have this information or you do not consider yourself to be the person identified to provide us
                    with this type of information, please provide us with the contact information of the person we should contact.
                    We will then send them personalized access to this section of the Connect application.</b><br/><br/><br/>

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
            <img src="{{ secure_asset("images/main.dark.png") }}" alt="Neo-OOH Logo">
        </td>
    </tr>
    <tr class="legals" align="center">
        <td colspan="2">{!! __("email-legals", ["date" => date("Y")]) !!}© {{ date("Y") }}</td>
    </tr>
</table>
</body>
</html>
