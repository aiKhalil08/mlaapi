<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>First View</title>
    <style>
        body{background-color: bisque}
    </style>
</head>
<body>
    Hello, welcome to the first route.

    How are you doing today?

    <img src="{{asset('/storage/images/testimonials/daniel_ishaya.jpg')}}" alt="">

    Would you like to see the second view? <a href="{{route('second_view')}}">click here</a>
</body>
</html>