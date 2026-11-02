/**
 * public/js/mobile.js - Universal Mobile Web Interaction Engine for JobLink
 * Điều khiển Drawer, Bottom Navigation, Sticky Actions, Messenger Mobile, và MasterWorkspace
 */
(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        initJoblinkMobileUI();
    });

    function initJoblinkMobileUI() {
        const drawer = document.getElementById('joblinkMobileDrawer') || document.getElementById('seekerMobileDrawer');
        const openBtn = document.getElementById('joblinkDrawerOpenBtn') || document.getElementById('seekerDrawerOpenBtn');
        const closeBtn = document.getElementById('joblinkDrawerCloseBtn') || document.getElementById('seekerDrawerCloseBtn');
        const backdrop = document.getElementById('joblinkDrawerBackdrop') || document.getElementById('seekerDrawerBackdrop');
        const avatarTrigger = document.getElementById('jmhAvatarTrigger') || document.getElementById('smhAvatarTrigger');
        const subBar = document.getElementById('joblinkMobileSubbar') || document.getElementById('seekerMobileSubbar');
        const subBarTitle = document.getElementById('jmsTitle') || document.getElementById('smsTitle');
        const subBarBackBtn = document.getElementById('jmsBackBtn') || document.getElementById('smsBackBtn');
        const bottomNav = document.getElementById('joblinkBottomNav') || document.getElementById('seekerBottomNav');

        // =====================================================================
        // 1. DRAWER TOGGLE & SWIPE GESTURE
        // =====================================================================
        function openDrawer() {
            if (!drawer) return;
            drawer.classList.add('open');
            document.body.style.overflow = 'hidden';
        }

        function closeDrawer() {
            if (!drawer) return;
            drawer.classList.remove('open');
            document.body.style.overflow = '';
        }

        if (openBtn) openBtn.addEventListener('click', openDrawer);
        if (avatarTrigger) avatarTrigger.addEventListener('click', openDrawer);
        if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
        if (backdrop) backdrop.addEventListener('click', closeDrawer);

        // Đóng khi nhấn phím Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && drawer && drawer.classList.contains('open')) {
                closeDrawer();
            }
        });

        // Tự động đóng drawer khi bấm vào bất kỳ link điều hướng nào
        if (drawer) {
            drawer.querySelectorAll('.jmd-link, .smd-link').forEach(function(link) {
                link.addEventListener('click', function() {
                    closeDrawer();
                });
            });
        }

        // Vuốt sang trái để đóng Drawer mượt mà (Swipe Left to Close)
        var touchStartX = 0;
        var touchStartY = 0;
        if (drawer) {
            drawer.addEventListener('touchstart', function(e) {
                if (e.touches && e.touches[0]) {
                    touchStartX = e.touches[0].clientX;
                    touchStartY = e.touches[0].clientY;
                }
            }, { passive: true });

            drawer.addEventListener('touchend', function(e) {
                if (e.changedTouches && e.changedTouches[0]) {
                    var diffX = e.changedTouches[0].clientX - touchStartX;
                    var diffY = Math.abs(e.changedTouches[0].clientY - touchStartY);
                    if (diffX < -60 && diffY < 80) {
                        closeDrawer();
                    }
                }
            }, { passive: true });
        }

        // =====================================================================
        // 2. BOTTOM NAV ACTIVE TAB HIGHLIGHTING
        // =====================================================================
        function updateActiveBottomNavTab(currentUrl) {
            if (!bottomNav) return;
            var path = (currentUrl || window.location.pathname).toLowerCase();
            var navItems = bottomNav.querySelectorAll('.jbn-item, .sbn-item');
            
            navItems.forEach(function(item) { item.classList.remove('active'); });

            var matched = null;
            if (path.includes('/applied') || path.includes('applied-jobs')) {
                matched = bottomNav.querySelector('[data-tab="applied"]');
            } else if (path.includes('/meetings') || path.includes('interview')) {
                matched = bottomNav.querySelector('[data-tab="meetings"]');
            } else if (path.includes('/jobs/create') || path.includes('create-job')) {
                matched = bottomNav.querySelector('[data-tab="create-job"]');
            } else if (path.includes('/applications')) {
                matched = bottomNav.querySelector('[data-tab="applications"]');
            } else if (path.includes('/employers')) {
                matched = bottomNav.querySelector('[data-tab="employers"]');
            } else if (path.includes('/jobs')) {
                matched = bottomNav.querySelector('[data-tab="jobs"]');
            } else if (path.includes('/messages')) {
                matched = bottomNav.querySelector('[data-tab="messages"]');
            } else if (path.includes('/about')) {
                matched = bottomNav.querySelector('[data-tab="about"]');
            } else if (path.includes('/accounts')) {
                matched = bottomNav.querySelector('[data-tab="accounts"]');
            } else if (path.includes('/ai-settings') || path.includes('/ai-analytics')) {
                matched = bottomNav.querySelector('[data-tab="ai"]');
            } else if (path.includes('/profile') || path.includes('/settings') || path.includes('/change-password')) {
                matched = bottomNav.querySelector('[data-tab="profile"]');
            } else if (path === '/' || path.includes('/dashboard') || path.endsWith('/joblink/') || path.endsWith('/public/')) {
                matched = bottomNav.querySelector('[data-tab="dashboard"]') || bottomNav.querySelector('[data-tab="home"]');
            }

            if (matched) {
                matched.classList.add('active');
            }
        }

        updateActiveBottomNavTab();

        // =====================================================================
        // 3. MASTER WORKSPACE INTEGRATION (SUB-BAR & TAB SYNC)
        // =====================================================================
        if (window.MasterWorkspace) {
            var originalSwitchTab = window.MasterWorkspace.switchTab;
            var originalOpenTab = window.MasterWorkspace.openTab;

            window.MasterWorkspace.switchTab = function(tabKey) {
                var res = originalSwitchTab.apply(this, arguments);
                handleTabChangeForMobile(tabKey);
                return res;
            };

            window.MasterWorkspace.openTab = function(url, title, icon, triggerEl) {
                var res = originalOpenTab.apply(this, arguments);
                setTimeout(function() {
                    handleTabChangeForMobile(window.MasterWorkspace.currentActiveTab, url, title);
                }, 30);
                return res;
            };

            function handleTabChangeForMobile(tabKey, tabUrl, tabTitle) {
                if (window.innerWidth > 900) return;

                if (tabKey === 'root') {
                    if (subBar) subBar.classList.remove('active');
                    updateActiveBottomNavTab(window.location.pathname);
                } else {
                    var tabData = window.MasterWorkspace.openedTabs.get(tabKey);
                    var title = tabTitle || (tabData ? tabData.title : 'Nội Dung');
                    var url = tabUrl || (tabData ? tabData.url : '');

                    if (subBar && subBarTitle) {
                        subBarTitle.textContent = title;
                        subBar.classList.add('active');
                    }
                    if (url) {
                        updateActiveBottomNavTab(url);
                    }
                }
            }

            if (subBarBackBtn) {
                subBarBackBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (window.MasterWorkspace) {
                        window.MasterWorkspace.switchTab('root');
                    }
                    if (subBar) subBar.classList.remove('active');
                    updateActiveBottomNavTab();
                });
            }
        }

        // =====================================================================
        // 4. STICKY ACTION BAR TRÊN TRANG CHI TIẾT VIỆC LÀM (MOBILE)
        // =====================================================================
        initStickyJobActions();

        function initStickyJobActions() {
            var isJobDetailPage = window.location.pathname.includes('/jobs/') && !window.location.pathname.endsWith('/jobs');
            if (!isJobDetailPage) return;

            var existingSticky = document.querySelector('.mobile-sticky-job-actions');
            if (existingSticky) return;

            var desktopApplyBtn = document.getElementById('btnOpenApplyModal');
            var desktopApplyForm = document.querySelector('form[action*="/seeker/apply/"]');
            var desktopSaveBtn = document.querySelector('.btn-save-job');

            if (!desktopApplyBtn && !desktopApplyForm && !desktopSaveBtn) return;

            var stickyBar = document.createElement('div');
            stickyBar.className = 'mobile-sticky-job-actions';

            var html = '';
            if (desktopSaveBtn) {
                var isSaved = desktopSaveBtn.classList.contains('saved');
                html += '<button type="button" class="mobile-sticky-save-btn" id="mobileStickySaveBtn" title="Lưu việc">' +
                        '<svg width="20" height="20" viewBox="0 0 24 24" fill="' + (isSaved ? '#e11d48' : 'none') + '" stroke="' + (isSaved ? '#e11d48' : 'currentColor') + '" stroke-width="2"><path d="m19 21-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path></svg>' +
                        '</button>';
            }

            if (desktopApplyBtn) {
                html += '<button type="button" class="mobile-sticky-apply-btn" id="mobileStickyApplyBtn">' +
                        '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"></path><path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"></path></svg>' +
                        '<span>Đăng Ký & Ứng Tuyển</span>' +
                        '</button>';
            } else if (desktopApplyForm) {
                html += '<button type="button" class="mobile-sticky-apply-btn" id="mobileStickyApplyBtn">' +
                        '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"></path><path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"></path></svg>' +
                        '<span>Nộp Đơn Ứng Tuyển Ngay</span>' +
                        '</button>';
            } else {
                html += '<a href="' + (window.APP_URL || '') + '/auth/login" class="mobile-sticky-apply-btn">' +
                        '<span>Đăng Nhập Để Ứng Tuyển</span>' +
                        '</a>';
            }

            stickyBar.innerHTML = html;
            document.body.appendChild(stickyBar);

            // Gắn sự kiện kích hoạt
            var mSave = document.getElementById('mobileStickySaveBtn');
            if (mSave && desktopSaveBtn) {
                mSave.addEventListener('click', function() {
                    desktopSaveBtn.click();
                    setTimeout(function() {
                        var updatedSaved = desktopSaveBtn.classList.contains('saved');
                        var svg = mSave.querySelector('svg');
                        if (svg) {
                            svg.setAttribute('fill', updatedSaved ? '#e11d48' : 'none');
                            svg.setAttribute('stroke', updatedSaved ? '#e11d48' : 'currentColor');
                        }
                    }, 200);
                });
            }

            var mApply = document.getElementById('mobileStickyApplyBtn');
            if (mApply) {
                mApply.addEventListener('click', function() {
                    if (desktopApplyBtn) {
                        desktopApplyBtn.click();
                    } else if (desktopApplyForm) {
                        desktopApplyForm.submit();
                    }
                });
            }
        }

        // =====================================================================
        // 5. MESSENGER MOBILE NAVIGATION (CHUYỂN ĐỔI GIỮA DANH SÁCH & PHÒNG CHAT)
        // =====================================================================
        initMessengerMobile();

        function initMessengerMobile() {
            var msgContainer = document.getElementById('messengerContainer');
            if (!msgContainer) return;

            var msgMain = document.getElementById('messengerMain');
            var convItems = msgContainer.querySelectorAll('.conversation-item');

            // Kiểm tra xem đang có partner chat được chọn sẵn qua query string không
            var hasActivePartner = window.location.search.includes('partner_id=') || 
                                   document.querySelector('.conversation-item.active') ||
                                   window.location.search.includes('partner=ai_joby');

            if (hasActivePartner && window.innerWidth <= 900) {
                msgContainer.classList.add('mobile-in-chat');
            }

            // Tạo thanh quay lại danh sách trên đỉnh khung chat di động nếu chưa có
            if (msgMain && !msgMain.querySelector('.messenger-mobile-back-bar')) {
                var backBar = document.createElement('div');
                backBar.className = 'messenger-mobile-back-bar';
                backBar.innerHTML = '<button type="button" class="messenger-mobile-back-btn" id="msgMobileBackBtn">' +
                                    '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"></polyline></svg>' +
                                    '<span>Danh sách tin nhắn</span>' +
                                    '</button>';
                msgMain.insertBefore(backBar, msgMain.firstChild);

                var backBtn = document.getElementById('msgMobileBackBtn');
                if (backBtn) {
                    backBtn.addEventListener('click', function() {
                        msgContainer.classList.remove('mobile-in-chat');
                    });
                }
            }

            // Khi người dùng bấm vào một cuộc trò chuyện bất kỳ
            convItems.forEach(function(item) {
                item.addEventListener('click', function() {
                    if (window.innerWidth <= 900) {
                        msgContainer.classList.add('mobile-in-chat');
                    }
                });
            });
        }

        // =====================================================================
        // 6. HELPER MỞ CHATBOT JOBY TỪ MOBILE MENU
        // =====================================================================
        window.joblinkOpenChat = function() {
            var launcherBtn = document.getElementById('ai-chat-btn-toggle');
            if (launcherBtn) {
                launcherBtn.click();
            } else {
                window.location.href = (window.APP_URL || '') + '/seeker/messages?partner_id=ai_joby';
            }
        };
    }
})();
