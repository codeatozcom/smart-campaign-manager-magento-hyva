/**
 * Codeatoz_Campaign — Promo Bar Live Preview
 * Loaded via layout XML on the campaign edit page.
 * Exposes a single global function: codeatozOpenPreview()
 */
require(['jquery', 'Magento_Ui/js/modal/modal'], function ($, modal) {
    'use strict';

    window.codeatozOpenPreview = function () {

        // ── helpers ────────────────────────────────────────────────────
        var getVal = function (scope) {
            // UI Component fields bind via data-scope — fall back to name attr
            var el = $('[data-scope="' + scope + '"] input, [data-scope="' + scope + '"] textarea, [data-scope="' + scope + '"] select').first();
            if (!el.length) {
                el = $('[name="' + scope + '"]').first();
            }
            return el.val() || '';
        };

        var getBool = function (scope) {
            var el = $('[data-scope="' + scope + '"] input[type="checkbox"]').first();
            if (!el.length) el = $('[name="' + scope + '"]').first();
            if (el.is(':checkbox')) return el.is(':checked');
            return parseInt(el.val() || '0', 10) === 1;
        };

        // ── read form values ───────────────────────────────────────────
        var text      = getVal('promo_bar_text')       || 'Your promo message will appear here';
        var bgColor   = getVal('promo_bar_bg_color')   || '#e63946';
        var textColor = getVal('promo_bar_text_color') || '#ffffff';
        var dealUrl   = getVal('promo_bar_deal_url');
        var dealLabel = getVal('promo_bar_deal_label') || 'Shop Now';
        var closable  = getBool('promo_bar_closable');
        var enabled   = getBool('status');

        var hexRe = /^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/;
        if (!hexRe.test(bgColor))   bgColor   = '#e63946';
        if (!hexRe.test(textColor)) textColor = '#ffffff';
        if (!dealLabel.trim())      dealLabel = 'Shop Now';

        // ── build bar HTML ─────────────────────────────────────────────
        var esc = function (s) { return $('<div>').text(s).html(); };

        var btnHtml = '';
        if (dealUrl) {
            btnHtml = '<a href="#" onclick="return false;" style="'
                + 'display:inline-block;padding:5px 16px;font-size:13px;font-weight:700;'
                + 'border-radius:3px;border:2px solid ' + textColor + ';'
                + 'color:' + bgColor + ';background-color:' + textColor + ';'
                + 'text-decoration:none;margin-left:4px;white-space:nowrap;">'
                + esc(dealLabel) + '</a>';
        }

        var closeHtml = closable
            ? '<span style="position:absolute;right:12px;top:50%;transform:translateY(-50%);'
              + 'font-size:20px;opacity:0.75;cursor:default;color:' + textColor + ';">&#215;</span>'
            : '';

        var warningHtml = !enabled
            ? '<p style="margin:10px 0 0;font-size:12px;color:#b30000;text-align:center;">'
              + '&#9888; Promo bar is currently <strong>disabled</strong>. Enable it to show on the storefront.</p>'
            : '';

        var barHtml = '<div style="'
            + 'background-color:' + bgColor + ';color:' + textColor + ';'
            + 'width:100%;position:relative;'
            + 'padding:10px ' + (closable ? '48px' : '16px') + ' 10px 16px;'
            + 'min-height:44px;box-sizing:border-box;'
            + 'display:flex;align-items:center;justify-content:center;gap:16px;flex-wrap:wrap;">'
            + '<span style="font-size:14px;font-weight:500;line-height:1.4;text-align:center;">'
            + esc(text) + '</span>'
            + btnHtml
            + closeHtml
            + '</div>';

        var content = '<div>'
            + '<p style="font-size:12px;color:#666;margin:0 0 10px 0;">'
            + 'Live preview based on current form values. Save the campaign to apply changes.</p>'
            + barHtml
            + warningHtml
            + '</div>';

        // ── open modal ─────────────────────────────────────────────────
        $('#codeatoz-preview-modal').remove();
        var $modal = $('<div id="codeatoz-preview-modal">' + content + '</div>');
        $('body').append($modal);

        modal({
            type: 'popup',
            responsive: true,
            innerScroll: false,
            title: $.mage.__('Promo Bar Preview'),
            buttons: [{
                text: $.mage.__('Close'),
                class: 'action-secondary action-dismiss',
                click: function () { this.closeModal(); }
            }]
        }, $modal);

        $modal.modal('openModal');
    };
});
