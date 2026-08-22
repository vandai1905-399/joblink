/**
 * public/js/jobs/ai_recommendation.js
 * Xử lý tương tác Phân tích AI Matching JobLink (Hiển thị tức thì khi bấm nút)
 */

document.addEventListener('DOMContentLoaded', function() {
    initAiJobFitButtons();

    // Nút AI Đánh giá & Tối ưu hồ sơ cá nhân
    const btnOptimize = document.getElementById('btn-ai-optimize-profile');
    if (btnOptimize) {
        btnOptimize.addEventListener('click', function(e) {
            e.preventDefault();
            runAiProfileOptimizer();
        });
    }
});

function initAiJobFitButtons() {
    document.querySelectorAll('[data-ai-job-fit]').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const jobId = this.getAttribute('data-ai-job-fit');
            let rawData = this.getAttribute('data-ai-data');
            let initialData = null;

            if (rawData) {
                try {
                    initialData = JSON.parse(rawData);
                } catch (err) {
                    initialData = null;
                }
            }

            openAiFitModal(jobId, initialData);
        });
    });
}

/**
 * Mở modal phân tích độ tương thích AI (Hiển thị tức thì nếu có sẵn dữ liệu phân tích)
 */
function openAiFitModal(jobId, initialData = null) {
    // Tạo modal container nếu chưa có
    let modal = document.getElementById('aiFitModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'aiFitModal';
        modal.className = 'ai-modal-overlay';
        modal.innerHTML = `
            <div class="ai-modal-content">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.25rem;">
                    <div style="display:flex; align-items:center; gap:0.5rem;">
                        <span style="display:inline-flex; align-items:center; justify-content:center; width:34px; height:34px; border-radius:8px; background:#f0fdfa; color:#0d9488;">
                            ✨
                        </span>
                        <h3 style="margin:0; font-size:1.2rem; font-weight:800; color:#0f172a;">AI Phân Tích Độ Tương Thích</h3>
                    </div>
                    <button type="button" onclick="closeAiFitModal()" style="background:none; border:none; font-size:1.5rem; color:#94a3b8; cursor:pointer; line-height:1;">&times;</button>
                </div>
                <div id="aiModalBody" style="min-height:160px;"></div>
            </div>
        `;
        document.body.appendChild(modal);
        modal.addEventListener('click', function(e) {
            if (e.target === modal) closeAiFitModal();
        });
    }

    modal.style.display = 'flex';
    const body = document.getElementById('aiModalBody');

    // 1. NẾU ĐÃ CÓ DỮ LIỆU TỰ ĐỘNG PHÂN TÍCH -> HIỆN NỘI DUNG NGAY LẬP TỨC!
    if (initialData && initialData.score) {
        renderAiFitModalBody(initialData, body, jobId);
        return;
    }

    // 2. NẾU CHƯA CÓ SẴN (VD: Khách vãng lai vừa đăng nhập) -> GỌI API PHÂN TÍCH
    body.innerHTML = `
        <div style="text-align:center; color:#64748b; padding:2rem 0;">
            <div style="font-size:1.8rem; margin-bottom:0.5rem;">⚡</div>
            <p style="margin:0; font-size:0.95rem; font-weight:600;">Đang tính toán độ tương thích đa chiều...</p>
        </div>
    `;

    fetch('/api/ai/job-fit/' + jobId, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.data) {
            const fit = data.data;

            // Cập nhật đồng bộ huy hiệu trên thẻ công việc ngoài màn hình (nếu có)
            const cardBadges = document.querySelectorAll(`[data-job-badge="${jobId}"]`);
            cardBadges.forEach(badge => {
                badge.innerHTML = `<svg class="icon-svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; display: inline-block;"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"></path></svg> <span>Tương thích ${fit.score}%</span>`;
                badge.className = `ai-match-badge ${fit.level}`;
            });

            renderAiFitModalBody(fit, body, jobId);
        } else {
            body.innerHTML = `<p style="color:#ef4444; text-align:center;">Không thể tải dữ liệu phân tích. Vui lòng thử lại sau.</p>`;
        }
    })
    .catch(err => {
        body.innerHTML = `<p style="color:#ef4444; text-align:center;">Lỗi kết nối máy chủ.</p>`;
    });
function dedupeSkills(arr) {
    const seen = new Set();
    const res = [];
    (arr || []).forEach(s => {
        if (!s) return;
        const k = s.trim().toLowerCase();
        if (!seen.has(k)) {
            seen.add(k);
            res.push(s.trim());
        }
    });
    return res;
}

/**
 * Render giao diện chi tiết phân tích AI vào Modal Body
 */
