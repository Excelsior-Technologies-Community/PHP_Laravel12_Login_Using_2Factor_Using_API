<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>2FA Security Portal</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    >

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</head>

<body class="bg-slate-950 text-white min-h-screen">

<div class="min-h-screen">

    <!-- =====================================================
         HEADER
    ====================================================== -->

    <header class="border-b border-slate-800 bg-slate-900">

        <div class="max-w-7xl mx-auto px-6 py-5">

            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

                <div>
                    <h1 class="text-2xl font-bold">
                        <i class="fa-solid fa-shield-halved text-cyan-400"></i>
                        2FA Security Portal
                    </h1>

                    <p class="text-slate-400 text-sm mt-1">
                        Laravel 12 • Google Authenticator • Sanctum
                    </p>
                </div>

                <div id="headerUser" class="hidden text-right">

                    <div class="font-semibold" id="headerUserName"></div>

                    <div
                        class="text-xs text-slate-400"
                        id="headerUserEmail"
                    ></div>

                </div>

            </div>

        </div>

    </header>


    <!-- =====================================================
         MAIN
    ====================================================== -->

    <main class="max-w-7xl mx-auto px-6 py-8">


        <!-- =================================================
             LOGIN
        ================================================== -->

        <section id="loginSection" class="max-w-md mx-auto">

            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-8 shadow-xl">

                <div class="text-center mb-8">

                    <div class="w-16 h-16 mx-auto rounded-full bg-cyan-500/10 flex items-center justify-center">

                        <i class="fa-solid fa-lock text-2xl text-cyan-400"></i>

                    </div>

                    <h2 class="text-2xl font-bold mt-4">
                        Secure Login
                    </h2>

                    <p class="text-slate-400 text-sm mt-2">
                        Sign in using your email and password.
                    </p>

                </div>


                <form id="loginForm" class="space-y-5">

                    <div>

                        <label class="block text-sm text-slate-300 mb-2">
                            Email
                        </label>

                        <input
                            id="loginEmail"
                            type="email"
                            required
                            class="w-full rounded-xl bg-slate-800 border border-slate-700 px-4 py-3 focus:outline-none focus:border-cyan-400"
                            placeholder="john@example.com"
                        >

                    </div>


                    <div>

                        <label class="block text-sm text-slate-300 mb-2">
                            Password
                        </label>

                        <input
                            id="loginPassword"
                            type="password"
                            required
                            class="w-full rounded-xl bg-slate-800 border border-slate-700 px-4 py-3 focus:outline-none focus:border-cyan-400"
                            placeholder="password"
                        >

                    </div>


                    <button
                        type="submit"
                        class="w-full bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold py-3 rounded-xl transition"
                    >

                        <i class="fa-solid fa-right-to-bracket"></i>

                        Login

                    </button>

                </form>


                <div
                    id="loginMessage"
                    class="hidden mt-5 p-4 rounded-xl text-sm"
                ></div>

            </div>

        </section>


        <!-- =================================================
             2FA
        ================================================== -->

        <section
            id="twoFactorSection"
            class="hidden max-w-2xl mx-auto"
        >

            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-8">

                <div class="text-center mb-8">

                    <div class="w-16 h-16 mx-auto rounded-full bg-purple-500/10 flex items-center justify-center">

                        <i class="fa-solid fa-mobile-screen-button text-2xl text-purple-400"></i>

                    </div>

                    <h2 class="text-2xl font-bold mt-4">
                        Two-Factor Authentication
                    </h2>

                    <p class="text-slate-400 text-sm mt-2">
                        Verify your identity using Google Authenticator.
                    </p>

                </div>


                <!-- SETUP -->

                <div
                    id="setupBox"
                    class="hidden mb-8"
                >

                    <div class="bg-slate-800 rounded-xl p-5">

                        <h3 class="font-bold mb-4">
                            <i class="fa-solid fa-qrcode text-cyan-400"></i>
                            Setup Google Authenticator
                        </h3>

                        <div class="grid md:grid-cols-2 gap-6 items-center">

                            <div class="bg-white rounded-xl p-4 w-fit mx-auto">
                                <div id="qrCode"></div>
                            </div>

                            <div>

                                <p class="text-sm text-slate-300 mb-3">
                                    Scan the QR code with Google Authenticator.
                                </p>

                                <p class="text-xs text-slate-400 mb-2">
                                    Manual key:
                                </p>

                                <div
                                    id="manualKey"
                                    class="break-all bg-slate-950 border border-slate-700 rounded-lg p-3 text-xs font-mono text-cyan-300"
                                ></div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- 2FA TABS -->

                <div class="flex border-b border-slate-800 mb-6">

                    <button
                        id="authenticatorTab"
                        onclick="switch2FaMethod('authenticator')"
                        class="flex-1 py-3 border-b-2 border-cyan-400 text-cyan-400 font-semibold"
                    >
                        Google OTP
                    </button>

                    <button
                        id="recoveryTab"
                        onclick="switch2FaMethod('recovery')"
                        class="flex-1 py-3 border-b-2 border-transparent text-slate-400"
                    >
                        Recovery Code
                    </button>

                </div>


                <!-- GOOGLE OTP -->

                <div id="authenticatorBox">

                    <label class="block text-sm text-slate-300 mb-2">
                        6-digit Google Authenticator code
                    </label>

                    <input
                        id="otpInput"
                        maxlength="6"
                        inputmode="numeric"
                        class="w-full text-center text-2xl tracking-[0.5em] bg-slate-800 border border-slate-700 rounded-xl px-4 py-4 focus:outline-none focus:border-cyan-400"
                        placeholder="000000"
                    >

                    <button
                        onclick="submitGoogleOtp()"
                        class="w-full mt-5 bg-purple-500 hover:bg-purple-400 text-white font-bold py-3 rounded-xl"
                    >
                        <i class="fa-solid fa-shield-halved"></i>
                        Verify OTP
                    </button>

                </div>


                <!-- RECOVERY -->

                <div
                    id="recoveryBox"
                    class="hidden"
                >

                    <label class="block text-sm text-slate-300 mb-2">
                        Emergency recovery code
                    </label>

                    <input
                        id="recoveryInput"
                        class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-4 font-mono focus:outline-none focus:border-cyan-400"
                        placeholder="ABCD-1234"
                    >

                    <button
                        onclick="submitRecoveryCode()"
                        class="w-full mt-5 bg-orange-500 hover:bg-orange-400 text-white font-bold py-3 rounded-xl"
                    >
                        <i class="fa-solid fa-key"></i>
                        Use Recovery Code
                    </button>

                </div>


                <div
                    id="twoFactorMessage"
                    class="hidden mt-5 p-4 rounded-xl text-sm"
                ></div>


                <button
                    onclick="backToLogin()"
                    class="w-full mt-5 text-slate-400 hover:text-white text-sm"
                >
                    ← Back to login
                </button>

            </div>

        </section>


        <!-- =================================================
             DASHBOARD
        ================================================== -->

        <section
            id="dashboardSection"
            class="hidden"
        >

            <!-- DASHBOARD HEADER -->

            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-8">

                <div>

                    <h2 class="text-3xl font-bold">
                        Security Dashboard
                    </h2>

                    <p class="text-slate-400 mt-1">
                        Monitor authentication, tokens and 2FA security.
                    </p>

                    <div class="text-xs text-slate-500 mt-2">
                        <i class="fa-solid fa-rotate"></i>
                        Auto refresh:
                        <span id="autoRefreshStatus">30s</span>
                        |
                        Last refresh:
                        <span id="lastRefreshTime">-</span>
                    </div>

                </div>


                <div class="flex gap-2">

                    <button
                        onclick="refreshSecurityDashboard()"
                        class="bg-cyan-500 hover:bg-cyan-400 text-slate-950 px-5 py-3 rounded-xl font-semibold"
                    >
                        <i class="fa-solid fa-arrows-rotate"></i>
                        Refresh
                    </button>

                    <button
                        onclick="handleLogout()"
                        class="bg-red-500 hover:bg-red-400 px-5 py-3 rounded-xl font-semibold"
                    >
                        <i class="fa-solid fa-right-from-bracket"></i>
                        Logout
                    </button>

                </div>

            </div>


            <!-- NAVIGATION -->

            <div class="flex flex-wrap gap-2 mb-8">

                <button
                    onclick="showDashboardTab('overview')"
                    class="dashboard-tab active-tab px-5 py-3 rounded-xl bg-cyan-500 text-slate-950 font-semibold"
                    data-tab="overview"
                >
                    <i class="fa-solid fa-chart-pie"></i>
                    Overview
                </button>


                <button
                    onclick="showDashboardTab('activity')"
                    class="dashboard-tab px-5 py-3 rounded-xl bg-slate-800 text-slate-300 font-semibold"
                    data-tab="activity"
                >
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    Security Activity
                </button>


                <button
                    onclick="showDashboardTab('tokens')"
                    class="dashboard-tab px-5 py-3 rounded-xl bg-slate-800 text-slate-300 font-semibold"
                    data-tab="tokens"
                >
                    <i class="fa-solid fa-key"></i>
                    API Tokens
                </button>


                <button
                    onclick="showDashboardTab('settings')"
                    class="dashboard-tab px-5 py-3 rounded-xl bg-slate-800 text-slate-300 font-semibold"
                    data-tab="settings"
                >
                    <i class="fa-solid fa-gear"></i>
                    Security Settings
                </button>


                <button
                    onclick="showDashboardTab('account')"
                    class="dashboard-tab px-5 py-3 rounded-xl bg-slate-800 text-slate-300 font-semibold"
                    data-tab="account"
                >
                    <i class="fa-solid fa-user-gear"></i>
                    Account Security
                </button>

            </div>


            <!-- =================================================
                 OVERVIEW
            ================================================== -->

            <div id="overviewTab">

                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">

                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">

                        <div class="text-slate-400 text-sm">
                            Total Events
                        </div>

                        <div
                            id="statTotalEvents"
                            class="text-3xl font-bold mt-2"
                        >
                            0
                        </div>

                    </div>


                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">

                        <div class="text-slate-400 text-sm">
                            Successful Logins
                        </div>

                        <div
                            id="statSuccessful"
                            class="text-3xl font-bold mt-2 text-green-400"
                        >
                            0
                        </div>

                    </div>


                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">

                        <div class="text-slate-400 text-sm">
                            Failed Events
                        </div>

                        <div
                            id="statFailed"
                            class="text-3xl font-bold mt-2 text-red-400"
                        >
                            0
                        </div>

                    </div>


                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">

                        <div class="text-slate-400 text-sm">
                            Today's Events
                        </div>

                        <div
                            id="statToday"
                            class="text-3xl font-bold mt-2 text-cyan-400"
                        >
                            0
                        </div>

                    </div>

                </div>


                <!-- EVENT SUMMARY -->

                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 mb-6">

                    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3 mb-5">

                        <div>

                            <h3 class="text-xl font-bold">
                                <i class="fa-solid fa-chart-column text-purple-400"></i>
                                Activity Summary by Event
                            </h3>

                            <p class="text-sm text-slate-400 mt-1">
                                Authentication and security events.
                            </p>

                        </div>

                    </div>


                    <div class="grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">

                        <div class="bg-slate-800 rounded-xl p-4">
                            <div class="text-xs text-slate-400">Login</div>
                            <div id="summaryLogin" class="text-2xl font-bold mt-1">0</div>
                        </div>

                        <div class="bg-slate-800 rounded-xl p-4">
                            <div class="text-xs text-slate-400">OTP</div>
                            <div id="summaryOtp" class="text-2xl font-bold mt-1">0</div>
                        </div>

                        <div class="bg-slate-800 rounded-xl p-4">
                            <div class="text-xs text-slate-400">Recovery</div>
                            <div id="summaryRecovery" class="text-2xl font-bold mt-1">0</div>
                        </div>

                        <div class="bg-slate-800 rounded-xl p-4">
                            <div class="text-xs text-slate-400">Logout</div>
                            <div id="summaryLogout" class="text-2xl font-bold mt-1">0</div>
                        </div>

                        <div class="bg-slate-800 rounded-xl p-4">
                            <div class="text-xs text-slate-400">Token</div>
                            <div id="summaryToken" class="text-2xl font-bold mt-1">0</div>
                        </div>

                        <div class="bg-slate-800 rounded-xl p-4">
                            <div class="text-xs text-slate-400">2FA</div>
                            <div id="summaryTwoFa" class="text-2xl font-bold mt-1">0</div>
                        </div>

                    </div>

                </div>


                <!-- ACCOUNT -->

                <div class="grid lg:grid-cols-2 gap-6">

                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">

                        <h3 class="text-xl font-bold mb-5">
                            <i class="fa-solid fa-user text-cyan-400"></i>
                            Account
                        </h3>

                        <div class="space-y-4">

                            <div>
                                <div class="text-xs text-slate-500">
                                    Name
                                </div>

                                <div
                                    id="profileName"
                                    class="font-semibold"
                                ></div>
                            </div>


                            <div>
                                <div class="text-xs text-slate-500">
                                    Email
                                </div>

                                <div
                                    id="profileEmail"
                                    class="font-semibold"
                                ></div>
                            </div>


                            <div>
                                <div class="text-xs text-slate-500">
                                    2FA Status
                                </div>

                                <span
                                    id="profile2FA"
                                    class="inline-flex mt-1 px-3 py-1 rounded-full text-xs font-semibold"
                                ></span>
                            </div>


                            <div>
                                <div class="text-xs text-slate-500">
                                    Recovery Codes Remaining
                                </div>

                                <div
                                    id="profileRecoveryCount"
                                    class="font-semibold text-orange-400"
                                ></div>
                            </div>

                        </div>

                    </div>


                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">

                        <h3 class="text-xl font-bold mb-5">
                            <i class="fa-solid fa-chart-column text-purple-400"></i>
                            Authentication Statistics
                        </h3>

                        <div class="space-y-4">

                            <div class="flex justify-between border-b border-slate-800 pb-3">
                                <span class="text-slate-400">
                                    OTP Verifications
                                </span>

                                <strong id="statOtp">
                                    0
                                </strong>
                            </div>


                            <div class="flex justify-between border-b border-slate-800 pb-3">
                                <span class="text-slate-400">
                                    Recovery Logins
                                </span>

                                <strong id="statRecovery">
                                    0
                                </strong>
                            </div>


                            <div class="flex justify-between">
                                <span class="text-slate-400">
                                    Successful Events
                                </span>

                                <strong
                                    id="statSuccessEvents"
                                    class="text-green-400"
                                >
                                    0
                                </strong>
                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 ACTIVITY
            ================================================== -->

            <div
                id="activityTab"
                class="hidden"
            >

                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">

                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">

                        <div>

                            <h3 class="text-xl font-bold">
                                Security Activity & Login History
                            </h3>

                            <p class="text-sm text-slate-400">
                                Review authentication and security events.
                            </p>

                        </div>


                        <div class="flex flex-wrap gap-2">

                            <button
                                onclick="exportSecurityActivity()"
                                class="bg-green-500 hover:bg-green-400 text-slate-950 px-4 py-2 rounded-lg font-semibold"
                            >
                                <i class="fa-solid fa-file-csv"></i>
                                Export CSV
                            </button>


                            <button
                                onclick="loadActivities(currentActivityPage)"
                                class="bg-cyan-500 hover:bg-cyan-400 text-slate-950 px-4 py-2 rounded-lg font-semibold"
                            >
                                <i class="fa-solid fa-refresh"></i>
                                Refresh
                            </button>

                        </div>

                    </div>


                    <!-- FILTERS -->

                    <div class="grid md:grid-cols-4 gap-3 mb-6">

                        <input
                            id="activitySearch"
                            oninput="debouncedActivitySearch()"
                            class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2"
                            placeholder="Search..."
                        >


                        <select
                            id="activityEvent"
                            onchange="loadActivities(1)"
                            class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2"
                        >

                            <option value="">
                                All Events
                            </option>

                            <option value="password_login">
                                Password Login
                            </option>

                            <option value="google_otp_login">
                                Google OTP
                            </option>

                            <option value="recovery_code_login">
                                Recovery Code
                            </option>

                            <option value="logout">
                                Logout
                            </option>

                            <option value="logout_all_devices">
                                Logout All Devices
                            </option>

                            <option value="password_changed">
                                Password Changed
                            </option>

                            <option value="profile_updated">
                                Profile Updated
                            </option>

                            <option value="api_token_created">
                                Token Created
                            </option>

                            <option value="api_token_revoked">
                                Token Revoked
                            </option>

                            <option value="recovery_codes_regenerated">
                                Recovery Codes
                            </option>

                            <option value="2fa_enabled">
                                2FA Enabled
                            </option>

                            <option value="2fa_disabled">
                                2FA Disabled
                            </option>

                            <option value="2fa_secret_regenerated">
                                Secret Regenerated
                            </option>

                        </select>


                        <select
                            id="activityStatus"
                            onchange="loadActivities(1)"
                            class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2"
                        >

                            <option value="">
                                All Status
                            </option>

                            <option value="success">
                                Success
                            </option>

                            <option value="failed">
                                Failed
                            </option>

                        </select>


                        <input
                            id="activityDate"
                            onchange="loadActivities(1)"
                            type="date"
                            class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2"
                        >

                    </div>


                    <div class="overflow-x-auto">

                        <table class="w-full text-sm">

                            <thead>

                            <tr class="border-b border-slate-800 text-left text-slate-400">

                                <th class="p-3">
                                    Event
                                </th>

                                <th class="p-3">
                                    Status
                                </th>

                                <th class="p-3">
                                    Description
                                </th>

                                <th class="p-3">
                                    IP Address
                                </th>

                                <th class="p-3">
                                    Date & Time
                                </th>

                            </tr>

                            </thead>

                            <tbody id="activityTable"></tbody>

                        </table>

                    </div>


                    <!-- PAGINATION -->

                    <div
                        id="activityPagination"
                        class="flex flex-wrap items-center justify-center gap-2 mt-6"
                    ></div>

                </div>

            </div>


            <!-- =================================================
                 TOKENS
            ================================================== -->

            <div
                id="tokensTab"
                class="hidden"
            >

                <div class="grid lg:grid-cols-3 gap-6">


                    <!-- CREATE TOKEN -->

                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">

                        <h3 class="text-xl font-bold mb-2">

                            <i class="fa-solid fa-plus text-green-400"></i>

                            Create API Token

                        </h3>

                        <p class="text-sm text-slate-400 mb-5">
                            Create a named Sanctum token for API access.
                        </p>


                        <input
                            id="newTokenName"
                            class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-3"
                            placeholder="e.g. Mobile App"
                        >


                        <button
                            onclick="createApiToken()"
                            class="w-full mt-4 bg-green-500 hover:bg-green-400 text-slate-950 font-bold py-3 rounded-lg"
                        >
                            Create Token
                        </button>


                        <div
                            id="newTokenResult"
                            class="hidden mt-5"
                        >

                            <div class="text-xs text-slate-400 mb-2">
                                New token — copy it now:
                            </div>

                            <div
                                id="newTokenValue"
                                class="bg-slate-950 border border-slate-700 rounded-lg p-3 break-all font-mono text-xs text-green-300"
                            ></div>

                            <button
                                onclick="copyText(document.getElementById('newTokenValue').innerText)"
                                class="mt-3 text-sm text-cyan-400"
                            >
                                <i class="fa-solid fa-copy"></i>
                                Copy Token
                            </button>

                        </div>

                    </div>


                    <!-- TOKEN LIST -->

                    <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-2xl p-6">

                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">

                            <div>

                                <h3 class="text-xl font-bold">
                                    Active API Tokens
                                </h3>

                                <p class="text-sm text-slate-400">
                                    Manage your Sanctum access tokens.
                                </p>

                            </div>


                            <button
                                onclick="revokeOtherTokens()"
                                class="bg-red-500/20 text-red-300 border border-red-500/30 px-4 py-2 rounded-lg"
                            >
                                Revoke Other Tokens
                            </button>

                        </div>


                        <!-- TOKEN SEARCH -->

                        <div class="mb-5">

                            <div class="relative">

                                <i class="fa-solid fa-search absolute left-3 top-3 text-slate-500"></i>

                                <input
                                    id="tokenSearch"
                                    oninput="filterTokens()"
                                    class="w-full bg-slate-800 border border-slate-700 rounded-lg pl-10 pr-4 py-3"
                                    placeholder="Search API token by name..."
                                >

                            </div>

                        </div>


                        <div class="overflow-x-auto">

                            <table class="w-full text-sm">

                                <thead>

                                <tr class="border-b border-slate-800 text-slate-400 text-left">

                                    <th class="p-3">
                                        Name
                                    </th>

                                    <th class="p-3">
                                        Created
                                    </th>

                                    <th class="p-3">
                                        Last Used
                                    </th>

                                    <th class="p-3">
                                        Status
                                    </th>

                                    <th class="p-3">
                                        Action
                                    </th>

                                </tr>

                                </thead>

                                <tbody id="tokenTable"></tbody>

                            </table>

                        </div>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 SETTINGS
            ================================================== -->

            <div
                id="settingsTab"
                class="hidden"
            >

                <div class="grid lg:grid-cols-2 gap-6">


                    <!-- 2FA STATUS -->

                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">

                        <h3 class="text-xl font-bold mb-5">

                            <i class="fa-solid fa-shield-halved text-cyan-400"></i>

                            Two-Factor Security

                        </h3>


                        <div class="bg-slate-800 rounded-xl p-5 mb-5">

                            <div class="text-sm text-slate-400">
                                Current Status
                            </div>

                            <div
                                id="settings2FAStatus"
                                class="text-xl font-bold mt-1"
                            ></div>

                        </div>


                        <h4 class="font-semibold mb-3">
                            Regenerate Recovery Codes
                        </h4>

                        <p class="text-sm text-slate-400 mb-4">
                            Generate a fresh set of emergency recovery codes.
                        </p>


                        <button
                            onclick="regenerateBackupCodes()"
                            class="w-full bg-orange-500 hover:bg-orange-400 py-3 rounded-lg font-semibold"
                        >

                            <i class="fa-solid fa-rotate"></i>

                            Regenerate Recovery Codes

                        </button>


                        <div class="border-t border-slate-800 my-6"></div>


                        <h4 class="font-semibold mb-3">
                            Change Authenticator
                        </h4>

                        <p class="text-sm text-slate-400 mb-4">
                            Password and current OTP are required before generating a new authenticator.
                        </p>


                        <input
                            id="changeAuthPassword"
                            type="password"
                            class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 mb-3"
                            placeholder="Current password"
                        >


                        <input
                            id="changeAuthOtp"
                            maxlength="6"
                            class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 mb-3"
                            placeholder="Current Google OTP"
                        >


                        <button
                            onclick="startAuthenticatorChange()"
                            class="w-full bg-purple-500 hover:bg-purple-400 py-3 rounded-lg font-semibold"
                        >

                            Generate New Authenticator

                        </button>

                    </div>


                    <!-- DISABLE 2FA -->

                    <div class="bg-slate-900 border border-red-500/20 rounded-2xl p-6">

                        <h3 class="text-xl font-bold mb-2 text-red-300">

                            <i class="fa-solid fa-triangle-exclamation"></i>

                            Disable Two-Factor Authentication

                        </h3>


                        <p class="text-sm text-slate-400 mb-6">
                            This is a sensitive security operation.
                        </p>


                        <label class="block text-sm text-slate-400 mb-2">
                            Current Password
                        </label>


                        <input
                            id="disablePassword"
                            type="password"
                            class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 mb-4"
                            placeholder="Current password"
                        >


                        <label class="block text-sm text-slate-400 mb-2">
                            Google Authenticator OTP
                        </label>


                        <input
                            id="disableOtp"
                            maxlength="6"
                            class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 mb-5"
                            placeholder="6-digit OTP"
                        >


                        <button
                            onclick="disableTwoFactor()"
                            class="w-full bg-red-500 hover:bg-red-400 py-3 rounded-lg font-bold"
                        >
                            Disable 2FA
                        </button>


                        <div
                            id="settingsMessage"
                            class="hidden mt-5 p-4 rounded-lg text-sm"
                        ></div>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 ACCOUNT SECURITY
            ================================================== -->

            <div
                id="accountTab"
                class="hidden"
            >

                <div class="grid lg:grid-cols-2 gap-6">


                    <!-- UPDATE PROFILE -->

                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">

                        <h3 class="text-xl font-bold mb-2">

                            <i class="fa-solid fa-user-pen text-cyan-400"></i>

                            Update Profile

                        </h3>

                        <p class="text-sm text-slate-400 mb-6">
                            Update your account name and email address.
                        </p>


                        <label class="block text-sm text-slate-400 mb-2">
                            Name
                        </label>

                        <input
                            id="updateName"
                            type="text"
                            class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 mb-4"
                            placeholder="Your name"
                        >


                        <label class="block text-sm text-slate-400 mb-2">
                            Email
                        </label>

                        <input
                            id="updateEmail"
                            type="email"
                            class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 mb-5"
                            placeholder="your@email.com"
                        >


                        <button
                            onclick="updateProfile()"
                            class="w-full bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold py-3 rounded-lg"
                        >
                            <i class="fa-solid fa-floppy-disk"></i>
                            Update Profile
                        </button>


                        <div
                            id="profileUpdateMessage"
                            class="hidden mt-5 p-4 rounded-lg text-sm"
                        ></div>

                    </div>


                    <!-- CHANGE PASSWORD -->

                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">

                        <h3 class="text-xl font-bold mb-2">

                            <i class="fa-solid fa-lock text-orange-400"></i>

                            Change Password

                        </h3>

                        <p class="text-sm text-slate-400 mb-6">
                            Verify your current password before changing it.
                        </p>


                        <label class="block text-sm text-slate-400 mb-2">
                            Current Password
                        </label>

                        <input
                            id="currentPassword"
                            type="password"
                            class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 mb-4"
                            placeholder="Current password"
                        >


                        <label class="block text-sm text-slate-400 mb-2">
                            New Password
                        </label>

                        <input
                            id="newPassword"
                            type="password"
                            class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 mb-4"
                            placeholder="New password"
                        >


                        <label class="block text-sm text-slate-400 mb-2">
                            Confirm New Password
                        </label>

                        <input
                            id="confirmPassword"
                            type="password"
                            class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 mb-5"
                            placeholder="Confirm new password"
                        >


                        <button
                            onclick="changePassword()"
                            class="w-full bg-orange-500 hover:bg-orange-400 py-3 rounded-lg font-bold"
                        >
                            <i class="fa-solid fa-key"></i>
                            Change Password
                        </button>


                        <div
                            id="passwordMessage"
                            class="hidden mt-5 p-4 rounded-lg text-sm"
                        ></div>

                    </div>


                    <!-- CURRENT SESSION -->

                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">

                        <h3 class="text-xl font-bold mb-2">

                            <i class="fa-solid fa-desktop text-green-400"></i>

                            Current Session Information

                        </h3>

                        <p class="text-sm text-slate-400 mb-6">
                            Information about your current authenticated session.
                        </p>


                        <div class="space-y-4">

                            <div class="flex justify-between gap-4 border-b border-slate-800 pb-3">

                                <span class="text-slate-400">
                                    Token
                                </span>

                                <span
                                    id="sessionToken"
                                    class="font-mono text-xs text-cyan-300 text-right break-all"
                                >
                                    -
                                </span>

                            </div>


                            <div class="flex justify-between gap-4 border-b border-slate-800 pb-3">

                                <span class="text-slate-400">
                                    IP Address
                                </span>

                                <span
                                    id="sessionIp"
                                    class="font-mono text-sm"
                                >
                                    -
                                </span>

                            </div>


                            <div class="flex justify-between gap-4 border-b border-slate-800 pb-3">

                                <span class="text-slate-400">
                                    Browser
                                </span>

                                <span
                                    id="sessionBrowser"
                                    class="text-sm text-right"
                                >
                                    -
                                </span>

                            </div>


                            <div class="flex justify-between gap-4">

                                <span class="text-slate-400">
                                    Device
                                </span>

                                <span
                                    id="sessionDevice"
                                    class="text-sm text-right"
                                >
                                    -
                                </span>

                            </div>

                        </div>


                        <button
                            onclick="loadSessionInformation()"
                            class="w-full mt-6 bg-slate-800 hover:bg-slate-700 py-3 rounded-lg font-semibold"
                        >
                            <i class="fa-solid fa-refresh"></i>
                            Refresh Session
                        </button>

                    </div>


                    <!-- LOGOUT ALL DEVICES -->

                    <div class="bg-slate-900 border border-red-500/20 rounded-2xl p-6">

                        <h3 class="text-xl font-bold mb-2 text-red-300">

                            <i class="fa-solid fa-right-from-bracket"></i>

                            Logout From All Devices

                        </h3>

                        <p class="text-sm text-slate-400 mb-6">
                            Revoke all Sanctum API tokens associated with your account.
                        </p>


                        <div class="bg-red-500/10 border border-red-500/20 rounded-xl p-4 mb-5">

                            <div class="text-sm text-red-300">

                                <i class="fa-solid fa-triangle-exclamation"></i>

                                This will invalidate authenticated sessions on other devices.

                            </div>

                        </div>


                        <button
                            onclick="logoutAllDevices()"
                            class="w-full bg-red-500 hover:bg-red-400 py-3 rounded-lg font-bold"
                        >
                            <i class="fa-solid fa-power-off"></i>
                            Logout From All Devices
                        </button>


                        <div
                            id="logoutAllMessage"
                            class="hidden mt-5 p-4 rounded-lg text-sm"
                        ></div>

                    </div>


                    <!-- PASSWORD SECURITY LOG -->

                    <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-2xl p-6">

                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-5">

                            <div>

                                <h3 class="text-xl font-bold">

                                    <i class="fa-solid fa-shield text-purple-400"></i>

                                    Password Change Security Log

                                </h3>

                                <p class="text-sm text-slate-400">
                                    Recent password and account-security changes.
                                </p>

                            </div>


                            <button
                                onclick="loadActivities(1, 'password')"
                                class="bg-slate-800 hover:bg-slate-700 px-4 py-2 rounded-lg"
                            >
                                <i class="fa-solid fa-refresh"></i>
                                Refresh Log
                            </button>

                        </div>


                        <div class="overflow-x-auto">

                            <table class="w-full text-sm">

                                <thead>

                                <tr class="border-b border-slate-800 text-left text-slate-400">

                                    <th class="p-3">
                                        Event
                                    </th>

                                    <th class="p-3">
                                        Status
                                    </th>

                                    <th class="p-3">
                                        IP
                                    </th>

                                    <th class="p-3">
                                        Date
                                    </th>

                                </tr>

                                </thead>

                                <tbody id="passwordSecurityLog"></tbody>

                            </table>

                        </div>

                    </div>

                </div>

            </div>

        </section>

    </main>

