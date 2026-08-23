<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\WorkflowAutomation;
use App\Models\Business;
use Illuminate\Support\Facades\Auth;

class WorkflowAutomationBuilder extends Component
{
    public $workflows = [];
    public $activeWorkflowId = null;
    public $workflowTitle = '';
    public $workflowStatus = 'ACTIVE';

    public $nodes = [];
    public $connections = [];
    
    // Inspector States
    public $selectedNodeIndex = null;
    public $selectedNodeId = null;
    public $inspectorTitle = '';
    public $inspectorSubtitle = '';
    public $inspectorKeyword = '';
    public $inspectorTemplate = '';
    public $inspectorDelay = '';
    public $inspectorCondition = '';

    // New Automation Modal State
    public $showCreateModal = false;
    public $newWorkflowTitle = '';
    public $newWorkflowTrigger = 'keyword';

    public $statusMessage = null;
    public $statusType = 'info';

    public function mount()
    {
        $this->ensureSeedWorkflows();
        $this->loadWorkflows();
    }

    public function getBusinessId()
    {
        $user = Auth::user();
        if ($user && $user->business_id) {
            return $user->business_id;
        }
        $b = Business::first();
        return $b ? $b->id : null;
    }

    public function ensureSeedWorkflows()
    {
        $bId = $this->getBusinessId();
        if (!$bId) return;

        $count = WorkflowAutomation::where('business_id', $bId)->count();
        if ($count === 0) {
            // Seed 3 starter workflows for this business
            WorkflowAutomation::create([
                'business_id' => $bId,
                'title' => 'Instant Lead Welcome & Quotation Flow',
                'trigger_type' => 'keyword',
                'trigger_summary' => 'Matches Keywords: "QUOTE", "PRICE"',
                'status' => 'ACTIVE',
                'executed_count' => 342,
                'conversion_rate' => '42.8%',
                'nodes' => [
                    [
                        'id' => 'node_1',
                        'type' => 'TRIGGER',
                        'title' => 'Trigger: Incoming WhatsApp Message',
                        'subtitle' => 'Matches Keywords: "PRICE", "QUOTE", "DEMO"',
                        'icon' => '⚡',
                        'color' => 'bg-emerald-500 text-white',
                        'borderColor' => 'border-emerald-600',
                        'config' => ['keyword' => 'QUOTE, PRICE'],
                        'x' => 80,
                        'y' => 220
                    ],
                    [
                        'id' => 'node_2',
                        'type' => 'ACTION',
                        'title' => 'Action: Send WhatsApp Meta Template',
                        'subtitle' => 'Template: lead_welcome_offer',
                        'icon' => '💬',
                        'color' => 'bg-blue-500 text-white',
                        'borderColor' => 'border-blue-600',
                        'config' => ['template' => 'lead_welcome_offer'],
                        'x' => 400,
                        'y' => 220
                    ],
                    [
                        'id' => 'node_3',
                        'type' => 'DELAY',
                        'title' => 'Delay: Wait 15 Minutes',
                        'subtitle' => 'Wait for 15 minutes',
                        'icon' => '⏱️',
                        'color' => 'bg-amber-500 text-white',
                        'borderColor' => 'border-amber-600',
                        'config' => ['duration' => '15m'],
                        'x' => 720,
                        'y' => 220
                    ],
                    [
                        'id' => 'node_4',
                        'type' => 'CONDITION',
                        'title' => 'Condition: Customer Clicked CTA Button?',
                        'subtitle' => 'Check if CTA button clicked',
                        'icon' => '🔀',
                        'color' => 'bg-purple-500 text-white',
                        'borderColor' => 'border-purple-600',
                        'config' => ['condition' => 'clicked_cta'],
                        'x' => 1040,
                        'y' => 220
                    ],
                    [
                        'id' => 'node_5',
                        'type' => 'ACTION',
                        'title' => 'Action: Update Pipeline Stage',
                        'subtitle' => 'Move Lead to Stage: "Qualified Proposal"',
                        'icon' => '🎯',
                        'color' => 'bg-indigo-500 text-white',
                        'borderColor' => 'border-indigo-600',
                        'config' => ['stage_id' => 3],
                        'x' => 1380,
                        'y' => 110
                    ],
                    [
                        'id' => 'node_6',
                        'type' => 'ACTION',
                        'title' => 'Action: Log Failed Response',
                        'subtitle' => 'Log to CRM activities',
                        'icon' => '📝',
                        'color' => 'bg-slate-500 text-white',
                        'borderColor' => 'border-slate-600',
                        'config' => ['log_message' => 'User did not click CTA'],
                        'x' => 1380,
                        'y' => 330
                    ]
                ],
                'connections' => [
                    ['from' => 'node_1', 'to' => 'node_2', 'fromPort' => 'output'],
                    ['from' => 'node_2', 'to' => 'node_3', 'fromPort' => 'output'],
                    ['from' => 'node_3', 'to' => 'node_4', 'fromPort' => 'output'],
                    ['from' => 'node_4', 'to' => 'node_5', 'fromPort' => 'true'],
                    ['from' => 'node_4', 'to' => 'node_6', 'fromPort' => 'false'],
                ]
            ]);

            WorkflowAutomation::create([
                'business_id' => $bId,
                'title' => 'Abandoned Inquiry 24h Re-engagement',
                'trigger_type' => 'after_hours',
                'trigger_summary' => 'No Reply for 24 Hours',
                'status' => 'ACTIVE',
                'executed_count' => 189,
                'conversion_rate' => '28.1%',
                'nodes' => [
                    [
                        'id' => 'node_1',
                        'type' => 'TRIGGER',
                        'title' => 'Trigger: Idle 24h Inactive Chat',
                        'subtitle' => 'Fires after 24 hrs silence',
                        'icon' => '⏱️',
                        'color' => 'bg-amber-500 text-white',
                        'borderColor' => 'border-amber-600',
                        'config' => ['duration' => '24h'],
                        'x' => 100,
                        'y' => 200
                    ],
                    [
                        'id' => 'node_2',
                        'type' => 'ACTION',
                        'title' => 'Action: AI Nudge Message',
                        'subtitle' => 'Generate time-aware follow up',
                        'icon' => '✨',
                        'color' => 'bg-indigo-500 text-white',
                        'borderColor' => 'border-indigo-600',
                        'config' => ['template' => 'nudge_auto'],
                        'x' => 450,
                        'y' => 200
                    ]
                ],
                'connections' => [
                    ['from' => 'node_1', 'to' => 'node_2', 'fromPort' => 'output']
                ]
            ]);

            WorkflowAutomation::create([
                'business_id' => $bId,
                'title' => 'After-Hours Auto Responder',
                'trigger_type' => 'after_hours',
                'trigger_summary' => 'Outside Business Hours (9am-6pm)',
                'status' => 'PAUSED',
                'executed_count' => 94,
                'conversion_rate' => '15.4%',
                'nodes' => [
                    [
                        'id' => 'node_1',
                        'type' => 'TRIGGER',
                        'title' => 'Trigger: Incoming Chat (After Hours)',
                        'subtitle' => 'Fires outside 9:00 AM - 6:00 PM',
                        'icon' => '🌙',
                        'color' => 'bg-purple-500 text-white',
                        'borderColor' => 'border-purple-600',
                        'config' => ['hours' => 'off_hours'],
                        'x' => 100,
                        'y' => 200
                    ],
                    [
                        'id' => 'node_2',
                        'type' => 'ACTION',
                        'title' => 'Action: Send Out-of-Office Reply',
                        'subtitle' => 'Auto-reply with expected contact time',
                        'icon' => '💬',
                        'color' => 'bg-blue-500 text-white',
                        'borderColor' => 'border-blue-600',
                        'config' => ['template' => 'after_hours_notice'],
                        'x' => 450,
                        'y' => 200
                    ]
                ],
                'connections' => [
                    ['from' => 'node_1', 'to' => 'node_2', 'fromPort' => 'output']
                ]
            ]);
        }
    }

