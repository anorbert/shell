<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

const steps = [
    { number: '01', title: 'Choose a market', description: 'Explore the available instruments and review the price chart before making a decision.' },
    { number: '02', title: 'Set your position', description: 'Choose your trade amount and expiry time. Check the terms before you confirm.' },
    { number: '03', title: 'Follow the outcome', description: 'Track an open position and review its result in your trading history after expiry.' },
];

const features = [
    { icon: 'chart', title: 'Market-focused workspace', description: 'A focused chart and trading workspace designed to help you assess price movement.' },
    { icon: 'clock', title: 'Defined expiry times', description: 'Every fixed-time position has a selected expiry, so the outcome window is clear.' },
    { icon: 'layers', title: 'Demo and real kept separate', description: 'Practice funds and real-money balances are distinct. Demo results are not withdrawable.' },
    { icon: 'activity', title: 'Trade activity at a glance', description: 'Review open positions, completed trades and settlement outcomes from your account.' },
];
</script>

<template>
    <Head title="SHELL — Fixed-Time Trading">
        <meta name="description" content="Explore SHELL's fixed-time trading workspace. Review markets, practice with demo funds and understand the risks before trading." />
        <meta name="theme-color" content="#07111f" />
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
        <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    </Head>

    <div class="shell-landing min-h-screen overflow-hidden bg-[#07111f] text-slate-100">
        <div class="pointer-events-none fixed inset-0 -z-0 overflow-hidden" aria-hidden="true">
            <div class="absolute -left-40 top-20 h-96 w-96 rounded-full bg-cyan-500/10 blur-[120px]"></div>
            <div class="absolute -right-40 top-72 h-96 w-96 rounded-full bg-emerald-400/10 blur-[130px]"></div>
            <div class="absolute inset-0 opacity-[0.12]" style="background-image:linear-gradient(rgba(148,163,184,.12) 1px,transparent 1px),linear-gradient(90deg,rgba(148,163,184,.12) 1px,transparent 1px);background-size:64px 64px"></div>
        </div>

        <header class="relative z-10 mx-auto flex w-full max-w-7xl items-center justify-between px-5 py-5 sm:px-8 lg:px-10">
            <a href="/" class="flex items-center gap-3" aria-label="SHELL home">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl border border-cyan-300/20 bg-cyan-300/10 text-lg font-extrabold tracking-tight text-cyan-200 shadow-lg shadow-cyan-950/30">S</span>
                <span class="font-['Manrope'] text-xl font-extrabold tracking-[0.18em] text-white">SHELL<span class="text-cyan-300">.</span></span>
            </a>
            <nav class="hidden items-center gap-8 text-sm font-medium text-slate-300 md:flex">
                <a href="#platform" class="transition hover:text-white">Platform</a>
                <a href="#how-it-works" class="transition hover:text-white">How it works</a>
                <a href="#risk" class="transition hover:text-white">Risk awareness</a>
            </nav>
            <div class="flex items-center gap-2 sm:gap-3">
                <Link v-if="$page.props.auth?.user" :href="route('dashboard')" class="rounded-xl border border-white/10 px-4 py-2.5 text-sm font-semibold text-slate-200 transition hover:border-white/25 hover:bg-white/5">Dashboard</Link>
                <template v-else>
                    <Link :href="route('login')" class="rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-300 transition hover:text-white sm:px-4">Log in</Link>
                    <Link :href="route('register')" class="rounded-xl bg-cyan-300 px-4 py-2.5 text-sm font-bold text-slate-950 shadow-lg shadow-cyan-950/30 transition hover:-translate-y-0.5 hover:bg-cyan-200 sm:px-5">Create account</Link>
                </template>
            </div>
        </header>

        <main class="relative z-10">
            <section class="mx-auto grid max-w-7xl items-center gap-14 px-5 pb-20 pt-12 sm:px-8 lg:grid-cols-[1.02fr_.98fr] lg:px-10 lg:pb-28 lg:pt-20">
                <div class="max-w-2xl">
                    <div class="mb-7 inline-flex items-center gap-2 rounded-full border border-cyan-300/20 bg-cyan-300/[0.07] px-3.5 py-2 text-xs font-semibold tracking-wide text-cyan-200">
                        <span class="h-2 w-2 rounded-full bg-cyan-300"></span>
                        A focused fixed-time trading experience
                    </div>
                    <h1 class="font-['Manrope'] text-5xl font-extrabold leading-[1.08] tracking-tight text-white sm:text-6xl lg:text-[4.35rem]">
                        Read the market.<br />
                        <span class="bg-gradient-to-r from-cyan-300 via-sky-200 to-emerald-300 bg-clip-text text-transparent">Make your move.</span>
                    </h1>
                    <p class="mt-7 max-w-xl text-base leading-8 text-slate-300 sm:text-lg">
                        A streamlined workspace for fixed-time UP and DOWN trading. Study price movement, define your position and expiry, then follow every trade through to its result.
                    </p>
                    <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                        <Link v-if="!$page.props.auth?.user" :href="route('register')" class="inline-flex items-center justify-center gap-3 rounded-xl bg-cyan-300 px-7 py-4 text-sm font-extrabold text-slate-950 shadow-xl shadow-cyan-950/40 transition hover:-translate-y-1 hover:bg-cyan-200">
                            Start with an account
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M4 10h12m-5-5 5 5-5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </Link>
                        <Link v-else :href="route('dashboard')" class="inline-flex items-center justify-center gap-3 rounded-xl bg-cyan-300 px-7 py-4 text-sm font-extrabold text-slate-950 shadow-xl shadow-cyan-950/40 transition hover:-translate-y-1 hover:bg-cyan-200">Open your dashboard</Link>
                        <a href="#how-it-works" class="inline-flex items-center justify-center rounded-xl border border-white/15 bg-white/[0.03] px-7 py-4 text-sm font-bold text-white transition hover:border-white/30 hover:bg-white/[0.07]">Explore the platform</a>
                    </div>
                    <div class="mt-8 flex flex-wrap items-center gap-x-6 gap-y-3 text-xs font-medium text-slate-400">
                        <span class="inline-flex items-center gap-2"><svg class="h-4 w-4 text-emerald-300" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="m4 10 4 4 8-8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg> Demo and real balances are separate</span>
                        <span class="inline-flex items-center gap-2"><svg class="h-4 w-4 text-emerald-300" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="m4 10 4 4 8-8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg> Defined trade expiry</span>
                    </div>
                </div>

                <div class="relative mx-auto w-full max-w-[590px]">
                    <div class="absolute -inset-5 rounded-[2rem] bg-gradient-to-br from-cyan-400/10 via-transparent to-emerald-400/10 blur-2xl"></div>
                    <div class="relative overflow-hidden rounded-3xl border border-white/10 bg-[#0c1a2b]/95 shadow-2xl shadow-black/40 backdrop-blur-xl">
                        <div class="flex items-center justify-between border-b border-white/[0.07] px-5 py-4 sm:px-6">
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/[0.07] text-sm font-bold text-white">FX</div>
                                <div><p class="text-sm font-bold text-white">Trading workspace</p><p class="mt-0.5 text-[11px] text-slate-500">Interface preview</p></div>
                            </div>
                            <span class="rounded-full border border-white/10 bg-white/[0.04] px-3 py-1.5 text-[10px] font-semibold uppercase tracking-wider text-slate-400">Illustrative</span>
                        </div>
                        <div class="grid grid-cols-3 gap-2 border-b border-white/[0.07] p-4 sm:px-6">
                            <div class="rounded-xl bg-white/[0.04] p-3"><p class="text-[10px] uppercase tracking-wider text-slate-500">Direction</p><p class="mt-1 text-sm font-bold text-emerald-300">UP / DOWN</p></div>
                            <div class="rounded-xl bg-white/[0.04] p-3"><p class="text-[10px] uppercase tracking-wider text-slate-500">Position</p><p class="mt-1 text-sm font-bold text-white">Set amount</p></div>
                            <div class="rounded-xl bg-white/[0.04] p-3"><p class="text-[10px] uppercase tracking-wider text-slate-500">Duration</p><p class="mt-1 text-sm font-bold text-white">Set expiry</p></div>
                        </div>
                        <div class="p-4 sm:p-6">
                            <div class="mb-4 flex items-start justify-between">
                                <div><p class="text-xs text-slate-500">Price movement preview</p><p class="mt-1 font-['Manrope'] text-2xl font-bold tracking-tight text-white">Market chart</p></div>
                                <div class="text-right"><p class="text-[10px] uppercase tracking-wider text-slate-500">Chart status</p><p class="mt-1 text-xs font-semibold text-cyan-200">Illustrative data</p></div>
                            </div>
                            <div class="relative h-48 overflow-hidden rounded-2xl border border-white/[0.07] bg-[#081522] p-3 sm:h-56">
                                <div class="absolute inset-0 opacity-60" style="background-image:linear-gradient(rgba(148,163,184,.10) 1px,transparent 1px),linear-gradient(90deg,rgba(148,163,184,.10) 1px,transparent 1px);background-size:36px 36px"></div>
                                <div class="absolute inset-x-3 top-1/2 border-t border-dashed border-white/15"></div>
                                <svg class="relative h-full w-full" viewBox="0 0 520 190" preserveAspectRatio="none" fill="none" aria-label="Illustrative price movement line">
                                    <defs><linearGradient id="chartStroke" x1="0" y1="0" x2="520" y2="0" gradientUnits="userSpaceOnUse"><stop stop-color="#38bdf8"/><stop offset="1" stop-color="#34d399"/></linearGradient><linearGradient id="chartFill" x1="260" y1="20" x2="260" y2="190" gradientUnits="userSpaceOnUse"><stop stop-color="#22d3ee" stop-opacity=".2"/><stop offset="1" stop-color="#22d3ee" stop-opacity="0"/></linearGradient></defs>
                                    <path d="M0 144 C25 138 29 116 51 123 S83 145 104 118 S135 104 154 113 S181 85 203 96 S230 122 249 91 S278 76 298 88 S324 56 345 69 S373 98 393 72 S420 55 438 63 S470 32 490 42 L520 24 V190 H0Z" fill="url(#chartFill)"/>
                                    <path d="M0 144 C25 138 29 116 51 123 S83 145 104 118 S135 104 154 113 S181 85 203 96 S230 122 249 91 S278 76 298 88 S324 56 345 69 S373 98 393 72 S420 55 438 63 S470 32 490 42 L520 24" stroke="url(#chartStroke)" stroke-width="3" stroke-linecap="round"/>
                                    <circle cx="490" cy="42" r="5" fill="#34d399" stroke="#d1fae5" stroke-width="2"/>
                                </svg>
                                <div class="absolute bottom-3 left-4 text-[9px] text-slate-600">Illustration only · not a live quote</div>
                            </div>
                            <div class="mt-4 grid grid-cols-2 gap-3">
                                <div class="flex items-center justify-center gap-2 rounded-xl border border-emerald-400/20 bg-emerald-400/10 py-3 text-sm font-bold text-emerald-300"><span>↗</span> UP</div>
                                <div class="flex items-center justify-center gap-2 rounded-xl border border-rose-400/20 bg-rose-400/10 py-3 text-sm font-bold text-rose-300"><span>↘</span> DOWN</div>
                            </div>
                            <p class="mt-4 text-center text-[10px] leading-5 text-slate-500">This visual is a product illustration. It does not represent current market prices, a trading signal or a guaranteed outcome.</p>
                        </div>
                    </div>
                    <div class="absolute -bottom-5 -left-5 hidden rounded-2xl border border-white/10 bg-[#102337] px-4 py-3 shadow-xl sm:block">
                        <p class="text-[10px] uppercase tracking-wider text-slate-500">Trade structure</p><p class="mt-1 text-sm font-bold text-white">Entry → Expiry → Result</p>
                    </div>
                </div>
            </section>

            <section id="platform" class="border-y border-white/[0.07] bg-white/[0.025]">
                <div class="mx-auto max-w-7xl px-5 py-16 sm:px-8 lg:px-10 lg:py-20">
                    <div class="max-w-2xl">
                        <p class="text-xs font-bold uppercase tracking-[0.22em] text-cyan-300">Built around the trading workflow</p>
                        <h2 class="mt-4 font-['Manrope'] text-3xl font-extrabold tracking-tight text-white sm:text-4xl">The tools to make each decision clearer.</h2>
                        <p class="mt-4 leading-7 text-slate-400">A trading interface should make the important details easy to find—not hide them behind clutter.</p>
                    </div>
                    <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <article v-for="feature in features" :key="feature.title" class="rounded-2xl border border-white/[0.08] bg-[#0b1929]/80 p-6 transition hover:-translate-y-1 hover:border-cyan-300/25">
                            <div class="flex h-11 w-11 items-center justify-center rounded-xl border border-cyan-300/15 bg-cyan-300/[0.08] text-cyan-200">
                                <svg v-if="feature.icon === 'chart'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 19V5m0 14h17M7 15l4-4 3 2 6-7" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                <svg v-else-if="feature.icon === 'clock'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.7"/><path d="M12 7v5l3 2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                                <svg v-else-if="feature.icon === 'layers'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m12 3 9 5-9 5-9-5 9-5Zm-9 9 9 5 9-5M3 16l9 5 9-5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                <svg v-else class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3 12h4l3-8 4 16 3-8h4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </div>
                            <h3 class="mt-5 text-base font-bold text-white">{{ feature.title }}</h3>
                            <p class="mt-2 text-sm leading-6 text-slate-400">{{ feature.description }}</p>
                        </article>
                    </div>
                </div>
            </section>

            <section id="how-it-works" class="mx-auto max-w-7xl px-5 py-16 sm:px-8 lg:px-10 lg:py-24">
                <div class="grid gap-12 lg:grid-cols-[.8fr_1.2fr] lg:items-start">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.22em] text-cyan-300">Simple workflow. Serious decisions.</p>
                        <h2 class="mt-4 font-['Manrope'] text-3xl font-extrabold tracking-tight text-white sm:text-4xl">Know the process before you place a trade.</h2>
                        <p class="mt-5 leading-7 text-slate-400">Fixed-time trading is straightforward to understand, but the financial risk is real. Take time to learn the mechanics before using real funds.</p>
                        <Link v-if="!$page.props.auth?.user" :href="route('register')" class="mt-7 inline-flex items-center gap-2 text-sm font-bold text-cyan-200 transition hover:text-cyan-100">Create your account <span aria-hidden="true">→</span></Link>
                        <Link v-else :href="route('dashboard')" class="mt-7 inline-flex items-center gap-2 text-sm font-bold text-cyan-200 transition hover:text-cyan-100">Continue to dashboard <span aria-hidden="true">→</span></Link>
                    </div>
                    <div class="space-y-4">
                        <article v-for="step in steps" :key="step.number" class="flex gap-5 rounded-2xl border border-white/[0.08] bg-white/[0.025] p-5 sm:p-6">
                            <div class="font-['Manrope'] text-2xl font-extrabold text-cyan-300/80">{{ step.number }}</div>
                            <div><h3 class="font-bold text-white">{{ step.title }}</h3><p class="mt-2 text-sm leading-6 text-slate-400">{{ step.description }}</p></div>
                        </article>
                    </div>
                </div>
            </section>

            <section id="risk" class="border-y border-amber-300/15 bg-amber-300/[0.045]">
                <div class="mx-auto flex max-w-7xl flex-col gap-5 px-5 py-8 sm:px-8 md:flex-row md:items-center md:justify-between lg:px-10">
                    <div class="max-w-4xl">
                        <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-amber-200"><span class="h-2 w-2 rounded-full bg-amber-300"></span> Risk disclosure</div>
                        <p class="mt-3 text-sm leading-6 text-slate-300">Fixed-time and binary-style options are high-risk products. You can lose the full amount committed to a trade. Returns are not guaranteed, and demo performance does not predict real-money results. Only trade with money you can afford to lose and check whether these products are permitted in your jurisdiction.</p>
                    </div>
                    <a href="#top" class="shrink-0 text-sm font-semibold text-amber-200 transition hover:text-amber-100">Back to top ↑</a>
                </div>
            </section>
        </main>

        <footer class="relative z-10 mx-auto flex max-w-7xl flex-col gap-4 px-5 py-8 text-xs text-slate-500 sm:px-8 md:flex-row md:items-center md:justify-between lg:px-10">
            <div class="flex items-center gap-3"><span class="font-extrabold tracking-[0.18em] text-slate-300">SHELL.</span><span>© {{ new Date().getFullYear() }} SHELL. All rights reserved.</span></div>
            <p>Trading involves risk. Inform yourself before committing funds.</p>
        </footer>
    </div>
</template>