</div>


<!-- =========================================================
     JAVASCRIPT
========================================================= -->

<script>

let currentUserId = null;
let currentUserEmail = null;
let currentUserName = null;

let currentSecretKey = null;
let currentQrUrl = null;
let currentToken = null;

let currentRecoveryCodes = [];

let currentActivityPage = 1;

let activitySearchTimer = null;

let autoRefreshTimer = null;

let allTokens = [];

const csrfToken = document
    .querySelector('meta[name="csrf-token"]')
    .getAttribute('content');


/* =========================================================
   API HELPER
========================================================= */

async function apiFetch(url, options = {}) {

    options.headers = {

        'Content-Type': 'application/json',

        'Accept': 'application/json',

        'Authorization': currentToken
            ? `Bearer ${currentToken}`
            : '',

        'X-CSRF-TOKEN': csrfToken,

        ...(options.headers || {})

    };


    const response = await fetch(url, options);


    const data = await response
        .json()
        .catch(() => ({
            success: false,
            message: 'Invalid server response.'
        }));


    return {
        response,
        data
    };

}


/* =========================================================
   MESSAGE
========================================================= */

function showMessage(
    elementId,
    message,
    type = 'error'
) {

    const element =
        document.getElementById(elementId);


    if (!element) {
        return;
    }


    element.classList.remove(
        'hidden',
        'bg-red-500/10',
        'border-red-500/30',
        'text-red-300',
        'bg-green-500/10',
        'border-green-500/30',
        'text-green-300'
    );


    if (type === 'success') {

        element.classList.add(
            'bg-green-500/10',
            'border',
            'border-green-500/30',
            'text-green-300'
        );

    } else {

        element.classList.add(
            'bg-red-500/10',
            'border',
            'border-red-500/30',
            'text-red-300'
        );

    }


    element.innerText = message;

}


