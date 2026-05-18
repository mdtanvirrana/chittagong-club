<style>
    .blue-depth-gradient { background: #ffffff; }

    .member-shell .blue-depth-gradient,
    .member-shell .blue-depth-gradient :where(p, span, h1, h2, h3, h4, h5, h6, small, label, li, dd, dt, strong, a) {
        color: #000000 !important;
        -webkit-text-fill-color: #000000 !important;
    }

    .member-shell .blue-depth-gradient :where([class~="text-primary"], [class~="text-primary/90"], [class~="text-primary/80"], [class~="text-primary/70"], [class~="text-primary/60"]) {
        color: var(--member-primary) !important;
        -webkit-text-fill-color: var(--member-primary) !important;
    }

    .member-shell .blue-depth-gradient :where(.gold-btn-gradient, [class~="bg-primary"]),
    .member-shell .blue-depth-gradient :where(.gold-btn-gradient, [class~="bg-primary"]) :where(p, span, h1, h2, h3, h4, h5, h6, small, strong) {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    .gold-text-gradient {
        background: var(--member-accent-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .gold-btn-gradient { background: var(--member-accent-gradient); }

    .login-input,
    .auth-select {
        background: rgba(255, 255, 255, 0.96);
        border: 1px solid rgba(197, 22, 46, 0.12);
        color: #111827;
        box-shadow: 0 18px 38px -34px rgba(185, 28, 28, 0.28);
    }

    .login-input::placeholder {
        color: #1f2937 !important;
        opacity: 1;
    }

    .member-shell .blue-depth-gradient .login-input::placeholder {
        color: #1f2937 !important;
        opacity: 1;
    }

    .login-icon {
        color: #1f2937;
        transition: color 180ms ease;
    }

    .group:focus-within .login-icon,
    .login-icon.is-active,
    .login-toggle:hover {
        color: var(--member-primary);
    }

    .login-toggle {
        color: #1f2937;
        transition: color 180ms ease;
    }

    .auth-floating-card {
        background: rgba(255, 255, 255, 0.72);
        border: 1px solid rgba(197, 22, 46, 0.10);
        box-shadow: 0 26px 48px -36px rgba(185, 28, 28, 0.30);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
    }

    .auth-step-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 9999px;
        background: rgba(197, 22, 46, 0.08);
        color: var(--member-primary);
        padding: 0.45rem 0.9rem;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.18em;
        text-transform: uppercase;
    }

    .auth-select:disabled {
        color: rgba(31, 41, 55, 0.92);
        cursor: not-allowed;
        opacity: 1;
    }

    .auth-prefix {
        color: rgba(31, 41, 55, 0.88);
        font-size: 0.95rem;
        font-weight: 700;
        letter-spacing: 0.04em;
    }

    [x-cloak] {
        display: none !important;
    }
</style>
