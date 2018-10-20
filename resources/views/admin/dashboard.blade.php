@extends('layouts.admin')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css">
@endpush

@section('content')
    <section class="section">
        <div class="container is-fluid">
            <section class="info-tiles">
                <div class="tile is-ancestor has-text-centered">
                    @include('partials.admin.dashboard.tile', ['icon' => 'person',  'value' => formatNumber($today), 'key' => 'visits_today'])
                    @include('partials.admin.dashboard.tile', ['icon' => 'people', 'value' => formatNumber($statistics['total_visits']), 'key' => 'total_visits'])
                    @include('partials.admin.dashboard.tile', ['icon' => 'redo', 'value' => $statistics['averages']['bounce'] . '%', 'key' => 'bounce_rate'])
                    @include('partials.admin.dashboard.tile', ['icon' => 'globe', 'value' => formatNumber($statistics['alexa'][1]), 'key' => 'alexa_world'])
                </div>
            </section>
            <div class="columns">
                <div class="column is-6">
                    <div class="tabs is-boxed" id="tab-header">
                        <ul>
                            @include('partials.admin.dashboard.tab_header', ['isActive' => 'is-active', 'id' => 'pages', 'icon' => 'document'])
                            @include('partials.admin.dashboard.tab_header', ['id' => 'keywords', 'icon' => 'key'])
                            @include('partials.admin.dashboard.tab_header', ['id' => 'entrance-pages', 'icon' => 'log-in'])
                            @include('partials.admin.dashboard.tab_header', ['id' => 'exit-pages', 'icon' => 'log-out'])
                            @include('partials.admin.dashboard.tab_header', ['id' => 'time-pages', 'icon' => 'clock'])
                            @include('partials.admin.dashboard.tab_header', ['id' => 'traffic-sources', 'icon' => 'bulb'])
                            @include('partials.admin.dashboard.tab_header', ['id' => 'browsers', 'icon' => 'browsers'])
                            @include('partials.admin.dashboard.tab_header', ['id' => 'os', 'icon' => 'laptop'])
                        </ul>
                    </div>
                    <div id="tab-container">
                        @include('partials.admin.dashboard.tab_box', ['isActive' => 'is-active', 'id' => 'pages', 'data' => $statistics['pages'], 'key' => 'url', 'value' => 'pageViews'])
                        @include('partials.admin.dashboard.tab_box', ['id' => 'keywords', 'data' => $statistics['keywords'], 'key' => 'keyword', 'value' => 'sessions'])
                        @include('partials.admin.dashboard.tab_box', ['id' => 'entrance-pages', 'data' => $statistics['landings'], 'key' => 'path', 'value' => 'visits'])
                        @include('partials.admin.dashboard.tab_box', ['id' => 'exit-pages', 'data' => $statistics['exits'], 'key' => 'path', 'value' => 'visits'])
                        @include('partials.admin.dashboard.tab_box', ['id' => 'time-pages', 'data' => $statistics['times'], 'key' => 'path', 'value' => 'time', 'isDate' => true])
                        @include('partials.admin.dashboard.tab_box', ['id' => 'traffic-sources', 'data' => $statistics['sources'], 'key' => 'path', 'value' => 'visits'])
                        @include('partials.admin.dashboard.tab_box', ['id' => 'browsers', 'data' => $statistics['browsers'], 'key' => 'browser', 'value' => 'visits'])
                        @include('partials.admin.dashboard.tab_box', ['id' => 'os', 'data' => $statistics['os'], 'key' => 'os', 'value' => 'visits'])
                    </div>
                </div>
                <div class="column is-6">
                    <div class="card">
                        <header class="card-header"><p class="card-header-title">{{ __('admin.fields.dashboard.visits') }}</p></header>
                        <div class="card-content"><div class="chart right-charts" id="visitor-chart"></div></div>
                    </div>
                    <div class="card">
                        <header class="card-header"><p class="card-header-title">{{ __('admin.fields.dashboard.region_visitors') }}</p></header>
                        <div class="card-content"><div id="region-map"></div></div>
                    </div>
                </div>
            </div>
            <div class="card">
                <header class="card-header"><p class="card-header-title">{{ __('admin.fields.dashboard.world_visitors') }}</p></header>
                <div class="card-content"><div id="world-map"></div></div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.2.0/raphael-min.js"></script>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script>
      $('#tab-header ul li').on('click', function() {
        $('#tab-header ul li').removeClass('is-active');
        $('#tab-container .container-item').removeClass('is-active');
        $(this).addClass('is-active');
        $($(this).data('href')).addClass('is-active');
      });
      $(function() {
        Morris.Line({
          element: 'visitor-chart',
          data: {!! $statistics['visits'] !!},
          xkey: 'date',
          ykeys: ['visits'],
          labels: ['{{ __('admin.fields.dashboard.visits') }}'],
          lineColors: ['#3B525E'],
          gridTextColor: ['#4a4a4a'],
          hideHover: 'auto',
          resize: true,
          redraw: true
        });
      });
      google.charts.load("visualization", "1", {packages:["geochart"], mapsApiKey: '{{ env('GOOGLE_MAPS_API_KEY') }}'});
      google.charts.setOnLoadCallback(drawRegionsMap);
      google.charts.setOnLoadCallback(drawLocalRegionsMap);
      function drawRegionsMap() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', '{{ __('admin.fields.dashboard.chart_country') }}');
        data.addColumn('number', '{{ __('admin.fields.dashboard.chart_visitors') }}');
        data.addRows({!! $statistics['countries'] !!});
        var options = {
          colors:['#c8e0ed','#24536e'],
          backgroundColor: '#f9f9f9',
          datalessRegionColor: '#e5e5e5',
          legend:  {textStyle: {fontName: 'sans-serif'}}
        };
        var chart = new google.visualization.GeoChart(document.getElementById('world-map'));
        chart.draw(data, options);
      }
      function drawLocalRegionsMap(){
        var data = new google.visualization.DataTable();
        data.addColumn('string', '{{ __('admin.fields.dashboard.chart_region') }}');
        data.addColumn('number', '{{ __('admin.fields.dashboard.chart_visitors') }}');
        data.addRows({!! $statistics['regions'] !!});
        var options = {
          colorAxis: {colors: ['#92c1dc', '#2d688a']},
          backgroundColor: '#55a9bc',
          legend:  {textStyle: {color: '#000', fontName: 'sans-serif'}},
          displayMode: 'markers',
          region: '{{  env('GOOGLE_ANALYTICS_COUNTRY_CODE') }}'
        };
        var chart = new google.visualization.GeoChart(document.getElementById('region-map'));
        chart.draw(data, options);
      }
    </script>
@endsection
