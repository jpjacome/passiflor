// Scroll-triggered square growth animation + Splitting.js + Anime.js text animations
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded');
    console.log('Splitting available:', typeof Splitting !== 'undefined');
    console.log('Anime available:', typeof anime !== 'undefined');
      // Force scroll to top on page load to prevent ~20px scroll offset issue
    if ('scrollRestoration' in history) {
        history.scrollRestoration = 'manual';
    }
    window.scrollTo(0, 0);
    
    // Handle page show event (including when page is loaded from browser cache)
    window.addEventListener('pageshow', function(event) {
        // Reset scroll position to top
        window.scrollTo(0, 0);
    });
      // Handle loading overlay fade-out
    window.addEventListener('load', function() {
        // Additional scroll reset on window load to ensure proper positioning
        window.scrollTo(0, 0);
        
        setTimeout(() => {
            const loadingOverlay = document.getElementById('loadingOverlay');
            if (loadingOverlay) {
                loadingOverlay.classList.add('fade-out');
                // Remove element completely after fade animation
                setTimeout(() => {
                    loadingOverlay.remove();
                }, 1000);
            }
        }, 500); // Wait 500ms after everything is loaded before starting fade
    });
    
    // Track page load time for overlay text animation timing
    const pageLoadTime = Date.now();    // Add loaded class to prevent flash of unstyled content
    const elementsToLoad = document.querySelectorAll('.hero-section, .about-section, .services-section, .consultation-section, .main-navbar, .hero-logo, .book-session-btn, .navbar-book-btn, .overlay-text, .background-container');
    elementsToLoad.forEach(element => {
        element.classList.add('loaded');
    });// Initialize Splitting.js + Anime.js for sophisticated text animation
    function initTextAnimations() {
        // Check if Splitting and anime are loaded
        if (typeof Splitting === 'undefined' || typeof anime === 'undefined') {
            console.warn('Splitting.js or Anime.js not loaded. Text animations will be skipped.');
            // Fallback: make sure the h1 and h2 are visible
            const introTitle = document.querySelector('.intro-section h1');
            if (introTitle) {
                introTitle.style.opacity = '1';
                introTitle.style.visibility = 'visible';
            }
            document.querySelectorAll('h2').forEach(h2 => {
                h2.style.opacity = '1';
                h2.style.visibility = 'visible';
            });
            return;
        }        
        
        // Animate the intro section h1
        const introTitle = document.querySelector('.intro-section h1');
        if (introTitle) {
            let animationTriggered = false;
            
            // Listen for scroll to trigger animation at 50px
            const scrollListener = () => {
                if (window.scrollY > 50 && !animationTriggered) {
                    animationTriggered = true;
                    window.removeEventListener('scroll', scrollListener);
                    
                    // Start the animation
                    const chars = introTitle.querySelectorAll('.char');
                    if (chars.length > 0) {
                        anime({
                            targets: chars,
                            opacity: 1,
                            translateY: 0,
                            scale: 1,
                            rotateZ: 0,
                            filter: 'blur(0px)',
                            duration: 1200,
                            delay: function(el, i) {
                                const waveDelay = Math.sin(i * 0.3) * 50;
                                return 100 + (i * 25) + waveDelay;
                            },
                            easing: 'easeOutElastic(1, .8)',
                            complete: function() {
                                const passiflor = document.querySelector('.passiflor-text');
                                if (passiflor) {
                                    anime({
                                        targets: passiflor,
                                        scale: [1, 1.015, 1],
                                        opacity: [1, 0.95, 1],
                                        duration: 3500,
                                        easing: 'easeInOutSine',
                                        loop: true,
                                        direction: 'alternate'
                                    });
                                }
                            }
                        });
                    }
                }
            };
            window.addEventListener('scroll', scrollListener);
            
            // First split by words to preserve line breaks, then split each word by characters
            const results = Splitting({ target: introTitle, by: 'words' });
            if (results && results.length > 0) {
                const words = introTitle.querySelectorAll('.word');
                words.forEach(word => {
                    Splitting({ target: word, by: 'chars' });
                });
                const chars = introTitle.querySelectorAll('.char');
                if (chars.length > 0) {
                    anime.set(chars, {
                        opacity: 0,
                        translateY: 30,
                        scale: 0.3,
                        rotateZ: 15,
                        filter: 'blur(5px)'
                    });
                }
            }
        }        
        
        // Animate .services-section h2 and .consultation-section h2 by words then chars (like h1)
        document.querySelectorAll('.services-section h2, .consultation-section h2').forEach(h2 => {
            // First split by words to preserve line breaks, then split each word by characters
            const results = Splitting({ target: h2, by: 'words' });
            if (results && results.length > 0) {
                const words = h2.querySelectorAll('.word');
                words.forEach(word => {
                    Splitting({ target: word, by: 'chars' });
                });
                const chars = h2.querySelectorAll('.char');
                if (chars.length > 0) {
                    anime.set(chars, {
                        opacity: 0,
                        translateY: 30,
                        scale: 0.3,
                        rotateZ: 15,
                        filter: 'blur(5px)'
                    });
                    const observer = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                anime({
                                    targets: chars,
                                    opacity: 1,
                                    translateY: 0,
                                    scale: 1,
                                    rotateZ: 0,
                                    filter: 'blur(0px)',
                                    duration: 1200,
                                    delay: function(el, i) {
                                        const waveDelay = Math.sin(i * 0.3) * 50;
                                        return 100 + (i * 25) + waveDelay;
                                    },
                                    easing: 'easeOutElastic(1, .8)'
                                });
                                observer.disconnect();
                            }
                        });
                    }, {
                        threshold: 0.25,
                        rootMargin: '0px 0px -25% 0px'
                    });
                    observer.observe(h2);
                }
            }
        });

        // Animate all other h2 elements (not in .services-section or .consultation-section) by words then chars if you want
        document.querySelectorAll('h2:not(.services-section h2):not(.consultation-section h2)').forEach(h2 => {
            const results = Splitting({ target: h2, by: 'words' });
            if (results && results.length > 0) {
                const words = h2.querySelectorAll('.word');
                words.forEach(word => {
                    Splitting({ target: word, by: 'chars' });
                });
                const chars = h2.querySelectorAll('.char');
                if (chars.length > 0) {
                    anime.set(chars, {
                        opacity: 0,
                        translateY: 30,
                        scale: 0.3,
                        rotateZ: 15,
                        filter: 'blur(5px)'
                    });
                    const observer = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                anime({
                                    targets: chars,
                                    opacity: 1,
                                    translateY: 0,
                                    scale: 1,
                                    rotateZ: 0,
                                    filter: 'blur(0px)',
                                    duration: 1000,
                                    delay: function(el, i) {
                                        const waveDelay = Math.sin(i * 0.3) * 80;
                                        return 200 + (i * 30) + waveDelay;
                                    },
                                    easing: 'easeOutElastic(1, .8)'
                                });
                                observer.disconnect();
                            }
                        });
                    }, {
                        threshold: 0.25,
                        rootMargin: '0px 0px -25% 0px'
                    });
                    observer.observe(h2);
                }
            }        });

        // Animate all h3.approach-section-title elements letter by letter
        document.querySelectorAll('.approach-section h3').forEach(h3 => {
            // Remove any previous splitting/animation
            h3.style.opacity = 0;
            h3.style.transition = 'opacity 1s cubic-bezier(0.25,0.8,0.25,1)';
            // Use IntersectionObserver for fade-in
            const observer = new IntersectionObserver((entries, obs) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        h3.style.opacity = 1;
                        obs.disconnect();
                    }
                });
            }, { threshold: 0.2 });
            observer.observe(h3);
        });
    }    // Initialize text animations
    initTextAnimations();

    // Initialize scroll-triggered animations for service and approach items
    function initScrollAnimations() {
        const animateElements = document.querySelectorAll('.animate-on-scroll');
        
        const observerOptions = {
            threshold: 0.15, // Trigger when 15% of element is visible
            rootMargin: '0px 0px -50px 0px' // Start animation 50px before element enters viewport
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    // Add visible class to trigger animation
                    entry.target.classList.add('visible');
                    // Stop observing this element once it's animated
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        // Start observing all elements with animate-on-scroll class
        animateElements.forEach(element => {
            observer.observe(element);
        });
    }    // Initialize scroll animations
    initScrollAnimations();

    // Function to manage overlay text visibility based on scroll position
    function manageOverlayTextVisibility() {
        const overlayText = document.querySelector('.overlay-text');
        if (!overlayText) return;

        const scrollY = window.scrollY;
        
        // Show overlay text only when within 20px of the top
        if (scrollY <= 20) {
            overlayText.style.opacity = '1';
        } else {
            overlayText.style.opacity = '0';
        }
    }    // Rest of the scroll-triggered square growth animation code...
    const root = document.documentElement;

    // Initialize CSS custom properties for dynamic hover colors
    root.style.setProperty('--navbar-hover-color', '#853720'); // Start with secondary color    // Configuration for overlay animation
    let initialSquareSize = window.innerWidth <= 700 ? 250 : 300;
    let maxSquareSize = Math.max(window.innerWidth, window.innerHeight) * 1.5;
    
    // Detect mobile device
    let isMobile = window.innerWidth <= 768 || /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    
    // Reduce max size for mobile devices to make hole smaller
    if (isMobile) {
        maxSquareSize = Math.max(window.innerWidth, window.innerHeight) * 1.0;
    }
    
    const scrollTriggerStart = 0;
    let scrollTriggerEnd = isMobile ? 400 : 1500;

    function updateSquareSize() {
        // Prevent scroll-based updates during overlay load animation
        if (document.body.classList.contains('overlay-animating')) return;
        const scrollY = window.scrollY;
        
        // Calculate progress (0 to 1)
        let progress = 0;
        if (scrollY > scrollTriggerStart) {
            progress = Math.min((scrollY - scrollTriggerStart) / (scrollTriggerEnd - scrollTriggerStart), 1);
        }

        // Apply easing function for smoother animation
        const easedProgress = easeOutCubic(progress);
        
        // Calculate new square size
        const currentSize = initialSquareSize + (maxSquareSize - initialSquareSize) * easedProgress;
        
        // Calculate overlay opacity (fade out as square grows)
        const overlayOpacity = 1 * (1 - easedProgress * 0.9);
        
        // Calculate border radius (from 100% to 0%)
        const borderRadius = 100 * (0.9 - easedProgress);
        
        // Update navbar background opacity and text color based on scroll
        const navbar = document.querySelector('.main-navbar');
        const navbarTitle = document.querySelector('.navbar-title');
        if (navbar && navbarTitle) {
            const navbarProgress = Math.min(scrollY / 500, 1);
            const navbarOpacity = navbarProgress;
            navbar.style.backgroundColor = `rgba(255, 252, 224, ${navbarOpacity})`;
            // Interpolate text color from primary to secondary
            const primaryColor = [255, 252, 224];
            const secondaryColor = [133, 55, 32];
            const r = Math.round(primaryColor[0] + (secondaryColor[0] - primaryColor[0]) * navbarProgress);
            const g = Math.round(primaryColor[1] + (secondaryColor[1] - primaryColor[1]) * navbarProgress);
            const b = Math.round(primaryColor[2] + (secondaryColor[2] - primaryColor[2]) * navbarProgress);            navbarTitle.style.color = `rgb(${r}, ${g}, ${b})`;
            // Also interpolate .navbar-link colors
            const navbarLinks = document.querySelectorAll('.navbar-link');
            navbarLinks.forEach(link => {
                link.style.color = `rgb(${r}, ${g}, ${b})`;
            });            // Also interpolate social media icon colors
            const socialIcons = document.querySelectorAll('.navbar-social a');
            socialIcons.forEach(icon => {
                icon.style.color = `rgb(${r}, ${g}, ${b})`;
            });
            
            // Set CSS custom properties for dynamic hover colors
            // Hover color should be primary at top, secondary after scroll
            const hoverR = Math.round(secondaryColor[0] + (primaryColor[0] - secondaryColor[0]) * (1 - navbarProgress));
            const hoverG = Math.round(secondaryColor[1] + (primaryColor[1] - secondaryColor[1]) * (1 - navbarProgress));
            const hoverB = Math.round(secondaryColor[2] + (primaryColor[2] - secondaryColor[2]) * (1 - navbarProgress));
            root.style.setProperty('--navbar-hover-color', `rgb(${hoverR}, ${hoverG}, ${hoverB})`);
        }

        // Update icon sources based on scroll progress
        const navbarLogo = document.querySelector('.navbar-logo');
        if (navbarLogo) {
            const iconProgress = Math.min(scrollY / 250, 1);
            const newIconSrc = iconProgress >= 0.5 ? './imgs/icon3.svg' : './imgs/icon5.svg';
            
            if (navbarLogo.src !== window.location.origin + '/' + newIconSrc) {
                navbarLogo.src = newIconSrc;
            }
        }        // Update CSS custom properties
        root.style.setProperty('--square-size', `${currentSize}px`);
        root.style.setProperty('--overlay-opacity', overlayOpacity);
        root.style.setProperty('--border-radius', `${borderRadius}%`);

        // Add parallax effect to intro section h1
        const introTitle = document.querySelector('.intro-section h1');
        if (introTitle) {
            const parallaxSpeed = 0.15;
            const parallaxOffset = scrollY * parallaxSpeed;
            introTitle.style.transform = `translateY(${parallaxOffset}px)`;
        }
        
        // Hide overlay completely when square is large enough
        const overlay = document.querySelector('.black-overlay');
        if (progress >= 0.95) {
            overlay.style.opacity = '0';
            overlay.style.pointerEvents = 'none';
        } else {
            overlay.style.opacity = '1';
            overlay.style.pointerEvents = 'none';
        }
    }
    
    // Easing function for smoother animation
    function easeOutCubic(t) {
        return 1 - Math.pow(1 - t, 3);
    }
      // Throttle scroll events for better performance
    let ticking = false;
    function onScroll() {
        if (!ticking) {
            requestAnimationFrame(function() {
                updateSquareSize();
                manageOverlayTextVisibility();
                ticking = false;
            });
            ticking = true;
        }
    }

    // Add scroll event listener
    window.addEventListener('scroll', onScroll);    // Update on resize
    window.addEventListener('resize', function() {
        initialSquareSize = window.innerWidth <= 700 ? 250 : 300;
        maxSquareSize = Math.max(window.innerWidth, window.innerHeight) * 1.5;
        isMobile = window.innerWidth <= 768 || /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        scrollTriggerEnd = isMobile ? 400 : 1500;
        updateSquareSize();
    });
    
    // Initialize
    updateSquareSize();



    // Animate hero-logo entrance only once on load
    const heroLogo = document.querySelector('.hero-logo');
    if (heroLogo) {
        // Wait for image/SVG to be fully parsed and visible
        setTimeout(() => {
            heroLogo.classList.add('animate-in');
        }, 100); // slight delay to ensure DOM is ready
    }

    // Remove scroll-based border-img rotation
    // window.addEventListener('scroll', function() {
    //     const borderImgs = document.querySelectorAll('.border-img');
    //     const angle = window.scrollY / 10;
    //     borderImgs.forEach(img => {
    //         img.style.transform = `rotate(${angle}deg)`;
    //     });
    // });
    
    // Add overlay-grow class to overlay for load animation
    const overlay = document.querySelector('.black-overlay');
    if (overlay) overlay.classList.add('overlay-grow');
    // Wait for next frame, then animate to 300px
    requestAnimationFrame(() => {
        if (overlay) overlay.classList.add('overlay-grow');
        setTimeout(() => {
            if (overlay) overlay.classList.remove('overlay-grow');
            document.body.classList.add('scrolled'); // enable scroll-based transitions
            updateSquareSize();
        }, 3000);
    });

    // WhatsApp Floating Button: fade/slide in on load, open WhatsApp on click/keyboard
    setTimeout(() => {
      document.body.classList.add('whatsapp-float-visible');
    }, 2200); // fade/slide in after other main content

    const waBtn = document.getElementById('whatsappFloat');
    if (waBtn) {
      waBtn.addEventListener('click', function() {
        window.open('https://wa.me/1234567890', '_blank');
      });
      waBtn.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
          window.open('https://wa.me/1234567890', '_blank');
        }
      });
    }    // Initialize hamburger menu (minimal toggle functionality)
    function initHamburgerMenu() {
        const hamburgerMenu = document.querySelector('.hamburger-menu');
        
        if (!hamburgerMenu) {
            console.warn('Hamburger menu not found');
            return;
        }
        
        let isMenuOpen = false;
        
        // Basic toggle functionality for mobile nav
        hamburgerMenu.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            isMenuOpen = !isMenuOpen;
            
            hamburgerMenu.setAttribute('aria-expanded', isMenuOpen.toString());
        });
        
        // Keyboard accessibility
        hamburgerMenu.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                hamburgerMenu.click();
            }
        });
        
        // Set up accessibility attributes
        hamburgerMenu.setAttribute('tabindex', '0');
        hamburgerMenu.setAttribute('role', 'button');
        hamburgerMenu.setAttribute('aria-label', 'Toggle navigation menu');
        hamburgerMenu.setAttribute('aria-expanded', 'false');
    }
      // Initialize hamburger menu
    initHamburgerMenu();

    const hamburger = document.querySelector('.hamburger-menu');
    if (hamburger) {
        hamburger.addEventListener('click', function() {
            hamburger.classList.toggle('hamburger-clicked');
        });
    }

    // Animate hamburger icon on class change (visual only, no property changes)
    if (hamburger) {
        let lastState = hamburger.classList.contains('hamburger-clicked');
        const observer = new MutationObserver(() => {
            const isClicked = hamburger.classList.contains('hamburger-clicked');
            if (isClicked !== lastState) {
                // Elastic scale: down, up, then original
                anime.remove(hamburger);
                anime({
                    targets: hamburger,
                    scale: [1, 0.92, 1.12, 1],
                    duration: 650,
                    easing: 'easeOutElastic(1, .7)'
                });
                lastState = isClicked;
            }
        });
        observer.observe(hamburger, { attributes: true, attributeFilter: ['class'] });
    }

    // === Hashtag Service Info Functionality ===
    (function initHashtagInfo() {
        const serviceInfo = {
            'online-therapy': {
                icon: 'ph-monitor-play',
                title: 'Terapia Online',
                description: 'Sesiones de terapia confidenciales y convenientes desde la comodidad de tu hogar mediante videollamadas seguras.'
            },
            'integrative-therapy': {
                icon: 'ph-puzzle-piece',
                title: 'Terapia Integrativa',
                description: 'Un enfoque holístico que combina métodos basados en evidencia adaptados a tus necesidades y objetivos únicos.'
            },
            'psychedelic-therapy': {
                icon: 'ph-flower-lotus',
                title: 'Terapia Psicodélica',
                description: 'Orientación y apoyo para experiencias psicodélicas terapéuticas seguras e integración.'
            },
            'microdosing-support': {
                icon: 'ph-drop',
                title: 'Apoyo en Microdosis',
                description: 'Asesoramiento experto y seguimiento para quienes exploran la microdosificación como parte de su bienestar.'
            },
            'autism-support': {
                icon: 'ph-brain',
                title: 'Apoyo en Autismo',
                description: 'Terapia compasiva y recursos para personas y familias en el espectro autista.'
            },
            'child-therapy': {
                icon: 'ph-baby',
                title: 'Terapia Infantil',
                description: 'Terapia lúdica y apropiada para ayudar a los niños a expresarse y desarrollar resiliencia.'
            },
            'young-adult-therapy': {
                icon: 'ph-student',
                title: 'Terapia para Jóvenes',
                description: 'Apoyo para adolescentes y jóvenes adultos ante transiciones, identidad y desafíos de salud mental.'
            },
            'yoga-healing': {
                icon: 'ph-yin-yang',
                title: 'Yoga para Sanar',
                description: 'Prácticas mente-cuerpo para fomentar la sanación, el equilibrio y la autoconciencia a través del yoga.'
            },
            'trauma-survivor': {
                icon: 'ph-shield-check',
                title: 'Supervivientes de Trauma',
                description: 'Atención sensible e informada en trauma para sobrevivientes de abuso y agresión sexual.'
            },
            'parent-support': {
                icon: 'ph-users-three',
                title: 'Apoyo a Padres',
                description: 'Orientación y comunidad para padres que buscan apoyar a sus hijos y a sí mismos.'
            }
        };
        const hashtagBtns = document.querySelectorAll('.hashtag-btn');
        const infoContainer = document.getElementById('serviceInfoContainer');
        const infoIcon = document.getElementById('serviceIcon');
        const infoTitle = infoContainer ? infoContainer.querySelector('.service-title') : null;
        const infoDesc = infoContainer ? infoContainer.querySelector('.service-description') : null;
        function showInfo(serviceKey) {
            if (!infoContainer || !infoIcon || !infoTitle || !infoDesc) return;
            const info = serviceInfo[serviceKey];
            if (!info) return;
            infoIcon.className = 'ph service-icon-display ' + info.icon;
            infoTitle.childNodes.forEach(node => {
                if (node.nodeType === 3) node.remove(); // Remove text nodes
            });
            infoTitle.appendChild(document.createTextNode(info.title));
            infoDesc.textContent = info.description;
            infoContainer.classList.add('visible');
            // Animate the icon: single, smooth, one-way effect (no bounce, no overshoot)
            anime.remove(infoIcon); // Remove previous animations
            anime({
                targets: infoIcon,
                scale: [0.7, 1],
                rotate: [18, 0],
                opacity: [0, 1],
                duration: 650,
                easing: 'cubicBezier(0.33, 1, 0.68, 1)'
            });
        }
        function hideInfo() {
            if (infoContainer) infoContainer.classList.remove('visible');
        }
        hashtagBtns.forEach(btn => {
            btn.addEventListener('mouseenter', () => showInfo(btn.dataset.service));
            btn.addEventListener('focus', () => showInfo(btn.dataset.service));
            btn.addEventListener('click', () => showInfo(btn.dataset.service));
        });
        // Hide info when mouse leaves the hashtag list
        const hashtagList = document.querySelector('.hashtag-list');
        if (hashtagList) {
            hashtagList.addEventListener('mouseleave', hideInfo);
        }
        // Hide info on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') hideInfo();
        });
        // Hide info on blur (if focus leaves hashtag-btns)
        hashtagBtns.forEach(btn => {
            btn.addEventListener('blur', function(e) {
                // Only hide if focus is not moving to another hashtag-btn
                setTimeout(() => {
                    if (!document.activeElement.classList.contains('hashtag-btn')) {
                        hideInfo();
                    }
                }, 10);
            });
        });
    })();

    // Mobile menu overlay
    const mobileMenuOverlay = document.querySelector('.mobile-menu-overlay');
    const mobileMenuContainer = mobileMenuOverlay ? mobileMenuOverlay.querySelector('.mobile-menu-container') : null;
    // const mobileMenuCloseBtn = mobileMenuOverlay ? mobileMenuOverlay.querySelector('.mobile-menu-close') : null;
    // Use existing 'hamburger' variable from outer scope
    let lastFocusedElementMenu = null;
    let menuOpen = false;

    // Ensure mobile menu is hidden on desktop screens on page load
    function initializeMobileMenuVisibility() {
        if (mobileMenuOverlay && window.innerWidth > 950) {
            mobileMenuOverlay.classList.remove('open');
            mobileMenuOverlay.style.display = 'none';
            menuOpen = false;
        } else if (mobileMenuOverlay && window.innerWidth <= 950) {
            mobileMenuOverlay.style.display = 'flex';
        }
    }
    
    // Call on page load
    initializeMobileMenuVisibility();

    function openMobileMenuOverlay() {
        if (!mobileMenuOverlay || !mobileMenuContainer) return;
        if (menuOpen) return;
        
        // Prevent mobile menu from opening on desktop screens
        if (window.innerWidth > 950) return;
        
        menuOpen = true;
        mobileMenuOverlay.classList.add('open');
        mobileMenuOverlay.setAttribute('aria-modal', 'true');
        mobileMenuOverlay.setAttribute('role', 'dialog');
        lastFocusedElementMenu = document.activeElement;
        // Animate container in
        if (typeof anime !== 'undefined') {
            anime.set(mobileMenuContainer, { scale: 0.7, opacity: 0, translateY: 50 });
            anime({
                targets: mobileMenuContainer,
                scale: [0.7, 1],
                opacity: [0, 1],
                translateY: [50, 0],
                duration: 600,
                easing: 'easeOutElastic(1, .7)'
            });
            // Animate links in with stagger
            const links = mobileMenuContainer.querySelectorAll('.navbar-link');
            anime.set(links, { opacity: 0, translateY: 30 });
            anime({
                targets: links,
                opacity: [0, 1],
                translateY: [30, 0],
                delay: anime.stagger(70, { start: 200 }),
                duration: 500,
                easing: 'easeOutCubic'
            });
        }
        setTimeout(() => {
            const firstLink = mobileMenuContainer.querySelector('.navbar-link');
            if (firstLink) firstLink.focus();
        }, 10);
        document.addEventListener('keydown', trapFocusInMobileMenu);
    }

    function closeMobileMenuOverlay() {
        if (!mobileMenuOverlay || !mobileMenuContainer) return;
        if (!menuOpen) return;
        menuOpen = false;
        // Animate links out with stagger
        if (typeof anime !== 'undefined') {
            const links = mobileMenuContainer.querySelectorAll('.navbar-link');
            anime({
                targets: links,
                opacity: [1, 0],
                translateY: [0, 30],
                delay: anime.stagger(40),
                duration: 200,
                easing: 'easeInCubic'
            });
            // Animate container out after links
            anime({
                targets: mobileMenuContainer,
                scale: [1, 0.7],
                opacity: [1, 0],
                translateY: [0, 50],
                duration: 350,
                delay: 120,
                easing: 'easeInCubic',
                complete: function() {
                    mobileMenuOverlay.classList.remove('open');
                    mobileMenuOverlay.removeAttribute('aria-modal');
                    mobileMenuOverlay.removeAttribute('role');
                    if (lastFocusedElementMenu) lastFocusedElementMenu.focus();
                }
            });
        } else {
            mobileMenuOverlay.classList.remove('open');
            mobileMenuOverlay.removeAttribute('aria-modal');
            mobileMenuOverlay.removeAttribute('role');
            if (lastFocusedElementMenu) lastFocusedElementMenu.focus();
        }
    }

    function trapFocusInMobileMenu(e) {
        if (!mobileMenuOverlay.classList.contains('open')) return;
        if (e.key === 'Escape') {
            closeMobileMenuOverlay();
            if (hamburger) hamburger.setAttribute('aria-expanded', 'false');
            return;
        }
        if (e.key !== 'Tab') return;
        const focusable = Array.from(mobileMenuContainer.querySelectorAll('a, button, [tabindex]:not([tabindex="-1"])'));
        if (!focusable.length) return;
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (e.shiftKey) {
            if (document.activeElement === first) {
                e.preventDefault();
                last.focus();
            }
        } else {
            if (document.activeElement === last) {
                e.preventDefault();
                first.focus();
            }
        }
    }    // Open/close handlers
    if (hamburger) {
        hamburger.addEventListener('click', function() {
            // Only handle mobile menu on mobile screens
            if (window.innerWidth <= 950) {
                if (mobileMenuOverlay.classList.contains('open')) {
                    closeMobileMenuOverlay();
                    hamburger.setAttribute('aria-expanded', 'false');
                } else {
                    openMobileMenuOverlay();
                    hamburger.setAttribute('aria-expanded', 'true');
                }
            }
        });
        hamburger.setAttribute('aria-controls', 'mobileMenu');
        hamburger.setAttribute('aria-expanded', 'false');
        hamburger.setAttribute('aria-label', 'Open mobile menu');
    }
    // if (mobileMenuCloseBtn) {
    //     mobileMenuCloseBtn.addEventListener('click', closeMobileMenuOverlay);
    // }
    // Close on overlay click (not container)
    if (mobileMenuOverlay && mobileMenuContainer) {
        mobileMenuOverlay.addEventListener('mousedown', function(e) {
            if (e.target === mobileMenuOverlay) closeMobileMenuOverlay();
        });
        // Prevent close when clicking inside container
        mobileMenuContainer.addEventListener('mousedown', function(e) {
            e.stopPropagation();
        });
    }    // Close mobile menu when resizing to desktop
    window.addEventListener('resize', function() {
        if (window.innerWidth > 950 && menuOpen) {
            closeMobileMenuOverlay();
            if (hamburger) {
                hamburger.setAttribute('aria-expanded', 'false');
            }
        }
        // Re-initialize mobile menu visibility on resize
        initializeMobileMenuVisibility();
    });

    // === Book Session Modal Functionality (Robust) ===
    (function initBookSessionModal() {
        const modal = document.getElementById('bookingModal');
        const modalContainer = modal ? modal.querySelector('.modal-container') : null;
        const openBtns = [
            ...document.querySelectorAll('.book-session-btn, .navbar-book-btn')
        ];
        const closeBtn = modal ? modal.querySelector('.modal-close') : null;
        let lastFocusedElement = null;
        let isOpen = false;

        function openModal(e) {
            if (!modal || !modalContainer) return;
            if (isOpen) return;
            isOpen = true;
            lastFocusedElement = document.activeElement;
            modal.classList.add('active');
            modal.setAttribute('aria-modal', 'true');
            modal.setAttribute('role', 'dialog');
            if (typeof anime !== 'undefined') {
                // Animate modal container in
                anime.set(modalContainer, { scale: 0.7, opacity: 0, translateY: 50 });
                anime({
                    targets: modalContainer,
                    scale: [0.7, 1],
                    opacity: [0, 1],
                    translateY: [50, 0],
                    duration: 600,
                    easing: 'easeOutElastic(1, .7)'
                });
                // Animate children in with stagger
                const children = modalContainer.querySelectorAll('.modal-title, .modal-field, .modal-action, .modal-close, input, select, textarea, button');
                anime.set(children, { opacity: 0, translateY: 30 });
                anime({
                    targets: children,
                    opacity: [0, 1],
                    translateY: [30, 0],
                    delay: anime.stagger(70, { start: 200 }),
                    duration: 500,
                    easing: 'easeOutCubic'
                });
            }
            setTimeout(() => {
                const firstInput = modal.querySelector('input, select, textarea, button');
                if (firstInput) firstInput.focus();
            }, 10);
            document.addEventListener('keydown', trapFocus);
        }
        function closeModal() {
            if (!modal || !modalContainer) return;
            if (!isOpen) return;
            isOpen = false;
            if (typeof anime !== 'undefined') {
                // Animate children out with stagger (optional, for extra polish)
                const children = modalContainer.querySelectorAll('.modal-title, .modal-field, .modal-action, .modal-close, input, select, textarea, button');
                anime({
                    targets: children,
                    opacity: [1, 0],
                    translateY: [0, 30],
                    delay: anime.stagger(40),
                    duration: 250,
                    easing: 'easeInCubic'
                });
                // Animate modal container out after children
                anime({
                    targets: modalContainer,
                    scale: [1, 0.7],
                    opacity: [1, 0],
                    translateY: [0, 50],
                    duration: 350,
                    delay: 120,
                    easing: 'easeInCubic',
                    complete: function() {
                        modal.classList.remove('active');
                        modal.removeAttribute('aria-modal');
                        modal.removeAttribute('role');
                        if (lastFocusedElement) lastFocusedElement.focus();
                    }
                });
            } else {
                modal.classList.remove('active');
                modal.removeAttribute('aria-modal');
                modal.removeAttribute('role');
                if (lastFocusedElement) lastFocusedElement.focus();
            }
            document.removeEventListener('keydown', trapFocus);
        }
        function trapFocus(e) {
            if (!modal.classList.contains('active')) return;
            if (e.key === 'Escape') {
                closeModal();
                return;
            }
            if (e.key !== 'Tab') return;
            const focusable = Array.from(modal.querySelectorAll('input, select, textarea, button, [tabindex]:not([tabindex="-1"])'));
            if (!focusable.length) return;
            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            if (e.shiftKey) {
                if (document.activeElement === first) {
                    e.preventDefault();
                    last.focus();
                }
            } else {
                if (document.activeElement === last) {
                    e.preventDefault();
                    first.focus();
                }
            }
        }
        // Open modal on button click
        openBtns.forEach(btn => btn.addEventListener('click', openModal));
        // Close modal on close button
        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        // Prevent modal close when clicking inside modalContainer
        if (modalContainer) {
            modalContainer.addEventListener('mousedown', function(e) {
                e.stopPropagation();
            });
        }
        // Close modal on overlay click (only if clicking the overlay, not the modal content)
        if (modal) {
            modal.addEventListener('mousedown', function(e) {
                if (e.target === modal) closeModal();
            });
        }
    })();    // Circle Overlay Animation and Interaction
    (function initCircleOverlay() {
        const circleOverlay = document.querySelector('.circle-overlay');
        const overlayMessage = document.querySelector('.overlay-message');
        const background5 = document.querySelector('.background-5');
        
        if (!circleOverlay || !background5) return;
        
        let animationCompleted = false; // Flag to track if animation has reached max size
        
        // Add scroll-based animation for circle overlay hole
        function animateCircleOverlay() {
            // If animation is already completed, don't run again
            if (animationCompleted) return;
            
            const rect = background5.getBoundingClientRect();
            const windowHeight = window.innerHeight;
            
            // Check if element is in viewport
            const isVisible = rect.top < windowHeight && rect.bottom > 0;
            
            if (isVisible) {
                // Calculate how much of the element is visible
                const elementTop = Math.max(0, -rect.top);
                const elementBottom = Math.min(rect.height, windowHeight - rect.top);
                const visibleHeight = Math.max(0, elementBottom - elementTop);
                const visibilityRatio = visibleHeight / rect.height;
                
                // Rapid climb: use exponential easing for faster growth
                const progress = Math.pow(visibilityRatio, 1);
                
                // Calculate hole size (from 0 to maximum size based on viewport)
                const maxHoleSize = Math.min(windowHeight * .8, 1400);
                const holeSize = progress * maxHoleSize;
                
                // Check if we've reached the maximum size
                if (holeSize >= maxHoleSize) {
                    animationCompleted = true;
                    // Remove scroll listener since animation is complete
                    window.removeEventListener('scroll', handleCircleOverlayScroll);
                }
                
                // Apply circular mask to create the hole effect
                const maskValue = `radial-gradient(circle at center, transparent ${holeSize}px, rgba(133, 55, 32, 1) ${holeSize + 2}px)`;
                circleOverlay.style.webkitMask = maskValue;
                circleOverlay.style.mask = maskValue;
                
                // Show overlay message once hole is big enough
                if (overlayMessage && holeSize > 50) {
                    overlayMessage.classList.add('visible');
                }
                
                // Make overlay visible
                circleOverlay.style.opacity = '1';
            }
        }
        
        // Add scroll listener for circle overlay animation
        let circleOverlayTicking = false;
        function handleCircleOverlayScroll() {
            if (!circleOverlayTicking && !animationCompleted) {
                requestAnimationFrame(() => {
                    animateCircleOverlay();
                    circleOverlayTicking = false;
                });
                circleOverlayTicking = true;
            }
        }
        
        window.addEventListener('scroll', handleCircleOverlayScroll);
        
        // Initial animation call
        animateCircleOverlay();
    })();
    
    // --- Repeated scroll reset for mobile browser UI overlays ---
    (function ensureScrollTopOnMobile() {
        if (window.innerWidth > 900) return; // Only run on mobile/tablet
        let start = Date.now();
        let maxDuration = 2500; // ms
        let interval = setInterval(() => {
            window.scrollTo(0, 0);
            if (Date.now() - start > maxDuration) clearInterval(interval);
        }, 100); // Try every 100ms for 2.5s
    })();

    // --- Real viewport height for mobile Safari and overlays ---
    function setRealVh() {
        const vh = window.innerHeight * 0.01;
        document.documentElement.style.setProperty('--real-vh', `${vh}px`);
    }
    setRealVh();
    window.addEventListener('resize', setRealVh);
    window.addEventListener('orientationchange', setRealVh);
});
