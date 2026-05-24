document.addEventListener('DOMContentLoaded', function() {


    /* БУРГЕР МЕНЮ */

    const burgerBtn = document.getElementById('burgerBtn');
    const menuList = document.getElementById('menuList');
    const menuLinks = document.querySelectorAll('.main-nav a');


    burgerBtn.addEventListener('click', () => {
        menuList.classList.toggle('active');
        burgerBtn.classList.toggle('open');
    });

    menuLinks.forEach(link => {
        link.addEventListener('click', () => {
            menuList.classList.remove('active');
            burgerBtn.classList.remove('open');
        });
    });


    /* ФОРМА */ 

    
    const form = document.getElementById('contactForm');
    const statusMsg = document.getElementById('statusMessage');

    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const userId = document.body.getAttribute('data-user-id');
            const isUpdate = userId && userId !== ''; 

            const method = isUpdate ? 'PUT' : 'POST';
            const apiPath = isUpdate ? `/fullstack-webproject/api/users/${userId}` : '/fullstack-webproject/api/users';

            const formData = new FormData(form);
            const bodyData = new URLSearchParams(formData);

            fetch(apiPath, {
                method: method,
                body: bodyData, 
                headers: { 
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest' 
                }
            })
            .then(response => {
                return response.text().then(text => {
                    if (text.trim().startsWith('{')) {
                        return JSON.parse(text);
                    } else {
                        throw new Error('Ошибка сервера: ' + text.substring(0, 50));
                    }
                });
            })
            .then(data => {
                document.querySelectorAll('.error-text').forEach(el => el.remove());

                if (data.status === 'error') {
                    if (statusMsg) {
                        statusMsg.textContent = '❌ Ошибка.';
                        statusMsg.className = 'status error';
                    }
                    if (data.errors) {
                        Object.keys(data.errors).forEach(key => {
                            const input = document.getElementById(key);
                            if (input) {
                                const errSpan = document.createElement('span');
                                errSpan.className = 'error-text';
                                errSpan.style.color = '#ff4d4d';
                                errSpan.style.display = 'block';
                                errSpan.textContent = data.errors[key];
                                input.closest('.input-group')?.appendChild(errSpan);
                            }
                        });
                    }
                } else if (data.status === 'success') {
                    if (data.mode === 'register') {
                        if (statusMsg) {
                            statusMsg.innerHTML = `
                                <div class="success-alert" style="background: rgba(46, 204, 113, 0.2); padding: 15px; color: #fff;">
                                    <p>✅ Заявка отправлена!</p>
                                    <p>Логин: <code>${data.login}</code></p>
                                    <p>Пароль: <code>${data.password}</code></p>
                                </div>`;
                        }
                        form.reset();
                    } else {
                        if (statusMsg) {
                            statusMsg.innerHTML = '<p style="color: #2ecc71;">✅ Данные обновлены</p>';
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                if (statusMsg) {
                    statusMsg.textContent = '❌ Ошибка: ' + error.message;
                }
            });
        });
    }

});


/* СЛАЙДЕР */

$(document).ready(function(){

    var $slider = $('.cottage-slider');

    $slider.slick({
        infinite: true,
        slidesToShow: 1, 
        slidesToScroll: 1,
        fade: true, 
        arrows: true, 
        prevArrow: $('.prev-slide'), 
        nextArrow: $('.next-slide')
    });

    var totalSlides = $slider.slick('getSlick').slideCount;

    if (totalSlides < 10) {
        $('.total').text('0' + totalSlides);
    } else {
        $('.total').text(totalSlides);
    }

    $slider.on('afterChange', function(event, slick, currentSlide){
        
        var slideNumber = currentSlide + 1;

        if (slideNumber < 10) {
            $('.current').text('0' + slideNumber);
        } else {
            $('.current').text(slideNumber);
        }
        
    });

});

