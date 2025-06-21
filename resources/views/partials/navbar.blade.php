<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Transparent Navbar</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .transition-bg {
      transition: background-color 0.3s ease;
    }
  </style>
</head>
<body class="relative">

  <!-- Navbar -->
  <nav id="navbar" class="fixed top-0 left-0 w-full z-50 transition-bg bg-transparent text-white">
    <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
      <!-- Logo -->
      <div class="text-xl font-bold">Logo</div>

        <!-- Center -->
     <div class="hidden md:flex gap-6 text-base" id="nav-menu">
        <a href="#" 
            class="nav-link relative text-[#d4af37] after:block after:content-[''] after:h-[1px] after:bg-[#d4af37] after:w-full after:mt-1"
            data-link="Home">Home</a>
        <a href="#" 
            class="nav-link text-[#7d661c]" 
            data-link="About">About</a>
        <a href="#" 
            class="nav-link text-[#7d661c]" 
            data-link="Services">Services</a>
        <a href="#" 
            class="nav-link text-[#7d661c]" 
            data-link="Contact">Contact</a>
        </div>

      <!-- Order Now -->
      <div>
        <button class="font-serif bg-[#7a0c0c] text-[#d4af37] border border-[#d4af37] rounded-full px-6 py-2 text-[16px] font-semibold shadow-xl transition transform hover:translate-y-0.5 active:translate-y-1 hover:shadow-lg active:shadow-sm">
        BOOK NOW
        </button>
      </div>
    </div>
  </nav>

  <script>
    //navbar trans
    const navbar = document.getElementById('navbar');
    window.addEventListener('scroll', () => {
      if (window.scrollY > 10) {
        navbar.classList.remove('bg-transparent');
        navbar.classList.add('bg-black/30');
      } else {
        navbar.classList.add('bg-transparent');
        navbar.classList.remove('bg-black/30');
      }
    });

    //center
    const links = document.querySelectorAll('.nav-link');
    links.forEach(link => {
        link.addEventListener('click', () => {
        links.forEach(el => {
            el.classList.remove('text-[#d4af37]');
            el.classList.add('text-[#7d661c]');
            el.classList.remove('after:block', 'after:content-[\'\']', 'after:h-[1px]', 'after:bg-[#d4af37]', 'after:w-full', 'after:mt-1', 'relative');
        });

        link.classList.add('text-[#d4af37]');
        link.classList.add('relative', 'after:block', 'after:content-[\'\']', 'after:h-[1px]', 'after:bg-[#d4af37]', 'after:w-full', 'after:mt-1');
        });
    });
  </script>

</body>
</html>
