/**
 * public/js/jobs/geo_search.js
 * Xử lý Định Vị GPS Tự Động, Gợi ý Địa Danh Google Maps & Quét Việc Làm Theo Bán Kính (JobLink Geo)
 */

(function() {
    const STORAGE_KEYS = {
        ALLOWED: 'joblink_geo_allowed',
        LAT: 'joblink_user_lat',
        LNG: 'joblink_user_lng',
        NAME: 'joblink_user_location_name',
        UPDATED_AT: 'joblink_geo_updated_at'
    };

    // Danh sách tọa độ tham chiếu dự phòng khi reverse geocoding không khả dụng
    const FALLBACK_CITIES = [
        { name: 'TP. Thủ Dầu Một, Bình Dương', lat: 10.9804, lng: 106.6519 },
        { name: 'TP. Thuận An, Bình Dương', lat: 10.9234, lng: 106.6975 },
        { name: 'TP. Dĩ An, Bình Dương', lat: 10.9069, lng: 106.7725 },
        { name: 'TP. Bến Cát, Bình Dương', lat: 11.1350, lng: 106.6022 },
        { name: 'TP. Tân Uyên, Bình Dương', lat: 11.0827, lng: 106.7719 },
        { name: 'Huyện Bàu Bàng, Bình Dương', lat: 11.2727, lng: 106.5936 },
        { name: 'TP. Hồ Chí Minh', lat: 10.7769, lng: 106.7009 },
        { name: 'TP. Thủ Đức, TP.HCM', lat: 10.8494, lng: 106.7537 },
        { name: 'TP. Biên Hòa, Đồng Nai', lat: 10.9574, lng: 106.8427 }
    ];

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initGeoLocationSearch);
    } else {
        initGeoLocationSearch();
    }

    /**
     * Lấy dữ liệu GPS đã lưu trong bộ nhớ trình duyệt
     */
    function getStoredGeo() {
        try {
            const isAllowed = localStorage.getItem(STORAGE_KEYS.ALLOWED) === '1';
            const lat = parseFloat(localStorage.getItem(STORAGE_KEYS.LAT));
            const lng = parseFloat(localStorage.getItem(STORAGE_KEYS.LNG));
            const name = localStorage.getItem(STORAGE_KEYS.NAME) || '';
            const time = parseInt(localStorage.getItem(STORAGE_KEYS.UPDATED_AT) || '0', 10);
            
            if (isAllowed && !isNaN(lat) && !isNaN(lng) && lat !== 0 && lng !== 0) {
                return { isAllowed: true, lat, lng, name, time };
            }
            return { isAllowed, lat: null, lng: null, name: '', time: 0 };
        } catch (e) {
            return { isAllowed: false, lat: null, lng: null, name: '', time: 0 };
        }
    }

    /**
     * Lưu thông tin vị trí vào localStorage
     */
    function saveStoredGeo(lat, lng, name) {
        try {
            localStorage.setItem(STORAGE_KEYS.ALLOWED, '1');
            localStorage.setItem(STORAGE_KEYS.LAT, lat.toString());
            localStorage.setItem(STORAGE_KEYS.LNG, lng.toString());
            if (name) {
                localStorage.setItem(STORAGE_KEYS.NAME, name);
            }
            localStorage.setItem(STORAGE_KEYS.UPDATED_AT, Date.now().toString());
        } catch (e) {}
    }

    /**
     * Xóa cờ cho phép vị trí khi bị từ chối
     */
    function clearStoredGeoPermission() {
        try {
            localStorage.removeItem(STORAGE_KEYS.ALLOWED);
            localStorage.removeItem(STORAGE_KEYS.LAT);
            localStorage.removeItem(STORAGE_KEYS.LNG);
            localStorage.removeItem(STORAGE_KEYS.NAME);
            localStorage.removeItem(STORAGE_KEYS.UPDATED_AT);
        } catch (e) {}
    }

    /**
     * Tìm tên địa danh gần nhất trong từ điển dự phòng
     */
    function getNearestFallbackName(lat, lng) {
        let nearestName = 'Vị trí GPS hiện tại của tôi';
        let minDistance = 999999;
        
        FALLBACK_CITIES.forEach(city => {
            const d = Math.hypot(city.lat - lat, city.lng - lng);
            if (d < minDistance) {
                minDistance = d;
                nearestName = `Gần ${city.name}`;
            }
        });
        
        return nearestName;
    }

    /**
     * Khởi tạo bộ máy tìm kiếm theo vị trí
     */
    function initGeoLocationSearch() {
        const geoContainers = document.querySelectorAll('.geo-search-container');
        if (!geoContainers.length) return;

        geoContainers.forEach(container => {
            setupContainerEvents(container);
        });

        // Tự động kiểm tra quyền và lấy vị trí nếu đã được cho phép
        checkAndAutoRetrieveLocation(geoContainers);
    }

    /**
     * Thiết lập sự kiện cho từng khung tìm kiếm vị trí
     */
    function setupContainerEvents(container) {
        const locationInput = container.querySelector('.geo-location-input');
        const latInput = container.querySelector('input[name="lat"]');
        const lngInput = container.querySelector('input[name="lng"]');
        const radiusSelect = container.querySelector('select[name="radius"]');
        const btnCurrentLoc = container.querySelector('.btn-get-current-location');
        const suggestionsBox = container.querySelector('.geo-suggestions-dropdown');

        if (!locationInput) return;

        // 1. NÚT LẤY VỊ TRÍ HIỆN TẠI (GPS)
        if (btnCurrentLoc) {
            btnCurrentLoc.addEventListener('click', function(e) {
                e.preventDefault();
                requestUserLocation({
                    container,
                    isManualClick: true
                });
            });
        }

        // 2. GỢI Ý ĐỊA DANH / KCN (AUTOCOMPLETE)
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
                let html = '<div style="padding:0.4rem 0.6rem; font-size:0.75rem; color:#64748b; font-weight:700; border-bottom:1px solid #f1f5f9; background:#f8fafc; display:flex; align-items:center; gap:4px;"><svg class="icon-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg> GỢI Ý ĐỊA DANH & KCN TRỌNG ĐIỂM</div>';
                
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

                        // Cập nhật trạng thái nút GPS về bình thường vì đây là chọn địa danh thủ công
                        if (btnCurrentLoc) {
                            resetGpsButtonState(btnCurrentLoc);
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
                // Người dùng tự gõ text mới -> xóa tọa độ GPS cũ để ưu tiên từ khóa người dùng
                if (latInput) latInput.value = '';
                if (lngInput) lngInput.value = '';
                if (btnCurrentLoc) resetGpsButtonState(btnCurrentLoc);

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
    }

    /**
     * Tự động kiểm tra quyền Geolocation và thực hiện tự động lấy vị trí nếu đã được cho phép
     */
    function checkAndAutoRetrieveLocation(geoContainers) {
        if (!navigator.geolocation) return;

        const cached = getStoredGeo();

        // 1. Kiểm tra Permission API hiện đại (Chrome, Edge, Firefox, Opera...)
        if (navigator.permissions && navigator.permissions.query) {
            navigator.permissions.query({ name: 'geolocation' }).then(function(permissionStatus) {
                if (permissionStatus.state === 'granted') {
                    // Quyền đã được cấp trước đó -> tự động lấy và áp dụng vị trí
                    autoApplyLocationToContainers(geoContainers, cached);
                } else if (permissionStatus.state === 'denied') {
                    clearStoredGeoPermission();
                } else if (cached.isAllowed && cached.lat && cached.lng) {
                    // Fallback theo cache nếu trạng thái 'prompt' nhưng từng được cho phép
                    applyCachedToEmptyContainers(geoContainers, cached);
                }

                // Lắng nghe thay đổi trạng thái quyền trong cài đặt trình duyệt
                permissionStatus.onchange = function() {
                    if (this.state === 'granted') {
                        autoApplyLocationToContainers(geoContainers, getStoredGeo());
                    } else if (this.state === 'denied') {
                        clearStoredGeoPermission();
                    }
                };
            }).catch(function() {
                // Trình duyệt không hỗ trợ query permissions -> Fallback kiểm tra localStorage
                if (cached.isAllowed) {
                    autoApplyLocationToContainers(geoContainers, cached);
                }
            });
        } else if (cached.isAllowed) {
            // Safari hoặc trình duyệt cũ -> Tự động kích hoạt nếu đã có cờ cho phép
            autoApplyLocationToContainers(geoContainers, cached);
        }
    }

    /**
     * Tự động điền tức thì từ cache (0ms) và gọi GPS cập nhật ngầm
     */
    function autoApplyLocationToContainers(containers, cached) {
        // 1. Áp dụng ngay dữ liệu đã lưu để giao diện không bị trống / giật
        if (cached && cached.lat && cached.lng) {
            applyCachedToEmptyContainers(containers, cached);
        }

        // 2. Chạy GPS ngầm để làm mới tọa độ chính xác nhất
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
                // Cập nhật cho tất cả container chưa bị người dùng gõ đè
                containers.forEach(container => {
                    updateContainerWithCoords(container, lat, lng, { isSilent: true });
                });
            },
            function(error) {
                if (error.code === error.PERMISSION_DENIED) {
                    clearStoredGeoPermission();
                }
            },
            {
                enableHighAccuracy: true,
                timeout: 8000,
                maximumAge: 180000 // Sử dụng cache tọa độ 3 phút
            }
        );
    }

    /**
     * Điền cache vào các container chưa có dữ liệu
     */
    function applyCachedToEmptyContainers(containers, cached) {
        containers.forEach(container => {
            const locationInput = container.querySelector('.geo-location-input');
            const latInput = container.querySelector('input[name="lat"]');
            const lngInput = container.querySelector('input[name="lng"]');
            const radiusSelect = container.querySelector('select[name="radius"]');
            const btnCurrentLoc = container.querySelector('.btn-get-current-location');

            if (!locationInput) return;

            // Chỉ tự động điền nếu ô tìm kiếm đang trống hoặc đang là vị trí GPS
            const isInputEmptyOrGps = !locationInput.value.trim() || 
                                     locationInput.value.includes('GPS');

            if (isInputEmptyOrGps) {
                if (latInput) latInput.value = cached.lat;
                if (lngInput) lngInput.value = cached.lng;
                locationInput.value = cached.name || 'Vị trí GPS hiện tại của tôi';

                if (radiusSelect && (!radiusSelect.value || radiusSelect.value === '0')) {
                    radiusSelect.value = '10';
                }

                if (btnCurrentLoc) {
                    setActiveGpsButtonState(btnCurrentLoc);
                }
            }
        });
    }

    /**
     * Yêu cầu người dùng lấy vị trí (khi bấm nút hoặc tự động)
     */
    function requestUserLocation({ container, isManualClick = false }) {
        if (!navigator.geolocation) {
            if (isManualClick) showGeoToast('Trình duyệt của bạn không hỗ trợ định vị GPS.', 'warning');
            return;
        }

        const btnCurrentLoc = container ? container.querySelector('.btn-get-current-location') : null;
        let originalBtnHtml = '';

        if (btnCurrentLoc) {
            originalBtnHtml = btnCurrentLoc.innerHTML;
            btnCurrentLoc.disabled = true;
            btnCurrentLoc.innerHTML = '⏳ <span style="font-size:0.75rem;">Đang định vị...</span>';
        }

        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;

                if (container) {
                    updateContainerWithCoords(container, lat, lng, { isSilent: !isManualClick });
                }

                if (btnCurrentLoc) {
                    btnCurrentLoc.disabled = false;
                    setActiveGpsButtonState(btnCurrentLoc);
                }

                if (isManualClick) {
                    showGeoToast('Đã xác định vị trí GPS thành công! (Tự động lưu cho lần sau)', 'success');
                }
            },
            function(error) {
                if (btnCurrentLoc) {
                    btnCurrentLoc.disabled = false;
                    btnCurrentLoc.innerHTML = originalBtnHtml;
                }

                if (error.code === error.PERMISSION_DENIED) {
                    clearStoredGeoPermission();
                    if (isManualClick) {
                        showGeoToast('Bạn đã từ chối quyền truy cập vị trí. Vui lòng cho phép quyền vị trí trên trình duyệt.', 'error');
                    }
                } else if (isManualClick) {
                    showGeoToast('Không thể lấy tọa độ GPS chính xác. Vui lòng thử lại.', 'warning');
                }
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 60000
            }
        );
    }

    /**
     * Cập nhật các ô input của container và tiến hành reverse geocoding
     */
    function updateContainerWithCoords(container, lat, lng, options = {}) {
        const locationInput = container.querySelector('.geo-location-input');
        const latInput = container.querySelector('input[name="lat"]');
        const lngInput = container.querySelector('input[name="lng"]');
        const radiusSelect = container.querySelector('select[name="radius"]');
        const btnCurrentLoc = container.querySelector('.btn-get-current-location');
        const suggestionsBox = container.querySelector('.geo-suggestions-dropdown');

        if (latInput) latInput.value = lat;
        if (lngInput) lngInput.value = lng;

        // Nếu chưa chọn bán kính, mặc định chọn 10 km
        if (radiusSelect && (!radiusSelect.value || radiusSelect.value === '0')) {
            radiusSelect.value = '10';
        }

        if (btnCurrentLoc) {
            setActiveGpsButtonState(btnCurrentLoc);
        }

        if (suggestionsBox) {
            suggestionsBox.style.display = 'none';
        }

        // Lấy tên địa danh đã lưu nếu tọa độ không xê dịch nhiều (< ~200m)
        const cached = getStoredGeo();
        if (cached.name && cached.lat && cached.lng) {
            const diff = Math.hypot(cached.lat - lat, cached.lng - lng);
            if (diff < 0.002) {
                if (locationInput) locationInput.value = cached.name;
                saveStoredGeo(lat, lng, cached.name);
                return;
            }
        }

        // Đặt tên mặc định trước khi reverse geocoding hoàn tất
        const fallbackName = getNearestFallbackName(lat, lng);
        if (locationInput) locationInput.value = fallbackName;
        saveStoredGeo(lat, lng, fallbackName);

        // Gọi OpenStreetMap Reverse Geocoding với timeout ngắn
        const controller = (typeof AbortController !== 'undefined') ? new AbortController() : null;
        const timeoutId = controller ? setTimeout(() => controller.abort(), 4000) : null;

        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=14&addressdetails=1`, {
            headers: { 'Accept-Language': 'vi,en' },
            signal: controller ? controller.signal : undefined
        })
        .then(res => res.json())
        .then(data => {
            if (timeoutId) clearTimeout(timeoutId);
            if (data && data.display_name) {
                const parts = data.display_name.split(',');
                const shortName = parts.slice(0, 3).join(',').trim();
                if (locationInput) locationInput.value = shortName;
                saveStoredGeo(lat, lng, shortName);
            }
        })
        .catch(() => {
            // Giữ nguyên fallbackName nếu request Nominatim thất bại
        });
    }

    /**
     * Đổi giao diện nút GPS sang trạng thái đang hoạt động / đã khóa vị trí
     */
    function setActiveGpsButtonState(btn) {
        if (!btn) return;
        btn.setAttribute('data-gps-active', '1');
        btn.style.borderColor = '#0d9488';
        btn.style.color = '#0d9488';
        btn.style.background = '#f0fdfa';
        btn.innerHTML = '<svg class="icon-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;"><polyline points="20 6 9 17 4 12"></polyline></svg> <span style="font-size:0.75rem; font-weight:700;">GPS của bạn</span>';
        btn.title = 'Đang sử dụng vị trí GPS của bạn. Bấm để cập nhật lại';
    }

    /**
     * Đặt lại giao diện nút GPS về ban đầu
     */
    function resetGpsButtonState(btn) {
        if (!btn) return;
        btn.removeAttribute('data-gps-active');
        btn.style.borderColor = '';
        btn.style.color = '';
        btn.style.background = '';
        btn.innerHTML = '<svg class="icon-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg> <span style="font-size:0.75rem;">GPS</span>';
        btn.title = 'Lấy vị trí GPS hiện tại';
    }

    function showGeoToast(message, type = 'info') {
        if (typeof Toast !== 'undefined' && Toast.show) {
            Toast.show(message, type);
        } else if (window.Toast && window.Toast.show) {
            window.Toast.show(message, type);
        } else {
            console.log(`[GeoLocation] ${type}: ${message}`);
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

    // Xuất hàm ra global nếu cần dùng bên ngoài
    window.JobLinkGeo = {
        init: initGeoLocationSearch,
        getStoredGeo: getStoredGeo,
        requestLocation: requestUserLocation
    };
})();

