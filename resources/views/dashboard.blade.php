<style>
   body {
    background-color: #f6f6f6;
  }
  .card-body {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 50vh;
  }

  /* First chart styles */
  #chartdiv {
    width: 80%;
    height: 480px;
    max-height: 500px;
    min-height: 500px;
    background-color: #ffffff;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
    border-radius: 8px;
    padding: 8px;
    position: relative; /* Needed for positioning the label inside */
  }

  .chart-label {
    position: absolute;
    top: 4px;
    left: 50%;
    transform: translateX(-50%);
    font-size: 14px;
    color: #555;
    font-weight: 500;
  }

  /* Second chart styles */
  #chartdiv2 {
    width: 28%; /* Narrower than chartdiv */
    height: 420px; /* Shorter than chartdiv */
    max-height: 420px;
    min-height: 420px;
    background-color: #ffffff;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
    border-radius: 8px;
    padding: 15px;
    display: flex;
    flex-direction: column;
  }

  .charts-wrapper {
    display: flex;
    justify-content: space-between;
    width: 100%;
    gap: 2%;
    margin-top: 0px; /* Reduce and Remove the top margin completely */
  }

  /* Document History Panel Styles */
  .history-header {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 15px;
    color: #333;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
  }

  .history-container {
    flex: 1;
    overflow-y: auto;
    padding-right: 5px;
  }

  .history-item {
    display: flex;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #f0f0f0;
  }

  .history-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
  }

  .history-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #e3f2fd;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 12px;
    flex-shrink: 0;
  }

  .history-icon i {
    color: #1976d2;
    font-size: 18px;
  }

  .history-content {
    flex: 1;
  }

  .history-title {
    font-weight: 500;
    margin-bottom: 3px;
    color: #333;
  }

  .history-desc {
    font-size: 13px;
    color: #666;
    margin-bottom: 5px;
  }

  .history-time {
    font-size: 12px;
    color: #999;
  }

  /* Scrollbar styling */
  .history-container::-webkit-scrollbar {
    width: 6px;
  }

  .history-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
  }

  .history-container::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 10px;
  }

  .history-container::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
  }

  @media (max-width: 768px) {
    .charts-wrapper {
      flex-direction: column;
      gap: 20px;
    }
    #chartdiv, #chartdiv2 {
      width: 100% !important;
      height: 400px !important;
    }
  }

  @media (max-width: 480px) {
    #chartdiv, #chartdiv2 {
      height: 350px !important;
    }
  }
</style>

