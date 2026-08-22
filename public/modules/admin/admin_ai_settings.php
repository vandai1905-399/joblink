<?php 
ob_start(); 
$currentApiKey = AIConfig::getApiKey();
$currentModel = AIConfig::getModel();
$isActive = AIConfig::get('gemini_is_active', '1');
$supportedModels = AIConfig::getSupportedModels();
$isConfigured = AIConfig::isConfigured();
?>

<div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
    <div>
        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
            <span style="font-size: 1.5rem;">🤖</span>
            <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--dark); margin: 0;">Cấu Hình Google Gemini AI</h1>
            <span class="badge <?= $isConfigured ? 'badge-success' : 'badge-warning' ?>" style="font-size: 0.8rem; font-weight: 700;">
                <?= $isConfigured ? '✓ Đang Kết Nối Gemini' : '⚡ Đang Dùng Engine Nội Bộ' ?>
            </span>
        </div>
        <p style="color: var(--slate-500); font-size: 0.95rem; margin: 0;">Quản lý API Key, tự động tải toàn bộ danh sách Model cung cấp bởi API Key và kiểm tra kết nối thời gian thực</p>
    </div>

    <button type="button" id="btn-test-ai-connection" class="btn" style="background: linear-gradient(135deg, #0d9488 0%, #0284c7 100%); color: #ffffff; font-weight: 700; box-shadow: 0 4px 12px rgba(13,148,136,0.25); display: inline-flex; align-items: center; gap: 0.4rem; border-radius: 8px;">
        ⚡ <span>Kiểm Tra Kết Nối API Ngay</span>
    </button>
</div>

<!-- KHỐI KẾT QUẢ KIỂM TRA TEST KẾT NỐI (HIỆN QUA AJAX) -->
<div id="test-connection-result" style="display: none; margin-bottom: 1.5rem;"></div>

