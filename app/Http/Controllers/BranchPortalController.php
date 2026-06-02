<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class BranchPortalController extends Controller
{
    public function showLogin(Request $request)
    {
        if ($request->session()->has('branch_portal.branch_id')) {
            return redirect()->route('branch.portal.employees');
        }

        return view('front.pages.branch-portal.login', [
            'title' => 'Branch Employee Login',
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string|max:255',
            'password' => 'required|string|max:255',
        ]);

        $username = trim((string) $credentials['username']);
        $rateLimitKey = Str::lower($username) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);

            return redirect()
                ->back()
                ->withInput($request->only('username'))
                ->with('error_message', 'Too many login attempts. Please try again in ' . $seconds . ' seconds.');
        }

        $branch = Branch::whereRaw('LOWER(serial_id) = ?', [Str::lower($username)])
            ->where('status', '=', 1)
            ->whereNull('deleted_at')
            ->first();

        if (!$branch || empty($branch->password) || !Hash::check($credentials['password'], $branch->password)) {
            RateLimiter::hit($rateLimitKey, 60);

            return redirect()
                ->back()
                ->withInput($request->only('username'))
                ->with('error_message', 'Invalid username or password.');
        }

        RateLimiter::clear($rateLimitKey);
        $request->session()->regenerate();
        $request->session()->put('branch_portal', [
            'branch_id' => (int) $branch->id,
            'unit_id' => (int) $branch->unit_id,
            'serial_id' => (string) $branch->serial_id,
            'branch_name' => (string) $branch->name,
        ]);

        return redirect()
            ->route('branch.portal.employees')
            ->with('success_message', 'Welcome to the branch employee directory.');
    }

    public function employees(Request $request)
    {
        $branch = $request->attributes->get('branch_portal');
        $centreBranchIds = Branch::whereRaw('LOWER(name) = ?', [Str::lower(trim((string) $branch->name))])
            ->where('status', '=', 1)
            ->whereNull('deleted_at')
            ->pluck('id')
            ->map(function ($branchId) {
                return (int) $branchId;
            })
            ->values()
            ->all();

        $rows = Employee::where('status', '!=', 3)
            ->where(function ($query) use ($centreBranchIds) {
                foreach ($centreBranchIds as $branchId) {
                    $query->orWhereJsonContains('branch', $branchId);
                }
            })
            ->orderBy('first_name', 'ASC')
            ->orderBy('last_name', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->map(function ($employee) {
                $employee->employee_name = $this->buildEmployeeName($employee);

                return $employee;
            });

        return view('front.pages.branch-portal.employees', [
            'title' => 'Branch Employee Directory',
            'branch' => $branch,
            'centre_branch_ids' => $centreBranchIds,
            'rows' => $rows,
        ]);
    }

    public function logout(Request $request)
    {
        $request->session()->forget('branch_portal');
        $request->session()->regenerateToken();

        return redirect()
            ->route('branch.portal.login')
            ->with('success_message', 'You have been logged out successfully.');
    }

    private function buildEmployeeName($employee): string
    {
        return collect([
            $employee->first_name,
            $employee->middle_name,
            $employee->last_name,
        ])->map(function ($namePart) {
            return trim((string) $namePart);
        })->filter()->implode(' ');
    }
}
