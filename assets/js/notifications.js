/**
 * notifications.js
 * Handles the notification bell dropdown, mark-as-read, and mark-all-read.
 * Vanilla JS only. No external libraries.
 */
(function () {
  'use strict';

  const BASE_URL = window._unifindBaseUrl || '';
  const API_URL  = BASE_URL + '/ajax/notifications_api.php';

  // ── DOM refs ────────────────────────────────────────────────────────────────
  const bellBtn       = document.getElementById('notif-bell-btn');
  const dropdown      = document.getElementById('notif-dropdown');
  const badge         = document.getElementById('notif-badge');
  const notifList     = document.getElementById('notif-list');
  const markAllBtn    = document.getElementById('notif-mark-all-btn');

  if (!bellBtn || !dropdown) return; // guard – not logged in

  // ── Toggle dropdown ─────────────────────────────────────────────────────────
  bellBtn.addEventListener('click', function (e) {
    e.stopPropagation();
    const isOpen = dropdown.classList.contains('notif-dropdown-open');
    if (isOpen) {
      closeDropdown();
    } else {
      openDropdown();
    }
  });

  function openDropdown() {
    dropdown.classList.add('notif-dropdown-open');
    bellBtn.setAttribute('aria-expanded', 'true');
  }

  function closeDropdown() {
    dropdown.classList.remove('notif-dropdown-open');
    bellBtn.setAttribute('aria-expanded', 'false');
  }

  // Close when clicking outside
  document.addEventListener('click', function (e) {
    const wrapper = document.getElementById('notif-bell-wrapper');
    if (wrapper && !wrapper.contains(e.target)) {
      closeDropdown();
    }
  });

  // Close on Escape key
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeDropdown();
  });

  // ── Mark single notification as read ────────────────────────────────────────
  if (notifList) {
    notifList.addEventListener('click', function (e) {
      const item = e.target.closest('.notif-item.notif-unread');
      if (!item) return;

      const id = item.dataset.id;
      if (!id) return;

      postAction({ action: 'mark_read', notification_id: id })
        .then(function (data) {
          if (data.success) {
            // Visual update
            item.classList.remove('notif-unread');
            item.classList.add('notif-read');
            const dot = item.querySelector('.notif-dot');
            if (dot) {
              dot.style.opacity = '0';
              dot.style.transform = 'scale(0)';
              setTimeout(() => dot.remove(), 300);
            }
            updateBadge(data.unread_count);
          }
        })
        .catch(function () {/* silently ignore */});
    });
  }

  // ── Mark all as read ────────────────────────────────────────────────────────
  if (markAllBtn) {
    markAllBtn.addEventListener('click', function () {
      postAction({ action: 'mark_all_read' })
        .then(function (data) {
          if (data.success) {
            // Visual update – flip all unread items
            const unreadItems = notifList.querySelectorAll('.notif-item.notif-unread');
            unreadItems.forEach(function (item) {
              item.classList.remove('notif-unread');
              item.classList.add('notif-read');
              const dot = item.querySelector('.notif-dot');
              if (dot) dot.remove();
            });
            updateBadge(0);
            // Hide the "mark all" button itself
            markAllBtn.style.opacity = '0';
            setTimeout(() => markAllBtn.remove(), 300);
          }
        })
        .catch(function () {/* silently ignore */});
    });
  }

  // ── Helpers ─────────────────────────────────────────────────────────────────
  function updateBadge(count) {
    if (!badge) return;
    if (count > 0) {
      badge.textContent = count > 99 ? '99+' : count;
      badge.classList.remove('notif-badge-hidden');
    } else {
      badge.textContent = '0';
      badge.classList.add('notif-badge-hidden');
    }
  }

  function postAction(params) {
    const body = new URLSearchParams(params);
    return fetch(API_URL, {
      method:  'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body:    body.toString(),
    }).then(function (res) {
      if (!res.ok) throw new Error('Network error');
      return res.json();
    });
  }
})();