/* =========================================================
   LOGIN
========================================================= */

document
    .getElementById('loginForm')
    .addEventListener(
        'submit',
        handleLoginSubmit
    );


async function handleLoginSubmit(event) {

    event.preventDefault();


    const email =
        document
            .getElementById('loginEmail')
            .value;


    const password =
        document
            .getElementById('loginPassword')
            .value;


    const { response, data } =
        await apiFetch(
            '/api/login',
            {
                method: 'POST',

                body: JSON.stringify({
                    email,
                    password
                })
            }
        );


    if (!response.ok || !data.success) {

        showMessage(
            'loginMessage',
            data.message || 'Login failed.'
        );

        return;
    }


    currentUserId = data.user_id;

    currentUserEmail = data.user_email;

    currentUserName = data.user_name;

    currentSecretKey =
        data.manual_key || null;

    currentQrUrl =
        data.qr_code || null;


    document
        .getElementById('loginSection')
        .classList.add('hidden');


    document
        .getElementById('twoFactorSection')
        .classList.remove('hidden');


    if (
        !data.google_2fa_enabled &&
        data.qr_code
    ) {

        document
            .getElementById('setupBox')
            .classList.remove('hidden');


        renderQrCode(data.qr_code);


        document
            .getElementById('manualKey')
            .innerText =
            data.manual_key || '';

    } else {

        document
            .getElementById('setupBox')
            .classList.add('hidden');

    }

}


