<?php

namespace App\Console\Commands;

use App\AppointmentReminders\AppointmentReminder;
use Illuminate\Console\Command;

class SendReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notification for upcomming appointment in 24 hours';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // return Command::SUCCESS;
        $appointmentReminder = new AppointmentReminder();
        $appointmentReminder->sendReminders();
    }
}
