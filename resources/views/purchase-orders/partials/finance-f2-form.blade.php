<div x-show="showFinanceF2Modal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click.self="showFinanceF2Modal = false"
    @keydown.escape.window="showFinanceF2Modal = false"
    class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" style="display: none;">
    <div x-show="showFinanceF2Modal" x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200 transform" x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">

        <form method="POST" action="{{ route('purchase-orders.approve-finance-f2', $purchaseOrder) }}"
            x-data="{ selectedPaymentMethod: '' }">
            @csrf
            <div class="p-6">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-check-circle text-2xl text-green-600"></i>
                </div>

                <h3 class="text-xl font-bold text-gray-900 text-center mb-6">Approve Finance F2</h3>

                <!-- PO Summary Info -->
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div>
                            <span class="font-medium text-gray-700">PO Number:</span>
                            <div class="text-gray-900">{{ $purchaseOrder->po_number }}</div>
                        </div>
                        <div>
                            <span class="font-medium text-gray-700">Supplier:</span>
                            <div class="text-gray-900">{{ $purchaseOrder->supplier->supplier_name ?? 'Not Selected' }}
                            </div>
                        </div>
                        <div>
                            <span class="font-medium text-gray-700">Total Amount:</span>
                            <div class="text-lg font-bold text-green-600">Rp
                                {{ number_format($purchaseOrder->total_amount, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>

                <!-- Available Payment Options from F1 -->
                @if ($purchaseOrder->available_payment_options)
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            Metode Pembayaran <span class="text-red-500">*</span>
                        </label>
                        <div class="bg-blue-50 rounded-lg p-4 mb-3">
                            <p class="text-sm text-blue-800 mb-3 font-medium">Opsi yang tersedia dari Finance F1:</p>
                            <div class="space-y-3">
                                @foreach ($purchaseOrder->available_payment_options as $option)
                                    <label
                                        class="flex items-start p-3 border border-blue-200 rounded-lg hover:bg-blue-100 cursor-pointer transition-colors">
                                        <input type="radio" name="payment_method" value="{{ $option }}" required
                                            x-model="selectedPaymentMethod"
                                            class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 mt-1">
                                        <div class="ml-3">
                                            <span class="text-sm font-medium text-gray-900">
                                                {{ \App\Constants\PurchaseOrderConstants::getPaymentMethods()[$option] ?? $option }}
                                            </span>
                                            <p class="text-xs text-gray-600 mt-1">
                                                @switch($option)
                                                    @case('bank_transfer')
                                                        Transfer langsung ke rekening supplier
                                                    @break

                                                    @case('virtual_account')
                                                        Pembayaran melalui virtual account
                                                    @break

                                                    @case('cash')
                                                        Pembayaran tunai
                                                    @break

                                                    @case('check')
                                                        Pembayaran dengan cek
                                                    @break

                                                    @case('credit_card')
                                                        Pembayaran dengan kartu kredit
                                                    @break

                                                    @default
                                                        Metode pembayaran khusus
                                                @endswitch
                                            </p>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Payment Amount -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Jumlah Pembayaran <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span
                                class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500 font-medium">Rp</span>
                            <input type="number" name="payment_amount" step="1" min="0"
                                value="{{ old('payment_amount', $purchaseOrder->total_amount) }}" required
                                class="pl-12 pr-4 py-3 w-full bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all @error('payment_amount') border-red-500 @enderror">
                        </div>
                        @error('payment_amount')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-sm text-gray-500 mt-1">Default: Total PO (Rp
                            {{ number_format($purchaseOrder->total_amount, 0, ',', '.') }})</p>
                    </div>
                </div>

        

                <!-- Bank Transfer Details -->
                <div x-show="selectedPaymentMethod === 'bank_transfer'" x-transition
                    class="border border-gray-200 rounded-lg p-4 bg-gray-50 mb-6">
                    <h4 class="text-sm font-medium text-gray-900 mb-4">Detail Bank Transfer</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Nama Bank <span class="text-red-500">*</span>
                            </label>
                            <input type="text" :name="selectedPaymentMethod === 'bank_transfer' ? 'bank_name' : ''"
                                :required="selectedPaymentMethod === 'bank_transfer'" placeholder="Contoh: Bank BCA"
                                value="{{ old('bank_name') }}"
                                class="w-full py-3 px-4 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Nomor Rekening <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                :name="selectedPaymentMethod === 'bank_transfer' ? 'account_number' : ''"
                                :required="selectedPaymentMethod === 'bank_transfer'" placeholder="1234567890"
                                value="{{ old('account_number') }}"
                                class="w-full py-3 px-4 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Nama Pemegang Rekening <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                :name="selectedPaymentMethod === 'bank_transfer' ? 'account_holder' : ''"
                                :required="selectedPaymentMethod === 'bank_transfer'"
                                placeholder="PT. SUPPLIER TERBAIK" value="{{ old('account_holder') }}"
                                class="w-full py-3 px-4 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500">
                        </div>
                    </div>
                </div>

                <!-- Virtual Account Details -->
                <div x-show="selectedPaymentMethod === 'virtual_account'" x-transition
                    class="border border-gray-200 rounded-lg p-4 bg-gray-50 mb-6">
                    <h4 class="text-sm font-medium text-gray-900 mb-4">Detail Virtual Account</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Bank Penerbit <span class="text-red-500">*</span>
                            </label>
                            <select :name="selectedPaymentMethod === 'virtual_account' ? 'bank_name' : ''"
                                :required="selectedPaymentMethod === 'virtual_account'"
                                class="w-full py-3 px-4 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500">
                                <option value="">Pilih Bank</option>
                                <option value="BCA" {{ old('bank_name') == 'BCA' ? 'selected' : '' }}>Bank BCA
                                </option>
                                <option value="BNI" {{ old('bank_name') == 'BNI' ? 'selected' : '' }}>Bank BNI
                                </option>
                                <option value="BRI" {{ old('bank_name') == 'BRI' ? 'selected' : '' }}>Bank BRI
                                </option>
                                <option value="Mandiri" {{ old('bank_name') == 'Mandiri' ? 'selected' : '' }}>Bank
                                    Mandiri</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Nomor Virtual Account <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                :name="selectedPaymentMethod === 'virtual_account' ? 'virtual_account_number' : ''"
                                :required="selectedPaymentMethod === 'virtual_account'"
                                placeholder="88808123456789012" value="{{ old('virtual_account_number') }}"
                                class="w-full py-3 px-4 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500">
                        </div>
                    </div>
                </div>

                <!-- Check Payment Details -->
                <div x-show="selectedPaymentMethod === 'check'" x-transition
                    class="border border-gray-200 rounded-lg p-4 bg-gray-50 mb-6">
                    <h4 class="text-sm font-medium text-gray-900 mb-4">Detail Pembayaran Cek</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Bank Penerbit Cek <span class="text-red-500">*</span>
                            </label>
                            <input type="text" :name="selectedPaymentMethod === 'check' ? 'bank_name' : ''"
                                :required="selectedPaymentMethod === 'check'" placeholder="Bank penerbit cek"
                                value="{{ old('bank_name') }}"
                                class="w-full py-3 px-4 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Nama Penerima
                            </label>
                            <input type="text"
                                :name="selectedPaymentMethod === 'check' ? 'account_holder' : ''"
                                placeholder="Nama yang tertera di cek" value="{{ old('account_holder') }}"
                                class="w-full py-3 px-4 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500">
                        </div>
                    </div>
                </div>

                <!-- Credit Card Details -->
                <div x-show="selectedPaymentMethod === 'credit_card'" x-transition
                    class="border border-gray-200 rounded-lg p-4 bg-gray-50 mb-6">
                    <h4 class="text-sm font-medium text-gray-900 mb-4">Detail Kartu Kredit</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Bank Penerbit Kartu <span class="text-red-500">*</span>
                            </label>
                            <input type="text" :name="selectedPaymentMethod === 'credit_card' ? 'bank_name' : ''"
                                :required="selectedPaymentMethod === 'credit_card'"
                                placeholder="Bank penerbit kartu kredit" value="{{ old('bank_name') }}"
                                class="w-full py-3 px-4 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Nama Kardholder
                            </label>
                            <input type="text"
                                :name="selectedPaymentMethod === 'credit_card' ? 'account_holder' : ''"
                                placeholder="Nama pemegang kartu" value="{{ old('account_holder') }}"
                                class="w-full py-3 px-4 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500">
                        </div>
                    </div>
                </div>

                <!-- Cash Payment -->
                <div x-show="selectedPaymentMethod === 'cash'" x-transition
                    class="border border-gray-200 rounded-lg p-4 bg-gray-50 mb-6">
                    <h4 class="text-sm font-medium text-gray-900 mb-4">Pembayaran Tunai</h4>
                    <div class="text-sm text-gray-600 bg-green-50 rounded-lg p-3">
                        <i class="fas fa-info-circle mr-1"></i>
                        <strong>Info:</strong> Pembayaran akan dilakukan secara tunai langsung ke supplier.
                    </div>
                </div>

                <!-- Payment Due Date -->
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Batas Waktu Pembayaran
                    </label>
                    <input type="date" name="payment_due_date" min="{{ now()->addDay()->toDateString() }}"
                        value="{{ now()->addDays(30)->toDateString() }}"
                        class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all">
                    <p class="text-sm text-gray-500 mt-1">Default: 30 hari dari sekarang. Kosongkan jika tidak ada
                        batas waktu.</p>
                </div>

                <!-- Finance F2 Notes -->
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Catatan Finance F2
                    </label>
                    <textarea name="finance_f2_notes" rows="4"
                        placeholder="Catatan khusus, instruksi pembayaran, atau informasi penting lainnya..."
                        class="w-full p-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"></textarea>
                </div>

                <!-- Final Approval Info -->
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mt-6">
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-check-circle text-green-600 mt-0.5"></i>
                        <div class="text-sm text-green-800">
                            <p class="font-medium mb-1">Final Approval</p>
                            <ul class="text-xs space-y-1">
                                <li>• PO akan mendapat status "Approved" setelah di-approve</li>
                                <li>• Sistem pembayaran akan di-setup sesuai metode yang dipilih</li>
                                <li>• PO siap dikirim ke supplier untuk diproses</li>
                                <li>• Email notifikasi akan dikirim ke tim terkait</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3 mt-6 pt-6 border-t border-gray-200">
                    <button type="button" @click="showFinanceF2Modal = false"
                        class="flex-1 px-4 py-3 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-200 flex items-center justify-center space-x-2">
                        <i class="fas fa-times"></i>
                        <span>Batal</span>
                    </button>
                    <button type="submit"
                        class="flex-1 px-4 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-xl hover:from-green-700 hover:to-green-800 transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl">
                        <i class="fas fa-check-circle"></i>
                        <span>Approve & Finalisasi</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
