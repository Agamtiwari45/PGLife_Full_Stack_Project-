<?php
    require_once __DIR__ . '/session.php';
    require_once __DIR__ . '/db.php';

    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);

    $cityMap = [
        'delhi' => 'Delhi',
        'mumbai' => 'Mumbai',
        'bengaluru' => 'Bengaluru',
        'bangalore' => 'Bengaluru',
        'hyderabad' => 'Hyderabad',
    ];

    $cityKey = strtolower(trim((string) ($_GET['city'] ?? 'mumbai')));
    $propertyId = filter_input(INPUT_GET, 'property_id', FILTER_VALIDATE_INT);
    $isLoggedIn = isUserLoggedIn();
    $cityName = $cityMap[$cityKey] ?? 'Mumbai';
    $property = null;
    $propertyError = null;

    if ($propertyId === false || $propertyId === null || $propertyId <= 0) {
        if (!empty($cityKey)) {
            $fallbackQuery = $pdo->prepare(
                'SELECT p.*, c.name AS city_name
                 FROM properties p
                 INNER JOIN cities c ON c.id = p.city_id
                 WHERE LOWER(c.name) = :city_name
                 ORDER BY p.id ASC
                 LIMIT 1'
            );
            $fallbackQuery->execute(['city_name' => strtolower($cityName)]);
            $fallbackProperty = $fallbackQuery->fetch();

            if ($fallbackProperty) {
                header('Location: property_detail.php?city=' . urlencode($cityKey) . '&property_id=' . (int) $fallbackProperty['id']);
                exit;
            }
        }

        $propertyError = 'The requested property could not be found.';
    } else {
        $propertyQuery = $pdo->prepare(
            'SELECT p.*, c.name AS city_name
             FROM properties p
             LEFT JOIN cities c ON c.id = p.city_id
             WHERE p.id = :property_id
             LIMIT 1'
        );
        $propertyQuery->execute(['property_id' => $propertyId]);
        $property = $propertyQuery->fetch();

        if ($property) {
            $cityKey = strtolower(trim((string) ($property['city_name'] ?? $cityKey)));
            $cityName = $cityMap[$cityKey] ?? (string) ($property['city_name'] ?? 'Mumbai');
        } else {
            $propertyError = 'The requested property could not be found.';
        }
    }

    $propertyId = (int) ($property['id'] ?? $propertyId ?? 0);
    $propertyName = $property['name'] ?? 'Property Details';
    $propertyAddress = $property['address'] ?? 'Address unavailable';
    $propertyRent = isset($property['rent']) ? (float) $property['rent'] : 0;
    $propertyGender = strtolower((string) ($property['gender'] ?? 'unisex'));
    $propertyRentLabel = 'Rs ' . number_format($propertyRent, 0, '.', ',') . '/-';
    $genderImage = 'img/unisex.png';

    if ($propertyGender === 'male') {
        $genderImage = 'img/male.png';
    } elseif ($propertyGender === 'female') {
        $genderImage = 'img/female.png';
    }

    $propertyImages = [
        'img/properties/1/1d4f0757fdb86d5f.jpg',
        'img/properties/1/46ebbb537aa9fb0a.jpg',
        'img/properties/1/eace7b9114fd6046.jpg',
    ];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($propertyName); ?> | PG Life</title>

    <link href="css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://use.fontawesome.com/releases/v5.11.2/css/all.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,600;0,700;0,800;1,300;1,400;1,600;1,700;1,800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="css/style.css" />
    <link href="css/common.css" rel="stylesheet" />
    <link href="css/property_detail.css" rel="stylesheet" />
</head>