/* =========================================================
   QR
========================================================= */

function renderQrCode(url) {

    const container =
        document.getElementById('qrCode');


    container.innerHTML = '';


    new QRCode(
        container,
        {
            text: url,
            width: 200,
            height: 200
        }
    );

}


/* =========================================================
   GOOGLE OTP
========================================================= */

async function submitGoogleOtp() {

    const otp =
        document
            .getElementById('otpInput')
            .value
            .trim();


    if (!/^\d{6}$/.test(otp)) {

        showMessage(
            'twoFactorMessage',
            'Please enter a valid 6-digit OTP.'
        );

        return;
    }


    const { response, data } =
        await apiFetch(
            '/api/verify-google-otp',
            {
                method: 'POST',

                body: JSON.stringify({
                    user_id: currentUserId,
                    otp
                })
            }
        );


    if (!response.ok || !data.success) {

        showMessage(
            'twoFactorMessage',
            data.message || 'Invalid OTP.'
        );

        return;
    }


    onAuthSuccess(data);

}


/* =========================================================
   RECOVERY CODE
========================================================= */

async function submitRecoveryCode() {

    const recoveryCode =
        document
            .getElementById('recoveryInput')
            .value
            .trim();


    if (!recoveryCode) {

        showMessage(
            'twoFactorMessage',
            'Please enter a recovery code.'
        );

        return;
    }


    const { response, data } =
        await apiFetch(
            '/api/verify-recovery-code',
            {
                method: 'POST',

                body: JSON.stringify({
                    user_id: currentUserId,
                    recovery_code: recoveryCode
                })
            }
        );


    if (!response.ok || !data.success) {

        showMessage(
            'twoFactorMessage',
            data.message || 'Invalid recovery code.'
        );

        return;
    }


    onAuthSuccess(data);

}


