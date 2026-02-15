<?php
namespace App\Http\Controllers\Common;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use App\Helpers\Helper;
class TableController extends Controller
{
    public function fetch(Request $request)
    {
        // Helper::pr($request->all());
        $table = $request->input('table');
        $orderBy = $request->input('orderBy', 'id');
        $orderType = $request->input('orderType', 'desc');
        $rawColumns = explode(',', $request->input('columns'));
        $search = $request->input('search');
        $page = $request->input('page', 1);
        // $limit = 50;
        $limit = $request->input('perPage', 50); // Default to 50

        if (!in_array('id', $rawColumns)) {
            $rawColumns[] = 'id';
        }

        // Start query
        $query = DB::table($table);

        // JOINs (PostgreSQL-safe with CAST)
        if ($table === 'states') {
            $query->leftJoin('countries', DB::raw("$table.country_id"), '=', DB::raw("countries.id"));
        }
        if ($table === 'cities') {
            $query->leftJoin('countries', DB::raw("$table.country_id"), '=', DB::raw("countries.id"));
            $query->leftJoin('states', DB::raw("$table.state_id"), '=', DB::raw("states.id"));
        }
        if ($table === 'campaigns') {
            $query->leftJoin('campaign_types', DB::raw("$table.campaign_type_id"), '=', DB::raw("campaign_types.id"));
        }
        if ($table === 'faq_sub_categories') {
            $query->leftJoin('faq_categories', DB::raw("$table.faq_category_id"), '=', DB::raw("faq_categories.id"));
        }
        if ($table === 'faqs') {
            $query->leftJoin('faq_categories', DB::raw("$table.faq_category_id"), '=', DB::raw("faq_categories.id"));
            $query->leftJoin('faq_sub_categories', DB::raw("$table.faq_sub_category_id"), '=', DB::raw("faq_sub_categories.id"));
        }
        if ($table === 'users') {
            $query->leftJoin('roles', DB::raw("$table.role_id"), '=', DB::raw("roles.id"));
        }
        if ($table == 'lead_statuses') {
            $query->leftJoin('lead_statuses as parent', DB::raw("$table.parent_id"), '=', DB::raw("parent.id"));
            // $query->leftJoin('lead_statuses as parent', 'lead_statuses.parent_id', '=', 'parent.id')
            //     ->addSelect('parent.name as parent_name');
        }
        if ($table === 'payment_details') {
            $query->leftJoin('submissions', DB::raw("$table.member_id"), '=', DB::raw("submissions.id"));
            $query->leftJoin('conference_packages', DB::raw("$table.package_id"), '=', DB::raw("conference_packages.id"));
        }
        if ($table === 'awards') {
            $query->leftJoin('submission_categories', DB::raw("$table.submission_category_id"), '=', DB::raw("submission_categories.id"));
        }
        if ($table === 'conference_packages') {
            $query->leftJoin('member_categories', DB::raw("$table.member_category_id"), '=', DB::raw("member_categories.id"));
            $query->leftJoin('package_categories', DB::raw("$table.package_category_id"), '=', DB::raw("package_categories.id"));
        }
        if ($table === 'questions') {
            $query->leftJoin('submission_categories', DB::raw("$table.submission_category_id"), '=', DB::raw("submission_categories.id"));
        }
        if ($table === 'uploads') {
            $query->leftJoin('submission_categories', DB::raw("$table.submission_category_id"), '=', DB::raw("submission_categories.id"));
        }

        // Aliased select columns
        $columns = array_map(function ($col) use ($table) {
            if ($table === 'states' && $col === 'country_id') {
                return 'countries.name as country_name';
            }
            if ($table === 'cities') {
                if ($col === 'country_id') {
                    return 'countries.name as country_name';
                }
                if ($col === 'state_id') {
                    return 'states.name as state_name';
                }
            }
            if ($table === 'campaigns' && $col === 'campaign_type_id') {
                return 'campaign_types.name as campaign_type_name';
            }
            if ($table === 'faq_sub_categories' && $col === 'faq_category_id') {
                return 'faq_categories.name as faq_category_name';
            }
            if ($table === 'faqs') {
                if ($col === 'faq_category_id') {
                    return 'faq_categories.name as faq_category_name';
                }
                if ($col === 'faq_sub_category_id') {
                    return 'faq_sub_categories.name as faq_sub_category_name';
                }
            }
            if ($table === 'users')
            {
                if($col === 'role_id') {
                    return 'roles.role_name as role_name';
                }
            }

            if ($table === 'lead_statuses' && $col === 'parent_id') {
                return 'parent.name as parent_name';
            }

            if ($table === 'payment_details') {
                if ($col === 'member_id') {
                    return DB::raw("CONCAT(submissions.first_name, ' ', submissions.last_name) as member_name");
                }
                if ($col === 'package_id') {
                    return 'conference_packages.name as package_name';
                }
            }

            if ($table === 'awards' && $col === 'submission_category_id') {
                return 'submission_categories.name as submission_category_name';
            }
            if ($table === 'conference_packages') {
                if ($col === 'member_category_id') {
                    return 'package_categories.name as member_category_name';
                }
                if ($col === 'package_category_id') {
                    return 'member_categories.name as package_category_name';
                }
            }
            if ($table === 'questions' && $col === 'submission_category_id') {
                return 'submission_categories.name as submission_category_name';
            }
            if ($table === 'uploads' && $col === 'submission_category_id') {
                return 'submission_categories.name as submission_category_name';
            }

            return str_contains($col, '.') ? $col : "$table.$col";
        }, $rawColumns);

        $query->select($columns);

        // Apply conditions
        $conditions = json_decode(urldecode($request->input('conditions', '[]')), true);
        if (!empty($conditions)) {
            foreach ($conditions as $condition) {
                if (isset($condition['column'], $condition['operator'], $condition['value'])) {
                    $column = str_contains($condition['column'], '.') ? $condition['column'] : "$table.{$condition['column']}";
                    $query->where($column, $condition['operator'], $condition['value']);
                }
            }
        }

        // Search
        // if ($search) {
        //     $query->where(function ($q) use ($columns, $search) {
        //         foreach ($columns as $col) {
        //             $baseCol = explode(' as ', $col)[0];
        //             $q->orWhere($baseCol, 'LIKE', "%{$search}%");
        //         }
        //     });
        // }

         // Search new version
        // if ($search) {
        //     $query->where(function ($q) use ($columns, $search, $query) {
        //         foreach ($columns as $col) {
        //             // Convert DB::raw() to usable string if needed
        //             if ($col instanceof \Illuminate\Database\Query\Expression) {
        //                 // Safely extract raw SQL text
        //                 $colStr = $col->getValue(DB::connection()->getQueryGrammar());
        //             } else {
        //                 $colStr = $col;
        //             }

        //             // Extract base column (if aliased)
        //             $parts = explode(' as ', strtolower($colStr));
        //             $baseCol = trim($parts[0]);

        //             // Only use simple column names (avoid CONCAT, CASE, etc.)
        //             if (
        //                 !str_contains($baseCol, '(') &&
        //                 !str_contains($baseCol, 'case ') &&
        //                 !str_contains($baseCol, 'concat(')
        //             ) {
        //                 // Ensure table prefix if missing
        //                 if (!str_contains($baseCol, '.')) {
        //                     $baseCol = $query->from . '.' . $baseCol;
        //                 }

        //                 $q->orWhere($baseCol, 'LIKE', "%{$search}%");
        //             }
        //         }
        //     });
        // }

        // Search (robust - handles DB::raw expressions like CONCAT(...))
            // Search (robust - handles DB::raw expressions like CONCAT(...))
            if ($search) {
                // escape user wildcards if you want (optional)
                $safeSearch = str_replace(['\\','%','_'], ['\\\\','\\%','\\_'], $search);

                $query->where(function ($q) use ($columns, $safeSearch, $query) {
                    foreach ($columns as $col) {
                        // === get a usable string for the column/expression ===
                        if ($col instanceof \Illuminate\Database\Query\Expression) {
                            try {
                                // preferred: get raw SQL text using the grammar
                                $colStr = $col->getValue(DB::connection()->getQueryGrammar());
                            } catch (\Throwable $e) {
                                // fallback: try casting (may still fail); ensure we at least have a string
                                try {
                                    $colStr = (string) $col;
                                } catch (\Throwable $e2) {
                                    // last resort: skip this column if we can't render it
                                    continue;
                                }
                            }
                        } else {
                            $colStr = $col;
                        }

                        $colStr = trim($colStr);

                        // strip alias like "expr as alias" and remove surrounding backticks/quotes
                        $exprOnly = preg_replace('/\s+as\s+[`"\']?[\w\-\.\@]+[`"\']?$/i', '', $colStr);
                        $exprOnly = preg_replace('/^`(.*)`$/', '$1', $exprOnly);

                        $exprLower = strtolower($exprOnly);

                        // detect function/raw expression
                        if (str_contains($exprLower, '(') || str_contains($exprLower, 'concat') || str_contains($exprLower, 'case ')) {
                            $rawExpr = "({$exprOnly})";
                            $likeOp = DB::getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';
                            $q->orWhereRaw("$rawExpr $likeOp ?", ["%{$safeSearch}%"]);
                            continue;
                        }

                        // simple column name -> ensure table prefix
                        $baseCol = $exprOnly;
                        if (!str_contains($baseCol, '.')) {
                            // $query->from is available on Query\Builder
                            $baseCol = $query->from . '.' . $baseCol;
                        }

                        $likeOp = DB::getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';
                        $q->orWhere($baseCol, $likeOp, "%{$safeSearch}%");
                    }
                });
            }


        // Count before pagination
        $total = (clone $query)->count();

        // Paginate
        $data = $query->orderBy("$table.$orderBy", $orderType)
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                $item->encoded_id = urlencode(base64_encode($item->id));
                return $item;
            });

        return response()->json([
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'pages' => ceil($total / $limit),
        ]);
    }

    public function export(Request $request)
    {
        $table = $request->input('table');
        $columns = explode(',', $request->input('columns'));
        $titles = explode(',', $request->input('headers', '')); // <-- NEW
        $format = $request->input('format', 'csv');
        $search = $request->input('search');

        $filename = $request->input('filename'); // Optional
        $defaultName = $table . '_export_' . now()->format('Y-m-d_H-i-s');
        // $filename = $filename ?: $defaultName;
        $filename =  $defaultName;

        // $columns = array_filter($columns, fn($col) => strtolower($col) !== 'actions');

        if ($table === 'payment_details') {
            $transformedColumns = [];
            foreach ($columns as $col) {
                if ($col === 'member_id') {
                    $transformedColumns[] = DB::raw("CONCAT(submissions.first_name, ' ', submissions.last_name) as member_name");
                } elseif ($col === 'package_id') {
                    $transformedColumns[] = DB::raw("conference_packages.name as package_name");
                } elseif ($col === 'status') {
                    $transformedColumns[] = DB::raw("CASE WHEN $table.status = 'Success' THEN 'Success' ELSE 'Failed' END as status");
                } else {
                    $transformedColumns[] = $table . '.' . $col; // prefix to avoid ambiguity
                }
            }
            $columns = $transformedColumns;
        }
        

        $query = DB::table($table)->select($columns);

        $conditions = json_decode(urldecode($request->input('conditions')), true);
        if (!empty($conditions)) {
            foreach ($conditions as $condition) {
                if (isset($condition['column'], $condition['operator'], $condition['value'])) {
                    $query->where($condition['column'], $condition['operator'], $condition['value']);
                }
            }
        }

        if ($search) {
            $query->where(function ($q) use ($columns, $search) {
                foreach ($columns as $col) {
                    $q->orWhere($col, 'like', '%' . $search . '%');
                }
            });
        }

        if ($table === 'payment_details') {
            $query->leftJoin('submissions', DB::raw("$table.member_id"), '=', DB::raw("submissions.id"));
            $query->leftJoin('conference_packages', DB::raw("$table.package_id"), '=', DB::raw("conference_packages.id"));
        }
        // if ($table === 'submissions') {
        //     $query->leftJoin('conference_packages', DB::raw("$table.package_id"), '=', DB::raw("conference_packages.id"));
        // }

        $rawData = $query->orderBy($table . '.id', 'DESC')->get()->toArray();

        // Add Sl. No. to data
        $data = [];
        foreach ($rawData as $index => $row) {
            // $data[] = array_merge(['Sl. No.' => $index + 1], (array) $row);
            $rowArray = (array) $row;

            // Remove unwanted columns
            unset($rowArray['id']);   // remove DB id
            // If duplicate status column exists, unset it here too
            // unset($rowArray['status']); // only if you end up with 2 of them

            // Add Sl. No. as first column
            $rowArray = array_merge(['Sl. No.' => $index + 1], $rowArray);

            $data[] = $rowArray;
        }

        // Add Sl. No. to headings
        // $columns = array_merge(['Sl. No.'], $columns);
        $columns = array_map(fn($c) => $c === 'id' ? 'Sl. No.' : $c, $columns);

        // Fallback to raw column names if no custom titles given
        // $headers = count($titles) === count($columns) ? $titles : $columns;
        $headers = count($titles) === count($columns) ? $titles : $columns;        
        
        switch ($format) {
            case 'csv':
                return $this->exportCsv($titles, $data, $filename . '.csv');

            case 'excel':
                return Excel::download(new \App\Exports\ArrayExport($columns, $data), 'export.xlsx');

            case 'pdf':
                $pdf = PDF::loadView('exports.table', ['columns' => $headers, 'headers' => $titles, 'data' => $data]);
                return $pdf->download($filename . '.pdf');
        }

        return response()->json(['error' => 'Invalid format'], 400);
    }

    protected function exportCsv($columns, $data, $filename)
    {
        // $filename = 'export.csv';
        $handle = fopen('php://output', 'w');

        header('Content-Type: text/csv');
        header("Content-Disposition: attachment; filename=$filename");

        // Write headers
        fputcsv($handle, $columns);
        
        // Write each row
        foreach ($data as $row) {
            fputcsv($handle, array_values($row));
        }

        fclose($handle);
        exit;
    }
}