<x-layouts.app title="Dashboard">
  <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <div class="grid auto-rows-min gap-4 md:grid-cols-5">
      <!-- Your 5 cards here (same as before) -->
      <!-- Blue Box -->
      <div class="relative aspect-video overflow-hidden rounded-xl border-2 border-gray-300" style="background-color: white;">
        <i class="fas fa-building text-white text-4xl absolute right-6 top-1/2 transform -translate-y-1/2 group-hover:text-yellow-400 transition-colors duration-200"></i>
        <div style="flex: 1; padding-right: 80px; display: flex; align-items: center; justify-content: flex-start; padding-top: 10px; padding-bottom: 10px;">
          <div style="flex: 1; padding-left: 10px;">
            <h3 id="document-heading" style="font-size: 18px; color: #333; margin-bottom: 2px;">Documents</h3>
            <p style="font-size: 19px; color: #000; margin-bottom: 8px;">350,897</p>
          </div>
          <div style="width: 50px; height: 50px; border-radius: 50%; background-color: #ffffff; display: flex; align-items: center; justify-content: center; position: absolute; right: 20px; top: 20px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);">
            <img src="https://cdn-icons-gif.flaticon.com/13099/13099823.gif" alt="Traffic Icon" style="width: 32px; height: 32px;">
          </div>
        </div>
        <div style="flex: 1; padding-left: 10px;">
            <p style="font-size: 13px; color: #4CAF50;">↑ 3.48% from last month</p>
            <p style="font-size: 19px; color: #000; margin-bottom: 8px;">350,897</p>
        </div>
      </div>

      <!-- Green Box -->
      <div class="relative aspect-video overflow-hidden rounded-xl border-2 border-gray-300" style="background-color: white;">
        <i class="fas fa-building text-white text-4xl absolute right-6 top-1/2 transform -translate-y-1/2 group-hover:text-yellow-400 transition-colors duration-200"></i>
        <div style="flex: 1; padding-right: 80px; display: flex; align-items: center; justify-content: flex-start; padding-top: 10px; padding-bottom: 10px;">
          <div style="flex: 1; padding-left: 10px;">
            <h3 id="document-heading" style="font-size: 18px; color: #333; margin-bottom: 2px;">Users</h3>
            <p style="font-size: 19px; color: #000; margin-bottom: 8px;">266</p>
          </div>
          <div style="width: 50px; height: 50px; border-radius: 50%; background-color: #ffffff; display: flex; align-items: center; justify-content: center; position: absolute; right: 20px; top: 20px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);">
            <img src="https://cdn-icons-gif.flaticon.com/7211/7211817.gif" alt="Traffic Icon" style="width: 32px; height: 32px;">
          </div>
        </div>
        <div style="flex: 1; padding-left: 10px;">
            <p style="font-size: 13px; color: #F44336;">↓ 1.26% from last month</p>
        </div>
      </div>

      <!-- Orange Box -->
      <div class="relative aspect-video overflow-hidden rounded-xl border-2 border-gray-300" style="background-color: white;">
        <i class="fas fa-building text-white text-4xl absolute right-6 top-1/2 transform -translate-y-1/2 group-hover:text-yellow-400 transition-colors duration-200"></i>
        <div style="flex: 1; padding-right: 80px; display: flex; align-items: center; justify-content: flex-start; padding-top: 10px; padding-bottom: 10px;">
          <div style="flex: 1; padding-left: 10px;">
            <h3 id="document-heading" style="font-size: 18px; color: #333; margin-bottom: 2px;">Files</h3>
            <p style="font-size: 19px; color: #000; margin-bottom: 8px;">350,897</p>
          </div>
          <div style="width: 50px; height: 50px; border-radius: 50%; background-color: #ffffff; display: flex; align-items: center; justify-content: center; position: absolute; right: 20px; top: 20px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);">
            <img src="https://cdn-icons-gif.flaticon.com/15309/15309697.gif" alt="Traffic Icon" style="width: 32px; height: 32px;">
          </div>
        </div>
        <div style="flex: 1; padding-left: 10px;">
            <p style="font-size: 13px; color: #35a7ff;">↑ 3.48% from last month</p>
        </div>
      </div>

      <!-- Red Box -->
      <div class="relative aspect-video overflow-hidden rounded-xl border-2 border-gray-300" style="background-color: white;">
        <i class="fas fa-building text-white text-4xl absolute right-6 top-1/2 transform -translate-y-1/2 group-hover:text-yellow-400 transition-colors duration-200"></i>
        <div style="flex: 1; padding-right: 80px; display: flex; align-items: center; justify-content: flex-start; padding-top: 10px; padding-bottom: 10px;">
          <div style="flex: 1; padding-left: 10px;">
            <h3 id="document-heading" style="font-size: 18px; color: #333; margin-bottom: 2px;">Offices</h3>
            <p style="font-size: 19px; color: #000; margin-bottom: 8px;">281</p>
          </div>
          <div style="width: 50px; height: 50px; border-radius: 50%; background-color: #ffffff; display: flex; align-items: center; justify-content: center; position: absolute; right: 20px; top: 20px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);">
            <img src="https://cdn-icons-gif.flaticon.com/8722/8722580.gif" alt="Traffic Icon" style="width: 32px; height: 32px;">
          </div>
        </div>
        <div style="flex: 1; padding-left: 10px;">
            <p style="font-size: 13px; color: #f1c50f;">↓ 1.02% from last month</p>
        </div>
      </div>

      <!-- Yellow Box -->
      <div class="relative aspect-video overflow-hidden rounded-xl border-2 border-gray-300" style="background-color: white;">
        <i class="fas fa-building text-white text-4xl absolute right-6 top-1/2 transform -translate-y-1/2 group-hover:text-yellow-400 transition-colors duration-200"></i>
        <div style="flex: 1; padding-right: 80px; display: flex; align-items: center; justify-content: flex-start; padding-top: 10px; padding-bottom: 10px;">
          <div style="flex: 1; padding-left: 10px;">
            <h3 id="document-heading" style="font-size: 18px; color: #333; margin-bottom: 2px;">Recieved</h3>
            <p style="font-size: 19px; color: #000; margin-bottom: 8px;">350,897</p>
          </div>
          <div style="width: 50px; height: 50px; border-radius: 50%; background-color: #ffffff; display: flex; align-items: center; justify-content: center; position: absolute; right: 20px; top: 20px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);">
            <img src="https://cdn-icons-gif.flaticon.com/7211/7211797.gif" alt="Traffic Icon" style="width: 32px; height: 32px;">
          </div>
        </div>
        <div style="flex: 1; padding-left: 10px;">
            <p style="font-size: 13px; color: #4CAF50;">↑ 3.48% from last month</p>
        </div>
      </div>
    </div>

    <!-- Charts Container -->
    <div class="chart-container">
      <div class="charts-wrapper">
        <div id="chartdiv">
         <div class="chart-label">ZPPSU - DTS Document Trackig Analytics</div>
          </div>
       <div id="chartdiv2">
  <div class="history-header">Document History</div>
  <div class="history-container">
    <div class="history-item">
      <div class="history-icon">
        <img src="https://cdn-icons-png.flaticon.com/128/4138/4138774.png" alt="Document Icon" style="width: 24px; height: 24px;">
      </div>
      <div class="history-content">
        <div class="history-title">New Document Created</div>
        <div class="history-desc">Project Proposal.docx</div>
        <div class="history-time">Just now</div>
      </div>
    </div>
    <div class="history-item">
      <div class="history-icon">
        <img src="https://cdn-icons-png.flaticon.com/128/3006/3006899.png" alt="Document Icon" style="width: 24px; height: 24px;">
      </div>
      <div class="history-content">
        <div class="history-title">Document Uploaded</div>
        <div class="history-desc">Annual Report 2025.pdf</div>
        <div class="history-time">2 minutes ago</div>
      </div>
    </div>
    <div class="history-item">
      <div class="history-icon">
        <img src="https://cdn-icons-png.flaticon.com/128/16683/16683419.png" alt="Document Icon" style="width: 24px; height: 24px;">
      </div>
      <div class="history-content">
        <div class="history-title">Document Edited</div>
        <div class="history-desc">Project Proposal.pptx</div>
        <div class="history-time">1 hour ago</div>
      </div>
    </div>
    <div class="history-item">
      <div class="history-icon">
         <img src="https://cdn-icons-png.flaticon.com/128/16683/16683439.png" alt="Document Icon" style="width: 24px; height: 24px;">
      </div>
      <div class="history-content">
        <div class="history-title">Document Shared</div>
        <div class="history-desc">Budget Plan.xlsx with Finance Team</div>
        <div class="history-time">3 hours ago</div>
      </div>
    </div>
    <div class="history-item">
      <div class="history-icon">
        <img src="https://cdn-icons-png.flaticon.com/128/14178/14178961.png" alt="Document Icon" style="width: 24px; height: 24px;">
      </div>
      <div class="history-content">
        <div class="history-title">Document Approved</div>
        <div class="history-desc">Contract Agreement.docx</div>
        <div class="history-time">Yesterday, 2:45 PM</div>
      </div>
    </div>
    <div class="history-item">
      <div class="history-icon">
        <img src="https://cdn-icons-png.flaticon.com/128/4138/4138853.png" alt="Document Icon" style="width: 24px; height: 24px;">
      </div>
      <div class="history-content">
        <div class="history-title">Document Downloaded</div>
        <div class="history-desc">Meeting Minutes.pdf by John Doe</div>
        <div class="history-time">Yesterday, 10:30 AM</div>
      </div>
    </div>
    <div class="history-item">
      <div class="history-icon">
        <img src="https://cdn-icons-png.flaticon.com/128/11107/11107601.png" alt="Document Icon" style="width: 24px; height: 24px;">
      </div>
      <div class="history-content">
        <div class="history-title">Document Archived</div>
        <div class="history-desc">Q1 Financial Report.xlsx</div>
        <div class="history-time">May 28, 2025</div>
      </div>
    </div>
  </div>
