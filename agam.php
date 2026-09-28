<?php
require_once __DIR__ . '/session.php';
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$isLoggedIn = isUserLoggedIn();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>PG LIFE</title>
        <link rel="stylesheet" href="css/style.css"/>
    </head>
    <body>

        <!-- ================= HEADER ================= -->

<header class="header">

    <!-- Logo -->
    <a href="agam.php" class="logo_link">
    <img src="Image/img/logo.png" alt="PG Life logo" class="logo-image"/> 
    </a>

    <!-- Navigation -->
    <nav class="nav">

        <?php if ($isLoggedIn): ?>
            <a href="dashboard.php" class="nav-link-button dashboard-link">Dashboard</a>
            <span class="welcome-text">👋 Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
            <a href="logout.php" class="nav-link-button">↪ Logout</a>
        <?php else: ?>
            <button onclick="openModal('signupModal')">👤 Signup</button>
            <button onclick="openModal('loginModal')">↪ Login</button>
        <?php endif; ?>

    </nav>

</header>

<?php if ($flash): ?>
    <div class="flash-message flash-<?php echo htmlspecialchars($flash['type']); ?>">
        <?php echo htmlspecialchars($flash['message']); ?>
    </div>
<?php endif; ?>

<!-- ================= HERO SECTION ================= -->

<section class="hero">
   <img src="Image/img/bg.png" alt="students">
   <div class="hero-content">
    <h1>Happiness per square Foot</h1>

        <form id="citySearchForm" class="search-box" method="GET" action="property_list.php" novalidate>
            <input
                type="text"
                name="city"
                id="citySearchInput"
                placeholder="Enter your city to search for PGs"
                aria-label="Search city"
            >

            <button type="submit" aria-label="Search city">
                🔍
            </button>
        </form>
        </div>
    </section>


<!-- ================= MAJOR CITIES ================= -->

<section class="cities" id="cities">

    <h2 class="section-title">
        Major Cities
    </h2>


    <div class="city-grid">


        <!-- Delhi -->

        <a href="property_list.php?city=Delhi" class="city-card">

            <div class="city-circle">

                <img
                    src="Image/img/delhi.png"
                    alt="Delhi"
                >

            </div>

            <div class="city-name">
                DELHI
            </div>

        </a>



        <!-- Mumbai -->

        <a href="property_list.php?city=Mumbai" class="city-card">

            <div class="city-circle">

                <img
                    src="Image/img/mumbai.png"
                    alt="Mumbai"
                >

            </div>

            <div class="city-name">
                MUMBAI
            </div>

        </a>



        <!-- Bengaluru -->

        <a href="property_list.php?city=Bengaluru" class="city-card">

            <div class="city-circle">

                <img
                    src="Image/img/bangalore.png"
                    alt="Bengaluru"
                >

            </div>

            <div class="city-name">
                BENGALURU
            </div>

        </a>



        <!-- Hyderabad -->

        <a href="property_list.php?city=Hyderabad" class="city-card">

            <div class="city-circle">

                <img
                    src="Image/img/hyderabad.png"
                    alt="Hyderabad"
                >

            </div>

            <div class="city-name">
                HYDERABAD
            </div>

        </a>

    </div>

</section>



<!-- ================= FOOTER ================= -->

<footer class="footer">

    <div class="footer-grid">

        <div>

            <h3>PG Life</h3>

            <p>
                Find comfortable and affordable
                paying guest accommodation
                in major cities.
            </p>

        </div>


        <div>

            <h3>Quick Links</h3>

            <ul>

                <li>
                    <a href="agam.php">Home</a>
                </li>

                <li>
                    <a href="#cities">Major Cities</a>
                </li>

            </ul>

        </div>


        <div>

            <h3>Contact</h3>

            <p>
                Email: support@pglife.com
            </p>

            <p>
                Phone: +91 98765 43210
            </p>

        </div>

    </div>


    <div class="footer-bottom">

        © 2026 PG Life. All Rights Reserved.

    </div>

</footer>

<script>
    (function () {
        const validCities = {
            delhi: 'Delhi',
            mumbai: 'Mumbai',
            bengaluru: 'Bengaluru',
            bangalore: 'Bengaluru',
            hyderabad: 'Hyderabad'
        };

        const form = document.getElementById('citySearchForm');
        const input = document.getElementById('citySearchInput');

        if (form && input) {
            form.addEventListener('submit', function (event) {
                event.preventDefault();

                const rawValue = (input.value || '').trim();
                if (!rawValue) {
                    alert('Please enter a city name to search for PGs.');
                    input.focus();
                    return;
                }

                const normalized = rawValue.toLowerCase();
                const cityKey = Object.keys(validCities).find(function (key) {
                    return normalized === key || normalized === validCities[key].toLowerCase();
                });

                if (!cityKey) {
                    alert('Please select or enter a valid city: Delhi, Mumbai, Bengaluru, or Hyderabad.');
                    input.focus();
                    return;
                }

                window.location.href = 'property_list.php?city=' + validCities[cityKey];
            });
        }
    })();
