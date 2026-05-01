<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        // Obtener mes y año de la URL, o usar los actuales por defecto
        $month = $request->get('month', Carbon::now()->month);
        $year = $request->get('year', Carbon::now()->year);

        // Crear objeto Carbon para el primer día del mes seleccionado
        $date = Carbon::createFromDate($year, $month, 1);

        // Datos para la navegación
        $data = [
            'currentMonth'     => $date->month, // Número del mes (1-12)
            'currentMonthName' => $date->translatedFormat('F'), // Enero, Febrero, etc.
            'currentYear'      => $date->year,
            'daysInMonth'      => $date->daysInMonth,
            'firstDayOfWeek'   => $date->dayOfWeek, // 0 (Dom) a 6 (Sáb)
            'prevMonth'        => (clone $date)->subMonth()->month,
            'prevYear'         => (clone $date)->subMonth()->year,
            'nextMonth'        => (clone $date)->addMonth()->month,
            'nextYear'         => (clone $date)->addMonth()->year,
            'today'            => Carbon::now()->day,
            'isCurrentMonth'   => ($date->month == Carbon::now()->month && $date->year == Carbon::now()->year),
            'years'            => range(Carbon::now()->year - 5, Carbon::now()->year + 5), // Rango de años para el dropdown
        ];

        if ($request->ajax()) {
            // Si es una petición AJAX, devolver solo el HTML del calendario
            return view('components.newcalender', compact('data'))->render();
        }

        return view('calendar.index', compact('data'));
    }
}