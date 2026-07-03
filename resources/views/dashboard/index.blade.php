@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
@endpush

@section('content')
@php
$periodLabels = ['daily' => 'Harian', 'weekly' => 'Mingguan', 'monthly' => 'Bulanan', 'yearly' => 'Tahunan'];
$periodIcons = ['daily' => 'fa-calendar-day', 'weekly' => 'fa-calendar-week', 'monthly' => 'fa-calendar-alt', 'yearly' => 'fa-calendar'];
$hasCustomRange = request()->has('from') && request()->has('to');
@endphp

<div class="space-y-6" x-data="{ loading: false }" id="dashboard-app">
    {{-- Period Filter Tabs (AJAX) --}}
    <div class="flex flex-wrap items-center gap-1">
        <span class="text-[10px] text-gray-400 italic mr-1">Filter waktu:</span>
        @foreach(['daily', 'weekly', 'monthly', 'yearly'] as $p)
        <button data-period="{{ $p }}"
            class="period-btn px-4 py-2 text-sm font-medium rounded-lg {{ $period === $p && !$hasCustomRange ? 'bg-gradient-to-r from-yellow-500 to-amber-500 text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 shadow-sm' }}">
            <i class="fas {{ $periodIcons[$p] }} mr-1.5"></i>{{ $periodLabels[$p] }}
        </button>
        @endforeach
        <span class="text-[10px] text-gray-400 italic mx-1">Dari:</span>
        <input type="date" id="filterFrom" value="{{ $fromDate->format('Y-m-d') }}"
            class="px-3 py-2 text-sm bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none">
        <span class="text-[10px] text-gray-400 italic">Sampai:</span>
        <input type="date" id="filterTo" value="{{ $toDate->format('Y-m-d') }}"
            class="px-3 py-2 text-sm bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-yellow-500/30 focus:border-yellow-500 text-gray-900 outline-none">
        <button id="applyDateBtn"
            class="px-3 py-2 text-sm font-medium bg-gradient-to-r from-yellow-500 to-amber-500 hover:from-yellow-400 hover:to-amber-400 text-white rounded-lg shadow-sm">
            <i class="fas fa-check"></i> Terapkan
        </button>
    </div>

    {{-- Period Info Bar --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm px-5 py-4 flex flex-col sm:flex-row sm:items-center gap-3">
        <div class="flex items-center gap-3 text-sm text-gray-700">
            <span class="flex items-center justify-center w-9 h-9 rounded-lg bg-yellow-50 text-yellow-500">
                <i class="fas fa-calendar-alt"></i>
            </span>
            <div class="leading-tight">
                <span class="font-semibold" id="infoFromDate">{{ $fromDate->format('d M Y') }}</span>
                <span class="text-gray-300 mx-1">—</span>
                <span class="font-semibold" id="infoToDate">{{ $toDate->format('d M Y') }}</span>
                <div class="text-[11px] text-gray-400 mt-0.5" id="infoPeriodLabel">{{ $hasCustomRange ? 'Rentang Kustom' : $periodLabels[$period] }}</div>
            </div>
            <span class="hidden sm:inline text-[10px] text-gray-400 italic ml-1 max-w-[200px] leading-tight">Gunakan filter di atas untuk mengubah rentang waktu tampilan data</span>
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

    {{-- Notif Biaya Harian --}}
    <div id="biayaNotifWrap">
        @if(!$biayaCount)
        <div class="bg-orange-50 border border-orange-200 text-orange-700 px-4 py-3 rounded-xl flex items-center gap-3 shadow-sm">
            <i class="fas fa-clock text-orange-500 text-lg"></i>
            <div class="flex-1">
                Biaya harian hari ini <strong>belum tercatat</strong>
            </div>
            <a href="{{ route('biaya-harian.index') }}" class="px-4 py-1.5 bg-orange-500 hover:bg-orange-600 text-white rounded-lg text-sm font-medium">
                Catat Sekarang
            </a>
        </div>
        @endif
    </div>

    {{-- Empty State (AJAX) --}}
    <div id="emptyStateWrap"></div>

    {{-- Row 1: Financial KPI (AJAX update) --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4" id="rowKpi">
        @include('dashboard._partials.kpi-cards', [
            'periodLabels' => $periodLabels,
            'period' => $period,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'periodTotals' => $periodTotals,
            'prevPeriodTotals' => $prevPeriodTotals,
            'changeRev' => $changeRev,
            'changeBiaya' => $changeBiaya,
            'changeNet' => $changeNet,
        ])
    </div>

    {{-- Row 2: Extra Metrics (AJAX update) --}}
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4" id="rowMetrics">
        @include('dashboard._partials.metrics', [
            'profitMargin' => $profitMargin,
            'changeMargin' => $changeMargin,
            'avgProfitPerService' => $avgProfitPerService,
            'changeAvgProfit' => $changeAvgProfit,
            'periodTotals' => $periodTotals,
            'prevPeriodTotals' => $prevPeriodTotals,
            'changeBiaya' => $changeBiaya,
            'totalCustomers' => $totalCustomers,
        ])
    </div>

    {{-- Row 3: Gaji Detail + Bank Jago (AJAX update) --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4" id="rowGaji">
        @include('dashboard._partials.gaji', [
            'periodTotals' => $periodTotals,
            'prevPeriodTotals' => $prevPeriodTotals,
            'hariKerja' => $hariKerja,
            'changeJago' => $changeJago,
        ])
    </div>

    {{-- Row 4: Chart + Status --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white border border-gray-200 rounded-xl shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-1 flex items-center gap-2">
                <i class="fas fa-chart-bar text-yellow-500"></i>
                Grafik <span id="chartPeriodLabel">{{ $periodLabels[$period] }}</span>
            </h3>
            <p class="text-[10px] text-gray-400 italic mb-3">Perbandingan Pendapatan, Pengeluaran &amp; Keuntungan Bersih per periode</p>
            <div class="relative" style="height: 260px;">
                <canvas id="revenueChart" style="max-height:100%;max-width:100%;"></canvas>
            </div>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-1 flex items-center gap-2">
                <i class="fas fa-chart-donut text-yellow-500"></i>
                Status Service
            </h3>
            <p class="text-[10px] text-gray-400 italic mb-3">Persentase status pengerjaan service saat ini</p>
            <div class="relative" style="height: 260px;">
                <canvas id="statusChart" style="max-height:100%;max-width:100%;"></canvas>
            </div>
        </div>
    </div>

    {{-- Row 5: Recent Services --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                    <i class="fas fa-clock-rotate-left text-yellow-500"></i>
                    Service Terbaru
                </h3>
                <p class="text-[10px] text-gray-400 italic mt-0.5">10 service terakhir yang dicatat</p>
            </div>
            <a href="{{ route('services.index') }}" class="text-xs text-yellow-600 hover:text-yellow-700 font-medium">Lihat Semua →</a>
        </div>
        <div class="divide-y divide-gray-100" id="recentServicesList">
            @forelse($recentServices as $service)
            <div class="px-5 py-3 flex items-center justify-between hover:bg-gray-50/50">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-yellow-50 to-amber-100 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-mobile-alt text-yellow-600 text-xs"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $service->customer?->nama ?? '-' }}</p>
                        @php $kr = is_string($service->kerusakan) ? json_decode($service->kerusakan, true) : $service->kerusakan; @endphp
                        <p class="text-xs text-gray-500 truncate">{{ $service->type_hp }} — {{ is_array($kr) ? implode(', ', $kr) : $service->kerusakan }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    @php
                    $cls = match($service->status) { 'menunggu' => 'bg-yellow-100 text-yellow-700', 'dikerjakan' => 'bg-blue-100 text-blue-700', 'selesai' => 'bg-emerald-100 text-emerald-700', 'diambil' => 'bg-gray-100 text-gray-600', 'batal' => 'bg-red-100 text-red-700', default => 'bg-gray-100 text-gray-600' };
                    $label = match($service->status) { 'menunggu' => 'Menunggu', 'dikerjakan' => 'Dikerjakan', 'selesai' => 'Selesai', 'diambil' => 'Diambil', 'batal' => 'Batal', default => $service->status };
                    @endphp
                    <span class="inline-flex items-center justify-center px-3 py-1 rounded-full text-xs font-medium min-w-[90px] leading-none {{ $cls }}">{{ $label }}</span>
                    <span class="text-xs text-gray-400 w-10 text-right">{{ $service->tanggal->format('d/m') }}</span>
                </div>
            </div>
            @empty
            <div class="px-5 py-8 text-center text-gray-400">
                <i class="fas fa-inbox text-2xl mb-1 block"></i>
                Belum ada service
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var periodLabels = @json($periodLabels);
var periodIcons = @json($periodIcons);
var statusCounts = @json($statusCounts);
var chartInstance = null;
var statusChartInstance = null;

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
            btn.className = 'period-btn px-4 py-2 text-sm font-medium rounded-lg bg-gradient-to-r from-yellow-500 to-amber-500 text-white shadow-sm';
        } else {
            btn.className = 'period-btn px-4 py-2 text-sm font-medium rounded-lg bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 shadow-sm';
        }
    });
}

function updateChart(newChartData) {
    if (chartInstance) {
        chartInstance.data.labels = newChartData.labels;
        chartInstance.data.datasets[0].data = newChartData.revenue;
        chartInstance.data.datasets[1].data = newChartData.pengeluaran;
        chartInstance.data.datasets[2].data = newChartData.net_profit;
        chartInstance.update('none');
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
}

function updateRecentServices(services) {
    var wrap = document.getElementById('recentServicesList');
    if (!wrap || !services || services.length === 0) {
        if (wrap) wrap.innerHTML = '<div class="px-5 py-8 text-center text-gray-400"><i class="fas fa-inbox text-2xl mb-1 block"></i>Belum ada service</div>';
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
        html += '<div class="px-5 py-3 flex items-center justify-between hover:bg-gray-50/50">'
            + '<div class="flex items-center gap-3 min-w-0">'
            +   '<div class="w-8 h-8 rounded-full bg-gradient-to-br from-yellow-50 to-amber-100 flex items-center justify-center flex-shrink-0"><i class="fas fa-mobile-alt text-yellow-600 text-xs"></i></div>'
            +   '<div class="min-w-0">'
            +     '<p class="text-sm font-medium text-gray-900 truncate">' + s.customer_name + '</p>'
            +     '<p class="text-xs text-gray-500 truncate">' + s.type_hp + ' — ' + kerusakan + '</p>'
            +   '</div>'
            + '</div>'
            + '<div class="flex items-center gap-2 flex-shrink-0">'
            +   '<span class="inline-flex items-center justify-center px-3 py-1 rounded-full text-xs font-medium min-w-[90px] leading-none ' + st.cls + '">' + st.label + '</span>'
            +   '<span class="text-xs text-gray-400 w-10 text-right">' + s.tanggal + '</span>'
            + '</div></div>';
    });
    wrap.innerHTML = html;
}

function updateCards(d) {
    // Tab highlight
    updateTabActive(d.period, d.hasCustomRange);

    // Info bar
    document.getElementById('infoFromDate').textContent = d.fromDate;
    document.getElementById('infoToDate').textContent = d.toDate;
    document.getElementById('chartPeriodLabel').textContent = d.hasCustomRange ? 'Rentang Kustom' : (periodLabels[d.period] || 'Kustom');

    var periodLabel = d.hasCustomRange ? 'Rentang Kustom' : (periodLabels[d.period] || '');
    document.getElementById('infoPeriodLabel').textContent = periodLabel;

    // Badge
    var badge = document.getElementById('infoBadge');
    var isComp = d.isComplete;
    badge.className = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium ' + (isComp ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200');
    badge.innerHTML = '<i class="fas ' + (isComp ? 'fa-check-circle' : 'fa-hourglass-half') + '"></i> ' + (isComp ? 'Selesai' : 'Belum Selesai');

    var endDate = document.getElementById('infoEndDate');
    if (isComp) { endDate.style.display = 'none'; }
    else { endDate.style.display = ''; document.getElementById('infoEndDateText').textContent = d.toDate; }

    // Input date values
    document.getElementById('filterFrom').value = d.fromDateInput;
    document.getElementById('filterTo').value = d.toDateInput;

    // Biaya harian notif
    var bw = document.getElementById('biayaNotifWrap');
    if (!d.biayaCount) {
        bw.innerHTML = '<div class="bg-orange-50 border border-orange-200 text-orange-700 px-4 py-3 rounded-xl flex items-center gap-3 shadow-sm">'
            + '<i class="fas fa-clock text-orange-500 text-lg"></i>'
            + '<div class="flex-1">Biaya harian hari ini <strong>belum tercatat</strong></div>'
            + '<a href="{{ route('biaya-harian.index') }}" class="px-4 py-1.5 bg-orange-500 hover:bg-orange-600 text-white rounded-lg text-sm font-medium">Catat Sekarang</a>'
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

    // === Row 1: KPI Cards ===
    var kpi = document.getElementById('rowKpi');
    kpi.innerHTML = ''
        + '<div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden group">'
        +   '<div class="p-5"><div class="flex items-start justify-between">'
        +     '<div class="flex-1 min-w-0">'
        +       '<p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Pendapatan ' + periodLabel + '</p>'
        +       '<p class="text-[10px] text-gray-400">' + d.fromDate + ' — ' + d.toDate + '</p>'
        +       '<p class="text-[10px] text-gray-400 italic mt-0.5">Total uang masuk dari jasa service — belum dipotong biaya apapun</p>'
        +       '<p class="text-2xl font-bold mt-1" style="color:#1F2937;">' + fmtRp(d.revenue) + '</p>'
        +       (d.changeRev !== null
        ?         '<div class="flex items-center gap-1 mt-1.5"><i class="fas fa-' + (d.changeRev >= 0 ? 'arrow-up' : 'arrow-down') + ' text-' + (d.changeRev >= 0 ? 'emerald' : 'red') + '-500 text-xs"></i><span class="text-xs font-medium text-' + (d.changeRev >= 0 ? 'emerald' : 'red') + '-600">' + fmtPct(d.changeRev) + '</span><span class="text-xs text-gray-400">vs periode lalu</span></div>'
        :         '<div class="flex items-center gap-1 mt-1.5"><span class="text-xs font-medium text-gray-400">— vs periode lalu</span></div>')
        +     '</div>'
        +     '<div class="w-11 h-11 rounded-xl bg-gradient-to-br from-emerald-50 to-green-100 flex items-center justify-center shadow-md flex-shrink-0"><i class="fas fa-money-bill-wave text-emerald-600 text-lg"></i></div>'
        +   '</div><p class="text-xs text-gray-400 mt-2">' + d.services + ' service tercatat</p>'
        +   '<div class="mt-1"><svg class="w-full h-8" viewBox="0 0 200 30" preserveAspectRatio="none"><path d="M0,25 Q25,15 45,20 T85,10 T125,14 T165,6 T200,12" fill="none" stroke="#10B981" stroke-width="2" stroke-linecap="round"/><path d="M0,25 Q25,15 45,20 T85,10 T125,14 T165,6 T200,12 L200,30 L0,30 Z" fill="url(#sparkEmerald)" opacity="0.15"/></svg></div>'
        + '</div></div>'

        + '<div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden group">'
        +   '<div class="p-5"><div class="flex items-start justify-between">'
        +     '<div class="flex-1 min-w-0">'
        +       '<p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Pengeluaran ' + periodLabel + '</p>'
        +       '<p class="text-[10px] text-gray-400">' + d.fromDate + ' — ' + d.toDate + '</p>'
        +       '<p class="text-[10px] text-gray-400 italic mt-0.5">Sparepart + Kebutuhan Pribadi + Gaji + Biaya Operasional</p>'
        +       '<p class="text-2xl font-bold mt-1 text-red-500">' + fmtRp(d.pengeluaran) + '</p>'
        +       '<div class="flex items-center gap-2 mt-1.5 text-xs">'
        +         '<span class="text-gray-400">Modal:</span><span class="font-medium text-red-500">' + fmtRp(d.expenses) + '</span>'
        +         '<span class="text-gray-300">|</span>'
        +         '<span class="text-gray-400">Gaji:</span><span class="font-medium text-purple-600">' + fmtRp(d.total_gaji) + '</span>'
        +         '<span class="text-gray-300">|</span>'
        +         '<span class="text-gray-400">Operasional:</span><span class="font-medium text-orange-600">' + fmtRp(d.biaya_harian) + '</span>'
        +       '</div>'
        +       (d.changeRev !== null
        ?         '<div class="flex items-center gap-1 mt-1.5"><i class="fas fa-' + (d.changeBiaya >= 0 ? 'arrow-up' : 'arrow-down') + ' text-' + (d.changeBiaya >= 0 ? 'red' : 'emerald') + '-500 text-xs"></i><span class="text-xs font-medium text-' + (d.changeBiaya >= 0 ? 'red' : 'emerald') + '-600">' + fmtPct(d.changeBiaya) + '</span><span class="text-xs text-gray-400">vs periode lalu</span></div>'
        :         '<div class="flex items-center gap-1 mt-1.5"><span class="text-xs font-medium text-gray-400">— vs periode lalu</span></div>')
        +     '</div>'
        +     '<div class="w-11 h-11 rounded-xl bg-gradient-to-br from-red-50 to-rose-100 flex items-center justify-center shadow-md flex-shrink-0"><i class="fas fa-cart-shopping text-red-600 text-lg"></i></div>'
        +   '</div></div></div>'

        + '<div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden group">'
        +   '<div class="p-5"><div class="flex items-start justify-between">'
        +     '<div class="flex-1 min-w-0">'
        +       '<p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Keuntungan Bersih ' + periodLabel + '</p>'
        +       '<p class="text-[10px] text-gray-400">' + d.fromDate + ' — ' + d.toDate + '</p>'
        +       '<p class="text-[10px] text-gray-400 italic mt-0.5">Sisa uang setelah Pendapatan dikurangi seluruh Pengeluaran</p>'
        +       '<p class="text-2xl font-bold mt-1 ' + (d.net_profit >= 0 ? 'text-emerald-600' : 'text-red-600') + '">' + fmtRp(d.net_profit) + '</p>'
        +       '<p class="text-xs text-gray-400 mt-1.5">Pendapatan - Pengeluaran</p>'
        +       (d.changeNet !== null
        ?         '<div class="flex items-center gap-1 mt-1"><i class="fas fa-' + (d.changeNet >= 0 ? 'arrow-up' : 'arrow-down') + ' text-' + (d.changeNet >= 0 ? 'emerald' : 'red') + '-500 text-xs"></i><span class="text-xs font-medium text-' + (d.changeNet >= 0 ? 'emerald' : 'red') + '-600">' + fmtPct(d.changeNet) + '</span><span class="text-xs text-gray-400">vs periode lalu</span></div>'
        :         '<div class="flex items-center gap-1 mt-1"><span class="text-xs font-medium text-gray-400">— vs periode lalu</span></div>')
        +     '</div>'
        +     '<div class="w-11 h-11 rounded-xl bg-gradient-to-br from-emerald-50 to-green-100 flex items-center justify-center shadow-md flex-shrink-0"><i class="fas fa-coins text-emerald-600 text-lg"></i></div>'
        +   '</div></div></div>';

    // === Row 2: Metrics ===
    var metric = document.getElementById('rowMetrics');
    metric.innerHTML = ''
        + '<div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 flex items-center gap-4">'
        +   '<div class="w-12 h-12 rounded-xl bg-gradient-to-br from-yellow-50 to-amber-100 flex items-center justify-center flex-shrink-0 shadow-md"><i class="fas fa-percent text-yellow-600 text-lg"></i></div>'
        +   '<div><p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Margin Keuntungan</p>'
        +   '<p class="text-[10px] text-gray-400 italic leading-tight">Persentase keuntungan bersih dari pendapatan</p>'
        +   '<p class="text-xl font-bold mt-0.5" style="color:#1F2937;">' + Number(d.profitMargin).toFixed(1) + '%</p>'
        +   (d.prevRevenue > 0
        ?     '<div class="flex items-center gap-1 mt-0.5"><i class="fas fa-' + ((d.profitMargin - ((d.prevNet/d.prevRevenue)*100)) >= 0 ? 'arrow-up' : 'arrow-down') + ' text-' + ((d.profitMargin - ((d.prevNet/d.prevRevenue)*100)) >= 0 ? 'emerald' : 'red') + '-500 text-[10px]"></i><span class="text-[10px] font-medium text-gray-400">vs periode lalu</span></div>'
        :     '<div class="flex items-center gap-1 mt-0.5"><span class="text-[10px] font-medium text-gray-400">— vs periode lalu</span></div>')
        + '</div></div>'

        + '<div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 flex items-center gap-4">'
        +   '<div class="w-12 h-12 rounded-xl bg-gradient-to-br from-violet-50 to-purple-100 flex items-center justify-center flex-shrink-0 shadow-md"><i class="fas fa-calculator text-violet-600 text-lg"></i></div>'
        +   '<div><p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Rata-rata Keuntungan/Service</p>'
        +   '<p class="text-[10px] text-gray-400 italic leading-tight">Rata-rata keuntungan bersih dari setiap service</p>'
        +   '<p class="text-xl font-bold mt-0.5" style="color:#1F2937;">' + fmtRp(d.avgProfitPerService) + '</p>'
        +   (d.prevServices > 0
        ?     '<div class="flex items-center gap-1 mt-0.5"><span class="text-[10px] font-medium text-gray-400">vs periode lalu</span></div>'
        :     '<div class="flex items-center gap-1 mt-0.5"><span class="text-[10px] font-medium text-gray-400">— vs periode lalu</span></div>')
        + '</div></div>'

        + '<div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 flex items-center gap-4">'
        +   '<div class="w-12 h-12 rounded-xl bg-gradient-to-br from-orange-50 to-amber-100 flex items-center justify-center flex-shrink-0 shadow-md"><i class="fas fa-receipt text-orange-600 text-lg"></i></div>'
        +   '<div><p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Biaya Harian</p>'
        +   '<p class="text-[10px] text-gray-400 italic leading-tight">Biaya Operasional Harian (Bank Jago + Lainnya) — di luar gaji karyawan</p>'
        +   '<p class="text-xl font-bold mt-0.5 text-orange-600">' + fmtRp(d.biaya_harian) + '</p>'
        +   '<div class="flex items-center gap-2 mt-1 text-[10px] text-gray-400">'
        +     '<span>Bank Jago: <span class="font-medium text-gray-600">' + fmtRp(d.bank_jago) + '</span></span>'
        +     '<span class="text-gray-300">|</span>'
        +     '<span>Lainnya: <span class="font-medium text-gray-600">' + fmtRp(d.biaya_harian - d.bank_jago) + '</span></span>'
        +   '</div></div></div>'

        + '<div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 flex items-center gap-4">'
        +   '<div class="w-12 h-12 rounded-xl bg-gradient-to-br from-sky-50 to-blue-100 flex items-center justify-center flex-shrink-0 shadow-md"><i class="fas fa-users text-blue-600 text-lg"></i></div>'
        +   '<div><p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Total Pelanggan</p>'
        +   '<p class="text-[10px] text-gray-400 italic leading-tight">Jumlah seluruh pelanggan yang pernah tercatat</p>'
        +   '<p class="text-xl font-bold mt-0.5" style="color:#1F2937;">' + Number(d.totalCustomers).toLocaleString('id-ID') + '</p>'
        +   '<p class="text-xs text-gray-400">Seluruh masa</p></div></div>';

    // === Row 3: Gaji ===
    var gaji = document.getElementById('rowGaji');
    gaji.innerHTML = ''
        + '<div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 flex items-center gap-4">'
        +   '<div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center flex-shrink-0 shadow-md"><i class="fas fa-user-tie text-blue-600 text-lg"></i></div>'
        +   '<div><p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Gaji Edi</p>'
        +   '<p class="text-[10px] text-gray-400 italic leading-tight">Rp 60.000/hari × ' + d.hariKerja + ' hari kerja</p>'
        +   '<p class="text-xl font-bold mt-0.5" style="color:#1F2937;">' + fmtRp(d.gaji_edi) + '</p>'
        +   '<p class="text-xs text-gray-400 mt-0.5">' + d.hariKerja + ' hari kerja</p></div></div>'

        + '<div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 flex items-center gap-4">'
        +   '<div class="w-12 h-12 rounded-xl bg-gradient-to-br from-pink-50 to-rose-100 flex items-center justify-center flex-shrink-0 shadow-md"><i class="fas fa-user-tie text-rose-600 text-lg"></i></div>'
        +   '<div><p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Gaji Nur</p>'
        +   '<p class="text-[10px] text-gray-400 italic leading-tight">Rp 30.000/hari × ' + d.hariKerja + ' hari kerja</p>'
        +   '<p class="text-xl font-bold mt-0.5" style="color:#1F2937;">' + fmtRp(d.gaji_nur) + '</p>'
        +   '<p class="text-xs text-gray-400 mt-0.5">' + d.hariKerja + ' hari kerja</p></div></div>'

        + '<div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 flex items-center gap-4">'
        +   '<div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-50 to-sky-100 flex items-center justify-center flex-shrink-0 shadow-md"><i class="fas fa-piggy-bank text-blue-600 text-lg"></i></div>'
        +   '<div><p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Tabungan Bank Jago</p>'
        +   '<p class="text-xl font-bold mt-0.5 text-blue-600">' + fmtRp(d.bank_jago) + '</p>'
        +   (d.prevBiaya > 0
        ?     '<div class="flex items-center gap-1 mt-0.5"><span class="text-[10px] font-medium text-gray-400">vs periode lalu</span></div>'
        :     '<div class="flex items-center gap-1 mt-0.5"><span class="text-[10px] font-medium text-gray-400">— vs periode lalu</span></div>')
        + '</div></div>';

    // Chart
    updateChart(d.chartData);
}

function fetchData(params) {
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
                    { label: 'Pendapatan', data: chartData.revenue, backgroundColor: 'rgba(16,185,129,0.7)', borderColor: '#10B981', borderWidth: 1, borderRadius: 4, order: 3 },
                    { label: 'Pengeluaran', data: chartData.pengeluaran, backgroundColor: 'rgba(239,68,68,0.5)', borderColor: '#EF4444', borderWidth: 1, borderRadius: 4, order: 3 },
                    { label: 'Keuntungan Bersih', data: chartData.net_profit, backgroundColor: 'rgba(59,130,246,0.5)', borderColor: '#3B82F6', borderWidth: 1, borderRadius: 4 },
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, padding: 12, font: { size: 11 } } },
                    tooltip: { mode: 'index', intersect: false }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { callback: function(v) { return 'Rp ' + v.toLocaleString('id-ID'); }, font: { size: 10 } }, grid: { color: 'rgba(0,0,0,0.06)' } },
                    x: { grid: { display: false }, ticks: { maxRotation: 0, font: { size: 10 }, autoSkip: true, maxTicksLimit: 15 } }
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
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 10, font: { size: 11 } } } }
            }
        });
    }

    // Tab clicks
    document.querySelectorAll('.period-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            var p = this.dataset.period;
            fetchData({ period: p }).then(function(data) { updateCards(data); });
        });
    });

    // Apply date filter
    document.getElementById('applyDateBtn').addEventListener('click', function() {
        var from = document.getElementById('filterFrom').value;
        var to = document.getElementById('filterTo').value;
        if (!from || !to) { alert('Harap isi tanggal Dari dan Sampai terlebih dahulu.'); return; }
        if (from > to) { alert('Tanggal "Dari" tidak boleh lebih besar dari tanggal "Sampai".'); return; }
        fetchData({ from: from, to: to }).then(function(data) { updateCards(data); });
    });

    // Auto-refresh every 30s
    var currentParams = new URLSearchParams(window.location.search);
    var refreshParams = {};
    if (currentParams.get('period')) refreshParams.period = currentParams.get('period');
    else if (currentParams.get('from') && currentParams.get('to')) { refreshParams.from = currentParams.get('from'); refreshParams.to = currentParams.get('to'); }
    else refreshParams.period = '{{ $period }}';

    setInterval(function() {
        Promise.all([
            fetch('/dashboard/data?' + new URLSearchParams(refreshParams).toString()).then(function(r) { return r.json(); }),
            fetch('/dashboard/recent-services').then(function(r) { return r.json(); })
        ]).then(function(results) {
            var dashData = results[0];
            var recentData = results[1];
            updateCards(dashData);
            updateRecentServices(recentData.recentServices);
            updateStatusChart(recentData.statusCounts);
        }).catch(function() {});
    }, 30000);

    window.addEventListener('resize', function() {
        if (chartInstance) chartInstance.resize();
        if (statusChartInstance) statusChartInstance.resize();
    });
});
</script>
@endpush
