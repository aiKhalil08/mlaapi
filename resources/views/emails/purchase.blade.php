<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Purchase Succesful</title>
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

        Hi, {{$sale->student->name}},
    
        <p>
            Thank you for your recent purchase of
            @if ($sale->type->name == 'Cohort')
                {{$sale->cohort->name}}
            @else
                {{$sale->course->title}}{{$sale->course->code ? ' - '.$sale->course->code : ''}}
            @endif
            on Mitiget Learning Academy! We're thrilled to have you on board and excited for you to start learning.
            {{-- @if ($sale->type->name == 'Cohort')
                You will be embarking on this journey with your fellow cohort.
            @endif --}}
        </p>
    
    
        <div class="purchase-details">
            <h3 style="margin-bottom: 8px">Purchase Details</h3>

            <table style="border-collapse: collapse; max-width: 600px; width: 90%;">
                <tr>
                    <td>Name</td>
                    <td>{{$sale->student->name}}</td>
                </tr>
                <tr>
                    <td>Email</td>
                    <td>{{$sale->student->email}}</td>
                </tr>
                <tr>
                    <td>Price</td>
                    <td>
                        @php
                        echo '&#8358;'.number_format($sale->price, 2);
                        @endphp
                    </td>
                </tr>
                <tr>
                    <td>Date</td>
                    <td>
                        @php
                            $carbon = new \Carbon\Carbon($sale->date);
                            echo $carbon->format('F d, Y');
                        @endphp
                    </td>
                </tr>
                <tr>
                    <td>Course name</td>
                    <td>
                        @if ($sale->type->name == 'Cohort')
                            {{$sale->cohort->course->title}}{{$sale->cohort->course->code ? ' - '.$sale->cohort->course->code : ''}}
                        @else
                            {{$sale->course->title}}{{$sale->course->code ? ' - '.$sale->course->code : ''}}
                        @endif
                    </td>
                </tr>
                @if ($sale->referral)
                <tr>
                    <td>Referred by</td>
                    <td>
                        {{$sale->referral->referrer->name}}
                    </td>
                </tr>
                @endif
            </table>
        </div>

        <p>
            Further information about this {{$sale->type->name == 'Cohort' ? 'cohort' : 'course'}} will be communicated to you in due time.
        </p>
    
        <h4>Need help?</h4>

        <p>
            If you have any questions or encounter any issues, please don't hesitate to contact our support team at mla@mitiget.com.ng.
        </p>
    
    
        <p><strong>Happy Learning!</strong></p>
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