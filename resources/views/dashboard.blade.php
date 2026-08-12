<style>
  body { background-color: #f6f6f6; }
  #chartdiv {
    width: 80%; height: 480px; max-height: 500px; min-height: 500px;
    background-color: #fff; box-shadow: 0 4px 8px rgba(0,0,0,.3);
    border-radius: 8px; padding: 8px; position: relative;
  }
  .chart-label {
    position: absolute; top: 4px; left: 50%; transform: translateX(-50%);
    font-size: 14px; color: #555; font-weight: 500; z-index: 1; white-space: nowrap;
  }
  #chartdiv2 {
    width: 28%; height: 420px; max-height: 420px; min-height: 420px;
    background-color: #fff; box-shadow: 0 4px 8px rgba(0,0,0,.3);
    border-radius: 8px; padding: 15px; display: flex; flex-direction: column;
  }
  .charts-wrapper { display: flex; justify-content: space-between; width: 100%; gap: 2%; margin-top: 0; }
  .history-header { font-size: 18px; font-weight: bold; margin-bottom: 15px; color: #333; padding-bottom: 10px; border-bottom: 1px solid #eee; }
  .history-container { flex: 1; overflow-y: auto; padding-right: 5px; }
  .history-item { display: flex; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #f0f0f0; }
  .history-item:last-child { border-bottom: none; margin-bottom: 0; }
  .history-icon { width: 40px; height: 40px; border-radius: 50%; background-color: #e3f2fd; display: flex; align-items: center; justify-content: center; margin-right: 12px; flex-shrink: 0; }
  .history-content { flex: 1; min-width: 0; }
  .history-title { font-weight: 500; margin-bottom: 3px; color: #333; overflow-wrap: anywhere; }
  .history-desc { font-size: 13px; color: #666; margin-bottom: 5px; overflow-wrap: anywhere; }
  .history-time { font-size: 12px; color: #999; }
  .history-container::-webkit-scrollbar { width: 6px; }
  .history-container::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
  .history-container::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 10px; }
  .history-container::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }
  @media (max-width: 768px) {
    .charts-wrapper { flex-direction: column; gap: 20px; }
    #chartdiv, #chartdiv2 { width: 100% !important; height: 400px !important; min-height: 400px; }
  }
  @media (max-width: 480px) {
    #chartdiv, #chartdiv2 { height: 350px !important; min-height: 350px; }
  }
</style>

<x-layouts.app title="Dashboard">
  @php
    $cardImages = ['doc.gif', 'team.gif', 'box.gif', 'building.gif', 'graph.gif'];
    $trendColors = ['#4CAF50', '#F44336', '#35a7ff', '#f1c50f', '#4CAF50'];
  @endphp

  <div class="flex h-full w-full min-w-0 flex-1 flex-col gap-4 rounded-xl">
    <div class="grid auto-rows-min gap-4 md:grid-cols-5">
      @foreach ($metrics as $index => $metric)
        <div class="relative aspect-video overflow-hidden rounded-xl border-2 border-gray-300" style="background-color: white;">
          <i class="fas fa-building text-white text-4xl absolute right-6 top-1/2 transform -translate-y-1/2 group-hover:text-yellow-400 transition-colors duration-200"></i>
          <div style="flex: 1; padding-right: 80px; display: flex; align-items: center; justify-content: flex-start; padding-top: 10px; padding-bottom: 10px;">
            <div style="flex: 1; padding-left: 10px; min-width: 0;">
              <h3 style="font-size: 18px; color: #333; margin-bottom: 2px;">{{ $metric['label'] }}</h3>
              <p style="font-size: 19px; color: #000; margin-bottom: 8px;">{{ number_format($metric['total']) }}</p>
            </div>
            <div style="width: 50px; height: 50px; border-radius: 50%; background-color: #ffffff; display: flex; align-items: center; justify-content: center; position: absolute; right: 20px; top: 20px; box-shadow: 0 4px 8px rgba(0,0,0,.5);">
              <img src="{{ asset('assets/img/'.$cardImages[$index]) }}" alt="{{ $metric['label'] }} Icon" style="width: 32px; height: 32px;">
            </div>
          </div>
          <div style="flex: 1; padding-left: 10px;">
            <p style="font-size: 13px; color: {{ $metric['change'] < 0 ? '#F44336' : $trendColors[$index] }};">
              {{ $metric['change'] > 0 ? '↑' : ($metric['change'] < 0 ? '↓' : '—') }}
              {{ number_format(abs($metric['change']), 2) }}% from last month
            </p>
          </div>
        </div>
      @endforeach
    </div>

    <div class="chart-container min-w-0">
      <div class="charts-wrapper min-w-0">
        <div id="chartdiv">
          <div class="chart-label">ZPPSU - DTS Document Tracking Analytics</div>
        </div>

        <div id="chartdiv2">
          <div class="history-header">Document History</div>
          <div class="history-container">
            @forelse ($recentActivity as $activity)
              <div class="history-item">
                <div class="history-icon">
                  <img src="{{ asset('assets/img/doc.gif') }}" alt="Document activity" style="width: 24px; height: 24px;">
                </div>
                <div class="history-content">
                  <div class="history-title">Document {{ ucfirst(strtolower($activity->action)) }}</div>
                  <div class="history-desc">
                    {{ $activity->document?->document_number ?? 'Deleted document' }}
                    @if ($activity->user) by {{ $activity->user->name }} @endif
                    @if ($activity->description) — {{ $activity->description }} @endif
                  </div>
                  <div class="history-time">{{ $activity->created_at->diffForHumans() }}</div>
                </div>
              </div>
            @empty
              <div class="history-desc">No document history yet.</div>
            @endforelse
          </div>
        </div>
      </div>
    </div>

    <script src="https://cdn.amcharts.com/lib/5/index.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/xy.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/themes/Animated.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

    <script>
      am5.ready(function () {
        const root = am5.Root.new('chartdiv');
        root._logo?.dispose();
        root.setThemes([am5themes_Animated.new(root)]);

        const chart = root.container.children.push(am5xy.XYChart.new(root, {
          panX: false, panY: false, wheelX: 'none', wheelY: 'none', paddingTop: 25
        }));
        const data = @json($months);
        const xAxis = chart.xAxes.push(am5xy.CategoryAxis.new(root, {
          categoryField: 'label', renderer: am5xy.AxisRendererX.new(root, { minGridDistance: 30 })
        }));
        const yAxis = chart.yAxes.push(am5xy.ValueAxis.new(root, {
          min: 0, renderer: am5xy.AxisRendererY.new(root, {})
        }));
        xAxis.data.setAll(data);

        function addSeries(name, field) {
          const series = chart.series.push(am5xy.ColumnSeries.new(root, {
            name: name, xAxis: xAxis, yAxis: yAxis, valueYField: field,
            categoryXField: 'label', tooltip: am5.Tooltip.new(root, { labelText: name + ': {valueY}' })
          }));
          series.columns.template.setAll({ width: am5.percent(75), cornerRadiusTL: 4, cornerRadiusTR: 4 });
          series.data.setAll(data);
          series.appear(1000);
        }

        addSeries('Internal Documents', 'internal');
        addSeries('External Documents', 'external');
        chart.set('cursor', am5xy.XYCursor.new(root, {}));
        chart.appear(1000, 100);
      });
    </script>
  </div>
</x-layouts.app>
