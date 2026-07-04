import './bootstrap';
import '@fontsource-variable/instrument-sans';
import Alpine from 'alpinejs';

Alpine.data('servicePage', () => ({
    todayLocal() {
        // Selalu pakai tanggal lokal (WIB), bukan UTC dari toISOString()
        var d = new Date();
        var y = d.getFullYear();
        var m = String(d.getMonth() + 1).padStart(2, '0');
        var day = String(d.getDate()).padStart(2, '0');
        return y + '-' + m + '-' + day;
    },
    showModal: false,
    editingId: null,
    loading: false,
    kerusakanOptions: [],
    typeHpOptions: [],
    statusUpdating: null,
    showDeleteConfirm: false,
    deleteData: { url: '', message: '' },
    statusCounts: {},
    customers: [],
    rowState: {},
    customerSearch: '',
    showGaransiModal: false,
    garansiPending: { serviceId: null, status: null, garansi: '' },
    customerDropdownOpen: false,
    showAddCustomer: false,
    addCustomerForm: { nama: '', no_hp: '' },
    customerSaving: false,
    totalPengeluaran: 0,
    totalJasa: 0,
    formData: {
        tanggal: '',
        customer_id: '',
        keterangan: '',
        status: 'menunggu',
        tanggal_selesai: '',
        estimasi_selesai: '',
        teknisi: '',
        garansi: '',
        allowedStatuses: [],
        items: [{ id: '', type_hp: '', kerusakanList: [], jasa: '', pengeluaran: '', keterangan: '', kerusakanSearch: '', kerusakanDropdownOpen: false, typeHpSearch: '', typeHpDropdownOpen: false }],
    },
    init() {
        var pd = window._pageData || {};
        this.kerusakanOptions = pd.kerusakanOptions || [];
        this.typeHpOptions = pd.typeHpOptions || [];
        this.statusCounts = pd.statusCounts || {};
        this.customers = pd.customers || [];
        this.formData.tanggal = pd.tanggal || this.todayLocal();
        if (pd.hasErrors) {
            this.showModal = true;
            if (pd._method === 'PUT') {
                this.editingId = pd.editing_id;
            }
            this.formData.tanggal = pd.old_tanggal || this.formData.tanggal;
            this.formData.customer_id = pd.old_customer_id || '';
            this.formData.keterangan = pd.old_keterangan || '';
            this.formData.status = pd.old_status || 'menunggu';
            var errTransitions = (pd.allowedTransitions || {})[this.formData.status] || [];
            this.formData.allowedStatuses = [this.formData.status].concat(errTransitions);
            this.formData.tanggal_selesai = pd.old_tanggal_selesai || '';
            this.formData.estimasi_selesai = pd.old_estimasi_selesai || '';
            this.formData.teknisi = pd.old_teknisi || '';
            if (pd.old_items && pd.old_items.length > 0) {
                this.formData.items = pd.old_items.map(function(item) {
                    var k = item.kerusakan;
                    var kerusakanList = Array.isArray(k) ? k.slice() : (k ? [k] : []);
                    var cleanJasa = item.jasa != null ? String(Math.round(parseFloat(item.jasa))) : '';
                    var cleanPengeluaran = item.pengeluaran != null ? String(Math.round(parseFloat(item.pengeluaran))) : '';
                    return {
                        id: item.id || '',
                        type_hp: item.type_hp || '',
                        kerusakanList: kerusakanList,
                        jasa: cleanJasa,
                        pengeluaran: cleanPengeluaran,
                        keterangan: item.keterangan || '',
                        kerusakanSearch: '',
                        kerusakanDropdownOpen: false,
                        typeHpSearch: '',
                        typeHpDropdownOpen: false,
                    };
                });
                this.recalculateTotals();
            }
        }
        var self = this;
        document.addEventListener('click', function(e) {
            for (var id in self.rowState) {
                if (!self.rowState[id].openStatus && !self.rowState[id].openAction) continue;
                var el = document.querySelector('[data-row="' + id + '"]');
                var statusDropdown = el ? el.querySelector('[data-status-dropdown]') : null;
                var actionDropdown = el ? el.querySelector('[data-action-dropdown]') : null;
                if (el && !el.contains(e.target) && !statusDropdown?.contains(e.target) && !actionDropdown?.contains(e.target)) {
                    self.closeRowDropdowns(id);
                }
            }
        });
        document.addEventListener('scroll', function() {
            for (var id in self.rowState) {
                if (self.rowState[id].openStatus || self.rowState[id].openAction) {
                    self.closeRowDropdowns(id);
                }
            }
        }, true);
    },
    get selectedCustomer() {
        var id = this.formData.customer_id;
        return id ? this.customers.find(function(c) { return c.id == id; }) || null : null;
    },
    recalculateTotals() {
        var totalJ = 0, totalP = 0;
        (this.formData.items || []).forEach(function(item) {
            totalJ += parseFloat(item.jasa || '0') || 0;
            totalP += parseFloat(item.pengeluaran || '0') || 0;
        });
        this.totalJasa = totalJ;
        this.totalPengeluaran = totalP;
    },
    get filteredCustomers() {
        var q = (this.customerSearch || '').toLowerCase();
        if (!q) return this.customers;
        return this.customers.filter(function(c) {
            return (c.nama || '').toLowerCase().indexOf(q) !== -1 ||
                   (c.no_hp || '').indexOf(q) !== -1;
        });
    },
    selectCustomer(customerId) {
        this.formData.customer_id = customerId;
        this.customerDropdownOpen = false;
        this.customerSearch = '';
    },
    openAddCustomer() {
        this.addCustomerForm = { nama: '', no_hp: '' };
        this.showAddCustomer = true;
    },
    saveNewCustomer() {
        if (this.customerSaving) return;
        var name = this.addCustomerForm.nama.trim();
        if (!name) { alert('Nama pelanggan harus diisi.'); return; }
        this.customerSaving = true;
        var self = this;
        var csrf = document.querySelector('meta[name="csrf-token"]');
        fetch('/customers/quick', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf ? csrf.content : '' },
            body: JSON.stringify({ nama: name, no_hp: this.addCustomerForm.no_hp.trim() })
        })
        .then(function(r) {
            if (!r.ok) { return r.json().then(function(e) { throw new Error(e.message || 'Gagal simpan'); }); }
            return r.json();
        })
        .then(function(data) {
            if (data.success) {
                self.customers.push(data.customer);
                self.formData.customer_id = data.customer.id;
                self.showAddCustomer = false;
                self.customerDropdownOpen = false;
                self.customerSearch = '';
            }
        })
        .catch(function(e) { console.error(e); alert('Gagal menyimpan pelanggan. Silakan coba lagi.'); })
        .finally(function() { self.customerSaving = false; });
    },
    filteredKerusakanForItem(index) {
        var item = this.formData.items[index];
        if (!item) return [];
        var q = (item.kerusakanSearch || '').toLowerCase();
        if (!q) return this.kerusakanOptions;
        return this.kerusakanOptions.filter(function(o) {
            return (o || '').toLowerCase().indexOf(q) !== -1;
        });
    },
    selectKerusakanItem(index, option) {
        var item = this.formData.items[index];
        if (!item) return;
        if (!item.kerusakanList) item.kerusakanList = [];
        if (item.kerusakanList.indexOf(option) === -1) {
            item.kerusakanList.push(option);
        }
        item.kerusakanDropdownOpen = false;
        item.kerusakanSearch = '';
        if (this.kerusakanOptions.indexOf(option) === -1) {
            this.kerusakanOptions.push(option);
            var csrf = document.querySelector('meta[name="csrf-token"]');
            if (csrf) {
                fetch('/settings/damage-categories/quick', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf.content },
                    body: JSON.stringify({ name: option })
                }).then(function(r) {
                    if (!r.ok) console.error('Gagal menyimpan Kategori Kerusakan');
                }).catch(function(e) { console.error('Gagal menyimpan Kategori Kerusakan:', e); });
            }
        }
    },
    filteredTypeHpForItem(index) {
        var item = this.formData.items[index];
        if (!item) return [];
        var q = (item.typeHpSearch || '').toLowerCase();
        if (!q) return this.typeHpOptions;
        return this.typeHpOptions.filter(function(o) {
            return (o || '').toLowerCase().indexOf(q) !== -1;
        });
    },
    selectTypeHpItem(index, option) {
        var item = this.formData.items[index];
        if (!item) return;
        item.type_hp = option;
        item.typeHpDropdownOpen = false;
        item.typeHpSearch = '';
        if (this.typeHpOptions.indexOf(option) === -1) {
            this.typeHpOptions.push(option);
            var csrf = document.querySelector('meta[name="csrf-token"]');
            if (csrf) {
                fetch('/settings/type-hp/quick', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf.content },
                    body: JSON.stringify({ name: option })
                }).then(function(r) {
                    if (!r.ok) console.error('Gagal menyimpan Type HP');
                }).catch(function(e) { console.error('Gagal menyimpan Type HP:', e); });
            }
        }
    },
    displayNumber(val) {
        if (!val && val !== 0) return '';
        var num = parseFloat(String(val));
        return !isNaN(num) && num > 0 ? Math.round(num).toLocaleString('id-ID') : '';
    },
    formatCurrencyItem(index, field, event) {
        var raw = event.target.value.replace(/\D/g, '');
        if (this.formData.items[index]) {
            this.formData.items[index][field] = raw === '0' ? '' : raw;
        }
        this.recalculateTotals();
    },
    toggleStatus(id, event) {
        if (!this.rowState[id]) this.rowState[id] = { openStatus: false, openAction: false };
        if (!this.rowState[id].openStatus) {
            this.rowState[id].openStatus = true;
            this.rowState[id].openAction = false;
            this.$nextTick(() => { this.positionDropdown(id, 'status', event.target); });
        } else {
            this.rowState[id].openStatus = false;
        }
    },
    toggleAction(id, event) {
        if (!this.rowState[id]) this.rowState[id] = { openStatus: false, openAction: false };
        if (!this.rowState[id].openAction) {
            this.rowState[id].openAction = true;
            this.rowState[id].openStatus = false;
            this.$nextTick(() => { this.positionDropdown(id, 'action', event.target); });
        } else {
            this.rowState[id].openAction = false;
        }
    },
    positionDropdown(id, type, btn) {
        var rect = btn.getBoundingClientRect();
        var key = type + 'Style';
        if (!this.rowState[id]) this.rowState[id] = {};
        this.rowState[id][key] = 'position:fixed;left:' + (rect.left - 40) + 'px;top:' + (rect.bottom + 4) + 'px;';
    },
    closeRowDropdowns(id) {
        if (this.rowState[id]) {
            this.rowState[id].openStatus = false;
            this.rowState[id].openAction = false;
        }
    },
    openCreateModal() {
        this.editingId = null;
        this.formData = {
            tanggal: this.todayLocal(),
            customer_id: '',
            keterangan: '',
            status: 'menunggu',
            tanggal_selesai: '',
            estimasi_selesai: '',
            teknisi: '',
            garansi: '',
            allowedStatuses: [],
            items: [{ id: '', type_hp: '', kerusakanList: [], jasa: '', pengeluaran: '', keterangan: '', kerusakanSearch: '', kerusakanDropdownOpen: false, typeHpSearch: '', typeHpDropdownOpen: false }],
        };
        this.recalculateTotals();
        this.showModal = true;
        this.$nextTick(() => { if (this.$refs.firstField) this.$refs.firstField.focus(); });
    },
    openEditModal(data) {
        this.editingId = data.id;
        this.formData.tanggal = String(data.tanggal || '').slice(0, 10);
        this.formData.customer_id = data.customer_id;
        this.formData.keterangan = data.keterangan || '';
        this.formData.status = data.status || 'menunggu';
        this.formData.tanggal_selesai = String(data.tanggal_selesai || '').slice(0, 10);
        this.formData.estimasi_selesai = String(data.estimasi_selesai || '').slice(0, 10);
        this.formData.teknisi = data.teknisi || '';
        this.formData.garansi = data.garansi || '';
        var transitions = (window._pageData?.allowedTransitions || {})[this.formData.status] || [];
        this.formData.allowedStatuses = [this.formData.status].concat(transitions);
        var srcItems = data.service_items || [];
        if (srcItems.length === 0) {
            var fallbackKerusakan = data.kerusakan || '';
            var parsed;
            try { parsed = JSON.parse(fallbackKerusakan); } catch(e) { parsed = null; }
            srcItems = [{
                type_hp: data.type_hp || '',
                kerusakan: Array.isArray(parsed) ? parsed : (fallbackKerusakan || ''),
                jasa: data.jasa,
                pengeluaran: data.pengeluaran,
            }];
        }
        this.formData.items = srcItems.map(function(item) {
            var k = item.kerusakan;
            var cleanJasa = item.jasa != null ? String(Math.round(parseFloat(item.jasa))) : '';
            var cleanPengeluaran = item.pengeluaran != null ? String(Math.round(parseFloat(item.pengeluaran))) : '';
            return {
                id: item.id || '',
                type_hp: item.type_hp || '',
                kerusakanList: Array.isArray(k) ? k.slice() : (k ? [k] : []),
                jasa: cleanJasa,
                pengeluaran: cleanPengeluaran,
                keterangan: item.keterangan || '',
                kerusakanSearch: '',
                kerusakanDropdownOpen: false,
                typeHpSearch: '',
                typeHpDropdownOpen: false,
            };
        });
        this.recalculateTotals();
        this.showModal = true;
        this.$nextTick(() => { if (this.$refs.firstField) this.$refs.firstField.focus(); });
    },
    addItem() {
        this.formData.items.push({ id: '', type_hp: '', kerusakanList: [], jasa: '', pengeluaran: '', keterangan: '', kerusakanSearch: '', kerusakanDropdownOpen: false, typeHpSearch: '', typeHpDropdownOpen: false });
        this.recalculateTotals();
    },
    removeItem(index) {
        if (this.formData.items.length <= 1) return;
        this.formData.items.splice(index, 1);
        this.recalculateTotals();
    },
    quickUpdateStatus(serviceId, newStatus) {
        if (this.statusUpdating) return;
        if (newStatus === 'diambil') {
            this.garansiPending = { serviceId: serviceId, status: newStatus, garansi: '' };
            this.showGaransiModal = true;
            return;
        }
        this.sendStatusUpdate(serviceId, newStatus, '');
    },
    sendStatusUpdate(serviceId, newStatus, garansi) {
        if (this.statusUpdating) return;
        this.statusUpdating = serviceId;
        var self = this;
        var csrf = document.querySelector('meta[name="csrf-token"]');
        if (!csrf) { alert('CSRF token tidak ditemukan.'); this.statusUpdating = null; return; }
        var body = { status: newStatus };
        if (garansi) { body.garansi = garansi; }
        fetch('/services/' + serviceId + '/status', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf.content },
            body: JSON.stringify(body)
        })
        .then(function(r) {
            return r.json().then(function(data) {
                if (!r.ok) { throw new Error(data.message || 'Gagal memperbarui status.'); }
                return data;
            });
        })
        .then(function(data) {
                    if (data.success) {
                self.statusCounts = data.statusCount;
                var row = document.querySelector('tr[data-row="' + serviceId + '"]');
                if (row) {
                    if (newStatus === 'batal') {
                        // Kolom: 0:No 1:NoService 2:Tgl 3:TglSelesai 4:Pelanggan 5:Status 6:TypeHP 7:Kerusakan 8:Modal 9:Jasa 10:Keuntungan
                        var cells = row.querySelectorAll('td');
                        if (cells[8]) cells[8].textContent = 'Rp 0';
                        if (cells[9]) cells[9].textContent = 'Rp 0';
                        if (cells[10]) { cells[10].textContent = 'Rp 0'; cells[10].className = cells[10].className.replace(/text-\w+-\d+/g, '').trim() + ' text-emerald-600'; }
                    }
                    var badge = row.querySelector('[data-status-badge]');
                    if (badge) {
                        var nodes = badge.childNodes;
                        for (var i = 0; i < nodes.length; i++) {
                            if (nodes[i].nodeType === 3) {
                                nodes[i].textContent = ' ' + data.label + ' ';
                                break;
                            }
                        }
                        badge.className = badge.className
                            .replace(/bg-\w+-\d+/g, '')
                            .replace(/text-\w+-\d+/g, '')
                            .replace(/border-\w+-\d+/g, '')
                            .replace(/\s+/g, ' ').trim();
                        (data.class || '').split(' ').forEach(function(c) {
                            if (c) badge.classList.add(c);
                        });
                    }
                    var dropdown = row.querySelector('[data-status-dropdown]');
                    if (dropdown) {
                        var rowId = parseInt(row.getAttribute('data-row'));
                        if (rowId) self.closeRowDropdowns(rowId);
                        var labels = {'menunggu':'Menunggu','dikerjakan':'Dikerjakan','selesai':'Selesai','diambil':'Diambil','batal':'Batal'};
                        var allowedNext = data.nextTransitions || [];
                        var html = '';
                        allowedNext.forEach(function(s) {
                            html += '<button type="button" class="block w-full text-left px-3 py-1.5 text-sm hover:bg-gray-100 text-gray-700" data-quick-status="' + s + '">' + (labels[s] || s) + '</button>';
                        });
                        if (allowedNext.length === 0) {
                            html = '<div class="px-3 py-2 text-xs text-gray-400 italic">Tidak ada aksi tersedia</div>';
                        }
                        dropdown.innerHTML = html;
                        dropdown.querySelectorAll('[data-quick-status]').forEach(function(btn) {
                            btn.addEventListener('click', function(e) {
                                e.stopPropagation();
                                var id = parseInt(btn.closest('[data-row]').getAttribute('data-row'));
                                self.closeRowDropdowns(id);
                                self.quickUpdateStatus(id, btn.getAttribute('data-quick-status'));
                            });
                        });
                    }
                }
            } else {
                alert(data.message || 'Gagal memperbarui status.');
            }
        })
        .catch(function(e) { console.error(e); alert(e.message || 'Terjadi kesalahan. Silakan coba lagi.'); })
        .finally(function() { self.statusUpdating = null; });
    },
    confirmGaransi() {
        this.showGaransiModal = false;
        var p = this.garansiPending;
        this.sendStatusUpdate(p.serviceId, p.status, p.garansi);
    },
    skipGaransi() {
        this.showGaransiModal = false;
        var p = this.garansiPending;
        this.sendStatusUpdate(p.serviceId, p.status, '');
    }
}));

Alpine.data('customerPage', () => ({
    showModal: false,
    editingId: null,
    loading: false,
    formData: { nama: '', no_hp: '', alamat: '' },
    init() {
        var self = this;
        this.$watch('showModal', function(val) {
            document.body.style.overflow = val ? 'hidden' : '';
        });
        var pd = window._pageData || {};
        if (pd.hasErrors) {
            this.showModal = true;
            if (pd._method === 'PUT') {
                this.editingId = pd.editing_id;
            }
            this.formData.nama = pd.old_nama || '';
            this.formData.no_hp = pd.old_no_hp || '';
            this.formData.alamat = pd.old_alamat || '';
        }
    },
    openCreateModal() {
        this.editingId = null;
        this.formData = { nama: '', no_hp: '', alamat: '' };
        this.showModal = true;
    },
    openEditModal(data) {
        this.editingId = data.id;
        this.formData = { nama: data.nama, no_hp: data.no_hp || '', alamat: data.alamat || '' };
        this.showModal = true;
    }
}));

window.Alpine = Alpine;
Alpine.start();
