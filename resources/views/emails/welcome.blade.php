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

        Hi, {{$first_name}},
    
        <p>
            Welcome to Mitiget Learning Academy, your one-stop shop for unlocking your full potential! We're thrilled to have you join our vibrant community of learners.
        </p>
    
    
        <p>
            At Mitiget, we believe that education should be accessible, engaging, and empowering. We offer a diverse range of courses, from in-demand programming and tech skills to practical corporate training and enriching events. Whether you're a seasoned professional looking to upskill, a recent graduate seeking career advancement, or simply someone passionate about learning new things, Mitiget has something for you.
        </p>
    
        <h4>Here's a glimpse of what you can expect at Mitiget Learning Academy:</h4>
    
        <ul style="list-style-type: disc">
    
            <li><strong>Extensive Course Library:</strong> Explore our comprehensive library of courses, covering a wide spectrum of topics from programming languages like Python and Java to data science, design thinking, and essential business skills.</li>
    
    
            <li><strong>Expert Instructors:</strong> Learn from industry experts and experienced instructors who are passionate about knowledge sharing and your success.</li>
    
            <li><strong>Engaging Learning Experience:</strong> Our interactive learning platform provides you with a flexible and engaging learning experience, allowing you to learn at your own pace, anytime, anywhere.</li>
    
            <li><strong>Live Events & Webinars:</strong> Stay ahead of the curve by attending our live events and webinars featuring industry leaders and thought-provoking discussions.</li>
    
            <li><strong>Supportive Community:</strong> Connect with fellow learners, ask questions, and share your experiences in our vibrant online community.</li>
    
        </ul>
    
    
        <h4>Get Started:</h4>
    
        <ul style="list-style-type: disc;">
    
            <li><strong>Browse Courses:</strong> Explore our course catalog to find the perfect program to fuel your learning journey: https://mla.mitiget.com/course-catalogue</li>
    
    
            <li><strong>Discover Events:</strong> See upcoming events and webinars that ignite your curiosity: https://mla.mitiget.com/events</li>
    
        </ul>
    
        <p>
            We're confident that Mitiget Learning Academy will equip you with the knowledge and skills you need to achieve your goals. Let's embark on this learning adventure together!
        </p>
    
    
        <p><strong>Warm Regards,</strong></p>
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