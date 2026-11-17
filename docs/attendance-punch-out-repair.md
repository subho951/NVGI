# Repair photo-free punch-outs

Deploy the holiday-only attendance fix together with `routes/console.php` before repairing data, so working-day bulk attendance cannot recreate the incorrect punches.

From the live Laravel project directory, preview the records for the incident date:

```sh
php artisan attendance:reset-photo-free-punch-outs 2026-11-17
```

Review the displayed employees. This selects only records for that attendance date with a punch-out time and a null, empty, or whitespace-only punch-out photo. It does not assume the database timestamps use the same timezone as roster times.

Apply to all reviewed matching records for that date:

```sh
php artisan attendance:reset-photo-free-punch-outs 2026-11-17 --apply
```

To restrict the correction to particular attendance record IDs from the live preview, add `--ids=123,456` to both commands. Use IDs from the live database; local IDs may differ.

The apply command locks matching rows, saves a complete JSON backup on the private local storage disk, and clears only `punch_out_at`, `punch_out_image`, `punch_out_ip`, and `punch_out_portal_branch_id`. It also updates `updated_at`. It preserves all punch-in and other attendance fields. The backup path is printed. If the backup fails, no records are changed.

Run the preview again to confirm no targeted photo-free punch-outs remain. Refresh the attendance portal; these employees can manually punch out with a photo while their roster is available for today's attendance. The correction does not recreate past actual departure times.

No database migration is required. This is an explicit data-repair command and does not run automatically during deployment. Re-running it skips records whose punch-outs are already cleared or have photos. Keep the private backup for review or recovery.
