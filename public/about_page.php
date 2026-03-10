<?php
$pageTitle = "About";
include __DIR__ . '/../includes/header.php';
?>

<body class="d-flex flex-column min-vh-100">
    <main class="flex-grow-1">
        <section class="py-5">
            <div class="container">
                <h1>About Us</h1>
                <p class="lead">Learn more about our team and project.</p>
                
                <div class="row mt-4">
                    <div class="col-lg-8">
                        <h3>IT490 System Integration Project</h3>
                        <p>This project demonstrates a web application with distributed architecture using RabbitMQ for message-based communication between frontend and backend services.</p>
                        
                        <h4 class="mt-4">Our Team</h4>
                        <p>We are a group of students working on system integration concepts including:</p>
                        <ul>
                            <li>Web frontend development with PHP and Bootstrap</li>
                            <li>Message queue integration with RabbitMQ</li>
                            <li>Database management and session handling</li>
                            <li>Distributed system architecture</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>
    </main>

<?php
include __DIR__ . '/../includes/footer.php';
?>
