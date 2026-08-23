<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Contact;
use App\Models\Business;
use App\Models\Lead;
use App\Models\LeadStage;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ContactBook extends Component
{
    use WithFileUploads;
    use WithPagination;

    // Search & Filters
    public $search = '';
    public $filterStatus = '';
    public $filterTag = '';
    public $filterSource = '';
    public $sortBy = 'created_at';
    public $sortDir = 'desc';
    public $viewMode = 'list'; // list | grid

    // Selected Contact (detail panel)
    public $selectedContactId = null;
    public $selectedContact = null;
    public $selectedLead = null;

    // Edit form fields
    public $form = [];
    public $isEditing = false;
    public $isCreating = false;

    // New tag input
    public $newTag = '';

    // Import / Export
    public $importFile = null;
    public bool $showImportModal = false;
    public string $importResult = '';

    // Status message
    public $statusMsg = '';
    public $statusType = 'success';

    protected $queryString = [
        'search'       => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'filterTag'    => ['except' => ''],
        'sortBy'       => ['except' => 'created_at'],
        'sortDir'      => ['except' => 'desc'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function selectContact($id)
    {
        $this->selectedContactId = $id;
        $this->selectedContact = Contact::with([
            'leads.stage',
            'leads.activities' => fn($q) => $q->latest()->limit(8),
            'conversations'    => fn($q) => $q->latest()->limit(1),
        ])->find($id);

        $this->selectedLead = $this->selectedContact?->leads()->with('stage')->latest()->first();
        $this->isEditing = false;
        $this->isCreating = false;
        $this->newTag = '';
    }

    public function startEdit()
    {
        if (!$this->selectedContact) return;
        $c = $this->selectedContact;
        $this->form = [
            'name'             => $c->name,
            'phone'            => $c->phone,
            'email'            => $c->email,
            'whatsapp_number'  => $c->whatsapp_number,
            'company'          => $c->company,
            'designation'      => $c->designation,
            'birthday'         => $c->birthday?->format('Y-m-d'),
            'city'             => $c->city,
            'state'            => $c->state,
            'country'          => $c->country,
            'address'          => $c->address,
            'website'          => $c->website,
            'linkedin_url'     => $c->linkedin_url,
            'instagram_handle' => $c->instagram_handle,
            'notes'            => $c->notes,
            'status'           => $c->status ?? 'active',
            'do_not_disturb'   => $c->do_not_disturb ?? false,
        ];
        $this->isEditing = true;
    }

    public function startCreate()
    {
        $this->selectedContactId = null;
        $this->selectedContact = null;
        $this->selectedLead = null;
        $this->form = [
            'name'             => '',
            'phone'            => '',
            'email'            => '',
            'whatsapp_number'  => '',
            'company'          => '',
            'designation'      => '',
            'birthday'         => '',
            'city'             => '',
            'state'            => '',
            'country'          => '',
            'address'          => '',
            'website'          => '',
            'linkedin_url'     => '',
            'instagram_handle' => '',
            'notes'            => '',
            'status'           => 'active',
            'do_not_disturb'   => false,
        ];
        $this->isEditing = false;
        $this->isCreating = true;
    }

    public function saveContact()
    {
        $this->validate([
            'form.phone' => 'required|string',
            'form.name'  => 'nullable|string|max:255',
            'form.email' => 'nullable|email',
        ], [
            'form.phone.required' => 'Phone number is required.',
        ]);

        try {
            $business = auth()->user()->business;

            $formData = $this->form;
            if (isset($formData['birthday']) && empty(trim($formData['birthday']))) {
                $formData['birthday'] = null;
            }

            if ($this->isCreating) {
                $contact = Contact::create(array_merge(
                    $formData,
                    ['business_id' => $business->id]
                ));
                $this->isCreating = false;
                $this->statusMsg = 'Contact created successfully!';
                $this->selectContact($contact->id);
            } elseif ($this->isEditing && $this->selectedContact) {
                $this->selectedContact->update($formData);
                $this->isEditing = false;
                $this->statusMsg = 'Contact saved successfully!';
                $this->selectContact($this->selectedContactId);
            }

            $this->statusType = 'success';
        } catch (\Exception $e) {
            $this->statusMsg = 'Failed to save contact: ' . $e->getMessage();
            $this->statusType = 'error';
        }
    }

    public function cancelEdit()
    {
        $this->isEditing = false;
        $this->isCreating = false;
    }

    public function deleteContact()
    {
        if (!$this->selectedContact) return;
        $this->selectedContact->delete();
        $this->selectedContact = null;
        $this->selectedContactId = null;
        $this->selectedLead = null;
        $this->statusMsg = 'Contact deleted.';
        $this->statusType = 'info';
    }

    public function addTag()
    {
        if (!$this->selectedContact || empty(trim($this->newTag))) return;
        $tags = $this->selectedContact->tags_array;
        $tag = trim($this->newTag);
        if (!in_array($tag, $tags)) {
            $tags[] = $tag;
            $this->selectedContact->update(['tags' => implode(',', $tags)]);
        }
        $this->newTag = '';
        $this->selectContact($this->selectedContactId);
    }

    public function removeTag($tag)
    {
        if (!$this->selectedContact) return;
        $tags = array_filter($this->selectedContact->tags_array, fn($t) => $t !== $tag);
        $this->selectedContact->update(['tags' => implode(',', $tags)]);
        $this->selectContact($this->selectedContactId);
    }

    public function toggleDnd()
    {
        if (!$this->selectedContact) return;
        $this->selectedContact->update(['do_not_disturb' => !$this->selectedContact->do_not_disturb]);
        $this->selectContact($this->selectedContactId);
    }

    public function startChatWithContact($contactId = null)
    {
        $id = $contactId ?: $this->selectedContactId;
        if (!$id) return;

        $businessId = auth()->user()->business_id;
        $conversation = \App\Models\Conversation::firstOrCreate(
            ['business_id' => $businessId, 'contact_id' => $id],
            [
                'status' => 'open',
                'unread_count' => 0,
            ]
        );

        return redirect()->to('/inbox?conversation_id=' . $conversation->id);
    }

    public function sortContacts($column)
    {
        if ($this->sortBy === $column) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDir = 'asc';
        }
    }

    // ── Export ──────────────────────────────────────────────────────────
    public function exportLeads(): StreamedResponse
    {
        $business = auth()->user()->business;

        $leads = Lead::with('contact')
            ->where('business_id', $business->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="leads_export_' . now()->format('Y-m-d_His') . '.csv"',
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
        ];

        $columns = [
            'Name', 'Phone', 'Email', 'Company', 'Designation',
            'City', 'State', 'Country', 'Website',
            'Source', 'Lead Score', 'Expected Value', 'Stage',
            'UTM Source', 'UTM Medium', 'UTM Campaign',
            'Notes', 'Tags', 'Status', 'Created At',
        ];

        $callback = function () use ($leads, $columns) {
            $file = fopen('php://output', 'w');
            // UTF-8 BOM for Excel compatibility
            fputs($file, "\xEF\xBB\xBF");
            fputcsv($file, $columns);

            foreach ($leads as $lead) {
                fputcsv($file, [
                    $lead->contact->name          ?? '',
                    $lead->contact->phone         ?? '',
                    $lead->contact->email         ?? '',
                    $lead->contact->company       ?? '',
                    $lead->contact->designation   ?? '',
                    $lead->contact->city          ?? '',
                    $lead->contact->state         ?? '',
                    $lead->contact->country       ?? '',
                    $lead->contact->website       ?? '',
                    $lead->source                 ?? '',
                    $lead->lead_score             ?? 0,
                    $lead->expected_value         ?? 0,
                    $lead->stage?->name           ?? '',
                    $lead->utm_source             ?? '',
                    $lead->utm_medium             ?? '',
                    $lead->utm_campaign           ?? '',
                    $lead->notes                  ?? '',
                    $lead->contact->tags          ?? '',
                    $lead->contact->status        ?? 'active',
                    $lead->created_at?->format('Y-m-d H:i') ?? '',
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    // ── Import ──────────────────────────────────────────────────────────
    public function importLeads(): void
    {
        $this->validate([
            'importFile' => 'required|file|mimes:csv,txt|max:5120',
        ], [
            'importFile.mimes' => 'Please upload a CSV file (.csv).',
            'importFile.max'   => 'File must be under 5MB.',
        ]);

        $business   = auth()->user()->business;
        $stage      = LeadStage::where('business_id', $business->id)->orderBy('order_index')->first();
        $path       = $this->importFile->getRealPath();
        $file       = fopen($path, 'r');

        // Strip UTF-8 BOM if present
        $firstLine  = fgets($file);
        $firstLine  = ltrim($firstLine, "\xEF\xBB\xBF");
        rewind($file);
        // skip the BOM-stripped first line (header)
        fgetcsv($file); // skip header row

        $created = 0;
        $updated = 0;
        $skipped = 0;

        while (($row = fgetcsv($file)) !== false) {
            // Expected column order matches export:
            // Name, Phone, Email, Company, Designation, City, State, Country, Website,
            // Source, Lead Score, Expected Value, Stage, UTM Source, UTM Medium, UTM Campaign,
            // Notes, Tags, Status, Created At
            if (count($row) < 2) { $skipped++; continue; }

            $phone = trim($row[1] ?? '');
            if (empty($phone)) { $skipped++; continue; }

            $contact = Contact::firstOrCreate(
                ['phone' => $phone, 'business_id' => $business->id],
                [
                    'name'        => trim($row[0]  ?? '') ?: null,
                    'email'       => trim($row[2]  ?? '') ?: null,
                    'company'     => trim($row[3]  ?? '') ?: null,
                    'designation' => trim($row[4]  ?? '') ?: null,
                    'city'        => trim($row[5]  ?? '') ?: null,
                    'state'       => trim($row[6]  ?? '') ?: null,
                    'country'     => trim($row[7]  ?? '') ?: null,
                    'website'     => trim($row[8]  ?? '') ?: null,
                    'tags'        => trim($row[17] ?? '') ?: null,
                    'status'      => trim($row[18] ?? '') ?: 'active',
                ]
            );

            if ($contact->wasRecentlyCreated) {
                $created++;
            } else {
                // Update non-empty fields
                $updates = array_filter([
                    'name'        => trim($row[0]  ?? '') ?: null,
                    'email'       => trim($row[2]  ?? '') ?: null,
                    'company'     => trim($row[3]  ?? '') ?: null,
                    'designation' => trim($row[4]  ?? '') ?: null,
                    'city'        => trim($row[5]  ?? '') ?: null,
                ]);
                if ($updates) $contact->update($updates);
                $updated++;
            }

            // Upsert lead
            Lead::firstOrCreate(
                ['business_id' => $business->id, 'contact_id' => $contact->id],
                [
                    'stage_id'       => $stage?->id ?? 1,
                    'source'         => trim($row[9]  ?? '') ?: 'Import',
                    'lead_score'     => (int) ($row[10] ?? 0),
                    'expected_value' => (float) ($row[11] ?? 0),
                    'utm_source'     => trim($row[13] ?? '') ?: null,
                    'utm_medium'     => trim($row[14] ?? '') ?: null,
                    'utm_campaign'   => trim($row[15] ?? '') ?: null,
                    'notes'          => trim($row[16] ?? '') ?: null,
                ]
            );
        }

        fclose($file);

        $this->importFile      = null;
        $this->showImportModal = false;
        $this->importResult    = "Import complete: {$created} created, {$updated} updated, {$skipped} skipped.";
        $this->statusMsg       = $this->importResult;
        $this->statusType      = 'success';
        $this->resetPage();
    }

    public function render()
    {
        $business = auth()->user()->business;

        $contacts = Contact::with(['leads.stage', 'conversations'])
            ->where('business_id', $business->id)
            ->when($this->search, function ($q) {
                $s = '%' . $this->search . '%';
                $q->where(function ($q2) use ($s) {
                    $q2->where('name', 'like', $s)
                       ->orWhere('phone', 'like', $s)
                       ->orWhere('email', 'like', $s)
                       ->orWhere('company', 'like', $s)
                       ->orWhere('city', 'like', $s);
                });
            })
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterTag, fn($q) => $q->where('tags', 'like', '%' . $this->filterTag . '%'))
            ->orderBy($this->sortBy, $this->sortDir)
            ->paginate(25);

        $stats = [
            'total'    => Contact::where('business_id', $business->id)->count(),
            'active'   => Contact::where('business_id', $business->id)->where('status', 'active')->count(),
            'dnd'      => Contact::where('business_id', $business->id)->where('do_not_disturb', true)->count(),
            'no_lead'  => Contact::where('business_id', $business->id)->doesntHave('leads')->count(),
        ];

        return view('livewire.contact-book', [
            'contacts' => $contacts,
            'stats'    => $stats,
        ]);
    }
}
