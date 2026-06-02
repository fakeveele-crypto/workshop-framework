<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AntrianController extends Controller
{
    private const KEY = 'antrian.realtime.state';

    public function index() { return auth()->check() ? to_route('antrian.admin') : to_route('antrian.guest'); }

    public function guest()
    {
        $s = $this->state();
        return view('antrian.guest', ['nextNumber' => $s['next_number'], 'waitingCount' => count($this->statusList($s['queues'], 'waiting')), 'boardUrl' => route('antrian.board')]);
    }

    public function storeGuest(Request $request)
    {
        $v = $request->validate(['nama' => ['required', 'string', 'max:100'], 'poli' => ['nullable', 'string', 'max:100']]);
        $s = $this->state();
        $token = (string) Str::uuid();
        $s['queues'][] = ['token' => $token, 'number' => $s['next_number']++, 'nama' => trim((string) $v['nama']), 'poli' => trim((string) ($v['poli'] ?? 'Umum')) ?: 'Umum', 'status' => 'waiting', 'created_at' => now()->toDateTimeString(), 'called_at' => null, 'late_at' => null, 'recall_count' => 0];
        $this->save($s);
        return to_route('antrian.guest.ticket', ['token' => $token]);
    }

    public function showGuestTicket(string $token)
    {
        $s = $this->state();
        $i = $this->queueIndex($s['queues'], $token);
        abort_unless($i !== null, 404);
        return view('antrian.ticket', ['queue' => $s['queues'][$i], 'boardUrl' => route('antrian.board'), 'adminUrl' => route('antrian.admin')]);
    }

    public function admin()
    {
        $d = $this->digest($this->state());
        return view('antrian.admin', ['summary' => $d['summary'], 'waitingQueues' => $d['waiting'], 'calledQueues' => $d['called'], 'lateQueues' => $d['late'], 'boardUrl' => route('antrian.board'), 'streamUrl' => route('antrian.sse')]);
    }

    public function callNext()
    {
        $s = $this->state();
        $i = array_key_first(array_filter($s['queues'], fn ($q) => ($q['status'] ?? '') === 'waiting'));
        if ($i === null) return response()->json(['success' => false, 'message' => 'Tidak ada antrian yang bisa dipanggil.'], 422);
        $s['queues'][$i]['status'] = 'called';
        $s['queues'][$i]['called_at'] = now()->toDateTimeString();
        $s['queues'][$i]['late_at'] = null;
        $s['last_called'] = $s['queues'][$i];
        $this->save($s);
        return response()->json(['success' => true, 'message' => 'Antrian berhasil dipanggil.', 'data' => $s['queues'][$i]]);
    }

    public function markLate(string $token)
    {
        return $this->updateByToken($token, function (array &$s, int $i): void { $s['queues'][$i]['status'] = 'late'; $s['queues'][$i]['late_at'] = now()->toDateTimeString(); }, 'Antrian dipindahkan ke daftar terlambat.', 'Antrian tidak ditemukan.');
    }

    public function recallLate(string $token)
    {
        return $this->updateByToken($token, function (array &$s, int $i): void { $s['queues'][$i]['status'] = 'called'; $s['queues'][$i]['called_at'] = now()->toDateTimeString(); $s['queues'][$i]['late_at'] = null; $s['queues'][$i]['recall_count'] = ((int) ($s['queues'][$i]['recall_count'] ?? 0)) + 1; $s['last_called'] = $s['queues'][$i]; }, 'Antrian terlambat berhasil dipanggil ulang.', 'Antrian terlambat tidak ditemukan.');
    }

    public function board()
    {
        $s = $this->state();
        $d = $this->digest($s);
        return view('antrian.papan', ['summary' => $d['summary'], 'waitingQueues' => $d['waiting'], 'lastCalled' => $s['last_called'], 'audioUrl' => asset('sounds/dingdong.mp3'), 'streamUrl' => route('antrian.sse'), 'speechConfig' => ['lang' => 'id-ID', 'rate' => 0.85]]);
    }

    public function resetAntrian() { Cache::forget(self::KEY); return response()->json(['success' => true, 'message' => 'Data antrian berhasil direset.']); }

    public function stream()
    {
        set_time_limit(0);
        return response()->stream(function (): void {
            ignore_user_abort(true);
            @ob_end_clean();
            ob_implicit_flush(true);
            $lastHash = null;
            session_write_close();
            while (true) {
                if (connection_aborted()) break;
                $s = $this->state();
                $d = $this->digest($s);
                $p = ['version' => $s['version'], 'updated_at' => $s['updated_at'], 'summary' => $d['summary'], 'waiting' => $d['waiting'], 'called' => $d['called'], 'late' => $d['late'], 'last_called' => $s['last_called']];
                $h = sha1(json_encode($p, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                if ($h !== $lastHash) {
                    echo "event: queue-update\n";
                    echo 'data: '.json_encode($p, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n\n";
                    $lastHash = $h;
                    @ob_flush();
                    flush();
                }
                sleep(1);
            }
        }, 200, ['Content-Type' => 'text/event-stream', 'Cache-Control' => 'no-cache', 'X-Accel-Buffering' => 'no']);
    }

    private function state(): array
    {
        $s = Cache::get(self::KEY);
        if (!is_array($s)) $s = ['next_number' => 1, 'queues' => [], 'last_called' => null, 'updated_at' => now()->toDateTimeString(), 'version' => (string) Str::uuid()];
        $s['next_number'] = (int) ($s['next_number'] ?? 1);
        $s['queues'] = is_array($s['queues'] ?? null) ? array_values($s['queues']) : [];
        $s['last_called'] = is_array($s['last_called'] ?? null) ? $s['last_called'] : null;
        $s['updated_at'] = (string) ($s['updated_at'] ?? now()->toDateTimeString());
        $s['version'] = (string) ($s['version'] ?? Str::uuid());
        if (!Cache::has(self::KEY)) {
            // first-time initialization write only
            Cache::forever(self::KEY, $s);
        }
        return $s;
    }

    private function save(array &$s): void { $s['updated_at'] = now()->toDateTimeString(); $s['version'] = (string) Str::uuid(); Cache::forever(self::KEY, $s); }

    private function updateByToken(string $token, callable $callback, string $okMessage, string $notFound)
    {
        $s = $this->state();
        $i = $this->queueIndex($s['queues'], $token);
        if ($i === null) return response()->json(['success' => false, 'message' => $notFound], 404);
        $callback($s, $i);
        $this->save($s);
        return response()->json(['success' => true, 'message' => $okMessage, 'data' => $s['queues'][$i]]);
    }

    // mark a queue item as completed
    public function complete(string $token)
    {
        return $this->updateByToken($token, function (array &$s, int $i): void {
            $s['queues'][$i]['status'] = 'selesai';
            $s['queues'][$i]['finished_at'] = now()->toDateTimeString();
        }, 'Antrian ditandai selesai.', 'Antrian tidak ditemukan.');
    }

    private function digest(array $s): array
    {
        $w = $this->statusList($s['queues'], 'waiting');
        $c = $this->statusList($s['queues'], 'called');
        $l = $this->statusList($s['queues'], 'late');
        return ['waiting' => $w, 'called' => $c, 'late' => $l, 'summary' => ['total' => count($s['queues']), 'waiting' => count($w), 'called' => count($c), 'late' => count($l), 'last_called' => $s['last_called']]];
    }

    private function statusList(array $queues, string $status): array { return array_values(array_filter($queues, fn ($q) => ($q['status'] ?? '') === $status)); }

    private function queueIndex(array $queues, string $token): ?int
    {
        foreach ($queues as $i => $q) if ((string) ($q['token'] ?? '') === $token) return $i;
        return null;
    }
}
