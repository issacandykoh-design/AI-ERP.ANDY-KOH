<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\TaskHistory;
use App\Services\Google;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendDailyTaskChangeReport extends Command
{
    protected $signature = 'send-daily-task-change-report';
    protected $description = 'Upload daily task change report to Google Drive';

    public function handle()
    {
        Company::active()->select(['id', 'company_name', 'token', 'google_calendar_verification_status'])->chunk(50, function ($companies) {
            foreach ($companies as $company) {
                if ($company->google_calendar_verification_status !== 'verified') {
                    continue;
                }
                if (empty($company->token)) {
                    continue;
                }

                $date = Carbon::now()->toDateString();
                $rows = [];
                $rows[] = ['Company', 'Task Code', 'Task Heading', 'Change Details', 'Board Column', 'User', 'Changed At'];

                $histories = TaskHistory::with(['task', 'user', 'boardColumn'])
                    ->whereDate('created_at', $date)
                    ->whereHas('task', function ($q) use ($company) {
                        $q->where('company_id', $company->id);
                    })
                    ->orderBy('created_at')
                    ->get();

                foreach ($histories as $h) {
                    $rows[] = [
                        $company->company_name,
                        $h->task ? ($h->task->task_short_code ?? $h->task->id) : '',
                        $h->task ? ($h->task->heading ?? '') : '',
                        $h->details,
                        $h->boardColumn ? ($h->boardColumn->column_name ?? '') : '',
                        $h->user ? ($h->user->name ?? '') : '',
                        $h->created_at ? $h->created_at->toDateTimeString() : '',
                    ];
                }

                $csv = '';
                foreach ($rows as $row) {
                    $escaped = array_map(function ($v) {
                        $v = (string) $v;
                        $v = str_replace('"', '""', $v);
                        if (str_contains($v, ',') || str_contains($v, '"') || str_contains($v, "\n")) {
                            $v = '"' . str_replace('"', '""', $v) . '"';
                        }
                        return $v;
                    }, $row);
                    $csv .= implode(',', $escaped) . "\n";
                }

                try {
                    $google = new Google();
                    $google->connectUsing($company->token);
                    $drive = $google->service('Drive');
                    $fileMetadata = new \Google_Service_Drive_DriveFile(['name' => 'Task-Changes-' . $date . '.csv']);
                    $drive->files->create($fileMetadata, [
                        'data' => $csv,
                        'mimeType' => 'text/csv',
                        'uploadType' => 'multipart',
                    ]);
                } catch (\Throwable $e) {
                    $this->error($e->getMessage());
                }
            }
        });

        return Command::SUCCESS;
    }
}

