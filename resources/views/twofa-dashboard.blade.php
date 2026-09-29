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

                <div id="headerUser"
                     class="hidden text-right">

                    <div class="font-semibold" id="headerUserName"></div>

                    <div class="text-xs text-slate-400"
                         id="headerUserEmail"></div>

                </div>

            </div>

        </div>

    </header>


    <!-- =====================================================
         MAIN
    ====================================================== -->

    <main class="max-w-7xl mx-auto px-6 py-8">


        <!-- =================================================
             LOGIN SECTION
        ================================================== -->

        <section id="loginSection"
                 class="max-w-md mx-auto">

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


                <form id="loginForm"
                      class="space-y-5">

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
                        class="w-full bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold py-3 rounded-xl transition">

                        <i class="fa-solid fa-right-to-bracket"></i>

                        Login

                    </button>

                </form>

                <div id="loginMessage"
                     class="hidden mt-5 p-4 rounded-xl text-sm">
                </div>

            </div>

        </section>


        <!-- =================================================
             2FA SECTION
        ================================================== -->

        <section id="twoFactorSection"
                 class="hidden max-w-2xl mx-auto">

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

                <div id="setupBox"
                     class="hidden mb-8">

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
                                    class="break-all bg-slate-950 border border-slate-700 rounded-lg p-3 text-xs font-mono text-cyan-300">
                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- AUTHENTICATOR / RECOVERY TABS -->

                <div class="flex border-b border-slate-800 mb-6">

                    <button
                        id="authenticatorTab"
                        onclick="switch2FaMethod('authenticator')"
                        class="flex-1 py-3 border-b-2 border-cyan-400 text-cyan-400 font-semibold">

                        Google OTP

                    </button>

                    <button
                        id="recoveryTab"
                        onclick="switch2FaMethod('recovery')"
                        class="flex-1 py-3 border-b-2 border-transparent text-slate-400">

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
                        class="w-full mt-5 bg-purple-500 hover:bg-purple-400 text-white font-bold py-3 rounded-xl">

                        <i class="fa-solid fa-shield-check"></i>

                        Verify OTP

                    </button>

                </div>


                <!-- RECOVERY CODE -->

                <div id="recoveryBox"
                     class="hidden">

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
                        class="w-full mt-5 bg-orange-500 hover:bg-orange-400 text-white font-bold py-3 rounded-xl">

                        <i class="fa-solid fa-key"></i>

                        Use Recovery Code

                    </button>

                </div>


                <div id="twoFactorMessage"
                     class="hidden mt-5 p-4 rounded-xl text-sm">
                </div>


                <button
                    onclick="backToLogin()"
                    class="w-full mt-5 text-slate-400 hover:text-white text-sm">

                    ← Back to login

                </button>

            </div>

        </section>


        <!-- =================================================
             DASHBOARD
        ================================================== -->

        <section id="dashboardSection"
                 class="hidden">

            <!-- DASHBOARD HEADER -->

            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-8">

                <div>

                    <h2 class="text-3xl font-bold">
                        Security Dashboard
                    </h2>

                    <p class="text-slate-400 mt-1">
                        Monitor authentication, tokens and 2FA security.
                    </p>

                </div>

                <button
                    onclick="handleLogout()"
                    class="bg-red-500 hover:bg-red-400 px-5 py-3 rounded-xl font-semibold">

                    <i class="fa-solid fa-right-from-bracket"></i>
                    Logout

                </button>

            </div>


            <!-- NAVIGATION -->

            <div class="flex flex-wrap gap-2 mb-8">

                <button
                    onclick="showDashboardTab('overview')"
                    class="dashboard-tab active-tab px-5 py-3 rounded-xl bg-cyan-500 text-slate-950 font-semibold"
                    data-tab="overview">

                    <i class="fa-solid fa-chart-pie"></i>
                    Overview

                </button>

                <button
                    onclick="showDashboardTab('activity')"
                    class="dashboard-tab px-5 py-3 rounded-xl bg-slate-800 text-slate-300 font-semibold"
                    data-tab="activity">

                    <i class="fa-solid fa-clock-rotate-left"></i>
                    Security Activity

                </button>

                <button
                    onclick="showDashboardTab('tokens')"
                    class="dashboard-tab px-5 py-3 rounded-xl bg-slate-800 text-slate-300 font-semibold"
                    data-tab="tokens">

                    <i class="fa-solid fa-key"></i>
                    API Tokens

                </button>

                <button
                    onclick="showDashboardTab('settings')"
                    class="dashboard-tab px-5 py-3 rounded-xl bg-slate-800 text-slate-300 font-semibold"
                    data-tab="settings">

                    <i class="fa-solid fa-gear"></i>
                    Security Settings

                </button>

            </div>


            <!-- =================================================
                 OVERVIEW TAB
            ================================================== -->

            <div id="overviewTab">

                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">

                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">

                        <div class="text-slate-400 text-sm">
                            Total Events
                        </div>

                        <div
                            id="statTotalEvents"
                            class="text-3xl font-bold mt-2">
                            0
                        </div>

                    </div>


                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">

                        <div class="text-slate-400 text-sm">
                            Successful Logins
                        </div>

                        <div
                            id="statSuccessful"
                            class="text-3xl font-bold mt-2 text-green-400">
                            0
                        </div>

                    </div>


                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">

                        <div class="text-slate-400 text-sm">
                            Failed Events
                        </div>

                        <div
                            id="statFailed"
                            class="text-3xl font-bold mt-2 text-red-400">
                            0
                        </div>

                    </div>


                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">

                        <div class="text-slate-400 text-sm">
                            Today's Events
                        </div>

                        <div
                            id="statToday"
                            class="text-3xl font-bold mt-2 text-cyan-400">
                            0
                        </div>

                    </div>

                </div>


                <!-- PROFILE -->

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

                                <div id="profileName"
                                     class="font-semibold">
                                </div>
                            </div>

                            <div>
                                <div class="text-xs text-slate-500">
                                    Email
                                </div>

                                <div id="profileEmail"
                                     class="font-semibold">
                                </div>
                            </div>

                            <div>
                                <div class="text-xs text-slate-500">
                                    2FA Status
                                </div>

                                <span
                                    id="profile2FA"
                                    class="inline-flex mt-1 px-3 py-1 rounded-full text-xs font-semibold">
                                </span>
                            </div>

                            <div>
                                <div class="text-xs text-slate-500">
                                    Recovery Codes Remaining
                                </div>

                                <div
                                    id="profileRecoveryCount"
                                    class="font-semibold text-orange-400">
                                </div>
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
                                    class="text-green-400">
                                    0
                                </strong>
                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 SECURITY ACTIVITY TAB
            ================================================== -->

            <div id="activityTab"
                 class="hidden">

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

                        <button
                            onclick="loadActivities()"
                            class="bg-cyan-500 hover:bg-cyan-400 text-slate-950 px-4 py-2 rounded-lg font-semibold">

                            <i class="fa-solid fa-refresh"></i>
                            Refresh

                        </button>

                    </div>


                    <!-- FILTERS -->

                    <div class="grid md:grid-cols-4 gap-3 mb-6">

                        <input
                            id="activitySearch"
                            oninput="loadActivities()"
                            class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2"
                            placeholder="Search...">

                        <select
                            id="activityEvent"
                            onchange="loadActivities()"
                            class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2">

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
                            onchange="loadActivities()"
                            class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2">

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
                            onchange="loadActivities()"
                            type="date"
                            class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2">

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

                            <tbody id="activityTable">

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 TOKEN TAB
            ================================================== -->

            <div id="tokensTab"
                 class="hidden">

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
                            placeholder="e.g. Mobile App">

                        <button
                            onclick="createApiToken()"
                            class="w-full mt-4 bg-green-500 hover:bg-green-400 text-slate-950 font-bold py-3 rounded-lg">

                            Create Token

                        </button>

                        <div id="newTokenResult"
                             class="hidden mt-5">

                            <div class="text-xs text-slate-400 mb-2">
                                New token — copy it now:
                            </div>

                            <div class="bg-slate-950 border border-slate-700 rounded-lg p-3 break-all font-mono text-xs text-green-300"
                                 id="newTokenValue">
                            </div>

                            <button
                                onclick="copyText(document.getElementById('newTokenValue').innerText)"
                                class="mt-3 text-sm text-cyan-400">

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
                                class="bg-red-500/20 text-red-300 border border-red-500/30 px-4 py-2 rounded-lg">

                                Revoke Other Tokens

                            </button>

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

                                <tbody id="tokenTable">

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 SECURITY SETTINGS
            ================================================== -->

            <div id="settingsTab"
                 class="hidden">

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
                                class="text-xl font-bold mt-1">
                            </div>

                        </div>


                        <h4 class="font-semibold mb-3">
                            Regenerate Recovery Codes
                        </h4>

                        <p class="text-sm text-slate-400 mb-4">
                            Generate a fresh set of eight emergency recovery codes.
                        </p>

                        <button
                            onclick="regenerateBackupCodes()"
                            class="w-full bg-orange-500 hover:bg-orange-400 py-3 rounded-lg font-semibold">

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
                            placeholder="Current password">

                        <input
                            id="changeAuthOtp"
                            maxlength="6"
                            class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 mb-3"
                            placeholder="Current Google OTP">

                        <button
                            onclick="startAuthenticatorChange()"
                            class="w-full bg-purple-500 hover:bg-purple-400 py-3 rounded-lg font-semibold">

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
                            This is a sensitive security operation. Your current password and Google OTP are required.
                        </p>


                        <label class="block text-sm text-slate-400 mb-2">
                            Current Password
                        </label>

                        <input
                            id="disablePassword"
                            type="password"
                            class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 mb-4"
                            placeholder="Current password">


                        <label class="block text-sm text-slate-400 mb-2">
                            Google Authenticator OTP
                        </label>

                        <input
                            id="disableOtp"
                            maxlength="6"
                            class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 mb-5"
                            placeholder="6-digit OTP">


                        <button
                            onclick="disableTwoFactor()"
                            class="w-full bg-red-500 hover:bg-red-400 py-3 rounded-lg font-bold">

                            Disable 2FA

                        </button>


                        <div id="settingsMessage"
                             class="hidden mt-5 p-4 rounded-lg text-sm">
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

