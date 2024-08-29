<?php

namespace App\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

use Illuminate\Support\ServiceProvider;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Queue\Events\JobFailed;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url')."/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });

        Queue::failing(function (JobFailed $event) {
            Log::debug('Job failed.', ['data' => $event->job->payload()]);
        });
            
        Queue::after(function (JobFailed|JobProcessed $event) {
            Log::debug('Job processed.', ['data' => $event->job->payload()]);
        });

        // add job to stats
        // $chapterProcessionQueueStat = new ChapterProcessionQueueStat();
        // $chapterProcessionQueueStat->user_id = $this->userId;
        // $chapterProcessionQueueStat->chapter_id = $this->chapterId;
        // $chapterProcessionQueueStat->status = 'finished';
        // $chapterProcessionQueueStat->word_count = $wordCount;
        // $chapterProcessionQueueStat->dispatched_at = $this->dispatchedAt;
        // $chapterProcessionQueueStat->started_at = $this->startedAt;
        // $chapterProcessionQueueStat->finished_at = Carbon::now();
        // $chapterProcessionQueueStat->save();
    }
}