<div style="display: grid; grid-template-columns: 1fr 340px; gap: 1.5rem; align-items: start;">
    <!-- CỘT CHÍNH: FORM CÀI ĐẶT -->
    <div class="card" style="border-radius: 12px; box-shadow: 0 4px 16px rgba(0,0,0,0.04);">
        <h3 style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin-bottom: 1.25rem; padding-bottom: 0.75rem; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; gap: 0.5rem;">
            <?= icon('settings', '', 18) ?> Thông Số Cấu Hình Google AI
        </h3>

        <form action="<?= url('/admin/ai-settings/update') ?>" method="POST" id="aiSettingsForm">
            <!-- GOOGLE GEMINI API KEY (CHỈ DÁN, KHÔNG HIỆN MÃ BẢO MẬT) -->
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label for="gemini_api_key" style="font-weight: 700; color: #1e293b; display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <span>Google Gemini API Key <span style="color: #ef4444;">*</span></span>
                    <?php if (!empty($currentApiKey)): ?>
                        <span style="font-size: 0.8rem; color: #10b981; font-weight: 600;">✓ Đã lưu mã bảo mật (••••••••••••)</span>
                    <?php endif; ?>
                </label>
                <div>
                    <input type="password" id="gemini_api_key" name="gemini_api_key" class="form-control" 
                           value="<?= htmlspecialchars($currentApiKey) ?>" 
                           placeholder="Dán Google Gemini API Key vào đây (AIzaSy...)" 
                           style="font-family: monospace; font-size: 0.95rem; letter-spacing: 0.05em;" 
                           autocomplete="off" required>
                </div>
                <small style="color: #64748b; font-size: 0.82rem; margin-top: 0.4rem; display: block;">
                    🔒 Mã API Key được ẩn bảo mật. Khi bạn dán key mới, hệ thống sẽ tự động quét và tải toàn bộ danh sách các model mà API Key của bạn có quyền sử dụng.
                </small>
            </div>

            <!-- CHỌN MODEL GOOGLE GEMINI (LOAD TỰ ĐỘNG THEO API KEY) -->
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <label for="gemini_model" style="font-weight: 700; color: #1e293b; margin: 0;">
                        Model Google Gemini (Tự động nạp từ API Key)
                    </label>
                    <button type="button" id="btn-fetch-models" style="background: none; border: none; color: #0284c7; font-size: 0.8rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 0.25rem;">
                        🔄 <span>Tải lại danh sách Model</span>
                    </button>
                </div>

                <div id="models-loading-spinner" style="display: none; font-size: 0.82rem; color: #0284c7; margin-bottom: 0.4rem;">
                    ⏳ Đang quét danh sách model từ Google AI API...
                </div>

                <select id="gemini_model" name="gemini_model" class="form-control" style="font-weight: 600; font-size: 0.95rem;">
                    <!-- Mặc định ban đầu nếu chưa load ajax -->
                    <?php foreach ($supportedModels as $mKey => $mInfo): ?>
                        <option value="<?= $mKey ?>" <?= $currentModel === $mKey ? 'selected' : '' ?>>
                            <?= htmlspecialchars($mInfo['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div style="margin-top: 0.5rem; font-size: 0.82rem; color: #64748b; line-height: 1.5;" id="model-description">
                    <?= htmlspecialchars($supportedModels[$currentModel]['desc'] ?? 'Model Gemini') ?>
                </div>
            </div>

            <!-- TRẠNG THÁI HOẠT ĐỘNG -->
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label for="gemini_is_active" style="font-weight: 700; color: #1e293b; margin-bottom: 0.5rem; display: block;">
                    Trạng Thái Google AI
                </label>
                <select id="gemini_is_active" name="gemini_is_active" class="form-control" style="font-weight: 600;">
                    <option value="1" <?= $isActive === '1' ? 'selected' : '' ?>>Bật (Kích hoạt Google Gemini)</option>
                    <option value="0" <?= $isActive === '0' ? 'selected' : '' ?>>Tắt (Chỉ dùng Local Engine)</option>
                </select>
            </div>

            <div style="border-top: 1px solid #f1f5f9; padding-top: 1.25rem; display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="submit" class="btn btn-primary" style="padding: 0.6rem 1.5rem; font-weight: 700;">
                    <?= icon('check', '', 16) ?> Lưu Cấu Hình AI
                </button>
            </div>
        </form>
    </div>

    <!-- CỘT PHỤ: HƯỚNG DẪN & BẢO VỆ -->
    <div>
        <div class="card" style="border-radius: 12px; background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border: 1px solid #e2e8f0; margin-bottom: 1.5rem;">
            <h4 style="font-size: 1rem; font-weight: 800; color: #0f172a; margin-top: 0; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.4rem;">
                ✨ Cách Kết Nối Model Mới Nhất
            </h4>
            <ol style="margin: 0; padding-left: 1.2rem; font-size: 0.85rem; line-height: 1.6; color: #334155;">
                <li style="margin-bottom: 0.5rem;">
                    Truy cập trang Google AI Studio: <br>
                    <a href="https://aistudio.google.com/app/apikey" target="_blank" style="color: #0284c7; font-weight: 700; text-decoration: underline;">
                        aistudio.google.com/app/apikey ↗
                    </a>
                </li>
                <li style="margin-bottom: 0.5rem;">
                    Nhấn <strong>Create API key</strong> và copy chuỗi mã <code>AIzaSy...</code>.
                </li>
                <li style="margin-bottom: 0.5rem;">
                    Dán vào ô <strong>Google Gemini API Key</strong>. Hệ thống sẽ tự nạp toàn bộ model (Gemini 3.7 Flash, 2.0 Flash,...).
                </li>
                <li>
                    Chọn Model mong muốn và nhấn <strong>Lưu Cấu Hình AI</strong>.
                </li>
            </ol>
        </div>

        <div class="card" style="border-radius: 12px; background: #f0fdfa; border: 1px solid #99f6e4;">
            <h4 style="font-size: 0.95rem; font-weight: 800; color: #0f766e; margin-top: 0; margin-bottom: 0.5rem;">
                🛡️ Cơ Chế Bảo Vệ Hybrid Fallback
            </h4>
            <p style="font-size: 0.82rem; line-height: 1.5; color: #115e59; margin: 0;">
                Nếu API Key hết quota hoặc mạng gặp sự cố, hệ thống sẽ <strong>tự động chuyển sang Local Scoring Engine</strong>, đảm bảo người dùng luôn tìm việc và nộp hồ sơ mượt mà.
            </p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const keyInput = document.getElementById('gemini_api_key');
    const modelSelect = document.getElementById('gemini_model');
    const modelDesc = document.getElementById('model-description');
    const fetchModelsBtn = document.getElementById('btn-fetch-models');
    const spinner = document.getElementById('models-loading-spinner');
    const testBtn = document.getElementById('btn-test-ai-connection');
    const resultBox = document.getElementById('test-connection-result');

    let modelsDataCache = {};

    // Hàm gọi API lấy toàn bộ danh sách model mà API Key có quyền truy cập
    function loadModelsFromApiKey(apiKey) {
        if (!apiKey) return;

        if (spinner) spinner.style.display = 'block';
        if (fetchModelsBtn) fetchModelsBtn.disabled = true;

        const formData = new FormData();
        formData.append('api_key', apiKey);

        fetch('<?= url('/admin/api/ai-models') ?>', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (spinner) spinner.style.display = 'none';
            if (fetchModelsBtn) fetchModelsBtn.disabled = false;

            if (data.success && data.models && data.models.length > 0) {
                const currentSelected = modelSelect.value;
                modelSelect.innerHTML = '';
                modelsDataCache = {};

                data.models.forEach(m => {
                    modelsDataCache[m.id] = m.description || 'Model Gemini ' + m.display_name;
                    const opt = document.createElement('option');
                    opt.value = m.id;
                    opt.textContent = `${m.display_name} (${m.id})`;
                    if (m.id === currentSelected || m.raw_name === currentSelected) {
                        opt.selected = true;
                    }
                    modelSelect.appendChild(opt);
                });

                // Nếu chưa có selected, chọn cái đầu tiên
                if (!modelSelect.value && modelSelect.options.length > 0) {
                    modelSelect.selectedIndex = 0;
                }

                if (modelsDataCache[modelSelect.value]) {
                    modelDesc.textContent = modelsDataCache[modelSelect.value];
                }
            }
        })
        .catch(err => {
            if (spinner) spinner.style.display = 'none';
            if (fetchModelsBtn) fetchModelsBtn.disabled = false;
        });
    }

    // Tự động load danh sách model khi vào trang nếu đã có Key
    if (keyInput && keyInput.value.trim()) {
        loadModelsFromApiKey(keyInput.value.trim());
    }

    // Nút tải lại model thủ công
    if (fetchModelsBtn) {
        fetchModelsBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const val = keyInput ? keyInput.value.trim() : '';
            if (!val) {
                if (typeof Toast !== 'undefined') Toast.show('Vui lòng dán API Key trước để tải danh sách Model!', 'warning');
                return;
            }
            loadModelsFromApiKey(val);
        });
    }

    // Khi người dùng dán hoặc đổi API Key -> Tự động nạp lại Model
    if (keyInput) {
        keyInput.addEventListener('change', function() {
            const val = this.value.trim();
            if (val.length > 15) {
                loadModelsFromApiKey(val);
            }
        });
        keyInput.addEventListener('paste', function() {
            setTimeout(() => {
                const val = keyInput.value.trim();
                if (val.length > 15) {
                    loadModelsFromApiKey(val);
                }
            }, 100);
        });
    }

    // Cập nhật mô tả khi đổi Model
    if (modelSelect && modelDesc) {
        modelSelect.addEventListener('change', function() {
            if (modelsDataCache[this.value]) {
                modelDesc.textContent = modelsDataCache[this.value];
            }
        });
    }

    // Nút kiểm tra kết nối API
    if (testBtn && resultBox) {
        testBtn.addEventListener('click', function() {
            const currentKey = keyInput ? keyInput.value.trim() : '';
            const currentModelVal = modelSelect ? modelSelect.value : 'gemini-2.0-flash';

            if (!currentKey) {
                if (typeof Toast !== 'undefined') {
                    Toast.show('Vui lòng dán API Key vào ô trước khi kiểm tra!', 'warning');
                } else {
                    alert('Vui lòng dán API Key trước!');
                }
                if (keyInput) keyInput.focus();
                return;
            }

            testBtn.disabled = true;
            testBtn.innerHTML = '⏳ <span>Đang kiểm tra kết nối...</span>';
            resultBox.style.display = 'block';
            resultBox.innerHTML = `
                <div style="padding: 1rem 1.25rem; border-radius: 10px; background: #f8fafc; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 0.75rem; color: #475569;">
                    <div style="font-size: 1.25rem;">⏳</div>
                    <div>Đang gửi yêu cầu kiểm tra đến Google Gemini API (Model: <strong>${currentModelVal}</strong>)...</div>
                </div>
            `;

            const formData = new FormData();
            formData.append('api_key', currentKey);
            formData.append('model', currentModelVal);

            fetch('<?= url('/admin/api/ai-test') ?>', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                testBtn.disabled = false;
                testBtn.innerHTML = '⚡ <span>Kiểm Tra Kết Nối API Ngay</span>';

                if (data.success) {
                    resultBox.innerHTML = `
                        <div style="padding: 1rem 1.25rem; border-radius: 10px; background: #f0fdf4; border: 1px solid #86efac; color: #166534;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                                <strong style="font-size: 0.95rem; display: flex; align-items: center; gap: 0.4rem;">
                                    ✓ Kết Nối Google Gemini API Thành Công!
                                </strong>
                                <span style="font-size: 0.8rem; background: #bbf7d0; padding: 0.2rem 0.5rem; border-radius: 999px; font-weight: 700;">
                                    Độ trễ: ${data.latency}ms
                                </span>
                            </div>
                            <div style="font-size: 0.85rem; line-height: 1.5; color: #15803d;">
                                <strong>Model:</strong> <code>${data.model}</code> | <strong>Phản hồi:</strong> "${data.reply || data.message}"
                            </div>
                        </div>
                    `;
                    if (typeof Toast !== 'undefined') Toast.show(data.message || 'Kết nối API thành công!', 'success');
                } else {
                    resultBox.innerHTML = `
                        <div style="padding: 1rem 1.25rem; border-radius: 10px; background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b;">
                            <div style="font-weight: 700; font-size: 0.95rem; margin-bottom: 0.35rem;">
                                ✗ Kết Nối Thất Bại (Mã HTTP: ${data.http_code || 'Error'})
                            </div>
                            <div style="font-size: 0.85rem; line-height: 1.5;">
                                ${data.message || 'Không thể kết nối với Google API. Vui lòng kiểm tra lại tính chính xác của API Key.'}
                            </div>
                        </div>
                    `;
                    if (typeof Toast !== 'undefined') Toast.show(data.message || 'Kết nối không thành công!', 'error');
                }
            })
            .catch(err => {
                testBtn.disabled = false;
                testBtn.innerHTML = '⚡ <span>Kiểm Tra Kết Nối API Ngay</span>';
                resultBox.innerHTML = `
                    <div style="padding: 1rem 1.25rem; border-radius: 10px; background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b;">
                        Lỗi kết nối máy chủ nội bộ. Vui lòng thử lại.
                    </div>
                `;
            });
        });
    }
});
</script>

<?php 
$content = ob_get_clean(); 
require_once __DIR__ . '/../layouts/admin_layout.php';
?>