const csrfToken = document
    .querySelector('meta[name="csrf-token"]')
    .getAttribute('content');


/*
|--------------------------------------------------------------------------
| API HELPER
|--------------------------------------------------------------------------
*/

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

    const data = await response.json().catch(() => ({
        success: false,
        message: 'Invalid server response.'
    }));

    return {
        response,
        data
    };
}


/*
|--------------------------------------------------------------------------
| MESSAGE
|--------------------------------------------------------------------------
*/

function showMessage(elementId, message, type = 'error') {

    const element = document.getElementById(elementId);

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


/*
|--------------------------------------------------------------------------
| LOGIN
|--------------------------------------------------------------------------
*/

document
    .getElementById('loginForm')
    .addEventListener('submit', handleLoginSubmit);


async function handleLoginSubmit(event) {

    event.preventDefault();

    const email = document
        .getElementById('loginEmail')
        .value;

    const password = document
        .getElementById('loginPassword')
        .value;

    const { response, data } = await apiFetch('/api/login', {
        method: 'POST',
        body: JSON.stringify({
            email,
            password
        })
    });

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

    currentSecretKey = data.manual_key || null;
    currentQrUrl = data.qr_code || null;

    document
        .getElementById('loginSection')
        .classList.add('hidden');

    document
        .getElementById('twoFactorSection')
        .classList.remove('hidden');

    /*
    |--------------------------------------------------------------------------
    | Initial 2FA setup
    |--------------------------------------------------------------------------
    */

    if (!data.google_2fa_enabled && data.qr_code) {

        document
            .getElementById('setupBox')
            .classList.remove('hidden');

        renderQrCode(data.qr_code);

        document
            .getElementById('manualKey')
            .innerText = data.manual_key || '';

    } else {

        document
            .getElementById('setupBox')
            .classList.add('hidden');
    }
}


/*
|--------------------------------------------------------------------------
| QR CODE
|--------------------------------------------------------------------------
*/

function renderQrCode(url) {

    const container = document
        .getElementById('qrCode');

    container.innerHTML = '';

    new QRCode(container, {
        text: url,
        width: 200,
        height: 200
    });
}


/*
|--------------------------------------------------------------------------
| GOOGLE OTP
|--------------------------------------------------------------------------
*/

async function submitGoogleOtp() {

    const otp = document
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

    const { response, data } = await apiFetch(
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


/*
|--------------------------------------------------------------------------
| RECOVERY CODE
|--------------------------------------------------------------------------
*/

async function submitRecoveryCode() {

    const recoveryCode = document
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

    const { response, data } = await apiFetch(
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


/*
|--------------------------------------------------------------------------
| AUTH SUCCESS
|--------------------------------------------------------------------------
*/

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
        .innerText = currentUserName;

    document
        .getElementById('headerUserEmail')
        .innerText = currentUserEmail;

    loadDashboard();

}


/*
|--------------------------------------------------------------------------
| DASHBOARD
|--------------------------------------------------------------------------
*/

async function loadDashboard() {

    await loadProfile();

    await loadStatistics();

    await loadActivities();

    await loadTokens();
}


/*
|--------------------------------------------------------------------------
| PROFILE
|--------------------------------------------------------------------------
*/

async function loadProfile() {

    const { data } = await apiFetch('/api/profile');

    if (!data.success) {
        return;
    }

    const user = data.user;

    document
        .getElementById('profileName')
        .innerText = user.name;

    document
        .getElementById('profileEmail')
        .innerText = user.email;

    document
        .getElementById('profileRecoveryCount')
        .innerText =
        user.recovery_stats.remaining;

    const status = document
        .getElementById('profile2FA');

    if (user.google_2fa_enabled) {

        status.innerText = 'Enabled';

        status.className =
            'inline-flex mt-1 px-3 py-1 rounded-full text-xs font-semibold bg-green-500/10 text-green-300';

        document
            .getElementById('settings2FAStatus')
            .innerText = 'Enabled';

    } else {

        status.innerText = 'Disabled';

        status.className =
            'inline-flex mt-1 px-3 py-1 rounded-full text-xs font-semibold bg-red-500/10 text-red-300';

        document
            .getElementById('settings2FAStatus')
            .innerText = 'Disabled';
    }
}


/*
|--------------------------------------------------------------------------
| STATISTICS
|--------------------------------------------------------------------------
*/

async function loadStatistics() {

    const { data } = await apiFetch(
        '/api/security/statistics'
    );

    if (!data.success) {
        return;
    }

    const stats = data.statistics;

    document
        .getElementById('statTotalEvents')
        .innerText = stats.total_events;

    document
        .getElementById('statSuccessful')
        .innerText = stats.successful_logins;

    document
        .getElementById('statFailed')
        .innerText = stats.failed_logins;

    document
        .getElementById('statToday')
        .innerText = stats.today_events;

    document
        .getElementById('statOtp')
        .innerText = stats.otp_verifications;

    document
        .getElementById('statRecovery')
        .innerText = stats.recovery_code_logins;

    document
        .getElementById('statSuccessEvents')
        .innerText = stats.successful_events;
}


/*
|--------------------------------------------------------------------------
| ACTIVITIES
|--------------------------------------------------------------------------
*/

async function loadActivities() {

    if (!currentToken) {
        return;
    }

    const search = document
        .getElementById('activitySearch')
        .value;

    const event = document
        .getElementById('activityEvent')
        .value;

    const status = document
        .getElementById('activityStatus')
        .value;

    const date = document
        .getElementById('activityDate')
        .value;

    const params = new URLSearchParams();

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

    const { data } = await apiFetch(
        `/api/security/activities?${params.toString()}`
    );

    if (!data.success) {
        return;
    }

    const tbody =
        document.getElementById('activityTable');

    tbody.innerHTML = '';

    const activities =
        data.activities.data || [];

    if (activities.length === 0) {

        tbody.innerHTML = `
            <tr>
                <td colspan="5"
                    class="p-6 text-center text-slate-500">
                    No security activity found.
                </td>
            </tr>
        `;

        return;
    }

    activities.forEach(activity => {

        const statusClass =
            activity.status === 'success'
                ? 'bg-green-500/10 text-green-300'
                : 'bg-red-500/10 text-red-300';

        tbody.innerHTML += `
            <tr class="border-b border-slate-800 hover:bg-slate-800/50">

                <td class="p-3 font-medium">
                    ${formatEvent(activity.event)}
                </td>

                <td class="p-3">
                    <span class="px-2 py-1 rounded-full text-xs ${statusClass}">
                        ${escapeHtml(activity.status)}
                    </span>
                </td>

                <td class="p-3 text-slate-400">
                    ${escapeHtml(activity.description || '')}
                </td>

                <td class="p-3 font-mono text-xs text-slate-400">
                    ${escapeHtml(activity.ip_address || '-')}
                </td>

                <td class="p-3 text-slate-400 whitespace-nowrap">
                    ${escapeHtml(activity.created_at)}
                </td>

            </tr>
        `;
    });
}


function formatEvent(event) {

    return event
        .replaceAll('_', ' ')
        .replace(/\b\w/g, c => c.toUpperCase());
}


/*
|--------------------------------------------------------------------------
| TOKEN MANAGEMENT
|--------------------------------------------------------------------------
*/

async function loadTokens() {

    const { data } = await apiFetch('/api/tokens');

    if (!data.success) {
        return;
    }

    const tbody =
        document.getElementById('tokenTable');

    tbody.innerHTML = '';

    if (!data.tokens.length) {

        tbody.innerHTML = `
            <tr>
                <td colspan="5"
                    class="p-6 text-center text-slate-500">
                    No API tokens found.
                </td>
            </tr>
        `;

        return;
    }

    data.tokens.forEach(token => {

        const status = token.is_current
            ? `
                <span class="px-2 py-1 rounded-full text-xs bg-cyan-500/10 text-cyan-300">
                    Current
                </span>
              `
            : `
                <span class="px-2 py-1 rounded-full text-xs bg-slate-800 text-slate-400">
                    Active
                </span>
              `;

        const action = token.is_current
            ? `
                <span class="text-xs text-slate-500">
                    Current session
                </span>
              `
            : `
                <button
                    onclick="revokeToken(${token.id})"
                    class="text-red-400 hover:text-red-300 text-xs">
                    Revoke
                </button>
              `;

        tbody.innerHTML += `
            <tr class="border-b border-slate-800">

                <td class="p-3 font-semibold">
                    ${escapeHtml(token.name)}
                </td>

                <td class="p-3 text-slate-400 text-xs">
                    ${escapeHtml(token.created_at || '-')}
                </td>

                <td class="p-3 text-slate-400 text-xs">
                    ${escapeHtml(token.last_used_at || 'Never')}
                </td>

                <td class="p-3">
                    ${status}
                </td>

                <td class="p-3">
                    ${action}
                </td>

            </tr>
        `;
    });
}


async function createApiToken() {

    const name =
        document.getElementById('newTokenName')
            .value.trim();

    if (!name) {

        alert('Please enter a token name.');

        return;
    }

    const { response, data } = await apiFetch(
        '/api/tokens',
        {
            method: 'POST',
            body: JSON.stringify({
                name
            })
        }
    );

    if (!response.ok || !data.success) {

        alert(data.message || 'Token creation failed.');

        return;
    }

    document
        .getElementById('newTokenResult')
        .classList.remove('hidden');

    document
        .getElementById('newTokenValue')
        .innerText = data.token;

    document
        .getElementById('newTokenName')
        .value = '';

    await loadTokens();
}


async function revokeToken(tokenId) {

    if (!confirm(
        'Are you sure you want to revoke this API token?'
    )) {
        return;
    }

    const { response, data } = await apiFetch(
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
}


async function revokeOtherTokens() {

    if (!confirm(
        'Revoke all other API tokens? Your current token will remain active.'
    )) {
        return;
    }

    const { response, data } = await apiFetch(
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

    alert(data.message);

    await loadTokens();
}


/*
|--------------------------------------------------------------------------
| RECOVERY CODES
|--------------------------------------------------------------------------
*/

async function regenerateBackupCodes() {

    if (!confirm(
        'Generate new recovery codes? Existing recovery codes will no longer be usable.'
    )) {
        return;
    }

    const { response, data } = await apiFetch(
        '/api/2fa/recovery-codes/regenerate',
        {
            method: 'POST'
        }
    );

    if (!response.ok || !data.success) {

        showSettingsMessage(
            data.message || 'Unable to generate recovery codes.'
        );

        return;
    }

    currentRecoveryCodes =
        data.recovery_codes || [];

    showSettingsMessage(
        'New recovery codes generated successfully.',
        'success'
    );

    alert(
        'New recovery codes:\n\n' +
        data.plain_codes.join('\n')
    );

    await loadProfile();

    await loadActivities();
}


/*
|--------------------------------------------------------------------------
| START AUTHENTICATOR CHANGE
|--------------------------------------------------------------------------
*/

async function startAuthenticatorChange() {

    const password =
        document.getElementById(
            'changeAuthPassword'
        ).value;

    const otp =
        document.getElementById(
            'changeAuthOtp'
        ).value;

    if (!password || !otp) {

        showSettingsMessage(
            'Current password and OTP are required.'
        );

        return;
    }

    const { response, data } = await apiFetch(
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

    currentSecretKey = data.manual_key;
    currentQrUrl = data.qr_code;

    showSettingsMessage(
        'New authenticator generated. Verify the new OTP below.',
        'success'
    );

    /*
    |--------------------------------------------------------------------------
    | Show confirmation dialog
    |--------------------------------------------------------------------------
    */

    const newOtp = prompt(
        'Scan the new QR code using Google Authenticator.\n\n' +
        'Manual key:\n' +
        data.manual_key +
        '\n\n' +
        'Enter the NEW 6-digit OTP to confirm:'
    );

    if (!newOtp) {
        return;
    }

    const result = await apiFetch(
        '/api/security/2fa/confirm-authenticator',
        {
            method: 'POST',
            body: JSON.stringify({
                otp: newOtp
            })
        }
    );

    if (!result.response.ok || !result.data.success) {

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

    await loadActivities();
}


/*
|--------------------------------------------------------------------------
| DISABLE 2FA
|--------------------------------------------------------------------------
*/

async function disableTwoFactor() {

    if (!confirm(
        'Are you sure you want to disable 2FA?'
    )) {
        return;
    }

    const password =
        document.getElementById(
            'disablePassword'
        ).value;

    const otp =
        document.getElementById(
            'disableOtp'
        ).value;

    if (!password || !otp) {

        showSettingsMessage(
            'Password and OTP are required.'
        );

        return;
    }

    const { response, data } = await apiFetch(
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

    await loadActivities();
}


/*
|--------------------------------------------------------------------------
| DASHBOARD TABS
|--------------------------------------------------------------------------
*/

function showDashboardTab(tab) {

    const tabs = [
        'overview',
        'activity',
        'tokens',
        'settings'
    ];

    tabs.forEach(item => {

        document
            .getElementById(`${item}Tab`)
            .classList.add('hidden');

    });

    document
        .getElementById(`${tab}Tab`)
        .classList.remove('hidden');


    document
        .querySelectorAll('.dashboard-tab')
        .forEach(button => {

            button.classList.remove(
                'bg-cyan-500',
                'text-slate-950'
            );

            button.classList.add(
                'bg-slate-800',
                'text-slate-300'
            );

        });

    const activeButton =
        document.querySelector(
            `.dashboard-tab[data-tab="${tab}"]`
        );

    activeButton.classList.remove(
        'bg-slate-800',
        'text-slate-300'
    );

    activeButton.classList.add(
        'bg-cyan-500',
        'text-slate-950'
    );


    if (tab === 'activity') {
        loadActivities();
    }

    if (tab === 'tokens') {
        loadTokens();
    }

    if (tab === 'settings') {
        loadProfile();
    }
}


/*
|--------------------------------------------------------------------------
| 2FA LOGIN TABS
|--------------------------------------------------------------------------
*/

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

    if (method === 'authenticator') {

        authenticator.classList.remove('hidden');
        recovery.classList.add('hidden');

        authTab.classList.add(
            'border-cyan-400',
            'text-cyan-400'
        );

        authTab.classList.remove(
            'border-transparent',
            'text-slate-400'
        );

        recoveryTab.classList.remove(
            'border-cyan-400',
            'text-cyan-400'
        );

        recoveryTab.classList.add(
            'border-transparent',
            'text-slate-400'
        );

    } else {

        authenticator.classList.add('hidden');
        recovery.classList.remove('hidden');

        recoveryTab.classList.add(
            'border-cyan-400',
            'text-cyan-400'
        );

        recoveryTab.classList.remove(
            'border-transparent',
            'text-slate-400'
        );

        authTab.classList.remove(
            'border-cyan-400',
            'text-cyan-400'
        );

        authTab.classList.add(
            'border-transparent',
            'text-slate-400'
        );
    }
}


/*
|--------------------------------------------------------------------------
| BACK TO LOGIN
|--------------------------------------------------------------------------
*/

function backToLogin() {

    document
        .getElementById('twoFactorSection')
        .classList.add('hidden');

    document
        .getElementById('loginSection')
        .classList.remove('hidden');

    document
        .getElementById('otpInput')
        .value = '';

    document
        .getElementById('recoveryInput')
        .value = '';
}


/*
|--------------------------------------------------------------------------
| LOGOUT
|--------------------------------------------------------------------------
*/

async function handleLogout() {

    if (!currentToken) {
        return;
    }

    await apiFetch('/api/logout', {
        method: 'POST'
    });

    currentToken = null;

    currentUserId = null;

    document
        .getElementById('dashboardSection')
        .classList.add('hidden');

    document
        .getElementById('headerUser')
        .classList.add('hidden');

    document
        .getElementById('loginSection')
        .classList.remove('hidden');

    document
        .getElementById('loginPassword')
        .value = '';

    alert('Logged out successfully.');
}


/*
|--------------------------------------------------------------------------
| SETTINGS MESSAGE
|--------------------------------------------------------------------------
*/

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


/*
|--------------------------------------------------------------------------
| COPY
|--------------------------------------------------------------------------
*/

async function copyText(text) {

    try {

        await navigator.clipboard.writeText(text);

        alert('Copied successfully.');

    } catch (error) {

        alert('Unable to copy.');

    }
}


/*
|--------------------------------------------------------------------------
| HTML ESCAPE
|--------------------------------------------------------------------------
*/

function escapeHtml(value) {

    const div = document.createElement('div');

    div.innerText = value ?? '';

    return div.innerHTML;
}


/*
|--------------------------------------------------------------------------
| OTP INPUT
|--------------------------------------------------------------------------
*/

document
    .getElementById('otpInput')
    .addEventListener('input', function () {

        this.value = this.value
            .replace(/\D/g, '')
            .slice(0, 6);

        if (this.value.length === 6) {

            submitGoogleOtp();

        }

    });


/*
|--------------------------------------------------------------------------
| INITIAL TAB
|--------------------------------------------------------------------------
*/

showDashboardTab('overview');

</script>

</body>

</html>