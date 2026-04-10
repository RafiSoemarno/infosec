@props(['count' => 0])

<div class="notif-bell">
    <img src="{{ asset('storage/icon_denso/icon_notification.png') }}" alt="Notifications" class="notif-bell__icon">
    @if ($count > 0)
        <span class="notif-bell__badge">{{ $count > 99 ? '99+' : $count }}</span>
    @endif
</div>
