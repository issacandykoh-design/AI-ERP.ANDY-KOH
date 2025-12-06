<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeBaseFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KnowledgeBaseMonitorController extends Controller
{
    /**
     * 显示监控仪表板
     */
    public function dashboard()
    {
        $stats = $this->getProcessingStatistics();
        $recentFiles = $this->getRecentProcessedFiles();
        $failedFiles = $this->getFailedFiles();
        
        return response()->json([
            'statistics' => $stats,
            'recent_files' => $recentFiles,
            'failed_files' => $failedFiles
        ]);
    }

    /**
     * 获取处理统计信息
     */
    public function getProcessingStatistics()
    {
        $total = KnowledgeBaseFile::count();
        $completed = KnowledgeBaseFile::where('processing_status', 'completed')->count();
        $pending = KnowledgeBaseFile::where('processing_status', 'pending')->count();
        $processing = KnowledgeBaseFile::where('processing_status', 'processing')->count();
        $failed = KnowledgeBaseFile::where('processing_status', 'failed')->count();
        
        // 今日处理统计
        $today = Carbon::today();
        $todayProcessed = KnowledgeBaseFile::where('ai_processed_at', '>=', $today)->count();
        $todayFailed = KnowledgeBaseFile::where('processing_status', 'failed')
            ->where('last_processing_attempt', '>=', $today)
            ->count();
        
        // 平均处理时间（基于最近100个文件）
        $avgProcessingTime = KnowledgeBaseFile::where('processing_status', 'completed')
            ->where('ai_processed_at', '>=', Carbon::now()->subDays(7))
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, last_processing_attempt, ai_processed_at)) as avg_time')
            ->value('avg_time');
        
        // 错误统计
        $errorStats = KnowledgeBaseFile::where('processing_status', 'failed')
            ->selectRaw('processing_error, COUNT(*) as count')
            ->groupBy('processing_error')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();
        
        return [
            'total_files' => $total,
            'completed' => $completed,
            'pending' => $pending,
            'processing' => $processing,
            'failed' => $failed,
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
            'today_processed' => $todayProcessed,
            'today_failed' => $todayFailed,
            'avg_processing_time' => $avgProcessingTime ? round($avgProcessingTime, 2) : null,
            'top_errors' => $errorStats
        ];
    }

    /**
     * 获取最近处理的文件
     */
    public function getRecentProcessedFiles($limit = 20)
    {
        return KnowledgeBaseFile::select([
            'id', 'filename', 'processing_status', 'ai_processed_at', 
            'last_processing_attempt', 'processing_attempts', 'processing_error'
        ])
        ->orderBy('last_processing_attempt', 'desc')
        ->limit($limit)
        ->get();
    }

    /**
     * 获取失败的文件
     */
    public function getFailedFiles($limit = 50)
    {
        return KnowledgeBaseFile::select([
            'id', 'filename', 'processing_error', 'last_processing_attempt', 
            'processing_attempts', 'knowledge_base_id'
        ])
        ->where('processing_status', 'failed')
        ->orderBy('last_processing_attempt', 'desc')
        ->limit($limit)
        ->get();
    }

    /**
     * 获取处理状态趋势（按天）
     */
    public function getProcessingTrends($days = 7)
    {
        $startDate = Carbon::now()->subDays($days);
        
        $trends = KnowledgeBaseFile::select([
            DB::raw('DATE(ai_processed_at) as date'),
            DB::raw('COUNT(*) as processed_count'),
            DB::raw('SUM(CASE WHEN processing_status = "completed" THEN 1 ELSE 0 END) as success_count'),
            DB::raw('SUM(CASE WHEN processing_status = "failed" THEN 1 ELSE 0 END) as failed_count')
        ])
        ->where('ai_processed_at', '>=', $startDate)
        ->groupBy(DB::raw('DATE(ai_processed_at)'))
        ->orderBy('date')
        ->get();
        
        return response()->json($trends);
    }

    /**
     * 重置失败文件状态
     */
    public function resetFailedFiles(Request $request)
    {
        $fileIds = $request->input('file_ids', []);
        
        if (empty($fileIds)) {
            // 重置所有失败文件
            $updated = KnowledgeBaseFile::where('processing_status', 'failed')
                ->update([
                    'processing_status' => 'pending',
                    'processing_error' => null,
                    'processing_attempts' => 0
                ]);
        } else {
            // 重置指定文件
            $updated = KnowledgeBaseFile::whereIn('id', $fileIds)
                ->where('processing_status', 'failed')
                ->update([
                    'processing_status' => 'pending',
                    'processing_error' => null,
                    'processing_attempts' => 0
                ]);
        }
        
        return response()->json([
            'success' => true,
            'message' => "已重置 {$updated} 个失败文件的状态",
            'updated_count' => $updated
        ]);
    }

    /**
     * 获取文件处理详情
     */
    public function getFileDetails($fileId)
    {
        $file = KnowledgeBaseFile::findOrFail($fileId);
        
        return response()->json([
            'file' => $file,
            'processing_history' => [
                'attempts' => $file->processing_attempts,
                'last_attempt' => $file->last_processing_attempt,
                'status' => $file->processing_status,
                'error' => $file->processing_error,
                'completed_at' => $file->ai_processed_at
            ]
        ]);
    }
}
