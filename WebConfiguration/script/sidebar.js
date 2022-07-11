var shown = 0;

const navSlide = () => {
  const burger = document.querySelector('.burger');
  const nav = document.querySelector('.nav-links');
  const navLinks = document.querySelectorAll('.nav-links li');
  const navBreakers = document.querySelectorAll('.nav-links hr');
  const alpha = document.querySelector('.alpha');


  burger.addEventListener('click', () => {
    // Toggle Navigation
    nav.classList.toggle('nav-default');
    nav.classList.toggle('nav-active');
    alpha.classList.toggle('alpha-default');
    alpha.classList.toggle('alpha-active');

    if (shown == 0) {
      // Disable Scrolling
      document.body.style.overflow = 'hidden';
      document.querySelector('html').scrollTop = window.scrollY;

      shown = 1;
    } else {
      // Enable Scrolling
      document.body.style.overflow = null;

      shown = 0;
    }

    // Animate Link
    navLinks.forEach((link, index) => {
      if (link.style.animation) {
        link.style.animation = '';
      } else {
        link.style.animation = `navLinkFade 100ms ease forwards`;
      }
    });

    // Animate Breakers
    navBreakers.forEach((breaker, index) => {
      if (breaker.style.animation) {
        breaker.style.animation = '';
      } else {
        breaker.style.animation = `navLinkFade 100ms ease forwards`;
      }
    });

    // Change Burger
    // burger.classList.toggle('close');
  });
}

navSlide();

window.onresize = function() {
  const burger = document.querySelector('.burger');
  const nav = document.querySelector('.nav-links');
  const navLinks = document.querySelectorAll('.nav-links li');
  const navBreakers = document.querySelectorAll('.nav-links hr');
  const alpha = document.querySelector('.alpha');

  if(nav.classList.contains("nav-active")) {
    nav.classList.toggle('nav-default');
    nav.classList.toggle('nav-active');
    alpha.classList.toggle('alpha-default');
    alpha.classList.toggle('alpha-active');

    // Enable Scrolling
    document.body.style.overflow = null;

    shown = 0;

    // Animate Link
    navLinks.forEach((link, index) => {
      if (link.style.animation) {
        link.style.animation = '';
      } else {
        link.style.animation = `navLinkFade 100ms ease forwards`;
      }
    });

    // Animate Breakers
    navBreakers.forEach((breaker, index) => {
      if (breaker.style.animation) {
        breaker.style.animation = '';
      } else {
        breaker.style.animation = `navLinkFade 100ms ease forwards`;
      }
    });

    // Change Burger
    // burger.classList.toggle('close');
  }

};
