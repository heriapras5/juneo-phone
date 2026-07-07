@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@push('styles')
<style>
.anm-wrap { opacity: 0; transform: translateY(20px); transition: opacity 0.5s ease-out, transform 0.5s ease-out; }
.anm-wrap.anm-show { opacity: 1; transform: translateY(0); }
.anm-card { opacity: 0; transform: translateY(16px); transition: opacity 0.4s ease-out, transform 0.4s ease-out; }
.anm-card.anm-show { opacity: 1; transform: translateY(0); }
@keyframes pulse-soft { 0%,100%{opacity:1} 50%{opacity:0.55} }
.anm-pulse { animation: pulse-soft 2s ease-in-out infinite; }
.anm-row { opacity: 0; transform: translateX(-8px); transition: opacity 0.3s ease-out, transform 0.3s ease-out; }
.anm-row.anm-show { opacity: 1; transform: translateX(0); }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
@endpush

@section('content')
@php
$periodLabels = ['daily' => 'Harian', 'weekly' => 'Mingguan', 'monthly' => 'Bulanan', 'yearly' => 'Tahunan'];
$periodIcons = ['daily' => 'fa-calendar-day', 'weekly' => 'fa-calendar-week', 'monthly' => 'fa-calendar-alt', 'yearly' => 'fa-calendar'];
$hasCustomRange = request()->has('from') && request()->has('to');
@endphp