/* =========================================================
   AUTH SUCCESS
========================================================= */

function onAuthSuccess(data) {

    currentToken = data.token;


    currentRecoveryCodes =
        data.user?.recovery_codes || [];


    document
        .getElementById('twoFactorSection')
        .classList.add('hidden');


    document
        .getElementById('dashboardSection')
        .classList.remove('hidden');


    document
        .getElementById('headerUser')
        .classList.remove('hidden');


    document
        .getElementById('headerUserName')
        .innerText =
        currentUserName;


    document
        .getElementById('headerUserEmail')
        .innerText =
        currentUserEmail;


    loadDashboard();

    startAutoRefresh();

}


/* =========================================================
   DASHBOARD
========================================================= */

async function loadDashboard() {

    await loadProfile();

    await loadStatistics();

    await loadActivitySummary();

    await loadActivities(1);

    await loadTokens();

    await loadSessionInformation();

    await loadPasswordSecurityLog();

    updateLastRefreshTime();

}


/* =========================================================
   REFRESH DASHBOARD
========================================================= */

async function refreshSecurityDashboard() {

    if (!currentToken) {
        return;
    }


    await loadDashboard();

}


/* =========================================================
   AUTO REFRESH
========================================================= */

function startAutoRefresh() {

    stopAutoRefresh();


    autoRefreshTimer =
        setInterval(
            async function () {

                if (!currentToken) {
                    return;
                }


                await loadStatistics();

                await loadActivitySummary();

                await loadActivities(
                    currentActivityPage
                );

                await loadTokens();

                await loadSessionInformation();

                updateLastRefreshTime();

            },
            30000
        );

}


function stopAutoRefresh() {

    if (autoRefreshTimer) {

        clearInterval(autoRefreshTimer);

        autoRefreshTimer = null;

    }

}


function updateLastRefreshTime() {

    const element =
        document.getElementById(
            'lastRefreshTime'
        );


    if (!element) {
        return;
    }


    element.innerText =
        new Date().toLocaleTimeString();

}


/* =========================================================
   PROFILE
========================================================= */

async function loadProfile() {

    const { data } =
        await apiFetch('/api/profile');


    if (!data.success) {
        return;
    }


    const user = data.user;


    document
        .getElementById('profileName')
        .innerText =
        user.name || '-';


    document
        .getElementById('profileEmail')
        .innerText =
        user.email || '-';


    document
        .getElementById('updateName')
        .value =
        user.name || '';


    document
        .getElementById('updateEmail')
        .value =
        user.email || '';


    document
        .getElementById('profileRecoveryCount')
        .innerText =
        user.recovery_stats?.remaining ?? 0;


    const status =
        document.getElementById('profile2FA');


    if (user.google_2fa_enabled) {

        status.innerText = 'Enabled';

        status.className =
            'inline-flex mt-1 px-3 py-1 rounded-full text-xs font-semibold bg-green-500/10 text-green-300';


        document
            .getElementById('settings2FAStatus')
            .innerText =
            'Enabled';

    } else {

        status.innerText = 'Disabled';

        status.className =
            'inline-flex mt-1 px-3 py-1 rounded-full text-xs font-semibold bg-red-500/10 text-red-300';


        document
            .getElementById('settings2FAStatus')
            .innerText =
            'Disabled';

    }


    currentUserName =
        user.name || currentUserName;


    currentUserEmail =
        user.email || currentUserEmail;


    document
        .getElementById('headerUserName')
        .innerText =
        currentUserName;


    document
        .getElementById('headerUserEmail')
        .innerText =
        currentUserEmail;

}


/* =========================================================
   UPDATE PROFILE
========================================================= */

async function updateProfile() {

    const name =
        document
            .getElementById('updateName')
            .value
            .trim();


    const email =
        document
            .getElementById('updateEmail')
            .value
            .trim();


    if (!name || !email) {

        showMessage(
            'profileUpdateMessage',
            'Name and email are required.'
        );

        return;
    }


    const { response, data } =
        await apiFetch(
            '/api/profile/update',
            {
                method: 'PUT',

                body: JSON.stringify({
                    name,
                    email
                })
            }
        );


    if (!response.ok || !data.success) {

        showMessage(
            'profileUpdateMessage',
            data.message ||
            'Profile update failed.'
        );

        return;
    }


    showMessage(
        'profileUpdateMessage',
        data.message ||
        'Profile updated successfully.',
        'success'
    );


    await loadProfile();

    await loadActivities(1);

}


/* =========================================================
   CHANGE PASSWORD
========================================================= */

async function changePassword() {

    const currentPassword =
        document
            .getElementById('currentPassword')
            .value;


    const newPassword =
        document
            .getElementById('newPassword')
            .value;


    const confirmPassword =
        document
            .getElementById('confirmPassword')
            .value;


    if (
        !currentPassword ||
        !newPassword ||
        !confirmPassword
    ) {

        showMessage(
            'passwordMessage',
            'All password fields are required.'
        );

        return;
    }


    if (newPassword !== confirmPassword) {

        showMessage(
            'passwordMessage',
            'New password and confirmation do not match.'
        );

        return;
    }


    const { response, data } =
        await apiFetch(
            '/api/security/change-password',
            {
                method: 'POST',

                body: JSON.stringify({
                    current_password: currentPassword,
                    new_password: newPassword,
                    new_password_confirmation:
                        confirmPassword
                })
            }
        );


    if (!response.ok || !data.success) {

        showMessage(
            'passwordMessage',
            data.message ||
            'Password change failed.'
        );

        return;
    }


    showMessage(
        'passwordMessage',
        data.message ||
        'Password changed successfully.',
        'success'
    );


    document
        .getElementById('currentPassword')
        .value = '';


    document
        .getElementById('newPassword')
        .value = '';


    document
        .getElementById('confirmPassword')
        .value = '';


    await loadActivities(1);

    await loadPasswordSecurityLog();

}


/* =========================================================
   PASSWORD SECURITY LOG
========================================================= */