    public function loadWorkflows()
    {
        $bId = $this->getBusinessId();
        $this->workflows = WorkflowAutomation::where('business_id', $bId)->latest()->get();

        if (count($this->workflows) > 0) {
            // Select active or first
            if (!$this->activeWorkflowId || !$this->workflows->contains('id', $this->activeWorkflowId)) {
                $this->selectWorkflow($this->workflows->first()->id);
            } else {
                $this->selectWorkflow($this->activeWorkflowId);
            }
        } else {
            $this->nodes = [];
            $this->connections = [];
            $this->activeWorkflowId = null;
            $this->workflowTitle = '';
        }
    }

    public function selectWorkflow($id)
    {
        $wf = WorkflowAutomation::find($id);
        if (!$wf) return;

        $this->activeWorkflowId = $wf->id;
        $this->workflowTitle = $wf->title;
        $this->workflowStatus = $wf->status;
        $this->nodes = $wf->nodes ?? [];
        $this->connections = $wf->connections ?? [];
        
        $this->selectedNodeIndex = null;
        $this->selectedNodeId = null;

        if (count($this->nodes) > 0) {
            $this->selectNode(0);
        }
    }

    public function createNewWorkflow()
    {
        $this->validate([
            'newWorkflowTitle' => 'required|string|max:100',
        ]);

        $bId = $this->getBusinessId();

        $triggerSummary = 'Custom Trigger';
        if ($this->newWorkflowTrigger === 'keyword') {
            $triggerSummary = 'Keyword Match: "HELLO", "HELP"';
        } elseif ($this->newWorkflowTrigger === 'stage_change') {
            $triggerSummary = 'Pipeline Stage Changed';
        } elseif ($this->newWorkflowTrigger === 'after_hours') {
            $triggerSummary = 'Outside Business Hours';
        } elseif ($this->newWorkflowTrigger === 'lead_created') {
            $triggerSummary = 'New Contact Created';
        }

        $defaultNodes = [
            [
                'id' => 'node_1',
                'type' => 'TRIGGER',
                'title' => 'Trigger: ' . ucfirst($this->newWorkflowTrigger) . ' Event',
                'subtitle' => $triggerSummary,
                'icon' => '⚡',
                'color' => 'bg-emerald-500 text-white',
                'borderColor' => 'border-emerald-600',
                'config' => ['keyword' => 'HELP'],
                'x' => 100,
                'y' => 200
            ],
            [
                'id' => 'node_2',
                'type' => 'ACTION',
                'title' => 'Action: Send WhatsApp Message',
                'subtitle' => 'Auto Response',
                'icon' => '💬',
                'color' => 'bg-blue-500 text-white',
                'borderColor' => 'border-blue-600',
                'config' => ['template' => 'lead_welcome_offer'],
                'x' => 450,
                'y' => 200
            ]
        ];

        $defaultConnections = [
            ['from' => 'node_1', 'to' => 'node_2', 'fromPort' => 'output']
        ];

        $wf = WorkflowAutomation::create([
            'business_id' => $bId,
            'title' => $this->newWorkflowTitle,
            'trigger_type' => $this->newWorkflowTrigger,
            'trigger_summary' => $triggerSummary,
            'status' => 'ACTIVE',
            'nodes' => $defaultNodes,
            'connections' => $defaultConnections,
        ]);

        $this->showCreateModal = false;
        $this->newWorkflowTitle = '';
        $this->loadWorkflows();
        $this->selectWorkflow($wf->id);

        $this->statusMessage = "Created new automation workflow '{$wf->title}'!";
        $this->statusType = 'success';
    }