<div class="space-y-6" id="dashboard-app">

    {{-- ============ Period Filter Bar (AJAX) ============ --}}
    <div x-data="{ df: '{{ $fromDate->format('Y-m-d') }}', dt: '{{ $toDate->format('Y-m-d') }}' }" id="dashFilterBar" class="anm-wrap bg-white border border-gray-200 rounded-xl shadow-sm px-3 sm:px-4 py-3 sm:py-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:flex-wrap sm:gap-1 lg:gap-2 border-l-4 border-yellow-500/30">
        <span class="text-[11px] text-gray-500 font-medium flex items-center gap-1 sm:gap-1.5">
            <i class="fas fa-filter text-yellow-500"></i>
            <span>Filter waktu:</span>
        </span>
        <div class="grid grid-cols-2 sm:flex sm:flex-wrap gap-2 sm:gap-0">
        @foreach(['daily', 'weekly', 'monthly', 'yearly'] as $p)
        <button data-period="{{ $p }}"
            class="period-btn w-full sm:w-auto px-2 lg:px-3.5 py-1.5 lg:py-2 text-xs lg:text-sm font-medium rounded-lg transition-colors {{ $period === $p && !$hasCustomRange ? 'bg-gradient-to-r from-yellow-500 to-amber-500 text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 shadow-sm' }}">
            <i class="fas {{ $periodIcons[$p] }} hidden lg:inline mr-1 sm:mr-1.5"></i>{{ $periodLabels[$p] }}
        </button>
        @endforeach
        </div>

        <span class="hidden lg:inline text-gray-200">|</span>

        <div class="flex items-center gap-2 flex-nowrap">
            <span class="text-[11px] text-gray-500 font-medium whitespace-nowrap">Dari</span>
            <input type="date" id="filterFrom" x-model="df"
                class="px-2 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none w-28 lg:w-auto flex-shrink-0">
            <span class="text-[11px] text-gray-500 font-medium whitespace-nowrap">Sampai</span>
            <input type="date" id="filterTo" x-model="dt"
                class="px-2 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none w-28 lg:w-auto flex-shrink-0">
            <button id="applyDateBtn"
                class="flex-shrink-0 px-3 sm:px-2 lg:px-4 py-1.5 sm:py-1.5 lg:py-2 text-xs sm:text-xs lg:text-sm font-medium bg-gradient-to-r from-yellow-500 to-amber-500 hover:from-yellow-400 hover:to-amber-400 text-white rounded-lg shadow-sm flex items-center justify-center gap-1 sm:gap-2">
                <i class="fas fa-filter"></i> <span class="hidden sm:inline">Filter</span>
            </button>
            <button id="resetDateBtn" @if(!$hasCustomRange) style="display:none" @endif
                class="flex-shrink-0 px-3 sm:px-2 lg:px-4 py-1.5 sm:py-1.5 lg:py-2 text-xs sm:text-xs lg:text-sm font-medium bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg shadow-sm flex items-center justify-center gap-1 sm:gap-2">
                <i class="fas fa-undo"></i> <span class="hidden sm:inline">Reset</span>
            </button>
        </div>
    </div>

    {{-- ============ Period Info Bar ============ --}}
    <div class="anm-wrap bg-white border border-gray-200 rounded-xl shadow-sm px-3 sm:px-5 py-3 sm:py-4 flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3 border-l-4 border-yellow-500/30">
        <div class="flex items-center gap-2 sm:gap-3 text-sm text-gray-700">
            <span class="flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-yellow-50 text-yellow-500 flex-shrink-0">
                <i class="fas fa-calendar-alt text-sm sm:text-base"></i>
            </span>
            <div class="leading-tight min-w-0">
                <span class="text-xs sm:text-sm font-semibold" id="infoFromDate">{{ $fromDate->format('d M Y') }}</span>
                <span class="text-gray-300 mx-0.5 sm:mx-1">—</span>
                <span class="text-xs sm:text-sm font-semibold" id="infoToDate">{{ $toDate->format('d M Y') }}</span>
                <div class="text-[10px] sm:text-[11px] text-gray-400 mt-0.5" id="infoPeriodLabel">{{ $hasCustomRange ? 'Rentang Kustom' : $periodLabels[$period] }}</div>
            </div>
            <span class="hidden lg:inline text-[10px] text-gray-400 italic ml-1 max-w-[200px] leading-tight">Gunakan filter di atas untuk mengubah rentang waktu tampilan data</span>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <span id="infoBadge"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium {{ $isPeriodComplete ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                <i class="fas {{ $isPeriodComplete ? 'fa-check-circle' : 'fa-hourglass-half' }}"></i>
                {{ $isPeriodComplete ? 'Selesai' : 'Belum Selesai' }}
            </span>
        </div>
        <div id="infoEndDate" class="sm:ml-auto text-xs text-gray-400 flex items-center gap-1.5" @if($isPeriodComplete)style="display:none"@endif>
            <i class="fas fa-flag-checkered text-gray-300"></i>
            Berakhir <span id="infoEndDateText">{{ $toDate->format('d M Y') }}</span>
        </div>
    </div>

    {{-- ============ Notif Kebutuhan Pribadi ============ --}}
    <div id="biayaNotifWrap" class="anm-wrap">
        @if(!$biayaCount)
        <div class="bg-orange-50 border border-orange-200 text-orange-700 px-4 py-3 rounded-xl flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3 shadow-sm">
            <div class="flex items-center gap-2 sm:gap-3 flex-1 min-w-0">
                <i class="fas fa-clock text-orange-500 text-lg flex-shrink-0"></i>
                <span class="text-sm">Kebutuhan Pribadi hari ini <strong>belum tercatat</strong></span>
            </div>
            <a href="{{ route('biaya-harian.index') }}" class="w-full sm:w-auto px-4 py-1.5 bg-orange-500 hover:bg-orange-600 text-white rounded-lg text-sm font-medium text-center flex-shrink-0">
                Catat Sekarang
            </a>
        </div>
        @endif
    </div>

    {{-- Empty State (AJAX) --}}
    <div id="emptyStateWrap" class="anm-wrap"></div>

    {{-- SVG Gradient Defs for sparklines --}}
    <svg width="0" height="0" style="position:absolute;pointer-events:none">
        <defs>
            <linearGradient id="sparkAmber" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#F59E0B"/>
                <stop offset="100%" stop-color="#D97706"/>
            </linearGradient>
            <linearGradient id="sparkEmerald" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#10B981"/>
                <stop offset="100%" stop-color="#059669"/>
            </linearGradient>
        </defs>
    </svg>

    {{-- ============ BAGIAN A: Ringkasan Keuangan ============ --}}
        <section class="space-y-3 anm-wrap" id="kpiSection">
        <div class="flex items-center gap-2">
            <span class="h-5 w-1.5 rounded-full bg-gradient-to-b from-yellow-500 to-amber-500"></span>
            <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wide">Ringkasan Keuangan</h2>
            <span class="text-[10px] sm:text-[11px] text-gray-400 italic hidden sm:inline">— Pengeluaran, Omzet, Kebutuhan Pribadi &amp; Profit</span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-stretch" id="rowKpi">
            @include('dashboard._partials.kpi-cards', [
                'periodLabels' => $periodLabels,
                'period' => $period,
                'fromDate' => $fromDate,
                'toDate' => $toDate,
                'periodTotals' => $periodTotals,
                'prevPeriodTotals' => $prevPeriodTotals,
                'changePengeluaran' => $changePengeluaran,
                'changeOmzet' => $changeOmzet,
                'changeKebutuhan' => $changeKebutuhan,
                'changeProfit' => $changeProfit,
            ])
        </div>
    </section>

    {{-- ============ BAGIAN B: Grafik & Status ============ --}}
        <section class="space-y-3 anm-wrap" id="chartSection">
        <div class="flex items-center gap-2">
            <span class="h-5 w-1.5 rounded-full bg-gradient-to-b from-yellow-500 to-amber-500"></span>
            <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wide">Grafik &amp; Status</h2>
        </div>
        <div class="flex flex-col gap-6 lg:grid lg:grid-cols-3 lg:gap-6">
            <div class="lg:col-span-2 bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden flex flex-col">
                <div class="p-5 pb-1 flex-1 flex flex-col">
                    <h3 class="text-sm font-semibold text-gray-700 mb-1 flex items-center gap-2">
                        <i class="fas fa-chart-bar text-yellow-500"></i>
                        Grafik <span id="chartPeriodLabel">{{ $periodLabels[$period] }}</span>
                    </h3>
                    <p class="text-[10px] text-gray-400 italic mb-3">Perbandingan Omzet, Pengeluaran &amp; Profit per periode</p>
                    <div class="relative flex-1 min-h-[180px] sm:min-h-[260px]">
                        <canvas id="revenueChart" class="absolute inset-0 w-full h-full"></canvas>
                    </div>
                    <div class="flex flex-nowrap items-center justify-center gap-x-3 sm:gap-x-4 pt-3 pb-1 text-xs text-gray-500 border-t border-gray-100 mt-3">
                        <span class="flex items-center gap-1.5 whitespace-nowrap"><span class="inline-block w-3 h-3 rounded-sm" style="background:rgba(16,185,129,0.7)"></span> Omzet</span>
                        <span class="flex items-center gap-1.5 whitespace-nowrap"><span class="inline-block w-3 h-3 rounded-sm" style="background:rgba(239,68,68,0.5)"></span> Pengeluaran</span>
                        <span class="flex items-center gap-1.5 whitespace-nowrap"><span class="inline-block w-3 h-3 rounded-sm" style="background:rgba(99,102,241,0.6)"></span> Profit</span>
                    </div>
                </div>
                <div class="h-1 rounded-b-xl bg-yellow-500"></div>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden flex flex-col">
                <div class="p-5 flex-1 flex flex-col">
                    <h3 class="text-sm font-semibold text-gray-700 mb-1 flex items-center gap-2">
                        <i class="fas fa-chart-donut text-yellow-500"></i>
                        Status Service
                    </h3>
                    <p class="text-[10px] text-gray-400 italic mb-3">Persentase status pengerjaan service saat ini</p>
                    <div class="relative flex-1 min-h-[180px] sm:min-h-[260px] flex items-center justify-center">
                        <canvas id="statusChart" style="max-height:100%;max-width:100%;"></canvas>
                    </div>
                    <div id="statusLegend" class="grid grid-cols-2 gap-x-4 gap-y-2 pt-3 border-t border-gray-100 mt-3 text-xs text-gray-500"></div>
                </div>
                <div class="h-1 rounded-b-xl bg-yellow-500"></div>
            </div>
        </div>
    </section>

    {{-- ============ BAGIAN C: Service Terbaru ============ --}}
    <section class="space-y-3 anm-wrap">
        <div class="flex items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <span class="h-5 w-1.5 rounded-full bg-gradient-to-b from-yellow-500 to-amber-500"></span>
                <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wide">Service Terbaru</h2>
            </div>
            <a href="{{ route('services.index') }}" class="text-[11px] sm:text-xs text-yellow-600 hover:text-yellow-700 font-medium whitespace-nowrap">Lihat Semua &rarr;</a>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <p class="px-4 sm:px-5 pt-3 text-[10px] text-gray-400 italic">10 service terakhir yang dicatat</p>
            <div class="divide-y divide-gray-100" id="recentServicesList">
                @forelse($recentServices as $service)
                <div class="anm-row px-3 sm:px-5 py-2.5 sm:py-3 flex items-center justify-between hover:bg-gray-50/50">
                    <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-gradient-to-br from-yellow-50 to-amber-100 items-center justify-center flex-shrink-0 hidden sm:flex">
                            <i class="fas fa-mobile-alt text-yellow-600 text-[10px] sm:text-xs"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $service->customer?->nama ?? '-' }}</p>
                            @php $kr = is_string($service->kerusakan) ? json_decode($service->kerusakan, true) : $service->kerusakan; @endphp
                            <p class="text-xs text-gray-500 truncate">{{ $service->type_hp }} — {{ is_array($kr) ? implode(', ', $kr) : $service->kerusakan }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0 ml-2">
                        @php
                        $cls = match($service->status) { 'menunggu' => 'bg-yellow-100 text-yellow-700', 'dikerjakan' => 'bg-blue-100 text-blue-700', 'selesai' => 'bg-emerald-100 text-emerald-700', 'diambil' => 'bg-gray-100 text-gray-600', 'batal' => 'bg-red-100 text-red-700', default => 'bg-gray-100 text-gray-600' };
                        $label = match($service->status) { 'menunggu' => 'Menunggu', 'dikerjakan' => 'Dikerjakan', 'selesai' => 'Selesai', 'diambil' => 'Diambil', 'batal' => 'Batal', default => $service->status };
                        @endphp
                        <span class="inline-flex items-center justify-center gap-1.5 px-2.5 sm:px-3 py-1 rounded-full text-xs font-medium leading-none {{ $cls }}"><span class="w-1.5 h-1.5 rounded-full inline-block flex-shrink-0 opacity-75" style="background:currentColor"></span>{{ $label }}</span>
                        <span class="text-xs text-gray-400 w-10 sm:w-12 text-right tabular-nums">{{ $service->tanggal->format('d/m') }}</span>
                    </div>
                </div>
                @empty
                <div class="px-5 py-10 text-center text-gray-400">
                    <i class="fas fa-inbox text-3xl mb-2 block"></i>
                    <p class="text-sm">Belum ada service</p>
                </div>
                @endforelse
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
var periodLabels = @json($periodLabels);
var statusCounts = @json($statusCounts);
var chartInstance = null;
var statusChartInstance = null;
var lastUserAction = Date.now();
var refreshParams = {};
var dateFilterApi = {
    df: '{{ $fromDate->format('Y-m-d') }}',
    dt: '{{ $toDate->format('Y-m-d') }}',
    set: function(from, to) {
        this.df = from; this.dt = to;
        var el = document.getElementById('dashFilterBar');
        if (el) { try { Alpine.$data(el).df = from; Alpine.$data(el).dt = to; } catch(e) {} }
    }
};

function fmtRp(val) {
    return 'Rp ' + Number(val).toLocaleString('id-ID');
}

function fmtPct(val) {
    return (val >= 0 ? '+' : '') + Number(val).toFixed(1) + '%';
}

function updateTabActive(period, hasCustom) {
    document.querySelectorAll('.period-btn').forEach(function(btn) {
        var p = btn.dataset.period;
        if (p === period && !hasCustom) {
            btn.className = 'period-btn w-full sm:w-auto px-2 lg:px-3.5 py-1.5 lg:py-2 text-xs lg:text-sm font-medium rounded-lg transition-colors bg-gradient-to-r from-yellow-500 to-amber-500 text-white shadow-sm';
        } else {
            btn.className = 'period-btn w-full sm:w-auto px-2 lg:px-3.5 py-1.5 lg:py-2 text-xs lg:text-sm font-medium rounded-lg transition-colors bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 shadow-sm';
        }
    });
}

function updateChart(newChartData) {
    if (chartInstance) {
        chartInstance.data.labels = newChartData.labels;
        chartInstance.data.datasets[0].data = newChartData.omzet;
        chartInstance.data.datasets[1].data = newChartData.pengeluaran;
        chartInstance.data.datasets[2].data = newChartData.profit;
        chartInstance.resize();
        chartInstance.update();
    }
}

function updateStatusChart(newCounts) {
    if (!statusChartInstance) return;
    var counts = newCounts || statusCounts;
    if (typeof counts === 'undefined') return;
    var labels = ['Menunggu', 'Dikerjakan', 'Selesai', 'Diambil'];
    var data = [counts.menunggu || 0, counts.dikerjakan || 0, counts.selesai || 0, counts.diambil || 0];
    var colors = ['#F59E0B', '#3B82F6', '#10B981', '#6B7280'];
    if (counts.batal) { labels.push('Batal'); data.push(counts.batal); colors.push('#EF4444'); }
    statusChartInstance.data.labels = labels;
    statusChartInstance.data.datasets[0].data = data;
    statusChartInstance.update('none');
    renderStatusLegend(labels, data, colors);
}

function renderStatusLegend(labels, data, colors) {
    var wrap = document.getElementById('statusLegend');
    if (!wrap) return;
    var html = '';
    for (var i = 0; i < labels.length; i++) {
        html += '<div class="flex items-center gap-2 text-xs text-gray-500">'
            + '<span class="inline-block w-2.5 h-2.5 rounded-sm flex-shrink-0" style="background:' + colors[i] + '"></span>'
            + '<span class="truncate">' + labels[i] + '</span>'
            + '</div>';
    }
    wrap.innerHTML = html;
}

function updateRecentServices(services) {
    var wrap = document.getElementById('recentServicesList');
    if (!wrap || !services || services.length === 0) {
        if (wrap) wrap.innerHTML = '<div class="px-5 py-10 text-center text-gray-400"><i class="fas fa-inbox text-3xl mb-2 block"></i><p class="text-sm">Belum ada service</p></div>';
        return;
    }
    var statusMap = {
        'menunggu': { cls: 'bg-yellow-100 text-yellow-700', label: 'Menunggu' },
        'dikerjakan': { cls: 'bg-blue-100 text-blue-700', label: 'Dikerjakan' },
        'selesai': { cls: 'bg-emerald-100 text-emerald-700', label: 'Selesai' },
        'diambil': { cls: 'bg-gray-100 text-gray-600', label: 'Diambil' },
        'batal': { cls: 'bg-red-100 text-red-700', label: 'Batal' }
    };
    var html = '';
    services.forEach(function(s) {
        var st = statusMap[s.status] || { cls: 'bg-gray-100 text-gray-600', label: s.status };
        var kerusakan = Array.isArray(s.kerusakan) ? s.kerusakan.join(', ') : (s.kerusakan || '-');
                    html += '<div class="anm-row px-3 sm:px-5 py-2.5 sm:py-3 flex items-center justify-between hover:bg-gray-50/50">'
            + '<div class="flex items-center gap-2 sm:gap-3 min-w-0">'
            +   '<div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-gradient-to-br from-yellow-50 to-amber-100 items-center justify-center flex-shrink-0 hidden sm:flex"><i class="fas fa-mobile-alt text-yellow-600 text-[10px] sm:text-xs"></i></div>'
            +   '<div class="min-w-0">'
            +     '<p class="text-sm font-medium text-gray-900 truncate">' + s.customer_name + '</p>'
            +     '<p class="text-xs text-gray-500 truncate">' + s.type_hp + ' — ' + kerusakan + '</p>'
            +   '</div>'
            + '</div>'
            + '<div class="flex items-center gap-2 flex-shrink-0 ml-2">'
            +   '<span class="inline-flex items-center justify-center gap-1.5 px-2.5 sm:px-3 py-1 rounded-full text-xs font-medium leading-none ' + st.cls + '"><span class="w-1.5 h-1.5 rounded-full inline-block flex-shrink-0 opacity-75" style="background:currentColor"></span>' + st.label + '</span>'
            +   '<span class="text-xs text-gray-400 w-10 sm:w-12 text-right tabular-nums">' + s.tanggal + '</span>'
            + '</div></div>';
    });
    wrap.innerHTML = html;
    setTimeout(function() {
        wrap.querySelectorAll('.anm-row').forEach(function(el, i) {
            setTimeout(function() { el.classList.add('anm-show'); }, i * 60);
        });
    }, 50);
}

{{-- Bangun SATU kartu KPI dengan struktur div seimbang (buka = tutup).
    Inilah kunci fix bug "angka hilang setelah filter tanggal": sebelumnya 4 kartu
    dibangun jadi satu rantai string yang saling bersarang tanpa </div> penutup. --}}
function kpiCardHTML(cfg) {
    var changeHtml;
    if (cfg.change !== null && cfg.change !== undefined) {
        var up = cfg.change >= 0;
        var good = cfg.upIsGood ? up : !up;       {{-- naik=bagus atau turun=bagus --}}
        var color = good ? 'emerald' : 'red';
        changeHtml = '<div class="flex items-center gap-1 mt-1">'
            + '<i class="fas fa-' + (up ? 'arrow-up' : 'arrow-down') + ' text-' + color + '-500 text-xs"></i>'
            + '<span class="text-xs font-medium text-' + color + '-600">' + fmtPct(cfg.change) + '</span>'
            + '<span class="text-xs text-gray-400">vs periode lalu</span>'
            + '</div>';
    } else {
        changeHtml = '<div class="flex items-center gap-1 mt-1"><span class="text-xs font-medium text-gray-400">— vs periode lalu</span></div>';
    }

    var foot  = cfg.foot  ? '<p class="text-xs text-gray-400 mt-3">' + cfg.foot + '</p>' : '';
    var extra = cfg.extra || '';

    return ''
    + '<div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden group flex flex-col h-full transition-all duration-200 ease-in-out hover:-translate-y-1 hover:shadow-lg">'
    +   '<div class="p-4 sm:p-5 flex-1 flex flex-col">'
    +     '<div class="flex items-start justify-between">'
    +       '<div class="flex-1 min-w-0">'
    +         '<p class="text-xs text-gray-500 uppercase tracking-wider font-medium">' + cfg.title + '</p>'
    +         '<p class="text-[10px] text-gray-400 italic mt-1 hidden sm:block">' + cfg.desc + '</p>'
    +                             '<p class="text-base sm:text-xl lg:text-2xl font-bold mt-1 kpi-value ' + cfg.valueCls + '" data-value="' + cfg.value + '">' + fmtRp(cfg.value) + '</p>'
    +         extra
    +         changeHtml
    +       '</div>'
    +       '<div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-gradient-to-br ' + cfg.iconBg + ' flex items-center justify-center shadow-md flex-shrink-0">'
    +         '<i class="fas ' + cfg.icon + ' text-' + cfg.iconColor + ' text-sm sm:text-lg"></i>'
    +       '</div>'
    +     '</div>'
    +     foot
    +     '<div class="mt-auto pt-3 hidden sm:block">'
    +       '<svg class="w-full h-8" viewBox="0 0 200 30" preserveAspectRatio="none">'
    +         '<path d="' + cfg.sparkPath + '" fill="none" stroke="' + cfg.sparkStroke + '" stroke-width="2" stroke-linecap="round"/>'
    +         '<path d="' + cfg.sparkPath + ' L200,30 L0,30 Z" fill="' + cfg.sparkFill + '" opacity="0.15"/>'
    +       '</svg>'
    +     '</div>'
    +   '</div>'
    +   '<div class="h-1.5 rounded-b-xl ' + cfg.barCls + '"></div>'
    + '</div>';
}

function buildKpiCardsHTML(d, periodLabel) {
    var profitPositive = d.profit >= 0;
    var cards = [
        kpiCardHTML({
            title: 'Pengeluaran ' + periodLabel,
            desc: 'Modal sparepart — total biaya beli komponen service',
            value: d.pengeluaran, valueCls: 'text-red-500',
            icon: 'fa-cart-shopping', iconBg: 'from-red-50 to-rose-100', iconColor: 'red-600',
            change: d.changePengeluaran, upIsGood: false,
            foot: d.services + ' service tercatat',
            sparkPath: 'M0,25 Q20,20 40,22 T80,15 T120,18 T160,8 T200,10',
            sparkStroke: '#EF4444', sparkFill: 'url(#sparkAmber)',
            barCls: 'bg-red-500'
        }),
        kpiCardHTML({
            title: 'Omzet ' + periodLabel,
            desc: 'Total tagihan jasa service ke pelanggan — belum dipotong biaya apapun',
            value: d.omzet, valueCls: 'text-gray-900',
            icon: 'fa-money-bill-wave', iconBg: 'from-emerald-50 to-green-100', iconColor: 'emerald-600',
            change: d.changeOmzet, upIsGood: true,
            foot: d.services + ' service tercatat',
            sparkPath: 'M0,25 Q25,15 45,20 T85,10 T125,14 T165,6 T200,12',
            sparkStroke: '#10B981', sparkFill: 'url(#sparkEmerald)',
            barCls: 'bg-emerald-500'
        }),
        kpiCardHTML({
            title: 'Kebutuhan Pribadi ' + periodLabel,
            desc: 'Bank Jago + Gaji Edi + Gaji Nur + Lainnya — total biaya kebutuhan harian',
            value: d.kebutuhan_pribadi, valueCls: 'text-orange-600',
            icon: 'fa-wallet', iconBg: 'from-orange-50 to-amber-100', iconColor: 'orange-600',
            change: d.changeKebutuhan, upIsGood: false,
            extra: '<div class="grid grid-cols-2 gap-x-2 sm:gap-x-4 mt-1 text-xs kpi-breakdown">'
                + '<div class="flex items-center gap-1"><span class="text-gray-400">Bank Jago:</span><span class="font-medium text-blue-600">' + fmtRp(d.bank_jago) + '</span></div>'
                + '<div class="flex items-center gap-1"><span class="text-gray-400">Gaji Edi:</span><span class="font-medium text-purple-600">' + fmtRp(d.gaji_edi) + '</span></div>'
                + '<div class="flex items-center gap-1"><span class="text-gray-400">Gaji Nur:</span><span class="font-medium text-purple-600">' + fmtRp(d.gaji_nur) + '</span></div>'
                + '<div class="flex items-center gap-1"><span class="text-gray-400">Lainnya:</span><span class="font-medium text-amber-600">' + fmtRp(d.lainnya) + '</span></div>'
                + '</div>',
            sparkPath: 'M0,25 Q20,20 40,22 T80,15 T120,18 T160,8 T200,10',
            sparkStroke: '#F59E0B', sparkFill: 'url(#sparkAmber)',
            barCls: 'bg-orange-500'
        }),
        kpiCardHTML({
            title: 'Profit ' + periodLabel,
            desc: 'Sisa keuntungan setelah dikurangi seluruh Kebutuhan Pribadi',
            value: d.profit, valueCls: profitPositive ? 'text-emerald-600' : 'text-red-600',
            icon: 'fa-coins', iconBg: 'from-emerald-50 to-green-100', iconColor: 'emerald-600',
            change: d.changeProfit, upIsGood: true,
            foot: 'Omzet - Pengeluaran - Kebutuhan Pribadi',
            sparkPath: 'M0,22 Q30,18 50,20 T100,12 T140,16 T180,5 T200,8',
            sparkStroke: profitPositive ? '#10B981' : '#EF4444',
            sparkFill: profitPositive ? 'url(#sparkEmerald)' : 'url(#sparkAmber)',
            barCls: profitPositive ? 'bg-emerald-500' : 'bg-red-500'
        })
    ];
    return cards.join('');
}

function updateCards(d) {
    // Update refreshParams untuk auto-refresh
    if (d.hasCustomRange) {
        refreshParams = { from: d.fromDateInput, to: d.toDateInput };
    } else if (d.period) {
        refreshParams = { period: d.period };
    }

    // Tab highlight
    updateTabActive(d.period, d.hasCustomRange);

    var periodLabel = d.hasCustomRange ? 'Rentang Kustom' : (periodLabels[d.period] || '');

    // Info bar
    document.getElementById('infoFromDate').textContent = d.fromDate;
    document.getElementById('infoToDate').textContent = d.toDate;
    document.getElementById('chartPeriodLabel').textContent = periodLabel;
    document.getElementById('infoPeriodLabel').textContent = periodLabel;

    // Badge
    var badge = document.getElementById('infoBadge');
    var isComp = d.isComplete;
    badge.className = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium ' + (isComp ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200');
    badge.innerHTML = '<i class="fas ' + (isComp ? 'fa-check-circle' : 'fa-hourglass-half') + '"></i> ' + (isComp ? 'Selesai' : 'Belum Selesai');

    var endDate = document.getElementById('infoEndDate');
    if (isComp) { endDate.style.display = 'none'; }
    else { endDate.style.display = ''; document.getElementById('infoEndDateText').textContent = d.toDate; }

    // Input date values — Alpine x-model akan sikronkan ke DOM
    dateFilterApi.set(d.fromDateInput, d.toDateInput);

    // Tombol Reset hanya muncul bila sedang mencari tanggal tertentu (custom range)
    var rb = document.getElementById('resetDateBtn');
    if (rb) rb.style.display = d.hasCustomRange ? '' : 'none';

    // Notif Kebutuhan Pribadi, empty state, KPI, chart
    updateKpiOnly(d, periodLabel);
}

