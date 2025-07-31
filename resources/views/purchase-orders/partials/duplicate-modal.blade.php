<div x-show="showDuplicateModal" x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0" @click.self="showDuplicateModal = false"
    @keydown.escape.window="showDuplicateModal = false"
    class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" style="display: none;">
    <div x-show="showDuplicateModal" x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200 transform"
        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
        class="bg-white rounded-2xl shadow-2xl max-w-lg w-full">

        <div class="p-6" x-data="{ loading: false }">
            <div class="w-16 h-16 bg-teal-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-copy text-2xl text-teal-600"></i>
            </div>

            <h3 class="text-xl font-bold text-gray-900 text-center mb-6">Duplicate Purchase Order</h3>

            <p class="text-gray-600 text-center mb-6">
                Buat duplikasi dari PO <strong>{{ $purchaseOrder->po_number }}</strong>?
                PO baru akan dibuat dengan status draft dan dapat diedit sesuai kebutuhan.
            </p>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                <div class="flex items-start space-x-3">
                    <i class="fas fa-info-circle text-blue-600 mt-0.5"></i>
                    <div class="text-sm text-blue-800">
                        <p class="font-medium mb-1">Yang akan diduplikasi:</p>
                        <ul class="text-xs space-y-1">
                            <li>• Semua item dan quantity</li>
                            <li>• Unit price dan total amount</li>
                            <li>• Supplier (jika sudah dipilih)</li>
                            <li>• Notes dasar</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <div class="flex items-start space-x-3">
                    <i class="fas fa-exclamation-triangle text-yellow-600 mt-0.5"></i>
                    <div class="text-sm text-yellow-800">
                        <p class="font-medium mb-1">Yang tidak diduplikasi:</p>
                        <ul class="text-xs space-y-1">
                            <li>• Workflow status (akan dimulai dari draft)</li>
                            <li>• Approval history</li>
                            <li>• Payment information</li>
                            <li>• Goods received data</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <button type="button" @click="showDuplicateModal = false"
                    class="flex-1 px-4 py-3 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-200 flex items-center justify-center space-x-2">
                    <i class="fas fa-times"></i>
                    <span>Batal</span>
                </button>
                <button type="button" @click="duplicatePO()" :disabled="loading"
                    class="flex-1 px-4 py-3 bg-gradient-to-r from-teal-600 to-teal-700 text-white rounded-xl hover:from-teal-700 hover:to-teal-800 transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl disabled:opacity-50">
                    <i class="fas fa-copy" :class="{ 'animate-spin fa-spinner': loading }"></i>
                    <span x-text="loading ? 'Menduplikasi...' : 'Duplicate PO'"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function duplicatePO() {
    this.loading = true;

    fetch(`{{ route('purchase-orders.duplicate', $purchaseOrder) }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showToast('PO berhasil diduplikasi!', 'success');

            // Close modal
            this.showDuplicateModal = false;

            // Redirect to new PO after short delay
            setTimeout(() => {
                window.location.href = data.redirect_url;
            }, 1500);
        } else {
            showToast(data.message || 'Gagal menduplikasi PO', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Terjadi kesalahan saat menduplikasi PO', 'error');
    })
    .finally(() => {
        this.loading = false;
    });
}

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 z-50 px-4 py-3 rounded-xl shadow-lg transition-all duration-300 ${
        type === 'success' ? 'bg-green-100 border border-green-400 text-green-700' :
        type === 'error' ? 'bg-red-100 border border-red-400 text-red-700' :
        'bg-blue-100 border border-blue-400 text-blue-700'
    }`;

    toast.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} mr-2"></i>
            <span>${message}</span>
        </div>
    `;

    document.body.appendChild(toast);

    setTimeout(() => {
        toast.remove();
    }, 5000);
}
</script>
