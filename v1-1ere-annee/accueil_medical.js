// Burger menu toggle
const burger = document.querySelector('.burger');
const nav = document.querySelector('nav');
const navLinks = document.querySelectorAll('nav .nav-link');

if (burger) {
  burger.addEventListener('click', () => {
    burger.classList.toggle('active');
    nav.classList.toggle('active');
  });

  // Close menu when a link is clicked
  navLinks.forEach(link => {
    link.addEventListener('click', () => {
      burger.classList.remove('active');
      nav.classList.remove('active');
    });
  });
  // Fermer le dropdown quand on clique ailleurs
    document.addEventListener('click', function() {
        const monthList = document.querySelector('.month-list');
        monthList.classList.remove('active');
    });
}
