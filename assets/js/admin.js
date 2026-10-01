/* ============================================================
   ADMIN CMS — JavaScript + Client-Side Image Compression (Vercel Serverless Payload Limit Protection)
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

    /* Helper Kompresi Gambar berbasis HTML5 Canvas untuk Mencegah Vercel 413 Payload Error */
    function compressImageFile(file, maxWidth, maxHeight, quality) {
        return new Promise(function (resolve) {
            if (!file.type || !file.type.match(/^image\/(jpeg|jpg|png|webp)$/i)) {
                resolve(file);
                return;
            }
            var reader = new FileReader();
            reader.onload = function (e) {
                var img = new Image();
                img.onload = function () {
                    var w = img.width;
                    var h = img.height;
                    if (w <= maxWidth && h <= maxHeight && file.size <= 1.2 * 1024 * 1024) {
                        resolve(file);
                        return;
                    }
                    if (w > h) {
                        if (w > maxWidth) {
                            h = Math.round((h * maxWidth) / w);
                            w = maxWidth;
                        }
                    } else {
                        if (h > maxHeight) {
                            w = Math.round((w * maxHeight) / h);
                            h = maxHeight;
                        }
                    }
                    var canvas = document.createElement('canvas');
                    canvas.width = w;
                    canvas.height = h;
                    var ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, w, h);
                    canvas.toBlob(function (blob) {
                        if (blob && blob.size < file.size) {
                            var fileName = file.name.replace(/\.[^/.]+$/, "") + ".jpg";
                            var compressedFile = new File([blob], fileName, {
                                type: 'image/jpeg',
                                lastModified: Date.now()
                            });
                            resolve(compressedFile);
                        } else {
                            resolve(file);
                        }
                    }, 'image/jpeg', quality);
                };
                img.onerror = function () { resolve(file); };
                img.src = e.target.result;
            };
            reader.onerror = function () { resolve(file); };
            reader.readAsDataURL(file);
        });
    }

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

    /* Penanganan Form Upload — Kompresi otomatis & validasi batas payload Vercel (4.0 MB) */
    document.querySelectorAll('form[enctype="multipart/form-data"]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (form.dataset.submitting === 'true') {
                return;
            }

            var fileInputs = Array.from(form.querySelectorAll('input[type="file"]'));
            var hasFiles = fileInputs.some(function (inp) { return inp.files && inp.files.length > 0; });
            if (!hasFiles) {
                return;
            }

            var btn = form.querySelector('button[type="submit"]');
            var originalBtnText = btn ? btn.innerHTML : '';

            // Jika gambar berukuran besar, lakukan kompresi otomatis sebelum dikirim
            var promises = [];
            fileInputs.forEach(function (input) {
                var files = Array.from(input.files || []);
                if (!files.length) return;

                var compPromises = files.map(function (f) {
                    return compressImageFile(f, 1920, 1080, 0.82);
                });

                promises.push(Promise.all(compPromises).then(function (compressedFiles) {
                    if (window.DataTransfer) {
                        var dt = new DataTransfer();
                        compressedFiles.forEach(function (cf) { dt.items.add(cf); });
                        input.files = dt.files;
                    }
                }));
            });

            e.preventDefault();
            e.stopPropagation();

            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Memproses gambar…';
            }

            Promise.all(promises).then(function () {
                // Hitung total ukuran payload berkas
                var totalPayload = 0;
                fileInputs.forEach(function (input) {
                    Array.from(input.files || []).forEach(function (f) { totalPayload += f.size; });
                });

                // Jika melebihi 3.8 MB (Batas Vercel serverless 4.5MB), beri peringatan ramah pengguna
                if (totalPayload > 3.8 * 1024 * 1024) {
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = originalBtnText;
                    }
                    alert('Ukuran total file (' + (totalPayload / (1024 * 1024)).toFixed(1) + ' MB) melebihi batas upload Vercel (maksimal 4.0 MB per kali simpan).\n\nSilakan kurangi jumlah/ukuran file yang diunggah.');
                    return;
                }

                form.dataset.submitting = 'true';
                if (btn) {
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Mengunggah…';
                }
                form.submit();
            }).catch(function (err) {
                form.dataset.submitting = 'true';
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = originalBtnText;
                }
                form.submit();
            });
        });
    });

    /* Sisipkan video ke textarea konten via unggah AJAX */
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
