{{-- Vendor Mobile Native App UX Stylesheet --}}
<style>
/* ==========================================================================
   1. FIXED DOCKED MOBILE BOTTOM NAVIGATION BAR
   ========================================================================== */
#vendor-mobile-bottom-nav,
.vendor-mobile-bottom-nav {
    position: fixed !important;
    bottom: 0 !important;
    left: 0 !important;
    right: 0 !important;
    width: 100% !important;
    z-index: 999 !important;
    background: rgba(255, 255, 255, 0.96) !important;
    -webkit-backdrop-filter: blur(16px) saturate(180%) !important;
    backdrop-filter: blur(16px) saturate(180%) !important;
    border-top: 1px solid rgba(226, 232, 240, 0.95) !important;
    box-shadow: 0 -4px 20px -2px rgba(15, 23, 42, 0.08), 0 -1px 3px rgba(15, 23, 42, 0.04) !important;
    padding-top: 6px !important;
    padding-bottom: max(6px, env(safe-area-inset-bottom, 0px)) !important;
    margin: 0 !important;
    box-sizing: border-box !important;
    display: block !important;
    pointer-events: auto !important;
}

/* Grid Container inside Bottom Nav */
.vendor-nav-container {
    display: grid !important;
    grid-template-columns: repeat(4, 1fr) !important;
    align-items: center !important;
    max-width: 480px !important;
    margin-left: auto !important;
    margin-right: auto !important;
    padding-left: 8px !important;
    padding-right: 8px !important;
    gap: 4px !important;
    box-sizing: border-box !important;
}

/* Individual Tab Item */
.vendor-nav-item {
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 6px 2px 4px 2px !important;
    border-radius: 12px !important;
    text-decoration: none !important;
    position: relative !important;
    color: #64748b !important;
    font-size: 11px !important;
    font-weight: 500 !important;
    line-height: 1.15 !important;
    min-height: 48px !important;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
    -webkit-tap-highlight-color: transparent !important;
    cursor: pointer !important;
    box-sizing: border-box !important;
    touch-action: manipulation !important;
}

.vendor-nav-item:hover,
.vendor-nav-item:focus {
    color: #1e3a8a !important;
}

.vendor-nav-item:active {
    transform: scale(0.96) !important;
}

/* Active Tab Highlight */
.vendor-nav-item.is-active {
    color: #1e3a8a !important;
    background: rgba(239, 246, 255, 0.95) !important;
    font-weight: 700 !important;
    box-shadow: 0 1px 2px rgba(30, 58, 138, 0.08) !important;
}

/* Icon Wrap & Sizing */
.vendor-nav-icon-wrap {
    position: relative !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 24px !important;
    height: 24px !important;
    margin-bottom: 2px !important;
}

.vendor-nav-icon {
    width: 22px !important;
    height: 22px !important;
    min-width: 22px !important;
    min-height: 22px !important;
    display: block !important;
    transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
}

.vendor-nav-item.is-active .vendor-nav-icon {
    transform: scale(1.1) !important;
}

/* Typography Label */
.vendor-nav-label {
    font-size: 11px !important;
    letter-spacing: -0.015em !important;
    margin-top: 1px !important;
    white-space: nowrap !important;
}

/* Active Dot Indicator */
.vendor-nav-active-dot {
    width: 5px !important;
    height: 5px !important;
    border-radius: 9999px !important;
    background-color: #1e3a8a !important;
    margin-top: 2px !important;
    display: block !important;
}

/* Badges for Count */
.vendor-nav-badge {
    position: absolute !important;
    top: -5px !important;
    right: -10px !important;
    min-width: 17px !important;
    height: 17px !important;
    padding: 0 4px !important;
    font-size: 9px !important;
    font-weight: 700 !important;
    line-height: 17px !important;
    text-align: center !important;
    color: #ffffff !important;
    background-color: #2563eb !important;
    border-radius: 9999px !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.18) !important;
    box-sizing: border-box !important;
}

.vendor-nav-badge-amber {
    background-color: #f59e0b !important;
}

/* Compliance Status Dot */
.vendor-compliance-indicator {
    position: absolute !important;
    top: -2px !important;
    right: -5px !important;
    width: 8px !important;
    height: 8px !important;
    border-radius: 9999px !important;
    border: 2px solid #ffffff !important;
    box-sizing: content-box !important;
}

.vendor-compliance-indicator.verified {
    background-color: #10b981 !important;
}

.vendor-compliance-indicator.pending {
    background-color: #f59e0b !important;
}

.vendor-compliance-indicator.rejected {
    background-color: #ef4444 !important;
}

/* ==========================================================================
   2. DARK MODE SUPPORT
   ========================================================================== */
