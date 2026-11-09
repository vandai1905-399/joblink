<?php 
ob_start(); 
$engineMode = AIConfig::getEngineMode();
$isLocal = AIConfig::isLocalMode();
$currentApiKey = AIConfig::getApiKey();
$currentModel = AIConfig::getModel();
$isActive = AIConfig::get('gemini_is_active', '1');
$supportedModels = AIConfig::getSupportedModels();
$isConfigured = AIConfig::isConfigured();
?>

<div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
    <div>
        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
            <span style="color: var(--teal-600); display: inline-flex; align-items: center;"><?= icon('cpu', '', 26) ?></span>
            <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--dark); margin: 0;">Cấu Hình Động Cơ AI</h1>
            <?php if ($isLocal): ?>
                <span class="badge badge-success" style="font-size: 0.8rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.35rem; background: #059669; color: #fff; padding: 0.35rem 0.65rem; border-radius: 999px;">
                    <?= icon('check', '', 14) ?> Local AI Engine (Mặc Định - Đang Chạy)
                </span>
            <?php else: ?>
                <span class="badge badge-primary" style="font-size: 0.8rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.35rem; background: #0284c7; color: #fff; padding: 0.35rem 0.65rem; border-radius: 999px;">
                    <?= icon('cloud', '', 14) ?> Google Gemini Cloud AI
                </span>
            <?php endif; ?>
        </div>
        <p style="color: var(--slate-500); font-size: 0.95rem; margin: 0;">Hệ thống AI chạy hoàn toàn nội bộ Local Engine (không cần API ngoài), hoặc chuyển đổi linh hoạt sang Google Gemini khi cần</p>
    </div>

    <button type="button" id="btn-test-ai-connection" class="btn" style="background: linear-gradient(135deg, #0d9488 0%, #0284c7 100%); color: #ffffff; font-weight: 700; box-shadow: 0 4px 12px rgba(13,148,136,0.25); display: inline-flex; align-items: center; gap: 0.4rem; border-radius: 8px;">
        <?= icon('zap', '', 16) ?> <span id="btn-test-text">Kiểm Tra Động Cơ AI Ngay</span>
    </button>
</div>

<!-- KHỐI KẾT QUẢ KIỂM TRA TEST KẾT NỐI (HIỆN QUA AJAX) -->
<div id="test-connection-result" style="display: none; margin-bottom: 1.5rem;"></div>

