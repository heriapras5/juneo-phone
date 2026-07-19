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
    helpers: [],
    addTeknisiIndex: null,
    newTeknisiNama: '',
    newTeknisiWarna: 'bg-gray-100 text-gray-600 border-gray-200',
    kerusakanOptions: [],
    typeHpOptions: [],
    statusUpdating: null,
    showDeleteConfirm: false,
    deleteData: { url: '', message: '' },
    statusCounts: {},
    customers: [],
    rowState: {},
    customerSearch: '',
    customerDropdownOpen: false,
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
        items: [{ id: '', type_hp: '', kerusakanList: [], keterangan: '', kerusakanSearch: '', kerusakanDropdownOpen: false, typeHpSearch: '', typeHpDropdownOpen: false }],
    },
    init() {
        var pd = window._pageData || {};
        this.kerusakanOptions = pd.kerusakanOptions || [];
        this.typeHpOptions = pd.typeHpOptions || [];
        this.helpers = pd.helpers || [];
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
                var oldTeknisi = pd.old_teknisi || '';
                this.formData.items = pd.old_items.map(function(item) {
                    var k = item.kerusakan;
                    var raw = Array.isArray(k) ? k.slice() : (k ? [k] : []);
                    var kerusakanList = raw.map(function(x, xi) {
                        if (typeof x === 'object' && x !== null) {
                            if (x.pengeluaran != null) x.pengeluaran = String(Math.round(parseFloat(x.pengeluaran)));
                            if (x.jasa != null) x.jasa = String(Math.round(parseFloat(x.jasa)));
                            if (!x.teknisi) x.teknisi = item.teknisi || oldTeknisi;
                            return x;
                        }
                        return { nama: x, teknisi: item.teknisi || oldTeknisi, pengeluaran: xi === 0 && item.pengeluaran ? String(Math.round(parseFloat(item.pengeluaran))) : '', jasa: xi === 0 && item.jasa ? String(Math.round(parseFloat(item.jasa))) : '' };
                    });
                    return {
                        id: item.id || '',
                        type_hp: item.type_hp || '',
                        kerusakanList: kerusakanList,
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
            (item.kerusakanList || []).forEach(function(k) {
                if (typeof k === 'object') {
                    totalJ += parseFloat(k.jasa || '0') || 0;
                    totalP += parseFloat(k.pengeluaran || '0') || 0;
                }
            });
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
    quickAddCustomer(name) {
        if (this.customerSaving) return;
        var n = (name || '').trim();
        if (!n) return;
        this.customerSaving = true;
        var self = this;
        var csrf = document.querySelector('meta[name="csrf-token"]');
        fetch('/customers/quick', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf ? csrf.content : '' },
            body: JSON.stringify({ nama: n, no_hp: '' })
        })
        .then(function(r) {
            if (!r.ok) { return r.json().then(function(e) { throw new Error(e.message || 'Gagal simpan'); }); }
            return r.json();
        })
        .then(function(data) {
            if (data.success) {
                self.customers.push(data.customer);
                self.formData.customer_id = data.customer.id;
                self.customerDropdownOpen = false;
                self.customerSearch = '';
            }
        })
        .catch(function(e) { console.error(e); alert('Gagal menyimpan pelanggan. Silakan coba lagi.'); })
        .finally(function() { self.customerSaving = false; });
    },
    saveNewCustomerInline() {
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
                self.customerDropdownOpen = false;
                self.customerSearch = '';
                self.addCustomerForm = { nama: '', no_hp: '' };
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
        var exists = item.kerusakanList.some(function(x) {
            return (typeof x === 'object' ? x.nama : x) === option;
        });
        if (!exists) {
            var fallbackTeknisi = this.formData.teknisi || '';
            if (!fallbackTeknisi && item.kerusakanList.length > 0) {
                var first = item.kerusakanList[0];
                fallbackTeknisi = (typeof first === 'object' && first !== null) ? (first.teknisi || '') : '';
            }
            item.kerusakanList.push({ nama: option, teknisi: fallbackTeknisi, pengeluaran: '', jasa: '' });
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
    removeKerusakan(index, ki) {
        var item = this.formData.items[index];
        if (!item || !item.kerusakanList) return;
        item.kerusakanList.splice(ki, 1);
    },
    kerusakanNama(k) {
        return (typeof k === 'object' && k !== null) ? (k.nama || '') : (k || '');
    },
    kerusakanTerpilih(list, option) {
        return list.some(function(x) {
            return (typeof x === 'object' ? x.nama : x) === option;
        });
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
    formatCurrencyKerusakan(index, ki, field, event) {
        var raw = event.target.value.replace(/\D/g, '');
        var item = this.formData.items[index];
        if (item && item.kerusakanList && item.kerusakanList[ki]) {
            item.kerusakanList[ki][field] = raw === '0' ? '' : raw;
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
        if (type === 'action') {
            this.rowState[id][key] = 'position:fixed;right:' + (window.innerWidth - rect.right + 8) + 'px;top:' + (rect.bottom + 4) + 'px;';
        } else {
            this.rowState[id][key] = 'position:fixed;left:' + (rect.left - 40) + 'px;top:' + (rect.bottom + 4) + 'px;';
        }
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
            items: [{ id: '', type_hp: '', kerusakanList: [], keterangan: '', kerusakanSearch: '', kerusakanDropdownOpen: false, typeHpSearch: '', typeHpDropdownOpen: false }],
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
        if (!this.formData.teknisi && data.service_items) {
            for (var si = 0; si < data.service_items.length; si++) {
                var sk = data.service_items[si].kerusakan;
                if (Array.isArray(sk)) {
                    for (var skIdx = 0; skIdx < sk.length; skIdx++) {
                        if (sk[skIdx] && sk[skIdx].teknisi) {
                            this.formData.teknisi = sk[skIdx].teknisi;
                            break;
                        }
                    }
                }
                if (this.formData.teknisi) break;
            }
        }
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
        var dataTeknisi = data.teknisi || '';
        this.formData.items = srcItems.map(function(item) {
            var k = item.kerusakan;
            var raw = Array.isArray(k) ? k.slice() : (k ? [k] : []);
            var kerusakanList = raw.map(function(x, xi) {
                if (typeof x === 'object' && x !== null) {
                    if (x.pengeluaran != null) x.pengeluaran = String(Math.round(parseFloat(x.pengeluaran)));
                    if (x.jasa != null) x.jasa = String(Math.round(parseFloat(x.jasa)));
                    if (!x.teknisi) x.teknisi = item.teknisi || dataTeknisi;
                    return x;
                }
                return { nama: x, teknisi: item.teknisi || dataTeknisi, pengeluaran: xi === 0 && item.pengeluaran ? String(Math.round(parseFloat(item.pengeluaran))) : '', jasa: xi === 0 && item.jasa ? String(Math.round(parseFloat(item.jasa))) : '' };
            });
            return {
                id: item.id || '',
                type_hp: item.type_hp || '',
                kerusakanList: kerusakanList,
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
        this.formData.items.push({ id: '', type_hp: '', kerusakanList: [], keterangan: '', kerusakanSearch: '', kerusakanDropdownOpen: false, typeHpSearch: '', typeHpDropdownOpen: false });
        this.recalculateTotals();
    },
    removeItem(index) {
        if (this.formData.items.length <= 1) return;
        this.formData.items.splice(index, 1);
        this.recalculateTotals();
    },
    openAddTeknisi(index) {
        this.addTeknisiIndex = index;
        this.newTeknisiNama = '';
        this.newTeknisiWarna = 'bg-gray-100 text-gray-600 border-gray-200';
    },
    saveNewTeknisi() {
        var name = this.newTeknisiNama.trim();
        if (!name) { alert('Nama teknisi harus diisi.'); return; }
        var self = this;
        var csrf = document.querySelector('meta[name="csrf-token"]');
        var idx = this.addTeknisiIndex;
        fetch('/helpers/quick', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf ? csrf.content : '' },
            body: JSON.stringify({ nama: name, warna: this.newTeknisiWarna })
        })
        .then(function(r) {
            if (!r.ok) { return r.json().then(function(e) { throw new Error(e.message || 'Gagal simpan'); }); }
            return r.json();
        })
        .then(function(data) {
            if (data.success) {
                self.helpers.push(data.helper);
                self.newTeknisiNama = '';
                self.addTeknisiIndex = null;
            }
        })
        .catch(function(e) { console.error(e); alert('Gagal menyimpan teknisi. Silakan coba lagi.'); });
    },
    quickUpdateStatus(serviceId, newStatus) {
        if (this.statusUpdating) return;
        this.sendStatusUpdate(serviceId, newStatus);
    },
    sendStatusUpdate(serviceId, newStatus) {
        if (this.statusUpdating) return;
        this.statusUpdating = serviceId;
        var self = this;
        var csrf = document.querySelector('meta[name="csrf-token"]');
        if (!csrf) { alert('CSRF token tidak ditemukan.'); this.statusUpdating = null; return; }
        fetch('/services/' + serviceId + '/status', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf.content },
            body: JSON.stringify({ status: newStatus })
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

Alpine.data('sparepartPage', () => ({
    showModal: false,
    editingId: null,
    loading: false,
    kategoriSearch: '',
    kategoriDropdownOpen: false,
    formData: {
        nama_barang: '',
        kategori: '',
        type_hp: '',
        stok: '',
        harga_beli: '',
        toko: '',
        keterangan: '',
    },
    get filteredKategori() {
        var q = (this.kategoriSearch || '').toLowerCase();
        var pd = window._pageData || {};
        var options = pd.kategoriOptions || [];
        if (!q) return options;
        return options.filter(function(o) {
            return (o || '').toLowerCase().indexOf(q) !== -1;
        });
    },
    selectKategori(name) {
        this.formData.kategori = name;
        this.kategoriDropdownOpen = false;
        this.kategoriSearch = '';
    },
    quickAddKategori() {
        var name = (this.kategoriSearch || '').trim();
        if (!name) { alert('Nama kategori harus diisi.'); return; }
        var self = this;
        var csrf = document.querySelector('meta[name="csrf-token"]');
        if (!csrf) return;
        fetch('/spareparts/kategori/quick', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf.content },
            body: JSON.stringify({ name: name })
        })
        .then(function(r) {
            if (!r.ok) { return r.json().then(function(e) { throw new Error(e.message || 'Gagal simpan'); }); }
            return r.json();
        })
        .then(function(data) {
            if (data.success) {
                var pd = window._pageData || {};
                if (pd.kategoriOptions) pd.kategoriOptions.push(data.kategori.name);
                self.formData.kategori = data.kategori.name;
                self.kategoriDropdownOpen = false;
                self.kategoriSearch = '';
            } else {
                alert(data.message || 'Gagal menyimpan kategori.');
            }
        })
        .catch(function(e) { console.error(e); alert('Gagal menyimpan kategori. Silakan coba lagi.'); });
    },
    init() {
        this.$watch('showModal', function(val) {
            document.body.style.overflow = val ? 'hidden' : '';
        });
        this.$watch('showTokoModal', function(val) {
            document.body.style.overflow = val ? 'hidden' : '';
        });
        this.initToko();
        var pd = window._pageData || {};
        if (pd.hasErrors && !pd.showTokoModal) {
            this.showModal = true;
            if (pd._method === 'PUT') {
                this.editingId = pd.editing_id;
            }
            this.formData.nama_barang = pd.old_nama_barang || '';
            this.formData.kategori = pd.old_kategori || '';
            this.formData.type_hp = pd.old_type_hp || '';
            this.formData.stok = pd.old_stok || '';
            this.formData.harga_beli = pd.old_harga_beli || '';
            this.formData.toko = pd.old_toko || '';
            this.formData.keterangan = pd.old_keterangan || '';
        }
    },
    openCreateModal() {
        this.editingId = null;
        this.formData = { nama_barang: '', kategori: '', type_hp: '', stok: '', harga_beli: '', toko: '', keterangan: '' };
        this.showModal = true;
        this.$nextTick(() => { if (this.$refs.firstField) this.$refs.firstField.focus(); });
    },
    openEditModal(data) {
        this.editingId = data.id;
        this.formData = {
            nama_barang: data.nama_barang || '',
            kategori: data.kategori || '',
            type_hp: data.type_hp || '',
            stok: '',
            harga_beli: data.harga_beli ? String(Math.round(parseFloat(data.harga_beli))) : '',
            toko: data.toko || '',
            keterangan: data.keterangan || '',
        };
        this.showModal = true;
        this.$nextTick(() => { if (this.$refs.firstField) this.$refs.firstField.focus(); });
    },
    closeModal() {
        this.showModal = false;
    },
    // Toko management
    showTokoModal: false,
    tokoEditingId: null,
    tokoNama: '',
    initToko() {
        var pd = window._pageData || {};
        if (pd.showTokoModal) {
            this.showTokoModal = true;
            this.tokoEditingId = pd.toko_editing_id || null;
            // tokoNama will be set by x-model if form has old value
        }
    },
    openTokoModal() {
        this.tokoEditingId = null;
        this.tokoNama = '';
        this.showTokoModal = true;
    },
    openTokoEditModal(data) {
        this.tokoEditingId = data.id;
        this.tokoNama = data.nama;
        this.showTokoModal = true;
    },
    closeTokoModal() {
        this.showTokoModal = false;
    }
}));

Alpine.data('scrollSync', () => ({
    syncFromTable(el) {
        const mirror = this.$refs.mirror;
        if (mirror) mirror.scrollLeft = el.scrollLeft;
    },
    syncFromMirror(el) {
        const table = this.$refs.table;
        if (table) table.scrollLeft = el.scrollLeft;
    },
}));

window.Alpine = Alpine;
Alpine.start();