<body>
    <div class="header sticky-top">
        <nav class="navbar navbar-expand-md navbar-light">
            <a class="navbar-brand" href="agam.php">
                <img src="img/logo.png" />
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#my-navbar">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-end" id="my-navbar">
                <ul class="navbar-nav">
                    <?php if ($isLoggedIn): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a>
                        </li>
                        <div class="nav-vl"></div>
                        <li class="nav-item">
                            <span class="nav-link"><i class="fas fa-user"></i>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                        </li>
                        <div class="nav-vl"></div>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <button type="button" class="btn btn-link nav-link" onclick="openModal('signupModal')">
                                <i class="fas fa-user"></i>Signup
                            </button>
                        </li>
                        <div class="nav-vl"></div>
                        <li class="nav-item">
                            <button type="button" class="btn btn-link nav-link" onclick="openModal('loginModal')">
                                <i class="fas fa-sign-in-alt"></i>Login
                            </button>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>
    </div>

    <div id="loading">
    </div>

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb py-2">
            <li class="breadcrumb-item">
                <a href="agam.php">Home</a>
            </li>
            <li class="breadcrumb-item">
                <a href="property_list.php?city=<?php echo urlencode($cityKey); ?>" id="breadcrumb-city-link"><?php echo htmlspecialchars($cityName); ?></a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                <?php echo htmlspecialchars($propertyName); ?>
            </li>
        </ol>
    </nav>

    <?php if ($propertyError): ?>
        <div class="page-container py-4">
            <div class="alert alert-warning" role="alert">
                <?php echo htmlspecialchars($propertyError); ?>
            </div>
        </div>
    <?php else: ?>
        <div id="property-images" class="carousel slide" data-ride="carousel">
            <ol class="carousel-indicators">
                <?php foreach ($propertyImages as $imageIndex => $imagePath): ?>
                    <li data-target="#property-images" data-slide-to="<?php echo $imageIndex; ?>" class="<?php echo $imageIndex === 0 ? 'active' : ''; ?>"></li>
                <?php endforeach; ?>
            </ol>
            <div class="carousel-inner">
                <?php foreach ($propertyImages as $imageIndex => $imagePath): ?>
                    <div class="carousel-item <?php echo $imageIndex === 0 ? 'active' : ''; ?>">
                        <img class="d-block w-100" src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($propertyName); ?>">
                    </div>
                <?php endforeach; ?>
            </div>
            <a class="carousel-control-prev" href="#property-images" role="button" data-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="sr-only">Previous</span>
            </a>
            <a class="carousel-control-next" href="#property-images" role="button" data-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="sr-only">Next</span>
            </a>
        </div>

        <div class="property-summary page-container">
            <div class="row no-gutters justify-content-between">
                <div class="star-container" title="4.8">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <div class="interested-container" data-property-id="<?php echo htmlspecialchars((string) $propertyId); ?>">
                    <i class="is-interested-image interested-icon far fa-heart"></i>
                </div>
            </div>
            <div class="detail-container">
                <div class="property-name"><?php echo htmlspecialchars($propertyName); ?></div>
                <div class="property-address"><?php echo htmlspecialchars($propertyAddress); ?></div>
                <div class="property-gender">
                    <img src="<?php echo htmlspecialchars($genderImage); ?>" alt="<?php echo htmlspecialchars($propertyGender); ?>" />
                </div>
            </div>
            <div class="row no-gutters">
                <div class="rent-container col-6">
                    <div class="rent"><?php echo htmlspecialchars($propertyRentLabel); ?></div>
                    <div class="rent-unit">per month</div>
                </div>
                <div class="button-container col-6">
                    <button type="button" class="btn btn-primary" id="book-now-button">Book Now</button>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="property-amenities">
        <div class="page-container">
            <h1>Amenities</h1>
            <div class="row justify-content-between">
                <div class="col-md-auto">
                    <h5>Building</h5>
                    <div class="amenity-container">
                        <img src="img/amenities/powerbackup.svg">
                        <span>Power backup</span>
                    </div>
                    <div class="amenity-container">
                        <img src="img/amenities/lift.svg">
                        <span>Lift</span>
                    </div>
                </div>

                <div class="col-md-auto">
                    <h5>Common Area</h5>
                    <div class="amenity-container">
                        <img src="img/amenities/wifi.svg">
                        <span>Wifi</span>
                    </div>
                    <div class="amenity-container">
                        <img src="img/amenities/tv.svg">
                        <span>TV</span>
                    </div>
                    <div class="amenity-container">
                        <img src="img/amenities/rowater.svg">
                        <span>Water Purifier</span>
                    </div>
                    <div class="amenity-container">
                        <img src="img/amenities/dining.svg">
                        <span>Dining</span>
                    </div>
                    <div class="amenity-container">
                        <img src="img/amenities/washingmachine.svg">
                        <span>Washing Machine</span>
                    </div>
                </div>

                <div class="col-md-auto">
                    <h5>Bedroom</h5>
                    <div class="amenity-container">
                        <img src="img/amenities/bed.svg">
                        <span>Bed with Matress</span>
                    </div>
                    <div class="amenity-container">
                        <img src="img/amenities/ac.svg">
                        <span>Air Conditioner</span>
                    </div>
                </div>

                <div class="col-md-auto">
                    <h5>Washroom</h5>
                    <div class="amenity-container">
                        <img src="img/amenities/geyser.svg">
                        <span>Geyser</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="property-about page-container">
        <h1>About the Property</h1>
        <p>Furnished studio apartment - share it with close friends! Located in posh area of Bijwasan in Delhi, this house is available for both boys and girls. Go for a private room or opt for a shared one and make it your own abode. Go out with your new friends - catch a movie at the nearest cinema hall or just chill in a cafe which is not even 2 kms away. Unwind with your flatmates after a long day at work/college. With a common living area and a shared kitchen, make your own FRIENDS moments. After all, there's always a Joey with unlimited supply of food. Remember, all it needs is one crazy story to convert a roomie into a BFF. What's nearby/Your New Neighborhood 4.0 Kms from Dwarka Sector- 21 Metro Station.</p>
    </div>

    <div class="property-rating">
        <div class="page-container">
            <h1>Property Rating</h1>
            <div class="row align-items-center justify-content-between">
                <div class="col-md-6">
                    <div class="rating-criteria row">
                        <div class="col-6">
                            <i class="rating-criteria-icon fas fa-broom"></i>
                            <span class="rating-criteria-text">Cleanliness</span>
                        </div>
                        <div class="rating-criteria-star-container col-6" title="4.3">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                    </div>

                    <div class="rating-criteria row">
                        <div class="col-6">
                            <i class="rating-criteria-icon fas fa-utensils"></i>
                            <span class="rating-criteria-text">Food Quality</span>
                        </div>
                        <div class="rating-criteria-star-container col-6" title="3.4">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                            <i class="far fa-star"></i>
                        </div>
                    </div>

                    <div class="rating-criteria row">
                        <div class="col-6">
                            <i class="rating-criteria-icon fa fa-lock"></i>
                            <span class="rating-criteria-text">Safety</span>
                        </div>
                        <div class="rating-criteria-star-container col-6" title="4.8">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="rating-circle">
                        <div class="total-rating">4.2</div>
                        <div class="rating-circle-star-container">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="far fa-star"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="property-testimonials page-container">
        <h1>What people say</h1>
        <div class="testimonial-block">
            <div class="testimonial-image-container">
                <img class="testimonial-img" src="img/man.png">
            </div>
            <div class="testimonial-text">
                <i class="fa fa-quote-left" aria-hidden="true"></i>
                <p>You just have to arrive at the place, it's fully furnished and stocked with all basic amenities and services and even your friends are welcome.</p>
            </div>
            <div class="testimonial-name">- Ashutosh Gowariker</div>
        </div>
        <div class="testimonial-block">
            <div class="testimonial-image-container">
                <img class="testimonial-img" src="img/man.png">
            </div>
            <div class="testimonial-text">
                <i class="fa fa-quote-left" aria-hidden="true"></i>
                <p>You just have to arrive at the place, it's fully furnished and stocked with all basic amenities and services and even your friends are welcome.</p>
            </div>
            <div class="testimonial-name">- Karan Johar</div>
        </div>
    </div>

    <div class="modal" id="signupModal">
        <div class="modal-box">
            <div class="modal-head">Signup with PGLife</div>
            <button class="close" onclick="closeModal('signupModal')">&times;</button>

            <form class="modal-body" method="POST" action="auth.php">
                <input type="hidden" name="action" value="signup">

                <div class="input-row">
                    <div class="icon">👤</div>
                    <input type="text" name="full_name" placeholder="Full Name" required>
                </div>

                <div class="input-row">
                    <div class="icon">☎</div>
                    <input type="tel" name="phone" placeholder="Phone Number" required>
                </div>

                <div class="input-row">
                    <div class="icon">✉</div>
                    <input type="email" name="email" placeholder="Email" required>
                </div>

                <div class="input-row">
                    <div class="icon">🔒</div>
                    <input type="password" name="password" placeholder="Password" required>
                </div>

                <div class="input-row">
                    <div class="icon">🏫</div>
                    <input type="text" name="college_name" placeholder="College Name" required>
                </div>

                <div class="gender">
                    I'm a
                    <label>
                        <input type="radio" name="gender" value="male" required> Male
                    </label>
                    <label>
                        <input type="radio" name="gender" value="female"> Female
                    </label>
                </div>

                <button class="submit" type="submit">Create Account</button>
            </form>

            <div class="modal-foot">
                Already have an account?
                <button onclick="switchModal('signupModal','loginModal')">Login</button>
            </div>
        </div>
    </div>

    <div class="modal" id="loginModal">
        <div class="modal-box">
            <div class="modal-head">Login to PGLife</div>
            <button class="close" onclick="closeModal('loginModal')">&times;</button>

            <form class="modal-body" method="POST" action="auth.php">
                <input type="hidden" name="action" value="login">

                <div class="input-row">
                    <div class="icon">✉</div>
                    <input type="email" name="email" placeholder="Email" required>
                </div>

                <div class="input-row">
                    <div class="icon">🔒</div>
                    <input type="password" name="password" placeholder="Password" required>
                </div>

                <button class="submit" type="submit">Login</button>
            </form>

            <div class="modal-foot">
                Don't have an account?
                <button onclick="switchModal('loginModal','signupModal')">Signup</button>
            </div>
        </div>
    </div>

    <div class="modal" id="authMessageModal">
        <div class="modal-box">
            <div class="modal-head">Message</div>
            <button class="close" onclick="closeModal('authMessageModal')">&times;</button>

            <div class="modal-body">
                <p id="authMessageText"></p>
            </div>

            <div class="modal-foot">
                <button class="submit" onclick="closeModal('authMessageModal')">OK</button>
            </div>
        </div>
    </div>

    <div class="footer">
        <div class="page-container footer-container">
            <div class="footer-cities">
                <div class="footer-city">
                    <a href="property_list.html">PG in Delhi</a>
                </div>
                <div class="footer-city">
                    <a href="property_list.html">PG in Mumbai</a>
                </div>
                <div class="footer-city">
                    <a href="property_list.html">PG in Bangalore</a>
                </div>
                <div class="footer-city">
                    <a href="property_list.html">PG in Hyderabad</a>
                </div>
            </div>
            <div class="footer-copyright">© 2020 Copyright PG Life </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.12.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
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

        (function () {
            const carousel = document.getElementById('property-images');
            if (!carousel) return;

            const slides = Array.from(carousel.querySelectorAll('.carousel-item'));
            const indicators = Array.from(carousel.querySelectorAll('.carousel-indicators li'));
            const prevBtn = carousel.parentElement.querySelector('.carousel-control-prev');
            const nextBtn = carousel.parentElement.querySelector('.carousel-control-next');

            if (!slides.length) return;

            let currentIndex = slides.findIndex(function (slide) {
                return slide.classList.contains('active');
            });

            if (currentIndex === -1) {
                currentIndex = 0;
                slides[0].classList.add('active');
            }

            function updateCarousel(nextIndex) {
                slides.forEach(function (slide, index) {
                    slide.classList.toggle('active', index === nextIndex);
                });

                indicators.forEach(function (indicator, index) {
                    indicator.classList.toggle('active', index === nextIndex);
                });

                currentIndex = nextIndex;
            }

            if (prevBtn) {
                prevBtn.addEventListener('click', function (event) {
                    event.preventDefault();
                    const nextIndex = (currentIndex - 1 + slides.length) % slides.length;
                    updateCarousel(nextIndex);
                });
            }

            if (nextBtn) {
                nextBtn.addEventListener('click', function (event) {
                    event.preventDefault();
                    const nextIndex = (currentIndex + 1) % slides.length;
                    updateCarousel(nextIndex);
                });
            }

            indicators.forEach(function (indicator, index) {
                indicator.addEventListener('click', function () {
                    updateCarousel(index);
                });
            });

            setInterval(function () {
                const nextIndex = (currentIndex + 1) % slides.length;
                updateCarousel(nextIndex);
            }, 3000);
        })();

        const cityMap = {
            delhi: 'Delhi',
            mumbai: 'Mumbai',
            bengaluru: 'Bengaluru',
            bangalore: 'Bengaluru',
            hyderabad: 'Hyderabad'
        };

        const params = new URLSearchParams(window.location.search);
        const cityKey = params.get('city') || 'mumbai';
        const cityName = cityMap[cityKey.toLowerCase()] || 'Mumbai';

        const breadcrumbCityLink = document.getElementById('breadcrumb-city-link');
        if (breadcrumbCityLink) {
            breadcrumbCityLink.textContent = cityName;
            breadcrumbCityLink.setAttribute('href', 'property_list.php?city=' + cityKey.toLowerCase());
        }

        const footerLinks = document.querySelectorAll('.footer-city a');
        footerLinks.forEach(function (link) {
            const text = link.textContent.trim().toLowerCase();
            if (text.includes('delhi')) {
                link.setAttribute('href', 'property_list.php?city=delhi');
            } else if (text.includes('mumbai')) {
                link.setAttribute('href', 'property_list.php?city=mumbai');
            } else if (text.includes('bangalore') || text.includes('bengaluru')) {
                link.setAttribute('href', 'property_list.php?city=bengaluru');
            } else if (text.includes('hyderabad')) {
                link.setAttribute('href', 'property_list.php?city=hyderabad');
            }
        });

        function showLoginModal() {
            const loginModal = document.getElementById('loginModal');
            if (!loginModal) {
                return;
            }

            if (window.bootstrap && window.bootstrap.Modal) {
                const modalInstance = bootstrap.Modal.getInstance(loginModal) || new bootstrap.Modal(loginModal);
                modalInstance.show();
            } else if (window.jQuery) {
                jQuery(loginModal).modal('show');
            } else {
                loginModal.classList.add('show');
                loginModal.style.display = 'block';
                document.body.classList.add('modal-open');
            }
        }

        function showSignupModal() {
            const signupModal = document.getElementById('signupModal');
            if (!signupModal) {
                return;
            }

            if (window.bootstrap && window.bootstrap.Modal) {
                const modalInstance = bootstrap.Modal.getInstance(signupModal) || new bootstrap.Modal(signupModal);
                modalInstance.show();
            } else if (window.jQuery) {
                jQuery(signupModal).modal('show');
            } else {
                signupModal.classList.add('show');
                signupModal.style.display = 'block';
                document.body.classList.add('modal-open');
            }
        }

        function updateInterestedDisplay(container, isInterested, count) {
            const icon = container.querySelector('.interested-icon, .is-interested-image');
            if (icon) {
                icon.classList.toggle('fas', isInterested);
                icon.classList.toggle('far', !isInterested);
                icon.classList.toggle('is-active', isInterested);
            }

            const countNode = container.querySelector('.interested-user-count');
            if (countNode) {
                countNode.textContent = count;
                return;
            }

            const textNode = container.querySelector('.interested-text');
            if (textNode) {
                textNode.textContent = count + ' interested';
            }
        }

        function loadInterestState() {
            const containers = Array.from(document.querySelectorAll('.interested-container[data-property-id]'));
            const propertyIds = containers
                .map(function (container) {
                    return container.getAttribute('data-property-id');
                })
                .filter(function (propertyId) {
                    return propertyId;
                })
                .join(',');

            if (!propertyIds) {
                return;
            }

            fetch('toggle_interested.php?action=load&property_ids=' + encodeURIComponent(propertyIds))
                .then(function (response) {
                    return response.json();
                })
                .then(function (data) {
                    if (!data || !data.success || !Array.isArray(data.properties)) {
                        return;
                    }

                    const propertiesMap = new Map(
                        data.properties.map(function (property) {
                            return [String(property.property_id), property];
                        })
                    );

                    containers.forEach(function (container) {
                        const propertyId = container.getAttribute('data-property-id');
                        const propertyData = propertiesMap.get(String(propertyId));

                        if (!propertyData) {
                            return;
                        }

                        updateInterestedDisplay(container, Boolean(propertyData.interested), Number(propertyData.count || 0));
                    });
                })
                .catch(function () {
                    return;
                });
        }

        document.addEventListener('click', function (event) {
            const container = event.target.closest('.interested-container');

            if (!container) {
                return;
            }

            const propertyId = container.getAttribute('data-property-id');
            if (!propertyId) {
                return;
            }

            event.preventDefault();

            const formData = new URLSearchParams();
            formData.append('action', 'toggle');
            formData.append('property_id', propertyId);

            fetch('toggle_interested.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                },
                body: formData.toString()
            })
                .then(function (response) {
                    return response.json();
                })
                .then(function (data) {
                    if (data && data.require_login) {
                        showLoginModal();
                        return;
                    }

                    if (!data || !data.success) {
                        alert(data && data.message ? data.message : 'Unable to update interest.');
                        return;
                    }

                    updateInterestedDisplay(container, Boolean(data.interested), Number(data.count || 0));
                })
                .catch(function () {
                    alert('Unable to update interest right now.');
                });
        });

        const bookNowButton = document.getElementById('book-now-button');

        if (bookNowButton) {
            bookNowButton.addEventListener('click', function () {
                fetch('property_interest.php?action=check_login')
                    .then(function (response) {
                        return response.json();
                    })
                    .then(function (data) {
                        if (!data || !data.loggedIn) {
                            showLoginModal();
                            return;
                        }

                        const propertyName = document.querySelector('.property-name');
                        alert('Booking request sent for ' + (propertyName ? propertyName.textContent.trim() : 'this property') + '.');
                    })
                    .catch(function () {
                        alert('Unable to process booking right now.');
                    });
            });
        }

        loadInterestState();
    </script>
</body>

</html>
