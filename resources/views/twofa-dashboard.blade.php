<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>2FA Security Portal - Google Authenticator & Recovery Codes</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- QRCode.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        code, pre, .font-mono { font-family: 'JetBrains Mono', monospace; }
        .glass-panel {
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .custom-scroll::-webkit-scrollbar { width: 5px; height: 5px; }
        .custom-scroll::-webkit-scrollbar-track { background: rgba(15, 23, 42, 0.6); }
        .custom-scroll::-webkit-scrollbar-thumb { background: rgba(51, 65, 85, 0.8); border-radius: 4px; }
        .otp-input::-webkit-outer-spin-button, .otp-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        .pulse-glow {
            box-shadow: 0 0 25px -5px rgba(56, 189, 248, 0.3);
        }
    </style>
</head>
<body class="h-full text-slate-200 antialiased bg-slate-950 flex flex-col justify-between overflow-x-hidden selection:bg-sky-500 selection:text-white">

    <!-- Background Atmospheric Glows -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none z-0">
        <div class="absolute -top-40 left-1/4 w-96 h-96 bg-sky-500/10 rounded-full blur-3xl"></div>
        <div class="absolute top-1/3 -right-20 w-96 h-96 bg-indigo-500/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-20 left-1/3 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl"></div>
    </div>

    <!-- Header Navbar -->
    <header class="relative z-10 glass-panel border-b border-slate-800/80 px-4 lg:px-8 py-3.5 flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-sky-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-sky-500/20 text-white font-black text-lg">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div>
                <h1 class="text-sm sm:text-base font-extrabold text-white tracking-tight flex items-center gap-2">
                    2FA Security Portal
                    <span class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-md bg-sky-500/15 text-sky-400 border border-sky-500/30">Google TOTP & 8-Digit Backup</span>
                </h1>
                <p class="text-[11px] text-slate-400">Laravel 12 API Authentication with Two-Factor Security</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <!-- User Status Chip -->
            <div id="header-user-status" class="hidden items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-800/80 border border-slate-700 text-xs">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="text-slate-300 font-medium" id="header-user-name">User</span>
                <button onclick="handleLogout()" class="ml-2 text-slate-400 hover:text-rose-400 transition-colors" title="Log Out">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                </button>
            </div>

            <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 bg-slate-900/80 px-3 py-1.5 rounded-xl border border-slate-800">
                <i class="fa-solid fa-server text-emerald-400"></i>
                <span>API Live</span>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="relative z-10 flex-1 flex items-center justify-center p-4 sm:p-6 lg:p-8">
        <div class="w-full max-w-xl">

            <!-- ========================================== -->
            <!-- STEP 1: EMAIL & PASSWORD LOGIN             -->
            <!-- ========================================== -->
            <div id="step-login" class="glass-panel rounded-3xl p-6 sm:p-8 shadow-2xl border border-slate-800 transition-all duration-300">
                <div class="text-center mb-6">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-sky-500/10 border border-sky-500/20 text-sky-400 flex items-center justify-center text-2xl mb-3 shadow-inner">
                        <i class="fa-solid fa-lock"></i>
                    </div>
                    <h2 class="text-xl font-bold text-white tracking-tight">Account Login</h2>
                    <p class="text-xs text-slate-400 mt-1">Enter your credentials to initiate Two-Factor Authentication</p>
                </div>

                <!-- Quick Test Credentials Chips -->
                <div class="mb-5 p-3 rounded-2xl bg-slate-900/70 border border-slate-800/80">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2 flex items-center gap-1.5">
                        <i class="fa-solid fa-bolt text-amber-400"></i> Quick Fill Demo Accounts
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" onclick="quickFill('john@example.com', 'password')" class="p-2 rounded-xl bg-slate-800/80 hover:bg-slate-700/80 border border-slate-700/60 text-left transition-all group">
                            <div class="text-xs font-bold text-slate-200 group-hover:text-sky-300">🧑‍💻 John Doe</div>
                            <div class="text-[11px] text-slate-400 truncate">john@example.com</div>
                        </button>
                        <button type="button" onclick="quickFill('sarah@example.com', 'password')" class="p-2 rounded-xl bg-slate-800/80 hover:bg-slate-700/80 border border-slate-700/60 text-left transition-all group">
                            <div class="text-xs font-bold text-slate-200 group-hover:text-indigo-300">👩‍💼 Sarah Connor</div>
                            <div class="text-[11px] text-slate-400 truncate">sarah@example.com</div>
                        </button>
                    </div>
                </div>

                <form id="login-form" onsubmit="handleLoginSubmit(event)" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Email Address</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-500">
                                <i class="fa-regular fa-envelope"></i>
                            </span>
                            <input type="email" id="login-email" required value="john@example.com" placeholder="name@example.com" class="w-full bg-slate-900 border border-slate-700/80 rounded-xl pl-10 pr-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500 transition-all">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Password</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-500">
                                <i class="fa-solid fa-key"></i>
                            </span>
                            <input type="password" id="login-password" required value="password" placeholder="••••••••" class="w-full bg-slate-900 border border-slate-700/80 rounded-xl pl-10 pr-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500 transition-all">
                        </div>
                    </div>

                    <div id="login-error" class="hidden p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs flex items-center gap-2">
                        <i class="fa-solid fa-circle-exclamation text-rose-400"></i>
                        <span id="login-error-msg">Invalid credentials</span>
                    </div>

                    <button type="submit" id="btn-login" class="w-full py-3 bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-400 hover:to-indigo-500 text-white font-bold text-sm rounded-xl shadow-lg shadow-sky-500/20 transition-all flex items-center justify-center gap-2">
                        <span>Continue to 2FA</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </button>
                </form>
            </div>

            <!-- ========================================== -->
            <!-- STEP 2: 2FA VERIFICATION (TOTP & BACKUP)   -->
            <!-- ========================================== -->
            <div id="step-2fa" class="glass-panel rounded-3xl p-6 sm:p-8 shadow-2xl border border-slate-800 hidden transition-all duration-300">
                
                <!-- Target User Banner -->
                <div class="flex items-center justify-between pb-4 mb-5 border-b border-slate-800">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-sky-500/15 border border-sky-500/30 text-sky-400 flex items-center justify-center font-bold text-sm">
                            <i class="fa-solid fa-user-shield"></i>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-white" id="auth-target-name">User Name</div>
                            <div class="text-[11px] text-slate-400" id="auth-target-email">user@example.com</div>
                        </div>
                    </div>
                    <button onclick="backToLogin()" class="text-xs text-slate-400 hover:text-slate-200 transition-colors flex items-center gap-1">
                        <i class="fa-solid fa-chevron-left text-[10px]"></i> Change
                    </button>
                </div>

                <!-- 2FA Method Selector Tabs -->
                <div class="grid grid-cols-2 gap-1.5 p-1 bg-slate-900 rounded-2xl border border-slate-800 mb-5 text-xs font-semibold">
                    <button type="button" id="tab-btn-totp" onclick="switch2FaMethod('totp')" class="py-2 rounded-xl transition-all flex items-center justify-center gap-2 bg-sky-600 text-white shadow-sm">
                        <i class="fa-solid fa-mobile-screen-button"></i>
                        <span>Google TOTP</span>
                    </button>
                    <button type="button" id="tab-btn-recovery" onclick="switch2FaMethod('recovery')" class="py-2 rounded-xl transition-all flex items-center justify-center gap-2 text-slate-400 hover:text-white">
                        <i class="fa-solid fa-key"></i>
                        <span>Emergency Code</span>
                    </button>
                </div>

                <!-- METHOD 1: GOOGLE AUTHENTICATOR (TOTP) -->
                <div id="method-totp" class="space-y-5">
                    <!-- QR Code & Key Box -->
                    <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800 flex flex-col sm:flex-row items-center gap-4">
                        <!-- Dynamic SVG QR Code Container -->
                        <div class="bg-white p-2.5 rounded-xl shadow-md shrink-0 flex items-center justify-center" style="width: 130px; height: 130px;">
                            <div id="qrcode-box" class="w-full h-full flex items-center justify-center"></div>
                        </div>

                        <div class="flex-1 text-center sm:text-left">
                            <span class="text-[10px] uppercase font-bold text-sky-400 tracking-wider">Scan with Authenticator</span>
                            <h4 class="text-xs font-semibold text-slate-200 mt-0.5">Google, Microsoft, or Authy</h4>
                            <p class="text-[11px] text-slate-400 mt-1">Scan the QR code or enter the secret key manually into your app.</p>
                            
                            <div class="mt-2.5 flex items-center gap-1.5 bg-slate-950 px-2.5 py-1.5 rounded-xl border border-slate-800">
                                <span class="font-mono text-[11px] text-sky-300 font-bold select-all truncate" id="manual-key-text">GENERATING...</span>
                                <button type="button" onclick="copySecretKey()" class="p-1 text-slate-400 hover:text-sky-300 transition-colors shrink-0" title="Copy Secret Key">
                                    <i class="fa-regular fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- ⏱️ Live 30-Second TOTP Progress Countdown Bar -->
                    <div class="p-3 rounded-2xl bg-slate-900/60 border border-slate-800/80">
                        <div class="flex items-center justify-between text-[11px] mb-1.5">
                            <span class="text-slate-400 font-medium flex items-center gap-1.5">
                                <i class="fa-regular fa-clock text-sky-400"></i>
                                <span>Code Refresh Cycle:</span>
                            </span>
                            <span id="totp-timer-text" class="font-mono font-bold text-emerald-400">30s remaining</span>
                        </div>
                        <div class="w-full bg-slate-800 h-1.5 rounded-full overflow-hidden">
                            <div id="totp-progress-bar" class="bg-emerald-400 h-full rounded-full transition-all duration-500 ease-linear" style="width: 100%;"></div>
                        </div>
                    </div>

                    <!-- Modern 6-Digit Auto-Focusing OTP Input -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-2 text-center">
                            Enter 6-Digit Code from Authenticator App
                        </label>
                        <div class="flex justify-center items-center gap-2 sm:gap-3" id="otp-inputs-container">
                            <input type="text" maxlength="1" inputmode="numeric" class="otp-input w-11 sm:w-12 h-12 text-center text-xl font-bold font-mono bg-slate-900 border border-slate-700 rounded-xl text-white focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 transition-all" data-index="0" autofocus>
                            <input type="text" maxlength="1" inputmode="numeric" class="otp-input w-11 sm:w-12 h-12 text-center text-xl font-bold font-mono bg-slate-900 border border-slate-700 rounded-xl text-white focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 transition-all" data-index="1">
                            <input type="text" maxlength="1" inputmode="numeric" class="otp-input w-11 sm:w-12 h-12 text-center text-xl font-bold font-mono bg-slate-900 border border-slate-700 rounded-xl text-white focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 transition-all" data-index="2">
                            <span class="text-slate-600 font-bold">-</span>
                            <input type="text" maxlength="1" inputmode="numeric" class="otp-input w-11 sm:w-12 h-12 text-center text-xl font-bold font-mono bg-slate-900 border border-slate-700 rounded-xl text-white focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 transition-all" data-index="3">
                            <input type="text" maxlength="1" inputmode="numeric" class="otp-input w-11 sm:w-12 h-12 text-center text-xl font-bold font-mono bg-slate-900 border border-slate-700 rounded-xl text-white focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 transition-all" data-index="4">
                            <input type="text" maxlength="1" inputmode="numeric" class="otp-input w-11 sm:w-12 h-12 text-center text-xl font-bold font-mono bg-slate-900 border border-slate-700 rounded-xl text-white focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 transition-all" data-index="5">
                        </div>
                    </div>

                    <div id="totp-error" class="hidden p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs flex items-center gap-2">
                        <i class="fa-solid fa-circle-exclamation text-rose-400"></i>
                        <span id="totp-error-msg">Invalid OTP code. Please try again.</span>
                    </div>

                    <button type="button" onclick="submitGoogleOtp()" id="btn-verify-totp" class="w-full py-3 bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-400 hover:to-indigo-500 text-white font-bold text-sm rounded-xl shadow-lg shadow-sky-500/20 transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid fa-check"></i>
                        <span>Verify & Sign In</span>
                    </button>
                </div>

                <!-- METHOD 2: EMERGENCY RECOVERY CODE -->
                <div id="method-recovery" class="space-y-5 hidden">
                    <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-300 text-xs">
                        <div class="font-bold flex items-center gap-2 mb-1">
                            <i class="fa-solid fa-triangle-exclamation"></i> Emergency Backup Access
                        </div>
                        <p class="text-[11px] text-amber-200/80">
                            Lost your phone or Google Authenticator? Enter one of your unused 8-digit emergency recovery codes (e.g. <span class="font-mono font-bold">A1B2-C3D4</span>) below.
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">8-Digit Recovery Code</label>
                        <input type="text" id="recovery-code-input" placeholder="e.g. 7F3A-9E2B" class="w-full bg-slate-900 border border-slate-700/80 rounded-xl px-4 py-3 text-sm font-mono text-center font-bold uppercase tracking-widest text-amber-300 placeholder-slate-600 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-all">
                    </div>

                    <div id="recovery-error" class="hidden p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs flex items-center gap-2">
                        <i class="fa-solid fa-circle-exclamation text-rose-400"></i>
                        <span id="recovery-error-msg">Invalid recovery code</span>
                    </div>

                    <button type="button" onclick="submitRecoveryCode()" id="btn-verify-recovery" class="w-full py-3 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-400 hover:to-orange-500 text-white font-bold text-sm rounded-xl shadow-lg shadow-amber-500/20 transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid fa-key"></i>
                        <span>Verify Emergency Code</span>
                    </button>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- STEP 3: LOGGED-IN DASHBOARD & RECOVERY     -->
            <!-- ========================================== -->
            <div id="step-dashboard" class="glass-panel rounded-3xl p-6 sm:p-8 shadow-2xl border border-slate-800 hidden space-y-6 transition-all duration-300">
                
                <!-- Success Banner -->
                <div class="p-4 rounded-2xl bg-gradient-to-r from-emerald-500/15 to-teal-500/10 border border-emerald-500/30 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 flex items-center justify-center text-lg">
                            <i class="fa-solid fa-shield-check"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-white flex items-center gap-2">
                                2FA Authentication Successful
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">Active</span>
                            </h3>
                            <p class="text-xs text-slate-300">Logged in as <strong id="dash-user-email" class="text-white">user@example.com</strong></p>
                        </div>
                    </div>
                    <button onclick="handleLogout()" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-rose-500/20 text-slate-300 hover:text-rose-300 border border-slate-700 text-xs font-semibold transition-all">
                        Logout
                    </button>
                </div>

                <!-- Emergency Backup Recovery Codes Card -->
                <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <h4 class="text-xs font-bold text-slate-200 uppercase tracking-wider flex items-center gap-2">
                                <i class="fa-solid fa-key text-amber-400"></i>
                                <span>Emergency Backup Recovery Codes (8-Digit)</span>
                            </h4>
                            <p class="text-[11px] text-slate-400 mt-0.5">Save these 8 one-time codes in a safe place. Each code can be used once.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button onclick="downloadBackupCodes()" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-[11px] font-semibold flex items-center gap-1.5 transition-all">
                                <i class="fa-solid fa-download text-sky-400"></i> Download TXT
                            </button>
                            <button onclick="copyAllBackupCodes()" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-[11px] font-semibold flex items-center gap-1.5 transition-all">
                                <i class="fa-solid fa-copy text-emerald-400"></i> Copy All
                            </button>
                            <button onclick="regenerateBackupCodes()" class="px-2.5 py-1 rounded-lg bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 border border-amber-500/30 text-[11px] font-semibold flex items-center gap-1.5 transition-all" title="Regenerate 8 fresh codes">
                                <i class="fa-solid fa-arrows-rotate"></i> Regenerate
                            </button>
                        </div>
                    </div>

                    <!-- Recovery Codes Grid -->
                    <div id="recovery-codes-grid" class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-center font-mono text-xs">
                        <!-- Populated by JS -->
                    </div>
                </div>

                <!-- Bearer Token & Live API Endpoint Tester -->
                <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-200 uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-passport text-sky-400"></i>
                            <span>Sanctum Bearer Token</span>
                        </span>
                        <button onclick="copyBearerToken()" class="text-xs text-sky-400 hover:text-sky-300 font-semibold flex items-center gap-1">
                            <i class="fa-regular fa-copy"></i> Copy Token
                        </button>
                    </div>

                    <div class="p-2.5 rounded-xl bg-slate-950 border border-slate-800 font-mono text-[11px] text-sky-300 break-all select-all" id="dash-token-display">
                        // Token will appear here
                    </div>

                    <!-- Live API Profile Request -->
                    <div class="pt-2 border-t border-slate-800 flex items-center justify-between">
                        <span class="text-xs text-slate-400 font-medium">Test Protected Route: <code class="text-emerald-400 font-bold">GET /api/profile</code></span>
                        <button onclick="testApiProfile()" class="px-3 py-1.5 bg-sky-600 hover:bg-sky-500 text-white rounded-xl text-xs font-bold transition-all flex items-center gap-1.5">
                            <i class="fa-solid fa-play text-[10px]"></i> Test API
                        </button>
                    </div>

                    <pre id="dash-api-response" class="hidden p-3 rounded-xl bg-slate-950 border border-slate-800 font-mono text-xs text-emerald-400 overflow-x-auto custom-scroll max-h-48"></pre>
                </div>

            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="relative z-10 glass-panel border-t border-slate-800/80 py-3 px-4 text-center text-xs text-slate-500">
        PHP Laravel 12 &bull; Google 2FA (TOTP RFC 6238) &bull; 8-Digit Emergency Recovery Codes &bull; Laravel Sanctum
    </footer>

    <!-- APP JAVASCRIPT LOGIC -->
    <script>
        // State
        let currentUserId = null;
        let currentUserEmail = '';
        let currentUserName = '';
        let currentSecretKey = '';
        let currentQrUrl = '';
        let currentToken = '';
        let currentRecoveryCodes = [];
        let totpTimerInterval = null;
        let qrCodeInstance = null;

        // Quick Fill Helper
        function quickFill(email, password) {
            document.getElementById('login-email').value = email;
            document.getElementById('login-password').value = password;
        }

        // Common Headers Helper
        function getHeaders(extra = {}) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const headers = {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                ...extra
            };
            if (csrfToken) {
                headers['X-CSRF-TOKEN'] = csrfToken;
                headers['X-XSRF-TOKEN'] = csrfToken;
            }
            return headers;
        }

        // ------------------ STEP 1: LOGIN SUBMIT ------------------
        async function handleLoginSubmit(e) {
            e.preventDefault();
            const btn = document.getElementById('btn-login');
            const errBox = document.getElementById('login-error');
            errBox.classList.add('hidden');
            btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin text-sm"></i> <span>Authenticating...</span>`;
            btn.disabled = true;

            const email = document.getElementById('login-email').value.trim();
            const password = document.getElementById('login-password').value;

            try {
                const res = await fetch('/api/login', {
                    method: 'POST',
                    headers: getHeaders(),
                    body: JSON.stringify({ email, password })
                });

                const data = await res.json();

                if (data.success && data.otp_required) {
                    currentUserId = data.user_id;
                    currentUserEmail = data.user_email || email;
                    currentUserName = data.user_name || 'User';
                    currentSecretKey = data.manual_key || '';
                    currentQrUrl = data.qr_code || '';

                    // Transition to 2FA screen
                    document.getElementById('auth-target-name').innerText = currentUserName;
                    document.getElementById('auth-target-email').innerText = currentUserEmail;
                    document.getElementById('manual-key-text').innerText = currentSecretKey;

                    // Render QR Code
                    renderQrCode(currentQrUrl);

                    // Start 30s TOTP countdown
                    startTotpTimer();

                    // Switch view
                    document.getElementById('step-login').classList.add('hidden');
                    document.getElementById('step-2fa').classList.remove('hidden');

                    // Focus first OTP input
                    setTimeout(() => {
                        const firstInput = document.querySelector('.otp-input[data-index="0"]');
                        if (firstInput) firstInput.focus();
                    }, 200);
                } else {
                    errBox.classList.remove('hidden');
                    document.getElementById('login-error-msg').innerText = data.message || 'Invalid credentials.';
                }
            } catch (err) {
                console.error('Login error:', err);
                errBox.classList.remove('hidden');
                document.getElementById('login-error-msg').innerText = 'Network error or server unavailable.';
            } finally {
                btn.innerHTML = `<span>Continue to 2FA</span> <i class="fa-solid fa-arrow-right text-xs"></i>`;
                btn.disabled = false;
            }
        }

        // ------------------ QR CODE RENDERER ------------------
        function renderQrCode(url) {
            const container = document.getElementById('qrcode-box');
            container.innerHTML = '';
            if (!url) return;

            try {
                qrCodeInstance = new QRCode(container, {
                    text: url,
                    width: 110,
                    height: 110,
                    colorDark: "#000000",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.M
                });
            } catch (e) {
                console.error('QRCode render error:', e);
            }
        }

        // ------------------ ⏱️ 30-SECOND TOTP TIMER ------------------
        function startTotpTimer() {
            if (totpTimerInterval) clearInterval(totpTimerInterval);

            function updateTimer() {
                const nowSec = Math.floor(Date.now() / 1000);
                const remaining = 30 - (nowSec % 30);
                const percentage = (remaining / 30) * 100;

                const timerText = document.getElementById('totp-timer-text');
                const progressBar = document.getElementById('totp-progress-bar');

                if (timerText) timerText.innerText = `${remaining}s remaining`;
                if (progressBar) {
                    progressBar.style.width = `${percentage}%`;

                    // Dynamic color changing
                    if (remaining > 15) {
                        progressBar.className = 'bg-emerald-400 h-full rounded-full transition-all duration-500 ease-linear';
                        timerText.className = 'font-mono font-bold text-emerald-400';
                    } else if (remaining > 6) {
                        progressBar.className = 'bg-amber-400 h-full rounded-full transition-all duration-500 ease-linear';
                        timerText.className = 'font-mono font-bold text-amber-400';
                    } else {
                        progressBar.className = 'bg-rose-400 h-full rounded-full transition-all duration-500 ease-linear animate-pulse';
                        timerText.className = 'font-mono font-bold text-rose-400';
                    }
                }
            }

            updateTimer();
            totpTimerInterval = setInterval(updateTimer, 1000);
        }

        // ------------------ 6-DIGIT OTP INPUT HANDLING ------------------
        const otpInputs = document.querySelectorAll('.otp-input');
        otpInputs.forEach((input, idx) => {
            input.addEventListener('input', (e) => {
                const val = e.target.value.replace(/[^0-9]/g, '');
                e.target.value = val ? val.slice(-1) : '';

                if (val && idx < otpInputs.length - 1) {
                    otpInputs[idx + 1].focus();
                }

                // If all 6 digits entered, auto-submit
                const fullOtp = getEnteredOtp();
                if (fullOtp.length === 6) {
                    submitGoogleOtp();
                }
            });

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !e.target.value && idx > 0) {
                    otpInputs[idx - 1].focus();
                }
            });

            // Handle Paste full OTP
            input.addEventListener('paste', (e) => {
                e.preventDefault();
                const pasteData = (e.clipboardData || window.clipboardData).getData('text').trim().replace(/[^0-9]/g, '');
                if (pasteData) {
                    for (let i = 0; i < otpInputs.length; i++) {
                        otpInputs[i].value = pasteData[i] || '';
                    }
                    const nextIdx = Math.min(pasteData.length, otpInputs.length - 1);
                    otpInputs[nextIdx].focus();

                    if (pasteData.length >= 6) {
                        submitGoogleOtp();
                    }
                }
            });
        });

        function getEnteredOtp() {
            let code = '';
            otpInputs.forEach(inp => code += inp.value);
            return code;
        }

        // ------------------ SUBMIT GOOGLE OTP ------------------
        async function submitGoogleOtp() {
            const otp = getEnteredOtp();
            const errBox = document.getElementById('totp-error');
            const btn = document.getElementById('btn-verify-totp');
            errBox.classList.add('hidden');

            if (otp.length < 6) {
                errBox.classList.remove('hidden');
                document.getElementById('totp-error-msg').innerText = 'Please enter all 6 digits.';
                return;
            }

            btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin text-sm"></i> <span>Verifying...</span>`;
            btn.disabled = true;

            try {
                const res = await fetch('/api/verify-google-otp', {
                    method: 'POST',
                    headers: getHeaders(),
                    body: JSON.stringify({ user_id: currentUserId, otp: otp })
                });

                const data = await res.json();

                if (data.success && data.token) {
                    onAuthSuccess(data);
                } else {
                    errBox.classList.remove('hidden');
                    document.getElementById('totp-error-msg').innerText = data.message || 'Invalid OTP code. Please check your authenticator.';
                    // Clear inputs
                    otpInputs.forEach(i => i.value = '');
                    otpInputs[0].focus();
                }
            } catch (err) {
                console.error('Verify error:', err);
                errBox.classList.remove('hidden');
                document.getElementById('totp-error-msg').innerText = 'Network error or server unavailable.';
            } finally {
                btn.innerHTML = `<i class="fa-solid fa-check"></i> <span>Verify & Sign In</span>`;
                btn.disabled = false;
            }
        }

        // ------------------ SUBMIT RECOVERY CODE ------------------
        async function submitRecoveryCode() {
            const codeInput = document.getElementById('recovery-code-input');
            const code = codeInput.value.trim();
            const errBox = document.getElementById('recovery-error');
            const btn = document.getElementById('btn-verify-recovery');
            errBox.classList.add('hidden');

            if (!code) {
                errBox.classList.remove('hidden');
                document.getElementById('recovery-error-msg').innerText = 'Please enter an 8-digit recovery code.';
                return;
            }

            btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin text-sm"></i> <span>Verifying Code...</span>`;
            btn.disabled = true;

            try {
                const res = await fetch('/api/verify-recovery-code', {
                    method: 'POST',
                    headers: getHeaders(),
                    body: JSON.stringify({ user_id: currentUserId, recovery_code: code })
                });

                const data = await res.json();

                if (data.success && data.token) {
                    onAuthSuccess(data);
                } else {
                    errBox.classList.remove('hidden');
                    document.getElementById('recovery-error-msg').innerText = data.message || 'Invalid or already used recovery code.';
                }
            } catch (err) {
                console.error('Recovery verify error:', err);
                errBox.classList.remove('hidden');
                document.getElementById('recovery-error-msg').innerText = 'Network error or server unavailable.';
            } finally {
                btn.innerHTML = `<i class="fa-solid fa-key"></i> <span>Verify Emergency Code</span>`;
                btn.disabled = false;
            }
        }

        // ------------------ SUCCESS HANDLER ------------------
        function onAuthSuccess(data) {
            currentToken = data.token;
            currentUserEmail = data.user?.email || currentUserEmail;
            currentUserName = data.user?.name || currentUserName;
            currentRecoveryCodes = data.user?.recovery_codes || [];

            if (totpTimerInterval) clearInterval(totpTimerInterval);

            // Update UI
            document.getElementById('step-2fa').classList.add('hidden');
            document.getElementById('step-dashboard').classList.remove('hidden');

            document.getElementById('header-user-status').classList.remove('hidden');
            document.getElementById('header-user-status').classList.add('flex');
            document.getElementById('header-user-name').innerText = currentUserName;

            document.getElementById('dash-user-email').innerText = currentUserEmail;
            document.getElementById('dash-token-display').innerText = currentToken;

            renderRecoveryCodesGrid(currentRecoveryCodes);
        }

        // ------------------ RENDER RECOVERY CODES GRID ------------------
        function renderRecoveryCodesGrid(codes) {
            const grid = document.getElementById('recovery-codes-grid');
            if (!codes || codes.length === 0) {
                grid.innerHTML = `<div class="col-span-full py-3 text-slate-500">No recovery codes generated yet.</div>`;
                return;
            }

            grid.innerHTML = codes.map((c, i) => {
                const isUsed = !!c.used_at;
                if (isUsed) {
                    return `
                        <div class="p-2.5 rounded-xl bg-slate-950/70 border border-slate-800/80 text-slate-600 line-through select-none relative group">
                            <span class="font-bold">${c.code}</span>
                            <span class="block text-[9px] uppercase font-bold text-rose-500/80 mt-0.5">Used</span>
                        </div>
                    `;
                }
                return `
                    <div class="p-2.5 rounded-xl bg-amber-500/10 border border-amber-500/25 text-amber-300 font-bold select-all hover:bg-amber-500/20 transition-all cursor-pointer" onclick="copyText('${c.code}', 'Code copied!')" title="Click to copy code">
                        <span>${c.code}</span>
                        <span class="block text-[9px] uppercase font-bold text-emerald-400 mt-0.5">Active</span>
                    </div>
                `;
            }).join('');
        }

        // ------------------ REGENERATE RECOVERY CODES ------------------
        async function regenerateBackupCodes() {
            if (!confirm('Are you sure you want to regenerate all 8 recovery codes? Old unused codes will be invalidated.')) return;

            try {
                const res = await fetch('/api/2fa/recovery-codes/regenerate', {
                    method: 'POST',
                    headers: getHeaders({ 'Authorization': `Bearer ${currentToken}` }),
                    body: JSON.stringify({ user_id: currentUserId })
                });

                const data = await res.json();
                if (data.success && data.recovery_codes) {
                    currentRecoveryCodes = data.recovery_codes;
                    renderRecoveryCodesGrid(currentRecoveryCodes);
                    alert('8 fresh recovery codes generated successfully!');
                } else {
                    alert(data.message || 'Error regenerating codes.');
                }
            } catch (e) {
                console.error('Regenerate error:', e);
                alert('Network error while regenerating codes.');
            }
        }

        // ------------------ DOWNLOAD & COPY BACKUP CODES ------------------
        function downloadBackupCodes() {
            let text = `=== 2FA EMERGENCY BACKUP RECOVERY CODES ===\n`;
            text += `Account: ${currentUserEmail}\n`;
            text += `Generated: ${new Date().toLocaleString()}\n`;
            text += `Instructions: Keep these one-time codes in a safe place. Each code can be used once to access your account if your Google Authenticator is unavailable.\n\n`;

            currentRecoveryCodes.forEach((c, idx) => {
                const status = c.used_at ? `[USED on ${c.used_at}]` : `[ACTIVE]`;
                text += `${idx + 1}. ${c.code}  ${status}\n`;
            });

            const blob = new Blob([text], { type: 'text/plain;charset=utf-8' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `2fa-recovery-codes-${currentUserEmail.split('@')[0]}.txt`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }

        function copyAllBackupCodes() {
            const activeCodes = currentRecoveryCodes.filter(c => !c.used_at).map(c => c.code).join('\n');
            copyText(activeCodes, 'All active recovery codes copied to clipboard!');
        }

        // ------------------ TEST PROTECTED API /api/profile ------------------
        async function testApiProfile() {
            const box = document.getElementById('dash-api-response');
            box.classList.remove('hidden');
            box.innerText = '// Sending GET /api/profile with Bearer Token...';

            const startTime = performance.now();
            try {
                const res = await fetch('/api/profile', {
                    headers: getHeaders({ 'Authorization': `Bearer ${currentToken}` })
                });
                const elapsed = Math.round(performance.now() - startTime);
                const data = await res.json();
                box.innerText = `// HTTP Status: ${res.status} OK (${elapsed} ms)\n\n` + JSON.stringify(data, null, 2);
            } catch (e) {
                box.innerText = '// Error testing profile API: ' + e.message;
            }
        }

        // ------------------ SWITCH 2FA METHODS (TOTP / RECOVERY) ------------------
        function switch2FaMethod(method) {
            const totpBtn = document.getElementById('tab-btn-totp');
            const recBtn = document.getElementById('tab-btn-recovery');
            const totpSec = document.getElementById('method-totp');
            const recSec = document.getElementById('method-recovery');

            if (method === 'totp') {
                totpBtn.className = 'py-2 rounded-xl transition-all flex items-center justify-center gap-2 bg-sky-600 text-white shadow-sm';
                recBtn.className = 'py-2 rounded-xl transition-all flex items-center justify-center gap-2 text-slate-400 hover:text-white';
                totpSec.classList.remove('hidden');
                recSec.classList.add('hidden');
            } else {
                recBtn.className = 'py-2 rounded-xl transition-all flex items-center justify-center gap-2 bg-amber-600 text-white shadow-sm';
                totpBtn.className = 'py-2 rounded-xl transition-all flex items-center justify-center gap-2 text-slate-400 hover:text-white';
                recSec.classList.remove('hidden');
                totpSec.classList.add('hidden');
                document.getElementById('recovery-code-input').focus();
            }
        }

        // ------------------ NAVIGATION & LOGOUT ------------------
        function backToLogin() {
            if (totpTimerInterval) clearInterval(totpTimerInterval);
            document.getElementById('step-2fa').classList.add('hidden');
            document.getElementById('step-login').classList.remove('hidden');
        }

        async function handleLogout() {
            if (currentToken) {
                try {
                    await fetch('/api/logout', {
                        method: 'POST',
                        headers: getHeaders({ 'Authorization': `Bearer ${currentToken}` })
                    });
                } catch (e) {}
            }

            // Reset state
            currentToken = '';
            currentUserId = null;
            if (totpTimerInterval) clearInterval(totpTimerInterval);

            document.getElementById('step-dashboard').classList.add('hidden');
            document.getElementById('step-2fa').classList.add('hidden');
            document.getElementById('step-login').classList.remove('hidden');
            document.getElementById('header-user-status').classList.add('hidden');
            document.getElementById('header-user-status').classList.remove('flex');
        }

        // Helper: Copy Text & Alert
        function copyText(txt, successMsg) {
            navigator.clipboard.writeText(txt);
            alert(successMsg || 'Copied to clipboard!');
        }

        function copySecretKey() {
            copyText(currentSecretKey, 'Secret key copied to clipboard!');
        }

        function copyBearerToken() {
            copyText(currentToken, 'Bearer token copied to clipboard!');
        }
    </script>
</body>
</html>
