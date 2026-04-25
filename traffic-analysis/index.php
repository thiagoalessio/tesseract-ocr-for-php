<?php
require_once __DIR__ . '/config.php';
$csrfToken = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(APP_NAME) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] }
                }
            }
        }
    </script>
</head>
<body class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 min-h-screen font-sans">

    <!-- Dekoratif arka plan -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-amber-500/5 rounded-full blur-3xl"></div>
        <div class="absolute top-1/2 -left-40 w-96 h-96 bg-blue-500/5 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 right-1/3 w-80 h-80 bg-orange-500/5 rounded-full blur-3xl"></div>
    </div>

    <!-- Header -->
    <header class="relative bg-slate-800/80 backdrop-blur-lg border-b border-slate-700/50 sticky top-0 z-50">
        <div class="max-w-5xl mx-auto px-4 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="relative">
                    <div class="w-11 h-11 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl flex items-center justify-center shadow-lg shadow-amber-500/25">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="absolute -top-1 -right-1 w-3 h-3 bg-green-500 rounded-full border-2 border-slate-800 animate-pulse"></div>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-white tracking-tight">Trafik Kazasi Tutanak Analiz</h1>
                    <p class="text-xs text-slate-400">Yapay Zeka Destekli Kusur Analizi</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="settings.php" class="flex items-center gap-2 text-sm text-slate-300 hover:text-amber-400 bg-slate-700/50 px-4 py-2 rounded-lg hover:bg-slate-700 transition-all border border-slate-600/50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    API Ayarlari
                </a>
            </div>
        </div>
    </header>

    <main class="relative max-w-5xl mx-auto px-4 py-8">

        <!-- Bilgilendirme -->
        <div class="bg-amber-500/10 border border-amber-500/20 rounded-xl p-5 mb-8 backdrop-blur">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-amber-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="text-sm text-amber-200/90">
                    <p class="font-semibold text-amber-300 mb-2">Nasil Kullanilir?</p>
                    <ol class="space-y-1.5">
                        <li class="flex items-start gap-2">
                            <span class="w-5 h-5 rounded-full bg-amber-500/20 text-amber-400 text-xs flex items-center justify-center flex-shrink-0 mt-0.5 font-semibold">1</span>
                            <span>Kaza Tespit Tutanagi fotografini yukleyin</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="w-5 h-5 rounded-full bg-amber-500/20 text-amber-400 text-xs flex items-center justify-center flex-shrink-0 mt-0.5 font-semibold">2</span>
                            <span>Varsa kaza yeri ve arac hasar fotograflarini ekleyin</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="w-5 h-5 rounded-full bg-amber-500/20 text-amber-400 text-xs flex items-center justify-center flex-shrink-0 mt-0.5 font-semibold">3</span>
                            <span>Taraf beyanlarini yazin (istege bagli)</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="w-5 h-5 rounded-full bg-amber-500/20 text-amber-400 text-xs flex items-center justify-center flex-shrink-0 mt-0.5 font-semibold">4</span>
                            <span>"Analiz Et" butonuna basin</span>
                        </li>
                    </ol>
                    <p class="mt-3 text-amber-400/80 text-xs border-t border-amber-500/20 pt-2">Bu sistem bir on degerlendirme aracidir. Kesin kusur tespiti yetkili merciler tarafindan yapilir.</p>
                </div>
            </div>
        </div>

        <!-- Ana Form -->
        <form id="analysisForm" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

            <!-- Fotograf Yukleme -->
            <div class="bg-slate-800/60 backdrop-blur rounded-xl border border-slate-700/50 p-6 shadow-xl">
                <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Fotograf Yukleme
                </h2>

                <!-- Drop Zone -->
                <div id="dropZone"
                     class="border-2 border-dashed border-slate-600 rounded-xl p-8 text-center cursor-pointer
                            hover:border-amber-500/50 hover:bg-amber-500/5 transition-all duration-300 group">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-slate-700/50 flex items-center justify-center group-hover:bg-amber-500/10 transition-all">
                        <svg class="w-8 h-8 text-slate-500 group-hover:text-amber-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <p class="text-slate-300 font-medium group-hover:text-white transition-colors">Fotograflari buraya surukleyin veya tiklayin</p>
                    <p class="text-slate-500 text-sm mt-1">JPG, PNG, WEBP - Maks. 20MB, en fazla <?= MAX_FILES ?> dosya</p>
                    <input type="file" id="fileInput" multiple accept=".jpg,.jpeg,.png,.webp" class="hidden">
                </div>

                <!-- Yuklenen Dosya Onizlemeleri -->
                <div id="previewContainer" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 mt-4 hidden"></div>
            </div>

            <!-- Taraf Beyanlari -->
            <div class="bg-slate-800/60 backdrop-blur rounded-xl border border-slate-700/50 p-6 shadow-xl">
                <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                    Taraf Beyanlari
                    <span class="text-xs font-normal text-slate-500 ml-1">(Istege bagli)</span>
                </h2>
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label for="tarafA" class="block text-sm font-medium text-slate-300 mb-2">
                            <span class="inline-flex items-center gap-1.5">
                                <span class="w-5 h-5 rounded bg-blue-500/20 text-blue-400 text-xs flex items-center justify-center font-bold">A</span>
                                Taraf A (1. Surucu)
                            </span>
                        </label>
                        <textarea id="tarafA" name="taraf_a" rows="4"
                                  class="w-full bg-slate-700/50 border border-slate-600 text-white rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 resize-none placeholder-slate-500 transition-all"
                                  placeholder="1. surucunun beyanini yazin..."></textarea>
                    </div>
                    <div>
                        <label for="tarafB" class="block text-sm font-medium text-slate-300 mb-2">
                            <span class="inline-flex items-center gap-1.5">
                                <span class="w-5 h-5 rounded bg-orange-500/20 text-orange-400 text-xs flex items-center justify-center font-bold">B</span>
                                Taraf B (2. Surucu)
                            </span>
                        </label>
                        <textarea id="tarafB" name="taraf_b" rows="4"
                                  class="w-full bg-slate-700/50 border border-slate-600 text-white rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 resize-none placeholder-slate-500 transition-all"
                                  placeholder="2. surucunun beyanini yazin..."></textarea>
                    </div>
                </div>
            </div>

            <!-- Analiz Butonu -->
            <div class="flex justify-center">
                <button type="submit" id="analyzeBtn" disabled
                        class="bg-gradient-to-r from-amber-500 to-orange-600 text-white font-semibold px-10 py-3.5 rounded-xl
                               hover:from-amber-600 hover:to-orange-700 focus:ring-4 focus:ring-amber-500/30
                               disabled:from-slate-600 disabled:to-slate-700 disabled:cursor-not-allowed disabled:shadow-none
                               transition-all shadow-lg shadow-amber-500/20 disabled:shadow-none
                               flex items-center gap-2 text-base">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                    Analiz Et
                </button>
            </div>
        </form>

        <!-- Yukleme / Analiz Durumu -->
        <div id="progressSection" class="hidden mt-8">
            <div class="bg-slate-800/60 backdrop-blur rounded-xl border border-slate-700/50 p-6 shadow-xl">
                <div class="flex items-center gap-3 mb-4">
                    <div id="spinner" class="w-8 h-8 border-3 border-amber-500 border-t-transparent rounded-full animate-spin"></div>
                    <p id="progressText" class="text-slate-200 font-medium">Fotograflar yukleniyor...</p>
                </div>
                <div class="w-full bg-slate-700 rounded-full h-2.5 overflow-hidden">
                    <div id="progressBar" class="bg-gradient-to-r from-amber-500 to-orange-500 h-2.5 rounded-full transition-all duration-500 ease-out" style="width: 0%"></div>
                </div>
            </div>
        </div>

        <!-- Sonuc Ekrani -->
        <div id="resultSection" class="hidden mt-8">
            <div class="bg-slate-800/60 backdrop-blur rounded-xl border border-slate-700/50 overflow-hidden shadow-xl">
                <div class="bg-gradient-to-r from-emerald-500/20 to-green-500/10 border-b border-emerald-500/20 px-6 py-4 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h2 class="text-lg font-semibold text-emerald-300">Analiz Tamamlandi</h2>
                </div>
                <div id="resultContent" class="px-6 py-6 prose prose-sm max-w-none text-slate-300"></div>
                <div class="border-t border-slate-700/50 px-6 py-4 bg-slate-800/40 flex flex-wrap gap-3">
                    <button id="printBtn" class="text-sm text-slate-400 hover:text-white flex items-center gap-1.5 bg-slate-700/50 px-4 py-2 rounded-lg hover:bg-slate-700 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        Yazdir
                    </button>
                    <button id="newAnalysisBtn" class="text-sm text-amber-400 hover:text-amber-300 font-medium flex items-center gap-1.5 bg-amber-500/10 px-4 py-2 rounded-lg hover:bg-amber-500/20 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Yeni Analiz Yap
                    </button>
                </div>
            </div>

            <!-- Uyari -->
            <div class="mt-4 bg-amber-500/10 border border-amber-500/20 rounded-xl p-4 backdrop-blur">
                <div class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-amber-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-sm text-amber-200/80">
                        Bu rapor yapay zeka tarafindan olusturulmus bir <strong class="text-amber-300">on degerlendirmedir</strong>.
                        Hukuki baglayiciligi yoktur. Kesin kusur tespiti yetkili merciler tarafindan yapilir.
                    </p>
                </div>
            </div>
        </div>

        <!-- Hata Ekrani -->
        <div id="errorSection" class="hidden mt-8">
            <div class="bg-red-500/10 border border-red-500/20 rounded-xl p-6 backdrop-blur">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg bg-red-500/20 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-red-300">Hata Olustu</h3>
                        <p id="errorMessage" class="text-sm text-red-300/80 mt-1"></p>
                        <button id="retryBtn" class="mt-3 text-sm text-red-400 hover:text-red-300 font-medium flex items-center gap-1.5 bg-red-500/10 px-4 py-2 rounded-lg hover:bg-red-500/20 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Tekrar Dene
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <!-- Footer -->
    <footer class="relative border-t border-slate-700/50 mt-16 py-6">
        <div class="max-w-5xl mx-auto px-4 text-center text-sm text-slate-500">
            <p>&copy; <?= date('Y') ?> <?= htmlspecialchars(APP_NAME) ?> v<?= APP_VERSION ?></p>
            <p class="mt-1 text-slate-600">Bu sistem bir on degerlendirme aracidir. KVKK uyumlu olarak calisir.</p>
        </div>
    </footer>

    <script src="assets/js/app.js"></script>
</body>
</html>
