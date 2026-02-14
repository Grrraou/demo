<?php

namespace App\Livewire\Calendar;

use App\Models\Event;
use App\Models\TeamMember;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class CalendarView extends Component
{
    public int $year;
    public int $month;
    public array $events = [];

    // Event form
    public bool $showEventModal = false;
    public bool $isEditing = false;
    public ?int $editingEventId = null;
    public string $title = '';
    public string $description = '';
    public string $startDate = '';
    public string $startTime = '09:00';
    public string $endDate = '';
    public string $endTime = '10:00';
    public bool $allDay = false;
    public string $color = 'blue';
    public array $selectedParticipants = [];
    public bool $createConversation = false;

    // Event detail view
    public bool $showEventDetail = false;
    public ?array $selectedEvent = null;

    public function mount(): void
    {
        $this->year = now()->year;
        $this->month = now()->month;
        $this->loadEvents();
    }

    public function loadEvents(): void
    {
        $companyId = session('current_owned_company_id');
        if (!$companyId) {
            $this->events = [];
            return;
        }

        $userId = Auth::id();
        $startOfMonth = Carbon::create($this->year, $this->month, 1)->startOfMonth()->subDays(7);
        $endOfMonth = Carbon::create($this->year, $this->month, 1)->endOfMonth()->addDays(7);

        $events = Event::where('owned_company_id', $companyId)
            ->where(function ($q) use ($userId) {
                $q->where('created_by', $userId)
                    ->orWhereHas('participants', fn($q) => $q->where('team_member_id', $userId));
            })
            ->where('start_at', '>=', $startOfMonth)
            ->where('start_at', '<=', $endOfMonth)
            ->with(['creator:id,name', 'participants:id,name', 'conversation:id'])
            ->get();

        $this->events = $events->map(fn(Event $e) => [
            'id' => $e->id,
            'title' => $e->title,
            'description' => $e->description,
            'type' => $e->type,
            'start_at' => $e->start_at->toIso8601String(),
            'end_at' => $e->end_at?->toIso8601String(),
            'all_day' => $e->all_day,
            'color' => $e->color ?? 'blue',
            'color_hex' => $e->color_hex,
            'creator_id' => $e->created_by,
            'creator_name' => $e->creator?->name ?? 'Unknown',
            'is_creator' => $e->isCreator($userId),
            'conversation_id' => $e->conversation_id,
            'participants' => $e->participants->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'status' => $p->pivot->status,
            ])->toArray(),
            'my_status' => $e->participants->firstWhere('id', $userId)?->pivot->status,
        ])->toArray();
    }

    public function previousMonth(): void
    {
        $date = Carbon::create($this->year, $this->month, 1)->subMonth();
        $this->year = $date->year;
        $this->month = $date->month;
        $this->loadEvents();
    }

    public function nextMonth(): void
    {
        $date = Carbon::create($this->year, $this->month, 1)->addMonth();
        $this->year = $date->year;
        $this->month = $date->month;
        $this->loadEvents();
    }

    public function goToToday(): void
    {
        $this->year = now()->year;
        $this->month = now()->month;
        $this->loadEvents();
    }

    public function openCreateModal(?string $date = null): void
    {
        $this->resetForm();
        $this->startDate = $date ?? now()->format('Y-m-d');
        $this->endDate = $this->startDate;
        $this->showEventModal = true;
    }

    public function openEditModal(int $eventId): void
    {
        $event = Event::with('participants')->find($eventId);
        if (!$event || !$event->canEdit(Auth::id())) {
            return;
        }

        $this->isEditing = true;
        $this->editingEventId = $eventId;
        $this->title = $event->title;
        $this->description = $event->description ?? '';
        $this->startDate = $event->start_at->format('Y-m-d');
        $this->startTime = $event->start_at->format('H:i');
        $this->endDate = $event->end_at?->format('Y-m-d') ?? $this->startDate;
        $this->endTime = $event->end_at?->format('H:i') ?? '10:00';
        $this->allDay = $event->all_day;
        $this->color = $event->color ?? 'blue';
        $this->selectedParticipants = $event->participants->pluck('id')->toArray();
        $this->createConversation = (bool) $event->conversation_id;
        $this->showEventDetail = false;
        $this->showEventModal = true;
    }

    public function closeEventModal(): void
    {
        $this->showEventModal = false;
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->isEditing = false;
        $this->editingEventId = null;
        $this->title = '';
        $this->description = '';
        $this->startDate = '';
        $this->startTime = '09:00';
        $this->endDate = '';
        $this->endTime = '10:00';
        $this->allDay = false;
        $this->color = 'blue';
        $this->selectedParticipants = [];
        $this->createConversation = false;
    }

    public function saveEvent(): void
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'startDate' => 'required|date',
            'endDate' => 'nullable|date|after_or_equal:startDate',
            'color' => 'required|string|in:' . implode(',', array_keys(Event::COLORS)),
        ]);

        $companyId = session('current_owned_company_id');
        if (!$companyId) return;

        $startAt = $this->allDay
            ? Carbon::parse($this->startDate)->startOfDay()
            : Carbon::parse("{$this->startDate} {$this->startTime}");

        $endAt = null;
        if ($this->endDate) {
            $endAt = $this->allDay
                ? Carbon::parse($this->endDate)->endOfDay()
                : Carbon::parse("{$this->endDate} {$this->endTime}");
        }

        if ($this->isEditing && $this->editingEventId) {
            $event = Event::find($this->editingEventId);
            if (!$event || !$event->canEdit(Auth::id())) return;

            $event->update([
                'title' => $this->title,
                'description' => $this->description ?: null,
                'start_at' => $startAt,
                'end_at' => $endAt,
                'all_day' => $this->allDay,
                'color' => $this->color,
            ]);

            // Sync participants
            $currentIds = $event->participants->pluck('id')->toArray();
            $newIds = array_diff($this->selectedParticipants, $currentIds);
            $removeIds = array_diff($currentIds, $this->selectedParticipants);

            if (!empty($removeIds)) {
                $event->participants()->detach($removeIds);
            }
            if (!empty($newIds)) {
                $event->invite($newIds);
            }
        } else {
            $event = Event::create([
                'owned_company_id' => $companyId,
                'title' => $this->title,
                'description' => $this->description ?: null,
                'type' => Event::TYPE_USER,
                'start_at' => $startAt,
                'end_at' => $endAt,
                'all_day' => $this->allDay,
                'color' => $this->color,
                'created_by' => Auth::id(),
            ]);

            if (!empty($this->selectedParticipants)) {
                $event->invite($this->selectedParticipants);
            }
        }

        // Create conversation if requested
        if ($this->createConversation && !$event->conversation_id) {
            $event->createLinkedConversation();
        }

        $this->closeEventModal();
        $this->loadEvents();
    }

    public function deleteEvent(): void
    {
        if (!$this->editingEventId) return;

        $event = Event::find($this->editingEventId);
        if (!$event || !$event->canEdit(Auth::id())) return;

        $event->delete();
        $this->closeEventModal();
        $this->loadEvents();
    }

    public function viewEvent(int $eventId): void
    {
        $event = collect($this->events)->firstWhere('id', $eventId);
        if ($event) {
            $this->selectedEvent = $event;
            $this->showEventDetail = true;
        }
    }

    public function closeEventDetail(): void
    {
        $this->showEventDetail = false;
        $this->selectedEvent = null;
    }

    public function respondToEvent(string $status): void
    {
        if (!$this->selectedEvent) return;

        $event = Event::find($this->selectedEvent['id']);
        if (!$event) return;

        $event->respond(Auth::id(), $status);
        $this->loadEvents();

        // Update selected event
        $this->selectedEvent = collect($this->events)->firstWhere('id', $this->selectedEvent['id']);
    }

    public function toggleParticipant(int $id): void
    {
        if (in_array($id, $this->selectedParticipants)) {
            $this->selectedParticipants = array_values(array_diff($this->selectedParticipants, [$id]));
        } else {
            $this->selectedParticipants[] = $id;
        }
    }

    public function getTeamMembersProperty()
    {
        return TeamMember::where('id', '!=', Auth::id())
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function getCalendarDaysProperty(): array
    {
        $firstOfMonth = Carbon::create($this->year, $this->month, 1);
        $startOfCalendar = $firstOfMonth->copy()->startOfWeek(Carbon::MONDAY);
        $endOfMonth = $firstOfMonth->copy()->endOfMonth();
        $endOfCalendar = $endOfMonth->copy()->endOfWeek(Carbon::SUNDAY);

        $days = [];
        $current = $startOfCalendar->copy();

        while ($current <= $endOfCalendar) {
            $dateStr = $current->format('Y-m-d');
            $days[] = [
                'date' => $dateStr,
                'day' => $current->day,
                'isCurrentMonth' => $current->month === $this->month,
                'isToday' => $current->isToday(),
                'events' => array_filter($this->events, function ($e) use ($dateStr) {
                    $eventDate = Carbon::parse($e['start_at'])->format('Y-m-d');
                    return $eventDate === $dateStr;
                }),
            ];
            $current->addDay();
        }

        return $days;
    }

    public function getMonthNameProperty(): string
    {
        return Carbon::create($this->year, $this->month, 1)->format('F Y');
    }

    public function render()
    {
        return view('livewire.calendar.calendar-view', [
            'calendarDays' => $this->calendarDays,
            'monthName' => $this->monthName,
            'colors' => Event::COLORS,
        ]);
    }
}
