<template>
  <button
    class="animated-gradient-btn px-6 py-3 rounded-xl text-white font-semibold shadow-lg hover:scale-105 transition-transform duration-200 cursor-pointer"
  >
    <!-- Use a slot so you can pass any text or icon inside the button -->
    <slot>Animated Button</slot>
  </button>
</template>

<style scoped>
/* 1. Define a custom property for the gradient angle */
@property --gradient-angle {
  syntax: "<angle>";
  initial-value: 0deg;
  inherits: false;
}

/* 2. Create the keyframes to rotate from 0deg to 360deg */
@keyframes spin-gradient {
  0% {
    --gradient-angle: 0deg;
  }
  100% {
    --gradient-angle: 360deg;
  }
}

/* 3. Apply the gradient and animation to the button */
.animated-gradient-btn {
  /* We use Tailwind v4 CSS variables for the colors */
  background: linear-gradient(
    var(--gradient-angle),
    var(--color-indigo-500),
    var(--color-purple-500),
    var(--color-pink-500)
  );
  animation: spin-gradient 4s linear infinite;
}

/* Optional: Speed up the animation on hover for a dynamic feel */
.animated-gradient-btn:hover {
  animation: spin-gradient 2s linear infinite;
}
</style>
