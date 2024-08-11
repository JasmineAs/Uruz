const carouselInner = document.querySelector('.carousel-inner');
const carouselItems = document.querySelectorAll('.carousel-item');

carouselItems.forEach(item => {
    item.addEventListener('mouseover', () => {
        carouselInner.style.animationPlayState = 'paused';
    });
    item.addEventListener('mouseout', () => {
        carouselInner.style.animationPlayState = 'running';
    });
});

