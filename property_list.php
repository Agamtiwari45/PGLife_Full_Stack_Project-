<?php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/db.php';

$cityMap = [
    'delhi' => 'Delhi',
    'mumbai' => 'Mumbai',
    'bengaluru' => 'Bengaluru',
    'bangalore' => 'Bengaluru',
    'hyderabad' => 'Hyderabad',
];

$cityKey = strtolower(trim((string) ($_GET['city'] ?? 'mumbai')));
$cityName = $cityMap[$cityKey] ?? 'Mumbai';
$isLoggedIn = isUserLoggedIn();

$properties = [];
$cityQuery = $pdo->prepare(
    'SELECT p.id, p.name, p.address, p.gender, p.rent
     FROM properties p
     INNER JOIN cities c ON c.id = p.city_id
     WHERE LOWER(c.name) = :city_name
     ORDER BY p.rent DESC'
);
$cityQuery->execute(['city_name' => strtolower($cityName)]);
$properties = $cityQuery->fetchAll();

$propertyImages = [
    'img/properties/1/1d4f0757fdb86d5f.jpg',
    'img/properties/1/eace7b9114fd6046.jpg',
    'img/properties/1/46ebbb537aa9fb0a.jpg',
];

$propertyStarTemplates = [
    '<div class="star-container" title="4.5"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i></div>',
    '<div class="star-container" title="4.8"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>',
    '<div class="star-container" title="3.5"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i><i class="far fa-star"></i></div>',
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Best PG's in <?php echo htmlspecialchars($cityName); ?> | PG Life</title>

    <link href="css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://use.fontawesome.com/releases/v5.11.2/css/all.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,600;0,700;0,800;1,300;1,400;1,600;1,700;1,800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="css/style.css" />
    <link href="css/common.css" rel="stylesheet" />
    <link href="css/property_list.css" rel="stylesheet" />
</head>

<body>
    <header class="header">
        <a href="agam.php" class="logo_link">
            <img src="img/logo.png" alt="PG Life logo" class="logo-image" />
        </a>

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

    <div id="loading">
    </div>

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb py-2">
            <li class="breadcrumb-item">
                <a href="agam.php">Home</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page" id="breadcrumb-city">
                <?php echo htmlspecialchars($cityName); ?>
            </li>
        </ol>
    </nav>

    <div class="page-container">
        <div class="filter-bar row justify-content-around">
            <div id="filter-trigger" class="col-auto" data-toggle="modal" data-target="#filter-modal">
                <img src="img/filter.png" alt="filter" />
                <span>Filter</span>
            </div>
            <div class="col-auto sort-option" data-sort="desc">
                <img src="img/desc.png" alt="sort-desc" />
                <span>Highest rent first</span>
            </div>
            <div class="col-auto sort-option" data-sort="asc">
                <img src="img/asc.png" alt="sort-asc" />
                <span>Lowest rent first</span>
            </div>
        </div>

        <?php if (!empty($properties)): ?>
            <?php foreach ($properties as $index => $property): ?>
                <?php
                $genderKey = strtolower($property['gender'] ?? 'male');
                $genderLogo = 'img/' . $genderKey . '.png';
                $rentValue = (float) ($property['rent'] ?? 0);
                $formattedRent = 'Rs ' . number_format($rentValue, 0, '.', ',') . '/-';
                $propertyId = (int) ($property['id'] ?? ($index + 1));
                $ratingMarkup = $propertyStarTemplates[$index % count($propertyStarTemplates)];
                $imagePath = $propertyImages[$index % count($propertyImages)] ?? $propertyImages[0];
                ?>
                <div class="property-card row" data-gender="<?php echo htmlspecialchars($genderKey); ?>" data-rent="<?php echo (int) round($rentValue); ?>">
                    <div class="image-container col-md-4">
                        <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($property['name']); ?>" />
                    </div>
                    <div class="content-container col-md-8">
                        <div class="row no-gutters justify-content-between">
                            <?php echo $ratingMarkup; ?>
                            <div class="interested-container" data-property-id="<?php echo htmlspecialchars((string) $propertyId); ?>">
                                <i class="interested-icon far fa-heart"></i>
                            </div>
                        </div>
                        <div class="detail-container">
                            <div class="property-name"><?php echo htmlspecialchars($property['name']); ?></div>
                            <div class="property-address"><?php echo htmlspecialchars($property['address']); ?></div>
                            <div class="property-gender">
                                <img src="<?php echo htmlspecialchars($genderLogo); ?>" alt="<?php echo htmlspecialchars($property['gender']); ?>" />
                            </div>
                        </div>
                        <div class="row no-gutters">
                            <div class="rent-container col-6">
                                <div class="rent"><?php echo htmlspecialchars($formattedRent); ?></div>
                                <div class="rent-unit">per month</div>
                            </div>
                            <div class="button-container col-6">
                                <a href="property_detail.php?city=<?php echo urlencode($cityKey); ?>&property_id=<?php echo (int) $propertyId; ?>" class="btn btn-primary">View</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <div class="no-property-container" style="display:none;">
            <p>No properties match the selected filter.</p>
        </div>
    </div>

    <div class="modal fade" id="filter-modal" tabindex="-1" role="dialog" aria-labelledby="filter-heading" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" id="filter-heading">Filters</h3>
                </div>

                <div class="modal-body">
                    <h5>Gender</h5>
                    <hr />
                    <div>
                        <button type="button" class="btn btn-outline-dark btn-active" data-filter="all">
                            No Filter
                        </button>
                        <button type="button" class="btn btn-outline-dark" data-filter="unisex">
                            <i class="fas fa-venus-mars"></i>Unisex
                        </button>
                        <button type="button" class="btn btn-outline-dark" data-filter="male">
                            <i class="fas fa-mars"></i>Male
                        </button>
                        <button type="button" class="btn btn-outline-dark" data-filter="female">
                            <i class="fas fa-venus"></i>Female
                        </button>
                    </div>
                </div>

                <div class="modal-footer">
                    <button data-dismiss="modal" class="btn btn-success">Okay</button>
                </div>
            </div>
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
                    <a href="property_list.php?city=Delhi">PG in Delhi</a>
                </div>
                <div class="footer-city">
                    <a href="property_list.php?city=Mumbai">PG in Mumbai</a>
                </div>
                <div class="footer-city">
                    <a href="property_list.php?city=Bengaluru">PG in Bangalore</a>
                </div>
                <div class="footer-city">
                    <a href="property_list.php?city=Hyderabad">PG in Hyderabad</a>
                </div>
            </div>
            <div class="footer-copyright">© 2020 Copyright PG Life </div>
        </div>
    </div>

    <script src="script.js"></script>
    <script>
        const flashData = {};

        document.addEventListener('DOMContentLoaded', function () {
            const authMessageText = document.getElementById('authMessageText');
            const authMessageModal = document.getElementById('authMessageModal');

            if (authMessageText && authMessageModal && flashData && flashData.message) {
                authMessageText.textContent = flashData.message;
                closeModal('loginModal');
                closeModal('signupModal');
                openModal('authMessageModal');
            }
        });

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

        const pageTitle = document.querySelector('title');
        if (pageTitle) {
            pageTitle.textContent = "Best PG's in " + cityName + " | PG Life";
        }

        const breadcrumbCity = document.getElementById('breadcrumb-city');
        if (breadcrumbCity) {
            breadcrumbCity.textContent = cityName;
        }

        const viewLinks = document.querySelectorAll('a.btn.btn-primary[href*="property_detail.php"]');
        viewLinks.forEach(function (link) {
            const href = new URL(link.getAttribute('href'), window.location.origin);

            if (!href.searchParams.has('property_id')) {
                href.searchParams.set('city', cityKey.toLowerCase());
                link.setAttribute('href', href.pathname + '?' + href.searchParams.toString());
            }
        });

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

        const filterTrigger = document.getElementById('filter-trigger');
        const filterModal = document.getElementById('filter-modal');

        if (filterTrigger && filterModal) {
            filterTrigger.addEventListener('click', function (event) {
                event.preventDefault();

                if (window.bootstrap && window.bootstrap.Modal) {
                    const modal = new bootstrap.Modal(filterModal);
                    modal.show();
                } else if (window.jQuery) {
                    jQuery(filterModal).modal('show');
                } else {
                    filterModal.classList.add('show');
                    filterModal.style.display = 'block';
                    document.body.classList.add('modal-open');
                }
            });
        }

        const propertyList = document.querySelector('.page-container');
        const propertyCards = Array.from(document.querySelectorAll('.property-card'));
        const filterButtons = document.querySelectorAll('#filter-modal [data-filter]');
        const sortOptions = document.querySelectorAll('.sort-option');
        const noPropertyContainer = document.querySelector('.no-property-container');
        const modalOkayButton = document.querySelector('#filter-modal .modal-footer .btn-success');
        let selectedFilter = 'all';
        let activeSort = 'desc';

        function applyPropertyFilter(filterValue) {
            let visibleCounter = 0;

            propertyCards.forEach(function (card) {
                const cardGender = card.getAttribute('data-gender');
                const shouldDisplay = filterValue === 'all' || cardGender === filterValue;

                card.style.display = shouldDisplay ? '' : 'none';

                if (shouldDisplay) {
                    visibleCounter += 1;
                }
            });

            if (noPropertyContainer) {
                noPropertyContainer.style.display = visibleCounter === 0 ? 'block' : 'none';
            }
        }

        function updateFilterButtons(filterValue) {
            filterButtons.forEach(function (button) {
                const isActive = button.getAttribute('data-filter') === filterValue;
                button.classList.toggle('btn-active', isActive);
                button.classList.toggle('btn-outline-dark', !isActive);
                button.classList.toggle('btn-dark', isActive);
            });
        }

        function updateSortButtons(sortValue) {
            sortOptions.forEach(function (option) {
                const isActive = option.getAttribute('data-sort') === sortValue;
                option.classList.toggle('font-weight-bold', isActive);
                option.style.opacity = isActive ? '1' : '0.75';
            });
        }

        function applySort(sortValue) {
            activeSort = sortValue;

            const sortedCards = propertyCards.slice().sort(function (cardA, cardB) {
                const rentA = parseInt(cardA.getAttribute('data-rent') || '0', 10);
                const rentB = parseInt(cardB.getAttribute('data-rent') || '0', 10);

                return sortValue === 'asc' ? rentA - rentB : rentB - rentA;
            });

            if (propertyList && noPropertyContainer) {
                sortedCards.forEach(function (card) {
                    card.remove();
                });

                sortedCards.forEach(function (card) {
                    propertyList.insertBefore(card, noPropertyContainer);
                });
            }

            updateSortButtons(activeSort);
        }

        filterButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                selectedFilter = button.getAttribute('data-filter');
                updateFilterButtons(selectedFilter);
            });
        });

        sortOptions.forEach(function (option) {
            option.addEventListener('click', function () {
                applySort(option.getAttribute('data-sort'));
            });
        });

        if (modalOkayButton) {
            modalOkayButton.addEventListener('click', function () {
                applyPropertyFilter(selectedFilter);

                if (window.bootstrap && window.bootstrap.Modal) {
                    const modalInstance = bootstrap.Modal.getInstance(filterModal) || new bootstrap.Modal(filterModal);
                    modalInstance.hide();
                } else if (window.jQuery) {
                    jQuery(filterModal).modal('hide');
                } else {
                    filterModal.classList.remove('show');
                    filterModal.style.display = 'none';
                    document.body.classList.remove('modal-open');
                }
            });
        }

        updateFilterButtons(selectedFilter);
        updateSortButtons(activeSort);
        applySort(activeSort);
        applyPropertyFilter(selectedFilter);

        function showLoginModal() {
            const loginModal = document.getElementById('loginModal') || document.getElementById('login-modal');
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
                    if (!data || !data.properties) {
                        return;
                    }

                    data.properties.forEach(function (property) {
                        const container = document.querySelector('.interested-container[data-property-id="' + property.property_id + '"]');
                        if (!container) {
                            return;
                        }

                        const icon = container.querySelector('.interested-icon');
                        if (icon) {
                            icon.classList.toggle('fas', property.interested);
                            icon.classList.toggle('far', !property.interested);
                            icon.classList.toggle('is-active', property.interested);
                        }
                    });
                })
                .catch(function (error) {
                    console.error('Could not load interest state:', error);
                });
        }

        document.addEventListener('click', function (event) {
            const interestedContainer = event.target.closest('.interested-container');
            if (!interestedContainer) {
                return;
            }

            const propertyId = interestedContainer.getAttribute('data-property-id');
            if (!propertyId) {
                return;
            }

            fetch('toggle_interested.php?action=toggle', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                },
                body: 'property_id=' + encodeURIComponent(propertyId)
            })
                .then(function (response) {
                    return response.json();
                })
                .then(function (data) {
                    if (data && data.require_login) {
                        showLoginModal();
                        return;
                    }

                    if (data && data.success && data.property_id) {
                        const icon = interestedContainer.querySelector('.interested-icon');
                        if (icon) {
                            icon.classList.toggle('fas', data.interested);
                            icon.classList.toggle('far', !data.interested);
                            icon.classList.toggle('is-active', data.interested);
                        }
                    }
                })
                .catch(function (error) {
                    console.error('Interest toggle failed:', error);
                });
        });

        loadInterestState();
    </script>
</body>
</html>
