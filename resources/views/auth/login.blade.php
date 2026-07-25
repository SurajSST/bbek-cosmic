<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign In — {{ config('app.name', 'Cosmic Bill') }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, .font-heading { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="h-full bg-slate-950 text-slate-100 flex items-center justify-center p-4 relative overflow-hidden selection:bg-indigo-500 selection:text-white">
    
    <!-- Background Gradient Glows -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-indigo-600/30 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-violet-600/30 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-md relative z-10 space-y-6">

        <!-- Logo Header -->
        <div class="text-center space-y-2">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-violet-500 text-white font-extrabold text-2xl shadow-xl shadow-indigo-500/30 mb-2">
                C
            </div>
            <h1 class="text-3xl font-extrabold text-white tracking-tight font-heading">Cosmic Bill</h1>
            <p class="text-xs text-slate-400 font-medium">Enterprise SaaS Billing & Access Control Engine</p>
        </div>

        <!-- Login Card -->
        <div class="bg-slate-900/80 backdrop-blur-2xl border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl space-y-6">
            
            <div class="border-b border-slate-800/80 pb-4">
                <h2 class="text-lg font-bold text-white font-heading">Sign in to workspace</h2>
                <p class="text-xs text-slate-400 mt-0.5">Enter your operational credentials below</p>
            </div>

            <!-- Session Info Message -->
            @if (session('info'))
                <div class="p-3.5 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-300 text-xs flex items-center gap-2.5">
                    <svg class="w-4 h-4 shrink-0 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="font-medium">{{ session('info') }}</span>
                </div>
            @endif

            <!-- Global Validation Errors -->
            @if ($errors->any())
                <div class="p-3.5 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-xs space-y-1">
                    @foreach ($errors->all() as $error)
                        <div class="flex items-center gap-2">
                            <span class="text-rose-400 font-bold">✕</span>
                            <span class="font-medium">{{ $error }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <!-- Email Input -->
                <div class="space-y-1.5">
                    <label for="email" class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider">Email Address</label>
                    <input type="email" id="email" name="email" value="{{ old('email', 'admin@example.com') }}" required autofocus
                        class="w-full px-4 py-3 rounded-2xl bg-slate-950/80 border border-slate-800 text-white placeholder-slate-500 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                        placeholder="admin@example.com">
                </div>

                <!-- Password Input -->
                <div class="space-y-1.5">
                    <label for="password" class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider">Password</label>
                    <input type="password" id="password" name="password" required value="password"
                        class="w-full px-4 py-3 rounded-2xl bg-slate-950/80 border border-slate-800 text-white placeholder-slate-500 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                        placeholder="••••••••">
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded-md border-slate-800 bg-slate-950 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-slate-900">
                        <span class="text-xs text-slate-400 font-medium">Keep me signed in</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full py-3.5 px-4 rounded-2xl bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white text-xs font-bold shadow-lg shadow-indigo-600/30 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-slate-900 transition transform active:scale-[0.99] uppercase tracking-wider">
                    Authenticate Account
                </button>
            </form>
        </div>

        <!-- Footer -->
        <div class="text-center space-y-1">
            <p class="text-[11px] text-slate-400 font-medium">
                Protected by Spatie Role & Permission Authorization Engine
            </p>
        </div>
    </div>
</body>
</html>
