// Fungsi untuk mengubah HTML ke format spans
function convertHTMLToSpans(html) {
    return html.replace(/(<[^>]+>)|([^<]+)/g, function(match, tag, text) {
      if (tag) {
        // Proses tag
        if (tag.startsWith('</')) {
          // Tag penutup
          const content = tag.slice(2, -1);
          return `<span class="tag">&lt;/${content}&gt;</span>`;
        } else {
        // Opening tag
        const isSelfClosing = tag.endsWith('/>');
        const tagContent = tag.slice(1, isSelfClosing ? -2 : -1);
        const [tagName, ...attrs] = tagContent.split(/\s+(?=(?:[^"]*"[^"]*")*[^"]*$)/);
  
          let result = `<span class="tag">&lt;${tagName}</span>`;
  
          for (const attr of attrs) {
            if (attr) {
              const [name, value] = attr.split('=');
              result += ` <span class="attribute">${name}</span>=<span class="value">${value}</span>`;
            }
          }
  
          result += `<span class="tag">&gt;</span>`;
          return result;
        }
      } else if (text) {
        // Proses teks
        if (/^\s+$/.test(text)) {
          // Whitespace (spasi, tab, newline)
          return text.replace(/\n/g, '<br>')
                     .replace(/ /g, '&nbsp;')
                     .replace(/\t/g, '&nbsp;');
        }
        return text; // Teks biasa
      }
      return match;
    });
  }

  function closeCode() {
    const codeContainer = document.getElementById("hasil_konversi");
    if (codeContainer.style.display === "none") {
      codeContainer.style.display = "block"; // Tampilkan ulang
    } else {
      codeContainer.style.display = "none"; // Sembunyikan
    }
  }
  
// Fungsi untuk menghapus spasi indentasi minimum di awal setiap baris
function normalizeIndentation(html) {
  // Pecah HTML menjadi array baris
  const lines = html.split('\n');
  
  // Cari jumlah spasi terkecil di awal baris yang tidak kosong
  const minIndent = Math.min(...lines
    .filter(line => line.trim().length > 0) // Abaikan baris kosong
    .map(line => line.match(/^\s*/)[0].length)); // Hitung spasi di awal
  
  // Kurangi spasi minimum dari setiap baris
  return lines.map(line => line.slice(minIndent)).join('\n');
}
// Fungsi untuk memuat konten dari header.html ke dalam elemen dengan id "header"
function loadHeader() {
  fetch("header.html")
    .then((response) => response.text())
    .then((data) => {
      document.getElementById("header").innerHTML = data;
    })
    .catch((error) => {
      console.error("Gagal memuat header:", error);
    });
}

// Panggil fungsi saat halaman selesai dimuat
window.addEventListener('DOMContentLoaded', loadHeader);


// Fungsi untuk menjalankan konversi setelah halaman selesai dimuat
function init() {
  // Ambil konten HTML dari div dengan id "code_asli"
  let originalCode = document.getElementById('code_asli').innerHTML;
  
  // Normalisasi indentasi
  originalCode = normalizeIndentation(originalCode);
  
  // Konversi kode HTML ke format spans
  const convertedCode = convertHTMLToSpans(originalCode);
  
  // Tampilkan hasil konversi di div dengan id "hasil_konversi"
  document.getElementById('hasil_konversi').innerHTML = convertedCode;
}
  
  // Jalankan fungsi init setelah halaman selesai dimuat
  window.addEventListener('DOMContentLoaded', init);