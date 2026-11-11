<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <span class="px-2.5 py-0.5 rounded-full text-2xs font-extrabold uppercase tracking-wider bg-rose-600 text-white">
                    Super Admin
                </span>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Platform Control Center</h1>
            </div>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Pantau seluruh tenant bengkel servis HP, pertumbuhan omset platform SaaS, dan kendali penangguhan akun.</p>
        </div>
    </div>

    <!-- Platform KPI Overview -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Tenants -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
            <span class="text-2xs font-semibold text-slate-400 uppercase tracking-wider block">Total Tenant / Toko</span>
            <div class="flex items-center justify-between mt-1">
                <span class="text-2xl font-bold text-slate-900">{{ $overview['total_tenants'] }}</span>
                <span class="w-8 h-8 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center">
                    <i data-lucide="building-2" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="flex items-center gap-2 mt-2 text-2xs text-slate-500">
                <span class="text-emerald-600 font-semibold">{{ $overview['active_tenants'] }} Aktif</span>
                <span>•</span>
                <span class="text-rose-500 font-semibold">{{ $overview['suspended_tenants'] }} Ditangguhkan</span>
            </div>
        </div>

        <!-- Monthly Recurring Revenue (MRR) -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
            <span class="text-2xs font-semibold text-slate-400 uppercase tracking-wider block">Estimasi MRR Platform</span>
            <div class="flex items-center justify-between mt-1">
                <span class="text-xl sm:text-2xl font-bold text-emerald-600">
                    Rp{{ number_format($overview['mrr'], 0, ',', '.') }}
                </span>
                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="trending-up" class="w-4 h-4"></i>
                </span>
            </div>
            <span class="text-2xs text-slate-400 block mt-2">Dari langganan bulanan aktif</span>
        </div>

        <!-- Total Orders -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
            <span class="text-2xs font-semibold text-slate-400 uppercase tracking-wider block">Total Order Servis</span>
            <div class="flex items-center justify-between mt-1">
                <span class="text-2xl font-bold text-indigo-600">{{ $overview['total_orders'] }}</span>
                <span class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <i data-lucide="wrench" class="w-4 h-4"></i>
                </span>
            </div>
            <span class="text-2xs text-slate-400 block mt-2">Volume agregat se-platform</span>
        </div>

        <!-- Total Users -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
            <span class="text-2xs font-semibold text-slate-400 uppercase tracking-wider block">Total Pengguna Terdaftar</span>
            <div class="flex items-center justify-between mt-1">
                <span class="text-2xl font-bold text-slate-800">{{ $overview['total_users'] }}</span>
                <span class="w-8 h-8 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center">
                    <i data-lucide="users" class="w-4 h-4"></i>
                </span>
            </div>
            <span class="text-2xs text-slate-400 block mt-2">Owner & Teknisi</span>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs space-y-3">
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Cari nama toko, slug, nama owner, email..."
                    class="w-full pl-10 pr-4 py-2 rounded-xl border-slate-200 text-xs focus:border-primary-500 focus:ring-primary-500 placeholder:text-slate-400"
                />
            </div>

            <div class="flex items-center gap-2">
                <select
                    wire:model.live="planFilter"
                    class="rounded-xl border-slate-200 text-xs focus:border-primary-500 focus:ring-primary-500 text-slate-700"
                >
                    <option value="">Semua Paket</option>
                    <option value="starter">Starter</option>
                    <option value="business">Business</option>
                    <option value="pro">Pro</option>
                </select>

                <select
                    wire:model.live="statusFilter"
                    class="rounded-xl border-slate-200 text-xs focus:border-primary-500 focus:ring-primary-500 text-slate-700"
                >
                    <option value="">Semua Status</option>
                    <option value="active">Aktif Saja</option>
                    <option value="suspended">Ditangguhkan</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Tenants Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase text-2xs tracking-wider border-b border-slate-200/80">
                    <tr>
                        <th class="py-3 px-4">Nama Toko & Slug</th>
                        <th class="py-3 px-4">Owner Toko</th>
                        <th class="py-3 px-4 text-center">Paket</th>
                        <th class="py-3 px-4">Terdaftar</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Aksi Super Admin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($tenants as $t)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3 px-4">
                                <span class="font-bold text-slate-900 block text-xs">{{ $t->name }}</span>
                                <span class="text-2xs font-mono text-slate-400">/{{ $t->slug }}</span>
                            </td>

                            <td class="py-3 px-4">
                                <span class="font-medium text-slate-800 block">{{ $t->owner?->name ?? '-' }}</span>
                                <span class="text-2xs text-slate-400">{{ $t->owner?->email ?? '-' }}</span>
                            </td>

                            <td class="py-3 px-4 text-center">
                                @php
                                    $planBadges = [
                                        'starter' => 'bg-slate-100 text-slate-700',
                                        'business' => 'bg-primary-50 text-primary-700 border border-primary-200',
                                        'pro' => 'bg-purple-50 text-purple-700 border border-purple-200',
                                    ];
                                @endphp
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-2xs font-bold uppercase {{ $planBadges[$t->plan] ?? 'bg-slate-100 text-slate-700' }}">
                                    {{ $t->plan }}
                                </span>
                            </td>

                            <td class="py-3 px-4 text-slate-500 text-2xs">
                                {{ $t->created_at->format('d/m/Y') }}
                            </td>

                            <td class="py-3 px-4 text-center">
                                @if ($t->status === 'active')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-2xs font-semibold bg-emerald-50 text-emerald-700">
                                        Aktif
                                    </span>
                                @elseif ($t->status === 'suspended')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-2xs font-semibold bg-rose-50 text-rose-700">
                                        Ditangguhkan
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-2xs font-semibold bg-slate-100 text-slate-600">
                                        {{ ucfirst($t->status) }}
                                    </span>
                                @endif
                            </td>

                            <td class="py-3 px-4 text-right">
                                @if ($t->status === 'suspended')
                                    <button
                                        type="button"
                                        wire:click="toggleSuspend('{{ $t->id }}')"
                                        wire:confirm="Aktifkan kembali toko '{{ $t->name }}'?"
                                        class="inline-flex items-center px-3 py-1 text-2xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-xl transition-colors"
                                    >
                                        <i data-lucide="check" class="w-3 h-3 mr-1"></i>
                                        Aktifkan
                                    </button>
                                @else
                                    <button
                                        type="button"
                                        wire:click="toggleSuspend('{{ $t->id }}')"
                                        wire:confirm="Yakin ingin menangguhkan (suspend) toko '{{ $t->name }}'? Owner dan teknisi tidak akan dapat mengakses sistem."
                                        class="inline-flex items-center px-3 py-1 text-2xs font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-xl transition-colors"
                                    >
                                        <i data-lucide="slash" class="w-3 h-3 mr-1"></i>
                                        Tangguhkan
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-slate-400">
                                Tidak ada data tenant yang cocok dengan kriteria pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($tenants->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $tenants->links() }}
            </div>
        @endif
    </div>
</div>

