<div x-show="showCancelModal" x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0" @click.self="showCancelModal = false"
    @keydown.escape.window="showCancelModal = false"
    class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" style="display: none;">
    <div x-show="showCancelModal" x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200 transform"
        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
        class="bg-white rounded-2xl shadow-2xl max-w-lg w-full">

        <form method="POST" action="{{ route('purchase-orders.cancel', $purchaseOrder) }}">
            @csrf
            <div class="p-6">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-ban text-2xl text-red-600"></i>
                </div>

                <h3 class="text-xl font-bold text-gray-900 text-center mb-6">Cancel Purchase Order</h3>

                <p class="text-gray-600 text-center mb-4">
                    Apakah Anda yakin ingin membatalkan PO <strong>{{ $purchaseOrder->po_number }}</strong>?
                    Tindakan ini tidak dapat dibatalkan.
                </p>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Alasan Pembatalan <span class="text-red-500">*</span>
                    </label>
                    <textarea name="reason" rows="4" required
                        placeholder="Berikan alasan detail mengapa PO ini dibatalkan..."
                        class="w-full p-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"></textarea>
                </div>

                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-exclamation-triangle text-red-600 mt-0.5"></i>
                        <div class="text-sm text-red-800">
                            <p class="font-medium mb-1">Peringatan!</p>
                            <ul class="text-xs space-y-1">
                                <li>• PO yang dibatalkan tidak dapat dikembalikan</li>
                                <li>• Semua item dalam PO akan dianggap tidak valid</li>
                                <li>• Supplier akan diberitahu tentang pembatalan ini</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <button type="button" @click="showCancelModal = false"
                        class="flex-1 px-4 py-3 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-200 flex items-center justify-center space-x-2">
                        <i class="fas fa-arrow-left"></i>
                        <span>Kembali</span>
                    </button>
                    <button type="submit"
                        onclick="return confirm('Apakah Anda benar-benar yakin? Tindakan ini tidak dapat dibatalkan!')"
                        class="flex-1 px-4 py-3 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-xl hover:from-red-700 hover:to-red-800 transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl">
                        <i class="fas fa-ban"></i>
                        <span>Cancel PO</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
