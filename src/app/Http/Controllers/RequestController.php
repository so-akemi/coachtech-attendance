<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Models\AttendanceCorrectRequest;
use App\Models\RestCorrectRequest;
use App\Http\Requests\CorrectionRequest;


class RequestController extends Controller
{
    public function index(Request $request)
    {

        $statusParam = $request->query('status', 'pending');
        $statusValue = ($statusParam === 'approved') ? 1 : 0;

        $query = AttendanceCorrectRequest::with('user')
            ->where('user_id', auth()->id())
            ->where('status', $statusValue);

        $requests = AttendanceCorrectRequest::where('user_id', auth()->id())
            ->where('status', $statusValue)
            ->latest()
            ->get();

        return view('requests.index', compact('requests', 'statusParam'));

    }

    public function store(CorrectionRequest $request, $id)
    {

        $attendance = \App\Models\Attendance::findOrFail($id);
        $date = \Carbon\Carbon::parse($attendance->date)->format('Y-m-d');

        $correctRequest = AttendanceCorrectRequest::create([
            'user_id'       => Auth::id(),
            'attendance_id' => $id,
            'start_time'    => $date . ' ' . $request->start_time,
            'end_time'      => $date . ' ' . $request->end_time,
            'reason'        => $request->note,
            'status'        => 0,
        ]);

        if ($request->has('rests')) {
            foreach ($request->rests as $restData) {
                if (!empty($restData['start_time']) && !empty($restData['end_time'])) {
                    $correctRequest->restCorrectRequests()->create([
                        'start_time' => $date . ' ' . $restData['start_time'],
                        'end_time'   => $date . ' ' . $restData['end_time'],
                    ]);
                }
            }
        }

        if ($request->has('new_rests')) {
            foreach ($request->new_rests as $newData) {
                if (!empty($newData['start_time']) && !empty($newData['end_time'])) {
                    $correctRequest->restCorrectRequests()->create([
                        'start_time' => $date . ' ' . $newData['start_time'],
                        'end_time'   => $date . ' ' . $newData['end_time'],
                    ]);
                }
            }
        }


        return redirect()->route('request.index')->with('success', '修正申請を出しました');
    }
}
