<div class="p-8 max-w-7xl mx-auto font-sans" x-data="{ activeTab: 'canvas' }">
    
    <!-- Top Header Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Visual Drag & Drop WhatsApp Automations</h1>
            <p class="text-gray-600 mt-1">Design automated multi-step messaging flows, keyword auto-responders, and pipeline stage triggers.</p>
        </div>

        <div class="flex items-center space-x-3">
            <button wire:click="$set('showCreateModal', true)" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-lg shadow-sm font-semibold flex items-center space-x-2 transition">
                <span>✨ + Create New Automation</span>
            </button>

            <button @click="activeTab = 'recipes'" class="bg-white border border-gray-300 text-gray-700 px-4 py-2.5 rounded-lg shadow-sm font-semibold hover:bg-gray-50 flex items-center space-x-2 transition">
                <span>📚 Workflow Recipes</span>
            </button>

            @if($activeWorkflowId)
                <button wire:click="saveWorkflow" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-lg shadow-sm font-semibold flex items-center space-x-2 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                    <span>Save Flow</span>
                </button>
            @endif
        </div>
    </div>

    @if($statusMessage)
        <div class="mb-6 p-4 rounded-lg flex items-center justify-between {{ $statusType === 'success' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-indigo-100 text-indigo-800 border border-indigo-300' }}">
            <span class="font-semibold text-xs md:text-sm">{{ $statusMessage }}</span>
            <button wire:click="$set('statusMessage', null)" class="text-sm font-bold opacity-75 hover:opacity-100">&times;</button>
        </div>
    @endif

    <!-- Workflow Builder Header Options & Selector -->
    <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center space-x-3 flex-1">
            <!-- Active Workflow Selector Dropdown -->
            <label class="text-xs font-bold text-gray-400 uppercase tracking-wider whitespace-nowrap">Active Flow:</label>
            <select wire:change="selectWorkflow($event.target.value)" class="border border-gray-300 rounded-lg px-3 py-2 text-sm font-bold text-gray-900 bg-white focus:ring-2 focus:ring-indigo-500 outline-none max-w-xs">
                @foreach($workflows as $wf)
                    <option value="{{ $wf->id }}" {{ $activeWorkflowId == $wf->id ? 'selected' : '' }}>
                        {{ $wf->title }} ({{ $wf->status }})
                    </option>
                @endforeach
            </select>

            <input type="text" wire:model.live="workflowTitle" placeholder="Automation Title..." class="text-base font-bold text-gray-900 border-b border-dashed border-gray-300 focus:border-indigo-500 outline-none pb-0.5 flex-1 min-w-[200px]">
            
            <button type="button" wire:click="toggleWorkflowStatus({{ $activeWorkflowId }})" class="cursor-pointer {{ $workflowStatus === 'ACTIVE' ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 'bg-amber-100 text-amber-800 border-amber-300' }} text-xs font-extrabold px-3 py-1.5 rounded-full border flex items-center space-x-1 transition hover:opacity-80">
                <span class="w-2 h-2 rounded-full {{ $workflowStatus === 'ACTIVE' ? 'bg-emerald-500 animate-pulse' : 'bg-amber-500' }} inline-block"></span>
                <span>STATUS: {{ $workflowStatus }}</span>
            </button>
        </div>

        <!-- Canvas Node Palette Controls -->
        <div class="flex items-center space-x-2">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider mr-2">Add Step:</span>
            <button wire:click="addNode('TRIGGER')" class="bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-300 text-xs font-bold px-3 py-1.5 rounded-lg transition flex items-center space-x-1">
                <span>⚡ + Trigger</span>
            </button>
            <button wire:click="addNode('ACTION')" class="bg-blue-50 hover:bg-blue-100 text-blue-800 border border-blue-300 text-xs font-bold px-3 py-1.5 rounded-lg transition flex items-center space-x-1">
                <span>💬 + Action</span>
            </button>
            <button wire:click="addNode('DELAY')" class="bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300 text-xs font-bold px-3 py-1.5 rounded-lg transition flex items-center space-x-1">
                <span>⏱️ + Delay</span>
            </button>
            <button wire:click="addNode('CONDITION')" class="bg-purple-50 hover:bg-purple-100 text-purple-800 border border-purple-300 text-xs font-bold px-3 py-1.5 rounded-lg transition flex items-center space-x-1">
                <span>🔀 + Condition</span>
            </button>
        </div>
    </div>

    <!-- Tab Content 1: Visual Interactive Canvas -->
    <div x-show="activeTab === 'canvas'" class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Left Side: Interactive Node Canvas -->
        <div class="lg:col-span-8 bg-[#0b0f19] rounded-2xl border border-slate-800 shadow-2xl relative min-h-[700px] overflow-hidden" 
             x-data="flowChart({
                 nodes: @entangle('nodes'),
                 connections: @entangle('connections'),
                 selectedNodeIndex: @entangle('selectedNodeIndex'),
                 selectedNodeId: @entangle('selectedNodeId')
             })"
             x-ref="canvas"
             @pointerdown="handlePointerDown($event)"
             @pointermove="handlePointerMove($event)"
             @pointerup="handlePointerUp($event)"
             @wheel.prevent="handleWheel($event)">
             
             <!-- Dynamic Dots Grid Background -->
             <div class="absolute inset-0 pointer-events-none" 
                  :style="'background-color: #0b0f19; background-image: radial-gradient(#1e293b 1.5px, transparent 1.5px); background-size: ' + (24 * scale) + 'px ' + (24 * scale) + 'px; background-position: ' + panX + 'px ' + panY + 'px;'">
             </div>
             
             <!-- Canvas Header Info & Simulation Controls -->
             <div class="absolute top-4 left-4 z-30 flex items-center space-x-3 pointer-events-auto">
                 <div class="text-xs font-bold text-slate-400 uppercase tracking-widest flex items-center space-x-2 select-none">
                     <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                     <span>Interactive Wiring Canvas</span>
                 </div>
                 <button type="button" @click="simulateFlow()" :disabled="isSimulating" class="bg-indigo-600 hover:bg-indigo-700 disabled:bg-indigo-800 text-white text-[10px] font-bold px-3 py-1.5 rounded-lg shadow-lg hover:shadow-indigo-500/20 disabled:shadow-none transition flex items-center space-x-1.5">
                     <span x-show="!isSimulating">⚡ Simulate Flow Run</span>
                     <span x-show="isSimulating" class="flex items-center space-x-1">
                         <svg class="animate-spin h-3 w-3 text-white mr-1" fill="none" viewBox="0 0 24 24">
                             <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                             <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                         </svg>
                         <span>Running Simulation...</span>
                     </span>
                 </button>
             </div>

             <!-- Floating Canvas Controls -->
             <div class="absolute top-4 right-4 z-30 flex flex-col space-y-2 bg-slate-900/90 border border-slate-800 p-1.5 rounded-xl shadow-lg">
                 <button type="button" @click="zoomIn()" class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-slate-700 text-white font-extrabold text-sm flex items-center justify-center transition-colors" title="Zoom In">+</button>
                 <button type="button" @click="zoomOut()" class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-slate-700 text-white font-extrabold text-sm flex items-center justify-center transition-colors" title="Zoom Out">-</button>
                 <button type="button" @click="recenter()" class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-slate-700 text-white text-xs flex items-center justify-center transition-colors" title="Recenter View">🎯</button>
                 <div class="text-[9px] font-mono text-slate-400 text-center pt-1" x-text="Math.round(scale * 100) + '%'"></div>
             </div>
             
             <!-- Instructions overlay -->
             <div class="absolute bottom-4 left-4 text-[10px] text-slate-500 pointer-events-none select-none z-30 bg-slate-900/90 px-3 py-2 rounded-lg border border-slate-800 shadow-md">
                 💡 <strong>Controls:</strong> Drag empty background to pan. Mouse wheel to zoom. Drag nodes by their headers. Drag from output ports to input ports. Click wires to delete.
             </div>

             <!-- Transformed Workspace Container -->
             <div class="absolute inset-0 origin-top-left pointer-events-none"
                  :style="'transform: translate(' + panX + 'px, ' + panY + 'px) scale(' + scale + ');'">
                  
                  <div class="relative w-full h-full pointer-events-auto" style="min-width: 3000px; min-height: 2000px;">
                      
                      <!-- SVG Marker Defs & Drafting Line (Static Layer) -->
                      <svg class="absolute inset-0 w-full h-full pointer-events-none" style="z-index: 10; overflow: visible;">
                          <defs>
                              <marker id="arrow" viewBox="0 0 10 10" refX="8" refY="5" markerWidth="6" markerHeight="6" orient="auto-start-reverse">
                                  <path d="M 0 1.5 L 8 5 L 0 8.5 z" fill="#64748b" />
                              </marker>
                          </defs>
                          
                          <!-- Connection Drafting Curve -->
                          <path x-show="drawingConnection" :d="getDraftingPath()" stroke="#3b82f6" stroke-width="2.5" stroke-dasharray="5,5" fill="none" />
                      </svg>
                      
                      <!-- Dynamic Wires Layer -->
                      <template x-for="(conn, idx) in connections" :key="'conn-' + idx">
                          <svg class="absolute inset-0 w-full h-full pointer-events-none" style="z-index: 11; overflow: visible;">
                              <g class="pointer-events-auto cursor-pointer group">
                                  <path :d="getBezierPath(conn)" stroke="transparent" stroke-width="12" fill="none" @click.stop="deleteConnection(conn)" />
                                  <path :d="getBezierPath(conn)" stroke="#475569" stroke-width="2.5" fill="none" class="group-hover:stroke-red-500 group-hover:stroke-[3px] transition-all" marker-end="url(#arrow)" />
                                  
                                  <g class="opacity-0 group-hover:opacity-100 transition-opacity">
                                      <circle :cx="getBezierCenter(conn).x" :cy="getBezierCenter(conn).y" r="9" fill="#ef4444" @click.stop="deleteConnection(conn)" />
                                      <text :x="getBezierCenter(conn).x" :y="getBezierCenter(conn).y + 3" fill="white" font-size="10" text-anchor="middle" font-weight="bold" class="pointer-events-none">&times;</text>
                                  </g>
                              </g>
                          </svg>
                      </template>

                      <!-- Simulation Packet Animation Layer -->
                      <template x-for="packet in activePackets" :key="packet.id">
                          <svg class="absolute inset-0 w-full h-full pointer-events-none" style="z-index: 12; overflow: visible;">
                              <circle r="6" :fill="packet.color" class="glow-packet">
                                  <animateMotion :dur="packet.dur + 'ms'" repeatCount="1" fill="freeze" :path="packet.path" />
                              </circle>
                          </svg>
                      </template>

                      <!-- Render Nodes -->
                      <div class="relative w-full h-full">
                          <template x-for="(node, index) in nodes" :key="node.id">
                              <div class="flow-node absolute bg-slate-900 border border-slate-800 rounded-xl shadow-xl text-white select-none transition-all duration-150 cursor-grab hover:shadow-2xl hover:border-slate-700"
                                   :style="'left: ' + node.x + 'px; top: ' + node.y + 'px; width: 260px; height: 96px; z-index: 20;'"
                                   :class="{
                                       'border-emerald-500 ring-2 ring-emerald-500/20': selectedNodeId === node.id,
                                       'ring-4 ring-indigo-500/80 shadow-[0_0_20px_rgba(99,102,241,0.5)] border-indigo-400': simStates[node.id] === 'active',
                                       'ring-4 ring-emerald-500/80 shadow-[0_0_20px_rgba(16,185,129,0.5)] border-emerald-400': simStates[node.id] === 'completed',
                                       'ring-4 ring-amber-500/80 shadow-[0_0_20px_rgba(245,158,11,0.5)] border-amber-400 animate-pulse': simStates[node.id] === 'waiting',
                                       'ring-4 ring-red-500/80 shadow-[0_0_20px_rgba(239,68,68,0.5)] border-red-400': simStates[node.id] === 'failed'
                                   }"
                                   :data-index="index"
                                   @click="selectNode(index)">
                                   
                                   <div class="h-full flex flex-col justify-between p-3 relative">
                                       <div class="flex items-center justify-between pb-1.5 border-b border-slate-800">
                                           <div class="flex items-center space-x-1.5 truncate pointer-events-none">
                                               <span x-show="simStates[node.id]" class="w-2.5 h-2.5 rounded-full animate-ping mr-1"
                                                     :class="{
                                                         'bg-indigo-400': simStates[node.id] === 'active',
                                                         'bg-emerald-400': simStates[node.id] === 'completed',
                                                         'bg-amber-400': simStates[node.id] === 'waiting',
                                                         'bg-red-400': simStates[node.id] === 'failed'
                                                     }"></span>
                                               <span class="text-base" x-text="node.icon"></span>
                                               <span class="font-bold text-xs text-slate-200 truncate" x-text="node.title"></span>
                                           </div>
                                           <button type="button" @click.stop="$wire.removeNode(index)" class="text-slate-500 hover:text-red-400 font-bold text-sm ml-2 select-none">&times;</button>
                                       </div>

                                       <div class="flex items-center justify-between flex-grow pt-2 pointer-events-none">
                                           <p class="text-[10px] text-slate-400 leading-tight truncate" x-text="node.subtitle"></p>
                                           <span x-show="simStates[node.id] === 'waiting'" class="text-[9px] font-extrabold text-amber-400 font-mono ml-2">WAITING...</span>
                                           <span x-show="simStates[node.id] === 'completed'" class="text-xs text-emerald-400 ml-2">✓</span>
                                           <span x-show="simStates[node.id] === 'failed'" class="text-xs text-red-400 ml-2">✗</span>
                                       </div>

                                       <div class="flex items-center justify-between mt-1 text-[9px] font-extrabold uppercase pointer-events-none">
                                           <span class="px-1.5 py-0.5 rounded text-[8px]" :class="node.color" x-text="node.type"></span>
                                           <span class="text-slate-500 font-mono" x-text="'ID: ' + node.id"></span>
                                       </div>

                                       <!-- Ports -->
                                       <template x-if="node.type !== 'TRIGGER'">
                                           <div class="port absolute -left-2 top-[48px] -translate-y-1/2 w-4 h-4 rounded-full bg-slate-900 border-2 border-slate-600 flex items-center justify-center transition-all duration-150 z-30"
                                                :class="hoveredInputNodeId === node.id ? 'border-emerald-400 bg-emerald-950 scale-125' : 'hover:scale-125 hover:border-emerald-500'"
                                                style="cursor: crosshair;">
                                                <div class="w-1.5 h-1.5 rounded-full" :class="hoveredInputNodeId === node.id ? 'bg-emerald-400' : 'bg-slate-500'"></div>
                                           </div>
                                       </template>

                                       <template x-if="node.type !== 'CONDITION'">
                                           <div class="port absolute -right-2 top-[48px] -translate-y-1/2 w-4 h-4 rounded-full bg-slate-900 border-2 border-slate-600 hover:border-emerald-400 hover:scale-125 transition-all duration-150 flex items-center justify-center z-30"
                                                @pointerdown.stop="startConnection($event, node.id, 'output')"
                                                style="cursor: crosshair;">
                                                <div class="w-1.5 h-1.5 rounded-full bg-slate-500 hover:bg-emerald-400"></div>
                                           </div>
                                       </template>

                                       <template x-if="node.type === 'CONDITION'">
                                           <div class="w-full">
                                               <div class="port absolute -right-2 top-[32px] -translate-y-1/2 w-4 h-4 rounded-full bg-slate-900 border-2 border-emerald-500 hover:border-emerald-400 hover:scale-125 transition-all duration-150 flex items-center justify-center z-30"
                                                    @pointerdown.stop="startConnection($event, node.id, 'true')"
                                                    style="cursor: crosshair;">
                                                    <div class="w-1.5 h-1.5 rounded-full bg-emerald-500"></div>
                                               </div>
                                               <span class="absolute right-3.5 top-[26px] text-[8px] font-bold text-emerald-400 select-none">TRUE</span>

                                               <div class="port absolute -right-2 top-[64px] -translate-y-1/2 w-4 h-4 rounded-full bg-slate-900 border-2 border-red-500 hover:border-red-400 hover:scale-125 transition-all duration-150 flex items-center justify-center z-30"
                                                    @pointerdown.stop="startConnection($event, node.id, 'false')"
                                                    style="cursor: crosshair;">
                                                    <div class="w-1.5 h-1.5 rounded-full bg-red-500"></div>
                                               </div>
                                               <span class="absolute right-3.5 top-[58px] text-[8px] font-bold text-red-400 select-none">FALSE</span>
                                           </div>
                                       </template>
                                   </div>
                              </div>
                          </template>
                      </div>
                  </div>
             </div>
        </div>

        <!-- Right Side: Node Settings & Live Automations List -->
        <div class="lg:col-span-4 space-y-6">
            
            <!-- Node Inspector Panel -->
            <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm space-y-4">
                <h3 class="font-bold text-gray-900 text-sm border-b pb-2 border-gray-100 flex items-center space-x-2">
                    <svg class="w-4 h-4 text-emerald-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    <span>Selected Step Property Inspector</span>
                </h3>

                @if($selectedNodeIndex !== null)
                    <div class="space-y-4 text-xs">
                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Step Title</label>
                            <input type="text" wire:model.live="inspectorTitle" wire:change="saveNodeChanges" class="w-full border border-gray-300 rounded px-2.5 py-1.5 focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>

                        @if(isset($nodes[$selectedNodeIndex]) && $nodes[$selectedNodeIndex]['type'] === 'TRIGGER')
                            <div>
                                <label class="block font-bold text-gray-700 mb-1">Trigger Event</label>
                                <select class="w-full border border-gray-300 rounded px-2.5 py-1.5 bg-white focus:ring-2 focus:ring-indigo-500 outline-none">
                                    <option>Incoming Keyword Match</option>
                                    <option>Pipeline Stage Changed</option>
                                    <option>Form Webhook Trigger</option>
                                </select>
                            </div>

                            <div>
                                <label class="block font-bold text-gray-700 mb-1">Keywords List (comma separated)</label>
                                <input type="text" wire:model.live="inspectorKeyword" wire:change="saveNodeChanges" class="w-full border border-gray-300 rounded px-2.5 py-1.5 font-mono focus:ring-2 focus:ring-indigo-500 outline-none">
                            </div>
                        @endif

                        @if(isset($nodes[$selectedNodeIndex]) && $nodes[$selectedNodeIndex]['type'] === 'ACTION')
                            <div>
                                <label class="block font-bold text-gray-700 mb-1">Meta WhatsApp Template</label>
                                <select wire:model.live="inspectorTemplate" wire:change="saveNodeChanges" class="w-full border border-gray-300 rounded px-2.5 py-1.5 bg-white font-semibold text-emerald-800 focus:ring-2 focus:ring-indigo-500 outline-none">
                                    <option value="lead_welcome_offer">lead_welcome_offer (APPROVED)</option>
                                    <option value="quotation_ready_link">quotation_ready_link (APPROVED)</option>
                                </select>
                            </div>
                        @endif

                        @if(isset($nodes[$selectedNodeIndex]) && $nodes[$selectedNodeIndex]['type'] === 'DELAY')
                            <div>
                                <label class="block font-bold text-gray-700 mb-1">Delay Duration</label>
                                <input type="text" wire:model.live="inspectorDelay" wire:change="saveNodeChanges" placeholder="e.g. 15m, 1h, 2d" class="w-full border border-gray-300 rounded px-2.5 py-1.5 focus:ring-2 focus:ring-indigo-500 outline-none">
                            </div>
                        @endif

                        @if(isset($nodes[$selectedNodeIndex]) && $nodes[$selectedNodeIndex]['type'] === 'CONDITION')
                            <div>
                                <label class="block font-bold text-gray-700 mb-1">Condition Type</label>
                                <select wire:model.live="inspectorCondition" wire:change="saveNodeChanges" class="w-full border border-gray-300 rounded px-2.5 py-1.5 bg-white focus:ring-2 focus:ring-indigo-500 outline-none">
                                    <option value="clicked_cta">Customer Clicked CTA Button</option>
                                    <option value="read_status">Message was Read</option>
                                </select>
                            </div>
                        @endif
                    </div>
                @else
                    <p class="text-xs text-gray-500 italic">Click on any node in the visual diagram canvas to select and inspect/edit its configuration properties.</p>
                @endif
            </div>

            <!-- All Saved Automations List -->
            <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm space-y-3">
                <div class="flex justify-between items-center border-b pb-2 border-gray-100">
                    <h3 class="font-bold text-gray-800 text-sm">All Saved Automations ({{ count($workflows) }})</h3>
                    <button wire:click="$set('showCreateModal', true)" class="text-xs font-bold text-indigo-600 hover:underline">+ New</button>
                </div>
                
                <div class="space-y-2 max-h-96 overflow-y-auto pr-1">
                    @foreach($workflows as $wf)
                        <div wire:click="selectWorkflow({{ $wf->id }})" 
                             class="p-3 rounded-lg border transition cursor-pointer flex items-center justify-between {{ $activeWorkflowId == $wf->id ? 'bg-indigo-50/70 border-indigo-300 text-indigo-950 font-bold shadow-xs' : 'bg-gray-50 hover:bg-gray-100 border-gray-200 text-gray-800' }}">
                            <div class="flex-1 pr-2">
                                <div class="font-bold text-xs truncate">{{ $wf->title }}</div>
                                <div class="text-[10px] text-gray-500 truncate">{{ $wf->trigger_summary ?? 'Custom Flow' }}</div>
                            </div>
                            <div class="flex items-center space-x-1.5">
                                <button type="button" wire:click.stop="toggleWorkflowStatus({{ $wf->id }})" class="text-[9px] font-extrabold px-2 py-0.5 rounded {{ $wf->status === 'ACTIVE' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $wf->status }}
                                </button>
                                <button type="button" wire:click.stop="deleteWorkflow({{ $wf->id }})" class="text-gray-400 hover:text-red-600 font-bold text-xs px-1">&times;</button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

    </div>

    <!-- Create New Automation Modal -->
    @if($showCreateModal)
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 border border-slate-200 space-y-5 animate-fade-in">
                <div class="flex justify-between items-center border-b pb-3">
                    <h3 class="text-base font-extrabold text-gray-900">Create New WhatsApp Automation</h3>
                    <button wire:click="$set('showCreateModal', false)" class="text-gray-400 hover:text-gray-600 font-bold text-xl leading-none">&times;</button>
                </div>

                <form wire:submit.prevent="createNewWorkflow" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Automation Title</label>
                        <input type="text" wire:model="newWorkflowTitle" placeholder="e.g., Black Friday Flash Sale Welcome Flow" class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 text-xs focus:ring-2 focus:ring-indigo-500 outline-none text-gray-800 font-semibold" required>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Trigger Condition</label>
                        <select wire:model="newWorkflowTrigger" class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 text-xs bg-white focus:ring-2 focus:ring-indigo-500 outline-none text-gray-800 font-semibold">
                            <option value="keyword">⚡ Keyword Match (e.g. "OFFER", "PRICE")</option>
                            <option value="stage_change">🎯 Pipeline Stage Changed (e.g. Moved to Proposal)</option>
                            <option value="after_hours">🌙 Outside Business Hours (9 AM - 6 PM)</option>
                            <option value="lead_created">👤 New Contact Created in CRM</option>
                        </select>
                    </div>

                    <div class="pt-3 border-t flex justify-end space-x-2">
                        <button type="button" wire:click="$set('showCreateModal', false)" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold px-4 py-2.5 rounded-xl transition">Cancel</button>
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-5 py-2.5 rounded-xl shadow-xs transition">Create Automation Flow</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Tab Content 2: Pre-built Recipes -->
    <div x-show="activeTab === 'recipes'" class="space-y-6">
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
            <h2 class="text-xl font-bold text-gray-900 mb-2">Pre-built Automation Recipes</h2>
            <p class="text-xs text-gray-500 mb-6">One-click install industry-proven automated WhatsApp lead nurturing flows.</p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="border border-gray-200 rounded-xl p-5 hover:shadow-md transition bg-gray-50">
                    <div class="text-2xl mb-2">⚡</div>
                    <h3 class="font-bold text-gray-900 text-sm mb-1">Instant Lead Welcome & Quote</h3>
                    <p class="text-xs text-gray-600 mb-4">Automatically send a greeting and quotation link when a customer sends "QUOTE".</p>
                    <button @click="activeTab = 'canvas'" class="w-full bg-emerald-600 text-white font-bold text-xs py-2 rounded-lg hover:bg-emerald-700">Use Recipe</button>
                </div>

                <div class="border border-gray-200 rounded-xl p-5 hover:shadow-md transition bg-gray-50">
                    <div class="text-2xl mb-2">⏱️</div>
                    <h3 class="font-bold text-gray-900 text-sm mb-1">24h Follow-up Sequence</h3>
                    <p class="text-xs text-gray-600 mb-4">Waits 24 hours after proposal is sent. If unread, sends a polite reminder message.</p>
                    <button @click="activeTab = 'canvas'" class="w-full bg-emerald-600 text-white font-bold text-xs py-2 rounded-lg hover:bg-emerald-700">Use Recipe</button>
                </div>

                <div class="border border-gray-200 rounded-xl p-5 hover:shadow-md transition bg-gray-50">
                    <div class="text-2xl mb-2">🌙</div>
                    <h3 class="font-bold text-gray-900 text-sm mb-1">After-Hours Auto Responder</h3>
                    <p class="text-xs text-gray-600 mb-4">Replies automatically during non-business hours informing customer of opening time.</p>
                    <button @click="activeTab = 'canvas'" class="w-full bg-emerald-600 text-white font-bold text-xs py-2 rounded-lg hover:bg-emerald-700">Use Recipe</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .cursor-grab { cursor: grab; }
        .cursor-grab:active { cursor: grabbing; }
        .port { touch-action: none; }
        .glow-packet { filter: drop-shadow(0 0 6px var(--tw-shadow-color, currentColor)); }
    </style>

    <script>
        (function() {
            const flowChartData = (config) => ({
                nodes: config.nodes,
                connections: config.connections,
                selectedNodeIndex: config.selectedNodeIndex,
                selectedNodeId: config.selectedNodeId,
                
                panX: 0,
                panY: 0,
                scale: 1.0,
                isPanning: false,
                panStartX: 0,
                panStartY: 0,

                draggingNodeIndex: null,
                dragOffsetX: 0,
                dragOffsetY: 0,
                drawingConnection: null,
                hoveredInputNodeId: null,

                isSimulating: false,
                simStates: {},
                activePackets: [],

                init() {
                    window.addEventListener('pointermove', (e) => this.handlePointerMove(e));
                    window.addEventListener('pointerup', (e) => this.handlePointerUp(e));
                },

                handlePointerDown(e) {
                    if (e.target.closest('.port, button, input, select, option, a')) return;
                    
                    const nodeEl = e.target.closest('.flow-node');
                    if (nodeEl) {
                        const index = parseInt(nodeEl.dataset.index);
                        this.draggingNodeIndex = index;
                        const node = this.nodes[index];
                        this.dragOffsetX = e.clientX - (this.panX + node.x * this.scale);
                        this.dragOffsetY = e.clientY - (this.panY + node.y * this.scale);
                        e.preventDefault();
                    } else {
                        this.isPanning = true;
                        this.panStartX = e.clientX - this.panX;
                        this.panStartY = e.clientY - this.panY;
                    }
                },

                handleWheel(e) {
                    const canvas = this.$refs.canvas;
                    if (!canvas) return;
                    const rect = canvas.getBoundingClientRect();
                    const mouseX = e.clientX - rect.left;
                    const mouseY = e.clientY - rect.top;

                    const lx = (mouseX - this.panX) / this.scale;
                    const ly = (mouseY - this.panY) / this.scale;

                    const zoomFactor = 1.1;
                    let newScale = e.deltaY < 0 ? this.scale * zoomFactor : this.scale / zoomFactor;
                    newScale = Math.min(1.8, Math.max(0.5, newScale));

                    this.panX = mouseX - lx * newScale;
                    this.panY = mouseY - ly * newScale;
                    this.scale = newScale;
                },

                zoomIn() {
                    const canvas = this.$refs.canvas;
                    if (!canvas) return;
                    const rect = canvas.getBoundingClientRect();
                    const mouseX = rect.width / 2;
                    const mouseY = rect.height / 2;
                    const lx = (mouseX - this.panX) / this.scale;
                    const ly = (mouseY - this.panY) / this.scale;

                    let newScale = Math.min(1.8, this.scale * 1.25);
                    this.panX = mouseX - lx * newScale;
                    this.panY = mouseY - ly * newScale;
                    this.scale = newScale;
                },

                zoomOut() {
                    const canvas = this.$refs.canvas;
                    if (!canvas) return;
                    const rect = canvas.getBoundingClientRect();
                    const mouseX = rect.width / 2;
                    const mouseY = rect.height / 2;
                    const lx = (mouseX - this.panX) / this.scale;
                    const ly = (mouseY - this.panY) / this.scale;

                    let newScale = Math.max(0.5, this.scale / 1.25);
                    this.panX = mouseX - lx * newScale;
                    this.panY = mouseY - ly * newScale;
                    this.scale = newScale;
                },

                recenter() {
                    this.panX = 0;
                    this.panY = 0;
                    this.scale = 1.0;
                },

                selectNode(index) {
                    this.selectedNodeIndex = index;
                    if (this.nodes[index]) {
                        this.selectedNodeId = this.nodes[index].id;
                    }
                    this.$wire.selectNode(index);
                },

                startConnection(e, nodeId, portType) {
                    e.stopPropagation();
                    const node = this.nodes.find(n => n.id === nodeId);
                    if (!node) return;
                    
                    let portX = node.x + 260;
                    let portY = node.y + 48;
                    if (node.type === 'CONDITION') {
                        portY = node.y + (portType === 'true' ? 32 : 64);
                    }
                    
                    this.drawingConnection = {
                        fromNodeId: nodeId,
                        fromPort: portType,
                        startX: portX,
                        startY: portY,
                        currentX: portX,
                        currentY: portY
                    };
                    
                    e.target.setPointerCapture(e.pointerId);
                },

                handlePointerMove(e) {
                    const canvas = this.$refs.canvas;
                    if (!canvas) return;
                    const rect = canvas.getBoundingClientRect();

                    if (this.isPanning) {
                        this.panX = e.clientX - this.panStartX;
                        this.panY = e.clientY - this.panStartY;
                        return;
                    }

                    if (this.draggingNodeIndex !== null) {
                        const node = this.nodes[this.draggingNodeIndex];
                        let newX = (e.clientX - this.dragOffsetX - this.panX) / this.scale;
                        let newY = (e.clientY - this.dragOffsetY - this.panY) / this.scale;
                        
                        node.x = Math.round(newX / 12) * 12;
                        node.y = Math.round(newY / 12) * 12;
                    }

                    if (this.drawingConnection) {
                        this.drawingConnection.currentX = (e.clientX - rect.left - this.panX) / this.scale;
                        this.drawingConnection.currentY = (e.clientY - rect.top - this.panY) / this.scale;

                        let foundHoveredNode = null;
                        const mouseX = this.drawingConnection.currentX;
                        const mouseY = this.drawingConnection.currentY;

                        this.nodes.forEach(n => {
                            if (n.id === this.drawingConnection.fromNodeId) return;
                            if (n.type === 'TRIGGER') return;
                            
                            const portX = n.x;
                            const portY = n.y + 48;
                            const dist = Math.hypot(mouseX - portX, mouseY - portY);
                            if (dist < 30) {
                                foundHoveredNode = n.id;
                            }
                        });

                        this.hoveredInputNodeId = foundHoveredNode;
                    }
                },

                handlePointerUp(e) {
                    if (this.isPanning) {
                        this.isPanning = false;
                    }
                    if (this.draggingNodeIndex !== null) {
                        this.draggingNodeIndex = null;
                    }

                    if (this.drawingConnection) {
                        if (this.hoveredInputNodeId) {
                            this.$wire.addConnection(
                                this.drawingConnection.fromNodeId,
                                this.hoveredInputNodeId,
                                this.drawingConnection.fromPort
                            );
                        }
                        this.drawingConnection = null;
                        this.hoveredInputNodeId = null;
                    }
                },

                getBezierPath(conn) {
                    const fromNode = this.nodes.find(n => n.id === conn.from);
                    const toNode = this.nodes.find(n => n.id === conn.to);
                    if (!fromNode || !toNode) return '';

                    let x1 = fromNode.x + 260;
                    let y1 = fromNode.y + 48;
                    if (fromNode.type === 'CONDITION') {
                        y1 = fromNode.y + (conn.fromPort === 'true' ? 32 : 64);
                    }

                    const x2 = toNode.x;
                    const y2 = toNode.y + 48;

                    const dx = Math.max(80, Math.abs(x2 - x1) * 0.5);
                    const dy = (y1 === y2) ? 0.01 : 0;
                    return `M ${x1} ${y1 + dy} C ${x1 + dx} ${y1 + dy}, ${x2 - dx} ${y2}, ${x2} ${y2}`;
                },

                getBezierCenter(conn) {
                    const fromNode = this.nodes.find(n => n.id === conn.from);
                    const toNode = this.nodes.find(n => n.id === conn.to);
                    if (!fromNode || !toNode) return { x: 0, y: 0 };

                    let x1 = fromNode.x + 260;
                    let y1 = fromNode.y + 48;
                    if (fromNode.type === 'CONDITION') {
                        y1 = fromNode.y + (conn.fromPort === 'true' ? 32 : 64);
                    }

                    const x2 = toNode.x;
                    const y2 = toNode.y + 48;

                    return {
                        x: (x1 + x2) / 2,
                        y: (y1 + y2) / 2
                    };
                },

                getDraftingPath() {
                    if (!this.drawingConnection) return '';
                    const x1 = this.drawingConnection.startX;
                    const y1 = this.drawingConnection.startY;
                    const x2 = this.drawingConnection.currentX;
                    const y2 = this.drawingConnection.currentY;

                    const dx = Math.max(80, Math.abs(x2 - x1) * 0.5);
                    const dy = (y1 === y2) ? 0.01 : 0;
                    return `M ${x1} ${y1 + dy} C ${x1 + dx} ${y1 + dy}, ${x2 - dx} ${y2}, ${x2} ${y2}`;
                },

                deleteConnection(conn) {
                    this.$wire.removeConnection(conn.from, conn.to, conn.fromPort);
                },

                async simulateFlow() {
                    if (this.isSimulating) return;
                    this.isSimulating = true;
                    this.simStates = {};
                    this.activePackets = [];

                    try {
                        const triggerNode = this.nodes.find(n => n.type === 'TRIGGER');
                        if (!triggerNode) {
                            alert("Trigger node not found in workspace!");
                            this.isSimulating = false;
                            return;
                        }

                        this.setNodeSimState(triggerNode.id, 'active');
                        await this.delay(1200);
                        this.setNodeSimState(triggerNode.id, 'completed');

                        let currentConn = this.connections.find(c => c.from === triggerNode.id);
                        let nextNodeId = currentConn ? currentConn.to : null;

                        while (nextNodeId) {
                            const nextNode = this.nodes.find(n => n.id === nextNodeId);
                            if (!nextNode) break;

                            const path = this.getBezierPath(currentConn);
                            const packetId = 'p-' + Date.now() + '-' + Math.random().toString(36).substr(2, 5);
                            const dur = 1000;
                            
                            this.activePackets.push({
                                id: packetId,
                                path: path,
                                color: '#6366f1',
                                dur: dur
                            });

                            await this.delay(dur);
                            this.activePackets = this.activePackets.filter(p => p.id !== packetId);

                            this.setNodeSimState(nextNode.id, 'active');

                            if (nextNode.type === 'DELAY') {
                                this.setNodeSimState(nextNode.id, 'waiting');
                                await this.delay(1800);
                            } else if (nextNode.type === 'CONDITION') {
                                await this.delay(1200);
                                const outcome = Math.random() > 0.4 ? 'true' : 'false';
                                this.setNodeSimState(nextNode.id, 'completed');

                                const branchConn = this.connections.find(c => c.from === nextNode.id && c.fromPort === outcome);
                                if (branchConn) {
                                    const branchPath = this.getBezierPath(branchConn);
                                    const branchPacketId = 'p-' + Date.now() + '-' + Math.random().toString(36).substr(2, 5);
                                    const branchDur = 1000;

                                    this.activePackets.push({
                                        id: branchPacketId,
                                        path: branchPath,
                                        color: outcome === 'true' ? '#10b981' : '#ef4444',
                                        dur: branchDur
                                    });

                                    await this.delay(branchDur);
                                    this.activePackets = this.activePackets.filter(p => p.id !== branchPacketId);

                                    currentConn = branchConn;
                                    nextNodeId = branchConn.to;
                                    continue;
                                } else {
                                    nextNodeId = null;
                                    break;
                                }
                            } else {
                                await this.delay(1200);
                            }

                            this.setNodeSimState(nextNode.id, 'completed');

                            currentConn = this.connections.find(c => c.from === nextNode.id);
                            nextNodeId = currentConn ? currentConn.to : null;
                        }

                        await this.delay(1000);
                        this.isSimulating = false;
                        
                        setTimeout(() => {
                            if (!this.isSimulating) {
                                this.simStates = {};
                            }
                        }, 5000);

                    } catch (err) {
                        console.error("Simulation failed:", err);
                        this.isSimulating = false;
                    }
                },

                setNodeSimState(nodeId, state) {
                    this.simStates = Object.assign({}, this.simStates, { [nodeId]: state });
                },

                delay(ms) {
                    return new Promise(resolve => setTimeout(resolve, ms));
                }
            });

            if (window.Alpine) {
                window.Alpine.data('flowChart', flowChartData);
            } else {
                document.addEventListener('alpine:init', () => {
                    Alpine.data('flowChart', flowChartData);
                });
            }
        })();
    </script>
</div>