    public function toggleWorkflowStatus($id)
    {
        $wf = WorkflowAutomation::find($id);
        if ($wf) {
            $wf->status = ($wf->status === 'ACTIVE') ? 'PAUSED' : 'ACTIVE';
            $wf->save();

            if ($this->activeWorkflowId === $id) {
                $this->workflowStatus = $wf->status;
            }

            $this->loadWorkflows();
            $this->statusMessage = "Workflow '{$wf->title}' status updated to {$wf->status}!";
            $this->statusType = 'info';
        }
    }

    public function deleteWorkflow($id)
    {
        $wf = WorkflowAutomation::find($id);
        if ($wf) {
            $title = $wf->title;
            $wf->delete();

            $this->loadWorkflows();
            $this->statusMessage = "Automation workflow '{$title}' deleted.";
            $this->statusType = 'info';
        }
    }

    public function saveWorkflow()
    {
        if (!$this->activeWorkflowId) return;

        $wf = WorkflowAutomation::find($this->activeWorkflowId);
        if ($wf) {
            $wf->title = $this->workflowTitle;
            $wf->status = $this->workflowStatus;
            $wf->nodes = $this->nodes;
            $wf->connections = $this->connections;
            $wf->save();

            $this->loadWorkflows();
            $this->statusMessage = "Automation workflow '{$this->workflowTitle}' saved and published live!";
            $this->statusType = 'success';
        }
    }

