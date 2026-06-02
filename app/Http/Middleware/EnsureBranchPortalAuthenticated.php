<?php

namespace App\Http\Middleware;

use App\Models\Branch;
use Closure;
use Illuminate\Http\Request;

class EnsureBranchPortalAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        $branchId = (int) $request->session()->get('branch_portal.branch_id', 0);
        $branch = Branch::where('id', '=', $branchId)
            ->where('status', '=', 1)
            ->whereNull('deleted_at')
            ->first();

        if (!$branch) {
            $request->session()->forget('branch_portal');

            return redirect()
                ->route('branch.portal.login')
                ->with('error_message', 'Please sign in with an active branch account.');
        }

        $request->attributes->set('branch_portal', $branch);

        return $next($request);
    }
}
