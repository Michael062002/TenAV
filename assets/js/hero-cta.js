(function () {
  'use strict';

  var isTouchDevice = window.matchMedia('(hover: none), (pointer: coarse)').matches;

  if (!isTouchDevice) {
    return;
  }

  document.body.classList.add('touch-device');

  var cards = document.querySelectorAll('.cta-card');

  cards.forEach(function (card) {
    card.addEventListener('click', function (event) {
      if (!card.classList.contains('is-active')) {
        event.preventDefault();
        cards.forEach(function (other) {
          if (other !== card) {
            other.classList.remove('is-active');
          }
        });
        card.classList.add('is-active');
      }
    });
  });

  document.addEventListener('click', function (event) {
    if (!event.target.closest('.cta-card')) {
      cards.forEach(function (card) {
        card.classList.remove('is-active');
      });
    }
  });
})();
