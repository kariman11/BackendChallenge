<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\OrganizationInvitation;
use App\Models\OrganizationUserRole;
use App\Models\Role;
use App\Models\User;
use App\Services\WebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class OrganizationController extends Controller
{
    /**
     * Create an organization + assign owner role
     */
    public function create(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $user = Auth::user();
        // Create organization
        $org = Organization::create([
            'name' => $request->name,
            'owner_id' => $user->id,
        ]);

        // Assign role "owner"
        $ownerRole = Role::where('name', 'owner')->first();

        OrganizationUserRole::create([
            'organization_id' => $org->id,
            'user_id'         => $user->id,
            'role_id'         => $ownerRole->id,
        ]);
        WebhookService::send(
            $org->id,
            'organization.created',
            [
                'org_id' => $org->id,
                'name' => $org->name,
                'owner_id' => $user->id,
            ]
        );

        return response()->json([
            'status' => true,
            'organization' => $org,
            'message' => 'Organization created successfully.',
        ], 201);
    }

    /**
     * List organizations belonging to logged-in user
     */
    public function index()
    {
        $user = Auth::user();

        $orgs = $user->organizations()->with('owner')->get();

        return response()->json([
            'status' => true,
            'organizations' => $orgs,
        ]);
    }

    /**
     * Add user to organization manually (owner/admin only)
     */
    public function addMemberOld(Request $request, Organization $org)
    {
        $request->validate([
            'email' => 'required|email',
            'role'  => 'required|string|in:admin,member,auditor'
        ]);

        $actingUser = Auth::user();

        // Must have permission
        if (! $actingUser->hasPermission($org->id, 'users.invite')) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized. Missing permission: users.invite'
            ], 403);
        }

        // Find or create user
        $user = User::firstOrCreate(
            ['email' => $request->email],
            ['name' => 'New Member', 'password' => bcrypt('password123')]
        );

        $role = Role::where('name', $request->role)->first();

        // Assign role to user
        OrganizationUserRole::updateOrCreate(
            ['organization_id' => $org->id, 'user_id' => $user->id],
            ['role_id' => $role->id]
        );

        WebhookService::send(
            $org->id,
            'organization.member_invited',
            [
                'org_id'       => $org->id,
                'inviter_id'   => $actingUser->id,
                'invited_email'=> $request->email,
                'role'         => $request->role,
            ]
        );


        return response()->json([
            'status' => true,
            'message' => 'Member added successfully.',
            'user' => $user,
        ]);
    }


    public function addMember(Request $request, Organization $org)
    {
        $request->validate([
            'email' => 'required|email',
            'role'  => 'required|string|in:admin,member,auditor'
        ]);

        $acting = $request->user();

        if (! $acting->hasPermission($org->id, 'users.invite')) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        // Create invitation
        $inv = OrganizationInvitation::create([
            'organization_id' => $org->id,
            'email' => $request->email,
            'role'  => $request->role,
            'token' => Str::uuid(),
        ]);

        // Build accept URL
        $acceptUrl = url("/api/orgs/{$org->id}/invites/accept/{$inv->token}");

        // Send email (Mailpit)
        \Mail::raw("You've been invited to join {$org->name}. Accept: $acceptUrl", function ($m) use ($request) {
            $m->to($request->email)->subject('Organization Invitation');
        });

        // Fire webhook
        WebhookService::send($org->id, 'organization.member_invited', [
            'org_id'   => $org->id,
            'inviter'  => $acting->id,
            'email'    => $request->email,
            'role'     => $request->role,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Invitation sent.',
            'accept_url' => $acceptUrl
        ]);
    }

    public function acceptInvite(Request $request, Organization $org, $token)
    {
        $inv = OrganizationInvitation::where('organization_id', $org->id)
            ->where('token', $token)
            ->first();

        if (! $inv) {
            return response()->json(['message' => 'Invalid invitation'], 404);
        }

        if ($inv->accepted_at) {
            return response()->json(['message' => 'Invitation already accepted'], 409);
        }

        // User must be logged in
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Authentication required'], 401);
        }

        // User email must match the invitation
        if ($user->email !== $inv->email) {
            return response()->json(['message' => 'Email mismatch'], 403);
        }

        // Assign role
        $role = Role::where('name', $inv->role)->first();

        OrganizationUserRole::updateOrCreate(
            ['organization_id' => $org->id, 'user_id' => $user->id],
            ['role_id' => $role->id]
        );

        // Mark invitation accepted
        $inv->update(['accepted_at' => now()]);

        // Fire webhook
        WebhookService::send($org->id, 'organization.member_joined', [
            'org_id' => $org->id,
            'user_id' => $user->id,
            'joined_at' => now()->toISOString(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Invitation accepted. You have joined the organization.'
        ]);
    }

}
