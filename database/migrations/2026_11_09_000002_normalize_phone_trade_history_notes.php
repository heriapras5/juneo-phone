<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('phone_trade_histories')
            ->where('note', 'like', '%->%')
            ->orderBy('id')
            ->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    $note = $this->normalizeNote((string) $row->note);

                    if ($note !== $row->note) {
                        DB::table('phone_trade_histories')
                            ->where('id', $row->id)
                            ->update(['note' => $note]);
                    }
                }
            });
    }

    public function down(): void
    {
        // Riwayat lama tidak dikembalikan karena nilai sebelumnya sudah tidak relevan.
    }

    private function normalizeNote(string $note): string
    {
        return preg_replace_callback(
            '/([A-Za-z][A-Za-z ]*?):\s*Rp\s*[\d\.]+\s*->\s*(Rp\s*[\d\.]+)/u',
            function (array $matches) {
                return trim($matches[1]) . ' diubah menjadi: ' . trim($matches[2]);
            },
            $note
        );
    }
};
