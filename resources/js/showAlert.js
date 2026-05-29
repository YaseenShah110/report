/**
 * ════════════════════════════════════════════════════════════════════
 *  showAlert  —  Global confirmation dialog
 *  showToast  —  Global auto-dismissing toast
 *
 *  Imported in app.js BEFORE window.showToast is defined.
 *  Therefore this file's window.showToast gets overwritten by app.js.
 *
 *  SOLUTION: This file only handles showAlert (the confirmation dialog).
 *  showToast is defined separately — see the window.showToast block
 *  at the BOTTOM of this file which runs AFTER app.js overwrites it,
 *  using a DOMContentLoaded + a safe no-op guard.
 *
 *  Actually the cleanest fix: showAlert.js does NOT set window.showToast.
 *  It calls window.showToast internally. app.js owns showToast.
 *
 *  Usage:
 *    window.showAlert({ type, title, message, confirmText, cancelText, onConfirm, onCancel })
 *    window.showToast('Your message here', 'success')   ← defined in app.js
 * ════════════════════════════════════════════════════════════════════
 */

(function (global) {
    "use strict";

    // ─── Inject styles once ────────────────────────────────────────────
    const STYLE_ID = "__sa_styles__";
    if (!document.getElementById(STYLE_ID)) {
        const style = document.createElement("style");
        style.id = STYLE_ID;
        style.textContent = `
      .sa-backdrop *, .sa-backdrop *::before, .sa-backdrop *::after { box-sizing:border-box; margin:0; padding:0; -webkit-font-smoothing:antialiased; }

      :root {
        --sa-font: 'Inter', ui-sans-serif, system-ui, sans-serif;
        --sa-radius-lg: 1.25rem; --sa-radius-btn: 0.75rem;
        --sa-shadow: 0 32px 80px -8px rgba(0,0,0,0.22), 0 8px 24px -4px rgba(0,0,0,0.12);
        --sa-bg: #ffffff; --sa-bg-header: #f8fafc;
        --sa-border: rgba(15,23,42,0.08); --sa-border-hdr: rgba(15,23,42,0.07);
        --sa-text-title: #0f172a; --sa-text-body: #475569;
        --sa-backdrop-bg: rgba(15,23,42,0.45);
        --sa-ring-danger:rgba(239,68,68,0.12); --sa-ring-warning:rgba(245,158,11,0.12);
        --sa-ring-success:rgba(16,185,129,0.12); --sa-ring-info:rgba(99,102,241,0.12);
        --sa-icon-bg-danger:rgba(239,68,68,0.1); --sa-icon-bg-warning:rgba(245,158,11,0.1);
        --sa-icon-bg-success:rgba(16,185,129,0.1); --sa-icon-bg-info:rgba(99,102,241,0.1);
        --sa-icon-danger:#ef4444; --sa-icon-warning:#f59e0b;
        --sa-icon-success:#10b981; --sa-icon-info:#6366f1;
        --sa-cancel-bg:#f1f5f9; --sa-cancel-bg-h:#e2e8f0;
        --sa-cancel-text:#475569; --sa-cancel-border:rgba(15,23,42,0.08);
      }
      html.dark {
        --sa-bg:#1e293b; --sa-bg-header:#162032;
        --sa-border:rgba(255,255,255,0.07); --sa-border-hdr:rgba(255,255,255,0.06);
        --sa-text-title:#f1f5f9; --sa-text-body:#94a3b8;
        --sa-backdrop-bg:rgba(0,0,0,0.65);
        --sa-ring-danger:rgba(239,68,68,0.18); --sa-ring-warning:rgba(245,158,11,0.18);
        --sa-ring-success:rgba(16,185,129,0.18); --sa-ring-info:rgba(99,102,241,0.18);
        --sa-icon-bg-danger:rgba(239,68,68,0.15); --sa-icon-bg-warning:rgba(245,158,11,0.15);
        --sa-icon-bg-success:rgba(16,185,129,0.15); --sa-icon-bg-info:rgba(99,102,241,0.15);
        --sa-cancel-bg:rgba(255,255,255,0.06); --sa-cancel-bg-h:rgba(255,255,255,0.10);
        --sa-cancel-text:#94a3b8; --sa-cancel-border:rgba(255,255,255,0.08);
        --sa-shadow:0 32px 80px -8px rgba(0,0,0,0.6),0 8px 24px -4px rgba(0,0,0,0.4);
      }

      .sa-backdrop {
        position:fixed; inset:0; z-index:9000;
        display:flex; align-items:center; justify-content:center; padding:1rem;
        background:var(--sa-backdrop-bg);
        backdrop-filter:blur(6px); -webkit-backdrop-filter:blur(6px);
        font-family:var(--sa-font);
        opacity:0; transition:opacity 0.25s ease; will-change:opacity;
      }
      .sa-backdrop.sa-in  { opacity:1; }
      .sa-backdrop.sa-out { opacity:0; }

      .sa-card {
        position:relative; width:100%; max-width:420px;
        background:var(--sa-bg); border-radius:var(--sa-radius-lg);
        box-shadow:var(--sa-shadow); border:1px solid var(--sa-border); overflow:hidden;
        transform:scale(0.88) translateY(20px); opacity:0;
        transition:transform 0.35s cubic-bezier(0.34,1.56,0.64,1),opacity 0.25s ease;
        will-change:transform,opacity;
      }
      .sa-backdrop.sa-in  .sa-card { transform:scale(1) translateY(0); opacity:1; }
      .sa-backdrop.sa-out .sa-card { transform:scale(0.94) translateY(10px); opacity:0; transition-duration:0.2s; }

      .sa-header {
        display:flex; flex-direction:column; align-items:center;
        padding:2rem 1.75rem 1.25rem;
        background:var(--sa-bg-header); border-bottom:1px solid var(--sa-border-hdr); text-align:center;
      }
      .sa-icon-ring {
        position:relative; width:72px; height:72px; border-radius:50%;
        display:flex; align-items:center; justify-content:center; margin-bottom:1.125rem;
      }
      .sa-icon-ring::before,.sa-icon-ring::after {
        content:''; position:absolute; border-radius:50%; opacity:0;
        animation:sa-ring-pulse 2s ease-in-out infinite;
      }
      .sa-icon-ring::before { inset:-8px;  animation-delay:0.4s; }
      .sa-icon-ring::after  { inset:-16px; animation-delay:0.8s; }

      .sa-ring-danger  { background:var(--sa-icon-bg-danger);  }
      .sa-ring-danger::before,.sa-ring-danger::after   { background:var(--sa-ring-danger); }
      .sa-ring-warning { background:var(--sa-icon-bg-warning); }
      .sa-ring-warning::before,.sa-ring-warning::after { background:var(--sa-ring-warning); }
      .sa-ring-success { background:var(--sa-icon-bg-success); }
      .sa-ring-success::before,.sa-ring-success::after { background:var(--sa-ring-success); }
      .sa-ring-info    { background:var(--sa-icon-bg-info);    }
      .sa-ring-info::before,.sa-ring-info::after       { background:var(--sa-ring-info); }

      .sa-icon-svg { width:32px; height:32px; animation:sa-icon-pop 0.5s cubic-bezier(0.34,1.56,0.64,1) 0.15s both; }
      .sa-icon-danger  { color:var(--sa-icon-danger);  }
      .sa-icon-warning { color:var(--sa-icon-warning); }
      .sa-icon-success { color:var(--sa-icon-success); }
      .sa-icon-info    { color:var(--sa-icon-info);    }

      .sa-title { font-size:1.0625rem; font-weight:700; letter-spacing:-0.02em; color:var(--sa-text-title); animation:sa-slide-up 0.4s ease 0.1s both; }
      .sa-body  { padding:1.25rem 1.75rem 1.5rem; text-align:center; animation:sa-slide-up 0.4s ease 0.18s both; }
      .sa-message { font-size:0.875rem; line-height:1.65; color:var(--sa-text-body); }
      .sa-message strong { color:var(--sa-text-title); font-weight:600; }

      .sa-footer { display:flex; gap:0.625rem; padding:0 1.75rem 1.75rem; animation:sa-slide-up 0.4s ease 0.25s both; }

      .sa-btn {
        flex:1; display:inline-flex; align-items:center; justify-content:center; gap:0.5rem;
        padding:0.75rem 1rem; border-radius:var(--sa-radius-btn);
        font-family:var(--sa-font); font-size:0.875rem; font-weight:600;
        cursor:pointer; border:none; outline:none;
        transition:transform 0.15s cubic-bezier(0.34,1.56,0.64,1),box-shadow 0.15s ease,background 0.15s ease;
        -webkit-tap-highlight-color:transparent;
      }
      .sa-btn:active { transform:scale(0.96); }
      .sa-btn:focus-visible { outline:2px solid currentColor; outline-offset:2px; }

      .sa-btn-cancel { background:var(--sa-cancel-bg); color:var(--sa-cancel-text); border:1px solid var(--sa-cancel-border); }
      .sa-btn-cancel:hover { background:var(--sa-cancel-bg-h); transform:translateY(-1px); }

      .sa-btn-danger  { background:linear-gradient(135deg,#ef4444,#dc2626); color:#fff; box-shadow:0 4px 14px rgba(239,68,68,0.4); }
      .sa-btn-danger:hover  { background:linear-gradient(135deg,#f87171,#ef4444); transform:translateY(-1px); box-shadow:0 6px 20px rgba(239,68,68,0.5); }
      .sa-btn-warning { background:linear-gradient(135deg,#f59e0b,#d97706); color:#fff; box-shadow:0 4px 14px rgba(245,158,11,0.4); }
      .sa-btn-warning:hover { background:linear-gradient(135deg,#fbbf24,#f59e0b); transform:translateY(-1px); }
      .sa-btn-success { background:linear-gradient(135deg,#10b981,#059669); color:#fff; box-shadow:0 4px 14px rgba(16,185,129,0.4); }
      .sa-btn-success:hover { background:linear-gradient(135deg,#34d399,#10b981); transform:translateY(-1px); }
      .sa-btn-info    { background:linear-gradient(135deg,#6366f1,#4f46e5); color:#fff; box-shadow:0 4px 14px rgba(99,102,241,0.4); }
      .sa-btn-info:hover    { background:linear-gradient(135deg,#818cf8,#6366f1); transform:translateY(-1px); }

      .sa-btn-spinner { width:15px; height:15px; border:2px solid rgba(255,255,255,0.35); border-top-color:#fff; border-radius:50%; animation:sa-spin 0.65s linear infinite; display:none; }
      .sa-btn.sa-loading .sa-btn-spinner { display:block; }
      .sa-btn.sa-loading .sa-btn-label   { opacity:0.7; }
      .sa-btn.sa-loading { pointer-events:none; opacity:0.85; }

      .sa-accent { position:absolute; top:0; left:0; right:0; height:3px; border-radius:var(--sa-radius-lg) var(--sa-radius-lg) 0 0; transform-origin:left; animation:sa-accent-grow 0.5s cubic-bezier(0.34,1.56,0.64,1) 0.05s both; }
      .sa-accent-danger  { background:linear-gradient(90deg,#ef4444,#f97316); }
      .sa-accent-warning { background:linear-gradient(90deg,#f59e0b,#eab308); }
      .sa-accent-success { background:linear-gradient(90deg,#10b981,#06b6d4); }
      .sa-accent-info    { background:linear-gradient(90deg,#6366f1,#8b5cf6); }

      @media (max-width:480px) {
        .sa-backdrop { align-items:flex-end; padding:0; }
        .sa-card { max-width:100%; border-radius:var(--sa-radius-lg) var(--sa-radius-lg) 0 0; transform:translateY(40px); }
        .sa-backdrop.sa-in  .sa-card { transform:translateY(0); }
        .sa-backdrop.sa-out .sa-card { transform:translateY(30px); }
        .sa-header { padding:1.625rem 1.5rem 1.125rem; }
        .sa-body   { padding:1rem 1.5rem 1.25rem; }
        .sa-footer { padding:0 1.5rem max(1.5rem,env(safe-area-inset-bottom)); }
      }
      @media (prefers-reduced-motion:reduce) {
        .sa-backdrop,.sa-card,.sa-icon-svg,.sa-btn,.sa-accent { animation-duration:0.01ms !important; transition-duration:0.01ms !important; }
        .sa-icon-ring::before,.sa-icon-ring::after { animation:none; }
      }

      @keyframes sa-ring-pulse { 0%{opacity:0;transform:scale(0.8)} 50%{opacity:1} 100%{opacity:0;transform:scale(1.4)} }
      @keyframes sa-icon-pop   { from{transform:scale(0.5) rotate(-8deg);opacity:0} to{transform:scale(1) rotate(0);opacity:1} }
      @keyframes sa-slide-up   { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:translateY(0)} }
      @keyframes sa-accent-grow{ from{transform:scaleX(0)} to{transform:scaleX(1)} }
      @keyframes sa-spin       { to{transform:rotate(360deg)} }
    `;
        document.head.appendChild(style);
    }

    // ─── SVG icons ────────────────────────────────────────────────────
    const ICONS = {
        danger: `<svg class="sa-icon-svg sa-icon-danger"  viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>`,
        warning: `<svg class="sa-icon-svg sa-icon-warning" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>`,
        success: `<svg class="sa-icon-svg sa-icon-success" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>`,
        info: `<svg class="sa-icon-svg sa-icon-info"    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>`,
    };

    // ─── Shared state ──────────────────────────────────────────────────
    let _backdrop = null;
    let _confirmBtn = null;
    let _isOpen = false;

    function raf2(fn) {
        requestAnimationFrame(() => requestAnimationFrame(fn));
    }

    function focusableElements(root) {
        return Array.from(
            root.querySelectorAll(
                'button:not([disabled]),[href],input:not([disabled]),[tabindex]:not([tabindex="-1"])',
            ),
        );
    }

    // ─── showAlert ────────────────────────────────────────────────────
    function showAlert(opts) {
        if (!opts || typeof opts !== "object") return;
        if (_isOpen) return;

        const {
            type = "info",
            title = "Are you sure?",
            message = "",
            confirmText = "Confirm",
            cancelText = "Cancel",
            onConfirm = null,
            onCancel = null,
        } = opts;

        _isOpen = true;
        const t = ["danger", "warning", "success", "info"].includes(type)
            ? type
            : "info";

        const backdrop = document.createElement("div");
        backdrop.className = "sa-backdrop";
        backdrop.setAttribute("role", "alertdialog");
        backdrop.setAttribute("aria-modal", "true");
        backdrop.setAttribute("aria-labelledby", "sa-title");
        backdrop.setAttribute("aria-describedby", "sa-msg");

        backdrop.innerHTML = `
      <div class="sa-card" role="document">
        <div class="sa-accent sa-accent-${t}"></div>
        <div class="sa-header">
          <div class="sa-icon-ring sa-ring-${t}">${ICONS[t]}</div>
          <div id="sa-title" class="sa-title">${title}</div>
        </div>
        <div class="sa-body">
          <p id="sa-msg" class="sa-message">${message}</p>
        </div>
        <div class="sa-footer">
          <button class="sa-btn sa-btn-cancel" data-sa="cancel" type="button">
            <span class="sa-btn-label">${cancelText}</span>
          </button>
          <button class="sa-btn sa-btn-${t}" data-sa="confirm" type="button">
            <span class="sa-btn-spinner"></span>
            <span class="sa-btn-label">${confirmText}</span>
          </button>
        </div>
      </div>
    `;

        document.body.appendChild(backdrop);
        _backdrop = backdrop;
        _confirmBtn = backdrop.querySelector('[data-sa="confirm"]');
        document.body.style.overflow = "hidden";

        raf2(() => backdrop.classList.add("sa-in"));
        setTimeout(() => {
            _confirmBtn && _confirmBtn.focus();
        }, 320);

        function close(confirmed) {
            if (!_backdrop) return;
            backdrop.classList.remove("sa-in");
            backdrop.classList.add("sa-out");
            document.removeEventListener("keydown", onKey);
            setTimeout(() => {
                if (backdrop.parentNode)
                    backdrop.parentNode.removeChild(backdrop);
                document.body.style.overflow = "";
                _backdrop = null;
                _confirmBtn = null;
                _isOpen = false;
                if (confirmed && typeof onConfirm === "function") onConfirm();
                else if (!confirmed && typeof onCancel === "function")
                    onCancel();
            }, 220);
        }

        backdrop.addEventListener("click", function (e) {
            if (e.target === backdrop) close(false);
            const btn = e.target.closest("[data-sa]");
            if (!btn) return;
            if (btn.dataset.sa === "cancel") close(false);
            if (btn.dataset.sa === "confirm") {
                btn.classList.add("sa-loading");
                close(true);
            }
        });

        function onKey(e) {
            if (e.key === "Escape") {
                e.preventDefault();
                close(false);
                return;
            }
            if (e.key === "Enter") {
                const active = document.activeElement;
                if (active && active.dataset && active.dataset.sa === "confirm")
                    return;
                e.preventDefault();
                _confirmBtn && _confirmBtn.click();
                return;
            }
            if (e.key === "Tab") {
                const focusable = focusableElements(backdrop);
                if (!focusable.length) {
                    e.preventDefault();
                    return;
                }
                const first = focusable[0],
                    last = focusable[focusable.length - 1];
                if (e.shiftKey) {
                    if (document.activeElement === first) {
                        e.preventDefault();
                        last.focus();
                    }
                } else {
                    if (document.activeElement === last) {
                        e.preventDefault();
                        first.focus();
                    }
                }
            }
        }
        document.addEventListener("keydown", onKey);
    }

    // ─── Expose ONLY showAlert globally ───────────────────────────────
    // showToast is intentionally NOT set here.
    // app.js defines window.showToast(message, type) and runs AFTER this
    // import, so it owns that global. We must not overwrite it.
    global.showAlert = showAlert;
})(window);
