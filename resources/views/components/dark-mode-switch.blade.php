<!DOCTYPE html>
<html lang="en"
      x-data="{
        darkMode: localStorage.getItem('darkMode') === 'true' ||
                  (localStorage.getItem('darkMode') === null &&
                   window.matchMedia('(prefers-color-scheme: dark)').matches),
        init() {
            this.$watch('darkMode', value => {
                localStorage.setItem('darkMode', value);
                document.documentElement.classList.toggle('dark', value);
                if (window.$flux && $flux.appearance) {
                    $flux.appearance = value ? 'dark' : 'light';
                }
            });
            document.documentElement.classList.toggle('dark', this.darkMode);
        }
      }"
      x-init="init()"
      :class="{ 'dark': darkMode }"
      class="transition-colors duration-300"
>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dark Mode Example</title>

    <!-- Tailwind CSS via CDN for demo -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Alpine.js via CDN -->
    <script src="https://unpkg.com/alpinejs" defer></script>
</head>
<body class="min-h-screen bg-white text-gray-900 dark:bg-gray-900 dark:text-gray-100 transition-colors duration-300">

    <!-- Toggle Bar -->
    <div class="flex items-center justify-between p-4 bg-gray-100 dark:bg-gray-800 shadow-md">
        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
            Quick Toggle
        </span>

        <button @click="darkMode = !darkMode" type="button"
                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-300"
                :class="{
                    'bg-gray-300': !darkMode,
                    'bg-blue-600': darkMode
                }">
            <span class="sr-only">Toggle dark mode</span>
            <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                  :class="{
                      'translate-x-6': darkMode,
                      'translate-x-1': !darkMode
                  }"></span>
        </button>
    </div>

    <!-- Page Content -->
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">Welcome to Dark Mode Page</h1>
        <p>This layout remembers your dark mode preference across all visits.</p>
    </div>

</body>
</html>
