<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ARENXFAMILY - Проект</title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <style>
        .error-text { color: #ff4d4d; font-size: 0.85em; margin-top: 5px; text-align: left; display: block; }
        .success-alert { background: rgba(46, 204, 113, 0.2); border: 1px solid #2ecc71; padding: 15px; border-radius: 5px; color: #fff; margin-bottom: 20px; text-align: left; }
        .success-alert code { background: #222; padding: 2px 6px; border-radius: 3px; color: #2ecc71; }
    </style>
</head>
<body data-user-id="<?= $_SESSION['user_id'] ?? '' ?>">

    <div class="main-wrapper">
        <header class="header">
            <div class="header-left">
                <a href="/fullstack-webproject/"><img class="logo" src="img/logo2.svg"></a>
            </div>
	    <nav class="main-nav">
                <ul id="menuList">
                    <li><a href="#section1">Коттеджи</a></li>
                    <li><a href="#section2">О нас</a></li>
                    
                    <?php if (!empty($_SESSION['user_id'])): ?>
                        <li><a href="#section3"><?php echo htmlspecialchars($_SESSION['login'] ?? 'Мой профиль'); ?></a></li>
                        <li><a href="/fullstack-webproject/modules/logout.php" style="color: #ff4d4d;">Выйти</a></li>
                    <?php else: ?>
                        <li><a href="/fullstack-webproject/modules/login.php">Войти</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            <div class="burger-menu" id="burgerBtn">
                <span></span><span></span><span></span>
            </div>
        </header>
        
        <div class="hero-block">
            <div class="hero-content">
                <h1 class="hero-title">ЭЛИТНЫЕ РЕШЕНИЯ<br>ПО ПРИЯТНЫМ<br>ЦЕНАМ</h1>
                <a href="#section1" class="btn">ПЕРЕЙТИ</a>
            </div>
        </div>

        <section class="houses-section"> 
            <h2 class="section-title" id="section1">КАКИЕ ДОМА МЫ <br> СТРОИМ?</h2>
            <div class="houses-grid">
                <div class="house-card card-overlay" style="background-image: url('img/homesky.jpeg');">
                    <div class="card-content">
                        <div class="card-text-row">
                            <h3>КОТТЕДЖИ</h3>
                            <span class="count">152шт</span>
                        </div>
                        <a href="#" class="btn-card">СМОТРЕТЬ</a>
                    </div>
                </div>
                <div class="house-card" style="background-image: url('img/home2sky.jpeg');"></div>
                <div class="house-card" style="background-image: url('img/home3sky.jpeg');"></div>
            </div>
        </section>

        <section class="selection-section" id="section2">
            <div class="slider-wrapper">
                <div class="cottage-slider">
                    <div class="slide-item"><div class="slide-img" style="background-image: url('img/slide1.jpg');"></div></div>
                    <div class="slide-item"><div class="slide-img" style="background-image: url('img/slide2.jpg');"></div></div>
                    <div class="slide-item"><div class="slide-img" style="background-image: url('img/slide3.jpg');"></div></div>
                </div>
                <div class="slider-controls">
                    <button class="slider-arrow prev-slide">
                        <svg width="10" height="16" viewBox="0 0 10 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8.5 1L1.5 8L8.5 15" stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                    <div class="slider-counter">
                        <span class="current">01</span>/<span class="total">06</span>
                    </div>
                    <button class="slider-arrow next-slide">
                        <svg width="10" height="16" viewBox="0 0 10 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1.5 1L8.5 8L1.5 15" stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                </div>
            </div>
            <div class="info-content">
                <h2 class="info-title">ВЫБЕРИ <br> КОТТЕДЖ МЕЧТЫ</h2>
                <p class="info-text">ARENXFAMILY - компания, занимающаяся строительством элитного жилья.</p>
                <div class="stats-row">
                    <div class="stat-item"><div class="stat-circle">12</div><span class="stat-desc">Лет в строительстве</span></div>
                    <div class="stat-item"><div class="stat-circle">1000+</div><span class="stat-desc">Довольных клиентов</span></div>
                </div>
            </div>
        </section>

        <section class="contact-section" id="section3">
            <div class="contact-container">
                <h2 class="contact-title">ОСТАЛИСЬ ВОПРОСЫ?</h2>

                <form id="contactForm" action="form-fallback" method="POST">
                    
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="_gotcha">

                    <div id="statusMessage" class="status">
                        <?php if ($c['success']): ?>
                            <div class="success-alert">
                                <p style="color: #2ecc71; font-weight: bold; margin-bottom: 10px;">Заявка успешно отправлена!</p>
                                <?php if ($c['gen_login'] && $c['gen_pass']): ?>
                                    <p>Для вас автоматически создан аккаунт:</p>
                                    <p>Логин: <code><?php echo htmlspecialchars($c['gen_login']); ?></code></p>
                                    <p>Пароль: <code><?php echo htmlspecialchars($c['gen_pass']); ?></code></p>
                                <?php else: ?>
                                    <p>Данные вашего профиля успешно обновлены.</p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="input-group">
                        <input type="text" id="fullName" name="fullName" placeholder="Ваше имя" required 
                               value="<?php echo htmlspecialchars($c['values']['fullName'] ?? ''); ?>">
                        <?php if (!empty($c['errors']['fullName'])): ?>
                            <span class="error-text"><?php echo $c['errors']['fullName']; ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="input-group">
                        <input type="tel" id="phone" name="phone" placeholder="Телефон" required 
                               value="<?php echo htmlspecialchars($c['values']['phone'] ?? ''); ?>">
                        <?php if (!empty($c['errors']['phone'])): ?>
                            <span class="error-text"><?php echo $c['errors']['phone']; ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="input-group">
                        <input type="email" id="email" name="email" placeholder="E-mail" required 
                               value="<?php echo htmlspecialchars($c['values']['email'] ?? ''); ?>">
                        <?php if (!empty($c['errors']['email'])): ?>
                            <span class="error-text"><?php echo $c['errors']['email']; ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="input-group">
                        <textarea id="message" name="message" rows="4" placeholder="Ваш комментарий" required><?php echo htmlspecialchars($c['values']['message'] ?? ''); ?></textarea>
                        <?php if (!empty($c['errors']['message'])): ?>
                            <span class="error-text"><?php echo $c['errors']['message']; ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="checkbox-group">
                        <input type="checkbox" id="consent" name="consent" required checked>
                        <label for="consent">Отправляя заявку, я даю согласие на обработку персональных данных</label>
                        <?php if (!empty($c['errors']['consent'])): ?>
                            <span class="error-text"><?php echo $c['errors']['consent']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" class="submit-btn">ОТПРАВИТЬ ЗАЯВКУ</button>
                </form>
            </div>
        </section>

        <footer class="main-footer">
            <div class="footer-top">
                <a href="#"><img src="img/logofooter.svg" alt="ARENXFAMILY" class="footer-logo"></a>
            </div>
            <div class="footer-bottom">
                <div class="footer-left">
                    <p>Developed by Pavel Kapitonov</p>
                    <a href="mailto:arenxfamily@mail.ru">arenxfamily@mail.ru</a>
                </div>
                <div class="footer-right">
                    <a href="tel:+79183456598">+7 (918) 345-65-98</a>
                </div>
            </div>
        </footer>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
    <script src="script.js"></script>
</body>
</html>
