<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Registration Succesful</title>
    {{-- <script src="https://cdn.tailwindcss.com"></script> --}}
    <style>
        
        footer {
            font-size: small;
            color: gray;
        }
        hr {
            height: 1px;
            background-color: #DD127B;

        }

        table {font-size: 14px; text-align: left}
        table, td {border: 1px solid #DD127B;}
        td {padding: 4px;}
    </style>
</head>
<body>
    <header>
        <img src="https://mla.mitiget.com/assets/logo.png" alt="mla logo" width="150">
    </header>
    <hr>
    <main class="bg-red-400">

        Dear {{$registration->first_name, $registration->last_name}},
    
        <p>
            Thank you for registering for our upcoming event: <strong>{{$event->name}}</strong>, scheduled to hold on {{\Carbon\Carbon::parse($event->date->start)->format('jS F, Y')}}! We are delighted to have you join us and look forward to your participation.
        </p>

        <p>
            Your registration is confirmed, and we will send a reminder email with additional details as we get closer to the event date.
        </p>
    

        <p>
            If you have any questions in the meantime, please don't hesitate to reach out to us at mla@mitiget.com.ng.
        </p>

        <p>
            Thank you again for your interest. We look forward to seeing you at {{$event->name}}!
        </p>
    
    
        <p>Best regards,</p>
        <p>The Mitiget Learning Academy Team</p>
    
        <p>
            <strong>P.S.</strong> Stay tuned for exciting offers and promotions on upcoming courses! Follow us on social media for the latest updates:
            <ul style="list-style-position: inside; list-style-type: disc">
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