var home_url = WP_URLs.home_url;
var network_home_url = WP_URLs.network_home_url;
var map, marker;

// Story Description Toggle
function descToggler() {
    let buttonDesc = document.querySelector('.descMore');
    if(buttonDesc.previousSibling.style.maxHeight === '202px') {
            buttonDesc.previousSibling.style.maxHeight = 'unset';
            buttonDesc.textContent = 'Show Less';
    } else {
            buttonDesc.previousSibling.style.maxHeight = '202px';
            buttonDesc.textContent = 'Show More';
    }
}


var ready = (callback) => {
    if (document.readyState != "loading") callback();
    else document.addEventListener("DOMContentLoaded", callback);
}
// Replacement for jQuery document.ready; It runs the code after DOM is completely loaded
ready(() => {

    // Description Toggle
    const paraToggler = document.querySelector('.descMore');
    if(paraToggler) {
        paraToggler.addEventListener('click', descToggler, false);
    }

    // Metadata collapse button on StoryPage
    const metaBtn = document.querySelector('#meta-collapse-btn');
    const metaContainer = document.querySelector('.js-container');
    const metaShowMore = document.querySelector('#meta-show-more');
    if(metaBtn){
        metaBtn.addEventListener('click', function() {
            if(metaContainer.style.height <= '110px') {
                metaContainer.style.height = 'unset';
                document.querySelector('#meta-show-more').style.display = 'none';
            } else {
                metaContainer.style.height = '110px';
                document.querySelector('#meta-show-more').style.display = 'block';
            }
        })

        metaShowMore.addEventListener('click', function() {
            if(metaContainer.style.height <= '110px') {
                metaContainer.style.height = 'unset';
                document.querySelector('#meta-show-more').style.display = 'none';
            } else {
                metaContainer.style.height = '110px';
                document.querySelector('#meta-show-more').style.display = 'block';
            }
        })

    }
    /// Test slider
    const sliderContainer = document.querySelector('#inner-slider');
    const sliderImages = JSON.parse(document.querySelector('#slider-images').innerHTML);
    const sliderWidth = sliderContainer.offsetWidth;
    const numOfStickers = Math.floor(sliderWidth/200);
    const storyId = document.querySelector('#story-id').textContent;

    const prevBtn = document.querySelector('.prev-slide');
    const nextBtn = document.querySelector('.next-slide');

    if(sliderImages.length < numOfStickers) {
        prevBtn.style.display = 'none';
        nextBtn.style.display = 'none';
    }

    let startSlide = 0;
    let endSlide = numOfStickers;
    
    // Create initial slides on the screen
    for(let x=0; x < numOfStickers; x++) {
        let imgInfo = sliderImages[x].split(' || ');
        let imgUri = imgInfo[0];
        let imgId = imgInfo[1];
        let imgCompStatus = imgInfo[2];

        sliderContainer.innerHTML += 
            `<div class='slide-sticker' data-value='${x + 1}'>` +
                `<div class='slide-img-wrap'>` +
                    `<a href='${home_url}/documents/story/item/?story=${storyId}&item=${imgId}' class='slider-link'>` +
                        `<img src='${imgUri}' class='slider-image' alt='slider-img-${x+1}' width='200' height='200'>` +
                    `</a>` +
                    `<div class='image-completion-status' style='background-color:${imgCompStatus};'>` +
                        `<div class='slide-number-wrap'>${x + 1}</div>` +
                    `</div>` +
                `</div>` +
            `</div>`;
    }
    ////// Second set of variables, after initial slider is rendered
    // Make nodelist of slides so we can manipulate them
    const sliderSlides = sliderContainer.querySelectorAll('.slide-sticker');
    // Get number of dots we need to show on screen
    const numOfSlides = Math.ceil(sliderImages.length / numOfStickers);
    const dotContainer = document.querySelector('#dot-indicators');
    let currentDot = 1;

    // Create dot indicators to jump to desired set of slides
    for(let z = 1; z <= numOfSlides; z++) {
        let singleDot = document.createElement('div');
        singleDot.classList.add('slider-dot');
        singleDot.setAttribute('data-value', (z));
        // Add event to the dot
        singleDot.addEventListener('click', function() {
            currentDot = parseInt(this.getAttribute('data-value'));
            this.classList.add('current');
            
            endSlide = numOfStickers * z;
            if(endSlide > sliderImages.length) {
                endSlide = sliderImages.length;
            }
            startSlide = endSlide - numOfStickers;
            slideImages(startSlide, endSlide, sliderSlides, sliderImages, storyId);
            activeDot(currentDot);
        });
        dotContainer.appendChild(singleDot);
    }
    dotContainer.querySelector('.slider-dot').classList.add('current');


    
    function slideImages(slideStart, slideEnd, slides, imageInfo, storyid) {
        let indexOfSlide = 0;
        for(let i = slideStart; i < slideEnd; i++) {
            let imgArr = imageInfo[i].split(' || ');
            
            slides[indexOfSlide].querySelector('.slider-image').setAttribute('src', imgArr[0]);
            slides[indexOfSlide].querySelector('.slider-link').setAttribute('href', `${home_url}/documents/story/item/?story=${storyid}&item=${imgArr[1]}`);
            slides[indexOfSlide].querySelector('.image-completion-status').style.backgroundColor = imgArr[2];
            slides[indexOfSlide].querySelector('.slide-number-wrap').textContent = i + 1;

            indexOfSlide ++;
        }
    }

    function activeDot(number) {
        const sliderDots = dotContainer.querySelectorAll('.slider-dot');
        for(let dot of sliderDots) {
            if(dot.getAttribute('data-value') < number || dot.getAttribute('data-value') > number) {
                if(dot.classList.contains('current')) {
                    dot.classList.remove('current');
                }
            }
        }
    }

    nextBtn.addEventListener('click', function () {

        if(endSlide === sliderImages.length) {
            endSlide = numOfStickers;
            startSlide = 0;
        } else if((endSlide + numOfStickers) > sliderImages.length) {
            endSlide = sliderImages.length;
            startSlide = sliderImages.length - numOfStickers;
        } else {
            endSlide = endSlide + numOfStickers;
            startSlide = startSlide + numOfStickers;
        }
       
        slideImages(startSlide, endSlide, sliderSlides, sliderImages, storyId);
        // change active dot
        const sliderDots = dotContainer.querySelectorAll('.slider-dot');
        let curDot = parseInt(dotContainer.querySelector('.current').getAttribute('data-value'));
        if(curDot == sliderDots.length) {
            sliderDots[curDot-1].classList.remove('current');
            sliderDots[0].classList.add('current');
        } else {
            sliderDots[curDot-1].classList.remove('current');
            sliderDots[curDot].classList.add('current');
        }
    });

    prevBtn.addEventListener('click', function() {
        if(startSlide === 0) {
            endSlide = sliderImages.length;
            startSlide = sliderImages.length - numOfStickers;
        } else if((startSlide - numOfStickers) < 0) {
            startSlide = 0;
            endSlide = numOfStickers;
        } else {
            startSlide -= numOfStickers;
            endSlide -= numOfStickers;
        }
        slideImages(startSlide, endSlide, sliderSlides, sliderImages, storyId);
        // Change active dot
        const sliderDots = dotContainer.querySelectorAll('.slider-dot');
        let curDot = parseInt(dotContainer.querySelector('.current').getAttribute('data-value'));
        if(curDot - 2 < 0) {
            sliderDots[curDot - 1].classList.remove('current');
            sliderDots[sliderDots.length-1].classList.add('current');
        } else {
            sliderDots[curDot - 1].classList.remove('current');
            sliderDots[curDot - 2].classList.add('current');
        }
    });



});