function renderAiFitModalBody(fit, body, jobId) {
    let matchedSkills = dedupeSkills(fit.matched_skills || []);
    let missingSkills = dedupeSkills(fit.missing_skills || []);

    // Loại bỏ khỏi missingSkills những kỹ năng đã có trong matchedSkills
    const matchedLower = new Set(matchedSkills.map(s => s.toLowerCase()));
    missingSkills = missingSkills.filter(s => !matchedLower.has(s.toLowerCase()));

    const reasons = fit.reasons || [];
    const breakdown = fit.breakdown || { skills: 80, location: 80, employment: 80, experience: 80 };
    const adviceList = fit.advice || [
        "Hãy nhấn mạnh các kỹ năng nổi bật của bạn trong phần giới thiệu khi liên hệ với nhà tuyển dụng.",
        "Xác nhận lại mức lương và ca làm việc phù hợp với lịch trình của bạn."
    ];

    let matchedHtml = (matchedSkills.length) 
        ? matchedSkills.map(s => `<span class="ai-skill-tag matched"><svg class="icon-svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; display: inline-block;"><polyline points="20 6 9 17 4 12"></polyline></svg> <span>${escapeHtml(s)}</span></span>`).join(' ')
        : '<span style="font-size:0.82rem; color:#64748b;">Chưa có kỹ năng khớp trực tiếp</span>';

    let missingHtml = (missingSkills.length)
        ? missingSkills.map(s => `<span class="ai-skill-tag missing">+ ${escapeHtml(s)}</span>`).join(' ')
        : '<span style="font-size:0.82rem; color:#10b981;">Đã đáp ứng đầy đủ yêu cầu chính</span>';

    let reasonsHtml = (reasons.length)
        ? reasons.map(r => `<li class="ai-reason-item"><svg class="icon-svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; display: inline-block; color: #0d9488;"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg> <span>${escapeHtml(r)}</span></li>`).join('')
        : `<li class="ai-reason-item"><span>•</span> <span>Công việc phù hợp với hồ sơ kỹ năng và vị trí mong muốn của bạn.</span></li>`;

    let adviceHtml = (adviceList.length)
        ? adviceList.map(a => `<li style="margin-bottom:0.4rem;">${escapeHtml(a)}</li>`).join('')
        : '';

    const aiProvider = fit.ai_provider || 'Google Gemini AI';

    body.innerHTML = `
        <div>
            <div style="display:flex; justify-content:space-between; align-items:center; background:linear-gradient(135deg, #f0fdfa 0%, #f0f9ff 100%); padding:1rem 1.25rem; border-radius:12px; border:1px solid #ccfbf1; margin-bottom:1.25rem;">
                <div>
                    <div style="font-size:0.82rem; color:#64748b; font-weight:600; display:flex; align-items:center; gap:0.4rem;">
                        <span>ĐỘ TƯƠNG THÍCH TỔNG QUAN</span>
                        <span style="font-size:0.72rem; padding:0.15rem 0.45rem; border-radius:6px; background:#e0f2fe; color:#0369a1; font-weight:700;">${escapeHtml(aiProvider)}</span>
                    </div>
                    <div style="font-size:1.6rem; font-weight:800; color:${fit.color || '#0d9488'}; display:flex; align-items:center; gap:0.4rem;">
                        ${fit.score}% <span style="font-size:0.95rem; font-weight:700;">(${escapeHtml(fit.label || 'Phù Hợp')})</span>
                    </div>
                </div>
                <div style="text-align:right;">
                    <span class="ai-match-badge ${fit.level || 'high'}" style="font-size:0.82rem; padding:0.35rem 0.75rem;">
                        <svg class="icon-svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; display: inline-block;"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"></path></svg>
                        <span>Tương thích ${fit.score}%</span>
                    </span>
                </div>
            </div>

            <div class="ai-breakdown-grid">
                <div class="ai-breakdown-card">
                    <div class="ai-breakdown-score">${breakdown.skills || 80}%</div>
                    <div class="ai-breakdown-label">Kỹ năng & Chức danh</div>
                </div>
                <div class="ai-breakdown-card">
                    <div class="ai-breakdown-score">${breakdown.location || 80}%</div>
                    <div class="ai-breakdown-label">Địa điểm làm việc</div>
                </div>
                <div class="ai-breakdown-card">
                    <div class="ai-breakdown-score">${breakdown.employment || 80}%</div>
                    <div class="ai-breakdown-label">Lương & Hình thức</div>
                </div>
                <div class="ai-breakdown-card">
                    <div class="ai-breakdown-score">${breakdown.experience || 80}%</div>
                    <div class="ai-breakdown-label">Kinh nghiệm & Uy tín</div>
                </div>
            </div>

            <div style="margin-bottom:1rem;">
                <div style="font-weight:700; font-size:0.9rem; color:#0f172a; margin-bottom:0.4rem;">Kỹ năng bạn đã đáp ứng:</div>
                <div style="display:flex; flex-wrap:wrap; gap:0.35rem;">${matchedHtml}</div>
            </div>

            <div style="margin-bottom:1.25rem;">
                <div style="font-weight:700; font-size:0.9rem; color:#0f172a; margin-bottom:0.4rem;">Kỹ năng nhà tuyển dụng yêu cầu thêm:</div>
                <div style="display:flex; flex-wrap:wrap; gap:0.35rem;">${missingHtml}</div>
            </div>

            <div style="background:#f8fafc; border-radius:10px; padding:1rem; border:1px solid #e2e8f0; margin-bottom:1.25rem;">
                <div style="font-weight:700; font-size:0.88rem; color:#0f172a; margin-bottom:0.4rem;">💡 Điểm nổi bật phù hợp:</div>
                <ul class="ai-reasons-list">${reasonsHtml}</ul>
            </div>

            <div style="background:#eff6ff; border-radius:10px; padding:1rem; border:1px solid #bfdbfe;">
                <div style="font-weight:700; font-size:0.88rem; color:#1e40af; margin-bottom:0.4rem;">💬 Lời khuyên ứng tuyển từ AI:</div>
                <ul style="margin:0; padding-left:1.2rem; font-size:0.85rem; color:#1e3a8a; line-height:1.6;">${adviceHtml}</ul>
            </div>

            <div style="margin-top:1.5rem; display:flex; justify-content:space-between; align-items:center;">
                <a href="/jobs/${jobId}" class="btn btn-primary btn-sm" style="font-weight:700; padding:0.4rem 1rem;">
                    Xem Chi Tiết & Ứng Tuyển &rarr;
                </a>
                <button type="button" onclick="closeAiFitModal()" class="btn btn-outline btn-sm">Đóng</button>
            </div>
        </div>
    `;
}

