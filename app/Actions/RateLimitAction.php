<?php

namespace App\Actions;

use Illuminate\Support\Facades\RateLimiter;
use App\Models\Message;

class RateLimitAction
{
    /**
     * Rate Limit after attempt from a specific IP.
     *
     * @param string $key
     * @param int $maxAttempts
     * @param int $decayMinutes
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function rateLimit(string $key, int $maxAttempts = 5, int $decayMinutes = 1)
    {
       
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'message' => "Too many attempts. Please try again in $seconds seconds."
            ], 429);
        }
       
        RateLimiter::hit($key, $decayMinutes * 60);

        return null;
    }

    /**
     * Mark the message as read.
     *
     * @param Message $message
     * @return Message
     */
    public function read(Message $message): Message
    {
        $message->markAsRead();

        RateLimiter::clear('send-message:' . $message->user_id);

        return $message;
    }
}

