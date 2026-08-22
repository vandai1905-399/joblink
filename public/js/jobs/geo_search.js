/**
 * public/js/jobs/geo_search.js
 * Xử lý Định Vị GPS, Gợi ý Địa Danh Google Maps & Quét Việc Làm Theo Bán Kính (JobLink Geo)
 */

document.addEventListener('DOMContentLoaded', function() {
    initGeoLocationSearch();
});

function initGeoLocationSearch() {
    const geoContainers = document.querySelectorAll('.geo-search-container');
    
    geoContainers.forEach(container => {
        const locationInput = container.querySelector('.geo-location-input');
        const latInput = container.querySelector('input[name="lat"]');
        const lngInput = container.querySelector('input[name="lng"]');
        const radiusSelect = container.querySelector('select[name="radius"]');
        const btnCurrentLoc = container.querySelector('.btn-get-current-location');
        const suggestionsBox = container.querySelector('.geo-suggestions-dropdown');

        if (!locationInput) return;

        // 1. NÚT LẤY VỊ TRÍ HIỆN TẠI (GPS GEOLOCATION)
        if (btnCurrentLoc) {
            btnCurrentLoc.addEventListener('click', function(e) {
                e.preventDefault();
                if (!navigator.geolocation) {
                    showGeoToast('Trình duyệt của bạn không hỗ trợ định vị GPS.', 'warning');
                    return;
                }

                const originalBtnHtml = btnCurrentLoc.innerHTML;
                btnCurrentLoc.disabled = true;
                btnCurrentLoc.innerHTML = '⏳ <span style="font-size:0.75rem;">Đang định vị...</span>';

                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;

                        if (latInput) latInput.value = lat;
                        if (lngInput) lngInput.value = lng;

                        locationInput.value = '📍 Vị trí GPS hiện tại của tôi';

                        // Nếu chưa chọn bán kính, tự động chọn 10 km
                        if (radiusSelect && (!radiusSelect.value || radiusSelect.value === '0')) {
                            radiusSelect.value = '10';
                        }

                        // Thử lấy tên địa chỉ gần đúng từ OpenStreetMap Reverse Geocoding
                        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=14&addressdetails=1`, {
                            headers: { 'Accept-Language': 'vi,en' }
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data && data.display_name) {
                                const parts = data.display_name.split(',');
                                const shortName = parts.slice(0, 3).join(',').trim();
                                locationInput.value = shortName || '📍 Vị trí hiện tại của tôi';
                            }
                        })
                        .catch(() => {})
                        .finally(() => {
                            btnCurrentLoc.disabled = false;
                            btnCurrentLoc.innerHTML = originalBtnHtml;
                            showGeoToast('Đã xác định vị trí GPS thành công!', 'success');
                            if (suggestionsBox) suggestionsBox.style.display = 'none';
                        });
                    },
                    function(error) {
                        btnCurrentLoc.disabled = false;
                        btnCurrentLoc.innerHTML = originalBtnHtml;
                        let msg = 'Không thể lấy vị trí hiện tại.';
                        if (error.code === error.PERMISSION_DENIED) {
                            msg = 'Bạn đã từ chối quyền truy cập vị trí. Vui lòng cho phép quyền vị trí trên trình duyệt.';
                        } else if (error.code === error.TIMEOUT) {
                            msg = 'Quá thời gian chờ định vị GPS.';
                        }
                        showGeoToast(msg, 'error');
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 60000
                    }
                );
            });
        }

        // 2. GỢI Ý ĐỊA DANH / KHU CÔNG NGHIỆP TRỌNG ĐIỂM (AUTOCOMPLETE)
        if (suggestionsBox) {
            let debounceTimer;

            function fetchSuggestions(query) {
                fetch(`/api/geo/popular-locations?q=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success && data.locations && data.locations.length > 0) {
                            renderSuggestions(data.locations);
                        } else {
                            suggestionsBox.style.display = 'none';
                        }
                    })
                    .catch(() => {
                        suggestionsBox.style.display = 'none';
                    });
            }

            function renderSuggestions(locations) {
                let html = '<div style="padding:0.4rem 0.6rem; font-size:0.75rem; color:#64748b; font-weight:700; border-bottom:1px solid #f1f5f9; background:#f8fafc;">📍 GỢI Ý ĐỊA DANH & KCN TRỌNG ĐIỂM</div>';
                
                locations.forEach(loc => {
                    html += `
                        <div class="geo-suggestion-item" data-lat="${loc.lat}" data-lng="${loc.lng}" data-name="${escapeHtml(loc.name)}" style="padding:0.6rem 0.85rem; cursor:pointer; font-size:0.85rem; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #f8fafc; transition:background 0.15s ease;">
                            <div>
                                <strong style="color:#0f172a;">${escapeHtml(loc.name)}</strong>
                                <div style="font-size:0.75rem; color:#64748b;">${escapeHtml(loc.group || 'Khu vực')}</div>
                            </div>
                            <span style="font-size:0.75rem; color:#0d9488; font-weight:600;">Chọn ↵</span>
                        </div>
                    `;
                });

                suggestionsBox.innerHTML = html;
                suggestionsBox.style.display = 'block';

                suggestionsBox.querySelectorAll('.geo-suggestion-item').forEach(item => {
                    item.addEventListener('click', function() {
                        const name = this.getAttribute('data-name');
                        const lat = this.getAttribute('data-lat');
                        const lng = this.getAttribute('data-lng');

                        locationInput.value = name;
                        if (latInput) latInput.value = lat;
                        if (lngInput) lngInput.value = lng;

                        suggestionsBox.style.display = 'none';

                        // Nếu chưa chọn bán kính, mặc định chọn 15km
                        if (radiusSelect && (!radiusSelect.value || radiusSelect.value === '0')) {
                            radiusSelect.value = '15';
                        }
                    });
                });
            }

            locationInput.addEventListener('focus', function() {
                if (!this.value.trim() || this.value.includes('GPS')) {
                    fetchSuggestions('');
                } else {
                    fetchSuggestions(this.value.trim());
                }
            });

            locationInput.addEventListener('input', function() {
                // Nếu người dùng tự gõ text mới, xóa tọa độ GPS cũ để tính theo địa danh mới
                if (latInput) latInput.value = '';
                if (lngInput) lngInput.value = '';

                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    fetchSuggestions(this.value.trim());
                }, 200);
            });

            document.addEventListener('click', function(e) {
                if (!container.contains(e.target)) {
                    suggestionsBox.style.display = 'none';
                }
            });
        }
    });
}

function showGeoToast(message, type = 'info') {
    if (typeof Toast !== 'undefined') {
        Toast.show(message, type);
    } else {
        alert(message);
    }
}

function escapeHtml(text) {
    if (!text) return '';
    return text.toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}