<div style="display: grid; grid-template-columns: 1fr 340px; gap: 1.5rem; align-items: start;">
    <!-- CỘT CHÍNH: FORM CÀI ĐẶT -->
    <div class="card" style="border-radius: 12px; box-shadow: 0 4px 16px rgba(0,0,0,0.04);">
        <h3 style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin-bottom: 1.25rem; padding-bottom: 0.75rem; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; gap: 0.5rem;">
            <?= icon('settings', '', 18) ?> Thiết Lập Động Cơ AI JobLink
        </h3>

        <form action="<?= url('/admin/ai-settings/update') ?>" method="POST" id="aiSettingsForm">
            <!-- CHỌN CHẾ ĐỘ ĐỘNG CƠ AI -->
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label style="font-weight: 700; color: #1e293b; margin-bottom: 0.75rem; display: block;">
                    Chế Độ Hoạt Động Của AI <span style="color: #ef4444;">*</span>
                </label>
                
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <!-- LỰA CHỌN 1: LOCAL AI ENGINE (MẶC ĐỊNH) -->
                    <label style="display: flex; align-items: flex-start; gap: 0.85rem; padding: 1rem 1.15rem; border-radius: 10px; border: 2px solid <?= $engineMode === 'local' ? '#0d9488' : '#e2e8f0' ?>; background: <?= $engineMode === 'local' ? '#f0fdfa' : '#fff' ?>; cursor: pointer; transition: all 0.2s ease;" id="label-engine-local">
                        <input type="radio" name="ai_engine_mode" value="local" <?= $engineMode === 'local' ? 'checked' : '' ?> style="margin-top: 0.25rem; accent-color: #0d9488; transform: scale(1.15);" id="radio-mode-local">
                        <div style="flex: 1;">
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                                <strong style="font-size: 0.95rem; color: #0f766e;">JobLink Local AI Engine</strong>
                                <span style="font-size: 0.75rem; font-weight: 800; background: #ccfbf1; color: #0f766e; padding: 0.15rem 0.5rem; border-radius: 999px;">Mặc định • Khuyên dùng</span>
                            </div>
                            <p style="margin: 0; font-size: 0.85rem; color: #475569; line-height: 1.45;">
                                Chạy 100% nội bộ trên máy chủ JobLink. Không cần API Key từ ngoài, không phụ thuộc mạng, phản hồi tức thì (&lt; 5ms), miễn phí vĩnh viễn và đảm bảo an toàn dữ liệu.
                            </p>
                        </div>
                    </label>

                    <!-- LỰA CHỌN 2: GOOGLE GEMINI CLOUD AI -->
                    <label style="display: flex; align-items: flex-start; gap: 0.85rem; padding: 1rem 1.15rem; border-radius: 10px; border: 2px solid <?= $engineMode === 'cloud' ? '#0284c7' : '#e2e8f0' ?>; background: <?= $engineMode === 'cloud' ? '#f0f9ff' : '#fff' ?>; cursor: pointer; transition: all 0.2s ease;" id="label-engine-cloud">
                        <input type="radio" name="ai_engine_mode" value="cloud" <?= $engineMode === 'cloud' ? 'checked' : '' ?> style="margin-top: 0.25rem; accent-color: #0284c7; transform: scale(1.15);" id="radio-mode-cloud">
                        <div style="flex: 1;">
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                                <strong style="font-size: 0.95rem; color: #0369a1;">Google Gemini Cloud AI</strong>
                                <span style="font-size: 0.75rem; font-weight: 800; background: #e0f2fe; color: #0369a1; padding: 0.15rem 0.5rem; border-radius: 999px;">Tùy chọn kết nối ngoài</span>
                            </div>
                            <p style="margin: 0; font-size: 0.85rem; color: #475569; line-height: 1.45;">
                                Kết nối đám mây với các mô hình ngôn ngữ lớn của Google (Gemini 2.0 Flash, 3.1 Flash-Lite...). Cần nhập API Key từ Google AI Studio.
                            </p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- CONTAINER CẤU HÌNH GOOGLE GEMINI (CHỈ HIỂN THỊ CHI TIẾT KHI CHỌN CLOUD HOẶC MUỐN LƯU DỰ PHÒNG) -->
            <div id="gemini-settings-container" style="border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.25rem; margin-bottom: 1.5rem; background: <?= $engineMode === 'local' ? '#f8fafc' : '#ffffff' ?>;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <div style="font-weight: 700; font-size: 0.95rem; color: #1e293b; display: flex; align-items: center; gap: 0.4rem;">
                        <?= icon('cloud', '', 16) ?> Thông Số Google Gemini Cloud (Tùy chọn)
                    </div>
                    <span id="gemini-status-hint" style="font-size: 0.8rem; color: #64748b;">
                        <?= $engineMode === 'local' ? 'Không bắt buộc khi chạy Local Engine' : 'Bắt buộc khi chạy chế độ Cloud' ?>
                    </span>
                </div>

                <!-- GOOGLE GEMINI API KEY -->
                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label for="gemini_api_key" style="font-weight: 700; color: #1e293b; display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <span>Google Gemini API Key</span>
                        <?php if (!empty($currentApiKey)): ?>
                            <span style="font-size: 0.8rem; color: #10b981; font-weight: 600; display: inline-flex; align-items: center; gap: 0.25rem;"><?= icon('check', '', 14) ?> Đã lưu mã bảo mật (••••••••••••)</span>
                        <?php endif; ?>
                    </label>
                    <div>
                        <input type="password" id="gemini_api_key" name="gemini_api_key" class="form-control" 
                               value="<?= htmlspecialchars($currentApiKey) ?>" 
                               placeholder="Dán Google Gemini API Key nếu muốn dùng chế độ Cloud (tùy chọn)" 
                               style="font-family: monospace; font-size: 0.95rem; letter-spacing: 0.05em;" 
                               autocomplete="off">
                    </div>
                    <small style="color: #64748b; font-size: 0.82rem; margin-top: 0.4rem; display: flex; align-items: center; gap: 0.35rem;">
                        <?= icon('lock', '', 14) ?> Khi ở chế độ Local Engine, bạn hoàn toàn có thể để trống ô này mà hệ thống vẫn chạy mượt mà 100%.
                    </small>
                </div>

                <!-- CHỌN MODEL GOOGLE GEMINI -->
                <div class="form-group" style="margin-bottom: 0;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <label for="gemini_model" style="font-weight: 700; color: #1e293b; margin: 0;">
                            Model Google Gemini
                        </label>
                        <button type="button" id="btn-fetch-models" style="background: none; border: none; color: #0284c7; font-size: 0.8rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 0.25rem;">
                            <?= icon('refresh', '', 14) ?> <span>Tải lại danh sách Model</span>
                        </button>
                    </div>

                    <div id="models-loading-spinner" style="display: none; font-size: 0.82rem; color: #0284c7; margin-bottom: 0.4rem; align-items: center; gap: 0.35rem;">
                        <?= icon('clock', '', 14) ?> Đang quét danh sách model từ Google AI API...
                    </div>

                    <select id="gemini_model" name="gemini_model" class="form-control" style="font-weight: 600; font-size: 0.95rem;">
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
            </div>

            <!-- TRẠNG THÁI HOẠT ĐỘNG CHUNG -->
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label for="gemini_is_active" style="font-weight: 700; color: #1e293b; margin-bottom: 0.5rem; display: block;">
                    Trạng Thái Động Cơ AI
                </label>
                <select id="gemini_is_active" name="gemini_is_active" class="form-control" style="font-weight: 600;">
                    <option value="1" <?= $isActive === '1' ? 'selected' : '' ?>>Bật (Kích hoạt toàn bộ tính năng AI Matching, Chatbot, Phân tích CV)</option>
                    <option value="0" <?= $isActive === '0' ? 'selected' : '' ?>>Tắt (Tạm dừng tính năng AI)</option>
                </select>
            </div>

            <div style="border-top: 1px solid #f1f5f9; padding-top: 1.25rem; display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="submit" class="btn btn-primary" style="padding: 0.6rem 1.5rem; font-weight: 700;">
                    <?= icon('check', '', 16) ?> Lưu Cấu Hình AI
                </button>
            </div>
        </form>
    </div>

    <!-- CỘT PHỤ: HƯỚNG DẪN & THÔNG TIN -->
    <div>
        <!-- THÔNG TIN LOCAL AI ENGINE -->
        <div class="card" style="border-radius: 12px; background: linear-gradient(135deg, #f0fdfa 0%, #e6fffa 100%); border: 1px solid #99f6e4; margin-bottom: 1.5rem;">
            <h4 style="font-size: 1rem; font-weight: 800; color: #0f766e; margin-top: 0; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.4rem;">
                <?= icon('cpu', '', 18) ?> Vì Sao Nên Dùng Local AI?
            </h4>
            <ul style="margin: 0; padding-left: 1.2rem; font-size: 0.85rem; line-height: 1.6; color: #115e59;">
                <li style="margin-bottom: 0.4rem;">
                    <strong>Không cần API Key:</strong> Chạy hoàn toàn độc lập, không cần đăng ký tài khoản hay nạp thẻ bên ngoài.
                </li>
                <li style="margin-bottom: 0.4rem;">
                    <strong>Tốc độ tức thì:</strong> Độ trễ dưới <strong>5ms</strong> (nhanh gấp 200 lần so với gọi API bên thứ ba).
                </li>
                <li style="margin-bottom: 0.4rem;">
                    <strong>Bảo mật tối đa:</strong> Toàn bộ dữ liệu ứng viên và doanh nghiệp được đối soát trực tiếp trong máy chủ JobLink.
                </li>
                <li>
                    <strong>Không lo hạn ngạch (quota):</strong> Hệ thống không bao giờ bị nghẽn hay gián đoạn khi lượng truy cập tăng cao.
                </li>
            </ul>
        </div>

        <!-- HƯỚNG DẪN CLOUD GEMINI -->
        <div class="card" style="border-radius: 12px; background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border: 1px solid #e2e8f0;">
            <h4 style="font-size: 0.95rem; font-weight: 800; color: #0f172a; margin-top: 0; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.4rem;">
                <?= icon('sparkle', '', 16) ?> Khi Nào Dùng Gemini Cloud?
            </h4>
            <p style="font-size: 0.82rem; line-height: 1.5; color: #475569; margin: 0 0 0.5rem 0;">
                Nếu bạn muốn trải nghiệm mô hình ngôn ngữ lớn (LLM) của Google cho các câu trả lời mang tính sáng tạo cao:
            </p>
            <ol style="margin: 0; padding-left: 1.2rem; font-size: 0.82rem; line-height: 1.5; color: #334155;">
                <li>Lấy API key tại: <a href="https://aistudio.google.com/app/apikey" target="_blank" style="color: #0284c7; font-weight: 700; text-decoration: underline;">Google AI Studio ↗</a></li>
                <li>Chọn chế độ <strong>Google Gemini Cloud AI</strong> ở form bên cạnh.</li>
                <li>Dán key và nhấn <strong>Lưu Cấu Hình AI</strong>.</li>
            </ol>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const radioLocal = document.getElementById('radio-mode-local');
    const radioCloud = document.getElementById('radio-mode-cloud');
    const labelLocal = document.getElementById('label-engine-local');
    const labelCloud = document.getElementById('label-engine-cloud');
    const geminiContainer = document.getElementById('gemini-settings-container');
    const geminiHint = document.getElementById('gemini-status-hint');
    const keyInput = document.getElementById('gemini_api_key');
    const modelSelect = document.getElementById('gemini_model');
    const modelDesc = document.getElementById('model-description');
    const fetchModelsBtn = document.getElementById('btn-fetch-models');
    const spinner = document.getElementById('models-loading-spinner');
    const testBtn = document.getElementById('btn-test-ai-connection');
    const testBtnText = document.getElementById('btn-test-text');
    const resultBox = document.getElementById('test-connection-result');

    let modelsDataCache = {};

    function updateModeUI() {
        const isLocalMode = radioLocal && radioLocal.checked;
        if (isLocalMode) {
            if (labelLocal) {
                labelLocal.style.borderColor = '#0d9488';
                labelLocal.style.backgroundColor = '#f0fdfa';
            }
            if (labelCloud) {
                labelCloud.style.borderColor = '#e2e8f0';
                labelCloud.style.backgroundColor = '#fff';
            }
            if (geminiContainer) {
                geminiContainer.style.backgroundColor = '#f8fafc';
            }
            if (geminiHint) {
                geminiHint.textContent = 'Không bắt buộc khi chạy Local Engine';
                geminiHint.style.color = '#0f766e';
            }
            if (testBtnText) {
                testBtnText.textContent = 'Kiểm Tra Local AI Engine';
            }
        } else {
            if (labelLocal) {
                labelLocal.style.borderColor = '#e2e8f0';
                labelLocal.style.backgroundColor = '#fff';
            }
            if (labelCloud) {
                labelCloud.style.borderColor = '#0284c7';
                labelCloud.style.backgroundColor = '#f0f9ff';
            }
            if (geminiContainer) {
                geminiContainer.style.backgroundColor = '#ffffff';
            }
            if (geminiHint) {
                geminiHint.textContent = 'Bắt buộc khi chạy chế độ Cloud';
                geminiHint.style.color = '#0284c7';
            }
            if (testBtnText) {
                testBtnText.textContent = 'Kiểm Tra Kết Nối Gemini API';
            }
        }
    }

    if (radioLocal) radioLocal.addEventListener('change', updateModeUI);
    if (radioCloud) radioCloud.addEventListener('change', updateModeUI);
    updateModeUI();

    // Hàm gọi API lấy danh sách model nếu có Key
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

    // Khi người dùng dán hoặc đổi API Key
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

    // Nút kiểm tra động cơ AI
    if (testBtn && resultBox) {
        testBtn.addEventListener('click', function() {
            const isLocalMode = radioLocal && radioLocal.checked;
            const currentKey = keyInput ? keyInput.value.trim() : '';
            const currentModelVal = modelSelect ? modelSelect.value : 'gemini-2.0-flash';

            if (!isLocalMode && !currentKey) {
                if (typeof Toast !== 'undefined') {
                    Toast.show('Vui lòng dán API Key vào ô trước khi kiểm tra chế độ Cloud!', 'warning');
                } else {
                    alert('Vui lòng dán API Key trước khi kiểm tra chế độ Cloud!');
                }
                if (keyInput) keyInput.focus();
                return;
            }

            testBtn.disabled = true;
            testBtn.innerHTML = '⏳ <span>Đang kiểm tra động cơ AI...</span>';
            resultBox.style.display = 'block';
            resultBox.innerHTML = `
                <div style="padding: 1rem 1.25rem; border-radius: 10px; background: #f8fafc; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 0.75rem; color: #475569;">
                    <div style="font-size: 1.25rem;">⏳</div>
                    <div>${isLocalMode ? 'Đang kiểm tra tính sẵn sàng của JobLink Local AI Engine...' : 'Đang gửi yêu cầu kiểm tra đến Google Gemini API (' + currentModelVal + ')...'}</div>
                </div>
            `;

            const formData = new FormData();
            formData.append('ai_engine_mode', isLocalMode ? 'local' : 'cloud');
            formData.append('api_key', isLocalMode ? 'local' : currentKey);
            formData.append('model', currentModelVal);

            fetch('<?= url('/admin/api/ai-test') ?>', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                testBtn.disabled = false;
                testBtn.innerHTML = '<svg class="icon-svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; display: inline-block;"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg> <span>' + (isLocalMode ? 'Kiểm Tra Local AI Engine' : 'Kiểm Tra Kết Nối Gemini API') + '</span>';

                if (data.success) {
                    resultBox.innerHTML = `
                        <div style="padding: 1rem 1.25rem; border-radius: 10px; background: #f0fdf4; border: 1px solid #86efac; color: #166534;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                                <strong style="font-size: 0.95rem; display: flex; align-items: center; gap: 0.4rem;">
                                    <svg class="icon-svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; display: inline-block;"><polyline points="20 6 9 17 4 12"></polyline></svg> ${data.provider || 'Động Cơ AI'} Hoạt Động Xuất Sắc!
                                </strong>
                                <span style="font-size: 0.8rem; background: #bbf7d0; padding: 0.2rem 0.5rem; border-radius: 999px; font-weight: 700;">
                                    Độ trễ: ${data.latency || 1}ms
                                </span>
                            </div>
                            <div style="font-size: 0.85rem; line-height: 1.5; color: #15803d;">
                                <strong>Động cơ:</strong> <code>${data.model}</code><br>
                                <strong>Thông điệp:</strong> ${data.reply || data.message}
                            </div>
                        </div>
                    `;
                    if (typeof Toast !== 'undefined') Toast.show(data.message || 'Động cơ AI hoạt động tốt!', 'success');
                } else {
                    resultBox.innerHTML = `
                        <div style="padding: 1rem 1.25rem; border-radius: 10px; background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b;">
                            <div style="font-weight: 700; font-size: 0.95rem; margin-bottom: 0.35rem; display: flex; align-items: center; gap: 0.4rem;">
                                <svg class="icon-svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; display: inline-block;"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg> Kiểm Tra Thất Bại
                            </div>
                            <div style="font-size: 0.85rem; line-height: 1.5;">
                                ${data.message || 'Không thể kết nối. Vui lòng kiểm tra lại.'}
                            </div>
                        </div>
                    `;
                    if (typeof Toast !== 'undefined') Toast.show(data.message || 'Kiểm tra thất bại!', 'error');
                }
            })
            .catch(err => {
                testBtn.disabled = false;
                testBtn.innerHTML = '<svg class="icon-svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; display: inline-block;"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg> <span>Kiểm Tra Động Cơ AI Ngay</span>';
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