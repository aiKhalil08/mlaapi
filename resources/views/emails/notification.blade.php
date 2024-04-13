<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Welcome to MLA</title>
    <style>
        html {
            font-size: 16px;
        }

        footer {
            font-size: small;
            color: gray;
        }

        ul, li {margin: 0; padding: 0;}

        ul {
            list-style-position: inside; 
        }

        li {
            margin: 8px 0;
        }

        hr {
            height: 1px;
            background-color: #DD127B;

        }

    </style>
</head>
<body>
    <header>
        <img src="https://mla.mitiget.com/assets/logo.png" alt="mla logo" width="150">
    </header>
    <hr>
    <main>

        {!! $template !!}
    
        <p>
            <strong>P.S.</strong> Stay tuned for exciting offers and promotions on upcoming courses! Follow us on social media for the latest updates:
            <ul style="list-style-type: disc">
                <li><a href="https://www.facebook.com/mitigetlearningacademy">Facebook</a></li>
                <li><a href="https://instagram.com/mitigetacademy">Instagram</a></li>
                <li><a href="https://www.linkedin.com/in/mitiget-learning-academy-0260732b1">Linkedin</a></li>
                <li><a href="https://twitter.com/mitigetmla">Twitter (X)</li>
            </ul>
        </p>
    </main>
    <hr>
    <footer>
        <p>Oluwatobi House, 5th Floor Front Wing 73 Allen Avenue Ikeja, Lagos</p>
        <p>&copy; {{date('Y')}} Powered by Mitiget Learning Academy. All rights reserved.</p>
    </footer>
</body>
</html>