    public function selectNode($index)
    {
        $this->selectedNodeIndex = $index;
        if (isset($this->nodes[$index])) {
            $node = $this->nodes[$index];
            $this->selectedNodeId = $node['id'];
            $this->inspectorTitle = $node['title'];
            $this->inspectorSubtitle = $node['subtitle'];
            $this->inspectorKeyword = $node['config']['keyword'] ?? '';
            $this->inspectorTemplate = $node['config']['template'] ?? '';
            $this->inspectorDelay = $node['config']['duration'] ?? '';
            $this->inspectorCondition = $node['config']['condition'] ?? '';
        }
    }

    public function saveNodeChanges()
    {
        if ($this->selectedNodeIndex !== null && isset($this->nodes[$this->selectedNodeIndex])) {
            $this->nodes[$this->selectedNodeIndex]['title'] = $this->inspectorTitle;
            $this->nodes[$this->selectedNodeIndex]['subtitle'] = $this->inspectorSubtitle;
            $this->nodes[$this->selectedNodeIndex]['config']['keyword'] = $this->inspectorKeyword;
            $this->nodes[$this->selectedNodeIndex]['config']['template'] = $this->inspectorTemplate;
            $this->nodes[$this->selectedNodeIndex]['config']['duration'] = $this->inspectorDelay;
            $this->nodes[$this->selectedNodeIndex]['config']['condition'] = $this->inspectorCondition;

            // Formatted subtitles
            if ($this->nodes[$this->selectedNodeIndex]['type'] === 'TRIGGER') {
                $this->nodes[$this->selectedNodeIndex]['subtitle'] = 'Matches Keywords: "' . $this->inspectorKeyword . '"';
            } elseif ($this->nodes[$this->selectedNodeIndex]['type'] === 'ACTION') {
                $this->nodes[$this->selectedNodeIndex]['subtitle'] = 'Template: ' . $this->inspectorTemplate;
            } elseif ($this->nodes[$this->selectedNodeIndex]['type'] === 'DELAY') {
                $this->nodes[$this->selectedNodeIndex]['subtitle'] = 'Wait for ' . $this->inspectorDelay;
            } elseif ($this->nodes[$this->selectedNodeIndex]['type'] === 'CONDITION') {
                $this->nodes[$this->selectedNodeIndex]['subtitle'] = 'Check if ' . str_replace('_', ' ', $this->inspectorCondition);
            }

            $this->statusMessage = "Step details updated successfully!";
            $this->statusType = 'success';
            $this->nodes = $this->nodes;
        }
    }

