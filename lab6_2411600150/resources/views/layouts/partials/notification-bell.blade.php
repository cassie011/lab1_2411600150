{{-- Academic alert bell (Lab 6). Used in the desktop and mobile headers. --}}
@php
    $unreadNotifications = auth()->user()->unreadNotifications()->latest()->take(5)->get();
    $unreadCount = auth()->user()->unreadNotifications()->count();
@endphp

<div class="dropdown notification-bell">

    <button
        class="btn btn-link position-relative p-1 text-primary-custom"
        type="button"
        id="notificationBell-{{ $bellId }}"
        data-bs-toggle="dropdown"
        aria-expanded="false"
        aria-label="Academic alerts"
    >
        <i class="bi bi-bell fs-5"></i>

        @if ($unreadCount > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-count">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </button>

    <div class="dropdown-menu dropdown-menu-end shadow-sm p-0 notification-menu" aria-labelledby="notificationBell-{{ $bellId }}">

        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
            <strong class="text-primary-custom">Academic Alerts</strong>

            @if ($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.readAll') }}">
                    @csrf
                    <button type="submit" class="btn btn-link btn-sm p-0 text-decoration-none">
                        Mark all as read
                    </button>
                </form>
            @endif
        </div>

        @forelse ($unreadNotifications as $notification)

            <a
                href="{{ route('notifications.read', $notification->id) }}"
                class="dropdown-item notification-item py-2"
            >
                <div class="d-flex gap-2">
                    <i class="bi bi-exclamation-triangle-fill text-warning mt-1"></i>

                    <div class="text-wrap">
                        <div class="small fw-semibold">
                            {{ $notification->data['student_name'] ?? 'Academic alert' }}
                        </div>

                        <div class="small text-muted">
                            {{ implode('; ', $notification->data['reasons'] ?? []) }}
                        </div>

                        <div class="small text-muted">
                            {{ $notification->created_at->diffForHumans() }}
                        </div>
                    </div>
                </div>
            </a>

        @empty

            <div class="px-3 py-4 text-center text-muted small">
                <i class="bi bi-bell-slash d-block fs-4 mb-1"></i>
                No new alerts
            </div>

        @endforelse

    </div>

</div>
