<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AttendanceController extends Controller
{
  public function storeAttendance(Request $request)
    {
        // الدالة تدعم جلسات متعددة خلال اليوم
        // الحالة status: 1 => دخول، 2 => خروج، 3 => منسية

        // 1) جلب بيانات المسؤول
        $admin   = Auth::guard('admin-api')->user();
        $adminId = $admin->id;

        // 2) فك تشفير shift_id وجلب الورديات
       // فك التشفير وفرض النوع array دائمًا
$decoded = json_decode($admin->shift_id, true);

// 2) إذا ما كان الناتج نصًّا، فكّ تشفير ثانية
if (is_string($decoded)) {
    $shiftIds = json_decode($decoded, true) ?: [];
} elseif (is_array($decoded)) {
    $shiftIds = $decoded;
} else {
    $shiftIds = [];
}

// 3) تأكّد من النوع array
$shiftIds = (array)$shiftIds;

// جلب الورديات
$shifts = Shift::whereIn('id', $shiftIds)->get();
// الآن $shiftIds إمَّا [] أو [1] أو [1,2,3] حسب المحتوى
$shifts   = Shift::whereIn('id', $shiftIds)->get();

        if ($shifts->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'لا توجد ورديات مخصصة لهذا المسؤول.'
            ], 400);
        }

        $now   = Carbon::now();
        $today = $now->toDateString();

        // 3) اختيار الوردية المناسبة (قبل البدء أو ضمن الفترة المسموح بها للخروج)
        $selectedShift = null;
        foreach ($shifts as $shift) {
            $start = Carbon::createFromFormat('H:i:s', $shift->start)
                          ->setDate($now->year, $now->month, $now->day);
            $end   = Carbon::createFromFormat('H:i:s', $shift->end)
                          ->setDate($now->year, $now->month, $now->day);

            // إذا امتدت الورديّة بعد منتصف الليل
            if ($end->lte($start)) {
                $end->addDay();
            }

            // نهاية الفترة المسموح بالخروج = نهاية الورديّة + فترة السماح
            $thresholdEnd = (clone $end)->addMinutes($shift->max_minutes);

            if ($now->lt($start) || $now->between($start, $thresholdEnd)) {
                $selectedShift = (object)[
                    'model'        => $shift,
                    'start'        => $start,
                    'end'          => $end,
                    'thresholdEnd' => $thresholdEnd,
                    'expectedHrs'  => $start->diffInHours($end),
                ];
                break;
            }
        }

        if (! $selectedShift) {
            return response()->json([
                'success' => false,
                'message' => 'أنت خارج نافذة أي من وردياتك المحددة.'
            ], 400);
        }

        $S     = $selectedShift;
        $shift = $S->model;

        // 4) معالجة بناءً على الحالة الرقمية
        $status = intval($request->status);

        // حالة الدخول (1)
        if ($status === 1) {
            // البحث عن جلسة مفتوحة
            $open = Attendance::where('admin_id', $adminId)
                ->whereDate('date', $today)
                ->whereNull('check_out')
                ->latest()
                ->first();

            if ($open) {
                // إذا انتهت فترة السماح
                if ($now->gt($S->thresholdEnd)) {
                    // نعتبر الجلسة السابقة منسية
                    $open->update([
                        'status' => 3,
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'لديك جلسة مفتوحة بالفعل.',
                        'data'    => $open,
                    ], 409);
                }
            }

            // وقت تسجيل الدخول
            $checkInTime = $now;
            // حساب time_late: إذا جاء قبل بداية الوردية => 0
            // وإلا الفرق بين وقت الدخول ووقت البداية
            $timeLate = $checkInTime->gt($S->start)
                ? $checkInTime->diffInMinutes($S->start)
                : 0;

            // إنشاء تسجيل دخول جديد
            $attendance = Attendance::create([
                'admin_id'     => $adminId,
                'date'         => $today,
                'check_in'     => $checkInTime,
                'lang'          => $request->lon,
                'late'          => $request->lat,
                'status'       => 1,
                'expected_hours'=> $S->expectedHrs,
                'time_late'    => $timeLate,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم تسجيل الدخول بنجاح.',
                'data'    => $attendance,
            ], 201);
        }

        // حالة الخروج (2)
        if ($status === 2) {
            $open = Attendance::where('admin_id', $adminId)
                ->whereDate('date', $today)
                ->whereNull('check_out')
                ->latest()
                ->first();

            if (! $open) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يوجد جلسة مفتوحة للخروج.',
                ], 404);
            }

            // إذا انتهت فترة السماح للخروج
            if ($now->gt($S->thresholdEnd)) {
                $open->update([
                    'status' => 3,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'انتهت فترة الخروج المسموح بها. تم اعتباره منسيًا.',
                    'data'    => $open,
                ], 200);
            }

            // حساب دقائق العمل
            $workedMinutes = Carbon::parse($open->check_in)->diffInMinutes($now);
            $open->update([
                'check_out'    => $now,
                'status'       => 2,
                'worked_hours' => round($workedMinutes / 60, 2),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم تسجيل الخروج بنجاح.',
                'data'    => $open,
            ], 200);
        }

        // حالة غير صالحة
        return response()->json([
            'success' => false,
            'message' => 'حالة غير صحيحة. استخدم 1 للدخول أو 2 للخروج.',
        ], 400);
    }
    /**
     * Display all attendance records.
     *
     * This method retrieves all attendance records, ordered by date in descending order.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function indexAttendance(Request $request)
    {
        try {
                $adminId = Auth::guard('admin-api')->user()->id;

            // Retrieve all attendance records ordered by date descending
            $records = Attendance::where('admin_id',$adminId)->orderBy('date', 'desc')->get();

            return response()->json([
                'success' => true,
                'data'    => $records
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving attendance records: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving attendance records.'
            ], 500);
        }
    }
}
