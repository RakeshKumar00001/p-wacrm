<div class="p-6 max-w-6xl mx-auto font-sans relative" x-data="{ showRawJson: false }">
    <!-- Background Gradient Glows -->
    <div class="absolute top-0 right-1/4 w-96 h-96 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute top-10 left-10 w-72 h-72 bg-purple-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <!-- Page Header -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-slate-200/80 pb-6 relative z-10">
        <div>
            <div class="flex items-center space-x-2.5">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-600 text-white flex items-center justify-center shadow-md shadow-indigo-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">AI Sales Agent Configuration</h1>
                    <p class="text-slate-500 text-xs mt-0.5 font-medium">Configure autonomous conversational AI agents that qualify WhatsApp leads and update stages in real time.</p>
                </div>
            </div>
        </div>
        <div class="mt-4 sm:mt-0 flex items-center space-x-2">
            <span class="inline-flex items-center space-x-2 bg-emerald-50 border border-emerald-200/80 rounded-xl px-3.5 py-2 text-emerald-700 text-xs font-bold shadow-xs">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>AI Agent Active</span>
            </span>
        </div>
    </div>

    <!-- Alert Messages -->
    @if($statusMessage)
        <div class="mb-6 p-4 rounded-xl flex items-center justify-between shadow-xs border transition-all relative z-10 {{ $statusType === 'success' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-rose-50 text-rose-800 border-rose-200' }}">
            <div class="flex items-center space-x-3 text-xs font-semibold">
                @if($statusType === 'success')
                    <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                @else
                    <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                @endif
                <span>{{ $statusMessage }}</span>
            </div>
            <button wire:click="$set('statusMessage', null)" class="text-slate-400 hover:text-slate-600 font-bold text-lg leading-none">&times;</button>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 relative z-10">
        <!-- Left Panel: Settings Form (7 cols) -->
        <div class="lg:col-span-7 space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-6">
                <h2 class="text-base font-extrabold text-slate-800 pb-4 mb-6 border-b border-slate-100 flex items-center space-x-2">
                    <svg class="w-5 h-5 text-indigo-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <span>API & AI Engine Configuration</span>
                </h2>

                <form wire:submit.prevent="saveSettings" class="space-y-6">
                    <!-- AI Provider Switcher -->
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Select AI Engine Provider</label>
                        <div class="grid grid-cols-3 gap-3">
                            <!-- OpenAI Option -->
                            <button type="button" wire:click="selectProvider('openai')" 
                                    class="flex flex-col items-center justify-center p-3.5 rounded-xl border transition-all duration-200 {{ $aiProvider === 'openai' ? 'border-indigo-600 bg-indigo-50/40 ring-2 ring-indigo-500/20 text-indigo-900 font-bold' : 'border-slate-200 hover:bg-slate-50 text-slate-500' }}">
                                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100 mb-2">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M21.73 10.24c-.13-.48-.4-.9-.75-1.22l-.12-.11c.36-.83.31-1.79-.17-2.61-.39-.67-.99-1.16-1.69-1.4-.41-.53-.98-.92-1.64-1.1-1.09-.32-2.28-.01-3.11.75C13.43 4.14 12.3 4 11.23 4.15c-.47-.48-1.09-.81-1.78-.93-1.05-.2-2.13.12-2.91.85-.45-.22-.95-.34-1.46-.34-1.23 0-2.37.69-2.94 1.78-.39.73-.46 1.57-.22 2.34-.48.33-.86.79-1.09 1.34-1.13 2.58.07 5.62 2.68 6.72.13.47.4.88.75 1.21.3.28.67.48 1.07.59-.28.77-.24 1.63.13 2.37.38.74 1.05 1.27 1.83 1.48.32.48.77.85 1.29 1.07.52.22 1.09.31 1.66.27.75.46 1.61.68 2.48.62 1.33-.08 2.53-.78 3.2-1.89.44.22.92.34 1.41.34 1.15-.01 2.21-.62 2.79-1.62.39-.67.49-1.46.29-2.21.46-.3.82-.72 1.05-1.21.84-1.76.7-3.86-.34-5.46zM11.66 20.3c-.6 0-1.18-.15-1.71-.44l.11-.06 4.31-2.49c.35-.2.56-.57.56-.97V10.2l1.62.94c.03.02.04.05.04.08v4.98c0 2.26-1.84 4.1-4.1 4.1zm-6.85-3.95c-.3-.52-.45-1.11-.45-1.71 0-1.06.41-2.08 1.14-2.84l.09.05v4.98c0 .4.22.77.56.97l4.31 2.49-1.62.94c-.03.01-.06.02-.09.02-2.25-.01-4.08-1.85-4.09-4.1zm1.26-9.15c.3-.52.74-.93 1.26-1.17l-.05.09-4.31 2.49c-.35.2-.56.57-.56.97v6.13L4 14.77V14.7c.01-2.26 1.85-4.09 4.1-4.09l-.03-.11zm8.7-2.61l-4.31-2.49c-.03-.02-.07-.03-.1-.03-2.26.01-4.09 1.86-4.08 4.12 0 .6.15 1.18.44 1.71l-.11.06 4.31-2.49c.35-.2.56-.57.56-.97v-6.1l3.29 1.9zm4.27 4.26c.3.52.45 1.11.45 1.71 0 1.96-1.39 3.6-3.32 3.99v-4.98c0-.4-.22-.77-.56-.97l-4.31-2.49 1.62-.94c.03-.01.06-.02.09-.02 2.25 0 4.09 1.84 4.1 4.1l-.07-.4zm-5.04.83l-2.69 1.55-2.69-1.55v-3.1l2.69-1.55 2.69 1.55v3.1zm1.63 2.76v-6.13l1.62-.94c.03-.02.06-.02.09-.02 2.26.01 4.09 1.85 4.09 4.1 0 .6-.15 1.18-.44 1.71l.11-.06-4.31 2.49c-.34-.2-.56-.57-.56-.97v-.18z"/>
                                    </svg>
                                </div>
                                <div class="text-center">
                                    <div class="text-xs font-extrabold text-slate-800">OpenAI</div>
                                    <div class="text-[9px] text-slate-400 font-semibold mt-0.5">GPT-4o / Mini</div>
                                </div>
                            </button>

                            <!-- Gemini Option -->
                            <button type="button" wire:click="selectProvider('gemini')" 
                                    class="flex flex-col items-center justify-center p-3.5 rounded-xl border transition-all duration-200 {{ $aiProvider === 'gemini' ? 'border-indigo-600 bg-indigo-50/40 ring-2 ring-indigo-500/20 text-indigo-900 font-bold' : 'border-slate-200 hover:bg-slate-50 text-slate-500' }}">
                                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100 font-extrabold text-xs mb-2">G</div>
                                <div class="text-center">
                                    <div class="text-xs font-extrabold text-slate-800">Gemini</div>
                                    <div class="text-[9px] text-slate-400 font-semibold mt-0.5">Google 1.5 / 2.5</div>
                                </div>
                            </button>

                            <!-- DeepSeek Option -->
                            <button type="button" wire:click="selectProvider('deepseek')" 
                                    class="flex flex-col items-center justify-center p-3.5 rounded-xl border transition-all duration-200 {{ $aiProvider === 'deepseek' ? 'border-purple-600 bg-purple-50/40 ring-2 ring-purple-500/20 text-purple-900 font-bold' : 'border-slate-200 hover:bg-slate-50 text-slate-500' }}">
                                <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center border border-purple-100 font-extrabold text-xs mb-2">DS</div>
                                <div class="text-center">
                                    <div class="text-xs font-extrabold text-slate-800">DeepSeek</div>
                                    <div class="text-[9px] text-slate-400 font-semibold mt-0.5">V3 & R1</div>
                                </div>
                            </button>
                        </div>
                    </div>

                    <!-- Model API Key -->
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">API Private Token / Key</label>
                        <div class="relative rounded-xl shadow-xs">
                            <input type="password" wire:model="aiApiKey" placeholder="{{ $aiProvider === 'openai' ? 'sk-...' : ($aiProvider === 'gemini' ? 'AIzaSy...' : 'sk-...') }}" 
                                   class="w-full border border-slate-200 rounded-xl pl-4 pr-10 py-2.5 text-xs focus:ring-2 {{ $aiProvider === 'openai' ? 'focus:ring-indigo-500/20 focus:border-indigo-500' : ($aiProvider === 'gemini' ? 'focus:ring-indigo-500/20 focus:border-indigo-500' : 'focus:ring-purple-500/20 focus:border-purple-500') }} outline-none font-mono text-slate-800">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <svg class="h-4 w-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            </div>
                        </div>
                        <p class="text-[10px] text-slate-400 mt-1.5 font-medium">Stored securely and used exclusively to process leads via webhook requests.</p>
                    </div>

                    <!-- Model Version -->
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">AI Model Version</label>
                        @if($aiProvider === 'openai')
                            <select wire:model="aiModel" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-xs bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none text-slate-800 font-semibold appearance-none">
                                <option value="gpt-4o">gpt-4o (Recommended - Smartest & Fast)</option>
                                <option value="gpt-4o-mini">gpt-4o-mini (Cost Efficient & Fast)</option>
                                <option value="gpt-4-turbo">gpt-4-turbo (Legacy High Capability)</option>
                            </select>
                        @elseif($aiProvider === 'gemini')
                            <select wire:model="aiModel" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-xs bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none text-slate-800 font-semibold appearance-none">
                                <option value="gemini-1.5-flash">gemini-1.5-flash (Fast & Accurate)</option>
                                <option value="gemini-1.5-pro">gemini-1.5-pro (Highly Complex Reasoning)</option>
                            </select>
                        @elseif($aiProvider === 'deepseek')
                            <select wire:model="aiModel" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-xs bg-white focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 outline-none text-slate-800 font-semibold appearance-none">
                                <option value="deepseek-chat">deepseek-chat (General / Chat - Fast V3)</option>
                                <option value="deepseek-reasoner">deepseek-reasoner (Deep Reasoning - R1)</option>
                            </select>
                        @endif
                    </div>

                    <!-- Read Previous Chats Option -->
                    <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-4 transition-all hover:bg-slate-100/50">
                        <div class="flex items-start justify-between">
                            <div class="pr-4">
                                <label class="block text-xs font-extrabold text-slate-800 flex items-center space-x-2">
                                    <svg class="w-4 h-4 text-indigo-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                    </svg>
                                    <span>Read Previous Customer Chats</span>
                                </label>
                                <span class="text-[11px] text-slate-500 mt-1 block leading-relaxed font-medium">
                                    When enabled, the AI Agent reads all past conversation history for this contact to provide complete context.
                                </span>
                            </div>
                            <div class="flex items-center h-5 mt-0.5 flex-shrink-0">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" wire:model="aiReadPreviousChats" class="sr-only peer">
                                    <div class="w-9 h-5 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Auto Engage Feature -->
                    <div class="bg-gradient-to-br from-violet-50 to-indigo-50 border border-violet-200/80 rounded-xl p-4 transition-all">
                        <div class="flex items-start justify-between">
                            <div class="pr-4 flex-1">
                                <label class="block text-xs font-extrabold text-slate-800 flex items-center space-x-2">
                                    <svg class="w-4 h-4 text-violet-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                    <span>Auto Engage</span>
                                    @if($autoEngageEligibleCount > 0)
                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold bg-violet-600 text-white animate-pulse">
                                            {{ $autoEngageEligibleCount }} eligible
                                        </span>
                                    @endif
                                </label>
                                <span class="text-[11px] text-slate-500 mt-1 block leading-relaxed font-medium">
                                    Sends a time-aware re-engagement message before the 24-hr WhatsApp window expires (*in the customer's exact language*).
                                </span>
                            </div>
                            <div class="flex items-center h-5 mt-0.5 flex-shrink-0">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" wire:model="autoEngageEnabled" class="sr-only peer" id="auto-engage-toggle">
                                    <div class="w-9 h-5 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-violet-600"></div>
                                </label>
                            </div>
                        </div>

                        @if($autoEngageEnabled)
                            <div class="mt-3 pt-2.5 border-t border-violet-200/60 flex items-center justify-between">
                                <div class="flex items-center space-x-1.5 text-[10px] font-bold text-violet-700">
                                    <span class="w-2 h-2 rounded-full bg-violet-500 animate-pulse inline-block"></span>
                                    <span>Auto Engage Active — runs every 15 mins</span>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Default Auto AI Resume Timer -->
                    <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-4 transition-all">
                        <div class="flex items-center justify-between">
                            <div class="pr-4">
                                <label class="block text-xs font-extrabold text-slate-800 flex items-center space-x-2">
                                    <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>Auto-Reactivate AI After Human Handoff</span>
                                </label>
                                <span class="text-[11px] text-slate-500 mt-1 block leading-relaxed font-medium">
                                    Automatically turn AI back ON for chats if no new human messages are sent after the specified duration.
                                </span>
                            </div>
                            <div class="flex-shrink-0">
                                <select wire:model="aiAutoResumeMinutes" class="border border-slate-200 rounded-xl px-3 py-1.5 text-xs bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none text-slate-800 font-bold cursor-pointer">
                                    <option value="0">Off / Manual Only</option>
                                    <option value="15">After 15 minutes</option>
                                    <option value="30">After 30 minutes</option>
                                    <option value="60">After 1 hour</option>
                                    <option value="120">After 2 hours</option>
                                    <option value="240">After 4 hours</option>
                                    <option value="1440">After 24 hours</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- System Prompt & Instructions -->
                    <div>
                        <div class="flex justify-between items-center mb-1.5">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest">System Instructions / Prompt</label>
                            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Agent Persona</span>
                        </div>
                        <textarea wire:model="aiSystemPrompt" rows="5" placeholder="Define the role of the AI agent..." 
                                  class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none text-slate-800 font-mono leading-relaxed"></textarea>
                        
                        <!-- Presets Section -->
                        <div class="mt-3 bg-slate-50/60 p-3 rounded-xl border border-slate-100">
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block mb-2">Apply Template Preset:</span>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" wire:click="applyPreset('sales')" class="bg-white hover:bg-slate-50 text-slate-700 text-xs px-3 py-1.5 rounded-lg transition font-semibold border border-slate-200 shadow-xs flex items-center space-x-1">
                                    <span>🛒</span> <span>Sales & Qualifier</span>
                                </button>
                                <button type="button" wire:click="applyPreset('automotive')" class="bg-white hover:bg-slate-50 text-slate-700 text-xs px-3 py-1.5 rounded-lg transition font-semibold border border-slate-200 shadow-xs flex items-center space-x-1">
                                    <span>🚗</span> <span>Automotive Dealership</span>
                                </button>
                                <button type="button" wire:click="applyPreset('saas')" class="bg-white hover:bg-slate-50 text-slate-700 text-xs px-3 py-1.5 rounded-lg transition font-semibold border border-slate-200 shadow-xs flex items-center space-x-1">
                                    <span>💻</span> <span>SaaS Consultant</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Save Trigger -->
                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs uppercase tracking-wider px-5 py-2.5 rounded-xl shadow-xs transition">
                            Save Agent Configuration
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Panel: Interactive Playground / Test Suite (5 cols) -->
        <div class="lg:col-span-5 space-y-6">
            <div class="bg-slate-900 text-white rounded-2xl shadow-md p-6 relative overflow-hidden border border-slate-800">
                <h2 class="text-sm font-extrabold pb-3 mb-4 border-b border-slate-800 flex items-center space-x-2 text-slate-100 tracking-wide">
                    <svg class="w-4 h-4 text-indigo-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>Agent Testing Playground</span>
                </h2>

                <p class="text-xs text-slate-400 mb-5 leading-relaxed font-medium">Verify qualification scores, extracted BANT parameters, and simulated responses in real time.</p>

                @if($playgroundError)
                    <div class="mb-4 p-3 bg-red-950/40 text-red-300 border border-red-900/40 rounded-xl text-xs font-semibold">
                        {{ $playgroundError }}
                    </div>
                @endif

                <div class="space-y-4">
                    <!-- Optional Chat History -->
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Simulated Chat History (Optional)</label>
                        <textarea wire:model="testHistory" rows="3" placeholder="e.g. Agent: Welcome to Acme! What car model interests you?" 
                                  class="w-full bg-slate-950/80 border border-slate-800 rounded-xl px-3 py-2 text-xs focus:ring-1 focus:ring-indigo-400 outline-none text-slate-100 font-mono leading-relaxed"></textarea>
                    </div>

                    <!-- New Customer Message -->
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Incoming Customer Message</label>
                        <input type="text" wire:model="testMessage" placeholder="e.g. I need a CNC machine, my budget is $15k and I need it by next week." 
                               class="w-full bg-slate-950/80 border border-slate-800 rounded-xl px-3 py-2.5 text-xs focus:ring-1 focus:ring-indigo-400 outline-none text-slate-100">
                    </div>

                    <!-- Send Request -->
                    <button type="button" wire:click="testAgent" 
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold uppercase tracking-wider py-2.5 px-4 rounded-xl transition flex items-center justify-center space-x-1.5 shadow-xs">
                        <span wire:loading.remove wire:target="testAgent">Run Simulation</span>
                        <span wire:loading wire:target="testAgent" class="inline-block animate-spin rounded-full h-3.5 w-3.5 border-t-2 border-r-2 border-white mr-1"></span>
                        <span wire:loading wire:target="testAgent">Thinking...</span>
                    </button>
                </div>
            </div>

            <!-- Simulation Results Output -->
            @if($playgroundResult)
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-5 space-y-4">
                    <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                        <h3 class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Simulation Output</h3>
                        @if($playgroundResult['handoff_required'] ?? false)
                            <span class="bg-rose-50 text-rose-700 border border-rose-100 text-[10px] px-2.5 py-0.5 rounded-full font-bold flex items-center space-x-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-600 animate-ping"></span>
                                <span>HANDOFF REQUIRED</span>
                            </span>
                        @else
                            <span class="bg-emerald-50 text-emerald-700 border border-emerald-100 text-[10px] px-2.5 py-0.5 rounded-full font-bold flex items-center space-x-1">
                                <span>AI HANDLED</span>
                            </span>
                        @endif
                    </div>

                    <!-- Visual Qualification Cards -->
                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div class="bg-slate-50 border border-slate-100 p-3 rounded-xl">
                            <span class="text-slate-400 block mb-1 font-semibold uppercase text-[9px] tracking-wider">Suggested Stage</span>
                            <span class="font-extrabold text-slate-800 text-xs">
                                {{ $playgroundResult['recommended_stage'] ?? 'New Lead' }}
                            </span>
                        </div>
                        <div class="bg-slate-50 border border-slate-100 p-3 rounded-xl flex flex-col justify-between">
                            <span class="text-slate-400 block mb-1 font-semibold uppercase text-[9px] tracking-wider">Qualification Score</span>
                            <div class="flex items-center space-x-2 mt-1">
                                <div class="w-full bg-slate-200 rounded-full h-1.5">
                                    <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $playgroundResult['lead_score'] ?? 0 }}%"></div>
                                </div>
                                <span class="font-extrabold text-slate-700 text-xs">{{ $playgroundResult['lead_score'] ?? 0 }}/100</span>
                            </div>
                        </div>
                    </div>

                    <!-- BANT Extracted Data -->
                    <div class="bg-slate-50 border border-slate-100 p-3.5 rounded-xl space-y-2 text-xs">
                        <div class="font-bold text-slate-400 border-b border-slate-200/60 pb-1.5 mb-1.5 uppercase text-[9px] tracking-widest">Extracted Attributes</div>
                        <div class="flex justify-between py-0.5">
                            <span class="text-slate-400 font-semibold">Desired Product:</span>
                            <span class="text-slate-800 font-extrabold">{{ $playgroundResult['req_product'] ?? 'None Identified' }}</span>
                        </div>
                        <div class="flex justify-between py-0.5">
                            <span class="text-slate-400 font-semibold">Customer Budget:</span>
                            <span class="text-slate-800 font-extrabold">{{ $playgroundResult['req_budget'] ?? 'None Identified' }}</span>
                        </div>
                        <div class="flex justify-between py-0.5">
                            <span class="text-slate-400 font-semibold">Buying Timeline:</span>
                            <span class="text-slate-800 font-extrabold">{{ $playgroundResult['req_timeline'] ?? 'None Identified' }}</span>
                        </div>
                    </div>

                    <!-- Generated WhatsApp Message bubble -->
                    <div class="space-y-1.5">
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Generated Agent Reply</div>
                        <div class="bg-indigo-50/60 border border-indigo-100 p-3.5 rounded-xl text-xs text-indigo-950 leading-relaxed font-medium">
                            {{ $playgroundResult['next_reply'] ?? 'No response generated.' }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
