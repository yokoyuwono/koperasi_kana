<?php

namespace App\Http\Controllers;

use App\Models\Komisi;
use App\Models\Deposit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class KomisiReportController extends Controller
{
    private function ensureAdmin(): void
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') abort(403);
    }

    private function computeRange(string $month, string $periode): array
    {
        $base = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $startOfMonth = $base->copy()->startOfMonth();
        $endOfMonth   = $base->copy()->endOfMonth();

        $lastDayPrevMonth = $startOfMonth->copy()->subDay(); // last day previous month
        $lastDayMinus1    = $endOfMonth->copy()->subDay();   // H-1 last day current month

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

        return [
            $lastDayPrevMonth->toDateString(),
            $lastDayMinus1->toDateString(),
        ];
    }

    /**
     * Query BARU: 1 row = 1 komisi (komisi.id)
     * Filter: deposit approved + komisi.status approved + range pada komisi.tanggal_periode
     */
    private function buildQuery(string $month, string $periode)
    {
        [$start, $end] = $this->computeRange($month, $periode);

        return Komisi::query()
            ->join('deposits', 'deposits.id', '=', 'komisi.id_deposit')
            ->join('agents', 'agents.id', '=', 'komisi.id_agent')
            ->where('deposits.status', 'approved')
            ->where('komisi.status', 'approved')
            ->whereBetween('komisi.tanggal_periode', [$start, $end])
            ->select([
                'komisi.id as komisi_id',
                'komisi.persen_komisi',
                'komisi.nominal',
                'komisi.tanggal_periode',
                'komisi.tanggal_pembayaran',

                'agents.kode_agent',
                'agents.nama as agent_nama',
                'agents.jabatan',

                // asumsi kolom kode bliyet ada di deposits
                'deposits.no_bilyet',

                // Jenis Komisi (dibuat via CASE)
                DB::raw("
                    CASE
                        WHEN komisi.persen_komisi = 0.5 THEN 'BDP Ref'
                        WHEN agents.jabatan = 'RM' THEN 'RM'
                        WHEN agents.jabatan = 'BDP' AND komisi.id_agent = deposits.id_agent THEN 'BDP'
                        ELSE 'BDPgetBDP'
                    END as jenis_komisi
                "),

                DB::raw("CASE WHEN komisi.tanggal_pembayaran IS NULL THEN 'UNPAID' ELSE 'PAID' END as status_pembayaran"),
            ])
            ->orderByDesc('komisi.id');
    }

    public function index(Request $request)
    {
        $this->ensureAdmin();

        $month   = $request->query('month', now()->format('Y-m'));
        $periode = $request->query('periode', 'all');

        [$start, $end] = $this->computeRange($month, $periode);

        // pagination (misal 25 row per halaman)
        $rows = $this->buildQuery($month, $periode)
            ->paginate(25)
            ->withQueryString();

        return view('komisi_report', compact('rows', 'month', 'periode', 'start', 'end'));
    }

    /**
     * PAY BARU: update tanggal_pembayaran per komisi.id (bukan agent)
     */
    public function pay(Request $request)
    {
        $this->ensureAdmin();

        $data = $request->validate([
            'month'   => 'required|string',
            'periode' => 'required|string',
            'tanggal_pembayaran' => 'required|date',

            'komisi_ids' => 'required|array|min:1',
            'komisi_ids.*' => 'integer',
        ]);

        [$start, $end] = $this->computeRange($data['month'], $data['periode']);

        $affected = Komisi::query()
            ->join('deposits', 'deposits.id', '=', 'komisi.id_deposit')
            ->where('deposits.status', 'approved')
            ->where('komisi.status', 'approved')
            ->whereBetween('komisi.tanggal_periode', [$start, $end])
            ->whereIn('komisi.id', $data['komisi_ids'])
            ->whereNull('komisi.tanggal_pembayaran') // biar tidak overwrite yg sudah paid
            ->update([
                'komisi.tanggal_pembayaran' => $data['tanggal_pembayaran'],
            ]);

        return redirect()->route('komisi.report', [
            'month' => $data['month'],
            'periode' => $data['periode'],
        ])->with('success', "Pembayaran berhasil diproses. Updated: {$affected} komisi.");
    }

    /**
     * EXPORT BARU: per komisi.id (ikut kolom tabel baru)
     */
    public function export(Request $request)
    {
        $this->ensureAdmin();

        $month   = $request->query('month', now()->format('Y-m'));
        $periode = $request->query('periode', 'all');

        [$start, $end] = $this->computeRange($month, $periode);

        $rows = $this->buildQuery($month, $periode)->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Komisi');

        $sheet->fromArray([
            ['Periode Bulan', $month],
            ['Range Tanggal Periode', $start . ' s/d ' . $end],
            ['Periode', $periode === 'all' ? 'Semua' : 'Periode ' . $periode],
        ], null, 'A1');

        $sheet->fromArray([[
            'ID Komisi',
            'Kode Agent',
            'Nama Agent',
            'Jabatan',
            'Jenis Komisi',
            'Kode Bliyet',
            'Tanggal Periode',
            'Nominal',
            'Tanggal Pembayaran',
            'Status Pembayaran',
        ]], null, 'A5');

        $data = [];
        foreach ($rows as $r) {
            $data[] = [
                (int) $r->komisi_id,
                $r->kode_agent,
                $r->agent_nama,
                $r->jabatan,
                $r->jenis_komisi,
                $r->no_bilyet,
                $r->tanggal_periode ? \Carbon\Carbon::parse($r->tanggal_periode)->format('d-m-Y') : '',
                (float) $r->nominal,
                $r->tanggal_pembayaran ? \Carbon\Carbon::parse($r->tanggal_pembayaran)->format('d-m-Y') : '',
                $r->status_pembayaran,
            ];
        }

        $sheet->fromArray($data, null, 'A6');

        foreach (range('A','J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'komisi_report_' . $month . '_periode_' . $periode . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
 
        $headerRow = 5;
        $dataStartRow = 6;
        $dataEndRow = $dataStartRow + max(count($data) - 1, 0);
        $lastCol = 'J'; // karena kolom A..J (10 kolom)

        // 1) Rapikan bagian info di atas
        // $sheet->mergeCells('A1:J1');
        // $sheet->mergeCells('A2:J2');
        // $sheet->mergeCells('A3:J3');

        $sheet->getStyle('A1:A3')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getFont()->setSize(14);

        $sheet->getStyle('A1:A3')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getRowDimension(1)->setRowHeight(24);

        // 2) Styling header tabel
        $headerRange = "A{$headerRow}:{$lastCol}{$headerRow}";
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '0F172A'], // slate-900
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E2E8F0'], // slate-200
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CBD5E1'], // slate-300
                ],
            ],
        ]);

        $sheet->getRowDimension($headerRow)->setRowHeight(20);

        // 3) Freeze pane supaya header tetap terlihat
        $sheet->freezePane("A{$dataStartRow}");

        // 4) AutoFilter
        $sheet->setAutoFilter("A{$headerRow}:{$lastCol}{$dataEndRow}");

        // 5) Format kolom (tanggal & nominal)
        if ($dataEndRow >= $dataStartRow) {
            // Nominal (kolom H)
            $sheet->getStyle("H{$dataStartRow}:H{$dataEndRow}")
                ->getNumberFormat()
                ->setFormatCode('#,##0'); // atau 'Rp #,##0' kalau mau teks Rp di excel

            // Tanggal Periode (kolom G) dan Tanggal Pembayaran (kolom I)
            // Karena kamu sudah kirim string "dd-mm-yyyy", Excel bisa treat sebagai text.
            // Kalau kamu ingin tetap text, skip format number.
            // Kalau ingin date asli Excel, kita bisa convert ke serial date (step lanjut).
            $sheet->getStyle("G{$dataStartRow}:G{$dataEndRow}")
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->getStyle("I{$dataStartRow}:I{$dataEndRow}")
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // ID komisi (kolom A) center
            $sheet->getStyle("A{$dataStartRow}:A{$dataEndRow}")
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Status pembayaran (kolom J) center
            $sheet->getStyle("J{$dataStartRow}:J{$dataEndRow}")
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // 6) Border seluruh tabel
        $tableRange = "A{$headerRow}:{$lastCol}{$dataEndRow}";
        $sheet->getStyle($tableRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($tableRange)->getBorders()->getAllBorders()->getColor()->setRGB('E2E8F0');

        // 7) Zebra rows (opsional, tapi enak dibaca)
        if ($dataEndRow >= $dataStartRow) {
            for ($r = $dataStartRow; $r <= $dataEndRow; $r++) {
                if (($r - $dataStartRow) % 2 === 1) {
                    $sheet->getStyle("A{$r}:{$lastCol}{$r}")->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'F8FAFC'], // slate-50
                        ],
                    ]);
                }
            }
        }

        // 8) Lebar kolom yang nyaman (lebih baik daripada autosize semua)
        $sheet->getColumnDimension('A')->setWidth(10); // ID Komisi
        $sheet->getColumnDimension('B')->setWidth(14); // Kode Agent
        $sheet->getColumnDimension('C')->setWidth(22); // Nama Agent
        $sheet->getColumnDimension('D')->setWidth(10); // Jabatan
        $sheet->getColumnDimension('E')->setWidth(14); // Jenis Komisi
        $sheet->getColumnDimension('F')->setWidth(18); // Kode Bliyet
        $sheet->getColumnDimension('G')->setWidth(14); // Tanggal Periode
        $sheet->getColumnDimension('H')->setWidth(16); // Nominal
        $sheet->getColumnDimension('I')->setWidth(16); // Tgl Pembayaran
        $sheet->getColumnDimension('J')->setWidth(16); // Status

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
