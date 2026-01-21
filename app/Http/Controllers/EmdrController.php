<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class EmdrController extends Controller
{
    // Admin control panel view
    public function admin()
    {
        return view('admin.emdr');
    }

    // Public live view
    public function live($token)
    {
        return view('emdr_live', ['token' => $token]);
    }

    // Public state endpoint (polled by live clients)
    // Supports optional ?since=<sequence> to return 304 when no changes
    public function getState(Request $request, $token)
    {
        $key = "emdr:state:" . $token;
        $state = Cache::get($key, null);

        if (!$state) {
            // return default state with sequence 0
            $state = [
                'isRunning' => false,
                'isPaused' => false,
                'speed' => 1,
                'pause' => 0,
                'sound' => 'beep',
                'volume' => 60,
                'objectType' => 'ball',
                'objectColor' => '#ffffff',
                'background' => 'blue',
                'customColor' => '#ffffff',
                'movementMode' => 'horizontal',
                'direction' => 'rtl',
                'sequence' => 0,
                'timestamp' => now()->timestamp,
            ];
        }

        $since = (int) $request->query('since', 0);
        $seq = isset($state['sequence']) ? (int) $state['sequence'] : 0;
        if ($since >= $seq) {
            return response('', 304);
        }

        return response()->json($state);
    }

    // Admin-only update state
    public function updateState(Request $request, $token)
    {
        // Basic auth check handled by middleware
        $data = $request->all();

        // Ensure timestamp and sequence
        $data['timestamp'] = now()->timestamp;
        if (!isset($data['sequence']) || !is_numeric($data['sequence'])) {
            // if sequence not supplied, generate one using timestamp
            $data['sequence'] = (int) ($data['timestamp']);
        }

        $key = "emdr:state:" . $token;
        // store for 6 hours
        Cache::put($key, $data, now()->addHours(6));

        return response()->json(['success' => true, 'sequence' => $data['sequence']]);
    }
}
