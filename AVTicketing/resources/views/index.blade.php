{{-- To fix errors with includes run mklink /j "C:\xampp\htdocs\avTicketing\AVTicketing\resources\views\node_modules" "C:
\xampp\htdocs\avTicketing\AVTicketing\public\node_modules" substituting the project directory in. --}}
<head>
	<base href="/">
	<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
	<script src="node_modules/core-js/client/shim.min.js"></script>
	<script src="node_modules/zone.js/dist/zone.js"></script>
	<script src="node_modules/reflect-metadata/Reflect.js"></script>
	<script src="node_modules/systemjs/dist/system.src.js"></script>
	<script src="systemjs.config.js"></script>
	<script>
		System.import('app');
	</script>

	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="description" content="A front-end template that helps you build fast, modern mobile web apps.">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0">
	<title>AV Ticketing</title>

	<!-- Add to homescreen for Chrome on Android -->
	<meta name="mobile-web-app-capable" content="yes">
	<link rel="icon" sizes="192x192" href="images/android-desktop.png">

	<!-- Add to homescreen for Safari on iOS -->
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="black">
	<meta name="apple-mobile-web-app-title" content="Material Design Lite">
	<link rel="apple-touch-icon-precomposed" href="images/ios-desktop.png">

	<!-- Tile icon for Win8 (144x144 + tile color) -->
	<meta name="msapplication-TileImage" content="images/touch/ms-touch-icon-144x144-precomposed.png">
	<meta name="msapplication-TileColor" content="#3372DF">

	<link rel="shortcut icon" href="images/favicon.png">

	<!-- SEO: If your mobile URL is different from the desktop URL, add a canonical link to the desktop page https://developers.google.com/webmasters/smartphone-sites/feature-phones -->
	<!--
	<link rel="canonical" href="http://www.example.com/">
	-->

	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:regular,bold,italic,thin,light,bolditalic,black,medium&amp;lang=en">
	<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
	<link rel="stylesheet" href="https://code.getmdl.io/1.2.1/material.cyan-light_blue.min.css">
	<link rel="stylesheet" href="styles.css">
	<style>
		#view-source {
			position: fixed;
			display: block;
			right: 0;
			bottom: 0;
			margin-right: 40px;
			margin-bottom: 40px;
			z-index: 900;
		}
	</style>

</head>
<body>
	<av-ticketing-app></av-ticketing-app>
</body>