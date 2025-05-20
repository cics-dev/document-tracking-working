<style>
  /* CSS to make the pie chart container flexible and positionable */
  .pie-chart-container {
    width: 100%;  /* Ensures the chart takes up the full width of the container */
    margin: 0 auto;  /* Centers the pie chart within its container */
    position: relative;  /* Allows for absolute positioning if needed */
  }

  /* Optional: Add responsiveness and control positioning */
  .card-body {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 50vh; /* Full screen height to center the chart vertically */
  }

  /* If you want to add custom position, you can override with absolute positioning */
  /* Example: */
  .pie-chart-container {
    position: absolute;
    top: 400%;  /* Centers vertically */
    left: 50%;  /* Centers horizontally */
    transform: translate(-50%, -50%);  /* Exact centering */
  }
</style>
<x-layouts.app title="Dashboard">
  <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <div class="grid auto-rows-min gap-4 md:grid-cols-5">
      <!-- Make sure to include Font Awesome CDN in your HTML head if it's not already included -->
      <head>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
      </head>

      <!-- Blue Box with home icon -->
      <div class="relative aspect-video overflow-hidden rounded-xl border border-blue-500 bg-blue-500">
        <x-placeholder-pattern class="absolute inset-0 size-full stroke-blue-500/20" />
        <!-- Home Icon on the right center -->
        <i class="fas fa-building text-white text-4xl absolute right-6 top-1/2 transform -translate-y-1/2 group-hover:text-yellow-400 transition-colors duration-200"></i>
        <!-- Number 219 -->
        <span class="absolute left-6 top-6 text-white text-2xl font-bold">219</span>
        <!-- Name "Offices" -->
        <span class="absolute left-6 bottom-6 text-white text-xl font-bold">Offices</span>
      </div>

      <!-- Green Box -->
      <div class="relative aspect-video overflow-hidden rounded-xl border border-green-500 bg-green-500">
        <x-placeholder-pattern class="absolute inset-0 size-full stroke-green-500/20" />
        <!-- Icon on the right center -->
        <i class="fas fa-users text-white text-4xl absolute right-4 top-1/2 transform -translate-y-1/2 group-hover:text-yellow-400 transition-colors duration-200"></i>
        <!-- Number 219 -->
        <span class="absolute left-6 top-6 text-white text-2xl font-bold">580</span>
        <!-- Name "Users" -->
        <span class="absolute left-6 bottom-6 text-white text-xl font-bold">Users</span>
      </div>

      <!-- Orange Box -->
      <div class="relative aspect-video overflow-hidden rounded-xl border border-orange-500 bg-orange-500">
        <x-placeholder-pattern class="absolute inset-0 size-full stroke-orange-500/20" />
        <!-- Icon on the right center -->
        <i class="fas fa-folder-open text-white text-4xl absolute right-4 top-1/2 transform -translate-y-1/2 group-hover:text-yellow-400 transition-colors duration-200"></i>
        <!-- Number 219 -->
        <span class="absolute left-6 top-6 text-white text-2xl font-bold">390</span>
        <!-- Name "Pen" -->
        <span class="absolute left-6 bottom-6 text-white text-xl font-bold">Documents</span>
      </div>

      <!-- Red Box -->
      <div class="relative aspect-video overflow-hidden rounded-xl border border-red-500 bg-red-500">
        <x-placeholder-pattern class="absolute inset-0 size-full stroke-red-500/20" />
        <!-- Icon on the right center -->
        <i class="fas fa-book text-white text-4xl absolute right-4 top-1/2 transform -translate-y-1/2 group-hover:text-yellow-400 transition-colors duration-200"></i>
        <!-- Number 219 -->
        <span class="absolute left-6 top-6 text-white text-2xl font-bold">285</span>
        <!-- Name "Files" -->
        <span class="absolute left-6 bottom-6 text-white text-xl font-bold">Files</span>
      </div>

      <!-- Yellow Box -->
      <div class="relative aspect-video overflow-hidden rounded-xl border border-yellow-500 bg-yellow-500">
        <x-placeholder-pattern class="absolute inset-0 size-full stroke-yellow-500/20" />
        <!-- Icon on the right center -->
        <i class="fas fa-chart-line text-white text-4xl absolute right-4 top-1/2 transform -translate-y-1/2 group-hover:text-yellow-400 transition-colors duration-200"></i>
        <!-- Number 219 -->
        <span class="absolute left-6 top-6 text-white text-2xl font-bold">219</span>
        <!-- Name "Home" -->
        <span class="absolute left-6 bottom-6 text-white text-xl font-bold">Pending</span>
      </div>
    </div>

    <!-- resources/views/dashboard.blade.php -->

    <div class="flex gap-8"> <!-- Flex container for the two charts side by side -->
      <!-- Left Chart (Line Chart) -->
      <div class="flex-3">
        <div class="relative aspect-video overflow-hidden rounded-xl border border-white bg-white dark:border-white dark:bg-white">
          <x-placeholder-pattern class="absolute inset-0 size-full stroke-white/20 dark:stroke-white/20" />
          <!-- Canvas for Chart.js -->
          <canvas id="myChart" class="w-full h-full"></canvas>
        </div>
      </div>

      <!-- Right Chart (Pie Chart) -->
      <div class="flex-1"> <!--Adjust the size of the Chart.js--->
        <div class="card-body">
          <div class="row">
            <div class="col-12">
              <div id="pie-chart" style="height: 195px;"></div> <!-- Adjust height here -->
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>

  <!-- Chart.js Script -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    var ctx = document.getElementById('myChart').getContext('2d');
    var myChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
        datasets: [{
          label: 'Document Tracking Dataset',
          data: [65, 59, 80, 81, 56, 55],
          fill: false,
          borderColor: 'rgb(75, 192, 192)',
          tension: 0.1
        }]
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });
  </script>

  <!-- ApexCharts Script -->
  <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js" crossorigin="anonymous"></script>
  <script>
    const pie_chart_options = {
      series: [700, 500, 400, 600, 300, 100],
      chart: {
        type: 'donut',
        height: 200, // Adjust height here for smaller or bigger pie chart
      },
      labels: ['Offices', 'Users', 'Documents', 'Files', 'Pending', 'All Files'],
      dataLabels: {
        enabled: false,
      },
      colors: ['#0d6efd', '#20c997', '#ffc107', '#d63384', '#6f42c1', '#adb5bd'],
      legend: {
        position: 'right',
        floating: false,
        offsetX: 0,
        offsetY: 0,
        labels: {
          useSeriesColors: true,
        },
      },
    };

    const pie_chart = new ApexCharts(document.querySelector('#pie-chart'), pie_chart_options);
    pie_chart.render();
  </script>

</x-layouts.app>
