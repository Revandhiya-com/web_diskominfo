/* ============================================================
   ADMIN CMS — JavaScript
   ============================================================ */
(function () {
    'use strict';

    /* Toggle sidebar mobile */
    var toggle = document.getElementById('adminMenuToggle');
    var sidebar = document.querySelector('.admin-sidebar');
    if (toggle && sidebar) {
        toggle.addEventListener('click', function () { sidebar.classList.toggle('open'); });
    }

    /* Konfirmasi sebelum delete (data-confirm="teks") */
    document.addEventListener('click', function (e) {
        var el = e.target.closest('[data-confirm]');
        if (!el) return;
        if (!window.confirm(el.getAttribute('data-confirm'))) {
            e.preventDefault();
            e.stopPropagation();
        }
    });

    /* Preview multi-file upload foto (input type=file multiple) */
    document.querySelectorAll('[data-upload-preview]').forEach(function (input) {
        var wrap = document.querySelector(input.getAttribute('data-upload-preview'));
        if (!wrap) return;
        input.addEventListener('change', function () {
            wrap.innerHTML = '';
            var files = Array.prototype.slice.call(input.files || []);
            if (files.length === 0) return;
            files.forEach(function (file) {
                if (!file.type.match(/^image\/(jpeg|png|webp)$/)) return;
                var reader = new FileReader();
                var item = document.createElement('div');
                item.className = 'p-item';
                var img = document.createElement('img');
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.setAttribute('aria-label', 'Hapus pratinjau');
                btn.innerHTML = '<i class="bi bi-x"></i>';
                btn.addEventListener('click', function () { item.remove(); });
                reader.onload = function (ev) { img.src = ev.target.result; };
                reader.readAsDataURL(file);
                item.appendChild(img);
                item.appendChild(btn);
                wrap.appendChild(item);
            });
        });
    });

    /* Pratinjau sampul album (input file tunggal) */
    document.querySelectorAll('[data-cover-preview]').forEach(function (input) {
        var target = document.querySelector(input.getAttribute('data-cover-preview'));
        if (!target) return;
        input.addEventListener('change', function () {
            var file = input.files && input.files[0];
            if (!file) return;
            var reader = new FileReader();
            reader.onload = function (ev) { target.src = ev.target.result; };
            reader.readAsDataURL(file);
        });
    });

    /* Konfirmasi form yang menandai "hanya klik sekali" (upload besar) */
    document.querySelectorAll('form[data-uploading]').forEach(function (form) {
        form.addEventListener('submit', function () {
            var btn = form.querySelector('button[type="submit"]');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Mengunggah…';
            }
        });
    });

    /* Sisipkan video ke textarea konten via unggah AJAX (admin/upload-video.php) */
    document.querySelectorAll('[data-video-insert]').forEach(function (btn) {
        var container = btn.parentElement;
        var input = container ? container.querySelector('[data-video-input]') : null;
        var form = btn.closest('form');
        var target = form ? form.querySelector('[data-video-target]') : null;
        if (!input || !target || !form) return;

        function insertAtCursor(textarea, text) {
            var start = typeof textarea.selectionStart === 'number' ? textarea.selectionStart : textarea.value.length;
            var end = typeof textarea.selectionEnd === 'number' ? textarea.selectionEnd : start;
            textarea.value = textarea.value.slice(0, start) + text + textarea.value.slice(end);
            var pos = start + text.length;
            try { textarea.setSelectionRange(pos, pos); } catch (err) { /* noop */ }
            textarea.focus();
        }

        input.addEventListener('change', function () {
            var file = input.files && input.files[0];
            if (!file) return;

            var csrfInput = form.querySelector('input[name="csrf_token"]');
            var original = btn.innerHTML;
            btn.disabled = true;
            input.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Unggah…';

            var fd = new FormData();
            fd.append('file', file);
            fd.append('csrf_token', csrfInput ? csrfInput.value : '');

            fetch('upload-video.php', { method: 'POST', body: fd, credentials: 'same-origin' })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (!data || !data.ok || !data.url) {
                        throw new Error((data && data.error) || 'Gagal mengunggah video.');
                    }
                    insertAtCursor(
                        target,
                        '<figure><video controls preload="metadata" playsinline src="' + data.url + '"></video></figure>\n'
                    );
                    window.alert('Video berhasil diunggah dan disisipkan ke isi.');
                })
                .catch(function (err) {
                    window.alert(err.message || 'Gagal mengunggah video.');
                })
                .finally(function () {
                    btn.disabled = false;
                    input.disabled = false;
                    btn.innerHTML = original;
                    input.value = '';
                });
        });
    });
})();
