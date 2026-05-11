import './bootstrap';
import { Html5QrcodeScanner, Html5QrcodeScanType } from "html5-qrcode";

window.Html5QrcodeScanner = Html5QrcodeScanner;
window.Html5QrcodeScanType = Html5QrcodeScanType;

window.getAccuratePosition = function getAccuratePosition(targetAccuracy = 50, maxWait = 20000) {
	return new Promise((resolve, reject) => {
		if (!navigator.geolocation) {
			reject(new Error('Browser tidak mendukung geolokasi.'));
			return;
		}

		const permissions = navigator.permissions;
		if (permissions && permissions.query) {
			permissions.query({ name: 'geolocation' }).then((result) => {
				if (result.state === 'denied') {
					reject(new Error('Izin lokasi ditolak oleh browser.'));
				}
			}).catch(() => {
				// Abaikan jika Permissions API tidak didukung penuh.
			});
		}

		let bestResult = null;
		let finished = false;
		const startTime = Date.now();

		const finish = (callback, value) => {
			if (finished) {
				return;
			}

			finished = true;
			try {
				navigator.geolocation.clearWatch(watchId);
			} catch (error) {
				console.error(error);
			}
			clearTimeout(fallbackTimer);
			callback(value);
		};

		const fallbackTimer = setTimeout(() => {
			if (finished) {
				return;
			}

			if (bestResult) {
				finish(resolve, bestResult);
				return;
			}

			finish(reject, new Error('Timeout, tidak dapat posisi'));
		}, maxWait);

		const watchId = navigator.geolocation.watchPosition(
			(position) => {
				const acc = position.coords.accuracy;

				if (!bestResult || acc < bestResult.coords.accuracy) {
					bestResult = position;
				}

				if (acc <= targetAccuracy) {
					finish(resolve, bestResult);
				}
			},
			(error) => {
				if (error && error.code === error.TIMEOUT && bestResult) {
					finish(resolve, bestResult);
					return;
				}

				finish(reject, error);
			},
			{ enableHighAccuracy: true, maximumAge: 0, timeout: maxWait }
		);
	});
};