function closeAiFitModal() {
    const modal = document.getElementById('aiFitModal');
    if (modal) modal.style.display = 'none';
}

/**
 * Trợ lý AI Đánh giá & Tối ưu hồ sơ
 */
function runAiProfileOptimizer() {
    const btn = document.getElementById('btn-ai-optimize-profile');
    const resultBox = document.getElementById('ai-profile-optimizer-result');
    if (!resultBox) return;

    btn.disabled = true;
    btn.innerHTML = '⏳ <span>AI đang phân tích hồ sơ...</span>';
    resultBox.style.display = 'block';
    resultBox.innerHTML = `
        <div style="padding: 1.5rem; text-align: center; color: #64748b;">
            <div style="font-size: 1.5rem; margin-bottom: 0.5rem;">🤖</div>
            <div>Đang đối chiếu hồ sơ của bạn với các tiêu chuẩn tuyển dụng mới nhất...</div>
        </div>
    `;

    fetch('/api/ai/optimize-profile', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '✨ <span>AI Đánh Giá & Gợi Ý Tối Ưu Hồ Sơ</span>';

        if (data.success && data.data) {
            const opt = data.data;
            let strengthsHtml = opt.strengths.map(s => `<li style="margin-bottom: 0.35rem; color: #065f46;">✓ ${escapeHtml(s)}</li>`).join('');
            let suggestionsHtml = opt.suggestions.map(s => `<li style="margin-bottom: 0.35rem; color: #9a3412;">👉 ${escapeHtml(s)}</li>`).join('');

            resultBox.innerHTML = `
                <div style="padding: 1.25rem; border-radius: 12px; background: #ffffff; border: 1px solid #ccfbf1; box-shadow: 0 4px 12px rgba(13,148,136,0.08);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.75rem;">
                        <div>
                            <span style="font-size: 0.8rem; font-weight: 700; color: #0d9488; text-transform: uppercase; letter-spacing: 0.05em;">Điểm hoàn thiện hồ sơ</span>
                            <div style="font-size: 1.6rem; font-weight: 800; color: #0f172a;">${opt.score}/100</div>
                        </div>
                        <span class="badge badge-success" style="font-size: 0.82rem; padding: 0.3rem 0.65rem;">
                            ✨ ${escapeHtml(opt.ai_provider || 'Google Gemini AI')}
                        </span>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div style="background: #f0fdf4; padding: 0.85rem; border-radius: 8px; border: 1px solid #bbf7d0;">
                            <strong style="font-size: 0.85rem; color: #166534; display: block; margin-bottom: 0.4rem;">🌟 Điểm mạnh nổi bật:</strong>
                            <ul style="margin: 0; padding-left: 1.1rem; font-size: 0.82rem; line-height: 1.5;">${strengthsHtml}</ul>
                        </div>
                        <div style="background: #fff7ed; padding: 0.85rem; border-radius: 8px; border: 1px solid #fed7aa;">
                            <strong style="font-size: 0.85rem; color: #9a3412; display: block; margin-bottom: 0.4rem;">💡 Gợi ý cải thiện để tăng 40% phỏng vấn:</strong>
                            <ul style="margin: 0; padding-left: 1.1rem; font-size: 0.82rem; line-height: 1.5;">${suggestionsHtml}</ul>
                        </div>
                    </div>
                </div>
            `;
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '✨ <span>AI Đánh Giá & Gợi Ý Tối Ưu Hồ Sơ</span>';
        resultBox.innerHTML = `<div style="padding: 1rem; color: #ef4444; text-align: center;">Không thể phân tích hồ sơ lúc này.</div>`;
    });
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