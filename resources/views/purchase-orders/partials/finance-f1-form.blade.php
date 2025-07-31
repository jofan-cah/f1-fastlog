<div x-show="showFinanceF1Modal" x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0" @click.self="showFinanceF1Modal = false"
    @keydown.escape.window="showFinanceF1Modal = false"
    class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" style="display: none;">
    <div x-show="showFinanceF1Modal" x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200 transform"
        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
        class="bg-white rounded-2xl shadow-2xl max-w-5xl w-full max-h-[95vh] overflow-y-auto">

        <form method="POST" action="{{ route('purchase-orders.process-finance-f1', $purchaseOrder) }}"
              x-data="{
                  selectedPaymentMethod: '{{ old('payment_method') }}',
                  bankName: '{{ old('bank_name') }}',
                  accountNumber: '{{ old('account_number') }}',
                  accountHolder: '{{ old('account_holder') }}',
                  virtualAccountNumber: '{{ old('virtual_account_number') }}'
              }">
            @csrf
            <div class="p-6">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-clipboard-check text-2xl text-blue-600"></i>
                </div>

                <h3 class="text-xl font-bold text-gray-900 text-center mb-6">Process Finance F1</h3>

                <!-- PO Summary -->
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div>
                            <span class="font-medium text-gray-700">PO Number:</span>
                            <div class="text-gray-900 font-semibold">{{ $purchaseOrder->po_number }}</div>
                        </div>
                        <div>
                            <span class="font-medium text-gray-700">Total Amount:</span>
                            <div class="text-lg font-bold text-green-600">Rp {{ number_format($purchaseOrder->total_amount, 0, ',', '.') }}</div>
                        </div>
                        <div>
                            <span class="font-medium text-gray-700">Items:</span>
                            <div class="text-gray-900">{{ $purchaseOrder->poDetails->count() }} items</div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Left Column: Basic Info -->
                    <div class="space-y-6">
                        <!-- Supplier Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                Pilih Supplier <span class="text-red-500">*</span>
                            </label>
                            <select name="supplier_id" required
                                class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('supplier_id') border-red-500 @enderror">
                                <option value="">Pilih Supplier</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->supplier_id }}"
                                        {{ old('supplier_id', $purchaseOrder->supplier_id) == $supplier->supplier_id ? 'selected' : '' }}>
                                        {{ $supplier->supplier_name }} ({{ $supplier->supplier_code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('supplier_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Payment Method Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                Metode Pembayaran <span class="text-red-500">*</span>
                            </label>
                            <div class="space-y-3">
                                @foreach(\App\Constants\PurchaseOrderConstants::getPaymentMethods() as $key => $method)
                                    <label class="flex items-start p-4 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors">
                                        <input type="radio" name="payment_method" value="{{ $key }}" required
                                            x-model="selectedPaymentMethod"
                                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 mt-1">
                                        <div class="ml-3">
                                            <span class="text-sm font-medium text-gray-900">{{ $method }}</span>
                                            <p class="text-xs text-gray-600 mt-1">
                                                @switch($key)
                                                    @case('bank_transfer')
                                                        Transfer langsung ke rekening supplier
                                                        @break
                                                    @case('virtual_account')
                                                        Pembayaran melalui virtual account
                                                        @break
                                                    @case('cash')
                                                        Pembayaran tunai langsung
                                                        @break
                                                    @case('check')
                                                        Pembayaran dengan cek/bilyet giro
                                                        @break
                                                    @case('credit_card')
                                                        Pembayaran dengan kartu kredit perusahaan
                                                        @break
                                                    @default
                                                        Metode pembayaran khusus
                                                @endswitch
                                            </p>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            @error('payment_method')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Payment Amount -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Jumlah Pembayaran <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500 font-medium">Rp</span>
                                <input type="number" name="payment_amount" step="1" min="0"
                                    value="{{ old('payment_amount', $purchaseOrder->total_amount) }}" required
                                    class="pl-12 pr-4 py-3 w-full bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('payment_amount') border-red-500 @enderror">
                            </div>
                            <p class="text-sm text-gray-500 mt-1">Default: Total PO (Rp {{ number_format($purchaseOrder->total_amount, 0, ',', '.') }})</p>
                            @error('payment_amount')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Payment Due Date -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Batas Waktu Pembayaran
                            </label>
                            <input type="date" name="payment_due_date"
                                min="{{ now()->addDay()->toDateString() }}"
                                value="{{ old('payment_due_date', now()->addDays(30)->toDateString()) }}"
                                class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('payment_due_date') border-red-500 @enderror">
                            <p class="text-sm text-gray-500 mt-1">Default: 30 hari dari sekarang</p>
                            @error('payment_due_date')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Right Column: Payment Details -->
                    <div>
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Detail Pembayaran</h4>

                        <!-- HIDDEN INPUTS for all payment fields - Strategy to avoid conflicts -->
                        <input type="hidden" name="bank_name" :value="bankName">
                        <input type="hidden" name="account_number" :value="accountNumber">
                        <input type="hidden" name="account_holder" :value="accountHolder">
                        <input type="hidden" name="virtual_account_number" :value="virtualAccountNumber">

                        <!-- Bank Transfer Details -->
                        <div x-show="selectedPaymentMethod === 'bank_transfer'" x-transition class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                            <h5 class="text-sm font-medium text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-university mr-2 text-blue-600"></i>
                                Detail Bank Transfer
                            </h5>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Nama Bank <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" placeholder="Contoh: Bank BCA"
                                        x-model="bankName"
                                        class="w-full py-3 px-4 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('bank_name') border-red-500 @enderror">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Nomor Rekening <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" placeholder="1234567890"
                                        x-model="accountNumber"
                                        class="w-full py-3 px-4 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('account_number') border-red-500 @enderror">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Nama Pemegang Rekening <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" placeholder="PT. SUPPLIER TERBAIK"
                                        x-model="accountHolder"
                                        class="w-full py-3 px-4 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('account_holder') border-red-500 @enderror">
                                </div>
                            </div>
                        </div>

                        <!-- Virtual Account Details -->
                        <div x-show="selectedPaymentMethod === 'virtual_account'" x-transition class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                            <h5 class="text-sm font-medium text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-credit-card mr-2 text-purple-600"></i>
                                Detail Virtual Account
                            </h5>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Bank Penerbit <span class="text-red-500">*</span>
                                    </label>
                                    <select x-model="bankName"
                                        class="w-full py-3 px-4 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('bank_name') border-red-500 @enderror">
                                        <option value="">Pilih Bank</option>
                                        <option value="BCA">Bank BCA</option>
                                        <option value="BNI">Bank BNI</option>
                                        <option value="BRI">Bank BRI</option>
                                        <option value="Mandiri">Bank Mandiri</option>
                                        <option value="CIMB Niaga">CIMB Niaga</option>
                                        <option value="Danamon">Bank Danamon</option>
                                        <option value="Permata">Bank Permata</option>
                                        <option value="BTN">Bank BTN</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Nomor Virtual Account <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" placeholder="88808123456789012"
                                        x-model="virtualAccountNumber"
                                        class="w-full py-3 px-4 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('virtual_account_number') border-red-500 @enderror">
                                </div>
                            </div>
                            <div class="mt-3 text-xs text-purple-700 bg-purple-50 rounded-lg p-3">
                                <i class="fas fa-info-circle mr-1"></i>
                                <strong>Info:</strong> Virtual Account akan aktif setelah setup dan dapat digunakan untuk pembayaran otomatis.
                            </div>
                        </div>

                        <!-- Check Payment Details -->
                        <div x-show="selectedPaymentMethod === 'check'" x-transition class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                            <h5 class="text-sm font-medium text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-money-check mr-2 text-green-600"></i>
                                Detail Pembayaran Cek
                            </h5>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Bank Penerbit Cek <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" placeholder="Bank penerbit cek"
                                        x-model="bankName"
                                        class="w-full py-3 px-4 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('bank_name') border-red-500 @enderror">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Nama Penerima Cek
                                    </label>
                                    <input type="text" placeholder="Nama yang tertera di cek"
                                        x-model="accountHolder"
                                        class="w-full py-3 px-4 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('account_holder') border-red-500 @enderror">
                                </div>
                            </div>
                        </div>

                        <!-- Credit Card Details -->
                        <div x-show="selectedPaymentMethod === 'credit_card'" x-transition class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                            <h5 class="text-sm font-medium text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-credit-card mr-2 text-indigo-600"></i>
                                Detail Kartu Kredit
                            </h5>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Bank Penerbit Kartu <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" placeholder="Bank penerbit kartu kredit"
                                        x-model="bankName"
                                        class="w-full py-3 px-4 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('bank_name') border-red-500 @enderror">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Nama Kardholder
                                    </label>
                                    <input type="text" placeholder="Nama pemegang kartu"
                                        x-model="accountHolder"
                                        class="w-full py-3 px-4 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('account_holder') border-red-500 @enderror">
                                </div>
                            </div>
                            <div class="mt-3 text-xs text-yellow-700 bg-yellow-50 rounded-lg p-3">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                <strong>Perhatian:</strong> Pastikan limit kartu kredit mencukupi untuk pembayaran ini.
                            </div>
                        </div>

                        <!-- Cash Payment - No additional details needed -->
                        <div x-show="selectedPaymentMethod === 'cash'" x-transition class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                            <h5 class="text-sm font-medium text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-money-bill-wave mr-2 text-green-600"></i>
                                Pembayaran Tunai
                            </h5>
                            <div class="text-sm text-gray-600 bg-green-50 rounded-lg p-3">
                                <i class="fas fa-info-circle mr-1"></i>
                                <strong>Info:</strong> Pembayaran akan dilakukan secara tunai langsung ke supplier. Pastikan dana cash tersedia di kas perusahaan.
                            </div>
                        </div>

                        <!-- Default state when no payment method selected -->
                        <div x-show="!selectedPaymentMethod" class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center text-gray-500">
                            <i class="fas fa-hand-point-up text-3xl mb-3"></i>
                            <p>Pilih metode pembayaran di atas untuk menampilkan form detail pembayaran</p>
                        </div>
                    </div>
                </div>

                <!-- Finance F1 Notes -->
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Catatan Finance F1
                    </label>
                    <textarea name="finance_f1_notes" rows="3"
                        placeholder="Catatan khusus, instruksi untuk Finance F2, atau informasi tambahan..."
                        class="w-full p-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('finance_f1_notes') border-red-500 @enderror">{{ old('finance_f1_notes') }}</textarea>
                    @error('finance_f1_notes')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Summary Info -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-6">
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-info-circle text-blue-600 mt-0.5"></i>
                        <div class="text-sm text-blue-800">
                            <p class="font-medium mb-1">Setelah di-process:</p>
                            <ul class="text-xs space-y-1">
                                <li>• Supplier dan payment method sudah final</li>
                                <li>• PO akan diteruskan ke Finance F2 untuk approval final</li>
                                <li>• Finance F2 hanya perlu approve tanpa input tambahan</li>
                                <li>• Payment details sudah lengkap untuk eksekusi</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Error Display -->
                @if($errors->any())
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mt-6">
                        <div class="flex items-start space-x-3">
                            <i class="fas fa-exclamation-circle text-red-600 mt-0.5"></i>
                            <div class="text-sm text-red-800">
                                <p class="font-medium mb-2">Terdapat kesalahan input:</p>
                                <ul class="text-xs space-y-1">
                                    @foreach($errors->all() as $error)
                                        <li>• {{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3 mt-6 pt-6 border-t border-gray-200">
                    <button type="button" @click="showFinanceF1Modal = false"
                        class="flex-1 px-4 py-3 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-200 flex items-center justify-center space-x-2">
                        <i class="fas fa-times"></i>
                        <span>Batal</span>
                    </button>
                    <button type="submit"
                        class="flex-1 px-4 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl">
                        <i class="fas fa-check"></i>
                        <span>Process & Kirim ke F2</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
