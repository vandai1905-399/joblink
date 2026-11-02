/**
 * public/js/seeker_mobile.js - Backward compatibility wrapper delegating to mobile.js
 */
if (!window._joblinkMobileLoaded) {
    window._joblinkMobileLoaded = true;
    var s = document.createElement('script');
    s.src = (window.APP_URL || '') + '/js/mobile.js';
    document.body.appendChild(s);
}
