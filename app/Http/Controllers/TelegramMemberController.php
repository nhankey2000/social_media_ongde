<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\TelegramMember;
use App\Services\TelegramMemberService;
use App\Services\TaskAssignmentService;
use Illuminate\Http\Request;

class TelegramMemberController extends Controller
{
    protected TelegramMemberService $memberService;
    protected TaskAssignmentService $taskService;

    public function __construct(
        TelegramMemberService $memberService,
        TaskAssignmentService $taskService
    ) {
        $this->memberService = $memberService;
        $this->taskService = $taskService;
    }

    /**
     * Hiển thị danh sách members của một location
     */
    public function index(Location $location)
    {
        $members = TelegramMember::where('location_id', $location->id)
            ->with('taskAssignments')
            ->get();

        $stats = $this->memberService->getMemberStats($location);

        return view('admin.members.index', compact('location', 'members', 'stats'));
    }

    /**
     * Sync members từ Telegram
     */
    public function sync(Location $location)
    {
        $result = $this->memberService->syncGroupMembers($location);

        if ($result['success']) {
            return back()->with('success', "Đã quét {$result['stats']['total']} thành viên!");
        }

        return back()->with('error', 'Lỗi: ' . $result['error']);
    }

    /**
     * Cập nhật role và keywords của member
     */
    public function update(Request $request, TelegramMember $member)
    {
        $validated = $request->validate([
            'role' => 'nullable|string|max:50',
            'keywords' => 'nullable|array',
            'is_active' => 'boolean'
        ]);

        $member->update($validated);

        return back()->with('success', 'Đã cập nhật thông tin thành viên!');
    }

    /**
     * Xem chi tiết member và tasks của họ
     */
    public function show(TelegramMember $member)
    {
        $member->load(['taskAssignments.report', 'location']);
        $stats = $this->taskService->getMemberTaskStats($member);

        return view('admin.members.show', compact('member', 'stats'));
    }

    /**
     * Test giao việc thủ công
     */
    public function testAssign(Request $request, Location $location)
    {
        $content = $request->input('content', 'Máy POS bị lỗi không in được hóa đơn');

        $relevantMembers = $this->memberService->findRelevantMembers(
            $location,
            $content,
            5
        );

        return response()->json([
            'content' => $content,
            'relevant_members' => $relevantMembers
        ]);
    }

    /**
     * Gửi reminder cho tasks quá hạn
     */
    public function sendReminders(Location $location)
    {
        $count = $this->taskService->sendOverdueReminders($location);

        return back()->with('success', "Đã gửi {$count} nhắc nhở!");
    }

    /**
     * API: Thống kê members theo role
     */
    public function roleStats(Location $location)
    {
        $members = TelegramMember::where('location_id', $location->id)->get();

        $stats = $members->groupBy('role')->map(function($group, $role) {
            return [
                'role' => $role ?? 'Không xác định',
                'count' => $group->count(),
                'active' => $group->where('is_active', true)->count(),
                'members' => $group->pluck('full_name')->toArray()
            ];
        })->values();

        return response()->json($stats);
    }
}