// ============================================================
// assets/js/admin.js
// Theme (dark/light) is handled by app.js, loaded before this
// file on every admin page — no duplication here.
// ============================================================
(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {

    // --- Modals -------------------------------------------
    document.querySelectorAll('[data-modal-open]').forEach(function (trigger) {
      trigger.addEventListener('click', function () {
        const overlay = document.getElementById(trigger.getAttribute('data-modal-open'));
        if (overlay) overlay.classList.add('open');
      });
    });

    document.querySelectorAll('.modal-overlay').forEach(function (overlay) {
      overlay.addEventListener('click', function (e) {
        if (e.target === overlay) overlay.classList.remove('open');
      });
      overlay.querySelectorAll('[data-modal-close]').forEach(function (b) {
        b.addEventListener('click', function () { overlay.classList.remove('open'); });
      });
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape')
        document.querySelectorAll('.modal-overlay.open').forEach(function (o) { o.classList.remove('open'); });
    });

    // --- Filling edit modals --------------------
    document.addEventListener('click', function (e) {
      const btn2 = e.target.closest('[data-edit]');
      if (!btn2) return;
      const data    = JSON.parse(btn2.getAttribute('data-edit'));
      const modalId = btn2.getAttribute('data-modal-open');
      if (!modalId) return;
      const modal = document.getElementById(modalId);
      if (!modal) return;
      Object.keys(data).forEach(function (key) {
        const input = modal.querySelector('[name="' + key + '"]');
        if (!input) return;
        if (input.type === 'checkbox') {
          input.checked = !!data[key] && data[key] !== '0';
        } else {
          input.value = data[key];
        }
      });
    });

    // --- Delete confirmation -----------------------
    document.addEventListener('click', function (e) {
      const b = e.target.closest('[data-confirm]');
      if (b && !confirm(b.getAttribute('data-confirm') || 'Confirm?')) e.preventDefault();
    });

    // --- Drag & drop (every [data-sortable] list) --
    document.querySelectorAll('[data-sortable]').forEach(function (list) {
      enableSortable(list);
    });

  }); // DOMContentLoaded

  // --------------------------------------------------------
  // Native drag & drop
  // --------------------------------------------------------
  function enableSortable(list) {
    let dragSrc = null;

    function getItems() { return list.querySelectorAll(':scope > .sortable-item'); }

    getItems().forEach(attachDrag);

    // Observer for dynamically added items
    new MutationObserver(function (mutations) {
      mutations.forEach(function (m) {
        m.addedNodes.forEach(function (n) {
          if (n.nodeType === 1 && n.classList.contains('sortable-item')) attachDrag(n);
        });
      });
    }).observe(list, { childList: true });

    function attachDrag(item) {
      item.setAttribute('draggable', 'true');

      item.addEventListener('dragstart', function (e) {
        dragSrc = item;
        item.classList.add('sortable-ghost');
        e.dataTransfer.effectAllowed = 'move';
      });

      item.addEventListener('dragend', function () {
        item.classList.remove('sortable-ghost');
        getItems().forEach(function (i) { i.classList.remove('drag-over'); });
        dragSrc = null;
        saveOrder(list);
      });

      item.addEventListener('dragover', function (e) {
        e.preventDefault();
        if (!dragSrc || dragSrc === item) return;
        const box = item.getBoundingClientRect();
        if (e.clientY < box.top + box.height / 2) {
          list.insertBefore(dragSrc, item);
        } else {
          list.insertBefore(dragSrc, item.nextSibling);
        }
      });

      item.addEventListener('drop', function (e) { e.preventDefault(); });
    }
  }

  function saveOrder(list) {
    const endpoint = list.getAttribute('data-sortable');
    const type     = list.getAttribute('data-type');
    const ids = [];
    list.querySelectorAll(':scope > .sortable-item').forEach(function (item) {
      ids.push(item.getAttribute('data-id'));
    });
    const csrf = (document.querySelector('meta[name=csrf]') || {}).content || '';
    fetch(endpoint, {
      method:  'POST',
      headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      body:    JSON.stringify({ type: type, ids: ids, csrf: csrf })
    }).catch(function () {});
  }

})();
