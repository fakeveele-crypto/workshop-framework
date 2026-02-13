<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<title>@yield('title', config('app.name', 'App'))</title>

		{{-- Global styles --}}
		@include('layouts.styleglobal')
		{{-- Page styles --}}
		@include('layouts.stylepage')
	</head>
	<body>
		<div class="container-scroller">
			{{-- header / top banner --}}
			@include('layouts.header')

			{{-- navbar --}}
			@include('layouts.navbar')

			<div class="container-fluid page-body-wrapper">
				{{-- sidebar --}}
				@include('layouts.sidebar')

				<div class="main-panel">
					{{-- content area --}}
					@include('layouts.content')

					{{-- footer --}}
					@include('layouts.footer')
				</div>
			</div>
		</div>

		{{-- Global JS --}}
		@include('layouts.javascriptglobal')
		{{-- Page JS --}}
		@include('layouts.javascriptpage')
	</body>
</html>