async function loadPasswordSecurityLog() {

    if (!currentToken) {
        return;
    }


    const params =
        new URLSearchParams();


    params.append(
        'search',
        'password'
    );


    params.append(
        'per_page',
        '10'
    );


    const { data } =
        await apiFetch(
            `/api/security/activities?${params.toString()}`
        );


    const tbody =
        document.getElementById(
            'passwordSecurityLog'
        );


    if (!tbody) {
        return;
    }


    tbody.innerHTML = '';


    if (
        !data.success ||
        !data.activities?.data?.length
    ) {

        tbody.innerHTML = `
            <tr>
                <td
                    colspan="4"
                    class="p-6 text-center text-slate-500"
                >
                    No password security events found.
                </td>
            </tr>
        `;

        return;
    }


    data.activities.data
        .filter(activity =>
            [
                'password_changed',
                'profile_updated',
                'logout_all_devices'
            ].includes(activity.event)
        )
        .forEach(activity => {

            const statusClass =
                activity.status === 'success'
                    ? 'bg-green-500/10 text-green-300'
                    : 'bg-red-500/10 text-red-300';


            tbody.innerHTML += `
                <tr class="border-b border-slate-800">

                    <td class="p-3 font-medium">
                        ${escapeHtml(
                            formatEvent(activity.event)
                        )}
                    </td>

                    <td class="p-3">
                        <span
                            class="px-2 py-1 rounded-full text-xs ${statusClass}"
                        >
                            ${escapeHtml(
                                activity.status || '-'
                            )}
                        </span>
                    </td>

                    <td class="p-3 font-mono text-xs text-slate-400">
                        ${escapeHtml(
                            activity.ip_address || '-'
                        )}
                    </td>

                    <td class="p-3 text-slate-400 whitespace-nowrap">
                        ${escapeHtml(
                            activity.created_at || '-'
                        )}
                    </td>

                </tr>
            `;

        });

}


/* =========================================================
   STATISTICS
========================================================= */

async function loadStatistics() {

    const { data } =
        await apiFetch(
            '/api/security/statistics'
        );


    if (!data.success) {
        return;
    }


    const stats =
        data.statistics;


    document
        .getElementById('statTotalEvents')
        .innerText =
        stats.total_events ?? 0;


    document
        .getElementById('statSuccessful')
        .innerText =
        stats.successful_logins ?? 0;


    document
        .getElementById('statFailed')
        .innerText =
        stats.failed_logins ?? 0;


    document
        .getElementById('statToday')
        .innerText =
        stats.today_events ?? 0;


    document
        .getElementById('statOtp')
        .innerText =
        stats.otp_verifications ?? 0;


    document
        .getElementById('statRecovery')
        .innerText =
        stats.recovery_code_logins ?? 0;


    document
        .getElementById('statSuccessEvents')
        .innerText =
        stats.successful_events ?? 0;

}


/* =========================================================
   ACTIVITY SUMMARY
========================================================= */

async function loadActivitySummary() {

    const { data } =
        await apiFetch(
            '/api/security/activity-summary'
        );


    if (!data.success) {
        return;
    }


    const summary =
        data.summary || {};


    document
        .getElementById('summaryLogin')
        .innerText =
        summary.login ?? 0;


    document
        .getElementById('summaryOtp')
        .innerText =
        summary.otp ?? 0;


    document
        .getElementById('summaryRecovery')
        .innerText =
        summary.recovery ?? 0;


    document
        .getElementById('summaryLogout')
        .innerText =
        summary.logout ?? 0;


    document
        .getElementById('summaryToken')
        .innerText =
        summary.token ?? 0;


    document
        .getElementById('summaryTwoFa')
        .innerText =
        summary.two_fa ?? 0;

}


/* =========================================================
   ACTIVITIES
========================================================= */

async function loadActivities(
    page = 1,
    mode = 'normal'
) {

    if (!currentToken) {
        return;
    }


    currentActivityPage = page;


    const search =
        document
            .getElementById('activitySearch')
            .value;


    const event =
        document
            .getElementById('activityEvent')
            .value;


    const status =
        document
            .getElementById('activityStatus')
            .value;


    const date =
        document
            .getElementById('activityDate')
            .value;


    const params =
        new URLSearchParams();


    if (search) {
        params.append('search', search);
    }


    if (event) {
        params.append('event', event);
    }


    if (status) {
        params.append('status', status);
    }


    if (date) {
        params.append('date', date);
    }


    params.append(
        'page',
        page
    );


    params.append(
        'per_page',
        10
    );


    const { data } =
        await apiFetch(
            `/api/security/activities?${params.toString()}`
        );


    if (!data.success) {
        return;
    }


    const tbody =
        document.getElementById(
            'activityTable'
        );


    tbody.innerHTML = '';


    const activities =
        data.activities?.data || [];


    if (activities.length === 0) {

        tbody.innerHTML = `
            <tr>
                <td
                    colspan="5"
                    class="p-6 text-center text-slate-500"
                >
                    No security activity found.
                </td>
            </tr>
        `;


        renderActivityPagination(
            data.activities
        );


        return;
    }


    activities.forEach(
        activity => {

            const statusClass =
                activity.status === 'success'
                    ? 'bg-green-500/10 text-green-300'
                    : 'bg-red-500/10 text-red-300';


            tbody.innerHTML += `
                <tr class="border-b border-slate-800 hover:bg-slate-800/50">

                    <td class="p-3 font-medium">
                        ${escapeHtml(
                            formatEvent(activity.event)
                        )}
                    </td>

                    <td class="p-3">

                        <span
                            class="px-2 py-1 rounded-full text-xs ${statusClass}"
                        >
                            ${escapeHtml(
                                activity.status || '-'
                            )}
                        </span>

                    </td>

                    <td class="p-3 text-slate-400">
                        ${escapeHtml(
                            activity.description || ''
                        )}
                    </td>

                    <td class="p-3 font-mono text-xs text-slate-400">
                        ${escapeHtml(
                            activity.ip_address || '-'
                        )}
                    </td>

                    <td class="p-3 text-slate-400 whitespace-nowrap">
                        ${escapeHtml(
                            activity.created_at || '-'
                        )}
                    </td>

                </tr>
            `;

        }
    );


    renderActivityPagination(
        data.activities
    );

}


/* =========================================================
   ACTIVITY PAGINATION
========================================================= */

function renderActivityPagination(meta) {

    const container =
        document.getElementById(
            'activityPagination'
        );


    container.innerHTML = '';


    if (!meta) {
        return;
    }


    const currentPage =
        Number(
            meta.current_page || 1
        );


    const lastPage =
        Number(
            meta.last_page || 1
        );


    const previousButton =
        document.createElement('button');


    previousButton.innerHTML =
        '<i class="fa-solid fa-chevron-left"></i>';


    previousButton.title =
        'Previous activity page';


    previousButton.disabled =
        currentPage <= 1;


    previousButton.className =
        currentPage <= 1
            ? 'px-4 py-2 rounded-lg bg-slate-800 text-slate-600 cursor-not-allowed'
            : 'px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-white';


    previousButton.onclick =
        function () {

            if (currentPage > 1) {

                loadActivities(
                    currentPage - 1
                );

            }

        };


    container.appendChild(
        previousButton
    );


    const startPage =
        Math.max(
            1,
            currentPage - 2
        );


    const endPage =
        Math.min(
            lastPage,
            currentPage + 2
        );


    for (
        let page = startPage;
        page <= endPage;
        page++
    ) {

        const button =
            document.createElement('button');


        button.innerText = page;


        button.className =
            page === currentPage
                ? 'px-4 py-2 rounded-lg bg-cyan-500 text-slate-950 font-bold'
                : 'px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-white';


        button.onclick =
            function () {
                loadActivities(page);
            };


        container.appendChild(button);

    }


    const nextButton =
        document.createElement('button');


    nextButton.innerHTML =
        '<i class="fa-solid fa-chevron-right"></i>';


    nextButton.title =
        'Next activity page';


    nextButton.disabled =
        currentPage >= lastPage;


    nextButton.className =
        currentPage >= lastPage
            ? 'px-4 py-2 rounded-lg bg-slate-800 text-slate-600 cursor-not-allowed'
            : 'px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-white';


    nextButton.onclick =
        function () {

            if (currentPage < lastPage) {

                loadActivities(
                    currentPage + 1
                );

            }

        };


    container.appendChild(
        nextButton
    );

}


