<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\invoices;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $invoices = invoices::all();

        $count_all = invoices::count();
        $count_invoices1 = invoices::where('Value_Status', 1)->count();
        $count_invoices2 = invoices::where('Value_Status', 2)->count();
        $count_invoices3 = invoices::where('Value_Status', 3)->count();

        if ($count_invoices1 == 0) {
            $nspainvoices1 = 0;
        } else {
            $nspainvoices1 = $count_invoices1 / $count_all * 100;
        }
        if ($count_invoices2 == 0) {
            $nspainvoices2 = 0;
        } else {
            $nspainvoices2 = $count_invoices2 / $count_all * 100;
        }
        if ($count_invoices3 == 0) {
            $nspainvoices3 = 0;
        } else {
            $nspainvoices3 = $count_invoices3 / $count_all * 100;
        }
        $chartjs_1 = app()->chartjs
            ->name('barChartTest')
            ->type('bar')
            ->size(['width' => 450, 'height' => 200])
            ->labels(['الفواتير المدفوعة', 'الفواتير الغير المدفوعة', 'الفواتير المدفوعة جزئيا'])
            ->datasets([
                [
                    "label" => "نسبة الفواتير",
                    'backgroundColor' => "rgba(0, 0,255,0.75)",
                    'borderColor' => "rgba(0, 0, 255)",
                    'data' => [$nspainvoices1, $nspainvoices2, $nspainvoices3]
                ]
            ])
            ->options([]);

        $chartjs_2 = app()->chartjs
            ->name('pieChartTest')
            ->type('pie')
            ->size(['width' => 340, 'height' => 200])
            ->labels(['الفواتير المدفوعة', 'الفواتير الغير المدفوعة', 'الفواتير المدفوعة جزئيا'])
            ->datasets([
                [
                    'backgroundColor' => ['#81b214', '#ec5858', '#ff9642'],
                    'data' => [$nspainvoices1, $nspainvoices2, $nspainvoices3]
                ]
            ])
            ->options([]);

        return view('home', compact('invoices', 'chartjs_1', 'chartjs_2'));
    }
}
