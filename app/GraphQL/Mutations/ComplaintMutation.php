<?php

namespace App\GraphQL\Mutations;

use App\Models\Complaint;
use App\Models\AdminResponse;
use Illuminate\Support\Facades\Auth;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class ComplaintMutation
{
    /**
     * Respond to a complaint (admin only).
     */
    public function respond($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): AdminResponse
    {
        $admin = Auth::user();
        $complaint = Complaint::findOrFail($args['complaint_id']);

        // Check if user is admin
        if (!$admin->isAdmin()) {
            throw new \Exception('Unauthorized. Admin access required.');
        }

        // Create admin response
        $response = AdminResponse::create([
            'complaint_id' => $complaint->id,
            'admin_id' => $admin->id,
            'response' => $args['response'],
        ]);

        // Update complaint status to responded
        $complaint->update(['status' => 'responded']);

        return $response->load(['complaint', 'admin']);
    }
}
