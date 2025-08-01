document.addEventListener('DOMContentLoaded', function() {
    // Intersection Observer for fadein elements
    const fadeinElements = document.querySelectorAll('.fadein');
    if (fadeinElements.length > 0) {
        const observer = new IntersectionObserver((entries, obs) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in-1');
                    obs.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1
        });
        fadeinElements.forEach(el => observer.observe(el));
    }

    // Existing h1 animation
    const h1 = document.querySelector('h1[data-splitting]');
    if (h1 && typeof Splitting !== 'undefined' && typeof anime !== 'undefined') {
        // First split by words, then split each word by chars
        const results = Splitting({ target: h1, by: 'words' });
        if (results && results.length > 0) {
            const words = h1.querySelectorAll('.word');
            words.forEach(word => {
                Splitting({ target: word, by: 'chars' });
            });
            const chars = h1.querySelectorAll('.char');
            if (chars.length > 0) {
                anime.set(chars, {
                    opacity: 0,
                    translateY: 30,
                    scale: 0.3,
                    rotateZ: 15,
                    filter: 'blur(5px)'
                });
                // Ensure browser renders initial state before animating
                setTimeout(function() {
                    anime({
                        targets: chars,
                        opacity: 1,
                        translateY: 0,
                        scale: 1,
                        rotateZ: 0,
                        filter: 'blur(0px)',
                        duration: 1200,
                        delay: function(el, i) {
                            // Match home.js wave effect
                            const waveDelay = Math.sin(i * 0.3) * 50;
                            return 100 + (i * 25) + waveDelay;
                        },
                        easing: 'easeOutElastic(1, .8)',
                    });
                }, 120);
            }
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
                        translateY: 10,
                        scale: 0.3,
                        rotateZ: 10,
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
                                        const waveDelay = Math.sin(i * 0.3) * 220;
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