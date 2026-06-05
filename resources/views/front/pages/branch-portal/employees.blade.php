<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <style>
        body {
            min-height: 100vh;
            margin: 0;
            background: #f3f7fb;
            color: #183047;
            font-family: Arial, sans-serif;
        }

        .portal-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 14px 24px;
            background: #ffffff;
            border-bottom: 1px solid #dce6ee;
            box-shadow: 0 7px 20px rgba(23, 48, 71, 0.06);
        }

        .portal-title {
            margin: 0;
            color: #143b5f;
            font-size: 20px;
            font-weight: 800;
        }

        .portal-subtitle {
            margin: 3px 0 0;
            color: #728598;
            font-size: 13px;
        }

        .portal-actions,
        .portal-nav {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .portal-nav a {
            border: 1px solid #d7e1ea;
            border-radius: 9px;
            padding: 7px 10px;
            color: #143b5f;
            font-size: 13px;
            font-weight: 800;
            text-decoration: none;
        }

        .portal-nav a.active {
            background: #143b5f;
            color: #ffffff;
        }

        .portal-main {
            max-width: 1480px;
            margin: 0 auto;
            padding: 24px;
        }

        .branch-hero {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 18px;
            padding: 22px;
            border-radius: 16px;
            background: linear-gradient(135deg, #123451 0%, #17616d 100%);
            color: #ffffff;
            box-shadow: 0 18px 38px rgba(18, 52, 81, 0.2);
        }

        .branch-hero h1 {
            margin: 0;
            font-size: 28px;
        }

        .branch-hero p {
            margin: 7px 0 0;
            color: rgba(255, 255, 255, 0.84);
        }

        .branch-badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 12px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            font-size: 13px;
            font-weight: 700;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 18px;
        }

        .summary-card {
            border: 1px solid #dce6ee;
            border-radius: 14px;
            background: #ffffff;
            padding: 16px;
            box-shadow: 0 10px 22px rgba(23, 48, 71, 0.05);
        }

        .summary-card span {
            display: block;
            color: #718395;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .summary-card strong {
            display: block;
            margin-top: 7px;
            color: #143b5f;
            font-size: 24px;
        }

        .employee-card {
            overflow: hidden;
            border: 1px solid #dce6ee;
            border-radius: 16px;
            background: #ffffff;
            box-shadow: 0 12px 28px rgba(23, 48, 71, 0.07);
        }

        .employee-card-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            flex-wrap: wrap;
            padding: 16px 18px;
            border-bottom: 1px solid #e5edf4;
        }

        .employee-card-head h2 {
            margin: 0;
            color: #143b5f;
            font-size: 18px;
        }

        .employee-search {
            width: min(100%, 310px);
            border: 1px solid #d7e1ea;
            border-radius: 9px;
            padding: 9px 12px;
        }

        .employee-table {
            margin: 0;
            min-width: 1040px;
        }

        .employee-table th {
            padding: 11px 9px;
            background: #143b5f;
            color: #ffffff;
            font-size: 11px;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .employee-table td {
            padding: 10px 9px;
            color: #294157;
            font-size: 13px;
            vertical-align: middle;
        }

        .employee-avatar-wrap {
            display: flex;
            justify-content: center;
        }

        .employee-avatar,
        .employee-avatar-fallback {
            width: 48px;
            height: 48px;
            border: 2px solid #d9e5ef;
            border-radius: 50%;
            background: #ffffff;
            object-fit: cover;
            box-shadow: 0 6px 14px rgba(23, 48, 71, 0.12);
        }

        .employee-avatar-fallback {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .employee-avatar-fallback.male {
            border-color: #bedbf2;
            background: #e9f5ff;
            color: #2672a9;
        }

        .employee-avatar-fallback.female {
            border-color: #f1c8db;
            background: #fff0f7;
            color: #bd4f82;
        }

        .employee-avatar-fallback.neutral {
            border-color: #d9e1e8;
            background: #f2f5f7;
            color: #718395;
        }

        .status-pill {
            display: inline-flex;
            border-radius: 999px;
            padding: 4px 8px;
            font-size: 11px;
            font-weight: 700;
        }

        .status-pill.active {
            background: #dbf5e7;
            color: #14663e;
        }

        .status-pill.inactive {
            background: #fff0d8;
            color: #985d12;
        }

        .empty-state {
            padding: 34px 18px;
            color: #718395;
            text-align: center;
        }

        @media (max-width: 700px) {
            .portal-topbar,
            .portal-main {
                padding: 14px;
            }

            .summary-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    @php
        $employeeRows = collect($rows);
    @endphp

    <div class="portal-topbar">
        <div>
            <h2 class="portal-title">Branch Employee Management</h2>
            <p class="portal-subtitle">{{ $branch->name }} branch directory</p>
        </div>
        <div class="portal-actions">
            <nav class="portal-nav">
                <a href="{{ route('branch.portal.employees') }}" class="active">Employees</a>
                <a href="{{ route('branch.portal.rosters') }}">Roster</a>
            </nav>
            <form method="POST" action="{{ route('branch.portal.logout') }}">
                @csrf
                <button type="submit" class="btn btn-danger btn-sm">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </button>
            </form>
        </div>
    </div>

    <main class="portal-main">
        @if(session('success_message'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success_message') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <section class="branch-hero">
            <div>
                <h1>{{ $branch->name }} Employee Directory</h1>
                <p>Read-only employee list for this assigned branch.</p>
            </div>
            <span class="branch-badge">
                <i class="fa-solid fa-code-branch"></i> {{ $branch->serial_id }}
            </span>
        </section>

        <section class="summary-grid">
            <div class="summary-card">
                <span>Total Assigned Employees</span>
                <strong>{{ $employeeRows->count() }}</strong>
            </div>
            <div class="summary-card">
                <span>Active Employees</span>
                <strong>{{ $employeeRows->where('status', 1)->count() }}</strong>
            </div>
            <div class="summary-card">
                <span>Inactive Employees</span>
                <strong>{{ $employeeRows->where('status', 0)->count() }}</strong>
            </div>
        </section>

        <section class="employee-card">
            <div class="employee-card-head">
                <h2>Assigned Employee List</h2>
                <input type="search" class="employee-search" id="employeeSearch" placeholder="Search employees">
            </div>

            @if($employeeRows->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-hover employee-table" id="employeeTable">
                        <thead>
                            <tr>
                                <th>Sl.</th>
                                <th>Employee No</th>
                                <th>Photo</th>
                                <th>Name</th>
                                <th>Gender</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>DOB</th>
                                <th>Age</th>
                                <th>DOJ</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($employeeRows as $employee)
                                @php
                                    $employeeImage = trim((string) $employee->image);
                                    $hasEmployeeImage = $employeeImage !== '' && file_exists(public_path(ltrim($employeeImage, '/\\')));
                                    $gender = strtolower(trim((string) $employee->gender));
                                    $avatarClass = $gender === 'male' ? 'male' : ($gender === 'female' ? 'female' : 'neutral');
                                    $avatarIcon = $gender === 'male' ? 'fa-person' : ($gender === 'female' ? 'fa-person-dress' : 'fa-user');
                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $employee->employee_no }}</td>
                                    <td>
                                        <div class="employee-avatar-wrap">
                                            @if($hasEmployeeImage)
                                                <img src="{{ url('public' . $employeeImage) }}"
                                                     alt="{{ $employee->employee_name !== '' ? $employee->employee_name : 'Employee' }}"
                                                     class="employee-avatar"
                                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';">
                                            @endif
                                            <span class="employee-avatar-fallback {{ $avatarClass }}" style="{{ $hasEmployeeImage ? 'display:none;' : '' }}">
                                                <i class="fa-solid {{ $avatarIcon }}"></i>
                                            </span>
                                        </div>
                                    </td>
                                    <td>{{ $employee->employee_name !== '' ? $employee->employee_name : '-' }}</td>
                                    <td>{{ $employee->gender ?: '-' }}</td>
                                    <td>{{ $employee->email ?: '-' }}</td>
                                    <td>{{ $employee->phone ?: '-' }}</td>
                                    <td>{{ $employee->dob ? date('d-m-Y', strtotime($employee->dob)) : '-' }}</td>
                                    <td>{{ $employee->age !== null ? $employee->age : '-' }}</td>
                                    <td>{{ $employee->doj ? date('d-m-Y', strtotime($employee->doj)) : '-' }}</td>
                                    <td>
                                        <span class="status-pill {{ (int) $employee->status === 1 ? 'active' : 'inactive' }}">
                                            {{ (int) $employee->status === 1 ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    <i class="fa-solid fa-users mb-2"></i>
                    <p class="mb-0">No employees are assigned to this branch.</p>
                </div>
            @endif
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const employeeSearch = document.getElementById('employeeSearch');
        const employeeRows = document.querySelectorAll('#employeeTable tbody tr');

        if (employeeSearch) {
            employeeSearch.addEventListener('input', function() {
                const searchValue = this.value.trim().toLowerCase();

                employeeRows.forEach(function(row) {
                    row.style.display = row.textContent.toLowerCase().includes(searchValue) ? '' : 'none';
                });
            });
        }
    </script>
</body>
</html>
