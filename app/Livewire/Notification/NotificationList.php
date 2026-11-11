<?php

namespace App\Livewire\Notification;

use App\Models\Reminder;
use App\Services\ReminderService;
use App\Services\TenantContext;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class NotificationList extends Component
{
    use WithPagination;

    public bool $onlyUnread = true;

    public function markAsRead(string $id): void
    {
        $reminder = Reminder::findOrFail($id);
        $reminder->markAsRead();

        $this->dispatch('toast', [
            'type' => 'info',
            'title' => 'Pengingat Ditandai',
            'message' => 'Notifikasi telah ditandai sudah dibaca.',
        ]);
    }

    public function markAllRead(TenantContext $tenantContext, ReminderService $reminderService): void
    {
        $store = $tenantContext->getStore();
        $reminderService->markAllAsRead($store);

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Semua Dibaca',
            'message' => 'Seluruh pengingat telah ditandai sudah dibaca.',
        ]);
    }

    public function scanNow(TenantContext $tenantContext, ReminderService $reminderService): void
    {
        $store = $tenantContext->getStore();
        $newCount = $reminderService->scanAndGenerateReminders($store);

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Pemindaian Selesai',
            'message' => "Sistem menemukan {$newCount} pengingat baru yang memerlukan perhatian.",
        ]);
    }

    public function render(TenantContext $tenantContext)
    {
        $query = Reminder::latest();

        if ($this->onlyUnread) {
            $query->unread();
        }

        $reminders = $query->paginate(20);

        return view('livewire.notification.notification-list', [
            'reminders' => $reminders,
        ])->layoutData([
            'title' => 'Pengingat & Notifikasi Sistem',
            'breadcrumbs' => [
                ['label' => 'Pengingat & Notifikasi'],
            ],
        ]);
    }
}
