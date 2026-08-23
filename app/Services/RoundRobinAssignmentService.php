<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class RoundRobinAssignmentService
{
    /**
     * Automatically assign a newly created conversation to an agent in the business
     * using a workload/round-robin distribution.
     */
    public function assignConversation(Conversation $conversation): ?User
    {
        if ($conversation->assigned_user_id) {
            return $conversation->assignedUser;
        }

        $businessId = $conversation->business_id;

        // Get all active team members for this business (agents, managers, owners)
        $users = User::where('business_id', $businessId)
            ->where('role', '!=', 'super_admin')
            ->get();

        if ($users->isEmpty()) {
            return null;
        }

        // Find agent with the lowest number of assigned open conversations
        $userCounts = Conversation::where('business_id', $businessId)
            ->where('status', 'open')
            ->whereNotNull('assigned_user_id')
            ->selectRaw('assigned_user_id, COUNT(*) as open_count')
            ->groupBy('assigned_user_id')
            ->pluck('open_count', 'assigned_user_id')
            ->toArray();

        $selectedUser = $users->sortBy(function ($user) use ($userCounts) {
            return $userCounts[$user->id] ?? 0;
        })->first();

        if ($selectedUser) {
            $conversation->update(['assigned_user_id' => $selectedUser->id]);
            Log::info("Round-robin assigned conversation #{$conversation->id} to user #{$selectedUser->id} ({$selectedUser->name})");
        }

        return $selectedUser;
    }
}
