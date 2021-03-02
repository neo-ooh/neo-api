<!doctype html>
<html lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>{!! __("email-2fa-subject") !!}</title>
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
    <tr class="main">
        <td colspan="2">
            <h1>{!! __("email-2fa-title") !!}</h1>
            <p>
                {!! __("email-2fa-body") !!}
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
    <tr class="legals" align="center">
        <td colspan="2">{!! __("email-legals", ["date" => date("Y")]) !!}</td>
    </tr>
</table>
</body>
</html>