</script>

<!-- ================= SIGNUP MODAL ================= -->

<div class="modal" id="signupModal">

    <div class="modal-box">


        <div class="modal-head">

            Signup with PGLife

        </div>


        <button
            class="close"
            onclick="closeModal('signupModal')"
        >
            &times;
        </button>


        <form
            class="modal-body"
            method="POST"
            action="auth.php"
        >
            <input type="hidden" name="action" value="signup">

            <!-- Full Name -->

            <div class="input-row">

                <div class="icon">
                    👤
                </div>

                <input
                    type="text"
                    name="full_name"
                    placeholder="Full Name"
                    required
                >

            </div>



            <!-- Phone -->

            <div class="input-row">

                <div class="icon">
                    ☎
                </div>

                <input
                    type="tel"
                    name="phone"
                    placeholder="Phone Number"
                    required
                >

            </div>



            <!-- Email -->

            <div class="input-row">

                <div class="icon">
                    ✉
                </div>

                <input
                    type="email"
                    name="email"
                    placeholder="Email"
                    required
                >

            </div>



            <!-- Password -->

            <div class="input-row">

                <div class="icon">
                    🔒
                </div>

                <input
                    type="password"
                    name="password"
                    placeholder="Password"
                    required
                >

            </div>



            <!-- College -->

            <div class="input-row">

                <div class="icon">
                    🏫
                </div>

                <input
                    type="text"
                    name="college_name"
                    placeholder="College Name"
                    required
                >

            </div>



            <!-- Gender -->

            <div class="gender">

                I'm a

                <label>
                    <input
                        type="radio"
                        name="gender"
                        value="male"
                        required
                    >
                    Male
                </label>

                <label>
                    <input
                        type="radio"
                        name="gender"
                        value="female"
                    >
                    Female
                </label>

            </div>



            <button
                class="submit"
                type="submit"
            >
                Create Account
            </button>

        </form>


        <div class="modal-foot">

            Already have an account?

            <button
                onclick="switchModal('signupModal','loginModal')"
            >
                Login
            </button>

        </div>

    </div>

</div>



<!-- ================= LOGIN MODAL ================= -->

<div class="modal" id="loginModal">

    <div class="modal-box">


        <div class="modal-head">

            Login to PGLife

        </div>


        <button
            class="close"
            onclick="closeModal('loginModal')"
        >
            &times;
        </button>


        <form
            class="modal-body"
            method="POST"
            action="auth.php"
        >
            <input type="hidden" name="action" value="login">

            <!-- Email -->

            <div class="input-row">

                <div class="icon">
                    ✉
                </div>

                <input
                    type="email"
                    name="email"
                    placeholder="Email"
                    required
                >

            </div>



            <!-- Password -->

            <div class="input-row">

                <div class="icon">
                    🔒
                </div>

                <input
                    type="password"
                    name="password"
                    placeholder="Password"
                    required
                >

            </div>



            <button
                class="submit"
                type="submit"
            >
                Login
            </button>

        </form>


        <div class="modal-foot">

            Don't have an account?

            <button
                onclick="switchModal('loginModal','signupModal')"
            >
                Signup
            </button>

        </div>

    </div>

</div>



<div class="modal" id="authMessageModal">

    <div class="modal-box">


        <div class="modal-head">

            Message

        </div>


        <button
            class="close"
            onclick="closeModal('authMessageModal')"
        >
            &times;
        </button>


        <div class="modal-body">
            <p id="authMessageText"></p>
        </div>


        <div class="modal-foot">

            <button
                class="submit"
                onclick="closeModal('authMessageModal')"
            >
                OK
            </button>

        </div>

    </div>

</div>



<script src="script.js"></script>
<script>
    const flashData = <?php echo json_encode($flash ?: []); ?>;

    document.addEventListener('DOMContentLoaded', function () {
        const authMessageText = document.getElementById('authMessageText');
        const authMessageModal = document.getElementById('authMessageModal');

        if (flashData && flashData.message && authMessageText && authMessageModal) {
            authMessageText.textContent = flashData.message;
            closeModal('loginModal');
            closeModal('signupModal');
            openModal('authMessageModal');
        }
    });
</script>

</body>
</html>
