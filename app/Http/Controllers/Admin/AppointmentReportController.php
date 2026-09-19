<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AppointmentReportRequest;
use App\Models\Professional;
use App\Services\Reports\AppointmentReportService;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class AppointmentReportController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('verified'),
            new Middleware('admin'),
        ];
    }

    public function index(
        AppointmentReportRequest $request,
        AppointmentReportService $reportService,
    ): View {
        $report = $reportService->build($request->filters());

        $professionals = Professional::query()
            ->with('user')
            ->get()
            ->sortBy(fn (Professional $professional): string => strtolower($professional->user?->name ?? ''))
            ->values();

        return view('admin.reports.appointments.index', [
            'report' => $report,
            'filters' => $report['filters'],
            'professionals' => $professionals,
        ]);
    }

    public function export(
        AppointmentReportRequest $request,
        AppointmentReportService $reportService,
    ): StreamedResponse {
        $report = $reportService->build($request->filters());
        $filename = 'appointments-kit-appointment-report-'.now('UTC')->format('Ymd_His').'.csv';

        return Response::streamDownload(function () use ($report): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                __('reports.csv.headers.appointment_id'),
                __('reports.csv.headers.patient_name'),
                __('reports.csv.headers.patient_email'),
                __('reports.csv.headers.professional_name'),
                __('reports.csv.headers.professional_email'),
                __('reports.csv.headers.session_type'),
                __('reports.csv.headers.starts_at_local'),
                __('reports.csv.headers.ends_at_local'),
                __('reports.csv.headers.starts_at_utc'),
                __('reports.csv.headers.ends_at_utc'),
                __('reports.csv.headers.appointment_status'),
                __('reports.csv.headers.payment_status'),
                __('reports.csv.headers.paid_at_local'),
                __('reports.csv.headers.paid_at_utc'),
                __('reports.csv.headers.price'),
                __('reports.csv.headers.currency'),
                __('reports.csv.headers.estimated_amount'),
                __('reports.csv.headers.collected_amount'),
                __('reports.csv.headers.pending_amount'),
            ]);

            foreach ($report['rows'] as $row) {
                fputcsv($handle, [
                    $row['appointment_id'],
                    $row['patient_name'],
                    $row['patient_email'],
                    $row['professional_name'],
                    $row['professional_email'],
                    $row['session_type_name'],
                    $row['starts_at_display'],
                    $row['ends_at_display'],
                    $row['starts_at_utc'],
                    $row['ends_at_utc'],
                    __('reports.status.appointment.'.$row['status']),
                    __('reports.status.payment.'.$row['payment_status']),
                    $row['paid_at_display'] ?? '',
                    $row['paid_at_utc'] ?? '',
                    number_format((float) $row['price'], 2, '.', ''),
                    $row['currency'],
                    number_format((float) $row['estimated_amount'], 2, '.', ''),
                    number_format((float) $row['collected_amount'], 2, '.', ''),
                    number_format((float) $row['pending_amount'], 2, '.', ''),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
