<div x-show="showFinanceF2Modal" x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0" @click.self="showFinanceF2Modal = false"
    @keydown.escape.window="showFinanceF2Modal = false"
    class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" style="display: none;">
    <div x-show="showFinanceF2Modal" x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200 transform"
        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
        class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[85vh] overflow-hidden">

        <!-- Header -->
        <div class="px-6 pt-6 pb-4 border-b border-gray-200 bg-white sticky top-0 z-10">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-check-circle text-2xl text-green-600"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 text-center">Final Approval - FINANCE RBP</h3>
        </div>

        <!-- Scrollable Content -->
        <div class="overflow-y-auto" style="max-height: calc(85vh - 180px);">
            <div class="px-6 py-4">
                <!-- PO Summary -->
                <div class="bg-gray-50 rounded-xl p-6 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <span class="text-sm font-medium text-gray-600">PO Number:</span>
                            <div class="text-lg font-bold text-gray-900">{{ $purchaseOrder->po_number }}</div>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-600">Total Amount:</span>
                            <div class="text-lg font-bold text-green-600">Rp {{ number_format($purchaseOrder->total_amount, 0, ',', '.') }}</div>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-600">Supplier:</span>
                            <div class="text-gray-900">{{ $purchaseOrder->supplier->supplier_name ?? 'Not Selected' }}</div>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-600">Items:</span>
                            <div class="text-gray-900">{{ $purchaseOrder->poDetails->count() }} items</div>
                        </div>
                    </div>
                </div>

                <!-- Payment Details from F1 -->
                @if($purchaseOrder->payment_method)
                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-6">
                        <h4 class="text-lg font-semibold text-blue-900 mb-4 flex items-center">
                            <i class="fas fa-credit-card mr-2"></i>
                            Payment Setup (by Finance F1)
                        </h4>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="font-medium text-blue-700">Payment Method:</span>
                                <div class="text-blue-900">{{ \App\Constants\PurchaseOrderConstants::getPaymentMethods()[$purchaseOrder->payment_method] ?? $purchaseOrder->payment_method }}</div>
                            </div>
                            <div>
                                <span class="font-medium text-blue-700">Payment Amount:</span>
                                <div class="text-blue-900 font-semibold">Rp {{ number_format($purchaseOrder->payment_amount, 0, ',', '.') }}</div>
                            </div>

                            @if($purchaseOrder->bank_name)
                                <div>
                                    <span class="font-medium text-blue-700">Bank:</span>
                                    <div class="text-blue-900">{{ $purchaseOrder->bank_name }}</div>
                                </div>
                            @endif

                            @if($purchaseOrder->payment_due_date)
                                <div>
                                    <span class="font-medium text-blue-700">Due Date:</span>
                                    <div class="text-blue-900">{{ $purchaseOrder->payment_due_date->format('d M Y') }}</div>
                                </div>
                            @endif

                            @if($purchaseOrder->account_number)
                                <div>
                                    <span class="font-medium text-blue-700">Account Number:</span>
                                    <div class="text-blue-900 font-mono">{{ $purchaseOrder->account_number }}</div>
                                </div>
                            @endif

                            @if($purchaseOrder->virtual_account_number)
                                <div>
                                    <span class="font-medium text-blue-700">Virtual Account:</span>
                                    <div class="text-blue-900 font-mono">{{ $purchaseOrder->virtual_account_number }}</div>
                                </div>
                            @endif

                            @if($purchaseOrder->account_holder)
                                <div class="md:col-span-2">
                                    <span class="font-medium text-blue-700">Account Holder:</span>
                                    <div class="text-blue-900">{{ $purchaseOrder->account_holder }}</div>
                                </div>
                            @endif
                        </div>

                        @if($purchaseOrder->finance_f1_notes)
                            <div class="mt-4 pt-4 border-t border-blue-200">
                                <span class="font-medium text-blue-700">Notes from Finance F1:</span>
                                <div class="text-blue-900 mt-1 bg-white rounded-lg p-3">{{ $purchaseOrder->finance_f1_notes }}</div>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-6">
                        <div class="flex items-center space-x-2 text-yellow-800">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span class="font-medium">Payment details belum di-setup oleh Finance F1</span>
                        </div>
                    </div>
                @endif

                <!-- FINANCE RBP Notes (Optional) -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Catatan FINANCE RBP <span class="text-gray-400">(Opsional)</span>
                    </label>
                    <textarea name="finance_f2_notes" rows="3"
                        placeholder="Tambahkan catatan approval atau instruksi khusus..."
                        class="w-full p-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent @error('finance_f2_notes') border-red-500 @enderror">{{ old('finance_f2_notes') }}</textarea>
                    @error('finance_f2_notes')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Final Approval Confirmation -->
                <div class="bg-green-50 border border-green-200 rounded-xl p-6 mb-4">
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-check-circle text-green-600 mt-1"></i>
                        <div class="text-sm text-green-800">
                            <p class="font-medium mb-2">Final Approval Confirmation</p>
                            <ul class="text-xs space-y-1">
                                <li>✓ Supplier sudah dipilih dan sesuai</li>
                                <li>✓ Payment method dan details sudah di-setup</li>
                                <li>✓ PO amount dan items sudah diverifikasi</li>
                                <li>✓ Ready untuk dikirim ke supplier</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer with Form -->
        <div class="border-t border-gray-200 bg-white sticky bottom-0 z-10">
            <form method="POST" action="{{ route('purchase-orders.approve-finance-f2', $purchaseOrder) }}" class="p-6">
                @csrf
                <input type="hidden" name="final_approval" value="1">

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <button type="button" @click="showFinanceF2Modal = false"
                        class="flex-1 px-6 py-3 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-200 flex items-center justify-center space-x-2">
                        <i class="fas fa-times"></i>
                        <span>Batal</span>
                    </button>
                    <button type="submit"
                        class="flex-1 px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-xl hover:from-green-700 hover:to-green-800 transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl text-lg font-semibold">
                        <i class="fas fa-check-circle"></i>
                        <span>APPROVE PO</span>
                    </button>
                </div>

                <div class="mt-3 text-xs text-center text-gray-500">
                    Dengan meng-klik "APPROVE PO", PO akan mendapat status final approval dan siap dikirim ke supplier
                </div>
            </form>
        </div>
    </div>
</div>
