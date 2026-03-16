@extends('layouts.app')

@section('title', 'Modul 5 - Pilihan Wilayah (Ajax)')

@section('content')
	<div class="page-header d-flex justify-content-between align-items-center">
		<h3 class="page-title">Modul 5 - Pilihan Wilayah (jQuery Ajax)</h3>
		<div class="d-flex gap-2">
			<a href="{{ route('modul5.index') }}" class="btn btn-outline-primary">Kembali ke Menu M5</a>
			<a href="{{ route('modul5.pilihanwilayah.axios') }}" class="btn btn-primary">Buka Versi Axios</a>
		</div>
	</div>

	<div class="alert alert-info" role="alert">
		Sumber data wilayah: <a href="https://github.com/guzfirdaus/Wilayah-Administrasi-Indonesia" target="_blank" rel="noopener">Wilayah Administrasi Indonesia (GitHub)</a>
	</div>

	<div class="card">
		<div class="card-header">
			<h4 class="mb-0">Versi jQuery Ajax</h4>
		</div>
		<div class="card-body">
			<div class="row g-3 align-items-center mb-3">
				<label for="ajaxProvince" class="col-md-2 col-form-label">Provinsi</label>
				<div class="col-md-10">
					<select id="ajaxProvince" class="form-select">
						<option value="0">Pilih Provinsi</option>
					</select>
				</div>
			</div>

			<div class="row g-3 align-items-center mb-3">
				<label for="ajaxCity" class="col-md-2 col-form-label">Kota</label>
				<div class="col-md-10">
					<select id="ajaxCity" class="form-select">
						<option value="0">Pilih Kota</option>
					</select>
				</div>
			</div>

			<div class="row g-3 align-items-center mb-3">
				<label for="ajaxDistrict" class="col-md-2 col-form-label">Kecamatan</label>
				<div class="col-md-10">
					<select id="ajaxDistrict" class="form-select">
						<option value="0">Pilih Kecamatan</option>
					</select>
				</div>
			</div>

			<div class="row g-3 align-items-center">
				<label for="ajaxVillage" class="col-md-2 col-form-label">Kelurahan</label>
				<div class="col-md-10">
					<select id="ajaxVillage" class="form-select">
						<option value="0">Pilih Kelurahan</option>
					</select>
				</div>
			</div>
		</div>
	</div>
@endsection

@push('scripts')
	<script>
		(function () {
			const endpoints = {
				provinces: '{{ route('modul5.provinces') }}',
				regencies: '{{ route('modul5.regencies') }}',
				districts: '{{ route('modul5.districts') }}',
				villages: '{{ route('modul5.villages') }}',
			};

			const placeholders = {
				province: 'Pilih Provinsi',
				city: 'Pilih Kota',
				district: 'Pilih Kecamatan',
				village: 'Pilih Kelurahan',
			};

			const provinceSelect = document.getElementById('ajaxProvince');
			const citySelect = document.getElementById('ajaxCity');
			const districtSelect = document.getElementById('ajaxDistrict');
			const villageSelect = document.getElementById('ajaxVillage');

			if (!provinceSelect || !citySelect || !districtSelect || !villageSelect || !window.jQuery) {
				return;
			}

			const resetSelect = function (selectElement, placeholder) {
				selectElement.innerHTML = '';
				selectElement.add(new Option(placeholder, '0'));
				selectElement.value = '0';
			};

			const setLoadingOption = function (selectElement, text) {
				selectElement.innerHTML = '';
				selectElement.add(new Option(text, '0'));
				selectElement.value = '0';
			};

			const fillSelect = function (selectElement, placeholder, items) {
				resetSelect(selectElement, placeholder);
				items.forEach(function (item) {
					selectElement.add(new Option(item.name, item.id));
				});
			};

			const load = function (url, params) {
				return new Promise(function (resolve, reject) {
					window.jQuery.ajax({
						url: url,
						method: 'GET',
						data: params || {},
						success: function (response) {
							resolve(response || []);
						},
						error: function () {
							reject();
						},
					});
				});
			};

			const loadProvinces = function () {
				setLoadingOption(provinceSelect, 'Memuat Provinsi...');

				load(endpoints.provinces)
					.then(function (items) {
						fillSelect(provinceSelect, placeholders.province, items);
					})
					.catch(function () {
						resetSelect(provinceSelect, placeholders.province);
					});
			};

			provinceSelect.addEventListener('change', function () {
				resetSelect(citySelect, placeholders.city);
				resetSelect(districtSelect, placeholders.district);
				resetSelect(villageSelect, placeholders.village);

				if (provinceSelect.value === '0') {
					return;
				}

				setLoadingOption(citySelect, 'Memuat Kota...');

				load(endpoints.regencies, { province_id: provinceSelect.value })
					.then(function (items) {
						fillSelect(citySelect, placeholders.city, items);
					})
					.catch(function () {
						resetSelect(citySelect, placeholders.city);
					});
			});

			citySelect.addEventListener('change', function () {
				resetSelect(districtSelect, placeholders.district);
				resetSelect(villageSelect, placeholders.village);

				if (citySelect.value === '0') {
					return;
				}

				setLoadingOption(districtSelect, 'Memuat Kecamatan...');

				load(endpoints.districts, { regency_id: citySelect.value })
					.then(function (items) {
						fillSelect(districtSelect, placeholders.district, items);
					})
					.catch(function () {
						resetSelect(districtSelect, placeholders.district);
					});
			});

			districtSelect.addEventListener('change', function () {
				resetSelect(villageSelect, placeholders.village);

				if (districtSelect.value === '0') {
					return;
				}

				setLoadingOption(villageSelect, 'Memuat Kelurahan...');

				load(endpoints.villages, { district_id: districtSelect.value })
					.then(function (items) {
						fillSelect(villageSelect, placeholders.village, items);
					})
					.catch(function () {
						resetSelect(villageSelect, placeholders.village);
					});
			});

			loadProvinces();
		})();
	</script>
@endpush
