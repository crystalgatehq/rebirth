<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ContactGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContactGroupShareController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function updateSharing(ContactGroup $contactGroup, Request $request)
    {
        $this->authorize('update', $contactGroup);

        $validated = Validator::make($request->all(), [
            'visibility' => ['required', 'in:PRIVATE,TEAM,ORGANIZATION'],
            'teams' => ['array', 'required_if:visibility,TEAM', 'exists:teams,id'],
            'teams.*' => ['integer'],
            'users' => ['array', 'required_if:visibility,PRIVATE'],
            'users.*' => ['exists:users,id'],
        ])->validate();

        $data = [
            'visibility' => $validated['visibility'],
        ];

        // Only update shared_with_teams if visibility is TEAM
        if ($validated['visibility'] === 'TEAM') {
            $data['shared_with_teams'] = $validated['teams'] ?? [];
            $data['shared_with_users'] = [];
        } 
        // Only update shared_with_users if visibility is PRIVATE
        elseif ($validated['visibility'] === 'PRIVATE') {
            $data['shared_with_users'] = $validated['users'] ?? [];
            $data['shared_with_teams'] = [];
        } 
        // For ORGANIZATION, clear all sharing
        else {
            $data['shared_with_users'] = [];
            $data['shared_with_teams'] = [];
        }

        $contactGroup->update($data);

        return response()->json([
            'message' => 'Sharing settings updated successfully',
            'data' => $contactGroup->fresh()
        ]);
    }

    public function getSharedGroups()
    {
        $user = auth()->user();
        
        // Get groups shared directly with user
        $directShared = ContactGroup::whereJsonContains('shared_with_users', $user->id)
            ->where('user_id', '!=', $user->id)
            ->with('user');

        // Get groups shared with user's teams
        $teamIds = $user->teams()->pluck('teams.id');
        $teamShared = ContactGroup::where(function($query) use ($teamIds) {
            foreach ($teamIds as $teamId) {
                $query->orWhereJsonContains('shared_with_teams', $teamId);
            }
        })->where('user_id', '!=', $user->id)
        ->with('user');

        // Combine and paginate results
        $sharedGroups = $directShared->union($teamShared)
            ->orderBy('updated_at', 'desc')
            ->paginate();

        return response()->json($sharedGroups);
    }

    public function getSharingInfo(ContactGroup $contactGroup)
    {
        $this->authorize('view', $contactGroup);

        $sharedWithUsers = [];
        if (!empty($contactGroup->shared_with_users)) {
            $sharedWithUsers = \App\Models\User::whereIn('id', $contactGroup->shared_with_users)
                ->select('id', 'name', 'email')
                ->get();
        }

        $sharedWithTeams = [];
        if (!empty($contactGroup->shared_with_teams)) {
            $sharedWithTeams = \App\Models\Team::whereIn('id', $contactGroup->shared_with_teams)
                ->select('id', 'name', 'description')
                ->get();
        }

        return response()->json([
            'visibility' => $contactGroup->visibility,
            'shared_with_users' => $sharedWithUsers,
            'shared_with_teams' => $sharedWithTeams,
            'owner' => $contactGroup->user->only(['id', 'name', 'email'])
        ]);
    }
}
