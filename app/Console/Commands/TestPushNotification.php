<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Notifications\ServiceRequestUpdated;
use App\Models\ServiceRequest;

class TestPushNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'push:test {user_id_or_email} {service_request_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test push notification to a user. Optionally specify a ServiceRequest ID.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $identifier = $this->argument('user_id_or_email');
        $serviceRequestId = $this->argument('service_request_id');

        $user = User::with('webPushSubscriptions')->where('id', $identifier)->orWhere('email', $identifier)->first();

        if (!$user) {
            $this->error("User with ID or email '{$identifier}' not found.");
            return Command::FAILURE;
        }

        if ($user->webPushSubscriptions->isEmpty()) {
            $this->info("User '{$user->email}' has no push subscriptions. Make sure the user has enabled push notifications in the browser.");
            return Command::SUCCESS;
        }

        // Create a dummy ServiceRequest if no ID is provided or if it's not found
        $serviceRequest = ServiceRequest::find($serviceRequestId) ?? new ServiceRequest([
            'id' => $serviceRequestId ?? 0, // Use provided ID or 0 for dummy
            'service_name' => 'Test Service (Console)',
            'status' => 'Richiesta integrazione', // Or any relevant status for testing
        ]);

        $this->info("Attempting to send push notification to user '{$user->email}' for Service Request '{$serviceRequest->service_name}'...");

        try {
            $user->notify(new ServiceRequestUpdated($serviceRequest));
            $this->info("Push notification sent successfully to '{$user->email}'.");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to send push notification: " . $e->getMessage());
            $this->error("Exception details: " . $e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}