/* =========================================================
   ACTIVITY SEARCH DEBOUNCE
========================================================= */

function debouncedActivitySearch() {

    clearTimeout(
        activitySearchTimer
    );


    activitySearchTimer =
        setTimeout(
            function () {

                loadActivities(1);

            },
            400
        );

}


/* =========================================================
   CSV EXPORT
========================================================= */

async function exportSecurityActivity() {

    const search =
        document
            .getElementById('activitySearch')
            .value;


    const event =
        document
            .getElementById('activityEvent')
            .value;


    const status =
        document
            .getElementById('activityStatus')
            .value;


    const date =
        document
            .getElementById('activityDate')
            .value;


    const params =
        new URLSearchParams();


    if (search) {
        params.append('search', search);
    }


    if (event) {
        params.append('event', event);
    }


    if (status) {
        params.append('status', status);
    }


    if (date) {
        params.append('date', date);
    }


    const url =
        `/api/security/activities/export?${params.toString()}`;


    try {

        const response =
            await fetch(
                url,
                {
                    headers: {
                        'Accept': 'text/csv',
                        'Authorization':
                            `Bearer ${currentToken}`,
                        'X-CSRF-TOKEN':
                            csrfToken
                    }
                }
            );


        if (!response.ok) {

            const data =
                await response
                    .json()
                    .catch(() => ({
                        message:
                            'CSV export failed.'
                    }));


            alert(
                data.message ||
                'CSV export failed.'
            );

            return;
        }


        const blob =
            await response.blob();


        const downloadUrl =
            window.URL.createObjectURL(
                blob
            );


        const link =
            document.createElement('a');


        link.href =
            downloadUrl;


        link.download =
            'security-activity.csv';


        document.body.appendChild(
            link
        );


        link.click();


        link.remove();


        window.URL.revokeObjectURL(
            downloadUrl
        );

    } catch (error) {

        alert(
            'Unable to export security activity.'
        );

    }

}


/* =========================================================
   TOKEN MANAGEMENT
========================================================= */

async function loadTokens() {

    const { data } =
        await apiFetch(
            '/api/tokens'
        );


    if (!data.success) {
        return;
    }


    allTokens =
        data.tokens || [];


    renderTokens(
        allTokens
    );

}


function renderTokens(tokens) {

    const tbody =
        document.getElementById(
            'tokenTable'
        );


    tbody.innerHTML = '';


    if (!tokens.length) {

        tbody.innerHTML = `
            <tr>
                <td
                    colspan="5"
                    class="p-6 text-center text-slate-500"
                >
                    No API tokens found.
                </td>
            </tr>
        `;

        return;
    }


    tokens.forEach(
        token => {

            const status =
                token.is_current

                    ? `
                        <span
                            class="px-2 py-1 rounded-full text-xs bg-cyan-500/10 text-cyan-300"
                        >
                            Current
                        </span>
                    `

                    : `
                        <span
                            class="px-2 py-1 rounded-full text-xs bg-slate-800 text-slate-400"
                        >
                            Active
                        </span>
                    `;


            const action =
                token.is_current

                    ? `
                        <span class="text-xs text-slate-500">
                            Current session
                        </span>
                    `

                    : `
                        <button
                            onclick="revokeToken(${token.id})"
                            class="text-red-400 hover:text-red-300 text-xs"
                        >
                            Revoke
                        </button>
                    `;


            tbody.innerHTML += `
                <tr class="border-b border-slate-800">

                    <td class="p-3 font-semibold">
                        ${escapeHtml(
                            token.name
                        )}
                    </td>

                    <td class="p-3 text-slate-400 text-xs">
                        ${escapeHtml(
                            token.created_at || '-'
                        )}
                    </td>

                    <td class="p-3 text-slate-400 text-xs">
                        ${escapeHtml(
                            token.last_used_at || 'Never'
                        )}
                    </td>

                    <td class="p-3">
                        ${status}
                    </td>

                    <td class="p-3">
                        ${action}
                    </td>

                </tr>
            `;

        }
    );

}


/* =========================================================
   TOKEN SEARCH
========================================================= */

function filterTokens() {

    const search =
        document
            .getElementById('tokenSearch')
            .value
            .toLowerCase()
            .trim();


    if (!search) {

        renderTokens(
            allTokens
        );

        return;
    }


    const filtered =
        allTokens.filter(
            token =>
                String(
                    token.name || ''
                )
                .toLowerCase()
                .includes(search)
        );


    renderTokens(
        filtered
    );

}


/* =========================================================
   CREATE TOKEN
========================================================= */

async function createApiToken() {

    const name =
        document
            .getElementById('newTokenName')
            .value
            .trim();


    if (!name) {

        alert(
            'Please enter a token name.'
        );

        return;
    }


    const { response, data } =
        await apiFetch(
            '/api/tokens',
            {
                method: 'POST',

                body: JSON.stringify({
                    name
                })
            }
        );


    if (!response.ok || !data.success) {

        alert(
            data.message ||
            'Token creation failed.'
        );

        return;
    }


    document
        .getElementById('newTokenResult')
        .classList.remove('hidden');


    document
        .getElementById('newTokenValue')
        .innerText =
        data.token;


    document
        .getElementById('newTokenName')
        .value = '';


    await loadTokens();

}


/* =========================================================
   REVOKE TOKEN
========================================================= */

async function revokeToken(tokenId) {

    if (
        !confirm(
            'Are you sure you want to revoke this API token?'
        )
    ) {
        return;
    }


    const { response, data } =
        await apiFetch(
            `/api/tokens/${tokenId}`,
            {
                method: 'DELETE'
            }
        );


    if (!response.ok || !data.success) {

        alert(
            data.message ||
            'Token revocation failed.'
        );

        return;
    }


    await loadTokens();

    await loadStatistics();

    await loadActivitySummary();

}


/* =========================================================
   REVOKE OTHER TOKENS
========================================================= */

async function revokeOtherTokens() {

    if (
        !confirm(
            'Revoke all other API tokens? Your current token will remain active.'
        )
    ) {
        return;
    }


    const { response, data } =
        await apiFetch(
            '/api/tokens/revoke-others',
            {
                method: 'DELETE'
            }
        );


    if (!response.ok || !data.success) {

        alert(
            data.message ||
            'Unable to revoke other tokens.'
        );

        return;
    }


    alert(
        data.message ||
        'Other tokens revoked successfully.'
    );


    await loadTokens();

    await loadActivitySummary();

}


/* =========================================================
   LOGOUT ALL DEVICES
========================================================= */

async function logoutAllDevices() {

    if (
        !confirm(
            'Logout from all devices? All other Sanctum sessions will be revoked.'
        )
    ) {
        return;
    }


    const { response, data } =
        await apiFetch(
            '/api/security/logout-all',
            {
                method: 'POST'
            }
        );


    if (!response.ok || !data.success) {

        showMessage(
            'logoutAllMessage',
            data.message ||
            'Unable to logout from all devices.'
        );

        return;
    }


    showMessage(
        'logoutAllMessage',
        data.message ||
        'All other devices have been logged out.',
        'success'
    );


    await loadTokens();

    await loadStatistics();

    await loadActivitySummary();

    await loadActivities(1);

}


/* =========================================================
   CURRENT SESSION
========================================================= */

async function loadSessionInformation() {

    const { data } =
        await apiFetch(
            '/api/security/session'
        );


    if (!data.success) {
        return;
    }


    const session =
        data.session || {};


    document
        .getElementById('sessionToken')
        .innerText =
        session.token ||
        'Current session';


    document
        .getElementById('sessionIp')
        .innerText =
        session.ip_address ||
        '-';


    document
        .getElementById('sessionBrowser')
        .innerText =
        session.browser ||
        '-';


    document
        .getElementById('sessionDevice')
        .innerText =
        session.device ||
        '-';

}


/* =========================================================
   RECOVERY CODES
========================================================= */

async function regenerateBackupCodes() {

    if (
        !confirm(
            'Generate new recovery codes? Existing recovery codes will no longer be usable.'
        )
    ) {
        return;
    }


    const { response, data } =
        await apiFetch(
            '/api/2fa/recovery-codes/regenerate',
            {
                method: 'POST'
            }
        );


    if (!response.ok || !data.success) {

        showSettingsMessage(
            data.message ||
            'Unable to generate recovery codes.'
        );

        return;
    }


    currentRecoveryCodes =
        data.recovery_codes || [];


    showSettingsMessage(
        'New recovery codes generated successfully.',
        'success'
    );


    if (data.plain_codes) {

        alert(
            'New recovery codes:\n\n' +
            data.plain_codes.join('\n')
        );

    }


    await loadProfile();

    await loadActivities(1);

}


