<?php

namespace Tests\Feature;

use App\Models\EmployeeAttendance;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BranchAttendanceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::create(2026, 6, 6, 8, 15, 0, 'Asia/Kolkata'));

        Schema::create('branches', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('unit_id');
            $table->text('serial_id')->nullable();
            $table->text('name')->nullable();
            $table->string('password')->nullable();
            $table->text('original_password')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('employees', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->text('image')->nullable();
            $table->text('gender')->nullable();
        });

        Schema::create('employee_schedule_rosters', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->integer('employee_id')->index();
            $table->text('employee_no')->nullable();
            $table->text('employee_name')->nullable();
            $table->string('category', 100)->index();
            $table->integer('unit_id')->nullable();
            $table->text('unit_name')->nullable();
            $table->integer('branch_id')->index();
            $table->text('branch_name')->nullable();
            $table->date('roster_date')->index();
            $table->unsignedTinyInteger('roster_month');
            $table->unsignedSmallInteger('roster_year');
            $table->string('day_name', 20)->nullable();
            $table->string('in_time', 5)->nullable();
            $table->string('out_time', 5)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamp('generated_at')->nullable();
            $table->integer('created_by')->default(0);
            $table->integer('updated_by')->default(0);
            $table->timestamps();
        });

        Schema::create('employee_attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('roster_id')->unique();
            $table->integer('employee_id')->index();
            $table->text('employee_no')->nullable();
            $table->text('employee_name')->nullable();
            $table->string('category', 100);
            $table->integer('unit_id')->nullable();
            $table->integer('branch_id')->index();
            $table->text('branch_name')->nullable();
            $table->date('attendance_date');
            $table->string('scheduled_in_time', 5)->nullable();
            $table->string('scheduled_out_time', 5)->nullable();
            $table->boolean('is_late')->default(false)->index();
            $table->unsignedSmallInteger('late_minutes')->default(0);
            $table->boolean('is_absent')->default(false)->index();
            $table->timestamp('absent_marked_at')->nullable();
            $table->timestamp('punch_in_at')->nullable();
            $table->text('punch_in_image')->nullable();
            $table->string('punch_in_ip', 45)->nullable();
            $table->integer('punch_in_portal_branch_id')->nullable();
            $table->timestamp('punch_out_at')->nullable();
            $table->text('punch_out_image')->nullable();
            $table->string('punch_out_ip', 45)->nullable();
            $table->integer('punch_out_portal_branch_id')->nullable();
            $table->timestamps();
        });

        $this->seedAttendanceScenario();
    }

    protected function tearDown(): void
    {
        if (Schema::hasTable('employee_attendances')) {
            EmployeeAttendance::query()
                ->get(['punch_in_image', 'punch_out_image'])
                ->each(function ($attendance) {
                    foreach ([$attendance->punch_in_image, $attendance->punch_out_image] as $path) {
                        if ($path) {
                            File::delete(public_path(ltrim((string) $path, '/\\')));
                        }
                    }
                });
        }

        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_each_tsa_branch_roster_has_an_independent_attendance_cycle(): void
    {
        $photo = $this->attendancePhoto();
        $mobileUserAgent = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) Mobile/15E148';

        $bibirhatSession = [
            'branch_portal' => [
                'branch_id' => 1,
                'unit_id' => 2,
                'serial_id' => 'BIB',
                'branch_name' => 'Bibirhat',
            ],
        ];

        $this->withSession($bibirhatSession)
            ->withHeader('User-Agent', $mobileUserAgent)
            ->get($this->routeUrl('branch.portal.attendance.index'))
            ->assertOk()
            ->assertSee('Today Attendance')
            ->assertSee('8:00 AM - 10:00 AM')
            ->assertSee('[data-attendance-row][hidden]', false)
            ->assertSee('data-search="test tsa teacher nvgi-0010', false)
            ->assertDontSee('2:00 PM - 4:00 PM')
            ->assertDontSee('6:00 PM - 8:00 PM');

        $this->withSession($bibirhatSession)
            ->withHeader('User-Agent', $mobileUserAgent)
            ->post($this->routeUrl('branch.portal.attendance.punch-in', ['roster' => 100]), ['photo' => $photo])
            ->assertRedirect($this->routeUrl('branch.portal.attendance.index'))
            ->assertSessionHas('success_message');

        $this->assertDatabaseHas('employee_attendances', [
            'roster_id' => 100,
            'branch_id' => 1,
            'punch_in_portal_branch_id' => 1,
            'is_late' => 1,
            'late_minutes' => 15,
        ]);
        $this->assertDatabaseMissing('employee_attendances', ['roster_id' => 101]);
        $this->assertDatabaseMissing('employee_attendances', ['roster_id' => 102]);

        $this->withSession($bibirhatSession)
            ->get($this->routeUrl('branch.portal.attendance.report', [
                'from_date' => now()->toDateString(),
                'to_date' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee('Attendance Report')
            ->assertSee('From Date')
            ->assertSee('To Date')
            ->assertSee('Employee Details')
            ->assertDontSee('>Employee Code<', false)
            ->assertDontSee('>Department<', false)
            ->assertDontSee('>Location<', false)
            ->assertSee('8:00 AM - 10:00 AM')
            ->assertSee('IN: 08:15 AM')
            ->assertSee('Late 15 min')
            ->assertSee('data-photo=', false)
            ->assertSee('Roster Only Employee')
            ->assertDontSee('name="branch_id"', false)
            ->assertDontSee('2:00 PM - 4:00 PM')
            ->assertDontSee('6:00 PM - 8:00 PM');

        $this->withSession($bibirhatSession)
            ->withHeader('User-Agent', $mobileUserAgent)
            ->post($this->routeUrl('branch.portal.attendance.punch-in', ['roster' => 101]), ['photo' => $photo])
            ->assertRedirect($this->routeUrl('branch.portal.attendance.index'))
            ->assertSessionHas('error_message');

        $this->assertDatabaseMissing('employee_attendances', ['roster_id' => 101]);

        $mukundapurSession = [
            'branch_portal' => [
                'branch_id' => 2,
                'unit_id' => 2,
                'serial_id' => 'MUK',
                'branch_name' => 'Mukundapur',
            ],
        ];

        $this->withSession($mukundapurSession)
            ->withHeader('User-Agent', $mobileUserAgent)
            ->post($this->routeUrl('branch.portal.attendance.punch-in', ['roster' => 101]), ['photo' => $photo])
            ->assertRedirect($this->routeUrl('branch.portal.attendance.index'))
            ->assertSessionHas('success_message');

        $this->assertDatabaseHas('employee_attendances', [
            'roster_id' => 101,
            'branch_id' => 2,
            'punch_in_portal_branch_id' => 2,
            'is_late' => 0,
            'late_minutes' => 0,
        ]);

        $this->withSession($mukundapurSession)
            ->withHeader('User-Agent', $mobileUserAgent)
            ->get($this->routeUrl('branch.portal.attendance.mark', ['roster' => 101]))
            ->assertOk()
            ->assertSee('PUNCH OUT');

        $this->withSession($mukundapurSession)
            ->withHeader('User-Agent', $mobileUserAgent)
            ->post($this->routeUrl('branch.portal.attendance.punch-out', ['roster' => 101]), ['photo' => $photo])
            ->assertRedirect($this->routeUrl('branch.portal.attendance.index'))
            ->assertSessionHas('success_message');

        $this->assertNotNull(
            EmployeeAttendance::where('roster_id', 101)->value('punch_out_at')
        );
    }

    public function test_report_keeps_roster_only_employee_and_persists_finished_absence(): void
    {
        $bibirhatSession = [
            'branch_portal' => [
                'branch_id' => 1,
                'unit_id' => 2,
                'serial_id' => 'BIB',
                'branch_name' => 'Bibirhat',
            ],
        ];

        $this->withSession($bibirhatSession)
            ->get($this->routeUrl('branch.portal.attendance.report', [
                'from_date' => now()->toDateString(),
                'to_date' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee('Roster Only Employee')
            ->assertSee('9:00 AM - 10:00 AM')
            ->assertSee('data-absent-count="0"', false);

        $this->assertDatabaseMissing('employee_attendances', ['roster_id' => 103]);

        $this->withSession($bibirhatSession)
            ->get($this->routeUrl('branch.portal.attendance.report', [
                'from_date' => now()->subDay()->toDateString(),
                'to_date' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee('Roster Only Employee')
            ->assertSee('ABSENT')
            ->assertSee('data-absent-count="1"', false);

        $this->assertDatabaseHas('employee_attendances', [
            'roster_id' => 104,
            'employee_id' => 20,
            'is_absent' => 1,
            'is_late' => 0,
            'late_minutes' => 0,
        ]);

        $this->assertNotNull(
            EmployeeAttendance::where('roster_id', 104)->value('absent_marked_at')
        );
    }

    private function seedAttendanceScenario(): void
    {
        $now = now();

        foreach ([
            ['id' => 1, 'unit_id' => 2, 'serial_id' => 'BIB', 'name' => 'Bibirhat'],
            ['id' => 2, 'unit_id' => 2, 'serial_id' => 'MUK', 'name' => 'Mukundapur'],
            ['id' => 3, 'unit_id' => 2, 'serial_id' => 'RAJ', 'name' => 'Rajarhat'],
        ] as $branch) {
            DB::table('branches')->insert(array_merge($branch, [
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        DB::table('employees')->insert([
            [
                'id' => 10,
                'image' => null,
                'gender' => 'Male',
            ],
            [
                'id' => 20,
                'image' => null,
                'gender' => 'Female',
            ],
        ]);

        $rosters = [
            ['id' => 100, 'employee_id' => 10, 'employee_no' => 'NVGI-0010', 'employee_name' => 'Test TSA Teacher', 'branch_id' => 1, 'branch_name' => 'Bibirhat', 'roster_date' => now(), 'in_time' => '08:00', 'out_time' => '10:00'],
            ['id' => 101, 'employee_id' => 10, 'employee_no' => 'NVGI-0010', 'employee_name' => 'Test TSA Teacher', 'branch_id' => 2, 'branch_name' => 'Mukundapur', 'roster_date' => now(), 'in_time' => '14:00', 'out_time' => '16:00'],
            ['id' => 102, 'employee_id' => 10, 'employee_no' => 'NVGI-0010', 'employee_name' => 'Test TSA Teacher', 'branch_id' => 3, 'branch_name' => 'Rajarhat', 'roster_date' => now(), 'in_time' => '18:00', 'out_time' => '20:00'],
            ['id' => 103, 'employee_id' => 20, 'employee_no' => 'NVGI-0020', 'employee_name' => 'Roster Only Employee', 'branch_id' => 1, 'branch_name' => 'Bibirhat', 'roster_date' => now(), 'in_time' => '09:00', 'out_time' => '10:00'],
            ['id' => 104, 'employee_id' => 20, 'employee_no' => 'NVGI-0020', 'employee_name' => 'Roster Only Employee', 'branch_id' => 1, 'branch_name' => 'Bibirhat', 'roster_date' => now()->subDay(), 'in_time' => '08:00', 'out_time' => '10:00'],
        ];

        foreach ($rosters as $roster) {
            DB::table('employee_schedule_rosters')->insert(array_merge($roster, [
                'category' => 'TSA TEACHER',
                'unit_id' => 2,
                'unit_name' => 'TSA',
                'roster_date' => $roster['roster_date']->toDateString(),
                'roster_month' => $roster['roster_date']->month,
                'roster_year' => $roster['roster_date']->year,
                'day_name' => $roster['roster_date']->format('l'),
                'status' => 1,
                'generated_at' => $now,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }

    private function attendancePhoto(): string
    {
        $image = imagecreatetruecolor(240, 240);
        $background = imagecolorallocate($image, 42, 117, 170);
        imagefill($image, 0, 0, $background);

        ob_start();
        imagejpeg($image, null, 82);
        $bytes = ob_get_clean();
        imagedestroy($image);

        return 'data:image/jpeg;base64,'.base64_encode($bytes);
    }

    private function routeUrl(string $name, array $parameters = []): string
    {
        return 'http://localhost'.route($name, $parameters, false);
    }
}
