/*
 * Kompresi/resize gambar di browser sebelum diunggah (mis. foto langsung
 * dari kamera HP yang beberapa MB) supaya upload lebih cepat & database
 * (tabel "photos" menyimpan foto sebagai BLOB) tidak membengkak. Dipakai
 * bersama oleh admin.js (semua upload lewat fetch ke upload-photo.php)
 * dan edit-portfolio.php (form upload tradisional).
 */
(function (global) {
  "use strict";

  function compressImageFile(file, maxDim, quality) {
    maxDim = maxDim || 1600;
    quality = quality || 0.82;

    // Animasi GIF rusak kalau digambar ulang lewat canvas (cuma kebawa
    // frame pertama) -- lewati saja, kirim file asli apa adanya.
    if (!file || file.type === "image/gif" || !window.HTMLCanvasElement) {
      return Promise.resolve(file);
    }

    return new Promise(function (resolve) {
      var url = URL.createObjectURL(file);
      var img = new Image();
      img.onload = function () {
        URL.revokeObjectURL(url);
        var scale = Math.min(1, maxDim / Math.max(img.width, img.height));
        var targetW = Math.round(img.width * scale);
        var targetH = Math.round(img.height * scale);
        var canvas = document.createElement("canvas");
        canvas.width = targetW;
        canvas.height = targetH;
        var ctx = canvas.getContext("2d");
        ctx.drawImage(img, 0, 0, targetW, targetH);
        // PNG dipertahankan sebagai PNG (jaga transparansi kalau ada),
        // format lain (JPEG dari kamera HP, biasanya paling besar) di-
        // konversi ke JPEG supaya kompresinya efektif.
        var outType = file.type === "image/png" ? "image/png" : "image/jpeg";
        canvas.toBlob(function (blob) {
          if (!blob || blob.size >= file.size) {
            resolve(file); // hasil kompresi tidak lebih kecil -- pakai file asli
            return;
          }
          var newName = file.name.replace(/\.\w+$/, outType === "image/png" ? ".png" : ".jpg");
          resolve(new File([blob], newName, { type: outType, lastModified: Date.now() }));
        }, outType, quality);
      };
      img.onerror = function () {
        URL.revokeObjectURL(url);
        resolve(file); // gagal decode -- fallback ke file asli, jangan blokir upload
      };
      img.src = url;
    });
  }

  global.compressImageFile = compressImageFile;
})(window);
