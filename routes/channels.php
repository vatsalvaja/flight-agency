<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('order.tracking.{orderId}', function ($user, $orderId) {
    Log::info('Broadcasting Auth Check running in channels.php', [
        'user_id' => $user ? $user->id : null,
        'user_role' => $user ? $user->role_id : null,
        'order_id' => $orderId
    ]);

    $order = \App\Models\AssignLuggage::find($orderId);
    
    if (!$order) {
        Log::warning('Broadcasting Auth Failed: Order not found.', ['order_id' => $orderId]);
        return false;
    }

    // 1. Admin users (role_id === 0) have access
    if ((int) $user->role_id === 0) {
        Log::info('Broadcasting Auth Passed: User is Admin.', ['user_id' => $user->id]);
        return true;
    }

    // 2. The assigned driver has access
    if ((int) $order->driver_id === (int) $user->id) {
        Log::info('Broadcasting Auth Passed: User is Assigned Driver.', ['user_id' => $user->id]);
        return true;
    }

    // 3. The manager who created/assigned this order has access
    if ((int) $order->created_by === (int) $user->id) {
        Log::info('Broadcasting Auth Passed: User is Managing Creator.', ['user_id' => $user->id]);
        return true;
    }

    Log::warning('Broadcasting Auth Failed: User does not match Admin, Driver, or Manager roles.', [
        'user_id' => $user->id,
        'order_driver_id' => $order->driver_id,
        'order_created_by' => $order->created_by
    ]);
    return false;
});