    public function addNode($type)
    {
        $id = 'node_' . (count($this->nodes) + 1);
        $x = 250 + (count($this->nodes) * 50) % 400;
        $y = 180 + (count($this->nodes) * 40) % 300;

        if ($type === 'TRIGGER') {
            $newNode = [
                'id' => $id,
                'type' => 'TRIGGER',
                'title' => 'Trigger: New Lead Created',
                'subtitle' => 'Fires when new contact enters CRM',
                'icon' => '⚡',
                'color' => 'bg-emerald-500 text-white',
                'borderColor' => 'border-emerald-600',
                'config' => ['keyword' => 'WELCOME'],
                'x' => $x,
                'y' => $y
            ];
        } elseif ($type === 'ACTION') {
            $newNode = [
                'id' => $id,
                'type' => 'ACTION',
                'title' => 'Action: Send WhatsApp Template',
                'subtitle' => 'Template: lead_welcome_offer',
                'icon' => '💬',
                'color' => 'bg-blue-500 text-white',
                'borderColor' => 'border-blue-600',
                'config' => ['template' => 'lead_welcome_offer'],
                'x' => $x,
                'y' => $y
            ];
        } elseif ($type === 'DELAY') {
            $newNode = [
                'id' => $id,
                'type' => 'DELAY',
                'title' => 'Delay: Wait 1 Hour',
                'subtitle' => 'Wait for 1 hour',
                'icon' => '⏱️',
                'color' => 'bg-amber-500 text-white',
                'borderColor' => 'border-amber-600',
                'config' => ['duration' => '1h'],
                'x' => $x,
                'y' => $y
            ];
        } elseif ($type === 'CONDITION') {
            $newNode = [
                'id' => $id,
                'type' => 'CONDITION',
                'title' => 'Condition: Message Read Status',
                'subtitle' => 'Check if message read',
                'icon' => '🔀',
                'color' => 'bg-purple-500 text-white',
                'borderColor' => 'border-purple-600',
                'config' => ['condition' => 'read_status'],
                'x' => $x,
                'y' => $y
            ];
        }

        $this->nodes[] = $newNode;
        $this->selectNode(count($this->nodes) - 1);
        $this->statusMessage = "Added new step to workflow canvas!";
        $this->statusType = 'success';
    }

    public function removeNode($index)
    {
        if (isset($this->nodes[$index])) {
            $nodeId = $this->nodes[$index]['id'];

            $this->connections = array_values(array_filter($this->connections, function($conn) use ($nodeId) {
                return $conn['from'] !== $nodeId && $conn['to'] !== $nodeId;
            }));

            unset($this->nodes[$index]);
            $this->nodes = array_values($this->nodes);
        }
        
        if (count($this->nodes) > 0) {
            $this->selectNode(0);
        } else {
            $this->selectedNodeIndex = null;
            $this->selectedNodeId = null;
        }

        $this->statusMessage = "Removed step and its connections from flow canvas.";
        $this->statusType = 'info';
    }

    public function addConnection($from, $to, $fromPort)
    {
        foreach ($this->connections as $conn) {
            if ($conn['from'] === $from && $conn['to'] === $to && $conn['fromPort'] === $fromPort) {
                return;
            }
        }
        
        $this->connections = array_values(array_filter($this->connections, function($conn) use ($to) {
            return $conn['to'] !== $to;
        }));

        $this->connections[] = [
            'from' => $from,
            'to' => $to,
            'fromPort' => $fromPort
        ];

        $this->statusMessage = "Connected nodes successfully!";
        $this->statusType = 'success';
    }

    public function removeConnection($from, $to, $fromPort)
    {
        $this->connections = array_values(array_filter($this->connections, function($conn) use ($from, $to, $fromPort) {
            return !($conn['from'] === $from && $conn['to'] === $to && $conn['fromPort'] === $fromPort);
        }));

        $this->statusMessage = "Removed connection.";
        $this->statusType = 'info';
    }

    public function render()
    {
        return view('livewire.workflow-automation-builder');
    }
}
