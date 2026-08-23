<div class="p-8 max-w-7xl mx-auto font-sans" x-data="{ activeTab: 'profile' }">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">User Profile & Account Settings</h1>
            <p class="text-gray-600 mt-1">Manage personal agent credentials, business tenant info, and team member seats.</p>
        </div>
    </div>

    @if($statusMessage)
        <div class="mb-6 p-4 rounded-lg flex items-center justify-between {{ $statusType === 'success' ? 'bg-green-100 text-green-800 border border-green-300' : 'bg-red-100 text-red-800 border border-red-300' }}">
            <span class="font-semibold">{{ $statusMessage }}</span>
            <button wire:click="$set('statusMessage', null)" class="text-sm font-bold opacity-75 hover:opacity-100">&times;</button>
        </div>
    @endif

    <!-- Navigation Tabs Bar -->
    <div class="flex border-b border-gray-200 mb-8 space-x-6">
        <button @click="activeTab = 'profile'" :class="activeTab === 'profile' ? 'border-green-600 text-green-600 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700'" class="pb-3 px-1 border-b-2 text-sm transition">
            Personal Profile & Security
        </button>

        <button @click="activeTab = 'business'" :class="activeTab === 'business' ? 'border-green-600 text-green-600 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700'" class="pb-3 px-1 border-b-2 text-sm transition">
            Business & Tenant Profile
        </button>

        <button @click="activeTab = 'team'" :class="activeTab === 'team' ? 'border-green-600 text-green-600 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700'" class="pb-3 px-1 border-b-2 text-sm transition">
            Team Members & Agent Seats ({{ count($teamMembers) }})
        </button>
    </div>

    <!-- Tab 1: Personal Profile & Security -->
    <div x-show="activeTab === 'profile'" class="space-y-6">
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm space-y-6">
            <div class="flex items-center space-x-4 border-b border-gray-100 pb-6">
                <div class="w-16 h-16 rounded-full bg-blue-600 text-white font-extrabold text-2xl flex items-center justify-center shadow-md">
                    {{ strtoupper(substr($name, 0, 2)) }}
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900">{{ $name }}</h2>
                    <p class="text-sm text-gray-500">{{ $email }} &bull; <span class="bg-blue-100 text-blue-800 text-xs px-2 py-0.5 rounded-full font-semibold">{{ $role }}</span></p>
                </div>
            </div>

            <form wire:submit.prevent="updateProfile" class="space-y-4 max-w-2xl">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Full Name</label>
                        <input type="text" wire:model="name" required class="w-full border border-gray-300 rounded-lg px-3.5 py-2 text-sm focus:ring-2 focus:ring-green-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Email Address</label>
                        <input type="email" wire:model="email" required class="w-full border border-gray-300 rounded-lg px-3.5 py-2 text-sm focus:ring-2 focus:ring-green-500 outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Phone Number</label>
                        <input type="text" wire:model="phone" class="w-full border border-gray-300 rounded-lg px-3.5 py-2 text-sm focus:ring-2 focus:ring-green-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Assigned Role</label>
                        <input type="text" wire:model="role" disabled class="w-full border border-gray-200 bg-gray-50 text-gray-500 rounded-lg px-3.5 py-2 text-sm">
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white text-sm font-bold px-6 py-2.5 rounded-lg shadow-sm transition">Save Profile Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tab 2: Business & Tenant Profile -->
    <div x-show="activeTab === 'business'" class="space-y-6">
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm space-y-6">
            <div class="border-b border-gray-100 pb-4">
                <h2 class="text-xl font-bold text-gray-900">Multi-Tenant Business Configuration</h2>
                <p class="text-xs text-gray-500">Configure your business details displayed on WhatsApp invoices and CAPI attribution.</p>
            </div>

            <form wire:submit.prevent="updateBusinessSettings" class="space-y-4 max-w-2xl">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Business Name</label>
                        <input type="text" wire:model="businessName" required class="w-full border border-gray-300 rounded-lg px-3.5 py-2 text-sm focus:ring-2 focus:ring-green-500 outline-none font-bold text-gray-900">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Business Support Email</label>
                        <input type="email" wire:model="businessEmail" required class="w-full border border-gray-300 rounded-lg px-3.5 py-2 text-sm focus:ring-2 focus:ring-green-500 outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Industry Sector</label>
                        <select wire:model="industry" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-green-500 outline-none">
                            <option value="Automotive & Machinery">Automotive & Machinery</option>
                            <option value="E-Commerce & Retail">E-Commerce & Retail</option>
                            <option value="Real Estate & Property">Real Estate & Property</option>
                            <option value="Education & Training">Education & Training</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Default Timezone</label>
                        <select wire:model="timezone" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-green-500 outline-none">
                            <option value="Asia/Kolkata">Asia/Kolkata (IST +5:30)</option>
                            <option value="UTC">UTC (GMT +0:00)</option>
                            <option value="America/New_York">America/New_York (EST -5:00)</option>
                        </select>
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white text-sm font-bold px-6 py-2.5 rounded-lg shadow-sm transition">Update Business Info</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tab 3: Team Members & Agent Seats -->
    <div x-show="activeTab === 'team'" class="space-y-6">
        
        <!-- Add Team Member Card -->
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Invite New Sales Agent</h2>
            
            <form wire:submit.prevent="addTeamMember" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Agent Name</label>
                    <input type="text" wire:model="newAgentName" placeholder="e.g. Anish Gupta" required class="w-full border border-gray-300 rounded-lg px-3.5 py-2 text-sm focus:ring-2 focus:ring-green-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Agent Email</label>
                    <input type="email" wire:model="newAgentEmail" placeholder="anish@acmeauto.com" required class="w-full border border-gray-300 rounded-lg px-3.5 py-2 text-sm focus:ring-2 focus:ring-green-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Role</label>
                    <select wire:model="newAgentRole" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-green-500 outline-none">
                        <option value="Sales Agent">Sales Agent</option>
                        <option value="Support Manager">Support Manager</option>
                        <option value="Administrator">Administrator</option>
                    </select>
                </div>

                <div>
                    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white text-sm font-bold py-2 rounded-lg shadow-sm transition flex items-center justify-center space-x-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        <span>Send Invite</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Team Members List -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="p-5 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                <h2 class="font-bold text-gray-800 text-lg">Active Team Members & Sales Agents</h2>
                <span class="text-xs text-gray-500 font-semibold">{{ count($teamMembers) }} Active Seats</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-600">
                    <thead class="bg-gray-100 text-gray-700 uppercase text-[11px] font-bold tracking-wider">
                        <tr>
                            <th class="py-3.5 px-5">Agent</th>
                            <th class="py-3.5 px-5">Role</th>
                            <th class="py-3.5 px-5">Status</th>
                            <th class="py-3.5 px-5">Joined Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($teamMembers as $mem)
                            <tr class="hover:bg-gray-50/80 transition">
                                <td class="py-4 px-5 flex items-center space-x-3">
                                    <div class="w-9 h-9 rounded-full bg-slate-800 text-white font-bold text-xs flex items-center justify-center">
                                        {{ strtoupper(substr($mem['name'], 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-gray-900">{{ $mem['name'] }}</div>
                                        <div class="text-xs text-gray-400">{{ $mem['email'] }}</div>
                                    </div>
                                </td>
                                <td class="py-4 px-5 font-semibold text-gray-800">{{ $mem['role'] }}</td>
                                <td class="py-4 px-5">
                                    @if($mem['status'] === 'ONLINE')
                                        <span class="bg-green-100 text-green-800 text-xs font-bold px-2.5 py-1 rounded-full border border-green-200 flex items-center space-x-1 w-max">
                                            <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                            <span>ONLINE</span>
                                        </span>
                                    @elseif($mem['status'] === 'ACTIVE')
                                        <span class="bg-blue-100 text-blue-800 text-xs font-bold px-2.5 py-1 rounded-full border border-blue-200">ACTIVE</span>
                                    @else
                                        <span class="bg-gray-100 text-gray-600 text-xs font-bold px-2.5 py-1 rounded-full border border-gray-200">OFFLINE</span>
                                    @endif
                                </td>
                                <td class="py-4 px-5 text-gray-500 font-mono text-xs">{{ $mem['joined'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
