<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        // 1. Validamos y obtenemos mes y año. 
        $month = (int) $request->get('month', date('n'));
        $year = (int) $request->get('year', date('Y'));

        // 2. Creamos la fecha base siempre en el día 1 para evitar errores de desbordamiento
        $date = Carbon::createFromDate($year, $month, 1);

        // 3. Calculamos navegación usando copias limpias
        $prevDate = $date->copy()->subMonth();
        $nextDate = $date->copy()->addMonth();

        $data = [
            'currentMonth'     => $date->month,
            'currentMonthName' => $date->translatedFormat('F'),
            'currentYear'      => $date->year,
            'daysInMonth'      => $date->daysInMonth,
            'firstDayOfWeek'   => $date->startOfMonth()->dayOfWeek,
            
            // Navegación exacta para los botones superiores
            'prevMonth'        => $prevDate->month,
            'prevYear'         => $prevDate->year,
            'nextMonth'        => $nextDate->month,
            'nextYear'         => $nextDate->year,
            
            'today'            => Carbon::now()->day,
            'isCurrentMonth'   => ($date->isSameMonth(Carbon::now())),
            'years'            => range(Carbon::now()->year - 5, Carbon::now()->year + 5),
        ];

        // --- CAMBIO CLAVE AQUÍ ---
        if ($request->ajax()) {
            return response()->json([
                // Enviamos el HTML del calendario
                'html' => view('components.newcalender', compact('data'))->render(),
                // Enviamos los nuevos datos de navegación para "reprogramar" los botones externos
                'nextMonth' => $data['nextMonth'],
                'nextYear'  => $data['nextYear'],
                'prevMonth' => $data['prevMonth'],
                'prevYear'  => $data['prevYear'],
                'currentMonthName' => ucfirst($data['currentMonthName']) . ' ' . $data['currentYear']
            ]);
        }

        return view('calendar.index', compact('data'));
    }
}