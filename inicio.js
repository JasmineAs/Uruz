let currentParagraph = 0;
let currentImage = 0;

const paragraphs = document.querySelectorAll('.changing-paragraphs p');
const images = document.querySelectorAll('.changing-images img');

function changeParagraph() {
    paragraphs[currentParagraph].style.display = 'none';
    currentParagraph = (currentParagraph + 1) % paragraphs.length;
    paragraphs[currentParagraph].style.display = 'block';
}

function changeImage() {
    images[currentImage].style.display = 'none';
    currentImage = (currentImage + 1) % images.length;
    images[currentImage].style.display = 'block';
}

setInterval(changeParagraph, 3000);
setInterval(changeImage, 5000);
