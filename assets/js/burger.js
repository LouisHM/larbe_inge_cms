document.addEventListener('DOMContentLoaded', function() {
  document.querySelector('.second-button').addEventListener('click', function () {

    document.querySelector('.animated-icon2').classList.toggle('open');
    document.querySelector('#toggleNav').classList.toggle('navMenu-show');
  });
});
