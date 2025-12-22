<?php

namespace App\Http\Controllers;

use App\Models\Komisi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KomisiReportController extends Controller
{
    private function ensureAdmin(): void
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') abort(403);
    }

    private function computeRange(string $month, string $periode): array
    {
        // $month format: YYYY-MM
        $base = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $startOfMonth = $base->copy()->startOfMonth();
        $endOfMonth   = $base->copy()->endOfMonth();

        $lastDayPrevMonth = $startOfMonth->copy()->subDay();          // last day previous month
        $lastDayMinus1    = $endOfMonth->copy()->subDay();            // H-1 last day current month

        if ($periode === '1') {
            return [
                $lastDayPrevMonth->toDateString(),
                $startOfMonth->copy()->day(14)->toDateString(),
            ];
        }

        if ($periode === '2') {
            return [
                $startOfMonth->copy()->day(15)->toDateString(),
                $lastDayMinus1->toDateString(),
            ];
        }

        // all: gabungkan 2 periode di bulan itu (tetap exclude last day current month)
        return [
            $lastDayPrevMonth->toDateString(),
            $lastDayMinus1->toDateString(),
        ];
    }

    public function pay(Request $request)
    {
        $this->ensureAdmin();

        $data = $request->validate([
            'month'   => 'required|string',        // format YYYY-MM
            'periode' => 'required|string',        // all|1|2
            'tanggal_pembayaran' => 'required|date',
            'agent_ids' => 'required|array|min:1',
            'agent_ids.*' => 'integer',
        ]);

        [$start, $end] = $this->computeRange($data['month'], $data['periode']);

        // Update komisi yang termasuk hasil filter (approved deposit + range approval)
        // dan hanya agent yang dicentang.
        $affected = Komisi::query()
            ->join('deposits', 'deposits.id', '=', 'komisi.id_deposit')
            ->where('deposits.status', 'approved')
            ->whereBetween('deposits.tanggal_approval', [$start, $end])
            ->whereIn('komisi.id_agent', $data['agent_ids'])
            ->where('komisi.status', 'approved') // aman: hanya komisi approved yang dibayar
            ->whereNull('komisi.tanggal_pembayaran') // biar gak overwrite yang sudah paid
            ->update([
                'komisi.tanggal_pembayaran' => $data['tanggal_pembayaran'],
            ]);

        return redirect()->route('komisi.report', [
            'month' => $data['month'],
            'periode' => $data['periode'],
        ])->with('success', "Pembayaran berhasil diproses. Updated: {$affected} komisi.");
    }

    private function buildQuery(string $month, string $periode)
    {
        [$start, $end] = $this->computeRange($month, $periode);

        // IMPORTANT:
        // - asumsi tanggal approval deposit disimpan di deposits.tanggal_approval
        // - deposit status approved
        // - komisi.status minimal 'approved' / atau data komisi sudah final saat deposit approved
        return Komisi::query()
            ->join('deposits', 'deposits.id', '=', 'komisi.id_deposit')
            ->join('agents', 'agents.id', '=', 'komisi.id_agent')
            ->where('deposits.status', 'approved')
            ->whereBetween('deposits.tanggal_approval', [$start, $end])
            ->select([
                'agents.id as agent_id',
                'agents.kode_agent',
                'agents.nama as agent_nama',
                'agents.jabatan',
                DB::raw('COUNT(DISTINCT deposits.id) as jumlah_deposit'),
                DB::raw('SUM(komisi.nominal) as total_komisi'),
                DB::raw(value: "MIN(deposits.tanggal_approval) as approval_min"),
                DB::raw("MAX(deposits.tanggal_approval) as approval_max"),
                 // ✅ status pembayaran (per agent, per range)
                DB::raw("SUM(CASE WHEN komisi.tanggal_pembayaran IS NULL THEN 1 ELSE 0 END) as unpaid_count"),
                DB::raw("MAX(komisi.tanggal_pembayaran) as last_paid_at"),
            ])
            ->groupBy('agents.id', 'agents.kode_agent', 'agents.nama', 'agents.jabatan')
            ->orderBy('agents.nama', 'asc');
    }

    public function index(Request $request)
    {
        $this->ensureAdmin();

        $month   = $request->query('month', now()->format('Y-m'));
        $periode = $request->query('periode', 'all'); // all|1|2

        [$start, $end] = $this->computeRange($month, $periode);

        $rows = $this->buildQuery($month, $periode)->get();

        return view('komisi_report', compact('rows', 'month', 'periode', 'start', 'end'));
    }

    public function export(Request $request)
    {
        $this->ensureAdmin();

        $month   = $request->query('month', now()->format('Y-m'));
        $periode = $request->query('periode', 'all');

        [$start, $end] = $this->computeRange($month, $periode);

        $rows = $this->buildQuery($month, $periode)->get();

        // ===== Export XLSX (PhpSpreadsheet) =====
        // Install: composer require phpoffice/phpspreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Komisi');

        $sheet->fromArray([
            ['Periode Bulan', $month],
            ['Range Approval COA', $start . ' s/d ' . $end],
            ['Periode', $periode === 'all' ? 'Semua' : 'Periode ' . $periode],
        ], null, 'A1');

        $sheet->fromArray([
            ['Kode Agent','Nama Agent','Jabatan','Jumlah Deposit','Total Komisi','Tanggal Approval']
        ], null, 'A5');

        $data = [];
        foreach ($rows as $r) {
            $data[] = [
                $r->kode_agent,
                $r->agent_nama,
                $r->jabatan,
                (int) $r->jumlah_deposit,
                (float) $r->total_komisi,
                $r->approval_min,
            ];
        }

        $sheet->fromArray($data, null, 'A6');

        // simple autosize
        foreach (range('A','G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'komisi_report_' . $month . '_periode_' . $periode . '.xlsx';

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
