<?php
/**
 * Trafik Kazası Tutanak Analiz Sistemi - API Ayarları Paneli
 *
 * Birden fazla API anahtarı eklenebilir.
 * Sistem sırasıyla dener, başarısız olursa sonrakine geçer.
 */

require_once __DIR__ . '/config.php';
$csrfToken = $_SESSION['csrf_token'];

// Mevcut ayarları oku
$keysFile = __DIR__ . '/api_keys.json';
$settings = ['keys' => [], 'active_provider' => 'claude', 'fallback_enabled' => true, 'updated_at' => null];
if (file_exists($keysFile)) {
    $data = json_decode(file_get_contents($keysFile), true);
    if ($data) {
        $settings = array_merge($settings, $data);
    }
}

// Kayıtlı anahtarları sağlayıcıya göre grupla
$keysByProvider = ['claude' => [], 'gemini' => [], 'openai' => []];
foreach ($settings['keys'] as $key) {
    $provider = $key['provider'] ?? 'claude';
    if (isset($keysByProvider[$provider])) {
        $keysByProvider[$provider][] = $key;
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Ayarları - <?= htmlspecialchars(APP_NAME) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 min-h-screen">

    <!-- Header -->
    <header class="bg-slate-800/80 backdrop-blur-lg border-b border-slate-700/50 sticky top-0 z-50">
        <div class="max-w-5xl mx-auto px-4 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl flex items-center justify-center shadow-lg shadow-amber-500/20">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-white">API Anahtar Yonetimi</h1>
                    <p class="text-xs text-slate-400">Birden fazla anahtar ekleyin, sistem sirasiyla dener</p>
                </div>
            </div>
            <a href="index.php" class="flex items-center gap-2 text-sm text-slate-300 hover:text-white bg-slate-700/50 px-4 py-2 rounded-lg hover:bg-slate-700 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Ana Sayfa
            </a>
        </div>
    </header>

    <main class="max-w-5xl mx-auto px-4 py-8">
        <form id="settingsForm">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

            <!-- Bilgilendirme -->
            <div class="bg-amber-500/10 border border-amber-500/30 rounded-xl p-5 mb-8 backdrop-blur">
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-amber-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <div class="text-sm text-amber-200/90">
                        <p class="font-semibold text-amber-300 mb-2">Nasil Calisir?</p>
                        <ul class="space-y-1 list-disc list-inside">
                            <li>Her saglayici icin birden fazla API anahtari ekleyebilirsiniz</li>
                            <li>Sistem ilk anahtari dener, basarisiz olursa siradakine gecer</li>
                            <li>Tum anahtarlar basarisiz olursa diger saglayicilara gecer</li>
                            <li>Hata detaylari asagida anlik olarak gosterilir</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Genel Ayarlar -->
            <div class="bg-slate-800/60 backdrop-blur rounded-xl border border-slate-700/50 p-6 mb-6 shadow-xl">
                <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Genel Ayarlar
                </h2>
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Tercih Edilen Saglayici</label>
                        <select name="active_provider" class="w-full bg-slate-700/50 border border-slate-600 text-white rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                            <option value="claude" <?= $settings['active_provider'] === 'claude' ? 'selected' : '' ?>>Claude (Anthropic)</option>
                            <option value="gemini" <?= $settings['active_provider'] === 'gemini' ? 'selected' : '' ?>>Gemini (Google)</option>
                            <option value="openai" <?= $settings['active_provider'] === 'openai' ? 'selected' : '' ?>>OpenAI (GPT-4)</option>
                        </select>
                    </div>
                    <div class="flex items-center">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="fallback_enabled" value="1" class="sr-only peer" <?= $settings['fallback_enabled'] ? 'checked' : '' ?>>
                            <div class="w-11 h-6 bg-slate-600 peer-focus:ring-2 peer-focus:ring-amber-500 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
                            <span class="ml-3 text-sm font-medium text-slate-300">Otomatik Fallback (Basarisiz olursa sonraki anahtari dene)</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Claude API Keys -->
            <div class="bg-slate-800/60 backdrop-blur rounded-xl border border-slate-700/50 p-6 mb-6 shadow-xl">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-purple-500 to-violet-600 flex items-center justify-center text-xs font-bold text-white">C</span>
                        Claude (Anthropic)
                    </h2>
                    <button type="button" onclick="addKeyRow('claude')" class="text-sm text-purple-400 hover:text-purple-300 flex items-center gap-1 bg-purple-500/10 px-3 py-1.5 rounded-lg hover:bg-purple-500/20 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        Anahtar Ekle
                    </button>
                </div>
                <div id="claude-keys" class="space-y-3">
                    <?php foreach ($keysByProvider['claude'] as $i => $key): ?>
                    <div class="key-row flex items-center gap-3 bg-slate-700/30 rounded-lg p-3">
                        <span class="text-xs text-slate-500 font-mono w-6">#<?= $i + 1 ?></span>
                        <input type="text" name="keys[claude][]" value="<?= htmlspecialchars($key['key']) ?>"
                               class="flex-1 bg-slate-700/50 border border-slate-600 text-white text-sm rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 font-mono"
                               placeholder="sk-ant-api03-...">
                        <span class="key-status text-xs px-2 py-1 rounded-full <?= ($key['status'] ?? '') === 'valid' ? 'bg-green-500/20 text-green-400' : (($key['status'] ?? '') === 'invalid' ? 'bg-red-500/20 text-red-400' : 'bg-slate-600/50 text-slate-400') ?>">
                            <?= ($key['status'] ?? '') === 'valid' ? 'Gecerli' : (($key['status'] ?? '') === 'invalid' ? 'Gecersiz' : 'Test edilmedi') ?>
                        </span>
                        <button type="button" onclick="removeKeyRow(this)" class="text-red-400 hover:text-red-300 p-1.5 rounded-lg hover:bg-red-500/10 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <p class="text-xs text-slate-500 mt-2">Model: claude-sonnet-4-20250514 | Gorsel analiz destekli</p>
            </div>

            <!-- Gemini API Keys -->
            <div class="bg-slate-800/60 backdrop-blur rounded-xl border border-slate-700/50 p-6 mb-6 shadow-xl">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center text-xs font-bold text-white">G</span>
                        Gemini (Google)
                    </h2>
                    <button type="button" onclick="addKeyRow('gemini')" class="text-sm text-blue-400 hover:text-blue-300 flex items-center gap-1 bg-blue-500/10 px-3 py-1.5 rounded-lg hover:bg-blue-500/20 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        Anahtar Ekle
                    </button>
                </div>
                <div id="gemini-keys" class="space-y-3">
                    <?php foreach ($keysByProvider['gemini'] as $i => $key): ?>
                    <div class="key-row flex items-center gap-3 bg-slate-700/30 rounded-lg p-3">
                        <span class="text-xs text-slate-500 font-mono w-6">#<?= $i + 1 ?></span>
                        <input type="text" name="keys[gemini][]" value="<?= htmlspecialchars($key['key']) ?>"
                               class="flex-1 bg-slate-700/50 border border-slate-600 text-white text-sm rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono"
                               placeholder="AIza...">
                        <span class="key-status text-xs px-2 py-1 rounded-full <?= ($key['status'] ?? '') === 'valid' ? 'bg-green-500/20 text-green-400' : (($key['status'] ?? '') === 'invalid' ? 'bg-red-500/20 text-red-400' : 'bg-slate-600/50 text-slate-400') ?>">
                            <?= ($key['status'] ?? '') === 'valid' ? 'Gecerli' : (($key['status'] ?? '') === 'invalid' ? 'Gecersiz' : 'Test edilmedi') ?>
                        </span>
                        <button type="button" onclick="removeKeyRow(this)" class="text-red-400 hover:text-red-300 p-1.5 rounded-lg hover:bg-red-500/10 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <p class="text-xs text-slate-500 mt-2">Model: gemini-2.0-flash | Ucretsiz katman mevcut</p>
            </div>

            <!-- OpenAI API Keys -->
            <div class="bg-slate-800/60 backdrop-blur rounded-xl border border-slate-700/50 p-6 mb-6 shadow-xl">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center text-xs font-bold text-white">O</span>
                        OpenAI (GPT-4)
                    </h2>
                    <button type="button" onclick="addKeyRow('openai')" class="text-sm text-green-400 hover:text-green-300 flex items-center gap-1 bg-green-500/10 px-3 py-1.5 rounded-lg hover:bg-green-500/20 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        Anahtar Ekle
                    </button>
                </div>
                <div id="openai-keys" class="space-y-3">
                    <?php foreach ($keysByProvider['openai'] as $i => $key): ?>
                    <div class="key-row flex items-center gap-3 bg-slate-700/30 rounded-lg p-3">
                        <span class="text-xs text-slate-500 font-mono w-6">#<?= $i + 1 ?></span>
                        <input type="text" name="keys[openai][]" value="<?= htmlspecialchars($key['key']) ?>"
                               class="flex-1 bg-slate-700/50 border border-slate-600 text-white text-sm rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500 font-mono"
                               placeholder="sk-proj-...">
                        <span class="key-status text-xs px-2 py-1 rounded-full <?= ($key['status'] ?? '') === 'valid' ? 'bg-green-500/20 text-green-400' : (($key['status'] ?? '') === 'invalid' ? 'bg-red-500/20 text-red-400' : 'bg-slate-600/50 text-slate-400') ?>">
                            <?= ($key['status'] ?? '') === 'valid' ? 'Gecerli' : (($key['status'] ?? '') === 'invalid' ? 'Gecersiz' : 'Test edilmedi') ?>
                        </span>
                        <button type="button" onclick="removeKeyRow(this)" class="text-red-400 hover:text-red-300 p-1.5 rounded-lg hover:bg-red-500/10 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <p class="text-xs text-slate-500 mt-2">Model: gpt-4o | Gorsel analiz destekli</p>
            </div>

            <!-- Kaydet ve Test Butonlari -->
            <div class="flex flex-wrap gap-4 justify-center mb-8">
                <button type="submit" id="saveBtn" class="bg-gradient-to-r from-amber-500 to-orange-600 text-white font-semibold px-8 py-3 rounded-xl hover:from-amber-600 hover:to-orange-700 focus:ring-4 focus:ring-amber-500/30 transition-all shadow-lg shadow-amber-500/20 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                    Kaydet
                </button>
                <button type="button" id="testAllBtn" class="bg-slate-700 text-white font-semibold px-8 py-3 rounded-xl hover:bg-slate-600 focus:ring-4 focus:ring-slate-500/30 transition-all border border-slate-600 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Tum Anahtarlari Test Et
                </button>
            </div>
        </form>

        <!-- Test Sonuclari / Log -->
        <div id="logSection" class="hidden bg-slate-800/60 backdrop-blur rounded-xl border border-slate-700/50 p-6 mb-8 shadow-xl">
            <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Test Sonuclari
            </h2>
            <div id="logContent" class="space-y-2 max-h-80 overflow-y-auto font-mono text-sm"></div>
        </div>

        <!-- Son Guncelleme -->
        <?php if ($settings['updated_at']): ?>
        <div class="text-center text-xs text-slate-500 mb-8">
            Son guncelleme: <?= htmlspecialchars($settings['updated_at']) ?>
        </div>
        <?php endif; ?>

    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-700/50 mt-8 py-6">
        <div class="max-w-5xl mx-auto px-4 text-center text-sm text-slate-500">
            <p>&copy; <?= date('Y') ?> <?= htmlspecialchars(APP_NAME) ?> v<?= APP_VERSION ?></p>
        </div>
    </footer>

    <script>
    (function() {
        'use strict';

        const form = document.getElementById('settingsForm');
        const logSection = document.getElementById('logSection');
        const logContent = document.getElementById('logContent');
        let keyCounters = { claude: 0, gemini: 0, openai: 0 };

        // Mevcut anahtar sayilarini say
        document.querySelectorAll('#claude-keys .key-row').forEach(() => keyCounters.claude++);
        document.querySelectorAll('#gemini-keys .key-row').forEach(() => keyCounters.gemini++);
        document.querySelectorAll('#openai-keys .key-row').forEach(() => keyCounters.openai++);

        window.addKeyRow = function(provider) {
            const container = document.getElementById(provider + '-keys');
            keyCounters[provider]++;
            const num = keyCounters[provider];

            const colors = {
                claude: { ring: 'purple', placeholder: 'sk-ant-api03-...' },
                gemini: { ring: 'blue', placeholder: 'AIza...' },
                openai: { ring: 'green', placeholder: 'sk-proj-...' }
            };

            const row = document.createElement('div');
            row.className = 'key-row flex items-center gap-3 bg-slate-700/30 rounded-lg p-3 animate-fadeIn';
            row.innerHTML = `
                <span class="text-xs text-slate-500 font-mono w-6">#${num}</span>
                <input type="text" name="keys[${provider}][]" value=""
                       class="flex-1 bg-slate-700/50 border border-slate-600 text-white text-sm rounded-lg px-4 py-2 focus:ring-2 focus:ring-${colors[provider].ring}-500 focus:border-${colors[provider].ring}-500 font-mono"
                       placeholder="${colors[provider].placeholder}">
                <span class="key-status text-xs px-2 py-1 rounded-full bg-slate-600/50 text-slate-400">Yeni</span>
                <button type="button" onclick="removeKeyRow(this)" class="text-red-400 hover:text-red-300 p-1.5 rounded-lg hover:bg-red-500/10 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            `;
            container.appendChild(row);
            row.querySelector('input').focus();
        };

        window.removeKeyRow = function(btn) {
            const row = btn.closest('.key-row');
            row.style.opacity = '0';
            row.style.transform = 'translateX(20px)';
            row.style.transition = 'all 0.3s ease';
            setTimeout(() => row.remove(), 300);
        };

        function addLog(message, type) {
            logSection.classList.remove('hidden');
            const colors = {
                info: 'text-blue-400',
                success: 'text-green-400',
                error: 'text-red-400',
                warning: 'text-amber-400'
            };
            const icons = {
                info: 'i',
                success: '&#10003;',
                error: '&#10007;',
                warning: '!'
            };
            const entry = document.createElement('div');
            entry.className = `flex items-start gap-2 p-2 rounded ${type === 'error' ? 'bg-red-500/10' : type === 'success' ? 'bg-green-500/10' : 'bg-slate-700/30'}`;
            const time = new Date().toLocaleTimeString('tr-TR');
            entry.innerHTML = `
                <span class="${colors[type]} font-bold text-xs mt-0.5">[${icons[type]}]</span>
                <span class="text-slate-400 text-xs">${time}</span>
                <span class="${colors[type]} text-xs flex-1">${message}</span>
            `;
            logContent.appendChild(entry);
            logContent.scrollTop = logContent.scrollHeight;
        }

        // Kaydet
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            logContent.innerHTML = '';
            addLog('Ayarlar kaydediliyor...', 'info');

            const formData = new FormData(form);

            try {
                const response = await fetch('save_settings.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    addLog('Ayarlar basariyla kaydedildi!', 'success');
                    if (result.key_count) {
                        addLog(`Toplam ${result.key_count} anahtar kaydedildi.`, 'info');
                    }
                } else {
                    addLog('Kaydetme hatasi: ' + (result.error || 'Bilinmeyen hata'), 'error');
                }
            } catch (err) {
                addLog('Baglanti hatasi: ' + err.message, 'error');
            }
        });

        // Tum Anahtarlari Test Et
        document.getElementById('testAllBtn').addEventListener('click', async () => {
            logContent.innerHTML = '';
            addLog('Tum anahtarlar test ediliyor...', 'info');

            const formData = new FormData(form);
            formData.append('action', 'test_all');

            try {
                const response = await fetch('save_settings.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.test_results) {
                    for (const test of result.test_results) {
                        const maskedKey = test.key.substring(0, 12) + '...' + test.key.substring(test.key.length - 4);
                        if (test.valid) {
                            addLog(`[${test.provider.toUpperCase()}] ${maskedKey} - Gecerli`, 'success');
                        } else {
                            addLog(`[${test.provider.toUpperCase()}] ${maskedKey} - Gecersiz: ${test.error}`, 'error');
                        }

                        // Durum badge'ini guncelle
                        const container = document.getElementById(test.provider + '-keys');
                        if (container) {
                            const rows = container.querySelectorAll('.key-row');
                            rows.forEach(row => {
                                const input = row.querySelector('input');
                                if (input && input.value === test.key) {
                                    const badge = row.querySelector('.key-status');
                                    if (test.valid) {
                                        badge.className = 'key-status text-xs px-2 py-1 rounded-full bg-green-500/20 text-green-400';
                                        badge.textContent = 'Gecerli';
                                    } else {
                                        badge.className = 'key-status text-xs px-2 py-1 rounded-full bg-red-500/20 text-red-400';
                                        badge.textContent = 'Gecersiz';
                                    }
                                }
                            });
                        }
                    }

                    const validCount = result.test_results.filter(t => t.valid).length;
                    const totalCount = result.test_results.length;
                    if (validCount === totalCount) {
                        addLog(`Tum ${totalCount} anahtar gecerli!`, 'success');
                    } else {
                        addLog(`${validCount}/${totalCount} anahtar gecerli.`, validCount > 0 ? 'warning' : 'error');
                    }
                } else {
                    addLog('Test edilecek anahtar bulunamadi.', 'warning');
                }
            } catch (err) {
                addLog('Test hatasi: ' + err.message, 'error');
            }
        });
    })();
    </script>

    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fadeIn { animation: fadeIn 0.3s ease; }
    </style>
</body>
</html>