function animateCounter(el, target, duration) {
    var start = 0;
    var startTime = null;
    function step(timestamp) {
        if (!startTime) startTime = timestamp;
        var progress = Math.min((timestamp - startTime) / duration, 1);
        var eased = 1 - (1 - progress) * (1 - progress);
        var current = Math.round(start + (target - start) * eased);
        el.textContent = 'Rp ' + current.toLocaleString('id-ID');
        if (progress < 1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
}

function animateKpiCounters() {
    document.querySelectorAll('.kpi-value').forEach(function(el) {
        var target = parseFloat(el.dataset.value) || 0;
        animateCounter(el, target, 800);
    });
}

function updateKpiOnly(d, periodLabel) {
    // Restore date inputs via Alpine x-model — tahan terhadap Chrome autofill
    if (d.fromDateInput && d.toDateInput) dateFilterApi.set(d.fromDateInput, d.toDateInput);
    if (!periodLabel) {
        periodLabel = d.hasCustomRange ? 'Rentang Kustom' : (periodLabels[d.period] || '');
    }

    // Notif Kebutuhan Pribadi
    var bw = document.getElementById('biayaNotifWrap');
    if (!d.biayaCount) {
        bw.innerHTML = '<div class="bg-orange-50 border border-orange-200 text-orange-700 px-4 py-3 rounded-xl flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3 shadow-sm">'
            + '<div class="flex items-center gap-2 sm:gap-3 flex-1 min-w-0">'
            + '<i class="fas fa-clock text-orange-500 text-lg flex-shrink-0"></i>'
            + '<span class="text-sm">Kebutuhan Pribadi hari ini <strong>belum tercatat</strong></span>'
            + '</div>'
            + '<a href="{{ route('biaya-harian.index') }}" class="w-full sm:w-auto px-4 py-1.5 bg-orange-500 hover:bg-orange-600 text-white rounded-lg text-sm font-medium text-center flex-shrink-0">Catat Sekarang</a>'
            + '</div>';
    } else {
        bw.innerHTML = '';
    }

    // Empty state
    var ew = document.getElementById('emptyStateWrap');
    if (d.services === 0) {
        ew.innerHTML = '<div class="bg-amber-50 border border-amber-200 text-amber-700 px-4 py-3 rounded-xl flex items-center gap-3 shadow-sm">'
            + '<i class="fas fa-info-circle text-amber-500 text-lg"></i>'
            + '<div class="flex-1">Belum ada data service pada periode ini.'
            + '<span class="text-[11px] text-amber-600 block mt-0.5">Gunakan filter waktu atau pilih periode lain untuk melihat laporan.</span></div>'
            + '</div>';
    } else {
        ew.innerHTML = '';
    }

    // === 4 KPI Cards — Pengeluaran | Omzet | Kebutuhan Pribadi | Profit ===
    document.getElementById('rowKpi').innerHTML = buildKpiCardsHTML(d, periodLabel);
    animateKpiCounters();
    
    // Grafik
    updateChart(d.chartData);
}

function fetchData(params) {
    lastUserAction = Date.now();
    var url = '/dashboard/data?' + new URLSearchParams(params).toString();
    return fetch(url).then(function(r) { return r.json(); });
}

document.addEventListener('DOMContentLoaded', function() {
    // Init charts
    var chartData = @json($chartData);
    var ctx1 = document.getElementById('revenueChart');
    if (ctx1) {
        chartInstance = new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: chartData.labels,
                datasets: [
                    { label: 'Omzet', data: chartData.omzet, backgroundColor: 'rgba(16,185,129,0.7)', borderColor: '#10B981', borderWidth: 1, borderRadius: 4, barPercentage: 0.7, categoryPercentage: 0.75 },
                    { label: 'Pengeluaran', data: chartData.pengeluaran, backgroundColor: 'rgba(239,68,68,0.5)', borderColor: '#EF4444', borderWidth: 1, borderRadius: 4, barPercentage: 0.7, categoryPercentage: 0.75 },
                    { label: 'Profit', data: chartData.profit, backgroundColor: 'rgba(99,102,241,0.6)', borderColor: '#6366F1', borderWidth: 1, borderRadius: 4, barPercentage: 0.7, categoryPercentage: 0.75 },
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                animation: { duration: 800, easing: 'easeOutQuart' },
                layout: { padding: { top: 5, right: 10, bottom: 0, left: 5 } },
                plugins: {
                    legend: { display: false },
                    tooltip: { mode: 'index', intersect: false }
                },
                scales: {
                    y: { beginAtZero: true, min: 0, ticks: { callback: function(v) { return 'Rp ' + v.toLocaleString('id-ID'); }, font: { size: 9 } }, grid: { color: 'rgba(0,0,0,0.06)' } },
                    x: { grid: { display: false }, ticks: { maxRotation: 0, font: { size: 9 }, autoSkip: true, maxTicksLimit: 8 } }
                }
            }
        });
    }

    var ctx2 = document.getElementById('statusChart');
    if (ctx2) {
        var scLabels = ['Menunggu', 'Dikerjakan', 'Selesai', 'Diambil'];
        var scData = [statusCounts.menunggu || 0, statusCounts.dikerjakan || 0, statusCounts.selesai || 0, statusCounts.diambil || 0];
        var scColors = ['#F59E0B', '#3B82F6', '#10B981', '#6B7280'];
        if (statusCounts.batal) { scLabels.push('Batal'); scData.push(statusCounts.batal); scColors.push('#EF4444'); }
        statusChartInstance = new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: scLabels,
                datasets: [{ data: scData, backgroundColor: scColors, borderWidth: 0 }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                cutout: '70%',
                plugins: { legend: { display: false } }
            }
        });
        renderStatusLegend(scLabels, scData, scColors);
    }

    // Tab clicks
    document.querySelectorAll('.period-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            var p = this.dataset.period;
            fetchData({ period: p }).then(function(data) { updateCards(data); });
        });
    });

    // Apply date filter — baca dari Alpine data, bukan DOM (tahan terhadap Chrome autofill)
    document.getElementById('applyDateBtn').addEventListener('click', function() {
        var el = document.getElementById('dashFilterBar');
        var from, to;
        if (el) { try { var a = Alpine.$data(el); from = a.df; to = a.dt; } catch(e) {} }
        if (!from || !to) { from = document.getElementById('filterFrom').value; to = document.getElementById('filterTo').value; }
        if (!from || !to) { alert('Harap isi tanggal Dari dan Sampai terlebih dahulu.'); return; }
        if (from > to) { alert('Tanggal "Dari" tidak boleh lebih besar dari tanggal "Sampai".'); return; }
        dateFilterApi.set(from, to);
        fetchData({ from: from, to: to }).then(function(data) { updateCards(data); });
    });

    // Reset filter — reload halaman tanpa parameter (auto-detect period)
    document.getElementById('resetDateBtn').addEventListener('click', function() {
        window.location.href = '{{ route("dashboard") }}';
    });

    // Auto-refresh tiap 30 detik (gentle: lewati jika user baru saja berinteraksi)
    var currentParams = new URLSearchParams(window.location.search);
    if (currentParams.get('period')) refreshParams = { period: currentParams.get('period') };
    else if (currentParams.get('from') && currentParams.get('to')) { refreshParams = { from: currentParams.get('from'), to: currentParams.get('to') }; }
    else refreshParams = { period: '{{ $period }}' };

    setInterval(function() {
        if (Date.now() - lastUserAction < 15000) return; {{-- jangan ganggu saat user aktif --}}
        {{-- Fetch dashboard data terpisah dari recent-services agar error satu tidak blokir yang lain --}}
        fetch('/dashboard/data?' + new URLSearchParams(refreshParams).toString())
            .then(function(r) { return r.json(); })
            .then(function(dashData) { updateKpiOnly(dashData); })
            .catch(function() {});
        fetch('/dashboard/recent-services')
            .then(function(r) { return r.json(); })
            .then(function(recentData) {
                updateRecentServices(recentData.recentServices);
                updateStatusChart(recentData.statusCounts);
            })
            .catch(function() {});
    }, 30000);

    window.addEventListener('resize', function() {
        if (chartInstance) chartInstance.resize();
        if (statusChartInstance) statusChartInstance.resize();
    });

    // ===== Animations =====
    setTimeout(function() {
        document.querySelectorAll('.anm-wrap').forEach(function(el) {
            el.classList.add('anm-show');
        });
    }, 50);

    setTimeout(function() {
        document.querySelectorAll('.anm-card').forEach(function(el) {
            el.classList.add('anm-show');
        });
    }, 350);

    setTimeout(function() {
        document.querySelectorAll('.anm-row').forEach(function(el, i) {
            setTimeout(function() { el.classList.add('anm-show'); }, i * 60);
        });
    }, 100);

    var badge = document.getElementById('infoBadge');
    if (badge && badge.textContent.includes('Belum')) badge.classList.add('anm-pulse');
});
</script>
@endpush