.dark #vendor-mobile-bottom-nav,
.dark .vendor-mobile-bottom-nav {
    background: rgba(15, 23, 42, 0.96) !important;
    border-top: 1px solid rgba(51, 65, 85, 0.8) !important;
    box-shadow: 0 -4px 20px -2px rgba(0, 0, 0, 0.4) !important;
}

.dark .vendor-nav-item {
    color: #94a3b8 !important;
}

.dark .vendor-nav-item:hover,
.dark .vendor-nav-item:focus {
    color: #60a5fa !important;
}

.dark .vendor-nav-item.is-active {
    color: #60a5fa !important;
    background: rgba(30, 58, 138, 0.4) !important;
}

.dark .vendor-nav-active-dot {
    background-color: #60a5fa !important;
}

.dark .vendor-compliance-indicator {
    border-color: #0f172a !important;
}

/* ==========================================================================
   3. DESKTOP VIEWPORT BEHAVIOR (> 768px)
   ========================================================================== */
@media (min-width: 769px) {
    #vendor-mobile-bottom-nav,
    .vendor-mobile-bottom-nav {
        display: none !important;
    }
}

/* ==========================================================================
   4. MOBILE VIEWPORT BEHAVIOR (<= 768px)
   ========================================================================== */
@media (max-width: 768px) {
    /* Safe clearance so page content does not hide behind bottom bar */
    .fi-main-ctn,
    .fi-main,
    main.fi-main {
        padding-bottom: calc(5.5rem + env(safe-area-inset-bottom, 0px)) !important;
    }

    /* Sticky topbar with frosted glass effect */
    .fi-topbar {
        position: sticky !important;
        top: 0 !important;
        z-index: 35 !important;
        backdrop-filter: blur(12px) !important;
        -webkit-backdrop-filter: blur(12px) !important;
    }

    /* Clear space for Filament's sticky footer actions */
    .fi-form-actions-ctn.fi-sticky,
    .fi-form-actions.fi-sticky,
    .fi-sticky-actions {
        bottom: calc(4.75rem + env(safe-area-inset-bottom, 0px)) !important;
        z-index: 30 !important;
    }

    /* Stacked Table Card Layout for Mobile */
    .fi-ta-table-stacked-on-mobile .fi-ta-row,
    .fi-ta-table-stacked-on-mobile tbody tr {
        display: block !important;
        margin-bottom: 0.875rem !important;
        border-radius: 1rem !important;
        border: 1px solid rgba(226, 232, 240, 0.95) !important;
        background: #ffffff !important;
        padding: 1rem !important;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px -1px rgba(0, 0, 0, 0.05) !important;
    }

    .dark .fi-ta-table-stacked-on-mobile .fi-ta-row,
    .dark .fi-ta-table-stacked-on-mobile tbody tr {
        background: #111827 !important;
        border-color: rgba(55, 65, 81, 0.8) !important;
    }

    /* Full-width touch-friendly actions inside mobile cards */
    .fi-ta-table-stacked-on-mobile .fi-ta-actions-cell {
        display: block !important;
        width: 100% !important;
        margin-top: 0.75rem !important;
        padding-top: 0.75rem !important;
        border-top: 1px dashed rgba(226, 232, 240, 0.8) !important;
    }

    .dark .fi-ta-table-stacked-on-mobile .fi-ta-actions-cell {
        border-top-color: rgba(55, 65, 81, 0.7) !important;
    }

    .fi-ta-table-stacked-on-mobile .fi-ta-actions {
        display: flex !important;
        flex-direction: column !important;
        gap: 0.5rem !important;
        width: 100% !important;
    }

    .fi-ta-table-stacked-on-mobile .fi-ta-actions .fi-btn,
    .fi-ta-table-stacked-on-mobile .fi-ta-actions button,
    .fi-ta-table-stacked-on-mobile .fi-ta-actions a {
        width: 100% !important;
        justify-content: center !important;
        min-height: 2.75rem !important;
        padding-top: 0.625rem !important;
        padding-bottom: 0.625rem !important;
        border-radius: 0.75rem !important;
        font-weight: 600 !important;
        font-size: 0.875rem !important;
    }

    /* Touch target padding for form action buttons */
    .fi-form-actions .fi-btn,
    .fi-ac .fi-btn {
        min-height: 2.75rem !important;
        font-size: 0.9375rem !important;
    }

    /* Full-width touch-friendly schema actions on mobile */
    .fi-ac.fi-width-full {
        width: 100% !important;
        margin-top: 1rem !important;
    }

    .fi-ac.fi-width-full .fi-btn,
    .fi-ac.fi-width-full a {
        width: 100% !important;
        justify-content: center !important;
    }
}
</style>