/* =========================================================
   AUTHENTICATOR CHANGE
========================================================= */

async function startAuthenticatorChange() {

    const password =
        document
            .getElementById(
                'changeAuthPassword'
            )
            .value;


    const otp =
        document
            .getElementById(
                'changeAuthOtp'
            )
            .value;


    if (!password || !otp) {

        showSettingsMessage(
            'Current password and OTP are required.'
        );

        return;
    }


    const { response, data } =
        await apiFetch(
            '/api/security/2fa/new-authenticator',
            {
                method: 'POST',

                body: JSON.stringify({
                    password,
                    otp
                })
            }
        );


    if (!response.ok || !data.success) {

        showSettingsMessage(
            data.message ||
            'Unable to generate new authenticator.'
        );

        return;
    }


    currentSecretKey =
        data.manual_key;


    currentQrUrl =
        data.qr_code;


    showSettingsMessage(
        'New authenticator generated.',
        'success'
    );


    const newOtp =
        prompt(
            'Scan the new QR code using Google Authenticator.\n\n' +
            'Manual key:\n' +
            data.manual_key +
            '\n\n' +
            'Enter the NEW 6-digit OTP to confirm:'
        );


    if (!newOtp) {
        return;
    }


    const result =
        await apiFetch(
            '/api/security/2fa/confirm-authenticator',
            {
                method: 'POST',

                body: JSON.stringify({
                    otp: newOtp
                })
            }
        );


    if (
        !result.response.ok ||
        !result.data.success
    ) {

        showSettingsMessage(
            result.data.message ||
            'New authenticator confirmation failed.'
        );

        return;
    }


    showSettingsMessage(
        'New authenticator successfully enabled.',
        'success'
    );


    await loadProfile();

    await loadActivities(1);

}


/* =========================================================
   DISABLE 2FA
========================================================= */

async function disableTwoFactor() {

    if (
        !confirm(
            'Are you sure you want to disable 2FA?'
        )
    ) {
        return;
    }


    const password =
        document
            .getElementById(
                'disablePassword'
            )
            .value;


    const otp =
        document
            .getElementById(
                'disableOtp'
            )
            .value;


    if (!password || !otp) {

        showSettingsMessage(
            'Password and OTP are required.'
        );

        return;
    }


    const { response, data } =
        await apiFetch(
            '/api/security/2fa/disable',
            {
                method: 'POST',

                body: JSON.stringify({
                    password,
                    otp
                })
            }
        );


    if (!response.ok || !data.success) {

        showSettingsMessage(
            data.message ||
            'Unable to disable 2FA.'
        );

        return;
    }


    showSettingsMessage(
        '2FA has been disabled successfully.',
        'success'
    );


    await loadProfile();

    await loadStatistics();

    await loadActivitySummary();

    await loadActivities(1);

}


/* =========================================================
   DASHBOARD TABS
========================================================= */

function showDashboardTab(tab) {

    const tabs = [
        'overview',
        'activity',
        'tokens',
        'settings',
        'account'
    ];


    tabs.forEach(
        item => {

            document
                .getElementById(
                    `${item}Tab`
                )
                .classList.add('hidden');

        }
    );


    document
        .getElementById(
            `${tab}Tab`
        )
        .classList.remove('hidden');


    document
        .querySelectorAll(
            '.dashboard-tab'
        )
        .forEach(
            button => {

                button.classList.remove(
                    'bg-cyan-500',
                    'text-slate-950'
                );


                button.classList.add(
                    'bg-slate-800',
                    'text-slate-300'
                );

            }
        );


    const activeButton =
        document.querySelector(
            `.dashboard-tab[data-tab="${tab}"]`
        );


    if (activeButton) {

        activeButton.classList.remove(
            'bg-slate-800',
            'text-slate-300'
        );


        activeButton.classList.add(
            'bg-cyan-500',
            'text-slate-950'
        );

    }


    if (tab === 'activity') {

        loadActivities(
            currentActivityPage
        );

    }


    if (tab === 'tokens') {

        loadTokens();

    }


    if (tab === 'settings') {

        loadProfile();

    }


    if (tab === 'account') {

        loadProfile();

        loadSessionInformation();

        loadPasswordSecurityLog();

    }

}


/* =========================================================
   2FA LOGIN TABS
========================================================= */

function switch2FaMethod(method) {

    const authenticator =
        document.getElementById(
            'authenticatorBox'
        );


    const recovery =
        document.getElementById(
            'recoveryBox'
        );


    const authTab =
        document.getElementById(
            'authenticatorTab'
        );


    const recoveryTab =
        document.getElementById(
            'recoveryTab'
        );


    if (
        method ===
        'authenticator'
    ) {

        authenticator
            .classList
            .remove('hidden');


        recovery
            .classList
            .add('hidden');


        authTab
            .classList
            .add(
                'border-cyan-400',
                'text-cyan-400'
            );


        authTab
            .classList
            .remove(
                'border-transparent',
                'text-slate-400'
            );


        recoveryTab
            .classList
            .remove(
                'border-cyan-400',
                'text-cyan-400'
            );


        recoveryTab
            .classList
            .add(
                'border-transparent',
                'text-slate-400'
            );

    } else {

        authenticator
            .classList
            .add('hidden');


        recovery
            .classList
            .remove('hidden');


        recoveryTab
            .classList
            .add(
                'border-cyan-400',
                'text-cyan-400'
            );


        recoveryTab
            .classList
            .remove(
                'border-transparent',
                'text-slate-400'
            );


        authTab
            .classList
            .remove(
                'border-cyan-400',
                'text-cyan-400'
            );


        authTab
            .classList
            .add(
                'border-transparent',
                'text-slate-400'
            );

    }

}


/* =========================================================
   BACK TO LOGIN
========================================================= */

function backToLogin() {

    document
        .getElementById(
            'twoFactorSection'
        )
        .classList
        .add('hidden');


    document
        .getElementById(
            'loginSection'
        )
        .classList
        .remove('hidden');


    document
        .getElementById(
            'otpInput'
        )
        .value = '';


    document
        .getElementById(
            'recoveryInput'
        )
        .value = '';

}


/* =========================================================
   LOGOUT
========================================================= */

async function handleLogout() {

    if (!currentToken) {
        return;
    }


    await apiFetch(
        '/api/logout',
        {
            method: 'POST'
        }
    );


    stopAutoRefresh();


    currentToken = null;

    currentUserId = null;

    currentUserEmail = null;

    currentUserName = null;


    document
        .getElementById(
            'dashboardSection'
        )
        .classList
        .add('hidden');


    document
        .getElementById(
            'headerUser'
        )
        .classList
        .add('hidden');


    document
        .getElementById(
            'loginSection'
        )
        .classList
        .remove('hidden');


    document
        .getElementById(
            'loginPassword'
        )
        .value = '';


    alert(
        'Logged out successfully.'
    );

}


/* =========================================================
   SETTINGS MESSAGE
========================================================= */

function showSettingsMessage(
    message,
    type = 'error'
) {

    showMessage(
        'settingsMessage',
        message,
        type
    );

}


/* =========================================================
   COPY
========================================================= */

async function copyText(text) {

    try {

        await navigator
            .clipboard
            .writeText(text);


        alert(
            'Copied successfully.'
        );

    } catch (error) {

        alert(
            'Unable to copy.'
        );

    }

}


/* =========================================================
   HTML ESCAPE
========================================================= */

function escapeHtml(value) {

    const div =
        document.createElement(
            'div'
        );


    div.innerText =
        value ?? '';


    return div.innerHTML;

}


/* =========================================================
   FORMAT EVENT
========================================================= */

function formatEvent(event) {

    if (!event) {
        return '-';
    }


    return event
        .replaceAll('_', ' ')
        .replace(/\b\w/g, c =>
            c.toUpperCase()
        );

}


/* =========================================================
   OTP INPUT
========================================================= */

document
    .getElementById('otpInput')
    .addEventListener(
        'input',
        function () {

            this.value =
                this.value
                    .replace(/\D/g, '')
                    .slice(0, 6);


            if (
                this.value.length === 6
            ) {

                submitGoogleOtp();

            }

        }
    );


/* =========================================================
   INITIAL TAB
========================================================= */

showDashboardTab(
    'overview'
);

</script>

</body>

</html>