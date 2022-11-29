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

    // Image Sider
    // New Js for Image slider
    const imgSliderCheck = document.querySelector('#img-slider');
    if(imgSliderCheck) {
        // function to show/hide images
        function showImages(start, end, images) {
            for(let img of images) {
                if(img.getAttribute('data-value') < start || img.getAttribute('data-value') > end) {
                    img.style.display = 'none';
                } else {
                    img.style.display = 'inline-block';
                }
            }
        }
        // Only item page(start slider with the item on the page)
        const currentItem = document.querySelector('#slide-start');
        //
        const imgStickers = document.querySelectorAll('.slide-sticker');
        const windowWidth = document.querySelector('#img-slider').clientWidth;
        let sliderStart = 1; // First Image to the left
        let sliderEnd = 0; // Last Image to the right
        const nextSet = document.querySelector('.next-slide');
        const prevSet = document.querySelector('.prev-slide');
        const leftSpanNumb = document.querySelector('#left-num');
        const rightSpanNumb = document.querySelector('#right-num');
        let currentDot = 1;
        let step = 0; // number of images on screen
        if(windowWidth > 1200) {
            step = 9;
        } else if(windowWidth > 800) {
            step = 5;
        } else {
            step = 3;
        }
    
        sliderEnd = step;
    
        if(imgStickers.length <= step){
            prevSet.style.display = 'none';
            nextSet.style.display = 'none';
        }
        leftSpanNumb.textContent = sliderStart;
        rightSpanNumb.textContent = sliderEnd;
        // check if there are more images than it fits on the screen
        if(nextSet.style.display != 'none') {
            showImages(sliderStart, sliderEnd, imgStickers);
        }
        // Slider dots
        const dotContainer = document.querySelector('#dot-indicators');
        const numberDots = Math.ceil(imgStickers.length / step);
        for(let i = 0; i < numberDots; i++) {
            const sliderDot = document.createElement('div');
            sliderDot.classList.add('slider-dot');
            sliderDot.setAttribute('data-value', (i+1));
            dotContainer.appendChild(sliderDot);
        }
    
        const sliderDots = document.querySelectorAll('.slider-dot');
        
        for(let dot of sliderDots) {
            dot.addEventListener('click', function() {
                currentDot = parseInt(dot.getAttribute('data-value'));
                dot.classList.add('current');
                if(dot.getAttribute('data-value') * step > imgStickers.length) {
                    sliderStart = (imgStickers.length - step) + 1;
                    sliderEnd = imgStickers.length;
                } else {
                    sliderEnd = parseInt(dot.getAttribute('data-value')) * step;
                    sliderStart = (sliderEnd - step) + 1;
                }
                showImages(sliderStart, sliderEnd, imgStickers);
                leftSpanNumb.textContent = sliderStart;
                rightSpanNumb.textContent = sliderEnd;
                for(let dot of sliderDots) {
                    if(dot.getAttribute('data-value') < currentDot || dot.getAttribute('data-value') > currentDot) {
                        if(dot.classList.contains('current')){
                            dot.classList.remove('current');
                        }
                    }
                }
            })
        }
        nextSet.addEventListener('click', function() {
            currentDot += 1;
            if(currentDot > numberDots ) {
                currentDot = 1;
            }
            if(rightSpanNumb.textContent == imgStickers.length) {
                sliderStart = 1;
                sliderEnd = step;
            } else if(sliderEnd + step <= imgStickers.length) {
                sliderStart = sliderStart + step;
                sliderEnd = sliderEnd + step;
            } else {
                sliderStart = (imgStickers.length - step) + 1;
                sliderEnd = imgStickers.length;
            }
            showImages(sliderStart, sliderEnd, imgStickers);
            leftSpanNumb.textContent = sliderStart;
            rightSpanNumb.textContent = sliderEnd;
            for(let dot of sliderDots) {
                if(parseInt(dot.getAttribute('data-value')) < currentDot || parseInt(dot.getAttribute('data-value')) > currentDot) {
                    if(dot.classList.contains('current')){
                        dot.classList.remove('current');
                    }
                } else {
                    dot.classList.add('current');
                }
            }
        })
        prevSet.addEventListener('click', function() {
            if(currentDot - 1 < 1) {
                currentDot = numberDots;
            } else {
                currentDot -= 1;
            }
            if(leftSpanNumb.textContent == '1') {
                sliderEnd = imgStickers.length;
                sliderStart = (imgStickers.length - step) + 1;
            } else if(sliderStart - step < 1) {
                sliderStart = 1;
                sliderEnd = step;
            } else {
                sliderEnd = sliderEnd - step;
                sliderStart = sliderStart - step;
            }
            showImages(sliderStart, sliderEnd, imgStickers);
            leftSpanNumb.textContent = sliderStart;
            rightSpanNumb.textContent = sliderEnd;
            for(let dot of sliderDots) {
                if(parseInt(dot.getAttribute('data-value')) < currentDot || parseInt(dot.getAttribute('data-value')) > currentDot) {
                    if(dot.classList.contains('current')){
                        dot.classList.remove('current');
                    }
                } else {
                    dot.classList.add('current');
                }
            }
        })
    }

    // Description Toggle
    const paraToggler = document.querySelector('.descMore');
    if(paraToggler) {
        paraToggler.addEventListener('click', descToggler, false);
    }

    // Metadata collapse button on StoryPage
    const metaBtn = document.querySelector('#meta-collapse-btn');
    const metaContainer = document.querySelector('.js-container');
    const metaStickers = document.querySelectorAll('.meta-sticker');
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
    }

});