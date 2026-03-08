@extends('layouts.app')

@section('title', 'Select Kota')

@push('styles')
	<link rel="stylesheet" href="{{ asset('assets/vendors/select2/select2.min.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/vendors/select2-bootstrap-theme/select2-bootstrap.min.css') }}">
	<style>
		.select-kota-uniform {
			background-color: #0d6efd;
			border-color: #0d6efd;
			color: #fff;
		}

		.select-kota-uniform:focus {
			background-color: #0d6efd;
			border-color: #0d6efd;
			color: #fff;
			box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
		}

		.select-kota-uniform-container .select2-selection--single {
			height: calc(2.875rem + 2px) !important;
			background-color: #0d6efd !important;
			border-color: #0d6efd !important;
			color: #fff !important;
			display: flex !important;
			align-items: center !important;
		}

		.select-kota-uniform-container .select2-selection__rendered {
			color: #fff !important;
			line-height: 1.5 !important;
			padding-left: 0.75rem !important;
		}

		.select-kota-uniform-container .select2-selection__placeholder {
			color: rgba(255, 255, 255, 0.85) !important;
		}

		.select-kota-uniform-container .select2-selection__arrow b {
			border-color: #fff transparent transparent transparent !important;
		}
	</style>
@endpush

@section('content')
	<div class="page-header d-flex justify-content-between align-items-center">
		<h3 class="page-title">Select Kota</h3>
		<a href="{{ route('inputbarang.index') }}" class="btn btn-outline-primary">Kembali ke Menu Input</a>
	</div>

	<div class="row g-4">
		<div class="col-12">
			<div class="card">
				<div class="card-header">
					<h4 class="mb-0">Select</h4>
				</div>
				<div class="card-body">
					<div class="row g-3 align-items-center mb-4">
						<label for="kotaInputDefault" class="col-md-2 col-form-label">Kota:</label>
						<div class="col-md-7">
							<input id="kotaInputDefault" type="text" class="form-control" placeholder="Masukkan nama kota">
						</div>
						<div class="col-md-3 text-md-end">
							<button id="addKotaDefault" type="button" class="btn btn-success px-4">Tambahkan</button>
						</div>
					</div>

					<div class="row g-3 align-items-center mb-4">
						<label for="kotaSelectDefault" class="col-md-2 col-form-label">Select Kota:</label>
						<div class="col-md-10">
							<select id="kotaSelectDefault" class="form-select select-kota-uniform">
								<option value="">Pilih kota</option>
							</select>
						</div>
					</div>

					<div class="row g-3 align-items-center">
						<label class="col-md-2 col-form-label">Kota Terpilih:</label>
						<div class="col-md-10">
							<span id="kotaTerpilihDefault" class="fw-semibold">-</span>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="col-12">
			<div class="card">
				<div class="card-header">
					<h4 class="mb-0">select 2</h4>
				</div>
				<div class="card-body">
					<div class="row g-3 align-items-center mb-4">
						<label for="kotaInputSelect2" class="col-md-2 col-form-label">Kota:</label>
						<div class="col-md-7">
							<input id="kotaInputSelect2" type="text" class="form-control" placeholder="Masukkan nama kota">
						</div>
						<div class="col-md-3 text-md-end">
							<button id="addKotaSelect2" type="button" class="btn btn-success px-4">Tambahkan</button>
						</div>
					</div>

					<div class="row g-3 align-items-center mb-4">
						<label for="kotaSelect2" class="col-md-2 col-form-label">Select Kota:</label>
						<div class="col-md-10">
							<select id="kotaSelect2" class="form-select select-kota-uniform">
								<option value="">Pilih kota</option>
							</select>
						</div>
					</div>

					<div class="row g-3 align-items-center">
						<label class="col-md-2 col-form-label">Kota Terpilih:</label>
						<div class="col-md-10">
							<span id="kotaTerpilihSelect2" class="fw-semibold">-</span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@push('scripts')
	<script src="{{ asset('assets/vendors/select2/select2.min.js') }}"></script>
	<script>
		(function () {
			const cards = [
				{
					inputId: 'kotaInputDefault',
					selectId: 'kotaSelectDefault',
					buttonId: 'addKotaDefault',
					outputId: 'kotaTerpilihDefault',
					useSelect2: false,
				},
				{
					inputId: 'kotaInputSelect2',
					selectId: 'kotaSelect2',
					buttonId: 'addKotaSelect2',
					outputId: 'kotaTerpilihSelect2',
					useSelect2: true,
				},
			];

			cards.forEach(function (card) {
				const input = document.getElementById(card.inputId);
				const select = document.getElementById(card.selectId);
				const button = document.getElementById(card.buttonId);
				const output = document.getElementById(card.outputId);

				if (!input || !select || !button || !output) {
					return;
				}

				const updateSelected = function () {
					output.textContent = select.value ? select.value : '-';
				};

				if (card.useSelect2 && window.jQuery && window.jQuery.fn.select2) {
					window.jQuery(select).select2({
						theme: 'bootstrap',
						width: '100%',
						placeholder: 'Pilih kota',
					});

					window.jQuery(select)
						.next('.select2-container')
						.addClass('select-kota-uniform-container');

					window.jQuery(select).on('change', updateSelected);
				} else {
					select.addEventListener('change', updateSelected);
				}

				const addCity = function () {
					const cityName = input.value.trim();

					if (!cityName) {
						input.focus();
						return;
					}

					let selectedOption = Array.from(select.options).find(function (option) {
						return option.value.toLowerCase() === cityName.toLowerCase();
					});

					if (!selectedOption) {
						selectedOption = new Option(cityName, cityName);
						select.add(selectedOption);
					}

					select.value = selectedOption.value;

					if (card.useSelect2 && window.jQuery) {
						window.jQuery(select).trigger('change');
					} else {
						updateSelected();
					}

					input.value = '';
					input.focus();
				};

				button.addEventListener('click', addCity);
				input.addEventListener('keydown', function (event) {
					if (event.key !== 'Enter') {
						return;
					}

					event.preventDefault();
					addCity();
				});

				updateSelected();
			});
		})();
	</script>
@endpush
