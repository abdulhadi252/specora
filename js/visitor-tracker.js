/**
 * js/visitor-tracker.js — Specora Visitor Analytics Tracker
 *
 * Automatically collects visitor information and sends it to the
 * server-side tracking endpoint (api/track.php) via AJAX.
 *
 * This script runs on every page load and tracks:
 *   - Page views
 *   - Browser & device info
 *   - Screen resolution
 *   - Session duration
 *   - Referrer
 *   - Entry/Exit pages
 *
 * It also sends periodic heartbeats to update session duration
 * and detect when a visitor goes offline.
 */

(function() {
    'use strict';

    // ── Configuration ──
    var TRACKING_ENDPOINT = window.TRACKING_ENDPOINT || '../api/track.php';
    var HEARTBEAT_INTERVAL = 30; // seconds between heartbeats
    var PAGE_LOAD_TIME = Date.now();
    var TRACKING_ID = null;

    // ── Helper: Get browser info ──
    function getBrowserInfo() {
        var ua = navigator.userAgent;
        var browser = 'Unknown';
        var version = '';

        if (ua.indexOf('Chrome') > -1) {
            browser = 'Chrome';
            version = ua.match(/Chrome\/([\d.]+)/);
            version = version ? version[1] : '';
        } else if (ua.indexOf('Firefox') > -1) {
            browser = 'Firefox';
            version = ua.match(/Firefox\/([\d.]+)/);
            version = version ? version[1] : '';
        } else if (ua.indexOf('Safari') > -1 && ua.indexOf('Chrome') === -1) {
            browser = 'Safari';
            version = ua.match(/Version\/([\d.]+)/);
            version = version ? version[1] : '';
        } else if (ua.indexOf('Edg') > -1 || ua.indexOf('Edge') > -1) {
            browser = 'Edge';
            version = ua.match(/Edg\/([\d.]+)/);
            version = version ? version[1] : '';
        } else if (ua.indexOf('Opera') > -1 || ua.indexOf('OPR') > -1) {
            browser = 'Opera';
            version = ua.match(/(?:Opera|OPR)\/([\d.]+)/);
            version = version ? version[1] : '';
        }

        return { name: browser, version: version };
    }

    // ── Helper: Get operating system ──
    function getOS() {
        var ua = navigator.userAgent;
        if (ua.indexOf('Windows') > -1) return 'Windows';
        if (ua.indexOf('Android') > -1) return 'Android';
        if (ua.indexOf('iOS') > -1 || ua.indexOf('iPhone') > -1 || ua.indexOf('iPad') > -1) return 'iOS';
        if (ua.indexOf('Mac OS') > -1 || ua.indexOf('Macintosh') > -1) return 'macOS';
        if (ua.indexOf('Linux') > -1) return 'Linux';
        if (ua.indexOf('CrOS') > -1) return 'Chrome OS';
        return 'Unknown';
    }

    // ── Helper: Get device type ──
    function getDeviceType() {
        var ua = navigator.userAgent;
        if (/tablet|iPad|PlayBook|Silk/i.test(ua)) return 'tablet';
        if (/Mobile|Android|iPhone|iPod|Opera Mini|IEMobile|WPDesktop/i.test(ua)) return 'mobile';
        return 'desktop';
    }

    // ── Helper: Get screen resolution ──
    function getScreenResolution() {
        return screen.width + 'x' + screen.height;
    }

    // ── Helper: Get referrer source ──
    function getReferrerSource(referrer) {
        if (!referrer || referrer === '') return 'Direct';
        
        var url = referrer.toLowerCase();
        if (url.indexOf('google.') > -1) return 'Google';
        if (url.indexOf('bing.') > -1) return 'Bing';
        if (url.indexOf('yahoo.') > -1) return 'Yahoo';
        if (url.indexOf('facebook.') > -1 || url.indexOf('fb.') > -1) return 'Facebook';
        if (url.indexOf('instagram.') > -1) return 'Instagram';
        if (url.indexOf('twitter.') > -1 || url.indexOf('x.com') > -1) return 'Twitter';
        if (url.indexOf('linkedin.') > -1) return 'LinkedIn';
        if (url.indexOf('youtube.') > -1) return 'YouTube';
        if (url.indexOf('pinterest.') > -1) return 'Pinterest';
        if (url.indexOf('reddit.') > -1) return 'Reddit';
        if (url.indexOf('whatsapp.') > -1) return 'WhatsApp';
        if (url.indexOf('duckduckgo.') > -1) return 'DuckDuckGo';
        if (url.indexOf('baidu.') > -1) return 'Baidu';
        if (url.indexOf('yandex.') > -1) return 'Yandek';

        // Check if it's an external website (not our own domain)
        var currentDomain = window.location.hostname;
        if (url.indexOf(currentDomain) === -1) {
            return 'Other Website';
        }
        
        return 'Direct';
    }

    // ── Helper: Get page title ──
    function getPageTitle() {
        return document.title || window.location.pathname;
    }

    // ── Send tracking data ──
    function sendTracking(action, extraData) {
        var browserInfo = getBrowserInfo();
        var data = {
            action: action,
            page_url: window.location.href,
            page_title: getPageTitle(),
            screen_resolution: getScreenResolution(),
            browser: browserInfo.name,
            browser_version: browserInfo.version,
            os: getOS(),
            device_type: getDeviceType(),
            referrer: document.referrer || '',
            referrer_source: getReferrerSource(document.referrer),
            session_id: TRACKING_ID,
            timestamp: Math.floor(Date.now() / 1000),
            time_on_page: Math.floor((Date.now() - PAGE_LOAD_TIME) / 1000)
        };

        // Merge any extra data
        if (extraData) {
            for (var key in extraData) {
                if (extraData.hasOwnProperty(key)) {
                    data[key] = extraData[key];
                }
            }
        }

        var xhr = new XMLHttpRequest();
        xhr.open('POST', TRACKING_ENDPOINT, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
        // Build form data
        var formData = [];
        for (var k in data) {
            if (data.hasOwnProperty(k)) {
                formData.push(encodeURIComponent(k) + '=' + encodeURIComponent(data[k]));
            }
        }
        
        xhr.onload = function() {
            if (xhr.status === 200 && action === 'pageview') {
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.session_id) {
                        TRACKING_ID = response.session_id;
                    }
                } catch(e) {}
            }
        };

        xhr.send(formData.join('&'));
    }

    // ── Track page view on load ──
    function trackPageView() {
        sendTracking('pageview');
    }

    // ── Track heartbeat (keeps session alive, updates duration) ──
    function sendHeartbeat() {
        sendTracking('heartbeat');
    }

    // ── Track page exit ──
    function trackPageExit() {
        if (TRACKING_ID) {
            // Use sendBeacon for reliable exit tracking
            var data = 'action=exit&session_id=' + encodeURIComponent(TRACKING_ID) +
                       '&time_on_page=' + Math.floor((Date.now() - PAGE_LOAD_TIME) / 1000) +
                       '&page_url=' + encodeURIComponent(window.location.href);
            
            if (navigator.sendBeacon) {
                navigator.sendBeacon(TRACKING_ENDPOINT, data);
            } else {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', TRACKING_ENDPOINT, false);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.send(data);
            }
        }
    }

    // ── Initialize ──
    function init() {
        // Track the page view after a small delay to ensure DOM is ready
        setTimeout(trackPageView, 100);

        // Send heartbeat every 30 seconds
        setInterval(sendHeartbeat, HEARTBEAT_INTERVAL * 1000);

        // Track exit when user leaves the page
        window.addEventListener('beforeunload', trackPageExit);
        window.addEventListener('pagehide', trackPageExit);

        // Track visibility change (tab switch) as a heartbeat
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                // User came back to the tab — send heartbeat
                sendHeartbeat();
            }
        });
    }

    // ── Start tracking when DOM is ready ──
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
