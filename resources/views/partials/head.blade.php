<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ 'DTS' }}</title>

@include('partials.form-readiness')

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" media="print" onload="this.media='all'" />

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
