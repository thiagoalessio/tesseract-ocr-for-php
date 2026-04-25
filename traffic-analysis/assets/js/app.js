/**
 * Trafik Kazası Tutanak Analiz Sistemi - Frontend Uygulaması
 */

(function () {
    'use strict';

    // ============================================================
    // DOM Elemanları
    // ============================================================
    const form = document.getElementById('analysisForm');
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('fileInput');
    const previewContainer = document.getElementById('previewContainer');
    const analyzeBtn = document.getElementById('analyzeBtn');
    const progressSection = document.getElementById('progressSection');
    const progressText = document.getElementById('progressText');
    const progressBar = document.getElementById('progressBar');
    const resultSection = document.getElementById('resultSection');
    const resultContent = document.getElementById('resultContent');
    const errorSection = document.getElementById('errorSection');
    const errorMessage = document.getElementById('errorMessage');
    const printBtn = document.getElementById('printBtn');
    const newAnalysisBtn = document.getElementById('newAnalysisBtn');
    const retryBtn = document.getElementById('retryBtn');

    // ============================================================
    // Durum
    // ============================================================
    let selectedFiles = [];
    const MAX_FILES = 10;
    const MAX_FILE_SIZE = 20 * 1024 * 1024; // 20MB
    const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    // ============================================================
    // Dosya Yükleme ve Önizleme
    // ============================================================

    // Drop zone tıklama
    dropZone.addEventListener('click', () => fileInput.click());

    // Dosya seçildiğinde
    fileInput.addEventListener('change', (e) => {
        addFiles(Array.from(e.target.files));
        fileInput.value = '';
    });

    // Sürükle-bırak
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('drop-active');
    });

    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('drop-active');
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('drop-active');
        addFiles(Array.from(e.dataTransfer.files));
    });

    /**
     * Dosyaları listeye ekler ve doğrular.
     */
    function addFiles(files) {
        for (const file of files) {
            if (selectedFiles.length >= MAX_FILES) {
                showError('En fazla ' + MAX_FILES + ' dosya yükleyebilirsiniz.');
                break;
            }

            if (!ALLOWED_TYPES.includes(file.type)) {
                showError(file.name + ': Desteklenmeyen dosya türü. (JPG, PNG, WEBP)');
                continue;
            }

            if (file.size > MAX_FILE_SIZE) {
                showError(file.name + ': Dosya boyutu 20MB limitini aşıyor.');
                continue;
            }

            if (file.size === 0) {
                showError(file.name + ': Dosya boş.');
                continue;
            }

            selectedFiles.push(file);
        }

        renderPreviews();
        updateButtonState();
    }

    /**
     * Önizleme kartlarını oluşturur.
     */
    function renderPreviews() {
        previewContainer.innerHTML = '';

        if (selectedFiles.length === 0) {
            previewContainer.classList.add('hidden');
            return;
        }

        previewContainer.classList.remove('hidden');

        selectedFiles.forEach((file, index) => {
            const card = document.createElement('div');
            card.className = 'preview-card';

            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.alt = file.name;
            img.onload = () => URL.revokeObjectURL(img.src);

            const removeBtn = document.createElement('button');
            removeBtn.className = 'remove-btn';
            removeBtn.type = 'button';
            removeBtn.innerHTML = '&times;';
            removeBtn.title = 'Kaldır';
            removeBtn.addEventListener('click', () => {
                selectedFiles.splice(index, 1);
                renderPreviews();
                updateButtonState();
            });

            const fileName = document.createElement('div');
            fileName.className = 'file-name';
            fileName.textContent = file.name;

            card.appendChild(img);
            card.appendChild(removeBtn);
            card.appendChild(fileName);
            previewContainer.appendChild(card);
        });
    }

    /**
     * Buton durumunu günceller.
     */
    function updateButtonState() {
        analyzeBtn.disabled = selectedFiles.length === 0;
    }

    // ============================================================
    // Analiz İşlemi
    // ============================================================

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        if (selectedFiles.length === 0) return;

        hideError();
        hideResult();
        showProgress('Fotoğraflar yükleniyor...', 10);

        const csrfToken = form.querySelector('[name="csrf_token"]').value;

        try {
            // 1. Dosyaları yükle
            const uploadData = new FormData();
            uploadData.append('csrf_token', csrfToken);
            selectedFiles.forEach(file => uploadData.append('images[]', file));

            const uploadResponse = await fetch('upload.php', {
                method: 'POST',
                body: uploadData,
            });

            const uploadResult = await uploadResponse.json();

            if (!uploadResult.success) {
                throw new Error(uploadResult.error || 'Dosya yükleme başarısız.');
            }

            showProgress('Yapay zeka analiz ediyor... Bu işlem 30-60 saniye sürebilir.', 40);

            // 2. Analiz isteği gönder
            const analyzeData = new FormData();
            analyzeData.append('csrf_token', csrfToken);
            analyzeData.append('session_id', uploadResult.session_id);
            analyzeData.append('taraf_a', document.getElementById('tarafA').value);
            analyzeData.append('taraf_b', document.getElementById('tarafB').value);

            const analyzeResponse = await fetch('api_handler.php', {
                method: 'POST',
                body: analyzeData,
            });

            const analyzeResult = await analyzeResponse.json();

            if (!analyzeResult.success) {
                throw new Error(analyzeResult.error || 'Analiz başarısız.');
            }

            showProgress('Rapor hazırlanıyor...', 90);

            // 3. Sonucu göster
            setTimeout(() => {
                hideProgress();
                showResult(analyzeResult.analysis);
            }, 500);

        } catch (error) {
            hideProgress();
            showError(error.message || 'Beklenmeyen bir hata oluştu.');
        }
    });

    // ============================================================
    // UI Yardımcıları
    // ============================================================

    function showProgress(text, percent) {
        progressSection.classList.remove('hidden');
        progressText.textContent = text;
        progressBar.style.width = percent + '%';
        analyzeBtn.disabled = true;
        form.querySelector('fieldset')?.setAttribute('disabled', '');
    }

    function hideProgress() {
        progressSection.classList.add('hidden');
        progressBar.style.width = '0%';
        updateButtonState();
    }

    function showResult(analysisText) {
        resultSection.classList.remove('hidden');
        resultContent.innerHTML = formatAnalysis(analysisText);
        resultSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function hideResult() {
        resultSection.classList.add('hidden');
        resultContent.innerHTML = '';
    }

    function showError(message) {
        errorSection.classList.remove('hidden');
        errorMessage.textContent = message;
        errorSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function hideError() {
        errorSection.classList.add('hidden');
    }

    /**
     * AI yanıtını HTML formatına dönüştürür (basit markdown desteği).
     */
    function formatAnalysis(text) {
        if (!text) return '<p>Analiz sonucu alınamadı.</p>';

        let html = text
            // Başlıklar
            .replace(/^### (.+)$/gm, '<h3>$1</h3>')
            .replace(/^## (.+)$/gm, '<h2>$1</h2>')
            .replace(/^# (.+)$/gm, '<h1>$1</h1>')
            // Numaralı başlıklar (1. TARAF BEYANLARI gibi)
            .replace(/^(\d+)\.\s+([A-ZÇĞİÖŞÜ\s]+)$/gm, '<h2>$1. $2</h2>')
            // Kalın metin
            .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
            // İtalik
            .replace(/\*(.+?)\*/g, '<em>$1</em>')
            // Liste öğeleri
            .replace(/^[-•]\s+(.+)$/gm, '<li>$1</li>')
            // Paragraflar
            .replace(/\n\n/g, '</p><p>')
            // Satır sonları
            .replace(/\n/g, '<br>');

        // li etiketlerini ul içine al
        html = html.replace(/(<li>.*?<\/li>(\s*<br>)*)+/g, (match) => {
            return '<ul>' + match.replace(/<br>/g, '') + '</ul>';
        });

        return '<p>' + html + '</p>';
    }

    // ============================================================
    // Buton Olayları
    // ============================================================

    // Yazdır
    printBtn.addEventListener('click', () => window.print());

    // Yeni analiz
    newAnalysisBtn.addEventListener('click', () => {
        selectedFiles = [];
        renderPreviews();
        hideResult();
        hideError();
        updateButtonState();
        document.getElementById('tarafA').value = '';
        document.getElementById('tarafB').value = '';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // Tekrar dene
    retryBtn.addEventListener('click', () => {
        hideError();
        if (selectedFiles.length > 0) {
            form.dispatchEvent(new Event('submit'));
        }
    });

})();