</div>

  <!-- amCharts Scripts -->
  <script src="https://cdn.amcharts.com/lib/5/index.js"></script>
  <script src="https://cdn.amcharts.com/lib/5/xy.js"></script>
  <script src="https://cdn.amcharts.com/lib/5/themes/Animated.js"></script>

  <!-- Font Awesome for icons -->
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

  <!-- Chart Script -->
  <script>
    am5.ready(function() {
      // Create first chart
      createChart("chartdiv");
    });

    function createChart(divId) {
      var root = am5.Root.new(divId);
      root._logo.set("scale", 0.0);
      root._logo.set("paddingTop", 1);
      root._logo.set("paddingRight", 1);
      root._logo.set("opacity", 0.01);
      root.setThemes([am5themes_Animated.new(root)]);

      var chart = root.container.children.push(
        am5xy.XYChart.new(root, {
          panX: false,
          panY: false,
          wheelX: "none",
          wheelY: "none",
          paddingLeft: 0
        })
      );

      var cursor = chart.set("cursor", am5xy.XYCursor.new(root, {}));
      cursor.lineY.set("visible", false);

      var xRenderer = am5xy.AxisRendererX.new(root, {
        minGridDistance: 30,
        minorGridEnabled: true
      });
      xRenderer.labels.template.setAll({ text: "{realName}" });

      var xAxis = chart.xAxes.push(am5xy.CategoryAxis.new(root, {
        maxDeviation: 0,
        categoryField: "category",
        renderer: xRenderer,
        tooltip: am5.Tooltip.new(root, { labelText: "{realName}" })
      }));

      var yAxis = chart.yAxes.push(am5xy.ValueAxis.new(root, {
        maxDeviation: 0.3,
        renderer: am5xy.AxisRendererY.new(root, {})
      }));

      var yAxis2 = chart.yAxes.push(am5xy.ValueAxis.new(root, {
        maxDeviation: 0.3,
        syncWithAxis: yAxis,
        renderer: am5xy.AxisRendererY.new(root, { opposite: true })
      }));

      var series = chart.series.push(am5xy.ColumnSeries.new(root, {
        name: "Series 1",
        xAxis: xAxis,
        yAxis: yAxis2,
        valueYField: "value",
        sequencedInterpolation: true,
        categoryXField: "category",
        tooltip: am5.Tooltip.new(root, {
          labelText: "{provider} {realName}: {valueY}"
        })
      }));

      series.columns.template.setAll({
        fillOpacity: 0.9,
        strokeOpacity: 0
      });

      series.columns.template.adapters.add("fill", (fill, target) =>
        chart.get("colors").getIndex(series.columns.indexOf(target))
      );
      series.columns.template.adapters.add("stroke", (stroke, target) =>
        chart.get("colors").getIndex(series.columns.indexOf(target))
      );

      var lineSeries = chart.series.push(am5xy.LineSeries.new(root, {
        name: "Series 2",
        xAxis: xAxis,
        yAxis: yAxis,
        valueYField: "quantity",
        sequencedInterpolation: true,
        stroke: chart.get("colors").getIndex(13),
        fill: chart.get("colors").getIndex(13),
        categoryXField: "category",
        tooltip: am5.Tooltip.new(root, { labelText: "{valueY}" })
      }));

      lineSeries.strokes.template.set("strokeWidth", 2);

      lineSeries.bullets.push(function() {
        return am5.Bullet.new(root, {
          locationY: 1,
          sprite: am5.Circle.new(root, {
            radius: 5,
            fill: lineSeries.get("fill")
          })
        });
      });

      lineSeries.events.on("datavalidated", function() {
        am5.array.each(lineSeries.dataItems, function(dataItem) {
          dataItem.set("locationX",
            dataItem.dataContext.count % 2 === 0 ? 0 : 0.5
          );
        });
      });

      var chartData = [];

      var data = {
        "Documents": {
          "2022": 5,
          "2023": 20,
          "2024": 15,
          "2025": 25,
          quantity: 430
        },
        "Offices": {
          "2020": 15,
          "2022": 21,
          "2024": 18,
          "2025": 22,
          quantity: 210
        },
        "Departments": {
          "2021": 25,
          "2022": 11,
          "2023": 17,
          "2024": 30,
          "2025": 20,
          quantity: 265
        },
        "Office Heads": {
          "2022": 12,
          "2023": 15,
          "2024": 10,
          "2025": 8,
          quantity: 98
        }
      };

      for (var providerName in data) {
        var providerData = data[providerName];
        var tempArray = [];
        var count = 0;

        for (var itemName in providerData) {
          if (itemName !== "quantity") {
            count++;
            tempArray.push({
              category: providerName + "_" + itemName,
              realName: itemName,
              value: providerData[itemName],
              provider: providerName
            });
          }
        }

        tempArray.sort((a, b) => a.value - b.value);
        var midIndex = Math.floor(count / 2);
        tempArray[midIndex].quantity = providerData.quantity;
        tempArray[midIndex].count = count;

        am5.array.each(tempArray, item => chartData.push(item));

        var range = xAxis.makeDataItem({});
        xAxis.createAxisRange(range);
        range.set("category", tempArray[0].category);
        range.set("endCategory", tempArray[tempArray.length - 1].category);
        range.get("label").setAll({
          text: tempArray[0].provider,
          dy: 30,
          fontWeight: "bold",
          tooltipText: tempArray[0].provider
        });
        range.get("tick").setAll({ visible: true, strokeOpacity: 1, length: 50, location: 0 });
        range.get("grid").setAll({ strokeOpacity: 1 });
      }

      var finalRange = xAxis.makeDataItem({});
      xAxis.createAxisRange(finalRange);
      finalRange.set("category", chartData[chartData.length - 1].category);
      finalRange.get("tick").setAll({ visible: true, strokeOpacity: 1, length: 50, location: 1 });
      finalRange.get("grid").setAll({ strokeOpacity: 1, location: 1 });

      xAxis.data.setAll(chartData);
      series.data.setAll(chartData);
      lineSeries.data.setAll(chartData);

      series.appear(1000);
      chart.appear(1000, 100);
    }
  </script>
</x-layouts.app>